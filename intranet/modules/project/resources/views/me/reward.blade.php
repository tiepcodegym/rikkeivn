<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Team\View\Config;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\MeReward;
use Carbon\Carbon;
use Rikkei\Me\View\View as MeView;
use Rikkei\Team\View\Permission;

$request = request();
//list config reward
$configRewards = MeView::listRewards($filterMonth);
$configRewardOnsite = MeView::listRewardsOnsite($filterMonth);
//filter team
$filterTeam = FormView::getFilterData('excerpt', 'team_id');
if (!$filterTeam) {
    $filterTeam = $defaultTeamId;
}
if ($request->has('team_id') && $request->get('team_id')) {
    $filterTeam = $request->get('team_id');
}

if ($filterMonth != '_all_') {
    $filterMonth = $filterMonth->toDateTimeString();
}
//filter status
$filterStatus = FormView::getFilterData('excerpt', 'reward_status');
if ($request->has('reward_status') && $request->get('reward_status')) {
    $filterStatus = $request->get('reward_status');
}
//status paid
$statusPaidLabels = MeView::rewardPaidLabels();

$filterHasSubmit = $filterTeam && $filterTeam != '_all_' && $filterMonth && $filterMonth != '_all_';
$hasChecked = true;
$permissUpdatePaid = Permission::getInstance()->isAllow('project::me.reward.update_paid');
$sttReward = \Rikkei\Project\Model\MeEvaluation::STT_REWARD;
?>

@extends('layouts.default')

<?php
$pageTitle = trans('project::me.OSDC Reward');
if ($isReview) {
    $pageTitle = trans('project::me.OSDC Reward Review');
}
?>
@section('title', $pageTitle)

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css">
<link rel="stylesheet" href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_team_style.css') }}" />
@endsection

