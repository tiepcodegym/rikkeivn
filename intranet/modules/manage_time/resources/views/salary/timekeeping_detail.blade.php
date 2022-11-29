@extends('manage_time::layout.common_layout')

<?php
    use Carbon\Carbon;
    use Rikkei\Team\View\Permission;
    use Rikkei\ManageTime\View\ManageTimeConst;
    use Rikkei\ManageTime\View\ManageTimeCommon;

    $userCurrent = Permission::getInstance()->getEmployee();
?>

@section('title-common')
	{{ trans('manage_time::view.Timekeeping month :month year :year', ['month' => $timekeepingTable->month, 'year' => $timekeepingTable->year]) }}
@endsection

@section('css-common')
    <style type="text/css">
        .font-weight-normal {
            font-weight: normal;
        }
        .font-weight-bold {
            font-weight: bold;
        }
        @media (min-width: 1200px) {
            .box-height-200 {
                height: 200px;
            }
        }
    </style>
@endsection

@section('sidebar-common')
    @include('manage_time::salary.include.sidebar_salary')
@endsection

@section('content-common')
    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-globe"></i> {{ trans('manage_time::view.Aggregate of timekeeping') }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover table-grid-data">
                <thead style="background-color: #d9edf7;">
                    <tr>
                        <th class="managetime-col-200 text-center">{{ trans('manage_time::view.Description timekeeping') }}</th>
                        <th class="managetime-col-60 text-center">{{ trans('manage_time::view.Total timekeeping') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $totalHoliday = $timekeepingAggregate->total_official_holiay + $timekeepingAggregate->total_trial_holiay;
                        $totalOTWeekdays = $timekeepingAggregate->total_official_ot_weekdays + $timekeepingAggregate->total_trial_ot_weekdays;
                        $totalOTWeekends = $timekeepingAggregate->total_official_ot_weekends + $timekeepingAggregate->total_trial_ot_weekends;
                        $totalOTHolidays = $timekeepingAggregate->total_official_ot_holidays + $timekeepingAggregate->total_trial_ot_holidays;
                        $totalWorkingDays = $timekeepingAggregate->total_official_working_days + $timekeepingAggregate->total_trial_working_days;
                        $totalLeaveDayHasSalary = $timekeepingAggregate->total_official_leave_day_has_salary + $timekeepingAggregate->total_trial_leave_day_has_salary;
                    ?>
                    <tr>
                        <td>{{ trans('manage_time::view.The total of working days (+)') }}</td>
                        <td class="text-center">{{ number_format($totalWorkingDays, 1) }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('manage_time::view.Overtime on weekdays') }}</td>
                        <td class="text-center">{{ number_format($totalOTWeekdays, 1) }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('manage_time::view.Overtime on weekends') }}</td>
                        <td class="text-center">{{ number_format($totalOTWeekends, 1) }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('manage_time::view.Overtime on holidays') }}</td>
                        <td class="text-center">{{ number_format($totalOTHolidays, 1) }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('manage_time::view.Total number of late in') }}</td>
                        <td class="text-center">{{ number_format($timekeepingAggregate->total_number_late_in, 1) }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('manage_time::view.Total number of early out') }}</td>
                        <td class="text-center">{{ number_format($timekeepingAggregate->total_number_early_out, 1) }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('manage_time::view.Leave day (P)') }}</td>
                        <td class="text-center">{{ number_format($totalLeaveDayHasSalary, 1) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /. box -->
    <div class="box box-solid">
        <div class="box-header with-border">
        	<h3 class="box-title"><i class="fa fa-info"></i> {{ trans('manage_time::view.Timekeeping detail') }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover table-grid-data">
                <thead style="background-color: #d9edf7;">
                    <?php
                        $datesTimekeeping = ManageTimeCommon::getDateRange(Carbon::parse($salary->start_date), Carbon::parse($salary->end_date));
                    ?>
                    <tr>
                        <th class="managetime-col-60">{{ trans('manage_time::view.Day of month') }}</th>
                        <th class="managetime-col-60">{{ trans('manage_time::view.Day of week') }}</th>
                        <th class="managetime-col-200 text-center">{{ trans('manage_time::view.Timekeeping sign') }}</th>
                        <th class="managetime-col-60 text-center">{{ trans('manage_time::view.Fines late in') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $days = ManageTimeConst::days();
                        $totalFinesLateIn = 0;
                    ?>
                    @if (isset($datesTimekeeping) && count($datesTimekeeping))
                        @foreach ($datesTimekeeping as $date)
                            <?php
                                $timekeepingSign = ManageTimeCommon::getTimekeepingSign($timekeepingTable->id, $userCurrent->id, $date);
                                $totalFinesLateIn += $timekeepingSign[1];
                            ?>
                            <tr>
                                <td>{{ $date->format('d/m/Y') }}</td>
                                <td>{{ $days[$date->dayOfWeek] }}</td>
                                <td>{{ $timekeepingSign[0] }}</td>
                                <td class="text-right">
                                    <span>{{ number_format($timekeepingSign[1], 2) }} đ</span>
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="3">
                                <span><b>{{ trans('manage_time::view.Total fines late in') }}<b></span>
                            </td>
                            <td class="text-right">
                                <span><b>{{ number_format($totalFinesLateIn, 2) }} đ<b></span>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <!-- /. box -->
@endsection

@section('script-common')
@endsection