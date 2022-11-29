<?php
    $layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p>{{ trans('resource::view.Hello :name,', ['name' => $data['name']]) }}</p>
    <p>{{ trans('resource::view.Have new a resource request has been created and asign to you.') }}</p>
    @if (isset($data['title']))
        <p><b>{{ trans('resource::view.Title:') }}</b> {{ $data['title'] }}</p>
    @endif
    @if (isset($data['deadline']))
        <p><b>{{ trans('resource::view.Deadline:') }}</b>  {{ $data['deadline'] }}</p>
    @endif
    <p><a href="{{ $data['href'] }}">{{ trans('resource::view.View detail') }}</a></p>
@endsection