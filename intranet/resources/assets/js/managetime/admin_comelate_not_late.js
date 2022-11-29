function getRowNewNotLate(number) {
    return '<tr data-row-id="new_not_late_' + number + '">' +
        ' <td class="text-center">' + number + '</td>' +
        ' <td><div class="text email hidden"></div></td>' +
        ' <td><div class="text name hidden"></div> <div class="input input-box">' +
        '   <select name="empid" class="form-control select-search-employee" data-remote-url="' + urlSearchEmp + '"></select>' +
        '   <div class="error empid-error"></div> </div> </td>' +
        ' <td class="col-week"><div class="text week hidden"></div>' +
        ' <div class="input"> <select class="form-control weekdays select2-hidden-accessible" name="weekdays[]" multiple="multiple">' +
        strDayWeek + ' </select> <div class="error weekdays-error"></div> </div> </td>' +
        ' <td class="ct_btn-group"> <div class="text hidden"> ' +
        ' <button class="btn btn-primary btn-ss-action" data-btn-action="edit" type="button"><i class="fa fa-pencil"></i></button>' +
        ' <button class="btn btn-danger btn-ss-action" data-btn-action="delete" type="button"><i class="fa fa-trash"></i></button>' +
        ' </div> <div class="input ">' +
        ' <button class="btn btn-success btn-ss-action" data-btn-action="save" type="button">' +
        ' <i class="fa fa-floppy-o"><i class="fa fa-refresh fa-spin margin-left-10 hidden" hiddenaria-hidden="true"></i></i></button>' +
        ' </div> </td> </tr>';
};

function loadSelect2() {
    $('.select-search-employee').selectSearchEmployee();
    $('.weekdays').select2();
};

$(function() {
    loadSelect2();
});

function setSelectWeek(row, week) {
    if (week) {
        var str = '';
        for (var i = 0; i < week.length; i++) {
            str += '<span class="label label-default">' + dataDayWeeks[week[i]] + '</span> '
        }
        row.find('.week').html(str);
    }
};

function updateRow(res, row) {
    var data = res.data;
    row.find('.email').text(data.emp_email);
    row.find('.name').text(data.emp_name);

    var arrWeek = data.weekdays.split(',');
    setSelectWeek(row, arrWeek);

    row.find('.fa-refresh').addClass('hidden');
    row.find('.text').removeClass('hidden');
    row.find('.input').addClass('hidden');
    RKExternal.notify(res.message, true);
};

$('#not-late').delegate("button[data-btn-action=update]", 'click', function() {
    var row = $(this).closest('tr');
    var empId = row.find('select[name="empid"]').val();
    var weekdays = row.find('select[name="weekdays[]"]').val();
    var id = row.attr('data-row-id');
    var check = true;
    row.find('.error').text('');
    if (empId === '' || empId == null) {
        row.find('.empid-error').text('Không để trống');
        check = false;
    }
    if (weekdays === '' || weekdays === null) {
        row.find('.weekdays-error').text('Không để trống');
        check = false;
    }

    if (check) {
        $(this).find('.hidden').removeClass('hidden');
        $.ajax({
            dataType: 'json',
            type: 'post',
            url: urlUpdateNotLate,
            data: {
                _token: token,
                id: id,
                empId: empId,
                weekdays: weekdays,
            },
            success: function(res) {
                if (res.errors) {
                    messageError(res);
                }
                if (res.status) {
                    updateRow(res, row);
                } else {
                    RKExternal.notify(res.message, false);
                }
                row.find('.fa-refresh').addClass('hidden');
            },
            error: function(error) {

            },
        })
    }
})

$('body').on('click', "button[data-btn-action=edit]", function() {
    var row = $(this).closest('tr');
    row.find('.text').addClass('hidden');
    row.find('.input').removeClass('hidden');
})

$(document).on('click', '.btn-add', function(event) {
    var IdTable = $(this).attr('data-table-id');
    var row = getRowNewNotLate(++numberNotlate);
    $('#' + IdTable + ' tbody:last-child').append(row);
    loadSelect2();
});

$('body').on('click', "button[data-btn-action=save]", function() {
    var row = $(this).closest('tr');
    var empId = row.find('select[name="empid"]').val();
    var weekdays = row.find('select[name="weekdays[]"]').val();
    var check = true;
    row.find('.error').text('');
    if (empId === '' || empId == null) {
        row.find('.empid-error').text('Không để trống');
        check = false;
    }
    if (weekdays === '' || weekdays === null) {
        row.find('.weekdays-error').text('Không để trống');
        check = false;
    }
    if (check) {
        $(this).find('.hidden').removeClass('hidden');
        $.ajax({
            dataType: 'json',
            type: 'post',
            url: urlSaveNotLate,
            data: {
                _token: token,
                empId: empId,
                weekdays: weekdays,
            },
            success: function(res) {
                if (res.errors) {
                    messageError(res);
                }
                if (res.status) {
                    updateRow(res, row);
                    var dataId = 'new_not_late_' + numberNotlate;
                    var trNew = $('#not-late').find('tr[data-row-id="' + dataId + '"]');
                    trNew.attr('data-row-id', res.data.id);
                    $('tr[data-row-id="' + res.data.id + '"]').find('button[data-btn-action="save"]').attr('data-btn-action', 'update');
                } else {
                    RKExternal.notify(res.message, false);
                }
                row.find('.fa-refresh').addClass('hidden');
            },
            error: function(error) {

            },
        })
    }
})

$('body').on('click', "button[data-btn-action=delete]", function() {
    var row = $(this).closest('tr');
    var name = row.find('.name').text();
    name = name.trim();
    var result = confirm(messageDelete + ": " + name + "?");

    if (result) {
        $.ajax({
            dataType: 'json',
            type: 'post',
            url: urlDeleteNotLate,
            data: {
                _token: token,
                id: row.attr('data-row-id')
            },
            success: function(res) {
                if (res.errors) {
                    messageError(res);
                }
                if (res.status) {
                    row.css("background", "red");
                    setTimeout(function() { row.remove(); }, 500);
                    RKExternal.notify(res.message, true);
                } else {
                    RKExternal.notify(res.message, false);
                }
            },
            error: function(error) {
                RKExternal.notify(error, false)
            },
        })
    }
})

function messageError(res) {
    var message = '';
    $.each(res.errors, function(key, value) {
        $.each(value, function(key2, value2) {
            message += value2 + "\n";
        });
    });
    RKExternal.notify(message, false)
}