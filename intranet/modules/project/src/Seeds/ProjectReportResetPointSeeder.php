<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjPointReport;

class ProjectReportResetPointSeeder extends CoreSeeder
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
        ProjPointReport::whereDate('created_at', '<', '2017-04-14')
            ->update([
                'point' => ProjPointReport::POINT_NULL
            ]);
        $this->insertSeedMigrate();
    }
}
