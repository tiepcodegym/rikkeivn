@extends('layouts.default')

@section('title', trans('test::test.candidate_infor'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('tests/css/main.css') }}">
@endsection

@section('content')

<?php
use Rikkei\Core\View\Form as FormView;
use Rikkei\Team\View\Config;
?>

<div class="box box-info">

    <div class="box-body">
        <div class="row">
            <div class="col-sm-8">
                {!! Form::open(['method' => 'post', 'route' => 'test::candidate.admin.import']) !!}
                <button type="submit" id="btn_import_cdd" class="btn-delete delete-confirm" data-noti="{{ trans('test::validate.Are you sure want to import') }}">{{ trans('test::test.Import to candidate') }}</button>
                {!! Form::close() !!}
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
                    <th>STT</th>
                    <th class="sorting {{ Config::getDirClass('full_name') }} col-name" data-order="full_name" data-dir="{{ Config::getDirOrder('full_name') }}">{{ trans('test::test.Full name') }}</th>
                    <th class="sorting {{ Config::getDirClass('email') }} col-name" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('test::test.email') }}</th>
                    <th>{{ trans('test::test.Date of birth') }}</th>
                    <th>{{ trans('test::test.Phone number') }}</th>
                    <th class="sorting {{ Config::getDirClass('position') }} col-name" data-order="position" data-dir="{{ Config::getDirOrder('position') }}">{{ trans('test::test.Position recruitment') }}</th>
                    <th class="sorting {{ Config::getDirClass('salary') }} col-name" data-order="salary" data-dir="{{ Config::getDirOrder('salary') }}">{{ trans('test::test.Desired salary') }}</th>
                    <th class="sorting {{ Config::getDirClass('recruiter') }} col-name" data-order="recruiter" data-dir="{{ Config::getDirOrder('recruiter') }}">{{ trans('test::test.hr_account') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>
                        <input type="text" name="filter[full_name]" value="{{ FormView::getFilterData('full_name') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[email]" value="{{ FormView::getFilterData('email') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[birth]" value="{{ FormView::getFilterData('birth') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[phone_number]" value="{{ FormView::getFilterData('phone_number') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[position]" value="{{ FormView::getFilterData('position') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[salary]" value="{{ FormView::getFilterData('salary') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                         <input type="text" name="filter[recruiter]" value="{{ FormView::getFilterData('recruiter') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                     </td>
                    <td></td>
                </tr>
                @if(!$collectionModel->isEmpty())
                    @foreach($collectionModel as $order => $item)
                    <tr>
                        <td>{{ ($order + 1) }}</td>
                        <td>{{ $item->full_name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->birth }}</td>
                        <td>{{ $item->phone_number }}</td>
                        <td>{{ $item->position }}</td>
                        <td>{{ $item->salary }}</td>
                        <td>{{ $item->recruiter }}</td>
                        <td>
                            <a href="{{ route('test::candidate.admin.show', ['id' => $item->id]) }}" class="btn btn-primary" data-toggle="tooltip" title="{{trans('test::test.view')}}"><i class="fa fa-eye"></i></a>
                            <a href="{{ route('test::candidate.admin.edit', ['id' => $item->id]) }}" class="btn-edit" data-toggle="tooltip" title="{{trans('test::test.edit')}}"><i class="fa fa-edit"></i></a>
                            {!! Form::open(['class' => 'form-inline', 'method' => 'delete', 'route' => ['test::candidate.admin.destroy', $item->id]]) !!}
                            <button type="submit" class="btn-delete delete-confirm" data-toggle="tooltip" title="{{trans('test::test.delete')}}"><i class="fa fa-trash"></i></button>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="7" class="text-center">{{trans('test::test.no_item')}}</td>
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
<script>
    $('#btn_import_cdd').click(function () {
       $(this).find('i').removeClass('hidden'); 
    });
</script>

@stop

