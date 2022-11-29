<?php
use Rikkei\Team\View\Config;
use Rikkei\Document\View\DocConst;
use Rikkei\Core\View\Form as CoreForm;
?>

@extends('layouts.default')

@section('title', trans('doc::view.Document types'))

@section('css')

@include('doc::includes.css')

@stop

@section('content')

<div class="box box-primary">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-6">
                <a href="{{ route('doc::admin.type.edit') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i>
                    {{ trans('doc::view.Create') }}
                </a>
            </div>
            <div class="col-sm-6">
                @include('team::include.filter')
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped dataTable table-bordered">
            <thead>
                <tr>
                    <th>{{ trans('core::view.NO.') }}</th>
                    <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('doc::view.Name') }}</th>
                    <th class="sorting {{ Config::getDirClass('status') }} col-name" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('doc::view.Status') }}</th>
                    <th class="sorting {{ Config::getDirClass('parent_name') }} col-name" data-order="parent_name" data-dir="{{ Config::getDirOrder('parent_name') }}">{{ trans('doc::view.Parent') }}</th>
                    <th class="sorting {{ Config::getDirClass('order') }} col-name" data-order="order" data-dir="{{ Config::getDirOrder('order') }}">{{ trans('doc::view.Sort order') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>
                        <input type="text" name="filter[type.name]" class="form-control filter-grid" 
                               placeholder="{{ trans('doc::view.Search') }}..." value="{{ CoreForm::getFilterData('type.name') }}">
                    </td>
                    <td>
                        <select name="filter[type.status]" class="form-control select-grid filter-grid select-search">
                            <option value="">&nbsp;</option>
                            @foreach (DocConst::listTypeStatuses() as $value => $label)
                            <option value="{{ $value }}" {{ $value == CoreForm::getFilterData('type.status') ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @if (!$collectionModel->isEmpty())
                    @foreach ($collectionModel as $order => $item)
                    <tr>
                        <td>{{ $order + 1 + ($collectionModel->currentPage() - 1) * $collectionModel->perPage() }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->getLabelStatus() }}</td>
                        <td>{{ $item->parent_name }}</td>
                        <td>{{ $item->order }}</td>
                        <td class="white-space-nowrap">
                            <a href="{{ route('doc::admin.type.edit', ['id' => $item->id]) }}"
                               title="{{ trans('doc::view.Edit') }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                            {!! Form::open(['class' => 'form-inline', 'method' => 'delete', 'route' => ['doc::admin.type.delete', $item->id]]) !!}
                                <button type="submit" class="btn-delete delete-confirm" title="{{ trans('doc::view.Delete') }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="6" class="text-center"><h4>{{ trans('doc::message.None item found') }}</h4></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="box-body">
        @include('team::include.pager')
    </div>
</div>

@stop

@section('script')

@include('doc::includes.script')

@stop

