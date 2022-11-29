<?php
use Carbon\Carbon;
?>
<div class="form-group">
    <label class="col-sm-3 control-label required">{{ trans('project::view.Create date') }} <em>*</em></label>
    <div class="col-md-9">
        <?php
        if (!$taskItem->id) {
            $createdAt = Carbon::now()->format('Y-m-d');
        } else {
            $createdAt = $taskItem->created_at;
        }
        ?>
        <input class="form-control input-field date-picker" type="text" name="task[created_at]"
            value="{{ $createdAt }}" placeholder="yyyy-mm-dd H:i:s"{{ $disabledAssign }}/>
    </div>
</div>
