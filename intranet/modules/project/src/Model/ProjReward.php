<?php

namespace Rikkei\Project\Model;

use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config as TeamConfig;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\MeReward;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Project\View\ProjDbHelp;
use Rikkei\Project\View\ProjConst;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Resource\View\getOptions;

class ProjReward extends Task
{
    
    //reward type
    const TYPE_TASK = 1;
    const TYPE_ME = 2;
    
    //payment state of reward base
    const STATE_UNPAID = 0; 
    const STATE_PAID = 1;

    /**
     * get all status avai of reward actual
     * 
     * @return array
     */
    public static function getStatusAvai()
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_REVIEWED => 'Reviewed',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_FEEDBACK => 'Feedback'
        ];
    }
    
    /**
     * list state paid
     * @return array
     */
    public static function getPaidStatus()
    {
        return [
            self::STATE_UNPAID => 'Unpaid',
            self::STATE_PAID => 'Paid'
        ];
    }

    /**
     * get status label of reward
     * 
     * @param int $status
     * @param array $statusAll
     * @return string
     */
    public static function getLabelStatusRewardActual($status, array $statusAll = [])
    {
        if (!$statusAll) {
            $statusAll = self::getStatusAvai();
        }
        if (isset($statusAll[$status])) {
            return $statusAll[$status];
        }
        return reset($statusAll);
    }
    
    /**
     * get grid data in list
     * 
     * @return collection
     */
    public static function getGridDataReward() {
        $rqPage = Input::get('page');
        $pageOption = [];
        if ($rqPage) {
            $pageOption = ['page' => $rqPage];
        }
        $pager = TeamConfig::getPagerData(null, $pageOption);
        $tableTask = self::getTableName();
        $tableProject = Project::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableRewardMeta = ProjRewardMeta::getTableName();
        $tableRewardEmployee = ProjRewardEmployee::getTableName();
        $tableTeamProject = TeamProject::getTableName();
        $tableTeam = Team::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        
        $querySumRewardSubmit = "SUM({$tableRewardEmployee}.reward_submit)";
        $querySumRewardReview = "SUM({$tableRewardEmployee}.reward_confirm)";
        $querySumRewardApprove = "SUM({$tableRewardEmployee}.reward_approve)";
        // check status, if new => get total is submit
        $queryRewardNew = "(CASE {$tableTask}.status WHEN ".Task::STATUS_NEW.
            " THEN {$querySumRewardSubmit} ";
        // status is reviewed, return reward reviewd (if not null) or reward submit
        $queryRewardReview = "WHEN ".Task::STATUS_REVIEWED.
            " THEN IFNULL({$querySumRewardReview}, {$querySumRewardSubmit}) ";
        // status is approved, return reward approved (if not null) or reward reviewd
        //    or reward submit
        $queryRewardApprove = "WHEN ".Task::STATUS_APPROVED.
            " THEN IFNULL({$querySumRewardApprove}, "
                . "IFNULL({$querySumRewardReview}, {$querySumRewardSubmit})) ";
        $queryRewardFeedback = "ELSE {$querySumRewardSubmit} END";
        $queryRewardCalApprove = $queryRewardNew . $queryRewardReview. 
            $queryRewardApprove . $queryRewardFeedback .
            ') as sum_reward_approve';
        
        $collection = self::select([$tableTask.'.id', $tableTask.'.status', $tableTask.'.bonus_money',
                $tableRewardMeta.'.approve_date', $tableTask.'.created_at', $tableProject.'.name',
                $tableEmployee.'.email', $tableRewardMeta.'.billable',
                $tableRewardMeta.'.reward_budget', $tableTask.'.project_id', $tableProject.'.type as project_type',
                DB::raw("GROUP_CONCAT(DISTINCT({$tableTeam}.name) SEPARATOR ', ') "
                    . "as team_name"),
                DB::raw($queryRewardCalApprove),
                DB::raw(self::TYPE_TASK . ' as rw_type'),
                "{$tableProject}.leader_id",
                DB::raw('NULL as meteam_id'),
                $tableRewardMeta.'.month_reward'
            ])
            ->join($tableProject, $tableProject.'.id', '=',
                $tableTask.'.project_id')
            ->leftJoin($tableTeamProject, $tableTeamProject.'.project_id', '=',
                $tableProject.'.id')
            ->leftJoin($tableTeam, $tableTeam.'.id', '=', 
                $tableTeamProject.'.team_id')
            ->leftJoin($tableEmployee, $tableEmployee.'.id', '=',
                $tableProject.'.manager_id')
            ->leftJoin($tableRewardMeta, $tableRewardMeta.'.task_id', '=',
                $tableTask.'.id')
            ->leftJoin($tableRewardEmployee, $tableRewardEmployee.'.task_id', '=',
                $tableTask.'.id')
            ->where($tableTask.'.type', Task::TYPE_REWARD)
            ->where(function ($query) use ($tableProject) {
                $query->where("{$tableProject}.state", Project::STATE_CLOSED)
                    ->orWhere("{$tableProject}.type", Project::TYPE_OSDC);
            })
            ->groupBy($tableTask.'.id');
            
        $statusFilter = CoreForm::getFilterData('status', $tableTask.'.status');
        if ($statusFilter) {
            if ($statusFilter == 1)
            {
                $collection->where($tableTask.'.status', Task::STATUS_CLOSED);
            }
            else {
                $collection->where($tableTask.'.status', $statusFilter);
            }
        }
        if (Project::isUseSoftDelete()) {
            $collection->whereNull($tableProject.'.deleted_at');
        }
        if (Team::isUseSoftDelete()) {
            $collection->whereNull($tableTeam.'.deleted_at');
        }
        
        $teamFilter = CoreForm::getFilterData('exception', 'team_id');
        if ($teamFilter) {
            $teamFilter = (int) $teamFilter;
            $arrayTeamFilter = [$teamFilter];
            $teamPath = Team::getTeamPath();
            if (isset($teamPath[$teamFilter]) && 
                isset($teamPath[$teamFilter]['child'])
            ) {
                $arrayTeamFilter = array_merge($arrayTeamFilter, 
                        (array) $teamPath[$teamFilter]['child']);
            }
            $collection->where(function ($query) 
                use (
                    $tableProject,
                    $tableTeamProject,
                    $arrayTeamFilter,
                    $tableRewardEmployee,
                    $tableTeamMember
            ){
                $query->whereIn($tableProject.'.id', function ($query) 
                    use (
                        $tableProject,
                        $tableTeamProject,
                        $arrayTeamFilter
                ){
                    $query->select($tableProject.'.id')
                        ->from($tableProject)
                        ->join($tableTeamProject, $tableTeamProject.'.project_id', 
                            '=', $tableProject.'.id')
                        ->whereIn($tableTeamProject.'.team_id', $arrayTeamFilter);
                })->orWhereIn($tableRewardEmployee.'.employee_id', function ($query) 
                    use (
                        $arrayTeamFilter,
                        $tableTeamProject,
                        $tableTeamMember
                ){
                    $query->select($tableTeamMember.'.employee_id')
                        ->from($tableTeamMember)
                        ->whereIn($tableTeamMember.'.team_id', $arrayTeamFilter)
                        ->whereIn($tableTeamProject.'.team_id', $arrayTeamFilter);
                });
            });
        }

        // check permission
        if (Permission::getInstance()->isScopeCompany(null, 'project::reward')) {
            // view all reward
        } elseif ($teamsUser = Permission::getInstance()->isScopeTeam(null, 'project::reward')) {
            $collection->where(function ($query) 
                use (
                    $tableProject,
                    $tableTeamProject,
                    $teamsUser,
                    $tableRewardEmployee,
                    $tableTeamMember
            ){
                $query->whereIn($tableProject.'.id', function ($query) 
                    use (
                        $tableProject,
                        $tableTeamProject,
                        $teamsUser
                ){
                    $query->select($tableProject.'.id')
                        ->from($tableProject)
                        ->join($tableTeamProject, $tableTeamProject.'.project_id', 
                            '=', $tableProject.'.id')
                        ->whereIn($tableTeamProject.'.team_id', $teamsUser);
                })->orWhereIn($tableRewardEmployee.'.employee_id', function ($query) 
                    use (
                        $teamsUser,
                        $tableTeamMember
                ){
                    $query->select($tableTeamMember.'.employee_id')
                        ->from($tableTeamMember)
                        ->whereIn($tableTeamMember.'.team_id', $teamsUser);
                });
            });
        } else { //view self project => not show
            $collection = self::select('id')->where('id', 0);
        }
        self::filterGrid($collection, ['exception']);
        $meRewards = MeReward::collectOnProject('project::reward');
        
        //filter task or me type (TYPE_TASK, TYPE_ME) not project type
        $filterProjType = CoreForm::getFilterData('exception', 'project_type');
        if ($filterProjType) {
            if ($filterProjType == self::TYPE_TASK) {
//                set null me reward
                $meRewards->where('me.id', -1);
            } else {
                //set null collection task
                $collection->where($tableTask.'.id', -1);
            }
        }
        //if not have filter project type of type = type TASK
        if (!$filterProjType || $filterProjType == self::TYPE_TASK) {
            $collection->where($tableProject.'.type', Project::TYPE_BASE);
        }
        //if not have filter project type or type = type ME

        //filter payment status
        $filterPaidStatus = CoreForm::getFilterData('exception', 'pay_status');
        if ($filterPaidStatus === null) {
            $filterPaidStatus = self::STATE_UNPAID;
        }
        if ($filterPaidStatus !== null && $filterPaidStatus !== '_all_') {
            $filterPaidStatus = intval($filterPaidStatus);
            $collection->where($tableTask.'.bonus_money', $filterPaidStatus);
            //filter me
            $meRewards->having('bonus_money', '=', $filterPaidStatus);
        }
        
        //filter month
        $filterMonthExists = CoreForm::getFilterData('exception', 'month');
        $filterMDefault = ProjDbHelp::getDateDefaultRewardFilter();
        if ($filterMonthExists) {
            $filterMonth = Carbon::createFromFormat('m-Y', $filterMonthExists);
            $filterMSame = $filterMonth->format('Y-m') === $filterMDefault->format('Y-m');
        } else {
            $filterMonth = $filterMDefault;
            $filterMSame = true;
        }
        //if filter month = now and filter paid status = Unpaid then get all project unpaid to now
        //ignore this filter
        /*$monthNowUnpaid = false;
        if ($filterPaidStatus === self::STATE_UNPAID
                && ($filterMonth->format('m-Y') === Carbon::now()->format('m-Y'))) {
            //set filter month start min
            $filterMStart = '1970-01-01';
            $monthNowUnpaid = true;
        } else {
            $filterMStart = $filterMonth->startOfMonth()->toDateString();
        }*/
        $filterMStart = $filterMonth->startOfMonth()->toDateString();
        $filterMEnd = $filterMonth->lastOfMonth()
            ->modify('+' . ProjConst::DAY_REWARD_PAID . ' days')->toDateString();
        $collection->where(function ($query) 
            use (
            $tableProject, 
            $tableRewardMeta, 
            $filterMStart,
            $filterMEnd,
            $filterMSame
        ){
            $query //filter follow approve date
            ->orWhere(function($query) use ($tableRewardMeta, $filterMStart, $filterMEnd) {
                $query->whereDate($tableRewardMeta.'.approve_date', '<=', $filterMEnd)
                ->whereDate($tableRewardMeta.'.approve_date', '>=', $filterMStart);
            })
            // filter follow create date
            ->orWhere(function($query) use ($tableRewardMeta, $filterMStart, $filterMEnd) {
                $query->whereDate($tableRewardMeta.'.month_reward', '<=', $filterMEnd)
                ->whereDate($tableRewardMeta.'.month_reward', '>=', $filterMStart)
                ->where($tableRewardMeta.'.approve_date', null);
            })
            // filter follow end date
            ->orWhere(function ($query) use ($tableProject, $tableRewardMeta, $filterMStart, $filterMEnd) {
                $query->whereDate($tableProject.'.end_at', '<=', $filterMEnd)
                    ->whereDate($tableProject.'.end_at', '>=', $filterMStart)
                    ->where($tableRewardMeta.'.approve_date', null)
                    ->where($tableRewardMeta.'.month_reward', null);
            });
            // filter default => get all not approve
            if ($filterMSame) {
                $query->orWhere(function ($query) use ($tableRewardMeta, $filterMStart, $filterMEnd) {
                    $query->where($tableRewardMeta.'.approve_date', null)
                        ->where(function($query) use ($tableRewardMeta, $filterMEnd) {
                            $query->orWhereNull($tableRewardMeta.'.month_reward')
                                ->orWhere(function($query) use ($tableRewardMeta, $filterMEnd) {
                                    $query->whereNotNull($tableRewardMeta.'.month_reward')
                                        ->whereDate($tableRewardMeta.'.month_reward', '<=', $filterMEnd);
                                });
                        });
                });
            }
        });
        //check unpaid and month now
        //ignore this filter
        /*if ($monthNowUnpaid) {
            $meRewards->whereDate('me.eval_time', '<=', $filterMEnd);
        } else {
            $meRewards->whereDate('me.eval_time', '=', $filterMStart);
        }*/
        $meRewards->whereDate('me.eval_time', '=', $filterMStart);

        $collection->union($meRewards);
        if (CoreForm::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('status', 'DESC')
                ->orderBy('created_at', 'DESC');
        }
        $collection = $collection->get();
        $page = $pager['page'];
        $perPage = $pager['limit'];
        $slice = $collection->slice(($page - 1) * $perPage, $perPage)->all();
        $data = new Paginator($slice, count($collection), $perPage, $page);
        $data->setPath(route('project::report.reward.list'));
        return $data;
    }
    
    /**
     * check user access view reward project follow acl
     * 
     * @param model $project
     * @return boolean
     */
    public static function isAccessViewReward($project)
    {
        if (Permission::getInstance()->isScopeCompany(null, 'project::reward')) {
            return true;
        }
        if (!Permission::getInstance()->isScopeTeam(null, 'project::reward')) {
            return false;
        }
        // acl Team => view reward of team
        $tableProject = Project::getTableName();
        $tableTeamProject = TeamProject::getTableName();
        $teamsUser = Permission::getInstance()->isScopeTeam(null, 'project::reward');
        
        $countTeamBelong = Project::select($tableProject.'.id')
            ->from($tableProject)
            ->join($tableTeamProject, $tableTeamProject.'.project_id', 
                '=', $tableProject.'.id')
            ->whereIn($tableTeamProject.'.team_id', $teamsUser)
            ->where($tableProject.'.id', $project->id)
            ->count();
        if ($countTeamBelong) {
            return true;
        }
        return false;
    }
    
    /**
     * export json data
     * @param type $scopeRoute
     * @return type
     */
    public static function exportData ($scopeRoute = 'project::reward', $both = null) 
    {
        $exportProjId = Input::get('project_ids');
        $teamMe = Input::get('teamIds');
        $data = Input::all();
        $scope = Permission::getInstance();
        $currUser = $scope->getEmployee();
        $userTeam = $scope->getTeams();
        $tblProjEmpReward = ProjRewardEmployee::getTableName();
        $tblEmp = Employee::getTableName();
        $tblProj = Project::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMb = TeamMember::getTableName();
        $tblTask = Task::getTableName();
        $tableRewardMeta = ProjRewardMeta::getTableName();
        $tblTeamProj = TeamProject::getTableName();

        $collection = ProjRewardEmployee::select('emp.id as emp_id', 'proj.id as proj_id', 
                    'emp.employee_code as emp_code', 'emp.name as emp_name', 'emp.email as emp_email',
                DB::raw('GROUP_CONCAT(DISTINCT(teamEmp.name)) as team_name'),
                DB::raw('NULL as teamme_name'),
                DB::raw('GROUP_CONCAT(DISTINCT(teamEmp.name)) as team'),
                DB::raw('IFNULL('.$tblProjEmpReward.'.reward_approve, 0) as reward_approve'),
                DB::raw($tblProjEmpReward.'.comment as comment'),
                DB::raw($tblProjEmpReward.'.type as member_type'),
                'proj.name as proj_name', 'proj.type as proj_type',
                DB::raw('NULL as me_point'))
                ->join($tblTask . ' as task', $tblProjEmpReward.'.task_id', '=', 'task.id')
                ->join($tblProj . ' as proj', 'task.project_id', '=', 'proj.id')
                ->join($tblEmp . ' as emp', $tblProjEmpReward.'.employee_id', '=', 'emp.id')
                ->join($tblTeamMb.' as tmb', 'emp.id', '=', 'tmb.employee_id')
                ->join($tblTeam.' as teamEmp', 'tmb.team_id', '=', 'teamEmp.id')
                ->join($tableRewardMeta, $tableRewardMeta.'.task_id', '=', 'task.id')
                //except partner employee
                ->leftJoin(EmployeeWork::getTableName() . ' as empw', 'empw.employee_id', '=', 'emp.id')
                ->where(function ($query) {
                    $query->whereNull('empw.employee_id')
                            ->orWhere('empw.contract_type', '!=', getOptions::WORKING_BORROW);
                });

        if ($scope->isScopeCompany(null, $scopeRoute)) {
            //view all
        } else if ($scope->isScopeTeam(null, $scopeRoute)) {
            $collection->whereIn('emp.id', function($query) use ($userTeam, $tblTeamMb) {
                $query->select('employee_id')->from($tblTeamMb)
                        ->whereIn('team_id', $userTeam);
            });
        } else {
            $collection->where('proj.leader_id', $currUser->id);
        }
        $filterMonth = CoreForm::getFilterData('exception', 'month', route('project::report.reward.list').'/');
        if (!$filterMonth) {
            $filterMonth = ProjDbHelp::getDateDefaultRewardFilter();
        } else {
            $filterMonth = Carbon::createFromFormat('m-Y', $filterMonth);
        }
        $filterMStart = $filterMonth->startOfMonth()->toDateString();
        $filterMEnd = clone $filterMonth;
        $filterMEnd = $filterMEnd->lastOfMonth()
            ->modify('+' . ProjConst::DAY_REWARD_PAID . ' days')->toDateString();
        //filter end_date
        $collection
            ->whereDate($tableRewardMeta.'.approve_date', '>=', $filterMStart)
            ->whereDate($tableRewardMeta.'.approve_date', '<=', $filterMEnd)
            ->where('task.status', Task::STATUS_APPROVED)
            //->where("task.bonus_money", Task::REWARD_IS_UNPAID)
            //ignore reward null or zero (0)
            ->where(function ($query) use ($tblProjEmpReward) {
                $query->whereNotNull($tblProjEmpReward. '.reward_approve')
                        ->where($tblProjEmpReward . '.reward_approve', '>', 0);
            })
            ->groupBy('task.project_id', $tblProjEmpReward.'.id');
            
        //from ME reward
        $meRewardExports = MeReward::getDataExport($filterMonth, $scopeRoute);
        
        //$teamMe if check ME for team
        //$exportProjId if check project
        if ($teamMe && $exportProjId) {
            $collection->whereIn('proj.id', $exportProjId);
            
            //$teamMe khong lien quan den project base
            
            $meRewardExports->where(function ($query) use ($teamMe, $exportProjId) {
                $query->whereIn('me.team_id', $teamMe)
                      ->orWhereIn('me.project_id', $exportProjId);
            });
        } else {
            if ($exportProjId && !$teamMe) {
                $collection->whereIn('proj.id', $exportProjId);
                $meRewardExports->whereIn('me.project_id', $exportProjId);
            } elseif ($teamMe && !$exportProjId) {
                $collection->whereNull('proj.id');
                $meRewardExports->whereIn('me.team_id', $teamMe);
            } else {
                //all
            }
        }
        if ($data['group']) {
            $teamFilter = (int) $data['group'];
            $arrayTeamFilter = [$teamFilter];
            $teamPath = Team::getTeamPath();
            if (isset($teamPath[$teamFilter]) && 
                isset($teamPath[$teamFilter]['child'])
            ) {
                $arrayTeamFilter = array_merge($arrayTeamFilter, 
                        (array) $teamPath[$teamFilter]['child']);
            }
            
            //export follow employee team
            $collection->where(function ($query) use (
                $arrayTeamFilter,
                $tblTeamMb,
                $tblTeamProj
            ) {
                //team member
                $query->whereIn('emp.id', function($query1) use ($arrayTeamFilter, $tblTeamMb) {
                    $query1->select('employee_id')
                            ->from($tblTeamMb)
                            ->whereIn('team_id', $arrayTeamFilter);
                });
                //orwhere team project
                $query->orWhereIn('proj.id', function ($query2) use ($arrayTeamFilter, $tblTeamProj) {
                    $query2->select('project_id')
                            ->from($tblTeamProj)
                            ->whereIn('team_id', $arrayTeamFilter);
                });
            });
            //me
            $meRewardExports->whereIn('emp.id', function($query) use ($arrayTeamFilter, $tblTeamMb) {
                $query->select('employee_id')->from($tblTeamMb)
                        ->whereIn('team_id', $arrayTeamFilter);
            });
        }
        if ($data['pm']) {
            $collection->whereIn('proj.manager_id', function ($query) use ($tblEmp, $data){
                $query->select($tblEmp.'.id')
                        ->from($tblEmp)
                        ->where($tblEmp.'.email', 'like', '%' . $data['pm'] . '%')
                        ->get();
                });
            //me
            $meRewardExports->whereIn('proj.manager_id', function ($query) use ($tblEmp, $data){
                $query->select($tblEmp.'.id')
                        ->from($tblEmp)
                        ->where($tblEmp.'.email', 'like', '%' . $data['pm'] . '%')
                        ->get();
                });
        }
        if ($data['projName']) {
            $collection->where('proj.name', 'like', '%' . $data['projName'] . '%');
            //me
            $meRewardExports->where('proj.name', 'like', '%' . $data['projName'] . '%');
        }
        if($data['type'] && $data['type'] == self::TYPE_TASK) {
            $collection->where('proj.type', Project::TYPE_BASE);
            //set null me
            $meRewardExports->where('me.id', -1);
        } elseif ($data['type'] && $data['type'] == self::TYPE_ME) {
            //set null task
            $collection->where($tblProjEmpReward . '.id', -1);
            
            $meRewardExports->where(function ($query) {
                    $query->whereNull('proj.id')
                            ->orWhere('proj.type', '!=', Project::TYPE_BASE);
                });
        } else {
            
        }
        //status paid
        if (isset($data['statusPaid'])) {
            $statusPaid = $data['statusPaid'];
            if ($statusPaid !== '_all_' && $statusPaid !== "") {
                $statusPaid = intval($statusPaid);
                $collection->where('task.bonus_money', $statusPaid);
                $meRewardExports->where('reward.status_paid', $statusPaid);
            }
        }
        //union select with me
        $collection->union($meRewardExports);
        if ($both) {
            return $collection->orderBy('team')->orderBy('emp_email')
                ->get()->toArray();
        }
        return  $collection->orderBy('team')->orderBy('emp_email')
            ->get()
            ->toJson();
        
    }
    
    /**
     * Check has permission view budget and approve reward in Report reward list
     * 
     * @param int $proLeaderId
     * @param Employee $curEmp
     * @return boolean
     */
    public static function hasShowBudgetApproveReward($proLeaderId, $curEmp, $route = 'project::reward')
    {
        return Permission::getInstance()->isScopeCompany(null, $route) || (Permission::getInstance()->isScopeTeam(null, $route) && $curEmp->id == $proLeaderId);
    }
}
