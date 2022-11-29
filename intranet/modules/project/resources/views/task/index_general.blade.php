@extends('layouts.default')

@section('title')
{{ $titlePage }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
<style>
    .member-group-btn .filter-action {
        display: inline-block;
    }
</style>
@endsection

@section('content')
<?php
use Carbon\Carbon;
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Project\Model\Task;
use Rikkei\Team\View\Config;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Team;

$tableTask = Task::getTableName();
$tableProject = Project::getTableName();
$tableTeam = Team::getTableName();
$buttons = [[
    'label' => 'Add Task', 
    'url'=> URL::route('project::task.general.create'), 
    'type' => 'link',
    'class' => 'btn btn-primary'
]];
$today = Carbon::parse(Carbon::today());
?>
<div class="nav-tabs-custom tab-keep-status" >
    <ul class="nav nav-tabs">
        <li class="active"><a class="relative" href="#My_Task" data-toggle="tab" aria-expanded="false">{{ trans('project::view.Project Task') }}</a>@if ($countNewOrProcessSelfTask)<span class="label pull-top-right bg-red">{{ $countNewOrProcessSelfTask }}</span>@endif</li>
        <li><a class="relative" href="#General_Task" data-toggle="tab" aria-expanded="true">{{ trans('project::view.General Task') }}</a>@if ($countNewOrProcessGeneralTask)<span class="label pull-top-right bg-red">{{ $countNewOrProcessGeneralTask }}</span>@endif</li>
    </ul>
    <div class="tab-content min-height-150">
        <div class="tab-pane filter-wrapper" id="General_Task" data-url="{{ $urlGeneralTask }}">
            <div class="row">
                <div class="col-sm-12">
                    <div>
                        <div class="box-body">
                            @include('team::include.filter', ['domainTrans' => 'project', 'buttons' => $buttons])
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                                <thead>
                                    <tr>
                                        <th class="col-id width-10" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                                        <th class="sorting {{ TeamConfig::getDirClass('title', $urlGeneralTask) }} col-title" style="width: 250px;" data-order="title" data-dir="{{ TeamConfig::getDirOrder('title', $urlGeneralTask) }}">{{ trans('project::view.Title') }}</th>
                                        <th class="sorting {{ TeamConfig::getDirClass('status', $urlGeneralTask) }} col-status" data-order="status" data-dir="{{ TeamConfig::getDirOrder('status', $urlGeneralTask) }}">{{ trans('project::view.Status') }}</th>
                                        <th class="sorting {{ TeamConfig::getDirClass('priority', $urlGeneralTask) }} col-priority" data-order="priority" data-dir="{{ TeamConfig::getDirOrder('priority', $urlGeneralTask) }}">{{ trans('project::view.Priority') }}</th>
                                        <th class="sorting {{ TeamConfig::getDirClass('email', $urlGeneralTask) }} col-email" data-order="email" data-dir="{{ TeamConfig::getDirOrder('email', $urlGeneralTask) }}">{{ trans('project::view.Assignee') }}</th>
                                        <th class="sorting {{ TeamConfig::getDirClass('created_at', $urlGeneralTask) }} col-created_at" data-order="created_at" data-dir="{{ TeamConfig::getDirOrder('created_at', $urlGeneralTask) }}">{{ trans('project::view.Create date') }}</th>
                                        <th class="sorting {{ TeamConfig::getDirClass('duedate', $urlGeneralTask) }} col-duedate" data-order="duedate" data-dir="{{ TeamConfig::getDirOrder('duedate', $urlGeneralTask) }}">{{ trans('project::view.Deadline') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="filter-input-grid">
                                        <td>&nbsp;</td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="hidden" name="filter[exception][proj_type]" value="1" class="filter-grid" />
                                                    <input type="text" name="filter[{{ $tableTask }}.title]" value="{{ CoreForm::getFilterData("{$tableTask}.title", null, $urlGeneralTask) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12 filter-multi-select">
                                                    <?php $filterStatus = (array) CoreForm::getFilterData('in', $tableTask.'.status', $urlGeneralTask) ?>
                                                    <select style="width: 90px" class="form-control multi-select-bst filter-grid hidden" id="statusFilter" name="filter[in][{{ $tableTask }}.status][]" multiple="multiple">
                                                        @foreach($taskStatus as $key => $value)
                                                            <option value="{{ $key }}" {{ in_array($key, $filterStatus) ? 'selected' : '' }}>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select style="width: 90px" class="form-control select-grid filter-grid select-search" name="filter[number][{{ $tableTask }}.priority]">
                                                        <option value="">&nbsp;</option>
                                                        @foreach($taskPriorities as $key => $value)
                                                            <option value="{{ $key }}" {{ CoreForm::getFilterData('number', $tableTask.'.priority', $urlGeneralTask) == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" name="filter[email]" value="{{ CoreForm::getFilterData("email", null, $urlGeneralTask) }}" placeholder="{{ trans('team::view.Search') }}..." class="task_email filter-grid form-control" />
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" name="filter[{{ $tableTask }}.created_at]" value="{{ CoreForm::getFilterData("{$tableTask}.created_at", null, $urlGeneralTask) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" name="filter[{{ $tableTask }}.duedate]" value="{{ CoreForm::getFilterData("{$tableTask}.duedate", null, $urlGeneralTask) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @if(isset($collectionModel) && count($collectionModel))
                                        <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                                        @foreach($collectionModel as $item)
                                            <tr>
                                                <td>{{ $i }}</td>
                                                <td>
                                                    <a href="{{ route('project::task.edit', ['id' => $item->id ]) }}" target="_blank">
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
                        <div class="box-body tab-pager">
                            @include('team::include.pager', ['domainTrans' => 'project', 'urlSubmitFilter' => $urlGeneralTask])
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane active filter-wrapper" id="My_Task" data-url="{{ $urlMyTask }}">
            <div class="row">
                <div class="col-sm-12">
                    <div class="box-body text-right member-group-btn">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#modal_import_project_task" style="display: inline-block">
                            Import project task
                        </button>
                        @include('team::include.filter', ['domainTrans' => 'project'])
                    </div>
                    <div class="table-responsive" id="myTask-index">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                            <thead>
                                <tr>
                                    <th class="col-id width-10" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                                    <th class="sorting {{ Config::getDirClass('title', $urlMyTask) }} col-title" style="width: 250px;" data-order="title" data-dir="{{ Config::getDirOrder('title', $urlMyTask) }}">{{ trans('project::view.Title') }}</th>
                                    <th class="sorting {{ Config::getDirClass('email', $urlMyTask) }} col-email" data-order="email" data-dir="{{ Config::getDirOrder('email', $urlMyTask) }}">{{ trans('project::view.PM') }}</th>
                                    <th class="sorting {{ Config::getDirClass('project_name', $urlMyTask) }} col-project_name" data-order="project_name" data-dir="{{ Config::getDirOrder('project_name', $urlMyTask) }}">{{ trans('project::view.Project') }}</th>
                                    <th class="sorting {{ Config::getDirClass('team_name', $urlMyTask) }} col-group" data-order="team_name" data-dir="{{ Config::getDirOrder('team_name', $urlMyTask) }}">{{ trans('project::view.Group') }}</th>
                                    <th class="sorting {{ Config::getDirClass('type', $urlMyTask) }} col-type" data-order="type" data-dir="{{ Config::getDirOrder('type', $urlMyTask) }}">{{ trans('project::view.Type') }}</th>
                                    <th class="sorting {{ Config::getDirClass('status', $urlMyTask) }} col-status" data-order="status" data-dir="{{ Config::getDirOrder('status', $urlMyTask) }}">{{ trans('project::view.Status') }}</th>
                                    <th class="sorting {{ Config::getDirClass('priority', $urlMyTask) }} col-priority" data-order="priority" data-dir="{{ Config::getDirOrder('priority', $urlMyTask) }}">{{ trans('project::view.Priority') }}</th>
                                    <th class="sorting {{ Config::getDirClass('created_at', $urlMyTask) }} col-created_at" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at', $urlMyTask) }}" style="width: 100px;">{{ trans('project::view.Create date') }}</th>
                                    <th class="sorting {{ Config::getDirClass('duedate', $urlMyTask) }} col-duedate" data-order="duedate" data-dir="{{ Config::getDirOrder('duedate', $urlMyTask) }}" style="width: 100px;">{{ trans('project::view.Deadline') }}</th
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="filter-input-grid">
                                    <td>&nbsp;</td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[title]" value="{{ CoreForm::getFilterData("title", null, $urlMyTask) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[ProjEmployee.email]" value="{{ CoreForm::getFilterData('ProjEmployee.email', null, $urlMyTask) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tableProject }}.name]" value="{{ CoreForm::getFilterData($tableProject.'.name', null, $urlMyTask) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tableTeam }}.name]" value="{{ CoreForm::getFilterData($tableTeam.'.name', null, $urlMyTask) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <select style="width: 120px" class="form-control select-grid filter-grid select-search" name="filter[number][{{ $tableTask }}.type]">
                                                    <option value="">&nbsp;</option>
                                                    @foreach($taskType as $key => $value)
                                                        <option value="{{ $key }}" {{ CoreForm::getFilterData('number', $tableTask.'.type', $urlMyTask) == $key ? 'selected' : '' }}>{{ $value }}</option>
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
                                                        <option value="{{ $key }}" {{ CoreForm::getFilterData('number', $tableTask.'.status', $urlMyTask) == $key ? 'selected' : '' }}>{{ $value }}</option>
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
                                                        <option value="{{ $key }}" {{ CoreForm::getFilterData('number', 'priority', $urlMyTask) == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tableTask }}.created_at]" value="{{ CoreForm::getFilterData("{$tableTask}.created_at", null, $urlMyTask) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[duedate]" value="{{ CoreForm::getFilterData("duedate", null, $urlMyTask) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @if(isset($collectionModelMySelf) && count($collectionModelMySelf))
                                    <?php $i = CoreView::getNoStartGrid($collectionModelMySelf); ?>
                                    @foreach($collectionModelMySelf as $item)
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td class="title-myTask-{{$item->id}}">
                                                @if (in_array($item->type, Task::typeLabelMyTask()))
                                                    <a href="{{ $item->getUrl() }}" target="_blank">
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
                                                    <a href="{{ $item->getUrl() }}" target="_blank">
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
                                            @if ((int)$item->type === Task::TYPE_WO || (int)$item->type === Task::TYPE_REWARD)
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
                    <div class="box-body tab-pager">
                        @include('team::include.pager', ['domainTrans' => 'project', 'collectionModel' => $collectionModelMySelf, 'urlSubmitFilter' => $urlMyTask])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal import project task -->
<div class="modal fade" id="modal_import_project_task" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="modal-title">Import project task</h3>
            </div>
            <form action="{{ route('project::task.project.import-task') }}" method="post" enctype="multipart/form-data" id="form-import-supplier">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ trans('asset::view.Choose file import') }} (xls, xlsx)</label>
                            <div class="input-box">
                                <input type="file" name="file_upload" class="form-control" placeholder="{{ trans('asset::view.Add file') }}" required/>
                            </div>
                        </div>
                        <p>Định dạng file</p>
                        <div class="text-center">
                            <img src="{{ URL::asset('asset_managetime/images/template/import_project_task.png') }}"
                            style="width: 100%"
                            alt="import project task"
                            >
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                            <button type="submit" class="btn btn-primary pull-right">{{ trans('asset::view.Import') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
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
    if (location.hash == '#My_Task') {
        $(".add-general-task").addClass('hidden');
    }
    $("a[href='#My_Task']").click(function () {
        $('.add-general-task').addClass('hidden');
    })
    $("a[href='#General_Task']").click(function () {
        $('.add-general-task').removeClass('hidden');
    })
</script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        selectSearchReload();
        $('#statusFilter').multiselect({
            numberDisplayed: 2,
            nonSelectedText: '----',
            allSelectedText: '{{ trans('project::view.All') }}',
            onDropdownHide: function(event) {
                RKfuncion.filterGrid.filterRequest(this.$select);
            }
        });
    });
</script>
@endsection
