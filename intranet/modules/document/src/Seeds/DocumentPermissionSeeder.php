<?php

namespace Rikkei\Document\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Action;
use Illuminate\Support\Facades\DB;

class DocumentPermissionSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed(4)) {
            return;
        }
        $actions = Action::whereIn('name', ['doc.manage', 'doc.type.manage', 'doc.request.manage'])->get();
        if ($actions->isEmpty()) {
            return;
        }
        $teamPQA = Team::where('type', Team::TEAM_TYPE_PQA)->first();
        if (!$teamPQA) {
            return;
        }
        $dataInsert = [
            [
                'role_id' => Team::ROLE_MEMBER,
                'scope' => Permission::SCOPE_SELF
            ],
            [
                'role_id' => Team::ROLE_SUB_LEADER,
                'scope' => Permission::SCOPE_TEAM,
            ],
            [
                'role_id' => Team::ROLE_TEAM_LEADER,
                'scope' => Permission::SCOPE_COMPANY
            ]
        ];
        DB::beginTransaction();
        try {
            foreach ($actions as $action) {
                foreach ($dataInsert as $data) {
                    $item = Permission::where('role_id', $data['role_id'])
                            ->where('team_id', $teamPQA->id)
                            ->where('action_id', $action->id)
                            ->first();
                    if (!$item) {
                        $data['team_id'] = $teamPQA->id;
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
