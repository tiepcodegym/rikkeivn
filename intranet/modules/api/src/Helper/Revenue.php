<?php

namespace Rikkei\Api\Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Project\Model\DevicesExpense;
use Rikkei\Project\Model\MeReward;
use Rikkei\Project\Model\Project as ProjectModel;
use Rikkei\Project\Model\ProjectApprovedProductionCost;
use Rikkei\Project\Model\ProjectAdditional;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Project\Model\ToolAndInfrastructure;
use Rikkei\Project\View\MeView;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Project\View\View as pView;
use Rikkei\Api\Helper\Project as ProjectHelper;

class Revenue extends BaseHelper
{
    const REVENUE_SCOPE_TEAM = 'team';
    const REVENUE_SCOPE_COMPANY = 'company';

    /**
     * Get List current team + child teams of current email
     *
     * @param $request
     */
    public function getOwnerList($request)
    {
        $employee = $this->getEmployee($request->email);
        $permission = $this->getPermission($employee);

        $selectedFields = [
            'teams.name', 'teams.id', 'teams.leader_id', 'employees.email as leader_email',
            DB::raw('GROUP_CONCAT( empSub.email SEPARATOR \', \' ) AS sub_leader_email'),
            'teams.type', 'teams.is_soft_dev', 'teams.code', 'is_sm_team',
            'teams.parent_id', 'teams.is_function', 'teams.branch_code', 'teams.is_bo',
            'teams.sort_order', 'teams.is_branch', 'teams.deleted_at'];

        $collections = Team::select($selectedFields)
        ->leftJoin('employees', 'employees.id', '=', 'teams.leader_id')
        ->leftJoin('team_members', function ($q) {
            $q->on('team_members.team_id', '=', 'teams.id')
            ->where('team_members.role_id', '=', Team::ROLE_SUB_LEADER);
        })
        ->leftJoin('employees as empSub', function ($q) {
            $q->on('empSub.id', '=', 'team_members.employee_id')
            ->where(function($query) {
                $query->whereNull('empSub.leave_date')
                    ->orWhere('empSub.leave_date', '>', Carbon::now()->format('Y-m-d'));
            });
        })
        ->groupBy('teams.id')
        ->withTrashed();
        if ($permission->isScopeCompany()) {
            $collections = $collections->orderBy('sort_order', 'asc');
            $scope = \Rikkei\Team\Model\Permission::SCOPE_COMPANY;
        } elseif ($ownedTeamIds = $permission->isScopeTeam()) {
            $collections = $collections->whereIn('teams.id', $ownedTeamIds);
            $scope = \Rikkei\Team\Model\Permission::SCOPE_TEAM;
        } elseif ($permission->isScopeSelf()) {
            $scope = \Rikkei\Team\Model\Permission::SCOPE_SELF;
        } else {
            return [];
        }

        $ownerTeamIds = $collections->get();

        return $this->getListOption($ownerTeamIds, $request, $employee, $scope);
    }

    public function getTeamIsWorking($employee, $isWorking = true)
    {
        $teamHistoryTbl = EmployeeTeamHistory::getTableName();
        $teamsTbl = Team::getTableName();

        $result = Team::select([
            'name', 'teams.id', 'leader_id', 'type', 'is_soft_dev', 'code',
            'parent_id', 'is_function', 'branch_code', 'is_bo', 'is_sm_team',
            'sort_order', 'is_branch', 'teams.deleted_at'])
            ->join($teamHistoryTbl, "{$teamsTbl}.id", '=', "{$teamHistoryTbl}.team_id")
            ->where("{$teamHistoryTbl}.employee_id", $employee->id)
           
            ->whereNull("{$teamHistoryTbl}.deleted_at")
            ->orderBy('sort_order', 'asc');
            if ($isWorking) {
                $result->where("{$teamHistoryTbl}.is_working", true);
                return $result->first();
            }
            $result->groupBy('teams.id');
            $now = Carbon::now()->toDateString();
            $result->where(function ($query) use ($now, $teamHistoryTbl) {
                    $query->whereDate("{$teamHistoryTbl}.end_at", '>=', $now)
                        ->orWhereNull("{$teamHistoryTbl}.end_at");
                });
            return $result->get(); 
    }

