<?php

namespace Rikkei\Project\Model;

use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Project\View\MeView;
use Carbon\Carbon;

class MeMailAlert {
    
    /**
     * send alert email to PM
     * @return type
     */
    public static function sendToPM() {
        $projectTbl = Project::getTableName();
        $employeeTbl = Employee::getTableName();
        $projMemTbl = ProjectMember::getTableName();
        $meTbl = MeEvaluation::getTableName();
        $lastMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        
        $pms = DB::select('SELECT emp.id, emp.name as pm_name, emp.email as pm_email, GROUP_CONCAT(DISTINCT proj.name SEPARATOR ", ") as project_names '
                . 'FROM ' . $projMemTbl . ' AS pm '
                . 'INNER JOIN ' . $projectTbl . ' AS proj ON pm.project_id = proj.id '
                . 'AND proj.status = ' . Project::STATUS_APPROVED . ' '
                . 'AND proj.deleted_at is NULL '
                . 'AND ((proj.state = ' . Project::STATE_CLOSED . ' AND proj.end_at >= \''. $lastMonth->subMonthNoOverflow()->toDateTimeString() .'\') '
                . 'OR proj.state = '. Project::STATE_PROCESSING .') '
                . 'AND proj.id NOT IN (SELECT project_id FROM '. $meTbl .' '
                                . 'WHERE status <> '. MeEvaluation::STT_DRAFT .' '
                                . 'AND eval_time >= \''. Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString() .'\' '
                                . 'GROUP BY project_id) '
                . 'INNER JOIN ' . $employeeTbl . ' AS emp ON pm.employee_id = emp.id '
                . 'AND emp.deleted_at is NULL '
                . 'WHERE pm.status = ' . ProjectMember::STATUS_APPROVED . ' '
                . 'AND pm.is_disabled <> ' . ProjectMember::STATUS_DISABLED . ' '
                . 'AND pm.type = ' . ProjectMember::TYPE_PM . ' '
                . 'GROUP BY emp.id');

        if (count($pms) < 1) {
            return;
        }
        $subjectMonth = Carbon::now()->subMonthNoOverflow()->format('m/Y');
        $pmIds = [];
        foreach ($pms as $pm) {
            $pmIds[] = $pm->id;
            $data = (array) $pm;
            $contentDetail = RkNotify::renderSections('project::me.mail.alert-pm', $data);
            $email = new EmailQueue();
            $email->setTo($pm->pm_email, $pm->pm_name)
                    ->setTemplate('project::me.mail.alert-pm', $data)
                    ->setSubject(trans('project::me.Alert PM', ['month' => $subjectMonth]))
                ->setNotify($pm->id, trans('project::me.Alert PM', ['month' => $subjectMonth]),
                    route('project::project.eval.index'),
                    ['schedule_code' => 'ME_alert_pm', 'icon' => 'project.png', 'category_id' => RkNotify::CATEGORY_PERIODIC, 'content_detail' => $contentDetail])
                    ->save();
        }
    }
    
