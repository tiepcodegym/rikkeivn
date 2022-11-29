<div class="modal fade in" id="modal_add_emp">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" action="{{ route("manage_time::timekeeping.addEmpToTimekeeping") }}"  enctype="multipart/form-data" autocomplete="off">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <h3 class="box-title">{{ trans('manage_time::view.Add employee') }}</h3>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="timekeeping_table_id" value="{{ $timeKeepingTable->id }}" />
                    <select name="employee[]" style="width: 100%" class="form-control select2" data-remote-url="{{ URL::route('team::employee.list.search.ajax', ['type' => 'not']) }}" multiple="">
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                    <button type="submit" class="btn btn-primary btn-add-emp pull-right" disabled="">{{ trans('manage_time::view.Add') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<div class="modal fade in" id="modal_remove_emp">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" action="{{ route("manage_time::timekeeping.removeEmpFromTimekeeping") }}"  enctype="multipart/form-data" autocomplete="off">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <h4 class="box-title">{{ trans('manage_time::view.The following employees will be removed from the time sheet.') }}</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="timekeeping_table_id" value="{{ $timeKeepingTable->id }}" />
                    <ol class="emp-list" style="font-size: 14px; line-height: 25px;">
                        
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                    <button type="submit" class="btn btn-primary btn-remove-emp pull-right">{{ trans('manage_time::view.Remove') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>