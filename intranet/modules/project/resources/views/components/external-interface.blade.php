<?php 
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\ExternalInterface;
use Rikkei\Project\Model\Task;

$allNameTab = Task::getAllNameTabWorkorder();
?>
<h5 class="box-title">
@if(isset($detail)) 
    <span class="slove-external-interface" id="slove-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}">
        <span class="btn btn-primary show-content-table margin-right-10 show-content-table-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}} display-none" data-type="{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}"><i class="fa fa-chevron-down"></i></span>
        <span class="btn btn-primary hide-content-table margin-right-10 hide-content-table-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}" data-type="{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}"><i class="fa fa-chevron-up"></i></span> 
        {{trans('project::view.External Interface')}}
    </span>
@else
    <span class="slove-add-external-interface" id="slove-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}">
        <span class="btn btn-primary show-content-table margin-right-10 show-content-table-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}" data-type="{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}"><i class="fa fa-chevron-down"></i></span>
        <span class="btn btn-primary hide-content-table margin-right-10 display-none hide-content-table-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}" data-type="{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}"><i class="fa fa-chevron-up"></i></span>
    </span>
    {{trans('project::view.External Interface')}}
    <span class="btn btn-primary loading-workorder display-none" id="loading-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}"><i class="fa fa-refresh fa-spin"></i></span>
