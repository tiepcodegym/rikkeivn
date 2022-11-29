<?php

namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\DB;

class WktLogPermissionSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $action = Action::where('name', 'working_time.log_times')->first();
        if (!$action) {
            return;
        }
        $teamJapanes = Team::where('code', 'like', Team::CODE_PREFIX_JP)->get();
        if ($teamJapanes->isEmpty()) {
            return;
        }
        $dataInsert = [
            [
                'role_id' => Team::ROLE_MEMBER,
                'scope' => Permission::SCOPE_SELF
            ],
            [
                'role_id' => Team::ROLE_SUB_LEADER,
                'scope' => Permission::SCOPE_SELF,
            ],
            [
                'role_id' => Team::ROLE_TEAM_LEADER,
                'scope' => Permission::SCOPE_SELF
            ]
        ];
        DB::beginTransaction();
        try {
            foreach ($dataInsert as $data) {
                foreach ($teamJapanes as $team) {
                    $item = Permission::where('role_id', $data['role_id'])
                            ->where('team_id', $team->id)
                            ->where('action_id', $action->id)
                            ->first();
                    if (!$item) {
                        $data['team_id'] = $team->id;
                        $data['action_id'] = $action->id;
                        Permission::create($data);
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

