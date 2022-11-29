<?php
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Task;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\TeamList;
$tableEmployee = Employee::getTableName();
$tableProject = Project::getTableName();
$tableTask = Task::getTableName();
$urlFilter = trim(URL::route('project::report.issue'), '/') . '/';
$priorityFilter = Form::getFilterData('except', "{$tableTask}.priority", $urlFilter);
$typeFilter = Form::getFilterData('except', "{$tableTask}.type", $urlFilter);
$statusFilter = Form::getFilterData('except', "{$tableTask}.status", $urlFilter);
$teamsOptionAll = TeamList::toOption(null, true, false);
$teamPath = Team::getTeamPath();
$listTypeIssue = [Task::TYPE_CRITICIZED, Task::TYPE_ISSUE_COST, Task::TYPE_ISSUE_QUA, Task::TYPE_ISSUE_TL, Task::TYPE_ISSUE_PROC];
?>
@extends('layouts.default')
@section('title')
    {{ trans('project::view.Issues list') }}
@endsection
@section('content')
<div class="row list-css-page">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row form-horizontal filter-input-grid">
                    <div class="col-sm-12">
                        <div class="form-group row col-sm-4">
                            <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Owner') }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="filter[except][{{ $tableEmployee }}.email]" value="{{ Form::getFilterData('except', "{$tableEmployee}.email", $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                            </div>
                        </div>
                        <div class="form-group row col-sm-4">
                            <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Status') }}</label>
                            <div class="col-sm-9">
                                <select class="form-control select-grid filter-grid" name="filter[except][{{$tableTask}}.status]">
                                    <option value="">&nbsp;</option>
                                    @foreach($statusIssue as $key => $value)
                                        <option value="{{ $key }}"<?php
                                        if ($key == $statusFilter): ?> selected<?php endif;
                                            ?>>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row col-sm-4">
                            <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Priority') }}</label>
                            <div class="col-sm-9">
                                <select class="form-control select-grid filter-grid" name="filter[except][{{$tableTask}}.priority]">
                                    <option value="">&nbsp;</option>
                                    @foreach($priority as $key => $value)
                                        <option value="{{ $key }}"<?php
                                        if ($key == $priorityFilter): ?> selected<?php endif;
                                            ?>>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group row col-sm-4">
                            <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Project') }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="filter[except][{{ $tableProject }}.name]" value="{{ Form::getFilterData('except', "{$tableProject}.name", $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                            </div>
                        </div>
                        <div class="form-group row col-sm-4">
                            <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Issue Type') }}</label>
                            <div class="col-sm-9">
                                <select name="filter[except][{{$tableTask}}.type]" class="form-control select-grid filter-grid">
                                    <option value="">&nbsp;</option>
                                    @foreach($typeIssue as $key => $value)
                                        <option value="{{ $key }}"<?php
                                        if ($key == $typeFilter): ?> selected<?php endif;
                                            ?>>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row col-sm-4">
                            <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Summary') }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="filter[except][{{ $tableTask }}.title]" value="{{ Form::getFilterData('except', "{$tableTask}.title", $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group row col-sm-4">
                            <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.From date2') }}</label>
                            <div class="col-sm-9">
                                <input class="date-picker form-control filter-grid" id="time_from" name="filter[except][created_at]" value="{{ Form::getFilterData('except', 'created_at', $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." />
                            </div>
                        </div>
                        <div class="form-group row col-sm-4">
                            <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.To date2') }}</label>
                            <div class="col-sm-9">
                                <input class='date-picker form-control filter-grid' id="time_to" name=filter[except][updated_at]" value="{{ Form::getFilterData('except', 'updated_at', $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." />
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="col-sm-3 col-form-label">{{ trans('project::view.Division') }}</label>
                                <div class="col-sm-9">
                                    <div class="list-team-select-box">
                                        @if ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                                            <div class="input-box filter-multi-select multi-select-style btn-select-team">
                                                <select name="filter[except][teams.id][]" id="select-team-member" multiple
                                                        class="form-control filter-grid multi-select-bst select-multi"
                                                        autocomplete="off">
                                                    {{-- show team available --}}
                                                    @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                                        @foreach($teamsOptionAll as $option)
                                                            <option value="{{ $option['value'] }}" class="checkbox-item"
                                                                    {{ in_array($option['value'], array_map("trim", explode(",", $teamIdCurrent))) ? 'selected' : '' }}>{{ $option['label'] }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        @endif
                                        {{-- end show team available --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>

                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="box-body">
                            <div class="filter-action">
                                <div class="col-sm-7">
                                </div>
                                <button href="#" style="margin-right: 10px;" class="btn add-issue btn-success col-sm-1"><i class="fa fa-plus"></i> {{ trans('project::view.Add issue') }}</button>
                                <form action="{{ route('project::project.export.issue') }}" method="post">
                                    {!! csrf_field() !!}
                                    <button type="submit" class="btn btn-success btn-submit-action col-sm-1" style="margin-right: 20px">{{ trans('project::view.Export') }}</button>
                                </form>
                                <button class="btn btn-primary btn-reset-filter col-sm-1" style="margin-right: 5px">
                                    <span>{{ trans('team::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                                <button class="btn btn-primary btn-search-filter col-sm-1">
                                    <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="edit-table table table-bordered table-condensed dataTable">
                                <thead>
                                <tr>
                                    <th class="width-5-per align-center">{{trans('project::view.No')}}</th>
                                    <th >{{trans('project::view.Issue Type')}}</th>
                                    <th >{{trans('project::view.Title')}}</th>
                                    <th >{{trans('project::view.Division')}}</th>
                                    <th>{{trans('project::view.Project')}}</th>
                                    <th >{{trans('project::view.Status')}}</th>
                                    <th >{{trans('project::view.Priority')}}</th>
                                    <th >{{trans('project::view.Owner')}}</th>
                                    <th >{{trans('project::view.Due Date')}}</th>
                                    <th >{{trans('project::view.Create date')}}</th>
                                    <th >{{trans('project::view.Update date')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if (count($collectionModel))
                                    <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                                    @foreach($collectionModel as $issue)
                                        <tr role="row" >
                                            <td>{{ $i++ }}</td>
                                            <td>@if ($issue->type)
                                                    {{ empty(Task::typeLabelForIssue()[$issue->type]) ? '' : Task::typeLabelForIssue()[$issue->type] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($issue->title)
                                                    @if (in_array($issue->type, $listTypeIssue))
                                                        <a href="{{ route('project::task.detail', ['id' => $issue->id]) }}">{!!nl2br(e($issue->title))!!}</a>
                                                    @else
                                                        <a href="{{ route('project::task.edit', ['id' => $issue->id]) }}">{!!nl2br(e($issue->title))!!}</a>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if ($issue->team_leader)
                                                    {{ $issue->team_leader }}
                                                @endif
                                            </td>
                                            <td><a href="{{ route('project::project.edit', ['id' => $issue->project_id]) }}">{{ $issue->project_name }}</a></td>
                                            <td>
                                                {{ Task::getStatusOfIssue($issue->status, $issue->status_backup) }}
                                            </td>
                                            <td>
                                                @if ($issue->priority)
                                                {{ Task::priorityLabel()[$issue->priority] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($issue->email)
                                                    {{ $issue->email }}
                                                @endif
                                            </td>
                                            <td>
                                                {{ Task::getDuedateOfIssue($issue->task_duedate, $issue->task_duedate_backup) }}
                                            </td>
                                            <td>{{$issue->created_at}}</td>
                                            <td>{{$issue->updated_at}}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="13" class="text-center">
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
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
@include('project::components.add_project_as_risk_issue')
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
    <link href="{{ asset('resource/css/resource.css') }}" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css">
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script type="text/javascript" src="{{ asset('lib/js/bootstrap-dialog.min.js') }}"></script>
    <script type="text/javascript">
        var teamPath = {!! json_encode($teamPath) !!};
        $('input.date-picker').datepicker({
            format: 'yyyy-mm-dd'
        })

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

        var token = '{!! csrf_token() !!}';
        var urlAddIssue = '{{ route('project::wo.editIssue') }}'
        $(document).on('click', '.add-issue', function () {
            if ($('#select_team_leader').val() != 0) {
                $('.add-issue').prop('disabled', false);
            }
            var $curElem = $(this);
            var projectId = $('#select_project').val();
            $('.add-issue').prop('disabled', true);
            $curElem.find('i.fa-plus').removeClass('fa-plus').addClass('fa-refresh').addClass('fa-spin');
            $.ajax({
                url: urlAddIssue.trim(),
                type: 'get',
                dataType: 'text',
                success: function (data) {
                    BootstrapDialog.show({
                        cssClass: 'task-dialog',
                        message: $('<div></div>').html(data),
                        closable: false,
                        buttons: [{
                            id: 'btn-close',
                            icon: 'fa fa-close',
                            label: 'Close',
                            cssClass: 'btn-primary',
                            autospin: false,
                            action: function(dialogRef){
                                dialogRef.close();
                            }
                        },{
                            id: 'btn-save',
                            icon: 'glyphicon glyphicon-check',
                            label: 'Save',
                            cssClass: 'btn-primary',
                            autospin: false,
                            action: function(dialogRef){
                                $('.form-issue-detail').submit();
                            }
                        }]
                    });
                },
                error: function () {
                    alert('ajax fail to fetch data');
                },
                complete: function () {
                    $curElem.find('i.fa-refresh').addClass('fa-plus').removeClass('fa-refresh').removeClass('fa-spin');
                    $('.add-issue').prop('disabled', false);
                }
            });
        });
    </script>
@endsection