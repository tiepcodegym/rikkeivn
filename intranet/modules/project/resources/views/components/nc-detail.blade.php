<?php
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Risk;
use Rikkei\Team\View\TeamList;

$urlSubmit = route('project::nc.save');
$status = Task::statusLabel();
$teamsOptionAll = TeamList::toOption(null, true, false);
?>

<form class="form-horizontal form-nc-detail" id="form-nc-detail" method="post" autocomplete="off" action="{{ $urlSubmit }}" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    @if (isset($isEdit))
        <input type="hidden" name="isEdit" value="1">
    @endif
    @if (isset($id))
        <input type="hidden" name="id" value="{{ $id }}">
    @endif
    
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('project::view.General information') }}</h3>
        </div>
        <div class="box-body">
            @if (!isset($projectId))
                <!-- ROW 0 -->
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="project_id" class="col-sm-4 col-form-label">{{ trans('project::view.Project') }}<span style="color:red;">*</span></label>
                        <div class="col-sm-8">
                            <select class="form-control width-93 select2-hidden-accessible select-search project" id="project_id" style="width:100%"
                                    data-remote-url="{{ URL::route('project::list.search.ajax') }}" name="project_id">
                                <option value="">{{ trans('project::view.Choose project') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div id="hidden_team"></div>
            @else
                <input type="hidden" id="project" name="project_id" value="{{ $projectId }}" />
                <input type="hidden" name="team" value="{{ $project->team_id }}" />
            @endif

            <div class="row">
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 col-form-label">{{ trans('project::view.Summary') }}<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" id="title" name="title">@if (isset($ncInfo)){!!$ncInfo->title!!}@endif</textarea>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 col-form-label">{{ trans('project::view.Reporter') }}</label>
                    <div class="col-sm-8">
                        @php
                            $idReporter = isset($ncInfo) ? $ncInfo->employee_reporter : $curEmp->id;
                            $emaileporter = isset($ncInfo) ? $ncInfo->name_reporter : $curEmp->name;
                        @endphp
                        <select style="width: 100%" class="form-control width-93 select2-hidden-accessible select-search" id="reporter" name="reporter"
                                data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                            <option value="{{ $idReporter }}" selected>{{ $emaileporter }}</option>
                        </select>
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
                            @if (isset($ncInfo))
                                <option value="{{$ncInfo->employee_id}}" selected>{{$ncInfo->email_assign}}</option>
                            @endif
                        </select>
                    </div>
                    <div id="error-employee-owner" style="margin-left:36%">&#160;</div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="employee_assignee" class="col-sm-4 col-form-label">Assignee<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select style="width: 100%" class="form-control width-93 select2-hidden-accessible select-search" id="employee_assignee" name="employee_assignee"
                                data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                            <option value="">{{ trans('project::view.Choose employee') }}</option>
                            @if (isset($ncInfo))
                                <option value="{{$ncInfo->assign_2_id}}" selected>{{$ncInfo->assign_2_email}}</option>
                            @endif
                        </select>
                    </div>
                    <div id="error-employee-assignee" style="margin-left:36%">&#160;</div>
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
                                    @if (isset($ncInfo) && $ncInfo->priority == $keyLevel) selected @endif
                                >{{ $valueLevel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 col-form-label">{{ trans('project::view.Division') }}</label>
                    <div class="col-sm-8">
                        <select id="team" name="team" class="form-control" disabled>
                            <option value="@if(isset($project)){{ $project->team_id }}@endif">@if(isset($project)){{ $project->name }}@endif</option>
                        </select>
                    </div>
                    <div id="error-team" style="margin-left:36%">&#160;</div>
                </div>
            </div>
        </div>
    </div>
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">NC detail</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="process" class="col-sm-4 col-form-label">Process</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="process" name="process">
                            <option value=""></option>
                            @foreach (Task::getAllProcessNC() as $keyPro => $process)
                                <option value="{{ $keyPro }}" {{ isset($ncInfo) && $ncInfo->process == $keyPro ? 'selected' : '' }}>{{ $process }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="corrective_action" class="col-sm-4 col-form-label">Corrective action</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" name="corrective_action">@if (isset($ncInfo)){!!$ncInfo->corrective_action!!}@endif</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 col-form-label">{{ trans('project::view.Description') }}</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Weakness') }}" id="content" name="content">@if (isset($ncInfo)){!!$ncInfo->content!!}@endif</textarea>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="employee_approver" class="col-sm-4 col-form-label">Approver<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select style="width: 100%" class="form-control width-93 select2-hidden-accessible" id="employee_approver" name="employee_approver">
                            <option value="">{{ trans('project::view.Choose employee') }}</option>
                            @if (isset($relaters))
                                @foreach ($relaters as $item)
                                    <option value="{{ $item->id }}" {{ isset($ncInfo) && $ncInfo->approver_id == $item->id ? 'selected' : '' }}>{{$item->name}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div id="error-employee-approver" style="margin-left:36%">&#160;</div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="label" class="col-sm-4 col-form-label">Label</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" placeholder="Label" name="label" value="@if (isset($ncInfo)) {!!$ncInfo->label!!} @endif">
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 col-form-label">Create date</label>
                    <div class="col-sm-8">{{ isset($ncInfo) ? $ncInfo->created_at : '' }}</div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="cause" class="col-sm-4 col-form-label">{{ trans('project::view.Issue cause') }}</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" name="cause">@if (isset($ncInfo)){!!$ncInfo->cause!!}@endif</textarea>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="duedate" class="col-sm-4 col-form-label">{{ trans('project::view.Due Date') }}</label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control nc-duedate" name="duedate" value="{{ (isset($ncInfo) && $ncInfo->duedate) ? date('Y-m-d', strtotime($ncInfo->duedate)) : '' }}" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="impact" class="col-sm-4 col-form-label">{{ trans('project::view.Impact') }}</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" name="impact">@if (isset($ncInfo)){!!$ncInfo->impact!!}@endif</textarea>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="status" class="col-sm-4 col-form-label">{{ trans('project::view.Status') }}</label>
                    <div class="col-sm-8">
                        <select class="form-control nc-status" name="status" >
                            {{-- <option value=""></option> --}}
                            @foreach (Task::statusNCLabel() as $keyStatus => $valueStatus)
                                <option value="{{ $keyStatus }}" {{ isset($ncInfo) && $ncInfo->status == $keyStatus ? 'selected' : '' }}>{{ $valueStatus }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="correction" class="col-sm-4 col-form-label">Correction</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="Correction" name="correction">@if (isset($ncInfo)){!!$ncInfo->correction!!}@endif</textarea>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="updated_at" class="col-sm-4 col-form-label">Updated</label>
                    <div class="col-sm-8">{{ isset($ncInfo) ? $ncInfo->updated_at : '' }}</div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-12">
                    <label for="comment" class="col-sm-4 col-form-label">Comment</label>
                    <div class="col-sm-12">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" name="comment">@if (isset($ncInfo)){!!$ncInfo->comment!!}@endif</textarea>
                    </div>
                </div>
            </div>
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

    @if (isset($btnSave))
    <div class="box box-info">
        <div class="box-body">
            <div class="row">
                <div class="align-center">
                    @php
                        $route = [route('project::report.ncm'), route('project::task.detail', ['id' => $ncInfo->id])];
                    @endphp
                    @if (isset($_SERVER['HTTP_REFERER']) && in_array($_SERVER['HTTP_REFERER'], $route))
                        <a type="button" href="{{ route('project::report.ncm') }}" class="btn btn-primary">{{trans('project::view.Back')}}</a>
                    @else
                        <a type="button" href="{{ route('project::project.edit', ['id' => $projectId]) . '#NC' }}" class="btn btn-primary">{{trans('project::view.Back')}}</a>
                    @endif
                    <button type="submit" class="btn-add">
                        {{trans('project::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</form>

@if(!isset($viewMode))
@if (!isset($ncInfo->id))
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
@else
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
@endif
@endif
<script type="text/javascript">
    var projectId = '{{ isset($projectId) ? $projectId : '' }}';
    var urlSubmit = '{{ $urlSubmit }}';
    var urlShowTeamByProj = '{{ route('project::project.show.team') }}';
    var urlShowApproverByProj = '{{ route('project::project.show.approver') }}';
    var token = '{{ csrf_token() }}';

    $(document).ready(function () {
        $("#form-nc-detail").validate({
            rules: {
                title: 'required',
                employee_owner: {
                    required: function () {
                        if ($('#employee_owner').val() === "")
                            return true;
                        else
                            return false;
                    }
                },
                priority: 'required',
                employee_assignee: {
                    required: function () {
                        if ($('#employee_assignee').val() === "")
                            return true;
                        else
                            return false;
                    }
                },
                employee_approver: {required: function(){
                        if($('#employee_approver').val() === "")
                            return true;
                        else
                            return false;
                    }
                },
                project_id:{required: function(){
                        if($('#project_id').val() === "")
                            return true;
                        else
                            return false;
                    }
                },
                team: {
                    required: function () {
                        if ($('#team').val() === "")
                            return true;
                        else
                            return false;
                    }
                },
            },
            messages: {
                title: requiredText,
                employee_owner: requiredText,
                priority: requiredText,
                employee_assignee: requiredText,
                employee_approver: requiredText,
                project_id: requiredText,
                team: requiredText,
            },
        });

        $('#employee_approver').select2();
        RKfuncion.select2.elementRemote(
            $('#employee_owner')
        );
        RKfuncion.select2.elementRemote(
            $('#project_id')
        );
        RKfuncion.select2.elementRemote(
            $('#employee_assignee')
        );
        RKfuncion.select2.elementRemote(
            $('#reporter')
        );

        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });

        if (!projectId) {
            $('#project_id').on('change', function () {
                var projectId = $('#project_id').val();
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
                            htmls += `<input type="hidden" name="team" value="${value.id}" />`
                        });
                        $('#team').html(html);
                        $('#hidden_team').html(htmls);
                    }
                });

                $.ajax({
                    url: urlShowApproverByProj,
                    method: "POST",
                    data: {
                        _token: token,
                        projectId: projectId,
                    },
                    success: function(data) {
                        var html_approver = `<option value="" selected>Choose employee</option>`;
                        $.each(data, function (index, value) {
                            html_approver += `<option value="${value.id}">${value.name}</option>`;
                        });
                        $('#employee_approver').html(html_approver);
                    }
                });
            })
        }

        $('.modal.task-dialog').removeAttr('tabindex').css('overflow', 'hidden');
        var heightBrowser = $(window).height() - 200;
        resizeModal('.modal.task-dialog .modal-body', heightBrowser);

        $(window).resize(function() {
            var heightBrowser = $(window).height() - 200;
            resizeModal('.modal.task-dialog .modal-body', heightBrowser);
        });

        function resizeModal(element, heightBrowser) {
            $(element).css({
                'height': "80vh",
                'overflow-y': 'scroll'
            });
        }
    });
</script>
