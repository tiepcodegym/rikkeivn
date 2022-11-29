<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<?php
extract($data);
?>

<p>Kính chào <strong>{{ $pm_name }}</strong>,</p>
<p>Tôi đã xem xét  đánh giá 
    @if ($project_name)
    <strong>{{ $project_name }}</strong>, <strong>{{$team_name}}</strong>  
    @endif
    tháng {{$time->format('m')}} năm {{ $time->format('Y') }} của tôi và đưa ra một số ý kiến phản hồi chi tiết trong bản đánh giá. </p>
<p><a href="{{ $feedback_link }}">chi tiết</a></p>
<p>Đề nghị Anh/Chị xem xét lại và phản hồi kết quả cho tôi.</p>
<p>Trân trọng cảm ơn.</p>
<p><strong>{{ $st_name }}</strong></p>
<br />
--------------------------------------------------------------
<br />
<p>Dear Ms/Mr <strong>{{ $pm_name }}</strong>,</p>
<p>I have checked the evaluation in {{ $time->format('F Y') }} and had some feedbacks, please help me consider & confirm them.</p>
<p><a href="{{ $feedback_link }}">view detail</a></p>
<p>Thanks and regard,</p>
<p><strong>{{ $st_name }}</strong>.</p>

@stop
