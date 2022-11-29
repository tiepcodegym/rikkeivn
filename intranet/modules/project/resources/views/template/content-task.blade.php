<?php
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\View\View;
use Carbon\Carbon;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\StageAndMilestone;
?>
@if($type != Task::TYPE_WO_PROJECT && isset($title) && $title)
<p>{{ $title }}</p>
@endif
@if($type == Task::TYPE_WO_DELIVERABLE)
    @foreach($inputs as $key => $input)
    <p class="text-indent-10">{{trans('project::view.Object')}} {{$key + 1}}</p>
    <ul>
        <li>{{trans('project::view.Deliverable')}} : <strong>{{$input->title}}</strong></li>
        <li>{{trans('project::view.Committed date of delivery')}} : <strong>{{$input->committed_date}}</strong></li>
        @if ($input->actual_date)
        <li>{{trans('project::view.Actual Date')}} : <strong>{{$input->actual_date}}</strong></li>
        @endif
        <li>{{trans('project::view.Stage')}} : <strong>{{View::getStageDeliverable($input, $allStage)}}</strong></li>
        @if($input->note)
        <li>{{trans('project::view.Note')}} : <strong>{{$input->note}}</strong></li>
        @endif
    </ul>
    @endforeach
@elseif ($type == Task::TYPE_WO_STAGE_MILESTONE)
    <?php
        $allStage = StageAndMilestone::getAllStage();
    ?>
    @foreach($inputs as $key => $input)
    <p class="text-indent-10">{{trans('project::view.Object')}} {{$key + 1}}</p>
    <ul>
        <li>{{trans('project::view.Stage')}} : <strong>{{View::generateStage($input, $allStage)}}</strong></li>
        <li>{{trans('project::view.Description')}} : <strong>{{$input->description}}</strong></li>
        <li>{{trans('project::view.Milestone Output')}} : <strong>{{$input->milestone}}</strong></li>
    </ul>
    @endforeach
@elseif ($type == Task::TYPE_WO_TRANING)
    @foreach($inputs as $key => $input)
    <p class="text-indent-10">{{trans('project::view.Object')}} {{$key + 1}}</p>
    <ul>
        <li>{{trans('project::view.Topic')}} : <strong>{{$input->topic}}</strong></li>
        <li>{{trans('project::view.Description')}} : <strong>{{$input->description}}</strong></li>
        <li>{{trans('project::view.Participants')}} : <strong>{{$input->participants}}</strong></li>
        <li>{{trans('project::view.Time')}} : <strong>{{$input->time}}</strong></li>
        <li>{{trans('project::view.Waiver criteria')}} : <strong>{{$input->walver_criteria}}</strong></li>
    </ul>
    @endforeach
@elseif ($type == Task::TYPE_WO_EXTERNAL_INTERFACE)
    @foreach($inputs as $key => $input)
    <p class="text-indent-10">{{trans('project::view.Object')}} {{$key + 1}}</p>
    <ul>
        <li>{{trans('project::view.Name')}} : <strong>{{$input->name}}</strong></li>
        <li>{{trans('project::view.Position')}} : <strong>{{$input->position}}</strong></li>
        <li>{{trans('project::view.Responsibilities')}} : <strong>{{$input->responsibilities}}</strong></li>
        <li>{{trans('project::view.Text, Fax, Email')}} : <strong>{{$input->contact}}</strong></li>
    </ul>
    @endforeach
@elseif ($type == Task::TYPE_WO_COMMINUCATION)
    @foreach($inputs as $key => $input)
    <p class="text-indent-10">{{trans('project::view.Object')}} {{$key + 1}}</p>
    <ul>
        <li>{{trans('project::view.Content')}} : <strong>{{$input->content}}</strong></li>
    </ul>
    @endforeach
@elseif ($type == Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE)
    @foreach($inputs as $key => $input)
    <p class="text-indent-10">{{trans('project::view.Object')}} {{$key + 1}}</p>
    <ul>
        <li>{{trans('project::view.Software/Hardware')}} : <strong>{{$input->soft_hard_ware}}</strong></li>
        <li>{{trans('project::view.Purpose')}} : <strong>{{$input->purpose}}</strong></li>
        @if($input->note)
        <li>{{trans('project::view.Note')}} : <strong>{{$input->note}}</strong></li>
        @endif
    </ul>
    @endforeach
@elseif($type == Task::TYPE_WO_PERFORMANCE)
    <ul>
        <li>{{trans('project::view.Change time end project to')}} :  <strong>{{$inputs[0]->end_at}}</strong></li>
    </ul>
@elseif($type == Task::TYPE_WO_QUALITY)
    @foreach($inputs as $input)
    @if($input->billable_effort)
        @if($quality)
        <li>{{trans('project::view.Change billable effort from')}} <strong>{{$quality->billable_effort}}</strong> {{trans('project::view.to')}} <strong>{{$input->billable_effort}}</strong></li>
        @else
        <li>{{trans('project::view.Change billable effort to')}} : <strong>{{$input->billable_effort}}</strong></li>
        @endif
    @endif
    @if($input->plan_effort)
        @if($quality)
        <li>{{trans('project::view.Change plan effort from')}} <strong>{{$quality->plan_effort}}</strong> {{trans('project::view.to')}} <strong>{{$input->plan_effort}}</strong></li>
        @else
        <li>{{trans('project::view.Change plan effort to')}} : <strong>{{$input->plan_effort}}</strong></li>
        @endif
    @endif
    @endforeach
