<?php

namespace Rikkei\ManageTime\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\Team\Model\Team;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\Log;
use Rikkei\ManageTime\Model\TimekeepingNotLate;

class RemindEmpWorkShortOfTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timekeeping:remind_emp_work_short_of_time {year=0} {month=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi mail thông báo cho nhân viên làm thiếu giờ của ngày hôm trước đó.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $this->info('=== Start send mail remind for employees work short of time.');
            Log::info('=== Start send mail remind for employees work short of time.');

            // Get param from command
            $year = $this->argument('year');
            $month = $this->argument('month');
            if (!$year || !$month) {
                $date = Carbon::now()->subMonth();
                $month = $date->month;
                $year = $date->year;
            }
    
            $tblTimekeepingTable = TimekeepingTable::getTableName();
            $tblTeam = Team::getTableName();
            $timekeepingTblIds = TimekeepingTable::select(
                    "{$tblTimekeepingTable}.id",
                    "{$tblTimekeepingTable}.timekeeping_table_name",
                    "{$tblTeam}.code as team_code",
                    "{$tblTeam}.name as team_name"
                )
                ->join("{$tblTeam}", "{$tblTeam}.id", '=', "{$tblTimekeepingTable}.team_id")
                ->where("{$tblTeam}.branch_code", Team::CODE_PREFIX_HN)
                ->where('month', $month)->where('year', $year)->get()->pluck('id')->toArray();
            if (!$timekeepingTblIds) {
                return;
            }
    
            $employeeIds = Timekeeping::select(
                'employee_id',
                DB::raw('SUM(time_over) AS totalTime')
            )
            ->whereIn('timekeeping_table_id', $timekeepingTblIds)
            ->groupBy('employee_id')
            ->limit(20)
            ->get();
    
            if ($employeeIds) {
                //send mail
                $this->sendMail($employeeIds, $month, $year);
            }
        } catch (Exception $ex) {
            Log::error($ex);
            $this->info($ex->getMessage());
        } finally {
            $this->info('=== End send mail remind for employees work short of time.');
            Log::info('=== End send mail remind for employees work short of time.');
        }
    }

    public function sendMail($dataEmps, $month, $year)
    {
        DB::beginTransaction();
        try {
            $objNotLate = new TimekeepingNotLate();
            $empNotLate = $objNotLate->getEmployeeNotLate()->pluck('emp_id')->toArray();
            $dataInsert = [];
            foreach ($dataEmps as $item) {
                $emp = Employee::find($item->employee_id);
                if (!$emp || in_array($item->employee_id, $empNotLate) || $item->totalTime >= 0 || ($item->totalTime*(-1)) < 60) {
                    continue;
                }
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($emp['email'], $emp['name'])
                    ->setSubject('[Rikkei.vn] Mail thông báo số phút làm thiếu vào tháng ' . $month.'-'.$year)
                    ->setTemplate('manage_time::timekeeping.mail.mail_timekeeping_remind', [
                        'name' => $emp['name'],
                        'email' => $emp['email'],
                        'month' => $month,
                        'year' => $year,
                        'hours' => (int)(($item->totalTime*(-1))/60),
                        'minutes' => ($item->totalTime*(-1))%60,
                        'route' => route('manage_time::profile.timekeeping-list')
                    ]);
                $dataInsert[] = $emailQueue->getValue();
            }

            if (!empty($dataInsert)) {
                EmailQueue::insert($dataInsert);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            $this->info($e->getMessage());
        }
    }

}