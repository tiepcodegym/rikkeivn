@extends('layouts.default')

@section('title')
{{ trans("magazine::view.Magazine manager") }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('magazine/css/style.css') }}" rel="stylesheet" type="text/css" >
@endsection

<?php
use Rikkei\Core\View\Form as FormView;
use Rikkei\Team\View\Config;
?>

@section('content')
<div class="box box-primary">
        
    <div class="box-body">
        <div class="pull-left">   
            <div class="btn_actions">
                <a href="{{route('magazine::create')}}" class="create-btn btn-add" title="{{trans('magazine::view.Add new')}}">
                    <i class="fa fa-plus"></i> <span class="">{{trans('magazine::view.Add new')}}</span>
                </a>
            </div>
        </div>

        @include('team::include.filter', ['domainTrans' => 'magazine'])

        <div class="clearfix"></div>
    </div>
        
        <div class="box-body">
            <table class="table table-hover table-striped dataTable table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans("magazine::view.Name") }}</th>
                        <th>{{ trans('magazine::view.Number images') }}</th>
                        <th class="sorting {{ Config::getDirClass('created_at') }} col-name" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('magazine::view.Date') }}</th>
                        <th>{{ trans('magazine::view.Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input type="text" name="filter[name]" value="{{ FormView::getFilterData("name") }}" placeholder="{{ trans('magazine::view.Search') }}..." class="filter-grid form-control" />
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    @if(!$collectionModel->isEmpty())
                        @foreach($collectionModel as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td><a href="{{ route('magazine::read', ['id' => $item->id, 'slug' => $item->slug]) }}" target="_blank">{{ $item->name }}</a></td>
                                <td>{{ $item->images()->count() }}</td>
                                <td>{{ $item->created_at->format('H:i d/m/Y') }}</td>
                                <td>
                                    <a class="btn btn-primary" target="_blank" href="{{ route('magazine::read', ['id' => $item->id, 'slug' => $item->slug]) }}" title="{{ trans('magazine::view.View') }}"><i class="fa fa-eye"></i></a>
                                    <a class="btn-edit" href="{{ route('magazine::edit', $item->id) }}" title="{{ trans("magazine::view.Edit") }}"><i class="fa fa-edit"></i></a>
                                    {!! Form::open(['method' => 'delete', 'route' => ['magazine::delete', $item->id], 'class' => 'form-inline']) !!}
                                        <button class="btn-delete delete-confirm" type="submit" title="{{ trans('magazine::view.Delete') }}"><i class="fa fa-trash"></i></button>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">
                                {{ trans('magazine::view.There are no Magazine.') }}
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

@endsection
