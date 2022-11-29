<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Team\Model\EmployeeTeamHistory;
use DB;

class UpdateDeletedEmployeeTeamHistorySeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
            $empDeleted = DB::table('employees')->whereNotNull('deleted_at')->select('id', 'deleted_at')->get();
            if (!empty($empDeleted)) {
                foreach($empDeleted as $emp) {
                    EmployeeTeamHistory::where('employee_id', $emp->id)->update(['deleted_at' => $emp->deleted_at]);
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
