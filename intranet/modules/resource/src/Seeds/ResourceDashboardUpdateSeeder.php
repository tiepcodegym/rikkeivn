<?php

namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\ResourceDashboard;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\Model\Dashboard;
use Illuminate\Support\Facades\DB;

class ResourceDashboardUpdateSeeder extends CoreSeeder
{
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        DB::beginTransaction();
        try {
            $twelveMonth = View::getMonthsInYear(2017);
            $teamConditions = ['is_soft_dev' => Team::IS_SOFT_DEVELOPMENT];
            $teamList = Team::getTeamList($teamConditions, ['id']);
            $listKey = ResourceDashboard::listChartKey();
            foreach ($listKey as $key) {
                Dashboard::updateData($key, $twelveMonth, 2017);
                foreach ($teamList as $team) {
                    Dashboard::updateData($key, $twelveMonth, 2017, $team->id);
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();
        }
    }
}

