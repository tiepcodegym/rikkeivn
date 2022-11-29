<?php
namespace Rikkei\Sales\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;

class CssCategoryEnglishSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('EvaluationUpdatePoint-v2')) {
            return;
        }
        $dataDemo = [
        //Project based
            [
                'name' => 'Project quality',//19
                'parent_id' => '2',
                'project_type_id' => '2',
                'sort_order' => 1,
                'lang_id' => '1'
            ],
            [
                'name' => 'Project management',//20
                'parent_id' => '2',
                'project_type_id' => '2',
                'sort_order' => 2,
                'lang_id' => '1'
            ],
            [
                'name' => 'BA\'s Evaluation',//21
                'parent_id' => '2',
                'project_type_id' => '2',
                'sort_order' => 3,
                'lang_id' => '1'
            ],
            [
                'name' => 'About Project Requirement Understanding',//22
                'parent_id' => '19',
                'project_type_id' => '2',
                'sort_order' => 1,
                'lang_id' => '1'
            ],
            [
                'name' => 'About Designing phase',//23
                'parent_id' => '19',
                'project_type_id' => '2',
                'sort_order' => 2,
                'lang_id' => '1'
            ],
            [
                'name' => 'About Coding phase',//24
                'parent_id' => '19',
                'project_type_id' => '2',
                'sort_order' => 3,
                'lang_id' => '1'
            ],
            [
                'name' => 'About the General Testing',//25
                'parent_id' => '19',
                'project_type_id' => '2',
                'sort_order' => 4,
                'lang_id' => '1'
            ],
            [
                'name' => 'About our Customer Support throughout the project',//26
                'parent_id' => '19',
                'project_type_id' => '2',
                'sort_order' => 5,
                'lang_id' => '1'
            ],

        //Project OSDC
            [
                'name' => 'Staff Abilities/Skills Assessment',//27
                'parent_id' => '1',
                'project_type_id' => '1',
                'sort_order' => 1,
                'lang_id' => '1'
            ],
            [
                'name' => 'The quality of our service',//28
                'parent_id' => '1',
                'project_type_id' => '1',
                'sort_order' => 2,
                'lang_id' => '1'
            ],
            [
                'name' => 'About English communication ability',//29
                'parent_id' => '1',
                'project_type_id' => '1',
                'sort_order' => 3,
                'lang_id' => '1'
            ],
            [
                'name' => 'About the person in charge of OSDC',//30
                'parent_id' => '1',
                'project_type_id' => '1',
                'sort_order' => 4,
                'lang_id' => '1'
            ],
            [
                'name' => 'Abilities/Skills',//31
                'parent_id' => '27',
                'project_type_id' => '1',
                'sort_order' => 1,
                'lang_id' => '1'
            ],
            [
                'name' => 'The result of work',//32
                'parent_id' => '27',
                'project_type_id' => '1',
                'sort_order' => 2,
                'lang_id' => '1'
            ],
            [
                'name' => 'Sense of responsibility',//33
                'parent_id' => '28',
                'project_type_id' => '1',
                'sort_order' => 3,
                'lang_id' => '1'
            ],
            [
                'name' => 'Labor discipline',//34
                'parent_id' => '28',
                'project_type_id' => '1',
                'sort_order' => 4,
                'lang_id' => '1'
            ]
        ];
        DB::beginTransaction();
        try {
            $maxId = DB::table('css_category')->count('id'); 
            if($maxId >= 18){
                foreach ($dataDemo as $data) {
                    if (! DB::table('css_category')->select('id')->where('name', $data['name'])->get()) {
                        DB::table('css_category')->insert($data);
                    }
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch(Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
