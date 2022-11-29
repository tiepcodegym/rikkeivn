<?php

namespace Rikkei\Project\View;

use Aws\Api\Parser\Exception\ParserException;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\ManageTime\Model\LeaveDayReason;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Project\Model\EmployeeContractMember;
use Rikkei\Project\Model\EmployeeContractPoint;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeContractHistory;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\TeamList;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;

class OperationMember
{
    /**
     * flag allow number max leader of a team
     */
    const CODE_BOD = 'bod';
    const DECIMALS_POINT = 2;

    /**
     * Get Effort Working Day for the first month of Join date
     *
     * @param  string $contractDate
     * @param  string $joinDate
     *
     * @return integer
     */
    public static function getEffortWorkingDayFromStart($contractDate, $joinDate)
    {
        $stringJoinDate = $joinDate ? date("Y-m", strtotime($joinDate)) : null;
        if ($contractDate == $stringJoinDate) {
            return self::getPercentWorkingFromSpecificDateToEndMonth($joinDate);
        }

        return 1;
    }

    /**
     * Get Effort Working Day for the last month of Working
     *
     * @param  string $joinDate
     * @param  string $leaveDate
     *
     * @return integer
     */
    public static function getEffortWorkingDayUntilLeave($joinDate, $leaveDate)
    {
        $totalWorkingDayInMonth = ManageTimeCommon::countWorkingDay(date("Y-m-1", strtotime($leaveDate)), date("Y-m-t", strtotime($leaveDate)));

        $stringJoinDate = $joinDate ? date("Y-m", strtotime($joinDate)) : null;
        $stringLeaveDate = $leaveDate ? date("Y-m", strtotime($leaveDate)) : null;

        //if joining date and leave date are in the same month, calculated from join date to leave date
        if ($stringJoinDate == $stringLeaveDate) {
            return ManageTimeCommon::countWorkingDay($joinDate, $leaveDate) / $totalWorkingDayInMonth;
        }

        //else calculated from begin of the month to leave date
        return self::getPercentWorkingFromStartToSpecificDate($leaveDate);
    }


    public static function getPercentWorkingFromStartToSpecificDate($specificDate)
    {
        $firstDateOfMonth = Carbon::parse($specificDate)->format('Y-m-01');
        $lastDateOfMonth = Carbon::parse($specificDate)->format('Y-m-t');
        $specificDate = Carbon::parse($specificDate)->format('Y-m-d');

        $totalWorkingDayInMonth = ManageTimeCommon::countWorkingDay($firstDateOfMonth, $lastDateOfMonth);
        if ($totalWorkingDayInMonth > 0) {
            return ManageTimeCommon::countWorkingDay($firstDateOfMonth, $specificDate) / $totalWorkingDayInMonth;
        } else {
            return 0;
        }
    }

    public static function getPercentWorkingFromSpecificDateToEndMonth($specificDate)
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

    public static function getPercentWorkingFromDateToDate($startDate, $endDate)
    {
        $firstDateOfMonth = Carbon::parse($startDate)->format('Y-m-01');
        $lastDateOfMonth = Carbon::parse($startDate)->format('Y-m-t');

        $totalWorkingDayInMonth = ManageTimeCommon::countWorkingDay($firstDateOfMonth, $lastDateOfMonth);
        if ($totalWorkingDayInMonth > 0) {
            return ManageTimeCommon::countWorkingDay($startDate, $endDate) / $totalWorkingDayInMonth;
        } else {
            return 0;
        }
    }


    /**
     * convert row item from employee_contract_members to dimension
     *
     * @param  array $items
     *
     * @return array
     */
    public static function convertUpdatePointDimension($items)
    {
        $dimensionData = [];
        foreach ($items as $item) {
            $dimensionData[$item->employee_id . '-' . $item->team_id][$item->month] = $item->point;
        }

        return $dimensionData;
    }

    public static function isValidContractWithCurrentTeam($contractStartDate, $contractEndDate, $teamStartAt, $teamEndAt)
    {
        $contractStartDate = $contractStartDate ? date("Y-m-1", strtotime($contractStartDate)) : null;
        $contractEndDate = $contractEndDate ? date("Y-m-t", strtotime($contractEndDate)) : null;

        $teamStartAt = $teamStartAt ? date("Y-m-1", strtotime($teamStartAt)) : null;
        $teamEndAt = $teamEndAt ? date("Y-m-t", strtotime($teamEndAt)) : null;

        if ($teamStartAt && $contractEndDate && $teamStartAt > $contractEndDate) return false;
        if ($teamEndAt && $teamEndAt < $contractStartDate) return false;

        return true;
    }

