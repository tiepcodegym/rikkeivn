// Wait for window load
$(window).load(function() {
    // Animate loader off screen
    $(".se-pre-con").fadeOut("slow");
    var code = $('#country_id option:selected').attr('code');
    if (code === 'JP') {
        $("#add-text-required").html("");
    } else if (code === 'VN') {
        $("#add-text-required").html("<em>*</em>");
    } else {
        $("#add-text-required").html("");
    }
});

var $startDateContainer = $('#datetimepicker-start-date');
var $endDateContainer = $('#datetimepicker-end-date');

function startAction (self, elem, arrow, empId) {
    var startDate = $(elem).data("DateTimePicker").date();
    var startTime = new Date(startDate);
    var dateKey = startTime.format('Y-m-d');

    if (arrow === 'increase') {
        if (startTime.getHours() === timeSetting[empId][dateKey]['morningInSetting']['hour']) {
            startTime.setHours(timeSetting[empId][dateKey]['afternoonInSetting']['hour'], timeSetting[empId][dateKey]['afternoonInSetting']['minute']);
            $(self).css('visibility', 'hidden');
            $(elem + ' a[data-action=decrementHours]').css('visibility', 'visible');
            setTimeout(function () {
                $(elem).data('DateTimePicker').date(startTime);
            }, 0);
        }
    } else {
        if (startTime.getHours() === timeSetting[empId][dateKey]['afternoonInSetting']['hour']) {
            startTime.setHours(timeSetting[empId][dateKey]['morningInSetting']['hour'], timeSetting[empId][dateKey]['morningInSetting']['minute']);
            $(self).css('visibility', 'hidden');
            $(elem + ' a[data-action=incrementHours]').css('visibility', 'visible');
            setTimeout(function () {
                $(elem).data('DateTimePicker').date(startTime);
            }, 0);
        }
    }
}

function endAction (self, elem, arrow, empId) {
    var endDate = $(elem).data("DateTimePicker").date();
    var endTime = new Date(endDate);
    var dateKey = endTime.format('Y-m-d');
    if (arrow === 'increase') {
        if (endTime.getHours() === timeSetting[empId][dateKey]['morningOutSetting']['hour']) {
            endTime.setHours(timeSetting[empId][dateKey]['afternoonOutSetting']['hour'], timeSetting[empId][dateKey]['afternoonOutSetting']['minute']);
            $(self).css('visibility', 'hidden');
            $(elem + ' a[data-action=decrementHours]').css('visibility', 'visible');
            setTimeout(function () {
                $(elem).data('DateTimePicker').date(endTime);
            }, 0);
        }
    } else {
        if (endTime.getHours() === timeSetting[empId][dateKey]['afternoonOutSetting']['hour']) {
            endTime.setHours(timeSetting[empId][dateKey]['morningOutSetting']['hour'], timeSetting[empId][dateKey]['morningOutSetting']['minute']);
            $(self).css('visibility', 'hidden');
            $(elem + ' a[data-action=incrementHours]').css('visibility', 'visible');
            setTimeout(function () {
                $(elem).data('DateTimePicker').date(endTime);
            }, 0);
        }
    }
}

function setStartDateOnChange(startTime, dateKey, empId) {
    if (startTime.getHours() === timeSetting[empId][dateKey]['afternoonInSetting']['hour']) {
        $('#datetimepicker-start-date a[data-action=incrementHours]').css('visibility', 'hidden');
        $('#datetimepicker-start-date a[data-action=decrementHours]').css('visibility', 'visible');
        startTime.setHours(timeSetting[empId][dateKey]['afternoonInSetting']['hour'], timeSetting[empId][dateKey]['afternoonInSetting']['minute']);
        $('#datetimepicker-start-date').data('DateTimePicker').date(startTime);
    } else {
        $('#datetimepicker-start-date a[data-action=decrementHours]').css('visibility', 'hidden');
        $('#datetimepicker-start-date a[data-action=incrementHours]').css('visibility', 'visible');
        startTime.setHours(timeSetting[empId][dateKey]['morningInSetting']['hour'], timeSetting[empId][dateKey]['morningInSetting']['minute']);
        $('#datetimepicker-start-date').data('DateTimePicker').date(startTime);
    }

    var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
    var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();

    var numberDaysTimekeeping = getDaysTimekeeping(startDate, endDate, empId);
    $('#number_days_off').val(numberDaysTimekeeping);
    $('#number_validate').val(numberDaysTimekeeping);
    //Set time in table supplement together
    $('#table_supplement_employees').find('tr[id='+$('#employee_id').val()+'] .start_at').text($('#start_date').val());
    $('#table_supplement_employees').find('tr[id='+$('#employee_id').val()+'] .number_days').text(numberDaysTimekeeping);
}

function setEndDateOnChange(endTime, dateKey, empId) {
    if (endTime.getHours() === timeSetting[empId][dateKey]['morningOutSetting']['hour']) {
        $('#datetimepicker-end-date a[data-action=decrementHours]').css('visibility', 'hidden');
        $('#datetimepicker-end-date a[data-action=incrementHours]').css('visibility', 'visible');
        endTime.setHours(timeSetting[empId][dateKey]['morningOutSetting']['hour'], timeSetting[empId][dateKey]['morningOutSetting']['minute']);
        $('#datetimepicker-end-date').data('DateTimePicker').date(endTime);
    } else {
        $('#datetimepicker-end-date a[data-action=decrementHours]').css('visibility', 'visible');
        $('#datetimepicker-end-date a[data-action=incrementHours]').css('visibility', 'hidden');
        endTime.setHours(timeSetting[empId][dateKey]['afternoonOutSetting']['hour'], timeSetting[empId][dateKey]['afternoonOutSetting']['minute']);
        $('#datetimepicker-end-date').data('DateTimePicker').date(endTime);
    }

    var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
    var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
    var numberDaysTimekeeping = getDaysTimekeeping(startDate, endDate, empId);
    $('#number_days_off').val(numberDaysTimekeeping);
    $('#number_validate').val(numberDaysTimekeeping);
    //Set time in table supplement together
    $('#table_supplement_employees').find('tr[id='+$('#employee_id').val()+'] .end_at').text($('#end_date').val());
    $('#table_supplement_employees').find('tr[id='+$('#employee_id').val()+'] .number_days').text(numberDaysTimekeeping);
}

function setStartTimeSupplementOt(startTime, endTime, employeeId, dateKey, date, oldDate) {
    empProjects = Object(empProjects);
    var tsMorningIn = timeSetting[employeeId][dateKey]['morningInSetting'];
    var tsAfternoonOut = timeSetting[employeeId][dateKey]['afternoonOutSetting'];
    var check = false;
    var dateStart = getStringDate(startTime);
    var count = Object.keys(empProjects).length;
    for (var i = 0; i < count; ++i) {
        var startEmpProj = empProjects[i].start_at;
        var endEmpProj = empProjects[i].end_at;
        if (startEmpProj <= dateStart && dateStart <= endEmpProj) {
            check = true;
            break;
        }
    }
    if (oldDate) {
        if (date.getDate() !== oldDate.getDate()) { /* change date */
            if (isHolidayOrWeekend(startTime)) {
                /* set default value is working time of employee */
                startTime.setHours(tsMorningIn['hour'], tsMorningIn['minute']);
            } else {
                if (!check) {
                    /* set default value: min start time OT is (working time finish + 1h) */
                    startTime.setHours(tsAfternoonOut['hour'] + 1, tsAfternoonOut['minute']);
                } else { /* project OT 18h: min start time OT is working time finish */
                    startTime.setHours(tsAfternoonOut['hour'], tsAfternoonOut['minute']);
                }
            }
        } else {
            /* only change hour: update valid time */
            if (!isHolidayOrWeekend(startTime)) {
                /* not project OT 18h: min start time OT is (working time finish + 1h) */
                if (!check && startTime.format('H:i') < convertTime(tsAfternoonOut['hour'] + 1, tsAfternoonOut['minute'])) {
                    startTime.setHours(tsAfternoonOut['hour'] + 1, tsAfternoonOut['minute']);
                }
                /* project OT 18h: min start time OT is working time finish */
                if (check && startTime.format('H:i') < convertTime(tsAfternoonOut['hour'], tsAfternoonOut['minute'])) {
                    startTime.setHours(tsAfternoonOut['hour'], tsAfternoonOut['minute']);
                }
            }
        }
    } else {
        /* set default value */
        if (!isHolidayOrWeekend(startTime)) {
            if (!check) {
                startTime.setHours(tsAfternoonOut['hour'] + 1, tsAfternoonOut['minute']);
            } else {
                startTime.setHours(tsAfternoonOut['hour'], tsAfternoonOut['minute']);
            }
        }
    }

    $('#datetimepicker-start-date').data('DateTimePicker').date(startTime);
    var numberDaysTimekeeping = getDaysTimekeeping(startTime, endTime, employeeId);
    $('#number_days_off').val(numberDaysTimekeeping);
    //Set time in table supplement together
    $('#table_supplement_employees').find('tr[id='+$('#employee_id').val()+'] .start_at').text($('#start_date').val());
    $('#table_supplement_employees').find('tr[id='+$('#employee_id').val()+'] .number_days').text(numberDaysTimekeeping);

    // check ot one day
    checkOneDay(startTime, endTime);
}

