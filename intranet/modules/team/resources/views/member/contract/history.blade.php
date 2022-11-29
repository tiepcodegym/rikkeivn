<?php

use Rikkei\Team\Model\EmployeeWork;
use Carbon\Carbon;
$optionsWorkContract = EmployeeWork::getAllTypeContract();
?>

<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover">
        <thead>
            <tr>
                <th>{{ trans('core::view.NO.') }}</th>
                <th>{{ trans('team::profile.Contract type') }}</th>
                <th>{{ trans('team::view.Employee code') }}</th>
                <th>{{ trans('team::view.Employee card id') }}</th>
                <th>{{ trans('team::profile.Start date') }}</th>
                <th>{{ trans('team::profile.End date') }}</th>
                <th>{{ trans('team::profile.Join date') }}</th>
                <th>{{ trans('team::profile.Official date') }}</th>
                <th>{{ trans('team::profile.Leave date') }}</th>
            </tr>
        </thead>
        <tbody>
            @if (!$contractHistories->isEmpty())
            @foreach ($contractHistories as $order => $empItem)
            <tr>
                <td>{{ $order + 1 }}</td>
                <td>{{ $empItem->getContractLabel($optionsWorkContract) }}</td>
                <td>{{ $empItem->employee_code }}</td>
                <td>{{ $empItem->employee_card_id }}</td>
                <td>{{ $empItem->start_at ? Carbon::parse($empItem->start_at)->toDateString() : ''}}</td>
                <td>{{ $empItem->end_at ? Carbon::parse($empItem->end_at)->toDateString() : '' }}</td>
                <td>{{ $empItem->join_date ? Carbon::parse($empItem->join_date)->toDateString() : '' }}</td>
                <td>{{ $empItem->official_date ? Carbon::parse($empItem->official_date)->toDateString() : '' }}</td>
                <td>{{ $empItem->leave_date ? Carbon::parse($empItem->leave_date)->toDateString() : '' }}</td>
            </tr>
            @endforeach
            @else
            <tr>
                <td colspan="9"><h5 class="text-center">{{ trans('team::messages.None item found') }}</h5></td>
            </tr>
            @endif
        </tbody>
    </table>
</div>