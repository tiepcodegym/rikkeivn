<div class="modal fade" id="add_task_modal" data-url="{{ route('resource::available.project.intime') }}">
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('resource::view.Add note task') }}</h4>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 180px); overflow: auto;">
                <form id="form_add_task" method="post" action="{{ route('project::task.general.save') }}"
                      class="form-horizontal form-submit-ajax has-valid" autocomplete="off"
                      data-callback-success="employeeTaskSuccess">
                    {!! csrf_field() !!}
                    <div class="form-content"></div>
                    <input type="hidden" name="employee_id" value="">
                    <input type="hidden" name="callback" value="\Rikkei\Resource\Model\EmplAvailTask::insertOrUpdate">
                </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

