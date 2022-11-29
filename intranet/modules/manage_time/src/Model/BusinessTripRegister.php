<?php

namespace Rikkei\ManageTime\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\View\ManageLeaveDay;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\Team\Model\Country;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeContact;
use Rikkei\Team\Model\Province;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;

class BusinessTripRegister extends CoreModel
{

    CONST STATUS_CANCEL = 1;
    CONST STATUS_DISAPPROVE = 2;
    CONST STATUS_UNAPPROVE = 3;
    CONST STATUS_APPROVED = 4;
    const IS_LONG = 1;

    use SoftDeletes;

    protected $table = 'business_trip_registers';

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
        $registerTableAs = self::getTableName();
        $registerTeamTable = BusinessTripTeam::getTableName();
        $registerTeamTableAs = 'business_trip_team_table';
        $employeeTable = Employee::getTableName();
        $employeeCreateTableAs = 'employee_table_for_created_by';
        $employeeApproveTableAs = 'employee_table_for_approver';
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';

        $registerRecord = self::select(
                        "{$registerTableAs}.id as id", "{$registerTableAs}.id as register_id", "{$registerTableAs}.status as status", "{$registerTableAs}.date_start as date_start", "{$registerTableAs}.date_end as date_end", "{$registerTableAs}.number_days_business_trip as number_days_business_trip", "{$registerTableAs}.company_customer as company_customer", "{$registerTableAs}.location as location", "{$registerTableAs}.purpose as purpose", "{$registerTableAs}.country_id", "{$registerTableAs}.province_id", "{$registerTableAs}.is_long", "{$employeeCreateTableAs}.id as creator_id", "{$employeeCreateTableAs}.employee_code as creator_code", "{$employeeCreateTableAs}.name as creator_name", "{$employeeCreateTableAs}.email as creator_email", "{$employeeApproveTableAs}.id as approver_id", "{$employeeApproveTableAs}.name as approver_name", "{$employeeApproveTableAs}.email as approver_email", "{$registerTableAs}.parent_id", DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$roleTableAs}.role, ' - ', {$teamTableAs}.name) ORDER BY {$roleTableAs}.role DESC SEPARATOR '; ') as role_name")
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
        $registerTeamTable = BusinessTripTeam::getTableName();
        $registerTeamTableAs = 'business_trip_team_table';
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
     * @param null $createdBy
     * @param null $approvedBy
     * @param null $status
     * @param $dataFilter
     * @param null $relates
     * @return \Rikkei\Core\Model\collection|\Rikkei\Core\Model\model|null
     */
    public static function getListRegisters($createdBy = null, $approvedBy = null, $status = null, $dataFilter, $relates = null)
    {
        $registerTable = self::getTableName();
        $registerTableAs = $registerTable;
        $employeeTable = Employee::getTableName();
        $relatesTable = BusinessTripRelater::getTableName();
        $employeeCreateTableAs = 'employee_table_for_creator';
        $employeeApproveTableAs = 'employee_table_for_approver';

        $collection = self::join("business_trip_employees", "business_trip_employees.register_id", "=", "business_trip_registers.id")
            ->select(
                "{$registerTableAs}.id as register_id",
                "{$registerTableAs}.creator_id as creator_id",
                "{$registerTableAs}.location as location",
                "{$registerTableAs}.purpose as purpose",
                "business_trip_employees.start_at",
                "business_trip_employees.end_at",
                "{$registerTableAs}.number_days_business_trip as number_days_business_trip",
                "{$registerTableAs}.status as status",
                "{$employeeCreateTableAs}.name as creator_name",
                "{$employeeApproveTableAs}.name as approver_name",
                "{$registerTableAs}.approved_at"
            );

        // join employee register
        if ($createdBy) {
            $collection->join("{$employeeTable} as {$employeeCreateTableAs}", function ($join) use ($employeeCreateTableAs) {
                $join->on("{$employeeCreateTableAs}.id", '=', "business_trip_employees.employee_id");
            }
            );

            $collection->where(function ($query) use ($createdBy) {
                $query->where('creator_id', $createdBy)
                        ->orWhere('employee_id', $createdBy);
            });
        } else {
            $collection->join("{$employeeTable} as {$employeeCreateTableAs}", function ($join) use ($createdBy, $employeeCreateTableAs, $registerTableAs) {
                $join->on("{$employeeCreateTableAs}.id", '=', "{$registerTableAs}.creator_id");
            }
            );
        }

        // join employee approver
        if ($approvedBy) {
            $collection->join("{$employeeTable} as {$employeeApproveTableAs}", function ($join) use ($approvedBy, $employeeApproveTableAs, $registerTableAs) {
                $join->on("{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver_id");
                $join->on("{$registerTableAs}.approver_id", '=', DB::raw($approvedBy));
            }
            );
        } else {
            $collection->join("{$employeeTable} as {$employeeApproveTableAs}", function ($join) use ($approvedBy, $employeeApproveTableAs, $registerTableAs) {
                $join->on("{$employeeApproveTableAs}.id", '=', "{$registerTableAs}.approver_id");
            }
            );
        }

        if ($relates) {
            $collection->join("{$relatesTable}", function ($join) use ($relates, $relatesTable, $registerTableAs) {
                $join->on("{$relatesTable}.register_id", '=', "{$registerTableAs}.id")
                    ->on("{$relatesTable}.relater_id", '=', DB::raw($relates));;
            });
        }

        $collection->where("{$registerTableAs}.status", '!=', self::STATUS_CANCEL)
                ->whereNull("{$registerTableAs}.deleted_at");

        if ($status) {
            $collection->where("{$registerTableAs}.status", $status);
        }

        try {
            if (isset($dataFilter['business_trip_registers.number_days_business_trip'])) {
                $collection->where("{$registerTableAs}.number_days_business_trip", $dataFilter['business_trip_registers.number_days_business_trip']);
            }

            if (isset($dataFilter['business_trip_registers.approved_at'])) {
                $approvedDateFilter = Carbon::parse($dataFilter['business_trip_registers.approved_at'])->toDateString();
                $collection->whereDate("{$registerTableAs}.approved_at", ">=", $approvedDateFilter);
            }

            if (isset($dataFilter['business_trip_employees.start_at'])) {
                $startDateFilter = Carbon::parse($dataFilter['business_trip_employees.start_at'])->toDateString();
                $collection->whereDate("business_trip_employees.start_at", ">=", $startDateFilter);
            }

            if (isset($dataFilter['business_trip_employees.end_at'])) {
                $endDateFilter = Carbon::parse($dataFilter['business_trip_employees.end_at'])->toDateString();
                $collection->whereDate("business_trip_employees.end_at", "<=", $endDateFilter);
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
        $busEmp = BusinessTripEmployee::getTableName();
        $collection = static::getListManageRegistersAll($dataFilter, $teamId, false);

        $pager = Config::getPagerData(null, ['order' => "{$busEmp}.register_id", 'dir' => 'DESC']);
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
        return BusinessTripEmployee::select('status')
                        ->join("business_trip_registers", "business_trip_registers.id", "=", "business_trip_employees.register_id")
                        ->where(function ($query) use ($createdBy) {
                            $query->where("employee_id", $createdBy)
                            ->orWhere("creator_id", $createdBy);
                        })
                        ->whereNull('business_trip_registers.deleted_at')
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
        return self::select(
            DB::raw('(select COUNT(id) from business_trip_registers where approver_id = ' . $approvedBy . ' and status != ' . self::STATUS_CANCEL . ' and deleted_at is null) as all_register'),
            DB::raw('(select COUNT(id) from business_trip_registers where approver_id = ' . $approvedBy . ' and status = ' . self::STATUS_DISAPPROVE . ' and deleted_at is null) as status_disapprove'),
            DB::raw('(select COUNT(id) from business_trip_registers where approver_id = ' . $approvedBy . ' and status = ' . self::STATUS_UNAPPROVE . ' and deleted_at is null) as status_unapprove'),
            DB::raw('(select COUNT(id) from business_trip_registers where approver_id = ' . $approvedBy . ' and status = ' . self::STATUS_APPROVED . ' and deleted_at is null) as status_approved')
        )
            ->first();
    }

    public static function countRegistersRelatesBy($relater)
    {
        return self::select(
            DB::raw('(select COUNT(id) from business_trip_registers JOIN business_trip_relaters ON business_trip_relaters.register_id = business_trip_registers.id where relater_id = ' . $relater . ' and status != ' . self::STATUS_CANCEL . ' and deleted_at is null) as all_register'),
            DB::raw('(select COUNT(id) from business_trip_registers JOIN business_trip_relaters ON business_trip_relaters.register_id = business_trip_registers.id where relater_id = ' . $relater . ' and status = ' . self::STATUS_DISAPPROVE . ' and deleted_at is null) as status_disapprove'),
            DB::raw('(select COUNT(id) from business_trip_registers JOIN business_trip_relaters ON business_trip_relaters.register_id = business_trip_registers.id where relater_id = ' . $relater . ' and status = ' . self::STATUS_UNAPPROVE . ' and deleted_at is null) as status_unapprove'),
            DB::raw('(select COUNT(id) from business_trip_registers JOIN business_trip_relaters ON business_trip_relaters.register_id = business_trip_registers.id where relater_id = ' . $relater . ' and status = ' . self::STATUS_APPROVED . ' and deleted_at is null) as status_approved')
        )
            ->first();
    }

    public static function checkRegisterExist($employeeId, $startDate, $endDate, $registerId = null)
    {
        $registerTable = self::getTableName();
        $empTable = Employee::getTableName();
        $registerEmpTable = BusinessTripEmployee::getTableName();
        $result = BusinessTripEmployee::join("{$empTable}", "{$empTable}.id", "=", "{$registerEmpTable}.employee_id")
                ->join("{$registerTable}", "{$registerTable}.id", "=", "{$registerEmpTable}.register_id")
                ->where("{$registerEmpTable}.employee_id", $employeeId)
                ->whereIn("{$registerTable}.status", [self::STATUS_UNAPPROVE, self::STATUS_APPROVED])
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where(function ($query1) use ($startDate) {
                        $query1->where('start_at', '<=', $startDate)
                        ->where('end_at', '>=', $startDate);
                    })
                    ->orWhere(function ($query2) use ($endDate) {
                        $query2->where('start_at', '<=', $endDate)
                        ->where('end_at', '>=', $endDate);
                    })
                    ->orWhere(function ($query3) use ($startDate, $endDate) {
                        $query3->where('start_at', '>=', $startDate)
                        ->where('end_at', '<=', $endDate);
                    });
                })
                ->whereNull("{$registerTable}.deleted_at");

        if ($registerId) {
            $result = $result->where('register_id', '!=', $registerId);
            $reg = self::find($registerId);
            $result->where("{$registerTable}.id", "<>", $reg->parent_id);
        }

        $result->select("{$registerEmpTable}.*", "{$empTable}.name", "{$empTable}.employee_code");

        return $result->first();
    }

    public static function getRegisterOfTimeKeeping($monthOfTimeKeeping, $empsIdOfTimeKeeping, $timekeepingTableStartDate, $timekeepingTableEndDate, $timeKeepingTable = null)
    {
        $tblBusinessTeam = BusinessTripTeam::getTableName();
        $tblBusRegister = self::getTableName();
        $collection = BusinessTripRegister::join("business_trip_employees", "business_trip_employees.register_id", "=", "business_trip_registers.id")
                ->leftJoin('working_times', function ($join) use ($monthOfTimeKeeping) {
                    $join->on('working_times.employee_id', '=', 'business_trip_employees.employee_id');
                    $join->where('from_month', '<=', $monthOfTimeKeeping);
                    $join->where('to_month', '>=', $monthOfTimeKeeping);
                    $join->where('working_times.status', '=', ManageTimeConst::STT_WK_TIME_APPROVED);
                })
                ->join('employees', 'employees.id', '=', 'business_trip_employees.employee_id')
                ->select(
                    'business_trip_registers.id',
                    'business_trip_employees.employee_id',
                    'business_trip_employees.start_at',
                    'business_trip_employees.end_at',
                    'start_time1',
                    'end_time1',
                    'start_time2',
                    'end_time2',
                    'employees.trial_date',
                    'employees.offcial_date',
                    'employees.leave_date',
                    'business_trip_registers.country_id',
                    'business_trip_registers.is_long'
                )
                ->where('business_trip_registers.status', '=', BusinessTripRegister::STATUS_APPROVED)
                ->whereDate('business_trip_employees.start_at', '<=', $timekeepingTableEndDate)
                ->whereDate('business_trip_employees.end_at', '>=', $timekeepingTableStartDate)
                ->whereIn('business_trip_employees.employee_id', $empsIdOfTimeKeeping);

        // dat nuoc khong tinh cong tac dai han vao cham cong:
        // viet nam van tinh la cong tac, nhung o nhat thi khong tinh la cong tac
        // phai co bang cong chi tiet cho nhan vien do
        // $ctCountry = Country::getIdByCode('jp');
        // if ($ctCountry &&
        //     isset($ctCountry['jp']) &&
        //     $ctCountry['jp'] &&
        //     $timeKeepingTable &&
        //     $timeKeepingTable->team_id
        // ) {
        //     $teamPathTree = Team::getTeamPathTree();
        //     // if bang cong la japan thi bang cong japan khong duoc tinh la ct
        //     if (isset($teamPathTree[$timeKeepingTable->team_id]) &&
        //         $teamPathTree[$timeKeepingTable->team_id]['data']['code'] &&
        //         starts_with($teamPathTree[$timeKeepingTable->team_id]['data']['code'], 'japan')
        //     ) {
        //         $collection->where(function ($query) use ($ctCountry) {
        //             $query->orWhereNull('business_trip_registers.country_id')
        //                 ->orWhere('business_trip_registers.country_id', '!=', $ctCountry['jp'])
        //                 ->orWhere(function ($query) use ($ctCountry) {
        //                     $query->where('business_trip_registers.country_id', '=', $ctCountry['jp'])
        //                         ->where(function ($query) {
        //                             $query->orWhere('business_trip_registers.is_long', '!=', 1)
        //                                 ->orWhereNull('business_trip_registers.is_long');
        //                         });
        //                 });
        //         });
        //     }
        // }
        return $collection->get();
    }

    /**
     * Delete record by id
     *
     * @param int $id
     *
     * @return boolean
     */
    public static function deleteById($id)
    {
        return self::where('id', $id)->delete();
    }

    public static function getChild($id)
    {
        return self::where('parent_id', $id)->get();
    }

    public static function getCountryIgnoreOrCorrect($country, &$countryIdIgnore = [], &$countryIdCorrect = [])
    {
        $country = intval($country);
        $countryInfo = null;
        if ($country == 1) {
            $countryInfo = Country::where('country_code', 'VN')->first();
            if ($countryInfo) {
                $countryIdCorrect[] = $countryInfo->id;
            }
        } elseif ($country == 2) {
            $countryInfo = Country::where('country_code', 'VN')->first();
            if ($countryInfo) {
                $countryIdIgnore[] = $countryInfo->id;
            }
        } else {

        }
    }

    /**
     * [getListManageReport: get list register is approved of manage]
     * @param string $filterDate date string format "dd-yyyy"
     * @param string $filterDate 1||2||''
     * @param array $teamTreeAvailable array id teams
     * @return [array]
     */
    public static function getListManageReport($filterDate = null, $country = '', array $teamTreeAvailable = [])
    {
        $viewTableName = 'view_business_trip';
        $collection = DB::table($viewTableName);
        if (count($teamTreeAvailable) > 0) {
            $collection->whereIn("$viewTableName.team_id", $teamTreeAvailable);
        }
        //Filter
        $firstDate = date('Y-m-d H:i:s', strtotime($filterDate . '-01 00:00:00'));
        $lastDate = date('Y-m-t H:i:s', strtotime($filterDate . '-01 23:59:59'));
        $collection->where("$viewTableName.end_at", '>=', $firstDate);
        $collection->where("$viewTableName.start_at", '<=', $lastDate);
        $collection->where(function($sql) use($viewTableName, $firstDate) {
            $sql->whereNull("$viewTableName.leave_date")->orWhere("$viewTableName.leave_date", '>=', $firstDate);
        });

        $countryIdIgnore = [];
        $countryIdCorrect = [];
        self::getCountryIgnoreOrCorrect($country, $countryIdIgnore, $countryIdCorrect);
        if (count($countryIdCorrect) > 0) {
            $collection->whereIn("{$viewTableName}.country_id", $countryIdCorrect);
        }

        if (count($countryIdIgnore) > 0) {
            $collection->whereNotIn("{$viewTableName}.country_id", $countryIdIgnore);
        }
        return $collection->get();
    }

    /**
     * processing data before render view Business Trip report
     * column 'number_days_business_trip' đang sai nếu dùng lại thì fix lại
     * 
     * @param Collection $collectionModel
     * @param Carbon $date
     * @param boolean $isExport
     * @return array
     */
    public function processingBeforeRenderViewBusinessReport($collectionModel, $date, $isExport = false)
    {
        $firstDay = Carbon::parse($date->firstOfMonth())->toDateString();
        $lastDay = Carbon::parse($date->lastOfMonth())->toDateString();
        $data = [];
        $exceptEmpTeams = [];
        foreach ($collectionModel as $item) {
            if (!isset($data[$item->team_id])) {
                $data[$item->team_id] = [
                    'name' => $item->team_name,
                    'employees' => [],
                ];
            }
            if (!isset($data[$item->team_id]['employees'][$item->employee_id])) {
                if (isset($exceptEmpTeams[$item->team_id])
                    && in_array($item->employee_id, $exceptEmpTeams[$item->team_id])) {
                    continue;
                }
                $aryStartAt = explode(';', $item->team_start_at);
                $aryEndAt = explode(';', $item->team_end_at);
                $isIncludeMonth = false;
                foreach ($aryStartAt as $i => $startAt) {
                    $aryEndAt[$i] = $aryEndAt[$i] === 'NULL' ? $lastDay : $aryEndAt[$i];
                    if ($startAt <= $lastDay && $aryEndAt[$i] >= $firstDay) {
                        $isIncludeMonth = true;
                        break;
                    }
                }
                if ($isIncludeMonth === false) {
                    $exceptEmpTeams[$item->team_id][] = $item->employee_id;
                    continue;
                }

                $location = $isExport ? [$item->location] : htmlentities($item->location) . '<br>';
                $startAt = $isExport ? [$item->start_at] : $item->start_at . '<br>';
                $endAt = $isExport ? [$item->end_at] : $item->end_at . '<br>';
                $data[$item->team_id]['employees'][$item->employee_id] = [
                    'name' => $item->employee_name,
                    'code' => $item->employee_code,
                    'email' => $item->email,
                    'onsite_days' => 0,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'location' => $location,
                ];
            } else {
                if ($isExport) {
                    $data[$item->team_id]['employees'][$item->employee_id]['location'][] = $item->location;
                    $data[$item->team_id]['employees'][$item->employee_id]['start_at'][] = $item->start_at;
                    $data[$item->team_id]['employees'][$item->employee_id]['end_at'][] = $item->end_at;
                } else {
                    $data[$item->team_id]['employees'][$item->employee_id]['location'] .= htmlentities($item->location) . '<br>';
                    $data[$item->team_id]['employees'][$item->employee_id]['start_at'] .= $item->start_at . '<br>';
                    $data[$item->team_id]['employees'][$item->employee_id]['end_at'] .= $item->end_at . '<br>';
                }
            }

            if ($item->start_at < $firstDay) {
                $item->start_at = $firstDay;
            }
            if ($item->end_at > $lastDay) {
                $item->end_at = $lastDay;
            }
            $cbStart = Carbon::parse($item->start_at);
            $cbEnd = Carbon::parse($item->end_at);
            $day = 0;
            while(strtotime($cbStart->toDateString()) <= strtotime($cbEnd->toDateString())) {
                $day += $cbStart->isWeekday() ? 1 : 0;
                $cbStart->addDay();
            }
            $data[$item->team_id]['employees'][$item->employee_id]['onsite_days'] += $day;
        }
        // filter to delete team has 0 employee
        foreach ($data as $key => $team) {
            if (!$team['employees']) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    /**
     * calculate number day business trip
     * @param  [cacbon] $startDate
     * @param  [cacbon] $endDate
     * @param  [int] $empId
     * @return [int]
     */
    public static function getNumberBusinessTrip($startDate, $endDate, $empId)
    {
        $employee = Employee::find($empId);
        if (empty($employee)) {
            return 0;
        }
        $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($employee);
        return ManageLeaveDay::getTimeLeaveDay($startDate, $endDate, $employee, $teamCodePrefix);
    }

    /**
     * [getListManageRegisters: get list register of manage]
     * @param  [int] $teamId
     * @param  [array] $dataFilter
     * @param boolean $fgContact
     * @return [array]
     */
    public static function getListManageRegistersAll($dataFilter, $teamId = null, $fgContact = true)
    {
        $registerTable = self::getTableName();
        $teamMember = TeamMember::getTableName();
        $busEmp = BusinessTripEmployee::getTableName();
        $tblEmp = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tblRole = Role::getTableName();
        $tblRegisterTeam = BusinessTripTeam::getTableName();
        $tblEmpContact = EmployeeContact::getTableName();
        $selectedColumns = [
            "{$busEmp}.register_id",
            "{$busEmp}.employee_id",
            "tblEmp.employee_code",
            "tblEmp.name",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(role_emp.role, '-', team_emp.name)) AS postion_emp"),
            "bus_reg.creator_id",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(role_reg.role, '-', team_reg.name)) AS postion_reg"),
            "bus_reg.location",
            "bus_reg.purpose",
            "{$busEmp}.start_at",
            "{$busEmp}.end_at",
            "bus_reg.approver_id",
            "bus_reg.status",
            "bus_reg.number_days_business_trip",
            "tblEmp_app.name as name_approver",
            "tblEmp.email",
            "bus_reg.approved_at"
        ];
        if ($fgContact) {
            $selectedColumns[] = "tblEmpContact.skype";
            $selectedColumns[] = "tblEmpContact.mobile_phone";
        }

        $collection = BusinessTripEmployee::select($selectedColumns)
        ->join("{$registerTable} as bus_reg", "bus_reg.id", "=", "{$busEmp}.register_id")
        ->leftJoin("{$tblEmp} as tblEmp", "tblEmp.id", "=", "{$busEmp}.employee_id");
        if ($fgContact) {
            $collection->leftJoin("{$tblEmpContact} as tblEmpContact", "tblEmpContact.employee_id", "=", "tblEmp.id");
        }
        $collection->leftJoin("{$teamMember} as teamMember", "teamMember.employee_id", "=", "{$busEmp}.employee_id")
            ->join("{$tblTeam} as team_emp", "team_emp.id", "=", "teamMember.team_id")
            ->join("{$tblRole} as role_emp", "role_emp.id", "=", "teamMember.role_id")
        ->leftJoin("{$tblRegisterTeam} as reg_team", "reg_team.register_id", "=", "bus_reg.id")
            ->join("{$tblTeam} as team_reg", "team_reg.id", "=", "reg_team.team_id")
            ->join("{$tblRole} as role_reg", "role_reg.id", "=", "reg_team.role_id")
        ->leftJoin("{$tblEmp} as tblEmp_app", "tblEmp_app.id", "=", "bus_reg.approver_id")
        ->whereNull("bus_reg.deleted_at");

        $teamIds = [];
        $teamIds[] = (int) $teamId;
        ManageTimeCommon::getTeamChildRecursive($teamIds, $teamId, true);

        if ($teamId) {
            $collection->whereIn('team_emp.id', $teamIds);
        }

        try {
            if (isset($dataFilter['bus_reg.number_days_business_trip'])) {
                $collection->where("bus_reg.number_days_business_trip", $dataFilter['bus_reg.number_days_business_trip']);
            }
            if (isset($dataFilter['bus_reg.approved_at'])) {
                $approvedDateFilter = Carbon::parse($dataFilter['bus_reg.approved_at'])->toDateString();
                $collection->whereDate("bus_reg.approved_at", ">=", $approvedDateFilter);
            }
            if (isset($dataFilter["{$busEmp}.start_at"]) && isset($dataFilter["{$busEmp}.end_at"])) {
                $startDateFilter = Carbon::parse($dataFilter["{$busEmp}.start_at"])->toDateString();
                $endDateFilter = Carbon::parse($dataFilter["{$busEmp}.end_at"])->toDateString();
                $collection->where(function ($query) use ($startDateFilter, $endDateFilter, $busEmp) {
                    $query->whereDate("{$busEmp}.start_at", "<=", $endDateFilter)
                        ->whereDate("{$busEmp}.end_at", ">=", $startDateFilter);
                });
            } else {
                if (isset($dataFilter["{$busEmp}.start_at"])) {
                    $startDateFilter = Carbon::parse($dataFilter["{$busEmp}.start_at"])->toDateString();
                    $collection->where(function ($query) use ($startDateFilter, $busEmp) {
                        $query->whereDate("{$busEmp}.start_at", ">=", $startDateFilter)
                            ->orWhereDate("{$busEmp}.end_at", ">=", $startDateFilter);
                    });
                }
                if (isset($dataFilter["{$busEmp}.end_at"])) {
                    $endDateFilter = Carbon::parse($dataFilter["{$busEmp}.end_at"])->toDateString();
                    $collection->where(function ($query) use ($endDateFilter, $busEmp) {
                        $query->whereDate("{$busEmp}.start_at", "<=", $endDateFilter)
                            ->orWhereDate("{$busEmp}.end_at", ">=", $endDateFilter);
                    });
                }
            }


        } catch (\Exception $e) {
            return null;
        }

        $collection->where('status', '<>', self::STATUS_CANCEL)
            ->groupBy("{$busEmp}.register_id")
            ->groupBy("{$busEmp}.employee_id")
            ->orderBy(
                DB::raw("CASE WHEN bus_reg.status = "
                . static::STATUS_UNAPPROVE
                . " THEN bus_reg.status + 3
                ELSE bus_reg.status
                END "),
                'DESC'
            )
            ->orderBy("bus_reg.date_start", 'DESC')
            ->orderBy(DB::raw("CASE WHEN bus_reg.creator_id = {$busEmp}.employee_id THEN  1 ELSE 0 END "), 'DESC');
        return $collection;
    }

    /**
     * get list manage register business trip apporved
     * @param  [int] $teamId
     * @param  [array] $dataFilter
     * @param  [int] $status
     * @return [array]
     */
    public static function getListManageRegistersApproved($dataFilter, $status, $teamId = null)
    {
        $busEmp = BusinessTripEmployee::getTableName();
        $collection = static::getListManageRegistersAll($dataFilter, $teamId)->where('status', "=", $status);

        $pager = Config::getPagerData(null, ['order' => "{$busEmp}.register_id", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * export list manage register business trip apporved
     * @param  [int] $teamId
     * @param  [array] $dataFilter
     * @param  [int] $status
     * @return [array]
     */
    public static function exportListManageRegistersApproved($dataFilter, $status, $teamId = null)
    {
        $busEmp = BusinessTripEmployee::getTableName();
        $collection = static::getListManageRegistersAll($dataFilter, $teamId)->where('status', "=", $status);
        $urlSubmitFilter = Redirect()->route('manage_time::timekeeping.manage.report-business-trip')->getTargetUrl() . "/" . $teamId;

        $pager = Config::getPagerData(null, ['order' => "{$busEmp}.register_id", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        return self::filterGrid($collection, [], $urlSubmitFilter, 'LIKE');
    }

    /**
     * check register mission disapproved with register mission other
     * @param  [array] $ids [id: disaparoved]
     * @return [type]
     */
    public function getExistDeleteWithOtherMission($ids)
    {
        $tblReg1 = self::getTableName();
        $tblReg2 = self::getTableName();
        $tblBusEmp1 = BusinessTripEmployee::getTableName();
        $tblBusEmp2 = BusinessTripEmployee::getTableName();

        return self::select(
            'tblBusEmp2.register_id',
            'tblBusEmp2.employee_id',
            'tblBusEmp2.start_at',
            'tblBusEmp2.end_at',
            'tblReg2.status'
        )
        ->leftJoin("{$tblBusEmp1} as tblBusEmp1", "tblBusEmp1.register_id", '=', "{$tblReg1}.id")
        ->join("{$tblBusEmp2} as tblBusEmp2", "tblBusEmp2.employee_id", '=', "tblBusEmp1.employee_id")
        ->leftJoin("{$tblReg2} as tblReg2", "tblBusEmp2.register_id", '=', "tblReg2.id")
        ->where("tblBusEmp1.start_at", '<=', DB::raw('tblBusEmp2.end_at'))
        ->where("tblBusEmp1.end_at", '>=', DB::raw('tblBusEmp2.start_at'))
        ->where("{$tblReg1}.status", '=', static::STATUS_DISAPPROVE)
        ->where("tblReg2.status", '!=', static::STATUS_DISAPPROVE)
        ->whereNull("tblReg2.deleted_at")
        ->whereIn("{$tblReg1}.id", $ids)
        ->get();
    }

    /**
     * check register mission overlap delete when approved
     * @param  [array] $ids [id: disaparoved]
     * @return [type]
     */
    public function getExistDelete($ids)
    {
        $tblReg1 = self::getTableName();
        $tblReg2 = self::getTableName();
        $tblBusEmp1 = BusinessTripEmployee::getTableName();
        $tblBusEmp2 = BusinessTripEmployee::getTableName();

        return self::select(
            'tblBusEmp2.register_id',
            'tblBusEmp2.employee_id',
            'tblBusEmp2.start_at',
            'tblBusEmp2.end_at',
            'tblReg2.status'
        )
        ->leftJoin("{$tblBusEmp1} as tblBusEmp1", "tblBusEmp1.register_id", '=', "{$tblReg1}.id")
        ->join("{$tblBusEmp2} as tblBusEmp2", "tblBusEmp2.employee_id", '=', "tblBusEmp1.employee_id")
        ->leftJoin("{$tblReg2} as tblReg2", "tblBusEmp2.register_id", '=', "tblReg2.id")
        ->where("tblBusEmp1.start_at", '<=', DB::raw('tblBusEmp2.end_at'))
        ->where("tblBusEmp1.end_at", '>=', DB::raw('tblBusEmp2.start_at'))
        ->whereIn("tblBusEmp2.register_id", $ids)
        ->whereIn("tblBusEmp1.register_id", $ids)
        ->where("tblBusEmp2.register_id", '!=', DB::raw('tblBusEmp1.register_id'))
        ->get();
    }


    /**
     * get employee onsite by provices
     *
     * @param  Carbon $date
     * @param  array $provinces
     * @param  array $empIds
     * @return collection
     */
    public function getEmployeeOnsiteProvince($date, $provinces, $empIds = [])
    {
        $tblReg = self::getTableName();
        $tblBusEmp = BusinessTripEmployee::getTableName();
        $tblProvince = Province::getTableName();

        $collection = static::select(
            'tblBusEmp.employee_id',
            'tblBusEmp.start_at',
            'tblBusEmp.end_at'
        )
        ->leftJoin("{$tblBusEmp} as tblBusEmp", "tblBusEmp.register_id", '=', "{$tblReg}.id")
        ->leftJoin("{$tblProvince} as tblprovince", "tblprovince.id", '=', "{$tblReg}.province_id")
        ->whereIn('tblprovince.province', $provinces);

        if ($empIds) {
            $collection->whereIn('tblBusEmp.employee_id', $empIds);
        }
        return $collection->where("{$tblReg}.status", static::STATUS_APPROVED)
            ->whereDate('tblBusEmp.start_at', '<=', $date->format('Y-m-d'))
            ->whereDate('tblBusEmp.end_at', '>=', $date->format('Y-m-d'))
            ->groupBy('tblBusEmp.employee_id')
            ->get();
    }
}
