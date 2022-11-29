
<?php
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\View\GeneralProject;
?>

<?php
$emailsReviewer = [];
$approver = $feedbacker = null;
?>
<div class="row">
    <!-- box confirm -->
    <div class="col-md-9 col-md-offset-1">
        <div class="box box-primary">
            <div class="box-header with-border box-header-split-right header-small">
                <h3 class="box-title">{{ trans('project::view.Project') }}: {{ $project->name }}</h3>
                <div class="panel-split panel-left-link">
                    <a href="{{ URL::route('project::point.edit', ['id' => $project->id]) }}" target="_blank">{{ trans('project::view.Project report') }}</a>
                    <a href="{{ URL::route('project::project.edit', ['id' => $project->id]) }}" target="_blank">{{ trans('project::view.View workorder') }}</a>
                    <a href="{{ URL::route('project::task.edit', ['id' => $taskItem->id]) }}" target="_blank">{{ trans('project::view.View detail') }}</a>
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
                        <h3>{{ trans('project::view.Basic Info') }}</h3>
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="numeric">Old value</th>
                                    <th class="numeric">New value</th>
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
                <!-- end box confirm -->
                @foreach ($taskAssigns as $item)
                    <?php 
                    if ($item->status == TaskAssign::STATUS_FEEDBACK && !$feedbacker) {
                        $feedbacker = $item;
                    }
                    ?>
                    @if ($item->role == TaskAssign::ROLE_REVIEWER)
                        <?php $emailsReviewer[$item->id] = $item->status; ?>
                    @elseif ($item->role == TaskAssign::ROLE_APPROVER)
                        <?php $approver = $item; ?>
                    @endif
                @endforeach
                <!-- end basic info change and change detail -->
                <!-- btn submit, accept, approve, feedback -->
                <?php $feedbackReviewAvai = $feedbackApproveAvai = false; ?>
                <div class="actions text-center margin-top-10 wo-action-btns">
                    @if ($taskItem->status == Task::STATUS_SUBMITTED)
                        {{-- wait review --}}
                        @if (isset($emailsReviewer[$userCurrent->id]) && $emailsReviewer[$userCurrent->id] == TaskAssign::STATUS_NO)
                            <div class="btn-submit-reviewer">
                                <button class="btn btn-danger margin-right-30" type="button" 
                                    data-toggle="modal" data-target="#feedback-modal">{{ trans('project::view.Feedback') }}</button>

                                <button class="btn btn-info post-ajax" data-url-ajax="{{ route('project::task.wo.review.submit', ['id' => $taskItem->id, 'myTask' => true]) }}" 
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

                            <button class="btn-edit post-ajax" data-url-ajax="{{ route('project::task.wo.approve.submit', ['id' => $taskItem->id, 'myTask' => true]) }}" 
                                type="button">{{ trans('project::view.Approve') }} 
                                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                            <?php $feedbackApproveAvai = true; ?>
                        @endif
                    {{--
                    @elseif ($taskItem->status == Task::STATUS_FEEDBACK && GeneralProject::isAccessFeedback($feedbacker, $taskItem->id))
                        <button class="btn btn-danger post-ajax margin-right-30" data-url-ajax="{{ route('project::task.wo.undo.feedback', ['id' => $taskItem->id, 'myTask' => true]) }}" 
                            type="button">{{ trans('project::view.Undo feedback') }} 
                            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                    --}}
                    @endif
                </div>
                <!-- end form accept, feedback -->
            </div>
        </div>
    </div>
    
@if ($feedbackReviewAvai)
<div id="feedback-modal" class="modal in">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-wo-review-feedback" method="post" action="{{ route('project::task.wo.review.feedback', ['id' => $taskItem->id, 'myTask' => true]) }}" 
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
            <form id="form-wo-approve-feedback" method="post" action="{{ route('project::task.wo.approve.feedback', ['id' => $taskItem->id, 'myTask' => true]) }}" 
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