    public function genDataTeam(&$options, $teamList, $parentId = null, $char = '', $hasPrefix = true)
    {
        if (empty($teamList)) {
            return;
        }

        foreach ($teamList as $key => $team) {
            if ($team['parent_id'] == $parentId) {
                $optionItem = [
                    'label' => $char . $team['name'],
                    'value' => $team['id'],
                    'leader_id' => $team['leader_id'],
                    'leader_email' => $team['leader_email'],
                    'sub_leader_email' => $team['sub_leader_email'],
                    'type' => $team['type'],
                    'is_soft_dev' => $team['is_soft_dev'],
                    'code' => $team['code'],
                    'parent_id' => $team['parent_id'],
                    'branch_code' => $team['branch_code'],
                    'is_bo' => $team['is_bo'],
                    'sort_order' => $team['sort_order'],
                    'is_branch' => $team['is_branch'],
                    'deleted_at' => $team['deleted_at'],
                    'is_sm_team' => $team['is_sm_team'],
                ];

                if (!$hasPrefix) {
                    $optionItem['label'] = $team->name;
                    $optionItem['prefix'] = $char;
                }
                $options[] = $optionItem;
                unset($teamList[$key]);
                $this->genDataTeam($options, $teamList, $team['id'], $char . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $hasPrefix);
            }
        }
    }

    public function getListOption($ownerTeamIds, $request, $employee, $scope)
    {
        $options = [];
        $teams = false;
        if ($request->team_id) {
            $teams = $ownerTeamIds->where('id', (int)$request->team_id)->first();
            if (!$teams) return [];
        } else {
            if ($scope == \Rikkei\Team\Model\Permission::SCOPE_TEAM || $scope == \Rikkei\Team\Model\Permission::SCOPE_SELF) {
                //$teams = $this->getTeamIsWorking($employee, false);
                $teams = $ownerTeamIds;
            }
        }
        if ($teams) {
            if ($teams instanceof \Illuminate\Database\Eloquent\Collection) {
                foreach ($teams as $team) {
                    $options[] = [
                        'label' => $team['name'],
                        'value' => $team['id'],
                        'leader_id' => $team['leader_id'],
                        'leader_email' => $team['leader_email'],
                        'sub_leader_email' => $team['sub_leader_email'],
                        'type' => $team['type'],
                        'is_soft_dev' => $team['is_soft_dev'],
                        'code' => $team['code'],
                        'parent_id' => $team['parent_id'],
                        'branch_code' => $team['branch_code'],
                        'is_bo' => $team['is_bo'],
                        'sort_order' => $team['sort_order'],
                        'is_branch' => $team['is_branch'],
                        'deleted_at' => $team['deleted_at'],
                        'is_sm_team' => $team['is_sm_team'],
                    ];
                    $this->genDataTeam($options, $ownerTeamIds, $team['id'], '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', true);
                }
            } else {
                $options[] = [
                    'label' => $teams['name'],
                    'value' => $teams['id'],
                    'leader_id' => $teams['leader_id'],
                    'leader_email' => $teams['leader_email'],
                    'sub_leader_email' => $teams['sub_leader_email'],
                    'type' => $teams['type'],
                    'is_soft_dev' => $teams['is_soft_dev'],
                    'code' => $teams['code'],
                    'parent_id' => $teams['parent_id'],
                    'branch_code' => $teams['branch_code'],
                    'is_bo' => $teams['is_bo'],
                    'sort_order' => $teams['sort_order'],
                    'is_branch' => $teams['is_branch'],
                    'deleted_at' => $teams['deleted_at'],
                    'is_sm_team' => $teams['is_sm_team'],
                ];
                $this->genDataTeam($options, $ownerTeamIds, $teams['id'], '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', true);
            }
            return $options;
        }

        $this->genDataTeam($options, $ownerTeamIds, null, '', true);

        return $options;
    }

    public function getEmployee($email)
    {
        return Employee::where('email', $email)->first();
    }

    public function getPermission($employee)
    {
        return Permission::getInstance($employee, true);
    }

