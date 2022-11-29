@extends('layouts.default')

@section('title')
    {{ trans('manage_time::view.Leave day register') }}
@endsection

<?php 
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\Model\CoreConfigData;
    use Rikkei\ManageTime\View\ManageTimeConst;
    use Rikkei\Team\Model\Team;
    use Rikkei\ManageTime\Model\LeaveDayReason;

    $urlSearchRelatedPerson = route('manage_time::profile.leave.find-employee');
    $urlCheckRegisterExist = route('manage_time::profile.leave.check-register-exist');
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
        <div class="col-md-12" id="mission_register">
            <!-- Box register -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.Register information of leave day') }}</h3>
                </div>
                <!-- /.box-header -->

                <div class="box-body no-padding">
                    <div class="col-lg-10 col-lg-offset-1">
                        <form role="form" method="post" action="{{ route('manage_time::profile.leave.save-admin-register') }}" class="managetime-form-register" id="form-register" enctype="multipart/form-data" autocomplete="off">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-sm-12 managetime-form-group">
                                                <label class="control-label required">{{ trans('manage_time::view.Registrant') }} <em>*</em></label>
                                                <div class="input-box">
                                                    <select id="employee_id" class="form-control select-search-employee" name="employee_id">
                                                    </select>
                                                </div>
                                                <label id="registrant-error" class="managetime-error" for="registrant">{{ trans('manage_time::view.This field is required') }}</label>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label required">{{ trans('manage_time::view.Leave day type') }} <em>*</em></label>
                                                <div class="input-box">
                                                    <select id="reason" class="form-control managetime-select-2" name="reason">
                                                    </select>
                                                    <input name="calculate_full_day" type="hidden" value="0" id="calculate-full-day">
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

                                        <div class="row">
                                            <div class="col-sm-6 managetime-form-group" id="list-member-rela">
                                                <label class="control-label required managetime-label">{{ trans('manage_time::view.Relationship of Member') }} <em>*</em>
                                                </label>
                                                <div class="input-box">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label">{{ trans('manage_time::view.Customer\'s company name') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="company_name" id="company_name" class="form-control"
                                                        placeholder="{{ trans('manage_time::view.Company name where you are onsite') }}"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label">{{ trans('manage_time::view.Customer name') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="customer_name" id="customer_name" class="form-control" 
                                                        placeholder="{{ trans('manage_time::view.Customer name who approved the leave application') }}"/>
                                                </div>
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
                                                <label class="control-label required">{{ trans('manage_time::view.From date') }} <em>*</em></label>
                                                <div class='input-group date' id='datetimepicker-start-date'>
                                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-start">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                    <input type='text' class="form-control managetime-date" name="start_date" id="start_date" data-date-format="DD-MM-YYYY HH:mm"  />
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
                                                    <input type='text' class="form-control" name="end_date" id="end_date" data-date-format="DD-MM-YYYY HH:mm" />
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
                                                    <input type="text" name="number_days_off" id="number_days_off" class="form-control" value="" readonly />
                                                </div>
                                                <label id="end_date_before_start_date-error" class="managetime-error" for="number_days_off">{{ trans('manage_time::view.The number day off must be than 0') }}</label>
                                                <label id="number_days_off-error" class="managetime-error" for="number_days_off">{{ trans('manage_time::view.The day of leave must be smaller day of remain') }}</label>
                                                <label id="reason_special_value-error" class="managetime-error">{{ trans('manage_time::message.The number day is greater than the number day allowed') }}</label>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label">{{ trans('manage_time::view.Number days unapprove') }}</label>
                                                <div class="input-box">
                                                    <input type="text" id="number_unapprove" class="form-control" value="0" readonly />
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="number_validate" id="number_validate" class="form-control" value="" readonly />
                                        <div class="row" id="information_leave_day">
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label">{{ trans('manage_time::view.Number days of used') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="number_days_used" id="number_days_used" class="form-control" value="0" readonly />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label">{{ trans('manage_time::view.Number days of remain japan') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="number_days_remain" id="number_days_remain" class="form-control" value="0" readonly />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label required">{{ trans('manage_time::view.Leave day reason') }} <em>*</em></label>
                                            <div class="input-box">
                                                <textarea id="note" name="note" class="form-control managetime-textarea"></textarea>
                                            </div>
                                            <label id="note-error" class="managetime-error" for="note">{{ trans('manage_time::view.This field is required') }}</label>
                                        </div>
                                        <div class="comelate-upload-file">
                                            <input type="file" name="files" accept="image/*">
                                        </div>
                                    </div>
                                    <!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary" id="submit" onclick="return checkFormLeaveDayRegister();"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Register') }}</button>
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
    /**
     * Store working time setting of employee
     */
    var compensationDays = <?php echo json_encode($compensationDays); ?>;
    var weekends = <?php echo json_encode($weekends); ?>;
    var timeSetting = <?php echo json_encode($timeSetting); ?>;
    var currentEmpId = {{ $userCurrent->id}};
    var token = '{{ csrf_token() }}';
    var urlGetTimeSetting = '{{ route("manage_time::profile.leave.get-time-setting") }}';
    var keyDateInit = '{{ $keyDateInit }}';
    var text_upload_image = '<?php echo trans('manage_time::view.Upload files'); ?>';
    var leaveSpecialType = {{ LeaveDayReason::SPECIAL_TYPE }};
    var urlCheckRegisterTypeExist = "{{ route('manage_time::profile.leave.check-register-type-exist') }}";
    var textUpload = '{{ trans('manage_time::view.Upload proofs (image file of computer logs, images of meeting with clients...)') }}';
    var disabledDates = [];
    var CalculateFullDay = false;
    var timeWorkingQuater = <?php echo json_encode($timeWorkingQuater); ?>;
</script>
    <script type="text/javascript">
        var chooseEmpText = 'Chọn nhân viên';
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.shorten.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/leave.register.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.fileuploader.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/comelate.fileupload.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>

    <script type="text/javascript">
        var urlSearchRelatedPerson = '{{ $urlSearchRelatedPerson }}';
        var urlCheckRegisterExist = '{{ $urlCheckRegisterExist }}';
        var urlAjaxGetApprover = '{{ route('manage_time::profile.leave.ajax-get-approver') }}';
        var urlAjaxGetLeaveDayReason = '{{ route('manage_time::profile.leave.ajax-get-leave-reason') }}';
        var urlAjaxGetLeaveDayRelation = '{{ route('manage_time::profile.leave.ajax-get-leave-relation') }}';
        const USED_LEAVE_DAY = '{{ ManageTimeConst::USED_LEAVE_DAY }}';
        const MIN_TIME_LEAVE_DAY = '{{ ManageTimeConst::MIN_TIME_LEAVE_DAY }}';
        var startDateDefault = new Date();
        startDateDefault.setHours(timeSetting[currentEmpId][keyDateInit]['morningInSetting']['hour'], timeSetting[currentEmpId][keyDateInit]['morningInSetting']['minute']);
        var endDateDefault = new Date();
        endDateDefault.setHours(timeSetting[currentEmpId][keyDateInit]['afternoonOutSetting']['hour'], timeSetting[currentEmpId][keyDateInit]['afternoonOutSetting']['minute']);

        var annualHolidays = '{{ implode(', ', $annualHolidays) }}';
        var arrAnnualHolidays = annualHolidays.split(', ');
        var specialHolidays = '{{ implode(', ', $specialHolidays) }}';
        var arrSpecialHolidays = specialHolidays.split(', ');
        var offcialDate = '{{ $userCurrent->offcial_date }}';
        var oldNumberDaysOff = 0;
        var registerBranch = '{{ $registerBranch }}';
        var reasonIsRelaDie = '{{ LeaveDayReason::REASON_RELATIONSHIP_MEMBER_IS_DIE }}';
        var reasonIsRelaDieJa = '{{ LeaveDayReason::REASON_RELATIONSHIP_MEMBER_IS_DIE_JA }}';

        $('.select-search-employee').select2({
            ajax: {
                url: urlSearchRelatedPerson,
                dataType: "JSON",
                data: function (params) {
                    return {
                        q: $.trim(params.term)
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
        $('#employee_id').on('change', function(e) {
            getApprover();
            getLeaveDayReason();

            currentEmpId = $('#employee_id').val();

            //reload salary_rate
            var rate = $('#reason').find('option:selected').data('salary_rate');
            if (rate === undefined) {
                rate = 100;
            }
            $('#salary_rate').val(rate+ ' %');
        });

        function getApprover() {
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
                    $('#registrant-error').hide();
                    $('#information_leave_day').html(data.htmlLeaveDay);
                    offcialDate = data.empSelected['offcial_date'];
                    $('#number_unapprove').val(data.regsUnapporve);

                    if (timeSetting[employeeId] === undefined) {
                        timeSetting[employeeId] = {};
                    }
                    timeSetting[employeeId] = data['timeSettingNew'][employeeId];
                    timeWorkingQuater[employeeId] = data['timeWorkingQuater'][employeeId];

                    //reload from date, to date by employee setting
                    setStartDateOnChange(startTime, keyDateInit, employeeId);
                    setEndDateOnChange(endTime, keyDateInit, employeeId);

                    //Check day off before offcial date
                    var reasonCode = $('#reason').find(":selected").attr("data-reason-code");
                    if (reasonCode == USED_LEAVE_DAY) {
                        if(checkRegisterBeforeOffcial()) {
                            $('#day_off_before_offcial_error').show();
                            status = 0;
                        } else {
                            $('#day_off_before_offcial_error').hide();
                        }
                    } else {
                        $('#day_off_before_offcial_error').hide();
                    }
                }
            });
        }

        function getLeaveDayReason() {
            var employeeId = $('#employee_id').val();
            $.ajax({
                url: urlAjaxGetLeaveDayReason,
                type: 'GET',
                data: {
                    employee_id: employeeId,
                },
                success: function (data) {
                    $('#reason').html('');
                    var options = [];
                    $.each(data, function(index, item){
                        options.push({
                            id: item.id,
                            salary_rate: item.salary_rate,
                            used_leave_day: item.used_leave_day,
                            type: item.type,
                            value: item.value,
                            repeated: item.repeated,
                            unit: item.unit,
                            calculate_full_day: item.calculate_full_day,
                            reason_name: parseInt(index + 1) + '. ' + item.reason_name
                        })
                    });
                    currentEmpId = $('#employee_id').val();
                    var itemTpl = $('script[data-template="reason-option-item"]').text().split(/\$\{(.+?)\}/g);

                    $('#reason').append(options.map(function(item) {
                        return itemTpl.map(render(item)).join('');
                    }));
                }
            });
        }

        function getLeaveDayRelation() {
            var employeeId = $('#employee_id').val();
            $.ajax({
                url: urlAjaxGetLeaveDayRelation,
                type: 'GET',
                data: {
                    employee_id: employeeId,
                },
                success: function (data) {
                    let html = '';
                    html += `<label class="control-label required managetime-label">{{ trans('manage_time::view.Relationship of Member') }} <em>*</em></label><br>`
                    $.each(data, function(index, value) {
                        html += `<input type="checkbox" name="employee_relationship[]" value="${value.r_id}"> ${value.r_name} - ${value.r_relationship_name} <br>`;
                    });
                    $('#list-member-rela').html(html);

                }
            });
        }

        function render(props) {
            return function(tok, i) {
                return (i % 2) ? props[tok] : tok;
            };
        }

        if ($('#reason').val() == reasonIsRelaDie || $('#reason').val() == reasonIsRelaDieJa) {
            getLeaveDayRelation();
        }

        $('#reason').change(function(){
            if ($(this).val() == reasonIsRelaDie || $(this).val() == reasonIsRelaDieJa) {
                getLeaveDayRelation();
            }
        })
    </script>
    <script type="text/template" data-template="reason-option-item">
        <option value="${id}" data-salary-rate="${salary_rate}"
                data-reason-code="${used_leave_day}"
                data-type="${type}" data-value="${value}" data-repeated="${repeated}"
                data-unit="${unit}" data-calculate-full-day="${calculate_full_day}">${reason_name}</option>
    </script>

    @include('manage_time::include.script_leave')
@endsection
