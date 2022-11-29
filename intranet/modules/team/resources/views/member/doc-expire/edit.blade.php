<?php
use Rikkei\Core\View\View; ?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Doc name') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="doc[name]"
                       placeholder="{{ trans('team::profile.Doc name') }}"
                       value="{{ $employeeItemMulti->name }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Doc place') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="doc[place]"
                       placeholder="{{ trans('team::profile.Doc place') }}"
                       value="{{ $employeeItemMulti->place }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Doc issue date') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="doc[issue_date]"
                       placeholder="yyyy-mm-dd" data-flag-type="date"
                       value="{{ $employeeItemMulti->issue_date }}"
                       {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Doc expire date') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="doc[expired_date]"
                       placeholder="yyyy-mm-dd" data-flag-type="date"
                       value="{{ $employeeItemMulti->expired_date }}"
                       {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::profile.Note') }}</label>
            <div class="input-box col-md-10">
                <textarea class="form-control" name="doc[note]" rows="6"
                    placeholder="{{ trans('team::profile.Note') }}"
                    {!!$disabledInput!!}>{{ $employeeItemMulti->note }}</textarea>
            </div>
        </div>
    </div>
</div>
@endsection
