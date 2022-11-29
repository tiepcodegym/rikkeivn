<?php

namespace Rikkei\Event\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Seeds\CoreSeeder;

class TurnOffSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $content = CoreConfigData::where('key', 'hr.email_content.turnoff')->first();
        $subject = CoreConfigData::where('key', 'hr.email_subject.turnoff')->first();

        $data[] = [
            'key' => 'hr.email_content.turnoff',
            'value' => 'Chào {{ name }}

Bạn đã để máy qua đêm ngày {{ date }}.

Trong tháng {{ month }} bạn đã không tắt máy {{ n }} lần gồm những ngày {{ listDate }}

Mỗi lần không tắt máy bạn sẽ được đóng góp cho công đoàn 10k.

Đề nghị bạn tắt máy tính trước khi ra về.

Trân trọng cảm ơn!',
        ];

        $data[] = [
            'key' => 'hr.email_subject.turnoff',
            'value' => 'Nhắc nhở không tắt máy tính'
        ];

        try {
            DB::beginTransaction();
            if ($content) {
                $content->delete();
            }
            if ($subject) {
                $subject->delete();
            }
            CoreConfigData::insert($data);
            $this->insertSeedMigrate();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

}
