<?php
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\ProjDeliverable;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CookieCore;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Team\View\Permission;

$statusTask = Task::statusLabel();
$isCheck = false;
$noteSb = CookieCore::get('sb_note_'.$project->id);
$typeTaskCheck = Task::getTypeTaskCheckCloseProject();
$statusTaskClose = Task::getStatusCloseOrReject();
//Check issue or task
$isIssueOrTask = Task::isIssueOrTaskByProjsId($project->id);
$isTask = false;
if ($isIssueOrTask == 'tasks') {
    $taskNotClose = Task::getList($project->id, $typeTaskCheck, [], null, $statusTaskClose);
    $isTask = true;
} else {
    $taskNotClose = Task::getIssueHasTaskActinoUnCloseByProjsId($project->id);
}

$cssNotApprove = Css::getCssFollowStatus($project->id);
$arrayIdCss = [];
foreach ($cssNotApprove as $css) {
    $arrayIdCss[] = $css->id;
}
$getProject = Css::getCssByProjectId($project->name);
$arrayCssCus = [];
foreach ($getProject as $item) {
    if (!Css::checkCustomerConfirmCss($item->id)) {
        $arrayCssCus[] = $item->id;
    }
}
$cssResultFeeback = CssResult::getCssResultFeedback($arrayIdCss);
$count = 0;