@section('content')
    @include('contract::message-alert')
    <style>
        .bootstrap-datetimepicker-widget {
            z-index: 999999;
        }
    </style>

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-7">
                <div class="form-inline box-action select-media mgr-35">
                    <select id="rw_team_filter" class="form-control select-search has-search select-grid filter-grid" name="filter[excerpt][team_id]">
                        <option value="_all_">{{trans('project::me.Select team')}}</option>
                        @if ($teamList)
                            @foreach($teamList as $team)
                            <option value="{{ $team['value'] }}" {{ $filterTeam == $team['value'] ? 'selected' : '' }}>{{ $team['label'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="form-inline box-action select-media mgr-35">
                    <input id="rw_time_filter" type="text" name="filter[excerpt][eval_time]"
                           class="form-control input-datepicker filter-grid" data-format="YYYY-MM"
                           value="{{ $filterMonth }}" autocomplete="off">
                </div>

                @if ($filterMonth && $filterMonth != '_all_')
                    <?php
                    $filterMonthFormat = Carbon::parse($filterMonth)->format('Y-m');
                    ?>
                    @if (isset($listRangeMonths[$filterMonthFormat]))
                    <div class="form-inline box-action select-media mgr-35">
                        {{ 'Date from: ' .  $listRangeMonths[$filterMonthFormat]['start'] . ' to: ' . $listRangeMonths[$filterMonthFormat]['end'] }}
                    </div>
                    @endif
                @endif

                <div class="form-inline box-action select-media mgr-35">
                    <select id="rw_type_filter" class="form-control select-search has-search select-grid filter-grid" name="filter[excerpt][type]">
                        <option value="_all_">{{trans('project::me.Select type')}}</option>
                        @foreach($projectTypeLabels as $key => $value)
                            <option value="{{ $key }}" {{ $filterProjType == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="block-1200"><i>
                        @if ($isReview)
                            ({{ trans('project::me.Select Team, Month and Project type OSDC to approve reward') }})
                        @else
                        ({{ trans('project::me.Select Team, Month and Project type OSDC to submit reward') }})
                        @endif
                    </i></div>
            </div>
            <div class="col-md-5 text-right">
                @if(Route::is('project::me.reward.edit'))
                    <button type="button"
                            class="btn btn-success import-contract"
                            onclick="fc_show_model_upload_file()"
                            id="modal_contract_import_excel"
                            title="Dùng để import nhân viên chưa có trong bảng danh sách thưởng"
                            data-url="{!! URL::route('project::me.reward.import_excel') !!}">
                        {!! trans('project::view.Import') !!}
                        <i class="fa fa-upload"></i>

                    </button>
                @endif

                <button type="button" class="btn btn-success"
                        data-toggle="modal" data-target="#modal_export_me_reward">
                    {{ trans('me::view.Export excel') }} <i class="fa fa-download"></i>
                </button>


                @include('team::include.filter', ['domainTrans' => 'project'])
                <a target="_blank" href="{{ route('project::project.eval.help') }}" class="btn btn-primary">{{ trans('project::me.Help') }}</a>
            </div>
        </div>

        <!-- set up the modal to start hidden and fade in and out -->
        <div id="model-delete-confirm-contract" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- dialog body -->
                    <div class="modal-body">
                        <h3></h3>
                    </div>
                    <!-- dialog buttons -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-ok">OK</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- HTML to write -->

        <!-- Modal import -->

        <!-- set up the modal to start hidden and fade in and out -->
        <div id="model-import-excel" class="modal fade" data-backdrop="static" data-keyboard="false">
            <form name="frmMain" id="frmMain" enctype="multipart/form-data" method="POST" action="{{route('project::me.reward.import_excel')}}">
                {!! csrf_field() !!}
                <div class="modal-dialog">
                    <div class="modal-content">
                        <!-- dialog body -->
                        <div class="modal-body">
                            <input id="filterimport" type="hidden" name="filterimport" class="input-datepicker " data-format="YYYY-MM" value="{{ $filterMonth }}">
                            <h4>{{trans('contract::view.Select file to import')}} <em style="color:red">*</em></span> </h4>
                            <input type="file" name="fileToUpload" id="fileToUpload" accept=".xlsx, .xls">
                            <label style="display: none;color: red" id="error-import-excel">{{trans('contract::message.File import is not null')}}</label>
                            <br/>
                            {!!trans('project::view.help-import-excel')!!}
                        </div>
                        <div class="col-md-12">
                            <h4>
                                <a href="{{route('project::me.reward.downloadFormatFile')}}">{{ trans('contract::view.Format excel file') }}
                                    <i class="fa fa-download"></i></a></h4>
                        </div>
                        <!-- dialog buttons -->
                        <div class="modal-footer">
                            <button type="submit" style="display: none" id="btn_submit_import_excel"></button>
                            <button type="button" onclick="fc_summit_import()" class="btn btn-primary btn-ok">Upload
                            </button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="modal-import-history" class="modal fade" data-backdrop="static" data-keyboard="false"
             url-action="{{route('contract::manage.contract.histories')}}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- dialog body -->
                    <div class="modal-body">
                        <h4>{{trans('contract::view.Danh_sach_file_import')}}</h4>
                        <div id="box-link-download">

                        </div>
                    </div>
                    <!-- dialog buttons -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="pdh-10">
        <div class="table-responsive _me_table_responsive fixed-table-container">
            <table id="me_reward_tbl" class="fixed-table table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle">
                <thead>
                    <tr>
                        @if ($hasChecked)
                        <th class="td-fixed td-check"><input type="checkbox" class="_check_all"></th>
                        @endif
                        <th class="td-fixed sorting {{ Config::getDirClass('employee_code') }} col-name" data-order="employee_code" data-dir="{{ Config::getDirOrder('employee_code') }}">ID</th>
                        @if ($filterMonth == '_all_')
                        <th class="td-fixed sorting {{ Config::getDirClass('eval_time') }} col-name" data-order="eval_time" data-dir="{{ Config::getDirOrder('eval_time') }}">{{ trans('project::me.Month') }}</th>
                        @endif
                        <th class="td-fixed sorting {{ Config::getDirClass('email') }} col-name" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('project::me.Account') }}</th>
                        <th class="sorting {{ Config::getDirClass('proj_name') }} col-name" data-order="proj_name" data-dir="{{ Config::getDirOrder('proj_name') }}">{{ trans('project::me.Project name') }}</th>
                        <th class="sorting {{ Config::getDirClass('proj_type') }} col-name" data-order="proj_type" data-dir="{{ Config::getDirOrder('proj_type') }}">{{ trans('project::me.Project type') }}</th>
                        <th class="sorting {{ Config::getDirClass('status') }} col-name" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('project::me.ME status') }}</th>
                        <th class="sorting {{ Config::getDirClass('avg_point') }} col-name" data-order="avg_point" data-dir="{{ Config::getDirOrder('avg_point') }}">{{ trans('project::me.Contribution level') }}</th>
                        <th class="sorting {{ Config::getDirClass('avg_point') }} col-name" data-order="avg_point" data-dir="{{ Config::getDirOrder('avg_point') }}">
                            {{ trans('project::me.Norm') }} <br />(đ)
                            <i class="fa fa-question-circle el-qtip" title="{{ trans('project::me.Reward corresponding contribute level') }} . {{ trans('me::view.Right click to view more comment') }}"></i>
                        </th>
                        <th>{{ trans('project::me.Effort') }}(%)</th>
                        <th>
                            {{ trans('project::me.Reward suggestion') }} <br />(đ)
                            <i class="fa fa-question-circle el-qtip" title="{{ trans('project::me.Multiply Norm and Effort') }}"></i>
                        </th>
                        <th class="minw-100 sorting {{ Config::getDirClass('reward_submit') }} col-name" data-order="reward_submit" data-dir="{{ Config::getDirOrder('reward_submit') }}">
                            {{ trans('project::me.Leader revise') }} <br />(đ)
                            <i class="fa fa-question-circle el-qtip" title="{{ trans('project::me.Reward help') }}"></i>
                        </th>
                        <th>
                            {{ trans('project::me.Comment') }}
                            <i class="fa fa-question-circle el-qtip" title="{{ trans('project::me.Comment leader') }} . {{ trans('me::view.Right click to view more comment') }}"></i> <br />
                        </th>
                        <th class="minw-100 sorting {{ Config::getDirClass('reward_approve') }} col-name" data-order="reward_approve" data-dir="{{ Config::getDirOrder('reward_approve') }}">
                            {{ trans('project::me.COO approve') }} <br />(đ)
                            <i class="fa fa-question-circle el-qtip" title="{{ trans('project::me.Reward help') }}"></i>
                        </th>
                        <th class="sorting {{ Config::getDirClass('reward_status') }} col-name" data-order="reward_status" data-dir="{{ Config::getDirOrder('reward_status') }}">{{ trans('project::me.Reward status') }}</th>
                        @if ($permissUpdatePaid)
                        <th class="sorting {{ Config::getDirClass('status_paid') }} col-name" data-order="status_paid" data-dir="{{ Config::getDirOrder('status_paid') }}">{{ trans('project::me.Reward paid') }}</th>
                        @endif
                    </tr>
                    <tr>
                        @if ($hasChecked)
                        <td class="td-fixed td-check"></td>
                        @endif
                        <td class="td-fixed"></td>
                        @if ($filterMonth == '_all_')
                        <td class="td-fixed"></td>
                        @endif
                        <td class="td-fixed">
                            <input type="text" class="form-control filter-grid" name="filter[emp.email]" value="{{ FormView::getFilterData('emp.email') }}" placeholder="{{ trans('project::me.Search') }}...">
                        </td>
                        <td>
                            <?php
                            if (!$filterProjectCode) {
                                $filterProjectCode = FormView::getFilterData('proj.name');
                            }
                            ?>
                            <input type="text" class="form-control filter-grid minw-100" name="filter[proj.name]" value="{{ $filterProjectCode }}" placeholder="{{ trans('project::me.Search') }}...">
                        </td>
                        <td></td>
                        <td>
                            <select class="form-control filter-grid select-grid" name="filter[me.status]"
                                    style="min-width: 130px;">
                                <option value="">&nbsp;</option>
                                @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" {{ $value == FormView::getFilterData('me.status') ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="form-control filter-grid select-grid" name="filter[excerpt][avg_point]"
                                    style="min-width: 140px;">
                                <option value="">&nbsp;</option>
                                @foreach ($contributes as $value => $label)
                                <option value="{{ $value }}" {{ $value == FormView::getFilterData('excerpt', 'avg_point') ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            @if (Permission::getInstance()->isAllow('project::me.config_data'))
                            <a href="{{ route('project::me.config_data') }}" target="_blank">{{ trans('project::me.Config') }}</a>
                            @endif
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <?php
                            if ($isReview) {
                                unset($rewardStatuses[MeReward::STT_DRAFT]);
                            }
                            ?>
                            <select class="form-control filter-grid select-grid" name="filter[excerpt][reward_status]"
                                    style="width: 120px">
                                <option value="">&nbsp;</option>
                                @foreach($rewardStatuses as $value => $label)
                                <option value="{{ $value }}" {{ $value == $filterStatus ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        @if ($permissUpdatePaid)
                        <td>
                            <?php $filterStatusPaid = FormView::getFilterData('number', 'status_paid'); ?>
                            <select class="form-control filter-grid select-grid" name="filter[number][status_paid]"
                                style="width: 100px">
                                <option value="">&nbsp;</option>
                                @foreach ($statusPaidLabels as $value => $label)
                                <option value="{{ $value }}" {{ is_numeric($filterStatusPaid) && intval($filterStatusPaid) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $countDraft = 0;
                    $countSubmited = 0;
                    $countApproved = 0;
                    $oldPermissEditSubmit = $permissEditSubmit;
                    $oldPermissEditApprove = $permissEditApprove;

                    $colSpan = $filterMonth == '_all_' ? 14 : 13;
                    if ($hasChecked) {
                        $colSpan++;
                    }
                    if ($permissUpdatePaid) {
                        $colSpan++;
                    }
                    ?>
                    @if (!$collectionModel->isEmpty())
                        @foreach($collectionModel as $item)
                        <?php
                            $htmlViewNoteOniste = '';
                            if ($item->proj_type == Project::TYPE_ONSITE) {
                                $htmlViewNoteOniste = '<ul> <li> Khoảng cách giữa các đợt đi onsite không quá: ' . Project::DAY_ALLOWED . ' ngày</li>';
                                $htmlViewNoteOniste .= '<li> Mức độ đóng góp ';
                                $htmlViewNoteOniste .= ($item->status != $sttReward) ? $item->contribute_label : 'N/A';
                                $itemReward = MeView::getItemReward($item->avg_point, $configRewardOnsite);
                                $monthOnsite = 0;
                                $allowanceOnste = 0;
                                $timeOnsite = '';
                                $htmlDate = '';
                                if (isset($inforEmployeeOnsite) &&
                                    isset($inforEmployeeOnsite[$item->emp_id])) {
                                    $infor = $inforEmployeeOnsite[$item->emp_id];
                                    $monthOnsite = $infor['month'];
                                    $allowanceOnste = MeView::getListAllowanceOnste($monthOnsite);
                                    $timeOnsite = $infor['startAt'] . ' -> ' . $infor['endAt'];
                                    foreach($infor['groupDate'] as $date) {
                                        $arrDate = explode('->', $date);
                                        if (isset($arrDate[0])) {
                                            $id = $infor['groupStartId'][$arrDate[0]];
                                            $htmlDate .= '<li> <a href="' . route('manage_time::profile.mission.edit', ['id' => $id]) . '">' . $date . '</a></li>';
                                        } else {
                                            $htmlDate .= '<li>' . $date . '</li>';
                                        }
                                    }
                                }
                                $htmlViewNoteOniste .= ': ' . number_format($itemReward, 0, '.', ',') . ' đ</li>';
                                $itemReward += $allowanceOnste;
                                $htmlViewNoteOniste .= '<li>Phụ cấp onsite: ' . number_format($allowanceOnste, 0, '.', ',') . ' đ';
                                $htmlViewNoteOniste .= '<ul> <li> Số tháng onsite: ' . $monthOnsite . ' tháng</li>';
                                if ($htmlDate) {
                                    $htmlViewNoteOniste .= '<li> Thời gian onsite: ' . $timeOnsite . '</li>';
                                    $htmlViewNoteOniste .= '<li> Chi tiết thời gian Onsite: <ul>' . $htmlDate . ' </ul> </li>';
                                }
                                $htmlViewNoteOniste .= '</li>  </ul>';
                            } else {
                                $itemReward = MeView::getItemReward($item->avg_point, $configRewards);
                            }
                        //$itemEffort = MeView::getEffortReward($item, $listRangeMonths);
                        $itemEffort = $item->day_effort * 100 / MeView::getDaysOfMonthBaseline($item->eval_time, $listRangeMonths);

                        if ($item->reward_status == MeReward::STT_APPROVE) {
                            $permissEditSubmit = false;
                            if (!$isReview) {
                                $permissEditApprove = false;
                            }
                            $countApproved++;
                        } else if ($item->reward_status == MeReward::STT_SUBMIT) {
                            if (!$isReview) {
                                $permissEditApprove = false;
                                $countDraft++;
                            } else {
                                $permissEditSubmit = false;
                            }
                            $countSubmited++;
                        } else {
                            $permissEditApprove = false;
                            $countDraft++;
                        }
                        if (!$filterHasSubmit) {
                            $permissEditSubmit = false;
                            $permissEditApprove = false;
                        }
                        ?>
                        @include('project::me.template.me-reward-item')
                        <?php
                        $permissEditSubmit = $oldPermissEditSubmit;
                        $permissEditApprove = $oldPermissEditApprove;
                        ?>
                        @endforeach
                    @else
                    <tr class="row-no-result">
                        <td colspan="{{ $colSpan }}" class="text-center"><h4>{{ trans('project::me.No result') }}</h4></td>
                    </tr>
                    @endif
                </tbody>

                <tfoot>
                    <tr>
                        @if ($hasChecked)
                        <td class="td-fixed text-right" colspan="3"><strong>{{ trans('me::view.Total') }}</strong></td>
                        <?php
                        $footColspan = $colSpan - 3;
                        ?>
                        @else
                        <td class="td-fixed" colspan="2"></td>
                        <?php
                        $footColspan = $colSpan - 2;
                        ?>
                        @endif
                        <td colspan="4" class="text-right"></td>
                        <td class="total-norm text-right text-bold"><i class="fa fa-spin fa-refresh"></i></td>
                        <td></td>
                        <td class="total-reward_suggest text-right text-bold"><i class="fa fa-spin fa-refresh"></i></td>
                        <td class="total-reward_submit text-right text-bold"><i class="fa fa-spin fa-refresh"></i></td>
                        <td></td>
                        <td class="total-reward_approve text-right text-bold"><i class="fa fa-spin fa-refresh"></i></td>
                        <td colspan="{{ $footColspan - 10 }}"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if ($filterHasSubmit && !$isReview)
        <div class="form-group">
            <button type="button" id="add_item_btn" class="btn btn-success">
                <i class="fa fa-plus"></i> {{ trans('project::view.Add') }}
            </button>
        </div>
        @endif
    </div>

    <?php
    $routeSubmit = route('project::me.reward.submit');
    $actionText = 'submit';
    if ($isReview) {
        $routeSubmit = route('project::me.reward.approve');
        $actionText = 'approve';
    }
    ?>
    <!--if filter team and project type OSDC-->
    <div class="box-body text-center">
        @if ($filterHasSubmit)

            @if ($collectionModel->hasMorePages())
            <p><i>({{ trans('project::me.This collection has more than one page, please :action foreach page', ['action' => $actionText]) }}</i>)</p>
            @endif

            <form id="reward_submit_form" method="post" action="{{ $routeSubmit }}" class="no-validate _inline">
                {!! csrf_field() !!}
                <div class="hidden submit-data"></div>
                @if (!$isReview)
                <button type="submit" class="btn btn-info mgr-10 btn-submit-confirm" data-save="1">{{ trans('project::me.Save') }}</button>
                @endif
                <button type="submit" class="btn-add btn-submit-confirm" data-save="0" data-noti="{{trans('project::me.Confirm submit')}}">
                    @if (!$isReview)
                    {{ trans('project::me.Submit') }}
                    @else
                    {{ trans('project::me.Approve') }}
                    @endif
                </button>
            </form>

        @endif

        @if ($filterHasSubmit && !$collectionModel->isEmpty() && $permissUpdatePaid && $countApproved > 0)
            &nbsp;&nbsp;
            <form method="post" action="{{ route('project::me.reward.update_paid') }}" class="reward_change_paid no-validate _inline">
                {!! csrf_field() !!}
                <input type="hidden" name="status" value="{{ MeView::STATE_PAID }}">
                <div class="item-eval-ids hidden"></div>
                <button type="submit" class="btn btn-success btn-submit-paid">
                    {{ trans('project::me.Paid') }}
                </button>
            </form>
            &nbsp;&nbsp;
            <form method="post" action="{{ route('project::me.reward.update_paid') }}" class="reward_change_paid no-validate _inline">
                {!! csrf_field() !!}
                <input type="hidden" name="status" value="{{ MeView::STATE_UNPAID }}">
                <div class="item-eval-ids hidden"></div>
                <button type="submit" class="btn btn-danger btn-submit-unpaid">
                    {{ trans('project::me.Unpaid') }}
                </button>
            </form>
        @endif
    </div>

    <div class="box-body">
        @include('team::include.pager')
    </div>

    @include('me::templates.reward-comment')
</div>

<div class="hidden">
    <table id="rw_table_template">
        <tbody>
            @include('project::me.template.me-reward-item', ['item' => null])
        </tbody>
    </table>
</div>
@include('me::templates.reward-export-modal')

@endsection

@section('warn_confirn_class', 'modal-default')

@section('script')
<div class="modal fade modal-default" id="_modal_confirm" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ trans('core::view.Are you sure delete item(s)?') }}</p>
                <p class="text-change"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close pull-left btn-default" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok btn-primary">{{ trans('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->

<div id="export_excel"></div>
<textarea id="filter_month_format" class="hidden">{{ $filterMonth != '_all_' ? Carbon::parse($filterMonth)->format('m-Y') : '' }}</textarea>

<div id="_me_alert"></div>

<script>
    var hasMorePages = '{{ $collectionModel->lastPage() > 1 }}';
    var hasBtnSubmit = '{{ $filterHasSubmit }}';
    var isReview = '{{ $isReview }}';
    var routeName = '{{ $request->route()->getName() }}';
    var team_id = '{{ $filterTeam }}';
    var filterMonth = '{{ $filterMonth }}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.3/jquery.number.min.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="{{ asset('lib/exceljs/excelexportjs.js') }}"></script>
<script src="{{ asset('lib/fixed-table/tableHeadFixer.js') }}"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script src="{{ CoreUrl::asset('project/js/me_reward.js') }}"></script>
<script>
    var _token = $('meta[name="_token"]').attr('content');
    var textErrorMaxNorm = '{{ trans("project::me.Please input Reward not be greater than Norm") }}';
    var textErrorMaxLen = "{{ trans('validation.max.string', ['attribute' => 'Comment', 'max' => 500]) }}";
    var textErrorRewardRequired = "{{ trans('project::me.Reward number is required') }}";
    var textRequiredTeamAndTime = "{{ trans('project::me.You must select Team and Month') }}";
    var textErrorNoItemChecked = "{{ trans('project::me.None item checked') }}";
    var textErrorSelectTeamMonth = "{{ trans('project::me.You must select team and month') }}";
    var textErrorSelectEmployee = "{{ trans('project::me.You must select employee') }}";
    var textConfirmDelete = "{!! trans('project::me.Are you sure you want to do this action', ['action' => trans('project::me.Delete')]) !!}";
    var textCommentRequired = '{!! trans("me::view.You must comment for this value") !!}';
    var textNote = '{!! trans("me::view.Note") !!}';

    var urlUpdateStatusPaid = "{{ route('project::me.reward.update_paid') }}";
    var urlSearchEmployee = "{{ route('team::employee.list.search.ajax', ['type' => null]) }}";
    var urlEmployeeInfor = "{{ route('team::employee.infor') }}";
    var urlDeleteItem = "{{ route('project::me.reward.delete_item') }}";
    var urlLoadEvalComments = "{{ route('me::comment.get_eval_comments') }}";
    var urlGetTotalReward = "{{ route('project::me.reward.total_reward') }}";

    var filterMonthFormat = $('#filter_month_format').val();
    var paramsColumn = [{
         emp_name: 'Name',
         emp_email: 'Account',
         team_name: 'D',
         reward_approve: 'Reward ' + filterMonthFormat,
         detail: 'Detail',
         comment: 'Comment'
    }];
    var TYPE_OSDC = {{ Project::TYPE_OSDC }};
    var IS_REVIEW = parseInt("{{ $isReview }}");
    var STATE_PAID = parseInt('{{ MeView::STATE_PAID }}');
    var contriMustComment = JSON.parse("{!! json_encode(MeView::typesMustComment()) !!}");
    var CM_TYPE_LATE_TIME = {{ \Rikkei\Me\Model\Comment::TYPE_LATE_TIME }};

    (function ($) {
        $(document).ready(function () {
            RKfuncion.select2.init();
            var fixedCols = $('.fixed-table thead tr:first .td-fixed').length;
            $(".fixed-table").tableHeadFixer({"left" : fixedCols});

            // @if ($hasChecked)
            //     if ($('.alert-error').length < 1 && $('._check_item').length > 0) {
            //         $('._check_all').click();
            //     }
            // @endif

            RKfuncion.general.initDateTimePicker();

            $('#rw_time_filter').on('dp.change', function () {
                $('.btn-search-filter').click();
            });
        });

        @if (!($filterHasSubmit && !$collectionModel->isEmpty()
                && ($isReview || !$isReview && $countDraft > 0))
                && $countApproved == 0)
            //$('.td-check').addClass('hidden');
        @endif

    })(jQuery);

    function fc_show_model_upload_file() {
        $('#model-import-excel').modal();
        $('#frmMain #fileToUpload').val('');

    }
    function fc_summit_import() {
        if ($('#fileToUpload').val().trim() == '') {
            $('#error-import-excel').show();
            return false;
        }
        $('#error-import-excel').hide();
        $('#model-import-excel').modal('hide');
        $('#btn_submit_import_excel').trigger('click');
    }

</script>


@endsection


