
<?php 
use Rikkei\Project\Model\Task;

$allNameTab = Task::getAllNameTabWorkorder();
?>

<!-- Project log -->
<div id="workorder-content-{{Task::TYPE_WO_PROJECT_LOG}}">
    <div class="grid-data-query task-list-ajax" data-url="{{ URL::route('project::workorder.log.list.ajax', ['id' => $project->id]) }}">
        <h4 class="box-title padding-left-15">{{ trans('project::view.Activity') }}&nbsp; <i class="fa fa-spin fa-refresh hidden"></i></h4>
        <div class="grid-data-query-table">@include('project::components.project_activity')</div>
    </div>
</div>

<!-- Change of work order -->
<div id="workorder-content-{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}">
    @include('project::components.change-workorder')
</div>