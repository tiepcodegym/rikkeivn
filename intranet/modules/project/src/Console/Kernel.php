<?php

namespace Rikkei\Project\Console;

use Illuminate\Console\Scheduling\Schedule;
use Rikkei\CallApi\View\View;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Rikkei\Project\Console\Commands\BlockAccountGitRedmine::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        try {
            $schedule->command('project:block_account_git_redmine')->monthlyOn('20', '19:00');
        } catch (\Exception $ex) {
            Log::info($ex);
        }
    }
}

