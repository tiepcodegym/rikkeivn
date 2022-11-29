<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;
use Rikkei\Project\Model\SaleProject;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Employee;

class AddSalerToProjectSeeder extends CoreSeeder
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
            $projNotSale = Project::getProjectsHasNotSale();
            if (!empty($projNotSale)) {
                $saleDefault = Employee::getEmpByEmail('anhptl@rikkeisoft.com');
                if (!empty($saleDefault)) {
                    $data = [];
                    foreach ($projNotSale as $proj) {
                        $data[] = [
                            'employee_id' => $saleDefault->id,
                            'project_id' => $proj->id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                    SaleProject::insert($data);
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
