@extends('layouts.default')

@section('title')
    {{ trans('manage_time::view.Supplement register') }}
@endsection

<?php 
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\Model\CoreConfigData;
    use Rikkei\Team\Model\Team;
    use Rikkei\ManageTime\Model\SupplementReasons;

    $teamCodePrefix = Team::getOnlyOneTeamCodePrefixChange($userCurrent);
    $annualHolidays = CoreConfigData::getAnnualHolidays(2);
    $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);
    $urlSearchRelatedPerson = route('manage_time::profile.supplement.find-employee');
    $urlCheckRegisterExist = route('manage_time::profile.supplement.check-register-exist');
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
            <div class="box box-primary" id="mission_register">
                <div class="box-header with-border">
                    <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.Register information of supplement') }}</h3>
                </div>
                <!-- /.box-header -->

                <div class="box-body no-padding">
                    <div class="col-lg-10 col-lg-offset-1">
                        <form role="form" method="post" action="{{ route('manage_time::profile.supplement.save-admin-register') }}" id="form-register" enctype="multipart/form-data" autocomplete="off">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-sm-12 managetime-form-group">
                                                <label class="control-label required">{{ trans('manage_time::view.Registrant') }} <em>*</em></label>
                                                <div class="input-box">
                                                    <select id="employee_id" class="form-control select-search-employee" name="employee_id" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
                                                    </select>
                                                </div>
                                                <label id="registrant-error" class="managetime-error" for="registrant">{{ trans('manage_time::view.This field is required') }}</label>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label required">{{ trans('manage_time::view.From date') }} <em>*</em></label>
                                                <div class='input-group date' id='datetimepicker-start-date'>
                                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-start">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                    <input type='text' class="form-control managetime-date" name="start_date" id="start_date" data-date-format="DD-MM-YYYY HH:mm" data-toggle="tooltip" title="{!! trans('manage_time::view.Start date tooltip') !!}" data-html="true" />
                                                </div>
                                                <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
                                                <label id="register_exist_error" class="managetime-error" for="start_date">{{ trans('manage_time::message.Registration time has been identical') }}</label>
                                                 <label id="supplement_ot_one_day" class="managetime-error" for="end_date">{{ trans('manage_time::view.Register supplement OT must be in a day') }}</label>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label required">{{ trans('manage_time::view.End date') }} <em>*</em></label>
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
                                                <label class="control-label">{{ trans('manage_time::view.Number of days supplement') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="number_days_off" id="number_days_off" class="form-control" value="" readonly />
                                                </div>
                                                <label id="end_date_before_start_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.The number days supplement of min is 0.5') }}</label>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label">&nbsp;</label>
                                                <div class="input-box">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" name="is_ot" value="1"> {{ trans('manage_time::view.Is OT') }}
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
                                                <textarea id="reason" name="reason" class="form-control required managetime-textarea {{ $reasons ? 'hidden' : '' }}"
                                                    onkeyup="checkInputReasonKeyup()"
                                                    placeholder="{{ trans('manage_time::view.Supplement reason') }}"></textarea>
                                            </div>
                                            <label id="reason-error" class="managetime-error" for="reason">{{ trans('manage_time::view.This field is required') }}</label>
                                        </div>

                                        <div class="managetime-upload-file">
                                            <input type="file" name="files" id="image_upload" accept="image/*">
                                            <label id="image_upload-error" class="managetime-error" >{{ trans('manage_time::view.This field is required') }}</label>
                                        </div>
                                    </div>
                                    <!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary" id="submit"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Register') }}</button>
                                        <input type="hidden" id="check_submit" name="" value="0">
                                        <input type="hidden" name="admin" value="1">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /. box -->
        </div>
    </div>
    <!-- /.row -->
@endsection

@section('script')
<script>
    var teamCode = '{{ $teamCodePrefix }}';
    var codeJp = '{{ Team::CODE_PREFIX_JP }}';
    var isEmpJp = teamCode === codeJp;
    /**
     * Store working time setting of employee
     */
    var compensationDays = <?php echo json_encode($compensationDays); ?>;
    var timeSetting = <?php echo json_encode($timeSetting); ?>;
    var currentEmpId = {{ $userCurrent->id}};
    var token = '{{ csrf_token() }}';
    var urlGetTimeSetting = '{{ route("manage_time::profile.leave.get-time-setting") }}';
    var keyDateInit = '{{ $keyDateInit }}';
    var typeOther = {{ SupplementReasons::TYPE_OTHER }};
    var typeImageRequired = {{ SupplementReasons::IS_IMAGE_REQUIRED }};

    // Variable check is working in Japan
    var isWorkingJP = false;
    @if ($reasons)
        isWorkingJP = true;
    @endif
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
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.fileuploader.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>

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
        var empProjects = <?php echo json_encode($empProjects); ?>;

        $('#employee_id').on('change', function(e) {
            $('#registrant-error').hide();
            var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
            var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
            var startTime = new Date(startDate);
            var endTime = new Date(endDate);
            var startDateKey = startTime.format('Y-m-d');
            var endDateKey = endTime.format('Y-m-d');
            var employeeId = $('#employee_id').val();
            $.ajax({
                url: urlAjaxGetApprover,
                type: 'GET',
                data: {
                    dateKey: keyDateInit,
                    employeeId: employeeId,
                    start_at: startDateKey,
                    end_at: endDateKey,
                },
                success: function (data) {
                    var employeeId = $('#employee_id').val();
                    $('#information_leave_day').html(data.htmlLeaveDay);
                    $('#number_unapprove').val(data.regsUnapporve);
                    if (timeSetting[employeeId] === undefined) {
                        timeSetting[employeeId] = {};
                    }
                    timeSetting[employeeId] = data['timeSettingNew'][employeeId];
                    empProjects = data.empProjects;

                    //reload from date, to date by employee setting
                    if ($('input[name=is_ot]').is(':checked')) {
                        var employeeId = currentEmpId;
                        setStartTimeSupplementOt(startTime, endTime, employeeId, startDateKey, startTime, null);
                        setEndTimeSupplementOt(startTime, endTime, employeeId, endDateKey, endTime, null);
                    } else {
                        setStartDateOnChange(startTime, startDateKey, employeeId);
                        setEndDateOnChange(endTime, endDateKey, employeeId);
                    }
                }
            });
        });
    </script>
@endsection
