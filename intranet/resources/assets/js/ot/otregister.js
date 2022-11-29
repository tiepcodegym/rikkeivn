// Wait for window load
$(window).load(function() {
    // Animate loader off screen
    $(".se-pre-con").fadeOut("slow");
});

var $timeStart = $('#time_start');
var $timeEnd = $('#time_end');
var $tableEmployeeOT = $('#table_ot_employees');
var $projectSelect = $('#project_list');
var $startContainers = {mainForm: $('#datetimepicker_start'), popupForm: $('#datetimepicker_add_start')};
var $endContainers = {mainForm: $('#datetimepicker_end'), popupForm: $('#datetimepicker_add_end')};

var dataInitCalendar = {
    allowInputToggle: true,
    format: 'DD-MM-YYYY HH:mm',
    sideBySide: true,
    maxDate: moment().add(10, 'y'),
    minDate: moment().subtract(10, 'y'),
};
if (!isEmpJp) {
    dataInitCalendar.disabledHours = [23];
}
//init ot time calendar
$.fn.initOTTimeCalendar = function () {
    $(this).datetimepicker(dataInitCalendar);
}
//include new OT Employee
function initAddOtEmployee() {
    var regId = $("#form_id").val();

    $('#emp_list').select2({
        minimumInputLength: 1,
        containerCssClass: "show-hide",
        ajax: {
            url: urlSearchEmp,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    queryStr: $.trim(params.term),
                    registerId: $("#register_id").data("id")
                };
            },
            processResults: function (data) {
                return {
                    results: data,
                };
            },
        },
    });

    //when employee change
    $('#emp_list').on('select2:close', function () {
        //set employee's code
        var selectedEmp = $('#emp_list').select2('data');
        if (selectedEmp[0]) {
            $('#add_register_code').prop('data-id', selectedEmp[0].id);
            $('#add_register_code').val(selectedEmp[0].code);

            $('#add_time_start').val('');
            $('#add_time_end').val('');
            $('#add_time_start').prop('disabled', false);

            //auto validate time slot
            if ($('#add_time_start').val()) {
                validateTimeSlot($('#add_time_start'), regId, selectedEmp[0].id, $('#add_time_start').val());
            }
            if ($('#add_time_end').val()) {
                validateTimeSlot($('#add_time_end'), regId, selectedEmp[0].id, $('#add_time_end').val());
            }
        }
    });

    $('#addEmp').find('.btn-confirm').on('click', function () {
        addOtEmployee($('#add_register_code').attr('data-id'));
        if (checkTimeNotFilled()) {
            $('#error_time_not_filled').show();
        } else {
            $('#error_time_not_filled').hide();
        }
    });
}

//validate start, end and break time
function validateTime() {
    controlOTStartTime(true);
    controlOTStartTime(false);
    controlOTEndTime(true);
    controlOTEndTime(false);
}

function setStartDateOnChange(startTime, dateKey, empId, start, oldStartTime) {
    //if change date then reset time
    var timeSettingItem = timeSetting[empId][dateKey];
    var tsAfternoonOut = timeSettingItem['afternoonOutSetting'];
    var tsMorningIn = timeSettingItem['morningInSetting'];
    /* change date: set start time OT default and disable invalid time */
    if (oldStartTime === undefined || startTime.format('Y-m-d') !== oldStartTime.format('Y-m-d')) {
        setTimeStart(startTime, start, timeSettingItem);
    } else { /* only change hours */
        if (isHoliday(startTime) || isWeekend(startTime) || teamCodePreOfEmp === 'japan') {
            /* holiday or weekend: min start time OT is working time start */
            if (startTime.format('H:i') < convertTime(tsMorningIn['hour'], tsMorningIn['minute'])) {
                startTime.setHours(tsMorningIn['hour'], tsMorningIn['minute']);
            }
        } else {
            if (isProjAllowed($projectSelect.val(), projectAllowedOT18Key)) {
                /* project OT 18h: min start time OT is working time finish */
                if (startTime.format('H:i') < convertTime(tsAfternoonOut['hour'], tsAfternoonOut['minute'])) {
                    startTime.setHours(tsAfternoonOut['hour'], tsAfternoonOut['minute']);
                }
            } else {
                /* min start time OT is working time finish + 1 hour */
                if (startTime.format('H:i') < convertTime(tsAfternoonOut['hour'] + 1, tsAfternoonOut['minute'])) {
                    startTime.setHours(tsAfternoonOut['hour'] + 1, tsAfternoonOut['minute']);
                }
            }
        }
    }
    start.data('DateTimePicker').date(startTime);
}

/**
 * update valid end time OT when change
 * @param {Date} endTime
 * @param {jQuery} end
 */
function setEndDateOnChange(endTime, end) {
    if (endTime.format('H:i') >= '22:00') {
        endTime.setHours(22, 0);
    }
    end.data('DateTimePicker').date(endTime);
}

/**
 * Set time start by date and time setting of employee
 *
 * @param {Date} startTime
 * @param {jQuery} start
 * @param {object} timeSettingItem
 * @returns {void}
 */
function setTimeStart(startTime, start, timeSettingItem) {
    var disabledTimeIntervals = [];
    var startDate = new Date(start.data("DateTimePicker").date());
    var tsMorningIn = timeSettingItem['morningInSetting'];
    var tsAfternoonOut = timeSettingItem['afternoonOutSetting'];

    if (isHoliday(startTime) || isWeekend(startTime)) {
        startTime.setHours(tsMorningIn['hour'], tsMorningIn['minute']);
        disabledTimeIntervals.push([startDate.setHours(0, 0), startDate.setHours(tsMorningIn['hour'] - 1, tsMorningIn['minute'])]);
    } else {
        if (isProjAllowed($projectSelect.val(), projectAllowedOT18Key)) {
            startTime.setHours(tsAfternoonOut['hour'], tsAfternoonOut['minute']);
            disabledTimeIntervals.push([startDate.setHours(0, 0), startDate.setHours(tsAfternoonOut['hour'] - 1, tsAfternoonOut['minute'])]);
        } else {
            startTime.setHours(tsAfternoonOut['hour'] + 1, tsAfternoonOut['minute']);
            disabledTimeIntervals.push([startDate.setHours(0, 0), startDate.setHours(tsAfternoonOut['hour'], tsAfternoonOut['minute'])]);
        }
    }
    if (!isEmpJp) {
        disabledTimeIntervals.push([startDate.setHours(22, 0), startDate.setHours(23, 59)]);
    }
    start.data("DateTimePicker").disabledTimeIntervals(disabledTimeIntervals);
    start.data("DateTimePicker").disabledHours([0]);
}

//control start time selection
function controlOTStartTime(mainForm) {
    var formKey = mainForm ? 'mainForm' : 'popupForm';
    var start = $startContainers[formKey];
    var end = $endContainers[formKey];
    start.on('dp.show', function (e) {
        var startTime = new Date(start.data("DateTimePicker").date());
        var employeeId = getEmployeeId(mainForm);
        var timeSettingItem = timeSetting[employeeId][startTime.format('Y-m-d')];
        setTimeStart(startTime, start, timeSettingItem);
    });
    start.on('dp.change', function (e) {
        if (!start.data("DateTimePicker").date()) {
            start.data("DateTimePicker").date(new Date());
        }
        var oldStartTime;
        if (e.oldDate) {
            oldStartTime = new Date(moment(e.oldDate.format('DD-MM-YYYY HH:mm').toString(), 'DD-MM-YYYY HH:mm'));
        }
        if (e.date) {
            var startTime = new Date(start.data("DateTimePicker").date());
            var endTime = new Date(end.data("DateTimePicker").date());
            var employeeId = getEmployeeId(mainForm);
            var valid = start.children("input").valid();

            updateTimeSettingData(employeeId, startTime.format('Y-m-d'), endTime.format('Y-m-d'));
            setStartDateOnChange(startTime, startTime.format('Y-m-d'), employeeId, start, oldStartTime);
            changeEvent(mainForm);

            start.siblings(".errorTxt").empty();
            //auto validate time slot
            if (valid && mainForm) {
                employeeId = $("#register_id").data("id");
                $tableEmployeeOT.find("tr#" + employeeId + ' td.start_at').text($timeStart.val());
                $tableEmployeeOT.find("tr#" + employeeId + ' td.end_at').text($timeEnd.val());
            }
        }
        if (end.children('input').val()) {
            end.children("input").valid();
        }
    });
    start.find('input').on('keyup mouseup', function () {
        if (start.find('input').val() == '') {
            start.siblings(".errorTxt").empty();
        }
    });
}

