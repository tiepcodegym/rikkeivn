<?php
return [
    'Profile' => [
        'title' => 'Profile',
        'child' => [
            'skill-sheet' => [
                'title' => 'Khai báo Skillsheet',
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
        ],
    ],
];
