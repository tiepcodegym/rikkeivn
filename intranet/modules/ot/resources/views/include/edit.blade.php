<div class="box-header with-border">
    @if ($pageType == 'create')
        <h3 class="box-title ot-box-title">{{ trans('ot::view.Register overtime') }}</h3>
    @else
        <h3 class="box-title ot-box-title">{{ trans('ot::view.View detail OT register') }}</h3>
    @endif
</div>
<?php
    use Illuminate\Support\Facades\URL;
    use Carbon\Carbon;
    use Rikkei\Ot\Model\OtRegister;
    use Rikkei\Ot\Model\OtBreakTime;
    use Rikkei\Ot\View\OtPermission;
    use Rikkei\Team\View\Permission;
    use Rikkei\Team\View\TeamList;
    use Rikkei\Core\View\View;
    use Rikkei\ManageTime\View\ManageTimeCommon;

    $teamsOption = TeamList::toOption(null, false, false);
    $statusUnapprove = OtRegister::WAIT;
    $statusApproved = OtRegister::DONE;
    $statusDisapprove = OtRegister::REJECT;
    $statusCancel = OtRegister::REMOVE;
    $notProject = with(new OtRegister())->getNotProject();
    /* create registration: set OT to = OT from => employee must be select OT to */
    if ($pageType == 'create') {
        $registerInfo->end_at = $registerInfo->start_at;
    }