//control end time selection
function controlOTEndTime(mainForm) {
    var formKey = mainForm ? 'mainForm' : 'popupForm';
    var start = $startContainers[formKey];
    var end = $endContainers[formKey];
    end.on('dp.change', function (e) {
        if (!end.data("DateTimePicker").date()) {
            var endDate = new Date();
            endDate.setHours(22, 0);
            end.data("DateTimePicker").date(endDate);
        }
        if (e.date) {
            var startTime = new Date(start.data("DateTimePicker").date());
            var endTime = new Date(end.data("DateTimePicker").date());
            var employeeId = getEmployeeId(mainForm);
            var valid = end.children("input").valid();

            updateTimeSettingData(employeeId, startTime.format('Y-m-d'), endTime.format('Y-m-d'));
            setEndDateOnChange(endTime, end);
            changeEvent(mainForm);

            //auto validate time slot
            if (valid && mainForm) {
                employeeId = $("#register_id").data("id");
                $tableEmployeeOT.find("tr#" + employeeId + ' td.start_at').text($timeStart.val());
                $tableEmployeeOT.find("tr#" + employeeId + ' td.end_at').text($timeEnd.val());
            }
        }
        if (start.children('input').val()) {
            start.children("input").valid();
        }
    });
    end.find('input').on('keyup mouseup', function () {
        if (end.find('input').val() == '') {
            end.siblings(".errorTxt").empty();
        }
    });
}

/**
 * get employee id of current form
 */
