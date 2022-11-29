<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectPoint;
use Illuminate\Support\Facades\Artisan;

class ProjectPointCssNullSeeder extends CoreSeeder
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
        $tblProj = Project::getTableName();
        $tblPoint = ProjectPoint::getTableName();
        $collection = ProjectPoint::select([$tblPoint.'.*'])
            ->join($tblProj . ' AS t_proj', 't_proj.id', '=', $tblPoint . '.project_id')
            ->where($tblPoint.'.css_css', 0)
            ->where('t_proj.state', Project::STATE_PROCESSING)
            ->whereNull('t_proj.deleted_at')
            ->get();
        if (!count($collection)) {
            return;
        }
        foreach ($collection as $projectPoint) {
            if (!$projectPoint->css_css) {
                $projectPoint->css_css = null;
                $projectPoint->save([], [
                    'not_employee' => 1
                ]);
            }
        }
        $this->insertSeedMigrate();
        Artisan::call('config:cache');
        Artisan::call('cache:clear');
    }
}
