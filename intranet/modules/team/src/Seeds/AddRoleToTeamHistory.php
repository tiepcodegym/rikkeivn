<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\TeamMember;
use DB;

class AddRoleToTeamHistory extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        DB::beginTransaction();
        try {
            $data = TeamMember::select('team_id', 'employee_id', 'role_id')->get();
            foreach ($data as $item) {
                DB::table('employee_team_history')
                    ->where('team_id', $item->team_id)
                    ->where('employee_id', $item->employee_id)
                    ->update(['role_id' => $item->role_id]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }

    }
}
