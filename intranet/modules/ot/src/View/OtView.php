<?php

namespace Rikkei\Ot\View;

use Carbon\Carbon;
use Rikkei\ManageTime\View\ManageTimeCommon;

class OtView
{
    /**
     * Get time register default
     *
     * @param array $timeSettingOfEmp working time of employee
     *
     * @return array
     */
    public static function getTimeRegisterDefault($timeSettingOfEmp)
    {
        $startDate = Carbon::today();
        $endDate = clone $startDate;

        if (ManageTimeCommon::isWeekendOrHoliday($startDate)) {
            $startDate->hour = $timeSettingOfEmp['morningInSetting']['hour'];
            $startDate->minute = $timeSettingOfEmp['morningInSetting']['minute'];
            $endDate->hour = $timeSettingOfEmp['afternoonOutSetting']['hour'];
            $endDate->minute = $timeSettingOfEmp['afternoonOutSetting']['minute'];
        } else {
            $startDate->hour = $timeSettingOfEmp['afternoonOutSetting']['hour'] + 1;
            $startDate->minute = $timeSettingOfEmp['afternoonOutSetting']['minute'];
            $endDate->hour = 22;
            $endDate->minute = 0;
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
