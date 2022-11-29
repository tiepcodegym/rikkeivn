<?php
use Rikkei\Team\View\ExportMember;

$columnsExport = ExportMember::columnsExport();
?>

<div class="modal fade" id="modal_member_export">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open([
                'method' => 'post',
                'route' => 'team::team.member.export_member',
                'id' => 'form_export_member',
                'class' => 'no-validate'
            ]) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ trans('team::export.Export member') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-blue"><i>{{ trans('team::export.export_note') }}</i></p>
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
                <h4>
                    {{ trans('team::export.choose_column_export') }}&nbsp;&nbsp;
                    <a href="#" class="cols-sorting"></a>&nbsp;
                    <a href="#" class="cols-clear-sort"><i class="fa fa-close"></i></a>
                </h4>
                <label><input type="checkbox" class="check-all" id="col_check_all" data-list=".list_export_cols"> <strong>{{ trans('team::export.all') }}</strong></label>
                <ul class="list-unstyled checkbox-list list_export_cols" data-all="#col_check_all">
                    <?php
                    $order = 0;
                    ?>
                    @foreach ($columnsExport as $col => $colData)
                    <li class="{{ isset($colData['df']) ? 'no-sort' : 'sort' }}">
                        <input type="checkbox" class="check-item" name="columns[{{ $order }}]" value="{{ $col }}" id="col_{{ preg_replace('/\./', '_', $col) }}"
                               {{ isset($colData['df']) ? 'checked' : null }}
                               data-default="{{ isset($colData['df']) }}">
                        &nbsp;
                        <label for="col_{{ preg_replace('/\./', '_', $col) }}">{{ $colData['tt'] }}</label>
                    </li>
                    <?php
                    $order++;
                    ?>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <input type="hidden" name="urlFilter" value="{{ $urlFilter }}">
                <input type="hidden" name="statusWork" value="{{ $statusWork }}">
                <input type="hidden" name="teamIdCurrent" value="{{ $teamIdCurrent }}">
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
