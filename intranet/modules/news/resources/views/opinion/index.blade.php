<?php
use Rikkei\Core\View\CoreUrl;

use Rikkei\Team\View\Config as Config;
use Rikkei\Core\View\View;
use Rikkei\Core\View\Form;
use Carbon\Carbon;

$filterStart = Form::getFilterData('opinions.created_at', 'from');
$filterEnd = Form::getFilterData('opinions.created_at', 'to');
if (!$filterStart) {
    $filterStart = Carbon::now()->format('Y-m-01');
}
if (!$filterEnd) {
    $filterEnd = Carbon::now()->format('Y-m-t');
}

?>

@extends('layouts.default')

@section('title')
    {{ trans('news::view.List opinion') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css"/>
@endsection

@section('content')
    @if(Session::has('flash_success'))
        <div class="alert alert-success alert-dismissible fade in alert-hiden" role="alert">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
            {{ Session::get('flash_success') }}
        </div>
    @endif
    @if(Session::has('flash_error'))
        <div class="alert alert-danger alert-dismissible fade in alert-hiden" role="alert">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
            {{ Session::get('flash_error') }}
        </div>
    @endif
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                   <div class="row">
                       <div class="col-md-8">
                           <div class="row">
                               <div class="team-ot-select-box col-md-3">
                                   <label>{{trans('manage_time::view.From date')}}</label>
                                   <div class="input-box">
                                       <input type="text"
                                              id="fromDate"
                                              class='form-control date-picker  form-inline'
                                              value="{{ \Carbon\Carbon::parse($filterStart)->format('d/m/Y') }}"
                                              />
                                       <input type="hidden" class="filter-grid" name="filter[opinions.created_at][from]" id="altFromDate" value="{{ $filterStart }}">

                                   </div>
                               </div>

                               <div class="team-ot-select-box col-md-3">
                                   <label>{{trans('manage_time::view.End date')}}</label>
                                   <div class="input-box">
                                       <input type="text"
                                              id="toDate"
                                              class='form-control date-picker  form-inline'

                                              value="{{ \Carbon\Carbon::parse($filterEnd)->format('d/m/Y') }}"
                                              />
                                       <input type="hidden" class="filter-grid"  name="filter[opinions.created_at][to]" id="altToDate" value="{{ $filterEnd }}">

                                   </div>
                               </div>
                           </div>
                       </div>
                       <div class="col-md-4 text-right">
                           @include('team::include.filter')
                       </div>
                   </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                        <tr>
                            <th class="col-id width-10" style="width: 20px;">{{ trans('news::view.No.') }}</th>
                            <th style="width: 200px" class="sorting {{ Config::getDirClass('employee_name') }}" data-order="employee_name" data-dir="{{ Config::getDirOrder('employee_name') }}" >{{trans('news::view.Employee Name')}}</th>
                            <th style="width: 550px" class="sorting {{ Config::getDirClass('content') }} col-content" data-order="content" data-dir="{{ Config::getDirOrder('content') }}">{{ trans('news::view.Content') }}</th>
                            <th class="sorting {{ Config::getDirClass('created_at') }} col-created" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('news::view.Created At') }}</th>
                            <th class="sorting {{ Config::getDirClass('status') }} col-status" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('news::view.Status') }}</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div>
                                            <select class="form-control filter-grid select-search-employee" name="filter[number][employee_id]" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">{!! !isset($filterEmployeeId) ? "<option value='{$filterEmployeeId}' selected>{$filterEmployeeName}</option>" : ''  !!}</select>

                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[content]" value="{{ Form::getFilterData("content") }}" placeholder="{{ trans('news::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select class="form-control select-grid filter-grid select-search" name="filter[number][status]">
                                            <option value="">{{trans('news::view.All')}}</option>
                                            <?php $filterStatus = Form::getFilterData('number', 'status');?>
                                            @foreach($listStatus as $key => $value)
                                                <option value="{{ $key }}" {{ $filterStatus == $key ? 'selected' : '' }}>{{ trans($value) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td data-toggle="tooltip" data-container="body" title="{{$item->employee_email}}">{{ $item->employee_name }}</td>
                                    <td>{{ str_limit($item->content, 150) }} </td>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                                    <td > <span class="{{$listStatusLabel[$item->status]}}">{{ trans($listStatus[$item->status]) }}</span></td>
                                    <td>
                                        <a class="btn btn-success" target="_blank"
                                           href="{{ route('news::opinions.edit', ['id' => $item->id]) }}" >
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <form action="{{ route('news::opinions.delete', ['id' => $item->id]) }}" method="post" class="form-inline">
                                            {!! csrf_field() !!}
                                            {!! method_field('delete') !!}
                                            <input type="hidden" name="id" value="{{ $item->id }}" />
                                            <button href="" class="btn-delete delete-confirm" title="{{ trans('manage_time::view.Delete') }}">
                                                <span><i class="fa fa-trash"></i></span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('news::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="box-body">
                    @include('team::include.pager')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.13/moment-timezone-with-data.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            selectSearchReload();
        });


        $.fn.selectSearchAjax = function(options) {
            var defaults = {
                url: "",
                pages: 1,
                delay: 300,
                placeholder: "Search ...",
                multiple: false,
                allowClear: true,
                allowHtml: true,
                tags: false,
                minimumInputLength: 2,
                maximumSelectionLength: 1,
                initSelection : function (element, callback) {
                    var id = '';
                    var text = '';
                    var data = [];
                    data.push({id: id, text: text});
                    callback(data);
                },
            };
            var settings = $.extend( {}, defaults, options );
            var search = this;

            search.init = function(selector) {
                $(selector).select2({
                    multiple: settings.multiple,
                    closeOnSelect : settings.closeOnSelect,
                    allowClear: settings.allowClear,
                    allowHtml: settings.allowHtml,
                    tags: settings.tags,
                    minimumInputLength: settings.minimumInputLength,
                    maximumSelectionLength: settings.minimumInputLength,
                    ajax: {
                        url: settings.url,
                        dataType: 'json',
                        delay: settings.delay,
                        data: function (params) {
                            return {
                                q: params.term,
                                {{--employee_branch: "{{ $employee_branch['branch'] }}",--}}
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            console.log(data.items);
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    },
                    placeholder: settings.placeholder,
                    templateResult: search.formatRepo,
                    templateSelection: search.formatRepoSelection,
                    initSelection : settings.initSelection,
                });
            }

            // temple
            search.formatRepo = function(repo) {
                if (repo.loading) {
                    return repo.text;
                }

                return markup  = repo.text;
            }

            // temple
            search.formatRepoSelection = function(repo) {
                return repo.text;
            }

            // Event select
            search.on("select2:select", function (e) {
                // remove all sesssion storage
                sessionStorage.clear();

                // assign session storage
                var id = $("#employee-assigned").val();
                var text = $("#employee-assigned").text();
                if (text != null) {
                    sessionStorage.setItem('employee-assigned-' + id, text);
                }

                // Trigger on close select2
                $('.btn-search-filter').trigger('click');
            })

            // init
            var selectors = $(this);
            return $.each(selectors, function(index, selector){
                search.init(selector);
            });
        };
        $('.select-search-employee').selectSearchAjax({
            url: $('.select-search-employee').data('remote-url'),
            initSelection: function (element, callback) {
                var id = '{{$filterEmployeeId}}';
                var text = '{{$filterEmployeeName}}';
                var data = [];
                data.push({id: id, text: text});
                callback(data);
            }
        });

        function reFormatDateFrom(dateDisplay,) {
            dateDisplay = moment(dateDisplay, 'DD/MM/YYYY');
            return moment(dateDisplay).format('YYYY-MM-DD');
        }

        function reFormatDateTo(dateDisplay) {
            dateDisplay = moment(dateDisplay, 'DD/MM/YYYY');
            return moment(dateDisplay).format('YYYY-MM-DD 23:59:59');
        }


        $('#fromDate').datetimepicker({
            format: 'DD/MM/Y',
            showClear: true
        });

        $('#toDate').datetimepicker({
            format: 'DD/MM/Y',
            showClear: true,
            useCurrent: false
        });

        $("#fromDate").on("dp.change", function (e) {
            $('#toDate').data("DateTimePicker").minDate(e.date);
            $('#altFromDate').val(reFormatDateFrom(e.date));
        });
        $("#toDate").on("dp.change", function (e) {
            $('#fromDate').data("DateTimePicker").maxDate(e.date);
            $('#altToDate').val(reFormatDateTo(e.date));
        });
    </script>
@endsection

