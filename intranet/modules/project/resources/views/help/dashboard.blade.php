@extends('layouts.default')

@section('title')
{{ trans('project::view.Project Report help') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
@endsection

@section('content')
<?php 
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Team\View\Permission;

$allColorStatus = ViewProject::getPointColor();
?>
<style type="text/css">
    .help-table-item table.table-bordered {
        border: 1px solid #000000;
    }
    .help-table-item .help-title-table {
        padding: 5px;
        margin-bottom: -6px;
    }
    .help-table-item .help-title-table:not(.title-sub) {
        background: rgb(142, 180, 227);
    }
    .help-table-item .help-title-table.title-sub {
        background: rgb(220, 230, 242);
    }
    a.comment-indicator:hover + comment { background:#ffd; position:absolute; display:block; border:1px solid black; padding:0.5em;  } 
    a.comment-indicator { background:red; display:inline-block; border:1px solid black; width:0.5em; height:0.5em;  } 
    comment { display:none;  } 
</style>

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <h2>I. Quy trình báo cáo Project Point</h2>
                <div>
                    <dt>1. Mục đích</dt>
                    <dd>Quy trình này quy định các bước báo cáo kiểm soát tình hình dự án hàng tuần.</dd>

                    <dt>2. Phạm vi áp dụng</dt>
                    <dd>Quy trình áp dụng chủ yếu cho PM và PQA để đảm bảo tình hình dự án được phản ánh một cách chuẩn xác nhất.</dd>

                    <dt>3. Tài liệu viện dẫn</dt>
                    <dd>- Không</dd>

                    <dt>4. Thuật ngữ và định nghĩa</dt>
                    <dd>- Không</dd>

                    <dt>5. Nội dung quy trình</dt>
                    <dd>
                        <p>5.1. Sơ đồ quy trình báo cáo PP</p>
                        <p>
                            <img src="{{ URL::asset('project/images/report.png') }}"
                        </p>
                        <p>5.2. Diễn giải quy trình</p>
                        </p>Khi báo cáo tình hình dự án, PM dựa vào tình hình cụ thể của dự án ở thời điểm hiện tại (cost, quality, schedule, timeliness,…) để báo cáo tình hình.
                        <br/>Sau khi PM click Report, PQA review lại các thông số đã phản ánh đúng chưa?
                        <br/>- Nếu chưa được, PQA feedback vấn đề lại cho PM. Lặp lại quy trình report.
                        <br/>- Nếu OK. Project point sẽ được baseline vào chủ nhật tuần đó.
                        <br />Kết thúc quy trình báo cáo tình hình dự án.
                        </p>
                    </dd>
                </div>
            </div>
        </div>

        <div class="row">
             <div class="col-md-12">
                <h2>II. Bảng đánh giá tình hình dự án</h2>
                <div class="help-table-item">
                    <h4 class="help-title-table">Summary Point</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                                <td  ><font >Project Information</font></td>
                                <td  ><font >Value</font></td>
                                <td  ><font >Point</font></td>
                                <td  ><font >LCL</font></td>
                                <td  ><font >Target</font></td>
                                <td  ><font >UCL</font></td>
                                <td  ><font >Note</font></td>
                                <td  ><font >Maining</font></td>
                                <td  ><font >Formula</font></td>
                            </tr>
                            <tr>
                                <td   ><font >Plan Effort - total (MM)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Tổng effort plan, lấy từ WorkOrder</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Tổng số effort PM estimate để hoàn thành dự án và đã có sự thống nhất với leader</font></td>
                            <td  ><font >Point = Follow Plan Effort - total: &lt;10: 0.5, &lt;=10-&lt;20: 1, &lt;=20-&lt;30: 2, &gt;=30: 3</font></td>
                            </tr>
                            <tr>
                                <td><font >Effort Effectiveness (%)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Actual Effort / Plan Effort - current</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="80" sdnum="1033;"><font >80</font></td>
                            <td  align="right" valign=bottom sdval="100" sdnum="1033;"><font >100</font></td>
                            <td  align="right" valign=bottom sdval="120" sdnum="1033;"><font >120</font></td>
                            <td  ><font</td>
                            <td  ><font >Tỷ lệ effort thực tế tính tới thời điểm hiện tại đã dùng so với effort theo plan tới thời điểm hiện tại</font></td>
                            <td  ><font >= Actual Effort/Plan Effort current *100<br>Point: Follow &quot;Effort Effectiveness&quot;: null: 1, &lt;=80: 3, 80-&lt;=100: 2, 100-&lt;=110: 1, 110-&lt;=120: -1, 120-&lt;=130: -2, &gt;130: -3</font></td>
                            </tr>
                            <tr>
                                <td   ><font >Effort Efficiency (%)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Plan Effort - total / Resource allocation - total</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="50" sdnum="1033;"><font >50</font></td>
                            <td  align="right" valign=bottom sdval="75" sdnum="1033;"><font >75</font></td>
                            <td  align="right" valign=bottom sdval="100" sdnum="1033;"><font >100</font></td>
                            <td  ><font</td>
                            <td  ><font >Tỷ lệ giữa tổng effort theo plan và tổng effort theo nguồn lực plan</font></td>
                            <td  ><font >= Plan Effort total/Resource allocation total*100<br>Follow Effort Efficiency: &lt;50: -2, =50-&lt;70: -1, =70-&lt;80: 0.5, =80-&lt;90: 1, &gt;=90: 2</font></td>
                            </tr>
                            <tr>
                                <td   ><font >Busy rate (%)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Actual Effort / Calendar Effort - current</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="80" sdnum="1033;"><font >80</font></td>
                            <td  align="right" valign=bottom sdval="100" sdnum="1033;"><font >100</font></td>
                            <td  align="right" valign=bottom sdval="120" sdnum="1033;"><font >120</font></td>
                            <td  ><font</td>
                            <td  ><font >Tỷ lệ effort thực tế tính tới thời điểm hiện tại đã dùng và tổng effort theo nguồn lực plan</font></td>
                            <td  ><font >= Actual Effort/Calendar Effort current *100<br>Point: Follow Busy rate: &lt;70: -2, =70-&lt;80: -1, =80-&lt;90: 1, =90-&lt;110: 2, =110-&lt;120: 1, =120-&lt;140: -1, &gt;140: -2</font></td>
                            </tr>
                            <tr>
                                <td  ><font >Leakage (%)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Leakage error / Defect error</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="3" sdnum="1033;"><font >3</font></td>
                            <td  align="right" valign=bottom sdval="5" sdnum="1033;"><font >5</font></td>
                            <td  align="right" valign=bottom sdval="7" sdnum="1033;"><font >7</font></td>
                            <td  ><font</td>
                            <td  align="left" valign=top><font  >Errors number: Tổng số leakage do khách hàng trả về của dự án.</font></td>
                            <td  ><font  >Value = Leakage error/Defect error*100<br>Point: Follow Leakage value: null: 3, &lt;=3: 3, 3-&lt;=5: 2, 5-&lt;=7: 1, 7-&lt;=9: 0.5, 9-&lt;=11: -1, 11-&lt;=13: -2, &gt;13: -3</font></td>
                            </tr>
                            <tr>
                                <td  ><font >Defect rate</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Defect error / Dev team effort (MD), if it exceeds the first quality gate actual date that this value &lt; 1 then reporting yellow</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="1" sdnum="1033;"><font >1</font></td>
                            <td  align="right" valign=bottom sdval="1.5" sdnum="1033;"><font >1.5</font></td>
                            <td  align="right" valign=bottom sdval="2" sdnum="1033;"><font >2</font></td>
                            <td  ><font</td>
                            <td  align="left" valign=top bgcolor="#FFFFFF"><font  >Errors number: Tổng số lỗi của dự án bao gồm do bên đội dự án tìm ra và leakage</font></td>
                            <td  ><font  >Value = Defect error/Dev team effort(MD)<br>Point: Follow Defect rate value: null: 2, &lt;=1: 2, 1-&lt;=3: 1, 3-&lt;=5: -1, &gt;5: -2</font></td>
                            </tr>
                            <tr>
                                <td  ><font >Late Schedule (pd)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Number days slower than schedule, PM fill</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="0" sdnum="1033;"><font >0</font></td>
                            <td  align="right" valign=bottom sdval="1" sdnum="1033;"><font >1</font></td>
                            <td  align="right" valign=bottom sdval="2" sdnum="1033;"><font >2</font></td>
                            <td  ><font</td>
                            <td  ><font  >Số ngày dự án đang chậm so với schedule tại thời điểm hiện tại<br>(PM điền)</font></td>
                            <td  ><font  >Value = number days slower than schedule<br>Point: Follow late schedule value: null: 2, 0: 2, 0-&lt;=1: 1, 1-&lt;=2: -1, &gt;2: -2</font></td>
                            </tr>
                            <tr>
                                <td  ><font >Deliverable (%)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Total deliverable on time / Total deliver till now (%)</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="40" sdnum="1033;"><font >40</font></td>
                            <td  align="right" valign=bottom sdval="70" sdnum="1033;"><font >70</font></td>
                            <td  align="right" valign=bottom sdval="100" sdnum="1033;"><font >100</font></td>
                            <td  ><font</td>
                            <td  ><font  >Số lần deliver đúng plan so với tổng deliver của dự án</font></td>
                            <td  ><font  >Value = Total deliver on time/Total deliver till now*100<br>Point: Follow deliver value: &lt;=40: -3, 40-&lt;=55: -2, 55-&lt;70: -1, =70: 0, 70-&lt;=85: 1, 85-&lt;100: 2, 100: 3</font></td>
                            </tr>
                            <tr>
                                <td   ><font >Process None Compliance</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Number process none compliance</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="0" sdnum="1033;"><font >0</font></td>
                            <td  align="right" valign=bottom sdval="1" sdnum="1033;"><font >1</font></td>
                            <td  align="right" valign=bottom sdval="2" sdnum="1033;"><font >2</font></td>
                            <td  ><font</td>
                            <td  ><font >Số lần dự án không tuân thủ đúng quy trình (do PQA đánh giá)</font></td>
                            <td  ><font > Point: Follow process none compliance: 0: 3, =1: 2, =2: 1, =3: 0, =4: -1, =5: -2, &gt;5: -3</font></td>
                            </tr>
                            <tr>
                                <td   ><font >Project Reports</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Total report: report yes + report no + report delayed</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="5" sdnum="1033;"><font >5</font></td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Weekly report, postmortem report, milestone reports, customer reports, mỗi lần muộn trừ 0.5 điểm, ko làm một lần trừ 1 điểm. Report đúng hạn được cộng 0.5 điểm. (Cộng tối đa đến 2 điểm, trừ tối đa đến -2 điểm)</font></td>
                            <td  ><font >Point = 2 + report yes * 0.5 - report no * 1 - report delayed * 0.5, -2 &lt;= point &lt;= 2</font></td>
                            </tr>
                            <tr>
                                <td   ><font >Customer Satisfation (Point)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Get from css system, COO can fill, if after 30 days since last actual date of deliver that not get css, value = 0</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="60" sdnum="1033;"><font >60</font></td>
                            <td  align="right" valign=bottom sdval="80" sdnum="1033;"><font >80</font></td>
                            <td  align="right" valign=bottom sdval="100" sdnum="1033;"><font >100</font></td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Point: Follow Customer satisfactions value: null: 0, 90-&lt;=100: 3, 80-&lt;=90: 2, 70-&lt;=80: 1, 60-&lt;=70: 0.5, 50-&lt;=60: -1, &lt;=50: -2</font></td>
                            </tr>
                            <?php /*<tr>
                                <td  ><font >Customer ideas (#)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Negative + Positive</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="0" sdnum="1033;"><font >0</font></td>
                            <td  align="right" valign=bottom sdval="0" sdnum="1033;"><font >0</font></td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Point = Positive - Negative (max: 2, min: -2)</font></td>
                            </tr>*/ ?>
                            <tr>
                                <td  ><font >Project Point</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Effort Effectiveness + Customer Satisfation + Deliverable + Leakage + Process None Compliance + Project Reports</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            </tr>
                            <tr>
                                <td  ><font >Project Evaluation</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Follow Total point: Excellent: &gt;20, Good: 15-&lt;=20, Fair: 10-&lt;=15, Acceptable: 0-&lt;=10, Failed: =0</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="help-table-item">
                    <h4 class="help-title-table">Cost</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                                <td  ><font >Project information</font></td>
                                <td  ><font >Value</font></td>
                                <td  ><font >Point</font></td>
                                <td  ><font >LCL</font></td>
                                <td  ><font >Target</font></td>
                                <td  ><font >UCL</font></td>
                                <td  ><font >Note</font></td>
                                <td  ><font >Maining</font></td>
                                <td  ><font >Formula</font></td>
                            </tr>
                            <tr>
                                <td   height="43" ><font >Billable Effort (MM)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Effort được thống nhất với khách hàng, tự động lấy từ WorkOrder</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Số effort bên sale thống nhất với khách hàng</font></td>
                            <td  ><font</td>
                            </tr>
                            <tr>
                                <td   height="43" ><font >Plan Effort - total (MM)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Tổng effort plan, lấy từ WorkOrder</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Tổng số effort PM estimate để hoàn thành dự án và đã có sự thống nhất với leader</font></td>
                            <td  ><font >Point: Follow Plan Effort - total: &lt;10: 0.5, &lt;=10-&lt;20: 1, &lt;=20-&lt;30: 2, &gt;=30: 3</font></td>
                            </tr>
                            <tr>
                                <td   height="43" ><font >Plan Effort - current (MM)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Effort plan tính tới thời điểm hiện tại, PM fill</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Số effort theo plan tính từ đầu dự án tới thời điểm hiện tại</font></td>
                            <td  ><font</td>
                            </tr>
                            <tr>
                                <td   height="43" ><font >Resource allocation - total (MM)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Tổng effort resource của member dự án, lấy từ WorkOrder</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Tổng số effort của cả dự án tính trên phương diện add resource</font></td>
                            <td  ><font</td>
                            </tr>
                            <tr>
                                <td   height="43" ><font >Calendar Effort - current (MM)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Effort resource của member tính tới thời điểm hiện tại, lấy từ WorkOrder</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Số effort theo team allocation tính từ đầu dự án tới thời điểm hiện tại</font></td>
                            <td  ><font</td>
                            </tr>
                            <tr>
                                <td   height="43" ><font >Actual Effort (MM)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Effort thực tế dự án đã sử dụng tính đến thời điểm hiện tại, PM fill</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Tổng thời gian thực tế dự án đã sử dụng tính tới thời điểm hiện tại</font></td>
                            <td  ><font</td>
                            </tr>
                            <tr>
                                <td   height="56" ><font >Effort Effectiveness (%)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Actual Effort / Plan Effort - current</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="80" sdnum="1033;"><font >80</font></td>
                            <td  align="right" valign=bottom sdval="100" sdnum="1033;"><font >100</font></td>
                            <td  align="right" valign=bottom sdval="120" sdnum="1033;"><font >120</font></td>
                            <td  ><font</td>
                            <td  ><font >Tỷ lệ effort thực tế tính tới thời điểm hiện tại đã dùng so với effort theo plan tới thời điểm hiện tại</font></td>
                            <td  ><font >= Actual Effort/Plan Effort current *100<br>Point: Follow &quot;Effort Effectiveness&quot;: null: 1, &lt;=80: 3, 80-&lt;=100: 2, 100-&lt;=110: 1, 110-&lt;=120: -1, 120-&lt;=130: -2, &gt;130: -3</font></td>
                            </tr>
                            <tr>
                                <td   height="68" ><font >Effort Efficiency (%)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Plan Effort - total / Resource allocation - total</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="50" sdnum="1033;"><font >50</font></td>
                            <td  align="right" valign=bottom sdval="75" sdnum="1033;"><font >75</font></td>
                            <td  align="right" valign=bottom sdval="100" sdnum="1033;"><font >100</font></td>
                            <td  ><font</td>
                            <td  ><font >Tỷ lệ giữa tổng effort theo plan và tổng effort theo nguồn lực plan</font></td>
                            <td  ><font >= Plan Effort total/Resource allocation total*100<br>Point: Follow Effort Efficiency: &lt;50: -2, =50-&lt;70: -1, =70-&lt;80: 0.5, =80-&lt;90: 1, &gt;=90: 2</font></td>
                            </tr>
                            <tr>
                                <td  ><font >Busy rate (%)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Actual Effort / Calendar Effort - current</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="80" sdnum="1033;"><font >80</font></td>
                            <td  align="right" valign=bottom sdval="100" sdnum="1033;"><font >100</font></td>
                            <td  align="right" valign=bottom sdval="120" sdnum="1033;"><font >120</font></td>
                            <td  ><font</td>
                            <td  ><font >Tỷ lệ giữa tổng effort theo plan và tổng effort theo nguồn lực plan</font></td>
                            <td  ><font >= Actual Effort/Calendar Effort current *100<br>Point: Follow Busy rate: &lt;70: -2, =70-&lt;80: -1, =80-&lt;90: 1, =90-&lt;110: 2, =110-&lt;120: 1, =120-&lt;140: -1, &gt;140: -2</font></td>
                            </tr>
                            <tr>
                                <td  ><font >Productivity</font></td>
                                <td  ><font</td>
                                <td  ><font >LOC:</font></td>
                                <td  ><font</td>
                                <td  ><font</td>
                                <td  ><font</td>
                                <td  ><font</td>
                                <td  ><font</td>
                                <td  ><font >=Line of code (current) / actual Effort</font></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="help-table-item">
                    <h4 class="help-title-table">Quality</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                                <td ><font >Project Information</font></td>
                                <td  ><font >Errors number</font></td>
                                <td  ><font >Value</font></td>
                                <td  ><font >Point</font></td>
                                <td  ><font >LCL</font></td>
                                <td  ><font >Target</font></td>
                                <td  ><font >UCL</font></td>
                                <td  ><font >Note</font></td>
                                <td  ><font</td>
                                <td  ><font</td>
                            </tr>
                            <tr>
                                <td  height="68" ><font >Leakage (%)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Số bug khách hàng tìm thấy sau khi release</comment>
                            <font</td>
                            <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Leakage error / Defect error</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="3" sdnum="1033;"><font >3</font></td>
                            <td  align="right" valign=bottom sdval="5" sdnum="1033;"><font >5</font></td>
                            <td  align="right" valign=bottom sdval="7" sdnum="1033;"><font >7</font></td>
                            <td  ><font</td>
                            <td  align="left" valign=top><font  >Errors number: Tổng số leakage do khách hàng trả về của dự án.</font></td>
                            <td  ><font  >Value = Leakage error/Defect error*100<br>Point: Follow Leakage value: null: 3, &lt;=3: 3, 3-&lt;=5: 2, 5-&lt;=7: 1, 7-&lt;=9: 0.5, 9-&lt;=11: -1, 11-&lt;=13: -2, &gt;13: -3</font></td>
                            </tr>
                            <tr>
                                <td  height="64" ><font >Defect rate</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Tổng bug của dự án</comment>
                            <font</td>
                            <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Defect error / Dev team effort (MD), if it exceeds the first quality gate actual date that this value &lt; 1 then reporting yellow</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="1" sdnum="1033;"><font >1</font></td>
                            <td  align="right" valign=bottom sdval="1.5" sdnum="1033;"><font >1.5</font></td>
                            <td  align="right" valign=bottom sdval="2" sdnum="1033;"><font >2</font></td>
                            <td  ><font</td>
                            <td  align="left" valign=top bgcolor="#FFFFFF"><font  >Errors number: Tổng số lỗi của dự án bao gồm do bên đội dự án tìm ra và leakage</font></td>
                            <td  ><font  >Value = Defect error/Dev team effort(MD)<br>Point: Follow Defect rate value: null: 2, &lt;=1: 2, 1-&lt;=3: 1, 3-&lt;=5: -1, &gt;5: -2</font></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="help-table-item">
                    <h4 class="help-title-table">Timeliness</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                                <td  ><font >Project Information</font></td>
                                <td  ><font >Value</font></td>
                                <td  ><font >Point</font></td>
                                <td  ><font >LCL</font></td>
                                <td  ><font >Target</font></td>
                                <td  ><font >UCL</font></td>
                                <td  ><font >Note</font></td>
                                <td  ><font</td>
                                <td  ><font</td>
                            </tr>
                            <tr>
                                <td  ><font >Late Schedule (pd)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Số ngày dự án đang chậm so với schedule, PM fill</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="0" sdnum="1033;"><font >0</font></td>
                            <td  align="right" valign=bottom sdval="1" sdnum="1033;"><font >1</font></td>
                            <td  align="right" valign=bottom sdval="2" sdnum="1033;"><font >2</font></td>
                            <td  ><font</td>
                            <td  ><font  >Số ngày dự án đang chậm so với schedule tại thời điểm hiện tại<br>(PM điền)</font></td>
                            <td  ><font  >Value = number days slower than schedule<br>Point: Follow late schedule value: null: 2, 0: 2, 0-&lt;=1: 1, 1-&lt;=2: -1, &gt;2: -2</font></td>
                            </tr>
                            <tr>
                                <td  ><font >Deliverable (%)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Total deliverable on time / Total deliver till now (%)</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="40" sdnum="1033;"><font >40</font></td>
                            <td  align="right" valign=bottom sdval="70" sdnum="1033;"><font >70</font></td>
                            <td  align="right" valign=bottom sdval="100" sdnum="1033;"><font >100</font></td>
                            <td  ><font</td>
                            <td  ><font  >Số lần deliver đúng plan so với tổng deliver của dự án</font></td>
                            <td  ><font  >Value = Total deliver on time/Total deliver till now*100<br>Point: Follow deliver value: &lt;=40: -3, 40-&lt;=55: -2, 55-&lt;70: -1, =70: 0, 70-&lt;=85: 1, 85-&lt;100: 2, 100: 3</font></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="help-table-item">
                    <h4 class="help-title-table title-sub">Deliverable list</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                                <td><font >No.</font></td>
                                <td><font >Deliverable</font></td>
                                <td><font >Committed date</font></td>
                                <td><font >Actual date</font></td>
                                <td><font >Point</font></td>
                                <td><font >Util now</font></td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                            </tr>
                            <tr>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="help-table-item">
                    <h4 class="help-title-table">Process</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                                <td  ><font >Project Information</font></td>
                                <td  ><font >Value</font></td>
                                <td  ><font >Point</font></td>
                                <td  ><font >LCL</font></td>
                                <td  ><font >Target</font></td>
                                <td  ><font >UCL</font></td>
                                <td  ><font >Note</font></td>
                                <td  ><font</td>
                                <td  ><font</td>
                            </tr>
                            <tr>
                                <td   ><font >Process None Compliance</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Số lần dự án vi phạm quy trình</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="0" sdnum="1033;"><font >0</font></td>
                            <td  align="right" valign=bottom sdval="1" sdnum="1033;"><font >1</font></td>
                            <td  align="right" valign=bottom sdval="2" sdnum="1033;"><font >2</font></td>
                            <td  ><font</td>
                            <td  ><font >Số lần dự án không tuân thủ đúng quy trình (do PQA đánh giá)</font></td>
                            <td  ><font >Point: Follow process none compliance: 0: 3, =1: 2, =2: 1, =3: 0, =4: -1, =5: -2, &gt;5: -3</font></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="help-table-item">
                    <h4 class="help-title-table title-sub">None Compliance list</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                                <td><font >No.</font></td>
                                <td><font >Title</font></td>
                                <td><font > Status</font></td>
                                <td><font > Priority</font></td>
                                <td><font >Assignee</font></td>
                                <td><font >Create date</font></td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                            </tr>
                            <tr>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="help-table-item">
                    <h4 class="help-title-table">CSS</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                            <tr>
                                <td  ><font >Project Information</font></td>
                                <td  ><font >Value</font></td>
                                <td  ><font >Point</font></td>
                                <td  ><font >LCL</font></td>
                                <td  ><font >Target</font></td>
                                <td  ><font >UCL</font></td>
                                <td  ><font >Note</font></td>
                                <td  ><font</td>
                                <td  ><font</td>
                            </tr>
                            <tr>
                                <td   ><font >Customer satisfactions</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Get from css system, COO can fill, if after 30 days since last actual date of deliver that not get css, value = 0</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  align="right" valign=bottom sdval="60" sdnum="1033;"><font >60</font></td>
                            <td  align="right" valign=bottom sdval="80" sdnum="1033;"><font >80</font></td>
                            <td  align="right" valign=bottom sdval="100" sdnum="1033;"><font >100</font></td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Point: Follow Customer satisfactions value: null: 0, 90-&lt;=100: 3, 80-&lt;=90: 2, 70-&lt;=80: 1, 60-&lt;=70: 0.5, 50-&lt;=60: -1, &lt;=50: -2</font></td>
                            </tr>
                            <tr>
                                <td  ><font</td>
                                <td  ><font</td>
                                <td  ><font</td>
                                <td  ><font >Positive</font></td>
                                <td  ><font >Negative</font></td>
                                <td  ><font</td>
                                <td  ><font</td>
                                <td  ><font</td>
                                <td  ><font</td>
                            </tr>
                            <?php /*<tr>
                                <td  ><font >Customer ideas (#)</font></td>
                                <td  ><a class="comment-indicator"></a>
                            <comment>maianh:
                                Negative + Positive</comment>
                            <font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font</td>
                            <td  ><font >Point = Positive - Negative (max: 2, min: -2)</font></td>
                            </tr>*/ ?>

                        </table>
                    </div>
                </div>

                <div class="help-table-item">
                    <h4 class="help-title-table title-sub">Positive and Negative</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                                <td><font >No.</font></td>
                                <td><font >Title</font></td>
                                <td><font > Type</font></td>
                                <td><font >Assignee</font></td>
                                <td><font >Create date</font></td>
                                <td><font >Point</font></td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                            </tr>
                            <tr>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                                <td><font</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="help-2-explain">
                    <dt>Summary Point: </dt>
                    <dd>
                        <p>- Là Tab tổng quan các chỉ số của dự án</p>
                        <p>- Điểm của dự án được tính bằng:</p>
                        <p>Project Point = Effort Effectiveness + Customer Satisfation + Deliverable + Leakage + Process None Compliance + Project Reports</p>
                        <p>- Xếp loại dự án:</p>
                        <p>Project Evaluation: Follow Total point: Excellent: >20, Good: 15-<=20, Fair: 10-<=15, Acceptable: 0-<=10, Failed: =0</p>
                    </dd>


                    <dt>Cost:</dt>
                    <dd>
                        <p>- Là tab phản ánh tình hình cost của dự án</p>
                        <p>- Các thông số tự động lấy từ WorkOrder: Billable Effort, Plan Effort - total, Resource allocation - total, Calendar Effort - current.</p>
                        <p>- Các thông số PM tự điền: Plan Effort - current, Actual Effort.</p>
                        <p>Effort Effectiveness = Actual Effort / Plan Effort current (tình hình effort thực tế sử dụng so với plan tính tời thời điểm hiện tại)</p>
                        <p>Effort Efficiency = Plan Effort total / Resource allocation total (tỷ lệ này phản ánh việc sử dụng resource so với effort đã plan) </p>
                        <p>Busy rate = Actual Effort / Calendar Effort current (Tỷ lệ này phản ánh effort thực tế sử dụng so với resource plan tính tới thời điểm hiện tại )</p>
                    </dd>

                    <dt>Quality:</dt>
                    <dd>
                        <p>- Là tab phản ánh chất lượng dự án qua số bug và leakage</p>
                        <p>- Nếu dự án dùng hệ thống redmine (git) nội bộ công ty để quản lý bug và leakage thì 2 giá trị này sẽ được tự động đồng bộ từ hệ thống redmine.</p>
                        <p>- Nếu dự án không dùng hệ thống quản lý bug nội bộ (redmine, git) thì PM tự điền 2 giá trị này vào cột Error number tương ứng.</p>
                    </dd>

                    <dt>Timeliness:</dt>
                    <dd>
                        <p>- Là tab phản ánh tiến độ phát triển và deliver của dự án</p>
                        <p>- Late Schedule: PM điền số ngày dự án đang chậm so với schedule vào ô Value</p>
                        <p>- Deliverable: Giá trị này phản ánh tỷ lệ deliver on time trên tổng số deliver, được tính tự động từ danh sách deliverable (Deliverable lisy). PM cần điền ngày thực tế deliver (Actual date).</p>
                    </dd>

                    <dt>Process:</dt>
                    <dd>
                        <p>- Là tab phản ánh việc tuân thủ quy trình của dự án</p>
                        <p>- Process None Compliance: Số lần dự án vi phạm quy trình. Giá trị này sẽ được lấy khi PQA thêm lần vi phạm vào danh sách None Compliance (None Compliance list)</p>
                    </dd>

                    <dt>CSS:</dt>
                    <dd>
                        <p>- Là tab thể hiện những đánh giá, phản ánh của khách hàng đối với dự án trong quá trình phát triển và khi kết thúc dự án</p>
                        <p>- Customer satisfactions: Tự động lấy khi có điểm CSS khách hàng đánh giá.</p>
                        <p>- Customer feedback: Ý kiến khách hàng theo dõi hàng ngày.</p>
                    </dd>
                </div>
            </div>
        </div>
        <!-- end II -->
        <?php $perCssHigh = Permission::getInstance()->isScopeCompany(null, 'project::css.reward.flag');?>
        <!-- III -->
        @if ($perCssHigh)
            <div class="row">
                 <div class="col-md-12">
                    <h2>III. Thưởng CSS điểm cao</h2>
                    <div>
                        <dt>1. Cơ chế lấy CSS: </dt>
                        <dd>
                            <p>Người gửi CSS: PQA</p>
                            <p>- Với cá nhân, OSDC: lấy 3 tháng 1 lần hoặc khi kết thúc OSDC</p>
                            <p>- Với Project base: kết thúc dự án.</p>
                            <p>Hoặc Sale ghi nhận nhận xét của KH trong quá trình thực hiện dự án.</p>
                        </dd>
                        <dt>2. Cơ chế thưởng:</dt>
                        <dd>
                            <p>- Thưởng ngay sau khi có CSS, deadline: ko quá ngày nhận CSS 1 tuần (5 workingdays)</p>
                            <p>- PQA confirm trước với leader và gửi tin cho truyền thông.</p>
                            <p>- Truyền thông vinh danh CSS cao >95 hoặc KH khen ngợi, 1 tháng 1 lần (intranet).</p>
                        </dd>
                        <dt>3. Mức thưởng: trích từ quỹ chi phí dự án của D, Leader dựa vào Guideline dưới để cân nhắc đưa ra quyết định:</dt>
                        <dd>
                            <p>Loại 1. Cá nhân: 100 đ, 500K (trường hợp onsite, đánh giá cá nhân)</p>
                            <p>Loại 2. Project: CSS>=95</p>
                            <p>- Billable<5MM: 500k</p>
                            <p>- Billable>=5MM: 1M</p>
                            <p>- Billable>=10MM: 1.5M</p>
                            <p>- Billable >=20MM: 2M</p>
                            <p>Loại 3: KH ko cho điểm nhưng có khen ngợi thành tích xuất sắc: Leader cân nhắc và ra quyết mực thưởng hợp lý. (không cao hơn mức thưởng CSS cao nhất)</p>
                        </dd>
                        <dt>4. Trách nhiệm các bên: </dt>
                        <dd>
                            <p>- PQA: chịu trách nhiệm đề xuất với leader khi nhận đc CSS, hoặc sale nhận đc khen thưởng thì cũng FW lại và đề xuất với Leader. Và PQA confirm trước với leader sau đó gửi tin cho truyền thông.</p>
                            <p>- Leader: có nhiệm vụ phản hồi nhanh chóng trong vòng 1 workingday.</p>
                            <p>- Admin: làm quyết định khen thưởng và giải ngân trong vòng 5 workingday.</p>
                            <p>- Truyền thông vinh danh CSS cao >95 hoặc KH khen ngợi, 1 tháng 1 lần (kênh intranet). </p>
                        </dd>
                    </div>
                 </div>
            </div>
        <!-- end III -->
        @endif
        <!-- IV -->
        <div class="row">
             <div class="col-md-12">
                <h2>{!! $perCssHigh ? 'IV' : 'III'!!}. Ký hiệu</h2>
                <div>
                    <h4>1. Màu điểm</h4>
                    <dd>
                        <p>
                        <img src="{{ $allColorStatus[ProjectPoint::COLOR_STATUS_RED] }}" />: 
                        Trạng thái cảnh báo cao cần xem lai những vấn đề liên quan để giải quyết
                        </p>
                    </dd>
                    
                    <dd>
                        <p>
                        <img src="{{ $allColorStatus[ProjectPoint::COLOR_STATUS_YELLOW] }}" />: 
                        Trạng thái cảnh báo bình thường
                        </p>
                    </dd>
                    
                    <dd>
                        <p>
                        <img src="{{ $allColorStatus[ProjectPoint::COLOR_STATUS_BLUE] }}" />: 
                        Trạng thái tốt
                        </p>
                    </dd>
                    
                    <dd>
                        <p>
                        <img src="{{ $allColorStatus[ProjectPoint::COLOR_STATUS_WHITE] }}" />: 
                        Do project không report hàng tuần.<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tuần trước PM không báo cáo, dashboard list hiện màu trắng bạc<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bắt đầu từ thứ 5 đến hết chủ nhật, PM không report thì project sẽ hiện màu trắng bạc ngoài dashboard list<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Baseline list hiện màu trắng bạc, tuần đó PM không report
                        </p>
                    </dd>
                    
                    <dd>
                        <p>
                        <img src="{{ $allColorStatus[ProjectPoint::COLOR_STATUS_GREY] }}" />: 
                        Do project quá end date mà vẫn chưa close
                        </p>
                    </dd>
                </div>
             </div>
        </div>
        <!-- end III -->
    </div>
</div>

@endsection