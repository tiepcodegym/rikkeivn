@extends('layouts.default')
<?php

use Carbon\Carbon;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Resource\View\getOptions;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\CookieCore;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\View\View;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\Model\CandidateMail;
?>
@section('title')
{{ trans('resource::view.Candidate detail - :email', ['email' => $candidate->email]) }}
@endsection
<?php
if($candidate->programs_name) {
    $programName = str_replace('.00', '0', $candidate->programs_name).' ';
    $programName = str_replace('0 ', ' ', $programName);
    $programName = str_replace(',', trans('resource::view.year').',', $programName).' '.trans('resource::view.year');
} else {
    $programName = "";
}
$checkEdit = true;
$urlSubmit = route('resource::candidate.postCreate', ['id' => $candidate->id]);
$tabActive = CookieCore::get('tab-keep-status-candidate-'.$candidate->id);
if (!$tabActive) {
  $tabActive = 'tab_contact';
}
if ($employee){
    $workingType = $employee->working_type;
}
else {
    $workingType = '';
}
$permissMoreInfo = Permission::getInstance()->isAllow(getOptions::ROUTE_MORE_INFO);

$extraLinkHtml = '<ul>
        <li><a target="_blank" href="https://vn.rikkeisoft.com/">Trang thông tin tuyển dụng Rikkeisoft.</a></li>
        <li><a target="_blank" href="https://docs.google.com/spreadsheets/d/1mbraBIZb7C63tUBj0v4m9PH_8Frviwp3fk7ymKX1mOg/edit#gid=0">Tổng hợp báo chí viết về Rikkeisoft.</a></li>
        <li><a target="_blank" href="https://issuu.com/rikkeisoft">Nội san YUME.</a></li>
        <li><a target="_blank" href="https://soundcloud.com/rikkeisoft">YUME Radio.</a></li>
        <li><a target="_blank" href="https://www.facebook.com/rikkeisoft">Our Fanpage</a>, <a target="_blank" href="https://www.youtube.com/channel/UCg4sqAGemXn5basWdzxEbVg/featured">Our Youtube.</a></li>
        <li><a target="_blank" href="https://www.youtube.com/watch?v=IOR9J6gGQFo">Video giới thiệu công ty (version 2017).</a></li>
    </ul>';
$extraJapanLink = '<ul>
        <li><a href="https://rikkeisoft.com/about/">https://rikkeisoft.com/about/</a></li>
        <li><a href="https://www.facebook.com/rikkeijapan/">https://www.facebook.com/rikkeijapan/</a></li>
    </ul>';
$signatureHtml = isset($recruiter) ? view('resource::candidate.include.modal.signature', ['recruiter' => $recruiter])->render() : view('resource::candidate.include.modal.signature')->render();
$signatureJPHtml = view('resource::candidate.include.modal.signature-jp')->render();
$signatureHCMHtml = view('resource::candidate.include.modal.signature-hcm')->render();
$thanksHtml = view('resource::candidate.include.modal.thanks', ['data'=>$candidate])->render();
$isWorkingOrLeaveOff = in_array($candidate->status, [getOptions::LEAVED_OFF]);
//$isWorking = in_array($candidate->status, [getOptions::WORKING]);
// check disable khi working type not in học việc, mượn, thuê ngoài
$isWorking = $candidate->isWorking() && !in_array($candidate->working_type, array_keys(getOptions::listWorkingTypeExternal()));
$lastSendInterviewFail = CandidateMail::getLastSend($candidate->email, [
    Candidate::MAIL_INTERVIEW_FAIL_JP,
    Candidate::MAIL_INTERVIEW_FAIL_HN,
    Candidate::MAIL_INTERVIEW_FAIL_DN,
    Candidate::MAIL_INTERVIEW_FAIL_HCM
]);
$urlChangeStatus = route('resource::resource.changeStatus');
$interestedOptions = getOptions::listInterestedOptions();
$gender = '';
if($recruiter) {
    $gender = 'Ms';
    if ($recruiter->gender) {
        $gender = 'Mr';
    }
}
?>

