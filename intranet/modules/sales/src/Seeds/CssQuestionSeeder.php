<?php
namespace Rikkei\Sales\Seeds;

use DB;

class CssQuestionSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        DB::beginTransaction();
        try {
            $dataDemo = [
                [
                    'content' => 'Khả năng phân tích yêu cầu và lý giải yêu cầu của team đánh giá thế nào',
                    'category_id' => '6',
                    'sort_order' => 1
                ],
                [
                    'content' => 'Các thay đổi yêu cầu có được đáp ứng đầy đủ vào sản phẩm không (tài liệu spec, source code, …)',
                    'category_id' => '6',
                    'sort_order' => 2
                ],
                [
                    'content' => 'Tài liệu thiết kế của team có nội dung chính xác, rõ ràng không?',
                    'category_id' => '7',
                    'sort_order' => 3
                ],
                [
                    'content' => 'Solution của thiết kế có thích hợp không?',
                    'category_id' => '7',
                    'sort_order' => 4
                ],
                [
                    'content' => 'Source code có được viết rõ ràng không? (có đúng convention không, có comment đầy đủ không)',
                    'category_id' => '8',
                    'sort_order' => 5
                ],
                [
                    'content' => 'Source code có phản ánh đầy đủ nội dung spec không?',
                    'category_id' => '8',
                    'sort_order' => 6
                ],
                [
                    'content' => 'Bạn nghĩ thế nào về khả năng làm unit test của team',
                    'category_id' => '8',
                    'sort_order' => 7
                ],
                [
                    'content' => 'Chất lượng của tài liệu test do team tạo ra thế nào?',
                    'category_id' => '9',
                    'sort_order' => 8
                ],
                [
                    'content' => 'Chất lượng thực hiện test của team thế nào?',
                    'category_id' => '9',
                    'sort_order' => 9
                ],
                [
                    'content' => 'Chất lượng đối ứng bug của team thế nào?',
                    'category_id' => '9',
                    'sort_order' => 10
                ],
                [
                    'content' => 'Response của team có nhanh hay không?',
                    'category_id' => '10',
                    'sort_order' => 11
                ],
                [
                    'content' => 'Solution mà team đề xuất có thích hợp hay không?',
                    'category_id' => '10',
                    'sort_order' => 12
                ],
                [
                    'content' => 'Team có đảm bảo được schedule không? (Thực hiện đúng tiến độ như schedule đề ra, release đúng thời gian kế hoạch)',
                    'category_id' => '4',
                    'sort_order' => 13
                ],
                [
                    'content' => 'Khả năng tạo tài liệu báo cáo của team thế nào?',
                    'category_id' => '4',
                    'sort_order' => 14
                ],
                [
                    'content' => 'Khả năng quản lý rủi ro, quản lý vấn đề của team thế nào?',
                    'category_id' => '4',
                    'sort_order' => 15
                ],
                [
                    'content' => 'Team làm việc có nhiệt tình, hăng hái hay không?',
                    'category_id' => '4',
                    'sort_order' => 16
                ],
                [
                    'content' => 'Quan hệ và thái độ của nhân viên trong team khi làm việc với khách hàng có tốt hay không?',
                    'category_id' => '4',
                    'sort_order' => 17
                ],
                [
                    'content' => 'Khả năng kỹ thuật của BrSE đánh giá thế nào? (khả năng phân tích và truyền đạt yêu cầu, thiết kế, coding,…)',
                    'category_id' => '5',
                    'sort_order' => 18
                ],
                [
                    'content' => 'Khả năng tiếng Nhật của BrSE thế nào (mail tiếng Nhật, chat tiếng Nhật, nói chuyện tiếng Nhật, dịch tài liệu tiếng Nhật,…)',
                    'category_id' => '5',
                    'sort_order' => 19
                ],
                [
                    'content' => 'Khả năng giải quyết vấn đề và support cho dự án đánh giá thế nào',
                    'category_id' => '5',
                    'sort_order' => 20
                ],
                [
                    'content' => 'Một cách tổng thể thì quý khách có hài lòng với dịch vụ và sản phẩm mà công ty chúng tôi cung cấp hay không?',
                    'category_id' => '2',
                    'sort_order' => 1,
                    'is_overview_question' => 1
                ],




                [
                    'content' => 'Năng lực của nhân viên công ty cung cấp cho các bạn phù hợp chứ?',
                    'category_id' => '15',
                    'sort_order' => 1
                ],
                [
                    'content' => 'Kỹ năng về kỹ thuật của nhân viên công ty cung cấp cho các bạn phù hợp chứ (khả năng phân tích yêu cầu, thiết kế, coding,..)',
                    'category_id' => '15',
                    'sort_order' => 2
                ],
                [
                    'content' => 'Khả năng giải quyết vấn đề của nhân viên công ty cung cấp cho các bạn thế nào?',
                    'category_id' => '15',
                    'sort_order' => 3
                ],
                [
                    'content' => 'Kỹ năng làm việc nhóm của các thành viên trong team thế nào?',
                    'category_id' => '15',
                    'sort_order' => 4
                ],
                [
                    'content' => 'Các project trong OSDC có đảm bảo tiến độ đã nếu trong schedule hay không?',
                    'category_id' => '15',
                    'sort_order' => 5
                ],
                [
                    'content' => 'Khả năng tạo tài liệu báo cáo, report hàng ngày của member trong OSDC có tốt không?',
                    'category_id' => '15',
                    'sort_order' => 6
                ],
                [
                    'content' => 'Các thay đổi về requirement có được phản ánh đầy đủ vào sản phẩm hay không? (tài liệu requiremetn, source code,…)',
                    'category_id' => '15',
                    'sort_order' => 7
                ],
                [
                    'content' => 'Các task của member có được hoàn thành 1 cách triệt để hay không?',
                    'category_id' => '16',
                    'sort_order' => 8
                ],
                [
                    'content' => 'Việc hoàn thành task của member chất lượng thế nào?',
                    'category_id' => '16',
                    'sort_order' => 9
                ],
                [
                    'content' => 'Thời gian response của member có tốt không?',
                    'category_id' => '16',
                    'sort_order' => 10
                ],
                [
                    'content' => 'Trách nhiệm và thái độ làm việc của member trong team thế nào?',
                    'category_id' => '17',
                    'sort_order' => 11
                ],
                [
                    'content' => 'Member có trách nhiệm và ý thức được rằng phải nỗ lực vì lợi ích của công ty và của khách hàng hay không?',
                    'category_id' => '17',
                    'sort_order' => 12
                ],
                [
                    'content' => 'Member trong team có cố gắng khắc vụ những sự cố hay việc cá nhân để hoàn thành dự án tốt hay không?',
                    'category_id' => '17',
                    'sort_order' => 13
                ],
                [
                    'content' => 'Member có dùng hiệu quả thời gian làm việc của mình hay không? (làm việc riêng trong giờ làm việc, làm các công việc khác,…)',
                    'category_id' => '18',
                    'sort_order' => 14
                ],
                [
                    'content' => 'Member có đảm bảo tuân thủ đúng các quy tắc nội dung đã thống nhất giữa 2 công ty hay không?',
                    'category_id' => '18',
                    'sort_order' => 15
                ],
                [
                    'content' => 'Khả năng đọc hiểu và dịch mail tiếng Nhật có tốt không?',
                    'category_id' => '13',
                    'sort_order' => 16
                ],
                [
                    'content' => 'Khả năng dịch các tài liệu tiếng Nhật có tốt không?',
                    'category_id' => '13',
                    'sort_order' => 17
                ],
                [
                    'content' => 'Chất lượng dịch tiếng Nhật trong các buổi họp với khách hàng có tốt hay không?',
                    'category_id' => '13',
                    'sort_order' => 18
                ],
                [
                    'content' => 'Hãy đánh giá trách nhiệm của người phụ trách OSDC (quan tâm đến tình hình các dự án, khả năng điều chỉnh nguồn lực,…)',
                    'category_id' => '14',
                    'sort_order' => 19
                ],
                [
                    'content' => 'Quan hệ giữa Team và khách hàng có tốt hay không?',
                    'category_id' => '14',
                    'sort_order' => 20
                ],
                [
                    'content' => 'Một cách tổng thể thì quý khách có hài lòng với dịch vụ và sản phẩm mà công ty chúng tôi cung cấp hay không? (OSDC)',
                    'category_id' => '1',
                    'sort_order' => 1,
                    'is_overview_question' => 1
                ],
            ];

            $maxId = DB::table('css_question')->max('id'); 
            if($maxId == 0){
                foreach ($dataDemo as $data) {
                    if (! DB::table('css_question')->select('id')->where('content', $data['content'])->get()) {
                        DB::table('css_question')->insert($data);
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
