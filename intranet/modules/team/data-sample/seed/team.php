<?php
use Rikkei\Team\View\TeamConst;

return [
    [
        'name' => 'BOD',
        'is_function' => '1',
        'follow_team_id' => '0',
        'code' => TeamConst::CODE_BOD,
        'child' => [
            [
                'name' => 'Rikkei - Hanoi',
                'is_function' => '0',
                'follow_team_id' => '0',
                'code' => TeamConst::CODE_HANOI,
                'child' => [
                    [
                        'name' => 'Resource Assuarance',
                        'is_function' => '0',
                        'follow_team_id' => '0',
                        'code' => TeamConst::CODE_HN_RESOURCE_ASSUARANCE,
                        'child' => [
                            [
                                'name' => 'Training',
                                'is_function' => '1',
                                'follow_team_id' => '0',
                                'code' => TeamConst::CODE_HN_TRAINING,
                            ],
                            [
                                'name' => 'HR',
                                'is_function' => '1',
                                'follow_team_id' => '0',
                                'code' => TeamConst::CODE_HN_HR,
                            ],
                        ]
                    ],
                    [
                        'name' => 'PTPM',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'flag_permission_children' => 1,
                        'code' => TeamConst::CODE_HN_DEV,
                        'child' => [
                            [
                                'name' => 'Production',
                                'is_function' => '1',
                                'code' => TeamConst::CODE_HN_PRODUCTION,
                            ],
                            [
                                'name' => 'D0',
                                'is_function' => '1',
                                'code' => TeamConst::CODE_HN_D0,
                            ],
                            [
                                'name' => 'D1', // Mobile
                                'is_function' => '1',
                                'code' => TeamConst::CODE_HN_D1,
                            ],
                            [
                                'name' => 'D2', // Web
                                'is_function' => '1',
                                'code' => TeamConst::CODE_HN_D2,
                            ],
                            [
                                'name' => 'D3', // Game
                                'is_function' => '1',
                                'code' => TeamConst::CODE_HN_D3,
                            ],
                            [
                                'name' => 'D5', // Finance
                                'is_function' => '1',
                                'code' => TeamConst::CODE_HN_D5,
                            ],
                            [
                                'name' => 'D6',
                                'is_function' => '1',
                                'code' => TeamConst::CODE_HN_D6,
                            ],
                            [
                                'name' => 'GD',
                                'is_function' => '1',
                                'code' => TeamConst::CODE_HN_GD,
                            ],
                            [
                                'name' => 'QA',
                                'is_function' => '1',
                                'code' => TeamConst::CODE_HN_QA,
                            ],
                        ]
                    ], //end PTPM
                    [
                        'name' => 'Systena',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'type' => 1,
                        'code' => TeamConst::CODE_HN_SYSTENA
                    ],
                    [
                        'name' => 'HC - TH',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'code' => TeamConst::CODE_HN_HCTH
                    ],
                    [
                        'name' => 'Sales',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'code' => TeamConst::CODE_HN_SALES
                    ],
                    [
                        'name' => 'PR',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'code' => TeamConst::CODE_HN_PR
                    ],
                    [
                        'name' => 'Hanoi - IT',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'code' => TeamConst::CODE_HN_IT
                    ],
                ]
            ], // end rikkei hanoi
            [
                'name' => 'Rikkei - Danang',
                'is_function' => '0',
                'follow_team_id' => '0',
                'code' => TeamConst::CODE_DANANG,
                'child' => [
                    [
                        'name' => 'Rikkei Đà Nẵng - IT',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'code' => TeamConst::CODE_DN_IT
                    ],
                    [
                        'name' => 'Rikkei Đà Nẵng - HCTH',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'code' => TeamConst::CODE_DN_HCTH
                    ],
                    [
                        'name' => 'Rikkei Đà Nẵng - PTPM',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'code' => TeamConst::CODE_DN_DEV,
                        'child' => [
                            [
                                'name' => 'DN0',
                                'is_function' => '1',
                                'code' => TeamConst::CODE_DN_D0,
                            ],
                            [
                                'name' => 'DN1',
                                'is_function' => '1',
                                'code' => TeamConst::CODE_DN_D1,
                            ],
                            [
                                'name' => 'DN2',
                                'is_function' => '1',
                                'code' => TeamConst::CODE_DN_D2,
                            ],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Rikkei - Jappan',
                'follow_team_id' => '0',
                'is_function' => '0',
                'code' => TeamConst::CODE_JAPAN,
                'child' => [
                    [
                        'name' => 'Rikkei Japan - HCTH',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'code' => TeamConst::CODE_JAPAN_HCTH
                    ],
                    [
                        'name' => 'Rikkei Japan - Sales',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'code' => TeamConst::CODE_JAPAN_SALE
                    ],
                    [
                        'name' => 'Rikkei Japan - PTPM',
                        'is_function' => '1',
                        'follow_team_id' => '0',
                        'code' => TeamConst::CODE_JAPAN_DEV
                    ],
                ]
            ],
            [
                'name' => 'PQA',
                'follow_team_id' => '0',
                'is_function' => '1',
                'code' => TeamConst::CODE_PQA
            ]
        ]
    ],
];
