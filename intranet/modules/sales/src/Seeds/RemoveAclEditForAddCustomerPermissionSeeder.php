<?php
namespace Rikkei\Sales\Seeds;

use DB;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Action;

class RemoveAclEditForAddCustomerPermissionSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
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
        DB::table('actions')->whereIn('name', ['add.customer-route.child.sales::customer.edit', 'add.customer-route.child.sales::customer.merge'])->delete();
        DB::table('actions')->where('name', 'add.customer')->update([
            'description' => 'Create customer',
        ]);
        $action = Action::where('name', 'edit.customer')->first();
        $actionList = Action::where('name', 'list.customer')->first();
        $teams = Team::whereIn('name', ['Sales', 'PQA'])->select('id', 'name')->get();
        $dataInsert = [];
        foreach ($teams as $team) {
            $dataInsert[] = [
                'role_id' => 3,
                'team_id' => $team->id,
                'action_id' => $action->id,
                'scope' => Permission::SCOPE_SELF,
            ];
            $dataInsert[] = [
                'role_id' => 2,
                'team_id' => $team->id,
                'action_id' => $action->id,
                'scope' => Permission::SCOPE_SELF,
            ];
            if ($team->name == 'PQA') {
                $dataInsert[] = [
                    'role_id' => 1,
                    'team_id' => $team->id,
                    'action_id' => $action->id,
                    'scope' => Permission::SCOPE_SELF,
                ];
                $dataInsert[] = [
                    'role_id' => 1,
                    'team_id' => $team->id,
                    'action_id' => $actionList->id,
                    'scope' => Permission::SCOPE_SELF,
                ];
                $dataInsert[] = [
                    'role_id' => 2,
                    'team_id' => $team->id,
                    'action_id' => $actionList->id,
                    'scope' => Permission::SCOPE_SELF,
                ];
                $dataInsert[] = [
                    'role_id' => 3,
                    'team_id' => $team->id,
                    'action_id' => $actionList->id,
                    'scope' => Permission::SCOPE_SELF,
                ];
            }
            if ($team->name == 'Sales') {
                $dataInsert[] = [
                    'role_id' => 1,
                    'team_id' => $team->id,
                    'action_id' => $action->id,
                    'scope' => Permission::SCOPE_COMPANY,
                ];
            }
        }
        Permission::insert($dataInsert);
        $this->insertSeedMigrate();
    }
}
