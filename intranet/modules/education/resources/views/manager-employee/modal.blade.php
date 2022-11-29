<div class="modal" tabindex="-1" role="dialog" id="modal_member_export">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{!! trans('education::view.manager_employee.labels.Confirm export employee') !!}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -33px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{!! trans('education::view.manager_employee.labels.message export') !!}</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="{!! route('education::education.manager.employee.export') !!}" class="no-validate" id="form_export">
                    {{csrf_field()}}
                    <input type="hidden" value="{{$teamIdCurrent}}" name="team_id">
                    <button type="submit" class="btn btn-primary">{!! trans('education::view.manager_employee.buttons.export') !!}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{!! trans('education::view.manager_employee.buttons.close') !!}</button>
                </form>
            </div>
        </div>
    </div>
</div>
