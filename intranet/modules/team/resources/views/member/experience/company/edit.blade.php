<?php
use Rikkei\Core\View\View; ?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::view.Name') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" placeholder="{{ trans('team::view.Name') }}" 
                    value="{{ $employeeItemMulti->name }}"
                    name="com[name]" {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::view.Position') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" placeholder="{{ trans('team::view.Position') }}" 
                    value="{{ $employeeItemMulti->position }}"
                    name="com[position]" {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::view.Address') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" placeholder="{{ trans('team::view.Address') }}" 
                    value="{{ $employeeItemMulti->address }}"
                    name="com[address]" {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::view.From') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="com[start_at]"
                       placeholder="yyyy-mm-dd" data-flag-type="date"
                       value="{{ $employeeItemMulti->start_at }}"
                       {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::view.To') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="com[end_at]"
                       placeholder="yyyy-mm-dd" data-flag-type="date"
                       value="{{ $employeeItemMulti->end_at }}"
                       {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
</div>
@endsection
