<div class="modal fade" id="modal_projects_detail" data-url="{{ route('resource::available.project.intime') }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header label-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('resource::view.Project (in time filter)') }}: 
                    {{ isset($dataSearch['from_date']) ? $dataSearch['from_date'] : 'Any' }} 
                    <i class="fa fa-long-arrow-right"></i> 
                    {{ isset($dataSearch['to_date']) ? $dataSearch['to_date'] : 'Any' }}
                </h4>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 180px); overflow: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ trans('core::view.NO.') }}</th>
                            <th>{{ trans('resource::view.Project name') }}</th>
                            <th style="min-width: 90px;">{{ trans('resource::view.Start at') }}</th>
                            <th style="min-width: 90px;">{{ trans('resource::view.End at') }}</th>
                            <th>{{ trans('resource::view.Effort') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="tr-loading hidden">
                            <td colspan="5"><div class="text-center"><i class="fa fa-spin fa-refresh"></i></div></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="tr-not-found hidden">
                            <td colspan="5"><h4 class="text-center">{{ trans('resource::message.Not found item') }}</h4></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('resource::view.Close') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

