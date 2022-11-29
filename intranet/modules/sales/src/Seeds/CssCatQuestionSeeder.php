<?php
namespace Rikkei\Sales\Seeds;

use Rikkei\Sales\Model\CssCategory;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssQuestion;
use Illuminate\Support\Facades\DB;

class CssCatQuestionSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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

        $typeOnsite = Css::TYPE_ONSITE;
        $dataDemo = [
            //type onsite
            [
                'name' => 'Onsite',
                'parent_id' => 0,
                'project_type_id' => $typeOnsite,
                'sort_order' => 0,
                'lang_id' => Css::VIE_LANG,
                'childs' => [
                    //start vi cats
                    [
                        'name' => 'Đánh giá ý thức kỉ luật của nhân viên',
                        'project_type_id' => $typeOnsite,
                        'lang_id' => Css::VIE_LANG,
                        'sort_order' => 1,
                        'questions' => [
                            [
                                'content' => 'Tuân thủ giờ làm việc của công ty?'
                                . "\r\n- Đi làm đúng giờ"
                                . "\r\n- Xin nghỉ có phép và báo trước ít nhất 1 ngày",
                                'sort_order' => 1
                            ],
                            [
                                'content' => "Tuân thủ nội quy của công ty?"
                                . "\r\n- Các quy định nội quy của công ty, dự án"
                                . "\r\n- Quy tắc Bảo mật thông tin",
                                'sort_order' => 2
                            ],
                            [
                                'content' => 'Tác phong làm việc: ăn mặc gọn gàng, giữ gìn vệ sinh chung?',
                                'sort_order' => 3
                            ],
                            [
                                'content' => "Giao tiếp với cấp trên?"
                                . "\r\n- Tôn trọng, lễ phép"
                                . "\r\n- Chủ động báo cáo tiến độ công việc, khó khăn gặp phải",
                                'sort_order' => 4
                            ],
                            [
                                'content' => "Giao tiếp với đồng nghiệp?"
                                . "\r\n- Hòa đồng, cởi mở"
                                . "\r\n- Chủ động trao đổi trong công việc",
                                'sort_order' => 5
                            ]
                        ]
                    ],
                    [
                        'name' => 'Đánh giá về chất lượng công việc',
                        'project_type_id' => $typeOnsite,
                        'lang_id' => Css::VIE_LANG,
                        'sort_order' => 2,
                        'questions' => [
                            [
                                'content' => "Khả năng chuyên môn có đáp ứng yêu cầu công việc?"
                                . "\r\n(Kiến thức chuyên môn, khả năng kĩ thuật, khả năng hiểu biết về nghiệp vụ dự án, kĩ năng code, kĩ năng test...)",
                                'sort_order' => 6
                            ],
                            [
                                'content' => "Tiến độ hoàn thành công việc được giao?"
                                . "\r\n(Khả năng hoàn thành công việc theo đúng deadline , năng suất làm việc…)",
                                'sort_order' => 7
                            ],
                            [
                                'content' => "Chất lượng công việc hoàn thành?"
                                . "\r\n(Hiệu quả công việc có tốt không? Có thỏa mãn yêu cầu đặt ra hay không?)",
                                'sort_order' => 8
                            ],
                            [
                                'content' => "Thái độ, Tinh thần, Tác phong làm việc?"
                                . "\r\n(Có tinh thần học hỏi, cầu tiến không? Có nghiêm túc trong công việc được giao không? Khi gặp khó khăn có tích cực tìm giải pháp khắc phục không?... )",
                                'sort_order' => 9
                            ],
                            [
                                'content' => "Kỹ năng làm việc độc lập?"
                                . "\r\n(Kỹ năng nhận diện, phân tích và giải quyết vấn đề một cách độc lập)",
                                'sort_order' => 10
                            ],
                            [
                                'content' => "Kỹ năng làm việc nhóm?"
                                . "\r\n(Có phối hợp, hỗ trợ tương tác tốt khi làm việc với các thành viên khác trong team không?)",
                                'sort_order' => 11
                            ]
                        ]
                    ],
                    //end vi cats
                    //start jp cats
                    [
                        'name' => 'スタッフの勤務態度',
                        'project_type_id' => $typeOnsite,
                        'lang_id' => Css::JAP_LANG,
                        'sort_order' => 1,
                        'questions' => [
                            [
                                'content' => 'スタッフは 労働規則を守っていますか。'
                                . "\r\n- 勤務時間を守っていますか"
                                . "\r\n- 欠勤したい場合は、事前に申請していますか",
                                'sort_order' => 1
                            ],
                            [
                                'content' => "スタッフは貴社の規則を遵守していますか。"
                                . "\r\n- プロジェクトのルールを守っていますか。"
                                . "\r\n- 情報セキュリティを守っていますか。",
                                'sort_order' => 2
                            ],
                            [
                                'content' => "スタッフの身だしなみや職場での整理整頓ができていますか。"
                                . "\r\n- 場面にあった身だしなみができていますか。"
                                . "\r\n- 身の周りや共有スペースを清潔に利用できていますか。",
                                'sort_order' => 3
                            ],
                            [
                                'content' => "社内での協調性、コミュニケーション"
                                . "\r\n- 同僚や上司に敬意をもって接していますか。"
                                . "\r\n- 作業の進捗や困ったことをタイムリーに報告・相談していますか。",
                                'sort_order' => 4
                            ]
                        ]
                    ],
                    [
                        'name' => 'スタッフの作業の成果',
                        'project_type_id' => $typeOnsite,
                        'lang_id' => Css::JAP_LANG,
                        'sort_order' => 2,
                        'questions' => [
                            [
                                'content' => "スタッフの能力は割り当てられた作業に適していますか。"
                                . "\r\n（テクニカルスキル、要求の理解力、設計、コーディング、テスト能力等）",
                                'sort_order' => 5
                            ],
                            [
                                'content' => "スタッフの作業完了度についてご評価ください。"
                                . "\r\n（進捗状況がスケジュール通りであったかどうか、就業時間を有効に活用すること等）",
                                'sort_order' => 6
                            ],
                            [
                                'content' => "スタッフの作業の質はいかがですか。"
                                . "\r\n（作業の成果、仕事の要件にあったかどうか等）",
                                'sort_order' => 7
                            ],
                            [
                                'content' => "スタッフの態度、責任感"
                                . "\r\n（向上心、勤勉な態度、問題があった時に自分で解決方法を探す努力が見える等）",
                                'sort_order' => 8
                            ],
                            [
                                'content' => "自律した作業能力がありますか。"
                                . "\r\n（すぐに人に頼らず自ら問題を発見し、分析・解決する能力等）",
                                'sort_order' => 9
                            ],
                            [
                                'content' => "チームワークスキル"
                                . "\r\n（チームへの貢献度、同僚・メンバとの関わり等）",
                                'sort_order' => 10
                            ]
                        ]
                    ],
                    //end jp cats
                    //start en cats
                    [
                        'name' => 'Sense of discipline',
                        'old_name' => 'Evaluation of conscious discipline',
                        'project_type_id' => $typeOnsite,
                        'lang_id' => Css::ENG_LANG,
                        'sort_order' => 1,
                        'questions' => [
                            [
                                'content' => 'Compliance with working hours of the company'
                                . "\r\n- Going to work on time"
                                . "\r\n- Asking for absence with prior notice at least 1 day",
                                'sort_order' => 1
                            ],
                            [
                                'content' => "Compliance with rules of the company"
                                . "\r\n- The rules specified by the company, the project"
                                . "\r\n- Information security rules",
                                'sort_order' => 2
                            ],
                            [
                                'content' => 'Working manners: dress neatly, general hygiene',
                                'sort_order' => 3
                            ],
                            [
                                'content' => "Communication with superiors"
                                . "\r\n- Respect, politeness"
                                . "\r\n- Actively reporting, updating any encountered difficulties",
                                'sort_order' => 4,
                                'old_content' => "Communication with superiors"
                                . "\r\n- Respect, politeness"
                                . "\r\n- Actively reporting work in progress , updating any encountered difficulties"
                            ],
                            [
                                'content' => "Communication with peers"
                                . "\r\n- Sociable, open-minded"
                                . "\r\n- Proactive exchange at work",
                                'sort_order' => 5,
                                'old_content' => "Communication with colleagues"
                                . "\r\n- Sociable, open-minded"
                                . "\r\n- Proactive views exchange at work"
                            ]
                        ]
                    ],
                    [
                        'name' => 'Work quality assessment',
                        'project_type_id' => $typeOnsite,
                        'lang_id' => Css::ENG_LANG,
                        'sort_order' => 2,
                        'questions' => [
                            [
                                'content' => "Qualification for the work requirements"
                                . "\r\n(Specialized knowledge, Language ability, Technical skill, Coding or Testing skill , etc)",
                                'sort_order' => 6
                            ],
                            [
                                'content' => "The progress of the tasks given"
                                . "\r\n(Punctuality, Productivity at work, etc.)",
                                'sort_order' => 7
                            ],
                            [
                                'content' => "Work results"
                                . "\r\n(Working efficiency, Satisfaction levels, etc.)",
                                'sort_order' => 8
                            ],
                            [
                                'content' => "Working attitude and spirit"
                                . "\r\n(Passion for self-improvement and learning, Consistency, Diligence, Devotion, Commitment, etc.)",
                                'sort_order' => 9
                            ],
                            [
                                'content' => "Individual skill"
                                . "\r\n(Ability of defining, analyzing and solving problems independently, etc.)",
                                'sort_order' => 10
                            ],
                            [
                                'content' => "Teamwork skill"
                                . "\r\n(Cooperation with other members in the project to complete tasks, etc)",
                                'sort_order' => 11,
                                'old_content' => "Teamwork skill"
                                . "\r\n(Communication and cooperation with other members in the project, etc)"
                            ]
                        ]
                    ],
                    //end en cats
                ],
                'questions' => [
                    [
                        'content' => "Nhìn chung, mức độ hài lòng về nhân sự onsite tại công ty bạn?",
                        'is_overview_question' => 1,
                        'quest_lang_id' => Css::VIE_LANG,
                        'sort_order' => 1
                    ],
                    [
                        'content' => "全体的に、弊社のオンサイトスタッフにご満足いただけていますか。",
                        'is_overview_question' => 1,
                        'quest_lang_id' => Css::JAP_LANG,
                        'sort_order' => 1
                    ],
                    [
                        'content' => "In general, what is the level of satisfaction of our onsiter working at your company?",
                        'old_content' => "In general,  what is the level of satisfaction of the onsiter at the company?",
                        'is_overview_question' => 1,
                        'quest_lang_id' => Css::ENG_LANG,
                        'sort_order' => 1
                    ]
                ]
            ]
        ];

        DB::beginTransaction();
        try {
            $newCat = new CssCategory();
            $catFillable = $newCat->getFillable();
            $newQuest = new CssQuestion();
            $questFillable = $newQuest->getFillable();
            foreach ($dataDemo as $dataCat) {
                $catItem = CssCategory::where('name', $dataCat['name'])->first();
                if (!$catItem) {
                    $catItem = CssCategory::create(array_only($dataCat, $catFillable));
                }
                //children
                if (isset($dataCat['childs'])) {
                    foreach ($dataCat['childs'] as $catOrder => $catChild) {
                        $catChildItem = CssCategory::where('name', $catChild['name']);
                        if (isset($catChild['old_name'])) {
                            $catChildItem->orWhere('name', $catChild['old_name']);
                        }
                        $catChildItem = $catChildItem->first();
                        if (!$catChildItem) {
                            $catChild['parent_id'] = $catItem->id;
                            $catChildItem = CssCategory::create(array_only($catChild, $catFillable));
                        } else {
                            $catChildItem->update(array_only($catChild, $catFillable));
                        }
                        foreach ($catChild['questions'] as $order => $dataQuest) {
                            $quesItem = CssQuestion::where('content', $dataQuest['content']);
                            if (isset($dataQuest['old_content'])) {
                                $quesItem->orWhere('content', $dataQuest['old_content']);
                            }
                            $quesItem = $quesItem->first();
                            if (!$quesItem) {
                                $dataQuest['category_id'] = $catChildItem->id;
                                CssQuestion::create(array_only($dataQuest, $questFillable));
                            } else {
                                $quesItem->update(array_only($dataQuest, $questFillable));
                            }
                        }
                    }
                }
                //questions
                if (isset($dataCat['questions'])) {
                    foreach ($dataCat['questions'] as $order => $dataQuestOv) {
                        $quesItemOv = CssQuestion::where('content', $dataQuestOv['content']);
                        if (isset($dataQuestOv['old_content'])) {
                            $quesItemOv->orWhere('content', $dataQuestOv['old_content']);
                        }
                        $quesItemOv = $quesItemOv->first();
                        if (!$quesItemOv) {
                            $dataQuestOv['category_id'] = $catItem->id;
                            CssQuestion::create(array_only($dataQuestOv, $questFillable));
                        } else {
                            $quesItemOv->update(array_only($dataQuestOv, $questFillable));
                        }
                    }
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
