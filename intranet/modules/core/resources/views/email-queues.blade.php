<?php
use Rikkei\Core\View\View;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Config;

$emailQueue = Rikkei\Core\Model\EmailQueue::getTableName();
?>
@extends('layouts.default')

@section('title', 'Email queues list')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
@endsection

@section('content')

<div class="box box-primary">
    <div class="box-body">
        @include('team::include.filter')
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped dataTable table-bordered">
                <thead>
                    <tr>
                        <th class="sorting {{ Config::getDirClass('id') }} col-name" data-order="id" data-dir="{{ Config::getDirOrder('id') }}">{{ trans('core::view.Id') }}</th>
                        <th>{{ trans('core::view.From email') }}</th>
                        <th>{{ trans('core::view.To email') }}</th>
                        <th>{{ trans('core::view.Subject') }}</th>
                        <th class="sorting {{ Config::getDirClass('created_at') }} col-name" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('core::view.Created at') }}</th>
                        <th class="sorting {{ Config::getDirClass('send_at') }} col-name" data-order="send_at" data-dir="{{ Config::getDirOrder('send_at') }}">{{ trans('core::view.Send at') }}</th>
                    	<th>{{ trans('core::view.Error') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                        	<input type="text" class="form-control filter-grid" name="filter[{{ $emailQueue }}.id]" value="{{ CoreForm::getFilterData("{$emailQueue}.id") }}" placeholder="{{ trans('core::view.Search') }}">
                        </td>
                        <td>
                        	<input type="text" class="form-control filter-grid" name="filter[{{ $emailQueue }}.from_email]" value="{{ CoreForm::getFilterData("{$emailQueue}.from_email") }}" placeholder="{{ trans('core::view.Search') }}">
                        </td>
                        <td>
                        	<input type="text" class="form-control filter-grid" name="filter[{{ $emailQueue }}.to_email]" value="{{ CoreForm::getFilterData("{$emailQueue}.to_email") }}" placeholder="{{ trans('core::view.Search') }}">
                        </td>
                        <td>
                        	<input type="text" class="form-control filter-grid" name="filter[{{ $emailQueue }}.subject]" value="{{ CoreForm::getFilterData("{$emailQueue}.subject") }}" placeholder="{{ trans('core::view.Search') }}">
                        </td>
                        <td>
                        	<input type="text" class="form-control filter-grid" name="filter[{{ $emailQueue }}.created_at]" value="{{ CoreForm::getFilterData("{$emailQueue}.created_at") }}" placeholder="{{ trans('core::view.Search') }}">
                        </td>
                        <td>
                        	<input type="text" class="form-control filter-grid" name="filter[{{ $emailQueue }}.send_at]" value="{{ CoreForm::getFilterData("{$emailQueue}.send_at") }}" placeholder="{{ trans('core::view.Search') }}">
                        </td>
                        <td>
                        	<div></div>
                        </td>
                    </tr>
                    @if(isset($collectionModel) && count($collectionModel))
                    <?php $i = View::getNoStartGrid($collectionModel); ?>
	                    @foreach ($collectionModel as $order => $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->from_email }}</td>
                            <td>{{ $item->to_email }}</td>
                            <td>{{ $item->subject }}</td>
                            <td>{{ $item->created_at }}</td>
                            <td>{{ $item->send_at }}</td>
                            <td>{{ $item->error }}</td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                    @else
					<tr>
                        <td colspan="7"><h4 class="text-center">{{ trans('core::view.None item') }}</h4></td>
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

@section('script')
@endsection
