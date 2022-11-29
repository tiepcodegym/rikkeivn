<?php

namespace Rikkei\Resource\Console;

class ResourceKernel
{
    public static function call($schedule)
    {
        // notify HR update interview result
        $schedule->command('interview-result:remind-update')->dailyAt('08:00');
        // notify HR send mail offering or fail interview
        $schedule->command('interview-result:remind-send-email')->dailyAt('08:00');
        // notify HR follow birthday of interested candidate
        $schedule->command('candidate:follow-birthday')->dailyAt('08:00');
        // notify HR follow interested candidate
        $schedule->command('candidate:follow-special')->dailyAt('08:00');
    }
}
