<?php 
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\Training;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\View as ViewHelper;
$allNameTab = Task::getAllNameTabWorkorder();

?>

<h3>2. Plan Training</h3>
@if(isset($detail))
@if(config('project.workorder_approved.training'))
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_TRANING]}}" id="table-training">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="width-5-per">{{trans('project::view.No')}}</th>
                <th class="width-15-per">{{trans('project::view.Topic')}}</th>
                <th class="width-15-per">{{trans('project::view.Description')}}</th>
                <th class="width-15-per">{{trans('project::view.Participants')}}</th>
                <th class="width-10-per">{{trans('project::view.Start Date')}}</th>
                <th class="width-10-per">{{trans('project::view.End date')}}</th>
                <th class="width-10-per">{{trans('project::view.Result')}}</th>
                <th class="width-10-per">{{trans('project::view.Note')}}</th>
                @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                    <th class="width-5-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($allTrainings as $key => $training)
            <?php
                $hasChild = false;
                if($training->status == Training::STATUS_APPROVED) {
                    if (count($training->projectTrainingChild) > 0) {
                        $hasChild = true;
                    }
                }
            ?>
            <tr class="background-{{ViewProject::getColorStatusWorkOrder($training->status)}} tr-training-{{$training->id}} tr-training-css"  data-toggle="tooltip" data-placement="top" title="{{Training::statusLabel()[$training->status]}}">
                <td>{{$key + 1}}</td>
                <td>
                    <span class="topic-training-{{$training->id}} white-space">{!!nl2br(e($training->topic))!!}</span>
                
                    <textarea class="display-none form-control input-topic-training-{{$training->id}} white-space" name="topic" rows="2">{{$training->topic}}</textarea>
                </td>
                <td>
                    <span class="description-training-{{$training->id}} white-space">{!!nl2br(e($training->description))!!}</span>
                
                    <textarea class="display-none form-control input-description-training-{{$training->id}} white-space" name="description" rows="2">{{$training->description}}</textarea>
                </td>
                <td class="td-training-member">
                    <?php
                        $memberTraning = Training::getAllMemberOfTraining($training->id);
                        $dataValue = '';
                        if ($memberTraning) {
                            $dataValue = implode(",",$memberTraning);
                        }
                    ?>
                    <span class="participants-training-{{$training->id}} white-space" data-value="{{$dataValue}}">{{ViewProject::getContentParticipants($training, $allEmployee)}}</span>
                    <select class="display-none form-control width-100 input-participants-training-{{$training->id}} training-member-select2" multiple="multiple">
                    @foreach($allEmployee as $key => $employee)
                    @if(!in_array($employee->email, $arrayCoo))
                        <option value="{{$employee->id}}" class="form-control width-100" {{in_array($employee->id, $memberTraning) ? 'selected' : ''}}>{{preg_replace('/@.*/', '', $employee->email)}}</option>
                    @endif    
                    @endforeach                        
                    </select>
                </td>
                <td>
                    <span class="start_at-training-{{$training->id}}" data-value="{{$training->type}}">{{ViewHelper::getDate($training->start_at)}}</span>
                    <input type="text" class="display-none form-control width-100 input-start_at-training-{{$training->id}}" name="start_at" value="{{ViewHelper::getDate($training->start_at)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span class="end_at-training-{{$training->id}}">{{ViewHelper::getDate($training->end_at)}}</span>
                    <input type="text" class="display-none form-control width-100 input-end_at-training-{{$training->id}}" name="end_at" value="{{ViewHelper::getDate($training->end_at)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span class="result-training-{{$training->id}}" data-value="{{$training->result}}">{{\Rikkei\Project\Model\Training::getLabelStatusTraining($training->result)}}</span>
                    <select name="result" class="form-control display-none input-result-training-{{$training->id}}">
                        <option value=""></option>
                        @foreach(\Rikkei\Project\Model\Training::getStatusResult() as $key => $item)
                            <option value="{{$key}}" @if ($training->result == $key) selected @endif>{{$item}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <span class="walver_criteria-training-{{$training->id}} white-space">{!!nl2br(e($training->walver_criteria))!!}</span>
                
                    <textarea class="display-none form-control input-walver_criteria-training-{{$training->id}} white-space" name="walver_criteria" rows="2">{{$training->walver_criteria}}</textarea>
                </td>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <td>
                    @if(($training->status == Training::STATUS_APPROVED && !$hasChild) ||  $training->status == Training::STATUS_DRAFT ||  $training->status == Training::STATUS_FEEDBACK)
                    <span>
                        <i class="fa fa-floppy-o display-none btn-add save-training save-training-{{$training->id}}" data-id="{{$training->id}}" data-status="{{$training->status}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-training edit-training-{{$training->id}}" data-id="{{$training->id}}" data-status="{{$training->status}}"></i>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_TRANING]}}-{{$training->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-trash-o btn-delete delete-training delete-confirm-new delete-training-{{$training->id}}" data-id="{{$training->id}}" data-status="{{$training->status}}"></i>
                        <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-training refresh-training-{{$training->id}}" data-id="{{$training->id}}" data-status="{{$training->status}}"></i>
                    </span>
                    @endif
                </td>
                @endif
            </tr>
            @if($training->status == Training::STATUS_APPROVED)
            @if(count($training->projectTrainingChild) > 0)
            <?php $training = $training->projectTrainingChild;?> 
             <tr class="background-{{ViewProject::getColorStatusWorkOrder($training->status)}} tr-training-{{$training->id}} tr-training-css" data-toggle="tooltip" data-placement="top" title="{{Training::statusLabel()[$training->status]}}">
                <td></td>
                <td>
                    <span class="topic-training-{{$training->id}} white-space">{!!nl2br(e($training->topic))!!}</span>
                
                    <textarea class="display-none form-control input-topic-training-{{$training->id}} white-space" name="topic" rows="2">{{$training->topic}}</textarea>
                </td>
                <td>
                    <span class="description-training-{{$training->id}} white-space">{!!nl2br(e($training->description))!!}</span>
                
                    <textarea class="display-none form-control input-description-training-{{$training->id}} white-space" name="description" rows="2">{{$training->description}}</textarea>
                </td>
                <td class="td-training-member">
                    <?php
                        $memberTraning = Training::getAllMemberOfTraining($training->id);
                        $dataValue = '';
                        if ($memberTraning) {
                            $dataValue = implode(",",$memberTraning);
                        }
                    ?>
                    <span class="participants-training-{{$training->id}} white-space" data-value="{{$dataValue}}">{{ViewProject::getContentParticipants($training, $allEmployee)}}</span>
                    <select class="display-none form-control width-100 input-participants-training-{{$training->id}} training-member-select2" multiple="multiple">
                    @foreach($allEmployee as $key => $employee)
                    @if(!in_array($employee->email, $arrayCoo))
                        <option value="{{$employee->id}}" class="form-control width-100" {{in_array($employee->id, $memberTraning) ? 'selected' : ''}}>{{preg_replace('/@.*/', '', $employee->email)}}</option>
                    @endif    
                    @endforeach                        
                    </select>
                </td>
                <td>
                    <span class="start_at-training-{{$training->id}}" data-value="{{$training->type}}">{{ViewHelper::getDate($training->start_at)}}</span>
                    <input type="text" class="display-none form-control width-100 input-start_at-training-{{$training->id}}" name="start_at" value="{{ViewHelper::getDate($training->start_at)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span class="end_at-training-{{$training->id}}">{{ViewHelper::getDate($training->end_at)}}</span>
                    <input type="text" class="display-none form-control width-100 input-end_at-training-{{$training->id}}" name="end_at" value="{{ViewHelper::getDate($training->end_at)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                 <td>
                     <span class="result-training-{{$training->id}}" data-value="{{$training->result}}">{{\Rikkei\Project\Model\Training::getLabelStatusTraining($training->result)}}</span>
                     <select name="result" class="form-control display-none input-result-training-{{$training->id}}">
                         <option value=""></option>
                         @foreach(\Rikkei\Project\Model\Training::getStatusResult() as $key => $item)
                             <option value="{{$key}}" @if ($training->result == $key) selected @endif>{{$item}}</option>
                         @endforeach
                     </select>
                 </td>
                <td>
                    <span class="walver_criteria-training-{{$training->id}} white-space">{!!nl2br(e($training->walver_criteria))!!}</span>
                
                    <textarea class="display-none form-control input-walver_criteria-training-{{$training->id}} white-space" name="walver_criteria" rows="2">{{$training->walver_criteria}}</textarea>
                </td>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <td>
                @if($training->status == Training::STATUS_DRAFT ||
                $training->status == Training::STATUS_FEEDBACK ||
                $training->status == Training::STATUS_DRAFT_EDIT ||
                $training->status == Training::STATUS_FEEDBACK_EDIT ||
                $training->status == Training::STATUS_FEEDBACK_DELETE ||
                $training->status == Training::STATUS_DRAFT_DELETE)
                    <span>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_TRANING]}}-{{$training->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        @if ($training->status == Training::STATUS_DRAFT_DELETE ||
                        $training->status == Training::STATUS_FEEDBACK_DELETE
                        )
                        <i class="fa fa-trash-o btn-delete delete-training delete-confirm-new delete-training-{{$training->id}}" data-id="{{$training->id}}" data-status="{{$training->status}}"></i>
                        @else
                        <i class="fa fa-floppy-o display-none btn-add save-training save-training-{{$training->id}}" data-id="{{$training->id}}" data-status="{{$training->status}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-training edit-training-{{$training->id}}" data-id="{{$training->id}}" data-status="{{$training->status}}"></i>
                        <i class="fa fa-trash-o btn-delete delete-training delete-confirm-new delete-training-{{$training->id}}" data-id="{{$training->id}}" data-status="{{$training->status}}"></i>
                        <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-training refresh-training-{{$training->id}}" data-id="{{$training->id}}" data-status="{{$training->status}}"></i>
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
            <tr class="tr-add-training">
                <td colspan="8" class="slove-training">
                  <span href="#" class="btn-add add-training"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            <tr class="display-none tr-training tr-training-hidden tr-training-css">
                <td></td>
                <td>
                    <span>
                        <textarea class="form-control width-100 topic-training" name="topic" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 description-training" name="description" rows="2"></textarea>
                    </span>
                </td>
                <td class="td-training-member">
                    <select name="employee_id" class="form-control width-100 training-member-training training-member-select2-new" multiple="multiple">
                    @foreach($allEmployee as $key => $employee)
                    @if(!in_array($employee->email, $arrayCoo))
                        <option value="{{$employee->id}}" class="form-control width-100">{{preg_replace('/@.*/', '', $employee->email)}}</option>
                    @endif    
                    @endforeach                        
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control width-100 start_at-training" name="start_at" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <input type="text" class="form-control width-100 end_at-training" name="end_at" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <select name="result" class="form-control result-training">
                        <option value=""></option>
                        @foreach(\Rikkei\Project\Model\Training::getStatusResult() as $key => $item)
                            <option value="{{$key}}" @if ($training->result == $key) selected @endif>{{$item}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 walver_criteria-training" name="walver_criteria" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item loading-item-{{$allNameTab[Task::TYPE_WO_TRANING]}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-training"></i>
                        <i class="fa fa-trash-o btn-delete remove-training"></i>
                    </span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@else
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_TRANING]}}" id="table-training">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="width-5-per">{{trans('project::view.No')}}</th>
                <th class="width-15-per">{{trans('project::view.Topic')}}</th>
                <th class="width-15-per">{{trans('project::view.Description')}}</th>
                <th class="width-15-per">{{trans('project::view.Participants')}}</th>
                <th class="width-10-per">{{trans('project::view.Start Date')}}</th>
                <th class="width-10-per">{{trans('project::view.End date')}}</th>
                <th class="width-10-per">{{trans('project::view.Result')}}</th>
                <th class="width-10-per">{{trans('project::view.Note')}}</th>
                @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                <th class="width-5-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($allTrainings as $key => $training)
            <tr class="tr-training-{{$training->id}} tr-training-css">
                <td>{{$key + 1}}</td>
                <td>
                    <span class="topic-training-{{$training->id}} white-space">{!!nl2br(e($training->topic))!!}</span>
                
                    <textarea class="display-none form-control input-topic-training-{{$training->id}} white-space" name="topic" rows="2">{{$training->topic}}</textarea>
                </td>
                <td>
                    <span class="description-training-{{$training->id}} white-space">{!!nl2br(e($training->description))!!}</span>
                
                    <textarea class="display-none form-control input-description-training-{{$training->id}} white-space" name="description" rows="2">{{$training->description}}</textarea>
                </td>
                <td>
                    <?php
                        $memberTraning = Training::getAllMemberOfTraining($training->id);
                        $dataValue = '';
                        if ($memberTraning) {
                            $dataValue = implode(",",$memberTraning);
                        }
                    ?>
                    <span class="participants-training-{{$training->id}} white-space" data-value="{{$dataValue}}">{{ViewProject::getContentParticipants($training, $allEmployee)}}</span>
                    <select class="display-none form-control width-100 input-participants-training-{{$training->id}} training-member-select2" multiple="multiple">
                    @foreach($allEmployee as $key => $employee)
                    @if(!in_array($employee->email, $arrayCoo))
                        <option value="{{$employee->id}}" class="form-control width-100" {{in_array($employee->id, $memberTraning) ? 'selected' : ''}}>{{preg_replace('/@.*/', '', $employee->email)}}</option>
                    @endif    
                    @endforeach                        
                    </select>
                </td>
                <td>
                    <span class="start_at-training-{{$training->id}}" data-value="{{$training->type}}">{{ViewHelper::getDate($training->start_at)}}</span>
                    <input type="text" class="display-none form-control width-100 input-start_at-training-{{$training->id}}" name="start_at" value="{{ViewHelper::getDate($training->start_at)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span class="end_at-training-{{$training->id}}">{{ViewHelper::getDate($training->end_at)}}</span>
                    <input type="text" class="display-none form-control width-100 input-end_at-training-{{$training->id}}" name="end_at" value="{{ViewHelper::getDate($training->end_at)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span class="result-training-{{$training->id}}" data-value="{{$training->result}}">{{\Rikkei\Project\Model\Training::getLabelStatusTraining($training->result)}}</span>
                    <select name="result" class="form-control display-none input-result-training-{{$training->id}}">
                        <option value=""></option>
                        @foreach(\Rikkei\Project\Model\Training::getStatusResult() as $key => $item)
                        <option value="{{$key}}" @if ($training->result == $key) selected @endif>{{$item}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <span class="walver_criteria-training-{{$training->id}} white-space">{!!nl2br(e($training->walver_criteria))!!}</span>
                    <textarea class="display-none form-control input-walver_criteria-training-{{$training->id}} white-space" name="walver_criteria" rows="2">{{$training->walver_criteria}}</textarea>
                </td>
                @if(isset($permissionEdit) && $permissionEdit)
                <td>
                    <span>
                        <i class="fa fa-floppy-o display-none btn-add save-training save-training-{{$training->id}}" data-id="{{$training->id}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-training edit-training-{{$training->id}}" data-id="{{$training->id}}"></i>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_TRANING]}}-{{$training->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-trash-o btn-delete delete-training delete-confirm-new delete-training-{{$training->id}}" data-id="{{$training->id}}"></i>
                        <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-training refresh-training-{{$training->id}}" data-id="{{$training->id}}" data-status="{{$training->status}}"></i>
                    </span>
                </td>
                @endif
            </tr>
            @endforeach
            @if(isset($permissionEdit) && $permissionEdit)
            <tr class="tr-add-training">
                <td colspan="8" class="slove-training">
                  <span href="#" class="btn-add add-training"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            <tr class="display-none tr-training tr-training-hidden tr-training-css">
                <td></td>
                <td>
                    <span>
                        <textarea class="form-control width-100 topic-training" name="topic" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 description-training" name="description" rows="2"></textarea>
                    </span>
                </td>
                <td class="td-training-member">
                    <select name="employee_id" class="form-control width-100 training-member-training training-member-select2-new" multiple="multiple">
                    @foreach($allEmployee as $key => $employee)
                    @if(!in_array($employee->email, $arrayCoo))
                        <option value="{{$employee->id}}" class="form-control width-100">{{preg_replace('/@.*/', '', $employee->email)}}</option>
                    @endif    
                    @endforeach                        
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control width-100 start_at-training" name="start_at" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <input type="text" class="form-control width-100 end_at-training" name="end_at" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <select name="result" class="form-control result-training">
                        <option value=""></option>
                        @foreach(\Rikkei\Project\Model\Training::getStatusResult() as $key => $item)
                            <option value="{{$key}}">{{$item}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 walver_criteria-training" name="walver_criteria" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_TRANING]}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-training"></i>
                        <i class="fa fa-trash-o btn-delete remove-training"></i>
                    </span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif
@endif
