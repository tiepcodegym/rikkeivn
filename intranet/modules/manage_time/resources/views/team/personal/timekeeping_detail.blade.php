@extends('manage_time::layout.common_layout')
<?php
    use Carbon\Carbon;
    use Rikkei\ManageTime\View\ManageTimeConst;
    use Rikkei\ManageTime\View\ManageTimeCommon;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\ManageTime\View\View as ManageTimeView;
    use Rikkei\ManageTime\Model\TimekeepingTable;

    $totalHoliday = $timekeepingAggregate->total_official_holiay + $timekeepingAggregate->total_trial_holiay;
    $totalOTWeekdays = $timekeepingAggregate->total_official_ot_weekdays + $timekeepingAggregate->total_trial_ot_weekdays;
    $totalOTWeekends = $timekeepingAggregate->total_official_ot_weekends + $timekeepingAggregate->total_trial_ot_weekends;
    $totalOTHolidays = $timekeepingAggregate->total_official_ot_holidays + $timekeepingAggregate->total_trial_ot_holidays;
    $totalWorkingDays = $timekeepingAggregate->total_official_working_days + $timekeepingAggregate->total_trial_working_days;
    $totalLeaveDayHasSalary = $timekeepingAggregate->total_official_leave_day_has_salary + $timekeepingAggregate->total_trial_leave_day_has_salary;
    $totalRegisterBusinessTrip = $timekeepingAggregate->total_official_business_trip + $timekeepingAggregate->total_trial_business_trip;
    $totalRegisterSupplement = $timekeepingAggregate->total_official_supplement + $timekeepingAggregate->total_trial_supplement;
    $totalCompensation = $timekeepingAggregate->number_com_off + $timekeepingAggregate->number_com_tri;
    $totalLeaveDayBasic = $timekeepingAggregate->total_official_leave_basic_salary + $timekeepingAggregate->total_trial_leave_basic_salary;
    $totalWorking = $timekeepingAggregate->total_working_officail + $timekeepingAggregate->total_working_trial;

    $datesTimekeeping = ManageTimeCommon::getDateRange(Carbon::parse($timekeepingTable->start_date), Carbon::parse($timekeepingTable->end_date));
    $worksInTimekeeping = ManageTimeCommon::countWorkingDay($timekeepingTable->start_date, $timekeepingTable->end_date);
?>
@section('title-common')
    {{ trans('manage_time::view.Timekeeping month :month year :year', ['month' => $timekeepingTable->month, 'year' => $timekeepingTable->year]) }}
    : {{ $userCurrent->name }}
@endsection

@section('css-common')
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/personal.css') }}" />
    <style>
        .table-hover>tbody>tr:hover {
            background-color: #e1979745;
        }
        .table-hover>tbody>tr:hover td {
            border: aliceblue;
        }
    </style>
@endsection

