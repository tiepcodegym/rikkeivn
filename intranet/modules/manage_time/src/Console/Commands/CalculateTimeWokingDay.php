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
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Rikkei\Team\Model\Team;

class CalculateTimeWokingDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timeworkingday:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tính toán thời gian làm việc của nhân viên hằng ngày hôm trước';

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
        DB::beginTransaction();
        try {
            Log::info('=== Start calculate time working of employee every day ===');
            $now = Carbon::now()->subDay();
            $month = $now->month;
            $year = $now->year;

            $tables = TimekeepingTable::where('month', $month)->where('year', $year)->whereNull('deleted_at')->get();
            if (!$tables) {
                Log::info('Table timekeeping month now do not exist');
                return false;
            }
            $ids = $tables->lists('id')->toArray();
            $timekeepings =  Timekeeping::select(
                    'timekeeping_table_id',
                    'employee_id',
                    'timekeeping_date',
                    'start_time_morning_shift',
                    'end_time_morning_shift',
                    'start_time_afternoon_shift',
                    'end_time_afternoon_shift',
                    'timekeeping_number',
                    'timekeeping_number_register'
                )
                ->whereIn('timekeeping_table_id', $ids)
                ->where('timekeeping_date', $now->format("Y-m-d"))
                ->where(function ($sql) {
                    $sql->whereNotNull('start_time_morning_shift')
                        ->orwhereNotNull('start_time_afternoon_shift');
                })
                ->orderBy('timekeeping_table_id')
                ->orderBy('employee_id')
                ->get();

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

            $viewTimeKeeping = new ViewTimeKeeping();
            if (!$timekeepings) {
                Log::info('Table manage_time_timekeepings do not exist');
            } else {
                $viewTimeKeeping->calculateTimeWork($timekeepings, $teams, $typeTable, $now->format("Y-m-d"));
            }

            // tinh OT
            $timekeepings = Timekeeping::select('timekeeping_table_id', 'employee_id')
                ->whereIn('timekeeping_table_id', $ids)
                ->where('timekeeping_date', $now->format("Y-m-d"))
                ->orderBy('timekeeping_table_id')
                ->orderBy('employee_id')
                ->get();
            $empIds = $timekeepings->lists('employee_id')->toArray();
            $viewTimeKeeping->calculationOT($now->format("Y-m-d"), $timekeepings, $empIds, $teams, $typeTable);

            //Check đi muộn về sớm
            foreach ($tables as $table) {
                ManageTimeView::updateEarlyLate($table);
            }

            // update timekeeping aggregate
            $request = new Request();
            foreach ($teams as $key => $value) {
                $data = [
                    'timekeeping_table_id' => $key,
                ];
                $request = new Request($data);
                TimekeepingController::updateTimekeepingAggregate($request);
            }

            DB::commit();
            Log::info('=== End calculate time working of employee every day ===');
        } catch (Exception $e) {
            DB::rollBack();
            $this->info($e->getMessage());
            Log::error($e);
        }
    }
}
