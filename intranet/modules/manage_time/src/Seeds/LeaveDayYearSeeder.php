<?php
namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Rikkei\ManageTime\Model\LeaveDayRegister;

class LeaveDayYearSeeder extends CoreSeeder
{
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
//            return true;
        }
        $leavesEmployee = DB::table('leave_days')
            ->select(['id', 'employee_id', 'day_used'])
            ->get();
        if (!count($leavesEmployee)) {
            return true;
        }
        $year = Carbon::today()->year;
        $reason = 2; // nghi co phep
        DB::beginTransaction();
        try {
            foreach ($leavesEmployee as $itemLeave) {
                // get sum day of last year
                $leaveRegister = DB::table('leave_day_registers')
                    ->select([DB::raw('SUM(number_days_off) AS sum_day')])
                    ->where('creator_id', '=', $itemLeave->employee_id)
                    ->where('reason_id', '=', $reason)
                    ->where('status', '=', LeaveDayRegister::STATUS_APPROVED)
                    ->whereRaw('YEAR(date_start) = ' . $year)
                    ->whereRaw('YEAR(date_end) = ' . $year)
                    ->first();
                if (!count($leaveRegister)) {
                    continue;
                }
                if (!$leaveRegister->sum_day) {
                    $day = 0;
                } else {
                    $day = $leaveRegister->sum_day;
                }
                DB::table('leave_days')
                    ->where('id', '=', $itemLeave->id)
                    ->update([
                        'day_used' => $day,
                    ]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::info($ex);
        }
    }
}
