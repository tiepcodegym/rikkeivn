<?php

namespace Rikkei\Ot\Model;

use Rikkei\Core\Model\CoreModel;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Rikkei\Ot\Model\OtRegister;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Role;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamConst;
use Rikkei\Core\Model\User;
use Rikkei\AdminSetting\Model\AdminOtDisallow;

class OtEmployee extends CoreModel 
{
    use SoftDeletes;
    
    protected $table = 'ot_employees';
    protected $dates = ['deleted_at'];
    
    public $timestamps = false;
    
    /**
     * get OT applicant info
     * @param int $registerId
     * @return array data of OT applicant
     */
    public static function getOTEmployees($registerId) 
    {
        return OtRegister::withTrashed()->find($registerId)->ot_employees()
                ->leftJoin('employees', 'ot_employees.employee_id', '=', 'employees.id')
                ->select('ot_employees.ot_register_id', 'ot_employees.employee_id', 'ot_employees.start_at', 'ot_employees.end_at', 'ot_employees.is_paid', 'ot_employees.time_break', 'employees.name', 'employees.employee_code')
                ->get();
    }

    /**
     * get list id  OT employees
     * @param int $registerId
     * @return array id
     */
    public static function getListIdOtEmloyees($registerId)
    {
        return self::where('ot_register_id',$registerId)->select('employee_id')->lists('employee_id')->toArray();
    }

    /**
     * get list of members of a project
     * @param int $projsId id of project
     * @return array members list
     */
    public static function getProjectMember($projsId) 
    {
        $userId = Permission::getInstance()->getEmployee()->id;
        $proj = Project::find($projsId);
        if (!$proj) {
            return null;
        }
        $leader_id = $proj->leader_id;
        
        return DB::table('project_members')
               ->leftJoin('employees', 'employees.id', '=', 'project_members.employee_id')
               ->select('project_members.employee_id', 'employees.name', 'employees.employee_code', 'employees.email')                
               ->where('project_members.status', '=', ProjectWOBase::STATUS_APPROVED) 
               ->where('project_members.employee_id', '<>', $userId)
               ->where('project_members.employee_id', '<>', $leader_id) 
               ->whereNull('project_members.deleted_at')
               ->where('project_members.project_id', '=', $projsId)
               ->get();
    }
    
    /**
     * get project approvers list
     * @param int $projsId
     * @return array
     */
    public static function getProjectApprovers($projsId, $employeeId)
    {
        //get table names
        $teamMemberTbl = TeamMember::getTableName();
        $teamTbl = Team::getTableName();
        $employeeTbl = Employee::getTableName();
        $roleTbl = Role::getTableName();
        $roleIds = [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER];
        
        //get list of teams project group leader belongs to
        $proj = Project::find($projsId);
        if (!$proj) {
            return null;
        }
        $projsLeader = $proj->leader_id;
        $approversList = DB::table("{$teamMemberTbl}")
            ->select("{$employeeTbl}.id as emp_id", "{$employeeTbl}.name as emp_name", "{$employeeTbl}.email as emp_email", "{$teamMemberTbl}.role_id as role_id", "{$roleTbl}.role", "{$teamTbl}.name as team_name")
            ->leftJoin("{$employeeTbl}", "{$employeeTbl}.id", "=", "{$teamMemberTbl}.employee_id")
            ->leftJoin("{$teamTbl}", "{$teamTbl}.id", "=", "{$teamMemberTbl}.team_id")
            ->leftJoin("{$roleTbl}", "{$roleTbl}.id", "=", "{$teamMemberTbl}.role_id");

        if ($projsLeader == $employeeId) {
            $isLeaderBOD = TeamMember::join("{$teamTbl}", "{$teamTbl}.id", "=", "{$teamMemberTbl}.team_id")
                ->where("{$teamTbl}.code", TeamConst::CODE_BOD)
                ->where("{$teamMemberTbl}.employee_id", $employeeId)
                ->count() ? true : false;
            if ($isLeaderBOD) {
                $roleIds = [Team::ROLE_SUB_LEADER];
            }
            $approversList = $approversList->where("{$teamTbl}.code", TeamConst::CODE_BOD)
                ->whereIn("{$teamMemberTbl}.role_id", $roleIds)
                ->orderBy("{$teamMemberTbl}.role_id")
                ->get();
            return $approversList;
        }
        $teamsByLeader = Team::select('id')->where('leader_id', $projsLeader)->get()->toArray();
        $teamProjs = TeamProject::select('team_id')
            ->where('project_id', $projsId)
            ->whereIn('team_id', $teamsByLeader)
            ->get()->toArray();

        $isSubleader = TeamMember::where('employee_id', $employeeId)
            ->where('role_id', Team::ROLE_SUB_LEADER)
            ->whereIn('team_id', $teamProjs)
            ->count() ? true : false;
        if ($isSubleader) {
            $roleIds = [Team::ROLE_TEAM_LEADER];
        }
        $approversList = $approversList->whereIn("{$teamMemberTbl}.role_id", $roleIds)
            ->whereIn("{$teamMemberTbl}.team_id", $teamProjs)
            ->orderBy("{$teamMemberTbl}.role_id")
            ->get();

        return $approversList;
    }
    
