<?php

return [
    'home' => [
        'path' => '/',
        'label' => 'Tin tức',
        'label_en' => 'News',
        'label_ja' => 'ホーム',
        'active' => '1'
    ],

    'profile' => [
        'path' => 'profile',
        'label' => 'Hồ sơ',
        'label_en' => 'Profile',
        'label_ja' => 'プロフィール',
        'active' => '1',
        'child' => [
            'profile.main' => [
                'path' => 'profile',
                'label' => 'Hồ sơ cá nhân',
                'label_en' => 'Personal Information',
                'label_ja' => '個人情報',
                'active' => '1',
            ],
            'profile.contact' => [
                'path' => 'contact',
                'label' => 'Danh bạ nhân viên',
                'label_en' => 'Contacts',
                'label_ja' => '連絡先',
                'active' => '1',
            ],
            'profile.evaluation' => [
                'path' => '#',
                'label' => 'Đánh giá hàng tháng',
                'label_en' => 'My Evaluation (ME)',
                'label_ja' => '評価 (ME)',
                'active' => '1',
                'child' => [
                    'profile.evaluation.list' => [
                        'path' => 'monthly-evaluation/profile',
                        'label' => 'Danh sách đánh giá',
                        'label_en' => 'My list Evaluation (ME)',
                        'label_ja' => '評価 (ME)',
                        'active' => '1',
                    ],
                    'profile.me.activity' => [
                        'path' => 'profile/evaluation/activities',
                        'label' => 'Hoạt động ME',
                        'label_en' => 'ME activities',
                        'label_ja' => 'ME 活動',
                        'active' => '1'
                    ],
                ]
            ],

            'cham.cong' => [
                'path' => '#',
                'label' => 'Chấm công',
                'label_en' => 'Timekeeping',
                'label_ja' => '勤怠時間管理',
                'active' => '1',
                'child' => [
                    'leave.register' => [
                        'path' => 'profile/leave-day/register',
                        'label' => 'ĐK Nghỉ phép',
                        'label_en' => 'Apply for Paid Leave',
                        'label_ja' => '休暇申請',
                        'active' => '1',
                        'action_code' => 'manage.register',
                    ],
                    'supplement.register' => [
                        'path' => 'profile/supplement/register',
                        'label' => 'ĐK Bổ sung công',
                        'label_en' => 'Add missing working hours',
                        'label_ja' => '勤務時間補充申請',
                        'active' => '1',
                        'action_code' => 'manage.register',
                    ],
                    'ot.register' => [
                        'path' => 'ot/register',
                        'label' => 'ĐK Làm thêm (OT)',
                        'label_en' => 'Apply for Overtime Work (OT)',
                        'label_ja' => '残業申請　(OT)',
                        'active' => '1',
                        'action_code' => 'manage.register',
                    ],
//                    'comelate.register' => [
//                        'path' => 'profile/late-in-early-out/register',
//                        'label' => 'ĐK đi muộn về sớm',
//                        'label_en' => 'Apply for Early Leave or Late Arrival',
//                        'label_ja' => '遅刻、早退申請',
//                        'active' => '1',
//                        'action_code' => 'manage.register',
//                    ],
                    'mission.register' => [
                        'path' => 'profile/business-trip/register',
                        'label' => 'ĐK đi công tác',
                        'label_en' => 'Apply for a Business Trip',
                        'label_ja' => '出張申請',
                        'active' => '1',
                        'action_code' => 'manage.register',
                    ],
                    'working-time.group' => [
                        'path' => 'working-times',
                        'label' => 'Thời gian làm việc',
                        'label_en' => 'Working Hours',
                        'label_ja' => '勤務時間',
                        'active' => '1',
                        'child' => [
                            'working-time.register' => [
                                'path' => 'working-times/register',
                                'label' => 'ĐK thay đổi giờ làm',
                                'label_en' => 'Change working hours',
                                'label_ja' => '勤務時間調整申請',
                                'active' => '1',
                                'action_code' => 'working_time.register'
                            ],
                            'working-time.my_list' => [
                                'path' => 'working-times',
                                'label' => 'Danh sách thời gian làm việc',
                                'label_en' => 'Working hours list',
                                'label_ja' => '勤務時間リスト',
                                'active' => '1',
                            ],
                        ]
                    ],
                    'wrorking-time.log_times' => [
                        'path' => 'working-times/logs',
                        'label' => 'Giờ ra/vào làm việc',
                        'label_en' => 'Register actual working time',
                        'label_ja' => '出勤・退勤時間',
                        'active' => '1',
                        'action_code' => 'working_time.log_times'
                    ],
                    'timekeeping.detail' => [
                        'path' => 'profile/timekeeping',
                        'label' => 'Bảng chi tiết công',
                        'label_en' => 'Working time monthly report',
                        'label_ja' => '勤務時間の詳細表',
                        'active' => '1',
                        'action_code' => 'manage.register',
                    ],
                    'fines-money' => [
                        'path' => 'profile/fines-money',
                        'label' => 'Tiền phạt nội quy',
                        'label_en' => 'Fines rules',
                        'label_ja' => 'Fines rules',
                        'active' => '1',
                    ],
                ],
            ],
            'task.general' => [
                'path' => '#',
                'label' => 'Task',
                'label_en' => 'Task',
                'label_ja' => 'タスク',
                'active' => '1',
                'child' => [
                    'task.general.list' => [
                        'path' => 'task/general/list',
                        'label' => 'Danh sách',
                        'label_en' => 'Task List',
                        'label_ja' => 'タスクリスト',
                        'active' => '1'
                    ],
                    'task.general.create' => [
                        'path' => 'task/general/create',
                        'label' => 'Tạo mới',
                        'label_en' => 'Create a new task',
                        'label_ja' => '新タスクを作成',
                        'active' => '1'
                    ],
                ]
            ],
            'profile.asset' => [
                'path' => 'profile/asset',
                'label' => 'Tài sản',
                'label_en' => 'Assets',
                'label_ja' => '固定資産・備品',
                'active' => '1',
                'child' => [
                    'profile.asset.list' => [
                        'path' => 'profile/asset',
                        'label' => 'Danh sách Tài sản',
                        'label_en' => 'Asset List',
                        'label_ja' => '固定資産・備品リスト',
                        'active' => '1'
                    ],
                    'profile.request_asset' => [
                        'path' => 'profile/request-asset',
                        'label' => 'Danh sách Yêu cầu tài sản',
                        'label_en' => 'Asset Request List',
                        'label_ja' => 'リクエスト',
                        'active' => '1'
                    ]
                ]
            ],
            'profile.welfare.information' => [
                'path' => 'profile/welfare',
                'label' => 'Chế độ phúc lợi',
                'label_en' => 'Benefit programs',
                'label_ja' => '福利厚生プログラム',
                'active' => '1'
            ],
            'education.teaching' => [
                'path' => '#',
                'label' => 'Khóa học đào tạo',
                'label_en' => 'Education Manager',
                'label_ja' => '教育マネージャー',
                'active' => '1',
                'child' => [
                    'profile.profileList' => [
                        'path' => 'profile/course/list',
                        'label' => 'Danh sách khóa học của tôi ',
                        'label_en' => 'My Education List',
                        'label_ja' => '私の教育リスト',
                        'active' => '1',
//                        'action_code' => 'education.managers.profile.list'
                    ],
                ],
            ],
            'education.register.teaching' => [
                'path' => '#',
                'label' => 'Yêu cầu giảng dạy',
                'label_en' => 'Teaching requirement',
                'label_ja' => '指導要件',
                'active' => '1',
                'child' => [
                    'education.teaching.list' => [
                        'path' => 'manager/teachings',
                        'label' => 'Danh sách đăng ký giảng dạy',
                        'label_en' => 'List of teaching registration',
                        'label_ja' => '教員登録一覧',
                        'active' => '1',
//                        'action_code' => 'list.teachings'
                    ],
                    'education.teaching.create' => [
                        'path' => 'manager/teachings/create',
                        'label' => 'Đăng ký giảng dạy',
                        'label_en' => 'Sign up for teaching',
                        'label_ja' => '教育に申し込む',
                        'active' => '1',
                        'action_code' => ''
                    ]
                ]
            ],
            'paid' => [
                'path' => '#',
                'label' => 'Chi trả',
                'label_en' => 'Paid',
                'label_ja' => '支払',
                'active' => '1',
                'action_code' => 'view.team.report.payment',
                'child' => [
                    'buying.actions' => [
                        'path' => 'profile/buying-register',
                        'label' => 'Mua đồ',
                        'label_en' => 'Buying',
                        'label_ja' => '買物',
                        'active' => '1'
                    ],
                    'no-bill.actions' => [
                        'path' => 'profile/transfer-register',
                        'label' => 'Di chuyển',
                        'label_en' => 'Transfer',
                        'label_ja' => '交通',
                        'active' => '1'
                    ]
                ]
            ]
        ]
    ],
    'team' => [
        'path' => '#',
        'label' => 'Team',
        'label_en' => 'Team',
        'label_ja' => '各事業部',
        'active' => '1',
        'child' => [
            'team.member' => [
                'path' => 'team/member',
                'label' => 'Danh sách nhân viên',
                'label_en' => 'Member List',
                'label_ja' => 'メンバーリスト',
                'active' => '1',
                'action_code' => 'view.list.member',
            ],
            'monthly_evaluations' => [
                'path' => '#',
                'label' => 'Đánh giá hàng tháng',
                'label_en' => 'Monthly Evaluation (ME)',
                'label_ja' => '月次評価 (ME)',
                'active' => '1',
                'child' => [
                    'project.me.create' => [
                        'path' => 'monthly-evaluation/edit',
                        'label' => 'Tạo ME',
                        'label_en' => 'Create Individual ME',
                        'label_ja' => '評価作成',
                        'active' => 1,
                        'action_code' => 'project.me.create_edit'
                    ],
                    'project.me.team.create_new' => [
                        'path' => 'monthly-evaluation/team/edit',
                        'label' => 'Tạo cho Division',
                        'label_en' => 'Create Team ME',
                        'label_ja' => 'チームのため作成',
                        'active' => 1,
                        'action_code' => 'project.me.create_edit.team'
                    ],
                    'project.me.approve' => [
                        'path' => 'monthly-evaluation/review/list',
                        'label' => 'Nhận xét',
                        'label_en' => 'Reviews',
                        'label_ja' => 'レビュー',
                        'active' => 1,
                        'action_code' => 'project.me.review'
                    ],
                    'project.me.view' => [
                        'path' => 'monthly-evaluation/view-member',
                        'label' => 'Nhân viên của Division',
                        'label_en' => 'Team Member',
                        'label_ja' => 'チームメンバー',
                        'active' => 1,
                        'action_code' => 'project.me.review'
                    ],
                    'project.me.view.activity' => [
                        'path' => 'project/monthly-evaluation/member-activities',
                        'label' => "Hoạt động ME của nhân viên",
                        'label_en' => "Member's Activities",
                        'label_ja' => "メンバーの活動",
                        'active' => 1,
                        'action_code' => 'project.me_activity.view'
                    ],
                    'project.me.manager_attrs' => [
                        'path' => 'project/monthly-evaluation/attributes',
                        'label' => 'Thuộc tính',
                        'label_en' => 'Attributes',
                        'label_ja' => 'アトリビュート',
                        'active' => 1,
                        'action_code' => 'project.me.attributes'
                    ],
                    'project.me.view.evaludated' => [
                        'path' => 'monthly-evaluation/view-evaluated',
                        'label' => 'Danh sách đã đánh giá',
                        'label_en' => 'Evaluated Member List',
                        'label_ja' => '評価されたメンバー',
                        'active' => 1,
                        'action_code' => 'project.me.view_evaluated'
                    ],
                    'project.me.view.not_evaluate' => [
                        'path' => 'monthly-evaluation/not-evaluate',
                        'label' => 'Danh sách chưa đánh giá',
                        'label_en' => 'Yet to be evaluated member list',
                        'label_ja' => '評価されないメンバー',
                        'active' => 1,
                        'action_code' => 'project.me.view_evaluated'
                    ],
                    'project.me.reward.edit' => [
                        'path' => 'monthly-evaluation-reward/edit',
                        'label' => 'Thưởng OSDC',
                        'label_en' => 'OSDC Bonus',
                        'label_ja' => 'OSDC賞',
                        'active' => 1,
                        'action_code' => 'project.me.reward.submit'
                    ],
                    'project.me.reward.approve' => [
                        'path' => 'monthly-evaluation-reward/review',
                        'label' => 'Review thưởng OSDC',
                        'label_en' => 'OSDC Bonus Reviews',
                        'label_ja' => 'OSDC賞のリビュー',
                        'active' => 1,
                        'action_code' => 'project.me.reward.approve'
                    ],
                    'project.me.config_data' => [
                        'path' => 'monthly-evaluation/config-data',
                        'label' => 'Cấu hình dữ liệu',
                        'label_en' => 'Data Configuration',
                        'label_ja' => 'データコンフィグ',
                        'active' => 1,
                        'action_code' => 'project.me.config_data'
                    ]
                ]
            ],
            'member.report' => [
                'path' => '#',
                'label' => 'Báo cáo nhân viên',
                'label_en' => 'Member Report',
                'label_ja' => 'メンバーレポート',
                'active' => '1',
                'child' => [
                    'team.timekeeping.aggregates' => [
                        'path' => 'team/list-timekeeping-aggregates',
                        'label' => 'Bảng công nhân viên',
                        'label_en' => 'Timesheets',
                        'label_ja' => '勤怠表',
                        'active' => '1',
                        'action_code' => 'view.team.timekeeping.aggregates',
                    ],
                    'team.report.minute_late' => [
                        'path' => 'team/late-minute-report',
                        'label' => 'Thống kê phút đi muộn và tiền phạt',
                        'label_en' => 'Report late minute and fine',
                        'label_ja' => 'Report late minute and fine',
                        'active' => '1',
                        'action_code' => 'view.team.report.late_minute',
                    ],
                    'education.ot' => [
                        'path' => 'team/OT_list',
                        'label' => 'Thống kê OT',
                        'label_en' => 'Statistic OT',
                        'label_ja' => 'Thống kê OT',
                        'active' => '1',
                        'action_code' => 'education.ot.list',
                    ],
                    'division.leave_day' => [
                        'path' => 'team/list-day-of-leave',
                        'label' => 'Phép của nhân viên',
                        'label_en' => 'List leave day',
                        'label_ja' => '',
                        'active' => '1',
                        'action_code' => 'division.leave_day.list',
                    ],
                    'team.report.attendance-table' => [
                        'path' => 'team/attendance-table-report',
                        'label' => 'Bảng điểm danh',
                        'label_en' => 'Attendance table',
                        'label_ja' => '出勤表',
                        'active' => '1',
                        'action_code' => 'view.team.report.attendance_table',
                    ],
                    'team.report.check-time-sheet' => [
                        'path' => 'team/check-time-sheet',
                        'label' => 'Kiểm tra nhập timesheet',
                        'label_en' => 'Check timesheet',
                        'label_ja' => '打刻確認',
                        'active' => '1',
                        'action_code' => 'view.team.report.check-time-sheet',
                    ],
                    'team.report.thoughts' => [
                        'path' => 'team/problems-thoughts-rp',
                        'label' => 'Vấn đề, cảm tưởng',
                        'label_en' => 'Problems, thoughts',
                        'label_ja' => '悩み、感想',
                        'active' => '1',
                        'action_code' => 'view.team.report.problem_thoughts',
                    ],
                    'team.report.payment-approval' => [
                        'path' => '#',
                        'label' => 'Duyệt chi trả',
                        'label_en' => 'Payment approval',
                        'label_ja' => '支払承認',
                        'active' => '1',
                        'action_code' => 'view.team.report.payment-approval',
                        'child' => [
                            'buying' => [
                                'path' => 'team/buying-approval',
                                'label' => 'Duyệt mua đồ',
                                'label_en' => 'Buying approval',
                                'label_ja' => '買物承認',
                                'active' => '1'
                            ],
                            'transfer' => [
                                'path' => 'team/transfer-approval',
                                'label' => 'Duyệt di chuyển',
                                'label_en' => 'Transfer approval',
                                'label_ja' => '交通費承認',
                                'active' => '1'
                            ]
                        ]
                    ],
                    'team.report.payment-statistics' => [
                        'path' => 'team/payment-statistics',
                        'label' => 'Thông kê chi trả',
                        'label_en' => 'Payment statistics',
                        'label_ja' => '支払統計',
                        'active' => '1',
                        'action_code' => 'view.team.report.payment-statistics',
                    ],
                    'team.report.employee-setting-by-month' => [
                        'path' => 'team/employee-setting-by-month',
                        'label' => 'Thiết lập nhân viên theo tháng',
                        'label_en' => 'Employee setting by month',
                        'label_ja' => '月ごとの従業員設定',
                        'active' => '1',
                        'action_id' => 1//Confirm
                    ]
                ],
            ],
            'education.request' => [
                'path' => '#',
                'label' => 'Yêu cầu đào tạo',
                'label_en' => 'Training request',
                'label_ja' => 'トレーニングリクエスト',
                'active' => '1',
                'child' => [
                    'request.list' => [
                        'path' => 'team/training-request/list',
                        'label' => 'Danh sách yêu cầu',
                        'label_en' => 'List training',
                        'label_ja' => 'リストトレーニング',
                        'active' => '1',
                        'action_code' => 'education.request.team.dlead',
                    ],
                    'request.create' => [
                        'path' => 'team/training-request/create',
                        'label' => 'Tạo mới yêu cầu',
                        'label_en' => 'Create training',
                        'label_ja' => 'トレーニングを作成する',
                        'active' => '1',
                        'action_code' => 'education.request.team.dlead'
                    ]
                ]
            ],

            'setting_admin' => [
                'path' => '#',
                'label' => 'Cài đặt',
                'label_en' => 'Setting',
                'label_ja' => '設定',
                'active' => '1',
                'child' => [
                    'setting_admin.ot' => [
                        'path' => 'setting/ot-admin',
                        'label' => 'Danh sách nhân viên không được tính OT',
                        'label_en' => 'OT Disallow',
                        'label_ja' => 'OT Disallow',
                        'active' => '1',
                        'action_code' => 'admin.setting.ot-admin',
                    ],

                ]
            ],

        ]
    ],
    'project' => [
        'path' => '#',
        'label' => 'Dự án',
        'label_en' => 'Project',
        'label_ja' => 'プロジェクト',
        'active' => '1',
        'child' => [
            'project.manage' => [
                'path' => '#',
                'label' => 'Quản lý',
                'label_en' => 'Project Management',
                'label_ja' => 'プロジェクト管理',
                'active' => '1',
                'child' => [
                    'project.point.dashboard' => [
                        'path' => 'project/dashboard',
                        'label' => 'Project dashboard',
                        'label_en' => 'Project dashboard',
                        'label_ja' => 'プロジェクトダッシュボード',
                        'active' => '1'
                    ],
                    'project.point.create' => [
                        'path' => 'project/create',
                        'label' => 'Thêm dự án',
                        'label_en' => 'Create a project',
                        'label_ja' => 'プロジェクトを作成',
                        'active' => '1',
                        'action_code' => 'project.create'
                    ],
                    'project.statistic.dashboard' => [
                        'path' => 'project/statistic',
                        'label' => 'Production Dashboard',
                        'label_en' => 'Production Dashboard',
                        'label_ja' => 'プロジェクト生産ダッシュボード',
                        'active' => '1',
                        'action_code' => 'project.statistic.dashboard',
                    ],
                    'project.statistic.dashboard.slide' => [
                        'path' => 'project/statistic/slide',
                        'label' => 'Production Dashboard (Slides)',
                        'label_en' => 'Production Dashboard (Slides)',
                        'label_ja' => 'プロジェクト生産ダッシュボードのスライド',
                        'active' => '1',
                        'action_code' => 'project.statistic.dashboard',
                    ],
                ],
            ],
            'report.project.reward' => [
                'path' => 'report/reward',
                'label' => 'Thưởng',
                'label_en' => 'Bonuses',
                'label_ja' => '賞',
                'active' => '1',
                'action_code' => 'project.reward.view',
            ],
            'project.baseline.baselineAll' => [
                'path' => '#',
                'label' => 'Baseline',
                'label_en' => 'Baseline',
                'label_ja' => 'ベースライン',
                'active' => '1',
                'child' => [
                    'project.baseline.all' => [
                        'path' => 'project/baseline-all',
                        'label' => 'Baseline (All)',
                        'label_en' => 'Baseline (All)',
                        'label_ja' => '全てのベースライン',
                        'active' => '1',
                        'action_code' => 'project.baselineAll.view',
                    ],
                    'report.kpi' => [
                        'path' => 'kpi',
                        'label' => 'KPI',
                        'label_en' => 'KPI',
                        'label_ja' => 'KPI',
                        'active' => '1',
                        'action_code' => 'project::report.kpi.index',
                    ],
                ],
            ],
            'kl.menu' => [
                'path' => 'tag/search/project',
                'label' => 'Knowhow',
                'label_en' => 'Knowhow',
                'label_ja' => '知識システム',
                'active' => '1',
                'child' => [
                    'kl.search.project' => [
                        'path' => 'tag/search/project',
                        'label' => 'Tìm kiếm dự án',
                        'label_en' => 'Project Searching',
                        'label_ja' => 'プロジェクト検索',
                        'active' => '1',
                        'action_code' => 'kl.view.project.search',
                    ],
                    'kl.object.project.tag' => [
                        'path' => 'tag/object/project',
                        'label' => 'Project tagging',
                        'label_en' => 'Project tagging',
                        'label_ja' => 'プロジェクトのタグ付け',
                        'active' => '1',
                        'action_code' => 'kl.view.project.tag.tagging',
                    ],
                    'kl.field.manage' => [
                        'path' => 'tag/field/manage',
                        'label' => 'Cài đặt trường dữ liệu',
                        'label_en' => 'Field Settings',
                        'label_ja' => 'セッティングフィールド',
                        'active' => '1',
                        'action_code' => 'kl.field.manage',
                    ],
                ],
            ],
            'proj.issue' => [
                'path' => '#',
                'label' => 'Các vấn đề',
                'label_en' => 'Issues',
                'label_ja' => '課題',
                'active' => '1',
                'child' => [
                    'report.risk' => [
                        'path' => '#',
                        'label' => 'Rủi ro',
                        'label_en' => 'Risks',
                        'label_ja' => 'リスク',
                        'active' => '1',
                        'child' => [
                            'report.list' => [
                                'path' => 'report/risk',
                                'label' => 'Danh sách',
                                'label_en' => 'List',
                                'label_ja' => 'リスト',
                                'active' => '1',
                                'action_code' => 'report.risk',
                            ],
                            'report.commonRisk' => [
                                'path' => 'report/common-risk',
                                'label' => 'Common Risk',
                                'label_en' => 'Common Risk',
                                'label_ja' => '共通のリスク',
                                'active' => '1',
                                // 'action_code' => 'report.common-risk',
                            ],
                        ],
                        
                    ],
                    'report.opportunity' => [
                        'path' => 'report/opportunity',
                        'label' => 'Opportunity',
                        'label_en' => 'Opportunity',
                        'label_ja' => '機会',
                        'active' => '1',
                        'action_code' => 'report.opportunity',
                    ],
                    'report.ncm' => [
                        'path' => 'report/ncm',
                        'label' => 'NCM',
                        'label_en' => 'NCM',
                        'label_ja' => 'NCM',
                        'active' => '1',
                        'action_code' => 'report.ncm',
                    ],
                    'report.issue' => [
                        'path' => 'report/issue',
                        'label' => 'Issues',
                        'label_en' => 'Issues',
                        'label_ja' => 'Issues',
                        'active' => '1',
                        'child' => [
                            'issue.list' => [
                                'path' => 'report/issue',
                                'label' => 'Danh sách',
                                'label_en' => 'List',
                                'label_ja' => 'リスト',
                                'active' => '1',
                                'action_code' => 'report.issue',
                            ],
                            'issue.commonIssue' => [
                                'path' => 'report/common-issue',
                                'label' => 'Common Issue',
                                'label_en' => 'Common Issue',
                                'label_ja' => '共通の問題',
                                'active' => '1',
                                // 'action_code' => 'report.common-issue',
                            ],
                        ],
                    ],
                    'report.gitlab' => [
                        'path' => 'report/gitlab',
                        'label' => 'Gitlab',
                        'label_en' => 'Gitlab',
                        'label_ja' => 'Gitlab',
                        'active' => '1',
                        'action_code' => 'report.gitlab',
                    ],
                ],
            ],
            'project_operation' => [
                'path' => '#',
                'label' => 'Operation',
                'label_en' => 'Operation',
                'label_ja' => '操作',
                'active' => '1',
                'child' => [
                    'project_operation_overview' => [
                        'path' => 'project-operations/overview',
                        'label' => 'Overview',
                        'label_en' => 'Overview',
                        'label_ja' => '概要',
                        'active' => '1',
                        'action_code' => 'project.operation_overview.report',
                    ],
                    'project_operation_member' => [
                        'path' => 'project-operations/members',
                        'label' => 'Member report',
                        'label_en' => 'Member report',
                        'label_ja' => 'メンバーレポート',
                        'active' => '1',
                        'action_code' => 'project.operation_member.report',
                    ],
                    'project_operation_project' => [
                        'path' => 'project-operations/projects',
                        'label' => 'Project report',
                        'label_en' => 'Project report',
                        'label_ja' => 'プロジェクトレポート',
                        'active' => '1',
                        'action_code' => 'project.operation_project.report',
                    ],
                ],
            ],
            'project_setting' => [
                'path' => 'project/setting',
                'label' => 'Thiết lập',
                'label_en' => 'Project Settings',
                'label_ja' => 'プロジェクト設定',
                'active' => '1',
                'action_code' => 'project.setting.general'
            ],
            'timesheet' => [
                'path' => 'timesheets',
                'label' => 'Time sheet',
                'label_en' => 'Time sheet',
                'label_ja' => 'Time sheet',
                'active' => '1',
                'action_code' => 'timesheet.list'
            ],
            'purchase_order_crm' => [
                'path' => 'purchase-order/crm/list',
                'label' => 'Danh sách hợp đồng CRM',
                'label_en' => 'List purchase order CRM',
                'label_ja' => '発注書CRMのリスト',
                'active' => '1',
                'action_code' => 'purchaseOrder.list'
            ],
        ],
    ],
    'sales' => [
        'path' => '#',
        'label' => 'Sales',
        'label_en' => 'Sales',
        'label_ja' => '営業',
        'active' => '1',
        'child' => [
            'sales.css' => [
                'path' => 'sale/css',
                'label' => 'CSS',
                'label_en' => 'CSS',
                'label_ja' => 'CSS',
                'active' => '1',
                'child' => [
                    'css.list' => [
                        'path' => 'sales/css/list',
                        'label' => 'Danh sách',
                        'label_en' => 'CSS List',
                        'label_ja' => 'CSS一覧',
                        'active' => '1',
                        'action_code' => 'view.list.css',
                    ],
                    'css.create' => [
                        'path' => 'css/create',
                        'label' => 'Tạo CSS',
                        'label_en' => 'Create a CSS',
                        'label_ja' => 'CSSを作成',
                        'active' => '1',
                        'action_code' => 'edit.detail.css',
                    ],
                    'view.analyze.css' => [
                        'path' => 'css/analyze',
                        'label' => 'Phân tích',
                        'label_en' => 'CSS Analysis',
                        'label_ja' => 'CSS分析',
                        'active' => '1',
                        'action_code' => 'view.analyze.css',
                    ],
                ],
            ],
            'customer' => [
                'label' => 'Khách hàng đại diện',
                'label_en' => 'Customers',
                'label_ja' => 'お客様',
                'active' => '1',
                'child' => [
                    'customer.list' => [
                        'path' => 'customer/list',
                        'label' => 'Danh sách khách hàng',
                        'label_en' => 'Customer List',
                        'label_ja' => 'お客様一覧',
                        'active' => '1',
                        'action_code' => 'list.customer'
                    ],
                    // 'customer.create' => [
                    //     'path' => 'customer/create',
                    //     'label' => 'Thêm khách hàng',
                    //     'label_en' => 'Create a customer',
                    //     'label_ja' => 'お客様の情報を作成',
                    //     'active' => '1',
                    //     'action_code' => 'add.customer'
                    // ],
                ],
            ],
            'company' => [
                'label' => 'Công ty khách hàng',
                'label_en' => 'Companies',
                'label_ja' => '会社',
                'active' => '1',
                'child' => [
                    'company.list' => [
                        'path' => 'company/list',
                        'label' => 'Danh sách Công ty',
                        'label_en' => 'Company List',
                        'label_ja' => '会社名一覧',
                        'active' => '1',
                        'action_code' => 'list.company'
                    ],
                    'customer.create' => [
                        'path' => 'company/create',
                        'label' => 'Thêm Công ty',
                        'label_en' => 'Create a company',
                        'label_ja' => '会社名を作成',
                        'active' => '1',
                        'action_code' => 'add.company'
                    ],
                ],
            ],
            'tracking' => [
                'path' => 'sales/tracking',
                'label' => 'Theo dõi',
                'label_en' => 'Tracking',
                'label_ja' => 'トラッキング',
                'active' => '1',
                'action_code' => 'sales.tracking',
            ],
            'request.opportunity' => [
                'label' => 'Cơ hội',
                'label_en' => 'Opportunities',
                'label_ja' => 'オポチュニティ',
                'active' => '1',
                'child' => [
                    'request.oppor.list' => [
                        'path' => 'sales/request-opportunity',
                        'label' => 'Danh sách yêu cầu',
                        'label_en' => 'Opportunity Request List',
                        'label_ja' => 'オポチュニティリクエスト一覧',
                        'active' => '1',
                        'action_code' => 'sales.request.opportunity'
                    ],
                    'request.oppor.create' => [
                        'path' => 'sales/request-opportunity/view',
                        'label' => 'Thêm yêu cầu',
                        'label_en' => 'Create an Opportunity Request',
                        'label_ja' => 'オポチュニティリクエストを作成',
                        'active' => '1',
                        'action_code' => 'sales.edit.request.opportunity'
                    ]
                ],
            ],
        ],
    ],
    'resource' => [
        'path' => '#',
        'label' => 'Resources',
        'label_en' => 'Resources',
        'label_ja' => 'リソース',
        'active' => '1',
        'child' => [
            'resource.request' => [
                'path' => '#',
                'label' => 'Yêu cầu',
                'label_en' => 'Request',
                'label_ja' => 'リクエスト',
                'active' => '1',
                'child' => [
                    'request.list' => [
                        'path' => 'resource/request/list',
                        'label' => 'Danh sách',
                        'label_en' => 'Request list',
                        'label_ja' => 'リクエスト一覧',
                        'active' => '1',
                        'action_code' => 'list.resource request',
                    ],
                    'request.create' => [
                        'path' => 'resource/request/create',
                        'label' => 'Thêm yêu cầu',
                        'label_en' => 'Create a request',
                        'label_ja' => 'リクエストを作成',
                        'active' => '1',
                        'action_code' => 'edit.resource request',
                    ]
                ]
            ],
            'resource.candidate' => [
                'path' => '#',
                'label' => 'Ứng viên',
                'label_en' => 'Candidates',
                'label_ja' => '候補者',
                'active' => '1',
                'child' => [
                    'candidate.list' => [
                        'path' => 'resource/candidate/list',
                        'label' => 'Danh sách',
                        'label_en' => 'Candidate list',
                        'label_ja' => '候補者一覧',
                        'active' => '1',
                        'action_code' => 'list.candidate',
                    ],
                    'candidate.create' => [
                        'path' => 'resource/candidate/create',
                        'label' => 'Thêm ứng viên',
                        'label_en' => 'Create a candidate',
                        'label_ja' => '候補者情報を作成',
                        'active' => '1',
                        'action_code' => 'create.candidate',
                    ],
                    'candidate.search' => [
                        'path' => 'resource/candidate/search',
                        'label' => 'Tìm kiếm nâng cao',
                        'label_en' => 'Advanced Search',
                        'label_ja' => 'アドバンス検索',
                        'active' => '1',
                        'action_code' => 'search.candidate',
                    ],
                    'candidate.history' => [
                        'path' => 'resource/candidate/history',
                        'label' => 'Lịch sử ứng viên',
                        'label_en' => 'Candidate History',
                        'label_ja' => '候補の活動歴史一覧',
                        'active' => '1',
                        'action_code' => 'history.candidate',
                    ],
                    'candidate.interested' => [
                        'path' => 'resource/candidate/interested',
                        'label' => 'Ứng viên quan tâm',
                        'label_en' => 'Ứng viên quan tâm',
                        'label_ja' => 'Ứng viên quan tâm',
                        'active' => '1',
                        'action_code' => 'interested.candidate',
                    ],
                    'candidate.importcv' => [
                        'path' => 'resource/candidate/importcv',
                        'label' => 'Nhập CV ',
                        'label_en' => 'CV Import ',
                        'label_ja' => '履歴書を輸入 ',
                        'active' => '1',
                        'action_code' => 'importcv.candidate',
                    ],
                    'candidate.checkExist' => [
                        'path' => 'resource/candidate/checkExist',
                        'label' => 'Kiểm tra sự tồn tại của ứng viên',
                        'label_en' => 'Candidate Existence Check',
                        'label_ja' => '存在性チェック',
                        'active' => '1',
                        'action_code' => 'checkExist.candidate',
                    ],
                    'candidate.test.history' => [
                        'path' => 'resource/candidate/test-schedule',
                        'label' => 'Lịch test',
                        'label_en' => 'Schedule a test',
                        'label_ja' => 'スケジュールテスト',
                        'active' => '1',
                        'action_code' => 'list.candidate'
                    ],
                    'candidate.follow' => [
                        'path' => 'resource/candidate/follow',
                        'label' => 'Theo dõi ứng viên',
                        'label_en' => 'Follow candidate',
                        'label_ja' => 'Follow candidate',
                        'active' => '1',
                        'action_code' => 'follow.candidate',
                    ],
                    'candidate.email.marketing' => [
                        'path' => 'recruitment/email-marketing',
                        'label' => 'Gửi email marketing',
                        'label_en' => 'Send email marketing',
                        'label_ja' => 'Send email marketing',
                        'active' => '1',
                        'action_code' => 'candidate.send_email_marketing',
                    ],
                    'candidate.recommend' => [
                        'path' => '#',
                        'label' => 'Giới thiệu ứng viên',
                        'label_en' => 'Recommend candidate',
                        'active' => '1',
                        'child' => [
                            'recommend' => [
                                'path' => 'resource/candidate/recommend',
                                'label' => 'Giới thiệu ứng viên',
                                'label_en' => 'Recommend candidate',
                                'active' => '1',
                            ],
                            'list.recommend' => [
                                'path' => 'resource/candidate/recommend/list',
                                'label' => 'Danh sách ứng viên đã giới thiệu',
                                'label_en' => 'List recommend candidate',
                                'active' => '1',
                            ],
                        ]
                    ],
                ]
            ],
            'resource.report' => [
                'path' => '#',
                'label' => 'Báo cáo thống kê',
                'label_en' => 'Statistics report',
                'label_ja' => '統計報告',
                'active' => '1',
                'child' => [
                    'staff.report.statistics' => [
                        'path' => 'resource/staff-statistics/month',
                        'label' => 'Thống kê nhân viên',
                        'label_en' => 'Employee statistics',
                        'label_ja' => 'Employee statistics',
                        'active' => '1',
                        'action_code' => 'staff.statistics',
                    ],
                    'recruit.report.statistics' => [
                        'path' => 'resource/recruitment',
                        'label' => 'Thống kê tuyển dụng',
                        'label_en' => 'Recruitment statistics',
                        'label_ja' => '採用統計',
                        'active' => '1',
                        'action_code' => 'candidate.recruit.report'
                    ],
                    'recruit.report.candidate' => [
                        'path' => 'resource/candidate/report',
                        'label' => 'Thống kê ứng viên',
                        'label_en' => 'Candidate statistics',
                        'label_ja' => 'Candidate statistics',
                        'active' => '1',
                        'action_code' => 'candidate.candidate.report'
                    ],
                    'recruit.report.plan' => [
                        'path' => 'resource/recruitment/plan',
                        'label' => 'Kế hoạch tuyển dụng',
                        'label_en' => 'Recruitment plan',
                        'label_ja' => '採用計画',
                        'active' => '1',
                        'action_code' => 'candidate.recruit.report'
                    ],
                    'recruit.manage.team.plan' => [
                        'path' => 'resource/teams-feature',
                        'label' => 'Danh sách kế hoạch của team',
                        'label_en' => 'Team plan list',
                        'label_ja' => 'チームの計画一覧',
                        'active' => '1',
                        'action_code' => 'recruit.manage.team.plan'
                    ]
                ]
            ],
            'resource.dashboard' => [
                'path' => 'resource/dashboard/index',
                'label' => 'Dashboard',
                'label_en' => 'Dashboard',
                'label_ja' => 'ダッシュボード',
                'active' => '1',
                'action_code' => 'dashboard.resource',
            ],
            'resource.utilization' => [
                'path' => 'resource/dashboard/utilization',
                'label' => 'Utilization',
                'label_en' => 'Utilization',
                'label_ja' => 'Utilization',
                'active' => '1',
                'action_code' => 'utilization.resource',
            ],
            'resource.available' => [
                'path' => 'resource/employees-available',
                'label' => 'Nhân viên rảnh',
                'label_en' => 'Available Resources',
                'label_ja' => '利用可能なリソース',
                'active' => '1',
                'action_code' => 'resource.available'
            ],
            'resource.setting' => [
                'path' => '#',
                'label' => 'Cài đặt',
                'label_en' => 'Settings',
                'label_ja' => '設定',
                'active' => '1',
                'child' => [
                    'resource.channel' => [
                        'path' => '#',
                        'label' => 'Kênh tuyển dụng',
                        'label_en' => 'Channels',
                        'label_ja' => 'チャンネル',
                        'active' => '1',
                        'child' => [
                            'channel.list' => [
                                'path' => 'resource/setting/channel/list',
                                'label' => 'Danh sách',
                                'label_en' => 'Channel list',
                                'label_ja' => 'チャンネルリスト',
                                'active' => '1',
                                'action_code' => 'channel list.resource',
                            ],
                            'channel.create' => [
                                'path' => 'resource/setting/channel/create',
                                'label' => 'Thêm mới',
                                'label_en' => 'Create a channel',
                                'label_ja' => 'チャンネルを新規作成',
                                'active' => '1',
                                'action_code' => 'channel.resource',
                            ]
                        ]
                    ],
                    'resource.programminglanguage' => [
                        'path' => '#',
                        'label' => 'Ngôn ngữ lập trình',
                        'label_en' => 'Programming languages',
                        'label_ja' => '開発言語',
                        'active' => '1',
                        'child' => [
                            'programminglanguages.list' => [
                                'path' => 'resource/setting/programminglanguages/list',
                                'label' => 'Danh sách',
                                'label_en' => 'Programming Language List',
                                'label_ja' => '開発言語一覧',
                                'active' => '1',
                                'action_code' => 'list.programminglanguages12'
                            ],
                            'programminglanguages.create' => [
                                'path' => 'resource/setting/programminglanguages/create',
                                'label' => 'Thêm mới',
                                'label_en' => 'Add a programming language',
                                'label_ja' => '開発言語を新規作成',
                                'active' => '1',
                                'action_code' => 'edit.programminglanguages12'
                            ]
                        ]
                    ],
                    'resource.language' => [
                        'path' => '#',
                        'label' => 'Ngôn ngữ',
                        'label_en' => 'Languages',
                        'label_ja' => '言語',
                        'active' => '1',
                        'child' => [
                            'language.list' => [
                                'path' => 'resource/setting/languages/list',
                                'label' => 'Danh sách',
                                'label_en' => 'Language List',
                                'label_ja' => '言語一覧',
                                'active' => '1',
                                'action_code' => 'list.languages12',
                            ],
                            'language.create' => [
                                'path' => 'resource/setting/languages/create',
                                'label' => 'Thêm mới',
                                'label_en' => 'Add a language',
                                'label_ja' => '言語を新規作成',
                                'active' => '1',
                                'action_code' => 'edit.languages12',
                            ],
                        ]
                    ],
                ]
            ],
            'resource.hr_weekly_report' => [
                'path' => 'resource/hr-weekly-report',
                'label' => 'Báo cáo nhân sự hàng tuần',
                'label_en' => 'HR Weekly Report',
                'label_ja' => '人事部の週報',
                'active' => '1',
                'action_code' => 'hr.weekly.report'
            ],
            'resource.monthly_report' => [
                'path' => 'resource/report/monthly',
                'label' => 'Báo cáo tuyển dụng hàng tháng',
                'label_en' => 'Monthly recruitment report',
                'label_ja' => 'Monthly recruitment report',
                'active' => '1',
                'action_code' => 'recruitment.monthly.report',
            ],
//            'resource.busy.rate' => [
//                'path' => 'resource/busy',
//                'label' => 'Busy rate',
//                'active' => '1',
//                'action_code' => 'view.profile.v1'
//            ]
        ]
    ],
    'hr' => [
        'path' => '#',
        'label' => 'HR',
        'label_en' => 'HR',
        'label_ja' => '人事部の人',
        'active' => '1',
        'child' => [
            'add.employee' => [
                'path' => 'profile/create',
                'label' => 'Thêm nhân viên',
                'label_en' => 'Add an employee',
                'label_ja' => '従業者の情報を作成する',
                'active' => '1',
                'action_code' => 'edit.profile.v1',
            ],
            'upload.member' => [
                'path' => 'team/member/upload',
                'label' => 'Import nhân viên',
                'label_en' => 'Import Employees',
                'label_ja' => 'メンバーアップロード',
                'active' => '1',
                'action_code' => 'upload.team.member',
            ],
            'upload.family_info' => [
                'path' => 'team/member/upload-family-info',
                'label' => 'Import thông tin gia đình nhân viên',
                'label_en' => 'Import family info',
                'label_ja' => '家族情報をインポート',
                'active' => '1',
                'action_code' => 'upload.team.member',
            ],
            'hr.child_test' => [
                'path' => '#',
                'label' => 'Test',
                'label_en' => 'Test',
                'label_ja' => '試験',
                'active' => '1',
                'child' => [
                    'test.index' => [
                        'path' => 'test/manage/tests',
                        'label' => 'Danh sách',
                        'label_en' => 'Test List',
                        'label_ja' => '試験リスト',
                        'active' => '1',
                        'action_code' => 'test.manage'
                    ],
                    'test.create' => [
                        'path' => 'test/manage/tests/create',
                        'label' => 'Thêm mới',
                        'label_en' => 'Create a Test',
                        'label_ja' => '試験を新規作成',
                        'active' => '1',
                        'action_code' => 'test.manage'
                    ],
                    'type.list_types' => [
                        'path' => 'test/manage/types',
                        'label' => 'Danh sách các loại kiểm tra',
                        'label_en' => 'Test Subject List',
                        'label_ja' => '試験種別',
                        'active' => '1',
                        'action_code' => 'test.type.manage'
                    ],
                    'test.candidate.infor' => [
                        'path' => 'test/candidate-information',
                        'label' => 'Thông tin ứng viên',
                        'label_en' => 'Candidate information',
                        'label_ja' => '候補者情 報',
                        'active' => '1',
                        'action_code' => 'test.manage.candidate'
                    ],
                    'test.upload_test_files' => [
                        'path' => 'test/upload-files',
                        'label' => 'Tải lên hình ảnh',
                        'label_en' => 'Upload test images',
                        'label_ja' => '試験画像をアップロード',
                        'active' => '1',
                        'action_code' => 'test.manage'
                    ],
                    'test.exam_list' => [
                        'path' => 'test/exam-list',
                        'label' => 'Danh sách làm bài thi',
                        'label_en' => 'Exam list',
                        'label_ja' => '試験リスト',
                        'active' => '1',
                        'action_code' => 'test.manage.exam'
                    ]
                ]
            ],
            'resource.enrollment_advice' => [
                'path' => 'resource/enrollment-advice',
                'label' => 'Enrollment Advice',
                'label_en' => 'Enrollment Advice',
                'label_ja' => '採用担当アドバイス ',
                'active' => '1',
                'action_code' => 'list.candidate',
            ],
            'education.manage' => [
                'path' => '#',
                'label' => 'Quản lý đào tạo',
                'label_en' => 'Education management',
                'label_ja' => '教育管理',
                'active' => '1',
                //'action_code' => 'education.request.team.hr',
                'child' => [
                    'hr.register_teachings' => [
                        'path' => 'manager/hr/teachings',
                        'label' => 'Quản lý đăng ký giảng dạy',
                        'label_en' => 'Teaching registration management',
                        'label_ja' => '登録管理の指導',
                        'active' => '1',
                        'action_code' => 'hr.teachings',
                    ],
                    'education.request.hr' => [
                        'path' => 'hr/training-request/list',
                        'label' => 'Danh sách yêu cầu đào tạo',
                        'label_en' => 'Training request list',
                        'label_ja' => 'トレーニングリクエストリスト',
                        'active' => '1',
                        'action_code' => 'education.request.team.hr',
                    ],
                    'education.manager-courses' => [
                        'path' => '#',
                        'label' => 'Khóa học đào tạo',
                        'label_en' => 'Education Manager',
                        'label_ja' => '教育マネージャー',
                        'active' => '1',
                        'child' => [
                            'education.list' => [
                                'path' => 'HR/course/list',
                                'label' => 'Danh sách khóa học',
                                'label_en' => 'Education List',
                                'label_ja' => '教育リスト',
                                'active' => '1',
                                'action_code' => 'education.managers.list'
                            ],
                            'education.create' => [
                                'path' => 'HR/course/new',
                                'label' => 'Tạo mới khóa học',
                                'label_en' => 'Education Create',
                                'label_ja' => '教育クリエイト',
                                'active' => '1',
                                'action_code' => 'education.managers.create'
                            ]
                        ],
                    ],
                    'education.manager.employees' => [
                        'path' => 'manager/employees',
                        'label' => 'Thống kê đào tạo',
                        'label_en' => 'Training statistics',
                        'label_ja' => 'トレーニング統計',
                        'active' => '1',
                        'action_code' => 'education.manager.employees',
                    ],
                    'education.hr.certificates' => [
                        'path' => 'hr/certificates',
                        'label' => 'Danh sách chứng chỉ',
                        'label_en' => 'List of certificates',
                        'label_ja' => '証明書のリスト',
                        'active' => '1',
                        'action_code' => 'education.hr.certificates',
                    ],
                    'education.manager' => [
                        'path' => '#',
                        'label' => 'Cài đặt',
                        'label_en' => 'Setting',
                        'label_ja' => '試験',
                        'active' => '1',
                        'child' => [
                            'education.setting.type' => [
                                'path' => '#',
                                'label' => 'Đào tạo',
                                'label_en' => 'Education',
                                'label_ja' => '教育',
                                'active' => '1',
                                'child' => [
                                    'education.setting.type.list' => [
                                        'path' => 'setting/educations/types',
                                        'label' => 'Danh sách',
                                        'label_en' => 'Education List',
                                        'label_ja' => '教育リスト',
                                        'active' => '1',
                                        'action_code' => 'crud.education12',
                                    ],
                                    'education.setting.type.create' => [
                                        'path' => 'setting/educations/types/create',
                                        'label' => 'Thêm mới',
                                        'label_en' => 'Add a education',
                                        'label_ja' => '教育を追加',
                                        'active' => '1',
                                        'action_code' => 'crud.education12',
                                    ],
                                ]
                            ],
                            'education.setting.template.mail' => [
                                'path' => 'setting/educations/template-mails',
                                'label' => 'Mẫu email',
                                'label_en' => 'Template mail',
                                'label_ja' => 'メールテンプレート',
                                'active' => '1',
                                'action_code' => 'template.mail.list.education12'
                            ],
                            'education.setting.branch.mail' => [
                                'path' => 'setting/educations/branches',
                                'label' => 'Địa chỉ mail',
                                'label_en' => 'Address mail',
                                'label_ja' => 'メールアドレス',
                                'active' => '1',
                                'action_code' => 'branches.mail.list.education12'
                            ],
                        ],
                    ],
                ],
            ],
            'report.employee_onsite' => [
                'path' => 'hr/report-onsite',
                'label' => 'Thống kê nhân viên onsite',
                'label_en' => 'Report employee onsite',
                'label_ja' => 'Report employee onsite',
                'active' => '1',
                'action_code' => 'report.employee_onsite.list'
            ],
        ]
    ],
    'admin' => [
        'path' => '#',
        'label' => 'Admin',
        'label_en' => 'Admin',
        'label_ja' => 'アドミン',
        'active' => '1',
        'child' => [
            'contract' => [
                'path' => 'manage/contract/list/all',
                'label' => 'Quản lý hợp đồng',
                'label_en' => 'contract management',
                'label_ja' => 'contract management',
                'active' => '1',
                'action_code' => 'manage.contract',
            ],
            'music' => [
                'path' => '#',
                'label' => 'Âm nhạc',
                'label_en' => 'Music',
                'label_ja' => '音楽',
                'active' => '1',
                'child' => [
                    'order' => [
                        'path' => '/music/order',
                        'label' => 'Yêu cầu',
                        'label_en' => 'Music for request',
                        'label_ja' => '音楽リクエスト',
                        'active' => '1',
                    ],
                    'music.order' => [
                        'path' => 'music/manage/order',
                        'label' => 'Danh sách yêu cầu',
                        'label_en' => 'Order List',
                        'label_ja' => 'リスト順',
                        'active' => '1',
                        'action_code' => 'music.order.list',
                    ],
                    'music.office' => [
                        'path' => 'music/manage/offices',
                        'label' => 'Danh sách văn phòng',
                        'label_en' => 'Office List',
                        'label_ja' => 'リストオフィス',
                        'active' => '1',
                        'action_code' => 'music.office.list',
                    ],
                ]
            ],
            'it' => [
                'path' => 'it/request',
                'label' => 'Danh sách yêu cầu hỗ trợ IT',
                'label_en' => 'IT Support Request List',
                'label_ja' => 'IT部へのリクエスト',
                'active' => '1',
            ],
            'pr' => [
                'path' => '#',
                'label' => 'Truyền thông',
                'label_en' => 'PR',
                'label_ja' => '広報活動',
                'active' => '1',
                'child' => [
                    'admin.slide-show' => [
                        'path' => '#',
                        'label' => 'Trình chiếu',
                        'label_en' => 'Slide show',
                        'label_ja' => 'スライドショー',
                        'active' => '1',
                        'child' => [
                            'slide-show.show' => [
                                'path' => 'slide-show',
                                'label' => 'Màn hình trình chiếu',
                                'label_en' => 'Slide show screen',
                                'label_ja' => 'スライドショー画面',
                                'active' => '1',
                                'action_code' => 'slide-show',
                            ],
                            'slide-show.list' => [
                                'path' => 'slide-show/list-slider',
                                'label' => 'Danh sách trình chiếu',
                                'label_en' => 'Slide show list',
                                'label_ja' => 'スライドショーリスト',
                                'active' => '1',
                                'action_code' => 'slide-show.setting',
                            ],
                            'slide-show.setting' => [
                                'path' => 'slide-show/setting',
                                'label' => 'Cài đặt trình chiếu',
                                'label_en' => 'Slide show Settings',
                                'label_ja' => 'スライドショー設定',
                                'active' => '1',
                                'action_code' => 'slide-show.setting',
                            ],
                            'slide-show.birthday' => [
                                'path' => 'slide_show/birthday',
                                'label' => 'Trình chiếu nội dung sinh nhật',
                                'label_en' => 'Birthday Contents for batch sending',
                                'label_ja' => '誕生日の内容のスライドショー',
                                'active' => '1',
                                'action_code' => 'slide-show.setting',
                            ],
                        ],
                    ],
                    'news' => [
                        'label' => 'Tin tức',
                        'label_en' => 'News',
                        'label_ja' => 'ニュース',
                        'active' => '1',
                        'path' => '/',
                        'child' => [
                            'news.manage.post' => [
                                'path' => 'news/manage/post',
                                'label' => 'Quản lý bài đăng',
                                'label_en' => 'Post Management',
                                'label_ja' => '投稿管理',
                                'active' => '1',
                                'action_code' => 'news.manage.post'
                            ],
                            'news.manage.category' => [
                                'path' => 'news/manage/category',
                                'label' => 'Quản lý danh mục',
                                'label_en' => 'Category Management',
                                'label_ja' => 'カテゴリー管理',
                                'active' => '1',
                                'action_code' => 'news.manage.category'
                            ],
                            'news.manage.send.email' => [
                                'path' => 'news/manage/send/email',
                                'label' => 'Thông báo bản tin',
                                'label_en' => 'Newsletter Notification',
                                'label_ja' => 'メールを送る',
                                'active' => '1',
                                'action_code' => 'news.manage.send.email',
                            ],
                            'news.manage.comments' => [
                                'path' => 'news/manage/comment',
                                'label' => 'Quản lý bình luận',
                                'label_en' => 'Comment Management',
                                'label_ja' => 'コメントを管理する',
                                'active' => '1',
                                'action_code' => 'news.manage.comment',
                            ],
                            'news.manage.opinions' => [
                                'path' => 'news/opinions',
                                'label' => 'Quản lý đóng góp ý kiến',
                                'label_en' => 'Opinion Management',
                                'label_ja' => '意見管理',
                                'active' => '1',
                                'action_code' => 'news.manage.opinion',
                            ],
                            'news.manage.posters' => [
                                'path' => 'news/posters',
                                'label' => 'Quản lý Poster',
                                'label_en' => 'Poster Management',
                                'label_ja' => 'ポスター管理',
                                'active' => '1',
                                'action_code' => 'news.manage.poster',
                            ],
                            'news.manage.featured_article' => [
                                'path' => 'news/manage/featured_article',
                                'label' => 'Quản lý bài viết nổi bật',
                                'label_en' => 'Managing Featured Posts',
                                'label_ja' => '注目の投稿の管理',
                                'active' => '1',
                                'action_code' => 'news.manage.featured_article',
                            ],
                        ],
                    ],
                    'hr.child.magazine' => [
                        'path' => '#',
                        'label' => 'Tạp chí',
                        'label_en' => 'Magazines',
                        'label_ja' => '社内雑誌',
                        'active' => '1',
                        'action_code' => 'magazine.manage',
                        'child' => [
                            'magazine.index' => [
                                'path' => 'magazines',
                                'label' => 'Danh sách tạp chí',
                                'label_en' => 'Magazine List',
                                'label_ja' => '雑誌リスト',
                                'active' => '1',
                                'action_code' => 'magazine.manage'
                            ],
                            'magazine.create' => [
                                'path' => 'magazines/create',
                                'label' => 'Tạo một tạp chí',
                                'label_en' => 'Create a Magazine',
                                'label_ja' => '雑誌を新規作成',
                                'active' => '1',
                                'action_code' => 'magazine.manage'
                            ]
                        ]
                    ],
                ],
            ],
            'event.send.mail.employees' => [
                'path' => '#',
                'label' => 'Gửi mail',
                'label_en' => 'Mail Sending',
                'label_ja' => 'メールを送る',
                'active' => '1',
                'child' => [
                    'event.send.mail.employees.tet.bonuses' => [
                        'path' => 'event/send/email/employees/tet-bonuses',
                        'label' => 'Thưởng tết',
                        'label_en' => 'Tet Bonuses',
                        'label_ja' => 'テトボーナス',
                        'active' => '1',
                        'action_code' => 'event.send.mail.bonuses.tet',
                    ],
                    /* 'hr.update_timesheet' => [
                      'path' => 'event/send/email/employees/timesheet/to/fines',
                      'label' => 'Tổng hợp thời gian đi muộn',
                      'active' => '1',
                      'action_code' => 'event.send.mail.total.timekeeping'
                      ], */
                    'event.send.sabbatical.mail' => [
                        'path' => 'event/mail-sabbatical-days',
                        'label' => 'Ngày nghỉ phép',
                        'label_en' => 'Paid Leaves',
                        'label_ja' => '有給休暇',
                        'active' => '1',
                        'action_code' => 'event.send.sabbatical.mail'
                    ],
                    /* 'event.send.to.male.mail' => [
                      'path' => 'event/send/email/employees/to-male',
                      'label' => 'Email to male',
                      'active' => '0',
                      'action_code' => 'event.send.mail.to.male'
                      ], */
                    'event.send.mail.total.timekeeping' => [
                        'path' => 'event/send/email/employees/total-timekeeping',
                        'label' => 'Tổng hợp chấm công',
                        'label_en' => 'Detailed Working Timesheet',
                        'label_ja' => 'タイムシート',
                        'active' => '1',
                        'action_code' => 'event.send.mail.total.timekeeping'
                    ],
                    'event.send.mail.tax' => [
                        'path' => 'event/send/email/employees/tax',
                        'label' => 'Thông tin thuế',
                        'label_en' => 'Tax Information',
                        'label_ja' => '課税に関する情報',
                        'active' => '1',
                        'action_code' => 'event.send.mail.tax'
                    ],
                    'event.send.mail.fines' => [
                        'path' => 'event/send/email/employees/fines',
                        'label' => 'Phạt nội quy',
                        'label_en' => 'Disciplinary Misconduct Fines',
                        'label_ja' => '規則違反罰金',
                        'active' => '1',
                        'action_code' => 'event.send.mail.fines'
                    ],
                    'email.send.notification' => [
                        'path' => 'email/notification',
                        'label' => 'Gửi thông báo',
                        'label_en' => 'Send notification',
                        'label_ja' => '通知を 発 信 する',
                        'action_code' => 'event.send.notification',
                    ],
                    'email.send.salary' => [
                        'path' => 'event/send/email/employees/salary',
                        'label' => 'Thông tin lương',
                        'label_en' => 'Detailed Salary',
                        'label_ja' => '給料明細',
                        'active' => '1',
                        'action_code' => 'event.send.mail.salary'
                    ],
                    'event.send.any' => [
                        'path' => 'event/send/email/employees/compose',
                        'label' => 'Tạo một email',
                        'label_en' => 'Create an email',
                        'label_ja' => 'メールを作成する',
                        'active' => '0',
                        'action_code' => 'event.send.mail.compose'
                    ],
                    'event.eventday.company' => [
                        'path' => '#',
                        'label' => 'Gửi mail sự kiện',
                        'label_en' => 'Send an event email',
                        'label_ja' => 'Send an event email',
                        'active' => '1',
                        'child' => [
                            'event.eventday.company.create' => [
                                'path' => 'event/eventday/company',
                                'label' => 'Gửi mail',
                                'label_en' => 'Mail Sending',
                                'label_ja' => 'メールを送る',
                                'active' => '1',
                                'action_code' => 'event.eventday.company',
                            ],
                            'event.eventday.company.list' => [
                                'path' => 'event/eventday/company/list',
                                'label' => 'Danh sách khách hàng',
                                'label_en' => 'Customer List',
                                'label_ja' => 'お客様一覧',
                                'active' => '1',
                                'action_code' => 'event.eventday.company',
                            ],
                        ],
                    ],
                    'event.event_birthday.company' => [
                        'path' => '#',
                        'label' => 'Gửi mail sự kiện sinh nhật',
                        'label_en' => 'Send birthday email',
                        'label_ja' => '誕生日のメールを送信する',
                        'active' => '1',
                        'child' => [
                            'event.event_birthday.company.create' => [
                                'path' => 'event/brithday/company',
                                'label' => 'Gửi mail',
                                'label_en' => 'Mail Sending',
                                'label_ja' => 'メールを送る',
                                'active' => '1',
                                'action_code' => 'event.event_birthday.company',
                            ],
                            'event.event_birthday.company.list' => [
                                'path' => 'event/brithday/company/list',
                                'label' => 'Danh sách khách hàng',
                                'label_en' => 'Customer List',
                                'label_ja' => 'お客様一覧',
                                'active' => '1',
                                'action_code' => 'event.event_birthday.company',
                            ],
                            'event.event_birthday.company.mail_cust_list' => [
                                'path' => 'event/brithday/company/cust/list',
                                'label' => 'Danh sách gửi mail',
                                'label_en' => 'Mailing list',
                                'label_ja' => 'メーリングリスト',
                                'active' => '1',
                                'action_code' => 'event.event_birthday.company',
                            ],
                        ],
                    ],
                    'se.config' => [
                        'path' => '#',
                        'label' => 'Cấu hình email',
                        'label_en' => 'Email Configuration',
                        'label_ja' => 'メール設定',
                        'active' => '1',
                        'child' => [
                            'admin.mail.birthday.employee' => [
                                'path' => 'event/mail/birth/employee',
                                'label' => 'Thư chúc mừng sinh nhật',
                                'label_en' => 'Birthday Congratulations Email',
                                'label_ja' => '従業員の誕生日の際にお祝いのメールを送る',
                                'active' => '1',
                                'action_code' => 'admin.mail.birthday.employee',
                            ],
                            'admin.mail.membership.employee' => [
                                'path' => 'event/mail/membership/employee',
                                'label' => 'Thư chào mừng nhân viên mới',
                                'label_en' => 'Welcome Letter to New Employee',
                                'label_ja' => 'メンバシップのメールを送る',
                                'active' => '1',
                                'action_code' => 'admin.mail.membership.employee',
                            ],
                        ],
                    ],
                    'it.send.mail.off' => [
                        'path' => 'mail-off/upload',
                        'label' => 'Xác nhận xóa email',
                        'label_en' => 'Confirm delete email',
                        'label_ja' => 'Confirm delete email',
                        'active' => '1',
                        'action_code' => 'it.sendmail.off'
                    ],
                    'event.send.mail.forgot-turn-off' => [
                        'path' => 'event/send/email/employees/forgot-turn-off',
                        'label' => 'Gửi mail nhắc nhở quên tắt máy',
                        'active' => '1',
                        'action_code' => 'event.send.mail.forgot-turn-off'
                    ],
                ],
            ],
            'Help' => [
                'path' => 'help/create',
                'label' => 'Quản lý trợ giúp',
                'label_en' => 'Manage Help',
                'label_ja' => 'Manage Help',
                'active' => '1',
                'action_code' => 'manage.help.create',
            ],
            'release' => [
                'path' => 'notes/manage/index',
                'label' => 'Release notes',
                'label_en' => 'Release notes',
                'label_ja' => 'Release notes',
                'active' => '1',
                'action_code' => 'release.notes.manage'
            ],
            'quan.ly.cham.cong' => [
                'path' => '#',
                'label' => 'Quản lý chấm công',
                'label_en' => 'Management Timekeeping',
                'label_ja' => '勤怠管理',
                'active' => '1',
                'child' => [
                    'timekeeping' => [
                        'path' => '#',
                        'label' => 'Bảng chấm công',
                        'label_en' => 'Timesheets',
                        'label_ja' => '勤怠表',
                        'active' => '1',
                        'action_code' => 'manage.timekeeping',
                        'child' => [
                            'timekeeping.table' => [
                                'path' => 'timekeeping/manage-timekeeping-table',
                                'label' => 'Danh sách',
                                'label_en' => 'Timesheets list',
                                'label_ja' => '勤怠表リスト',
                                'active' => '1',
                            ],
                            'timekeeping.detail' => [
                                'path' => 'timekeeping/timekeeping-detail',
                                'label' => 'Chi tiết',
                                'label_en' => 'Timesheets details',
                                'label_ja' => '詳細な勤怠表',
                                'active' => '1',
                            ],
                            'timekeeping.aggregate' => [
                                'path' => 'timekeeping/timekeeping-aggregate',
                                'label' => 'Tổng hợp',
                                'label_en' => 'Summary of timesheets',
                                'label_ja' => 'Summary of timesheets',
                                'active' => '1',
                            ],
                        ]
                    ],
                    'timekeeping.view' => [
                        'path' => '#',
                        'label' => 'Xem chấm công',
                        'label_en' => 'View timesheets',
                        'label_ja' => 'View timesheets',
                        'active' => '1',
                        'action_code' => 'manage.timekeeping.view',
                        'child' => [
                            'timekeeping.table' => [
                                'path' => 'timekeeping/manage-timekeeping-table',
                                'label' => 'Danh sách',
                                'label_en' => 'Timesheets list',
                                'label_ja' => 'Timesheets list',
                                'active' => '1',
                            ],
                            'timekeeping.detail' => [
                                'path' => 'timekeeping/timekeeping-detail',
                                'label' => 'Chi tiết',
                                'label_en' => 'Timesheets details',
                                'label_ja' => 'Timesheets details',
                                'active' => '1',
                            ],
                            'timekeeping.aggregate' => [
                                'path' => 'timekeeping/timekeeping-aggregate',
                                'label' => 'Tổng hợp',
                                'label_en' => 'Summary of timesheets',
                                'label_ja' => 'Summary of timesheets',
                                'active' => '1',
                            ],
                        ]
                    ],
                    'manage.time' => [
                        'path' => '#',
                        'label' => 'Quản lý thời gian',
                        'label_en' => 'Time management',
                        'label_ja' => '時間管理',
                        'active' => '1',
                        'action_code' => 'manage.view',
                        'child' => [
                            'manage.leave' => [
                                'path' => 'timekeeping/manage/leave-day',
                                'label' => 'Đơn nghỉ phép',
                                'label_en' => 'List of leave',
                                'label_ja' => '休暇申請リスト',
                                'active' => '1',
                            ],
                            'manage.supplement' => [
                                'path' => 'timekeeping/manage/supplement',
                                'label' => 'Bổ sung công',
                                'label_en' => 'List of additional timekeeping',
                                'label_ja' => 'List of additional timekeeping',
                                'active' => '1',
                            ],
                            'manage.ot' => [
                                'path' => 'timekeeping/manage/ot',
                                'label' => 'Làm thêm giờ',
                                'label_en' => 'List of OT registrations',
                                'label_ja' => '残業申申請リスト',
                                'active' => '1',
                            ],
                            'manage.comelate' => [
                                'path' => 'timekeeping/manage/late-in-early-out',
                                'label' => 'Đi muộn về sớm',
                                'label_en' => 'List of late arrival and early checkout',
                                'label_ja' => '遅刻早退申請リスト',
                                'active' => '1',
                            ],
                            'manage.mission' => [
                                'path' => 'timekeeping/manage/business-trip',
                                'label' => 'Công tác',
                                'label_en' => 'business trip list',
                                'label_ja' => '出張リスト',
                                'active' => '1',
                            ],
                        ],
                    ],
                    'manage.leave' => [
                        'path' => 'admin/manage-day-of-leave',
                        'label' => 'Quản lý ngày phép',
                        'label_en' => 'Manage permission days',
                        'label_ja' => '有給休暇管理',
                        'active' => '1',
                        'action_code' => 'manage.leave',
                    ],
                    'manage.reason.leave' => [
                        'path' => 'admin/manage-reason-leave',
                        'label' => 'Lý do nghỉ phép',
                        'label_en' => 'Reason for leaving',
                        'label_ja' => '休暇申請の事由',
                        'active' => '1',
                        'action_code' => 'manage.reason.leave',
                    ],
                    'manage.working_time' => [
                        'path' => 'working-times/manage',
                        'label' => 'Thời gian làm việc',
                        'label_en' => 'Working Hours',
                        'label_ja' => '勤務時間',
                        'active' => '1',
                        'action_code' => 'manage.working_times',
                        'child' => [
                            'manage.working_time.list' => [
                                'path' => 'working-times/manage',
                                'label' => 'Danh sách đăng ký',
                                'label_en' => 'List of subscriptions',
                                'label_ja' => 'List of subscriptions',
                                'active' => '1',
                            ],
                            'manage.working_time.list.log_times' => [
                                'path' => 'working-times/manage/log-times',
                                'label' => 'Danh sách giờ vào/ra',
                                'label_en' => 'List check-in/Check-out time',
                                'label_ja' => 'List check-in/Check-out time',
                                'active' => '1',
                            ]
                        ]
                    ],
                    'manage.staff-are-late' => [
                        'path' => 'admin/staff-are-late',
                        'label' => 'Quản lý nhân viên không đi muộn',
                        'label_en' => 'staff manager are not late',
                        'label_ja' => '勤務時間',
                        'active' => '1',
                        'action_code' => 'list.staff-are-late',
                    ],
                    'timekeeping-management' => [
                        'path' => 'admin/timekeeping-management/setting',
                        'label' => 'Cài đặt',
                        'label_en' => 'Setting',
                        'label_ja' => '設定',
                        'active' => '1',
                        'action_code' => 'setting.timekeeping-management',
                    ],
                ],
            ],
            'hr.manage.asset' => [
                'path' => '',
                'label' => 'Quản lý tài sản',
                'label_en' => 'Asset Management',
                'label_ja' => '財産管理',
                'active' => '1',
                'child' => [
                    'hr.manage.asset.supplier' => [
                        'path' => 'admin/manage/asset/supplier',
                        'label' => 'Nhà cung cấp',
                        'label_en' => 'Asset Provider',
                        'label_ja' => '資産提供者',
                        'active' => '1',
                        'action_code' => 'management.asset.view.list'
                    ],
                    'hr.manage.asset.origin' => [
                        'path' => 'admin/manage/asset/origin',
                        'label' => 'Nguồn gốc',
                        'label_en' => 'Asset Origin',
                        'label_ja' => '資産の起源',
                        'active' => '1',
                        'action_code' => 'management.asset.view.list'
                    ],
                    'hr.manage.asset.group' => [
                        'path' => 'admin/manage/asset/group',
                        'label' => 'Nhóm tài sản',
                        'label_en' => 'Asset group',
                        'label_ja' => '資産グループ',
                        'active' => '1',
                        'action_code' => 'management.asset.view.list'
                    ],
                    'hr.manage.asset.category' => [
                        'path' => 'admin/manage/asset/category',
                        'label' => 'Loại tài sản',
                        'label_en' => 'Asset Type',
                        'label_ja' => '資産タイプ',
                        'active' => '1',
                        'action_code' => 'management.asset.view.list'
                    ],
                    'hr.manage.asset.attribute' => [
                        'path' => 'admin/manage/asset/attribute',
                        'label' => 'Thuộc tính tài sản',
                        'label_en' => 'Asset attribute',
                        'label_ja' => '資産属性',
                        'active' => '1',
                        'action_code' => 'management.asset.view.list'
                    ],
                    'hr.manage.asset.list' => [
                        'path' => 'admin/manage/asset',
                        'label' => 'Danh sách tài sản',
                        'label_en' => 'Asset list',
                        'label_ja' => '財産一覧',
                        'active' => '1',
                        'action_code' => 'management.asset.view.list'
                    ],
                    'hr.manage.asset.warehouse' => [
                        'path' => 'admin/manage/asset/warehouse',
                        'label' => 'Kho tài sản',
                        'label_en' => 'Property warehouse',
                        'label_ja' => '倉庫',
                        'active' => '1',
                        'action_code' => 'management.asset.view.list'
                    ],
                    'resource.request.asset' => [
                        'path' => 'admin/resource/request-asset',
                        'label' => 'Yêu cầu tài sản',
                        'label_en' => 'Asset claim',
                        'label_ja' => '資産請求',
                        'active' => '1',
                        'action_code' => 'request.asset.view.list'
                    ],
                    'asset.manage.inventory' => [
                        'label' => 'Kiểm kê tài sản',
                        'label_en' => 'Stock-taking',
                        'label_ja' => '財産を棚卸',
                        'path' => 'admin/asset/manage/inventory',
                        'active' => '1',
                        'action_code' => 'management.asset.inventory'
                    ],
                    'asset.manage.report' => [
                        'label' => 'Yêu cầu của NV',
                        'label_en' => 'Employee\'s request',
                        'label_ja' => '財産のハンドオーバー、遺失報告、故障報告一覧',
                        'path' => 'admin/asset/manage/report',
                        'active' => '1',
                        'action_code' => 'management.asset.confirm'
                    ],
                    'asset.manage.setting' => [
                        'label' => 'Cài đặt tài sản',
                        'label_en' => 'Asset setting',
                        'label_ja' => 'Asset setting',
                        'path' => 'admin/setting-asset',
                        'active' => '1',
                        'action_code' => 'mamagement.asset.setting',
                    ],
                    'asset.allocation.warehouse_for_it' => [
                        'label' => 'Kho cấp phát tài sản',
                        'label_en' => 'Asset allocation warehouse',
                        'label_ja' => 'Asset allocation warehouse',
                        'path' => 'admin/manage/asset/request-asset-to-warehouse',
                        'active' => '1',
                        'action_code' => 'management.asset.allocation_warehouse_for_it',
                    ],
                ],
            ],
            'welfare' => [
                'path' => 'welfare',
                'label' => 'Quản lý phúc lợi',
                'label_en' => 'Welfare management',
                'label_ja' => 'Welfare management',
                'active' => '1',
                'action_code' => 'viewWelfare',
                'child' => [
                    'welfare.list' => [
                        'path' => 'welfare',
                        'label' => 'Danh sách phúc lợi',
                        'label_en' => 'employee benefit programs',
                        'label_ja' => '福祉プログラム一覧',
                        'active' => '1',
                        'action_code' => 'viewWelfare',
                    ],
                    'realtion' => [
                        'path' => 'relation',
                        'label' => 'Quản lý mối quan hệ',
                        'label_en' => 'Relationship manager',
                        'label_ja' => 'Relationship manager',
                        'active' => '1',
                        'action_code' => '',
                        'child' => [
                            'relation.list' => [
                                'path' => 'welfare/relation/list',
                                'label' => 'Danh sách mối quan hệ',
                                'label_en' => 'Relationship list',
                                'label_ja' => 'Relationship list',
                                'active' => '1',
                                'action_code' => 'viewRelationName',
                            ],
                            'relation.create' => [
                                'path' => 'welfare/relation/create',
                                'label' => 'Thêm mối quan hệ',
                                'label_en' => 'Create a new relationship',
                                'label_ja' => 'Create a new relationship',
                                'active' => '1',
                                'action_code' => 'editRelationName',
                            ],
                        ],
                    ],
                ]
            ],
            'report' => [
                'path' => '',
                'label' => 'Báo cáo',
                'label_en' => 'Report',
                'label_ja' => '報告書',
                'active' => '1',
                'child' => [
                    'onsite' => [
                        'path' => 'timekeeping/manage/report',
                        'label' => 'Thống kê onsite',
                        'label_en' => 'Onsite statistics',
                        'label_ja' => 'Onsite statistics',
                        'active' => '1',
                        'action_code' => 'admin.report.onsite',
                    ],
                    'business.approved' => [
                        'path' => 'timekeeping/manage/report-business-trip',
                        'label' => 'Đơn công tác',
                        'label_en' => 'Report business trip',
                        'label_ja' => 'Report business trip',
                        'active' => '1',
                        'action_code' => 'admin.report.business.trip',
                    ],
                    'team.report.certificates' => [
                        'path' => 'team/report/certificates',
                        'label' => 'Chứng chỉ',
                        'label_en' => 'Certificates',
                        'label_ja' => 'Certificates',
                        'active' => '1',
                        'action_code' => 'admin.report.certificates',
                    ],
                    'project.timekeeping.systena' => [
                        'path' => 'timekeeping/manage/report-project-timekeeping-systena',
                        'label' => 'Theo dõi công Systena',
                        'label_en' => 'Report project timekeeping systena',
                        'label_ja' => 'Report project timekeeping systena',
                        'active' => '1',
                        'action_code' => 'admin.report.project.timekeeping.systena',
                    ],
                    'report.ot' => [
                        'path' => 'ot/manage/report-ot',
                        'label' => 'Đơn OT',
                        'label_en' => 'Report OT',
                        'label_ja' => 'Report OT',
                        'active' => '1',
                        'action_code' => 'admin.report.ot',
                    ],
                ]
            ],
            'finesmoney' => [
                'path' => '',
                'label' => 'Quản lý phạt nội quy',
                'label_en' => 'Management rules',
                'label_ja' => 'Management rules',
                'active' => '1',
                'child' => [
                    'list' => [
                        'path' => 'fines-money/manage/list/all',
                        'label' => 'Danh sách',
                        'label_en' => 'List',
                        'label_ja' => 'リスト',
                        'active' => '1',
                        'action_code' => 'view.list.finesmoney',
                    ],
                    'history' => [
                        'path' => 'fines-money/manage/history',
                        'label' => 'Lịch sử',
                        'label_en' => 'History',
                        'label_ja' => '歴史',
                        'active' => '1',
                        'action_code' => 'view.list.finesmoney',
                    ]
                ]
            ],
            'setting_admin' => [
                'path' => '#',
                'label' => 'Cài đặt',
                'label_en' => 'Setting',
                'label_ja' => '設定',
                'active' => '1',
                'child' => [
                    'setting_admin.list' => [
                        'path' => 'setting/list-admin',
                        'label' => 'List admin',
                        'label_en' => 'List admin',
                        'label_ja' => 'List admin',
                        'active' => '1',
                        'action_code' => 'admin.setting.list-admin',
                    ],
                ]
            ],
            'manage.work.place.management' => [
                'path' => 'profile/work-place-management',
                'label' => 'Quản lý địa điểm làm việc',
                'label_en' => 'Work place management',
                'label_ja' => '職場管理',
                'active' => '1',
                'action_code' => 'work.place.management',
            ],
        ],
    ],
    'document' => [
        'path' => 'document',
        'label' => 'Tài liệu',
        'label_en' => 'Documents',
        'label_ja' => '書類',
        'active' => '1',
        'child' => [
            'document.view.list' => [
                'path' => 'document',
                'label' => 'Danh sách tài liệu',
                'label_en' => 'Document list',
                'label_ja' => '書類リスト',
                'active' => '1'
            ],
            'document.manage' => [
                'path' => 'document/manage',
                'label' => 'Quản lý tài liệu',
                'label_en' => 'Document Management',
                'label_ja' => '書類管理',
                'active' => '1',
                'action_code' => 'doc.manage'
            ],
            'document.create' => [
                'path' => 'document/manage/edit',
                'label' => 'Thêm tài liệu',
                'label_en' => 'Create a document',
                'label_ja' => '書類を作成する',
                'active' => '1',
                'action_code' => 'doc.manage'
            ],
            'document.request.list' => [
                'path' => 'document/manage/request/index',
                'label' => 'Yêu cầu tài liệu',
                'label_en' => 'Request a document',
                'label_ja' => '資料請求',
                'active' => '1',
                'action_code' => 'doc.request.manage'
            ],
            'document.type.list' => [
                'path' => 'document/manage/types',
                'label' => 'Các loại tài liệu',
                'label_en' => 'Document types',
                'label_ja' => ' 文書の 種類',
                'active' => '1',
                'action_code' => 'doc.type.manage'
            ],
        ]
    ],
    'mobile' => [
        'path' => '#',
        'label' => 'Mobile',
        'label_en' => 'Mobile',
        'label_ja' => 'モバイル',
        'active' => '1',
        'child' => [
            'home_message'=>[
                'path'=>'home_message',
                'label'=>'Quản lý lời nhắn và banner',
                'label_en'=>'Quản lý lời nhắn và banner',
                'label_ja'=>'Quản lý lời nhắn và banner',
                'active'=> '1',
                'action_code'=>'admin.home-message'
            ],
            'admin.notify'=>[
                'path'=>'notify/list',
                'label'=>'Quản lý thông báo',
                'label_en'=>'Quản lý thông báo',
                'label_ja'=>'Quản lý thông báo',
                'active'=> '1',
                'action_code'=>'admin.notify'
            ],
            'manage.proposed' => [
                'path' => 'proposed/manage-proposed',
                'label' => 'Quản lý ý kiến xây dựng Rikkei',
                'label_en' => 'Quản lý ý kiến xây dựng Rikkei',
                'label_ja' => 'Quản lý ý kiến xây dựng Rikkei',
                'active' => '1',
                'action_code' => 'manage.proposed',
            ],
            'rank_point'=>[
                'path'=>'http://mobile.rikkei.vn/shake-rule',
                'label'=>'Quản lý sự kiện, quà tặng và R-point',
                'label_en'=>'Quản lý sự kiện, quà tặng và R-point',
                'label_ja'=>'Quản lý sự kiện, quà tặng và R-point',
                'active'=>1,
                'action_code'=>'admin.rank_point'
            ],
            'confession'=>[
                'path'=>'http://mobile.rikkei.vn/confession?approved_flg=0',
                'label'=>'Quản lý confession',
                'label_en'=>'Quản lý confession',
                'label_ja'=>'Quản lý confession',
                'active'=>1,
                'action_code'=>'admin.confession'
            ],
            'market'=>[
                'path'=>'http://mobile.rikkei.vn/market/product',
                'label'=>'Quản lý chợ',
                'label_en'=>'Quản lý chợ',
                'label_ja'=>'Quản lý chợ',
                'active'=>1,
                'action_code'=>'admin.market'
            ],
            'game'=>[
                'path'=>'http://mobile.rikkei.vn/game',
                'label'=>'Quản lý trò chơi',
                'label_en'=>'Quản lý trò chơi',
                'label_ja'=>'Quản lý trò chơi',
                'active'=>1,
                'action_code'=>'admin.game'
            ],
            'statistics'=>[
                'path'=>'http://mobile.rikkei.vn/statistic/markets',
                'label'=>'Số liệu thống kê',
                'label_en'=>'Statistics',
                'label_ja'=>'Statistics',
                'active'=>1,
                'action_code'=>'admin.statistics'
            ],
            'donates'=>[
                'path'=>'http://mobile.rikkei.vn/donates',
                'label'=>'Quản lý chương trình từ thiện',
                'label_en'=>'Donation charity management',
                'label_ja'=>'Statistics',
                'active'=>1,
                'action_code'=>'admin.donates'
            ],
            'mobile.config'=>[
                'path'=>'mobile/config',
                'label'=>'Quản lý cấu hình Admin Rikkeisoft',
                'label_en'=>'Config management',
                'label_ja'=>'Config management',
                'active'=>1,
                'action_code'=>'mobile.config'
            ],
            'app-version'=>[
                'path'=>'http://mobile.rikkei.vn/app-version',
                'label'=>'Quản lý phiên bản ứng dụng',
                'label_en'=>'Quản lý phiên bản ứng dụng',
                'label_ja'=>'Quản lý phiên bản ứng dụng',
                'active'=>1,
                'action_code'=>'admin.app-version'
            ],
        ],
    ],
    'HRM' => [
        'path' => '#',
        'label' => 'HRM',
        'label_en' => 'HRM',
        'label_ja' => 'HRM',
        'active' => '1',
        'child' => [
            'profile'=>[
                'path' => config('services.hrm_profile_url'),
                'label' => 'Hồ sơ cá nhân',
                'label_en' => 'Profile',
                'label_ja' => 'Profile',
                'active' => '1',
            ],
            'hrm'=>[
                'path'=> '#',
                'label'=>'Học tập, đào tạo',
                'label_en'=>'Training Manage',
                'label_ja'=>'Training Manage',
                'active'=> '1',
                'action_code'=>'',
                'child' => [
                    'learning' => [
                        'path' => 'https://hrm.rikkei.vn/hrm/learning/g-point/my-g-point',
                        'label' => 'My G-Point',
                        'label_en' => 'My G-Point',
                        'label_ja' => 'My G-Point',
                        'active' => '1',
                        'action_code' => '',
                    ],
                    'profile' => [
                        'path' => 'https://hrm.rikkei.vn/hrm/profile/basic-info',
                        'label' => 'Thêm chứng chỉ',
                        'label_en' => 'Create certificate',
                        'label_ja' => 'Create certificate',
                        'active' => '1',
                        'action_code' => '',
                    ],
                ],
            ],
        ],
    ],
    'link' => [
        'path' => '#',
        'label' => 'Liên kết',
        'label_en' => 'Link',
        'label_ja' => 'リンク',
        'active' => '1',
        'child' => [
            'recruiment' => [
                'path' => config('services.webvn'),
                'label' => 'Tuyển dụng',
                'label_en' => 'Recruitment',
                'label_ja' => '採用',
                'active' => '1'
            ],
            'training' => [
                'path' => config('services.training'),
                'label' => 'Đào tạo',
                'label_en' => 'Training',
                'label_ja' => 'Training',
                'active' => '1'
            ],
            'core-values' => [
                'path' => config('services.core_values_url'),
                'label' => 'Giá trị cốt lõi',
                'label_en' => 'Core values',
                'label_ja' => 'Core values',
                'active' => '1'
            ],
            'mentorship' => [
                'path' => config('services.mentorship_url'),
                'label' => 'Rikkei Mentorship',
                'label_en' => 'Rikkei Mentorship',
                'label_ja' => 'Rikkei Mentorship',
                'active' => '1',
            ],
            'rikkei-10years' => [
                'path' => config('services.rikkei_10years_url'),
                'label' => 'Rikkeisoft 10 năm cùng nhau',
                'label_en' => 'Rikkeisoft 10 years together',
                'label_ja' => 'Rikkeisoft 10 years together',
                'active' => '1',
            ],
            'road-to-japan' => [
                'path' => config('services.roadtojapan_url'),
                'label' => 'Road to Japan',
                'label_en' => 'Road to Japan',
                'label_ja' => 'Road to Japan',
                'active' => '1',
            ],
        ]
    ],
    'ip_portal' => [
        'path' => config('services.ip_portal_url'),
        'label' => 'IP Portal',
        'label_en' => 'IP Portal',
        'label_ja' => 'IPポータル',
        'active' => '1',
    ],
    /** menu setting */
    'setting' => [
        'path' => '#',
        'label' => 'Cài đặt',
        'label_en' => 'Setting',
        'label_ja' => '設定',
        'active' => '1',
        'child' => [
            'team' => [
                'path' => 'setting/team',
                'label' => 'Team & roles',
                'label_en' => 'Team & roles',
                'label_ja' => 'テームと各メンバーの役割',
                'active' => '1',
                'action_code' => 'edit.setting.team',
            ],
            'menu.item' => [
                'path' => '#',
                'label' => 'Menu',
                'label_en' => 'Menu',
                'label_ja' => 'メニュー',
                'active' => '1',
                'action_code' => 'edit.setting.menu',
                'child' => [
                    'setting.menu.item.list' => [
                        'path' => 'setting/menu/item',
                        'label' => 'Danh sách',
                        'label_en' => 'List',
                        'label_ja' => 'リスト',
                        'active' => '1',
                        'action_code' => 'edit.setting.menu',
                    ],
                    'setting.menu.item.create' => [
                        'path' => 'setting/menu/item/create',
                        'label' => 'Tạo mới',
                        'label_en' => 'Add',
                        'label_ja' => '追加',
                        'active' => '1',
                        'action_code' => 'edit.setting.menu',
                    ],
                    'menu.group' => [
                        'path' => 'setting/menu/group',
                        'label' => 'Nhóm Menu',
                        'label_en' => 'Menu Group',
                        'label_ja' => 'メニューグループ',
                        'active' => '1',
                        'action_code' => 'edit.setting.menu',
                    ],
                    'setting.acl.list' => [
                        'path' => 'setting/acl',
                        'label' => 'Acl',
                        'label_en' => 'Acl',
                        'label_ja' => 'Acl',
                        'active' => '1',
                        'action_code' => 'edit.setting.acl',
                    ],
                ],
            ],
            'system.config' => [
                'path' => 'setting/system/data',
                'label' => 'Dữ liệu hệ thống',
                'label_en' => 'System data',
                'label_ja' => 'システム・データ',
                'active' => '1',
                'action_code' => 'edit.setting.system.data'
            ],
            'db.logs' => [
                'path' => 'setting/system/db-logs',
                'label' => 'DB Logs',
                'label_en' => 'DB Logs',
                'label_ja' => 'DB Logs',
                'active' => '1',
                'action_code' => 'view.db_log',
            ],
            'email-queues' => [
                'path' => 'setting/email-queues',
                'label' => 'Email queues list',
                'label_en' => 'Email queues list',
                'label_ja' => 'Email queues list',
                'active' => '1',
                'action_code' => 'view.email-queues',
            ],
            'api-token-setting' => [
                'path' => 'api-web/setting/api-tokens',
                'label' => 'Api tokens',
                'label_en' => 'Api tokens',
                'label_ja' => 'Api tokens',
                'active' => '1',
                'action_code' => 'edit.setting.api_token',
            ],
        ]
    ],
];
