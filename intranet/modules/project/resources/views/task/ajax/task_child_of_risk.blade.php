<?php
use Rikkei\Core\View\View;
use Carbon\Carbon;
use Rikkei\Project\Model\Task;
$today = Carbon::parse(Carbon::today());
?>

    
@if(isset($collectionModel) && count($collectionModel))
    <tr data-risk-id="{{ $riskId }}">
        <td></td>
        <td colspan="7">
            <table class="edit-table table table-bordered table-condensed dataTable">
                <tr>
                    <th class="">{{ trans('project::view.No.') }}</th>
                    <th class="">{{ trans('project::view.Title') }}</th>
                    <th class="">{{ trans('project::view.Status') }}</th>
                    <th class="">{{ trans('project::view.Priority') }}</th>
                    <th class="">{{ trans('project::view.Type') }}</th>
                    <th class="">{{ trans('project::view.Assignee') }}</th>
                    <th class="">{{ trans('project::view.Create date') }}</th>
                    <th class="">{{ trans('project::view.Deadline') }}</th>
                </tr>
                @foreach($collectionModel as $childIndex => $item)
                <tr>
                    <td>{{ ($index + 1) . '.' . ($childIndex + 1) }}</td>
                    <td class="">
                        <a class="post-ajax btn-add-task" href="#" data-url-ajax="{{ route('project::task.edit.ajax', ['id' => $item->id ]) . '?risk_id=' . $riskId }}"
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
                    <td>{{ $item->getStatus() }}</td>
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
                </tr>
                @endforeach
            </table>
        </td>
    </tr>
@endif
          