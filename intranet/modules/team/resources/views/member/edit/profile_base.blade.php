@extends('layouts.default')
<?php
if (!isset($moduleTrans)) {
    $moduleTrans = 'team::profile';
}
if (!isset($contactField)) {
    $contactField = 'employeeContact';
}
?>
<?php
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Country;
use Rikkei\Resource\Model\Candidate;

$candidateAs = Candidate::getCandidate($employeeModelItem->id);
$genderOption = Employee::toOptionGender();
$maritalOption = Employee::toOptionMarital();
$folkOption = Employee::toOptionFolk();
$religionOption = Employee::toOptionReligion();

$libCountry = Country::getCountryList();

$avatar = $userItem->avatar_url;
$avatar = View::getLinkImage($avatar);
$avatar = preg_replace('/\?(sz=)(\d+)/i', '', $avatar);

if (isset($employeeItemTypeId)) {
    $urlSubmitForm = route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $employeeItemTypeId]);
} else {
    $urlSubmitForm = route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => $tabType]);
}
$teamPQA = Team::getTeamPQAByType()->pluck('id')->toArray();
if (isset($arrayTeamIdOfEmp) && array_intersect($teamPQA, $arrayTeamIdOfEmp)) {
    $classHidden = '';
} else {
    $classHidden = 'hidden';
}
$permissEditRole = Permission::getInstance()->isAllow(Employee::ROUTE_EDIT_ROLE);
?>

@section('title')
@if (!empty($employeeModelItem->id))
{{ trans('team::view.Profile of :employeeName', ['employeeName' => $employeeModelItem->name]) }}
@else
{{ trans('team::view.Add a employee') }}
@endif
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="{{ CoreURL::asset('team/css/style.css') }}" />
@endsection
@section('content')
@if (isset($candidateAs) && $candidateAs->id)
<div class="row">
    <a href="{!!route('resource::candidate.detail', ['id'=>$candidateAs->id])!!}" target="_blank" style="float: right; padding: 0px 15px 0px 10px;">Candidate detail</a><i class="fa fa-yelp" style="float: right;"></i>
