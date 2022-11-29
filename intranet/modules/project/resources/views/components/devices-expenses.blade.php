<?php
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\DevicesExpense;
use Rikkei\Project\Model\Task;
use Carbon\Carbon;
$allNameTab = Task::getAllNameTabWorkorder();

?>
<h5 class="box-title">
    @if(isset($detail))
    <span class="slove-derived-expenses" id="slove-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}">
        <span class="btn btn-primary show-content-table margin-right-10 show-content-table-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}} display-none" data-type="{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}"><i class="fa fa-chevron-down"></i></span>
        <span class="btn btn-primary hide-content-table margin-right-10 hide-content-table-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}" data-type="{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}"><i class="fa fa-chevron-up"></i></span>  {{trans('project::view.Devices expenses')}}
    </span>
    @else
    <span class="slove-add-derived-expenses" id="slove-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}">
        <span class="btn btn-primary show-content-table margin-right-10 show-content-table-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}" data-type="{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}"><i class="fa fa-chevron-down"></i></span>
        <span class="btn btn-primary hide-content-table margin-right-10 display-none hide-content-table-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}" data-type="{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}"><i class="fa fa-chevron-up"></i></span>
    </span>
    {{trans('project::view.Devices expenses')}}
    <span class="btn btn-primary loading-workorder display-none" id="loading-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}"><i class="fa fa-refresh fa-spin"></i></span>
    @endif
