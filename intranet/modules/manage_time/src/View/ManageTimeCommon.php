<?php

namespace Rikkei\ManageTime\View;

use Carbon\Carbon;
use DB;
use Lang;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\User;
use Rikkei\Files\Model\ManageFileText;
use Rikkei\ManageTime\Model\ComeLateDayWeek;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Team\Model\Permission as PermissionModel;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamConst;

class ManageTimeCommon
{
    /**
     * Team List to option
     *
     * @param int|null $skipId
     * @param boolean $isFunction
     * @param boolean $valueNull
     * @return array
     */
    public static function toOption($parentId = null, $skipId = null, $isFunction = false, $valueNull = true)
    {
        $options = [];
        if ($valueNull) {
            $options[] = [
                'label' => Lang::get('team::view.--Please choose--'),
                'value' => '',
                'option' => '',
            ];
        }
        if ($parentId) {
            $team = Team::find($parentId);
            $options[] = [
                'label' => $team->name,
                'value' => $team->id,
                'option' => '',
            ];

            self::toOptionFunctionRecursive($options, $parentId, $skipId, $isFunction, $level = 1);
        } else {
            self::toOptionFunctionRecursive($options, $parentId, $skipId, $isFunction, $level = 0);
        }

        return $options;
    }

    /**
     * Team list to option recuresive call all child
     *
     * @param array $options
     * @param int $parentId
     * @param int|null $skipId
     * @param boolean $isFunction
     * @param int $level
     */
    protected static function toOptionFunctionRecursive(&$options, $parentId, $skipId, $isFunction, $level)
    {
        $teamList = Team::select('id', 'name', 'parent_id', 'is_function', 'follow_team_id', 'leader_id', 'is_soft_dev')
                ->where('parent_id', $parentId)
                ->orderBy('sort_order', 'asc');
        if ($skipId) {
            $teamList = $teamList->where('id', '<>', $skipId);
        }
        $teamList = $teamList->get();
        $countCollection = count($teamList);
        if (!$countCollection) {
            return;
        }
        $prefixLabel = '';
        for ($i = 0; $i < $level; $i++) {
            $prefixLabel .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        foreach ($teamList as $team) {
            $optionMore = '';
            $leaderName = '';
            if($team->leader_id) {
                $leader = Employee::getEmpById($team->leader_id);
                if ($leader) {
                    $leaderName = $leader->name;
                }
            }
            $options[] = [
                'label' => $prefixLabel . $team->name,
                'value' => $team->id,
                'option' => $optionMore,
                'leader_name' => $leaderName,
                'leader_id' => $team->leader_id,
                'is_soft_dev' => $team->is_soft_dev,
            ];
            self::toOptionFunctionRecursive($options, $team->id, $skipId, $isFunction, $level + 1);
        }
    }

    /**
     * [getApproversForEmployee: get approver list for employee]
     * @param  [int] $employeeId
     * @return [array]
     */
    public static function getApproversForEmployee($employeeId, $teamIds = [])
    {
        $teamTable = Team::getTableName();
        $teamTableAs = $teamTable;
        $employeeTable = Employee::getTableName();
        $employeeTableAs = $employeeTable;
        $teamMemberTable = TeamMember::getTableName();
        $teamMemberTableAs = $teamMemberTable;

        $userCurrent = Permission::getInstance()->getEmployee();
        if ($userCurrent->id == $employeeId) {
            $employee = $userCurrent;
        } else {
            $employee = Employee::find($employeeId);
        }

        if (!$employee) {
            return null;
        }

        $roleIds = [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER];
        if ($employee->isLeader()) {
            $isLeaderBOD = TeamMember::join("{$teamTable}", "{$teamTable}.id", "=", "{$teamMemberTable}.team_id")
                ->where("{$teamTable}.code", TeamConst::CODE_BOD)
                ->where("{$teamMemberTable}.employee_id", $employee->id)
                ->count();
            if ($isLeaderBOD) {
                $roleIds = [Team::ROLE_SUB_LEADER];
            }
            return TeamMember::select("{$employeeTableAs}.id as id", "{$employeeTableAs}.name as name", "{$employeeTableAs}.email as email")
                ->join("{$employeeTable} as {$employeeTableAs}", "{$employeeTableAs}.id", '=', "{$teamMemberTableAs}.employee_id")
                ->join("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", '=', "{$teamMemberTableAs}.team_id")
                ->where("{$teamTableAs}.code", TeamConst::CODE_BOD)
                ->whereIn("{$teamMemberTableAs}.role_id", $roleIds)
                ->whereNull("{$employeeTableAs}.leave_date")
                ->get();
        }

        $isSubLeader = self::isSubLeader($employeeId);
        if ($isSubLeader) {
            $roleIds = [Team::ROLE_TEAM_LEADER];
        }

        if (empty($teamIds)) {
            $teamIds = TeamMember::where('employee_id', $employee->id)->pluck('team_id')->toArray();
        }

        return TeamMember::select("{$employeeTableAs}.id as id", "{$employeeTableAs}.name as name", "{$employeeTableAs}.email as email")
            ->join("{$employeeTable} as {$employeeTableAs}", "{$employeeTableAs}.id", '=', "{$teamMemberTableAs}.employee_id")
            ->join("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", '=', "{$teamMemberTableAs}.team_id")
            ->whereIn("{$teamMemberTableAs}.team_id", $teamIds)
            ->whereIn("{$teamMemberTableAs}.role_id", $roleIds)
            ->where(function ($query) {
                $query->whereNull("employees.leave_date")
                    ->orWhere('employees.leave_date', '>', Carbon::now()->format('Y-m-d'));
            })
            ->get();
    }

    public static function isSubLeader($employeeId)
    {
        return TeamMember::where('employee_id', $employeeId)
            ->where('role_id', Team::ROLE_SUB_LEADER)
            ->count();
    }

    public static function getPMByEmployee($employeeId)
    {
        $projectTable = Project::getTableName();
        $projectTableAs = $projectTable;
        $employeeTable = Employee::getTableName();
        $employeeTableAs = $employeeTable;
        $projectMemberTable = ProjectMember::getTableName();
        $projectMemberTableAs = $projectMemberTable;

        $pmList = Project::select("{$employeeTableAs}.id as employee_id", "{$employeeTableAs}.name as employee_name", "{$employeeTableAs}.email as employee_email")
            ->join("{$projectMemberTable} as {$projectMemberTableAs}", "{$projectMemberTableAs}.project_id", '=', "{$projectTable}.id")
            ->join("{$employeeTable} as {$employeeTableAs}", "{$employeeTableAs}.id", '=', "{$projectTable}.manager_id")
            ->where("{$projectMemberTableAs}.employee_id", $employeeId)
            ->where("{$projectTable}.state", Project::STATE_PROCESSING)
            ->whereNull("{$employeeTableAs}.leave_date")
            ->distinct()
            ->get();

        return $pmList;
    }

    /**
     * [getRegistrantInformation: get info of registrant]
     * @param  [int] $employeeId
     * @return [type]
     */
    public static function getRegistrantInformation($employeeId)
    {
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';
        $employeeTable = Employee::getTableName();
        $employeeTableAs = 'employee_table';
        $teamMemberTable = TeamMember::getTableName();
        $teamMemberTableAs = $teamMemberTable;

        if (empty($employeeId)) {
            return null;
        }
        return TeamMember::select(
            "{$employeeTableAs}.id as id",
            "{$employeeTableAs}.employee_code as employee_code",
            "{$employeeTableAs}.name as employee_name",
            "{$employeeTableAs}.email as employee_email",
            DB::raw("GROUP_CONCAT(DISTINCT " . "CONCAT(`{$roleTableAs}`.`role`, ' - ', `{$teamTableAs}`.`name`)" . " SEPARATOR'; ')" . " as role_name"))
            ->join("{$employeeTable} as {$employeeTableAs}", "{$employeeTableAs}.id", '=', "{$teamMemberTableAs}.employee_id")
            ->join("{$roleTable} as {$roleTableAs}", "{$roleTableAs}.id", '=', "{$teamMemberTableAs}.role_id")
            ->join("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", '=', "{$teamMemberTableAs}.team_id")
            ->where("{$teamMemberTableAs}.employee_id", $employeeId)
            ->first();
    }

    /**
     * [getTeamsOfEmployee: get all team of employee]
     * @param  [int] $employeeId
     * @return [array]
     */
    public static function getTeamsOfEmployee($employeeId)
    {
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';
        $employeeTable = Employee::getTableName();
        $employeeTableAs = 'employee_table';
        $teamMemberTable = TeamMember::getTableName();
        $teamMemberTableAs = $teamMemberTable;

        if (empty($employeeId)) {
            return null;
        }

        $teams = TeamMember::select("{$teamTableAs}.id as id", "{$teamTableAs}.name as name", "{$teamMemberTableAs}.role_id as role_id")
                ->join("{$employeeTable} as {$employeeTableAs}", "{$employeeTableAs}.id", '=', "{$teamMemberTableAs}.employee_id")
                ->join("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", '=', "{$teamMemberTableAs}.team_id")
                ->where("{$teamMemberTableAs}.employee_id", $employeeId)
                ->whereNull("{$teamTableAs}.deleted_at")
                ->get();

        return $teams;
    }

    /**
     * get team child recursive
     * 
     * @param array $teamPaths
     * @param null|int $teamId
     */
    public static function getTeamChildRecursive(&$teamPaths = [], $teamId = null, $withTrashed = false)
    {
        if (! $teamId) {
            return;
        }
        $teamChildren = Team::select('id', 'parent_id')
            ->where('parent_id', $teamId);
        if ($withTrashed) {
            $teamChildren = $teamChildren->withTrashed();
        }
        $teamChildren = $teamChildren->get();
        if (! count($teamChildren)) {
            return;
        }
        foreach ($teamChildren as $item) {
            $teamPaths[] = (int) $item->id;
            self::getTeamChildRecursive($teamPaths, $item->id, true);
        }
    }

    /**
     * Get team child list by employee
     * @param int $employeeId
     * @return array teamId
     */
    public static function getArrTeamIdByEmployee($employeeId)
    {
        $teamMembersModel = new TeamMember();
        $teamMembers = $teamMembersModel->getTeamMembersByEmployee($employeeId);
        
        //get teams of current user
        $arrTeamIdTemp = [];
        foreach ($teamMembers as $item) {
            $arrTeamIdTemp[] = self::getTeamChild($item->team_id);
        }
        
        $arrTeamId = [];
        $countArrTeamIdTemp = count($arrTeamIdTemp);
        for ($i = 0; $i < $countArrTeamIdTemp; $i++) {
            $countArrTeamIdTempIndex = count($arrTeamIdTemp[$i]);
            for ($j = 0; $j < $countArrTeamIdTempIndex; $j++) {
                $arrTeamId[] = $arrTeamIdTemp[$i][$j];
            }
        }
        return $arrTeamId;
    }

    /**
     * Get team child list by teamId
     * @param int $teamId
     * @return array teamId
     */
    public static function getTeamChild($teamId)
    {
        $arrTeamId = [];
        $arrTeamId[] = $teamId;
        $model = new Team();
        $teamChilds = $model->getTeamByParentIdNoTrashed($teamId);
        
        if (count($teamChilds)) {
            foreach ($teamChilds as $child) {
                $arrTeamId[] = $child->id;
                if (!self::isTeamChildLowest($child->id)) {
                    $childs = self::getTeamChild($child->id);
                    $count = count($childs);
                    for ($i = 0; $i < $count; $i++) {
                        $arrTeamId[] = $childs[$i];
                    }
                }
            }
        }
        return $arrTeamId;
    }

    /**
     * Check is team child lowest
     * @param int $teamId
     * @return boolean
     */
    public static function isTeamChildLowest($teamId)
    {
        $model = new Team();
        $teamChilds = $model->getTeamByParentIdNoTrashed($teamId);
        if (count($teamChilds)) {
            return false;
        }
        
        return true;
    }

    public static function getTeamIdIsScopeTeam($employeeId, $route)
    {
        $teams = self::getTeamsOfEmployee($employeeId);

        $teamIds = [];

        if ($teams) {
            foreach ($teams as $team) {
                if (Permission::getInstance()->isScopeTeam($team->id, $route)) {
                    $teamIds[] = $team->id;
                }
            }
        }

        return $teamIds;
    }

    public static function isMemberOfTeam($employeeId, $teamCode)
    {
        $teamTable = Team::getTableName();
        $teamMemberTable = TeamMember::getTableName();

        $isMemberOfTeam = TeamMember::join("{$teamTable}", "{$teamTable}.id", "=", "{$teamMemberTable}.team_id")
            ->where("{$teamTable}.code", $teamCode)
            ->where("{$teamMemberTable}.employee_id", $employeeId)
            ->count() ? true : false;

        return $isMemberOfTeam;
    }

    /**
     * [searchEmployee: search employee by email or name from key search]
     * @param  [string] $keySearch
     * @return [array]
     */
    public static function searchEmployee($keySearch)
    {
        return Employee::where(function ($query) use ($keySearch) {
            $query->where('email','like', '%' . $keySearch .'%')
                  ->orwhere('name','like', '%' . $keySearch .'%');
        })
        ->where(function ($query) {
            $query->orWhereNull('leave_date')
                ->orWhereDate('leave_date', '>=', Carbon::now()->format('Y-m-d'));
        })
        ->get();
    }

    /**
     * Get nick name from email
     * @param string $name
     * @return string
     */
    public static function getNickName($name, $ucfirst = false)
    {
        $nickName = strtolower(preg_replace('/@.*/', '', $name));
        if ($ucfirst) {
            $nickName = ucfirst($nickName);
        }
        return $nickName;
    }

    /**
     * Search employee ajax
     * @param [string] $keySearch
     * @param [array] $config
     * @param [int|null] $type [null: nv đang làm việc, 1: all (cả nhân viên đã nghỉ)]
     * @return [array]
     */
    public static function searchEmployeeAjax($keySearch, array $config = [], $type = null)
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 5,
        ];

