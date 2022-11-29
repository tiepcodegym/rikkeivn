<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<?php
extract($data);
$time = Carbon\Carbon::now()->startOfMonth()->subMonth();
$multi = 'các';
?>

<p>Xin chào Anh/Chị <strong>{{ $st_name }}</strong>,</p> 
<p>Đề nghị Anh/Chị kiểm tra và cho ý kiến nếu cần thiết bản đánh giá tháng {{ $time->format('m/Y') }}, cho {{ $multi }} dự án:<br /> {{ $project_names }}</p>
<p><a href="{{ route('project::project.profile.confirm') }}">chi tiết</a></p>
<p>Trân trọng cảm ơn.</p>
<br />
-----------------------------------------

@stop