    /**
     * send alert email to GL
     * @return type
     */
    public static function sendToGL() {
        $projectTbl = Project::getTableName();
        $employeeTbl = Employee::getTableName();
        $meTbl = MeEvaluation::getTableName();
        $lastMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        
        $leaders = DB::select('SELECT emp.id, emp.name as leader_name, emp.email as leader_email, GROUP_CONCAT(DISTINCT proj.name SEPARATOR ", ") AS project_names '
                . 'FROM ' . $meTbl . ' AS me '
                . 'INNER JOIN ' . $projectTbl . ' AS proj ON me.project_id = proj.id '
                . 'AND proj.deleted_at is NULL '
                . 'INNER JOIN ' . $employeeTbl . ' AS emp ON proj.leader_id = emp.id '
                . 'AND emp.deleted_at is NULL '
                . 'WHERE me.eval_time = \'' . $lastMonth->toDateTimeString() . '\' '
                . 'AND me.status = ' . MeEvaluation::STT_SUBMITED . ' '
                . 'GROUP BY emp.id');
        
        if (count($leaders) < 1) {
            return;
        }
        $leaderIds = [];
        foreach ($leaders as $leader) {
            $leaderIds[] = $leader->id;
            $data = (array) $leader;
            $email = new EmailQueue();
            $email->setTo($leader->leader_email, $leader->leader_name)
                    ->setTemplate('project::me.mail.alert-gl', $data)
                    ->setSubject(trans('project::me.Alert GL', ['month' => $lastMonth->format('m/Y')]))
                    ->save();
            \RkNotify::put(
                $leader->id,
                trans('project::me.Alert GL', ['month' => $lastMonth->format('m/Y')]),
                route('project::project.eval.list_by_leader'),
                ['schedule_code' => 'ME_alert_leader', 'icon' => 'project.png',
                    'category_id' => RkNotify::CATEGORY_PERIODIC,
                    'content_detail' => trans('project::me.Alert GL', ['month' => $lastMonth->format('m/Y')])
                ]);
        }
    }
    
    /**
     * send alert email to staff
     * @return type
     */
    public static function sendToStaff() {
        $projectTbl = Project::getTableName();
        $employeeTbl = Employee::getTableName();
        $meTbl = MeEvaluation::getTableName();
        $lastMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        
        $staffs = DB::select('SELECT emp.id, emp.name as st_name, emp.email as st_email, GROUP_CONCAT(DISTINCT proj.name SEPARATOR ", ") AS project_names '
                . 'FROM ' . $meTbl . ' AS me '
                . 'INNER JOIN ' . $projectTbl . ' AS proj ON me.project_id = proj.id '
                . 'AND proj.deleted_at is NULL '
                . 'INNER JOIN ' . $employeeTbl . ' AS emp ON me.employee_id = emp.id '
                . 'AND emp.deleted_at is NULL '
                . 'WHERE me.eval_time = \'' . $lastMonth->toDateTimeString() . '\' '
                . 'AND me.status = ' . MeEvaluation::STT_APPROVED . ' '
                . 'GROUP BY emp.id');

        if (count($staffs) < 1) {
            return;
        }
        $staffIds = [];
        foreach ($staffs as $st) {
            $staffIds[] = $st->id;
            $data = (array) $st;
            $email = new EmailQueue();
            $email->setTo($st->st_email, $st->st_name)
                    ->setTemplate('project::me.mail.alert-st', $data)
                    ->setSubject(trans('project::me.Alert ST', ['month' => $lastMonth->format('m/Y')]))
                    ->save();
            \RkNotify::put(
                $st->id,
                trans('project::me.Alert ST', ['month' => $lastMonth->format('m/Y')]),
                route('project::project.profile.confirm'), [
                    'schedule_code' => 'ME_alert_staff', 'icon' => 'project.png', 'category_id' => RkNotify::CATEGORY_PROJECT,
                    'content_detail' => trans('project::me.Alert ST', ['month' => $lastMonth->format('m/Y')])
                ]);
        }
    }

