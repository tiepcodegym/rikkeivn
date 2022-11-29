<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<?php
extract($data);
$st_content = '';
if (is_array($st_name) && count($st_name) > 0) {
    $st_content = 'của các anh/chị: ';
    if (count($st_content) == 1) {
        $st_content = 'của anh/chị: ';
    }
    $st_content .= '<ul>';
    foreach ($st_name as $name) {
        $st_content .= '<li><strong>'. $name .'</strong></li>';
    }
    $st_content .= '</ul>';
}
?>

<p>Xin chào Anh/Chị <strong>{{ $pm_name }}</strong>,</p>
<p>Tôi đã xem xét  đánh giá  team <strong>{{ $team_name }}</strong> + project <strong>{{$project_name}}</strong>  tháng {{ $time->format('Y-m') }} {!! $st_content !!}</p> 
<p>Và đưa ra một số ý kiến.</p>
<p>Đề nghị Anh/Chị vào hệ thống để xem xét lại.</p>
<p><a href="{{ $feedback_link }}">chi tiết</a></p>
<p>Trân trọng cảm ơn.</p>
<p><strong>{{ $leader_name }}</strong></p>
<br />
--------------------------------------------------------------
<br />
<p>DearMr/Ms  <strong>{{ $pm_name }}</strong>,</p>
<p>I have reviewed the monthly evaluation in {{ $time->format('F Y') }} and have some comments:</p>
<p>Please check & consider my comments. </p>
<p><a href="{{ $feedback_link }}">view detail</a></p>
<p>Thanks and regard,</p>
<p><strong>{{ $leader_name }}</strong></p>

@stop
