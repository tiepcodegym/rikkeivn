<?php

use Rikkei\Core\View\Form;
use Rikkei\Sales\Model\Css;
use Rikkei\Core\View\View;

$teamChargeFilter = Form::getFilterData('except','team_charge_id', null);
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
table tr td {
    border: 1px solid #0a0a0a;
            text-align: center;
        }
    </style>
</head>
<body>
@if ($teamChargeFilter)
    <input type="hidden" name="filter[except][team_charge_id]" value="{{ $teamChargeFilter }}">
@endif
<table>
    <tr class="offset">
        <td>
{{ trans('sales::view.No.') }}
</td>
<td>
    {{ trans('sales::view.Project name') }}
</td>
<td>
    {{ trans('sales::view.Team_in_charge') }}
</td>
<td>
    {{ trans('sales::view.Team') }}
</td>
<td>
    {{ trans('sales::view.PM name') }}
</td>
<td>
    {{ trans('sales::view.Project type') }}
</td>
<td>
    {{ trans('sales::view.Customer name') }}
</td>
<td>
    {{ trans('sales::view.Company Name') }}
</td>
<td>
    {{ trans('sales::view.Status of project') }}
</td>
<td>
    {{ trans('sales::view.Start date') }}
</td>
<td>
    {{ trans('sales::view.End Date') }}
</td>
<td>
    {{ trans('sales::view.Avg point') }}
</td>
<td>
    {{ trans('sales::view.Sale name') }}
</td>
<td>
    {{ trans('sales::view.CSS.List.Date created') }}
</td>
<td>
    {{ trans('sales::view.Last working day') }}
</td>
<td>
    {{ trans('sales::view.Viewed') }}
</td>
<td>
    {{ trans('sales::view.Marked') }}
</td>
<td>
    {{ trans('sales::view.Status') }}
</td>
<td>
    {{ trans('sales::view.Language') }}
</td>
</tr>
@foreach($css as $key => $data)
    <tr class="offset">
        <td>{{ $key + 1 }}</td>
        <td>{{ $data->project_name }}</td>
        <td>{{ $data->team_leader_name }}</td>
        <td>{{ $data->teamsName }}</td>
        <td>{{ $data->pm_name }}</td>
        <td>{{ $data->project_type_name }}</td>
        <td>{{ $data->customer_name }}</td>
        <td>{{ $data->company_name }}</td>
        <td>{{ isset(Css::getStatusCss()[$data->status]) ? Css::getStatusCss()[$data->status] : '' }}</td>
        <td>{{ $data->start_date }}</td>
        <td>{{ $data->end_date }}</td>
        <td>{{ $data->avg_point }}</td>
        <td>{{ $data->sale_name }}</td>
        <td>{{ $data->created_date }}</td>
        <td>{{ $data->lastWork_date }}</td>
        <td>{{ $data->countViewCss }}</td>
        <td>{{ $data->countMakeCss }}</td>
        <td>{{ Css::getAnalyzeStatus($data->analyze_status) }}</td>
        <td>
            @if ($data->lang_id == Css::ENG_LANG) 
                {{ trans('sales::view.English')}}
            @elseif ($data->lang_id == Css::VIE_LANG)
                {{ trans('sales::view.Vietnamese') }}
            @else
                {{trans('sales::view.Japanese')}}
            @endif
        </td>
    </tr>
    @endforeach
    </table>
    </body>
    </html>
