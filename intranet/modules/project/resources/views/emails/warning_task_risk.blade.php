<?php
use Rikkei\Core\Model\EmailQueue;
use Carbon\Carbon;
use Rikkei\Project\Model\Task;

extract($data);
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p><strong>[Tasks Warning]Có một số task chưa hoàn thành liên quan đến bạn cần được xử lí:</strong></p>
@foreach($dataEmail as $key => $data)
<p></p>
<p><strong>{{ ($key+1) }}. {{ $data['content_task'] }}</strong></p>
<p>&emsp; Creator: {{ $data['creator_name'] . " (" . $data['creator_email'] .")" }}</p>
<p>&emsp; Assignee: {{ $data['name'] . " (" . $data['email'] .")" }}</p>
<p>&emsp; Created at: {{ Carbon::parse($data['created_at'])->format('d/m/Y') }}</p>
@if ($data['type'] == Task::TYPE_RISK)
<p>&emsp; Link: <a href="{{ URL::route('project::task.edit', ['id' => $data['id']]) }}" target="_blank">{{ URL::route('project::task.edit', ['id' => $data['id']]) }}</a></p>
@else
<p>&emsp; Link: <a href="{{ URL::route('project::report.risk.detail', ['id' => $data['id']]) }}" target="_blank">{{ URL::route('project::report.risk.detail', ['id' => $data['id']]) }}</a></p>
@endif
@endforeach
@endsection
