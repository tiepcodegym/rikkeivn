$(document).on('keyup', '.time-break-value', function() {
    $(this).parent().find('.max_time_break-error').hide();
});

$('.ot-select-2').select2({
    minimumResultsForSearch: Infinity
});

$('.minimal').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});

var $employeeSelect = $('#employee_id');
var $startDateContainer = $('#datetimepicker_start');
var $endDateContainer = $('#datetimepicker_end');

$('#reason').keyup(function() {
    var reason = $('#reason').val();
    if (reason.trim() == '') {
        $('#reason-error').show();
    } else {
        $('#reason-error').hide();
    }
});

$employeeSelect.on('change', function() {
    $('#registrant-error').hide();
    var employeeId = $employeeSelect.val();
    var startDate = new Date($startDateContainer.data("DateTimePicker").date()).format('Y-m-d');
    var endDate = new Date($endDateContainer.data("DateTimePicker").date()).format('Y-m-d');
    var data = {empId : employeeId, start_at: startDate, end_at: endDate};
    if (startDate > endDate) { /* swap start at and end at */
        data.start_at = endDate;
        data.end_at = startDate;
    }
    $.ajax({
        url: urlGetProject,
        type: 'GET',
        data: data,
        dataType: 'JSON',
        success : function(result) {
            var selectobject = document.getElementById("project_list");
            selectobject.options.length = 0;
            var projects = result['projects'];
            var str = '<option value="' + keyNotProject + '"' + ">"  + notProject[keyNotProject] + "</option>";
            for (var i = 0; i < projects.length; i++) {
                str += ('<option value=' + projects[i].project_id + '>' + projects[i].projName + '</option>');
            }
            $('#project_list').append(str);
            idCurrent = employeeId;
            timeSetting[employeeId] = result['timeSetting'][employeeId];
            checkStartDate();
        },
    });
});

/**
 * check validate form
 * @returns {boolean}
 */
function checkSubmitRegister() {
    var status = 1;
    $('#check_submit').val(1);

    var startDate = $('#datetimepicker_start').data("DateTimePicker").date();
    if (startDate == null) {
        $('#start_date-error').show();
        status = 0;
    }

    var endDate = $('#datetimepicker_end').data("DateTimePicker").date();
    if (endDate == null) {
        $('#end_date-error').show();
        status = 0;
    }

    if ($('#employee_id').length) {
        var employeeId = $('#employee_id').val();
        if (employeeId == null) {
            $('#registrant-error').show();
            status = 0;
        }
    }
    if ($('#project_list').val() == "") {
        $('#project-error').show();
        status = 0;
    } else {
        $('#project-error').hide();
    }
    var reasonRegister = $('#reason').val().trim();
    if (reasonRegister == '') {
        $('#reason-error').show();
        status = 0;
    }
    if (startDate.format('DD-MM-YYYY') == endDate.format('DD-MM-YYYY')) {
        if (startDate >= endDate) {
            RKExternal.notify(mesStartLessEnd, false);
            status = 0;
        }
    } else {
        RKExternal.notify(mesSameDay, false);
        status = 0;
    }
    if (status == 0) {
        return false;
    }
    var timeBreaks = [];

    $('#has_set_time_break .set-time-break-item').each(function () {
        timeBreaks.push({
            "time_break": $(this).find('.time-break-value').val(),
            "date": $(this).find('.time-break-date .time-break-date-val').text(),
        });
    });
    $('#time_breaks').val(JSON.stringify(timeBreaks));

    return true;
}

/**
 * start date
 */
$('#datetimepicker_start').datetimepicker({
    allowInputToggle: true,
    format: 'DD-MM-YYYY HH:mm',
    sideBySide: true
}).on('dp.hide', function () {
    checkStartDate();
    showHideBreak();
}).on('dp.change', function (e) {
    if (!$startDateContainer.data("DateTimePicker").date()) {
        $startDateContainer.data('DateTimePicker').date(moment(startDateDefault, 'DD-MM-YYYY HH:mm'));
        return;
    }
    var startDate = new Date($startDateContainer.data("DateTimePicker").date()).format('Y-m-d');
    var endDate = new Date($endDateContainer.data("DateTimePicker").date()).format('Y-m-d');
    updateTimeSettingData(idCurrent, startDate, endDate);
    setTimeDefaultStart(e);
});

