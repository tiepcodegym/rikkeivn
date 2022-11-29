<?php

namespace Rikkei\Resource\Seeds;

use Rikkei\Resource\Model\Candidate;
use Rikkei\Test\Models\Test;
use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;

class CandidateGmatTypeSeeder extends CoreSeeder
{
    
    public function run() {
        if ($this->checkExistsSeed()) {
            return;
        }
        
        DB::beginTransaction();
        try {
            $candidateGmat = Candidate::select('test_option_gmat', 'test_option_type_ids', 'id')
                    ->where('test_option_gmat', 1)->get();
            if (!$candidateGmat->isEmpty()) {
                $gmatId = Test::getGMATId();
                foreach ($candidateGmat as $item) {
                    $typeIds = $item->test_option_type_ids;
                    if (in_array($gmatId, $typeIds)) {
                        unset($typeIds[array_search($gmatId, $typeIds)]);
                    }
                    array_unshift($typeIds, $gmatId);
                    $item->test_option_type_ids = $typeIds;
                    $item->save();
                }
            }
            
            DB::commit();
            $this->insertSeedMigrate();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            throw $ex;
        }
    }

}

