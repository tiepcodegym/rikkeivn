<?php
namespace Rikkei\Sales\Seeds;

use DB;

class CssCategorySeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $dataDemo = [
            [
                'name' => 'OSDC',
                'parent_id' => '0',
                'project_type_id' => '1',
                'sort_order' => 0
            ],
            [
                'name' => 'Project base',
                'parent_id' => '0',
                'project_type_id' => '2',
                'sort_order' => 0
            ],
            [
                'name' => 'Chất lượng dự án',
                'parent_id' => '2',
                'project_type_id' => '2',
                'sort_order' => 1
            ],
            [
                'name' => 'Về quản lý dự án',
                'parent_id' => '2',
                'project_type_id' => '2',
                'sort_order' => 2
            ],
            [
                'name' => 'Về đánh giá BrSE',
                'parent_id' => '2',
                'project_type_id' => '2',
                'sort_order' => 3
            ],
            [
                'name' => 'Về chất lượng giai đoạn phân tích yêu cầu',
                'parent_id' => '3',
                'project_type_id' => '2',
                'sort_order' => 1
            ],
            [
                'name' => 'Về chất lượng giai đoạn thiết kế',
                'parent_id' => '3',
                'project_type_id' => '2',
                'sort_order' => 2
            ],
            [
                'name' => 'Về chất lượng giai đoạn coding',
                'parent_id' => '3',
                'project_type_id' => '2',
                'sort_order' => 3
            ],
            [
                'name' => 'Về chất lượng giai đoạn test',
                'parent_id' => '3',
                'project_type_id' => '2',
                'sort_order' => 4
            ],
            [
                'name' => 'Về Chất lượng support khách hàng',
                'parent_id' => '3',
                'project_type_id' => '2',
                'sort_order' => 5
            ],
            [
                'name' => 'Về năng lực và thành tích',
                'parent_id' => '1',
                'project_type_id' => '1',
                'sort_order' => 1
            ],
            [
                'name' => 'Chất lượng dịch vụ',
                'parent_id' => '1',
                'project_type_id' => '1',
                'sort_order' => 2
            ],
            [
                'name' => 'Khả năng communication bằng tiếng Nhật',
                'parent_id' => '1',
                'project_type_id' => '1',
                'sort_order' => 3
            ],
            [
                'name' => 'Đánh giá người phụ trách OSDC',
                'parent_id' => '1',
                'project_type_id' => '1',
                'sort_order' => 4
            ],
            [
                'name' => 'Kỹ năng, năng lực',
                'parent_id' => '11',
                'project_type_id' => '1',
                'sort_order' => 1
            ],
            [
                'name' => 'Hiệu quả làm việc',
                'parent_id' => '11',
                'project_type_id' => '1',
                'sort_order' => 2
            ],
            [
                'name' => 'Tinh trách nhiệm',
                'parent_id' => '12',
                'project_type_id' => '1',
                'sort_order' => 3
            ],
            [
                'name' => 'Nội quy lao động',
                'parent_id' => '12',
                'project_type_id' => '1',
                'sort_order' => 4
            ]
        ];
        
        $maxId = DB::table('css_category')->max('id'); 
        if($maxId == 0){
            foreach ($dataDemo as $data) {
                if (! DB::table('css_category')->select('id')->where('name', $data['name'])->get()) {
                    DB::table('css_category')->insert($data);
                }
            }
        }
        $this->insertSeedMigrate();
    }
}
