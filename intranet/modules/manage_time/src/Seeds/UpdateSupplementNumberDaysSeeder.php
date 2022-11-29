<?php
namespace Rikkei\ManageTime\Seeds;

use DB;
use Exception;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\ManageTime\Model\SupplementEmployee;

class UpdateSupplementNumberDaysSeeder extends CoreSeeder
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
        DB::beginTransaction();
        try {
            $regList = SupplementRegister::all();
            foreach ($regList as $reg) {
                SupplementEmployee::where('supplement_registers_id', $reg->id)->update([
                    'number_days' => $reg->number_days_supplement,
                ]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
