<?php
use Rikkei\Core\View\View;
use Rikkei\Project\View\View as ViewProject;
use Carbon\Carbon;
use Rikkei\Project\Model\Task;

$viewProject = new ViewProject();
$colors = $viewProject->getColorByValue();
$timelinessColor = $viewProject->getTimelinessColor($projectPointInformation['tl_deliver'], $projectPointInformation['tl_deliver_target'], $projectPointInformation['tl_deliver_lcl'], $projectPointInformation['tl_deliver_ucl'], $checkCate);
$isTypeTrainingOrRD = $project->isTypeTrainingOfRD();
?>

<div class="row">
    <div class="col-md-12">
        <p>
            <span>{{ trans('project::view.Deliverable') }} (%):</span>
            @if($isTypeTrainingOrRD)
            <span>{{trans('project::view.NA')}}</span>
            @else
            <span class="pp-value tl_deliver_point">{{ $projectPointInformation['tl_deliver_point'] }}</span>
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
                <td class="align-center col-input">{{ trans('project::view.Value') }}</td>
                @if(!$viewBaseline || $viewNote)
                    <td class="align-center col-note">
                        {!! $textHtmlNoteHead !!}
                    </td>
                @endif
            </tr>
            <tr class="">
                <td class="align-left" title="{{ trans('project::view.Formula timeliness') }}">{{ trans('project::view.timeliness') }} (%)</td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['tl_deliver_lcl'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['tl_deliver_target'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['tl_deliver_ucl'] }}
                    @endif
                </td>
                <td class="align-right" bgcolor="{{ $colors[$timelinessColor] }}">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span title="{{ trans('project::view.Formula timeliness') }}"
                              data-toggle="tooltip" class="pp-value tl_deliver">&nbsp;
                        {{ $projectPointInformation['tl_deliver'] }}
                    </span>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->tl_deliver_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->tl_deliver_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="tl_deliver_note" class="note-input form-control" 
                                rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php 
                                endif; ?>>{{ $projectPoint->tl_deliver_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="tl_deliver_note" class="note-input form-control" 
                                rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php 
                                endif; ?>>{{ $projectPoint->tl_deliver_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'tl_deliver_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
                @endif
            </tr>
            
        </tbody>
    </table>
</div>

<div class="grid-data-query task-list-ajax" data-url="{{ URL::route('project::point.deliver.list.ajax', ['id' => $project->id]) }}"
    data-type="{{ Task::TYPE_OUT_DELIVER }}">
    <h3>{{ trans('project::view.Deliverable list') }}&nbsp; <i class="fa fa-spin fa-refresh"></i></h3>
    <div class="grid-data-query-table"></div>
</div>

@include('project::point.tab.add_noti_remove_issue_list')
