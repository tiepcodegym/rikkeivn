<?php
    $layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>{{ trans('resource::view.Hello :name,', ['name' => $data['name']]) }}</p>
<p>{{ trans('resource::view.The request `:title` has expired or recruited enough. Please click on the link below to close the request', ['title' => $data['title']]) }}</p>
<p><a href="{{ $data['url'] }}">{{ trans('resource::view.Redirect to request detail') }}</a></p>
@endsection