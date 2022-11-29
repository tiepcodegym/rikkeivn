<?php

use Illuminate\Support\Facades\Session;
use Rikkei\ManageTime\Model\LeaveDayReason;

$specialHolidayTwo = [];


$urlSearchRelatedPerson = route('manage_time::profile.leave.find-employee');
$urlCheckRegisterExist = route('manage_time::profile.leave.check-register-exist');

$dayUsed = 0;
$remainDay = 0;
if (isset($leaveDay) && count($leaveDay)) {
    $dayUsed = $leaveDay->day_used;
    if ($dayUsed < 0) {
        $dayUsed = 0;
    }
    $remainDay = $leaveDay->remain_day;

    if ($remainDay < 0) {
        $remainDay = 0;
    }
    $remainDayFeatureNow = $leaveDay->remain_day_feature_now;
    if ($remainDayFeatureNow < 0) {
        $remainDayFeatureNow = 0;
    }
}

$lang = Session::get('locale');
?>
<form role="form" class="managetime-form-register" id="form-register" enctype="multipart/form-data" autocomplete="off">

<div class="row">
    <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
        @if($teamCodePreOfEmp == \Rikkei\Team\Model\Team::CODE_PREFIX_JP)
            <div class="box-body">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead class="managetime-thead">
                    <tr>
                        <th>前回付与⽇</th>
                        <th>次回付与⽇</th>
                        <th>前期繰越⽇数</th>
                        <th>今期付与⽇数</th>
                        <th>今期取得⽇数</th>
                        <th>有給休暇残⽇数</th>
                        <th>取得予定⽇数</th>
                        <th>年5⽇の時季指定残⽇数</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{{ $grantDate['last_grant_date'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($grantDate['next_grant_date'])->addDays(1)->format('Y-m-d') }}</td>
                        <td style="text-align: right;">{{ $leaveDay ? number_format($leaveDay->day_last_transfer, 2) : '0.00' }}</td>
                        <td style="text-align: right;">{{ $leaveDay ? number_format($leaveDay->day_current_year + $leaveDay->day_seniority, 2) : '0.00' }}</td>
                        <td style="text-align: right;">{{ $leaveDay ? number_format($leaveDay->day_used, 2) : '0.00' }}</td>
                        <td style="text-align: right;">{{ $leaveDay ? number_format($leaveDay->day_last_transfer + $leaveDay->day_current_year + $leaveDay->day_seniority - $leaveDay->day_used, 2) : '0.00' }}</td>
                        <td style="text-align: right;">{{ number_format($regsUnapporve, 2) }}</td>
                        <td style="text-align: right;">
                            @if($leaveDay && $leaveDay->day_used < 5)
                                <span>{{ number_format(5 - $leaveDay->day_used, 2) }}</span>
                            @else
                                <span>0.00</span>
                            @endif
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @endif
        <div class="box-body">
            <input type="hidden" name="employee_id" id="employee_id" value="{{ $registrantInformation->creator_id }}">
            <div class="row">
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label">{{ trans('manage_time::view.Registrant') }}</label>
                    <div class="input-box">
                        <input type="text" name="employee_name" class="form-control" value="{{ $registrantInformation->employee_name }} ({{ $registrantInformation->employee_email }})" disabled />
                    </div>
                </div>
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label">{{ trans('manage_time::view.Employee code') }}</label>
                    <div class="input-box">
                        <input type="text" name="employee_code" class="form-control" value="{{ $registrantInformation->employee_code }}" disabled />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label">{{ trans('manage_time::view.Position') }}</label>
                    <div class="input-box">
                        <input type="text" name="role_name" class="form-control" value="{{ $registrantInformation->role_name }}" disabled />
                    </div>
                </div>
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label required">{{ trans('manage_time::view.Approver') }} <em>*</em></label>
                    <div class="input-box">
                        <select id="approver" class="form-control select-search-employee-no-pagination" name="approver" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee-can-approve', ['route' => 'manage_time::manage-time.manage.leave_day.approve']) }}">
                            @if ($suggestApprover)
                                <option value="{{ $suggestApprover->approver_id }}" selected>{{ $suggestApprover->approver_name . ' (' . preg_replace('/@.*/', '',$suggestApprover->approver_email) . ')' }}</option>
                            @endif
                        </select>
                    </div>
                    <label id="approver-error" class="managetime-error" for="approver">{{ trans('core::view.This field is required') }}</label>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label">{{ trans('manage_time::view.Substitute person') }}</label>
                    <div class="input-box">
                        <select name="substitute" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label">{{ trans('manage_time::view.Related persons need notified') }}</label>
                    <div class="input-box">
                        <select name="related_persons_list[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple>
                        </select>
                    </div>
                </div>
            </div>

            @if (isset($groupEmail) && count($groupEmail))
                <div class="row">
                    <div class="col-sm-6 managetime-form-group">
                        <label class="control-label">{{ trans('manage_time::view.Group email need notified') }}</label>
                        <div class="input-box">
                            <select name="group_email[]" class="form-control group-email" multiple>
                                @foreach($groupEmail as $value)
                                    <option value="{{$value}}">{{substr($value, 0, strrpos($value, '@'))}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label">{{ trans('manage_time::view.Customer\'s company name') }}</label>
                    <div class="input-box">
                        <input type="text" name="company_name" id="company_name" class="form-control" value="{{old('company_name')}}"
                               placeholder="{{ trans('manage_time::view.Company name where you are onsite') }}"/>
                    </div>
                </div>
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label">{{ trans('manage_time::view.Customer name') }}</label>
                    <div class="input-box">
                        <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{old('customer_name')}}"
                               placeholder="{{ trans('manage_time::view.Customer name who approved the leave application') }}"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label required">{{ trans('manage_time::view.Leave day type') }} <em>*</em>
                        @if($teamCodePreOfEmp != \Rikkei\Team\Model\Team::CODE_PREFIX_JP)
                            <span class="fa fa-question-circle tooltip-leave" data-toggle="tooltip" title="" data-html="true" ></span>
                        @endif
                    </label>
                    <div class="input-box">
                        <select id="reason" class="form-control managetime-select-2" name="reason" data-aja>
                            @if(isset($listLeaveDayReasons) && count($listLeaveDayReasons))
                                @php $i = 1; @endphp
                                @foreach($listLeaveDayReasons as $item)
                                    <option value="{{ $item->id }}"
                                            data-salary-rate="{{ $item->salary_rate }}"
                                            data-reason-code="{{ $item->used_leave_day }}"
                                            data-type="{{ $item->type }}"
                                            data-value="{{ $item->value }}"
                                            data-repeated="{{ $item->repeated }}"
                                            data-unit="{{ $item->unit }}"
                                            data-calculate-full-day="{{ $item->calculate_full_day }}"

                                            @if(old('reason') ==  $item->id)
                                                selected
                                            @endif
                                    >{{ $i++ . '. ' . LeaveDayReason::languageLeaveDayReasons($item, $lang) }}</option>
                                @endforeach
                            @endif
                        </select>
                        <input name="calculate_full_day" type="hidden" value="0" id="calculate-full-day" value="{{old('calculate_full_day')}}">
                    </div>
                    <label id="reason-error" class="managetime-error" for="reason">{{ trans('manage_time::view.This field is required') }}</label>
                </div>
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label">{{ trans('manage_time::view.Salary rate') }}</label>
                    <div class="input-box">
                        <input type="text" id="salary_rate" name="salary_rate" class="form-control" value="" readonly />
                    </div>
                </div>
            </div>
{{--            <div class="row">--}}
{{--                <div class="col-sm-6 managetime-form-group" id="list-member-rela">--}}
{{--                    <label class="control-label required managetime-label">{{ trans('manage_time::view.Relationship of Member') }} <em>*</em>--}}
{{--                    </label>--}}
{{--                    <div class="input-box">--}}
{{--                        @if ($getRelation && count($getRelation))--}}
{{--                            @foreach($getRelation as $key => $items)--}}
{{--                                <input type="checkbox" name="employee_relationship[]" class="ckbox" value="{{ $items["r_id"] }}" data-id="{{ $items["r_id"] }}"/> <label>{{ $items["r_name"] }} - {{ $items["r_relationship_name"] }}</label> <br/>--}}
{{--                            @endforeach--}}
{{--                        @endif--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

            <div class="row">
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label required">{{ trans('manage_time::view.From date') }} <em>*</em></label>
                    <div class='input-group date' id='datetimepicker-start-date'>
                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-start">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                        <input type='text' class="form-control managetime-date" name="start_date" id="start_date" data-date-format="DD-MM-YYYY HH:mm" data-toggle="tooltip"  value="{{$dateStart}}"/>
                    </div>
                    <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
                    <label id="register_exist_error" class="managetime-error" for="start_date">{{ trans('manage_time::message.Registration time has been identical') }}</label>
                    <label id="register_type_exist_error" class="managetime-error"></label>
                    <label id="day_off_before_offcial_error" class="managetime-error" for="start_date">{{ trans('manage_time::message.Unable Day off with salary before offcial date') }}</label>
                </div>
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label required">{{ trans('manage_time::view.End date') }} <em>*</em></label>
                    <div class='input-group date' id='datetimepicker-end-date'>
                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-end">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                        <input type='text' class="form-control" name="end_date" id="end_date" data-date-format="DD-MM-YYYY HH:mm" data-html="true"  value="{{$dateEnd}}"/>
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
                    <label class="control-label">{{ trans('manage_time::view.Number of days off') }}</label>
                    <div class="input-box">
                        <input type="text" name="number_days_off" id="number_days_off" class="form-control" value="1" readonly />
                    </div>
                    <label id="end_date_before_start_date-error" class="managetime-error" for="number_days_off">{{ trans('manage_time::view.The number day off must be than 0') }}</label>
                    <label id="number_days_off-error" class="managetime-error" for="number_days_off">{{ trans('manage_time::view.The day of leave must be smaller day of remain') }}</label>
                    <label id="reason_special_value-error" class="managetime-error">{{ trans('manage_time::message.The number day is greater than the number day allowed') }}</label>
                </div>
                @if($teamCodePreOfEmp == \Rikkei\Team\Model\Team::CODE_PREFIX_JP)
                    <div class="col-sm-6 managetime-form-group">
                        <label class="control-label">{{ trans('manage_time::view.Number days of remain japan') }}</label>
                        <div class="input-box">
                            <input type="text" name="number_days_remain_2" id="number_days_remain_2" class="form-control" value="{{ $remainDay }}" readonly />
                        </div>
                    </div>

            </div>

            <div class="row" id="information_leave_day">
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label">{{ trans('manage_time::view.Number days of used') }}</label>
                    <div class="input-box">
                        <input type="text" name="number_days_used" id="number_days_used" class="form-control" value="{{ $dayUsed }}" readonly />
                    </div>
                </div>
                <div class="row" id="information_leave_day">
                    <div class="col-sm-6 managetime-form-group">
                        <label class="control-label">{{ trans('manage_time::view.Number days unapprove') }}</label>
                        <div class="input-box">
                            <input type="text" id="number_unapprove" class="form-control" value="{{ $regsUnapporve }}" readonly />
                        </div>
                    </div>
                </div>

            </div>
            @else
                <div class="col-sm-6 managetime-form-group">
                    <label class="control-label">{{ trans('manage_time::view.Number days of remain up to now') }}</label>
                    <div class="input-box">
                        <input type="text" name="number_days_remain_2" id="number_days_remain_2" class="form-control" value="{{ $remainDayFeatureNow }}" readonly />
                    </div>
                </div>

        </div>

        <div class="row" id="information_leave_day">
            <div class="col-sm-6 managetime-form-group">
                <label class="control-label">{{ trans('manage_time::view.Number days of used') }}</label>
                <div class="input-box">
                    <input type="text" name="number_days_used" id="number_days_used" class="form-control" value="{{ $dayUsed }}" readonly />
                </div>
            </div>
            <div class="col-sm-6 managetime-form-group">
                <label class="control-label">{{ trans('manage_time::view.Number days of remain') }}</label>
                <div class="input-box">
                    <input type="text" name="number_days_remain" id="number_days_remain" class="form-control" value="{{ $remainDay }}" readonly />
                </div>
            </div>

        </div>
        <div class="row" id="information_leave_day">
            <div class="col-sm-6 managetime-form-group">
                <label class="control-label">{{ trans('manage_time::view.Number days unapprove') }}</label>
                <div class="input-box">
                    <input type="text" id="number_unapprove" class="form-control" value="{{ $regsUnapporve }}" readonly />
                </div>
            </div>
        </div>
        @endif

        <input type="hidden" name="number_validate" id="number_validate" class="form-control" value="1" readonly />
        <div class="form-group">
            <a id='number_days_leave' style="cursor: pointer;">{{ trans('manage_time::view.Infor leave days') }}</a>
        </div>
        <div class="form-group">
            <input type="hidden" id="team_code_pre_of_emp" name="team_code_pre_of_emp" value="{{ $teamCodePreOfEmp }}" />
            <input type="hidden" id="code_prefix_jp" name="code_prefix_jp" value="{{ \Rikkei\Team\Model\Team::CODE_PREFIX_JP }}" />
            <input type="hidden" id="reason_paid_leave_jp" name="reason_paid_leave_jp" value="{{ \Rikkei\ManageTime\Model\LeaveDayReason::REASON_PAID_LEAVE_JA }}" />
            @if($teamCodePreOfEmp == \Rikkei\Team\Model\Team::CODE_PREFIX_JP)
                <label class="control-label">{{ trans('manage_time::view.Leave day reason') }}</label>
            @else
                <label class="control-label required">{{ trans('manage_time::view.Leave day reason') }} <em>*</em></label>
            @endif
            <div class="input-box">
                <textarea id="note" name="note" class="form-control managetime-textarea">{{old('note')}}</textarea>
            </div>
            <label id="note-error" class="managetime-error" for="note">{{ trans('manage_time::view.This field is required') }}</label>
        </div>
        <div class="comelate-upload-file">
            <input type="file" name="files" accept="image/*">
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <button type="button" class="btn btn-primary" id="submit" onclick="return checkFormLeaveDayRegister();"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Register') }}</button>
        <input type="hidden" id="check_submit" name="" value="0">
    </div>
</div>
</div>
</form>