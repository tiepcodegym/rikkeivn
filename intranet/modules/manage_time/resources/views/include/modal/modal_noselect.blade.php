<div class="modal fade in managetime-modal" id="modal_noselect">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h3 class="box-title">{{ trans('manage_time::view.Notification') }}</h3>
            </div>
            <div class="modal-body">
                <div class="form-group form-group-select2">
                    <p>{{ trans('manage_time::view.You have not selected any objects yet') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>