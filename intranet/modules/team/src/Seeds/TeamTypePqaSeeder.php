<?php

namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Team;

class TeamTypePqaSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        DB::table('teams')->where('name', 'PQA')
            ->update(['type' => Team::TEAM_TYPE_PQA]);
        $this->insertSeedMigrate();
    }
}
