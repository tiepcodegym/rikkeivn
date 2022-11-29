<?php
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Employee;

$tabTitleSub = trans('team::profile.onsite_title_sub');
?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Place') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="ons[place]"
                       placeholder="{{ trans('team::profile.Place') }}"
                       value="{{ $employeeItemMulti->place }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
     <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Start at') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control date-picker" name="ons[start_at]"
                   placeholder="yyyy-mm-dd" data-flag-type="date"
                   value="{{ View::getOnlyDate($employeeItemMulti->start_at) }}"
                   {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div> 
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.End at') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control date-picker" name="ons[end_at]"
                   placeholder="yyyy-mm-dd" data-flag-type="date"
                   value="{{ View::getOnlyDate($employeeItemMulti->end_at) }}"
                   {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div> 
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Reason') }}</label>
            <div class="input-box col-md-8">
                <textarea type="text" class="form-control" name="ons[reason]"
                    placeholder="{{ trans('team::profile.Reason') }}" rows="6"
                    {!!$disabledInput!!}>{{ $employeeItemMulti->reason }}</textarea>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Note') }}</label>
            <div class="input-box col-md-8">
                <textarea type="text" class="form-control" name="ons[note]"
                    placeholder="{{ trans('team::profile.Note') }}" rows="6"
                    {!!$disabledInput!!}>{{ $employeeItemMulti->note }}</textarea>
            </div>
        </div>
    </div>
</div>
@endsection