?>
<div class="row">
    @if($taskWOApproved)
        @if($taskWOApproved->status == Task::STATUS_SUBMITTED)
            <?php $statusClass = 'callout-warning'; ?>
        @elseif($taskWOApproved->status == Task::STATUS_FEEDBACK)
            <?php $statusClass = 'callout-danger'; ?>
        @elseif($taskWOApproved->status == Task::STATUS_REVIEWED)
            <?php $statusClass = 'callout-info'; ?>
        @else
            <?php $statusClass = ''; ?>
        @endif
        @foreach($statusTask as $key => $value)
        @if ($key == $taskWOApproved->status)
        <?php $isCheck = true; ?>
        <div class="col-sm-2">
            <div class="callout {{$statusClass}} status">
                <p class="text-center text-uppercase"><strong>{{$value}}</strong></p>
            </div>
        </div>
        @endif
        @endforeach
    @else
        @if ($checkHasTaskWorkorderApproved)
        <?php $isCheck = true; ?>
        <div class="col-sm-2">
            <div class="callout callout-success status">
                <p class="text-center text-uppercase"><strong>{{$statusTask[Task::STATUS_APPROVED]}}</strong></p>
            </div>
        </div>
        @endif
    @endif
    @if ($isCheck)
    <div class="col-sm-2 col-sm-offset-4">
        <h4 class="text-right">{{trans('project::view.Note')}}:</h4>
    </div>
    <div class="col-sm-2">
        <h4><span class="label label-warning status">{{trans('project::view.Unapproved Value')}}</span></h4>
    </div>
    @else
    <div class="col-sm-2">
        <h4>{{trans('project::view.Note')}}: <span class="label label-warning status">{{trans('project::view.Unapproved Value')}}</span></h4>
    </div>
    @endif
    <div class="col-sm-2">
        <table class="table"><tr><td class="background-draft">{{trans('project::view.Unapproved Row')}}</td></tr></table>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom tab-danger tab-keep-status" data-type="workorder">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" <?php if($tabActiveWO == 'summary'): ?> class="active"<?php endif; ?>><a href="#summary" class="active-summary" aria-controls="summary" role="tab" data-toggle="tab">{{trans('project::view.Basic info')}}</a></li>
                <li role="presentation" <?php if($tabActiveWO == 'scope'): ?> class="active"<?php endif; ?>><a href="#scope" aria-controls="scope" role="tab" data-toggle="tab">{{trans('project::view.Scope & Object')}}</a></li>
                <li role="presentation" <?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_STAGE_MILESTONE]): ?> class="active"<?php endif; ?>><a href="#{{$allNameTab[Task::TYPE_WO_STAGE_MILESTONE]}}" aria-controls="{{$allNameTab[Task::TYPE_WO_STAGE_MILESTONE]}}" role="tab" data-toggle="tab">{{trans('project::view.Stages')}}</a></li>
                <li role="presentation" <?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_DELIVERABLE]): ?> class="active"<?php endif; ?>><a href="#{{$allNameTab[Task::TYPE_WO_DELIVERABLE]}}" aria-controls="{{$allNameTab[Task::TYPE_WO_DELIVERABLE]}}" role="tab" data-toggle="tab">{{trans('project::view.Deliverable')}}</a></li>
                <li role="presentation" <?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_PROJECT_MEMBER]): ?> class="active"<?php endif; ?>><a href="#{{$allNameTab[Task::TYPE_WO_PROJECT_MEMBER]}}" aria-controls="{{$allNameTab[Task::TYPE_WO_PROJECT_MEMBER]}}" role="tab" data-toggle="tab">{{trans('project::view.Team Allocation')}}</a></li>
                <li role="presentation" <?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_RISK]): ?> class="active"<?php endif; ?>><a href="#{{$allNameTab[Task::TYPE_WO_RISK]}}" aria-controls="{{$allNameTab[Task::TYPE_WO_RISK]}}" role="tab" data-toggle="tab">{{trans('project::view.Risk')}}</a></li>
                <li role="presentation" <?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_OPPORTUNITY]): ?> class="active"<?php endif; ?>><a href="#{{$allNameTab[Task::TYPE_WO_OPPORTUNITY]}}" aria-controls="{{$allNameTab[Task::TYPE_WO_OPPORTUNITY]}}" role="tab" data-toggle="tab">Opportunity</a></li>
                <li role="presentation" <?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_ISSUE]): ?> class="active"<?php endif; ?>><a href="#{{$allNameTab[Task::TYPE_WO_ISSUE]}}" aria-controls="{{$allNameTab[Task::TYPE_WO_ISSUE]}}" role="tab" data-toggle="tab">{{trans('project::view.Issues')}}</a></li>
                <li role="presentation" <?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_NC]): ?> class="active"<?php endif; ?>><a href="#{{$allNameTab[Task::TYPE_WO_NC]}}" aria-controls="{{$allNameTab[Task::TYPE_WO_NC]}}" role="tab" data-toggle="tab">NC</a></li>
                <li role="presentation" <?php if($tabActiveWO == 'security'): ?> class="active"<?php endif; ?>><a href="#security" aria-controls="security" role="tab" data-toggle="tab">Security</a></li>
                <li role="presentation" <?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_TRANING]): ?> class="active"<?php endif; ?>><a href="#{{$allNameTab[Task::TYPE_WO_TRANING]}}" aria-controls="{{$allNameTab[Task::TYPE_WO_TRANING]}}" role="tab" data-toggle="tab">{{trans('project::view.Training Plan')}}</a></li>
                <li role="presentation" <?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_CM_PLAN]): ?> class="active"<?php endif; ?>><a href="#{{$allNameTab[Task::TYPE_WO_CM_PLAN]}}" aria-controls="{{$allNameTab[Task::TYPE_WO_CM_PLAN]}}" role="tab" data-toggle="tab">{{trans('project::view.CM Plan')}}</a></li>
                <li role="presentation" <?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_COMMINUCATION]): ?> class="active"<?php endif; ?>><a href="#{{$allNameTab[Task::TYPE_WO_COMMINUCATION]}}" aria-controls="{{$allNameTab[Task::TYPE_WO_COMMINUCATION]}}" role="tab" data-toggle="tab">Communication Plan</a></li>
            </ul>
            <!-- Tab panes -->
            <div class="wo-tab-content tab-content">
                <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == 'summary'): ?> active<?php endif; ?>" id="summary">
                    @include('project::tab_content.summary')
                </div>
                <!-- /Scope tabpanel -->
                <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == 'scope'): ?> active<?php endif; ?>" id="scope">
                    <div class="fetch-content workorder-scope" id="workorder-content-scope">
                    @include('project::components.scope-object')
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == 'security'): ?> active<?php endif; ?>" id="security">
                    <h4>{{ trans('project::view.Note security') }}
                        <a href="{{ asset('project/images/security.png') }}" target="_blank" data-toggle="tooltip" data-html="true">{{ trans('project::view.Here') }}</a> {{ trans('project::view.view sample report') }}</h4>
                    <div class="fetch-content workorder-security" id="workorder-content-security">
                        @include('project::components.security')
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_STAGE_MILESTONE]): ?> active<?php endif; ?>" id="{{$allNameTab[Task::TYPE_WO_STAGE_MILESTONE]}}">
                    <div class="fetch-content workorder-stage-and-milestone" id="workorder-content-{{$allNameTab[Task::TYPE_WO_STAGE_MILESTONE]}}">
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_DELIVERABLE]): ?> active<?php endif; ?>" id="{{$allNameTab[Task::TYPE_WO_DELIVERABLE]}}">
                    <div class="fetch-content workorder-deliverable" id="workorder-content-{{$allNameTab[Task::TYPE_WO_DELIVERABLE]}}">
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_PROJECT_MEMBER]): ?> active<?php endif; ?>" id="{{$allNameTab[Task::TYPE_WO_PROJECT_MEMBER]}}">
                    @if(Permission::getInstance()->isAllow('project::project.export'))
                    <form action="{{ route('project::project.export') }}" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" name="projectId" value=" {{ $project->id }} ">
                        <a type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-select-options-export-team-allocation"
                           title="{{ trans('project::view.Export all approved members') }}"
                           style="margin-bottom: 5px">{{ trans('project::view.Export approved members') }}</a>
                    </form>
                    @endif
                    <div class="fetch-content workorder-project-member" id="workorder-content-{{$allNameTab[Task::TYPE_WO_PROJECT_MEMBER]}}"></div>
                </div>
            <!-- Risk tabpanel -->
            <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_RISK]): ?> active<?php endif; ?>" id="{{$allNameTab[Task::TYPE_WO_RISK]}}">
                <div class="fetch-content workorder-quality panel-left-link"><a  style="float: right;" href="{{asset('help/risk/riskhelp.html')}}" target="_blank">{{trans('project::view.Help')}}</a><div style="clear: both;"></div></div>
                <div class="fetch-content workorder-quality" id="workorder-content-{{$allNameTab[Task::TYPE_WO_RISK]}}">
                </div>
            </div>
            <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_OPPORTUNITY]): ?> active<?php endif; ?>" id="{{$allNameTab[Task::TYPE_WO_OPPORTUNITY]}}">
                <div class="fetch-content workorder-quality" id="workorder-content-{{$allNameTab[Task::TYPE_WO_OPPORTUNITY]}}">
                    @include('project::components.opportunity')
                </div>
            </div>
            <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_ISSUE]): ?> active<?php endif; ?>" id="{{$allNameTab[Task::TYPE_WO_ISSUE]}}">
                <div class="fetch-content workorder-quality" id="workorder-content-{{$allNameTab[Task::TYPE_WO_ISSUE]}}">
                    @include('project::components.issue')
                </div>
            </div>
            <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_NC]): ?> active<?php endif; ?>" id="{{$allNameTab[Task::TYPE_WO_NC]}}">
                <div class="fetch-content workorder-quality" id="workorder-content-{{$allNameTab[Task::TYPE_WO_NC]}}">
                    @include('project::components.nc')
                </div>
            </div>
            <!-- /Risk tabpanel -->
            <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_OVER_PLAN]): ?> active<?php endif; ?>" id="{{$allNameTab[Task::TYPE_WO_OVER_PLAN]}}">
                <div class="fetch-content">
                    <div class="workorder-critical-dependencies" id="workorder-content-{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}">
                    @include('project::components.critical-dependencies')
                    </div>

                    <!-- Assumption and constrains -->
                    <div class="workorder-assumption-constrains" id="workorder-content-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}">
                    @include('project::components.assumption-constrains')
                    </div>

                    <!-- external-interface -->
                    <div class="workorder-external-interface" id="workorder-content-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}">
                    @include('project::components.external-interface')
                    </div>

                    <!-- Tools and infrastructure -->
                    <div class="workorder-tool-and-infrastructure" id="workorder-content-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}">
                    @include('project::components.tools-infrastructure')
                    </div>

                    <!-- Devices expenses -->
                    <div class="workorder-derived-expenses" id="workorder-content-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}">
                        @include('project::components.devices-expenses')
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_TRANING]): ?> active<?php endif; ?>" id="{{$allNameTab[Task::TYPE_WO_TRANING]}}">
                <h3>1. Needed Knowledge And Skill</h3>
                @include('project::components.skills-request')
                <div class="fetch-content workorder-training" id="workorder-content-{{$allNameTab[Task::TYPE_WO_TRANING]}}">
                </div>
            </div>
            <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_CM_PLAN]): ?> active<?php endif; ?>" id="{{$allNameTab[Task::TYPE_WO_CM_PLAN]}}">
                <div class="fetch-content workorder-cm-plan" id="workorder-content-{{$allNameTab[Task::TYPE_WO_CM_PLAN]}}">
                </div>
            </div>
                <div role="tabpanel" class="tab-pane<?php if($tabActiveWO == $allNameTab[Task::TYPE_WO_COMMINUCATION]): ?> active<?php endif; ?>" id="{{$allNameTab[Task::TYPE_WO_COMMINUCATION]}}">
                    @include('project::components.customer-role-com')
                    @include('project::components.person-role-com')
                    @include('project::components.communication')
                </div>
          </div>
        </div>
    </div>
