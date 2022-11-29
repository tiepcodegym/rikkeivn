@extends('manage_time::layout.common_layout')

@section('title-common')
	{{ trans('manage_time::view.Salary month :month year :year', ['month' => $salary->month, 'year' => $salary->year]) }}
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
    <div class="row">
        <div class="col-lg-6">
            <div class="box box-solid box-height-200">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-globe"></i> {{ trans('manage_time::view.Information common') }}</h3>
                </div>
                <div class="box-body">
                    <div class="margin-bottom-5 form-label-left col-md-12">
                        <label class="col-sm-5 control-label font-weight-normal">{{ trans('manage_time::view.Employee code:') }}</label>
                        <div class="col-sm-7">
                            <label class="control-label">{{ $salary->employee_code }}</label>
                        </div>
                    </div>
                    <div class="margin-bottom-5 form-label-left col-md-12">
                        <label class="col-sm-5 control-label font-weight-normal">{{ trans('manage_time::view.Full name:') }}</label>
                        <div class="col-sm-7">
                            <label class="control-label">{{ $salary->employee_name }}</label>
                        </div>
                    </div>
                    <div class="margin-bottom-5 form-label-left col-md-12">
                        <label class="col-sm-5 control-label font-weight-normal">{{ trans('manage_time::view.Money received this period:') }}</label>
                        <div class="col-sm-7">
                            <label class="control-label">{{ number_format($salary->money_received, 2) }} đ</label>
                        </div>
                    </div>
                    <div class="margin-bottom-5 form-label-left col-md-12">
                        <label class="col-sm-5 control-label font-weight-normal">{{ trans('manage_time::view.Basic salary:') }}</label>
                        <div class="col-sm-7">
                            <label class="control-label">{{ number_format($salary->basic_salary, 2) }} đ</label>
                        </div>
                    </div>
                    <div class="margin-bottom-5 form-label-left col-md-12">
                        <label class="col-sm-5 control-label font-weight-normal">{{ trans('manage_time::view.Insurance premiums:') }}</label>
                        <div class="col-sm-7">
                            <label class="control-label"></label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /. box -->
        </div>
        <div class="col-lg-6">
            <div class="box box-solid box-height-200">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> {{ trans('manage_time::view.Information to salary') }}</h3>
                </div>
                <div class="box-body">
                    <?php
                        $totalWorkingToSalary = 0;
                        $totalOTToSalary = 0;
                        if ($timekeepingAggregate) {
                            $totalHoliday = $timekeepingAggregate->total_official_holiay + $timekeepingAggregate->total_trial_holiay;
                            $totalOTWeekdays = $timekeepingAggregate->total_official_ot_weekdays + $timekeepingAggregate->total_trial_ot_weekdays;
                            $totalOTWeekends = $timekeepingAggregate->total_official_ot_weekends + $timekeepingAggregate->total_trial_ot_weekends;
                            $totalOTHolidays = $timekeepingAggregate->total_official_ot_holidays + $timekeepingAggregate->total_trial_ot_holidays;
                            $totalOTOfficial = $timekeepingAggregate->total_official_ot_weekdays + $timekeepingAggregate->total_official_ot_weekends + $timekeepingAggregate->total_official_ot_holidays;
                            $totalOTTrial = $timekeepingAggregate->total_trial_ot_weekdays + $timekeepingAggregate->total_trial_ot_weekends + $timekeepingAggregate->total_trial_ot_holidays;
                            $totalWorkingOfficialToSalary = $timekeepingAggregate->total_official_working_days + $timekeepingAggregate->total_official_business_trip + $timekeepingAggregate->total_official_leave_day_has_salary + $timekeepingAggregate->total_official_supplement + $timekeepingAggregate->total_official_holiay;
                            $totalWorkingTrialToSalary = $timekeepingAggregate->total_trial_working_days + $timekeepingAggregate->total_trial_business_trip + $timekeepingAggregate->total_trial_leave_day_has_salary + $timekeepingAggregate->total_trial_supplement + $timekeepingAggregate->total_trial_holiay;
                            $totalWorkingToSalary = $totalWorkingOfficialToSalary + $totalWorkingTrialToSalary;
                            $totalWorkingNoSalary = $salary->number_working_days - $totalWorkingToSalary;
                            $totalOTToSalary = $totalOTOfficial + $totalOTTrial;
                        }
                    ?>
                    <div class="margin-bottom-5 form-label-left col-md-12">
                        <label class="col-lg-6 col-sm-5 control-label font-weight-normal">{{ trans('manage_time::view.Total timekeeping has salary:') }}</label>
                        <div class="col-lg-6 col-sm-7">
                            <label class="control-label">{{ number_format($totalWorkingToSalary, 2) }}</label>
                            <span style="margin-left: 20px;"><a href="{{ route('manage_time::profile.salary.timekeeping-detail', ['id' => $salary->salary_table_id]) }}">{{ trans('manage_time::view.Detail of timekeeping') }}</a></span>
                        </div>
                    </div>
                    <div class="margin-bottom-5 form-label-left col-md-12">
                        <label class="col-lg-6 col-sm-5 control-label font-weight-normal">{{ trans('manage_time::view.Total time of overtime has salary:') }}</label>
                        <div class="col-lg-6 col-sm-7">
                            <label class="control-label">{{ number_format($totalOTToSalary, 2) }}</label>
                        </div>
                    </div>
                    <div class="margin-bottom-5 form-label-left col-md-12">
                        <label class="col-lg-6 col-sm-5 control-label font-weight-normal">{{ trans('manage_time::view.Total timekeeping no salary:') }}</label>
                        <div class="col-lg-6 col-sm-7">
                            <label class="control-label">{{ number_format($totalWorkingNoSalary, 2) }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /. box -->
        </div>
    </div>
    <div class="box box-solid">
        <div class="box-header with-border">
        	<h3 class="box-title">{{ trans('manage_time::view.Detail of money received') }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover table-grid-data">
                <tbody>
                    <tr class="font-weight-bold">
                        <td class="managetime-col-320">{{ trans('manage_time::view.(1) Income from salaries and wages') }}</td>
                        <td class="managetime-col-180 text-right">{{ number_format($salary->total_income, 2) }} đ</td>
                        <td class="managetime-col-200"></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('manage_time::view.Official salary') }}</td>
                        <td class="text-right">{{ number_format($salary->official_salary, 2) }} đ</td>
                        <td></td>
                    </tr>
                    @if ($salary->trial_salary > 0)
                        <tr>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('manage_time::view.Trial salary') }}</td>
                            <td class="text-right">{{ number_format($salary->trial_salary, 2) }} đ</td>
                            <td></td>
                        </tr>
                    @endif
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('manage_time::view.Overtime salary') }}</td>
                        <td class="text-right">{{ number_format($salary->overtime_salary, 2) }} đ</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('manage_time::view.Gasonlie allowance') }}</td>
                        <td class="text-right">{{ number_format($salary->gasoline_allowance, 2) }} đ</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('manage_time::view.Telephone allowance') }}</td>
                        <td class="text-right">{{ number_format($salary->telephone_allowance, 2) }} đ</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('manage_time::view.Certificate allowance') }}</td>
                        <td class="text-right">{{ number_format($salary->certificate_allowance, 2) }} đ</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('manage_time::view.Bonus and other allowance') }}</td>
                        <td class="text-right">{{ number_format($salary->bonus_and_other_allowance, 2) }} đ</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('manage_time::view.Other income') }}</td>
                        <td class="text-right">{{ number_format($salary->other_income, 2) }} đ</td>
                        <td></td>
                    </tr>
                    <tr class="font-weight-bold">
                        <td>{{ trans('manage_time::view.(2) Deductions') }}</td>
                        <td class="text-right">{{ number_format($salary->total_deduction, 2) }} đ</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('manage_time::view.Premium and union') }}</td>
                        <td class="text-right">{{ number_format($salary->premium_and_union, 2) }} đ</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('manage_time::view.Advance payment') }}</td>
                        <td class="text-right">{{ number_format($salary->advance_payment, 2) }} đ</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('manage_time::view.Personal income tax') }}</td>
                        <td class="text-right">{{ number_format($salary->personal_income_tax, 2) }} đ</td>
                        <td></td>
                    </tr>
                    <tr class="font-weight-bold">
                        <td>{{ trans('manage_time::view.(3) Money received = (1) - (2)') }}</td>
                        <td class="text-right">{{ number_format($salary->money_received, 2) }} đ</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /. box -->
@endsection

@section('script-common')
@endsection