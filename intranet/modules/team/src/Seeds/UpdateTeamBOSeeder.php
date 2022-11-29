<?php

namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Team;

class UpdateTeamBOSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        DB::table('teams')->where('name', 'Production')->orWhere(function ($q) {
                $q->where('id', '!=', 1)
                ->whereNull('is_soft_dev')
                ->where('is_branch', 0);
            })
            ->update(['is_bo' => true]);
        $this->insertSeedMigrate();
    }
}
