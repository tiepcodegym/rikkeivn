<?php

namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\MeEvaluation;
use DB;

class EvaluationAssignee extends CoreSeeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        if ($this->checkExistsSeed()) {
            return true;
        }
        MeEvaluation::whereIn('status', [MeEvaluation::STT_APPROVED, MeEvaluation::STT_CLOSED])
                ->where(DB::raw('assignee'), '!=', DB::raw('employee_id'))
                ->update(['assignee' => DB::raw('employee_id')]);
        $this->insertSeedMigrate();
    }

}																		