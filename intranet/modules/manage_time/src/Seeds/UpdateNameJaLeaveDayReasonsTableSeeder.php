<?php

namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\ManageTime\Model\LeaveDayReason;
use Illuminate\Support\Facades\DB;

class UpdateNameJaLeaveDayReasonsTableSeeder extends CoreSeeder
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
        if ($this->checkExistsSeed(6)) {
            return true;
        }

        DB::beginTransaction();
        try {
            // Create thêm các bản ghi thuộc team VN
            $data = [
                [
                    'name' => 'Nghỉ có phép',
                    'name_ja' => '許可休暇'
                ],
                [
                    'name' => 'Nghỉ không phép',
                    'name_ja' => '無断欠勤',
                ],
                [
                    'name' => 'Nghỉ hưởng lương cơ bản',
                    'name_ja' => '有給休暇 基本給',
                ],
                [
                    'name' => 'Nghỉ cưới',
                    'name_ja' => '結婚休暇',
                ],
                [
                    'name' => 'Bố mẹ, vợ/chồng, con cái qua đời',
                    'name_ja' => '思いやり休暇（両親、夫婦、子供が亡くなる）',
                ],
                [
                    'name' => 'Con của nhân viên kết hôn',
                    'name_ja' => '従業員の子供が結婚',
                ],
                [
                    'name' => 'Nghỉ thai sản',
                    'name_ja' => '産休',
                ],
                [
                    'name' => 'Nhân viên kết hôn',
                    'name_ja' => '結婚休暇（3日）',
                ],
                [
                    'name' => 'Vợ của nhân viên sinh em bé',
                    'name_ja' => '産休（夫の場合)',
                ],
                [
                    'name' => 'Ông bà, anh chị em, bố mẹ của chồng/vợ qua đời',
                    'name_ja' => '思いやり休暇（両親、義理の両親、夫婦、兄弟、子供が亡くなる）',
                ],
                [
                    'name' => 'Đi khám thai (Dưới 23 tuần)',
                    'name_ja' => '妊婦健診（23週未満）',
                ],
                [
                    'name' => 'Đi khám thai (Từ 24-35 tuần)',
                    'name_ja' => '妊婦健診（24週～35週）',
                ],
                [
                    'name' => 'Đi khám thai (Từ 36 tuần đến khi sinh)',
                    'name_ja' => '妊婦健診（36週～出産まで）',
                ],
                [
                    'name' => 'Nghỉ obon',
                    'name_ja' => 'お盆の日',
                ],
                [
                    'name' => 'Nghỉ hè (bao gồm nghỉ Obon)',
                    'name_ja' => '有給休暇（お盆含む）',
                ],
                [
                    'name' => 'Nghỉ phép lương cơ bản',
                    'name_ja' => '基本給休暇',
                ]
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
