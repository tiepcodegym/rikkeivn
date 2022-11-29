<?php

namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\ManageTime\Model\LeaveDayReason;
use Illuminate\Support\Facades\DB;

class UpdateLeaveDayReasonsTableSeeder extends CoreSeeder
{
    /**
     * Duplicate các bản ghi có type = 0 và update team_type = 2 (JP)
     * Create thêm các bản ghi thuộc team VN
     * 1. Nghỉ cưới ( tối đa 3 ngày)
     * 2. Bố mẹ, con chết (tối đa 3 ngày)
     * 3. Con kết hôn: nghỉ tối đa 1 ngày.
     * 4. Nghỉ thai sản: tối đa 6 tháng.
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
            // Default team_type = 1 (JP)
            // Duplicate các bản ghi có type = 0 và update team_type = 2 (VN)
            $results = LeaveDayReason::where('type', '=', LeaveDayReason::NORMAL_TYPE)->get();
            foreach ($results as $leaveReason) {
                $newLeaveReason = $leaveReason->replicate();
                $newLeaveReason->team_type = LeaveDayReason::TEAM_TYPE_VN;

                $newLeaveReason->save();
            }

            // Create thêm các bản ghi thuộc team VN
            $data = [
                [
                    'name' => 'Nghỉ cưới',
                    'salary_rate' => '100',
                    'sort_order' => '2',
                    'used_leave_day' => '0',
                    'type' => LeaveDayReason::SPECIAL_TYPE,
                    'repeated' => '0',
                    'unit' => '',
                    'value' => '3',
                    'team_type' => LeaveDayReason::TEAM_TYPE_VN,
                    'calculate_full_day' => '0',
                ],
                [
                    'name' => 'Bố mẹ, vợ/chồng, con cái qua đời',
                    'salary_rate' => '100',
                    'sort_order' => '3',
                    'used_leave_day' => '0',
                    'type' => LeaveDayReason::SPECIAL_TYPE,
                    'repeated' => '0',
                    'unit' => '',
                    'value' => '3',
                    'team_type' => LeaveDayReason::TEAM_TYPE_VN,
                    'calculate_full_day' => '0',
                ],
                [
                    'name' => 'Con của nhân viên kết hôn',
                    'salary_rate' => '100',
                    'sort_order' => '4',
                    'used_leave_day' => '0',
                    'type' => LeaveDayReason::SPECIAL_TYPE,
                    'repeated' => '0',
                    'unit' => '',
                    'value' => '1',
                    'team_type' => LeaveDayReason::TEAM_TYPE_VN,
                    'calculate_full_day' => '0',
                ],
                [
                    'name' => 'Nghỉ thai sản',
                    'salary_rate' => '0',
                    'sort_order' => '5',
                    'used_leave_day' => '0',
                    'type' => LeaveDayReason::SPECIAL_TYPE,
                    'repeated' => '0',
                    'unit' => '',
                    'value' => '180',
                    'team_type' => LeaveDayReason::TEAM_TYPE_VN,
                    'calculate_full_day' => '1',
                ],
            ];

            foreach ($data as $row) {
                LeaveDayReason::create($row);
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $e) {
            \Log::error($e);
            DB::rollback();
        }
    }
}
