<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\MeEvaluation;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Project;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Project\Model\MeComment;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Project\View\MeView;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Team;
use Carbon\Carbon;

class MeActivity extends CoreModel
{
    protected $table = 'me_activities';
    protected $fillable = ['month', 'employee_id', 'attr_id', 'content'];

    /*
     * insert or update data
     */
    public static function insertOrUpdate($month, $data = [])
    {
        if (!isset($data['activities']) || !($activities = $data['activities'])) {
            return false;
        }
        $currUser = Permission::getInstance()->getEmployee();
        foreach ($activities as $attrId => $content) {
            $item = self::where('employee_id', $currUser->id)
                    ->where('attr_id', $attrId)
                    ->where('month', $month)
                    ->first();
            if (!$item) {
                $item = self::create([
                    'month' => $month,
                    'employee_id' => $currUser->id,
                    'attr_id' => $attrId,
                    'content' => $content
                ]);
            } else {
                $item->update(['content' => $content]);
            }
            //update comment
            MeComment::updateActivityComment($currUser->id, [
                'month' => $month,
                'attr_id' => $attrId,
                'content' => $content
            ]);
        }
        return true;
    }

    /*
     * get activities by employee
     */
    public static function getByEmpId($month, $empId = null)
    {
        if (!$empId) {
            $empId = auth()->id();
        }
        $result = self::where('month', $month)
                ->where('employee_id', $empId)
                ->get();
        if ($result->isEmpty()) {
            return [];
        }
        return $result->groupBy('attr_id');
    }

    /*
     * get by employee ids
     */
    public static function getByEmpIds($month, $empIds = [])
    {
        return self::where('month', $month)
                ->whereIn('employee_id', $empIds)
                ->get();
    }

