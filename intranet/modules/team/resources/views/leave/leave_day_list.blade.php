@extends('layouts.default')

@section('title')
    {{trans('manage_time::view.List leave days')}}
@endsection

<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\ManageTime\View\View;
    use Carbon\Carbon;

    $tblFilter = View::getFilterLeaveDayTable();
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/day_list.css') }}" />
    <style>
        .filter-action .fa-refresh {
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-4 margin-bottom-5">
                           
                        </div>
                        <div class="col-sm-8 row-filters text-right">
                            @include('team::include.filter')
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table-day">
                        <thead>
                            <tr class="info">
                                <th class="col-id width-10" style="width: 20px;">{{ trans('manage_time::view.No.') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('employee_code') }} col-employee_code" data-order="employee_code" data-dir="{{ TeamConfig::getDirOrder('employee_code') }}">{{ trans('manage_time::view.Employee code') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('name') }} col-name" data-order="name" data-dir="{{ TeamConfig::getDirOrder('name') }}" style="min-width: 80px;">{{ trans('manage_time::view.Employee fullname') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_last_year') }} col-day_last_year" data-order="day_last_year" data-dir="{{ TeamConfig::getDirOrder('day_last_year') }}">{{ trans('manage_time::view.Number day last year') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_last_transfer') }} col-day_last_transfer" data-order="day_last_transfer" data-dir="{{ TeamConfig::getDirOrder('day_last_transfer') }}">{{ trans('manage_time::view.Number day last year use') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_current_year') }} col-day_current_year" data-order="day_current_year" data-dir="{{ TeamConfig::getDirOrder('day_current_year') }}">{{ trans('manage_time::view.Number day current year') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_seniority') }} col-day_seniority" data-order="day_seniority" data-dir="{{ TeamConfig::getDirOrder('day_seniority') }}">{{ trans('manage_time::view.Number day seniority') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_OT') }} col-day_OT" data-order="day_OT" data-dir="{{ TeamConfig::getDirOrder('day_OT') }}">{{ trans('manage_time::view.Number day OT') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('total_day') }} col-total_day" data-order="total_day" data-dir="{{ TeamConfig::getDirOrder('total_day') }}">{{ trans('manage_time::view.Total number day') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_used') }} col-day_used" data-order="day_used" data-dir="{{ TeamConfig::getDirOrder('day_used') }}">{{ trans('manage_time::view.Number day used') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('remain_day') }} col-remain_day" data-order="remain_day" data-dir="{{ TeamConfig::getDirOrder('remain_day') }}">{{ trans('manage_time::view.Number day remain') }}</th>
                            </tr>
                        </thead>
                        <tbody class="checkbox-body">
                            <tr class="filter-input-grid">
                                <td>&nbsp;</td>
                                <td>
                                    <input type="text" name="filter[employees.employee_code]" value="{{ CoreForm::getFilterData('employees.employee_code') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
                                <td>
                                    <input type="text" name="filter[employees.name]" value="{{ CoreForm::getFilterData('employees.name') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
                                <td>
                                    <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_last_year]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_last_year') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
                                <td>
                                    <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_last_transfer]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_last_transfer') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
                                <td>
                                    <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_current_year]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_current_year') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
                                <td>
                                    <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_seniority]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_seniority') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
                                <td>
                                    <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_ot]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_ot') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
                                <td>
                                    <input type="text" name="filter[spec][total_day]" value="{{ CoreForm::getFilterData('spec','total_day') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
                                <td>
                                    <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_used]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_used') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
                                <td>
                                    <input type="text" name="filter[spec][remain_day]" value="{{ CoreForm::getFilterData('spec','remain_day') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
                            </tr>
                            @if(isset($collectionModel) && count($collectionModel))
                                <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                                @foreach($collectionModel as $item)
                                    <tr day-id="{{$item->id}}" class="reason-data">
                                        <td>{{ $i }}</td>
                                        @if ($item->employee_code)
                                            <td class="employee_code">{{$item->employee_code}}</td>
                                        @else
                                            <td>&nbsp;</td>
                                        @endif
                                        @if ($item->name)
                                            <td class="full_name">{{$item->name}}</td>
                                        @else
                                            <td>&nbsp;</td>
                                        @endif
                                        <td class="day_last_year">{{$item->day_last_year}}</td>
                                        <td class="day_last_transfer">{{$item->day_last_transfer}}</td>
                                        <td class="day_current_year">{{$item->day_current_year}}</td>
                                        <td class="day_seniority">{{$item->day_seniority}}</td>
                                        <td class="day_OT">{{$item->day_ot}}</td>
                                        <td class="total_day">{{$item->total_day}}</td>
                                        <td class="day_used">{{$item->day_used}}</td>
                                        <td>{{$item->remain_day}}</td>
                                    </tr>
                                    <?php $i++; ?>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="{{ $isBaseline ? 13 : 12 }}" class="text-center">
                                        <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="box-body">
                    @include('team::include.pager')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/leave_day.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            selectSearchReload();
            RKfuncion.general.initDateTimePicker();
            $('.input-datepicker').on('dp.change', function () {
                window.location.href = urlIndex + '?month=' + $(this).val();
            });
        });

        $('.btn-reset-filter').click(function () {
            var location = window.location;
            window.history.pushState({}, document.title, location.origin + location.pathname);
        });
    </script>
@endsection

