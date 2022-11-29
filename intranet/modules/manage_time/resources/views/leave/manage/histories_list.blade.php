<?php
    use Rikkei\Core\View\Form;
?>

@extends('layouts.default')

@section('title')
    {{ trans('manage_time::view.Leave day histories changes') }}
@endsection



@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
@endsection

@section('content')
    <div class="box box-info">
        <div class="box-body">
            <a class="btn btn-primary" href="{{ route('manage_time::admin.manage-day-of-leave.index') }}">
                <i class="fa fa-fw fa-long-arrow-left"></i>
                <span>{{ trans('manage_time::view.Back') }}</span>
            </a>
            <div class="pull-right">
                @include('team::include.filter')
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table-reason">
                <thead>
                    <tr>
                        <th class="width-220">{{ trans('manage_time::view.Date time') }}</th>
                        <th >{{ trans('manage_time::view.Employee code') }}</th>
                        <th >{{ trans('manage_time::view.Employee name') }}</th>
                        <th >{{ trans('manage_time::view.Type') }}</th>
                        <th >{{ trans('manage_time::view.Changes') }}</th>
                        <th >{{ trans('manage_time::view.Created by') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="filter-input-grid">
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" class='form-control filter-grid' name="filter[leave_day_histories.created_at]" value="{{ Form::getFilterData('leave_day_histories.created_at') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" class='form-control filter-grid' name="filter[employees.employee_code]" value="{{ Form::getFilterData('employees.employee_code') }}" placeholder="{{ trans('team::view.Search') }}..." />
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
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <select name="filter[leave_day_histories.type]]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                        <option value="">&nbsp;</option> 
                                        @foreach ($typeLabels as $type => $label)
                                        <option value="{{ $type }}" {{ $type == Form::getFilterData('leave_day_histories.type') ? 'selected' : '' }}>{{ $label }}</option> 
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </td>
                        <td>&nbsp;</td>
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" class='form-control filter-grid' name="filter[owner.email]" value="{{ Form::getFilterData('owner.email') }}" placeholder="{{ trans('team::view.Search') }}..."  />
                                </div>
                            </div>
                        </td>
                    </tr>
                    @if(isset($collectionModel) && count($collectionModel))
                        @foreach($collectionModel as $item)
                            <tr >
                                <td><a title="{{ trans('manage_time::view.View detail') }}" href="{{ route('manage_time::admin.manage-day-of-leave.histories.detail', $item->id) }}">{{ $item->created_at }}</a></td>
                                <td>{{ $item->employee_code }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->getType() }}</td>
                                <td>{!! $item->getContent() !!}</td>
                                <td>{{ $item->owner_email }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">
                                <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
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
@endsection



