@extends('layouts.default')

@section('title', trans('manage_time::view.Report late minute and fine'))

<?php 
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\Form;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\ManageTime\View\ManageTimeConst;
    use Rikkei\Team\View\Config;
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}" />
    <style>
        .tooltip-inner {
            max-width: inherit !important;
            text-align: left!important;
            padding-top: 10px;
        }
        .select2-container .select2-selection--single .select2-selection__rendered {
            padding-left: 0;
        }
        .display_flex {
            display: flex;
        }
        .w-50 {
            width: 50px;
        }
    </style>
@endsection

@section('content')
    <div class="box box-primary">
        <div class="box-header">
            <div class="row">
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row form-group">
                                <label class="col-sm-2 col-md-2 margin-top-5  text-right">
                                    {{trans('project::view.Choose project:')}}
                                    <span class="fa fa-question-circle tooltip-leave" 
                                        data-toggle="tooltip" title=""
                                        data-placement="bottom"
                                        data-html="true"
                                        data-original-title="<ul>
                                        <li>Quyền Cty: thấy tất cả dự án</li>
                                        <li>Quyền team: thấy được các dự án của team (các dự án mà Group Leader là người trong team)</li>
                                        <li>Quyền cá nhân: thấy các dự án hiện tại mình là PM</li>
                                    </ul>"></span>
                                </label>
                                <div class="col-sm-10 col-md-4">
                                    <select class="form-control select-tooltip select2-base select-search select-grid filter-grid"
                                        name="filter[except][project_id]">
                                        <option>&nbsp</option>
                                    @if(isset($projects) && count($projects))
                                        @foreach($projects as $item)
                                            <option
                                                value="{{$item->id}}"
                                                @if(isset($filter['except']['project_id']) && $filter['except']['project_id'] == $item->id)
                                                    selected
                                                @endif
                                            >
                                                {{$item->name}}
                                            </option>
                                        @endforeach 
                                    @endif
                                    </select>
                                </div>
                                <label class="col-sm-2 col-md-2 margin-top-5 text-right">{{ trans('project::me.StartMonth') }} </label>
                                <div class="col-sm-10 col-md-4">
                                    <div class="input-group date date-picker">
                                        <input type="text" name="filter[except][start_month]" autocomplete="off"
                                            value='{{ $startMonth }}'
                                            class="form-control filter-grid form-inline">
                                        <div class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row form-group select_team">
                                @if (isset($teamsOption) && count($teamsOption))
                                    <label class="col-sm-2 col-md-2 margin-top-5 text-right">
                                        {{ trans('manage_time::view.Select team') }}
                                        <span class="fa fa-question-circle tooltip-leave" 
                                            data-toggle="tooltip" title=""
                                            data-html="true"
                                            data-original-title="<ul>
                                            <li>Quyền Cty: thấy tất cả team</li>
                                            <li>Quyền team: thấy được những team được phần quyền</li>
                                        </ul>"></span>
                                    </label>
                                    <div class="col-sm-10 col-md-4">
                                        <select name="filter[except][team_id]"
                                            class="form-control select-tooltip select-search select2-base select-grid filter-grid ct-text-select">
                                            <option>&nbsp</option>
                                            @foreach($teamsOption as $option)
                                                <option
                                                    value="{{ $option['value'] }}"
                                                    @if(isset($filter['except']['team_id']) && $filter['except']['team_id'] == $option['value'])
                                                        selected
                                                    @endif
                                                >
                                                    {{ $option['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div class="col-sm-12 col-md-6"></div>
                                @endif
                                <label class="col-sm-2 col-md-2 margin-top-5 text-right">{{ trans('project::me.EndMonth') }}</label>
                                <div class="col-sm-10 col-md-4">
                                    <div class="input-group date date-picker">
                                        <input type="text" name="filter[except][end_month]" autocomplete="off"
                                            value='{{ $endMonth }}'
                                            class="form-control filter-grid form-inline">
                                        <div class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-right member-group-btn">
                    <a href="{{route('manage_time::division.export-late-minute')}}" class="btn btn-success btn-export">
                        <span>Export <i class="fa fa-spin fa-refresh hidden"></i></span>
                    </a>
                    @include('team::include.filter')
                </div>
            </div>
        </div>

        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table-control" id="managetime_table_primary" style="margin-top: 50px !important;">
                    <thead class="managetime-thead">
                        <tr>
                            <th><input type="checkbox" class="check-all" id="tbl_check_all" data-list=".table-check-list"></th>
                            <th class="managetime-col-25 col-no" style="min-width: 25px;">{{ trans('manage_time::view.No.') }}</th>
                            <th
                                class="sorting {{ Config::getDirClass('employees.employee_code') }} col-title col-employee_code"
                                data-order="employees.employee_code" data-dir="{{ Config::getDirOrder('employees.employee_code') }}"
                            >
                                {{ trans('manage_time::view.Employee code') }}
                            </th>
                            <th
                                class="sorting {{ Config::getDirClass('employees.name') }} col-title col-name"
                                data-order="employees.name" data-dir="{{ Config::getDirOrder('employees.name') }}"
                            >
                                {{ trans('manage_time::view.Employee name') }}
                            </th>
                            <th>{{ trans('education::view.Division') }}</th>
                            <th class="sorting {{ Config::getDirClass('tbl_lm.time_over') }} col-title col-sum_late_minute"
                                data-order="tbl_lm.time_over" data-dir="{{ Config::getDirOrder('tbl_lm.time_over') }}"
                            >
                                {{ trans('manage_time::view.Sum time over/lack (minute)') }} <span class="fa fa-question-circle tooltip-leave" 
                                            data-toggle="tooltip" title=""
                                            data-html="true"
                                            data-original-title="<ul>
                                            <li>> 0: Làm thừa</li>
                                            <li>< 0: Làm thiếu</li>
                                        </ul>"></span>
                            </th>
                            <th
                                class="sorting {{ Config::getDirClass('tbl_lm.count_late_minute') }} col-title col-count_late_minute"
                                data-order="tbl_lm.count_late_minute" data-dir="{{ Config::getDirOrder('tbl_lm.count_late_minute') }}"
                            >
                                {{ trans('manage_time::view.Total number of late trips') }}
                            </th>
                            <th class="sorting {{ Config::getDirClass('tbl_lm.sum_late_minute') }} col-title col-sum_late_minute"
                                data-order="tbl_lm.sum_late_minute" data-dir="{{ Config::getDirOrder('tbl_lm.sum_late_minute') }}"
                            >
                                {{ trans('manage_time::view.Total late minutes') }}
                            </th>
                            <th
                                class="sorting {{ Config::getDirClass('tbl_lm.total_fine_money') }} col-title col-total_fine_money"
                                data-order="tbl_lm.total_fine_money" data-dir="{{ Config::getDirOrder('tbl_lm.total_fine_money') }}"
                            >
                                {{ trans('manage_time::view.Total fines late in') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <input type="text" name="filter[employees.employee_code]" value='{{ Form::getFilterData("employees.employee_code") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                            </td>
                            <td>
                                <input type="text" name="filter[employees.name]" value='{{ Form::getFilterData("employees.name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                            </td>
                            <td></td>
                            <td>
                                <div class="display_flex">
                                    <select class="compare form-control filter-grid w-50"
                                        name="filter[compare][tbl_lm.time_over]"
                                    >
                                        @foreach ($optionsCompare as $value)
                                        <option value="{{ $value }}" {{ Form::getFilterData('compare', "tbl_lm.time_over") == $value ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="filter[except2][tbl_lm.time_over]" value='{{ Form::getFilterData("except2", "tbl_lm.time_over") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </td>
                            <td>
                                <div class="display_flex">
                                    <select class="compare form-control filter-grid w-50"
                                        name="filter[compare][tbl_lm.count_late_minute_compare]"
                                    >
                                        @foreach ($optionsCompare as $value)
                                        <option value="{{ $value }}" {{ Form::getFilterData('compare', "tbl_lm.count_late_minute_compare") == $value ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="filter[except2][tbl_lm.count_late_minute]" value='{{ Form::getFilterData("except2", "tbl_lm.count_late_minute") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </td>
                            <td>
                                <div class="display_flex">
                                    <select class="compare form-control filter-grid w-50"
                                        name="filter[compare][tbl_lm.sum_late_minute_compare]"
                                    >
                                        @foreach ($optionsCompare as $value)
                                        <option value="{{ $value }}" {{ Form::getFilterData('compare', "tbl_lm.sum_late_minute_compare") == $value ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="filter[except2][tbl_lm.sum_late_minute]" value='{{ Form::getFilterData("except2", "tbl_lm.sum_late_minute") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </td>
                            <td>
                                <div class="display_flex">
                                    <select class="compare form-control filter-grid w-50"
                                    name="filter[compare][tbl_lm.total_fine_money_compare]"
                                >
                                    @foreach ($optionsCompare as $value)
                                    <option value="{{ $value }}" {{ Form::getFilterData('compare', "tbl_lm.total_fine_money_compare") == $value ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                    <input type="text" name="filter[except2][tbl_lm.total_fine_money]" value='{{ Form::getFilterData("except2", "tbl_lm.total_fine_money") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php
                                $i = CoreView::getNoStartGrid($collectionModel);
                            ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td><input type="checkbox" class="check-item" value="{{$item->employee_id}}"></td>
                                    <td>{{ $i }}</td>
                                    <td>{{ $item->employee_code }}</td>
                                    <td><a href="{{ route('manage_time::division.timekeeping', ['idTable' => $item->timekeeping_table_id, 'idEmp' => $item->employee_id]) }}">{{ $item->employee_name }}</a></td>
                                    <td>{{ $item->team_name }}</td>
                                    <td>{{ $item->time_over }}</td>
                                    <td>{{ $item->count_late_minute }}</td>
                                    <td>{{ $item->sum_late_minute }}</td>
                                    <td>{{ number_format($item->total_fine_money) }}</td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
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
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.shorten.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
    <script src="{{ CoreUrl::asset('common/js/check_item.js') }}"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/xlsx.js') }}"></script>
    <script>
        $(document).ready(function() {
            RKfuncion.select2.init();
            $('.date-picker').each(function () {
                $(this).datepicker({
                    minViewMode: 1,
                    format: 'mm-yyyy',
                    autoclose: true,
                    todayHighlight: true
                }).change(function () {
                    $('.btn-search-filter').trigger('click');
                });
            });
        });
        $(document).ready(function() {
            $('.select2-base').select2();
        });
    </script>
    <script>
        var sessionKeys = 'report_minute_late',
            url_export ="{{ route('manage_time::division.export-late-minute') }}",
            _token = "{{ csrf_token() }}";
            setCheckItem(sessionKeys);
    </script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/late_minute_report.js') }}"></script>
@endsection