! function(n) { RKfuncion.select2.init() }(jQuery);

var $modalError = $('#modalError');
var $workingTimeForm = $('#working_time_form');
var $btnSubmit = $workingTimeForm.find('#btn-submit-form');

function appendToRow(dataEmployee) {
    var rowClone = $('#row_employees_generate_clone tbody tr').closest('tr').clone();
    resetRowTable();

    var keyWorkingTime = $('select[name="workingTime"]').val();
    var keyWorkingTimeHalf = $('select[name="workingTimeHalf"]').val();
    var arrTimeDate = {
        'startDate': $('#dateStartPicker input').val(),
        'endDate': $('#dateEndPicker input').val(),
    }
    var html = '';
    jQuery.each(dataEmployee, function(index, value) {
        if (arrEmpIdTable.indexOf(parseInt(value.employee_id)) === -1) {
            var arrEmp = {
                'id': value.employee_id,
                'code': value.employee_code,
                'name': value.employee_name
            };

            rowTableNew = setRowTable(rowClone, arrEmp, arrTimeDate, keyWorkingTime, keyWorkingTimeHalf);
            html += rowTableNew.prop('outerHTML');
            arrEmpIdTable.push(parseInt(value.employee_id));
        }
    });

    $('#table_employees_generate tbody').append(html);
}

/**
 * reset row table - remove row generate = 1
 */
function resetRowTable() {
    var rowTableOld = '';
    arrEmpIdTable = [];
    var arrTimeDate = {
        'startDate': $('#dateStartPicker input').val(),
        'endDate': $('#dateEndPicker input').val(),
    }
    var keyWorkingTime = $('select[name="workingTime"]').val();
    var keyWorkingTimeHalf = $('select[name="workingTimeHalf"]').val();

    $('#table_employees_generate > tbody tr').each(function() {
        var rowFind = $(this);
        var check = parseInt(rowFind.data('generate'));
        if (!check) {
            if (parseInt(rowFind.data('status'))) {
                rowFind = setRowTableWithTime(rowFind, arrTimeDate, keyWorkingTime, keyWorkingTimeHalf);
            }
            rowTableOld += rowFind.prop('outerHTML');
            arrEmpIdTable.push(parseInt(rowFind.data('id_emp')));
        }
    });
    $('#table_employees_generate tbody').html('')
    $(rowTableOld).appendTo('#table_employees_generate tbody');
}

/**
 * set time table
 */
function setTimeRowTable() {
    arrEmpIdTable = [];
    var arrTimeDate = {
        'startDate': $('#dateStartPicker input').val(),
        'endDate': $('#dateEndPicker input').val(),
    }
    var keyWorkingTime = $('select[name="workingTime"]').val();
    var keyWorkingTimeHalf = $('select[name="workingTimeHalf"]').val();

    $('#table_employees_generate > tbody tr').each(function() {
        var rowFind = $(this);
        if (parseInt(rowFind.data('status'))) {
            setRowTableWithTime(rowFind, arrTimeDate, keyWorkingTime, keyWorkingTimeHalf);
        }
    });
}

/**
 * set detail time row table
 */
function setRowTableWithTime(rowTableNew, arrTimeDate, keyWorkingTime, keyWorkingTimeHalf) {
    var timeFrame = workingTimeFrame[keyWorkingTime];
    var timeHalf = workingTimeHalfFrame[keyWorkingTimeHalf];

    rowTableNew.attr('data-key_working_time', keyWorkingTime);
    rowTableNew.attr('data-key_working_time_half', keyWorkingTimeHalf);
    rowTableNew.find('.col-start_date').text(arrTimeDate['startDate']);
    rowTableNew.find('.col-end_date').text(arrTimeDate['endDate']);
    rowTableNew.find('.col-morning_in').text(timeFrame[0]);
    rowTableNew.find('.col-morning_out').text(timeFrame[1]);
    rowTableNew.find('.col-afternoon_in').text(timeFrame[2]);
    rowTableNew.find('.col-afternoon_out').text(timeFrame[3]);
    rowTableNew.find('.col-half_morning').text(timeHalf[0]);
    rowTableNew.find('.col-half_afternoon').text(timeHalf[1]);

    return rowTableNew;
}

/**
 * set row table with infor employee, date
 */
