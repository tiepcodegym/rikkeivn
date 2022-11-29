@extends('test_old::layouts.front')

@section('title', trans('test_old::test.auth'))

@section('content')

<div class="row">
    <div class="col-sm-4 col-sm-offset-4">
        
        {!! show_messes() !!}
        
        {!! Form::open(['method' => 'post', 'route' => 'test_old::auth', 'id' => 'form_auth']) !!}
        
        <h1 class="page-header text-center" style="border: none;"></h1>
        <div class="form-group">
            {!! Form::password('password', ['class' => 'form-control', 'placeholder' => trans('test_old::test.enter_password')]) !!}
            {!! error_field('password') !!}
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">{{trans('test_old::test.submit')}}</button>
        </div>
        
        {!! Form::close() !!}
    </div>
</div>

@stop


