<?php

namespace Rikkei\Core\View;

use Carbon\Carbon;

class TimeHelper
{
    /**
     * custom add month
     * @param Carbon $time
     * @param int $n
     * @return Carbon
     */
    public static function addMonth($time, $n)
    {
        $nMonthLater = Carbon::create($time->year, $time->month, 1)->addMonth($n);
        if ($time->day > $nMonthLater->daysInMonth) {
            return $nMonthLater->addMonth(1);
        }
        return Carbon::parse($time)->addMonth($n);
    }
}
