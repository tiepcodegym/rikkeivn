<?php

namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\MeAttribute;
use DB;
use Rikkei\Project\Model\MeAttributeLang;

class EvaluationAttribute extends CoreSeeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        if ($this->checkExistsSeed(27)) {
            return;
        }
        $percenPerform = 10;
        $attrs = [
            [
                'id' =>         1,
                'order' =>      0,
                'weight' =>     7.5,
                'range_min' =>  0,
                'range_max' =>  10,
                'range_step' => 1,
                'default' =>    10,
                'group' =>      1,
                'can_fill' =>   0,
                'has_na' =>     0,
                'type' =>       NULL
            ],
            [
                'id' =>         2,
                'order' =>      1,
                'weight' =>     7.5,
                'range_min' =>  0,
                'range_max' =>  10,
                'range_step' => 1,
                'default' =>    0,
                'group' =>      1,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       MeAttribute::TYPE_REGULATIONS
            ],
            [
                'id' =>         3,
                'order' =>      2,
                'weight' =>     7.5,
                'range_min' =>  0,
                'range_max' =>  10,
                'range_step' => 1,
                'default' =>    0,
                'group' =>      1,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       MeAttribute::TYPE_PRO_ACTIVITY
            ],
            [
                'id' =>         4,
                'order' =>      3,
                'weight' =>     7.5,
                'range_min' =>  0,
                'range_max' =>  10,
                'range_step' => 1,
                'default' =>    0,
                'group' =>      1,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       MeAttribute::TYPE_SOCIAL_ACTIVITY
            ],
            [
                'id' =>         5,
                'order' =>      4,
                'weight' =>     $percenPerform,
                'range_min' =>  -1,
                'range_max' =>  5,
                'range_step' => 1,
                'default' =>    -1,
                'group' =>      2,
                'can_fill' =>   1,
                'has_na' =>     1,
                'type' =>       MeAttribute::TYPE_CUSTOMER_FEEDBACK
            ],
            [
                'id' =>         6,
                'order' =>      5,
                'weight' =>     $percenPerform,
                'range_min' =>  -1,
                'range_max' =>  5,
                'range_step' => 1,
                'default' =>    3,
                'group' =>      2,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       MeAttribute::TYPE_WORK_QUALITY
            ],
            [
                'id' =>         7,
                'order' =>      6,
                'weight' =>     $percenPerform,
                'range_min' =>  -1,
                'range_max' =>  5,
                'range_step' => 1,
                'default' =>    3,
                'group' =>      2,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       MeAttribute::TYPE_WORK_PROGRESS
            ],
            [
                'id' =>         8,
                'order' =>      7,
                'weight' =>     $percenPerform,
                'range_min' =>  -1,
                'range_max' =>  5,
                'range_step' => 1,
                'default' =>    3,
                'group' =>      2,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       MeAttribute::TYPE_WORK_PROCESS
            ],
            [
                'id' =>         9,
                'order' =>      8,
                'weight' =>     $percenPerform,
                'range_min' =>  -1,
                'range_max' =>  5,
                'range_step' => 1,
                'default' =>    3,
                'group' =>      2,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       MeAttribute::TYPE_TEAMWORK
            ],
            //new
            [
                'id' =>         10,
                'order' =>      9,
                'weight' =>     40,
                'range_min' =>  0,
                'range_max' =>  10,
                'range_step' => 1,
                'default' =>    0,
                'group' =>      MeAttribute::GR_NEW_PERFORM,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       MeAttribute::TYPE_WORK_PERFORM,
            ],
            [
                'id' =>         11,
                'order' =>      10,
                'weight' =>     20,
                'range_min' =>  0,
                'range_max' =>  10,
                'range_step' => 1,
                'default' =>    0,
                'group' =>      MeAttribute::GR_NEW_PERFORM,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       NULL,
            ],
            [
                'id' =>         12,
                'order' =>      11,
                'weight' =>     15,
                'range_min' =>  0,
                'range_max' =>  10,
                'range_step' => 1,
                'default' =>    0,
                'group' =>      MeAttribute::GR_NEW_PERFORM,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       NULL,
            ],
            [
                'id' =>         13,
                'order' =>      12,
                'weight' =>     15,
                'range_min' =>  0,
                'range_max' =>  10,
                'range_step' => 1,
                'default' =>    0,
                'group' =>      MeAttribute::GR_NEW_NORMAL,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       MeAttribute::TYPE_NEW_PRO_ACTIVITY,
            ],
            [
                'id' =>         14,
                'order' =>      13,
                'weight' =>     10,
                'range_min' =>  0,
                'range_max' =>  10,
                'range_step' => 1,
                'default' =>    0,
                'group' =>      MeAttribute::GR_NEW_NORMAL,
                'can_fill' =>   1,
                'has_na' =>     0,
                'type' =>       MeAttribute::TYPE_NEW_REGULATIONS,
            ]
        ];
        $attrLang = [
            'vi' => [
                [
                    'attr_id' =>        1,
                    'name' =>           'Đảm bảo tuân thủ thời gian làm việc',
                    'label' =>          'Thời gian làm việc',
                    'description' =>    '<p>Tự động lấy từ dữ liệu chấm công của tháng trước. Mỗi lần đi muộn trừ 1 điểm.</p>
                        Tối đa: 10 điểm <br>
                        Tối thiểu: 0 điểm <br>',
                ],
                [
                    'attr_id' =>        2,
                    'name' =>           'Đảm bảo tuân thủ kỷ luật công việc',
                    'label' =>          'Quy định',
                    'description' =>    '<p>Trừ điểm theo các vi phạm: làm việc không tập trung, làm việc riêng trong giờ, vi phạm bảo mật, đi làm muộn quá nhiều... <br>
                        Thông thường: trừ 5 điểm/lần. <br>
                        Nặng: trừ 10 điểm <br>
                        Member không vi phạm: 10 điểm </p>
                        Tối đa: 10 điểm <br>
                        Tối thiểu: 0 điểm <br>',
                ],
                [
                    'attr_id' =>        3,
                    'name' =>           'Đóng góp cho các hoạt động chuyên môn',
                    'label' =>          'Hoạt động chuyên môn',
                    'description' =>    '<p>Tham gia đào tạo, seminar, workshop.... đóng góp cho team và công ty về mặt chuyên môn, ngoại ngữ</p>
                        <ul>
                            <li>8-10 điểm/lần: tham gia với vai trò host seminar/workshop, trainer</li>
                            <li>5: tham gia 1 khóa đào tạo dài hạn</li>
                            <li>3 điểm/lần: tham gia làm member (khán giả) của 1 seminar/workshop</li>
                            <li>0: không tham gia</li>
                        </ul>
                        Tối đa: 10 điểm <br>
                        Tối thiểu: 0 điểm <br>',
                ],
                [
                    'attr_id' =>        4,
                    'name' =>           'Đóng góp cho các hoạt động tinh thần, xã hội',
                    'label' =>          'Hoạt động xã hội',
                    'description' =>    '<p>Tham gia các hoạt động tinh thần, tổng hội, tạp san, ngoại khóa, từ thiện, phong trào xã hội...</p>
                        <ul>
                            <li>8-10 điểm/lần: tham gia với vai trò tổ chức event</li>
                            <li>5-7 điểm/lần: tham gia nhiệt tình với vài trò làm member</li>
                            <li>3-5 điểm/lần: tham gia không nhiệt tình với vài trò làm member</li>
                            <li>1-2 điểm: tham gia thường xuyên các CLB của công ty có hoạt động định kỳ hàng tháng</li>
                        </ul>
                        Tối đa: 10 điểm<br>
                        Tối thiểu: 0 điểm<br>',
                ],
                [
                    'attr_id' =>        5,
                    'name' =>           'Phản hồi của khách hàng về member',
                    'label' =>          'ý kiến khách hàng',
                    'description' =>    'Nếu không có phản hồi, chọn "N/A"',
                ],
                [
                    'attr_id' =>        6,
                    'name' =>           'Đảm bảo chất lượng',
                    'label' =>          'Chất lượng',
                    'description' =>    'PM đánh giá mức độ hoàn thành của member',
                ],
                [
                    'attr_id' =>        7,
                    'name' =>           'Đảm bảo tiến độ công việc',
                    'label' =>          'Tiến độ',
                    'description' =>    'PM đánh giá mức độ hoàn thành của member',
                ],
                [
                    'attr_id' =>        8,
                    'name' =>           'Tuân thủ quy trình',
                    'label' =>          'Qui trình làm việc',
                    'description' =>    'PM đánh giá mức độ hoàn thành của member',
                ],
                [
                    'attr_id' =>        9,
                    'name' =>           'Kỹ năng làm việc nhóm',
                    'label' =>          'Teamwork',
                    'description' =>    'PM đánh giá mức độ hoàn thành của member',
                ],
                //new
                [
                    'attr_id' =>        10,
                    'name' =>           'Hiệu suất công việc (Trên cơ sở chất lượng & thời gian hoàn thành công việc)',
                    'label' =>          'Năng suất, chất lượng công việc',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Hoàn thành vượt khối lượng task được giao với chất lượng xuất sắc, không có bug <br />'
                                                . '- Giải quyết các task phức tạp nhưng đạt chất lượng xuất sắc</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Hoàn thành khối lượng task được giao với chất lượng xuất sắc, không có bug <br />'
                                                . '- Hoặc hoàn thành vượt khối lượng task được giao, có bug nhưng không ảnh hưởng lớn tới dự án</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Hoàn thành đủ task nhưng có chậm hoặc vẫn còn bug nhưng chưa ảnh hưởng nghiêm trọng tới dự án</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Chậm deadline quá nhiều hoặc gây lỗi nghiêm trọng</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        11,
                    'name' =>           'Thái độ, trách nhiệm & tính chủ động',
                    'label' =>          'Thái độ, trách nhiệm',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Trách nhiệm, chủ động đề xuất cải tiến, support tăng năng suất chất lượng cho dự án</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Trách nhiệm, làm tròn công việc được giao</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Thụ động ngồi chờ việc, phải nhắc nhở</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Thái độ thiếu hợp tác, không tuân thủ yêu cầu của quản lý và yêu cầu công việc <br />'
                                                . '- Nghỉ nhiều không có lý do khách quan</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        12,
                    'name' =>           'Kỹ năng làm việc nhóm',
                    'label' =>          'Làm việc nhóm',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Chủ động hoàn thành phần việc của mình, và tích cực support, chia sẻ công việc các thành viên khác</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Độc lập hoàn thành phần việc của mình, khi cần cũng support hỗ trợ cho các các thành viên khác</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Còn thiếu chủ động hợp tác, phải nhắc nhở</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Không hợp tác với các thành viên khác</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        13,
                    'name' =>           'Tham gia Đào tạo, phát triển cá nhân',
                    'label' =>          'Học tập, đào tạo phát triển',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Host ít nhất 2 sự kiện chuyên môn trong tháng <br />'
                                                . '- Tich cực  trong việc sẵn sàng chia sẻ kiến thức cho đội ngũ</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Host 1 sự kiện hoặc tham gia ít nhất 3 sự kiện chuyên môn trong tháng</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Tham gia 1-2 sự kiện chuyên môn trong tháng</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Không tham gia hoạt động chuyên môn nào</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        14,
                    'name' =>           'Tính tuân thủ các quy định, quy trình của Công ty & của dự án',
                    'label' =>          'Chuyên cần, tuân thủ quy định chung',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Không đi muộn, không vi phạm nội quy</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Đi muộn, vi phạm nội quy ở mức độ nhẹ <br />'
                                                . '- Nghỉ nhiều vì lý do khách quan</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Thường xuyên đi muộn hoặc vi phạm nội quy ở mức nghiêm trọng</li>'
                                        . '</ul>',
                ],
            ],
            'en' => [
                [
                    'attr_id' =>        1,
                    'name' =>           'Following working time regulations',
                    'label' =>          'Working time',
                    'description' =>    '<p>Data is automatically collected from attendance system. 1 point is subtracted for each time arriving late at work.</p>
                        Maximum: 10 <br>
                        Minimum: 0 <br>',
                ],
                [
                    'attr_id' =>        2,
                    'name' =>           'Following work regulations',
                    'label' =>          'Regulations',
                    'description' =>    '<p>Substract points when: employee not focusing on work, do personal work during working time, violating data security regulations, late arrival at work,...<br>
                        If not too serious: minus 5 point/each <br>
                        If serious: minus 10 point/each <br>
                        If not violating any regulations: 10 point </p>
                        Maximum point: 10 <br>
                        Minimum point: 0 <br>',
                ],
                [
                    'attr_id' =>        3,
                    'name' =>           'Contribute to professional activities ',
                    'label' =>          'Professional activities',
                    'description' =>    '<p>Take part in training classes, seminars, workshops... contribute to teams and company in term of specialized skills and foreign languages.</p>
                        <ul>
                            <li>8-10point/each activity: participate as seminar/workshop\'s host, trainer...</li>
                            <li>5point: participate in a long-term training</li>
                            <li>3point/each activity: participate as a participant of a seminar, workshop</li>
                            <li>0point: not participating in any activities</li>
                        </ul>
                        Maximum point: 10 <br>
                        Minimun point: 0<br>',
                ],
                [
                    'attr_id' =>        4,
                    'name' =>           'Contribute to social activities. ',
                    'label' =>          'Social activities',
                    'description' =>    '<p>Participate in other extra-cirriculum activities, charity, social work etc.</p>
                        <ul>
                            <li>8-10point/each activity: participate as an event\'s organizer</li>
                            <li>5-7point/each activity: participate as a normal participant</li>
                            <li>3-5point/each activity: participate but not very enthusiastic as a participant</li>
                            <li>1-2point/each activity: participate frequently in monthly activities hosted by company\'s clubs.</li>
                        </ul>
                        Maximum point: 10<br>
                        Minimun point: 0<br>',
                ],
                [
                    'attr_id' =>        5,
                    'name' =>           'Feedback from clients about members',
                    'label' =>          'Customer feedback',
                    'description' =>    'If there is none, select "N/A"',
                ],
                [
                    'attr_id' =>        6,
                    'name' =>           'Work Quality Assurance',
                    'label' =>          'Work quality',
                    'description' =>    'PM assesses member\'s work performance',
                ],
                [
                    'attr_id' =>        7,
                    'name' =>           'Work Progress Assurance',
                    'label' =>          'Work progress',
                    'description' =>    'PM assesses member\'s work performance',
                ],
                [
                    'attr_id' =>        8,
                    'name' =>           'Complying with procedures',
                    'label' =>          'Work process',
                    'description' =>    'PM assesses member\'s compliance with company\'s procedures',
                ],
                [
                    'attr_id' =>        9,
                    'name' =>           'Teamwork skills',
                    'label' =>          'Teamwork',
                    'description' =>    'PM assesses member\'s teamwork skills',
                ],
                //new
                [
                    'attr_id' =>        10,
                    'name' =>           'Hiệu suất công việc (Trên cơ sở chất lượng & thời gian hoàn thành công việc)',
                    'label' =>          'Năng suất, chất lượng công việc',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Hoàn thành vượt khối lượng task được giao với chất lượng xuất sắc, không có bug <br />'
                                                . '- Giải quyết các task phức tạp nhưng đạt chất lượng xuất sắc</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Hoàn thành khối lượng task được giao với chất lượng xuất sắc, không có bug <br />'
                                                . '- Hoặc hoàn thành vượt khối lượng task được giao, có bug nhưng không ảnh hưởng lớn tới dự án</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Hoàn thành đủ task nhưng có chậm hoặc vẫn còn bug nhưng chưa ảnh hưởng nghiêm trọng tới dự án</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Chậm deadline quá nhiều hoặc gây lỗi nghiêm trọng</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        11,
                    'name' =>           'Thái độ, trách nhiệm & tính chủ động',
                    'label' =>          'Thái độ, trách nhiệm',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Trách nhiệm, chủ động đề xuất cải tiến, support tăng năng suất chất lượng cho dự án</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Trách nhiệm, làm tròn công việc được giao</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Thụ động ngồi chờ việc, phải nhắc nhở</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Thái độ thiếu hợp tác, không tuân thủ yêu cầu của quản lý và yêu cầu công việc <br />'
                                                . '- Nghỉ nhiều không có lý do khách quan</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        12,
                    'name' =>           'Kỹ năng làm việc nhóm',
                    'label' =>          'Làm việc nhóm',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Chủ động hoàn thành phần việc của mình, và tích cực support, chia sẻ công việc các thành viên khác</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Độc lập hoàn thành phần việc của mình, khi cần cũng support hỗ trợ cho các các thành viên khác</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Còn thiếu chủ động hợp tác, phải nhắc nhở</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Không hợp tác với các thành viên khác</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        13,
                    'name' =>           'Tham gia Đào tạo, phát triển cá nhân',
                    'label' =>          'Học tập, đào tạo phát triển',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Host ít nhất 2 sự kiện chuyên môn trong tháng <br />'
                                                . '- Tich cực  trong việc sẵn sàng chia sẻ kiến thức cho đội ngũ</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Host 1 sự kiện hoặc tham gia ít nhất 3 sự kiện chuyên môn trong tháng</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Tham gia 1-2 sự kiện chuyên môn trong tháng</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Không tham gia hoạt động chuyên môn nào</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        14,
                    'name' =>           'Tính tuân thủ các quy định, quy trình của Công ty & của dự án',
                    'label' =>          'Chuyên cần, tuân thủ quy định chung',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Không đi muộn, không vi phạm nội quy</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Đi muộn, vi phạm nội quy ở mức độ nhẹ <br />'
                                                . '- Nghỉ nhiều vì lý do khách quan</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Thường xuyên đi muộn hoặc vi phạm nội quy ở mức nghiêm trọng</li>'
                                        . '</ul>',
                ],
            ],

            'jp' => [
                [
                    'attr_id' =>        1,
                    'name' =>           '作業勤務時間順守',
                    'label' =>          '勤務時間',
                    'description' =>    '<p>勤務時間管理システムから自動的に取得されます。 遅刻した場合は1回につき、1点を差し引きます。</p>
                        最大：10点 <br>
                        最小：0点 <br>',
                ],
                [
                    'attr_id' =>        2,
                    'name' =>           '作業規律順守',
                    'label' =>          '規則順守',
                    'description' =>    '<p>次の違反行為によってポイントを差し引く：夢中で勤務する、作業時間で個人的な仕事をする事、データセキュリティ違反行為、遅刻、。。。 <br>
                        正常：１回５ポイント差し引く <br>
                        深刻：１回１０ポイント差し引く <br>
                        違反しない：１０ポイント取得 </p>
                        最大：１０ポイント <br>
                        最小：０ポイント <br>',
                ],
                [
                    'attr_id' =>        3,
                    'name' =>           '専門活動に対する貢献 ',
                    'label' =>          '専門活動',
                    'description' =>    '<p>ゼミナール、訓練、ワークショップ活動など専門活動に貢献・参加すること。専門、言語の面でチーム及び会社に貢献すること。</p>
                        <ul>
                            <li>1回につき8～10点：ホストとして参加する</li>
                            <li>5点：長期訓練に参加する</li>
                            <li>1回につき3点：ゼミナール、ワークショップにメンバー（静聴者）として参加する</li>
                            <li>0点：参加しない</li>
                        </ul>
                        最大：10点<br>
                        最小：0点<br>',
                ],
                [
                    'attr_id' =>        4,
                    'name' =>           '社会、精神活動に対する貢献. ',
                    'label' =>          '社会活動',
                    'description' =>    '<p>社会、精神活動に貢献すること。社会交流、慈善、課外、雑誌などの活動に参加すること。</p>
                        <ul>
                            <li>1回につき8～10点：イベント主催者として参加する</li>
                            <li>1回につき5～7点：メンバとして熱心に参加する</li>
                            <li>1回につき3～5点：メンバとして不熱心に参加する</li>
                            <li>1～2点：クラブに参加する</li>
                        </ul>
                        最大：10点<br>
                        最小：0点<br>',
                ],
                [
                    'attr_id' =>        5,
                    'name' =>           'メンバーに対するお客様のフィードバック',
                    'label' =>          'お客様のフィードバック',
                    'description' =>    'フィードバックがない場合、[N/A］を選択',
                ],
                [
                    'attr_id' =>        6,
                    'name' =>           '仕事の質',
                    'label' =>          '仕事の質',
                    'description' =>    'PMがメンバーの完成度を評価する',
                ],
                [
                    'attr_id' =>        7,
                    'name' =>           '作業進捗',
                    'label' =>          '作業進捗',
                    'description' =>    'PMがメンバーの完成度を評価する',
                ],
                [
                    'attr_id' =>        8,
                    'name' =>           'プロセス順守',
                    'label' =>          'プロセス',
                    'description' =>    'PMがメンバーの完成度を評価する',
                ],
                [
                    'attr_id' =>        9,
                    'name' =>           'チームワークスキル',
                    'label' =>          'チームワーク',
                    'description' =>    'PMがメンバーの完成度を評価する',
                ],
                //new
                [
                    'attr_id' =>        10,
                    'name' =>           'Hiệu suất công việc (Trên cơ sở chất lượng & thời gian hoàn thành công việc)',
                    'label' =>          'Năng suất, chất lượng công việc',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Hoàn thành vượt khối lượng task được giao với chất lượng xuất sắc, không có bug <br />'
                                                . '- Giải quyết các task phức tạp nhưng đạt chất lượng xuất sắc</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Hoàn thành khối lượng task được giao với chất lượng xuất sắc, không có bug <br />'
                                                . '- Hoặc hoàn thành vượt khối lượng task được giao, có bug nhưng không ảnh hưởng lớn tới dự án</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Hoàn thành đủ task nhưng có chậm hoặc vẫn còn bug nhưng chưa ảnh hưởng nghiêm trọng tới dự án</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Chậm deadline quá nhiều hoặc gây lỗi nghiêm trọng</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        11,
                    'name' =>           'Thái độ, trách nhiệm & tính chủ động',
                    'label' =>          'Thái độ, trách nhiệm',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Trách nhiệm, chủ động đề xuất cải tiến, support tăng năng suất chất lượng cho dự án</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Trách nhiệm, làm tròn công việc được giao</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Thụ động ngồi chờ việc, phải nhắc nhở</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Thái độ thiếu hợp tác, không tuân thủ yêu cầu của quản lý và yêu cầu công việc <br />'
                                                . '- Nghỉ nhiều không có lý do khách quan</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        12,
                    'name' =>           'Kỹ năng làm việc nhóm',
                    'label' =>          'Làm việc nhóm',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Chủ động hoàn thành phần việc của mình, và tích cực support, chia sẻ công việc các thành viên khác</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Độc lập hoàn thành phần việc của mình, khi cần cũng support hỗ trợ cho các các thành viên khác</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Còn thiếu chủ động hợp tác, phải nhắc nhở</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Không hợp tác với các thành viên khác</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        13,
                    'name' =>           'Tham gia Đào tạo, phát triển cá nhân',
                    'label' =>          'Học tập, đào tạo phát triển',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Host ít nhất 2 sự kiện chuyên môn trong tháng <br />'
                                                . '- Tich cực  trong việc sẵn sàng chia sẻ kiến thức cho đội ngũ</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Host 1 sự kiện hoặc tham gia ít nhất 3 sự kiện chuyên môn trong tháng</li>'
                                            . '<li><span> 4 điểm: </span><br />'
                                                . '- Tham gia 1-2 sự kiện chuyên môn trong tháng</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Không tham gia hoạt động chuyên môn nào</li>'
                                        . '</ul>',
                ],
                [
                    'attr_id' =>        14,
                    'name' =>           'Tính tuân thủ các quy định, quy trình của Công ty & của dự án',
                    'label' =>          'Chuyên cần, tuân thủ quy định chung',
                    'description' =>    '<ul>'
                                            . '<li><span>10 điểm: </span><br />'
                                                . '- Không đi muộn, không vi phạm nội quy</li>'
                                            . '<li><span> 7 điểm: </span><br />'
                                                . '- Đi muộn, vi phạm nội quy ở mức độ nhẹ <br />'
                                                . '- Nghỉ nhiều vì lý do khách quan</li>'
                                            . '<li><span> 0 điểm: </span><br />'
                                                . '- Thường xuyên đi muộn hoặc vi phạm nội quy ở mức nghiêm trọng</li>'
                                        . '</ul>',
                ],
            ]
        ];

        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            MeAttribute::truncate();
            MeAttributeLang::truncate();
            //import data
            MeAttribute::insert($attrs);

            $dataLang = [];
            foreach ($attrLang as $lang => $val) {
                foreach ($val as $item) {
                    $item['lang_code'] = $lang;
                    $dataLang[] = $item;
                }
            }
            MeAttributeLang::insert($dataLang);

            $this->insertSeedMigrate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

}
