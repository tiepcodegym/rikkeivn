<?php
use Rikkei\Project\Model\TaskComment;
use Rikkei\Core\View\View;
use Carbon\Carbon;
use Rikkei\Project\Model\Task;

if (!$taskItem->id) {
    return;
}
?>
<div class="row">
    <div id="comments" class="col-md-8">
        <div class="box box-primary box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('project::view.Comments') }}</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    @if ((isset($showComment) && $showComment) || (isset($project) && $project->isOpen()))
                        <form id="form-task-comment" method="post" action="{{route('project::task.save.comment')}}" 
                              class="form-submit-ajax has-valid" autocomplete="off"
                              data-callback-success="commentSuccess">
                            {!! csrf_field() !!}
                            <input type="hidden" name="id" value="{{ $taskItem->id }}" />
                            <input type="hidden" name="comment_id" value="" />
                            <div class="col-md-10 form-group">
                                <textarea name="tc[content]" class="form-control text-resize-y" rows="3" id="comment"></textarea>
                                <span class="text-esc hidden" style="font-size: 11px; margin-left: 5px;">Nhấn Esc để hủy edit</span>
                            </div>
                            <div class="col-md-2 form-group">
                                <button id="button-comment-add" class="btn btn-primary" type="submit">{{ trans('project::view.Add') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </form>
                    @else
                        <div class="col-md-12">
                            <span>{{ trans('project::message.Project isnot in status New or Processing') }}</span>
                        </div>
                    @endif
                </div>
                <div class="col-xs-12 comment-list">
                    <div class="grid-data-query task-list-ajax" data-url="{{ URL::route('project::task.comment.list.ajax', ['id' => $taskItem->id]) }}">
                        <span><i class="fa fa-spin fa-refresh hidden"></i></span>
                        <div class="grid-data-query-table comment-create-task">
                            @include ('project::task.include.comment_list', ['collectionModel' => $taskCommentList])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /comments -->

    <div id="history" class="col-md-4">
        <div class="box box-primary box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('project::view.History') }}</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12 history-list">
                        <div class="grid-data-query task-list-ajax" data-url="{{ URL::route('project::task.history.list.ajax', ['id' => $taskItem->id]) }}">
                            <span><i class="fa fa-spin fa-refresh hidden"></i></span>
                            <div class="grid-data-query-table">
                                @include ('project::task.include.history_list', ['collectionModel' => $taskHistoryList])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /history -->
</div>
