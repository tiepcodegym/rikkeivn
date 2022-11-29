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
                        <th class="col-id" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                        <th class="" style="max-width: 300px">{{ trans('project::view.Title') }}</th>
                        <th class="col-sm-1">{{ trans('project::view.Status') }}</th>
                        <th class="col-sm-1">{{ trans('project::view.Priority') }}</th>
                        <th class="">{{ trans('project::view.Type') }}</th>
                        <th class="">{{ trans('project::view.Assignee') }}</th>
                        <th class="">{{ trans('project::view.Create date') }}</th>
                        <th class="">{{ trans('project::view.Deadline') }}</th>
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
                                <td class="status-index" data-id="{{$item->id}}" data-status="{{ $item->status }}" data-select="0">{{ $item->getStatus() }}</td>
                                <td class="priority-index" data-id="{{$item->id}}" data-priority="{{ $item->priority }}" data-select="0">{{ $item->getPriority() }}</td>
                                <td>{{ $item->getType() }}</td>
                                <td>{{ $item->email }}</td>
                                <td>
                                    @if ($item->created_at)
                                        {{ Carbon::parse($item->created_at)->format('Y-m-d') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($item->duedate)
                                        {{ Carbon::parse($item->duedate)->format('Y-m-d') }}
                                    @endif
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
<script type="text/javascript">
    var varGlobalPassModule = {
        priorities: JSON.parse('{!! json_encode($taskPriorities) !!}'), 
        status: JSON.parse('{!! json_encode($taskStatus) !!}'), 
        routePriority: '{{ route('project::task.general.save.priority') }}',
        routeStatus: '{{ route('project::task.general.save.status') }}'
    };
</script>