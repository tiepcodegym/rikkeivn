<?php
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\CommonIssue;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Project\Model\Risk;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\TeamList;

$tableCommonIssue = CommonIssue::getTableName();
$limit = empty(Form::getFilterPagerData('limit', null)) ? $limited : Form::getFilterPagerData('limit', null);
$teamPath = Team::getTeamPath(false, true);

?>
@extends('layouts.default')
@section('title')
    {{ trans('project::view.LBL_PURCHASE_ORDER_LIST') }}
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
                                           class="col-sm-3 control-label">{{ trans('project::view.Team_in_charge') }}</label>
                                    <div class="col-sm-6">    
                                        <div class="input-box filter-multi-select multi-select-style btn-select-team">
                                            <div class="list-team-select-box" style="display: flex;">
                                                {{-- show team available --}}
                                                    <div class="input-box filter-multi-select multi-select-style btn-select-team">
                                                        <select name="filter[team_id][]" id="select-team-member" multiple
                                                                class="filter-grid multi-select-bst select-multi"
                                                                autocomplete="off">
                                                            {{-- show team available --}}
                                                                @foreach($teamsOptionAll as $option)
                                                                        <option value="{{ $option['value'] }}" class="checkbox-item" 
                                                                        {{ in_array($option['value'], !is_null(Form::getFilterData('team_id')) ? Form::getFilterData('team_id') : []) ? 'selected' : '' }}
                                                                        >
                                                                            {{ $option['label'] }}</option>
                                                                @endforeach
                                                            
                                                        </select>
                                                    </div>
                                                {{-- end show team available --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="inputEmail3"
                                           class="col-sm-3 control-label">{{ trans('project::view.LBL_ACCOUNT_MANAGER') }}</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="filter[account_manager]" class="filter-grid form-control"
                                        value="{{ !is_null(Form::getFilterData('account_manager')) ? Form::getFilterData('account_manager') : '' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="inputEmail3"
                                           class="col-sm-3 control-label">{{ trans('project::view.LBL_PURCHASE_ORDER_NAME') }}</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="filter[po_title]" class="filter-grid form-control"
                                        value="{{ !is_null(Form::getFilterData('po_title')) ? Form::getFilterData('po_title') : '' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="inputEmail3"
                                           class="col-sm-3 control-label">{{ trans('project::view.LBL_CUSTOMER_NAME') }}</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="filter[account_name]" class="filter-grid form-control"
                                        value="{{ !is_null(Form::getFilterData('account_name')) ? Form::getFilterData('account_name') : '' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="inputEmail3"
                                           class="col-sm-3 control-label">{{ trans('project::view.LBL_START_MONTH') }}</label>
                                    <div class="col-sm-6">
                                        <input type="text" id="activity_month_from_overview" name="filter[month_from]"
                                            class="form-control form-inline filter-grid month-picker-overview date-picker maxw-165"
                                            value="{{ !is_null(Form::getFilterData('month_from')) ? Form::getFilterData('month_from') : '' }}" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="inputEmail3"
                                           class="col-sm-3 control-label">{{ trans('project::view.LBL_END_MONTH') }}</label>
                                    <div class="col-sm-6">
                                        <input type="text" id="activity_month_to" name="filter[month_to]"
                                            class="form-control form-inline filter-grid month-picker-overview date-picker maxw-165"
                                            value="{{ !is_null(Form::getFilterData('month_to')) ? Form::getFilterData('month_to') : '' }}" autocomplete="off">
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
                                        <button class="btn btn-primary btn-search-filter">
                                            <span>{{ trans('team::view.Search') }} <i
                                                        class="fa fa-spin fa-refresh hidden"></i></span>
                                        </button>
                                        <button class="btn btn-primary btn-reset-filter">
                                            <span>{{ trans('team::view.Reset filter') }} <i
                                                        class="fa fa-spin fa-refresh hidden"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed dataTable" style="display: none; z-index: 1090; position: relative;" id="managetime_table_fixed">
                                    <thead style="background-color: #d9edf7; height: 50px;">
                                    <tr>
                                        <th class="align-center" style="width: 2%;" data-order="id" data-dir="{{ Config::getDirOrder('id', $urlFilter) }}">{{trans('project::view.ID risk')}}</th>
                                        <th class="align-center" style="width: 20%;">{{trans('project::view.LBL_PURCHASE_ORDER_ID')}}</th>
                                        <th class="sorting {{ Config::getDirClass('name', $urlFilter) }}" style="width: 20%;" data-order="name" data-dir="{{ Config::getDirOrder('name', $urlFilter) }}" >{{trans('project::view.LBL_PURCHASE_ORDER_NAME')}}</th>
                                        <th class="sorting {{ Config::getDirClass('division', $urlFilter) }}" style="width: 7%;" data-order="division" data-dir="{{ Config::getDirOrder('division', $urlFilter) }}" >{{trans('project::view.Division')}}</th>
                                        <th class="sorting {{ Config::getDirClass('customer_name', $urlFilter) }}" style="width: 10%;" data-order="customer_name" data-dir="{{ Config::getDirOrder('customer_name', $urlFilter) }}" >{{trans('project::view.LBL_CUSTOMER_NAME')}}</th>
                                        <th class="align-center" style="width: 15%;" >{{trans('project::view.Project')}}</th>
                                        <th class="sorting {{ Config::getDirClass('account_manager', $urlFilter) }}" style="width: 10%;" data-order="account_manager" data-dir="{{ Config::getDirOrder('account_manager', $urlFilter) }}" >{{trans('project::view.LBL_ACCOUNT_MANAGER')}}</th>
                                        <th class="sorting {{ Config::getDirClass('start_date', $urlFilter) }}" style="width: 7%;" data-order="start_date" data-dir="{{ Config::getDirOrder('start_date', $urlFilter) }}" >{{trans('project::view.Start Date')}}</th>
                                        <th class="sorting {{ Config::getDirClass('end_date', $urlFilter) }}" style="width: 7%;" data-order="end_date" data-dir="{{ Config::getDirOrder('end_date', $urlFilter) }}" >{{trans('project::view.End Date')}}</th>
                                    </tr>
                                    </thead>
                                </table>
                                <table class="edit-table table table-bordered table-condensed dataTable">
                                    <thead style="background-color: #d9edf7; height: 50px;">
                                    <tr>
                                        <th class="align-center" style="width: 2%;" data-order="id" data-dir="{{ Config::getDirOrder('id', $urlFilter) }}">{{trans('project::view.ID risk')}}</th>
                                        <th class="align-center" style="width: 20%;">{{trans('project::view.LBL_PURCHASE_ORDER_ID')}}</th>
                                        <th class="sorting {{ Config::getDirClass('name', $urlFilter) }}" style="width: 20%;" data-order="name" data-dir="{{ Config::getDirOrder('name', $urlFilter) }}" >{{trans('project::view.LBL_PURCHASE_ORDER_NAME')}}</th>
                                        <th class="sorting {{ Config::getDirClass('division', $urlFilter) }}" style="width: 7%;" data-order="division" data-dir="{{ Config::getDirOrder('division', $urlFilter) }}" >{{trans('project::view.Division')}}</th>
                                        <th class="sorting {{ Config::getDirClass('customer_name', $urlFilter) }}" style="width: 10%;" data-order="customer_name" data-dir="{{ Config::getDirOrder('customer_name', $urlFilter) }}" >{{trans('project::view.LBL_CUSTOMER_NAME')}}</th>
                                        <th class="align-center" style="width: 15%;" >{{trans('project::view.Project')}}</th>
                                        <th class="sorting {{ Config::getDirClass('account_manager', $urlFilter) }}" style="width: 10%;" data-order="account_manager" data-dir="{{ Config::getDirOrder('account_manager', $urlFilter) }}" >{{trans('project::view.LBL_ACCOUNT_MANAGER')}}</th>
                                        <th class="sorting {{ Config::getDirClass('start_date', $urlFilter) }}" style="width: 7%;" data-order="start_date" data-dir="{{ Config::getDirOrder('start_date', $urlFilter) }}" >{{trans('project::view.Start Date')}}</th>
                                        <th class="sorting {{ Config::getDirClass('end_date', $urlFilter) }}" style="width: 7%;" data-order="end_date" data-dir="{{ Config::getDirOrder('end_date', $urlFilter) }}" >{{trans('project::view.End Date')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody id="position_start_header_fixed">
                                    @if ($collectionModel && count($collectionModel))
                                        @foreach($collectionModel as $key => $po)
                                            <tr role="row" data-id="{{ $po['id'] }}">
                                                <td class="align-center">{{ ++$key + ($currentPage - 1) * $limited }}</td>
                                                <td class="align-center">{{ $po['id'] }}</td>
                                                <td>{{ $po['name'] }}</td>
                                                <td>{{ $divisionNameList[$po['division']] }}</td>
                                                <td>{{ $po['customer_name'] }}</td>
                                                <td style="max-height: 60px; overflow: auto; position: relative; display: block;">
                                                    @if (isset($po['project']))
                                                        @foreach ($po['project'] as $project)
                                                            <a class="tag label label-info" href="{{ route('project::project.edit' , ['id' => $project['project_id']]) }}">{{ $project['project_name'] }}</a>
                                                        @endforeach
                                                    @endif
                                                    
                                                </td>
                                                <td>{{ $po['account_manager'] }}</td>
                                                <td>{{ $po['start_date'] }}</td>
                                                <td>{{ $po['end_date'] }}</td>
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
                            {{-- paging --}}
                            <div class="box-body">
                                <div class="grid-pager">
                                    <div class="data-pager-info grid-pager-box" role="status" aria-live="polite">
                                        <span>{!! trans('project::view.Total :itemTotal entries / :pagerTotal page', [
                                            'itemTotal' => $totalRecord,
                                            'pagerTotal' => $totalPage,
                                            ]) !!}</span>
                                    </div>
                                    <div class="grid-pager-box-right">
                                        <div class="dataTables_length grid-pager-box">
                                            <label>{{ trans('project::view.Show') }}
                                                <select name="limit" class="form-control input-sm" autocomplete="off">
                                                    @foreach(Config::toOptionLimit() as $option)
                                                        <option value="{{ Config::urlParams(['limit' => $option['value']]) }}"<?php 
                                                            if ($option['value'] == $limit): ?> selected<?php endif; ?>
                                                        data-value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                        </div>

                                        <div class="dataTables_paginate paging_simple_numbers grid-pager-box pagination-wrapper">
                                            <ul class="pagination">
                                                <li class="paginate_button first-page<?php if($currentPage == 1): ?> disabled<?php endif; ?>">
                                                    <a href="<?php if($currentPage != 1): ?>{{ Config::urlParams(['page' => 1]) }}<?php else: ?>#<?php endif; ?>" data-page="1">
                                                        <i class="fa fa-angle-double-left"></i>
                                                    </a>
                                                </li>
                                                <li class="paginate_button previous<?php if($currentPage == 1): ?> disabled<?php endif; ?>">
                                                    <a href="<?php 
                                                        if($currentPage != 1): ?>{{ Config::urlParams(['page' => $currentPage-1]) }}<?php 
                                                        else: ?>#<?php endif; ?>" data-page="{{ $currentPage-1 }}">
                                                        <i class="fa fa-arrow-left"></i>
                                                    </a>
                                                </li>
                                                <li class="paginate_button">
                                                    <div action="{{ Config::urlParams(['page' => null]) }}" method="get" class="form-pager">
                                                        <input class="input-text form-control" name="page" value="{{ $currentPage }}" />
                                                    </div>
                                                </li>
                                                <li class="paginate_button next {{ (($currentPage) == $totalPage) ? 'disabled' : '' }}">
                                                    <a href="" data-page="{{ $currentPage + 1 }}">
                                                        <i class="fa fa-arrow-right"></i>
                                                    </a>
                                                </li>
                                                <li class="paginate_button lastpage-page">
                                                    <a href="" data-page="{{ $totalPage }}">
                                                        <i class="fa fa-angle-double-right"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                  
                                    </div>
                                </div>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css">
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/js/bootstrap-dialog.min.js') }}"></script>
    <script type="text/javascript">
        var teamPath = {!! json_encode($teamPath) !!};
        var token = '{!! csrf_token() !!}';
        var idItemDelete = '';
        var urlDeleteCommonIssue = '{{ route('project::commonIssue.delete') }}';
        var urlAddIssue = '{{ route('project::commonIssue.edit') }}';
        $(function () {
            $('.list_export_cols').sortable({
                stop: function (event, ui) {
                    $('.list_export_cols li input').each(function (index) {
                        $(this).attr('name', 'columns['+ index +']');
                    });
                },
            });
            $('#form_export_relationship button[type="submit"]').click(function () {
                var btn = $(this);
                setTimeout(function () {
                    btn.prop('disabled', false);
                    btn.closest('.modal').modal('hide');
                }, 500);
            });
            $('#work').click(function(){
                window.location.href = "{{ route('team::team.member.index') }}";
            });
            $('#leave').click(function(){
                window.location.href = "{{ route('team::team.member.index', ['statusWork' => 'leave']) }}";
            });
            $('#all').click(function(){
                window.location.href = "{{ route('team::team.member.index', ['statusWork' => 'all']) }}";
            });
            selectSearchReload();
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
            changeLabelSelected();
            $('.multiselect-selected-text').css('float', 'none');
        });

        jQuery(document).ready(function () {
            $('input.date-picker').datepicker({
                format: 'yyyy-mm',
                viewMode: "months",
                minViewMode: "months",
                // endDate: '0y',
                // autoclose: true,
            });

            var fixTop = $('#position_start_header_fixed').offset().top;
            $(window).scroll(function() {
                var scrollTop = $(window).scrollTop();
                if (scrollTop > fixTop) {
                    $('#managetime_table_fixed').css('top', scrollTop - $('.table-responsive').offset().top + 45);
                    $('#managetime_table_fixed').show();
                } else {
                    $('#managetime_table_fixed').hide();
                }
            });
        });
        $(document).on('click', '.btn-export', function () {
            $('#form-export').submit();
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
            changeLabelSelected();
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