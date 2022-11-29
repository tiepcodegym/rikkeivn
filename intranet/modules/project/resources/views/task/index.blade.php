@extends('layouts.default')

@section('title')
{{ $titlePage }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
@endsection

@section('content')
<?php
use Carbon\Carbon;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Task;

$tableTask = Task::getTableName();
if ($project->isOpen()) {
    $buttons = [[
        'label' => 'Add Task', 
        'url'=> URL::route('project::task.add', ['id' => $project->id]), 
        'type' => 'link',
        'class' => 'btn btn-primary'
    ]];
} else {
    $buttons = null;
}
$today = Carbon::parse(Carbon::today());
?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="filter-panel-left">
                    <h3 class="box-body-title">Project: {{ $project->name }}</h3>
                </div>
                @include('team::include.filter', ['domainTrans' => 'project', 'buttons' => $buttons])
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id width-10" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                            <th class="sorting {{ Config::getDirClass('title') }} col-title" style="width: 250px;" data-order="title" data-dir="{{ Config::getDirOrder('title') }}">{{ trans('project::view.Title') }}</th>
                            <th class="sorting {{ Config::getDirClass('status') }} col-status" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('project::view.Status') }}</th>
                            @if ($typeShow != Task::TYPE_WO)
                                <th class="sorting {{ Config::getDirClass('priority') }} col-priority" data-order="priority" data-dir="{{ Config::getDirOrder('priority') }}">{{ trans('project::view.Priority') }}</th>
                            @endif
                            <th class="sorting {{ Config::getDirClass('email') }} col-email" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('project::view.Assignee') }}</th>
                            <th class="sorting {{ Config::getDirClass('created_at') }} col-created_at" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('project::view.Create date') }}</th>
                            @if ($typeShow != Task::TYPE_WO)
                                <th class="sorting {{ Config::getDirClass('duedate') }} col-duedate" data-order="duedate" data-dir="{{ Config::getDirOrder('duedate') }}">{{ trans('project::view.Deadline') }}</th>
                            @endif
                            <th class="sorting {{ Config::getDirClass('type') }} col-type" data-order="type" data-dir="{{ Config::getDirOrder('type') }}">{{ trans('project::view.Type') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[title]" value="{{ Form::getFilterData("title") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select style="width: 90px" class="form-control select-grid filter-grid select-search" name="filter[number][{{ $tableTask }}.status]">
                                            <option value="">&nbsp;</option>
                                            @foreach($taskStatus as $key => $value)
                                                <option value="{{ $key }}" {{ Form::getFilterData('number', $tableTask.'.status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            @if ($typeShow != Task::TYPE_WO)
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select style="width: 90px" class="form-control select-grid filter-grid select-search" name="filter[number][priority]">
                                                <option value="">&nbsp;</option>
                                                @foreach($taskPriorities as $key => $value)
                                                    <option value="{{ $key }}" {{ Form::getFilterData('number', 'priority') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            @endif
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[email]" value="{{ Form::getFilterData("email") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tableTask }}.created_at]" value="{{ Form::getFilterData("{$tableTask}.created_at") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            @if ($typeShow != Task::TYPE_WO)
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[duedate]" value="{{ Form::getFilterData("duedate") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                        </div>
                                    </div>
                                </td>
                            @endif
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select style="width: 90px" class="form-control select-grid filter-grid select-search" name="filter[number][{{ $tableTask }}.type]">
                                            <option value="">&nbsp;</option>
                                            @foreach($taskTypes as $key => $value)
                                                <option value="{{ $key }}" {{ Form::getFilterData('number', $tableTask.'.type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>
                                        <a href="{{ route('project::task.edit', ['id' => $item->id ]) }}">
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
                                    @if ($typeShow != Task::TYPE_WO)
                                        <td class="priority-index" data-id="{{$item->id}}" data-priority="{{ $item->priority }}" data-select="0">{{ $item->getPriority() }}</td>
                                    @endif
                                    <td>{{ preg_replace('/@.*/', '',$item->email) }}</td>
                                    <td>
                                        @if ($item->created_at)
                                            {{ Carbon::parse($item->created_at)->format('Y-m-d') }}
                                        @endif
                                    </td>
                                    @if ($typeShow != Task::TYPE_WO)
                                        <td>
                                            @if ($item->duedate)
                                                {{ Carbon::parse($item->duedate)->format('Y-m-d') }}
                                            @endif
                                        </td>
                                    @endif
                                    <td>{{ $item->getType() }}</td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="14" class="text-center">
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
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script type="text/javascript">
    var varGlobalPassModule = {
        priorities: JSON.parse('{!! json_encode($taskPriorities) !!}'), 
        status: JSON.parse('{!! json_encode($taskStatus) !!}'), 
        routePriority: '{{ route('project::task.general.save.priority') }}',
        routeStatus: '{{ route('project::task.general.save.status') }}'
    };
    jQuery(document).ready(function ($) {
//        jQuery(".select-search").select2({ 
//            dropdownAutoWidth : 
//        });
        selectSearchReload();
    });
</script>
@endsection

