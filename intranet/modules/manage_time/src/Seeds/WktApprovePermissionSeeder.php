<?php

namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\DB;

class WktApprovePermissionSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $action = Action::where('name', 'working_time.approve')->first();
        if (!$action) {
            return;
        }
        $teams = Team::where('is_function', 1)
                ->where(function ($query) {
                    $query->where('follow_team_id', 0)
                            ->orWhereNull('follow_team_id');
                })
                ->get();
        if ($teams->isEmpty()) {
            return;
        }
        $roleId = Team::ROLE_TEAM_LEADER;
        $scope = Permission::SCOPE_SELF;

        DB::beginTransaction();
        try {
            foreach ($teams as $team) {
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
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

}

