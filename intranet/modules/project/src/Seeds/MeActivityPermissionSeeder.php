<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Action;
use Illuminate\Support\Facades\DB;

class MeActivityPermissionSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(5)) {
            return;
        }
        $action = Action::where('name', 'project.me_activity.view')->first();
        if (!$action) {
            return;
        }
        $rolePm = Role::where('role', 'PM')->first();
        $dataInsert = [
            [
                'role_id' => $rolePm->id,
                'scope' => Permission::SCOPE_SELF
            ],
        ];
        DB::beginTransaction();
        try {
            foreach ($dataInsert as $data) {
                $item = Permission::where('role_id', $data['role_id'])
                        ->where('action_id', $action->id)
                        ->first();
                if (!$item) {
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
