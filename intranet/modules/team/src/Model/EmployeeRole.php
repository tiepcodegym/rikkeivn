<?php
namespace Rikkei\Team\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Illuminate\Support\Facades\DB;

class EmployeeRole extends CoreModel
{
    protected $table = 'employee_roles';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employee_id', 'role_id', 'start_at', 'end_at'
    ];
    
    /**
     * get collection to show grid
     * 
     * @return type
     */
    public static function getGridData($roleId = null)
    {
        $pager = Config::getPagerData();
        $collection = self::select('employees.id as id','employees.name as name', 'employees.email as email')
            ->leftJoin('employees', 'employee_roles.employee_id', '=', 'employees.id')
            ->orderBy($pager['order'], $pager['dir']);
        if ($roleId) {
            $collection = $collection->where('role_id', $roleId);
        }
        $collection = $collection->paginate($pager['limit']);
        return $collection;
    }
    
    /**
     * get leader and sub
     *
     * @param array $teamIds
     * @return array
     */
    public static function getLeadAndSub($teamIds = [], $working = false)
    {
        if (!count($teamIds)) {
            return [];
        }
        $tableEmployee = Employee::getTableName();
        $tableTeam = Team::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tableRole = Role::getTableName();
        
        $collection = DB::table($tableEmployee . ' AS t_empl')
            ->select(['t_empl.id', 't_empl.name', 't_empl.email', 't_team.id as team_id', 't_team_mem.role_id as role_id'])
            ->join($tableTeamMember . ' AS t_team_mem', 't_team_mem.employee_id', '=', 't_empl.id')
            ->join($tableTeam . ' AS t_team', 't_team.id', '=', 't_team_mem.team_id')
            ->join($tableRole . ' AS t_role', 't_role.id', '=', 't_team_mem.role_id')
            ->whereNull('t_empl.deleted_at')
            ->whereNull('t_team_mem.deleted_at')
            ->whereNull('t_team.deleted_at')
            ->whereNull('t_role.deleted_at')
            ->whereIn('t_team.id', $teamIds)
            ->where('t_role.special_flg', Role::FLAG_POSITION)
            // order to get leader(1) and sub (2)
            ->whereIn('t_team_mem.role_id', [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER]);
        if ($working) {
            $collection = $collection->where(function ($query) {
                $query->whereNull('t_empl.leave_date')
                    ->orwhere('t_empl.leave_date', '>', Carbon::now()->format('Y-m-d'));
            });
        }
        $collection = $collection->groupBy('t_empl.id')
            ->orderBy('t_role.sort_order', 'asc')
            ->get();
        $result = [];
        foreach ($collection as $item) {
            $result[$item->id] = [
                'id' => $item->id,
                'team_id' => $item->team_id,
                'role_id' => $item->role_id,
                'name' => $item->name,
                'email' => $item->email
            ];
        }
        return $result;
    }

    /**
     * get leader
     *
     * @param array $teamIds
     * @return array
     */
    public static function getLeader($teamIds = [], $working = false)
    {
        if (!count($teamIds)) {
            return [];
        }
        $tableEmployee = Employee::getTableName();
        $tableTeam = Team::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tableRole = Role::getTableName();
        
        $collection = DB::table($tableEmployee . ' AS t_empl')
            ->select(['t_empl.id', 't_empl.name', 't_empl.email', 't_team.id as team_id', 't_team.name as team_name', 't_team_mem.role_id as role_id'])
            ->join($tableTeamMember . ' AS t_team_mem', 't_team_mem.employee_id', '=', 't_empl.id')
            ->join($tableTeam . ' AS t_team', 't_team.id', '=', 't_team_mem.team_id')
            ->join($tableRole . ' AS t_role', 't_role.id', '=', 't_team_mem.role_id')
            ->whereNull('t_empl.deleted_at')
            ->whereNull('t_team_mem.deleted_at')
            ->whereNull('t_team.deleted_at')
            ->whereNull('t_role.deleted_at')
            ->whereIn('t_team.id', $teamIds)
            ->where('t_role.special_flg', Role::FLAG_POSITION)
            ->where('t_team_mem.role_id', Team::ROLE_TEAM_LEADER);
        if ($working) {
            $collection = $collection->where(function ($query) {
                $query->whereNull('t_empl.leave_date')
                    ->orwhere('t_empl.leave_date', '>', Carbon::now()->format('Y-m-d'));
            });
        }
        $collection = $collection->groupBy('t_empl.id')
            ->orderBy('t_role.sort_order', 'asc')
            ->get();
        $result = [];
        foreach ($collection as $item) {
            $result[$item->id] = [
                'id' => $item->id,
                'team_id' => $item->team_id,
                'role_id' => $item->role_id,
                'name' => $item->name,
                'team_name' => $item->team_name,
                'email' => $item->email
            ];
        }
        return $result;
    }

    public static function hasRole($role)
    {
        $roleData = Role::where('role', $role)->first();
        if (!$roleData) {
            return false;
        }

        $curEmp = \Rikkei\Team\View\Permission::getInstance()->getEmployee();
        $check = self::where('employee_id', $curEmp->id)->where('role_id', $roleData->id)->first();
        if (!$check) {
            return false;
        }
        return true;
    }
}
