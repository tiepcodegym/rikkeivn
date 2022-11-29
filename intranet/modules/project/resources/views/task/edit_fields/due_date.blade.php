<?php use Rikkei\Core\View\View as CoreView; ?>
<div class="form-group">
    <label for="duedate" class="col-sm-3 control-label required">{{ trans('project::view.Deadline') }} <em>*</em></label>
    <div class="col-md-9">
        @if($accessEditTask)
            <input name="task[duedate]" class="form-control input-field date-picker" type="text" id="duedate" 
                value="{{ CoreView::getDate($taskItem->duedate) }}" placeholder="yyyy-mm-dd" />
        @else
            <input class="form-control input-field" type="text" id="duedate" disabled
                value="{{ CoreView::getDate($taskItem->duedate) }}" placeholder="yyyy-mm-dd" />
        @endif
    </div>
</div>