@section('content')
<div class="box box-solid timekeeping-personal-detail">
    <div class="row">
        <div class="col-md-12">
            <div class="box-body">
                @if ($timekeepingTable->lock_up == TimekeepingTable::CLOSE_LOCK_UP)
                    <div class="text-center">
                        <i class="fa fa-lock color-7d0" aria-hidden="true" style="font-size: 20px"></i> :
                        {{ Carbon::parse( $timekeepingTable->lock_up_time)->format('d-m-Y H:i') }}
                    </div>
                    <hr class="mt-0">
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <div class="box-body">
                <h3 class="box-title mt-0">
                    {{ trans('manage_time::view.Total timekeeping has salary') }}
                    <span class="fa fa-question-circle help"><span class="help-note">{{ trans('manage_time::view.Timekeeping personal help note') }}</span></span> : {{ $totalWorking . ' / ' . $worksInTimekeeping }}
                </h3>
                <table class="table table-striped table-bordered table-hover table-grid-data">
                    <thead style="background-color: #d9edf7;">
                        <tr>
                            <th class="managetime-col-200 text-center">{{ trans('manage_time::view.Detail') }}</th>
                            <th class="managetime-col-60 text-center">{{ trans('manage_time::view.Number of timekeeping') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ trans('manage_time::view.The total of working days (+)') }}</td>
                            <td class="text-center">{{ number_format($totalWorkingDays, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Overtime on weekdays') }}</td>
                            <td class="text-center">{{ number_format($totalOTWeekdays, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Overtime on weekends') }}</td>
                            <td class="text-center">{{ number_format($totalOTWeekends, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Overtime on holidays') }}</td>
                            <td class="text-center">{{ number_format($totalOTHolidays, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Overtime no salary (OTKL)') }}</td>
                            <td class="text-center">{{ number_format($timekeepingAggregate->total_ot_no_salary, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Total number of late in') }}</td>
                            <td class="text-center">{{ number_format($timekeepingAggregate->total_number_late_in, 0) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Total number of early out') }}</td>
                            <td class="text-center">{{ number_format($timekeepingAggregate->total_number_early_out, 0) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Leave day (P)') }}</td>
                            <td class="text-center">{{ number_format($totalLeaveDayHasSalary, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Leave day no salary (KL)') }}</td>
                            <td class="text-center">{{ number_format($timekeepingAggregate->total_leave_day_no_salary, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Basic salary (LCB)') }}</td>
                            <td class="text-center">{{ number_format($totalLeaveDayBasic, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Holiday (L)') }}</td>
                            <td class="text-center">{{ number_format($totalHoliday, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Business trip (CT)') }}</td>
                            <td class="text-center">{{ number_format($totalRegisterBusinessTrip, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Supplement (BS)') }}</td>
                            <td class="text-center">{{ number_format($totalRegisterSupplement, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Late start shift (M1)') }}</td>
                            <td class="text-center">{{ number_format($timekeepingAggregate->total_late_start_shift, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Early mid shift (S1)') }}</td>
                            <td class="text-center">{{ number_format($timekeepingAggregate->total_early_mid_shift, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Late mid shift (M2)') }}</td>
                            <td class="text-center">{{ number_format($timekeepingAggregate->total_late_mid_shift, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('manage_time::view.Early end shift (S2)') }}</td>
                            <td class="text-center">{{ number_format($timekeepingAggregate->total_early_end_shift, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-8">
           <div class="body-left">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info"></i> {{ trans('manage_time::view.Timekeeping detail') }}</h3>
                    </div>
                    <div class="table-responsive box-body">
                        <table class="table table-striped table-bordered table-hover table-grid-data">
                            <thead style="background-color: #d9edf7;">
                                <tr>
                                    <th class="managetime-col-60">{{ trans('manage_time::view.Day of month') }}</th>
                                    <th class="managetime-col-60">{{ trans('manage_time::view.Day of week') }}</th>
                                    <th class="managetime-col-60">{{ trans('manage_time::view.Time in morning') }}</th>
                                    <th class="managetime-col-60">{{ trans('manage_time::view.Time out morning') }}</th>
                                    <th class="managetime-col-60">{{ trans('manage_time::view.Time in afternoon') }}</th>
                                    <th class="managetime-col-60">{{ trans('manage_time::view.Time out afternoon') }}</th>
                                    <th class="managetime-col-200 text-center">{{ trans('manage_time::view.Timekeeping sign') }}</th>
                                    <th class="managetime-col-60 text-center">Số phút làm thừa/thiếu <span class="fa fa-question-circle tooltip-leave" data-toggle="tooltip" title="" data-html="true" data-original-title="<ul><li>> 0: Làm thừa</li> <li>< 0: Làm thiếu</li></ul>"></span></th>
                                    <th class="managetime-col-60 text-center">{{ trans('manage_time::view.Fines late in') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $days = ManageTimeConst::days();
                                    $totalFinesLateIn = 0;
                                    $totalLateJp = 0;
                                    $totalEarlyJp = 0;
                                ?>
                                @if (isset($datesTimekeeping) && count($datesTimekeeping))
                                    @foreach ($datesTimekeeping as $date)
                                        <?php
                                            $data = $dataKeeping[$date->format('Y-m-d')];
                                            $timekeepingSign = ManageTimeCommon::getTimekeepingSign($dataKeeping[date('Y-m-d', strtotime($date))], $teamCodePrefix, $compensationDays, $arrHolidays);   
                                            $timeInOut = ManageTimeView::displayTimeInOut($dataKeeping[$date->format('Y-m-d')]);
                                            if (strpos($timekeepingTable->code, 'japan') !== false) {
                                                $lateJp = ManageTimeView::getLateEarly($dataKeeping[date('Y-m-d', strtotime($date))])['late'];
                                                $earlyJp = ManageTimeView::getLateEarly($dataKeeping[date('Y-m-d', strtotime($date))])['early'];
                                                $totalLateJp += $lateJp;
                                                $totalEarlyJp += $earlyJp;
                                            }
                                            $strWT = '';
                                            if (isset($workingTimdDate) && isset($workingTimdDate[$date->format('Y-m-d')])) {
                                                $strWT = $workingTimdDate[$date->format('Y-m-d')];
                                            }
                                        ?>
                                        <tr title='{{ $strWT }}'>
                                            <td>{{ $date->format('d/m/Y') }}</td>
                                            <td>{{ $days[$date->dayOfWeek] }}</td>
                                            <td>{{ $timeInOut['timeInMor'] }}</td>
                                            <td>{{ $timeInOut['timeOutMor'] }}</td>
                                            <td>{{ $timeInOut['timeInAfter'] }}</td>
                                            <td>{{ $timeInOut['timeOutAfter'] }}</td>
                                            <td>{{ $timekeepingSign[0] }}</td>
                                            <td>{{ $timeInOut['timeOver'] }}</td>
                                            @if (strpos($timekeepingTable->code, 'japan') !== false)
                                                <td class="text-center">
                                                    {{ $lateJp }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $earlyJp }}
                                                </td>
                                            @else
                                                <td class="text-right">
                                                    <span>
                                                        @php
                                                            $finesMoney = with(new ManageTimeConst())->getFinesMoneyLateIn($timekeepingSign[1], $timekeepingTable->code);
                                                            $totalFinesLateIn += $finesMoney;
                                                            echo number_format($finesMoney, 0);
                                                        @endphp
                                                        đ
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="8">
                                            <span>
                                                <b>{{ trans('manage_time::view.Total fines late in') }}</b>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <span>
                                                <b>{{ number_format($totalFinesLateIn, 0) }} đ</b>
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
           </div>
        </div>
    </div>
</div>
@endsection