    /*
     * alert schedule ME & reward
     */
    public static function alertSchedule()
    {
        $roleIds = [Team::ROLE_SUB_LEADER, Team::ROLE_TEAM_LEADER];
        $roleNames = ['PM'];
        $empTbl = Employee::getTableName();
        $listRecievers = Employee::select(
            $empTbl.'.id',
            $empTbl.'.name',
            $empTbl.'.email',
            'tmb.role_id',
            'role.role'
        )
            ->leftJoin(TeamMember::getTableName() . ' as tmb', 'tmb.employee_id', '=', $empTbl . '.id')
            ->leftJoin(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
            ->leftJoin(EmployeeRole::getTableName() . ' as emp_role', 'emp_role.employee_id', '=', $empTbl . '.id')
            ->leftJoin(Role::getTableName() . ' as role', 'emp_role.role_id', '=', 'role.id')
            ->where(function ($query) use ($roleIds, $roleNames) {
                $query->where(function ($subQuery) use ($roleIds) {
                    $subQuery->whereIn('tmb.role_id', $roleIds)
                        ->where('team.is_soft_dev', Team::IS_SOFT_DEVELOPMENT);
                })
                ->orWhereIn('role.role', $roleNames);
            })
            ->where(function ($query) {
                $query->whereNull('leave_date')
                    ->orWhereRaw('DATE(leave_date) > CURDATE()');
            })
            ->groupBy($empTbl.'.id')
            ->get();

        if ($listRecievers->isEmpty()) {
            return;
        }

        $aryBaseLineDates = MeView::getBaselineWeekDates();
        $baseLineDate = Carbon::now()->day($aryBaseLineDates[1]);

        $pmSubmitDate = clone $baseLineDate;
        $pmSubmitDate = MeView::findNextWorkDate($pmSubmitDate->addDay());

        $leaderReviewDate = clone $pmSubmitDate;
        $leaderReviewDate = MeView::findNextWorkDate($leaderReviewDate->addDay());

        $memberFeedbackDate = clone $leaderReviewDate;
        $memberFeedbackDate = MeView::findNextWorkDate($memberFeedbackDate->addDay());

        $memberConfirmDate = clone $memberFeedbackDate;
        $memberConfirmDate = MeView::findNextWorkDate($memberConfirmDate->addDay());

        $submitRewardDate = clone $memberConfirmDate;
        $submitRewardDate = MeView::findNextWorkDate($submitRewardDate->addDay());

        $deadLines = [
            'baseLineDate' => [
                MeView::textDayOfWeek($baseLineDate),
                $baseLineDate->toDateString(),
                trans('project::me.Baseline Project Point')
            ],
            'activity' => [
                MeView::textDayOfWeek($baseLineDate),
                $baseLineDate->toDateString(),
                trans('project::me.Deadline Member fill activities')
            ],
            'pmSubmit' => [
                MeView::textDayOfWeek($pmSubmitDate),
                $pmSubmitDate->toDateString(),
                trans('project::me.Deadline PM submit ME')
            ],
            'leaderReview' => [
                MeView::textDayOfWeek($leaderReviewDate),
                $leaderReviewDate->toDateString(),
                trans('project::me.Deadline Leader review ME')
            ],
            'memberFeedback' => [
                Meview::textDayOfWeek($memberFeedbackDate),
                $memberFeedbackDate->toDateString(),
                trans('project::me.Deadline Member feedback ME')
            ],
            'memberConfirm' => [
                MeView::textDayOfWeek($memberConfirmDate),
                $memberConfirmDate->toDateString(),
                trans('project::me.Deadline Member confirm ME & baseline ME')
            ],
            'submitReward' => [
                MeView::textDayOfWeek($submitRewardDate),
                $submitRewardDate->toDateString(),
                trans('project::me.Deadline Leader suggest OSDC, Project reward')
            ]
        ];

        $currentMonth = Carbon::now()->format('m/Y');
        $toNotifyIds = [];

        foreach ($listRecievers as $reciever) {
            $toNotifyIds[] = $reciever->id;
            $data = (array) $reciever;
            $data['deadLines'] = $deadLines;
            $data['month'] = $currentMonth;
            $email = new EmailQueue();
            $email->setTo($reciever->email, $reciever->name)
                    ->setTemplate('project::me.mail.alert-schedule', $data)
                    ->setSubject(trans('project::me.alert_schedule', ['month' => $currentMonth]))
                    ->save();
            $contentDetail = RkNotify::renderSections('project::me.mail.alert-schedule', $data);
            \RkNotify::put(
                $reciever->id,
                trans('project::me.alert_schedule', ['month' => $currentMonth]) . ', ' . trans('notify::view.Detail in mail'),
                null,
                ['icon' => 'project.png', 'category_id' => RkNotify::CATEGORY_PERIODIC, 'content_detail' => $contentDetail]
            );
        }
    }
}

