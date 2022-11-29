<?php
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Risk;
use Rikkei\Team\View\TeamList;

$urlOpportunitySubmit = route('project::report.opportunity.save');
$status = Task::statusLabel();
$teamsOptionAll = TeamList::toOption(null, true, false);
$priorities = Task::priorityLabelV2();
?>

<form class="form-horizontal form-opportunity-detail" id="form-opportunity-detail" method="post" autocomplete="off" action="{{ $urlOpportunitySubmit }}" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    @if (isset($isEdit))
        <input type="hidden" name="isEdit" value="1">
    @endif
    @if (isset($id))
        <input type="hidden" name="id" value="{{ $id }}">
    @endif
    
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Opportunity detail</h3>
        </div>
        <div class="box-body">
            
            <div class="row">
                @if (!isset($projectId))
                    <div class="form-group col-sm-6">
                        <label for="project_id" class="col-sm-4 col-form-label">Project<span style="color:red;">*</span></label>
                        <div class="col-sm-8">
                            <select class="form-control width-93 select2-hidden-accessible select-search project" id="project_id" style="width:100%"
                                    data-remote-url="{{ URL::route('project::list.search.ajax') }}" name="project_id">
                                <option value="">{{ trans('project::view.Choose project') }}</option>
                            </select>
                        </div>
                    </div>
                    <div id="hidden_team"></div>
                @else
                    <input type="hidden" id="project" name="project_id" value="{{ $projectId }}" />
                    <input type="hidden" name="team" value="{{ $project->team_id }}" />
                @endif

                @if (isset($viewMode) || isset($isEdit))
                    <div class="form-group col-sm-6">
                        <label for="ID" class="col-sm-4 col-form-label">ID</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" value="@if (isset($oopInfo)){!!$oopInfo->id!!}@endif" disabled>
                        </div>
                    </div>                 
                @endif
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 col-form-label">{{ trans('project::view.Division') }}</label>
                    <div class="col-sm-8">
                        <select id="team" class="form-control" disabled>
                            <option value="@if(isset($project)){{ $project->team_id }}@endif">@if(isset($project)){{ $project->name }}@endif</option>
                        </select>
                    </div>
                    <div id="error-team" style="margin-left:36%">&#160;</div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="opportunity_source" class="col-sm-4 col-form-label">Opportunity source<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control" id="opportunity_source" name="opportunity_source">
                            <option value=""></option>
                            @foreach (Task::getAllOpportunitySource() as $keyS => $source)
                                <option value="{{ $keyS }}" {{ isset($oopInfo) && $oopInfo->opportunity_source == $keyS ? 'selected' : '' }}>{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="content" class="col-sm-4 col-form-label">Description<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" name="content">@if (isset($oopInfo)){!!$oopInfo->content!!}@endif</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="cost" class="col-sm-4 col-form-label">Cost<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control opportunity-cost" name="cost">
                            <option value=""></option>
                            @foreach (Task::priorityLabelV2() as $keyPri => $valuePri)
                                <option value="{{ $keyPri }}" {{ isset($oopInfo) && $oopInfo->cost == $keyPri ? 'selected' : '' }}>{{ $valuePri }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="employee_oop_assignee" class="col-sm-4 col-form-label">Assignee<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select style="width: 100%" class="form-control width-93 select2-hidden-accessible select-search" id="employee_oop_assignee" name="employee_oop_assignee"
                                data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                            <option value="">{{ trans('project::view.Choose employee') }}</option>
                            @if (isset($oopInfo))
                                <option value="{{$oopInfo->assign_2_id}}" selected>{{$oopInfo->assign_2_email}}</option>
                            @endif
                        </select>
                    </div>
                    <div id="error-employee-assignee" style="margin-left:36%">&#160;</div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="expected_benefit" class="col-sm-4 col-form-label">Expected Benefit<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control opportunity-benefit" name="expected_benefit">
                            <option value=""></option>
                            @foreach (Task::priorityLabelV2() as $keyPri => $valuePri)
                                <option value="{{ $keyPri }}" {{ isset($oopInfo) && $oopInfo->expected_benefit == $keyPri ? 'selected' : '' }}>{{ $valuePri }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="action_plan" class="col-sm-4 col-form-label">Action plan<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" name="action_plan">@if (isset($oopInfo)){!!$oopInfo->action_plan!!}@endif</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                
                <div class="form-group col-sm-6">
                    <label for="priority" class="col-sm-4 col-form-label">Priority</label>
                    <div class="col-sm-8">
                        <select disabled class="form-control js-oop-priority">
                            @if (isset($oopInfo) && in_array($oopInfo->priority, array_keys($priorities)))
                                <option value="">{{ $priorities[$oopInfo->priority] }}</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="action_status" class="col-sm-4 col-form-label">Action Status<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control opportunity-status" name="action_status" >
                            {{-- <option value=""></option> --}}
                            @foreach (Task::statusActionOpportunityLabel() as $keyStatus => $valueStatus)
                                <option value="{{ $keyStatus }}" {{ isset($oopInfo) && $oopInfo->action_status == $keyStatus ? 'selected' : '' }}>{{ $valueStatus }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="duedate" class="col-sm-4 col-form-label">Plan end date<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control opportunity-duedate" name="duedate" value="{{ (isset($oopInfo) && $oopInfo->duedate) ? date('Y-m-d', strtotime($oopInfo->duedate)) : '' }}" />
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label for="actual_date" class="col-sm-4 col-form-label">Actual end date</label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control opportunity-actual_date" name="actual_date" value="{{ (isset($oopInfo) && $oopInfo->actual_date) ? date('Y-m-d', strtotime($oopInfo->actual_date)) : '' }}" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="status" class="col-sm-4 col-form-label">Status<span style="color:red;">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control opportunity-status" name="status" >
                            {{-- <option value=""></option> --}}
                            @foreach (Task::statusOpportunityLabel() as $keyStatus => $valueStatus)
                                <option value="{{ $keyStatus }}" {{ isset($oopInfo) && $oopInfo->status == $keyStatus ? 'selected' : '' }}>{{ $valueStatus }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-sm-6"></div>
            </div>
            <div class="row">
                <div class="form-group col-sm-12">
                    <label for="comment" class="col-sm-4 col-form-label">Comment</label>
                    <div class="col-sm-12">
                        <textarea class="form-control" placeholder="{{ trans('project::view.Content') }}" name="comment">@if (isset($oopInfo)){!!$oopInfo->comment!!}@endif</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (isset($btnSave))
    <div class="box box-info">
        <div class="box-body">
            <div class="row">
                <div class="align-center">
                    @if (!empty($urlBack) && $urlBack == route('project::report.opportunity'))
                        <a type="button" href="{{ route('project::report.opportunity') }}" class="btn btn-primary">{{trans('project::view.Back')}}</a>
                    @else
                        <a type="button" href="{{ route('project::project.edit', ['id' => $projectId]) . '#Opportunity' }}" class="btn btn-primary">{{trans('project::view.Back')}}</a>
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
@if (!isset($oopInfo->id))
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
@else
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
@endif
@endif
<script type="text/javascript">
    var projectId = '{{ isset($projectId) ? $projectId : '' }}';
    var urlOpportunitySubmit = '{{ $urlOpportunitySubmit }}';
    var urlShowTeamByProj = '{{ route('project::project.show.team') }}';
    var token = '{{ csrf_token() }}';
    var priorities = JSON.parse('{!! json_encode($priorities, true) !!}');
    var typeLow = "{{ Task::PRIORITY_LOW }}";
    var typeMedium = "{{ Task::PRIORITY_NORMAL }}";
    var typeHigh = "{{ Task::PRIORITY_HIGH }}";

    $(document).ready(function () {
        $("#form-opportunity-detail").validate({
            rules: {
                opportunity_source: 'required',
                content: 'required',
                cost: 'required',
                expected_benefit: 'required',
                status: 'required',
                action_plan: 'required',
                employee_oop_assignee: {
                    required: function () {
                        if ($('#employee_oop_assignee').val() === "")
                            return true;
                        else
                            return false;
                    }
                },
                duedate: 'required',
                action_status: 'required',
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
                opportunity_source: requiredText,
                content: requiredText,
                cost: requiredText,
                expected_benefit: requiredText,
                status: requiredText,
                action_plan: requiredText,
                employee_oop_assignee: requiredText,
                duedate: requiredText,
                action_status: requiredText,                
                project_id: requiredText,
                team: requiredText,
            },
        });

        RKfuncion.select2.elementRemote(
            $('#project_id')
        );
        RKfuncion.select2.elementRemote(
            $('#employee_oop_assignee')
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

        $('body').on('change', '.opportunity-cost', function (){
            let cost = $(this).val();
            let benefit = $('.opportunity-benefit').val();
            if (cost && benefit) {
                let priority = fillPriority(cost, benefit);
                let priorityText = priorities[priority];
                let html_option = '<option value="">'+priorityText+'</option>';
                $('.js-oop-priority').html(html_option);
            } else {
                $('.js-oop-priority').html("");
            }
        });
        $('body').on('change', '.opportunity-benefit', function (){
            let benefit = $(this).val();
            let cost = $('.opportunity-cost').val();
            if (cost && benefit) {
                let priority = fillPriority(cost, benefit);
                let priorityText = priorities[priority];
                let html_option = '<option value="">'+priorityText+'</option>';
                $('.js-oop-priority').html(html_option);
            } else {
                $('.js-oop-priority').html("");
            }
        });

        function fillPriority(cost, benefit) {
            if (cost == typeHigh) {
                switch(benefit) {
                    case typeLow:
                        return typeLow;
                        break;
                    case typeMedium:
                        return typeMedium;
                        break;
                    case typeHigh:
                        return typeMedium;
                        break;
                    default:
                        return '';
                }
            }
            if (cost == typeMedium) {
                switch(benefit) {
                    case typeLow:
                        return typeLow;
                        break;
                    case typeMedium:
                        return typeMedium;
                        break;
                    case typeHigh:
                        return typeHigh;
                        break;
                    default:
                        return '';
                }
            }
            if (cost == typeLow) {
                switch(benefit) {
                    case typeLow:
                        return typeMedium;
                        break;
                    case typeMedium:
                        return typeHigh;
                        break;
                    case typeHigh:
                        return typeHigh;
                        break;
                    default:
                        return '';
                }
            }
        }
    });
</script>
