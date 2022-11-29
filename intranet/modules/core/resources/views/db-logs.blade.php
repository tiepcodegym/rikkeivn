<?php
use Rikkei\Core\View\View;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Config;
?>

@extends('layouts.default')

@section('title', 'Database log')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<style>
    pre.sf-dump {
        z-index: 1!important;
    }
</style>
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
                        <th>No.</th>
                        <th class="sorting {{ Config::getDirClass('action') }} col-name" data-order="action" data-dir="{{ Config::getDirOrder('action') }}">{{ trans('core::view.Action name') }}</th>
                        <th class="sorting {{ Config::getDirClass('model') }} col-name" data-order="model" data-dir="{{ Config::getDirOrder('model') }}">{{ trans('core::view.Model/Table') }}</th>
                        <th>{{ trans('core::view.Subject ID') }}</th>
                        <th>{{ trans('core::view.Attributes') }}</th>
                        <th class="sorting {{ Config::getDirClass('email') }} col-name" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('core::view.Actor') }}</th>
                        <th class="sorting {{ Config::getDirClass('created_at') }} col-name" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('core::view.Created at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input name="filter[db.action]" class="form-control filter-grid" 
                                   value="{{ CoreForm::getFilterData('db.action') }}"
                                   placeholder="{{ trans('core::view.Search') }}">
                        </td>
                        <td>
                            <input name="filter[db.model]" class="form-control filter-grid" 
                                   value="{{ CoreForm::getFilterData('db.model') }}"
                                   placeholder="{{ trans('core::view.Search') }}">
                        </td>
                        <td>
                            <input name="filter[number][db.subject_id]" class="form-control filter-grid" 
                                   value="{{ CoreForm::getFilterData('number', 'db.subject_id') }}"
                                   placeholder="{{ trans('core::view.Search') }}">
                        </td>
                        <td>
                            <input name="filter[db.attributes]" class="form-control filter-grid" 
                                   value="{{ CoreForm::getFilterData('db.attributes') }}"
                                   placeholder="{{ trans('core::view.Search') }}">
                        </td>
                        <td>
                            <input name="filter[emp.email]" class="form-control filter-grid" 
                                   value="{{ CoreForm::getFilterData('emp.email') }}"
                                   placeholder="{{ trans('core::view.Search') }}">
                        </td>
                        <td>
                            <input name="filter[db.created_at]" class="form-control filter-grid" 
                                   value="{{ CoreForm::getFilterData('db.created_at') }}"
                                   placeholder="{{ trans('core::view.Search') }}">
                        </td>
                    </tr>
                    @if ($collectionModel->isEmpty())
                    <tr>
                        <td colspan="7"><h4 class="text-center">{{ trans('core::view.None item') }}</h4></td>
                    </tr>
                    @else
                        <?php
                        $perPage = $collectionModel->perPage();
                        $currPage = $collectionModel->currentPage();
                        ?>
                        @foreach ($collectionModel as $order => $item)
                        <tr>
                            <td>{{ $order + ($currPage - 1) * $perPage + 1 }}</td>
                            <td>{{ $item->action }}</td>
                            <td>{{ $item->model }}</td>
                            <td>{{ $item->subject_id }}</td>
                            <td>
                                <?php
                                $attributes = $item->attributes ? json_decode($item->attributes, true) : [];
                                dump($attributes);
                                ?>
                            </td>
                            <td>{{ View::getNickName($item->email) }}</td>
                            <td>{{ $item->created_at }}</td>
                        </tr>
                        @endforeach
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
<script>
    (function ($) {
        $('.log-content').shortedContent({showChars: 200});
    })(jQuery);
</script>
@stop