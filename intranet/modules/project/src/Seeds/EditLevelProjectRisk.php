<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\Risk;
use DB;

class EditLevelProjectRisk extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if ($this->checkExistsSeed(20)) {
            return true;
        }

        DB::table('proj_op_ricks')->whereNotIn('level_important',Risk::getListLevelRisk())
            ->update(['level_important' => Risk::LEVEL_LOW]);
        $this->insertSeedMigrate();
    }
}
