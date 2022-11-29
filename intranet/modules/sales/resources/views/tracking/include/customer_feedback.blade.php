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
    <a class="btn btn-edit button_tracking" data-toggle="modal" data-target="#feedbackModal"><span class="glyphicon glyphicon-plus"></span> &nbsp;<span>{{ trans('sales::view.Create new') }}</span></a>
</div>
@endif
<div class="table-responsive">
    <table class="table dataTable table-bordered table-hover table-grid-data" id="tbl-feedback">
        <thead>
            <tr>
                <th class="no-sort">{{ trans('sales::view.No.') }}</th>
                <th>{{ trans('sales::view.Project') }}</th>
                <th class="">{{ trans('sales::view.Task') }}</th>
                <th class="">{{ trans('sales::view.Status') }}</th>
                <th class="">{{ trans('sales::view.Priority') }}</th>
                <th class="">{{ trans('sales::view.Type') }}</th>
                <th class="">{{ trans('sales::view.Assignee') }}</th>
                <th>{{ trans('sales::view.Created date') }}</th>
                <th>{{ trans('sales::view.Deadline date') }}</th>
                <th>{{ trans('sales::view.Issues') }}</th>
                <th class="no-sort"></th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>

<table class="hidden" id="tbl-feedback2">
    <thead>
        <tr class="row-filter">
            <th>
            </th>
            <th>
                <input type="text" class="form-control filter-project_name" />
            </th>
            <th>
                <input type="text" class="form-control filter-title" />
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
                <select class="form-control filter-type">
                    <option>&nbsp;</option>
                    @foreach ($taskType as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </th>
            <th>
                <input type="text" class="form-control filter-assignee" />
            </th>
            <th>
                <input type="text" class="form-control dateFeedbacks filter-created_at" />
            </th>
            <th>
                <input type="text" class="form-control dateFeedbacks filter-duedate" />
            </th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
</table>
@include('sales::tracking.include.modal.feedback')
@include('sales::tracking.include.modal.feedback_child')
