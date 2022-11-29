<?php

namespace Rikkei\Ticket\View;

use Carbon\Carbon;

class DateTime
{
    /**
     * Team List tree
     * 
     * @return type
     */
    public static function convertDateTime($dateTime)
    {
        $today = Carbon::today();

        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $dateTime);

        if($today->year == $dt->year && $today->month == $dt->month && $today->day == $dt->day)
        {
            return $dt->format('h:i A \T\o \d\a\y');
        } 

        return $dt->diffForHumans();
    }
}
