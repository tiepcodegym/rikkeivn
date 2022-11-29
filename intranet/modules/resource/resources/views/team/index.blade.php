<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form as FormView;
?>

@extends('layouts.default')

@section('title', trans('resource::view.Team feature'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('content')

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-4">
                <a href="{{ route('resource::plan.team.create') }}" class="btn-add"><i class="fa fa-plus"></i> {{ trans('resource::view.Add new') }}</a>
            </div>
            <div class="col-sm-8">
                @include('team::include.filter')
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped dataTable table-bordered table-hover table-grid-data statistics-table">
            <thead>
                <tr>
                    <th>{{ trans('core::view.NO.') }}</th>
                    <th class="sorting {{ Config::getDirClass('name') }}" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('resource::view.Team name') }}</th>
                    <th class="sorting {{ Config::getDirClass('alias_name') }}" data-order="alias_name" data-dir="{{ Config::getDirOrder('alias_name') }}">{{ trans('resource::view.Alias team') }}</th>
                    <th class="sorting {{ Config::getDirClass('sort_order') }}" data-order="sort_order" data-dir="{{ Config::getDirOrder('sort_order') }}">{{ trans('resource::view.Sort order') }}</th>
                    <th width="120"></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>
                        <input type="text" name="filter[team_ft.name]" value="{{ FormView::getFilterData('team_ft.name') }}"
                               class="form-control filter-grid" placeholder="{{ trans('team::view.Search') }}...">
                    </td>
                    <td>
                        <select class="form-control select-grid filter-grid select-search" name="filter[number][team.id]">
                            <option value="">&nbsp;</option>
                            @if ($teamList)
                                @foreach ($teamList as $team)
                                <option value="{{ $team['value'] }}" 
                                        {{ FormView::getFilterData('number', 'team.id') == $team['value'] ? 'selected' : '' }}>
                                        {{ $team['label'] }}
                                </option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                @if (!$collectionModel->isEmpty())
                    <?php 
                    $perPage = $collectionModel->perPage(); 
                    $currentPage = $collectionModel->currentPage();
                    ?>
                    @foreach ($collectionModel as $order => $item)
                        <tr>
                            <td>{{ $order + 1 + (($currentPage - 1) * $perPage) }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->alias_name }}</td>
                            <td>{{ $item->sort_order }}</td>
                            <td>
                                <a href="{{ route('resource::plan.team.edit', ['id' => $item->id]) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                                {!! Form::open(['method' => 'delete', 'route' => ['resource::plan.team.destroy', $item->id], 'class' => 'form-inline']) !!}
                                <button type="submit" class="btn-delete delete-confirm"><i class="fa fa-trash"></i></button>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="5" class="text-center"><h4>{{ trans('resource::message.No data') }}</h4></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="box-body">
        @include('team::include.pager')
    </div>

</div>

@endsection

@section('script')

@include('resource::recruit.script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script>
    selectSearchReload();
</script>

@endsection