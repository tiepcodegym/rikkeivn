<?php
use Rikkei\Core\View\CoreUrl;
?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Hobby') }} <em>*</em></label>
            <div class="input-box col-md-8">
                <textarea class="form-control" placeholder="{{ trans('team::profile.Hobby') }}"
                          name="employeeHobby[hobby_content]" rows="6">{{ $employeeHobby->hobby_content }}</textarea>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Personal goal') }}</label>
            <div class="input-box col-md-8">
                <textarea class="form-control"
                          placeholder="{{ trans('team::profile.Personal goal') }}"
                          name="employeeHobby[personal_goal]"
                          rows="6"
                          {!!$disabledInput!!}>{{ $employeeHobby->personal_goal }}</textarea>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Gifted') }}</label>
            <div class="input-box col-md-8">
                <textarea class="form-control" name="employeeHobby[hobby]" 
                    placeholder="{{ trans('team::profile.Gifted') }}"
                    rows="6"
                   {!!$disabledInput!!}>{{ $employeeHobby->hobby }}</textarea>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Forte') }}</label>
            <div class="input-box col-md-8">
                <textarea class="form-control" name="employeeHobby[forte]" 
                       placeholder="{{ trans('team::profile.Forte') }}"
                       rows="6"
                       {!!$disabledInput!!}>{{ $employeeHobby->forte }}</textarea>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Weakness') }}</label>
            <div class="input-box col-md-8">
                <textarea class="form-control" name="employeeHobby[weakness]" 
                       placeholder="{{ trans('team::profile.Weakness') }}"
                       rows="6"
                       {!!$disabledInput!!}>{{ $employeeHobby->weakness }}</textarea>
            </div>
        </div>
    </div>
</div>
@endsection
@section('profile_js_custom')
    <script src="{{ CoreUrl::asset('team/js/view.js') }}"></script>
@endsection