function setRowTable(rowTableNew, arrEmp, arrTimeDate, keyWorkingTime, keyWorkingTimeHalf) {
    var timeFrame = workingTimeFrame[keyWorkingTime];
    var timeHalf = workingTimeHalfFrame[keyWorkingTimeHalf];

    rowTableNew.attr('data-id_emp', arrEmp['id']);
    rowTableNew.attr('data-key_working_time', keyWorkingTime);
    rowTableNew.attr('data-key_working_time_half', keyWorkingTimeHalf);
    rowTableNew.find('.col-code').text(arrEmp['code']);
    rowTableNew.find('.col-name').text(arrEmp['name']);
    rowTableNew.find('.col-start_date').text(arrTimeDate['startDate']);
    rowTableNew.find('.col-end_date').text(arrTimeDate['endDate']);
    rowTableNew.find('.col-morning_in').text(timeFrame[0]);
    rowTableNew.find('.col-morning_out').text(timeFrame[1]);
    rowTableNew.find('.col-afternoon_in').text(timeFrame[2]);
    rowTableNew.find('.col-afternoon_out').text(timeFrame[3]);
    rowTableNew.find('.col-half_morning').text(timeHalf[0]);
    rowTableNew.find('.col-half_afternoon').text(timeHalf[1]);

    return rowTableNew;
}

/**
 * find employee in project
 */
function findEmployeeByProj(idProj, startDate, endDate) {
    $.ajax({
        type: 'GET',
        url: urlEmpProj,
        data: {
            _token: _token,
            'id_proj': idProj,
            'start_date': startDate,
            'end_date': endDate,
        },
        success: function(response) {
            appendToRow(response);
        },
        error: function(error) {},
    });
}

/**
 * handling when have change datepicker, time, project
 */
$(function() {
    $('body').on('change', '#select-project', function(e) {
        var idProj = $(this).val();
        var startDate = $('#dateStartPicker input').val();
        var endDate = $('#dateEndPicker input').val();
        if (!idProj) {
            resetRowTable();
            return;
        }
        if (!startDate || !endDate) {
            $('#modalError .modal-body').html('<p>' + messRequired + '</p>');
            $('.btn-modalError').click();
            return;
        }
        findEmployeeByProj(idProj, startDate, endDate);
    })

    $('body').on('change', '.select-time', function(e) {
        var startDate = $('#dateStartPicker input').val();
        var endDate = $('#dateEndPicker input').val();
        if (!startDate || !endDate) {
            return;
        }
        setTimeRowTable();
    })

    $('.datepicker').datetimepicker({
        format: 'DD-MM-YYYY',
    }).on('dp.change', function(event) {
        var idProj = $('select[name="project_id"]').val();
        var startDate = $('#dateStartPicker input').val();
        var endDate = $('#dateEndPicker input').val();

        if (!idProj || (startDate && endDate)) {
            setTimeRowTable();
        }
        if (!idProj || !startDate || !endDate) {
            return;
        }
        // findEmployeeByProj(idProj, startDate, endDate);
    })

    $('.datepickerModal').datetimepicker({
        format: 'DD-MM-YYYY',
    }).on('dp.change', function(event) {})
})


/**
 * handling when employee click action
 */
$(function() {
    $("#table_employees_generate").on("click", ".btn-delete", function() {
        var empId = $(this).closest("tr").data('id_emp');
        const index = arrEmpIdTable.indexOf(empId);
        if (index > -1) {
            arrEmpIdTable.splice(index, 1);
        }
        $(this).closest("tr").remove();
    });

    $("#table_employees_generate").on("click", ".btn-edit", function() {
        var row = $(this).closest("tr");
        var keyWokingTime = row.data('key_working_time');
        var keyWokingTimeHalf = row.data('key_working_time_half');

        $('#dateStartPickerEdit input').val(row.find('.col-start_date').text());
        $('#dateEndPickerEdit input').val(row.find('.col-end_date').text());
        $('input[name="empId"]').val(row.attr('data-id_emp'));

        $('select[name="workingTimeEdit"] option').each(function() {
            if ($(this).val() == keyWokingTime) {
                $(this).attr("selected", "selected");
            } else {
                $(this).prop('checked', false);
            }
        });
        $('select[name="workingTimeHalfEdit"] option').each(function() {
            if ($(this).val() == keyWokingTimeHalf) {
                $(this).attr("selected", "selected");
            } else {
                $(this).prop('checked', false);
            }
        });
        $('.select2-base').select2();
        $(".btn-modalEdit").click();
    });
})

/**
 * update detail row table when edit
 */
