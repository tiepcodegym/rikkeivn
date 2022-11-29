
$('#modal-calendar-create .start-date').datetimepicker({
    format: 'YYYY-MM-DD H:mm',
}).on('dp.change', function () {
    if (jQuery(this).val()) {
        $('.end-date').data('DateTimePicker').minDate(jQuery(this).val());
    } else {
        $('.end-date').data('DateTimePicker').minDate('2000-01-01');
    }

    checkRoomAvailable();
});

$('#modal-calendar-create .end-date').datetimepicker({
    format: 'YYYY-MM-DD H:mm',
}).on('dp.change', function () {
    if (jQuery(this).val()) {
        $('.start-date').data('DateTimePicker').maxDate(jQuery(this).val());
    } else {
        $('.start-date').data('DateTimePicker').maxDate('2099-01-01');
    }

    checkRoomAvailable();
});

/*
 * google login popup 
 * @type @exp;window@call;open
 */
var newWindow = null;

/*
 * store url authentication google account
 * @type string
 */
var auth_url = null;

/*
 * The `run` variable used to check after closing google login popup 
 * re-run the showCalendars() function to generate data
 * @type boolean, default value is false
 */
var run = false;

/*
 * Store roomId selected when booked a room
 * using when show form update calendar event
 *
 * @type string|null
 */
var roomSelected = null;

/**
 * Get form Google calendar
 *
 * @param {boolean} isFirst
 * @returns {void}
 */
function showCalendars(isFirst) {
    if (isFirst) {
        newWindow = window.open('', '_blank', 'width=500,height=500');
    }

    var modalFormCalendar = $('#modal-calendar-create');
    modalFormCalendar.find('.loader-container').removeClass('hidden');
    modalFormCalendar.find('.form-calendar').addClass('hidden');
    modalFormCalendar.find('.error').addClass('hidden');
    $('.btn-create-calendar').addClass('hidden');
    $('.btn-cancel-create_calendar').addClass('hidden');
    $.ajax({
        url: urlGetFormCalendar,
        type: 'GET',
        data: {
            interviewerIds: $('#interviewer').val(),
            calendarId: calendarId,
            eventId: eventId,
            candidateId: $('input[name=candidate_id]').val(),
        },
        dataType: 'json',
        success: function(res) {
            if (parseInt(res['success']) === 1) {
                modalFormCalendar.find('.loader-container').addClass('hidden');

                //if not creator show error
                if (!res['isCreator']) {
                    getCalendarErrorHandle(modalFormCalendar, errorNotCreatorText);
                    return false;
                }

                modalFormCalendar.find('.form-calendar').removeClass('hidden');

                //Set value to form
                $('.start-date').data("DateTimePicker").date(res['minDate']);
                $('.end-date').data("DateTimePicker").date(res['maxDate']);
                $('#calendar-title').val(res['title']);
                $('#calendar-description').val(res['description']);
                roomSelected = res['roomId'];

                closeWindow(newWindow);

                //set interviewers
                var optEmp = '';
                if (res['interviewers'] !== null && res['interviewers'].length > 0) {
                    $.each(res['interviewers'], function (key, emp) {
                        optEmp += '<option value="' + emp.id + '" selected>' + emp.email.substring(0, emp.email.lastIndexOf("@")) + '</option>';
                    });
                }
                $('#calendar-interviewer').html(optEmp);
                select2Interviewers('#calendar-interviewer');

                if (res['notFound']) {
                    $('#modal-calendar-create .alert-warning').removeClass('hidden');
                    $('#modal-calendar-create .alert-warning .content').html(notFoundEventText);
                }

                $('.btn-create-calendar').removeClass('hidden');
                $('.btn-cancel-create_calendar').removeClass('hidden');

                setTimeout(function() {
                    if (!$('#modal-calendar-create .alert-warning').hasClass('hidden')) {
                        $('#modal-calendar-create .alert-warning').addClass('hidden')
                    }
                }, 3000);
            } else {
                if (isFirst) {
                    newWindow.location.href = res['auth_url'];
                    run = true;
                } else {
                    getCalendarErrorHandle(modalFormCalendar, errorOccurText);
                }
            }
        },
        error: function () {
            getCalendarErrorHandle(modalFormCalendar, errorOccurText);
        },
    });
}

$('#modal-calendar-create .start-date, #modal-calendar-create .end-date')

var xhr = null;

/**
 * Get rooms list
 *
 * @returns {void}
 */
function checkRoomAvailable() {
    var modalFormCalendar = $('#modal-calendar-create');
    var minDate = modalFormCalendar.find('.start-date').val();
    var maxDate = modalFormCalendar.find('.end-date').val();
    var calendarSelect = modalFormCalendar.find('#calendar-room');
    calendarSelect.html('<option value="0">' + selectMeetingRoomText + '</option>');
    if (!minDate || !maxDate) {
        return false;
    }

    calendarSelect.prop('disabled', true);
    calendarSelect.select2();
    $('.loading-room').removeClass('hidden');

    xhr = $.ajax({
        url: urlCheckRoomAvailable,
        type: 'POST',
        data: {
            'minDate': minDate,
            'maxDate': maxDate,
        },
        dataType: 'html',
        beforeSend : function() {
            if (xhr !== null) {
                xhr.abort();
            }
        },
        success: function(res) {
            calendarSelect.html(res);
            calendarSelect.prop('disabled', false);
            $('.loading-room').addClass('hidden');

            //if is update, selected old room
            if (roomSelected != null) {
                calendarSelect.find('option[value="'+roomSelected+'"]').prop('disabled', false);
                calendarSelect.val(roomSelected);
            }
            calendarSelect.select2({
                minimumResultsForSearch: -1
            });
        },
        error: function () {
        },
    });
}

