<?php
use Rikkei\Core\View\View as ViewHelper;
use Carbon\Carbon;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Project\Model\Task;

$allNameTab = Task::getAllNameTabWorkorder();
?>


@if(isset($detail))
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_QUALITY]}}" id="table-quality">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="width-20-per">{{trans('project::view.Metrics')}}</th>
                <th class="width-30-per">{{trans('project::view.Targeted')}}</th>
                <th>{{trans('project::view.Note')}}</th>
            </tr>
        </thead>
        <tbody>
            @if (!$project->isOpen())
                <tr>
                    <td>
                        <span>{{trans('project::view.Actual Effort')}} ({{trans('project::view.MM')}})</span>
                    </td>
                    <td>
                        <span>{{ $projectPointInformation['cost_actual_effort'] }}</span>
                    </td>
                    <td>
                        <span>
                            @if($permissionUpdateNote)
                                <textarea class="form-control input-project-wo-note input-qua_actual" name="qua_actual" rows="2">{{ $projectNote->qua_actual }}</textarea>
                            @else
                            {{ $projectNote->qua_actual }}
                            @endif
                        </span>
                    </td>
                </tr>
            @endif
            <tr>
                <td>
                    <span>{{trans('project::view.Effort Effectiveness')}} ({{trans('project::view.%')}})</span>
                </td>
                <td>
                    <span>{{ $projectPointInformation['cost_target'] }}</span>
                </td>
                <td>
                    <span>
                        @if($permissionUpdateNote)
                            <textarea class="form-control input-project-wo-note input-qua_effectiveness" name="qua_effectiveness" rows="2">{{ $projectNote->qua_effectiveness }}</textarea>
                        @else
                        {{ $projectNote->qua_effectiveness }}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span>{{trans('project::view.Customer Satisfation')}} ({{trans('project::view.Point')}})</span>
                </td>
                <td>
                    <span>{{ $projectPointInformation['css_css_target'] }}</span>
                </td>
                <td>
                    <span>
                        @if($permissionUpdateNote)
                            <textarea class="form-control input-project-wo-note input-qua_css" name="qua_css" rows="2">{{ $projectNote->qua_css }}</textarea>
                        @else
                        {{ $projectNote->qua_css }}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span>{{trans('project::view.Timeliness')}} ({{trans('project::view.%')}})</span>
                </td>
                <td>
                    <span>{{ $projectPointInformation['tl_deliver_target'] }}</span>
                </td>
                <td>
                    <span>
                        @if($permissionUpdateNote)
                            <textarea class="form-control input-project-wo-note input-qua_timeliness" name="qua_timeliness" rows="2">{{ $projectNote->qua_timeliness }}</textarea>
                        @else
                        {{ $projectNote->qua_timeliness }}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span>{{trans('project::view.Leakage')}} ({{trans('project::view.%')}})</span>
                </td>
                <td>
                    <span>{{ $projectPointInformation['qua_leakage_target'] }}</span>
                </td>
                <td>
                    <span>
                        @if($permissionUpdateNote)
                            <textarea class="form-control input-project-wo-note input-qua_leakage" name="qua_leakage" rows="2">{{ $projectNote->qua_leakage }}</textarea>
                        @else
                        {{ $projectNote->perf_pm }}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span>{{trans('project::view.Process Compliance')}} ({{trans('project::view.NC')}})</span>
                </td>
                <td>
                    <span>{{ $projectPointInformation['proc_compliance_target'] }}</span>
                </td>
                <td>
                    <span>
                        @if($permissionUpdateNote)
                        <textarea class="form-control input-project-wo-note input-qua_process" name="qua_process" rows="2">{{ $projectNote->qua_process }}</textarea>
                        @else
                        {{ $projectNote->qua_process }}
                        @endif
                    </span>
                </td>
            </tr>
            @if (!$project->isOpen())
                <tr>
                    <td>
                        <span>{{trans('project::view.Project Report')}} ({{trans('project::view.%')}})</span>
                    </td>
                    <td>
                        <span>{{ $projectPointInformation['proc_report'] }}</span>
                    </td>
                    <td>
                        <span>
                            @if($permissionUpdateNote)
                            <textarea class="form-control input-project-wo-note input-qua_report" name="qua_report" rows="2">{{ $projectNote->qua_report }}</textarea>
                            @else
                            {{ $projectNote->qua_report }}
                            @endif
                        </span>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@include('project::components.quality-plan')
@endif