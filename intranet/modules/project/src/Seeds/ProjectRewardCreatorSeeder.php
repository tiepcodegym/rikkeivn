<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\Project;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Project\Model\Task;

class ProjectRewardCreatorSeeder extends CoreSeeder
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
        $projects = Project::where('state', Project::STATE_CLOSED)
            ->whereIn('type', [Project::TYPE_BASE, Project::TYPE_OSDC])
            ->where('status', Project::STATUS_APPROVED)
            ->get();
        if (!$projects || !count($projects)) {
            $this->insertSeedMigrate();
            return true;
        }
        DB::beginTransaction();
        try {
            foreach ($projects as $project) {
                Task::createReward($project, [
                    'send_email' => true
                ]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
