<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\Project;

class ProjectCodeAutoSeeder extends CoreSeeder
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
        $projects = Project::get();
        foreach ($projects as $project) {
            $project->renderProjectCodeAuto();
        }
        $this->insertSeedMigrate();
    }
}
