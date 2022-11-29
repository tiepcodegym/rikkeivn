<?php
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\FinesMoney\Model\FinesMoney;
?>
@extends('layouts.default')
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <style>
        .wrap-filter{
            display: flex;
            align-items: flex-end;
        }
    </style>
@endsection
@section('title')
    {{ trans('fines_money::view.fines_money_title') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <div class="row wrap-filter">
                        <div class="col-md-6">
                            <table class="table table-bordered table-grid-data" style="width: 280px;">
                                <tr class="success">
                                    <th width="150">{{ trans('fines_money::view.total_paid') }}</th>
                                    <td align="right" >{{ number_format($fines->paid) }}</td>
                                </tr>
                                <tr class="warning">
                                    <th>{{ trans('fines_money::view.total_un_paid') }}</th>
                                    <td align="right">{{ number_format($fines->total - $fines->paid) }}</td>
                                </tr>
                                <tr class="info">
                                    <th>{{ trans('fines_money::view.total') }}</th>
                                    <td align="right"><strong>{{ number_format($fines->total) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="pull-right">
                                @include('team::include.filter')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                            <thead>
                            <tr>
                                <th class="col-id width-10">{{trans('fines_money::view.label_no')}}</th>
                                <th class="{{ TeamConfig::getDirClass('type') }} col-name"
                                    data-dir="{{ TeamConfig::getDirOrder('type') }}">{{trans('fines_money::view.type')}}</th>
                                <th class="{{ TeamConfig::getDirClass('count') }} col-name"
                                    data-dir="{{ TeamConfig::getDirOrder('count') }}">{{trans('fines_money::view.count')}}</th>
                                <th class="{{ TeamConfig::getDirClass('amount') }} col-name"
                                    data-dir="{{ TeamConfig::getDirOrder('amount') }}">{{trans('fines_money::view.amount')}}</th>
                                <th class="{{ TeamConfig::getDirClass('status_amount') }} col-name"
                                    data-dir="{{ TeamConfig::getDirOrder('status_amount') }}">{{trans('fines_money::view.status_amount')}}</th>
                                <th class="{{ TeamConfig::getDirClass('month') }} col-name"
                                    data-dir="{{ TeamConfig::getDirOrder('month') }}">{{trans('fines_money::view.month')}}</th>
                                <th class="{{ TeamConfig::getDirClass('year') }} col-name"
                                    data-dir="{{ TeamConfig::getDirOrder('year') }}">{{trans('fines_money::view.year')}}</th>
                                <th class="{{ TeamConfig::getDirClass('note') }} col-name"
                                    data-dir="{{ TeamConfig::getDirOrder('note') }}">{{trans('fines_money::view.Note')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="filter-input-grid">
                                <td>&nbsp;</td>
                                <td>
                                    <?php $searchType = CoreForm::getFilterData("fines_money.type"); ?>
                                    {{ Form::select('filter[fines_money.type]', [null => trans('fines_money::view.select')] + $types,
                                                $searchType,
                                                ['class' => 'form-control select-grid filter-grid select-search']) }}
                                </td>
                                <td>&nbsp;</td><!-- count column -->
                                <td>&nbsp;</td><!-- amount column -->
                                <td>
                                    <?php $searchStatus = CoreForm::getFilterData("fines_money.status_amount"); ?>
                                    {{ Form::select('filter[fines_money.status_amount]', [null => trans('fines_money::view.select')] + $status,
                                                $searchStatus,
                                                ['class' => 'form-control select-grid filter-grid select-search']) }}
                                </td>
                                <td>
                                    <?php
                                    $searchMonth = CoreForm::getFilterData("fines_money.month");
                                    $month = range(1, 12);
                                    $month = array_combine($month, array_map(function ($value){
                                        return trans('fines_money::view.month') .' '. $value;
                                    }, $month));
                                    ?>
                                    {{ Form::select('filter[fines_money.month]', ['' => trans('fines_money::view.select')] + $month,
                                                $searchMonth,
                                                ['class' => 'form-control select-grid filter-grid select-search']) }}
                                </td>
                                <td>
                                    <?php
                                    $searchYear = CoreForm::getFilterData("fines_money.year");
                                    $year = range(2015, \Carbon\Carbon::now()->year);
                                    $year = array_combine($year, array_map(function ($value){
                                        return trans('fines_money::view.year') .' '. $value;
                                    }, $year));

                                    ?>
                                    {{ Form::select('filter[fines_money.year]', ['' => trans('fines_money::view.select')] + $year,
                                                $searchYear,
                                                ['class' => 'form-control select-grid filter-grid select-search']) }}
                                </td>
                                <td></td>
                            </tr>

                            @if(isset($collectionModel) && count($collectionModel))
                                <?php $i = CoreView::getNoStartGrid($collectionModel) ?>

                                @foreach($collectionModel as $item)
                                    <tr id="{{ $item->id}}">
                                        <td>{{ $i }}</td>
                                        <td>{{ data_get($types, $item->type) }}</td>
                                        <td>{{ $item->count }}</td>
                                        <td>{{ number_format($item->amount) }}</td>
                                        <td>{{ data_get($status, $item->status_amount) }}</td>
                                        <td>{{ trans('fines_money::view.month') }} {{ $item->month }}</td>
                                        <td>{{ trans('fines_money::view.year') }} {{ $item->year }}</td>
                                        <td>{{ $item->note ? $item->note : '' }}</td>
                                    </tr>
                                    <?php $i++; ?>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <h2 class="no-result-grid">{{ trans('fines_money::view.data_not_found') }}</h2>
                                    </td>
                                </tr>
                            @endif

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer">
                    @include('team::include.pager')
                </div>
            </div>
        </div>

    </div>

@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
@endsection