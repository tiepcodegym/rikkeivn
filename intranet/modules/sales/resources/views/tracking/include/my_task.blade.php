<?php
$multi = ' multiple="multiple"';
if ($accessEditTask) {
    $disabledAssign = '';
    $disabledParticipant = '';
} else {
    $disabledAssign = ' disabled';
    $disabledParticipant = ' disabled';
}
?>
@if ($accessEditTask)
<div class="rows">
    <a class="btn btn-edit button_tracking" data-toggle="modal" data-target="#taskModal"><span class="glyphicon glyphicon-plus"></span> &nbsp;<span>{{ trans('sales::view.Create new') }}</span></a>
</div>
@endif
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="tbl-my-task">
        <thead>
            <tr>
                <th class="no-sort">{{ trans('sales::view.No.') }}</th>
                <th class="title">{{ trans('sales::view.Task') }}</th>
                <th class="title">{{ trans('project::view.Assignee') }}</th>
                <th>{{ trans('sales::view.Project') }}</th>
                <th>{{ trans('project::view.PM') }}</th>
                <th>{{ trans('sales::view.Status') }}</th>
                <th>{{ trans('sales::view.Priority') }}</th>
                <th>{{ trans('sales::view.Created date') }}</th>
                <th>{{ trans('sales::view.Deadline date') }}</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>
</div>
<table class="hidden" id="tbl-my-task2">
    <thead>
        <tr class="row-filter">
            <th></th>
            <th>
                <input type="text" class="form-control filter-title" />
            </th>
            <th>
                <input type="text" class="form-control filter-assignee" />
            </th>
            <th>
                <input type="text" class="form-control filter-project_name" />
            </th>
            <th>
                <input type="text" class="form-control filter-pm" />
            </th>
            <th>
                <select class="form-control filter-status">
                    <option>&nbsp;</option>
                    @foreach ($taskStatus as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </th>
            <th>
                <select class="form-control filter-priority">
                    <option>&nbsp;</option>
                    @foreach ($taskPriority as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </th>
            <th>
                <input type="text" class="form-control dateMyTasks filter-created_at" />
            </th>
            <th>
                <input type="text" class="form-control dateMyTasks filter-duedate" />
            </th>
        </tr>
    </thead>
</table>
@include('sales::tracking.include.modal.task')
