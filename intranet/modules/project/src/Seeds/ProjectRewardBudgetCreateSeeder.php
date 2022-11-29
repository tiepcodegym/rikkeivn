<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\Project;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Project\Model\ProjRewardBudget;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Core\View\CacheHelper;

class ProjectRewardBudgetCreateSeeder extends CoreSeeder
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
        $collection = Project::where('state', '!=', Project::STATE_NEW)
            ->where('status', Project::STATUS_APPROVED)
            ->where('type', Project::TYPE_BASE)
            ->get();
        if (!count($collection)) {
            $this->insertSeedMigrate();
            return true;
        }
        DB::beginTransaction();
        try {
            foreach ($collection as $project) {
                $data = [];
                $evaluationKey = array_keys(ProjectPoint::evaluationLabel());
                $rewardMetaConfig = CoreConfigData::get('project.reward');
                $data['projectPoint'] = ProjectPoint::findFromProject($project->id);
                $projectQuality = ProjQuality::getFollowProject($project->id);
                $billableEffort = $data['projectPoint']->getCostBillableEffort($projectQuality);
                if ($project->type_mm == Project::MD_TYPE) {
                    $billableEffort = round(
                        $billableEffort / 
                        (float) CoreConfigData::get('project.mm'),
                    2);
                }
                $rewardEvalUnitConfig = $project->getRewardEvalUnitConfig($rewardMetaConfig);
                foreach ($evaluationKey as $key) {
                    $countReward = ProjRewardBudget::where('project_id', $project->id)
                        ->where('level', $key)
                        ->count();
                    if (!$countReward) {
                        $rewardBudget = $rewardEvalUnitConfig[$key] * $billableEffort;
                        ProjRewardBudget::insert([
                            'project_id' => $project->id,
                            'level' => $key,
                            'reward' => $rewardBudget
                        ]);
                    }
                }
                CacheHelper::forget(ProjRewardBudget::KEY_CACHE, $project->id);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
