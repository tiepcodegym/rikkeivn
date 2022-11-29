<div class="modal fade in" id="test-history-modal" tabindex="-1" role="dialog"  data-keyboard="false" >
    <div class="modal-dialog">
        <div class="modal-content"  >
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title">{{ trans('resource::view.Test history') }}</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="edit-table table table-striped table-bordered table-condensed dataTable" cellspacing="0" id="test-history-table">
                        <thead>
                            <tr>
                                <th>{{ trans('resource::view.Type') }}</th>
                                <th>{{ trans('resource::view.Total correct') }}</th>
                                <th>{{ trans('resource::view.Total answer') }}</th>
                                <th>{{ trans('resource::view.Total question') }}</th>
                                <th>{{ trans('resource::view.Date') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>