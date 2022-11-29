<?php

namespace Rikkei\Resource\Seeds;

use Rikkei\Resource\Model\Candidate;
use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;

class CandidateGmatPointSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }

        DB::beginTransaction();
        try {
            $candidates = Candidate::select('id', 'test_mark', 'test_gmat_point')
                    ->whereNotNull('test_mark')
                    ->get();
            if (!$candidates->isEmpty()) {
                foreach ($candidates as $item) {
                    $item->test_gmat_point = $item->test_mark;
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

