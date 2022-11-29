<?php

namespace Rikkei\Test\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Test\Models\TestResult;
use Illuminate\Support\Facades\DB;

class ResultDetailSeeder extends CoreSeeder {

    public function run() {
        if ($this->checkExistsSeed()) {
            return;
        }
        
        DB::beginTransaction();
        try {
            $childDetails = TestResult::with('rsParent')
                    ->whereNotNull('parent_id')
                    ->where('test_result_id', 0)
                    ->get();
            
            if ($childDetails->isEmpty()) {
                DB::commit();
                return;
            }
            
            foreach ($childDetails as $item) {
                if ($item->rsParent) {
                    $item->test_result_id = $item->rsParent->test_result_id;
                    $item->save();
                }
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex){
            DB::rollback();
            \Log::info($ex);
            throw $ex;
        }
    }

}
