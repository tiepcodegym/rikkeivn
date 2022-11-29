<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')
<p>{{ trans('resource::view.Hello :name,', ['name' => $data['recruiterName']]) }}</p>
<p>{!! trans('resource::view.The candidate <b>:name</b> has just been created then assign to you', ['name' => $data['candidateName']]) !!}. 
{{ trans('resource::view.Please visit the link below to view the information of candidates') }}</p>
<p><a href="{{ $data['urlToCandidate'] }}" target="_blank">{{ $data['urlToCandidate'] }}</a></p>
@endsection
