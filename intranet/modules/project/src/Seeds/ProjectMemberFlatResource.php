<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjectMember;

class ProjectMemberFlatResource extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('ProjectMemberFlatResource-v4')) {
            return true;
        }
        ProjectMember::flatAllResource();
        $this->insertSeedMigrate();
    }
}
