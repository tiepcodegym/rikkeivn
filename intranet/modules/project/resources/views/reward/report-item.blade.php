<?php
use Rikkei\Project\Model\ProjReward;
use Rikkei\Team\View\Permission;
use Carbon\Carbon;
use Rikkei\Project\Model\Project;

if ($monthFilter) {
    $timeRewardParam = Carbon::createFromFormat('m-Y', $monthFilter);
    $timeRewardParam = $timeRewardParam->startOfMonth()->toDateTimeString();
}
$routeReward = 'project::me.reward.edit';
if (!Permission::getInstance()->isAllow($routeReward)) {
    $routeReward = 'project::me.reward.review';
}
$curEmp = Permission::getInstance()->getEmployee();

    if ($item->project_type == Project::TYPE_BASE) {
        $textCate = trans('project::view.Base');
    } else {
        $textCate = trans('project::view.OSDC');
    }
?>

@if ($item->rw_type == ProjReward::TYPE_TASK)
    <tr>
        <td>{{ $i }}</td>
        <td><input type="checkbox" value="{{ $item->project_id }}" class="input-export-reward" name="rewardIds[project][]"></td>
        <td>
            <a href="{{ route('project::reward', ['id' => $item->project_id, 'taskID' => $item->id]) }}">{{ $item->name }}</a>
        </td>
        <td class="text-uppercase">{{ preg_replace('/@.*$/','',$item->email) }}</td>
        <td>{{ $textCate }}</td>
        <td data-reward-col="team" >{{ $item->team_name }}</td>
        <td>{{ ProjReward::getLabelStatusRewardActual($item->status, $taskStatusAll) }}</td>
        <td class="text-right">{{ $item->billable }}</td>
        <td class="text-right">
            @if (ProjReward::hasShowBudgetApproveReward($item->leader_id, $curEmp))
                {{ number_format($item->reward_budget) }}
            @endif
        </td>
        <td class="text-right" data-reward-col="approve" data-reward="{{ ProjReward::hasShowBudgetApproveReward($item->leader_id, $curEmp) ? $item->sum_reward_approve : '' }}"></td>
        <td class="text-right">
            @if ($item->bonus_money == ProjReward::STATE_PAID)
                {{ trans('project::view.State Paid') }}
            @else
                {{ trans('project::view.State Unpaid') }}
            @endif
        </td>
        <td class="text-right">
            @if ($item->approve_date)
                {{Carbon::createFromFormat('Y-m-d H:i:s', $item->approve_date)->format('Y-m-d')}}
            @endif
        </td>
        <td>
            @if ($item->month_reward)
                <strong>{{ Carbon::parse($item->month_reward)->format('Y-m') }}</strong>
            @endif
        </td>
    </tr>
@else
    <tr>
        <td>{{ $i }}</td>
        <td>
            @if ($item->project_id)
            <input type="checkbox" value="{{ $item->project_id }}" class="input-export-reward" name="rewardIds[project][]">
            @else 
            <input type="checkbox" value="{{ $item->meteam_id }}" class="input-export-reward-team" name="rewardIds[team][]">
            @endif
        </td>
        <td>
            @if ($item->project_id)
            <?php
            $paramRewards = ['project_id' => $item->project_id];
            if ($monthFilter) {
                $paramRewards['time'] = $timeRewardParam;
            }
            ?>
            <a href="{{ route($routeReward, $paramRewards) }}" target="_blank" class="text-green">{{ $item->name }}</a>
            @else
            <span class="text-aqua">{{ trans('project::view.Team') }}: {{ $item->name }}</span>
            @endif
        </td>
        <td class="text-uppercase">
            @if ($item->project_id)
            {{ preg_replace('/@.*$/','',$item->email) }}
            @else
            {{ trans('project::view.N/A') }}
            @endif
        </td>
        <td class="text-green">{{ $textCate }}</td>
        <td>
            @if ($item->project_id)
            {{ $item->team_name }}
            @else
            {{ $item->name }}
            @endif
        </td>
        <td>{{ ProjReward::getLabelStatusRewardActual($item->status, $taskStatusAll) }}</td>
        <td class="text-right">
            @if ($item->project_id)
                {{ $item->billable }}
            @else
                {{ trans('project::view.N/A') }}
            @endif
        </td>
        <td class="text-right">{{ trans('project::view.N/A') }}</td>
        <td class="text-right">
            @if (ProjReward::hasShowBudgetApproveReward($item->leader_id, $curEmp))
                <?php
                $arrReward = explode(',', $item->sum_reward_approve);
                $sumReward = 0;
                if (count($arrReward) > 0) {
                    foreach ($arrReward as $rw) {
                        $arrRw = explode('|', $rw);
                        if (count($arrRw) < 2) {
                            continue;
                        }
                        $sumReward += intval($arrRw[1]);
                    }
                }
                ?>
                {{ number_format($sumReward) }}
            @endif
        </td>

        <td class="text-right">
            @if ($item->bonus_money == ProjReward::STATE_PAID)
                {{ trans('project::view.State Paid') }}
            @else
                {{ trans('project::view.State Unpaid') }}
            @endif
        </td>
        <td class="text-right">{{ trans('project::view.N/A') }}</td>
        <td>
            @if ($item->month_reward)
                <strong>{{ Carbon::parse($item->month_reward)->format('Y-m') }}</strong>
            @endif
        </td>
    </tr>
@endif
