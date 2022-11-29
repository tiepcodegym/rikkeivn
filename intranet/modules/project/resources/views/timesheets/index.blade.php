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
    <style>
        .thead{
            background-color: #d9edf7;
        }
        .header-title{
            margin: 0;
        }
    </style>
@endsection

@section('title')
    {{trans('project::timesheet.timesheet_list')}}
@endsection
@section('content')
    <div class="content-container">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box-header row">
                                    <div class="col-md-8 ">
                                    </div>
                                    <div class="filter-action col-md-4">
                                        <a href="{{ route('project::timesheets.create') }}" class="btn btn-primary">
                                            <i class="fa fa-plus"></i>
                                            {{ trans('project::timesheet.create_timesheet') }}
                                        </a>
                                        <button class="btn btn-primary btn-reset-filter">
                                            <span>{{ trans('team::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive" style="margin: 0">
                                    <table class="table table-hover table-striped dataTable table-bordered">
                                        <thead>
                                            <tr class="thead">
                                                <th>{{ trans('project::timesheet.no') }}</th>
                                                <th>{{ trans('project::timesheet.title') }}</th>
                                                <th>{{ trans('project::timesheet.project') }}</th>
                                                <th>{{ trans('project::timesheet.po') }}</th>
                                                <th class="sorting col-start_date {{ TeamConfig::getDirClass('start_date') }}" data-order="start_date" data-dir="{{ TeamConfig::getDirOrder('start_date') }}">{{ trans('project::timesheet.start_date') }}</th>
                                                <th class="sorting col-end_date {{ TeamConfig::getDirClass('end_date') }}" data-order="end_date" data-dir="{{ TeamConfig::getDirOrder('end_date') }}">{{ trans('project::timesheet.end_date') }}</th>
{{--                                                <th>{{ trans('project::timesheet.creator') }}</th>--}}
                                                <th class="sorting col-created_at {{ TeamConfig::getDirClass('created_at') }}" data-order="created_at" data-dir="{{ TeamConfig::getDirOrder('created_at') }}">{{ trans('project::timesheet.created_at') }}</th>
                                                <th>{{ trans('project::timesheet.status') }}</th>
                                                <th>{{ trans('project::timesheet.action') }}</th>
                                            </tr>
                                            <tr class="row-search">
                                                <td></td>
                                                <td>
                                                    {{ Form::text('filter[title]', FormFilter::getFilterData('title'), ['class' => 'form-control filter-grid', 'autocomplete' => 'off']) }}
                                                </td>
                                                <td>
                                                    {{ Form::select('filter[project_id]',
                                                   ['' => trans('project::timesheet.select')] + $projects,
                                                   FormFilter::getFilterData('project_id'),
                                                   ['class' => 'form-control filter-grid select-grid',
                                                   'autocomplete' => 'off',
                                                   'data-select2-dom' => '1',
                                                   'data-select2-search' => '1']) }}
                                                </td>
                                                <td>
                                                    {{ Form::text('filter[po_title]', FormFilter::getFilterData('po_title'), ['class' => 'form-control filter-grid', 'autocomplete' => 'off']) }}
                                                </td>
                                                <td>
                                                    {{ Form::text('filter[except][start_date]', FormFilter::getFilterData('except', 'start_date'), ['class' => 'form-control filter-grid filter-date', 'autocomplete' => 'off']) }}
                                                </td>
                                                <td>
                                                    {{ Form::text('filter[except][end_date]', FormFilter::getFilterData('except', 'end_date'), ['class' => 'form-control filter-grid filter-date', 'autocomplete' => 'off']) }}
                                                </td>
                                                <td>
                                                    {{ Form::text('filter[except][created_at]', FormFilter::getFilterData('except', 'created_at'), ['class' => 'form-control filter-grid filter-date', 'autocomplete' => 'off']) }}
                                                </td>
                                                <td>
                                                    {{ Form::select('filter[status]',
                                                    ['' => trans('project::timesheet.select')] + \Rikkei\Project\Model\Timesheet::getStatus(),
                                                    FormFilter::getFilterData('status'),
                                                    ['class' => 'form-control filter-grid select-grid', 'autocomplete' => 'off']) }}
                                                </td>
                                                <td>
                                                    <button class="btn btn-primary btn-search-filter">
                                                        <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                                    </button>
                                                </td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($collectionModel) && count($collectionModel))
                                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                                            @foreach($collectionModel as $item)
                                                <tr>
                                                    <td>{{ $i }}</td>
                                                    <td>
                                                        {{ $item->title }}
                                                    </td>
                                                    <td>
                                                        {{ $projects[$item->project_id] }}
                                                    </td>
                                                    <td>
                                                        {{ $item->po_title }}
                                                    </td>
                                                    <td>
                                                        {{ $item->start_date }}
                                                    </td>
                                                    <td>
                                                        {{ $item->end_date }}
                                                    </td>
                                                    {{--<td>--}}
                                                        {{--{{ $item->creator_id }}--}}
                                                    {{--</td>--}}
                                                    <td>
                                                        {{ $item->created_at }}
                                                    </td>
                                                    <td>
                                                        {{ $status[$item->status] }}
                                                    </td>
                                                    <td class="text-center">
                                                        <a class="btn btn-info" href="{{ route('project::timesheets.edit', ['id' => $item->id]) }}"><i class="fa fa-edit"></i></a>
                                                        <button class="btn btn-danger button-delete"
                                                                data-route="{{ route('project::timesheets.destroy', ['timesheet' => $item->id]) }}" title="Xóa"
                                                                data-toggle="modal"><i class="fa fa-trash-o"></i></button>
                                                    </td>
                                                 </tr>
                                                <?php $i++; ?>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="10" class="text-center">
                                                    <h2 class="no-result-grid">{{ trans('project::timesheet.no_results') }}</h2>
                                                </td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>

                                <div class="box-footer no-padding">
                                    <div class="mailbox-controls">
                                        @include('team::include.pager')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade in modal-danger" id="modal_delete">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h3 class="box-title">{{ trans('project::timesheet.confirm_delete_title') }}</h3>
                </div>
                <div class="modal-body">
                    <div class="form-group form-group-select2">
                        <p>{{ trans('project::timesheet.confirm_delete') }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">{{ trans('project::timesheet.close') }}</button>
                    {{ Form::open(['method' => 'POST', 'id' => 'delete-timesheet-form']) }}
                        <button type="submit" class="btn btn-primary pull-right"
                                data-loading-text="<i class='fa fa-spin fa-refresh'></i> {{ trans('project::timesheet.yes') }}">
                            {{ trans('project::timesheet.yes') }}</button>
                    {{ Form::close() }}
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
{{--    <script src="{{ CoreUrl::asset('project/js/timesheet.js') }}"></script>--}}
    <script>
        $(document).ready(function() {
            $('.button-delete').on('click', function () {
                var route = $(this).data('route');
                $('#delete-timesheet-form').attr('action', route);
                $('#modal_delete').modal();
            });

            $('.filter-date').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                weekStart: 1,
                todayHighlight: true
            });
            $('.btn-reset-filter').click(function () {
                var location = window.location;
                window.history.pushState({}, document.title, location.origin + location.pathname);
            });
            RKExternal.select2.init();
        })
    </script>
@endsection