    public static function countTotalWorkingDay($dateStart, $dateEnd)
    {
        $dateStart = Carbon::parse($dateStart)->format('Y-m-t');
        $dateEnd = Carbon::parse($dateEnd)->format('Y-m-d');

        return ManageTimeCommon::countWorkingDay($dateStart, $dateEnd);
    }

    public static function transformMaternityLeaveDayCollectionToDimension($collections)
    {
        $dimensionData = [];
        foreach ($collections as $collecion) {
            $leaveStart = $collecion->date_start;
            $leaveEnd = $collecion->date_end;

            $leaveStartFormatYm = Carbon::parse($leaveStart)->format('Y-m');
            $leaveEndFormatYm = Carbon::parse($leaveEnd)->format('Y-m');

            //Tính % ngày làm việc tính từ leaveStart đến hết tháng
            $percentNotWorkingDayFromStartOfLeave = self::getPercentWorkingFromSpecificDateToEndMonth($leaveStart);
            //Tính % ngày làm việc tính từ đầu tháng đến leaveEnd
            $percentNotWorkingDayUntilEndOfLeave = self::getPercentWorkingFromStartToSpecificDate($leaveEnd);

            //Nếu leaveStart và leaveEnd trong cùng 1 tháng thì tính tỉ lệ % ngày làm việc từ leaveStart -> leaveEnd
            if ($leaveStartFormatYm === $leaveEndFormatYm) {
                $percentNotWorkingDayFromStartOfLeave = self::getPercentWorkingFromDateToDate($leaveStart, $leaveEnd);
            }

            if (!isset($dimensionData[$collecion->creator_id])) {
                $dimensionData[$collecion->creator_id] = [];
            }

            $dimensionData[$collecion->creator_id][] = [
                'leave_code' => $collecion->code ? $collecion->code : LeaveDayReason::CODE_UNPAID_LEAVE,
                'leave_start' =>   $leaveStartFormatYm,
                'leave_end' =>   $leaveEndFormatYm,
                'percent_not_working_from_leave_start' => round($percentNotWorkingDayFromStartOfLeave, 2),
                'percent_not_working_until_leave_end' =>round($percentNotWorkingDayUntilEndOfLeave, 2)
            ];
        }

        return $dimensionData;
    }

    /**
     * convert row item from employee points to dimension
     *
     * @param  array $items
     *
     * @return array
     */
    public static function convertDataMemberToDimension($items)
    {
        $dimensionData = [];
        foreach ($items as $item) {
            $actualPointOfFirstMonth = null;
            $actualPointOfLastMonth = null;
            $teamStartAt = $item->team_start_at;
            $teamEndAt = $item->team_end_at;
            $teamStartAtString = $teamStartAt  ? date("Y-m", strtotime($teamStartAt)) : null;
            $contractDate = $item->contract_date ? $item->contract_date : $item->employee_contract_date;
            if ($teamStartAt && $teamEndAt && $teamStartAt > $teamEndAt) continue;
            //Nếu thời gian join team không nằm trong thời gian hợp đồng thì điểm sẽ là 0
            if (!self::isValidContractWithCurrentTeam($contractDate, $item->contract_end_date, $teamStartAt, $teamEndAt)) {
                $item->point = 0;
            }
            $joinDate = $item->join_date;
            if ($teamStartAt) {
                $joinDate = $teamStartAt;
            }
            $actualPointOfFirstMonth = $item->point * self::getEffortWorkingDayFromStart($teamStartAtString, $joinDate);
            $leaveDate = $item->leave_date ? $item->leave_date : $item->deleted_at;
            if ($teamEndAt) {
                $leaveDate = $leaveDate ? min($teamEndAt, $leaveDate) : $teamEndAt;
            }
            if ($leaveDate) {
                $actualPointOfLastMonth = $item->point * self::getEffortWorkingDayUntilLeave($joinDate, $leaveDate);
                if ($joinDate > $leaveDate) continue;
            }
            $startHavingPoint = $teamStartAt ? $teamStartAt : $joinDate;
            $startHavingPoint = $startHavingPoint ? date("Y-m", strtotime($startHavingPoint)) : $contractDate;
            $dimensionData[$item->team_id . '-' . $item->id][$startHavingPoint][$contractDate] = [
                'name' => $item->name,
                'email' => $item->email,
                'account_name' => preg_replace('/@.*/', '', $item->email),
                'employee_code' => $item->employee_code,
                'employee_id' => $item->id,
                'actual_point_first_month' => number_format((float)$actualPointOfFirstMonth, self::DECIMALS_POINT),
                'actual_point_last_month' => number_format((float)$actualPointOfLastMonth, self::DECIMALS_POINT),
                'contract_type' => $item->contract_type,
                'point' => $item->point,
                'join_date' => $startHavingPoint ? $startHavingPoint : null,
                'leave_date' => $leaveDate ? date("Y-m", strtotime($leaveDate)) : null,
                'team_name' => $item->team_name,
            ];
        }

        return $dimensionData;
    }

