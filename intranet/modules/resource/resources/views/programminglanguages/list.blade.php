@extends('layouts.default')
<?php

use Carbon\Carbon;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form as FormView;

$channelTable = Rikkei\Resource\Model\Programs::getTableName();
$domainTrans = "resource";
$langDomain = "resource::view."
?>
@section('title')
{{ trans('resource::view.Programminglanguages.List.List Programminglanguages list') }}
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
                <div class="pull-left">   
                    <div class="btn_actions">
                        <a href="{{route('resource::programminglanguages.create')}}" class="create-btn btn-add">
                            <i class="fa fa-plus"></i> <span class="">{{trans('resource::view.Greate btn')}}</span>
                        </a>
                        @if(!$collectionModel->isEmpty())
                        <a href="{{route('test::admin.test.m_action')}}" action="delete" class="m_action_btn btn-delete delete-confirm">
                            <i class="fa fa-trash"></i> <span class="">{{trans('resource::view.Delete btn')}}</span>
                        </a>
                        @endif
                    </div>
                </div>

                @include('team::include.filter')
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" name="massdel" class="check_all" style="vertical-align: text-top; margin-top: 3px;" /></th>
                            <th class="col-id width-5-per">{{ trans('resource::view.Numerical order') }}</th>
                            <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('resource::view.Programminglanguages.List.List Programminglanguages name') }}</th>
                            <th class="sorting {{ Config::getDirClass('primary_chart') }} col-name" data-order="primary_chart" data-dir="{{ Config::getDirOrder('primary_chart') }}">{{ trans('resource::view.Programminglanguages.Create.Primary char') }}</th>
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
                                        <input type="text" name="filter[{{ $channelTable }}.name]" value="{{ FormView::getFilterData("{$channelTable}.name") }}" placeholder="{{ trans('sales::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>  
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td><input type="checkbox" name="check_items[]" class="check_item" value="{{$item->id}}"></td>
                                    <td>{{ $i }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>
                                    @if($item->primary_chart && $item->primary_chart == 1) 
                                        {{ trans('resource::view.Programminglanguages.Yes') }}
                                    @else 
                                        {{ trans('resource::view.Programminglanguages.No') }} 
                                    @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('resource::programminglanguages.edit', ['id' => $item->id]) }}" class="btn-edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                         {!! Form::open(['class' => 'form-inline', 'method' => 'Post', 'route' => ['resource::programminglanguages.delete', $item->id]]) !!}
                                        <button  class="btn-delete delete-confirm">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center">
                                    <h2 class="no-result-grid">{{trans('resource::view.Languages.List.No results found')}}</h2>
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
@endsection
@section('script')
<script>
    url = "{{route('resource::programminglanguages.ajaxDelete')}}";
    _token = "{{ csrf_token() }}";
</script>
<script src="{{asset('resource/js/program/program.js')}}"></script>
@endsection