<?php

namespace Rikkei\HomeMessage\Seeds;

use DB;
use Exception;
use Log;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\HomeMessage\Model\HomeMessage;
use Rikkei\HomeMessage\Model\HomeMessageDay;
use Rikkei\HomeMessage\Model\HomeMessageGroup;
use Rikkei\HomeMessage\Model\HomeMessageReceiver;

class InitHomeMessageSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }
        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            DB::table('home_message_day')->truncate();
            DB::table('home_message_receivers')->truncate();
            DB::table('home_messages')->truncate();
            DB::table('m_home_message_groups')->truncate();
            $dataTmp = [
                [
                    'name_vi' => 'Lời nhắn ưu tiên',
                    'priority' => 1

                ],
                [
                    'name_vi' => 'Chúc mừng sinh nhật',
                    'priority' => 2,
                    'home_messages' => [
                        [
                            'message_vi' => 'Chúc bạn sinh nhật vui vẻ!!!',
                            'icon_url' => '/storage/home-message/finger_print.png',
                            'start_at' => '00:00',
                            'end_at' => '23:59'
                        ],
                        [
                            'message_vi' => 'Tuổi mới thành công bạn nhé!',
                            'icon_url' => '/storage/home-message/finger_print.png',
                            'start_at' => '00:00',
                            'end_at' => '23:59'
                        ],
                        [
                            'message_vi' => 'Wow! Sinh nhật bạn rồi, party thôi!',
                            'icon_url' => '/storage/home-message/finger_print.png',
                            'start_at' => '00:00',
                            'end_at' => '23:59'
                        ],
                        [
                            'message_vi' => 'Happy birthday!',
                            'icon_url' => '/storage/home-message/finger_print.png',
                            'start_at' => '00:00',
                            'end_at' => '23:59'
                        ]
                    ],
                ],
                [
                    'name_vi' => 'Hiển thị vào các ngày lễ trong năm',
                    'priority' => 3,
                    'home_messages' => [
                        [
                            'message_vi' => 'Chúc mừng năm mới!',
                            'icon_url' => '/storage/home-message/firework.png',
                            'start_at' => '00:00',
                            'end_at' => '23:59',
                            'home_message_day' => [
                                'type' => 1,
                                'permanent_day' => '01-01',
                                'is_sun' => 0,
                                'is_mon' => 0,
                                'is_tues' => 0,
                                'is_wed' => 0,
                                'is_thur' => 0,
                                'is_fri' => 0,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Happy new year!',
                            'icon_url' => '/storage/home-message/firework.png',
                            'start_at' => '00:00',
                            'end_at' => '23:59',
                            'home_message_day' => [
                                'type' => 1,
                                'permanent_day' => '01-01',
                                'is_sun' => 0,
                                'is_mon' => 0,
                                'is_tues' => 0,
                                'is_wed' => 0,
                                'is_thur' => 0,
                                'is_fri' => 0,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Giáng sinh vui vẻ!',
                            'icon_url' => '/storage/home-message/tree.png',
                            'start_at' => '00:00',
                            'end_at' => '23:59',
                            'home_message_day' => [
                                'type' => 1,
                                'permanent_day' => '24-12',
                                'is_sun' => 0,
                                'is_mon' => 0,
                                'is_tues' => 0,
                                'is_wed' => 0,
                                'is_thur' => 0,
                                'is_fri' => 0,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Chúc bạn một năm mới an lành, hạnh phúc, thành công!',
                            'icon_url' => '/storage/home-message/firework.png',
                            'start_at' => '00:00',
                            'end_at' => '23:59',
                            'home_message_day' => [
                                'type' => 1,
                                'permanent_day' => '01-01',
                                'is_sun' => 0,
                                'is_mon' => 0,
                                'is_tues' => 0,
                                'is_wed' => 0,
                                'is_thur' => 0,
                                'is_fri' => 0,
                                'is_sar' => 0,
                            ]
                        ],
                    ],

                ],
                [
                    'name_vi' => 'Hiển thị vào thời gian cố định trong ngày',
                    'priority' => 4,
                    'home_messages' => [
                        [
                            'message_vi' => 'Sáng nay bạn đã chấm công chưa?',
                            'icon_url' => '/storage/home-message/checklist.png',
                            'start_at' => '07:50',
                            'end_at' => '08:00',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Chúc anh em Rikkei ngày mới tràn đầy năng lượng.',
                            'icon_url' => '/storage/home-message/happy.png',
                            'start_at' => '00:00',
                            'end_at' => '08:00',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Nhớ ăn sáng trước khi bắt đầu công việc nhé',
                            'icon_url' => '/storage/home-message/burger.png',
                            'start_at' => '07:55',
                            'end_at' => '08:05',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Hôm nay bạn đi làm có bị tắc đường không?',
                            'icon_url' => '/storage/home-message/burger.png',
                            'start_at' => '08:05',
                            'end_at' => '08:15',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Thời điểm hoàn hảo để thưởng thức một tách café.',
                            'icon_url' => '/storage/home-message/coffee.png',
                            'start_at' => '08:05',
                            'end_at' => '09:00',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Ghi chú danh sách công việc phải làm để quản lý công việc hiệu quả hơn',
                            'icon_url' => '/storage/home-message/checklist.png',
                            'start_at' => '08:05',
                            'end_at' => '09:00',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Trưa nay bạn ăn gì thế?',
                            'icon_url' => '/storage/home-message/rice.png',
                            'start_at' => '10:30',
                            'end_at' => '11:30',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Ngủ trưa cải thiện đáng kể sức khỏe và tâm trạng của chúng ta.',
                            'icon_url' => '/storage/home-message/sleepy.png',
                            'start_at' => '12:30',
                            'end_at' => '13:30',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Giấc ngủ trưa 15-20p sẽ giúp chúng ta tỉnh táo cả buổi chiều đấy!',
                            'icon_url' => '/storage/home-message/sleepy.png',
                            'start_at' => '12:30',
                            'end_at' => '13:30',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Đừng quên chấm công lúc về nhé!',
                            'icon_url' => '/storage/home-message/student.png',
                            'start_at' => '17:00',
                            'end_at' => '18:00',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Tình hình dự án thế nào rồi? Hôm nay bạn có phải OT không?',
                            'icon_url' => '/storage/home-message/time-is-money.png',
                            'start_at' => '17:00',
                            'end_at' => '18:00',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Hãy dành 15 phút cuối ngày để review lại công việc bạn nhé!',
                            'icon_url' => '/storage/home-message/checklist.png',
                            'start_at' => '17:00',
                            'end_at' => '18:00',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Chơi thể thao sau giờ làm việc rất tốt cho sức khỏe(Cuối ngày rồi, vận động đi nào)',
                            'icon_url' => '/storage/home-message/dancing.png',
                            'start_at' => '17:00',
                            'end_at' => '18:00',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 0,
                                'is_mon' => 1,
                                'is_tues' => 1,
                                'is_wed' => 1,
                                'is_thur' => 1,
                                'is_fri' => 1,
                                'is_sar' => 0,
                            ]
                        ],
                        [
                            'message_vi' => 'Chúc bạn cuối tuần vui vẻ với người thân!(Cuối tuần rồi, quẩy thôi!!!)',
                            'icon_url' => '/storage/home-message/happy.png',
                            'start_at' => '17:00',
                            'end_at' => '18:00',
                            'home_message_day' => [
                                'type' => 2,
                                'is_sun' => 1,
                                'is_mon' => 0,
                                'is_tues' => 0,
                                'is_wed' => 0,
                                'is_thur' => 0,
                                'is_fri' => 0,
                                'is_sar' => 1,
                            ]
                        ],
                    ],

                ],
                [
                    'name_vi' => 'Random',
                    'priority' => 5,
                    'home_messages' => [
                        [
                            'message_vi' => 'Ngồi lâu quá rồi, đứng dậy đi lại chút thôi.',
                            'icon_url' => '/storage/home-message/pedestrian.png',
                            'start_at' => '13:30',
                            'end_at' => '17:30'
                        ],
                        [
                            'message_vi' => 'Một chút âm nhạc sẽ giúp bạn thư giãn hơn',
                            'icon_url' => '/storage/home-message/musicalnotes.png',
                            'start_at' => '08:00',
                            'end_at' => '23:59'
                        ],
                        [
                            'message_vi' => 'Đừng quên uống nước bạn nhé.(Uống nước cho đep da nè)',
                            'icon_url' => '/storage/home-message/sparkling-water.png',
                            'start_at' => '08:30',
                            'end_at' => '23:59'
                        ],
                        [
                            'message_vi' => 'Rửa mặt với nước lạnh sẽ giảm bức xạ từ máy tính.',
                            'icon_url' => '/storage/home-message/face.png',
                            'start_at' => '08:00',
                            'end_at' => '17:30'
                        ], [
                            'message_vi' => 'Hôm nay bạn cảm thấy thế nào?',
                            'icon_url' => '/storage/home-message/happy.png',
                            'start_at' => '00:00',
                            'end_at' => '17:30'
                        ], [
                            'message_vi' => 'Trà sữa không!!!',
                            'icon_url' => '/storage/home-message/ice-tea.png',
                            'start_at' => '08:00',
                            'end_at' => '17:30'
                        ],

                    ],

                ],
            ];
            foreach ($dataTmp as $group) {
                $hmGModel = new HomeMessageGroup();
                $hmGModel->name_vi = $group['name_vi'];
                $hmGModel->priority = $group['priority'];
                if (!$hmGModel->save()) {
                    throw new Exception('Lỗi tạo nhóm thông báo');
                }
                if (!empty($group['home_messages'])) {
                    foreach ($group['home_messages'] as $home_message) {
                        $hmModel = new HomeMessage();
                        $hmModel->message_vi = $home_message['message_vi'];
                        $hmModel->group_id = $hmGModel->id;
                        $hmModel->start_at = isset($home_message['start_at']) ? $home_message['start_at'] : '';
                        $hmModel->end_at = isset($home_message['end_at']) ? $home_message['end_at'] : '';
                        $hmModel->icon_url = $home_message['icon_url'];
                        $hmModel->created_id = 1;
                        if (!$hmModel->save()) {
                            throw new Exception('Lỗi tạo thông báo');
                        }
                        if (!empty($home_message['home_message_day'])) {
                            $home_message['home_message_day']['home_message_id'] = $hmModel->id;
                            HomeMessageDay::create($home_message['home_message_day']);
                        }
                        $homeMessageRecevier = [
                            'home_message_id' => $hmModel->id,
                            'team_id' => 5
                        ];
                        HomeMessageReceiver::create($homeMessageRecevier);
                    }
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
        }

    }
}
