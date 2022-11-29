<?php 
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\AssumptionConstrain;
use Rikkei\Project\Model\Task;
use Illuminate\Support\Str;

$allNameTab = Task::getAllNameTabWorkorder();
?>
<h5 class="box-title">
@if (isset($detail))
    <span class="slove-assumption-constrains" id="slove-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}">
        <span class="btn btn-primary show-content-table margin-right-10 show-content-table-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}} display-none" data-type="{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}"><i class="fa fa-chevron-down"></i></span>
        <span class="btn btn-primary hide-content-table margin-right-10 hide-content-table-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}" data-type="{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}"><i class="fa fa-chevron-up"></i></span> 
        {{trans('project::view.Assumption and constrains')}}
    </span>
@else
    <span class="slove-assumption-constrains" id="slove-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}">
        <span class="btn btn-primary show-content-table margin-right-10 show-content-table-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}" data-type="{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}"><i class="fa fa-chevron-down"></i></span>
        <span class="btn btn-primary hide-content-table margin-right-10 display-none hide-content-table-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}" data-type="{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}"><i class="fa fa-chevron-up"></i></span>
    </span>
    {{trans('project::view.Assumption and constrains')}}
    <span class="btn btn-primary loading-workorder display-none" id="loading-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}"><i class="fa fa-refresh fa-spin"></i></span>
