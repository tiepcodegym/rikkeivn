<?php
use Carbon\Carbon;
?>

<p><b>{{ trans('manage_time::view.List of employees on the business trip') }}</b></p>
@if (isset($isAllowEdit) && $isAllowEdit)
<div class="row">
    <div class="col-sm-3">
        <div style="margin-top:5px">
            <label class="checkbox-inline"><input type="checkbox" id="checkbox-serach-employee-type">Tìm được cả nhân đã nghỉ việc</label>
        </div>
    </div>
    <div class="col-sm-8 request-form-group">
        <div class="input-box">
            <select name="" class="form-control select-search-employee" id="search_employee_ot"
                data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}"
                multiple>
            </select>
        </div>
    </div>
    <div class="col-sm-1 request-form-group">
        <div class="input-box">
            <a class="btn btn-success" id="btn_add_employee_ot" data-url="{{ route('manage_time::profile.mission.get-working-time-employees') }}">
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
<!-- Table người cùng đi công tác -->
<div class="table-responsive ot-table-responsive">
    <table class="table edit-table table-striped table-grid-data table-responsive table-hover table-bordered table-condensed" id="table_supplement_employees">
        <thead>
            <tr class="info">
                <th class="col-width-60">{{ trans('manage_time::view.Employee code') }}</th>
                <th class="col-width-90">{{ trans('manage_time::view.Employee Name') }}</th>
                <th class="col-width-80">{{ trans('manage_time::view.Out date') }}</th>
                <th class="col-width-80">{{ trans('manage_time::view.On date') }}</th>
                <th class="col-width-80">{{ trans('manage_time::view.Number of days business trip') }}</th>
                @if (isset($isAllowEdit) && $isAllowEdit)
                <th class="col-width-100"></th>
                @endif
            </tr>
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
    <div class="error ot-error hidden" id="error_no_employee">Danh sách nhân viên đi công tác không được để trống</div>
</div>
<div class="modal fade" data-backdrop="static" tabindex="-1" data-keyboard="false" id="addEmp" role="dialog">
    <form id="addEmpForm">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h3 class="box-title">{{ trans('ot::view.Detailed registration information') }}</h3>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 ot-form-group">
                            <label class="control-label">{{ trans('ot::view.Employee Name') }}<em class="input-required">(*)</em></label>
                            <select class="form-control" id="emp_list" name="emp_list"  disabled="" style="width: 100%;">
                            </select>
                            <div class="errorTxt"></div>
                        </div>
                        <div class="col-md-6 ot-form-group">
                            <label class="control-label">{{ trans('ot::view.Employee code') }}</label>
                            <div class="input-box">
                                <input type="hidden" id="add_emp_id" />
                                <input type="text" id="add_register_code" data-id="" value="" class="form-control" disabled="">
                            </div>
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6 start ot-form-group">
                            <label class="control-label">{{ trans('ot::view.OT from') }}<em class="input-required error">(*)</em></label>
                            <div id="datetimepicker_add_start">
                                <input type='text' class="form-control required" name="add_time_start" id="add_time_start"
                                       data-toggle="tooltip" data-placement="top" data-html="true"/>
                            </div>
                            <div class="errorTxt add_time_start-error error">Không được để trống</div>
                            <div class="errorTxt add_time_start-compare-error error hidden">Thời gian bắt đầu phải lớn hơn thời gian kết thúc</div>
                        </div>
                        <div class="col-md-6 end ot-form-group">
                            <label class="control-label">{{ trans('ot::view.OT to') }}<em class="input-required error">(*)</em></label>
                            <div id="datetimepicker_add_end">
                                <input type='text' class="form-control required" name="add_time_end" id="add_time_end"
                                       data-toggle="tooltip" data-placement="top"  data-html="true"/>
                            </div>
                            <div class="errorTxt add_time_end-error error">Không được để trống</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-confirm pull-right">{{ trans('ot::view.Save') }}</button>
                    <button type="button" class="btn btn-default btn-cancel pull-left" data-dismiss="modal">{{ trans('ot::view.Close') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
