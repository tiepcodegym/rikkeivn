<?php

namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\DB;

class ExportCddSearchPermissionSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $action = Action::where('name', 'search.export.candidate')->first();
        if (!$action) {
            return;
        }
        $team = Team::where('type', Team::TEAM_TYPE_HR)->first();
        if (!$team) {
            return;
        }
        $roleId = Team::ROLE_TEAM_LEADER;
        $scope = Permission::SCOPE_SELF;

        DB::beginTransaction();
        try {
            $item = Permission::where('role_id', $roleId)
                    ->where('team_id', $team->id)
                    ->where('action_id', $action->id)
                    ->first();
            if (!$item) {
                Permission::create([
                    'role_id' => $roleId,
                    'action_id' => $action->id,
                    'scope' => $scope,
                    'team_id' => $team->id
                ]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

}

