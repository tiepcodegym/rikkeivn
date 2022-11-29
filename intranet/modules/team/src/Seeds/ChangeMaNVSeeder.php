<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Employee;

class ChangeMaNVSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
            $phund = Employee::where("email", "phund@rikkeisoft.com")->first();
            $phund2 = Employee::where("email", "phund2@rikkeisoft.com")->first();
            
            if ($phund) {
                $phund->employee_code  = "NV0000273";
                $phund->employee_card_id = 273;
                $phund->save();
            } 
            if ($phund2) {
                $phund2->employee_code = "NV0000532";
                $phund2->employee_card_id = 532;
                $phund2->save();
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