    /*
     * get list member activiti of team or project
     */
    public static function getDataGrid($month)
    {
        $route = 'project::me_activity.view';
        $tblEmp = Employee::getTableName();
        $collection = Employee::select(
            $tblEmp.'.id',
            $tblEmp.'.name',
            $tblEmp.'.email',
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(proj.id, "||", proj.name)) SEPARATOR ",,") as proj_names')
        )
                ->with(['meActivities' => function ($query) use ($month) {
                    $query->where('month', $month);
                }])
                ->leftJoin(self::getTableName() . ' as act', $tblEmp.'.id', '=', 'act.employee_id')
                ->leftJoin(ProjectMember::getTableName() . ' as pjm', function ($join) use ($month) {
                    $timeMonth = Carbon::parse($month);
                    $join->on('act.employee_id', '=', 'pjm.employee_id')
                            ->where('pjm.start_at', '<=', $timeMonth->endOfMonth()->toDateString())
                            ->where('pjm.end_at', '>=', $timeMonth->startOfMonth()->toDateString());
                })
                ->leftJoin(TeamProject::getTableName() . ' as tpj', 'pjm.project_id', '=', 'tpj.project_id')
                ->leftJoin(Project::getTableName() . ' as proj', function ($join) {
                    $join->on('pjm.project_id', '=', 'proj.id')
                            ->whereNull('proj.deleted_at');
                })
                ->leftJoin(TeamMember::getTableName() . ' as tmb', $tblEmp . '.id', '=', 'tmb.employee_id')
                ->where('act.month', $month)
                ->groupBy($tblEmp.'.id');
        //check permission
        $scope = Permission::getInstance();
        if ($scope->isScopeCompany(null, $route)) {
            //get all
        } elseif ($scope->isScopeTeam(null, $route)) {
            $currUser = $scope->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $collection->where(function ($query) use ($teamIds) {
                $query->whereIn('tpj.team_id', $teamIds)
                        ->orWhereIn('tmb.team_id', $teamIds);
            });
        } elseif ($scope->isScopeSelf(null, $route)) {
            $collection->whereIn(
                'pjm.project_id',
                function ($query) use ($scope) {
                    $query->select('project_id')
                            ->from(ProjectMember::getTableName())
                            ->where('employee_id', $scope->getEmployee()->id);
                }
            );
        } else {
            CoreView::viewErrorPermission();
        }
        $pager = Config::getPagerData();
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('act.created_at', 'desc');
        }
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * check is editable activities
     */
    public static function checkEditable($month)
    {
        $empId = Permission::getInstance()->getEmployee()->id;
        $month = Carbon::parse($month);
        $listMe = MeEvaluation::where('eval_time', $month->startOfMonth()->toDateTimeString())
                ->where('employee_id', $empId)
                ->select(
                    DB::raw('COUNT(id) AS total'),
                    DB::raw('SUM(CASE WHEN status = '. MeEvaluation::STT_CLOSED .' THEN 1 ELSE 0 END) AS total_approve'))
                ->first();
        if (!$listMe || !$listMe->total) {
            return true;
        }
        return $listMe->total != (int) $listMe->total_approve;
    }

    /**
     * cronjob alert ME activities
     * @return null
     */
    public static function mailAlert()
    {
        $strMails = CoreConfigData::getValueDb(MeView::KEY_MAIL_ACTIVITY);
        if (!$strMails) {
            return;
        }
        $arrayMails = preg_split('/\;|\r\n|\n|\r/', $strMails);

        //filter mail group
        $teamList = Team::whereIn('mail_group', $arrayMails)->select('id', 'mail_group')->get();
        $listTeamIds = $teamList->lists('id')->toArray();
        //filter mail account
        $accounts = Employee::whereIn('email', $arrayMails)->select('id', 'name', 'email')->get();

        $timeNow = Carbon::now();
        $month = $timeNow->format('m-Y');
        $arrayMailQueues = [];
        $subject = trans('project::me.mail_subject_alert_activity', ['month' => $month]);
        $detailLink = route('project::profile.me.activity', ['month' => $timeNow->format('Y-m')]);
        $blWeek = MeView::getBaselineWeekDates();
        $dataMailItem = [
            'month' => $month,
            'detailLink' => $detailLink,
            'blWeek' => $blWeek,
        ];

        if ($arrayMails) {
            foreach ($arrayMails as $mail) {
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($mail)
                        ->setSubject($subject)
                        ->setTemplate('project::me.mail.alert-activity', $dataMailItem);
                $arrayMailQueues[] = $emailQueue->getValue();
            }
        }

        if (!$accounts->isEmpty()) {
            foreach ($accounts as $emp) {
                $dataMailItem['dearName'] = $emp->name;
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($emp->email)
                        ->setSubject($subject)
                        ->setTemplate('project::me.mail.alert-activity', $dataMailItem);
                $arrayMailQueues[] = $emailQueue->getValue();
            }
        }

        if (!$arrayMailQueues) {
            return;
        }

        DB::beginTransaction();
        try {
            EmailQueue::insert($arrayMailQueues);
            $employeeIds = [];
            if ($listTeamIds) {
                $empTbl = Employee::getTableName();
                $employeeIds = Employee::select($empTbl . '.id')
                        ->join(TeamMember::getTableName() . ' as tmb', $empTbl . '.id', '=', 'tmb.employee_id')
                        ->whereIn('tmb.team_id', $listTeamIds)
                        ->where(function ($query) use ($empTbl) {
                            $query->whereNull('leave_date')
                                    ->orWhereRaw('DATE('. $empTbl .'.leave_date) > CURDATE()');
                        })
                        ->lists($empTbl . '.id')
                        ->toArray();
            }
            $employeeIds = array_unique(array_merge($employeeIds, $accounts->lists('id')->toArray()));
            $contentDetail = RkNotify::renderSections('project::me.mail.alert-activity', $dataMailItem);
            if ($employeeIds) {
                \RkNotify::put($employeeIds, $subject, $detailLink, ['icon' => 'project.png', 'actor_id' => null,
                    'category_id' => RkNotify::CATEGORY_PERIODIC, 'content_detail' => $contentDetail]);
            }
            DB::commit();
        } catch (\Exception $ex) {
            \Log::info($ex);
            DB::rollback();
        }
    }
}
