<?php
use Rikkei\Team\View\Config;
use Rikkei\Document\View\DocConst;
use Rikkei\Core\View\Form as CoreForm;

$listRequestStatus = DocConst::listRequestStatuses();
?>

@extends('layouts.default')

@section('title', trans('doc::view.Document request'))

@section('css')

@include('doc::includes.css')

@stop

@section('content')

<div class="box box-primary">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-6">
                <a href="{{ route('doc::admin.request.edit') }}" class="btn btn-primary">
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
                    <th class="sorting {{ Config::getDirClass('author_email') }} col-name" data-order="author_email" data-dir="{{ Config::getDirOrder('author_email') }}">{{ trans('doc::view.Author') }}</th>
                    <th class="sorting {{ Config::getDirClass('creator_email') }} col-name" data-order="creator_email" data-dir="{{ Config::getDirOrder('creator_email') }}">{{ trans('doc::view.Document creator') }}</th>
                    <th class="sorting {{ Config::getDirClass('created_at') }} col-name" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('doc::view.Created time') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>
                        <input type="text" name="filter[docrq.name]" class="form-control filter-grid" 
                               placeholder="{{ trans('doc::view.Search') }}..." value="{{ CoreForm::getFilterData('docrq.name') }}">
                    </td>
                    <td>
                        <input type="text" name="filter[author.email]" class="form-control filter-grid" 
                               placeholder="{{ trans('doc::view.Search') }}..." value="{{ CoreForm::getFilterData('author.email') }}">
                    </td>
                    <td>
                        <input type="text" name="filter[creator.email]" class="form-control filter-grid" 
                               placeholder="{{ trans('doc::view.Search') }}..." value="{{ CoreForm::getFilterData('creator.email') }}">
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                @if (!$collectionModel->isEmpty())
                    @foreach ($collectionModel as $order => $item)
                    <tr>
                        <td>{{ $order + 1 + ($collectionModel->currentPage() - 1) * $collectionModel->perPage() }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ DocConst::getAccount($item->author_email) }}</td>
                        <td>{{ $item->creator_email }}</td>
                        <td>{{ $item->created_at }}</td>
                        <td class="white-space-nowrap">
                            <a href="{{ route('doc::admin.request.edit', ['id' => $item->id]) }}"
                               title="{{ trans('doc::view.Edit') }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                            {!! Form::open(['class' => 'form-inline', 'method' => 'delete', 'route' => ['doc::admin.request.delete', $item->id]]) !!}
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

