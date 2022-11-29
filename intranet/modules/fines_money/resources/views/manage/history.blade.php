<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as FilterForm;
use Rikkei\Team\View\Config;
use Rikkei\ManageTime\Model\FinesMoney;
use Rikkei\Team\Model\Employee;

$fineHis = 'fch';
$employee = Employee::getTableName();
?>

@extends('layouts.default')

@section('title', trans('fines_money::view.History fines money'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
@stop

@section('content')

<div class="content-sidebar">
    <div class="box box-info">
            <div class="row">
                <div class="col-sm-12">
                    <div class="box-body filter-mobile-left">
                        <div class="pull-right">
                            <div>
                                <label class="col-md-12">&nbsp;</label>
                            </div>
                            @include('team::include.filter')
                        </div>
                    </div>
                </div>
            </div>
        <div class="table-responsive">
            <table class="table dataTable table-bordered working-time-tbl table-grid-data">
                <thead>
                    <tr>
                        <th>{{ trans('core::view.NO.') }}</th>
                        <th class="sorting {{ Config::getDirClass('nameChecker') }} col-nameChecker" data-order="nameChecker"
                            data-dir="{{ Config::getDirOrder('nameChecker') }}">{{ trans('fines_money::view.User check') }}</th>
                        <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name"
                            data-dir="{{ Config::getDirOrder('name') }}">{{ trans('fines_money::view.Người nộp phạt') }}</th>
                        <th class="sorting {{ Config::getDirClass('month') }} col-month" data-order="month"
                            data-dir="{{ Config::getDirOrder('month') }}">{{ trans('fines_money::view.month') }}</th>
                        <th class="sorting {{ Config::getDirClass('year') }} col-year" data-order="year"
                            data-dir="{{ Config::getDirOrder('year') }}">{{ trans('fines_money::view.year') }}</th>
                        <th class="sorting {{ Config::getDirClass('year') }} col-type" data-order="type"
                            data-dir="{{ Config::getDirOrder('year') }}">{{ trans('fines_money::view.type') }}</th>
                        <th class="sorting {{ Config::getDirClass('note') }} col-note width-300" data-order="note"
                            data-dir="{{ Config::getDirOrder('note') }}">{{ trans('fines_money::view.Note') }}</th>
                        <th class="sorting {{ Config::getDirClass('checked_date') }} col-checked_date" data-order="checked_date"
                            data-dir="{{ Config::getDirOrder('checked_date') }}">{{ trans('fines_money::view.Date check') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[{{$employee}}.checker]" value='{{ FilterForm::getFilterData($employee.'.checker') }}' placeholder="{{ trans('sales::view.Search') }}..." class="filter-grid form-control" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[{{$employee}}.name]" value='{{ FilterForm::getFilterData($employee. '.name') }}' placeholder="{{ trans('sales::view.Search') }}..." class="filter-grid form-control" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[{{$fineHis}}.month]" value='{{ FilterForm::getFilterData($fineHis. '.month') }}' placeholder="{{ trans('sales::view.Search') }}..." class="filter-grid form-control" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[{{$fineHis}}.year]" value='{{ FilterForm::getFilterData($fineHis. '.year') }}' placeholder="{{ trans('sales::view.Search') }}..." class="filter-grid form-control" />
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ Form::select("filter[$fineHis.type]", [null => trans('fines_money::view.select_all')] + $types,
                                                                    FilterForm::getFilterData("$fineHis.type", null, null),
                                                                     ['class' => 'form-control select-grid filter-grid']) }}
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>

                    </tr>
                    @if(isset($collectionModel) && count($collectionModel))
                        <?php $i = 1; ?>
                        @foreach($collectionModel as $item)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $item->nameChecker ? $item->nameChecker : '' }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->month }}</td>
                                <td>{{ $item->year }}</td>
                                <td>{{ data_get($types, $item->type) }}</td>
                                <td>{!! $item->content ? strip_tags($item->content, '<br>') : '' !!}</td>
                                <td>{{ $item->checked_date ? $item->checked_date : ''}}</td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="10" class="text-center">
                                <h2 class="no-result-grid">{{trans('files::view.No results found')}}</h2>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <!-- /.box-body -->
        <div class="box-body">
            @include('team::include.pager')
        </div>
    </div>  
</div>
@stop

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
@stop