@endif 
</h5>
@if(isset($detail))
@if(config('project.workorder_approved.external_interface'))
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}" id="table-external-interface">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="width-5-per">{{trans('project::view.No')}}</th>
                <th class="width-20-per">{{trans('project::view.Name')}}</th>
                <th class="width-20-per">{{trans('project::view.Position')}}</th>
                <th class="width-25-per">{{trans('project::view.Responsibilities')}}</th>
                <th class="width-20-per">{{trans('project::view.Text, Fax, Email')}}</th>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($allExternalInterfaces as $key => $external)
            <?php
                $hasChild = false;
                if($external->status == ExternalInterface::STATUS_APPROVED) {
                    if (count($external->projectExternalInterfaceChild) > 0) {
                        $hasChild = true;
                    }
                }
            ?>
            <tr class="background-{{ViewProject::getColorStatusWorkOrder($external->status)}}" data-toggle="tooltip" data-placement="top" title="{{ExternalInterface::statusLabel()[$external->status]}}">
                <td>{{$key + 1}}</td>
                <td>
                    <span class="name-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->name))!!}</span>
                
                    <textarea class="display-none form-control input-name-external-interface-{{$external->id}} white-space" name="name" rows="2">{{$external->name}}</textarea>
                </td>
                <td>
                    <span class="position-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->position))!!}</span>
                
                    <textarea class="display-none form-control input-position-external-interface-{{$external->id}} white-space" name="position" rows="2">{{$external->position}}</textarea>
                </td>
                <td>
                    <span class="responsibilities-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->responsibilities))!!}</span>
                
                    <textarea class="display-none form-control input-responsibilities-external-interface-{{$external->id}} white-space" name="responsibilities" rows="2">{{$external->responsibilities}}</textarea>
                </td>
                <td>
                    <span class="contact-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->contact))!!}</span>
                
                    <textarea class="display-none form-control input-contact-external-interface-{{$external->id}} white-space" name="contact" rows="2">{{$external->contact}}</textarea>
                </td>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <td>
                    @if(($external->status == ExternalInterface::STATUS_APPROVED && !$hasChild) ||  $external->status == ExternalInterface::STATUS_DRAFT ||  $external->status == ExternalInterface::STATUS_FEEDBACK)
                    <span>
                        <i class="fa fa-floppy-o display-none btn-add save-external-interface save-external-interface-{{$external->id}}" data-id="{{$external->id}}" data-status="{{$external->status}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-external-interface edit-external-interface-{{$external->id}}" data-id="{{$external->id}}" data-status="{{$external->status}}"></i>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}-{{$external->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-trash-o btn-delete delete-external-interface delete-confirm-new delete-external-interface-{{$external->id}}" data-id="{{$external->id}}" data-status="{{$external->status}}"></i>
                        <i class="display-none fa fa-ban btn-refresh btn-primary refresh-external-interface refresh-external-interface-{{$external->id}}" data-id="{{$external->id}}" data-status="{{$external->status}}"></i>
                    </span>
                    @endif
                </td>
                @endif
            </tr>
            @if($external->status == ExternalInterface::STATUS_APPROVED)
            @if(count($external->projectExternalInterfaceChild) > 0)
            <?php $external = $external->projectExternalInterfaceChild;?> 
             <tr class="background-{{ViewProject::getColorStatusWorkOrder($external->status)}}" data-toggle="tooltip" data-placement="top" title="{{ExternalInterface::statusLabel()[$external->status]}}">
                <td></td>
                <td>
                    <span class="name-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->name))!!}</span>
                
                    <textarea class="display-none form-control input-name-external-interface-{{$external->id}} white-space" name="name" rows="2">{{$external->name}}</textarea>
                </td>
                <td>
                    <span class="position-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->position))!!}</span>
                
                    <textarea class="display-none form-control input-position-external-interface-{{$external->id}} white-space" name="position" rows="2">{{$external->position}}</textarea>
                </td>
                <td>
                    <span class="responsibilities-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->responsibilities))!!}</span>
                
                    <textarea class="display-none form-control input-responsibilities-external-interface-{{$external->id}} white-space" name="responsibilities" rows="2">{{$external->responsibilities}}</textarea>
                </td>
                <td>
                    <span class="contact-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->contact))!!}</span>
                
                    <textarea class="display-none form-control input-contact-external-interface-{{$external->id}} white-space" name="contact" rows="2">{{$external->contact}}</textarea>
                </td>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <td>
                @if($external->status == ExternalInterface::STATUS_DRAFT ||
                $external->status == ExternalInterface::STATUS_FEEDBACK ||
                $external->status == ExternalInterface::STATUS_DRAFT_EDIT ||
                $external->status == ExternalInterface::STATUS_FEEDBACK_EDIT ||
                $external->status == ExternalInterface::STATUS_FEEDBACK_DELETE ||
                $external->status == ExternalInterface::STATUS_DRAFT_DELETE)
                    <span>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}-{{$external->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        @if ($external->status == ExternalInterface::STATUS_DRAFT_DELETE ||
                        $external->status == ExternalInterface::STATUS_FEEDBACK_DELETE
                        )
                        <i class="fa fa-trash-o btn-delete delete-external-interface delete-confirm-new delete-external-interface-{{$external->id}}" data-id="{{$external->id}}" data-status="{{$external->status}}"></i>
                        @else
                        <i class="fa fa-floppy-o display-none btn-add save-external-interface save-external-interface-{{$external->id}}" data-id="{{$external->id}}" data-status="{{$external->status}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-external-interface edit-external-interface-{{$external->id}}" data-id="{{$external->id}}" data-status="{{$external->status}}"></i>
                        <i class="fa fa-trash-o btn-delete delete-external-interface delete-confirm-new delete-external-interface-{{$external->id}}" data-id="{{$external->id}}" data-status="{{$external->status}}"></i>
                        <i class="display-none fa fa-ban btn-refresh btn-primary refresh-external-interface refresh-external-interface-{{$external->id}}" data-id="{{$external->id}}" data-status="{{$external->status}}"></i>
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
            <tr class="tr-add-external-interface">
                <td colspan="4" class="slove-external-interface">
                  <span href="#" class="btn-add add-external-interface"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            <tr class="display-none tr-external-interface">
                <td></td>
                <td>
                    <span>
                        <textarea class="form-control width-100 name-external-interface" name="name" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 position-external-interface" name="position" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 responsibilities-external-interface" name="responsibilities" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 contact-external-interface" name="contact" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item loading-item-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-external-interface"></i>
                        <i class="fa fa-trash-o btn-delete remove-external-interface"></i>
                    </span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@else
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}" id="table-external-interface">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="width-5-per">{{trans('project::view.No')}}</th>
                <th class="width-20-per">{{trans('project::view.Name')}}</th>
                <th class="width-20-per">{{trans('project::view.Position')}}</th>
                <th class="width-25-per">{{trans('project::view.Responsibilities')}}</th>
                <th class="width-20-per">{{trans('project::view.Text, Fax, Email')}}</th>
                @if(isset($permissionEdit) && $permissionEdit)
                <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($allExternalInterfaces as $key => $external)
            <tr>
                <td>{{$key + 1}}</td>
                <td>
                    <span class="name-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->name))!!}</span>
                
                    <textarea class="display-none form-control input-name-external-interface-{{$external->id}} white-space" name="name" rows="2">{{$external->name}}</textarea>
                </td>
                <td>
                    <span class="position-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->position))!!}</span>
                
                    <textarea class="display-none form-control input-position-external-interface-{{$external->id}} white-space" name="position" rows="2">{{$external->position}}</textarea>
                </td>
                <td>
                    <span class="responsibilities-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->responsibilities))!!}</span>
                
                    <textarea class="display-none form-control input-responsibilities-external-interface-{{$external->id}} white-space" name="responsibilities" rows="2">{{$external->responsibilities}}</textarea>
                </td>
                <td>
                    <span class="contact-external-interface-{{$external->id}} white-space">{!!nl2br(e($external->contact))!!}</span>
                
                    <textarea class="display-none form-control input-contact-external-interface-{{$external->id}} white-space" name="contact" rows="2">{{$external->contact}}</textarea>
                </td>
                @if(isset($permissionEdit) && $permissionEdit)
                <td>
                    <span>
                        <i class="fa fa-floppy-o display-none btn-add save-external-interface save-external-interface-{{$external->id}}" data-id="{{$external->id}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-external-interface edit-external-interface-{{$external->id}}" data-id="{{$external->id}}"></i>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}-{{$external->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-trash-o btn-delete delete-external-interface delete-confirm-new delete-external-interface-{{$external->id}}" data-id="{{$external->id}}"></i>
                        <i class="display-none fa fa-ban btn-refresh btn-primary refresh-external-interface refresh-external-interface-{{$external->id}}" data-id="{{$external->id}}" data-status="{{$external->status}}"></i>
                    </span>
                </td>
                @endif
            </tr>
            @endforeach
            @if(isset($permissionEdit) && $permissionEdit)
            <tr class="tr-add-external-interface">
                <td colspan="4" class="slove-external-interface">
                  <span href="#" class="btn-add add-external-interface"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            <tr class="display-none tr-external-interface">
                <td></td>
                <td>
                    <span>
                        <textarea class="form-control width-100 name-external-interface" name="name" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 position-external-interface" name="position" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 responsibilities-external-interface" name="responsibilities" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 contact-external-interface" name="contact" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-external-interface"></i>
                        <i class="fa fa-trash-o btn-delete remove-external-interface"></i>
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
