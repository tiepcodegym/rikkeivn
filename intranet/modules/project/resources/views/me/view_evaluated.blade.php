@extends('layouts.default')

@section('title', trans('project::me.Monthly Evaluation') . ' - ' . trans('project::me.Evaluated'))
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

$evalTable = MeEvaluation::getTableName();
$request = request();
$filterMonth = request()->get('eval_time') ? request()->get('eval_time') : FormView::getFilterData('month' , 'eval_time');
if (!$filterMonth) {
    $filterMonth = Carbon::now()->subMonthNoOverflow()->format('Y-m');
}
$filterProjectId = FormView::getFilterData('number', $evalTable.'.project_id');
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
                    @if (!$projsNotEval->isEmpty() && $filterMonth != '_all_')
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#notEvaluate">
                      {{ trans('project::me.Not Evaluate', ['month' => $filterMonth]) }}
                    </button>

                    <span class="margin-left-40">{{$totalMember}} {{ trans('project::me.persons evaluated') }}</span>
                    @endif
                    <span @if(!$projsNotEval->isEmpty()) class="margin-left-40" @endif>{!! trans('team::view.Total :itemTotal entries / :pagerTotal page', [
                        'itemTotal' => $collectionModel->total(),
                        'pagerTotal' => $collectionModel->lastPage(),
                        ]) !!}</span>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 text-right">
                @include('team::include.filter')
                <a target="_blank" href="{{ route('project::project.eval.help') }}" class="btn btn-primary">{{ trans('project::me.Help') }}</a>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    
    <div class="pdh-10">
        <div class="table-responsive _me_table_responsive">
            <table id="_me_table" require-comment="1" class="table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle" style="border-left: 1px solid #ccc;">

                <thead>
                    <tr>
                        <th class="minw-50 sorting {{ Config::getDirClass('employee_code') }} col-name" data-order="employee_code" data-dir="{{ Config::getDirOrder('employee_code') }}">ID</th>
                        <th class="sorting {{ Config::getDirClass('eval_time') }} col-name" data-order="eval_time" data-dir="{{ Config::getDirOrder('eval_time') }}">{{trans('project::me.Month')}}</th>
                        <th class="sorting {{ Config::getDirClass('email') }} col-name" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{trans('project::me.Account')}}</th>
                        <th>{{trans('project::me.Project code')}}</th>
                        <th class="sorting {{ Config::getDirClass('avg_point') }} col-name" data-order="avg_point" data-dir="{{ Config::getDirOrder('avg_point') }}">
                            <span>{{trans('project::me.Contribution level')}}</span>
                        </th>
                        <th class="sorting {{ Config::getDirClass('status') }} col-name" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">
                            {{ trans('project::me.Status') }}
                        </th>
                    </tr>
                </thead>
                
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <select id="filter_months" class="form-control select-search select-grid filter-grid has-search" name="filter[month][eval_time]">
                                <?php
                                $prev_month = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();
                                ?>
                                <option value="_all_">{{trans('project::me.Select month')}}</option>
                                @if (!$filterMonths->isEmpty())
                                    @foreach ($filterMonths as $month)
                                        <?php 
                                        $selected = '';
                                        $ftEvalMonth = Carbon::parse($month->eval_time)->format('Y-m');
                                        if (($filterMonth != '_all_') && 
                                                (($request->has('time') && $ftEvalMonth == $request->get('time')) || 
                                                ($ftEvalMonth == $filterMonth) ||
                                                ($ftEvalMonth == $prev_month && !$filterMonth && !$request->has('time')))) {
                                            $selected = 'selected';
                                        }
                                        ?>
                                        <option value="{{ $ftEvalMonth }}" {{ $selected }}>{{ $ftEvalMonth }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td>
                            <?php
                            $filterEmployee = FormView::getFilterData('number', $evalTable.'.employee_id');
                            ?>
                            <select data-employee="{{$filterEmployee}}" id="filter_employee" class="form-control select-search select-grid filter-grid has-search" name="filter[number][{{$evalTable}}.employee_id]">
                                <option value="">{{trans('project::me.Select employee')}}</option>
                            </select>
                        </td>
                        <td>
                            <select id="filter_projects" class="form-control select-search select-grid filter-grid has-search" name="filter[number][{{$evalTable}}.project_id]">
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
                        <td></td>
                        <td>
                            <select class="form-control select-search select-grid filter-grid" name="filter[{{ $evalTable }}.status]">
                                <option value="">--{{trans('project::me.Status')}}--</option>
                                <?php $statuses = MeEvaluation::filterStatus(); ?>
                                @foreach ($statuses as $key => $status)
                                <?php 
                                $selected = '';
                                if ($key == FormView::getFilterData($evalTable.'.status')) {
                                    $selected = 'selected';
                                }
                                ?>
                                <option value="{{$key}}" {{ $selected }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @if (!$collectionModel->isEmpty())
                        @foreach($collectionModel as $item)
                        <?php
                        $projectPoint = $item->proj_point;
                        ?>

                        <tr data-eval="{{$item->id}}" data-project="{{$item->project_id}}" data-email="{{$item->email}}" data-time="{{$item->eval_time}}">
                            <td class="_break_word">{{$item->employee_code}}</td>
                            <td class="_nowwrap">{{$item->eval_time->format('Y-m')}}</td>
                            <td class="_break_word employee">{{ ucfirst(preg_replace('/@.*/', '', $item->email)) }}</td>
                            @if ($item->project_id)
                            <td><a href="{{ route('project::point.edit', ['id' => $item->project_id]) }}" target="_blank" class="project_code_auto">{{ $item->project_name }}</a></td>
                            @else 
                            <td>Team: {{ $item->team_name }}</td>
                            @endif
                            <td class="_break_word">{{ $item->contribute_label }}</td>
                            <td class="_break_word">{{ $item->status_label }}</td>
                        </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="6"><h4 class="text-center">{{ trans('project::me.No result') }}</h4></td>
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
   
    @if (!$projsNotEval->isEmpty())
    <!-- Modal not evaluate -->
    <div class="modal fade" id="notEvaluate" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{{ trans('project::me.Not Evaluate', ['month' => $evalMonth]) }}</h4>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="edit-table table table-bordered table-condensed dataTable" id="table-not-eval">
                            <thead>
                                <tr>
                                    <th class="width-20-per-im">{{ trans('project::me.Project code') }}</th>
                                    <th class="width-30-per-im">{{ trans('project::me.Project') }}</th>
                                    <th class="width-50-per-im">{{ trans('project::me.Member') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($projsNotEval as $proj)
                                <?php 
                                    $pmName = '';
                                    if ($proj->email) {
                                        $pmName = ucfirst(preg_replace('/@.*/', '', $proj->email));
                                    }
                                    $teamName = $proj->team_names;
                                    $memberOfProject = $proj->employees ? explode(',', $proj->employees) : [];
                                ?>
                                <tr>
                                    <td><a href="{{ route('project::point.edit', ['id' => $proj->id]) }}" target="_blank">{{$proj->project_code_auto}}</a></td>
                                    <td>
                                        <p>{{trans('project::view.Project Name')}} : {{$proj->name}}</p>
                                        @if($pmName)
                                        <p>{{trans('project::view.Project Manager')}}: <span class="text-uppercase">{{$pmName}}</span></p>
                                        @endif
                                        <p>{{trans('project::view.Group')}}: {{$teamName}}</p>
                                    </td>
                                    <td>
                                        <div class="box box-default collapsed-box box-solid">
                                            <div class="box-header with-border">
                                                <h3 class="box-title margin-right-30">{{$proj->name}} @if($pmName) - <span class="text-uppercase">{{$pmName}}</span>@endif</h3>
                                                @if(count($memberOfProject) > 0)
                                                <div class="box-tools pull-right">
                                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                                @endif
                                                <!-- /.box-tools -->
                                            </div>
                                            @if(count($memberOfProject) > 0)
                                                <!-- /.box-header -->
                                                <div class="box-body" style="display: none;">
                                                    <ul>
                                                        @foreach($memberOfProject as $member)
                                                        <li class="member-not-eval">{{$member}}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

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

