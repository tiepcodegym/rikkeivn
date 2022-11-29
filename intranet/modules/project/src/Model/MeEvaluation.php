<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Project\View\View;
use Carbon\Carbon;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\MeHistory;
use Rikkei\Project\Model\MeComment;
use Rikkei\Project\Model\ProjPointBaseline;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Me\View\View as MeView;
use Rikkei\ManageTime\Model\TimekeepingAggregate;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Rikkei\Core\View\View as CoreView;
use Session;

class MeEvaluation extends CoreModel {

    protected $table = 'me_evaluations';
    protected $fillable = [
        'employee_id',
        'project_id',
        'team_id',
        'eval_time',
        'proj_point',
        'avg_point',
        'version',
        'comment',
        'manager_id',
        'status',
        'assignee',
        'effort',
        'proj_index'
    ];
    public $dates = ['eval_time', 'created_at', 'updated_at'];

    const STT_DRAFT = 0;
    const STT_NEW = 1;
    const STT_SUBMITED = 2;
    const STT_APPROVED = 3;
    const STT_FEEDBACK = 4;
    const STT_CLOSED = 5;
    const STT_REWARD = 20;
    
    const FT_OSDC = 1;
    const FT_BASE = 1.2;
    const PJ_OSDC = 1;
    const PJ_BASE = 2;
    
    const LEADER_UPDATED = 1;
    const COO_UPDATED = 2;
    
    const TH_EXCELLENT = 4;
    const TH_GOOD = 3.3;
    const TH_FAIR = 2.8;
    const TH_SATIS = 2;
    const TH_UNSATIS = 0;
    
    /**
     * max project point
     */
    const MAX_PP = 28;
    /**
     * max range point
     */
    const MAX_POINT = 5;

    /*
     * pager limit in view leader review
     */
    const PAGER_LIMIT_IN_LEADER_REVIEW = 50;
    const VAL_ALL = '_all_';

    /**
     * get array status
     * @return array
     */
    public static function arrayStatus() {
        return [
            self::STT_DRAFT => trans('project::me.Draft'),
            self::STT_NEW => trans('project::me.New'),
            self::STT_SUBMITED => trans('project::me.Submitted'),
            self::STT_APPROVED => trans('project::me.Approved'),
            self::STT_FEEDBACK => trans('project::me.Feedbacked'),
            self::STT_CLOSED => trans('project::me.Accepted')
        ];
    }
    
    public static function filterStatus() {
        return [
            self::STT_SUBMITED => trans('project::me.Submitted'),
            self::STT_APPROVED => trans('project::me.Approved'),
            self::STT_FEEDBACK => trans('project::me.Feedbacked'),
            self::STT_CLOSED => trans('project::me.Accepted')
        ];
    }
    
    /**
     * get list contributes level
     * @return array
     */
    public static function filterContributes() {
        return [
            self::TH_EXCELLENT.'-1000' => trans('project::me.Excellent'),
            self::TH_GOOD.'-'.self::TH_EXCELLENT => trans('project::me.Good'),
            self::TH_FAIR.'-'.self::TH_GOOD => trans('project::me.Fair'),
            self::TH_SATIS.'-'.self::TH_FAIR => trans('project::me.Satisfactory'),
            self::TH_UNSATIS.'-'.self::TH_SATIS => trans('project::me.Unsatisfactory')
        ];
    }
    
    /**
     * get html list options point
     * @param type $itemPoint
     * @return string
     */
    public function optionsPoint($itemPoint = null, $hasNA = false) {
        $html = '';
        $options = MeAttribute::optionPoints($hasNA);
        foreach ($options as $point => $label) {
            $html .= '<option value="'. $point .'" '. ($point == $itemPoint ? 'selected' : '') .'>'. $label .'</option>';
        }
        return $html;
    }
    
    public function getLabelPerformPoint($point, $hasNA = false) {
        $options = MeAttribute::optionPoints($hasNA);
        foreach ($options as $key => $value) {
            if ($point >= $key) {
                return $value;
            }
        }
        return 0;
    }

    /**
     * Get status label
     * @return string
     */
    public function getStatusLabelAttribute() {
        if ($this->status == self::STT_REWARD) {
            return 'N/A';
        }
        $prefix = '';
        if ($this->status == self::STT_FEEDBACK && $this->last_user_updated) {
            if ($this->is_leader_updated == self::LEADER_UPDATED) {
                $prefix = trans('project::me.Leader');
            } else if ($this->is_leader_updated == self::COO_UPDATED) {
                $prefix = trans('project::me.COO');
            } else {
                $prefix = trans('project::me.Staff');
            }
        }
        
        $statuses = self::arrayStatus();
        return $prefix . ' ' . (isset($statuses[$this->status]) ? $statuses[$this->status] : trans('project::me.Draft'));
    }
    
    /**
     * check status can change point
     */
    public function canChangePoint()
    {
        /*$scope = Permission::getInstance();
        if (!$currentUser) {
            $currentUser = $scope->getEmployee();
        }
        if ($scope->isAllow('project::me.coo_edit_point')) {
            return true;
        } dont check COO accout*/
        return !in_array($this->status, [self::STT_APPROVED, self::STT_SUBMITED, self::STT_CLOSED]);
    }

    /**
     * get list of attributes
     * @return type
     */
    public function meAttributes() {
        return $this->belongsToMany('\Rikkei\Project\Model\MeAttribute', 'me_points', 'eval_id', 'attr_id')->withPivot('point');
    }

