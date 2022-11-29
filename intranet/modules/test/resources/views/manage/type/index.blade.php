@extends('layouts.default')

@section('title', trans('test::test.test_type'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('tests/css/main.css') }}">
@endsection

@section('content')

<?php
use Rikkei\Test\Models\Type;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Team\View\Config;

$typeTbl = Type::getTableName();
?>

<div class="box box-info">

    <div class="box-body">
        <div class="row">
            <div class="col-sm-8">   
                <a class="create-btn btn-add" href="{{ route('test::admin.type.create') }}">
                    <i class="fa fa-plus"></i> <span class="">{{trans('test::test.add_new')}}</span>
                </a>
            </div>
            <div class="col-sm-4">
                @include('team::include.filter')
            </div>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover table-striped dataTable table-bordered">
            <thead>
                <tr>
                    <th>{{ trans('core::view.NO.') }}</th>
                    <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('test::test.name') }}</th>
                    <th class="sorting {{ Config::getDirClass('count_test') }} col-name" data-order="count_test" data-dir="{{ Config::getDirOrder('count_test') }}">{{ trans('test::test.test_number') }}</th>
                    <th class="sorting {{ Config::getDirClass('created_at') }} col-name" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('test::test.time_created') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>
                        <input type="text" name="filter[type.name]" value="{{ FormView::getFilterData('type.name') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                @if(!$collectionModel->isEmpty())
                    <?php
                    $extraNo = ($collectionModel->currentPage() - 1) * $collectionModel->perPage();
                    $collection = Type::toNested($collectionModel);
                    ?>
                    @foreach ($collection as $order => $item)
                    <tr>
                        <td>{{ $order + 1 + $extraNo }}</td>
                        <td class="_break_all">
                            {!! ($item->parent_id ? '-- ' : ' ') . Type::displayName($item->name) !!}
                        </td>
                        <td>{{ $item->count_test }}</td>
                        <td class="_nowwrap">{{ $item->created_at->format('H:i d-m-Y') }}</td>
                        <td class="_nowwrap">
                            <a href="{{route('test::admin.type.edit', ['id' => $item->id])}}" data-toggle="tooltip" title="{{trans('test::test.edit')}}" class="btn-edit"><i class="fa fa-edit"></i></a>
                            {!! Form::open(['class' => 'form-inline', 'method' => 'delete', 'route' => ['test::admin.type.destroy', $item->id]]) !!}
                            <button type="submit" class="btn-delete delete-confirm" data-toggle="tooltip" title="{{trans('test::test.delete')}}"><i class="fa fa-trash"></i></button>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="5" class="text-center">{{trans('test::test.no_item')}}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="cleafix"></div>
    
    <div class="box-body">
        @include('team::include.pager')
    </div>

</div>

@stop

@section('script')

@include('test::template.script')

@stop