    public static function getMaternityLeaveDayCollection($filter)
    {
        $teamMemberTable = EmployeeTeamHistory::getTableName();
        $teamIsSoftDev = Team::IS_SOFT_DEVELOPMENT;
        $leaveDayReasonTable = LeaveDayReason::getTableName();
        $leaveDayRegisterTable = LeaveDayRegister::getTableName();
        $teamsTable = Team::getTableName();
        $teamId = false;

        if ($filter['team_id']) {
            $teamId = $filter['team_id'];
            $team = Team::getTeamPath($withTrashed = true);
            if (isset($team[$teamId]['child'])) { //get all team childs except BOD
                $teamId = '(' . $teamId . ', ' . implode(", ", $team[$teamId]['child']) . ')';
            } else {
                $teamId = '(' . $teamId . ')';
            }
        }

        $collections = DB::table($leaveDayRegisterTable)->select(
                "{$leaveDayRegisterTable}.date_start",
                "{$leaveDayRegisterTable}.date_end",
                "{$leaveDayRegisterTable}.creator_id",
                "{$leaveDayReasonTable}.code"
            )
            ->join($leaveDayReasonTable, "{$leaveDayRegisterTable}.reason_id", '=', "{$leaveDayReasonTable}.id")
            ->join($teamMemberTable, "{$teamMemberTable}.employee_id", '=', "{$leaveDayRegisterTable}.creator_id")
            ->join($teamsTable, "{$teamsTable}.id", '=', "{$teamMemberTable}.team_id")
            ->where([
                ["{$leaveDayRegisterTable}.status", LeaveDayRegister::STATUS_APPROVED],
            ])
            ->where(function ($q) {
                $leaveDayReasonTable = LeaveDayReason::getTableName();
                //Hoặc là nghỉ thai sản
                $q->where("{$leaveDayReasonTable}.code", LeaveDayReason::CODE_MATERNITY)
                    ->orWhere(function ($q) {
                        $leaveDayReasonTable = LeaveDayReason::getTableName();
                        $leaveDayRegisterTable = LeaveDayRegister::getTableName();
                        //Hoặc là nghỉ không lương nhưng số ngày nghỉ > 5 ngày
                        $q->where("{$leaveDayRegisterTable}.number_days_off", '>', 5)
                            ->where("{$leaveDayReasonTable}.salary_rate", 0);
                    });
            })
            ->whereDate("{$leaveDayRegisterTable}.date_start", '<=', $filter['monthTo'])
            ->whereDate("{$leaveDayRegisterTable}.date_end", '>=', $filter['monthFrom'])
            ->where("{$teamsTable}.is_soft_dev" , $teamIsSoftDev)
            ->whereNull("{$leaveDayRegisterTable}.deleted_at")
            ->groupBy(['date_start', 'date_end', 'creator_id', 'code']);

        if ($teamId) {
            $collections = $collections->whereRaw(
                "{$teamMemberTable}.team_id IN {$teamId}"
            );
        }

        return $collections->get();
    }
    /**
     * get data by month
     *
     * @param $filter
     *
     * @return object
     */
    public static function getDataByMonth($filter)
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
        $teamMemberTable = EmployeeTeamHistory::getTableName();
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
                "{$teamMemberTable}.team_id",
                "{$teamMemberTable}.start_at as team_start_at",
                "{$teamMemberTable}.end_at as team_end_at",
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
                    where start_at <= ? and start_at >= '2012-01-01 00:00:00' and deleted_at is  NULL
                    group by
                    {$employeeContactHistoryTable}.employee_id,
                    DATE_FORMAT({$employeeContactHistoryTable}.start_at, '%Y-%m')
                ) as b on  a.id = b.latest_id ) as {$newEmployeeContactHistoryTable} "), "{$employeeTable}.id", '=', "{$newEmployeeContactHistoryTable}.employee_id")
            ->leftJoin($employeeContractPointTable, "{$employeeContractPointTable}.contract_type", '=', "{$newEmployeeContactHistoryTable}.contract_type")
            ->leftJoin($teamMemberTable, "{$teamMemberTable}.employee_id", '=', "{$employeeTable}.id")
            ->leftJoin($teamsTable, "{$teamsTable}.id", '=', "{$teamMemberTable}.team_id")
            ->whereRaw("{$teamsTable}.is_soft_dev = {$teamIsSoftDev}")
            ->whereRaw("{$teamsTable}.type != {$typePQA}")
