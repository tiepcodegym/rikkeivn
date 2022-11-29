<div class="form-group form-group-select2">
    <label for="action_type" class="col-sm-3 control-label">{{ trans('project::view.Action type') }}</label>
    <div class="col-md-9">
        @if($accessEditTask)
            <select name="task[action_type]" class="select-search" id="action_type">
                @foreach ($taskAction as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}"{{ $taskItem->action_type == $optionValue ? ' selected' : '' }}>{{ $optionLabel }}</option>
                @endforeach
            </select>
        @else
            <input class="form-control input-field" type="text" id="priority" disabled
                value="{{ $taskItem->getAction() }}" />
        @endif
    </div>
</div>
