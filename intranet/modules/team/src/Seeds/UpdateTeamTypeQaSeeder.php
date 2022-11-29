<?php

namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Team;

class UpdateTeamTypeQaSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        DB::table('teams')->where('name', 'QA')
            ->update(['type' => Team::TEAM_TYPE_QA]);
        $this->insertSeedMigrate();
    }
}
