<?php
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Task;
?>
<div class="row">
    <div class="col-md-2 workorder">
        @if ($taskItem->status == Task::STATUS_SUBMITTED)
            <div class="callout callout-warning status">
                <p class="text-center text-uppercase"><strong>{{ trans('project::view.Submitted') }}</strong></p>
            </div>
        @elseif ($taskItem->status == Task::STATUS_REVIEWED)
            <div class="callout callout-info status">
                <p class="text-center text-uppercase"><strong>{{ trans('project::view.Reviewed') }}</strong></p>
            </div>
        @elseif ($taskItem->status == Task::STATUS_APPROVED)
            <div class="callout callout-success status">
                <p class="text-center text-uppercase"><strong>{{ trans('project::view.Approved') }}</strong></p>
            </div>
        @elseif ($taskItem->status == Task::STATUS_FEEDBACK)
            <div class="callout callout-danger status">
                <p class="text-center text-uppercase"><strong>{{ trans('project::view.Feedback') }}</strong></p>
            </div>
        @endif
    </div>
</div>