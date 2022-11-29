@extends('test::layouts.front')

@section('title', trans('test::test.auth'))

@section('messages')
<div class="row">
    <div class="col-sm-4 col-sm-offset-4">
        @include('messages.success')
        @include('messages.errors')
    </div>
</div>
@stop

@section('content')

<div class="row">
    <div class="col-sm-4 col-sm-offset-4">
        
        {!! Form::open(['method' => 'post', 'route' => 'test::auth', 'id' => 'form_auth']) !!}
        
        <h1 class="page-header text-center" style="border: none;"></h1>
        
        <div class="form-group">
            {!! Form::email('email', old('email'), ['class' => 'form-control', 'min' => 0, 'placeholder' => trans('test::test.input_email'), 'autocomplete' => 'off']) !!}
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">{{trans('test::test.submit')}}</button>
        </div>
        
        {!! Form::close() !!}
    </div>
</div>

@stop


