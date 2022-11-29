<?php
use Rikkei\Team\View\Config;
use Rikkei\Document\View\DocConst;
use Rikkei\Core\View\Form as CoreForm;

$arrayTypes = $listTypes->lists('name', 'id')->toArray();
$listDocStatus = DocConst::listDocStatuses();
?>

@extends('layouts.default')

@section('title', trans('doc::view.Document'))

@section('css')

@include('doc::includes.css')

@stop

@section('content')

<div class="box box-primary">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-6">
                <a href="{{ route('doc::admin.edit') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i>
                    {{ trans('doc::view.Create') }}
                </a>
                @if ($permissType)
                <a target="_blank" href="{{ route('doc::admin.type.index') }}" class="btn btn-success">
                    {{ trans('doc::view.Document types') }}
                </a>
                @endif
                <a target="_blank" href="{{ route('doc::admin.help') }}" class="btn btn-info">
                    {{ trans('doc::view.Help') }}
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
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('title') }} col-name" data-order="title" data-dir="{{ Config::getDirOrder('title') }}">{{ trans('doc::view.Document code') }}</th>
                    <th class="sorting {{ Config::getDirClass('url') }} col-name" data-order="url" data-dir="{{ Config::getDirOrder('url') }}">URL</th>
                    <th class="sorting {{ Config::getDirClass('mimetype') }} col-name" data-order="mimetype" data-dir="{{ Config::getDirOrder('mimetype') }}">{{ trans('doc::view.Type file') }}</th>
                    <th class="sorting {{ Config::getDirClass('status') }} col-name" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('doc::view.Status') }}</th>
                    <th class="white-space-nowrap" width="300">{{ trans('doc::view.Document types') }}</th>
                    <th class="sorting {{ Config::getDirClass('team_names') }} col-name" data-order="team_names" data-dir="{{ Config::getDirOrder('team_names') }}">{{ trans('doc::view.Group') }}</th>
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('author_id') }} col-name" data-order="author_id" data-dir="{{ Config::getDirOrder('author_id') }}">{{ trans('doc::view.Author') }}</th>
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('created_at') }} col-name" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('doc::view.Created time') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>
                        <input type="text" name="filter[doc.code]" class="form-control filter-grid" 
                               placeholder="{{ trans('doc::view.Search') }}..." value="{{ CoreForm::getFilterData('doc.code') }}">
                    </td>
                    <td>
                        <input type="text" name="filter[file.name]" class="form-control filter-grid"
                               placeholder="{{ trans('doc::view.Search') }}..." value="{{ CoreForm::getFilterData('file.name') }}">
                    </td>
                    <td>
                        <input type="text" name="filter[doc.mimetype]" class="form-control filter-grid"
                               placeholder="{{ trans('doc::view.Search') }}..." value="{{ CoreForm::getFilterData('doc.mimetype') }}">
                    </td>
                    <td>
                        <select name="filter[number][doc.status]" class="form-control select-grid filter-grid select-search"
                                style="width: 110px;">
                            <option value="">&nbsp;</option>
                            @foreach ($listDocStatus as $value => $label)
                            <option value="{{ $value }}" {{ $value == CoreForm::getFilterData('number', 'doc.status') ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="filter[excerpt][type_id]" class="form-control select-grid filter-grid select-search has-search"
                                style="min-width: 150px;">
                            <option value="">&nbsp;</option>
                            {!! DocConst::toNestedOptions($listTypes, CoreForm::getFilterData('excerpt', 'type_id')) !!}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="filter[excerpt][team.name]" class="form-control filter-grid"
                               placeholder="{{ trans('doc::view.Search') }}..." value="{{ CoreForm::getFilterData('excerpt', 'team.name') }}">
                    </td>
                    <td>
                        <input type="text" name="filter[emp.email]" class="form-control filter-grid"
                               placeholder="{{ trans('doc::view.Search') }}..." value="{{ CoreForm::getFilterData('emp.email') }}">
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                @if (!$collectionModel->isEmpty())
                    @foreach ($collectionModel as $order => $item)
                    <tr>
                        <td>{{ $order + 1 + ($collectionModel->currentPage() - 1) * $collectionModel->perPage() }}</td>
                        <td>{{ $item->code }}</td>
                        <td>
                            <a href="{{ $item->file_type == 'link' ? $item->url : route('doc::admin.download', ['docId' => $item->id, 'id' => $item->file_id]) }}"
                               target="_blank">{{ $item->file_name }}</a>
                        </td>
                        <td>{{ $item->mimetype }}</td>
                        <td>{!! DocConst::renderStatusHtml($item->status, $listDocStatus, 'label') !!}</td>
                        <td>
                            <ul class="padding-left-15">
                            {!! DocConst::getListTypeName($item->type_ids, $arrayTypes) !!}
                            </ul>
                        </td>
                        <td>{{ $item->team_names }}</td>
                        <td>{{ DocConst::getAccount($item->author_email) }}</td>
                        <td class="white-space-nowrap">{{ $item->created_at }}</td>
                        <td class="white-space-nowrap">
                            <a href="{{ route('doc::admin.edit', ['id' => $item->id]) }}"
                               title="{{ trans('doc::view.Edit') }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                            {!! Form::open(['class' => 'form-inline', 'method' => 'delete', 'route' => ['doc::admin.delete', $item->id]]) !!}
                                <button type="submit" class="btn-delete delete-confirm" title="{{ trans('doc::view.Delete') }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="10" class="text-center"><h4>{{ trans('doc::message.None item found') }}</h4></td>
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

