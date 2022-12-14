<?php
namespace Rikkei\Team\Seeds;

use Illuminate\Database\Seeder;
use DB;
use Rikkei\Team\Model\CheckpointQuestion;

class CheckpointQuestionSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        $dataDemo = [
            [
                'content' => 'Kinh nghiệm làm developer',
                'category_id' => '11',
                'sort_order' => 1,
                'weight'     => 5,
                'rank1_text' => '<18 tháng',
                'rank2_text' => '>= 18 tháng',
                'rank3_text' => '>=3 năm',
                'rank4_text' => '>=5 năm',
            ],
            [
                'content' => 'Thời gian làm việc tại Rikkei',
                'category_id' => '11',
                'sort_order' => 2,
                'weight'     => 5,
                'rank1_text' => '<18 tháng',
                'rank2_text' => '>= 18 tháng',
                'rank3_text' => '>=3 năm',
                'rank4_text' => '>=5 năm',
            ],
            [
                'content' => 'Trình độ học vấn',
                'category_id' => '12',
                'sort_order' => 1,
                'weight'     => 4,
                'rank1_text' => 'N/A',
                'rank2_text' => 'Đại học',
                'rank3_text' => 'Thạc sĩ',
                'rank4_text' => 'Tiến sĩ',
            ],
            [
                'content' => 'Chứng chỉ',
                'category_id' => '12',
                'sort_order' => 2,
                'weight'     => 3,
                'rank1_text' => 'Chưa có',
                'rank2_text' => 'FE (tương đương)',
                'rank3_text' => 'FE (tương đương) + 1 chứng chỉ chuyên ngành',
                'rank4_text' => 'AP (tương đương) + 1 chứng chỉ chuyên ngành or FE + 2 chứng chỉ chuyên ngành',
            ],
            [
                'content' => 'Ngoại ngữ',
                'category_id' => '12',
                'sort_order' => 3,
                'weight'     => 8,
                'rank1_text' => 'Chưa có',
                'rank2_text' => 'N4 or TOEIC 500',
                'rank3_text' => 'N3 or TOEIC 650',
                'rank4_text' => 'N2 or TOEIC 800',
            ],
            [
                'content' => 'Số man month trong 6 tháng tại rikkei',
                'category_id' => '13',
                'sort_order' => 1,
                'weight'     => 4,
                'rank1_text' => '0-4.5mm',
                'rank2_text' => '4.5-6mm',
                'rank3_text' => '6-9mm',
                'rank4_text' => '9mm~',
            ],
            [
                'content' => 'Giải thưởng cá nhân tại rikkei',
                'category_id' => '13',
                'sort_order' => 2,
                'weight'     => 7,
                'rank1_text' => 'chưa có',
                'rank2_text' => '1 giải thưởng',
                'rank3_text' => '2 giải thưởng',
                'rank4_text' => '3 giải thưởng',
            ],
            [
                'content' => 'Khả năng Đọc hiểu requirement',
                'category_id' => '17',
                'sort_order' => 1,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng tạo estimation',
                'category_id' => '17',
                'sort_order' => 2,
                'weight'     => 2,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Khả năng phát triển, đề xuất spec',
                'category_id' => '17',
                'sort_order' => 3,
                'weight'     => 4,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Khả năng tạo tài liệu design',
                'category_id' => '17',
                'sort_order' => 4,
                'weight'     => 4,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng review tài liệu/code cho member khác',
                'category_id' => '17',
                'sort_order' => 5,
                'weight'     => 2,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Khả năng tạo tài liệu coding plan/ coding convention',
                'category_id' => '17',
                'sort_order' => 6,
                'weight'     => 3,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng tạo và thực hiện unit test/fix bug',
                'category_id' => '17',
                'sort_order' => 7,
                'weight'     => 3,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng tiến bộ từ các bài học và kinh nghiệm đúc kết sau mỗi dự án.',
                'category_id' => '17',
                'sort_order' => 8,
                'weight'     => 2,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Khả năng tư duy logic',
                'category_id' => '18',
                'sort_order' => 1,
                'weight'     => 4,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng sử dụng các công cụ lập trình (IDE, Debugger, …)',
                'category_id' => '18',
                'sort_order' => 2,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng nắm vững các khái niệm cơ bản của ngôn ngữ lập trình chủ chốt (ViewController, Delegate, …)',
                'category_id' => '18',
                'sort_order' => 3,
                'weight'     => 4,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng nắm vững các kỹ thuật chuyên sâu của ngôn ngữ lập trình chủ chốt (CoreData, SQLite, Thread,….)',
                'category_id' => '18',
                'sort_order' => 4,
                'weight'     => 4,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Khả năng sử dụng database (mysql, SQL server,…)',
                'category_id' => '18',
                'sort_order' => 5,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng tự giải quyết các vấn đề khi coding (tự tìm trên mạng, hỏi người khác,…)',
                'category_id' => '18',
                'sort_order' => 6,
                'weight'     => 2,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            
            
            
            
            [
                'content' => 'Khả năng hướng dẫn người khác về các vấn đề kỹ thuật',
                'category_id' => '18',
                'sort_order' => 7,
                'weight'     => 2,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Khả năng tìm hiểu và học tập các kỹ thuật mới',
                'category_id' => '18',
                'sort_order' => 8,
                'weight'     => 2,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Khả năng thành thạo các ngôn ngữ lập trình khác',
                'category_id' => '18',
                'sort_order' => 9,
                'weight'     => 2,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Khả năng giao tiếp',
                'category_id' => '15',
                'sort_order' => 1,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
                
            ],
            [
                'content' => 'Khả năng làm việc nhóm',
                'category_id' => '15',
                'sort_order' => 2,
                'weight'     => 3,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng làm việc độc lập',
                'category_id' => '15',
                'sort_order' => 3,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Đảm bảo an toàn thông tin trong công ty',
                'category_id' => '16',
                'sort_order' => 1,
                'weight'     => 2,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Chấp hành nghiêm chỉnh nội quy công ty',
                'category_id' => '16',
                'sort_order' => 2,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Đóng góp vào sự phát triển của team (tạo tài liệu traning cho member trong team,gợi ý cho leader, ….)',
                'category_id' => '16',
                'sort_order' => 3,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Thái độ làm việc',
                'category_id' => '16',
                'sort_order' => 4,
                'weight'     => 5,
                'rank1_text' => 'Kém',
                'rank2_text' => 'Bình thường',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Rất tốt',
            ],
            
            
            /** ------------ QA ------------------- */
            
            
            [
                'content' => 'Kinh nghiệm làm QA',
                'category_id' => '3',
                'sort_order' => 1,
                'weight'     => 5,
                'rank1_text' => '>= 6 tháng',
                'rank2_text' => '>= 18 tháng',
                'rank3_text' => '>= 3 năm',
                'rank4_text' => '>= 5 năm',
            ],
            [
                'content' => 'Thời gian làm việc tại Rikkei',
                'category_id' => '3',
                'sort_order' => 2,
                'weight'     => 5,
                'rank1_text' => '>= 6 tháng',
                'rank2_text' => '>= 18 tháng',
                'rank3_text' => '>= 3 năm',
                'rank4_text' => '>= 5 năm',
            ],
            [
                'content' => 'Trình độ học vấn',
                'category_id' => '4',
                'sort_order' => 1,
                'weight'     => 6,
                'rank1_text' => 'N/A',
                'rank2_text' => 'Đại học',
                'rank3_text' => 'Thạc sĩ',
                'rank4_text' => 'Tiến sĩ',
            ],
            [
                'content' => 'Chứng chỉ',
                'category_id' => '4',
                'sort_order' => 2,
                'weight'     => 4,
                'rank1_text' => 'Chưa có',
                'rank2_text' => 'ISTQB1',
                'rank3_text' => 'ISTQB2',
                'rank4_text' => 'ISTQB3(chưa có ở VN)',
            ],
            [
                'content' => 'Ngoại ngữ',
                'category_id' => '4',
                'sort_order' => 3,
                'weight'     => 5,
                'rank1_text' => 'Chưa có',
                'rank2_text' => 'N4 or TOEIC 500',
                'rank3_text' => 'N3 or TOEIC 650',
                'rank4_text' => 'N2 or TOEIC 800',
            ],
            [
                'content' => 'Số man month làm trong 6 tháng trước',
                'category_id' => '5',
                'sort_order' => 1,
                'weight'     => 3,
                'rank1_text' => '0-4.5mm',
                'rank2_text' => '4.5-6mm',
                'rank3_text' => '6-9mm',
                'rank4_text' => '9mm~',
            ],
            [
                'content' => 'Giải thưởng cá nhân tại Rikkei',
                'category_id' => '5',
                'sort_order' => 2,
                'weight'     => 7,
                'rank1_text' => 'chưa có',
                'rank2_text' => '1 giải thưởng',
                'rank3_text' => '2 giải thưởng',
                'rank4_text' => '3 giải thưởng',
            ],
            [
                'content' => 'Khả năng đọc hiểu requirement',
                'category_id' => '9',
                'sort_order' => 1,
                'weight'     => 4,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng phát triển, đề xuất spec',
                'category_id' => '9',
                'sort_order' => 2,
                'weight'     => 4,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng thiết kế Matrix (bao phủ bug)',
                'category_id' => '9',
                'sort_order' => 3,
                'weight'     => 4,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng viết test case (rõ ràng, đầy đủ)',
                'category_id' => '9',
                'sort_order' => 4,
                'weight'     => 4,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng review tài liệu thiết kế test',
                'category_id' => '9',
                'sort_order' => 5,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng tạo test script',
                'category_id' => '9',
                'sort_order' => 6,
                'weight'     => 4,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng đánh giá mức độ quan trọng của bug và đăng ký bug đúng quy trình',
                'category_id' => '9',
                'sort_order' => 7,
                'weight'     => 3,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng xác nhận bug',
                'category_id' => '9',
                'sort_order' => 8,
                'weight'     => 3,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng tiến bộ từ các bài học và kinh nghiệm đúc kết sau mỗi dự án',
                'category_id' => '9',
                'sort_order' => 9,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            
            
            
            
            
            [
                'content' => 'Khả năng sử dụng test tool',
                'category_id' => '10',
                'sort_order' => 1,
                'weight'     => 5,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Khả năng xác nhận spec',
                'category_id' => '10',
                'sort_order' => 2,
                'weight'     => 5,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Khả năng sử dụng tool hỗ trợ công việc ( DB, SVN…)',
                'category_id' => '10',
                'sort_order' => 3,
                'weight'     => 5,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            [
                'content' => 'Khả năng tự giải quyết vấn đề khi test (tự tìm hướng giải quyết hoặc hỏi người khác)',
                'category_id' => '10',
                'sort_order' => 4,
                'weight'     => 5,
                'rank1_text' => 'Không có khả năng',
                'rank2_text' => 'Có khả năng',
                'rank3_text' => 'Làm tốt',
                'rank4_text' => 'Làm xuất sắc',
            ],
            
            
            [
                'content' => 'Khả năng liên lạc (liên lạc và phản hồi thông tin nhanh, chính xác)',
                'category_id' => '7',
                'sort_order' => 1,
                'weight'     => 3,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng làm việc nhóm',
                'category_id' => '7',
                'sort_order' => 2,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Khả năng làm việc độc lập',
                'category_id' => '7',
                'sort_order' => 3,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            
            
            
            
            
            [
                'content' => 'Đảm bảo an toàn thông tin trong công ty',
                'category_id' => '8',
                'sort_order' => 1,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Chấp hành nội quy công ty',
                'category_id' => '8',
                'sort_order' => 2,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Đóng góp vào sự phát triển của team',
                'category_id' => '8',
                'sort_order' => 3,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Thái độ làm việc',
                'category_id' => '8',
                'sort_order' => 4,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            
            
        ];
        
        $maxId = DB::table('checkpoint_question')->max('id'); 
        if($maxId == 0){
            foreach ($dataDemo as $data) {
                $model = new CheckpointQuestion();
                $model->setData($data);
                $model->save();
            }
        }
        $this->insertSeedMigrate();
    }
}
