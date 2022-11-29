@extends('files::layout.file_layout')

@section('title')
    {{ trans('files::view.File text') }}
@endsection
<?php
use Rikkei\Core\View\Form;
use Carbon\Carbon;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\CoreUrl;

$ManageFilesTable = Rikkei\Files\Model\ManageFileText::getTableName();
$EmployeeTable = Rikkei\Team\Model\Employee::getTableName();
$TeamsTable = Rikkei\Team\Model\Team::getTableName();
$typeOptions = Rikkei\Files\Model\ManageFileText::getTypeOptions();
$typeStatus = Rikkei\Files\Model\ManageFileText::getTypeStatus();
$typeCandidateFilter = Form::getFilterData('manage_file_text.type');
$typeStatusSelected = Form::getFilterData('manage_file_text.status');
?>

@section('sidebar-common')
@include('files::include.sidebar_leave')
@endsection

@section('content-common')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
@endsection
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="col-md-6">
                    <h3 class="box-title managetime-box-title">{{ trans('files::view.List Files') }}</h3>
                </div>
                <div class="col-md-6">
                    @include('team::include.filter')
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id width-5-per">{{ trans('files::view.Numerical order') }}</th>
                            <th class="" data-order="quote_text" data-dir="{{ Config::getDirOrder('quote_text') }}">{{ trans('files::view.Kiểu văn bản') }}</th>
                            <th class="" data-order="note_text" data-dir="{{ Config::getDirOrder('note_text') }}">{{ trans('files::view.Số kí hiệu') }}</th>
                            <th class="" data-order="note_text" data-dir="{{ Config::getDirOrder('note_text') }}">{{ trans('files::view.Nơi ban hành') }}</th>
                            <th class="" data-order="note_text" data-dir="{{ Config::getDirOrder('note_text') }}">{{ trans('files::view.Ngày văn bản') }}</th>
                            <th class="" data-order="note_text" data-dir="{{ Config::getDirOrder('note_text') }}">{{ trans('files::view.Người ký') }}</th>
                            <th class="" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('files::view.Status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select name="filter[{{ $ManageFilesTable }}.type]" class="form-control select-grid filter-grid select-search width-100">
                                            <option value="">&nbsp;</option>
                                            @foreach($typeOptions as $option)
                                                <option value="{{ $option['id'] }}"<?php
                                                    if ($option['id'] == $typeCandidateFilter): ?> selected<?php endif; 
                                                        ?>>{{ $option['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $ManageFilesTable }}.code_file]" value='{{ Form::getFilterData("{$ManageFilesTable}.code_file") }}' placeholder="{{ trans('sales::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $TeamsTable }}.name]" value='{{ Form::getFilterData("{$TeamsTable}.name") }}' placeholder="{{ trans('sales::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $EmployeeTable }}.name]" value='{{ Form::getFilterData("{$EmployeeTable}.name") }}' placeholder="{{ trans('sales::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select name="filter[{{ $ManageFilesTable }}.status]" class="form-control select-grid filter-grid select-search width-100">
                                            <option value="">&nbsp;</option>
                                            @foreach($typeStatus as $status)
                                                @if($status['id'])
                                                <option value="{{ $status['id'] }}" <?php 
                                                        if ($status['id'] == $typeStatusSelected): ?> selected<?php endif; 
                                                            ?>>{{ $status['name'] }}</option>
                                                @endif
                                            @endforeach       
                                        </select>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
              
                                    <td>{{ $i }}</td>
                                    <td>@if($item->type  == 2) {{ trans('files::view.Công văn đi') }} @else {{ trans('files::view.Công văn đến') }} @endif</td>
                                    <td>{{ $item->code_file }}</td>
                                    <td>{{ $item->name_teams }}</td>
                                    <td>{{ Carbon::parse($item->date_file)->format('d-m-Y') }}</td>
                                    <td>{{ $item->name_employees }}</td>
                                    <td>@if($item->status  == 1) {{ trans('files::view.Đã vào sổ') }} @else {{ trans('files::view.Chưa vào sổ') }} @endif</td>
                                    <td>@if($item->status  == 1) 
                                        <a href="{{ route('file::file.editApproval', ['id' => $item->id]) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                                        @else
                                        <a href="{{ route('file::file.editApproval', ['id' => $item->id]) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                                            <form action="{{route('file::file.delete')}}" method="post" class="form-inline">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="id" value="{{$item->id}}">
                                                <button class="btn-delete delete-confirm" title="Delete">
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
                                    <h2 class="no-result-grid">{{trans('files::view.No results found')}}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="box-body">
                @include('files::include.pager')
            </div>
        </div>
    </div>
</div>
@endsection
