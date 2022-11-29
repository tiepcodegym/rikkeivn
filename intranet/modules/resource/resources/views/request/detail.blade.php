@extends('layouts.default')
<?php

use Carbon\Carbon;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CookieCore;
use Rikkei\Resource\View\View;
use Rikkei\Team\Model\Employee;
use Rikkei\Resource\Model\RequestPriority;
use Rikkei\Core\View\CoreUrl;

$lang = Session::get('locale');
?>
@section('title')

{{ trans('resource::view.Request detail - :title', ['title' => $request->title])}}

@endsection
<?php
if (Permission::getInstance()->isAllow('resource::request.approved')
    || Permission::getInstance()->getEmployee()->id == $request->employees_assign) {
    $urlSubmit = route('resource::request.approved', ['id' => $request->id]);
} else if (Permission::getInstance()->isAllow('resource::request.assignee')
    || Permission::getInstance()->getEmployee()->id == $request->employees_assign) {
    $urlSubmit = route('resource::request.assignee', ['id' => $request->id]);
}
$urlGenerate = route('resource::request.generate');
// Get tab cookie
$tabActive = CookieCore::get('tab-keep-status-request-'.$request->id);
if (!$tabActive) {
  $tabActive = 'tab_1';
}
$checkEdit = true;
$today = Carbon::now()->format('Y-m-d');
?>

@section('css')
<meta name="_token" content="{{ csrf_token() }}"/>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('resource/css/resource.css') }}" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">

@endsection

