<?php
use Rikkei\Project\Model\Task;

$allNameTab = Task::getAllNameTabWorkorder();
?>
<hr>
<h4 class="box-title padding-left-15">{{trans('project::view.Change of workorder')}} 
@if(isset($detail)) 
    <span class="slove-change-work-order" id="slove-{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}">
        <span class="btn btn-primary show-content-table show-content-table-{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}} display-none" data-type="{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}"><i class="fa fa-chevron-down"></i></span>
        <span class="btn btn-primary hide-content-table hide-content-table-{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}" data-type="{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}"><i class="fa fa-chevron-up"></i></span>
        </span>
    </span>
@else
    <span class="slove-change-work-order" id="slove-{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}">
        <span class="btn btn-primary show-content-table show-content-table-{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}" data-type="{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}"><i class="fa fa-chevron-down"></i></span>
        <span class="btn btn-primary hide-content-table display-none hide-content-table-{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}" data-type="{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}"><i class="fa fa-chevron-up"></i></span>
    </span>
@endif
</h4>
@if(isset($detail))
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}" id="table-change-workorder">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th>{{trans('project::view.Item')}}</th>
                <th>{{trans('project::view.Changes')}}</th>
                <th>{{trans('project::view.Reason')}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allChangeWorkorder as $key => $change)
            <tr>
                <td>v{{$change->version}}</td>
                <td>
                    <span class="change-change-workorder-{{$change->id}}"><a href="{{route('project::task.edit', ['id' => $change->change])}}" target="_blank">{{trans('project::view.Detail')}}</a></span>                
                </td>
                <td>
                @if($isCoo)
                <textarea class="form-control input-reason-change-workorder input-reason-change-workorder-{{$change->id}}" name="reason" rows="2" data-id="{{$change->id}}">{{$change->reason}}</textarea>
                @else
                    <span class="reason-change-workorder-{{$change->id}}">{{$change->reason}}</span>               
                @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif