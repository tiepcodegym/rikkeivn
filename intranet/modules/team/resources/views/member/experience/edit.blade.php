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
                    name="exp[name]" {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label">{{ trans('team::profile.Of company') }}</label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="exp[company_id]" class="form-control select-search has-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($employeeCompany as $item)
                    <option value="{{ $item->id }}"<?php 
                            if ($employeeItemMulti->company_id != null && $item->id == $employeeItemMulti->company_id): ?> selected<?php 
                            endif; ?>>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Position location') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" placeholder="{{ trans('team::profile.Position location') }}" 
                    value="{{ $employeeItemMulti->position }}"
                    name="exp[position]" {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Customer') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" placeholder="{{ trans('team::profile.Customer') }}" 
                    value="{{ $employeeItemMulti->customer }}"
                    name="exp[customer]" {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Start') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="exp[start_at]"
                       placeholder="yyyy-mm-dd" data-flag-type="date"
                       value="{{ $employeeItemMulti->start_at }}"
                       {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.End') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="exp[end_at]"
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
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Number member') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" placeholder="{{ trans('team::profile.Number member') }}" 
                    value="{{ $employeeItemMulti->no_member }}"
                    name="exp[no_member]" {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Period') }}</label>
            <div class="input-box col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" class="form-control" placeholder="{{ trans('team::profile.Year') }}" 
                            value="{{ $employeeItemMulti->period_y }}"
                            name="ex_mo[per_y]" {!!$disabledInput!!} />
                        <i class="fa form-control-feedback margin-right-20"><b>Y</b></i>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" placeholder="{{ trans('team::profile.Month') }}" 
                            value="{{ $employeeItemMulti->period_m }}"
                            name="ex_mo[per_m]" {!!$disabledInput!!} />
                        <i class="fa form-control-feedback margin-right-20"><b>M</b></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group form-group-select2 row">
            <label class="col-md-4 control-label">{{ trans('team::view.OS') }}</label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="ex_mo[os][]" data-select2-dom="1"  multiple="multiple" {!!$disabledInput!!}
                    data-select2-url="{!!route('tag::search.tag.select2', ['fieldCode' => 'os'])!!}">
                    @foreach ($projExperOs as $item)
                        <option value="{{ $item->os_id }}" selected>{{ $item->os_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group form-group-select2 row">
            <label class="col-md-4 control-label">{{ trans('team::view.Database') }}</label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="ex_mo[db][]" data-select2-dom="1"  multiple="multiple" {!!$disabledInput!!}
                    data-select2-url="{!!route('tag::search.tag.select2', ['fieldCode' => 'database'])!!}">
                    @foreach ($projExperDb as $item)
                        <option value="{{ $item->db_id }}" selected>{{ $item->db_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group form-group-select2 row">
            <label class="col-md-4 control-label">{{ trans('team::view.Language') }}</label>
            <div class="input-box col-md-8 fg-valid-custom">
                <select name="ex_mo[lang][]" data-select2-dom="1"  multiple="multiple" {!!$disabledInput!!}
                    data-select2-url="{!!route('tag::search.tag.select2', ['fieldCode' => 'language'])!!}">
                    @foreach ($projExperLangs as $item)
                        <option value="{{ $item->lang_id }}" selected>{{ $item->lang_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::view.Environment') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" placeholder="{{ trans('team::view.Environment') }}" 
                    value="{{ $employeeItemMulti->env }}"
                    name="exp[env]" {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::view.Other Tech') }}</label>
            <div class="input-box col-md-8">
                <textarea name="exp[other_tech]" class="form-control" {!!$disabledInput!!} rows="5"
                    placeholder="{{ trans('team::view.Other Tech') }}">{{ $employeeItemMulti->other_tech }}</textarea>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Responsible') }}</label>
            <div class="input-box col-md-8">
                <textarea name="exp[responsible]" class="form-control" {!!$disabledInput!!} rows="5"
                    placeholder="{{ trans('team::profile.Responsible') }}">{{ $employeeItemMulti->responsible }}</textarea>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::view.Description') }}</label>
            <div class="input-box col-md-10">
                <textarea name="exp[description]" class="form-control" {!!$disabledInput!!} rows="5"
                    placeholder="{{ trans('team::view.Description') }}">{{ $employeeItemMulti->description }}</textarea>
            </div>
        </div>
    </div>
</div>
@endsection
