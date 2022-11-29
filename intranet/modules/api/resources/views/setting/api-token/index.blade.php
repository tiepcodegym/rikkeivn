@extends('layouts.default')

@section('title', trans('api::view.Api access token setting'))

@section('css')
<style>
    .table>tbody>tr>td, .table>thead>tr>th {
        vertical-align: middle;
    }
    .table>thead>tr>th:last-child {
        min-width: 55px;
        width: 55px;
    }
    .table tr>td.title {
        background-color: #3c8dbc;
        color: #fff;
        font-weight: 600;
    }
</style>
@stop

@section('content')

<div class="box box-rikkei">
    <div class="box-header with-border">
        <h3 class="box-title">{!! trans('api::view.Api access token list') !!}</h3>
    </div>
    <div class="box-body"></div>

    <div class="table-responsive">
        <table class="table table-hover table-striped dataTable table-bordered">
            <thead>
                <tr>
                    <th>{!! trans('core::view.NO.') !!}</th>
                    <th>{!! trans('api::view.Route name') !!}</th>
                    <th>{!! trans('api::view.Description') !!}</th>
                    <th>{!! trans('api::view.Api token') !!}</th>
                    <th>{!! trans('api::view.Expired at') !!}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                @if (count($routes) > 0)
                    @foreach ($routes as $key => $groupApi)
                        <tr>
                            <td colspan="6" class="title">{!! $groupApi['label'] !!}</td>
                        </tr>
                        <?php $i = 0; ?>
                        @foreach ($groupApi['api'] as $name => $desc)
                        <?php
                        $item = isset($collectionModel[$name]) ? $collectionModel[$name]->first() : null;
                        $editParams = [
                            'id' => $item ? $item->id : null
                        ];
                        if (!$item) {
                            $editParams['route'] = $name;
                        }
                        ?>
                        <tr>
                            <td>{!! ++$i !!}</td>
                            <td>{!! $name !!}</td>
                            <td>{!! $desc !!}</td>
                            <td>{{ $item ? $item->token : null }}</td>
                            <td>{{ $item && $item->expired_at ? \Carbon\Carbon::parse($item->expired_at)->format('Y-m-d H:i') : null }}</td>
                            <td class="white-space-nowrap">
                                <a href="{{ route('api-web::setting.tokens.edit', $editParams) }}"
                                    class="btn btn-success" title="{!! trans('core::view.Edit item') !!}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                @else
                <tr class="none-row">
                    <td colspan="5"><h4 class="text-center">{!! trans('core::view.Not found item') !!}</h4></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="box-header with-border" style="border-bottom: 3px solid #D32F2F;"></div>
</div>

@stop

