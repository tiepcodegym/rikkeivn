<?php use Rikkei\Core\View\View as CoreView; ?>

<div class="form-group">
    <label for="actual-date" class="col-sm-3 control-label">{{ trans('project::view.Actual date') }}</label>
    <div class="col-md-9">
        @if($accessEditTask)
            <input name="task[actual_date]" class="form-control input-field date-picker" type="text" id="actual-date" 
                value="{{ CoreView::getDate($taskItem->actual_date) }}" placeholder="yyyy-mm-dd" />
        @else
            <input class="form-control input-field" type="text" id="actual-date" disabled
                value="{{ CoreView::getDate($taskItem->actual_date) }}" placeholder="yyyy-mm-dd" />
        @endif
    </div>
</div>
