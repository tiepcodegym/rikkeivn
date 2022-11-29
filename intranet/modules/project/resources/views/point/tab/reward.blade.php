<?php
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjRewardBudget;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\ProjectMeta;

$rewardMetaConfig = CoreConfigData::get('project.reward');
$rewardEvalUnitConfig = $project->getRewardEvalUnitConfig($rewardMetaConfig);
$evaluationLabel = array_reverse(ProjectPoint::evaluationLabel(), true);
$rewardBudgetsDb = ProjRewardBudget::getRewardBudgets($project->id);
$isEditRewardBudget = Permission::getInstance()->isAllow('project::reward.budget.update');

if ($project->type_mm == Project::MD_TYPE) {
    $billableEffort = round(
            $projectPointInformation['cost_billable_effort'] / 
            (float) CoreConfigData::get('project.mm'),
        2);
} else {
    $billableEffort = round($projectPointInformation['cost_billable_effort'],2);
}
?>
<div class="budget-status-callout">
@if ($projectMeta->isHideRewardBudget())
    <div class="callout callout-inline callout-warning">
        <p class="text-center text-uppercase"><strong>{{ trans('project::view.Private') }}</strong></p>
    </div>
@else
    <ol class="cd-breadcrumb triangle">
        <li class="{{ $projectMeta->isSubmittedRewardBudget() ? 'current' : '' }}"><strong>{{ trans('project::view.SubmittedRewards') }}</strong></li>
        <li class="{{ $projectMeta->isReviewedRewardBudget() ? 'current' : '' }}"><strong>{{ trans('project::view.ReviewedRewards') }}</strong></li>
        <li class="{{ $projectMeta->isShowRewardBudget() ? 'current' : '' }}"><strong>{{ trans('project::view.ApprovedRewards') }}</strong></li>
    </ol>
@endif
</div>
<p>{{ trans('project::view.Currency unit') }}: VND</p>
<div class="block-form-data form-reward-budget">
    <div class="table-responsive">
        <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
            <thead>
                <tr>
                    <th class="text-center">{{ trans('project::view.Level') }}</th>
                    <th class="text-center">{{ trans('project::view.Billable Effort') }}</th>
                    @if ($isViewPrivateRewardBudget || $isEditRewardBudget || ($isReviewBudget && $isLeader) || ($isApproveBudget && $isCoo))
                        <th class="text-center">{{ trans('project::view.Norm') }}</th>
                    @endif
                    <th class="text-center">{{ trans('project::view.Budget reward') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($evaluationLabel as $evaluationKey => $evaluationItem)
                    <?php
                    $rewardLevelItem = $rewardEvalUnitConfig[$evaluationKey] * $billableEffort;
                    ?>
                    <tr>
                        <td class="text-center">
                            {{ $evaluationItem }}
                        </td>
                        <td class="text-right">
                            {{ $projectPointInformation['cost_billable_effort'] }}
                        </td>
                        <?php // dd($isViewPrivateRewardBudget) ?>
                        @if ($isViewPrivateRewardBudget || $isEditRewardBudget || ($isReviewBudget && $isLeader) || ($isApproveBudget && $isCoo))
                            <td class="text-right">
                                {{ number_format($rewardLevelItem) }}
                            </td>
                            <td class="text-right">
                                @if ($project->isOpen() && ($isEditRewardBudget || ($isReviewBudget && $isLeader) || ($isApproveBudget && $isCoo)))
                                <input name="reward_budget[{{ $evaluationKey }}]" 
                                    value="{{ (isset($rewardBudgetsDb[$evaluationKey])) ? number_format($rewardBudgetsDb[$evaluationKey]) : number_format($rewardLevelItem) }}"
                                    class="input-number-format form-control input-reward-budget" data-block-form="1" data-reward-level="{{ $evaluationKey }}" />
                                @else
                                    {{ (isset($rewardBudgetsDb[$evaluationKey])) ? number_format($rewardBudgetsDb[$evaluationKey]) : '' }}
                                @endif
                            </td>
                        @else
                            <td class="text-right">
                                {{ (isset($rewardBudgetsDb[$evaluationKey])) ? number_format($rewardBudgetsDb[$evaluationKey]) : '' }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($project->isOpen())
        <div class="row">
            <div class="col-md-12 text-center margin-top-20">
                
                @if ($isReviewBudget)
                    @if ( $isLeader || $isEditRewardBudget)
                    <button type="button" class="btn btn-primary post-ajax"
                        data-url-ajax="{{ URL::route('project::reward.budget.save', ['id' => $project->id, 'save' => 1]) }}"
                        data-block-form-submit="1">
                        {{ trans('project::view.Save') }}
                        <i class="fa fa-spin fa-refresh submit-ajax-refresh-btn hidden"></i>
                    </button>
                    <button type="button" class="btn btn-primary margin-left-15 post-ajax"
                        data-url-ajax="{{ URL::route('project::reward.budget.save', ['id' => $project->id]) }}"
                        data-block-form-submit="1">
                            <span class="btn-reward-public-text">{{ trans('project::view.Submit') }}</span>
                        <i class="fa fa-spin fa-refresh submit-ajax-refresh-btn hidden"></i>
                    </button>
                    @endif
                @elseif ($isApproveBudget)
                    @if($isCoo || $isEditRewardBudget)
                    <button type="button" class="btn btn-primary post-ajax"
                        data-url-ajax="{{ URL::route('project::reward.budget.save', ['id' => $project->id, 'save' => 1]) }}"
                        data-block-form-submit="1">
                        {{ trans('project::view.Save') }}
                        <i class="fa fa-spin fa-refresh submit-ajax-refresh-btn hidden"></i>
                    </button>
                    <button type="button" class="btn btn-primary margin-left-15 post-ajax"
                        data-url-ajax="{{ URL::route('project::reward.budget.save', ['id' => $project->id]) }}"
                        data-block-form-submit="1">
                        @if ($projectMeta->isShowRewardBudget())
                            <span class="btn-reward-public-text">{{ trans('project::view.Private') }}</span>
                        @else
                            <span class="btn-reward-public-text">{{ trans('project::view.Approve') }}</span>
                        @endif
                        <i class="fa fa-spin fa-refresh submit-ajax-refresh-btn hidden"></i>
                    </button>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>