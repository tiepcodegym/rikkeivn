@extends('layouts.default')

@section('title')
404 page
@endsection

@section('content')
<h4>{{ isset($message) ? $message : trans('core::view.Not found route') }}</h4>
@endsection

