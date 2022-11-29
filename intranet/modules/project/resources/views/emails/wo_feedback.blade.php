<?php
use Rikkei\Core\View\View as ViewCore;

$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
@if(isset($data['dear_name']))
<p>Dear {{ $data['dear_name'] }},</p>
@endif
<p>
    <strong>Project:</strong> {{ isset($data['project_name']) ? $data['project_name'] : '' }}<br />
    <strong>PM:</strong> {{ isset($data['project_pm']) ? $data['project_pm'] : '' }}<br />
    <strong>Group:</strong> {{ isset($data['project_group']) ? $data['project_group'] : '' }}
</p>
<p>{{ isset($data['task_title']) ? $data['task_title'] : 'Workorder' }} has been feedback, need you to change</p>
@if (isset($data['feedback_content']) && $data['feedback_content'])
<p><strong>Feedback content:</strong><br/>
{!! ViewCore::nl2br($data['feedback_content']) !!}</p>
@endif
<p>&nbsp;</p>
@if (isset($data['task_link']))
<p><a href="{{ $data['task_link'] }}" style="color: #15c">View detail</a></p>
@endif
@endsection
