<?php
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Task;
use Carbon\Carbon;

$today = Carbon::parse(Carbon::today());
$taskTypes = [
    Task::TYPE_ISSUE_COST => 'Task Cost',
    Task::TYPE_ISSUE_QUA => 'Task Quality',
    Task::TYPE_ISSUE_TL => 'Task Timeless',
    Task::TYPE_ISSUE_PROC => 'Task Process',
    Task::TYPE_ISSUE_CSS => 'Task Css',
    Task::TYPE_RISK => 'Task Risk',
];
?>

<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th class="col-id" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                        <th class="" style="max-width: 300px">{{ trans('project::view.Title') }}</th>
                        <th class="">{{ trans('project::view.Assignee') }}</th>
                        <th class="">{{ trans('project::view.Deadline') }}</th>
                        <th class="col-md-2">{{ trans('project::view.Status') }}</th>
                        <th class="col-md-2" style="width: 133px">{{ trans('project::view.Type') }}</th>
                    </tr>
                    <tr class="row-filter">
                        <th></th>
                        <th>
                            <input type="text" class="form-control hidden" />
                        </th>
                        <th>
                            <input type="text" class="form-control hidden" />
                        </th>
                        <th>
                            <input type="text" class="form-control hidden" />
                        </th>
                        <th>
                            <input type="text" class="form-control hidden" />
                        </th>
                        <th>
                            <select class="form-control filter-type" onchange="typeChanged(this)">
                                <option value="">&nbsp;</option>
                                @foreach ($taskTypes as $key => $label)
                                <option value="{{ $key }}" {{ isset($type) && $type == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($collectionModel) && count($collectionModel))
                        <?php $i = View::getNoStartGrid($collectionModel); ?>
                        @foreach($collectionModel as $item)
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
                                <td>{{ $item->email }}</td>
                                <td>
                                    @if ($item->duedate)
                                        {{ Carbon::parse($item->duedate)->format('Y-m-d') }}
                                    @endif
                                </td>
                                @if (Task::hasEditStatusTasks($item, $project))
                                    <td class="status-index" data-id="{{$item->id}}" data-status="{{ $item->status }}" data-select="0">{{ $item->getStatus() }}</td>
                                @else
                                    <td>{{ $item->getStatus() }}</td>
                                @endif
                                <td>{{ $item->getType() }}</td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">
                                <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="box-body">
            @include('team::include.pager', ['domainTrans' => 'project', 'isShow' => true])
        </div>
    </div>
</div>
<script type="text/javascript">
    var varGlobalPassModule = {
        priorities: JSON.parse('{!! json_encode($taskPriorities) !!}'), 
        status: JSON.parse('{!! json_encode($taskStatus) !!}'), 
        routePriority: '{{ route('project::task.general.save.priority') }}',
        routeStatus: '{{ route('project::task.general.save.status') }}'
    };
</script>