</div>
@if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
<div class="modal fade modal-warning" id="modal-submit-wo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">
                {{ Lang::get('project::message.Are you sure submit workorder') }}
                </p>
            </div>
            <div class="modal-footer">
                <form action="" method="post" accept-charset="utf-8" class="no-validate">
                    {{ csrf_field() }}
                    <input type="hidden" name="project_id" value="{{$project->id}}">
                    <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                    <button type="submit" class="btn btn-outline btn-ok">{{ Lang::get('core::view.OK') }}</button>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<div class="row">
    <div class="text-center {{$checkDisplaySubmitButton && $checkEditWorkOrder ? '' : 'display-none'}} submit-workorder">
        <button type="button" class="btn btn-primary text-center align-center open-modal-wo-submit">
        @if($checkHasTask)
            {{trans('project::view.Submit Work Order')}}
        @else
            {{trans('project::view.Create Work Order')}}
        @endif
        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i>
        </button>
    </div>
</div>
@endif
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
                <p class="text-change">{{ Lang::get('core::view.Are you sure cancel value edited?') }}</p>
                <p class="text-undo">{{ Lang::get('core::view.Are you sure undo this item?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok">{{ Lang::get('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->


<div class="modal fade modal-warning" id="modal-warning-reload">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{trans('project::view.Notification')}}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{trans('project::view.Workorder changed status. Please reload page!')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="location.reload();">{{trans('project::view.Ok')}}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal-wo-submit">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('project::project.submit_workorder', ['id' => $project->id]) }}"
                class="form-horizontal form-submit-ajax" autocomplete="off"
                data-callback-error="workoderErrorDate">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{trans('project::view.Submit workorder note')}}</h4>
                </div>
                <div class="modal-body">
                    <textarea rows="5" name="sb[note]" class="form-control">{{ $noteSb }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('project::view.Close') }}</button>
                    <button type="submit" class="btn-add">
                        {{ trans('project::view.Submit') }}
                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                    </button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- modal imformation list task not close -->
<div class="modal fade" id="modal-task-close">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="javascript:window.location.reload()">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('project::view.Bạn cần đóng những task này trước khi muốn close dự án!') }}</h4>
            </div>
            <div class="modal-body">
                @if (isset($taskNotClose) || isset($cssNotApprove) || isset($arrayCssCus))
                    @foreach ($taskNotClose as $key => $item)
                    <?php $count += 1 ?>
                        <span>{{ $key + 1 }}. </span>
                        <a href="{{ $isTask ? Task::getUrlTaskFollowType($item) : route('project::issue.detail', ['id' => $item->id]) }}" target="_blank">@if($item->title) {{ $item->title }} @else {{ $isTask ? Task::getUrlTaskFollowType($item) : route('project::issue.detail', ['id' => $item->id]) }} @endif</a>
                        <br>
                    @endforeach
                    @foreach ($cssResultFeeback as $key => $item)
                        <span>{{ $key + $count + 1 }}. </span>
                        <a href="{{ route('sales::css.detail', ['id' => $item->id]) }}" target="_blank">{{ route('sales::css.detail', ['id' => $item->id]) }}</a>
                        <br>
                    @endforeach
                    @foreach ($arrayCssCus as $item => $value)
                        <span>{{ $item + $count + 1 }}. </span>
                        <a href="{{ route('sales::css.update', ['id' => $value]) . '?type=detail' }}" target="_blank">{{ route('sales::css.update', ['id' => $value]) . '?type=detail' }}</a>
                    @endforeach
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal" onclick="javascript:window.location.reload()">{{ trans('project::view.Close') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- modal select options export team allocation -->
<div class="modal fade" id="modal-select-options-export-team-allocation">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('project::view.Option') }}</h4>
            </div>
            <div class="modal-body" style="display: flex;">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="choseOptionExport" id="exportDetailByMonth" value="ExportDetailByMonth">
                    <label class="form-check-label" for="exportDetailByMonth">
                        {{ trans('project::view.Export chi tiết theo tháng') }}
                    </label>
                </div>
                <div class="form-check" style="margin-left: 30px;">
                    <input class="form-check-input" type="radio" name="choseOptionExport" id="exportOverview" value="ExportOverview" checked>
                    <label class="form-check-label" for="exportOverview">
                        {{ trans('project::view.Export tổng quan') }}
                    </label>
                </div>
            </div>
            <div class="modal-body preview-export-option">
                <h5>{{ trans('project::view.Preview') }}</h5>
                <div id="imgExportDetailByMonth">
                    <img class="img-fluid" src="{{ asset('common/images/project_wo/export-detail-by-month.png') }}" style="max-width: 850px;">
                </div>
                <div id="imgExportOverview">
                    <img class="img-fluid" src="{{ asset('common/images/project_wo/export-overvew.png') }}" style="max-width: 850px;">
                </div>
            </div>
            <div class="modal-footer">
                <a type="button" class="btn btn-primary" id="btnExportOverview" href="{{ route('project::project.export', $project->id) }}">{{ trans('project::view.Export') }}</a>
                <a type="button" class="btn btn-primary" id="btnExportDetailByMonth" href="{{ route('project::project.export-by-month', $project->id) }}">{{ trans('project::view.Export') }}</a>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- member content -->
