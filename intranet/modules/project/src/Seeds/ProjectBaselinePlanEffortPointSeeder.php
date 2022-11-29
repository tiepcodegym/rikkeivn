<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjPointBaseline;
use Rikkei\Project\Model\Project;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Project\Model\ProjectPoint;

class ProjectBaselinePlanEffortPointSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $blTable = ProjPointBaseline::getTableName();
        $projTable = Project::getTableName();
        
        $attrsPoint = [
            'cost_plan_effort_total_point',
            'cost_effort_effectiveness_point',
            'cost_busy_rate_point',
            'cost_effort_efficiency2_point',
            'css_css_point',
            'tl_schedule_point',
            'tl_deliver_point',
            'qua_defect_point',
            'qua_leakage_point',
            'proc_compliance_point',
            'proc_report_point'
        ];
        $collection = ProjPointBaseline::select($blTable.'.id',$blTable.'.project_id',
            $blTable.'.cost_plan_effort_total', $blTable.'.point_total')
            ->addSelect($attrsPoint)
            ->join($projTable, $projTable.'.id', '=', $blTable.'.project_id')
            ->where($projTable.'.type_mm', Project::MD_TYPE)
            ->where($projTable.'.state', Project::STATE_PROCESSING)
            ->where($projTable.'.status', Project::STATUS_APPROVED)
            ->whereIn($projTable.'.type', [Project::TYPE_OSDC, Project::TYPE_BASE])
            ->whereDate($blTable.'.created_at', '>=','2017-06-19')
            ->whereDate($blTable.'.created_at', '<','2017-06-26')
            ->whereNull($projTable.'.deleted_at')
            ->get();
        if (!count($collection)) {
            return true;
        }
        DB::beginTransaction();
        try {
            foreach ($collection as $item) {
                $project = new Project();
                $project->type_mm = Project::MD_TYPE;
                $projectPoint = new ProjectPoint();
                
                $item->cost_plan_effort_total_point = $projectPoint
                    ->getCostPlanEffortTotalPoint(
                        $item->cost_plan_effort_total, 
                        $project
                );
                $item->save();
                ProjPointBaseline::updatePointBaselineItem($item);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
