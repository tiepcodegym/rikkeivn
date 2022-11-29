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
            <a class="btn btn-primary" href="{{ route('manage_time::admin.manage-day-of-leave.histories') }}">
                <i class="fa fa-fw fa-long-arrow-left"></i>
                <span>{{ trans('manage_time::view.Back') }}</span>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table-reason">
                <tbody>
                    <tr>
                        <td class="width-220">{{ trans('manage_time::view.Date time') }}</td>
                        <td>{{ $history->created_at }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('manage_time::view.Employee code') }}</td>
                        <td>{{ $history->employee_code }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('manage_time::view.Employee name') }}</td>
                        <td>{{ $history->name }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('manage_time::view.Type') }}</td>
                        <td>{{ $history->getType() }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('manage_time::view.Created by') }}</td>
                        <td>{{ $history->owner_email }}</td>
                    </tr>
                    <tr class="vertical-align-top" >
                        <td>{{ trans('manage_time::view.Changes') }}</td>
                        <td>{!! $history->getContent() !!}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection



