<?php
namespace Rikkei\Sales\Seeds;

use DB;
use Carbon\Carbon;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssCategory;
use Rikkei\Sales\Model\CssQuestion;

class CssTemplateDefaultVNSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(2)) {
            return true;
        }

        $cssCateIds = CssCategory::where("css_id", 0)->where("code", 1)->where("lang_id", Css::VIE_LANG)->get()->pluck("id")->toArray();
        if (!empty($cssCateIds)) {
            CssQuestion::whereIn("category_id", $cssCateIds)->delete();
            CssCategory::whereIn("id", $cssCateIds)->delete();
        }
        
        DB::beginTransaction();
        try {
            $data = [
                [
                    'data' => [
                        'name' => "CSS Template default VN",
                        'parent_id' => 0,
                        'project_type_id' => Css::TYPE_OSDC,
                        'code' => 1,
                        'lang_id' => Css::VIE_LANG,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                    'questions' => [
                        [
                            'content' => 'Nhìn chung, bạn cảm thấy hài lòng về các sản phẩm và dịch vụ chúng tôi không?',
                            'sort_order' => 1,
                            'is_overview_question' => 1,
                            'explain' => '5=>Hài lòng
                                        4=>Tương đối hài lòng
                                        3=>Bình thường
                                        2=>Tương đối không hài lòng 
                                        1=>Không hài lòng'
                        ],
                    ],
                    'child' => [
                        [
                            'data' => [
                                'name' => 'Chất lượng dự án',
                                'project_type_id' => Css::TYPE_OSDC,
                                'code' => 1,
                                'sort_order' => 1,
                                'lang_id' => Css::VIE_LANG,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ],
                            'sub-child' => [
                                [
                                    'data' => [
                                        'name' => 'Chất lượng của giai đoạn phân tích yêu cầu?',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 1,
                                        'lang_id' => Css::VIE_LANG,
                                        'question_explanation' => 'Chất lượng của giai đoạn nghiên cứu và phân tích yêu cầu bao gồm: SRS, Prototype, Q&A về yêu cầu,…',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => "Khả năng phân tích và làm rõ yêu cầu của dự án như thế nào?",
                                            'sort_order' => 1,
                                            'explain' => '5=>Khả năng hiểu và phân tích yêu cầu người dùng tốt, không phát sinh vấn đề.
                                                4=>Khả năng hiểu và phân tích yêu cầu tương đối tốt, vẫn cần hoạt động confirm Q&A tuy nhiên không ảnh hưởng đến tiến độ của dự án. 
                                                3=>Khả năng hiểu và phân tích nghiệp vụ chưa tốt, cần hoạt động confirm Q&A nhưng vẫn có thể chấp nhận được.
                                                2=>Vẫn xảy ra 1 vài vấn đề trong quá trình hiểu và phân tích yêu cầu, phải confirm bởi Q&A nhiều lần, phát sinh tình trạng công việc bị chậm hoặc làm lại,…
                                                1=>Xảy ra nhiều vấn đề trong quá trình hiểu và phân tích yêu cầu, có ảnh hưởng lớn đến tiến độ và chất lượng dự án.
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                        [
                                            'content' => 'Tài liệu đặc tả do dự án phát triển có rõ ràng không?',
                                            'sort_order' => 2,
                                            'explain' => '5=>Nội dung được mô tả rất rõ ràng để dự án có thể vận hành ổn định mà không phát sinh vấn đề
                                                4=>Nội dung được mô tả chưa rõ ràng, nhưng dự án đã làm rõ các nội dung này thông qua hoạt động Q&A
                                                3=>Nội dung được mô tả mơ hồ, chưa rõ ràng nhưng không phát sinh vấn đề re-work 
                                                2=>Phát sinh vấn đề delay, re-work từ nội dung mô tả yêu cầu chưa rõ ràng.
                                                1=>Nội dung được mô tả không đầy đủ và rõ ràng, ảnh hưởng lớn đến tiến độ và chất lượng dự án.
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                        [
                                            'content' => 'Thời gian hoàn thành tài liệu đặc tả có phù hợp không?',
                                            'sort_order' => 3,
                                            'explain' => '5=>Các tài liệu đặc tả được hoàn thành đúng hạn
                                                4=>Tài liệu đặc tả được hoàn thành hơi muộn nhưng không ảnh hưởng đến deadline của dự án
                                                3=>Tài liệu đặc tả được hoàn thành muộn, một số tasks gặp vấn đề nhưng không bị ảnh hưởng lớn
                                                2=>Phát sinh các vấn đề delay hoặc re-work do tài liệu đặc tả được hoàn thành muộn
                                                1=>Tài liệu đặc tả được hoàn thành muộn gây ảnh hưởng lớn đến tiến độ và chất lượng dự án
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => 'Chất lượng của giai đoạn Design',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 2,
                                        'lang_id' => Css::VIE_LANG,
                                        'question_explanation' => 'Chất lượng của giai đoạn Design bao gồm chất lượng của các sản phẩm: Architecture Design, Detailed Design, Screen Design; Solutions for design; Áp dụng công nghệ mới trong dự án…',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => 'Nội dung tài liệu Design do dự án phát triển có rõ ràng không?',
                                            'sort_order' => 1,
                                            'explain' => '5=>Nội dung được mô tả rất rõ ràng để dự án có thể vận hành ổn định mà không phát sinh vấn đề
                                                4=>Nội dung được mô tả chưa rõ ràng, nhưng dự án đã làm rõ các nội dung này thông qua hoạt động Q&A
                                                3=>Nội dung được mô tả mơ hồ, chưa rõ ràng nhưng không phát sinh vấn đề re-work 
                                                2=>Phát sinh vấn đề delay, re-work từ nội dung mô tả yêu cầu chưa rõ ràng. 
                                                1=>Nội dung được mô tả không đầy đủ và rõ ràng, ảnh hưởng lớn đến tiến độ và chất lượng dự án.
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                        [
                                            'content' => 'Chất lượng của các giải pháp cho Design có tốt không?',
                                            'sort_order' => 2,
                                            'explain' => '5=>Các giải pháp rất tốt và phù hợp để dự án có thể vận hành ổn định mà không phát sinh vấn đề
                                                4=>Một vài vấn đề phát sinh ở giải pháp nhưng được quản lí và giải quyết kịp thời không gây ảnh hưởng đến dự án.
                                                3=>Một vài vấn đề phát sinh ở giải pháp gây tốn chi phí nhưng không lớn và trong phạm vi cho phép.
                                                2=>Giải pháp chưa tốt dẫn tới chi phí sửa chữa cao nhưng không ảnh hưởng đến khách hàng
                                                1=>Giải pháp không tốt gây ra ảnh hưởng lớn đến khách hàng
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                        [
                                            'content' => 'Thời gian hoàn thành giai đoạn Design có phù hợp không?',
                                            'sort_order' => 3,
                                            'explain' => '5=>Design được hoàn thành đúng thời hạn
                                                4=>Design được hoàn thành hơi muộn nhưng không gây ảnh hưởng đến mốc deadline của dự án
                                                3=>Design được hoàn thành muộn, phát sinh vấn đề ở một số task nhưng không gây ảnh hưởng lớn.
                                                2=>Design được hoàn thành muộn, phát sinh vấn đề chậm trễ và re-work 
                                                1=>Design được hoàn thành muộn gây ảnh hưởng lớn đến tiến độ và chất lượng của dự án
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => 'Chất lượng của giai đoạn Coding',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 3,
                                        'lang_id' => Css::VIE_LANG,
                                        'question_explanation' => 'Chất lượng của phase Coding bao gồm chất lượng của source code, code review….',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => 'Source code có được viết rõ ràng không?',
                                            'sort_order' => 1,
                                            'explain' => '5=>Source code tuân thủ rule Coding convention, comments code được mô tả đầy đủ và rõ ràng.
                                                4=>Một vài source code không tuân thủ rule Coding convention, tuy nhiên comments code được mô tả đầy đủ và rõ ràng.
                                                3=>Một vài source code không tuân thủ rule Coding convention, comments code không được mô tả đầy đủ và rõ ràng.
                                                2=>Hầu hết các source code đều không tuân thủ rule Coding convention, và thiếu comments code.
                                                1=>Các source code đều không tuân thủ Coding convention, không có comments code.
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                        [
                                            'content' => 'Source code đã bao gồm tất các chức năng khách hàng yêu cầu chưa? ',
                                            'sort_order' => 2,
                                            'explain' => '5=>Source code đã bao gồm tất cả chức năng yêu cầu được mô tả trong tài liệu Spec, Design, chương trình chạy ổn định
                                                4=>Source code đã bao gồm các chức năng yêu cầu được mô tả trong tài liệu Spec, Design tuy nhiên chưa đầy đủ. 
                                                3=>Source code chưa phản ánh đầy đủ các yêu cầu, nhưng tốn ít effort để sửa chữa
                                                2=>Source code chưa phản ánh đầy đủ các yêu cầu, tốn nhiều effort để sửa chữa, tuy nhiên không gây ảnh hưởng đến khách hàng.
                                                1=>Source code chưa phản ánh đầy đủ các yêu cầu, tốn nhiều effort để sửa chữa và gây ảnh hưởng đến khách hàng.
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                        [
                                            'content' => 'Tình trạng các lỗi cơ bản trong source code như thế nào? (Phát hiện trong quá trình review code)',
                                            'sort_order' => 3,
                                            'explain' => '5=>Hầu hết source code đều không có lỗi cơ bản.
                                                4=>Còn một số lỗi cơ bản nhưng không tốn nhiều effort để sửa chữa, nằm trong phạm vi cho phép.
                                                3=>Phát sinh nhiều lỗi cơ bản nhưng không tốn nhiều effort để sửa chữa, nằm trong phạm vi cho phép.
                                                2=>Hầu hết các source code đều có lỗi cơ bản, tốn nhiều effort để sửa chữa, tuy nhiên không ảnh hưởng đến chất lượng và mốc deadline của dự án. 
                                                1=>Phát sinh nhiều lỗi cơ bản gây ảnh hưởng lớn đến tiến độ và chất lượng sản phẩm. 
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => 'Chất lượng của giai đoạn Testing',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 4,
                                        'lang_id' => Css::VIE_LANG,
                                        'question_explanation' => 'Chất lượng của giai đoạn Testing (Unit Test/ Integration Test/ System Test) bao gồm chất lượng source code sau giai đoạn testing, test cases, test data,  test evidences hoặc bug list được cung cấp bởi RikkeiSoft',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => 'Chất lượng của các tài liệu test có tốt không?',
                                            'sort_order' => 1,
                                            'explain' => '5=>Chất lượng tài liệu test tốt, không phát sinh vấn đề ảnh hưởng đến tiến độ và chất lượng dự án.
                                                4=>Tài liệu test có một vài lỗi nhỏ nhưng không ảnh hưởng đến tiến độ và chất lượng dự án.
                                                3=>Tài liệu test có một vài lỗi, phát sinh vấn đề ảnh hưởng đến chất lượng tuy nhiên vẫn có thể chấp nhận được.
                                                2=>Chất lượng tài liệu chưa tốt cần phải cập nhật và sửa chữa tài liệu.
                                                1=>Chất lượng tài liệu không tốt, cần phải được sửa chữa lại và gây ảnh hưởng đến quy trình và chất lượng dự án
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                        [
                                            'content' => 'Chất lượng của hoạt động test có tốt không?',
                                            'sort_order' => 2,
                                            'explain' => '5=>Không lọt leakage sau phase acceptance test của khách hàng, không có vấn đề phát sinh
                                                4=>Số lượng leakage sau phase acceptance test của khách hàng vẫn thuộc phạm vi cho phép, không ảnh hưởng đến quá trình vận hành
                                                3=>Số lượng leakage sau hoạt động acceptance test của khách hàng vẫn thuộc phạm vi cho phép, và có ảnh hưởng đến quá trình vận hành dự án
                                                2=>Số lượng leakage lọt sau phase acceptance test nhiều, phát sinh vấn đề delay và re-work, nhưng không ảnh hưởng đến mốc bàn giao
                                                1=>Số lượng leakage lọt sau phase acceptance test nhiều, gây ảnh hưởng lớn đến hoạt động của khách hàng
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                    ],
                                ],
                                [
                                    'data' => [
                                        'name' => 'Chất lượng của dịch vụ hỗ trợ khách hàng?',
                                        'project_type_id' => Css::TYPE_OSDC,
                                        'code' => 1,
                                        'sort_order' => 5,
                                        'lang_id' => Css::VIE_LANG,
                                        'question_explanation' => 'Chất lượng của dịch vụ hỗ trợ khách hàng từ thời điểm bàn giao cho hoạt động Acceptance test bao gồm: thời gian phản hồi, đề xuất giải pháp, tiến hành giải pháp,…',
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ],
                                    'questions' => [
                                        [
                                            'content' => "Thời gian phản hồi khách hàng của dự án có tốt không?",
                                            'sort_order' => 1,
                                            'explain' => '5=>Không phát sinh vấn đề về thời gian phản hồi
                                                4=>Phát sinh một vài vấn đề về thời gian phản hồi nhưng không ảnh hưởng đến dự án.
                                                3=>Thời gian phản hồi chậm, có ảnh hưởng nhưng không lớn đến dự án.
                                                2=>Thời gian phản hồi chậm nên dự án bỏ lỡ một số tasks
                                                1=>Không có hoạt động phản hồi cho các yêu cầu hoặc Q&A từ phía khách hàng.
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                        [
                                            'content' => 'Giải pháp dự án đề xuất có phù hợp không?',
                                            'sort_order' => 2,
                                            'explain' => '5=>Các giải pháp được đưa ra có thể giải quyết hầu hết các yêu cầu. 
                                                4=>Đưa ra giải pháp tốt, tuy nhiên một số yêu cầu chưa được xử lí đúng theo kì vọng.
                                                3=>Sau một vài lần confirm khách hàng mới nhận được các đề xuất giải pháp nhưng không ảnh hưởng đến tiến độ của dự án
                                                2=>Sau một vài lần confirm khách hàng mới nhận được các đề xuất giải pháp và gây ảnh hưởng tới tiến độ dự án
                                                1=>Khách hàng không nhận được bất kỳ giải pháp nào từ phía dự án gây ảnh hưởng lớn đến công việc của họ. 
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                        ],
                                    ],
                                ],
                            ]
                        ],
                        [
                            'data' => [
                                'name' => 'Quản lý thay đổi yêu cầu ',
                                'project_type_id' => Css::TYPE_OSDC,
                                'code' => 1,
                                'sort_order' => 2,
                                'lang_id' => Css::VIE_LANG,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ],
                            'questions' => [
                                [
                                    'content' => 'Các yêu cầu thay đổi đã được phản ánh và thực hiện đầy đủ trong các sản phẩm liên quan (SRS, Design, Source code, tài liệu Test,…) chưa?',
                                    'sort_order' => 1,
                                    'explain' => '5=>Các nội dung thay đổi yêu cầu được thực hiện và phản ánh đầy đủ  trong các sản phẩm liên quan, và không gây ảnh hưởng đến tiến độ dự án.
                                                4=>Có một số sai sót trong phần mô tả yêu cầu thay đổi, nhưng không ảnh hưởng đến tiến độ của dự án
                                                3=>Nội dung yêu cầu thay đổi không được mô tả đầy đủ, việc triển khai bị chậm trễ nhưng không xảy ra tình trạng làm lại
                                                2=>Nội dung yêu cầu thay đổi không được mô tả đầy đủ, việc triển khai bị chậm trễ dẫn đến tình trạng làm lại
                                                1=>Nội dung yêu cầu thay đổi không được mô tả đầy đủ, ảnh hưởng đến tiến độ và chất lượng dự án
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                ],
                                [
                                    'content' => 'Thời gian hoàn thành yêu cầu thay đổi có phù hợp không?',
                                    'sort_order' => 2,
                                    'explain' => '5=>Các yêu cầu thay đổi được hoàn thành đúng hạn
                                                4=>Các yêu cầu thay đổi được hoàn thành có sự chậm trễ tuy nhiên không ảnh hưởng đến mốc deadline của dự án
                                                3=>Các yêu cầu thay đổi được hoàn thành muộn, phát sinh vấn đề ở một số task nhưng không gây ảnh hưởng lớn
                                                2=>Phát sinh vấn đề delay và re-work khi các yêu cầu thay đổi được hoàn thành chậm trễ
                                                1=>Các yêu cầu thay đổi được hoàn thành muộn gây ảnh hưởng lớn đến tiến độ và chất lượng dự án
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                ],
                            ],
                        ],
                        [
                            'data' => [
                                'name' => 'Quản lý dự án',
                                'project_type_id' => Css::TYPE_OSDC,
                                'code' => 1,
                                'sort_order' => 3,
                                'lang_id' => Css::VIE_LANG,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ],
                            'questions' => [
                                [
                                    'content' => 'Tình trạng tuân thủ kế hoạch đã thống nhất của dự án như thế nào?
                                    (Thực hiện đúng tiến độ đã cam kết được thể hiện trong tiến độ phát triển, bàn giao đúng thời hạn…)',
                                    'sort_order' => 1,
                                    'explain' => '5=>Dự án được quản lý chặt chẽ và hoàn thành đúng tiến độ
                                                4=>Một vài vấn đề phát sinh trong quá trình kiểm soát tiến độ tuy nhiên không gây ảnh hưởng đến kế hoạch đã cam kết
                                                3=>Một vài vấn đề phát sinh trong quá trình kiểm soát tiến độ; kế hoạch bị trì hoãn nhưng không ảnh hưởng đến khách hàng
                                                2=>Tiến độ dự án không được quản ký chặt chẽ, ảnh hưởng đến kế hoạch của khách hàng
                                                1=>Tiến độ dự án ngoài tầm kiểm soát, ảnh hưởng lớn đến chất lượng dự án và kế hoạch của khách hàng
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                ],
                                [
                                    'content' => "Bạn đánh giá như thế nào về khả năng tạo báo cáo và chất lượng báo cáo của dự án? ",
                                    'sort_order' => 2,
                                    'explain' => '5=>Báo cáo được gửi đúng tiến độ và phản ánh đúng tình trạng thực tế của dự án
                                                4=>Báo cáo được gửi muộn nhưng phản ánh đúng tình trạng thực tế của dự án
                                                3=>Báo cáo được gửi muộn với một vài lỗi phát sinh tuy nhiên không cần hoạt động update
                                                2=>Báo cáo không được gửi đúng tiến độ hoặc phát sinh lỗi và cần được làm lại
                                                1=>Dự án không có hoạt động gửi report theo yêu cầu của khách hàng.
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                ],
                                [
                                    'content' => "Bạn đánh giá thế nào về khả năng quản lý Risk và Issue của dự án?",
                                    'sort_order' => 3,
                                    'explain' => '5=>Quản lý Risk và Issue tốt, đưa ra được các giải pháp đề phòng và giảm thiểu rủi ro tốt
                                                4=>Một số Risk và Issue chưa được quản lý tốt, nhưng các biện pháp giải quyết và đề phòng rủi ro được thực hiện kịp thời
                                                3=>Hoạt động quản lý Risk và Issue chưa tốt, tuy nhiên các biện pháp giải quyết và đề phòng rủi ro được đưa ra và không gây ảnh hưởng đến dự án
                                                2=>Hoạt động quản lý Risk và Issue chưa tốt, không đưa ra được các biện pháp giải quyết và đề phòng rủi ro gây ảnh hưởng đến dự án.
                                                1=>Hoạt động quản lý Risk và Issue kém gây ảnh hưởng lớn đến quy trình và chất lượng dự án
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                ],
                                [
                                    'content' => "Bạn đánh giá như thế nào về sự tận tâm của dự án?",
                                    'sort_order' => 4,
                                    'explain' => '5=>Hài lòng
                                                4=>Tương đối hài lòng
                                                3=>Bình thường
                                                2=>Tương đối không hài lòng 
                                                1=>Không hài lòng
                                                0=>Không đánh giá vì tiêu chí này không quan trọng hoặc không áp dụng trong dự án'
                                ]
                            ],
                        ],
                    ],
                ],
            ];
            foreach ($data as $key => $itemRoot) {
                if (isset($itemRoot['data'])) {
                    $rootCate = CssCategory::create($itemRoot['data']);
                    if (isset($itemRoot['questions'])) {
                        foreach ($itemRoot['questions'] as $item) {
                            $item['category_id'] = $rootCate->id;
                            if (!empty($item['explain'])) {
                                $qsExplains = preg_split('/\r\n|[\r\n]/', $item['explain']);
                                if ($qsExplains) {
                                    $ovvQsExplain = [];
                                    foreach ($qsExplains as $qsExplain) {
                                        $qsExplainItem = explode('=>', $qsExplain);
                                        if (count($qsExplainItem) > 1) {
                                            $ovvQsExplain[trim($qsExplainItem[0])] = trim($qsExplainItem[1]);
                                        }
                                    }
                                    $item['explain'] = json_encode($ovvQsExplain);
                                }
                            }
                            CssQuestion::create($item);
                        }
                    }
                    if (isset($itemRoot['child'])) {
                        foreach ($itemRoot['child'] as $key1 => $itemChild) {
                            if (isset($itemChild['data'])) {
                                $itemChild['data']['parent_id'] = $rootCate->id;
                                $childCate = CssCategory::create($itemChild['data']);
                                if (isset($itemChild['questions'])) {
                                   foreach ($itemChild['questions'] as $key2 => $itemQs) {
                                        $itemQs['category_id'] = $childCate->id;
                                        if (!empty($itemQs['explain'])) {
                                            $qsExplains = preg_split('/\r\n|[\r\n]/', $itemQs['explain']);
                                            if ($qsExplains) {
                                                $ovvQsExplain = [];
                                                foreach ($qsExplains as $qsExplain) {
                                                    $qsExplainItem = explode('=>', $qsExplain);
                                                    if (count($qsExplainItem) > 1) {
                                                        $ovvQsExplain[trim($qsExplainItem[0])] = trim($qsExplainItem[1]);
                                                    }
                                                }
                                                $itemQs['explain'] = json_encode($ovvQsExplain);
                                            }
                                        }
                                        CssQuestion::create($itemQs);
                                   }
                                } elseif (isset($itemChild['sub-child'])) {
                                    foreach ($itemChild['sub-child'] as $key3 => $itemSub) {
                                        if (isset($itemSub['data'])) {
                                            $itemSub['data']['parent_id'] = $childCate->id;
                                            $subCate = CssCategory::create($itemSub['data']);
                                            if ($itemSub['questions']) {
                                               foreach ($itemSub['questions'] as $key4 => $itemSubQs) {
                                                    $itemSubQs['category_id'] = $subCate->id;
                                                    if (!empty($itemSubQs['explain'])) {
                                                        $qsExplains = preg_split('/\r\n|[\r\n]/', $itemSubQs['explain']);
                                                        if ($qsExplains) {
                                                            $ovvQsExplain = [];
                                                            foreach ($qsExplains as $qsExplain) {
                                                                $qsExplainItem = explode('=>', $qsExplain);
                                                                if (count($qsExplainItem) > 1) {
                                                                    $ovvQsExplain[trim($qsExplainItem[0])] = trim($qsExplainItem[1]);
                                                                }
                                                            }
                                                            $itemSubQs['explain'] = json_encode($ovvQsExplain);
                                                        }
                                                    }
                                                    CssQuestion::create($itemSubQs);
                                               }
                                            }
                                        }
                                    }
                                }
                            }
                        }
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
