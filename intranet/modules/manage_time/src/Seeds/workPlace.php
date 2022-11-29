<?php

namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;

class workPlace extends CoreSeeder
{
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }

        $dataInsert = [
            [
                'name' => 'Lotte',
                'code' => 'LOTTE',
                'is_surcharge' => 1,
                'is_status' => 0
            ],
            [
                'name' => 'Handico',
                'code' => 'HANDICO',
                'is_surcharge' => 1,
                'is_status' => 0
            ],
            [
                'name' => 'Sudico',
                'code' => 'SUDICO',
                'is_surcharge' => 1,
                'is_status' => 0
            ],
            [
                'name' => 'Sông Đà',
                'code' => 'SONGDA',
                'is_surcharge' => 1,
                'is_status' => 0,
            ],
            [
                'name' => 'Đà Nẵng',
                'code' => 'DANANG',
                'is_surcharge' => 1,
                'is_status' => 0
            ],
            [
                'name' => 'Hồ Chí Minh',
                'code' => 'HOCHIMINH',
                'is_surcharge' => 1,
                'is_status' => 0
            ],
            [
                'name' => 'Nhật Bản',
                'code' => 'NHATBAN',
                'is_surcharge' => 1,
                'is_status' => 0
            ],
        ];

        DB::beginTransaction();
        try {
            foreach ($dataInsert as $data) {
                if (! DB::table('manage_work_places')->select('id')->where('code', $data['code'])->get()) {
                    DB::table('manage_work_places')->insert($data);
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
