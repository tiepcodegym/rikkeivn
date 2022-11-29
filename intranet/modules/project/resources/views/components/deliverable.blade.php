<?php 
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\ProjDeliverable;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectWOBase;

$allNameTab = Task::getAllNameTabWorkorder();
$changeList = ProjDeliverable::getChangeList();
?>
@if(isset($detail))
@if(config('project.workorder_approved.deliverable'))
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_DELIVERABLE]}}" id="table-deliverable">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th>{{trans('project::view.Deliverable')}}</th>
                <th class="width-10-per">{{trans('project::view.Stage')}}</th>
                <th class="width-15-per">{{trans('project::view.Committed date of delivery')}}</th>
                <th class="width-15-per">{{trans('project::view.Re-Plan Release')}}</th>
                @if($project->state != Project::STATE_NEW)
                <th class="width-15-per">{{trans('project::view.Actual release')}}</th>
                @endif
                <th class="width-15-per">{{trans('project::view.Change request by')}}</th>
                <th class="width-15-per">{{trans('project::view.Note')}}</th>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <?php
                $arrayStatusDelete = [ProjDeliverable::STATUS_DRAFT_DELETE,
                                        ProjDeliverable::STATUS_SUBMMITED_DELETE,
                                        ProjDeliverable::STATUS_FEEDBACK_DELETE,
                                        ProjDeliverable::STATUS_REVIEWED_DELETE];
            ?>
            @if (count($allDeliverable))
            @foreach($allDeliverable as $key => $deliverable)
            <?php
                $hasChild = false;
                $deliverableParent = $deliverable['parent'];
                $deliverableChild = $deliverable['parent'];
                if($deliverableParent->status == ProjDeliverable::STATUS_APPROVED) {
                    if (isset($deliverable['child']) && $deliverable['child']) {
                        if(ViewProject::isChangeValue($deliverableParent, $deliverable['child'])) {
                            $deliverableChild = $deliverable['child'];
                            $hasChild = true;
                        }
                    }
                }
                $deliverable = $deliverableParent;
                if($deliverable->status == ProjDeliverable::STATUS_APPROVED) {
                    $deliverableChildDraft = $deliverable->projectDeliverableChild;
                    if (count($deliverableChildDraft) > 0) {
                        if (ViewProject::isChangeValue($deliverable, $deliverableChildDraft)) {
                            $deliverableChild = $deliverableChildDraft;
                            $hasChild = true;
                        }
                    }
                }
            ?>
            @if($deliverable->status == ProjDeliverable::STATUS_APPROVED && $hasChild)
            <?php
                if (in_array($deliverableChild->status, $arrayStatusDelete)) {
                    $background = ViewProject::getColorStatusWorkOrder($deliverableChild->status);
                    $isOpenTooltip = true;   
                } else {
                    $isOpenTooltip = false;   
                    $background = ViewProject::getColorStatusWorkOrder($deliverable->status);
                }
            ?>
            @if ($isOpenTooltip)
            <tr class="background-{{$background}} tr-deliverables tr-deliverable-{{$deliverableChild->id}} is-tooltip" title="{{ProjDeliverable::statusLabel()[$deliverableChild->status]}}">
            @else
            <tr class="background-{{$background}} tr-deliverables tr-deliverable-{{$deliverableChild->id}}">
            @endif
            @else
            <tr class="background-{{ViewProject::getColorStatusWorkOrder($deliverable->status)}} tr-deliverables tr-deliverable-{{$deliverableChild->id}} is-tooltip" title="{{ProjDeliverable::statusLabel()[$deliverable->status]}}">
            @endif
                @if ($hasChild && $deliverable->title != $deliverableChild->title)
                <td class="is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{$deliverable->title}}">
                @else
                <td>
                @endif
                    <span class="title-deliverable-{{$deliverableChild->id}} white-space">{!!nl2br(e($deliverableChild->title))!!}</span>
                
                    <textarea class="display-none form-control input-title-deliverable-{{$deliverableChild->id}} white-space" name="title" rows="2">{{$deliverableChild->title}}</textarea>
                </td>
                @if($hasChild && ViewProject::getStageDeliverable($deliverable, $allStage) != ViewProject::getStageDeliverable($deliverableChild, $allStage))
                <td class="td-deliverable is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ViewProject::getStageDeliverable($deliverable, $allStage)}}">
                @else
                <td class="td-deliverable">
                @endif
                    <span class="stage-deliverable-{{$deliverableChild->id}} white-space deliverable-stage-{{$deliverableChild->stage_id}} stage-name-{{$deliverableChild->stage_id}}" data-value="{{$deliverableChild->stage_id ? $deliverableChild->stage_id : ''}}">{{ViewProject::getStageDeliverable($deliverableChild, $allStage)}}</span>
                    
                    <select name="stage_id" class="select-stage-custom select-stage-deliverable display-none form-control width-100 input-stage-deliverable-{{$deliverableChild->id}} white-space">
                        @if(!$deliverableChild->stage_id)
                            <option value="" selected>{{$deliverableChild->stage}}</option>
                        @endif
                        @foreach($allStageOfProject as $stage)
                        @if (array_key_exists($stage->stage, $allStage))
                        <option value="{{$stage->id}}" class="form-control width-100 option-select-stage-{{$stage->id}}" {{ViewProject::isSelectedStageInDeliverable($deliverableChild, $stage) ? 'selected' : ''}}>{{$allStage[$stage->stage]}}</option>
                        @endif
                        @endforeach
                    </select>
                </td>
                @if($hasChild && $deliverable->committed_date != $deliverableChild->committed_date)
                <td class="is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ViewHelper::getDate($deliverable->committed_date)}}">
                @else
                <td>
                @endif
                    <span class="committed_date-deliverable-{{$deliverableChild->id}} white-space">{{ViewHelper::getDate($deliverableChild->committed_date)}}</span>
                    
                    <input type="text" class="form-control display-none display input-committed_date-deliverable-{{$deliverableChild->id}} white-space input-field date width-100" name="committed_date" value="{{ViewHelper::getDate($deliverableChild->committed_date)}}" data-date-format="yyyy-mm-dd" data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                @if($hasChild && $deliverable->re_commited_date != $deliverableChild->re_commited_date)
                <td class="is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ViewHelper::getDate($deliverable->re_commited_date)}}">
                @else
                <td>
                @endif
                    <span class="re_commited_date-deliverable-{{$deliverableChild->id}} white-space">{{ViewHelper::getDate($deliverableChild->re_commited_date)}}</span>
                    
                    <input type="text" class="form-control display-none display input-re_commited_date-deliverable-{{$deliverableChild->id}} white-space input-field date width-100" name="re_commited_date" value="{{ViewHelper::getDate($deliverableChild->re_commited_date)}}" data-date-format="yyyy-mm-dd" data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                @if($project->state != Project::STATE_NEW)
                @if($hasChild && $deliverable->actual_date != $deliverableChild->actual_date)
                <td class="is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ViewHelper::getDate($deliverable->actual_date)}}">
                @else
                <td>
                @endif
                    <span class="actual_date-deliverable-{{$deliverableChild->id}} white-space">{{ViewHelper::getDate($deliverableChild->actual_date)}}</span>
                    
                    <input type="text" class="form-control display-none input-field input-actual_date-deliverable-{{$deliverableChild->id}} white-space date width-100" name="actual_date" value="{{ViewHelper::getDate($deliverableChild->actual_date)}}" data-date-format="yyyy-mm-dd" data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                @endif
               
                @if($hasChild && $deliverable->change_request_by != $deliverableChild->change_request_by))
                <td class="td-deliverable is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ $deliverable->change_request_by ? $changeList[$deliverable->change_request_by] : '' }}">
                @else
                <td class="td-deliverable">
                @endif
                    <span class="change_request_by-deliverable-{{$deliverableChild->id}}">{{$deliverableChild->change_request_by ? $changeList[$deliverableChild->change_request_by] : ''}}</span>
                    
                    <select name="change_request_by" class="select-change_request_by-custom select-change_request_by-deliverable display-none form-control width-100 input-change_request_by-deliverable-{{$deliverableChild->id}} white-space">
                        <option value=""></option>
                        @foreach($changeList as $key => $change)
                        <option value="{{ $key }}" class="form-control width-100 option-select-change_request_by-{{ $key }}" {{$deliverableChild->change_request_by == $key ? 'selected' : ''}}>{{ $change }}</option>
                        @endforeach
                    </select>
                </td>
                @if($hasChild && $deliverableChild->note != $deliverable->note)
                <td class="is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {!!nl2br(e($deliverable->note))!!}">
                @else
                <td>
                @endif
                    <span class="note-deliverable-{{$deliverableChild->id}} white-space">{!!nl2br(e($deliverableChild->note))!!}</span>
                
                    <textarea class="display-none form-control input-note-deliverable-{{$deliverableChild->id}} white-space" name="note" rows="2">{{$deliverableChild->note}}</textarea>
                </td>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <td>
                    @if(in_array($deliverableChild->status, [
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
                    ($deliverableChild->status == ProjDeliverable::STATUS_APPROVED && !$hasChild))
                    <span>
                        <i class="fa fa-floppy-o display-none btn-add save-deliverable save-deliverable-{{$deliverableChild->id}}" data-id="{{$deliverableChild->id}}" data-status="{{$deliverableChild->status}}" data-stage="{{$deliverableChild->stage_id}}"></i>
                        @if (!in_array($deliverableChild->status, $arrayStatusDelete))
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-deliverable edit-deliverable-{{$deliverableChild->id}}" data-id="{{$deliverableChild->id}}" data-status="{{$deliverableChild->status}}"></i>
                        @endif
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_DELIVERABLE]}}-{{$deliverableChild->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa @if($hasChild) @if(in_array($deliverableChild->status, $arrayStatusDelete)) fa-undo @else fa-times @endif @else fa-trash-o @endif btn-delete delete-deliverable delete-confirm-new delete-deliverable-{{$deliverableChild->id}}" data-id="{{$deliverableChild->id}}" data-status="{{$deliverableChild->status}}" data-stage="{{$deliverableChild->stage_id}}"></i>
                        <i class="display-none fa fa-ban btn-refresh btn-primary refresh-deliverable refresh-deliverable-{{$deliverableChild->id}}" data-id="{{$deliverableChild->id}}" data-status="{{$deliverableChild->status}}"></i>
                    </span>
                    @endif
                </td>
                @endif
            </tr>

            @endforeach
            @endif
            @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
            <tr class="display-none tr-deliverable">
                <td>
                    <span>
                        <textarea class="form-control width-100 title-deliverable" name="title" rows="2"></textarea>
                    </span>
                </td>
                <td class="td-deliverable">
                    <select name="stage_id" class="select-stage-custom form-control width-100 stage-deliverable">
                        @foreach($allStageOfProject as $stage)
                        @if (array_key_exists($stage->stage, $allStage))
                        <option value="{{$stage->id}}" class="form-control width-100 option-select-stage-{{$stage->id}}">{{$allStage[$stage->stage]}}</option>
                        @endif
                        @endforeach
                    </select>
                </td>
                <td>
                    <span> 
                        <input type="text" class="form-control committed_date-deliverable input-field date width-100" id="committed_date" name="committed_date" value="" data-date-format="yyyy-mm-dd" data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </span> 
                </td>
                <td>
                    <span> 
                        <input type="text" class="form-control re_commited_date-deliverable input-field date width-100" id="re_commited_date" name="re_commited_date" value="" data-date-format="yyyy-mm-dd" data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </span> 
                </td>
                @if($project->state != Project::STATE_NEW)
                <td>
                    <span> 
                        <input type="text" class="form-control input-field actual_date-deliverable date width-100" id="actual_date" name="actual_date" value="" data-date-format="yyyy-mm-dd" data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </span> 
                </td>
                @endif
                <td class="td-deliverable">
                    <select name="change_request_by" class="select-change_request_by-custom form-control width-100 change_request_by-deliverable">
                        <option value=""></option>
                        @foreach($changeList as $key => $change)
                        <option value="{{ $key }}" class="form-control width-100 option-select-stage-{{ $key }}" >{{ $change }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 note-deliverable" name="note" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item-{{$allNameTab[Task::TYPE_WO_DELIVERABLE]}} loading-item" ><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-deliverable"></i>
                        <i class="fa fa-trash-o btn-delete remove-deliverable"></i>
                    </span>
                </td>
            </tr>
            <tr class="tr-add-deliverable">
                <td colspan="5" class="slove-deliverable">
                  <span href="#" class="btn-add add-deliverable"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@else
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_DELIVERABLE]}}" id="table-deliverable">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th>{{trans('project::view.Deliverable')}}</th>
                <th class="width-25-per">{{trans('project::view.Committed date of delivery')}}</th>
                @if($project->state != Project::STATE_NEW)
                <th class="width-15-per">{{trans('project::view.Actual release')}}</th>
                @endif
                <th class="width-15-per">{{trans('project::view.Stage')}}</th>
                <th class="width-15-per">{{trans('project::view.Note')}}</th>
                @if(isset($permissionEdit) && $permissionEdit)
                <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($allDeliverable as $key => $deliverable)
            <tr class="tr-deliverables tr-deliverable-{{$deliverable->id}}">
                <td>
                    <span class="title-deliverable-{{$deliverable->id}} white-space">{!!nl2br(e($deliverable->title))!!}</span>
                
                    <textarea class="display-none form-control input-title-deliverable-{{$deliverable->id}} white-space" name="title" rows="2">{{$deliverable->title}}</textarea>
                </td>
                <td class="td-deliverable">
                    <span class="stage-deliverable-{{$deliverable->id}} white-space deliverable-stage-{{$deliverable->stage_id}} stage-name-{{$deliverable->stage_id}}">{{ViewProject::getStageDeliverable($deliverable, $allStage)}}</span>
                    
                    <select name="stage_id" class="select-stage-custom select-stage-deliverable display-none form-control width-100 input-stage-deliverable-{{$deliverable->id}} white-space">
                        @if(!$deliverable->stage_id)
                            <option value="" selected>{{$deliverable->stage}}</option>
                        @endif
                        @foreach($allStageOfProject as $stage)
                        @if (array_key_exists($stage->stage, $allStage))
                        <option value="{{$stage->id}}" class="form-control width-100 option-select-stage-{{$stage->id}}" {{ViewProject::isSelectedStageInDeliverable($deliverable, $stage) ? 'selected' : ''}}>{{$allStage[$stage->stage]}}</option>
                        @endif
                        @endforeach
                    </select>
                </td>
                <td>
                    <span class="committed_date-deliverable-{{$deliverable->id}} white-space">{{ViewHelper::getDate($deliverable->committed_date)}}</span>
                    
                    <input type="text" class="form-control display-none display input-committed_date-deliverable-{{$deliverable->id}} white-space input-field date width-100" name="committed_date" value="{{ViewHelper::getDate($deliverable->committed_date)}}" data-date-format="yyyy-mm-dd"  data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span class="re_commited_date-deliverable-{{$deliverable->id}} white-space">{{ViewHelper::getDate($deliverable->re_commited_date)}}</span>
                    
                    <input type="text" class="form-control display-none display input-re_commited_date-deliverable-{{$deliverable->id}} white-space input-field date width-100" name="re_commited_date" value="{{ViewHelper::getDate($deliverable->re_commited_date)}}" data-date-format="yyyy-mm-dd"  data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                @if($project->state != Project::STATE_NEW)
                <td>
                    <span class="actual_date-deliverable-{{$deliverable->id}} white-space">{{ViewHelper::getDate($deliverable->actual_date)}}</span>
                    
                    <input type="text" class="form-control display-none input-field input-actual_date-deliverable-{{$deliverable->id}} white-space date width-100" name="actual_date" value="{{ViewHelper::getDate($deliverable->actual_date)}}" data-date-format="yyyy-mm-dd"  data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                @endif
                <td class="td-deliverable">
                    <span class="stage-deliverable-{{$deliverable->id}} ">{{$deliverable->change_request_by ? $changeList[$deliverable->change_request_by] : ''}}</span>
                    <select name="change_request_by" class="select-stage-custom select-stage-deliverable display-none form-control width-100 input-stage-deliverable-{{$deliverable->id}} white-space">
                        <option value=""></option>
                        @foreach($changeList as $key => $change)
                        <option value="{{ $key }}" class="form-control width-100 option-select-stage-{{ $key }}" {{$deliverableChild->change_request_by == $key ? 'selected' : ''}}>{{ $change }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <span class="note-deliverable-{{$deliverable->id}} white-space">{!!nl2br(e($deliverable->note))!!}</span>
                
                    <textarea class="display-none form-control input-note-deliverable-{{$deliverable->id}} white-space" name="note" rows="2">{{$deliverable->note}}</textarea>
                </td>
                @if(isset($permissionEdit) && $permissionEdit)
                <td>
                    <span>
                        <i class="fa fa-floppy-o display-none btn-add save-deliverable save-deliverable-{{$deliverable->id}}" data-id="{{$deliverable->id}}" data-status="{{$deliverable->status}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-deliverable edit-deliverable-{{$deliverable->id}}" data-id="{{$deliverable->id}}" data-status="{{$deliverable->status}}"></i>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_DELIVERABLE]}}-{{$deliverable->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-trash-o btn-delete delete-deliverable delete-confirm-new delete-deliverable-{{$deliverable->id}}" data-id="{{$deliverable->id}}" data-status="{{$deliverable->status}}"></i>
                        <i class="display-none fa fa-ban btn-refresh btn-primary refresh-deliverable refresh-deliverable-{{$deliverable->id}}" data-id="{{$deliverable->id}}" data-status="{{$deliverable->status}}"></i>
                    </span>
                </td>
                @endif
            </tr>
            @endforeach
            @if(isset($permissionEdit) && $permissionEdit)
            <tr class="display-none tr-deliverable">
                <td>
                    <span>
                        <textarea class="form-control width-100 title-deliverable" name="title" rows="2"></textarea>
                    </span>
                </td>
                <td class="td-deliverable">
                    <select name="stage_id" class="select-stage-custom select-stage-deliverable form-control stage-deliverable width-100">
                        @foreach($allStageOfProject as $stage)
                        @if (array_key_exists($stage->stage, $allStage))
                        <option value="{{$stage->id}}" class="form-control width-100 option-select-stage-{{$stage->id}}">{{$allStage[$stage->stage]}}</option>
                        @endif
                        @endforeach
                    </select>
                </td>
                <td>
                    <span> 
                        <input type="text" class="form-control committed_date-deliverable input-field date width-100" id="committed_date" name="committed_date" value="" data-date-format="yyyy-mm-dd"  data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </span> 
                </td>
                <td>
                    <span> 
                        <input type="text" class="form-control re_commited_date-deliverable input-field date width-100" id="re_commited_date" name="re_commited_date" value="" data-date-format="yyyy-mm-dd"  data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </span> 
                </td>
                @if($project->state != Project::STATE_NEW)
                <td>
                    <span> 
                        <input type="text" class="form-control input-field actual_date-deliverable date width-100" id="actual_date" name="actual_date" value="" data-date-format="yyyy-mm-dd" data-date-week-start="1" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </span> 
                </td>
                @endif
                <td class="td-deliverable">
                    <select name="change_request_by" class="select-stage-custom select-stage-deliverable form-control stage-deliverable width-100">
                        <option value=""></option>
                        @foreach($changeList as $key => $change)
                        <option value="{{ $key }}" class="form-control width-100 option-select-stage-{{ $key }}" {{$deliverable->change_request_by == $key ? 'selected' : ''}}>{{ $change }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 note-deliverable" name="note" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_DELIVERABLE]}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-deliverable"></i>
                        <i class="fa fa-trash-o btn-delete remove-deliverable"></i>
                    </span>
                </td>
            </tr>
            <tr class="tr-add-deliverable">
                <td colspan="5" class="slove-deliverable">
                  <span href="#" class="btn-add add-deliverable"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif
@endif
