@extends('layouts.default')

@section('title')
{{ trans('team::view.Acl') }} 
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
@endsection

@section('content')
<?php

use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\Action;
use Rikkei\Core\View\View;

$actionTable = Action::getTableName();
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
                            <th class="col-id" style="width:30px">{{ trans('core::view.NO.') }}</th>
                            <th class="sorting {{ Config::getDirClass('name') }} col-name" style="width:100px" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">Code</th>
                            <th class="sorting {{ Config::getDirClass('description') }} col-name" style="width:140px" data-order="description" data-dir="{{ Config::getDirOrder('description') }}">{{ trans('team::view.Description') }}</th>
                            <th class="sorting {{ Config::getDirClass('route') }} col-name" data-order="route" data-dir="{{ Config::getDirOrder('route') }}">Route</th>
                            <th class="sorting {{ Config::getDirClass('name_parent') }} col-name" style="width:100px" data-order="name_parent" data-dir="{{ Config::getDirOrder('name_parent') }}">{{ trans('team::view.Parent') }}</th>
                            <th class="sorting {{ Config::getDirClass('sort_order') }} col-name" data-order="sort_order" data-dir="{{ Config::getDirOrder('sort_order') }}">{{ trans('team::view.Sort order') }}</th>
                            <th class="col-action col-a2">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $actionTable }}.name]" value="{{ Form::getFilterData("{$actionTable}.name") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $actionTable }}.description]" value="{{ Form::getFilterData("{$actionTable}.description") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $actionTable }}.route]" value="{{ Form::getFilterData("{$actionTable}.route") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[action_parent.name]" value="{{ Form::getFilterData('action_parent.name') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>{{ $item->route }}</td>
                                    <td>{{ $item->name_parent }}</td>
                                    <td>{{ $item->sort_order }}</td>
                                    <td>
                                        <a href="{{ route('team::setting.acl.edit', ['id' => $item->id ]) }}" class="btn-edit" title="{{ trans('team::view.Edit') }}"><i class="fa fa-edit"></i></a>
                                        <form action="{{ route('team::setting.acl.delete') }}" method="post" class="form-inline">
                                            {!! csrf_field() !!}
                                            {!! method_field('delete') !!}
                                            <input type="hidden" name="id" value="{{ $item->id }}" />
                                            <button href="" class="btn-delete delete-confirm" disabled title="{{ trans('core::view.Remove') }}">
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
