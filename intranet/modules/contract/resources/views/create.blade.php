<?php

use Rikkei\Team\Model\Employee;

$selTypeContract = old('sel_contract_type') ? old('sel_contract_type') : '';
$employeeChosen = null;
if (old('sel_employee_id'))
    $employeeChosen = Employee::getEmpById(old('sel_employee_id'));
?>
@extends('layouts.default')
@section('title')
{{trans('contract::vi.create contract')}}
@endsection
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
@endsection

@section('content')
@include('contract::message-alert')
<style>
    .padding-right-0
    {
        padding-right: 0;
    }
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-contract-edit" method="post" action="{{route('contract::manage.contract.save')}}" class="form-horizontal  has-valid" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="sel_employee_id" class="col-sm-3 control-label required padding-right-0">
                                            {{trans('contract::vi.employee name')}} <em>*</em>
                                        </label>
                                        <div class="col-md-9">
                                            <select 
                                                class="form-control select-search" 
                                                id="sel_employee_id"  
                                                name="sel_employee_id" 
                                                required=""
                                                data-remote-url="{{ URL::route('contract::manage.contract.employee.search.ajax') }}"
                                                />
                                            <option value="{{$employeeChosen?$employeeChosen->id:''}}">{{$employeeChosen ? $employeeChosen->name:''}}</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="sel_contract_type" class="col-sm-3 control-label required padding-right-0">
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
                                        <label for="txt_start_at" class="col-sm-3 control-label required padding-right-0">
                                            {{trans('contract::vi.start at')}} <em>*</em>
                                        </label>
                                        <div class="col-md-3">
                                            <input name="txt_start_at" class="form-control input-field datetimepicker" type="text" id="txt_start_at" value="{{old('txt_start_at')}}" required="" />
                                            <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
                                        </div>
                                        <label for="txt_end_at" class="col-sm-3 control-label required">
                                            {{trans('contract::vi.end at')}} <em>*</em>
                                        </label>
                                        <div class="col-md-3">
                                            <input {{old('sel_contract_type') == 5 ? 'disabled' : ''}} name="txt_end_at" class="form-control input-field datetimepicker" type="text" id="txt_end_at" value="{{old('txt_end_at')}}" />
                                            <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-md-9 col-md-offset-3 text-center">
                                            <button type="submit" class="btn btn-success"> {{trans('contract::vi.create contract')}}</button>
                                            <a href="{{ URL::route('contract::manage.contract.index',['tab'=>'all']) }}" class="btn btn-default">{{trans('contract::view.Go back')}}</a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
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
    RKfuncion.select2.init();

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
