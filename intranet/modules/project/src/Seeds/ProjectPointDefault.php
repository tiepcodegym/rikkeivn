<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\ProjPointFlat;
use Rikkei\Project\Model\Project;

class ProjectPointDefault extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('ProjectPointDefault-v6')) {
            return true;
        }
        $valueDefault = ProjectPoint::pointValueDefault();
        $collection = Project::where('state', Project::STATE_PROCESSING)
            ->where('status', Project::STATUS_APPROVED)
            ->get();
        if (!count($collection)) {
            return;
        }
        foreach ($collection as $item) {
            $projectPoint = ProjectPoint::findFromProject($item->id);
            $projectPoint->setData($valueDefault);
            $projectPoint->save([], [
                'not_employee' => 1
            ]);
        }
        ProjPointFlat::flatAllProject();
        $this->insertSeedMigrate();
    }
}
