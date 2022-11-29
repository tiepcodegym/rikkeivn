<?php

namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\ManageTime\Model\LeaveDayReason;
use Illuminate\Support\Facades\DB;

class UpdateNameEnLeaveDayReasonsTableSeeder extends CoreSeeder
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
        if ($this->checkExistsSeed(5)) {
            return true;
        }

        DB::beginTransaction();
        try {
            // Create thêm các bản ghi thuộc team VN
            $data = [
                [
                    'name' => 'Nghỉ có phép',
                    'name_en' => 'Paid Leave'
                ],
                [
                    'name' => 'Nghỉ không phép',
                    'name_en' => 'Unpaid Leave'
                ],
                [
                    'name' => 'Nghỉ hưởng lương cơ bản',
                    'name_en' => 'Basic paid leave'
                ],
                [
                    'name' => 'Nghỉ cưới',
                    'name_en' => 'Marriage leave'
                ],
                [
                    'name' => 'Bố mẹ, vợ/chồng, con cái qua đời',
                    'name_en' => 'Compassionate leave (parents, conjugal, children pass away)',
                ],
                [
                    'name' => 'Con của nhân viên kết hôn',
                    'name_en' => 'Children of employee married',
                ],
                [
                    'name' => 'Nghỉ thai sản',
                    'name_en' => 'Maternity Leave'
                ],
                [
                    'name' => 'Nhân viên kết hôn',
                    'name_en' => 'Marriage leave (3 days)'
                ],
                [
                    'name' => 'Vợ của nhân viên sinh em bé',
                    'name_en' => 'Maternity leave (For husband)'
                ],
                [
                    'name' => 'Ông bà, anh chị em, bố mẹ của chồng/vợ qua đời',
                    'name_en' => 'Compassionate leave (parents, parents in-law, conjugal, sibling, children pass away)'
                ],
                [
                    'name' => 'Đi khám thai (Dưới 23 tuần)',
                    'name_en' => 'Prenatal check-up (under 23 weeks)'
                ],
                [
                    'name' => 'Đi khám thai (Từ 24-35 tuần)',
                    'name_en' => 'Prenatal check-up (from 24 to 35 weeks)'
                ],
                [
                    'name' => 'Đi khám thai (Từ 36 tuần đến khi sinh)',
                    'name_en' => 'Prenatal check-up (from 36 weeks until giving birth)'
                ],
                [
                    'name' => 'Nghỉ obon',
                    'name_en' => 'Obon Day'
                ],
                [
                    'name' => 'Nghỉ hè (bao gồm nghỉ Obon)',
                    'name_en' => 'Vacation (included Obon day)'
                ],
                [
                    'name' => 'Nghỉ phép lương cơ bản',
                    'name_en' => 'Basic salary leave'
                ],
            ];

            foreach ($data as $row) {
                $leaveDay = LeaveDayReason::where('name', $row['name']);
                if ($leaveDay->get()) {
                    $leaveDay->update($row);
                }
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $e) {
            \Log::error($e);
            DB::rollback();
        }
    }
}
