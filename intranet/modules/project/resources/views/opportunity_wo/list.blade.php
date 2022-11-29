@extends('layouts.default')

@section('title', 'Opportunity')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('resource/css/resource.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css">
<style>
    .bootstrap-dialog.type-primary .modal-header {
        background-color: #428bca;
    }
    .bootstrap-dialog .bootstrap-dialog-title {
        color: #fff;
        display: inline-block;
        font-size: 16px;
    }

    label.error{
        position: unset;
    }
    @media (min-width: 768px) {
        .modal.task-dialog .modal-dialog {
            width: 93%;
        }
    }
    
</style>
@endsection

@section('content')
<?php
use Carbon\Carbon;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Task;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\TaskNcmRequest;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\CoreUrl;

$tableTask = Task::getTableName();
$tableEmployee = Employee::getTableName();
$tableNcm = TaskNcmRequest::getTableName();
$tableProject = Project::getTableName();

$teamsOptionAll = TeamList::toOption(null, true, false);
$urlFilter = trim(URL::route('project::report.opportunity'), '/') . '/';
$teamPath = Team::getTeamPath();
?>

<div class="row list-css-page">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row form-horizontal filter-input-grid">
                    <div class="col-sm-12">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-3 control-label">Project</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control filter-grid" name="filter[projs.name]" value="{{ Form::getFilterData('projs.name') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-3 control-label">Assignee</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control filter-grid" name="filter[tblEmpAssign.email]" value="{{ Form::getFilterData('tblEmpAssign.email') }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-3 control-label">Status</label>
                                <div class="col-sm-9">
                                    <select class="form-control select-grid filter-grid" name="filter[tasks.status]">
                                        <option></option>
                                        @foreach (Task::statusOpportunityLabel() as $keyStatus => $valueStatus)
                                            <option value="{{ $keyStatus }}" {{ !is_null(Form::getFilterData('tasks.status')) && Form::getFilterData('tasks.status') == $keyStatus ? 'selected' : '' }}>{{ $valueStatus }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ trans('project::view.Division') }}</label>
                                <div class="col-sm-9">
                                    <div class="list-team-select-box">
                                        @if ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                                            <div class="input-box filter-multi-select multi-select-style btn-select-team">
                                                <select name="filter[except][teams.id][]" id="select-team-member" multiple
                                                        class="form-control filter-grid multi-select-bst select-multi" autocomplete="off">
                                                    @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                                        @foreach($teamsOptionAll as $option)
                                                                @if ($teamIdsAvailable === true || (is_array($teamIdsAvailable) && in_array($option['value'], $teamIdsAvailable)))
                                                                    <option value="{{ $option['value'] }}" class="checkbox-item"
                                                                            {{ in_array($option['value'], array_map("trim", explode(",", $teamIdCurrent))) ? 'selected' : '' }}<?php
                                                                            if ($teamIdsAvailable === true):
                                                                            elseif (! in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else:
                                                                        ?>{{ $option['option'] }}<?php endif; ?>>{{ $option['label'] }}</option>
                                                                @endif
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-3 control-label">Priority</label>
                                <div class="col-sm-9">
                                    <select class="form-control select-grid filter-grid" name="filter[tasks.priority]">
                                        <option></option>
                                        @foreach (Task::priorityLabelV2() as $keyS => $priority)
                                            <option value="{{ $keyS }}" {{ !is_null(Form::getFilterData('tasks.priority')) && Form::getFilterData('tasks.priority')  == $keyS ? 'selected' : '' }}>{{ $priority }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 row">
                        <div class="col-md-4">
                            <label for="deadline_from" class="col-md-3 control-label">Plan end date</label>
                            <div class="col-md-9">
                                <div class="form-inline col-md-12">
                                    <label for="deadline_from" class="col-md-3 control-label">{{ trans('project::view.From') }}</label>
                                    <input class="date-picker col-md-10 form-control filter-grid" id="deadline_from" name="filter[except][tasks.deadline_from]" value="{{ Form::getFilterData('except', 'tasks.deadline_from') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                                <div class="form-inline col-sm-12 margin-top-20">
                                    <label for="deadline_from" class="col-sm-3 control-label">{{ trans('project::view.To') }}</label>
                                    <input class="date-picker form-control filter-grid" id="deadline_to" name="filter[except][tasks.deadline_to]" value="{{ Form::getFilterData('except', 'tasks.deadline_to') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="created_from" class="col-sm-3 control-label">{{ trans('project::view.Created') }}</label>
                            <div class="col-md-9">
                                <div class="form-inline col-md-12">
                                    <label for="created_from" class="col-md-3 control-label">{{ trans('project::view.From') }}</label>
                                    <input class="date-picker col-md-10 form-control filter-grid" id="created_from" name="filter[except][tasks.created_from]" value="{{ Form::getFilterData('except', 'tasks.created_from') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                                <div class="form-inline col-sm-12 margin-top-20">
                                    <label for="created_to" class="col-sm-3 control-label">{{ trans('project::view.To') }}</label>
                                    <input class="date-picker form-control filter-grid" id="created_to" name="filter[except][tasks.created_to]" value="{{ Form::getFilterData('except', 'tasks.created_to') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="created_from" class="col-sm-3 control-label">{{ trans('project::view.Updated') }}</label>
                            <div class="col-md-9">
                                <div class="form-inline col-md-12">
                                    <label for="updated_form" class="col-md-3 control-label">{{ trans('project::view.From') }}</label>
                                    <input class="date-picker col-md-10 form-control filter-grid" id="updated_form" name="filter[except][tasks.updated_form]" value="{{ Form::getFilterData('except', 'tasks.updated_form') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                                <div class="form-inline col-sm-12 margin-top-20">
                                    <label for="updated_to" class="col-sm-3 control-label">{{ trans('project::view.To') }}</label>
                                    <input class="date-picker form-control filter-grid" id="updated_to" name="filter[except][tasks.updated_to]" value="{{ Form::getFilterData('except', 'tasks.updated_to') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="box-body">
                            <button class="btn btn-primary btn-reset-filter col-sm-1" style="display: inline-block;float: right;margin-right: 5px">
                                <span>{{ trans('team::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                            </button>
                            <button class="btn btn-primary btn-search-filter col-sm-1" style="display: inline-block;float: right;margin-right: 5px">
                                <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                            </button>
                            <form action="{{ route('project::report.opportunity.export') }}" method="post" style="margin-block-end: 0;">
                                {!! csrf_field() !!}
                                <button class="btn btn-success col-sm-1" style="display: inline-block;float: right;margin-right: 20px;">
                                    {{ trans('project::view.Export') }}
                                </button>
                            </form>
                            <button href="#" style="display: inline-block;float: right;margin-right: 5px;" class="btn add-opportunity btn-success col-sm-1"><i class="fa fa-plus"></i> Create</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info" style="font-size: 14px;">
                                <thead>
                                    <tr role="row">
                                        <th style="width: 10px;">ID</th>
                                        <th style="width: 150px;">Opportunity source</th>
                                        <th style="width: 80px;">Division</th>
                                        <th style="width: 300px;">Project</th>
                                        <th style="width: 80px;">Status</th>
                                        <th >Priority</th>
                                        <th style="width: 40px;">Assignee</th>
                                        <th >Plan end date</th>
                                        <th >Create date</th>
                                        <th >Update date</th>
                                        <th >&nbsp;</th>
                                   </tr>
                                </thead>
                                <tbody>
                                    @if(count($collectionModel) > 0)
                                    @foreach($collectionModel as $item)
                                    <tr role="row" data-id="{{ $item->id }}">
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->getOpportunitySource() }}</td>
                                        <td>{{ $item->team_name }}</td>
                                        <td>
                                            <a href="{{ route('project::project.edit', ['id' => $item->project_id]) }}">{{ $item->projs_name }}</a>
                                        </td>
                                        <td>
                                            {{ $item->getStatusOpportunity() }}
                                        </td>
                                        <td>
                                            {{ $item->getPriority() }}
                                        </td>
                                        <td>
                                            {{ $item->assign_email }}
                                        </td>
                                        <td>
                                            {{ Carbon::parse($item->duedate)->format('Y-m-d') }}
                                        </td>
                                        <td>
                                            {{ Carbon::parse($item->created_at)->format('Y-m-d') }}
                                        </td>
                                        <td>
                                            {{ Carbon::parse($item->updated_at)->format('Y-m-d') }}
                                        </td>
                                        <td style="text-align: right;">
                                            <a class="btn-edit" href="{{ route('project::report.opportunity.detail', ['id'=>$item->id]) }}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <button class="btn-delete btn-delete-opportunity" data-id="{{ $item->id }}">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10" class="text-align-center"><h2>{{trans('sales::view.No result not found')}}</h2></td>
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
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->

<div class="modal fade modal-danger" id="modal-delete-confirm-opportunity" tabindex="-1" role="dialog">
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
                <button type="button" class="btn btn-outline btn-submit" data-id="">{{ Lang::get('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
@endsection

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
    var urlGetForm = '{{ route("project::wo.getFormOpportunity") }}';
    var modalTitle = 'Opportunity info';
    var urlDeleteNC = '{{ route("project::nc.delete") }}';

    jQuery(document).ready(function ($) {
        $('input.date-picker').datepicker({
            format: 'yyyy-mm-dd'
        })
    });

    $(document).on('click', '.add-opportunity', function () {
        var $curElem = $(this);
        $('.add-opportunity').prop('disabled', true);
        $curElem.find('i.fa-plus').removeClass('fa-plus').addClass('fa-refresh').addClass('fa-spin');
        $.ajax({
            url: urlGetForm,
            type: 'get',
            data: {},
            dataType: 'text',
            success: function (data) {
                BootstrapDialog.show({
                    title: modalTitle,
                    cssClass: 'task-dialog',
                    message: $('<div></div>').html(data),
                    closable: false,
                    buttons: [{
                        id: 'btn-opportunity-close',
                        icon: 'fa fa-close',
                        label: 'Close',
                        cssClass: 'btn-primary',
                        autospin: false,
                        action: function(dialogRef){
                            dialogRef.close();
                        }
                    },{
                        id: 'btn-opportunity-save',
                        icon: 'glyphicon glyphicon-check',
                        label: 'Save',
                        cssClass: 'btn-primary',
                        autospin: false,
                        action: function(dialogRef){
                            $('.form-opportunity-detail').submit();
                        }
                    }]
                });
            },
            error: function () {
                alert('ajax fail to fetch data');
            },
            complete: function () {
                $curElem.find('i.fa-refresh').addClass('fa-plus').removeClass('fa-refresh').removeClass('fa-spin');
                $('.add-opportunity').prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btn-delete-opportunity', function() {
        var issueId = $(this).attr('data-id');
        $('#modal-delete-confirm-opportunity').modal('show');
        $('#modal-delete-confirm-opportunity').find(".btn-submit").attr('data-id', issueId);
    });
    $(document).on('click', '#modal-delete-confirm-opportunity .btn-submit', function () {
        $('#modal-delete-confirm-opportunity').modal('hide');
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

    $(document).on('mouseup', 'li.checkbox-item', function () {
        var domInput = $(this).find('input');
        var id = domInput.val();
        var isChecked = !domInput.is(':checked');
        if (teamPath[id] && typeof teamPath[id].child !== "undefined") {
            var teamChild = teamPath[id].child;
            $('li.checkbox-item input').map((i, el) => {
                if (teamChild.indexOf(parseInt($(el).val())) !== -1 && $(el).is(':checked') === !isChecked) {
                    $(el).click();
                }
            });
        }
        setTimeout(() => {
            changeLabelSelected();
        }, 0)
    });
    $(document).ready(function () {
        selectSearchReload();
        changeLabelSelected();
        $('.select-multi').multiselect({
            numberDisplayed: 1,
            nonSelectedText: '--------------',
            allSelectedText: '{{ trans('project::view.All') }}',
            onDropdownHide: function(event) {
                RKfuncion.filterGrid.filterRequest(this.$select);
            }
        });
        $('.js-select-multi-role').multiselect({
            numberDisplayed: 1,
            nonSelectedText: '--------------',
            allSelectedText: '{{ trans('project::view.All') }}',
            enableCaseInsensitiveFiltering: true,
            onDropdownHide: function(event) {
                RKfuncion.filterGrid.filterRequest(this.$select);
            }
        });
        // Limit the string length to column roles.
        $('.role-special').shortedContent({showChars: 150});
    });

    function changeLabelSelected() {
        var checkedValue = $(".list-team-select-box option:selected");
        var title = '';
        if (checkedValue.length === 0) {
            $(".list-team-select-box .multiselect-selected-text").text('--------------');
        }
        if (checkedValue.length === 1) {
            $(".list-team-select-box .multiselect-selected-text").text($.trim(checkedValue.text()));
        }
        for (let i = 0; i < checkedValue.length; i++) {
            title += $.trim(checkedValue[i].label) + ', ';
        }
        $('.list-team-select-box button').prop('title', title.slice(0, -2))
    }
</script>
@endsection