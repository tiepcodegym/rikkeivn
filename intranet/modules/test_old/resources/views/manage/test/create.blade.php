@extends('layouts.default')

@section('title', trans('test_old::test.add_new_test'))

@section('content')

<div class="box box-primary">
    <div class="box-body">
        {!! show_messes() !!}

        {!! Form::open(['method' => 'post', 'route' => 'test_old::admin.test.store']) !!}
        
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{trans('test_old::test.name')}} (*)</label>
                    {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => trans('test_old::test.name')]) !!}
                </div>

                <div class="form-group">
                    <label>{{trans('test_old::test.link')}} (*)</label>
                    {!! Form::text('link', old('link'), ['class' => 'form-control', 'placeholder' => trans('test_old::test.link')]) !!}
                </div>

                <div class="form-group">
                    <label>{{trans('test_old::test.time')}} (*)</label>
                    {!! Form::number('time', old('time'), ['class' => 'form-control', 'min' => 0, 'placeholder' => trans('test_old::test.time')]) !!}
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{trans('test_old::test.test_type')}}</label>
                    <select name="type" class="form-control" id="box_type">
                        <option value="1">{{trans('test_old::test.subject')}}</option>
                        <option value="2">GMAT</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{trans('test_old::test.teams')}}</label>
                    <select id="cat_box" name="cat_id" class="form-control select-search">
                        <option value="">{{trans('test_old::test.selection')}}</option>
                        @if ($cats)
                        @foreach($cats as $cat)
                        <option value="{{$cat['value']}}">{{$cat['label']}}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group text-center">
            <div>
                <br />
                <a href="{{route('test_old::admin.test.index')}}" class="btn btn-primary"><i class="fa fa-long-arrow-left"></i> {{trans('test_old::test.back')}}</a>
                <button type="submit" class="btn-add"><i class="fa fa-save"></i> {{trans('test_old::test.add_new')}}</button>
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