</div>
@endif
<div class="row member-profile">
   <form action="{!!$urlSubmitForm!!}"
        autocomplete="off" method="post" id="form-employee-info"
        data-form-submit="ajax"
        data-cb-get-form-data="callbackGetFormData"
        data-cb-success="formEmployeeInfoSuccess"
        data-cb-complete="updateOriginalData"
        data-valid-type="{!!$tabType!!}"
        data-form-file="1" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <!-- left menu -->
        <div class="col-lg-2 col-md-3">
            @include('team::member.left_menu',['active' => $tabType])
        </div>
        <!-- /. End left menu -->
    
        <!-- Right column-->
        <!-- Edit form -->
        <div class="col-lg-10 col-md-9 tab-content">
            <div class="tab-pane active">
                <div class="box box-info">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="box-header with-border">
                                <h2 class="box-title">{!!$tabTitle!!}</h2>
                                @if (isset($helpLink) && $helpLink)
                                <a href="{!!$helpLink!!}" target="_blank" title="Help">
                                        <i class="fa fa-fw fa-question-circle" style="font-size: 18px;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="box-header pull-right">
                                <?php
                                $checkCreate = strpos(\Request::url(), 'create');
                                ?>
                                @if($isAccessSubmitForm)
                                    @if ($isScopeCompany) 
                                        <button type="submit" class="btn btn-primary btn-save" style="display: none;" id="save_base">
                                            {!!trans('team::view.Save')!!}
                                            <i class="fa fa-spin fa-refresh loading-submit hidden"></i>
                                        </button>
                                    @endif
                                    @if ($isScopeCompany && !$checkCreate)
                                        <button type="button" class="btn btn-primary btn-edit-profile">
                                            {!!trans('team::view.Edit')!!}
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>

                    </div>
                    <div class="box-body">
                       <div class="row">
                            <div class="col-md-6 form-horizontal">
                                <div class="row form-group">
                                    <label class="col-md-4 control-label required">{{ trans('team::view.Full name') }}<em>*</em></label>
                                    <div class="input-box col-md-8">
                                        <input type="text" name="employee[name]" class="form-control" 
                                            placeholder="{{ trans('team::view.Full name') }}" 
                                            value="{{ $employeeModelItem->name }}"
                                            {!!$isCompanyDisableInput !!} />
                                    </div>
                                </div>
                                @if (!$employeeModelItem->id)
                                    <div class="row form-group" data-flag-profile="base-email">
                                        <label class="col-md-4 control-label required">{{ trans('team::view.Email Rikkei') }}<em>*</em></label>
                                        <div class="input-box col-md-8">
                                            <input type="text" name="employee[email]" class="form-control" 
                                                placeholder="{{ trans('team::view.Email Rikkei') }}"
                                                value="{{ $employeeModelItem->email }}" />
                                        </div>
                                    </div>
                                @endif
                                <div class="row form-group form-group-select2">
                                    <label class="col-md-4 control-label">{{ trans('team::view.Gender') }}</label>
                                    <div class="input-box col-md-8">
                                        <select name="employee[gender]" class="form-control select-search"{!!$isCompanyDisableInput!!}>
                                            @foreach ($genderOption as $option)
                                                <option value="{{ $option['value'] }}"<?php 
                                                    if ($employeeModelItem->gender !== null && (int) $option['value'] === (int) $employeeModelItem->gender): ?> selected<?php 
                                                    endif; ?>>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-md-4 control-label">{{ trans('team::view.Birthday') }}</label>
                                    <div class="input-box col-md-8">
                                        <input type="text" name="employee[birthday]" id="employee-birthday" 
                                               class="form-control date-picker" placeholder="yyyy-mm-dd" 
                                               value="{{ $employeeModelItem->birthday }}"
                                               {!!$isCompanyDisableInput!!} data-flag-type="date" />
                                        <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 form-horizontal">
                                <div class="form-group">
                                    <div class="col-md-offset-2 col-md-9 post">
                                        <div class="user-block input-box-img-preview text-center">
                                            <div class="image-preview employee-image-preview">
                                                <img src="{{ $avatar }}"
                                                    class="img-responsive employee-image-preview img-circle img-bordered-sm{!!$isAccessSubmitForm ? ' cursor-pointer' : ''!!}"
                                                    data-col="image_preview"
                                                    id="employee-avatar_url"
                                                />
                                                @if($isAccessSubmitForm)
                                                    <div class="image-avai">
                                                        <div class="ia-bg"></div>
                                                        <div class="ia-icon">
                                                            <span>{!!trans('team::view.Change')!!}</span>
                                                        </div>
                                                    </div>
                                                    {{-- <div class="employee-upload-input hidden"> --}}
                                                    <div class="employee-upload-input">
                                                        <div class="img-input">
                                                            <input type="file" value="" name="avatar_url" id="avatar_url" />
                                                        </div>
                                                        <div class="help-block hidden">
                                                            The maximum file size allowed is 200KB.
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6 form-horizontal">
                                <div class="row form-group">
                                    <label class="col-md-4 control-label">{{ trans('team::view.Identity card number') }}</label>
                                    <div class="input-box col-md-8">
                                        <input type="text" name="employee[id_card_number]" class="form-control" 
                                            placeholder="{{ trans('team::view.Identity card number') }}" 
                                            value="{{ $employeeModelItem->id_card_number }}"
                                            {!!$isCompanyDisableInput!!} />
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-md-4 control-label">{{ trans('team::view.Identity card date') }}</label>
                                    <div class="input-box col-md-8">
                                        <input type="text" name="employee[id_card_date]" id="employee-id_card_date"
                                            class="form-control date-picker" placeholder="yyyy-mm-dd" 
                                            value="{{ View::getDate($employeeModelItem->id_card_date) }}"
                                            {!!$isCompanyDisableInput!!} data-flag-type="date" />
                                        <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label">{{ trans('team::view.Identity card addr') }}</label>
                                    <div class="input-box col-md-8">
                                        <input type="text" name="employee[id_card_place]" class="form-control" 
                                            placeholder="{{ trans('team::view.Identity card addr') }}"
                                            value="{{ $employeeModelItem->id_card_place }}"
                                            {!!$isCompanyDisableInput!!} />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-horizontal">
                                <div class="row form-group">
                                    <label class="col-md-4 control-label" for="employee-passport_number">{{ trans('team::view.Passport') }}</label>
                                    <div class="input-box col-md-8">
                                        <input type="text" name="employee[passport_number]" id="employee-passport_number" 
                                            class="form-control" placeholder="ABC123456789" 
                                            value="{{ $employeeModelItem->passport_number }}"
                                            {!!$isCompanyDisableInput!!} />
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-md-4 control-label" for="employee-passport_addr">{{ trans('team::view.Passport address') }}</label>
                                    <div class="input-box col-md-8">
                                        <input type="text" name="employee[passport_addr]" id="employee-passport_addr" 
                                            class="form-control" placeholder="Hà Nội" 
                                            value="{{ $employeeModelItem->passport_addr }}"
                                            {!!$isCompanyDisableInput!!} />
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-md-4 control-label" for="employee-passport_date_start">{{ trans('team::view.Passport start') }}</label>
                                    <div class="input-box col-md-8">
                                        <input type="text" name="employee[passport_date_start]" id="employee-passport_date_start" 
                                            class="form-control date-picker" placeholder="yyyy-mm-dd" data-flag-type="date"
                                            value="{{ View::getDate($employeeModelItem->passport_date_start) }}"
                                            {!!$isCompanyDisableInput!!} />
                                        <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-md-4 control-label" for="employee-passport_date_exprie">{{ trans('team::view.Passport exprie') }}</label>
                                    <div class="input-box col-md-8">
                                        <input type="text" name="employee[passport_date_exprie]" id="employee-passport_date_exprie" 
                                            class="form-control date-picker" placeholder="yyyy-mm-dd" data-flag-type="date"
                                            value="{{ View::getDate($employeeModelItem->passport_date_exprie) }}"
                                            {!!$isCompanyDisableInput!!} />
                                        <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--  -->
                        <div class="row">
                            <div class="sixteen columns header"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-horizontal">
                                <div class="row form-group form-group-select2">
                                    <label class="col-md-4 control-label">{{ trans('team::view.Folk') }}</label>
                                    <div class="input-box col-md-8">
                                        <select name="employee[folk]" class="form-control select-search has-search"
                                            {!!$isCompanyDisableInput!!} >
                                            <option value="">&nbsp;</option>
                                            @foreach ($folkOption as $option)
                                                <option value="{{ $option['value'] }}"<?php 
                                                    if ($employeeModelItem->folk !== null && $employeeModelItem->folk != '' && (int) $option['value'] === (int) $employeeModelItem->folk): ?> selected<?php 
                                                    endif; ?>>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-horizontal">
                                <div class="row form-group">
                                    <label class="col-md-4 control-label">{{ trans('team::view.Religion') }}</label>
                                    <div class="input-box col-md-8">
                                        <select name="employee[religion]" class="form-control select-search has-search"
                                            {!!$isCompanyDisableInput!!} >
                                            <option value="">&nbsp;</option>
                                            @foreach ($religionOption as $option)
                                                <option value="{{ $option['value'] }}"<?php 
                                                    if ($employeeModelItem->religion !== null && $employeeModelItem->religion != '' && (int) $option['value'] === (int) $employeeModelItem->religion): ?> selected<?php 
                                                    endif; ?>>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-horizontal">
                                <div class="form-group form-group-select2">
                                    <label class="col-md-4 control-label">{{ trans('team::view.Marital') }}</label>
                                    <div class="input-box col-md-8">
                                        <select name="employee[marital]" class="form-control select-search"
                                            {!!$isCompanyDisableInput!!}>
                                            <option value="">&nbsp;</option>
                                            @foreach ($maritalOption as $option)
                                                <option value="{{ $option['value'] }}"<?php 
                                                    if (is_numeric($employeeModelItem->marital) && $option['value'] == $employeeModelItem->marital): ?> selected<?php 
                                                    endif; ?>>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-horizontal">
                                <div class="row form-group">
                                    <label class="col-md-4 control-label">{{ trans($moduleTrans .'.National') }}</label>
                                    <div class="input-box col-md-8">
                                        <select name="employee[country_id]" class="form-control select-search has-search"
                                            {!!$disabledInput!!}>
                                            <option value="">&nbsp;</option>
                                            @foreach ($libCountry as $key => $label)
                                                <option value="{{ $key }}"<?php 
                                                    if ($employeeModelItem && $key == $employeeModelItem->country_id): ?> selected<?php 
                                                    endif; ?>>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="action-profile">
                    <div class="col-md-9" data-height-same="team">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h2 class="box-title">{!!trans('team::view.Team')!!}</h2>
                            </div>
                            <div class="box-body">
                                <p class="error">{!!trans('team::view.note edit information of Japan teams affect employee holidays')!!}</p>
                                <input type="hidden" name="employee_team_change" value="0" />
                                <div class="team-edit">
                                    @include('team::member.edit.team')
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3" data-height-same="team">
                        <div class="box box-info box-role">
                            <div class="box-header with-border header-role">
                                <h2 class="box-title">{!!trans('team::view.Role Special')!!}</h2>
                                @if ($permissEditRole)
                                <span class="btn-edit-role"
                                      data-target="#employee-role-form"
                                      data-toggle="modal"
                                      title="{{ trans('team::view.Update special role') }}">
                                    <i class="fa fa-edit"></i>
                                </span>
                                @endif
                            </div>
                            <div class="box-body">
                                <input type="hidden" name="employee_role_change" value="0" />
                                @include('team::member.edit.role')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!--box select team responsible-->
                    <div class="col-md-6">
                        <div class="box box-info team-responsible {!! $classHidden !!} height-240">
                            <div class="col-sm-8">
                                <div class="box-header">
                                    <h2>{!!trans('team::view.Responsible team')!!}</h2>
                                    <a class="help-responsible-team" href="#" title="- Khi chọn team, nhân viên sẽ có quyền mặc định reviewer cho dự án mà team này làm.