    public function getTeamIdsSelected($email, $filter, &$collection, $projectFuture = false)
    {
        $employee = $this->getEmployee($email);
        $permission = $this->getPermission($employee);
        $isCompany = false;

        $tblTeamProject = TeamProject::getTableName();
        $tblProject = \Rikkei\Project\Model\Project::getTableName();

        $teamIdsAvailable = [];

        $teamPathTree = Team::getTeamPath(true);
        $teamFilters = isset($filter['team_id']) ? $filter['team_id'] : [];
        if ($permission->isScopeCompany()) {
            $isCompany = !$teamFilters;
        } elseif ($ownedTeamIds = $permission->isScopeTeam()) {
            //lựa chọn team giao nhau, đảm bảo filter team phải nằm trong team quản lý
            if ($teamFilters) {
                $teamFilters = array_intersect($teamFilters, $ownedTeamIds);
            } else {
                $teamFilters = $ownedTeamIds;
            }
        } elseif ($permission->isScopeSelf()) {
            $teamFilters = [];
            $collection = $collection->where( "{$tblProject}.manager_id", $employee->id);
            $isCompany = true;
        } else {
            return false;
        }
        if (!$isCompany) {
            //get toàn bộ team childs của filter teams
            foreach ($teamFilters as $teamId) {
                if (isset($teamPathTree[$teamId]) && $teamPathTree[$teamId]) {
                    if (isset($teamPathTree[$teamId]['child'])) {
                        $teamIdsAvailable = array_merge($teamFilters, $teamPathTree[$teamId]['child']);
                        unset($teamPathTree[$teamId]['child']);
                    }
                }
            }
            $teamIdsAvailable = array_merge($teamFilters, $teamIdsAvailable);

            if (!$projectFuture) {
                $collection->whereIn("{$tblTeamProject}.team_id", $teamIdsAvailable)
                    ->addSelect(
                        DB::raw("(SELECT GROUP_CONCAT(DISTINCT {$tblTeamProject}.team_id SEPARATOR ';')
                        FROM {$tblTeamProject}
                        WHERE {$tblTeamProject}.project_id = {$tblProject}.id
                        ) AS teams")
                    );
            } else {
                $collection->whereIn("team_id", $teamIdsAvailable)
                    ->addSelect(DB::raw("GROUP_CONCAT(DISTINCT team_id SEPARATOR ';') AS teams"));
            }
        } else {
            if (!$projectFuture) {
                $collection->addSelect(DB::raw("GROUP_CONCAT(DISTINCT {$tblTeamProject}.team_id SEPARATOR ';') AS teams"));
            } else {
                $collection->addSelect(DB::raw("GROUP_CONCAT(DISTINCT team_id SEPARATOR ';') AS teams"));
            }
        }
    }

    public static function initContinuousMonth($monthFrom, $monthTo)
    {
        $monthFrom = Carbon::parse($monthFrom);
        $monthTo = Carbon::parse($monthTo);
        $data = [];
        while ($monthFrom <= $monthTo) {
            $data[] = $monthFrom->format('Y-m');
            $monthFrom->addMonth(1);
        }

        return $data;
    }

