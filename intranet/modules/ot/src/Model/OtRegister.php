<?php

namespace Rikkei\Ot\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Illuminate\Support\Facades\Lang;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\Project;
use Rikkei\Core\Model\User;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Permission as PermissionModel;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Team\View\TeamConst;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Ot\View\OtPermission;
use Rikkei\ManageTime\Model\ManageTimeComment;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Ot\Model\OtEmployee;

class OtRegister extends CoreModel 
{
    use SoftDeletes;
    
    protected $table = 'ot_registers';
    
    protected $dates = ['deleted_at'];
    
    protected $fillable = ['status', 'approver'];

    protected static $instance;

    const REMOVE = 1;
    const REJECT = 2;
    const WAIT = 3;
    const DONE = 4;
    
    const REGISTER = 1;
    const APPROVER = 2;
    const RELATETO = 3;

    const IS_ONSITE = 1;
    const IS_NOT_ONSITE = 0;

    const IS_PAID = 1;
    const KEY_NOTPROJECT_OT = 'notProjectOT';
    /**
     * get ot employees of register
     * @return ot employees
     */
    public function ot_employees()
    {
        return $this->hasMany('Rikkei\Ot\Model\OtEmployee')->withTrashed();
    }
    
    /**
     * get ot employees of register
     * @return ot employees
     */
    public function employeeOTs()
    {
        return $this->hasMany('Rikkei\Ot\Model\OtEmployee')
            ->select('ot_employees.ot_register_id',
                'ot_employees.employee_id',
                'ot_employees.start_at',
                'ot_employees.end_at',
                'ot_employees.is_paid',
                'ot_employees.time_break');
    }

