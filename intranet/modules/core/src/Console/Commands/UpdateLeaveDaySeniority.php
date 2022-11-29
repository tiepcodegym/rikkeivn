<?php

namespace Rikkei\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rikkei\ManageTime\Model\LeaveDay;

class UpdateLeaveDaySeniority extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaveday:seniority';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update LeaveDay Seniority';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('Start cron job update leave seniority');
        LeaveDay::cronJobUpdateLeaveDaySeniority();
        Log::info('End cron job update leave seniority');
    }
}
