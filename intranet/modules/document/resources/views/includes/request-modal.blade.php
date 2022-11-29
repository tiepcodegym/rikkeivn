<div class="modal fade" id="request_feedback_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="post" action="{{ route('doc::admin.request.feedback', $item->id) }}">
            {!! csrf_field() !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('doc::view.Feedback reason') }} <em class="required">*</em></h4>
                </div>
                <div class="modal-body">
                    <textarea class="form-control noresize" required
                              rows="5" name="feedback_reason">{{ old('feedback_reason') }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('doc::view.Close') }}</button>
                    <button type="submit" class="btn btn-danger" id="task_feedback_btn">Feedback</button>
                </div>
            </div><!-- /.modal-content -->
        </form>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

