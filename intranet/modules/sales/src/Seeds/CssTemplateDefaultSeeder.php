<?php
namespace Rikkei\Sales\Seeds;

use DB;
use Carbon\Carbon;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Sales\Model\CssCategory;
use Rikkei\Sales\Model\CssQuestion;

class CssTemplateDefaultSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(9)) {
            return true;
        }
        
        DB::beginTransaction();
        try {
            $data = [
                [
                    'data' => [
                        'name' => "About the project's products and services",
                        'parent_id' => 0,
                        'project_type_id' => Css::TYPE_OSDC,
                        'code' => 1,
                        'lang_id' => Css::ENG_LANG,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                    'questions' => [
                        [
                            'content' => 'In overall, do our products and services satisfy you?',
                            'sort_order' => 1,
                            'is_overview_question' => 1,
                        ],
                    ],
                    'child' => [
                        [
                            'data' => [
                                'name' => 'Project Quality',
                                'project_type_id' => Css::TYPE_OSDC,
                                'code' => 1,
                                'sort_order' => 1,
                                'lang_id' => Css::ENG_LANG,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ],
                            'sub-child' => [
                                [
                                    'data' => [
                                        'name' => 'The quality of requirement analysis phase?',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 1,
                                        'lang_id' => Css::ENG_LANG,
                                        'question_explanation' => 'The quality of requirement study & analysis phase includes: SRS, Prototype, question & answer about the requirement …',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => "How about the project team's capability to analyze and clarify the requirement?",
                                            'sort_order' => 1,
                                        ],
                                        [
                                            'content' => 'Is the specification content developed by the project team clear?',
                                            'sort_order' => 2,
                                        ],
                                        [
                                            'content' => 'Is the duration of  Specification completion suitable?',
                                            'sort_order' => 3,
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => 'The quality of design phase?',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 2,
                                        'lang_id' => Css::ENG_LANG,
                                        'question_explanation' => 'The quality of design phase includes the quality of products such as Architecture Design, Detailed Design, Screen Design; Solutions for design; Applying new technology in the project…',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => 'Is the design content developed by the project team clear?',
                                            'sort_order' => 1,
                                        ],
                                        [
                                            'content' => 'Is the quality of design solutions good?',
                                            'sort_order' => 2,
                                        ],
                                        [
                                            'content' => 'Is the duration of Design completion suitable?',
                                            'sort_order' => 3,
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => 'The quality of coding phase?',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 3,
                                        'lang_id' => Css::ENG_LANG,
                                        'question_explanation' => 'The quality of coding phase includes the quality of source code,  code review…',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => 'Is the source code written clearly?',
                                            'sort_order' => 1,
                                        ],
                                        [
                                            'content' => 'Does the source code represent the functions required by customer?',
                                            'sort_order' => 2,
                                        ],
                                        [
                                            'content' => 'How about the basic bugs in the source code? (detected by performing the code review)',
                                            'sort_order' => 3,
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => 'The quality of testing phase?',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 4,
                                        'lang_id' => Css::ENG_LANG,
                                        'question_explanation' => 'The quality of testing phase (Unit Test/ Integration Test/ System Test) includes the quality of code after testing, test cases, test data,  test evidences or bug list supplied by RikkeiSoft',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => 'Is the quality of test documents good?',
                                            'sort_order' => 1,
                                        ],
                                        [
                                            'content' => 'Is the quality of test execution good?',
                                            'sort_order' => 2,
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => 'The quality of customer support service?',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 5,
                                        'lang_id' => Css::ENG_LANG,
                                        'question_explanation' => 'The quality of customer support service since the release for the acceptance test includes: response time, solution suggestion, solution implementation.',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => "Is the project team's response time good?",
                                            'sort_order' => 1,
                                        ],
                                        [
                                            'content' => 'Is the solution suggested by the project team suitable?',
                                            'sort_order' => 2,
                                        ],
                                    ],
                                ],
                            ]
                        ],
                        [
                            'data' => [
                                'name' => ' Change Request Management ',
                                'project_type_id' => Css::TYPE_OSDC,
                                'code' => 1,
                                'sort_order' => 2,
                                'lang_id' => Css::ENG_LANG,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ],
                            'questions' => [
                                [
                                    'content' => 'Is the change request (CR) represented fully in relevant products such as SRS, Design, Source code, Test documents, etc?',
                                    'sort_order' => 1,
                                ],
                                [
                                    'content' => 'Is the duration of CR completion suitable?',
                                    'sort_order' => 2,
                                ],
                            ],
                        ],
                        [
                            'data' => [
                                'name' => 'Project Management',
                                'project_type_id' => Css::TYPE_OSDC,
                                'code' => 1,
                                'sort_order' => 3,
                                'lang_id' => Css::ENG_LANG,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ],
                            'questions' => [
                                [
                                    'content' => 'How do the project team follow the committed schedule? 
                                        (Following the committed schedule is represented in the development progress, the on-time deliverables …)',
                                    'sort_order' => 1,
                                ],
                                [
                                    'content' => "What do you think about the project team's capability of making reports and the report's quality?",
                                    'sort_order' => 2,
                                ],
                                [
                                    'content' => "What do you think about the project team's risks & issues management capability? ",
                                    'sort_order' => 3,
                                ],
                                [
                                    'content' => "What do you think about the devotion of the project team?",
                                    'sort_order' => 4,
                                ]
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
