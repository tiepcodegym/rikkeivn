@extends('layouts.default')
@section('title')
    {{ trans('team::view.Checkpoint.List.checkpoint') }}
@endsection
@section('content')
<?php

use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Core\View\MobileDetect;
use Rikkei\Team\View\CheckpointPermission;

?>

<div class="row list-css-page">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="box-body">
                            <div class="filter-action">
                                <button class="btn btn-primary btn-reset-filter">
                                    <span>{{ trans('team::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                                <button class="btn btn-primary btn-search-filter">
                                    <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
                                <thead>
                                    <tr role="row">
                                        <th class="sorting {{ Config::getDirClass('id') }}" data-order="id" data-dir="{{ Config::getDirOrder('id') }}" >{{ trans('team::view.Checkpoint Id') }}</th>
                                        <th class="sorting {{ Config::getDirClass('checkpoint_type_name') }}" data-order="checkpoint_type_name" data-dir="{{ Config::getDirOrder('checkpoint_type_name') }}" >{{ trans('team::view.Checkpoint.List.Type') }}</th>
                                        <th class="sorting {{ Config::getDirClass('checkpoint_time.check_time') }}" data-order="checkpoint_time.check_time" data-dir="{{ Config::getDirOrder('checkpoint_time.check_time') }}" >{{ trans('team::view.Checkpoint.List.Time') }}</th>
                                        <th class="sorting {{ Config::getDirClass('teams.name') }}" data-order="teams.name" data-dir="{{ Config::getDirOrder('teams.name') }}" >{{ trans('team::view.Checkpoint.List.Team name') }}</th>
                                        <th >{{ trans('team::view.Checkpoint.List.Evaluators') }}</th>
                                        <th class="sorting {{ Config::getDirClass('start_date') }}" data-order="start_date" data-dir="{{ Config::getDirOrder('start_date') }}" >{{ trans('team::view.Checkpoint.List.Date') }}</th>
                                        <th class="sorting {{ Config::getDirClass('result_date') }}" data-order="result_date" data-dir="{{ Config::getDirOrder('result_date') }}" >{{ trans('team::view.Checkpoint.List.Checkpoint Date') }}</th>
                                        <th  class="width-70" ></th>
                                   </tr>
                                </thead>
                                <tbody>
                                    <tr class="filter-input-grid">
                                        <td>&nbsp;</td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid' name="filter[checkpoint_type.name]" value="{{ Form::getFilterData('checkpoint_type.name') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid' name="filter[checkpoint_time.check_time]" value="{{ Form::getFilterData('checkpoint_time.check_time') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid' name="filter[teams.name]" value="{{ Form::getFilterData('teams.name') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                                </div>
                                            </div>
                                        </td>
                                        <td></td>
                                       
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    
                                                </div>
                                            </div>
                                        </td>
                                        
                                    </tr>
                                    @if(count($collectionModel) > 0)
                                    @foreach($collectionModel as $item)
                                    <tr role="row" class="odd">
                                        <td rowspan="1" colspan="1" >{{ $item->id }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->checkpoint_type_name }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->check_time }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->team_name }}</td>
                                        <td rowspan="1" colspan="1" >{{ CheckpointPermission::getInstance()->findEvaluator($item, $curEmp)->email }}</td>
                                        <td rowspan="1" colspan="1" >{{ date('Y/m/d',strtotime($item->start_date)) }} - {{ date('Y/m/d',strtotime($item->end_date)) }}</td>
                                        <td rowspan="1" colspan="1" >
                                            @if ($item->count_make)
                                            {{ date('Y/m/d h:i:s',strtotime($item->result_date)) }}
                                            @else
                                            {{ trans('team::view.Checkpoint.Made.Not make') }}
                                            @endif
                                        </td>
                                        <td  rowspan="1" colspan="1" >
                                            @if ($item->count_make)
                                            <a class="btn-edit" target="_blank" title="{{ trans('team::view.Xem lại checkpoint')}}" href="{{route('team::checkpoint.detail', ['id' => $item->result_id])}}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            @else
                                            <a class="btn-edit" target="_blank" title="{{ trans('team::view.Làm checkpoint')}}" href="{{route('team::checkpoint.welcome', ['token' => $item->token, 'id' => $item->id])}}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            @endif
                                            
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr><td colspan="13" class="text-align-center"><h2>{{trans('sales::view.No result not found')}}</h2></td></tr>
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
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
<div class="modal modal-primary" id="modal-clipboard">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('sales::view.Notification') }}</h4>
            </div>
            <div class="modal-body">
                <p>{{ trans('team::messages.Text notification copy clipboard')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ trans('sales::view.Close') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection
<!-- Styles -->
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script src="{{ asset('team/js/checkpoint/list.js') }}"></script>
@endsection