function getEmployeeId(mainForm) {
    var employeeId = currentEmpId;
    if ($('select#employee_id').length > 0 && $('select#employee_id').val() != null) {
        employeeId = $('select#employee_id').val();
    }
    if (!mainForm) {
        employeeId = $('#add_register_code').data('id');
    }
    return employeeId;
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

function changeEvent(mainForm) {
    if (mainForm) {
        var start = '#datetimepicker_start';
        var end = '#datetimepicker_end';
    } else {
        var start = '#datetimepicker_add_start';
        var end = '#datetimepicker_add_end';
    }

    var startDate = moment($(start).find('input:first').val(), 'DD-MM-YYYY');
    var endDate = moment($(end).find('input:first').val(), 'DD-MM-YYYY');
    var startDate2 = new Date(moment($(start).find('input:first').val(), 'DD-MM-YYYY HH:mm'));
    var endDate2 = new Date(moment($(end).find('input:first').val(), 'DD-MM-YYYY HH:mm'));

    if (mainForm) {
        $('#check_change_date').val(1);
        $('#check_set_break_time').val(0);
        //$('#box_set_time_break').empty();
        $('#has_set_time_break').empty();

        if (startDate != null && endDate != null) {
            startDate = new Date(startDate);
            endDate = new Date(endDate);
            startDate.setHours(0, 0);
            endDate.setHours(0, 0);

            $("#table_ot_employees").find("tr#" + $("#register_id").data("id")).find("td.emp_code .has-set-time-break-edit").html('');
            if (hasHolidayOrWeekend(startDate, endDate, annualHolidayList, specialHolidayList)) {
                $('#form_set_time_break').show();
            } else {
                $('#form_set_time_break').hide();
            }
            var totalBreak = 0;
            for (var date = startDate; date <= endDate; date.setDate(date.getDate() + 1)) {
                var holidayOrWeekend = isHolidayOrWeekend(date, annualHolidayList, specialHolidayList);
                if (holidayOrWeekend) {
                    var daysOfWeek = getDayOfWeek(date.getDay());
                    var strDate = get2Digis(date.getDate()) + '/' + get2Digis(date.getMonth() + 1) + '/' + date.getFullYear();

                    $('#duplicate_set_time_break_item .time-break-date .time-break-date-val').attr('data-date', strDate).text(strDate);
                    $('#duplicate_set_time_break_item .time-break-date .time-break-date-day').text(' (' + daysOfWeek + ')');
                    $('#duplicate_set_time_break_item .time-break-value').attr('data-date', strDate);
                    var html = $('#duplicate_set_time_break_item').html();
                    $('#has_set_time_break').append(html);
                    //$('#has_set_time_break .time-break-value[data-date="'+strDate+'"]').val(oldValue);
                    $("#table_ot_employees").find("tr#" + $("#register_id").data("id")).find("td.emp_code .has-set-time-break-edit").append(html);

//                    if (timeBreakOrigin[strDate] !== undefined && timeBreakOrigin[strDate] != 0) {
//                        totalBreak += parseFloat(timeBreakOrigin[strDate]);
//                        $("#table_ot_employees").find("tr#" + $("#register_id").data("id")).find('td.emp_code .has-set-time-break-edit .time-break-value[data-date="'+strDate+'"]').val(timeBreakOrigin[strDate]);
//                    } else {
                    //Set default time break
                    var empId = currentEmpId;
                    if ($('select#employee_id').length > 0 && $('select#employee_id').val() != null) {
                        empId = $('select#employee_id').val();
                    }

                    var lunchBreak = setLunchBreak(empId, date, startDate2, endDate2, strDate);
                    totalBreak += lunchBreak;
                    if (timeBreakOrigin[empId] === undefined) {
                        timeBreakOrigin[empId] = {};
                    }
                    timeBreakOrigin[empId][strDate] = lunchBreak;
                    setTimeBreak(empId);
                    saveSetTimeBreak();
//                    }
                }
            }
            $('#total_time_break').val(rounding(totalBreak, 2));
            $("#table_ot_employees").find("tr#" + $("#register_id").data("id")).find('.relax').text(rounding(totalBreak, 2));

        }
    } else {
        if (startDate != null && endDate != null) {
            startDate = new Date(startDate);
            endDate = new Date(endDate);
            startDate.setHours(0, 0);
            endDate.setHours(0, 0);
            $('#box_set_time_break_edit').html('');
            for (var date = startDate; date <= endDate; date.setDate(date.getDate() + 1)) {
                var holidayOrWeekend = isHolidayOrWeekend(date, annualHolidayList, specialHolidayList);
                if (holidayOrWeekend) {
                    var daysOfWeek = getDayOfWeek(date.getDay());
                    var strDate = get2Digis(date.getDate()) + '/' + get2Digis(date.getMonth() + 1) + '/' + date.getFullYear();
                    $('#duplicate_set_time_break_item .time-break-date .time-break-date-val').attr('data-date', strDate).text(strDate);
                    $('#duplicate_set_time_break_item .time-break-date .time-break-date-day').text(' (' + daysOfWeek + ')');
                    $('#duplicate_set_time_break_item .time-break-value').attr('data-date', strDate);
                    var html = $('#duplicate_set_time_break_item').html();
                    $('#box_set_time_break_edit').append(html);

//                    if (breakTimesEmployees[$('#add_register_code').data('id')][strDate] != undefined) {
//                        var oldValue = breakTimesEmployees[$('#add_register_code').data('id')][strDate];
//                        $('#box_set_time_break_edit .time-break-value[data-date="'+strDate+'"]').val(oldValue);
//                    } else {
                    var empId = $('#add_register_code').attr('data-id');
                    totalBreak += setLunchBreak(empId, date, startDate2, endDate2, strDate);

                    var oldValue = breakTimesEmployees[empId][strDate];
                    $('#box_set_time_break_edit .time-break-value[data-date="'+strDate+'"]').val(oldValue);
//                    }
                }
            }
        }
    }
}

/**
 * get lunch break of employee from 2 date
 *
 * @param {int} empId
 * @param {Date} date
 * @param {Date} startDate2 from date
 * @param {Date} endDate2 to date
 * @param {string} strDate
 * @returns {float}
 */
function setLunchBreak(empId, date, startDate2, endDate2, strDate) {
    var dateKey = date.format('Y-m-d');
    var startTime = new Date(dateKey);
    var endTime = new Date(dateKey);
    var startDate2Str = startDate2.format('Y-m-d');
    var dateStr = date.format('Y-m-d');
    var endDate2Str = endDate2.format('Y-m-d');
    var timeSettingItem = timeSetting[empId][dateKey];

    if (startDate2Str < dateStr && dateStr < endDate2Str) {
        startTime.setHours(timeSettingItem['morningInSetting']['hour'], timeSettingItem['morningInSetting']['minute'], 0);
        endTime.setHours(timeSettingItem['afternoonOutSetting']['hour'], timeSettingItem['afternoonOutSetting']['minute'], 0);
    } else if (startDate2Str < dateStr && dateStr === endDate2Str) {
        startTime.setHours(timeSettingItem['morningInSetting']['hour'], timeSettingItem['morningInSetting']['minute'], 0);
        if (isMorningOutTime(endDate2)) {
            endTime.setHours(timeSettingItem['morningOutSetting']['hour'], timeSettingItem['morningOutSetting']['minute'], 0);
        } else {
            endTime.setHours(timeSettingItem['afternoonOutSetting']['hour'], timeSettingItem['afternoonOutSetting']['minute'], 0);
        }
    } else if (startDate2Str === dateStr && dateStr < endDate2Str) {
        endTime.setHours(timeSettingItem['afternoonOutSetting']['hour'], timeSettingItem['afternoonOutSetting']['minute'], 0);
        if (isMorningInTime(startDate2)) {
            startTime.setHours(timeSettingItem['morningInSetting']['hour'], timeSettingItem['morningInSetting']['minute'], 0);
        } else {
            startTime.setHours(timeSettingItem['afternoonInSetting']['hour'], timeSettingItem['afternoonInSetting']['minute'], 0);
        }
    } else {
        startTime.setHours(startDate2.getHours(), startDate2.getMinutes());
        endTime.setHours(endDate2.getHours(), endDate2.getMinutes());
    }
    var lunchBreak = getLunchBreak(startTime, endTime, 0, empId, dateKey);

    if (breakTimesEmployees[empId] === undefined) {
        breakTimesEmployees[empId] = {};
    }
    breakTimesEmployees[empId][strDate] = lunchBreak;

    return lunchBreak;
}

$(document).on('blur', '#addEmp .time-break-value', function() {
    var date = $(this).data('date');
    var empId = $('#add_register_code').data('id');
    breakTimesEmployees[empId][date] = this.value;
});

//validate time slot
function validateTimeSlot(element, regId, empId, time) {
    $.ajax({
        url: urlcheckOccupiedTimeSlot,
        type: 'GET',
        data: {"id": empId, "time": time, "regId": regId},
        success: function (result) {
            if (result != 0) {
                element.siblings(".errorTxt").html(errList[4]);
            } else {
                element.siblings(".errorTxt").empty();
            }
        }
    });
}

//validate time slot
function validateTimeSlotExist(elementFirst, elementSecond, registerId, employeeId, startDate, endDate) {
    $.ajax({
        url: urlAjaxCheckOccupiedTimeSlot,
        type: 'GET',
        data: {"employeeId": employeeId, "startDate": startDate, "endDate": endDate, "registerId": registerId},
        success: function (result) {
            if (result) {
                elementFirst.siblings(".errorTxt").html(errList[4]);
            } else {
                elementFirst.siblings(".errorTxt").empty();
                elementSecond.siblings(".errorTxt").empty();
            }
        },
    });
}

function checkExistTimeSlotBeforeSubmit() {
    var hasError = false;
    var otEmpArr = [];
    if ($('#table_ot_employees > tbody').find('tr').length > 0) {
        $('#table_ot_employees > tbody').find('tr').each(function (i, ele) {
            var tds = $(this).find('td')
            otEmpArr.push({
                "empId": $(this).attr('id'),
                "empCode": tds.closest('.emp_code').children('.emp_code_main').text().trim(),
                "empName": tds.closest('.emp_name').text(),
                "startAt": tds.closest('.start_at').text(),
                "endAt": tds.closest('.end_at').text(),
                "isPaid": tds.find('input').is(':checked'),
                "break": tds.closest('.relax').text()
            });
        });
    }
    var otEmpJson = JSON.stringify(otEmpArr);
    $.ajax({
        async: false,
        url: urlAjaxCheckExistTimeSlotByEmployees,
        type: 'GET',
        data: {
            "dataEmployees": otEmpJson,
            "registerId": $("#form_id").val(),
        },
        success: function (result) {
            hasError = result.hasErrorExist;
            if (hasError) {
                $('#exist_time_lot_before_submit_error').html(result.html);
            }
        },
    });
    return hasError;
}
$.validator.addMethod("enddateMax", function (value, element) {
        if (!value) {
            return true;
        }
        var times = value.replace(/.*\s/, ''),
            hour = times.replace(/:.*/, ''),
            minute = times.replace(/.*:/, '');
        if (!hour || !minute || isNaN(hour) || isNaN(minute)) {
            return true;
        }
        if (!isEmpJp && (('0' + hour).slice(-2) + ':' + ('0' + minute).slice(-2) > '22:00')) {
            return false;
        }
        return true;
    }, 'Must not be greater 22:00.'
);
//validate form
function validateForm() {
    $('#form-register').validate({
        rules: {
            'leader_input': {
                required: true,
            },
            'reason': {
                required: true,
            },
            'project_list': {
                required: true,
            },
            'time_start': {
                required: true,
                //checkTime: true,
                compareTimeStart: '#time_end',
            },
            'time_end': {
                required: true,
                //checkTime: true,
                compareTimeEnd: '#time_start',
                enddateMax: true,
            },
            'relax': {
                number: true,
                checkBreak: true,
                checkBreakOT: ['#time_start', '#time_end'],
            }
        },
        messages: {
            'leader_input': {
                required: errList[0],
            },
            'reason': {
                required: errList[0],
            },
            'project_list': {
                required: errList[0],
            },
            'time_start': {
                required: errList[0],
                //checkTime: errList[1],
                compareTimeStart: errList[5],
            },
            'time_end': {
                required: errList[0],
                //checkTime: errList[1],
                compareTimeEnd: errList[2],
            },
            'relax': {
                number: errList[7],
                checkBreak: errList[3],
                checkBreakOT: errList[3],
            }
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element.parent());
        },
    });

    $('#addEmpForm').validate({
        rules: {
            'add_time_start': {
                required: true,
                //checkTime: true,
                compareTimeStart: '#add_time_end',
            },
            'add_time_end': {
                required: true,
                //checkTime: true,
                compareTimeEnd: '#add_time_start',
            },
        },
        messages: {
            'add_time_start': {
                required: errList[0],
                //checkTime: errList[1],
                compareTimeStart: errList[5],
            },
            'add_time_end': {
                required: errList[0],
                //checkTime: errList[1],
                compareTimeEnd: errList[2],
            },
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element.parent());
        },
    });

    $.validator.addMethod('checkTime', function (value, element, param) {
        var date = moment(value, 'DD-MM-YYYY HH:mm');
        var dayOfWeek = date.day();
        var hour = date.hours();
        var minute = date.minutes();
        var holidayList = getHolidayList(date.year());
        var isHoliday = false;
        for (var i = 0; i < holidayList.length; i++) {
            if (moment(date).isSame(holidayList[i], 'day')) {
                isHoliday = true;
            }
        }
        if (dayOfWeek == 0 || dayOfWeek == 6 || isHoliday) {
            return !(hour < 8 || (hour >= 22 && minute > 0) || hour > 22);
        } else {
            if (isProjAllowed($('#project_list').val(), projectAllowedOT18Key)) {
                return !(hour < 18 || (hour >= 22 && minute > 0) || hour > 22);
            } else {
                return !((hour == 18 && minute < 30) || hour < 18 || (hour >= 22 && minute > 0) || hour > 22);
            }
        }
    });

    $.validator.addMethod('compareTimeEnd', function (value, element, param) {
        var dateCompare = new Date(moment($(param).val(), 'DD-MM-YYYY HH:mm'));
        var arrParam = $(param).val().split(" ");
        var arrValue = value.split(" ");
        return arrParam[0] == arrValue[0];
        // return (new Date(moment(value, 'DD-MM-YYYY HH:mm'))) > dateCompare;
    });

    $.validator.addMethod('compareTimeStart', function (value, element, param) {
        var dateCompare = new Date(moment($(param).val(), 'DD-MM-YYYY HH:mm'));
        var arrParam = $(param).val().split(" ");
        var arrValue = value.split(" ");
        if ($(param).val()) {
            return arrParam[0] == arrValue[0];
            // return (new Date(moment(value, 'DD-MM-YYYY HH:mm'))) < dateCompare;
        } else {
            return true;
        }
    })

    $.validator.addMethod('checkBreak', function (value, element, param) {
        if ($(element).prop("readonly")) {
            return true;
        }
        return (value >= 0 && value <= 14);
    });

    $.validator.addMethod('checkBreakOT', function (value, element, param) {
        if ($(element).prop("readonly")) {
            return true;
        }
        if (!$(param[0]).val() && !$(param[1]).val()) {
            return true;
        }
        var start = moment($(param[0]).val(), 'DD-MM-YYYY HH:mm');
        var end = moment($(param[1]).val(), 'DD-MM-YYYY HH:mm');
        var timeRange = moment.range(moment($(param[0]).val(), 'DD-MM-YYYY'), moment($(param[1]).val(), 'DD-MM-YYYY'));
        var isHolidayEnd = false;
        var isHolidayStart = false;
        if (moment(start).isSame(end, 'day') && (start.day() == 0 || start.day() == 6)) {
            var diff = end.diff(start, 'minutes') / 60;
            return value < diff;
        } else {
            //count numbers of weekends
            var numWeekEnd = countCertainDays([0, 6], start, end);
            //count number of holidays
            var holidayList = getHolidayList(end.year());
            var numHoliday = 0;
            for (var i = 0; i < holidayList.length; i++) {
                if (timeRange.contains(holidayList[i])) {
                    numHoliday++;
                }
                if (moment(start).isSame(holidayList[i], 'day')) {
                    isHolidayStart = true;
                }
                if (moment(end).isSame(holidayList[i], 'day')) {
                    isHolidayEnd = true;
                }
            }
            //set end day of ot end time
            var end_endOfDay = moment($(param[1]).val(), 'DD-MM-YYYY HH:mm').hours(22).minutes(0);
            if (numWeekEnd > 0 || numHoliday > 0) {
                if (start.day() === 0 || start.day() === 6 || isHolidayStart) {
                    var start_startOfDay = moment($(param[0]).val(), 'DD-MM-YYYY HH:mm').hours(8).minutes(0);
                    if (end.day() === 0 || end.day() === 6 || isHolidayEnd) {
                        var diff = (numWeekEnd + numHoliday) * 14 - end_endOfDay.diff(end, 'minutes') / 60 - start.diff(start_startOfDay, 'minutes') / 60;
                    } else {
                        var diff = (numWeekEnd + numHoliday) * 14 - start.diff(start_startOfDay, 'minutes') / 60;
                    }
                } else {
                    var start_startOfDay = moment($(param[0]).val(), 'DD-MM-YYYY HH:mm').hours(18).minutes(30);
                    if (end.day() === 0 || end.day() === 6 || isHolidayEnd) {
                        var diff = (numWeekEnd + numHoliday) * 14 - end_endOfDay.diff(end, 'minutes') / 60;
                    } else {
                        var diff = (numWeekEnd + numHoliday) * 14;
                    }
                }
                return value < diff;
            } else {
                return true;
            }
        }
    });

    $.validator.addMethod("valueNotEquals", function (value, element, arg) {
        return arg !== value;
    });
}

