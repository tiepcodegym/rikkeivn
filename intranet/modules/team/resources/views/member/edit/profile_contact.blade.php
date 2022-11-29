<?php
use Rikkei\Core\View\CoreUrl;
?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Mobile phone') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="employeeContact[mobile_phone]" 
                       placeholder="{{ trans('team::profile.Mobile phone') }}" 
                       value="{{ $employeeContact->mobile_phone }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Office phone') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeContact[office_phone]" class="form-control" 
                       placeholder="{{ trans('team::profile.Office phone') }}" 
                       value="{{ $employeeContact->office_phone }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Home phone') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeContact[home_phone]" class="form-control" 
                       placeholder="{{ trans('team::profile.Home phone') }}"
                       value="{{ $employeeContact->home_phone }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>

    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Other phone') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeContact[other_phone]" class="form-control" 
                       placeholder="{{ trans('team::profile.Other phone') }}" 
                       value="{{ $employeeContact->other_phone }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Other email') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeContact[personal_email]" class="form-control" 
                       placeholder="{{ trans('team::profile.Other email') }}" 
                       value="{{ $employeeContact->personal_email }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>

    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Other email') }} 2</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeContact[other_email]" class="form-control" 
                       placeholder="{{ trans('team::profile.Other email') }}" value="{{ $employeeContact->other_email }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Yahoo ID') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeContact[yahoo]" class="form-control" 
                       placeholder="{{ trans('team::profile.Yahoo ID') }}" value="{{ $employeeContact->yahoo }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Skype ID') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeContact[skype]" class="form-control" 
                       placeholder="{{ trans('team::profile.Skype ID') }}" value="{{ $employeeContact->skype }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::view.Facebook ID') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeContact[facebook]" class="form-control" 
                    placeholder="{{ trans('team::view.Facebook ID') }}" value="{{ $employeeContact->facebook }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>

<div class="box box-info"></div>

<div class="box-header">
    <h2 class="box-title">{{ trans('team::profile.Permanent residence') }}</h2>
</div>
@include('team::member.edit.profile_contact_native')

<div class="box box-info"></div>

<div class="box-header">
    <h2 class="box-title">{{ trans('team::profile.Current accommodation') }}</h2>
</div>
@include('team::member.edit.profile_contact_tempo')

<div class="box box-info"></div>

<div class="box-header">
    <h2 class="box-title">{{ trans('team::profile.Emergency contact') }}</h2>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Emergency contact name') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeContact[emergency_contact_name]" class="form-control" 
                       placeholder="{{ trans('team::profile.Emergency contact name') }}" value="{{ $employeeContact->emergency_contact_name }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Emergency contact relationship') }}</label>
            <div class="input-box col-md-8">
                <select name="employeeContact[emergency_relationship]" class="form-control select-search has-search"
                    {!!$disabledInput!!}>
                    @foreach ($relationOptions as $value => $label)
                        <option value="{{ $value }}"<?php if ($employeeContact->emergency_relationship !== null && (int) $value === (int) $employeeContact->emergency_relationship): ?> selected<?php endif;
                        ?>>{!!$label!!}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Emergency contact mobile') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeContact[emergency_mobile]" class="form-control" 
                       placeholder="{{ trans('team::profile.Emergency contact mobile') }}" value="{{ $employeeContact->emergency_mobile }}" 
                        {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Emergency contact phone') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeContact[emergency_contact_mobile]" class="form-control" 
                       placeholder="{{ trans('team::profile.Emergency contact phone') }}" value="{{ $employeeContact->emergency_contact_mobile }}" 
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::profile.Emergency contact addr') }}</label>
            <div class="input-box col-md-10">
                <input type="text" name="employeeContact[emergency_addr]" class="form-control" 
                    placeholder="{{ trans('team::profile.Emergency contact addr') }}"
                    value="{{ $employeeContact->emergency_addr }}"
                  {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
@endsection
@section('profile_js_custom')
    <script src="{{ CoreUrl::asset('team/js/view.js') }}"></script>
@endsection
