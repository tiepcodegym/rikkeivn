@extends('layouts.default')

@section('title', trans('project::me.Monthly Evaluation') . ' - ' . trans('project::me.Not.Evaluate'))
@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
@endsection

@section('content')

<?php
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Core\View\Form as FormView;
use Carbon\Carbon;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\Team;
use Rikkei\Project\Model\Project;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Team\View\Config;

$filterMonthStart = request()->get('start_at') ? request()->get('start_at') : FormView::getFilterData('month', 'start_at');
$currMonth = null;
if (!$filterMonthStart) {
    $currMonth = Carbon::now()->subMonthNoOverflow()->format('Y-m');
}
$rqTeam = request()->get('teams') ? request()->get('teams') : FormView::getFilterData('team_filter', 'team_id');
?>

<div class="box box-info _me_review_page">
    <div class="box-body">
        <div class="row">
            <div class="col-md-8 col-lg-9">
                <div class="form-inline select-media box-action mgr-35">
                    <select id="filter_teams" class="form-control select-search select-grid filter-grid has-search" name="filter[team_filter][team_id]">
                        <option value="0">{{ trans('project::me.Select team') }}</option>
                        @if (count($filterTeams) > 0)
                        @foreach ($filterTeams as $team)
                        <option value="{{ $team['value'] }}" {{ $team['value'] == $rqTeam ? 'selected' : '' }}>{{ $team['label'] }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-inline">
                    <label>
                        @if ($currMonth)
                        {{ trans('project::me.Month') }}: {{ $currMonth }} <i>({{ trans('project::me.Time between Start at and End at') }})</i>
                        @else
                        {{ trans('me::view.All month') }}
                        @endif
                    </label>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 text-right">
                @include('team::include.filter')
                <a target="_blank" href="{{ route('project::project.eval.help') }}" class="btn btn-primary">{{ trans('project::me.Help') }}</a>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-md-9 col-sm-8">
                <span>
                    {!! trans('team::view.Total :itemTotal entries / :pagerTotal page', [
                    'itemTotal' => $collectionModel->total(),
                    'pagerTotal' => $collectionModel->lastPage(),
                    ]) !!}
                </span>
            </div>
        </div>
    </div>

    <div class="pdh-10">
        <div class="table-responsive _me_table_responsive">
            <table id="_me_table" require-comment="1" class="table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle" style="border-left: 1px solid #ccc;">

                <thead>
                    <tr>
                        <th class="minw-50 sorting {{ Config::getDirClass('employee_code') }} col-name" data-order="employee_code" data-dir="{{ Config::getDirOrder('employee_code') }}">ID</th>
                        <th class="sorting {{ Config::getDirClass('email') }} col-name" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('project::me.Account') }}</th>
                        <th class="sorting {{ Config::getDirClass('project_code_auto') }} col-name" data-order="project_code_auto" data-dir="{{ Config::getDirOrder('project_code_auto') }}">{{ trans('project::me.Project code') }}</th>
                        <th class="sorting {{ Config::getDirClass('start_at') }} col-name" data-order="start_at" data-dir="{{ Config::getDirOrder('start_at') }}">{{ trans('project::me.Start at') }}</th>
                        <th class="sorting {{ Config::getDirClass('end_at') }} col-name" data-order="end_at" data-dir="{{ Config::getDirOrder('end_at') }}">{{ trans('project::me.End at') }}</th>
                    </tr>
                </thead>
                
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <?php
                            $filterEmployee = FormView::getFilterData('number', 'emp.id');
                            ?>
                            <select id="filter_employee" class="form-control select-search select-grid filter-grid has-search" 
                                    name="filter[number][emp.id]" style="width: 160px;">
                                <option value="">{{trans('project::me.Select employee')}}</option>
                            </select>
                        </td>
                        <td>
                            <select id="filter_projects" 
                                    class="form-control select-search select-grid filter-grid has-search" 
                                    name="filter[number][projmb.project_id]" style="width: 260px;">
                                <?php $filterProjectId = FormView::getFilterData('number', 'projmb.project_id'); ?>
                                <option value="">{{trans('project::me.Select project')}}</option>
                                @if (!$filterProjects->isEmpty())
                                    @foreach($filterProjects as $proj)
                                        <?php 
                                        if (!$proj->id) {
                                            continue;
                                        }
                                        $selected = ''; 
                                        if ($proj->id == $filterProjectId) {
                                            $selected = 'selected';
                                        }
                                        ?>
                                        <option value="{{ $proj->id }}" {{ $selected }}>
                                            {{$proj->name}}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td>
                            <select id="filter_start_at" class="form-control select-search select-grid filter-grid has-search" 
                                    name="filter[month][start_at]" style="width: 160px;">
                                <option value="_all_">{{trans('project::me.Select month')}}</option>
                                @if (!$filterMonths->isEmpty())
                                    @foreach ($filterMonths as $month)
                                        <?php 
                                        $selected = '';   
                                        if ($filterMonthStart != '_all_' && $month->month_start_at == $filterMonthStart) {
                                            $selected = 'selected';
                                        }
                                        ?>
                                        <option value="{{$month->month_start_at}}" {{ $selected }}>
                                             {{ $month->month_start_at }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ trans('project::me.Time between Start at and End at') }}"></i>
                        </td>
                        <td>
                            <select id="filter_end_at" class="form-control select-search select-grid filter-grid has-search" 
                                    name="filter[month][end_at]" style="width: 160px;">
                                <?php $filterMonthEnd = request()->get('end_at') ? request()->get('end_at') : FormView::getFilterData('month', 'end_at'); ?>
                                <option value="">{{trans('project::me.Select month')}}</option>
                                @if (!$filterMonthsEnd->isEmpty())
                                    @foreach ($filterMonthsEnd as $month)
                                        <option value="{{$month->month_end_at}}" {{ $month->month_end_at == $filterMonthEnd ? 'selected' : '' }}>
                                             {{ $month->month_end_at }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                    </tr>
                    
                    @if (!$collectionModel->isEmpty())
                        @foreach($collectionModel as $item)
                        <?php
                        $projectPoint = $item->proj_point;
                        ?>

                        <tr>
                            <td class="_break_word">{{$item->employee_code}}</td>
                            <td class="_break_word employee">{{ ucfirst(preg_replace('/@.*/', '', $item->email)) }}</td>
                            @if ($item->project_id)
                            <td><a href="{{ route('project::point.edit', ['id' => $item->project_id]) }}" target="_blank" class="project_code_auto">{{ $item->project_name }}</a></td>
                            @else 
                            <td></td>
                            @endif
                            <td class="_nowwrap">{{ $item->start_at }}</td>
                            <td class="_nowwrap">{{ $item->end_at }}</td>
                        </tr>
                        @endforeach
                    
                    @else
                    <tr>
                        <td colspan="5"><h4 class="text-center">{{ trans('project::me.No result') }}</h4></td>
                    </tr>
                    @endif
                </tbody>
                
            </table>
        </div>
    </div>
    <div class="cleafix"></div>
    
    <div class="box-body">
        @include('team::include.pager')
    </div>
    
</div>
@endsection

@section('confirm_class', 'modal-warning')

@section('script')
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
@include('project::me.template.script')
<script>
    var filterEmployees = '<?php echo $filterEmployees; ?>';
    var filterEmployee = '<?php echo $filterEmployee; ?>';
    filterEmployees = JSON.parse(filterEmployees);
    if (filterEmployees.length > 0) {
        for (var i = 0; i < filterEmployees.length; i++) {
            var employee = filterEmployees[i];
            var selected = employee.employee_id == parseInt(filterEmployee) ? 'selected' : '';
            var empname = employee.email.split("@")[0];
            empname = empname[0].toUpperCase() + empname.slice(1);
            var option = '<option value="'+ employee.employee_id +'" '+ selected +'>'+ empname +'</option>';
            $('#filter_employee').append(option);
        }
    }
</script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/me_script.js') }}"></script>
@endsection

