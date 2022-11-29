@extends('layouts.default')

@section('title', trans('test_old::test.password'))

@section('content')

<div class="box box-primary">
    <div class="box-body">
        
        {!! Form::open(['method' => 'post', 'route' => 'test_old::admin.test.update_pass']) !!}
        
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
                {!! show_messes() !!}
                <div class="form-group">
                    <label>{{trans('test_old::test.password')}}</label>
                    {!! Form::text('password', $password, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
                </div>
            </div>
        </div>
        
        <div class="form-group text-center">
            <div>
                <button type="submit" class="btn-edit"><i class="fa fa-save"></i> {{trans('test_old::test.update')}}</button>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('tests_old/ad_src/main.css') }}">
@stop
@section('script')
<script>
    var _token = "{{csrf_token()}}";
    var textNoItem = '<?php echo trans('test::test.no_item'); ?>';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('tests_old/ad_src/main.js') }}"></script>
@stop
