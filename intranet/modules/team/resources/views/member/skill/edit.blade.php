<?php
use Rikkei\Core\View\View; ?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Skill type') }}<em>*</em></label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="skill[type]" class="select-search" id="skills-type"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($skillTypes as $key => $name)
                        <option value="{{ $key }}"<?php 
                            if ($key == $employeeItemMulti->type): ?> selected<?php 
                            endif; ?>>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Skill name') }}<em>*</em></label>
            <div class="input-box col-md-8 fg-valid-custom">
                <input type="text" class="form-control" placeholder="PHP" 
                    value="{{ $employeeItemMulti->name }}" 
                    name="skill[name]" {!!$disabledInput!!}
                    data-autocomplete-dom="true"
                    data-ac-url="{!!URL::route('team::search.autocomplete.skill')!!}"/>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label required">{{ trans('team::view.Level') }}<em>*</em></label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="skill[level]" class="select-search" id="skills-level"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @for ($i = 1; $i < 6; $i++)
                        <option value="{{ $i }}"<?php 
                            if ($i == $employeeItemMulti->level): ?> selected<?php 
                            endif; ?>>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::view.Experience') }}</label>
            <div class="input-box col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" class="form-control" placeholder="{{ trans('team::profile.Year') }}" 
                            value="{{ $employeeItemMulti->exp_y }}"
                            name="ski_mo[exp_y]" {!!$disabledInput!!} />
                        <i class="fa form-control-feedback margin-right-20"><b>Y</b></i>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" placeholder="{{ trans('team::profile.Month') }}" 
                            value="{{ $employeeItemMulti->exp_m }}"
                            name="ski_mo[exp_m]" {!!$disabledInput!!} />
                        <i class="fa form-control-feedback margin-right-20"><b>M</b></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after_content_profile')
<p><strong>* Note:</strong> Cấp độ</p>
<ol>
    <li>{!!trans('team::cv.note level 1', [], '', 'vi')!!}</li>
    <li>{!!trans('team::cv.note level 2', [], '', 'vi')!!}</li>
    <li>{!!trans('team::cv.note level 3', [], '', 'vi')!!}</li>
    <li>{!!trans('team::cv.note level 4', [], '', 'vi')!!}</li>
    <li>{!!trans('team::cv.note level 5', [], '', 'vi')!!}</li>
</ol>
@endsection