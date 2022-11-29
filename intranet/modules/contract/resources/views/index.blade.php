<?php

use Rikkei\Contract\View\Config;
use Rikkei\Core\View\View;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Core\View\Form as FilterForm;
use Carbon\Carbon;

$urlFilter = request()->url();
$domainTrans = 'contract';

$filterStartAt = FilterForm::getFilterData('except', "start_at_expire", $urlFilter);
$filterEndAt = FilterForm::getFilterData('except', "end_at_expire", $urlFilter);
if ($filterStartAt) {
    $filterStartAt = Carbon::parse($filterStartAt)->toDateString();
}
if ($filterEndAt) {
    $filterEndAt = Carbon::parse($filterEndAt)->toDateString();
}
?>
@extends('layouts.default')

@section('title')
    {{ trans('contract::vi.Contract list') }}
@endsection

@section('css')

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css"/>

@endsection

@section('content')
    @include('contract::message-alert')
    <style>
        .bootstrap-datetimepicker-widget {
            z-index: 999999;
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info filter-wrapper" data-url="{{ URL::route('contract::manage.contract.index',['tab'=>$currentTab])}}">
                <div class="box-body filter-mobile-left">
                    <div class="team-select-box">
                    {{-- show team available --}}
                    @if ($currentTab === 'about-to-expire')
                        <!--<label for="select-team-member">{{ trans('contract::vi.Choose date') }}</label>-->
                            <div class="input-box">
                                <div class='col-md-2'>
                                    <label for="choose-time-filter">{{ trans('contract::vi.start at') }}</label>
                                    <div class="input-group datetime-picker-start-at col-md-12">
                                        <input
                                                type="text"
                                                class="form-control choose-time-filter-start-at filter-grid"
                                                name="filter[except][start_at_expire]"
                                                id="filter-date-start-at"
                                                autocomplete="off"
                                        >
                                        <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                    </div>
                                </div>
                                <div class='col-md-2'>
                                    <label for="choose-time-filter">{{ trans('contract::vi.end at') }}</label>
                                    <div class="input-group datetime-picker-end-at col-md-12">
                                        <input
                                                type="text"
                                                class="form-control choose-time-filter-end-at filter-grid"
                                                name="filter[except][end_at_expire]"
                                                id="filter-date-end-at"
                                                autocomplete="off"
                                        >
                                        <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                    </div>
                                </div>
                                <div class='col-md-2'>
                                    <label for="choose-time-filter">&nbsp;</label>
                                    <div class="input-group datetime-picker col-md-12">
                                        <button class="btn btn-primary btn-search-filter">
                                            <span>{{ trans($domainTrans . '::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        {{-- end show team available --}}
                    </div>
                    <div class="text-right member-group-btn">
                        <a class="btn btn-success" type="button" href='{{URL::route('contract::manage.contract.create')}}'>{{ trans('contract::vi.create') }}</a>
                        <button type="button"
                                class="btn btn-success import-contract"
                                onclick="fc_show_model_upload_file()"
                                id="modal_contract_import_excel"
                                data-url="{!! URL::route('contract::manage.contract.import-excel') !!}">
                            <i class="fa fa-upload"></i>
                            {!! trans('contract::vi.Import contract') !!}
                        </button>
                        @if ($currentTab !== 'none')
                            <button type="button"
                                    class="btn btn-success btn-export-contract"
                                    data-url="{{ route('contract::manage.contract.export', ['tab' => $currentTab]) }}">
                                <i class="fa fa-download"></i>
                                {!! trans('contract::view.export') !!}
                            </button>
                            {{ Form::open(['route' => ['contract::manage.contract.export', $currentTab], 'method' => 'POST', 'id' => 'export-contract', 'class' => 'no-disabled hide']) }}
                                <input type="hidden" name="filter[url_filter]" value="{{ $urlFilter }}">
                                <div class="fildata"></div>
                            {{ Form::close() }}
                        @endif

                        <button type="button"
                                class="btn btn-primary"
                                onclick="fc_import_histories()"
                                id="modal_import_history"
                                data-url="{!! URL::route('contract::manage.contract.histories') !!}">
                            {!! trans('contract::vi.Import history') !!}
                        </button>
                        @if ($currentTab !=='about-to-expire')
                            <button class="btn btn-primary btn-search-filter">
                                <span>{{ trans($domainTrans . '::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                            </button>
                        @endif
                        <button class="btn btn-primary btn-reset-filter">
                            <span>{{ trans($domainTrans . '::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                        </button>

                    </div>
                </div>
                <div class="tab-content">
                    <ul class="nav nav-tabs">
                        <li <?php if (!in_array($currentTab, ['about-to-expire', 'none'])) echo ' class="active"'; ?>>
                            <a href="{{ URL::route('contract::manage.contract.index',['tab'=>'all'])}}">
                                {{ trans('contract::vi.Contract list') }}
                            </a>
                        </li>
                        <li <?php if ($currentTab === 'about-to-expire') echo ' class="active"'; ?>>
                            <a href="{{ URL::route('contract::manage.contract.index',['tab'=>'about-to-expire']) }}">
                                {{ trans('contract::vi.The contract list is about to expire') }}
                            </a>
                        </li>
                        <li <?php if ($currentTab === 'none') echo ' class="active"'; ?>>
                            <a href="{{ URL::route('contract::manage.contract.index',['tab'=>'none'])}}">
                                {{ trans('contract::vi.No contract yet') }}
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="home" class="tab-pane fade in active">
                            @include('contract::tab')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- set up the modal to start hidden and fade in and out -->
    <div id="model-delete-confirm-contract" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- dialog body -->
                <div class="modal-body">
                    <h3></h3>
                </div>
                <!-- dialog buttons -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-ok">OK</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- HTML to write -->

    <!-- set up the modal to start hidden and fade in and out -->
    <div id="model-import-excel" class="modal fade" data-backdrop="static" data-keyboard="false">
        <form name="frmMain" id="frmMain" enctype="multipart/form-data" method="POST" action="{{route('contract::manage.contract.import-excel')}}">
            {!! csrf_field() !!}
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- dialog body -->
                    <div class="modal-body">

                        <h4>{{trans('contract::view.Select file to import')}} <em style="color:red">*</em></span> </h4>
                        <input type="file" name="fileToUpload" id="fileToUpload" accept=".xlsx, .xls">
                        <label style="display: none;color: red" id="error-import-excel">{{trans('contract::message.File import is not null')}}</label>
                        <br/>
                        {!!trans('contract::view.help-import-excel')!!}
                    </div>
                    <div class="col-md-12">
                        <h4>
                            <a href="{{route('contract::manage.downloadFormatFile')}}">{{ trans('contract::view.Format excel file') }}
                                <i class="fa fa-download"></i></a></h4>
                    </div>
                    <!-- dialog buttons -->
                    <div class="modal-footer">
                        <button type="submit" style="display: none" id="btn_submit_import_excel"></button>
                        <button type="button" onclick="fc_summit_import()" class="btn btn-primary btn-ok">Upload
                        </button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div id="modal-import-history" class="modal fade" data-backdrop="static" data-keyboard="false"
         url-action="{{route('contract::manage.contract.histories')}}">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- dialog body -->
                <div class="modal-body">
                    <h4>{{trans('contract::view.Danh_sach_file_import')}}</h4>
                    <div id="box-link-download">

                    </div>
                </div>
                <!-- dialog buttons -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>

    <script>

        function synchronize(id) {
            $('#model-delete-confirm-contract h3').html('{!!trans("contract::message.Are you sure synchronize item?")!!}');
            $('#model-delete-confirm-contract').modal('show');
            $('#model-delete-confirm-contract .btn-ok').click(function () {
                $('#model-delete-confirm-contract').modal('hide');
                $.ajax({
                    method: "POST",
                    url: "{{route('contract::manage.contract.synchronize')}}",
                    data: {
                        '_token': "{{csrf_token()}}",
                        'id': id
                    },
                    success: function (data) {
                        var msg = data.message || 'not found message';
                        if (data.success) {
                            RKExternal.notify(msg, true);
                            window.location.reload();
                        } else {
                            RKExternal.notify(msg, false);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.responseJSON.error && jqXHR.responseJSON.error == 1) {
                            RKExternal.notify(jqXHR.responseJSON.message, false);
                        } else {
                            RKExternal.notify('System error', false);
                        }

                    }
                });
            });
        }

        function fc_import_histories() {
            var modalElement = $('#modal-import-history');
            $.ajax({
                method: "GET",
                url: modalElement.attr('url-action'),
                data: {
                    '_token': "{{csrf_token()}}",
                },
                success: function (data) {
                    modalElement.find('#box-link-download').html(data);
                    modalElement.modal();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    modalElement.find('#box-link-download').html('');
                }
            });

        }

        function fc_show_model_upload_file() {
            $('#model-import-excel').modal();
            $('#frmMain #fileToUpload').val('');

        }

        function fc_summit_import() {
            if ($('#fileToUpload').val().trim() == '') {
                $('#error-import-excel').show();
                return false;
            }
            $('#error-import-excel').hide();
            $('#model-import-excel').modal('hide');
            $('#btn_submit_import_excel').trigger('click');
        }

        (function ($) {
            $('.datetime-picker-start-at').datetimepicker({
                format: 'DD-MM-YYYY',
                showClose: true,
                minDate: moment().format('Y-MM-DD'),
                date: "{{$filterStartAt}}"
            });
            $('.datetime-picker-end-at').datetimepicker({
                format: 'DD-MM-YYYY',
                showClose: true,
                minDate: moment().format('Y-MM-DD'),
                date: "{{$filterEndAt}}"
            });
            $('.delete-contract-confirm').click(function (e) {
                var urlDelete = $(this).data('url-ajax') || '';
                if (urlDelete.trim() == '')
                    return true;
                $('#model-delete-confirm-contract h3').html('{{trans("contract::message.Are you sure delete item?")}}');
                $('#model-delete-confirm-contract').modal('show');
                $('#model-delete-confirm-contract .btn-ok').click(function () {
                    $('#model-delete-confirm-contract').modal('hide');
                    $.ajax({
                        method: "DELETE",
                        url: urlDelete,
                        data: {
                            '_token': "{{csrf_token()}}",
                        },
                        success: function (data) {
                            var msg = data.message || 'not found message';
                            if (data.success) {
                                RKExternal.notify(msg, true);
                                window.location.reload();
                            } else {
                                RKExternal.notify(msg, false);
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            RKExternal.notify('{{trans("contract::message.Delete contract failed.")}}', false);
                        }
                    });
                });
            });
        })(jQuery);

        $('.btn-export-contract').on('click', function(e) {
            $('#export-contract .fildata').html('');
            $('.filter-grid').each(function(i,k) {
                var element = $(this).clone();
                $('#export-contract .fildata').append(element);
            });

            $('#export-contract').submit();
        })

    </script>
@endsection
