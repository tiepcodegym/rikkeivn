<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>
Project <strong>{{ isset($data['project_name']) ? $data['project_name'] : '' }}</strong>
</p>
<p>
{{ isset($data['task_type']) ? $data['task_type'] : '' }}
<strong>{{ isset($data['task_title']) ? $data['task_title'] : '' }}</strong> reassign to you
</p>
<p>&nbsp;</p>
@if (isset($data['task_link']))
<p><a href="{{ $data['task_link'] }}" style="color: #15c">View detail</a></p>
@endif
@endsection