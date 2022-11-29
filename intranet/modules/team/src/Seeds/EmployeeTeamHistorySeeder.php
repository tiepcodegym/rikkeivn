<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\TeamMember;
use DB;
use Rikkei\Team\Model\Employee;

class EmployeeTeamHistorySeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
            $teamMembers = TeamMember::all(['team_id', 'employee_id']);
            $data = [];
            foreach ($teamMembers as $member) {
                $data[] = [
                    'team_id' => $member->team_id,
                    'employee_id' => $member->employee_id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ]; 
            }
            if (count($data)) {
                EmployeeTeamHistory::insert($data);
            }
            
            //get Employee leave job
            $empLeave = Employee::whereNotNull('leave_date')->select('id', 'leave_date')->get();
            //update end at fo employee leave job
            if (!empty($empLeave)) {
                foreach ($empLeave as $leave) {
                    EmployeeTeamHistory::updateEndAt($leave->id, $leave->leave_date);
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
