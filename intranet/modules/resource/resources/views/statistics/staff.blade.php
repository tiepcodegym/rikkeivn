@extends('layouts.default')

@section('title', trans('resource::view.stat.staff_statistics'))

<?php
use Rikkei\Core\View\CoreUrl;

$workTimes = [
    6 => [
        'from' => 0,
        'to' => 6,
        'title' => '0 - < 6 ' . trans('resource::view.month')
    ],
    12 => [
        'from' => 6,
        'to' => 12,
        'title' => '6 '. trans('resource::view.month') .' - 1 ' . trans('resource::view.year')
    ],
    36 => [
        'from' => 12,
        'to' => 36,
        'title' => '1 - 3 ' . trans('resource::view.year')
    ],
    1000 => [
        'from' => 36,
        'to' => 1000,
        'title' => '> 3 ' . trans('resource::view.year')
    ],
];

$totalTeam = isset($total) ? $total->groupBy('team_id')->toArray() : null;
$dateEnd = isset($dateEnd) ? $dateEnd : null;
$routeDetail = route('resource::staff.stat.index', ['timeType' => $timeType]);
?>

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('resource/css/statistics.css') }}">
@endsection

@section('content')

<div class="box box-info">

    <div class="box-body">
        @if ($timeType == 'year')
        <div class="form-inline margin-right-20">
            <h4 class="form-inline">{{ trans('resource::view.Detail') . ' ' . trans('resource::view.Year') . ': ' }}&nbsp;&nbsp;</h4>
            <input type="text" class="input-datepicker year-picker form-control form-inline" data-format="YYYY" value="{{ $year }}" style="max-width: 230px;">
        </div>
        <div class="form-inline">
            <h4><a href="{{ route('resource::staff.stat.index', ['timeType' => 'month']) }}">{{ trans('resource::view.stat.View by month') }}</a></h4>
        </div>
        @else
        <div class="form-inline margin-right-20">
            <h4 class="form-inline">{{ trans('resource::view.Detail') . ' ' . trans('resource::view.Month') . ': ' }}&nbsp;&nbsp;</h4>
            <input type="text" class="input-datepicker month-picker form-control form-inline" data-format="YYYY-MM" value="{{ $year . '-' . (intval($month) < 10 ? '0' . intval($month) : $month) }}" style="max-width: 230px;">
        </div>
        <div class="form-inline">
            <h4><a href="{{ route('resource::staff.stat.index', ['timeType' => 'year']) }}">{{ trans('resource::view.stat.View by year') }}</a></h4>
        </div>
        @endif
    </div>

    <div class="table-responsive fixed-container">
        <table class="fixed-table table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle statistics-table">
            <thead>
                <tr>
                    <th class="fixed-col" rowspan="2">No.</th>
                    <th class="fixed-col" rowspan="2">{{ trans('resource::view.stat.Department') }}</th>
                    <th class="fixed-col" rowspan="2">{{ trans('resource::view.stat.Total number') }}</th>
                    <th class="bg-success text-center" colspan="{{ count($roles) }}">{{ trans('resource::view.stat.Role work') }}</th>
                    <th class="bg-info text-center" colspan="{{ count($workTimes) }}">{{ trans('resource::view.stat.Seniority work') }}</th>
                    <th class="bg-warning text-center" colspan="{{ count($contracts) }}">{{ trans('resource::view.stat.Working type') }}</th>
                </tr>
                <tr>
                    @foreach ($roles as $roleName)
                    <th class="bg-success">{{ $roleName }}</th>
                    @endforeach
                    @foreach ($workTimes as $workTime)
                    <th class="bg-info">{{ $workTime['title'] }}</th>
                    @endforeach
                    @foreach ($contracts as $contractName)
                    <th class="bg-warning">{{ $contractName }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <?php $order = 1; ?>
                @if (isset($staffStatisticsBaseline))
                    @foreach($arrayTeams as $team)
                    <?php $teamId = $team['id']; ?>
                    <tr data-id="{{ $teamId }}" data-parent="{{ $team['parent_id'] }}">
                        <td class="fixed-col">{{ $order++ }}</td>
                        <td class="fixed-col white-space-nowrap">{{ str_repeat("&nbsp;", $team['depth'] * 4) . $team['name'] }}</td>
                        <?php // The team was created before insert statistics baseline ?>
                        @if (isset($staffStatisticsBaseline[$teamId]))
                            <?php $teamData = $staffStatisticsBaseline[$teamId]; ?>
                            <td class="fixed-col col-total{{ $teamId == -1 ? ' num-highlight' : '' }}">{!! $teamData['total'] !!}</td>

                            @if (isset($teamData['roles']))
                                @foreach ($roles as $roleId => $roleName)
                                <td class="col-role bg-success text-right">
                                    {!! isset($teamData['roles'][$roleId]) ? $teamData['roles'][$roleId] : 0 !!}
                                </td>
                                @endforeach
                            @else
                                @foreach ($roles as $roleId => $roleName)
                                    <td class="col-role bg-success text-right">0</td>
                                @endforeach
                            @endif

                            @if (isset($teamData['workedMonths']))
                                @foreach ($workTimes as $monthId => $workTime)
                                    <td class="col-time bg-info text-right">{!! $teamData['workedMonths'][$monthId] !!}</td>
                                @endforeach
                            @else
                                @foreach ($workTimes as $monthId => $workTime)
                                    <td class="col-time bg-info text-right">0</td>
                                @endforeach
                            @endif

                            @if (isset($teamData['contracts']))
                                @foreach ($contracts as $contractType => $contractName)
                                    <td class="col-contract bg-warning text-right">{!! $teamData['contracts'][$contractType] !!}</td>
                                @endforeach
                            @else
                                @foreach ($contracts as $contractType => $contractName)
                                    <td class="col-contract bg-warning text-right">0</td>
                                @endforeach
                            @endif
                        @else
                            <td class="fixed-col col-total{{ $teamId == -1 ? ' num-highlight' : '' }}"></td>
                            @foreach ($roles as $roleId => $roleName)
                                <td class="col-role bg-success text-right"></td>
                            @endforeach
                            @foreach ($workTimes as $monthId => $workTime)
                                <td class="col-time bg-info text-right"></td>
                            @endforeach
                            @foreach ($contracts as $contractType => $contractName)
                                <td class="col-contract bg-warning text-right"></td>
                            @endforeach
                        @endif
                    </tr>
                    @endforeach
                @else
                    @foreach($arrayTeams as $team)
                        <?php $teamId = $team['id']; ?>
                        <tr data-id="{{ $teamId }}" data-parent="{{ $team['parent_id'] }}">
                            <td class="fixed-col">{{ $order++ }}</td>
                            <td class="fixed-col white-space-nowrap">{{ str_repeat("&nbsp;", $team['depth'] * 4) . $team['name'] }}</td>
                            <td class="fixed-col col-total{{ $teamId == -1 ? ' num-highlight' : '' }}">
                                @if ($teamId > 0)
                                    <div class="hidden team-data">{!! isset($totalTeam[$teamId]) ? json_encode($totalTeam[$teamId]) : null !!}</div>
                                @else
                                    <div class="hidden team-data">{!! json_encode($total->toArray()) !!}</div>
                                @endif
                            </td>
                            @foreach ($roles as $roleId => $roleName)
                                <td class="col-role bg-success text-right" data-role="{{ $roleId }}"></td>
                            @endforeach
                            @foreach ($workTimes as $monthId => $workTime)
                                <td class="col-time bg-info text-right" data-month="{{ $monthId }}" data-from="{{ $workTime['from'] }}" data-to="{{ $workTime['to'] }}"></td>
                            @endforeach
                            @foreach ($contracts as $contractType => $contractName)
                                <td class="col-contract bg-warning text-right" data-type="{{ $contractType }}"></td>
                            @endforeach
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="box-body"></div>

</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="{{ asset('lib/fixed-table/tableHeadFixer.js') }}"></script>
<script>
    var dateEnd = '{{ $dateEnd }}';
    var aryWorkTimes = JSON.parse('{!! json_encode($workTimes) !!}');
    var TEAM_BOD_ID = {{ Rikkei\Team\Model\Team::TEAM_BOD_ID }};
    var fixedCols = 3;
    var routeDetail = '{{ $routeDetail }}';

    RKfuncion.general.initDateTimePicker();

    $('.year-picker').on('dp.change', function (e) {
        var date = new Date(e.date);
        window.location.href = routeDetail + '?year=' + date.getFullYear();
    });
    $('.month-picker').on('dp.change', function (e) {
        var date = new Date(e.date);
        window.location.href = routeDetail + '?year=' + date.getFullYear() + '&month=' + (date.getMonth() + 1);
    });
</script>
<script src="{{ CoreUrl::asset('resource/js/staff/statistics.js') }}"></script>
@endsection
