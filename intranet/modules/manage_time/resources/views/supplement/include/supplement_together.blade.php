<?php
use Carbon\Carbon;
?>

<p class="hidden"><b>Danh sách nhân viên đăng ký bổ sung công</b></p>
@if (isset($isAllowEdit) && $isAllowEdit)
<div class="row hidden">
    <div class="col-sm-11 request-form-group">
        <div class="input-box">
            <select name="" class="form-control select-search-employee" id="search_employee_ot" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple>
            </select>
        </div>
    </div>
    <div class="col-sm-1 request-form-group">
        <div class="input-box">
            <a class="btn btn-success" id="btn_add_employee_ot">
                <i class="fa fa-plus"></i>
            </a>
        </div>
    </div>
</div>
@endif
<div>
    <input type="hidden" name="table_data_emps" id="table_data_emps">
</div>
<br>        
<!-- Table người cùng BSC -->
<div class="table-responsive ot-table-responsive">
    <table class="hidden table edit-table table-striped table-grid-data table-responsive table-hover table-bordered table-condensed" id="table_supplement_employees">
        <thead>
            <th class="col-width-60">{{ trans('manage_time::view.Employee code') }}</th>
            <th class="col-width-90">{{ trans('manage_time::view.Employee Name') }}</th>
            <th class="col-width-80">{{ trans('manage_time::view.Out date') }}</th>
            <th class="col-width-80">{{ trans('manage_time::view.On date') }}</th>
            <th class="col-width-80">{{ trans('manage_time::view.Number of days business trip') }}</th>
            @if (isset($isAllowEdit) && $isAllowEdit)
            <th class="col-width-100"></th>
            @endif
        </thead>
        <tbody>                           
        @if ($registerRecord->id)
            @foreach ($tagEmployeeInfo as $emp)
            <tr id="{{ $emp->employee_id }}">
                <td class="emp_code">
                    <div class="emp_code_main">{{ $emp->employee_code }}</div>
                </td>
                <td class="emp_name">{{ $emp->name }}</td>
                <td class="start_at">{{ $emp->start_at != null ? Carbon::createFromFormat('Y-m-d H:i:s', $emp->start_at)->format('d-m-Y H:i') : '' }}</td>
                <td class="end_at">{{ $emp->end_at != null ? Carbon::createFromFormat('Y-m-d H:i:s', $emp->end_at)->format('d-m-Y H:i') : '' }}</td>
                <td class="number_days"></td>
                @if (isset($isAllowEdit) && $isAllowEdit)
                <td class="btn-manage">
                    <button type="button" class="btn btn-primary edit" onclick="editEmp({{ $emp->employee_id }})"><i class="fa fa-pencil-square-o"></i></button>
                    <button type="button" class="btn btn-delete delete" onclick="removeEmp({{ $emp->employee_id }})"><i class="fa fa-minus"></i></button>
                </td>
                @endif
            </tr>
            @endforeach
        @endif
        </tbody>
    </table>
    <div class="employee-exist"></div>
    <div class="error hidden" id="exist_time_lot_before_submit_error"></div>
    <div class="error ot-error hidden" id="error_no_employee">{{ trans('manage_time::message.The register OT list is required') }}</div>
</div>
<div class="modal fade" data-backdrop="static" tabindex="-1" data-keyboard="false" id="addEmp" role="dialog">
    <form id="addEmpForm">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h3 class="box-title">{{ trans('manage_time::view.Detailed registration information') }}</h3>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 ot-form-group">
                            <label class="control-label">{{ trans('manage_time::view.Employee name') }}<em class="input-required">(*)</em></label>
                            <select class="form-control" id="emp_list" name="emp_list"  disabled="" style="width: 100%;">
                            </select>
                            <div class="errorTxt"></div>
                        </div>
                        <div class="col-md-6 ot-form-group">
                            <label class="control-label">{{ trans('manage_time::view.Employee code') }}</label>
                            <div class="input-box">
                                <input type="text" id="add_register_code" data-id="" value="" class="form-control" disabled="">
                            </div>
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6 start ot-form-group">
                            <label class="control-label">{{ trans('manage_time::view.OT from') }}<em class="input-required error">(*)</em></label>
                            <div id="datetimepicker_add_start">
                                <input type='text' class="form-control required" name="add_time_start" id="add_time_start"
                                       data-toggle="tooltip" data-placement="top" data-html="true"/>
                            </div>
                            <div class="errorTxt add_time_start-error error">{{ trans('manage_time::view.This field is required') }}</div>
                            <div class="errorTxt add_time_start-compare-error error hidden">Thời gian bắt đầu phải lớn hơn thời gian kết thúc</div>
                        </div>
                        <div class="col-md-6 end ot-form-group">
                            <label class="control-label">{{ trans('manage_time::view.OT to') }}<em class="input-required error">(*)</em></label>
                            <div id="datetimepicker_add_end">
                                <input type='text' class="form-control required" name="add_time_end" id="add_time_end"
                                       data-toggle="tooltip" data-placement="top"  data-html="true"/>
                            </div>
                            <div class="errorTxt add_time_end-error error">{{ trans('manage_time::view.This field is required') }}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-confirm pull-right">{{ trans('manage_time::view.Save') }}</button>
                    <button type="button" class="btn btn-default btn-cancel pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
