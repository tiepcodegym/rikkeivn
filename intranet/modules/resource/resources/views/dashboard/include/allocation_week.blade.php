<?php
use Rikkei\Resource\Model\Dashboard;
use Rikkei\Resource\View\View as rView;

?>
<div class="row margin-top-30 margin-bottom-30 notice-color">
    <div class="col-md-12">
        <div class="col-md-2 padding-left-0">
            <div class="notice-color" style="background: {{Dashboard::BG_EFFORT_WHITE}}">effort = 0%</div>
        </div>
        <div class="col-md-2 padding-left-0">
            <div type="button" class="notice-color" style="background: {{Dashboard::BG_EFFORT_YELLOW}}">0% < effort <= 70%</div>
        </div>
        <div class="col-md-2">
            <div type="button" class="notice-color" style="background: {{Dashboard::BG_EFFORT_GREEN}}">70% < effort <= 120%</div>
        </div>
        <div class="col-md-2">
            <div type="button" class="notice-color" style="background: {{Dashboard::BG_EFFORT_RED}}">effort > 120%</div>
        </div>
    </div>
</div>
<div class="position-relative">
    <div class="loader-container hidden"></div>
    <div class="loader hidden"></div>
    <div class="table-data-container">
        @include ('resource::dashboard.include.utilization_data')
    </div>
</div>