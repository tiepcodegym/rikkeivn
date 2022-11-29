<?php
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\CommonIssue;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Project\Model\Risk;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Team;
$tableCommonIssue = CommonIssue::getTableName();
$urlFilter = trim(URL::route('project::report.issue'), '/') . '/';
$listSourceType = Risk::getSourceList();

?>
@extends('layouts.default')
@section('title')
    {{ trans('project::view.LBL_COMMON_ISSUE') }}
@endsection
@section('content')
    <div class="row list-css-page">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="row form-horizontal filter-input-grid">
                        <div class="col-sm-12">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="inputEmail3"
                                           class="col-sm-3 control-label">{{ trans('project::view.Issue Type') }}</label>
                                    <div class="col-sm-6">
                                        <select name="filter[{{$tableCommonIssue}}.issue_type]"
                                                class="form-control filter-grid">
                                            <option value="">&nbsp;</option>
                                            @foreach($typeIssue as $key => $value)
                                                <option value="{{ $key }}" {{ !is_null(Form::getFilterData('common_issue.issue_type')) && Form::getFilterData('common_issue.issue_type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="inputEmail3"
                                           class="col-sm-3 control-label">{{ trans('project::view.LBL_ISSUE_SOURCE') }}</label>
                                    <div class="col-sm-6">
                                        <select name="filter[{{$tableCommonIssue}}.issue_source]"
                                                class="form-control filter-grid">
                                            <option value="">&nbsp;</option>
                                            @foreach ($listSourceType as $keyIssueSource => $valueIssueSource)
                                                <option value="{{ $keyIssueSource }}" {{ !is_null(Form::getFilterData('common_issue.issue_source')) && Form::getFilterData('common_issue.issue_source') == $keyIssueSource ? 'selected' : '' }}>{{ $valueIssueSource }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form id="form-export" action="{{ route('project::project.export.commonIssue') }}" method="post">
                        {!! csrf_field() !!}
                    </form>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12 bg-light text-right">
                                        @if($permissionEdit)
                                            <button href="#" class="btn add-common-issue btn-success"><i
                                                        class="fa fa-plus"></i> {{ trans('project::view.Add issue') }}
                                            </button>
                                        @endif
                                        <button class="btn btn-success btn-submit-action btn-export">{{ trans('project::view.Export') }}</button>
                                        <button class="btn btn-primary btn-reset-filter">
                                            <span>{{ trans('team::view.Reset filter') }} <i
                                                        class="fa fa-spin fa-refresh hidden"></i></span>
                                        </button>
                                        <button class="btn btn-primary btn-search-filter">
                                            <span>{{ trans('team::view.Search') }} <i
                                                        class="fa fa-spin fa-refresh hidden"></i></span>
                                        </button>

                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="edit-table table table-bordered table-condensed dataTable">
                                    <thead>
                                    <tr>
                                        <th class="align-center"
                                            style="width: 10px;">{{trans('project::view.ID risk')}}</th>
                                        <th>{{trans('project::view.Issue Type')}}</th>
                                        <th>{{trans('project::view.LBL_ISSUE_SOURCE')}}</th>
                                        <th>{{trans('project::view.LBL_ISSUE_DESCRIPTION')}}</th>
                                        <th>{{trans('project::view.LBL_CAUSE')}}</th>
                                        <th>{{trans('project::view.Action')}}</th>
                                        <th>{{trans('project::view.Create date')}}</th>
                                        <th>{{trans('project::view.Update date')}}</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if (count($collectionModel))
                                        <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                                        @foreach($collectionModel as $issue)
                                            <tr role="row" data-id="{{ $issue->id }}">
                                                <td>{{ $i++ }}</td>
                                                <td>{{ $typeIssue[$issue->issue_type] }}</td>
                                                <td>{{ $listSourceType[$issue->issue_source] }}</td>
                                                <td>{!! CoreView::nl2br($issue->issue_description) !!}</td>
                                                <td>{!! CoreView::nl2br($issue->cause) !!}</td>
                                                <td>{!! CoreView::nl2br($issue->action) !!}</td>
                                                <td>{{ substr($issue->created_at, 0, 10) }}</td>
                                                <td>{{ substr($issue->updated_at, 0, 10) }}</td>
                                                @if($permissionEdit)
                                                    <td style="text-align: center;">
                                                        <a class="btn-edit"
                                                           href="{{ route('project::commonIssue.detail', ['id' => $issue->id]) }}">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <button class="btn-delete btn-delete-common-issue"
                                                                data-id="{{ $issue->id }}">
                                                            <i class="fa fa-trash-o"></i>
                                                        </button>
                                                    </td>
                                                @else
                                                    <td>
                                                        <button class="btn-edit btn-show-detail"
                                                                data-id="{{ $issue->id }}">
                                                            <i class="fa fa-info-circle"></i>
                                                        </button>
                                                    </td>
                                                @endif
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
    @include('project::components.modal_delete_confirm_panel')
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('resource/css/resource.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css"
          rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css">
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/js/bootstrap-dialog.min.js') }}"></script>
    <script type="text/javascript">
        var token = '{!! csrf_token() !!}';
        var idItemDelete = '';
        var urlDeleteCommonIssue = '{{ route('project::commonIssue.delete') }}';
        var urlAddIssue = '{{ route('project::commonIssue.edit') }}';
        $(document).on('click', '.add-common-issue', function () {
            var $curElem = $(this);
            $('.add-common-issue').prop('disabled', true);
            $curElem.find('i.fa-plus').removeClass('fa-plus').addClass('fa-refresh').addClass('fa-spin');
            $.ajax({
                url: urlAddIssue.trim(),
                type: 'get',
                dataType: 'text',
                success: function (data) {
                    BootstrapDialog.show({
                        cssClass: 'risk-dialog',
                        message: $('<div></div>').html(data),
                        closable: false,
                        buttons: [{
                            id: 'btn-close',
                            icon: 'fa fa-close',
                            label: 'Close',
                            cssClass: 'btn-primary',
                            autospin: false,
                            action: function (dialogRef) {
                                dialogRef.close();
                            }
                        }, {
                            id: 'btn-save',
                            icon: 'glyphicon glyphicon-check',
                            label: 'Save',
                            cssClass: 'btn-primary',
                            autospin: false,
                            action: function (dialogRef) {
                                $('.form-common-issue-detail').submit();
                            }
                        }]
                    });
                },
                error: function () {
                    alert('ajax fail to fetch data');
                },
                complete: function () {
                    $curElem.find('i.fa-refresh').addClass('fa-plus').removeClass('fa-refresh').removeClass('fa-spin');
                    $('.add-common-issue').prop('disabled', false);
                }
            });
        });
        $(document).on('click', '.btn-delete-common-issue', function () {
            $('#modal-delete-confirm-panel').modal('show');
            idItemDelete = $(this).attr('data-id');
        });
        $(document).on('click', '#modal-delete-confirm-panel .btn-submit', function () {
            $('#modal-delete-confirm-panel').modal('hide');
            $('.modal-backdrop').remove();
            let issueId = idItemDelete;
            $.ajax({
                url: urlDeleteCommonIssue,
                type: 'GET',
                data: {
                    id: issueId
                },
                success: function (data) {
                    $("tr[data-id='" + issueId + "']").remove();
                },
                error: function () {
                    alert('ajax fail to fetch data');
                },
            });
        });
        $(document).on('click', '.btn-export', function () {
            $('#form-export').submit();
        });
        $(document).on('click', '.btn-show-detail', function () {
            var idElement = $(this).attr('data-id');
            $.ajax({
                url: urlAddIssue.trim(),
                type: 'get',
                data: {
                    isCreateNew: false,
                    id: idElement,
                },
                dataType: 'text',
                success: function (data) {
                    BootstrapDialog.show({
                        cssClass: 'risk-dialog',
                        message: $('<div></div>').html(data),
                        closable: false,
                        buttons: [{
                            id: 'btn-close',
                            icon: 'fa fa-close',
                            label: 'Close',
                            cssClass: 'btn-primary',
                            autospin: false,
                            action: function (dialogRef) {
                                dialogRef.close();
                            }
                        }]
                    });
                },
                error: function () {
                    alert('ajax fail to fetch data');
                },
            });
        });
    </script>
@endsection