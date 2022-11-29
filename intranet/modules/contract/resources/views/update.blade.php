<?php

use Rikkei\Team\Model\Employee;
use Carbon\Carbon;
$selTypeContract = old('sel_contract_type') ? old('sel_contract_type') : $collectionModel->type;
$employee_id = old('sel_employee_id') ? old('sel_employee_id') : $collectionModel->employee_id;
$startAt = old('txt_start_at') ? old('txt_start_at') : Carbon::parse($collectionModel->start_at)->format('dd-mm-Y');
$endAt = old('txt_end_at') ? old('txt_end_at') : ($collectionModel->end_at ? Carbon::parse($collectionModel->end_at)->format('dd-mm-Y') : '');
?>
@extends('layouts.default')
@section('title')
{{trans('contract::view.Edit contract')}}
@endsection
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
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
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-contract-edit" method="POST" action="{{route('contract::manage.contract.update',['id'=>$collectionModel->id])}}" class="form-horizontal  has-valid" autocomplete="off">
                    {!! csrf_field() !!}
                    <input type="hidden" value="{{$collectionModel->id}}" name="id" />
                    <div class="row">
                        <div class="col-md-6 @if (isset($collectionModel->confirmExpire->id)) border_right @endif">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="sel_employee_id" class="col-sm-3 control-label required  padding-right-0">
                                            {{trans('contract::vi.employee name')}} <em>*</em>
                                        </label>
                                        <div class="col-md-9">
                                            <input type="hidden" value="{{$employee_id}}" name="sel_employee_id" />
                                            <label class="control-label text-bold">{{$employeeInfo->name ? $employeeInfo->name : ''}}</label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="sel_contract_type" class="col-sm-3 control-label required  padding-right-0">
                                            {{trans('contract::vi.contract type')}} <em>*</em>
                                        </label>
                                        <div class="col-md-9">
                                            <select class="form-control" id='sel_contract_type' name="sel_contract_type" required="">
                                                <option value=""> -- {{trans('contract::vi.Type contract')}} --</option>
                                                @foreach($allTypeContract as $typeContractCode => $typeContractValue)
                                                <option {{$selTypeContract == $typeContractCode ?  'selected' : ''}}  value="{{$typeContractCode}}">{{$typeContractValue}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="txt_start_at" class="col-sm-3 control-label required  padding-right-0">
                                            {{trans('contract::vi.start at')}} <em>*</em>
                                        </label>
                                        <div class="col-md-3">
                                            <input name="txt_start_at" class="form-control input-field datetimepicker" type="text" id="txt_start_at" value="{{$startAt}}" required="" />
                                            <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
                                        </div>
                                        <label for="txt_end_at" class="col-sm-3 control-label required">
                                            {{trans('contract::vi.end at')}} <em>*</em>
                                        </label>
                                        <div class="col-md-3">
                                            <input {{$selTypeContract == 5 ? 'disabled' :''}} name="txt_end_at" class="form-control input-field datetimepicker" type="text" id="txt_end_at" value="{{$endAt}}" />
                                            <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
                                        </div>
                                    </div>

                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-md-9 col-md-offset-3 text-center">
                                            <button type="submit" class="btn btn-success"> {{trans('contract::view.Save')}}</button>
                                            <a href="{{ url()->previous() }}" class="btn btn-default">{{trans('contract::view.Go back')}}</a>
                                        </div>
                                    </div>

                                </div>
                            </div>
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
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script type="text/javascript">
jQuery(document).ready(function ($) {
    $('input.datetimepicker').datetimepicker({
        format: 'DD-MM-YYYY',
    });
    $('#form-contract-edit').validate();

    $('#sel_contract_type').on('change', function () {
        var value = $(this).val();
        $('#txt_end_at').removeAttr('disabled');
        if (parseInt(value) == 5)
        {
            $('#txt_end_at').val('');
            $('#txt_end_at').attr('disabled', true);
        }
    });
});
</script>
@endsection