//change approver and team member list when change project
$.fn.changeLeaderByProject = function (projSelector) {
    $(projSelector).on('change', function () {
        var idProject = $(this).find(':selected').val();
        if (idProject === "") {
            $("#projectbtn").prop("disabled", true);
        } else {
            $('#project_list-error').hide();
            $("#leader_input").prop("disabled", false);
            $("#projectbtn").prop("disabled", false);
        }
    });
}

//change approver
$.fn.changeApprover = function () {
    $(this).on('change', function () {
        $('#leader_input-error').hide();
        $('#leader_id').val($(this).val());
    });
}

//change company team member when change team selection
$.fn.changeTagEmployeesByTeam = function () {
    $(this).on('change', function () {
        var idTeam = $(this).find(':selected').val();

        $.ajax({
            url: urlTeamEmployees,
            type: 'GET',
            data: {idTeam: idTeam},
            success: function (memberList) {
                tableTeamEmp.clear().draw();
                $.each(memberList, function (key, value) {
                    tableTeamEmp.row.add({
                        "checkbox": "",
                        "id": key,
                        "code": value.code,
                        "name": value.name,
                        "email": value.email
                    }).draw();
                });
                $("#teamMemberModal .checkbox_all").click(function () {
                    $('#teamMemberModal .checkbox_select').not(this).prop('checked', this.checked);
                });
                $('#teamMemberModal .checkbox_select').click(function () {
                    if ($('#teamMemberModal .checkbox_select').filter(':checked').length === $('#teamMemberModal .checkbox_select').length) {
                        $("#teamMemberModal .checkbox_all").prop('checked', true);
                    } else {
                        $("#teamMemberModal .checkbox_all").prop('checked', false);
                    }
                });
            }
        })
    });
}

