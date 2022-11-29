@extends('manage_time::layout.common_layout')

@section('title-common')
    {{ trans('manage_time::view.Leave day register') }}
@endsection

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View;
    use Rikkei\Core\Model\CoreConfigData;
    use Rikkei\ManageTime\Model\LeaveDayRegister;
    use Rikkei\ManageTime\View\ManageTimeConst;
    use Rikkei\Team\Model\Team;
    use Rikkei\ManageTime\Model\LeaveDayReason;

    $annualHolidays = CoreConfigData::getAnnualHolidays(2);
    $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePreOfEmp);

    $isShowFormEdit = false;
    $disabled = '';
    if (isset($isAllowEdit) && $isAllowEdit) {
            $isShowFormEdit = true;
    } else {
            $disabled = 'disabled';
    }

    $statusUnapprove = LeaveDayRegister::STATUS_UNAPPROVE;
    $statusApproved = LeaveDayRegister::STATUS_APPROVED;
    $statusDisapprove = LeaveDayRegister::STATUS_DISAPPROVE;
    $statusCancel = LeaveDayRegister::STATUS_CANCEL;
    $urlApprove = route('manage_time::profile.leave.approve');
    $urlDisapprove = route('manage_time::profile.leave.disapprove');
    $urlSearchRelatedPerson = route('manage_time::profile.leave.find-employee');
    $urlCheckRegisterExist = route('manage_time::profile.leave.check-register-exist');
    $contentModalApprove = trans('manage_time::view.Do you want to approve the register of leave day?');

    $dayUsed = 0;
    $remainDay = 0;
    if (isset($leaveDay) && count($leaveDay)) {
            $dayUsed = $leaveDay->day_used;
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

@section('css-common')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/jquery.fileuploader.css') }}" />
@endsection

@section('sidebar-common')
	@include('manage_time::include.sidebar_leave')
@endsection

@section('content-common')
    <div class="se-pre-con"></div>
    <!-- Box mission list -->
    <div class="box box-primary" id="mission_register">
        <div class="box-header with-border">
		    <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.View detail leave day register') }}</h3>
		</div>
		<!-- /.box-header -->

		<div class="box-body no-padding">
			@if($isShowFormEdit)
				<form role="form" method="post" action="{{ route('manage_time::profile.leave.update') }}" class="managetime-form-register" id="form-edit-register" enctype="multipart/form-data" autocomplete="off">
				{!! csrf_field() !!}
			@endif
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
			            	<input type="hidden" name="register_id" id="register_id" value="{{ $registerRecord->register_id }}">
			            	<input type="hidden" name="" id="employee_id" value="{{ $registerRecord->creator_id }}">
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <?php
                                        if ($registerRecord->status == $statusDisapprove) {
                                            $classStatus = 'callout-disapprove';
                                            $status = trans('manage_time::view.Disapprove');
                                        } elseif ($registerRecord->status == $statusUnapprove) {
                                            $classStatus = 'callout-unapprove';
                                            $status = trans('manage_time::view.Unapprove');
                                        } elseif ($registerRecord->status == $statusApproved) {
                                            $classStatus = 'callout-approved';
                                            $status = trans('manage_time::view.Approved');
                                        } else {
                                            $classStatus = 'callout-cancelled';
                                            $status = trans('manage_time::view.Cancelled');
                                        }
                                        ?>
                                        <div class="managetime-callout {{ $classStatus }}">
                                            <p class="text-center"><strong>{{ $status }}</strong></p>
                                        </div>
                                    </div>
                                </div>

                                        <div class="row">
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label managetime-label">{{ trans('manage_time::view.Registrant') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="employee_name" class="form-control" value="{{ $registerRecord->creator_name }} ({{ $registerRecord->creator_email }})" disabled />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label managetime-label">{{ trans('manage_time::view.Employee code') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="employee_code" class="form-control" value="{{ $registerRecord->creator_code }}" disabled />
                                                </div>
                                            </div>
                                        </div>
                                  

                                <div class="row">
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label managetime-label">{{ trans('manage_time::view.Position') }}</label>
                                        <div class="input-box">
                                            <input type="text" name="role_name" class="form-control" value="{{ $registerRecord->role_name }}" disabled />
                                        </div>
                                    </div>
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label required managetime-label">{{ trans('manage_time::view.Approver') }} <em>*</em></label>
                                        <div class="input-box">
                                            <select id="approver" class="form-control select-search-employee-no-pagination" name="approver" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee-can-approve', ['route' => 'manage_time::manage-time.manage.leave_day.approve']) }}" {{ $disabled }}>
                                                <option value="{{ $registerRecord->approver_id }}" selected>{{ $registerRecord->approver_name . ' (' . preg_replace('/@.*/', '',$registerRecord->approver_email) . ')' }}</option>
                                            </select>
                                        </div>
                                        <label id="approver-error" class="managetime-error" for="approver">{{ trans('manage_time::view.This field is required') }}</label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label managetime-label">{{ trans('manage_time::view.Substitute person') }}</label>
                                        <div class="input-box">
                                            <select name="substitute" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" {{ $disabled }}>
                                                @if($registerRecord->substitute_name)
                                                <option value="{{ $registerRecord->substitute_id }}" selected>{{ $registerRecord->substitute_name . ' (' . preg_replace('/@.*/', '',$registerRecord->substitute_email) . ')' }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label managetime-label">{{ trans('manage_time::view.Related persons need notified') }}</label>
                                        <div class="input-box">
                                            <select name="related_persons_list[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple {{ $disabled }}>
                                                    @if(isset($relatedPersonsList) && count($relatedPersonsList))
                                                    @foreach($relatedPersonsList as $item)
                                                    <option value="{{ $item->relater_id }}" selected>{{ $item->relater_name . ' (' . preg_replace('/@.*/', '',$item->relater_email) . ')' }}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                @if (isset($groupEmail) && count($groupEmail) || isset($groupEmailRegister) && count($groupEmailRegister))
                                <div class="row">
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label managetime-label">{{ trans('manage_time::view.Group email need notified') }}</label>
                                        <div class="input-box">
                                            <select name="group_email[]" class="form-control group-email" multiple {{ $disabled }}>
                                                    @if (isset($groupEmail) && count($groupEmail))
                                                    @foreach($groupEmail as $value)
                                                    <option value="{{$value}}" selected>{{substr($value, 0, strrpos($value, '@'))}}</option>
                                                @endforeach
                                                @if (isset($groupEmailRegister) && count($groupEmailRegister))
                                                @foreach($groupEmailRegister as $value)
                                                <option value="{{$value}}">{{substr($value, 0, strrpos($value, '@'))}}</option>
                                                @endforeach
                                                @endif
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="row">
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label">{{ trans('manage_time::view.Customer\'s company name') }}</label>
                                        <div class="input-box">
                                            <input type="text" name="company_name" id="company_name" class="form-control" {{ $disabled }}
                                                   value="{{ $registerRecord->company_name }}"
                                                   placeholder="{{ trans('manage_time::view.Company name where you are onsite') }}"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label">{{ trans('manage_time::view.Customer name') }}</label>
                                        <div class="input-box">
                                            <input type="text" name="customer_name" id="customer_name" class="form-control" {{ $disabled }}
                                                   value="{{ $registerRecord->customer_name }}"
                                                   placeholder="{{ trans('manage_time::view.Customer name who approved the leave application') }}"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label required managetime-label">{{ trans('manage_time::view.Leave day type') }} <em>*</em>
                                            @if($teamCodePreOfEmp != \Rikkei\Team\Model\Team::CODE_PREFIX_JP)
                                                <span class="fa fa-question-circle tooltip-leave" data-toggle="tooltip" title="" data-html="true" ></span>
                                            @endif
                                        </label>
                                        <div class="input-box">
                                            <select id="reason" class="form-control managetime-select-2" name="reason" {{ $disabled }}>
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
                                                     <?php if ($item->id == $registerRecord->reason_id) { ?> selected <?php } ?>
                                                >{{ $i++ . '. ' . LeaveDayReason::languageLeaveDayReasons($item, $lang) }}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                            <input name="calculate_full_day" type="hidden" value="{{ $item->calculate_full_day }}" id="calculate-full-day">
                                        </div>
                                        <label id="reason-error" class="managetime-error" for="reason">{{ trans('manage_time::view.This field is required') }}</label>
                                    </div>
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label managetime-label">{{ trans('manage_time::view.Salary rate') }}</label>
                                        <div class="input-box">
                                            <input type="text" id="salary_rate" name="salary_rate" class="form-control" value="" readonly />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6 managetime-form-group" id="list-member-rela-edit">
                                        <label class="control-label required managetime-label">{{ trans('manage_time::view.Relationship of Member') }} <em>*</em>
                                        </label>
                                        <div class="input-box">
                                            <?php
                                            $getRelationMemberId = [];
                                            if (isset($getRelationMember) && count($getRelationMember)) {
                                                for ($i = 1; $i <= count($getRelationMember); $i++) {
                                                    $getRelationMemberId[] = $getRelationMember[$i - 1]['employee_relationship_id'];
                                                }
                                            }
                                            ?>
                                            @if (isset($getRelation) && count($getRelation))
                                                @foreach($getRelation as $key => $items)
                                                    <input type="checkbox" name="employee_relationship[]" value="{{ $items["r_id"] }}"
                                                       <?php
                                                       if (LeaveDayReason::checkReasonTeamType($registerRecord->reason_id)
                                                       && in_array($items["r_id"], $getRelationMemberId)) { ?> checked <?php } ?>
                                                       data-id="{{ $items["r_id"] }}"/> <label>{{ $items["r_name"] }} - {{ $items["r_relationship_name"] }}</label> <br/>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if(!$isShowFormEdit)
                                <div class="row">
                                    <div class="col-sm-6 managetime-form-group">
                                        <div class="input-box">
                                            @if (isset($registerRecord->reason_id) && LeaveDayReason::checkReasonTeamType($registerRecord->reason_id))
                                                @foreach($getRelationMember as $key => $items)
                                                    <input type="checkbox" name="employee_relationship[]" value="{{ $items["employee_relationship_id"] }}" checked disabled /> <label>{{ $items["r_name"] }} - {{ $items["r_relationship_name"] }}</label> <br/>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="row">
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label required managetime-label">{{ trans('manage_time::view.From date') }} <em>*</em></span></label>
                                        <div class='input-group date' id='datetimepicker-start-date'>
                                            <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-start">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            <input type='text' class="form-control managetime-date" name="start_date" id="start_date" data-date-format="DD-MM-YYYY HH:mm"  value="" {{ $disabled }} />
                                        </div>
                                        <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
                                        <label id="register_exist_error" class="managetime-error" for="start_date">{{ trans('manage_time::message.Registration time has been identical') }}</label>
                                        <label id="register_type_exist_error" class="managetime-error"></label>
                                        <label id="day_off_before_offcial_error" class="managetime-error" for="start_date">{{ trans('manage_time::message.Unable Day off with salary before offcial date') }}</label>
                                    </div>
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label required managetime-label">{{ trans('manage_time::view.End date') }} <em>*</em></span></label>
                                        <div class='input-group date' id='datetimepicker-end-date'>
                                            <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-end">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            <input type='text' class="form-control" name="end_date" id="end_date" data-date-format="DD-MM-YYYY HH:mm"  value="" {{ $disabled }} />
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
                                    <label class="control-label managetime-label">{{ trans('manage_time::view.Number of days off') }}</label>
                                    <div class="input-box">
                                        <input type="text" name="number_days_off" id="number_days_off" class="form-control" value="{{ $registerRecord->number_days_off }}" readonly />
                                    </div>
                                    <label id="end_date_before_start_date-error" class="managetime-error" for="number_days_off">{{ trans('manage_time::view.The number days leave of min is 0.5') }}</label>
                                    <label id="number_days_off-error" class="managetime-error" for="number_days_off">{{ trans('manage_time::view.The day of leave must be smaller day of remain') }}</label>
                                    <label id="reason_special_value-error" class="managetime-error">{{ trans('manage_time::message.The number day is greater than the number day allowed') }}</label>
                                </div>
                                @if($teamCodePreOfEmp == \Rikkei\Team\Model\Team::CODE_PREFIX_JP)
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label managetime-label">{{ trans('manage_time::view.Number days of remain japan') }}</label>
                                    <div class="input-box">
                                        <input type="text" name="number_days_remain" id="number_days_remain" class="form-control" value="{{ $remainDay }}" readonly />
                                    </div>
                                </div>
			                </div>

                                <div class="row" id="information_leave_day">
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label managetime-label">{{ trans('manage_time::view.Number days of used') }}</label>
                                        <div class="input-box">
                                            <input type="text" name="number_days_used" id="number_days_used" class="form-control" value="{{ $dayUsed }}" readonly />
                                        </div>
                                    </div>
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label managetime-label">{{ trans('manage_time::view.Number days unapprove') }}</label>
                                        <div class="input-box">
                                            <input type="text" id="number_unapprove" class="form-control" value="{{ $regsUnapporve }}" readonly />
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label managetime-label">{{ trans('manage_time::view.Number days of remain up to now') }}</label>
                                    <div class="input-box">
                                        <input type="text" name="number_days_remain" id="number_days_remain" class="form-control" value="{{ $remainDayFeatureNow }}" readonly />
                                    </div>
                                </div>
			                </div>

                                <div class="row" id="information_leave_day">
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label managetime-label">{{ trans('manage_time::view.Number days of used') }}</label>
                                        <div class="input-box">
                                            <input type="text" name="number_days_used" id="number_days_used" class="form-control" value="{{ $dayUsed }}" readonly />
                                        </div>
                                    </div>
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label managetime-label">{{ trans('manage_time::view.Number days of remain') }}</label>
                                        <div class="input-box">
                                            <input type="text" name="number_days_remain" id="number_days_remain" class="form-control" value="{{ $remainDay }}" readonly />
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-sm-6 managetime-form-group">
                                        <label class="control-label managetime-label">{{ trans('manage_time::view.Number days unapprove') }}</label>
                                        <div class="input-box">
                                            <input type="text" id="number_unapprove" class="form-control" value="{{ $regsUnapporve }}" readonly />
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="form-group">
                                    <a id='number_days_leave' style="cursor: pointer;">{{ trans('manage_time::view.Infor leave days') }}</a>
                                </div>
                                <div class="form-group">
                                    <input type="hidden" id="team_code_pre_of_emp" name="team_code_pre_of_emp" value="{{ $teamCodePreOfEmp }}" />
                                    <input type="hidden" id="code_prefix_jp" name="code_prefix_jp" value="{{ \Rikkei\Team\Model\Team::CODE_PREFIX_JP }}" />
                                    <input type="hidden" id="reason_paid_leave_jp" name="reason_paid_leave_jp" value="{{ \Rikkei\ManageTime\Model\LeaveDayReason::REASON_PAID_LEAVE_JA }}" />
                                    @if($teamCodePreOfEmp == \Rikkei\Team\Model\Team::CODE_PREFIX_JP)
                                        <label class="control-label managetime-label">{{ trans('manage_time::view.Leave day reason') }}</label>
                                    @else
                                        <label class="control-label managetime-label required">{{ trans('manage_time::view.Leave day reason') }} <em>*</em></label>
                                    @endif
                                    <div class="input-box">
                                        <textarea id="note" name="note" class="form-control managetime-textarea" {{ $disabled }}>{{ $registerRecord->note }}</textarea>
                                    </div>
                                    <label id="note-error" class="managetime-error" for="note">{{ trans('manage_time::view.This field is required') }}</label>
                                </div>
                                @if(isset($isShowFormEdit) && $isShowFormEdit)
                                <div class="comelate-upload-file">
                                    <input type="file" name="files" {{ $disabled }} data-fileuploader-files="{{ $appendedFiles }}">
                                </div>
                                @else
                                @if(isset($attachmentsList) && count($attachmentsList))
                                @foreach($attachmentsList as $img)
                                <a class="fancybox" href="{{ URL::asset($img->path) }}" rel="group" style="cursor:zoom-in"><img src="{{ URL::asset($img->path) }}" width="180" border="0" alt=""></a>
                                @endforeach
                                @endif
                                @endif
                                <input type="hidden" name="number_validate" id="number_validate" class="form-control" value="{{ $registerRecord->number_days_off }}" readonly />
                                @if(isset($commentsList) && count($commentsList))
                                <div class="form-group">
                                    <div class="box box-widget">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">{{ trans('manage_time::view.Disapprove reason') }}</h3>
                                        </div>
                                        <!-- /.box-header -->
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
                                        <!-- /.box-body -->
                                    </div>
                                </div>
                                @endif
                            </div>
                                    <!-- /.box-body -->

                        <div class="box-footer">
                            @if(isset($isShowFormEdit) && $isShowFormEdit)
                            <button type="submit" class="btn btn-primary" id="submit" onclick="return checkFormLeaveDayRegister();"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Update') }}</button>
                            <input type="hidden" id="check_submit" name="" value="0">
                            <input type="hidden" id="check_status" name="" value="{{ $registerRecord->status }}">

                            <!-- Allow edit modal -->
                            @include('manage_time::include.modal.modal_allow_edit')
                            @endif

                            @if(isset($isAllowApprove) && $isAllowApprove)
                            <button type="button" class="btn btn-success {{ $isAllowApprove ? '' : 'hidden' }}" id="button_approve" value="{{ $registerRecord->register_id }}" data-toggle="modal" data-target="#modal_approve"><i class="fa fa-check"></i> {{ trans('manage_time::view.Approve') }}</button>
                            <button type="button" class="btn btn-danger {{ $isAllowApprove ? '' : 'hidden' }}" id="button_disapprove" value="{{ $registerRecord->register_id }}" data-toggle="modal" data-target="#modal_disapprove"><i class="fa fa-minus-circle"></i> {{ trans('manage_time::view.Not approve') }}</button>

                            <!-- Approve modal -->
                            @include('manage_time::include.modal.modal_approve')

                            <!-- Disapprove modal -->
                            @include('manage_time::include.modal.modal_disapprove')
                            @endif
                        </div>
			        </div>
			    </div>
			</form>
		</div>
		<!-- /.box-body -->
    </div>
    <!-- /. box -->

@include('manage_time::include.modal.modal_leave_days')

@endsection

@section('script-common')
<script>
    var compensationDays = <?php echo json_encode($compensationDays); ?>;
    var weekends = <?php echo json_encode($weekends); ?>;
    /**
     * Store working time setting of employee
     */
    var timeSetting = <?php echo json_encode($timeSetting); ?>;
    var currentEmpId = {{ $registerRecord->creator_id }};
    var token = '{{ csrf_token() }}';
    var urlGetTimeSetting = '{{ route("manage_time::profile.leave.get-time-setting") }}';
    var registerBranch = '{{ $registerBranch }}';
    var text_upload_image = '<?php echo trans('manage_time::view.Upload files'); ?>';
    var leaveSpecialType = {{ LeaveDayReason::SPECIAL_TYPE }};
    var urlCheckRegisterTypeExist = "{{ route('manage_time::profile.leave.check-register-type-exist') }}";
    var textUpload = '{{ trans('manage_time::view.Upload proofs (image file of computer logs, images of meeting with clients...)') }}';
    var disabledDates = [];
    var CalculateFullDay = false;
    if($('#reason option:selected').data('calculate-full-day')){
        CalculateFullDay = true;
    }
</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/leave.register.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.fileuploader.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/comelate.fileupload.js') }}"></script>

    <script type="text/javascript">
    	const USED_LEAVE_DAY = '{{ ManageTimeConst::USED_LEAVE_DAY }}';
    	const MIN_TIME_LEAVE_DAY = '{{ ManageTimeConst::MIN_TIME_LEAVE_DAY }}';
    	const STATUS_APPROVED = '{{ $statusApproved }}';
    	const STATUS_CANCEL = '{{ $statusCancel }}';

    	var urlApprove = '{{ $urlApprove }}';
    	var urlDisapprove = '{{ $urlDisapprove }}';
    	var urlSearchRelatedPerson = '{{ $urlSearchRelatedPerson }}';
    	var urlCheckRegisterExist = '{{ $urlCheckRegisterExist }}';
    	var startDateDefault = '{{ $registerRecord->date_start }}';
    	var endDateDefault = '{{ $registerRecord->date_end }}';
    	var notificationStatusApproved = '<?php echo trans("manage_time::message.The register of leave day has been approved can not edit"); ?>';
    	var notificationStatusCanceled = '<?php echo trans("manage_time::message.The register of leave day has been canceled can not edit"); ?>';

    	var annualHolidays = '{{ implode(', ', $annualHolidays) }}';
    	var arrAnnualHolidays = annualHolidays.split(', ');
    	var specialHolidays = '{{ implode(', ', $specialHolidays) }}';
    	var arrSpecialHolidays = specialHolidays.split(', ');
        var offcialDate = '{{ $curEmp->offcial_date }}';
        var oldNumberDaysOff = {{ $registerRecord->number_days_off }};
        var timeWorkingQuater = <?php echo json_encode($timeWorkingQuater); ?>;
        var reasonIsRelaDie = '{{ LeaveDayReason::REASON_RELATIONSHIP_MEMBER_IS_DIE }}';
        var reasonIsRelaDieJa = '{{ LeaveDayReason::REASON_RELATIONSHIP_MEMBER_IS_DIE_JA }}';
        $(function() {
            $('.select-search-employee').selectSearchEmployee();
	    	$('.select-search-employee-no-pagination').selectSearchEmployeeNoPagination();
        });
        var tooltipLeave = `{!! trans('manage_time::view.Tooltip about leave') !!}`;
        $('.tooltip-leave').attr('data-original-title', tooltipLeave);
        $('.tooltip-leave').attr('title', tooltipLeave);

        if ($('#reason').val() == reasonIsRelaDie || $('#reason').val() == reasonIsRelaDieJa) {
            document.getElementById("list-member-rela-edit").style.display = 'block';
        } else {
            document.getElementById("list-member-rela-edit").style.display = 'none';
        }
        $('#reason').change(function(){
            if ($(this).val() == reasonIsRelaDie || $(this).val() == reasonIsRelaDieJa) {
                document.getElementById("list-member-rela-edit").style.display = 'block';
            } else {
                document.getElementById("list-member-rela-edit").style.display = 'none';
            }
        })
    </script>

    @include('manage_time::include.script_leave')
@endsection
