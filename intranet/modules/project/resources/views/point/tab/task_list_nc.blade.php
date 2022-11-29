<?php
use Rikkei\Core\View\View;
use Carbon\Carbon;
use Rikkei\Project\Model\Task;
$today = Carbon::parse(Carbon::today());
?>

<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th class="col-id" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                        <th class="">{{ trans('project::view.Title') }}</th>
                        <th class="">{{ trans('project::view.Request date') }}</th>
                        <th class="">{{ trans('project::view.Estimated date') }}</th>
                        <th class="">{{ trans('project::view.Request standard') }}</th>
                        <th class="">{{ trans('project::view.Requester') }}</th>
                        <th class="">{{ trans('project::view.Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($collectionModel) && count($collectionModel))
                        <?php $i = View::getNoStartGrid($collectionModel); ?>
                        @foreach($collectionModel as $item)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>
                                    <a class="post-ajax" href="#" data-url-ajax="{{ URL::route('project::task.ncm.edit', ['id' => $item->id ]) }}"
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
                                <td>
                                    @if ($item->request_date)
                                        {{ Carbon::parse($item->request_date)->format('Y-m-d') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($item->duedate)
                                        {{ Carbon::parse($item->duedate)->format('Y-m-d') }}
                                    @endif
                                </td>
                                <td>{{ $item->request_standard }}</td>
                                <td>{{ $item->requester_email }}</td>
                                <td class="status-index" data-id="{{$item->id}}" data-status="{{ $item->status }}" data-select="0">{{ $item->getStatus() }}</td>
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