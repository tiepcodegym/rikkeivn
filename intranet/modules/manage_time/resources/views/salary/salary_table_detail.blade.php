@extends('layouts.default')

@section('title')
    {{ $salaryTable->salary_table_name }}
@endsection

<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Team\Model\Employee;
    use Rikkei\ManageTime\Model\Salary;

    $tblSalary = Salary::getTableName();
    $tblEmployee = Employee::getTableName();
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}" />
@endsection

@section('content')
    <div class="box box-primary">
        <div class="box-header with-border">
            <div class="pull-right">   
                @include('manage_time::include.filter')
            </div>
        </div>
        <!-- /.box-header -->

        <div class="box-body no-padding"> 
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table-control" id="table-reason">
                    <thead>
                        <tr>
                            <th class="managetime-col-25" style="min-width: 25px;">{{ trans('manage_time::view.No.') }}</th>
                            <th class="managetime-col-60 sorting {{ TeamConfig::getDirClass('employee_code') }}" data-order="employee_code" data-dir="{{ TeamConfig::getDirOrder('employee_code') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.Employee code') }}</th>
                            <th class="managetime-col-80 sorting {{ TeamConfig::getDirClass('employee_name') }}" data-order="employee_name" data-dir="{{ TeamConfig::getDirOrder('employee_name') }}" style="min-width: 80px; max-width: 80px;">{{ trans('manage_time::view.Full name') }}</th>
                            <th class="managetime-col-120 sorting {{ TeamConfig::getDirClass('role_name') }}" data-order="role_name" data-dir="{{ TeamConfig::getDirOrder('role_name') }}" style="min-width: 120px; max-width: 120px;">{{ trans('manage_time::view.Position') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('official_salary') }}" data-order="official_salary" data-dir="{{ TeamConfig::getDirOrder('official_salary') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Official salary (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('trial_salary') }}" data-order="trial_salary" data-dir="{{ TeamConfig::getDirOrder('trial_salary') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Trial salary (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('overtime_salary') }}" data-order="overtime_salary" data-dir="{{ TeamConfig::getDirOrder('overtime_salary') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Overtime salary (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('gasoline_allowance') }}" data-order="gasoline_allowance" data-dir="{{ TeamConfig::getDirOrder('gasoline_allowance') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Gasonlie allowance (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('telephone_allowance') }}" data-order="telephone_allowance" data-dir="{{ TeamConfig::getDirOrder('telephone_allowance') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Telephone allowance (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('certificate_allowance') }}" data-order="certificate_allowance" data-dir="{{ TeamConfig::getDirOrder('certificate_allowance') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Certificate allowance (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('bonus_and_other_allowance') }}" data-order="bonus_and_other_allowance" data-dir="{{ TeamConfig::getDirOrder('bonus_and_other_allowance') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Bonus and other allowance (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('other_income') }}" data-order="other_income" data-dir="{{ TeamConfig::getDirOrder('other_income') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Other income (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('total_income') }}" data-order="total_income" data-dir="{{ TeamConfig::getDirOrder('total_income') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Total income (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('premium_and_union') }}" data-order="premium_and_union" data-dir="{{ TeamConfig::getDirOrder('premium_and_union') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Premium and union (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('advance_payment') }}" data-order="advance_payment" data-dir="{{ TeamConfig::getDirOrder('advance_payment') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Advance payment (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('personal_income_tax') }}" data-order="personal_income_tax" data-dir="{{ TeamConfig::getDirOrder('personal_income_tax') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Personal income tax (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('total_deduction') }}" data-order="total_deduction" data-dir="{{ TeamConfig::getDirOrder('total_deduction') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Total deduction (đ)') }}</th>
                            <th class="managetime-col-70 sorting {{ TeamConfig::getDirClass('money_received') }}" data-order="money_received" data-dir="{{ TeamConfig::getDirOrder('money_received') }}" style="min-width: 70px; max-width: 70px;">{{ trans('manage_time::view.Money received (đ)') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tblEmployee }}.employee_code]" value='{{ CoreForm::getFilterData("{$tblEmployee}.employee_code") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tblEmployee }}.name]" value='{{ CoreForm::getFilterData("{$tblEmployee}.name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        @if (isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <?php
                                        $moneyReceived = $item->money_received;
                                        if ($moneyReceived < 0) {
                                            $moneyReceived = 0;
                                        }
                                    ?>
                                    <td>{{ $i }}</td>
                                    <td>{{ $item->employee_code }}</td>
                                    <td>{{ $item->employee_name }}</td>
                                    <td>{{ $item->role_name }}</td>
                                    <td class="text-right">{{ number_format($item->official_salary, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->trial_salary, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->overtime_salary, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->gasoline_allowance, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->telephone_allowance, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->certificate_allowance, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->bonus_and_other_allowance, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->other_income, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->total_income, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->premium_and_union, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->advance_payment, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->personal_income_tax, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->total_deduction, 2) }}</td>
                                    <td class="text-right">{{ number_format($moneyReceived, 2) }}</td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="18" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <!-- /.box-body -->

        <div class="box-footer no-padding">
            @include('team::include.pager')
        </div>
        <!-- /.box-footer -->
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript">
    </script>
@endsection

