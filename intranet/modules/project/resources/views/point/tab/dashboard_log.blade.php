<?php 
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Risk;
use Rikkei\Core\View\View as ViewCore;

$collectionModel = $dashboardLogs;
?>
<!-- Dashboard log -->

<div class="grid-data-query task-list-ajax"
     data-type="{{ Task::TYPE_CRITICIZED . Task::TYPE_ISSUE_COST . Task::TYPE_ISSUE_QUA . Task::TYPE_ISSUE_TL . Task::TYPE_ISSUE_PROC }}">
    <h3>{{ trans('project::view.Issues list') }}&nbsp; <i class="fa fa-spin fa-refresh"></i>
    </h3>
    <div class="grid-data-query-table"></div>
</div>

<div class="grid-data-query risk-list-ajax"
     data-type="{{ Risk::TYPE_QUALITY . Risk::TYPE_PROCESS . Risk::TYPE_COST . Risk::TYPE_DELIVERY }}">
    <h3>{{ trans('project::view.Risk list') }}&nbsp; <i class="fa fa-spin fa-refresh"></i>
    </h3>
    <div class="grid-data-query-table"></div>
</div>


