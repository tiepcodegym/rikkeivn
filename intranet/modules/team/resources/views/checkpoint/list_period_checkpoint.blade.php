@extends('layouts.default')
@section('title')
    {{ trans('team::view.List of Checkpoint periods') }}
@endsection
@section('content')
<?php

use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Core\View\MobileDetect;
use Rikkei\Team\View\CheckpointPermission;

$detectMobile = new MobileDetect();
$deviceIos = $detectMobile->isiOS();
?>
@if (session('status-error'))
    <div class="alert alert-warning">
        {{ session('status-error') }}
    </div>
@endif
@if (session('status-success'))
    <div class="alert alert-success">
        {{ session('status-success') }}
    </div>
@endif
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
                                <button class="btn btn-success btn-add-period-checkpoint" data-toggle="modal" data-target="#add-period-checkpoint">
                                    <span><i class="fa fa-plus"></i></span>
                                    <span>{!! trans('team::view.Add period checkpoint') !!}<i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
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
                                        <th class="sorting {{ Config::getDirClass('id') }}" data-order="id" data-dir="{{ Config::getDirOrder('id') }}" >{{ trans('team::view.Checkpoint.List.Id') }}</th>
                                        <th class="sorting {{ Config::getDirClass('checkpoint_time.check_time') }}" data-order="checkpoint_time.check_time" data-dir="{{ Config::getDirOrder('checkpoint_time.check_time') }}" >{{ trans('team::view.Checkpoint.List.Time') }}</th>
                                        <th class="sorting {{ Config::getDirClass('checkpoint_time.created_at') }}" data-order="checkpoint_time.created_at" data-dir="{{ Config::getDirOrder('checkpoint_time.created_at') }}" >{{ trans('team::view.Checkpoint.List.Created at') }}</th>
                                        <th >{{ trans('team::view.Action') }}</th>
                                   </tr>
                                </thead>
                                <tbody>
                                    <tr class="filter-input-grid">
                                        <td>&nbsp;</td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select style="width:150px" name="filter[checkpoint_time.id]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                                        <option value="">&nbsp;</option>
                                                        @foreach($checkpointTime as $time)
                                                            <option value="{{ $time->id }}"<?php
                                                                if ($time->id == Form::getFilterData('checkpoint_time.id')): ?> selected<?php endif; 
                                                                    ?>>{{ $time->check_time }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @if(count($collectionModel) > 0)
                                    @foreach($collectionModel as $item)
                                    <tr role="row" class="odd">
                                        <td rowspan="1" colspan="1" >{{ $item->id }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->check_time }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->created_at->format('d-m-Y') }}</td>
                                        <td class="action-checkpoint-time" rowspan="1" colspan="1" data-id="{{ $item->id }}" data-time="{{ $item->check_time }}">
                                            <button class="btn-edit edit-checkpoint-time" title="{{ trans('team::view.Edit') }}">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn-delete del-checkpoint-time" title="{{ trans('team::view.Delete') }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
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
<!--modal confirm delete.-->
<div class="modal modal-danger" tabindex="-1" role="dialog" id="modal-confirm-del">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">{!! trans('team::view.Confirm delete') !!}</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -30px;">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{!! trans('team::messages.Are you sure want to delete?') !!}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline button-modal-confirm-checkpoint" data-url="{!! route('team::checkpoint.period.delete') !!}">{!! trans('team::view.Delete') !!}</button>
                <button type="button" class="btn btn-outline" data-dismiss="modal">{!! trans('team::view.Cancel') !!}</button>
            </div>
        </div>
    </div>
</div>

@include('team::checkpoint.include.modal.add_period_checkpoint')
@include('team::checkpoint.include.modal.edit_period_checkpoint')
@endsection
<!-- Styles -->
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/eonasdan-bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" rel="stylesheet"/>
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script src="{{ asset('team/js/checkpoint/list.js') }}"></script>
<script type="text/javascript">
    $(function () {
        $('.datetimepicker1-checkpoint').datetimepicker({
            viewMode: 'years',
            format: "YYYY",
        });
        moment.updateLocale('en', null);
    });
</script>
@endsection
