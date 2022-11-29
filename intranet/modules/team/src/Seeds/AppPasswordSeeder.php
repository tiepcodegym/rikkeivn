<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\CoreConfigData;
use DB;

class AppPasswordSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }

        $emailCustom = CoreConfigData::getEmailSentCustom(2);
        DB::beginTransaction();
        try {
            if ($emailCustom) {
                foreach ($emailCustom as $item) {
                    $employee = Employee::where('email', $item[2])->first();
                    if (!$employee) {
                        continue;
                    }
                    $employee->app_password = $item[3];
                    $employee->save();
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
