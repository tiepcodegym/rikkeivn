<div class="form-group form-group-select2" title="{!!trans('project::view.hinit freequency css')!!}">
    <label for="priority" class="col-sm-3 control-label">{{ trans('project::view.Freequency Of Reporting') }}</label>
    <div class="col-md-9">
        @if($accessEditTask)
        <select name="task[freequency_report]" class="select-search" id="freequency">
                @foreach ($taskFreequencyOfRp as $optionValue => $optionLabel)
                    <option title="Phụ thuộc theo priority:&#013; -Low, Normal: Weekly.&#013; -High, Serious: Daily." value="{{ $optionValue }}"{{ $taskItem->freequency_report == $optionValue ? ' selected' : '' }}>{{ $optionLabel }}</option>
                @endforeach
            </select>
        @else
            <input class="form-control input-field" type="text" id="freequency" disabled
                value="{{ $taskItem->getFreequencyOfReport() }}" />
        @endif
    </div>
</div>
