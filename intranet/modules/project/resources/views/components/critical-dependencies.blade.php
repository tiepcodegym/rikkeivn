<?php 
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\CriticalDependencie;
use Rikkei\Project\Model\Task;
use Illuminate\Support\Str;

$allNameTab = Task::getAllNameTabWorkorder();
$nameTableCritical = CriticalDependencie::getTableName();
?>
<div id="workorder_critical-dependencies">
@if(isset($detail))
    @if(config('project.workorder_approved.critical_dependencies'))
    <div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}" id="table-critical-dependencies">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
                <tr>
                    <th class="width-5-per">{{trans('project::view.No')}}</th>
                    <th class="width-15-per">{{trans('project::view.Critical dependencies')}}</th>
                    <th class="width-15-per">{{trans('project::view.Expected Delivery Date')}}</th>
                    @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <th class="width-5-per">&nbsp;</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($allCriticalDependencies as $key => $critical)
                <?php
                    $hasChild = false;
                    if($critical->status == CriticalDependencie::STATUS_APPROVED) {
                        if (count($critical->projectCriticalDependenciesChild) > 0) {
                            $hasChild = true;
                        }
                    }
                ?>
                <tr class="background-{{ViewProject::getColorStatusWorkOrder($critical->status)}}" data-toggle="tooltip" data-placement="top" title="{{CriticalDependencie::statusLabel()[$critical->status]}}">
                    <td>{{$key + 1}}</td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="critical" data-id="{{$critical->id}}" name="content" class="popover-wo-other content-critical-dependencies-{{$critical->id}} white-space">{!!Str::words(nl2br(e($critical->content)), 30, '...')!!}</span>
                    
                        <textarea class="white-space display-none form-control input-content-critical-dependencies-{{$critical->id}}" name="content" rows="2">{!! $critical->content !!}</textarea>
                    </td>
                    <td>
                        <span class="expected_date-critical-dependencies-{{$critical->id}}">{{ViewHelper::getDate($critical->expected_date)}}</span>
                        <input type="text" class="display-none form-control width-100 input-expected_date-critical-dependencies-{{$critical->id}}" name="expected_date" value="{{ViewHelper::getDate($critical->expected_date)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </td>
                    @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <td>
                        @if(($critical->status == CriticalDependencie::STATUS_APPROVED && !$hasChild) ||  $critical->status == CriticalDependencie::STATUS_DRAFT || $critical->status == CriticalDependencie::STATUS_FEEDBACK)
                        <span>
                            <i class="fa fa-floppy-o display-none btn-add save-critical-dependencies save-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}" data-status="{{$critical->status}}"></i>
                            <i class="fa fa-pencil-square-o width-38 btn-edit edit-critical-dependencies edit-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}" data-status="{{$critical->status}}"></i>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}-{{$critical->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-trash-o btn-delete delete-critical-dependencies delete-confirm-new delete-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}" data-status="{{$critical->status}}"></i>
                            <i class="display-none fa fa-ban btn-refresh btn-primary refresh-critical-dependencies refresh-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}" data-status="{{$critical->status}}"></i>
                        </span>
                        @endif
                    </td>
                    @endif
                </tr>
                @if($critical->status == CriticalDependencie::STATUS_APPROVED)
                @if(count($critical->projectCriticalDependenciesChild) > 0)
                <?php $critical = $critical->projectCriticalDependenciesChild;?> 
                 <tr class="background-{{ViewProject::getColorStatusWorkOrder($critical->status)}}" data-toggle="tooltip" data-placement="top" title="{{CriticalDependencie::statusLabel()[$critical->status]}}">
                    <td></td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="critical" data-id="{{$critical->id}}" name="content" class="popover-wo-other content-critical-dependencies-{{$critical->id}} white-space">{!! Str::words(nl2br(e($critical->content)), 30, '...') !!}</span>
                        <textarea class="white-space display-none form-control input-content-critical-dependencies-{{$critical->id}}" name="content" rows="2">{!! $critical->content !!}</textarea>
                    </td>
                     <td>
                         <span class="expected_date-critical-dependencies-{{$critical->id}}">{{ViewHelper::getDate($critical->expected_date)}}</span>
                         <input type="text" class="display-none form-control width-100 input-expected_date-critical-dependencies-{{$critical->id}}" name="expected_date" value="{{ViewHelper::getDate($critical->expected_date)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                     </td>
                    @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <td>
                    @if($critical->status == CriticalDependencie::STATUS_DRAFT ||
                    $critical->status == CriticalDependencie::STATUS_FEEDBACK ||
                    $critical->status == CriticalDependencie::STATUS_DRAFT_EDIT ||
                    $critical->status == CriticalDependencie::STATUS_FEEDBACK_EDIT ||
                    $critical->status == CriticalDependencie::STATUS_FEEDBACK_DELETE ||
                    $critical->status == CriticalDependencie::STATUS_DRAFT_DELETE)
                        <span>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}-{{$critical->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                            @if ($critical->status == CriticalDependencie::STATUS_DRAFT_DELETE ||
                            $critical->status == CriticalDependencie::STATUS_FEEDBACK_DELETE
                            )
                            <i class="fa fa-trash-o btn-delete delete-critical-dependencies delete-confirm-new delete-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}" data-status="{{$critical->status}}"></i>
                            @else
                            <i class="fa fa-floppy-o display-none btn-add save-critical-dependencies save-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}" data-status="{{$critical->status}}"></i>
                            <i class="fa fa-pencil-square-o width-38 btn-edit edit-critical-dependencies edit-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}" data-status="{{$critical->status}}"></i>
                            <i class="fa fa-trash-o btn-delete delete-critical-dependencies delete-confirm-new delete-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}" data-status="{{$critical->status}}"></i>
                            <i class="display-none fa fa-ban btn-refresh btn-primary refresh-critical-dependencies refresh-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}" data-status="{{$critical->status}}"></i>
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
                <tr class="tr-add-critical-dependencies">
                    <td colspan="4" class="slove-critical-dependencies">
                      <span href="#" class="btn-add add-critical-dependencies"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-critical-dependencies">
                    <td></td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 content-critical-dependencies" name="content" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <input type="text" class="form-control width-100 expected_date-critical-dependencies" name="expected_date" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-item loading-item-{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-critical-dependencies"></i>
                            <i class="fa fa-trash-o btn-delete remove-critical-dependencies"></i>
                        </span>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @else
    <div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}" id="table-critical-dependencies">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
                <tr>
                    <th class="width-5-per">{{trans('project::view.No')}}</th>
                    <th class="width-15-per">{{trans('project::view.Critical dependencies')}}</th>
                    <th class="width-15-per">{{trans('project::view.Expected date')}}</th>
                    @if(isset($permissionEdit) && $permissionEdit)
                    <th class="width-5-per">&nbsp;</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($allCriticalDependencies as $key => $critical)
                <tr>
                    <td>{{$key + 1}}</td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="critical" data-id="{{$critical->id}}" name="content" class="popover-wo-other content-critical-dependencies-{{$critical->id}} white-space">{!! Str::words(nl2br(e($critical->content)), 30, '...') !!}</span>
                    
                        <textarea class="white-space display-none form-control input-content-critical-dependencies-{{$critical->id}}" name="content" rows="2">{!! $critical->content !!}</textarea>
                    </td>
                    <td>
                        <span class="expected_date-critical-dependencies-{{$critical->id}}">{{ViewHelper::getDate($critical->expected_date)}}</span>
                        <input type="text" class="display-none form-control width-100 input-expected_date-critical-dependencies-{{$critical->id}}" name="expected_date" value="{{ViewHelper::getDate($critical->expected_date)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </td>
                    @if(isset($permissionEdit) && $permissionEdit)
                    <td>
                        <span>
                            <i class="fa fa-floppy-o display-none btn-add save-critical-dependencies save-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}"></i>
                            <i class="fa fa-pencil-square-o width-38 btn-edit edit-critical-dependencies edit-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}"></i>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}-{{$critical->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-trash-o btn-delete delete-critical-dependencies delete-confirm-new delete-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}"></i>
                            <i class="display-none fa fa-ban btn-refresh btn-primary refresh-critical-dependencies refresh-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}" data-status="{{$critical->status}}"></i>
                        </span>
                    </td>
                    @endif
                </tr>
                @endforeach
                @if(isset($permissionEdit) && $permissionEdit)
                <tr class="tr-add-critical-dependencies">
                    <td colspan="4" class="slove-critical-dependencies">
                      <span href="#" class="btn-add add-critical-dependencies"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-critical-dependencies">
                
                    <td></td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 content-critical-dependencies" name="content" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <input type="text" class="form-control width-100 expected_date-critical-dependencies" name="expected_date" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-critical-dependencies"></i>
                            <i class="fa fa-trash-o btn-delete remove-critical-dependencies"></i>
                        </span>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif
@else
        <div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}" id="table-critical-dependencies">
            <table class="edit-table table table-bordered table-condensed dataTable">
                <thead>
                <tr>
                    <th class="width-5-per">{{trans('project::view.No')}}</th>
                    <th class="width-15-per">{{trans('project::view.Critical dependencies')}}</th>
                    <th class="width-15-per">{{trans('project::view.Expected Delivery Date')}}</th>
                    @if(isset($permissionEdit) && $permissionEdit)
                        <th class="width-5-per">&nbsp;</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @foreach($allCriticalDependencies as $key => $critical)
                    <tr>
                        <td>{{$key + 1}}</td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="critical" data-id="{{$critical->id}}" name="content" class="popover-wo-other content-critical-dependencies-{{$critical->id}} white-space">{!! Str::words(nl2br(e($critical->content)), 30, '...') !!}</span>

                            <textarea class="white-space display-none form-control input-content-critical-dependencies-{{$critical->id}}" name="content" rows="2">{!! $critical->content !!}</textarea>
                        </td>
                        <td>
                            <span class="expected_date-critical-dependencies-{{$critical->id}}">{{ViewHelper::getDate($critical->expected_date)}}</span>
                            <input type="text" class="display-none form-control width-100 input-expected_date-critical-dependencies-{{$critical->id}}" name="expected_date" value="{{ViewHelper::getDate($critical->expected_date)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit)
                            <td>
                        <span>
                            <i class="fa fa-floppy-o display-none btn-add save-critical-dependencies save-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}"></i>
                            <i class="fa fa-pencil-square-o width-38 btn-edit edit-critical-dependencies edit-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}"></i>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}-{{$critical->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-trash-o btn-delete delete-critical-dependencies delete-confirm-new delete-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}"></i>
                            <i class="display-none fa fa-ban btn-refresh btn-primary refresh-critical-dependencies refresh-critical-dependencies-{{$critical->id}}" data-id="{{$critical->id}}" data-status="{{$critical->status}}"></i>
                        </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
                @if(isset($permissionEdit) && $permissionEdit)
                    <tr class="tr-add-critical-dependencies">
                        <td colspan="4" class="slove-critical-dependencies">
                            <span href="#" class="btn-add add-critical-dependencies"><i class="fa fa-plus"></i></span>
                        </td>
                    </tr>
                    <tr class="display-none tr-critical-dependencies">

                        <td></td>
                        <td>
                        <span>
                            <textarea class="form-control width-100 content-critical-dependencies" name="content" rows="2"></textarea>
                        </span>
                        </td>
                        <td>
                            <input type="text" class="form-control width-100 expected_date-critical-dependencies" name="expected_date" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                        </td>
                        <td>
                        <span>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-critical-dependencies"></i>
                            <i class="fa fa-trash-o btn-delete remove-critical-dependencies"></i>
                        </span>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
@endif
<hr>
</div>