<?php
use Carbon\Carbon;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as FormFilter;
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\View as CoreView;
?>

@extends('layouts.default')
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    @include('project::timesheets.style')
@endsection
@section('title')
    {{trans('project::timesheet.create_title')}}
@endsection
@section('content')
    <div class="content-container page-timesheet" data-page="create">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-body">
                        {{ Form::open(['route' => 'project::timesheets.store', 'id' => 'timesheet-form', 'method' => 'POST']) }}
                        <!-- Filter box -->
                        <div class="row">
                            <div class="col-md-12 filter form-inline">
                                <div class="filter-box">
                                    <div class="form-group mr20">
                                        <label>{{ trans('project::timesheet.project') }}</label>
                                        {{ Form::select('project_id', ['' => trans('project::timesheet.select')] + $projects, null,
                                         ['class' => 'form-control', 'id' => 'project-select', 'data-select2-dom' => '1','data-select2-search' => '1', 'style' => 'min-width: 170px']) }}
                                    </div>
                                    <div class="form-group mr20 po-group" style="display: none">
                                        <label>{{ trans('project::timesheet.po') }}</label>
                                        {{ Form::select('po_id', ['' => trans('project::timesheet.select')], null, ['class' => 'form-control', 'id' => 'po-select']) }}
                                    </div>
                                    <div class="form-group mr20 period-group" style="display: none">
                                        <label>{{ trans('project::timesheet.period') }}</label>
                                        {{ Form::select('period', ['' => trans('project::timesheet.select')], null, ['class' => 'form-control', 'id' => 'period-select']) }}
                                        {{ Form::hidden('start_date', null, ['id' => 'start_date']) }}
                                        {{ Form::hidden('end_date', null, ['id' => 'end_date']) }}
                                        {{ Form::hidden('po_title', null, ['id' => 'po_title']) }}
                                        {{ Form::hidden('project_name', null, ['id' => 'project_name']) }}

                                        {{ Form::hidden('checkin_standard', null, ['id' => 'checkin_standard']) }}
                                        {{ Form::hidden('checkout_standard', null, ['id' => 'checkout_standard']) }}
                                        {{ Form::hidden('ot_normal_start', null, ['id' => 'ot_normal_start']) }}
                                        {{ Form::hidden('ot_day_off_start', null, ['id' => 'ot_day_off_start']) }}
                                        {{ Form::hidden('ot_day_off_end', null, ['id' => 'ot_day_off_end']) }}
                                        {{ Form::hidden('ot_holiday_start', null, ['id' => 'ot_holiday_start']) }}
                                        {{ Form::hidden('ot_holiday_end', null, ['id' => 'ot_holiday_end']) }}
                                        {{ Form::hidden('ot_overnight_start', null, ['id' => 'ot_overnight_start']) }}
                                        {{ Form::hidden('ot_overnight_end', null, ['id' => 'ot_overnight_end']) }}
                                    </div>
                                    <div class="form-group mr20 po-group-no-item" style="display: none">
                                        <label style="color: red">{{trans('project::timesheet.po_no_item')}}</label>
                                    </div>
                                    <img class="img-loading" style="display: none" src="{{ asset('common/images/loading.gif') }}" alt="">
                                </div>
                                @include('project::timesheets.table-rate-ot')
                            </div>
                            <div id="note" class="col-md-12">
                            </div>
                        </div>
                        <!-- /Filter box -->

                        <div id="timesheet-body">
                            <img class="data-loading" style="display: none" src="{{ asset('common/images/loading.gif') }}" alt="">
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="message-error" style="display: none"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div>
                                    <div class="form-group form-inline btn-submit">
                                        <label>{{ trans('project::timesheet.status') }}</label>
                                        {{ Form::select('status', $status, null,
                                         ['class' => 'form-control', 'id' => 'status']) }}
                                        {{Form::button('Save', ['class' => 'btn btn-primary', 'id' => 'btn-submit'])}}
                                    </div>
                                    {{Form::button('Cancel', ['class' => 'btn btn-default', 'onclick' => 'goBack()'])}}
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div>
    </div>
    @include('project::timesheets.note-modal')
    @include('project::timesheets.sync-modal')
    @include('project::timesheets.edit-row-modal')
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script src="{{ CoreUrl::asset('project/js/timesheet.js') }}"></script>

    <script>
        var has_data = '{{ old('has_data') }}';
        const GET_PO_URL = '{{ route('project::timesheets.get-po') }}';
        const GET_LINE_ITEM_URL = '{{ route('project::timesheets.get-line-item') }}';
        const IMG_LOADING = '{{ asset('common/images/loading.gif') }}';
        const URL_SYNC_TIMESHEET = '{{ route('project::timesheets.sync-timesheet') }}';
        const REFRESH_LINE_ITEM_URL = '{{ route('project::timesheets.reload-period') }}';
        var TIMESHEET_ID = null;
        var note = "{!! trans('project::timesheet.note_1') !!}";

        function goBack() {
            window.history.back();
        }
        $(document).ready(function() {
            if(has_data == '1') {
                var flag = false;
                $('#project-select').trigger('change');
                if(!flag) {
                    $(document).ajaxComplete(function () {
                        $('#po-select').val('{{ old('po_id') }}');
                        $('#po-select').trigger('change');
                        $('#period-select').val('{!! old('period') !!}');
                        setTimeout(function () {
                            if (!flag) {
                                $('#period-select').val('{!! old('period') !!}');
                                $('#period-select').trigger('change');
                            }
                            flag = true;
                        }, 200);
                    });
                }
            }
        })
    </script>
@endsection
