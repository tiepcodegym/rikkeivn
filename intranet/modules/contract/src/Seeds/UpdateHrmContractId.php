<?php
namespace Rikkei\Contract\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Contract\Model\ContractModel;

class UpdateHrmContractId extends CoreSeeder
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
            $contracts = ContractModel::all();
            foreach ($contracts as $item) {
                $id = $item->id;
                $contract = ContractModel::find($id);
                $contract->update([
                    'hrm_contract_id' => $id
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
