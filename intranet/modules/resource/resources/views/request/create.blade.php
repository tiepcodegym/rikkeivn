@extends('layouts.default')
<?php
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\View\View as ViewResource;
use Rikkei\Sales\View\CssPermission;
use Rikkei\Team\View\TeamList;
use Rikkei\Resource\Model\RequestPriority;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Team\View\Permission;

$benefits = ResourceRequest::BENEFITS;
?>
@section('title')
@if (isset($request) && $request)

{{ trans('resource::view.Request.Create.Update request') }}

@else
{{ trans('resource::view.Request.Create.Create request') }}
@endif
@endsection
<?php
if (isset($request) && $request) {
    $checkEdit = true;
    $urlSubmit = route('resource::request.postCreate', ['id' => $request->id]);
} else {
    $checkEdit = false;
    $urlSubmit = route('resource::request.postCreate');
}

$teamsOptionAll = TeamList::toOption(null, true, false);
$configApprove = CoreConfigData::getAccountToEmail(1, CoreConfigData::AUTO_APPROVE_KEY);
$lang = Session::get('locale');
?>

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('resource/css/resource.css') }}" />
@endsection

@section('content')
<div class="css-create-page request-create-page">
    <div class="css-create-body">
        <form id="form-create-request" class="form-horizontal" method="post" action="{{$urlSubmit}}" enctype="multipart/form-data" autocomplete="off">
        <div class="box box-primary padding-bottom-30">
            <input type="hidden" value="{{ (isset($teams) && isset($teams[0]) && isset($teams[0]['pos_selected']))? $teams[0]['pos_selected'] : ''}}" name="position">
            @if($checkEdit)
                <input type="hidden" name="request_id" value="{{$request->id}}">
            @endif
            {!! csrf_field() !!}
            @if($checkEdit && isset($isCoo) && $isCoo)
            <div class="box-header with-border">
                <h4 class="box-title with-border">{{trans('resource::view.Request.Create.Request detail')}}</h4>
            </div>
            @endif
            <div class="detail">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Title')}}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-8">
                                <span>
                                    <input id="title" name="title" type="text" class="form-control" value="{{$checkEdit ? trim($request->title) : ''}}" tabindex='1' />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-4 control-label">{{trans('resource::view.Request.Create.Customer')}}</label>
                            <div class="col-md-8">
                                <span>
                                    <input type='text' class="form-control" id="customer" name="customer" tabindex=2 value="{{$checkEdit ? trim($request->customer) : ''}}" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.IsHot')}}
                            </label>
                            <div class="col-md-8">
                                <label class="control-label">
                                    <input name="is_hot" type="checkbox" @if($checkEdit&& $request->is_hot) checked @endif value="1">
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Priority')}}
                            </label>
                            <div class="col-md-8">
                                <select type='text' class="form-control" name="priority" id="priority">
                                    @foreach($priorityOption as $priority)
                                        <option  value="{{ $priority->id }}" @if($checkEdit && $priority->id == $request->priority_id) selected @elseif(!$checkEdit && $priority->id == RequestPriority::PRIORITY_NORMAL) selected @endif>{{ RequestPriority::getNameLang($priority, $lang) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="select_team" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Team')}}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-8">
                                <span>
                                    <input type="text" class="form-control select-team cursor-pointer bg-white" readonly="true" value="{{$checkEdit ? $teamsSelected->team_selected : trans('resource::view.Select team')}}" >
                                    <input type="text" id="select_team" name="select_team" style="opacity:0; width:0;" value="{{$checkEdit ? $teamsSelected->team_selected : ''}}" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="languages" class="col-md-4 control-label">{{trans('resource::view.Request.Create.Languages')}}</label>
                            <div class="col-md-8">
                                <span>
                                    <select id="languages" name="languages[]" class="form-control width-93 multiple_select" multiple="multiple">
                                        @foreach($langs as $lang)
                                            <option value="{{ $lang->id }}" @if(isset($allLangs) && in_array($lang->id, $allLangs)) selected @endif>{{ $lang->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" style="opacity: 0; position: absolute" value="{{isset($allLangs) ? 1 : ''}}" id="chk_lang" name="chk_lang" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="deadline" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Deadline')}}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-8">
                                <span>
                                    <input type='text' autocomplete="off" class="form-control date" id="deadline" name="deadline" data-provide="datepicker" placeholder="YYYY-MM-DD" tabindex=6 value="{{$checkEdit ? $request->deadline:''}}" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="programs" class="col-md-4 control-label">{{trans('resource::view.Request.Create.Programming languages')}}</label>
                            <div class="col-md-8">
                                <span>
                                    <select id="programs" name="programs[]" class="form-control width-93 multiple_select" multiple="multiple">
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}" @if(isset($allProgrammingLangs) && in_array($program->id, $allProgrammingLangs)) selected @endif>{{ $program->name }}</option>
                                        @endforeach
                                    </select>
                                </span>
                                <input type="text" style="opacity: 0; position: absolute" value="{{isset($allProgrammingLangs) ? 1 : ''}}" id="chk_pro" name="chk_pro" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="onsite" class="col-md-4 control-label">{{trans('resource::view.Request.Create.Onsite')}}</label>
                            <div class="col-md-8">
                                <span>
                                    <select id="onsite" name="onsite" class="form-control width-93 select2-hidden-accessible select-search">
                                        <option value="0">{{ trans('resource::view.Request.Create.Select Yes No') }}</option>
                                        @foreach($onsiteOption as $onsite)
                                            <option value="{{ $onsite['id'] }}" @if($checkEdit && $onsite['id'] == $request->onsite) selected @endif>{{ $onsite['name'] }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="salary" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Salary')}}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-8">
                                <span>
                                    <input type='text' class="form-control" id="salary" name="salary" tabindex=7 value="{{$checkEdit ? trim($request->salary) : ''}}" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="start_working" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Start working')}}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-8">
                                <span>
                                    <input type='text' autocomplete="off" class="form-control date" id="start_working" name="start_working" data-provide="datepicker" placeholder="YYYY-MM-DD" tabindex=8 value="{{$checkEdit ? $request->start_working:''}}" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10 end-working-container"
                             @if (!($checkEdit && $request->onsite == getOptions::ONSITE_ON)) style="display:none;" @endif>

                            <label for="end_working" class="col-md-4 control-label">{{trans('resource::view.Request.Create.End working')}}</label>
                            <div class="col-md-8">
                                <span>
                                    <input type='text' autocomplete="off" class="form-control date" id="end_working" name="end_working" data-provide="datepicker" placeholder="YYYY-MM-DD" tabindex=9 value="{{$checkEdit ? $request->end_working:''}}" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="languages" class="col-md-4 control-label">{{trans('resource::view.Request.Create.Location')}}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-8">
                                <span>
                                    <select name="location" class="form-control width-93 location">
                                        <option value="0">{{ trans('resource::view.Request.Create.Select Location') }}</option>
                                        @foreach($places as $key => $place)
                                            <option value="{{ $key }}" @if(isset($request->location) && $key == $request->location) selected @endif>{{ $place }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="request_date" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Request date')}}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-8">
                                <span>
                                    <input type='text' class="form-control date" id="request_date" name="request_date" data-provide="datepicker" placeholder="YYYY-MM-DD" tabindex=4 value="{{$checkEdit ? $request->request_date: date('Y-m-d')}}" />
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="interviewer" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Interviewer')}}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-8">
                                <span>
                                    <select id="interviewer" name="interviewer[]" class="form-control width-93" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}" multiple="multiple">
                                        @if ($checkEdit && $interviewers)
                                            @foreach ($interviewers as $interviewer)
                                            <option value="{{ $interviewer->id }}" selected="">{{ ViewHelper::getNickName($interviewer->email) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </span>
                                <input type="text" style="opacity: 0; position: absolute" value="{{isset($request->interviewer) ? 1 : ''}}" id="chk_interviewer" name="chk_interviewer" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="saler" class="col-md-4 control-label">{{trans('resource::view.Request.Create.Saler')}}</label>
                            <div class="col-md-8">
                                <span>
                                    <select id="saler" name="saler" class="form-control width-93" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                        @if ($checkEdit && $saler)
                                        <option value="{{ $saler->id }}">{{ ViewHelper::getNickName($saler->email) }}</option>
                                        @endif
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="status" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Status')}}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-8">
                                <span>
                                    <select id="status" name="status" class="form-control width-93 select2-hidden-accessible select-search">
                                        @foreach($statusOption as $status)
                                            <option value="{{ $status['id'] }}" @if($checkEdit && $status['id'] == $request->status) selected @endif>{{ $status['name'] }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="effort" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Effort')}}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-8">
                                <span>
                                    <select id="effort" name="effort" class="form-control width-93 select2-hidden-accessible select-search">
                                        <option value="0">{{ trans('resource::view.Request.Create.Select effort') }}</option>
                                        @foreach($effort as $eff)
                                            <option value="{{ $eff['id'] }}" @if($checkEdit && $eff['id'] == $request->effort) selected @endif>{{ $eff['name'] }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="type" class="col-md-4 control-label">{{trans('resource::view.Candidate type')}} <em class="required" aria-required="true">*</em></label>
                            <div class="col-md-8">
                                <span>
                                    <select id="type" name="typecandidate[]" class="form-control width-93" multiple="multiple">
                                        @foreach($typeOptions as $type)
                                            <option value="{{ $type['id'] }}" @if(!empty($typeOfRq) && in_array($type['id'], $typeOfRq)) selected @endif>{{ $type['name'] }}</option>
                                        @endforeach
                                    </select>
                                </span>
                                <input type="text" style="opacity: 0; position: absolute" value="{{isset($typeOfRq) ? 1 : ''}}" id="chk_type" name="chk_type" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="recruiter" class="col-md-4 control-label">{{trans('resource::view.Candidate.Recruiter')}} <em class="required" aria-required="true">*</em></label>
                            <div class="col-md-8">
                                <span>
                                    <select id="recruiter" name="recruiter" class="form-control width-93 select2-hidden-accessible">
                                        <option value="0">{{trans('resource::view.Candidate.Create.Select recruiter')}}</option>
                                        @foreach ($hrAccounts as $nickname => $email)
                                            <option value="{{$email}}" @if ($checkEdit && $email == $request->recruiter) selected @endif>{{$email}}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="set_description" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Description')}}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-8">
                                <span>
                                    <textarea rows="4" class="form-control col-md-8" id="description" name="description" tabindex=10>{{$checkEdit ? trim($request->description) : ''}}</textarea>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="set_job_qualifi" class="col-md-4 control-label">
                                {{trans('resource::view.Request.Create.Job qualifications')}}
                            </label>
                            <div class="col-md-8">
                            <span>
                                <textarea rows="5" class="form-control col-md-8" id="job_qualifi" name="job_qualifi" tabindex=10 >{{$checkEdit ? $request->job_qualifi: ''}}</textarea>
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group margin-top-10">
                        <label for="set_benefit" class="col-md-4 control-label">
                            {{trans('resource::view.Request.Create.Benefits')}}
                            <em class="error">*</em>
                        </label>
                        <div class="col-md-8">
                            <span>
                                <textarea rows="5" class="form-control col-md-8" id="benefits" name="benefits" tabindex=10 >{{$checkEdit ? $request->benefits: $benefits}}</textarea>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group margin-top-10">
                        <label for="note" class="col-md-4 control-label">{{trans('resource::view.Request.Create.Note')}}</label>
                        <div class="col-md-8">
                                <span>
                                    <textarea rows="5" class="form-control col-md-8" id="note" name="note" tabindex=10>{{$checkEdit ? $request->note: ''}}</textarea>
                                </span>
                        </div>
                    </div>
                </div>
            </div>

            @if (isset($canEdit) && $canEdit)
            <div class="row">
                <div class="col-md-12 align-center margin-top-40">
                    <button type="button" class="btn btn-primary btn-submit-confirm" >{{trans('resource::view.Request.Create.Submit')}}</button>
                    @if (isset($checkEdit)
                        && $checkEdit
                        && $request->status == getOptions::STATUS_INPROGRESS
                        && $request->deadline >= date('Y-m-d')
                        && Permission::getInstance()->isAllow('resource::request.postDataRequest'))
                        <button type="button" title="{{ trans('resource::view.Publish this request to webvn') }}" class="btn btn-success btn-publish-request">
                            @if ($request->published)
                            {{trans('resource::view.Request.Create.Published')}}
                            @else
                            {{trans('resource::view.Request.Create.Publish')}}
                            @endif
                        </button>
                    @endif
                </div>
            </div>
            @endif

       </div>
        <!-- modal submit cofirm -->
        <div class="modal fade modal-warning" id="modal-submit-confirm"  role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ Lang::get('resource::view.Are you sure submit request?') }}</p>
                        <p class="text-change"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-close pull-left" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                        <button type="submit" class="btn btn-outline btn-ok" data-publish = {{ (isset($checkEdit) && $checkEdit) ? $request->published : '' }}>{{ Lang::get('core::view.OK') }}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div> <!-- modal submit cofirm -->

        <div class="modal fade modal-warning" id="modal-publish-request"  role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">Publish to Webvn</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ Lang::get('resource::view.Unsaved data will not be published.') }}</p>
                        <p class="text-change"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-close pull-left" data-dismiss="modal">{{ Lang::get('core::view.Cancel') }}</button>
                        <button type="button" class="btn btn-outline btn-publish-recruitment">{{ Lang::get('core::view.OK') }}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div> <!-- modal submit cofirm -->
    </form>
   </div>

</div>
<div class="hidden end-working-container-fake">
    <label for="name" class="col-md-4 control-label">{{trans('resource::view.Request.Create.End working')}}</label>
    <div class="col-md-8">
        <span>
            <input type='text' autocomplete="off" class="form-control date" id="end_working" name="end_working" data-provide="datepicker" placeholder="YYYY-MM-DD" tabindex=7 value="{{$checkEdit ? $request->end_working:''}}" />
        </span>
        &nbsp;<span class="required" aria-required="true">(*)</span>
    </div>
</div>
<!-- INCLUDE MODAL TEAMS OF REQUEST -->
@if (isset($checkEdit) && $checkEdit)
<div id="input_value" class="hidden">
    <input type="hidden" id="title" value="{{ $request->title }}" />
    <input type="hidden" id="is_hot" value="{{ $request->is_hot }}" />
    <input type="hidden" id="deadline" value="{{ $request->deadline }}" />
    <input type="hidden" id="request_date" value="{{ $request->request_date }}" />
    <input type="hidden" id="location" value="{{ $request->location }}" />
    <input type="hidden" id="salary" value="{{ $request->salary }}" />
    <input type="hidden" id="description" value="{{ $request->description }}" />
    <input type="hidden" id="benefits" value="{{ $request->benefits }}" />
    <input type="hidden" id="job_qualifi" value="{{ $request->job_qualifi }}" />
    <input type="hidden" id="status" value="{{ $request->status }}" />
    @if (count($allProgrammingLangs))
        <input type="hidden" id="programs" value="{{ implode(',', $allProgrammingLangs) }}" />
    @endif
    @if (count($typeOfRq))
        <input type="hidden" id="types" value="{{ implode(',', $typeOfRq) }}" />
    @endif
</div>
@endif
    @include ('resource::request.include.add_team_request')

@endsection

@section('script')
<script>
//define texts
var requiredText = '{{trans('resource::message.Required field')}}';
var checkWorkingDate = '{{trans('resource::message.End working has greater than start working')}}';
var checkDeadlineDate = '{{trans('resource::message.Deadline date has greater than request date')}}';
var onsiteOn = {{getOptions::ONSITE_ON}};
var statusPublished = {{ ResourceRequest::PUBLISHED }};
var _token = '{{ csrf_token() }}';
var urlPostRequest = '{{ route("resource::request.postDataRequest") }}';
var urlPostRequestRecruitment = '{{ route("resource::request.postDataRequestRecruitment") }}';
@if ($checkEdit)
    var requestId = {{$request->id}};
    var urlSend = '{{ url('resource/request/data-request', $request->id) }}';
    var checkEdit = 1;
@else
    var checkEdit = 0;
@endif
var updateContentText = '{{trans("resource::view.Update content")}}';
var addContentText = '{{trans("resource::view.Add content")}}';
var chooseEmployeeText = '<?php echo trans("resource::view.Choose employee") ?>';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="{{ CoreUrl::asset('resource/js/candidate/select2.common.js') }}"></script>
<script src="{{ CoreUrl::asset('resource/js/candidate/common.js') }}"></script>
<script src="{{ CoreUrl::asset('resource/js/request/create.js') }}"></script>
<script src="{{ CoreUrl::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script>
$('#team_id').select2();
$('#languages').multiselect({
    enableFiltering: true,
    enableCaseInsensitiveFiltering: true,
    buttonText: function(options, select) {
        return customBtnText(options);
    },
});
$('#programs').multiselect({
    enableFiltering: true,
    enableCaseInsensitiveFiltering: true,
    buttonText: function(options, select) {
        return customBtnText(options);
    },
});
$('#type').multiselect();
select2Employees('#saler');
select2Employees('#interviewer');
$('#recruiter').select2();

jQuery(document).ready(function($) {
    selectSearchReload();
    RKfuncion.CKEditor.init(['content']);
});
@if(isset($create))
    $('#status').prop('disabled', true);
@endif
//If edit then view only
@if(isset($canEdit) && !$canEdit)
    $('.detail input').prop('disabled', true);
    $('.detail select').prop('disabled', true);
    $('.detail textarea').prop('disabled', true);
    $('#programs').multiselect('disable');
    $('#languages').multiselect('disable');
    $('.detail .required').remove();
@endif
</script>
@endsection