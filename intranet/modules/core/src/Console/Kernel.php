<?php

namespace Rikkei\Core\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Rikkei\Api\Helper\HrmFo;
use Rikkei\Education\Http\Services\SettingTemplateMailService;
use Rikkei\Education\View\EducationRemindCronJob;
use Rikkei\Project\Model\ProjPointReport;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Project\Model\ProjPointBaseline;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Project\View\EmployeePointCronJob;
use Rikkei\Project\View\OperationCronJob;
use Rikkei\Sales\Model\CssMail;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\ProjPointFlat;
use Illuminate\Support\Facades\Log;
use Rikkei\Test\Models\TestTemp;
use Rikkei\Project\Model\MeMailAlert;
use Rikkei\SlideShow\View\RunBgSlide;
use Rikkei\Project\Model\Project;
use Rikkei\Resource\Model\Dashboard;
use Rikkei\Ticket\View\CronJob as TicketCronjob;
use Rikkei\Music\View\CronJob as MusicCronjob;
use Rikkei\Project\View\View as ProjectCronjob;
use Rikkei\Event\View\MailEmployee;
use Rikkei\Project\Model\MonthlyReport;
use Rikkei\Project\Model\TeamEffort;
use Rikkei\Project\View\CheckDeadlineMail;
use Illuminate\Support\Facades\Artisan;
use Rikkei\Project\View\CheckWarningTask;
use Rikkei\Statistic\Helpers\STProjHelper;
use Rikkei\Event\View\TimekeepingHelper;
use Rikkei\Project\View\CheckEndDateProjsOnWeek;
use Rikkei\Team\View\SendMailCron;
use Rikkei\Project\View\CheckTaskCF;
use Rikkei\Assets\Console\AssetKernel;
use Rikkei\Contract\Model\ContractQueue;
use Rikkei\Team\Model\Employee;
use Rikkei\Resource\Console\ResourceKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Rikkei\Core\Console\Commands\Inspire::class,
        \Rikkei\Statistic\Console\Commands\GitlabCmd::class,
        \Rikkei\Team\Console\Commands\EmplPass::class,
        \Rikkei\Core\Console\Commands\UpdateAvatar::class,
        \Rikkei\Core\Console\Commands\StatsTurnOff::class,
        \Rikkei\Core\Console\Commands\StatsBaseline::class,
        \Rikkei\Core\Console\Commands\UpdateLeaveDayDaily::class,
        \Rikkei\Core\Console\Commands\UpdateLeaveDayMonthly::class,
        \Rikkei\Core\Console\Commands\UpdateLeaveDaySeniority::class,
        \Rikkei\Core\Console\Commands\UpdateLeaveDayYearly::class,
        \Rikkei\Core\Console\Commands\SendEmailRemindCertificate::class,
        \Rikkei\ManageTime\Console\Commands\EmployeesLeaveDay::class,
        \Rikkei\Core\Console\Commands\CheckEmployeeBirthdayInDay::class,
        \Rikkei\FinesMoney\Console\Commands\FinesMoneyLasteAfter::class,
