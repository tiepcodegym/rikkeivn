<?php

namespace Rikkei\Notify\Seeds;

use Carbon\Carbon;
use Exception;
use Log;
use Rikkei\Core\Seeds\CoreSeeder;
use DB;

class NotificationCategoriesSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return bool
     * @throws Exception
     */
    public function run()
    {
        if ($this->checkExistsSeed(3)) {
            return true;
        }
        DB::beginTransaction();
        try {
            $data = [
                [
                    'id' => 1,
                    'name' => 'Admin',
                    'description' => 'Thông báo sự kiện (thông báo từ admin):  sự kiện, lịch nghỉ lễ, thông tin quan trọng của công ty.',
                    'name_en' => 'Admin',
                    'is_important' => 1,
                    'priority' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'id' => 2,
                    'name' => 'Hoạt động định kỳ',
                    'name_en' => 'Monthly activity',
                    'description' => 'Điền hoạt động ME, Thông báo phiếu lương, chấm công, tiền phạt nội quy, chúc mừng sinh nhật, lễ tết',
                    'is_important' => 0,
                    'priority' => 2,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'id' => 3,
                    'name' => 'Chấm công',
                    'name_en' => 'Timekeeping',
                    'description' => 'thông báo duyệt, đăng ký đơn nghỉ phép, chấm công, làm thêm, công tác',
                    'is_important' => 0,
                    'priority' => 3,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'id' => 4,
                    'name' => 'Dự án',
                    'name_en' => 'Project',
                    'description' => 'CSS, Project, Work Order',
                    'is_important' => 0,
                    'priority' => 4,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'id' => 5,
                    'name' => 'Nhân sự',
                    'name_en' => 'HR',
                    'description' => 'effort nhân viên trong tháng, thông tin ứng viên, điền review checkpoint...',
                    'is_important' => 0,
                    'priority' => 5,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'id' => 6,
                    'name' => 'Khác',
                    'name_en' => 'Other',
                    'description' => 'Thông báo liên quan cấp phát, mất hỏng tài sản, thông báo có tin tức mới',
                    'is_important' => 0,
                    'priority' => 6,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'id' => 7,
                    'name' => 'Tin tức',
                    'name_en' => 'News',
                    'description' => 'Thông báo có các bài tin tức mới.',
                    'is_important' => 0,
                    'priority' => 7,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'id' => 8,
                    'name' => 'Chợ',
                    'name_en' => 'Market',
                    'description' => 'Thông báo về các mặt hàng trong chợ',
                    'is_important' => 0,
                    'priority' => 8,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
            ];
            foreach ($data as $datum) {
                $cate = DB::table('notification_categories')->where('id', $datum['id']);
                if ($cate->first()) {
                    $cate->update($datum);
                } else {
                    DB::table('notification_categories')->insert($datum);
                }
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
        }
    }
}
