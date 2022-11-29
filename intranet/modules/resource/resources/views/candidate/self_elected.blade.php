@extends('layouts.guest')
<?php

use Carbon\Carbon;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Resource\View\getOptions;
?>


<?php
if (isset($candidate) && $candidate) {
    $checkEdit = true;
    $urlSubmit = route('resource::candidate.postCreate', ['id' => $candidate->id]);
} else {
    $checkEdit = false;
    $urlSubmit = route('resource::candidate.postSelfElected');
}
?>

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.3.8/css/AdminLTE.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.3.8/css/skins/_all-skins.min.css" />
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">    
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('resource/css/resource.css') }}" />
<style>
    .select2-selection {
        height: 34px !important;
    }
    .select2-selection__arrow {
        height: 32px !important;
    }
</style>
@endsection

@section('content')
<div class="css-create-page request-create-page">
    <div class="css-create-body">
        <div class="box box-primary padding-bottom-30">
            <form id="form-create-candidate" method="post" action="{{$urlSubmit}}" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group position-relative form-label-left">
                            <label for="fullname" class="col-sm-3 control-label">{{trans('resource::view.Candidate.Create.Fullname')}}</label>
                            <div class="col-md-9">
                                <span>                                  
                                    <input type="text" id="fullname" name="fullname" class="form-control" tabindex="1" value="{{$checkEdit ? $candidate->fullname : ''}}" />
                                </span>
                                &nbsp;<span class="required" aria-required="true">(*)</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group position-relative form-label-left">
                            <label for="email" class="col-sm-3 control-label">{{trans('resource::view.Candidate.Create.Email')}}</label>
                            <div class="col-md-9">
                                <span>                                  
                                    <input type='text' class="form-control" id="email" name="email"  tabindex=2 value="{{$checkEdit ? $candidate->email: ''}}" />
                                </span>
                                &nbsp;<span class="required" aria-required="true">(*)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group position-relative form-label-left">
                            <label for="birthday" class="col-sm-3 control-label">{{trans('resource::view.Candidate.Create.Birthday')}}</label>
                            <div class="col-md-9">
                                <span>                                  
                                    <input type='text' class="form-control date" id="birthday" name="birthday" data-provide="datepicker" placeholder="YYYY-MM-DD" tabindex=3 value="{{$checkEdit ? $candidate->birthday: ''}}" />
                                </span>
                                &nbsp;<span class="required" aria-required="true">(*)</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group position-relative form-label-left">
                            <label for="mobile" class="col-sm-3 control-label">{{trans('resource::view.Candidate.Create.Mobile')}}</label>
                            <div class="col-md-9">
                                <span>                                  
                                    <input type='text' class="form-control" id="mobile" name="mobile"  tabindex=4 value="{{$checkEdit ? $candidate->mobile: ''}}" />
                                </span>
                                &nbsp;<span class="required" aria-required="true">(*)</span>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="row">
                    
                    <div class="col-md-6">
                        <div class="form-group position-relative form-label-left">
                            <label for="position_apply" class="col-sm-3 control-label">{{trans('resource::view.Candidate.Create.Position apply')}}</label>
                            <div class="col-md-9">
                                <span>                                  
                                    <select id="position_apply" name="position_apply" class="form-control width-93 select2-hidden-accessible select-search" tabindex='5' >
                                        @foreach($roles as $key => $option)
                                        <option value="{{ $key }}" @if($checkEdit && $key == $candidate->position_apply) selected @endif>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </span>
                                &nbsp;<span class="required" aria-required="true">(*)</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group position-relative form-label-left">
                            <label for="experience" class="col-sm-3 control-label">{{trans('resource::view.Candidate.Create.Experience')}}</label>
                            <div class="col-md-9">
                                <span>                                  
                                    <input id="experience" name="experience" type="number" class="form-control num" min="0" value="{{$checkEdit ? $candidate->experience: '0'}}" tabindex='6' />
                                </span>
                                &nbsp;<span class="required" aria-required="true">(*)</span>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group position-relative form-label-left">
                            <label for="university" class="col-sm-3 control-label">{{trans('resource::view.Candidate.Create.University')}}</label>
                            <div class="col-md-9">
                                <span>                                  
                                    <textarea maxlength="500" rows="4" class="form-control col-md-9" id="university" name="university" tabindex=7>{{$checkEdit ? $candidate->university: ''}}</textarea>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group position-relative form-label-left">
                            <label for="certificate" class="col-sm-3 control-label">{{trans('resource::view.Candidate.Create.Certificate')}}</label>
                            <div class="col-md-9">
                                <span>                                  
                                    <textarea maxlength="500" rows="4" class="form-control col-md-9" id="certificate" name="certificate" tabindex=8>{{$checkEdit ? $candidate->certificate: ''}}</textarea>
                                </span>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group position-relative form-label-left">
                            <label for="languages" class="col-sm-3 control-label">{{trans('resource::view.Candidate.Create.Languages')}}</label>
                            <div class="col-md-9">
                                <span>                                  
                                    <select id="languages" name="languages[]" class="form-control width-93" multiple="multiple" tabindex='9'>
                                        @foreach($langs as $option)
                                        <option value="{{ $option->id }}" @if(isset($allLangs) && in_array($option->id, $allLangs)) selected @endif>{{ $option->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" style="opacity: 0; position: absolute" value="{{isset($allLangs) ? 1 : ''}}" id="chk_lang" name="chk_lang" />
                                </span>
                                &nbsp;<span class="required" aria-required="true">(*)</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group position-relative form-label-left">
                            <label for="programs" class="col-sm-3 control-label">{{trans('resource::view.Candidate.Create.Programming languages')}}</label>
                            <div class="col-md-9">
                                <span>                                  
                                    <select id="programs" name="programs[]" class="form-control width-93" multiple="multiple" tabindex='10'>
                                        @foreach($programs as $option)
                                        <option value="{{ $option->id }}" @if(isset($allProgrammingLangs) && in_array($option->id, $allProgrammingLangs)) selected @endif>{{ $option->name }}</option>
                                        @endforeach
                                    </select>
                                </span>
                                &nbsp;<span class="required" aria-required="true">(*)</span>
                                <input type="text" style="left:0; opacity: 0; position: absolute" value="{{isset($allProgrammingLangs) ? 1 : ''}}" id="chk_pro" name="chk_pro" />
                            </div>
                        </div>
                    </div>
                </div>
               
                <div class="row">
                    <div class="col-md-12 align-center margin-top-40">
                        <button type="submit" class="btn btn-primary">{{trans('resource::view.Request.Create.Submit')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('common/js/script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ asset('resource/js/candidate/create.js') }}"></script>
<script>
        $('#request_id').select2();
        $('#languages').multiselect();
        $('#programs').multiselect();
        $('#role').select2();
        $('#channels').select2();
        $('#recruiter').select2();
        jQuery(document).ready(function($) {
            selectSearchReload();
        });</script>
<script>
//define texts
var requiredText = '{{trans('resource::message.Required field')}}';
var invalidEmail = '{{trans('resource::message.Email is invalid')}}';
</script>
@endsection