$("#modalEdit").on("click", ".btn-submit-update", function() {
    var empId = $('input[name="empId"]').val();
    var workingTimeEdit = $('select[name="workingTimeEdit"]').val();
    var workingTimeHalfEdit = $('select[name="workingTimeHalfEdit"]').val();
    var arrTimeDate = {
        'startDate': $('input[name="startDateEdit"]').val(),
        'endDate': $('input[name="endDateEdit"]').val(),
    }

    $('#table_employees_generate > tbody tr').each(function() {
        var rowFind = $(this);
        var empIdRow = parseInt(rowFind.data('id_emp'));
        if (empIdRow == empId) {
            setRowTableWithTime(rowFind, arrTimeDate, workingTimeEdit, workingTimeHalfEdit);
            rowFind.attr('data-status', statusNotUpdate);
            rowFind.attr('data-generate', statusNotGenerate);
        }
    });
    $(".btn-modalEdit").click();
});

/**
 * add employee to table
 */
$("form").on("click", "#btn_add_employee_table_generate", function() {
    var employees = $('#search_add_employee').select2('data');
    var rowClone = $('#row_employees_generate_clone tbody tr').closest('tr').clone();
    var keyWorkingTime = $('select[name="workingTime"]').val();
    var keyWorkingTimeHalf = $('select[name="workingTimeHalf"]').val();
    var arrTimeDate = {
        'startDate': $('#dateStartPicker input').val(),
        'endDate': $('#dateEndPicker input').val(),
    }
    var html = '';
    $.each(employees, function(index, value) {
        var arrEmp = {
            'id': value['id'],
            'code': value['employee_code'],
            'name': value['employee_name']
        };
        if (arrEmpIdTable.indexOf(parseInt(arrEmp['id'])) === -1) {
            rowClone.attr('data-generate', statusNotGenerate);
            rowTableNew = setRowTable(rowClone, arrEmp, arrTimeDate, keyWorkingTime, keyWorkingTimeHalf);
            html += rowTableNew.prop('outerHTML');
            arrEmpIdTable.push(parseInt(arrEmp['id']));
        }
    });
    $('#table_employees_generate tbody').append(html);
    $('#search_add_employee').html('').trigger('change');
})

$btnSubmit.click(submitRegisterForm);

/**
 * validate register working time form
 * @returns {object} {errorMessages: array, data: array, status: boolean}
 */
function validateRegisterForm() {
    var data = getFormData();
    var errorMessages = [];
    if (!data.reason) {
        errorMessages.push(messTheReson);
    }
    if (!data.approver_id) {
        errorMessages.push(messEmpApprove);
    }
    if (!checkIsDate(data.from_date) || !checkIsDate(data.to_date) || data.from_date > data.to_date) {
        errorMessages.push(messEndThanStart);
    }
    if (!data.wtDetail.length) {
        errorMessages.push(messNotObject);
    } else {
        data.wtDetail.forEach(function(wtDetail) {
            if (!checkIsDate(wtDetail.end_date) || !checkIsDate(wtDetail.start_date) || wtDetail.start_date > wtDetail.end_date) {
                errorMessages.push('Nhân viên: <b>' + wtDetail.employee_name + '</b> ' + messEndLessStart);
            }
        });
    }
    return {
        status: !errorMessages.length,
        errorMessages: errorMessages,
        data: data,
    }
}

/**
 * submit register working time form
 * @param {Event} event
 */