/**
 * Using when get Calendar data error
 *
 * @param {dom} modalFormCalendar
 * @returns {void}
 */
function getCalendarErrorHandle(modalFormCalendar, errorTexxt) {
    modalFormCalendar.find('.loader-container').addClass('hidden');
    modalFormCalendar.find('.error').removeClass('hidden');
    modalFormCalendar.find('.error h4').html(errorTexxt);
    modalFormCalendar.find('.btn-cancel-create_calendar').removeClass('hidden');
    closeWindow(newWindow);
}

/**
 * Close google login popup
 *
 * @param {window} newWindow
 * @returns {void}
 */
function closeWindow(newWindow) {
    if (newWindow !== null) {
        newWindow.close();
        newWindow = null;
    }
}

setInterval(function() {
    if (run && newWindow != null && newWindow.closed) {
        showCalendars();
        newWindow = null;
        run = false;
    }
}, 1000);

/**
 * Create/update a new Calendar event
 *
 * @returns {void}
 */
function saveCalendar(element) {
    var form = $('.form-calendar');
    var buttonCreate = $(element);
    var buttonCreateFake = $('.btn-create-calendar-fake');
    buttonCreate.addClass('hidden');
    buttonCreateFake.removeClass('hidden');
    $.ajax({
        url: urlsaveCalendars,
        type: 'POST',
        data: {
            'title': form.find('#calendar-title').val(),
            'description': form.find('#calendar-description').val(),
            'startDate' : form.find('.start-date').val(),
            'endDate' : form.find('.end-date').val(),
            'roomId': form.find('#calendar-room').val(),
            'location': form.find('#calendar-room option:selected').text(),
            'attendeesId': form.find('#calendar-interviewer').val(),
            'candidateId': $('input[name=candidate_id]').val(),
            'calendarId': calendarId,
            'eventId': eventId,
        },
        dataType: 'json',
        success: function(res) {
            if (parseInt(res['success']) === 1) {
                var optEmp = '';
                if (res['interviewers'].length > 0) {
                    $.each(res['interviewers'], function (key, emp) {
                        optEmp += '<option value="' + emp.id + '" selected>' + emp.email.substring(0, emp.email.lastIndexOf("@")) + '</option>';
                    });
                }
                $('#interviewer').html(optEmp);
                select2Interviewers('#interviewer');
                $('#form-interview-candidate').submit();
            } else {
                $('#modal-calendar-create .alert-warning').removeClass('hidden');
                $('#modal-calendar-create .alert-warning .content').html(errorOccurText);
            }
        },
        error: function () {
        },
        complete: function () {
            buttonCreate.removeClass('hidden');
            buttonCreateFake.addClass('hidden');
        },
    });
}

/**
 * Validate calendar form
 * @returns {Boolean}
 */
function isInvalidCalendarForm() {
    var form = $('.form-calendar');
    var calendarTitleDom = form.find('#calendar-title');
    var startDateDom = form.find('.start-date');
    var endDateDom = form.find('.end-date');
    var calendarRoomDom = form.find('#calendar-room');
    var calendarInterviewDom = form.find('#calendar-interviewer');

    var title = calendarTitleDom.val().trim();
    var startDate = startDateDom.val().trim();
    var endDate = endDateDom.val().trim();
    var roomId = calendarRoomDom.val();
    var attendeesId = calendarInterviewDom.val();

    if (!title) {
        addClassRequired(calendarTitleDom);
    } else {
        removeClassRequired(calendarTitleDom);
    }

    if (!startDate) {
        addClassRequired(startDateDom);
    } else {
        removeClassRequired(startDateDom);
    }

    if (!endDate) {
        addClassRequired(endDateDom);
    } else {
        removeClassRequired(endDateDom);
    }

    if (parseInt(roomId) === 0) {
        addClassRequired(calendarRoomDom.next('.select2-container'));
    } else {
        removeClassRequired(calendarRoomDom.next('.select2-container'));
    }

    if (!attendeesId) {
        addClassRequired(calendarInterviewDom.next('.select2-container'));
    } else {
        removeClassRequired(calendarInterviewDom.next('.select2-container'));
    }

    return !title || !startDate || !endDate || parseInt(roomId) === 0 || !attendeesId;
}

setInterval(function() {
    $('.btn-create-calendar').prop('disabled', isInvalidCalendarForm());
}, 200);

function addClassRequired(dom) {
    dom.addClass('input-required');
}

function removeClassRequired(dom) {
    dom.removeClass('input-required');
}

function select2Interviewers(dom) {
    $(dom).select2({
        ajax: {
            url: $(dom).data('remote-url'),
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 5) < data.total_count
                    }
                };
            },
            cache: true
          },
          placeholder: 'Chọn nhân viên',
          escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
          minimumInputLength: 3,
          templateResult: formatRepo,
          templateSelection: formatRepoSelection,
          maximumSelectionSize: 5,
    });
}

function formatRepo (repo) {
  if (repo.loading) {
    return repo.text;
  }

  var markup = "<div class='select2-result-repository clearfix'>" +
    "<div class='select2-result-repository__avatar' style='float: left;'><img style='border-radius: 50px;width: 50px; height: 50px;' src='" + repo.avatar + "' /></div>" +
    "<div class='select2-result-repository__meta select2_text' style='float: left; padding: 17px 10px 17px 10px; font-size: 13px;'>" +
      "<div class='select2-result-repository__title'>" + repo.text + "</div>" +
    "</div>" + 
    "</div>";

  
  return markup;
}

function formatRepoSelection (repo) {
  return repo.text;
}