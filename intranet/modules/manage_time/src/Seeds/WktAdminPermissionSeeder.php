<?php

namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\DB;

class WktAdminPermissionSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $action = Action::where('name', 'manage.working_times')->first();
        if (!$action) {
            return;
        }
        $teams = Team::whereIn('code', [Team::CODE_PREFIX_HN, Team::CODE_PREFIX_DN, Team::CODE_PREFIX_JP])
                ->get();
        if ($teams->isEmpty()) {
            return;
        }
        $dataInsert = [
            [
                'role_id' => Team::ROLE_MEMBER,
                'scope' => Permission::SCOPE_TEAM
            ],
            [
                'role_id' => Team::ROLE_SUB_LEADER,
                'scope' => Permission::SCOPE_TEAM,
            ],
            [
                'role_id' => Team::ROLE_TEAM_LEADER,
                'scope' => Permission::SCOPE_TEAM
            ]
        ];

        DB::beginTransaction();
        try {
            foreach ($teams as $team) {
                foreach ($dataInsert as $data) {
                    $item = Permission::where('role_id', $data['role_id'])
                            ->where('team_id', $team->id)
                            ->where('action_id', $action->id)
                            ->first();
                    if (!$item) {
                        Permission::create([
                            'role_id' => $data['role_id'],
                            'action_id' => $action->id,
                            'scope' => $team->code == Team::CODE_PREFIX_HN ? Permission::SCOPE_COMPANY : $data['scope'],
                            'team_id' => $team->id
                        ]);
                    }
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

