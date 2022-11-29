<?php
use Rikkei\ManageTime\View\ManageTimeConst as MTConst;
?>

<div class="modal fade" id="wk_time_modal_reject" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="post" action="{{ route('manage_time::wktime.approve_register') }}" class="no-validate">
            {!! csrf_field() !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('manage_time::view.Disapprove reason') }} <em class="required">*</em></h4>
                </div>
                <div class="modal-body">
                    <textarea class="form-control noresize" required
                              rows="5" name="reject_reason">{{ old('reject_reason') }}</textarea>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="ids" value="{{ isset($workingTime) ? $workingTime->id : null }}">
                    <input type="hidden" name="status" value="{{ MTConst::STT_WK_TIME_REJECT }}">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                    <button type="submit" class="btn btn-danger" id="task_feedback_btn">{{ trans('manage_time::view.Not approve') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </form>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->