<?php
use Rikkei\Core\View\CoreUrl;
use Carbon\Carbon;
use Rikkei\ManageTime\View\ManageTimeConst as MTConst;
?>

@extends('layouts.default')

@section('title', trans('manage_time::view.Working time in/out'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/working-time.css') }}">
@stop

@section('content')

<div class="box box-info">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <form class="form-inline no-validate" method="get" action="{{ request()->url() }}" id="form_month_filter">
                            <div class="form-group">
                                <strong>{{ trans('manage_time::view.Month') }}: </strong>&nbsp;&nbsp;&nbsp;
                                <input type="text" id="log_month" name="month" class="form-control form-inline month-filter maxw-230" value="{{ $month }}" autocomplete="off">
                                @if ($employeeId)
                                <input type="hidden" name="employee_id" value="{{ $employeeId }}">
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 text-right">
                        @if (!$employeeId)
                        <button type="button" id="reset_date_btn" title="{{ trans('manage_time::view.Reset') }}"
                                class="btn btn-primary"><i class="fa fa-close"></i> {{ trans('manage_time::view.Reset') }}</button>
                        <button type="button" id="render_date_btn" data-toggle="tooltip" title="{{ trans('manage_time::view.copy_log_date') }}"
                                class="btn btn-primary"><i class="fa fa-copy"></i> {{ trans('manage_time::view.Copy') }}</button>
                        <button type="button" id="btn_submit_form" class="btn btn-primary">
                            <i class="fa fa-save"></i> {{ trans('manage_time::view.Save') }}
                        </button>
                        @else
                        <h3 class="box-title margin-top-0">{{ trans('manage_time::view.Working time in/out of employee') }}: {{ $employee->getNickName() }}</h3>
                        @endif
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                
                <div class="box-body">
                    <ul class="list-inline note-squares">
                        <li><span class="square bg-blue"></span> <span class="desc">{{ trans('manage_time::view.Saturday_txt') }}</span></li>
                        <li><span class="square bg-red"></span> <span class="desc">{{ trans('manage_time::view.Sunday') }}</span></li>
                        <li><span class="square bg-orange"></span> <span class="desc">{{ trans('manage_time::view.Holiday') }}</span></li>
                    </ul>
                </div>

                {!! Form::open([
                    'method' => 'post',
                    'route' => 'manage_time::wktime.save_log_time',
                    'id' => 'working_time_log_form',
                ])  !!}

                <table class="table table-hover table-striped dataTable table-bordered working-time-tbl">
                    <thead>
                        <tr>
                            <th>{{ trans('manage_time::view.No.') }}</th>
                            <th>{{ trans('manage_time::view.Day of week') }}</th>
                            <th>{{ trans('manage_time::view.Date') }}</th>
                            <th>{{ trans('manage_time::view.Time in') }}</th>
                            <th>{{ trans('manage_time::view.Time out') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $daysOfWeek = MTConst::days();
                        try {
                            $timeMonth = Carbon::createFromFormat('m-Y', $month);
                        } catch (\Exception $ex) {
                            $timeMonth = Carbon::now()->format('m-Y');
                        }
                        $collectDates = [];
                        $dateStart = clone $timeMonth;
                        $dateStart->startOfMonth();
                        $dateEnd = clone $timeMonth;
                        $dateEnd->endOfMonth();
                        $numOrder = 1;
                        ?>
                        @while($dateEnd->gte($dateStart))
                        <?php
                        $dateFormat = $dateStart->format('d-m-Y');
                        $collectDates[] = $dateFormat;
                        $classItem = '';
                        if ($dateStart->isSaturday()) {
                            $classItem = 'bg-blue';
                        }
                        if ($dateStart->isSunday()) {
                            $classItem = 'bg-red';
                        }
                        if (in_array($dateStart->toDateString(), $holidays)) {
                            $classItem = 'bg-orange';
                        }
                        $oldTimeIn = old('time_in');
                        $oldTimeOut = old('time_out');
                        
                        ?>
                        <tr {!! $classItem ? 'class="day-off"' : '' !!}>
                            <td>{{ $numOrder }}</td>
                            <td {!! $classItem ? 'class="'. $classItem .'"' : '' !!}>{{ $daysOfWeek[$dateStart->dayOfWeek] }}</td>
                            <td {!! $classItem ? 'class="'. $classItem .'"' : '' !!}>{{ $dateFormat }}</td>
                            <td>
                                <input type="text" name="time_in[{{ $dateFormat }}]" class="form-control time-in time-picker" placeholder="HH:mm" autocomplete="off"
                                       data-format="HH:mm" {{ $employeeId ? 'disabled' : '' }}
                                       value="{{ isset($oldTimeIn[$dateFormat]) ? $oldTimeIn[$dateFormat] : (isset($dataLogs[$dateFormat]) ? $dataLogs[$dateFormat]['time_in'] : null) }}">
                            </td>
                            <td>
                                <input type="text" name="time_out[{{ $dateFormat }}]" class="form-control time-out time-picker" placeholder="HH:mm" autocomplete="off"
                                       data-format="HH:mm" {{ $employeeId ? 'disabled' : '' }}
                                       value="{{ isset($oldTimeOut[$dateFormat]) ? $oldTimeOut[$dateFormat] : (isset($dataLogs[$dateFormat]) ? $dataLogs[$dateFormat]['time_out'] : null) }}">
                            </td>
                        </tr>
                        <?php
                        $numOrder++;
                        $dateStart->addDay();
                        ?>
                        @endwhile
                    </tbody>
                </table>
                
                @if (!$employeeId)
                <div class="box-body text-center">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> {{ trans('manage_time::view.Save') }}
                    </button>
                </div>
                @endif

                {!! Form::close() !!}

            </div>
            
        </div>
    </div>
</div>

@stop

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script>
    var textRequiredItem = '{{ trans('manage_time::view.None item checked') }}';
    var textItemNotValid = '{{ trans('manage_time::view.None item valid!') }}';
    $('#btn_submit_form').click(function () {
        $('#working_time_log_form').submit();
    });

    $('.month-filter').datetimepicker({
        format: 'MM-YYYY',
        useCurrent: false,
    });

    $('#log_month').on('dp.change', function () {
        var form = $(this).closest('form');
        form.submit();
    });

    $('.time-picker').datetimepicker({
        format: 'HH:mm',
        showClear: true,
    });

    $('#render_date_btn').click(function (e) {
        e.preventDefault();
        var firstTr = $('.working-time-tbl tbody tr:not(.day-off):first');
        var timeIn = firstTr.find('input.time-in').val();
        var timeOut = firstTr.find('input.time-out').val();
        $('.working-time-tbl tbody tr:not(.day-off) input.time-in').filter(function () {
            return this.value.trim() == '';
        }).val(timeIn);
        $('.working-time-tbl tbody tr:not(.day-off) input.time-out').filter(function () {
            return this.value.trim() == '';
        }).val(timeOut);
    });

    $('#reset_date_btn').click(function (e) {
        e.preventDefault();
        $('.working-time-tbl tbody input').val('');
    });

    var collectDates = JSON.parse('{!! json_encode($collectDates) !!}');
    var notValidDateFormat = '<?php echo trans('manage_time::message.not_valid_date_format') ?>';
    var textGreaterThanStartTime  = '<?php echo trans('manage_time::message.Time out must be greater than time in') ?>';
</script>
<script src="{{ CoreUrl::asset('asset_managetime/js/working-time.js') }}"></script>
@stop
