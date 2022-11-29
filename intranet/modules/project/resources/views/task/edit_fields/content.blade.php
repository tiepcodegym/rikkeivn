<div class="form-group">
    <label for="content" class="col-sm-3 control-label required" data-label="{{ trans('project::view.Content') }}">{{ trans('project::view.Content') }} <em>*</em></label>
    <div class="col-md-9">
        @if($accessEditTask)
            <textarea name="task[content]" class="form-control input-field text-resize-y" id="content" 
                rows="5">{{ $taskItem->content }}</textarea>
        @else
            <textarea name="task[content]" class="form-control input-field text-resize-y" id="content" 
                rows="5" disabled>{{ $taskItem->content }}</textarea>
        @endif
    </div>
</div>
