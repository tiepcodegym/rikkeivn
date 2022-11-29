<?php

namespace Rikkei\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rikkei\ManageTime\Model\LeaveDay;

class JapanLeaveDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaveday:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Thông báo nhân viên tại Japan trong 1 năm chưa nghỉ đủ 5 ngày phép';

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
        try {
            Log::info('Start cron job notice of Japan leave');
            LeaveDay::cronJobNoticeJapanLeave();
            Log::info('End cron job notice of Japan leave');
        } catch (\Exception $e) {
            Log::error($e);
        }
    }
}
