<?php
namespace Rikkei\Team\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Rikkei\Files\Model\ManageFileText;
use Rikkei\Team\Model\Permission;

class TeamMember extends CoreModel
{
    protected $table = 'team_members';
    
    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'team_id', 'employee_id', 'position_id'
    ];

    /**
     * Get TeamMember by employee
     * 
     * @param int $employeeId
     * @return TeamMember list
     */
    public function getTeamMembersByEmployee ($employeeId){
        return self::where('employee_id',$employeeId)->get();
    }

    /**
     * check team has member
     * @param int
     * @param int
     * @param int
     * @return boolean
     */
    public static function checkTeamMember($teamId, $employeeId, $roleId)
    {
        return self::where('team_id', $teamId)
                    ->where('employee_id', $employeeId)
                    ->where('role_id', $roleId)
                    ->count() ? true : false;
    }

    /**
     * Get TeamMember by employee
     * 
     * @param int $employeeId
     * @param array $selectedFields
     * @return TeamMember list
     */
    public static function getTeamMembersByEmployees ($employeeId, $selectedFields = ['*']) {
        return self::where('employee_id', $employeeId)
            ->select($selectedFields)
            ->get();
    }

    /**
     * get leader or subleader
     * @param int
     * @param int
     * @return array
     */
    public static function getLeaderOrSubleader($employeeId, $roleId)
    {
        return self::where('employee_id', $employeeId)
                    ->whereIn('role_id', $roleId)
                    ->select(['team_id'])
                    ->get();
    }

    /**
     * Get Leader and sub leader by team ID
     *
     * @param $teamId
     * @return mixed
     */
    public static function getLeaderAndSubLeaderByTeamId($teamId)
    {
        $roleIds = [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER];
        return TeamMember::query()
            ->select(['id', 'name', 'email'])
            ->join('employees', 'employees.id', '=', 'team_members.employee_id')
            ->where('team_members.team_id', $teamId)
            ->where(function ($query) {
                $query->whereNull('leave_date')
                      ->orWhereDate('leave_date', '>', Carbon::now()->format('Y-m-d'));
            })
            ->whereIn('role_id', $roleIds)
            ->get();
    }

    /**
     * get all memeber of team
     * @param int
     * @return array
     */
    public static function getAllMemberOfTeam($teamId)
    {
        if (!is_array($teamId)) {
            $teamId = [$teamId];
        }
        return self::whereIn('team_id', $teamId)
                    ->where('role_id', Team::ROLE_MEMBER)
                    ->lists('employee_id')
                    ->toArray();
    }
    
    /**
     * get Employees by teamId
     * @param int
     * @return array
     */
    public static function getEmployeesByTeamId($teamId)
    {
        return self::where('teams.id', $teamId)
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->join('employees', 'employees.id', '=', 'team_members.employee_id')
            ->lists('employees.email')
            ->toArray();
    }

    /**
     * get all memeber of team by team code
     * @param string
     * @return array
     */
    public static function getAllMemberOfTeamByCode($teamCode)
    {
        return self::where('teams.code', $teamCode)
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->join('employees', 'employees.id', '=', 'team_members.employee_id')
            ->select('employee_id', 'employees.name')
            ->get();
    }

    /**
     * check member of team
     * @param string
     * @param int
     * @return boolean
     */
    public static function checkMemberOfTeamByTeamCode($teamCode, $employeeId)
    {
        return self::where('teams.code', $teamCode)
                    ->where('team_members.employee_id', $employeeId)
                    ->join('teams', 'teams.id', '=', 'team_members.team_id')
                    ->join('employees', 'employees.id', '=', 'team_members.employee_id')
                    ->count() ? true : false;
    }
    
    /**
     * get teams of employees
     * 
     * @param type $employeeIds
     * @return collection
     */
    public static function getTeamOfEmployee($employeeIds)
    {
        return self::select('employee_id',
            DB::raw('GROUP_CONCAT(team_id SEPARATOR ",") AS team_ids'))
            ->whereIn('employee_id', $employeeIds)
            ->groupBy('employee_id')
            ->get();
    }

    /**
     * check team include employee?
     *
     * @param int $employeeId
     * @param array $teamIds
     * @return boolean
     */
    public static function isEmployeeOfTeam($employeeId, array $teamIds)
    {
        $result = self::select('team_id')->where('employee_id', $employeeId)
            ->whereIn('team_id', $teamIds)
            ->first();
        if ($result) {
            return true;
        }
        return false;
    }

    public static function getLeaderOfEmployee($employee)
    {
        // get team first - leader not leave
        $tblTeam = Team::getTableName();
        $tblMember = self::getTableName();
        $tblEmployee = Employee::getTableName();
        $team = self::select([$tblMember . '.team_id', $tblMember . '.role_id',
            't_team.leader_id', 't_emp.email', 't_emp.name'])
            ->join($tblTeam . ' AS t_team', function ($query) use ($tblMember){
                $query->on('t_team.id', '=', $tblMember . '.team_id')
                    ->whereNull('t_team.deleted_at')
                    ->whereNotNull('t_team.leader_id');
            })
            ->join($tblEmployee . ' AS t_emp', function ($query) {
                $query->on('t_emp.id', '=', 't_team.leader_id')
                    ->whereNull('t_emp.deleted_at')
                    ->whereNull('t_emp.leave_date');
            })
            ->where('employee_id', $employee->id)
            ->orderBy('team_id', 'ASC')
            ->first();
        return $team;
    }

    /**
     * get leader by team id
     */
    public static function getLeaderByTeamId($teamId)
    {
        $tmbTbl = self::getTableName();
        return self::join(Employee::getTableName() . ' as emp', $tmbTbl.'.employee_id', '=', 'emp.id')
                ->whereNull('emp.deleted_at')
                ->whereNull('emp.leave_date')
                ->where($tmbTbl.'.team_id', $teamId)
                ->where($tmbTbl.'.role_id', Team::ROLE_TEAM_LEADER)
                ->select('emp.id', 'emp.name', 'emp.email')
                ->get();
    }

    /**
     * get list leader or subleader by team id
     *
     * @param array $teamId
     * @return collection.
     */
    public static function getListLeaderByTeamIds($teamId)
    {
        $tmbTbl = self::getTableName();
        return self::join(Employee::getTableName() . ' as emp', $tmbTbl.'.employee_id', '=', 'emp.id')
                ->join('employee_team_history' . ' as history', $tmbTbl . '.employee_id', '=', 'history.employee_id')
                ->whereNull('history.end_at')
                ->whereNull('emp.deleted_at')
                ->whereNull('emp.leave_date')
                ->whereIn($tmbTbl.'.team_id', $teamId)
                ->where(function ($query) use ($tmbTbl) {
                    $query->where($tmbTbl.'.role_id', Team::ROLE_TEAM_LEADER)
                            ->orWhere($tmbTbl.'.role_id', Team::ROLE_SUB_LEADER);
                })
                ->where(function ($query) {
                    $query->whereNull('emp.leave_date')
                        ->orWhere('emp.leave_date', '<=', Carbon::now());
                })
                ->select('emp.id', 'emp.name', 'emp.email')
                ->groupBy('emp.id')
                ->get();
    }

    /**
     * check leader or subleader team follow id employee.
     *
     * @param int $idCheckLeader that compare with id of leader or subleader team. 
     * @param  array $employeeId : list id of employee, in order to Retrieve list id of leader or subleader.
     * @return boolean.
     */
    public static function isLeaderOrSubleader($idCheckLeader, $employeeId)
    {
        $teamMembersModel = new TeamMember();
        $teamEmpCollection = $teamMembersModel->getTeamMembersByEmployee($employeeId);
        $teamEmpIds = [];
        foreach ($teamEmpCollection as $teamMember) {
            $teamEmpIds[] = $teamMember->team_id;
        }
        $leadersOfEmp = TeamMember::getListLeaderByTeamIds($teamEmpIds);
        $idsLeader = [];
        foreach ($leadersOfEmp as $leader) {
            $idsLeader[] = $leader->id;
        }

        return in_array($idCheckLeader, $idsLeader);
    }

    /*
     * get all team leaders
     */
    public static function getAllLeaders($includeSubLead = false, $isSoftDev = false, $teamIds = [])
    {
        $roleIds = [Team::ROLE_TEAM_LEADER];
        if ($includeSubLead) {
            $roleIds[] = Team::ROLE_SUB_LEADER;
        }
        $empTbl = Employee::getTableName();
        $collect = Employee::select($empTbl.'.id', $empTbl.'.name', $empTbl.'.email')
            ->join(self::getTableName() . ' as tmb', 'tmb.employee_id', '=', $empTbl.'.id')
            ->whereIn('tmb.role_id', $roleIds)
            ->where(function ($query) use ($empTbl) {
                $query->whereNull($empTbl.'.leave_date')
                    ->orWhereRaw('DATE('. $empTbl .'.leave_date) > CURDATE()');
            });
        if ($isSoftDev) {
            $collect->join(Team::getTableName() . ' as team', 'team.id', '=', 'tmb.team_id')
                    ->where('team.is_soft_dev', Team::IS_SOFT_DEVELOPMENT);
        }
        if ($teamIds) {
            $collect->whereIn('tmb.team_id', $teamIds);
        }
        return $collect->get();
    }

    /**
     * Get team and role of user
     * eg: Production Member, D3 Member
     *
     * @param $employeeId
     * @return mixed
     */
    public static function getTeamAndRole($employeeId)
    {
        return TeamMember::selectRaw('GROUP_CONCAT(CONCAT(teams.name, " ", roles.role) SEPARATOR ", ") as team_role')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->leftJoin('roles', 'team_members.role_id', '=', 'roles.id')
            ->where('team_members.employee_id', $employeeId)
            ->first();
    }

    /**
     * Get team of user
     * eg: Production Member, D3 Member
     *
     * @param $employeeId
     * @return mixed
     */
    public static function getTeamEmployee($employeeId)
    {
        return TeamMember::select('teams.id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->leftJoin('roles', 'team_members.role_id', '=', 'roles.id')
            ->where('team_members.employee_id', $employeeId)
            ->get();
    }

    /**
     * get employee in TCT-PKT
     */
    public static function checkEmployee()
    {
        return DB::table('team_members')
            ->join('teams', 'team_members.team_id', '=', 'teams.id')
            ->where([
                'team_members.employee_id' => auth()->id(),
                'teams.code' => ManageFileText::CODE_HC
            ])
            ->first();
    }

    public function getTeam(){
        return self::belongsTo(Team::class, 'team_id', 'id');
    }

    /**
     * get List Employee Has Permission.
     *
     * @param $teamId
     * @param $actionName
     * @return array
     */
    public static function listIsScopeTeamofEmployee($teamId, $actionName)
    {
        $teamMemberTable = self::getTableName();
        $permission = new Permission();

        $sql = $permission->getEmployeeByActionName($actionName);
        $sql = DB::table(DB::raw("({$sql->toSql()}) as emps"))
            ->mergeBindings($sql->getQuery());

        $listEmployee = $sql->leftjoin($teamMemberTable, $teamMemberTable . '.employee_id', '=', 'emps.id')
            ->whereIn($teamMemberTable . '.team_id', $teamId)
            ->whereIn($teamMemberTable . '.role_id', [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER])
            ->where(function ($query) {
                $query->whereNull('emps.leave_date')
                    ->orWhere('emps.leave_date', '>=', Carbon::now());
            })
            ->select('emps.id', 'emps.name', 'emps.email')
            ->groupBy('emps.id')
            ->get();

        $response = [];
        if ($listEmployee) {
            foreach ($listEmployee as $employee) {
                $response[$employee->id] = $employee->name;
            }
        }

        return $response;
    }

    public static function updateTeamIsWorking()
    {
        $tmp = TeamMember::join('teams', 'team_members.team_id', '=', 'teams.id')
            ->where('teams.branch_code', Team::CODE_PREFIX_JP)
            ->groupBy('team_members.employee_id')
            ->select('teams.id', 'teams.branch_code', 'team_members.*')->get();
        $employees = [];
        if (count($tmp) > 0) {
            foreach ($tmp as $item) {
                array_push($employees, $item->employee_id);
                $item->is_working = self::IS_WORKING;
                $item->save();
            }
        }
        $result = TeamMember::join('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereNotIn('team_members.employee_id', $employees)
            ->select('teams.id', 'team_members.team_id', 'team_members.employee_id', 'team_members.is_working')
            ->orderBY('employee_id', 'asc')
            ->limit(30)
            ->get()
            ->groupBy('employee_id')
            ->map(function ($abc) {
                return $abc[0];
            });
        if (count($result) > 0) {
            foreach ($result as $value) {
                if ($value->employee_id == 1) {
                    $value->is_working = self::IS_WORKING;
                    $value->save();
                }
/*                    $a = TeamMember::find($value->team_id);
                    $a->is_working = self::IS_WORKING;
                    $a->save();*/

            }
        }
    }
       
    /**
     * get employee by list team id
     *
     * @param  array $teamIds
     * @return collection
     */
    public function getEmployeesByTeam($teamIds)
    {
        return self::select(
            'team_members.team_id',
            'employees.id',
            'employees.name',
            'employees.email'
        )
        ->join('employees', 'employees.id', '=', 'team_members.employee_id')
        ->whereIn('team_members.team_id', $teamIds)
        ->get();
    }
}
