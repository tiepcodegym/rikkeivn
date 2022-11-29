<?php
namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\ManageTime\Model\LeaveDayReason;
use DB;

class LeaveDayReasonSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(4)) {
            return;
        }
        $dataDemo = [
            [
                'name' => 'Nghỉ có phép',
                'salary_rate' => 100,
                'sort_order' => 0,
                'used_leave_day' => 1,
            ],
            [
                'name' => 'Nghỉ không phép',
                'salary_rate' => 0,
                'sort_order' => 1,
                'used_leave_day' => 0,
            ],
            [
                'name' => 'Nhân viên kết hôn',
                'salary_rate' => 100,
                'sort_order' => 2,
                'used_leave_day' => 0,
                'repeated' => 0,
                'value' => 3,
                'type' => 1,
            ],
            [
                'name' => 'Con của nhân viên kết hôn',
                'salary_rate' => 100,
                'sort_order' => 3,
                'used_leave_day' => 0,
                'repeated' => 0,
                'value' => 2,
                'type' => 1,
            ],
            [
                'name' => 'Vợ của nhân viên sinh em bé',
                'salary_rate' => 100,
                'sort_order' => 4,
                'used_leave_day' => 0,
                'repeated' => 0,
                'value' => 2,
                'type' => 1,
            ],
            [
                'name' => 'Bố mẹ, vợ/chồng, con cái qua đời',
                'salary_rate' => 100,
                'sort_order' => 5,
                'used_leave_day' => 0,
                'repeated' => 0,
                'value' => 5,
                'type' => 1,
            ],
            [
                'name' => 'Ông bà, anh chị em, bố mẹ của chồng/vợ qua đời',
                'salary_rate' => 100,
                'sort_order' => 6,
                'used_leave_day' => 0,
                'repeated' => 0,
                'value' => 2,
                'type' => 1,
            ],
            [
                'name' => 'Đi khám thai (Dưới 23 tuần)',
                'salary_rate' => 100,
                'sort_order' => 7,
                'used_leave_day' => 0,
                'repeated' => 4,
                'unit' => 'week',
                'value' => 1,
                'type' => 1,
            ],
            [
                'name' => 'Đi khám thai (Từ 24-35 tuần)',
                'salary_rate' => 100,
                'sort_order' => 8,
                'used_leave_day' => 0,
                'repeated' => 2,
                'unit' => 'week',
                'value' => 1,
                'type' => 1,
            ],
            [
                'name' => 'Đi khám thai (Từ 36 tuần đến khi sinh)',
                'salary_rate' => 100,
                'sort_order' => 9,
                'used_leave_day' => 0,
                'repeated' => 1,
                'unit' => 'week',
                'value' => 1,
                'type' => 1,
            ],
            [
                'name' => 'Nghỉ Obon',
                'salary_rate' => 100,
                'sort_order' => 10,
                'used_leave_day' => 0,
                'repeated' => 0,
                'value' => 3,
                'type' => 1,
            ],
        ];
        DB::beginTransaction();
        try {
            foreach ($dataDemo as $data) {
                if (! DB::table('leave_day_reasons')->select('id')->where('name', $data['name'])->get()) {
                    DB::table('leave_day_reasons')->insert($data);
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
