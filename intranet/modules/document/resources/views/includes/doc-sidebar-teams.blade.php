<?php
use Rikkei\Document\View\DocConst;
?>

<div class="box-header with-border">
    <h3 class="box-title">{{ trans('doc::view.View by team') }}</h3>
</div>

<div class="box-body">
    <ul class="list-unstyled list-types-bar">
        {!! DocConst::toNestedList($teamList, null, 0, $teamDoc ? $teamDoc->id : null) !!}
    </ul>
</div>