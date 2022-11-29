@extends('test_old::layouts.front')

@section('title', trans('test_old::test.select_test'))

@section('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<style>
    .select2-container .select2-selection--single{
        height: 35px; padding: 3px 8px;
    }
</style>
@stop

@section('content')
<h1></h1>
{!! show_messes() !!}

{!! Form::open(['method' => 'get', 'route' => 'test_old::post_select_test', 'id' => 'select_form']) !!}
    
    <div class="form-group row">
        <div class="col-sm-6">
            <label>{{trans('test_old::test.select_gmat')}}</label>
            <select name="gmat_id" id="gmat_box" class="form-control" data-slug="">
                <option value="">{{trans('test_old::test.selection')}}</option>
                @if (!$gmats->isEmpty())
                @foreach($gmats as $item)
                <option value="{{$item->id}}" data-slug="{{$item->slug}}">{{$item->name}}</option>
                @endforeach
                @endif
            </select>
            <div id="gmat_error" class="help-block alert alert-danger hidden">{{trans('test_old::validate.please_select_gmat')}}</div>
            {!! error_field('gmat_id') !!}
        </div>
    </div>
   
    <div class="form-group row">
        <div class="col-sm-6 form-group">
            <label>{{trans('test_old::test.select_subject')}}</label>
            <select id="cat_box" name="cat_id" class="form-control select-search">
                <option value="">{{trans('test_old::test.selection')}}</option>
                @if ($cats)
                @foreach($cats as $cat)
                <option value="{{$cat['value']}}">{{$cat['label']}}</option>
                @endforeach
                @endif
            </select>
        </div>
        <div class="col-sm-6 from-group">
            <label>{{trans('test_old::test.select_test')}}</label>
            <select id="test_box" name="test_id" class="form-control" data-slug="">
                <option value="">{{trans('test_old::test.selection')}}</option>
            </select>
            <div id="test_error" class="help-block alert alert-danger hidden">{{trans('test_old::validate.please_select_test_subject')}}</div>
            {!! error_field('test_id') !!}
        </div>
    </div>
    
    <div class="form-group row">
        <div class="col-sm-12 text-center"><br />
            <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> {{trans('test_old::test.start')}}</button>
        </div>
    </div>
    
{!! Form::close() !!}    

@stop


