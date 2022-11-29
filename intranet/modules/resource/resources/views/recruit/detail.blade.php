@extends('layouts.default')

@section('title', $title)

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('resource/css/recruit.css') }}" />
@endsection

@section('content')

<?php
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Team\View\Config;
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;

$empTbl = Employee::getTableName();
$cddTbl = Candidate::getTableName();
$cddWorkingTypeFilter = FormView::getFilterData('candidate', 'working_type');
$cddTypeFilter = FormView::getFilterData('candidate', 'type');
$empWorkingTypeFilter = FormView::getFilterData('employee', 'working_type');

$tabLinks = [];
$routeName = 'resource::recruit.report_detail';
foreach ($aryTimeTypes as $iTimeType) {
    $tabLinks[$iTimeType] = [];
    foreach ($aryTypes as $iType) {
        $tabLinks[$iTimeType][$iType] = [
            'title' => trans('resource::view.recruit_tab.' . $iType),
            'link' => route($routeName, ['timeType' => $iTimeType, 'type' => $iType, 'year' => $year, 'month' => $month])
        ];
    }
}

$routeDetail = route('resource::recruit.report_detail', ['timeType' => $timeType, 'type' => $type, 'year' => null, 'month' => null]);
$tooltipInternal = implode(', ', getOptions::listWorkingTypeInternal());
$typeHasHead = ['in', 'out', 'total-in', 'total-out'];
?>

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-8">
                <div class="form-inline margin-right-20">
                @if ($timeType == 'year')
                    <h4 class="form-inline">{{ trans('resource::view.Detail') . ' ' . trans('resource::view.Year') . ': ' }}&nbsp;&nbsp;</h4>
                    <input type="text" class="year-picker form-control form-inline" value="{{ $year }}" style="max-width: 230px;">
                @else
                    <h4 class="form-inline">{{ trans('resource::view.Detail') . ' ' . trans('resource::view.Month') . ': ' }}&nbsp;&nbsp;</h4>
                    <input type="text" class="month-picker form-control form-inline" value="{{ $year . '-' . (intval($month) < 10 ? '0' . intval($month) : $month) }}" style="max-width: 230px;">
                @endif
                </div>
                <div class="form-inline filter-wrapper" data-url="{{ $routeTeamFilter }}">
                    <h4 class="form-inline">{{ trans('resource::view.Team') }}&nbsp;&nbsp;</h4>
                    @if (!$isScopeTeam || count($teamList) > 1)
                    <select style="width: 230px;" name="filter[excerpt][team_id]"
                            class="form-control select-grid filter-grid select-search has-search">
                        @if (!$isScopeTeam)
                        <option value="">&nbsp;</option>
                        @endif
                        @foreach($teamList as $option)
                        <option value="{{ $option['value'] }}"<?php if ($option['value'] == $cdTeamFilter): ?> selected<?php endif;?>>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    @else
                        @if (isset($teamList[0]))
                        <span>{{ $teamList[0]['label'] }}</span>
                        @endif
                    @endif
                </div>
            </div>
            <div class="col-md-4 text-right tooltip-large-group">
                @if (in_array($type, $typeHasHead))
                <button class="btn btn-success" data-toggle="tooltip" title="{!! trans('resource::view.statistics_export_note') !!}"
                        data-html="true"
                        data-url="{{ route('resource::recruit.export_detail', [
                            'timeType' => $timeType,
                            'type' => $type,
                            'year' => $year,
                            'month' => $month
                        ]) }}"
                        id="btn_export_stats_detail">
                    <i class="fa fa-download"></i> {{ trans('resource::view.Export') }}
                </button>
                @endif
                <span class="form-inline">
                    @include('team::include.filter')
                </span>
            </div>
        </div>
    </div>

    @if (in_array($type, $typeHasHead))
    <div class="margin-bottom-10 padding-lr-10">
        <div class="table-responsive padding-lr-0">
            <table class="table dataTable table-bordered table-hover table-grid-data statistics-table">
                <thead>
                    <tr class="bg-light-blue">
                        <th class="nowwrap">{{ trans('resource::view.Team') }}</th>
                        @foreach ($generalTeams as $team)
                        <th class="nowwrap">{{ $team->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $arrSumIn = [];
                    $arrSumOut = [];
                    $sumCddIn = 0;
                    ?>
                    <tr>
                        <th>+/-</th>
                        @foreach ($generalTeams as $team)
                        <?php
                        $arrJoin = isset($joinedTeams[$team->id]) ? $joinedTeams[$team->id]->lists('emp_id')->toArray() : [];
                        $arrLeave = isset($leavedTeams[$team->id]) ? explode(',', $leavedTeams[$team->id]) : [];
                        $numCdd = 0;
                        if (isset($cddJoinedTeams[$team->id])) {
                            $numCdd = $cddJoinedTeams[$team->id];
                        }
                        $sumCddIn += $numCdd;
                        $numJoin = count($arrJoin) + $numCdd;
                        $numLeave = count($arrLeave);
                        $arrSumIn = array_unique(array_merge($arrSumIn, $arrJoin));
                        $arrSumOut = array_unique(array_merge($arrSumOut, $arrLeave));
                        ?>
                        <td class="text-left">{{ $numJoin - $numLeave }}</td>
                        @endforeach
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>
                            {{ trans('resource::view.Sum') }} 
                            <span class="fa fa-question-circle" data-toggle="tooltip" 
                                  title="{{ trans('resource::view.recruit_tab.in') . ' - ' . trans('resource::view.recruit_tab.out') }}"></span>
                        </th>
                        <th colspan="{{ count($teamList) }}">{{ count($arrSumIn) + $sumCddIn - count($arrSumOut) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
    <hr />
    @endif
    
    <div class="margin-bottom-10 padding-lr-10">
        <div class="nav-tabs-custom tab-keep-status">
            <ul class="nav nav-tabs recruit-tabs" role="tablist">
                @foreach ($tabLinks[$timeType] as $iType => $tab)
                <li {!! $type == $iType ? 'class="active"' : '' !!}>
                    <a href="{{ $tab['link'] }}">
                        {{ $tab['title'] }}
                        @if (in_array($iType, ['in', 'out']))
                        &nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" title="{{ $tooltipInternal }}"></i>
                        @endif
                    </a>
                </li>
                @endforeach
            </ul>
            <div class="tab-content min-height-150">
                
                @include('resource::recruit.include.employee-' . $type)
                
            </div>           
        </div>
    </div>
    
</div>

@endsection

@section('script')

@include('resource::recruit.script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('resource/js/recruit/index.js') }}"></script>
@if ($type == 'dev-position')
<script src="{{ asset('lib/fixed-table/tableHeadFixer.js') }}"></script>
@endif
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        selectSearchReload();
    });
    var routeDetail = '{{ $routeDetail }}';

    (function ($) {
        $('.btn-search-filter, .btn-reset-filter').click(function () {
            resetUrl();
        });
        $('select.filter-grid').change(function () {
            resetUrl();
        });
        $('input.filter-grid').keydown(function (e) {
            if (e.which == 13) {
                resetUrl();
            }
        });

        function resetUrl() {
            var url = window.location;
            window.history.pushState('', '', url.pathname + url.hash);
        }

        $('.year-picker').datepicker({
            format: 'yyyy',
            viewMode: "years", 
            minViewMode: "years",
            autoclose: true
        }).on('changeDate', function (e) {
            window.location.href = routeDetail + '/' + e.format();
        });

        $('.month-picker').datepicker({
            format: 'yyyy-mm',
            viewMode: "months", 
            minViewMode: "months",
            autoclose: true
        }).on('changeDate', function (e) {
            var date = e.date;
            window.location.href = routeDetail + '/' + date.getFullYear() + '/' + (date.getMonth() + 1);
        });

        @if ($type == 'dev-position')
            $('.fixed-table').tableHeadFixer({'left' : 1}); 
        @endif

    })(jQuery);
</script>
@endsection