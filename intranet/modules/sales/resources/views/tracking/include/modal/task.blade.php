<?php
use Carbon\Carbon;

$multi = ' multiple="multiple"';
if ($accessEditTask) {
    $disabledAssign = '';
    $disabledParticipant = '';
} else {
    $disabledAssign = ' disabled';
    $disabledParticipant = ' disabled';
}
?>
<div class="modal fade" id="taskModal" style="display: none;">
   <div class="modal-dialog modal-lg" style="width: 90%;">
      <div class="modal-content">
         <form id="form-task-edit" method="post" action="{{route('sales::tracking.saveTasks')}}" class="form-horizontal form-submit-ajax has-valid" autocomplete="off">
            {!! csrf_field() !!}
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">×</span></button>
               <h4 class="modal-title">{{ trans('project::view.Task general create') }}</h4>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-sm-12">
                     <div class="box box-info">
                        <div class="box-body">
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="row">
                                    <div class="col-md-12">
                                       @include('project::task.edit_fields.project')
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-md-12">
                                       @include('project::task.edit_fields.title')
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-md-12">
                                       @include('project::task.edit_fields.status')
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-md-12">
                                       @include('project::task.edit_fields.due_date')
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-md-12">
                                       @include('project::task.edit_fields.actual_date')
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-md-12">
                                       @include('project::task.edit_fields.content')
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="row">
                                    <div class="col-md-12">
                                       <?php $createdAt = Carbon::parse($taskItem->created_at)->format('Y-m-d'); ?>
                                       <div class="form-group">
                                          <label class="col-sm-3 control-label required">{{ trans('project::view.Create date') }}</label>
                                          <div class="col-md-9">
                                             <p class="form-control-static">{{ $createdAt }}</p>
                                          </div>
                                       </div>
                                       <input type="hidden" name="task[created_at]" value="{{ $createdAt }}" />
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-md-12">
                                       @include('project::task.edit_fields.assignee')
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-md-12">
                                       @include('project::task.edit_fields.priority')
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-md-12">
                                       @include('project::task.edit_fields.participant')
                                    </div>
                                 </div>
                                 <div class="row relate-task-type hidden">
                                    <div class="col-md-12">
                                       @include('project::task.edit_fields.type')
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row">
                    <div class="align-center">
                        <button type="submit" class="btn-add">
                    {{trans('project::view.Save')}}
                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                        <button type="button" class="btn btn-close margin-left-10" data-dismiss="modal">{{trans('project::view.Close')}}</button>
                    </div>
                </div>
            </div>
         </form>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
