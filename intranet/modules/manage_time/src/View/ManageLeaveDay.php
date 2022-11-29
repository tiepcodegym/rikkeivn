<?php

namespace Rikkei\ManageTime\View;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\Model\LeaveDay;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Permission as PermissionView;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Rikkei\Core\Model\CoreConfigData;

class ManageLeaveDay
{
    public static function totalDay($dayTransfer, $dayCurrent, $daySeniority, $dayOT)
    {
        $sum = $dayTransfer + $dayCurrent + $daySeniority;
        return $sum <= ManageTimeConst::MAX_DAY ? $sum + $dayOT : ManageTimeConst::MAX_DAY + $dayOT;
    }

    public static function maxTransfer($dayLastYear)
    {
        return $dayLastYear <= ManageTimeConst::MAX_TRANSFER ? $dayLastYear : ManageTimeConst::MAX_TRANSFER;
    }

    /**
     * insert and update data into table leave day
     *
     * @param  [array] $data
     */
    public static function insertUpdateData($data)
    {
        // Insert
        if (isset($data['insert'])) {
            LeaveDay::insert($data['insert']);
        }

        if (!isset($data['update'])) {
            return null;
        }
        // Update
        $now = Carbon::now()->format('Y-m-d H:i:s');
       	$tblLeaveDay = LeaveDay::getTableName();
       	$query = 'UPDATE `' . $tblLeaveDay . '` AS tbl_leave_day SET `updated_at` = "'.$now.'", `deleted_at` = null, `day_ot` = CASE `employee_id` ';
       	$employeeIdsString = '';
       	foreach ($data['update'] as $employeeId => $item) {
            $employeeIdsString .= $employeeId . ', ';
            $query .= 'WHEN '.$employeeId.' THEN "'.$item['day_ot'].'" ';
       	}
        $query .= 'END ' . 'WHERE `employee_id` IN ('.substr($employeeIdsString, 0, -2).')';
        DB::update($query);
    }

    /**
     * calculation again time register leave
     * @param  [string | carbon] $startDate
     * @param  [string | carbon] $endDate
     * @param  [string | null] $teamCodePrefix
     * @param  [collection|null] $employee
     * @return [float]
     */
    public static function getTimeLeaveDay($startDate, $endDate, $employee = null, $teamCodePrefix = null, $calculateFullDay = false)
    {
        if (!$employee) {
            $employee = PermissionView::getInstance()->getEmployee();
        }

        if (!$startDate instanceof Carbon) {
            $startDate = Carbon::parse($startDate);
        }
        if (!$endDate instanceof Carbon) {
            $endDate = Carbon::parse($endDate);
        }

        if (!$teamCodePrefix) {
            $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($employee);
        }

        $workTimeStart = Employee::getTimeWorkEmployeeDate($startDate->format('Y-m-d'), $employee, $teamCodePrefix);
        $workTimeEnd = Employee::getTimeWorkEmployeeDate($endDate->format('Y-m-d'), $employee, $teamCodePrefix);

        $time = 0;
        $dateStart = clone $startDate;
        $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
        $regTimeSystem = CoreConfigData::checkBranchRegister($employee, $teamCodePrefix); // branch register time 1/4

        while (strtotime($startDate->toDateString()) <= strtotime($endDate->toDateString())) {
            $isWeekend = ManageTimeCommon::isWeekend($startDate, $compensationDays);
            $isHoliday = ManageTimeCommon::isHoliday($startDate, null, null, $teamCodePrefix);

            //Nếu là ko phải ngày nghỉ hoặc trường hợp tính full ngày (vd: nghỉ thai sản)
            if ((!$isWeekend && !$isHoliday) || $calculateFullDay) {
                if ($teamCodePrefix != Team::CODE_PREFIX_JP && $regTimeSystem) {
                    if ($dateStart->toDateString() == $startDate->toDateString() && $startDate->toDateString() == $endDate->toDateString()) {
                        $time = $time + ManageTimeView::getDiffTimesRegister($startDate->hour, $endDate->hour, $startDate->minute, $endDate->minute, $workTimeStart);
                    } elseif ($dateStart->toDateString() == $startDate->toDateString() && $startDate->toDateString() < $endDate->toDateString()) {
                        $time = $time + ManageTimeView::getDiffTimesRegister($startDate->hour, $workTimeStart["afternoonOutSetting"]->hour, $startDate->minute, $workTimeStart["afternoonOutSetting"]->minute, $workTimeStart);
                    } elseif ($dateStart->toDateString() < $startDate->toDateString() && $startDate->toDateString() < $endDate->toDateString()) {
                        $time = $time + 1;
                    } else {
                        $time = $time + ManageTimeView::getDiffTimesRegister($workTimeEnd["morningInSetting"]->hour, $endDate->hour, $workTimeEnd["morningInSetting"]->minute, $endDate->minute, $workTimeEnd);
                    }
                } else {
                    if ($dateStart->toDateString() == $startDate->toDateString() && $startDate->toDateString() == $endDate->toDateString()) {
                        if ($startDate->hour == $workTimeStart["morningInSetting"]->hour && $endDate->hour == $workTimeEnd["afternoonOutSetting"]->hour) {
                            $time = $time + 1;
                        } else {
                            $time = $time + 0.5;
                        }
                    } elseif ($dateStart->toDateString() == $startDate->toDateString() && $startDate->toDateString() < $endDate->toDateString()) {
                        if ($startDate->hour > $workTimeStart["morningInSetting"]->hour) {
                            $time = $time + 0.5;
                        } else {
                            $time = $time + 1;
                        }
                    } elseif ($dateStart->toDateString() < $startDate->toDateString() && $startDate->toDateString() < $endDate->toDateString()) {
                        $time = $time + 1;
                    } else {
                        if ($endDate->hour == $workTimeEnd["afternoonOutSetting"]->hour) {
                            $time = $time + 1;
                        } else {
                            $time = $time + 0.5;
                        }
                    }
                }
            }
            $startDate->addDay();
        }
        return $time;
    }
}
