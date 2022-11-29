<?php

namespace Rikkei\ManageTime\Console\Commands;;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\ManageTime\Http\Controllers\TimekeepingController;

class CalculateTimekeepingAggreagtes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tkaggregates:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tính tổng hợp công của nhân viên';

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
     * // lấy trong tháng hiện tại
     * @return mixed
     */
    public function handle()
    {
        try {
            $now = Carbon::now();
            $month = $now->month;
            $year = $now->year;

            $tktables = DB::table('manage_time_timekeeping_tables as tktable')
                ->where('tktable.month', $month)
                ->where('tktable.year', $year)
                ->whereNull('deleted_at')
                ->get();
            if (!count($tktables)) {
                Log::info('=== Not exists timekeeping aggreagtes === ');
                return;
            }
            Log::info('=== Start calculate timekeeping aggreagtes ===');
            foreach ($tktables as $tktables) {
                Log::info('Timekeeping aggreagtes ' . $tktables->id);
                $data = [
                    'timekeeping_table_id' => $tktables->id,
                ];
                $request = new Request($data);
                TimekeepingController::updateTimekeepingAggregate($request);
            }
            Log::info('=== End calculate timekeeping aggreagtes ===');
        } catch (Exception $e) {
            $this->info($e->getMessage());
            Log::error($e);
        }
    }
}
