<?php

return [
    'ME' => [
        'title' => 'ME',
        'active' => '1',
        'content' => view('help::items.ME')->render(),
        'child' => [
            'ME-PM' => [
                'title' => 'Hướng dẫn PM',
                'active' => '1',
                'content' => view('help::items.include.ME-PM')->render(),
            ],
            'ME-GroupLeader' => [
                'title' => 'Hướng dẫn Group Leader',
                'active' => '1',
                'content' => view('help::items.include.ME-GroupLeader')->render(),
            ],
            'ME-Member' => [
                'title' => 'Hướng dẫn Member',
                'active' => '1',
                'content' => view('help::items.include.ME-Member')->render(),
            ],
        ],
    ],
    'Project' => [
        'title' => 'Project',
        'active' => '1',
        'content' => '',
        'child' => [
            'Project-Report' => [
                'title' => 'Project Report',
                'active' => '1',
                'content' => view('help::items.ProjectReport')->render(),
                'child' => [
                    'Report-Summary' => [
                        'title' => 'Summary Point',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectReport-SummaryPoint')->render(),
                    ],
                    'Report-Cost' => [
                        'title' => 'Cost',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectReport-Cost')->render(),
                    ],
                    'Report-Quality' => [
                        'title' => 'Quality',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectReport-Quality')->render(),
                    ],
                    'Report-Timeliness' => [
                        'title' => 'Timeliness',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectReport-Timeliness')->render(),
                    ],
                    'Report-Process' => [
                        'title' => 'Process',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectReport-Process')->render(),
                    ],
                    'Report-CSS' => [
                        'title' => 'CSS',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectReport-CSS')->render(),
                    ],
                    'Report-Legend' => [
                        'title' => 'Ký hiệu',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectReport-Legend')->render(),
                    ],
                ],
            ],
            'Project-Workorder' => [
                'title' => 'Project Workorder',
                'active' => '1',
                'content' => '',
                'child' => [
                    'Workorder-BasicInfo' => [
                        'title' => 'Basic Info',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectWorkorder-BasicInfo')->render(),
                    ],
                    'Workorder-Scope' => [
                        'title' => 'Scope and Objectives',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectWorkorder-Scope')->render(),
                    ],
                    'Workorder-Stages' => [
                        'title' => 'Stages',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectWorkorder-Stages')->render(),
                    ],
                    'Workorder-Deliverable' => [
                        'title' => 'Deliverable',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectWorkorder-Deliverable')->render(),
                    ],
                    'Workorder-TeamAllocation' => [
                        'title' => 'Team Allocation',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectWorkorder-TeamAllocation')->render(),
                    ],
                    'Workorder-Performance' => [
                        'title' => 'Performance',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectWorkorder-Performance')->render(),
                    ],
                    'Workorder-Quality' => [
                        'title' => 'Quality',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectWorkorder-Quality')->render(),
                    ],
                    'Workorder-Qualityplan' => [
                        'title' => 'Quality plan',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectWorkorder-Qualityplan')->render(),
                    ],
                    'Workorder-CM' => [
                        'title' => 'CM plan',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectWorkorder-CM')->render(),
                    ],
                    'Workorder-Others' => [
                        'title' => 'Others',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectWorkorder-Others')->render(),
                    ],
                    'Workorder-Risk' => [
                        'title' => 'Risk',
                        'active' => '1',
                        'content' => '',
                        'child' => [
                            'Risk-Explain' => [
                                'title' => 'Giải thích trường dữ liệu',
                                'active' => '1',
                                'content' => view('help::items.include.Risk-Explain')->render(),
                            ],
                            'Risk-Details' => [
                                'title' => 'Ví dụ bảng đánh giá',
                                'active' => '1',
                                'content' => view('help::items.include.Risk-Details')->render(),
                            ],
                        ]
                    ],
                    'Workorder-Sonar' => [
                        'title' => 'Sonar',
                        'active' => '1',
                        'content' => view('help::items.include.ProjectWorkorder-Sonar')->render(),
                    ],
                ],
            ],
        ],
    ],
    'Profile' => [
        'title' => 'Profile',
        'active' => '1',
        'content' => view('help::items.include.profile.head')->render(),
        'order' => 10,
        'child' => [
            'khai-bao-tt-ca-nhan' => [
                'title' => 'Khai báo thông tin cá nhân',
                'active' => '1',
                'content' => view('help::items.include.profile.personal')->render(),
                'child' => [
                    'personal-general' => [
                        'title' => 'Thông tin chung',
                        'active' => '1',
                        'content' => view('help::items.include.profile.personal.general')->render(),
                    ],
                    'personal-work' => [
                        'title' => 'Thông tin công việc',
                        'active' => '1',
                        'content' => view('help::items.include.profile.personal.work')->render(),
                    ],
                    'personal-contact' => [
                        'title' => 'Thông tin liên hệ',
                        'active' => '1',
                        'content' => view('help::items.include.profile.personal.contact')->render(),
                    ],
                    'personal-family' => [
                        'title' => 'Thông tin gia đình',
                        'active' => '1',
                        'content' => view('help::items.include.profile.personal.family')->render(),
                    ],
                    'personal-education' => [
                        'title' => 'Quá trình học tập',
                        'active' => '1',
                        'content' => view('help::items.include.profile.personal.education')->render(),
                    ],
                    'personal-certificate' => [
                        'title' => 'Chứng chỉ',
                        'active' => '1',
                        'content' => view('help::items.include.profile.personal.certificate')->render(),
                    ],
                    'personal-attach' => [
                        'title' => 'Scan giấy tờ',
                        'active' => '1',
                        'content' => view('help::items.include.profile.personal.attach')->render(),
                    ],
                    'personal-onsite' => [
                        'title' => 'Mong muốn onsite',
                        'active' => '1',
                        'content' => view('help::items.include.profile.personal.onsite')->render(),
                    ],
                    'personal-other' => [
                        'title' => 'Thông tin khác',
                        'active' => '1',
                        'content' => view('help::items.include.profile.personal.other')->render(),
                    ],
                ],
            ],
            'skill-sheet' => [
                'title' => 'Khai báo Skillsheet',
                'active' => '1',
                'content' => view('help::items.include.profile.skillsheet')->render(),
                'child' => [
                    'ss-summary' => [
                        'title' => 'Tab Summary',
                        'active' => '1',
                        'content' => view('help::items.include.profile.ss.summary')->render(),
                    ],
                    'ss-project' => [
                        'title' => 'Tab Project',
                        'active' => '1',
                        'content' => view('help::items.include.profile.ss.project')->render(),
                    ],
                    'ss-skill' => [
                        'title' => 'Tab Skills',
                        'active' => '1',
                        'content' => view('help::items.include.profile.ss.skill')->render(),
                    ],
                    'ss-action' => [
                        'title' => 'Action',
                        'active' => '1',
                        'content' => view('help::items.include.profile.ss.action')->render(),
                    ],
                ],
            ],
            'profile-hr' => [
                'title' => 'Phần cho HCTH, nhân sự',
                'active' => '1',
                'content' => view('help::items.include.profile.hr')->render(),
                'child' => [
                    'hr-basicinfo' => [
                        'title' => 'Thông tin chung',
                        'active' => '1',
                        'content' => view('help::items.include.profile.hr.basicinfo')->render(),
                    ],
                    'hr-work' => [
                        'title' => 'Thông tin công viẹc',
                        'active' => '1',
                        'content' => view('help::items.include.profile.hr.work')->render(),
                    ],
                ],
            ],
        ],
    ],
    'Admin Acl' => [
        'title' => 'Admin Acl',
        'active' => '1',
        'content' => '',
        'order' => 100,
        'child' => [
            'acl-kl' => [
                'title' => 'Knowledge system',
                'active' => '1',
                'content' => view('help::items.include.acl.kl')->render(),
            ],
            'acl-profile' => [
                'title' => 'Profile',
                'active' => '1',
                'content' => view('help::items.include.acl.profile')->render(),
            ],
        ]
    ],
];
