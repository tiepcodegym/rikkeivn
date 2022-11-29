<?php

namespace Rikkei\Assets\Console;

use Illuminate\Support\Facades\Log;
use Rikkei\Assets\View\CronJob;
use SebastianBergmann\RecursionContext\Exception;

class AssetKernel
{
    public static function call($schedule)
    {
        //sen email when asset not confirm
        //run weekday at 16:15 PM
        try {
            $schedule->call(function () {
                CronJob::sendEmailEmpolyeeNotConfirmAsset();
            })
            ->weekdays()
            ->dailyAt('16:15');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        // send email alert asset out of date every day at 08:00
        try {
            $schedule->call(function () {
                CronJob::AlertAssetOutOfDate();
            })
            ->cron('00 08 * * *');
        } catch (Exception $ex) {

        }
    }
}
