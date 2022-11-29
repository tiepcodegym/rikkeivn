@extends('layouts.default')

@section('title')
<?php
use Rikkei\Core\View\View;
use Rikkei\Core\View\CookieCore;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Project;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Project\View\ProjDbHelp;
use Rikkei\Project\Model\ProjRewardBudget;
use Rikkei\Project\View\View as ViewProject;

//init object viewProject
$viewProject = new ViewProject();

if (!$viewBaseline) {
    echo trans('project::view.Project Report:');
    $tabActive = CookieCore::get('tab-keep-status-project-dashboard');
    $textHtmlNoteHead = trans('project::view.Note');
} else {
    echo trans('project::view.Project Baseline detail:');
    $tabActive = CookieCore::get('tab-keep-status-project-dashboard-baseline');
    $textHtmlNoteHead = '<a href="#href-baseline-note">'.
        trans('project::view.Note') .'</a>';
}
if (!$tabActive) {
    $tabActive = 'summary';
}
echo e($project->name);
if (isset($pmActive) && $pmActive) {
    echo ' - PM: ' . \Rikkei\Project\View\GeneralProject::getNickName($pmActive->email);
}
$langDomain = 'project::view.';
$isViewPrivateRewardBudget = Permission::getInstance()->isAllow('project::reward.budget.view');
$permissionAllowViewReward = false;
if ($project->type == Project::TYPE_BASE &&
    !$viewBaseline &&
    Task::createRewardAvailable($project) &&
    (($isPm && $projectMeta->isShowRewardBudget() || ($isLeader ||$isCoo || $isViewPrivateRewardBudget)))
) {
    if (ProjRewardBudget::isExistsBudget($project)) {
        $permissionAllowViewReward = true;
    }
}
$reportSubmitAvai = $project->canChangeDashboard() && !$viewBaseline && (
    ($permissionEditPP['pm'] || Permission::getInstance()->isCOOAccount() || $permissionEditPP['subPM'])
);
$colorOptions = ProjDbHelp::toOptionReportColor();
?>
@endsection
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" type="text/css" >
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
<style rel="stylesheet" type="text/css">
@if(!$timeCloseProject)
table tr td:last-child div {
    width: calc(100% - 100px);
    float: left;
    margin-right: 5px;
}
@endif
table tr td:last-child {
    min-width: 300px;
}
.bootstrap-datetimepicker-widget table tr td:last-child {
    min-width: 0px !important;
}
#form-dashboard-point .tooltip-inner {
    white-space: normal;
}
.text-tooltip-wrapper.dropdown .text-tooltip.dropdown-menu {
    top: 45px;
    left: -15px;
    right: 0;
    padding: 10px;
    border-radius: 0;
    box-shadow: 0px 0px 5px;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        {!! $project->noticeToClose() !!}
        <div class="box box-info border-none">
            <div class="box-body box-body-no-padding">
                <div class="box-body-header baseline-box">
                    @include('project::point.include.baseline_select_detail')
                </div>
                <div class="clearfix"></div>
                <form autocomplete="off" class="submit-disable" id="form-dashboard-point">
                <!-- tab wrapper -->
                <div class="nav-tabs-custom tab-keep-status dashboard-tabs-custom" data-type="project-dashboard<?php if($viewBaseline): ?>-baseline<?php endif; ?>">
                    <!-- tab header -->
                    <ul class="nav nav-tabs tabs-point-task">
                        <li<?php if($tabActive == 'summary'): ?> class="active"<?php endif; ?>>
                            <a href="#summary" data-toggle="tab" data-type="{{ Task::TYPE_ISSUE }}"><strong>{{ trans('project::view.Summary Point') }}</strong>
                                <i class="fa fa-spin fa-refresh hidden summary"></i>
                                <span class="pp-color summary_color point-tab-title" data-report-color="summary">
                                    <img src="{{ $allColorStatus[$projectPointInformation['summary']] }}" />
                                </span>
                            </a>
                        </li>
                        <li<?php if($tabActive == 'cost'): ?> class="active"<?php endif; ?>>
                            <a href="#cost" data-toggle="tab" data-type="{{ Task::TYPE_ISSUE_COST }}"><strong>{{ trans('project::view.Cost') }}</strong>
                                <i class="fa fa-spin fa-refresh hidden cost"></i>
                                                                <div class="report-color-wrapper">
                                                                    <span class="pp-color cost_color point-tab-title" data-report-color="cost">
                                                                            <img src="{{ $allColorStatus[$viewProject->getCostColor($projectPointInformation['cost_effort_efficiency2'], $projectPointInformation['cost_effort_efficiency_target'], $projectPointInformation['cost_effort_efficiency_lcl'], $projectPointInformation['correction_cost'], $projectPointInformation['correct_cost_target'], $projectPointInformation['correct_cost_ucl'], $projectPointInformation['correct_cost_lcl'], $checkCate)] }}" />
                                                                    </span>
                                                                    <div class="report-color-select hidden form-group-select2">
                                                                        <select data-name="color[cost]" data-s2-init="report-color">
                                                                            @foreach ($colorOptions as $key => $value)
                                                                                <option value="{{ $key }}"@if ($key == $projectPointInformation['cost']) selected @endif>{{ $value }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <input name="color[cost]" class="pp-input" type="hidden" value="{{ $projectPointInformation['cost'] }}" />
                                                                    </div>
                                                                </div>
                            </a>
                        </li>
                        <li<?php if($tabActive == 'quality'): ?> class="active"<?php endif; ?>>
                            <a href="#quality" data-toggle="tab" data-type="{{ Task::TYPE_ISSUE_QUA }}"><strong>{{ trans('project::view.Quality') }}</strong>
                                <i class="fa fa-spin fa-refresh hidden quality"></i>
                                                                <div class="report-color-wrapper">
                                                                    <span class="pp-color qua_color point-tab-title" data-report-color="quality">
                                                                            <img src="{{ $allColorStatus[$viewProject->getQualiTyColor($projectPointInformation['qua_leakage'], $projectPointInformation['qua_leakage_target'], $projectPointInformation['qua_leakage_ucl'], $projectPointInformation['qua_defect'], $projectPointInformation['qua_defect_target'], $projectPointInformation['qua_defect_ucl'], $checkCate)] }}" />
                                                                    </span>
                                                                    <div class="report-color-select hidden form-group-select2">
                                                                        <select data-name="color[quality]" data-s2-init="report-color">
                                                                            @foreach ($colorOptions as $key => $value)
                                                                                <option value="{{ $key }}"@if ($key == $projectPointInformation['quality']) selected @endif>{{ $value }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <input name="color[quality]" class="pp-input" type="hidden" value="{{ $projectPointInformation['quality'] }}" />
                                                                    </div>
                                                                </div>
                            </a>
                        </li>
                        <li<?php if($tabActive == 'timeliness'): ?> class="active"<?php endif; ?>>
                            <a href="#timeliness" data-toggle="tab" data-type="{{ Task::TYPE_ISSUE_TL }}"><strong>{{ trans('project::view.Timeliness') }}</strong>
                                <i class="fa fa-spin fa-refresh hidden timeliness"></i>
                                                                <div class="report-color-wrapper">
                                                                    <span class="pp-color tl_color point-tab-title" data-report-color="tl">
                                                                            <img src="{{ $allColorStatus[$viewProject->getTimelinessColor($projectPointInformation['tl_deliver'], $projectPointInformation['tl_deliver_target'], $projectPointInformation['tl_deliver_lcl'], $projectPointInformation['tl_deliver_ucl'], $checkCate)] }}" />
                                                                    </span>
                                                                    <div class="report-color-select hidden form-group-select2">
                                                                        <select data-name="color[tl]" data-s2-init="report-color">
                                                                            @foreach ($colorOptions as $key => $value)
                                                                                <option value="{{ $key }}"@if ($key == $projectPointInformation['tl']) selected @endif>{{ $value }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <input name="color[tl]" class="pp-input" type="hidden" value="{{ $projectPointInformation['tl'] }}" />
                                                                    </div>
                                                                </div>
                            </a>
                        </li>
                        <li<?php if($tabActive == 'process'): ?> class="active"<?php endif; ?>>
                            <a href="#process" data-toggle="tab" data-type="{{ Task::TYPE_ISSUE_PROC }}"><strong>{{ trans('project::view.Process') }}</strong>
                                <i class="fa fa-spin fa-refresh hidden process"></i>
                                                                <div class="report-color-wrapper">
                                                                    <span class="pp-color proc_color point-tab-title" data-report-color="proc">
                                                                            <img src="{{ $allColorStatus[$viewProject->getProcessColor($projectPointInformation['proc_compliance'], $projectPointInformation['proc_compliance_target'], $projectPointInformation['proc_compliance_lcl'], $checkCate)] }}" />
                                                                    </span>
                                                                    <div class="report-color-select hidden form-group-select2">
                                                                        <select data-name="color[proc]" data-s2-init="report-color">
                                                                            @foreach ($colorOptions as $key => $value)
                                                                                <option value="{{ $key }}"@if ($key == $projectPointInformation['proc']) selected @endif>{{ $value }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <input name="color[proc]" class="pp-input" type="hidden" value="{{ $projectPointInformation['proc'] }}" />
                                                                    </div>
                                                                </div>
                            </a>
                        </li>
                        <li<?php if($tabActive == 'css'): ?> class="active"<?php endif; ?>>
                            <a href="#css" data-toggle="tab" data-type="{{ Task::TYPE_ISSUE_CSS }}"><strong>{{ trans('project::view.CSS') }}</strong>
                                <i class="fa fa-spin fa-refresh hidden css"></i>
                                                                <div class="report-color-wrapper">
                                                                    <span class="pp-color css_color point-tab-title" data-report-color="css">
                                                                            <img src="{{ $allColorStatus[$viewProject->getCssColor($projectPointInformation['css_css'], $projectPointInformation['css_css_target'], $projectPointInformation['css_css_lcl'], $checkCate)] }}" />
                                                                    </span>
                                                                    <div class="report-color-select hidden form-group-select2">
                                                                        <select data-name="color[css]" data-s2-init="report-color">
                                                                            @foreach ($colorOptions as $key => $value)
                                                                                <option value="{{ $key }}"@if ($key == $projectPointInformation['css']) selected @endif>{{ $value }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <input name="color[css]" class="pp-input" type="hidden" value="{{ $projectPointInformation['css'] }}" />
                                                                    </div>
                                                                </div>
                            </a>
                        </li>
                        <li class="text-center <?php if ($tabActive == 'dashboard_log'): ?> active <?php endif; ?>">
                            <a href="#dashboard_log" data-toggle="tab" data-type="" style="padding-bottom: 11px;"><strong>{{ trans('project::view.Monitoring') }}</strong><br />
                                <i class="fa fa-spin fa-refresh hidden dashboard_log"></i>
                                <span>{{ trans('project::view.Report') }}</span>
                            </a>
                        </li>
                        @if ($permissionAllowViewReward)
                            <li class="text-center<?php if($tabActive == 'reward'): ?> active<?php endif; ?>">
                                <a href="#reward" data-toggle="tab" data-type="reward"><strong>{{ trans('project::view.Rewards') }}</strong><br/>{{ trans('project::view.budget') }}</a>
                            </li>
                        @endif
                    </ul> <!-- end tab header -->

                    <!-- tab content -->
                    <div class="tab-content">
                        <div class="tab-pane<?php if($tabActive == 'summary'): ?> active<?php endif; ?>" id="summary">
                            @include('project::point.tab.summary')
                        </div>
                        <div class="tab-pane<?php if($tabActive == 'cost'): ?> active<?php endif; ?>" id="cost">
                            @include('project::point.tab.cost')
                        </div>
                        <div class="tab-pane<?php if($tabActive == 'quality'): ?> active<?php endif; ?>" id="quality">
                            @include('project::point.tab.quality')
                        </div>
                        <div class="tab-pane<?php if($tabActive == 'timeliness'): ?> active<?php endif; ?>" id="timeliness">
                            @include('project::point.tab.timeliness')
                        </div>
                        <div class="tab-pane<?php if($tabActive == 'process'): ?> active<?php endif; ?>" id="process">
                            @include('project::point.tab.process')
                        </div>
                        <div class="tab-pane<?php if($tabActive == 'css'): ?> active<?php endif; ?>" id="css">
                            @include('project::point.tab.css')
                        </div>
                        <div class="tab-pane<?php if ($tabActive == 'dashboard_log'): ?> active <?php endif; ?>" id="dashboard_log">
                                                        @include('project::point.tab.dashboard_log')
                        </div>
                        @if ($permissionAllowViewReward)
                        <div class="tab-pane<?php if($tabActive == 'reward'): ?> active<?php endif; ?>" id="reward">
                                                    @if ($project->isLong())
                                                        <?php $monthRewards = $project->getMonthReward();?>
                                                        @include('project::point.tab.long_proj_budget')
                                                    @else
                                                        @include('project::point.tab.reward')
                                                    @endif
                        </div>
                        @endif
                    </div> <!-- end tab content -->

                </div> <!-- end tab wrapper -->
                </form>
            </div>
        </div>
    </div>
</div>
<?php /*
@if ($viewBaseline)
<!-- baseline note -->
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="box-body-header" id="href-baseline-note">
                    <h4>{{ trans('project::view.Baseline note') }}</h4>
                </div>
                <div class="clearfix"></div>
                <div class="form-group form-input-loading">
                    @if (isset($permissionEditPP['note_bl']) && $permissionEditPP['note_bl'])
                    <textarea name="bl_summary_note" class="text-resize-y note-input form-control note-input-normal"
                        rows="6">{{ $projectPoint->bl_summary_note }}</textarea>
                    <i class="fa fa-refresh fa-spin input-loading hidden"></i>
                    @else
                        {!! View::nl2br($projectPoint->bl_summary_note) !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div> <!-- end baseline note -->
@endif
*/ ?>
<div class="hidden">
    <button class="warning-action"></button>
</div>
<!-- modal ncm editor-->
<div class="modal fade" id="modal-ncm-editor">
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('project::view.None Compliance') }}</h4>
            </div>
            <div class="modal-body">
                <div class="modal-ncm-editor-main"></div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div><!-- end modal ncm editor -->

<!-- modal warn cofirm submit report-->
<div class="modal fade modal-warning" id="modal-submit-report-warn-confirm" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('project::view.Confirm') }}</h4>
                <h4 class="modal-title-change"></h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ trans('project::view.Are you sure to do this action?') }}</p>
                <p class="text-change"></p>
                <ul class="ul-wraning">

                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('project::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok">{{ trans('project::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal warn cofirm submit report -->

<!-- modal warning cofirm -->
        <div class="modal fade modal-warning" id="modal-warning-input-productivity">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ 'Notification' }}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default"></p>
                        <p class="text-change"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ 'Close' }}</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div><!-- end modal warning cofirm -->
