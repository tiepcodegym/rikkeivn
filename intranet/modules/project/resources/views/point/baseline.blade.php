@extends('layouts.default')

@section('title')
<?php
    echo 'Project Baseline All';
?>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
@endsection

<style>
    .multi-select-bst .btn-group>.btn:first-child {
        width: 180px;
    }
</style>

@section('content')
<?php
use Rikkei\Project\Model\Project;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\ProjPointBaseline;
use Rikkei\Team\View\TeamList;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\OptionCore;
use Rikkei\Project\View\GeneralProject;
use Carbon\Carbon;

$urlSubmitFilter = GeneralProject::getUrlFilterDb();
$teamsOptionAll = TeamList::toOption(null, true, false);
$teamFilter = Form::getFilterData('exception', 'team_id', $urlSubmitFilter);
$tableProject = Project::getTableName();
$tableEmployee = Employee::getTableName();
$optionYesNo = OptionCore::yesNo(true, true);
$viewDashboardasBaseline = false;
$nowTime = Carbon::now();
$labelState = Project::lablelState();
$tableProjPointBaseline = ProjPointBaseline::getTableName();

?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info filter-wrapper" data-url="{{ $urlSubmitFilter }}">
            <div class="box-body filter-mobile-left">
                @include('team::include.filter', ['domainTrans' => 'project'])
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th dataTable-project">
                    <thead>
                        <tr>
                            <th class="sorting {{ Config::getDirClass('name', $urlSubmitFilter) }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name', $urlSubmitFilter) }}">{{ trans('project::view.Name') }}</th>
                            <th style="width: 120px;" class="sorting {{ Config::getDirClass('name_team', $urlSubmitFilter) }}" data-order="name_team" data-dir="{{ Config::getDirOrder('name_team', $urlSubmitFilter) }}">Team</th>
                            <th style="width: 120px;" class="sorting {{ Config::getDirClass('email', $urlSubmitFilter) }}" data-order="email" data-dir="{{ Config::getDirOrder('email', $urlSubmitFilter) }}">PM</th>
                            <th class="sorting {{ Config::getDirClass('summary', $urlSubmitFilter) }} col-summary width-110" data-order="summary" data-dir="{{ Config::getDirOrder('summary', $urlSubmitFilter) }}">{{ trans('project::view.Summary') }}</th>
                            <th class="sorting {{ Config::getDirClass('cost', $urlSubmitFilter) }} width-90" data-order="cost" data-dir="{{ Config::getDirOrder('cost', $urlSubmitFilter) }}">{{ trans('project::view.Cost') }}</th>
                            <th class="sorting {{ Config::getDirClass('quality', $urlSubmitFilter) }} width-90" data-order="quality" data-dir="{{ Config::getDirOrder('quality', $urlSubmitFilter) }}">{{ trans('project::view.Quality') }}</th>
                            <th class="sorting {{ Config::getDirClass('tl', $urlSubmitFilter) }} width-90" data-order="tl" data-dir="{{ Config::getDirOrder('tl', $urlSubmitFilter) }}">{{ trans('project::view.Timeliness') }}</th>
                            <th class="sorting {{ Config::getDirClass('proc', $urlSubmitFilter) }} width-90" data-order="proc" data-dir="{{ Config::getDirOrder('proc', $urlSubmitFilter) }}">{{ trans('project::view.Process') }}</th>
                            <th class="sorting {{ Config::getDirClass('css', $urlSubmitFilter) }} width-90" data-order="css" data-dir="{{ Config::getDirOrder('css', $urlSubmitFilter) }}">{{ trans('project::view.Css') }}</th>
                            <th class="col-point sorting {{ Config::getDirClass('point_total', $urlSubmitFilter) }} width-90" data-order="point_total" data-dir="{{ Config::getDirOrder('point_total', $urlSubmitFilter) }}">{{ trans('project::view.Point') }}</th>
                            <th class="col-status">{{ trans('project::view.Status') }}</th>
                            <th style="width: 60px;" class="col-type">{{ trans('project::view.Type') }}</th>
                            <th class="width-90">{{ trans('project::view.Date created') }}</th>
                            <th style="width: 85px;">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tableProject }}.name]" value="{{ Form::getFilterData("{$tableProject}.name", null, $urlSubmitFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select style="width: 150px" name="filter[exception][team_id]" class="form-control select-grid filter-grid select-search">
                                            <option value="">&nbsp;</option>
                                            @foreach($teamsOptionAll as $option)
                                                <option value="{{ $option['value'] }}"<?php
                                                    if ($option['value'] == $teamFilter): ?> selected<?php endif; 
                                                        ?>>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tableEmployee }}.email]" value="{{ Form::getFilterData("{$tableEmployee}.email", null, $urlSubmitFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                    <select class="form-control select-grid filter-grid select-search width-110" name="filter[number][{{ $tableProject }}.state]" id="state">
                                        <option value="">&nbsp;</option>
                                        @foreach($status as $key => $value)
                                            <option value="{{$key}}" {{ Form::getFilterData('number', "{$tableProject}.state", $urlSubmitFilter) == $key ? 'selected' : '' }}>{{$value}}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12 filter-multi-select">
                                        <?php $filterType = (array) Form::getFilterData('in', "{$tableProject}.type", $urlSubmitFilter);?>
                                        <select class="form-control multi-select-bst filter-grid hidden"
                                                name="filter[in][{{ $tableProject }}.type][]" id="type" multiple="multiple">
                                            @foreach($types as $key => $value)
                                                <option value="{{ $key }}" {{ in_array($key, $filterType) ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                             <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tableProjPointBaseline }}.created_at]" value="{{ Form::getFilterData("{$tableProjPointBaseline}.created_at", null, $urlSubmitFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            @foreach($collectionModel as $item)
                                <?php
                                $itemRaise = $item->raise;
                                $projs = new Project();
                                ?>
                                <tr>
                                    <td>
                                        <a href="{{ route('project::point.baseline.detail', ['id' => $item->id ]) }}">{{ $item->name }}</a>
                                    </td>
                                    <td class="col-hover-tooltip">{{ $item->name_team }}
                                        <div class="hidden">
                                            <div class="tooltip-content">
                                                <p>
                                                    {{ trans('project::view.Customer') . ': '. $item->customer_name }}
                                                    @if ($item->customer_name_jp)
                                                        ({{ $item->customer_name_jp }})
                                                    @endif
                                                </p>
                                                <p>
                                                    {{ trans('project::view.Company') . ': ' . $item->company_name }}
                                                    @if ($item->company_name_ja)
                                                        ({{ $item->company_name_ja }})
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-uppercase">{{ preg_replace('/@.*/', '',$item->email) }}</td>
                                    <td class="align-center middle tr-td-not-click task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE }}">
                                        <span class="point-color summary-point">
                                            <img src="{{ $allColorStatus[$item->summary] }}" />
                                        </span>
                                    </td>
                                    <td class="align-center middle task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_COST }}">
                                        <span class="point-color cost-point">
                                            @if ($viewBaseline && !$item->first_report && !$projs->isTypeTrainingOfRD($item->type))
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, 0, Project::TYPE_TRAINING)}}" />
                                            @else
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, $item->cost, $item->type)}}" />
                                            @endif
                                        </span>
                                    </td>
                                    <td class="align-center middle task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_QUA }}">
                                        <span class="point-color quality-point">
                                            @if ($viewBaseline && !$item->first_report && !$projs->isTypeTrainingOfRD($item->type))
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, 0, Project::TYPE_TRAINING)}}" />
                                            @else
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, $item->quality, $item->type)}}" />
                                            @endif
                                        </span>
                                    </td>
                                    <td class="align-center middle task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_TL }}">
                                        <span class="point-color timeliness-point">
                                            @if ($viewBaseline && !$item->first_report && !$projs->isTypeTrainingOfRD($item->type))
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, 0, Project::TYPE_TRAINING)}}" />
                                            @else
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, $item->tl, $item->type)}}" />
                                            @endif
                                        </span>
                                    </td>
                                    <td class="align-center middle task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_PROC }}">
                                        <span class="point-color process-point">
                                            @if ($viewBaseline && !$item->first_report && !$projs->isTypeTrainingOfRD($item->type))
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, 0, Project::TYPE_TRAINING)}}" />
                                            @else
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, $item->proc, $item->type)}}" />
                                            @endif
                                        </span>
                                    </td>
                                    <td class="align-center middle task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_CSS}}">
                                        <span class="point-color css-point">
                                            @if ($viewBaseline && !$item->first_report && !$projs->isTypeTrainingOfRD($item->type))
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, 0, Project::TYPE_TRAINING)}}" />
                                            @else
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, $item->css, $item->type)}}" />
                                            @endif
                                        </span>
                                    </td>
                                    <td class="align-center task-tooltip" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_SUMMARY }}">
                                        {{ $item->point_total }}
                                    </td>
                                    <td>{{ Project::getLabelState($item->state, $labelState) }}</td>
                                    <td>{{ Project::getLabelState($item->type) }}</td>
                                    <td>{{ $item->created_at->format("Y-m").'-'.trans('project::view.weeks').' '.$item->created_at->format("W") }}</td>
                                    <td class="align-center">
                                        <a href="{{ route('project::point.baseline.detail', ['id' => $item->id ]) }}" class="btn-edit" title="{{ trans('project::view.View baseline') }}" data-toggle="tooltip">
                                            <i class="fa fa-info-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="13" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="box-body">
                @include('team::include.pager', ['domainTrans' => 'project', 'urlSubmitFilter' => $urlSubmitFilter])
            </div>
        </div>
    </div>
