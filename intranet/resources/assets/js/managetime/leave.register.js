// Wait for window load
$(window).load(function() {
    // Animate loader off screen
    $(".se-pre-con").fadeOut("slow");
});

var $startDateContainer = $('#datetimepicker-start-date');
var $endDateContainer = $('#datetimepicker-end-date');

function startAction(self, elem, arrow, empId) {
    var startDate = $(elem).data("DateTimePicker").date();
    var startTime = new Date(startDate);
    var dateKey = getStrDate(startTime);
    if (arrow === 'increase') {
        increase(startTime, dateKey, empId, elem, 'timeIn')
    } else {
        decrease(startTime, dateKey, empId, elem, 'timeIn');
    }
}

function endAction(self, elem, arrow, empId) {
    var endDate = $(elem).data("DateTimePicker").date();
    var endTime = new Date(endDate);
    var dateKey = getStrDate(endTime);
    if (arrow === 'increase') {
        increase(endTime, dateKey, empId, elem, 'timeOut')
    } else {
        decrease(endTime, dateKey, empId, elem, 'timeOut');
    }
}

function setStartDateOnChange() {
    var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
    var startTime = new Date(startDate);
    var dateKey = getStrDate(startTime);

    setTimeStart(startTime, dateKey, currentEmpId, '#datetimepicker-start-date');
    reCalculateLeaveDay()
}

function reCalculateLeaveDay() {
    var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
    var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
    var empId = currentEmpId;

    var numberDaysTimekeeping = getDaysTimekeeping(startDate, endDate, empId);
    $('#number_days_off').val(numberDaysTimekeeping);
    $('#number_validate').val(numberDaysTimekeeping);
}

function setEndDateOnChange() {
    var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
    var endTime = new Date(endDate);
    var dateKey = getStrDate(endTime);
    setTimeEnd(endTime, dateKey, currentEmpId, '#datetimepicker-end-date');
    reCalculateLeaveDay();
}

