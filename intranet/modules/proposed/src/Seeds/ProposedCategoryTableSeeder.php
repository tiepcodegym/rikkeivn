<?php

namespace Rikkei\Proposed\Seeds;

use Illuminate\Database\Seeder;
use DB;
use Rikkei\Core\Seeds\CoreSeeder;

class ProposedCategoryTableSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(4)) {
            return true;
        }
        DB::beginTransaction();
        try {
            $data = [
                [
                    'id' => 1,
                    'name_vi' => 'Chế độ chính sách',
                    'name_en' => 'Policies',
                    'name_ja' => 'Chế độ chính sách',
                    'status' => 1,
                    'created_by' => 1
                ],
                [
                    'id' => 2,
                    'name_vi' => 'Cơ sở vật chất',
                    'name_en' => 'Facilities',
                    'name_ja' => 'Cơ sở vật chất',
                    'status' => 1,
                    'created_by' => 1
                ],
                [
                    'id' => 3,
                    'name_vi' => 'Thủ tục hành chính',
                    'name_en' => 'Administrative procedures',
                    'name_ja' => 'Thủ tục hành chính',
                    'status' => 1,
                    'created_by' => 1
                ],
                [
                    'id' => 4,
                    'name_vi' => 'Quy trình làm việc',
                    'name_en' => 'Working process',
                    'name_ja' => 'Quy trình làm việc',
                    'status' => 1,
                    'created_by' => 1
                ],
                [
                    'id' => 5,
                    'name_vi' => 'Nhân sự',
                    'name_en' => 'HR',
                    'name_ja' => 'Nhân sự',
                    'status' => 1,
                    'created_by' => 1
                ],
                [
                    'id' => 6,
                    'name_vi' => 'Khác',
                    'name_en' => 'Other',
                    'name_ja' => 'Khác',
                    'status' => 1,
                    'created_by' => 1
                ],
            ];
           foreach ($data as $datum) {
               $proposeCate = DB::table('proposed_categories')->where('id', $datum['id']);
               if ($proposeCate->first()) {
                   $proposeCate->update($datum);
               } else {
                   DB::table('proposed_categories')->insert($datum);
               }
           }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::info($e->getMessage());
        }
    }
}