    /**
     * Get danh sách dự án với thông tin cơ bản và approved production cost
     *
     * @param type $filter
     * @return boolean
     */
    public function getRevenueList($filter = [])
    {
        $defaultStateFilter = [
            ProjectModel::STATE_NEW,
            ProjectModel::STATE_PROCESSING,
            ProjectModel::STATE_PENDING,
            ProjectModel::STATE_CLOSED,
        ];
        $defaultTypeFilter = [
            ProjectModel::TYPE_OSDC,
            ProjectModel::TYPE_BASE,
            ProjectModel::TYPE_ONSITE,
        ];
        $stateFilter = isset($filter['state']) ? $filter['state'] : $defaultStateFilter;
        $typeFilter = isset($filter['type']) ? $filter['type'] : $defaultTypeFilter;
        $tblProject = ProjectModel::getTableName();
        $tblProjectFuture = ProjectAdditional::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmpAsManager = "employee_as_manager";
        $tblTeamProject = TeamProject::getTableName();
        $tblQualities = ProjQuality::getTableName();
        $tblSoftware = ToolAndInfrastructure::getTableName();
        $tblDeriveExpense = DevicesExpense::getTableName();


        //Lấy ra những project thích hợp theo state và type
        $collection = ProjectModel::leftJoin("{$tblEmployee} AS {$tblEmpAsManager}", "{$tblEmpAsManager}.id", '=', "{$tblProject}.manager_id")
            ->leftJoin($tblTeamProject, "{$tblTeamProject}.project_id", '=', "{$tblProject}.id")
            ->leftJoin($tblSoftware, "{$tblSoftware}.project_id", '=', "{$tblProject}.id")
            ->join($tblQualities, function ($query) use ($tblProject, $tblQualities) {
                $query->on("{$tblProject}.id", '=', "{$tblQualities}.project_id")
                    ->whereNull("{$tblQualities}.deleted_at")
                    ->where("{$tblQualities}.status", '=', ProjQuality::STATUS_APPROVED);
            })
            ->whereIn("{$tblProject}.state", $stateFilter)
            ->whereIn("{$tblProject}.type", $typeFilter)
            ->where("{$tblProject}.status", ProjectModel::STATUS_APPROVED)
            ->whereNull("{$tblSoftware}.deleted_at")
            ->groupBy("{$tblProject}.id")
            ->select([
                "{$tblProject}.id",
                "{$tblProject}.name",
                "{$tblEmpAsManager}.name AS pm",
                DB::raw("date_format({$tblProject}.start_at, '%Y-%m') as project_start_at"),
                DB::raw("date_format({$tblProject}.end_at, '%Y-%m') as project_end_at"),
                DB::raw("0 as amount"),
                DB::raw("0 as is_future"),
                "{$tblProject}.kind_id"
            ]);

        //Filter project id
        if (isset($filter['project_id'])) {
            $collection = $collection->whereIn("{$tblProject}.id", $filter['project_id']);
        }

        if (isset($filter['month_from'])) {
            $collection->whereRaw(DB::raw("date_format({$tblProject}.end_at, '%Y-%m') >= '{$filter['month_from']}'"));
        }

        if (isset($filter['month_to'])) {
            $collection->whereRaw(DB::raw("date_format({$tblProject}.start_at, '%Y-%m') <= '{$filter['month_to']}'"));
        }

        //Filter theo quyền và team
        if ($this->getTeamIdsSelected($filter['email'], $filter, $collection) === false) {
            return false;
        }

        //Lấy ra softwares tương ứng
        $collection->addSelect(DB::raw("GROUP_CONCAT(DISTINCT {$tblSoftware}.id SEPARATOR ';') AS softwares"));

        // Lấy ra những project future thích hợp theo type
        $collectionFuture = ProjectAdditional::select([
            "{$tblProjectFuture}.id",
            "{$tblProjectFuture}.name",
            DB::raw("null as pm"),
            DB::raw("MIN(concat(year, '-', LPAD(month, 2, 0))) AS project_start_at"),
            DB::raw("Max(concat(year, '-', LPAD(month, 2, 0))) as project_end_at"),
            DB::raw("0 as amount"),
            DB::raw("1 as is_future"),
            "{$tblProjectFuture}.kind_id"
        ])
            ->whereIn("{$tblProjectFuture}.type", $typeFilter)
            ->whereNull("{$tblProjectFuture}.deleted_at")
            ->groupBy("{$tblProjectFuture}.name");

        $teams = Team::query()->withTrashed();

        if (isset($filter['month_from'])) {
            $teams->where(function ($q) use ($filter) {
                $q->whereRaw(DB::raw("date_format(deleted_at, '%Y-%m') >= '{$filter['month_from']}'"))
                    ->orWhereNull('deleted_at');
            });
            $collectionFuture->whereRaw(DB::raw("CONCAT({$tblProjectFuture}.year , '-', LPAD({$tblProjectFuture}.month,2,0)) >= '{$filter['month_from']}'"));
        }

        if (isset($filter['month_to'])) {
            $collectionFuture->whereRaw(DB::raw("CONCAT({$tblProjectFuture}.year , '-', LPAD({$tblProjectFuture}.month,2,0)) <= '{$filter['month_to']}'"));
        }

        //Filter theo quyền và team của Project Future
        if ($this->getTeamIdsSelected($filter['email'], $filter, $collectionFuture, true) === false) {
            return false;
        }

        //Lấy ra softwares tương ứng của Project Future
        $collectionFuture->addSelect(DB::raw("null AS softwares"));

        //Kết thúc câu SQL bằng việc join Sql của Project với Sql Project Future
        $projects = $collection->union($collectionFuture)->get();

        $teams = $teams->get()->pluck('name', 'id')->toArray();
        //Chi phí phần mềm
        $softwares = ToolAndInfrastructure::whereNull('deleted_at');
        //chi phí khác
        $deriveExpense = DevicesExpense::select('id', 'project_id', DB::raw("date_format(time, '%Y-%m') as month"), DB::raw("sum(amount) as amount"))
            ->groupBy('project_id')
            ->groupBy('month');
        //Đơn giá của project
        $projectApproveCost = ProjectApprovedProductionCost::select('project_id', 'team_id', DB::raw('coalesce(unit_price, 1) as unit_price') ,'approved_production_cost', 'price', 'unapproved_price', DB::raw("concat(year, '-', LPAD(month, 2, 0)) as month"));
        //Đơn giá của Project Future
        $projectFutureApproveCost = ProjectAdditional::select('team_id', 'name', DB::raw('coalesce(unit_price, 1) as unit_price') , DB::raw("sum(approved_production_cost) as approved_production_cost"), DB::raw("sum(price) as price"), DB::raw("concat(year, '-', LPAD(month, 2, 0)) as month"));

        if (isset($filter['month_from'])) {
            $softwares->whereRaw(DB::raw("date_format({$tblSoftware}.start_date, '%Y-%m') >= '{$filter['month_from']}'"));
            $deriveExpense->whereRaw(DB::raw("date_format({$tblDeriveExpense}.time, '%Y-%m') >= '{$filter['month_from']}'"));
            $projectApproveCost->whereRaw(DB::raw("concat(year, '-', LPAD(month, 2, 0)) >= '{$filter['month_from']}'"));
            $projectFutureApproveCost->whereRaw(DB::raw("concat(year, '-', LPAD(month, 2, 0)) >= '{$filter['month_from']}'"));

        }
        if (isset($filter['month_to'])) {
            $softwares->whereRaw(DB::raw("date_format({$tblSoftware}.end_date, '%Y-%m') <= '{$filter['month_to']}'"));
            $deriveExpense->whereRaw(DB::raw("date_format({$tblDeriveExpense}.time, '%Y-%m') <= '{$filter['month_to']}'"));
            $projectApproveCost->whereRaw(DB::raw("concat(year, '-', LPAD(month, 2, 0)) <= '{$filter['month_to']}'"));
            $projectFutureApproveCost->whereRaw(DB::raw("concat(year, '-', LPAD(month, 2, 0)) <= '{$filter['month_to']}'"));
        }

        $softwaresCollection = $softwares->get();
        $softwaresGroupById = $softwaresCollection->groupBy('id')->toArray();
        $softwaresGroupBySoftwareId = $softwares->select(DB::raw('distinct(project_id)'), 'software_id')->get()->groupBy('software_id')->toArray();
        $deriveExpense = $deriveExpense->get()->groupBy('project_id')->toArray();
        $projectApproveCost = $projectApproveCost->get()->groupBy('project_id');
        $projectFutureApproveCost = $projectFutureApproveCost->groupBy('name')->groupBy('team_id')->groupBy('month')->get()->groupBy('team_id');

        //Member của project
        $memberOfProject = self::getMembersOfProject($filter['month_from'], $filter['month_to']);
        $memberOfProjectGroupbyProjectId = $memberOfProject->groupBy('project_id');

        $results = [];

        foreach ($projects as $project) {
            if (isset($filter['project_id'])) {
                if ($project->is_future == '1') continue;
            }
            //get amount of each project
            $project->amount = isset($deriveExpense[$project->id]) ? $deriveExpense[$project->id] : [];

            //get list software of each project
            $softwaresProjects = [];

            foreach (explode(';', $project->softwares) as $id) {
                if (isset($softwaresGroupById[$id])) {
                    $currentSoftwareArray = $softwaresGroupById[$id][0];
                    $softwaresProjects[] = [
                        'id' => $currentSoftwareArray['software_id'],
                        'name' => $currentSoftwareArray['soft_hard_ware'],
                        'startDate' => $currentSoftwareArray['start_date'],
                        'endDate' => $currentSoftwareArray['end_date'],
                        'projectUsing' => isset($softwaresGroupBySoftwareId[$currentSoftwareArray['software_id']]) ? count($softwaresGroupBySoftwareId[$currentSoftwareArray['software_id']]) : 0
                    ];
                }
            }

            $project->softwares = $softwaresProjects;
            $teamProjects = [];
            $costGroupByTeamId = isset($projectApproveCost[$project->id]) ? $projectApproveCost[$project->id]->groupBy('team_id') : [];
            $memberOfProjectGroupbyTeamId = isset($memberOfProjectGroupbyProjectId[$project->id]) ? $memberOfProjectGroupbyProjectId[$project->id]->groupBy('team_id') : [];

            //Loop for each team of project with pm not null
            if (!is_null($project->pm)) {
                foreach (explode(';', $project->teams) as $id) {
                    if (isset($teams[$id])) {
                        //get avalable month for each teams
                        $countMonths = self::initContinuousMonth(
                            max($project->project_start_at, isset($filter['month_from']) ? $filter['month_from'] : $project->project_start_at),
                            min($project->project_end_at, isset($filter['month_to']) ? $filter['month_to'] : $project->project_end_at)
                        );
                        //Cost for each project, group by month
                        $costGroupByMonth = (isset($costGroupByTeamId[$id])) ? $costGroupByTeamId[$id]->groupBy('month') : [];
                        // Group member of each team
                        $memberOfProjectGroupbyEmployeeId = (isset($memberOfProjectGroupbyTeamId[$id])) ? $memberOfProjectGroupbyTeamId[$id]->groupBy('employee_id')->toArray() : null;
                        $months = [];
                        foreach ($countMonths as $unitMonth) {
                            $employees = [];
                            if ($memberOfProjectGroupbyEmployeeId) {
                                //get Info for each member
                                foreach ($memberOfProjectGroupbyEmployeeId as $employeeId => $datas) {
                                    $employees[] = self::getAvgEffortEachMembers($datas, $unitMonth);
                                }
                            }
                            
                            //get cost
                            $arrCost = [];
                            if (isset($costGroupByMonth[$unitMonth])) {
                                foreach ($costGroupByMonth[$unitMonth] as $itemCost) {
                                    $objProjHelper = new ProjectHelper();
                                    $arrCost[] = [
                                        'mm' => $itemCost->approved_production_cost,
                                        'cost' => $objProjHelper->getPrice($itemCost->price, $itemCost->unapproved_price),
                                        'unit_price' => (is_null($itemCost->unit_price) || $itemCost->unit_price == 0) ? ProjectApprovedProductionCost::UNIT_PRICE_VND : (int)$itemCost->unit_price,
                                    ];
                                }
                            }
                            
                            $months[] = [
                                'month' => $unitMonth,
                                'arrCost' => $arrCost,
                                'employees' => $employees
                            ];
                        }
                        $teamProjects[] = [
                            'id' => $id,
                            'name' => $teams[$id],
                            'months' => $months
                        ];
                    }
                }
            } else {
                //Loop for each team of project get project future
                foreach (explode(';', $project->teams) as $id) {
                    if (isset($projectFutureApproveCost[$id])) {
                        //get avalable month for each teams
                        $countMonths = self::initContinuousMonth(
                            max($project->project_start_at, isset($filter['month_from']) ? $filter['month_from'] : $project->project_start_at),
                            min($project->project_end_at, isset($filter['month_to']) ? $filter['month_to'] : $project->project_end_at)
                        );
                        //Cost for each project, group by month
                        $costGroupByMonth = (isset($projectFutureApproveCost[$id])) ? $projectFutureApproveCost[$id]->groupBy('month') : [];
                        $months = [];
                        foreach ($countMonths as $unitMonth) {
                            if (isset($costGroupByMonth[$unitMonth])) {
                                $collectionCost = $costGroupByMonth[$unitMonth]->filter(function ($item) use ($project) {
                                    return  $project->name == $item->name;
                                });
                                $mm = 0;
                                foreach ($collectionCost as $cost) {
                                    $mm = $cost->approved_production_cost ? $cost->approved_production_cost : 0;
                                    break;
                                }
                            } else {
                                $mm = 0;
                            }

                            $months[] = [
                                'month' => $unitMonth,
                                'mm' =>  $mm,
                                'cost' => isset($costGroupByMonth[$unitMonth]) ? ((is_null($costGroupByMonth[$unitMonth][0]->price) || $costGroupByMonth[$unitMonth][0]->price == 0) ? 30000000 : $costGroupByMonth[$unitMonth][0]->price) : 30000000,
                                'unit_price' => isset($costGroupByMonth[$unitMonth]) ? ((is_null($costGroupByMonth[$unitMonth][0]->unit_price) || $costGroupByMonth[$unitMonth][0]->unit_price == 0) ? ProjectApprovedProductionCost::UNIT_PRICE_VND : (int)$costGroupByMonth[$unitMonth][0]->unit_price) : ProjectApprovedProductionCost::UNIT_PRICE_VND,
                                'employees' => []
                            ];
                        }
                        $teamProjects[] = [
                            'id' => $id,
                            'name' => $teams[$id],
                            'months' => $months
                        ];
                    }
                }
            }
            $project->teams = $teamProjects;
            $results[] = $project;
        }

        return $results;
    }

