<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<?php
extract($data);
$action_text_vi = 'Tôi đã hoàn thành';
$action_text_en = 'I have completed';
if ($has_feedback) {
    $action_text_vi = 'Tôi đã chỉnh sửa và có ý kiến';
    $action_text_en = 'I have edited';
}
?>
<p>Kính gửi Anh/Chị <strong>{{ $leader_name }}</strong>,</p>
<p>{{$action_text_vi}} đánh giá của dự án sau:</p>
<ul>
    <li>Dự án: {{ $project_name }}</li>
    <li>Team: {{ $team_name }}</li>
    <li>Tháng {{ $time->format('m') }} năm {{ $time->format('Y') }}</li>
    <li><a href="{{ $review_link }}">chi tiết</a></li>
</ul>
<p>Đề nghị Anh/Chị <strong>{{ $leader_name }}</strong> xem xét, cho ý kiến và phê duyệt.</p>
<p>Trân trọng cảm ơn.</p>
<p><strong>{{ $pm_name }}</strong></p>
<br />
---------------------------------------
<br />
<p>Dear Mr/Ms <strong>{{ $leader_name }}</strong>,</p>
<p>{{$action_text_en}} the monthly evaluation of the project below.</p>
<ul style="list-style: ">
    <li>Project name: {{ $project_name }}</li>
    <li>Team: {{ $team_name }}</li>
    <li>Month year: {{ $time->format('F Y') }}</li>
    <li><a href="{{ $review_link }}">view detail</a></li>
</ul>
<p>Please help me review, comment and approve them.</p>
<p>Thanks and regard,</p>
<p><strong>{{ $pm_name }}</strong></p>

@stop
