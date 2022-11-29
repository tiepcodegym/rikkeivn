<?php
use Rikkei\Core\View\View;
use Carbon\Carbon;
use Rikkei\Project\Model\Task;
$today = Carbon::parse(Carbon::today());
?>

@if(isset($collectionModel) && count($collectionModel))
    @foreach($collectionModel as $childIndex => $item)
        <tr style="background: #eef0f6;" data-parent-id="{{ $parentId }}">
            <td>
                @if ($index)
                {{ $index . '.' . ($childIndex + 1) }}
                @endif
            </td>
            @if (isset($hasColumnProject) && $hasColumnProject)
            <td></td>
            @endif
            <td>
                <a
                   @if ($redirect)
                   href="{{ route('project::task.edit', ['id' => $item->id ]) }}"
                   target="_blank"
                   @else
                   class="post-ajax" 
                   href="#" data-url-ajax="{{ route('project::task.edit.ajax', ['id' => $item->id ]) . '?parent_id=' . $parentId }}"
                   data-callback-success="loadModalFormSuccess"
                   @endif
                >
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
            <td>
                {{ $item->getAction() }}
            </td>
        </tr>
    @endforeach
@endif
          