//round 2 decimal break time
$.fn.limitBreakInputLength = function () {
    $(this).number(true, 2);
    $(this).keyup(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number or stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
}

//check if number has decimal place
function hasDecimalPlace(value, x) {
    var pointIndex = value.indexOf('.');
    return  pointIndex >= 0 && pointIndex < value.length - x;
}

// days is an array of weekdays: 0 is Sunday, ..., 6 is Saturday
function countCertainDays(days, d0, d1) {
    var ndays = 1 + Math.round(d1.diff(d0, 'days'));
    var sum = function (a, b) {
        return a + Math.floor((ndays + (d0.day() + 6 - b) % 7) / 7);
    };
    return days.reduce(sum, 0);
}

//get List of Momentjs that are holidays
function getHolidayList(year_end) {
    var holidayList = [];

    for (var i = 0; i < annualHolidayList.length; i++) {
        for (var j = (new Date()).getFullYear(); j <= year_end; j++) {
            holidayList.push(moment(j + "-" + annualHolidayList[i], 'YYYY-MM-DD'));
        }
    }
    for (var i = 0; i < specialHolidayList.length; i++) {
        holidayList.push(moment(specialHolidayList[i], 'YYYY-MM-DD'));
    }
    return holidayList;
}

function checkNoEmployee() {
    if ($('#table_ot_employees tbody tr').length <= 0) {
        return true;
    }
    return false;
}

// import projs members to ot employees list
function importMember(table) {
    var selected = table.$("td > input:checked");

    selected.each(function () {
        var member = table.row($(this).parent().parent()).data();
        if ($('#table_ot_employees > tbody > tr#' + member.id).length > 0) {
            $('#projectbtn').parent().append('<div class="include-error errorTxt">' + member.name + errList[7] + '</div>');
            setTimeout(function () {
                $('.include-error').remove();
            }, 3000);
        } else {
            var html = "<tr id='" + member.id + "'>";
            html += "<td class='emp_code'>" + member.code + "</td>";
            html += "<td class='emp_name'>" + member.name + "</td>";
            html += "<td class='start_at'>" + $('#time_start').val() + "</td>";
            html += "<td class='end_at'>" + $('#time_end').val() + "</td>";
            html += "<td class='ot_paid'><center><input type='checkbox' checked/></center></td>";
            html += "<td class='relax'>0.00</td>";
            html += "<td class='btn-manage'><button type='button' class='btn btn-primary edit' onclick='editEmp(" + member.id + ")'><i class='fa fa-pencil-square-o'></i></button>";
            html += " <button type='button' class='btn btn-delete delete' onclick='removeEmp(" + member.id + ")'><i class='fa fa-minus'></i></button></td>";
            html += "</tr>"

            $('#table_ot_employees > tbody').append(html);
        }
    });
    $('#projsMemberModal').modal('hide');
    $('#teamMemberModal').modal('hide');
    if (checkNoEmployee()) {
        $('#error_no_employee').show();
    } else {
        $('#error_no_employee').hide();
    }
}

//add ot employee to the list
function addOtEmployee(empId) {
    var html = "";

    if (!empId) {
        $('#emp_list').siblings('.errorTxt').html(errList[0]);
    } else {
        $('#emp_list').siblings('.errorTxt').empty();
        if (isValidForm($("#addEmpForm"))) {
            //add new ot employees
            if ($('tr#' + empId).length == 0) {
                html += "<tr id='" + empId + "'>";
                html += "<td class='emp_code'>" + $('#add_register_code').val() + "</td>";
                html += "<td class='emp_name'>" + $('#emp_list').find(':selected').text().trim() + "</td>";
                html += "<td class='start_at'>" + $('#add_time_start').val() + "</td>";
                html += "<td class='end_at'>" + $('#add_time_end').val() + "</td>";
                html += "<td class='ot_paid'><center><input type='checkbox' " + ($('#is_paid').is(':checked') ? 'checked' : '') + " readonly=''/></center></td>";
                var timeBreak = parseFloat($('#add_relax').val());
                html += "<td class='relax'>" + timeBreak.toFixed(2) + "</td>";
                html += "<td class='btn-manage'><button type='button' class='btn btn-primary edit' onclick='editEmp(" + empId + ")'><i class='fa fa-pencil-square-o'></i></button>";
                html += " <button type='button' class='btn btn-delete delete' onclick='removeEmp(" + empId + ")'><i class='fa fa-minus'></i></button></td>";
                html += "</tr>"

                $('#table_ot_employees > tbody').append(html);
            } else {
                var totalTimeBreak = 0;
                var status = 0;
                var htmlBreakTime = "";
                var index = 0;
                $('#box_set_time_break_edit .time-break-value').each(function() {
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
                        htmlBreakTime = htmlBreakTime + "<div class='set-time-break-item row'>" +
                            "<div class='col-md-4 ot-form-group'>" +
                            "<label class='control-label time-break-date'>" + date + "</label>" +
                            "</div>" +
                            "<div class='col-md-8 ot-form-group'>" +
                            "<input type='text' class='form-control time-break-value' value='" + timeBreak + "'>" +
                            "</div>" +
                            "</div>";
                    }
                    index++;
                });
                if (status === 1) {
                    return;
                }

                $('tr#' + empId).find('.emp_code .emp_code_main').html($('#add_register_code').val());
                $('tr#' + empId).find('.emp_code .has-set-time-break-edit').html(htmlBreakTime);
                $('tr#' + empId).children('.emp_name').html($('#emp_list option:selected').text().trim());
                $('tr#' + empId).children('.start_at').html($('#add_time_start').val());
                $('tr#' + empId).children('.end_at').html($('#add_time_end').val());
                var timeBreak = parseFloat($('#add_relax').val());
                $('tr#' + empId).children('.relax').html(totalTimeBreak.toFixed(2));
                $('tr#' + empId).children('.ot_paid').find('input').prop('checked', $('#is_paid').is(':checked'));
                if (empId == $('#emp_id').val()) {
                    $('#time_end').val($('#add_time_end').val());
                    $('#time_end').prop('disabled', false);
                    $('#time_start').val($('#add_time_start').val());
                    $('#time_start-error').remove();
                    $('#time_end-error').remove();
                    $('#total_time_break').val(totalTimeBreak.toFixed(2));
                    $('#has_set_time_break').html(htmlBreakTime);
                    $('#check_set_break_time').val(1);
                    $('#relax').val($('#add_relax').val());
                    $('#relax').val();
                    if ($('#add_relax').is('[readonly]')) {
                        $('#relax').prop('readonly', true);
                    } else {
                        $('#relax').prop('readonly', false);
                    }
                    var startDate = moment($('#datetimepicker_start').find('input:first').val(), 'DD-MM-YYYY');
                    var endDate = moment($('#datetimepicker_end').find('input:first').val(), 'DD-MM-YYYY');
                    if (startDate != null && endDate != null) {
                        startDate = new Date(startDate);
                        endDate = new Date(endDate);
                        startDate.setHours(0, 0);
                        endDate.setHours(0, 0);
                        if (hasHolidayOrWeekend(startDate, endDate, annualHolidayList, specialHolidayList)) {
                            $('#form_set_time_break').show();
                        } else {
                            $('#form_set_time_break').hide();
                        }
                    }
                    $('#box_set_time_break_edit .set-time-break-item').each(function() {
                        var date = $('.time-break-date-val').data('date');
                        var breakValue = $('.time-break-value').val();
                        timeBreakOrigin[date] = breakValue;
                    });
                }
            }
            $('#addEmp').modal('hide');
            $('#modal_set_time_break #box_set_time_break .time-break-date-val').text()
        }
    }
}

