<?php
use Rikkei\Core\View\CoreUrl;
?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-label-left">
            <div class="col-md-4">&nbsp;</div>
            <label class="col-md-8 control-label">{!!trans('team::profile.Asian style')!!}</label>
        </div>
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Shirt') }} <em>*</em></label>
            <div class="input-box col-md-8">
                <select name="employeeCostume[asia_shirts]" class="form-control select-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($asiaSizesOption as $option)
                        <option value="{{ $option['value'] }}"<?php
                            if ($employeeCostume->asia_shirts !== null && $option['value'] === $employeeCostume->asia_shirts): ?> selected<?php
                            endif; ?>>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Pants') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeCostume[asia_paints]" class="form-control select-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($asiaSizesOption as $option)
                        <option value="{{ $option['value'] }}"<?php
                            if ($employeeCostume->asia_paints !== null && $option['value'] === $employeeCostume->asia_paints): ?> selected<?php
                            endif; ?>>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Zuyp') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeCostume[asia_zuyp]" class="form-control select-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($asiaSizesOption as $option)
                        <option value="{{ $option['value'] }}"<?php
                            if ($employeeCostume->asia_zuyp !== null &&  $option['value'] ===  $employeeCostume->asia_zuyp): ?> selected<?php
                            endif; ?>>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Protection gear') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeCostume[asia_protective]" class="form-control select-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($asiaSizesOption as $option)
                        <option value="{{ $option['value'] }}"<?php
                            if ($employeeCostume->asia_protective !== null &&  $option['value'] ===  $employeeCostume->asia_protective): ?> selected<?php
                            endif; ?>>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-label-left">
            <div class="col-md-4">&nbsp;</div>
            <label class="col-md-8 control-label">{!!trans('team::profile.European style')!!}</label>
        </div>
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Shirt') }} <em>*</em></label>
            <div class="input-box col-md-8">
                <select name="employeeCostume[euro_shirts]" class="form-control select-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($europeSizesOption as $option)
                        <option value="{{ $option }}"<?php
                            if ($employeeCostume->euro_shirts !== null && (int) $option === (int) $employeeCostume->euro_shirts): ?> selected<?php
                            endif; ?>>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Pants') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeCostume[euro_paints]" class="form-control select-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($europeSizesOption as $option)
                        <option value="{{ $option }}"<?php
                            if ($employeeCostume->euro_paints !== null && (int) $option === (int) $employeeCostume->euro_paints): ?> selected<?php
                            endif; ?>>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Zuyp') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeCostume[euro_zuyp]" class="form-control select-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($europeSizesOption as $option)
                        <option value="{{ $option }}"<?php
                            if ($employeeCostume->euro_zuyp !== null && (int) $option === (int) $employeeCostume->euro_zuyp): ?> selected<?php
                            endif; ?>>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Protection gear') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeCostume[euro_protective]" class="form-control select-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($europeSizesOption as $option)
                        <option value="{{ $option }}"<?php
                            if ($employeeCostume->euro_protective !== null && (int) $option === (int) $employeeCostume->euro_protective): ?> selected<?php
                            endif; ?>>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="box box-info">
    <h3 class="box-title">{{ trans('team::profile.Tailor-made') }}</h3>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Shoulder width') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeCostume[shoudler_width]" class="form-control text-right number num"
                        value="{{ $employeeCostume->shoudler_width > 0 ? $employeeCostume->shoudler_width : '' }}"
                        {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Butt') }}</label>
            <div class="input-box col-md-8">
                    <input type="text" name="employeeCostume[round_butt]" class="form-control text-right number num"
                            value="{{ $employeeCostume->round_butt > 0 ? $employeeCostume->round_butt : '' }}"
                            {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Long sleeve') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeCostume[long_sleeve]" class="form-control text-right number num"
                    value="{{ $employeeCostume->long_sleeve > 0 ? $employeeCostume->long_sleeve : '' }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Long pants') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeCostume[long_pants]" class="form-control text-right number num"
                        value="{{ $employeeCostume->long_pants > 0 ? $employeeCostume->long_pants : '' }}"
                        {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Long shirt') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeCostume[long_shirt]" class="form-control text-right number num"
                        value="{{ $employeeCostume->long_shirt > 0 ? $employeeCostume->long_shirt : '' }}"
                        {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Long skirts') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeCostume[long_skirt]" class="form-control text-right number num"
                        value="{{ $employeeCostume->long_skirt > 0 ? $employeeCostume->long_skirt : '' }}"
                        {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Chest') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeCostume[round_chest]" class="form-control text-right number num"
                        value="{{ $employeeCostume->round_chest > 0 ? $employeeCostume->round_chest : '' }}"
                        {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Thighs') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeCostume[round_thigh]" class="form-control text-right number num"
                        value="{{ $employeeCostume->round_thigh > 0 ? $employeeCostume->round_thigh : '' }}"
                        {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Waist') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeCostume[round_waist]" class="form-control text-right number num"
                        value="{{ $employeeCostume->round_waist > 0 ? $employeeCostume->round_waist : '' }}"
                        {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
@endsection
@section('profile_js_custom')
    <script src="{{ CoreUrl::asset('team/js/view.js') }}"></script>
@endsection
