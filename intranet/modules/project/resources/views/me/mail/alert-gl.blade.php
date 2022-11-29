<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<?php
extract($data);
$time = Carbon\Carbon::now()->subMonthNoOverflow();
$link = route('project::project.eval.list_by_leader');
?>

<p>Xin chào Anh/Chị <strong>{{ $leader_name }}</strong>,</p> 
<p>Đề nghị Anh/Chị <b>review</b> ME của tháng {{ $time->format('m/Y') }}, cho các dự án:<br /> {{ $project_names }}</p>
<p><a href="{{ $link }}">chi tiết</a></p>
<p>Trân trọng cảm ơn.</p>
<br />
-----------------------------------------

@stop
