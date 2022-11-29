<?php use Rikkei\Core\View\View as CoreView; ?>
<div class="form-group form-group-select2">
    <label for="assign" class="col-sm-3 control-label required">{{ trans('project::view.Assignee') }} <em>*</em></label>
    <div class="col-md-9 fg-valid-custom">
        <select name="task_assign[]" class="select-search" id="assign"{{ $disabledAssign . $multi }}
            data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
            @if ($assignees)
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->employee_id }}" selected>{{ CoreView::getNickName($assignee->email) }}</option>
                @endforeach
            @endif
        </select>
    </div>
</div>