$(function() {
    $('.managetime-upload-file input:file').fileuploader({
        addMore: true,
        extensions: ['jpg', 'jpeg', 'png', 'bmp'],
    });

    $('.fancybox').fancybox({
        openEffect: 'none',
        closeEffect: 'none'
    });

    var salaryRate = 0;
    if ($('#reason').find(":selected").attr("data-salary-rate") !== undefined) {
        salaryRate = $('#reason').find(":selected").attr("data-salary-rate");
    }
    $('#salary_rate').val(salaryRate + ' %');

    for (var i = 0; i < weekends.length; i++) {
        if ($.inArray(weekends[i], compensationDays.com) === -1) {
            disabledDates.push(moment(weekends[i], 'YYYY-MM-DD'));
        }
    }
    for (var i = 0; i < compensationDays.lea.length; i++) {
        disabledDates.push(moment(compensationDays.lea[i], 'YYYY-MM-DD'));
    }

    $('#datetimepicker-start-date').datetimepicker({
        allowInputToggle: true,
        sideBySide: true,
        //daysOfWeekDisabled: [0],
        disabledDates: disabledDates,
        //disabledHours: disabledHoursStart(),
        defaultDate: startDateDefault,
    }).on('dp.show', function() {
        var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
        $('#datetimepicker-start-date a[data-action=incrementHours]').attr('data-action', 'increase-hour').attr('href', 'javascript:void(0)').attr('onclick', 'startAction(this, "#datetimepicker-start-date", "increase", ' + currentEmpId + ')');
        $('#datetimepicker-start-date a[data-action=decrementHours]').attr('data-action', 'decrease-hour').attr('href', 'javascript:void(0)').attr('onclick', 'startAction(this, "#datetimepicker-start-date", "decrease", ' + currentEmpId + ')');
        $('#hidden-end-date').hide();
        $('#datetimepicker-end-date').show();

        var checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            if (startDate == null) {
                $('#start_date-error').show();
                $('#number_days_off-error').hide();
                $('#end_date_before_start_date-error').hide();
                $('#register_exist_error').hide();
            } else {
                $('#start_date-error').hide();
                $('#number_days_off-error').hide();
                $('#end_date_before_start_date-error').hide();
                $('#register_exist_error').hide();
            }
        }
        setStartDateOnChange();

    }).on('dp.hide', function() {
        var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
        if (startDate == null) {
            $('#hidden-end-date').show();
            $('#datetimepicker-end-date').hide();
            $('#datetimepicker-end-date').data("DateTimePicker").date(null);
        }

        var checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            if (start_date == null) {
                $('#start_date-error').show();
                $('#end_date-error').show();
                $('#end_date_before_start_date-error').hide();
                $('#register_exist_error').hide();
            } else {
                $('#start_date-error').hide();
                $('#register_exist_error').hide();
            }
        }
    }).on('dp.change', function() {
        $('#number_days_off-error').hide();
        var startDate = $startDateContainer.data("DateTimePicker").date();
        /* set default start date is current date when date is invalid */
        if (!startDate) {
            $startDateContainer.data('DateTimePicker').date(new Date());
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
        setStartDateOnChange();
    });

    $('#datetimepicker-end-date').datetimepicker({
        allowInputToggle: true,
        sideBySide: true,
        //daysOfWeekDisabled: [0],
        disabledDates: disabledDates,
        //disabledHours: disabledHoursEnd(),
        defaultDate: endDateDefault
    }).on('dp.show', function() {
        $('#datetimepicker-end-date a[data-action=incrementHours]').attr('data-action', 'increase-hour').attr('href', 'javascript:void(0)').attr('onclick', 'endAction(this, "#datetimepicker-end-date", "increase", ' + currentEmpId + ')');
        $('#datetimepicker-end-date a[data-action=decrementHours]').attr('data-action', 'decrease-hour').attr('href', 'javascript:void(0)').attr('onclick', 'endAction(this, "#datetimepicker-end-date", "descrease", ' + currentEmpId + ')');
        var checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
            if (endDate == null) {
                $('#end_date-error').show();
                $('#number_days_off-error').hide();
                $('#end_date_before_start_date-error').hide();
                $('#register_exist_error').hide();
            } else {
                $('#end_date-error').hide();
                $('#number_days_off-error').hide();
                $('#end_date_before_start_date-error').hide();
                $('#register_exist_error').hide();
            }
        }
        setEndDateOnChange();
    }).on('dp.hide', function(e) {
        var checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
            if (endDate == null) {
                $('#end_date-error').show();
                $('#number_days_off-error').hide();
                $('#end_date_before_start_date-error').hide();
                $('#register_exist_error').hide();
            } else {
                $('#end_date-error').hide();
                $('#number_days_off-error').hide();
                $('#end_date_before_start_date-error').hide();
                $('#register_exist_error').hide();
            }
        }
    }).on('dp.change', function() {
        $('#number_days_off-error').hide();
        var endDate = $endDateContainer.data("DateTimePicker").date();
        /* set default end date is current date when date is invalid */
        if (!endDate) {
            $endDateContainer.data('DateTimePicker').date(new Date());
            return;
        }
        var startDate = $startDateContainer.data("DateTimePicker").date();
        var startTime = new Date(startDate);
        var endTime = new Date(endDate);

        var employeeId = currentEmpId;
        if ($('select#employee_id').length > 0 && $('select#employee_id').val() != null) {
            employeeId = $('select#employee_id').val();
        }
        updateTimeSettingData(employeeId, startTime.format('Y-m-d'), endTime.format('Y-m-d'));
        setEndDateOnChange();
    });

    reCalculateLeaveDay();
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

    $(".search-employee").select2({
        ajax: {
            url: urlSearchRelatedPerson,
            dataType: "JSON",
            data: function(params) {
                return {
                    q: $.trim(params.term)
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });

    $('#reason').on('select2:select', function(evt) {
        var salaryRate = $('#reason').find(":selected").attr("data-salary-rate");
        $('#salary_rate').val(salaryRate + ' %');

        var reasonCode = $('#reason').find(":selected").attr("data-reason-code");
        if (reasonCode == USED_LEAVE_DAY) {
            var checkSubmit = $('#check_submit').val();
            if (checkSubmit == 1) {
                var numberDaysOff = $('#number_days_off').val();
                var numberDaysRemain = $('#number_days_remain').val();

                if (parseFloat(numberDaysOff) > parseFloat(numberDaysRemain)) {
                    $('#number_days_off-error').show();
                }
            }

            //Check register before offcial date
            if (checkRegisterBeforeOffcial()) {
                $('#day_off_before_offcial_error').show();
            } else {
                $('#day_off_before_offcial_error').hide();
            }
        } else {
            $('#number_days_off-error').hide();
            $('#day_off_before_offcial_error').hide();
        }
    });

    $('#reason').on('change', function() {
        var fullday = $(this).find('option:selected').data('calculate-full-day');
        if (fullday) {
            CalculateFullDay = true;
        } else {
            CalculateFullDay = false;
        }
        reCalculateLeaveDay();
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
            data: {
                registerId: registerId,
                urlCurrent: urlCurrent
            },
            success: function(result) {
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
            data: {
                registerId: registerId,
                urlCurrent: urlCurrent,
                reasonDisapprove: reasonDisapprove
            },
            success: function(result) {
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

    $('#note').keyup(function() {
        var teamCodePreOfEmp = $('#team_code_pre_of_emp').val().trim();
        var codePrefixJp = $('#code_prefix_jp').val().trim();
        var reason = $('#reason').val().trim();
        var reasonPaidLeaveJp = $('#reason_paid_leave_jp').val.trim();
        if (teamCodePreOfEmp != codePrefixJp && reason != reasonPaidLeaveJp) {
            var note = $('#note').val();
            if (note.trim() == '') {
                $('#note-error').show();
            } else {
                $('#note-error').hide();
            }
            
        }
    });

    $("#approver").on('change', function(evt) {
        $('#approver-error').hide();
    });
});

Date.prototype.addDays = function(days) {
    this.setDate(this.getDate() + parseInt(days));
    return this;
};

function dateRange(startDate, endDate) {
    var dates = [];
    startDate = new Date(startDate);
    endDate = new Date(endDate);
    while (Date.parse(startDate) <= Date.parse(endDate)) {
        dates.push(getStrDate(startDate));
        startDate.addDays(1);
    }
    return dates;
}

function getDaysTimekeeping(startDate, endDate, empId) {
    var startDateGetDiffDay = new Date(startDate);
    var endDateGetDiffDay = new Date(endDate);
    var startDateString = getStrDate(startDateGetDiffDay);
    var endDateString = getStrDate(endDateGetDiffDay);
    var arrayDateRange = dateRange(startDateString, endDateString);
    var timeDiff = 0;
    var calculateFullDay = $('#reason').find(":selected").data("calculate-full-day");
    for (var i = 0; i < arrayDateRange.length; i++) {
        var dateKey = arrayDateRange[i];

        //First, last date of month
        var start = '01';
        var y = arrayDateRange[i].split('-')[0];
        var m = arrayDateRange[i].split('-')[1];
        var end = new Date(y, parseInt(m), 0).getDate();

        if (i === 0) {
            start = startDateGetDiffDay.getDate();
        }
        if (i === arrayDateRange.length - 1) {
            end = endDateGetDiffDay.getDate();
        }
        if (('' + start).length === 1) {
            start = '0' + start;
        }
        if (('' + end).length === 1) {
            end = '0' + end;
        }
        start = new Date(arrayDateRange[i]);
        end = new Date(arrayDateRange[i]);

        if (arrayDateRange.length > 1) {
            if (i < arrayDateRange.length - 1) {
                end.setHours(timeSetting[empId][dateKey]['afternoonOutSetting']['hour'], timeSetting[empId][dateKey]['afternoonOutSetting']['minute'], 00);
            } else {
                end.setHours(endDateGetDiffDay.getHours(), endDateGetDiffDay.getMinutes());
            }
            if (i === 0) {
                start.setHours(startDateGetDiffDay.getHours(), startDateGetDiffDay.getMinutes());
            } else {
                start.setHours(timeSetting[empId][dateKey]['morningInSetting']['hour'], timeSetting[empId][dateKey]['morningInSetting']['minute'], 00);
            }
        } else {
            start.setHours(startDateGetDiffDay.getHours(), startDateGetDiffDay.getMinutes());
            end.setHours(endDateGetDiffDay.getHours(), endDateGetDiffDay.getMinutes());
        }
        if (!parseInt(calculateFullDay)) {
            if (isHolidayOrWeekend(start)) {
                start.setHours(timeSetting[empId][dateKey]['morningInSetting']['hour'], timeSetting[empId][dateKey]['morningInSetting']['minute'], 00);
            }
            if (isHolidayOrWeekend(end)) {
                end.setHours(timeSetting[empId][dateKey]['afternoonOutSetting']['hour'], timeSetting[empId][dateKey]['afternoonOutSetting']['minute'], 00);
            }
        }
        timeDiff += getTimeDiffInRange(start, end, empId, dateKey);
    }
    if (registerBranch === '1') {
        if (parseFloat(timeDiff) < 0) {
            return 0;
        }
        return rounding(parseFloat(timeDiff), 2);
    } else {
        Math.round(1 * 2) / 2
        return rounding(parseFloat(Math.round(timeDiff * 2) / 2), 1);
    }
}

function getTimeDiffInRange(startDate, endDate, empId, dateKey) {
    var ONE_DAY = 1000 * 60 * 60 * 24;
    var s = new Date(startDate);
    var e = new Date(endDate);
    s.setHours(00, 00);
    e.setHours(00, 00);

    var numberDaysWeekend = getNumberDaysWeekend(new Date(startDate), new Date(endDate));

    var startDateTime = s.getTime();
    var endDateTime = e.getTime();

    var diffMS = endDateTime - startDateTime;
    var diffDays = Math.round((diffMS / ONE_DAY) * 100) / 100;
    if (CalculateFullDay) {
        numberDaysWeekend = 0;
    }

    diffDays = diffDays - numberDaysWeekend;

    if (diffDays < 0) {
        return 0;
    }

    var eveningBreak = getEveningBreak(diffDays, empId, dateKey);
    var lunchBreak = getLunchBreak(startDate, endDate, diffDays, empId, dateKey);
    var totalHoursBreak = eveningBreak + lunchBreak + 24 * numberDaysWeekend;
    var totalHoursReal = getTimeDiff(startDate, endDate) - totalHoursBreak;

    if (registerBranch === '1') {
        return parseFloat(rounding(totalHoursReal / getHoursWork(empId, dateKey), 2));
    } else {
        return parseFloat(rounding(totalHoursReal / getHoursWork(empId, dateKey), 1));
    }
}

function getHoursWork(empId, dateKey) {
    var startTime = new Date();
    var endTime = new Date();
    startTime.setHours(timeSetting[empId][dateKey]['morningInSetting']['hour'], timeSetting[empId][dateKey]['morningInSetting']['minute'], 00);
    endTime.setHours(timeSetting[empId][dateKey]['afternoonOutSetting']['hour'], timeSetting[empId][dateKey]['afternoonOutSetting']['minute'], 00);
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
    date.setHours(timeSetting[empId][dateKey]['afternoonOutSetting']['hour'], timeSetting[empId][dateKey]['afternoonOutSetting']['minute'], 00);
    nextDate.setHours(timeSetting[empId][dateKey]['morningInSetting']['hour'], timeSetting[empId][dateKey]['morningInSetting']['minute'], 00);

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
    startLunchdate.setHours(timeSetting[empId][dateKey]['morningOutSetting']['hour'], timeSetting[empId][dateKey]['morningOutSetting']['minute'], 00);
    endLunchdate.setHours(timeSetting[empId][dateKey]['afternoonInSetting']['hour'], timeSetting[empId][dateKey]['afternoonInSetting']['minute'], 00);

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
    fromDate = parseInt(fromDate.getTime() / 1000);
    toDate = parseInt(toDate.getTime() / 1000);
    return fromDate > toDate ? 0 : (toDate - fromDate) / 3600;
}

function getNumberDaysWeekend(startDate, endDate) {
    startDate.setHours(00, 00);
    endDate.setHours(00, 00);
    var yearCurrent = startDate.getFullYear();
    var numberDaysWeekend = 0;
    for (var date = startDate; date <= endDate; date.setDate(date.getDate() + 1)) {
        yearCurrent = date.getFullYear();
        var getTimeOfDate = date.getTime();

        if (isWeekend(date)) {
            var isCompensationDay = false;
            var compensationDaysLen = compensationDays.com.length;
            for (var i = 0; i < compensationDaysLen; i++) {
                dateCom = new Date(compensationDays.com[i]);
                dateCom.setHours(00, 00);
                getDateTimeCom = dateCom.getTime();
                if (getDateTimeCom == getTimeOfDate) {
                    isCompensationDay = true;
                    break;
                }
            }
            // if that day is not CompensationDay then it is weekend
            if (!isCompensationDay) {
                numberDaysWeekend++;
            }
        } else {
            var isHoliday = false;
            if (typeof arrAnnualHolidays == 'object') {
                var arrAnnualHolidaysLen = arrAnnualHolidays.length;
                for (var i = 0; i < arrAnnualHolidaysLen; i++) {
                    dateAnnual = new Date(yearCurrent + '-' + arrAnnualHolidays[i]);
                    dateAnnual.setHours(00, 00);
                    getDateTimeAnnual = dateAnnual.getTime();
                    if (getDateTimeAnnual == getTimeOfDate) {
                        isHoliday = true;
                        break;
                    }
                }
            }
            if (!isHoliday) {
                if (typeof arrSpecialHolidays == 'object') {
                    var arrSpecialHolidaysLen = arrSpecialHolidays.length;
                    for (var i = 0; i < arrSpecialHolidaysLen; i++) {
                        dateSpecial = new Date(arrSpecialHolidays[i]);
                        dateSpecial.setHours(00, 00);
                        getDateTimeSpecial = dateSpecial.getTime();
                        if (getDateTimeSpecial == getTimeOfDate) {
                            isHoliday = true;
                            break;
                        }
                    }
                }
            }
            if (isHoliday) {
                numberDaysWeekend++;
            }
        }
    }

    return numberDaysWeekend;
}

function checkFormLeaveDayRegister() {
    $('.managetime-error').hide();
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
        if (parseFloat(diffDate) <= 0) {
            $('#end_date_before_start_date-error').show();
            status = 0;
        } else {
            var registerId = $('#register_id').val();
            var employeeId = $('#employee_id').val();
            if ($('#employee_id').length) {
                if (employeeId != null) {
                    var isExistRegister = checkExistRegister($('#start_date').val(), $('#end_date').val(), registerId, employeeId);
                    if (isExistRegister) {
                        $('#register_exist_error').show();
                        status = 0;
                    }
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

    // Validate leave reason
    var reasonSelected = $('#reason').find(":selected");
    var reasonCode = reasonSelected.attr("data-reason-code");
    var numberDaysOff = $('#number_days_off').val();
    if (reasonCode == USED_LEAVE_DAY) {
        var numberDaysRemain = $('#number_days_remain').val();
        var numberDaysUnapprove = $('#number_unapprove').val();

        if (parseFloat(numberDaysOff) - parseFloat(oldNumberDaysOff) > parseFloat(numberDaysRemain) - parseFloat(numberDaysUnapprove)) {
            $('#number_days_off-error').show();
            status = 0;
        }

    }

    // validate leave special reason
    if (reasonSelected.data('type') === leaveSpecialType) {
        if (numberDaysOff > reasonSelected.data('value')) {
            $('#reason_special_value-error').show();
            status = 0;
        } else {
            if (parseInt(reasonSelected.data('repeated')) > 0) {
                var registerId = $('#register_id').val();
                var employeeId = $('#employee_id').val();
                if ($('#employee_id').length) {
                    if (employeeId != null) {
                        var isExistRegisterType = checkExistRegisterType($('#start_date').val(), registerId, employeeId);
                        if (isExistRegisterType.exist) {
                            $('#register_type_exist_error').text(isExistRegisterType.message)
                            $('#register_type_exist_error').show();
                            status = 0;
                        }
                    }
                }
            }
        }
    }

    var reasonRegister = $('#reason').val().trim();
    if (reasonRegister == '') {
        $('#reason-error').show();
        status = 0;
    }

    var teamCodePreOfEmp = $('#team_code_pre_of_emp').val().trim();
    var codePrefixJp = $('#code_prefix_jp').val().trim();
    var reason = $('#reason').val().trim();
    var reasonPaidLeaveJp = $('#reason_paid_leave_jp').val().trim();
    // if user type japan
    if (teamCodePreOfEmp == codePrefixJp) {
        // if leave day reason not paid leave
        if (reason != reasonPaidLeaveJp) {
            var note = $('#note').val().trim();
            if (note == '') {
                $('#note-error').show();
                status = 0;
            }
        }
    } else {
        var note = $('#note').val().trim();
        if (note == '') {
            $('#note-error').show();
            status = 0;
        }
    }

    if (status == 0) {
        return false;
    }

    return true;
}

/**
 * Check register before offcial date
 *
 * @returns {Boolean} true if register before offcial date
 */
function checkRegisterBeforeOffcial() {
    if (!offcialDate) {
        return true;
    }
    var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
    return new Date(startDate).getTime() < new Date(offcialDate).getTime();
}

function checkExistRegister(startDate, endDate, registerId, employeeId) {
    var isExistRegister = false;
    if (typeof registerId == 'undefined') {
        registerId = null;
    }
    if (typeof employeeId == 'undefined') {
        employeeId = null;
    }
    $.ajax({
        type: "GET",
        url: urlCheckRegisterExist,
        data: {
            registerId: registerId,
            startDate: startDate,
            endDate: endDate,
            employeeId: employeeId,
        },
        async: false,
        success: function(result) {
            isExistRegister = result;
        }
    });
    return isExistRegister;
}

function checkExistRegisterType(startDate, registerId, employeeId) {
    var isExistRegister = false;
    if (typeof registerId == 'undefined') {
        registerId = null;
    }
    if (typeof employeeId == 'undefined') {
        employeeId = null;
    }
    $.ajax({
        type: "get",
        url: urlCheckRegisterTypeExist,
        data: {
            registerId: registerId,
            startDate: startDate,
            employeeId: employeeId,
            reasonId: $('#reason').val(),
        },
        async: false,
        success: function(result) {
            isExistRegister = result;
        }
    });
    return isExistRegister;
}

function get2Digis(num) {
    return (num < 10 ? '0' : '') + num;
}

// ===== 1/2 and 1/4 ====
function setTimeStart(startTime, dateKey, empId, elem) {
    var timeQuater = timeWorkingQuater[empId][dateKey];
    var stTime = getStringTime(startTime);
    if (!timeQuater['timeIn'].includes(stTime)) {
        var arrTime = timeQuater['timeIn'][0].split(':');
        startTime.setHours(arrTime[0], arrTime[1]);
        $(elem).data('DateTimePicker').date(startTime);
        // $(id + 'a[data-action=increase-hour]').css('opacity', 1);
        // $(id + 'a[data-action=decrease-hour]').css('opacity', 0);
    }
}

function setTimeEnd(endTime, dateKey, empId, elem) {
    var timeQuater = timeWorkingQuater[empId][dateKey];
    var stTime = getStringTime(endTime);
    if (!timeQuater['timeOut'].includes(stTime)) {
        var arrTime = timeQuater['timeOut'][timeQuater['timeOut'].length - 1].split(':');
        endTime.setHours(arrTime[0], arrTime[1]);
        $(elem).data('DateTimePicker').date(endTime);
    }
}

function increase(startTime, dateKey, empId, elem, category) {
    var timeQuater = timeWorkingQuater[empId][dateKey];
    var arrTime = [];
    if (timeQuater[category].length == 2) {
        arrTime = timeQuater['timeIn'][1].split(':');
    } else {
        var stTime = getStringTime(startTime);
        switch (getArrayKey(timeQuater[category], stTime)) {
            case 0:
                arrTime = timeQuater[category][1].split(':');
                break;
            case 1:
                arrTime = timeQuater[category][2].split(':');
                break;
            case 2:
                arrTime = timeQuater[category][3].split(':');
                break;
            default:
                arrTime = timeQuater[category][0].split(':');
                break;
        }
    }
    hour = arrTime[0];
    minute = arrTime[1];
    startTime.setHours(hour, minute);
    $(elem).data('DateTimePicker').date(startTime);
}

function decrease(startTime, dateKey, empId, elem, category) {
    var timeQuater = timeWorkingQuater[empId][dateKey];
    var arrTime = [];
    if (timeQuater[category].length == 2) {
        arrTime = timeQuater[category][0].split(':');
    } else {
        var stTime = getStringTime(startTime);
        switch (getArrayKey(timeQuater[category], stTime)) {
            case 3:
                arrTime = timeQuater[category][2].split(':');
                break;
            case 2:
                arrTime = timeQuater[category][1].split(':');
                break;
            case 1:
                arrTime = timeQuater[category][0].split(':');
                break;
            default:
                arrTime = timeQuater[category][3].split(':');
                break;
        }
    }
    hour = arrTime[0];
    minute = arrTime[1];
    startTime.setHours(hour, minute);
    $(elem).data('DateTimePicker').date(startTime);
}

/**
 * get key array by value
 */
function getArrayKey(array, value) {
    var n = array.length;
    var i = 0;
    for (i; i < n; i++) {
        if (array[i] == value) {
            return i;
        }
    }
    return -1;
}

/**
 * convert time to string
 * @param  {[object]} time
 * @return {[string]}
 */
function getStringTime(time) {
    var hour = (time.getHours() < 10 ? '0' : '') + time.getHours();
    var minute = (time.getMinutes() < 10 ? '0' : '') + time.getMinutes();
    return hour + ':' + minute;
}

/**
 * convert date to string
 * @param  {[object]} time
 * @return {[string]}
 */
function getStringDate(date) {
    var year = (date.getFullYear() < 10 ? '0' : '') + date.getFullYear();
    var month = ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1);
    return year + '-' + month;
}

/**
 * convert date to string
 * @param  {[object]} time
 * @return {[string]}
 */
function getStrDate(date) {
    var year = (date.getFullYear() < 10 ? '0' : '') + date.getFullYear();
    var month = ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1);
    var day = ((date.getDate()) < 10 ? '0' : '') + date.getDate();
    return year + '-' + month + '-' + day;
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

/**
 * update data time setting and time working quarter of employee
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
    var dateArray = dateRange(startDate, endDate);
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
            async: false,
            data: {
                period: { start_date: startDate, end_date: endDate },
                empId: employeeId,
                _token: token,
            },
            success: function(response) {
                var empWorkingTimes = response['timeWorking'][employeeId];
                var empTimeWorkingQuarters = response['timeQuater'][employeeId];
                Object.keys(empWorkingTimes).forEach(function(date) {
                    timeSetting[employeeId][date] = empWorkingTimes[date];
                    timeWorkingQuater[employeeId][date] = empTimeWorkingQuarters[date];
                });
            }
        });
    }
}