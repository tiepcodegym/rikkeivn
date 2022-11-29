<div class="modal" id="modal-merge">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('sales::view.Merge confirm') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="list-group">
                        </ul>
                    </div>
                    <div class="col-md-12 align-center">
                        <span class="glyphicon glyphicon-arrow-down" style="font-size: 50px;"></span>
                    </div>
                    <div class="col-md-12">
                        <select class="form-control select-merge-in">
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('sales::view.Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="merge();">{{ trans('sales::view.Merge') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>