<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Project\Model\Task;

$labelTask = Task::statusLabel();
extract($data);
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p><strong>Dear {{ $dear_name }},</strong></p>
<p>Có một số task Customer feedback chưa resolved liên quan đến bạn!</p>
@foreach($dataEmail as $key => $data)
<p></p>
<p><strong>{{ ($key+1) }}.</strong><a href="{{ route('project::task.edit', ['id' => $data->id]) }}" target="_blank"> {{ $data->title }}</a></p>
<p>&emsp; <strong>Project:</strong> {{ $data->name }}</p>
<p>&emsp; <strong>PM:</strong> {{ $data->manager_name }} ( {{ preg_replace('/@.*/', '', $data->manager_email) }} )</p>
@if ($labelTask[$data->status])
<p>&emsp; <strong>Status task:</strong> {{ $labelTask[$data->status] }}</p>
@endif
<p>&emsp; <strong>Link:</strong> <a href="{{ route('project::task.edit', ['id' => $data->id]) }}" target="_blank">{{ route('project::task.edit', ['id' => $data->id]) }}</a></p>
@endforeach
@endsection
