<?php

namespace Rikkei\Core\Console\Commands;

use Illuminate\Console\Command;
use Rikkei\AdminSetting\Model\AdminDivision;

class CheckEmployeeBirthdayInDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:sendmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check những nhân viên có sinh nhật trong ngày và gửi mail thông báo cho admin';

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
            $employeeHasBirthdayInDay = AdminDivision::getEmployeeHasBirthdayInDay();
            if (count($employeeHasBirthdayInDay) === 0) {
                return;
            }

            \Log::info('=== Start crontabCheckEmployeeHasBirthdayInDayAndSendMailToAdmin ===');
            $this->info('=== Start crontabCheckEmployeeHasBirthdayInDayAndSendMailToAdmin ===');
            if (count($employeeHasBirthdayInDay) > 0) {
                $adminDivision = [];
                foreach ($employeeHasBirthdayInDay as $value) {
                    $value->admin = str_replace(['{', '}'], '', $value->admin);
                    $value->admin = explode(',', $value->admin);
                    $adminDivision[$value->division][] = [
                        'id' => $value->empId,
                        'name' => $value->name,
                        'email' => $value->email
                    ];
                }
                foreach ($adminDivision as $key => $val) {
                    $division = AdminDivision::getByDivision($key, true);
                    AdminDivision::sendMailNotifyAdminEmpBirthday($division, $val);
                }
            }
            $this->info('=== Start crontabCheckEmployeeHasBirthdayInDayAndSendMailToAdmin ===');
            \Log::info('=== Start crontabCheckEmployeeHasBirthdayInDayAndSendMailToAdmin ===');
        } catch (\Exception $e) {
            $this->info($e->getMessage());
            \Log::error($e);
        }
    }
}
