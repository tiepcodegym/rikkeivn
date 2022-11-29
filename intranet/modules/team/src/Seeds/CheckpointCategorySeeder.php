<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\CheckpointCategory;

class CheckpointCategorySeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
                'name' => 'Dev',
                'parent_id' => '0',
                'checkpoint_type_id' => '1',
                'sort_order' => 0
            ],
            [
                'name' => 'QA',
                'parent_id' => '0',
                'checkpoint_type_id' => '2',
                'sort_order' => 0
            ],
            [
                'name' => 'Experience',
                'parent_id' => '2',
                'checkpoint_type_id' => '2',
                'sort_order' => 1
            ],
            [
                'name' => 'Education and Training',
                'parent_id' => '2',
                'checkpoint_type_id' => '2',
                'sort_order' => 2
            ],
            [
                'name' => 'Achivement',
                'parent_id' => '2',
                'checkpoint_type_id' => '2',
                'sort_order' => 3
            ],
            [
                'name' => 'Technical Knowledge and Skills',
                'parent_id' => '2',
                'checkpoint_type_id' => '2',
                'sort_order' => 4
            ],
            [
                'name' => 'Soft Skills',
                'parent_id' => '2',
                'checkpoint_type_id' => '2',
                'sort_order' => 5
            ],
            [
                'name' => 'Tác phong và thái độ làm việc',
                'parent_id' => '2',
                'checkpoint_type_id' => '2',
                'sort_order' => 6
            ],
            [
                'name' => 'Năng lực áp dụng quy trình',
                'parent_id' => '6',
                'checkpoint_type_id' => '2',
                'sort_order' => 1
            ],
            [
                'name' => 'Năng lực chuyên môn',
                'parent_id' => '6',
                'checkpoint_type_id' => '2',
                'sort_order' => 2
            ],
            [
                'name' => 'Experience',
                'parent_id' => '1',
                'checkpoint_type_id' => '1',
                'sort_order' => 1
            ],
            [
                'name' => 'Education and Training',
                'parent_id' => '1',
                'checkpoint_type_id' => '1',
                'sort_order' => 2
            ],
            [
                'name' => 'Achievement',
                'parent_id' => '1',
                'checkpoint_type_id' => '1',
                'sort_order' => 3
            ],
            [
                'name' => 'Technical Knowledge and Skills',
                'parent_id' => '1',
                'checkpoint_type_id' => '1',
                'sort_order' => 4
            ],
            [
                'name' => 'Soft Skills',
                'parent_id' => '1',
                'checkpoint_type_id' => '1',
                'sort_order' => 5
            ],
            [
                'name' => 'Tác phong & Thái độ làm việc',
                'parent_id' => '1',
                'checkpoint_type_id' => '1',
                'sort_order' => 6
            ],
            [
                'name' => 'Năng lực áp dụng quy trình',
                'parent_id' => '14',
                'checkpoint_type_id' => '1',
                'sort_order' => 1
            ],
            [
                'name' => 'Năng lực chuyên môn',
                'parent_id' => '14',
                'checkpoint_type_id' => '1',
                'sort_order' => 2
            ]
        ];
        
        $maxId = DB::table('checkpoint_category')->max('id'); 
        if($maxId == 0){
            foreach ($dataDemo as $data) {
                $model = new CheckpointCategory();
                $model->setData($data);
                $model->save();
            }
        }
        $this->insertSeedMigrate();
    }
}
