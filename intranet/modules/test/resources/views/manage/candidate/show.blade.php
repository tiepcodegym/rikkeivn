@extends('layouts.default')

@section('title', trans('test::test.candidate_infor'))

<?php
use Rikkei\Core\View\CoreUrl;
?>
@section('css')
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('tests/css/main.css') }}" />
@stop

@section('body_class', 'list-cdd-page')

@section('content')

<div class="box box-info">
    <div class="box-body">

        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <h3>{{ trans('test::test.candidate_infor') }}</h3>
                
                {!! Form::open(['method' => 'put', 'route' => ['test::candidate.admin.update', $item->id], 'id' => 'form_infor']) !!}
                
                @foreach($fields as $key => $attrs)
                <div class="form-group">
                    <label>{{ $attrs['label'] }} {!! $attrs['required'] ? '<em>*</em>' : '' !!}</label>
                    <input name="{{ $attrs['key'] }}" {{ isset($edit) ? '': 'disabled' }} class="form-control no-resize" value="{{ old($attrs['key']) !== null ? old($attrs['key']) : $item->{$attrs['key']} }}">
                    {!! error_field($attrs['key']) !!}
                </div>
                @endforeach
                
                <div class="text-center">
                    @if (isset($edit))
                    <button type="submit" class="btn-edit"><i class="fa fa-save"></i> {{ trans('test::test.update') }}</button>
                    @endif
                    <a class="btn btn-primary" href="{{route('test::candidate.admin.index')}}">{{trans('test::test.back')}} <i class="fa fa-long-arrow-right"></i></a>
                </div>
                
                {!! Form::close() !!}
            </div>
        </div>

    </div>
</div>

@stop

@section('script')
<script src="{{ CoreUrl::asset('common/js/script.js') }}"></script>

<script>
    numberFormat($('input[name="salary"]'));
</script>

@stop
