<?php
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Task;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\View\CoreUrl;
$tableEmployee = Employee::getTableName();
$tableProject = Project::getTableName();
$tableTask = Task::getTableName();
$priorityFilter = Form::getFilterData("{$tableTask}.priority", null);
$statusFilter = Form::getFilterData('exception', "{$tableTask}.status");
?>
<div class="box-body">
    <div class="filter-input-grid">
        <div class="col-sm-12">
            <div class="form-group row col-sm-4">
                <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Owner') }}</label>
                <div class="col-sm-9">
                    <input type="text" name="filter[{{ $tableEmployee }}.email]" value="{{ Form::getFilterData("{$tableEmployee}.email", null) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                </div>
            </div>
            <div class="form-group row col-sm-4">
                <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Status') }}</label>
                <div class="col-sm-9 filter-multi-select">
                    <select class="form-control select-grid filter-grid select-search" name="filter[exception][{{$tableTask}}.status]">
                        <option value="">&nbsp;</option>
                        @foreach(Task::statusNewLabel() as $key => $value)
                            <option value="{{ $key }}"<?php
                            if ($key == $statusFilter): ?> selected<?php endif;
                                ?>>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row col-sm-4">
                <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Priority') }}</label>
                <div class="col-sm-9 filter-multi-select">
                    <select class="form-control select-grid filter-grid select-search" name="filter[{{$tableTask}}.priority]">
                        <option value="">&nbsp;</option>
                        @foreach($priority as $key => $value)
                            <option value="{{ $key }}"<?php
                            if ($key == $priorityFilter): ?> selected<?php endif;
                                ?>>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>

        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </div>
    <div class="box-body filter-action">
        <?php
        if (!isset($domainTrans) || !$domainTrans) {
            $domainTrans = 'team';
        }
        ?>
        <button class="btn btn-primary btn-reset-filter">
            <span>{{ trans($domainTrans . '::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
        </button>
        <button class="btn btn-primary btn-search-filter">
            <span>{{ trans($domainTrans . '::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
        </button>
    </div>
    <p>{{ trans('project::view.Note security') }} <a target="_blank" rel="noopener noreferrer" href="{{ route('project::report.common-issue')}}">{{ trans('project::view.Here') }}</a> {{ trans('project::view.view sample report') }}</p>
    <div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_ISSUE]}}" id="table-issue">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
            <tr>
                <th class="width-5-per align-center">{{trans('project::view.No')}}</th>
                <th >{{trans('project::view.Issues Type')}}</th>
                <th >{{trans('project::view.Title')}}</th>
                <th >{{trans('project::view.Status')}}</th>
                <th >{{trans('project::view.Priority')}}</th>
                <th >{{trans('project::view.Owner')}}</th>
                <th >{{trans('project::view.Due Date')}}</th>
                <th >{{trans('project::view.Create date')}}</th>
                <th >{{trans('project::view.Update date')}}</th>
            </tr>
            </thead>
            <tbody>
            @if (count($taskManager))
                @foreach($taskManager as $key => $issue)
                    <tr role="row" data-id="{{$issue->id}}">
                        <td>{{ $key+1 }}</td>
                        <td>{{ empty($issue->type) ? '' : Task::getTypeIssues()[$issue->type] }}</td>
                        <td>
                            @if ($issue->title)
                            <a href="{{ route('project::issue.detail', ['id' => $issue->id ]) }}">{!!nl2br(e($issue->title))!!}</a>
                            @endif
                        </td>
                        <td>
                            {{ Task::getStatusOfIssue($issue->status, $issue->status_backup) }}
                        </td>
                        <td>
                            {{ Task::priorityLabel()[$issue->priority] }}
                        </td>
                        <td>
                            @if ($issue->email)
                                {{ $issue->email }}
                            @endif
                        </td>
                        <td>
                            {{ Task::getDuedateOfIssue($issue->task_duedate, $issue->task_duedate_backup) }}
                        </td>
                        <td>{{$issue->created_at}}</td>
                        <td>{{$issue->updated_at}}</td>
                        <td>
                            <i class="fa fa-trash-o btn-delete delete-issue delete-confirm-new delete-issue-{{$issue->id}}" data-id="{{$issue->id}}" data-status="{{$issue->status}}"></i>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="13" class="text-center">
                        <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
        <tr class="tr-add-issue">
            <td colspan="9" class="slove-issue">
                <button href="#" class="btn-add add-issue" data-project-id="{{$project->id}}"><i class="fa fa-plus"></i></button>
            </td>
        </tr>
    </div>
</div>
<div class="box-body">
    @include('team::include.pager', ['domainTrans' => 'project'])
</div>
<div class="modal fade modal-danger" id="modal-delete-confirm-issue" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ Lang::get('core::view.Are you sure delete item(s)?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-submit">{{ Lang::get('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
