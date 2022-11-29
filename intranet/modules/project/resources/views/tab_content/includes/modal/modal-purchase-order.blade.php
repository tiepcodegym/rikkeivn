<div class="modal" id="modal-purchase">
    <div class="modal-dialog modal-lg" style="width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('project::view.Purchase order') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="box box-info">
                                <div class="box-body scrolls">
                                    <div class="row">
                                        <div class="col-sm-2">&ensp;</div>
                                        <div class="col-sm-8 group-error hidden">
                                            <label class="error-input-mess labl-error error" ></label>
                                        </div>
                                        <div class="col-sm-2">&ensp;</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">&ensp;</div>
                                        <div class="col-md-8" style="height: 30vh;">
                                            <div class="spinner-border" role="status"></div>
                                            <table class="table dataTable table-bordered  table-grid-data">
                                                <thead>
                                                <tr>
                                                    <th style="min-width: 100px;" class="col-month required">{{ trans('project::view.Person') }}</th>
                                                    <th style="min-width: 50px" class="col-cost required">{{ trans('project::view.Category') }}</th>
                                                    <th style="min-width: 150px;" class="col-team required">{{ trans('project::view.Roles') }}</th>
                                                    <th style="min-width: 150px;" class="col-team">{{ trans('project::view.Level') }}</th>
                                                    <th style="min-width: 10px;">{{ trans('project::view.Order type') }}</th>
                                                    <th style="min-width: 10px;">{{ trans('project::view.Unit Price') }}</th>
                                                    <th style="min-width: 10px;">{{ trans('project::view.Quantity') }}</th>
                                                    <th style="min-width: 10px;">{{ trans('project::view.Unit') }}</th>
                                                    <th style="min-width: 10px;">{{ trans('project::view.Currency id') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody id="list_purchase">
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-sm-2">&ensp;</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>