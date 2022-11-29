<?php
$urlCheckRegisterExist = route('manage_time::profile.supplement.check-register-exist');
?>
<form role="form" method="post" action="{{ route('manage_time::profile.supplement.save') }}" class="managetime-form-register supplement-register" id="form-register" enctype="multipart/form-data" autocomplete="off">
    {!! csrf_field() !!}
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
            <div class="box-body">
                <input type="hidden" name="employee_id" id="employee_id" value="{{ $registrantInformation->id }}">
                <div class="row">
                    <div class="col-sm-6 managetime-form-group">
                        <label class="control-label">{{ trans('manage_time::view.Registrant') }}</label>
                        <div class="input-box">
                            <input type="text" name="employee_name" id="employee_name" class="form-control" value="{{ $registrantInformation->employee_name }} ({{ $registrantInformation->employee_email }})" disabled />
                        </div>
                    </div>
                    <div class="col-sm-6 managetime-form-group">
                        <label class="control-label">{{ trans('manage_time::view.Employee code') }}</label>
                        <div class="input-box">
                            <input type="text" name="employee_code" id='employee_code' class="form-control" value="{{ $registrantInformation->employee_code }}" disabled />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label">{{ trans('manage_time::view.Position') }}</label>
                    <div class="input-box">
                        <input type="text" name="role_name" class="form-control" value="{{ $registrantInformation->role_name }}" disabled />
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 managetime-form-group">
                        <label class="control-label required">{{ trans('manage_time::view.Approver') }} <em>*</em></label>
                        <div class="input-box">
                            <select id="approver" class="form-control select-search-employee-no-pagination" name="approver" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee-can-approve', ['route' => 'manage_time::manage-time.manage.supplement.approve']) }}">
                                @if ($suggestApprover)
                                    <option value="{{ $suggestApprover->approver_id }}" selected>{{ $suggestApprover->approver_name . ' (' . preg_replace('/@.*/', '',$suggestApprover->approver_email) . ')' }}</option>
                                @endif
                            </select>
                        </div>
                        <label id="approver-error" class="managetime-error" for="approver">{{ trans('manage_time::view.This field is required') }}</label>
                    </div>
                    <div class="col-sm-6 managetime-form-group">
                        <label class="control-label">{{ trans('manage_time::view.Related persons need notified') }}</label>
                        <div class="input-box">
                            <select name="related_persons_list[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 managetime-form-group">
                        <label class="control-label required">{{ trans('manage_time::view.From date') }} <em>*</em> <span class="fa fa-question-circle" data-toggle="tooltip" title="{!! trans('manage_time::view.Start date tooltip') !!}" data-html="true" ></span></label>
                        <div class='input-group date' id='datetimepicker-start-date'>
                                        <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-start">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                            <input type='text' class="form-control managetime-date" name="start_date" id="start_date" data-date-format="DD-MM-YYYY HH:mm"  />
                        </div>
                        <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
                        <label id="register_exist_error" class="managetime-error" for="start_date">{{ trans('manage_time::message.Registration time has been identical') }}</label>
                        <label id="supplement_ot_one_day" class="managetime-error" for="end_date">{{ trans('manage_time::view.Register supplement OT must be in a day') }}</label>
                    </div>
                    <div class="col-sm-6 managetime-form-group">
                        <label class="control-label required">{{ trans('manage_time::view.End date') }} <em>*</em> <span class="fa fa-question-circle" data-toggle="tooltip" title="{!! trans('manage_time::view.End date tooltip') !!}" data-html="true" ></span></label>
                        <div class='input-group date' id='datetimepicker-end-date'>
                                        <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-end">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                            <input type='text' class="form-control" name="end_date" id="end_date" data-date-format="DD-MM-YYYY HH:mm"  />
                        </div>
                        <div class='input-group date' id='hidden-end-date' style="display: none;">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                            <input type='text' class="form-control managetime-date" name="" id="" readonly />
                        </div>
                        <label id="end_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.This field is required') }}</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 managetime-form-group">
                        <label class="control-label">{{ trans('manage_time::view.Number of days supplement') }}</label>
                        <div class="input-box">
                            <input type="text" name="number_days_off" id="number_days_off" class="form-control" value="" readonly />
                        </div>
                        <label id="end_date_before_start_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.The number days supplement must be than 0') }}</label>
                    </div>
                    <div class="col-sm-6 managetime-form-group">
                        <label class="control-label">&nbsp;</label>
                        <div class="input-box">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_ot" id="is_ot" value="1"> {{ trans('manage_time::view.Is OT') }} <span class="fa fa-question-circle" data-toggle="tooltip" title="{!! trans('manage_time::view.Tooltip supplement ot') !!}" data-html="true" ></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label required">{{ trans('manage_time::view.Supplement reason') }} <em>*</em></label>
                    @if ($reasons)
                        <select class="form-control select-search col-md-6" id="reason_id" name="reason_id">
                            @foreach ($reasons as $reason)
                                <option value="{{ $reason->id }}" data-required="{{ $reason->is_image_required }}" data-other="{{ $reason->is_type_other }}">{{ $reason->name }}</option>
                            @endforeach
                        </select>
                        <div class="margin-bottom-15">&nbsp;</div>
                    @endif
                    <div class="input-box">
                                    <textarea id="reason" name="reason"
                                              class="form-control required managetime-textarea {{ $reasons ? 'hidden' : '' }}"
                                              onkeyup="checkInputReasonKeyup()"
                                              placeholder="{{ trans('manage_time::view.Supplement reason') }}"></textarea>
                    </div>
                    <label id="reason-error" class="managetime-error" for="reason">{{ trans('manage_time::view.This field is required') }}</label>
                </div>

                <div class="managetime-upload-file">
                    <input type="file" name="files" id="image_upload" accept="image/*">
                    <label id="image_upload-error" class="managetime-error" >{{ trans('manage_time::view.This field is required') }}</label>
                </div>
                <div>
                    @include('manage_time::supplement.include.supplement_together')
                </div>
            </div>


            <!-- /.box-body -->

            <div class="box-footer">
                <button type="submit" class="btn btn-primary" action="SupplementRegister" id="submit" onclick="return checkFormSupplementRegister('{{ $urlCheckRegisterExist }}');"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Register') }}</button>
                <input type="hidden" id="check_submit" name="" value="0">
            </div>
        </div>
    </div>
</form>
