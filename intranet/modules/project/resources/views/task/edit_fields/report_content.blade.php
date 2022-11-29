<div class="form-group">
    <label for="content" class="col-sm-3 control-label" data-label="{{ trans('project::view.Report') }}">{{ trans('project::view.Report') }}</label>
    <div class="col-md-9">
        @if($accessEditTask)
            <textarea name="task[report_content]" class="form-control input-field text-resize-y" id="report_content" 
                rows="5">{{ $taskItem->report_content }}</textarea>
        @else
            <textarea name="task[report_content]" class="form-control input-field text-resize-y" id="report_content" 
                rows="5" disabled>{{ $taskItem->report_content }}</textarea>
        @endif
    </div>
</div>
