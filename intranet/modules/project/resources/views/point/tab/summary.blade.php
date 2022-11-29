<?php
use Rikkei\Core\View\View;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Project;
?>
<div class="row summary-tab">
    <div class="col-md-12">
        @if ($reportSubmitAvai && $project->type == Project::TYPE_ONSITE)
        <div class="row">
            <div class="col-md-12">
                <p></p>
                <b>{!! trans('project::view.Double click on the color to change it') !!}</b>
                <p></p>
            </div>
        </div>
        @endif
        @include('project::point.tab.summary_data')
    </div>
</div>
@include('project::point.tab.add_noti_remove_issue_list')
