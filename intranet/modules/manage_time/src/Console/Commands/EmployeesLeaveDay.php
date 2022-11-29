<?php

namespace Rikkei\ManageTime\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Team\Model\Employee;
use Rikkei\ManageTime\Model\LeaveDay;

class EmployeesLeaveDay extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaveday:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa bỏ ngày nghỉ phép của những nhân viên đã nghỉ việc';

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
            $employeesLeaveDay = Employee::getEmployeesLeaveDay();
            if (empty($employeesLeaveDay)) {
                return;
            }

            \Log::info('=== Start crontabRemoveDayOffEmployessLeaveOff ===');
            $this->info('=== Start crontabRemoveDayOffEmployessLeaveOff ===');
            if (count($employeesLeaveDay) > 0) {
                $employeesArray = $employeesLeaveDay->lists('employeeId');
                LeaveDay::whereIn('employee_id', $employeesArray)->forceDelete();
            }
            $this->info('=== End crontabRemoveDayOffEmployessLeaveOff ===');
            \Log::info('=== End crontabRemoveDayOffEmployessLeaveOff === ');
        } catch (\Exception $e) {
            $this->info($e->getMessage());
            \Log::error($e);
        }
    }
}
