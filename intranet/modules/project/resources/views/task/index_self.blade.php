@extends('layouts.default')

@section('title')
{{ $titlePage }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
@endsection

@section('content')
<?php
use Carbon\Carbon;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;

$tableTask = Task::getTableName();
$tableProject = Project::getTableName();
$tableEmployee = Employee::getTableName();
$tableTeam = Team::getTableName();
$today = Carbon::parse(Carbon::today());
?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                @include('team::include.filter', ['domainTrans' => 'project'])
            </div>
            <div class="table-responsive" id="myTask-index">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id width-10" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                            <th class="sorting {{ Config::getDirClass('title') }} col-title" style="width: 250px;" data-order="title" data-dir="{{ Config::getDirOrder('title') }}">{{ trans('project::view.Title') }}</th>
                            <th class="sorting {{ Config::getDirClass('email') }} col-email" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('project::view.PM') }}</th>
                            <th class="sorting {{ Config::getDirClass('project_name') }} col-project_name" data-order="project_name" data-dir="{{ Config::getDirOrder('project_name') }}">{{ trans('project::view.Project') }}</th>
                            <th class="sorting {{ Config::getDirClass('group') }} col-group" data-order="group" data-dir="{{ Config::getDirOrder('group') }}">{{ trans('project::view.Group') }}</th>
                            <th class="sorting {{ Config::getDirClass('type') }} col-type" data-order="type" data-dir="{{ Config::getDirOrder('type') }}">{{ trans('project::view.Type') }}</th>
                            <th class="sorting {{ Config::getDirClass('status') }} col-status" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('project::view.Status') }}</th>
                            <th class="sorting {{ Config::getDirClass('priority') }} col-priority" data-order="priority" data-dir="{{ Config::getDirOrder('priority') }}">{{ trans('project::view.Priority') }}</th>
                            <th class="sorting {{ Config::getDirClass('created_at') }} col-created_at" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}" style="width: 100px;">{{ trans('project::view.Create date') }}</th>
                            <th class="sorting {{ Config::getDirClass('duedate') }} col-duedate" data-order="duedate" data-dir="{{ Config::getDirOrder('duedate') }}" style="width: 100px;">{{ trans('project::view.Deadline') }}</th
                            
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
                                        <input type="text" name="filter[ProjEmployee.email]" value="{{ Form::getFilterData('ProjEmployee.email') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tableProject }}.name]" value="{{ Form::getFilterData($tableProject.'.name') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tableTeam }}.name]" value="{{ Form::getFilterData($tableTeam.'.name') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select style="width: 120px" class="form-control select-grid filter-grid select-search" name="filter[number][{{ $tableTask }}.type]">
                                            <option value="">&nbsp;</option>
                                            @foreach($taskType as $key => $value)
                                                <option value="{{ $key }}" {{ Form::getFilterData('number', $tableTask.'.type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
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
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tableTask }}.created_at]" value="{{ Form::getFilterData("{$tableTask}.created_at") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[duedate]" value="{{ Form::getFilterData("duedate") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td class="title-myTask-{{$item->id}}">
                                        @if (in_array($item->type, Task::typeLabelMyTask()))
                                            <a class="post-ajax" href="{{ $item->getUrl() }}" data-url-ajax="{{ route('project::task.edit.ajax', ['id' => $item->id ]) }}"
                                                data-callback-success="loadModalFormSuccess">
                                                @if($item->status != Task::STATUS_CLOSED && 
                                                        $item->duedate !== null      &&
                                                        $item->duedate->lt($today)
                                                )
                                                <span class="title-myTask text-color-red">
                                                    {{ $item->title }}
                                                </span>
                                                @else
                                                <span class="title-myTask">
                                                    {{ $item->title }}
                                                <span>
                                                @endif
                                            
                                            </a>
                                        @else
                                            <a href="{{ $item->getUrl() }}">
                                                @if($item->status != Task::STATUS_CLOSED && 
                                                        $item->duedate !== null      &&
                                                        $item->duedate->lt($today)
                                                )
                                                    <span class="title-myTask text-color-red">
                                                    {{ $item->title }}
                                                    </span>
                                                @else
                                                    <span class="title-myTask">
                                                    {{ $item->title }}
                                                    </span>
                                                @endif
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ strstr($item->email, '@', true) }}</td>
                                    <td>{{ $item->project_name }}</td>
                                    <td>{{ $item->team_name }}</td>
                                    <td class="type-myTask-{{$item->id}}">{{ $item->getType() }}</td>
                                    @if($item->type === Task::TYPE_WO || $item->type === Task::TYPE_REWARD)
                                        <td >{{ $item->getStatus() }}</td>
                                        <td>{{ $item->getPriority() }}</td>
                                    @else 
                                        <td class="status-index status-myTask-{{$item->id}}" data-id="{{$item->id}}" data-status="{{ $item->status }}" data-select="0">{{ $item->getStatus() }}</td>
                                        <td class="priority-index priority-myTask-{{$item->id}}" data-id="{{$item->id}}" data-priority="{{ $item->priority }}" data-select="0">{{ $item->getPriority() }}</td>
                                    @endif
                                    <td class="created-myTask-{{$item->id}}">
                                        @if ($item->created_at)
                                            {{ Carbon::parse($item->created_at)->format('Y-m-d') }}
                                        @endif
                                    </td>
                                    <td class="duedate-myTask-{{$item->id}}">
                                        @if ($item->duedate)
                                            {{ Carbon::parse($item->duedate)->format('Y-m-d') }}
                                        @endif
                                    </td>
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

<div class="modal fade" id="modal-ncm-editor">
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('project::view.Task edit') }}</h4>
            </div>
            <div class="modal-body">
                <div class="modal-ncm-editor-main">
       
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script type="text/javascript">

    var varGlobalPassModule = {
        priorities: JSON.parse('{!! json_encode($taskPriorities) !!}'), 
        status: JSON.parse('{!! json_encode($taskStatus) !!}'), 
        routePriority: '{{ route('project::task.general.save.priority') }}',
        routeStatus: '{{ route('project::task.general.save.status') }}'
    };
    jQuery(document).ready(function ($) {
        selectSearchReload();
    });
    
</script>
@endsection

