<div class="rate-ot-block" style="display: none;">
    <table class="table table-bordered rate_of_ot">
        <tr>
            <td>{{ trans('project::timesheet.checkin_standard') }}: <strong class="checkin_standard"></strong></td>
            <td>{{ trans('project::timesheet.checkout_standard') }}: <strong class="checkout_standard"></strong></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>{{ trans('project::timesheet.ot_normal_start') }}: <strong class="ot_normal_start"></strong></td>
            <td>{{ trans('project::timesheet.ot_normal_end') }}: <strong class="ot_normal_end"></strong></td>
            <td>{{ trans('project::timesheet.ot_day_off_start') }}: <strong class="ot_day_off_start"></strong></td>
            <td>{{ trans('project::timesheet.ot_day_off_end') }}: <strong class="ot_day_off_end"></strong></td>
        </tr>
        <tr>
            <td>{{ trans('project::timesheet.ot_holiday_start') }}: <strong class="ot_holiday_start"></strong></td>
            <td>{{ trans('project::timesheet.ot_holiday_end') }}: <strong class="ot_holiday_end"></strong></td>
            <td>{{ trans('project::timesheet.ot_overnight_start') }}: <strong class="ot_overnight_start"></strong></td>
            <td>{{ trans('project::timesheet.ot_overnight_end') }}: <strong class="ot_overnight_end"></strong></td>
        </tr>
    </table>
    <div class="row edit-time-block" style="display: none">
        <div class="col-md-12">
            <div class="form-group form-inline" style="margin-right: 20px">
                <label>{{ trans('project::timesheet.checkin') }}</label>
                <input type="text" class="form-control edit-item edit-checkin" style="width: 100px">
            </div>
            <div class="form-group form-inline" style="margin-right: 20px">
                <label>{{ trans('project::timesheet.checkout') }}</label>
                <input type="text" class="form-control edit-item edit-checkout" style="width: 100px">
            </div>
            <div class="form-group form-inline">
                <label>{{ trans('project::timesheet.break_time') }}</label>
                <input type="text" class="form-control edit-item edit-breaktime" style="width: 100px">
            </div>
            <button class="btn btn-primary" id="btn-edit-time" type="button">{{ trans('project::timesheet.edit') }}</button>
            <p class="valid-mesage" style="display: none">{{ trans('project::timesheet.time_invalid') }}</p>
            <p style="font-style: italic;">{{ trans('project::timesheet.note_2') }}</p>
        </div>
    </div>
    <hr>
</div>

<div class="row">
    <div class="col-md-2">
        <label for="">
            <span class="leave leave-1"></span>
            {{ trans('project::timesheet.leave_1') }}
        </label>
    </div>
    <div class="col-md-2">
        <label for="">
            <span class="leave leave-05"></span>
            {{ trans('project::timesheet.leave_05') }}
        </label>
    </div>
    <div class="col-md-2">
        <label for="">
            <span class="leave leave-025"></span>
            {{ trans('project::timesheet.leave_025') }}
        </label>
    </div>
</div>