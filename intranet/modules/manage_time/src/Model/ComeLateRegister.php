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
use Rikkei\Team\View\TeamConst;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComeLateRegister extends CoreModel
{
    CONST STATUS_CANCEL = 1;
    CONST STATUS_DISAPPROVE = 2;
    CONST STATUS_UNAPPROVE = 3;
    CONST STATUS_APPROVED = 4;

    use SoftDeletes;

    protected $table = 'come_late_registers';

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
            ->where("{$tblEmployee}.id", $this->approver)
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
        $registerTeamTable = ComeLateTeam::getTableName();
        $registerTeamTableAs = 'register_team_table';
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_created_by';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';

        $registerRecord = self::select(
            "{$registerTableAs}.id as id",
            "{$registerTableAs}.id as register_id",
            "{$registerTableAs}.status as status",
            "{$registerTableAs}.approver",
            "{$registerTableAs}.date_start as date_start",
            "{$registerTableAs}.date_end as date_end",
            "{$registerTableAs}.late_start_shift as late_start_shift",
            "{$registerTableAs}.early_mid_shift as early_mid_shift",
            "{$registerTableAs}.late_mid_shift as late_mid_shift",
            "{$registerTableAs}.early_end_shift as early_end_shift",
            "{$registerTableAs}.reason as reason",
            "{$employeeCreateTableAs}.id as creator_id",
            "{$employeeCreateTableAs}.employee_code as creator_code",
            "{$employeeCreateTableAs}.name as creator_name",
            "{$employeeCreateTableAs}.email as creator_email",
            "{$employeeApproveTableAs}.id as approver_id",
            "{$employeeApproveTableAs}.name as approver_name",
            "{$employeeApproveTableAs}.email as approver_email",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$roleTableAs}.role, ' - ', {$teamTableAs}.name) ORDER BY {$roleTableAs}.role DESC SEPARATOR '; ') as role_name")
        );

        $registerRecord = $registerRecord->join("{$employeeTable} as {$employeeCreateTableAs}", "{$employeeCreateTableAs}.id", "=", "{$registerTableAs}.employee_id")
            ->join("{$employeeTable} as {$employeeApproveTableAs}", "{$employeeApproveTableAs}.id", "=", "{$registerTableAs}.approver") 
            ->join("{$registerTeamTable} as {$registerTeamTableAs}", "{$registerTeamTableAs}.come_late_id", "=", "{$registerTableAs}.id")
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
        $registerTeamTable = ComeLateTeam::getTableName();
        $registerTeamTableAs = 'register_team_table';
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_creator';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';

        $listRegisterRecord = self::join("{$employeeTable} as {$employeeCreateTableAs}", "{$employeeCreateTableAs}.id", "=", "{$registerTableAs}.employee_id")
            ->join("{$employeeTable} as {$employeeApproveTableAs}", "{$employeeApproveTableAs}.id", "=", "{$registerTableAs}.approver") 
            ->join("{$registerTeamTable} as {$registerTeamTableAs}", "{$registerTeamTableAs}.come_late_id", "=", "{$registerTableAs}.id")
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
    public static function getListRegisters($createdBy = null, $approvedBy = null, $status = null, $dataFilter)
    {
        $registerTable = self::getTableName();
        $registerTableAs = $registerTable;
        $registerDayWeekTable = ComeLateDayWeek::getTableName();
        $registerDayWeekTableAs = 'come_late_day_week_table';
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_creator';
        $employeeApproveTableAs = 'employee_table_for_approver';

        $collection = self::select(
            "{$registerTableAs}.id as register_id",
            "{$registerTableAs}.date_start as date_start",
            "{$registerTableAs}.date_end as date_end",
            "{$registerTableAs}.late_start_shift as late_start_shift",
            "{$registerTableAs}.early_mid_shift as early_mid_shift",
            "{$registerTableAs}.late_mid_shift as late_mid_shift",
            "{$registerTableAs}.early_end_shift as early_end_shift",
            "{$registerTableAs}.status as status",
            "{$registerTableAs}.reason as reason",
            "{$employeeCreateTableAs}.name as creator_name",
            "{$employeeApproveTableAs}.name as approver_name",
            DB::raw("GROUP_CONCAT(DISTINCT {$registerDayWeekTableAs}.day ORDER BY {$registerDayWeekTableAs}.day ASC SEPARATOR ';') as apply_days")
        );

        // join employee register
        if ($createdBy) {    
            $collection->join("{$employeeTable} as {$employeeCreateTableAs}", 
                function ($join) use ($createdBy, $employeeCreateTableAs, $registerTableAs)
                {
                    $join->on("{$employeeCreateTableAs}.id", '=', "{$registerTableAs}.employee_id");
                    $join->on("{$registerTableAs}.employee_id", '=', DB::raw($createdBy));
                }
            );
        } else {
            $collection->join("{$employeeTable} as {$employeeCreateTableAs}", 
                function ($join) use ($createdBy, $employeeCreateTableAs, $registerTableAs)
                {
                    $join->on("{$employeeCreateTableAs}.id", '=', "{$registerTableAs}.employee_id");
                }
            );
        }

        // join employee approver
        if ($approvedBy) {
            $collection->join("{$employeeTable} as {$employeeApproveTableAs}", 
                function ($join) use ($approvedBy, $employeeApproveTableAs, $registerTableAs) 
                {
                    $join->on("{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver");
                    $join->on("{$registerTableAs}.approver", '=', DB::raw($approvedBy));
                }
            );
        } else {
            $collection->join("{$employeeTable} as {$employeeApproveTableAs}", 
                function ($join) use ($approvedBy, $employeeApproveTableAs, $registerTableAs) 
                {
                    $join->on("{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver");
                }
            );
        }

        // join come late day week
        $collection->leftJoin("{$registerDayWeekTable} as {$registerDayWeekTableAs}", 
            function ($join) use ($registerDayWeekTableAs, $registerTableAs) 
            {
                $join->on("{$registerDayWeekTableAs}.come_late_id", '=', "{$registerTableAs}.id");
            }
        );

        $collection->where("{$registerTableAs}.status", '!=', self::STATUS_CANCEL)
            ->whereNull("{$registerTableAs}.deleted_at");

        if ($status) {
            $collection->where("{$registerTableAs}.status", $status);
        }

        try {
            if (isset($dataFilter['come_late_registers.date_start']) && isset($dataFilter['come_late_registers.date_end'])) {
                $startDateFilter = Carbon::parse($dataFilter['come_late_registers.date_start'])->toDateString();
                $endDateFilter = Carbon::parse($dataFilter['come_late_registers.date_end'])->toDateString();
                $collection->where(function ($query) use ($startDateFilter, $endDateFilter, $registerTableAs) {
                    $query->whereDate("{$registerTableAs}.date_start", "<=", $endDateFilter)
                        ->whereDate("{$registerTableAs}.date_end", ">=", $startDateFilter);
                });

            } else {
                if (isset($dataFilter['come_late_registers.date_start'])) {
                    $startDateFilter = Carbon::parse($dataFilter['come_late_registers.date_start'])->toDateString();
                    $collection->where(function ($query) use ($startDateFilter, $registerTableAs) {
                        $query->whereDate("{$registerTableAs}.date_start", ">=", $startDateFilter)
                            ->orWhereDate("{$registerTableAs}.date_end", ">=", $startDateFilter);
                    });
                }
                if (isset($dataFilter['come_late_registers.date_end'])) {
                    $endDateFilter = Carbon::parse($dataFilter['come_late_registers.date_end'])->toDateString();
                    $collection->where(function ($query) use ($endDateFilter, $registerTableAs) {
                        $query->whereDate("{$registerTableAs}.date_start", "<=", $endDateFilter)
                            ->orWhereDate("{$registerTableAs}.date_end", "<=", $endDateFilter);
                    });
                }
            }

            if (isset($dataFilter['come_late_registers.late_start_shift'])) {
                $collection->where("{$registerTableAs}.late_start_shift", $dataFilter['come_late_registers.late_start_shift']);
            }

            if (isset($dataFilter['come_late_registers.early_mid_shift'])) {
                $collection->where("{$registerTableAs}.early_mid_shift", $dataFilter['come_late_registers.early_mid_shift']);
            }

            if (isset($dataFilter['come_late_registers.late_mid_shift'])) {
                $collection->where("{$registerTableAs}.late_mid_shift", $dataFilter['come_late_registers.late_mid_shift']);
            }

            if (isset($dataFilter['come_late_registers.early_end_shift'])) {
                $collection->where("{$registerTableAs}.early_end_shift", $dataFilter['come_late_registers.early_end_shift']);
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
        $registerDayWeekTable = ComeLateDayWeek::getTableName();
        $registerDayWeekTableAs = 'come_late_day_week_table';
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_creator';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $registerTeamTable = ComeLateTeam::getTableName();
        $registerTeamTableAs = 'register_team_table';
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';

        $teamIds = [];
        $teamIds[] = (int) $teamId;
        ManageTimeCommon::getTeamChildRecursive($teamIds, $teamId);

        $collection = self::select(
            "{$registerTableAs}.id as register_id",
            "{$registerTableAs}.date_start as date_start",
            "{$registerTableAs}.date_end as date_end",
            "{$registerTableAs}.late_start_shift as late_start_shift",
            "{$registerTableAs}.early_mid_shift as early_mid_shift",
            "{$registerTableAs}.late_mid_shift as late_mid_shift",
            "{$registerTableAs}.early_end_shift as early_end_shift",
            "{$registerTableAs}.status as status",
            "{$registerTableAs}.reason as reason",
            "{$employeeCreateTableAs}.id as creator_id",
            "{$employeeCreateTableAs}.name as creator_name",
            "{$employeeCreateTableAs}.employee_code as creator_code",
            "{$employeeApproveTableAs}.name as approver_name",
            DB::raw("GROUP_CONCAT(DISTINCT {$registerDayWeekTableAs}.day ORDER BY {$registerDayWeekTableAs}.day ASC SEPARATOR ';') as apply_days"),
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$roleTableAs}.role, ' - ', {$teamTableAs}.name) ORDER BY {$roleTableAs}.role DESC SEPARATOR '; ') as role_name")
        );

        // join employee register
        $collection->join("{$employeeTable} as {$employeeCreateTableAs}", 
            function ($join) use ($registerTableAs, $employeeCreateTableAs)
            {
                $join->on("{$employeeCreateTableAs}.id", '=', "{$registerTableAs}.employee_id");
            }
        );

        // join employee approver
        $collection->join("{$employeeTable} as {$employeeApproveTableAs}",
            function ($join) use ($registerTableAs, $employeeApproveTableAs)
            {
                $join->on("{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver");
            }
        );

        // join come late day week
        $collection->leftJoin("{$registerDayWeekTable} as {$registerDayWeekTableAs}", 
            function ($join) use ($registerDayWeekTableAs, $registerTableAs) 
            {
                $join->on("{$registerDayWeekTableAs}.come_late_id", '=', "{$registerTableAs}.id");
            }
        );

        // join register team
        if ($teamId) {
            $collection->join(
                "{$registerTeamTable} as {$registerTeamTableAs}", 
                function ($join) use ($teamIds, $registerTableAs, $registerTeamTableAs) 
                {
                    $join->on("{$registerTableAs}.id", '=', "{$registerTeamTableAs}.come_late_id")
                        ->whereIn("{$registerTeamTableAs}.team_id", $teamIds);
                }
            );
        } else {
            $collection->join(
                "{$registerTeamTable} as {$registerTeamTableAs}", 
                function ($join) use ($registerTableAs, $registerTeamTableAs) 
                {
                    $join->on("{$registerTableAs}.id", '=', "{$registerTeamTableAs}.come_late_id");
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
            if (isset($dataFilter['come_late_registers.date_start']) && isset($dataFilter['come_late_registers.date_end'])) {
                $startDateFilter = Carbon::parse($dataFilter['come_late_registers.date_start'])->toDateString();
                $endDateFilter = Carbon::parse($dataFilter['come_late_registers.date_end'])->toDateString();
                $collection->where(function ($query) use ($startDateFilter, $endDateFilter, $registerTableAs) {
                    $query->whereDate("{$registerTableAs}.date_start", "<=", $endDateFilter)
                        ->whereDate("{$registerTableAs}.date_end", ">=", $startDateFilter);
                });

            } else {
                if (isset($dataFilter['come_late_registers.date_start'])) {
                    $startDateFilter = Carbon::parse($dataFilter['come_late_registers.date_start'])->toDateString();
                    $collection->where(function ($query) use ($startDateFilter, $registerTableAs) {
                        $query->whereDate("{$registerTableAs}.date_start", ">=", $startDateFilter)
                            ->orWhereDate("{$registerTableAs}.date_end", ">=", $startDateFilter);
                    });
                }
                if (isset($dataFilter['come_late_registers.date_end'])) {
                    $endDateFilter = Carbon::parse($dataFilter['come_late_registers.date_end'])->toDateString();
                    $collection->where(function ($query) use ($endDateFilter, $registerTableAs) {
                        $query->whereDate("{$registerTableAs}.date_start", "<=", $endDateFilter)
                            ->orWhereDate("{$registerTableAs}.date_end", "<=", $endDateFilter);
                    });
                }
            }

            if (isset($dataFilter['come_late_registers.late_start_shift'])) {
                $collection->where("{$registerTableAs}.late_start_shift", $dataFilter['come_late_registers.late_start_shift']);
            }

            if (isset($dataFilter['come_late_registers.late_start_shift'])) {
                $collection->where("{$registerTableAs}.late_start_shift", $dataFilter['come_late_registers.late_start_shift']);
            }

            if (isset($dataFilter['come_late_registers.early_mid_shift'])) {
                $collection->where("{$registerTableAs}.early_mid_shift", $dataFilter['come_late_registers.early_mid_shift']);
            }

            if (isset($dataFilter['come_late_registers.late_mid_shift'])) {
                $collection->where("{$registerTableAs}.late_mid_shift", $dataFilter['come_late_registers.late_mid_shift']);
            }

            if (isset($dataFilter['come_late_registers.early_end_shift'])) {
                $collection->where("{$registerTableAs}.early_end_shift", $dataFilter['come_late_registers.early_end_shift']);
            }

            if (isset($dataFilter['employee_table_for_creator.employee_code'])) {
                $collection->where("{$employeeTableAs}.employee_code", $dataFilter['employee_table_for_creator.employee_code']);
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
     * @param int, int
     *
     * @return int
     */
    public static function countRegistersCreatedBy($createdBy)
    {
        $collection = self::where('employee_id', $createdBy)
            ->where('status', '!=', self::STATUS_CANCEL)
            ->whereNull('deleted_at');

        $collection = $collection->select(
            'employee_id',
            DB::raw('(select COUNT(id) from come_late_registers where employee_id = ' . $createdBy .' and status != '. self::STATUS_CANCEL . ' and deleted_at is null) as all_register'),
            DB::raw('(select COUNT(id) from come_late_registers where employee_id = ' . $createdBy . ' and status = ' . self::STATUS_DISAPPROVE .' and deleted_at is null) as status_disapprove'),
            DB::raw('(select COUNT(id) from come_late_registers where employee_id = ' . $createdBy . ' and status = ' . self::STATUS_UNAPPROVE .' and deleted_at is null) as status_unapprove'),
            DB::raw('(select COUNT(id) from come_late_registers where employee_id = ' . $createdBy . ' and status = ' . self::STATUS_APPROVED .' and deleted_at is null) as status_approved')
        );

        $collection = $collection->groupBy('employee_id')->first();

        return $collection;
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
        $collection = self::where('approver', $approvedBy)
            ->where('status', '!=', self::STATUS_CANCEL)
            ->whereNull('deleted_at');

        $collection = $collection->select(
            'approver',
            DB::raw('(select COUNT(id) from come_late_registers where approver = ' . $approvedBy .' and status != '. self::STATUS_CANCEL . ' and deleted_at is null) as all_register'),
            DB::raw('(select COUNT(id) from come_late_registers where approver = ' . $approvedBy . ' and status = ' . self::STATUS_DISAPPROVE .' and deleted_at is null) as status_disapprove'),
            DB::raw('(select COUNT(id) from come_late_registers where approver = ' . $approvedBy . ' and status = ' . self::STATUS_UNAPPROVE .' and deleted_at is null) as status_unapprove'),
            DB::raw('(select COUNT(id) from come_late_registers where approver = ' . $approvedBy . ' and status = ' . self::STATUS_APPROVED .' and deleted_at is null) as status_approved')
        );

        $collection = $collection->groupBy('approver')->first();

        return $collection;
    }

    public static function checkRegisterExist($employeeId, $startDate, $endDate, $registerId = null)
    {
        $registerTable = self::getTableName();
        $result = self::where("{$registerTable}.employee_id", $employeeId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($query1) use ($startDate) {
                        $query1->where('date_start', '<=', $startDate)
                            ->where('date_end', '>=', $startDate);
                        })
                        ->orWhere(function ($query2) use ($endDate) {
                            $query2->where('date_start', '<=', $endDate)
                                ->where('date_end', '>=', $endDate);
                        })
                        ->orWhere(function ($query3) use ($startDate, $endDate) {
                            $query3->where('date_start', '>=', $startDate)
                                ->where('date_end', '<=', $endDate);
                });
            });

        if ($registerId) {
            $result = $result->where('id', '!=', $registerId);
        }

        $result = $result->count() ? true : false;

        return $result;
    }

    /**
     * [getDataCcMail: get data EmployeeId]
     * @param  [array] $arrEmployeeId
     * @return Employee collection
     */
    public static function getDataCcMail($arrEmployeeId)
    {
        $tblEmployee = Employee::getTableName();

        return Employee::select("id","{$tblEmployee}.email as emailCc")
            ->whereIn("id",$arrEmployeeId)
            ->get();
    }
}
