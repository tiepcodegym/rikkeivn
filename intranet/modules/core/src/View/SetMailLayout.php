<?php

namespace Rikkei\Core\View;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreConfigData;


class SetMailLayout
{
    /*
     * config mail layout for special occasion.
     * @return void.
     */
    public static function setDataEmailLayout()
    {
        $curDate = Carbon::now();
        $curMonth = $curDate->format('m');
        $curYear = $curDate->format('Y');
        $item = [];

        if ($curMonth == '12') {
            $merryChristmasStart = Carbon::create($curYear, 12, 25, 0)->subDays(14);
            $merryChristmasEnd = Carbon::create($curYear, 12, 25, 0);
            $newYearDateStart = Carbon::create($curYear, 12, 26, 0);
            $newYearDateEnd = Carbon::create($curYear+1, 2, 1, 0)->endOfMonth();

            // set merry Christmas day.
            if ($curDate->between($merryChristmasStart, $merryChristmasEnd)) {
                $item = ['core.email.layout'=> '5'];
            }

            // set new year.
            if ($curDate->between($newYearDateStart, $newYearDateEnd)) {
                $item = ['core.email.layout'=> '1'];
            }
            if (!$item) {
                $item = ['core.email.layout'=> '0'];
            }
        } elseif ($curMonth == '1' || $curMonth == '2') {
            $item = ['core.email.layout'=> '1'];
        } elseif ($curMonth == '3' || $curMonth == '4') {
            $summerDateStart = Carbon::create($curYear, 4, 7, 0);
            $summerDateEnd = Carbon::create($curYear, 4, 15, 0)->addDays(14);
            $birthDayCompanyStart = Carbon::create($curYear, 4, 6, 0)->subDays(14);
            $birthDayCompanyEnd = Carbon::create($curYear, 4, 6, 0);

            // set birthday company.
            if ($curDate->between($birthDayCompanyStart, $birthDayCompanyEnd)) {
                $item = ['core.email.layout'=> '3'];
            }

            // set summery day.
            if ($curDate->between($summerDateStart, $summerDateEnd)) {
                $item = ['core.email.layout'=> '4'];
            }
            if (!$item) {
                $item = ['core.email.layout'=> '0'];
            }
        } else {
            $item = ['core.email.layout'=> '0'];
        }
        foreach ($item as $key => $value) {
            $layoutEmail = CoreConfigData::getItem($key);
            if (!$layoutEmail) {
                $layoutEmail = new CoreConfigData();
                $layoutEmail->key = $key;
            }
            $layoutEmail->value = $value;
            $layoutEmail->save();
        }
    }
}
