<!-- Modal -->
<div id="edit-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{ trans('project::timesheet.edit_row_title') }}</h4>
            </div>
            <div class="modal-body">
                <p>{{ trans('project::timesheet.note_edit_row') }}</p>
                <input class="form-control" id="data-row" data-type="">
                <span class="error edit-row-error"></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-save-row" data-line-id="" data-row="">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>

    </div>
</div>