    public function getAvgEffortEachMembers($datas, $unitMonth)
    {
        if (!$unitMonth instanceof Carbon) {
            $unitMonth = Carbon::parse($unitMonth);
        }
        $monthStart = clone $unitMonth;
        $monthStart->startOfMonth();
        $monthEnd = clone $unitMonth;
        $monthEnd->lastOfMonth();

        // Total work days of month
        $daysInMonth = pView::getMM($monthStart, $monthEnd, 2);

        $totalEffort = 0;
        $bonusBase = 0;
        $bonusOSD = 0;
        $gridOSDCReward = [];

        foreach ($datas as $data) {
            $totalDays = 0;
            $startDateEffort = Carbon::parse($data['start_at']);
            $endDateEffort = Carbon::parse($data['end_at']);
            $teamStartDateEffort = $data['team_start_at'] ? Carbon::parse($data['team_start_at']) : null;
            $teamEndDateEffort = $data['team_end_at'] ? Carbon::parse($data['team_end_at']) : null;

            if (($teamStartDateEffort && $teamStartDateEffort->gt($monthEnd)) || ($teamEndDateEffort && $teamEndDateEffort->lt($monthStart))) continue;

            if ($startDateEffort->gt($monthEnd) || $endDateEffort->lt($monthStart)) continue;

            $rangeDateEffortStart = max($monthStart, $startDateEffort);
            $rangeDateEffortEnd = min($monthEnd, $endDateEffort);

            $model = new ResourceView();
            // Total work days in month of employee
            $diffInDays = $model->getRealDaysOfMonth($unitMonth->month, $unitMonth->year, $rangeDateEffortStart, $rangeDateEffortEnd);
            $effort =  $diffInDays * $data['effort'];
            $totalEffort += $daysInMonth ? $effort / $daysInMonth : 0;

            //calculate total reward base
            if ($unitMonth->format('Y-m') == $data['project_end_at']) {
                $bonusBase += $data['base_reward'];
            }

            //calculate total osdc
            if (count($gridOSDCReward) < 1) {
                if ($data['group_month_me_reward']) {
                    $explodeGroupOsdcReward = explode(',', $data['group_month_me_reward']);

                    if ($explodeGroupOsdcReward) {
                        foreach ($explodeGroupOsdcReward as $item) {
                            $item = explode(':', $item);
                            if (is_array($item) && count($item) >= 2) {
                                $gridOSDCReward[] = [
                                    $item[0] => $item[1]
                                ];
                            }
                        }
                    }
                }
            }
            if (isset($gridOSDCReward[$unitMonth->format('Y-m')])) {
                $bonusOSD += $gridOSDCReward[$unitMonth->format('Y-m')];
            }
        }


        return [
            'employee_id' => $datas[0]['employee_id'],
            'employee_code' => $datas[0]['employee_code'],
            'employee_name' => $datas[0]['employee_name'],
            'effort' => number_format($totalEffort, 2),
            'bonus' => $bonusOSD + $bonusBase
        ];
    }

