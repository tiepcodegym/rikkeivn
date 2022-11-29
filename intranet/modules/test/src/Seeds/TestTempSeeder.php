<?php

namespace Rikkei\Test\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Test\Models\TestTemp;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestTempSeeder extends CoreSeeder {

    public function run() {
        if ($this->checkExistsSeed()) {
            return;
        }
        
        DB::beginTransaction();
        try {
            TestTemp::where('created_at', '<=', Carbon::now()->subDays(2))
                    ->delete();

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex){
            DB::rollback();
            \Log::info($ex);
            throw $ex;
        }
    }

}