//edit selected employee
function editEmp(id) {

    $('#exist_time_lot_before_submit_error').html('');
    $('#addEmp').validate().resetForm();
    $('#addEmp').find('.errorTxt').empty();
    var empRow = $('#table_ot_employees > tbody').children('tr#' + id);
    $('#add_register_code').attr('data-id', empRow.prop('id'));

    $('#datetimepicker_add_start').data('DateTimePicker').clear();
    $('#datetimepicker_add_end').data('DateTimePicker').clear();
    $('#addEmp').modal('show');
    $('#addEmp').find('h4.modal-title').html(titleList[1]);
    $('#add_register_code').prop('value', empRow.find('.emp_code .emp_code_main').html());
    $('#add_time_start').val(empRow.children('.start_at').html());
    $('#box_set_time_break_edit').html(empRow.find('.emp_code .has-set-time-break-edit').html());
    $('#box_set_time_break_edit .set-time-break-item').each(function() {
        var date = $(this).find('.time-break-value').data('date');
        if (breakTimesEmployees[id] && breakTimesEmployees[id][date]) {
            $(this).find('.time-break-value').val(breakTimesEmployees[id][date]);
        }
    });
    $('#group_set_time_edit').css('display', 'block');
    if (empRow.children('.end_at').html()) {
        $('#datetimepicker_add_end').data("DateTimePicker").enable();
        $('#add_time_end').val(empRow.children('.end_at').html());
    } else {
        $('#datetimepicker_add_end').data("DateTimePicker").disable();
    }
    $('#add_relax').val(empRow.children('.relax').html());
    isBreakTimeWeekend(false);
    if (empRow.find('input').is(':checked')) {
        $('#is_paid').prop('checked', true);
    } else {
        $('#is_paid').prop('checked', false);
    }
    if ($('#emp_list').find('option[value=' + id + ']').length > 0) {
        $('#emp_list').val(id).trigger('change');
    } else {
        $('#emp_list').append($('<option>', {value: id, text: empRow.children('.emp_name').html()}));
        $('#emp_list').val(id).trigger('change');
    }
    $('.time-break-value').limitBreakInputLength();
}

//remove selected employee
function removeEmp(id) {
    $('#exist_time_lot_before_submit_error').html('');
    $('#table_ot_employees > tbody').children('tr#' + id).remove();
    $('#table_ot_employees > tbody tr').map(function (index, item) {
        if ($('#table_ot_employees > tbody tr').length <= 1) {
            $('#table_ot_employees > thead > tr').children(':first-child').remove();
            $('#table_ot_employees > tbody > tr').children(':first-child').remove();
        }
        $(item).find(".stt").text($(item).index() + 1);
    });

    if (checkTimeNotFilled()) {
        $('#error_time_not_filled').show();
    } else {
        $('#error_time_not_filled').hide();
    }
    if (checkNoEmployee()) {
        $('#error_no_employee').show();
    } else {
        $('#error_no_employee').hide();
    }
}

function checkTimeNotFilled() {
    var isNotFilled = false;
    $('#table_ot_employees > tbody').find('td.start_at, td.end_at').each(function (i, ele) {
        if (!$(this).text()) {
            isNotFilled = true;
        }
    });
    return isNotFilled;
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

//preprocess form before save
function preSaveProcessing() {
    var otEmpArr = [];
    var timeBreaks = [];
    //convert table data to json
    if ($('#table_ot_employees > tbody').find('tr').length > 0) {
        $('#table_ot_employees > tbody').find('tr').each(function () {
            var tds = $(this).find('td');
            var employeeId = $(this).attr('id');
            otEmpArr.push({
                "empId": $(this).attr('id'),
                "empCode": tds.closest('.emp_code').children('.emp_code_main').text().trim(),
                "empName": tds.closest('.emp_name').text(),
                "startAt": tds.closest('.start_at').text(),
                "endAt": tds.closest('.end_at').text(),
                "isPaid": tds.find('input').is(':checked'),
                "break": tds.closest('.relax').text()
            });
            $(this).find('td.emp_code .has-set-time-break-edit .time-break-value').each(function() {
                timeBreaks.push({
                    "empId": employeeId,
                    "time_break": $(this).val(),
                    "date": $(this).closest('.set-time-break-item').find('.time-break-date .time-break-date-val').text(),
                });
            });
        });
    }
    $('#has_set_time_break .time-break-value').each(function() {
        timeBreaks.push({
            "empId": $('#emp_id').val(),
            "time_break": $(this).val(),
            "date": $(this).closest('.set-time-break-item').find('.time-break-date .time-break-date-val').text(),
        });
    });
    var otEmpJson = JSON.stringify(otEmpArr);
    $('#table_data_emps').val(otEmpJson);
    $('#time_breaks').val(JSON.stringify(timeBreaks));

    //submit form
    $('#form-register').submit();
}

//validate ot employees table
function validateTagEmployees() {
    var valid = true;
    //check ot employees table required columns are filled
    $('#table_ot_employees > tbody').find('td.start_at, td.end_at').each(function (i, ele) {
        if (!$(this).text()) {
            $('#error_time_not_filled').show();
            valid = false;
            return false;
        }
    });
    if ($('#table_ot_employees tbody tr').length <= 0) {
        $('#error_no_employee').show();
        valid = false;
        return false;
    }
    return valid;
}

// inittialize Datatable
function initDataTable(selector) {
    return $(selector).DataTable({
        'columns': [
            {'data': "checkbox"},
            {'data': "id"},
            {'data': "code"},
            {'data': "name"},
            {'data': "email"}
        ],
        'columnDefs': [{
            'targets': 0,
            'searchable': false,
            'orderable': false,
            'className': 'dt-body-center',
            'render': function () {
                return '<input class="checkbox_select" type="checkbox">';
            }
        },
            {
                "targets": 1,
                "visible": false,
                "searchable": false
            }],
        'order': [[1, 'asc']],
        "bAutoWidth": false,
        "language": {
            "emptyTable": viewList[0]
        }
    });
}

//check if form is error-free
function isValidForm(formSelector) {
    var valid = formSelector.valid();
    var error = false;

    formSelector.find(".errorTxt").each(function () {
        if ($(this).text().trim()) {
            error = true;
            return false;
        }
    });

    return (valid && !error);
}

//add user info to ot employee table
function initOtEmployeeTable() {
    if ($('#table_ot_employees').find('tr#' + $('#register_id').data('id')).length == 0) {
        var regId = $('#register_id').data('id');
        var html = "<tr id='" + regId + "'>";
        var htmlSetTimeBreak = "<div class='has-set-time-break-edit' style='display: none;'>" + $('#has_set_time_break').html() + "</div>";
        html += "<td class='emp_code'><div class='emp_code_main'>" + $('#register_code').val() + "</div>" + htmlSetTimeBreak + "</td>";
        html += "<td class='emp_name'>" + $('#register_id').val() + "</td>";
        html += "<td class='start_at'>" + $('#time_start').val() + "</td>";
        html += "<td class='end_at'>" + $('#time_end').val() + "</td>";
        html += "<td class='ot_paid'><center><input type='checkbox' checked readonly=''></center></td>";
        // var timeBreak = parseFloat($('#relax').val());
        var timeBreak = parseFloat($('#total_time_break').val());
        html += "<td class='relax'>" + timeBreak.toFixed(2) + "</td>";
        html += "<td class='btn-manage'><button type='button' class='btn btn-primary edit' onclick='editEmp(" + $('#emp_id').val() + ")'><i class='fa fa-pencil-square-o'></i></button>";
        html += " <button type='button' class='btn btn-delete delete' onclick='removeEmp(" + regId + ")'><i class='fa fa-minus'></i></button></td>";
        html += "</tr>"
        $('#table_ot_employees').children('tbody').prepend(html);

        var date = moment($('#time_start').val(), 'DD-MM-YYYY');
        var date = date.format('DD-MM-YYYY').toString();
        breakTimesEmployees[regId] = {};
        breakTimesEmployees[regId][date] = timeBreak;
    }
}

//change break time in ot employee table when change break time in form
$.fn.changeBreakTime = function () {
    $(this).bind('keyup mouseup', function () {
        var value = $(this).val();
        if (hasDecimalPlace(value, 3)) {
            value = value.match(/^\d+\.\d{1,2}/)[0];
        }
        value = parseFloat(value);
        $("#table_ot_employees").find("tr#" + $("#register_id").data("id")).children("td.relax").text(value.toFixed(2));
    });
}

//check if is break time for weekend
function isBreakTimeWeekend(mainForm) {
    if (mainForm) {
        var start = $('#datetimepicker_start');
        var end = $('#datetimepicker_end');
        var relaxId = "#relax";
    } else {
        var start = $('#datetimepicker_add_start');
        var end = $('#datetimepicker_add_end');
        var relaxId = "#add_relax";
    }
    if (start.children('input').val() && end.children('input').val()) {
        var numHoliday = 0;
        var startTime = moment(start.children('input').val(), 'DD-MM-YYYY HH:mm');
        var endTime = moment(end.children('input').val(), 'DD-MM-YYYY HH:mm');
        var holidayList = getHolidayList(endTime.year());

        if (moment(startTime).isSame(endTime, 'day')) {
            var date = moment($(start).find('input:first').val(), 'DD-MM-YYYY');
            for (var i = 0; i < holidayList.length; i++) {
                if (moment(date).isSame(holidayList[i], 'day')) {
                    numHoliday++;
                }
            }
        } else {
            var timeRange = moment.range(startTime, endTime);
            for (var i = 0; i < holidayList.length; i++) {
                if (timeRange.contains(holidayList[i])) {
                    numHoliday++;
                }
            }
        }
        var numWeekEnd = countCertainDays([0, 6], startTime, endTime);
        if (numWeekEnd > 0 || numHoliday > 0) {
            $(relaxId).prop("readonly", false);
        } else {
            $(relaxId).prop("readonly", true);
        }
    }
}

//clear all checkbox when open add ot emp/project emp modal
function clearModalCheckBox() {
    $('.btn-add').click(function () {
        $('#teamMemberModal, #projsMemberModal').find('input:checkbox').prop('checked', false);
    });
}

// initialize ot register form
function initForm(pageType) {
    if (pageType == "edit") {
        if (isEditable && isEditable != "") {
            $('#time_end').prop('disabled', false);
        }
        $('#project_list').trigger('change');
    }
    $('.team-search').select2();
    //init datetimepicker
    var start_at = $('#time_start').val();
    var end_at = $('#time_end').val();
    $('#datetimepicker_start, #datetimepicker_end, #datetimepicker_add_start, #datetimepicker_add_end').initOTTimeCalendar();
    $('#time_start').val(start_at);
    $('#time_end').val(end_at);
    //change approver when change project
    $('#leader_input').changeLeaderByProject('#project_list');
    $('.team-search').changeTagEmployeesByTeam();
    $('#leader_input').changeApprover();
    //round 2 decimal break time
    $('#relax').limitBreakInputLength();
    $('#add_relax').limitBreakInputLength();
    //validate form
    validateForm();
    validateTime();
    initAddOtEmployee();
    if (pageType === "create") {
        //prepare form
        initOtEmployeeTable();
    }
    isBreakTimeWeekend(true);
    $("button#submitBtn").click(function () {
        var hasError = checkExistTimeSlotBeforeSubmit();
        if (isValidForm($("#form-register")) && validateTagEmployees()) {
            if (!hasError) {
                preSaveProcessing();
            }
        }
    });
    //other
    $.fn.modal.Constructor.prototype.enforceFocus = function () {};
    $(".ot-read-more").shorten({
        "showChars": 200,
        "moreText": "See more",
        "lessText": "Less",
    });
    $('b[role="presentation"]').hide();
    $("#relax").changeBreakTime();
    clearModalCheckBox();
    $('.btn-approve').setApproveIds();
    $('.btn-reject').setRejectIds();
}

$.fn.selectSearchEmployee = function () {
    $(this).select2({
        id: function(response){
            return response.id;
        },
        ajax: {
            url: $(this).data('remote-url'),
            dataType: "JSON",
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page,
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 5) < data.total_count
                    }
                };
            },
            cache: true,
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 2,
        templateResult: formatReponse,
        templateSelection: formatReponseSelection,
    });
};

