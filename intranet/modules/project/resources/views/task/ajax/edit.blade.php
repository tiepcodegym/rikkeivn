<?php
use Rikkei\Project\View\GeneralProject;
use Rikkei\Core\View\Form;
use Carbon\Carbon;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\TaskComment;

if (!$project->isOpen() || !$accessEditTask) {
    $disabledAssign = ' disabled';
    $disabledParticipant = 'disabled';
} else {
    $disabledAssign = '';
    $disabledParticipant = '';
}
?>
@if ($taskItem->id)
    <div class="row">
        <div class="col-sm-12">
            <p class="text-right">
                <a href="{{ URL::route('project::task.edit', ['id' => $taskItem->id ]) }}">{{ trans('project::view.View detail') }}</a>
            </p>
        </div>
    </div>
@endif
<div class="row">
    <div class="col-sm-12">
        @include ('project::task.include.task_body')
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        RKfuncion.select2.init();
        RKfuncion.formValidateTask();
    });
</script>