@extends('layouts.default')

@section('title')
{{ $titlePage }}
@endsection

@section('css')
<?php use Rikkei\Core\View\CoreUrl; ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/report.css') }}">
<style>
    .grid-pager .pagination {
        display: none;
    }
</style>
@endsection

@section('content')
<?php
use Carbon\Carbon;
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Project\Model\Task;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\ProjRewardMeta;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\TeamList;
use Rikkei\Project\Model\ProjReward;
use Rikkei\Team\View\Permission;

$tableTask = Task::getTableName();
$tableProject = Project::getTableName();
$tableEmployee = Employee::getTableName();
$tableRewardMeta = ProjRewardMeta::getTableName();
$tableTeam = Team::getTableName();

$teamList = TeamList::toOption(null, false, false);
$teamFilter = CoreForm::getFilterData('exception', 'team_id');
$statusFilter = CoreForm::getFilterData('status', $tableTask.'.status');
$monthFilter = CoreForm::getFilterData('exception', 'month');
?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-7">
                        <div class="row form-group">
                            <label class="col-sm-2 col-md-1 margin-top-5">{{ trans('project::view.Month') }}</label>
                            <div class="col-sm-10 col-md-11">
                                <input type="text" style="width: 200px;" name="filter[exception][month]" autocomplete="off"
                                       @if ($monthFilter)
                                        value="{{ $monthFilter }}"
                                       @else
                                        value="{{ $lastMonthApprove }}"
                                       @endif
                                       id="rw_time_filter" 
                                       class="form-control date-picker filter-grid form-inline">
                                <span class="btn-sets-box"><i>({{ trans('project::view.Select Month to export excel file') }})</i></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 text-right">
