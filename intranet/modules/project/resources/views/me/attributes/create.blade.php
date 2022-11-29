@extends('layouts.default')

@section('title', trans('project::me.Monthly evaluation attributes'))

@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
@endsection

@section('content')

<?php
use Rikkei\Project\Model\MeAttribute;
?>

<div class="box box-primary">
    <div class="box-body">

        {!! Form::open(['method' => 'post', 'route' => 'project::eval.attr.store']) !!}
        
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{trans('project::me.Label')}} (*)</label>
                    {!! Form::text('label', old('label'), ['class' => 'form-control', 'placeholder' => trans('project::me.Label')]) !!}
                </div>
                
                <div class="form-group">
                    <label>{{trans('project::me.Name')}} (*)</label>
                    {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => trans('project::me.Name')]) !!}
                </div>
                
                <div class="form-group">
                    <label>{{trans('project::me.Group')}}</label>
                    {!! Form::select('group', MeAttribute::getGroupTypes(), old('group'), ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    <label>{{trans('project::me.Description')}} </label>
                     {!! Form::textarea('description', old('description'), ['class' => 'form-control', 'rows' => 3]) !!}
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{trans('me::view.Weight')}} (%) (*)</label>
                    {!! Form::number('weight', old('weight'), ['class' => 'form-control']) !!}
                </div>
                <div class="row">
                    <div class="col-xs-4">
                        <div class="form-group">
                            <label>{{trans('project::me.Min value')}}</label>
                            {!! Form::number('range_min', old('range_min'), ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="form-group">
                            <label>{{trans('project::me.Max value')}}</label>
                            {!! Form::number('range_max', old('range_max'), ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="form-group">
                            <label>{{trans('project::me.Step')}}</label>
                            {!! Form::number('range_step', old('range_step'), ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{trans('project::me.Order')}}</label>
                    {!! Form::number('order', old('order'), ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    <label>{{trans('project::me.Fill')}} </label>
                     {!! Form::checkbox('can_fill', old('can_fill'), old('can_fill')) !!}
                </div>
            </div>
        </div>
        
        <div class="form-group text-center">
            <div>
                <br />
                <a href="{{route('project::eval.attr.index')}}" class="btn btn-primary"><i class="fa fa-long-arrow-left"></i> {{trans('project::me.Back')}}</a>
                <button type="submit" class="btn-add"><i class="fa fa-save"></i> {{trans('project::me.Create')}}</button>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script type="text/javascript">
jQuery(document).ready(function ($) {
    selectSearchReload();
});
</script>
@endsection