<div data-flag-dom="wo-team-allocate" class="hidden">
    <div class="table-content-team-allocation">
        <div id="ta-vis" class="vis-member"></div>
        <div class="margin-top-10 row">
            <div class="col-md-3">
                <button type="button" class="btn-add" data-btn-action="woAddProjMember">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
            <div class="col-md-9">
                <div class="pull-right">
                    <table class="table table-no-border">
                        <tr>
                            <td style="padding-right: 25px;">
                                {!!trans('project::view.Total actual effort')!!}
                                (<span data-dom-flag="type-resource"></span>)
                            </td>
                            <td><b>{!!trans('project::view.Approved')!!}</b>:</td>
                            <td><span data-dom-effort="approved"></span></td>
                        </tr>
                        <tr data-dom-flag="tae-unapprove">
                            <td>&nbsp;</td>
                            <td class="is-change-value"><b>{!!trans('project::view.Unapprove')!!}</b>:</td>
                            <td class="is-change-value"><span data-dom-effort="un"></span></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-md-12">
                <p><strong>{{ trans('project::view.Note:') }}</strong></p>
                <p>{!! trans('project::view.note_team_allocation') !!}</p>
            </div>
        </div>
    </div>
</div>
<!-- end member content -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
<script>
$(document).ready(function() {
    var imgExportOverview = $("#imgExportOverview");
    var imgExportDetailByMonth = $("#imgExportDetailByMonth");
    var btnExportDetailByMonth = $("#btnExportDetailByMonth");
    var btnExportOverview = $("#btnExportOverview");
    imgExportDetailByMonth.hide();
    btnExportDetailByMonth.hide();
    $("input[name$='choseOptionExport']").click(function() {
        var value = $(this).val();
        imgExportOverview.hide();
        imgExportDetailByMonth.hide();
        btnExportDetailByMonth.hide();
        btnExportOverview.hide();
        $("#btn" + value).show();
        $("#img" + value).show();
    });
    btnExportDetailByMonth.click(function(){
        $("#modal-select-options-export-team-allocation").modal('hide');
    })
    btnExportOverview.click(function(){
        $("#modal-select-options-export-team-allocation").modal('hide');
    })
});
</script>