@section('content')
<div class="css-create-page request-review-page">
    <div class="css-create-body">
        <div class="box box-body box-primary">
            @if (isset($urlSubmit))
            <form id="form-approve-request"  method="post" action="{{$urlSubmit}}" class="form-horizontal has-valid" autocomplete="off" novalidate="novalidate">
                {!! csrf_field() !!}
            @else
            <div class="form-horizontal">
            @endif
                <input type="hidden" name="request_id" value="{{$request->id}}">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="callout {{getOptions::getClassRequestStatus($request->status)}} status">
                            @if ($request->deadline < $today && $request->status == getOptions::STATUS_INPROGRESS)
                                <p class="text-center text-uppercase"><strong>{{ trans('resource::view.Request.Create.Expired') }}</strong></p>
                            @else
                                <p class="text-center text-uppercase"><strong>{{ getOptions::getInstance()->getStatus($request->status) }}</strong></p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="col-md-12">
                        <div class="form-group form-label-left">
                            <label for="title" class="col-sm-3 control-label required" aria-required="true">{{trans('resource::view.Request.Create.Title')}} </label>
                            <div class="col-md-9">
                                <p>{{$request->title}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group form-label-left form-group-select2">
                            <label for="approve" class="col-sm-3 control-label required" aria-required="true">{{trans('resource::view.Request.Detail.Approve status')}} <em>*</em></label>
                            <div class="col-md-9">

                                <select name="approve" class="select-search select2-hidden-accessible form-control" id="approve" tabindex="-1" aria-hidden="true"
                                   @if ((!(Permission::getInstance()->isAllow('resource::request.approved')
                                   || Permission::getInstance()->getEmployee()->id == $request->employees_assign)) || $request->approve != getOptions::APPROVE_YET || $request->status == getOptions::STATUS_CANCEL ) disabled @endif
                                >
                                    @foreach ($approveOptions as $option)
                                        <option value="{{$option['id']}}"
                                            @if($option['id'] == $request->approve) selected @endif
                                        >{{$option['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group form-label-left form-group-select2 <?php if($request->approve != getOptions::APPROVE_ON || $request->type != getOptions::TYPE_RECRUIT) echo 'hidden'; ?>">
                            <label for="assign" class="col-sm-3 control-label required" aria-required="true">{{trans('resource::view.Request.Detail.Assignee')}} <em>*</em></label>
                            <div class="col-md-9 fg-valid-custom">
                                <select name="recruiter" class="select-search has-search select2-hidden-accessible" id="recruiter" tabindex="-1" aria-hidden="true"
                                    @if ((!Permission::getInstance()->isAllow('resource::request.approved')
                                                && !Permission::getInstance()->isAllow('resource::request.assignee')
                                                && !Permission::getInstance()->getEmployee()->id == $request->employees_assign)
                                            || $request->status == getOptions::STATUS_CLOSE)
                                        disabled
                                    @endif
                                >
                                    @foreach ($hrAccounts as $nickname => $email)
                                        <option value="{{$email}}"
                                            <?php if ($email == $request->recruiter) echo 'selected'; ?>
                                        >{{$email}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" id="submit-tantai">
                    <div class="col-md-12">
                        <div class="form-group form-label-left">
                            <label class="col-sm-3 control-label required" aria-required="true">{{ trans('resource::view.Create date') }}</label>
                            <div class="col-md-9">
                                <p>{{$request->created_at}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group form-label-left form-group-select2 <?php if($request->approve != getOptions::APPROVE_ON) echo 'hidden'; ?>">
                            <label for="assign" class="col-sm-3 control-label required" aria-required="true">{{trans('resource::view.Request.Detail.Type')}} <em>*</em></label>
                            <div class="col-md-9 fg-valid-custom">
                                <select name="type" class="select-search has-search select2-hidden-accessible" id="type" tabindex="-1" aria-hidden="true"
                                    @if ($request->approve != getOptions::APPROVE_YET) disabled @endif
                                >
                                    @foreach ($typeOption as $option)
                                        <option value="{{$option['id']}}"
                                           @if($option['id'] == $request->type) selected @endif
                                        >{{$option['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="col-md-12">
                        <div class="form-group form-label-left hidden" id="assign-tantai">
                            <label for="title" class="col-sm-3 control-label required" aria-required="true">Approver <em>*</em></label>
                            @if ($request->status != getOptions::STATUS_CLOSE && (Permission::getInstance()->isAllow('resource::request.approved')
                            || ($request->approve == getOptions::APPROVE_ON && Permission::getInstance()->isAllow('resource::request.assignee')))
                            && $request->status != getOptions::STATUS_CANCEL)
                            <div class="col-md-8" id="content-assig">
                                <input type="text" disabled="" value="@if($request->employees_assign) {{Employee::getEmailEmpById($request->employees_assign)}} @else Not assign @endif" class="form-control input-field">
                                <label style="display: none;">
                                    <div class="col-md-11 row">
                                        <select type="text" name="assin" placeholder="{{ trans('sales::view.Search') }}..." class="select-search form-control has-search col-md-9" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}" />
                                            <option value="">&nbsp;</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-1">
                                        <button type="button" class="btn btn-success btn-xs save-asign">
                                            <i class="fa fa-floppy-o "></i>
                                            <i class="fa fa-spin fa-refresh btn-submit-refresh hidden"></i>
                                        </button>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-primary btn-change btn-xs ">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-remove-approver btn-xs " style="display: none;">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                            @else
                            <div class="col-md-9">
                                <input type="text" disabled="" value="@if($request->employees_assign) {{Employee::getEmailEmpById($request->employees_assign)}} @else Not assign @endif" class="form-control input-field">
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <?php $priority = RequestPriority::getPriorityNameById($request->priority_id); ?>
                        <div class="form-group form-label-left form-group-select2">
                            <label for="priority" class="col-sm-3 control-label required" aria-required="true">{{trans('resource::view.Request.Create.Priority')}} </label>
                            <div class="col-md-9 fg-valid-custom">
                                <p>{{ RequestPriority::getNameLang($priority, $lang) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <?php $priority = RequestPriority::getPriorityNameById($request->priority_id); ?>
                        <div class="form-group form-label-left form-group-select2">
                            <label for="priority" class="col-sm-3 control-label required" aria-required="true">{{trans('resource::view.Request.Create.Priority')}} </label>
                            <div class="col-md-9 fg-valid-custom">
                                <p>{{ RequestPriority::getNameLang($priority, $lang) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- <div class="col-md-6">
                        <div class="form-group form-label-left form-group-select2 <?php if($request->approve != getOptions::APPROVE_ON) echo 'hidden'; ?>">
                            <label for="assign" class="col-sm-3 control-label required" aria-required="true">{{trans('resource::view.Request.Detail.Type')}} <em>*</em></label>
                            <div class="col-md-9 fg-valid-custom">
                                <select name="type" class="select-search has-search select2-hidden-accessible" id="type" tabindex="-1" aria-hidden="true"
                                    @if ($request->approve != getOptions::APPROVE_YET) disabled @endif
                                >
                                    @foreach ($typeOption as $option)
                                        <option value="{{$option['id']}}"
                                           @if($option['id'] == $request->type) selected @endif     
                                        >{{$option['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="col-md-6">
                        <div class="form-group form-label-left form-group-select2 <?php if($request->approve != getOptions::APPROVE_ON || $request->type != getOptions::TYPE_RECRUIT) echo 'hidden'; ?>">
                            <label for="assign" class="col-sm-3 control-label required" aria-required="true">{{trans('resource::view.Request.Detail.Assignee')}} <em>*</em></label>
                            <div class="col-md-9 fg-valid-custom">
                                <select name="recruiter" class="select-search has-search select2-hidden-accessible" id="recruiter" tabindex="-1" aria-hidden="true"
                                    @if ((!Permission::getInstance()->isAllow('resource::request.approved')
                                                && !Permission::getInstance()->isAllow('resource::request.assignee')
                                                && !Permission::getInstance()->getEmployee()->id == $request->employees_assign)
                                            || $request->status == getOptions::STATUS_CLOSE) 
                                        disabled 
                                    @endif  
                                >
                                    @foreach ($hrAccounts as $nickname => $email)
                                        <option value="{{$email}}"
                                            <?php if ($email == $request->recruiter) echo 'selected'; ?>
                                        >{{$email}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div> -->
                </div>
                @if ($request->status != getOptions::STATUS_CLOSE && (Permission::getInstance()->isAllow('resource::request.approved')
                    || ($request->approve == getOptions::APPROVE_ON && Permission::getInstance()->isAllow('resource::request.assignee'))
                    || Permission::getInstance()->getEmployee()->id == $request->employees_assign)
                    && $request->status != getOptions::STATUS_CANCEL)
                <div class="row">
                    <div class="col-md-12 align-center">
                        <button class="btn-add" type="submit">
                            {{ trans('resource::view.Submit') }}
                            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                        </button>
                    </div>
                </div>
                @endif
            @if (isset($urlSubmit))
            </form>
            @else
            </div>
            @endif
        </div>
        
        
            <div class="nav-tabs-custom margin-top-50 tab-keep-status" data-type="request-{{$request->id}}">
                <ul class="nav nav-tabs">
                    <li <?php if($tabActive == 'tab_1'): ?> class="active" <?php endif; ?>><a href="#tab_1" data-toggle="tab" aria-expanded="true"><b>{{trans('resource::view.Request.Detail.Request information')}}</b></a></li>
                    @if ($request->type == getOptions::TYPE_RECRUIT)
                    <li <?php if($tabActive == 'tab_2'): ?> class="active" <?php endif; ?>><a href="#tab_2" data-toggle="tab" aria-expanded="false"><b>{{trans('resource::view.Request.Detail.Candidate statistic of request')}}</b></a></li>
                    <li <?php if($tabActive == 'tab_3'): ?> class="active" <?php endif; ?>><a href="#tab_3" data-toggle="tab" aria-expanded="false"><b>{{trans('resource::view.Request.Detail.Channel of request')}}</b></a></li>
                    @endif
                </ul>
                <div class="tab-content min-height-150">
                    <div class="tab-pane <?php if($tabActive == 'tab_1'): ?> active <?php endif; ?>" id="tab_1">
                        @include ('resource::request.tab_detail.request_info')
                    </div>
                    @if ($request->type == getOptions::TYPE_RECRUIT)
                    <div class="tab-pane <?php if($tabActive == 'tab_2'): ?> active <?php endif; ?>" id="tab_2">
                        @include ('resource::request.tab_detail.candidate_info')
                    </div>
                    <div class="tab-pane <?php if($tabActive == 'tab_3'): ?> active <?php endif; ?>" id="tab_3">
                        @include ('resource::request.tab_detail.recruiment_cost')
                    </div>
                    @endif
                </div>
                <!-- /.tab-content -->
            </div>
        
    </div>
</div>
<!-- modal delete cofirm -->
<div class="modal fade modal-danger" id="modal-delete-confirm-new" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ Lang::get('core::view.Are you sure delete item(s)?') }}</p>
                <p class="text-change"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok">{{ Lang::get('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->
@include ('resource::request.include.warning')
@include ('resource::request.include.deadline_warning')
@endsection

@section('script')
<script>
var approveOff = {{getOptions::APPROVE_OFF}};
var approveOn = {{getOptions::APPROVE_ON}};
var approveYet = {{getOptions::APPROVE_YET}};
var typeUtilize = {{getOptions::TYPE_UTILIZE_RESOURCE}};
var typeRecruit = {{getOptions::TYPE_RECRUIT}};
var token = '{{Session::token()}}';
var requestId = {{$request->id}};
var urlAddChannel = '{{route("resource::request.saveChannel")}}';
var urlCandidateList = "{{route('resource::request.candidateList', ['requestId' => $request->id])}}";
messageError = '<?php echo trans('project::view.Error while processing add') ?>';
project_id = 0;
var deadlineWarning = {{$warning}};
var urlPostRequest = '{{ url('resource/request/post-data') }}';
var _token = '{{ csrf_token() }}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<script src="{{ CoreUrl::asset('resource/js/request/detail.js') }}"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('lib/js/jquery.cookie.min.js') }}"></script>
<script src="{{ CoreUrl::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<script>
$(document).ready(function() {
    $('#modal-content #content').prop('disabled', true);
    RKfuncion.CKEditor.init(['content']);
    RKfuncion.select2.init();
    <?php if (View::showModalWarning($checkOverload, $checkFull)) : ?>
            $('#modal-warning').modal('show');
    <?php endif; ?>
});
$(document).ready(function() {
    $('.btn-change').on('click',function() {
        $(this).css('display','none');
        $(this).next().css('display','block');
        $(this).parent().prev().find("input").css('display','none');
        $(this).parent().prev().find("label:last-child").css('display','block');

    });

    $('.btn-remove-approver').on('click',function() {
        $(this).css('display','none');
        $(this).prev().css('display','block');
        $(this).parent().prev().find("input").css('display','block');
        $(this).parent().prev().find("label:last-child").css('display','none');
    });

    

});


    $('#toggle-event').bootstrapToggle({
      on: 'Submit',
      off: 'Approver'
    });

$('#toggle-event').change(function() {
    event = $(this).prop('checked');
    if (event == true) { 
        $('#assign-tantai').removeClass('hidden');
        $('#submit-tantai').addClass('hidden');
        $('button[type="submit"]').addClass('hidden');
    } else {
        $('#assign-tantai').addClass('hidden');
        $('#submit-tantai').removeClass('hidden');
         $('button[type="submit"]').removeClass('hidden');
    }

});

</script>
@endsection
