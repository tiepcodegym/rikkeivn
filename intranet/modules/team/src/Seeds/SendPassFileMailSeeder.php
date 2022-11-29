<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Team\Model\EmployeeSetting;
use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;

class SendPassFileMailSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(6)) {
            return true;
        }

        DB::beginTransaction();
        try {

            EmployeeSetting::cronSendFilePass();

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

}
