<?php
use Rikkei\Core\Model\EmailQueue;
use Carbon\Carbon;

extract($data);
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p><strong>Có một số task chưa hoàn thành liên quan đến bạn sắp đến deadline:</strong></p>
@foreach($dataEmail as $key => $data)
<p></p>
<p><strong>{{ ($key+1) }}. {{ $data['title'] }}</strong></p>
<p>&emsp; Creator: {{ $data['creator_name'] . " (" . $data['creator_email'] .")" }}</p>
<p>&emsp; Assignee: {{ $data['name'] . " (" . $data['email'] .")" }}</p>
<p>&emsp; Date Deadline: {{ Carbon::parse($data['duedate'])->format('d/m/Y') }}</p>
<p>&emsp; Link: <a href="{{ URL::route('project::task.edit', ['id' => $data['id']]) }}" target="_blank">{{ URL::route('project::task.edit', ['id' => $data['id']]) }}</a></p>
@endforeach
@endsection
