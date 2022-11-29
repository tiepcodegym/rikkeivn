<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<?php
extract($data);
$time = Carbon\Carbon::now()->startOfMonth()->subMonth();
$link = route('project::project.eval.index');
?>

<p>Xin chào Anh/Chị <strong>{{ $pm_name }}</strong>,</p> 
<p>Đã đến lúc làm đánh giá tháng {{ $time->format('m/Y') }}, cho các dự án sau:<br /> {{ $project_names }}</p>
<p><a href="{{ $link }}">chi tiết</a></p>
<p>Trân trọng cảm ơn.</p>
<br />
-----------------------------------------

@stop
