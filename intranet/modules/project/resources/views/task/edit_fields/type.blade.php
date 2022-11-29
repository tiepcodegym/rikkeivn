<?php
use Rikkei\Project\Model\Task;


if ($taskItem->isTaskIssues()) {
    $taskTypes = Task::getTypeIssuesCreator();
} else if ($taskItem->isTaskCustomerIdea()){
    $taskTypes = Task::getTypeCustomerIdea();
} else {
    return;
}
?>

<div class="form-group form-group-select2">
    <label for="type" class="col-sm-3 control-label">{{ trans('project::view.Type') }}</label>
    <div class="col-md-9">
        <select name="task[type]" class="select-search" id="type"{{ $disabledAssign }}>
            @foreach ($taskTypes as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}"{{ $taskItem->type ==  $optionValue ? ' selected' : ''}}>{{ $optionLabel }}</option>
            @endforeach
        </select>
    </div>
</div>
