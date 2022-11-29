<?php

namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\MeEvaluation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EvaluationUpdateTimeSheet extends CoreSeeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        if ($this->checkExistsSeed(1)) {
            return;
        }
        //$time = Carbon::now()->subMonthNoOverflow();
        $time = Carbon::parse('2018-08-01');
        DB::beginTransaction();
        try {
            MeEvaluation::updateMEAfterUploadTimeSheet($time);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

}
