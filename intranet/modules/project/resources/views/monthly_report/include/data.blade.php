<?php
use Rikkei\Project\Model\MonthlyReport;
use Rikkei\Team\View\Permission;

$rowNa = MonthlyReport::ROW_NA;
$rowPlanRevenue = MonthlyReport::ROW_PLAN_REVENUE;
$rowApprovedCost = MonthlyReport::ROW_APPROVED_COST;
$rowBillEffort = MonthlyReport::ROW_BILLABLE_EFFORT;
$rowApprovedProdCost = MonthlyReport::ROW_APPROVED_PROD_COST;
$rowCost = MonthlyReport::ROW_COST;
$rowPlan = MonthlyReport::ROW_PLAN;
$rowActual = MonthlyReport::ROW_ACTUAL;
$rowBillActual = MonthlyReport::ROW_BILLACTUAL;
$rowBillStaffPlan = MonthlyReport::ROW_BILL_STAFF_PLAN;
$rowBillStaffActual = MonthlyReport::ROW_BILL_STAFF_ACTUAL;
$rowBusinessEffective = MonthlyReport::ROW_BUSINESS_EFFECTIVE;
$rowCompletedPlan = MonthlyReport::ROW_COMPLETED_PLAN;
$rowBusyRate = MonthlyReport::ROW_BUSY_RATE;
$rowProjectPoint = MonthlyReport::ROW_PROJECT_POINT;
$rowCssPoint = MonthlyReport::ROW_CSS_POINT;
$rowCssImprovement = MonthlyReport::ROW_CSS_IMPROVEMENT;
$rowHrPlan = MonthlyReport::ROW_HR_PLAN;
$rowCusComment = MonthlyReport::ROW_COMMENT;
$rowHrActual = MonthlyReport::ROW_HR_ACTUAL;
$rowHrOut = MonthlyReport::ROW_HR_OUT;
$rowHrCompletedPlan = MonthlyReport::ROW_HR_COMPLETED_PLAN;
$rowHrTurnOverate = MonthlyReport::ROW_HR_TURN_OVERATE;
$rowTrainingPlan = MonthlyReport::ROW_TRAINING_PLAN;
$rowTrainingActual = MonthlyReport::ROW_TRAINING_ACTUAL;
$rowTrainingPoint = MonthlyReport::ROW_TRAINING_POINT;
$rowLanguageIndex = MonthlyReport::ROW_LANGUAGE_INDEX;
$rowAvgLanguageIndex = MonthlyReport::ROW_AVG_LANGUAGE_INDEX;
$rowAvgSocialActivity = MonthlyReport::ROW_AVG_SOCIAL_ACTIVITY;
$rowAlloStaffActual = MonthlyReport::ROW_ALLO_STAFF_ACTUAL;
$rowProdStaffActual = MonthlyReport::ROW_PROD_STAFF_ACTUAL;
$updatePemission = Permission::getInstance()->isAllow('project::monthly.report.update');
?>

@if (count($values))
<div class="nav-tabs-custom monthly-report-page margin-top-10">
    <div class="nav-tabs-custom monthly-report-page">
        <ul class="nav nav-tabs">
            @php $index = 0; @endphp
            @foreach ($teamsByPermission as $team)
            <li class="{{ $index == 0 ? 'active' : '' }}"><a href="#{{ $team->id }}" data-toggle="tab" aria-expanded="false">{{ $team->name }}</a></li>
            @php $index++; @endphp
            @endforeach
        </ul>
        <div class="tab-content">
            @php $index = 0; @endphp
            @foreach ($teamsByPermission as $team)
            <div class="tab-pane tab-pane-team {{ $index == 0 ? 'active' : '' }}" id="{{ $team->id }}" data-team='{{ $team->id }}'>
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#{{ $team->id }}_business" data-toggle="tab" aria-expanded="false">Business</a></li>
                    <li class=""><a href="#{{ $team->id }}_operation" data-toggle="tab" aria-expanded="false">Operation</a></li>
                    <li class=""><a href="#{{ $team->id }}_hr" data-toggle="tab" aria-expanded="false">HR</a></li>
                    <li class=""><a href="#{{ $team->id }}_training" data-toggle="tab" aria-expanded="false">Training</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="{{ $team->id }}_business" data-team='{{ $team->id }}'>
                        @include('project::monthly_report.include.business')
                    </div>
                    <div class="tab-pane" id="{{ $team->id }}_operation" data-team='{{ $team->id }}'>
                        @include('project::monthly_report.include.operation')
                    </div>
                    <div class="tab-pane" id="{{ $team->id }}_hr" data-team='{{ $team->id }}'>
                        @include('project::monthly_report.include.hr')
                    </div>
                    <div class="tab-pane" id="{{ $team->id }}_training" data-team='{{ $team->id }}'>
                        @include('project::monthly_report.include.training')
                    </div>
                </div>
            </div>
            <!-- /.tab-pane -->
            @php $index++; @endphp
            @endforeach
        </div>
        <!-- /.tab-content -->
    </div>
</div>
@else
<h3>{{ trans('project::view.No data') }}</h3>
@endif
