<!-- Modal get timekeeping data -->
<div id="modal_get_timekeeping_data" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" style="font-size: 24px;">{{ trans('manage_time::view.Update salary table by data timekeeping') }}</h4>
            </div>
            <form method="POST" action="{{ route('manage_time::timekeeping.salary.get-timekeeping-data') }}">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" id="salary_table_id_for_update_timekeeping" name="salary_table_id" class="form-control" />
                        <label class="control-label">{{ trans('manage_time::view.Timekeeping table') }} <em>*</em></label>
                        <div class="input-box">
                            <select class="form-control select-search" name="timekeeping_table_id" id="timekeeping_table_id" style="width: 100%">
                                @if (isset($timekeepingTablesList) && count($timekeepingTablesList))
                                    @foreach ($timekeepingTablesList as $item)
                                        <option value="{{ $item->timekeeping_table_id }}">{{ $item->timekeeping_table_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <label id="timekeeping_table_id-error" class="managetime-error" for="timekeeping_table_id">{{ trans('manage_time::view.This field is required') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                    <button type="submit" class="btn-add" id="btn_submit_get_timekeeping_data" onclick="return checkGetTimekeepingData();" data-loading-text="<i class='fa fa-spin fa-refresh'></i> {{ trans('manage_time::view.Save') }}"><i class="fa fa-save"></i> {{ trans('manage_time::view.Save') }}</button>
                    <input type="hidden" id="check_get_timekeeping_data" name="" value="0">
                </div>
            </form>
        </div>
    </div>
</div>