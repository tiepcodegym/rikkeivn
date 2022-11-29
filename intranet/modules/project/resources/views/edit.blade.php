<?php
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Task;
use Rikkei\Team\View\Permission;
use Rikkei\Project\View\View;
use Rikkei\Core\View\CookieCore;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Core\View\CoreUrl;

$tabActive = CookieCore::get('tab-keep-status-project-workorder');
if (!$tabActive) {
  $tabActive = 'workorder';
}
if(Session::has('tabActiveWO')) {
  $tabActiveWO = Session::get('tabActiveWO');
}
if(!isset($tabActiveWO)) {
  $tabActiveWO = CookieCore::get('tab-keep-status-workorder');
  if (!$tabActiveWO) {
    $tabActiveWO = 'summary';
  }
}
$allNameTab = Task::getAllNameTabWorkorder();

$langDomain = 'project::view.'
?>
@extends('layouts.default', ['createProject' => true])

@section('title')
@if (isset($project) && $project->id)
    {{ $project->name }}
    @if (isset($pmActive) && $pmActive)
        {{ ' - PM: ' . GeneralProject::getNickName($pmActive->email) }}
    @endif
@else
    {{trans('project::view.Create project')}}
@endif
@endsection

<?php
if (isset($project) && $project && $project->id) {
    $checkEdit = true;
    $projectId = $project->id;
} else {
    $checkEdit = false;
    $projectId = false;
}
?>
@section('css')
<link href="{{ CoreUrl::asset('project/css/edit.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('project/css/flex-text.css') }}" rel="stylesheet" type="text/css" >
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link href="{{ asset('sales/css/customer_create.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis-timeline-graph2d.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<link href="{{ asset('lib/table-sorter/css/table-sorter.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('lib/css/bootstrap-dialog.min.css') }}" rel="stylesheet" type="text/css" >
<!-- Fullcalendar style -->
<link href="{{ asset('assets/fullcalendar/core/main.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/fullcalendar/daygrid/main.min.css') }}" rel="stylesheet" type="text/css" />
<!-- /.Fullcalendar style -->

<!-- Theme style -->
<!-- bootstrap-tagsinput -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.6.0/bootstrap-tagsinput.min.css">
<style>
  .error-input2 {
      border: 1px solid red;
  }
  .select2-results__group {
    text-transform: uppercase;
  }
</style>
@endsection

@section('content')
{!! $project->noticeToClose() !!}
<div class="wrapper workorder">
      <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <div class="box-body-header baseline-box box-header-split-right header-small">
                <div class="filter-panel-left panel-left-link">
                    @if ($project->id)
                        <a href="#" type="button" class="clone-project">{{ trans('project::view.Clone Project') }}</a>
                        <a href="{{ URL::route('project::plan.comment', ['projectId' => $project->id]) }}" target="_blank" class="btn-add-task">{{ trans('project::view.Project Plan') }}</a>
                        <a href="{{ URL::route('help::display.help.view', ['id' => 124]) }}" target="_blank">{{ trans('project::view.Help') }}</a>
                        <a href="{{ URL::route('project::point.edit', ['id' => $project->id]) }}" target="_blank">{{ trans('project::view.Project Report') }}</a>
                        @if($taskWOApproved)
                            <a href="{{ route('project::task.edit', ['id' => $taskWOApproved->id ]) }}" target="_blank">{{ trans('project::view.Workorder review') }}</a>
                        @endif
                        @if (Project::rewardAvai($project))
                            <a href="{{ route('project::reward', ['id' => $project->id ]) }}">{{ trans('project::view.Project Reward') }}</a>
                        @endif
                    @endif
                </div>
            </div>
        <!-- Main content -->
        <section class="content">
          @if ($checkEdit)
            <div class="nav-tabs-custom tab-keep-status" data-type="project-workorder" id="project-over">
              <ul class="nav nav-tabs" id="menu-tab" role="tablist">
                <li role="presentation" <?php if($tabActive == 'workorder'): ?> class="active"<?php endif; ?>>
                  <a href="#workorder" aria-controls="workorder" role="tab" data-toggle="tab">{{trans('project::view.Work Order')}}</a>
                </li>
                <li role="presentation" <?php if($tabActive == 'activity'): ?> class="active"<?php endif; ?>>
                  <a href="#activity" aria-controls="active" role="tab" data-toggle="tab">{{trans('project::view.Activity Log')}}</a>
                </li>

                <li role="presentation" <?php if($tabActive == 'calendar-report'): ?> class="active"<?php endif; ?>>
                  <a href="#calendar-report" aria-controls="active" role="tab" data-toggle="tab">{{trans('project::view.calendar_report')}}</a>
                </li>
              </ul>
              <!-- Tab panes -->
              <div class="prj-tab-content tab-content">
                <div role="tabpanel" class="tab-pane<?php if($tabActive == 'workorder'): ?> active<?php endif; ?>" id="workorder">
                  @include('project::tab_content.workorder')
                </div>
                <div role="tabpanel" class="tab-pane<?php if($tabActive == 'activity'): ?> active<?php endif; ?>" id="activity">
                  @include('project::tab_content.activity')
                </div>
                <div role="tabpanel" class="tab-pane <?php if($tabActive == 'calendar-report'): ?> active<?php endif; ?>" id="calendar-report">
                  @include('project::tab_content.calendar-report')
                </div>
              </div>
            </div>
          @else
            @include('project::tab_content.create')
          @endif
        </section>
        <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->
    </div>
    <!-- ./wrapper -->
<!-- modal delete cofirm -->
<div class="modal fade modal-info" id="modal-wanrning-create-stage" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default"></p>
                <p class="text-change"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok">{{ Lang::get('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->

<!-- modal warn cofirm -->
<div class="modal fade @yield('warn_confirn_class', 'modal-warning')" id="modal-update-time-confirm" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('project::view.Confirm') }}</h4>
                <h4 class="modal-title-change"></h4>
            </div>
            <div class="modal-body">
                <p class="text-default"></p>
                <p class="text-change"></p>
                <ul class="ul-wraning"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('project::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok post-ajax"
                    data-url-ajax="{{ route('project::project.updateTime', ['projectId' => $project->id]) }}">{{ Lang::get('project::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->
@endsection

@section('script')
@include('project::includes.modal-member-edit')
@if($project->id)
@include('project::includes.modal-clone-project')
@endif
@include('project::tab_content.includes.modal.billable')
<script type="text/javascript">
  var maxEffort = {{ $maxEffort }};
  var urlGetCurrentSkill = "{!! route('project::project.get-current-skill') !!}"
</script>
@include('project::includes.project-edit-script')
@include('project::tab_content.includes.js.js-billable-cost')
<script rc="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
@endsection
