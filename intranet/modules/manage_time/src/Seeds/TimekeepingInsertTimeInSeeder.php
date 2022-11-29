<?php
namespace Rikkei\ManageTime\Seeds;

use Excellption;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\ManageTime\Model\TimekeepingTable;

class TimekeepingInsertTimeInSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $startDate = '2020-08-01';
        $endDate = '2020-08-27';
        $tkTabke = TimekeepingTable::getTableName();
        $tk = Timekeeping::getTableName();

        $collection =  TimekeepingTable::select(
            'tk.id',
            'tk.timekeeping_table_id',
            'tk.timekeeping_date',
            'tk.start_time_morning_shift',
            'tk.start_time_morning_shift_real'
        )
        ->leftJoin('manage_time_timekeepings as tk', 'tk.timekeeping_table_id', '=', "{$tkTabke}.id")
        ->leftJoin('teams', 'teams.id', '=', "{$tkTabke}.team_id")
        ->where("{$tkTabke}.start_date", '>=', $startDate)
        ->where("tk.timekeeping_date", '<=', $endDate)
        ->where("teams.branch_code", '<>', 'hanoi')
        ->get();
        if (!count($collection)) {
            return;
        }
        $id = $collection->lists('id')->toArray();
        $id = implode(', ',  $id);

        try {
            DB::statement("UPDATE manage_time_timekeepings AS tk SET tk.start_time_morning_shift_real = tk.start_time_morning_shift  WHERE id IN ($id) ");
            $this->insertSeedMigrate();
        } catch (Excellption $e) {
            Log::info($e);
        }
    }
}