function submitRegisterForm(event) {
    event.preventDefault();
    var strMessage = '';
    var dataValidate = validateRegisterForm();
    /* validate form fail */
    if (!dataValidate.status) {
        dataValidate.errorMessages.forEach(function(message) {
            strMessage += '<li>' + message + '</li>';
        });
        $modalError.modal('show').find('.modal-body').html('<ul>' + strMessage + '</ul>');
        return;
    }

    $btnSubmit.find('i').removeClass('hidden');
    $.ajax({
        url: $workingTimeForm.attr('action'),
        type: $workingTimeForm.attr('method'),
        data: dataValidate.data,
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                location.href = response.redirect;
                return;
            }

            strMessage += '<li>' + response.message + '</li>';
            var errorMessage = '';
            Object.keys(response['exists'] || []).forEach(function(key) {
                if ($.inArray(key, ['leave_day', 'supplement', 'business_trip', 'ot', 'working_time']) === -1) {
                    return;
                }
                response['exists'][key].forEach(function(registration) {
                    var note = '';
                    if (registration.note) {
                        note += '<div>' + registration['note']['old_text'] + '</div>'
                            + '<div>' + registration['note']['new_text'] + '</div>';
                    }
                    errorMessage += '<tr>' +
                        '<td>' + registration['employee_name'] + '</td>' +
                        '<td>' + registration['type'] + '</td>' +
                        '<td>' + new Date(registration['date_start']).format('d-m-Y H:i') + '</td>' +
                        '<td>' + new Date(registration['date_end']).format('d-m-Y H:i') + '</td>' +
                        '<td>' + note + '</td>' +
                        '<td><a target="_blank" href="' + registration['url'].replace(':id', registration['id']) + '">' + viewDetail + '</a></td>' +
                        '</tr>';
                })
            });
            if (errorMessage) {
                strMessage += '<table class="table-register-exist">' +
                    '<thead>' +
                    '<tr>' +
                    '<th> ' + viewEmployee + '</th>' +
                    '<th>' + viewAppType + '</th>' +
                    '<th>' + viewFromDate + '</th>' +
                    '<th>' + viewToDate + '</th>' +
                    '<th>' + txtNote + '</th>' +
                    '<th></th>' +
                    '</tr>' +
                    '</thead>' +
                    '<tbody>' + errorMessage + '</tbody>' +
                    '</table>'
            }
        },
        error: function() {
            strMessage = 'Đã có lỗi xảy ra. Vui lòng thử lại sau!';
        },
        complete: function() {
            $btnSubmit.find('i.fa-refresh').addClass('hidden');
            strMessage && $modalError.modal('show').find('.modal-body').html('<ul>' + strMessage + '</ul>');
        }
    });
}

/**
 * get data form working time register
 */
function getFormData() {
    var wtDetails = [];
    $('#table_employees_generate > tbody tr').each(function() {
        var rowTable = $(this);
        wtDetails.push({
            'employee_id': rowTable.data('id_emp'),
            'employee_name': rowTable.find('.col-name').text(),
            'key_working_time': rowTable.attr('data-key_working_time'),
            'key_working_time_half': rowTable.attr('data-key_working_time_half'),
            'start_date': rowTable.find('.col-start_date').text().split('-').reverse().join('-'),
            'end_date': rowTable.find('.col-end_date').text().split('-').reverse().join('-'),
        });
    });
    return {
        register_id: idRegister,
        approver_id: $('select[name="approver_id"]').val(),
        related_ids: $('select[name="related_ids[]"]').val(),
        from_date: $('#dateStartPicker input').val().split('-').reverse().join('-'),
        to_date: $('#dateEndPicker input').val().split('-').reverse().join('-'),
        key_working_time: $('select[name="workingTime"]').val(),
        key_working_time_half: $('select[name="workingTimeHalf"]').val(),
        proj_id: $('#select-project').val(),
        reason: $('textarea[name="reason"]').val().trim(),
        wtDetail: wtDetails,
        _token: _token,
    };
}

// approve register
$('#working_time_form button.status-submit').click(function() {
    var btn = $(this);
    var url = btn.data('url');
    var status = btn.data('status');
    var id = btn.data('id');
    var check = true;
    var message = '';
    if (typeof id == 'undefined' || !id) {
        message = '<li> ' + messEmpNotCode + ' </li>';
        check = false;
    }

    if (!url || !status) {
        message += '<li> Có lỗi xảy ra!</li>';
        check = false;
    }
    if (!check) {
        $('#modalError .modal-body').html('<ul>' + message + '</ul>');
        $('.btn-modalError').click();
        return false;
    }

    bootbox.confirm({
        className: 'modal-warning',
        message: btn.data('noti'),
        buttons: {
            cancel: {
                label: confirmNo,
            },
            confirm: {
                label: confirmYes,
            },
        },

        callback: function(result) {
            if (result) {
                $('#working_time_form').attr('action', url)
                $('#working_time_form').submit();
            }
        },
    });
});

// reject register
$('#btn_time_modal_reject').click(function() {
    $('#wk_time_modal_reject').modal('show');
});

$(function() {
    $('select[name="workingTime"]').change(function() {
        var index = wKTRelation[$(this).val()];
        $('select[name="workingTimeHalf"] option').each(function() {
            if ($(this).val() == index) {
                $(this).attr('selected', 'selected');
                $(this).trigger('change');
            } else {
                $(this).attr('selected', false)
            }
        });
    })
    $('select[name="workingTimeEdit"]').change(function() {
        var index = wKTRelation[$(this).val()];
        $('select[name="workingTimeHalfEdit"] option').each(function() {
            if ($(this).val() == index) {
                $(this).attr('selected', 'selected');
                $(this).trigger('change');
            } else {
                $(this).attr('selected', false)
            }
        });
    })
})