<?php

namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;
use Rikkei\ManageTime\Model\SupplementReasons;

class SupplementReasonsSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed(3)) {
            return;
        }

        $data = [
            ['name' => 'Đi nộp visa', 'sort_order' => 1, 'is_image_required' => 0, 'is_type_other' => 0],
            ['name' => 'Đi lấy thẻ ngoại kiều', 'sort_order' => 2, 'is_image_required' => 0, 'is_type_other' => 0],
            ['name' => 'Tàu trễ', 'sort_order' => 3, 'is_image_required' => 1, 'is_type_other' => 0],
            ['name' => 'Đăng kí địa chỉ/ đổi địa chỉ', 'sort_order' => 4, 'is_image_required' => 0, 'is_type_other' => 0],
            ['name' => 'Đến công ty làm việc', 'sort_order' => 5, 'is_image_required' => 0, 'is_type_other' => 0],
            ['name' => 'Đi công tác theo yêu cầu của công ty', 'sort_order' => 6, 'is_image_required' => 0, 'is_type_other' => 0],
            ['name' => 'Đi phỏng vấn theo yêu cầu công ty', 'sort_order' => 7, 'is_image_required' => 0, 'is_type_other' => 0],
            ['name' => 'Khác', 'sort_order' => 8, 'is_image_required' => 0, 'is_type_other' => 1]
        ];
        DB::beginTransaction();
        try {
            SupplementReasons::insert($data);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

}

