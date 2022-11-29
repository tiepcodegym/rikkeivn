<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')
<p>{{ trans('resource::view.Hello :name,', ['name' => $data['recruiterName']]) }}</p>
<p>{!! trans('resource::view.The candidate <b>:name</b> has just been re-apply', ['name' => $data['candidateName']]) !!}. 
{{ trans('resource::view.Please visit the link below to update the information of candidates (years of experience, CV, ...)') }}</p>
<p><a href="{{ $data['urlToCandidate'] }}" target="_blank">{{ $data['urlToCandidate'] }}</a></p>
@endsection
