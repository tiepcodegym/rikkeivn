<?php

use Rikkei\Core\View\Form;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;

$genderOption = Employee::toOptionGender();
if (isset($isCreatePage) && $isCreatePage || Permission::getInstance()->isScopeCompany(null, 'team::team.member.edit')) {
    $employeePermission = true;
} else {
    $employeePermission = false;
}
$editEmployeeCodePermission = (isset($isCreatePage) && $isCreatePage)
                        || Permission::getInstance()->isAllow('team::team.member.editEmployeeCode')
?>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ trans('team::view.Employee code') }}</label>
        @if ($editEmployeeCodePermission)
            <div class="input-box col-md-9">
                <input type="text" name=employee[employee_code]" class="form-control" placeholder="{{ trans('team::view.Employee code') }}" value="{{ Form::getData('employee.employee_code') }}" />
        @else
            <div class="form-control-static col-md-9">
                <span>{{ Form::getData('employee.employee_code') }}</span>
        @endif
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required">{{ trans('team::view.Employee card id') }}<em>*</em></label>
        <div class="input-box col-md-9">
            <input type="text" class="form-control" name="employee[employee_card_id]" 
                placeholder="{{ trans('team::view.Employee card id') }}" 
                value="{{ Form::getData('employee.employee_card_id') }}" 
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required">{{ trans('team::view.Full name') }}<em>*</em></label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[name]" class="form-control" 
                placeholder="{{ trans('team::view.Full name') }}" 
                value="{{ Form::getData('employee.name') }}" 
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ trans('team::view.Birthday') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[birthday]" id="employee-birthday" 
                class="form-control date-picker" placeholder="yyyy-mm-dd" 
                value="{{ Form::getData('employee.birthday') }}"
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group form-group-select2">
        <label class="col-md-3 control-label">{{ trans('team::view.Gender') }}</label>
        <div class="input-box col-md-9">
            <select name="employee[gender]" class="form-control select-search"
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> >
                @foreach ($genderOption as $option)
                    <option value="{{ $option['value'] }}"<?php 
                        if (Form::getData('employee.gender') !== null && (int) $option['value'] === (int) Form::getData('employee.gender')): ?> selected<?php 
                        endif; ?>>{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required">{{ trans('team::view.Identity card number') }}<em>*</em></label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[id_card_number]" class="form-control" 
                placeholder="{{ trans('team::view.Identity card number') }}" 
                value="{{ Form::getData('employee.id_card_number') }}" 
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ trans('team::view.Phone') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[mobile_phone]" id="employee-phone" 
                class="form-control" placeholder="{{ trans('team::view.Phone') }}" 
                value="{{ Form::getData('employee.mobile_phone') }}" 
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required">Email Rikkei<em>*</em></label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[email]" class="form-control" 
                placeholder="Email Rikkei" value="{{ Form::getData('employee.email') }}" 
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ trans('team::view.Email another') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[personal_email]" class="form-control" 
                placeholder="{{ trans('team::view.Email another') }}" 
                value="{{ Form::getData('employee.personal_email') }}"
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label">Skype</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[skype]" class="form-control" 
                placeholder="Skype" 
                value="{{ Form::getData('employee.skype') }}"
                <?php if (! $employeePermission): ?>disabled<?php endif; ?>/>
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ trans('team::view.Address') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[address]" class="form-control" 
                placeholder="{{ trans('team::view.Address') }}" 
                value="{{ Form::getData('employee.address') }}"
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required">{{ trans('team::view.Join date') }}<em>*</em></label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[join_date]" id="employee-joindate" 
                class="form-control date-picker" placeholder="yyyy-mm-dd" 
                value="{{ View::getDate(Form::getData('employee.join_date')) }}"
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>
<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required">{{ trans('team::view.Trial date') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[trial_date]" id="employee-trialdate" 
                class="form-control date-picker" placeholder="yyyy-mm-dd" 
                value="{{ View::getDate(Form::getData('employee.trial_date')) }}"
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>
<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ trans('team::view.Offical date') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[offcial_date]" id="employee-offcial_date" 
                class="form-control date-picker" placeholder="yyyy-mm-dd" 
                value="{{ View::getDate(Form::getData('employee.offcial_date')) }}"
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>
<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required">{{ trans('team::view.Leave date') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[leave_date]" id="employee-leavedate" 
                class="form-control date-picker" placeholder="yyyy-mm-dd" 
                value="{{ View::getDate(Form::getData('employee.leave_date')) }}"
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required">{{ trans('team::view.Leave reason') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[leave_reason]" id="employee-leave-reason" 
                class="form-control" placeholder="{{ trans('team::view.Leave reason') }}" 
                value="{{ Form::getData('employee.leave_reason') }}"
                <?php if (! $employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>

<?php /*div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ trans('team::view.Presenter') }} <i class="fa fa-spin fa-refresh hidden"></i></label>
        <div class="input-box col-md-9">
            <input type="text" id="employee-presenter" class="form-control" 
                placeholder="{{ trans('team::view.Presenter') }}" 
                value="{{ Form::getData('recruitment.present') }}" disabled />
        </div>
    </div>
</div*/ ?>

<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required" for="employee-passport_number">{{ trans('team::view.Passport') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[passport_number]" id="employee-passport_number" 
                class="form-control" placeholder="ABC123456789" 
                value="{{ Form::getData('employee.passport_number') }}"
                <?php if (!$employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>
<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required" for="employee-passport_date_start">{{ trans('team::view.Passport start') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[passport_date_start]" id="employee-passport_date_start" 
                class="form-control date-picker" placeholder="2017-06-19" 
                value="{{ View::getDate(Form::getData('employee.passport_date_start')) }}"
                <?php if (!$employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>
<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required" for="employee-passport_date_exprie">{{ trans('team::view.Passport exprie') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[passport_date_exprie]" id="employee-passport_date_exprie" 
                class="form-control date-picker" placeholder="2017-06-19"
                value="{{ View::getDate(Form::getData('employee.passport_date_exprie')) }}"
                <?php if (!$employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>
<div class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="col-md-3 control-label required" for="employee-passport_addr">{{ trans('team::view.Passport address') }}</label>
        <div class="input-box col-md-9">
            <input type="text" name="employee[passport_addr]" id="employee-passport_addr" 
                class="form-control" placeholder="Hà Nội" 
                value="{{ Form::getData('employee.passport_addr') }}"
                <?php if (!$employeePermission): ?>disabled<?php endif; ?> />
        </div>
    </div>
</div>