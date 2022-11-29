<?php
use Rikkei\Core\View\CoreUrl;
?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Blood group') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeHealth[blood_type]" class="form-control select-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($bloodTypesOption as $option)
                    <option value="{{ $option }}"<?php if ($employeeHealth->blood_type !== null && $option === $employeeHealth->blood_type): ?> selected<?php endif;
                        ?>>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Height (cm)') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control num" name="employeeHealth[height]"
                    placeholder="{{ trans('team::profile.Height (cm)') }}"
                    value="{{ $employeeHealth->height }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Weigth (kg)') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control num" name="employeeHealth[weigth]"
                       placeholder="{{ trans('team::profile.Weigth (kg)') }}"
                       value="{{ $employeeHealth->weigth }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::profile.Health status') }}</label>
            <div class="input-box col-md-10">
                <input type="text" class="form-control" name="employeeHealth[health_status]"
                       placeholder="{{ trans('team::profile.Health status') }}"
                       value="{{ $employeeHealth->health_status }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::profile.Health note') }}</label>
            <div class="input-box col-md-10">
                <textarea class="form-control" name="employeeHealth[health_note]"
                       rows="6"
                       placeholder="{{ trans('team::profile.Health note') }}"
                       {!!$disabledInput!!}>{{ $employeeHealth->health_note }}</textarea>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::profile.Ailment') }}</label>
            <div class="input-box col-md-10">
                <textarea class="form-control" name="employeeHealth[ailment]"
                        rows="6"
                       placeholder="{{ trans('team::profile.Ailment') }}"
                       {!!$disabledInput!!}>{{ $employeeHealth->ailment }}</textarea>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::profile.Is disabled') }}</label>
            <div class="input-box col-md-2">
                <div class="checkbox">
                    <label>
                       <input type="checkbox" name="employeeHealth[is_disabled]"
                       id="employeeHealth_is_disabled" value="1"
                       placeholder="{{ trans('team::profile.Is disabled') }}"
                       <?php if ((int)($employeeHealth->is_disabled)): ?> checked<?php endif;?>
                       {!!$disabledInput!!} />
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