$.fn.selectSearchEmployeeCanapprove = function () {
    $(this).select2({
        id: function(response){
            return response.id;
        },
        ajax: {
            url: $(this).data('remote-url'),
            dataType: "JSON",
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                };
            },
            processResults: function (data) {
                return {
                    results: data.items,
                };
            },
            cache: true,
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 2,
        templateResult: formatReponse,
        templateSelection: formatReponseSelection,
    });
};

function formatReponse (response) {
    if (response.loading) {
        return response.text;
    }

    return markup = (response.avatar_url) ?
        "<div class='select2-result-repository clearfix'>" +
        "<div class='select2-result-repository__title'>" +
        "<img style=\"margin-right:8px;max-width: 32px;max-height: 32px;border-radius: 50%;\" src=\""+
        response.avatar_url+"\">" + response.text +
        "</div>" +
        "</div>"
        :
        "<div class='select2-result-repository clearfix'>" +
        "<div class='select2-result-repository__title'>" +
        "<i style='margin-right:8px' class='fa fa-user-circle fa-2x' aria-hidden='true'></i>" +
        response.text +
        "</div>" +
        "</div>";
}

function formatReponseSelection (response, domSpan) {
    if (typeof response.dataMore === 'object') {
        var domSelect = domSpan.closest('.select2.select2-container')
            .siblings('select').first();
        $.each(response.dataMore, function (key, value) {
            domSelect.data('select2-more-' + key, value);
        });
    }
    return  response.text;
}

function hasHolidayOrWeekend(startDate, endDate, annualHolidayList, specialHolidayList) {
    if (startDate === null || endDate === null) {
        return false;
    }
    startDate.setHours(0, 0);
    endDate.setHours(0, 0);
    for (var date = startDate; date <= endDate; date.setDate(date.getDate() + 1)) {
        if (isHolidayOrWeekend(date, annualHolidayList, specialHolidayList)) {
            return true;
        }
    }
    return false;
}

function isHolidayOrWeekend(date, annualHolidayList, specialHolidayList) {
    if (date === null) {
        return false;
    }
    var dateCompare = new Date(date);
    dateCompare.setHours(0, 0);
    var daysOfWeek = dateCompare.getDay();
    var yearCurrent = dateCompare.getFullYear();
    var getTimeOfDate = dateCompare.getTime();
    if (isWeekend(date)) {
        return true;
    } else {
        if (typeof annualHolidayList === 'object') {
            var arrAnnualHolidaysLen = annualHolidayList.length;
            for (var i = 0; i < arrAnnualHolidaysLen; i++) {
                dateAnnual = new Date(yearCurrent + '-' + annualHolidayList[i]);
                dateAnnual.setHours(0, 0);
                getDateTimeAnnual = dateAnnual.getTime();
                if (getDateTimeAnnual === getTimeOfDate) {
                    return true;
                }
            }
        }
        if (typeof specialHolidayList === 'object') {
            var arrSpecialHolidaysLen = specialHolidayList.length;
            for (var i = 0; i < arrSpecialHolidaysLen; i++) {
                dateSpecial = new Date(specialHolidayList[i]);
                dateSpecial.setHours(0, 0);
                getDateTimeSpecial = dateSpecial.getTime();
                if (getDateTimeSpecial === getTimeOfDate) {
                    return true;
                }
            }
        }
    }
    return false;
}

