<?php
namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\ManageTime\Model\LeaveDayReason;
use DB;

class LeaveDayReasonWfhSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return;
        }
        $data = [
            'name' => 'Nghỉ hưởng lương cơ bản',
            'salary_rate' => 100,
            'team_type' => LeaveDayReason::TEAM_TYPE_VN,
            'used_leave_day' => 0,
            'type' => LeaveDayReason::BASIC_TYPE,
        ];
        DB::beginTransaction();
        try {
            LeaveDayReason::updateOrCreate([
                'name' => $data['name']
            ], $data);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
