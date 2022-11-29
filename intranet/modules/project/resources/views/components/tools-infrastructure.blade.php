<?php
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\ToolAndInfrastructure;
use Rikkei\Project\Model\Task;
$allNameTab = Task::getAllNameTabWorkorder();

?>
<h5 class="box-title">
@if(isset($detail))
    <span class="slove-tool-and-infrastructure" id="slove-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}">
        <span class="btn btn-primary show-content-table margin-right-10 show-content-table-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}} display-none" data-type="{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}"><i class="fa fa-chevron-down"></i></span>
        <span class="btn btn-primary hide-content-table margin-right-10 hide-content-table-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}" data-type="{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}"><i class="fa fa-chevron-up"></i></span>  {{trans('project::view.Tools and infrastructure')}}
    </span>
@else
    <span class="slove-add-tool-and-infrastructure" id="slove-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}">
        <span class="btn btn-primary show-content-table margin-right-10 show-content-table-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}" data-type="{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}"><i class="fa fa-chevron-down"></i></span>
        <span class="btn btn-primary hide-content-table margin-right-10 display-none hide-content-table-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}" data-type="{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}"><i class="fa fa-chevron-up"></i></span>
    </span>
    {{trans('project::view.Tools and infrastructure')}}
    <span class="btn btn-primary loading-workorder display-none" id="loading-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}"><i class="fa fa-refresh fa-spin"></i></span>
