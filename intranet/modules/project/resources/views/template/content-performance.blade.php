<?php
     use Rikkei\Core\View\View as ViewHelper;
?>
@if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
<span class="content-endat-approved">{{ViewHelper::getDate($project->end_at)}}</span>
<span class="value new label label-warning {{!$performance ? 'display-none' : ''}} edit-draft-end-date">{{$performance ? ViewHelper::getDate($performance->end_at) : ''}}</span>
@if($performance)
<input type="text" class="form-control width-100 input-endat display-none" name="end_at" value="{{ViewHelper::getDate($performance->end_at)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}" data-id="{{$performance->id}}">
@else
<input type="text" class=" display-none form-control width-100 display-none input-endat input-endat-approved" name="end_at" value="{{ViewHelper::getDate($project->end_at)}}" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
@endif
<span class="btn-add btn-save-end-at display-none"><i class="fa fa-floppy-o margin-right-0"></i></span>
<span class="btn-primary btn-edit btn-edit-end-at width-38"><i class="fa fa-pencil-square-o margin-right-0"></i></span>
<span class="btn btn-default btn-cancel btn-cancel-end-at display-none"><i class="fa fa-ban"></i></span>
@if($performance)
<span class="btn-delete delete-performance delete-confirm-new delete-performance-{{$performance->id}}" data-id="{{$performance->id}}""><i class="fa fa-trash-o"></i></span>
@endif
@else
<span>{{ViewHelper::getDate($project->end_at)}}</span>
@endif