<?php
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Task;
use Carbon\Carbon;
$today = Carbon::parse(Carbon::today());
?>

<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th>{{ trans('project::view.No.') }}</th>
                        <th >{{trans('project::view.ID risk')}}</th>
                        <th >{{ trans('project::view.Type') }}</th>
                        <th style="width: 80px;">{{ trans('project::view.Title') }}</th>
                        <th >{{ trans('project::view.Status') }}</th>
                        <th >{{ trans('project::view.Priority') }}</th>
                        <th class="">{{ trans('project::view.Owner') }}</th>
                        <th class="">{{ trans('project::view.Due Date') }}</th>
                        <th class="">{{ trans('project::view.Create date') }}</th>
                        <th class="">{{ trans('project::view.Update date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($collectionModel) && count($collectionModel))
                        <?php $i = View::getNoStartGrid($collectionModel);
                        ?>
                        @foreach($collectionModel as $item)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->getType() }}</td>
                                <td>
                                    @if ($item->title)
                                        <a href="{{ route('project::issue.detail', ['id' => $item->id ]) }}">{!!nl2br(e($item->title))!!}</a>
                                    @endif
                                </td>
                                <td>
                                    {{ Task::getStatusOfIssue($item->status, $item->status_backup) }}
                                </td>
                                <td class="priority-index" data-id="{{$item->id}}" data-priority="{{ $item->priority }}" data-select="0">{{ $item->getPriority() }}</td>
                                <td>{{ $item->email }}</td>
                                <td>
                                    @if ($item->task_duedate)
                                        {{ Task::getDuedateOfIssue($item->task_duedate, $item->task_duedate_backup) }}
                                    @endif
                                </td>
                                <td>
                                    @if ($item->created_at)
                                        {{ Carbon::parse($item->created_at)->format('Y-m-d') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($item->updated_at)
                                        {{ Carbon::parse($item->updated_at)->format('Y-m-d') }}
                                    @endif
                                </td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="10" class="text-center">
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
<script type="text/javascript">
    var varGlobalPassModule = {
        priorities: JSON.parse('{!! json_encode($taskPriorities) !!}'), 
        status: JSON.parse('{!! json_encode($taskStatus) !!}'), 
        routePriority: '{{ route('project::task.general.save.priority') }}',
        routeStatus: '{{ route('project::task.general.save.status') }}'
    };
</script>