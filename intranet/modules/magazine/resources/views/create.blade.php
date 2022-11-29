@extends('layouts.default')

@section('title', trans('magazine::view.Create Magazine'))

<!-- Styles -->
@section('css')
<link href="{{ asset('magazine/css/style.css') }}" rel="stylesheet" type="text/css" >
@endsection

@section('content')

<div class="box box-primary">
    <div class="box-body">
        <div id="error_box" class="hidden"></div>
        
        {!! Form::open(['method' => 'post', 'route' => 'magazine::save', 'files' => true, 'id' => 'frm_create_magazine', 'class' => 'imageloaderForm']) !!}    

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group row">
                        <label class="col-sm-3">{{ trans('magazine::view.Magazine name') }} (*)</label>
                        <div class="col-sm-9">
                            {!! Form::text('name', old('name'), ['id' => 'name', 'class' => 'form-control', 'placeholder' => trans('magazine::view.Magazine name')]) !!}
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6">
                    <label class="fileUpload btn btn-primary">
                        {{ trans('magazine::view.Add image') }} <i class="hidden uploading fa fa-spin fa-refresh"></i>
                        <input id="fileUpload" class="upload" type="file" accept="image/*" multiple/>
                    </label>
                    
                    <span><i>{{ trans('magazine::view.Drag image to range order, check image to choose background') }}</i></span>
                    <br />
                    {{ trans('magazine::view.Recommend size') }}
                </div>
            </div>
        
            <div id="uploadPreview"></div>
        
            <input type="hidden" name="selected" id="selected" value="">
            <div class="form-group text-center">
                <br />
                <p class="submit-alert hidden"><i class="fa fa-spin fa-refresh"></i> {{ trans('magazine::message.Processing image, please wait') }}</p>
                <a href="{{route('magazine::manage')}}" class="btn btn-primary"><i class="fa fa-long-arrow-left"></i> {{trans('magazine::view.Back')}}</a>
                <button id="submit" class="btn-add" type="submit"><i class="fa fa-save"></i> {{ trans('magazine::view.Create') }}</button>
            </div>
        
        {!! Form::close() !!}
        
    </div>
</div>

<input id="token" type="hidden" value="{{ Session::token() }}" />
<!-- Check value if press back button then reload page -->
<input type="hidden" id="refreshed" value="no">

@endsection

<!-- Script -->
@section('script')

<script src="/lib/js/exif.js"></script>
@include('magazine::template.script')

@endsection