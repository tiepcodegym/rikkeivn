<?php

namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Project\Model\MeEvaluation;
use Carbon\Carbon;
use DB;

class MEReCalAvgPoint extends CoreSeeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }

        $projIds = [2591];
        $time = Carbon::now()->subMonth()->firstOfMonth();
        $meItems = MeEvaluation::with(['meAttributes'])
                ->where(DB::raw('DATE(eval_time)'), '=', $time->toDateString());
        if ($projIds) {
            $meItems->whereIn('project_id', $projIds);
        }
        $meItems = $meItems->get();

        if ($meItems->isEmpty()) {
            return;
        }

        $attrsNormal = MeAttribute::getNormalAttrs();
        $attrsPerform = MeAttribute::getPerformAttrs();

        DB::beginTransaction();
        try {
            foreach ($meItems as $item) {
                $isMeTeam = null;
                if ($item->project_id && $item->project) {
                    $isMeTeam = false;
                } else if ($item->team_id) {
                    $isMeTeam = true;
                }
                if ($isMeTeam !== null) {
                    $this->updateMePoint($item, $attrsNormal, $attrsPerform, $isMeTeam);
                }
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
            throw $ex;
        }
    }

    public function updateMePoint(
        $item,
        $attrsNormal,
        $attrsPerform,
        $isMeTeam
    )
    {
        $pointRule = 0;
        $normalWeight = 0;
        foreach ($attrsNormal as $attr) {
            $normalWeight += $attr->weight;
            $pointRule += $item->getPoint($attr)['point'] * $attr->weight;
        }
        $pointRule = round($pointRule / ($normalWeight * 2), 2);

        //individua index
        $pointIndividual = 0;
        $individualWeight = 0;
        $individualCount = 0;
        foreach ($attrsPerform as $attr) {
            $attrPoint = $item->getPoint($attr)['point'];
            $individualWeight += $attr->weight;
            if ($attrPoint > MeAttribute::NA) {
                $pointIndividual += $attrPoint;
                $individualCount ++;
            }
        }
        $pointIndividual = $pointIndividual / $individualCount;

        $projectPoint = $item->proj_point;
        $projectIndex = $item->proj_index;
        if ($isMeTeam && !$projectIndex) {
            $projectIndex = 1;
        }

        //point performance
        $ppPoint = min([$projectPoint * $projectIndex * MeEvaluation::MAX_POINT / MeEvaluation::MAX_PP, MeEvaluation::MAX_POINT]);
        $ppWeight = 100 - $normalWeight - $individualWeight;

        //caculate summary point
        $sumary = $pointRule * $normalWeight + $pointIndividual * $individualWeight + $ppPoint * $ppWeight;
        $sumary = round($sumary / 100, 2);

        $item->avg_point = $sumary;
        return $item->save();
    }

}																		
