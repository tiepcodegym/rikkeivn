<?php
namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;
use Log;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\ManageTime\Model\SupplementEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\Model\BusinessTripEmployee;

class UpdateOldRegisterSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('2')) {
            return true;
        }
        DB::beginTransaction();
        try {
            // Update supplement
            $supplementEmpTbl = SupplementEmployee::getTableName();
            $supplementRegisters = SupplementRegister::whereRaw("id NOT IN (SELECT supplement_registers_id FROM {$supplementEmpTbl})")->get();
            if (count($supplementRegisters)) {
                $dataSupplement = [];
                foreach ($supplementRegisters as $reg) {
                    $dataSupplement[] = [
                        'supplement_registers_id' => $reg->id,
                        'employee_id' => $reg->creator_id,
                        'start_at' => $reg->date_start,
                        'end_at' => $reg->date_end,
                    ];
                }
                SupplementEmployee::insert($dataSupplement);
            }

            // Update business
            $businessEmpTbl = BusinessTripEmployee::getTableName();
            $businessRegisters = BusinessTripRegister::whereRaw("id NOT IN (SELECT register_id FROM {$businessEmpTbl})")->get();
            if (count($businessRegisters)) {
                $dataBusiness = [];
                foreach ($businessRegisters as $regBusiness) {
                    $dataBusiness[] = [
                        'register_id' => $regBusiness->id,
                        'employee_id' => $regBusiness->creator_id,
                        'start_at' => $regBusiness->date_start,
                        'end_at' => $regBusiness->date_end,
                    ];
                }
                BusinessTripEmployee::insert($dataBusiness);
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::info($ex);
        }
    }
}
