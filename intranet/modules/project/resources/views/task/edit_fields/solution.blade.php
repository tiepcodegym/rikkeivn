<div class="form-group">
    <label for="content" class="col-sm-3 control-label required" data-label="{{ trans('project::view.Solution') }}">{{ trans('project::view.Solution') }}</label>
    <div class="col-md-9">
        <textarea name="task[solution]" class="form-control input-field text-resize-y" id="content" 
            rows="5" {{ $accessEditTask ? '' : 'disabled' }}>{{ $taskItem->solution }}</textarea>
    </div>
</div>
