<!-- Modal -->
<div id="sync-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{ trans('project::timesheet.sync_timesheet') }}</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ trans('project::timesheet.employee')}}</label>
                    {{ Form::select('employee_id', ['' => '--- Select ---'], null, ['class' => 'form-control search-employee',
                     'data-remote-url' => route('manage_time::profile.comelate.ajax-search-employee'), 'style' =>'width:100%']) }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="sync-timesheet" data-line-id="" data-start-date="" data-end-date="" data-dismiss="modal">{{ trans('project::timesheet.sync') }}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('project::timesheet.cancel') }}</button>
            </div>
        </div>
    </div>
</div>