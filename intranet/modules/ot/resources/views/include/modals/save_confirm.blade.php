<div class="modal fade modal-success" id="save_confirm">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h3 class="box-title">{{ trans('ot::view.Save confirm') }}</h3>
            </div>
            <div class="modal-body">
                <div id="assign_body">
                    <div class="form-group form-group-select2">
                        <p>{{ trans('ot::message.Do you really want to save the register?') }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">{{ trans('ot::view.Close') }}</button>
                <button type="button" class="btn btn-outline pull-right btn-confirm">{{ trans('ot::view.Yes') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
