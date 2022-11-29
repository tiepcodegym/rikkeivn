<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\CheckpointQuestion;
use Rikkei\Team\Model\Checkpoint;

class CheckpointTooltipSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('CheckpointTooltipSeeder-v14')) {
            return true;
        }

        DB::beginTransaction();
        try {
            //Update rank text of `Thái độ làm việc`
            CheckpointQuestion::where('content', 'Thái độ làm việc')
                    ->update([
                        'rank1_text' => 'Kém',
                        'rank2_text' => 'Bình thường',
                        'rank3_text' => 'Tốt',
                        'rank4_text' => 'Rất tốt',
                    ]);

            //Update rank text of `Khả năng phát triển, đề xuất spec`
            CheckpointQuestion::where('content', 'Khả năng phát triển, đề xuất spec')
                    ->update([
                        'rank1_text' => 'Không có khả năng',
                        'rank2_text' => 'Có khả năng',
                        'rank3_text' => 'Làm tốt',
                        'rank4_text' => 'Làm xuất sắc',
                    ]);

            //Data `Experience`, `Education and training`, `Achievement`
            $data = [
                'Kinh nghiệm làm Developer' => 'Tính từ mốc bắt đầu làm dự án đầu tiên của khách hàng (tính cả công ty đã từng làm)',
                'Kinh nghiệm làm QA' => 'Tính từ mốc bắt đầu làm dự án đầu tiên của khách hàng (tính cả công ty đã từng làm)',
                'Thời gian làm việc tại Rikkei' => 'Tính từ thời gian ký hợp đồng thử việc',
                'Trình độ học vấn' => 'Dưới đại học (THPT, Trung cấp, Cao đẳng, Aptech, ... : N/A )',
                'Chứng chỉ' => "Chứng chỉ chuyên ngành: FE (kỹ sư CNTT cơ bản), SW (kỹ sư CNTT phần mềm), chứng chỉ IBM, Cisco, MicroSoft, Aptech, ...
                                <br>=> <b>Ghi cụ thể tên chứng chỉ và năm cấp vào phần NOTE</b>",
                'Ngoại ngữ' => "Chỉ xét tiếng Nhật hoặc tiếng Anh. Trường hợp thi IELTS, TOEFL (tiếng Anh) hoặc thi NAT Test (tiếng Nhật) có thể quy điểm tương đương
                                <br>=> <b>Ghi cụ thể tên chứng chỉ Ngoại ngữ và năm cấp vào phần NOTE</b>",
                'Số man month trong 6 tháng tại Rikkei' => 'Số man.month tạo ra cho công ty trong vòng 6 tháng (1/9/2017~28/2/2018) được KH trả tiền (billable effort).
                                                    <br>Tham khảo người đánh giá nếu không nắm rõ',
                'Giải thưởng cá nhân tại Rikkei' => 'Giải thưởng của công ty/chi nhánh dành riêng cho cá nhân trong vòng 6 tháng (1/9/2017~28/2/2018):
                                                    nhân viên tiêu biểu, nhân viên trẻ triển vọng, ...
                                                    <br>=> <b>Ghi cụ thể tên của giải thưởng và thời điểm nhận giải vào phần NOTE</b>',
                'Số man month làm trong 6 tháng trước' => 'Số man.month tạo ra cho công ty trong vòng 6 tháng (1/9/2017~28/2/2018) được KH trả tiền (billable effort).
                                                    <br>Tham khảo người đánh giá nếu không nắm rõ',
            ];

            //Data Technical Knowledge and Skills
            $questionTechSkills = CheckpointQuestion::join('checkpoint_category', 'checkpoint_category.id', '=', 'checkpoint_question.category_id')
                    ->whereIn('checkpoint_category.name', ['Năng lực áp dụng quy trình', 'Năng lực chuyên môn'])
                    ->select('checkpoint_question.*')
                    ->get();
            foreach ($questionTechSkills as $itemSkill) {
                $rank = [];
                for ($i = 1; $i <= Checkpoint::TOTAL_CHOICE; $i++) {
                    $rankOrder = 'rank' . $i . '_text';
                    $rank[] = $itemSkill->$rankOrder;
                }
                $arrTooltip = [
                    'Không có khả năng' => '<b>Không có khả năng</b>: Chưa làm bao giờ, và ko tự tin nếu được giao phó',
                    'Có khả năng' => '<b>Có khả năng</b>: Chưa làm bao giờ, nhưng tự tin sẽ làm được nếu được giao phó',
                    'Bình thường' => '<b>Bình thường</b>: Đã từng làm, và thấy rằng làm được khi có sự hướng dẫn, đào tạo',
                    'Khá' => '<b>Khá</b>: Đã từng làm, và thấy rằng mình phù hợp để làm công việc này',
                    'Tốt' => '<b>Tốt</b>: Được người quản lý trực tiếp đánh giá tốt/tin tưởng & được giao phó tương đối thường xuyên',
                    'Làm tốt' => '<b>Làm tốt</b>: Được người quản lý trực tiếp đánh giá tốt/tin tưởng & được giao phó tương đối thường xuyên',
                    'Xuất sắc' => '<b>Xuất sắc</b>: Trên mức làm tốt & qua đó tạo ra tính lan toả, hướng dẫn cho nhiều người khác; dẫn dắt team',
                    'Làm xuất sắc' => '<b>Làm xuất sắc</b>: Trên mức làm tốt & qua đó tạo ra tính lan toả, hướng dẫn cho nhiều người khác; dẫn dắt team',
                ];
                $dataTooltip = [];
                foreach ($arrTooltip as $key => $value) {
                    if (in_array($key, $rank)) {
                        $dataTooltip[] = $value;
                    }
                }
                $data[$itemSkill->content] = implode('<br>', $dataTooltip);
            }

            //Data Soft Skills
            $questionSoftSkills = CheckpointQuestion::join('checkpoint_category', 'checkpoint_category.id', '=', 'checkpoint_question.category_id')
                    ->whereIn('checkpoint_category.name', ['Soft Skills'])
                    ->select('checkpoint_question.content')
                    ->get();
            foreach ($questionSoftSkills as $itemSoftSkill) {
                $data[$itemSoftSkill->content] = '<b>Bình thường</b>: Đã từng làm những công việc yêu cầu kỹ năng tương ứng hoặc tự tin nếu được trao cơ hội sử dụng
                                    <br><b>Khá</b>: Đánh giá bản thân phù hợp với những công việc có yêu cầu kỹ năng tương ứng
                                    <br><b>Tốt</b>: Được quản lý đánh giá tốt và tin tưởng giao phó những công việc có yêu cầu kỹ năng tương ứng
                                    <br><b>Xuất sắc</b>: Được quản lý và tập thể tín nhiệm với công việc yêu cầu kỹ năng tương ứng; tạo ra sức ảnh hưởng và dẫn dắt tập thể';
            }

            //Data Tác phong & Thái độ làm việc
            $question = CheckpointQuestion::join('checkpoint_category', 'checkpoint_category.id', '=', 'checkpoint_question.category_id')
                    ->where('checkpoint_category.name', 'like', '%Tác phong%')
                    ->select('checkpoint_question.*')
                    ->get();
            foreach ($question as $item) {
                $rank = [];
                for ($i = 1; $i <= Checkpoint::TOTAL_CHOICE; $i++) {
                    $rankOrder = 'rank' . $i . '_text';
                    $rank[] = $item->$rankOrder;
                }
                $arrTooltip = [
                    'Kém' => '<b>Kém</b>: Tự đánh giá mình không phù hợp và khó thích nghi',
                    'Không có khả năng' => '<b>Không có khả năng</b>: Tự đánh giá mình không phù hợp và khó thích nghi',
                    'Có khả năng' => '<b>Có khả năng</b>: Tự đánh giá mình phù hợp và khả năng cống hiến nhiều hơn cho tập thể trong tương lai',
                    'Bình thường' => '<b>Bình thường</b>: Tự đánh giá mình phù hợp và tự tin rằng sẽ quen được với tác phong làm việc công ty',
                    'Khá' => '<b>Khá</b>: Tự đánh giá mình phù hợp và khả năng cống hiến nhiều hơn cho tập thể trong tương lai',
                    'Tốt' => '<b>Tốt</b>: Được quản lý đánh giá tốt và tin tưởng',
                    'Làm tốt' => '<b>Làm tốt</b>: Được quản lý đánh giá tốt và tin tưởng',
                    'Xuất sắc' => '<b>Xuất sắc</b>: Được quản lý và tập thể đánh giá cao; tạo ra sức ảnh hưởng và dẫn dắt tập thể',
                    'Làm xuất sắc' => '<b>Làm xuất sắc</b>: Được quản lý và tập thể đánh giá cao; tạo ra sức ảnh hưởng và dẫn dắt tập thể',
                    'Rất tốt' => '<b>Rất tốt</b>: Được quản lý và tập thể đánh giá cao; tạo ra sức ảnh hưởng và dẫn dắt tập thể',
                ];
                $dataTooltip = [];
                foreach ($arrTooltip as $key => $value) {
                    if (in_array($key, $rank)) {
                        $dataTooltip[] = $value;
                    }
                }
                $data[$item->content] = implode('<br>', $dataTooltip);
            }

            //Update into database
            foreach ($data as $content => $tooltip) {
                CheckpointQuestion::where('content', $content)->update(['tooltip' => $tooltip]);
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
