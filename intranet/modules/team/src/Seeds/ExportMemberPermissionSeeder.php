<?php

namespace Rikkei\Team\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\DB;

class ExportMemberPermissionSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $action = Action::where('name', 'export.team.member')->first();
        if (!$action) {
            return;
        }
        $teamHc = Team::where('code', Team::CODE_HC_TH)->first();
        if (!$teamHc) {
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
                $item = Permission::where('role_id', $data['role_id'])
                        ->where('team_id', $teamHc->id)
                        ->where('action_id', $action->id)
                        ->first();
                if (!$item) {
                    $data['team_id'] = $teamHc->id;
                    $data['action_id'] = $action->id;
                    Permission::create($data);
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