//        \Rikkei\Core\Console\Commands\InitCacheRole::class,
        \Rikkei\Resource\Console\Commands\RemindUpdateInterviewResults::class,
        \Rikkei\Resource\Console\Commands\RemindSendEmailAfterHasInterviewResults::class,
        \Rikkei\Resource\Console\Commands\FollowBirthdayCandidate::class,
        \Rikkei\Resource\Console\Commands\FollowSpecialCandidate::class,
        \Rikkei\ManageTime\Console\Commands\CreateTableTimekeeping::class,
        \Rikkei\ManageTime\Console\Commands\CalculateTimeWokingDay::class,
        \Rikkei\ManageTime\Console\Commands\CalculateTimeWokingDayAll::class,
        \Rikkei\ManageTime\Console\Commands\CacheMenu::class,
        \Rikkei\ManageTime\Console\Commands\CalculateTimekeepingAggreagtes::class,
        \Rikkei\ManageTime\Console\Commands\CheckHolidayTimekeeping::class,
        \Rikkei\ManageTime\Console\Commands\CheckCompensatoryTimekeeping::class,
        \Rikkei\ManageTime\Console\Commands\SetSignTimekeeping::class,
        \Rikkei\ManageTime\Console\Commands\UpdateTimeKeepingFromCSV::class,
        \Rikkei\ManageTime\Console\Commands\CheckEmpTimekeeping::class,
        \Rikkei\Project\Console\Commands\BlockAccountGitRedmine::class,
        \Rikkei\Project\Console\Commands\ChangeUsernameGit::class,
        \Rikkei\Project\Console\Commands\ChangeUsernameRedmine::class,
		\Rikkei\Project\Console\Commands\SyncPoIdToCRM::class,
        \Rikkei\Assets\Console\Commands\SynchronizedReportAssets::class,
        \Rikkei\ManageTime\Console\Commands\ConvertTimeRegisterHcm::class,
        \Rikkei\Assets\Console\Commands\ReturnCustomer::class,
        \Rikkei\Core\Console\Commands\GetAvatarEmplyee::class,
        \Rikkei\Core\Console\Commands\JapanLeaveDay::class,
        \Rikkei\Sales\Console\Commands\SetCompany::class,
        \Rikkei\Sales\Console\Commands\SendMailToPQA::class,
        \Rikkei\ManageTime\Console\Commands\CalculateTimekeepingByWorkingTime::class,
        \Rikkei\ManageTime\Console\Commands\ExportTimeInOutCsv::class,
        \Rikkei\Event\Console\Commands\SendMailBirthday::class,
        \Rikkei\Event\Console\Commands\SendMailHRday::class,
        \Rikkei\Sales\Console\Commands\CustomerContactCRM::class,
        \Rikkei\ManageTime\Console\Commands\TimeLack::class,
        \Rikkei\ManageTime\Console\Commands\NotiBsc::class,
        \Rikkei\ManageTime\Console\Commands\ExportTimeInOut::class,
        \Rikkei\ManageTime\Console\Commands\RemindEmpWorkShortOfTime::class,
        \Rikkei\Project\Console\Commands\MeSendMailNotify::class,
        \Rikkei\Project\Console\Commands\NcRemindDuedate::class,
        \Rikkei\Sales\Console\Commands\RemindCssTimeReplyOver::class,
        \Rikkei\Core\Console\Commands\MenuSaveCache::class,
        \Rikkei\News\Console\Commands\PostEnable::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // manage time module
        \Rikkei\ManageTime\Console\TimeKernel::call($schedule);

        //assets module
        AssetKernel::call($schedule);

        // resource module
        ResourceKernel::call($schedule);

        try {
            // crontab project report
            $reportTime = CoreConfigData::getProjectReportYesTime();
            if ($reportTime) {
                //report ontime
                $reportTimeDayOfWeek = $reportTime[0];
                $runBeforeTenMinute = strtotime($reportTime[1]) - 10 * 60;
                $timeReportTimeHMS = date('H:i:s', $runBeforeTenMinute);
                list($reportTimeHour, $reportTimeMinute, $reportTimeSecond) = explode(':', $timeReportTimeHMS);
                $stringCronTab = "{$reportTimeMinute} {$reportTimeHour} * * {$reportTimeDayOfWeek}";
                $schedule->call(function () {
                    ProjPointReport::reportAllProject(false);
                    ProjPointReport::checkReportInWeek();
                })->cron($stringCronTab);

                //crontab project
                // run every day, 23:30:00
                $stringCronTab = "30 23 * * *";
                $schedule->call(function () {
                    ProjPointReport::reportAllProject();
                })->cron($stringCronTab);
            }

            //me activities alert
            $projBaselineDates = \Rikkei\Project\View\MeView::getBaselineWeekDates();
            if ($projBaselineDates) {
                // run add 14:00 on start of week has baseline date
                $stringCronTab = '00 14 ' . $projBaselineDates[0] . ' * *';
                $schedule->call(function () {
                    \Rikkei\Project\Model\MeActivity::mailAlert();
                })->cron($stringCronTab);
                // schedule me & reward
                $schedule->call(function () {
                    MeMailAlert::alertSchedule();
                })->cron($stringCronTab);
            }
        } catch (Exception $ex) {
            Log::info($ex);
        }

        // update personal email for employees from candidates
        try {
            $schedule->call(function () {
                Log::info('start update email');
                Employee::updatePersonalEmail();
                Log::info('end update email');
            })->cron('45 9 16 8 5');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //crontab project baseline
        // run every day, 23:30:00
        $stringCronTab = "30 23 * * *";
        try {
            $schedule->call(function () {
                ProjPointBaseline::baselineAllProject();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //crontab reset notes of project points + send notice close project expired 30days
        //run every week on monday at 06:00
        $stringCronTab = "0 6 * * 1";
        try {
            $schedule->call(function () {
                ProjectPoint::resetAllNote();
                Project::noticeCloseProjectMail();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        // project flat data
        // run every day, 01:30:00
        $stringCronTab = "30 1 * * *";
        try {
            $schedule->call(function () {
                ProjPointFlat::flatAllProject();

                //cron job update candidate leaved off
                \Rikkei\Resource\Model\Candidate::cronUpdateLeavedOff();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //project month reward
        //$run every day, 08:00:00
        $stringCronTab = "00 08 * * *";
        try {
            $schedule->call(function () {
                ProjectCronjob::sendEmailMonthReward();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::error($ex);
        }
        // project report check time
        // run every day, 2:30:00
        $stringCronTab = "30 2 * * *";
        try {
            $schedule->call(function () {
                ProjPointReport::checkReportInWeek();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * Send mail to PQA khi customer don't make CSS
         */
        // $schedule->command('css:send_mail_pqa')->weekly()->mondays()->at('08:00'); //Cancel
        $schedule->command('css:remind_time_reply')->weekly()->mondays()->at('08:00');


        //crontab destroy raise
        // run 15h friday
        $stringCronTab = "* 15 * * 5";
        try {
            $schedule->call(function () {
                ProjectPoint::destroyRaiseAll();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * send email music
         */
        $stringCronTab = "* * * * 1-5 *";
        try {
            $schedule->call(function () {
                MusicCronjob::sendNoti();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }


        //crontab sent email queue, education remind
        // run every 5m
        $stringCronTab = "* * * * *";
        try {
            $schedule->call(function () {
                EducationRemindCronJob::sendMailRemind();
                EmailQueue::sentAll();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /* $stringCronTab = "* * * * *";
          try {
          $schedule->call(function () {
          TimekeepingSplit::splitEachEmployee();
          })->cron($stringCronTab);
          } catch (Exception $ex) {
          Log::info($ex);
          }

          //gui mail bang cham cong, 5phut/lan
          try {
          $schedule->call(function () {
          TimekeepingSplit::doSplitTimesheetFiles();
          })->everyFiveMinutes();
          } catch (Exception $ex) {
          Log::info($ex);
          } */

        //crontab truncate email queue
        // run every 1 month
        $stringCronTab = "0 5 15 * *";
        try {
            $schedule->call(function () {
                EmailQueue::truncate();
                \Rikkei\Api\Models\ApiQueue::truncate();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //crontab run sync git
        // run every 10m
        try {
            $schedule->call(function () {
                TestTemp::submitResult();
            })->everyTenMinutes();
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //crontab run ME alert to PM
        //run at 06:00 on date 02 every month
        $stringCronTab = '0 6 2 * *';
        try {
            $schedule->call(function () {
                MeMailAlert::sendToPM();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //crontab run ME alert to GL
        //run at 06:00 on date 04 every month
        $stringCronTab = '0 6 4 * *';
        try {
            $schedule->call(function () {
                MeMailAlert::sendToGL();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //crontab run ME alert to ST
        //run at 06:00 on date 05 every month
        $stringCronTab = '0 6 5 * *';
        try {
            $schedule->call(function () {
                MeMailAlert::sendToStaff();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //crontab resize image
        // run every
        $stringCronTab = "* * * * *";
        try {
            $schedule->call(function () {
                RunBgSlide::resizeSlide();
            })->cron($stringCronTab);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //crontab send mail deadline
        //run every day at 16:15 PM
        $stringCronTab = "15 16 * * *";
        try {
            $schedule->call(function () {
                TicketCronjob::getTicketCronjob();
            })->dailyAt('08:00');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * send auto 8:00
         */
        // try {
        //     $schedule->call(function () {
        //         MailEmployee::sendAllEmployeeBirthday();
        //     })->cron('00 8 * * *');
        // } catch (Exception $ex) {
        //     Log::info($ex);
        // }
        $schedule->command('event:send_mail_birthday')->cron('00 8 * * *');

        /**
         * send auto 8:00 for employee membership
         */
        /* try {
          $schedule->call(function () {
          MailEmployee::sendAllEmployeeMembership();
          })->cron('00 8 * * *');
          } catch (Exception $ex) {
          Log::info($ex);
          } */

        /**
         * Set end_at for employees leave date in today
         */
        try {
            $schedule->call(function () {
                Log::info('start cron update end at employee team history');
                \Rikkei\Team\Model\EmployeeTeamHistory::cronUpdate();
                Log::info('end cron update end at employee team history');
            })->everyThirtyMinutes();
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * set data monthly report
         * insert data to table monthly_report
         */
        try {
            $schedule->call(function () {
                Log::info('start cron monthly report');
                MonthlyReport::cronData();
                TeamEffort::cronUpdateTeamEffort();
                Log::info('end cron monthly report');
            })->everyThirtyMinutes();
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * Delete data from monthly_report table
         * where created_at <> current month
         */
        try {
            $schedule->call(function () {
                Log::info('start cron monthly report delete old data');
                MonthlyReport::cronDeleteOldData();
                Log::info('end cron monthly report delete old data');
            })->monthlyOn(15, '23:00');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /*        try {
          $schedule->call(function () {
          Log::info('start cron check project reward budget reviewed');
          ProjectCronjob::sendMailPromptReviewBudget();
          Log::info('end cron check project reward budget reviewed');
          })->dailyAt('8:00');
          } catch (Exception $ex) {
          Log::info($ex);
          } */

        /**
         * send mail notification when request has expired or recruited enough
         */
//        try {
//            $schedule->call(function () {
//                CronData::cronMailCloseRequest();
//            })->dailyAt('8:00');
//        } catch (Exception $ex) {
//            Log::info($ex);
//        }

        /**
         * Send mail to leader, bod every monday at 8:00
         * Send list employee with effort = 0%
         */
//        try {
//            $schedule->call(function () {
//                CronData::cronMailUtilization();
//            })->cron('00 08 * * 1');
//        } catch (Exception $ex) {
//            Log::info($ex);
//        }

        /**
         * set resource dashboard data
         * insert data to table resource_dashboard
         */
        try {
            $schedule->call(function () {
                Dashboard::cronUpdateDashboardData();
            })->everyThirtyMinutes();
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //cron data Hr weekly report once an hour
        try {
            $schedule->call(function () {
                \Rikkei\Resource\View\HrWeeklyReport::cronData();
            })->cron(config('candidate.cron_hr_weekly_report'));
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * cron send mail notification tasks deadline.
         */
        try {
            $schedule->call(function () {
                CheckDeadlineMail::cronMail();
            })->dailyAt('9:00');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        // remove cache everyday - temp
        try {
            $schedule->call(function () {
                Artisan::call('config:cache');
                Artisan::call('cache:clear');
            })->cron('30 5 * * *');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * cron send mail notification tasks warning.
         */
        try {
            $schedule->call(function () {
                CheckWarningTask::checkWarningTaskRisk();
            })->cron('0 9 * * 1');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //delete test temp folder at 03:00 date 1 every month
        try {
            $schedule->call(function () {
                \Rikkei\Test\View\ViewTest::cronDelTempImage();
            })->cron('0 3 1 * *');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //crontab run sync git
        try {
            $schedule->call(function () {
                STProjHelper::processAllSTProj();
            })->cron('0 23 * * *');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        // remove process 1:00 am
        try {
            $schedule->call(function () {
                TimekeepingHelper::getInstance()->removeProcess();
                EmailQueue::deleteProcessing();
            })->cron('0 1 * * *');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //send email file password to new employee, check every day send at 12:30
        try {
            $schedule->call(function () {
                \Rikkei\Team\Model\EmployeeSetting::createFilePassAndSendMail();
            })->dailyAt('12:30');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * cron send mail notification projects has end date or deliver for customer on current week.
         */
        try {
            $schedule->call(function () {
                CheckEndDateProjsOnWeek::endDateMailProjsOnWeek();
            })->cron('0 8 * * 1');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * cron send mail introduce new staff
         */
        /* try {
          $schedule->call(function () {
          SendMailCron::cronMailNewStaff();
          })->monthlyOn(1, '8:30');
          } catch (Exception $ex) {
          Log::info($ex);
          } */

        /**
         * Send mail to PQA and PM when customer feedback task that status is new or progress.
         */
        try {
            $schedule->call(function () {
                CheckTaskCF::sendMailCF($freequencyReport = 2); // check mail every day.
            })->dailyAt('8:45');
        } catch (Exception $ex) {
            Log::info($ex);
        }
        try {
            $schedule->call(function () {
                CheckTaskCF::sendMailCF($freequencyReport = 1); // check mail every week.
            })->weekly()->mondays()->at('8:45');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        try {
            $schedule->call(function () {
                SendMailCron::sendMailEmployeeOutsourced();
            })->dailyAt('9:10');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /*
         * update data employee available
         */
        try {
            $schedule->call(function () {
                \Rikkei\Resource\Model\EmpAvailableData::cronUpdate();
            })->dailyAt('06:00');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /*
         * Opportunity alert deadline, update status
         */
        try {
            $schedule->call(function () {
                \Rikkei\Sales\Model\ReqOpportunity::cronSendMailAlert();
            })->dailyAt('07:00');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * Delete notification 2 month before
         */
        try {
            $schedule->call(function () {
                \Rikkei\Notify\Model\Notification::cronDeleteNotify();
            })->cron('00 00 01 */2 *');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        // do update member after upload file
        try {
            $schedule->call(function () {
                \Rikkei\Team\View\UploadMember::getInstance()->doUpdateMember();
            })->everyFiveMinutes();
        } catch (Exception $ex) {
            Log::info($ex);
        }

        //Send remind project report at 8am friday weekly
        try {
            $schedule->call(function () {
                Project::sendMailReportToPmProject();
            })->weeklyOn(5, '8:00');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * Remind update skillsheet
         * */
//        try {
//            $schedule->call(function () {
//                \Rikkei\Team\Model\Employee::sendMailEmployeesUnSubmitSkillSheet();
//            })->dailyAt('08:00');
//        } catch (Exception $ex) {
//            Log::info($ex);
//        }


        try {
            //Thong bao co hop dong het han
            $schedule->call(function () {
                ContractQueue::notifyContractExpireDate();
            })
            ->dailyAt('05:00');
            // ->cron('* * * * *');

        } catch (Exception $ex) {
            Log::info($ex);
        }

        /*
         * call api update IM User every 30 minutes
         */
        try {
            $schedule->call(function () {
                \Rikkei\Api\Models\ApiQueue::callApi();
            })->everyThirtyMinutes();
        } catch (Exception $ex) {
            Log::info($ex);
        }

          /*
         * call funtion insert/update DB table Cronjob_employee_points twice a day
         */
        try {
            $schedule->call(function () {
                Log::info('start cron Employee point ( every hour )');
                EmployeePointCronJob::cronJobEmployeePoint();
                Log::info('end cron Employee point ( every hour )');
            })->cron('40 9,15 * * *');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /*
         * call funtion insert DB table Operation Overview 30 minutes
         */
        try {
            $schedule->call(function () {
                Log::info('start cron Operation Overview ( every 30 minutes )');
                OperationCronJob::cronJobOperationOverview();
                Log::info('end cron Operation Overview ( every 30 minutes )');
            })->cron('50 9,15 * * *');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /*
         * call funtion insert DB table cronjob_project_allocations Twice a day
         */
        try {
            $schedule->call(function () {
                HrmFo::cronjobHrmAllocation();
            })->twiceDaily(0, 12);
        } catch (Exception $ex) {
            Log::info($ex);
        }

        /**
         * cron send mail reminder education to employees every hours
         */
        try {
            $schedule->call(function () {
                Log::info('start cron send mail reminder education to employees');
                SettingTemplateMailService::cronSendMailReminder();
                Log::info('end cron send mail reminder education to employees');
            })->everyFiveMinutes();
        } catch (Exception $ex) {
            Log::info($ex);
        }

        try {
            $schedule->call(function () {
                MailEmployee::mailSpecialDate();
            })->dailyAt('08:00');
        } catch (Exception $ex) {
            Log::info($ex);
        }

        // Get fines forgot turn off of employee by last month
        // Run first day of monthly at 04:05 AM
        $schedule->command('stats:turnoff')->monthlyOn(1, '4:5');

        // Check employees have birthday in day
        // Run daily at 04:00 AM
        $schedule->command('admin:sendmail')->dailyAt('04:00');
        // Employee statistics baseline
        // Run first day of monthly at 00:20 AM
        $schedule->command('stats:baseline')->monthlyOn(1, '00:20');

        // Block git,redmine accounts of employees leave job
        $schedule->command('project:block_account_git_redmine')->dailyAt('23:55');
        
        // Cron job notice of Japan leave
        $schedule->command('leaveday:notify')->dailyAt('08:00');

        // insert, update, delete company synchronized crm At minute 59 past hour 6, 12, 19, and 0
        $schedule->command('company:set')->cron('59 6,12,19,0 * * *');

        // NC remind duedate
        $schedule->command('project:nc_remind_duedate')->dailyAt('10:10');

        // insert, update, delete cust_contacts synchronized crm At minute 59 past hour 6, 12, 19, and 0
        $schedule->command('customer-contact:set')->cron('59 6,12,19,0 * * *');

        //[News] Set lịch đăng bài viết
        $schedule->command('post:enable')->everyFiveMinutes();

        // Cache menu for all employee
        $schedule->command('cache-menu')->dailyAt('00:10');

        $schedule->command('event:send_mail_HRday');

    }
}
