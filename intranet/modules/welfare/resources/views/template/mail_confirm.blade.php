<?php
use Rikkei\Core\Model\EmailQueue;

$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)
@section('content')
    @if (isset($data['receiver_name']) && $data['receiver_name'])
        <p>{{ trans('welfare::view.Dear :receiver_name,', ['receiver_name' => $data['receiver_name']]) }}</p>
    @else
        <p>{{ trans('welfare::view.Hello,') }}</p>
    @endif
    <p>{{ $data['title']}}</p>
    <a href="{{ $data['link'] }}">{{ trans('welfare::view.Link event') }}</a>
@endsection