    /**
     * get employee
     * @return type
     */
    public function employee() {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'employee_id', 'id');
    }

    /**
     * get project 
     * @return type
     */
    public function project() {
        return $this->belongsTo('\Rikkei\Project\Model\Project', 'project_id', 'id');
    }

    /**
     * get team
     * @return type
     */
    public function team() {
        return $this->belongsTo('\Rikkei\Team\Model\Team', 'team_id', 'id');
    }

    /**
     * get creator (manager)
     * @return object
     */
    public function creator()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'manager_id', 'id');
    }
    
    /**
     * get histories item
     * @return type
     */
    public function histories() {
        return $this->hasMany('\Rikkei\Project\Model\MeHistory', 'eval_id');
    }

    /**
     * get factor project type base or osdc
     * @param integer $projectType
     * @return integer
     */
    public function getFactorProjectType($projectType = null) {
        return self::factorOfProjType($this->project_id, $projectType, $this->proj_index);
    }

    public static function factorOfProjType($projId = null, $projType = null, $projIndex = null)
    {
        if (!$projId) {
            return $projIndex !== null ? number_format($projIndex, 1, '.', ',') : 'N/A';
        }
        if (!$projType) {
            $projType = self::PJ_OSDC;
            $project = Project::find($projId);
            if ($project) {
                $projType = $project->type;
            }
        }
        if ($projType == self::PJ_BASE) {
            return self::FT_BASE;
        }
        return self::FT_OSDC;
    }

    /**
     * check evaluation exists
     * @param type $employee_id
     * @param type $project_id
     * @param type $month
     * @param type $year
     * @return object | boolean
     */
    public static function checkExists($employee_id, $project_id, $time) {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        $check = self::where('employee_id', $employee_id)
                ->where('project_id', $project_id)
                ->where(DB::raw('MONTH(eval_time)'), $time->format('m'))
                ->where(DB::raw('YEAR(eval_time)'), $time->format('Y'))
                ->first();
        return $check;
    }
    
    /**
     * check exists employee team
     * @param type $employee_id
     * @param type $team_id
     * @param type $time
     * @return type
     */
    public static function checkExistsTeam($employee_id, $team_id, $time) {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        $check = self::where('employee_id', $employee_id)
                ->where('team_id', $team_id)
                ->where(DB::raw('MONTH(eval_time)'), $time->format('m'))
                ->where(DB::raw('YEAR(eval_time)'), $time->format('Y'))
                ->first();
        return $check;
    }

    /**
     * get list comments
     * @return type
     */
    public function meComments() {
        return $this->hasMany('\Rikkei\Project\Model\MeComment', 'eval_id', 'id');
    }
    
    /**
     * check has comment
     * @param type $attr_id
     * @return boolean
     */
    public function hasComments($attr_id = null) {
        $type = 'has_comment ';
        $comments = $this->meComments();
        if ($attr_id) {
            $comments = $comments->where('attr_id', $attr_id);
        } else {
            $comments = $comments->where('comment_type', MeComment::TYPE_NOTE);
        }
        $comments = $comments->select('type')
                ->groupBy('type')
                ->get();
        if ($comments->isEmpty()) {
            return false;
        }
        
        foreach ($comments as $comment) {
            $type .= 'td'.$comment->type_class.' ';
        }
        return trim($type);
    }

    /*
     * get list array id => point of attribute
     */
    public function listPoints()
    {
        $attributes = $this->meAttributes;
        if ($attributes->isEmpty()) {
            return [];
        }
        return $attributes->lists('pivot.point', 'id')->toArray();
    }
    
    /**
     * get attriubte point
     * @param array $listPoints
     * @param int $attrId
     * @param mixed $default
     * @return int
     */
    public function getAttrPoint($listPoints, $attrId, $default = null)
    {
        if (!(Carbon::parse($this->eval_time)->diff(Carbon::parse(config('project.me_late_month')))->invert)
                && $attrId == MeAttribute::getMeTimeAttrId()) {
            return MeAttribute::NA;
        }
        if (isset($listPoints[$attrId])) {
            return round($listPoints[$attrId]);
        }
        if ($default) {
            return round($default);
        }
        if ($this->status != self::STT_DRAFT) {
            return 0;
        }
        return null;
    }
    
    /**
     * update ME point after project point had changed
     * @param type $project
     * @return type
     */
    public static function updateProjectPointChange($project, $project_point = null, $data = []) {
        if (!is_object($project)) {
            $project = Project::find($project);
            if (!$project) {
                return;
            }
        }
        $project_id = $project->id;
        $end_at = Carbon::parse($project->end_at);
        $me_items = self::with('employee')
                ->with('histories')
                ->where('project_id', $project_id)
                ->where('eval_time', $end_at->startOfMonth()->toDateTimeString())
                ->get();

        if ($me_items->isEmpty()) {
            return;
        }
        
        if (!$project_point) {
            $project_point = self::getProjectPointLastMonth($project, $end_at);
        }
        $collect_member = [];
        $attrs_normal = MeAttribute::getNormalAttrs();
        $attrs_perform = MeAttribute::getPerformAttrs();
        if ($attrs_normal->isEmpty() || $attrs_perform->isEmpty()) {
            return;
        }
        
        foreach ($me_items as $item) {
            $old_point = $item->avg_point;
            //update point
            self::updateMEPoint($item, $attrs_normal, $attrs_perform, $project_point);
            
            $employee = $item->employee;
            $member_data = [
                'to_type' => 1,
                'employee_name' => $employee->name,
                'project_name' => $project->name,
                'time' => $item->eval_time,
                'me_point_old' => $old_point,
                'me_point_new' => $item->avg_point,
                'project_point_old' => $data['project_point_old'],
                'project_point_new' => $project_point,
                'css_point_old' => $data['css_point_old'],
                'css_point_new' => $data['css_point_new'],
                'is_coo_update' => $data['is_coo_update']
            ];

            $check_evaluated = $item->histories()
                    ->where('action_type', MeHistory::AC_APPROVED)
                    ->first();
            if (in_array($item->status, [self::STT_APPROVED, self::STT_CLOSED]) || $check_evaluated) {
                $mail_to_member = new EmailQueue();
                $mail_to_member->setTo($employee->email)
                        ->setSubject(trans('project::me.Rikkei Monthly Evaluation - Update'))
                        ->setTemplate('project::me.mail.summary-update', $member_data)
                        ->setNotify(
                            $employee->id,
                            trans('project::me.Rikkei Monthly Evaluation - Update') . ' on project ' . $project->name .
                                ' (view email detail)', null, ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                        )
                        ->save();
            }
            
            array_push($collect_member, array_only($member_data, ['employee_name', 'me_point_old', 'me_point_new']));
        }
        
        $pm_id = self::getPMIdOfProject($project_id);
        $pm = Employee::find($pm_id);
        if (!$pm) {
            return;
        }
        $pm_data = [
            'to_type' => 2,
            'pm_name' => $pm->name,
            'project_name' => $project->name,
            'time' => $end_at,
            'project_point_old' => $data['project_point_old'],
            'project_point_new' => $project_point,
            'css_point_old' => $data['css_point_old'],
            'css_point_new' => $data['css_point_new'],
            'is_coo_update' => $data['is_coo_update'],
            'members' => $collect_member
        ];
        $mail_to_pm = new EmailQueue();
        $mail_to_pm->setTo($pm->email)
                ->setSubject(trans('project::me.Rikkei Monthly Evaluation - Update'))
                ->setTemplate('project::me.mail.summary-update', $pm_data)
                ->setNotify(
                    $pm->id,
                    trans('project::me.Rikkei Monthly Evaluation - Update') . ' on project ' . $project->name .
                        ' (view email detail)', null, ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                )
                ->save();
        
        $leader = $project->groupLeader;
        if (!$leader) {
            return;
        }
        $leader_data = $pm_data;
        unset($leader_data['pm_name']);
        $leader_data['to_type'] = 3;
        $leader_data['leader_name'] = $leader->name;
        $mail_to_leader = new EmailQueue();
        $mail_to_leader->setTo($leader->email)
                ->setSubject(trans('project::me.Rikkei Monthly Evaluation - Update'))
                ->setTemplate('project::me.mail.summary-update', $leader_data)
                ->setNotify(
                    $leader->id,
                    trans('project::me.Rikkei Monthly Evaluation - Update') . ' on project ' . $project->name .
                        ' (view email detail)', null, ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                )
                ->save();
    }
    
    /**
     * get project baseline date
     * @param type $time
     * @return object
     */
    public static function getBaselineDate($time) {
        $projBaselineDate = CoreConfigData::getValueDb('project.me.baseline_date');
        $projBaselineDay = Carbon::FRIDAY;
        $projMeDate = clone $time;
        if ($projBaselineDate) {
            $projMeDate->setDate($time->year, $time->month, (int) $projBaselineDate);
        } else {
            $projMeDate->endOfMonth();
        }
        if ($projMeDate->gt($time->endOfMonth())) {
            $projMeDate = $time;
        }
        $dayChoose = clone $projMeDate;
        $meYear = $projMeDate->year;
        $meWeek = $projMeDate->weekOfYear;
        $dayChoose->setISODate($meYear, $meWeek, $projBaselineDay == 0 ? 7 : $projBaselineDay);
        if ($dayChoose->gt($projMeDate)) {
            $dayChoose->subWeek();
        }
        return $dayChoose->setTime(23, 59, 59);
    }
    
    /**
     * get project poin last month
     * @param type $project_id
     * @param type $time
     * @return int
     */
    public static function getProjectPointLastMonth($project, $time) {
        if (!is_object($project)) {
            $project = Project::getProjectById($project);
        }
        $project_id = $project->id;
        if ($project->type == Project::TYPE_TRAINING) {
            return Project::POINT_PROJECT_TYPE_TRANING;
        } else if ($project->type == Project::TYPE_RD) {
            return Project::POINT_PROJECT_TYPE_RD;
        } else if ($project->type == Project::TYPE_ONSITE) {
            return Project::POINT_PROJECT_TYPE_ONSITE;
        }
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        } 
        $dayChoose = self::getBaselineDate($time);
        $point = ProjPointBaseline::where('project_id', $project_id)
                ->whereDate('created_at', '>=', $time->startOfMonth()->toDateString())
                ->whereDate('created_at', '<=', $dayChoose->toDateString())
                ->orderBy('created_at', 'desc')
                ->first(['point_total']);
        if (!$point) {
            return 0;
        }
        return $point->point_total;
    }

    /**
     * add attribute point
     * @param type $eval_id
     * @param type $attr_id
     * @param type $point
     * @param type $avg_point
     */
    public static function addAttrPoint($eval_id, $attr_id, $point, $avg_point = null) {
        $eval_item = self::find($eval_id);
        if ($eval_item) {
            if (!$eval_item->canChangePoint()) {
                return false;
            }
            
            if ($avg_point) {
                $eval_item->avg_point = $avg_point;
                $eval_item->save();
            }
            
            $eval_attr = $eval_item->meAttributes()->wherePivot('attr_id', $attr_id)->first();
            if ($eval_attr) {
                $eval_item->meAttributes()->updateExistingPivot($attr_id, ['point' => $point]);
            } else {
                $eval_item->meAttributes()->attach([$attr_id => ['point' => $point]]);
            }
        }
        return $eval_item;
    }

    /**
     * get append value
     * @return string
     */
    public function getContributeLabelAttribute() {
        $value = $this->avg_point;
        return self::getContributeLabel($value, $this->eval_time);
    }
    
    public static function getContributeLabel($value, $evalTime = null) {
        if ($evalTime && $evalTime->format('Y-m') > config('project.me_sep_month')) {
            if ($value >= MeView::TYPE_S) {
                return 'S';
            }
            if ($value >= MeView::TYPE_A) {
                return 'A';
            }
            if ($value >= MeView::TYPE_B) {
                return 'B';
            }
            if ($value >= MeView::TYPE_C) {
                return 'C';
            }
        }

        if ($value >= self::TH_EXCELLENT) {
            return trans('project::me.Excellent');
        }
        if ($value >= self::TH_GOOD) {
            return trans('project::me.Good');
        }
        if ($value >= self::TH_FAIR) {
            return trans('project::me.Fair');
        }
        if ($value >= self::TH_SATIS) {
            return trans('project::me.Satisfactory');
        }
        return trans('project::me.Unsatisfactory');
    }

    /**
     * change value by column name
     * @param type $id
     * @param type $column
     * @param type $value
     * @return type
     */
    public static function changeValue($id, $column, $value) {
        $eval = self::find($id);
        if (!$eval) {
            return false;
        }
        $eval->$column = $value;
        $eval->save();
        return $eval;
    }

    /**
     * get items by assignee
     * @param type $assignee
     * @return collection
     */
    public static function getEvalByAssignee($assignee) {
        return self::where('assignee', $assignee)
                        ->where('status', '!=', self::STT_DRAFT)
                        ->get();
    }

    /**
     * get all projects of current user
     * @param type $manager_id
     * @return type
     */
    public static function getProjectsOfCurrentManager($pmId = null, $returnBuilder = false)
    {
        $managerId = $pmId ? $pmId : Permission::getInstance()->getEmployee()->id;
        $tblProj = Project::getTableName();
        $tblProjMb = ProjectMember::getTableName();
        $tblTeamProj = TeamProject::getTableName();
        //join project member get allocation
        $projects = Project::join($tblProjMb . ' as projmb', function ($join) use ($tblProj) {
                $join->on($tblProj.'.id', '=', 'projmb.project_id')
                    ->where('projmb.status', '=', ProjectMember::STATUS_APPROVED)
                    ->where('projmb.is_disabled', '!=', ProjectMember::STATUS_DISABLED);
            });
        
        //check permission
        if (Permission::getInstance()->isScopeCompany()) {
            //get all
        } elseif (Permission::getInstance()->isScopeTeam()) {
            $teamIds = TeamMember::where('employee_id', $managerId)
                    ->lists('team_id')->toArray();
            //join team project get project of team or leader,pm of project
            $projects->join($tblTeamProj . ' as tpj', $tblProj.'.id', '=', 'tpj.project_id')
                    ->where(function ($query) use ($teamIds, $managerId, $tblProj) {
                        $query->whereIn('tpj.team_id', $teamIds)
                            ->orWhere(function ($subquery) use ($managerId, $tblProj) {
                                $subquery->where($tblProj.'.manager_id', '=', $managerId)
                                    ->orWhere($tblProj.'.leader_id', '=', $managerId);
                            });
                    });
        } elseif (Permission::getInstance()->isScopeSelf()) {
            //get project manager or leader
            $projects->where(function ($query) use ($managerId, $tblProj) {
                    $query->whereIn('projmb.type', [ProjectMember::TYPE_PM, ProjectMember::TYPE_SUBPM])
                        ->where('projmb.employee_id', '=', $managerId)
                        ->orWhere($tblProj.'.leader_id', '=', $managerId);
                });
        }

        //get project processing or closest recently not over 2 month
        $timeTwoMonthsPrevious = Carbon::now()->subMonth(2)->startOfMonth()->toDateTimeString();
        $projects->where($tblProj.'.status', Project::STATUS_APPROVED)
                ->where(function($query) use ($timeTwoMonthsPrevious, $tblProj) {
                    $query->where($tblProj.'.state', Project::STATE_CLOSED)
                          ->where($tblProj.'.end_at', ">=", $timeTwoMonthsPrevious);
                    $query->orWhere($tblProj.'.state', Project::STATE_PROCESSING);
                })
                ->orderBy($tblProj.'.created_at', 'desc')
                ->groupBy($tblProj.'.id');

        if ($returnBuilder) {
            return $projects;
        }
        return $projects->select($tblProj.'.id', $tblProj.'.name', $tblProj.'.leader_id', $tblProj.'.start_at', $tblProj.'.end_at')
                ->get();
    }

    /**
     * get Stafts (Devs) of this project
     * @param type $project_id
     * @param type $month
     * @param type $year
     * @return type
     */
    public static function getDevsOfProject($project_id, $time) {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        $employeeTable = Employee::getTableName();
        $memberTable = ProjectMember::getTableName();
        return Employee::join($memberTable . " as pjm", $employeeTable . ".id", '=', "pjm.employee_id")
                        ->where('pjm.project_id', $project_id)
                        ->whereDate('pjm.start_at', '<=', $time->lastOfMonth()->toDateString())
                        ->whereDate('pjm.end_at', '>=', $time->firstOfMonth()->toDateString())
                        ->where('pjm.type', ProjectMember::TYPE_DEV)
                        ->where('pjm.status', ProjectMember::STATUS_APPROVED)
                        ->select($employeeTable . '.id', $employeeTable . '.employee_card_id', 'pjm.employee_id', $employeeTable . '.name', $employeeTable . '.email', $employeeTable.'.nickname', 'pjm.start_at', 'pjm.end_at')
                        ->orderBy('pjm.start_at', 'asc')
                        ->get();
    }
    
    /**
     * get members of project
     * @param object $project
     * @param mixed $time
     * @return collection
     */
    public static function getStaffsOfProject($project, $time) 
    {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        $employeeTable = Employee::getTableName();
        $memberTable = ProjectMember::getTableName();
        $rangeTime = MeView::getBaselineRangeTime($time);

        $collection = Employee::join($memberTable . " as pjm", $employeeTable . ".id", '=', "pjm.employee_id")
            ->where('pjm.project_id', $project->id)
            ->whereDate('pjm.start_at', '<=', $rangeTime['end']->toDateString())
            ->whereDate('pjm.end_at', '>=', $rangeTime['start']->toDateString())
            ->whereNotIn('pjm.type', [ProjectMember::TYPE_PQA, ProjectMember::TYPE_COO])
            ->where(function ($query) use ($employeeTable, $time) {
                $query->whereNull($employeeTable.'.leave_date')
                    ->orWhereDate($employeeTable.'.leave_date', '>=', $time->startOfMonth()->toDateString());
            });
        //join timekeepingsheet
        $prevTime = clone $time;
        $prevTime->subMonthNoOverflow();
        $collection->leftJoin(
            DB::raw('(SELECT tkavg.employee_id, SUM(tkavg.total_number_late_in) as late_time '
                    . 'FROM ' . TimekeepingAggregate::getTableName() . ' as tkavg '
                    . 'INNER JOIN ' . TimekeepingTable::getTableName() . ' as tktbl ON tkavg.timekeeping_table_id = tktbl.id '
                    . 'WHERE tktbl.month = ' . $prevTime->month . ' AND tktbl.year = ' . $prevTime->year . ' '
                    . 'AND tktbl.deleted_at IS NULL AND tkavg.deleted_at IS NULL '
                    . 'GROUP BY tkavg.employee_id) as tkp'),
            $employeeTable . '.id',
            '=',
            'tkp.employee_id'
        )
        ->where('pjm.status', ProjectMember::STATUS_APPROVED)
        ->select(
            $employeeTable . '.id',
            $employeeTable . '.employee_card_id',
            'pjm.employee_id',
            'pjm.effort',
            $employeeTable . '.name',
            $employeeTable . '.email',
            $employeeTable.'.nickname',
            'pjm.start_at',
            'pjm.end_at',
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, "|", pjm.start_at)) SEPARATOR ",") AS arr_start_at'),
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, "|", pjm.end_at)) SEPARATOR ",") AS arr_end_at'),
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, "|", pjm.effort)) SEPARATOR ",") AS arr_effort'),
            'tkp.late_time'
        )
        ->groupBy($employeeTable.'.id')
        ->orderBy('pjm.start_at', 'asc');

        return [
            'members' => $collection->get(),
            'range_time' => $rangeTime
        ];
    }
    
    /**
     * get member PQA in all projects by month of year
     * @param type $time
     * @return type
     */
    public static function getMembersOfTeam($teamId, $time, $excerptEmployees = []) {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        $employeeTable = Employee::getTableName();
        $memberTable = ProjectMember::getTableName();
        $projTable = Project::getTableName();
        $projBaselineTbl = ProjPointBaseline::getTableName();
        $teamMemberTbl = TeamMember::getTableName();
        $lateTime = clone $time;
        $lateTime->subMonthNoOverflow();
        //get config proj baseline date
        $dayChoose = self::getBaselineDate($time);
        //two month previous
        $timeTwoMonthsPrevious = Carbon::now()->subMonth(2)->startOfMonth()->toDateTimeString();
        //get range time
        $rangeTime = MeView::getBaselineRangeTime($time);
        
        $excerptEmpQuery = '';
        if ($excerptEmployees) {
            $excerptEmpQuery .= 'AND emp.id NOT IN (' . implode(',', $excerptEmployees) . ')'; 
        }
        $queryOrderBy = 'ORDER BY pjm.start_at DESC';
        $members = DB::select(
                'SELECT emp.id as employee_id, emp.employee_card_id, emp.name, emp.email, emp.nickname, emp.employee_code, '
                    . 'tmb.team_id, tkp.late_time, '
                    . 'GROUP_CONCAT(DISTINCT(proj.id) '. $queryOrderBy .' SEPARATOR ",") AS arr_proj_id, '
                    . 'GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, "|", DATE(pjm.start_at))) '. $queryOrderBy .' SEPARATOR ",") '
                        . 'AS arr_start_at, '
                    . 'GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, "|", DATE(pjm.end_at))) '. $queryOrderBy .' SEPARATOR ",") '
                        . 'AS arr_end_at, '
                    . 'GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, "|", pjm.effort)) '. $queryOrderBy .' SEPARATOR ",") AS arr_effort, '
                    . 'GROUP_CONCAT(DISTINCT(CONCAT(proj.id, "|", IFNULL(projbl.point_total, 0))) '. $queryOrderBy .' SEPARATOR ",") '
                        . 'AS arr_point_total, '
                    . 'GROUP_CONCAT(DISTINCT(CONCAT(proj.id, "|", proj.type)) '. $queryOrderBy .' SEPARATOR ",") AS arr_proj_type, '
                    . 'GROUP_CONCAT(DISTINCT(proj.name) SEPARATOR ",") AS arr_proj_name '
                . 'FROM ' . $employeeTable . ' AS emp '
                . 'INNER JOIN ' . $teamMemberTbl . ' AS tmb ON emp.id = tmb.employee_id '
                    . 'AND tmb.team_id = ? '
                . 'INNER JOIN ' . $memberTable . ' AS pjm ON emp.id = pjm.employee_id '
                    . 'AND pjm.start_at <= "' . $rangeTime['end']->toDateString() . '" '
                    . 'AND pjm.end_at >= "' . $rangeTime['start']->toDateString() . '" '
                    . 'AND pjm.status = ' . ProjectMember::STATUS_APPROVED . ' '
                    . 'AND pjm.is_disabled <> ' . ProjectMember::STATUS_DISABLED . ' '
                . 'INNER JOIN ' . $projTable . ' AS proj ON pjm.project_id = proj.id '
                    . 'AND proj.deleted_at IS NULL '
                    . 'AND proj.status = ' . Project::STATUS_APPROVED . ' '
                    . 'AND (proj.state = ' . Project::STATE_PROCESSING . ' '
                        . 'OR (proj.end_at >= "'. $timeTwoMonthsPrevious .'" AND proj.state = '. Project::STATE_CLOSED .'))'
                . 'LEFT JOIN '
                    . '(SELECT bl2.project_id, bl2.point_total FROM '
                        . '(SELECT project_id, point_total, MAX(created_at) as max_created_at FROM '. $projBaselineTbl .' '
                            . 'WHERE created_at >= "' . $time->startOfMonth()->toDateTimeString() . '" '
                            . 'AND created_at <= "' . $dayChoose->toDateTimeString() . '" '
                            . 'GROUP BY project_id '
                        . ') AS bl1 '
                        . 'LEFT JOIN '. $projBaselineTbl .' as bl2 ON bl1.project_id = bl2.project_id '
                            . 'AND bl1.max_created_at = bl2.created_at '
                    . ') AS projbl ON pjm.project_id = projbl.project_id '
                //join timesheet table get late time
                . 'LEFT JOIN (SELECT tkavg.employee_id, SUM(tkavg.total_number_late_in) AS late_time '
                    . 'FROM ' . TimekeepingAggregate::getTableName() . ' as tkavg '
                    . 'INNER JOIN ' . TimekeepingTable::getTableName() . ' as tktbl ON tkavg.timekeeping_table_id = tktbl.id '
                    . 'WHERE tktbl.month = ' . $lateTime->month . ' AND tktbl.year = ' . $lateTime->year . ' '
                    . 'AND tktbl.deleted_at IS NULL AND tkavg.deleted_at IS NULL '
                    . 'GROUP BY tkavg.employee_id) AS tkp '
                    . 'ON emp.id = tkp.employee_id '
                . 'WHERE emp.deleted_at IS NULL '
                . 'AND (emp.leave_date IS NULL '
                    . 'OR DATE(emp.leave_date) >= "'. $time->startOfMonth()->toDateString() .'") '
                . $excerptEmpQuery . ' '
                . 'GROUP BY pjm.employee_id, tmb.team_id '
                . 'ORDER BY pjm.employee_id ASC, pjm.start_at ASC'
            , [$teamId]);

        return [
            'members' => $members,
            'range_time' => $rangeTime
        ];
    }
    
    /**
     * create or find item
     * @param type $member
     * @param type $project_id
     * @param type $time
     * @param type $curr_user
     * @param type $project_point
     * @return type
     */
    public static function createOrFindItem (
        $member,
        $project,
        $time,
        $currUser,
        $projectPoint,
        $existsMonth,
        $lateTime = true
    )
    {
        $workDates = [];
        $arrStartAt = explode(',', $member->arr_start_at);
        $arrEndAt = explode(',', $member->arr_end_at);
        $arrEffort = explode(',', $member->arr_effort);
        $length = count($arrStartAt);
        $projectId = $project->id;
        
        for ($i = 0; $i < $length; $i++) {
            array_push($workDates, [
                explode('|', $arrStartAt[$i])[1],
                explode('|', $arrEndAt[$i])[1],
                explode('|', $arrEffort[$i])[1]
            ]);
        }
        $item = self::checkExists($member->employee_id, $projectId, $time);
        $updateData = [
            'proj_point' => $projectPoint,
            'proj_index' => self::factorOfProjType($projectId, $project->type),
            'effort' => self::calMemberDaysInMonth($workDates, $time),
        ];
        if (!$item) {
            $evalData = [
                'employee_id' => $member->employee_id,
                'project_id' => $projectId,
                'eval_time' => Carbon::parse($time)->toDateString(),
                'proj_point' => $projectPoint,
                'avg_point' => static::defaultAvgPoint(),
                'manager_id' => $currUser->id,
                'assignee' => $currUser->id
            ];
            $item = MeEvaluation::create(array_merge($evalData, $updateData));
        } else {
            $item->update($updateData);
        }
        $item->work_dates = $workDates;
        //insert or update activities comment
        MeComment::insertActivities($item);
        if ($lateTime) {
            //check time before config me late month
            if (Carbon::parse($time)->diff(Carbon::parse(config('project.me_late_month')))->invert) {
                $point = MeAttribute::POINT_MAX_REG;
                //create or update late time point
                if ($existsMonth) {
                    $point = MeAttribute::POINT_MAX_REG - $member->late_time;
                    if ($point < 0) {
                        $point = 0;
                    }
                }
            } else {
                $point = MeAttribute::NA;
            }
            $item->meAttributes()->syncWithoutDetaching([
                MeAttribute::getMeTimeAttrId() => ['point' => $point]
            ]);
        }
        return $item;
    }

    public static function defaultAvgPoint()
    {
        return 1;
    }

    /**
     * create or find item team
     * @param type $member
     * @param type $project_id
     * @param type $time
     * @param type $currUser
     * @param type $project_point
     * @return type
     */
    public static function createOrFindItemTeam ($member, $time, $currUser, $existsMonth) {
        $limit = 15;
        $arrStartAt = array_slice(explode(',', $member->arr_start_at), 0, $limit);
        $arrEndAt = array_slice(explode(',', $member->arr_end_at), 0, $limit);
        $arrEffort = array_slice(explode(',', $member->arr_effort), 0, $limit);
        $arrProjId = array_slice(explode(',', $member->arr_proj_id), 0, $limit);
        $arrProjType = array_slice(explode(',', $member->arr_proj_type), 0, $limit);
        $arrPointTotal = array_slice(explode(',', $member->arr_point_total), 0, $limit);

        $workDates = [];
        $projPoint = 0;
        $projIndex = null;
        $uniqueLength = 1;
        if ($arrStartAt) {
            for ($i = 0; $i < count($arrStartAt); $i++) {
                array_push($workDates, [
                    explode('|', $arrStartAt[$i])[1],
                    explode('|', $arrEndAt[$i])[1],
                    explode('|', $arrEffort[$i])[1]
                ]);
            }
        }
        if ($arrProjId) {
            $uniqueLength = count($arrProjId);
            for ($i = 0; $i < $uniqueLength; $i++) {
                $type = explode('|', $arrProjType[$i])[1];
                $pointIndex = self::FT_OSDC;
                if ($type == self::PJ_BASE) {
                    $pointIndex = self::FT_BASE;
                }
                $pointItem = explode('|', $arrPointTotal[$i])[1];
                if ($type == Project::TYPE_TRAINING) {
                    $pointItem = Project::POINT_PROJECT_TYPE_TRANING;
                } else if ($type == Project::TYPE_RD) {
                    $pointItem = Project::POINT_PROJECT_TYPE_RD;
                } else if ($type == Project::TYPE_ONSITE){
                    $pointItem = Project::POINT_PROJECT_TYPE_ONSITE;
                }
                $projPoint += $pointItem * $pointIndex;
            }
            $projPoint = number_format($projPoint / $uniqueLength, 1, '.', ',');
        }
        $item = self::checkExistsTeam($member->employee_id, $member->team_id, $time);
        if (!$item) {
            $evalData = [
                'employee_id' => $member->employee_id,
                'eval_time' => Carbon::parse($time)->toDateString(),
                'team_id' => $member->team_id,
                'avg_point' => 1,
                'manager_id' => $currUser->id,
                'assignee' => $currUser->id
            ];
            $item = MeEvaluation::create($evalData);
        }
        $item->proj_point = $projPoint;
        $item->proj_index = $projIndex;
        $item->effort = $item->memberDaysInMonth($workDates);
        $item->save();
        //insert or update activities comment
        MeComment::insertActivities($item);
        //check time before config me late month
        if (Carbon::parse($time)->diff(Carbon::parse(config('project.me_late_month')))->invert) {
            $point = MeAttribute::POINT_MAX_REG;
            //create or update late time point
            if ($existsMonth) {
                $point = MeAttribute::POINT_MAX_REG - $member->late_time;
                if ($point < 0) {
                    $point = 0;
                }
            }
        } else {
            $point = MeAttribute::NA;
        }
        $item->meAttributes()->syncWithoutDetaching([
            MeAttribute::getMeTimeAttrId() => ['point' => $point]
        ]);
        return $item;
    }

    /*
     * delete invalid items
     */
    public static function delInvalidItems($objId, $time, $validIds = [], $type = 'project')
    {
        $items = self::where('eval_time', $time)
                ->whereNotIn('id', $validIds);
        if ($type == 'project') {
            $items->where('project_id', $objId);
        } else {
            $items->where('team_id', $objId);
        }
        return $items->delete();
    }

    public static function collectByLeader($urlFilter = null)
    {
        $scope = Permission::getInstance();
        $leaderId = $scope->getEmployee()->id;
        $projectMemberTbl = ProjectMember::getTableName();
        $projectTbl = Project::getTableName();
        $evalTbl = self::getTableName();
        $empTbl = Employee::getTableName();
        $teamTbl = Team::getTableName();
        $teamMbTbl = TeamMember::getTableName();

        $collection = self::leftJoin($projectMemberTbl.' as pjm', function ($join) use ($evalTbl) {
                $join->on($evalTbl.'.project_id', '=', 'pjm.project_id')
                        ->on($evalTbl.'.employee_id', '=', 'pjm.employee_id')
                        ->where('pjm.status', '=', ProjectMember::STATUS_APPROVED);
            })
            ->where(function ($query) use ($evalTbl){
                $query->where($evalTbl.'.employee_id', DB::raw('pjm.employee_id'))
                        ->whereNotNull($evalTbl.'.project_id')
                        ->orWhereNull($evalTbl.'.project_id');
            })
            ->leftJoin($projectTbl.' as proj', function ($join) use ($evalTbl) {
                $join->on($evalTbl.'.project_id', '=', 'proj.id')
                        ->where('proj.status', '=', Project::STATUS_APPROVED);
            })
            ->whereNull('proj.deleted_at')
            ->leftJoin($teamTbl.' as team', $evalTbl.'.team_id', '=', 'team.id')
            ->join($empTbl.' as emp', $evalTbl.'.employee_id', '=', 'emp.id')
            ->where($evalTbl.'.status', '!=', self::STT_REWARD);
        
        $statuses = [self::STT_SUBMITED, self::STT_APPROVED, self::STT_CLOSED];

        if ($scope->isScopeCompany(null, 'project::project.eval.list_by_leader')) {
            $collection->where($evalTbl.'.status', '!=', self::STT_DRAFT);
        } else if ($teamIds = $scope->isScopeTeam(null, 'project::project.eval.list_by_leader')) {
            $projTeamTbl = TeamProject::getTableName();
            //me team
            $teamIds = is_array($teamIds) ? $teamIds : [];
            $collection->leftJoin($projTeamTbl . ' as teamproj', 'proj.id', '=', 'teamproj.project_id')
                    ->leftJoin($teamMbTbl . ' as tmb', 'tmb.employee_id', '=', $evalTbl . '.employee_id')
                    ->where(function ($query) use ($evalTbl, $teamIds, $teamMbTbl) {
                        $query->whereIn('proj.leader_id', function ($subQuery) use ($teamIds, $teamMbTbl) {
                            $subQuery->select('employee_id')
                                ->from($teamMbTbl)
                                ->whereIn('team_id', $teamIds)
                                ->whereIn('role_id', [Team::ROLE_SUB_LEADER, Team::ROLE_TEAM_LEADER]);
                        })
                        ->orWhereIn('tmb.team_id', $teamIds)
                        ->orWhereIn($evalTbl . '.team_id', $teamIds);
                })
                ->where($evalTbl.'.status', '!=', self::STT_DRAFT);
        } else if ($scope->isScopeSelf(null, 'project::project.eval.list_by_leader')) {
            $eval_histories_ids = MeHistory::where('action_type', MeHistory::AC_SUBMIT)
                    ->where('type_id', $leaderId)
                    ->groupBy('eval_id')
                    ->lists('eval_id')
                    ->toArray();

            $collection->where(function ($query) use ($evalTbl, $eval_histories_ids, $leaderId, $statuses) {
                $query->where(function ($query2) use ($evalTbl, $leaderId, $statuses) {
                    $query2->where($evalTbl.'.assignee', $leaderId)
                            ->whereIn($evalTbl.'.status', $statuses);
                    })
                    ->orWhereIn($evalTbl.'.id', $eval_histories_ids);
            });
        }
        
        // filter team project
        $teamFilter = Form::getFilterData('team_filter', 'team_id', $urlFilter);
        if ($teamFilter) {
            $teamFilter = Team::teamChildIds($teamFilter);
            $teamProjTbl = TeamProject::getTableName();
            $collection->join($teamProjTbl.' as tpj', function ($join) use ($evalTbl, $teamFilter) {
                    $join->on($evalTbl.'.project_id', '=', 'tpj.project_id')
                        ->whereIn('tpj.team_id', $teamFilter);
                });
        }
        //filter team member
        $teamMember = Form::getFilterData('team_filter', 'team_member', $urlFilter);
        if ($teamMember) {
            $teamMember = Team::teamChildIds($teamMember);
            $collection->join($teamMbTbl . ' as teammb', function ($join) use ($evalTbl, $teamMember) {
                $join->on($evalTbl . '.employee_id', '=', 'teammb.employee_id')
                        ->whereIn('teammb.team_id', $teamMember);
            });
        }
        return $collection;
    }

    /**
     * get items by leader id
     * @param type $leader_id
     * @return collection
     */
    public static function getByLeader($collection, $data = [], $urlFilter = null)
    {
        if (!$urlFilter) {
            $urlFilter = route('project::project.eval.list_by_leader') . '/';
        }
        $pager = Config::getPagerData($urlFilter);

        $evalTbl = self::getTableName();
        $meAttrTbl = MeAttribute::getTableName();
        $mePointTbl = 'me_points';
        $commentTbl = MeComment::getTableName();
        $projMbTbl = ProjectMember::getTableName();
        $historyTbl = MeHistory::getTableName();
        $sepMonth = config("project.me_sep_month");
        if ($sepMonth) {
            $collection->where(DB::raw('DATE_FORMAT('. $evalTbl .'.eval_time, "%Y-%m")'), '<=', $sepMonth);
        }

        $collection->leftJoin(DB::raw('(SELECT pt.* FROM ' . $meAttrTbl . ' as attr '
                                . 'LEFT JOIN ' . $mePointTbl . ' as pt ON attr.id = pt.attr_id) AS point'), 
                            $evalTbl.'.id', '=', 'point.eval_id')
                ->leftJoin($commentTbl . ' as cmt', $evalTbl.'.id', '=', 'cmt.eval_id')
                ->leftJoin($projMbTbl . ' as tpjm', function ($join) use ($evalTbl) {
                        $join->on($evalTbl.'.employee_id', '=', 'tpjm.employee_id')
                                ->on('tpjm.end_at', '>=', DB::raw($evalTbl.'.eval_time'))
                                ->on('tpjm.start_at', '<=', DB::raw('LAST_DAY('. $evalTbl .'.eval_time)'))
                                ->whereNotNull($evalTbl.'.team_id')
                                ->where('tpjm.status', '=', ProjectMember::STATUS_APPROVED);
                })
                //check action feedback
                ->leftJoin($historyTbl.' as htfb', function ($join) use ($evalTbl) {
                    $join->on($evalTbl.'.id', '=', 'htfb.eval_id')
                            ->on($evalTbl.'.version', '=', 'htfb.version')
                            ->where('htfb.employee_id', '=', auth()->id())
                            ->whereIn('action_type', [MeHistory::AC_NOTE, MeHistory::AC_COMMENT]);
                });
        
        
        //filter month
        $filterMonth = $data['month'];
        /*if (!isset($data['time'])) {
            if (!$filterMonth) {
                $filterMonth = Carbon::now();
                if ($filterMonth->day <= MeView::SEP_DATE) {
                    $filterMonth->subMonthNoOverflow();
                }
            }
            if ($filterMonth != self::VAL_ALL) {
                $filterMonth = Carbon::parse($filterMonth);
            }
        } else {
            $filterMonth = Carbon::createFromFormat('Y-m-d H:i:s', $data['time']);
        }*/
        if ($filterMonth) {
            $collection->where(DB::raw('DATE_FORMAT(' . $evalTbl . '.eval_time, "%Y-%m")'), $filterMonth);
        }

        // if isset param project_id
        if (isset($data['project_id']) && ($projId = $data['project_id'])) {
            if (is_numeric($projId)) {
                $collection->where($evalTbl.'.project_id', $data['project_id']);
            } else {
                $arrIds = explode('_', $projId);
                if (count($arrIds) == 2) {
                    $collection->where($evalTbl.'.team_id', $arrIds[1]);
                }
            }
        }
        // if isest param time
        if (isset($data['time']) && $data['time']) {
            Form::forgetFilter($urlFilter);
        }
        // filter avg_point
        $avgRange = Form::getFilterData('excerpt', 'avg_point', $urlFilter);
        $arrAvg = explode('-', $avgRange); 
        if (count($arrAvg) > 1) {
            $collection->where($evalTbl.'.avg_point', '>=', $arrAvg[0])
                            ->where($evalTbl.'.avg_point', '<', $arrAvg[1]);
        }
        //filter project type
        $projectType = Form::getFilterData('excerpt', 'proj_type', $urlFilter);
        if ($projectType) {
            if ($projectType == '_team_') {
                $collection->whereNull($evalTbl.'.project_id');
            } else {
                $collection->where('proj.type', $projectType);
            }
        }
        
        $collection->select($evalTbl.'.*',
                    'proj.project_code_auto', 'proj.name as project_name', 'proj.type as project_type', 
                    'emp.email', 'emp.employee_code', 
                    'team.name as team_name',
                    DB::raw('DATE_FORMAT('.$evalTbl.'.eval_time, "%Y-%m") as eval_month'),
                    DB::raw('GROUP_CONCAT(DISTINCT(htfb.id)) as htfb_ids'),
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(point.attr_id, "|", point.point)) SEPARATOR ",") as point_attrs'),
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(IFNULL(cmt.id, -1), "|", IFNULL(cmt.attr_id, -1), "|", IFNULL(cmt.type, -1))) SEPARATOR ",") as cmt_attrs'),
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, "|", pjm.start_at, "|", pjm.end_at, "|", pjm.effort)) SEPARATOR ",") as pjm_attrs'),
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(tpjm.id, "|", DATE(tpjm.start_at), "|", DATE(tpjm.end_at), "|", tpjm.effort)) SEPARATOR ",") as tpjm_attrs'))
                ->groupBy($evalTbl.'.id');
        if (Form::getFilterPagerData('order', $urlFilter)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('eval_time', 'desc')
                    ->orderBy('project_id', 'desc');
        }
        self::filterGrid($collection, [], $urlFilter);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * get filter project assignne
     * @param type $employee_id
     * @return collection
     */
    public static function filterAttrByAssignee($attr, $status, $employee_id = null) {
        $scope = Permission::getInstance();
        if (!$employee_id) {
            $employee = $scope->getEmployee();
            $employee_id = $employee->id;
        } else {
            $employee = Employee::find($employee_id);
        }
        $projTbl = Project::getTableName();
        $evalTbl = self::getTableName();
        $collection =  Project::join($evalTbl.' as evl', $projTbl.'.id', '=', 'evl.project_id')
                ->where($projTbl. '.status', Project::STATUS_APPROVED)
                ->whereNull($projTbl.'.deleted_at');
        
        if ($scope->isScopeSelf(null, 'project::project.eval.list_by_leader')) {
            $eval_histories_ids = MeHistory::where('action_type', MeHistory::AC_SUBMIT)
                    ->where('type_id', $employee_id)
                    ->groupBy('eval_id')
                    ->lists('eval_id')
                    ->toArray();

            $collection = $collection->where(function ($query) use ($eval_histories_ids, $employee_id, $status) {
                $query->where(function ($query2) use ($employee_id, $status) {
                    $query2->where('evl.assignee', $employee_id)
                            ->whereIn('evl.status', $status);
                    })
                    ->orWhereIn('evl.id', $eval_histories_ids);
            });
        }
        
        if ($scope->isScopeTeam(null, 'project::project.eval.list_by_leader')) {
            $teamMemberTbl = TeamMember::getTableName();
            $projTeamTbl = TeamProject::getTableName();
            $project_ids = TeamProject::join($teamMemberTbl.' as tmb', $projTeamTbl.'.team_id', '=', 'tmb.team_id')
                    ->where('tmb.employee_id', $employee_id)
                    ->groupBy($projTeamTbl.'.project_id')
                    ->lists($projTeamTbl.'.project_id')
                    ->toArray();
            $collection->whereIn('evl.project_id', $project_ids)
                    ->whereIn('evl.status', $status);
        }
        
        if ($scope->isScopeCompany(null, 'project::project.eval.list_by_leader')) {
            $collection->whereIn('evl.status', $status);
        }
                
        $collection = $collection->groupBy('evl.'.$attr)
                ->select('evl.'.$attr, $projTbl.'.name');
        if ($attr == 'eval_time') {
            $collection = $collection->orderBy('evl.eval_time', 'desc');
        } else {
            $collection = $collection->orderBy($projTbl.'.created_at', 'desc');
        }
        return $collection->get();
    }
    
    /**
     * count and caculator percent evaluation by avg point
     * @param type $leader_id
     * @return array
     */
    public static function countByAvgPoint($collection, $urlFilter = null)
    {
        $evalTbl = self::getTableName();
        $data = Session::get(MeView::KEY_REVIEW_FILTER);
        $sepMonth = config("project.me_sep_month");
        if ($sepMonth) {
            $collection->where(DB::raw('DATE_FORMAT('. $evalTbl .'.eval_time, "%Y-%m")'), '<=', $sepMonth);
        }

        $filterMonth = $data ? $data['month'] : null;
        /*if (!$filterMonth && !isset($data['time'])) {
            $collection = $collection->where($evalTbl.'.eval_time', Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString());
        } else if ($filterMonth != '_all_') { */
        if ($filterMonth) {//filter month
            $collection->where(DB::raw('DATE_FORMAT('. $evalTbl .'.eval_time, "%Y-%m")'), $filterMonth);
        }
        //filter avg point
        $avgRange = Form::getFilterData('excerpt', 'avg_point', $urlFilter);
        $arrAvg = explode('-', $avgRange);
        if (count($arrAvg) > 1) {
            $collection->where($evalTbl.'.avg_point', '>=', $arrAvg[0])
                            ->where($evalTbl.'.avg_point', '<', $arrAvg[1]);
        }
        //filter project type
        $projectType = Form::getFilterData('excerpt', 'proj_type', $urlFilter);
        if ($projectType) {
            if (!is_numeric($projectType)) {
                $collection->whereNull($evalTbl.'.project_id');
            } else {
                $collection->where('proj.type', $projectType);
            }
        }
        //filter project
        $projId = $data ? $data['project_id'] : null;
        if ($projId) {
            if (is_numeric($projId)) {
                $collection->where($evalTbl.'.project_id', $data['project_id']);
            } else {
                $arrIds = explode('_', $projId);
                if (count($arrIds) == 2) {
                    $collection->where($evalTbl.'.team_id', $arrIds[1]);
                }
            }
        }
        
        self::filterGrid($collection, [], $urlFilter);
        $collection->select($evalTbl.'.id', $evalTbl.'.avg_point')
                                ->groupBy($evalTbl.'.id');
        $collection = DB::table(DB::raw("({$collection->toSql()}) as $evalTbl"))		        
                        ->mergeBindings($collection->getQuery());
        //count range point
        $collection->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN avg_point >= '. self::TH_EXCELLENT .' THEN 1 ELSE 0 END) as count_excellent'),
                DB::raw('SUM(CASE WHEN avg_point >= '. self::TH_GOOD .' AND avg_point < '. self::TH_EXCELLENT .' THEN 1 ELSE 0 END) as count_good'),
                DB::raw('SUM(CASE WHEN avg_point >= '. self::TH_FAIR .' AND avg_point < '. self::TH_GOOD .' THEN 1 ELSE 0 END) as count_fair'),
                DB::raw('SUM(CASE WHEN avg_point >= '. self::TH_SATIS .' AND avg_point < '. self::TH_FAIR .' THEN 1 ELSE 0 END) as count_satis'),
                DB::raw('SUM(CASE WHEN avg_point >= '. self::TH_UNSATIS .' AND avg_point < '. self::TH_SATIS .' THEN 1 ELSE 0 END) as count_unsatis')
            );
        $summary = $collection->first();
        
        $result = null;
        if ($summary) {
            $result = [
                'total' => [
                    'num' => $summary->total ? $summary->total : 0
                ],
                'excellent' => [
                    'num' => $summary->count_excellent ? $summary->count_excellent : 0,
                    'percent' => $summary->total ? round($summary->count_excellent / $summary->total * 100, 2) : 0
                ],
                'good' => [
                    'num' => $summary->count_good ? $summary->count_good : 0,
                    'percent' => $summary->total ? round($summary->count_good / $summary->total * 100, 2) : 0
                ],
                'fair' => [
                    'num' => $summary->count_fair ? $summary->count_fair : 0,
                    'percent' => $summary->total ? round($summary->count_fair / $summary->total * 100, 2) : 0
                ],
                'satis' => [
                    'num' => $summary->count_satis ? $summary->count_satis : 0,
                    'percent' => $summary->total ? round($summary->count_satis / $summary->total * 100, 2) : 0
                ],
                'unsatis' => [
                    'num' => $summary->count_unsatis ? $summary->count_unsatis : 0,
                    'percent' => $summary->total ? round($summary->count_unsatis / $summary->total * 100, 2) : 0
                ]
            ];
        }
        return $result;
    }

    /*
     * get project not evaluation
     */
    public static function getProjectNotEval($data = [], $urlFilter = null)
    {
        $scopeRoute = 'project::project.eval.list_by_leader';
        if ($urlFilter === null) {
            $urlFilter = route($scopeRoute) . '/';
        }
        $scope = Permission::getInstance();
        $leaderId = $scope->getEmployee()->id;

        $projTbl = Project::getTableName();
        $teamProjTbl = TeamProject::getTableName();
        $empTbl = Employee::getTableName();
        //filter month
        if (!isset($data['month']) || !$data['month']) {
            return collect();
        }
        $filterMonth = MeView::parseDateFromFormat($data['month'], 'Y-m');
        $rangeTime = MeView::getBaselineRangeTime($filterMonth);
        $timeTwoMonthsPrevious = clone $filterMonth;
        $timeTwoMonthsPrevious->subMonth(2);
        $collection = Project::select(
            $projTbl.'.id',
            $projTbl.'.name',
            $projTbl.'.project_code_auto',
            'pm.id as employee_id',
            'pm.email',
            DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") as team_names'),
            DB::raw('GROUP_CONCAT(DISTINCT(pjm.employee_id)) as emp_ids'),
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(emp.name, " - ", emp.email)) SEPARATOR ", ") as employees')
        )
                ->join(ProjectMember::getTableName() . ' as pjm', function ($join) use ($projTbl, $rangeTime) {
                    $join->on($projTbl . '.id', '=', 'pjm.project_id')
                            ->where('pjm.status', '=', ProjectMember::STATUS_APPROVED)
                            ->whereNotIn('pjm.type', [ProjectMember::TYPE_PQA, ProjectMember::TYPE_COO])
                            ->where('pjm.start_at', '<=', $rangeTime['end']->toDateString())
                            ->where('pjm.end_at', '>=', $rangeTime['start']->toDateString());
                })
                ->leftJoin($empTbl . ' as pm', function ($join) use ($projTbl) {
                    $join->on($projTbl.'.manager_id', '=', 'pm.id');
                })
                ->leftJoin(self::getTableName() . ' as me', function ($join) use ($filterMonth) {
                    $join->on('pjm.project_id', '=', 'me.project_id')
                            ->on('pjm.employee_id', '=', 'me.employee_id')
                            ->where('me.eval_time', '=', $filterMonth->startOfMonth()->toDateTimeString())
                            ->where('me.status', '!=', self::STT_DRAFT);
                })
                ->leftJoin($empTbl . ' as emp', function ($join) use ($filterMonth) {
                    $join->on('pjm.employee_id', '=', 'emp.id')
                            ->where(function ($query) use ($filterMonth) {
                                $query->whereNull('emp.leave_date')
                                        ->orWhere('emp.leave_date', '>=', $filterMonth->startOfMonth()->toDateString());
                            })
                            ->whereNull('emp.deleted_at');
                })
                ->leftJoin(TeamProject::getTableName() . ' as teamproj', $projTbl . '.id', '=', 'teamproj.project_id')
                ->leftJoin(Team::getTableName() . ' as team', 'teamproj.team_id', '=', 'team.id')
                ->where($projTbl . '.status', Project::STATUS_APPROVED)
                ->where(function ($query) use ($timeTwoMonthsPrevious, $projTbl) {
                    $query->where($projTbl.'.state', Project::STATE_CLOSED)
                            ->where($projTbl.'.end_at', '>=', $timeTwoMonthsPrevious->startOfMonth()->toDateString())
                            ->where($projTbl.'.start_at', '<=', $timeTwoMonthsPrevious->endOfMonth()->toDateString())
                            ->orWhere($projTbl.'.state', Project::STATE_PROCESSING);
                })
                ->whereNull('me.id')
                ->groupBy($projTbl.'.id');

        //fillter project
        $projectId = $data['project_id'];
        if ($projectId && is_numeric($projectId)) {
             $collection->where($projTbl.'.id', $projectId);
        }
        // filter team project
        if ($teamFilter = Form::getFilterData('team_filter', 'team_id', $urlFilter)) {
            $collection->where('teamproj.team_id', $teamFilter);
        }
        //filter team member
        if ($teamMemberId = Form::getFilterData('team_filter', 'team_member', $urlFilter)) {
            $collection->leftJoin(TeamMember::getTableName() . ' as tmb', 'pjm.employee_id', '=', 'tmb.employee_id')
                    ->where('tmb.team_id', $teamMemberId);
        }

        if ($scope->isScopeCompany(null, $scopeRoute)) {
            //do nothing
        } else {
            $collection->where($projTbl.'.leader_id', $leaderId);
        }

        return $collection->havingRaw('employees IS NOT NULL')
                ->get();
    }

    /**
     * collect evaluation by staff id
     * @param type $staft_id
     * @param type $data
     * @return collection
     */
    public static function collectByStaft($staft_id = null, $data = []) {
        if (!$staft_id) {
            $staft_id = auth()->id();
        }
        
        $projectMemberTbl = ProjectMember::getTableName();
        $projectTbl = Project::getTableName();
        $evalTbl = self::getTableName();
        $teamTbl = Team::getTableName();
        
        $collection = self::leftJoin($projectMemberTbl.' as pjm', function ($join) use ($evalTbl) {
                        $join->on($evalTbl.'.project_id', '=', 'pjm.project_id')
                                ->where('pjm.status', '=', ProjectMember::STATUS_APPROVED);
                    })
                    ->where(function ($query) use ($evalTbl){
                        $query->where($evalTbl.'.employee_id', DB::raw('pjm.employee_id'))
                                ->whereNotNull($evalTbl.'.project_id')
                                ->orWhereNull($evalTbl.'.project_id');
                    })
                    ->leftJoin($projectTbl.' as proj', function ($join) use ($evalTbl) {
                        $join->on($evalTbl.'.project_id', '=', 'proj.id')
                                ->where('proj.status', '=', Project::STATUS_APPROVED);
                    })
                    ->whereNull('proj.deleted_at')
                    ->leftJoin($teamTbl.' as team', $evalTbl.'.team_id', '=', 'team.id');
        
        $eval_histories_ids = MeHistory::where('action_type', MeHistory::AC_APPROVED)
                ->where('type_id', $staft_id)
                ->groupBy('eval_id')
                ->lists('eval_id')
                ->toArray();
        $collection->where(function ($query) use ($evalTbl, $eval_histories_ids, $staft_id) {
            $query->where(function ($query2) use ($evalTbl, $staft_id) {
                $query2->where($evalTbl.'.employee_id', $staft_id)
                        ->whereIn($evalTbl.'.status', [self::STT_APPROVED, self::STT_CLOSED]);
                })
                ->orWhereIn($evalTbl.'.id', $eval_histories_ids);
        });
        
        return $collection;
    }

    /**
     * get items by staft id
     * @param type $staft_id
     * @return collection
     */
    public static function getByStaft($staft_id = null, $data = []) {
        $pager = Config::getPagerData();
        $pager['order'] = 'eval_time';
        $pager['dir'] = 'desc';
        $evalTbl = self::getTableName();
        $commentTbl = MeComment::getTableName();
        $projMbTbl = ProjectMember::getTableName();
        $historyTbl = MeHistory::getTableName();
        $meAttrTbl = MeAttribute::getTableName();
        $mePointTbl = 'me_points';
        
        $collection = self::collectByStaft($staft_id, $data);
        $sepMonth = config("project.me_sep_month");
        if ($sepMonth) {
            $collection->where(DB::raw('DATE_FORMAT('. $evalTbl .'.eval_time, "%Y-%m")'), '<=', $sepMonth);
        }

        $collection->leftJoin(DB::raw('(SELECT pt.* FROM ' . $meAttrTbl . ' as attr '
                                . 'LEFT JOIN ' . $mePointTbl . ' as pt ON attr.id = pt.attr_id) AS point'), 
                            $evalTbl.'.id', '=', 'point.eval_id')
                ->leftJoin($commentTbl . ' as cmt', $evalTbl.'.id', '=', 'cmt.eval_id')
                ->leftJoin($projMbTbl . ' as tpjm', function ($join) use ($evalTbl) {
                        $join->on($evalTbl.'.employee_id', '=', 'tpjm.employee_id')
                                ->on('tpjm.end_at', '>=', DB::raw($evalTbl.'.eval_time'))
                                ->on('tpjm.start_at', '<=', DB::raw('LAST_DAY('. $evalTbl .'.eval_time)'))
                                ->whereNotNull($evalTbl.'.team_id')
                                ->where('tpjm.status', '=', ProjectMember::STATUS_APPROVED);
                })
                //check action feedback
                ->leftJoin($historyTbl.' as htfb', function ($join) use ($evalTbl) {
                    $join->on($evalTbl.'.id', '=', 'htfb.eval_id')
                            ->on($evalTbl.'.version', '=', 'htfb.version')
                            ->where('htfb.employee_id', '=', auth()->id())
                            ->whereIn('action_type', [MeHistory::AC_NOTE, MeHistory::AC_COMMENT]);
                });
        
        $filterProjectId = Form::getFilterData('number', $evalTbl.'.project_id');
        
        //filter month
        $filterMonth = Form::getFilterData('month', 'eval_time');
        if (!isset($data['time'])) {
            if (!$filterMonth) {
                $filterMonth = MeView::defaultFilterMonth();
            }
            if ($filterMonth != self::VAL_ALL) {
                $filterMonth = Carbon::parse($filterMonth);
            }
        } else {
            $filterMonth = Carbon::parse($data['time']);
        }
        if ($filterMonth != self::VAL_ALL) {
            $collection->where($evalTbl . '.eval_time', $filterMonth->startOfMonth()->toDateTimeString());
        }
        
        if (isset($data['project_id']) && $data['project_id']) {
            $collection->where($evalTbl.'.project_id', $data['project_id']);
        }
        if (isset($data['time']) && $data['time']) {
            Form::forgetFilter();
            $collection->where($evalTbl.'.eval_time', $data['time']);
        }
        if (isset($data['team_id']) && $data['team_id']) {
            $collection->where($evalTbl . '.team_id', $data['team_id']);
        }
        
        $collection->select($evalTbl.'.*', 'proj.start_at', 'proj.end_at', 'proj.project_code_auto', 
                    'proj.type as project_type', 'team.name as team_name', 'proj.name as proj_name',
                    DB::raw('GROUP_CONCAT(DISTINCT(htfb.id)) as htfb_ids'),
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(point.attr_id, "|", point.point)) SEPARATOR ",") as point_attrs'),
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(IFNULL(cmt.id, -1), "|", IFNULL(cmt.attr_id, -1), "|", IFNULL(cmt.type, -1))) SEPARATOR ",") as cmt_attrs'),
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, "|", pjm.start_at, "|", pjm.end_at, "|", pjm.effort)) SEPARATOR ",") as pjm_attrs'),
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(tpjm.id, "|", DATE(tpjm.start_at), "|", DATE(tpjm.end_at), "|", tpjm.effort)) SEPARATOR ",") as tpjm_attrs'))
                ->orderBy($pager['order'], $pager['dir'])
                ->orderBy($evalTbl.'.created_at', 'desc')
                ->groupBy($evalTbl.'.id');
        
        self::filterGrid($collection);
        $result['all'] = clone $collection;
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        $result['collection'] = $collection;
        $result['has_project'] = false;
        if ($filterProjectId) {
            $result['has_project'] = true;
        }
        $result['filter_month'] = $filterMonth;
        return $result;
    }

    /**
     * get PM id of project
     * @param type $project_id
     * @return int
     */
    public static function getPMIdOfProject($project_id) {
        $projectMember = ProjectMember::where('project_id', $project_id)
                ->where('status', ProjectMember::STATUS_APPROVED)
                ->where('is_disabled', '!=', ProjectMember::STATUS_DISABLED)
                ->where('type', ProjectMember::TYPE_PM)
                ->orderBy('end_at', 'desc')
                ->first();
        if ($projectMember) {
            return $projectMember->employee_id;
        }
        $project = Project::find($project_id);
        if ($project) {
            return $project->manager_id;
        }
        return null;
    }
    
    /**
     * check is PM of project
     * @param type $user_id
     * @param type $project_id
     * @return boolean
     */
    public static function isPMOfProject($user_id, $project_id) {
        $pm = ProjectMember::where('employee_id', $user_id)
                ->where('project_id', $project_id)
                ->where('type', ProjectMember::TYPE_PM)
                ->where('status', ProjectMember::STATUS_APPROVED)
                ->where('is_disabled', '!=', ProjectMember::STATUS_DISABLED)
                ->first();
        if ($pm){
            return true;
        }
        return false;
    }

    /**
     * update status
     * @param type $id
     * @param type $status
     * @return boolean
     */
    public static function updateStatus($id, $status, $leaderId = false, $isCoo = false)
    {
        $eval = self::find($id);
        if ($eval) {
            if ($status == self::STT_FEEDBACK) {
                $eval->assignee = $eval->manager_id;
                $eval->last_user_updated = auth()->id();
                if ($isCoo) {
                    $eval->is_leader_updated = self::COO_UPDATED;
                } elseif ($leaderId) {
                    $eval->is_leader_updated = self::LEADER_UPDATED;
                } else {
                    $eval->is_leader_updated = 0;
                }
            }
            if ($status == self::STT_APPROVED || $status == self::STT_CLOSED) {
                $eval->assignee = $eval->employee_id;
            }
            $eval->status = $status;
            if ($status == self::STT_APPROVED) {
                if ($eval->employee_id == $eval->manager_id || $leaderId == $eval->employee_id) {
                    $eval->status = self::STT_CLOSED;
                }
            }
            $eval->save();
            return $eval;
        }
        return false;
    }
    
    /**
     * get work dates of member in month
     * @param array $workDates (start_at, end_at, effort)
     * @param type $time
     * @return type
     */
    public function memberDaysInMonth($workDates, $time = null)
    {
        if (!$time) {
            $time = $this->eval_time;
        }
        return self::calMemberDaysInMonth($workDates, $time);
    }

    public static function calMemberDaysInMonth($workDates, $time = null)
    {
        $rangeTime = MeView::getBaselineRangeTime($time);
        $timeFirstDay = $rangeTime['start'];
        $timeLastDay = $rangeTime['end'];

        $result = 0;

        foreach ($workDates as $dates) {
            $dateStart = isset($dates[0]) ? $dates[0] : $dates['start_at'];
            $dateEnd = isset($dates[1]) ? $dates[1] : $dates['end_at'];
            $effort = isset($dates[2]) ? $dates[2] : $dates['effort'];
            $timeStart = ($dateStart instanceof Carbon) ? $dateStart : Carbon::parse($dateStart);
            $timeEnd = ($dateEnd instanceof Carbon) ? $dateEnd : Carbon::parse($dateEnd);

            if ($timeStart->lt($timeFirstDay)) {
                $timeStart = $timeFirstDay;
            }
            if ($timeEnd->gt($timeLastDay)) {
                $timeEnd = $timeLastDay;
            }
            $result += View::getMM($timeStart, $timeEnd, 2) * $effort / 100;
        }

        return round($result, 1);
    }
    
    /**
     * get work dates of employee in month
     * @return float
     */
    public function getWorkDatesInMonth() {
        $time = $this->eval_time;
        $project_id = $this->project_id;
        $employee_id = $this->employee_id;
        $result = 0;
        if (!$project_id) {
            return $result;
        }
        $rangTime = MeView::getBaselineRangeTime($time);
        $projectMembers = ProjectMember::where('project_id', $project_id)
                ->where('employee_id', $employee_id)
                ->whereDate('start_at', '<=', $rangTime['end']->toDateString())
                ->whereDate('end_at', '>=', $rangTime['start']->toDateString())
                ->where('status', ProjectMember::STATUS_APPROVED)
                ->select('start_at', 'end_at', 'effort')
                ->get()
                ->toArray();

        if ($projectMembers) {
            $this->memberDaysInMonth($projectMembers, $time);
        }
        return round($result, 1);
    }
    
    /**
     * lists months of this porject
     * @return array
     */
    public static function listProjectMonths($projId)
    {
        if (is_numeric($projId)) {
            $project = Project::find($projId, ['start_at', 'end_at']);
        } else {
            $project = $projId;
        }
        if (!$project) {
            return [];
        }

        $startAt = $project->start_at->startOfMonth();
        $endAt = $project->end_at->lastOfMonth();
        $now = Carbon::now()->endOfMonth();
        if ($endAt->gt($now)) {
            $endAt = $now;
        }
        $months = [];
        while ($startAt->lte($endAt)) {
            array_unshift($months, ['timestamp' => $startAt->format('Y-m'), 'string' => $startAt->format('Y-m')]);
            $startAt->addMonth();
        }
        return $months;
    }
    
    /**
     * check current user comment attribute
     * @param type $attr_id
     * @return type
     */
    public function currentUserComment($attr_id) {
        $curr_user_id = Permission::getInstance()->getEmployee()->id;
        $comments = $this->meComments()
                ->where('attr_id', $attr_id)
                ->where('employee_id', $curr_user_id)
                ->count();
        return $comments;
    }

    public static function hasStatus($ids, $status) {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        if (self::whereIn('id', $ids)->where('status', $status)->first()) {
            return true;
        }
        return false;
    }
    
    /**
     * check list ids has status > submited
     * @param type $ids
     * @return type
     */
    public static function hasSubmited($ids) {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        self::whereIn('id', $ids)
                ->update(['manager_id' => auth()->id()]);
        $count = self::whereIn('id', $ids)
                ->whereIn('status', [self::STT_SUBMITED, self::STT_APPROVED, self::STT_CLOSED])
                ->get()->count();
        return ($count == count($ids));
    }
    
    /**
     * increment version
     */
    public function incrementVersion() {
        $this->increment('version');
    }
    
    /**
     * check current user can change status
     * @param type $status
     * @return boolean
     */
    public function canChangeStatus($status) {
        if ($this->status == self::STT_CLOSED) {
            if (!Permission::getInstance()->isAllow('project::project.eval.list_by_leader')) {
                return false;
            }
        }
        if (in_array($this->status, [self::STT_FEEDBACK, self::STT_DRAFT]) && in_array($status, [self::STT_APPROVED, self::STT_CLOSED])) {
            return false;
        }
        if ($status == $this->status) {
            return false;
        }
        $checkComment = MeHistory::checkUserAction($this->id, [MeHistory::AC_COMMENT, MeHistory::AC_NOTE]);
        if ($status == self::STT_FEEDBACK) {
            return $checkComment;
        }
        return true;
    }
    
    public static function collectLeaderViewMember($data) {
        $idEmployee = Permission::getInstance()->getEmployee()->id;
        $projectMemberTbl = ProjectMember::getTableName();
        $projectTbl = Project::getTableName();
        $meEvalTbl = MeEvaluation::getTableName();
        $teamTbl = Team::getTableName();
        $empTbl = Employee::getTableName();
        
        //get collection
        $collection = self::leftJoin($projectMemberTbl.' as pmt', function ($join) use ($meEvalTbl) {
                        $join->on($meEvalTbl. '.project_id', '=', 'pmt.project_id')
                            ->where('pmt.status', '=', ProjectMember::STATUS_APPROVED);
                    })
                    ->where(function ($query) use ($meEvalTbl){
                        $query->where($meEvalTbl.'.employee_id', DB::raw('pmt.employee_id'))
                                ->whereNotNull($meEvalTbl.'.project_id')
                                ->orWhereNull($meEvalTbl.'.project_id');
                    })
                    ->leftJoin($projectTbl.' as proj', function ($join) use ($meEvalTbl) {
                        $join->on($meEvalTbl. '.project_id', '=', 'proj.id')
                                ->where('proj.status', '=', Project::STATUS_APPROVED);
                    })
                    ->whereNull('proj.deleted_at')
                    ->leftJoin($teamTbl.' as team', $meEvalTbl.'.team_id', '=', 'team.id')
                    ->join($empTbl.' as emp', $meEvalTbl.'.employee_id', '=', 'emp.id')
                    ->whereNotIn($meEvalTbl.'.status', [self::STT_DRAFT, self::STT_NEW, self::STT_REWARD]);
        
        if (Permission::getInstance()->isScopeCompany(null, 'project::project.eval.leader_view_of_team')) {
            //do nothing
        } else {
            $teamIds = TeamMember::where('employee_id', $idEmployee)->lists('team_id')->toArray();

            $teamTbl = Team::getTableName();
            $teamMemberTbl = TeamMember::getTableName();
            $collection->join($teamMemberTbl.' as tmb', $meEvalTbl.'.employee_id', '=', 'tmb.employee_id')
                    ->whereIn('tmb.team_id', $teamIds);
        }
        
        //filter teams
        $filterTeamId = Form::getFilterData('spec_data', 'team_id');
        if ($filterTeamId) {
            $teamMemberTbl = TeamMember::getTableName();
            $collection->join($teamMemberTbl.' as ft_tmb', function ($join) use ($meEvalTbl, $filterTeamId) {
                            $join->on($meEvalTbl.'.employee_id', '=', 'ft_tmb.employee_id')
                                ->where('ft_tmb.team_id', '=', $filterTeamId);
                        });
        }
        
        if (isset($data['project_id']) && $data['project_id']) {
            if (is_numeric($data['project_id'])) {
                $collection->where($meEvalTbl.'.project_id', $data['project_id']);
            } else {
                $collection->where($meEvalTbl.'.team_id', explode('_', $data['project_id'])[1]);
            }
        }
        if (isset($data['time']) && $data['time']) {
            $collection->where(DB::raw('DATE_FORMAT('. $meEvalTbl .'.eval_time, "%Y-%m")'), $data['time']);
        }
        if ($filterMonth = Form::getFilterData('except', 'month')) {
            $collection->where(DB::raw('DATE_FORMAT('. $meEvalTbl .'.eval_time, "%Y-%m")'), $filterMonth);
        }
        
        return $collection;
    }

    /*
     * leader view member of team
     * @param array
     * @return collection
     */
    public static function leaderViewMemberOfTeam($data)
    {
        $meEvalTbl = self::getTableName();
        $pager = Config::getPagerData();
        $collection = self::collectLeaderViewMember($data);
        
        $meAttrTbl = MeAttribute::getTableName();
        $mePointTbl = 'me_points';
        $commentTbl = MeComment::getTableName();

        $sepMonth = config("project.me_sep_month");
        if ($sepMonth) {
            $collection->where(DB::raw('DATE_FORMAT('. $meEvalTbl .'.eval_time, "%Y-%m")'), '<=', $sepMonth);
        }
        
        $collection->leftJoin(DB::raw('(SELECT pt.* FROM ' . $meAttrTbl . ' as attr '
                                . 'LEFT JOIN ' . $mePointTbl . ' as pt ON attr.id = pt.attr_id) AS point'), 
                            $meEvalTbl.'.id', '=', 'point.eval_id')
                ->leftJoin($commentTbl . ' as cmt', $meEvalTbl.'.id', '=', 'cmt.eval_id');
        //join employee
        $collection->select($meEvalTbl.'.*', 'proj.project_code_auto', 'proj.name as project_name', 'proj.type as project_type',
                'team.name as team_name', 'emp.email as employee_email', 'emp.employee_code',
                DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(point.attr_id, "|", point.point)) SEPARATOR ",") as point_attrs'),
                DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(IFNULL(cmt.id, -1), "|", IFNULL(cmt.attr_id, -1), "|", IFNULL(cmt.type, -1))) SEPARATOR ",") as cmt_attrs'),
                DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(pmt.id, "|", pmt.start_at, "|", pmt.end_at, "|", pmt.effort)) SEPARATOR ",") as pjm_attrs'))
            ->groupBy($meEvalTbl.'.id');
        
        if (Form::getFilterPagerData('order')) {
            $collection = $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection = $collection->orderBy('eval_time', 'desc')
                    ->orderBy('project_id', 'desc');
        }
        self::filterGrid($collection, ['exception']);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * get total member of team leader manager by projects
     * @param array
     * @return int
     */
    public static function getTotalMemberOfLeader($data = [], $scopeRoute = 'project::project.eval.list_by_leader', $urlFilter = null)
    {
        if ($urlFilter === null) {
            $urlFilter = route($scopeRoute) . '/';
        }
        $scope = Permission::getInstance();
        $leaderId = $scope->getEmployee()->id;

        $projTbl = Project::getTableName();
        $projMbTbl = ProjectMember::getTableName();
        $teamProjTbl = TeamProject::getTableName();
        $teamMbTbl = TeamMember::getTableName();
        $evalTbl = self::getTableName();
        $empTbl = Employee::getTableName();

        if (!isset($data['month']) || !$data['month']) {
            return 0;
        }
        $filterMonth = MeView::parseDateFromFormat($data['month'], 'Y-m');
        $rangeTime = MeView::getBaselineRangeTime($filterMonth);
        $timeTwoMonthsPrevious = clone $filterMonth;
        $timeTwoMonthsPrevious->subMonth(2);

        //cal employee evaluted in month
        $numEvaluted = self::select($evalTbl.'.employee_id')
                ->join($empTbl . ' as emp', $evalTbl.'.employee_id', '=', 'emp.id')
                ->whereNull('emp.deleted_at')
                ->where($evalTbl . '.eval_time', $filterMonth->startOfMonth()->toDateTimeString())
                ->where($evalTbl . '.status', '!=', self::STT_DRAFT)
                ->groupBy($evalTbl . '.employee_id');

        //cal all member join project
        $allMember = ProjectMember::select('pjm.employee_id')
                ->from($projMbTbl . ' as pjm')
                ->join($projTbl.' as proj', function ($join) {
                    $join->on('pjm.project_id', '=', 'proj.id');
                })
                ->join($empTbl . ' as emp', 'pjm.employee_id', '=', 'emp.id')
                ->whereNull('emp.deleted_at')
                ->where('pjm.status', ProjectMember::STATUS_APPROVED)
                ->where('pjm.is_disabled', '!=', ProjectMember::STATUS_DISABLED)
                ->whereNotIn('pjm.type', [ProjectMember::TYPE_PQA, ProjectMember::TYPE_COO])
                ->where(function ($query) use ($timeTwoMonthsPrevious) {
                    $query->where('proj.state', Project::STATE_CLOSED)
                            ->where('proj.end_at', '>=', $timeTwoMonthsPrevious->toDateString())
                            ->orWhere('proj.state', Project::STATE_PROCESSING);
                })
                ->where('pjm.start_at', '<=', $rangeTime['end'])
                ->where('pjm.end_at', '>=', $rangeTime['start'])
                ->groupBy('pjm.employee_id');

        //fillter project
        $projectId = $data['project_id'];
        if ($projectId && is_numeric($projectId)) {
             $numEvaluted->where($evalTbl.'.project_id', $projectId);
             $allMember->where('proj.id', $projectId);
        }
        // filter team project
        if ($teamFilter = Form::getFilterData('team_filter', 'team_id', $urlFilter)) {
            $numEvaluted->leftJoin($teamProjTbl . ' as teamproj', $evalTbl.'.project_id', '=', 'teamproj.team_id')
                    ->where('teamproj.team_id', $teamFilter);

            $allMember->leftJoin($teamProjTbl . ' as teamproj', 'proj.id', '=', 'teamproj.project_id')
                    ->where('teamproj.team_id', $teamFilter);
        }
        //filter team member
        if ($teamMemberId = Form::getFilterData('team_filter', 'team_member', $urlFilter)) {
            $numEvaluted->leftJoin($teamMbTbl . ' as tmb', $evalTbl.'.employee_id', '=', 'tmb.employee_id')
                    ->where('tmb.team_id', $teamMemberId);

            $allMember->leftJoin($teamMbTbl . ' as tmb', 'pjm.employee_id', '=', 'tmb.employee_id')
                    ->where('tmb.team_id', $teamMemberId);
        }

        if ($scope->isScopeCompany(null, $scopeRoute)) {
            //do nothing
        } else {
            $numEvaluted->leftJoin($projTbl . ' as proj', $evalTbl.'.project_id', '=', 'proj.id')
                    ->where('proj.leader_id', $leaderId);

            $allMember->where('proj.leader_id', $leaderId);
        }

        $numEvaluted = $numEvaluted->get()->count();
        $allMember = $allMember->get()->count();

        return $numEvaluted . '/' . $allMember;
    }
    
    /**
     * check all evaluation of project is submited
     * @param type $project_id
     * @param type $time
     * @return boolean
     */
    public static function checkEvalProjectSubmited($project_id, $time) {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time)->startOfMonth();
        }
        $item = self::where('project_id', $project_id)
                ->where('eval_time', $time->toDateTimeString())
                ->whereIn('status', [self::STT_DRAFT, self::STT_FEEDBACK])
                ->first();
        if ($item) {
            return true;
        }
        return false;
    }
    
    /**
     * get me of all employee all time
     * 
     * @param model $project
     * @return array
     */
    public static function getMEEmployeesOfProject($project)
    {
        $collection = self::select('employee_id', 'eval_time', 
                'avg_point')
            ->where('project_id', $project->id)
            ->where('status', self::STT_CLOSED)
            ->get();
        if (!$collection || !count($collection)) {
            return null;
        }
        $result = [];
        foreach ($collection as $item) {
            $result[$item->employee_id][Carbon::parse($item->eval_time)->format('Y-m')] 
                = $item->avg_point;
        }
        return $result;
    }
    
    /**
     * update ME point after upload timesheet
     * @param type $time
     * @return type
     */
    public static function updateMEAfterUploadTimeSheet($time) {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        $meItems = self::with('project', 'employee', 'meAttributes')
                ->where('eval_time', '>=', $time->startOfMonth()->toDateTimeString())
                ->where('eval_time', '<=', $time->endOfMonth()->toDateTimeString())
                ->get();
        if ($meItems->isEmpty()) {
            return;
        }
        //get time attribute
        $attr_time = MeAttribute::where('can_fill', 0)->first();
        $attrs_normal = MeAttribute::getNormalAttrs();
        $attrs_perform = MeAttribute::getPerformAttrs();
        if ($attrs_normal->isEmpty() || $attrs_perform->isEmpty()) {
            return;
        }
        foreach ($meItems as $item) {
            if ($attr_time) {
                $timeEval = $item->eval_time;
                if ($timeEval) {
                    if (!is_object($timeEval)) {
                        $timeEval = Carbon::parse($timeEval);
                    }
                    $timeEval->modify('-1 month');
                }
                $maxPoint = $attr_time->range_max;
                $email = $item->employee->email;
                $sub = MeTimeSheet::getTimeLateByEmail($email, $timeEval);
                $subPoint = $maxPoint - $sub;
                if ($subPoint < 0) {
                    $subPoint = 0;
                }
                $evalAttr = $item->meAttributes()->wherePivot('attr_id', $attr_time->id)->first();
                if ($evalAttr) {
                    $item->meAttributes()->updateExistingPivot($attr_time->id, ['point' => $subPoint]);
                } else {
                    $item->meAttributes()->attach([$attr_time->id => ['point' => $subPoint]]);
                }
            }
            $isMeTeam = null;
            if ($item->project_id && $item->project) {
                $isMeTeam = false;
            } else if ($item->team_id) {
                $isMeTeam = true;
            }
            if ($isMeTeam !== null) {
                self::updateMEPoint($item, $attrs_normal, $attrs_perform, null, $isMeTeam);
            }
        }
    }
    
    /**
     * update ME point
     * @param type $item
     * @return type
     */
    public static function updateMEPoint($item, $attrs_normal, $attrs_perform, $project_point = null, $isMeTeam = false) {
        $project = $item->project;
        $point_rule = 0;
        $normal_weight = 0;
        foreach ($attrs_normal as $attr) {
            $normal_weight += $attr->weight;
            $point_rule += $item->getPoint($attr)['point'] * $attr->weight;
        }
        $point_rule = round($point_rule / ($normal_weight * 2), 2);
        
        //individua index
        $point_individual = 0;
        $individual_weight = 0;
        $individual_count = 0;
        foreach ($attrs_perform as $attr) {
            $attr_point = $item->getPoint($attr)['point'];
            $individual_weight += $attr->weight;
            if ($attr_point > MeAttribute::NA) {
                $point_individual += $attr_point;
                $individual_count ++;
            }
        }
        $point_individual = $point_individual / $individual_count;
        
        if (!$isMeTeam) {
            //project index
            $projectIndex = $item->getFactorProjectType($project->type);
            //get project point
            if (!$project_point) {
                $project_point = MeEvaluation::getProjectPointLastMonth($project, $item->eval_time);
            }
        } else {
            $projectIndex = 1;
            if (!$project_point) {
                $project_point = $item->proj_point;
            }
        }
        
        //point performance
        $pp_point = min([$project_point * $projectIndex * self::MAX_POINT / self::MAX_PP, self::MAX_POINT]);
        $pp_weight = 100 - $normal_weight - $individual_weight;
        
        //caculate summary point
        $sumary = $point_rule * $normal_weight + $point_individual * $individual_weight + $pp_point * $pp_weight;
        $sumary = round($sumary / 100, 2);

        $item->proj_index = $isMeTeam ? null : $projectIndex;
        $item->proj_point = $project_point;
        $item->avg_point = $sumary;
        $item->effort = $item->getWorkDatesInMonth();
        return $item->save();
    }

    /**
     * get point by attribute id
     * @param type $attr_id
     * @return int
     */
    public function getPoint($meAttribute)
    {
        $result = [];
        if (!is_object($meAttribute)) {
            $meAttribute = MeAttribute::find($meAttribute);
        }
        $attrId = $meAttribute->id;
        if ($this->meAttributes) {
            $evalAttr = $this->meAttributes()->wherePivot('attr_id', $attrId)->first();
            if ($evalAttr) {
                $result['point'] = round($evalAttr->pivot->point, 0);
                return $result;
            }
        }
        if ($meAttribute) {
            if ($meAttribute->default) {
                $result['point'] = round($meAttribute->default, 0);
            } else {
                $result['point'] = null;
                if ($this->status != self::STT_DRAFT) {
                    $result['point'] = 0;
                }
                $result['default'] = true;
            }
            return $result;
        }
    }

    /*
     * search project or team
     */
    public static function searchProjectOrTeamAjax($name, $config = [])
    {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 20
        ];
        $config = array_merge($arrayDefault, $config);
        $collection = Project::select('id as idc', 'name', DB::raw('2 as type'))
                ->where('name', 'LIKE', '%' . $name . '%')
                ->whereNull('parent_id')
                ->groupBy('id');

        $collectTeams = Team::select(
            DB::raw('CONCAT("t_", id) as idc'),
            DB::raw('CONCAT(name, " (Team)") as name'),
            DB::raw('1 as type')
        )
            ->where('name', 'LIKE', '%' . $name . '%')
            ->groupBy('id');

        $collection->union($collectTeams);
        $collection->orderBy('type', 'asc')
                ->orderBy('name', 'asc');

        $collection = $collection->get();
        $result['total_count'] = $collection->count();
        $page = $config['page'];
        $perPage = $config['limit'];
        $slice = $collection->slice(($page - 1) * $perPage, $perPage)->all();
        $data = new Paginator($slice, count($collection), $perPage, $page);
        $data->setPath(route('project::me.search.project.team.ajax'));
        foreach ($data as $item) {
            $result['items'][] = [
                'id' => $item->idc,
                'text' => e($item->name),
                'loading' => 1,
            ];
        }
        return $result;
    }

    /*
     * find project name or employee nam by id
     */
    public static function findProjectOrTeamName($projTeamId)
    {
        if (!$projTeamId) {
            return null;
        }
        if (is_numeric($projTeamId)) {
            $project = Project::find($projTeamId);
            if ($project) {
                return $project->name;
            }
            return null;
        }
        $arrIds = explode('_', $projTeamId);
        if (count($arrIds) != 2) {
            return null;
        }
        $team = Team::find($arrIds[1]);
        if ($team) {
            return $team->name . ' (Team)';
        }
        return null;
    }

    /*
     * find employee name by id
     */
    public static function findEmployeeName($employeeId)
    {
        if (!$employeeId) {
            return null;
        }
        $employee = Employee::find($employeeId, ['id', 'email']);
        if (!$employee) {
            return null;
        }
        return CoreView::getNickName($employee->email);
    }

    /**
     * list review items (ajax)
     * @param array $dataFilter
     * @return string
     */
    public static function listReviewItems($urlFilter, $dataFilter = [])
    {
        $collection = MeEvaluation::collectByLeader($urlFilter);
        $collectionModel = MeEvaluation::getByLeader($collection, $dataFilter);
        $normalAttrs = MeAttribute::getNormalAttrs();
        $performAttrs = MeAttribute::getPerformAttrs();
        //get baseline date each month
        $listRangeMonths = MeView::listRangeBaselineDate(array_keys($collectionModel->lists('id', 'eval_month')->toArray()));

        $filterEmployee = (isset($dataFilter['employee_id']) && $dataFilter['employee_id']) ? $dataFilter['employee_id'] : null;
        $arrayTypeLabel = Project::labelTypeProject();
        return [
            'collection_html' => view('project::me.template.review-items', compact(
                    'collectionModel',
                    'filterEmployee',
                    'arrayTypeLabel',
                    'normalAttrs',
                    'performAttrs',
                    'listRangeMonths'
                ))->render(),
            'collection_pager' => view('team::include.pager', compact('collectionModel'))->render()
        ];
    }

    /*
     * get review statistic
     */
    public static function reviewStatistic()
    {
        $urlFilter = route('project::project.eval.list_by_leader') . '/';
        $collection = self::collectByLeader($urlFilter);
        $dataFilter = Session::get(MeView::KEY_REVIEW_FILTER);
        $projsNotEval = self::getProjectNotEval($dataFilter);
        return [
            'statistic' => self::countByAvgPoint($collection, $urlFilter),
            'proj_not_eval' => $projsNotEval,
            'total_member' => self::getTotalMemberOfLeader($dataFilter),
            'review_items' => self::listReviewItems($urlFilter, $dataFilter)
        ];
    }
}