function setEndTimeSupplementOt(startTime, endTime, employeeId, dateKey, date, oldDate) {
    if (oldDate) {
        if (date.getDate() !== oldDate.getDate()) {
            if (isHolidayOrWeekend(endTime)) {
                endTime.setHours(timeSetting[employeeId][dateKey]['afternoonOutSetting']['hour'], timeSetting[employeeId][dateKey]['afternoonOutSetting']['minute']);
            } else {
                endTime.setHours(22, 0);
            }
        }
        if (endTime.getHours() > 22)
        {
            endTime.setHours(22, 0);
        }
    } else {
        if (!isHolidayOrWeekend(endTime)) {
            endTime.setHours(22, 0);
        }
    }
    $('#datetimepicker-end-date').data('DateTimePicker').date(endTime);
    var numberDaysTimekeeping = getDaysTimekeeping(startTime, endTime, employeeId);
    $('#number_days_off').val(numberDaysTimekeeping);
    //Set time in table supplement together
    $('#table_supplement_employees').find('tr[id='+$('#employee_id').val()+'] .end_at').text($('#end_date').val());
    $('#table_supplement_employees').find('tr[id='+$('#employee_id').val()+'] .number_days').text(numberDaysTimekeeping);

    // check ot one day
    checkOneDay(startTime, endTime);
}