</h5>
@if(isset($detail))
@if(config('project.workorder_approved.devices_expenses'))
    <div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}" id="table-derived-expenses">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
                <tr>
                    <th class="width-5-per">{{trans('project::view.No')}}</th>
                    <th class="width-10-per">{{trans('project::view.Time')}}</th>
                    <th class="width-10-per">{{trans('project::view.Amount')}}</th>
                    <th class="width-40-per">{{trans('project::view.Description')}}</th>
                    @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                        <th class="width-9-per">&nbsp;</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($allDerivedExpense as $key => $derived)
                    <?php
                    $hasChild = false;
                    if($derived->status == DevicesExpense::STATUS_APPROVED) {
                        if (count($derived->projectDerivedExpensesChild) > 0) {
                            $hasChild = true;
                        }
                    }
                    ?>
                    <tr class="background-{{ViewProject::getColorStatusWorkOrder($derived->status)}}" data-toggle="tooltip" data-placement="top">
                        <td>{{$key + 1}}</td>
                        <td>
                            <span class="time-derived-expenses-{{$derived->id}}">{{Carbon::createFromFormat('Y-m-d', $derived->time)->format('Y-m')}}</span>
                            <input type="text" class="display-none time-datepicker form-control width-100 input-time-derived-expenses-{{$derived->id}}" name="time" value="{{Carbon::createFromFormat('Y-m-d', $derived->time)->format('Y-m')}}" placeholder="{{trans('project::view.YY-MM-DD')}}">
                        </td>
                        <td>
                            <span class="amount-derived-expenses-{{$derived->id}}">{{ number_format($derived->amount, 3, ",", ".") }}</span>
                            <input type="text" min="0" class="display-none form-control amount-active input-amount-derived-expenses-{{$derived->id}} white-space" value="{{ number_format($derived->amount, 3, ",", ".") }}" name="amount" placeholder="Giá trị" aria-invalid="false">
                        </td>
                        <td>
                            <span class="description-derived-expenses-{{$derived->id}} white-space">{!!nl2br(e($derived->description))!!}</span>
                            <textarea class="display-none form-control input-description-derived-expenses-{{$derived->id}} white-space" name="description" rows="2">{{$derived->description}}</textarea>
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                            <td>
                                @if(($derived->status == DevicesExpense::STATUS_APPROVED && !$hasChild) ||  $derived->status == DevicesExpense::STATUS_DRAFT ||  $derived->status == DevicesExpense::STATUS_FEEDBACK)
                                    <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-derived-expenses save-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}" data-status="{{$derived->status}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-derived-expenses edit-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}" data-status="{{$derived->status}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}-{{$derived->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-derived-expenses delete-confirm-new delete-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}" data-status="{{$derived->status}}"></i>
                                    <i class="display-none fa fa-ban btn-refresh btn-primary refresh-tool-and-infrastructure refresh-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}" data-status="{{$derived->status}}"></i>
                                </span>
                                @endif
                            </td>
                        @endif
                    </tr>
                    @if($derived->status == DevicesExpense::STATUS_APPROVED)
                        @if(count($derived->projectDerivedExpensesChild) > 0)
                            <?php $derived = $derived->projectDerivedExpensesChild;?>
                            <tr class="background-{{ViewProject::getColorStatusWorkOrder($derived->status)}}" data-toggle="tooltip" data-placement="top">
                                <td></td>
                                <td>
                                    <span class="time-derived-expenses-{{$derived->id}} white-space">{!!nl2br(e($tool->time))!!}</span>
                                    <input type="text" class="display-none time-datepicker form-control width-100 input-time-derived-expenses-{{$derived->id}}" name="time" value="{{$derived->time}}" placeholder="{{trans('project::view.YY-MM-DD')}}">
                                </td>
                                <td>
                                    <span class="amount-derived-expenses-{{$derived->id}}">{{ number_format($derived->amount, 3, ",", ".") }}</span>
                                    <input type="text" min="0" class="display-none form-control amount-active input-amount-derived-expenses-{{$derived->id}} white-space" value="{{ number_format($derived->amount, 3, ",", ".") }}" name="amount" placeholder="Giá trị" aria-invalid="false">
                                </td>
                                <td>
                                    <span class="description-derived-expenses-{{$derived->id}} white-space">{!!nl2br(e($derived->description))!!}</span>
                                    <textarea class="display-none form-control input-description-derived-expenses-{{$derived->id}} white-space" name="description" rows="2">{{$derived->description}}</textarea>
                                </td>
                                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                                    <td>
                                        @if($derived->status == DevicesExpense::STATUS_DRAFT ||
                                        $derived->status == DevicesExpense::STATUS_FEEDBACK ||
                                        $derived->status == DevicesExpense::STATUS_DRAFT_EDIT ||
                                        $derived->status == DevicesExpense::STATUS_FEEDBACK_EDIT ||
                                        $derived->status == DevicesExpense::STATUS_FEEDBACK_DELETE ||
                                        $derived->status == DevicesExpense::STATUS_DRAFT_DELETE)
                                            <span>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}-{{$derived->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        @if ($derived->status == DevicesExpense::STATUS_DRAFT_DELETE || $derived->status == DevicesExpense::STATUS_FEEDBACK_DELETE)
                            <i class="fa fa-trash-o btn-delete delete-derived-expenses delete-confirm-new delete-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}" data-status="{{$derived->status}}"></i>
                            @else
                                <i class="fa fa-floppy-o display-none btn-add save-derived-expenses save-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}" data-status="{{$derived->status}}"></i>
                                <i class="fa fa-pencil-square-o width-38 btn-edit edit-derived-expenses edit-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}" data-status="{{$derived->status}}"></i>
                                <i class="fa fa-trash-o btn-delete delete-derived-expenses delete-confirm-new delete-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}" data-status="{{$derived->status}}"></i>
                                <i class="display-none fa fa-ban btn-refresh btn-primary refresh-derived-expenses refresh-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}" data-status="{{$derived->status}}"></i>
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
                    <tr class="tr-add-derived-expenses">
                        <td colspan="7" class="slove-derived-expenses">
                            <span href="#" class="btn-add add-derived-expenses"><i class="fa fa-plus"></i></span>
                        </td>
                    </tr>
                    <tr class="display-none tr-derived-expenses">
                        <td></td>
                        <td>
                            <input type="text" class="form-control width-100 time-datepicker time-derived-expenses" name="time" placeholder="{{trans('project::view.YY-MM-DD')}}">
                        </td>
                        <td>
                            <span>
                                <input type="text" min="0" class="form-control width-100 amount-active amount-derived-expenses" name="amount" placeholder="Giá trị" aria-invalid="false">
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 description-derived-expenses" name="description" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <span class="btn btn-primary display-none loading-item loading-item-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}"><i class="fa fa-refresh fa-spin"></i></span>
                                <i class="fa fa-floppy-o btn-add add-new-derived-expenses"></i>
                                <i class="fa fa-trash-o btn-delete remove-derived-expenses"></i>
                            </span>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@else
    <div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}" id="table-derived-expenses">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
            <tr>
                <th class="width-5-per">{{trans('project::view.No')}}</th>
                <th class="width-10-per">{{trans('project::view.Time')}}</th>
                <th class="width-10-per">{{trans('project::view.Amount')}}</th>
                <th class="width-40-per">{{trans('project::view.Description')}}</th>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
            </thead>
            <tbody>
                @foreach($allDerivedExpense as $key => $derived)
                    <tr class="tr-derived-expenses-{{$derived->id}}">
                        <td> {{ $key + 1 }} </td>
                        <td>
                            <span class="time-derived-expenses-{{$derived->id}}">{{Carbon::createFromFormat('Y-m-d', $derived->time)->format('Y-m')}}</span>
                            <input type="text" class="display-none time-datepicker form-control width-100 input-time-derived-expenses-{{$derived->id}}" name="time" value="{{Carbon::createFromFormat('Y-m-d', $derived->time)->format('Y-m')}}" placeholder="{{trans('project::view.YY-MM-DD')}}">
                        </td>
                        <td>
                            <span class="amount-derived-expenses-{{$derived->id}}">{{ number_format($derived->amount, 3, ",", ".") }}</span>
                            <input type="text" min="0" class="display-none form-control amount-active input-amount-derived-expenses-{{$derived->id}} white-space" value="{{ number_format($derived->amount, 3, ",", ".") }}" name="amount" placeholder="Giá trị" aria-invalid="false">
                        </td>
                        <td>
                            <span class="description-derived-expenses-{{$derived->id}} white-space">{!!nl2br(e($derived->description))!!}</span>
                            <textarea class="display-none form-control input-description-derived-expenses-{{$derived->id}} white-space" name="description" rows="2">{{$derived->description}}</textarea>
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit)
                            <td>
                                <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-derived-expenses save-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-derived-expenses edit-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}-{{$derived->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-derived-expenses delete-confirm-new delete-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}"></i>
                                    <i class="display-none fa fa-ban btn-refresh btn-primary refresh-derived-expenses refresh-derived-expenses-{{$derived->id}}" data-id="{{$derived->id}}" data-status="{{$derived->status}}"></i>
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
                @if(isset($permissionEdit) && $permissionEdit)
                    <tr class="tr-add-derived-expenses">
                        <td colspan="7" class="slove-derived-expenses">
                            <span href="#" class="btn-add add-derived-expenses"><i class="fa fa-plus"></i></span>
                        </td>
                    </tr>
                    <tr class="display-none tr-derived-expenses">
                        <td></td>
                        <td>
                            <input type="text" class="form-control width-100 time-datepicker time-derived-expenses" name="time"  placeholder="{{trans('project::view.YY-MM-DD')}}">
                        </td>
                        <td>
                             <span>
                                <input type="text" min="0" class="form-control width-100 amount-derived-expenses amount-active" name="amount" placeholder="Giá trị" aria-invalid="false">
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 description-derived-expenses" name="description" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}"><i class="fa fa-refresh fa-spin"></i></span>
                                <i class="fa fa-floppy-o btn-add add-new-derived-expenses"></i>
                                <i class="fa fa-trash-o btn-delete remove-derived-expenses"></i>
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