@endif
</h5>
@if(isset($detail))
    @if(config('project.workorder_approved.assumption_constrain'))
    <div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}" id="table-assumption-constrains">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
                <tr>
                    <th class="width-5-per">{{trans('project::view.No')}}</th>
                    <th class="width-25-per">{{trans('project::view.Assumption and constrains')}}</th>
                    <th class="width-15-per">{{trans('project::view.Note')}}</th>
                    <th class="width-10-per">{{trans('project::view.Impact')}}</th>
                    <th class="width-10-per">{{trans('project::view.Action')}}</th>
                    <th class="width-20-per">{{trans('project::view.Assignee')}}</th>
                    @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <th class="width-9-per">&nbsp;</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($allassumptionConstrains as $key => $assumption)
                <?php
                    $hasChild = false;
                    if($assumption->status == AssumptionConstrain::STATUS_APPROVED) {
                        if (count($assumption->projectAssumptionConstrainChild) > 0) {
                            $hasChild = true;
                        }
                    }
                ?>
                <tr class="background-{{ViewProject::getColorStatusWorkOrder($assumption->status)}}" data-toggle="tooltip" data-placement="top" title="{{AssumptionConstrain::statusLabel()[$assumption->status]}}">
                    <td>{{$key + 1}}</td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="content" class="popover-wo-other content-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->content)), 30, '...')!!}</span>
                        <textarea class="display-none form-control input-content-assumption-constrains-{{$assumption->id}} white-space" name="content" rows="2">{{$assumption->content}}</textarea>
                    </td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="note" class="popover-wo-other note-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->note)), 20, '...')!!}</span>
                    
                        <textarea class="display-none form-control input-note-assumption-constrains-{{$assumption->id}} white-space" name="note" rows="2">{{$assumption->note}}</textarea>
                    </td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="impact" class="popover-wo-other impact-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->impact)), 20, '...')!!}</span>
                    
                        <textarea class="display-none form-control input-impact-assumption-constrains-{{$assumption->id}} white-space" name="impact" rows="2">{{$assumption->impact}}</textarea>
                    </td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="action" class="popover-wo-other action-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->action)), 20, '...')!!}</span>
                    
                        <textarea class="display-none form-control input-action-assumption-constrains-{{$assumption->id}} white-space" name="action" rows="2">{{$assumption->action}}</textarea>
                    </td>
                    <td>
                        <?php
                            $assigneedMembers = AssumptionConstrain::getAllMemberOfAssumptionConstrain($assumption->id);
                            $firstMember = true;
                        ?>
                        <span class="assignee-assumption-constrains-{{$assumption->id}} white-space">
                            @foreach ($assigneedMembers as $key => $assigneedMember)
                                @if($firstMember)
                                {{ preg_replace('/@.*/', '', $assigneedMember['email']) }}
                                <?php $firstMember = false;?>
                                @else 
                                {{ ', '.preg_replace('/@.*/', '', $assigneedMember['email']) }}
                                @endif
                            @endforeach
                        </span>
                        <select class="display-none form-control width-100 assignee-assumption-assignee-select2 input-assignee-assumption-constrains-{{$assumption->id}}" rows="2" multiple="multiple">
                        @foreach($assigneedMembers as $key => $assigneedMember)
                        <option value="{{$assigneedMember['id']}}" class="form-control width-100" selected >{{preg_replace('/@.*/', '', $assigneedMember['email'])}}</option>
                        @endforeach                          
                        </select>
                    </td>
                    @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <td>
                        @if(($assumption->status == AssumptionConstrain::STATUS_APPROVED && !$hasChild) ||  $assumption->status == AssumptionConstrain::STATUS_DRAFT ||  $assumption->status == AssumptionConstrain::STATUS_FEEDBACK)
                        <span>
                            <i class="fa fa-floppy-o display-none btn-add save-assumption-constrains save-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}" data-status="{{$assumption->status}}"></i>
                            <i class="fa fa-pencil-square-o width-38 btn-edit edit-assumption-constrains edit-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}" data-status="{{$assumption->status}}"></i>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}-{{$assumption->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-trash-o btn-delete delete-assumption-constrains delete-confirm-new delete-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}" data-status="{{$assumption->status}}"></i>
                            <i class="display-none fa fa-ban btn-refresh btn-primary refresh-assumption-constrains refresh-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}" data-status="{{$assumption->status}}"></i>
                        </span>
                        @endif
                    </td>
                    @endif
                </tr>
                @if($assumption->status == AssumptionConstrain::STATUS_APPROVED)
                @if(count($assumption->projectAssumptionConstrainChild) > 0)
                <?php $assumption = $assumption->projectAssumptionConstrainChild;?> 
                 <tr class="background-{{ViewProject::getColorStatusWorkOrder($assumption->status)}}" data-toggle="tooltip" data-placement="top" title="{{AssumptionConstrain::statusLabel()[$assumption->status]}}">
                    <td></td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="content" class="popover-wo-other content-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->content)), 30, '...')!!}</span>
                    
                        <textarea class="display-none form-control input-content-assumption-constrains-{{$assumption->id}} white-space" name="content" rows="2">{{$assumption->content}}</textarea>
                    </td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="note" class="popover-wo-other note-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->note)), 20, '...')!!}</span>
                    
                        <textarea class="display-none form-control input-note-assumption-constrains-{{$assumption->id}} white-space" name="note" rows="2">{{$assumption->note}}</textarea>
                    </td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="impact" class="popover-wo-other impact-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->impact)), 20, '...')!!}</span>
                    
                        <textarea class="display-none form-control input-impact-assumption-constrains-{{$assumption->id}} white-space" name="impact" rows="2">{{$assumption->impact}}</textarea>
                    </td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="action" class="popover-wo-other action-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->action)), 20, '...')!!}</span>
                    
                        <textarea class="display-none form-control input-action-assumption-constrains-{{$assumption->id}} white-space" name="action" rows="2">{{$assumption->action}}</textarea>
                    </td>
                    <td>
                        <?php
                            $assigneedMembers = AssumptionConstrain::getAllMemberOfAssumptionConstrain($assumption->id);
                            $firstMember = true;
                        ?>
                        <span class="assignee-assumption-constrains-{{$assumption->id}} white-space">
                            @foreach ($assigneedMembers as $key => $assigneedMember)
                                @if($firstMember)
                                {{ preg_replace('/@.*/', '', $assigneedMember['email']) }}
                                <?php $firstMember = false;?>
                                @else 
                                {{ ', '.preg_replace('/@.*/', '', $assigneedMember['email']) }}
                                @endif
                            @endforeach
                        </span>
                        <select class="display-none form-control width-100 assignee-assumption-assignee-select2 input-assignee-assumption-constrains-{{$assumption->id}}" rows="2" multiple="multiple">
                        @foreach($assigneedMembers as $key => $assigneedMember)
                        <option value="{{$assigneedMember['id']}}" class="form-control width-100" selected >{{preg_replace('/@.*/', '', $assigneedMember['email'])}}</option>
                        @endforeach                          
                        </select>
                    </td>
                    @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <td>
                    @if($assumption->status == AssumptionConstrain::STATUS_DRAFT ||
                    $assumption->status == AssumptionConstrain::STATUS_FEEDBACK ||
                    $assumption->status == AssumptionConstrain::STATUS_DRAFT_EDIT ||
                    $assumption->status == AssumptionConstrain::STATUS_FEEDBACK_EDIT ||
                    $assumption->status == AssumptionConstrain::STATUS_FEEDBACK_DELETE ||
                    $assumption->status == AssumptionConstrain::STATUS_DRAFT_DELETE)
                        <span>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}-{{$assumption->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                            @if ($assumption->status == AssumptionConstrain::STATUS_DRAFT_DELETE ||
                            $assumption->status == AssumptionConstrain::STATUS_FEEDBACK_DELETE
                            )
                            <i class="fa fa-trash-o btn-delete delete-assumption-constrains delete-confirm-new delete-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}" data-status="{{$assumption->status}}"></i>
                            @else
                            <i class="fa fa-floppy-o display-none btn-add save-assumption-constrains save-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}" data-status="{{$assumption->status}}"></i>
                            <i class="fa fa-pencil-square-o width-38 btn-edit edit-assumption-constrains edit-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}" data-status="{{$assumption->status}}"></i>
                            <i class="fa fa-trash-o btn-delete delete-assumption-constrains delete-confirm-new delete-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}" data-status="{{$assumption->status}}"></i>
                            <i class="display-none fa fa-ban btn-refresh btn-primary refresh-assumption-constrains refresh-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}" data-status="{{$assumption->status}}"></i>
                            @endif
                        </span>
                    @endif
                    </td>
                    @endif
                </tr>
                @endif
                @endif

                @endforeach
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <tr class="tr-add-assumption-constrains">
                    <td colspan="4" class="slove-assumption-constrains">
                      <span href="#" class="btn-add add-assumption-constrains"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-assumption-constrains">
                    <td></td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 content-assumption-constrains" name="content" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 note-assumption-constrains" name="note" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 impact-assumption-constrains" name="impact" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 action-assumption-constrains" name="action" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <select name="assumption_assignee[]" class="form-control width-100 assignee-assumption-assignee-select2-new assignee-assumption-constrains" rows="2" multiple="multiple">                     
                        </select>
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-item loading-item-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-assumption-constrains"></i>
                            <i class="fa fa-trash-o btn-delete remove-assumption-constrains"></i>
                        </span>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @else
    <div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}" id="table-assumption-constrains">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
                <tr>
                    <th class="width-5-per">{{trans('project::view.No')}}</th>
                    <th class="width-25-per">{{trans('project::view.Assumption and constrains')}}</th>
                    <th class="width-15-per">{{trans('project::view.Note')}}</th>
                    <th class="width-10-per">{{trans('project::view.Impact')}}</th>
                    <th class="width-10-per">{{trans('project::view.Action')}}</th>
                    <th class="width-20-per">{{trans('project::view.Assignee')}}</th>
                    @if(isset($permissionEdit) && $permissionEdit)
                    <th class="width-9-per">&nbsp;</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($allassumptionConstrains as $key => $assumption)
                <tr>
                    <td>{{$key + 1}}</td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="content" class="popover-wo-other content-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->content)), 30, '...') !!}</span>
                    
                        <textarea class="display-none form-control input-content-assumption-constrains-{{$assumption->id}} white-space" name="content" rows="2">{{$assumption->content}}</textarea>
                    </td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="note" class="popover-wo-other note-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->note)), 20, '...')!!}</span>
                    
                        <textarea class="display-none form-control input-note-assumption-constrains-{{$assumption->id}} white-space" name="note" rows="2">{{$assumption->note}}</textarea>
                    </td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="impact" class="popover-wo-other impact-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->impact)), 20, '...')!!}</span>
                    
                        <textarea class="display-none form-control input-impact-assumption-constrains-{{$assumption->id}} white-space" name="impact" rows="2">{{$assumption->impact}}</textarea>
                    </td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumption" data-id="{{$assumption->id}}" name="action" class="popover-wo-other action-assumption-constrains-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->action)), 20, '...')!!}</span>
                    
                        <textarea class="display-none form-control input-action-assumption-constrains-{{$assumption->id}} white-space" name="action" rows="2">{{$assumption->action}}</textarea>
                    </td>
                    <td>
                        <?php
                            $assigneedMembers = AssumptionConstrain::getAllMemberOfAssumptionConstrain($assumption->id);
                            $firstMember = true;
                        ?>
                        <span class="assignee-assumption-constrains-{{$assumption->id}} white-space">
                            @foreach ($assigneedMembers as $key => $assigneedMember)
                                @if($firstMember)
                                {{ preg_replace('/@.*/', '', $assigneedMember['email']) }}
                                <?php $firstMember = false;?>
                                @else 
                                {{ ', '.preg_replace('/@.*/', '', $assigneedMember['email']) }}
                                @endif
                            @endforeach
                        </span>
                        <select class="display-none form-control width-100 assignee-assumption-assignee-select2 input-assignee-assumption-constrains-{{$assumption->id}}" rows="2" multiple="multiple">
                        @foreach($assigneedMembers as $key => $assigneedMember)
                        <option value="{{$assigneedMember['id']}}" class="form-control width-100" selected >{{preg_replace('/@.*/', '', $assigneedMember['email'])}}</option>
                        @endforeach                          
                        </select>
                    </td>
                    @if(isset($permissionEdit) && $permissionEdit)
                    <td>
                        <span>
                            <i class="fa fa-floppy-o display-none btn-add save-assumption-constrains save-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}"></i>
                            <i class="fa fa-pencil-square-o width-38 btn-edit edit-assumption-constrains edit-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}"></i>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}-{{$assumption->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-trash-o btn-delete delete-assumption-constrains delete-confirm-new delete-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}"></i>
                            <i class="display-none fa fa-ban btn-refresh btn-primary refresh-assumption-constrains refresh-assumption-constrains-{{$assumption->id}}" data-id="{{$assumption->id}}" data-status="{{$assumption->status}}"></i>
                        </span>
                    </td>
                    @endif
                </tr>
                @endforeach
                @if(isset($permissionEdit) && $permissionEdit)
                <tr class="tr-add-assumption-constrains">
                    <td colspan="4" class="slove-assumption-constrains">
                      <span href="#" class="btn-add add-assumption-constrains"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-assumption-constrains">
                
                    <td></td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 content-assumption-constrains" name="content" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 note-assumption-constrains" name="note" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 impact-assumption-constrains" name="impact" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 action-assumption-constrains" name="action" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <select name="assumption_assignee[]" class="form-control width-100 assignee-assumption-assignee-select2-new assignee-assumption-constrains" rows="2" multiple="multiple">                     
                        </select>
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-assumption-constrains"></i>
                            <i class="fa fa-trash-o btn-delete remove-assumption-constrains"></i>
                        </span>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif
@endif
<hr>
