<div class="modal fade" id="modal-candidate-detail" tabindex="-1" role="dialog" data-keyboard="false">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content"  >
            <div class="modal-body bg-wrapper">
                    <div class="control-label channel-name text-center h3"></div>
                <section class="box box-primary">
                    <div class="box-body form-horizontal">
                        <table class="table table-striped dataTable table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="col-id width-5-per">{{ trans('sales::view.Numerical order') }}</th>
                                    <th>{{ trans('resource::view.Employee name') }}</th>
                                    <th>{{ trans('resource::view.Employee code') }}</th>
                                    <th>{{ trans('team::view.Join date') }} </th>
                                    <th>{{ trans('resource::view.Request.Detail.Cost') }}</th>
                                </tr>
                            </thead>
                            <tbody class="candidate-detail">
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
