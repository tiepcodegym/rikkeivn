<?php
use Rikkei\Resource\View\View;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\Permission;
use Rikkei\Assets\View\RequestAssetPermission;

$disable = null;
//if ($employee && $candidate->isWorkingOrEndOrLeave() && !in_array($candidate->working_type, array_keys(getOptions::listWorkingTypeExternal()))) {
//    $disable = 'disabled';
//}
$suggestEmail = '';
if ($candidate->fullname) {
    $suggestEmail = View::suggestEmail($candidate->fullname);
}
$libCountry = View::getListCountries();
$listEmployeeStatus = getOptions::listEmployeeStatus();
$employeeContact = $employee ? $employee->contact : null;
$permissRequestAsset = $employee && RequestAssetPermission::permissEditRequesets([], Permission::getInstance()->getEmployee()->id);
if ($candidate->status == getOptions::LEAVED_OFF) {
    $listEmployeeStatus[getOptions::LEAVED_OFF] = trans('resource::view.Candidate.Detail.Leaved off');
}
$requireClass = in_array($candidate->status, [getOptions::WORKING, getOptions::PREPARING]) ? 'required-text' : '';
?>

<div class="tab-pane <?php if($tabActive == 'tab_employee'): ?> active <?php endif; ?>" id="tab_employee">
    <form id="form-employee-candidate" class="form-horizontal form-candidate-detail" 
          method="post" action="{{$urlSubmit}}">
        {!! csrf_field() !!}
        <div class="form-group">
            <div class="col-md-10 col-md-offset-2">
                <label>
                    <input type="checkbox" name="is_old_employee" id="is_old_member" value="1" {{ $disable }} {{ $disable && $candidate->is_old_employee ? 'checked' : '' }}> 
                    <strong>{{ trans('resource::view.Is old member') }}</strong>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2">{{ trans('resource::view.Candidate.Detail.Status') }} <em class="required">*</em></label>
            <div class="col-md-10">
                <select class="form-control select-search employee-status" id="employee_status" name="status" {{ $disable }}>
                    <option value="">&nbsp;</option>
                    @foreach ($listEmployeeStatus as $value => $label)
                    <option value="{{ $value }}" {{ $candidate->status == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group" id="box-contract-team" style="{{(int)$candidate->status != getOptions::PREPARING ? 'display: none':''}}">
            <label for="contract_team_id" class="control-label col-md-2">
                {{trans('resource::view.Department of records management')}} <em class="required" aria-required="true">*</em>
            </label>
            <div class="col-md-10">
                <select 
                    class="form-control select-search has-search " 
                    id="contract_team_id"  
                    name="contract_team_id"
                    autocomplete="off"
                    >
                <option value="">-- {{trans('contract::vi.Choose team')}} --</option>
                @if (count($teamsOptionAll))
                @foreach($teamsOptionAll as $option)
                <option value="{{ $option['value'] }}" {{$candidate->contract_team_id == $option['value']   ? 'selected' :''}} >
                    {{ $option['label'] }}
                </option>
                @endforeach
                @endif
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2 {{ $requireClass }}" data-label="email">{{ trans('resource::view.Candidate.Detail.Email Rikkei') }}</label>
            <div class="col-md-10">
                <div class="emp-new-email">
                    <input id="email" list="suggest_email" type="email" name="employee[email]" class="form-control" {{ $disable }}
                           value="{{ $employee ? $employee->email : old('employee.email') }}" autocomplete="off">
                    @if (!$disable)
                    <datalist id="suggest_email">
                        <option value="{{ $suggestEmail }}">
                    </datalist>
                    <div class="text-desc">{{ trans('resource::view.Suggest') }}: {{ $suggestEmail }}</div>
                    @endif
                </div>
                <div class="emp-old-email hidden">
                    <select class="form-control select-search check-change" id="emp_old_email" name="old_employee_id"
                        data-title="{{ trans('resource::view.Old employee email') }}"
                        data-value="{{ $employee ? $employee->email : null }}"
                        data-change-url="{{ route('resource::candidate.employee.info') }}"
                        data-remote-url="{{ route('team::employee.list.search.ajax', ['type' => '', 'fullEmail' => 1, 'has_leave' => 1]) }}">
                        @if ($employee && $candidate->is_old_employee)
                        <option value="{{ $employee->id }}" selected>{{ $employee->email }}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2 {{ $requireClass }}" data-label="employee_card_id">{{ trans('resource::view.Candidate.Detail.Employee card id') }}</label>
            <div class="col-md-10">
                <input id="employee_card_id" list="suggest_card_id" type="text" name="employee[employee_card_id]" class="form-control" {{ $disable }}
                       value="{{ $employee ? ($employee->employee_card_id != 0 ? $employee->employee_card_id : '') : old('employee.employee_card_id') }}"
                       autocomplete="off" maxlength="8">
                @if (!$disable)
                <datalist id="suggest_card_id">
                    <option value="{{ $maxEmployeeCardId }}">
                </datalist>
                <div class="text-desc">{{ trans('resource::view.Suggest') }}: {{ $maxEmployeeCardId }}</div>
                @endif
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2 {{ $requireClass }}" data-label="employee_code">{{ trans('resource::view.Candidate.Detail.Employee code') }}</label>
            <div class="col-md-10">
                <span id="employee_code" class="employee-code-form">{{ $employee ? (!empty($employee->employee_code) ? $employee->employee_code : '') : old('employee.employee_code') }}</span>
                <input type="hidden" name="employee_code" value="{!! old('employee.employee_card_id') ? old('employee.employee_code') : '' !!}">
            </div>
        </div>
        
        <div class="row margin-top-30">
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label col-md-4 {{ $requireClass }}" data-label="id_card_number">{{ trans('resource::view.Candidate.Detail.ID Card number') }}</label>
                    <div class="col-md-8">
                        <input type="text" name="employee[id_card_number]" class="form-control" {{ $disable }}
                               value="{{ $employee && $employee->id_card_number ? $employee->id_card_number : $candidate->identify }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-4 {{ $requireClass }}" data-label="id_card_place">{{ trans('resource::view.Candidate.Detail.ID Card place') }}</label>
                    <div class="col-md-8">
                        <input type="text" name="employee[id_card_place]" class="form-control" {{ $disable }}
                               value="{{ $employee && $employee->id_card_place ? $employee->id_card_place : $candidate->issued_place }}">
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label col-md-4 {{ $requireClass }}" data-label="id_card_date">{{ trans('resource::view.Candidate.Detail.ID Card date') }}</label>
                    <div class="col-md-8">
                        <input type="text" name="employee[id_card_date]" class="form-control field-date-picker" {{ $disable }}
                               value="{{ $employee && $employee->id_card_date && $employee->id_card_date != '0000-00-00 00:00:00'
                               ? $employee->id_card_date : $candidate->issued_date }}">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-md-2 control-label">{{ trans('resource::view.Candidate.Detail.Permanent address') }}</label>
        </div>

        @include('team::member.edit.profile_contact_native', [
            'moduleTrans' => 'resource::view.Candidate.Detail',
            'disabledInput' => $disable,
            'contactField' => 'employee[contact]',
            'notCheckNative' => true,
            'requireClass' => $requireClass,
        ])

        <div class="row">
            <div class="col-md-12 <?php if((int)$candidate->status !== \Rikkei\Resource\View\getOptions::FAIL_CDD): ?>hidden<?php endif; ?> interested-input-container">
                <div class="form-group position-relative">
                    <label class="col-md-2 control-label">{{trans('resource::view.Candidate.Create.Interested')}}</label>
                    <div class="col-md-10">
                        <span>
                            <select name="interested" class="form-control">
                                @foreach ($interestedOptions as $key => $interested)
                                    <option value="{!! $key !!}"
                                            class="{!! $interested['class'] !!} font-15"
                                            @if ((int)$candidate->interested === $key) selected @endif>{!! $interested['label'] !!}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @if (!$disable)
        <div class="margin-top-40 text-center form-group">
            <input type="hidden" name="had_worked" value="1">
            <input type="hidden" name="candidate_id" value="{{ $candidate->id }}">
            <input id="employee_id" type="hidden" {{ $disable }}
                   value="{{ $employee ? $employee->id : null }}">
            <input type="hidden" name="detail" value="detail" />
            @if ($permissRequestAsset && $candidate->status != getOptions::LEAVED_OFF)
            <button type="button" id="btn_view_request_asset" data-toggle="modal" data-target="#modal_request_asset"
                    class="btn btn-info">{{ trans('resource::view.Request asset') }}</button>
            @endif
            <button type="submit" id="btn_submit_employee" class="btn btn-primary" disabled
                    data-status="{!! json_encode([getOptions::FAIL_CDD, getOptions::WORKING]) !!}"
                    data-noti="{{ trans('resource::message.You can only submit this once, are you sure?') }}"
                    data-other-noti="{{ trans('resource::message.Are you sure want to save change') }}">
                {{trans('resource::view.Submit Employee')}}
            </button>
        </div>
        @endif
    </form>
</div>

@if ($permissRequestAsset)
<div class="modal fade" id="modal_request_asset">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><strong>{{ trans('resource::view.Request asset') }}</strong></h4>
            </div>
            <div class="modal-body">
                <iframe src="" frameborder="0" class="frm-modal"
                        data-src="{{ route('asset::resource.request.edit', [
                            'id' => $candidate->request_asset_id,
                            'is_popup' => 1,
                            'cdd_id' => $candidate->id,
                            'emp_id' => $employee->id,
                            'cdd_team_id' => $candidate->team_id
                        ]) }}"></iframe>
            </div>
        </div>
    </div>
</div>
@endif

