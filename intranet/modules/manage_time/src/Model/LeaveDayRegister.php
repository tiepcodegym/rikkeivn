<?php

namespace Rikkei\ManageTime\Model;

use DB;
use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\EmployeeRelationship;
use Rikkei\Team\Model\LeaveDayRelationMember;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\ManageTime\View\ManageTimeConst;

class LeaveDayRegister extends CoreModel
{
    CONST STATUS_CANCEL = 1;
    CONST STATUS_DISAPPROVE = 2;
    CONST STATUS_UNAPPROVE = 3;
    CONST STATUS_APPROVED = 4;
    const LEAVE_DAY_UNPAID_LIMIT = 30;

    use SoftDeletes;

    protected $table = 'leave_day_registers';

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
        $registerReasonTable = LeaveDayReason::getTableName();
        $registerReasonTableAs = 'leave_day_reason_table';
        $registerTeamTable = LeaveDayTeam::getTableName();
        $registerTeamTableAs = 'leave_day_team_table';
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_created_by';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $employeeAubstituteTableAs = 'employee_table_for_substitute';
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';

        $registerRecord = self::select(
            "{$registerTableAs}.id as id",
            "{$registerTableAs}.id as register_id",
            "{$registerTableAs}.status as status",
            "{$registerTableAs}.date_start as date_start",
            "{$registerTableAs}.date_end as date_end",
            "{$registerTableAs}.number_days_off as number_days_off",
            "{$registerTableAs}.note as note",
            "{$registerTableAs}.created_at as created_at",
            "{$registerTableAs}.company_name",
            "{$registerTableAs}.customer_name",
            "{$registerReasonTableAs}.id as reason_id",
            "{$registerReasonTableAs}.name as reason",
            "{$registerReasonTableAs}.salary_rate",
            "{$registerReasonTableAs}.used_leave_day",
            "{$registerReasonTableAs}.salary_rate as salary_rate",
            "{$employeeCreateTableAs}.id as creator_id",
            "{$employeeCreateTableAs}.employee_code as creator_code",
            "{$employeeCreateTableAs}.name as creator_name",
            "{$employeeCreateTableAs}.email as creator_email",
            "{$employeeApproveTableAs}.id as approver_id",
            "{$employeeApproveTableAs}.name as approver_name",
            "{$employeeApproveTableAs}.email as approver_email",
            "{$employeeAubstituteTableAs}.id as substitute_id",
            "{$employeeAubstituteTableAs}.name as substitute_name",
            "{$employeeAubstituteTableAs}.email as substitute_email",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$roleTableAs}.role, ' - ', {$teamTableAs}.name) ORDER BY {$roleTableAs}.role DESC SEPARATOR '; ') as role_name")
        );

        return $registerRecord->leftjoin("{$employeeTable} as {$employeeCreateTableAs}", "{$employeeCreateTableAs}.id", "=", "{$registerTableAs}.creator_id")
            ->leftjoin("{$registerReasonTable} as {$registerReasonTableAs}", "{$registerReasonTableAs}.id", "=", "{$registerTableAs}.reason_id")
            ->leftjoin("{$employeeTable} as {$employeeApproveTableAs}", "{$employeeApproveTableAs}.id", "=", "{$registerTableAs}.approver_id")
            ->leftjoin("{$employeeTable} as {$employeeAubstituteTableAs}", "{$employeeAubstituteTableAs}.id", "=", "{$registerTableAs}.substitute_id")
            ->leftjoin("{$registerTeamTable} as {$registerTeamTableAs}", "{$registerTeamTableAs}.register_id", "=", "{$registerTableAs}.id")
            ->leftjoin("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", "=", "{$registerTeamTableAs}.team_id")
            ->leftjoin("{$roleTable} as {$roleTableAs}", "{$roleTableAs}.id", "=", "{$registerTeamTableAs}.role_id")
            ->where("{$roleTableAs}.special_flg", DB::raw(Role::FLAG_POSITION))
            ->where("{$registerTableAs}.id", $registerId)
            ->withTrashed()
            ->first();
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
        $registerTeamTable = LeaveDayTeam::getTableName();
        $registerTeamTableAs = 'leave_day_team_table';
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

        return $listRegisterRecord->whereIn("{$registerTableAs}.id", $arrRegisterId)
            ->groupBy("{$registerTableAs}.id")
            ->lists("{$registerTableAs}.id")
            ->toArray();
    }

    /**
     * Purpose : get list of registers
     *
     * @param $createdBy , $approvedBy, $status, $dataFilter
     *
     * @return collection model
     */
    public static function getListRegisters($createdBy = null, $approvedBy = null, $status = null, $dataFilter, $relater = null)
    {
        $registerTable = self::getTableName();
        $registerTableAs = $registerTable;
        $registerReasonTable = LeaveDayReason::getTableName();
        $registerReasonTableAs = 'leave_day_reason_table';
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_creator';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $registerRelateTbl = LeaveDayRelater::getTableName();

        $collection = self::select(
            "{$registerTableAs}.id as register_id",
            "{$registerTableAs}.date_start as date_start",
            "{$registerTableAs}.date_end as date_end",
            "{$registerTableAs}.status as status",
            "{$registerTableAs}.number_days_off as number_days_off",
            "{$registerTableAs}.note as note",
            "{$registerTableAs}.created_at as created_at",
            "{$registerReasonTableAs}.id as id",
            "{$registerReasonTableAs}.name as reason",
            "{$employeeCreateTableAs}.name as creator_name",
            "{$employeeApproveTableAs}.name as approver_name",
            "{$registerTableAs}.approved_at as approved_at"
        );

        // join employee register
        if ($createdBy) {
            $collection->join("{$employeeTable} as {$employeeCreateTableAs}",
                function ($join) use ($createdBy, $employeeCreateTableAs, $registerTableAs) {
                    $join->on("{$employeeCreateTableAs}.id", '=', "{$registerTableAs}.creator_id");
                    $join->on("{$registerTableAs}.creator_id", '=', DB::raw($createdBy));
                }
            );
        } else {
            $collection->join("{$employeeTable} as {$employeeCreateTableAs}",
                function ($join) use ($createdBy, $employeeCreateTableAs, $registerTableAs) {
                    $join->on("{$employeeCreateTableAs}.id", '=', "{$registerTableAs}.creator_id");
                }
            );
        }

        if ($relater) {
            $collection->leftJoin("{$registerRelateTbl}", "$registerTable.id", '=', "{$registerRelateTbl}.register_id");
            $collection->leftJoin("{$employeeTable} as relater_tbl", "relater_tbl.id", '=', "{$registerRelateTbl}.relater_id");
            if ($status) {
                $collection->where(function ($query) use ($registerTable, $registerRelateTbl, $status, $relater) {
                    $query->where(function ($subQuery) use ($registerTable, $registerRelateTbl, $status, $relater) {
                        $subQuery->where("{$registerTable}.status", $status)
                            ->where("{$registerRelateTbl}.relater_id", $relater);
                    });
                    $query->orWhere("{$registerTable}.substitute_id", $relater);
                });
            } else {
                $collection->where(function ($query) use ($registerTable, $registerRelateTbl, $status, $relater) {
                    $query->where("{$registerRelateTbl}.relater_id", $relater);
                    $query->orWhere("{$registerTable}.substitute_id", $relater);
                });
            }
        }

        // join employee approver
        if ($approvedBy) {
            $collection->join("{$employeeTable} as {$employeeApproveTableAs}",
                function ($join) use ($approvedBy, $employeeApproveTableAs, $registerTableAs) {
                    $join->on("{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver_id");
                    $join->on("{$registerTableAs}.approver_id", '=', DB::raw($approvedBy));
                }
            );
        } else {
            $collection->join("{$employeeTable} as {$employeeApproveTableAs}",
                function ($join) use ($approvedBy, $employeeApproveTableAs, $registerTableAs) {
                    $join->on("{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver_id");
                }
            );
        }

        $collection->join("{$registerReasonTable} as {$registerReasonTableAs}",
            function ($join) use ($registerReasonTableAs, $registerTableAs) {
                $join->on("{$registerReasonTableAs}.id", '=', "{$registerTableAs}.reason_id");
            }
        );

        $collection->where("{$registerTableAs}.status", '!=', self::STATUS_CANCEL)
            ->whereNull("{$registerTableAs}.deleted_at");

        if ($status) {
            $collection->where("{$registerTableAs}.status", $status);
        }

        try {
            if (isset($dataFilter['leave_day_registers.number_days_off'])) {
                $collection->where("{$registerTableAs}.number_days_off", $dataFilter['leave_day_registers.number_days_off']);
            }

            if (isset($dataFilter['leave_day_registers.approved_at'])) {
                $approvedDateFilter = Carbon::parse($dataFilter['leave_day_registers.approved_at'])->toDateString();
                $collection->whereDate("{$registerTableAs}.approved_at", "=", $approvedDateFilter);
            }

            if (isset($dataFilter['leave_day_registers.date_start'])) {
                $startDateFilter = Carbon::parse($dataFilter['leave_day_registers.date_start'])->toDateString();
                $collection->whereDate("{$registerTableAs}.date_start", ">=", $startDateFilter);
            }

            if (isset($dataFilter['leave_day_registers.date_end'])) {
                $endDateFilter = Carbon::parse($dataFilter['leave_day_registers.date_end'])->toDateString();
                $collection->whereDate("{$registerTableAs}.date_end", "<=", $endDateFilter);
            }

            if (isset($dataFilter['leave_day_registers.created_at'])) {
                $createdAtFilter = Carbon::parse($dataFilter['leave_day_registers.created_at'])->toDateString();
                $collection->whereDate("{$registerTableAs}.created_at", "=", $createdAtFilter);
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
        $registerReasonTable = LeaveDayReason::getTableName();
        $registerReasonTableAs = 'leave_day_reason_table';
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_creator';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $employeeAubstituteTableAs = 'employee_table_for_substitute';
        $registerTeamTable = LeaveDayTeam::getTableName();
        $registerTeamTableAs = 'leave_day_team_table';
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';

        $teamIds = [];
        $teamIds[] = (int)$teamId;
        ManageTimeCommon::getTeamChildRecursive($teamIds, $teamId, true);

        $collection = self::select(
            "{$registerTableAs}.id as register_id",
            "{$registerTableAs}.date_start as date_start",
            "{$registerTableAs}.date_end as date_end",
            "{$registerTableAs}.status as status",
            "{$registerTableAs}.number_days_off as number_days_off",
            "{$registerTableAs}.note as note",
            "{$registerTableAs}.created_at as created_at",
            "{$registerReasonTableAs}.name as reason",
            "{$employeeCreateTableAs}.name as creator_name",
            "{$employeeCreateTableAs}.employee_code as creator_code",
            "{$employeeApproveTableAs}.name as approver_name",
            "{$employeeAubstituteTableAs}.name as substitute_name",
            "{$registerTableAs}.approved_at as approved_at",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$roleTableAs}.role, ' - ', {$teamTableAs}.name) ORDER BY {$roleTableAs}.role DESC SEPARATOR '; ') as role_name")
        );

        // join employee register
        $collection->join("{$employeeTable} as {$employeeCreateTableAs}",
            function ($join) use ($registerTableAs, $employeeCreateTableAs) {
                $join->on("{$employeeCreateTableAs}.id", '=', "{$registerTableAs}.creator_id");
            }
        );

        // join employee approver
        $collection->join("{$employeeTable} as {$employeeApproveTableAs}",
            function ($join) use ($registerTableAs, $employeeApproveTableAs) {
                $join->on("{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver_id");
            }
        );

        // join employee substitute
        $collection->leftjoin("{$employeeTable} as {$employeeAubstituteTableAs}",
            function ($join) use ($registerTableAs, $employeeAubstituteTableAs) {
                $join->on("{$employeeAubstituteTableAs}.id", '=', "{$registerTableAs}.substitute_id");
            }
        );

        // join leave day reason
        $collection->join("{$registerReasonTable} as {$registerReasonTableAs}",
            function ($join) use ($registerTableAs, $registerReasonTableAs) {
                $join->on("{$registerReasonTableAs}.id", '=', "{$registerTableAs}.reason_id");
            }
        );

        // join register team
        if ($teamId) {
            $collection->join(
                "{$registerTeamTable} as {$registerTeamTableAs}",
                function ($join) use ($teamIds, $registerTableAs, $registerTeamTableAs) {
                    $join->on("{$registerTableAs}.id", '=', "{$registerTeamTableAs}.register_id")
                        ->whereIn("{$registerTeamTableAs}.team_id", $teamIds);
                }
            );
        } else {
            $collection->join(
                "{$registerTeamTable} as {$registerTeamTableAs}",
                function ($join) use ($registerTableAs, $registerTeamTableAs) {
                    $join->on("{$registerTableAs}.id", '=', "{$registerTeamTableAs}.register_id");
                }
            );
        }

        // join team
        $collection->join(
            "{$teamTable} as {$teamTableAs}",
            function ($join) use ($teamTableAs, $registerTeamTableAs) {
                $join->on("{$teamTableAs}.id", '=', "{$registerTeamTableAs}.team_id");
            }
        );

        // join role
        $collection->join(
            "{$roleTable} as {$roleTableAs}",
            function ($join) use ($roleTableAs, $registerTeamTableAs) {
                $join->on("{$roleTableAs}.id", '=', "{$registerTeamTableAs}.role_id");
                $join->on("{$roleTableAs}.special_flg", '=', DB::raw(Role::FLAG_POSITION));
            }
        );

        try {
            if (isset($dataFilter['leave_day_registers.number_days_off'])) {
                $collection->where("{$registerTableAs}.number_days_off", $dataFilter['leave_day_registers.number_days_off']);
            }

            if (isset($dataFilter['leave_day_registers.approved_at'])) {
                $approvedDateFilter = Carbon::parse($dataFilter['leave_day_registers.approved_at'])->toDateString();
                $collection->whereDate("{$registerTableAs}.approved_at", "=", $approvedDateFilter);
            }

            if (isset($dataFilter['leave_day_registers.date_start']) && isset($dataFilter['leave_day_registers.date_end'])) {
                $startDateFilter = Carbon::parse($dataFilter['leave_day_registers.date_start'])->toDateString();
                $endDateFilter = Carbon::parse($dataFilter['leave_day_registers.date_end'])->toDateString();
                $collection->where(function ($query) use ($startDateFilter, $endDateFilter, $registerTableAs) {
                    $query->whereDate("{$registerTableAs}.date_start", "<=", $endDateFilter)
                        ->whereDate("{$registerTableAs}.date_end", ">=", $startDateFilter);
                });
            } else {
                if (isset($dataFilter['leave_day_registers.date_start'])) {
                    $startDateFilter = Carbon::parse($dataFilter['leave_day_registers.date_start'])->toDateString();
                    $collection->where(function ($query) use ($startDateFilter, $registerTableAs) {
                        $query->whereDate("{$registerTableAs}.date_start", ">=", $startDateFilter)
                            ->orWhereDate("{$registerTableAs}.date_end", ">=", $startDateFilter);
                    });
                }
                if (isset($dataFilter['leave_day_registers.date_end'])) {
                    $endDateFilter = Carbon::parse($dataFilter['leave_day_registers.date_end'])->toDateString();
                    $collection->where(function ($query) use ($endDateFilter, $registerTableAs) {
                        $query->whereDate("{$registerTableAs}.date_start", "<=", $endDateFilter)
                            ->orWhereDate("{$registerTableAs}.date_end", "<=", $endDateFilter);
                    });
                }
            }
            if (isset($dataFilter['leave_day_registers.created_at'])) {
                $createdAtFilter = Carbon::parse($dataFilter['leave_day_registers.created_at'])->toDateString();
                $collection->whereDate("{$registerTableAs}.created_at", "=", $createdAtFilter);
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
        $collection = self::where('creator_id', $createdBy)
            ->where('status', '!=', self::STATUS_CANCEL)
            ->whereNull('deleted_at');

        $collection->select(
            'creator_id',
            DB::raw('(select COUNT(id) from leave_day_registers where creator_id = ' . $createdBy . ' and status != ' . self::STATUS_CANCEL . ' and deleted_at is null) as all_register'),
            DB::raw('(select COUNT(id) from leave_day_registers where creator_id = ' . $createdBy . ' and status = ' . self::STATUS_DISAPPROVE . ' and deleted_at is null) as status_disapprove'),
            DB::raw('(select COUNT(id) from leave_day_registers where creator_id = ' . $createdBy . ' and status = ' . self::STATUS_UNAPPROVE . ' and deleted_at is null) as status_unapprove'),
            DB::raw('(select COUNT(id) from leave_day_registers where creator_id = ' . $createdBy . ' and status = ' . self::STATUS_APPROVED . ' and deleted_at is null) as status_approved')
        );

        $collection->groupBy('creator_id');

        return $collection->first();
    }

    /**
     * Purpose : count registers of created by
     *
     * @param int, int
     *
     * @return int
     */
    public static function countRegistersRelated($employeeId)
    {
        $registerTbl = self::getTableName();
        $relatedTbl = LeaveDayRelater::getTableName();
        return self::select('status')
            ->leftJoin("{$relatedTbl}", "{$registerTbl}.id", "=", "{$relatedTbl}.register_id")
            ->where("relater_id", $employeeId)
            ->orWhere('substitute_id', $employeeId)
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
            DB::raw('(select COUNT(id) from leave_day_registers where approver_id = ' . $approvedBy . ' and status != ' . self::STATUS_CANCEL . ' and deleted_at is null) as all_register'),
            DB::raw('(select COUNT(id) from leave_day_registers where approver_id = ' . $approvedBy . ' and status = ' . self::STATUS_DISAPPROVE . ' and deleted_at is null) as status_disapprove'),
            DB::raw('(select COUNT(id) from leave_day_registers where approver_id = ' . $approvedBy . ' and status = ' . self::STATUS_UNAPPROVE . ' and deleted_at is null) as status_unapprove'),
            DB::raw('(select COUNT(id) from leave_day_registers where approver_id = ' . $approvedBy . ' and status = ' . self::STATUS_APPROVED . ' and deleted_at is null) as status_approved')
        );

        $collection = $collection->groupBy('approver_id')->first();

        return $collection;
    }

    public static function checkRegisterExist($employeeId, $startDate, $endDate, $registerId = null)
    {
        $registerTable = self::getTableName();
        $result = self::where("{$registerTable}.creator_id", $employeeId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($query1) use ($startDate) {
                    $query1->where('date_start', '<=', $startDate)
                        ->where('date_end', '>', $startDate);
                })
                    ->orWhere(function ($query2) use ($endDate) {
                        $query2->where('date_start', '<', $endDate)
                            ->where('date_end', '>=', $endDate);
                    })
                    ->orWhere(function ($query3) use ($startDate, $endDate) {
                        $query3->where('date_start', '>=', $startDate)
                            ->where('date_end', '<=', $endDate);
                    });
            })
            ->whereIn('status', [self::STATUS_UNAPPROVE, self::STATUS_APPROVED]);

        if ($registerId) {
            $result = $result->where('id', '!=', $registerId);
        }
        $result = $result->count() ? true : false;

        return $result;
    }

    /*
     * Suggest approver
     */
    public static function suggestApprover($employeeId, $type)
    {
        $tblEmployee = Employee::getTableName();
        $query = Employee::select("{$tblEmployee}.id", "{$tblEmployee}.id as approver_id", "{$tblEmployee}.name as approver_name", "{$tblEmployee}.email as approver_email");

        switch ($type) {
            case ManageTimeConst::TYPE_COMELATE:
                $tblJoin = 'come_late_registers';
                $query->leftJoin($tblJoin, "{$tblJoin}.approver", '=', 'employees.id')
                    ->where("{$tblJoin}.employee_id", $employeeId);
                break;
            case ManageTimeConst::TYPE_MISSION:
                $tblJoin = 'business_trip_registers';
                $query->leftJoin($tblJoin, "{$tblJoin}.approver_id", '=', 'employees.id')
                    ->where("{$tblJoin}.creator_id", $employeeId);
                break;
            case ManageTimeConst::TYPE_SUPPLEMENT:
                $tblJoin = 'supplement_registers';
                $query->leftJoin($tblJoin, "{$tblJoin}.approver_id", '=', 'employees.id')
                    ->where("{$tblJoin}.creator_id", $employeeId);
                break;
            case ManageTimeConst::TYPE_LEAVE_DAY:
                $tblJoin = 'leave_day_registers';
                $query->leftJoin($tblJoin, "{$tblJoin}.approver_id", '=', 'employees.id')
                    ->where("{$tblJoin}.creator_id", $employeeId);
                break;
            default:
                $tblJoin = 'ot_registers';
                $query->leftJoin($tblJoin, "{$tblJoin}.approver", '=', 'employees.id')
                    ->where("{$tblJoin}.employee_id", $employeeId);

        }

        return $query->whereNull("{$tblEmployee}.deleted_at")
            ->where(function ($query) {
                $query->whereNull('leave_date')
                    ->orWhere('leave_date', '>', Carbon::now()->format('Y-m-d'));
            })
            ->orderBy("{$tblJoin}.id", "DESC")
            ->first();
    }

    /**
     * Get list register by employee
     *
     * @param int $employeeId
     * @param array $col selected column
     * @param int $status
     *
     * @return LeaveDayRegister collection
     */
    public static function listRegs($employeeId, $col = ['*'], $status = null)
    {
        $regTbl = self::getTableName();
        $reasonTbl = LeaveDayReason::getTableName();
        $result = self::join("{$reasonTbl}", "{$reasonTbl}.id", "=", "{$regTbl}.reason_id")
            ->where('used_leave_day', ManageTimeConst::USED_LEAVE_DAY)
            ->where('creator_id', $employeeId);
        if ($status) {
            $result->where('status', $status);
        }
        $result->select($col);
        return $result->get();
    }

    public static function getRegisterOfTimeKeeping($monthOfTimeKeeping, $empsIdOfTimeKeeping, $timekeepingTableStartDate, $timekeepingTableEndDate)
    {
        return LeaveDayRegister::select(
                'leave_day_registers.id',
                'creator_id',
                'leave_day_registers.reason_id',
                'employees.email',
                'date_start',
                'date_end',
                'salary_rate',
                'used_leave_day',
                'number_days_off',
                'start_time1',
                'end_time1',
                'start_time2',
                'end_time2',
                'employees.trial_date',
                'employees.offcial_date',
                'employees.leave_date',
                'leave_day_reasons.type as type_reasons' // salary rate
            )
            ->where('leave_day_registers.status', '=', LeaveDayRegister::STATUS_APPROVED)
            ->whereDate('date_start', '<=', $timekeepingTableEndDate)
            ->whereDate('date_end', '>=', $timekeepingTableStartDate)
            ->whereIn('leave_day_registers.creator_id', $empsIdOfTimeKeeping)
            ->join('leave_day_reasons', 'leave_day_reasons.id', '=', 'leave_day_registers.reason_id')
            ->join('employees', 'employees.id', '=', 'leave_day_registers.creator_id')
            ->leftJoin('working_times', function ($join) use ($monthOfTimeKeeping) {
                $join->on('working_times.employee_id', '=', 'leave_day_registers.creator_id');
                $join->where('from_month', '<=', $monthOfTimeKeeping);
                $join->where('to_month', '>=', $monthOfTimeKeeping);
                $join->where('working_times.status', '=', ManageTimeConst::STT_WK_TIME_APPROVED);
            })
            ->get();
    }

    public static function getLeaveDayApproved($empId, $startDate, $endDate)
    {
        return LeaveDayRegister::select(
            'id',
            'creator_id',
            'date_start',
            'date_end',
            'number_days_off',
            'status'
        )
            ->whereDate('date_start', '<=', $endDate)
            ->whereDate('date_end', '>=', $startDate)
            ->where('status', '=', LeaveDayRegister::STATUS_APPROVED)
            ->where('creator_id', '=', $empId)
            ->whereNull('deleted_at')
            ->get();
    }

    public function getRegisterExistSupp($employeeId, $startDate, $endDate)
    {
        $registerTable = self::getTableName();
        return self::where("{$registerTable}.creator_id", $employeeId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('date_end', '=', $startDate)
                    ->orWhereDate('date_start', '=', $endDate);
            })
            ->whereIn('status', [self::STATUS_UNAPPROVE, self::STATUS_APPROVED])
            ->get();
    }

    /**
     * check register leave days disapproved with register leave days other
     * @param  [array] $ids [id: disaparoved]
     * @return [type]
     */
    public function getExistDeleteWithOtherLeaveDays($ids)
    {
        $tblReg = self::getTableName();
        $tblLeaveDays = self::getTableName();

        return self::select(
            'tblReg.id',
            'tblReg.creator_id',
            'tblReg.date_start',
            'tblReg.date_end',
            'tblReg.number_days_off',
            'tblReg.status'
        )
        ->join("{$tblReg} as tblReg", "tblReg.creator_id", '=', "{$tblLeaveDays}.creator_id")
        ->where("{$tblLeaveDays}.date_start", '<=', DB::raw('tblReg.date_end'))
        ->where("{$tblLeaveDays}.date_end", '>=', DB::raw('tblReg.date_start'))
        ->where("{$tblLeaveDays}.status", '=', static::STATUS_DISAPPROVE)
        ->where("tblReg.status", '!=', static::STATUS_DISAPPROVE)
        ->whereIn("{$tblLeaveDays}.id", $ids)
        ->whereNull("tblReg.deleted_at")
        ->get();
    }

    /**
     * check register leave days overlap delete when approved
     * @param  [array] $ids [id: disaparoved]
     * @return [type]
     */
    public function getExistDelete($ids)
    {
        $tblReg = self::getTableName();
        $tblLeaveDaysReg = self::getTableName();

        return self::select(
            'tblReg.id',
            'tblReg.creator_id',
            'tblReg.date_start',
            'tblReg.date_end',
            'tblReg.number_days_off',
            'tblReg.status'
        )
        ->join("{$tblReg} as tblReg", "tblReg.creator_id", '=', "{$tblLeaveDaysReg}.creator_id")
        ->whereIn("{$tblLeaveDaysReg}.id", $ids)
        ->whereIn("tblReg.id", $ids)
        ->where("{$tblLeaveDaysReg}.date_start", '<=', DB::raw('tblReg.date_end'))
        ->where("{$tblLeaveDaysReg}.date_end", '>=', DB::raw('tblReg.date_start'))
        ->where("{$tblLeaveDaysReg}.id", '!=', DB::raw('tblReg.id'))
        ->groupBy('tblReg.id')
        ->get();
    }

    /**
     * lấy đơn xin nghỉ phép của nhiều nhân viên
     *
     * @param $empIds
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getLeaveDayApprovedByEmpId($empIds, $startDate, $endDate)
    {
        return LeaveDayRegister::select(
            'id',
            'creator_id',
            'date_start',
            'date_end',
            'number_days_off',
            'status'
        )
        ->whereDate('date_start', '<=', $endDate)
        ->whereDate('date_end', '>=', $startDate)
        ->where('status', '=', LeaveDayRegister::STATUS_APPROVED)
        ->whereIn('creator_id',  $empIds)
        ->whereNull('deleted_at')
        ->get();
    }

    /**
     * @param $empIds
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function getArrLeaveDayApprovedByEmpId($empIds, $startDate, $endDate)
    {
        $data = [];
        $collection = $this->getLeaveDayApprovedByEmpId($empIds, $startDate, $endDate);
        if (count($collection)) {
            foreach ($collection as $item) {
                $data[$item->creator_id][] = $item;
            }
        }
        return $data;
    }
    
    /**
     * đơn đăng ký nghỉ theo loại phép
     * ko loại bỏ xóa vì có thể duyệt lại khi đã xóa - ko có code check
     * 
     * @param  int $reasonId
     * @param  array $empIds (int)
     * @param  date $startDate (Y-m-d)
     * @param  date $endDate (Y-m-d)
     * @param  array status 
     * @return
     */
    public function getListRegisterByReasonEmpId($reasonId, $empIds, $startDate = null, $endDate = null, $status = null)
    {
        $coll = LeaveDayRegister::select(
            'id',
            'creator_id',
            'reason_id',
            'date_start',
            'date_end',
            'number_days_off',
            'status',
            'created_at',
            'note'
        )
        ->where('reason_id',  $reasonId)
        ->whereIn('creator_id',  $empIds);
        if ($status)
        {
            $coll->whereIn('status', $status);
        } else {
            $coll->whereIn('status', [self::STATUS_APPROVED, self::STATUS_UNAPPROVE]);
        }
        if ($startDate && $endDate) {
            $coll->whereDate('date_start', '<=', $endDate)
                ->whereDate('date_end', '>=', $startDate);
        }
        return  $coll->get();
    }

    /**
     * Get ds nhân viên có đơn nghỉ không lương dài hạn
     *
     * @return array: lists employees
     */
    public static function getEmpUnpaidLeaveDay()
    {
        return self::where('reason_id' , 12) // Đơn nghỉ phép không lương
        ->whereRaw('DATEDIFF(date_end,date_start) > '. static::LEAVE_DAY_UNPAID_LIMIT .  '') // Đơn nghỉ dài hạn 
        ->select("creator_id",
        \DB::raw('(CASE 
        WHEN date_end > NOW() THEN ROUND(DATEDIFF(NOW(),date_start)/30)
        ELSE ROUND(DATEDIFF(date_end,date_start)/30)
        END) AS sub_days_leave'));
    }

    /**
     * Hàm lấy ngày nghỉ không phép dài hạn của nhân viên
     */

    public static function getUnpaidLeaveDay($employeeId , $offcialDate)
    {
        // Lấy danh sách đơn nghỉ phép
        $unpaid_leave_days = 0;
        $leave_days_register = self::where('creator_id', $employeeId) // Lọc theo id 
           ->where('date_start', '>=', Carbon::parse($offcialDate)->format('Y-m-d')) // Ngày làm đơn > ngày chính thức
           ->where('status', static::STATUS_APPROVED) // Chỉ xét đơn đã được duyệt
           ->where('reason_id' , 12) // Đơn nghỉ phép không lương
           ->whereRaw('DATEDIFF(date_end,date_start) > ' . static::LEAVE_DAY_UNPAID_LIMIT . '') // Đơn nghỉ dài hạn 
           ->select("creator_id",
           \DB::raw('(CASE 
           WHEN date_end > NOW() THEN ROUND(DATEDIFF(NOW(),date_start)/30)
           ELSE ROUND(DATEDIFF(date_end,date_start)/30)
           END) AS days'))
           ->get();

        // Tính ngày không được hưởng phép
        $unpaid_leave_days = $leave_days_register->sum('days');
        return $unpaid_leave_days;
    }

    /**
     * thực hiện kiểm tra phạm vi thời gian của ngày bắt đầu và kết thúc có năm trong khoảng thời gian cấp ngày nghỉ phép hay không
     * @param startDate ngày bắt đầu so sánh
     * @param endDate   ngày kết thúc só sánh
     * @param grantDate khoảng thời gian cấp ngày nghỉ phép
     * @return boolean
     */
    public static function checkLeaveDayRegisterGrantDateOtherYear($startDate, $endDate, $grantDate)
    {
        $isOther = false;
        // TH phạm vi so sánh nằm ngoài khoảng thời gian cấp ngày nghỉ phép
        if (!Carbon::parse($startDate)->between(Carbon::parse($grantDate['last_grant_date']), Carbon::parse($grantDate['next_grant_date']))
            || !Carbon::parse($endDate)->between(Carbon::parse($grantDate['last_grant_date']), Carbon::parse($grantDate['next_grant_date'])))
        {
            $isOther = true;
        }
        return $isOther;
    }
}