/**
 * check start date
 */
function checkStartDate() {
    var startDate = $('#datetimepicker_start').data("DateTimePicker").date();
    var project = $('#project_list').val();
    var startTime = new Date(startDate);
    var dateAfter = timeSetting[idCurrent][startDate.format("YYYY-MM-DD")]['afternoonOutSetting'];
    var timeOT = new Date(startDate);
    timeOT.setHours(dateAfter.hour, dateAfter.minute);
    timeOT.setTime(timeOT.getTime() + (60 * 60 * 1000));
    if (!isWeekendOrHoliday(startTime)) {
        if (projectAllowedOT18Key.indexOf(project) >= 0) {
            startTime.setHours(dateAfter.hour, dateAfter.minute);
            $('#datetimepicker_start').data('DateTimePicker').date(startTime);
        } else {
            if (startTime < timeOT) {
                startTime.setHours(timeOT.getHours(), timeOT.getMinutes());
                $('#datetimepicker_start').data('DateTimePicker').date(startTime);
            }
        }
    } else {
        var dateMor = timeSetting[idCurrent][startDate.format("YYYY-MM-DD")]['morningInSetting'];
        var timeMor = new Date(startDate);
        timeMor.setHours(dateMor.hour, dateMor.minute);
        if (startTime < timeMor) {
            $('#datetimepicker_start').data('DateTimePicker').date(timeMor);
        }
    }
}

/**
 * set time default start weekend Or holiday
 * @param e
 */
function setTimeDefaultStart(e) {
    var startDate = $('#datetimepicker_start').data("DateTimePicker").date();
    var dateMor = timeSetting[idCurrent][startDate.format("YYYY-MM-DD")]['morningInSetting'];
    var timeMor = new Date(startDate);
    var oldStartTime = new Date(startDate)
    if (e.oldDate) {
        oldStartTime = new Date(moment(e.oldDate.format('DD-MM-YYYY HH:mm').toString(), 'DD-MM-YYYY HH:mm'));
    }

    timeMor.setHours(dateMor.hour, dateMor.minute);
    if (e.oldDate &&
        e.oldDate.format('DD-MM-YYYY') !== startDate.format("DD-MM-YYYY") &&
        isWeekendOrHoliday(timeMor) && !isWeekendOrHoliday(oldStartTime)) {
        $('#datetimepicker_start').data('DateTimePicker').date(timeMor);
    }
}
/**
 *  end date
 */
$('#datetimepicker_end').datetimepicker({
    allowInputToggle: true,
    format: 'DD-MM-YYYY HH:mm',
    sideBySide: true
}).on('dp.hide', function () {
    showHideBreak();
}).on('dp.change', function(e) {
    var endDate = $('#datetimepicker_end').data("DateTimePicker").date();
    var endTime = endDate ? new Date(endDate) : moment(endDateDefault, 'DD-MM-YYYY HH:mm').toDate();

    if ((endTime.getHours() == 22 && endTime.getMinutes() > 0) ||
        endTime.getHours() > 22) {
        endTime.setHours(22, 0);
    }
    $('#datetimepicker_end').data('DateTimePicker').date(endTime);

    endDate = new Date($endDateContainer.data("DateTimePicker").date()).format('Y-m-d');
    var startDate = new Date($startDateContainer.data("DateTimePicker").date()).format('Y-m-d');
    updateTimeSettingData(idCurrent, startDate, endDate);
});

/**
 *
 */
$('#project_list').change(function () {
    if ($('#project_list').val() == "") {
        $('#project-error').show();
    } else {
        $('#project-error').hide();
    }
    checkStartDate();
})

/**
 * check date is holiday or weekend
 * @param dateTime
 * @returns {boolean}
 */
