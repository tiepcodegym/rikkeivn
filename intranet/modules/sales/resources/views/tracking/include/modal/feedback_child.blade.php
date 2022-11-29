<?php
use Rikkei\Project\Model\Task;

$disabledAssign = '';
$disabledParticipant = '';
$taskItem->type = Task::TYPE_ISSUE_CSS;
$taskAction = Task::actionLabel();

?>
<div class="modal fade" id="feedbackchildModal" style="display: none;">
   <div class="modal-dialog modal-lg" style="width: 90%;">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span></button>
            <h4 class="modal-title">{{ trans('project::view.Task CSS') }}</h4>
         </div>
         <div class="modal-body">
            <div class="modal-ncm-editor-main">
               <div class="row">
                  <div class="col-sm-12">
                        <form id="form-task-edit-customer_child" method="post" action="{{route('sales::tracking.saveTasks')}}" class="form-horizontal form-submit-ajax has-valid" autocomplete="off" data-callback-success="myTaskCallBack">
                         {!! csrf_field() !!}
                         <input type="hidden" name="type" value="{{ $taskItem->type }}" />
                         <input type="hidden" name="parent_id" value="" />
                         @if (isset($editFormAjax) && $editFormAjax)
                             <input type="hidden" name="editFormAjax" value="1" />
                         @endif
                             <?php
                             $multi = ' multiple="multiple"';
                             ?>
                          @if ($taskItem->isTaskCustomerIdea())   
                                 <div class="row">
                                     <div class="col-md-6">
                                         <div class="row">
                                               <div class="col-md-12">
                                                  @include('project::task.edit_fields.project')
                                               </div>
                                        </div>
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
                                     </div>
                                     <div class="col-md-6">
                                         @include('project::task.edit_fields.assignee')
                                     </div>
                                     <div class="col-md-6">
                                         @include('project::task.edit_fields.created_at')
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
                                                    value="{{ $taskItem->duedate }}" placeholder="yyyy-mm-dd" />
                                            @else
                                                <input class="form-control input-field" type="text" id="duedate" disabled
                                                    value="{{ $taskItem->duedate }}" placeholder="yyyy-mm-dd" />
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
                                                    value="{{ $taskItem->actual_date }}" placeholder="yyyy-mm-dd" />
                                            @else
                                                <input class="form-control input-field" type="text" id="actual-date" disabled
                                                    value="{{ $taskItem->actual_date }}" placeholder="yyyy-mm-dd" />
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
                                @if ($taskItem->type == Task::TYPE_ISSUE_CSS)
                                <div class="col-md-6">
                                    @include('project::task.edit_fields.action_type')
                                </div>
                                @endif
                            </div>
                        @endif
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
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
