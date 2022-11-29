<?php
use Rikkei\Project\Model\MeReward;
use Rikkei\Project\View\MeView;
use Rikkei\Project\Model\Project;
?>

@if ($item)
<?php
$isMeRewardType = $filterHasSubmit && $item->status == Rikkei\Project\Model\MeEvaluation::STT_REWARD;
?>
<tr data-id="{{ $item->id }}">
    @if ($hasChecked)
    <td class="td-fixed td-check"><input type="checkbox" class="_check_item" value="{{ $item->id }}"></td>
    @endif
    <td class="td-fixed _nowwrap{{ $isMeRewardType ? ' has-del' : '' }}" data-col="employee_code">
        @if ($isMeRewardType)
            <button type="button" class="btn btn-danger btn-sm rw-del-btn"><i class="fa fa-trash"></i></button>
        @endif
        <span class="value">{{ $item->employee_code }}</span>
    </td>
    @if ($filterMonth == '_all_')
    <td class="td-fixed _nowwrap date-tooltip">
        {{ $item->eval_time->format('m-Y') }}
        @if (isset($listRangeMonths[$item->eval_month]))
        <i data-toggle="tooltip" data-placement="bottom" class="fa fa-question-circle"
           title="{{ $listRangeMonths[$item->eval_month]['start'] . ' : ' . $listRangeMonths[$item->eval_month]['end'] }}"></i>
        @endif
    </td>
    @endif
    <td class="td-fixed _nowwrap" data-col="email">{{ ucfirst(preg_replace('/@.*/', '', $item->email)) }}</td>
    <td class="_nowwrap" data-col="proj_name">
        @if ($item->status != $sttReward)
            @if ($item->proj_id)
            <a href="{{ route('project::point.edit', ['id' => $item->proj_id]) }}" target="_blank" class="project_code_auto">{{ $item->proj_name }}</a>
            @else
            {{ $item->team_name }}
            @endif
        @else
            N/A
        @endif
    </td>
    <td data-col="proj_type">{{ isset($projectTypeLabels[$item->proj_type]) ? $projectTypeLabels[$item->proj_type] : 'N/A' }}</td>
    <td data-col="me_status">{{ $item->status_label }}</td>
    <td data-col="me_contribute">{{ ($item->status != $sttReward) ? $item->contribute_label : 'N/A' }}</td>

    <td class="text-right"  data-col="norm"
        @if ($item->proj_type == Project::TYPE_ONSITE)
            data-view_note="view-note"
        @endif
    >
        {{ ($item->status != $sttReward) ? number_format($itemReward, 0, '.', ',') : 'N/A' }}
        <div class="note-allowance-onsite hidden">
            {!! $htmlViewNoteOniste !!}
        </div>
    </td>
    <td class="text-right {{ $itemEffort > 100 ? 'error' : '' }}" data-col="effort">
        {{ ($item->status != $sttReward) ? number_format($itemEffort, 2, '.', ',') : 'N/A' }}
    </td>
    <?php $rewareSuggest = number_format($itemReward * min([$itemEffort, 100]) / 100, 0, '.', ','); ?>
    <td class="text-right" data-value="{{ round($itemReward * $itemEffort / 100, 0) }}" data-col="reward_suggest">
        {{ ($item->status != $sttReward) ? $rewareSuggest : 'N/A' }}
    </td>
    <td class="input_select text-right @if ($item->submit_histories) td-relative td-histories"
        data-histories="{{ json_encode(unserialize($item->submit_histories)) }}"
        @else " @endif  data-col="reward_submit">
        @if ($permissEditSubmit)
            <?php
            $oldRewardSubmit = old('rewards.'.$item->id.'.submit');
            $rewardSubmit = $rewareSuggest;
            if ($oldRewardSubmit) {
                $rewardSubmit = $oldRewardSubmit;
            } else if ($item->reward_submit !== null) {
                $rewardSubmit = number_format($item->reward_submit, 0, '.', ',');
            }
            ?>
            <input type="text" data-min="0" data-max="{{ $itemReward }}"
                   name="rewards[{{ $item->id }}][submit]"
                   value="{{ $rewardSubmit }}"
                   class="form-control input-value input-number">
        <input type="hidden" name="rewards[{{ $item->id }}][reward_suggest]" class="input-value" value="{{ $item->reward_submit !== null ? $item->reward_submit : $rewareSuggest }}">
        @else
        <span class="text-blue">{{ $item->reward_submit !== null ? number_format($item->reward_submit, 0, '.', ',') : null }}</span>
        @endif
        @if ($item->submit_histories)
        <a href="#" class="icon-history" title=""><i class="fa fa-history"></i></a>
        @endif
    </td>
    <td class="td-edit {{ isset($commentClasses[$item->id]) ? implode(' ', $commentClasses[$item->id]) : '' }}"
        data-col="comment" title="{{ trans('me::view.Right click to view more comment') }}">
        <?php
        $oldRewardComment = old('rewards.'.$item->id.'.comment');
        $rewardComment = $oldRewardComment ? $oldRewardComment : $item->reward_comment;
        ?>
        <div class="input-edit" data-eval="{{ $item->id }}">
            <span class="value-view hidden">{{ $rewardComment }}</span>
            @if ($permissEditSubmit||$permissEditApprove)
            <textarea name="rewards[{{ $item->id }}][comment]"
                      class="form-control input-value value-edit">{{ $rewardComment }}</textarea>
            <button type="button" class="btn btn-sm btn-success input-edit-btn"><i class="fa fa-edit"></i></button>
            @endif
        </div>
    </td>
    <td class="input_select text-right @if ($item->approve_histories) td-relative td-histories"
        data-histories="{{ json_encode(unserialize($item->approve_histories)) }}"
        @else " @endif data-col="reward_approve">
        @if ($permissEditApprove)
            <?php
            $oldRewardApprove = old('rewards.'.$item->id.'.approve');
            $rewardApprove = $rewareSuggest;
            if ($oldRewardApprove) {
                $rewardApprove = $oldRewardApprove;
            } else if ($item->reward_approve !== null) {
                $rewardApprove = number_format($item->reward_approve, 0, '.', ',');
            } else if ($item->reward_submit !== null) {
                $rewardApprove = number_format($item->reward_submit, 0, '.', ',');
            }
            ?>
            <input type="text" data-min="0" data-max="{{ $itemReward }}"
                   name="rewards[{{ $item->id }}][approve]"
                   value="{{ $rewardApprove  }}"
                   class="form-control input-value input-number {{ $item->approve_histories ? 'text-green' : '' }}">
            <input type="hidden" name="rewards[{{ $item->id }}][reward_submit]" class="input-value" value="{{ $item->reward_approve !== null ? $item->reward_approve : $item->reward_submit }}">
        @else
            <span>{{ $item->reward_approve !== null ? number_format($item->reward_approve) : null }}</span>
        @endif
        @if ($item->approve_histories)
        <a href="#" class="icon-history" title=""><i class="fa fa-history"></i></a>
        @endif
    </td>
    <td class="{{ $item->reward_status == MeReward::STT_APPROVE ? 'text-green' : '' }}" data-col="reward_status">
        {{ MeView::getRewardStatus($item->reward_status, $rewardStatuses) }}
    </td>
    @if ($permissUpdatePaid)
    <td class="{{ $item->status_paid == MeView::STATE_UNPAID ? 'text-red' : '' }}" data-col="is_paid">
        @if ($item->reward_status == MeReward::STT_APPROVE)
        <span data-id="{{ $item->id }}" data-status="{{ $item->status_paid }}"
            class="status-paid-item reward-approved">
                   {{ isset($statusPaidLabels[$item->status_paid]) ? $statusPaidLabels[$item->status_paid] : null }}
        </span>
        @endif
    </td>
    @endif
