<?php
return [
    'suffix_email' => '@rikkeisoft.com',
    'effort_change_approve' => 0, // % effort change approved
    'mm' => (int) env('PROJECT_MANMONTH', 21), // number man day = man month
    'css_after_deliver' => (int) env('PROJECT_CSS_AFTER_DELIVER', 30),
    'weekend' => [
        'Sat',
        'Sun'
    ],
    'report_yes' => env('PROJECT_REPORT_YES', 'friday_15:00:00'),
    'check_report_in_week' => env('PROJECT_CHECK_REPORT_IN_WEEK', 'thursday_00:00:00'),

    'workorder_approved' => [
        'critical_dependencies' => false,
        'assumption_constrain' => false,
        'risk' => false,
        'stage_and_milestone' => true,
        'training' => false,
        'external_interface' => false,
        'tool_and_infrastructure' => false,
        'devices_expenses' => false,
        'communication' => false,
        'deliverable' => true,
        'performance' => true,
        'project_member' => true,
        'quality' => true,
    ],
    'redmine_api' => [
        'issue_title_leakage' => env('REDMINE_API_BUG_LEAKAGE', '[Bug Leakage]'),
        'issue_type_rejected' => 'Rejected',
        'issue_task_title_feature' => [
            'Feature',
            'Support',
            'Task',
            'Requirements'
        ],
        'issue_title_defect_reward' => [
            '[Intergration test] Bug',
            '[System test] Bug'
        ],
        'issue_defect_reward_flag' => [
            'intergration test',
            'system test',
            'it bug',
            'st bug',
        ],
        'issue_leakage_flag' => [
            'bug leakage',
        ],
        'issue_title_bug_flag' => [
            'bug',
            //'comment'
        ], // title include this work =>  is bug
        'issue_cf_incurred_flag' => [
            'incurred',
        ],
        'issue_status_rejected' => [
            'rejected'
        ],
        'issue_status_closed' => [
            'closed'
        ],
    ],
    'me_char_image' => '/project/images/ME.png',
    'reward' => [
        'evaluation_unit' => [
            'hanoi' => [
                0 => 0,
                1 => 1200000,
                2 => 1500000,
                3 => 1800000,
                4 => 2100000,
            ],
            'danang' => [
                0 => 0,
                1 => 1000000,
                2 => 1300000,
                3 => 1500000,
                4 => 1700000,
            ]
        ],
        'unit_reward_leakage_actual' => 200000,
        'unit_reward_leakage_qa' => 150000,
        'unit_reward_defect' => 50000,
        'unit_reward_defect_pqa' => 50000,
        'factor_reward_pm' => 4,
        'factor_reward_dev' => 1,
        'factor_reward_brse' => 1,
    ],
    'reward_disable' => [
        'team_code' => 'danang',
        'project_start_at' => '2017-04-15',
        'ids' => [
            5,132,133
        ]
    ],
    'reward_long' => [
        'apply_date' => '2017-11-01'
    ],
    'me_late_month' => env('ME_LATE_MONTH', '2017-12'),
    'me_sep_month' => env('ME_SEP_MONTH', '2019-12'),
    'me_new2_sep_month' => env('ME_NEW2_SEP_MONTH', '2021-03'),
    'sonar' => [
        'jenkins' => [
            'path_prefix' => 'job/',
            'path_suffix_dev' => '_develop',
            'path_suffix_preview' => '_preview',
            'path_project_prefix' => '/project/',
            'program_to_flag' => [
                '/^php$/' => 'php',
                '/^java$/' => 'java',
                '/android$/' => 'android',
                '/^c#$/' => 'cs',
            ],
            'node_plugin_xml' => [
                'php' => [
                    'plugin' => 'hudson.plugins.sonar.SonarRunnerBuilder',
                    'property' => 'properties',
                ],
                'android' => [
                    'plugin' => 'hudson.plugins.gradle.Gradle',
                    'property' => 'systemProperties',
                ],
                'java' => [
                    'plugin' => 'hudson.tasks.Maven',
                    'property' => 'properties',
                ]
            ],
            'build_properties' => [
                'php_develop' => [
                    'sonar.projectKey' => 'prod:intranet',
                    'sonar.projectName' => 'Intranet',
                    'sonar.projectVersion' => '1',
                ],
                'php_preview' => [
                    'sonar.projectKey' => 'prod:intranet',
                    'sonar.projectName' => 'Intranet',
                    'sonar.projectVersion' => '1',
                    'sonar.analysis.mode' => 'preview',
                    'sonar.gitlab.project_id' => 'production/intranet',
                    'sonar.gitlab.commit_sha' => '${gitlabMergeRequestLastCommit}'
                ],
                'android_develop' => [
                    'sonar.projectKey' => 'production:intranet',
                    'sonar.projectName' => 'Intranet',
                    'sonar.profile' => 'Rikkei Android way'
                ],
                'android_preview' => [
                    'sonar.projectKey' => 'production:intranet',
                    'sonar.projectName' => 'Intranet',
                    'sonar.profile' => 'Rikkei Android way',
                    'sonar.analysis.mode' => 'preview',
                    'sonar.gitlab.project_id' => 'production/intranet',
                    'sonar.gitlab.commit_sha' => '${gitlabMergeRequestLastCommit}'
                ],
                'java_develop' => [
                    'sonar.projectKey' => 'production:intranet',
                    'sonar.projectName' => 'Intranet',
                ],
                'java_preview' => [
                    'sonar.projectKey' => 'production:intranet',
                    'sonar.projectName' => 'Intranet',
                    'sonar.analysis.mode' => 'preview',
                    'sonar.gitlab.project_id' => 'production/intranet',
                    'sonar.gitlab.commit_sha' => '${gitlabMergeRequestLastCommit}'
                ],
            ]
        ],
        'gitlab' => [
            '_develop' => [
                'push_events' => true,
                'enable_ssl_verification' => false
            ],
            '_preview' => [
                'merge_requests_events' => true,
                'note_events' => true,
                'enable_ssl_verification' => false
            ],
        ]
    ],
    'base_url' => env('DESERT_EBS_BASE_URL'),
    'email' => env('AUTH_EMAIL'),
    'password' => env('AUTH_PASSWORD')
];
