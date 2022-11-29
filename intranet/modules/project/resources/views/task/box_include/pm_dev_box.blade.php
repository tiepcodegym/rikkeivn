<?php
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Task;
?>
<div data-flag-load="reward">
    <p class="text-center">
        <i class="fa fa-spin fa-refresh"></i>
    </p>
    
</div>
<div class="box-body hidden" data-loading="reward">
    <p class="help-block">{!! trans('project::view.Reward help') !!}</p>
    <div>
        <button class="btn btn-primary" d-btn-reward="hide-me">{!!trans('project::view.hide me-eff column')!!}</button>
    </div>
    <form method="post" action="{{ route('project::reward.submit', ['id' => $project->id, 'taskID'=> $taskId]) }}"
          class="form-submit-ajax has-valid" autocomplete="off" id="form-project-reward">
          {!! csrf_field() !!}
        <input type="hidden" name="save" value="0" />
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table_reward_employees">
                <thead>
                    <tr>
                        <th class="text-center" style="min-width: 120px;">{{ trans('project::view.Position') }}</th>
                        @foreach ($periodExec as $itemPeriodExec)
                            <th class="text-center col-mee" style="min-width: 50px;">ME<br/>{{ $itemPeriodExec }}</th>
                            <th class="text-center col-mee" style="min-width: 50px;">Effort<br/>{{ $itemPeriodExec }}</th>
                        @endforeach
                        <th class="text-center">{{ trans('project::view.Point') }}</th>
                        <th class="text-center">{{ trans('project::view.Norm') }}</th>
                        <th class="text-center" style="min-width: 95px;">{{ trans("project::view.PM's suggestion") }}</th>
                        <th class="text-center" style="min-width: 95px;">{{ trans("project::view.Leader's verification") }}<i class="fa fa-question-circle reward-help" data-toggle="tooltip" data-placement="top" title="{!! trans('project::view.Reward help') !!}" data-html="true" aria-hidden="true"></i></th>
                        <th class="text-center" style="min-width: 95px;">{{ trans("project::view.COO's confirmation") }}<i class="fa fa-question-circle reward-help" data-toggle="tooltip" data-placement="top" title="{!! trans('project::view.Reward help') !!}" data-html="true" aria-hidden="true"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- calculator reward of employees by effort and M.E -->
                    <?php
                    $rewardMemberQA = [];
                    foreach ($rewardMembers as $itemRewardMember) {
                        // store member qa to render bottom
                        if (in_array($itemRewardMember->type,[
                            ProjectMember::TYPE_SQA,
                            ProjectMember::TYPE_PQA
                        ])) {
                            $rewardMemberQA[] = $itemRewardMember;
                        } else {
                            RKRewardEmployeeRow(
                                    $itemRewardMember, 
                                    $typesMember, 
                                    $periodExec, 
                                    $meMembers, 
                                    $taskItem, 
                                    $taskAssigns,
                                    $rewardMeta,
                                    $userCurrent,
                                    $permission,
                                    $totalReward, 
                                    $totalPoint, 
                                    $showSubmitBtn
                            );
                        }
                    }
                    if (count($rewardMemberQA)) {
                        foreach ($rewardMemberQA as $itemRewardMember) {
                            RKRewardEmployeeRow(
                                    $itemRewardMember, 
                                    $typesMember, 
                                    $periodExec, 
                                    $meMembers, 
                                    $taskItem, 
                                    $taskAssigns,
                                    $rewardMeta,
                                    $userCurrent,
                                    $permission,
                                    $totalReward, 
                                    $totalPoint, 
                                    $showSubmitBtn
                            );
                        }
                    }
                    ?>
                    <tr class="reward-total-report">
                        <td data-cel-total-span="{!! count($periodExec) * 2 + 1 !!}" colspan="{!! count($periodExec) * 2 + 1 !!}">&nbsp;</td>
                        <td class="text-right" data-reward-total="point">{{ number_format($totalPoint['total']) }}</td>
                        <td class="text-right" data-reward-total="norm"></td>
                        <td class="text-right">
                            <span class="reward-total-cal" data-type="submit">{{ $totalReward['submit'] ? number_format($totalReward['submit']) : '' }}</span>
                            <div class="text-left nvd-parent">
                                <input type="text" class="not-visiable-dom" name="total[submit]" data-type="submit" value="0" />
                            </div>
                        </td>
                        <td class="text-right">
                            @if ($taskItem->status == Task::STATUS_REVIEWED || $taskItem->status == Task::STATUS_APPROVED)
                                <div class="text-center text-info">
                                    <span class="text-reward-confirm">{{ number_format($totalReward['confirm']) }}<i class="fa fa-check"></i></span>
                                </div>
                            @else
                                <span class="reward-total-cal" data-type="confirm">{{ $totalReward['confirm'] ? number_format($totalReward['confirm']) : '' }}</span>
                                <div class="text-left nvd-parent">
                                    <input type="text" class="not-visiable-dom" name="total[confirm]" data-type="confirm" value="0" />
                                </div>
                            @endif
                        </td>
                        <td class="text-right">
                            @if ($taskItem->status == Task::STATUS_APPROVED)
                                <div class="text-center text-success">
                                    <span class="text-reward-confirm">{{ number_format($totalReward['approve']) }}<i class="fa fa-check"></i></span>
                                </div>
                            @else
                                <span class="reward-total-cal" data-type="approve">{{ $totalReward['confirm'] ? number_format($totalReward['approve']) : '' }}</span>
                                <div class="text-left nvd-parent">
                                    <input type="text" class="not-visiable-dom" name="total[approve]" data-type="approve" value="0" />
                                </div>
                            @endif
                        </td>
                    </tr>
                    <!-- calculator reward of employees by effort and M.E -->
                </tbody>
                @if ($isShowAddBtn)
                <tfoot>
                    <tr>
                        <td>
                            <button type="button" id="btn-add-reward" class="btn btn-success">
                                <i class="fa fa-plus"></i> {{ trans('project::view.Add member')  }}
                            </button>
                        </td>
                        <td colspan="{{ count($periodExec) * 2 + 5 }}"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="row">
            <div class="col-md-12 text-center margin-top-20">
                <p>
                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>  
                </p>
                @if ($showSubmitBtn['submiter'])
                    <button type="submit" class="btn btn-primary btn-submit-reward" data-save="0">{{ trans('project::view.Save') }}</button>
                    <button type="submit" class="btn btn-primary margin-left-15 btn-submit-reward" data-save="1">{{ trans('project::view.Submit') }}</button>
                @elseif ($showSubmitBtn['reviewer'])
                    <button type="submit" class="btn btn-primary btn-submit-reward btn-reward-save" data-save="0">{{ trans('project::view.Save') }}</button>
                    <button type="submit" class="btn btn-info margin-left-15 btn-submit-reward warn-confirm" 
                        data-save="1" data-noti="{{ trans("project::view.Are you sure to verify PM's reward suggestion?") }}">{{ trans('project::view.Submit') }}</button>
                @elseif ($showSubmitBtn['approver'])
                    <button type="submit" class="btn btn-primary btn-submit-reward btn-reward-save" data-save="0">{{ trans('project::view.Save') }}</button>
                    <button type="submit" class="btn btn-success margin-left-15 btn-submit-reward warn-confirm" data-save="1"
                        data-noti="{{ trans("project::view.Are you sure to confirm PM's reward suggestion?") }}">{{ trans('project::view.Approve') }}</button>
                @endif
                <!--show feedback btn-->
                @if ($showSubmitBtn['feedback'])
                    <button type="button" class="btn btn-danger margin-left-15" data-toggle="modal" data-target="#rw_modal_feedback">
                        {{ trans('project::view.Feedback') }}
                    </button>
                @endif
            </div>
        </div>
    </form>
    @if ($taskItem->status == Task::STATUS_APPROVED)
    <!--box check pay bonus money-->
    <div class="bonus-money">
        <strong>
            <p><span class="padding-right-40">{{ trans('project::view.state of pay bonus money') }}</span>
            @if ($changeStateBM)
            <input id="bonus-money-checkbox" type="checkbox" @if ($taskItem->bonus_money == Task::REWARD_IS_PAID) checked @endif data-toggle="toggle" data-on="Đã Trả" data-off="Chưa Trả" data-onstyle="success" data-offstyle="danger" data-size="small" onchange="bonusMoneyOnclick(this)">
            @else
                @if ($taskItem->bonus_money == Task::REWARD_IS_PAID)
                <span class="text-success"><i class="fa fa-check"></i>{{ trans('project::view.paid') }}</span>
                @else
                <span class="text-danger">{{ trans('project::view.unpaid') }}</span>
                @endif
            @endif
            </p>
        </strong>
    </div>
    <!--end box-->
    @endif
</div>

@include('project::task.box_include.modal_feedback')