    /*
     * Get approver information
     */
    public function getApproverInformation()
    {
        $tblRole = Role::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblEmployee = Employee::getTableName();
        return Employee::select("{$tblEmployee}.id as approver_id", "{$tblEmployee}.name as approver_name", "{$tblEmployee}.email as approver_email", DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$tblRole}.role, ' - ', {$tblTeam}.name) ORDER BY {$tblRole}.role DESC SEPARATOR '; ') as approver_position"))
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", "=", "{$tblEmployee}.id")
            ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
            ->join("{$tblRole}", "{$tblRole}.id", "=", "{$tblTeamMember}.role_id")
            ->where("{$tblRole}.special_flg", DB::raw(Role::FLAG_POSITION))
            ->where("{$tblEmployee}.id", $this->approver)
            ->first();
    }

    /*
     * Get creator information
     */
    public function getCreatorInformation()
    {
        $tblRole = Role::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblEmployee = Employee::getTableName();
        return Employee::select("{$tblEmployee}.id as creator_id", "{$tblEmployee}.name as creator_name", "{$tblEmployee}.email as creator_email", DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$tblRole}.role, ' - ', {$tblTeam}.name) ORDER BY {$tblRole}.role DESC SEPARATOR '; ') as creator_position"))
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", "=", "{$tblEmployee}.id")
            ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
            ->join("{$tblRole}", "{$tblRole}.id", "=", "{$tblTeamMember}.role_id")
            ->where("{$tblRole}.special_flg", DB::raw(Role::FLAG_POSITION))
            ->where("{$tblEmployee}.id", $this->employee_id)
            ->first();
    }
    
    /**
     * get role and team of employee
     * @param int $empId employee's id
     * @return array $empRole role and team
     */
    public static function getRoleandTeam($empId) 
    {
        return DB::table('team_members')
               ->leftJoin('roles', 'roles.id', '=', 'team_members.role_id')
               ->leftJoin('teams', 'teams.id', '=', 'team_members.team_id')
               ->where('team_members.employee_id', $empId)
               ->select('roles.role', 'teams.name')
               ->get();
    }
    
    /**
     * get project employee is working on
     * @param int $emp_id employee's id
     * @param boolean $leaderOnly check if only need leaders
     * @return array $projectlist
     */
    public static function getProjectsbyEmployee($emp_id, $groupBy = true) 
    {        
        $collection = DB::table('project_members as member')
            ->select(
                'member.project_id',
                'projs.name as projName',
                'member.start_at',
                'member.end_at'
            )
            ->leftJoin('projs', 'projs.id', '=', 'member.project_id')
            ->where('projs.state', '=', Project::STATE_PROCESSING)
            ->whereNull('member.deleted_at')
            ->whereNull('projs.deleted_at')
            ->where(function($query) use ($emp_id){
                $query->where('member.status', '=', ProjectWOBase::STATUS_APPROVED);
                $query->where('member.employee_id', '=', $emp_id);
                $query->orWhere('projs.leader_id', '=', $emp_id);      
            })
            ->orderBy('projs.name');
            if ($groupBy) {
                $collection = $collection->groupBy('member.project_id');
            }
            return $collection->distinct()->get();
    }
    
    /**
     * get ot register information
     * @param int $regId
     * @return collection
     */
    public static function getRegisterInfo($registerId)
    {
        $registerTable = self::getTableName();
        $registerTableAs = $registerTable;
        $projectTable = Project::getTableName();
        $registerTeamTable = OtTeam::getTableName();
        $registerTeamTableAs = 'ot_team_table';
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_created_by';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';

        $registerRecord = self::select(
            "{$registerTableAs}.*",
            "{$projectTable}.name as projs_name",
            "{$employeeCreateTableAs}.id as creator_id",
            "{$employeeCreateTableAs}.employee_code as employee_code",
            "{$employeeCreateTableAs}.name as employee_name",
            "{$employeeCreateTableAs}.email as creator_email",
            "{$employeeApproveTableAs}.id as approver_id",
            "{$employeeApproveTableAs}.name as approver_name",
            "{$employeeApproveTableAs}.email as approver_email",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$roleTableAs}.role, ' - ', {$teamTableAs}.name) ORDER BY {$roleTableAs}.role DESC SEPARATOR '; ') as role_name")
        );

        $registerRecord = $registerRecord->leftjoin("{$projectTable} as {$projectTable}", "{$projectTable}.id", "=", "{$registerTableAs}.projs_id")
            ->join("{$employeeTable} as {$employeeCreateTableAs}", "{$employeeCreateTableAs}.id", "=", "{$registerTableAs}.employee_id")
            ->join("{$employeeTable} as {$employeeApproveTableAs}", "{$employeeApproveTableAs}.id", "=", "{$registerTableAs}.approver")
            ->join("{$registerTeamTable} as {$registerTeamTableAs}", "{$registerTeamTableAs}.register_id", "=", "{$registerTableAs}.id")
            ->join("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", "=", "{$registerTeamTableAs}.team_id")
            ->join("{$roleTable} as {$roleTableAs}", "{$roleTableAs}.id", "=", "{$registerTeamTableAs}.role_id")
            ->where("{$roleTableAs}.special_flg", DB::raw(Role::FLAG_POSITION))
            ->where("{$registerTableAs}.id", $registerId)
            ->withTrashed()
            ->groupBy("{$registerTableAs}.id")
            ->first();

        return $registerRecord;
    }

    /**
     * get list of register with status
     * @param int $status
     * @return $collection
     */
    public static function getRegisterList($empId, $empType, $status, $dataFilter) 
    {
        $registerTable = self::getTableName();
        $registerTableAs = $registerTable;
        $tblOtEmployee = OtEmployee::getTableName();
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_creator';
        $employeeApproveTableAs = 'employee_table_for_approver';

        $collection =  DB::table("{$registerTable}")
                        ->select("{$registerTableAs}.id",
                            "{$registerTableAs}.employee_id as creator_id",
                            "{$registerTableAs}.start_at",
                            "{$registerTableAs}.end_at",
                            "{$registerTableAs}.time_break",
                            "{$registerTableAs}.reason",
                            "{$registerTableAs}.status",
                            "{$tblOtEmployee}.employee_id as emp_employee_id",
                            "{$tblOtEmployee}.start_at as emp_start_at",
                            "{$tblOtEmployee}.end_at as emp_end_at",
                            "{$tblOtEmployee}.time_break as emp_time_break",
                            "{$employeeCreateTableAs}.name as creator_name",
                            "{$employeeApproveTableAs}.name as approver_name",
                            "{$registerTableAs}.approved_at")
                        ->leftJoin("{$tblOtEmployee}", "{$tblOtEmployee}.ot_register_id", '=', "{$registerTableAs}.id")
                        ->leftJoin("{$employeeTable} as {$employeeCreateTableAs}", "{$employeeCreateTableAs}.id", '=', "{$registerTableAs}.employee_id")
                        ->leftJoin("{$employeeTable} as {$employeeApproveTableAs}", "{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver")
                        ->whereNull('ot_registers.deleted_at');
        if ($status) {
            $collection = $collection->where('ot_registers.status', $status);
        }
        if ($empType == self::REGISTER) {
            $collection = $collection->where(function ($query) use ($registerTableAs, $tblOtEmployee, $empId) {
                $query->where("{$registerTableAs}.employee_id", $empId)
                    ->orWhere("{$tblOtEmployee}.employee_id", $empId);
            });

        } else if($empType == self::RELATETO) {
            $roleIds = [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER];
            $listTeamId = TeamMember::getLeaderOrSubleader($empId, $roleIds)->toArray();
            $listEmployeesId = TeamMember::getAllMemberOfTeam(array_column($listTeamId, 'team_id'));
            $collection = $collection->whereIn("{$tblOtEmployee}.employee_id", $listEmployeesId)
                ->where("{$registerTableAs}.employee_id", '!=', $empId)
                ->where("{$registerTableAs}.approver", '!=', $empId)->where("{$tblOtEmployee}.deleted_at", null);
        } else {
            $collection = $collection->where("{$registerTableAs}.approver", $empId);
        }
        if (isset($dataFilter['start_at'])) {
            $collection->whereDate("{$registerTableAs}.start_at", "=", Carbon::parse($dataFilter['start_at'])->toDateString());            
        }
        if (isset($dataFilter['end_at'])) {
            $collection->whereDate("{$registerTableAs}.end_at", "=", Carbon::parse($dataFilter['end_at'])->toDateString());            
        }
        if (isset($dataFilter['approved_at'])) {
            $collection->whereDate("{$registerTableAs}.approved_at", "=", Carbon::parse($dataFilter['approved_at'])->toDateString());            
        }
        if (isset($dataFilter["{$registerTableAs}.time_break"])) {
            $collection->where("{$registerTableAs}.time_break", $dataFilter["{$registerTableAs}.time_break"]);            
        }
        $pager = Config::getPagerData(null, ['order' => "ot_registers.id", 'dir' => 'DESC']);
        $collection = $collection->groupBy("{$registerTableAs}.id")->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }
    
    
    /**
     * format date filter data
     * @param type $data
     * @return string
     */
    public static function formatDateData($data)
    {        
        $day = null;
        $month = null;
        $year = null;
        $hour = null;
        $dateFilter = explode("-", $data);
        if (array_key_exists(2, $dateFilter)) {
            $dateFilter2 = explode(" ", $dateFilter[2]);
            $year = $dateFilter2[0];
            if (array_key_exists(1, $dateFilter2)) {
                $hour = $dateFilter2[1];
            }
        }
        if (array_key_exists(1, $dateFilter)) {
            $month = $dateFilter[1];
        }
        $day = $dateFilter[0];

        $dateFilter = '';
        if ($year) {
            $dateFilter = $year . '-'; 
        }
        if ($month) {
            $dateFilter = $dateFilter . $month . '-'; 
        }
        if ($day) {
            $dateFilter = $dateFilter . $day;
        }
        if ($hour) {
            $dateFilter = $dateFilter . ' ' . $hour; 
        }
        
        return $dateFilter;        
    }
    
    /**
     * get list of OT registers for admin
     * @param int $userId id of current user
     * @param int $teamId id of team that query registers
     * @return collection $collection
     */
    public static function getListManageRegisters ($userId, $teamId, $dataFilter) 
    {
        $tblEmp = Employee::getTableName();
        $tblOtReg = self::getTableName();
        $tblProjs = Project::getTableName();
        $commentTbl = ManageTimeComment::getTableName();
        $tblTeamRegOT = OtTeam::getTableName();
        $tblRole = Role::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblOtEmp = OtEmployee::getTableName();

        $collection = OtEmployee::select(
            "{$tblOtEmp}.ot_register_id as idRegister",
            "emp.id as empId",
            "emp.employee_code",
            "emp.name as empName",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(role_emp.role, '-', team_emp.name)) as teamp_emp"),
            "{$tblOtEmp}.start_at",
            "{$tblOtEmp}.end_at",
            "{$tblOtEmp}.time_break",
            "ot_reg.employee_id as empIdReg",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(role_reg.role, '-', team_reg.name)) as teamp_reg"),
            "ot_reg.status",
            "ot_reg.created_at",
            "ot_reg.approved_at",
            "ot_reg.reason",
            "emp_approve.name as emp_app_name",
            "project.name as nameProject"
        )
        ->leftJoin("{$tblEmp} as emp", "emp.id", '=', "{$tblOtEmp}.employee_id")
        ->leftJoin("{$tblTeamMember} as teamMem", "teamMem.employee_id", '=', "{$tblOtEmp}.employee_id")
            ->join("{$tblTeam} as team_emp", "team_emp.id", '=', "teamMem.team_id")
            ->join("{$tblRole} as role_emp", "role_emp.id", '=', "teamMem.role_id")
        ->join("{$tblOtReg} as ot_reg", "ot_reg.id", '=', "{$tblOtEmp}.ot_register_id")
        ->join("{$tblTeamRegOT} as team_ot", "team_ot.register_id", '=', "ot_reg.id")
            ->join("{$tblTeam} as team_reg", "team_reg.id", '=', "team_ot.team_id")
            ->join("{$tblRole} as role_reg", "role_reg.id", '=', "team_ot.role_id")
        ->leftJoin("{$tblProjs} as project", "project.id", '=', "ot_reg.projs_id")
        ->leftJoin("{$tblEmp} as emp_approve", "emp_approve.id", '=', "ot_reg.approver");

        //join team
        if ($teamId) {
            $teamIds = [];
            $teamIds[] = (int) $teamId;
            ManageTimeCommon::getTeamChildRecursive($teamIds, $teamId, true);

            $collection->whereIn('team_emp.id', $teamIds);
        }

        if (isset($dataFilter["ot_reg.created_at"])) {
            $collection->whereDate("ot_reg.created_at", "=", Carbon::parse($dataFilter["ot_reg.created_at"])->toDateString());
        }
        if (isset($dataFilter["ot_reg.approved_at"])) {
            $collection->whereDate("ot_reg.approved_at", "=", Carbon::parse($dataFilter["ot_reg.approved_at"])->toDateString());
        }
        if (isset($dataFilter["ot_employees.start_at"]) && isset($dataFilter["ot_employees.end_at"])) {
            $startDateFilter = Carbon::parse($dataFilter["ot_employees.start_at"])->toDateString();
            $endDateFilter = Carbon::parse($dataFilter["ot_employees.end_at"])->toDateString();
            $collection->where(function ($query) use ($startDateFilter, $endDateFilter) {
                $query->whereDate("ot_employees.start_at", "<=", $endDateFilter)
                    ->whereDate("ot_employees.end_at", ">=", $startDateFilter);
            });
        } else {
            if (isset($dataFilter["ot_employees.start_at"])) {
                $startDateFilter = Carbon::parse($dataFilter["ot_employees.start_at"])->toDateString();
                $collection->where(function ($query) use ($startDateFilter) {
                    $query->whereDate("ot_employees.start_at", ">=", $startDateFilter)
                        ->orWhereDate("ot_employees.end_at", ">=", $startDateFilter);
                });
            }
            if (isset($dataFilter["ot_employees.end_at"])) {
                $endDateFilter = Carbon::parse($dataFilter["ot_employees.end_at"])->toDateString();
                $collection->where(function ($query) use ($endDateFilter) {
                    $query->whereDate("ot_employees.start_at", "<=", $endDateFilter)
                        ->orWhereDate("ot_employees.end_at", "<=", $endDateFilter);
                });
            }
        }

        if (isset($dataFilter["ot_employees.time_break"])) {
            $collection->where("ot_employees.time_break", $dataFilter["ot_employees.time_break"]);
        }
        if (isset($dataFilter["status"])) {
            $collection->where("ot_reg.status", '=', $dataFilter["status"]);
        }
        $collection->groupBy("{$tblOtEmp}.ot_register_id")
            ->groupBy("{$tblOtEmp}.employee_id")
            ->orderBy(
                DB::raw("CASE WHEN ot_reg.status = ". static::WAIT
                . " THEN ot_reg.status + 3 ELSE ot_reg.status
                END "),
                'DESC'
            );

        //paginate
        $pager = Config::getPagerData(null, ['order' => "ot_reg.id", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])
            ->orderBy(DB::raw("CASE WHEN ot_reg.employee_id = {$tblOtEmp}.employee_id THEN 1 ELSE 0 END"), "DESC");
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }
    
    /**
     * get list of ot status
     * @return array list of ot project
     */
    public static function getStatusList($empType) 
    {
        if ($empType == self::REGISTER) {
            return [
                3 => Lang::get('ot::view.Unapproved Label'),
                4 => Lang::get('ot::view.Approved Label'),
                2 => Lang::get('ot::view.Rejected Label'),
                1 => Lang::get('ot::view.Remove Label'),
            ];
        }
        else {
            return [
                3 => Lang::get('ot::view.Unapproved Label'),
                4 => Lang::get('ot::view.Approved Label'),
                2 => Lang::get('ot::view.Rejected Label'),
            ];
        }
    }
    
    /**
     * get label of ot status
     * @return String ot status
     */
    public static function getStatusLabel($empType, $status) 
    {
        $statusList = self::getStatusList($empType);
        return $statusList[$status];
    }
    
    /**
     * get key of ot Status
     * @param int $empType
     * @param int $status
     * @return int
     */
    public static function getStatusKey($empType, $status) 
    {
        $statusList = self::getStatusList($empType);
        return array_search($status, $statusList);
    }
    
    /**
     * get total number of ot registry by type
     * @param int $empId employee id
     * @param int $typeOt type of OT Note
     * @return array ot registries and their sum
     */
    public static function countTotalRegister($empId, $typeOt) 
    {
        $tblRegister = self::getTableName();
        $tblOtEmployee = OtEmployee::getTableName();
        //init array
        $totalRegisters = ['0' => 0, '1' => 0, '2' => 0, '3' => 0, '4' => 0];
        //get total reg of each type
        $totalCount = self::groupBy("{$tblRegister}.status")
            ->leftJoin("{$tblOtEmployee}", "{$tblOtEmployee}.ot_register_id", '=', "{$tblRegister}.id")
            ->selectRaw("{$tblRegister}.status, count(DISTINCT {$tblRegister}.id) as total")
            ->whereNull("{$tblRegister}.deleted_at");
        if ($typeOt == self::APPROVER) {
            $totalCount = $totalCount->where("{$tblRegister}.approver", $empId)->withTrashed();
        } elseif ($typeOt == self::RELATETO) {
            $roleIds = [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER];
            $listTeamId = TeamMember::getLeaderOrSubleader($empId,$roleIds)->toArray();
            $listEmployeesId = TeamMember::getAllMemberOfTeam(array_column($listTeamId, 'team_id'));
            $totalCount = $totalCount->whereIn("{$tblOtEmployee}.employee_id", $listEmployeesId)->where("{$tblRegister}.approver",'!=', $empId)->where("{$tblOtEmployee}.deleted_at",null)->where("{$tblRegister}.employee_id", '!=', $empId);
        } else {
            // $totalCount = $totalCount->where('employee_id', $empId)->withTrashed();
            $totalCount = $totalCount->where(function ($query) use ($tblRegister, $tblOtEmployee, $empId) {
                $query->where("{$tblRegister}.employee_id", $empId)
                    ->orWhere("{$tblOtEmployee}.employee_id", $empId);
            })->withTrashed();
        }
        $totalCount = $totalCount->get()->toArray();
        $totalAll = 0;
        //push result into array
        foreach ($totalCount as $key => $value) {
            $totalRegisters[$value['status']] = $value['total'];
            $totalAll = $totalAll + $value['total'];
        }
        $totalRegisters['0'] = $totalAll;
      
        return $totalRegisters;
    }
    
    /**
     * get info list of approvers
     * @return collection
     */
    public static function getApproverList($empId = null, $listType = null, $teamId = null) 
    {
        $empTbl = Employee::getTableName();
        $otregTbl = self::getTableName();
        $collection =  DB::table("{$otregTbl}")
                       ->leftJoin("{$empTbl}", "{$empTbl}.id", "=", "{$otregTbl}.approver")
                       ->select("{$empTbl}.id", "{$empTbl}.name");
        //filter employee               
        if ($empId) {
            $collection = $collection->where("{$otregTbl}.employee_id", $empId)
                                     ->where("{$otregTbl}.status", $listType);
        }
        //filter team 
        if ($teamId) {
            $team = Team::find($teamId);
            $teamList= OtPermission::getKeyArray(OtPermission::getTeamDescendants($team->id));
            $teamList[] = $team->id;

            $empOfTeam = TeamMember::whereIn("team_id", $teamList)->lists("employee_id")->toArray();

            $collection = $collection->whereIn("{$otregTbl}.employee_id", $empOfTeam);
        }
        $collection = $collection->distinct()->get();
        return $collection;
    }

    public static function getApproverForNotSoftDev($employeeId)
    {
        if (!$employeeId) {
            return null;
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return null;
        }

        $teamMemberTbl = TeamMember::getTableName();
        $teamTbl = Team::getTableName();
        $employeeTbl = Employee::getTableName();
        $roleTbl = Role::getTableName();
        $collection = null;
        $roleIds = [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER];

        $teamIdsIsNotDev = Team::join("{$teamMemberTbl}", "{$teamMemberTbl}.team_id", '=', "{$teamTbl}.id")
            ->join("{$employeeTbl}", "{$employeeTbl}.id", '=', "{$teamMemberTbl}.employee_id")
            ->where("{$teamMemberTbl}.employee_id", $employeeId)
            ->where(function($query) use($teamTbl) {
            $query->where(function($query1) use($teamTbl) {
                    $query1->where("{$teamTbl}.is_soft_dev", "!=", Team::IS_SOFT_DEVELOPMENT);
                })
                ->orWhere(function($query2) use($teamTbl) {
                    $query2->whereNull("{$teamTbl}.is_soft_dev");
                });
            })
            ->lists("{$teamTbl}.id")
            ->toArray();

        if (!count($teamIdsIsNotDev)) {
            return null;
        }
        $isLeader = TeamMember::where('employee_id', $employeeId)
            ->where('role_id', Team::ROLE_TEAM_LEADER)
            ->whereIn('team_id', $teamIdsIsNotDev)
            ->count() ? true : false;
        if ($isLeader) {
            $isLeaderBOD = TeamMember::join("{$teamTbl}", "{$teamTbl}.id", "=", "{$teamMemberTbl}.team_id")
                ->where("{$teamTbl}.code", TeamConst::CODE_BOD)
                ->whereIn("{$teamTbl}.id", $teamIdsIsNotDev)
                ->count() ? true : false;
            if ($isLeaderBOD) {
                $roleIds = [Team::ROLE_SUB_LEADER];
            }
            $collection = TeamMember::select("{$employeeTbl}.id as emp_id",
                "{$employeeTbl}.name as emp_name",
                "{$employeeTbl}.email as emp_email",
                "{$teamMemberTbl}.role_id as role_id",
                "{$roleTbl}.role",
                "{$teamTbl}.name as team_name"
            )->join("{$employeeTbl}", "{$employeeTbl}.id", "=", "{$teamMemberTbl}.employee_id")
                ->join("{$teamTbl}", "{$teamTbl}.id", "=", "{$teamMemberTbl}.team_id")
                ->join("{$roleTbl}", "{$roleTbl}.id", "=", "{$teamMemberTbl}.role_id")
                ->where("{$teamTbl}.code", TeamConst::CODE_BOD)
                ->whereIn("{$teamMemberTbl}.role_id", $roleIds)
                ->whereNull("{$employeeTbl}.leave_date")
                ->orderBy("{$teamMemberTbl}.role_id")
                ->get();
        } else {
            $isSubLeader = TeamMember::where('employee_id', $employeeId)
                ->where('role_id', Team::ROLE_SUB_LEADER)
                ->whereIn('team_id', $teamIdsIsNotDev)
                ->count() ? true : false;
            if ($isSubLeader) {
                $roleIds = [Team::ROLE_TEAM_LEADER];
            }

            $collection = TeamMember::select("{$employeeTbl}.id as emp_id",
                "{$employeeTbl}.name as emp_name",
                "{$employeeTbl}.email as emp_email",
                "{$teamMemberTbl}.role_id as role_id",
                "{$roleTbl}.role",
                "{$teamTbl}.name as team_name"
            )->join("{$employeeTbl}", "{$employeeTbl}.id", "=", "{$teamMemberTbl}.employee_id")
                ->join("{$teamTbl}", "{$teamTbl}.id", "=", "{$teamMemberTbl}.team_id")
                ->join("{$roleTbl}", "{$roleTbl}.id", "=", "{$teamMemberTbl}.role_id")
                ->whereIn("{$teamMemberTbl}.team_id", $teamIdsIsNotDev)
                ->whereIn("{$teamMemberTbl}.role_id", $roleIds)
                ->whereNull("{$employeeTbl}.leave_date")
                ->orderBy("{$teamMemberTbl}.role_id")
                ->get();
        }

        return $collection;
    }

    /*
     * Search employees can approve
     */
    public static function searchEmployeesCanApprove($keySearch, $employeeId = null, $projectId = null, $route = 'manage_time::manage-time.manage.ot.approve')
    {
        if (!$employeeId) {
            return null;
        }
        $employeesCanApprove = [];
        $checkDuplicate = [];
        $tblAction = Action::getTableName();
        $tblPermission  = PermissionModel::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblTeamMemberAs = 'tbl_team_members';
        $tblEmployee = Employee::getTableName();
        $tblEmployeeRole = EmployeeRole::getTableName();
        $tblUser = User::getTableName();
        $tblRole = Role::getTableName();
        $roleIds = [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER];
        $now = Carbon::now();
        if ($projectId) {
            $project = Project::find($projectId);
            if (!$project) {
                return null;
            }
            $projectLeader = $project->leader_id;
            $employeesCanApproveByProject = TeamMember::select("{$tblTeamMember}.employee_id", "{$tblEmployee}.name as employee_name", "{$tblEmployee}.email as employee_email", "{$tblUser}.avatar_url")
                ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblTeamMember}.employee_id")
                ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
                ->join("{$tblRole}", "{$tblRole}.id", "=", "{$tblTeamMember}.role_id")
                ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
                ->where(function ($query) use ($tblEmployee, $keySearch) {
                    $query->orWhere("{$tblEmployee}.email", 'LIKE', '%' . $keySearch . '%')
                        ->orWhere("{$tblEmployee}.name", 'LIKE', '%' . $keySearch . '%');
                })
                ->where(function ($query) use ($tblEmployee, $now) {
                    $query->orWhereNull("{$tblEmployee}.leave_date")
                        ->orWhereDate("{$tblEmployee}.leave_date", '>=', $now->format('Y-m-d'));
                });

            if ($projectLeader == $employeeId) {
                $isLeaderBOD = TeamMember::join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
                    ->where("{$tblTeam}.code", TeamConst::CODE_BOD)
                    ->where("{$tblTeamMember}.employee_id", $employeeId)
                    ->count();
                if ($isLeaderBOD) {
                    $roleIds = [Team::ROLE_SUB_LEADER];
                }
                $employeesCanApproveByProject = $employeesCanApproveByProject->where("{$tblTeam}.code", TeamConst::CODE_BOD)
                    ->whereIn("{$tblTeamMember}.role_id", $roleIds)
                    ->orderBy("{$tblTeamMember}.role_id")
                    ->get();
            } else {
                $teamsByLeader = Team::select('id')->where('leader_id', $projectLeader)->get()->toArray();
                $teamProjects = TeamProject::select('team_id')
                    ->where('project_id', $projectId)
                    ->whereIn('team_id', $teamsByLeader)
                    ->get()->toArray();

                $isSubleader = TeamMember::where('employee_id', $employeeId)
                    ->where('role_id', Team::ROLE_SUB_LEADER)
                    ->whereIn('team_id', $teamProjects)
                    ->count();
                if ($isSubleader) {
                    $roleIds = [Team::ROLE_TEAM_LEADER];
                }
                $employeesCanApproveByProject = $employeesCanApproveByProject->whereIn("{$tblTeamMember}.role_id", $roleIds)
                    ->whereIn("{$tblTeamMember}.team_id", $teamProjects)
                    ->orderBy("{$tblTeamMember}.role_id")
                    ->get();
            }
            if (count($employeesCanApproveByProject)) {
                foreach ($employeesCanApproveByProject as $emp) {
                    if (isset($checkDuplicate[$emp->employee_id])) {
                        continue;
                    }
                    $employeesCanApprove['items'][] = [
                        'id' => $emp->employee_id,
                        'text' => $emp->employee_name . ' (' . strtolower(preg_replace('/@.*/', '', $emp->employee_email)) . ')',
                        'avatar_url' => $emp->avatar_url,
                    ];
                    $checkDuplicate[$emp->employee_id] = true;
                }
            }
            if (!empty(Config('services.account_root'))) {
                $rootAcc = Employee::getEmpByEmail(Config('services.account_root'));
                if (!count($employeesCanApprove) || (count($employeesCanApprove) && !static::hasRootInApprovers($employeesCanApprove['items'], $rootAcc))) {
                    $employeesCanApprove['items'][] = [
                        'id' => $rootAcc->id,
                        'text' => $rootAcc->name . ' (' . self::getNickName($rootAcc->email) . ')',
                        'avatar_url' => User::where('employee_id', $rootAcc->id)->first()->avatar_url,
                    ];
               }
            }
            return $employeesCanApprove;
        } else {
            $employeesCanApprove = ManageTimeCommon::searchEmployeesCanApprove($keySearch, $employeeId, $route);
        }

        return $employeesCanApprove;
    }
    
    public static function getRejectReasons($regId)
    {
        $commentTbl = ManageTimeComment::getTableName();
        $empTbl= Employee::getTableName();
        $userTbl = 'users';
        
        return ManageTimeComment::select("{$commentTbl}.comment as comment", "{$commentTbl}.type as type", "{$commentTbl}.created_at as created_at", "{$empTbl}.name as name", "{$userTbl}.avatar_url as avatar_url")
           ->join("{$empTbl}", "{$empTbl}.id", '=', "{$commentTbl}.created_by")
           ->leftJoin("{$userTbl}", "{$userTbl}.employee_id", '=', "{$commentTbl}.created_by")
           ->where("{$commentTbl}.register_id", $regId)
           ->where("{$commentTbl}.type", ManageTimeConst::TYPE_OT)
           ->get();
    }

    public static function getRegisterOfTimeKeeping($monthOfTimeKeeping, $empsIdOfTimeKeeping, $timekeepingTableStartDate, $timekeepingTableEndDate)
    {
        $otRegisterTable = self::getTableName();
        $otEmployeeTable = OtEmployee::getTableName();
        return DB::table("{$otEmployeeTable} AS tblOTEmployee")
            ->select([
                'tblOTRegister.id',
                'tblOTRegister.is_onsite',
                'tblOTEmployee.employee_id',
                'tblOTEmployee.start_at',
                'tblOTEmployee.end_at',
                'tblOTEmployee.time_break',
                'tblOTEmployee.is_paid',
                'employee_works.contract_type',
                'employees.trial_date',
                'employees.leave_date',
                'employees.offcial_date'
            ])
            ->join("{$otRegisterTable} AS tblOTRegister", 'tblOTRegister.id', '=', 'tblOTEmployee.ot_register_id')
            ->leftJoin("employee_works", "tblOTEmployee.employee_id", "=", "employee_works.employee_id")
            ->join('employees', 'employees.id', '=', 'tblOTEmployee.employee_id')
            ->whereDate('tblOTEmployee.start_at', '<=', $timekeepingTableEndDate)
            ->whereDate('tblOTEmployee.end_at', '>=', $timekeepingTableStartDate)
            ->where('tblOTRegister.status', '=', OtRegister::DONE)
            ->whereNull('tblOTEmployee.deleted_at')
            ->whereNull('tblOTRegister.deleted_at')
            ->whereIn('tblOTEmployee.employee_id', $empsIdOfTimeKeeping)
            ->get();
    }

    /**
     * get register time ot  approver between start and end in today
     * @param  [date] $empsIdOfTimeKeeping
     * @param  [date] $date (Y-m-d)
     * @return [type]
     */
    public static function getRegisterOfTimeKeepingCron($empsIdOfTimeKeeping, $date)
    {
        $otRegisterTable = self::getTableName();
        $otEmployeeTable = OtEmployee::getTableName();
        return DB::table("{$otEmployeeTable} AS tblOTEmployee")
            ->select([
                'tblOTRegister.id',
                'tblOTRegister.is_onsite',
                'tblOTEmployee.employee_id',
                'tblOTEmployee.start_at',
                'tblOTEmployee.end_at',
                'tblOTEmployee.time_break',
                'tblOTEmployee.is_paid',
                'employee_works.contract_type',
                'employees.trial_date',
                'employees.leave_date',
                'employees.offcial_date',
                'tblOTRegister.updated_at'
            ])
            ->join("{$otRegisterTable} AS tblOTRegister", 'tblOTRegister.id', '=', 'tblOTEmployee.ot_register_id')
            ->leftJoin("employee_works", "tblOTEmployee.employee_id", "=", "employee_works.employee_id")
            ->join('employees', 'employees.id', '=', 'tblOTEmployee.employee_id')
            ->where('tblOTRegister.status', '=', OtRegister::DONE)
            ->whereNull('tblOTEmployee.deleted_at')
            ->whereNull('tblOTRegister.deleted_at')
            ->whereIn('tblOTEmployee.employee_id', $empsIdOfTimeKeeping)
            ->whereDate('tblOTRegister.updated_at', '=', $date)
            ->get();
    }

    /**
     * get list register time ot by ids
     * @param  [int] $registerIds
     * @return [collection]
     */
    public function getListRegisterById($registerIds)
    {
        $otReg = self::getTableName();
        $otEmployee = OtEmployee::getTableName();
        return self::select(
            "{$otReg}.id",
            "{$otReg}.is_onsite",
            'otEmp.employee_id',
            'otEmp.start_at',
            'otEmp.end_at',
            'otEmp.time_break',
            'otEmp.is_paid'
        )
        ->leftJoin("{$otEmployee} AS otEmp", "{$otReg}.id", '=', 'otEmp.ot_register_id')
        ->whereIn("{$otReg}.id", $registerIds)
        ->whereNull("{$otReg}.deleted_at")
        ->get();
    }

    /**
     * get list register time ot by Not ids
     * @param  [int] $registerIds
     * @return [collection]
     */
    public function getListRegisterByNotId($registerId, $empId, $timekeepingTableStartDate, $timekeepingTableEndDate)
    {
        $otRegisterTable = self::getTableName();
        $otEmployeeTable = OtEmployee::getTableName();

        return DB::table("{$otEmployeeTable} AS tblOTEmployee")
            ->select([
                'tblOTRegister.id',
                'tblOTRegister.is_onsite',
                'tblOTEmployee.employee_id',
                'tblOTEmployee.start_at',
                'tblOTEmployee.end_at',
                'tblOTEmployee.time_break',
                'tblOTEmployee.is_paid',
                'employee_works.contract_type',
                'employees.trial_date',
                'employees.leave_date',
                'employees.offcial_date',
                'tblOTRegister.updated_at'
            ])
            ->join("{$otRegisterTable} AS tblOTRegister", 'tblOTRegister.id', '=', 'tblOTEmployee.ot_register_id')
            ->leftJoin("employee_works", "tblOTEmployee.employee_id", "=", "employee_works.employee_id")
            ->join('employees', 'employees.id', '=', 'tblOTEmployee.employee_id')
            ->where("tblOTRegister.id", '<>', $registerId)
            ->whereDate('tblOTEmployee.start_at', '<=', $timekeepingTableEndDate)
            ->whereDate('tblOTEmployee.end_at', '>=', $timekeepingTableStartDate)
            ->where("tblOTRegister.status", '=', self::DONE)
            ->whereNull('tblOTEmployee.deleted_at')
            ->whereNull('tblOTRegister.deleted_at')
            ->where('tblOTEmployee.employee_id', $empId)
            ->get();
    }

    public function getNotProject()
    {
        return [
            self::KEY_NOTPROJECT_OT => Lang::get('ot::view.No project'),
        ];
    }

    public function getOtTimeOfProject($projectId)
    {;
        $OtRegTbl = self::getTableName();
        $OtEmpTbl = OtEmployee::getTableName();
        $result = self::join("{$OtEmpTbl}", "{$OtRegTbl}.id", "=", "{$OtEmpTbl}.ot_register_id")
                ->where("{$OtRegTbl}.projs_id", $projectId)
                ->whereNull("{$OtRegTbl}.deleted_at")
                ->where("{$OtRegTbl}.status", self::DONE)
                ->whereNull("{$OtEmpTbl}.deleted_at")
                ->groupBy("{$OtRegTbl}.projs_id")
                ->selectRaw("sum((TIMESTAMPDIFF(MINUTE, {$OtEmpTbl}.start_at, {$OtEmpTbl}.end_at) - ot_employees.time_break * 60)/60/8) AS ot_time")
                ->value('ot_time');
        return $result ? number_format((float)$result, 2) : 0;
    }

    /**
     * Singleton instance
     *
     * @return \Rikkei\Team\View\CheckpointPermission
     */
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    public static function getInformationRegister($registerId)
    {
        return self::find($registerId);
    }
}
