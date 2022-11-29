<?php
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Employee; ?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Full name') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="relative[name]"
                       id="relative-name"
                       placeholder="{{ trans('team::profile.Full name') }}"
                       value="{{ $employeeItemMulti->name }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Emergency contact relationship') }}</label>
            <div class="input-box col-md-8">
                <select name="relative[relationship]" class="form-control select-search"
                    id="relative-relationship"
                    {!!$disabledInput!!}>
                    @foreach ($toOptionsRelation as $key => $label)
                        <option value="{{ $key }}"<?php
                            if ($employeeItemMulti->relationship !== null && (int) $key === (int) $employeeItemMulti->relationship): ?> selected<?php
                            endif; ?>>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Date of birth') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="relative[date_of_birth]"
                    data-flag-type="date" value="{{ $employeeItemMulti->date_of_birth }}"
                    placeholder="{{ trans('team::profile.Date of birth') }}"
                    {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.National') }}</label>
            <div class="input-box col-md-8">
                <select name="relative[national]" class="form-control select-search has-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($libCountry as $key => $label)
                        <option value="{{ $key }}"<?php
                            if ($employeeItemMulti->national != null && $key == $employeeItemMulti->national): ?> selected<?php
                            endif; ?>>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Passport') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="relative[id_number]"
                       placeholder="{{ trans('team::profile.Passport') }}"
                       value="{{ $employeeItemMulti->id_number }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Emergency contact mobile') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="relative[mobile]"
                       placeholder="{{ trans('team::profile.Emergency contact mobile') }}"
                       value="{{ $employeeItemMulti->mobile }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Emergency contact phone') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="relative[tel]"
                       placeholder="{{ trans('team::profile.Emergency contact phone') }}"
                       value="{{ $employeeItemMulti->tel }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Native addr') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="relative[address]"
                       placeholder="{{ trans('team::profile.Native addr') }}"
                       value="{{ $employeeItemMulti->address }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::view.Email') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="relative[email]"
                       placeholder="{{ trans('team::view.Email') }}"
                       value="{{ $employeeItemMulti->email }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Taxt code') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="relative[tax_code]"
                       placeholder="{{ trans('team::profile.Taxt code') }}"
                       value="{{ $employeeItemMulti->tax_code }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Carrer') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="relative[career]"
                       placeholder="{{ trans('team::profile.Carrer') }}"
                       value="{{ $employeeItemMulti->career }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::profile.Working place') }}</label>
            <div class="input-box col-md-10">
                <input type="text" class="form-control" name="relative[working_place]"
                       placeholder="{{ trans('team::profile.Working place') }}"
                       value="{{ $employeeItemMulti->working_place }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label"></label>
            <div class="input-box col-md-10">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="relative[is_dependent]" value="1"
                            id="relative-is_dependent" data-checkbox-source="dependent"
                            <?php if ($employeeItemMulti->is_dependent): ?> checked<?php endif;?>
                            {!!$disabledInput!!} />
                            {!!trans('team::profile.Is dependent')!!}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <?php
    $disabledDependent = !$disabledInput && $employeeItemMulti->is_dependent ? '' : ' disabled';
    ?>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label" title="{!!trans('team::profile.deduction_start')!!}">{{ trans('team::profile.Deduction start date') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="relative[deduction_start_date]"
                       placeholder="yyyy-mm-dd" data-checkbox-dist="dependent"
                       value="{{ $employeeItemMulti->deduction_start_date }}"
                       {!!$disabledDependent!!} data-flag-type="date" />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label" title="{!!trans('team::profile.deduction_end')!!}">{{ trans('team::profile.Deduction end date') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="relative[deduction_end_date]"
                       placeholder="yyyy-mm-dd" data-checkbox-dist="dependent"
                       value="{{ $employeeItemMulti->deduction_end_date }}"
                       {!!$disabledDependent!!} data-flag-type="date" />
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
                <textarea class="form-control" name="relative[note]"
                    placeholder="{{ trans('team::profile.Note') }}" rows="6"
                    {!!$disabledInput!!}>{{ $employeeItemMulti->note }}</textarea>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label"></label>
            <div class="input-box col-md-10">
                <div class="checkbox">
                    <label>
                       <input type="checkbox" name="relative[is_die]"
                        id="relative-is_die" value="1"
                       <?php if ($employeeItemMulti->is_die): ?> checked<?php endif;?>
                       {!!$disabledInput!!} />
                        {{ trans('team::profile.Is die')}}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