?>
<!-- /.box-header -->
<div class="se-pre-con"></div>
<!-- form start -->
<form role="form" method="post" action="{{ URL::route('ot::ot.saveot') }}" class="" autocomplete="off" id="form-register" >
    {!! csrf_field() !!}
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1" >
            <div class="box-body">
                @if ($pageType == 'create')
                    <input type="hidden" name="form_id" id="form_id" value="{{ $registerInfo->id }}"/>
                    <input type="hidden" name="emp_id" id="emp_id" value="{{ $applicant->id }}"/>
                @else
                    <input type="hidden" name="form_id" id="form_id" value="{{ $registerInfo->id }}"/>
                    <input type="hidden" name="emp_id" id="emp_id" value="{{ $registerInfo->employee_id }}"/>
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
                @endif
                <div class="row">
                    <div class="col-md-6 ot-form-group">
                        <label class="control-label">{{ trans('ot::view.Applicant') }}</label>
                        <div class="input-box">
                            <input type="text" id="register_id" value="{{ $pageType == 'create'? $registrantInformation->employee_name : $registerInfo->employee_name }} ({{ $pageType == 'create' ? $registrantInformation->employee_email : $registerInfo->creator_email }})"
                                   data-id="{{ $pageType == 'create' ? $applicant->id : $registerInfo->employee_id }}" class="form-control" name="register_id" disabled="">
                        </div>
                    </div>
                    <div class="col-md-6 ot-form-group">
                        <label class="control-label">{{ trans('ot::view.Employee code') }}</label>
                        <div class="input-box">
                            <input type="text" id="register_code" value="{{ $pageType == 'create' ? $registrantInformation->employee_code : $registerInfo->employee_code }}" class="form-control" name="register_code" disabled="">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 ot-form-group">
                        <label class="control-label">{{ trans('ot::view.Position') }}</label>
                        <div class="input-box">
                            <input type="text" value="{{ $pageType == 'create' ? $registrantInformation->role_name : $registerInfo->role_name }}" class="form-control" name="" disabled="">
                        </div>
                    </div>
                    <div class="col-md-6 ot-form-group">
                        <label class="control-label">{{ trans('ot::view.Register Date') }}</label>
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
                            <label class="control-label">{{ trans('ot::view.OT Project') }} <em class="input-required">*</em></label>
                            <select style="width: 100%;" id="project_list" name="project_list" class="project_list form-control ot-select-2" {{ $isEditable ? '' : 'disabled' }}>
                                <option value="">&nbsp</option>
                                <option value="{{ OtRegister::KEY_NOTPROJECT_OT }}"
                                        @if ($pageType == 'edit' && $registerInfo->projs_id == '')
                                            selected
                                        @endif
                                >{{ data_get($notProject, OtRegister::KEY_NOTPROJECT_OT )}}</option>

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
                        <div>
                            <label class="control-label">{{ trans('ot::view.Approver') }} <em class="input-required">*</em></label>
                            <input type="hidden" class="form-control" id="leader_id" name="leader_id" value="{{ $registerInfo->approver }}">
                            <select style="width: 100%;" id="leader_input" name="leader_input" class="project_list form-control select-search-approver" {{ $isEditable ? '' : 'disabled' }} data-remote-url="{{ URL::route('ot::ot.ajax-search-approver') }}">
                                @if (isset($registerInfo) && $registerInfo->approver)
                                    <option value="{{ $registerInfo->approver }}" selected>{{ $registerInfo->approver_name . ' (' . preg_replace('/@.*/', '',$registerInfo->approver_email) . ')' }}</option>
                                @endif
                                @if (isset($suggestApprover) && $suggestApprover)
                                    <option value="{{ $suggestApprover->approver_id }}" selected>{{ $suggestApprover->approver_name . ' (' . preg_replace('/@.*/', '',$suggestApprover->approver_email) . ')' }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 ot-form-group">
                        <div>
                            <label class="control-label">{{ trans('manage_time::view.Related persons need notified') }}</label>
                            <div class="input-box">
                                <select name="related_persons_list[]" class="form-control select-search-employee" {{ $isEditable ? '' : 'disabled' }} data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee-can-approve', ['route' => 'manage_time::manage-time.manage.ot.approve']) }}" multiple>
                                    @if(isset($relatedPersonsList) && count($relatedPersonsList))
                                        @foreach($relatedPersonsList as $item)
                                            <option value="{{ $item->relater_id }}" selected>{{ $item->relater_name . ' (' . preg_replace('/@.*/', '',$item->relater_email) . ')' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 ot-form-group">
                        <label class="control-label">{{ trans('ot::view.OT from') }} <em class="input-required">*</em> <span class="fa fa-question-circle" data-toggle="tooltip" title="{!! trans('ot::view.Start date tooltip') !!}" data-html="true" ></span></label>
                        <div class='input-group date' id='datetimepicker_start'>
                            <input type='text' class="form-control required" name="time_start"  data-date-format="DD-MM-YYYY HH:mm" id="time_start" {{ $isEditable ? '' : 'disabled' }}
                                   data-toggle="tooltip" data-placement="top" data-inputmask="'alias': 'yyyy-mm-dd'" data-mask=""
                                   value="{{ $registerInfo->start_at != null ? Carbon::createFromFormat('Y-m-d H:i:s', $registerInfo->start_at)->format('d-m-Y H:i') : '' }}" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>    
                        <div class="errorTxt"></div>
                    </div>
                    <div class="col-md-6 ot-form-group">
                        <label class="control-label">{{ trans('ot::view.OT to') }}<em class="input-required">*</em> <span class="fa fa-question-circle" data-toggle="tooltip" title="{!! trans('ot::view.End date tooltip') !!}" data-html="true" ></span></label>
                        <div class='input-group date' id='datetimepicker_end'>
                            <input type='text' class="form-control required" name="time_end"  data-date-format="DD-MM-YYYY HH:mm" id="time_end" {{ $isEditable ? '' : 'disabled' }}
                                   data-toggle="tooltip" data-placement="top"
                                   value="{{ $registerInfo->end_at != null ? Carbon::createFromFormat('Y-m-d H:i:s', $registerInfo->end_at)->format('d-m-Y H:i') : '' }}" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                        <div class="errorTxt"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 ot-form-group ot-hidden" id="form_set_time_break">
                        <div class="input-box">
                            @if ($isEditable)
                            <a class="btn btn-success btn-set-time-break" style="margin-bottom: 10px;">{{ trans('ot::view.Set shift break time') }}</a>
                            @else
                            <label class="control-label">{{ trans('ot::view.Shift break time') }}</label>
                            @endif
                            <input type="text" class="form-control" name="total_time_break" id="total_time_break" value="{{ $registerInfo->time_break ? number_format($registerInfo->time_break, 2) : '0.00'}}" readonly="">
                            <input type="hidden" name="" id="check_change_date" value="0">
                            <input type="hidden" name="" id="check_set_break_time" value="{{ (isset($breakTimeByRegister) && count($breakTimeByRegister)) ? '1' : '0' }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 ot-form-group">
                        <label class="bs-switch">
                            <div>{{ trans('ot::view.Is OT onsite?') }}&nbsp; <span class="fa fa-question-circle" data-toggle="tooltip" title="{!! trans('ot::view.Registration onsite the timesheet is not based on the time go in and out') !!}" data-html="true" ></span></div>
                            <input type="checkbox" name="is_onsite"
                                   value="{{ OtRegister::IS_ONSITE }}"
                                   {{ !$isEditable ? 'disabled' : '' }}
                                   {{ $registerInfo->is_onsite == OtRegister::IS_ONSITE ? 'checked' : '' }}
                                data-on="{{ trans('ot::view.OT onsite.Yes') }}" data-off="{{ trans('ot::view.OT onsite.No') }}" data-toggle="toggle">
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 ot-form-group">
                        <label class="control-label">{{ trans('ot::view.OT reason') }}<em class="input-required">*</em></label>
                        <div class="input-box reason">
                            <textarea class="form-control" rows="4" name="reason" id="reason" required="" {{ $isEditable ? '' : 'disabled' }} style="min-height: 100px; max-width: 100%; min-width: 100%;">{{ $registerInfo->reason }}</textarea>
                        </div>
                    </div>
                </div>
                <div>
                    <p><b>{{ trans('ot::view.OT employees') }}</b></p>
                    <hr class="tbl-seperator">
                    @if ($isEditable)
                        <div class="row">
                            <div class="col-sm-11 request-form-group">
                                <div class="input-box">
                                    <select name="" class="form-control select-search-employee-ot" id="search_employee_ot" data-remote-url="{{ URL::route('ot::ot.ajax-search-employee') }}" multiple>
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
                        <i class="form-group text-language-ot">{!! trans('ot::view.Note add register') !!}</i>
                    @endif
                </div>
                <div>
                    <input type="hidden" name="table_data_emps" id="table_data_emps">
                    <input type="hidden" name="time_breaks" id="time_breaks">
                </div>
                <br>        
                <!-- Table người cùng làm thêm -->
                <div class="table-responsive ot-table-responsive">
                    <table class="edit-table table-striped table-grid-data table-responsive table-hover table-bordered table-condensed" id="table_ot_employees">
                        <thead>
                            @if (($registerInfo->id && $tagEmployeeInfo->count() > 1) || (old('table_data_emps') && !$registerInfo->id && count(json_decode(old('table_data_emps'))) > 1))
                            <th class="col-width-40">{{ trans('ot::view.No') }}</th>
                            @endif
                            <th class="col-width-60">{{ trans('ot::view.Employee code') }}</th>
                            <th class="col-width-90">{{ trans('ot::view.Employee Name') }}</th>
                            <th class="col-width-80">{{ trans('ot::view.OT from') }}</th>
                            <th class="col-width-80">{{ trans('ot::view.OT to') }}</th>
                            <th class="col-width-60">{{ trans('ot::view.OT paid?') }}</th>
                            <th class="col-width-40">{{ trans('ot::view.Total break time (h)') }}</th>
                            @if ($isEditable)
                                <th class="col-width-100"></th>
                            @endif
                        </thead>
                        <tbody>
                        <?php
                            $i = 1;
                            $employeeInfo = [];
                        ?>
                            @if ($registerInfo->id)
                                @foreach ($tagEmployeeInfo as $emp)
                                    <tr id="{{ $emp->employee_id }}">
                                        @if($tagEmployeeInfo->count() > 1)
                                            <td class="stt text-center">{{ $i }}</td>
                                        @endif
                                        <td class="emp_code">
                                            <div class="emp_code_main">{{ $emp->employee_code }}</div>
                                            <?php
                                                $breakTimes = OtBreakTime::getBreakTimesByRegister($emp->ot_register_id, $emp->employee_id);
                                                $employeeInfo[$emp->employee_id] = $emp->name
                                            ?>
                                            @if (count($breakTimes))
                                                <div class="has-set-time-break-edit" style="display: none;">
                                                    @foreach ($breakTimes as $item)
                                                        <div class="set-time-break-item row">
                                                            <div class="col-md-4 ot-form-group">
                                                                <label class="control-label time-break-date">
                                                                    <span class="time-break-date-val" data-date="{{ Carbon::parse($item->ot_date)->format('d/m/Y') }}">{{ Carbon::parse($item->ot_date)->format('d/m/Y') }}</span>
                                                                    <span class="time-break-date-day"> ({{ ManageTimeCommon::getLabelDayOfWeek(Carbon::parse($item->ot_date)->dayOfWeek) }})</span>
                                                                </label>
                                                            </div>
                                                            <div class="col-md-8 ot-form-group">
                                                                <input type="text" class="form-control time-break-value" data-date="{{ Carbon::parse($item->ot_date)->format('d/m/Y') }}" value="{{ number_format($item->break_time, 2) }}">
                                                                <label class="ot-error max_time_break-error">{{ trans('ot::view.Max break time is 14h') }}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td class="emp_name">{{ $emp->name }}</td>
                                        <td class="start_at">{{ $emp->start_at != null ? Carbon::createFromFormat('Y-m-d H:i:s', $emp->start_at)->format('d-m-Y H:i') : '' }}</td>
                                        <td class="end_at">{{ $emp->end_at != null ? Carbon::createFromFormat('Y-m-d H:i:s', $emp->end_at)->format('d-m-Y H:i') : '' }}</td>
                                        <td class="ot_paid"><center><input type="checkbox" {{ $emp->is_paid == 1 ? 'checked=""' : '' }} {{ $isEditable ? '' : 'disabled' }}></center></td>
                                        <?php
                                            $timeBreak = 0;
                                            if ($emp->time_break) {
                                                $timeBreak = $emp->time_break;
                                            }
                                            $timeBreak = round($timeBreak, 2);
                                        ?>
                                        <td class="relax">{{ number_format($timeBreak, 2) }}</td>
                                        @if ($isEditable)
                                            <td class="btn-manage">
                                                <button type="button" class="btn btn-primary edit" onclick="editEmp({{ $emp->employee_id }})"><i class="fa fa-pencil-square-o"></i></button>
                                                <button type="button" class="btn btn-delete delete" onclick="removeEmp({{ $emp->employee_id }})"><i class="fa fa-minus"></i></button>
                                            </td>
                                        @endif
                                        <?php $i++; ?>
                                    </tr>
                                @endforeach
                            @endif
                            @if(old('table_data_emps') && !$registerInfo->id)
                                <?php $tableData = json_decode(old('table_data_emps')); ?>
                                @foreach ($tableData as $emp)
                                    <tr id="{{ $emp->empId }}">
                                        @if(count($tableData) > 1)
                                            <td class="stt text-center">{{ $i }}</td>
                                        @endif
                                        <td class="emp_code">
                                            <div class="emp_code_main">{{ $emp->empCode }}</div>
                                        </td>
                                        <td class="emp_name">{{ $emp->empName }}</td>
                                        <td class="start_at">{{ $emp->startAt }}</td>
                                        <td class="end_at">{{ $emp->endAt }}</td>
                                        <td class="ot_paid"><center><input type="checkbox" {{ $emp->isPaid == 1 ? 'checked=""' : '' }} {{ $isEditable ? '' : 'disabled' }}></center></td>
                                        <?php
                                        $timeBreak = 0;
                                        if ($emp->break) {
                                            $timeBreak = $emp->break;
                                        }
                                        $timeBreak = round($timeBreak, 2);
                                        ?>
                                        <td class="relax">{{ number_format($timeBreak, 2) }}</td>
                                        @if ($isEditable)
                                            <td class="btn-manage">
                                                <button type="button" class="btn btn-primary edit" onclick="editEmp({{ $emp->empId }})"><i class="fa fa-pencil-square-o"></i></button>
                                                <button type="button" class="btn btn-delete delete" onclick="removeEmp({{ $emp->empId }})"><i class="fa fa-minus"></i></button>
                                            </td>
                                        @endif
                                        <?php $i++; ?>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    <div class="error" id="exist_time_lot_before_submit_error"></div>
                    <div class="error ot-error" id="error_no_employee">{{ trans('ot::message.The register OT list is required') }}</div>
                </div>
                @if(isset($commentsList) && count($commentsList) && $registerInfo->status == OtRegister::REJECT)
                    <div class="form-group">
                        <p><b>{{ trans('manage_time::view.Disapprove reason') }}</b></p>
                        <div class="box-body">
                            <ul class="products-list product-list-in-box">
                                @foreach($commentsList as $item)
                                    <li class="item">
                                        <div class="post">
                                            <div class="user-block">
                                                <img class="img-bordered-sm" src="{{ $item->avatar_url }}" alt="{{ $item->name }}">
                                                <span class="username">{{ $item->name }}</span>
                                                <span class="description">{{ Carbon::parse($item->created_at)->format('d-m-Y H:i:s') }}</span>
                                            </div>
                                            <!-- /.user-block -->
                                            <p>
                                            {!! View::nl2br($item->comment) !!}
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>                         
                    </div>
                @endif
            </div>            
            <div class="box-footer">
                @if ($isEditable)
                    <?php
                        $titleButtonSubmit = trans('ot::view.Register');
                        if ($pageType == 'edit') {
                            $titleButtonSubmit = trans('ot::view.Update');
                        }
                    ?>
                    <button type="button" class="btn btn-primary" id="submitBtn" name="submitBtn" @if ($registerInfo->status == OtRegister::DONE) style="display: none;" @endif ><i class="fa fa-floppy-o"></i> {{ $titleButtonSubmit }}</button>
                @endif
                @if (OtPermission::isAllowApprove($registerInfo, $applicant->id) && isset($isApprove) && $isApprove)
                    <button type="button" class="btn btn-success btn" onclick="setApproveId({{ $registerInfo->id }})" data-toggle="modal" data-target="#approve_confirm"
                            @if ($registerInfo->status == OtRegister::DONE) style="display: none;" @endif ><i class="fa fa-check"></i> {{ trans('ot::view.Approve') }}</button>
                    <button type="button" class="btn btn-danger btn" onclick="setRejectId({{ $registerInfo->id }})" data-toggle="modal" data-target="#reject_confirm"
                            @if ($registerInfo->status == OtRegister::REJECT) style="display: none;" @endif ><i class="fa fa-minus-circle"></i> {{ trans('ot::view.Not approve') }}</button>
                @endif
            </div>
            <br>
            @if ($isEditable && $pageType == 'edit' && isset($isApprove) && $isApprove)
                <p>Thông tin chấm công thao khảo</p>
                @include('ot::include.detail_timekeeping', ['employeeInfo' => $employeeInfo])
            @endif
        </div>
    </div>    
</form>
<!-- end form -->

<div id="has_set_time_break" style="display: none;">
    @if (isset($breakTimeByRegister) && count($breakTimeByRegister))
        @foreach ($breakTimeByRegister as $item)
            <div class="set-time-break-item row">
                <div class="col-xs-4 ot-form-group">
                    <label class="control-label time-break-date">
                        <span class="time-break-date-val" data-date="{{ Carbon::parse($item->ot_date)->format('d/m/Y') }}">{{ Carbon::parse($item->ot_date)->format('d/m/Y') }}</span>
                        <span class="time-break-date-day"> ({{ ManageTimeCommon::getLabelDayOfWeek(Carbon::parse($item->ot_date)->dayOfWeek) }})</span>
                    </label>
                </div>
                <div class="col-xs-8 ot-form-group">
                    <input type="text" class="form-control time-break-value" data-date="{{ Carbon::parse($item->ot_date)->format('d/m/Y') }}" value="{{ number_format($item->break_time, 2) }}" {{ $isEditable ? '' : 'disabled' }}>
                    <label class="ot-error max_time_break-error" >{{ trans('ot::view.Max break time is 14h') }}</label>
                </div>
            </div>
        @endforeach
    @endif
</div>
<div id="duplicate_set_time_break_item" style="display: none;">
    <div class="set-time-break-item row">
        <div class="col-xs-4 ot-form-group">
            <label class="control-label time-break-date">
                <span class="time-break-date-val"></span>
                <span class="time-break-date-day"></span>
            </label>
        </div>
        <div class="col-xs-8 ot-form-group">
            <input type="text" class="form-control time-break-value" value="0.00">
            <label class="ot-error max_time_break-error">{{ trans('ot::view.Max break time is 14h') }}</label>
        </div>
    </div>
</div>
<!-- Modal set time break-->
<div class="modal fade" id="modal_set_time_break" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="box-title">{{ trans('ot::view.Set shift break time') }}</h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-4 ot-form-group">
                        <label class="control-label">{{ trans('ot::view.Date') }}</label>
                    </div>
                    <div class="col-xs-8 ot-form-group">
                        <label class="control-label">{{ trans('ot::view.Shift break time (h)') }}</label>
                    </div>
                </div>
                <div id="box_set_time_break">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-cancel pull-left" data-dismiss="modal">{{ trans('ot::view.Close') }}</button>
                @if ($isEditable)
                    <button type="button" class="btn btn-primary btn-confirm pull-right" onclick="saveSetTimeBreak();">{{ trans('ot::view.Save') }}</button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- modal -->
<!-- start form add member anywhere-->
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
                                <input type="text" id="add_register_code" data-id="" value="" class="form-control" disabled="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 ot-form-group">
                            <label class="checkbox-inline"><input type="checkbox" id="is_paid" style="margin-top: 2px;" checked />{{ trans('ot::view.OT paid?') }}</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 start ot-form-group">
                            <label class="control-label">{{ trans('ot::view.OT from') }}<em class="input-required">(*)</em></label>
                            <div id="datetimepicker_add_start">
                                <input type='text' class="form-control required" name="add_time_start" id="add_time_start"
                                       data-toggle="tooltip" data-placement="top" title="{!! trans('ot::message.start_end date tooltip') !!}" data-html="true"/>
                            </div>
                            <div class="errorTxt"></div>
                        </div>
                        <div class="col-md-6 end ot-form-group">
                            <label class="control-label">{{ trans('ot::view.OT to') }}<em class="input-required">(*)</em></label>
                            <div id="datetimepicker_add_end">
                                <input type='text' class="form-control required" name="add_time_end" id="add_time_end"
                                       data-toggle="tooltip" data-placement="top" title="{!! trans('ot::message.start_end date tooltip') !!}" data-html="true"/>
                            </div>
                            <div class="errorTxt"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="group_set_time_edit" style="display: none;">
                            <div class="col-xs-4 ot-form-group">
                                <label class="control-label">{{ trans('ot::view.Date') }}</label>
                            </div>
                            <div class="col-xs-8 ot-form-group">
                                <label class="control-label">{{ trans('ot::view.Shift break time (h)') }}</label>
                            </div>
                            <div id="box_set_time_break_edit" class="col-md-12">
                            </div>
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
<!-- start form import member from project-->
<div class="modal fade" id="projsMemberModal" tabindex="-1" role="dialog" aria-labelledby="myProjsMemberLabel">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="box-title">{{ trans('ot::view.OT project member') }}</h3>
            </div>

            <div class="modal-body">
                <table id="memberTbl" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="checkbox_all"></th>
                            <th>ID</th>
                            <th class="col-sm-3">{{ trans('ot::view.Employee code') }}</th>
                            <th class="col-sm-4">{{ trans('ot::view.Employee Name') }}</th>
                            <th class="col-sm-4">{{ trans('ot::view.Employee Email') }}</th>
                        </tr>                    
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-confirm pull-right" onclick="importMember(tableProjsMember)">{{ trans('ot::view.Save') }}</button>
                <button type="button" class="btn btn-default btn-cancel pull-left" data-dismiss="modal">{{ trans('ot::view.Close') }}</button>
            </div>
        </div>
    </div>
</div>
<!-- start form import any employee from company-->
<div class="modal fade" id="teamMemberModal" tabindex="-1" role="dialog" aria-labelledby="myLabel">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="box-title">{{ trans('ot::view.OT company employee') }}</h3>
            </div>

            <div class="modal-body">
                <select class="form-control select-search team-search" style="width: 40%">
                    <option value="">{{ trans('ot::view.Select team') }}</option>
                    @foreach ($teamsOption as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <br><br>
                <table id="otherEmployeeTbl" class="table table-bordered table-striped ot-data-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="checkbox_all"></th>
                            <th>ID</th>
                            <th class="col-sm-3">{{ trans('ot::view.Employee code') }}</th>
                            <th class="col-sm-4">{{ trans('ot::view.Employee Name') }}</th>
                            <th class="col-sm-4">{{ trans('ot::view.Employee Email') }}</th>
                        </tr>                    
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-confirm pull-right" onclick="importMember(tableTeamEmp)">{{ trans('ot::view.Save') }}</button>
                <button type="button" class="btn btn-default btn-cancel pull-left" data-dismiss="modal">{{ trans('ot::view.Close') }}</button>
            </div>
        </div>
    </div>
</div>
<div class="box box-primary">
    <div class="box-body font-size-14">
        {!! trans('ot::view.Guider register OT') !!}
    </div>
</div>
<script>
    var compensationDays = <?php echo json_encode($compensationDays); ?>;
</script>