function isWeekendOrHoliday(dateTime) {
    var date = new Date(dateTime.getTime());
    date.setHours(0, 0);
    var daysOfWeek = date.getDay();
    var yearCurrent = date.getFullYear();
    var getTimeOfDate = date.getTime();
    if (daysOfWeek == 0 || daysOfWeek == 6) {
        return true;
    } else {
        if (typeof annualHolidayList == 'object') {
            var arrAnnualHolidaysLen = annualHolidayList.length;
            for (var i = 0; i < arrAnnualHolidaysLen; i++) {
                var dateAnnual = new Date(yearCurrent + '-' + annualHolidayList[i]);
                dateAnnual.setHours(0, 0);
                var getDateTimeAnnual = dateAnnual.getTime();
                if (getDateTimeAnnual == getTimeOfDate) {
                    return true;
                }
            }
        }
        if (typeof specialHolidayList == 'object') {
            var arrSpecialHolidaysLen = specialHolidayList.length;
            for (var i = 0; i < arrSpecialHolidaysLen; i++) {
                var dateSpecial = new Date(specialHolidayList[i]);
                dateSpecial.setHours(0, 0);
                var getDateTimeSpecial = dateSpecial.getTime();
                if (getDateTimeSpecial == getTimeOfDate) {
                    return true;
                }
            }
        }
    }
    return false;
}

/**
 * show or hide break
 */
function showHideBreak() {
    var startDate = moment($('#datetimepicker_start').find('input:first').val(), 'DD-MM-YYYY');
    var endDate = moment($('#datetimepicker_end').find('input:first').val(), 'DD-MM-YYYY');
    startDate = new Date(startDate);
    endDate = new Date(endDate);
    if (hasHolidayOrWeekend(startDate, endDate, annualHolidayList, specialHolidayList)) {
        $('#form_set_time_break').show();
    } else {
        $('#form_set_time_break').hide();
    }
}

function saveSetTimeBreak() {
    var totalTimeBreak = 0;
    var html = "";
    var status = 0;
    $('#box_set_time_break .time-break-value').each(function() {
        var timeBreak = parseFloat($(this).val());
        if ($(this).val() != '' && $(this).val() !== null) {
            if (timeBreak > 14) {
                status = 1;
                $(this).parent().find('.max_time_break-error').show();
            }
            if (status == 0) {
                totalTimeBreak = totalTimeBreak + parseFloat($(this).val());
            }
        }
        if (status == 0) {
            var date = $(this).closest('.set-time-break-item').find('.time-break-date').html();
            timeBreak = timeBreak.toFixed(2);
            html = html + "<div class='set-time-break-item row'>" +
                "<div class='col-md-4 ot-form-group'>" +
                "<label class='control-label time-break-date'>" + date + "</label>" +
                "</div>" +
                "<div class='col-md-8 ot-form-group'>" +
                "<input type='text' class='form-control time-break-value' value='" + timeBreak + "'>" +
                "</div>" +
                "</div>";
        }
    });
    if (status == 1) {
        return;
    }
    $('#check_set_break_time').val(1);
    $('#total_time_break').val(totalTimeBreak.toFixed(2));
    $('#has_set_time_break').html(html);
    $('#modal_set_time_break').modal('hide');
}

/**
 * event handling click set time break
 */
$('.btn-set-time-break').click(function(e) {
    e.preventDefault();
    var checkChangeDate = $('#check_change_date').val();
    var checkSetBreakTime = $('#check_set_break_time').val();
    if (checkChangeDate == 1 || (checkChangeDate == 0 && checkSetBreakTime == 0)) {
        $('#box_set_time_break').html('');
        var startDate = moment($('#datetimepicker_start').find('input:first').val(), 'DD-MM-YYYY');
        var endDate = moment($('#datetimepicker_end').find('input:first').val(), 'DD-MM-YYYY');

        if (startDate != null && endDate != null) {
            startDate = new Date(startDate);
            endDate = new Date(endDate);
            startDate.setHours(0, 0);
            endDate.setHours(0, 0);
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
                    $('#box_set_time_break').append(html);
                }
            }
        }
    } else {
        $('#box_set_time_break').html($('#has_set_time_break').html());
    }
    $('.time-break-value').limitBreakInputLength();
    $('#check_change_date').val(0);
    $('#modal_set_time_break').modal('show');
});