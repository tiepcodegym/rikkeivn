<?php

namespace Rikkei\Event\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\DB;

class SalaryPermissionSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $action = Action::where('name', 'event.send.mail.salary')->first();
        if (!$action) {
            return;
        }
        $teamHcth = Team::where('code', Team::CODE_HC_TH)->first();
        if (!$teamHcth) {
            return;
        }
        $dataInsert = [
            [
                'role_id' => Team::ROLE_TEAM_LEADER,
                'scope' => Permission::SCOPE_TEAM
            ]
        ];
        DB::beginTransaction();
        try {
            foreach ($dataInsert as $data) {
                $item = Permission::where('role_id', $data['role_id'])
                        ->where('team_id', $teamHcth->id)
                        ->where('action_id', $action->id)
                        ->first();
                if (!$item) {
                    $data['team_id'] = $teamHcth->id;
                    $data['action_id'] = $action->id;
                    Permission::create($data);
                } else {
                    $item->update($data);
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
