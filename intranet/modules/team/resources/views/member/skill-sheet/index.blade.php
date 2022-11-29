<?php
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\Model\EmployeeProjExper;

$candidateAs = Candidate::getCandidate($employeeModelItem->id);
$dataRoles = EmployeeProjExper::listRoles();
$valueTransRole = [];
foreach ($skillsProj as $projId => $proj) {
    $valueTransRole[$projId] = isset($proj['role']) ? reset($proj['role']) : [];
}
?>
@extends('layouts.default')

@section('body_class', 'page-cv')

@section('title')
{{ trans('team::view.Skill sheet of :employeeName', ['employeeName' => $employeeModelItem->name]) }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="{{ URL::asset('lib/tag-it/css/jquery.tagit.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('lib/tag-it/css/tagit.ui-zendesk.min.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('team/css/ss.css') }}" />
@endsection

@section('content')
<script>
var globalValueTrans = {
    res: {},
    role: {!! json_encode($valueTransRole, JSON_HEX_TAG) !!},
};
</script>
@if (isset($candidateAs) && $candidateAs->id)
<div class="row">
     <a href="{!!route('resource::candidate.detail', ['id'=>$candidateAs->id])!!}" target="_blank" style="float: right; padding: 0px 30px 0px 10px;">Candidate detail</a><i class="fa fa-yelp" style="float: right;"></i>
</div>
@endif
<div class="container-fluid">
<div class="row">
    {!! csrf_field() !!}
    <div class="col-lg-2 col-md-3">
        @include('team::member.left_menu',['active' => $tabType])
    </div>
    <div class="col-lg-10 col-md-9">
        <form action="{!!route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => $tabType])!!}"
            autocomplete="off" method="post" id="form-employee-cv">
            <div class="overlay-par hidden"></div>
            <div class="box box-info">
                <div class="row sks-header">
                    <div class="col-md-8">
                        <ol class="steps-ui">
                            <li{!! $employeeCvEav->getVal('status') == EmplCvAttrValue::STATUS_SUBMIT ? ' class="current"' : '' !!}><strong>{{ trans('team::view.Submitted') }}</strong></li>
                            <li{!! $employeeCvEav->getVal('status') == EmplCvAttrValue::STATUS_FEEDBACK ? ' class="current"' : '' !!}><strong>{{ trans('team::view.Feedback') }}</strong></li>
                            <li{!! $employeeCvEav->getVal('status') == EmplCvAttrValue::STATUS_APPROVE ? ' class="current"' : '' !!}><strong>{{ trans('team::view.Approved') }}</strong></li>
                        </ol>
                        <div class="multi-lang">
                            <div class="btn-group" data-toggle="buttons">
                                <label class="btn btn-default active form-check-label">
                                    <input value="en" class="form-check-input" type="radio" name="cv_view_lang" autocomplete="off"> EN
                                </label>
                                <label class="btn btn-default form-check-label">
                                    <input value="ja" class="form-check-input" type="radio" name="cv_view_lang" autocomplete="off"> JP
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 margin-top-10">
                        <div class="pull-right margin-right-10">
                            <button type="button" class="btn btn-primary" id="btn_export_cv"
                                    data-url="{{ route('team::member.profile.skillsheet.export', ['employeeId' => $employeeModelItem->id]) }}">
                                <i class="fa fa-download"></i> {{ trans('team::view.Export') }}
                            </button>
                            @if ($isAccess)
                                <button type="button" class="btn btn-primary" data-btn-submit="2">{{ trans('team::view.Submit') }}</button>
                            @endif
                            @if ($isAccessTeamEdit)
                                <button type="button" class="btn btn-danger" id="fb-ss">{{ trans('team::view.Feedback') }}</button>
                                <button type="button" class="btn btn-success" data-btn-submit="3">{{ trans('team::view.Approve') }}</button>
                            @endif
                        </div>
                    </div>
                </div>
                @if ($employeeCvEav->getVal('status') == EmplCvAttrValue::STATUS_SUBMIT || $employeeCvEav->getVal('status') == EmplCvAttrValue::STATUS_APPROVE)
                <div class="row" d-dom-fg="box-approve">
                    <div class="col-md-6">
                        <div class="form-horizontal ss-box-assign" action="{!!route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => 'approveSkillChange'])!!}" method="post">
                            <p class="input-group form-group-select2">
                                <label class="input-group-addon ng-binding">Approver</label>
                                <select name="assignee" data-select2-dom="1" data-old-value="{!!$approver->id!!}"
                                    data-select2-url="{!!route('team::employee.list.search.external.ajax')!!}?type=1"
                                    d-ss-dom="select-assign" class="hidden"{{$isAccessTeamEdit ? '' : ' disabled'}}>
                                    <option value="{!!$approver->id!!}" selected>{{$approver->name . ' ('.CoreView::getNickname($approver->email) .')'}}</option>
                                </select>
                                @if ($isAccessTeamEdit)
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-success" d-ss-btn="approver"
                                        data-action="{!!route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => 'changeApprover'])!!}">
                                        <i class="fa fa-spin fa-refresh hidden loading-submit"></i>
                                        <i class="fa fa-user fa-user-assignee loading-hidden-submit"></i> {{ trans('team::view.Assign') }}
                                    </button>
                                </span>
                                @endif
                            </p>
                            <label class="error hidden" d-ss-error="select-assign"></label>
                        </div>
                    </div>
                </div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div class="box-header with-border text-center">
                            <h2 class="box-title">{!!trans('team::profile.Developer Skill Sheet')!!}</h2>
                            <a href="{!!route('help::display.help.view', ['id' => 'profile-khai-bao-skillsheet'])!!}" target="_blank" title="help">
                                <i class="fa fa-fw fa-question-circle" style="font-size: 18px;"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="nav-tabs-custom tab-danger">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="active">
                            <a href="#cv-tab-general" aria-controls="cv-tab-general" role="tab" data-toggle="tab"
                               aria-expanded="false">{!!trans('team::profile.Summary')!!}
                            </a>
                        </li>
                        <li>
                            <a href="#cv-tab-proj" aria-controls="cv-tab-proj" role="tab" data-toggle="tab"
                               aria-expanded="false" data-tab-title="cv-proj">{!!trans('team::profile.Project')!!}</a>
                        </li>
                        <li>
                            <a href="#cv-tab-skill" aria-controls="cv-tab-skill" role="tab" data-toggle="tab"
                               aria-expanded="false">{!!trans('team::profile.Skills')!!}</a>
                        </li>
                        @if (!$disabledInput)
                            <li>
                                <a href="#cv-tab-import" aria-controls="cv-tab-import" role="tab" data-toggle="tab"
                                   aria-expanded="false">{!!trans('team::profile.Import')!!}</a>
                            </li>
                        @endif
                        <li>
                            <a href="#cv-note" aria-controls="cv-tab-skill" role="tab" data-toggle="tab"
                               aria-expanded="false">{!!trans('team::profile.Note')!!}</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" role="tabpanel" id="cv-tab-general">
                            @include('team::member.skill-sheet.baseinfo')
                        </div>
                        <div class="tab-pane" role="tabpanel" id="cv-tab-proj">
                            @include ('team::member.skill-sheet.project')
                        </div>
                        <div class="tab-pane" role="tabpanel" id="cv-tab-skill">
                            @include ('team::member.skill-sheet.skill')
                        </div>
                        @if (!$disabledInput)
                            <div class="tab-pane" role="tabpanel" id="cv-tab-import">
                                @include ('team::member.skill-sheet.import')
                            </div>
                        @endif
                        <div class="tab-pane" rote="tabpanel" id ="cv-note">
                            {!!trans('team::view.cv-note')!!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <p><strong>{!!trans('team::profile.Template')!!}</strong>:</p>
                            <ul>
                                <li>
                                    <a href="{!!asset('assets/cv/Rikkeisoft_Skillsheet_Nguyen_Van_A_template_jp.xlsx')!!}">skill_sheet_jp.xlsx</a>
                                </li>
                                <li>
                                    <a href="{!!asset('assets/cv/Rikkeisoft_Skillsheet_Nguyen_Van_A_template_en.xlsx')!!}">skill_sheet_en.xlsx</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <!-- comment feedback -->
        <form id="form-comment-feedback" method="post" action="{{ route('team::member.profile.skillsheet.feedback', ['employeeId' => $employeeModelItem->id]) }}"
            class="form-horizontal form-submit-ajax has-valid" autocomplete="off">
            {!! csrf_field() !!}
            @if ($accessView['view'])
                <input type="hidden" name="accessView[view]" value="{{ $accessView['view'] }}">
                <input type="hidden" name="accessView[approver]" value="{{ $accessView['approver'] }}">
            @endif
            <input type="hidden" name="employeeId" value="{{ $employeeModelItem->id }}" id="employeeId">
            <input type="hidden" name="_token" id="token-comment" value="{{ csrf_token() }}">
            <div id="comments">
                <div class="box box-solid box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('team::view.Comments') }}</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                        <!-- /.box-tools -->
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-10">
                                <textarea name="skc[content]" class="form-control text-resize-y" rows="3" id="ss-comment"></textarea>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary" type="submit" id="add-comment-feedback">{{ trans('project::view.Add') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                        <br/><br/>
                        <div class="grid-data-query" data-url="{!! route('team::member.profile.skillsheet.comment.list.ajax', ['employeeId' => $employeeModelItem->id]) !!}">
                            <div class="grid-data-query-table">
                                @include('team::include.comment.comment_list', ['collectionModel' => $collectionModel])
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </form>
    </div>
</div>
</div>

<div id="feedback-modal" class="modal in">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-review-feedback" method="post" action="{{ route('team::member.profile.skillsheet.feedback', ['employeeId' => $employeeModelItem->id]) }}"
                  class="form-horizontal form-submit-ajax has-valid" autocomplete="off">
                {!! csrf_field() !!}
                <input type="hidden" name="employeeId" value="{{ $employeeModelItem->id }}">
                <input type="hidden" name="save_type" value="{{ EmplCvAttrValue::STATUS_FEEDBACK }}">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('project::view.Feedback Content') }}</h4>
                </div>
                <div class="modal-body">
                    <textarea rows="5" name="fb" class="form-control"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('project::view.Close') }}</button>
                    <button type="submit" class="btn btn-danger" data-btn-feedback="1">{{ trans('project::view.Feedback') }}
                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
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
    var globalPassModule = {
        tabType: '{!!$tabType!!}',
        isSelfProfile: {!!$isSelfProfile ? 1 : 0!!},
        urlSaveCv: '{!!route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => 0])!!}',
        urlRemoteos: '{!!route('tag::search.tag.select2', ['fieldCode' => 'os'])!!}',
        urlRemotelang: '{!!route('tag::search.tag.select2', ['fieldCode' => 'language-database'])!!}',
        urlRemotelanguage: '{!!route('tag::search.tag.select2', ['fieldCode' => 'language'])!!}',
        urlRemotedatabase: '{!!route('tag::search.tag.select2', ['fieldCode' => 'database'])!!}',
        isAccess: {!!(int) $isAccess!!},
        accessApprover: {!!$accessApprover ? 1 : 0!!},
        isAccessTeamEdit: {!!(int) $isAccessTeamEdit!!},
        cvXmlFile: {
            ja: '{!!asset('assets/cv/ja.xml')!!}',
            en: '{!!asset('assets/cv/en.xml')!!}',
        }
    },
    globalTrans = {
        en: {!!json_encode(trans('team::cv', [], '', 'en'))!!},
        ja: {!!json_encode(trans('team::cv', [], '', 'ja'))!!},
        more: {
            en: {
                greaterEqualThan: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::cv.start at', [], null, 'en')], null, 'en')!!}',
            },
        },
    },
    globalDataFixSelect = {
        res: {!!json_encode($projPosition, JSON_HEX_TAG)!!},
        role: {!! json_encode($dataRoles, JSON_HEX_TAG) !!},
    },
    globalDbTrans = {!!json_encode($employeeCvEav->eav, JSON_HEX_TAG)!!},
    globalTagData = {!!json_encode($tagData, JSON_HEX_TAG)!!},
    globalValidMess = {
        file_large: '{!!trans('validation.file_large_5')!!}',
        file_required: '{!!trans('validation.file_required')!!}',
        file_type: '{!!trans('validation.file_type')!!}',
        field_required: '{!!trans('validation.field_required')!!}',
        assign_same: '{!!trans('team::view.assign_same')!!}',
        confirm_delete: '{!!trans('core::view.confirm delete')!!}',
        success_delete: '{!!trans('core::view.Delete success')!!}',
        confirm_continue_import: '{!! trans('team::cv.confirm continue import') !!}',
    };
   var messageSubmit = '{!!trans('team::messages.Information edit must save before click submit')!!}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/blob-polyfill/2.0.20171115/Blob.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/javascript-canvas-to-blob/3.14.0/js/canvas-to-blob.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.min.js"></script>
<script src="{{ URL::asset('lib/tag-it/js/tag-it.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.1/shim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.1/xlsx.full.min.js"></script>
<script src="{{ CoreUrl::asset('team/js/ss.js') }}"></script>
@endsection
