<?php

namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Resource\Model\Week;
use Rikkei\Resource\View\HrWeeklyReport;
use Rikkei\Resource\View\getOptions;
use Illuminate\Support\Facades\DB;

class UpdateCandidateWeekSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('UpdateCandidateWeekSeeder-V3')) {
            return;
        }
        $allDate = Week::all();

        DB::beginTransaction();
        try {
            foreach ($allDate as $item) {
                if (!$item->week) {
                    continue;
                }
                $dataSave = [
                    'number_cvs' => json_encode(HrWeeklyReport::getDataNumCv($item->week)),
                    'tests' => json_encode(HrWeeklyReport::getDataTest($item->week)),
                    'tests_pass' => json_encode(HrWeeklyReport::getDataTest($item->week, getOptions::RESULT_PASS)),
                    'gmats_8' => json_encode(HrWeeklyReport::getDataTest($item->week, null, 8)),
                    'interviews' => json_encode(HrWeeklyReport::getDataInterview($item->week)),
                    'interviews_pass' => json_encode(HrWeeklyReport::getDataInterview($item->week, getOptions::RESULT_PASS)),
                    'offers' => json_encode(HrWeeklyReport::getDataOffer($item->week)),
                    'offers_pass' => json_encode(HrWeeklyReport::getDataOffer($item->week, getOptions::RESULT_PASS)),
                    'workings' => json_encode(HrWeeklyReport::getDataWorking($item->week))
                ];
                $item->update($dataSave);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
