<?php
namespace Rikkei\ManageTime\Console;

use Exception;
use Illuminate\Support\Facades\Log;
use Rikkei\Event\View\TimekeepingHelper;
use Rikkei\ManageTime\Model\LeaveDay;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class TimeKernel
{
    public static function call($schedule)
    {
        // Get data timekeeping file to timekeeping, after upload file
        try {
            $schedule->call(function () {
                Log::info('Start cron job get up file timekeeping');
                ManageTimeView::doUpdateTimekeeping();
                Log::info('End cron job get up file timekeeping');
            })->cron('* * * * *');
        } catch (Exception $ex) {
            Log::info($ex);
        }
        // Cập nhật dữ liệu liên quan từ đơn
        try {
            $schedule->call(function () {
                $files = Storage::allfiles('timekeeping_upload_related');
                $countFiles = count($files);
                Log::info('start cron ralated employee');
                if($countFiles < 20){
                    ViewTimeKeeping::cronRelatedPerson();
                    Log::info('Run cron ralated employee');
                }
                Log::info('End cron ralated employee');
                Log::info('Start cron job get relate module');
                ManageTimeView::doUpdateRelated();
                Log::info('End cron job get relate module');
                
                $objViewTK = new ViewTimeKeeping();
                // update related timekeeping
                $objViewTK->wKTUpdateRelated();
            })->cron('* * * * *');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * Cron job update leave day when employee tranfer from trial to offcial
         */
        try {
            $schedule->call(function () {
                Log::info('Start cron job update leave trial to official');
                LeaveDay::cronJobUpdateLeaveDayTrialToOffcial();
                Log::info('End cron job update leave trial to official');
            })->dailyAt('01:25');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * Cron job update leave day daily
         */
        
        try {
            $schedule->command('leaveday:daily')->dailyAt('02:00');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * Cron job update leave day monthy
         */
        try {
            $schedule->command('leaveday:monthly')->monthlyOn(01, '01:30');
        } catch (Exception $ex) {
            Log::info($ex);
        }


        /**
         * Cron job update leave day yearly
         */
        try {
            $schedule->command('leaveday:yearly')->cron('15 01 01 01 *');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * Cron job update leave day seniority
         */
        try {
            $schedule->command('leaveday:seniority')->dailyAt('02:30');
        } catch (Exception $ex) {
            Log::info($ex);
        }


        // ananyze timesheet to fines
        try {
            $schedule->call(function () {
                TimekeepingHelper::getInstance()->tsToFines();
            })->cron('* * * * *');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //baseline leave day run every 1st month
        try {
            $schedule->call(function () {
                \Rikkei\ManageTime\Model\LeaveDayBaseline::cronSaveDays();
            })->cron('01 00 01 * *');
        } catch (Exception $ex) {
            Log::info($ex);
        }


        // Delete employee on leave daily
        // Run first day of monthly at 00:15 AM
        // Sau khi nghỉ vẫn cần show ngày phép => comment command này
        //$schedule->command('leaveday:remove')->monthlyOn(1, '0:15');

        // create timekeeping table 01 monthly
        $schedule->command('tktable:create')->cron('10 01 01 * *');

        // run every day calculate time working of employee - tinh công hàng ngày
        //$schedule->command('timeworkingday:calculate')->cron('30 01 * * *');

        // kiểm tra nhân viên mới và update
        $schedule->command('timekeeping:check')->cron('30 22 * * *');

        // tổng hợp công
        // $schedule->command('tkaggregates:calculate');

        try {
            // Update time in/out in to table timekeeping
            $schedule->command('timekeeping:update_from_csv')->cron('* * * * *');

            // Tạo các file chạy tính toán công, sau khi update giờ time in/out
            $objViewTK = new ViewTimeKeeping();
            $schedule->call(function () use ($objViewTK) {
                $objViewTK->cronExportTimeInOut();
            })->cron('09 01 * * *');
            $schedule->call(function () use ($objViewTK) {
                $objViewTK->cronTimekeepingRelated();
            })->cron('09 02 * * *');
            $schedule->call(function () use ($objViewTK) {
                $objViewTK->cronTimekeepingRelatedSalaryRate();
            })->cron('0 4 * * *');
            $schedule->call(function () use ($objViewTK) {
                $objViewTK->cronTimekeepingAggregateSalaryRate();
            })->cron('30 4 * * *');

            // Tính thời gian làm thừa thiếu
            $year = Carbon::now()->subDay()->year;
            $month = Carbon::now()->subDay()->month;
            $dateFrom = Carbon::now()->subDay()->format('Y-m-d');
            $dateTo = Carbon::now()->format('Y-m-d');
            $schedule->command("timekeeping:timelack {$year} {$month} {$dateFrom} {$dateTo}")->cron('0 5 * * *');

            // Mail thông báo thiếu công
            // Chỉ thông báo vào các thứ 2 -> 6
            // Thứ 2 thông báo cho thứ 6 tuần trước
//            $dayOfTheWeek = Carbon::now()->dayOfWeek;
//            if (!in_array($dayOfTheWeek, [0, 6])) {
//                if ($dayOfTheWeek == 1) {
//                    $year = Carbon::now()->subDays(3)->year;
//                    $month = Carbon::now()->subDays(3)->month;
//                    $date = Carbon::now()->subDays(3)->format('Y-m-d');
//                } else {
//                    $year = Carbon::now()->subDay()->year;
//                    $month = Carbon::now()->subDay()->month;
//                    $date = Carbon::now()->subDay()->format('Y-m-d');
//                }
//                $schedule->command("timekeeping:notibsc {$year} {$month} {$date}")->cron('0 10 * * *');
//            }
            
        } catch (Exception $ex) {
            Log::info($ex);
        }

        $schedule->call(function () {
            $objView = new ManageTimeView();
            // send email timekeeping systena
            $objView->sendEmailSystena();
            
            // update aggregate salary rate and send email
            $objView->updateSalaryRateAgregateCron();

        })->cron('*/5 * * * *');

        // kiểm tra ngày lễ của chi nhánh khi bị update
        $schedule->command('timekeeping:check_holiday')->cron('30 02 * * *');

        // kiểm tra ngày làm bù của chi nhánh khi bị update
        $schedule->command('timekeeping:check_compensatory')->cron('40 02 * * *');

        //update sign timekeeping
        try {
            $objSign = new \Rikkei\ManageTime\Console\Commands\SetSignTimekeeping();
            $schedule->call(function () use ($objSign) {
                $objSign->SetSignTimekeepingCron();
            })->cron('*/3 * * * *');

        } catch (Exception $ex) {
            Log::info($ex);
        }
    }
}