$(function() {
    $('.managetime-upload-file input:file').fileuploader({
        addMore: true,
        extensions : ['jpg', 'jpeg', 'png', 'bmp'],
    });

    $('.fancybox').fancybox({
        openEffect  : 'none',
        closeEffect : 'none'
    });

    $('#datetimepicker-start-date').datetimepicker({
        allowInputToggle: true,
        sideBySide: true,
//        daysOfWeekDisabled: [0, 6],
        disabledHours: [22, 23],
        defaultDate: startDateDefault
    }).on('dp.show', function(e) {
        $('#hidden-end-date').hide();
        $('#datetimepicker-end-date').show();
        var checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
            if (startDate == null) {
                $('#start_date-error').show();
                $('#end_date_before_start_date-error').hide();
            } else {
                $('#start_date-error').hide();
                $('#end_date_before_start_date-error').hide();
                $('#register_exist_error').hide();
            }
        }
        if (!$('input[name=is_ot]').is(':checked')) {
            var employeeId = currentEmpId;
            if ($('#employee_id').val() != '' && $('#employee_id').val() !== null) {
                employeeId = $('#employee_id').val();
            }
            if ($('select#employee_id').length > 0 && $('select#employee_id').val() != null) {
                employeeId = $('select#employee_id').val();
            }
            $('a[data-action="incrementMinutes"], a[data-action="decrementMinutes"]').css('display', 'none');
            $('#datetimepicker-start-date a[data-action=incrementHours]').attr('href', 'javascript:void(0)').attr('onclick', 'startAction(this, "#datetimepicker-start-date", "increase", '+ employeeId +')');
            $('#datetimepicker-start-date a[data-action=decrementHours]').attr('href', 'javascript:void(0)').attr('onclick', 'startAction(this, "#datetimepicker-start-date", "decrease", '+ employeeId +')');
            var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
            var startTime = new Date(startDate);
            var dateKey = startTime.format('Y-m-d');
            if (startTime.getHours() === timeSetting[employeeId][dateKey]['afternoonInSetting']['hour']) {
                $('#datetimepicker-start-date a[data-action=incrementHours]').css('visibility', 'hidden');
                startTime.setHours(timeSetting[employeeId][dateKey]['afternoonInSetting']['hour'], timeSetting[employeeId][dateKey]['afternoonInSetting']['minute']);
                $('#datetimepicker-start-date').data('DateTimePicker').date(startTime);
            } else {
                $('#datetimepicker-start-date a[data-action=decrementHours]').css('visibility', 'hidden');
                startTime.setHours(timeSetting[employeeId][dateKey]['morningInSetting']['hour'], timeSetting[employeeId][dateKey]['morningInSetting']['minute']);
                $('#datetimepicker-start-date').data('DateTimePicker').date(startTime);
            }
        } else {
            $('a[data-action="incrementMinutes"], a[data-action="decrementMinutes"]').css('display', 'inline-block');
        }

    }).on('dp.hide', function(e) {
        var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
        if (startDate == null) {
            $('#hidden-end-date').show();
            $('#datetimepicker-end-date').hide();
            $('#datetimepicker-end-date').data("DateTimePicker").date(null);
        }

        var checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            if (startDate == null) {
                $('#start_date-error').show();
                $('#end_date-error').show();
                $('#end_date_before_start_date-error').hide();
            } else {
                $('#start_date-error').hide();
                $('#register_exist_error').hide();
            }
        }
    }).on('dp.change', function(e) {
        var startDate = $startDateContainer.data("DateTimePicker").date();
        if (!startDate) {
            $startDateContainer.data("DateTimePicker").date(new Date());
            return;
        }
        var endDate = $endDateContainer.data("DateTimePicker").date();
        var startTime = new Date(startDate);
        var endTime = new Date(endDate);

        var employeeId = currentEmpId;
        if ($('select#employee_id').length > 0 && $('select#employee_id').val() != null) {
            employeeId = $('select#employee_id').val();
        }

        updateTimeSettingData(employeeId, startTime.format('Y-m-d'), endTime.format('Y-m-d'));
        var dateKey = startTime.format('Y-m-d');
        if (!$('input[name=is_ot]').is(':checked')) {
            setStartDateOnChange(startTime, dateKey, employeeId);
            // Check date changed
            if (e.oldDate) {
                var timeSettingItem = timeSetting[employeeId][dateKey];
                if (isMorningInTime(startTime)) {
                    startTime.setHours(timeSettingItem['morningInSetting']['hour'], timeSettingItem['morningInSetting']['minute']);
                } else {
                    startTime.setHours(timeSettingItem['afternoonInSetting']['hour'], timeSettingItem['afternoonInSetting']['minute']);
                }
                $('#datetimepicker-start-date').data('DateTimePicker').date(startTime);
            }
        } else {
            setStartTimeSupplementOt(startTime, endTime, employeeId, dateKey, new Date(e.date), new Date(e.oldDate));
        }
    });

    var dataInitCalendar = {
        allowInputToggle: true,
        sideBySide: true,
//        daysOfWeekDisabled: [0, 6],
        defaultDate: endDateDefault
    };
    if (!isEmpJp) {
        dataInitCalendar.disabledHours = [23];
    }
    $('#datetimepicker-end-date').datetimepicker(dataInitCalendar).on('dp.show', function(e) {
        var employeeId = currentEmpId;
        if ($('#employee_id').val() != '' && $('#employee_id').val() !== null) {
            employeeId = $('#employee_id').val();
        }
        if ($('select#employee_id').length > 0 && $('select#employee_id').val() != null) {
            employeeId = $('select#employee_id').val();
        }
        var checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
            if (endDate == null) {
                $('#end_date-error').show();
                $('#end_date_before_start_date-error').hide();
            } else {
                $('#end_date-error').hide();
                $('#end_date_before_start_date-error').hide();
                $('#register_exist_error').hide();
            }
        }
        if (!$('input[name=is_ot]').is(':checked')) {
            $('a[data-action="incrementMinutes"], a[data-action="decrementMinutes"]').css('display', 'none');
            $('#datetimepicker-end-date a[data-action=incrementHours]').attr('href', 'javascript:void(0)').attr('onclick', 'endAction(this, "#datetimepicker-end-date", "increase", '+ employeeId +')');
            $('#datetimepicker-end-date a[data-action=decrementHours]').attr('href', 'javascript:void(0)').attr('onclick', 'endAction(this, "#datetimepicker-end-date", "descrease", '+ employeeId +')');
            var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
            var endTime = new Date(endDate);
            var dateKey = endTime.format('Y-m-d');
            if (endTime.getHours() === timeSetting[employeeId][dateKey]['morningOutSetting']['hour']) {
                $('#datetimepicker-end-date a[data-action=decrementHours]').css('visibility', 'hidden');
                endTime.setHours(timeSetting[employeeId][dateKey]['morningOutSetting']['hour'], timeSetting[employeeId][dateKey]['morningOutSetting']['minute']);
                $('#datetimepicker-end-date').data('DateTimePicker').date(endTime);
            } else {
                $('#datetimepicker-end-date a[data-action=incrementHours]').css('visibility', 'hidden');
                endTime.setHours(timeSetting[employeeId][dateKey]['afternoonOutSetting']['hour'], timeSetting[employeeId][dateKey]['afternoonOutSetting']['minute']);
                $('#datetimepicker-end-date').data('DateTimePicker').date(endTime);
            }
        } else {
            $('a[data-action="incrementMinutes"], a[data-action="decrementMinutes"]').css('display', 'inline-block');
        }
    }).on('dp.hide', function(e) {
        var checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
            if (endDate == null) {
                $('#end_date-error').show();
            } else {
                $('#end_date-error').hide();
                $('#register_exist_error').hide();
            }
        }
    }).on('dp.change', function(e) {
        var endDate = $endDateContainer.data("DateTimePicker").date();
        if (!endDate) {
            $endDateContainer.data("DateTimePicker").date(new Date());
            return;
        }
        var startDate = $startDateContainer.data("DateTimePicker").date();
        var startTime = new Date(startDate);
        var endTime = new Date(endDate);

        if (endTime.getHours() === 22) {
            endTime.setHours(22, 0);
            $('#datetimepicker-end-date').data('DateTimePicker').date(endTime);
        }
        var employeeId = currentEmpId;
        if ($('select#employee_id').length > 0 && $('select#employee_id').val() != null) {
            employeeId = $('select#employee_id').val();
        }
        updateTimeSettingData(employeeId, startTime.format('Y-m-d'), endTime.format('Y-m-d'));
        var dateKey = endTime.format('Y-m-d');
        if (!$('input[name=is_ot]').is(':checked')) {
            setEndDateOnChange(endTime, dateKey, employeeId);
        } else {
            setEndTimeSupplementOt(startTime, endTime, employeeId, dateKey, new Date(e.date), new Date(e.oldDate));
        }
    });

    var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
    var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
    var numberDaysTimekeeping = getDaysTimekeeping(startDate, endDate, currentEmpId);
    $('#number_days_off').val(numberDaysTimekeeping);

    $('#managetime-icon-date-start').on('click', function() {
        $('.managetime-select-2').select2('close');
        $('#related_persons').select2('close');
        $('#datetimepicker-start-date').data("DateTimePicker").show();
    });

    $('#managetime-icon-date-end').on('click', function() {
        $('#related_persons').select2('close');
        $('.managetime-select-2').select2('close');
        $('#datetimepicker-start-end').data("DateTimePicker").show();
    });

    $("#related_persons").select2({
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

    $('#button_approve').click(function() {
        $('#register_id_approve').val($(this).val());
    });

    $('#button_disapprove').click(function() {
        $('#reason_disapprove-error').hide();
        $('#reason_disapprove').val('');
        $('#register_id_disapprove').val($(this).val());
    });

    $('#button_approve_submit').click(function() {
        var registerId = $('#register_id_approve').val();
        var urlCurrent = window.location.href.substr(window.location.href);
        var $this = $(this);
        $this.button('loading');

        $.ajax({
            type: "GET",
            url: urlApprove,
            data : {
                registerId: registerId,
                urlCurrent: urlCurrent
            },
            success: function (result) {
                $('#modal_approve').modal('hide');
                var data = JSON.parse(result);
                window.location = data.url;
            }
        });
    });

    $('#button_disapprove_submit').click(function() {
        var registerId = $('#register_id_disapprove').val();
        var urlCurrent = window.location.href.substr(window.location.href);
        var reasonDisapprove = $('#reason_disapprove').val();
        if (reasonDisapprove.trim() == '') {
            $('#reason_disapprove-error').show();
            return;
        }
        var $this = $(this);
        $this.button('loading');

        $.ajax({
            type: "GET",
            url: urlDisapprove,
            data : {
                registerId: registerId,
                urlCurrent: urlCurrent,
                reasonDisapprove: reasonDisapprove
            },
            success: function (result) {
                $('#modal_disapprove').modal('hide');
                var data = JSON.parse(result);
                window.location = data.url;
            }
        });
    });

    $('#reason_disapprove').keyup(function() {
        var reasonDisapprove = $('#reason_disapprove').val();
        if (reasonDisapprove.trim() == '') {
            $('#reason_disapprove-error').show();
        } else {
            $('#reason_disapprove-error').hide();
        }
    });

    $("#approver").on('change', function (evt) {
        $('#approver-error').hide();
    });
    $("#country_id").change(function () {
        $('#country_id-error').hide();
        $('#province_id-error').hide();
    });
    $("#province_id").change(function () {
        $('#province_id-error').hide();
    });
    $("#location").change(function () {
        $('#location-error').hide();
    });
});

$('input[name=is_ot]').change(function () {
    var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
    var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
    var startTime = new Date(startDate);
    var endTime = new Date(endDate);
    var startDateKey = startTime.format('Y-m-d');
    var endDateKey = endTime.format('Y-m-d');
    var employeeId = currentEmpId;
    if ($('select#employee_id').length > 0 && $('select#employee_id').val() != null) {
        employeeId = $('select#employee_id').val();
    }
    if (!$(this).is(':checked')) {
        setStartDateOnChange(startTime, startDateKey, employeeId);
        setEndDateOnChange(endTime, endDateKey, employeeId);
        $('#supplement_ot_one_day').hide();
    } else {
        setStartTimeSupplementOt(startTime, endTime, employeeId, startDateKey, startTime, null);
        setEndTimeSupplementOt(startTime, endTime, employeeId, endDateKey, endTime, null);
    }
});

function dateRange(startDate, endDate) {
    var start      = startDate.split('-');
    var end        = endDate.split('-');
    var startYear  = parseInt(start[0]);
    var endYear    = parseInt(end[0]);
    var dates      = [];

    for(var i = startYear; i <= endYear; i++) {
        var endMonth = i != endYear ? 11 : parseInt(end[1]) - 1;
        var startMon = i === startYear ? parseInt(start[1])-1 : 0;
        for(var j = startMon; j <= endMonth; j = j > 12 ? j % 12 || 11 : j+1) {
            var month = j+1;
            var displayMonth = month < 10 ? '0' + month : month;
            dates.push([i, displayMonth].join('-'));
        }
    }
    return dates;
}