        $tblEmployee = Employee::getTableName();
        $tblUser = User::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $config = array_merge($configDefault, $config);
        $now = Carbon::now();
        $collection = Employee::select("{$tblEmployee}.id", "{$tblEmployee}.employee_code", "{$tblEmployee}.name", "{$tblEmployee}.email", "{$tblUser}.avatar_url")
            ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", '=', "{$tblEmployee}.id")
            ->where(function ($query) use ($tblEmployee, $keySearch) {
                $query->orWhere("{$tblEmployee}.email", 'LIKE', '%' . $keySearch . '%')
                    ->orWhere("{$tblEmployee}.name", 'LIKE', '%' . $keySearch . '%');
            })
            ->orderBy("{$tblEmployee}.id")
            ->distinct("{$tblEmployee}.id");
        if (!$type) { 
            $collection->where(function ($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            });
        }
        Employee::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'employee_code' => $item->employee_code,
                'employee_name' => $item->name,
                'text' => $item->name . ' (' . self::getNickName($item->email) . ')',
                'avatar_url' => $item->avatar_url,
            ];
        }
        return $result;
    }

/**
     * Search employee ajax
     * @param [string] $keySearch
     * @param [array] $config
     * @param [int|null] $type [null: nv đang làm việc, 1: all (cả nhân viên đã nghỉ)]
     * @return [array]
     */
    public static function searchEmployeeOtDisallowAjax($keySearch, array $config = [], $type = null, $team = null)
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 5,
        ];

        $tblEmployee = Employee::getTableName();
        $tblUser = User::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $config = array_merge($configDefault, $config);
        $now = Carbon::now();
        $collection = Employee::select("{$tblEmployee}.id", "{$tblEmployee}.employee_code", "{$tblEmployee}.name", "{$tblEmployee}.email", "{$tblUser}.avatar_url")
            ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", '=', "{$tblEmployee}.id")
            ->where(function ($query) use ($tblEmployee, $keySearch) {
                $query->orWhere("{$tblEmployee}.email", 'LIKE', '%' . $keySearch . '%')
                    ->orWhere("{$tblEmployee}.name", 'LIKE', '%' . $keySearch . '%');
            })
            ->orderBy("{$tblEmployee}.id")
            ->distinct("{$tblEmployee}.id");
        if (!$type) { 
            $collection->where(function ($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            });
        }
        if ($team) {
            $collection->where("{$tblTeamMember}.team_id", $team);
        }
        Employee::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'employee_code' => $item->employee_code,
                'employee_name' => $item->name,
                'text' => $item->name . ' (' . self::getNickName($item->email) . ')',
                'avatar_url' => $item->avatar_url,
            ];
        }
        return $result;
    }    

    /*
     * Search employees can approve
     */
    public static function searchEmployeesCanApprove($keySearch, $employeeId = null, $route = null)
    {
        $employeesCanApprove = [];
        $checkDuplicate = [];
        $tblAction = Action::getTableName();
        $tblPermission  = PermissionModel::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMemberAs = 'tbl_team_members';
        $tblEmployee = Employee::getTableName();
        $tblEmployeeRole = EmployeeRole::getTableName();
        $tblUser = User::getTableName();
        $now = Carbon::now();

        $employeesCanApproveByRole = PermissionModel::select("{$tblEmployeeRole}.employee_id", "{$tblEmployee}.name as employee_name", "{$tblEmployee}.email as employee_email", "{$tblUser}.avatar_url")
            ->join("{$tblAction}", "{$tblAction}.parent_id", "=", "{$tblPermission}.action_id")
            ->join("{$tblEmployeeRole}", "{$tblEmployeeRole}.role_id", "=", "{$tblPermission}.role_id")
            ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblEmployeeRole}.employee_id")
            ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
            ->where("{$tblPermission}.scope", "!=", PermissionModel::SCOPE_NONE)
            ->where("{$tblAction}.route", $route)
            ->whereNull("{$tblPermission}.team_id")
            ->whereNull("{$tblEmployee}.deleted_at")
            ->where(function ($query) use ($tblEmployee, $keySearch) {
                $query->orWhere("{$tblEmployee}.email", 'LIKE', '%' . $keySearch . '%')
                    ->orWhere("{$tblEmployee}.name", 'LIKE', '%' . $keySearch . '%');
            })
            ->where(function ($query) use ($tblEmployee, $now) {
                $query->orWhereNull("{$tblEmployee}.leave_date")
                    ->orWhereDate("{$tblEmployee}.leave_date", '>=', $now->format('Y-m-d'));
            });
        if ($employeeId) {
            $employeesCanApproveByRole = $employeesCanApproveByRole->where("{$tblEmployee}.id", "!=", $employeeId);
        }
        $employeesCanApproveByRole = $employeesCanApproveByRole->groupBy("{$tblEmployeeRole}.employee_id")
            ->orderBy("{$tblEmployee}.id")
            ->distinct("{$tblEmployee}.id")
            ->get();
        if (count($employeesCanApproveByRole)) {
            foreach ($employeesCanApproveByRole as $emp) {
                if (isset($checkDuplicate[$emp->employee_id])) {
                    continue;
                }
                $employeesCanApprove['items'][] = [
                    'id' => $emp->employee_id,
                    'text' => $emp->employee_name . ' (' . self::getNickName($emp->employee_email) . ')',
                    'avatar_url' => $emp->avatar_url,
                ];
                $checkDuplicate[$emp->employee_id] = true;
            }
        }

        $teamsCanApprove = PermissionModel::select("{$tblPermission}.team_id", "{$tblPermission}.role_id")
            ->join("{$tblAction}", "{$tblAction}.parent_id", "=", "{$tblPermission}.action_id")
            ->join("{$tblTeamMember}", "{$tblTeamMember}.team_id", "=", "{$tblPermission}.team_id")
            ->where("{$tblPermission}.scope", "!=", PermissionModel::SCOPE_NONE)
            ->where("{$tblAction}.route", $route)
            ->whereNotNull("{$tblPermission}.team_id")
            ->groupBy(["{$tblPermission}.role_id", "{$tblPermission}.team_id"])
            ->orderBy("{$tblPermission}.team_id")
            ->get();
        if (count($teamsCanApprove)) {
            foreach ($teamsCanApprove as $team) {
                $employeesCanApproveByTeam = TeamMember::select("{$tblTeamMember}.employee_id", "{$tblTeamMember}.team_id", "{$tblEmployee}.name as employee_name", "{$tblEmployee}.email as employee_email", "{$tblUser}.avatar_url")
                    ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblTeamMember}.employee_id")
                    ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
                    ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
                    ->whereNull("{$tblEmployee}.deleted_at")
                    ->where(function ($query) use ($tblTeam, $team) {
                        $query->orWhere("{$tblTeam}.id", $team->team_id)
                            ->orWhere("{$tblTeam}.follow_team_id", $team->team_id);
                    })
                    ->where("{$tblTeamMember}.role_id", $team->role_id)
                    ->where(function ($query) use ($tblEmployee, $keySearch) {
                        $query->orWhere("{$tblEmployee}.email", 'LIKE', '%' . $keySearch . '%')
                            ->orWhere("{$tblEmployee}.name", 'LIKE', '%' . $keySearch . '%');
                    })
                    ->where(function ($query) use ($tblEmployee, $now) {
                        $query->orWhereNull("{$tblEmployee}.leave_date")
                            ->orWhereDate("{$tblEmployee}.leave_date", '>=', $now->format('Y-m-d'));
                    });

                if ($employeeId) {
                    $employeesCanApproveByTeam = $employeesCanApproveByTeam->where("{$tblEmployee}.id", "!=", $employeeId);
                }
                $employeesCanApproveByTeam = $employeesCanApproveByTeam->orderBy("{$tblEmployee}.id")
                    ->distinct("{$tblEmployee}.id")
                    ->get();
                if (count($employeesCanApproveByTeam)) {
                    foreach ($employeesCanApproveByTeam as $emp) {
                        if (isset($checkDuplicate[$emp->employee_id])) {
                            continue;
                        }
                        $employeesCanApprove['items'][] = [
                            'id' => $emp->employee_id,
                            'text' => $emp->employee_name . ' (' . self::getNickName($emp->employee_email) . ')',
                            'avatar_url' => $emp->avatar_url,
                        ];
                        $checkDuplicate[$emp->employee_id] = true;
                    }
                }
            }
        }

        if (!empty(Config('services.account_root'))) {
            $rootAcc = Employee::getEmpByEmail(Config('services.account_root'));
            if (!count($employeesCanApprove) || (count($employeesCanApprove) &&!static::hasRootInApprovers($employeesCanApprove['items'], $rootAcc))) {
                $employeesCanApprove['items'][] = [
                    'id' => $rootAcc->id,
                    'text' => $rootAcc->name . ' (' . self::getNickName($rootAcc->email) . ')',
                    'avatar_url' => User::where('employee_id', $rootAcc->id)->first()->avatar_url,
                ];
            }
        }

        return $employeesCanApprove;
    }
    
    public static function hasRootInApprovers($approvers, $rootAcc)
    {
        foreach ($approvers as $approver) {
            if ($approver['id'] == $rootAcc->id) {
                return true;
            }
        }
        return false;
    }

    /**
     * [pushEmailToQueue: push email to queue]
     * @param $data
     * @param $template
     * @param bool $setNotify
     * @param array $notificationData
     * @return bool [boolean]
     * @throws \Exception
     */
    public static function pushEmailToQueue($data, $template, $setNotify = false, $notificationData = [])
    {
        $subject = $data['mail_title'];
        $emailQueue = new EmailQueue();
        if (!empty($data['mail_cc'])) {
            foreach ($data['mail_cc'] as $valueMailCc) {
                $emailQueue->addCc($valueMailCc->emailCc);
                $emailQueue->addCcNotify($valueMailCc->id);
            };
        }
        if (!empty($data['file'])) {
            $file = $data['file'];
            $filePath = storage_path(ManageFileText::PATH_FILE . $file);
            $emailQueue->setTo($data['mail_to'])
                   ->setSubject($subject)
                   ->setTemplate($template, $data)
                   ->addAttachment($filePath, false);
        } else {
            $emailQueue->setTo($data['mail_to'])
                   ->setSubject($subject)
                   ->setTemplate($template, $data);
        }
        if ($setNotify) {
            $notifyData = [];
            if(isset($data['actor_id']))
            {
                $notifyData['actor_id'] = $data['actor_id'];
            }
            $notifyData['category_id'] = !empty($notificationData['category_id']) ? $notificationData['category_id'] : RkNotify::CATEGORY_OTHER;
            $emailQueue->setNotify($data['to_id'], $data['noti_content'], $data['link'], $notifyData);
        }
        try {
            $emailQueue->save();
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * [limitText: Cut string by words number]
     * @param  [string] $text  
     * @param  [int]    $limit 
     * @return [string]        
     */
    public static function limitText($text, $limit) 
    {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos = array_keys($words);
            $text = substr($text, 0, $pos[$limit]) . '...';
        }

        return $text;
    }

    /**
     * [convertApplyDaysToString: convert days apply to string]
     * @param  [type] $applyDays
     * @return [string]
     */
    public static function convertApplyDaysToString($applyDays)
    {
        $days = explode(';', $applyDays);
        $applyDaysList = array();
        foreach ($days as $key => $value) {
            switch($value) {
                case ComeLateDayWeek::MONDAY:
                    $applyDaysList[] = Lang::get('manage_time::view.Monday'); 
                    break;
                case ComeLateDayWeek::TUESDAY:
                    $applyDaysList[] = Lang::get('manage_time::view.Tuesday'); 
                    break;
                case ComeLateDayWeek::WEDNESDAY:
                    $applyDaysList[] = Lang::get('manage_time::view.Wednesday'); 
                    break;
                case ComeLateDayWeek::THURSDAY:
                    $applyDaysList[] = Lang::get('manage_time::view.Thursday'); 
                    break;
                case ComeLateDayWeek::FRIDAY:
                    $applyDaysList[] = Lang::get('manage_time::view.Friday'); 
                    break;
                default:
                    break;
            }
        }

        if (count($applyDaysList) == 5) {
            $applyDaysString = Lang::get('manage_time::view.Apply to all days of week');
        } else {
            $applyDaysString = implode('; ', $applyDaysList);
        }

        return $applyDaysString;
    }

    /**
     * [getDateRange: get dates range list]
     * @param  [Carbon] $startDate
     * @param  [Carbon] $endDate
     * @return [array]
     */
    public static function getDateRange($startDate, $endDate)
    {
        if (!$startDate || !$endDate) {
            return null;
        }
        $dates = [];
        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');

        while (strtotime($startDate) <= strtotime($endDate)) {
                    $dates[] = Carbon::parse($startDate);
                    $startDate = date ("Y-m-d", strtotime("+1 day", strtotime($startDate)));
        }

        return $dates;
    }

    /**
     * [getDayOfWeek: get day of week from a date]
     * @param  [type] $date
     * @return [type]
     */
    public static function getDayOfWeek($date)
    {
        $dateFormatDayAndMonth = $date->format('d/m');
        $dayOfWeek = $date->dayOfWeek;

        if ($dayOfWeek == ManageTimeConst::SUNDAY) {
            return $dateFormatDayAndMonth . '<br>CN';
        } elseif ($dayOfWeek == ManageTimeConst::MONDAY) {
            return $dateFormatDayAndMonth . '<br>T2';
        } elseif ($dayOfWeek == ManageTimeConst::TUESDAY) {
            return $dateFormatDayAndMonth . '<br>T3';
        } elseif ($dayOfWeek == ManageTimeConst::WEDNESDAY) {
            return $dateFormatDayAndMonth . '<br>T4';
        } elseif ($dayOfWeek == ManageTimeConst::THURSDAY) {
            return $dateFormatDayAndMonth . '<br>T5';
        } elseif ($dayOfWeek == ManageTimeConst::FRIDAY) {
            return $dateFormatDayAndMonth . '<br>T6';
        } else {
            return $dateFormatDayAndMonth . '<br>T7';
        }
    }

    /**
     * [setDefautDateFilter: set default date filter]
     */
    public static function setDefautDateFilter() {
        return [
            Carbon::today()->startOfMonth(),
            Carbon::today()->endOfMonth(),
            Carbon::today()->month,
            Carbon::today()->year
        ];
    }

    /**
     * [getListMenuTimekeeping: get menu list for sidebar]
     * @param  [type] $date
     * @return [type]
     */
    public static function getListMenuTimekeeping($date = null)
    {
        if (!$date) {
            $date = Carbon::today();
        }
        $monthCurrent = $date->month;
        $yearCurrent = $date->year;

        $listMenuTimekeeping = [];
        for ($i=1; $i <= $monthCurrent; $i++) {
            $monthAndYear = $i . '/' .  $yearCurrent;
            $listMenuTimekeeping[] = $monthAndYear;
        }

        return $listMenuTimekeeping;
    }

    /**
     * [getStartAndEndMonth: get start and end month from a date]
     * @param  [type] $date
     * @param  [type] $formatDate
     * @return [type]
     */
    public static function getStartAndEndMonth($date, $formatDate = null)
    {
        if (!$formatDate) {
            $formatDate = 'd/m/Y';
        }
        $dateStart = Carbon::createFromFormat($formatDate, $date);
        $dateEnd = Carbon::createFromFormat($formatDate, $date);

        return [
            $dateStart->startOfMonth(),
            $dateEnd->endOfMonth()
        ];
    }

    /**
     * [isWeekendOrHoliday: check date is weekend or holiday]
     * @param  [m/d/Y]  $date
     * @param  boolean $checkWeekend
     * @param  boolean $checkHoliday
     * @return boolean
     */
    public static function isWeekendOrHoliday(
            $date,
            $checkWeekend = true,
            $checkHoliday = true,
            $teamCodePrefix = Team::CODE_PREFIX_HN,
            array $options = []
    ) {
        $isWeekendOrHoliday = false;
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);
        if (isset($options['compensationDays'])) {
            $compensationDays = $options['compensationDays'];
        } else {
            $compensationDays = CoreConfigData::getComAndLeaveDays($teamCodePrefix);
        }
        if ($checkWeekend) {
            $isWeekendOrHoliday = static::isWeekend($date, $compensationDays);
        }
        if ($checkHoliday) {
            if (!$isWeekendOrHoliday && static::isHoliday($date, $annualHolidays, $specialHolidays)) {
                $isWeekendOrHoliday = true;
            }
        }

        return $isWeekendOrHoliday;
    }

    /**
     * check date is weekend day
     *
     * @param  [Carbon]  $date
     * @return boolean
     */
    public static function isWeekend($date, $compensationDays)
    {
        return (in_array($date->dayOfWeek, [Carbon::SUNDAY, Carbon::SATURDAY]) // is weekend
            && !in_array($date->format('Y-m-d'), $compensationDays['com'])) || // not is compensation days
            in_array($date->format('Y-m-d'), $compensationDays['lea']); // is leave
    }

    public static function countWorkingDay($startDate, $endDate)
    {
        $count = 0;
        while (strtotime($startDate) <= strtotime($endDate)) {
            $carbonStart = Carbon::parse($startDate);
            $startDate = date("Y-m-d", strtotime("+1 day", strtotime($startDate)));
            // is weekend
            if ($carbonStart->isWeekend()) {
                continue;
            }
            $count++;
        }
        return $count;
    }

    public static function countWorkingDayWithoutHoliday($startDate, $endDate)
    {
        $count = 0;
        while (strtotime($startDate) <= strtotime($endDate)) {
            $carbonStart = Carbon::parse($startDate);
            $startDate = date("Y-m-d", strtotime("+1 day", strtotime($startDate)));
            // is weekend
            if ( static::isWeekendOrHoliday($carbonStart)) {
                continue;
            }
            $count++;
        }
        return $count;
    }

    /**
     * Get team code for timekeeping
     *
     * @param string $teamCode
     *
     * @return string
     */
    public static function getTeamCodePrefix($teamCode)
    {
        switch ($teamCode) {
            case Team::CODE_PREFIX_AI:
                $teamCode = Team::CODE_PREFIX_HN;
                break;
            case Team::CODE_PREFIX_RS:
                $teamCode = Team::CODE_PREFIX_HCM;
                break;
            default:
                break;
        }
        return Team::getTeamCodePrefix($teamCode);
    }

    /**
     * check date is holiday
     *
     * @param [Carbon]  $date
     * @param array|null $annualHolidays
     * @param array|null $specialHolidays
     * @return boolean
     */
    public static function isHoliday($date, $annualHolidays = null, $specialHolidays = null, $teamCodePrefix = 'hanoi')
    {
        if (!$annualHolidays) {
            $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        }
        if (!$specialHolidays) {
            $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);
        }
        return in_array($date->format('m-d'), $annualHolidays)
                || in_array($date->format('Y-m-d'), $specialHolidays);
    }

    /**
     * check date is holiday
     *
     * @param [Carbon]  $date
     * @param [array|null] $arrHolidays [annualHolidays, specialHolidays]
     * @return boolean
     */
    public static function isHolidays($date, $arrHolidays)
    {
        return in_array($date->format('m-d'), $arrHolidays[0])
                || in_array($date->format('Y-m-d'), $arrHolidays[1]);
    }

    /**
     * [getTimekeepingSign description]
     * @param  [object] $timekeepingOfEmployee
     * @param  [string] $teamCodePrefix
     * @param  [array] $compensationDays
     * @param  [array] $arrHolidays
     * @return [array]
     */
    public static function getTimekeepingSign($timekeepingOfEmployee, $teamCodePrefix, $compensationDays, $arrHolidays)
    {
        if (!$timekeepingOfEmployee) {
            return ['', 0];
        }
        $date = $timekeepingOfEmployee->timekeeping_date;
        $isWeekend = static::isWeekend(Carbon::parse($date), $compensationDays);
        $isHoliday = static::isHolidays(Carbon::parse($date), $arrHolidays);

        // ========= check information working =========
        $joinDate = $timekeepingOfEmployee->join_date;
        $trialDate = $timekeepingOfEmployee->trial_date;
        $offcialDate = $timekeepingOfEmployee->offcial_date;
        $leaveDate = $timekeepingOfEmployee->leave_date;
        $typesOffcial = \Rikkei\Resource\View\getOptions::typeEmployeeOfficial();
        //check employees leave the company
        if (!empty($leaveDate) && Carbon::parse($leaveDate)->lt($date) && !$isWeekend) {
            return ['', 0];
        }

        if (((empty($trialDate) && (!empty($offcialDate) && strtotime($date) < strtotime($offcialDate)))
                || (!empty($trialDate) && strtotime($date) < strtotime($trialDate)))
            && in_array($timekeepingOfEmployee->contract_type, $typesOffcial)) {
            return ['', 0];
        }
        if (!$isWeekend && !$isHoliday) {
            if (((empty($trialDate) && (empty($offcialDate) || strtotime($date) >= strtotime($offcialDate)))
                    || (!empty($trialDate) && strtotime($date) >= strtotime($trialDate)))
                        && !in_array($timekeepingOfEmployee->contract_type, $typesOffcial) && !(empty($trialDate) && empty($offcialDate))) {
                return ['', 0];
            }
        }

        // ========= timekeeping sign =========
        $finesLateIn = 0;
        $timekeepingSign = '';

        if ($isWeekend || $isHoliday) {
            if ($isHoliday) {
                if ((in_array($timekeepingOfEmployee->contract_type, $typesOffcial)
                        && ((empty($trialDate) && (empty($offcialDate) || strtotime($date) < strtotime($offcialDate)))
                            || (!empty($trialDate) && strtotime($date) < strtotime($trialDate))))
                    || (!in_array($timekeepingOfEmployee->contract_type, $typesOffcial) && strtotime($date) < strtotime($joinDate))) {
                    return ['', 0];
                }
                if (in_array($timekeepingOfEmployee->contract_type, $typesOffcial)) {
                    if (!empty((float)$timekeepingOfEmployee->no_salary_holiday)) {
                        $timekeepingSign = 'KL';
                    } else {
                        $timekeepingSign = 'L';
                    }
                }
            }
        } else {
            $isSign = true;
            $isSignCT = true;
            $absent = ManageTimeConst::FULL_TIME; // time khong lam viec

            // ==== business trip ====
            $hasBusiness = $timekeepingOfEmployee->has_business_trip;
            if ($hasBusiness) {
                if ($hasBusiness == ManageTimeConst::FULL_TIME) {
                    $timekeepingSign = 'CT';
                    $isSignCT = false;
                    $absent = 0;
                } else {
                    $timekeepingSign = 'CT: ' . $timekeepingOfEmployee->register_business_trip_number;
                    $absent = 1 - $timekeepingOfEmployee->register_business_trip_number;
                }
            }

            // ==== leave day ====
            $regLeave = static::getSignLeave($timekeepingOfEmployee, $isSign, $absent);
            $isSign = $regLeave['isSign'];
            $leaveHas = $regLeave['leaveHas'];
            $leaveNo = $regLeave['leaveNo'];
            $absent = $regLeave['absent'];
            $timekeepingSign  = $timekeepingSign . $regLeave['timekeepingSign'];

            // ==== supplement ====
            $hasSupplement = $timekeepingOfEmployee->has_supplement;
            if ($isSign && $isSignCT && $hasSupplement) {
                $regSupplemnt = static::getSignSupplement($timekeepingOfEmployee, $isSign, $leaveHas, $leaveNo, $absent);
                $timekeepingSign = $timekeepingSign . $regSupplemnt['timekeepingSign'];
                $isSign = $regSupplemnt['isSign'];
                $absent = $regSupplemnt['absent'];
            }
            // ==== working ====
            if ($isSign && $isSignCT) {
                $woking = static::getSignTimeWoking($timekeepingOfEmployee, $absent, $leaveHas, $leaveNo);
                $timekeepingSign = $timekeepingSign . $woking['timekeepingSign'];
            }

            // ==== time late and early ====
            $lateEarly = static::getSignlateEarly($timekeepingOfEmployee, $leaveHas, $leaveNo);
            if (count($lateEarly)) {
                $timekeepingSign = $timekeepingSign . $lateEarly['timekeepingSign'];
                $finesLateIn = $lateEarly['finesLateIn'];
            }
        }
        // ==== OT ====
        $regOT = static::getSignOT($timekeepingOfEmployee);
        $timekeepingSign = $timekeepingSign . $regOT['timekeepingSign'];

        $patterns = [
            '/P: 0\.5(0|(\,)|$)/',
            '/P: 1\.00/',
            '/KL: 0\.5(0|(\,)|$)/',
            '/KL: 1\.00/',
            '/BS: 0\.5(0|(\,)|$)/',
            '/BS: 1/',
            '/X: 0\.5(0|(\,)|$)/',
            '/X: 1/',
            '/V: 0\.5(0|(\,)|$)/',
            '/V: 1/',
            '/CT: 0\.5(0|(\,)|$)/',
            '/LCB: 0\.5(0|(\,)|$)/',
            '/LCB: 1\.00/',
        ];
        $replacements = [
            'P/2$2',
            'P',
            'KL/2$2',
            'KL',
            'BS/2$2',
            'BS',
            'X/2$2',
            'X',
            'V/2$2',
            'V',
            'CT/2$2',
            'LCB/2$2',
            'LCB',
        ];
        $results[0] = preg_replace($patterns, $replacements, trim($timekeepingSign, ','));
        $results[1] = $finesLateIn;
        return $results;
    }

    public static function getTimekeepingSignOld($timekeepingOfEmployee, $teamCodePrefix, $compensationDays, $arrHolidays)
    {
        $date = $timekeepingOfEmployee->timekeeping_date;
        $teamCodePrefix = Team::changeTeam($teamCodePrefix);
        $isWeekend = static::isWeekend(Carbon::parse($date), $compensationDays);
        $isHoliday = static::isHolidays(Carbon::parse($date), $arrHolidays);

        $isTeamCodeJapan = static::isTeamCodeJapan($teamCodePrefix);
        $joinDate = $timekeepingOfEmployee->join_date;
        $trialDate = $timekeepingOfEmployee->trial_date;
        $offcialDate = $timekeepingOfEmployee->offcial_date;
        $leaveDate = $timekeepingOfEmployee->leave_date;
        $results = [];
        $finesLateIn = 0;
        $timekeepingSign = '';
        if ($timekeepingOfEmployee) {
            $timekeeping = $timekeepingOfEmployee->timekeeping;
            $hasSupplement = $timekeepingOfEmployee->has_supplement;
            $timekeepingNumber = $timekeepingOfEmployee->timekeeping_number;
            $timekeepingNumberRegister = $timekeepingOfEmployee->timekeeping_number_register;
            $registerBusinessTripNumber = $timekeepingOfEmployee->register_business_trip_number;
            $registerLeaveHasSalary = $timekeepingOfEmployee->register_leave_has_salary;
            $registerLeaveNoSalary = $timekeepingOfEmployee->register_leave_no_salary;
            $registerSupplementNumber = $timekeepingOfEmployee->register_supplement_number;
            $registerOT = $timekeepingOfEmployee->register_ot;
            $registerOTHasSalary = $timekeepingOfEmployee->register_ot_has_salary;
            $registerOTNoSalary = $timekeepingOfEmployee->register_ot_no_salary;
            $allowTimekeepingSign = true;
            $countNumberRegister = 0;
            $supplement = 0;
            $typesOffcial = \Rikkei\Resource\View\getOptions::typeEmployeeOfficial();
            $timeWork = $timekeepingNumber + $timekeepingNumberRegister;

            if (!empty($leaveDate) && Carbon::parse($leaveDate)->lt($date) && !$isWeekend) {
                return ['V', 0];
            }
            if (!$isWeekend && !$isHoliday && ((empty($trialDate) && (empty($offcialDate) || strtotime($date) < strtotime($offcialDate)))
                            || (!empty($trialDate) && strtotime($date) < strtotime($trialDate)))
                        && in_array($timekeepingOfEmployee->contract_type, $typesOffcial)) {
                return ['V', 0];
            }

            if (!$isWeekend && !$isHoliday && ((empty($trialDate) && (empty($offcialDate) || strtotime($date) >= strtotime($offcialDate)))
                            || (!empty($trialDate) && strtotime($date) >= strtotime($trialDate)))
                        && !in_array($timekeepingOfEmployee->contract_type, $typesOffcial) && !(empty($trialDate) && empty($offcialDate))) {
                return ['V', 0];
            }

            if ($isWeekend || $isHoliday) {
                if ($isHoliday && !$isWeekend) {
                    if ((in_array($timekeepingOfEmployee->contract_type, $typesOffcial)
                            && (
                               (empty($trialDate) && (empty($offcialDate) || strtotime($date) < strtotime($offcialDate)))
                                 || (!empty($trialDate) && strtotime($date) < strtotime($trialDate))
                            ))
                        || (!in_array($timekeepingOfEmployee->contract_type, $typesOffcial) && strtotime($date) < strtotime($joinDate))
                            
                    ) {
                        return ['V', 0];
                    }
//                    if ((empty($trialDate) && (empty($offcialDate) || strtotime($date) < strtotime($offcialDate)))
//                            || (!empty($trialDate) && strtotime($date) < strtotime($trialDate))
//                        || !in_array($timekeepingOfEmployee->contract_type, $typesOffcial)) {echo '1';
//                        return ['V', 0];
//                    }
                    if (in_array($timekeepingOfEmployee->contract_type, $typesOffcial)) {
                        $timekeepingSign = 'L';
                    }
                } else {
                    $timekeepingSign = '';
                }
            } else {
                if ($registerBusinessTripNumber > 0 && $allowTimekeepingSign) {
                    if ($registerBusinessTripNumber >= 1) {
                        if ($timekeepingSign) {
                            $timekeepingSign = $timekeepingSign . '; CT';
                        } else {
                            $timekeepingSign = 'CT';
                        }
                        $allowTimekeepingSign = false;
                    } else {
                        if ($timekeepingSign) {
                            $timekeepingSign = $timekeepingSign . '; CT/2';
                        } else {
                            $timekeepingSign = 'CT/2';
                        }
                        $countNumberRegister++;
                    }
                    if ($countNumberRegister == 2) {
                        $allowTimekeepingSign = false;
                    }
                }
                if ($registerLeaveHasSalary > 0) {
                    if ($registerLeaveHasSalary == 1) {
                        if ($timekeepingSign) {
                            $timekeepingSign = $timekeepingSign . '; P';
                        } else {
                            $timekeepingSign = 'P';
                        }
                        $allowTimekeepingSign = false;
                    } elseif ($registerLeaveHasSalary == 0.5) {
                        if ($timekeepingSign) {
                            $timekeepingSign = $timekeepingSign . '; P/2';
                        } else {
                            $timekeepingSign = 'P/2';
                        }
                        if ($timekeepingOfEmployee->has_business_trip != $timekeepingOfEmployee->has_leave_day) {
                            $countNumberRegister = $countNumberRegister + 2;
                        }
                    } else {
                        if ($timekeepingSign) {
                            $timekeepingSign = $timekeepingSign . '; P: ' . $registerLeaveHasSalary;
                        } else {
                            $timekeepingSign = 'P: ' . $registerLeaveHasSalary;
                        }
                        if ($timekeepingOfEmployee->has_business_trip != $timekeepingOfEmployee->has_leave_day) {
                            $countNumberRegister++;
                        }
                    }
                    if ($countNumberRegister >= 4) {
                        $allowTimekeepingSign = false;
                    }
                }
                if ($registerLeaveNoSalary > 0) {
                    if ($registerLeaveNoSalary == 1) {
                        if ($timekeepingSign) {
                            $timekeepingSign = $timekeepingSign . '; KL';
                        } else {
                            $timekeepingSign = 'KL';
                        }
                        $allowTimekeepingSign = false;
                    } elseif ($registerLeaveNoSalary == 0.5) {
                        if ($timekeepingSign) {
                            $timekeepingSign = $timekeepingSign . '; KL/2';
                        } else {
                            $timekeepingSign = 'KL/2';
                        }
                        if ($timekeepingOfEmployee->has_business_trip != $timekeepingOfEmployee->has_leave_day_no_salary) {
                            $countNumberRegister = $countNumberRegister + 2;
                        }
                    } else {
                        if ($timekeepingSign) {
                            $timekeepingSign = $timekeepingSign . '; KL: ' . $registerLeaveNoSalary;
                        } else {
                            $timekeepingSign = 'KL: ' . $registerLeaveNoSalary;
                        }
                        if ($timekeepingOfEmployee->has_business_trip != $timekeepingOfEmployee->has_leave_day_no_salary) {
                            $countNumberRegister++;
                        }
                    }
                    if ($countNumberRegister >= 4) {
                        $allowTimekeepingSign = false;
                    }
                }
                if ($registerSupplementNumber > 0 && $allowTimekeepingSign) {
                    if ($registerSupplementNumber == 1 && $countNumberRegister == 0) {
                        if ($timekeepingSign) {
                            $timekeepingSign = $timekeepingSign . '; BS';
                        } else {
                            $timekeepingSign = 'BS';
                        }
                        $allowTimekeepingSign = false;
                    } else {
                         if ($timekeepingOfEmployee->has_business_trip != $timekeepingOfEmployee->has_supplement
                                && $timekeepingOfEmployee->has_leave_day != $timekeepingOfEmployee->has_supplement) {
                            if ($timekeepingOfEmployee->has_supplement != $timekeepingOfEmployee->timekeeping) {
                                $supplement = $registerSupplementNumber;
                                if (($timekeepingOfEmployee->register_leave_no_salary + $timekeepingOfEmployee->register_leave_has_salary == 0.5)
                                    && $timekeepingOfEmployee->has_leave_day < $timekeepingOfEmployee->register_leave_has_salary
                                    && $timekeepingOfEmployee->has_leave_day_no_salary < $timekeepingOfEmployee->register_leave_no_salary) {
                                    $supplement = abs($registerSupplementNumber - $registerLeaveHasSalary - $registerLeaveNoSalary);
                                }
                                if ($timekeepingOfEmployee->has_supplement == 1) {
                                    $supplement = abs($registerSupplementNumber - $registerLeaveHasSalary - $registerLeaveNoSalary);
                                }
                                if ($timekeepingOfEmployee->has_leave_day * 2 == $registerSupplementNumber) {
                                   $supplement = abs($registerSupplementNumber - $timekeepingOfEmployee->has_leave_day);
                                }
                                if ($timekeepingOfEmployee->has_leave_day_no_salary * 2 == $registerSupplementNumber) {
                                    $supplement = abs($registerSupplementNumber - $timekeepingOfEmployee->has_leave_day_no_salary);
                                }
                                if (($timekeepingOfEmployee->has_supplement == ManageTimeConst::HAS_SUPPLEMENT_MORNING && $timekeepingOfEmployee->has_leave_day == ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF)
                                    || ($timekeepingOfEmployee->has_supplement == ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON && $timekeepingOfEmployee->has_leave_day == ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF)) {
                                    $supplement = $registerSupplementNumber - $timekeepingOfEmployee->register_leave_has_salary;
                                }
                                
                                if (($timekeepingOfEmployee->has_supplement == ManageTimeConst::HAS_SUPPLEMENT_MORNING && $timekeepingOfEmployee->has_leave_day_no_salary == ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF)
                                    || ($timekeepingOfEmployee->has_supplement == ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON && $timekeepingOfEmployee->has_leave_day_no_salary == ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF)) {
                                    $supplement = $registerSupplementNumber - $timekeepingOfEmployee->register_leave_no_salary;
                                }
                                if ($supplement == 0.5) {
                                    $leaveDayRegister = $registerLeaveNoSalary + $registerLeaveHasSalary;
                                    if ($leaveDayRegister >= 0 && $leaveDayRegister < $registerSupplementNumber) {
                                        if ($timekeepingSign) {
                                            $timekeepingSign = $timekeepingSign . '; BS/2';
                                        } else {
                                            $timekeepingSign = 'BS/2';
                                        }
                                        $countNumberRegister = $countNumberRegister + 2;
                                    } else {
                                        if ($leaveDayRegister > $supplement) {
                                            $timekeepingSign = $timekeepingSign . '; BS: ' . ($leaveDayRegister - $supplement);
                                        } else {
                                            if ($timekeepingSign) {
                                                $timekeepingSign = $timekeepingSign . '; BS/2';
                                            } else {
                                                $timekeepingSign = 'BS/2';
                                            }
                                            $countNumberRegister = $countNumberRegister + 2;
                                        }
                                    }
                                } else {
                                    if ($timekeepingSign) {
                                        $timekeepingSign = $timekeepingSign . '; BS: ' . $supplement;
                                    } else {
                                        $timekeepingSign = 'BS: ' . $supplement;
                                    }
                                }
                            } else {
                                if (($timekeepingOfEmployee->has_supplement == ManageTimeConst::HAS_SUPPLEMENT_MORNING && $timekeepingOfEmployee->has_leave_day_no_salary == ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF)
                                    || ($timekeepingOfEmployee->has_supplement == ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON && $timekeepingOfEmployee->has_leave_day_no_salary == ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF)) {
                                    $registerSupplementNumber = $registerSupplementNumber - $timekeepingOfEmployee->register_leave_no_salary;
                                }
                                if (($timekeepingOfEmployee->has_supplement == ManageTimeConst::HAS_SUPPLEMENT_MORNING && $timekeepingOfEmployee->has_leave_day == ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF)
                                    || ($timekeepingOfEmployee->has_supplement == ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON && $timekeepingOfEmployee->has_leave_day == ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF)) {
                                    $registerSupplementNumber = $registerSupplementNumber - $timekeepingOfEmployee->register_leave_has_salary;
                                }

                                if ($timekeepingSign) {
                                    if ($registerSupplementNumber == 1) {
                                        $registerSupplementNumber = $registerSupplementNumber - $registerLeaveNoSalary - $registerLeaveHasSalary;
                                    }
                                    $timekeepingSign = $timekeepingSign . '; BS:  ' . $registerSupplementNumber;
                                } else {
                                    $timekeepingSign = 'BS/2';
                                }
                                $countNumberRegister = $countNumberRegister + 2;
                            }
                        }
                    }
                    if ($countNumberRegister >= 4) {
                        $allowTimekeepingSign = false;
                    }
                }
                if ($allowTimekeepingSign) {
                    if (($timekeeping && !in_array($timekeeping, [
                            $timekeepingOfEmployee->has_business_trip,
                            $timekeepingOfEmployee->has_leave_day,
                            $timekeepingOfEmployee->has_business,
                            $timekeepingOfEmployee->has_leave_day_no_salary,
                        ])) || $timeWork) {
                        if ($timekeeping == ManageTimeConst::HOLIDAY_TIME) {
                            $timekeepingSign = 'L';
                        } else {
                            if ($timekeepingNumber == 1 && $countNumberRegister == 0) {
                                if ($timekeepingSign) {
                                    $timekeepingSign = $timekeepingSign . '; X';
                                } else {
                                    $timekeepingSign = 'X';
                                }
                                $allowTimekeepingSign = false;
                            } else {
                                if (($timekeeping == ManageTimeConst::PART_TIME_MORNING
                                        && $hasSupplement == ManageTimeConst::HAS_SUPPLEMENT_MORNING
                                        && !$timekeepingNumberRegister) ||
                                        ($timekeeping == ManageTimeConst::PART_TIME_AFTERNOON
                                        && $hasSupplement == ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON
                                        && !$timekeepingNumberRegister)) {
                                    if (isset($supplement)) {
                                        $work = $supplement + $registerLeaveHasSalary + $registerLeaveNoSalary;                        
                                    } else {
                                        $work = $timeWork;
                                    }
                                    if ($timekeepingSign) {
                                        if ($work == 0.5) {
                                            $timekeepingSign = $timekeepingSign . '; V/2';
                                        } else {
                                            $timekeepingSign = $timekeepingSign . '; V: ' . (1 - $work);
                                        }
                                    } else {
                                        $timekeepingSign = 'V/2';
                                    }

                                } else {
                                    if ($registerSupplementNumber == 0) {
                                        if ($timekeepingNumberRegister) {
                                            if ($timekeepingSign) {
                                                if ($timeWork) {
                                                    if ($timeWork == 0.5) {
                                                        $timekeepingSign = $timekeepingSign . '; X/2';
                                                    } else {
                                                        $timekeepingSign = $timekeepingSign . '; X: ' . $timeWork;
                                                    }
                                                    $v = 1 - $timeWork - $registerLeaveHasSalary - $registerLeaveNoSalary - $registerBusinessTripNumber;
                                                    if ($v > 0) {
                                                        $timekeepingSign = $timekeepingSign . '; V: ' . $v;
                                                    }
                                                } else {
                                                    $v = 1 - $registerLeaveHasSalary - $registerLeaveNoSalary;
                                                    if ($v > 0) {
                                                        if ($timekeepingSign) {
                                                            $timekeepingSign = $timekeepingSign . '; V: ' . $v;
                                                        } else {
                                                            $timekeepingSign = '; V: ' . $v;
                                                        }
                                                    }
                                                }
                                            } else {
                                                if ($timeWork == 0.5) {
                                                    $timekeepingSign = 'X/2; V/2';
                                                } else {
                                                    $timekeepingSign = 'X : ' . $timeWork .'; V: ' . (1 - $timeWork);
                                                }
                                            }
                                        } else {
                                            if (
                                                (($timekeepingOfEmployee->has_leave_day == 2 || $timekeepingOfEmployee->has_leave_day_no_salary == 2) && $timekeepingOfEmployee->timekeeping == 2)
                                                || (($timekeepingOfEmployee->has_leave_day == 3 || $timekeepingOfEmployee->has_leave_day_no_salary == 3) && $timekeepingOfEmployee->timekeeping == 4)
                                                && !$timeWork) {
                                                $timekeepingSign = $timekeepingSign . '; V/2';
                                                $countNumberRegister = $countNumberRegister + 2;
                                            } else {
                                                if ($timekeepingSign) {
                                                    $timekeepingSign = $timekeepingSign . '; X/2';
                                                    $v = 1 - $timeWork - $registerLeaveHasSalary - $registerLeaveNoSalary;
                                                    if ($v > 0) {
                                                        $timekeepingSign = $timekeepingSign . '; V: ' . $v;
                                                    }
                                                } else {
                                                    $timekeepingSign = 'X/2; V/2';
                                                }
                                            }
                                        }
                                    } else {
                                        if ((($timekeeping == ManageTimeConst::PART_TIME_MORNING || $timekeeping == 1)
                                            && $hasSupplement == ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON) ||
                                            (($timekeeping == ManageTimeConst::PART_TIME_AFTERNOON || $timekeeping == 1)
                                            && $hasSupplement == ManageTimeConst::HAS_SUPPLEMENT_MORNING) && $allowTimekeepingSign) {
                                                if ($timeWork > 0.5 && $supplement = 0.5) {
                                                    $timeWork  = $timeWork - $supplement;
                                                }
                                                if ($timeWork > 0.5 && ($registerLeaveHasSalary < 0.5 ||  $registerLeaveNoSalary < 0.5)) {
                                                    $timeWork  = $timeWork - $registerLeaveHasSalary - $registerLeaveNoSalary;
                                                }
                                                if ($timeWork >= 0.5 && $timekeepingOfEmployee->has_leave_day * 2 == $registerSupplementNumber) {
                                                   $timeWork = $timeWork - $timekeepingOfEmployee->has_leave_day;
                                                }
                                                if ($timeWork >= 0.5 && $timekeepingOfEmployee->has_leave_day_no_salary * 2 == $registerSupplementNumber) {
                                                    $timeWork = $timeWork - $timekeepingOfEmployee->has_leave_day_no_salary;
                                                }
                                                if ($timekeepingNumberRegister >= 0) {
                                                    if ($timekeepingSign) {
                                                        $timekeepingSign = $timekeepingSign . '; X/2';
                                                    } else {
                                                        $timekeepingSign = 'X/2';
                                                    }
                                                } else {
                                                    if ($timekeepingSign) {
                                                        $timekeepingSign = $timekeepingSign . '; X: ' .  $timeWork;
                                                    } else {
                                                        $timekeepingSign = 'X: ' . $timeWork;
                                                    }
                                                }
                                        } else {
                                            if ($timeWork > 0) {
                                                $timekeepingSign = $timekeepingSign . '; X: ' .  $timeWork;
                                            }
                                        }
                                    }
                                    $countNumberRegister++;
                                }
                            }
                            $lateStartShift = $timekeepingOfEmployee->late_start_shift;
                            $earlyMidShift = $timekeepingOfEmployee->early_mid_shift;
                            $lateMidShift = $timekeepingOfEmployee->late_mid_shift;
                            $earlyEndShift = $timekeepingOfEmployee->early_end_shift;
                            $hasBusinessTrip = $timekeepingOfEmployee->has_business_trip;
                            $hasLeaveDay = $timekeepingOfEmployee->has_leave_day;
                            $hasLeaveDayNoSalary = $timekeepingOfEmployee->has_leave_day_no_salary;
                            $hasSupplement = $timekeepingOfEmployee->has_supplement;

                            $maxTimeLateInEarlyOut = ManageTimeConst::MAX_TIME_LATE_IN_EARLY_OUT;

                            $hasBusinessTripFullDay = ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY;
                            $hasBusinessTripMorning = ManageTimeConst::HAS_BUSINESS_TRIP_MORNING;
                            $hasBusinessTripAfternoon = ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON;

                            $hasLeaveDayFullDay = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
                            $hasLeaveDayMorning = ManageTimeConst::HAS_LEAVE_DAY_MORNING;
                            $hasLeaveDayAfternoon = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;

                            $hasSupplementFullDay = ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY;
                            $hasSupplementMorning = ManageTimeConst::HAS_SUPPLEMENT_MORNING;
                            $hasSupplementAfternoon = ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON;

                            $hasRegisterManageTimeMorning = ($hasBusinessTrip != $hasBusinessTripFullDay &&
                                    $hasBusinessTrip != $hasBusinessTripMorning &&
                                    $hasLeaveDay != $hasLeaveDayFullDay &&
                                    $hasLeaveDay != $hasLeaveDayMorning &&
                                    $hasLeaveDayNoSalary != $hasLeaveDayFullDay &&
                                    $hasLeaveDayNoSalary != $hasLeaveDayMorning &&
                                    $hasSupplement != $hasSupplementFullDay &&
                                    $hasSupplement != $hasSupplementMorning
                            );
                            $hasRegisterManageTimeAfternoon = ($hasBusinessTrip != $hasBusinessTripFullDay &&
                                    $hasBusinessTrip != $hasBusinessTripAfternoon &&
                                    $hasLeaveDay != $hasLeaveDayFullDay &&
                                    $hasLeaveDay != $hasLeaveDayAfternoon &&
                                    $hasLeaveDayNoSalary != $hasLeaveDayFullDay &&
                                    $hasLeaveDayNoSalary != $hasLeaveDayAfternoon &&
                                    $hasSupplement != $hasSupplementFullDay &&
                                    $hasSupplement != $hasSupplementAfternoon
                            );

                            if ($lateStartShift > 0 && (($lateStartShift <= $maxTimeLateInEarlyOut && $earlyMidShift <= $maxTimeLateInEarlyOut && $hasRegisterManageTimeMorning) || $isTeamCodeJapan)) {
                                $timekeepingSign = $timekeepingSign . '; ' . 'M1: ' . $lateStartShift;
                                $finesLateIn = CEIL($lateStartShift / ManageTimeConst::TIME_LATE_IN_PER_BLOCK);
                            }
                            if ($earlyMidShift > 0 && (($earlyMidShift <= $maxTimeLateInEarlyOut && $lateStartShift <= $maxTimeLateInEarlyOut && $hasRegisterManageTimeMorning) || $isTeamCodeJapan)) {
                                $timekeepingSign = $timekeepingSign . '; ' . 'S1: ' . $earlyMidShift;
                            }
                            if ($lateMidShift > 0 && (($lateMidShift <= $maxTimeLateInEarlyOut && $earlyEndShift <= $maxTimeLateInEarlyOut && $hasRegisterManageTimeAfternoon) || $isTeamCodeJapan)) {
                                $timekeepingSign = $timekeepingSign . '; ' . 'M2: ' . $lateMidShift;
                            }
                            if ($earlyEndShift > 0 && (($earlyEndShift <= $maxTimeLateInEarlyOut && $lateMidShift <= $maxTimeLateInEarlyOut && $hasRegisterManageTimeAfternoon) || $isTeamCodeJapan)) {
                                $timekeepingSign = $timekeepingSign . '; ' . 'S2: ' . $earlyEndShift;
                            }
                        }
                    } else {
                        if ($countNumberRegister == 0) {
                            $timekeepingSign = 'V';
                        } elseif ($registerSupplementNumber) {
                            if ((float)$timekeepingNumberRegister && $timeWork > 0) {
                                if ($timekeepingNumber) {
                                    if ($timeWork - $supplement == 0.5) {
                                        $timekeepingSign = $timekeepingSign . '; X/2';
                                    } else {
                                        $timekeepingSign = $timekeepingSign . '; X: ' . ($timeWork - $supplement);
                                    }
                                } else {
                                    if ($timekeepingNumberRegister == 0.5) {
                                        $timekeepingSign = $timekeepingSign . '; X/2';
                                    } else {
                                        $timekeepingSign = $timekeepingSign . '; X: ' . $timeWork;
                                    }
                                }
                            } else {
                                $work = $supplement + $registerLeaveNoSalary + $registerLeaveHasSalary;
                                if (!$timeWork && $work > 0 && (1 - $work) > 0 && $allowTimekeepingSign && $registerBusinessTripNumber == 0) {
                                    if ($work == 0.5) {
                                        $timekeepingSign = $timekeepingSign . '; V/2';
                                    } else {
                                        $timekeepingSign = $timekeepingSign . '; V: ' . (1 - $work);
                                    }
                                }
                            }
                        } else {
                            if ($timeWork) {
                                if ($timeWork == 0.5) {
                                    if ($timekeepingSign) {
                                        $timekeepingSign = $timekeepingSign . '; V/2';
                                    } else {
                                        $timekeepingSign = 'V/2';
                                    }
                                } else {
                                    if ($timekeepingSign) {
                                        $timekeepingSign = $timekeepingSign . '; V:  ' . (1 - $timeWork);
                                    } else {
                                        $timekeepingSign = 'V: ' . (1 - $timeWork);
                                    }
                                }
                            } else {
                                $leave = $registerLeaveHasSalary + $registerLeaveNoSalary;
                                if ($leave == 0.5) {
                                    $timekeepingSign = $timekeepingSign . '; V/2';
                                } else {
                                    $timekeepingSign = $timekeepingSign . '; V: ' . (1 - $leave);
                                }
                            }
                        }
                    }
                } elseif ($timekeepingNumber || $timekeepingNumberRegister) {
                    if ($timekeepingOfEmployee->has_supplement != $timekeepingOfEmployee->timekeeping && $allowTimekeepingSign) {
                        if ($hasSupplement) {
                            if ($timekeepingNumberRegister >= 0) {
                                if ($timekeepingNumber == 0.5) {
                                    $timekeepingSign = $timekeepingSign . '; X/2';
                                } else {
                                    $timekeepingSign = $timekeepingSign . '; X: ' . $timekeepingNumber;
                                }
                            } else {
                                $timekeepingSign = $timekeepingSign . '; X: ' . $timekeepingNumberRegister;
                            }
                        }
                    }
                } else {
                    //no something
                }
            }
            if (($registerOTHasSalary > 0 || $registerOTNoSalary > 0) && $registerOT > 0) {
                if ($registerOTHasSalary > 0) {
                    if ($timekeepingSign) {
                        $timekeepingSign = $timekeepingSign . '; ' . 'OT: ' . $registerOTHasSalary;
                    } else {
                        $timekeepingSign = 'OT: ' . $registerOTHasSalary;
                    }
                }
                if ($registerOTNoSalary > 0) {
                    if ($timekeepingSign) {
                        $timekeepingSign = $timekeepingSign . '; ' . 'OTKL: ' . $registerOTNoSalary;
                    } else {
                        $timekeepingSign = 'OTKL: ' . $registerOTNoSalary;
                    }
                }
            }
        } else {
            if (!$isWeekend) {
                if ($isHoliday) {
                    $timekeepingSign = 'L';
                } else {
                    $timekeepingSign = 'V';
                }
            }
        }
        $results[0] =  str_replace(": 0.5", "/2", $timekeepingSign);
        $results[1] = $finesLateIn;

        return $results;
    }

    public static function isTeamCodeJapan($codePrefix)
    {
        return in_array($codePrefix, [
            Team::CODE_PREFIX_JP,
        ]);
    }

    public static function isTeamCodeVn($codePrefix)
    {
        return in_array($codePrefix, [
            Team::CODE_PREFIX_HN,
            Team::CODE_PREFIX_DN,
            Team::CODE_PREFIX_HCM,
        ]);
    }

    public function isTeamHCM($teamCode)
    {
        return in_array($teamCode, [
            Team::CODE_PREFIX_HCM,
            Team::CODE_PREFIX_RS,
        ]);
    }

    public static function getMonths($getNumber = false)
    {
        if ($getNumber) {
            return [
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4',
                5 => '5',
                6 => '6',
                7 => '7',
                8 => '8',
                9 => '9',
                10 => '10',
                11 => '11',
                12 => '12'
            ];
        }
        return [
            1 => Lang::get('manage_time::view.January'),
            2 => Lang::get('manage_time::view.February'),
            3 => Lang::get('manage_time::view.March'),
            4 => Lang::get('manage_time::view.April'),
            5 => Lang::get('manage_time::view.May'),
            6 => Lang::get('manage_time::view.June'),
            7 => Lang::get('manage_time::view.July'),
            8 => Lang::get('manage_time::view.August'),
            9 => Lang::get('manage_time::view.September'),
            10 => Lang::get('manage_time::view.October'),
            11 => Lang::get('manage_time::view.November'),
            12 => Lang::get('manage_time::view.December')
        ];
    }

    /**
     * Get team_id from team code
     *
     * @param string $teamCode
     *
     * @return Team
     */
    public static function getTeamIdByTeamCode($teamCode)
    {
        return Team::select('id as team_id')
            ->where('code', $teamCode)
            ->first();
    }

    /**
     * Get week label from key of day
     *
     * @param int $day
     *
     * @return string
     */
    public static function getLabelDayOfWeek($day)
    {
        switch ($day) {
            case 1:
                return 'Thứ hai';
            case 2:
                return 'Thứ ba';
            case 3:
                return 'Thứ tư';
            case 4:
                return 'Thứ năm';
            case 5:
                return 'Thứ sáu';
            case 6:
                return 'Thứ bảy';
            default:
                return 'Chủ nhật';
        }
    }

    /**
     * Options compare use in time keeping aggregate page
     *
     * @return array
     */
    public static function optionsCompare()
    {
        return ['=', '>=', '>', '<=', '<'];
    }

    /**
     * Get value filter in time keeping aggregate page
     *
     * @param string $compare operator filter (`=`, `>`, `<`, ...)
     * @param string $value value filter
     *
     * @return string
     */
    public static function valueFilter($compare, $value)
    {
        if (!empty($value) || $value === '0') {
            return $compare . ' ' . $value;
        }
        return '';
    }
    
    /**
     * Fields search filter in time keeping aggregate page
     *
     * @return array
     */
    public static function keysFilter()
    {
        return [
            'total_official_working_days', 'total_trial_working_days', 'total_ot_weekdays',
            'total_ot_weekends', 'total_ot_holidays', 'total_number_late_in',
            'total_number_early_out', 'total_business_trip', 'total_leave_day',
            'total_leave_basic_salary',
            'total_leave_day_no_salary', 'total_supplement', 'total_holiday',
            'total_late_start_shift', 'total_ot_no_salary',
            'number_com_off', 'total_official_ot',
            'total_trial_ot', 'total_official_salary', 'total_trial_salary', 'total_leave_basic_salary_s',
        ];
    }

    /**
     * Fields search filter in time keeping aggregate page
     *
     * @return array
     */
    public static function keysFilterJapan()
    {
        return [
            'total_official_working_days', 'total_trial_working_days', 'total_ot_weekdays',
            'total_ot_weekends', 'total_ot_holidays', 'total_number_late_in',
            'total_number_early_out', 'total_business_trip', 'total_leave_day',
            'total_leave_day_no_salary', 'total_supplement', 'total_holiday',
            'total_late_shift', 'total_early_shift', 'total_ot_no_salary',
            'number_com_off', 'total_official_ot',
            'total_trial_ot', 'total_official_salary', 'total_trial_salary'
        ];
    }

    /**
     * default working time
     *
     * @param string $teamCode
     * @param array|null $rangeTimes
     *
     * @return array
     */
    public static function defaultWorkingTime($teamCode, $rangeTimes = null)
    {
        if (!$rangeTimes) {
            $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);
        }

        $rangeTimes = $rangeTimes ? unserialize($rangeTimes) : [
            'start1' => '07:00',
            'end1' => '08:30',
            'start2' => '12:00',
            'end2' => '13:30',
            'min_mor' => 4,
            'min_aft' => 3,
            'max_end_mor' => '12:00',
            'max_end_aft' => '19:00'
        ];
        $defaultMaxMin = array_only($rangeTimes, ['min_mor', 'min_aft']);
        if (static::isTeamCodeJapan($teamCode)) {
            return array_merge([
                'start_time1' => '09:00',
                'end_time1' => '13:00',
                'start_time2' => '14:00',
                'end_time2' => '18:00',
            ], $defaultMaxMin);
        }
        if (with(new ManageTimeCommon())->isTeamHCM($teamCode)) {
            return array_merge([
                    'start_time1' => '08:30',
                    'end_time1' => '12:00',
                    'start_time2' => '13:15',
                    'end_time2' => '17:45',
                ], $defaultMaxMin);
        }

        return array_merge([
            'start_time1' => '08:00',
            'end_time1' => '12:00',
            'start_time2' => '13:30',
            'end_time2' => '17:30',
            'range_time' => [
                'rstart1' => [$rangeTimes['start1'], $rangeTimes['end1']],
                'rstart2' => [$rangeTimes['start2'], $rangeTimes['end2']],
                'rend1' => [$rangeTimes['start1'], $rangeTimes['max_end_mor']],
                'rend2' => [$rangeTimes['start2'], $rangeTimes['max_end_aft']]
            ]
        ], $defaultMaxMin);
    }

    /**
     * Get suggest aprrover with permission `manage_time::manage-time.manage.approve`
     *
     * @param int $type     type of applicant
     * @param Employee $userCurrent
     *
     * @return Employee|null
     */
    public static function suggestApprover($type, $userCurrent = null)
    {
        if (!$userCurrent) {
            $userCurrent = Permission::getInstance()->getEmployee();
        }

        return static::approverFromType($type, $userCurrent);
    }

    /**
     * Get suggest approve from type
     *
     * @param int $type     type of applicant
     * @param Employee $userCurrent
     *
     * @return Employee
     */
    public static function approverFromType($type, $userCurrent = null)
    {
        if (!$userCurrent) {
            $userCurrent = Permission::getInstance()->getEmployee();
        }
        return LeaveDayRegister::suggestApprover($userCurrent->id, $type);
    }

    /**
     * calculator count days compensation
     *
     * @param model $timeKeepingTable
     * @param date $emplOffDate
     * @param date $emplJoinDate
     * @param date $emplLeaveDate
     * @param array $compInTime [check => [date], big => [date]]
     *
     * @return array integer
     */
    public static function calComDayEmpInTime(
        $timeKeepingTable,
        $emplOffDate,
        $emplJoinDate,
        $emplLeaveDate,
        $compInTime,
        $isItemCom = false
    ) {
        $result = [
            'number_com_tri' => 0,
            'number_com_off' => 0,
        ];

        $emplLeave = $emplLeaveDate ? Carbon::parse($emplLeaveDate) : null;
        if (!$compInTime['check'] || $timeKeepingTable->end_date->lte($emplLeave)) {
            return $result;
        }
        $emplLeave = $emplLeaveDate ? Carbon::parse($emplLeaveDate) : null;
        if (!$compInTime['check'] || $timeKeepingTable->end_date->lte($emplLeave)) {
            return $result;
        }
        if ($timeKeepingTable->type == getOptions::WORKING_OFFICIAL) {
            if ($emplOffDate) {
                $emplOffical = Carbon::parse($emplOffDate);
            } else {
                $emplOffical = null;
            }
        } else {
            $emplOffical = null;
        }

        $emplJoin = Carbon::parse($emplJoinDate);
        foreach ($compInTime['check'] as $index => $dateCom) {
            if ($emplJoin->gt($dateCom)) {
                continue;
            }
            $comparison = $isItemCom ? 'gte' : 'lte';
            if ($emplLeave && $emplLeave->$comparison($compInTime['big'][$index])) {
                continue;
            }
            if ($emplOffical && $emplOffical->lte($dateCom)) {
                $result['number_com_off']++;
            } else {
                $result['number_com_tri']++;
            }
        }
        return $result;
    }

    /**
     * get compensation in time period
     *
     * @param object $timeKeepingTable
     * @param array $compensation @CoreConfigData::getComAndLeaveDays($teamCodePre)
     * @return array date format
     */
    public static function getCompInTime($timeKeepingTable, $compensation, $typeCheck = 'com')
    {
        $result = [
            'check' => [], // hoan doi vi tri ngay lam viec va ngay nghi
            'big' => [], // check ngay nghi > ngay big => tinh cong nhu binh thuong
        ];
        if (!$compensation['com']) {
            return $result;
        }
        if ($typeCheck === 'com') {
            $typeCompare = 'lea';
        } else {
            $typeCheck = 'lea';
            $typeCompare = 'com';
        }
        // vi tri 2 ngay lam bu - nghi bu hoan doi cho nhau
        // => check ngay lam bu => get ngay nghi bu de count
        foreach ($compensation[$typeCheck] as $index => $dateCom) {
            // lam bu trong thang => khong thay doi so ngay cong => ko tinh ngay lam bu
            if (substr($dateCom, 0, 7) === substr($compensation[$typeCompare][$index], 0, 7)) {
                continue;
            }
            $dateCom = Carbon::parse($dateCom);
            if ($timeKeepingTable->start_date->lte($dateCom) && $timeKeepingTable->end_date->gte($dateCom)) {
                $result['check'][] = $compensation[$typeCompare][$index];
                $result['big'][] = $dateCom->lt($compensation[$typeCompare][$index]) ?
                        Carbon::parse($compensation[$typeCompare][$index]) :
                        $dateCom;
            }
        }
        return $result;
    }

    public static function getAllWeekend()
    {
        $fromDate = Carbon::now()->subMonths(3)->startOfMonth();
        $toDate = Carbon::now()->addMonths(3)->endOfMonth();
        $weekends = [];
        $startDate = Carbon::parse($fromDate)->next(Carbon::SATURDAY); // Get the first saturday.
        $endDate = Carbon::parse($toDate);

        for ($date = $startDate; $date->lte($endDate); $date->addWeek()) {
            $weekends[] = $date->format('Y-m-d');
            $weekends[] = $date->copy()->addDay()->format('Y-m-d');
        }

        return $weekends;
    }

    /**
     * ky hieu di muon ve xom
     */
    public static function getSignlateEarly($timekeepingOfEmployee, $leaveHas, $leaveNo)
    {

        $lateStartShift = $timekeepingOfEmployee->late_start_shift;
        $earlyMidShift = $timekeepingOfEmployee->early_mid_shift;
        $lateMidShift = $timekeepingOfEmployee->late_mid_shift;
        $earlyEndShift = $timekeepingOfEmployee->early_end_shift;
        $hasBusinessTrip = $timekeepingOfEmployee->has_business_trip;
        $hasLeaveDay = $timekeepingOfEmployee->has_leave_day;
        $hasLeaveDayNoSalary = $timekeepingOfEmployee->has_leave_day_no_salary;
        $hasSupplement = $timekeepingOfEmployee->has_supplement;

        $maxTimeLateInEarlyOut = ManageTimeConst::MAX_TIME_LATE_IN_EARLY_OUT;

        $hasBusinessTripFullDay = ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY;
        $hasBusinessTripMorning = ManageTimeConst::HAS_BUSINESS_TRIP_MORNING;
        $hasBusinessTripAfternoon = ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON;

        $hasLeaveDayFullDay = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
        $hasLeaveDayMorning = ManageTimeConst::HAS_LEAVE_DAY_MORNING;
        $hasLeaveDayAfternoon = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;
        $hasLeaveDayMorningHalf = ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF;
        $hasLeaveDayAfternoonHalf = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF;

        $hasSupplementFullDay = ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY;
        $hasSupplementMorning = ManageTimeConst::HAS_SUPPLEMENT_MORNING;
        $hasSupplementAfternoon = ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON;

        $hasRegisterManageTimeMorning = ($hasBusinessTrip != $hasBusinessTripFullDay &&
                $hasBusinessTrip != $hasBusinessTripMorning &&
                $hasLeaveDay != $hasLeaveDayFullDay &&
                $hasLeaveDay != $hasLeaveDayMorning &&
                $hasLeaveDayNoSalary != $hasLeaveDayFullDay &&
                $hasLeaveDayNoSalary != $hasLeaveDayMorning &&
                $hasSupplement != $hasSupplementFullDay &&
                $hasSupplement != $hasSupplementMorning
        );
        $hasRegisterManageTimeAfternoon = ($hasBusinessTrip != $hasBusinessTripFullDay &&
                $hasBusinessTrip != $hasBusinessTripAfternoon &&
                $hasLeaveDay != $hasLeaveDayFullDay &&
                $hasLeaveDay != $hasLeaveDayAfternoon &&
                $hasLeaveDayNoSalary != $hasLeaveDayFullDay &&
                $hasLeaveDayNoSalary != $hasLeaveDayAfternoon &&
                $hasSupplement != $hasSupplementFullDay &&
                $hasSupplement != $hasSupplementAfternoon
        );

        $timekeepingSign = '';
        $finesLateIn = 0;
        if ($lateStartShift > 0 && ($lateStartShift <= $maxTimeLateInEarlyOut && $earlyMidShift <= $maxTimeLateInEarlyOut && $hasRegisterManageTimeMorning)) {
            $timekeepingSign = $timekeepingSign . ', M1: ' . $lateStartShift;
            $finesLateIn = CEIL($lateStartShift / ManageTimeConst::TIME_LATE_IN_PER_BLOCK);
        }
        if ($earlyMidShift > 0 && ($earlyMidShift <= $maxTimeLateInEarlyOut && $lateStartShift <= $maxTimeLateInEarlyOut && $hasRegisterManageTimeMorning)) {
            $timekeepingSign = $timekeepingSign . ', S1: ' . $earlyMidShift;
        }
        if ($lateMidShift > 0 && ($lateMidShift <= $maxTimeLateInEarlyOut && $earlyEndShift <= $maxTimeLateInEarlyOut && $hasRegisterManageTimeAfternoon)) {
            $timekeepingSign = $timekeepingSign . ', M2: ' . $lateMidShift;
        }
        if ($earlyEndShift > 0 && ($earlyEndShift <= $maxTimeLateInEarlyOut && $lateMidShift <= $maxTimeLateInEarlyOut && $hasRegisterManageTimeAfternoon)) {
            $timekeepingSign = $timekeepingSign . ', S2: ' . $earlyEndShift;
        }

        return [
            'finesLateIn' => $finesLateIn,
            'timekeepingSign' => $timekeepingSign,
        ];
    }

    /**
     * lay thong tin ngay nghi phep or nghi khong phep
     * @param  [type] $hasLeave
     * @param  [type] $registerLeave
     * @return [type]
     */
    public static function getleaveDay($timekeepingOfEmployee, $hasLeave, $registerLeave)
    {
        if ($hasLeave == ManageTimeConst::FULL_TIME ||
            $hasLeave == ManageTimeConst::HAS_LEAVE_DAY_MORNING ||
            $hasLeave == ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON ||
            $hasLeave == ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF ||
            $hasLeave == ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF) {
            $leave = [[$hasLeave], [$registerLeave]];
        } else {
            $leave = static::leaveSpecial($timekeepingOfEmployee);
        }
        return $leave;
    }

    /**
     * ky hieu thoi gian nghi
     * @param  [object] $timekeepingOfEmployee 
     * @param  [boolean] $isSign
     * @param  float $absent
     * @return [array]
     */
    public static function getSignLeave($timekeepingOfEmployee, $isSign, $absent)
    {
        $hasLeaveDay = $timekeepingOfEmployee->has_leave_day;
        $leaveDaySalary = $timekeepingOfEmployee->register_leave_has_salary;
        $hasLeaveDayNo = $timekeepingOfEmployee->has_leave_day_no_salary;
        $leaveDayNoSalary = $timekeepingOfEmployee->register_leave_no_salary;
        $totalLeave = $leaveDaySalary + $leaveDayNoSalary;
        $leaveDayBasicSalary = $timekeepingOfEmployee->register_leave_basic_salary;

        $hasBusiness = $timekeepingOfEmployee->has_business_trip;

        $leaveHas = [array(), array()];
        $leaveNo = [array(), array()];
        $leave = 0;
        $timekeepingSign = '';
        if ($hasLeaveDay || $hasLeaveDayNo) {
            if ($hasLeaveDay) {
                $leaveHas = static::getleaveDay($timekeepingOfEmployee, $hasLeaveDay, $leaveDaySalary);
            }
            if ($hasLeaveDayNo) {
                $leaveNo = static::getleaveDay($timekeepingOfEmployee, $hasLeaveDayNo, $leaveDayNoSalary);
            }
            if ($hasLeaveDay) {
                $phep = 0;
                $phepL = 0;
                if (count($leaveHas)) {
                    foreach ($leaveHas[0] as $key => $value) {
                        if ($hasBusiness && ($hasBusiness == ManageTimeConst::FULL_TIME ||
                            $value == $hasBusiness ||
                            ($hasBusiness == ManageTimeConst::MORNING && $value == ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF) ||
                            ($hasBusiness == ManageTimeConst::AFTERNOON && $value == ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF))) {
                            $phepL = $phepL + $leaveHas[1][$key];
                        } else {
                            $absent = $absent - $leaveHas[1][$key];
                            $phep = $phep + $leaveHas[1][$key];
                        }
                    }
                }
                // if ($phepL) { phép trùng
                //     $timekeepingSign = $timekeepingSign .'&#8801; P: ' . $phepL;
                // }
                // if ($phep) { phép không trùng
                //     $timekeepingSign = $timekeepingSign .', P: ' . $phep;
                // }
                // $timekeepingSign = $timekeepingSign .', P: ' . $leaveDaySalary;
                if (!empty((float)$leaveDaySalary - $leaveDayBasicSalary)) {
                    $number = number_format($leaveDaySalary - $leaveDayBasicSalary, 2);
                    $timekeepingSign = $timekeepingSign .', P: ' . $number;
                }
                if (!empty((float)$leaveDayBasicSalary)) {
                    $timekeepingSign = $timekeepingSign .', LCB: ' . $leaveDayBasicSalary;
                }
            }
            if ($hasLeaveDayNo) {
                $phepKL = 0;
                $phepKLL = 0;
                foreach ($leaveNo[0] as $key => $value) {
                    if ($hasBusiness && ($hasBusiness == ManageTimeConst::FULL_TIME ||
                        $value == $hasBusiness ||
                        ($hasBusiness == ManageTimeConst::MORNING && $value == ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF) ||
                        ($hasBusiness == ManageTimeConst::AFTERNOON && $value == ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF))) {
                        $phepKLL = $phepKLL + $leaveNo[1][$key];
                    } else {
                        $absent = $absent - $leaveNo[1][$key];
                        $phepKL = $phepKL + $leaveNo[1][$key];
                    }
                }
                // if ($phepKLL) {phép trùng
                //     $timekeepingSign = $timekeepingSign .'&#8801; KL: ' . $phepKLL;
                // }
                // if ($phepKL) {phép không trùng
                //     $timekeepingSign = $timekeepingSign .', KL: ' . $phepKL;
                // }
                $timekeepingSign = $timekeepingSign .', KL: ' . $leaveDayNoSalary;
            }

            if ($totalLeave >= ManageTimeConst::FULL_TIME) {
                $isSign = false;
            }
        }
        return [
            'isSign' => $isSign,
            'leaveHas' => $leaveHas,
            'leaveNo' => $leaveNo,
            'absent' => $absent,
            'timekeepingSign' => $timekeepingSign,
        ];
    }

    /**
     * ky hieu thoi gian lam viec
     * @param  [object] $timekeepingOfEmployee
     * @param  int $absent
     * @param  [array] $leaveHas [nghi co phep]
     * @param  [array] $leaveNo [nghỉ không phép]
     * @return [array]
     */
    public static function getSignTimeWoking($timekeepingOfEmployee, $absent, $leaveHas, $leaveNo)
    {
        $timekeeping = $timekeepingOfEmployee->timekeeping;
        $timekeepingNumber = (float)$timekeepingOfEmployee->timekeeping_number;
        $timekeepingNumberRegister = (float)$timekeepingOfEmployee->timekeeping_number_register; 
        $timeWork = $timekeepingNumber + $timekeepingNumberRegister;

        // cong tac
        $hasBusiness = $timekeepingOfEmployee->has_business_trip;
        // phep
        $hasLeaveDay = $timekeepingOfEmployee->has_leave_day;
        $hasLeaveDayNo = $timekeepingOfEmployee->has_leave_day_no_salary;
        $totalLeave = $timekeepingOfEmployee->register_leave_has_salary + $timekeepingOfEmployee->register_leave_no_salary;
        //bo sung cong
        $hasSupplement = $timekeepingOfEmployee->has_supplement;

        $arrHas = [$hasBusiness, $hasLeaveDay, $hasLeaveDayNo, $hasSupplement];
        $timekeepingSign = '';
        if ($timeWork && $timeWork > 0) {
            if ($timekeepingNumberRegister) {
                if ($timekeepingNumber == 1) {
                    $timekeepingSign = $timekeepingSign . ', X: ' . $absent;
                    $absent = 0;
                } else {
                    if ($hasSupplement) {
                        if ($hasSupplement == ManageTimeConst::AFTERNOON &&
                            (in_array(ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF, $leaveNo[0]) ||
                                in_array(ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF, $leaveHas[0]))) {
                            $timekeepingSign = $timekeepingSign . ', X: ' . $timekeepingNumber;
                            $absent = $absent - $timekeepingNumber;
                        } elseif ($hasSupplement == ManageTimeConst::MORNING &&
                            (in_array(ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF, $leaveNo[0]) ||
                                in_array(ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF, $leaveHas[0]))) {
                            if ($timekeeping != ManageTimeConst::MORNING) {
                                $timekeepingSign = $timekeepingSign . ', X: ' . $timekeepingNumber;
                                $absent = $absent - $timekeepingNumber;
                            }
                        } elseif (($hasSupplement == ManageTimeConst::AFTERNOON &&
                                (in_array(ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF, $leaveNo[0]) ||
                                in_array(ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF, $leaveHas[0]))) ||
                                ($hasSupplement == ManageTimeConst::MORNING &&
                                (in_array(ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF, $leaveNo[0]) ||
                                in_array(ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF, $leaveHas[0])))) {
                                $timekeepingSign = $timekeepingSign . ', X: ' . $absent;
                                $absent = 0;
                        } else {
                            $timekeepingSign = $timekeepingSign . ', X: ' . $timeWork;
                            $absent = $absent - $timeWork;
                        }
                    } else {
                        $timekeepingSign = $timekeepingSign . ', X: ' . $timeWork;
                        $absent = $absent - $timeWork;
                    }
                }
            } else {
                if ($timekeeping == ManageTimeConst::FULL_TIME) {
                    if (!$hasBusiness && !$hasLeaveDay && !$hasLeaveDayNo && !$hasSupplement) {
                        $timekeepingSign = $timekeepingSign . ', X ';
                        $absent = 0;
                    } elseif ($hasBusiness) {
                       $timekeepingSign = $timekeepingSign . ', X: ' . ($timeWork - $timekeepingOfEmployee->register_business_trip_number);
                       $absent = 0;
                    } else {
                        if (in_array(ManageTimeConst::MORNING, $arrHas) &&
                            in_array(ManageTimeConst::AFTERNOON, $arrHas)) {
                        } elseif (in_array(ManageTimeConst::MORNING, $arrHas) ||
                            in_array(ManageTimeConst::AFTERNOON, $arrHas)) {
                            $timekeepingSign = $timekeepingSign . ', X: ' . ($timeWork - $totalLeave - $timekeepingOfEmployee->register_supplement_number);;
                            $absent = $absent - $timekeepingNumber;
                        } else {
                        }
                    }
                } elseif ($timekeeping == ManageTimeConst::PART_TIME_MORNING) {
                    if (in_array($hasLeaveDay, [ManageTimeConst::HAS_LEAVE_DAY_MORNING, ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF])
                         || in_array($hasLeaveDayNo, [ManageTimeConst::HAS_LEAVE_DAY_MORNING, ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF])   ) {
                        $timekeepingSign = $timekeepingSign . ', X: ' . ($timekeepingNumber - $totalLeave);
                        $absent = $absent - ($timekeepingNumber - $totalLeave);
                    } elseif (!in_array(ManageTimeConst::PART_TIME_MORNING, $arrHas)) {
                        $timekeepingSign = $timekeepingSign . ', X: ' . $timekeepingNumber;
                        $absent = $absent - $timekeepingNumber;
                    }
                } elseif ($timekeeping == ManageTimeConst::PART_TIME_AFTERNOON) { // nua chieu 4
                    if (in_array($hasLeaveDay, [ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON, ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF])
                            || in_array($hasLeaveDayNo, [ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON, ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF])) {
                        $timekeepingSign = $timekeepingSign . ', X: ' . ($timekeepingNumber - $totalLeave);
                        $absent = $absent - ($timekeepingNumber - $totalLeave);
                    } elseif (!in_array(ManageTimeConst::AFTERNOON, $arrHas)) { //p, bsc, ct nua chieu la 3
                        $timekeepingSign = $timekeepingSign . ', X: ' . $timeWork;
                        $absent = $absent - $timekeepingNumber;
                    }
                } else {
                }
            }
        }
        if ($absent > 0) {
            $timekeepingSign = $timekeepingSign . ', V: ' . $absent;
        }
        return [
            'timekeepingSign' => $timekeepingSign,
        ];
    }

    /**
     * ky hieu thoi gian OT
     * @param  [object] $timekeepingOfEmployee
     * @return [arrrya]
     */
    public static function getSignOT($timekeepingOfEmployee)
    {
        $registerOTHasSalary = $timekeepingOfEmployee->register_ot_has_salary;
        $registerOTNoSalary = $timekeepingOfEmployee->register_ot_no_salary;
        $registerOT = $timekeepingOfEmployee->register_ot;
        $timekeepingSign = '';
        if (($registerOTHasSalary > 0 || $registerOTNoSalary > 0) && $registerOT > 0) {
            if ($registerOTHasSalary > 0) {
                $timekeepingSign = $timekeepingSign . ', ' . 'OT: ' . $registerOTHasSalary;
            }
            if ($registerOTNoSalary > 0) {
                $timekeepingSign = $timekeepingSign . ', ' . 'OTKL: ' . $registerOTNoSalary;
            }
        }
        return [
            'timekeepingSign' => $timekeepingSign,
        ];
    }

    /**
     * @param $timekeepingOfEmployee
     * @param $isSign
     * @param $leaveHas
     * @param $leaveNo
     * @param $absent
     * @return array
     */
    public static function getSignSupplement($timekeepingOfEmployee, $isSign, $leaveHas, $leaveNo, $absent)
    {
        $hasSupplement = $timekeepingOfEmployee->has_supplement;
        $totalLeave = $timekeepingOfEmployee->register_leave_has_salary + $timekeepingOfEmployee->register_leave_no_salary;
        $timekeepingSign = '';

        if ($hasSupplement == ManageTimeConst::FULL_TIME) {
            $timekeepingSign = $timekeepingSign . ', BS: ' . ($hasSupplement - $totalLeave);
            $isSign = false;
        } elseif ($totalLeave > ManageTimeConst::TIME_MORE_HALF && $timekeepingOfEmployee->register_supplement_number < 1) {
            $timekeepingSign = $timekeepingSign . ', BS: ' . (1 - $totalLeave);
            $absent = 0;
            $isSign = false;
        } else {
            if ($hasSupplement == ManageTimeConst::MORNING) {
                $regSupp = static::getRegisterSupp($timekeepingOfEmployee, ManageTimeConst::MORNING, $absent, $leaveHas, $leaveNo);
            } else {
                $regSupp = static::getRegisterSupp($timekeepingOfEmployee, ManageTimeConst::AFTERNOON, $absent, $leaveHas, $leaveNo);
            }
            $absent = $regSupp['absent'];
            $sup = $regSupp['sup'];
            if ($sup) {
                $timekeepingSign = $timekeepingSign . ', BS: ' . $sup;
            }
        }
        return [
            'isSign' => $isSign,
            'absent' => $absent,
            'timekeepingSign' => $timekeepingSign,
        ];
    }

    /**
     * tinh toan lai cac truong hop phep dac biet
     * @param  [object] $timekeepingOfEmployee
     * @return [array]
     */
    public static function leaveSpecial($timekeepingOfEmployee)
    {
        $empId = $timekeepingOfEmployee->employee_id;
        $dateStart = $timekeepingOfEmployee->timekeeping_date;
        $dateEnd = $timekeepingOfEmployee->timekeeping_date;
        $leaveDayRegister = leaveDayRegister::getLeaveDayApproved($empId, $dateStart, $dateEnd);
        $leave = [array(), array()];
        foreach ($leaveDayRegister as $regLeave) {
            $startDate = Carbon::parse($regLeave->date_start);
            $endDate = Carbon::parse($regLeave->date_end);
            if ($startDate->format('Y-m-d') == $dateStart) {
                $start = clone $startDate;
            } elseif ($endDate->format('Y-m-d') == $dateStart) {
                $start = clone $endDate;
            } else {
                continue;
            }
            $number = $regLeave->register_leave_no_salary + $regLeave->register_leave_has_salary;
            if ($startDate->format('Y-m-d') == $endDate->format('Y-m-d')) {
                if ($start->hour <= 13) {
                    if (in_array(5, $leave[0])) {
                        $key = array_search (5, $leave[0]);
                        $leave[0][$key] = 2;
                        $leave[1][$key] = $leave[1][$key] + $number;
                    } else {
                        $leave[0][] = 5;
                        $leave[1][] = $regLeave->number_days_off;
                    }
                } else {
                    if (in_array(7, $leave[0])) {
                        $key = array_search (7, $leave[0]);
                        $leave[0][$key] = 3;
                        $leave[1][$key] = $leave[1][$key] + $number;
                    } else {
                        $leave[0][] = 7;
                        $leave[1][] = $regLeave->number_days_off;
                    }
                }
            } else {
                if ($start->hour <= 13) {
                    if (in_array(5, $leave[0])) {
                        $key = array_search (5, $leave[0]);
                        $leave[0][$key] = 2;
                        $leave[1][$key] = $leave[1][$key] + $number;
                    } else {
                        $leave[0][] = 5;
                        $leave[1][] = $number;
                    }
                } else {
                    if (in_array(7, $leave[0])) {
                        $key = array_search (7, $leave[0]);
                        $leave[0][$key] = 3;
                        $leave[1][$key] = $leave[1][$key] + $number;
                    } else {
                        $leave[0][] = 7;
                        $leave[1][] = $number;
                    }
                }
            }
            
        }
        return $leave;
    }

    /**
     * tinh toan lai bo sung cong
     * @param  [object] $timekeepingOfEmployee
     * @param  [int] $hasSupp [morning or afternoon]
     * @param  [int] $absent
     * @param  [array] $leaveHas
     * @param  [array] $leaveNo
     * @return [array]
     */
    public static function getRegisterSupp($timekeepingOfEmployee, $hasSupp, $absent, $leaveHas, $leaveNo)
    {
        $supplement = $timekeepingOfEmployee->register_supplement_number;
        $hasLeaveDay = $timekeepingOfEmployee->has_leave_day;
        $hasLeaveDayNo = $timekeepingOfEmployee->has_leave_day_no_salary;
        $hasBusiness = $timekeepingOfEmployee->has_business_trip;

        switch ($hasSupp) {
            case ManageTimeConst::MORNING:
                $half = ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF;
                break;
            case ManageTimeConst::AFTERNOON:
                $half = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF;
                break;
            default:
                $half = ManageTimeConst::FULL_TIME;
                break;
        }

        if ($hasSupp && ($hasSupp == $hasBusiness ||
            in_array($hasSupp, $leaveHas[0]) ||
            in_array($hasSupp, $leaveNo[0]))) {
            $sup = 0;
        } elseif (($hasSupp != $hasBusiness) &&
            (!in_array($half, $leaveHas[0])) &&
            (!in_array($half, $leaveNo[0]))) {
            $sup = $supplement;
            $absent = $absent - $supplement;
        } else {
            $sup = $supplement;
        }

        if ($sup && $hasLeaveDay) {
            if (in_array($half, $leaveHas[0])) {
                foreach ($leaveHas[0] as $key => $value) {
                    if ($value == $hasSupp || $half == $value) {
                        $sup = $sup - $leaveHas[1][$key];
                        $absent = $absent - $sup;
                    }
                }
            }
        }

        if ($sup && $hasLeaveDayNo) {
            if (in_array($half, $leaveNo[0])) {
                foreach ($leaveNo[0] as $key => $value) {
                    if ($value == $hasSupp || $half == $value) {
                        $sup = $sup - $leaveNo[1][$key];
                        $absent = $absent - $sup;
                    }
                }
            }
        }
        return [
            'sup' => $sup,
            'absent' => $absent,
        ];
    }

    /**
     * setting time 1/4 HCM
     * @return [array]
     */
    public function getTimeSettingQuarterHCM()
    {
        return [
            'timeInMor' => [
                '08:30',
                '10:30'
            ],
            'timeOutMor' => [
                '10:30',
                '12:00'
            ],
            'timeInAfter' => [
                '13:15',
                '15:45'
            ],
            'timeOutAfter' => [
                '15:45',
                '17:45'
            ],
            'timeIn' => [
                '08:30',
                '10:30',
                '13:15',
                '15:45',
            ],
            'timeOut' => [
                '10:30',
                '12:00',
                '15:45',
                '17:45'
            ]
        ];
    }

    /**
     * setting time 1/4 HN
     * @return [array]
     */
    public function getTimeSettingQuarterHN()
    {
        return [
            'timeInMor' => [
                '08:00',
                '10:00'
            ],
            'timeOutMor' => [
                '10:00',
                '12:00'
            ],
            'timeInAfter' => [
                '13:30',
                '15:30'
            ],
            'timeOutAfter' => [
                '15:30',
                '17:30'
            ],
            'timeIn' => [
                '08:00',
                '10:00',
                '13:30',
                '15:30',
            ],
            'timeOut' => [
                '10:00',
                '12:00',
                '15:30',
                '17:30'
            ]
        ];
    }

    /**
     * setting time 1/4 japan
     * @return [array]
     */
    public function getTimeSettingQuarterJapan()
    {
        return [
            'timeInMor' => [
                '09:00',
            ],
            'timeOutMor' => [
                '13:00',
            ],
            'timeInAfter' => [
                '14:00',
            ],
            'timeOutAfter' => [
                '18:00'
            ],
            'timeIn' => [
                '09:00',
                '14:00',
            ],
            'timeOut' => [
                '13:00',
                '18:00'
            ]
        ];
    }

    /**
     * get time 1/4 of chi nhanh
     * @param  [type] $teamCodePrefix
     * @return [type]
     */
    public function getTimeSettingQuarter($teamCodePrefix)
    {
        switch ($teamCodePrefix) {
            case Team::CODE_PREFIX_HCM:
            case Team::CODE_PREFIX_RS:
                $arrTime = $this->getTimeSettingQuarterHCM();
                break;
            case Team::CODE_PREFIX_JP:
                $arrTime = $this->getTimeSettingQuarterJapan();
                break;
            default:
                $arrTime = $this->getTimeSettingQuarterHN();
                break;
        }
        return $arrTime;
    }
        
    /**
     * getTimeSettingQuarterByWTRegister
     *
     * @param  collection $workingTimeOfEmployee
     * @return array
     */
    public function getTimeSettingQuarterByWTRegister($workingTimeOfEmployee)
    {
        return [
            'timeInMor' => [
                $workingTimeOfEmployee->start_time1,
                $workingTimeOfEmployee->half_morning
            ],
            'timeOutMor' => [
                $workingTimeOfEmployee->half_morning,
                $workingTimeOfEmployee->end_time1
            ],
            'timeInAfter' => [
                $workingTimeOfEmployee->start_time2,
                $workingTimeOfEmployee->half_afternoon
            ],
            'timeOutAfter' => [
                $workingTimeOfEmployee->half_afternoon,
                $workingTimeOfEmployee->end_time2
            ],
            'timeIn' => [
                $workingTimeOfEmployee->start_time1,
                $workingTimeOfEmployee->half_morning,
                $workingTimeOfEmployee->start_time2,
                $workingTimeOfEmployee->half_afternoon
            ],
            'timeOut' => [
                $workingTimeOfEmployee->half_morning,
                $workingTimeOfEmployee->end_time1,
                $workingTimeOfEmployee->half_afternoon,
                $workingTimeOfEmployee->end_time2
            ]
        ];
    }
}
