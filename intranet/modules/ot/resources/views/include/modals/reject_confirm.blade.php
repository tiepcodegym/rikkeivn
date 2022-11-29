<?php
    use Rikkei\ManageTime\Model\ManageTimeComment;
    use Rikkei\ManageTime\View\ManageTimeConst;
    
    $otCommentList = ManageTimeComment::getReasonDisapprove(null, ManageTimeConst::TYPE_OT);
?>
<div class="modal fade" id="reject_confirm">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('ot::ot.approver.reject') }}" accept-charset="UTF-8" class="ng-pristine ng-valid">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h3 class="box-title">{{ trans('ot::view.Reject register') }}</h3>
                </div>
                <div class="modal-body">
                    <div id="assign_body">
                        <div class="form-group form-group-select2">
                            <input type="hidden" name="ot_reject_id" id="ot_reject_id" value="">
                            <input type="hidden" name="page_type" class="page_type" value="">
                            <p>{{ trans('ot::view.Reject reason') }} *</p>
                            <textarea class="form-control" id="reject_reason"  name="reject_reason" rows="5"></textarea>
                            <div class="rejectError error"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('ot::view.Close') }}</button>
                    <button type="button" class="btn btn-primary pull-right submit">{{ trans('ot::view.Yes') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
