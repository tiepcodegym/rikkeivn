@extends('layouts.default')
<?php

use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Illuminate\Support\Facades\URL;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Checkpoint;
use Rikkei\Team\Model\CheckpointResult;

$filterEvalutor = Form::getFilterData('except', 'checkpoint.evaluator_id');
$filterCreated = Form::getFilterData('except', 'checkpoint_result.create_at');
$filterupdated = Form::getFilterData('except', 'checkpoint_result.update_at');
?>

@section('title')
    @if ($team && count($team))
        {{ trans('team::view.Checkpoint.Made.List of', ['team' => $team->name, 'checkpointTime' => $checkpointTime->check_time])}}
    @else
        {{ trans('team::view.Checkpoint.Made.List', ['checkpointTime' => $checkpointTime->check_time])}}
    @endif
@endsection

@section('content')

<div class="row view-css-page">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div >
                <div class="row">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="box-body">
                            @include('team::include.filter')
                        </div>
                        <div class="table-responsive">
                            <table id="example2" class="table table-bordered table-hover dataTable table-striped" role="grid" aria-describedby="example2_info">
                                <thead>
                                    <tr role="row">
                                        <th class="sorting {{ Config::getDirClass('id') }}" data-order="id" data-dir="{{ Config::getDirOrder('id') }}"  >{{ trans('team::view.Checkpoint.List.Id') }}</th>
                                        <th class="sorting {{ Config::getDirClass('emp_name') }}" data-order="emp_name" data-dir="{{ Config::getDirOrder('emp_name') }}"  >{{ trans('team::view.Checkpoint.Made.Name') }}</th>
                                        <th class="sorting {{ Config::getDirClass('total_point') }}" data-order="total_point" data-dir="{{ Config::getDirOrder('total_point') }}"  >{{ trans('team::view.Checkpoint.Made.point') }}</th>
                                        <th class="sorting {{ Config::getDirClass('created_at') }}" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}"  >{{ trans('team::view.Checkpoint.Made.Created at') }}</th>
                                        <th class="sorting {{ Config::getDirClass('leader_total_point') }}" data-order="leader_total_point" data-dir="{{ Config::getDirOrder('leader_total_point') }}" >{{ trans('team::view.Checkpoint.Made.Leader point') }}</th>
                                        <th class="sorting {{ Config::getDirClass('updated_at') }}" data-order="updated_at" data-dir="{{ Config::getDirOrder('updated_at') }}"  >{{ trans('team::view.Checkpoint.Made.Updated at') }}</th>
                                        <th class="sorting {{ Config::getDirClass('evaluator_id') }}" data-order="evaluator_id" data-dir="{{ Config::getDirOrder('evaluator_id') }}"  >{{ trans('team::view.Checkpoint.List.Evaluators') }}</th>
                                        <th tabindex="0"  rowspan="1" colspan="1" >{{ trans('sales::view.View css detail') }}</th>
                                   </tr>
                                </thead>
                                <tbody>
                                    <tr class="filter-input-grid">
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    
                                                    <select name="filter[except][checkpoint_result.create_at]]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                                        <option value="">&nbsp;</option> 
                                                         <option value="{{ Checkpoint::NOT_CREATED }}" {{ Checkpoint::NOT_CREATED == $filterCreated ? 'selected' : '' }}>{{ trans('team::view.Checkpoint.Made.Not make') }}</option>
                                                         <option value="{{ Checkpoint::CREATED }}" {{ Checkpoint::CREATED == $filterCreated ? 'selected' : '' }}>{{ trans('team::view.Checkpoint.Made.Make') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>&nbsp;</td>
                                        
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select name="filter[except][checkpoint_result.update_at]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                                        <option value="">&nbsp;</option>
                                                        <option value="{{ Checkpoint::NOT_CREATED }}" {{ Checkpoint::NOT_CREATED == $filterupdated ? 'selected' : '' }}>{{ trans('team::view.Checkpoint.Made.Not evaluated') }}</option>
                                                             <option value="{{ Checkpoint::CREATED }}" {{ Checkpoint::CREATED == $filterupdated ? 'selected' : '' }} >{{ trans('team::view.Checkpoint.Made.Evaluated') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                         <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select name="filter[except][checkpoint.evaluator_id]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                                        <option value="">&nbsp;</option>
                                                        @if (count($evalutors))
                                                            @foreach($evalutors as $evaId => $evaluator)
                                                            <option value="{{ $evaId }}" {{ $filterEvalutor == $evaId ? 'selected' : '' }}>{{ $evaluator }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @if(count($collectionModel))
                                    @foreach($collectionModel as $item)
                                     @if (in_array($item->emp_id, $evaluatedSelected))
                                    <tr role="row" class="odd">
                                        <td tabindex="0"  rowspan="1" colspan="1" >{{ $item->id }}</td>
                                        <td tabindex="0"  rowspan="1" colspan="1" >{{ $item->emp_name }}</td>
                                        <td tabindex="0"  rowspan="1" colspan="1" >{{ number_format($item->total_point,2) }}</td>
                                        <td tabindex="0"  rowspan="1" colspan="1" >
                                            {{ ($item->created_at) ? date('Y/m/d H:i:s',strtotime($item->created_at)) : trans('team::view.Checkpoint.Made.Not make') }}
                                        </td>
                                        <td tabindex="0"  rowspan="1" colspan="1" >{{ number_format($item->leader_total_point,2) }}</td>
                                        <td tabindex="0"  rowspan="1" colspan="1" >
                                            {{ ($item->leader_total_point != 0) ? date('Y/m/d H:i:s',strtotime($item->updated_at)) : trans('team::view.Checkpoint.Made.Not evaluated') }}
                                        </td>
                                        <td>{{ $item->eva }}</td>
                                        <td tabindex="0"  rowspan="1" colspan="1" >
                                            @if ($item->id)
                                            <a href="{{URL::route('team::checkpoint.detail', ['id' => $item->id])}}">{{ trans('team::view.Checkpoint.Made.View') }}</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                    @foreach($collectionModel as $item)
                                    @if (!in_array($item->emp_id, $evaluatedSelected))
                                    <tr role="row" class="odd">
                                        <td tabindex="0"  rowspan="1" colspan="1" >{{ $item->id }}</td>
                                        <td tabindex="0"  rowspan="1" colspan="1" >{{ $item->emp_name }}</td>
                                        <td colspan="6">{{trans('team::view.Checkpoint.Made.Have not in list evaluated')}}</td>
                                    </tr>
                                    @endif
                                    @endforeach
                                    @else
                                    <tr><td colspan="13" class="text-align-center"><h2>{{ trans('sales::view.No result not found')}}</td></tr></h2>
                                    @endif
                                </tbody>
                            </table>
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
@endsection
<!-- Styles -->
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
@endsection