@endsection

@section('script')
<script>
    var globalPassModule = {
        urlSavePoint: '{{ route('project::point.save', ['id' => $project->id]) }}',
        urlSaveNote: '{{ route('project::point.updateNote', ['id' => $project->id]) }}',
        urlSyncSource: '{{ URL::route('project::sync.source.server', ['id' => $project->id]) }}',
        baselineId: null,
        urlWorkingDays: '{{ URL::route('project::get.working.days') }}',
        isReportColor: {{ ($project->isTypeTrainingOfRD()) ? 'true' : 'false' }},
        reportColorAll: JSON.parse('{!! json_encode($allColorStatus) !!}'),
        isReportSubmitAvai: {{ $reportSubmitAvai ? 'true' : 'false' }}
    };
    var globalMessage = {
        reportProjectMsg: '{{ trans("project::view.Warning note report project") }}',
        errorSystemMsg: '{{ trans("project::view.Could not complete this action, please check the inputs again!") }}',
    };
    var varGlobalPassModule = {
        messageValidateRewardMin: '{{ trans('project::view.Please fill reward >= 0') }}',
        messageValidateRewardGreater: '{{ trans("project::view.Please fill value >= level smaller's reward") }}'
    };
    @if(!isset($export))
        globalPassModule.urlInitPoint = '{{ route('project::point.init.ajax', ['id' => $project->id]) }}';
    @endif
    @if (isset($viewNote) && $viewNote)
        globalPassModule.baselineId = '{{ $projectPoint->id }}';
    @endif
    var token = '{{ csrf_token() }}';
    var project_id = '{{$project->id}}';
    var urlEditBasicInfo = '{{route('project::project.edit_basic_info')}}';
    var urlCheckNoteReport = '{{route('project::point.check-report-note', ['id' => $project->id]) }}';
    var url_costProductivityProglang = '{{route('project::cost.productivity.save', ['id' => $project->id]) }}';
    var typeIssueCSS = {{ Task::TYPE_ISSUE_CSS }};
    var typeCustomerFeedback = {{ Task::TYPE_COMMENDED }};
    var urlGenerateHtml = '{{ route("project::task.generateHtml", ['id' => $project->id]) }}';
    var token = '{{ csrf_token() }}';
    var urlTaskChild = '{{ route("project::task.task_child.ajax") }}';
    var viewBaseline = '{{ $viewBaseline ? $viewBaseline : '' }}';
    var viewBaselineId = '{{ $projectPoint ? $projectPoint->id : '' }}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.3/jquery.number.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>

