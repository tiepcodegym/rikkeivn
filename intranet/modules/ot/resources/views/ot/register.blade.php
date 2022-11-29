@extends('layouts.default')

@section('title', trans('ot::view.Register OT'))

@section('css')
<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Ot\View\OtPermission;
    use Rikkei\Team\Model\Team;
    use Rikkei\Core\Model\CoreConfigData;
    use Rikkei\Team\View\Permission;
    use Carbon\Carbon;
    
    $isCompanyPermission = OtPermission::isScopeManageOfCompany();
    $isTeamPermission = OtPermission::isScopeManageOfTeam();

    if (empty($applicant)) {
        $applicant = Permission::getInstance()->getEmployee();
    }
    $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($applicant);
    $annualHolidays = json_encode(CoreConfigData::getAnnualHolidays(2));
    $specialHolidays = json_encode(CoreConfigData::getSpecialHolidays(2, $teamCodePrefix));
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
@if ($pageType == 'create' || $pageType == 'edit')
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
@endif
<link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_ot/css/register_ot.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_ot/css/list_ot.css') }}" />
<style type="text/css">
    .ot-error {
        display: none;
        color: red;
    }
    #exist_time_lot_before_submit_error {
        margin-top: 10px;
    }
    #exist_time_lot_before_submit_error ul li:first-child {
        list-style: none;
    }
    .set-time-break-item label {
        font-weight: normal !important;
        padding-top: 8px;
    }
    #addEmpForm .ot-form-group .control-label {
        font-weight: 600;
    }
    .select2-container .select2-selection--single .select2-selection__rendered {
        padding-left: 0px;
        padding-right: 0px;
    }
</style>
@endsection

@section('content')
    <div class="row">
        <!-- Menu left -->
        <div class="col-lg-2 col-md-3">
            @include('ot::include.menu_left')
        </div>
        <!-- /.col -->
        <div class="col-lg-10 col-md-9 content-ot">
            <div class="box box-primary">
                @if ($pageType == 'create' || $pageType == 'edit')
                    @include('ot::include.edit')
                @else
                    @include('ot::include.list')
                @endif
            </div>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->    
    <div>
        @include('ot::include.modals.info_preview')
    </div>
    <div>
        @include('ot::include.modals.delete_warning')
    </div>
    <div>
        @include('ot::include.modals.approve_confirm')
    </div>
    <div>
        @include('ot::include.modals.reject_confirm')
    </div>
    <div>
        @include('ot::include.modals.save_confirm')
    </div>
@endsection

@section('script')
<script>
    var teamCode = '{{ $teamCodePrefix }}';
    var codeJp = '{{ Team::CODE_PREFIX_JP }}';
    var isEmpJp = teamCode === codeJp;
    /**
     * Store working time setting of employee
     */
    @if ($pageType == 'edit' || $pageType == 'create')
    var timeSetting = <?php echo empty($timeSetting) ? null : json_encode($timeSetting); ?>;
    @endif
    var currentEmpId = {{ $pageType == 'edit' ? $registerInfo->employee_id : $applicant->id }};
    var token = '{{ csrf_token() }}';
    var keyDateInit = '{{ empty($keyDateInit) ? "" : $keyDateInit }}';
    var urlGetTimeSetting = '{{ route("manage_time::profile.leave.get-time-setting") }}';
    var teamCodePreOfEmp = '';
    @if (isset($teamCodePreOfEmp)) 
        teamCodePreOfEmp = '{{ $teamCodePreOfEmp }}';
    @endif
    var breakTimesEmployees = {};
    jQuery(document).ready(function ($) {
        //Store break time of every employees
        $('#table_ot_employees tbody tr').each(function() {
            var tr = $(this);
            var empId = tr.attr('id');
            breakTimesEmployees[empId] = {};
            tr.find('.has-set-time-break-edit .set-time-break-item').each(function() {
                var item = $(this);
                var date = item.find('.time-break-date-val').data('date');
                var valueBreak = item.find('.time-break-value').val();
                breakTimesEmployees[empId][date] = valueBreak;
            });
        });
    });