function getDaysTimekeeping(startDate, endDate, empId) {
    var startDateGetDiffDay = moment(startDate).toDate();
    var endDateGetDiffDay = moment(endDate).toDate();
    var arrayDateRange = generateDatesInPeriod(startDateGetDiffDay.format('Y-m-d'), endDateGetDiffDay.format('Y-m-d'));

    var timeDiff = 0;
    for (var i = 0; i < arrayDateRange.length; i++) {
        var dateKey = arrayDateRange[i];
        var start = new Date(arrayDateRange[i]);
        var end = new Date(arrayDateRange[i]);

        if (arrayDateRange.length > 1) {
            if (i < arrayDateRange.length - 1) {
                end.setHours(timeSetting[empId][dateKey]['afternoonOutSetting']['hour'], timeSetting[empId][dateKey]['afternoonOutSetting']['minute'], 0);
            } else {
                end.setHours(endDateGetDiffDay.getHours(), endDateGetDiffDay.getMinutes());
            }
            if (i === 0) {
                start.setHours(startDateGetDiffDay.getHours(), startDateGetDiffDay.getMinutes());
            } else {
                start.setHours(timeSetting[empId][dateKey]['morningInSetting']['hour'], timeSetting[empId][dateKey]['morningInSetting']['minute'], 0);
            }
        } else {
            start.setHours(startDateGetDiffDay.getHours(), startDateGetDiffDay.getMinutes());
            end.setHours(endDateGetDiffDay.getHours(), endDateGetDiffDay.getMinutes());
        }
        if (!$('input[name=is_ot]').is(':checked')) {
            if (isHolidayOrWeekend(start)) {
                start.setHours(timeSetting[empId][dateKey]['morningInSetting']['hour'], timeSetting[empId][dateKey]['morningInSetting']['minute'], 0);
            }
            if (isHolidayOrWeekend(end)) {
                end.setHours(timeSetting[empId][dateKey]['afternoonOutSetting']['hour'], timeSetting[empId][dateKey]['afternoonOutSetting']['minute'], 0);
            }
        }
        timeDiff += getTimeDiffInRange(start, end, empId, dateKey);
    }
    var str1 = rounding(timeDiff, 1);
    var str2 = rounding(timeDiff, 2);
    if (parseFloat(str1) == parseFloat(str2)) {
        return str1;
    }
    return str2;
}

function getTimeDiffInRange(startDate, endDate, empId, dateKey) {
    var ONE_DAY = 1000 * 60 * 60 * 24;
    var s = new Date(startDate);
    var e = new Date(endDate);
    s.setHours(0, 0);
    e.setHours(0, 0);

    var numberDaysWeekend = getNumberDaysWeekend(new Date(startDate), new Date(endDate));

    var startDateTime = s.getTime();
    var endDateTime = e.getTime();

    var diffMS = endDateTime - startDateTime;
    var diffDays = Math.round((diffMS / ONE_DAY) * 100)/100;

    if (!$('input[name=is_ot]').is(':checked')) {
        diffDays = diffDays - numberDaysWeekend;
    }

    if (diffDays < 0) {
        return 0;
    }

    var eveningBreak = getEveningBreak(diffDays, empId, dateKey);
    var lunchBreak = getLunchBreak(startDate, endDate, diffDays, empId, dateKey);
    var totalHoursBreak = eveningBreak + lunchBreak;
    if (!$('input[name=is_ot]').is(':checked')) {
        totalHoursBreak += 24 * numberDaysWeekend;
    }

    var totalHoursReal = getTimeDiff(startDate, endDate) - totalHoursBreak;

    return parseFloat(rounding(totalHoursReal / getHoursWork(empId, dateKey), 2));
}

function getHoursWork(empId, dateKey) {
    var startTime = new Date();
    var endTime = new Date();
    startTime.setHours(timeSetting[empId][dateKey]['morningInSetting']['hour'], timeSetting[empId][dateKey]['morningInSetting']['minute'], 0);
    endTime.setHours(timeSetting[empId][dateKey]['afternoonOutSetting']['hour'], timeSetting[empId][dateKey]['afternoonOutSetting']['minute'], 0);
    var lunchBreak = getLunchBreak(startTime, endTime, 0, empId, dateKey);
    return getTimeDiff(startTime, endTime) - lunchBreak;
}

/**
 * Get Hours in evening break between 2 dates
 *
 * days diff between 2 dates. Example: days diff of today and tomorrow is 1.
 *
 * @returns {float}
 */
function getEveningBreak(diffDays, empId, dateKey) {
    var date = new Date();
    var nextDate = new Date();
    nextDate.setDate(date.getDate() + 1);
    date.setHours(timeSetting[empId][dateKey]['afternoonOutSetting']['hour'], timeSetting[empId][dateKey]['afternoonOutSetting']['minute'], 0);
    nextDate.setHours(timeSetting[empId][dateKey]['morningInSetting']['hour'], timeSetting[empId][dateKey]['morningInSetting']['minute'], 0);

    return getTimeDiff(date, nextDate) * diffDays;
}

/**
 * Get Hours in lunch break between 2 dates
 *
 * @param {Date} fromDate
 * @param {Date} toDate
 * @param {int} diffDays days diff between 2 dates. Example: days diff of today and tomorrow is 1.
 *
 * @returns {float}
 */
function getLunchBreak(fromDate, toDate, diffDays, empId, dateKey) {
    var startLunchdate = new Date();
    var endLunchdate = new Date();
    startLunchdate.setHours(timeSetting[empId][dateKey]['morningOutSetting']['hour'], timeSetting[empId][dateKey]['morningOutSetting']['minute'], 0);
    endLunchdate.setHours(timeSetting[empId][dateKey]['afternoonInSetting']['hour'], timeSetting[empId][dateKey]['afternoonInSetting']['minute'], 0);

    var lunchBreakTime = getTimeDiff(startLunchdate, endLunchdate);

    var countLunchBreak = diffDays;
    if (isMorningInTime(fromDate) && !isMorningOutTime(toDate)) {
        countLunchBreak++;
    } else if (!isMorningInTime(fromDate) && isMorningOutTime(toDate)) {
        countLunchBreak--;
    } else {
        // Do nothing
    }

    if (countLunchBreak < 0) {
        countLunchBreak = 0;
    }
    return lunchBreakTime * countLunchBreak;
}

/**
 * Check time input is morning or afternoon time
 * True is morning time, false is afternoon time
 *
 * @param {Date} date
 *
 * @returns {Boolean}
 */
function isMorningInTime(date) {
    var morningTime = [7, 8, 9, 10, 11, 12];
    return $.inArray(date.getHours(), morningTime) !== -1;
}

function isMorningOutTime(date) {
    var morningTime = [7, 8, 9, 10, 11, 12, 13];
    return $.inArray(date.getHours(), morningTime) !== -1;
}

/**
 * get time diff between 2 datetime
 *
 * @param {datetime} fromDate
 * @param {datetime} toDate
 * @returns {float} hour diff
 */
function getTimeDiff(fromDate, toDate) {
    fromDate = parseInt(new Date(fromDate).getTime()/1000);
    toDate = parseInt(new Date(toDate).getTime()/1000);
    return fromDate > toDate ? 0 : (toDate - fromDate)/3600;
}

/**
 * Get count days is weekend or holiday from start date to end date
 *
 * @param {Date} startDate
 * @param {Date} endDate
 *
 * @returns {int}
 */
function getNumberDaysWeekend(startDate, endDate)
{
    startDate.setHours(0, 0);
    endDate.setHours(0, 0);
    var numberDaysWeekend = 0;
    for (var date = startDate; date <= endDate; date.setDate(date.getDate() + 1)) {
        if (isHolidayOrWeekend(date)) {
            numberDaysWeekend++;
        }
    }

    return numberDaysWeekend;
}

/**
 * Check date is holiday or weekend
 *
 * @param {Date} date
 * @returns {Boolean}   true is weekend or holiday
 */
