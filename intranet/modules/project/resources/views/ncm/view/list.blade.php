@extends('layouts.default')

@section('title')
{{ $titlePage }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
<style>
    .bootstrap-dialog.type-primary .modal-header {
        background-color: #428bca;
    }
    .bootstrap-dialog .bootstrap-dialog-title {
        color: #fff;
        display: inline-block;
        font-size: 16px;
    }
    .datepicker {
        background-color: #ecf0f5 !important;
    }

    .datepicker table tr td.day:hover {
        background: #fff;
    }

    .datepicker-dropdown:before {
        border-top: 7px solid #ecf0f5 !important;
    }

    .datepicker-dropdown:after {
        border-top: 7px solid #ecf0f5 !important;
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
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\TaskNcmRequest;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\Risk;
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\CoreUrl;

$tableTask = Task::getTableName();
$tableEmployee = Employee::getTableName();
$tableNcm = TaskNcmRequest::getTableName();
$tableProject = Project::getTableName();
$teamsOptionAll = TeamList::toOption(null, true, false);
$urlFilter = trim(URL::route('project::report.risk'), '/') . '/';
$teamPath = Team::getTeamPath();
?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="row form-horizontal filter-input-grid box-body">
                <div class="col-sm-12">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-3 control-label">{{ trans('project::view.Title') }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="filter[{{ $tableTask }}.title]" value="{{ CoreForm::getFilterData($tableTask.'.title') }}" placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-3 control-label">{{ trans('project::view.Project name') }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="filter[{{ $tableProject }}.name]" value="{{ CoreForm::getFilterData($tableProject.'.name') }}" placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-3 control-label">{{ trans('project::view.Priority') }}</label>
                            <div class="col-sm-9">
                                <select class="form-control select-grid filter-grid select-search" name="filter[{{ $tableTask }}.priority]">
                                    <option value="">&nbsp;</option>
                                    @foreach($taskPriority as $key => $item)
                                        <option value="{{ $key }}" {{ CoreForm::getFilterData($tableTask.'.priority') == $key ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{ trans('project::view.Owner') }}</label>
                            <div class="col-sm-9">
                                @php
                                    $ownerFilter = CoreForm::getFilterData('tblEmpAssign.email');
                                @endphp
                                <select name="filter[tblEmpAssign.email]" id="flt_employee_owner" class="form-control select-grid filter-grid select-search">
                                    <option value="">&nbsp;</option>
                                    @foreach($owners as $option)
                                        <option value="{{ $option->email }}" {{ $option->email == $ownerFilter ? 'selected' : '' }}>{{ $option->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-3 control-label">{{ trans('project::view.Status') }}</label>
                            <div class="col-sm-9">
                                <select class="form-control select-grid filter-grid select-search" name="filter[number][{{ $tableTask }}.status]">
                                    <option value="">&nbsp;</option>
                                    @foreach($ncStatusAll as $key => $value)
                                        <option value="{{ $key }}" {{ CoreForm::getFilterData('number', $tableTask.'.status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 row">
                    <div class="col-md-4">
                        <label for="duedate_from" class="col-md-3 control-label">{{ trans('project::view.Due date') }}</label>
                        <div class="col-md-9">
                            <div class="form-inline col-md-12">
                                <label for="duedate_from" class="col-md-3 control-label">{{ trans('project::view.From') }}</label>
                                <input class="date-picker col-md-10 form-control filter-grid" id="duedate_from" name="filter[except][tasks.duedate_from]" value="{{ CoreForm::getFilterData('except', 'tasks.duedate_from') }}" placeholder="{{ trans('team::view.Search') }}..." />
                            </div>
                            <div class="form-inline col-sm-12 margin-top-20">
                                <label for="duedate_to" class="col-sm-3 control-label">{{ trans('project::view.To') }}</label>
                                <input class="date-picker form-control filter-grid" id="duedate_to" name="filter[except][tasks.duedate_to]" value="{{ CoreForm::getFilterData('except', 'tasks.duedate_to') }}" placeholder="{{ trans('team::view.Search') }}..." />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="created_from" class="col-sm-3 control-label">{{ trans('project::view.Create date') }}</label>
                        <div class="col-md-9">
                            <div class="form-inline col-md-12">
                                <label for="created_from" class="col-md-3 control-label">{{ trans('project::view.From') }}</label>
                                <input class="date-picker col-md-10 form-control filter-grid" id="created_from" name="filter[except][tasks.created_from]" value="{{ CoreForm::getFilterData('except', 'tasks.created_from') }}" placeholder="{{ trans('team::view.Search') }}..." />
                            </div>
                            <div class="form-inline col-sm-12 margin-top-20">
                                <label for="created_to" class="col-sm-3 control-label">{{ trans('project::view.To') }}</label>
                                <input class="date-picker form-control filter-grid" id="created_to" name="filter[except][tasks.created_to]" value="{{ CoreForm::getFilterData('except', 'tasks.created_to') }}" placeholder="{{ trans('team::view.Search') }}..." />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body" style="display: flex; justify-content: flex-end;">
                <div>
                    <button href="#" style="width: 100px; margin-right: 5px;" class="btn btn-success col-sm-1 add-nc">
                        <i class="fa fa-plus"></i> Create
                    </button>
                </div>
                @include('team::include.filter', ['domainTrans' => 'project'])
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id width-10" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('title') }} col-title" style="width: 250px;" data-order="title" data-dir="{{ TeamConfig::getDirOrder('title') }}">{{ trans('project::view.Title') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('name') }} col-name" data-order="name" data-dir="{{ TeamConfig::getDirOrder('name') }}">{{ trans('project::view.Project name') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('request_date') }} col-request_date" data-order="request_date" data-dir="{{ TeamConfig::getDirOrder('request_date') }}">{{ trans('project::view.Create date') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('duedate') }} col-duedate" data-order="duedate" data-dir="{{ TeamConfig::getDirOrder('duedate') }}">{{ trans('project::view.Due date') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('request_standard') }} col-request_standard" data-order="request_standard" data-dir="{{ TeamConfig::getDirOrder('request_standard') }}">{{ trans('project::view.Priority') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('requester_email') }} col-requester_email" data-order="requester_email" data-dir="{{ TeamConfig::getDirOrder('requester_email') }}">{{ trans('project::view.Owner') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('status') }} col-status" data-order="status" data-dir="{{ TeamConfig::getDirOrder('status') }}">{{ trans('project::view.Status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr data-id="{{ $item->id }}">
                                    <td>{{ $i }}</td>
                                    <td>
                                        {{ $item->title }}
                                    </td>
                                    <td>
                                        <a href="{{ route('project::project.edit', ['id' => $item->proj_id]) }}">{{ $item->name }}</a>
                                    </td>
                                    <td>
                                        {{ Carbon::parse($item->created_at)->format('Y-m-d') }}
                                    </td>
                                    <td>
                                        @if ($item->duedate)
                                            {{ Carbon::parse($item->duedate)->format('Y-m-d') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (in_array($item->priority, array_keys($taskPriority)))
                                            {{ $taskPriority[$item->priority] }}
                                        @endif
                                    </td>
                                    <td>{{ $item->assign_email }}</td>
                                    <td>{{ in_array($item->status, array_keys($ncStatusAll)) ? $ncStatusAll[$item->status] : '' }}</td>
                                    <td style="text-align: center;">
                                        <a class="btn-edit" href="{{ route('project::nc.detail', ['id'=>$item->id]) }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <button class="btn-delete btn-delete-nc" data-id="{{ $item->id }}">
                                            <i class="fa fa-trash-o"></i>
                                        </button>
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
@endsection

<div class="modal fade modal-danger" id="modal-delete-confirm-nc" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ Lang::get('core::view.Are you sure delete item(s)?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-submit">{{ Lang::get('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

@section('script')
<script type="text/javascript" src="{{ asset('lib/js/bootstrap-dialog.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ asset('lib/js/jquery.flexText.min.js') }}"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script type="text/javascript" src="{{ URL::asset('lib/js/jquery.cookie.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script type="text/javascript">
    var urlGetFormNC = '{{ route("project::wo.getFormNC") }}';
    var modalNCTitle = 'NC info';
    var urlDeleteNC = '{{ route("project::nc.delete") }}';

    jQuery(document).ready(function ($) {
//        jQuery(".select-search").select2({ 
//            dropdownAutoWidth : 
//        });
        selectSearchReload();
        // RKfuncion.select2.elementRemote(
        //     $('#flt_employee_owner')
        // );
        $('#flt_employee_owner').select2();
        $('input.date-picker').datepicker({
            format: 'yyyy-mm-dd'
        })
    });

    $(document).on('click', '.add-nc', function () {
        var $curElem = $(this);
        $('.add-nc').prop('disabled', true);
        $curElem.find('i.fa-plus').removeClass('fa-plus').addClass('fa-refresh').addClass('fa-spin');
        $.ajax({
            url: urlGetFormNC,
            type: 'get',
            data: {},
            dataType: 'text',
            success: function (data) {
                BootstrapDialog.show({
                    title: modalNCTitle,
                    cssClass: 'task-dialog',
                    message: $('<div></div>').html(data),
                    closable: false,
                    buttons: [{
                        id: 'btn-nc-close',
                        icon: 'fa fa-close',
                        label: 'Close',
                        cssClass: 'btn-primary',
                        autospin: false,
                        action: function(dialogRef){
                            dialogRef.close();
                        }
                    },{
                        id: 'btn-nc-save',
                        icon: 'glyphicon glyphicon-check',
                        label: 'Save',
                        cssClass: 'btn-primary',
                        autospin: false,
                        action: function(dialogRef){
                            $('.form-nc-detail').submit();
                        }
                    }]
                });
            },
            error: function () {
                alert('ajax fail to fetch data');
            },
            complete: function () {
                $curElem.find('i.fa-refresh').addClass('fa-plus').removeClass('fa-refresh').removeClass('fa-spin');
                $('.add-nc').prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btn-delete-nc', function() {
        var issueId = $(this).attr('data-id');
        $('#modal-delete-confirm-nc').modal('show');
        $('#modal-delete-confirm-nc').find(".btn-submit").attr('data-id', issueId);
    });
    $(document).on('click', '#modal-delete-confirm-nc .btn-submit', function () {
        $('#modal-delete-confirm-nc').modal('hide');
        $('.modal-backdrop').remove();
        var issueId = $(this).attr('data-id');
        $.ajax({
            url: urlDeleteNC,
            type: 'GET',
            data: {
                issueId: issueId
            },
            success: function (data) {
                console.log(data);
                $("tr[data-id='" + issueId + "']").remove();
            },
            error: function () {
                alert('ajax fail to fetch data');
            },
        });
    });
</script>
@endsection


