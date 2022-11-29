@extends('layouts.default')

<?php
    use Rikkei\Project\Model\MeAttribute;
    use Rikkei\Core\View\CoreUrl;
?>

@section('title', trans('project::me.Monthly evaluation attributes'))

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
    <style>
        label sup {
            color: red
        }
    </style>
@endsection

@section('content')
<div class="box box-primary">
    <div class="box-body">
        {!! Form::open(['method' => 'put', 'route' => ['project::eval.attr.update', $item->id]]) !!}
        <div class="row">
            <div class="col-sm-6">
                <div id="tab-text" class="tab-pane fade in active">
                    <ul class="nav nav-tabs text-content-tab">
                        <li class="{{ $lang == 'vi' ? 'active' : ''}}"><a data-toggle="tab" href="#vn">Vietnamese</a></li>
                        <li class="{{ $lang == 'jp' ? 'active' : ''}}"><a data-toggle="tab" href="#jp">Japanese</a></li>
                        <li class="{{ $lang == 'en' ? 'active' : ''}}"><a data-toggle="tab" href="#en">English</a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="vn" class="tab-pane fade {{ $lang == 'vi' ? 'in active' : ''}}">
                            @include('project::me.attributes.form_text', ['langCode' => 'vi'])
                        </div>
                        <div id="jp" class="tab-pane fade {{ $lang == 'jp' ? 'in active' : ''}}">
                            @include('project::me.attributes.form_text', ['langCode' => 'jp'])
                        </div>
                        <div id="en" class="tab-pane fade {{ $lang == 'en' ? 'in active' : ''}}">
                            @include('project::me.attributes.form_text', ['langCode' => 'en'])
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
            @include('project::me.attributes.form_value')
            
            <div class="form-group text-center">
                <div>
                    <br />
                    <a href="{{route('project::eval.attr.index')}}" class="btn btn-primary"><i class="fa fa-long-arrow-left"></i> {{trans('project::me.Back')}}</a>
                    <button type="submit" class="btn-add"><i class="fa fa-save"></i> {{trans('project::me.Save')}}</button>
                </div>
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
        jQuery(document).ready(function($) {
            selectSearchReload();
        });
    </script>
@endsection