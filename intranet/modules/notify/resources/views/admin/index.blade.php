@extends('layouts.default')

@section('title')

@endsection
<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;use Rikkei\HomeMessage\Model\HomeMessage;use Rikkei\HomeMessage\Model\HomeMessageGroup;
use Rikkei\Team\View\Config as TeamConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Rikkei\Resource\View\getOptions;
use Illuminate\Support\Facades\URL;
$collectionModel = $collection;
$perPage = $collectionModel->perPage();
$perPage = $perPage ? (int)$perPage : 20;
$currentPage = $collectionModel->currentPage();
$currentPage = $currentPage ? (int)$currentPage : 1;
$notifyMobileTbl = \Rikkei\Notify\Model\NotifyMobile::getTableName();
$notifyMobileTblAvailableAt = CoreForm::getFilterData("{$notifyMobileTbl}.available_at");
$notifyMobileTblStatus = CoreForm::getFilterData("{$notifyMobileTbl}.status");

?>

@section('css')
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}"/>
    <style>
        .content-header {
            display: none;
        }
        .table-responsive .table{
            min-width: 900px;
        }
        .table-wrapper .table .col-title{
            width: 250px;
        }
        .table-wrapper .table .col-available-at{
            width: 100px;
        }
        .table-wrapper .table .col-status{
            width: 80px;
        }
        .table-wrapper .table thead tr th:first-child{
            width: 50px;
        }
        .table-wrapper .table tr th:last-child,
        .table-wrapper .table tr td:last-child{
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="row">
            <!-- Menu left -->
            <div class="col-lg-2 col-md-3">
                @include('notify::admin.include.menu_left')
            </div>
            <!-- /.col -->
            <div class="col-lg-10 col-md-9 content-ot">
                <div class="box box-primary">
                    <div class="box-header filter-mobile-left">
                        <div class="action" style="margin: 10px;">
                            <a class="btn btn-primary ot-margin-bottom-5" style="float: left" href="{{ Url::route('notify::admin.notify.create') }}">
                                <span>{{ trans('notify::view.notify_create') }}</span>
                            </a>
                        </div>
                        @include('notify::admin.include.filter')
                    </div>
                    <div class="table-responsive table-wrapper">
                        <table class="table dataTable table-striped table-grid-data table-responsive table-hover table-bordered list-ot-table">
                            <thead class="list-head">
                            <tr>
                                <th>{{ trans('notify::view.order_number') }}</th>
                                <th class="col-width-75 sorting col-title {{ TeamConfig::getDirClass('title') }}"
                                    data-order="title" data-dir="{{ TeamConfig::getDirOrder('title') }}">
                                    {{trans('notify::view.title_list')}}
                                </th>
                                <th class="col-width-75 sorting {{ TeamConfig::getDirClass('content') }}"
                                    data-order="content" data-dir="{{ TeamConfig::getDirOrder('content') }}">
                                    {{trans('notify::view.content_list')}}
                                </th>
                                <th class="col-width-75 sorting col-available-at {{ TeamConfig::getDirClass('available_at') }}"
                                    data-order="available_at" data-dir="{{ TeamConfig::getDirOrder('available_at') }}">
                                    {{trans('notify::view.available_list')}}
                                </th>
                                <th class="col-width-75 sorting col-status {{ TeamConfig::getDirClass('status') }}"
                                    data-order="status" data-dir="{{ TeamConfig::getDirOrder('status') }}">
                                    {{trans('notify::view.status_list')}}
                                </th>
                                <th></th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[{{ $notifyMobileTbl }}.title]"
                                                   value='{{ CoreForm::getFilterData("{$notifyMobileTbl}.title") }}'
                                                   placeholder="{{ trans('team::view.Search') }}..."
                                                   class="filter-grid form-control" autocomplete="off"/>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[{{ $notifyMobileTbl }}.content]"
                                                   value='{{ CoreForm::getFilterData("{$notifyMobileTbl}.content") }}'
                                                   placeholder="{{ trans('team::view.Search') }}..."
                                                   class="filter-grid form-control" autocomplete="off"/>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[{{ $notifyMobileTbl }}.available_at]"
                                                   value='{{ isset($notifyMobileTblAvailableAt) ? \Carbon\Carbon::parse($notifyMobileTblAvailableAt)->format('Y-m-d') : null }}'
                                                   placeholder="{{ trans('team::view.Search') }}..."
                                                   class="filter-grid form-control filter-date" autocomplete="off"/>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select name="filter[{{$notifyMobileTbl}}.status]"
                                                    class="form-control select-grid filter-grid select-search"
                                                    autocomplete="off" style="width: 100%;">
                                                <option value="">&nbsp;</option>
                                                @foreach($allStatus as $typeK => $typeV)
                                                    <option {{ isset($notifyMobileTblStatus) && CoreForm::getFilterData("{$notifyMobileTbl}.status") == $typeK ? 'selected' : null }} value="{{$typeK}}">{{$typeV}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($collection->count())
                                @for($i=0; $i< $collection->count();$i++)
                                    <tr>
                                        <td>{{$perPage * ($currentPage -1) + $i + 1}}</td>
                                        <td>{{$collection[$i]->title}}</td>
                                        <td style="white-space: pre-line">
                                            {{ $collection[$i]->content }}
                                        </td>
                                        <td>{{Carbon::parse($collection[$i]->available_at)->format('Y-m-d H:i')}}</td>
                                        <td>
                                            @if($collection[$i]->status == getOptions::RESULT_DEFAULT)
                                                {{ trans('notify::view.not_send') }}
                                            @elseif($collection[$i]->status == getOptions::STATUS_INPROGRESS)
                                                {{ trans('notify::view.sent') }}
                                            @else
                                                {{ trans('notify::view.failure') }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($collection[$i]->status == getOptions::RESULT_DEFAULT && $collection[$i]->created_by == Auth::id())
                                                <button class="btn btn-success"
                                                        onclick="singleRecord('{{route('notify::admin.notify.edit',['id'=>$collection[$i]->id])}}')">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger"
                                                        onclick="deleteRecord('{{route('notify::admin.notify.destroy',['id'=>$collection[$i]->id])}}')">
                                                    <i class="fa fa-remove"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endfor
                            @else
                                <tr>
                                    <td colspan="13">
                                        <h2 class="no-result-grid text-center">{{ trans('notify::view.No results found') }}</h2>
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer no-padding">
                        <div class="mailbox-controls">
                            @include('HomeMessage::include.pager')
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.col -->
    </div>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script>
        function singleRecord(url) {
            window.location.href = url;
        }

        function deleteRecord(url) {
            if (!confirm('{!! trans('HomeMessage::message.Are you sure delete item selected?') !!}')) {
                return false;
            }
            $.ajax({
                url: url,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function (resp) {
                    window.location.reload();
                },
                error: function (errors) {
                    console.log(errors.responseJSON);
                }
            })
        }
        $('.filter-date').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            weekStart: 1,
            todayHighlight: true
        });
    </script>
@endsection
