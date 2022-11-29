<div class="modal fade" id="modal_export_fines_money">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 10px">
            {!! Form::open([
                'method' => 'post',
                'route' => 'fines-money::fines-money.manage.export',
                'id' => 'form_export_fines_money',
                'class' => 'no-validate'
            ]) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">{{ trans('fines_money::view.Export fines money') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-red hidden error-mess"></p>
                <h4>{{ trans('team::export.export_options') }}</h4>
                <ul class="list-inline">
                    <li>
                        <label>
                            <input type="radio" name="export_all" value="0">
                            {{ trans('team::export.export_only_selected') }}
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="export_all" value="1" checked>
                            {{ trans('team::export.export_all') }}
                        </label>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <input type="hidden" class="filter-grid" name="export" value="1">
                <input type="hidden" name="tab" value="{{ $currentTab}}" >
                <input type="hidden" name="itemsChecked" value="">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                <button type="submit" class="btn btn-success">
                    <span class="icon-processing hidden"><i class="fa fa-spin fa-refresh"></i>&nbsp;</span>
                    {{ trans('team::view.Export') }}
                </button>
            </div>
            {!! Form::close() !!}
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
