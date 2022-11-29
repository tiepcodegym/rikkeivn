<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View;
    use Rikkei\Team\View\Permission;
    use Rikkei\Team\Model\Team; 
    use Rikkei\Team\Model\Employee;
    use Rikkei\Ot\Model\OtRegister;
    
    $statusUnapprove = OtRegister::WAIT;
    $statusApproved = OtRegister::DONE;
    $statusDisapprove = OtRegister::REJECT;
    $statusCancel = OtRegister::REMOVE;
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h3 class="box-title">{{ trans('ot::view.Register information of OT') }}</h3>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="box-body">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <?php
                                    switch ($registerInfo->status) {
                                        case $statusDisapprove:
                                            $classStatus = 'callout-disapprove';
                                            $status = trans('ot::view.Rejected Label');
                                            break;
                                        case $statusUnapprove:
                                            $classStatus = 'callout-unapprove';
                                            $status = trans('ot::view.Unapproved Label');
                                            break;
                                        case $statusApproved:
                                            $classStatus = 'callout-approved';
                                            $status = trans('ot::view.Approved Label');
                                            break;
                                        default:
                                            $classStatus = 'callout-cancelled';
                                            $status = trans('ot::view.Remove Label');
                                            break;
                                    }
                                ?>
                                <div class="ot-callout {{ $classStatus }}">
                                    <p class="text-center"><strong>{{ $status }}</strong></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 ot-form-group">
                                <label class="control-label ot-label">{{ trans('ot::view.Applicant') }}</label>
                                <div class="input-box">
                                    <input type="text" id="register_id" value="{{ $registerInfo->employee_name }}" 
                                           data-id="{{ $registerInfo->employee_id }}" class="form-control" name="register_id" disabled="">
                                </div>
                            </div>
                            <div class="col-md-6 ot-form-group">
                                <label class="control-label ot-label">{{ trans('ot::view.Employee code') }}</label>
                                <div class="input-box">
                                    <input type="text" id="register_code" value="{{ $registerInfo->employee_code }}" class="form-control" name="register_code" disabled="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 ot-form-group">
                                <label class="control-label ot-label">{{ trans('ot::view.Position') }}</label>
                                <div class="input-box">
                                    <input type="text" value="{{ $registerInfo->role_name }}" class="form-control" name="" disabled="">
                                </div>
                            </div>
                            <div class="col-md-6 ot-form-group">
                                <label class="control-label ot-label">{{ trans('ot::view.Register Date') }}</label>
                                <div class='input-group date' id='datetimepicker_register'>
                                    <input type='text' class="form-control" name="time_register"  data-date-format="DD-MM-YYYY" id="time_register"  disabled=""                                    
                                           value="{{ $registerInfo->created_at != null ? Carbon::createFromFormat('Y-m-d H:i:s', $registerInfo->created_at)->format('d-m-Y') : Carbon::now()->format('d-m-Y') }}" />
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 ot-form-group">
                                <div>
                                    <label class="control-label ot-label">{{ trans('ot::view.OT Project') }}<em class="input-required">*</em></label>
                                    <select style="width: 100%;" id="project_list" name="project_list" class="project_list form-control ot-select-2" disabled>
                                        <option value="default">&nbsp;</option>
                                        @if ($empProjects)
                                            @foreach ($empProjects as $projs)                                
                                                <option value="{{ $projs->project_id }}" {{ $registerInfo->projs_id == $projs->project_id ? ' selected' : '' }}>
                                                    {{ $projs->projName }}</option>                              
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 ot-form-group">
                                <label class="control-label ot-label">{{ trans('ot::view.Approver') }}</label>        
                                <input type="hidden" class="form-control" id="leader_id" name="leader_id" value="{{ $registerInfo->approver }}">                        
                                <select style="width: 100%;" id="leader_input" name="leader_input" class="project_list form-control ot-select-2" disabled="">
                                    <?php 
                                        $checkApprover = false;
                                        if (isset($approverByProject) && count($approverByProject) && $approverByProject) {
                                            foreach ($approverByProject as $item) {
                                                if ($item->emp_id == $registerInfo->approver_id) {
                                                    $checkApprover = true;
                                                    break;
                                                }
                                            }
                                        } elseif (isset($approverForNotSoftDev) && count($approverForNotSoftDev) && $approverForNotSoftDev) {
                                            foreach ($approverForNotSoftDev as $item) {
                                                if ($item->emp_id == $registerInfo->approver_id) {
                                                    $checkApprover = true;
                                                    break;
                                                }
                                            }
                                        }
                                    ?>
                                    @if(!$checkApprover)
                                        <option value="{{ $registerInfo->approver_id }}" selected>{{ $registerInfo->approver_name . ' (' . preg_replace('/@.*/', '',$registerInfo->approver_email) . ')' }}</option>
                                    @endif
                                    @if (isset($approverByProject) && $approverByProject)
                                        @foreach ($approverByProject as $approver)
                                            <option value="{{ $approver->emp_id }}" {{ $approver->emp_id == $registerInfo->approver ? ' selected' : '' }}>
                                                {{ $approver->emp_name . ' (' . preg_replace('/@.*/', '',$approver->emp_email) . ')' }}</option>
                                        @endforeach
                                    @elseif ($approverForNotSoftDev)
                                        @foreach ($approverForNotSoftDev as $approver)
                                            <option value="{{ $approver->emp_id }}" {{ $approver->emp_id == $registerInfo->approver ? ' selected' : '' }}>
                                                {{ $approver->emp_name . ' (' . preg_replace('/@.*/', '',$approver->emp_email) . ')' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 ot-form-group">
                                <label class="control-label ot-label">{{ trans('ot::view.OT from') }} <em class="input-required">*</em></label>
                                <div class='input-group date' id='datetimepicker_start'>
                                    <input type='text' class="form-control required" name="time_start"  data-date-format="DD-MM-YYYY HH:mm" id="time_start" disabled
                                           data-toggle="tooltip" data-placement="top" data-html="true" data-inputmask="'alias': 'yyyy-mm-dd'" data-mask=""
                                           value="{{ $registerInfo->start_at != null ? Carbon::createFromFormat('Y-m-d H:i:s', $registerInfo->start_at)->format('d-m-Y H:i') : '' }}" />
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>    
                                <div class="errorTxt"></div>
                            </div>
                            <div class="col-md-6 ot-form-group">
                                <label class="control-label ot-label">{{ trans('ot::view.OT to') }}<em class="input-required">*</em></label>
                                <div class='input-group date' id='datetimepicker_end'>
                                    <input type='text' class="form-control required" name="time_end"  data-date-format="DD-MM-YYYY HH:mm" id="time_end" disabled
                                           data-toggle="tooltip" data-placement="top" data-html="true"
                                           value="{{ $registerInfo->end_at != null ? Carbon::createFromFormat('Y-m-d H:i:s', $registerInfo->end_at)->format('d-m-Y H:i') : '' }}" disabled=""/>
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                                <div class="errorTxt"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 ot-form-group">
                                <label class="control-label ot-label">{{ trans('ot::view.Shift break time') }}</label>
                                <div class="input-box">
                                    <input type="text" min="0" max="14" class="form-control" name="relax" id="relax" value="{{ $registerInfo->time_break ? $registerInfo->time_break : '0.00'}}" readonly="">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 ot-form-group">
                                <label class="control-label ot-label">{{ trans('ot::view.OT reason') }}<em class="input-required">*</em></label>
                                <div class="input-box reason">
                                    <textarea class="form-control" rows="4" name="reason" id="reason" required="" disabled style="min-height: 100px; max-width: 100%; min-width: 100%;">{{ $registerInfo->reason }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <p><b>{{ trans('ot::view.OT employees') }}</b></p>
                            </div>
                        </div>

                        <!-- Table người cùng làm thêm -->
                        <div class="table-responsive ot-table-responsive">
                            <table class="table-striped table-grid-data table-responsive table-hover table-bordered table-condensed" id="table_ot_employees">
                                <thead>
                                    <th class="col-width-60">{{ trans('ot::view.Employee code') }}</th>
                                    <th class="col-width-90">{{ trans('ot::view.Employee Name') }}</th>
                                    <th class="col-width-80">{{ trans('ot::view.OT from') }}</th>
                                    <th class="col-width-80">{{ trans('ot::view.OT to') }}</th>
                                    <th class="col-width-60">{{ trans('ot::view.OT paid?') }}</th>
                                    <th class="col-width-40">{{ trans('ot::view.Shift break time') }}</th>
                                </thead>
                                <tbody>                           
                                    @if ($registerInfo->id)
                                        @foreach ($tagEmployeeInfo as $emp)
                                            <tr id="{{ $emp->employee_id }}">
                                                <td class="emp_code">{{ $emp->employee_code }}</td>
                                                <td class="emp_name">{{ $emp->name }}</td>
                                                <td class="start_at">{{ $emp->start_at != null ? Carbon::createFromFormat('Y-m-d H:i:s', $emp->start_at)->format('d-m-Y H:i') : '' }}</td>
                                                <td class="end_at">{{ $emp->end_at != null ? Carbon::createFromFormat('Y-m-d H:i:s', $emp->end_at)->format('d-m-Y H:i') : '' }}</td>
                                                <td class="ot_paid"><center><input type="checkbox" {{ $emp->is_paid == 1 ? 'checked=""' : '' }} disabled></center></td>
                                                <?php
                                                    $timeBreak = 0;
                                                    if ($emp->time_break) {
                                                        $timeBreak = $emp->time_break;
                                                    }
                                                    $timeBreak = round($timeBreak, 2);
                                                ?>
                                                <td class="relax">{{ $timeBreak }}</td>
                                            </tr>
                                        @endforeach
                                    @endif                        
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default pull-left" data-dismiss="modal" style="margin-left: 10px;">{{ trans('ot::view.Close') }}</button>
            <a class="pull-right" href="{{ route('ot::ot.detail', ['id' => $registerInfo->id]) }}"><button type="button" class="btn btn-primary">{{ trans('ot::view.See details') }}</button></a>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->