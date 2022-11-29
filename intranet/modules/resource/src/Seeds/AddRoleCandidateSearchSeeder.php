<?php
namespace Rikkei\Resource\Seeds;

use Illuminate\Database\Seeder;
use DB;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Permission;

class AddRoleCandidateSearchSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    private $actionName = 'search.candidate';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        DB::beginTransaction();
        try {
            //get teams
            $teams = Team::where('follow_team_id', 0)
                        ->where('is_function', 1)
                        ->select('id', 'type', 'leader_id')
                        ->get();
            //get action search candidate
            $action = Action::where('name', $this->actionName)->select('id')->first();
            if (!empty($teams) && !empty($action)) {
                $data = [];
                foreach ($teams as $team) {
                    if ($team->type == Team::TEAM_TYPE_HR) {
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
                            'scope' => 1
                        ];
                        $data[] = [
                            'team_id' => $team->id,
                            'action_id' => $action->id,
                            'role_id' => 3,
                            'scope' => 1
                        ];
                    } else {
                        $data[] = [
                            'team_id' => $team->id,
                            'action_id' => $action->id,
                            'role_id' => 1,
                            'scope' => 2
                        ];
                        $data[] = [
                            'team_id' => $team->id,
                            'action_id' => $action->id,
                            'role_id' => 2,
                            'scope' => 1
                        ];
                        $data[] = [
                            'team_id' => $team->id,
                            'action_id' => $action->id,
                            'role_id' => 3,
                            'scope' => 1
                        ];
                    }
                }
                Permission::insert($data);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            $ex.getMessage();
        }
        
    }
}
