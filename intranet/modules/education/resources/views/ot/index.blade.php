<?php
$urlFilter = '';
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Team\View\TeamList;
use Carbon\Carbon;
use Rikkei\Team\Model\Team;
$teamsOptionAll = TeamList::toOption(null, true, false);
$dataFilter = Form::getFilterData('except', null, null);

$filterFromDate = !is_null($dataFilter) && isset($dataFilter['date_from']) ? $dataFilter['date_from'] : null;
$filterToDate = !is_null($dataFilter) && isset($dataFilter['date_to']) ? $dataFilter['date_to'] : null;
$filterCategoryId = !is_null($dataFilter) && isset($dataFilter['category_id']) ? $dataFilter['category_id'] : null;
if (is_null($dataFilter)){
    $filterFromDate = date("Y-m-01", strtotime(Carbon::now()));
    $filterToDate = date("Y-m-t", strtotime(Carbon::now()));
    $filterCategoryId = \Rikkei\Education\Http\Services\EmployeeOtService::ALL_OT;
}
$teamList = TeamList::toOption(null, false, false);
?>

@extends('layouts.default')

@section('title')
    {{ trans('education::view.List times OT.Title') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="{{ CoreUrl::asset('education/css/education-ot-style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('project/css/report.css') }}">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css"/>

@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="team-ot-select-box col-md-3">
                                    <label>{{trans('education::view.List times OT.labels.From date')}}</label>
                                    <div class="input-box">
                                        <input type="text"
                                               id="fromDate"
                                               class='form-control date-picker filter-grid form-inline'
                                               name="filter[except][date_from]"
                                               value="{{ $filterFromDate }}"
                                               placeholder="{{ trans('education::view.List times OT.labels.buttons.Search') }}"/>
                                    </div>
                                </div>

                                <div class="team-ot-select-box col-md-3">
                                    <label>{{trans('education::view.List times OT.labels.To date')}}</label>
                                    <div class="input-box">
                                        <input type="text"
                                               id="toDate"
                                               class='form-control date-picker filter-grid form-inline'
                                               name="filter[except][date_to]"
                                               value="{{ $filterToDate }}"
                                               placeholder="{{ trans('education::view.List times OT.labels.buttons.Search') }}"/>
                                    </div>
                                </div>

                                <div class="team-ot-select-box col-md-3">
                                    <label>{{trans('education::view.List times OT.labels.Category')}}</label>
                                    <div class="input-box">
                                        <select class="form-control select-search select-grid filter-grid" name="filter[except][category_id]">
                                            @foreach($categories as $key => $category)
                                                <option value="{{$key}}" @if($filterCategoryId == $key) selected @endif>
                                                    {{ trans("education::view.List times OT.labels.Category option.{$category}") }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-primary btn-export-filter">
                                <span>Export</span>
                            </button>
                            @include('team::include.filter')
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered dataTable" role="grid">
                        <thead>
                        <tr role="row">
                            <th class="{{ Config::getDirClass('id') }}" data-order="id" data-dir="{{ Config::getDirOrder('id') }}" >{{trans('education::view.List times OT.labels.Header Table.No')}}</th>
                            <th class="sorting {{ Config::getDirClass('employee_code') }}" data-order="employee_code" data-dir="{{ Config::getDirOrder('employees.employee_code') }}" >{{trans('education::view.List times OT.labels.Header Table.Code')}}</th>
                            <th class="sorting {{ Config::getDirClass('employee_name') }}" data-order="employee_name" data-dir="{{ Config::getDirOrder('employee_name') }}" >{{trans('education::view.List times OT.labels.Header Table.Name')}}</th>
                            <th class="sorting {{ Config::getDirClass('team_name') }}" data-order="team_name" data-dir="{{ Config::getDirOrder('team_name') }}" >{{trans('education::view.List times OT.labels.Header Table.Team')}}</th>
                            <th class="sorting {{ Config::getDirClass('project_name') }}" data-order="project_name" data-dir="{{ Config::getDirOrder('project_name') }}">{{trans('education::view.List times OT.labels.Header Table.Project')}}</th>

                            <th class="sorting {{ Config::getDirClass('ot_in_week') }} text-center" data-order="ot_in_week" data-dir="{{ Config::getDirOrder('ot_in_week') }}">
                                {{trans('education::view.List times OT.labels.Header Table.OT in week')}}
                                <br> {{ trans('education::view.List times OT.labels.Header Table.Hours') }}
                            </th>
                            <th class="sorting {{ Config::getDirClass('ot_end_week') }} text-center" data-order="ot_end_week" data-dir="{{ Config::getDirOrder('ot_end_week') }}">
                                {{trans('education::view.List times OT.labels.Header Table.OT end week')}}
                                <br> {{ trans('education::view.List times OT.labels.Header Table.Hours') }}
                            </th>
                            <th class="sorting {{ Config::getDirClass('ot_holidays_week') }} text-center" data-order="ot_holidays_week" data-dir="{{ Config::getDirOrder('ot_holidays_week') }}">
                                {{trans('education::view.List times OT.labels.Header Table.OT in holidays')}}
                                <br> {{ trans('education::view.List times OT.labels.Header Table.Hours') }}
                            </th>
                            <th class="text-center">
                                {{trans('education::view.List times OT.labels.Header Table.OT exchange')}}
                                <br> {{ trans('education::view.List times OT.labels.Header Table.Hours') }}
                                <span class="fa fa-question-circle"
                                      data-toggle="tooltip"
                                      title=""
                                      data-html="true"
                                      data-original-title="
                                            {{trans('education::view.List times OT.labels.Header Table.OT exchange')}}
                                            =
                                            ({{trans('education::view.List times OT.labels.Header Table.OT in week')}} * 1.5)
                                            +
                                            ({{trans('education::view.List times OT.labels.Header Table.OT end week')}} * 2)
                                            +
                                            ({{trans('education::view.List times OT.labels.Header Table.OT in holidays')}} * 3)
                                      ">
                                </span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="filter-input-grid">
                            <td></td>
                            <td style="width: 200px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <input class='form-control filter-grid' name="filter[employee_code]" value="{{ Form::getFilterData('except' ,'employee_code') }}" placeholder="{{ trans('education::view.List times OT.labels.buttons.Search') }}..."/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div>
                                            <select id="sale_id" class="form-control select-search hidden select-grid filter-grid"
                                                    name="filter[except][sale_id]" style="width: 100%" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                                <option value="">&nbsp;</option>
                                                @foreach ($employeeList as $value)
                                                    <option value="{{ $value->id }}" {{ Form::getFilterData('except', 'sale_id') == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{--<input class='form-control filter-grid' name="filter[except][employee_name]" value="{{ Form::getFilterData('except', 'employee_name') }}" placeholder="{{ trans('education::view.List times OT.labels.buttons.Search') }}..." class="filter-grid form-control" />--}}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">

                                        <select style="width: 100%" name="filter[except][team_id]" class="form-control select-grid filter-grid select-search has-search" data-team="dev">
                                            <option value="">&nbsp;</option>
                                            @if($isScopeCompany === true)
                                                @foreach ($teamList as $teamOpt)
                                                    <option value="{{$teamOpt['value']}}" @if($teamOpt['value'] == $teamIdCurrent) selected @endif>{{ $teamOpt['label'] }}</option>
                                                @endforeach
                                            @elseif($isScopeTeam == true || $isScopeSelf == true)
                                                @foreach ($teamList as $teamOpt)
                                                    <option value="{{$teamOpt['value']}}"
                                                        {{ ($teamOpt['value'] == $teamIdCurrent) ? 'selected' : ''  }}
                                                        @if(!in_array($teamOpt['value'], $teamIdsAvailable)) disabled @endif>{{ $teamOpt['label'] }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select name="filter[except][project]" id="select-search-project" style="width: 100%"
                                                class="form-control select-search select-grid filter-grid">
                                                <option value="">{{ trans('education::view.team_default') }}</option>
                                                @foreach($projects as $project)
                                                    <option value="{{$project->id}}" @if($projectCurrent == $project->id) selected @endif>
                                                        {{$project->name}}
                                                    </option>
                                                @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $dataOtTotal['ot_week'] }}</td>
                            <td>{{ $dataOtTotal['ot_weekend'] }}</td>
                            <td>{{ $dataOtTotal['ot_holiday'] }}</td>
                            <td>{{ $dataOtTotal['total_ot_hours'] }}</td>
                        </tr>

                        @if(count($collectionModel) > 0)
                        @php
                            $i = View::getNoStartGrid($collectionModel);
                        @endphp
                        @foreach($dataForRender as $employeeCode => $employeeDataGroupByEmployeeName)
                            @php
                                $rowSpanForGroupByEmployeeName = count($employeeDataGroupByEmployeeName);
                                $isFirstRenderGroupByEmployee = true;
                            @endphp
                            <tr>
                                <td class="col-id" rowspan='{{$rowSpanForGroupByEmployeeName}}'>
                                    {{$i}}
                                </td>
                                <td class="employee_code" rowspan='{{$rowSpanForGroupByEmployeeName}}'>
                                    {{$employeeDataGroupByEmployeeName->first()->employee_code}}
                                </td>
                                <td class="employee_name" rowspan='{{$rowSpanForGroupByEmployeeName}}'>
                                    {{$employeeDataGroupByEmployeeName->first()->employee_name}}
                                </td>
                                @php
                                    $employeeDataGroupByTeams = $employeeDataGroupByEmployeeName->groupBy('team_id');
                                @endphp
                                @foreach($employeeDataGroupByTeams as $index => $employeeDataGroupByTeam)
                                    @php
                                        $isFirstRenderGroupByTeam = true;
                                        $rowSpanForGroupByTeam = count($employeeDataGroupByTeam);
                                    @endphp
                                    @foreach($employeeDataGroupByTeam as $employeeData)
                                        @if(!$isFirstRenderGroupByEmployee)
                                            <tr>
                                            @php
                                                $isFirstRenderGroupByEmployee = true
                                            @endphp
                                        @endif
                                        @if($isFirstRenderGroupByTeam)
                                            <td class="team_name" rowspan='{{$rowSpanForGroupByTeam}}'>
                                                {{$employeeData->teams}}
                                            </td>
                                            @php
                                                $isFirstRenderGroupByTeam = false
                                            @endphp

                                        @endif
                                        @php
                                            $ot_in_week = ($employeeData->team_count > 1) ? round($employeeData->ot_in_week/$employeeData->team_count, 2) : round($employeeData->ot_in_week, 2);
                                            $ot_end_week = ($employeeData->team_count > 1) ? round($employeeData->ot_end_week/$employeeData->team_count, 2) : round($employeeData->ot_end_week, 2);
                                            $ot_holidays_week = ($employeeData->team_count > 1) ? round($employeeData->ot_holidays_week/$employeeData->team_count, 2) : round($employeeData->ot_holidays_week, 2);
                                        @endphp
                                            <td class="project_name" >
                                                {{$employeeData->project_name}}
                                            </td>
                                            <td class="ot_week ot_hour" >
                                                {{ $ot_in_week }}
                                            </td>
                                            <td class="ot_weekend ot_hour">
                                                {{ $ot_end_week }}
                                            </td>
                                            <td class="ot_holiday ot_hour">
                                                {{ $ot_holidays_week }}
                                            </td>
                                            <td class="total_ot_hours ot_hour">
                                                {{round(($ot_in_week * 1.5 + $ot_end_week * 2 + $ot_holidays_week * 3), 2)}}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            <?php $i++; ?>
                        @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">
                                    <h3>
                                        {{trans('education::view.List times OT.labels.no data')}}
                                    </h3>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    @include('education::ot.include.pager')
                </div>
            </div>
            <div class="col-sm-12 text-center">
                <a href="/" class="btn btn-primary">
                    {{ trans('education::view.List times OT.labels.buttons.Close') }}
                </a>
            </div>
            <form action="{{ route('education::education.ot.export.ot_list') }}" method="post" id="export-OT-list" class="no-validate">
                {!! csrf_field() !!}
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script type="text/javascript">
        $(function () {
            RKfuncion.select2.init();
            $('#select-search-team').select2()
            $('#select-search-project').select2()
            $('#fromDate').datetimepicker({
                format: 'Y-MM-DD',
                showClear: true
            });

            $('#toDate').datetimepicker({
                format: 'Y-MM-DD',
                showClear: true
            });
        })

        $(document).ready(function() {
            $('.btn-export-filter').click(function () {
                $('#export-OT-list').submit();
            });
        });
    </script>
@endsection

