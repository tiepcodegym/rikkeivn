<?php

namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Team;

class UpdateTeamTypeSaleSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        DB::table('teams')->whereIn('name', ['Sales', 'Rikkei Japan - Sales'])
            ->update(['type' => Team::TEAM_TYPE_SALE]);
        $this->insertSeedMigrate();
    }
}
