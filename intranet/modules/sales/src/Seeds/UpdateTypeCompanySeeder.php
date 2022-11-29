<?php
namespace Rikkei\Sales\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Sales\Model\Company;

class UpdateTypeCompanySeeder extends CoreSeeder
{
    /**
     * @throws \Exception
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        DB::beginTransaction();
        try {
            Company::where('company', 'LIKE', '%systena%')->update(['type' => Company::TYPE_SYSTENA]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