function isHolidayOrWeekend(date) {
    var dateCompare = new Date(date);
    dateCompare.setHours(0, 0);
    var yearCurrent = dateCompare.getFullYear();
    var getTimeOfDate = dateCompare.getTime();

    if (isWeekend(date)) {
        return true;
    }

    if (typeof arrAnnualHolidays == 'object') {
        var arrAnnualHolidaysLen = arrAnnualHolidays.length;
        for (var i = 0; i < arrAnnualHolidaysLen; i++) {
            var dateAnnual = new Date(yearCurrent + '-' + arrAnnualHolidays[i]);
            dateAnnual.setHours(0, 0);
            var getDateTimeAnnual = dateAnnual.getTime();
            if (getDateTimeAnnual == getTimeOfDate) {
                return true;
            }
        }
    }
    if (typeof arrSpecialHolidays == 'object') {
        var arrSpecialHolidaysLen = arrSpecialHolidays.length;
        for (var i = 0; i < arrSpecialHolidaysLen; i++) {
            var dateSpecial = new Date(arrSpecialHolidays[i]);
            dateSpecial.setHours(0, 0);
            var getDateTimeSpecial = dateSpecial.getTime();
            if (getDateTimeSpecial == getTimeOfDate) {
                return true;
            }
        }
    }

    return false;
}

function checkFormMissionRegisterByAdmin(urlCheckRegisterExist) {
    if ($('#check_status').length > 0) {
        var checkStatus = $('#check_status').val();
        if (checkStatus == STATUS_CANCEL) {
            $('#show_notification').text(notificationStatusCanceled);
            $('#modal_allow_edit').modal('show');
            return false;
        }
    }

    var status = 1;
    $('#check_submit').val(1);

    var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
    if (startDate == null) {
        $('#start_date-error').show();
        status = 0;
    }

    var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
    if (endDate == null) {
        $('#end_date-error').show();
        status = 0;
    }

    if (startDate != null && endDate != null) {
        var diffDate = $('#number_days_off').val();
        if (diffDate <= 0) {
            $('#end_date_before_start_date-error').show();
            status = 0;
        }
    }

    if ($('#employee_id').length) {
        var employeeId = $('#employee_id').val();
        if (employeeId == null) {
            $('#registrant-error').show();
            status = 0;
        }
    }

    if ($('#approver').length) {
        var approver = $('#approver').val();
        if (approver == null) {
            $('#approver-error').show();
            status = 0;
        }
    }
    if ($('#country_id').length) {
        var country = $('#country_id').val();
        if (!country) {
            $('#country_id-error').show();
            status = 0;
        }
        var code = $('#country_id option:selected').attr('code');
        if (code == VN) {
            if ($('#province_id').length) {
                var province = $('#province_id').val();
                if (!province) {
                    $('#province_id-error').show();
                    status = 0;
                }
            }
        }
    }
    if ($('#location').length) {
        var location = $('#location').val();
        if (isEmptyOrSpaces(location)) {
            $('#location-error').show();
            status = 0;
        }
    }

    var reasonRegister = $('#reason').val().trim();
    if (reasonRegister == '') {
        $('#reason-error').show();
        status = 0;
    }


    if (status == 0) {
        return false;
    }

    return true;
}

function checkFormMissionRegister(urlCheckRegisterExist) {
    if ($('#check_status').length > 0) {
        var checkStatus = $('#check_status').val();
//        if (checkStatus == STATUS_APPROVED) {
//            $('#show_notification').text(notificationStatusApproved);
//            $('#modal_allow_edit').modal('show');
//            return false;
//        }
        if (checkStatus == STATUS_CANCEL) {
            $('#show_notification').text(notificationStatusCanceled);
            $('#modal_allow_edit').modal('show');
            return false;
        }
    }

    var status = 1;
    $('#check_submit').val(1);

    var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
    if (startDate == null) {
        $('#start_date-error').show();
        status = 0;
    }

    var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
    if (endDate == null) {
        $('#end_date-error').show();
        status = 0;
    }

    if (startDate != null && endDate != null) {
        var diffDate = $('#number_days_off').val();
        if (diffDate <= 0) {
            $('#end_date_before_start_date-error').show();
            status = 0;
        } else {
            if (checkNoEmployee()) {
                status = 0;
            } else {
                if (checkExistRegister(urlCheckRegisterExist)) {
                    status = 0;
                }
            }
        }
    }

    if ($('#employee_id').length) {
        var employeeId = $('#employee_id').val();
        if (employeeId == null) {
            $('#registrant-error').show();
            status = 0;
        }
    }

    if ($('#approver').length) {
        var approver = $('#approver').val();
        if (approver == null) {
            $('#approver-error').show();
            status = 0;
        }
    }
    if ($('#country_id').length) {
        var country = $('#country_id').val();
        if (!country) {
            $('#country_id-error').show();
            status = 0;
        }
        var code = $('#country_id option:selected').attr('code');
        if (code == VN) {
            if ($('#province_id').length) {
                var province = $('#province_id').val();
                if (!province) {
                    $('#province_id-error').show();
                    status = 0;
                }
            }
        }
    }
    if ($('#location').length) {
        var location = $('#location').val();
        if (isEmptyOrSpaces(location)) {
            $('#location-error').show();
            status = 0;
        }
    }
    /*var location = $('#location').val().trim();
    if (location == '') {
        $('#location-error').show();
        status = 0;
    }*/

    var reasonRegister = $('#reason').val().trim();
    if (reasonRegister == '') {
        $('#reason-error').show();
        status = 0;
    }

    if (status == 0) {
        return false;
    }

    return true;
}

function checkFormSupplementRegister(urlCheckRegisterExist) {
    if ($('#check_status').length > 0) {
        var checkStatus = $('#check_status').val();
        if (checkStatus == STATUS_APPROVED) {
            $('#show_notification').text(notificationStatusApproved);
            $('#modal_allow_edit').modal('show');
            return false;
        }
        if (checkStatus == STATUS_CANCEL) {
            $('#show_notification').text(notificationStatusCanceled);
            $('#modal_allow_edit').modal('show');
            return false;
        }
    }

    var status = 1;
    $('#check_submit').val(1);

    var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
    if (startDate == null) {
        $('#start_date-error').show();
        status = 0;
    }

    var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
    if (endDate == null) {
        $('#end_date-error').show();
        status = 0;
    }

    if (startDate != null && endDate != null) {
        var diffDate = $('#number_days_off').val();
        if (diffDate <= 0) {
            $('#end_date_before_start_date-error').show();
            status = 0;
        } else {
            if (checkNoEmployee()) {
                status = 0;
            } else {
                if (checkExistRegister(urlCheckRegisterExist)) {
                    status = 0;
                }
            }

        }
    }

    if ($('#employee_id').length) {
        var employeeId = $('#employee_id').val();
        if (employeeId == null) {
            $('#registrant-error').show();
            status = 0;
        }
    }

    if ($('#approver').length) {
        var approver = $('#approver').val();
        if (approver == null) {
            $('#approver-error').show();
            status = 0;
        }
    }
    if ($('#country_id').length) {
        var country = $('#country_id').val();
        if (!country) {
            $('#country_id-error').show();
            status = 0;
        }
    }

    if (!isWorkingJP || $('#reason_id').find('option:selected').data('other') === typeOther) {
        var reasonRegister = $('#reason').val().trim();
        if (reasonRegister === '') {
            $('#reason-error').show();
            status = 0;
        }
    }

    if (isWorkingJP && $('#reason_id').find('option:selected').data('required') === typeImageRequired) {
        if( document.getElementById("image_upload").files.length == 0 ){
            $('#image_upload-error').show();
            status = 0;
        }
    }

    // check ot one day
    if (!isOneDay(startDate, endDate) && $('input[name=is_ot]').is(':checked')) {
        status = 0;
    }

    if (status === 0) {
        return false;
    }

    return true;
}

function checkDiffDate(startDate, endDate) {
    var startDateMS = new Date(startDate.format("MM/DD/YYYY HH:mm:ss"));
    var endDateMS = new Date(endDate.format("MM/DD/YYYY HH:mm:ss"));
    var differenceMS = endDateMS - startDateMS;
    if (differenceMS <= 0) {
        return true;
    }
    return false;
}