    /**
     * get members of team 
     * @param int $teamId
     * @return collection
     */
    public static function getTeamEmployee($teamId)
    {
        $userId = Permission::getInstance()->getEmployee()->id;
        return DB::table('team_members')
            ->leftJoin('employees', 'employees.id', '=', 'team_members.employee_id')
            ->select('team_members.employee_id', 'employees.name', 'employees.employee_code', 'employees.email')
            ->whereNull('team_members.deleted_at')
            ->where('team_members.employee_id', '<>', $userId)
            ->where('team_members.team_id', '=', $teamId)
            ->get();
    }
    
    /**
     * get searching employee info
     * @param type $value
     * @param type $array
     * @return type
     */
    public static function getDataSearch($value = '', $regId = '') 
    {
        $regId = (int) $regId;
        return DB::table('employees')->select('id', 'name', 'nickname', 'employee_code')
            ->where('id', '!=', $regId)
            ->where(function($query) use ($value) {
                $query->where('name', 'like', '%'.$value.'%');
                $query->orWhere('nickname', 'like', '%'.$value.'%');
            })
            ->whereNull('leave_date')
            ->limit(10)
            ->get();
    }
    
    /**
     * check if request time is registered (case: registered time range contains another registered time range)
     * @param int $regId registration id
     * @param int $empId employee id
     * @param string $start registered start time
     * @param string $end registered end time
     * @return boolean
     */
    public static function containOccupiedTimeSlot($regId, $empId, $start, $end) {
        if ($regId && OtRegister::find($regId)) {
            $isFree = $isFree->where("ot_register_id", "!=", $regId);
        }
        $isFreeContains = DB::table('ot_employees')->where('start_at', '>=', $start)
                                                   ->where('end_at', '<=', $end)
                                                   ->where("employee_id", "=", $empId)
                                                   ->count('ot_register_id');
        
        $isFreeIntersect = DB::table('ot_employees')->where("employee_id", "=", $empId)
                                                    ->where(function($query) use ($start, $end){
                                                        $query->where('end_at', '<=', $end);
                                                        $query->where('start_at', '>=', $start);
                                                    })
                                                    ->count('ot_register_id');

        return ($isFreeContains == 0 && $isFreeIntersect == 0);
    }
    