</div>
<div class="task-list-popup-wraper">
    <div class="modal fade task-list-modal" role="dialog" data-id="0" data-type="0">
        <div class="modal-dialog" role="document">
            <div class="modal-content grid-data-query" data-url="{{ URL::route('project::task.list.ajax', ['id' => 0]) }}">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h3 class="task-list-title">
                        <span class="task-list-title-text">{{ trans('project::view.Task list') }}</span>
                        <span class="task-list-title-text hidden" data-type="{{ Task::TYPE_ISSUE_COST }}">{{ trans('project::view.Task cost list') }}</span>
                        <span class="task-list-title-text hidden" data-type="{{ Task::TYPE_ISSUE_QUA }}">{{ trans('project::view.Task quality list') }}</span>
                        <span class="task-list-title-text hidden" data-type="{{ Task::TYPE_ISSUE_TL }}">{{ trans('project::view.Task timeliness list') }}</span>
                        <span class="task-list-title-text hidden" data-type="{{ Task::TYPE_ISSUE_PROC }}">{{ trans('project::view.Task process list') }}</span>
                        <span class="task-list-title-text hidden" data-type="{{ Task::TYPE_ISSUE_CSS }}">{{ trans('project::view.Task css list') }}</span>
                        &nbsp; <i class="fa fa-spin fa-refresh hidden"></i>
                        <a class="btn-add btn-add-task" target="_blank" href="#" data-url="{{ URL::route('project::task.add', ['id' => 0]) }}">
                            <i class="fa fa-plus"></i>
                        </a>
                    </h3>
                </div>
                <div class="modal-body">
                    <div class="grid-data-query-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        selectSearchReload();
        $('#type').multiselect({
            numberDisplayed: 2,
            nonSelectedText: '----',
            allSelectedText: '{{ trans('project::view.All') }}',
            onDropdownHide: function(event) {
                RKfuncion.filterGrid.filterRequest(this.$select);
            }
        });
    });
</script>
@endsection
