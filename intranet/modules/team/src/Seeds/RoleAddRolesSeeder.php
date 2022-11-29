<?php

namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Role;

class RoleAddRolesSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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

        DB::beginTransaction();
        try {

            $role = Role::where('role', Role::ROLE_VIEW_PROFILE_2_NAME)->first();
            if (!$role) {
                $role = new Role();
                $role->role = Role::ROLE_VIEW_PROFILE_2_NAME;
                $role->description = 'Xem profile nhân viên trừ các nhân viên chưa vào làm việc';
                $role->save();
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
