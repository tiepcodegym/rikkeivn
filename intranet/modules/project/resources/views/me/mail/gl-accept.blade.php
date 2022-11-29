<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<?php
extract($data);
if (!isset($approved_time)) {
    $confirm_time = \Carbon\Carbon::now();
} else {
    $confirm_time = \Carbon\Carbon::parse($approved_time);
}

$confirm_time->addDay();
?>

<p>Xin chào Anh/Chị <strong>{{ $st_name }}</strong>,</p> 
<p>Cảm ơn những đóng góp của bạn.</p>
<p>Tôi gửi Anh/Chị đánh giá  tháng {{ $time->format('m') }} năm {{ $time->format('Y') }} 
    @if (isset($project_name))
    trong dự án <strong>{{ $project_name }}</strong>
    @endif
</p>
<p>Đề nghị Anh/Chị kiểm tra chi tiết và cho ý kiến nếu cần thiết. </p>
<p>Nếu có thông tin cần thay đổi thông tin trong bản đánh giá tháng {{ $time->format('Y-m') }}, anh/chị vui lòng phản hồi lại trước 17h30 ngày {{ $confirm_time->format('d/m/Y') }} để thông tin được cập nhật chính xác </p>
<p><a href="{{ $accept_link }}">chi tiết</a></p>
<p>Trân trọng cảm ơn.</p>
<p>Ký tên</p>
<p><strong>{{ $pm_name }}</strong></p>
<br />
-----------------------------------------
<br />
<p>Dear Ms/Mr <strong>{{ $st_name }}</strong>,</p>
<p>Many thanks for your contribution.</p>
<p>I send you the evaluation 
    @if (isset($project_name))
    in project <strong>{{ $project_name }}</strong> 
    @endif
    in {{ $time->format('F Y') }}</p>
<p>Please check the evaluation carefully & give me your comments if necessary before 17h30 {{ $confirm_time->format('Y-m-d') }}.</p>
<p><a href="{{ $accept_link }}">view detail</a></p>
<p>Thanks and regard,</p>
<p><strong>{{ $pm_name }}</strong>.</p>

@stop
