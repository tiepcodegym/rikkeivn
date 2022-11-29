<div class="modal fade" id="approve_confirm">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('ot::ot.approver.approve') }}" accept-charset="UTF-8" class="ng-pristine ng-valid">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h3 class="box-title">{{ trans('ot::view.Approve register') }}</h3>
                </div>
                <div class="modal-body">
                    <div id="assign_body">
                        <div class="form-group form-group-select2">
                            <p>{{ trans('ot::message.Do you really want to approve the register?') }}</p>
                            <input type="hidden" name="ot_approve_id" id="ot_approve_id" value="">
                            <input type="hidden" name="page_type" class="page_type" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('ot::view.Close') }}</button>
                    <button type="submit" class="btn btn-primary pull-right">{{ trans('ot::view.Yes') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
