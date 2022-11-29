<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Role;

class EditEmployeeCodeSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(5)) {
            return true;
        }
        $model = new Permission();
        $model->role_id = Role::where('role', 'Team Leader')->select('id')->first()->id;
        $model->scope = Permission::SCOPE_COMPANY;
        $model->action_id = Action::where('name', 'edit.employee_code.member')
                ->select('id')->first()->id;
        $model->team_id = Team::where('type', Team::TEAM_TYPE_HR)
                ->select('id')->first()->id;
        $model->save();
        $this->insertSeedMigrate();
    }
}
