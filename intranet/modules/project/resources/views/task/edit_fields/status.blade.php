<?php
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\ProjectMember;

?>
<div class="form-group form-group-select2">
<label for="status" class="col-sm-3 control-label">{{ trans('project::view.Status') }}</label>
<div class="col-md-9">
    @if($accessEditTask || (isset($project) && Task::hasEditStatusTasks($taskItem, $project)))
        <select name="task[status]" class="select-search" id="status">
            @foreach ($taskStatus as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}"{{ $taskItem->status == $optionValue ? ' selected' : '' }}>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @else
        <input class="form-control input-field" type="text" id="status" disabled
            value="{{ $taskItem->getStatus() }}" />
    @endif
</div>
</div>
