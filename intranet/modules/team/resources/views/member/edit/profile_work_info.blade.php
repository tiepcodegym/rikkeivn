@extends('team::member.profile_layout')
<?php
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\EmployeeContact;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\Model\Role;
use Rikkei\Core\View\CoreUrl;

$postionsOption = Role::toOptionPosition();
$teamsOption = TeamList::toOption(null, true, false);
?>
@section('content_profile')
@if (!$disabledInput)
<p class="error">{!!trans('team::view.note edit join date affect holiday')!!}</p>
@endif
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="row form-group">
            <label class="col-md-4 control-label required">{{ trans('team::view.Employee code') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" name="employee[employee_code]" class="form-control"
                    placeholder="{{ trans('team::view.Employee code') }}"
                    value="{{ $employeeModelItem->employee_code }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="row form-group">
            <label class="col-md-4 control-label">{{ trans('team::view.Employee card id') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="employee[employee_card_id]"
                    placeholder="{{ trans('team::view.Employee card id') }}"
                    value="{{ $employeeModelItem->employee_card_id }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="row form-group">
            <label class="col-md-4 control-label required">{{ trans('team::view.Email Rikkei') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" name="employee[email]" class="form-control"
                    placeholder="{{ trans('team::view.Email Rikkei') }}"
                    value="{{ $employeeModelItem->email }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group">
            <label class="col-md-4 control-label required">{{ trans('team::view.Join date') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" name="employee[join_date]" id="employee-joindate"
                    class="form-control" placeholder="yyyy-mm-dd" data-flag-type="date"
                    value="{{ View::getDate($employeeModelItem->join_date) }}"
                    {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group">
            <label class="col-md-4 control-label">{{ trans('team::view.Trial date') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employee[trial_date]" id="employee-joindate"
                    class="form-control" placeholder="yyyy-mm-dd" data-flag-type="date"
                    value="{{ View::getDate($employeeModelItem->trial_date) }}"
                    {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="row form-group form-group-select2">
            <label class="col-md-4 control-label">{{ trans('team::view.Trial end date') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employee[trial_end_date]" id="employee-trial_end_date"
                    class="form-control input-valid-custom" placeholder="yyyy-mm-dd" data-flag-type="date"
                    value="{{ View::getDate($employeeModelItem->trial_end_date) }}"
                    {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="row form-group form-group-select2">
            <label class="col-md-4 control-label">{{ trans('team::view.Offical date') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="employee[offcial_date]" id="employee-offcial_date"
                    class="form-control input-valid-custom" placeholder="yyyy-mm-dd" data-flag-type="date"
                    value="{{ View::getDate($employeeModelItem->offcial_date) }}"
                    {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
   
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group">
            <label class="col-md-4 control-label">{{ trans('team::profile.Tax code person') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="e_w[tax_code]"
                    class="form-control" placeholder="{{ trans('team::profile.Tax code person') }}"
                    value="{{ $employeeRelativeItem->tax_code }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group form-group-select2">
            <label class="col-md-4 control-label">{{ trans('team::profile.Contract type') }}</label>
            <div class="input-box col-md-8">
                <select name="e_w[contract_type]" class="form-control select-search"{!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($optionsWorkContract as $key => $value)
                        <option value="{!!$key!!}"<?php
                            if ($employeeRelativeItem->contract_type !== null && (int) $key === (int) $employeeRelativeItem->contract_type): ?> selected<?php
                            endif; ?>>{!!$value!!}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group">
            <label class="col-md-4 control-label">{{ trans('team::profile.Bank account') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="e_w[bank_account]"
                    class="form-control" placeholder="{{ trans('team::profile.Bank account') }}"
                    value="{{ $employeeRelativeItem->bank_account }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group">
            <label class="col-md-4 control-label">{{ trans('team::profile.Bank name') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="e_w[bank_name]"
                    class="form-control" placeholder="{{ trans('team::profile.Bank name') }}"
                    value="{{ $employeeRelativeItem->bank_name }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group">
            <label class="col-md-4 control-label">{{ trans('team::profile.Number insurrance book') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="e_w[insurrance_book]"
                    class="form-control" placeholder="{{ trans('team::profile.Number insurrance book') }}"
                    value="{{ $employeeRelativeItem->insurrance_book }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{ trans('team::profile.Insurrance start') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="e_w[insurrance_date]"
                    class="form-control" placeholder="yyyy-mm-dd"
                    value="{{ $employeeRelativeItem->insurrance_date }}" data-flag-type="date"
                    {!!$disabledInput!!} />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{ trans('team::profile.Ratio insurrance') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="e_w[insurrance_ratio]"
                    class="form-control" placeholder="{{ trans('team::profile.Ratio insurrance') }}"
                    value="{{ $employeeRelativeItem->insurrance_ratio }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group">
            <label class="col-md-4 control-label">{{ trans('team::profile.Number insurrance health') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="e_w[insurrance_h_code]"
                    class="form-control" placeholder="{{ trans('team::profile.Number insurrance health') }}"
                    value="{{ $employeeRelativeItem->insurrance_h_code }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{ trans('team::profile.Register examinatin place') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="e_w[register_examination_place]"
                    class="form-control" placeholder="{{ trans('team::profile.Register examinatin place') }}"
                    value="{{ $employeeRelativeItem->register_examination_place }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{ trans('team::profile.Insurrance health end') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="e_w[insurrance_h_expire]"
                    class="form-control" placeholder="yyyy-mm-dd"
                    value="{{ $employeeRelativeItem->insurrance_h_expire }}" data-flag-type="date"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<hr/>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="row form-group">
            <label class="col-md-4 control-label">
                {{ trans('team::view.Leave date') }}
                <span class="label label-warning hidden" data-flag-dom="label-left"
                    data-text-left="{!!trans('team::view.left work')!!}"
                    data-text-will="{!!trans('team::view.will left work')!!}"></span>
            </label>
            <div class="input-box col-md-8">
                <input type="text" name="employee[leave_date]" id="employee-leavedate"
                    class="form-control date-picker" placeholder="yyyy-mm-dd" data-flag-type="date"
                    value="{{ View::getDate($employeeModelItem->leave_date) }}"
                    {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="row form-group">
            <label class="col-md-2 control-label">{{ trans('team::view.Leave reason') }}</label>
            <div class="input-box col-md-10">
                <textarea name="employee[leave_reason]" id="employee-leave-reason"
                    class="form-control" placeholder="{{ trans('team::view.Leave reason') }}"
                    rows="5"
                    {!!$disabledInput!!}>{{ $employeeModelItem->leave_reason }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_contract_history" data-employee-id="{{ $employeeModelItem->id }}"
     data-url="{{ route('team::member.profile.index', ['employeeId' => $employeeModelItem->id, 'type' => 'contractHistory']) }}">
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('team::profile.Contract history') }}</h4>
            </div>
            <div class="modal-body">
                
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@endsection
@section('profile_js_custom')
<script>
    var probationaryWorkingType = '{!! \Rikkei\Resource\View\getOptions::WORKING_PROBATION !!}';
</script>
<script src="{{ CoreUrl::asset('team/js/view.js') }}"></script>
@endsection
