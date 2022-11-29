<?php
use Rikkei\Core\View\View;
use Carbon\Carbon;
use Rikkei\Project\Model\Task;
$today = Carbon::parse(Carbon::today());
?>
<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <table class="table  table-bordered table-grid-data">
                <thead>
                    <tr>
                        <th class="col-id" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                        <th class="">{{ trans('project::view.Title') }}</th>
                        <th class="">{{ trans('project::view.Status') }}</th>
                        <th class="">{{ trans('project::view.Priority') }}</th>
                        <th class="">{{ trans('project::view.Type') }}</th>
                        <th class="">{{ trans('project::view.Assignee') }}</th>
                        <th class="">{{ trans('project::view.Create date') }}</th>
                        <th class="">{{ trans('project::view.Deadline') }}</th>
                        <th class="">{{ trans('project::view.Issues') }}</th>
                        <th class=""></th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($collectionModel) && count($collectionModel))
                        <?php $i = View::getNoStartGrid($collectionModel);?>
                        @foreach($collectionModel as $item)
                            <?php
                            /*$point = null;
                            if ($item->type == Task::TYPE_COMMENDED) {
                                $point = 1;
                            } elseif ($item->type == Task::TYPE_CRITICIZED) {
                                $point = -1;
                            } else {
                                $point = 0;
                            }*/
                            ?>
                            <tr>
                                <td>{{ $i }}</td>
                                <td>
                                    <a class="post-ajax" href="#" data-url-ajax="{{ route('project::task.edit.ajax', ['id' => $item->id ]) }}"
                                       data-callback-success="loadModalFormSuccess">
                                        @if($item->status != Task::STATUS_CLOSED && 
                                                    $item->duedate !== null      &&
                                                    $item->duedate->lt($today)
                                        )
                                            <span style="color:red">
                                            {{ $item->title }}
                                            </span>
                                        @else
                                            {{ $item->title }}
                                        @endif       
                                    </a>
                                </td>
                                <td>{{ $item->status ? Task::statusLabel()[$item->status] : ''}}</td>
                                <td>{{ $item->getPriority() }}</td>
                                <td>{{ $item->getType() }}</td>
                                <td>{{ $item->email }}</td>
                                <td>
                                    @if ($item->created_at)
                                        {{ Carbon::parse($item->created_at)->format('Y-m-d') }}
                                    @endif
                                </td>
                                <td>
                                    {{ Carbon::parse($item->duedate)->format('Y-m-d') }}
                                </td>
                                <td>
                                    @if (empty($item->count_issues))
                                    {{ $item->count_issues }}
                                    @else
                                    <a data-direction="open" data-id="{{ $item->id }}" href="javascript:void(0);" onclick="displayIssue({{ $item->id }}, this);">{{ $item->count_issues }} <span class="glyphicon glyphicon-menu-down"></span></a>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn-add btn-add-task post-ajax" data-url-ajax="{{ URL::route('project::task.add.ajax', ['id' => $project->id, 'type' => Task::TYPE_ISSUE_CSS, 'parent_id' => $item->id, 'template' => 1]) }}"
                                        type="button" data-callback-success="loadModalFormSuccess">
                                        <i class="fa fa-plus"></i>
                                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center">
                                <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="box-body">
            @include('team::include.pager', ['domainTrans' => 'project'])
        </div>
    </div>
</div>