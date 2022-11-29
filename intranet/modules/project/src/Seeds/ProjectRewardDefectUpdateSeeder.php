<?php

namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Project;
use Exception;
use Rikkei\Project\View\ProjectRedmine;
use Rikkei\Project\Model\SourceServer;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\ProjRewardMeta;

class ProjectRewardDefectUpdateSeeder extends CoreSeeder 
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        if ($this->checkExistsSeed()) {
            return;
        }
        $tasksReawrd = Task::select('id', 'project_id', 'status')
            ->where('type', Task::TYPE_REWARD)
            ->where('status', Task::STATUS_NEW)
            ->get();
        if (!count($tasksReawrd)) {
            $this->insertSeedMigrate();
            return;
        }
        DB::beginTransaction();
        try {
            foreach ($tasksReawrd as $taskReward) {
                // -- get redmine defect error
                $project = $taskReward->getProject();
                // project not close, not approve, not base => continyue
                if (!$project || 
                    $project->state != Project::STATE_CLOSED ||
                    $project->status != Project::STATUS_APPROVED ||
                    $project->type != Project::TYPE_BASE
                ) {
                    continue;
                }
                
                $projectSourceInfo = SourceServer::getSourceServer($project->id);
                // project not connect redmine => continue
                if (!$projectSourceInfo->is_check_redmine || 
                    !$projectSourceInfo->id_redmine
                ) {
                    continue;
                }
                $redmince = ProjectRedmine::getInstance();
                $bug = $redmince->countBug($projectSourceInfo);
                $projectPoint = ProjectPoint::findFromProject($project->id);
                $projectPoint->qua_defect_reward_errors = $bug['defect_reward'];
                $projectPoint->save([], [
                    'project' => $project,
                    'not_employee' => true
                ]);
                
                // update reward
                $rewardMeta = ProjRewardMeta::getRewardMeta($taskReward);
                if (!$rewardMeta) {
                    continue;
                }
                $rewardMeta->count_defect = $bug['defect_reward'];
                $rewardMeta->save();
            }
            
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
