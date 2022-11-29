<?php
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Employee; ?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Prize name') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="employeeItemPrize[name]"
                    placeholder="{!! trans('team::profile.Prize name') !!}"
                    value="{{ $employeeItemMulti->name }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Prize level') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="employeeItemPrize[level]" 
                       placeholder="{{ trans('team::profile.Prize level') }}" 
                       value="{{ $employeeItemMulti->level }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="row form-group form-group-select2">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Prize issue date') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeItemPrize[issue_date]" id="employee-offcial_date" 
                    class="form-control input-valid-custom" placeholder="yyyy-mm-dd" data-flag-type="date"
                    value="{{ View::getOnlyDate($employeeItemMulti->issue_date) }}"
                    {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="row form-group">
            <label class="col-md-4 control-label">{{ trans('team::profile.Prize expire date') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employeeItemPrize[expire_date]" id="employee-leavedate" 
                    class="form-control date-picker" placeholder="yyyy-mm-dd" data-flag-type="date"
                    value="{{ View::getOnlyDate($employeeItemMulti->expire_date) }}"
                    {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Prize place') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="employeeItemPrize[place]"
                          value="{{ $employeeItemMulti->place }}"
                          placeholder="{{ trans('team::profile.Prize place') }}" 
                          {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::view.Image') }}</label>
            <div class="input-box col-md-4 input-box-img-preview prize-image">
                <div class="image-preview employee-image-preview">
                    <?php
                    $image = $employeeItemMulti->image;
                    if (!$image) {
                        $image = View::getLinkImage();
                    } else {
                        $image = URL::asset(
                            Config::get('general.upload_folder')
                            . '/' . Employee::ATTACH_FOLDER
                            . $employeeItemMulti->image
                            );
                    }
                    ?>
                    <img src="{!!$image!!}"
                         class="img-responsive" data-img-pre-img="prize" />
                </div>
                <?php if (!$disabledInput) :?>
                    <div>
                        <input type="file" class="form-control" value="" name="image" data-img-pre-input="prize" />
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::profile.Note') }}</label>
            <div class="input-box col-md-10">
                <textarea class="form-control" name="employeeItemPrize[note]"
                       rows="6"
                       placeholder="{{ trans('team::profile.Note') }}" 
                       {!!$disabledInput!!}>{{ $employeeItemMulti->note }}</textarea>
            </div>
        </div>
    </div>
</div>
@endsection

@section('profile_js_custom')
<script>
    jQuery(document).ready(function () {
        RKExternal.previewImage.init();
    });
</script>
@endsection