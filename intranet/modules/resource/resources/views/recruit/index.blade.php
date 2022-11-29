@extends('layouts.default')

@section('title', $title)

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('resource/css/recruit.css') }}" />
@endsection

<?php
use Rikkei\Core\View\CoreUrl;

$routeIndex = 'resource::recruit.index';
$tooltipInternal = implode(', ', \Rikkei\Resource\View\getOptions::listWorkingTypeInternal());
?>

@section('content')

<div class="box box-info">
    <div class="box-body">
        {!! Form::open(['method' => 'get', 'route' => $routeIndex, 'class' => 'no-validate']) !!}
        <div class="row">
            <div class="col-sm-3">
                <div class="row">
                    <label class="col-md-3 margin-top-5 bold-label">{{ trans('resource::view.Year') }}</label>
                    <div class="col-md-9">
                        <input type="number" min="1" name="year" class="form-control date-picker" value="{{ $currYear }}">
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data statistics-table">
                    <thead>
                        <tr class="bg-aqua">
                            <th>{{ trans('resource::view.Month') }}</th>
                            <th class="white-space-nowrap">
                                {{ trans('resource::view.Just joined') }}
                                &nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" title="{{ $tooltipInternal . trans('resource::view.internal/total') }}"></i>
                            </th>
                            <th class="white-space-nowrap">
                                {{ trans('resource::view.Leave off') }}
                                &nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" title="{{ $tooltipInternal . trans('resource::view.internal/total') }}"></i>
                            </th>
                            <th class="white-space-nowrap">
                                {{ trans('resource::view.Inc/Dec') }}
                                &nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" title="{{ $tooltipInternal . trans('resource::view.internal/total') }}"></i>
                            </th>
                            <th class="white-space-nowrap">
                                {{ trans('resource::view.Actual') }}
                                &nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" title="{{ $tooltipInternal . trans('resource::view.internal/total') }}"></i>
                            </th>
                            <th class="white-space-nowrap">{{ trans('resource::view.Plan') }}</th>
                            <th class="white-space-nowrap">
                                {{ trans('resource::view.Percent leave off') }}
                                &nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" title="{{ $tooltipInternal . trans('resource::view.internal/total') }}"></i>
                            </th>
                            <th class="white-space-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sumJoined = 0;
                        $sumJoinedTotal = 0;
                        $sumLeave = 0;
                        $sumLeaveTotal = 0;
                        $sumEndPlan = 0;
                        $percentLeave = 0;
                        $percentLeaveTotal = 0;
                        $charActualsMonth = [];
                        $totalCddPass = 0;
                        $totalCddPassTotal = 0;
                        $currentMonth = \Carbon\Carbon::now()->month;
                        ?>
                        @for ($month = 1; $month <= 12; $month++)
                            <?php
                            $numPlan = isset($plansMonth[$month]) ? $plansMonth[$month] : 0;
                            $numLeave = $actualsMonth['leave_month_' . $month];
                            $numLeaveTotal = $actualsMonthTotal['leave_month_' . $month] + (isset($leavedMonthTotal[$month]) ? $leavedMonthTotal[$month] : 0);
                            //candidate passed
                            $cddPass = isset($passesMonth[$month]) ? $passesMonth[$month] : 0;
                            $cddPassTotal = isset($passesMonthTotal[$month]) ? $passesMonthTotal[$month] : 0;
                            $numPass = $actualsMonth['join_month_' . $month] + $cddPass;
                            $numPassTotal = $actualsMonthTotal['join_month_' . $month] + $cddPassTotal;
                            //total candidate passed before
                            $totalCddPass += $cddPass;
                            $totalCddPassTotal += $cddPassTotal;
                            $numActual = $actualsMonth['month_' . $month] + $totalCddPassedBefore + $totalCddPass;
                            $numActualTotal = $actualsMonthTotal['month_' . $month] + $cddPassedBeforeTotal + $totalCddPassTotal;
                            //actual month
                            $charActualsMonth[$month] = $numActual;
                            $sumJoined += $numPass;
                            $sumJoinedTotal += $numPassTotal;
                            $sumLeave += $numLeave;
                            $sumLeaveTotal += $numLeaveTotal;
                            $percentLeave += $numActual == 0 ? 0 : $numLeave / $numActual;
                            $percentLeaveTotal += $numActual == 0 ? 0 : $numLeaveTotal / $numActualTotal;
                            if ($numPlan > 0) {
                                $sumEndPlan = $numPlan;
                            }
                            ?>
                            <tr>
                                <th class="text-center">{{ $month < 10 ? '0' . $month : $month }}</th>
                                <td>{{ $numPass }}/{{ $numPassTotal }}</td>
                                <td>{{ $numLeave }}/{{ $numLeaveTotal }}</td>
                                <td>{{ $numPass - $numLeave }}/{{ $numPassTotal - $numLeaveTotal }}</td>
                                <td>{{ $numActual }}/{{ $numActualTotal }}</td>
                                <td>{{ $numPlan }}</td>
                                <td>
                                    {{ $numActual == 0 ? '0.00' : number_format($numLeave / $numActual * 100, 2, '.', ',') }}% /
                                    {{ $numActualTotal == 0 ? '0.00' : number_format($numLeaveTotal / $numActualTotal * 100, 2, '.', ',') }}%
                                </td>
                                <td class="text-center">
                                    <a target="_blank" href="{{ route('resource::recruit.report_detail', [
                                        'timeType' => 'month',
                                        'type' => 'in',
                                        'year' => $currYear,
                                        'month' => $month < 10 ? '0' . $month : $month
                                    ]) }}"
                                       class="btn btn-sm btn-info" title="{{ trans('resource::view.Detail') }}"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                    <tfoot>
                        <tr class="bg-aqua">
                            <th>{{ trans('resource::view.Sum') }}</th>
                            <td>{{ $sumJoined }}/{{ $sumJoinedTotal }}</td>
                            <td>{{ $sumLeave }}/{{ $sumLeaveTotal }}</td>
                            <td>{{ $sumJoined - $sumLeave }}/{{ $sumJoinedTotal - $sumLeaveTotal }}</td>
                            <td class="white-space-nowrap">
                                {{ $numActual }}/{{ $numActualTotal }}
                                &nbsp;<span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ trans('resource::view.Number of last month') }}"></span>
                            </td>
                            <td class="white-space-nowrap">
                                {{ $sumEndPlan }}
                                &nbsp;<span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ trans('resource::view.Number of last month') }}"></span>
                            </td>
                            <td>
                                <span>{{ number_format($percentLeave * 100, 2, '.', ',') }}%</span> /
                                <span>{{ number_format($percentLeaveTotal * 100, 2, '.', ',') }}%</span>
                            </td>
                            <th class="text-center">
                                <a href="{{ route('resource::recruit.report_detail', [
                                    'timeType' => 'year',
                                    'type' => 'in',
                                    'year' => $currYear
                                ]) }}" class="link color-white" target="_blank"
                                   data-toggle="tooltip" data-placement="top" title="{{ trans('resource::view.Detail this year') }}">
                                    <i class="fa fa-bars"></i>
                                </a>
                            </th>
                        </tr>
                        @if (count($plansMonth) < 1)
                        <tr>
                            <td colspan="8" class="text-center">{{ trans('resource::message.No plan') }}, <a href="{{ route('resource::recruit.build_plan', ['year' => $currYear]) }}">{{ trans('resource::view.Create plan') }}</a></td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <div class="table-responsive">
                <h4 class="text-center">
                    {{ trans('resource::view.Human resource chart')
                    . (isset($isTrainee) ? ' (' . trans('resource::view.Statistic.Trainee') . ') ' : '') . ' '
                    . trans('resource::view.year') . ' ' . $currYear }}
                </h4>
                @if (count($plansMonth) == 0)
                <p class="text-center">({{ trans('resource::message.No plan') }})</p>
                @else
                <br />
                @endif
                <canvas id="recruit_char" width="700" height="400"></canvas>
            </div>
        </div>
    </div>

</div>

@endsection

@section('script')

@include('resource::recruit.script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/chartjs/Chart_2.5_.min.js') }}"></script>
<script>
    var isTrainee = parseInt('{{ isset($isTrainee) }}');
    (function ($) {
        $('.date-picker').datepicker({
            format: 'yyyy',
            viewMode: "years",
            minViewMode: "years",
            autoclose: true
        }).on('changeDate', function (e) {
            $(e.target).closest('form').submit();
        });
    })(jQuery);
</script>
<script src="{{ CoreUrl::asset('resource/js/recruit/index.js') }}"></script>
@endsection