    public function getMembersOfProject($monthFrom, $monthTo)
    {
        if (!$monthFrom instanceof Carbon) {
            $monthFrom = Carbon::parse($monthFrom);
        }
        if (!$monthTo instanceof Carbon) {
            $monthTo = Carbon::parse($monthTo);
        }

        $empTbl = Employee::getTableName();
        $pjmTbl = ProjectMember::getTableName();
        $tblRewardBase = 'reward_project_bases';
        $tblRewardOSDC = 'reward_project_osdc';

        $monthFrom = MeView::getBaselineRangeTime($monthFrom)['start'];
        $monthTo = MeView::getBaselineRangeTime($monthTo)['end'];
        $employeeTeamHistory = EmployeeTeamHistory::getTableName();
        $statusOSDCApprove = MeReward::STT_APPROVE;

        $sqlGetRewardBase = "(select proj_reward_employees.employee_id, tasks.project_id, sum(COALESCE(proj_reward_employees.reward_approve, 0)) as base_reward from proj_reward_employees
                            inner join tasks on tasks.id = proj_reward_employees.task_id
                            group by proj_reward_employees.employee_id) as {$tblRewardBase}";

        $sqlGetRewardOSDC = "(select me_evaluations.project_id, me_evaluations.employee_id,  
                            group_concat(concat(date_format( me_evaluations.eval_time, '%Y-%m'), ':', COALESCE(me_reward.reward_approve, 0))  SEPARATOR ',') as group_month_me_reward
                            from me_reward
                            inner join me_evaluations on me_evaluations.id = me_reward.eval_id
                            where me_reward.status = {$statusOSDCApprove}
                            group by project_id, employee_id) as {$tblRewardOSDC}";

        $collection = Employee::join($pjmTbl . ' as pjm', $empTbl . '.id', '=', 'pjm.employee_id')
            ->join(ProjectModel::getTableName() . ' as proj', function ($join) {
                $join->on('proj.id', '=', 'pjm.project_id')
                    ->whereNull('proj.deleted_at');
            })
            ->whereDate('pjm.start_at', '<=', $monthTo->toDateString())
            ->whereDate('pjm.end_at', '>=',$monthFrom->toDateString())
            ->whereNotIn('pjm.type', [ProjectMember::TYPE_PQA, ProjectMember::TYPE_COO])
            ->where('pjm.status', ProjectMember::STATUS_APPROVED);