function checkExistRegister(urlCheckRegisterExist) {
    var isExist = false;
    var otEmpArr = [];
    //convert table data to json
    if ($('#table_supplement_employees > tbody').find('tr').length > 0) {
        $('#table_supplement_employees > tbody').find('tr').each(function () {
            var tds = $(this).find('td');
            otEmpArr.push({
                "empId": $(this).attr('id'),
                "startAt": tds.eq(2).text(),
                "endAt": tds.eq(3).text(),
            });
        });
    }

    var otEmpJson = JSON.stringify(otEmpArr);

    $.ajax({
        type: "GET",
        url: urlCheckRegisterExist,
        dataType: 'json',
        async: false,
        data: {
            empList: otEmpJson,
            registerId: $('#register_id').val(),
            isOt: $('#is_ot:checked').val(),
        },
        success: function (result) {
            $('.employee-exist').html('');
            if (result.length > 0) {
                $('.employee-exist').append('<div class="error">Các nhân viên sau đã đăng ký trùng thời gian với đơn đăng ký khác:</div>');
                isExist = true;
                $.each(result, function(index, value) {
                    $('.employee-exist').append('<div class="error emp-row" data-emp="'+value['empId']+'">Nhân viên: '+value['empName']+', mã nhân viên: '+value['empCode']+' <a target="_blank" href="'+value['url']+'">Chi tiết</a></div>');
                });
            }
        }
    });

    return isExist;
}

$(function () {
    $('#datetimepicker_start, #datetimepicker_end').initOTTimeCalendar();
    if (typeof pageType !== 'undefined' && pageType === "create") {
        //prepare form
        initEmployeeTable();
    }
    $('.select-search-employee').selectSearchEmployee();
    $('.select-search-employee-no-pagination').selectSearchEmployeeNoPagination();
    calculateNumberDays();
});

function initEmployeeTable() {
    if ($('#table_supplement_employees').find('tr#' + $('#register_id').data('id')).length == 0) {
        var regId = $('#employee_id').val();
        var html = "<tr id='" + regId + "'>";
        var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
        var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
        html += "<td class='emp_code_main'>" + $('#employee_code').val() + "</td>";
        html += "<td class='emp_name'>" + $('#employee_name').val() + "</td>";
        html += "<td class='start_at'>" + $('#start_date').val() + "</td>";
        html += "<td class='end_at'>" + $('#end_date').val() + "</td>";
        html += "<td class='number_days'>" + getDaysTimekeeping(startDate, endDate, currentEmpId) + "</td>"
        // var timeBreak = parseFloat($('#relax').val());
        html += "<td class='btn-manage'><button type='button' class='btn btn-primary edit' onclick='editEmp(" + regId + ")'><i class='fa fa-pencil-square-o'></i></button>";
        html += " <button type='button' class='btn btn-delete delete' onclick='removeEmp(" + regId + ")'><i class='fa fa-minus'></i></button></td>";
        html += "</tr>"
        $('#table_supplement_employees').children('tbody').prepend(html);
    }
}

function calculateNumberDays() {
    $('#table_supplement_employees tbody tr').each(function() {
        var tr = $(this);
        var empId = tr.attr('id');
        var startDate = convertDate(tr.find('.start_at').text());
        var endDate = convertDate(tr.find('.end_at').text());
        var numberDays = getDaysTimekeeping(startDate, endDate, empId);
        tr.find('.number_days').text(numberDays);
    });
}

/**
 * Process before submit form register
 *
 * @returns {void}
 */
function preSaveProcessing() {
    var otEmpArr = [];

    //convert table data to json
    if ($('#table_supplement_employees > tbody').find('tr').length > 0) {
        $('#table_supplement_employees > tbody').find('tr').each(function () {
            var tds = $(this).find('td');
            otEmpArr.push({
                "empId": $(this).attr('id'),
                "startAt": tds.eq(2).text(),
                "endAt": tds.eq(3).text(),
            });

        });
    }

    var otEmpJson = JSON.stringify(otEmpArr);
    $('#table_data_emps').val(otEmpJson);
}

$('#btn_add_employee_ot').click(function () {
    var btnThis = $(this);
    var employees = $('#search_employee_ot').select2('data');
    var empIds = [];
    jQuery.each(employees, function (index, value) {
        empIds.push(value['id']);
    });
    var url = $(this).data('url');
    if (!empIds.length) {
        return true;
    }
    if (btnThis.data('process')) {
        return true;
    }
    btnThis.data('process', 1);
    $.ajax({
        type: "POST",
        url: url,
        dataType: 'json',
        data : {
            empIds: empIds,
            _token : token,
            startDate: $('#start_date').val(),
            endDate: $('#end_date').val(),
        },
        success: function (result) {
            var startDate = moment(convertDate($('#start_date').val())).toDate();
            var endDate = moment(convertDate($('#end_date').val())).toDate();
            var startTime = new Date(startDate);
            var startDateKey = startTime.format('Y-m-d');
            var endTime = new Date(endDate);
            var endDateKey = endTime.format('Y-m-d');

            $.each(empIds, function(k, empId) {
                if (typeof timeSetting[empId] == "undefined") {
                    timeSetting[empId] = {};
                }
                timeSetting[empId] = result[0][empId];
            });

            jQuery.each(employees, function (index, value) {
                $('#error_no_employee').addClass('hidden');
                if ($('#table_supplement_employees > tbody > tr#' + value['id']).length <= 0) {
                    var html = "<tr id='" + value['id'] + "'>";
                    var empId = value['id'];
                    var startIsMorining = isMorningInTime(startDate);
                    var endIsMorning = isMorningOutTime(endDate);

                    if (startIsMorining) {
                        startDate.setHours(timeSetting[empId][startDateKey]['morningInSetting']['hour'], timeSetting[empId][startDateKey]['morningInSetting']['minute']);
                    } else {
                        startDate.setHours(timeSetting[empId][startDateKey]['afternoonInSetting']['hour'], timeSetting[empId][startDateKey]['afternoonInSetting']['minute']);
                    }

                    if (endIsMorning) {
                        endDate.setHours(timeSetting[empId][endDateKey]['morningOutSetting']['hour'], timeSetting[empId][endDateKey]['morningOutSetting']['minute']);
                    } else {
                        endDate.setHours(timeSetting[empId][endDateKey]['afternoonOutSetting']['hour'], timeSetting[empId][endDateKey]['afternoonOutSetting']['minute']);
                    }

                    html += "<td class='emp_code'><div class='emp_code_main'>" + value['employee_code'] + "</div></td>";
                    html += "<td class='emp_name'>" + value['employee_name'] + "</td>";
                    html += "<td class='start_at'>" + getFullDate(startDate) + "</td>";
                    html += "<td class='end_at'>" + getFullDate(endDate) + "</td>";
                    html += "<td class='number_days'></td>";
                    html += "<td class='btn-manage'><button type='button' class='btn btn-primary edit' onclick='editEmp(" + empId + ")'><i class='fa fa-pencil-square-o'></i></button>";
                    html += " <button type='button' class='btn btn-delete delete' onclick='removeEmp(" + empId + ")'><i class='fa fa-minus'></i></button></td>";
                    html += "</tr>"
                    $('#table_supplement_employees > tbody').append(html);
                }
            });
            calculateNumberDays();
            $('#search_employee_ot').html('').trigger('change');
        },
        complete: function () {
            btnThis.data('process', 0);
        },
    });

});

function getFullDate(date) {
    return get2Digis(date.getDate()) + "-" + get2Digis(date.getMonth()+1) + "-" + date.getFullYear()
        + " " + get2Digis(date.getHours()) + ":" + get2Digis(date.getMinutes());
}

/**
 * Remove a employee from register employees list
 *
 * @param {int} id: id of employee
 *
 * @returns {void}
 */
function removeEmp(id) {
    $('#exist_time_lot_before_submit_error').html('');
    $('#table_supplement_employees > tbody').children('tr#' + id).remove();

    if (checkNoEmployee()) {
        $('#error_no_employee').removeClass('hidden');
    } else {
        $('#error_no_employee').addClass('hidden');
    }
    $('.employee-exist').find('.error[data-emp=' + id + ']').remove();
    if ($('.employee-exist').find('.emp-row').length == 0) {
        $('.employee-exist').html('');
    }
}

