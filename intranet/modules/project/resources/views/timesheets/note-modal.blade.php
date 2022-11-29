<!-- Modal -->
<div id="note-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{ trans('project::timesheet.note_modal_title') }}</h4>
            </div>
            <div class="modal-body">
                <textarea name="note" id="txt-note" rows="5" class="form-control"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" data-id="" data-date="" class="btn btn-primary btn-save-note" data-dismiss="modal">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>

    </div>
</div>