//
        $collection->leftJoin("{$employeeTeamHistory}", "{$empTbl}.id", '=', "{$employeeTeamHistory}.employee_id")
            ->where(function ($q) use ($employeeTeamHistory, $monthTo) {
                $q->whereDate("{$employeeTeamHistory}.start_at", '<=', $monthTo)
                    ->orWhereNull("{$employeeTeamHistory}.start_at");
            })
            ->where(function ($q) use ($employeeTeamHistory, $monthFrom) {
                $q->whereDate("{$employeeTeamHistory}.end_at", '>=', $monthFrom)
                    ->orWhereNull("{$employeeTeamHistory}.end_at");
            });
//
        $collection->leftJoin(DB::raw($sqlGetRewardBase), function ($q) use ($tblRewardBase, $pjmTbl) {
            $q->on("{$tblRewardBase}.project_id", '=', "pjm.project_id");
            $q->on("{$tblRewardBase}.employee_id", '=', "pjm.employee_id");
        })->leftJoin(DB::raw($sqlGetRewardOSDC), function ($q) use ($tblRewardOSDC, $pjmTbl) {
            $q->on("{$tblRewardOSDC}.project_id", '=', "pjm.project_id");
            $q->on("{$tblRewardOSDC}.employee_id", '=', "pjm.employee_id");
        });

        $collection->select(
            $empTbl . '.id as employee_id',
            $empTbl . '.name as employee_name',
            DB::raw("{$employeeTeamHistory}.team_id as team_id"),
            DB::raw("{$employeeTeamHistory}.start_at as team_start_at"),
            DB::raw("{$employeeTeamHistory}.end_at as team_end_at"),
            DB::raw("{$tblRewardBase}.base_reward"),
            DB::raw("{$tblRewardOSDC}.group_month_me_reward"),
            $empTbl . '.employee_card_id',
            $empTbl . '.employee_code',
            'pjm.effort',
            'pjm.project_id',
            $empTbl . '.name',
            $empTbl . '.email',
            'pjm.start_at',
            'pjm.end_at',
            'proj.name as proj_name',
            DB::raw("date_format(proj.end_at, '%Y-%m') as project_end_at")
        )
            ->orderBy('project_id', 'asc')
            ->orderBy('employee_id', 'asc');

        return $collection->get();
    }
}