<script>
    $('.change_value').on('change', function(){
        var valProcCompliance = $('#proc_compliance').val();
        var valCostActual = $('#cost_actual_effort').val();
        var valCss = $('#css_css').val();
        $.ajax({
            type: 'POST',
            data: {
                id: '{{ $project->id }}',
                valProcCompliance : valProcCompliance,
                valCostActual : valCostActual,
                valCss : valCss,
                viewBaseline : viewBaseline,
                viewBaselineId: viewBaselineId,
                _token: token,
            },
            url: '{{ route("project::point.insert-compliance") }}',
            success: function (res) {
                window.location.reload();
            },
        });
    });
</script>
<script>
    RKfuncion.jqueryValidatorExtend.greaterEqual();
    function displayIssue(taskId, self) {
        self = $(self);
        if (self.data('direction') === 'open') {
            $.ajax({
                url: urlTaskChild,
                type: 'post',
                dataType: 'html',
                data: {
                    _token: token,
                    taskId: taskId,
                    index: self.closest('tr').find('td:nth-child(1)').text(),
                },
                success: function (data) {
                    self.closest('tr').after(data);
                    self.data('direction', 'close');
                    self.find('span.glyphicon').removeClass('glyphicon-menu-down').addClass('glyphicon-menu-up');
                },
                error: function() {

                },
                complete: function () {

                }
            });
        } else {
            $('tr[data-parent-id='+self.data('id')+']').remove();
            self.data('direction', 'open');
            self.find('span.glyphicon').removeClass('glyphicon-menu-up').addClass('glyphicon-menu-down');
        }
    }
