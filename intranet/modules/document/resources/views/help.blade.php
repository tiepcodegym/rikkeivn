@extends('layouts.default')

@section('title', trans('doc::view.Document help'))

@section('content')

<div class="box box-primary">
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                {!! trans('doc::view.guide_document') !!}
            </div>
            
            <div class="col-md-6">
                <h4><b>{{ trans('doc::view.Created document') }}</b></h4>
                {!! trans('doc::view.guide_create_doc') !!}
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                {!! trans('doc::view.guide_publish_document') !!}
            </div>
        </div>
    </div>
</div>

@stop


