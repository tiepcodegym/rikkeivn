<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;
use Rikkei\Project\Model\SaleProject;
use Rikkei\Project\Model\Project;

class RemoveSalerOfProjectDraftSeeder extends CoreSeeder
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
        DB::beginTransaction();
        try {
            $projectsDraft = Project::where('status', '!=', Project::STATUS_APPROVED)
                                    ->whereNotNull('parent_id')
                                    ->select('id')
                                    ->get();
            $idProjsDraft = [];
            if (count($projectsDraft)) {
                foreach ($projectsDraft as $proj) {
                    $idProjsDraft[] = $proj->id;
                }
            }
            if (count($idProjsDraft)) {
                SaleProject::whereIn('project_Id', $idProjsDraft)->delete();
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
