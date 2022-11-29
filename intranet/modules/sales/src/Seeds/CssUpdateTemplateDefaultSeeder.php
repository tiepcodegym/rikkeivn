<?php
namespace Rikkei\Sales\Seeds;

use DB;
use Carbon\Carbon;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Sales\Model\CssCategory;
use Rikkei\Sales\Model\CssQuestion;

class CssUpdateTemplateDefaultSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }
        
        DB::beginTransaction();
        try {
            $data = [
                [
                    'data' => [
                        'name' => 'Communication in Japanese',
                        'parent_id' => 42,
                        'project_type_id' => Css::TYPE_OSDC,
                        'code' => 1,
                        'sort_order' => 4,
                        'lang_id' => Css::ENG_LANG,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                    'questions' => [
                        [
                            'content' => "Please rate the quality of Japanese in e-mails/Chat software, etc",
                            'sort_order' => 1,
                        ],
                        [
                            'content' => 'Please rate the quality of Japanese interpretation in meeting. (including conference call, TV meeting, meeting through chat, etc.)',
                            'sort_order' => 2,
                        ],
                        [
                            'content' => 'Please rate the quality of Japanese in translated documents.',
                            'sort_order' => 3,
                        ],
                        [
                            'content' => "What do you think the communicator's understanding about the project?",
                            'sort_order' => 4,
                        ],
                    ],
                ],
                [
                    'data' => [
                        'name' => 'BridgeSE skill',
                        'parent_id' => 42,
                        'project_type_id' => Css::TYPE_OSDC,
                        'code' => 1,
                        'sort_order' => 5,
                        'lang_id' => Css::ENG_LANG,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                    'questions' => [
                        [
                            'content' => "Please rate the technical skill. (Ability to comprehend the Requirements, Design, Coding skill,... )",
                            'sort_order' => 1,
                        ],
                        [
                            'content' => 'Please rate the Japanese language skill.',
                            'sort_order' => 2,
                        ],
                        [
                            'content' => 'Please rate the ability in problem solving and project support (Ability to comprehend the problems, support for offshore team, bridge between customer and offshore team …)',
                            'sort_order' => 3,
                        ],
                    ],
                ],
            ];
            foreach ($data as $key => $itemChild) {
                if (isset($itemChild['data'])) {
                    $childCate = CssCategory::create($itemChild['data']);
                    if (isset($itemChild['questions'])) {
                       foreach ($itemChild['questions'] as $key2 => $itemQs) {
                            $itemQs['category_id'] = $childCate->id;
                            CssQuestion::create($itemQs);
                       }
                    }
                }
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }        
    }
}
