<div class="form-group">
    <label for="cause" class="col-sm-3 control-label required" data-label="{{ trans('project::view.Root cause') }}">{{ trans('project::view.Root cause') }}</label>
    <div class="col-md-9">
        <textarea name="task[cause]" class="form-control input-field text-resize-y" id="content" 
            rows="5" {{ $accessEditTask ? '' : 'disabled' }}>{{ $taskItem->cause }}</textarea>
    </div>
</div>
