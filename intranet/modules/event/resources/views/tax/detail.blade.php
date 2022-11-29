<?php
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Config;
?>

@extends('layouts.default')

@section('title', trans('event::view.Detail send email tax file'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
@stop

@section('content')

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-6">
                <h4>{{ $taxFile->filename }}</h4>
            </div>
            <div class="col-sm-6">
                @include('team::include.filter', ['domainTrans' => 'event'])
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover dataTable" id="salary_table">
                <thead>
                    <tr>
                        <th>{{ trans('event::view.STT') }}</th>
                        <th class="sorting {{ Config::getDirClass('employee_code') }}" data-order="employee_code" data-dir="{{ Config::getDirOrder('employee_code') }}">{{ trans('event::view.Employee code') }}</th>
                        <th class="sorting {{ Config::getDirClass('email') }}" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('event::view.Email') }}</th>
                        <th class="sorting {{ Config::getDirClass('fullname') }}" data-order="fullname" data-dir="{{ Config::getDirOrder('fullname') }}">{{ trans('event::view.Employee name') }}</th>
                        <th class="sorting {{ Config::getDirClass('number_sent') }}" data-order="number_sent" data-dir="{{ Config::getDirOrder('number_sent') }}">{{ trans('event::view.Number sent') }}</th>
                        <th class="sorting {{ Config::getDirClass('sent_at') }}" data-order="sent_at" data-dir="{{ Config::getDirOrder('sent_at') }}">{{ trans('event::view.Sent at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input name="filter[employee_code]" class="form-control filter-grid" placeholder="{{ trans('event::view.Search') }}..."
                                   value="{{ CoreForm::getFilterData('employee_code') }}">
                        </td>
                        <td>
                            <input name="filter[email]" class="form-control filter-grid" placeholder="{{ trans('event::view.Search') }}..."
                                   value="{{ CoreForm::getFilterData('email') }}">
                        </td>
                        <td>
                            <input name="filter[fullname]" class="form-control filter-grid" placeholder="{{ trans('event::view.Search') }}..."
                                   value="{{ CoreForm::getFilterData('fullname') }}">
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    @if (!$collectionModel->isEmpty())
                        @foreach ($collectionModel as $order => $item)
                        <tr>
                            <td>{{ $order + 1 + ($collectionModel->currentPage() - 1) * $collectionModel->perPage() }}</td>
                            <td>{{ $item->employee_code }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->fullname }}</td>
                            <td>{{ $item->number_sent }}</td>
                            <td>{{ $item->sent_at }}</td>
                        </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="6"><h4 class="text-center">{{ trans('event::message.Not found item') }}</h4></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="box-body">
        @include('team::include.pager')
    </div>
</div>

@endsection
