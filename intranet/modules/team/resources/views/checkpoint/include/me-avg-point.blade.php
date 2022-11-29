<?php
use Rikkei\Me\Model\ME as MeEvaluation;
$sepMonth = Carbon\Carbon::parse(config('project.me_sep_month'))
        ->addMonthNoOverflow();
?>

@if (isset($avgMePoint))
<div class="row-info">
    {{ trans('team::view.Checkpoint.Make.Average ME last 6 month') }} 
    <strong class="text-blue">{{ $avgMePoint . '/' . MeEvaluation::MAX_POINT_NEW }}</strong> 
    ({{ MeEvaluation::getContributeLabel($avgMePoint, $sepMonth) }})
</div>
@endif

