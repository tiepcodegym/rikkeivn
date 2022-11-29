@extends('layouts.default')

@section('title')
{{ trans('core::view.Menu') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
@endsection

@section('content')
<?php

use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;

$menuItemsTable = Rikkei\Core\Model\MenuItem::getTableName();
$menuGroupTable = \Rikkei\Core\Model\Menu::getTableName();
?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                @include('team::include.filter')
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id">{{ trans('core::view.NO.') }}</th>
                            <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('core::view.Name') }}</th>
                            <th class="sorting {{ Config::getDirClass('nane_group') }} col-name" data-order="nane_group" data-dir="{{ Config::getDirOrder('nane_group') }}">{{ trans('core::view.Menu group') }}</th>
                            <th class="sorting {{ Config::getDirClass('name_parent') }} col-name" data-order="name_parent" data-dir="{{ Config::getDirOrder('name_parent') }}">{{ trans('core::view.Parent Menu') }}</th>
                            <th class="sorting {{ Config::getDirClass('url') }} col-name" data-order="url" data-dir="{{ Config::getDirOrder('url') }}">{{ trans('core::view.Url') }}</th>
                            <th class="col-action">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $menuItemsTable }}.name]" value="{{ Form::getFilterData("{$menuItemsTable}.name") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $menuGroupTable }}.name]" value="{{ Form::getFilterData("{$menuGroupTable}.name") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[menu_item_parent.name]" value="{{ Form::getFilterData('menu_item_parent.name') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $menuItemsTable }}.url]" value="{{ Form::getFilterData("{$menuItemsTable}.url") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->nane_group }}</td>
                                    <td>{{ $item->name_parent }}</td>
                                    <td>{{ $item->url }}</td>
                                    <td>
                                        <a href="{{ route('core::setting.menu.item.edit', ['id' => $item->id ]) }}" class="btn-edit" title="{{ trans('team::view.Edit') }}"><i class="fa fa-edit"></i></a>
                                        <form action="{{ route('core::setting.menu.item.delete') }}" method="post" class="form-inline">
                                            {!! csrf_field() !!}
                                            {!! method_field('delete') !!}
                                            <input type="hidden" name="id" value="{{ $item->id }}" />
                                            <button href="" class="btn-delete delete-confirm" disabled>
                                                <span><i class="fa fa-trash"></i></span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center">
                                    <h2 class="no-result-grid">{{trans('core::view.No results found')}}</h2>
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