@elseif($type == Task::TYPE_WO_PROJECT_MEMBER)
    @foreach($inputs as $key => $input)
    <p class="text-indent-10">{{trans('project::view.Object')}} {{$key + 1}}</p>
    <ul>
        <li>{{trans('project::view.Position')}} : <strong>{{ProjectMember::getTypeMember()[$input->type]}}</strong></li>
        <li>{{trans('project::view.Name')}} : <strong>{{$input->name}}</strong></li>
        <li>{{trans('project::view.Start date')}} : <strong>{{$input->start_at}}</strong></li>
        <li>{{trans('project::view.End date')}} : <strong>{{$input->end_at}}</strong></li>
        <li>{{trans('project::view.Effort')}} : <strong>{{$input->effort}}</strong></li>
    </ul>
    @endforeach
@elseif($type == Task::TYPE_WO_PROJECT)
    <?php
        $isChangeName = View::isChangeValueProject($inputs, $project, 'name');
        $isChangeProjectCode = View::isChangeValueProject($inputs, $project, 'project_code');
        $isChangeType = View::isChangeValueProject($inputs, $project, 'type');
        $isChangeState = View::isChangeValueProject($inputs, $project, 'state');
        $isChangeStartAt = View::isChangeValueProject($inputs, $project, 'start_at');
        $isChangeEndAt = View::isChangeValueProject($inputs, $project, 'end_at');
        $isChangeLeaderId = View::isChangeValueProject($inputs, $project, 'leader_id');
        $isChangeTeamId = View::isChangeValueProject($inputs, $project, 'team_id');
    ?>
        @if($isChangeName)
        <li>{{trans('project::view.Change project name from')}} <strong>{{$project['name']}}</strong> {{trans('project::view.to')}} <strong>{{$inputs['name']}}</strong></li>
        @endif
        @if($isChangeProjectCode)
        <li>{{trans('project::view.Change project alias from')}} <strong>{{$project['project_code']}}</strong> {{trans('project::view.to')}} <strong>{{$inputs['project_code']}}</strong></li>
        @endif
        @if($isChangeType)
        <li>{{trans('project::view.Change project type from')}} <strong>{{$labelTypeProject[$project['type']]}}</strong> {{trans('project::view.to')}} <strong>{{$labelTypeProject[$inputs['type']]}}</strong></li>
        @endif
        @if($isChangeState)
        <li>{{trans('project::view.Change project status from')}} <strong>{{$labelStatusProject[$project['state']]}}</strong> {{trans('project::view.to')}} <strong>{{$labelStatusProject[$inputs['state']]}}</strong></li>
        @endif
        @if($isChangeStartAt)
        <li>{{trans('project::view.Change project start date from')}} <strong>{{Carbon::parse($project->start_at)->format('Y-m-d')}}</strong> {{trans('project::view.to')}} <strong>{{Carbon::parse($inputs['start_at'])->format('Y-m-d')}}</strong></li>
        @endif
        @if($isChangeEndAt)
        <li>{{trans('project::view.Change project end date from')}} <strong>{{Carbon::parse($project['end_at'])->format('Y-m-d')}}</strong> {{trans('project::view.to')}} <strong>{{Carbon::parse($inputs['end_at'])->format('Y-m-d')}}</strong></li>
        @endif
        @if($isChangeLeaderId)
        <li>{{trans('project::view.Change group leader from')}} <strong>{{Employee::getNameEmpById($project->leader_id)}}</strong> {{trans('project::view.to')}} <strong>{{Employee::getNameEmpById($inputs['leader_id'])}}</strong></li>
        @endif
        @if($isChangeTeamId)
        <?php
            $allTeamName = Team::getAllTeam();
            $allTeam = Project::getAllTeamOfProject($project->id);
            $allTeamDraft = Project::getAllTeamOfProject($inputs['id']);
        ?>
        <li>{{trans('project::view.Change group join project from')}} <strong>{{View::getLabelTeamOfProject($allTeamName, $allTeam)}}</strong> {{trans('project::view.to')}} <strong>{{View::getLabelTeamOfProject($allTeamName, $allTeamDraft)}}</strong></li>
        @endif
@else
    @foreach($inputs as $key => $input)
    <p class="text-indent-10">{{trans('project::view.Object')}} {{$key + 1}}</p>
    <ul>
        <li>{{trans('project::view.Content')}} : <strong>{{$input->content}}</strong></li>
        @if($input->note)
        <li>{{trans('project::view.Note')}} : <strong>{{$input->note}}</strong></li>
        @endif
    </ul>
    @endforeach
@endif    