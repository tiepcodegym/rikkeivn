// Wait for window load
$(window).load(function() {
    // Animate loader off screen
    $(".se-pre-con").fadeOut("slow");;
});

$(function() {
    optionDatePickerStart = {
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true
    };

    optionDatePickerEnd = {
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true,
        startDate: startDateDefault
    };

    $('#datetimepicker-start-date').datepicker(optionDatePickerStart).on('changeDate', function(selected) {
        startDate = new Date(selected.date.valueOf());
        startDate.setDate(startDate.getDate(new Date(selected.date.valueOf())));

        startDate = $('#datetimepicker-start-date').datepicker("getDate");
        endDate = $('#datetimepicker-end-date').datepicker("getDate");
        if (startDate !== null && endDate !== null) {
            startDate_ms = startDate.getTime();
            endDate_ms = endDate.getTime();
            if (startDate_ms > endDate_ms) {
                $('#datetimepicker-end-date').datepicker('update', startDate);
            }
        }
        $('#datetimepicker-end-date').datepicker('setStartDate', startDate);
        hideDaysOfWeek();

        $('#start_date-error').hide();
        $('#register_exist_error').hide();

        startDate = $('#datetimepicker-start-date').datepicker("getDate");
        endDate = $('#datetimepicker-end-date').datepicker("getDate");
        showCheckDayApply(startDate, endDate);
    }).on('clearDate', function() {
        $('#datetimepicker-end-date').datepicker('setStartDate', '');
        $('#register_exist_error').hide();
        hideDaysOfWeek();

        checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            $('#start_date-error').show();
        }
    });

    $('#datetimepicker-end-date').datepicker(optionDatePickerEnd).on('changeDate', function(selected) {
        startDate = $('#datetimepicker-start-date').datepicker("getDate");
        endDate = $('#datetimepicker-end-date').datepicker("getDate");
        
        $('#end_date-error').hide();
        $('#register_exist_error').hide();

        showCheckDayApply(startDate, endDate);
    }).on('clearDate', function() {
        hideDaysOfWeek();
        $('#register_exist_error').hide();

        checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            $('#end_date-error').show();
        }
    });

    $('#select_all_day input').on('ifClicked', function(event) {
        checked = event.target.checked;
        if (checked) {
            $('#monday, #tuesday, #wednesday, #thursday, #friday').iCheck('uncheck');
        } else {
            $('#monday, #tuesday, #wednesday, #thursday, #friday').iCheck('check');
        }
    });

    $('#monday input').on('ifChanged', function(event) {
        checked = event.target.checked;

        if (checked) {
            count_checkbox_show = countCheckboxShow();
            count_checkbox_checked = countCheckboxChecked();

            if (count_checkbox_show == count_checkbox_checked) {
                $('#select_all_day').iCheck('check');
            }
        } else {
            $('#select_all_day').iCheck('uncheck');
        }
    });

    $('#tuesday input').on('ifChanged', function(event) {
        checked = event.target.checked;

        if (checked) {
            count_checkbox_show = countCheckboxShow();
            count_checkbox_checked = countCheckboxChecked();

            if (count_checkbox_show == count_checkbox_checked) {
                $('#select_all_day').iCheck('check');
            }
        } else {
            $('#select_all_day').iCheck('uncheck');
        }
    });

    $('#wednesday input').on('ifChanged', function(event) {
        checked = event.target.checked;

        if (checked) {
            count_checkbox_show = countCheckboxShow();
            count_checkbox_checked = countCheckboxChecked();

            if (count_checkbox_show == count_checkbox_checked) {
                $('#select_all_day').iCheck('check');
            }
        } else {
            $('#select_all_day').iCheck('uncheck');
        }
    });

    $('#thursday input').on('ifChanged', function(event) {
        checked = event.target.checked;

        if (checked) {
            count_checkbox_show = countCheckboxShow();
            count_checkbox_checked = countCheckboxChecked();

            if (count_checkbox_show == count_checkbox_checked) {
                $('#select_all_day').iCheck('check');
            }
        } else {
            $('#select_all_day').iCheck('uncheck');
        }
    });

    $('#friday input').on('ifChanged', function(event) {
        checked = event.target.checked;

        if (checked) {
            count_checkbox_show = countCheckboxShow();
            count_checkbox_checked = countCheckboxChecked();

            if (count_checkbox_show == count_checkbox_checked) {
                $('#select_all_day').iCheck('check');
            }
        } else {
            $('#select_all_day').iCheck('uncheck');
        }
    });

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

    //iCheck for checkbox and radio inputs
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_minimal-blue'
    });

    $("#approver").select2().on('select2:select', function (evt) {
        $('#approver-error').hide();
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

    $('.manage-time').keyup(function() {
        checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            lateStartShift = $('#late_start_shift').val().trim();
            earlyMidShift = $('#early_mid_shift').val().trim();
            lateMidShift = $('#late_mid_shift').val().trim();
            earlyEndShift = $('#early_end_shift').val().trim();

            if (lateStartShift == '' && earlyMidShift == '' && lateMidShift == '' && earlyEndShift == '') {
                $('#error-time').show();
            } else {
                $('#error-time').hide();
            }
        }
    });

    $('#reason').keyup(function() {
        var checkSubmit = $('#check_submit').val();
        if (checkSubmit == 1) {
            var reasonRegister = $('#reason').val().trim();

            if (reasonRegister == '') {
                $('#reason-error').show();
            } else {
                $('#reason-error').hide();
            }
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
        var personList = $(".select-search-employee").val();
        var $this = $(this);
        $this.button('loading');

        $.ajax({
            type: "GET",
            url: urlApprove,
            data : { 
                registerId: registerId,
                urlCurrent: urlCurrent,
                personList: personList
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
        var personList = $(".select-search-employee").val();
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
                personList: personList,
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
});

function loadEditGetDaysApply()
{
    var startDate = new Date(startDateDefault);
    var endDate = new Date(endDateDefault);

    var ONE_DAY = 1000 * 60 * 60 * 24;

    var startDate_ms = startDate.getTime();
    var endDate_ms = endDate.getTime();

    var difference_ms = Math.abs(startDate_ms - endDate_ms);
    var difference_day = Math.round(difference_ms/ONE_DAY);

    var totalDaysApply = countDaysApply -1;

    var countDays = 0;

    if (startDate.getDay() > endDate.getDay()) {
        for (i = 1; i <= endDate.getDay(); i++) {
            countDays++;
        }

        for (i = startDate.getDay(); i <= 5; i++) {
            countDays++;
        }
    }

    if (startDate.getDay() < endDate.getDay()) {
        for (i = startDate.getDay(); i <= endDate.getDay(); i++) {
            if (i != 6) {
                countDays++;
            }
        }
    }

    if (countDays == countDaysApply) {
        $('#select_all_day').iCheck('check');
    }

    if (totalDaysApply == 4) {
        $('#select_all_day').iCheck('check');
    } else if (totalDaysApply == difference_day) {
        $('#select_all_day').iCheck('check');
    }

    if ((difference_day == 0) || (difference_day == 1 && startDate.getDay() == 6 && endDate.getDay() == 0)) {
        hideDaysOfWeek();
    } else if (0 < difference_day && difference_day < 6) {
        $('.col-monday').hide();
        $('.col-tuesday').hide();
        $('.col-wednesday').hide();
        $('.col-thursday').hide();
        $('.col-friday').hide();
        $('.col-all-day').show();

        $('#all_day_hidden').val('');

        var all_day_hidden = [];

        if (startDate.getDay() < endDate.getDay()) {
            for (i = startDate.getDay(); i <= endDate.getDay(); i++) {
                getDay = checkDays(i);

                if (getDay != null) {
                    all_day_hidden.push(getDay);
                }
            }

            $('#all_day_hidden').val(all_day_hidden.join(", "));
        } else if (startDate.getDay() > endDate.getDay()) {
            for (i = 0; i <= endDate.getDay(); i++) {
                getDay = checkDays(i);

                if (getDay != null) {
                    all_day_hidden.push(getDay);
                }
            }

            for (i = startDate.getDay(); i <= 6; i++) {
                getDay = checkDays(i);

                if (getDay != null) {
                    all_day_hidden.push(getDay);
                }
            }

            $('#all_day_hidden').val(all_day_hidden.join(", "));
        }
    } else {
        showDaysOfWeek();
    }
}

function hideDaysOfWeek()
{
    // Hide days of week
    $('.col-monday').hide();
    $('.col-tuesday').hide();
    $('.col-wednesday').hide();
    $('.col-thursday').hide();
    $('.col-friday').hide();
    $('.col-all-day').hide();

    $('#select_all_day, #monday, #tuesday, #wednesday, #thursday, #friday').iCheck('uncheck');

    $('#all_day_hidden').val('');
}

function showDaysOfWeek()
{
    // Show days of week
    $('.col-monday').show();
    $('.col-tuesday').show();
    $('.col-wednesday').show();
    $('.col-thursday').show();
    $('.col-friday').show();
    $('.col-all-day').show();
    
    var all_day_hidden = ['1', '2', '3', '4', '5'];
    $('#all_day_hidden').val(all_day_hidden.join(', '));
}

function checkDays(day)
{
    monday = $("#monday input[type='checkbox']").val();
    tuesday = $("#tuesday input[type='checkbox']").val();
    wednesday = $("#wednesday input[type='checkbox']").val();
    thursday = $("#thursday input[type='checkbox']").val();
    friday = $("#friday input[type='checkbox']").val();

    getDay = null;

    if (day == monday) {
        $('.col-monday').show();
        getDay = '1';
    } 
    if (day == tuesday) {
        $('.col-tuesday').show();
        getDay = '2';
    } 
    if (day == wednesday) {
        $('.col-wednesday').show();
        getDay = '3';
    } 
    if (day == thursday) {
        $('.col-thursday').show();
        getDay = '4';
    } 
    if (day == friday) {
        $('.col-friday').show();
        getDay = '5';
    }

    return getDay;
}

function countCheckboxShow()
{
    count_checkbox_show = 0;

    if ($('.col-monday').is(':visible')) {
        count_checkbox_show++;
    }
    if ($('.col-tuesday').is(':visible')) {
        count_checkbox_show++;
    }
    if ($('.col-wednesday').is(':visible')) {
        count_checkbox_show++;
    }
    if ($('.col-thursday').is(':visible')) {
        count_checkbox_show++;
    }
    if ($('.col-friday').is(':visible')) {
        count_checkbox_show++;
    }

    return count_checkbox_show;
}

function countCheckboxChecked()
{
    count_checkbox_checked = 0;

    if ($('.col-monday').is(':visible')) {
        if ($("#monday input[type='checkbox']").prop('checked')) {
            count_checkbox_checked++;
        }
    }

    if ($('.col-tuesday').is(':visible')) {
        if ($("#tuesday input[type='checkbox']").prop('checked')) {
            count_checkbox_checked++;
        }
    }

    if ($('.col-wednesday').is(':visible')) {
        if ($("#wednesday input[type='checkbox']").prop('checked')) {
            count_checkbox_checked++;
        }
    }

    if ($('.col-thursday').is(':visible')) {
        if ($("#thursday input[type='checkbox']").prop('checked')) {
            count_checkbox_checked++;
        }
    }

    if ($('.col-friday').is(':visible')) {
        if ($("#friday input[type='checkbox']").prop('checked')) {
            count_checkbox_checked++;
        }
    }

    return count_checkbox_checked;
}

function checkFormComelateRegister()
{   
    var checkStatus = $('#check_status').val();
    if (checkStatus == 4) {
        $('#show_notification').text(notificationStatusApproved);
        $('#modal_allow_edit').modal('show');
        return false;
    }
    if (checkStatus == 1) {
        $('#show_notification').text(notificationStatusCanceled);
        $('#modal_allow_edit').modal('show');
        return false;
    }

    status = 1;
    $('#check_submit').val(1);

    startDate = $('#datetimepicker-start-date').datepicker("getDate");
    if (startDate == null) {
        $('#start_date-error').show();
        status = 0;
    }

    endDate = $('#datetimepicker-end-date').datepicker("getDate");
    if (endDate == null) {
        $('#end_date-error').show();
        status = 0;
    }

    if (startDate != null && endDate != null) {
        var registerId = $('#register_id').val();
        var isExistRegister = checkExistRegister($('#start_date').val(), $('#end_date').val(), registerId);
        if (isExistRegister) {
            $('#register_exist_error').show();
            status = 0;
        }
    }

    approver = $('#approver').val();
    if (approver == null) {
        $('#approver-error').show();
        status = 0;
    }

    lateStartShift = $('#late_start_shift').val().trim();
    earlyMidShift = $('#early_mid_shift').val().trim();
    lateMidShift = $('#late_mid_shift').val().trim();
    earlyEndShift = $('#early_end_shift').val().trim();

    if ((lateStartShift == '' && earlyMidShift == '' && lateMidShift == '' && earlyEndShift == '') || (lateStartShift == 0 && earlyMidShift == 0 && lateMidShift == 0 && earlyEndShift == 0)) {
        $('#error-time').show();
        status = 0;
    }

    reasonRegister = $('#reason').val().trim();
    if (reasonRegister == '') {
        $('#reason-error').show();
        status = 0;
    }

    if (status == 0) {
        return false;
    } 

    return true;
}

function checkExistRegister(startDate, endDate, registerId) {
    var isExistRegister = false;
    if (typeof registerId == 'undefined') {
        registerId = null;
    }
    $.ajax({
        type: "GET",
        url: urlCheckRegisterExist,
        data: {
            registerId: registerId,
            startDate: startDate,
            endDate: endDate
        },
        async: false,
        success: function (result) {
            isExistRegister = result;
        }
    });
    return isExistRegister;
}

function showCheckDayApply(start, end) {
    if (start !== null && end !== null) {
        ONE_DAY = 1000 * 60 * 60 * 24;

        startDate_ms = start.getTime();
        endDate_ms = end.getTime();

        difference_ms = Math.abs(startDate_ms - endDate_ms);
        difference_day = Math.round(difference_ms/ONE_DAY);

        if ((difference_day === 0) || (difference_day === 1 && start.getDay() === 6 && end.getDay() === 0)) {
            hideDaysOfWeek();
        } else if (0 < difference_day && difference_day < 6) {
            $('.col-monday').hide();
            $('.col-tuesday').hide();
            $('.col-wednesday').hide();
            $('.col-thursday').hide();
            $('.col-friday').hide();
            $('.col-all-day').show();

            $('#select_all_day, #monday, #tuesday, #wednesday, #thursday, #friday').iCheck('uncheck');

            $('#all_day_hidden').val('');

            var all_day_hidden = [];

            if (start.getDay() < end.getDay()) {
                for (i = start.getDay(); i <= end.getDay(); i++) {
                    getDay = checkDays(i);

                    if (getDay !== null) {
                        all_day_hidden.push(getDay);
                    }
                }

                $('#all_day_hidden').val(all_day_hidden.join(", "));
            } else if (start.getDay() > end.getDay()) {
                for (i = 0; i <= end.getDay(); i++) {
                    getDay = checkDays(i);

                    if (getDay !== null) {
                        all_day_hidden.push(getDay);
                    }
                }

                for (i = start.getDay(); i <= 6; i++) {
                    getDay = checkDays(i);

                    if (getDay !== null) {
                        all_day_hidden.push(getDay);
                    }
                }

                $('#all_day_hidden').val(all_day_hidden.join(", "));
            }
        } else {
            showDaysOfWeek();
        }
        
        $('#select_all_day, #monday, #tuesday, #wednesday, #thursday, #friday').iCheck('enable');
        $('#select_all_day, #monday, #tuesday, #wednesday, #thursday, #friday').iCheck('check');
    }
}