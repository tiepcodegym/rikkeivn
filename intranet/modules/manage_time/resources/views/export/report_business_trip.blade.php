<?php
$colspan = 7;
$txtClassHeader = 'text-center vertical-align-middle bg-header border-black';
?>

<html>
<head>
    <meta content="charset=utf-8">
    <style>
        .text-center {text-align: center}
        .text-left {text-align: left}
        .vertical-align-middle {vertical-align: middle}
        .bg-header {background-color: #d9edf7}
        .border-black {border: 1px solid #000}
        td {height: 22}
        .header th, td.team-name {height: 30}
    </style>
</head>
<body>
<table>
    <thead>
    <tr class="bg-header border-black">
        <th colspan="{!! $colspan !!}" class="text-center vertical-align-middle" height="50">
            {!! trans('manage_time::export.filter_date: :filterDate', ['filterDate' => $filterDate]) !!}
        </th>
    </tr>
    <tr class="header">
        <th class="{!! $txtClassHeader !!}" width="6">{!! trans('manage_time::view.No.') !!}</th>
        <th class="{!! $txtClassHeader !!}" width="18">{!! trans('manage_time::view.Employee code') !!}</th>
        <th class="{!! $txtClassHeader !!}" width="30">{!! trans('manage_time::view.Employee') !!}</th>
        <th class="{!! $txtClassHeader !!}" width="30">{!! trans('manage_time::view.Location') !!}</th>
        <th class="{!! $txtClassHeader !!}" width="20">{!! trans('manage_time::view.From date') !!}</th>
        <th class="{!! $txtClassHeader !!}" width="20">{!! trans('manage_time::view.End date') !!}</th>
        <th class="{!! $txtClassHeader !!}" width="41">{!! trans('manage_time::view.Number of business trip days in a month') !!}</th>
    </tr>
    </thead>
    <tbody>
        @foreach ($collectionModel as $team)
            <tr>
                <td colspan="{!! $colspan !!}" class="text-left vertical-align-middle team-name">
                    <b>{{ $team['name'] }} ({!! trans('manage_time::view.Total user business trip:') !!} {!! count($team['employees']) !!})</b>
                </td>
            </tr>
            <?php $i = 0; ?>
            @foreach ($team['employees'] as $emp)
                <?php $totalStartAt = count($emp['start_at']);  ?>
                @foreach ($emp['start_at'] as $key => $startAt)
                <tr>
                    @if ($key === 0)
                    <td rowspan="{!! $totalStartAt !!}" class="text-center vertical-align-middle">{!! ++$i !!}</td>
                    <td rowspan="{!! $totalStartAt !!}" class="text-left vertical-align-middle">{!! $emp['code'] !!}</td>
                    <td rowspan="{!! $totalStartAt !!}" class="text-left vertical-align-middle">{{ $emp['name'] }}</td>
                    @else
                    <td></td><td></td><td></td>
                    @endif
                    <td class="text-left">{{ $emp['location'][$key] }}</td>
                    <td>{!! $startAt !!}</td>
                    <td>{!! $emp['end_at'][$key] !!}</td>
                    @if ($key === 0)
                    <td rowspan="{!! $totalStartAt !!}" class="text-center vertical-align-middle">{!! $emp['onsite_days'] !!}</td>
                    @else
                    <td></td>
                    @endif
                </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
</body>
</html>
