<?php
    $dayUsed = 0;
    $remainDay = 0;
    if (isset($informationLeaveDay) && count($informationLeaveDay)) {
        $dayUsed = $informationLeaveDay->day_used;
        if ($dayUsed < 0) {
            $dayUsed = 0;
        }
        $remainDay = $informationLeaveDay->remain_day;
        if ($remainDay < 0) {
            $remainDay = 0;
        }
    }
?>
<div class="col-sm-6 managetime-form-group">
    <label class="control-label">{{ trans('manage_time::view.Number days of used') }}</label>
    <div class="input-box">
        <input type="text" name="number_days_used" id="number_days_used" class="form-control" value="{{ $dayUsed }}" readonly />
    </div>
</div>
<div class="col-sm-6 managetime-form-group">
    <label class="control-label">{{ trans('manage_time::view.Number days of remain japan') }}</label>
    <div class="input-box">
        <input type="text" name="number_days_remain" id="number_days_remain" class="form-control" value="{{ $remainDay }}" readonly />
    </div>
</div>