- Ngoài ra, nhân viên này sẽ có quyền xem được profile(danh sách và detail) của team.">
                                        <i class="fa fa-fw fa-question-circle" style="font-size: 30px;"></i>
                                    </a>
                                </div>
                                <div class="box-body">
                                    <label class="control-label">{!!trans('team::view.Team')!!}</label>
                                    <select class="responsible" name="team-responsible[]" multiple="multiple">
                                        @foreach($teamsOption as $option)
                                            <option value="{{ $option['value'] }}"<?php
                                                if (isset($listTeamId) && in_array($option['value'], $listTeamId)): ?> selected<?php endif; 
                                            ?>>{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4"></div>
                        </div>
                    </div>
                </div>

                @if($isAccessSubmitForm || (isset($isAccessDeleteEmployee) && $isAccessDeleteEmployee))
                    <div class="box-footer">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary btn-save-profile">
                                    {!!trans('team::view.Save')!!}
                                    <i class="fa fa-spin fa-refresh loading-submit hidden"></i>
                                </button>
                                @if (isset($isAccessDeleteEmployee) && $isAccessDeleteEmployee)
                                    <button type="button" class="btn btn-danger pull-right btn-del-empl{!!$employeeModelItem->id ? '' : ' hidden'!!} hidden"
                                        data-btn-submit="ajax" data-flag-dom="btn-employee-remove"
                                        data-submit-noti="{!!trans('team::view.Are you sure delete this member?')!!}"
                                        action="{!!route('team::member.profile.delete', ['id' => $employeeModelItem->id])!!}">
                                        {!!trans('team::view.Remove employee')!!}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <br>
            @if($isAccessSubmitForm && ($isScopeCompany && !$checkCreate))
                <div class="box box-primary">
                    <div class="box-body font-size-14">
                        {!! trans('team::view.Guide edit team profile employee') !!}
                    </div>
                </div>
            @endif
        </div>
            <div class="modal" tabindex="-1" role="dialog" id="notice-change-team" hidden>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div id="assign_body">
                                <div class="form-group form-group-select2" style="text-align: center">
                                    <p>{{ trans('team::messages.Confirm when change team VN/JP and vice versa') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" id="cancel-change-team"
                                    data-dismiss="modal">{{ trans('ot::view.Close') }}</button>
                            <button type="submit" class="btn btn-primary pull-right"
                                    id="confirm-change-team">{{ trans('ot::view.Yes') }}</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>

   </form>
</div>
@endsection

@section('script')
<?php
if (!isset($employeeItemTypeId)) {
    $employeeItemTypeId = 0;
}
?>
<script>
    var globalPassModule = {
        urlViewProfile: '{!!route('team::member.profile.index', ['id' => 0])!!}',
        urlUploadAttachFile: '{!!route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => 'attachFile', 'typeId' => $employeeItemTypeId])!!}',
        urlDeleteAttachFile: '{!!route('team::member.profile.delete.relative2', ['employeeId' => $employeeModelItem->id, 'type' => 'attachFile', 'typeId' => $employeeItemTypeId])!!}',
        urlAssetStorage: '{!!asset(Storage::disk('public')->url('/'))!!}/',
        tabType: '{!!$tabType!!}',
        isSelfProfile: {!!$isSelfProfile ? 1 : 0!!},
        trans: {
            base_passport_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::view.Passport start')])!!}',
            offical_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::view.Join date')])!!}',
            leave_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::view.Offical date')])!!}',
            work_japan_to_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::profile.From')])!!}',
            prize_expire_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::profile.Prize issue date')])!!}',
            relative_deduction_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::profile.Deduction start date')])!!}',
            military_left_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::profile.Military join date')])!!}',
            doc_expired_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::profile.Doc issue date')])!!}',
            edu_end_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::profile.From')])!!}',
            cer_end_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::profile.Effect from')])!!}',
            exp_end_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::profile.Start')])!!}',
            wo_end_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::profile.Start at')])!!}',
            ew_ih_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::profile.Insurrance health start')])!!}',
            positive: '{!!trans('validation.positive')!!}',
            positive_equal0: '{!!trans('validation.positive equal 0')!!}',
            save_successfully: '{!! trans('core::message.Save success') !!}',
        }
    };
    var pleaseEnter = '{{trans('resource::view.Please enter')}}';
    var messEndDay = '{{trans('manage_time::view.The end date at must be after start date')}}';
    var messStartDay = '{{trans('manage_time::view.The start date at must be before end date')}}';
    var noEndDate = '{{trans('team::messages.Please enter end date')}}';
    var incorrectDate = '{{trans('team::messages.Date data is incorrect')}}';
    var timeEndAfterStart = '{{trans('team::messages.The end date at must be after start date')}}';
    var typeAllow = [{!! '"' . implode('","', Config::get('services.file.image_allow')) . '"' !!}],
        sizeAllow = {!! Config::get('services.file.image_max') !!},
        imagePreviewImageDefault = '{{ View::getLinkImage() }}',
        txtMessageSize = '{!! trans('core::message.File size is large') !!}',
        txtMessageType = '{!! trans('core::message.File type dont allow') !!}';
</script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/localization/messages_vi.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script src="{!!CoreUrl::asset('lib/js/jquery.match.height.addtional.js')!!}"></script>
<script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
@if (!$checkCreate)
<script src="{{ CoreUrl::asset('team/js/view.js') }}"></script>
@endif
<script>
    jQuery(document).ready(function($) {
        //Default hide teams leave
        hideTeamsLeave();
        $('.show_team_leave').click(function() {
            $('.team-edit .group-team-position').removeClass('hidden');
            $(this).addClass('hidden');
            $('.hide_team_leave').removeClass('hidden');
            $(this).closest('.box-info').css('height', 'auto');
        });
        $('.hide_team_leave').click(function() {
            hideTeamsLeave();
            $(this).addClass('hidden');
            $('.show_team_leave').removeClass('hidden');
            $(this).closest('.box-info').css('height', 'auto');
        });
    });
    function hideTeamsLeave() {
        var today = new Date();
        var now = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
        $('.team-edit .input-end-at').each(function() {
            if ($(this).val() && $(this).val() < now) {
                $(this).closest('.group-team-position').addClass('hidden');
            }
        });
    }
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.responsible').select2({ width: '300px' });
    });
    var isJP = {{ isset($isJP) ? $isJP : 0 }};
    var listTeamJP = JSON.parse('{!! isset($listTeamJP) ? json_encode($listTeamJP) : null !!}');
    var checkJP = 0;
    $(document).on('click touchstart', '.is-working', function(event) {
        $('.is-working').not(this).prop('checked', false);
    });
</script>
@endsection
