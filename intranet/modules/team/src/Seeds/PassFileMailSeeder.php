<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeSetting;
use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Rikkei\Resource\View\getOptions;

class PassFileMailSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(2)) {
            return true;
        }

        DB::beginTransaction();
        try {

            /*
             * init default pass
             */
            $now = Carbon::now();
            $employees = Employee::where(function ($query) use ($now) {
                $query->whereNull("leave_date")
                    ->orWhereDate("leave_date", '>=', $now->format('Y-m-d'));
            })
                    ->whereNotIn("account_status", [getOptions::PREPARING, getOptions::FAIL_CDD])
                    ->select('id')
                    ->get();
            if (!$employees->isEmpty()) {
                foreach ($employees as $emp) {
                    EmployeeSetting::insertOrUpdate(
                        $emp->id,
                        ['pass_open_file' => encrypt(str_random(8))]
                    );
                }
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

}
