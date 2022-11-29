<?php
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Task;

?>
<form id="form-task-edit" method="post" action="{{route('project::task.save')}}" class="form-horizontal form-submit-ajax has-valid" autocomplete="off"
      data-callback-success="myTaskCallBack">
    {!! csrf_field() !!}
    <input type="hidden" name="project_id" value="{{ $project->id }}">
    <input type="hidden" name="id" value="{{ $taskItem->id }}" />
    <input type="hidden" name="type" value="{{ $taskItem->type }}" />
    @if (!empty($parentId))
        <input type="hidden" name="parent_id" value="{{ $parentId }}" />
    @endif
    @if (!empty($riskId))
        <input type="hidden" name="risk_id" value="{{ $riskId }}" />
    @endif
    @if (isset($editFormAjax) && $editFormAjax)
        <input type="hidden" name="editFormAjax" value="1" />
    @endif
    @if ($taskItem->isTaskCustomerIdea())
        <?php
        $multi = ' multiple="multiple"';
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
                        <div class="form-group form-group-select2">
                            <label for="priority" class="col-sm-3 control-label">{{ trans('project::view.Priority') }}</label>
                            <div class="col-md-9">
                                @if($accessEditTask)
                                    <select name="task[priority]" class="select-search" id="priority">
                                        @foreach ($taskPriorities as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}"{{ $taskItem->priority == $optionValue ? ' selected' : '' }}>{{ $optionLabel }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input class="form-control input-field" type="text" id="priority" disabled
                                        value="{{ $taskItem->getPriority() }}" />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        @include('project::task.edit_fields.type')
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        @include('project::task.edit_fields.content')
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        @include('project::task.edit_fields.freequency')
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        @include('project::task.edit_fields.report_content')
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                @include('project::task.edit_fields.assignee')
            </div>
            <div class="col-md-6">
                @include('project::task.edit_fields.created_at')
            </div>
            <div class="col-md-6">
                <div class="form-group" title="Phụ thuộc theo priority:&#013 -Low:1 tuần.&#013 -Normal:3 ngày.&#013 -High, Serious:24h trừ thứ 7, chủ nhật.">
                    <label for="duedate" class="col-sm-3 control-label required">{{ trans('project::view.Due date') }} <em>*</em></label>
                    <div class="col-md-9">
                        @if($accessEditTask && $accessEditDueDateCF)
                            <input name="task[duedate]" class="form-control input-field date-picker" type="text" id="duedate" 
                                   value="{{ View::getDate($taskItem->duedate) }}" placeholder="yyyy-mm-dd"/>
                        @else
                            <input class="form-control input-field" type="text" id="duedate" disabled
                                value="{{ View::getDate($taskItem->duedate) }}" placeholder="yyyy-mm-dd" />
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                @include('project::task.edit_fields.participant')
            </div>
            <div class="col-md-6">
                @include('project::task.edit_fields.cause')
            </div>
            <div class="col-md-6">
                @include('project::task.edit_fields.solution')
            </div>
            @if ($taskItem->type == Task::TYPE_ISSUE_CSS && !empty($parentId))
            <div class="col-md-6">
                @include('project::task.edit_fields.action_type')
            </div>
            @endif
        </div>
    @else
        <?php
        $multi = '';
        ?>
        <div class="row">
            <div class="col-md-6">
                @include('project::task.edit_fields.title')
            </div>
            <div class="col-md-6">
                @include('project::task.edit_fields.assignee')
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                @include('project::task.edit_fields.status')
            </div>
            <div class="col-md-6">
                @include('project::task.edit_fields.created_at')
            </div>
        </div>
        @if ($taskItem->isTaskIssues() || $taskItem->type == Task::TYPE_QUALITY_PLAN || $taskItem->type == Task::TYPE_RISK)
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="duedate" class="col-sm-3 control-label required">{{ trans('project::view.Deadline') }} <em>*</em></label>
                    <div class="col-md-9">
                        @if($accessEditTask)
                            <input name="task[duedate]" class="form-control input-field date-picker" type="text" id="duedate" 
                                value="{{ View::getDate($taskItem->duedate) }}" placeholder="yyyy-mm-dd" />
                        @else
                            <input class="form-control input-field" type="text" id="duedate" disabled
                                value="{{ View::getDate($taskItem->duedate) }}" placeholder="yyyy-mm-dd" />
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="actual-date" class="col-sm-3 control-label">{{ trans('project::view.Actual date') }}</label>
                    <div class="col-md-9">
                        @if($accessEditTask)
                            <input name="task[actual_date]" class="form-control input-field date-picker" type="text" id="actual-date" 
                                value="{{ View::getDate($taskItem->actual_date) }}" placeholder="yyyy-mm-dd" />
                        @else
                            <input class="form-control input-field" type="text" id="actual-date" disabled
                                value="{{ View::getDate($taskItem->actual_date) }}" placeholder="yyyy-mm-dd" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="row">
            @if ($taskItem->isTaskIssues())
                <div class="col-md-6">
                    @include('project::task.edit_fields.type')
                </div>
            @endif
            <div class="col-md-6">
                <div class="form-group form-group-select2">
                    <label for="priority" class="col-sm-3 control-label">{{ trans('project::view.Priority') }}</label>
                    <div class="col-md-9">
                        @if($accessEditTask)
                            <select name="task[priority]" class="select-search" id="priority">
                                @foreach ($taskPriorities as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}"{{ $taskItem->priority == $optionValue ? ' selected' : '' }}>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        @else
                            <input class="form-control input-field" type="text" id="priority" disabled
                                value="{{ $taskItem->getPriority() }}" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                @include('project::task.edit_fields.content')
            </div>
            <div class="col-md-6">
                @include('project::task.edit_fields.solution')
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                @include('project::task.edit_fields.participant')
            </div>
            @if ($taskItem->type == Task::TYPE_ISSUE_CSS && !empty($parentId))
            <div class="col-md-6">
                @include('project::task.edit_fields.action_type')
            </div>
            @endif
        </div>
    @endif

    @if (!$project->isOpen() || !$accessEditTask)
        @if (Task::hasEditStatusTasks($taskItem, $project))
            <div class="row">
                <div class="col-md-12 align-center">
                    <button class="btn-add" type="submit">
                        @if ($taskItem->id)
                            {{trans('project::view.Save')}}
                        @else
                            {{trans('project::view.Create')}}
                        @endif
                       <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                   </button>
                </div>
            </div>
        @endif
    @else
    <div class="row">
        <div class="col-md-12 align-center">
            <button class="btn-add" type="submit">
                @if ($taskItem->id)
                    {{trans('project::view.Save')}}
                @else
                    {{trans('project::view.Create')}}
                @endif
               <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
           </button>
        </div>
    </div>
    @endif
</form>
