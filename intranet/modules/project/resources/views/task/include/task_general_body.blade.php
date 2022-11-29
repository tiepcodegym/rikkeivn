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

<div class="row">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                @include('project::task.edit_fields.title')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('project::task.edit_fields.status')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('project::task.edit_fields.due_date')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('project::task.edit_fields.actual_date')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('project::task.edit_fields.content')
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <?php $createdAt = \Carbon\Carbon::parse($taskItem->created_at)->format('Y-m-d'); ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label required">{{ trans('project::view.Create date') }}</label>
                    <div class="col-md-9">
                        <p class="form-control-static">{{ $createdAt }}</p>
                    </div>
                </div>
                <input type="hidden" name="task[created_at]" value="{{ $createdAt }}" />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('project::task.edit_fields.assignee')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('project::task.edit_fields.priority')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('project::task.edit_fields.participant')
            </div>
        </div>
    </div>
</div>

@if (isset($isPopup))
<input type="hidden" name="is_popup" value="1">
@endif

@if ($accessEditTask)
    <div class="row">
        <div class="col-md-12 align-center">
           <button class="btn-add" type="submit">
               {{trans('project::view.Save')}}
               <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
           </button>
        </div>
    </div>
@endif