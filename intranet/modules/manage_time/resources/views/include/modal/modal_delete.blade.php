<div class="modal fade in managetime-modal" id="modal_delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h3 class="box-title">{{ trans('manage_time::view.Confirm') }}</h3>
            </div>
            <div class="modal-body">
                <div class="form-group form-group-select2">
                    <p>{{ trans('manage_time::view.Do you really want to delete the register?') }}</p>
                    <input type="hidden" name="" id="register_id_delete" value="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                <button type="submit" class="btn btn-primary pull-right" id="button_delete_submit" data-loading-text="<i class='fa fa-spin fa-refresh'></i> {{ trans('manage_time::view.Yes') }}">{{ trans('manage_time::view.Yes') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>