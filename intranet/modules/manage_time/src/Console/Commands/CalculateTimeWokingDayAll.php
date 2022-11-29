<?php

namespace Rikkei\ManageTime\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\ManageTime\Http\Controllers\TimekeepingController;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Rikkei\Team\Model\Team;

class CalculateTimeWokingDayAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timeworkingdayAll:calculate {users} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tính toán thời gian làm việc của nhân viên hằng ngày toàn tháng';

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
     * calculate time working
     *
     */
    public function handle()
    {
        try {
            $this::info('=== Start calculate time working of employee in month ===');
            $date = $this->argument('date');
            if (strpos($date, '-') != 4) {
                $this->info('sai định dạng: Y-m');
                return;
            }
            $date = explode('-', $date);
            if (!is_numeric($date[0]) || !is_numeric($date[1])) {
                $this->info('năm và tháng là kiểu số.');
                return;
            }
            $users = $this->argument('users');
            $users = str_replace('[', '', $users);
            $users = str_replace(']', '', $users);
            if ($users == '') {
                $users = [];
            } else {
                $users = explode(',', $users);
            }

            $cbDate = Carbon::parse($date[0] . '-' . $date[1]);
            $this->calculateAllMonth($cbDate, $users);
            $this::info('=== End calculate time working of employee in month ===');
        } catch (Exception $e) {
            $this->info($e->getMessage());
            Log::error($e);
        }
    }

    public function calculateAllMonth($cbDate, $users, $checkCron = false)
    {
        DB::beginTransaction();
        try {
            Log::info('=== Start calculate time working of employee in month ===');
            $startDate = clone $cbDate->firstOfMonth();
            $endDate = clone $cbDate->endOfMonth();

            $month = $cbDate->month;
            $year = $cbDate->year;

            $tables = TimekeepingTable::select('id', 'creator_id', 'team_id', 'month', 'year', 'type', 'start_date', 'end_date')
                ->where('month', $month)->where('year', $year)->whereNull('deleted_at')->get();
            if (!$tables) {
                Log::info('Table timekeeping month now do not exist');
                return false;
            }
            $ids = $tables->lists('id')->toArray();
            //=== get team of tables ===
            $teams = [];
            $typeTable = [];
            foreach ($tables as $table) {
                $team = Team::getTeamById($table->team_id);
                $teamCodePre = Team::getTeamCodePrefix($team->code);
                $teamCodePre = Team::changeTeam($teamCodePre);
                $teams[$table->id] = empty($teamCodePre) ? "hanoi" : $teamCodePre;
                $typeTable[$table->id] = $table->type;
            }

            $this->resetTimeKeeping($ids, $startDate, $endDate, $users, $checkCron);

            $timekeepingsWork = Timekeeping::select(
                    'timekeeping_table_id',
                    'employee_id',
                    'timekeeping_date',
                    'start_time_morning_shift',
                    'end_time_morning_shift',
                    'start_time_afternoon_shift',
                    'end_time_afternoon_shift',
                    'timekeeping_number',
                    'timekeeping_number_register',
                    'emp.email'
                )
                ->leftJoin('employees as emp', 'emp.id', '=', 'manage_time_timekeepings.employee_id')
                ->whereIn('timekeeping_table_id', $ids)
                ->where('timekeeping_date', '>=', $startDate->format("Y-m-d"))
                ->where('timekeeping_date', '<=', $endDate->format("Y-m-d"))
                ->where(function ($sql) {
                    $sql->whereNotNull('start_time_morning_shift')
                        ->orwhereNotNull('start_time_afternoon_shift');
                });
            if (count($users)) {
                $timekeepingsWork = $timekeepingsWork->whereIn('employee_id', $users);
            }
            $timekeepingsWork = $timekeepingsWork->orderBy('timekeeping_table_id')->orderBy('employee_id')->get();

            // tinh OT
            $timekeepingsOT = Timekeeping::select('timekeeping_table_id', 'employee_id')
                ->whereIn('timekeeping_table_id', $ids)
                ->whereDate('timekeeping_date', '>=', $startDate->format("Y-m-d"))
                ->whereDate('timekeeping_date', '<=', $endDate->format("Y-m-d"));
            if (count($users)) {
                $timekeepingsOT = $timekeepingsOT->whereIn('employee_id', $users);
            }
            $timekeepingsOT = $timekeepingsOT->orderBy('timekeeping_table_id')->orderBy('employee_id')->get();
            $empIds = $timekeepingsOT->lists('employee_id')->toArray();
            $objView = new ManageTimeView();
            $dataNotLate = $objView->getDataNotLate($empIds, $startDate, $endDate);

            while (strtotime($startDate) <= strtotime($endDate)) {
                $now = clone $startDate;
                $viewTimeKeeping = new ViewTimeKeeping;
                if (!$timekeepingsWork) {
                    Log::info('Table manage_time_timekeepings do not exist');
                } else {
                    $viewTimeKeeping->calculateTimeWork($timekeepingsWork, $teams, $typeTable, $now->format("Y-m-d"), $dataNotLate);
                }
                $viewTimeKeeping->calculationOT($now->format("Y-m-d"), $timekeepingsOT, $empIds, $teams, $typeTable);
                $startDate->addDay();
            }
            //Check đi muộn về sớm
            foreach ($tables as $table) {
                ManageTimeView::updateEarlyLate($table);
            }
            DB::commit();
            Log::info('=== End calculate time working of employee in month ===');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
        }
    }
    
    public function resetTimeKeeping($tblIds, $startDate, $endDate, $users, $checkCron)
    {
        $updateTk = Timekeeping::whereIn('timekeeping_table_id', $tblIds)
            ->where('timekeeping_date', '>=', $startDate->format("Y-m-d"))
            ->where('timekeeping_date', '<=', $endDate->format("Y-m-d"));
        if (count($users)) {
            $updateTk = $updateTk->whereIn('employee_id', $users);
        }
        $dataResetTimekeeping = [
            'late_start_shift' => 0,
            'early_mid_shift' => 0,
            'late_mid_shift' => 0,
            'early_end_shift' => 0,
            'timekeeping' => 0,
            'timekeeping_number' => 0,
            'timekeeping_number_register' => 0,
        ];
        if ($checkCron) {
            $data = [
                'has_business_trip' => ManageTimeConst::HAS_NOT_BUSINESS_TRIP,
                'register_business_trip_number' => 0,
                'has_leave_day' => ManageTimeConst::HAS_NOT_LEAVE_DAY,
                'has_leave_day_no_salary' => ManageTimeConst::HAS_NOT_LEAVE_DAY,
                'register_leave_has_salary' => 0,
                'register_leave_no_salary' => 0,
                'has_supplement' => ManageTimeConst::HAS_NOT_SUPPLEMENT,
                'register_supplement_number' => 0,
                'leave_day_added' => 0,
                'register_ot' => 0,
                'register_ot_no_salary' => 0,
                'register_ot_has_salary' => 0,
            ];
            $dataResetTimekeeping = array_merge($dataResetTimekeeping, $data);
        }
        $updateTk->update($dataResetTimekeeping);
    }
}