<!--                        <button type="button" id="btn_export_excel" class="btn btn-info" data-url="{{ route('project::report.reward.export') }}">
                            <i class="fa fa-file-excel-o "></i> {{ trans('project::view.Export excel') }} <i class="fa fa-spin fa-refresh hidden"></i>
                        </button>-->
                        <button type="button" id="btn_export_base_osdc" class="btn btn-info" data-url="{{ route('project::report.reward_osdc_base.export') }}">
                            <i class="fa fa-file-excel-o "></i> {{ trans('project::view.Export excel') }} <i class="fa fa-spin fa-refresh hidden"></i>
                        </button>
                        @include('team::include.filter', ['domainTrans' => 'project'])
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data tbl-reward-report">
                    <thead>
                        <tr>
                            <th class="col-id width-10" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                            <th class="col-id width-5" style="width: 20px;">&nbsp;</th>
                            <th class="sorting {{ TeamConfig::getDirClass('name') }} col-name" data-order="name" data-dir="{{ TeamConfig::getDirOrder('name') }}">{{ trans('project::view.Project name') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('email') }} col-email" data-order="email" data-dir="{{ TeamConfig::getDirOrder('email') }}">{{ trans('project::view.PM') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('project_type') }} col-project_type" data-order="project_type" data-dir="{{ TeamConfig::getDirOrder('project_type') }}">{{ trans('project::view.Type') }}</th>
                            <th style="width: 120px" class="sorting {{ TeamConfig::getDirClass('team_name') }} col-team_name" data-order="team_name" data-dir="{{ TeamConfig::getDirOrder('team_name') }}">{{ trans('project::view.Group') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('status') }} col-status" data-order="status" data-dir="{{ TeamConfig::getDirOrder('status') }}">{{ trans('project::view.Status') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('billable') }} col-billable" data-order="billable" data-dir="{{ TeamConfig::getDirOrder('billable') }}">{{ trans('project::view.Billable effort') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('reward_budget') }} col-reward_budget" data-order="reward_budget" data-dir="{{ TeamConfig::getDirOrder('reward_budget') }}">{{ trans('project::view.Budget') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('sum_reward_approve') }} col-sum_reward_approve" data-order="sum_reward_approve" data-dir="{{ TeamConfig::getDirOrder('sum_reward_approve') }}">{{ trans('project::view.Reward approve') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('bonus_money') }} col-bonus_money" data-order="bonus_money" data-dir="{{ TeamConfig::getDirOrder('bonus_money') }}">{{ trans('project::view.Payment status') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('approve_date') }} col-approve_date" data-order="approve_date" data-dir="{{ TeamConfig::getDirOrder('approve_date') }}">{{ trans('project::view.Approved Date') }}</th>
                            <th>{{ trans('project::view.Created month') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <input type="checkbox" id="input-export-reward-all" name="rewardIds[]">
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input id="rw_projectname_filter" type="text" name="filter[{{ $tableProject }}.name]" value="{{ CoreForm::getFilterData($tableProject.'.name') }}" placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input id="rw_employee_filter" type="text" name="filter[{{ $tableEmployee }}.email]" value="{{ CoreForm::getFilterData($tableEmployee.'.email') }}" placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php $filterProjType = CoreForm::getFilterData('exception', 'project_type'); ?>
                                        <select id="rw_type_filter" style="width: 100px" name="filter[exception][project_type]" class="form-control select-grid filter-grid select-search">
                                            <option value="">&nbsp;</option>
                                            @foreach ($projectTypeAll as $value => $label)
                                            <option value="{{ $value }}" {{ $filterProjType == $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select id="rw_team_filter" style="width: 150px" name="filter[exception][team_id]" 
                                            class="form-control select-grid filter-grid select-search has-search" data-team="dev">
                                            <option value="">&nbsp;</option>
                                            @foreach ($teamList as $teamOpt)
                                            <option value="{{ $teamOpt['value'] }}" {{ $teamFilter == $teamOpt['value'] ? 'selected' : '' }}>{{ $teamOpt['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select style="width: 120px" class="form-control select-grid filter-grid select-search" name="filter[status][{{ $tableTask }}.status]">
                                            <option value="">&nbsp;</option>
                                            @foreach($taskStatusAll as $key => $value)
                                                <option value="{{ $key }}" {{ $statusFilter == $key ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <?php
                                $paidStatusFilter = CoreForm::getFilterData('exception', 'pay_status');
                                if ($paidStatusFilter === null) {
                                    $paidStatusFilter = ProjReward::STATE_UNPAID;
                                }
                                if ($paidStatusFilter !== null && $paidStatusFilter !== '_all_') {
                                    $paidStatusFilter = intval($paidStatusFilter);
                                }
                                ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select style="width: 100px" name="filter[exception][pay_status]"
                                                class="form-control select-grid filter-grid select-search"
                                                id="rw_status_paid_filter">
                                            <option value="_all_">&nbsp;</option>
                                            @foreach ($listPaidStatus as $value => $label)
                                            <option value="{{ $value }}" {{ $value === $paidStatusFilter ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                @include('project::reward.report-item')
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    @if ($collectionModel->hasMorePages())
                    <tfoot>
                        <tr>
                            <td colspan="13" class="text-center">
                                <h4>
                                    <a href="#" type="button" id="btn_loadmore_reward" data-url="{{ $collectionModel->nextPageUrl() }}">
                                        {{ trans('project::view.load_more_vi') }} <i class="fa fa-spin fa-refresh icon-loading hidden"></i>
                                    </a>
                                </h4>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            <div class="box-body">
                @include('team::include.pager', ['domainTrans' => 'project'])
            </div>
        </div>
    </div>
</div>
<div id="tbl_template" class="hidden"></div>
@endsection

@section('script')
<script>
    var textRequiredTeamAndTime = "{{ trans('project::message.You must select Month') }}";
    var filterMonthFormat = "{{ $monthFilter }}";
    var osdcBaseColumn = [{
         team_name: "{{ trans('project::view.Team') }}",
         emp_code: "{{ trans('project::view.ID Employee') }}",
         emp_name: "{{ trans('project::view.Name Employee') }}",
         total_reward: "{{ trans('project::view.Reward Level (k)') }}",
         reason: "{{ trans('project::view.Reward Reason') }}",
         comment: "{{ trans('project::view.Comment') }}",
         total: "{{ trans('project::view.Reward Total') }}",
    }];
    var TYPE_OSDC = {{ Project::TYPE_OSDC }};
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.3/jquery.number.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.3/jquery.number.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
<script type="text/javascript" src="{{ asset('lib/xlsx/jszip.js') }}"></script>
<script type="text/javascript" src="{{ asset('lib/xlsx/xlsx.js') }}"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
<script src="{{ CoreUrl::asset('project/js/me_reward.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        RKfuncion.select2.init();
        
        $('.date-picker').each(function () {
            $(this).datepicker({
                minViewMode: 1,
                format: 'mm-yyyy',
                autoclose: true,
                todayHighlight: true
            }).change(function () {
                $('.btn-search-filter').trigger('click');
            });
        });
    });
</script>
@endsection


