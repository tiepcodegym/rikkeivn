<div class="modal fade" id="rw_modal_feedback" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="post" action="{{ route('project::reward.feedback', ['id' => $project->id, 'taskId' => $taskItem->id]) }}">
            {!! csrf_field() !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('project::view.Feedback reason') }} (*)</h4>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" style="resize: vertical;" required
                              rows="5" name="feedback_reason">{{ old('feedback_reason') }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('project::view.Close') }}</button>
                    <button type="submit" class="btn btn-danger" id="task_feedback_btn">{{ trans('project::view.Feedback') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </form>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
