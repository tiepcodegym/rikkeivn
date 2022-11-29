@extends('layouts.default')
<?php
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Skill;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Resource\Model\Candidate;

$candidateAs = Candidate::getCandidate($employeeModelItem->id);
?>

@section('title')
{{ trans('team::view.Profile of :employeeName', ['employeeName' => $employeeModelItem->name]) }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
@yield('profile_css')
@endsection

@section('content')
@if (isset($candidateAs) && $candidateAs->id)
<div class="row">
    <a href="{!!route('resource::candidate.detail', ['id'=>$candidateAs->id])!!}" target="_blank" style="float: right; padding: 0px 15px 0px 10px;">Candidate detail</a><i class="fa fa-yelp" style="float: right;"></i>
</div>
@endif
<?php
if (in_array($tabType, ['base', 'prize', 'attach', 'certificate'])) {
    $dataFormFile = ' data-form-file="1" enctype="multipart/form-data"';
} else {
    $dataFormFile = '';
}
if (isset($employeeItemTypeId)) {
    $urlSubmitForm = route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $employeeItemTypeId]);
} else {
    $urlSubmitForm = route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => $tabType]);
}
?>
<div class="row member-profile">
    <form action="{!!$urlSubmitForm!!}"
        autocomplete="off" method="post" id="form-employee-info"
        data-form-submit="ajax"
        data-cb-success="formEmployeeInfoSuccess"
        data-valid-type="{!!$tabType!!}"
        {!!$dataFormFile!!}>
        {!! csrf_field() !!}
        <!-- left menu -->
        <div class="col-lg-2 col-md-3">
            @include('team::member.left_menu',['active' => $tabType])
        </div>
        <!-- /. End left menu -->
    
        <!-- Right column-->
        <!-- Edit form -->
        <div class="col-lg-10 col-md-9 tab-content">
            <div class="box box-info tab-pane active">
                <div class="row">
                    <div class="col-md-8">
                        <div class="box-header with-border">
                            <h2 class="box-title">{!!$tabTitle!!}</h2>
                            @if (isset($helpLink) && $helpLink)
                            <a href="{!!$helpLink!!}" target="_blank" title="Help">
                                    <i class="fa fa-fw fa-question-circle" style="font-size: 18px;"></i>
                                </a>
                            @endif
                            @if (isset($tabTitleSub) && $tabTitleSub)
                                <p class="margin-top-10">{!!$tabTitleSub!!}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-header pull-right">
                            @if ($tabType == 'work')
                                @if ($isScopeCompany)
{{--                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal_contract_history">{{ trans('team::profile.Contract history') }}</button>--}}
                                @endif
                                <a href="
                                    @if (Auth()->id() == $employeeModelItem->id)
                                        {{ route('contract::contract.list') }}
                                    @else
                                        {{ route('contract::contract.employee-list', ['id' => $employeeModelItem->id ]) }}
                                    @endif
                                " class="btn btn-info" role="button">{{ trans('contract::vi.Contract list') }}</a>
                            @endif

                            @if (isset($employeeItemMulti))
                                <a type="button" href="{!!route('team::member.profile.index', ['employeeId' => $employeeModelItem->id, 'type' => $tabType])!!}" class="btn btn-primary margin-right-10">
                                    {!!trans('core::view.Back')!!}
                                </a>
                            @endif
                            @if($isAccessSubmitForm)
                                @if (isset($employeeItemMulti))
                                    @if (!$employeeItemMulti instanceof \Rikkei\Team\Model\EmployeeAttach || !$employeeItemMulti->required)
                                        <button type="button" class="btn btn-danger margin-right-10{!!$employeeItemMulti->id ? '' : ' hidden'!!}"
                                            data-btn-submit="ajax" data-flag-dom="btn-employee-remove-item"
                                            data-submit-noti="{!!$deleteConfirmNoti!!}"
                                            action="{!!route('team::member.profile.item.relate.delete', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $employeeItemMulti->id])!!}">
                                            {!!trans('core::view.Remove')!!}
                                        </button>
                                    @endif
                                @endif
                                <button type="submit" class="btn btn-primary">
                                    {!!trans('team::view.Save')!!}
                                    <i class="fa fa-spin fa-refresh loading-submit hidden"></i>
                                </button>
                                <?php
                                  $checkCreate = strpos(\Request::url(), 'create');
                                ?>
                                <button type="button" class="btn btn-primary btn-edit-profile {{ $checkCreate ? 'hidden' : '' }}">
                                        {!!trans('team::view.Edit')!!}
                                </button>
                            @endif
                        </div>
                    </div>
                    
                </div>
                <div class="box-body">
                   @yield('content_profile')
                </div>
                @if($isAccessSubmitForm || (!empty($isScopeTeam) && $isScopeTeam))
                    <div class="box-footer">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">
                                    {!!trans('team::view.Save')!!}
                                    <i class="fa fa-spin fa-refresh loading-submit hidden"></i>
                                </button>
                                @yield('more_btn_submit')
                            </div>
                        </div>
                    </div>
                @endif
                <div class="box-body">
                    @yield('after_content_profile')
                </div>
            </div>
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
        urlFileNotAvai: '{!!asset('common/images/file-not-avai.png')!!}',
        tabType: '{!!$tabType!!}',
        isSelfProfile: {!!$isSelfProfile ? 1 : 0!!},
        skillTypeAnother: '{!!Skill::TYPE_OTHER!!}',
        trans: {
            base_passport_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::view.Passport start')])!!}',
            offical_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::view.Join date')])!!}',
            trial_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::view.Join date')])!!}',
            trial_end_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::view.Join date') . ', ' . trans('team::view.Trial date')])!!}',
            leave_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::view.Join date')])!!}',
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
        }
    };
</script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/localization/messages_vi.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
@yield('profile_js_file')
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
@yield('profile_js_custom')
@if (isset($employeeItemMulti) && $employeeItemMulti->id)
    <script src="{{ CoreUrl::asset('team/js/view.js') }}"></script>
@endif
@endsection
