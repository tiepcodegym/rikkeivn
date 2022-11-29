<?php
use Rikkei\Core\View\View;

$tabTitleSub = trans('team::profile.edu_title_sub');
?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Education Place') }}<em>*</em></label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="edu[school_id]" class="select-search has-search" placeholder="{{ trans('team::profile.Education Place') }}" {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach($educationList as $eduId => $eduName)
                        <option{!!$employeeItemMulti->school_id == $eduId ? ' selected' : ''!!} value="{!!$eduId!!}">{{$eduName}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.From') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="edu[start_at]"
                       placeholder="yyyy-mm-dd" data-flag-type="date"
                       value="{{ $employeeItemMulti->start_at }}"
                       {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.To') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="edu[end_at]"
                       placeholder="yyyy-mm-dd" data-flag-type="date"
                       value="{{ $employeeItemMulti->end_at }}"
                       {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label required">{{ trans('team::view.Country') }}<em>*</em></label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="edu[country]" class="form-control select-search has-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($libCountry as $key => $label)
                        <option value="{{ $key }}"<?php 
                            if ($employeeItemMulti->country != null && $key == $employeeItemMulti->country): ?> selected<?php 
                            endif; ?>>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Province') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" name="edu[province]" class="form-control" 
                    placeholder="{{ trans('team::profile.Native city') }}" value="{{ $employeeItemMulti->province }}" 
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Faculty') }}<em>*</em></label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="edu[faculty_id]" class="select-search has-search" {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach($facultyList as $eduId => $eduName)
                        <option{!!$employeeItemMulti->faculty_id == $eduId ? ' selected' : ''!!} value="{!!$eduId!!}">{{$eduName}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Major') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <select name="edu[major_id]" class="select-search has-search" {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach($majorList as $eduId => $eduName)
                        <option{!!$employeeItemMulti->major_id == $eduId ? ' selected' : ''!!} value="{!!$eduId!!}">{{$eduName}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Quality') }}<em>*</em></label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="edu[quality]" class="select-search has-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($educationQualities as $key => $value)
                        <option value="{{ $key }}"<?php 
                            if ($employeeItemMulti->quality != null && $key == $employeeItemMulti->quality): ?> selected<?php 
                            endif; ?>>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Type') }}<em>*</em></label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="edu[type]" class="select-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($educationType as $key => $value)
                        <option value="{{ $key }}"<?php 
                            if ($employeeItemMulti->type != null && $key == $employeeItemMulti->type): ?> selected<?php 
                            endif; ?>>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <div class="col-md-4 control-label"></div>
            <div class="input-box col-md-8">
                <div class="checkbox">
                    <label>
                       <input type="checkbox" name="edu[is_graduated]"
                       value="1"
                       <?php if ($employeeItemMulti->is_graduated): ?> checked<?php endif;?>
                       data-checkbox-source="awarded" {!!$disabledInput!!}/>
                       {{ trans('team::profile.Graduated') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $disableGroup = $disabledInput || !$employeeItemMulti->is_graduated ? ' disabled' : '' ?>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label">{{ trans('team::profile.Degree') }}</label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="edu[degree]" class="select-search" data-checkbox-dist="awarded"
                    {!!$disableGroup!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($educationDegree as $key => $value)
                        <option value="{{ $key }}"<?php 
                            if ($employeeItemMulti->degree != null && $key == $employeeItemMulti->degree): ?> selected<?php 
                            endif; ?>>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Awarded date') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="edu[awarded_date]"
                       placeholder="yyyy-mm-dd" data-flag-type="date"
                       value="{{ $employeeItemMulti->awarded_date }}"
                       data-checkbox-dist="awarded"
                       {!!$disableGroup!!} />
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
                <textarea type="text" class="form-control" name="edu[note]" rows="6"
                    {!!$disabledInput!!}>{{ $employeeItemMulti->note }}</textarea>
            </div>
        </div>
    </div>
</div>
@endsection