//            ->whereRaw("{$employeeTable}.id = 4060")
            ->whereNotNull("{$employeeTable}.join_date")
            ->whereNull("{$employeeTable}.deleted_at")
            ->whereNull("{$teamMemberTable}.deleted_at");

        if ($teamId) {
            $items = $items->whereRaw(
                "{$teamMemberTable}.team_id IN {$teamId}"
            );
        }

        return $items->orderBy('team_id', 'asc')
            ->orderBy("id", 'asc')
            ->orderBy("{$teamMemberTable}.id", 'asc')
            ->setBindings([$filter['monthTo'] . ' 00:00:00'])
            ->get();
    }

    /**
     * check valid contract data employee
     *
     * @param $comparedMonth, $employeePoints
     *
     * @return boolean
     */
    public static function checkValidContractDateEmployee($comparedMonth, $employeePoints)
    {
        $firstStartMonthOfEmployee = null;
        $flag = false;
        foreach ($employeePoints as $month => $value) {
           if ($comparedMonth >= $month) $flag = true;
        }

        return $flag;
    }

    /**
     * get string calculated month
     *
     * @param $stringMonth, $monthIndex
     *
     * @return array
     */
    public static function getStringCalculatedMonth($stringMonth, $monthIndex)
    {

        return Carbon::parse($stringMonth)->addMonths($monthIndex)->format('Y-m');
    }

    /**
     * get data mapping month
     *
     * @param $employeePointsGridDatas, $employeeId, $month
     *
     * @return array
     */
    public static function getDataMappingMonth(&$employeePointsGridDatas, $employeeId, $index, $month)
    {
        if (!array_key_exists($month, $employeePointsGridDatas[$employeeId][$index])) {
            $stringPreviousMonth = self::getStringCalculatedMonth($month, -1);
            $employeePointsGridDatas[$employeeId][$index][$month] = self::getDataMappingMonth($employeePointsGridDatas, $employeeId, $index, $stringPreviousMonth);
            $data = $employeePointsGridDatas[$employeeId][$index][$month];
            if ($data && $data['leave_date'] && $month > $data['leave_date']) {
                return null;
            }
        }

        return $employeePointsGridDatas[$employeeId][$index][$month];
    }

    /**
     * get total point each month
     *
     * @param $filter, $employeePointsGridDatas, $employeeUpdatedPointsGridDatas
     *
     * @return array
     */
    public static function getTotalPointEachMonth($filter, $employeePointsGridDatas)
    {
        $monthFrom = $filter['monthFrom'];
        $monthTo = $filter['monthTo'];
        $monthFrom = Carbon::parse($monthFrom);
        $monthTo = Carbon::parse($monthTo);
        $interval = DateInterval::createFromDateString('1 month');
        $periods   = new DatePeriod($monthFrom, $interval, $monthTo);
        $results = [];

        foreach ($periods as $period) {
            $totalPoint = 0;
            $scopeMonthFromFormat = $period->format('Y-m');
            foreach ($employeePointsGridDatas as $employeeId => $employeePoints) {
                foreach ($employeePoints as $index => $currentTimeline){
                    if (!self::checkValidContractDateEmployee($scopeMonthFromFormat, $currentTimeline)) {
                        continue;
                    }

                    $employeeInforMappingMonth = self::getDataMappingMonth($employeePointsGridDatas, $employeeId, $index, $scopeMonthFromFormat);

                    if ($employeeInforMappingMonth) {
                        $point = $employeeInforMappingMonth['point'];
                        if ($employeeInforMappingMonth['join_date'] > $scopeMonthFromFormat) continue;
                        if ($employeeInforMappingMonth['join_date'] === $scopeMonthFromFormat) {
                            $point = $employeeInforMappingMonth['actual_point_first_month'];
                        }
                        if ($employeeInforMappingMonth['leave_date'] === $scopeMonthFromFormat) {
                            $point = $employeeInforMappingMonth['actual_point_last_month'];
                        }

                        $totalPoint += $point;
                    }
                }

            }
            $results[$scopeMonthFromFormat] = $totalPoint;
        }

        return $results;
    }

    /**
     * get all rows from employee_contract_members
     *
     * @return object
     */
    public static function getDataPointMember($request)
    {
        $teams = [];
        if ($request->teamId) {
            $teamId = $request->teamId;
            $teamModel = Team::find($teamId);
            $teams = [$teamId];
            $team = Team::getTeamPath($withTrashed = true);
            if (isset($team[$teamId]['child'])) { //get all team childs except BOD
                $teams =  $team[$teamId]['child'];
                $teams[] = $teamId;
            }
        }
        if ($teams) {
            return EmployeeContractMember::whereIn('team_id', $teams)->get();
        }

        return EmployeeContractMember::all();
    }
}
