@extends('layouts.default')
<?php
use Rikkei\Core\View\Form;
use Carbon\Carbon;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\CoreUrl;

$companyTable = Rikkei\Sales\Model\Company::getTableName();

?>
@section('title')
{{ trans('sales::view.Company.List') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('sales/css/customer_index.css') }}" rel="stylesheet" type="text/css" >
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="col-md-6">
                    <a href="{{ route('sales::company.create') }}" class="btn btn-edit"><span class="glyphicon glyphicon-plus"></span> &nbsp;<span>{{ trans('sales::view.Create new') }}</span></a>
                    <button class="btn btn-primary btn-merge" onclick="mergeConfirm();" disabled=""><span class="glyphicon glyphicon-resize-small"></span> &nbsp;<span>{{ trans('sales::view.Merge') }}</span></button>
                </div>
                <div class="col-md-6">
                    @include('team::include.filter')
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="align-center"><input type="checkbox" class="check-parent" onclick="parentCheck(this);" /></th>
                            <th class="col-id width-5-per">{{ trans('sales::view.Numerical order') }}</th>
                            <th class="sorting {{ Config::getDirClass('name_ja') }} col-name" data-order="company" data-dir="{{ Config::getDirOrder('name_ja') }}">{{ trans('sales::view.Company.Create.Name') }}</th>
                            <th class="sorting {{ Config::getDirClass('name_ja') }} col-name_ja" data-order="name_ja" data-dir="{{ Config::getDirOrder('name_ja') }}">{{ trans('sales::view.Name ja') }}</th>
                            <th class="col-action width-10-per">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $companyTable }}.company]" value="{{ Form::getFilterData("{$companyTable}.company") }}" placeholder="{{ trans('sales::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $companyTable }}.name_ja]" value="{{ Form::getFilterData("{$companyTable}.name_ja") }}" placeholder="{{ trans('sales::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td class="align-center"><input type="checkbox" class="check-child" value="{{ $item->id }}" data-name='{{ $item->company }}' onclick="childCheck(this);" /></td>
                                    <td>{{ $i }}</td>
                                    <td>{{ $item->company }}</td>
                                    <td>{{ $item->name_ja }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('sales::company.edit', ['id' => $item->id]) }}" class="btn-edit" title="{{ trans('team::view.Edit') }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if (Permission::getInstance()->isAllow('sales::company.delete'))
                                            <form action="{{route('sales::company.delete')}}" method="post" class="form-inline">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="id" value="{{$item->id}}">
                                                <button class="btn-delete delete-confirm" title="{{ trans('team::view.Delete') }}">
                                                    <span><i class="fa fa-trash"></i></span>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center">
                                    <h2 class="no-result-grid">{{trans('sales::view.No results found')}}</h2>
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
@include('sales::company.include.modal_merge_confirm')
@endsection
@section('script')
<script>
    var routeMerge = '{{ route("sales::company.merge") }}';
    var token = '{{ csrf_token() }}';
</script>
<script src="{{ CoreUrl::asset('sales/js/common.js') }}"></script>
@endsection
