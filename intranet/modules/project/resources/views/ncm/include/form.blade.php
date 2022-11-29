<?php
use Rikkei\Project\View\GeneralProject;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\TaskNcmRequest;
use Carbon\Carbon;
use Rikkei\Project\Model\Task;

$userCurrent = Permission::getInstance()->getEmployee();
$ncmRequestResultLabels = TaskNcmRequest::getTestResultLabels();
?>
@if($accessEditTask)
<form id="form-task-ncm-edit" method="post" action="{{ route('project::task.ncm.save') }}" 
    class="form-horizontal form-submit-ajax has-valid" autocomplete="off">
    {!! csrf_field() !!}
    <input type="hidden" name="project_id" value="{{ $project->id }}">
    @if ($taskItem->id)
        <input type="hidden" name="id" value="{{ $taskItem->id }}" />
    @endif
@else
<div class="form-horizontal">
@endif
<div class="box box-info">
    <!-- row request -->
    <div class="row">
        <div class="col-sm-12">
                <div class="box-header with-border">
                    <h3 class="box-title" style="margin-right: 8px;">{{ trans('project::view.ncl.Request') }}</h3>
                    @if ($taskItem->id)
                    <div class="btn-group">
                        <a href="{{ route('project::ncm.pdf',['download' => 'pdf', 'taskId' => $taskItem->id]) }}" class="btn btn-info button-exportPDF ">
                            <span class="glyphicon glyphicon-download-alt"></span> Export to PDF
                        </a>
                    </div>
                    @endif
                </div>
        </div>
    </div>
    <div class="box-body">
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group form-group-nmargin">
                <label for="task_title" class="control-label required col-md-2">{{ trans('project::view.ncl.Title') }}@if (!$viewMode)<em>*</em>@endif</label>
                <div class="input-box col-md-10">
                    @if($accessEditTask)
                        <input class="form-control" name="task[title]" id="task_title" value="{{ $taskItem->title }}" />
                    @else
                        <p class="form-control-static">{{ $taskItem->title }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="col-md-6">
                <div class="form-group form-group-select2 team-dropdown">
                    <label for="teams" class="col-md-4 control-label required">{{trans('project::view.ncl.Related department')}}@if (!$viewMode)<em>*</em>@endif</label>
                    <div class="fg-valid-custom col-md-8">
                        @if($accessEditTask)
                            <select id="teams" class="hidden bootstrap-multiselect" name="teams[]" multiple="multiple">
                                @foreach($teamsOptionAll as $option)
                                    <option value="{{ $option['value'] }}"{{ in_array($option['value'], $teamsSelected) ? ' selected' : '' }}>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        @else
                            <p class="form-control-static">{{ $teamsSelected }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="ncm_request_date" class="col-md-3 control-label required">{{trans('project::view.ncl.Request date')}}@if (!$viewMode)<em>*</em>@endif</label>
                    <div class="col-md-9">
                        @if($accessEditTask)
                            <input class="form-control date-picker" name="ncm[request_date]" value="{{ CoreView::getDate($taskNcmRequest->request_date) }}" id="ncm_request_date" />
                        @else
                            <p class="form-control-static">{{ CoreView::getDate($taskNcmRequest->request_date) }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="ncm-document" class="col-md-4 control-label">{{ trans('project::view.ncl.Related document') }}</label>
                    <div class="col-md-8">
                        @if($accessEditTask)
                            <input class="form-control" name="ncm[document]" value="{{ $taskNcmRequest->document }}" id="ncm-document" />
                        @else
                            <p class="form-control-static">{{ $taskNcmRequest->document }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="ncm-request_standard" class="col-md-3 control-label">{{trans('project::view.ncl.Request standard')}}</label>
                    <div class="col-md-9">
                        @if($accessEditTask)
                            <input class="form-control" name="ncm[request_standard]" value="{{ $taskNcmRequest->request_standard }}" id="ncm-request_standard" />
                        @else
                            <p class="form-control-static">{{ $taskNcmRequest->request_standard }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group form-group-nmargin">
                <label for="task-content" class="control-label required col-md-2">{{ trans('project::view.ncl.Describe the mismatch') }} @if (!$viewMode)<em>*</em>@endif</label>
                <div class="input-box col-md-10">
                    @if($accessEditTask)
                        <textarea class="form-control text-resize-y" name="task[content]" id="task-content" rows="4">{{ $taskItem->content }}</textarea>
                    @else
                        <textarea class="form-control text-resize-y" rows="4" disabled>{{ $taskItem->content }}</textarea>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group form-group-select2 form-group-nmargin">
                <label for="ncm_requester" class="col-md-2 control-label required">{{trans('project::view.ncl.Requester')}}@if (!$viewMode)<em>*</em>@endif</label>
                <div class="col-md-10 fg-valid-custom">
                    @if($accessEditTask)
                        <select class="form-control select-search" name="ncm[requester]" value="{{ $taskNcmRequest->requester }}"
                            id="ncm_requester" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}?fullName=1">
                            @if (!$taskItem->id)
                                <option value="{{ $userCurrent->id }}" selected>{{ CoreView::getNickname($userCurrent->email) . ' (' . $userCurrent->name . ')' }}</option>
                            @elseif ($taskNcmRequest->requester)
                                <option value="{{ $taskNcmRequest->requester }}" selected>{{ CoreView::getNickname($taskNcmRequest->requester_email) . ' (' . $taskNcmRequest->requester_name . ')'  }}</option>
                            @endif
                        </select>
                    @elseif ($taskNcmRequest->requester)
                        <p class="form-control-static">{{ CoreView::getNickname($taskNcmRequest->requester_email) . ' (' . $taskNcmRequest->requester_name . ')'  }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <label for="task-fix_reason" class="control-label required col-md-2">{{ trans('project::view.ncl.Reason') }} @if (!$viewMode)<em>*</em>@endif</label>
            <div class="form-group form-group-nmargin col-md-10">
                <div class="input-box">
                    @if($accessEditTask)
                        <textarea class="form-control text-resize-y" name="ncm[fix_reason]" id="task-fix_reason" rows="4">{{ $taskNcmRequest->fix_reason }}</textarea>
                    @else
                        <textarea class="form-control text-resize-y" rows="4" disabled>{{ $taskNcmRequest->fix_reason }}</textarea>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <label for="task-fix_content" class="control-label col-md-2">{{ trans('project::view.ncl.Corrective action') }}</label>
            <div class="form-group form-group-nmargin col-md-10">
                <div class="input-box">
                    @if($accessEditTask)
                        <textarea class="form-control text-resize-y" name="ncm[fix_content]" id="task-fix_content" style="height: 99px;">{{ $taskNcmRequest->fix_content }}</textarea>
                    @else
                        <textarea class="form-control text-resize-y" style="height: 99px;" disabled>{{ $taskNcmRequest->fix_content }}</textarea>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="task_duedate" class="control-label required col-md-4">{{ trans('project::view.ncl.Estimated date') }}@if (!$viewMode)<em>*</em>@endif</label>
                    <div class="input-box col-md-8">
                        @if($accessEditTask)
                            <input class="form-control date-picker" name="task[duedate]" id="task_duedate" value="{{ CoreView::getDate($taskItem->duedate) }}" />
                        @else
                            <p class="form-control-static">{{ CoreView::getDate($taskItem->duedate) }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group form-group-select2">
                    <label for="task_assign-depart_represent" class="col-md-3 control-label">{{trans('project::view.ncl.Department represent')}}</label>
                    <div class="col-md-9 fg-valid-custom">
                        @if($accessEditTask)
                            <select class="form-control select-search" name="task_assign[depart_represent]" value="{{ $taskAssign->depart_represent }}"
                                id="task_assign-depart_represent" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}?fullName=1">
                                @if ($taskAssign->depart_represent)
                                    <option value="{{ $taskAssign->depart_represent }}" selected>{{ CoreView::getNickname($taskAssign->depart_represent_email) . ' (' . $taskAssign->depart_represent_name . ')'  }}</option>
                                @endif
                            </select>
                        @elseif ($taskAssign->depart_represent)
                            <p class="form-control-static">{{ CoreView::getNickname($taskAssign->depart_represent_email) . ' (' . $taskAssign->depart_represent_name . ')'  }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- end box request -->
    </div>
</div>
    
    <!-- box test and result -->
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('project::view.ncl.Test and result') }}</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="col-md-6">
                                <div class="form-group form-group-select2">
                                    <label for="ncm-test_result" class="col-md-4 control-label">{{trans('project::view.ncl.Test result')}}</label>
                                    <div class="col-md-8 fg-valid-custom">
                                        @if($accessEditTask)
                                            <select class="form-control select-search" name="ncm[test_result]" value="{{ $taskNcmRequest->test_result }}"
                                                id="ncm-test_result">
                                                    <option>&nbsp;</option>
                                                @foreach ($ncmRequestResultLabels as $key => $value)
                                                    <option value="{{ $key }}"@if ($key == $taskNcmRequest->test_result) selected @endif>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <p class="form-control-static">{{ isset($ncmRequestResultLabels[$taskNcmRequest->test_result]) ? $ncmRequestResultLabels[$taskNcmRequest->test_result] : '' }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-group-select2">
                                    <label for="task_status" class="col-md-3 control-label">{{trans('project::view.ncl.Status')}}</label>
                                    <div class="col-md-9 fg-valid-custom">
                                        @if($accessEditTask)
                                            <select class="form-control select-search" name="task[status]" value="{{ $taskItem->status }}"
                                                id="task_status">
                                                @foreach ($taskStatusAll as $key => $value)
                                                    <option value="{{ $key }}"@if ($key == $taskItem->status) selected @endif>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <p class="form-control-static">{{ $taskItem->getStatus() }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <label for="ncm-next_measure" class="control-label col-md-2">{{ trans('project::view.ncl.Next measure') }}</label>
                            <div class="form-group form-group-nmargin col-md-10">
                                <div class="input-box">
                                    @if($accessEditTask)
                                        <textarea class="form-control text-resize-y" name="ncm[next_measure]" id="task-next_measure" style="height: 99px;">{{ $taskNcmRequest->next_measure }}</textarea>
                                    @else
                                        <textarea class="form-control text-resize-y" style="height: 99px;" disabled>{{ $taskNcmRequest->next_measure }}</textarea>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="task-actual_date" class="control-label col-md-4">{{ trans('project::view.ncl.Test date') }}</label>
                                    <div class="input-box col-md-8">
                                        @if($accessEditTask)
                                            <input class="form-control date-picker" name="task[actual_date]" id="task-actual_date" value="{{ CoreView::getDate($taskItem->actual_date) }}" />
                                        @else
                                            <p class="form-control-static">{{ CoreView::getDate($taskItem->actual_date) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-group-select2">
                                    <label for="task_assign-tester" class="col-md-3 control-label">{{trans('project::view.ncl.Tester')}}</label>
                                    <div class="col-md-9 fg-valid-custom">
                                        @if($accessEditTask)
                                            <select class="form-control select-search" name="task_assign[tester]" value="{{ $taskAssign->tester }}"
                                                id="task_assign-tester" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}?fullName=1">
                                                @if ($taskAssign->tester)
                                                    <option value="{{ $taskAssign->tester }}" selected>{{ CoreView::getNickname($taskAssign->tester_email) . ' (' . $taskAssign->tester_name . ')'  }}</option>
                                                @endif
                                            </select>
                                        @elseif ($taskAssign->tester)
                                            <p class="form-control-static">{{ CoreView::getNickname($taskAssign->tester_email) . ' (' . $taskAssign->tester_name . ')'  }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end box test and result -->
    
    <!-- box evaluate effect -->
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('project::view.ncl.Evaluation efficiency') }}</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <label for="ncm-evaluate_effect" class="control-label col-md-2">{{ trans('project::view.ncl.Content') }}</label>
                            <div class="form-group form-group-nmargin">
                                <div class="input-box col-md-10">
                                    @if($accessEditTask)
                                        <textarea class="form-control text-resize-y" name="ncm[evaluate_effect]" id="task-evaluate_effect" style="height: 99px;">{{ $taskNcmRequest->evaluate_effect }}</textarea>
                                    @else
                                        <textarea class="form-control text-resize-y" style="height: 99px;" disabled>{{ $taskNcmRequest->evaluate_effect }}</textarea>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ncm-evaluate_date" class="control-label col-md-4">{{ trans('project::view.ncl.Evaluate date') }}</label>
                                    <div class="input-box col-md-8">
                                        @if($accessEditTask)
                                            <input class="form-control date-picker" name="ncm[evaluate_date]" id="ncm-evaluate_date" value="{{ CoreView::getDate($taskNcmRequest->evaluate_date) }}" />
                                        @else
                                            <p class="form-control-static">{{ CoreView::getDate($taskNcmRequest->evaluate_date) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-group-select2">
                                    <label for="task_assign-evaluater" class="col-md-3 control-label">{{trans('project::view.ncl.Evaluator')}}</label>
                                    <div class="col-md-9 fg-valid-custom">
                                        @if($accessEditTask)
                                            <select class="form-control select-search" name="task_assign[evaluater]" value="{{ $taskAssign->evaluater }}"
                                                id="task_assign-evaluater" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}?fullName=1">
                                                @if ($taskAssign->evaluater)
                                                    <option value="{{ $taskAssign->evaluater }}" selected>{{ CoreView::getNickname($taskAssign->evaluater_email) . ' (' . $taskAssign->evaluater_name . ')'  }}</option>
                                                @endif
                                            </select>
                                        @elseif ($taskAssign->evaluater)
                                            <p class="form-control-static">{{ CoreView::getNickname($taskAssign->evaluater_email) . ' (' . $taskAssign->evaluater_name . ')'  }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end box evaluate effect -->
    
    <div class="row">
        <div class="col-md-12 align-center">
            @if($accessEditTask)
                @if ($taskItem->id)
                    <button type="submit" class="btn-add">
                    {{ trans('project::view.Save') }}
                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                    <button type="button" class="btn-delete post-ajax delete-confirm margin-left-10" 
                        data-url-ajax="{{ URL::route('project::task.ncm.delete', ['id' => $taskItem->id]) }}"
                        data-noti="{{ trans('project::view.Are you sure delete item?') }}">
                        {{ trans('project::view.Delete') }}
                    </button>
                @else
                    <button type="submit" class="btn-add">
                    {{ trans('project::view.Create') }}
                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                @endif
            @endif
            @if (!$viewMode)
                <button type="button" class="btn btn-close margin-left-10" data-dismiss="modal">{{ trans('project::view.Close') }}</button>
            @else
                <a href="{{ URL::route('project::report.ncm') }}" class="btn btn-add">{{ trans('project::view.Back') }}</a>
            @endif
            <br><br>
        </div>
    </div>
@if($accessEditTask)
</form>
@else
</div>
@endif
<!-- start box comment and history -->
@if(isset($taskItem->id))
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Comment and History</h3>
            </div>
            <div class="box-body">
                    @include ('project::task.include.comment')
            </div>
        </div>
    </div>
</div>
    <!-- end box comment and history -->
@endif
@if (!$viewMode)
<script type="text/javascript">
    var requireCmt = '{{ trans('project::view.Kindly add comments') }}';
    jQuery(document).ready(function ($) {
        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        RKfuncion.select2.init({
            enforceFocus: true
        });
        RKfuncion.bootstapMultiSelect.init({
            nonSelectedText: '{{ trans('project::view.ncl.Choose items') }}',
            allSelectedText: '{{ trans('project::view.All') }}',
            nSelectedText: '{{ trans('project::view.items selected') }}',
        });
        if ($('#form-task-ncm-edit').length) {
            $('#form-task-ncm-edit').validate({
                rules: {
                    'task[title]': {
                        required: true,
                        maxlength: 255
                    },
                    'teams[]': {
                        required: true
                    },
                    'ncm[request_date]': {
                        required: true
                    },
                    'ncm[requester]': {
                        required: true
                    },
                    'task[duedate]': {
                        required: true,
                        greaterEqual: 'input[name^="ncm[request_date]"'
                    },
                    'task[content]': {
                        required: true
                    },
                    'ncm[fix_reason]': {
                        required: true
                    },
                    'ncm[document]': {
                        maxlength: 255
                    },
                    'ncm[request_standard]': {
                        maxlength: 255
                    },
                    'task[actual_date]': {
                        greaterEqual: 'input[name^="ncm[request_date]"'
                    },
                    'ncm[evaluate_date]': {
                        greaterEqual: 'input[name^="ncm[request_date]"'
                    }
                },
                ignore: ':hidden:not("#teams")',
                errorPlacement: function (error, element) {
                    if (element.attr("name") == "team_id[]") {
                        $('.#teams').parent().append(error)
                    } else {
                        error.insertAfter(element);
                    }
                },
                messages: {
                    'ncm[evaluate_date]': {
                        greaterEqual: '{{ trans('project::view.Please enter a value greater or equal "Request date"') }}'
                    },
                    'task[actual_date]': {
                        greaterEqual: '{{ trans('project::view.Please enter a value greater or equal "Request date"') }}'
                    },
                    'task[duedate]': {
                        greaterEqual: '{{ trans('project::view.Please enter a value greater or equal "Request date"') }}'
                    }
                }
            });
        }
        if ($('#form-task-comment').length) {
            formNcmValid = $('#form-task-comment').validate({
                rules: {
                    'tc[content]': "required",
                },
                messages: {
                    'tc[content]': requireCmt,
                }
            });
        }
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
        $('#comment').val('');
        var e = jQuery.Event("keypress");
        e.keyCode = $.ui.keyCode.ENTER;
        $('#modal-ncm-editor input[name="page"]').val(1).trigger(e);
        if (typeof formNcmValid === 'object' && typeof formNcmValid.resetForm === 'function') {
            formNcmValid.resetForm();
        }
    }

</script>
@endif
