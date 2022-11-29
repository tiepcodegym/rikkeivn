<?php

use Rikkei\Project\Model\MonthlyReport;

?>
<div class="table-responsive padding-left-250">
    <table class="table table-bordered table-hover dataTable" role="grid"
           data-type='{{ MonthlyReport::TYPE_BUSINESS }}'>
        <thead>
        <tr role="row">
            <th></th>
            @for ($i=$startMonth; $i<=$endMonth; $i++)
                <th rowspan="1" colspan="2" class="align-center">T{{ $i }}</th>
                @endfor
            </tr>
            <tr role="row">
                <th></th>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <th rowspan="1" colspan="1" class="align-center">{{ trans('project::view.Value') }}</th>
                <th rowspan="1" colspan="1" class="align-center">{{ trans('project::view.Point') }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            <tr role="row" class="odd" data-row="{{ $rowBillEffort }}">
                <td class="width-200">{{ trans('project::view.Billable effort(MM)') }}</td>
                @for ($i = $startMonth; $i <= $endMonth; $i++)
                <td class="sorting_1">
                    <span>
                        {{ isset($values[$team->id][$i][$rowBillEffort][MonthlyReport::IS_VALUE]) ? $values[$team->id][$i][$rowBillEffort][MonthlyReport::IS_VALUE] : $notAvailable }}
                    </span>
                </td>
                <td class="sorting_1 text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowBillEffort][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowBillEffort][MonthlyReport::IS_POINT] : $notAvailable}}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="even" data-row='{{ $rowApprovedCost }}'>
                <td class="width-200">{{ trans('project::view.Approved cost (cash Man)') }}</td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                @php
                    $approvedCostMonth = isset($values[$team->id][$i][$rowApprovedCost][MonthlyReport::IS_VALUE]) ? $values[$team->id][$i][$rowApprovedCost][MonthlyReport::IS_VALUE] : $notAvailable;
                @endphp
                <td class="sorting_1">
                    @if ($updatePemission)
                        <input type="hidden" class="form-control num" data-month="{{ $i }}"
                               data-value-or-point='{{ MonthlyReport::IS_VALUE }}' autocomplete="off"
                               value="{{ $approvedCostMonth }}"
                        />
                    @endif
                    <span>{{ $approvedCostMonth }}</span>
                </td>
                <td class="sorting_1 text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowApprovedCost][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowApprovedCost][MonthlyReport::IS_POINT] : $notAvailable}}
                    </span>
                </td>
            @endfor
        </tr>
        <tr role="row" class="odd" data-row="{{ $rowCost }}">
            <td class="width-200">Cost (cash Man)</td>
            @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1">
                    @if ($updatePemission)
                        <input type="text" class="form-control num" data-month="{{ $i }}"
                               data-value-or-point='{{ MonthlyReport::IS_VALUE }}' autocomplete="off"
                               value="{{ isset($values[$team->id][$i][$rowCost][MonthlyReport::IS_VALUE]) ?  $values[$team->id][$i][$rowCost][MonthlyReport::IS_VALUE] : ''}}"
                        />
                    @else
                        {{ isset($values[$team->id][$i][$rowCost][MonthlyReport::IS_VALUE]) ?  $values[$team->id][$i][$rowCost][MonthlyReport::IS_VALUE] : ''}}
                    @endif
                </td>
                <td class="sorting_1 text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowCost][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowCost][MonthlyReport::IS_POINT] : $notAvailable}}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="even" data-row="{{ $rowApprovedProdCost }}">
                <td class="width-200">
                    <span>{{ trans('project::view.Approved production cost (MM)') }}</span>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                @php
                    $projApprovedProdCost = isset($values[$team->id][$i][$rowApprovedProdCost]['value']) ? $values[$team->id][$i][$rowApprovedProdCost]['value'] : $notAvailable;
                @endphp
                <td class="sorting_1 month-value">
                    @if ($updatePemission)
                        <input type="hidden" class="form-control num" data-month="{{ $i }}"
                               data-value-or-point='{{ MonthlyReport::IS_VALUE }}' autocomplete="off"
                               value="{{ $projApprovedProdCost }}"
                        />
                    @endif
                    <span data-month="{{ $i }}">{{ $projApprovedProdCost }}</span>
                </td>
                <td class="sorting_1 text-green month-point">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowApprovedProdCost][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowApprovedProdCost][MonthlyReport::IS_POINT] : $notAvailable}}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="odd hidden" data-row="{{ $rowBillActual }}">
                <td class="width-200">
                    <span>{{ trans('project::view.Billable actual (MM)') }}</span>
                </td>
                @for ($i = $startMonth; $i <= $endMonth; $i++)
                <td class="sorting_1">
                    @php
                        $cellBillActualVal = isset($values[$team->id][$i][$rowBillActual][MonthlyReport::IS_VALUE]) ? $values[$team->id][$i][$rowBillActual][MonthlyReport::IS_VALUE] : null;
                    @endphp
                    @if ($updatePemission)
                        <input type="text" class="form-control num" data-month="{{ $i }}"
                               data-value-or-point="{{ MonthlyReport::IS_VALUE }}"
                               autocomplete="off" value="{{ $cellBillActualVal }}"/>
                    @else
                        {{ $cellBillActualVal }}
                    @endif
                </td>
                <td class="sorting_1 text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowPlan][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowPlan][MonthlyReport::IS_POINT] : $notAvailable}}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="even" data-row="{{ $rowActual }}">
                <td class="width-200">
                    <span>{{ trans('project::view.Actual allocation (MM)') }}</span>
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.Total effort allocation of projects in month') }}"></i>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1 month-value">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'>
                        {{ isset($values[$team->id][$i][$rowActual][MonthlyReport::IS_VALUE]) ?  $values[$team->id][$i][$rowActual][MonthlyReport::IS_VALUE] : $notAvailable}}
                    </span>
                </td>
                <td class="sorting_1 text-green month-point">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowActual][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowActual][MonthlyReport::IS_POINT] : $notAvailable}}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="odd" data-row="{{ $rowBusinessEffective }}">
                <td class="width-200">
                    {{ trans('project::view.% Business Effectiveness') }}
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.% Cost/Approved cost (cash man)') }}"></i>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1 month-value">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'>
                        {{ isset($values[$team->id][$i][$rowBusinessEffective][MonthlyReport::IS_VALUE]) ?  $values[$team->id][$i][$rowBusinessEffective][MonthlyReport::IS_VALUE] : $notAvailable}}
                    </span>
                </td>
                <td class="sorting_1 month-point text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowBusinessEffective][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowBusinessEffective][MonthlyReport::IS_POINT] : 0}}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="even"  data-row="{{ $rowCompletedPlan }}">
                <td class="width-200">
                    {{ trans('project::view.% Cost controlling') }}
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.% Actual allocate/Approved production cost (MM)') }}"></i>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1 month-value">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'>
                    {{ isset($values[$team->id][$i][$rowCompletedPlan][MonthlyReport::IS_VALUE]) ? $values[$team->id][$i][$rowCompletedPlan][MonthlyReport::IS_VALUE] : $notAvailable }}
                    </span>
                </td>
                <td class="sorting_1 month-point text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowCompletedPlan][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowCompletedPlan][MonthlyReport::IS_POINT] : 0}}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="odd">
                <td class="width-200">
                    {{ trans('project::view.% Busy rate') }}
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.Collect from Project Dashboard, total actual effort of projects/total allocation of projects.') }}"></i>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1 month-value">{{ isset($values[$team->id][$i][$rowBusyRate][MonthlyReport::IS_VALUE]) ? $values[$team->id][$i][$rowBusyRate][MonthlyReport::IS_VALUE] : $notAvailable }}</td>
                <td class="sorting_1 month-point text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowBusyRate][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowBusyRate][MonthlyReport::IS_POINT] : 0}}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="even" data-row="{{ $rowAlloStaffActual }}">
                <td class="width-200">
                    {{ trans('project::view.% Allocation/Staffs actual') }}
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.% Actual allocation/Staffs actual') }}"></i>
                </td>
                @for ($i = $startMonth; $i <= $endMonth; $i++)
                <td class="sorting_1 month-value">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'>
                        {{ isset($values[$team->id][$i][$rowAlloStaffActual][MonthlyReport::IS_VALUE]) ? $values[$team->id][$i][$rowAlloStaffActual][MonthlyReport::IS_VALUE] : $notAvailable }}
                    </span>
                </td>
                <td class="sorting_1 month-point text-green">
                    <span data-month="{{ $i }}" data-value-or-point="{{ MonthlyReport::IS_POINT }}">
                        {{ isset($values[$team->id][$i][$rowAlloStaffActual][MonthlyReport::IS_POINT]) ? $values[$team->id][$i][$rowAlloStaffActual][MonthlyReport::IS_POINT] : 0 }}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="odd" data-row="{{ $rowProdStaffActual }}">
                <td class="width-200" style="white-space: nowrap;">
                    {{ trans('project.view.% Production cost/Staffs actual') }}
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.% Approved production cost/Staffs actual') }}"></i>
                </td>
                @for ($i = $startMonth; $i <= $endMonth; $i++)
                <td class="sorting_1 month-value">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'>
                        {{ isset($values[$team->id][$i][$rowProdStaffActual][MonthlyReport::IS_VALUE]) ? $values[$team->id][$i][$rowProdStaffActual][MonthlyReport::IS_VALUE] : $notAvailable }}
                    </span>
                </td>
                <td class="sorting_1 month-point text-green">
                    <span data-month="{{ $i }}" data-value-or-point="{{ MonthlyReport::IS_POINT }}">
                        {{ isset($values[$team->id][$i][$rowProdStaffActual][MonthlyReport::IS_POINT]) ? $values[$team->id][$i][$rowProdStaffActual][MonthlyReport::IS_POINT] : 0 }}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="even hidden" data-row="{{ $rowBillStaffPlan }}">
                <td class="width-200">
                    {{ trans('project::view.% Billable/Staffs planning') }}
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.% Billable complete/Staffs planning') }}"></i>
                </td>
                @for ($i = $startMonth; $i <= $endMonth; $i++)
                <td class="sorting_1 month-value">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'>
                        {{ isset($values[$team->id][$i][$rowBillStaffPlan][MonthlyReport::IS_VALUE]) ? $values[$team->id][$i][$rowBillStaffPlan][MonthlyReport::IS_VALUE] : $notAvailable }}
                    </span>
                </td>
                <td class="sorting_1 month-point text-green">
                    <span data-month="{{ $i }}" data-value-or-point="{{ MonthlyReport::IS_POINT }}">
                        {{ isset($values[$team->id][$i][$rowBillStaffPlan][MonthlyReport::IS_POINT]) ? $values[$team->id][$i][$rowBillStaffPlan][MonthlyReport::IS_POINT] : 0 }}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="odd hidden" data-row="{{ $rowBillStaffActual }}">
                <td class="width-200">
                    {{ trans('project::view.% Billable/Staffs actual') }}
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.% Billable complete/Staffs actual') }}"></i>
                </td>
                @for ($i = $startMonth; $i <= $endMonth; $i++)
                <td class="sorting_1 month-value">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'>
                        {{ isset($values[$team->id][$i][$rowBillStaffActual][MonthlyReport::IS_VALUE]) ? $values[$team->id][$i][$rowBillStaffActual][MonthlyReport::IS_VALUE] : $notAvailable }}
                    </span>
                </td>
                <td class="sorting_1 month-point text-green">
                    <span data-month="{{ $i }}" data-value-or-point="{{ MonthlyReport::IS_POINT }}">
                        {{ isset($values[$team->id][$i][$rowBillStaffActual][MonthlyReport::IS_POINT]) ? $values[$team->id][$i][$rowBillStaffActual][MonthlyReport::IS_POINT] : 0 }}
                    </span>
                </td>
            @endfor
        </tr>
        </tbody>
    </table>
</div>
@if ($updatePemission)
<div class="row">
    <div class="col-md-12 align-center">
        <button type="button" class="btn-add btn-submit">
            {{ trans('project::view.Submit') }}
            <i class="fa fa-spin fa-refresh hidden"></i>
        </button>
    </div>
@endif
