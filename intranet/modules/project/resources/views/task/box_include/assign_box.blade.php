<?php
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Task;
?>
<div class="row margin-top-20">
    <div class="col-md-4">
        @if (isset($taskAssigns['role'][TaskAssign::ROLE_PM]))
            <strong>PM:</strong>
            <span>{{ $taskAssigns['role'][TaskAssign::ROLE_PM]['name'] }} 
                ({{ $taskAssigns['role'][TaskAssign::ROLE_PM]['email'] }})</span>
        @endif
    </div>
    <div class="col-md-4">
        @if (isset($taskAssigns['role'][TaskAssign::ROLE_REVIEWER]))
            <strong>Leader:</strong>
            <span>{{ $taskAssigns['role'][TaskAssign::ROLE_REVIEWER]['name'] }} 
                ({{ $taskAssigns['role'][TaskAssign::ROLE_REVIEWER]['email'] }})</span>
        @endif
    </div>
    <div class="col-md-4">
        @if (isset($taskAssigns['role'][TaskAssign::ROLE_APPROVER]))
            <strong>COO:</strong>
            <span>{{ $taskAssigns['role'][TaskAssign::ROLE_APPROVER]['name'] }} 
                ({{ $taskAssigns['role'][TaskAssign::ROLE_APPROVER]['email'] }})</span>
        @endif
    </div>
</div>