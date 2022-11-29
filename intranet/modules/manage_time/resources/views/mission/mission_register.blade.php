@extends('manage_time::layout.common_layout')

@section('title-common')
    {{ trans('manage_time::view.Business trip register') }}
@endsection

<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\Model\CoreConfigData;
    use Rikkei\Team\Model\Team;

    $annualHolidays = CoreConfigData::getAnnualHolidays(2);
    $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePreOfEmp);

    $urlSearchRelatedPerson = route('manage_time::profile.mission.find-employee');
    $urlCheckRegisterExist = route('manage_time::profile.mission.check-register-exist');
?>

@section('css-common')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/jquery.fileuploader.css') }}" />
    <style>
        .box .table-responsive {
            margin-left: 0px;
            margin-right: 0px
        }
    </style>
@endsection

@section('sidebar-common')
	@include('manage_time::include.sidebar_mission')
@endsection

@section('content-common')
    <div class="se-pre-con"></div>
    <!-- Box mission list -->
    <div class="box box-primary" id="mission_register">
        <div class="box-header with-border">
		    <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.Business trip register') }}</h3>
		</div>
		<!-- /.box-header -->

		<div class="box-body no-padding">
			<form role="form" method="post" action="{{ route('manage_time::profile.mission.save') }}" class="managetime-form-register" id="form-register" enctype="multipart/form-data" autocomplete="off">
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
                                                        <input type="text" name="employee_code" id="employee_code" class="form-control" value="{{ $registrantInformation->employee_code }}" disabled />
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
				                        <select id="approver" class="form-control select-search-employee-no-pagination" name="approver" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee-can-approve', ['route' => 'manage_time::manage-time.manage.mission.approve']) }}">
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
				                    <label class="control-label required">{{ trans('manage_time::view.Out date') }} <em>*</em> <span class="fa fa-question-circle" data-toggle="tooltip" title="{!! trans('manage_time::view.Start date tooltip') !!}" data-html="true" ></span></label>
				                    <div class='input-group date' id='datetimepicker-start-date'>
			                            <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-start">
			                                <span class="glyphicon glyphicon-calendar"></span>
			                            </span>
			                            <input type='text' class="form-control managetime-date" name="date_start" id="start_date" data-date-format="DD-MM-YYYY HH:mm"/>
			                        </div>
			                        <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
			                        <label id="register_exist_error" class="managetime-error" for="start_date">{{ trans('manage_time::message.Registration time has been identical') }}</label>
			                	</div>
			                	<div class="col-sm-6 managetime-form-group">
				                    <label class="control-label required">{{ trans('manage_time::view.On date') }} <em>*</em> <span class="fa fa-question-circle" data-toggle="tooltip" title="{!! trans('manage_time::view.End date tooltip') !!}" data-html="true" ></span></label>
				                    <div class='input-group date' id='datetimepicker-end-date'>
			                            <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-end">
			                                <span class="glyphicon glyphicon-calendar"></span>
			                            </span>
			                            <input type='text' class="form-control" name="date_end" id="end_date" data-date-format="DD-MM-YYYY HH:mm" />
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
				                    <label class="control-label">{{ trans('manage_time::view.Number of days business trip') }}</label>
				                    <div class="input-box">
				                        <input type="text" name="number_days_off" id="number_days_off" class="form-control" value="" readonly />
				                    </div>
			                        <label id="end_date_before_start_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.The number days business trip of min is 0.5') }}</label>
			                	</div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label" for="mt-is-long">{{ trans('manage_time::view.is long') }}
                                                </label>
                                                <div class="input-box">
                                                    <input type="checkbox" name="is_long" id="mt-is-long" class="" value="1" />
                                                </div>
                                            </div>
			                </div>

                                        <div class="row">
                                            <div class="col-sm-6 managetime-form-group">
				                <label class="control-label required">{{ trans('manage_time::view.Country') }} <em>*</em></label>
                                                <div class="input-box">
                                                    <select name="country_id" data-cp-country="time" id="country_id"></select>
                                                </div>
                                                <label id="country_id-error" class="managetime-error" for="country_id">{{ trans('manage_time::view.This field is required') }}</label>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group" data-cp-province-wrapper="time">
                                                <label class="control-label required managetime-label">{{ trans('manage_time::view.Province') }} <em id="add-text-required">*</em></label>
                                                <div class="input-box">
                                                    <select name="province_id" id="province_id" data-cp-province="time"></select>
                                                </div>
                                                <label id="province_id-error" class="managetime-error" for="province_id">{{ trans('manage_time::view.This field is required') }}</label>
                                            </div>
			                </div>

			                <div class="row">
			                	<div class="col-sm-6 managetime-form-group">
				                    <label class="control-label required">{{ trans('manage_time::view.Location') }} <em>*</em></label>
				                    <div class="input-box">
				                        <input type="text" id="location" name="location" class="form-control" value="" onkeyup="checkInputKeyup()" />
				                    </div>
				                    <label id="location-error" class="managetime-error" for="location">{{ trans('manage_time::view.This field is required') }}</label>
			                	</div>
			                	<div class="col-sm-6 managetime-form-group">
				                    <label class="control-label required managetime-label">{{ trans('manage_time::view.Company name of customer') }}</label>
				                    <div class="input-box">
				                        <input type="text" id="company_customer" name="company_customer" class="form-control" value="" />
				                    </div>
			                	</div>
			                </div>
						
			                <div class="form-group">
			                    <label class="control-label required">{{ trans('manage_time::view.Purpose') }} <em>*</em></label>
			                    <div class="input-box">
			                        <textarea id="reason" name="purpose" class="form-control required managetime-textarea" onkeyup="checkInputReasonKeyup()"></textarea>
			                    </div>
			                    <label id="reason-error" class="managetime-error" for="reason">{{ trans('manage_time::view.This field is required') }}</label>
			                </div>
                                        @include('manage_time::mission.include.mission_together')
			            </div>
			            <!-- /.box-body -->

			            <div class="box-footer">
			                <button type="submit" class="btn btn-primary" id="submit" onclick="return checkFormMissionRegister('{{ $urlCheckRegisterExist }}');"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Register') }}</button>
			                <input type="hidden" id="check_submit" name="" value="0">
			            </div>
			        </div>
			    </div>
			</form>
		</div>
		<!-- /.box-body -->
		<div class="box box-primary">
			<div class="box-body font-size-14">
				<h4><b>{{ trans('manage_time::view.guide') }}</b></h4>
				<ul>
					<li>{!! trans('manage_time::view.guide_line_1') !!}</li>
					<li>{!! trans('manage_time::view.guide_line_2') !!}</li>
					<li>{!! trans('manage_time::view.guide_line_3') !!}</li>
					<li>{!! trans('manage_time::view.guide_line_4') !!}</li>
				</ul>
			</div>
		</div>
    </div>
    <!-- /. box -->