</script>
<script language="javascript">
    // Hàm xử lý khi thẻ select thay đổi giá trị type được chọn
    // type là tham số truyền vào và cũng chính là thẻ select
    var urlTaskListAjax = $('.task-list-ajax').attr('data-url');
    function typeChanged(type)
    {
        var statusFilter = true;
        var value = type.value;
        var elGrid = $(type).closest('.task-list-ajax');
        url = urlTaskListAjax + '&type=' + value + '&statusFilter=' + statusFilter;
        elGrid.attr('data-url', url);
        $('input[name="page"]').val(1);
        var e = jQuery.Event("keypress");
        e.keyCode = $.ui.keyCode.ENTER;
        elGrid.find('input[name="page"]').trigger(e);
    }
</script>
@if(!$timeCloseProject)
<script type="text/javascript">
    function getPrevNote(e, column) {
        $('.tabs-point-task li i.fa-spin').attr('style', 'display: none;');
        let _this = $(e.target);
        _this.closest('td').find('button').attr("disabled", true).after('<i class="fa fa-spin fa-refresh" style="margin-left: 5px;"></i>').button('loading');
        let data = {
            _token: token,
            id: '{{ $project->id }}',
            column: column
        };
        $.ajax({
            type: 'GET',
            data: data,
            url: '{{ route("project::point.getPoint") }}',
            success: function (res) {
                if(res.status) {
                    $('table td:last-child div.text-display textarea[name="'+ column +'"]').val(res.msg).change();
                }
            },
            complete: function () {
                _this.closest('td').find('button').attr("disabled", false).button('reset').next('i').remove();
            }
        });
    }
</script>
@endif
@endsection
