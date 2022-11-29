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
                       <input type="checkbox" name="employeePolitic[is_party_member]"
                       id="employeePolitic-is_party_member" value="1"
                       <?php if ((int)($employeePolitic->is_party_member)) : ?> checked<?php endif;?>
                       {!!$disabledInput!!} data-checkbox-source="party" />
                       {{ trans('team::profile.Is party member') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $disableGroup = $disabledInput || !$employeePolitic->is_party_member ? ' disabled' : '' ?>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Party join date') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control date-picker fill-disable" name="employeePolitic[party_join_date]"
                       id="employeePolitic-party_join_date"
                       placeholder="yyyy-mm-dd" data-checkbox-dist="party"
                       value="{{View::getOnlyDate($employeePolitic->party_join_date)}}" data-flag-type="date"
                       {!!$disableGroup!!}
                       />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group form-group-select2">
            <label class="col-md-4 control-label">{{ trans('team::profile.Position') }}</label>
            <div class="input-box col-md-8">
                <select name="employeePolitic[party_position]" class="form-control select-search fill-disable"
                    id="employeePolitic-party_position" data-checkbox-dist="party"
                    {!!$disableGroup!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($partyOptions as $key => $value)
                        <option value="{{ $key }}"<?php
                            if ($employeePolitic->party_position !== null && (int) $key === (int) $employeePolitic->party_position): ?> selected<?php
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
            <label class="col-md-2 control-label">{{ trans('team::profile.Party join place') }}</label>
            <div class="input-box col-md-10">
                <input typpe="text" class="form-control fill-disable" name="employeePolitic[party_join_place]"
                    id="employeePolitic-party_join_place" data-checkbox-dist="party"
                    value="{{ $employeePolitic->party_join_place }}"
                    placeholder="{{ trans('team::profile.Party join place') }}"
                    {!!$disableGroup!!} />
            </div>
        </div>
    </div>
</div>

<div class="row margin-top-20">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <div class="col-md-4 control-label"></div>
            <div class="input-box col-md-8">
                <div class="checkbox">
                    <label>
                       <input type="checkbox" name="employeePolitic[is_union_member]"
                       id="employeePolitic-is_union_member" data-checkbox-source="union" value="1"
                       <?php if ((int)($employeePolitic->is_union_member)) : ?> checked<?php endif;?>
                       {!!$disabledInput!!} />
                       {{ trans('team::profile.Is union member') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $disableGroup = $disabledInput || !$employeePolitic->is_union_member ? ' disabled' : '' ?>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Union join date') }}</label>
            <div class="input-box col-md-8 input-group-table">
                 <input type="text" class="form-control date-picker input-group-table date fill-disable" name="employeePolitic[union_join_date]"
                        id="employeePolitic-union_join_date"
                        placeholder="yyyy-mm-dd" data-flag-type="date" data-checkbox-dist="union"
                        value="{{ View::getOnlyDate($employeePolitic->union_join_date) }}"
                        {!!$disableGroup!!} />
                        <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
             </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group form-group-select2">
            <label class="col-md-4 control-label">{{ trans('team::profile.Position') }}</label>
            <div class="input-box col-md-8">
                <select name="employeePolitic[union_poisition]" class="form-control select-search fill-disable"
                    id="employeePolitic-union_poisition" data-checkbox-dist="union"
                    {!!$disableGroup!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($unionOptions as $key => $value)
                        <option value="{{ $key }}"<?php
                            if ($employeePolitic->union_poisition !== null && (int) $key === (int) $employeePolitic->union_poisition): ?> selected<?php
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
            <label class="col-md-2 control-label">{{ trans('team::profile.Party join place') }}</label>
            <div class="input-box col-md-10">
                <input type="text" class="form-control fill-disable" name="employeePolitic[union_join_place]"
                    id="employeePolitic-union_join_place" data-checkbox-dist="union"
                    placeholder="{{ trans('team::profile.Party join place') }}"
                    value="{{ $employeePolitic->union_join_place }}"
                    {!!$disableGroup!!} />
            </div>
        </div>
    </div>
</div>
@endsection
@section('profile_js_custom')
    <script src="{{ CoreUrl::asset('team/js/view.js') }}"></script>
@endsection