<?php
use Rikkei\Core\View\CoreUrl;
?>

@extends('layouts.default')

@section('title')
    
    {{ trans('team::view.Checkpoint.Preview.Title') }}
    
@endsection

@section('content')

<div class="box box-primary preview-page">
    <div class="body-padding">
        <h3 class="box-title">
            {{ trans('team::view.Checkpoint.Preview.Url') }}
        </h3>
        {!! trans('team::view.Checkpoint.Preview.Link make info') !!}
        <h3 id="link-make" class="wrap">{{$hrefMake}}</h3>
        <div class="text-align-center"><a href="{{$hrefUpdateCss}}" class="btn btn-primary btn-update-css">Back</a></div>
    </div>
    
    
    <!-- PREVIEW WELCOME PAGE -->
    <hr />
    <div class="welcome-body body-padding">
        <h3 class="box-title">{{ trans('team::view.Checkpoint.Preview.Title') }}</h3>
        <h4 class="preview-title">{{ trans('team::view.Checkpoint.Preview.Welcome title')}}</h4>
        <div class="logo-rikkei">
            <img src="{{ URL::asset('common/images/logo-rikkei.png') }}">
        </div>
        <div class="welcome-header">
            <h2 class="welcome-title <?php if($checkpoint->checkpoint_type_id == 1){ echo 'color-blue'; } ?>">{{ trans('team::view.Checkpoint.Welcome.Title') }}</h2>
        </div>
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span12">
                    <div >
                        <p class="welcome-line">{!! trans('team::view.Checkpoint.Welcome.Content') !!}</p>
                    </div>
                </div>
            </div>
            <div class="row-fluid ">
                <div class="css-make-info">
                    
                    <div>
                        <div class="customer-name-title">{{ trans('team::view.Checkpoint.Welcome.Employee name title') . trans('team::view.Checkpoint.Welcome.Employee name')}}</div>
                        <div >
                            <button type="button" class="btn btn-default btn-to-make <?php if($checkpoint->checkpoint_type_id == 1){ echo 'bg-color-blue'; } ?>" name="submit">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="clear-both"></div>
    
    
    <!-- PREVIEW MAKE CSS PAGE -->
    <hr />
    <div class="make-css-page checkpoint-make-page body-padding">
        <h3 class="box-title">{{ trans('team::view.Checkpoint.Preview.Title') }}</h3>
        <h4 class="preview-title">{{ trans('team::view.Checkpoint.Preview.Make title')}}</h4>
        <div class="row">
            <div class="col-md-12">
                @include('team::checkpoint.include.checkpoint_form', ['isPreview' => true])
            </div>
        </div>
    </div>
</div>
@endsection
<!-- Styles -->
@section('css')
<link href="{{ CoreUrl::asset('team/css/style.css') }}" rel="stylesheet" type="text/css" />
@endsection

<!-- Script -->
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/script.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/checkpoint/preview.js') }}"></script>
@endsection