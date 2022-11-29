<?php
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Config;
?>

@extends('layouts.default')

@section('title', trans('event::view.Tax file uploaded'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
@stop

@section('content')

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-6"></div>
            <div class="col-sm-6">
                @include('team::include.filter')
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover dataTable" id="salary_table">
                <thead>
                    <tr>
                        <th>{{ trans('event::view.STT') }}</th>
                        <th class="sorting {{ Config::getDirClass('filename') }}" data-order="filename" data-dir="{{ Config::getDirOrder('filename') }}">{{ trans('event::view.File name') }}</th>
                        <th class="sorting {{ Config::getDirClass('email') }}" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('event::view.Creator') }}</th>
                        <th class="sorting {{ Config::getDirClass('count_row') }}" data-order="count_row" data-dir="{{ Config::getDirOrder('count_row') }}">{{ trans('event::view.Number of row') }}</th>
                        <th class="sorting {{ Config::getDirClass('created_at') }}" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('event::view.Created time') }}</th>
                        <th>{{ trans('event::view.Detail sent mail') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input name="filter[sf.filename]" class="form-control filter-grid" placeholder="{{ trans('event::view.Search') }}..."
                                   value="{{ CoreForm::getFilterData('sf.filename') }}">
                        </td>
                        <td>
                            <input name="filter[emp.email]" class="form-control filter-grid" placeholder="{{ trans('event::view.Search') }}..."
                                   value="{{ CoreForm::getFilterData('emp.email') }}">
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @if (!$collectionModel->isEmpty())
                        @foreach ($collectionModel as $order => $item)
                        <tr>
                            <td>{{ $order + 1 + ($collectionModel->currentPage() - 1) * $collectionModel->perPage() }}</td>
                            <td>{{ $item->filename }}</td>
                            <td>{{ ucfirst(preg_replace('/\@.*/', '', $item->email)) }}</td>
                            <td>{{ $item->count_row }}</td>
                            <td>{{ $item->created_at }}</td>
                            <td>
                                <a href="{{ route('event::send.email.employees.tax.mail_detail', ['id' => $item->id]) }}"
                                   class="btn btn-info" title="{{ trans('event::view.Details') }}">
                                    <i class="fa fa-eye"></i> 
                                </a>
                            </td>
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
