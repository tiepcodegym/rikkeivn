<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectPoint;

class ProjectPointCssSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('2')) {
            return true;
        }
        $collection = Project::select('id')
            ->get();
        if (!count($collection)) {
            return;
        }
        foreach ($collection as $project) {
            $projectPoint = ProjectPoint::findFromProject($project->id);
            $cssPoint = $projectPoint->getCssCssFromCssResult();
            $projectPoint->css_css = $cssPoint;
            $projectPoint->save([], [
                'not_employee' => 1
            ]);
        }
        $this->insertSeedMigrate();
    }
}
