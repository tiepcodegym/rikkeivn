<?php 
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Project\Model\StageAndMilestone;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\ProjectWOBase;

$allNameTab = Task::getAllNameTabWorkorder();
?>

@if(isset($detail))
@if(config('project.workorder_approved.stage_and_milestone'))
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_STAGE_MILESTONE]}}" id="table-stage-and-milestone">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="width-20-per">{{trans('project::view.Stage')}}</th>
                <th class="width-25-per">{{trans('project::view.Description')}}</th>
                <th class="width-25-per">{{trans('project::view.Milestone Output')}}</th>
                <th class="width-20-per">{{trans('project::view.Quality gate plan date')}}</th>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <?php
                $arrayStatusDelete = [StageAndMilestone::STATUS_DRAFT_DELETE,
                                        StageAndMilestone::STATUS_SUBMMITED_DELETE,
                                        StageAndMilestone::STATUS_FEEDBACK_DELETE,
                                        StageAndMilestone::STATUS_REVIEWED_DELETE];
            ?>
            @if (count($allStagesAndMilestones))
            @foreach($allStagesAndMilestones as $key => $stage)
            <?php
                $hasChild = false;
                $stageParent = $stage['parent'];
                $stageChild = $stage['parent'];
                if($stageParent->status == StageAndMilestone::STATUS_APPROVED) {
                    if (isset($stage['child']) && $stage['child']) {
                        if(ViewProject::isChangeValue($stageParent, $stage['child'])) {
                            $stageChild = $stage['child'];
                            $hasChild = true;
                        }
                    }
                }
                $stage = $stageParent;
            ?>
            @if($stage->status == StageAndMilestone::STATUS_APPROVED && $hasChild)
            <?php
                if (in_array($stageChild->status, $arrayStatusDelete)) {
                    $background = ViewProject::getColorStatusWorkOrder($stageChild->status);
                    $isOpenTooltip = true;   
                } else {
                    $isOpenTooltip = false;   
                    $background = ViewProject::getColorStatusWorkOrder($stage->status);
                }
            ?>
            @if($isOpenTooltip)
            <tr class="background-{{$background}} tr-stage tr-stage-{{$stageChild->id}} is-tooltip" title="{{StageAndMilestone::statusLabel()[$stageChild->status]}}">
            @else
            <tr class="background-{{$background}} tr-stage tr-stage-{{$stageChild->id}}">
            @endif
            @else
            <tr class="background-{{ViewProject::getColorStatusWorkOrder($stage->status)}} tr-stage tr-stage-{{$stageChild->id}} is-tooltip" title="{{StageAndMilestone::statusLabel()[$stage->status]}}">
            @endif
                @if ($hasChild && ViewProject::generateStage($stageChild, $allStage) != ViewProject::generateStage($stage, $allStage))
                <td class="td-stage is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ViewProject::generateStage($stage, $allStage)}}">
                @else
                <td class="td-stage">
                @endif
                    <span class="stage-stage-and-milestone-{{$stageChild->id}} white-space">{{ViewProject::generateStage($stageChild, $allStage)}}</span>
                    
                    <select name="stage" class="select-stage form-control width-100 display-none input-stage-stage-and-milestone-{{$stageChild->id}} white-space">
                        @if (!array_key_exists($stageChild->stage, $allStage))
                        <option value="{{$stageChild->stage}}">{{$stageChild->stage}}</option>
                        @endif
                        @foreach($allStage as $key =>  $option)
                            <option value="{{$key}}" {{$key == $stageChild->stage ? 'selected' : ''}}>{{$option}}</option>
                        @endforeach
                    </select>
                </td>
                @if ($hasChild && $stage->description != $stageChild->description)
                <td class="is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{$stage->description}}">
                @else
                <td>
                @endif
                    <span class="description-stage-and-milestone-{{$stageChild->id}} white-space">{!!nl2br(e($stageChild->description))!!}</span>
                
                    <textarea class="display-none form-control input-description-stage-and-milestone-{{$stageChild->id}} white-space" name="description" rows="2">{{$stageChild->description}}</textarea>
                </td>
                @if ($hasChild && $stage->milestone != $stageChild->milestone)
                <td class="is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{$stage->milestone}}">
                @else
                <td>
                @endif
                    <span class="milestone-stage-and-milestone-{{$stageChild->id}} white-space">{!!nl2br(e($stageChild->milestone))!!}</span>
                
                    <textarea class="display-none form-control input-milestone-stage-and-milestone-{{$stageChild->id}} white-space" name="milestone" rows="2">{{$stageChild->milestone}}</textarea>
                </td>
                @if ($hasChild && ViewHelper::getDate($stage->qua_gate_plan) != ViewHelper::getDate($stageChild->qua_gate_plan))
                <td class="is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ViewHelper::getDate($stage->qua_gate_plan)}}">
                @else
                <td>
                @endif
                    <span class="qua_gate_plan-stage-and-milestone-{{$stageChild->id}}">{{ViewHelper::getDate($stageChild->qua_gate_plan)}}</span>
                    <input type="text" class="display-none form-control width-100 input-qua_gate_plan-stage-and-milestone-{{$stageChild->id}}" name="qua_gate_plan" value="{{ViewHelper::getDate($stageChild->qua_gate_plan)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <td>
                    @if(in_array($stageChild->status, [
                            ProjectWOBase::STATUS_DRAFT,
                            ProjectWOBase::STATUS_DRAFT_EDIT,
                            ProjectWOBase::STATUS_FEEDBACK,
                            ProjectWOBase::STATUS_FEEDBACK_EDIT,
                            ProjectWOBase::STATUS_FEEDBACK_DELETE,
                            ProjectWOBase::STATUS_DRAFT_DELETE,
                            ProjectWOBase::STATUS_SUBMITTED,
                            ProjectWOBase::STATUS_SUBMIITED_EDIT,
                            ProjectWOBase::STATUS_SUBMMITED_DELETE
                        ]) ||
                        ($stageChild->status == StageAndMilestone::STATUS_APPROVED && !$hasChild))
                    <span>
                        <i class="width-40 fa fa-floppy-o display-none btn-add save-stage-and-milestone save-stage-and-milestone-{{$stageChild->id}}" data-id="{{$stageChild->id}}" data-status="{{$stageChild->status}}"></i>
                        @if (!in_array($stageChild->status, $arrayStatusDelete))
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-stage-and-milestone edit-stage-and-milestone-{{$stageChild->id}}" data-id="{{$stageChild->id}}" data-status="{{$stageChild->status}}"></i>
                        @endif
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_STAGE_MILESTONE]}}-{{$stageChild->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        @if(!$stageChild->projectDeliverable($stageChild->id, $project->id))
                        <i class="fa @if($hasChild) @if(in_array($stageChild->status, $arrayStatusDelete)) fa-undo @else fa-times @endif @else fa-trash-o @endif btn-delete delete-stage-and-milestone delete-confirm-new delete-stage-and-milestone-{{$stageChild->id}}" data-id="{{$stageChild->id}}" data-status="{{$stageChild->status}}"></i>
                        @else
                        <i class="fa @if($hasChild) @if(in_array($stageChild->status, $arrayStatusDelete)) fa-undo @else fa-times @endif @else fa-trash-o @endif btn-delete delete-stage-and-milestone delete-confirm-new delete-stage-and-milestone-{{$stageChild->id}} display-none" data-id="{{$stageChild->id}}" data-status="{{$stageChild->status}}"></i>
                        @endif
                        <i class="display-none fa fa-ban btn-refresh btn-primary refresh-stage-and-milestone refresh-stage-and-milestone-{{$stageChild->id}}" data-id="{{$stageChild->id}}" data-status="{{$stageChild->status}}"></i>
                    </span>
                    @endif
                </td>
                @endif
            </tr>

            @endforeach
            @endif
            
            @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
            <tr class="display-none tr-stage-and-milestone">
                <td class="td-stage">
                    <span>
                        <select name="stage" class="form-control width-100 stage-stage-and-milestone">
                            @foreach($allStage as $key =>  $option)
                                <option value="{{$key}}">{{$option}}</option>
                            @endforeach
                        </select>
                    <span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 description-stage-and-milestone" name="description" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 milestone-stage-and-milestone" name="milestone" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <input type="text" class="form-control width-100 qua_gate_plan-stage-and-milestone" name="qua_gate_plan" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item loading-item-{{$allNameTab[Task::TYPE_WO_STAGE_MILESTONE]}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="width-40 fa fa-floppy-o btn-add add-new-stage-and-milestone"></i>
                        <i class="fa fa-trash-o btn-delete remove-stage-and-milestone"></i>
                    </span>
                </td>
            </tr>
            <tr class="tr-add-stage-and-milestone">
                <td colspan="5" class="slove-stage-and-milestone">
                  <span href="#" class="btn-add btn-add-stage add-stage-and-milestone"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@else
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_STAGE_MILESTONE]}}" id="table-stage-and-milestone">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="width-20-per">{{trans('project::view.Stage')}}</th>
                <th class="width-25-per">{{trans('project::view.Description')}}</th>
                <th class="width-25-per">{{trans('project::view.Milestone Output')}}</th>
                <th class="width-20-per">{{trans('project::view.Quality gate plan date')}}</th>
                @if(isset($permissionEdit) && $permissionEdit)
                <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($allStagesAndMilestones as $key => $stage)
            <tr class="tr-stage tr-stage-{{$stage->id}}">
                <td class="td-stage">
                    <span class="stage-stage-and-milestone-{{$stage->id}} white-space">{{ViewProject::generateStage($stage, $allStage)}}</span>
                    
                    <select name="stage" class="select-stage form-control width-100 display-none input-stage-stage-and-milestone-{{$stage->id}} white-space">
                        @if (!array_key_exists($stage->stage, $allStage))
                        <option value="{{$stage->stage}}">{{$stage->stage}}</option>
                        @endif
                        @foreach($allStage as $key =>  $option)
                            <option value="{{$key}}" {{$key == $stage->stage ? 'selected' : ''}}>{{$option}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <span class="description-stage-and-milestone-{{$stage->id}} white-space">{!!nl2br(e($stage->description))!!}</span>
                
                    <textarea class="display-none form-control input-description-stage-and-milestone-{{$stage->id}} white-space" name="description" rows="2">{{$stage->description}}</textarea>
                </td>
                <td>
                    <span class="milestone-stage-and-milestone-{{$stage->id}} white-space">{!!nl2br(e($stage->milestone))!!}</span>
                
                    <textarea class="display-none form-control input-milestone-stage-and-milestone-{{$stage->id}} white-space" name="milestone" rows="2">{{$stage->milestone}}</textarea>
                </td>
                <td>
                    <span class="qua_gate_plan-stage-and-milestone-{{$stage->id}}">{{ViewHelper::getDate($stage->qua_gate_plan)}}</span>
                    <input type="text" class="display-none form-control width-100 input-qua_gate_plan-stage-and-milestone-{{$stage->id}}" name="qua_gate_plan" value="{{ViewHelper::getDate($stage->qua_gate_plan)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                @if(isset($permissionEdit) && $permissionEdit)
                <td>
                    <span>
                        <i class="fa fa-floppy-o display-none btn-add save-stage-and-milestone save-stage-and-milestone-{{$stage->id}}" data-id="{{$stage->id}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-stage-and-milestone edit-stage-and-milestone-{{$stage->id}}" data-id="{{$stage->id}}"></i>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_STAGE_MILESTONE]}}-{{$stage->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        @if(!$stage->projectDeliverable($stage->id, $project->id))
                        <i class="fa fa-trash-o btn-delete delete-stage-and-milestone delete-confirm-new delete-stage-and-milestone-{{$stage->id}}" data-id="{{$stage->id}}"></i>
                        @endif
                        <i class="display-none fa fa-ban btn-refresh btn-primary refresh-stage-and-milestone refresh-stage-and-milestone-{{$stage->id}}" data-id="{{$stage->id}}" data-status="{{$stage->status}}"></i>
                    </span>
                </td>
                @endif
            </tr>
            @endforeach
            @if(isset($permissionEdit) && $permissionEdit)
            <tr class="display-none tr-stage-and-milestone">
                <td class="td-stage">
                    <span>
                        <select name="stage" class="select-stage form-control width-100 stage-stage-and-milestone">
                            @foreach($allStage as $key => $option)
                                <option value="{{$key}}">{{$option}}</option>
                            @endforeach
                        </select>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 description-stage-and-milestone" name="description" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 milestone-stage-and-milestone" name="milestone" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <input type="text" class="form-control width-100 qua_gate_plan-stage-and-milestone" name="qua_gate_plan" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_STAGE_MILESTONE]}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-stage-and-milestone"></i>
                        <i class="fa fa-trash-o btn-delete remove-stage-and-milestone"></i>
                    </span>
                </td>
            </tr>
            <tr class="tr-add-stage-and-milestone">
                <td colspan="5" class="slove-stage-and-milestone">
                  <span href="#" class="btn-add btn-add-stage add-stage-and-milestone"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif
@endif
