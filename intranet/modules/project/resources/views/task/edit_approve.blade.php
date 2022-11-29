@extends('layouts.default')

@section('title')
<?php
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\View\GeneralProject;
?>
{{ trans('project::view.Workorder review') }}
@if (isset($pmActive) && $pmActive)
{{ ' - PM: ' . GeneralProject::getNickName($pmActive->email) }}
@endif
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
@endsection

@section('content')
<?php
$emailsReviewer = [];
$approver = $feedbacker = null;
?>
<div class="row">
    <!-- box confirm -->
    <div class="col-sm-8">
        <div class="box box-primary">
            <div class="box-header with-border box-header-split-right header-small">
                <h3 class="box-title">{{ trans('project::view.Project') }}: {{ $project->name }}</h3>
                <div class="panel-split panel-left-link">
                    <a href="{{ URL::route('project::point.edit', ['id' => $project->id]) }}" target="_blank">{{ trans('project::view.Project report') }}</a>
                    <a href="{{ URL::route('project::project.edit', ['id' => $project->id]) }}" target="_blank">{{ trans('project::view.View workorder') }}</a>
                </div>
            </div>
            <div class="box-body">
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{{ trans('project::view.Title') }}</label>
                        <div class="col-md-10">
                            <p class="form-control-static">{{ $project->name . ': '. $taskItem->title }}</p>
                          </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">{{ trans('project::view.Status') }}</label>
                        <div class="col-md-10">
                            <?php
                            $classStatus = '';
                            switch ($taskItem->status) {
                                case Task::STATUS_SUBMITTED:
                                    $classStatus = 'callout-warning';
                                    break;
                                case Task::STATUS_REVIEWED:
                                    $classStatus = 'callout-info';
                                    break;
                                case Task::STATUS_APPROVED:
                                    $classStatus = 'callout-success';
                                    break;
                                case Task::STATUS_FEEDBACK:
                                    $classStatus = 'callout-danger';
                                    break;
                                default:
                                    $classStatus = 'callout-success';
                            }
                            ?>
                            <span class="callout status-wo {{ $classStatus }}">{{ $taskItem->getStatus() }}</span>
                        </div>
                    </div>
                </div>
                <!-- basic info change and change detail -->
                <div class="row">
                    <div class="col-md-5">
                        <h3>{{ trans('project::view.Basic info') }}</h3>
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th>{{ trans('project::view.Item') }}</th>
                                    <th class="numeric">{{ trans('project::view.Old value') }}</th>
                                    <th class="numeric">{{ trans('project::view.New value') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {!! $taskWoChangesContent['htmlBasicInfo'] !!}
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-7">
                        <h3>{{ trans('project::view.Changes') }}</h3>
                        {!! $taskWoChangesContent['htmlChanges'] !!}
                      <!-- /#changes -->
                    </div>
                </div>
                <!-- end basic info change and change detail -->
                <!-- form accept, feedback -->
                <div class="actions text-center margin-top-10 wo-action-btns"></div>
                <!-- end form accept, feedback -->
            </div>
        </div>
    </div>
    <!-- end box confirm -->
    
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-body">
                <div class="wo-assignees wo-assignee-box">
                    <div class="form-group wa-item">
                        <label class="control-label">{{ trans('project::view.PM') }}</label>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>
                                        @if ($pmActive)
                                            <p class="form-control-static">{{ $pmActive->name . ' (' . GeneralProject::getNickName($pmActive->email) . ')' }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="wa-item">
                        <label class="control-label">{{ trans('project::view.Reviewers') }}</label>
                        <table class="table fa-not-margin">
                            <tbody>
                                @foreach ($taskAssigns as $item)
                                    <?php 
                                    if ($item->status == TaskAssign::STATUS_FEEDBACK && !$feedbacker) {
                                        $feedbacker = $item;
                                    }
                                    ?>
                                    @if ($item->role == TaskAssign::ROLE_REVIEWER)
                                        <tr class="assign-item">
                                            <td>{{ $item->name . ' (' . GeneralProject::getNickName($item->email) . ')' }}</td>
                                            <td>
                                                <?php $cssClass = GeneralProject::getClassCssStatusReviewer($item->status); ?>
                                                <span class="{{ $cssClass }}">{{ $cssClass ? View::getLabelOfOptions($item->status, $allStatusAssign) : '' }}</span>
                                            </td>
                                            <td>
                                                @if ($accessChangeReviewer && $item->status == TaskAssign::STATUS_NO)
                                                    <form action="{{ URL::route('project::task.wo.reviewer.delete', ['id' => $taskItem->id]) }}" 
                                                        method="post" class="form-submit-ajax" autocomplete="off" data-callback-success="woDeleteReviewerSuccess">
                                                        {!! csrf_field() !!}
                                                        <input type="hidden" name="assign[id]" value="{{ $item->employee_id }}" />
                                                        <button type="submit" class="btn btn-danger btn-remove delete-confirm btn-xs{{ $reviewerOnly ? ' hidden' : '' }}">
                                                            <i class="fa fa-minus btn-submit-main"></i>
                                                            <i class="fa fa-spin fa-refresh btn-submit-refresh hidden"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        <?php $emailsReviewer[$item->id] = $item->status; ?>
                                    @elseif ($item->role == TaskAssign::ROLE_APPROVER)
                                        <?php $approver = $item; ?>
                                    @endif
                                @endforeach
                                <!-- add reviewer -->
                                @if ($accessChangeReviewer)
                                    <tr>
                                        <td>
                                            <div class="assign-item">
                                                <form action="{{ URL::route('project::task.wo.reviewer.add', ['id' => $taskItem->id]) }}" 
                                                      id="form-wo-change-reviewer" method="post" class="form-submit-ajax has-valid" autocomplete="off" data-callback-success="woAddReviewerSuccess">
                                                    {!! csrf_field() !!}
                                                    <div class="assign-name form-group-select2">
                                                        <div class="assign-name-select fg-valid-custom">
                                                            <select class="select-search-remote-reviewer" name="assign[id]" 
                                                                data-remote-url="{{ URL::route('team::employee.list.search.ajax', ['type' => 'reviewer', 'task' => $taskItem->id]) }}"></select>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                        <td></td>
                                        <td>
                                            <div class="assign-name-save">
                                                <button type="button" class="btn btn-success btn-xs btn-submit-href" data-href="#form-wo-change-reviewer">
                                                    <i class="fa fa-plus btn-submit-main"></i>
                                                    <i class="fa fa-spin fa-refresh btn-submit-refresh hidden"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                <!-- end add reviewer -->
                            </tbody>
                        </table>
                    </div>
                    <!-- end box reviewers -->
                    <!-- box approver -->
                    <div class="wa-item">
                        <label class="control-label">{{ trans('project::view.Approver') }}</label>
                        <table class="table fa-not-margin">
                            <tr class="assign-item">
                                <td> 
                                    <form action="{{ URL::route('project::task.wo.approver.change', ['id' => $taskItem->id]) }}" 
                                        method="post" class="form-submit-ajax" autocomplete="off" data-callback-success="woChangeApproverSuccess">
                                        {!! csrf_field() !!}
                                        <div class="assign-name form-group-select2">
                                            @if ($approver)
                                                <span class="assign-name-old">{{ $approver->name . ' (' . GeneralProject::getNickName($approver->email) . ')' }}</span>
                                            @endif
                                            @if ($accessChangeApprover)
                                                <div class="assign-name-select hidden">
                                                    <select class="select-search-remote" name="assign[id]" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                                        @if ($approver)
                                                            <option value="{{ $approver->id }}">{{ GeneralProject::getNickName($approver->email) }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                          
                                                <div class="assign-name-save hidden">
                                                    <button type="submit" class="btn btn-success btn-save btn-xs">
                                                        <i class="fa fa-floppy-o btn-submit-main"></i>
                                                        <i class="fa fa-spin fa-refresh btn-submit-refresh hidden"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    @if ($approver)
                                        <?php $cssClass = GeneralProject::getClassCssStatusReviewer($approver->status); ?>
                                        <span class="{{ $cssClass }}">{{ $cssClass ? View::getLabelOfOptions($approver->status, $allStatusAssign) : '' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($accessChangeApprover)
                                        <button type="button" class="btn btn-primary btn-change-approver btn-change btn-xs">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-remove-approver btn-remove hidden btn-disable-submitting btn-xs">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- end box approver -->
                </div>
            </div>
        </div>
    </div>
</div>
@include ('project::task.include.comment', ['collectionModel' => $taskCommentList])
<!-- btn submit, accept, approve, feedback -->
<?php $feedbackReviewAvai = $feedbackApproveAvai = false; ?>
<div class="hidden wo-action-btns-href">
    @if ($taskItem->status == Task::STATUS_SUBMITTED)
        {{-- wait review --}}
        @if (isset($emailsReviewer[$userCurrent->id]) && $emailsReviewer[$userCurrent->id] == TaskAssign::STATUS_NO)
            <div class="btn-submit-reviewer">
                <button class="btn btn-danger margin-right-30" type="button" 
                    data-toggle="modal" data-target="#feedback-modal">{{ trans('project::view.Feedback') }}</button>

                <button class="btn btn-info post-ajax" data-url-ajax="{{ route('project::task.wo.review.submit', ['id' => $taskItem->id]) }}" 
                    type="button">{{ trans('project::view.Confirm') }} 
                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
            </div>
            <?php $feedbackReviewAvai = true; ?>
        @endif
    @elseif ($taskItem->status == Task::STATUS_REVIEWED)
        {{-- wait approve --}}
        @if (($approver && $userCurrent->id == $approver->id)  || $accessApprove)
            <button class="btn btn-danger margin-right-30" type="button" 
                data-toggle="modal" data-target="#feedback-modal">{{ trans('project::view.Feedback') }}</button>

            <button class="btn-edit post-ajax" data-url-ajax="{{ route('project::task.wo.approve.submit', ['id' => $taskItem->id]) }}" 
                type="button">{{ trans('project::view.Approve') }} 
                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
            <?php $feedbackApproveAvai = true; ?>
        @endif
    {{--
    @elseif ($taskItem->status == Task::STATUS_FEEDBACK && GeneralProject::isAccessFeedback($feedbacker, $taskItem->id))
        <button class="btn btn-danger post-ajax margin-right-30" data-url-ajax="{{ route('project::task.wo.undo.feedback', ['id' => $taskItem->id]) }}" 
            type="button">{{ trans('project::view.Undo feedback') }} 
            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
    --}}
    @endif
</div>
@if ($feedbackReviewAvai)
<div id="feedback-modal" class="modal in">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-wo-review-feedback" method="post" action="{{ route('project::task.wo.review.feedback', ['id' => $taskItem->id]) }}" 
                  class="form-horizontal form-submit-ajax has-valid" autocomplete="off">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('project::view.Feedback Content') }}</h4>
                </div>
                <div class="modal-body">
                    <textarea rows="5" name="fb[comment]" class="form-control"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('project::view.Close') }}</button>
                    <button type="submit" class="btn btn-danger">{{ trans('project::view.Feedback') }}
                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endif
@if ($feedbackApproveAvai)
<div id="feedback-modal" class="modal in">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-wo-approve-feedback" method="post" action="{{ route('project::task.wo.approve.feedback', ['id' => $taskItem->id]) }}" 
                class="form-horizontal form-submit-ajax has-valid" autocomplete="off">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('project::view.Feedback Content') }}</h4>
                </div>
                <div class="modal-body">
                    <textarea rows="5" name="fb[comment]" class="form-control"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('project::view.Close') }}</button>
                    <button type="submit" class="btn btn-danger">{{ trans('project::view.Feedback') }}
                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endif
<!-- end btns submit -->
<!-- modal changes -->
{!! $taskWoChangesContent['htmlModal'] !!}
<!-- end modal changes -->
@endsection

@section('script')
<script>
    @if (isset($project) && $project->id)
    var globalPassModule = {
        teamProject: '{{ isset($teamsProject) && $teamsProject ? $teamsProject : '' }}'
    };
    var requiredCmt = '{{ trans('resource::message.Kindly add comments') }}';
    @endif
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
@endsection

