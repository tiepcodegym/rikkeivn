<?php
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\ProjectMember;
?>
<div class="form-group form-group-select2">
@if($taskItem->type == Task::TYPE_COMMENDED)
<label for="status" class="col-sm-3 control-label required">{{ trans('project::view.Select project') }}<em>*</em></label>
@else
<label for="status" class="col-sm-3 control-label">{{ trans('project::view.Select project') }}</label>
@endif
<div class="col-md-9">
    @if($accessEditTask)
        <select name="task[project_id]" class="select-search has-search" id="project" onchange="projectChanged(this)">
                <option value=""z>&nbsp</option>
            @foreach ($project as $projs)
                <option value="{{ $projs->id }}">{{ $projs->name }}</option>
            @endforeach
        </select>
    @else
        <input class="form-control input-field" type="text" id="status" disabled
            value="" />
    @endif
</div>
</div>
