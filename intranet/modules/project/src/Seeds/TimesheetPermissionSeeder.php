<?php

namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\DB;

class TimesheetPermissionSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $action = Action::where('name', 'timesheet.eval.upload')->first();
        if (!$action) {
            return;
        }
        $teamHr = Team::where('code', Team::CODE_HC_TH)->first();
        if (!$teamHr) {
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
                'scope' => Permission::SCOPE_TEAM
            ]
        ];
        DB::beginTransaction();
        try {
            foreach ($dataInsert as $data) {
                $item = Permission::where('role_id', $data['role_id'])
                        ->where('team_id', $teamHr->id)
                        ->where('action_id', $action->id)
                        ->first();
                if (!$item) {
                    $data['team_id'] = $teamHr->id;
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

