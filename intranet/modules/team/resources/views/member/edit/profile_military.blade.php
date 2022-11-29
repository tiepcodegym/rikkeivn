<?php
use Rikkei\Core\View\View;
use Rikkei\Core\View\CoreUrl;
?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <div class="col-md-4 control-label"></div>
            <div class="input-box col-md-8">
                <div class="checkbox">
                    <label>
                       <input type="checkbox" name="employeeMilitary[is_service_man]"
                       id="employeeMilitary-is_service_man" value="1" data-checkbox-source="service"
                       <?php if ((int)($employeeMilitary->is_service_man)) : ?> checked<?php endif;?>
                       {!!$disabledInput!!} >
                       {{ trans('team::profile.Is service man') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $disableGroup = $disabledInput || !$employeeMilitary->is_service_man ? ' disabled' : '' ?>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Military join date') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control date-picker fill-disable" name="employeeMilitary[join_date]"
                   id="employeeMilitary-join_date" data-checkbox-dist="service"
                   placeholder="yyyy-mm-dd" data-flag-type="date"
                   value="{{ View::getOnlyDate($employeeMilitary->join_date) }}"
                   {!!$disableGroup!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div> 
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Military left date') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control date-picker fill-disable" name="employeeMilitary[left_date]"
                    id="employeeMilitary-left_date" data-checkbox-dist="service"
                    placeholder="yyyy-mm-dd" data-flag-type="date"
                    value="{{ View::getOnlyDate($employeeMilitary->left_date) }}"
                    {!!$disableGroup!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group form-group-select2 row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Military rank') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeMilitary[rank]" class="form-control select-search fill-disable"
                    id="employeeMilitary-rank" data-checkbox-dist="service"
                    {!!$disableGroup!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($rankOptions as $key => $value)
                        <option value="{{ $key }}"<?php 
                            if ($employeeMilitary->rank !== null && (int) $key === (int) $employeeMilitary->rank): ?> selected<?php 
                            endif; ?>>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">        
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label">{{ trans('team::profile.Military position') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeMilitary[position]" class="form-control select-search fill-disable"
                    id="employeeMilitary-position" data-checkbox-dist="service"
                    {!!$disableGroup!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($positionOptions as $key => $value)
                        <option value="{{ $key }}"<?php 
                            if ($employeeMilitary->position !== null && (int) $key === (int) $employeeMilitary->position): ?> selected<?php 
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
            <label class="col-md-4 control-label">{{ trans('team::profile.Military branch') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control fill-disable" name="employeeMilitary[branch]"
                    id="employeeMilitary-branch" data-checkbox-dist="service"
                    value="{{ $employeeMilitary->branch }}"
                    placeholder="{{ trans('team::profile.Military branch') }}"
                    {!!$disableGroup!!}>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">           
        <div class="form-group form-group-select2 row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Military arm') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeMilitary[arm]" class="form-control select-search fill-disable"
                    id="employeeMilitary-arm" data-checkbox-dist="service"
                    {!!$disableGroup!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($armOptions as $key => $value)
                        <option value="{{ $key }}"<?php 
                            if ($employeeMilitary->arm !== null && (int) $key === (int) $employeeMilitary->arm): ?> selected<?php 
                            endif; ?>>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::profile.Reason left') }}</label>
            <div class="input-box col-md-10">
                <input type="text" class="form-control fill-disable" name="employeeMilitary[left_reason]"
                   id="employeeMilitary-left_reason" data-checkbox-dist="service"
                   value="{{ $employeeMilitary->left_reason }}"
                   placeholder="{{ trans('team::profile.Reason') }}"
                   {!!$disableGroup!!} />
            </div>
        </div>
    </div>
</div>
<div class="row margin-top-20">
    <div class="col-md-6 form-horizontal">                  
        <div class="form-group row">
            <label class="col-md-4 control-label"></label>
            <div class="input-box col-md-8">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="employeeMilitary[is_wounded_soldier]"
                       id="employeeMilitary-is_wounded_soldier" data-checkbox-source="wounded"
                       value="1"
                       <?php if ((int)($employeeMilitary->is_wounded_soldier)) : ?> checked<?php endif;?>
                       {!!$disabledInput!!} />
                       {{ trans('team::profile.Is wounded soldier') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $disableGroup = $disabledInput || !$employeeMilitary->is_wounded_soldier ? ' disabled' : '' ?>
<div class="row">
    <div class="col-md-6 form-horizontal">                  
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Revolution join date') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control date-picker fill-disable" name="employeeMilitary[revolution_join_date]"
                    id="employeeMilitary-revolution_join_date" data-checkbox-dist="wounded"
                    value="{{ View::getOnlyDate($employeeMilitary->revolution_join_date) }}"
                    placeholder="yyyy-mm-dd" data-flag-type="date"
                    {!!$disableGroup!!} />
                    <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group form-group-select2 row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Wounded soldier level') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeMilitary[wounded_soldier_level]" class="form-control select-search fill-disable"
                        id="employeeMilitary-wounded_soldier_level" data-checkbox-dist="wounded"
                    {!!$disableGroup!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($level as $key => $value)
                        <option value="{{ $key }}"<?php 
                            if ($employeeMilitary->wounded_soldier_level !== null && (int) $key === (int) $employeeMilitary->wounded_soldier_level): ?> selected<?php 
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
            <label class="col-md-4 control-label">{{ trans('team::profile.Disability rate') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control number-percent num fill-disable" name="employeeMilitary[num_disability_rate]"
                   id="employeeMilitary-num_disability_rate"
                   min="0" max=100 data-checkbox-dist="wounded"
                   value="{{ $employeeMilitary->num_disability_rate }}"
                   placeholder="{{ trans('team::profile.Disability rate') }}"
                   {!!$disableGroup!!} />
                <i class="fa fa-percent form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">                  
        <div class="form-group row">
            <label class="col-md-4 control-label" for="employeeMilitary-is_martyr_regime">{{ trans('team::profile.Is martyr regime') }}</label>
            <div class="input-box col-md-8">
                <div class="checkbox">
                    <label>
                    <input type="checkbox" name="employeeMilitary[is_martyr_regime]" value="1"
                       id="employeeMilitary-is_martyr_regime" data-checkbox-dist="wounded" class="fill-disable"
                       <?php if ((int)($employeeMilitary->is_martyr_regime)) : ?> checked<?php endif;?>
                       {!!$disableGroup!!} />
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('profile_js_custom')
    <script src="{{ CoreUrl::asset('team/js/view.js') }}"></script>
@endsection