<?php
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
<p>{{ isset($data['task_title']) ? $data['task_title'] : 'Workorder' }} has been reviewed, please approve.</p>
<p>&nbsp;</p>
@if (isset($data['task_link']))
<p><a href="{{ $data['task_link'] }}" style="color: #15c">View detail</a></p>
@endif
@endsection
