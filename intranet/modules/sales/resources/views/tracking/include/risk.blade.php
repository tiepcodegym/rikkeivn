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
    <a class="btn btn-edit button_tracking" data-toggle="modal" data-target="#riskModal"><span class="glyphicon glyphicon-plus"></span> &nbsp;<span>{{ trans('sales::view.Create new') }}</span></a>
</div>
@endif
<div class="table-responsive">
    <table class="table dataTable table-bordered table-hover table-grid-data" id="tbl-risk">
        <thead>
            <tr>
                <th class="no-sort">{{ trans('sales::view.No.') }}</th>
                <th>{{ trans('sales::view.Project') }}</th>
                <th>{{ trans('sales::view.Content') }}</th>
                <th class="">{{ trans('sales::view.Weakness') }}</th>
                <th class="">{{ trans('sales::view.Level important') }}</th>
                <th class="">{{ trans('sales::view.Owner') }}</th>
                <th class="">{{ trans('sales::view.Status') }}</th>
                <th class="">{{ trans('sales::view.Task') }}</th>
                <th class="no-sort"></th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>
</div>
<table class="hidden" id="tbl-risk2">
    <thead>
        <tr class="row-filter">
            <th></th>
            <th>
                <input type="text" class="form-control filter-project" />
            </th>
            <th>
                <input type="text" class="form-control filter-content" />
            </th>
            <th>
                <input type="text" class="form-control filter-weakness" />
            </th>
            <th>
                <select class="form-control filter-level_important">
                    <option>&nbsp;</option>
                    @foreach ($levelsImportant as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </th>
            <th>
                <input type="text" class="form-control filter-owner" />
            </th>
            <th>
                <select class="form-control filter-status">
                    <option>&nbsp;</option>
                    @foreach ($riskStatus as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </th>
            <th></th>
            <th></th>
        </tr>
    </thead>
</table>
@include('sales::tracking.include.modal.risk')
@include('sales::tracking.include.modal.risk_child')
