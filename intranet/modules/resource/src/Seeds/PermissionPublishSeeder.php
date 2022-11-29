<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Permission;

class PermissionPublishSeeder extends CoreSeeder
{
    private $actionName = 'publish.resource request';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('PermissionPublishSeeder-v2')) {
            return true;
        }

        DB::beginTransaction();
        try {
            //get teams HR
            $teams = Team::where('type', Team::TEAM_TYPE_HR)
                        ->select('id')
                        ->get();

            //get action publish to webvn
            $action = Action::where('name', $this->actionName)->select('id')->first();

            //insert to database
            if (!empty($teams) && !empty($action)) {
                $data = [];
                foreach ($teams as $team) {
                    $data[] = [
                        'team_id' => $team->id,
                        'action_id' => $action->id,
                        'role_id' => 1,
                        'scope' => 3
                    ];
                    $data[] = [
                        'team_id' => $team->id,
                        'action_id' => $action->id,
                        'role_id' => 2,
                        'scope' => 2
                    ];
                    $data[] = [
                        'team_id' => $team->id,
                        'action_id' => $action->id,
                        'role_id' => 3,
                        'scope' => 1
                    ];
                }
                Permission::insert($data);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
