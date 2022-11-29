<?php

namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Project\Model\MeEvaluation;
use Carbon\Carbon;
use DB;

class MEConvertPoint extends CoreSeeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        
        $oldMaxPoint = 1.3;
        $defaulOldPoint = 1;
        
        if ($this->checkExistsSeed('MEConvertPoint-v1')) {
            return;
        }
        
        $time = Carbon::createFromDate(2017, 3, 31)->setTime(23, 59, 59);
        $meItems = MeEvaluation::with(['meAttributes', 'project'])
                ->where('eval_time', '<=', $time->toDateTimeString())
                ->select('id', 'project_id')
                ->get();
        if ($meItems->isEmpty()) {
            return;
        }
        $attrs_normal = MeAttribute::getNormalAttrs();
        $attrs_perform = MeAttribute::getPerformAttrs();
        
        DB::beginTransaction();
        try {
            foreach ($meItems as $item) {
                foreach ($attrs_perform as $attr) {
                    $evalAttr = $item->meAttributes()->wherePivot('attr_id', $attr->id)->first();
                    if ($evalAttr) {
                        $oldPoint = $evalAttr->pivot->point;
                        $newPoint = round($oldPoint * MeEvaluation::MAX_POINT / $oldMaxPoint);
                        $item->meAttributes()->updateExistingPivot($attr->id, ['point' => $newPoint]);
                    } else {
                        $newPoint = round($defaulOldPoint * MeEvaluation::MAX_POINT / $oldMaxPoint);
                        $item->meAttributes()->attach([$attr->id => ['point' => $newPoint]]);
                    }
                }
                $isMeTeam = null;
                if ($item->project_id && $item->project) {
                    $isMeTeam = false;
                } else if ($item->team_id) {
                    $isMeTeam = true;
                }
                if ($isMeTeam !== null) {
                    MeEvaluation::updateMEPoint($item, $attrs_normal, $attrs_perform, null, $isMeTeam);
                }
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

}																		
