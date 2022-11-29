@extends('layouts.default')

@section('title')
<?php
use Rikkei\Project\View\GeneralProject;
?>
@if ($taskItem->isTaskCustomerIdea())
    @if (!$taskItem->id)
        {{ trans('project::view.Customer feedback') }}
    @else
        {{ $taskItem->getType() }}
    @endif
@else
    {{ $taskItem->getType() }}
@endif

@if ($taskItem->id)
    {{ trans('project::view.edit') }}
@else
    {{ trans('project::view.create') }}
@endif
@if (isset($pmActive) && $pmActive)
{{ ' - PM: ' . GeneralProject::getNickName($pmActive->email) }}
@endif
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
@endsection

@section('content')
<?php
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
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="box-body-header bh-split">
                    <h2 class="box-body-title">{{ trans('project::view.Project') }}: {{ $project->name }}</h2>
                    <div class="filter-panel-left panel-left-link">
                        <a href="{{ URL::route('project::point.edit', ['id' => $project->id]) }}" target="_blank">{{ trans('project::view.Project report') }}</a>
                        <a href="{{ URL::route('project::project.edit', ['id' => $project->id]) }}" target="_blank">{{ trans('project::view.View workorder') }}</a>
                    </div>
                    <div class="clearfix"></div>
                </div>
                @include ('project::task.include.task_body')
            </div>
        </div>
    </div>
</div>
@include ('project::task.include.comment', ['collectionModel' => $taskCommentList])
@endsection

@section('script')
<div class="cmt-wrapper hidden">
    <div class="item">
        <p class="author">
            <strong class="cmt-created_by"></strong>
            <i>{{ trans('project::view.at') }} <span class="cmt-created_at"><span></i>
        </p>
        <p class="comment white-space-pre"><p>
    </div>
</div>
<script>
    @if (isset($project) && $project->id)
    var globalPassModule = {
        teamProject: '{{ isset($teamsProject) && $teamsProject ? $teamsProject : '' }}'
    }
    @endif
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        RKfuncion.select2.init();
    });
</script>
<script type="text/javascript">
    var userNameCurrent = '{{ $curEmp->name }}';
    var userEmailCurrent = '{{ $curEmp->email }}';
    var emailCurrent = userEmailCurrent.slice(0, userEmailCurrent.indexOf('@'));
    jQuery(document).ready(function($) {
        var e = jQuery.Event("keypress");
        e.keyCode = $.ui.keyCode.ENTER;
        $('input[name="page"]').trigger(e);

        $('#comment').keydown(function(e) {
            var content = $('#comment').val();
                var key = e.which;
                if (key === 13) {
                 // As ASCII code for ENTER key is "13"
                if (e.shiftKey) {
                 if (content =='') {
                       return false;
                 } else {
                       $(this).val($(this).val() + '\n');
                 }
                } else {
                  $('#form-task-comment').submit();
                }

                return false;
            }
        });
    });
    RKfuncion.formSubmitAjax['commentSuccess'] = function (dom, data) {
        var created_by = userNameCurrent+' ('+emailCurrent+') ';
        $('div.cmt-wrapper strong.cmt-created_by').text(created_by);
        $('div.cmt-wrapper span.cmt-created_at').text(data.created_at);
        $('div.cmt-wrapper .comment').text($('#comment').val().trim());
        var commentHtml = $('.cmt-wrapper').html();
        $('.comment-create-task').prepend(commentHtml);
        $('#comment').val('');
        if (typeof formTaskValid === 'object' && typeof formTaskValid.resetForm === 'function') {
            formTaskValid.resetForm();
        }
        var e = jQuery.Event("keypress");
        e.keyCode = $.ui.keyCode.ENTER;
        $('.comment-create-task input[name="page"]').val(1).trigger(e);
    }

</script>
<?php
//remove flash session
Form::forget();
?>
@endsection