@endsection

@section('script-common')
<script>
    var isEmpJp = false;
    var compensationDays = <?php echo json_encode($compensationDays); ?>;
    /**
     * Store working time setting of employee
     */
    var timeSetting = <?php echo json_encode($timeSetting); ?>;
    var currentEmpId = {{ $registrantInformation->id }};
    var token = '{{ csrf_token() }}';
    var keyDateInit = '{{ $keyDateInit }}';
    var urlGetTimeSetting = '{{ route("manage_time::profile.leave.get-time-setting") }}';
    var provinces = {!!json_encode($provinces)!!},
        country = {!!json_encode($country)!!},
        countryActive = {
            time: null,
        },
        provinceActive = {
            time: null,
        };
    const VN = "{{ $vn }}";
    const JP = "{{ $jp }}";
    var textUpload = '{{ trans('manage_time::view.Upload proofs (image file of computer logs, images of meeting with clients...)') }}';
</script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/register.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.fileuploader.js') }}"></script>

    <script type="text/javascript">
    	var urlSearchRelatedPerson = '{{ $urlSearchRelatedPerson }}';
    	var startDateDefault = new Date();
        startDateDefault.setHours(timeSetting[currentEmpId][keyDateInit]['morningInSetting']['hour'], timeSetting[currentEmpId][keyDateInit]['morningInSetting']['minute']);
        var endDateDefault = new Date();
        endDateDefault.setHours(timeSetting[currentEmpId][keyDateInit]['afternoonOutSetting']['hour'], timeSetting[currentEmpId][keyDateInit]['afternoonOutSetting']['minute']);

    	var annualHolidays = '{{ implode(', ', $annualHolidays) }}';
    	var arrAnnualHolidays = annualHolidays.split(', ');
    	var specialHolidays = '{{ implode(', ', $specialHolidays) }}';
    	var arrSpecialHolidays = specialHolidays.split(', ');
        var pageType = "{{ $pageType }}";

        $(function() {
            $('.select-search-employee').selectSearchEmployee();
            $('.select-search-employee-no-pagination').selectSearchEmployeeNoPagination();
        });
    </script>
@endsection
