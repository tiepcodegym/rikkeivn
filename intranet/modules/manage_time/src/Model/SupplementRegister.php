<?php

namespace Rikkei\ManageTime\Model;

use DB;
use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lang;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\Model\SupplementReasons;
use Rikkei\Team\View\Permission;

class SupplementRegister extends CoreModel
{
    const STATUS_CANCEL = 1;
    const STATUS_DISAPPROVE = 2;
    const STATUS_UNAPPROVE = 3;
    const STATUS_APPROVED = 4;

    /**
     * Type OT
     */
    const IS_OT = 1;
    const IS_NOT_OT = 0;

    use SoftDeletes;

    protected $table = 'supplement_registers';

    /**
     * List label of supplement status
     *
     * @return array
     */
    public static function statusLabel()
    {
        return [
            STATUS_CANCEL => Lang::get('manage_time.view.Cancel'),
            STATUS_DISAPPROVE => Lang::get('manage_time.view.Disapprove'),
            STATUS_UNAPPROVE => Lang::get('manage_time.view.Unapprove'),
            STATUS_APPROVED => Lang::get('manage_time.view.Approved'),
        ];
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
        return Employee::select("{$tblEmployee}.name as approver_name", "{$tblEmployee}.email as approver_email", DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$tblRole}.role, ' - ', {$tblTeam}.name) ORDER BY {$tblRole}.role DESC SEPARATOR '; ') as approver_position"))
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", "=", "{$tblEmployee}.id")
            ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
            ->join("{$tblRole}", "{$tblRole}.id", "=", "{$tblTeamMember}.role_id")
            ->where("{$tblRole}.special_flg", DB::raw(Role::FLAG_POSITION))
            ->where("{$tblEmployee}.id", $this->approver_id)
            ->first();
    }

    /**
     * [getInformationRegister: get information of register]
     * @param  [int] $registerId
     * @return [type]
     */
    public static function getInformationRegister($registerId)
    {
        $registerTable = self::getTableName();
        $registerTableAs = $registerTable;
        $registerTeamTable = SupplementTeam::getTableName();
        $registerTeamTableAs = 'supplement_team_table';
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_created_by';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';

        $registerRecord = self::select(
            "{$registerTableAs}.id as id",
            "{$registerTableAs}.is_ot",
            "{$registerTableAs}.id as register_id",
            "{$registerTableAs}.status as status",
            "{$registerTableAs}.date_start as date_start",
            "{$registerTableAs}.date_end as date_end",
            "{$registerTableAs}.number_days_supplement as number_days_supplement",
            "{$registerTableAs}.reason as reason",
            "{$registerTableAs}.reason_id",        
            "{$employeeCreateTableAs}.id as creator_id",
            "{$employeeCreateTableAs}.employee_code as creator_code",
            "{$employeeCreateTableAs}.name as creator_name",
            "{$employeeCreateTableAs}.email as creator_email",
            "{$employeeApproveTableAs}.id as approver_id",
            "{$employeeApproveTableAs}.name as approver_name",
            "{$employeeApproveTableAs}.email as approver_email",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$roleTableAs}.role, ' - ', {$teamTableAs}.name) ORDER BY {$roleTableAs}.role DESC SEPARATOR '; ') as role_name")
        );

        $registerRecord = $registerRecord->join("{$employeeTable} as {$employeeCreateTableAs}", "{$employeeCreateTableAs}.id", "=", "{$registerTableAs}.creator_id")
            ->join("{$employeeTable} as {$employeeApproveTableAs}", "{$employeeApproveTableAs}.id", "=", "{$registerTableAs}.approver_id") 
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
     * [getRegisterByStatus: get list of register by status]
     * @param  [array] $arrRegisterId
     * @param  [int] $status
     * @return [array]
     */
    public static function getRegisterByStatus($arrRegisterId, $status = null)
    {
        $registerTable = self::getTableName();
        $registerTableAs = $registerTable;
        $registerTeamTable = SupplementTeam::getTableName();
        $registerTeamTableAs = 'supplement_team_table';
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_creator';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';

        $listRegisterRecord = self::join("{$employeeTable} as {$employeeCreateTableAs}", "{$employeeCreateTableAs}.id", "=", "{$registerTableAs}.creator_id")
            ->join("{$employeeTable} as {$employeeApproveTableAs}", "{$employeeApproveTableAs}.id", "=", "{$registerTableAs}.approver_id") 
            ->join("{$registerTeamTable} as {$registerTeamTableAs}", "{$registerTeamTableAs}.register_id", "=", "{$registerTableAs}.id")
            ->join("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", "=", "{$registerTeamTableAs}.team_id")
            ->join("{$roleTable} as {$roleTableAs}", "{$roleTableAs}.id", "=", "{$registerTeamTableAs}.role_id")
            ->where("{$roleTableAs}.special_flg", DB::raw(Role::FLAG_POSITION));
        if ($status) {
            $listRegisterRecord = $listRegisterRecord->where("{$registerTableAs}.status", "!=", $status);
        }
            
        $listRegisterRecord = $listRegisterRecord->whereIn("{$registerTableAs}.id", $arrRegisterId)
            ->groupBy("{$registerTableAs}.id")
            ->lists("{$registerTableAs}.id")
            ->toArray();

        return $listRegisterRecord;
    }

    /**
     * Purpose : get list of registers
     *
     * @param $createdBy, $approvedBy, $status, $dataFilter
     *
     * @return collection model
     */
    public static function getListRegisters($createdBy = null, $approvedBy = null, $status = null, $dataFilter, $relater = null)
    {
        $registerTable = self::getTableName();
        $registerTableAs = $registerTable;
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_creator';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $reasonsTbl = SupplementReasons::getTableName();
        $relaterTbl = SupplementRelater::getTableName();

        $collection = self::leftJoin("supplement_employees", "supplement_employees.supplement_registers_id", "=", "supplement_registers.id")
            ->leftJoin("{$reasonsTbl}", "{$reasonsTbl}.id", "=", "{$registerTableAs}.reason_id")
            ->select(
                "{$registerTableAs}.id as register_id",
                "{$registerTableAs}.creator_id as creator_id",
                "{$registerTableAs}.reason as reason",
                "{$registerTableAs}.created_at as created_at",
                "supplement_employees.start_at",
                "supplement_employees.end_at",
                "supplement_employees.number_days",
                "{$registerTableAs}.number_days_supplement as number_days_supplement",
                "{$registerTableAs}.status as status",
                "{$employeeCreateTableAs}.name as creator_name",
                "{$employeeApproveTableAs}.name as approver_name",
                "{$reasonsTbl}.name as reason_name",
                "{$reasonsTbl}.is_type_other",
                "{$registerTableAs}.approved_at as approved_at"
            );

        // join employee register
        if ($createdBy) {    
            $collection->join("{$employeeTable} as {$employeeCreateTableAs}", 
                function ($join) use ($employeeCreateTableAs)
                {
                    $join->on("{$employeeCreateTableAs}.id", '=', "supplement_employees.employee_id");
                }
            );

            $collection->where(function ($query) use ($createdBy) {
                $query->where('creator_id', $createdBy)
                    ->orWhere('employee_id', $createdBy);
            });
        } else {
            $collection->join("{$employeeTable} as {$employeeCreateTableAs}", 
                function ($join) use ($createdBy, $employeeCreateTableAs, $registerTableAs)
                {
                    $join->on("{$employeeCreateTableAs}.id", '=', "{$registerTableAs}.creator_id");
                }
            );
        }

        // join employee approver
        if ($approvedBy) {
            $collection->join("{$employeeTable} as {$employeeApproveTableAs}", 
                function ($join) use ($approvedBy, $employeeApproveTableAs, $registerTableAs) 
                {
                    $join->on("{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver_id");
                    $join->on("{$registerTableAs}.approver_id", '=', DB::raw($approvedBy));
                }
            );
        } else {
            $collection->join("{$employeeTable} as {$employeeApproveTableAs}", 
                function ($join) use ($approvedBy, $employeeApproveTableAs, $registerTableAs) 
                {
                    $join->on("{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver_id");
                }
            );
        }

        if ($relater) {
            $collection->join("{$relaterTbl}",
                function ($join) use ($relater, $registerTableAs, $relaterTbl) {
                    $join->on("{$relaterTbl}.register_id", '=', "{$registerTableAs}.id");
                    $join->on("{$relaterTbl}.relater_id", '=', DB::raw($relater));
                }
            );
        }

        $collection->where("{$registerTableAs}.status", '!=', self::STATUS_CANCEL)
            ->whereNull("{$registerTableAs}.deleted_at");

        if ($status) {
            $collection->where("{$registerTableAs}.status", $status);
        }

        try {
            if (isset($dataFilter['supplement_registers.number_days_supplement'])) {
                $collection->where("{$registerTableAs}.number_days_supplement", $dataFilter['supplement_registers.number_days_supplement']);
            }
            if (isset($dataFilter['supplement_registers.approved_at'])) {
                $approvedDateFilter = Carbon::parse($dataFilter['supplement_registers.approved_at'])->toDateString();
                $collection->whereDate("{$registerTableAs}.approved_at", ">=", $approvedDateFilter);
            }
            if (isset($dataFilter['supplement_employees.start_at'])) {
                $startDateFilter = Carbon::parse($dataFilter['supplement_employees.start_at'])->toDateString();
                $collection->whereDate("supplement_employees.start_at", ">=", $startDateFilter);
            }

            if (isset($dataFilter['supplement_employees.end_at'])) {
                $endDateFilter = Carbon::parse($dataFilter['supplement_employees.end_at'])->toDateString();
                $collection->whereDate("supplement_employees.end_at", "<=", $endDateFilter);
            }
        } catch (\Exception $e) {
            return null;
        }

        $collection->groupBy("{$registerTableAs}.id");

        $pager = Config::getPagerData(null, ['order' => "{$registerTableAs}.id", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * [getListManageRegisters: get list register of manage]
     * @param  [int] $teamId
     * @param  [array] $dataFilter
     * @return [array]
     */
    public static function getListManageRegisters($teamId = null, $dataFilter)
    {
        $registerTable = self::getTableName();
        $registerTableAs = $registerTable;
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_creator';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $registerTeamTable = SupplementTeam::getTableName();
        $registerTeamTableAs = 'supplement_team_table';
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';
        $reasonsTbl = SupplementReasons::getTableName();

        $teamIds = [];
        $teamIds[] = (int) $teamId;
        ManageTimeCommon::getTeamChildRecursive($teamIds, $teamId, true);

        $collection = self::select(
            "{$registerTableAs}.id as register_id",
            "{$registerTableAs}.reason as reason",
            "{$registerTableAs}.date_start as date_start",
            "{$registerTableAs}.date_end as date_end",
            "{$registerTableAs}.number_days_supplement as number_days_supplement",
            "{$registerTableAs}.status as status",
            "{$employeeCreateTableAs}.name as creator_name",
            "{$employeeCreateTableAs}.employee_code as creator_code",
            "{$employeeApproveTableAs}.name as approver_name",
            "{$reasonsTbl}.name as reason_name",
            "{$reasonsTbl}.is_type_other",
            "{$registerTableAs}.approved_at as approved_at",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$roleTableAs}.role, ' - ', {$teamTableAs}.name) ORDER BY {$roleTableAs}.role DESC SEPARATOR '; ') as role_name")
        );
        $collection->leftJoin("{$reasonsTbl}", "{$reasonsTbl}.id", "=", "{$registerTableAs}.reason_id");
        // join employee register
        $collection->join("{$employeeTable} as {$employeeCreateTableAs}", 
            function ($join) use ($registerTableAs, $employeeCreateTableAs)
            {
                $join->on("{$employeeCreateTableAs}.id", '=', "{$registerTableAs}.creator_id");
            }
        );

        // join employee approver
        $collection->join("{$employeeTable} as {$employeeApproveTableAs}",
            function ($join) use ($registerTableAs, $employeeApproveTableAs)
            {
                $join->on("{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver_id");
            }
        );

        // join register team
        if ($teamId) {
            $collection->join(
                "{$registerTeamTable} as {$registerTeamTableAs}", 
                function ($join) use ($teamIds, $registerTableAs, $registerTeamTableAs) 
                {
                    $join->on("{$registerTableAs}.id", '=', "{$registerTeamTableAs}.register_id")
                        ->whereIn("{$registerTeamTableAs}.team_id", $teamIds);
                }
            );
        } else {
            $collection->join(
                "{$registerTeamTable} as {$registerTeamTableAs}", 
                function ($join) use ($registerTableAs, $registerTeamTableAs) 
                {
                    $join->on("{$registerTableAs}.id", '=', "{$registerTeamTableAs}.register_id");
                }
            );
        }

        // join team
        $collection->join(
            "{$teamTable} as {$teamTableAs}", 
            function ($join) use ($teamTableAs, $registerTeamTableAs) 
            {
                $join->on("{$teamTableAs}.id", '=', "{$registerTeamTableAs}.team_id");
            }
        );

        // join role
        $collection->join(
            "{$roleTable} as {$roleTableAs}", 
            function ($join) use ($roleTableAs, $registerTeamTableAs) 
            {
                $join->on("{$roleTableAs}.id", '=', "{$registerTeamTableAs}.role_id");
                $join->on("{$roleTableAs}.special_flg", '=', DB::raw(Role::FLAG_POSITION));
            }
        );

        try {
            if (isset($dataFilter['supplement_registers.number_days_supplement'])) {
                $collection->where("{$registerTableAs}.number_days_supplement", $dataFilter['supplement_registers.number_days_supplement']);
            }
            if (isset($dataFilter['supplement_registers.approved_at'])) {
                $approvedDateFilter = Carbon::parse($dataFilter['supplement_registers.approved_at'])->toDateString();
                $collection->where(function ($query) use ($approvedDateFilter, $registerTableAs) {
                    $query->whereDate("{$registerTableAs}.approved_at", ">=", $approvedDateFilter);
                });
            }

            if (isset($dataFilter['supplement_registers.date_start']) && isset($dataFilter['supplement_registers.date_end'])) {
                $startDateFilter = Carbon::parse($dataFilter['supplement_registers.date_start'])->toDateString();
                $endDateFilter = Carbon::parse($dataFilter['supplement_registers.date_end'])->toDateString();
                $collection->where(function ($query) use ($startDateFilter, $endDateFilter, $registerTableAs) {
                    $query->whereDate("{$registerTableAs}.date_start", "<=", $endDateFilter)
                        ->whereDate("{$registerTableAs}.date_end", ">=", $startDateFilter);
                });
            } else {
                if (isset($dataFilter['supplement_registers.date_start'])) {
                    $startDateFilter = Carbon::parse($dataFilter['supplement_registers.date_start'])->toDateString();
                    $collection->where(function ($query) use ($startDateFilter, $registerTableAs) {
                        $query->whereDate("{$registerTableAs}.date_start", ">=", $startDateFilter)
                            ->orWhereDate("{$registerTableAs}.date_end", ">=", $startDateFilter);
                    });
                }
                if (isset($dataFilter['supplement_registers.date_end'])) {
                    $endDateFilter = Carbon::parse($dataFilter['supplement_registers.date_end'])->toDateString();
                    $collection->where(function ($query) use ($endDateFilter, $registerTableAs) {
                        $query->whereDate("{$registerTableAs}.date_start", "<=", $endDateFilter)
                            ->orWhereDate("{$registerTableAs}.date_end", "<=", $endDateFilter);
                    });
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        $collection->withTrashed()->groupBy("{$registerTableAs}.id");

        $pager = Config::getPagerData(null, ['order' => "{$registerTableAs}.id", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * Purpose : count registers of created by
     *
     * @param int $createdBy id of current user logging
     *
     * @return array
     */
    public static function countRegistersCreatedBy($createdBy)
    {
        return SupplementEmployee::select('status')
            ->join("supplement_registers", "supplement_registers.id", "=", "supplement_employees.supplement_registers_id")
            ->where(function ($query) use ($createdBy) {
                $query->where("employee_id", $createdBy)
                    ->orWhere("creator_id", $createdBy);
            })
            ->groupBy("id")
            ->get()
            ->groupBy("status")
            ->toArray();
    }

    /**
     * Purpose : count registers of approved by
     *
     * @param int, int
     *
     * @return int
     */
    public static function countRegistersApprovedBy($approvedBy)
    {
        $collection = self::where('approver_id', $approvedBy)
            ->where('status', '!=', self::STATUS_CANCEL)
            ->whereNull('deleted_at');

        $collection = $collection->select(
            'approver_id',
            DB::raw('(select COUNT(id) from supplement_registers where approver_id = ' . $approvedBy .' and status != '. self::STATUS_CANCEL . ' and deleted_at is null) as all_register'),
            DB::raw('(select COUNT(id) from supplement_registers where approver_id = ' . $approvedBy . ' and status = ' . self::STATUS_DISAPPROVE .' and deleted_at is null) as status_disapprove'),
            DB::raw('(select COUNT(id) from supplement_registers where approver_id = ' . $approvedBy . ' and status = ' . self::STATUS_UNAPPROVE .' and deleted_at is null) as status_unapprove'),
            DB::raw('(select COUNT(id) from supplement_registers where approver_id = ' . $approvedBy . ' and status = ' . self::STATUS_APPROVED .' and deleted_at is null) as status_approved')
        );

        $collection = $collection->groupBy('approver_id')->first();

        return $collection;
    }

    public static function countRegistersRelatesBy($relater)
    {
        return self::select(
            DB::raw('(select COUNT(id) from supplement_registers JOIN supplement_relaters ON supplement_relaters.register_id = supplement_registers.id where relater_id = ' . $relater . ' and status != ' . self::STATUS_CANCEL . ' and deleted_at is null) as all_register'),
            DB::raw('(select COUNT(id) from supplement_registers JOIN supplement_relaters ON supplement_relaters.register_id = supplement_registers.id where relater_id = ' . $relater . ' and status = ' . self::STATUS_DISAPPROVE . ' and deleted_at is null) as status_disapprove'),
            DB::raw('(select COUNT(id) from supplement_registers JOIN supplement_relaters ON supplement_relaters.register_id = supplement_registers.id where relater_id = ' . $relater . ' and status = ' . self::STATUS_UNAPPROVE . ' and deleted_at is null) as status_unapprove'),
            DB::raw('(select COUNT(id) from supplement_registers JOIN supplement_relaters ON supplement_relaters.register_id = supplement_registers.id where relater_id = ' . $relater . ' and status = ' . self::STATUS_APPROVED . ' and deleted_at is null) as status_approved')
        )
            ->first();
    }

    public static function checkRegisterExist($employeeId, $startDate, $endDate, $registerId = null, $isOt = false)
    {   
        return self::getRegisterExist($employeeId, $startDate, $endDate, $registerId, $isOt)->first();
    }

    public static function getRegisterExist($employeeId, $startDate, $endDate, $registerId = null, $isOt = false)
    {
        $registerEmpTable = SupplementEmployee::getTableName();
        $empTable = Employee::getTableName();
        $registertable = self::getTableName();
        $result = SupplementEmployee::join("{$empTable}", "{$empTable}.id", "=", "{$registerEmpTable}.employee_id")
            ->join("{$registertable}", "{$registertable}.id", "=", "{$registerEmpTable}.supplement_registers_id")
            ->where("{$registerEmpTable}.employee_id", $employeeId)
            ->whereIn("{$registertable}.status", [self::STATUS_UNAPPROVE, self::STATUS_APPROVED])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($query1) use ($startDate) {
                        $query1->where('start_at', '<=', $startDate)
                            ->where('end_at', '>', $startDate);
                        })
                        ->orWhere(function ($query2) use ($endDate) {
                            $query2->where('start_at', '<', $endDate)
                                ->where('end_at', '>=', $endDate);
                        })
                        ->orWhere(function ($query3) use ($startDate, $endDate) {
                            $query3->where('start_at', '>=', $startDate)
                                ->where('end_at', '<=', $endDate);
                });
            })
            ->whereNull("{$registertable}.deleted_at");

        if ($registerId) {
            $result = $result->where('supplement_registers_id', '!=', $registerId);
        }

        if ($isOt) {
            $result = $result->where("{$registertable}.is_ot", self::IS_OT);
        } else {
            $result = $result->where("{$registertable}.is_ot", self::IS_NOT_OT);
        }

        $result->select("{$registerEmpTable}.*", "{$empTable}.name", "{$empTable}.employee_code");

        return $result->get();
    }

    public static function getRegisterOfTimeKeeping($monthOfTimeKeeping, $empsIdOfTimeKeeping, $timekeepingTableStartDate, $timekeepingTableEndDate, $isOT = false)
    {
        $registertable = self::getTableName();
        $result = SupplementRegister::join("supplement_employees", "supplement_employees.supplement_registers_id", "=", "supplement_registers.id")
            ->join('employees', 'employees.id', '=', 'supplement_employees.employee_id')
            ->where('supplement_registers.status', '=', SupplementRegister::STATUS_APPROVED)
            ->whereDate('supplement_employees.start_at', '<=', $timekeepingTableEndDate)
            ->whereDate('supplement_employees.end_at', '>=', $timekeepingTableStartDate)
            ->whereIn('supplement_employees.employee_id', $empsIdOfTimeKeeping)
            ->select(
                "{$registertable}.id",
                'supplement_employees.employee_id',
                'supplement_employees.start_at',
                'supplement_employees.end_at',
                'employees.trial_date',
                'employees.leave_date',
                'employees.offcial_date'
            );

        if ($isOT) {
            $result->leftJoin('working_times', function ($join) use ($monthOfTimeKeeping) {
                $join->on('working_times.employee_id', '=', 'supplement_employees.employee_id');
                $join->where('from_month', '<=', $monthOfTimeKeeping);
                $join->where('to_month', '>=', $monthOfTimeKeeping);
                $join->where('working_times.status', '=', ManageTimeConst::STT_WK_TIME_APPROVED);
            })
            ->where('is_ot', self::IS_OT)
            ->addSelect(
                'start_time1',
                'end_time1',
                'start_time2',
                'end_time2'
            );
        } else {
            $result->where('is_ot', '<>', self::IS_OT);
        }
   
        return $result->get();
    }

    public static function setReason($registerRecord, $reason)
    {
        if ($registerRecord->reason_id) {
            $reasonOtherType = SupplementReasons::find($registerRecord->reason_id);
            if ($reasonOtherType->is_type_other != SupplementReasons::TYPE_OTHER) {
                $reason = '';
            }
        }

        return $reason;
    }

    /**
     * get register suplement approved - supplemnt now only one person -> not join tbl supplement_employee
     * @param  [date] $start [Y-m-d]
     * @param  [date] $end   [Y-m-d]
     * @param  [int] $empId
     * @return [type]
     */
    public static function getSupplementApproved($start, $end, $empId)
    {
        return self::select(
            'id',
            'creator_id',
            'date_start',
            'date_end',
            'number_days_supplement',
            'status',
            'is_ot'
        )
        ->whereDate('date_start', '<=', $end)
        ->whereDate('date_end', '>=', $start)
        ->where('creator_id', "=", $empId)
        ->where('status', "=", self::STATUS_APPROVED)
        ->whereNull('deleted_at')
        ->get();
    }

    /**
     * check register supplement disapproved with register supplement other
     * @param  [array] $ids [id: disaparoved]
     * @return [type]
     */
    public function getExistDeleteWithOtherSupp($ids)
    {
        $tblSupReg = self::getTableName();
        $tblSup = self::getTableName();

        return self::select(
            'supReg.id',
            'supReg.creator_id',
            'supReg.date_start',
            'supReg.date_end',
            'supReg.number_days_supplement',
            'supReg.status'
        )
        ->join("{$tblSupReg} as supReg", "supReg.creator_id", '=', "{$tblSup}.creator_id")
        ->where("{$tblSup}.date_start", '<', DB::raw('supReg.date_end'))
        ->where("{$tblSup}.date_end", '>', DB::raw('supReg.date_start'))
        ->where("{$tblSup}.status", '=', static::STATUS_DISAPPROVE)
        ->where("supReg.status", '!=', static::STATUS_DISAPPROVE)
        ->whereIn("{$tblSup}.id", $ids)
        ->whereNull("supReg.deleted_at")
        ->where("{$tblSup}.is_ot", '=', "supReg.is_ot")
        ->get();
    }

    /**
    * check register supplement overlap delete when approved
    * @param  [array] $ids [id: disaparoved]
    * @return [type]
    */
    public function getExistDelete($ids)
    {
        $tblSupReg = self::getTableName();
        $tblSup = self::getTableName();

        return self::select(
            'supReg.id',
            'supReg.creator_id',
            'supReg.date_start',
            'supReg.date_end',
            'supReg.number_days_supplement',
            'supReg.status'
        )
        ->join("{$tblSupReg} as supReg", "supReg.creator_id", '=', "{$tblSup}.creator_id")
        ->whereIn("supReg.id", $ids)
        ->whereIn("{$tblSup}.id", $ids)
        ->where("{$tblSup}.date_start", '<', DB::raw('supReg.date_end'))
        ->where("{$tblSup}.date_end", '>', DB::raw('supReg.date_start'))
        ->where("{$tblSup}.id", '!=', DB::raw('supReg.id'))
        ->groupBy('supReg.id')
        ->get();
    }

    public static function checkCloseAllTimekeeping($dataEmps)
    {
        $tblTimekeepingTable = TimekeepingTable::getTableName();
        $tblTimekeeping = Timekeeping::getTableName();
        $empError = [];
        foreach ($dataEmps as $item) {
            $empId = !empty($item->empId) ? $item->empId : $item->employee_id;
            $startAt = !empty($item->startAt) ? $item->startAt : $item->start_at;
            $date = Carbon::parse($startAt);
            $year = $date->format('Y');
            $month = $date->format('m');

            $timekeepings = TimekeepingTable::select(
                "{$tblTimekeepingTable}.*",
                "{$tblTimekeeping}.employee_id"
            )
                ->join("{$tblTimekeeping}", "{$tblTimekeeping}.timekeeping_table_id", "=", "{$tblTimekeepingTable}.id")
                ->where("{$tblTimekeepingTable}.year", $year)
                ->where("{$tblTimekeepingTable}.month", $month)
                ->where("{$tblTimekeeping}.employee_id", $empId)
                ->groupBy("{$tblTimekeepingTable}.id")
                ->get();
            $close = 0;
            foreach ($timekeepings as $item) {
                if ($item->lock_up == TimekeepingTable::CLOSE_LOCK_UP) {
                    $close++;
                }
            }
            $countItem = count($timekeepings);
            if ($countItem && $close == $countItem) {
                $emp = Employee::find($empId);
                $empError[] = $emp->name;
            }
        }
        if (!empty($empError)) {
            return implode(", ", $empError);
        }
        return false;
    }

}
