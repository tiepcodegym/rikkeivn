<div class="form-group">
    <label for="title" class="col-sm-3 control-label required">{{trans('project::view.Title')}} <em>*</em></label>
    <div class="col-md-9">
        @if($accessEditTask)
            <input name="task[title]" class="form-control input-field" type="text" id="title" value="{{ $taskItem->title }}" />
        @else
            <input class="form-control input-field" type="text" id="title" value="{{ $taskItem->title }}" disabled />
        @endif
    </div>
</div>
