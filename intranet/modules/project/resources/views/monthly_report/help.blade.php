@extends('layouts.default')

@section('title')
{{ trans('project::view.Monthly report') }} help
@endsection

@section('content')

<div class="box box-primary">
    <div class="box-body">
        
        <div class="row" style="font-size: 15px; margin-bottom: 30px;">
            <div class="col-md-6">
                <h4 style="font-size: 20px;"><b>Để xuất file mẫu</b></h4>
                <p><a target="_blank" href="{{ route('project::monthly.report.index') }}">Trang chính</a></p>
                <p>Đầu tiên cần xuất file mẫu để điền dữ liệu vào</p>
                <ol style="margin-bottom: 20px;">
                    <li>Click nút "Export billable", hiển thị bảng chọn</li>
                    <li>Chọn team của mình</li>
                    <li>Chọn tháng bắt đầu đến tháng kết thúc (m-Y)</li>
                    <li>Nhấn nút export</li>
                </ol>
            </div>
            <div class="col-md-6" style="font-size: 15px;">
                <h4 style="font-size: 20px;"><b>Giải thích các trường trong file excel</b></h4>
                <ol>
                    <li>No: Số thứ tự <i>(gộp ô theo project)</i></li>
                    <li>Opportunity code: Mã do bên sale đặt <i>(gộp ô theo project)</i></li>
                    <li>Customer company: Công ty khách hàng <i>(gộp ô theo project)</i></li>
                    <li>Project name: Tên dự án <i>(gộp ô theo project)</i></li>
                    <li>Project code: Mã dự án <i>(gộp ô theo project)</i></li>
                    <li>Type: Loại dự án</li>
                    <li>Estimated: Ước tính effort</li>
                    <li>Member: Member tham gia dự án (mỗi member là 1 hàng)</li>
                    <li>Role: Role member trong dự án</li>
                    <li>Effort: Effort mỗi member trong dự án</li>
                    <li>Start: Ngày bắt đầu</li>
                    <li>End: Ngày kết thúc</li>
                    <li>
                        Các cột tháng m/Y
                        <ul>
                            <li>Allocation: effort allocation</li>
                            <li>Approved production cost (MM): số MM các D chốt với sale để làm dự án</li>
                            <li>Approved cost (cash): số cash các D chốt với sale để làm dự án (chuyển đổi MM * đơn giá = cash) <i>(gộp ô theo project)</i></li>
                            <li>Note: Note chú ý <i>(gộp ô theo project)</i></li>
                        </ul>
                    </li>
                </ol>
            </div>
        </div>
        
        <div>
            <?php $imgUrl = asset('project/images/d_report_sheet.png') ?>
            <a target="_blank" href="{{ $imgUrl }}" title="click xem kích thước đầy đủ"><img class="img-responsive" src="{{ $imgUrl }}"></a>
        </div>
        
    </div>
</div>

@endsection
