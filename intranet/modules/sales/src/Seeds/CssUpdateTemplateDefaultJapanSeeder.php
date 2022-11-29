<?php
namespace Rikkei\Sales\Seeds;

use DB;
use Carbon\Carbon;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Sales\Model\CssCategory;
use Rikkei\Sales\Model\CssQuestion;

class CssUpdateTemplateDefaultJapanSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(2)) {
            return true;
        }
        
        DB::beginTransaction();
        try {
            $data = [
                [
                    'data' => [
                        'name' => '日本語でのコミュニケーション',
                        'parent_id' => 78,
                        'project_type_id' => Css::TYPE_OSDC,
                        'code' => 1,
                        'sort_order' => 4,
                        'lang_id' => Css::JAP_LANG,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                    'questions' => [
                        [
                            'content' => "メール日本語訳の品質はいかがでしたか。",
                            'sort_order' => 1,
                        ],
                        [
                            'content' => '会議での日本語通訳の質はいかがでしたか。（電話会議、TV会議、チャット等）',
                            'sort_order' => 2,
                        ],
                        [
                            'content' => '各種ドキュメントの日本語訳の質はいかがでしたか。',
                            'sort_order' => 3,
                        ],
                        [
                            'content' => "プロジェクトについてのコミュニケーターの理解度はどう思われましたか。",
                            'sort_order' => 4,
                        ],
                    ],
                ],
                [
                    'data' => [
                        'name' => 'ブリッジSEのスキル',
                        'parent_id' => 78,
                        'project_type_id' => Css::TYPE_OSDC,
                        'code' => 1,
                        'sort_order' => 5,
                        'lang_id' => Css::JAP_LANG,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                    'questions' => [
                        [
                            'content' => "テクニカルスキルについてどう思われましたか。（要求の理解力、設計、コーディング能力等）",
                            'sort_order' => 1,
                        ],
                        [
                            'content' => '日本語能力についてどう思われましたか。',
                            'sort_order' => 2,
                        ],
                        [
                            'content' => '問題解決能力、プロジェクトサポート能力についてどう思われましたか。',
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