</tr>
@else
<tr data-id="">
    @if ($hasChecked)
    <td class="td-fixed td-check"><input type="checkbox" class="_check_item" value=""></td>
    @endif
    <td class="td-fixed _nowwrap" data-col="employee_code"></td>
    <td class="td-fixed _nowwrap" data-col="email"></td>
    <td class="_nowwrap" data-col="proj_name"></td>
    <td class="_nowwrap" data-col="proj_type"></td>
    <td data-col="me_status"></td>
    <td data-col="me_contribute"></td>
    <td class="text-right" data-col="norm"></td>
    <td class="text-right" data-col="effort"></td>
    <td class="text-right" data-value="" data-col="reward_suggest"></td>
    <td class="input_select text-right" data-col="reward_submit">
        <input type="text" data-min="0" data-max=""
               name="rewards[new_id][submit]" value=""
               class="form-control input-value input-number">
    </td>
    <td class="td-edit" data-col="comment">
        <div class="input-edit" data-eval="">
            <span class="value-view hidden"></span>
            <textarea name="rewards[new_id][comment]"
                      class="form-control input-value value-edit"></textarea>
            <button type="button" class="btn btn-sm btn-success input-edit-btn"><i class="fa fa-edit"></i></button>
        </div>
    </td>
    <td class="input_select text-right" data-col="reward_approve"></td>
    <td class="" data-col="reward_status"></td>
    @if ($permissUpdatePaid)
    <td class="" data-col="is_paid"></td>
    @endif
</tr>
@endif