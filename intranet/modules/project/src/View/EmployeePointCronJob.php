<?php

namespace Rikkei\Project\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Project\Model\CronjobEmployeePoints;
use Rikkei\Project\Model\EmployeeContractPoint;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team;

class EmployeePointCronJob
{
    const TABLE = 'cronjob_employee_points';

    public static function cronJobEmployeePoint()
    {
        DB::beginTransaction();
        try {
            $defaultMonth = "2020-01";
            $currentMonth = Carbon::now()->format('Y-m');

            // The employees have been created
            if(self::isExistsDB()) {

                // Check exists employess by current month
                if (self::_isExistsDBEmployeeByCurrentMonth()) {

                    // The employees have modify (created, updated)
                    if (self::_isModifiedEmployee()) {
                        $filter = [
                            'monthFrom' => $defaultMonth,
                            'monthTo' => $currentMonth,
                        ];
                        $data = self::_getPointEmployees($filter, true);

                        // Get all employees
                        $employees = CronjobEmployeePoints::lists('employee_id')->toArray();

                        foreach ($data as $employee) {
                            $employeeId = $employee['employee_id'];
                            $month = $employee['month'];
                            $teamId = $employee['team_id'];

                            // Check the employees have been exists
                            if (in_array($employeeId, $employees)) {

                                // Update employee have been exists
                                CronjobEmployeePoints::where('employee_id', $employeeId)->where('month', $month)->where('team_id', $teamId)->update($employee);
                            } else {
                                $employee['created_at'] = Carbon::now();

                                // Create
                                CronjobEmployeePoints::insert($employee);
                            }
                        }
                    }
                } else {
                    $filter = [
                        'monthFrom' => $currentMonth,
                        'monthTo' => $currentMonth
                    ];
                    $data = self::_getPointEmployees($filter);
                    CronjobEmployeePoints::insert($data);
                }

            } else {
                // Run at the first time
                $filter = [
                    'monthFrom' => $defaultMonth,
                    'monthTo' => $currentMonth
                ];
                $data = self::_getPointEmployees($filter);
                CronjobEmployeePoints::insert($data);
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    private static function _isExistsDBEmployeeByCurrentMonth() {
        $currentMonth = Carbon::now()->format('Y-m');
        $isCheck = DB::table(self::TABLE)->where('month', $currentMonth)->count();

        if ($isCheck > 0) {
            return true;
        }

        return false;
    }

    public static function isExistsDB()
    {
        $isCheck = DB::table(self::TABLE)->count();

        if ($isCheck > 0) {
            return true;
        }

        return false;
    }

    private static function _getPointEmployees($filter, $isModified = false)
    {
        $firstDateOfMonth = Carbon::createFromFormat('Y-m', $filter['monthFrom'])->firstOfMonth()->format('Y-m-d');
        $endDateOfMonth = Carbon::createFromFormat('Y-m', $filter['monthTo'])->endOfMonth()->format('Y-m-d');
        $arrTeamId = self::_getTeamProjectCost();
        $result = [];

        foreach ($arrTeamId as $key => $value) {
            $filters = [
                'monthFrom' => $firstDateOfMonth,
                'monthTo' => $endDateOfMonth,
                'team_id' => $value['team_id']
            ];

            $employeePoints = $isModified ? self::_getDataByMonthWithEmployeeModified($filters) : self::_getDataByMonth($filters);

            if (count($employeePoints) == 0) continue;

            $employeePointsDimension = OperationMember::convertDataMemberToDimension($employeePoints);
            $collectionMaternity = OperationMember::getMaternityLeaveDayCollection($filters);
            $maternityDataDimension = OperationMember::transformMaternityLeaveDayCollectionToDimension($collectionMaternity);

            $employees = self::_getPointEmployeeEachMonth(
                $filters,
                $employeePointsDimension,
                $maternityDataDimension
            );
            $data = self::_getDetailEmployeeEachMonth($employees, $value, $isModified);
            $result = array_merge($result, $data);
        }

        return $result;
    }

    /**
     * The employees have created or updated
     * Check employees modified include fields created_at, updated_at from table Employee, contracts, EmployeeTeamHistory
     */
    private static function _isModifiedEmployee() {
        $employeeTable = Employee::getTableName();
        $contractTable = ContractModel::getTableName();
        $teamHistoryTable = EmployeeTeamHistory::getTableName();
        $collection = Employee::join("{$contractTable} as c", "c.employee_id", "=", "${employeeTable}.id")
            ->join("{$teamHistoryTable} as h", "h.employee_id", "=", "${employeeTable}.id")
            ->whereRaw(DB::raw("date_format(${employeeTable}.created_at, '%Y-%m-%d') = curdate()"))
            ->orwhereRaw(DB::raw("date_format(${employeeTable}.updated_at, '%Y-%m-%d') = curdate()"))
            ->orwhereRaw(DB::raw("date_format(c.created_at, '%Y-%m-%d') = curdate()"))
            ->orwhereRaw(DB::raw("date_format(c.updated_at, '%Y-%m-%d') = curdate()"))
            ->orwhereRaw(DB::raw("date_format(h.created_at, '%Y-%m-%d') = curdate()"))
            ->orwhereRaw(DB::raw("date_format(h.updated_at, '%Y-%m-%d') = curdate()"))
            ->get()->count();

        return $collection;
    }

    private static function _getPointEmployeeEachMonth($filters, $employeePointsDimension, $maternityDataDimension)
    {
        $employeePoints = $employeePointsDimension;
        $employeeMaternity = $maternityDataDimension;
        $result = [];
        $monthFrom = date('Y-m', strtotime($filters['monthFrom']));
        while ($monthFrom <= date('Y-m', strtotime($filters['monthTo']))) {
            $total = 0;
            foreach ($employeePoints as $key => $value) {
                if (isset($employeePoints[$key])) {
                    foreach ($employeePoints[$key] as $k_time => $v_data) {
                        $listTimeline = $employeePoints[$key][$k_time];
                        if(!self::_checkValidContractDateEmployee($monthFrom, $listTimeline)) {
                            continue;
                        }
                        $employeeInforMappingMonth = self::_getDataMappingMonth($employeePoints, $key, $k_time, $monthFrom);
                        if($employeeInforMappingMonth) {
                            $point = floatval($employeeInforMappingMonth['point']);
                            if ($employeeInforMappingMonth['join_date'] > $monthFrom) {
                                continue;
                            }
                            if ($employeeInforMappingMonth['join_date'] === $monthFrom) {
                                $point = floatval($employeeInforMappingMonth['actual_point_first_month']);
                            }
                            if($employeeInforMappingMonth['leave_date'] === $monthFrom) {
                                $point = floatval($employeeInforMappingMonth['actual_point_last_month']);
                            }
                            $detailEmployeeId = explode("-", $key)[1];

                            $percentNotWorking = self::_getPointForMaternity($monthFrom, $detailEmployeeId, $employeeMaternity);
                            $point = ($point - ($percentNotWorking * $point));
                            $totalPoint = floatval($point >= 0 ? $point : 0);
                            $total += $totalPoint;
                            $result[$monthFrom][] = [
                                'employee_id' => $employeeInforMappingMonth['employee_id'],
                                'email' => $employeeInforMappingMonth['email'],
                                'contract_type' => $employeeInforMappingMonth['contract_type'],
                                'point' => $totalPoint
                            ];
                        }
                    }
                }
            }

            $monthFrom = Carbon::parse($monthFrom)->addMonths(1)->format('Y-m');
        }

        return $result;
    }

    private static function _getPointForMaternity($monthFrom, $detailEmployeeId, $employeeMaternity)
    {
        if (!isset($employeeMaternity[$detailEmployeeId])) {
            return 0;
        }

        foreach ($employeeMaternity[$detailEmployeeId] as $key => $value) {
            $currentData = $employeeMaternity[$detailEmployeeId][$key];
            if ($monthFrom == $currentData['leave_start']) {
                return $currentData['percent_not_working_from_leave_start'];
            } else if ($monthFrom == $currentData['leave_end']) {
                return $currentData['percent_not_working_until_leave_end'];
            } else if ($monthFrom > $currentData['leave_start'] && $monthFrom < $currentData['leave_end']) {
                return 1;
            }
        }

        return 0;
    }

    private static function _getDataMappingMonth($employeePoints, $employeeId, $startTeam, $scopeMonthFrom)
    {
        if (!isset($employeePoints[$employeeId][$startTeam][$scopeMonthFrom])) {
            if ($scopeMonthFrom > '2012-01') {
                $arrayKeyContractStartDates = array_keys($employeePoints[$employeeId][$startTeam]);
                sort($arrayKeyContractStartDates);
                $reverseArray = array_reverse($arrayKeyContractStartDates);
                foreach ($reverseArray as $key => $value) {
                    if ($scopeMonthFrom > $reverseArray[$key]) {
                        $result = $employeePoints[$employeeId][$startTeam][$reverseArray[$key]];
                        if ($result && $result['leave_date'] && $scopeMonthFrom > $result['leave_date']) {
                            return null;
                        }

                        return $result;
                    }
                }
            }
        }

        return $employeePoints[$employeeId][$startTeam][$scopeMonthFrom];
    }

    private static function _checkValidContractDateEmployee($monthFrom, $listTimeline)
    {
        $months = array_keys($listTimeline);
        $flag = false;
        foreach ($months as $key => $value) {
            if ($monthFrom >= $value) $flag = true;
        }

        return $flag;
    }

    private static function _getTeamProjectCost()
    {
        $arrNew = [];
        $teamConditions = [
            'is_soft_dev' => Team::IS_SOFT_DEVELOPMENT,
        ];
        $teamIsDev = Team::getTeamList($teamConditions, ['id', 'name', 'branch_code', 'parent_id'])->toArray();
        $parent = self::_getParentByTeam($teamIsDev);
        // Get teamId có is_soft_dev = 1.
        foreach ($teamIsDev as $value) {
            $arrNew[$value ['id']] = [
                'team_id' => $value ['id'],
                'branch_code' => $value ['branch_code']
            ];
        }
        // Parent id team.
        foreach ($parent as $val) {
            if (!array_key_exists($val, $arrNew)) {
                $arrNew[$val] = [
                    'team_id' => $val,
                    'branch_code' => ''
                ];
            }
        }
        // Danh sach chi nhanh.
        $listPrefixBranch = Team::listPrefixBranch();
        $teamIsbranh = Team::select('id', 'name', 'branch_code')
            ->whereIn('branch_code', array_keys($listPrefixBranch))
            ->where('is_branch', 1)
            ->get()->toArray();

        foreach ($teamIsbranh as $value) {
            if (!array_key_exists($value ['id'], $arrNew)) {
                $arrNew[$value['id']] = [
                    'team_id' => $value ['id'],
                    'branch_code' => $value ['branch_code']
                ];
            }
        }

        return $arrNew;
    }

    private static function _getParentByTeam($teamIds)
    {
        $litsTeam = Team::getTeamPathTree();
        $teamParent = [];
        foreach ($teamIds as $value) {
            if (array_key_exists($value['id'], $litsTeam)) {
                $teamParent = array_merge($teamParent, $litsTeam[$value['id']]['parent']);
            }
        }
        return array_unique($teamParent);
    }

    /**
     * get data by month
     *
     * @param $filter
     *
     * @return object
     */
    private static function _getDataByMonth($filter)
    {
        $teamId = '';
        if ($filter['team_id']) {
            $teamId = $filter['team_id'];
            $team = Team::getTeamPath($withTrashed = true);
            if (isset($team[$teamId]['child'])) { //get all team childs except BOD
                $teamId = '(' . $teamId . ', ' . implode(", ", $team[$teamId]['child']) . ')';
            } else {
                $teamId = '(' . $teamId . ')';
            }
        }

        $employeeContactHistoryTable = ContractModel::getTableName();
        $newEmployeeContactHistoryTable = 'empl_contract_histories';
        $employeeTable = Employee::getTableName();
        $employeeContractPointTable = EmployeeContractPoint::getTableName();
        $teamHistoryTable = EmployeeTeamHistory::getTableName();
        $teamsTable = Team::getTableName();
        $teamIsSoftDev = Team::IS_SOFT_DEVELOPMENT;
        $typePQA = Team::TEAM_TYPE_PQA;

        $items = DB::table("{$employeeTable}")
            ->select(
                "{$employeeTable}.name",
                "{$employeeTable}.email",
                "{$employeeTable}.employee_code",
                "{$employeeTable}.id",
                "{$employeeTable}.leave_date",
                "{$employeeTable}.deleted_at",
                "{$employeeTable}.join_date",
                "{$employeeTable}.working_type",
                DB::raw("DATE_FORMAT({$employeeTable}.join_date, '%Y-%m') as employee_contract_date"),
                "{$employeeContractPointTable}.point",
                "{$employeeContractPointTable}.contract_type",
                "{$newEmployeeContactHistoryTable}.*",
                "{$teamHistoryTable}.team_id",
                "{$teamHistoryTable}.start_at as team_start_at",
                "{$teamHistoryTable}.end_at as team_end_at",
                "{$teamsTable}.name as team_name"
            )
            ->leftJoin(DB::raw("(
            select a.employee_id, DATE_FORMAT(a.start_at, '%Y-%m') as contract_date, DATE_FORMAT(a.end_at, '%Y-%m') as contract_end_date,
            a.type as contract_type from {$employeeContactHistoryTable} a
            inner join
            (
                select
                {$employeeContactHistoryTable}.employee_id, start_at, type,  Max(id) as latest_id
                from {$employeeContactHistoryTable}
                where start_at <= ? and start_at >= '2012-01-01 00:00:00' and deleted_at is NULL
                group by
                {$employeeContactHistoryTable}.employee_id,
                DATE_FORMAT({$employeeContactHistoryTable}.start_at, '%Y-%m')
            ) as b on  a.id = b.latest_id ) as {$newEmployeeContactHistoryTable}"), "{$employeeTable}.id", '=', "{$newEmployeeContactHistoryTable}.employee_id")
            ->leftJoin($employeeContractPointTable, "{$employeeContractPointTable}.contract_type", '=', "{$newEmployeeContactHistoryTable}.contract_type")
            ->leftJoin($teamHistoryTable, "{$teamHistoryTable}.employee_id", '=', "{$employeeTable}.id")
            ->leftJoin($teamsTable, "{$teamsTable}.id", '=', "{$teamHistoryTable}.team_id")
            ->whereRaw("{$teamsTable}.is_soft_dev = {$teamIsSoftDev}")
            ->whereRaw("{$teamsTable}.type != {$typePQA}")
            ->whereNotNull("{$employeeTable}.join_date")
            ->whereNull("{$teamHistoryTable}.deleted_at");

        if ($teamId) {
            $items = $items->whereRaw(
                "{$teamHistoryTable}.team_id IN {$teamId}"
            );
        }

        return $items->orderBy('team_id', 'asc')
            ->orderBy("{$employeeTable}.id", 'asc')
            ->orderBy("{$teamHistoryTable}.id", 'asc')
            ->setBindings([$filter['monthTo'] . ' 00:00:00'])
            ->get();
    }

    private static function _getDataByMonthWithEmployeeModified($filter)
    {
        $teamId = '';
        if ($filter['team_id']) {
            $teamId = $filter['team_id'];
            $team = Team::getTeamPath($withTrashed = true);
            if (isset($team[$teamId]['child'])) { //get all team childs except BOD
                $teamId = '(' . $teamId . ', ' . implode(", ", $team[$teamId]['child']) . ')';
            } else {
                $teamId = '(' . $teamId . ')';
            }
        }

        $employeeContactHistoryTable = ContractModel::getTableName();
        $newEmployeeContactHistoryTable = 'empl_contract_histories';
        $employeeTable = Employee::getTableName();
        $employeeContractPointTable = EmployeeContractPoint::getTableName();
        $teamHistoryTable = EmployeeTeamHistory::getTableName();
        $teamsTable = Team::getTableName();
        $teamIsSoftDev = Team::IS_SOFT_DEVELOPMENT;
        $typePQA = Team::TEAM_TYPE_PQA;

        $items = DB::table("{$employeeTable}")
            ->select(
                "{$employeeTable}.name",
                "{$employeeTable}.email",
                "{$employeeTable}.employee_code",
                "{$employeeTable}.id",
                "{$employeeTable}.leave_date",
                "{$employeeTable}.deleted_at",
                "{$employeeTable}.join_date",
                "{$employeeTable}.working_type",
                DB::raw("DATE_FORMAT({$employeeTable}.join_date, '%Y-%m') as employee_contract_date"),
                "{$employeeContractPointTable}.point",
                "{$employeeContractPointTable}.contract_type",
                "{$newEmployeeContactHistoryTable}.*",
                "{$teamHistoryTable}.team_id",
                "{$teamHistoryTable}.start_at as team_start_at",
                "{$teamHistoryTable}.end_at as team_end_at",
                "{$teamsTable}.name as team_name"
            )
            ->leftJoin(DB::raw("(
            select a.employee_id, DATE_FORMAT(a.start_at, '%Y-%m') as contract_date, DATE_FORMAT(a.end_at, '%Y-%m') as contract_end_date,
            a.type as contract_type, contract_created_at, contract_updated_at from {$employeeContactHistoryTable} a
            inner join
            (
                select
                {$employeeContactHistoryTable}.employee_id, start_at, type,  Max(id) as latest_id, created_at as contract_created_at, updated_at as contract_updated_at
                from {$employeeContactHistoryTable}
                where start_at <= ? and start_at >= '2012-01-01 00:00:00' and deleted_at is NULL
                group by
                {$employeeContactHistoryTable}.employee_id,
                DATE_FORMAT({$employeeContactHistoryTable}.start_at, '%Y-%m')
            ) as b on  a.id = b.latest_id ) as {$newEmployeeContactHistoryTable}"), "{$employeeTable}.id", '=', "{$newEmployeeContactHistoryTable}.employee_id")
            ->leftJoin($employeeContractPointTable, "{$employeeContractPointTable}.contract_type", '=', "{$newEmployeeContactHistoryTable}.contract_type")
            ->leftJoin($teamHistoryTable, "{$teamHistoryTable}.employee_id", '=', "{$employeeTable}.id")
            ->leftJoin($teamsTable, "{$teamsTable}.id", '=', "{$teamHistoryTable}.team_id")
            ->whereRaw("{$teamsTable}.is_soft_dev = {$teamIsSoftDev}")
            ->whereRaw("{$teamsTable}.type != {$typePQA}")
            ->whereNotNull("{$employeeTable}.join_date")
            ->whereNull("{$teamHistoryTable}.deleted_at")
            ->where(function($query) use ($newEmployeeContactHistoryTable, $employeeTable, $teamHistoryTable) {
                return $query->whereRaw(DB::raw("date_format($newEmployeeContactHistoryTable.contract_created_at, '%Y-%m-%d') = curdate()"))
                    ->orwhereRaw(DB::raw("date_format($newEmployeeContactHistoryTable.contract_updated_at, '%Y-%m-%d') = curdate()"))
                    ->orwhereRaw(DB::raw("date_format($employeeTable.created_at, '%Y-%m-%d') = curdate()"))
                    ->orwhereRaw(DB::raw("date_format($employeeTable.updated_at, '%Y-%m-%d') = curdate()"))
                    ->orwhereRaw(DB::raw("date_format($teamHistoryTable.created_at, '%Y-%m-%d') = curdate()"))
                    ->orwhereRaw(DB::raw("date_format($teamHistoryTable.updated_at, '%Y-%m-%d') = curdate()"));
            });

        if ($teamId) {
            $items = $items->whereRaw(
                "{$teamHistoryTable}.team_id IN {$teamId}"
            );
        }

        return $items->orderBy('team_id', 'asc')
            ->orderBy("{$employeeTable}.id", 'asc')
            ->orderBy("{$teamHistoryTable}.id", 'asc')
            ->setBindings([$filter['monthTo'] . ' 00:00:00'])
            ->get();
    }

    /**
     * Get Effort Working Day for the first month of Join date
     *
     * @param  string $contractDate
     * @param  string $joinDate
     *
     * @return integer
     */
    private static function _getEffortWorkingDayFromStart($contractDate, $joinDate)
    {
        $stringJoinDate = $joinDate ? date("Y-m", strtotime($joinDate)) : null;
        if ($contractDate == $stringJoinDate) {
            return self::_getPercentWorkingFromSpecificDateToEndMonth($joinDate);
        }

        return 1;
    }

    private static function _isValidContractWithCurrentTeam($contractStartDate, $contractEndDate, $teamStartAt, $teamEndAt)
    {
        $contractStartDate = $contractStartDate ? date("Y-m-1", strtotime($contractStartDate)) : null;
        $contractEndDate = $contractEndDate ? date("Y-m-t", strtotime($contractEndDate)) : null;

        $teamStartAt = $teamStartAt ? date("Y-m-1", strtotime($teamStartAt)) : null;
        $teamEndAt = $teamEndAt ? date("Y-m-t", strtotime($teamEndAt)) : null;

        if ($teamStartAt && $contractEndDate && $teamStartAt > $contractEndDate) return false;
        if ($teamEndAt && $teamEndAt < $contractStartDate) return false;

        return true;
    }

    private static function _getPercentWorkingFromSpecificDateToEndMonth($specificDate)
    {
        $firstDateOfMonth = Carbon::parse($specificDate)->format('Y-m-01');
        $lastDateOfMonth = Carbon::parse($specificDate)->format('Y-m-t');
        $specificDate = Carbon::parse($specificDate)->format('Y-m-d');

        $totalWorkingDayInMonth = ManageTimeCommon::countWorkingDay($firstDateOfMonth, $lastDateOfMonth);
        if ($totalWorkingDayInMonth > 0) {
            return ManageTimeCommon::countWorkingDay($specificDate, $lastDateOfMonth) / $totalWorkingDayInMonth;
        } else {
            return 0;
        }
    }

    private static function _getDetailEmployeeEachMonth($employees, $team, $isModified)
    {
        $result = [];
        foreach ($employees as $key => $values) {
            foreach ($values as $value) {
                if ($isModified) {
                    $result[] = [
                        'month' => $key,
                        'team_id' => $team['team_id'],
                        'employee_id' => $value['employee_id'],
                        'email' => $value['email'],
                        'contract_type' => $value['contract_type'],
                        'point' => $value['point'],
                        'updated_at' => Carbon::now()
                    ];
                } else {
                    $result[] = [
                        'month' => $key,
                        'team_id' => $team['team_id'],
                        'employee_id' => $value['employee_id'],
                        'email' => $value['email'],
                        'contract_type' => $value['contract_type'],
                        'point' => $value['point'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            }
        }

        return $result;
    }

}
