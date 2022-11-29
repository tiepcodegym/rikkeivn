<?php
use Carbon\Carbon;
use Rikkei\Project\Model\TaskComment;
use Rikkei\Project\Model\Task;

$allNameTab = Task::getAllNameTabWorkorder();
$allStatus = Task::statusLabel();
?>

@if(isset($detail))
<div class="table-content-{{$allNameTab[Task::TYPE_WO_QUALITY_PLAN]}}">
    <h3 id="title-quality-plan">{{$allNameTab[Task::TYPE_WO_QUALITY_PLAN]}}</h3>
    <h5>
        {{trans('project::view.Strategy')}}
        @if(isset($permissionEdit) && $permissionEdit)
        <span class="slove-strategy">
            <span class="btn-edit edit-strategy"><i class="fa fa-pencil-square-o"></i></span>
            <span class="btn btn-primary refresh-strategy display-none width-38"><i class="fa fa-refresh"></i></span>
        </span>
        @endif
    </h5>
    @if($qualityPlan->content)
    <p class="padding-10 content-strategy white-space">{{$qualityPlan->content}}</p>
    @if(isset($permissionEdit) && $permissionEdit)
    <input type="text" value="{{ $qualityPlan->content }}" class="white-space form-control input-content-strategy display-none width-100">
    @endif
    @else
    @if(isset($permissionEdit) && $permissionEdit)
    <p class="padding-10 content-strategy">{{trans('project::view.Content strategy here')}}</p>
    <input type="text" class="form-control display-none input-content-strategy width-100" value="{{trans('project::view.Content strategy here')}}">
    @endif
    @endif
    <h5 class="table-content-{{Task::TYPE_WO_CRITICAL_DEPENDENCIES}}">
        {{trans('project::view.Activities')}}
        @if(isset($permissionEdit) && $permissionEdit)
        <span class="slove-activities width-38">
            <a href="{{ route('project::task.add', ['id' => $project->id ]) }}?type={{Task::TYPE_QUALITY_PLAN}}" 
               class="btn-add">
                <i class="fa fa-plus"></i>
            </a>
        </span>
        @endif
    </h5>
    <div class="table-responsive" id="table-quality-plan">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
                <tr>
                    <th class="width-5-per">{{trans('project::view.No')}}</th>
                    <th class="width-15-per">{{trans('project::view.Activity')}}</th>
                    <th>{{trans('project::view.Date/Frequency')}}</th>
                    <th class="width-12-per">{{trans('project::view.Assignee')}}</th>
                    <th class="width-10-per">{{trans('project::view.Duedate')}}</th>
                    <th class="width-12-per">{{trans('project::view.Actual')}}</th>
                    <th class="width-12-per">{{trans('project::view.Status')}}</th>
                    <?php /*<th class="width-15-per">{{trans('project::view.Note')}}</th>*/ ?>
                </tr>
            </thead>
            <tbody>
            @foreach($qualityPlans as $key => $quality)
                <tr>
                    <td>{{$key + 1}}</td>
                    <td><a href="{{ route('project::task.edit', ['id' => $quality->id ]) }}">{{$quality->title}}</a></td>
                    <td>{{$quality->content}}</td>
                    <td>{{preg_replace('/@.*/', '',$quality->email)}}</td>
                    <td>
                    @if($quality->duedate)
                        {{Carbon::parse($quality->duedate)->format('Y-m-d')}}
                    @endif
                    </td>
                    <td>
                    @if($quality->actual_date)
                        {{Carbon::parse($quality->actual_date)->format('Y-m-d')}}
                    @endif
                    </td>
                    <td>
                        {{ isset($allStatus[$quality->status]) ? $allStatus[$quality->status] : '' }}
                    </td>
                    <?php /*<td>
                        <span class="white-space">
                            {!! TaskComment::getCommentOfTask($quality->id) !!}
                        </span>
                    </td>*/ ?>
                </tr>
            @endforeach    
            </tbody>  
        </table>
    </div>
</div>
@endif