@extends('layouts.default')

@section('title')
    {{ trans('manage_time::view.Business trip register') }}
@endsection

<?php 
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\Model\CoreConfigData;

    $annualHolidays = CoreConfigData::getAnnualHolidays(2);
    $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePre);

    $urlSearchRelatedPerson = route('manage_time::profile.mission.find-employee');
    $urlCheckRegisterExist = route('manage_time::profile.mission.check-register-exist');
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/jquery.fileuploader.css') }}" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Box register -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.Register information of business trip') }}</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    <div class="col-lg-10 col-lg-offset-1">
                        <form role="form" method="post" action="{{ route('manage_time::profile.mission.save-admin-register') }}" class="managetime-form-register" id="form-register" enctype="multipart/form-data" autocomplete="off">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-sm-9 managetime-form-group">
                                                <label class="control-label required">{{ trans('manage_time::view.Registrant') }} <em>*</em></label>
                                                <div class="input-box">
                                                    <select id="employee_id" class="form-control select-search-employee" name="employee_id" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
                                                    </select>
                                                </div>
                                                <label id="registrant-error" class="managetime-error" for="registrant">{{ trans('manage_time::view.This field is required') }}</label>
                                            </div>
                                            <div class="col-sm-3">
                                                <div style="margin-top:10px">
                                                    <label class="checkbox-inline"><input type="checkbox" id="checkbox-serach-employee-type">Tìm được cả nhân đã nghỉ việc</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label required">{{ trans('manage_time::view.Out date') }} <em>*</em></label>
                                                <div class='input-group date' id='datetimepicker-start-date'>
                                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-start">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                    <input type='text' class="form-control managetime-date" name="start_date" id="start_date" data-date-format="DD-MM-YYYY HH:mm" data-toggle="tooltip" title="{!! trans('manage_time::view.Start date tooltip') !!}" data-html="true" />
                                                </div>
                                                <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
                                                <label id="register_exist_error" class="managetime-error" for="start_date">{{ trans('manage_time::message.Registration time has been identical') }}</label>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label required">{{ trans('manage_time::view.On date') }} <em>*</em></label>
                                                <div class='input-group date' id='datetimepicker-end-date'>
                                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-end">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                    <input type='text' class="form-control" name="end_date" id="end_date" data-date-format="DD-MM-YYYY HH:mm" data-toggle="tooltip" title="{!! trans('manage_time::view.End date tooltip') !!}" data-html="true" />
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
                                                <label class="control-label" for="mt-is-long">{{ trans('manage_time::view.is long') }}</label>
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
                                                <textarea id="reason" name="reason" class="form-control required managetime-textarea" onkeyup="checkInputReasonKeyup()"></textarea>
                                            </div>
                                            <label id="reason-error" class="managetime-error" for="reason">{{ trans('manage_time::view.This field is required') }}</label>
                                        </div>
                                    </div>
                                    <!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary" id="submit" onclick="return checkFormMissionRegisterByAdmin('{{ $urlCheckRegisterExist }}');"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Register') }}</button>
                                        <input type="hidden" id="check_submit" name="" value="0">
                                        <input type="hidden" name="admin" value="1">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
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
        </div>
    </div>
    <!-- /.row -->
@endsection

@section('script')
<script>
    var isEmpJp = false;
    /**
     * Store working time setting of employee
     */
    var compensationDays = <?php echo json_encode($compensationDays); ?>;
    var timeSetting = <?php echo json_encode($timeSetting); ?>;
    var currentEmpId = {{ $userCurrent->id }};
    var token = '{{ csrf_token() }}';
    var keyDateInit = '{{ $keyDateInit }}';
    var urlGetTimeSetting = '{{ route("manage_time::profile.leave.get-time-setting") }}';
    var provinces = {!!json_encode($provinces)!!},
        country = {!!json_encode($country)!!},
        countryActive = {
            time: '{!!$registerRecord->country_id!!}',
        },
        provinceActive = {
            time: '{!!$registerRecord->province_id!!}',
        };
    const VN = "{{ $vn }}";
    const JP = "{{ $jp }}"
    var textUpload = '{{ trans('manage_time::view.Upload proofs (image file of computer logs, images of meeting with clients...)') }}';
</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.shorten.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/register.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.fileuploader.js') }}"></script>
    
    <script type="text/javascript">
        var urlSearchRelatedPerson = '{{ $urlSearchRelatedPerson }}';
        var urlCheckRegisterExist = '{{ $urlCheckRegisterExist }}';
        var urlAjaxGetApprover = '{{ route('manage_time::profile.supplement.ajax-get-approver') }}';
        var startDateDefault = new Date();
        startDateDefault.setHours(timeSetting[currentEmpId][keyDateInit]['morningInSetting']['hour'], timeSetting[currentEmpId][keyDateInit]['morningInSetting']['minute']);
        var endDateDefault = new Date();
        endDateDefault.setHours(timeSetting[currentEmpId][keyDateInit]['afternoonOutSetting']['hour'], timeSetting[currentEmpId][keyDateInit]['afternoonOutSetting']['minute']);

        var annualHolidays = '{{ implode(', ', $annualHolidays) }}';
        var arrAnnualHolidays = annualHolidays.split(', ');
        var specialHolidays = '{{ implode(', ', $specialHolidays) }}';
        var arrSpecialHolidays = specialHolidays.split(', ');

        $('#employee_id').on('change', function(e) {
            $('#registrant-error').hide();
            var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
            var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
            var startTime = new Date(startDate);
            var endTime = new Date(endDate);
            var employeeId = $('#employee_id').val();
            $.ajax({
                url: urlAjaxGetApprover,
                type: 'GET',
                data: {
                    dateKey: keyDateInit,
                    employeeId: employeeId,
                    start_at: startTime.getFullYear() + '-' + get2Digis(startTime.getMonth() + 1) + '-' + startTime.getDate(),
                    end_at: endTime.getFullYear() + '-' + get2Digis(endTime.getMonth() + 1) + '-' + endTime.getDate(),
                },
                success: function (data) {
                    $('#information_leave_day').html(data.htmlLeaveDay);
                    $('#number_unapprove').val(data.regsUnapporve);
                    if (timeSetting[employeeId] === undefined) {
                        timeSetting[employeeId] = {};
                    }
                    timeSetting[employeeId] = data['timeSettingNew'][employeeId];

                    //reload from date, to date by employee setting
                    setStartDateOnChange(startTime, keyDateInit, employeeId);
                    setEndDateOnChange(endTime, keyDateInit, employeeId);
                }
            });
        });
    </script>
@endsection
