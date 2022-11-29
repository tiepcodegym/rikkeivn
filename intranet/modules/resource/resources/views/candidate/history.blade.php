@extends('layouts.default')
@section('title')
    {{ trans('resource::view.Candidate.History.Candidate history action list') }}
@endsection
@section('content')
<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Resource\View\getOptions;
use Illuminate\Support\Facades\URL;
use Rikkei\Team\View\Permission;

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
                                        <th class="col-md-1 sorting {{ Config::getDirClass('candidate_id') }}" data-order="candidate_id" data-dir="{{ Config::getDirOrder('candidate_id') }}" >{{ trans('resource::view.Candidate.History.Candidate Id') }}</th>
                                        <th class="sorting {{ Config::getDirClass('name') }}" data-order="name" data-dir="{{ Config::getDirOrder('name') }}" >{{ trans('resource::view.Candidate.History.Employee') }}</th>
                                        <th >{{ trans('resource::view.Candidate.History.Action') }}</th>
                                        <th >{{ trans('resource::view.Candidate.History.Note') }}</th>
                                        <th class="sorting {{ Config::getDirClass('recruit_process.created_at') }}" data-order="recruit_process.created_at" data-dir="{{ Config::getDirOrder('recruit_process.created_at') }}" >{{ trans('resource::view.Candidate.History.Created at') }}</th>
                                   </tr>
                                </thead>
                                <tbody>
                                    <tr class="filter-input-grid">
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid' name="filter[candidate_id]" value="{{ Form::getFilterData('candidate_id') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid' name="filter[employees.name]" value="{{ Form::getFilterData('employees.name') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                                </div>
                                            </div>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    @if(count($collectionModel) > 0)
                                    @foreach($collectionModel as $item)
                                    <tr role="row" class="odd">
                                        <td rowspan="1" colspan="1" >{{ $item->candidate_id }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->name }}</td>
                                        <td rowspan="1" colspan="1" >
                                        <?php $actions = json_decode($item->action); ?>

                                        @if (count($actions))
                                        @foreach ($actions as $action)
                                        {{ nl2br($action) }} <br>
                                        @endforeach
                                        @endif
                                        </td>
                                        <td rowspan="1" colspan="1" >{{ $item->note }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->created_at}}</td>
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
<div class="modal " id="modal-channel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">

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
<link href="{{ asset('common/css/style.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script src="{{ asset('resource/js/request/list.js') }}"></script>

@endsection
