<?php
use Rikkei\Core\Model\EmailQueue;

$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>
Task <strong>{{ isset($data['task_title']) ? $data['task_title'] : '' }}</strong> changed
	@if(isset($data['participant'])  && !$data['participant'])
		and assigned to you
	@endif
</p>
<p>&nbsp;</p>
@if (isset($data['task_link']))
<p><a href="{{ $data['task_link'] }}" style="color: #15c">View detail</a></p>
@endif
@endsection