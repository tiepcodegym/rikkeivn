<?php
use Rikkei\Core\View\CoreUrl;

use Rikkei\Team\View\Config as Config;
use Rikkei\Core\View\View;
use Rikkei\Core\View\Form;
use Carbon\Carbon;
$buttonAction['create'] = [
    'label' => 'Create Poster',
    'class' => 'btn btn-primary',
    'disabled' => false,
    'url'=> URL::route('news::posters.create'),
    'type' => 'link'
];

$filterStart = Form::getFilterData('end_at', 'from');
$filterEnd = Form::getFilterData('start_at', 'to');

if (!$filterStart) {
    $filterStart = Carbon::now()->format('Y-m-01');
}
if (!$filterEnd) {
    $filterEnd = Carbon::now()->format('Y-m-t');
}
?>

@extends('layouts.default')

@section('title')
    {{ trans('news::view.List poster') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css"/>
@endsection

@section('content')

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
                                              class='form-control date-picker filter-grid form-inline'
                                              value="{{ \Carbon\Carbon::parse($filterStart)->format('d/m/Y') }}"
                                              />
                                       <input type="hidden" class="filter-grid" name="filter[end_at][from]" id="altFromDate" value="{{ $filterStart }}">
                                   </div>
                               </div>

                               <div class="team-ot-select-box col-md-3">
                                   <label>{{trans('manage_time::view.End date')}}</label>
                                   <div class="input-box">
                                       <input type="text"
                                              id="toDate"
                                              class='form-control date-picker filter-grid form-inline'
                                              value="{{ \Carbon\Carbon::parse($filterEnd)->format('d/m/Y') }}"
                                              />
                                       <input type="hidden" class="filter-grid" name="filter[start_at][to]" id="altToDate" value="{{ $filterEnd }}">
                                   </div>
                               </div>
                           </div>
                       </div>
                       <div class="col-md-4 text-right">
                           @include('team::include.filter', ['domainTrans' => 'news', 'buttons' => $buttonAction])
                       </div>
                   </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                            <tr>
                                <th class="col-id width-10" style="width: 20px;">{{ trans('news::view.No.') }}</th>
                                <th style="width: 200px" class="sorting {{ Config::getDirClass('title') }}" data-order="title" data-dir="{{ Config::getDirOrder('title') }}" >{{trans('news::view.title')}}</th>
                                <th class="sorting {{ Config::getDirClass('start_at') }} col-startat" data-order="start_at" data-dir="{{ Config::getDirOrder('start_at') }}">{{ trans('news::view.Start At') }}</th>
                                <th class="sorting {{ Config::getDirClass('end_at') }} col-endat" data-order="end_at" data-dir="{{ Config::getDirOrder('end_at') }}">{{ trans('news::view.End At') }}</th>
                                <th class="sorting {{ Config::getDirClass('order') }} col-order" data-order="order" data-dir="{{ Config::getDirOrder('order') }}">{{ trans('news::view.Order') }}</th>
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
                                            <input type="text" name="filter[except][slug]" value="{{ Form::getFilterData('except','slug') }}"
                                                   class="form-control filter-grid" placeholder="{{ trans('team::view.Search') }}...">
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
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
                                    <td>{{ $item->title }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->start_at)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->end_at)->format('d/m/Y') }}</td>
                                    <td>{{ $item->order }}</td>
                                    <td> <span class="{{$listStatusLabel[$item->status]}}">{{ trans($listStatus[$item->status]) }}</span></td>
                                    <td>
                                        <a class="btn btn-success" target="_blank"
                                           href="{{ route('news::posters.edit', ['id' => $item->id]) }}" >
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <form action="{{ route('news::posters.delete', ['id' => $item->id]) }}" method="post" class="form-inline">
                                            {!! csrf_field() !!}
                                            {!! method_field('delete') !!}
                                            <input type="hidden" name="id" value="{{ $item->id }}" />
                                            <button href="" class="btn-delete delete-confirm" title="{{ trans('manage_time::view.Delete') }}">
                                                <span><i class="fa fa-trash"></i></span>
                                            </button>
                                        </form>
                                    </td>
                                    <td></td>
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

        function reFormatDate(dateDisplay) {
            dateDisplay = moment(dateDisplay, 'DD/MM/YYYY');
            return moment(dateDisplay).format('YYYY-MM-DD');
        }

        $('#fromDate').datetimepicker({
            format: 'DD/MM/Y',
            showClear: true,
        });

        $('#toDate').datetimepicker({
            format: 'DD/MM/Y',
            showClear: true,
            useCurrent: false,
        });

        $("#fromDate").on("dp.change", function (e) {
            $('#toDate').data("DateTimePicker").minDate(e.date);
            $('#altFromDate').val(reFormatDate(e.date));
        });

        $("#toDate").on("dp.change", function (e) {
            $('#fromDate').data("DateTimePicker").maxDate(e.date);
            $('#altToDate').val(reFormatDate(e.date));
        });
    </script>
@endsection

