<?php
use Rikkei\Core\View\View;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Risk;
use Carbon\Carbon;

$isTypeTrainingOrRD = $project->isTypeTrainingOfRD();
$viewProject = new ViewProject();
$colors = $viewProject->getColorByValue();
$colorProcess = $viewProject->getProcessColor($projectPointInformation['proc_compliance'], $projectPointInformation['proc_compliance_target'], $projectPointInformation['proc_compliance_lcl'], $checkCate);
$colorInput = $colors[$colorProcess];
?>
<div class="row">
    <div class="col-md-12">
        <p>
            <span>{{ trans('project::view.Process None Compliance') }} (#):</span>
            @if($isTypeTrainingOrRD)
            <span>{{trans('project::view.NA')}}</span>
            @else
            <span class="pp-value proc_compliance_point">{{ $projectPointInformation['proc_compliance_point'] }}</span>
            @endif
        </p>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data table-point">
        <tbody>
            <tr>
                <td class="align-center col-title">
                    <strong>{{ trans('project::view.Project Information') }}</strong>
                </td>
                <td class="align-center col-lcl">{{ trans('project::view.LCL') }}</td>
                <td class="align-center col-lcl">{{ trans('project::view.Target') }}</td>
                <td class="align-center col-lcl">{{ trans('project::view.UCL') }}</td>
                <td class="align-center col-value">{{ trans('project::view.Value') }}</td>
                @if(!$viewBaseline || $viewNote)
                    <td class="align-center col-note">
                        {!! $textHtmlNoteHead !!}
                    </td>
                @endif
            </tr>
            
            <tr class="">
                <td class="align-left" title="{{ trans('project::view.Formula PCV') }}">{{ trans('project::view.PCV') }}</td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['proc_compliance_lcl'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['proc_compliance_target'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['proc_compliance_ucl'] }}
                    @endif
                </td>
                <td class="align-right" bgcolor="{{ $colorInput }}">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <input style="background-color: {{ $colorInput }}" title="{{ trans('project::view.Formula PCV') }}" id="proc_compliance" class="pp-value change_value form-control" value="{{ $projectPointInformation['proc_compliance'] }}" @if(!$project->isOpen() && !$permissionEditPP['qa']) disabled @endif/>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->proc_compliance_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->proc_compliance_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="proc_compliance_note" class="note-input form-control" 
                                rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php 
                                endif; ?>>{{ $projectPoint->proc_compliance_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="proc_compliance_note" class="note-input form-control" 
                                rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php 
                                endif; ?>>{{ $projectPoint->proc_compliance_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'proc_compliance_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
                @endif
            </tr>
        </tbody>
    </table>
</div>
<div class="grid-data-query task-list-ajax"
     data-type="{{ Task::TYPE_ISSUE_PROC }}">
    <h3>{{ trans('project::view.Issues list') }}&nbsp; <i class="fa fa-spin fa-refresh"></i>
    </h3>
    <div class="grid-data-query-table"></div>
</div>

<div class="grid-data-query risk-list-ajax"
     data-type="{{ Risk::TYPE_PROCESS }}">
    <h3>{{ trans('project::view.Risk list') }}&nbsp; <i class="fa fa-spin fa-refresh"></i>
    </h3>
    <div class="grid-data-query-table"></div>
</div>

@include('project::point.tab.add_noti_remove_issue_list')
