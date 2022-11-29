<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as CoreForm;
use Carbon\Carbon;

$filterEmpId = CoreForm::getFilterData('excerpt', 'employee_id');
$showMonth = $month || ($filterEmpId && !$month);
?>

@extends('layouts.default')

@section('title', trans('manage_time::view.View working time in/out'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/working-time.css') }}">
@stop

@section('content')

<div class="box box-info">
 
    <div class="box-body">
        <div class="row">
            <div class="col-md-8">
                <form class="form-inline no-validate margin-right-10" method="get" action="{{ request()->url() }}" id="form_month_filter">
                    <div class="form-group">
                        <strong>{{ trans('manage_time::view.Month') }}: </strong>&nbsp;&nbsp;&nbsp;
                        <input type="text" id="log_month" name="month" class="form-control form-inline month-filter" style="max-width: 120px;"
                               value="{{ $month }}" autocomplete="off" placeholder="m-Y">
                    </div>
                </form>
                <div class="form-group form-inline margin-right-10">
                    <strong>{{ trans('manage_time::view.Employee') }}: </strong>&nbsp;&nbsp;&nbsp;
                    <select name="filter[excerpt][employee_id]" class="form-control filter-grid select-grid select2-search" style="width: 180px;"
                            data-url="{{ route('team::employee.list.search.ajax') }}">
                        <?php
                        $filterEmp = $filterEmpId ? CoreView::getOldEmployees($filterEmpId) : null;
                        ?>
                        @if ($filterEmp)
                        <option value="{{ $filterEmpId }}" selected>{{ $filterEmp->getNickName() }}</option>
                        @endif
                    </select>
                </div>
                <div class="form-group form-inline">
                    <strong>{{ trans('manage_time::view.Team') }}: </strong>&nbsp;&nbsp;&nbsp;
                    <select name="filter[excerpt][team_id]" class="form-control filter-grid select-grid select-search" style="width: 180px;">
                        <?php
                        $filterTeamId = CoreForm::getFilterData('excerpt', 'team_id');
                        ?>
                        <option value="">&nbsp;</option>
                        @if ($teamList)
                            @foreach ($teamList as $team)
                            <option value="{{ $team['value'] }}" {{ $team['value'] == $filterTeamId ? 'selected' : '' }}>{{ $team['label'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-md-4 text-right">
                @include('team::include.filter')
            </div>
        </div>
    </div>

    <div class="table-responsive">

        <table class="table table-hover table-striped dataTable table-bordered working-time-tbl">
            <thead>
                <tr>
                    <th>No.</th>
                    @if ($showMonth)
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('from_month') }} col-name" data-order="from_month" data-dir="{{ Config::getDirOrder('from_month') }}">{{ trans('manage_time::view.Month') }}</th>
                    @endif
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('employee_code') }} col-name" data-order="employee_code" data-dir="{{ Config::getDirOrder('employee_code') }}">{{ trans('manage_time::view.Employee code') }}</th>
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('manage_time::view.Employee name') }}</th>
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('email') }} col-name" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('manage_time::view.Email') }}</th>
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('team_names') }} col-name" data-order="team_names" data-dir="{{ Config::getDirOrder('team_names') }}">{{ trans('manage_time::view.Team') }}</th>
                    @if ($showMonth)
                    <th>{{ trans('manage_time::view.Working time register') }}</th>
                    <th></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @if (!$collectionModel->isEmpty())
                    @foreach ($collectionModel as $order => $item)
                    <?php
                    $currentPage = $collectionModel->currentPage();
                    $perPage = $collectionModel->perPage();
                    ?>
                    <tr>
                        <td>{{ $order + 1 + ($currentPage - 1) * $perPage }}</td>
                        @if ($showMonth)
                        <td>
                            <?php
                            $fromMonth = Carbon::parse($item->from_month)->format('m-Y');
                            $toMonth = Carbon::parse($item->to_month)->format('m-Y');
                            $month = $fromMonth;
                            if ($fromMonth != $toMonth) {
                                $month = $fromMonth . ' <i class="fa fa-long-arrow-right"></i> ' . $toMonth;
                            }
                            ?>
                            {!! $month !!}
                        </td>
                        @endif
                        <td>{{ $item->employee_code }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->team_names }}</td>
                        @if ($showMonth)
                        <td>
                            @if ($item->start_time1)
                            <div>{{ trans('manage_time::view.Morning shift') . ': ' . $item->start_time1 . ' - ' . $item->end_time1 }}</div>
                            <div>{{ trans('manage_time::view.Afternoon shift') . ': ' . $item->start_time2 . ' - ' . $item->end_time2 }}</div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('manage_time::wktime.log_time', ['employee_id' => $item->id]) }}" target="_blank"
                               title="{{ trans('manage_time::view.Detail') }}" class="btn btn-success">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="{{ $showMonth ? 8 : 5 }}"><h4 class="text-center">{{ trans('manage_time::view.No results found') }}</h4></td>
                </tr>
                @endif
            </tbody>
        </table>

    </div>

    <div class="box-body">
        @include('team::include.pager')
    </div>

</div>

@stop

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script>
    selectSearchReload();
    $('.select2-search').each(function () {
        initFilterSelect2($(this));
    });
    
    $('#btn_submit_form').click(function () {
        $('#working_time_log_form').submit();
    });

    $('.month-filter').datetimepicker({
        format: 'MM-YYYY',
        useCurrent: false,
        showClear: true
    });

    $('#log_month').on('dp.change', function () {
        var form = $(this).closest('form');
        form.submit();
    });

    var currUrl = '{{ route("manage_time::wktime.manage.list.logs") }}';
    $('.btn-reset-filter').click(function () {
        window.history.pushState(null, null, currUrl);
    });

    function initFilterSelect2(element) {
        var select2Option = {allowClear: true, placeholder: 'Search',};
        if (typeof element.attr('data-url') != 'undefined' && element.attr('data-url')) {
            select2Option.minimumInputLength = 2;
            select2Option.ajax = {
                url: element.attr('data-url'),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 20) < data.total_count,
                        },
                    };
                },
                cache: true,
            };
        }
        element.select2(select2Option);
    }
</script>
<!--<script src="{{ CoreUrl::asset('asset_managetime/js/working-time.js') }}"></script>-->
@stop
