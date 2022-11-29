<?php

namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Project\Model\MeAttribute;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EvaluationUpdatePoint extends CoreSeeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return;
        }
        //$evalTime = Carbon::now()->startOfMonth()->toDateTimeString();
        $evalTime = '2018-08-01';
        $meItems = MeEvaluation::with(['project', 'employee'])
            ->whereDate('eval_time', '=', $evalTime)
            ->get();
        if ($meItems->isEmpty()) {
            return;
        }

        $attrsNormal = MeAttribute::where('group', MeAttribute::GR_NORMAL)->get();
        $attrsPerform = MeAttribute::where('group', MeAttribute::GR_PERFORM)->get();
        if ($attrsNormal->isEmpty() || $attrsPerform->isEmpty()) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach ($meItems as $item) {
                $isMeTeam = null;
                if ($item->team_id) {
                    $isMeTeam = true;
                } else if ($item->project_id && $item->project) {
                    $isMeTeam = false;
                }
                if ($isMeTeam !== null) {
                    MeEvaluation::updateMEPoint($item, $attrsNormal, $attrsPerform, null, $isMeTeam); 
                }
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

}