@endif
</h5>
@if(isset($detail))
@if(config('project.workorder_approved.tool_and_infrastructure'))
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}" id="table-tool-and-infrastructure">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="width-5-per">{{trans('project::view.No')}}</th>
                <th class="width-20-per">{{trans('project::view.Software/Hardware')}}</th>
                <th class="width-20-per">{{trans('project::view.Purpose')}}</th>
                <th class="width-10-per">{{trans('project::view.Start date')}}</th>
                <th class="width-10-per">{{trans('project::view.End Date')}}</th>
                <th class="width-20-per">{{trans('project::view.Note')}}</th>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($allToolAndInfrastructure as $key => $tool)
            <?php
                $hasChild = false;
                if($tool->status == ToolAndInfrastructure::STATUS_APPROVED) {
                    if (count($tool->projectToolAndInfrastructureChild) > 0) {
                        $hasChild = true;
                    }
                }
            ?>
            <tr class="background-{{ViewProject::getColorStatusWorkOrder($tool->status)}} tr-tool-and-infrastructure-{{$tool->id}} tr-tool-css" data-toggle="tooltip" data-placement="top" title="{{ToolAndInfrastructure::statusLabel()[$tool->status]}}">
                <td>{{$key + 1}}</td>
                <td>
                    <span class="soft_hard_ware-tool-and-infrastructure-{{$tool->id}} white-space">{!!nl2br(e($tool->soft_hard_ware))!!}</span>

                    <select name="soft_hard_ware" class="select-soft-hard-ware form-control width-100 display-none input-soft_hard_ware-tool-and-infrastructure-{{$tool->id}} white-space">
                        @if (!array_key_exists($tool->software_id, $allSoftware))
                            <option value="{{$tool->software_id}}">{{$tool->soft_hard_ware}}</option>
                        @endif
                        @foreach($allSoftware as $key =>  $option)
                            <option value="{{$key}}" {{$key == $tool->software_id ? 'selected' : ''}}>{{$option}}</option>
                        @endforeach
                    </select>
{{--                    <textarea class="display-none form-control input-soft_hard_ware-tool-and-infrastructure-{{$tool->id}} white-space" name="soft_hard_ware" rows="2">{{$tool->soft_hard_ware}}</textarea>--}}
                </td>
                <td>
                    <span class="purpose-tool-and-infrastructure-{{$tool->id}} white-space">{!!nl2br(e($tool->purpose))!!}</span>

                    <textarea class="display-none form-control input-purpose-tool-and-infrastructure-{{$tool->id}} white-space" name="purpose" rows="2">{{$tool->purpose}}</textarea>
                </td>
                <td>
                    <span class="start-date-tool-and-infrastructure-{{$tool->id}}">{{$tool->start_date}}</span>
                    <input type="text" class="display-none form-control width-100 input-start-date-tool-and-infrastructure-{{$tool->id}}" name="start_date" value="{{$tool->start_date}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span class="end-date-tool-and-infrastructure-{{$tool->id}}">{{$tool->end_date}}</span>
                    <input type="text" class="display-none form-control width-100 input-end-date-tool-and-infrastructure-{{$tool->id}}" name="end_date" value="{{$tool->end_date}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span class="note-tool-and-infrastructure-{{$tool->id}} white-space">{!!nl2br(e($tool->note))!!}</span>

                    <textarea class="display-none form-control input-note-tool-and-infrastructure-{{$tool->id}} white-space" name="note" rows="2">{{$tool->note}}</textarea>
                </td>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <td>
                    @if(($tool->status == ToolAndInfrastructure::STATUS_APPROVED && !$hasChild) ||  $tool->status == ToolAndInfrastructure::STATUS_DRAFT ||  $tool->status == ToolAndInfrastructure::STATUS_FEEDBACK)
                    <span>
                        <i class="fa fa-floppy-o display-none btn-add save-tool-and-infrastructure save-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}" data-status="{{$tool->status}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-tool-and-infrastructure edit-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}" data-status="{{$tool->status}}"></i>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}-{{$tool->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-trash-o btn-delete delete-tool-and-infrastructure delete-confirm-new delete-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}" data-status="{{$tool->status}}"></i>
                        <i class="display-none fa fa-ban btn-refresh btn-primary refresh-tool-and-infrastructure refresh-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}" data-status="{{$tool->status}}"></i>
                    </span>
                    @endif
                </td>
                @endif
            </tr>
            @if($tool->status == ToolAndInfrastructure::STATUS_APPROVED)
            @if(count($tool->projectToolAndInfrastructureChild) > 0)
            <?php $tool = $tool->projectToolAndInfrastructureChild;?>
             <tr class="background-{{ViewProject::getColorStatusWorkOrder($tool->status)}} tr-tool-and-infrastructure-{{$tool->id}} tr-tool-css" data-toggle="tooltip" data-placement="top" title="{{ToolAndInfrastructure::statusLabel()[$tool->status]}}">
                <td></td>
                <td>
                    <span class="soft_hard_ware-tool-and-infrastructure-{{$tool->id}} white-space">{!!nl2br(e($tool->soft_hard_ware))!!}</span>

                    <select name="soft_hard_ware" class="select-soft-hard-ware form-control width-100 display-none input-soft_hard_ware-tool-and-infrastructure-{{$tool->id}} white-space">
                        @if (!array_key_exists($tool->software_id, $allSoftware))
                            <option value="{{$tool->software_id}}">{{$tool->soft_hard_ware}}</option>
                        @endif
                        @foreach($allSoftware as $key =>  $option)
                            <option value="{{$key}}" {{$key == $tool->software_id ? 'selected' : ''}}>{{$option}}</option>
                        @endforeach
                    </select>
{{--                    <textarea class="display-none form-control input-soft_hard_ware-tool-and-infrastructure-{{$tool->id}} white-space" name="soft_hard_ware" rows="2">{{$tool->soft_hard_ware}}</textarea>--}}
                </td>
                <td>
                    <span class="purpose-tool-and-infrastructure-{{$tool->id}} white-space">{!!nl2br(e($tool->purpose))!!}</span>

                    <textarea class="display-none form-control input-purpose-tool-and-infrastructure-{{$tool->id}} white-space" name="purpose" rows="2">{{$tool->purpose}}</textarea>
                </td>
                <td>
                    <span class="note-tool-and-infrastructure-{{$tool->id}} white-space">{!!nl2br(e($tool->note))!!}</span>

                    <textarea class="display-none form-control input-note-tool-and-infrastructure-{{$tool->id}} white-space" name="note" rows="2">{{$tool->note}}</textarea>
                </td>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <td>
                @if($tool->status == ToolAndInfrastructure::STATUS_DRAFT ||
                $tool->status == ToolAndInfrastructure::STATUS_FEEDBACK ||
                $tool->status == ToolAndInfrastructure::STATUS_DRAFT_EDIT ||
                $tool->status == ToolAndInfrastructure::STATUS_FEEDBACK_EDIT ||
                $tool->status == ToolAndInfrastructure::STATUS_FEEDBACK_DELETE ||
                $tool->status == ToolAndInfrastructure::STATUS_DRAFT_DELETE)
                    <span>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}-{{$tool->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        @if ($tool->status == ToolAndInfrastructure::STATUS_DRAFT_DELETE ||
                        $tool->status == ToolAndInfrastructure::STATUS_FEEDBACK_DELETE
                        )
                        <i class="fa fa-trash-o btn-delete delete-tool-and-infrastructure delete-confirm-new delete-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}" data-status="{{$tool->status}}"></i>
                        @else
                        <i class="fa fa-floppy-o display-none btn-add save-tool-and-infrastructure save-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}" data-status="{{$tool->status}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-tool-and-infrastructure edit-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}" data-status="{{$tool->status}}"></i>
                        <i class="fa fa-trash-o btn-delete delete-tool-and-infrastructure delete-confirm-new delete-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}" data-status="{{$tool->status}}"></i>
                        <i class="display-none fa fa-ban btn-refresh btn-primary refresh-tool-and-infrastructure refresh-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}" data-status="{{$tool->status}}"></i>
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
            <tr class="tr-add-tool-and-infrastructure">
                <td colspan="7" class="slove-tool-and-infrastructure">
                  <span href="#" class="btn-add add-tool-and-infrastructure"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            <tr class="display-none tr-tool-and-infrastructure tr-tool-css">
                <td></td>
                <td>
                    <span>
                        <select name="soft_hard_ware" class="select-soft-hard-ware form-control width-100 soft_hard_ware-tool-and-infrastructure">
                            @foreach($allSoftware as $key => $option)
                                <option value="{{$key}}">{{$option}}</option>
                            @endforeach
                        </select>
{{--                        <textarea class="form-control width-100 soft_hard_ware-tool-and-infrastructure" name="soft_hard_ware" rows="2"></textarea>--}}
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 purpose-tool-and-infrastructure" name="purpose" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <input type="text" class="form-control width-100 start-date-tool-and-infrastructure" name="start_date" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <input type="text" class="form-control width-100 end-date-tool-and-infrastructure" name="end_date" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 note-tool-and-infrastructure" name="note" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item loading-item-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-tool-and-infrastructure"></i>
                        <i class="fa fa-trash-o btn-delete remove-tool-and-infrastructure"></i>
                    </span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@else
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}" id="table-tool-and-infrastructure">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="width-5-per">{{trans('project::view.No')}}</th>
                <th class="width-20-per">{{trans('project::view.Software/Hardware')}}</th>
                <th class="width-20-per">{{trans('project::view.Purpose')}}</th>
                <th class="width-10-per">{{trans('project::view.Start date')}}</th>
                <th class="width-10-per">{{trans('project::view.End Date')}}</th>
                <th class="width-20-per">{{trans('project::view.Note')}}</th>
                @if(isset($permissionEdit) && $permissionEdit)
                <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($allToolAndInfrastructure as $key => $tool)
            <tr class="tr-tool-and-infrastructure-{{$tool->id}} tr-tool-css">
                <td>{{$key + 1}}</td>
                <td>
                    <span class="soft_hard_ware-tool-and-infrastructure-{{$tool->id}} white-space">{!!nl2br(e($tool->soft_hard_ware))!!}</span>

                    <select name="soft_hard_ware" class="select-soft-hard-ware form-control width-100 display-none input-soft_hard_ware-tool-and-infrastructure-{{$tool->id}} white-space">
                        {{ $tool->software_id }}
                        @if (!array_key_exists($tool->software_id, $allSoftware))
                            <option value="{{$tool->software_id}}">{{$tool->soft_hard_ware}}</option>
                        @endif
                        @foreach($allSoftware as $key =>  $option)
                            <option value="{{$key}}" {{$key == $tool->software_id ? 'selected' : ''}}>{{$option}}</option>
                        @endforeach
                    </select>
{{--                    <textarea class="display-none form-control input-soft_hard_ware-tool-and-infrastructure-{{$tool->id}} white-space" name="soft_hard_ware" rows="2">{{$tool->soft_hard_ware}}</textarea>--}}
                </td>
                <td>
                    <span class="purpose-tool-and-infrastructure-{{$tool->id}} white-space">{!!nl2br(e($tool->purpose))!!}</span>

                    <textarea class="display-none form-control input-purpose-tool-and-infrastructure-{{$tool->id}} white-space" name="purpose" rows="2">{{$tool->purpose}}</textarea>
                </td>
                <td>
                    <span class="start-date-tool-and-infrastructure-{{$tool->id}}">{{$tool->start_date}}</span>
                    <input type="text" class="display-none form-control width-100 input-start-date-tool-and-infrastructure-{{$tool->id}}" name="start_date_tool" value="{{$tool->start_date}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span class="end-date-tool-and-infrastructure-{{$tool->id}}">{{$tool->end_date}}</span>
                    <input type="text" class="display-none form-control width-100 input-end-date-tool-and-infrastructure-{{$tool->id}}" name="end_date_tool" value="{{$tool->end_date}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span class="note-tool-and-infrastructure-{{$tool->id}} white-space">{!!nl2br(e($tool->note))!!}</span>

                    <textarea class="display-none form-control input-note-tool-and-infrastructure-{{$tool->id}} white-space" name="note" rows="2">{{$tool->note}}</textarea>
                </td>
                @if(isset($permissionEdit) && $permissionEdit)
                <td>
                    <span>
                        <i class="fa fa-floppy-o display-none btn-add save-tool-and-infrastructure save-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-tool-and-infrastructure edit-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}"></i>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}-{{$tool->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-trash-o btn-delete delete-tool-and-infrastructure delete-confirm-new delete-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}"></i>
                        <i class="display-none fa fa-ban btn-refresh btn-primary refresh-tool-and-infrastructure refresh-tool-and-infrastructure-{{$tool->id}}" data-id="{{$tool->id}}" data-status="{{$tool->status}}"></i>
                    </span>
                </td>
                @endif
            </tr>
            @endforeach
            @if(isset($permissionEdit) && $permissionEdit)
            <tr class="tr-add-tool-and-infrastructure">
                <td colspan="7" class="slove-tool-and-infrastructure">
                  <span href="#" class="btn-add add-tool-and-infrastructure"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            <tr class="display-none tr-tool-and-infrastructure tr-tool-css">
                <td></td>
                <td>
                    <span>
                        <select name="soft_hard_ware" class="select-soft-hard-ware form-control width-100 soft_hard_ware-tool-and-infrastructure">
                            @foreach($allSoftware as $key => $option)
                                <option value="{{$key}}">{{$option}}</option>
                            @endforeach
                        </select>
                        <span class="soft_hard_ware_tool-and-infrastructure"></span>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 purpose-tool-and-infrastructure" name="purpose" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <input type="text" class="form-control width-100 start-date-tool-and-infrastructure" name="start_date" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <input type="text" class="form-control width-100 end-date-tool-and-infrastructure" name="end_date" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" data-date-week-start="1" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 note-tool-and-infrastructure" name="note" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-tool-and-infrastructure"></i>
                        <i class="fa fa-trash-o btn-delete remove-tool-and-infrastructure"></i>
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
