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
                    @if($budgetData)
                        @foreach($budgetData as $key => $budget)
                            @if($key != 'sum')
                                <th class="text-center">{{DATE_FORMAT(date_create($key), 'm-Y')}}</th>
                            @endif
                        @endforeach
                    @else
                        @foreach($monthRewards as $key => $month)
                            <th class="text-center">{{DATE_FORMAT(date_create($month), 'm-Y')}}</th>
                        @endforeach
                    @endif
                    <th class="text-center">Sum</th>
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
                        @if ($isViewPrivateRewardBudget || $isEditRewardBudget || ($isReviewBudget && $isLeader) || ($isApproveBudget && $isCoo))
                            <td class="text-right">
                                {{ number_format($rewardLevelItem) }}
                            </td>
                            @if($budgetData)
                                @foreach($budgetData as $key => $budget)
                                    @if($key != 'sum')
                                        <td class="text-right" level="{{ $evaluationKey }}">
                                            @if (($project->isOpen() && ($isEditRewardBudget || ($isReviewBudget && $isLeader) || ($isApproveBudget && $isCoo)))&& !$project->isDayReward($key))
                                            <input name="reward_budget[{{$key}}][{{ $evaluationKey }}]" 
                                                @if($budgetData) value="{{number_format($budgetData[$key][$evaluationKey])}}" @endif
                                                class="input-number-format form-control input-reward-budget-long" data-block-form="1" data-reward-level="{{ $evaluationKey }}" data-reward-month="{{ $key }}"/>
                                            @else
                                                {{ (isset($budgetData[$key][$evaluationKey])) ? number_format($budgetData[$key][$evaluationKey]) : '' }}
                                                <input @if($budgetData) value="{{number_format($budgetData[$key][$evaluationKey])}}" @endif
                                                class="input-number-format form-control input-reward-budget-long" data-block-form="1" data-reward-level="{{ $evaluationKey }}" data-reward-month="{{ $key }}" type="hidden"/>
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            @else
                                @foreach($monthRewards as $key => $month)
                                <td class="text-right" level="{{ $evaluationKey }}">
                                    @if (($project->isOpen() && ($isEditRewardBudget || ($isReviewBudget && $isLeader) || ($isApproveBudget && $isCoo)))&& !$project->isDayReward($month))
                                        <input name="reward_budget[{{$month}}][{{ $evaluationKey }}]" 
                                        value="{{number_format($rewardLevelItem/count($monthRewards))}}"
                                        class="input-number-format form-control input-reward-budget-long" data-block-form="1" data-reward-level="{{ $evaluationKey }}" data-reward-month="{{ $month }}"/>
                                    @else
                                        {{number_format($rewardLevelItem/count($monthRewards))}}
                                        <input name="reward_budget[{{$month}}][{{ $evaluationKey }}]" value="{{number_format($rewardLevelItem/count($monthRewards))}}" 
                                        class="input-number-format form-control input-reward-budget-long" data-block-form="1" data-reward-level="{{ $evaluationKey }}" data-reward-month="{{ $month }}" type="hidden"/>
                                    @endif
                                </td>
                                @endforeach
                            @endif
                        @else
                            @if($budgetData)
                                @foreach($budgetData as $key => $budget)
                                    @if($key != 'sum')
                                        <td class="text-right" level="{{ $evaluationKey }}">
                                            {{ (isset($budgetData[$key][$evaluationKey])) ? number_format($budgetData[$key][$evaluationKey]) : '' }}
                                        </td>
                                    @endif    
                                @endforeach
                            @else
                                @foreach($monthRewards as $key => $month)
                                <td class="text-right" level="{{ $evaluationKey }}">
                                    {{number_format($rewardLevelItem/count($monthRewards))}}
                                </td>
                                @endforeach
                            @endif
                        @endif
                        @if($budgetData)
                            <td class="text-right" sum-level="{{ $evaluationKey }}"> 
                                {{ (isset($budgetData['sum'][$evaluationKey])) ? number_format($budgetData['sum'][$evaluationKey]) : '' }}
                            </td>
                        @else
                            <td class="text-right" sum-level="{{ $evaluationKey }}"> 
                                {{ number_format($rewardLevelItem)}}
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