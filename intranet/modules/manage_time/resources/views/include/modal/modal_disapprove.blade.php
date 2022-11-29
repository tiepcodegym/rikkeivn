<!-- Disapprove modal -->
<div class="modal fade in managetime-modal" id="modal_disapprove">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h3 class="box-title">{{ trans('manage_time::view.Refusal to approve registration') }}</h3>
            </div>
            <div class="modal-body">
                <div class="form-group form-group-select2">
                    <div class="form-group">
                        <label class="control-label managetime-label">{{ trans('manage_time::view.Disapprove reason') }} <em>*</em></label>
                        <div class="input-box">
                            <textarea id="reason_disapprove" name="reason_disapprove" class="form-control required managetime-textarea" placeholder="{{ trans('manage_time::view.Please enter reason for not approving') }}" ></textarea>
                        </div>
                        <label id="reason_disapprove-error" class="error" for="reason_disapprove" style="display: none;">{{ trans('manage_time::view.This field is required') }}</label>
                    </div>
                    <input type="hidden" name="" id="register_id_disapprove" value="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                <button type="submit" class="btn btn-primary pull-right" id="button_disapprove_submit" data-loading-text="<i class='fa fa-spin fa-refresh'></i> {{ trans('manage_time::view.Yes') }}">{{ trans('manage_time::view.Yes') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!-- Disapprove modal -->