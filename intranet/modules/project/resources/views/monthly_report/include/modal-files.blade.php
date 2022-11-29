<?php
use Carbon\Carbon;
?>

<div class="modal fade" id="modal_import_billable">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ trans('project::view.Import billable') }}</h4>
            </div>
            {!! Form::open([
                'method' => 'post',
                'route' => 'project::monthly.report.import_billable',
                'files' => true,
                'id' => 'form_import_billable'
            ]) !!}
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ trans('project::view.Choose team') }} <em class="required">*</em></label>
                    <select class="form-control select-search" name="team_id">
                        <option value="">&nbsp;</option>
                        @if (!$teamsByPermission->isEmpty())
                            @foreach ($teamsByPermission as $team)
                            <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="form-group">
                    <label>{{ trans('project::view.Select month to import, empty to import all') }}</label>
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" class="form-control date-picker" name="from_month" id="im_from_month"
                                   value="{{ old('from_month') }}" placeholder="{{ trans('project::view.From month') }}">
                        </div>
                        <div class="col-sm-6">
                            <input type="text" class="form-control date-picker" name="to_month" id="im_to_month"
                                   value="{{ old('to_month') }}" placeholder="{{ trans('project::view.To month') }}">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>{{ trans('project::view.Import excel file') }} <em class="required">*</em></label>
                    <input type="file" name="excel_file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                </div>
                
                <div class="form-group loading-block text-blue hidden">
                    <i class="fa fa-spin fa-refresh"></i> <i>{{ trans('project::message.Time processing file may take several minutes, please wait!') }}</i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="btn_import_billable">{{ trans('project::view.Import') }}</button>
            </div>
            {!! Form::close() !!}
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal_export_billable">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ trans('project::view.Export billable') }}</h4>
            </div>
            {!! Form::open([
                'method' => 'post',
                'route' => 'project::monthly.report.export_billable',
                'id' => 'form_export_billable'
            ]) !!}
            <div class="modal-body">
                
                <div class="form-group">
                    <label>{{ trans('project::view.Choose team') }} <em class="required">*</em></label>
                    <select class="form-control select-search" name="team_id">
                        <option value="">&nbsp;</option>
                        @if (!$teamsByPermission->isEmpty())
                            @foreach ($teamsByPermission as $team)
                            <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="form-group">
                    <label>{{ trans('project::view.Select Month to export excel file') }} <em class="required">*</em></label>
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" class="form-control date-picker" name="from_month" id="ex_from_month"
                                   value="{{ Carbon::now()->startOfYear()->format('m-Y') }}" placeholder="{{ trans('project::view.From month') }}">
                        </div>
                        <div class="col-sm-6">
                            <input type="text" class="form-control date-picker" name="to_month" id="ex_to_month"
                                   value="{{ Carbon::now()->endOfYear()->format('m-Y') }}" placeholder="{{ trans('project::view.To month') }}">
                        </div>
                    </div>
                </div>
                
                <div class="form-group loading-block text-blue hidden">
                    <i class="fa fa-spin fa-refresh"></i> <i>{{ trans('project::message.Time processing file may take several minutes, please wait!') }}</i>
                </div>
                <div class="form-group error-block text-red hidden"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="btn_export_billable">{{ trans('project::view.Export') }}</button>
            </div>
            {!! Form::close() !!}
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>