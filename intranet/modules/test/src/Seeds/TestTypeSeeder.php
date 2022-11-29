<?php

namespace Rikkei\Test\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Test\Models\Test;
use Illuminate\Support\Facades\DB;

class TestTypeSeeder extends CoreSeeder {

    public function run() {
        if ($this->checkExistsSeed()) {
            return;
        }
        
        DB::beginTransaction();
        try {
            Test::whereNull('type_id')
                    ->update(['type_id' => Test::getGMATId()]);

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex){
            DB::rollback();
            \Log::info($ex);
            throw $ex;
        }
    }

}
