<?php

use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\RiskAction;

if (isset($riskInfo) && $riskInfo) {
    $checkEdit = true;
    $urlSubmit = route('project::wo.saveRisk', ['riskId' => $riskInfo->id]);
    $urlDetailRisk = route('project::report.risk.detail', ['riskId' => $riskInfo->id]);
} else {
    $checkEdit = false;
    $urlSubmit = route('project::wo.saveRisk');
}
$urlDelteFile = route('project::project.delete.file');
?>

@if ($checkEdit)
<div class="select-status-container">
@foreach (Risk::statusLabel() as $statusKey => $statusText)
<button class="btn {{ $riskInfo->status == $statusKey ? 'btn-default' : 'btn-primary' }} btn-lg btn-status" data-key="{{ $statusKey }}" data-url="{{ route('project::wo.changeRiskStatus') }}"
        onclick="changeStatus(this);">
    <i class="fa fa-refresh fa-spin hidden"></i>
    {{ $statusText }}
</button>
@endforeach
</div>
<br><br>
@endif

<form class="form-horizontal form-riks-detail" method="post" autocomplete="off" action="{{$urlSubmit}}" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    @if (isset($projectId))
    <input type="hidden" name="project_id" value="{{ $projectId }}">
    @endif
    @if ($checkEdit)
    <input type="hidden" id="id" name="id" value="{{ $riskInfo->id }}" />
    @endif
    @if (!empty($redirectUrl))
    <input type="hidden" name="redirectUrl" value="{{ $redirectUrl }}">
    @endif
    @if (!empty($urlDetailRisk))
    <input type="hidden" name="redirectDetailRisk" value="{{ $urlDetailRisk }}" />
    @endif
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('project::view.General information') }}</h3>
        </div>
        <div class="box-body">
            @if (!isset($projectId))
                <!-- ROW 0 -->
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="content" class="col-sm-3 control-label">{{ trans('project::view.Project') }}<em class="required" aria-required="true">*</em></label>
                        <div class="col-sm-9">
                            <select class="form-control width-93 select2-hidden-accessible select-search project" id="project_id" style="width:100%"
                                    data-remote-url="{{ URL::route('project::list.search.ajax') }}" name="project_id">
                                <option value="">{{ trans('project::view.Choose project') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            @endif
            <!-- ROW 1 -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="content" class="col-sm-3 control-label">{{ trans('project::view.Summary') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="content" name="content" placeholder="{{ trans('project::view.Content') }}">@if ($checkEdit){!!$riskInfo->content!!}@endif</textarea>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="weakness" class="col-sm-3 control-label">{{ trans('project::view.Description') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="weakness" name="weakness" placeholder="{{ trans('project::view.Weakness') }}">@if ($checkEdit){!!$riskInfo->weakness!!}@endif</textarea>
                    </div>
                </div>
            </div>
            <!-- ROW 2 -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label  class="col-sm-3 control-label">{{ trans('project::view.Owner') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <div class="row">
                            <div class="col-sm-6" >
                                <select class="form-control width-93 select2-hidden-accessible select-search" id="owner" name="owner" style="width:100%"
                                    data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                    <option value="">{{ trans('project::view.Choose employee') }}</option>
                                    @if ($checkEdit && $riskInfo->owner)
                                    <option value="{{$riskInfo->owner}}" selected>{{View::getNickName($riskInfo->owner_mail)}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-93 select2-hidden-accessible select-search" id="team_owner" name="team_owner" style="width:100%"
                                        data-remote-url="{{ URL::route('team::team.list.search.ajax') }}" disabled>
                                    <option value="{{ isset($project) ? $project->team_id : '' }}" selected>{{isset($project) ? $project->name : ''}}</option>
                                </select>
                            </div>
                            <div id="error-owner" style="margin-left:15px">&#160;</div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="probability_backup" class="col-sm-3 control-label">{{ trans('project::view.Posibility') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <select class="form-control changeValue" id="probability_backup" name="probability_backup"  >
                            <option value=""></option>
                            @foreach (Risk::getListLevelRisk() as $keyLevel => $valueLevel)
                                <option value="{{ $valueLevel }}"
                                    @if ($checkEdit && $riskInfo->probability_backup == $valueLevel) selected @endif
                                >{{ $keyLevel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <!-- ROW 3 -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="type" class="col-sm-3 control-label">{{ trans('project::view.Risk Type') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <select class="form-control" id="type" name="type"  >
                            <option value=""></option>
                            @foreach (Risk::getTypeList() as $keyLevel => $valueLevel)
                                <option value="{{ $keyLevel }}"
                                    @if ($checkEdit && $riskInfo->type == $keyLevel) selected @endif
                                >{{ $valueLevel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="level_important" class="col-sm-3 control-label">{{ trans('project::view.Priority') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <input class="form-control" id="level_important" name="level_important" disabled>
                    </div>
                </div>
            </div>
            <!-- ROW 4 -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="impact_backup" class="col-sm-3 control-label">{{ trans('project::view.Impact') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <select class="form-control changeValue" id="impact_backup" name="impact_backup"  >
                            <option value=""></option>
                            @foreach (Risk::getListLevelRisk() as $keyLevel => $valueLevel)
                                <option value="{{ $valueLevel }}"
                                    @if ($checkEdit && $riskInfo->impact_backup == $valueLevel) selected @endif
                                >{{ $keyLevel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="source" class="col-sm-3 control-label">{{ trans('project::view.Risk Source') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <select class="form-control" id="source" name="source"  >
                            <option value=""></option>
                            @foreach (Risk::getSourceList() as $keyLevel => $valueLevel)
                                <option value="{{ $keyLevel }}"
                                    @if ($checkEdit && $riskInfo->source == $keyLevel) selected @endif
                                >{{ $valueLevel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <!-- ROW 5 -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="trigger" class="col-sm-3 control-label">{{ trans('project::view.Trigger') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="trigger" name="trigger" placeholder="{{ trans('project::view.Trigger') }}">@if ($checkEdit){!!$riskInfo->trigger!!}@endif</textarea>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="solution_using" class="col-sm-3 control-label">{{ trans('project::view.PQA\'s Suggestion') }}</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="solution_using" name="solution_using" placeholder="{{ trans('project::view.PQA\'s Suggestion') }}">@if ($checkEdit){!!$riskInfo->solution_using!!}@endif</textarea>
                    </div>
                </div>
            </div>
            <!-- ROW 6 -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="trigger" class="col-sm-3 control-label">{{ trans('project::view.Duedate total') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <input type="date" class="form-control" name="due_date" id="due_date" value="@if ($checkEdit){!!$riskInfo->due_date!!}@endif">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('project::view.Mitigation Action') }}</h3>
        </div>
        <div class="box-body ">
            <div class="mitigation-box">
                @php $i = 0; @endphp
                @if (isset($riskMitigation))
                @foreach($riskMitigation as $miti)
                <div class="row row-mitigation" data-order="{{ ++$i }}">
                    <div class="form-group col-md-6">
                        <div>
                            <textarea class="form-control task-content" rows="6" name="task[{{ $i }}][content]">{{ $miti->content }}</textarea>
                        </div> 
                    </div>
                    <div class="form-group col-md-6">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{ trans('project::view.Assignee') }}<em class="required" aria-required="true">*</em></label>
                            <div class="col-sm-8">
                                <select class="form-control width-93 select2-hidden-accessible select-search task-assignee" id="task-assignee" name="task[{{ $i }}][assignee]" style="width:100%"
                                    data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                    <option value="{{ $miti->assignee }}">{{ $miti->employee_name }}</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button class="fa fa-trash-o btn-delete" ></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{ trans('project::view.Due Date') }}<em class="required" aria-required="true">*</em></label>
                            <div class="col-sm-8">
                                <input type="date" class="form-control task-duedate" name="task[{{ $i }}][duedate]" value="{{ $miti->duedate }}" />
                            </div> 
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{ trans('project::view.Status') }}<em class="required" aria-required="true">*</em></label>
                            <div class="col-sm-8">
                                <select class="form-control task-status" name="task[{{ $i }}][status]" >
                                    <option value=""></option>
                                    @foreach (RiskAction::getStatus() as $keyLevel => $valueLevel)
                                        <option value="{{ $keyLevel }}"
                                            @if ($checkEdit && $miti->status == $keyLevel) selected @endif
                                        >{{ $valueLevel }}</option>
                                    @endforeach
                                </select>
                            </div> 
                        </div>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
            <i class="fa fa-plus btn-edit btn-add-mitigation" title="Add Mitigation Action"></i>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('project::view.Contigency Action') }}</h3>
        </div>
        <div class="box-body ">
            <div class="contigency-box">
                @php $i = 0; @endphp
                @if (isset($riskContigency))
                    @foreach($riskContigency as $conti)
                        <div class="row row-contigency" data-order="{{ ++$i }}">
                            <div class="form-group col-md-6">
                                <div>
                                    <textarea class="form-control contigency-content" rows="6" name="contigency[{{ $i }}][content]">{{ $conti->content }}</textarea>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{ trans('project::view.Assignee') }}<em class="required" aria-required="true">*</em></label>
                                    <div class="col-sm-8">
                                        <select class="form-control width-93 select2-hidden-accessible select-search contigency-assignee" id="contigency-assignee" name="contigency[{{ $i }}][assignee]" style="width:100%"
                                                data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                            <option value="{{ $conti->assignee }}">{{ $conti->employee_name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button class="fa fa-trash-o btn-delete" ></button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{ trans('project::view.Due Date') }}<em class="required" aria-required="true">*</em></label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control contigency-duedate" name="contigency[{{ $i }}][duedate]" value="{{ $conti->duedate }}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{ trans('project::view.Status') }}<em class="required" aria-required="true">*</em></label>
                                    <div class="col-sm-8">
                                        <select class="form-control contigency-status" name="contigency[{{ $i }}][status]" >
                                            <option value=""></option>
                                            @foreach (RiskAction::getStatus() as $keyLevel => $valueLevel)
                                                <option value="{{ $keyLevel }}"
                                                        @if ($checkEdit && $conti->status == $keyLevel) selected @endif
                                                >{{ $valueLevel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <i class="fa fa-plus btn-edit btn-add-contigency" title="Add Contigency Action"></i>
        </div>
    </div>
    
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('project::view.Attachment') }}</h3>
        </div>
        <div class="box-body">
            @if (isset($attachs))
                @foreach($attachs as $a)
                    <div data-file="{{$a->id}}"><a href="{{ route('project::issue.download', ['id' => $a->id]) }}">{{ basename($a->path) }}</a>
                        <span><button type="button" class="delete-file" data-id="{{$a->id}}"><i class="fa fa-remove" style="font-size:15px; color:red;"></i></button></span></div>
                @endforeach
            @endif
            <input  type="file" name="attach[]" multiple />
        </div>
    </div>

    <div class="box box-info">
        <div class="box-body">
            <div class="row">
                <div class="align-center">
                    @if (isset($permissionEdit) && $permissionEdit && isset($btnSave))
                    <?php
                        $route = [route('project::report.risk'), route('project::report.risk.detail', ['id' => $riskInfo->id])];
                    ?>
                    @if (isset($_SERVER['HTTP_REFERER']) && in_array($_SERVER['HTTP_REFERER'], $route))
                        <a type="button" href="{{ route('project::report.risk') }}" class="btn btn-primary">{{trans('project::view.Back')}}</a>
                    @else
                        <a type="button" href="{{ route('project::project.edit', ['id' => $projectId]) . '#risk' }}" class="btn btn-primary">{{trans('project::view.Back')}}</a>
                    @endif
                    <button id = "save" type="submit" class="btn-add">
                {{trans('project::view.Save')}}
                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>

<div class="mitigation-box-copy hidden">
        <div class="row row-mitigation">
            <div class="form-group col-md-6">
                <div>
                    <textarea class="form-control task-content" rows="6"></textarea>
                </div> 
            </div>
            <div class="form-group col-md-6">
                <div class="form-group">
                    <label class="col-sm-3 control-label">{{ trans('project::view.Assignee') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-8">
                        <select class="form-control width-93 select2-hidden-accessible select-search task-assignee" id="task-assignee" style="width:100%"
                            data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                            <option value="">{{ trans('project::view.Choose employee') }}</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button class="fa fa-trash-o btn-delete" ></button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">{{ trans('project::view.Due Date') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control task-duedate"  />
                    </div> 
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">{{ trans('project::view.Status') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-8">
                        <select class="form-control task-status" >
                            <option value=""></option>
                            @foreach (RiskAction::getStatus() as $keyLevel => $valueLevel)
                                <option value="{{ $keyLevel }}">{{ $valueLevel }}</option>
                            @endforeach
                        </select>
                    </div> 
                </div>
            </div>
        </div>
    </div>
<div class="contigency-box-copy hidden">
    <div class="row row-contigency">
        <div class="form-group col-md-6">
            <div>
                <textarea class="form-control contigency-content" rows="6"></textarea>
            </div>
        </div>
        <div class="form-group col-md-6">
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('project::view.Assignee') }}<em class="required" aria-required="true">*</em></label>
                <div class="col-sm-8">
                    <select class="form-control width-93 select2-hidden-accessible select-search contigency-assignee" id="contigency-assignee" style="width:100%"
                            data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                        <option value="">{{ trans('project::view.Choose employee') }}</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="fa fa-trash-o btn-delete" ></button>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('project::view.Due Date') }}<em class="required" aria-required="true">*</em></label>
                <div class="col-sm-8">
                    <input type="date" class="form-control contigency-duedate"  />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('project::view.Status') }}<em class="required" aria-required="true">*</em></label>
                <div class="col-sm-8">
                    <select class="form-control contigency-status" >
                        <option value=""></option>
                        @foreach (RiskAction::getStatus() as $keyLevel => $valueLevel)
                            <option value="{{ $keyLevel }}">{{ $valueLevel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
@if(!isset($isWOAddRisk))
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
@endif
<script type="text/javascript">
    var statusOpen = {{ Risk::STATUS_OPEN }};
    var statusHappen = {{ Risk::STATUS_HAPPEN }};
    var statusClosed = {{ Risk::STATUS_CLOSED }};
    var riskId = '{{ isset($riskInfo) ? $riskInfo->id : '' }}';
    var projectId = '{{ isset($projectId) ? $projectId : '' }}';
    var urlDeleteFile = '{{ $urlDelteFile }}';
    var token = '{{ csrf_token() }}';
    var requiredText = '{{ trans("project::view.This field is required.") }}';
    var urlShowTeamByProj = '{{ route('project::project.show.team') }}';
    $(document).ready(function () {
        var firstMiti = $('.mitigation-box .row-mitigation:first-child');
        var firstConti = $('.contigency-box .row-contigency:first-child');
        if (!riskId || firstMiti.length== 0 || firstConti.length== 0) {
            $('.mitigation-box').append($('.mitigation-box-copy').html());
            var newMiti = $('.mitigation-box .row-mitigation:last-child');
            newMiti.attr('data-order', 1);
            newMiti.find('.task-content').attr('name', 'task[1][content]');
            newMiti.find('.task-assignee').attr('name', 'task[1][assignee]');
            newMiti.find('.task-duedate').attr('name', 'task[1][duedate]');
            newMiti.find('.task-status').attr('name', 'task[1][status]');
            RKfuncion.select2.elementRemote(
                newMiti.find('#task-assignee')
            );

            $('.contigency-box').append($('.contigency-box-copy').html());
            var newConti = $('.contigency-box .row-contigency:last-child');
            newConti.attr('data-order', 1);
            newConti.find('.contigency-content').attr('name', 'contigency[1][content]');
            newConti.find('.contigency-assignee').attr('name', 'contigency[1][assignee]');
            newConti.find('.contigency-duedate').attr('name', 'contigency[1][duedate]');
            newConti.find('.contigency-status').attr('name', 'contigency[1][status]');
            RKfuncion.select2.elementRemote(
                newConti.find('#contigency-assignee')
            );
            newMiti.find('.btn-delete').prop('disabled', true);
            newConti.find('.btn-delete').prop('disabled', true);
        }
        firstMiti.find('.btn-delete').prop('disabled', true);
        firstConti.find('.btn-delete').prop('disabled', true);
    });

    $('.btn-add-mitigation').click(function() {
        var order = $('.mitigation-box .row-mitigation:last-child').data('order');
        if(typeof order === "undefined") {
            order = 0;
        }
        $('.mitigation-box').append($('.mitigation-box-copy').html());
        var newChild = $('.mitigation-box .row-mitigation:last-child');
        order++;
        newChild.attr('data-order', order);
        newChild.find('.task-content').attr('name', 'task['+order+'][content]');
        newChild.find('.task-assignee').attr('name', 'task['+order+'][assignee]');
        newChild.find('.task-duedate').attr('name', 'task['+order+'][duedate]');
        newChild.find('.task-status').attr('name', 'task['+order+'][status]');
        RKfuncion.select2.elementRemote(
            newChild.find('#task-assignee')
        );
        newChild.find('.task-content').rules('add', {
            required: true
        });
        newChild.find('.task-assignee').rules('add', {
            required: true
        });
        newChild.find('.task-duedate').rules('add', {
            required: true
        });
        newChild.find('.task-status').rules('add', {
            required: true
        });
    });

    $(document).on('click', '.btn-delete', function() {
        $(this).closest('.row-mitigation').remove();
    });

    $('.btn-add-contigency').click(function() {
        var order = $('.contigency-box .row-contigency:last-child').data('order');
        if(typeof order === "undefined") {
            order = 0;
        }
        $('.contigency-box').append($('.contigency-box-copy').html());
        var newChild = $('.contigency-box .row-contigency:last-child');
        order++;
        newChild.attr('data-order', order);
        newChild.find('.contigency-content').attr('name', 'contigency['+order+'][content]');
        newChild.find('.contigency-assignee').attr('name', 'contigency['+order+'][assignee]');
        newChild.find('.contigency-duedate').attr('name', 'contigency['+order+'][duedate]');
        newChild.find('.contigency-status').attr('name', 'contigency['+order+'][status]');
        RKfuncion.select2.elementRemote(
            newChild.find('#contigency-assignee')
        );
        newChild.find('.contigency-content').rules('add', {
            required: true
        });
        newChild.find('.contigency-assignee').rules('add', {
            required: true
        });
        newChild.find('.contigency-duedate').rules('add', {
            required: true
        });
        newChild.find('.contigency-status').rules('add', {
            required: true
        });
    });
    $(document).on('click', '.btn-delete', function() {
        $(this).closest('.row-contigency').remove();
    });

    $(document).ready(function () {
        $(".form-riks-detail").validate({
            errorPlacement: function(error, element) {
                if (element.attr("name") == "owner" ) {
                    $("#error-owner").html( error );
                } else {
                    error.insertAfter(element);
                }
            },
            rules: {
                content: 'required',
                weakness: 'required',
                owner:{required: function(){
                        if($('#owner').val() === "")
                            return true;
                        else
                            return false;
                    }
                },
                type: 'required',
                impact_backup: 'required',
                probability_backup: 'required',
                source: 'required',
                trigger: 'required',
                due_date: 'required',
                project_id:{required: function(){
                        if($('#project_id').val() === "")
                            return true;
                        else
                            return false;
                    }
                },
            },
            messages: {
                content: requiredText,
                weakness: requiredText,
                level_important: requiredText,
                team_owner: requiredText,
                owner: requiredText,
                due_date: requiredText,
                project_id: requiredText,
                impact_backup: requiredText,
                probability_backup: requiredText,
            },
        });
        addRules();
        function addRules() {
            $('.mitigation-box').find('.task-content').each(function() {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.mitigation-box').find('.task-assignee').each(function() {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.mitigation-box').find('.task-duedate').each(function() {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.mitigation-box').find('.task-status').each(function() {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.contigency-box').find('.contigency-content').each(function() {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.contigency-box').find('.contigency-assignee').each(function() {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.contigency-box').find('.contigency-duedate').each(function() {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.contigency-box').find('.contigency-status').each(function() {
                $(this).rules('add', {
                    required: true
                });
            });
        }
        RKfuncion.select2.elementRemote(
            $('#performer')
        );
        RKfuncion.select2.elementRemote(
            $('#tester')
        );
        RKfuncion.select2.elementRemote(
            $('#owner')
        );
        RKfuncion.select2.elementRemote(
            $('#project_id')
        );
        RKfuncion.select2.elementRemote(
            $('#team_owner')
        );
        $('.modal.risk-dialog').removeAttr('tabindex').css('overflow', 'hidden');
        var heightBrowser = $(window).height() - 200;
        resizeModal('.modal.risk-dialog .modal-body', heightBrowser);

        $(window).resize(function() {
            var heightBrowser = $(window).height() - 200;
            resizeModal('.modal.risk-dialog .modal-body', heightBrowser);
        });

        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
    });

    if (!projectId) {
        $('#project_id').on('change', function () {
            var projectId = $('#project_id').val();
            console.log(projectId);
            $.ajax({
                url: urlShowTeamByProj,
                method: "POST",
                data: {
                    _token: token,
                    projectId: projectId,
                },
                success: function(data) {
                   var html = '';
                    $.each(data, function (index, value) {
                        html += `<option value="${value.id}" selected>${value.name}</option>`
                    });
                    $('#team_owner').html(html);
                }
            });
        })
    }

    function resizeModal(element, heightBrowser) {
        $(element).css({
            'height':  heightBrowser,
            'overflow-y': 'scroll'
        });
    }
    $('.select-risk-status').select2({minimumResultsForSearch: -1});
     // Change status risk
    function changeStatus(e) {
        var status = $(e).data('key');
        var ajaxUrl = $(e).data('url');
        $(e).find('i').removeClass('hidden');
        $('.text-risk-status-success').remove();
        $.ajax({
            url: ajaxUrl,
            type: 'post',
            dataType: 'html',
            data: {status: status, riskId: $('#id').val(), _token: token},
            timeout: 30000,
            success: function (result) {
                $('.btn-status').removeClass('btn-default').addClass('btn-primary');
                $(e).removeClass('btn-primary').addClass('btn-default');
            },
            error: function (x, t, m) {
                if (t == "timeout") {
                    alert("got timeout");
                } else {
                    alert('ajax fail to fetch data');
                }
            },
            complete: function () {
                $(e).find('i').addClass('hidden');
            },
        });
    }

    $(document).on("click", ".delete-file", function () {
        var fileId = $('.delete-file').attr('data-id');
        $.ajax({
            url: urlDeleteFile,
            method: "POST",
            dataType: "json",
            data: {
                _token: token,
                fileId: fileId,
            },
            success: function(data) {
                $("div[data-file='" + fileId + "']").remove();
            }
        });
    });

    if (($('#probability_backup').val() == '{{ Risk::LEVEL_HIGH }}' && ($('#impact_backup').val() == '{{ Risk::LEVEL_HIGH }}' || $('#impact_backup').val() == '{{ Risk::LEVEL_NORMAL }}'))
        || ($('#probability_backup').val() == '{{ Risk::LEVEL_NORMAL }}' && $('#impact_backup').val() == '{{ Risk::LEVEL_HIGH }}')) {
        $('#level_important').val('{{ trans('project::view.Level High') }}'); 
    } else if ($('#probability_backup').val() == '{{ Risk::LEVEL_LOW }}' && $('#impact_backup').val() == '{{ Risk::LEVEL_LOW }}') {
        $('#level_important').val('{{ trans('project::view.Level Low') }}');
    } else if ($('#probability_backup').val() == '' || $('#impact_backup').val() == '') {
        $('#level_important').val();
    } else {
        $('#level_important').val('{{ trans('project::view.Level Normal') }}');
    }

    $(function () {
        $('.changeValue').change(function() {
            valueProbability = $('#probability_backup').val();
            valueImpact =  $('#impact_backup').val();
           setValueForPriority(valueProbability, valueImpact);
        });
    });

    function setValueForPriority(valueProbability, setValueForPriority){
        if ((valueProbability == '{{ Risk::LEVEL_HIGH }}' && (valueImpact == '{{ Risk::LEVEL_HIGH }}' || valueImpact == '{{ Risk::LEVEL_NORMAL }}'))
            || (valueProbability == '{{ Risk::LEVEL_NORMAL }}' && valueImpact == '{{ Risk::LEVEL_HIGH }}')) {
            $('#level_important').val('{{ trans('project::view.Level High') }}'); 
        } else if (valueProbability == '{{ Risk::LEVEL_LOW }}' && valueImpact == '{{ Risk::LEVEL_LOW }}') {
            $('#level_important').val('{{ trans('project::view.Level Low') }}');
        } else if (valueProbability == '' || valueImpact == '') {
            $('#level_important').val();
        } else {
            $('#level_important').val('{{ trans('project::view.Level Normal') }}');
        }
    }
</script>