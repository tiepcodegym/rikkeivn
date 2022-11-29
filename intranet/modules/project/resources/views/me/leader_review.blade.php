@extends('layouts.default')

@section('title', trans('project::me.Monthly Evaluation'))
@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
@endsection

@section('content')

<?php
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Project\Model\Project;

$evalTable = MeEvaluation::getTableName();
$filterProjectType = FormView::getFilterData('excerpt', 'proj_type');
$filterStatus = FormView::getFilterData($evalTable.'.status');
$filterAvgPoint = FormView::getFilterData('excerpt', 'avg_point');
$arrayTypeLabel = Project::labelTypeProject();
$timeSepMonth = \Carbon\Carbon::parse(config('project.me_sep_month'));
?>

<div id="overlay"><i class="fa fa-spin fa-refresh iloading"></i></div>

<div class="box box-info _me_review_page">
    <div class="box-body">
        <div class="row">
            <div class="col-md-8 col-lg-9">
                <div class="form-inline select-media box-action mgr-35">
                    <select id="filter_teams" class="form-control select-search select-grid filter-grid has-search" name="filter[team_filter][team_id]">
                        <option value="">{{ trans('project::me.Select project team') }}</option>
                        @if (count($filterTeams) > 0)
                        @foreach ($filterTeams as $team)
                        <option value="{{ $team['value'] }}" {{ $team['value'] == FormView::getFilterData('team_filter', 'team_id') ? 'selected' : '' }}>{{ $team['label'] }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-inline select-media box-action mgr-35">
                    <select id="filter_teams" class="form-control select-search select-grid filter-grid has-search" name="filter[team_filter][team_member]">
                        <option value="">{{ trans('project::me.Select team member') }}</option>
                        @if (count($filterTeams) > 0)
                        @foreach ($filterTeams as $team)
                        <option value="{{ $team['value'] }}" {{ $team['value'] == FormView::getFilterData('team_filter', 'team_member') ? 'selected' : '' }}>{{ $team['label'] }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                @if ($hasPermissCreateTeam && false)
                <div class="form-inline select-media box-action mgr-35">
                    <select id="filter_teams" class="form-control select-search select-grid filter-grid has-search" name="filter[number][{{ $evalTable }}.team_id]">
                        <option value="">{{ trans('project::me.Select evaluation team') }}</option>
                        @if (count($filterTeams) > 0)
                        @foreach ($filterTeams as $team)
                        <option value="{{ $team['value'] }}" {{ $team['value'] == FormView::getFilterData('number', $evalTable.'.team_id') ? 'selected' : '' }}>{{ $team['label'] }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                @endif
                
                <div class="form-inline hidden" id="proj_not_eval_box">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#notEvaluate">
                      {{ trans('project::me.Not Evaluate', ['month' => $filterMonth]) }}
                    </button>
                    <strong class="margin-left-20"><span id="total_member"></span> {{ trans('project::me.persons evaluated') }}</strong>
                </div>
                <?php echo \Rikkei\Project\View\MeView::renderNewVerLink(route('me::review.list', ['month' => $timeSepMonth->addMonthNoOverflow()->format('m-Y')])); ?>
            </div>
            <div class="col-md-4 col-lg-3 text-right">
                @include('team::include.filter')
                <a target="_blank" href="{{ route('project::project.eval.help') }}" class="btn btn-primary">{{ trans('project::me.Help') }}</a>
                <div class="text-right"><i>{{ trans('project::me.Right click to comment') }}</i></div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div id="head_statistic" class="hide-filter text-center hidden"></div>
    </div>
    
    <div id="top_fixed_head">
        
    </div>
    <div class="pdh-10">
        <div class="table-responsive _me_table_responsive fixed-table-container">
            <table id="_me_table" require-comment="1" class="fixed-table table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle">

                @include('project::me.template.thead', ['checkbox' => true, 'has_month' => true, 'action_col' => true, 'sort_contri' => true, 'is_leader_view' => 'true'])

                <tbody>
                    <tr class="tr-filter">
                        <td class="fixed-col"></td>
                        <td class="fixed-col"></td>
                        <td class="td_filter_months fixed-col">
                            <input type="text" name="filter[excerpt][month]" value="{{ $filterMonth }}"
                                   class="form-control filter-grid month-picker" placeholder="Y-m"
                                   style="min-width: 75px;" autocomplete="off">
                        </td>
                        <td class="td_filter_employees fixed-col">
                            <select @if($filterEmployee) data-employee="{{ $filterEmployee }}" @endif
                                     class="form-control select-grid filter-grid select-search" name="filter[number][{{ $evalTable }}.employee_id]"
                                     data-url="{{ route('team::employee.list.search.ajax') }}"
                                     data-placeholder="{{ trans('project::me.Select employee') }}"
                                     style="min-width: 130px;">
                                <option value="">{{ trans('project::me.Select employee') }}</option>
                                @if ($filterEmployeeName)
                                <option value="{{ $filterEmployee }}" selected>{{ $filterEmployeeName }}</option>
                                @endif
                            </select>
                        </td>
                        <td>
                            <select id="filter_projects" class="form-control select-search select-grid filter-grid" name="filter[excerpt][{{$evalTable}}.project_id]"
                                    data-url="{{ route('project::me.search.project.team.ajax') }}"
                                    data-placeholder="{{ trans('project::me.Select project') }}"
                                    style="min-width: 160px;">
                                <option value="">{{trans('project::me.Select project')}}</option>
                                @if ($filterProjectName)
                                <option value="{{ $filterProjectId }}" selected>{{ $filterProjectName }}</option>
                                @endif
                            </select>
                        </td>
                        <td>
                            <select id="filter_project_types" class="form-control select-search select-grid filter-grid" name="filter[excerpt][proj_type]"
                                    style="min-width: 110px;">
                                <option value="">{{ trans('project::me.Selection') }}</option>
                                <option value="_team_" {{ '_team_' == $filterProjectType ? 'selected' : '' }}>Team</option>
                                @foreach ($arrayTypeLabel as $value => $label)
                                <option value="{{ $value }}" {{ $value == $filterProjectType ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        @for($t = 0; $t < ($normalAttrs->count() + $performAttrs->count()); $t++)
                        <td></td>
                        @endfor
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <select class="form-control select-search select-grid filter-grid" name="filter[excerpt][avg_point]" style="min-width: 140px;">
                                <option value="">--{{trans('project::me.Contribution level')}}--</option>
                                <?php $contributes = MeEvaluation::filterContributes(); ?>
                                @foreach ($contributes as $key => $contri)
                                <option value="{{$key}}" {{ $key == $filterAvgPoint ? 'selected' : '' }}>{{ $contri }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td></td>
                        <td>
                            @if(!isset($notShowFilterStatus))
                                <select class="form-control select-search select-grid filter-grid" name="filter[{{ $evalTable }}.status]" style="min-width: 110px;">
                                    <option value="">--{{trans('project::me.Status')}}--</option>
                                    <?php $statuses = MeEvaluation::filterStatus(); ?>
                                    @foreach ($statuses as $key => $status)
                                    <option value="{{$key}}" {{ $key == $filterStatus ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="{{ $normalAttrs->count() + $performAttrs->count() + 14 }}" class="text-center">
                            <i class="fa fa-spin fa-refresh"></i>
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>
    </div>
    <div class="cleafix"></div>

    <div class="box-body text-center hide-filter" id="footer_statistic">
        <span class="per_gr"><strong>{{ trans('project::me.Total') }}:</strong> <span class="val-total"></span></span>
        <span class="per_gr"><strong>{{ trans('project::me.Excellent') }}:</strong> <span class="val-excellent"></span></span>
        <span class="per_gr"><strong>{{ trans('project::me.Good') }}:</strong> <span class="val-good"></span></span>
        <span class="per_gr"><strong>{{ trans('project::me.Fair') }}:</strong> <span class="val-fair"></span></span>
        <span class="per_gr"><strong>{{ trans('project::me.Satisfactory') }}:</strong> <span class="val-satis"></span></span>
        <span class="per_gr"><strong>{{ trans('project::me.Unsatisfactory') }}:</strong> <span class="val-unsatis"></span></span> 
    </div>

    <div class="box-body text-right hide-filter">
        <form class="no-validate form-inline _actions_form" action="{{ route('project::project.eval.multi_actions') }}">
            <input type="hidden" name="action" value="{{ MeEvaluation::STT_FEEDBACK }}">
            <button type="submit" class="btn-delete delete-confirm btn_form_feedback" disabled="" data-noti="{{trans('project::me.Are you sure you want to do this action', ['action' => trans('project::me.Feedback')])}}" data-warning="{{trans('project::me.You must comment before feedback')}}" data-mesasge="{{trans('project::me.You must comment employee')}}" data-message2="{{trans('project::me.before feedback')}}">
                {{ trans('project::me.Feedback') }}
            </button>
        </form>
        <form class="no-validate form-inline _actions_form" action="{{ route('project::project.eval.multi_actions') }}">
            <input type="hidden" name="action" value="{{ MeEvaluation::STT_APPROVED }}">
            <button type="submit" class="btn btn-primary delete-confirm btn_form_accept" disabled="" data-noti="{{trans('project::me.Are you sure you want to do this action', ['action' => trans('project::me.Accept')])}}">
                {{ trans('project::me.Approve') }}
            </button>
        </form>
    </div>
    
    <div class="box-body">
        <div id="collection_pager"></div>
    </div>
    
</div>

<!-- Modal not evaluate -->
<div class="modal fade" id="notEvaluate" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
           <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">{{ trans('project::me.Not Evaluate', ['month' => $filterMonth]) }}</h4>
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

<div class="box box-default collapsed-box box-solid hidden" id="proj_member_collapse">
    <div class="box-header with-border">
        <h3 class="box-title margin-right-30 proj-name"></h3>
        <div class="box-tools pull-right hidden">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
    </div>
    <div class="box-body members-list" style="display: none;"></div>
</div>

@endsection

@section('confirm_class', 'modal-warning')

@section('warn_confirn_class', 'modal-default')

@section('script')
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="{{ asset('lib/fixed-table/tableHeadFixer.js') }}"></script>
@include('project::me.template.script')
<script>
    var projPoinEditUrl = "{{ route('project::point.edit', ['id' => '']) }}";
    var textProjName = '<?php echo trans('project::view.Project Name') ?>';
    var textPmName = '<?php echo trans('project::view.Project Manager') ?>';
    var textViewGroup = '<?php echo trans('project::view.Group') ?>';

    var filterEmployee = '<?php echo $filterEmployee ?>';
    var urlStatistic = '<?php echo route("project::project.eval.review_statistic") ?>';
    newMeUrl = "{{ route('me::review.list') }}";
</script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/me_script.js') }}"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/review_script.js') }}"></script>
@endsection

