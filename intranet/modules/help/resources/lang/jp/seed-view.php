<?php
return [
    'ME guide' => 'Hướng dẫn',
    'ME score' => '<h4><strong>Điểm ME được tính dựa trên 3 khối điểm (lấy thang điểm 5):</strong></h4>
        <div><strong>Rule &amp; activities (30%):</strong> điểm của 3 tiêu chí<ul>
                            <li>Đảm bảo tuân thủ kỷ luật công việc * 25%;</li>
                            <li>Đóng góp cho các hoạt động chuyên môn * 50%;</li>
                            <li>Đóng góp cho các hoạt động tinh thần, xã hội * 25%;</li></ul></div>
        <div><strong>Individual Performance In Project (50%):</strong> Trung bình cộng của các chỉ số cá nhân trong dự án<ul>
                            <li>Phản hồi của khách hàng;</li> 
                            <li>Đảm bảo chất lượng;</li> 
                            <li>Đảm bảo tiến độ;</li> 
                            <li>Đảm bảo quy trình; Làm việc nhóm;</li></ul></div>
        <div><strong>Project point (20%):</strong> Tích của project point và project index.<ul>
                            <li>Công thức chuyển đổi: <b>MIN(project point * project index * 5 / 28, 5)</b> (28 là điểm tối đa của project point)</li></ul></div>',
    'ME discipline' => '<h4><strong>Đảm bảo tuân thủ kỷ luật công việc</strong></h4>
        <p>Trừ điểm theo các vi phạm: làm việc không tập trung, làm việc riêng trong giờ, vi phạm bảo mật, đi làm muộn quá nhiều...<br>
                        Nhắc nhở 1 lần, trừ 1 điểm (nghiêm trọng vẫn trừ 5-10 điểm, ví dụ: không tuân thủ bảo mật thông tin, ...)<br>
                        Nhắc nhở 3 lần: trừ 5 điểm<br>
                        Nhắc nhở > 3 lần, trừ 10 điểm<br>
                        Tối đa: 10 điểm<br>
                        Tối thiểu: 0 điểm</p>',
    'ME professional activities' => '<h4><strong>Đóng góp cho các hoạt động chuyên môn</strong></h4>
        <p>Tham gia các khoá đào tạo, seminar, workshop... đóng góp cho team và công ty về mặt ngoại ngữ và chuyên môn.<br>
            8-10 điểm/lần: tham gia với vai trò host seminar/workshop, trainer<br>
            5 điểm: tham gia 1 khóa đào tạo dài hạn<br>
            3 điểm/lần: tham gia làm member (khán giả) của 1 seminar/workshop<br>
            Tối đa: 10 điểm<br>
            Tối thiểu: 0 điểm</p>',
    'ME social activities' => '<h4><strong>Đóng góp cho các hoạt động tinh thần, xã hội</strong></h4>
        <p>Tham gia các hoạt động tinh thần, tổng hội, tập san, ngoại khóa, từ thiện, phong trào xã hội...<br>
            8-10 điểm/lần: tham gia với vai trò tổ chức event<br>
            5-7 điểm/lần: tham gia nhiệt tình với vai trò làm member<br>
            3-5 điểm/lần: tham gia không nhiệt tình với vai trò làm member<br>
            1-2 điểm: tham gia thường xuyên các CLB của công ty có hoạt động định kỳ hàng tháng</p>',
    'ME criteria' => '<h4><strong>Các tiêu chí liên quan đến dự án</strong></h4>
        <p>Phản hồi của khách hàng, Đảm bảo chất lượng, Đảm bảo tiến độ, Đảm bảo quy trình, Làm việc nhóm.<br>
            PM chọn các khoảng điểm từ "Unsatisfactory" đến "Excellent":</p>
        <ul style="padding-left: 20px;">
            <li>Excellent: nỗ lực hoàn thành thành nhiệm vụ được giao, kết quả tốt hơn mong đợi, chủ động đề xuất nâng cao hiệu suất, chất lượng... của cá nhân và team.</li>
            <li>Good: chủ động hoàn thành tốt nhiệm vụ được giao, vượt trên mức kỳ vọng của quản lý.</li>
            <li>Fair: làm tốt công việc được giao, đạt mức quản lý kỳ vọng.</li>
            <li>Satisfactory: hoàn thành các yêu cầu ở mức độ trung bình, vẫn còn thiếu sót nhưng vẫn chấp nhận được, không ảnh hưởng nghiêm trọng tới dự án.</li>
            <li>Unsatisfactory: chưa hoàn thành nhiệm vụ được giao, gây ảnh hưởng nghiêm trọng tới chất lượng, tiến độ dự án. Cần cố gắng để cải thiện hơn.</li>
        </ul>
        <p>Riêng phần Phản hồi khách hàng, nếu không có phản hồi thì để giá trị là "N/A" và sẽ không được tính vào điểm tổng.</p>',
    'ME note' => '<h4><i>Chú ý: Nếu CBNV tham gia nhiều dự án thì đánh giá sẽ tính theo tỉ lệ thời gian đóng góp trong các dự án</i></h4>',
    'ME group leader' => '<p>Sau khi PM submit, Group Leader thực hiện review ME.</p>
                            <ol>
                                <li>Để feedback lại cho PM<br>
                                    Group Leader nhấn chuột phải tại các điểm số cần feedback để comment.<br>
                                    Sau đó nhấn nút Feedback để gửi feedback cho PM.
                                </li>
                                <li>Để chấp nhận ME<br>
                                    Group Leader nhấn nút Approve.</li>
                            </ol>',
    'ME member' => '<p>Sau khi Group Leader chấp nhận ME, Member thực hiện review ME.</p>
                    <ol>
                        <li>Để feedback<br>
                            Member nhấn chuột phải tại các điểm số cần feedback để comment.<br>
                            Sau đó nhấn nút Feedback để gửi feedback.</li>
                        <li>Để chấp nhận ME<br>
                            Member nhấn nút Accept.</li>
                    </ol>',
    'ME PM' => '<ol>
                    <li>PM điền các điểm số cho từng nhân viên và Submit để Group Leader review.</li>
                    <li>Trường hợp Group Leader có feedback, status sẽ chuyển sang trạng thái Feedback.<br>
                        Điểm số nào có feedback sẽ có ký hiệu mũi tên.<br>
                        PM nhấn chuột phải vào điểm số đó để xem và thêm comment.<br>
                        PM điều chỉnh lại điểm số và Submit lại nếu cần.</li>
                </ol>',
    'ProjectReport title' => 'I. Quy trình báo cáo Project Point',
    'ProjectReport purpose' => '<dt>1. Mục đích</dt>
                                <dd>Quy trình này quy định các bước báo cáo kiểm soát tình hình dự án hàng tuần.</dd>',
    'ProjectReport scale' => '<dt>2. Phạm vi áp dụng</dt>
                            <dd>Quy trình áp dụng chủ yếu cho PM và PQA để đảm bảo tình hình dự án được phản ánh một cách chuẩn xác nhất.</dd>',
    'ProjectReport document' => '<dt>3. Tài liệu viện dẫn</dt>
                                <dd>- Không</dd>',
    'ProjectReport definition' => '<dt>4. Thuật ngữ và định nghĩa</dt>
                                    <dd>- Không</dd>',
    'ProjectReport details 1' => '<dt>5. Nội dung quy trình</dt>
                                <dd>
                                    <p>5.1. Sơ đồ quy trình báo cáo PP</p>',
    'ProjectReport details 2' => '<p>5.2. Diễn giải quy trình</p>
                                    <p></p>Khi báo cáo tình hình dự án, PM dựa vào tình hình cụ thể của dự án ở thời điểm hiện tại (cost, quality, schedule, timeliness,…) để báo cáo tình hình.
                                    <br>Sau khi PM click Report, PQA review lại các thông số đã phản ánh đúng chưa?
                                    <br>- Nếu chưa được, PQA feedback vấn đề lại cho PM. Lặp lại quy trình report.
                                    <br>- Nếu OK. Project point sẽ được baseline vào chủ nhật tuần đó.
                                    <br>Kết thúc quy trình báo cáo tình hình dự án.
                                    <p></p>
                                </dd>',
    'ProjectReport Summary Point Plan Effort comment' => 'Tổng effort plan, lấy từ WorkOrder',
    'ProjectReport Summary Point Plan Effort maining' => 'Tổng số effort PM estimate để hoàn thành dự án và đã có sự thống nhất với leader',
    'ProjectReport Summary Point Effort Effectiveness maining' => 'Tỷ lệ effort thực tế tính tới thời điểm hiện tại đã dùng so với effort theo plan tới thời điểm hiện tại',
    'ProjectReport Summary Point Effort Efficiency maining' => 'Tỷ lệ giữa tổng effort theo plan và tổng effort theo nguồn lực plan',
    'ProjectReport Summary Point Busy rate maining' => 'Tỷ lệ giữa tổng effort theo plan và tổng effort theo nguồn lực plan',
    'ProjectReport Summary Point Leakage maining' => 'Errors number: Tổng số leakage do khách hàng trả về của dự án.',
    'ProjectReport Summary Point Defect rate maining' => 'Errors number: Tổng số lỗi của dự án bao gồm do bên đội dự án tìm ra và leakage',
    'ProjectReport Summary Point Late Schedule maining' => 'Số ngày dự án đang chậm so với schedule tại thời điểm hiện tại<br>(PM điền)',
    'ProjectReport Summary Point Deliverable maining' => 'Số lần deliver đúng plan so với tổng deliver của dự án',
    'ProjectReport Summary Point Process None Compliance maining' => 'Số lần dự án không tuân thủ đúng quy trình (do PQA đánh giá)',
    'ProjectReport Summary Point Project Reports maining' => 'Weekly report, postmortem report, milestone reports, customer reports, mỗi lần muộn trừ 0.5 điểm, ko làm một lần trừ 1 điểm. Report đúng hạn được cộng 0.5 điểm. (Cộng tối đa đến 2 điểm, trừ tối đa đến -2 điểm)',
    'ProjectReport Summary Point explain' => ' <p>- Là Tab tổng quan các chỉ số của dự án</p>
            <p>- Điểm của dự án được tính bằng:</p>
            <p>Project Point = Effort Effectiveness + Customer Satisfation + Deliverable + Leakage + Process None Compliance + Project Reports</p>
            <p>- Xếp loại dự án:</p>
            <p>Project Evaluation: Follow Total point:<ul class="_table_list">
            <li>Excellent: &gt;20,</li><li>Good: 15-&lt;=20,</li> <li>Fair: 10-&lt;=15,</li> <li>Acceptable: 0-&lt;=10,</li> <li>Failed: =0</p></li></ul>',
    'ProjectReport Cost Billable Effort comment' => 'Effort được thống nhất với khách hàng, tự động lấy từ WorkOrder',
    'ProjectReport Cost Billable Effort maining' => 'Số effort bên sale thống nhất với khách hàng',
    'ProjectReport Cost Plan Effort - total comment' => 'Tổng effort plan, lấy từ WorkOrder',
    'ProjectReport Cost Plan Effort - total maining' => 'Tổng số effort PM estimate để hoàn thành dự án và đã có sự thống nhất với leader',
    'ProjectReport Cost Plan Effort - current comment' => 'Effort plan tính tới thời điểm hiện tại, PM fill',
    'ProjectReport Cost Plan Effort - current maining' => 'Số effort theo plan tính từ đầu dự án tới thời điểm hiện tại',
    'ProjectReport Cost Resource allocation - total comment' => 'Tổng effort resource của member dự án, lấy từ WorkOrder',
    'ProjectReport Cost Resource allocation - total maining' => 'Tổng số effort của cả dự án tính trên phương diện add resource',
    'ProjectReport Cost Calendar Effort - current comment' => 'Effort resource của member tính tới thời điểm hiện tại, lấy từ WorkOrder',
    'ProjectReport Cost Calendar Effort - current maining' => 'Số effort theo team allocation tính từ đầu dự án tới thời điểm hiện tại',
    'ProjectReport Cost Actual Effort comment' => 'Effort thực tế dự án đã sử dụng tính đến thời điểm hiện tại, PM fill',
    'ProjectReport Cost Actual Effort maining' => 'Tổng thời gian thực tế dự án đã sử dụng tính tới thời điểm hiện tại',
    'ProjectReport Cost Effort Effectiveness maining' => 'Tỷ lệ effort thực tế tính tới thời điểm hiện tại đã dùng so với effort theo plan tới thời điểm hiện tại',
    'ProjectReport Cost Effort Efficiency maining' => 'Tỷ lệ giữa tổng effort theo plan và tổng effort theo nguồn lực plan',
    'ProjectReport Cost Busy rate maining' => 'Tỷ lệ giữa tổng effort theo plan và tổng effort theo nguồn lực plan',
    'ProjectReport Cost explain' => '<p>- Là tab phản ánh tình hình cost của dự án</p>
                                    <p>- Các thông số tự động lấy từ WorkOrder: Billable Effort, Plan Effort - total, Resource allocation - total, Calendar Effort - current.</p>
                                    <p>- Các thông số PM tự điền: Plan Effort - current, Actual Effort.</p>
                                    <p>Effort Effectiveness = Actual Effort / Plan Effort current (tình hình effort thực tế sử dụng so với plan tính tời thời điểm hiện tại)</p>
                                    <p>Effort Efficiency = Plan Effort total / Resource allocation total (tỷ lệ này phản ánh việc sử dụng resource so với effort đã plan) </p>
                                    <p>Busy rate = Actual Effort / Calendar Effort current (Tỷ lệ này phản ánh effort thực tế sử dụng so với resource plan tính tới thời điểm hiện tại )</p>',
    'ProjectReport Quality Leakage comment' => 'Số bug khách hàng tìm thấy sau khi release',
    'ProjectReport Quality Leakage maining' => 'Errors number: Tổng số leakage do khách hàng trả về của dự án.',
    'ProjectReport Quality Defect rate comment' => 'Tổng bug của dự án',
    'ProjectReport Quality Defect rate maining' => 'Errors number: Tổng số lỗi của dự án bao gồm do bên đội dự án tìm ra và leakage',
    'ProjectReport Quality explain' => '<p>- Là tab phản ánh chất lượng dự án qua số bug và leakage</p>
                                        <p>- Nếu dự án dùng hệ thống redmine (git) nội bộ công ty để quản lý bug và leakage thì 2 giá trị này sẽ được tự động đồng bộ từ hệ thống redmine.</p>
                                        <p>- Nếu dự án không dùng hệ thống quản lý bug nội bộ (redmine, git) thì PM tự điền 2 giá trị này vào cột Error number tương ứng.</p>',
    'ProjectReport Timeliness Late Schedule comment' => 'Số ngày dự án đang chậm so với schedule, PM fill',
    'ProjectReport Timeliness Late Schedule maining' => 'Số ngày dự án đang chậm so với schedule tại thời điểm hiện tại<br>(PM điền)',
    'ProjectReport Timeliness Deliverable maining' => 'Số lần deliver đúng plan so với tổng deliver của dự án',
    'ProjectReport Timeliness explain' => '<p>- Là tab phản ánh tiến độ phát triển và deliver của dự án</p>
                                            <p>- Late Schedule: PM điền số ngày dự án đang chậm so với schedule vào ô Value</p>
                                            <p>- Deliverable: Giá trị này phản ánh tỷ lệ deliver on time trên tổng số deliver, được tính tự động từ danh sách deliverable (Deliverable lisy). PM cần điền ngày thực tế deliver (Actual date).</p>',
    'ProjectReport Process Process None Compliance comment' => 'Số lần dự án vi phạm quy trình',
    'ProjectReport Process Process None Compliance maining' => 'Số lần dự án không tuân thủ đúng quy trình (do PQA đánh giá)',
    'ProjectReport Process explain' => '<p>- Là tab phản ánh việc tuân thủ quy trình của dự án</p>
                                        <p>- Process None Compliance: Số lần dự án vi phạm quy trình. Giá trị này sẽ được lấy khi PQA thêm lần vi phạm vào danh sách None Compliance (None Compliance list)</p>',
    'ProjectReport CSS explain' => '<p>- Là tab thể hiện những đánh giá, phản ánh của khách hàng đối với dự án trong quá trình phát triển và khi kết thúc dự án</p>
                                    <p>- Customer satisfactions: Tự động lấy khi có điểm CSS khách hàng đánh giá.</p>
                                    <p>- Customer feedback: Ý kiến khách hàng theo dõi hàng ngày.</p>',
    'Legend title' => '1. Màu điểm',
    'Legend red' => 'Trạng thái cảnh báo cao cần xem lai những vấn đề liên quan để giải quyết',
    'Legend yellow' => 'Trạng thái cảnh báo bình thường',
    'Legend blue' => 'Trạng thái tốt',
    'Legend white' => 'Do project không report hàng tuần.<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tuần trước PM không báo cáo, dashboard list hiện màu trắng bạc<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bắt đầu từ thứ 5 đến hết chủ nhật, PM không report thì project sẽ hiện màu trắng bạc ngoài dashboard list<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Baseline list hiện màu trắng bạc, tuần đó PM không report',
    'Legend grey' => 'Do project quá end date mà vẫn chưa close',
    'Risk General information' => '(Thông tin chung)',
    'Risk General information Content' => 'nội dung của rủi ro',
    'Risk General information Level important' => 'mức độ quan trọng của rủi ro',
    'Risk General information Weakness' => 'điểm yếu của rủi ro',
    'Risk General information Owner' => 'bộ phận có trách nhiệm với rủi ro này',
    'Risk Solution using' => '(Biện pháp áp dụng)',
    'Risk Solution using Solution' => 'biện pháp đang áp dụng',
    'Risk Solution using Posibility' => 'khả năng xảy ra rủi ro',
    'Risk Solution Value' => 'giá trị rủi ro',
    'Risk Solution Impact' => 'tác động của rủi ro',
    'Risk Solution Handling method' => 'phương pháp xử lý rủi ro',
    'Risk General information Level important Low' => 'Mức độ Quan trọng: Thấp (3 điểm)',
    'Risk General information Level important Medium' => 'Mức độ Quan trọng: Trung Bình (4-5 điểm)',
    'Risk General information Level important High' => 'Mức độ Quan trọng: Cao (6-7 điểm)',
    'Risk General information Level important Very High' => 'Mức độ Quan trọng:<span style="mso-spacerun:yes">&nbsp; </span>Rất cao (8-9 điểm)',
    'Risk Value Low' => 'Giá trị rủi ro: Thấp (&lt;=0.5)',
    'Risk Value Medium' => 'Giá trị rủi ro: Trung Bình (0.5 &lt;x &lt;=3 )',
    'Risk Value High' => 'Giá trị rủi ro: Cao (&gt;3 hoặc = 6)',
    'Risk Value Very High' => 'Giá trị rủi ro:<span style="mso-spacerun:yes">&nbsp; </span>Rất cao (&gt;6)',
    'Risk Result' => '(Kết quả)',
    'Risk Result Finish date' => 'ngày hoàn thành',
    'Risk Result Test date' => 'ngày kiểm tra',
    'Risk Result Performer' => 'người thực hiện',
    'Risk Result Tester' => 'người kiểm tra',
    'Risk Result Result' => 'kết quả hoàn thành thực tế',
    'Risk Result Evidence' => 'bằng chứng chứng minh thực hiện giải pháp rủi ro',
    'Risk Solution suggest' => '(Biện pháp đề nghị)',
    'Risk Solution suggest Solution' => 'đề nghị giải pháp rủi ro',
    'Risk Solution suggest Posibility' => 'khả năng xảy ra',
    'Risk Solution suggest Risk acceptance criteria' => 'tiêu chí chấp nhận rủi ro',
    'Risk Solution suggest Acceptance reason' => 'lý do chấp nhận rủi ro',
];
