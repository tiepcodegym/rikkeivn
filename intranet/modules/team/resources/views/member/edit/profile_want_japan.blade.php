<?php
use Rikkei\Core\View\View;
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
                       <input type="checkbox" name="employeeJapan[want_to_japan]"
                       value="1" data-checkbox-source="japan"
                       <?php if ((int)($employeeJapan->want_to_japan)): ?> checked<?php endif;?>
                       {!!$disabledInput!!} >
                       {{ trans('team::profile.Want to Japan') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $disableGroup = $disabledInput || !$employeeJapan->want_to_japan ? ' disabled' : '' ?>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.From') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control date-picker" name="employeeJapan[from]"
                   data-checkbox-dist="japan"
                   placeholder="yyyy-mm-dd" data-flag-type="date"
                   value="{{ View::getOnlyDate($employeeJapan->from) }}"
                   {!!$disableGroup!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div> 
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.To') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control date-picker" name="employeeJapan[to]"
                   data-checkbox-dist="japan"
                   placeholder="yyyy-mm-dd" data-flag-type="date"
                   value="{{ View::getOnlyDate($employeeJapan->to) }}"
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
                <textarea type="text" class="form-control" name="employeeJapan[note]"
                    data-checkbox-dist="japan"
                    placeholder="{{ trans('team::profile.Note') }}" rows="6"
                    {!!$disableGroup!!}>{{ $employeeJapan->note }}</textarea>
            </div>
        </div>
    </div>
</div>
@endsection