@section('css')
<meta name="_token" content="{{ csrf_token() }}"/>
<link href="{{ CoreUrl::asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('resource/css/resource.css') }}" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="request-detail-page break-word">
    <div class="css-create-body candidate-detail-page">
        <div class="row position-relative padding-bottom-30">
            <div class="col-sm-2">
                <div class="callout margin-bottom-0 {{ getOptions::getClassCandidateStatus($candidate->status) }}">
                    <p class="text-center text-uppercase"><strong>{{ getOptions::getInstance()->getCandidateStatus($candidate->status, $candidate) }}</strong></p>
                </div>
            </div>
            <div class="col-sm-5 col-sm-offset-6 request-detail-link right-link">
                <a class="pull-right" target="_blank" href="{{ route('resource::candidate.edit', ['id'=>$candidate->id]) }}">
                    <span>
                        <i class="fa fa-hand-pointer-o"></i>
                        {{ trans('resource::view.Edit candidate') }}
                    </span>
                </a>
                @include('resource::candidate.include.apply_list', ['page' => 'detail'])
            </div>
        </div>

        <div class="row">
            <!-- Basic information -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h4 class="box-title">{{ trans('resource::view.Basic information') }}</h4>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 item-basic">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Candidate.Create.Fullname')}}: </label>
                                    <span> {{ $candidate->fullname }} </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 item-basic">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Candidate.Create.Email')}}: </label>
                                    <span id='email-candidate'> {{ $candidate->email }} </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 item-basic">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Candidate.Create.Mobile')}}: </label>
                                    <span> {{ $candidate->mobile }} </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 item-basic">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Candidate.Create.Birthday')}}: </label>
                                    <span> {{ $candidate->birthday }} </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 item-basic">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Gender')}}: </label>
                                    <span> {{ getOptions::getGender($candidate->gender) }} </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 item-basic">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Skype')}}: </label>
                                    <span> {{ $candidate->skype }} </span>
                                </div>
                            </div>
                            <div class="col-md-12 item-basic">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Other contact')}}: </label>
                                    <span class="content_br"> {{ $candidate->other_contact }} </span>
                                </div>
                            </div>
                        </div>
                        @if ($permissMoreInfo)
                        <div class="row">
                            <div class="col-md-12">
                                <button class="btn btn-info pull-right" data-toggle="modal" data-target="#modal-more-information">{{ trans('resource::view.View more information') }}</button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- ./ Basic information -->
            <!-- Experience information -->
             <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h4 class="box-title">{{ trans('resource::view.Experience information') }}</h4>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6 item-experience">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Candidate.Create.Languages')}}: </label>
                                    <span> {{ $candidate->lang_name }} </span>
                                </div>
                            </div>
                            <div class="col-md-6 item-experience">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Programming lang')}}: </label>
                                    <span> {{ $programName }} </span>
                                </div>
                            </div>
                            <div class="col-md-6 item-experience">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Candidate.Create.University')}}: </label>
                                    <span class="content_br">{{ $candidate->university }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 item-experience">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Candidate.Create.Certificate')}}: </label>
                                    <span class="content_br">{{ $candidate->certificate }} </span>
                                </div>
                            </div>
                            <div class="col-md-6 item-experience">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Old company')}}: </label>
                                    <span class="old-company content_br"> {{ $candidate->old_company }} </span>
                                </div>
                            </div>
                            <div class="col-md-6 item-experience">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{trans('resource::view.Candidate.Create.Experience')}}: </label>
                                    <span> {{ $candidate->experience }} </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./ Experience information -->
        </div>
        <div class="row">
            <!-- Request information -->
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h4 class="box-title">{{ trans('resource::view.Request information') }}</h4>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <input type="hidden" name="candidate_id" value="{{ $candidate->id }}">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{ trans('resource::view.Candidate.Create.Request', ['request'=> '']) }}: </label>
                                        <span>
                                        <?php
                                            if (!empty($candidate->requests)) :
                                                $strUrl = [];
                                                $requests = explode(CoreModel::GROUP_CONCAT, $candidate->requests);
                                                if (is_array($requests) && count($requests)) :
                                                    foreach ($requests as $requestStr) :
                                                        if (!empty($requestStr)) :
                                                            $requestInfo = explode(CoreModel::CONCAT, $requestStr);
                                                            if (is_array($requestInfo) && count($requestInfo)) :
                                                                $strUrl[] = "<a target='_blank' href='" . route('resource::request.detail', ['id' => $requestInfo[1]]) . "'>" . $requestInfo[0] . "</a>";
                                                            endif;
                                                        endif;
                                                    endforeach;
                                                endif;
                                                echo "<span><i class='fa fa-hand-pointer-o'></i></span> " . implode(', ', $strUrl);
                                            endif;
                                        ?>
                                        </span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{ trans('resource::view.Candidate.Create.Team') }}: </label>
                                    <span>{{ $candidate->team_name }}</span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{ trans('resource::view.Candidate.Create.Position apply') }}: </label>
                                        <span>
                                        <?php
                                            if (!empty($candidate->positions)) :
                                                $strPos = [];
                                                $positions = explode(',', $candidate->positions);
                                                if (is_array($positions) && count($positions)) :
                                                    foreach ($positions as $pos) :
                                                        $strPos[] = getOptions::getInstance()->getRole($pos);
                                                    endforeach;
                                                endif;
                                                echo implode(', ', $strPos);
                                            endif;
                                        ?>
                                        </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./ Request information -->
            <!-- Recruit information -->
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h4 class="box-title">{{ trans('resource::view.Recruit information') }}</h4>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 item-recruit">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{ trans('resource::view.Candidate.Create.Received CV date') }}: </label>
                                    <span> {{ $candidate->received_cv_date }} </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 item-recruit">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{ trans('resource::view.Recruiter') }}: </label>
                                    <span> {{ $candidate->recruiter }} </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 item-recruit">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{ trans('resource::view.Found by') }}: </label>
                                    <span> {{ $founder ? $founder->email : '' }} </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 item-recruit">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{ trans('resource::view.Candidate.Create.Channel') }}: </label>
                                    <span>
                                        @if ($channel && $channel->is_presenter && $presenter)
                                            {{ $channel->name . ' (' . $presenter->email . ')' }}
                                        @else
                                            {{ $channel ? $channel->name : '' }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 item-recruit">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{ trans('resource::view.CV') }}: </label>
                                    <span>
                                        @if ($candidate->cv)
                                        <?php
                                        $filename = substr($candidate->cv, strrpos($candidate->cv, '/') + 1);
                                        $filePath = $pathFolder . '/' .  $filename;
                                        $fileDiskPath = storage_path("app/" . Config::get('general.upload_storage_public_folder') ."/". Candidate::UPLOAD_CV_FOLDER . $filename);
                                        ?>
                                        <label class="filename">
                                            @if (file_exists($fileDiskPath))
                                            <a href="#" class="btn btn-primary" data-toggle="modal" onclick='viewFile({{ Candidate::TYPE_CV }}, "https://docs.google.com/gview?url={{ $filePath }}&embedded=true");'>{{ trans('resource::view.View CV') }}</a>
                                            <a class="btn btn-success" href="{{route('resource::candidate.viewcv', ['id'=>$candidate->id,'filename'=>$filename])}}">Download CV</a>
                                            @else
                                            <span class="error">{{ trans('resource::message.File does not exist') }}</span>
                                            @endif
                                        </label>
                                        @include('resource::candidate.include.modal.view_file')
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 item-recruit">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{ trans('resource::view.Screening') }}: </label>
                                    <span class="content_br">{{ $candidate->screening }} </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 item-recruit">
                                <div class="form-group position-relative ">
                                    <label class="control-label">{{ trans('resource::view.Type') }}: </label>
                                    <span> {{ Candidate::getType($candidate->type) }} </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./ Recruit information -->
        </div>

        <!-- TAB -->
        <?php
        $employeeStatus = getOptions::listEmployeeStatus();
        $employeeStatus[getOptions::END] = 'Pass';
        $hasTabEmployee = ((isset($employeeStatus[$candidate->status]) || $candidate->status == getOptions::LEAVED_OFF));
        ?>
        @if ($candidate->status != getOptions::DRAFT)
            <div class="nav-tabs-custom  tab-keep-status" data-type="candidate-{{$candidate->id}}">
                <ul class="nav nav-tabs" role="tablist">
                    <li <?php if($tabActive == 'tab_contact'): ?> class="active" <?php endif; ?>><a href="#tab_contact" data-toggle="tab" aria-expanded="true" aria-controls="tab_contact">{{trans('resource::view.Candidate.Detail.Contact')}}</a></li>
                    <li <?php if($tabActive == 'tab_test'): ?> class="active" <?php endif; ?>><a href="#tab_test" data-toggle="tab" aria-expanded="true" aria-controls="tab_test">{{trans('resource::view.Candidate.Detail.Test')}}</a></li>
                    <li <?php if($tabActive == 'tab_interview'): ?> class="active" <?php endif; ?>><a href="#tab_interview" data-toggle="tab" aria-expanded="false" aria-controls="tab_test">{{trans('resource::view.Candidate.Detail.Interview')}}</a></li>
                    <li <?php if($tabActive == 'tab_offer'): ?> class="active" <?php endif; ?>><a href="#tab_offer" data-toggle="tab" aria-expanded="false" aria-controls="tab_test">{{trans('resource::view.Candidate.Detail.Offer')}}</a></li>
                    @if ($hasTabEmployee)
                        <li @if ($tabActive == 'tab_employee') class="active" @endif><a href="#tab_employee" data-toggle="tab">{{ trans('resource::view.Employee information') }}</a></li>
                    @endif
                </ul>
                <div class="tab-content min-height-150">
                    <!-- tab contact -->
                    @include('resource::candidate.include.tab_contact')

                    <!-- tab test -->
                    @include('resource::candidate.include.tab_test')

                    <!-- tab interview -->
                    @include('resource::candidate.include.tab_interview')

                    <!-- tab offer -->
                    @include('resource::candidate.include.tab_offer')

                    <!-- tab employee information -->
                    @if ($hasTabEmployee)
                        @include('resource::candidate.include.tab_employee')
                    @endif
                </div>
                <!-- /.tab-content -->
            </div>
                <!-- /end TAB -->
        @endif
    </div>
</div>

@if ($permissMoreInfo)
<!--Model more information-->
<div class="modal fade" id="modal-more-information">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><strong>{{ trans('resource::view.Request.Detail.Candidate information') }}</strong></h4>
            </div>
            <div class="modal-body">
                <?php
                $fields = [
                    'identify' => trans('resource::view.Identify'),
                    'issued_date' => trans('resource::view.Issue Date'),
                    'issued_place' => trans('resource::view.Issue Place'),
                    'position_apply_input' => trans('resource::view.Position apply'),
                    'home_town' => trans('resource::view.Home town'),
                    'offer_salary_input' => trans('resource::view.Desired salary'),
                    'offer_start_date' => trans('resource::view.If recruited, when can you start the job'),
                    'had_worked' => trans('resource::view.Have you worked at our company'),
                    'channel_input' => trans('resource::view.Where did you hear about our recruitment'),
                    'relative_worked' => trans('resource::view.Your name or your relatives are working for our company')
                ]
                ?>
                @foreach($fields as $key => $label)
                <div class="form-group">
                    <label>{{ $label }}</label>
                    @if ($candidate->{$key})
                        <div class="well well-sm">{{ $candidate->{$key} }}</div>
                    @else
                        <div>&nbsp;</div>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn btn-default" data-dismiss="modal">{{ trans('resource::view.Close') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endif

<div class="modal" id="modal-notification">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Notification</h4>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal modal-warning" id="modal-notification-file-not-exists">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Notification</h4>
            </div>
            <div class="modal-body">
                <p>File not exists!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal modal-warning" id="modal-validate-interview-plan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Notification</h4>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal modal-warning" id="modal-mail-offer-confirm">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Notification</h4>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline btn-send">
                    <span>
                        {{ Lang::get('resource::view.Send') }}
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </span>
                </button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade modal-warning" id="modal-warn-confirm-submit" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('core::view.Confirm') }}</h4>
                <h4 class="modal-title-change"></h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ trans('core::view.Are you sure to do this action?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline btn-ok">OK</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<input type="hidden" id="load_invite" value="0" />
@endsection

@section('script')
<div class="cmt-wrapper hidden">
    <div class="item">
        <p class="author">
            <strong class="cmt-created_by"></strong>
            <i>{{ trans('project::view.at') }} <span class="cmt-created_at"><span></i>
        </p>
        <p class="comment white-space-pre"><p>
    </div>
</div>

<script>
    //define texts
    var candidateId = '{{ $candidate->id }}';
    var requiredText = '{{trans('resource::message.Required field')}}';
    var errorEasyMax = '{{trans('resource::message.Error max easy')}}';
    var errorMediumMax = '{{trans('resource::message.Error max medium')}}';
    var errorHardMax = '{{trans('resource::message.Error max hard')}}';
    var isDigits = '{{trans('resource::message.Is digits')}}';
    var createSuccess = '{{trans('resource::message.Create Success')}}';
    var updateSuccess = '{{trans('resource::message.Update Success')}}';
    var SystemError = '{{ trans('resource::message.System Error!') }}';
    var minDuration = '{{ trans('resource::message.Min Duration') }}';
    var overtimeTest = '{{ trans('resource::message.Overtime Test') }}';
    var minTotalEasyMediumHard = '{{ trans('resource::message.Min Total of Easy, Medium, Hard') }}';
    var invalidEmail = '{{trans('resource::message.Email is invalid')}}';
    var emailFormat = '{{trans('resource::message.Wrong Email Format')}}';
    var resultPass = {{getOptions::RESULT_PASS}};
    var resultFail = {{getOptions::RESULT_FAIL}};
    var urlMailOffer = '{{route("resource::candidate.sendMailOffer")}}';
    var urlMailThanks = '{{route("resource::candidate.sendMailThanks")}}';
    var mailTitleRequired = '{{ trans("resource::view.Mail title is required.") }}';
    var mailFailRequiredText = '{{ trans('resource::view.Mail offer.Warning content') }}';
    var mailFailText = '{{ trans("resource::view.An error occurred. Please try again.") }}';
    var mailSentText = '{{ trans('resource::view.Mail offer.Mail sent') }}';
    var mailSuccessText = '{{ trans('resource::view.Mail offer.Success content') }}';
    var sentMailNotice = '{{ trans('resource::view.* This candidate was sent mail') }}';
    var typeMailOffer = {{Candidate::MAIL_OFFER}};
    var typeMailOfferHH3 = {{Candidate::MAIL_OFFER_HH3}};
    var typeMailOfferHH4 = {{Candidate::MAIL_OFFER_HH4}};
    var typeMailOfferDN = {{Candidate::MAIL_OFFER_DN}};
    var typeMailOfferJP = {{Candidate::MAIL_OFFER_JP}};
    var typeMailOfferHandico = {{Candidate::MAIL_OFFER_HANDICO}};
    var typeMailTest = {{Candidate::MAIL_TEST}};
    var typeMailInterview = {{Candidate::MAIL_INTERVIEW}};
    var typeMailFailInterview = {{ Candidate::MAIL_INTERVIEW_FAIL }};
    var typeMailInterviewHH3 = {{Candidate::MAIL_INTERVIEW_TEST_HH3}};
    var typeMailInterviewHH4 = {{Candidate::MAIL_INTERVIEW_TEST_HH4}};
    var typeMailInterviewDN = {{Candidate::MAIL_INTERVIEW_TEST_DN}};
    var typeMailRecruiter = {{Candidate::MAIL_RECRUITER}};
    var typeMailThanks = {{Candidate::MAIL_THANKS}};
    var urlcheckExistEmpPropertyValue = "{{route('resource::candidate.checkExistEmpPropertyValue')}}";
    var urlPdfSave = "{{route('resource::candidate.pdfSave', ['id'=>$candidate->id])}}";
    var inviteHtmlGenerate = "<a href='#' data-toggle='modal' onclick='viewFile(\"{{ Candidate::TYPE_ATTACH }}\", \"https://docs.google.com/gview?url={{ $pathFolderAttach . '/' . View::getInviteLeterName($candidate->email)}}&embedded=true\");'>{{ Candidate::FILE_NAME_INVITE . '.pdf' }}</a>";
    var tutorial = '{{Candidate::FILE_NAME_TUTORIAL}}';
    var invite = '{{View::getInviteLeterName($candidate->email)}}';
    var startWorking = '{{$candidate->start_working_date ? $candidate->start_working_date : ""}}';
    var teamId = '{{$candidate->team_id ? $candidate->team_id : ""}}';
    var candidateStatus = '{{ $candidate->offer_result }}';
    var textFieldChangeOldEmp = '{!! trans("resource::view.text_alert_change_old_employee") !!}';

    // option offer, import employee
    var offerPassValue = {{ getOptions::RESULT_PASS }};
    var offerWorkingValue = {{ getOptions::RESULT_WORKING }};
    var offerDefaultValue = {{ getOptions::RESULT_DEFAULT }};
    var workingTypeTrainee = {{ getOptions::WORKING_INTERNSHIP }};
    var workingTypeProbation = {{ getOptions::WORKING_PROBATION }};
    var workingTypeBorrow = {{ getOptions::WORKING_BORROW }};
    var workingTypeSeasonal = {{ getOptions::WORKING_PARTTIME }};
    var workingTypeUnlimit = {{ getOptions::WORKING_UNLIMIT }};
    var typeNotRequireTrial = [{{ getOptions::WORKING_PARTTIME }}, {{ getOptions::WORKING_OFFICIAL }}, {{ getOptions::WORKING_UNLIMIT }}, {{ getOptions::WORKING_BORROW }}];
    var offerPassCollapse = $('#offer_pass_collapse');
    var employeeValidErrors = {{ Session::has('employee_errors') ? 1 : 0 }};
    var urlTestHistory = '{{route("resource::candidate.testHistory", ["email" => $candidate->email, "candidateId" => $candidate->id])}}';
    var urlCheckEmpMail = '{{ route("resource::candidate.check_employee_email") }}';
    var htmlRequired = '<em class="required" aria-required="true">*</em>';
    //Store position, team of request
    //var requestArray = <?php //echo json_encode($requestArray); ?>;
    var typeCv = {{ Candidate::TYPE_CV }};
    var empCodePrefix = '{{ $empCodePrefix }}';
    var isExtraEmpCode = '{{ isset(getOptions::extraEmpPrefix()[$candidate->working_type]) ? 1 : 0 }}';
    var workingPlaceHH3 = '{{ trans("resource::view.Working place") }}';
    var workingPlaceHH4 = '{{ trans("resource::view.Working place HH4") }}';
    var workingPlaceDN = '{{ trans("resource::view.Working place DN") }}';
    var workingPlaceHandico = '{{ trans("resource::view.Working place Handico") }}';
    var urlGetFormCalendar = '{{ route("resource::candidate.getFormCalendar") }}';
    var urlsaveCalendars = '{{ route("resource::candidate.saveCalendar") }}';
    var selectMeetingRoomText = '{{ trans('resource::view.Select meeting room') }}';
    var selectGuestText = '{{ trans('resource::view.Select guest') }}';
    var urlCheckRoomAvailable = '{{ route("resource::candidate.checkRoomAvailable") }}';
    var messageSaveCalendarSuccess = '{{ trans("resource::message.Meeting has been created/updated success") }}';
    var calendarId = '{{ $candidate->calendar_id }}';
    var eventId = '{{ $candidate->event_id }}';
    var messageCreateCalendarConfirm = '{{ trans("resource::view.Create a new google calendar meeting") }}';
    var messageUpdateCalendarConfirm = '{{ trans("resource::view.Update google calendar meeting") }}';
    var notFoundEventText = '{{ trans("resource::message.Event not found") }}';
    var errorOccurText = '{{ trans('resource::message.Error! An error occurred. Please try again later') }}';
    var errorNotCreatorText = '{{ trans("resource::view.Not the person who book the room of this candidate") }}';
    var errorStartDateBefore = '{{ trans("resource::message.End date must be greater than Start date") }}';
    var userNameCurrent = '{{ $curEmp->name }}';
    var userEmailCurrent = '{{ $curEmp->email }}';
    // chuyen email ve dang ten
    var emailCurrent = userEmailCurrent.slice(0, userEmailCurrent.indexOf('@'));
    var deleteCommentCandidate = '{{ route("resource::candidate.delete.comment") }}';
    var resultFail = '{{ getOptions::RESULT_FAIL }}';
    var _token = '{{ csrf_token() }}';
    var statusFail = {{ getOptions::FAIL_CDD }};
    var statusWorking = {{ getOptions::WORKING }};
    var statusPreparing = {{ getOptions::PREPARING }};
    var idPosDev = {{$idPosDev}};
    var requiredCmt = '{{ trans('resource::message.Kindly add comments') }}';
    var deleteCommentCandidateTextConfirm = '<?php echo trans("resource::message.Are you sure you want to delete comments?") ?>';
    var chooseEmployeeText = '<?php echo trans("resource::view.Choose employee") ?>';
    var errorTrialStartDateBefore = '{{ trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('resource::view.Start working date')]) }}';
    var errorTrialEndDateBefore = '{{ trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('resource::view.Trial work start date')]) }}';
    var errorOfficialDateBefore = '{{ trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('resource::view.Start working date')]) }}';
    var seasonalStartDate = '{!! trans('resource::view.Seasonal start date') !!}';
    var seasonalEndDate = '{!! trans('resource::view.Seasonal end date') !!}';
    var outsourcingStartDate = '{!! trans('resource::view.Outsourcing start date') !!}';
    var outsourcingEndDate = '{!! trans('resource::view.Outsourcing end date') !!}';
    var trialStartDate = '{!! trans('resource::view.Trial work start date') !!}';
    var trialEndDate = '{!! trans('resource::view.Trial work end date') !!}';
    var prefixPartner = '{!! getOptions::extraEmpPrefix()[getOptions::WORKING_BORROW ] !!}';
    var lastSendInterviewFail = '{!! $lastSendInterviewFail; !!}';
    var urlChangeStatus = '{{ $urlChangeStatus }}';
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script src="{{ CoreUrl::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('resource/js/candidate/select2.common.js') }}"></script>
<script src="{{ CoreUrl::asset('resource/js/candidate/google_calendar.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('lib/js/jquery.cookie.min.js') }}"></script>
<script src="{{ CoreUrl::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ CoreUrl::asset('lib/js/jquery.match.height.addtional.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script type="text/javascript" src="https://viralpatel.net/blogs/demo/jquery/jquery.shorten.1.0.js"></script>
<script src="{{ CoreUrl::asset('resource/js/candidate/common.js') }}"></script>
<script src="{{ CoreUrl::asset('resource/js/candidate/detail.js') }}"></script>
<script>
        collapseOfferResult();

        $('#candidate_request').multiselect({
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            buttonText: function(options, select) {
                return customBtnText(options);
            },
        });

        $('#request_id').multiselect({
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            buttonText: function(options, select) {
                return customBtnText(options);
            },
        });

        $('#test_type_id').multiselect({
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            onChange: function(option, checked, select) {
                var opselected = $(option).data('code');
                if(opselected == 'rikkei-code') {
                    if(checked == true) {
                        $("#level-rikkei-code").show()
                    } else if(checked == false) {
                        $("#level-rikkei-code").hide()
                    }
                }
            },
            buttonText: function(options, select) {
                return customBtnText(options);
            },
        })

        var workingType = {{ $employee ? $employee->working_type : $candidate->working_type }};
        var workingInternship = '{{ getOptions::WORKING_INTERNSHIP }}';
        jQuery(document).ready(function($) {
            RKfuncion.select2.init();
            //selectSearchReload();
            $(function() {
                $('.item-basic, .item-recruit, .item-experience').matchHeight();
            });
            $('#working_type').change();
        });
        $('#btn_view_request_asset').click(function (e) {
            e.preventDefault();
            var frm = $('#modal_request_asset iframe');
            frm.attr('src', frm.data('src'));
        });
        $('#btn_submit_employee').click(function (e) {
            var form = $(this).closest('form');
            if (!form.valid()) {
                return false;
            }
        });
</script>
<script type="text/javascript">
    var formCandidateValid;
    var dataLang = {
        'sZeroRecords': '{{ trans('welfare::view.sZeroRecords') }}',
        'sInfo': '{{ trans('welfare::view.sInfo') }}',
        'sInfoEmpty': '{{ trans('welfare::view.sInfoEmpty') }}',
    };
    if ($('#form-candidate-comment').length) {
        formCandidateValid = $('#form-candidate-comment').validate({
            rules: {
                'candidate_comment[content]' : "required",
            },
            messages: {
                'candidate_comment[content]' : requiredCmt,
            },
        });
    }

    jQuery(document).ready(function() {
        var change = $('#interview_result').val();
        if (change == resultFail) {
             $("#showStatus").css('display','none');
             $("#showStatusWorkDate").css('display','none');
        }

        if (change == resultPass) {
            $("#working_type_interview").rules( "add", {
                required: true,
                messages: {
                    required: requiredText,
                }
            });
        }

        $('.multiple_select').multiselect();
        $('#interview_result').on('change', function() {
            $('#working_type_interview_label').find('em').remove();
            $('#working_type_interview').rules("remove", "required");

            if ($(this).val() == resultFail) {
                $("#showStatus").css('display','none');
                $("#showStatusWorkDate").css('display','none');
            } else if($(this).val() == resultPass) {

                $('#working_type_interview_label').append('<em class="required" aria-required="true">*</em>');
                $("#working_type_interview").rules( "add", {
                    required: true,
                    messages: {
                        required: requiredText,
                    }
                });

                $("#showStatus").css('display','block');
                $("#showStatusWorkDate").css('display','block');
            } else {
                $("#showStatus").css('display','block');
                $("#showStatusWorkDate").css('display','block');
            }
        });

        $("#offer_date").rules( "add", {
            required: true,
            messages: {
                required: requiredText,
            }
        });


        $('#button-mail-interview').click(function () {
            let interviewPlan = $("#interview_plan").val();
            if (!interviewPlan) {
                var str = '<p>{{ trans('resource::message.Please enter interview plan') }}</p>';
                $('#modal-validate-interview-plan .modal-body').html(str);
                $('#modal-validate-interview-plan').modal('show');
            } else {
                $('#button-mail-interview').prop('onclick', showMail);
            }
        });

        function showMail() {
            var mailInterview = '{{ Candidate::MAIL_INTERVIEW }}';
            return ((typeof showMailContent == 'undefined') ? console.log('loading') : showMailContent(mailInterview, 'mail_interview'));
        }
    });

    function insertAtCursor(myField, myValue) {
        //IE support
        if (document.selection) {
            myField.focus();
            sel = document.selection.createRange();
            sel.text = myValue;
        }
        //MOZILLA and others
        else if (myField.selectionStart || myField.selectionStart == '0') {
            var startPos = myField.selectionStart;
            var endPos = myField.selectionEnd;
            myField.value = myField.value.substring(0, startPos)
                + myValue
                + myField.value.substring(endPos, myField.value.length);
            myField.setSelectionRange(startPos + 1, startPos + 1);
        } else {
            myField.value += myValue;
        }
    }

    RKfuncion.formSubmitAjax['commentSuccess'] = function (dom, data) {
        var created_by = userNameCurrent+' ('+emailCurrent+') ';
        $('div.cmt-wrapper strong.cmt-created_by').text(created_by);
        $('div.cmt-wrapper span.cmt-created_at').text(data.created_at);
        $('div.cmt-wrapper .comment').text($('#comment').val().trim());
        var commentHtml = $('.cmt-wrapper').html();
        $('.grid-data-query-table').prepend(commentHtml);
        $('#comment').val('');
        if (typeof formCandidateValid === 'object' && typeof formCandidateValid.resetForm === 'function') {
            formCandidateValid.resetForm();
        }
        var e = jQuery.Event("keypress");
        e.keyCode = $.ui.keyCode.ENTER;
        $('input[name="page"]').val(1).trigger(e);
    }
</script>
<script type="text/javascript">
    $('#offer_result').on('change', function () {
        var offer_result = $(this).find('option:selected').val();
        if (offer_result == resultFail) {
            $('#form-offering-candidate').find('em.required').addClass('hide');
            $('#start_working_date_collapse').collapse('hide');
            $('#trial_work_start_and_end_date_collapse').collapse('hide');
            $('#official_date_collapse').collapse('hide');
        } else {
            $('#form-offering-candidate').find('em.required').removeClass('hide');
            $('#start_working_date_collapse').collapse('show');
            $('#trial_work_start_and_end_date_collapse').collapse('show');
            $('#official_date_collapse').collapse('show');
        }
    })

    if ($('#offer_result option:selected').val() == resultFail) {
        $('#form-offering-candidate').find('em.required').addClass('hide');
        $('#start_working_date_collapse').collapse('hide');
        $('#trial_work_start_and_end_date_collapse').collapse('hide');
        $('#official_date_collapse').collapse('hide');
    } else {
        $('#form-offering-candidate').find('em.required').removeClass('hide');
        $('#start_working_date_collapse').collapse('show');
        $('#trial_work_start_and_end_date_collapse').collapse('show');
        $('#official_date_collapse').collapse('show');
    }

    $(document).ready(function() {

        $(".comment").shorten({
            "showChars" : 500,
            "moreText"  : "Xem thêm",
            "lessText"  : "Rút gọn",
        });

        @if ($candidate->is_old_employee)
            $('#is_old_member').click();
        @endif
    });
</script>
<script>
    // disabled if candidate worked
    var isWorkingOrLeaveOff = '{!! $isWorkingOrLeaveOff !!}';
    var isWorking = '{!! $isWorking !!}';
//    $(document).ready(function () {
//        if (isWorkingOrLeaveOff) {
//            $('#form-offering-candidate').find('input').attr('disabled', 'disabled');
//            $('#form-offering-candidate').find('select').attr('disabled', 'disabled');
//            $('#form-offering-candidate').find('textarea').attr('disabled', 'disabled');
//        }
//
//        if (isWorking) {
//            $('#form-offering-candidate').find('input.field-date-picker').attr('disabled', 'disabled');
//            $('#form-offering-candidate').find('select.select2-hidden-accessible').attr('disabled', 'disabled');
//            $('#form-offering-candidate').find('textarea').attr('disabled', 'disabled');
//        }
//    });

    // Delete space at the top of string when search
    $(document).on("keyup", '.multiselect-search', function (event) {
        if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
            return;
        }
        let string = $(this).val();
        if (string.charAt(0) === ' ')
            string = string.slice(1);
        $(this).val(function () {
            return string;
        });
    });

    $(document).ready(function () {
        var status = $('#employee_status').val();
        var candidateId = '{{ $candidate->id }}';
        var statusFail = '{{ Candidate::STATUS_OFFER_FAIL }}';
        var statusEmployeeFail = '{{ Candidate::STATUS_EMPLOYEE_FAIL }}';
        if(status == statusEmployeeFail) {
            change_status(statusFail, candidateId);
            $("#offer_result").val("2").change();
        }

        function change_status(statusFail, candidateId) {
            $.ajax({
                url: urlChangeStatus,
                method: "POST",
                data: {
                    statusFail: statusFail,
                    candidateId: candidateId,
                },
                success: function(data) {

                }
            });
            return false; // prevent from submit
        }
    });
</script>
@endsection
