<?php
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Risk;
use Rikkei\Team\View\TeamList;
$urlSubmit = route('project::wo.saveIssue');
$status = Task::statusLabel();
$teamsOptionAll = TeamList::toOption(null, true, false);
$url = trim(route('project::issue.save.comment'));
?>

<form class="form-horizontal form-issue-detail" id="form-issue-detail" method="post" autocomplete="off" action="{{$urlSubmit}}" enctype="multipart/form-data">
    @if (isset($projectId))
    <input type="hidden" id="project" name="project_id" value="{{ $projectId }}" />
    <input type="hidden" id="team" name="team" value="{{ $project->team_id }}" />
    @else
    <div id="hidden_team"></div>
    @endif
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    @if (!isset($issueInfo))
    <input type="hidden" id="reporter" name="reporter" value="{{ $curEmp->id }}" />
    @else
    <input type="hidden" id="id" name="id" value="{{ $issueInfo->id }}" />
    <input type="hidden" id="reporter" name="reporter" value="{{ $issueInfo->employee_reporter }}" />
    @endif
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('project::view.General information') }}</h3>
        </div>
        @if (!isset($projectId))
            <!-- ROW 0 -->
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="content" class="col-sm-4 col-form-label">{{ trans('project::view.Project') }}<em class="required" aria-required="true">*</em></label>
                    <div class="col-sm-8">
                        <select class="form-control width-93 select2-hidden-accessible select-search project" id="project_id" style="width:100%"
                                data-remote-url="{{ URL::route('project::list.search.ajax') }}" name="project_id">
                            <option value="">{{ trans('project::view.Choose project') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        @endif
        <div class="box-body">
            <div class="row">
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 col-form-label">{{ trans('project::view.Summary') }}<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" id="title" name="title">@if (isset($issueInfo)){!!$issueInfo->title!!}@endif</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="employee_owner" class="col-sm-4 col-form-label">{{ trans('project::view.Owner') }}<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select style="width: 100%" class="form-control width-93 select2-hidden-accessible select-search" id="employee_owner" name="employee_owner"
                                data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                            <option value="">{{ trans('project::view.Choose employee') }}</option>
                            @if (isset($issueInfo))
                                <option value="{{$issueInfo->employee_id}}" selected>{{$issueInfo->email_assign}}</option>
                            @endif
                        </select>
                    </div>
                    <div id="error-employee-owner" style="margin-left:36%">&#160;</div>
                </div>
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 col-form-label">{{ trans('project::view.Reporter') }}<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select style="width: 100%" class="form-control width-93 " id="reporter" name="reporter" disabled>
                            <option value="{{ $curEmp->id }}">{{ $curEmp->name }}</option>
                            @if(isset($issueInfo) && $issueInfo->employee_reporter)
                                <option value="{{$issueInfo->employee_reporter}}" selected>{{$issueInfo->email_reporter}}</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="level_important" class="col-sm-4 col-form-label">{{ trans('project::view.Priority') }}<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control" id="priority" name="priority"  >
                            <option value=""></option>
                            @foreach (Task::priorityLabel(true) as $keyLevel => $valueLevel)
                                <option value="{{ $keyLevel }}"
                                    @if (isset($issueInfo) && $issueInfo->priority == $keyLevel) selected @endif
                                >{{ $valueLevel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 col-form-label">{{ trans('project::view.Division') }}<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select id="team" name="team" class="form-control" disabled>
                            <option value="@if(isset($project)){{ $project->team_id }}@endif">@if(isset($project)){{ $project->name }}@endif</option>
                        </select>
                    </div>
                    <div id="error-team-owner" style="margin-left:36%">&#160;</div>
                </div>
            </div>
        </div>
    </div>
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('project::view.Issue detail') }}</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="type" class="col-sm-4 col-form-label">{{ trans('project::view.Issues Type') }}<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control" id="type" name="type">
                            <option value=""></option>
                            @foreach (Task::typeLabelForIssue() as $keyLevel => $valueLevel)
                                <option value="{{ $keyLevel }}"
                                    @if (isset($issueInfo) && $issueInfo->type == $keyLevel) selected @endif
                                >{{ $valueLevel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="type" class="col-sm-4 col-form-label">{{ trans('project::view.Issue source') }}<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control" id="solution" name="solution">
                            <option value=""></option>
                            @foreach (Risk::getSourceList() as $keyLevel => $valueLevel)
                                <option value="{{ $keyLevel }}"
                                    @if (isset($issueInfo) && $issueInfo->solution == $keyLevel) selected @endif
                                >{{ $valueLevel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 col-form-label">{{ trans('project::view.Description') }}<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Weakness') }}" id="content" name="content">@if (isset($issueInfo)){!!$issueInfo->content!!}@endif</textarea>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="content" class="col-sm-4 col-form-label">{{ trans('project::view.Issue cause') }}<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" name="cause">@if (isset($issueInfo)){!!$issueInfo->cause!!}@endif</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="content" class="col-sm-4 col-form-label">{{ trans('project::view.Impact') }}</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" name="impact">@if (isset($issueInfo)){!!$issueInfo->impact!!}@endif</textarea>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="content" class="col-sm-4 col-form-label">{{ trans('project::view.PQA Suggestion') }}</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" name="pqa_suggestion">@if (isset($issueInfo)){!!$issueInfo->pqa_suggestion!!}@endif</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('project::view.Action') }}<span style="color:red;">*</span></h3>
        </div>
        <div class="box-body">
            <div class="mitigation-box">
                @php $i = 0; @endphp
                @if (isset($issueMitigation))
                @foreach($issueMitigation as $miti)
                <div class="row row-mitigation"  style="margin-bottom: 30px;" data-order="{{ ++$i }}">
                    <div class="col-md-6">
                        <div>
                            <textarea class="form-control issue-content" rows="6" name="issue[{{ $i }}][content]">{{ $miti->content }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" style="margin-top: 0;">
                            <label class="col-sm-3 control-label">{{ trans('project::view.Assignee') }}<span style="color:red;">*</span></label>
                            <div class="col-sm-8">
                                <select class="form-control width-93 select2-hidden-accessible select-search issue-assignee" id="issue-assignee" name="issue[{{ $i }}][assignee]" style="width:100%"
                                        data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                    <option value="{{ $miti->assignee }}">{{ $miti->employee_name }}</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button class="fa fa-trash-o btn-delete" ></button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 0;">
                            <label class="col-sm-3 control-label">{{ trans('project::view.Due Date') }}<span style="color:red;">*</span></label>
                            <div class="col-sm-8">
                                <input type="date" class="form-control issue-duedate" name="issue[{{ $i }}][duedate]" value="{{ date('Y-m-d', strtotime($miti->duedate)) }}" />
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 0;">
                            <label class="col-sm-3 control-label">{{ trans('project::view.Status') }}<span style="color:red;">*</span></label>
                            <div class="col-sm-8">
                                <select class="form-control issue-status" name="issue[{{ $i }}][status]" >
                                    <option value=""></option>
                                    @foreach (Task::statusNewLabel() as $keyLevel => $valueLevel)
                                        <option value="{{ $keyLevel }}"
                                            @if ($miti->status == $keyLevel) selected @endif
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
            <h3 class="box-title">{{ trans('project::view.Attachment') }}</h3>
        </div>
        <div class="box-body">
            @if (isset($attachs))
                @foreach($attachs as $a)
                    <div data-file="{{$a->id}}"><a href="{{ route('project::issue.download', ['id' => $a->id]) }}">{{ basename($a->path) }}</a>
                        <span><button type="button" class="delete-file" data-id="{{$a->id}}"><i class="fa fa-remove" style="font-size:15px; color:red;"></i></button></span></div>
                @endforeach
            @endif
            <input type="file" name="attach[]" multiple />
        </div>
    </div>
    <div class="box box-info">
        <div class="box-body">
            <div class="row">
                <div class="align-center">
                    @if (isset($btnSave))
                    <?php
                    $route = [route('project::report.issue'), route('project::task.detail', ['id' => $issueInfo->id])];
                    ?>
                    @if (isset($_SERVER['HTTP_REFERER']) && in_array($_SERVER['HTTP_REFERER'], $route))
                        <a type="button" href="{{ route('project::report.issue') }}" class="btn btn-primary">{{trans('project::view.Back')}}</a>
                    @else
                        <a type="button" href="{{ route('project::project.edit', ['id' => $projectId]) . '#issue' }}" class="btn btn-primary">{{trans('project::view.Back')}}</a>
                    @endif
                    <button type="submit" class="btn-add">
                        {{trans('project::view.Save')}}
                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                    @endif
                </div>
            </div>
        </div>
    </div>

</form>
<div class="mitigation-box-copy hidden">
    <div class="row row-mitigation" style="margin-bottom: 30px;">
        <div class="col-md-6">
            <div>
                <textarea class="form-control issue-content" rows="6"></textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('project::view.Assignee') }}<span style="color:red;">*</span></label>
                <div class="col-sm-8">
                    <select class="form-control width-93 select-search issue-assignee" id="issue-assignee" style="width:100%"
                            data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                        <option value="">{{ trans('project::view.Choose employee') }}</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="fa fa-trash-o btn-delete" ></button>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('project::view.Due Date') }}<span style="color:red;">*</span></label>
                <div class="col-sm-8">
                    <input type="date" class="form-control issue-duedate"  />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('project::view.Status') }}<span style="color:red;">*</span></label>
                <div class="col-sm-8">
                    <select class="form-control issue-status" >
                        <option value=""></option>
                        @foreach (Task::statusNewLabel() as $keyLevel => $valueLevel)
                            <option value="{{ $keyLevel }}">{{ $valueLevel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
@if (!isset($issueInfo->id))
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
@else
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
@endif
<script type="text/javascript">
    var projectId = '{{ isset($projectId) ? $projectId : '' }}';
    var urlShowTeamByProj = '{{ route('project::project.show.team') }}';
    $(document).ready(function () {
        var issueId = '{{ isset($issueInfo) ? $issueInfo->id : '' }}';
        var firstMiti = $('.mitigation-box .row-mitigation:first-child');
        if (!issueId || firstMiti.length == 0) {
            $('.mitigation-box').append($('.mitigation-box-copy').html());
            var newMiti = $('.mitigation-box .row-mitigation:last-child');
            newMiti.attr('data-order', 1);
            newMiti.find('.issue-content').attr('name', 'issue[1][content]');
            newMiti.find('.issue-assignee').attr('name', 'issue[1][assignee]');
            newMiti.find('.issue-duedate').attr('name', 'issue[1][duedate]');
            newMiti.find('.issue-status').attr('name', 'issue[1][status]');
            RKfuncion.select2.elementRemote(
                newMiti.find('#issue-assignee')
            );
            newMiti.find('.btn-delete').prop('disabled', true);
        }
        firstMiti.find('.btn-delete').prop('disabled', true);
    });

    $(document).ready(function () {
        $('.btn-add-mitigation').click(function () {
            var order = $('.mitigation-box .row-mitigation:last-child').data('order');
            if (typeof order === "undefined") {
                order = 0;
            }
            $('.mitigation-box').append($('.mitigation-box-copy').html());
            var newChild = $('.mitigation-box .row-mitigation:last-child');
            order++;
            newChild.attr('data-order', order);
            newChild.find('.issue-content').attr('name', 'issue[' + order + '][content]');
            newChild.find('.issue-assignee').attr('name', 'issue[' + order + '][assignee]');
            newChild.find('.issue-duedate').attr('name', 'issue[' + order + '][duedate]');
            newChild.find('.issue-status').attr('name', 'issue[' + order + '][status]');
            RKfuncion.select2.elementRemote(
                newChild.find('#issue-assignee')
            );
            newChild.find('.issue-content').rules('add', {
                required: true
            });
            newChild.find('.issue-assignee').rules('add', {
                required: true
            });
            newChild.find('.issue-duedate').rules('add', {
                required: true
            });
            newChild.find('.issue-status').rules('add', {
                required: true
            });
        });

        $('.mitigation-box .row-mitigation').find('#issue-assignee').each(function () {
            RKfuncion.select2.elementRemote(
                $(this)
            );
        });
        $(document).on('click', '.btn-delete', function () {
            $(this).closest('.row-mitigation').remove();
        });

        function addRules() {
            $('.mitigation-box').find('.issue-content').each(function () {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.mitigation-box').find('.issue-assignee').each(function () {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.mitigation-box').find('.issue-duedate').each(function () {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.mitigation-box').find('.issue-status').each(function () {
                $(this).rules('add', {
                    required: true
                });
            });
        }

        $("#form-issue-detail").validate({
            rules: {
                title: 'required',
                content: 'required',
                employee_owner: {
                    required: function () {
                        if ($('#employee_owner').val() === "")
                            return true;
                        else
                            return false;
                    }
                },
                level_important: 'required',
                priority: 'required',
                team: {
                    required: function () {
                        if ($('#team').val() === "")
                            return true;
                        else
                            return false;
                    }
                },
                type: 'required',
                solution: 'required',
                cause: 'required',
                reporter: 'required',
                project_id:{required: function(){
                        if($('#project_id').val() === "")
                            return true;
                        else
                            return false;
                    }
                },
            },
            messages: {
                title: requiredText,
                content: requiredText,
                reporter: requiredText,
                level_important: requiredText,
                team: requiredText,
                type: requiredText,
                priority: requiredText,
                solution: requiredText,
                cause: requiredText,
                employee_owner: requiredText,
                project_id: requiredText,
            },
        });
        addRules();

        function addRules() {
            $('.mitigation-box').find('.issue-content').each(function () {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.mitigation-box').find('.issue-assignee').each(function () {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.mitigation-box').find('.issue-duedate').each(function () {
                $(this).rules('add', {
                    required: true
                });
            });
            $('.mitigation-box').find('.issue-status').each(function () {
                $(this).rules('add', {
                    required: true
                });
            });
        }

        RKfuncion.select2.elementRemote(
            $('#employee_owner')
        );
        RKfuncion.select2.elementRemote(
            $('#project_id')
        );

        $('.modal.task-dialog').removeAttr('tabindex').css('overflow', 'hidden');
        var heightBrowser = $(window).height() - 200;
        resizeModal('.modal.task-dialog .modal-body', heightBrowser);

        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });

        function resizeModal(element, heightBrowser) {
            $(element).css({
                'height': heightBrowser,
                'overflow-y': 'scroll'
            });
        }

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
                        var htmls = '';
                        $.each(data, function (index, value) {
                            html += `<option value="${value.id}" selected>${value.name}</option>`;
                            htmls += `<input type="hidden" id="team" name="team" value="${value.id}" />`
                        });
                        $('#team').html(html);
                        $('#hidden_team').html(htmls);
                    }
                });
            })
        }
    });
</script>