function getDayOfWeek(day) {
    switch (day) {
        case 0 : {
            return 'Chủ nhật';
        }
        case 1 : {
            return 'Thứ hai';
        }
        case 2 : {
            return 'Thứ ba';
        }
        case 3 : {
            return 'Thứ tư';
        }
        case 4 : {
            return 'Thứ năm';
        }
        case 5 : {
            return 'Thứ sáu';
        }
        default : {
            return 'Thứ bảy'
        }
    }
}

function isProjAllowed(projSelected, projAllowedList) {
    return jQuery.inArray(projSelected, projAllowedList) !== -1
}

function setTimeBreak(empId) {
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
                var strDate = twoDigitDate + '/' + twoDigitMonth + '/' + date.getFullYear();
                $('#duplicate_set_time_break_item .time-break-date .time-break-date-val').attr('data-date', strDate).text(strDate);
                $('#duplicate_set_time_break_item .time-break-date .time-break-date-day').text(' (' + daysOfWeek + ')');
                $('#duplicate_set_time_break_item .time-break-value').attr('data-date', strDate);
                var html = $('#duplicate_set_time_break_item').html();
                $('#box_set_time_break').append(html);
                var oldValue = breakTimesEmployees[empId][strDate];
                if (oldValue) {
                    $('#box_set_time_break .time-break-value[data-date="'+strDate+'"]').val(oldValue);
                }
            }
        }
    }

    $('.time-break-value').limitBreakInputLength();
}

function saveSetTimeBreak() {
    var totalTimeBreak = 0;
    var html = "";
    var status = 0;
    var empId = $("#register_id").data("id");
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
            var dateHtml = $(this).closest('.set-time-break-item').find('.time-break-date').html();
            timeBreak = timeBreak.toFixed(2);
            var date = $(this).closest('.set-time-break-item').find('.time-break-date-val').text();
            html = html + "<div class='set-time-break-item row'>" +
                "<div class='col-md-4 ot-form-group'>" +
                "<label class='control-label time-break-date' >" + dateHtml + "</label>" +
                "</div>" +
                "<div class='col-md-8 ot-form-group'>" +
                "<input type='text' class='form-control time-break-value' data-date='" + date + "' value='" + timeBreak + "'>" +
                "</div>" +
                "</div>";

            timeBreakOrigin[date] = timeBreak;
            breakTimesEmployees[empId][date] = timeBreak;
        }
    });
    if (status == 1) {
        return;
    }
    $('#check_set_break_time').val(1);
    $('#total_time_break').val(totalTimeBreak.toFixed(2));
    $('#has_set_time_break').html(html);
    $("#table_ot_employees").find("tr#" + empId).children("td.relax").text(totalTimeBreak.toFixed(2));
    $("#table_ot_employees").find("tr#" + empId).find("td.emp_code .has-set-time-break-edit").html($('#has_set_time_break').html());
    $('#modal_set_time_break').modal('hide');
}

$('#btn_add_employee_ot').on('click', function() {
    var $this = $(this);
    var employees = $('#search_employee_ot').select2('data');
    if (!employees.length || $this.data('process')) {
        return true;
    }
    $this.data('process', 1);
    var url = $this.data('url');
    var startTime = moment(convertDate($timeStart.val())).toDate();
    var startDate = startTime.format('Y-m-d');
    var endTime = moment(convertDate($timeEnd.val())).toDate();
    var endDate = endTime.format('Y-m-d');
    var employeeIds = [];
    jQuery.each(employees, function (index, value) {
        employeeIds.push(value['id']);
    });
    $.ajax({
        type: "POST",
        url: url,
        dataType: 'json',
        data : {empIds: employeeIds, _token : token, startDate: startDate, endDate: endDate},
        success: function (response) {
            employeeIds.forEach(function(employeeId) {
                timeSetting[employeeId] = timeSetting[employeeId] || {};
                var empTimeSetting = response[0][employeeId];
                Object.keys(empTimeSetting).forEach(function (date) {
                    timeSetting[employeeId][date] = empTimeSetting[date];
                });
            });

            if ($tableEmployeeOT.find('tbody > tr').length <= 1 && $tableEmployeeOT.find('thead > tr').children().length < 8) {
                $tableEmployeeOT.find('thead > tr').prepend("<th class='col-width-40'>" + viewStt +" </th>");
                $tableEmployeeOT.find('tbody > tr').prepend("<td class='stt text-center'> 1 </td>");
            }
            var employeeCount = $tableEmployeeOT.find('tbody > tr').length;
            jQuery.each(employees, function (index, value) {
                var startTimeOTPopup = getStartTimeOT(value['id'], startDate);
                var startTimeOT = startTimeOTPopup >= startTime ? startTimeOTPopup : startTime;
                var endTimeOTPopup = new Date(endDate);
                endTimeOTPopup.setHours(22, 0);
                var endTimeOT = endTimeOTPopup <= endTime ? endTimeOTPopup : endTime;

                if (!$tableEmployeeOT.find('tbody > tr#' + value['id']).length) {
                    var html = "<tr id='" + value['id'] + "'>";
                    var htmlSetTimeBreak = "<div class='has-set-time-break-edit' style='display: none;'>" + $('#has_set_time_break').html() + "</div>";
                    html += "<td class='stt text-center'>"+ (++employeeCount) + "</td>";
                    html += "<td class='emp_code'><div class='emp_code_main'>" + value['employee_code'] + "</div>" + htmlSetTimeBreak + "</td>";
                    html += "<td class='emp_name'>" + value['employee_name'] + "</td>";
                    html += "<td class='start_at'>" + startTimeOT.format('d-m-Y H:i') + "</td>";
                    html += "<td class='end_at'>" + endTimeOT.format('d-m-Y H:i') + "</td>";
                    html += "<td class='ot_paid text-center'><input type='checkbox' checked/></td>";
                    html += "<td class='relax'>" + $('#total_time_break').val() + "</td>";
                    html += "<td class='btn-manage'><button type='button' class='btn btn-primary edit' onclick='editEmp(" +value['id'] + ")'><i class='fa fa-pencil-square-o'></i></button>";
                    html += "<button type='button' class='btn btn-delete delete' onclick='removeEmp(" + value['id'] + ")'><i class='fa fa-minus'></i></button></td>";
                    html += "</tr>";
                    $tableEmployeeOT.find('tbody').append(html);
                }
            });
            $('#search_employee_ot').html('').trigger('change');
        },
        complete: function () {
            $this.data('process', 0);
        },
    });
});

/**
 * get time start OT of employee in date
 * @param {number} employeeId
 * @param {string} dateKey - format YYYY-mm-dd
 * @returns {Date}
 */
function getStartTimeOT(employeeId, dateKey) {
    var tsItem = timeSetting[employeeId][dateKey];
    var startTime = new Date(dateKey);
    if (isHolidayOrWeekend(startTime, annualHolidayList, specialHolidayList)) {
        startTime.setHours(tsItem['morningInSetting']['hour'], tsItem['morningInSetting']['minute']);
        return startTime;
    }
    if (isProjAllowed($projectSelect.val(), projectAllowedOT18Key)) {
        startTime.setHours(tsItem['afternoonOutSetting']['hour'], tsItem['afternoonOutSetting']['minute']);
        return startTime;
    }
    startTime.setHours(tsItem['afternoonOutSetting']['hour'] + 1, tsItem['afternoonOutSetting']['minute']);
    return startTime;
}

/**
 * check date is holiday
 * @param {Date} startTime
 * @return {boolean}
 */
function isHoliday(startTime) {
    var holidayList = getHolidayList(startTime.getFullYear());
    for (var i = 0; i < holidayList.length; i++) {
        if (moment(startTime).isSame(holidayList[i], 'day')) {
            return true;
        }
    }
    return false;
}

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
