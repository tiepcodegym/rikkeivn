@extends('test_old::layouts.front')

@if ($test)
<?php
$title = $test->name;
$name = $test->name; 
if ($test->cat) {
    $title = $test->name .' - '.$test->cat->name;
    $name = $test->cat->name.' <i class="fa fa-angle-double-right"></i> '.$test->name; 
}
?>
@section('title', $title)

@section('body_class', 'test-page')
@section('content')

<h1 class="page-header single-title">
    <span class="name pull-left">{!! $name !!} ({{$test->time.' '.trans('test_old::test.minute')}})</span>
    <span class="test-time pull-right"><button type="button" class="btn btn-sm btn-primary btn-start">{{trans('test_old::test.start')}}</button> <i class="fa fa-clock-o"></i> {!! '<span class="minute">'.($test_time < 10 ? '0'.$test_time : $test_time).'</span>:<span class="second">00</span>' !!}</span>
</h1>

<div class="test-content">
    <iframe class="iframe-show" src="{{$test->link}}" frameborder="0"></iframe>
</div>

@stop

@else

@section('title', trans('test_old::test.test_not_found'))

@section('content')

<p class="text-center">{{trans('test_old::test.test_not_found')}}</p>

@stop

@endif