function checkTimeNotFilled() {
    var isNotFilled = false;
    $('#table_supplement_employees > tbody').find('td.start_at, td.end_at').each(function (i, ele) {
        if (!$(this).text()) {
            isNotFilled = true;
        }
    });
    return isNotFilled;
}

function checkNoEmployee() {
    if ($('#table_supplement_employees tbody tr').length <= 0) {
        return true;
    }
    return false;
}

function setStartDateChildOnChange(startTime, empId, dateKey) {
    if (startTime.getHours() === timeSetting[empId][dateKey]['afternoonInSetting']['hour']) {
        $('#datetimepicker_add_start a[data-action=incrementHours]').css('visibility', 'hidden');
        $('#datetimepicker_add_start a[data-action=decrementHours]').css('visibility', 'visible');
        startTime.setHours(timeSetting[empId][dateKey]['afternoonInSetting']['hour'], timeSetting[empId][dateKey]['afternoonInSetting']['minute']);
        $('#datetimepicker_add_start').data('DateTimePicker').date(startTime);
    } else {
        $('#datetimepicker_add_start a[data-action=decrementHours]').css('visibility', 'hidden');
        $('#datetimepicker_add_start a[data-action=incrementHours]').css('visibility', 'visible');
        startTime.setHours(timeSetting[empId][dateKey]['morningInSetting']['hour'], timeSetting[empId][dateKey]['morningInSetting']['minute']);
        $('#datetimepicker_add_start').data('DateTimePicker').date(startTime);
    }
}

function setEndDateChildOnChange(endTime, empId, dateKey) {
    if (endTime.getHours() === timeSetting[empId][dateKey]['morningOutSetting']['hour']) {
        $('#datetimepicker_add_end a[data-action=decrementHours]').css('visibility', 'hidden');
        $('#datetimepicker_add_end a[data-action=incrementHours]').css('visibility', 'visible');
        endTime.setHours(timeSetting[empId][dateKey]['morningOutSetting']['hour'], timeSetting[empId][dateKey]['morningOutSetting']['minute']);
        $('#datetimepicker_add_end').data('DateTimePicker').date(endTime);
    } else {
        $('#datetimepicker_add_end a[data-action=decrementHours]').css('visibility', 'visible');
        $('#datetimepicker_add_end a[data-action=incrementHours]').css('visibility', 'hidden');
        endTime.setHours(timeSetting[empId][dateKey]['afternoonOutSetting']['hour'], timeSetting[empId][dateKey]['afternoonOutSetting']['minute']);
        $('#datetimepicker_add_end').data('DateTimePicker').date(endTime);
    }
}

/**
 * Open modal edit start time, end time of a employee from register employees list
 *
 * @param {int} id: id of employee
 *
 * @returns {void}
 */
function editEmp(id) {
    $('#add_emp_id').val(id);
    $('#datetimepicker_add_start').datetimepicker({
        allowInputToggle: true,
        format: 'DD-MM-YYYY HH:mm',
        sideBySide: true,
        maxDate: moment().add(10, 'y'),
        minDate: moment().subtract(10, 'y'),
        disabledHours: [23],
    }).on('dp.show', function(e) {
        if (id == $('#add_emp_id').val()) {
            $('a[data-action="incrementMinutes"], a[data-action="decrementMinutes"]').css('display', 'none');
            $('#datetimepicker_add_start a[data-action=incrementHours]').attr('href', 'javascript:void(0)').attr('onclick', 'startAction(this, "#datetimepicker_add_start", "increase", '+id+')');
            $('#datetimepicker_add_start a[data-action=decrementHours]').attr('href', 'javascript:void(0)').attr('onclick', 'startAction(this, "#datetimepicker_add_start", "descrease", '+id+')');
            var startDate = $('#datetimepicker_add_start').data("DateTimePicker").date();
            var startTime = new Date(startDate);
            var dateKey = startTime.format('Y-m-d');
            if (startTime.getHours() === timeSetting[id][dateKey]['afternoonInSetting']['hour']) {
                $('#datetimepicker_add_start a[data-action=incrementHours]').css('visibility', 'hidden');
                startTime.setHours(timeSetting[id][dateKey]['afternoonInSetting']['hour'], timeSetting[id][dateKey]['afternoonInSetting']['minute']);
                $('#datetimepicker_add_start').data('DateTimePicker').date(startTime);
            } else {
                $('#datetimepicker_add_start a[data-action=decrementHours]').css('visibility', 'hidden');
                startTime.setHours(timeSetting[id][dateKey]['morningInSetting']['hour'], timeSetting[id][dateKey]['morningInSetting']['minute']);
                $('#datetimepicker_add_start').data('DateTimePicker').date(startTime);
            }
        }
    }).on('dp.change', function(e) {
        if (id == $('#add_emp_id').val()) {
            var startDate = $('#datetimepicker_add_start').data("DateTimePicker").date();
            var startTime = new Date(startDate);
            var endTime = new Date($('#datetimepicker_add_end').data("DateTimePicker").date());
            if (startTime.getFullYear() === 1970) {
                startDate = $('#table_supplement_employees > tbody').children('tr#' + id).children('.start_at').html();
                startTime = new Date(convertDate(startDate))
            }

            updateTimeSettingData(id, startTime.format('Y-m-d'), endTime.format('Y-m-d'));
            setStartDateChildOnChange(startTime, id, startTime.format('Y-m-d'));
        }
    });
    $('#datetimepicker_add_end').datetimepicker({
        allowInputToggle: true,
        format: 'DD-MM-YYYY HH:mm',
        sideBySide: true,
        maxDate: moment().add(10, 'y'),
        minDate: moment().subtract(10, 'y'),
        disabledHours: [23],
    }).on('dp.show', function(e) {
        if (id == $('#add_emp_id').val()) {
            $('a[data-action="incrementMinutes"], a[data-action="decrementMinutes"]').css('display', 'none');
            $('#datetimepicker_add_end a[data-action=incrementHours]').attr('href', 'javascript:void(0)').attr('onclick', 'endAction(this, "#datetimepicker_add_end", "increase", '+id+')');
            $('#datetimepicker_add_end a[data-action=decrementHours]').attr('href', 'javascript:void(0)').attr('onclick', 'endAction(this, "#datetimepicker_add_end", "descrease", '+id+')');
            var endDate = $('#datetimepicker_add_end').data("DateTimePicker").date();
            var endTime = new Date(endDate);
            var dateKey = endTime.format('Y-m-d');
            if (endTime.getHours() === timeSetting[id][dateKey]['morningOutSetting']['hour']) {
                $('#datetimepicker_add_end a[data-action=decrementHours]').css('visibility', 'hidden');
                endTime.setHours(timeSetting[id][dateKey]['morningOutSetting']['hour'], timeSetting[id][dateKey]['morningOutSetting']['minute']);
                $('#datetimepicker_add_end').data('DateTimePicker').date(endTime);
            } else {
                $('#datetimepicker_add_end a[data-action=incrementHours]').css('visibility', 'hidden');
                endTime.setHours(timeSetting[id][dateKey]['afternoonOutSetting']['hour'], timeSetting[id][dateKey]['afternoonOutSetting']['minute']);
                $('#datetimepicker_add_end').data('DateTimePicker').date(endTime);
            }
        }
    }).on('dp.change', function(e) {
        var startTime = new Date($('#datetimepicker_add_start').data("DateTimePicker").date());
        var endDate = $('#datetimepicker_add_end').data("DateTimePicker").date();
        var endTime = new Date(endDate);
        if (endTime.getFullYear() === 1970) {
            endDate = $('#table_supplement_employees > tbody').children('tr#' + id).children('.end_at').html();
            endTime = new Date(convertDate(endDate))
        }
        if (endTime.getHours() == 22) {
            endTime.setHours(22, 0);
            $('#datetimepicker_add_end').data('DateTimePicker').date(endTime);
        }
        if (id == $('#add_emp_id').val()) {
            updateTimeSettingData(id, startTime.format('Y-m-d'), endTime.format('Y-m-d'));
            setEndDateChildOnChange(endTime, id, endTime.format('Y-m-d'));
        }
    });

    $('#exist_time_lot_before_submit_error').html('');
    //$('#addEmp').validate().resetForm();
    $('#addEmp').find('.errorTxt').addClass('hidden');
    var empRow = $('#table_supplement_employees > tbody').children('tr#' + id);
    //$('#datetimepicker_add_start').data('DateTimePicker').clear();
    //$('#datetimepicker_add_end').data('DateTimePicker').clear();
    $('#addEmp').modal('show');

    $('.btn-confirm').data('id', id);
    $('#add_register_code').data('id', empRow.attr('id'));
    $('#add_register_code').val(empRow.find('.emp_code_main').text());
    $('#add_time_start').val(empRow.children('.start_at').html());
    $('#add_time_end').val(empRow.children('.end_at').html());

    $('#add_relax').val(empRow.children('.relax').html());
    if ($('#emp_list').find('option[value=' + id + ']').length > 0) {
        $('#emp_list').val(id).trigger('change');
    } else {
        $('#emp_list').append($('<option>', {value: id, text: empRow.children('.emp_name').html()}));
        $('#emp_list').val(id).trigger('change');
    }
}

