<?php
use Rikkei\Test\View\ViewTest;

$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')
<p>{{ trans('resource::view.Hello :name,', ['name' => $data['name']]) }}</p>
<p>{!! trans('resource::view.The candidate <b>:name</b> has just been updated status to preparing', ['name' => $data['candidateName']]) !!}</p> 

<p>{{ trans('resource::view.Please visit the link below to view detail candidates') }}</p>

<p><a href="{{ $data['urlToCandidate'] }}" target="_blank">{{ $data['urlToCandidate'] }}</a></p>
@endsection
