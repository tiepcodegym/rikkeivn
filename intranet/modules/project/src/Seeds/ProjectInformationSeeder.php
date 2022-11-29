<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjPointFlat;

class ProjectInformationSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('ProjectInformation-v2')) {
            return true;
        }
        ProjPointFlat::flatAllProject();
        $this->insertSeedMigrate();
    }
}
