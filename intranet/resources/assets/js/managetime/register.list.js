$(function() {
    var fixTop = $('#position_start_header_fixed').offset().top;
    $(window).scroll(function() {
        var scrollTop = $(window).scrollTop();
        if (scrollTop > fixTop) {
            $('#managetime_table_fixed').css('top', scrollTop - $('.table-responsive').offset().top + 52);
            $('#managetime_table_fixed').show();
        } else {
            $('#managetime_table_fixed').hide();
        }
    });
    
    $('#button_delete_submit').click(function() {
        var registerId = $('#register_id_delete').val();
        var urlCurrent = window.location.href.substr(window.location.href);
        var $this = $(this);
        $this.button('loading');

        $.ajax({
            type: "GET",
            url: urlDelete,
            data : { 
                registerId: registerId,
                urlCurrent: urlCurrent
            },
            success: function (result) {
                $('#modal_delete').modal('hide');
                var data = JSON.parse(result);
                window.location = data.url;
            }
        });
    });  

    $('.button-delete').click(function() {
        var checkStatus = $(this).attr('data-status');
        if(checkStatus == statusApproved)
        {   
            $('#show_notification').text(contentDisallowDelete);
            $('#modal_allow_edit').modal('show');
            return false;
        }

        $('#register_id_delete').val($(this).val());
        $('#modal_delete').modal('show');
    });
});

function getNumberDaysWeekend(startDate, endDate) {
    startDate.setHours(00, 00);
    endDate.setHours(00, 00);
    var yearCurrent = startDate.getFullYear();
    var numberDaysWeekend = 0;
    for (var date = startDate; date <= endDate; date.setDate(date.getDate() + 1)) {
        var daysOfWeek = date.getDay();
        yearCurrent = date.getFullYear();
        var getTimeOfDate = date.getTime();
        if (daysOfWeek == 0 || daysOfWeek == 6) {
            numberDaysWeekend++;
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

function getDaysTimekeeping(startDate, endDate) {
    var ONE_DAY = 1000 * 60 * 60 * 24;

    var startDateGetDiffDay = new Date(startDate);
    var endDateGetDiffDay = new Date(endDate);
    var dateOfWeekStart = startDateGetDiffDay.getDay();
    var dateOfWeekEnd = endDateGetDiffDay.getDay();
    var numberDaysWeekend = getNumberDaysWeekend(new Date(startDate), new Date(endDate));

    startDateGetDiffDay.setHours(00, 00);
    endDateGetDiffDay.setHours(00, 00);

    startDateGetDiffDay = startDateGetDiffDay.getTime();
    endDateGetDiffDay = endDateGetDiffDay.getTime();

    var diffMS = endDateGetDiffDay - startDateGetDiffDay;
    var diffDays = Math.round((diffMS / ONE_DAY) * 100)/100;
    diffDays = diffDays - numberDaysWeekend;

    var startDateGetTimekeeping = new Date(startDate);
    var endDateGetTimekeeping = new Date(endDate);
    var hourStart = startDateGetTimekeeping.getHours();
    var minuteStart = startDateGetTimekeeping.getMinutes();

    var hourEnd = endDateGetTimekeeping.getHours();
    var minuteEnd = endDateGetTimekeeping.getMinutes();

    if (diffDays < 0) {
        return 0;
    } else if (diffDays == 0) {
        if (dateOfWeekStart == 0 || dateOfWeekStart == 6) {
            hourStart = 8;
            minuteStart = 0;
        } else {
            if (hourStart < 8) {
                hourStart = 8;
                minuteStart = 0;
            } else if (hourStart == 12 || (hourStart == 13 && minuteStart <= 30)) {
                hourStart = 13;
                minuteStart = 30;
            }
        }
    } else {
        if (dateOfWeekStart == 0 || dateOfWeekStart == 6) {
            hourStart = 8;
            minuteStart = 0;
        } else {
            if (hourStart < 8 ) {
                hourStart = 8;
                minuteStart = 0;
            } else if (hourStart == 12 || (hourStart == 13 && minuteStart <= 30)) {
                hourStart = 13;
                minuteStart = 30;
            } else if (hourStart > 17 ||  (hourStart == 17 && minuteStart >= 30)) {
                hourStart = 8;
                minuteStart = 0;
                diffDays = diffDays - 1;
            }
        }
    }

    var diffHours = 0;
    var diffMinutes = 0;

    if (hourStart >= 8 && hourStart <= 12) {
        if (hourEnd <= 12) {
            diffHours = hourEnd - hourStart;
            diffMinutes = minuteEnd - minuteStart;
        } else {
            diffHours = (12 - hourStart) + (hourEnd - 13);
            diffMinutes = (0 - minuteStart) + (minuteEnd - 30);
        }
    } else if (hourStart > 12 && hourEnd <= 12 && diffDays > 0) {
            diffHours = (17 - hourStart) + (hourEnd - 8);
            diffMinutes = (30 - minuteStart) + minuteEnd;
            diffDays = diffDays - 1;
    } else {
        diffHours = hourEnd - hourStart;
        diffMinutes = minuteEnd - minuteStart;
    }

    var timeWorkingDaily = 480; // Minutes
    var totalDiffMinutes = diffHours * 60 + diffMinutes;
    var numberDaysTimekeeping = diffDays + Math.round((totalDiffMinutes / timeWorkingDaily) * 100)/100;

    return numberDaysTimekeeping;
}