    /**
     * Check time OT is exist?
     * 
     * @param int $employeeId
     * @param datetime $startDate
     * @param datetime $endDate
     * @param int $registerId
     * @return type
     */
    public static function checkRegisterExist($employeeId, $startDate, $endDate, $registerId = null)
    {
        $registEmpTable = self::getTableName();
        $registTable = OtRegister::getTableName();
        $result = self::where("{$registEmpTable}.employee_id", $employeeId)
            ->where(function ($query) use ($startDate, $endDate, $registEmpTable) {
                $query->where(function ($query1) use ($startDate, $registEmpTable) {
                        $query1->where("{$registEmpTable}.start_at", '<=', $startDate)
                            ->where("{$registEmpTable}.end_at", '>=', $startDate);
                        })
                        ->orWhere(function ($query2) use ($endDate, $registEmpTable) {
                            $query2->where("{$registEmpTable}.start_at", '<=', $endDate)
                                ->where("{$registEmpTable}.end_at", '>=', $endDate);
                        })
                        ->orWhere(function ($query3) use ($startDate, $endDate, $registEmpTable) {
                            $query3->where("{$registEmpTable}.start_at", '>=', $startDate)
                                ->where("{$registEmpTable}.end_at", '<=', $endDate);
                });
            })
            ->join("{$registTable}", "{$registTable}.id", "=", "{$registEmpTable}.ot_register_id")
            ->where("{$registTable}.status", "<>", OtRegister::REMOVE);
        if ($registerId) {
            $result = $result->where('ot_register_id', '!=', $registerId);
        }

        return $result->count() ? true : false;
    }

    /*
     * Get register by employeees exist
     */
    public static function getRegisterExist($employeeId, $startDate, $endDate, $registerId = null)
    {
        $registEmpTable = self::getTableName();
        $registTable = OtRegister::getTableName();
        $tblEmployee = Employee::getTableName();
        $result = self::select("{$tblEmployee}.employee_code", "{$tblEmployee}.name as employee_name")
            ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$registEmpTable}.employee_id")
            ->join("{$registTable}", "{$registTable}.id", "=", "{$registEmpTable}.ot_register_id")
            ->where("{$registEmpTable}.employee_id", $employeeId)
            ->whereIn("{$registTable}.status", [OtRegister::WAIT, OtRegister::DONE])
            ->where(function ($query) use ($startDate, $endDate, $registEmpTable) {
                $query->where(function ($query1) use ($startDate, $registEmpTable) {
                        $query1->where("{$registEmpTable}.start_at", '<=', $startDate)
                            ->where("{$registEmpTable}.end_at", '>=', $startDate);
                        })
                        ->orWhere(function ($query2) use ($endDate, $registEmpTable) {
                            $query2->where("{$registEmpTable}.start_at", '<=', $endDate)
                                ->where("{$registEmpTable}.end_at", '>=', $endDate);
                        })
                        ->orWhere(function ($query3) use ($startDate, $endDate, $registEmpTable) {
                            $query3->where("{$registEmpTable}.start_at", '>=', $startDate)
                                ->where("{$registEmpTable}.end_at", '<=', $endDate);
                });
            });
        if ($registerId) {
            $result = $result->where('ot_register_id', '!=', $registerId);
        }

        return $result->first();
    }
    /**
     * Check Disallow OT is exist?
     * @param int $employeeId
     */
    public static function getDisableOtExist($employeeId)
    {
        $result = AdminOtDisallow::where('employee_id', 'like', '{' . $employeeId . ',%')
        ->orWhere('employee_id', 'like', '%,' . $employeeId . ',%')
        ->orWhere('employee_id', 'like', '%,'  . $employeeId . '}')
        ->orWhere('employee_id', 'like', '%{'  . $employeeId . '}%')
        ->limit(1);
        
        return $result->first();
    }
    
    /**
     * Search employee ajax
     * @param [string] $keySearch
     * @param [array] $config
     * @return [array]
     */
    public static function searchEmployeeAjax($keySearch, array $config = [])
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
            ->distinct("{$tblEmployee}.id")
            ->where(function ($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            });
        Employee::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'employee_code' => $item->employee_code,
                'employee_name' => $item->name,
                'text' => $item->name . ' (' . strtolower(preg_replace('/@.*/', '', $item->email)) . ')',
                'avatar_url' => $item->avatar_url,
            ];
        }
        return $result;
    }
}