$.fn.initOTTimeCalendar = function () {
    $(this).datetimepicker({
        allowInputToggle: true,
        format: 'DD-MM-YYYY HH:mm',
        sideBySide: true,
        maxDate: moment().add(10, 'y'),
        minDate: moment().subtract(10, 'y'),
        disabledHours: [23],
    }).on('dp.show', function() {
        $(this).data("DateTimePicker").disabledTimeIntervals([
            [moment().hour(22).minutes(0), moment().hour(23).minutes(59)],
        ]);
    }).on('dp.change', function() {
        var date = $(this).data("DateTimePicker").date();
        var time = new Date(date);
        if (time.getHours() === 22) {
            time.setHours(22, 0);
            $(this).data('DateTimePicker').date(time);
        }
    });
}

/**
 * Convert date from d-m-Y to Y-m-d
 *
 * @param {string} date
 *
 * @returns {String}
 */
function convertDate(date) {
    var arrayDateTime = date.split(' ');
    var date = arrayDateTime[0];
    var time = arrayDateTime[1];
    var arrayDate = date.split('-');
    return arrayDate[2] + '-' + arrayDate[1] + '-' + arrayDate[0] + ' ' + time;
}

/**
 * save after edit start time, end time of a employee event
 *
 * @return {void}
 */
$('#addEmp').find('.btn-confirm').on('click', function () {
    var timeStart = $('#add_time_start').val();
    var timeEnd = $('#add_time_end').val();
    var startDate = moment(convertDate(timeStart)).toDate();
    var endDate = moment(convertDate(timeEnd)).toDate();

    if (startDate.getTime() > endDate.getTime()) {
        $('.add_time_start-compare-error').removeClass('hidden');
        return false;
    }

    var empId = $(this).data('id');
    var trEmp = $('#table_supplement_employees').find('tr[id=' + empId + ']');

    trEmp.find('.start_at').text(timeStart);
    trEmp.find('.end_at').text(timeEnd);
    $('#addEmp').modal('hide');
    calculateNumberDays();
    if (parseInt(empId) === parseInt(currentEmpId)) {
        $('#datetimepicker-start-date').data('DateTimePicker').date(startDate);
        $('#datetimepicker-end-date').data('DateTimePicker').date(endDate);
    }
});

function checkTimeNotFilled() {
    var isNotFilled = false;
    if (!$('#addEmp').find('#add_time_end').val()) {
        $('.add_time_end-error').removeClass('hidden');
        isNotFilled = true;
    }
    return isNotFilled;
}

/**
 * Submit form register
 *
 * @param {type} param
 */
$('.managetime-form-register').submit(function (e) {
    preSaveProcessing();
});

$('#reason_id').on('change', function() {
    var elem =$(this);
    var areaReason = $('#reason');
    if (elem.find(':selected').data('other') === typeOther) {
        areaReason.removeClass('hidden');
    } else {
        areaReason.addClass('hidden');
    }

    var errorImageUpload = $('#image_upload-error');
    if (elem.find(':selected').data('required') !== typeImageRequired) {
        errorImageUpload.hide();
    }
});

$(function(){
    $("select#country_id").change(function(){
        var code = $('#country_id option:selected').attr('code');
        if (code === 'JP') {
            $("#add-text-required").html("");
        } else if (code === 'VN') {
            $("#add-text-required").html("<em>*</em>");
        } else {
            $("#add-text-required").html("");
        }
    });
});

/**
 * Submit form register check null and space
 *
 * @param {str} param
 */
function isEmptyOrSpaces(str){
    return str === null || str.match(/^ *$/) !== null;
}

function getStringDate(time) {
    var month = time.getMonth() + 1;
    month = month < 10 ? ('0' + month) : month;
    return time.getFullYear() + '-' + month + '-' + time.getDate();
}

function isOneDay(startDate, endDate)
{
    var dateStart = getStringDate(new Date(startDate));
    var dateEnd = getStringDate(new Date(endDate));
    if (dateStart === dateEnd) {
        return true;
    }
    return false;
}

function checkOneDay(startDate, endDate)
{
    if (!isOneDay(startDate, endDate) && $('input[name=is_ot]').is(':checked')) {
        $('#supplement_ot_one_day').show();
        $('#number_days_off').val(0);
        $('#number_validate').val(0);
    } else {
        $('#supplement_ot_one_day').hide();
        var empId = $('#employee_id').val();
        if (empId) {
            var numberDaysTimekeeping = getDaysTimekeeping(startDate, endDate, empId);
            $('#number_days_off').val(numberDaysTimekeeping);
            $('#number_validate').val(numberDaysTimekeeping);
        }
    }
}

$('body').on('click', '#checkbox-serach-employee-type', function(e) {
    if ($(this).is(":checked")) {
        $('#search_employee_ot').attr('data-type', 1); //1 search cả nhân viên đã nghỉ
        $('#form-register #employee_id').attr('data-type', 1);
    } else {
        $('#search_employee_ot').attr('data-type', 0);
        $('#form-register #employee_id').attr('data-type', 0);
    }
    $('.select-search-employee').selectSearchEmployee();
});

/**
 * convert to time format HH:ii
 * @param {number} hour
 * @param {number} minute
 * @return {string}
 */
function convertTime(hour, minute) {
    return ('0' + hour).slice(-2) + ':' + ('0' + minute).slice(-2);
}

/**
 * update data time setting of employee
 * @param {number} employeeId
 * @param {string} startDate - format 'YYYY-mm-dd'
 * @param {string} endDate - format 'YYYY-mm-dd'
 * @return void
 */
function updateTimeSettingData(employeeId, startDate, endDate) {
    if (startDate > endDate) {
        var tempDate = startDate;
        startDate = endDate;
        endDate = tempDate;
    }
    var dateArray = generateDatesInPeriod(startDate, endDate);
    /* check data timeSetting is filled full days between startDate and endDate */
    var i, isFillFull = true;
    /* narrow the start time */
    for (i = 0; i < dateArray.length; i++) {
        if (!timeSetting[employeeId][dateArray[i]]) {
            isFillFull = false;
            startDate = dateArray[i];
            break;
        }
    }

    if (!isFillFull) {
        /* narrow the end time */
        for (i = dateArray.length - 1; i >= 0; i--) {
            if (!timeSetting[employeeId][dateArray[i]]) {
                endDate = dateArray[i];
                break;
            }
        }
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: urlGetTimeSetting,
            async : false,
            data : {
                period: {start_date: startDate, end_date: endDate},
                empId: employeeId,
                _token: token,
            },
            success: function (response) {
                var empWorkingTimes = response['timeWorking'][employeeId];
                Object.keys(empWorkingTimes).forEach(function (date) {
                    timeSetting[employeeId][date] = empWorkingTimes[date];
                });
            }
        });
    }
}
