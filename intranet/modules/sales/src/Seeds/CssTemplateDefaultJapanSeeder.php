<?php
namespace Rikkei\Sales\Seeds;

use DB;
use Carbon\Carbon;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Sales\Model\CssCategory;
use Rikkei\Sales\Model\CssQuestion;

class CssTemplateDefaultJapanSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
                        'name' => "プロジェクトの製品・サービスについて",
                        'parent_id' => 0,
                        'project_type_id' => Css::TYPE_OSDC,
                        'code' => 1,
                        'lang_id' => Css::JAP_LANG,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                    'questions' => [
                        [
                            'content' => '全体的に、弊社の提供した製品とサービスにご満足いただけましたか。',
                            'sort_order' => 1,
                            'is_overview_question' => 1,
                        ],
                    ],
                    'child' => [
                        [
                            'data' => [
                                'name' => 'プロジェクトの品質',
                                'project_type_id' => Css::TYPE_OSDC,
                                'code' => 1,
                                'sort_order' => 1,
                                'lang_id' => Css::JAP_LANG,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ],
                            'sub-child' => [
                                [
                                    'data' => [
                                        'name' => '要求分析工程の品質',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 1,
                                        'lang_id' => Css::JAP_LANG,
                                        'question_explanation' => 'ソフトウェア要求仕様書、プロトタイプ、要求理解のためのQ＆A等の要求理解・分析の品質について',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => "プロジェクトチームの要求分析及び理解能力についてどう思われますか。",
                                            'sort_order' => 1,
                                        ],
                                        [
                                            'content' => 'プロジェクトチームが作成した仕様書の内容は明確でしたか。',
                                            'sort_order' => 2,
                                        ],
                                        [
                                            'content' => '仕様書完成の時期は適切でしたか。',
                                            'sort_order' => 3,
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => '設計工程の品質',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 2,
                                        'lang_id' => Css::JAP_LANG,
                                        'question_explanation' => 'アーキテクチャ設計、詳細設計、画面設計、設計へのソリューション、新技術適用等の設計工程の品質について',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => 'プロジェクトチームが作成した設計書の内容は明確でしたか。',
                                            'sort_order' => 1,
                                        ],
                                        [
                                            'content' => '設計のソリューションは適切でしたか。',
                                            'sort_order' => 2,
                                        ],
                                        [
                                            'content' => '設計の完了時期は適切でしたか。',
                                            'sort_order' => 3,
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => 'コーディング工程の品質',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 3,
                                        'lang_id' => Css::JAP_LANG,
                                        'question_explanation' => 'ソースコードやコードレビュー等の質について',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => 'ソースコードは明確に記述されていましたか（コーディング規則に従い、コメントは十分でしたか。）',
                                            'sort_order' => 1,
                                        ],
                                        [
                                            'content' => 'ソースコードは、ご要求の機能を全て反映していましたか。',
                                            'sort_order' => 2,
                                        ],
                                        [
                                            'content' => 'コードレビュー後の基本的なバグはどうでしたか。',
                                            'sort_order' => 3,
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => 'プロジェクトのテスト工程の品質',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 4,
                                        'lang_id' => Css::JAP_LANG,
                                        'question_explanation' => 'テストフェーズ（単体テスト/統合テスト/システムテスト）の品質には、RikkeiSoftが提供するテスト後のコードの品質、テストケース、テストデータ、テスト証拠、またはバグリストが含まれます。 ',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => 'テストのドキュメントの品質は良好でしたか。',
                                            'sort_order' => 1,
                                        ],
                                        [
                                            'content' => 'テスト実施の品質は良好でしたか。',
                                            'sort_order' => 2,
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => 'プロジェクトのお客様サポートの品質',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 5,
                                        'lang_id' => Css::JAP_LANG,
                                        'question_explanation' => 'レスポンスタイム、ソリューション提案、ソリューション実施等を含む、受入テスト用の納品後のサポートの質について',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => "チームのレスポンスタイムはいかがでしたか。",
                                            'sort_order' => 1,
                                        ],
                                        [
                                            'content' => 'チームの提案したソリューションは適切なものでしたか。',
                                            'sort_order' => 2,
                                        ],
                                    ],
                                ],
                            ]
                        ],
                        [
                            'data' => [
                                'name' => '要求変更管理',
                                'project_type_id' => Css::TYPE_OSDC,
                                'code' => 1,
                                'sort_order' => 2,
                                'lang_id' => Css::JAP_LANG,
                                'question_explanation' => '',
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ],
                            'questions' => [
                                [
                                    'content' => '関連成果物に要求変更が十分に反映されていましたか。（要求仕様書、ソースコード、テストドキュメント等）',
                                    'sort_order' => 1,
                                ],
                                [
                                    'content' => '要求変更の完了時期は適切でしたか。',
                                    'sort_order' => 2,
                                ],
                            ],
                        ],
                        [
                            'data' => [
                                'name' => 'プロジェクト管理',
                                'project_type_id' => Css::TYPE_OSDC,
                                'code' => 1,
                                'sort_order' => 3,
                                'lang_id' => Css::JAP_LANG,
                                'question_explanation' => '',
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ],
                            'questions' => [
                                [
                                    'content' => 'プロジェクトチームのスケジュールの遵守度はいかがでしたか。（進捗状況がスケジュール通りであったかどうか、納品は納期通りであったかどうか等） ',
                                    'sort_order' => 1,
                                ],
                                [
                                    'content' => "プロジェクトチームの報告書の作成能力とその質はいかがでしたか。",
                                    'sort_order' => 2,
                                ],
                                [
                                    'content' => "プロジェクトチームの課題・リスク管理能力はいかがでしたか。",
                                    'sort_order' => 3,
                                ],
                                [
                                    'content' => "プロジェクトチームの熱心度はいかがでしたか。",
                                    'sort_order' => 4,
                                ],
                                [
                                    'content' => "貴社とプロジェクトチーム（又は弊社）との関係はいかがでしたでしょうか。（仕事上やそれ以外も含む直接の会話やメールを通してのやりとりによる関係）",
                                    'sort_order' => 5,
                                ],
                            ],
                        ],
                    ],
                ],
            ];
            foreach ($data as $key => $itemRoot) {
                if (isset($itemRoot['data'])) {
                    $rootCate = CssCategory::create($itemRoot['data']);
                    if (isset($itemRoot['questions'])) {
                        foreach ($itemRoot['questions'] as $item) {
                             $item['category_id'] = $rootCate->id;
                             CssQuestion::create($item);
                        }
                    }
                    if (isset($itemRoot['child'])) {
                        foreach ($itemRoot['child'] as $key1 => $itemChild) {
                            if (isset($itemChild['data'])) {
                                $itemChild['data']['parent_id'] = $rootCate->id;
                                $childCate = CssCategory::create($itemChild['data']);
                                if (isset($itemChild['questions'])) {
                                   foreach ($itemChild['questions'] as $key2 => $itemQs) {
                                        $itemQs['category_id'] = $childCate->id;
                                        CssQuestion::create($itemQs);
                                   }
                                } elseif (isset($itemChild['sub-child'])) {
                                    foreach ($itemChild['sub-child'] as $key3 => $itemSub) {
                                        if (isset($itemSub['data'])) {
                                            $itemSub['data']['parent_id'] = $childCate->id;
                                            $subCate = CssCategory::create($itemSub['data']);
                                            if ($itemSub['questions']) {
                                               foreach ($itemSub['questions'] as $key4 => $itemSubQs) {
                                                    $itemSubQs['category_id'] = $subCate->id;
                                                    CssQuestion::create($itemSubQs);
                                               }
                                            }
                                        }
                                    }
                                }
                            }
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
