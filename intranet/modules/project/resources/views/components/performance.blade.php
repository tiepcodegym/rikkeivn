<?php
use Rikkei\Core\View\View as ViewHelper;
use Carbon\Carbon;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\Performance;
use Rikkei\Project\Model\Task;
$allNameTab = Task::getAllNameTabWorkorder();

?>

@if(isset($detail))
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_PERFORMANCE]}}" id="table-performance">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="width-20-per">{{trans('project::view.Metrics')}}</th>
                <th class="width-30-per">{{trans('project::view.Targeted')}}</th>
                <th>{{trans('project::view.Note')}}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <span>{{trans('project::view.Duration')}} ({{trans('project::view.Day')}})</span>
                </td>
                <td>
                    @if($project->end_at)
                    <span class="duration">{!!$duration!!}</span>
                    @endif
                </td>
                <td>
                    <span>
                        @if($permissionUpdateNote)
                            <textarea class="form-control input-project-wo-note input-perf_duration" name="perf_duration" rows="2">{{$projectNote->perf_duration}}</textarea>
                        @else
                        {{$projectNote->perf_duration}}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span>{{trans('project::view.Maximum Team Size')}} ({{trans('project::view.Person')}})</span>
                </td>
                <td>
                    <span class="team-size">{!!$effort['count']!!}</span>
                </td>
                <td>
                    <span>
                        @if($permissionUpdateNote)
                            <textarea class="form-control input-project-wo-note input-perf_plan_effort" name="perf_plan_effort" rows="2">{{$projectNote->perf_plan_effort}}</textarea>
                        @else
                        {{$projectNote->perf_plan_effort}}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span>{{trans('project::view.Effort Usage')}} ({{ $project->getLabelTypeMM() }})</span>
                </td>
                <td>
                    <span class="effort-usage">{!!$effort['total']!!}</span>
                </td>
                <td>
                    <span>
                        @if($permissionUpdateNote)
                            <textarea class="form-control input-project-wo-note input-perf_effort_usage" name="perf_effort_usage" rows="2">{{$projectNote->perf_effort_usage}}</textarea>
                        @else
                        {{$projectNote->perf_effort_usage}}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{trans('project::view.Development')}} ({{trans('project::view.%')}})</span>
                </td>
                <td>
                    <span class="effort-dev">{!!$effort['dev']!!}</span>
                </td>
                <td>
                    <span>
                        @if($permissionUpdateNote)
                            <textarea class="form-control input-project-wo-note input-perf_dev" name="perf_dev" rows="2">{{$projectNote->perf_dev}}</textarea>
                        @else
                        {{$projectNote->perf_dev}}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{trans('project::view.Management')}} ({{trans('project::view.%')}})</span>
                </td>
                <td>
                    <span class="effort-pm">{!!$effort['pm']!!}</span>
                </td>
                <td>
                    <span>
                        @if($permissionUpdateNote)
                            <textarea class="form-control input-project-wo-note input-perf_pm" name="perf_pm" rows="2">{{$projectNote->perf_pm}}</textarea>
                        @else
                        {{$projectNote->perf_pm}}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{trans('project::view.Quality')}} ({{trans('project::view.%')}})</span>
                </td>
                <td>
                    <span class="effort-qa">{!!$effort['qa']!!}</span>
                </td>
                <td>
                    <span>
                        @if($permissionUpdateNote)
                        <textarea class="form-control input-project-wo-note input-perf_qa" name="perf_qa" rows="2">{{$projectNote->perf_qa}}</textarea>
                        @else
                        {{$projectNote->perf_qa}}
                        @endif
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endif