</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/additional-methods.min.js"></script>
{{--    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>--}}
{{--    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>--}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-range/3.0.3/moment-range.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.6/jquery.number.js"></script>
    @if ($pageType == 'create' || $pageType == 'edit')
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    @endif
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.shorten.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_ot/js/otregister.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_ot/js/otlist.js') }}"></script>
    <script>
        @if ($pageType == 'create' || $pageType == 'edit')
            var projectSelected = jQuery('#project_list').val();
            var projectAllowedOT18Key = null;
            @if ($projectAllowedOT18Key)
                projectAllowedOT18Key = <?php echo json_encode($projectAllowedOT18Key); ?>;
            @endif
        @endif   
        const urlcheckOccupiedTimeSlot= "{{ route('ot::ot.checkOccupiedTimeSlot') }}";
        const urlAjaxCheckOccupiedTimeSlot= "{{ route('ot::ot.ajaxCheckOccupiedTimeSlot') }}";
        const urlAjaxCheckExistTimeSlotByEmployees= "{{ route('ot::ot.ajaxCheckExistTimeSlotByEmployees') }}";
        const urlProjectMember = "{{ route('ot::ot.getProjectMember') }}";
        const urlTeamEmployees = "{{ route('ot::ot.getTeamEmployee') }}";
        const urlSearchEmp = "{{ route('ot::ot.searchemp') }}";
        const urlSearchReg = "{{ route('ot::ot.searchreg') }}";
        const urlEditReg = "{{ route('ot::ot.editot') }}";
        const urlProjectApprover = "{{ route('ot::ot.getProjectApprovers') }}";
        const urlApproveRegister = "{{ route('ot::ot.approver.approve') }}";
        const urlRejectRegister = "{{ route('ot::ot.approver.reject') }}";
        const urlMassApproveRegister = "{{ route('ot::ot.approver.massApprove') }}";
        const urlMassRejectRegister = "{{ route('ot::ot.approver.massReject') }}";

        var arrEmpPro = <?php echo json_encode($arrEmpPro); ?>;
        var pageType = "{{ $pageType }}";
        var isEditable = "{{ $isEditable }}";
        var annualHolidayList = {!! $annualHolidays !!};
        var specialHolidayList = {!! $specialHolidays !!};
        var errList = [
            "{{ trans('ot::message.This field is required') }}",
            "{{ trans('ot::message.Out of time range') }}",
            "",
            "{{ trans('ot::message.Break time invalid') }}",
            "{{ trans('ot::message.Time slot taken') }}",
            "{{ trans('ot::message.Register time OT same day') }}",
            "{{ trans('ot::message.Number') }}",
            "{{ trans('ot::message.Employee already included') }}",
            "{{ trans('ot::message.OT time not filled') }}"
        ];
        var viewList = [
            "{{ trans('ot::view.No more project member') }}"
        ];
        var titleList = [
            "{{ trans('ot::view.Create new Ot Employee') }}",
            "{{ trans('ot::view.Edit new Ot Employee') }}"
        ];
        var roleIdList = [
            "{{ Team::ROLE_TEAM_LEADER }}",
            "{{ Team::ROLE_SUB_LEADER }}",
            "{{ Team::ROLE_MEMBER }}",
        ];
        $.extend($.fn.dataTable.defaults, {
            paging: false,
            info: false,
            searching: false
        });
        var isScopeManageOfCompany = "{{ $isCompanyPermission }}";
        var tableProjsMember = initDataTable('#memberTbl');
        var tableTeamEmp = initDataTable('#otherEmployeeTbl');
        var timeBreakOrigin = {};
        @if (isset($breakTimeByRegister) && count($breakTimeByRegister))
            @foreach ($breakTimeByRegister as $item)
                timeBreakOrigin["{{ Carbon::parse($item->ot_date)->format('d/m/Y') }}"] = "{{ number_format($item->break_time, 2) }}";
            @endforeach
        @endif

        jQuery(document).ready(function ($) {
            var startDate = moment($('#datetimepicker_start').find('input:first').val(), 'DD-MM-YYYY');
            var endDate = moment($('#datetimepicker_end').find('input:first').val(), 'DD-MM-YYYY');
            if (startDate != null && endDate != null) {
                startDate = new Date(startDate);
                endDate = new Date(endDate);
                startDate.setHours(00, 00);
                endDate.setHours(00, 00);
                if (hasHolidayOrWeekend(startDate, endDate, annualHolidayList, specialHolidayList)) {
                    $('#form_set_time_break').show();
                } else {
                    $('#form_set_time_break').hide();
                }
                if (pageType == 'create') {
                    for (var date = startDate; date <= endDate; date.setDate(date.getDate() + 1)) {
                        var holidayOrWeekend = isHolidayOrWeekend(date, annualHolidayList, specialHolidayList);
                        if (holidayOrWeekend) {
                            var twoDigitMonth = date.getMonth() + 1;
                            twoDigitMonth = twoDigitMonth.toString();
                            if (twoDigitMonth.length === 1) {
                                twoDigitMonth = "0" + twoDigitMonth;
                            }
                            var twoDigitDate = date.getDate();
                            twoDigitDate = twoDigitDate.toString();
                            if (twoDigitDate.length === 1) {
                                twoDigitDate = "0" + twoDigitDate;
                            }
                            var daysOfWeek = getDayOfWeek(date.getDay());
                            $('#duplicate_set_time_break_item .time-break-date .time-break-date-val').text(twoDigitDate + '/' + twoDigitMonth + '/' + date.getFullYear());
                            $('#duplicate_set_time_break_item .time-break-date .time-break-date-day').text(' (' + daysOfWeek + ')');
                            var html = $('#duplicate_set_time_break_item').html();
                            $('#has_set_time_break').append(html);
                        }
                    }
                }
            }
            $('.select-search-approver').selectSearchEmployeeCanapprove();
            window['moment-range'].extendMoment(moment);
            //init register form (create or edit)
            initForm(pageType);
            //checkbox
            controlListCheckBox();
            checkRejectReason();

            $('.ot-select-2').select2({
                minimumResultsForSearch: 3,
            });

            selectSearchReload();

            var pgurl = window.location.href.substr(window.location.href);
            $('.menu-ot li a').each(function() {
                if ($(this).attr('href') == pgurl) {
                    $(this).parent().addClass('active');
                }
            });

            $('.filter-date').datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy',
                weekStart: 1,
                todayHighlight: true
            });

            $('.filter-date').on('keyup', function(e) {
                e.stopPropagation();
                if (e.keyCode == 13) {
                    $('.btn-search-filter').trigger('click');
                }
            });


            $('.select-search-employee').selectSearchEmployee();
            $('.select-search-employee-ot').selectSearchEmployee();

            $('.btn-set-time-break').click(function(e) {
                e.preventDefault();
                setTimeBreak(currentEmpId);
                $('#modal_set_time_break').modal('show');
            });
            
            $(document).on('keyup', '.time-break-value', function(e) {
                $(this).parent().find('.max_time_break-error').hide();
            });
        });
        $(function() {
            $('#project_list').on('change', function() {
                var start = $('#datetimepicker_start');
                var startTime = new Date(moment($("input[name='time_start']").val().toString(), 'DD-MM-YYYY HH:mm'));
                var dateStart = startTime.format('Y-m-d');
                var tsAfternoonOut = timeSetting[currentEmpId][dateStart]['afternoonOutSetting'];
                if (isProjAllowed($('#project_list').val(), projectAllowedOT18Key)) {
                    var idProject = $('#project_list').val();
                    var number = arrEmpPro[idProject].length;
                    var check = true;
                    for (var i = 0; i < number; i++) {
                        if (arrEmpPro[idProject][i]['start_at'] <= dateStart && dateStart <= arrEmpPro[idProject][i]['end_at']) {
                            startTime.setHours(tsAfternoonOut['hour'], tsAfternoonOut['minute']);
                            check = false;
                            break;
                        }
                    }
                    if (check) {
                        startTime.setHours(tsAfternoonOut['hour'] + 1, tsAfternoonOut['minute']);
                    }
                } else {
                    var date = moment($('#datetimepicker_start').find('input:first').val(), 'DD-MM-YYYY');
                    date = new Date(date)
                    if (!isHolidayOrWeekend(date, annualHolidayList, specialHolidayList)) {
                        startTime.setHours(tsAfternoonOut['hour'] + 1, tsAfternoonOut['minute']);
                    }
                }
                start.data('DateTimePicker').date(startTime);
            });
        });
        var viewStt = "{{ trans('ot::view.No') }}";
    </script>
@endsection
