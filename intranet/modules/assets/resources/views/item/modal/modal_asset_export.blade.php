<?php
use Rikkei\Assets\View\ExportAsset;

$columnsExport = ExportAsset::columnsExport();
?>

<div class="modal fade" id="modal_asset_export">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open([
                'method' => 'post',
                'route' => 'asset::asset.export_asset',
                'id' => 'form_asset_export',
                'class' => 'no-validate'
            ]) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ trans('asset::export.Export assets') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-blue"><i>{{ trans('asset::export.export_note') }}</i></p>
                <p class="text-red hidden error-mess"></p>
                <h4>{{ trans('asset::export.export_options') }}</h4>
                <ul class="list-inline">
                    <li>
                        <label>
                            <input type="radio" name="export_all" value="0" checked> 
                            {{ trans('asset::export.export_only_selected') }}
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="export_all" value="1"> 
                            {{ trans('asset::export.export_all') }}
                        </label>
                    </li>
                </ul>
                <h4>
                    <input type="checkbox" class="check-all-modal" id="col_check_all" data-list=".list_export_cols"> 
                    <label for="col_check_all">{{ trans('asset::export.all columns_export') }}</label>
                </h4>
                <ul class="list-unstyled checkbox-list list_export_cols" data-all="#col_check_all">
                    <?php
                    $order = 0;
                    ?>
                    @foreach ($columnsExport as $col => $colData)
                    <li>
                        <input type="checkbox" class="check-item" name="columns[{{ $order }}]" value="{{ $col }}" id="col_{{ $col }}"
                               {{ isset($colData['df']) ? 'checked' : null }}
                               data-default="{{ isset($colData['df']) }}">
                        &nbsp;
                        <label for="col_{{ $col }}">{{ $colData['tt'] }}</label>
                    </li>
                    <?php
                    $order++;
                    ?>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
            
                <input type="hidden" name="itemsChecked" value="">
                <button type="submit" class="btn btn-success">
                    <span class="icon-processing hidden"><i class="fa fa-spin fa-refresh"></i>&nbsp;</span>
                    {{ trans('team::view.Export') }}
                </button>
            </div>
            {!! Form::close() !!}
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
