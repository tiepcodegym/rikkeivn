@extends('layouts.default')

@section('title')
<?php
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\TaskReward;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View as CoreView;
?>
{{ trans('project::view.Project Reward') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}?v=2" />
@endsection

@section('content')
<?php
$totalReward = [
    'submit' => 0,
    'confirm' => 0,
    'approve' => 0
];
$showSubmitBtn = [
    'save' => false,
    'submiter' => false,
    'reviewer' => false,
    'approver' => false,
    'feedback' => false,
];
if (in_array($taskItem->status, [Task::STATUS_SUBMITTED, Task::STATUS_REVIEWED, Task::STATUS_APPROVED])
        && $taskItem->bonus_money != Task::REWARD_IS_PAID
        && (!isset($taskAssigns['role'][TaskAssign::ROLE_PM]['employee_id']) ||
                $userCurrent->id != $taskAssigns['role'][TaskAssign::ROLE_PM]['employee_id'])) {
    $showSubmitBtn['feedback'] = true;
}
$totalPoint = [
    'dev' => 0,
    'sqa' => 0,
    'pqa' => 0,
    'total' => 0,
];
$langDomain = 'project::view.';
global $isShowAddBtn;
$isShowAddBtn = in_array($taskItem->status, [Task::STATUS_NEW, Task::STATUS_FEEDBACK]) &&
        ((isset($taskAssigns['role'][TaskAssign::ROLE_PM]['employee_id']) &&
            $userCurrent->id == $taskAssigns['role'][TaskAssign::ROLE_PM]['employee_id']) ||
            $permission->isAllow('project::reward.submit'));
?>
<script>
    var RKProjectReward = {
        reward: {
            dev: {{ $rewardMetaInfor['reward_pm_dev'] }},
            sqa: {{ $rewardMetaInfor['reward_qa'] }},
            pqa: {{ $rewardMetaInfor['reward_pqa'] }},
            add: {{ $rewardMetaInfor['reward_add'] }},
            total: {{ $rewardMetaInfor['reward_actual'] }}
        },
        count_employee: {
            dev: 0,
            sqa: 0,
            pqa: 0,
            add: 0,
            total: 0
        },
        point_employee: {
            dev: {
                total: 0
            },
            sqa: {
                total: 0
            },
            pqa: {
                total: 0
            },
            add: {
                total: 0
            },
            total: 0
        }
    };
</script>
<?php
global $resultRewardProgess;
$resultRewardProgess = [
    'pm_null' => true, // PM not fill reward => get same Norm
    'leader_null' => true, // leader not fill reward => get same PM
    'coo_null' => true, // coo not fill reward => get same leader
];
/**
 * render html a row employee in reward
 * 
 * @param object $itemRewardMember
 * @param array $typesMember
 * @param array $periodExec
 * @param array $meMembers
 * @param object $taskItem
 * @param object $taskAssigns
 * @param object $rewardMeta
 * @param object $userCurrent
 * @param array $totalReward
 * @param array $totalPoint
 * @param array $showSubmitBtn
 */
function RKRewardEmployeeRow(
    $itemRewardMember,
    $typesMember,
    $periodExec,
    $meMembers,
    $taskItem,
    $taskAssigns,
    $rewardMeta,
    $userCurrent,
    $permission,
    &$totalReward,
    &$totalPoint,
    &$showSubmitBtn
) {
    global $resultRewardProgess;
    global $isShowAddBtn;
    $effortMemberReward = $itemRewardMember->effort_resource;
    $effortMemberReward = json_decode($effortMemberReward, true);
    $pointMember = 0;
    $typeKeyJs = 'dev';
    $isRewardType = $itemRewardMember->type == ProjectMember::TYPE_REWARD;
    switch ($itemRewardMember->type) {
        case ProjectMember::TYPE_PM:
        case ProjectMember::TYPE_DEV:
        case ProjectMember::TYPE_BRSE:
            $typeKeyJs = 'dev';
            break;
        case ProjectMember::TYPE_SQA:
            $typeKeyJs = 'sqa';
            break;
        case ProjectMember::TYPE_PQA:
            $typeKeyJs = 'pqa';
            break;
        case ProjectMember::TYPE_REWARD;
            $typeKeyJs = 'add';
            break;
        default:
            $typeKeyJs = 'dev';
    }
    ?>
    <tr id="reward-employee" class="reward-employee-row" data-type="{{ $typeKeyJs }}" data-id="{{ $itemRewardMember->id }}">
        <td class="text-left flag-parent-cmt{{ $isShowAddBtn && $isRewardType ? ' col-action' : '' }}" data-col="employee">
            @if (!$itemRewardMember->no_comment)
                <span class="flag-has-cmt"></span>
            @endif
            @if ($isShowAddBtn && $isRewardType)
            <button type="button" class="btn btn-danger btn-sm btn-del-member" title="{{ trans('project::view.Delete') }}"><i class="fa fa-trash"></i></button>
            @endif
            <span class="white-space-nowrap">
                {{ isset($typesMember[$itemRewardMember->type]) ? $typesMember[$itemRewardMember->type].':' : '' }}
                {{ CoreView::getNickName($itemRewardMember->email) }}
            </span>
        </td>
        @if ($isRewardType)
            @foreach ($periodExec as $itemPeriodExec)
                <td class="text-right col-mee"></td>
                <td class="text-right col-mee"></td>
            @endforeach
            <td class="text-right">
                {{ number_format($pointMember) }}
            </td>
            <td class="text-right reward-norm" data-id="{{ $itemRewardMember->id }}">
                0
            </td>
        @else
            <!-- for M.E and effort -->
            @foreach ($periodExec as $itemPeriodExec)
            <td class="text-right col-mee">
                @if (isset($meMembers[$itemRewardMember->employee_id][$itemPeriodExec]))
                {{ $meMembers[$itemRewardMember->employee_id][$itemPeriodExec] }}
                <?php $meItem = $meMembers[$itemRewardMember->employee_id][$itemPeriodExec]; ?>
                @else
                <?php $meItem = 0; ?>
                @endif
            </td>
            <td class="text-right col-mee">
                @if (isset($effortMemberReward[$itemPeriodExec]))
                {{ $effortMemberReward[$itemPeriodExec] }}
                <?php $effortItem = $effortMemberReward[$itemPeriodExec]; ?>
                @else
                <?php $effortItem = 0; ?>
                @endif
            </td>
            <?php
            if ($itemRewardMember->type == ProjectMember::TYPE_SUBPM) {
                $pointMember = 0;
            } else {
                $pointMember += $meItem * $effortItem;
            }
            ?>
            @endforeach
            <!-- end M.E and effort -->
            <?php
            // factor reward member * point
            /*switch ($itemRewardMember->type) {
                case ProjectMember::TYPE_PM:
                    $pointMember *= $rewardMeta->factor_reward_pm;
                    break;
                case ProjectMember::TYPE_DEV:
                    $pointMember *= $rewardMeta->factor_reward_dev;
                    break;
                case ProjectMember::TYPE_BRSE:
                    $pointMember *= $rewardMeta->factor_reward_brse;
                    break;
                default:
                    // nothing
            }*/
            $totalPoint['total'] += round($pointMember);
            ?>
            <script>
                RKProjectReward.point_employee.{{ $typeKeyJs }}['{{ $itemRewardMember->id }}'] = {{ round($pointMember) }};
                RKProjectReward.point_employee.{{ $typeKeyJs }}.total += {{ round($pointMember) }};
                RKProjectReward.point_employee.total += {{ round($pointMember) }};
                RKProjectReward.count_employee.{{ $typeKeyJs }}++;
                RKProjectReward.count_employee.total++;
            </script>
            <td class="text-right">
                {{ number_format($pointMember) }}
            </td>
            <td class="text-right reward-norm" data-id="{{ $itemRewardMember->id }}">
                0
            </td>
        @endif
        <td class="text-right" data-col="reward_submit"> <!-- PM suggest -->
            <?php
            if ($itemRewardMember->reward_submit === null) {
                $reward = '';
            } else {
                $reward = number_format($itemRewardMember->reward_submit);
                $resultRewardProgess['pm_null'] = false;
            }
            ?>
            <?php
            if (($taskItem->status == Task::STATUS_NEW || $taskItem->status == Task::STATUS_FEEDBACK) &&
                    ((isset($taskAssigns['role'][TaskAssign::ROLE_PM]['employee_id']) &&
                    $userCurrent->id == $taskAssigns['role'][TaskAssign::ROLE_PM]['employee_id']) ||
                    $permission->isAllow('project::reward.submit'))):
                ?>
                <input name="reward[submit][{{ $itemRewardMember->id }}]" class="form-control input-reward input-reward-submit @if ($typeKeyJs === 'dev') input-dev-team @endif" data-id="{{ $itemRewardMember->id }}"
                       data-type="submit" value="{{ $reward }}" />
                       <?php
                       $showSubmitBtn['save'] = true;
                       $showSubmitBtn['submiter'] = true;
                       ?>
                   <?php else: ?>
                <span data-reward-fill="pm">{{ $reward }}</span>
            <?php endif; ?>
            <?php $totalReward['submit'] += $itemRewardMember->reward_submit; ?>
        </td>
        <td class="text-right" data-col="reward_confirm">  <!-- leader submit -->
            <?php
            if ($itemRewardMember->reward_confirm === null) {
                $rewardConfirm = '';
            } else {
                $rewardConfirm = number_format($itemRewardMember->reward_confirm);
                $resultRewardProgess['leader_null'] = false;
            }
            ?>
            <?php $totalReward['confirm'] += $itemRewardMember->reward_confirm; ?>
            @if ($taskItem->status == Task::STATUS_REVIEWED || $taskItem->status == Task::STATUS_APPROVED)
            <div class="text-center text-info">
                <span class="text-reward-confirm" data-reward-fill="leader">{{ $rewardConfirm }}<i class="fa fa-check"></i></span>
            </div>
            @else
                <?php
                if ($taskItem->status == Task::STATUS_SUBMITTED && (
                    (isset($taskAssigns['role'][TaskAssign::ROLE_REVIEWER]['employee_id']) &&
                    $userCurrent->id == $taskAssigns['role'][TaskAssign::ROLE_REVIEWER]['employee_id']) ||
                    $permission->isAllow('project::reward.confirm'))):
                ?>
                    <input name="reward[confirm][{{ $itemRewardMember->id }}]" class="form-control input-reward input-reward-confirm @if ($typeKeyJs === 'dev') input-dev-team @endif"  data-id="{{ $itemRewardMember->id }}"
                       data-type="confirm" value="{{ $rewardConfirm }}" />
                       <?php
                       $showSubmitBtn['save'] = true;
                       $showSubmitBtn['reviewer'] = true;
                       ?>
                <?php else: ?>
                    <span data-reward-fill="leader">{{ $rewardConfirm }}</span>
                <?php endif; ?>
            @endif
        </td>
        <td class="text-right" data-col="reward_approve"> <!-- coo approve -->
            <?php
            if ($itemRewardMember->reward_approve === null) {
                $rewardApprove = '';
            } else {
                $rewardApprove = number_format($itemRewardMember->reward_approve);
                $resultRewardProgess['coo_null'] = false;
            }
            ?>
            <?php $totalReward['approve'] += $itemRewardMember->reward_approve; ?>
            @if ($taskItem->status == Task::STATUS_APPROVED)
            <div class="text-center text-success">
                <span class="text-reward-approve" data-reward-fill="coo">{{ $rewardApprove }}<i class="fa fa-check"></i></span>
            </div>
            @else
            <?php
            if ($taskItem->status == Task::STATUS_REVIEWED && (
                    (isset($taskAssigns['role'][TaskAssign::ROLE_APPROVER]['employee_id']) &&
                    $userCurrent->id == $taskAssigns['role'][TaskAssign::ROLE_APPROVER]['employee_id']) ||
                    $permission->isAllow('project::reward.approve'))):
                ?>
                <input name="reward[approve][{{ $itemRewardMember->id }}]" class="form-control input-reward input-reward-approve @if ($typeKeyJs === 'dev') input-dev-team @endif"  data-id="{{ $itemRewardMember->id }}"
                       data-type="approve" value="{{ $rewardApprove }}" />
                       <?php
                       $showSubmitBtn['save'] = true;
                       $showSubmitBtn['approver'] = true;
                       ?>
                   <?php else: ?>
                       <span data-reward-fill="coo">{{ $rewardApprove }}</span>
                   <?php endif; ?>
                   @endif
        </td>
    </tr>
<?php }

// end function RKRewardEmployeeRow  
?>

<div class="row">
    <!-- box assngin -->
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border box-header-split-right header-small">
                <h3 class="box-title">{{ trans('project::view.Project') }}: {{ $project->name }}</h3>
                <div class="panel-split panel-left-link">
                    @if (TaskReward::isDeleteAvai($taskItem, true))
                        <button data-url-ajax="{{ URL::route('project::reward.actual.delete', ['id' => $taskItem->id]) }}" 
                            class="post-ajax btn-delete delete-confirm" type="button" data-noti="{{ trans('project::view.Are you sure delete this reward?') }}">{{ trans('project::view.Delete reward') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                    @endif
                    <a href="{{ URL::route('project::point.edit', ['id' => $project->id]) }}" target="_blank">{{ trans('project::view.Project report') }}</a>
                    <a href="{{ URL::route('project::project.edit', ['id' => $project->id]) }}" target="_blank">{{ trans('project::view.View workorder') }}</a>
                </div>
            </div>

            <div class="box-body">
                @if(!$project->isLong() || (isset($tasks) && count($tasks) <= 1))
                @include ('project::task.box_include.status_box')
                @endif
                @include ('project::task.box_include.assign_box')
            </div>

        </div>
    </div>
    <!-- end box assngin -->

    @if(isset($tasks) && count($tasks)>1 && $project->isLong())
    <!-- box summary -->
    
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border box-header-split-right header-small">
                <h3 class="box-title">Summary</h3>
            </div>

            <div class="box-body">
                <?php 
                    $sumBudget = 0;
                    foreach ($tasks as $key=>$task) {
                        if($project->checkMonthReward($task->month_reward)) {
                            $sumBudget = $sumBudget + $task->reward_budget;
                        }
                    }
                ?>
                <strong>Budget reward:</strong>
                <span>{{number_format($sumBudget)}}</span>
            </div>
            @if(count($totalRewardEmp))
                <div class="box-body">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="text-center" style="min-width: 120px;">{{ trans('project::view.Position') }}</th>
                            <th class="text-center" style="min-width: 95px;">{{ trans("project::view.COO's confirmation") }}<i class="fa fa-question-circle reward-help" data-toggle="tooltip" data-placement="top" title="{!! trans('project::view.Reward help') !!}" data-html="true" aria-hidden="true"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $type = ProjectMember::getTypeMember();
                        ?>
                        @foreach($totalRewardEmp as $rewardEmp)
                        <tr>
                            <td>{{$type[$rewardEmp->type]}}: {{$rewardEmp->name}}</td>
                            <td class="text-right">{{ number_format($rewardEmp->totalReward)}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif

        </div>
    </div>
    
    <!-- box reward follow circle -->
    <div class="col-md-12">
        <div class="content-wrapper">
            <div class="nav-tabs-custom tab-keep-status">
                <ul class="nav nav-tabs" role="tablist">
                    @foreach($tasks as $key=>$task)
                        @if($project->checkMonthReward($task->month_reward))
                            <li @if($task->id == $taskItem->id) class="active" @endif>
                                 <a href="{{ route('project::reward', ['id' => $project->id, 'taskID' => $task->id ]) }}#">{{$task->month_format}}</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
                <div class="box-body">
                    @include ('project::task.box_include.status_box')
                </div>

                <!-- box qa -->
                @include ('project::task.box_include.qa_box')
                <!-- end box qa -->

                @include ('project::task.box_include.pm_dev_box')
                <div class="box-body">
                    @include ('project::task.include.comment', ['collectionModel' => $taskCommentList, 'showComment' => true])
                </div>
            </div>

        </div>
    </div>
    <!-- end box reward follow circle -->
    @else


    <!-- box qa -->
    <div class="col-sm-12">
        <div class="box box-primary">
            @include ('project::task.box_include.qa_box')
        </div>
    </div>
    <!-- end box qa -->

    <!-- box pm dev -->
    <div class="col-sm-12">
        <div class="box box-primary">
            @include ('project::task.box_include.pm_dev_box')
        </div>
    </div>
    <!-- end box pm, dev -->
    @endif
</div>
@if(!$project->isLong()||(isset($tasks) && count($tasks) <= 1))
@include ('project::task.include.comment', ['collectionModel' => $taskCommentList, 'showComment' => true])
@endif

@if ($showSubmitBtn['reviewer'] || $showSubmitBtn['approver'])
<div id="modal-reward-feedback" class="modal in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('project::view.Feedback Content') }}</h4>
            </div>
            <div class="modal-body">
                <textarea rows="5" name="fb[comment]" class="form-control"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('project::view.Close') }}</button>
                <button type="submit" class="btn btn-danger">{{ trans('project::view.Feedback') }}
                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endif
<div class="reward-comment hidden">
    <div id="reward-comment-box" class="cmt-box-wrapper">
        <button type="button" class="close" ><span>&times;</span></button>
        <h4 class="me_comment_title">
            <strong>{{ trans('project::view.Comment') }}</strong>
            &nbsp;
            <a href="javascript:void(0)" class="cmt-btn-edit">
                <i class="fa fa-pencil-square-o"></i>
            </a>
        </h4>
        <div class="reward_comment_form">
            <div class="_loading hidden"><i class="fa fa-spin fa-refresh"></i></div>
            <div class="cmt-box-list">
                <div id="reward-comments-list" class="comments_list"></div>
            </div>
            <div class="input-group cmt-box-edit hidden">
                <textarea type="text" id="reward-comment-text" class="cmt-input form-control text-resize-y" rows="1"></textarea>
                <span class="input-group-btn">
                    <button class="reward_comment_submit btn btn-primary" type="button"><i class="fa fa-floppy-o"></i></button>
                </span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    var varGlobalPassModule = {
    messageValidateRewardFillTotal: '{{ trans('project::view.Please fill total reward littler actual reward') }}',
            messageValidateRewardMin: '{{ trans('project::view.Please fill reward >= 0') }}',
            messageValidateRewardTotalDevTeam: '{{ trans('project::view.validate total dev team') }}'
    };
    var textTrans = {
        messageConfirmDelete: '{!! trans('project::message.Are you sure want to delete?') !!}',
        messageRequireEmployee: '{!! trans('project::message.Please input employee') !!}',
    };
    var urlBonusMoney = '{{route('project::reward.update.bonusMoney', ['id'=> $project->id])}}';
    var urlAddCommentRW = '{{route('project::reward.employee.comment')}}';
    var urlGetCommentRW = '{{route('project::reward.employee.getComment')}}';
    var urlSearchEmployee = "{{ route('team::employee.list.search.ajax', ['type' => null]) }}";
    var urlDelEmpRW = "{{ route('project::reward.delete.employee') }}";
    var token = '{{csrf_token()}}';
    RKProjectReward.leader_null = {{ $resultRewardProgess['leader_null'] ? 'true' : 'false' }};
    RKProjectReward.coo_null = {{ $resultRewardProgess['coo_null'] ? 'true' : 'false' }};
    RKProjectReward.pm_null = {{ $resultRewardProgess['pm_null'] ? 'true' : 'false' }};
    RKProjectReward.is_submitted = {{ $taskItem->status == Task::STATUS_SUBMITTED ? 'true' : 'false' }};
    RKProjectReward.is_reviewed = {{ $taskItem->status == Task::STATUS_REVIEWED ? 'true' : 'false' }};
</script>
<script src="{!!CoreUrl::asset('common/js/methods.validate.js')!!}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.3/jquery.number.min.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.3/jquery.number.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}?v=2"></script>
<script>
    jQuery(document).ready(function($) {
    $('[data-toggle="popover"]').tooltip();
    });
</script>
@endsection

