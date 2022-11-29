<?php

use Carbon\Carbon;
use Rikkei\Team\Model\Employee;
?>
@extends('layouts.default')
@section('title')
{{trans('contract::view.Detail contract')}}
@endsection
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
@endsection

@section('content')
@include('contract::message-alert')
<style>
    .padding-right-0
    {
        padding-right: 0;
    }
    .border_right {
        border-right: 1px solid;
    }
    .form-control[readonly] {
        background: white;
    }
</style>
<form id="form-contract-edit" class="form-horizontal  has-valid" >
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6 @if (isset($collectionModel->confirmExpire->id)) border_right @endif">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="sel_employee_id" class="col-sm-3 control-label ">
                                            {{trans('contract::vi.employee name')}}:
                                        </label>
                                        <div class="col-md-9">
                                            <label class="control-label text-bold">{{$employeeInfo->name}}</label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="sel_contract_type" class="col-sm-3 control-label required">
                                            {{trans('contract::vi.contract type')}}:
                                        </label>
                                        <div class="col-md-9">
                                            <label class="control-label text-bold">
                                                {{isset($allTypeContract[$collectionModel->type]) ? $allTypeContract[$collectionModel->type] : ''}}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="txt_start_at" class="col-sm-3 control-label required">
                                            {{trans('contract::vi.start at')}}:
                                        </label>
                                        <div class="col-md-3">
                                            <label class="control-label text-bold">
                                                {{Carbon::parse($collectionModel->start_at)->format('d-m-Y')}}
                                            </label>
                                        </div>
                                        <label for="txt_end_at" class="col-sm-3 control-label required">
                                            {{trans('contract::vi.end at')}}:
                                        </label>
                                        <div class="col-md-3">
                                            <label class="control-label text-bold">
                                                {{$collectionModel->end_at ? Carbon::parse($collectionModel->end_at)->format('d-m-Y')  :''}}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($isPermissionEdit)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-md-9 col-md-offset-3 text-center">
                                            <a href="{{route('contract::manage.contract.edit',['id'=>$collectionModel->id])}}" class="btn btn-success"><i class='fa fa-edit'></i> {{trans('contract::view.Edit')}}</a>
                                            <a href="{{ URL::route('contract::manage.contract.index',['tab'=>'all']) }}" class="btn btn-default">{{trans('contract::view.Go back')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if (isset($collectionModel->confirmExpire->id))
                                <p style="padding-top: 7px">{{trans('contract::view.Staff feedback on the contract')}}:
                                    <span class="label {{ $bgText[$collectionModel->confirmExpire->type] }}">{{$allTypeContractExpire[$collectionModel->confirmExpire->type]}}</span>
                                </p>
                                <p>{{trans('contract::view.Note of employee')}}: <textarea class="form-control" rows="4" readonly>{{ $collectionModel->confirmExpire->note }}</textarea></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('script')
@endsection
