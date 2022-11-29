<div class="form-group form-group-select2">
    <label for="priority" class="col-sm-3 control-label">{{ trans('project::view.Priority') }}</label>
    <div class="col-md-9">
        @if($accessEditTask)
            <select name="task[priority]" class="select-search" id="priority">
                @foreach ($taskPriorities as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}"{{ $taskItem->priority == $optionValue ? ' selected' : '' }}>{{ $optionLabel }}</option>
                @endforeach
            </select>
        @else
            <input class="form-control input-field" type="text" id="priority" disabled
                value="{{ $taskItem->getPriority() }}" />
        @endif
    </div>
</div>
