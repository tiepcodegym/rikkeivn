jQuery(document).ready(function ($) {
    selectSearchReload();
});


/*
 * Datepicker set
 */
var $startMonth = $('#start_date');
var $endMonth = $('#end_date');


$startMonth.datepicker({
    format: 'yyyy/mm/dd',
    autoclose: true,
    endDate: $endMonth.val(),
    todayHighlight: true,
}).on('changeDate', function () {
    $endMonth.datepicker('setStartDate', $startMonth.val());
});

$endMonth.datepicker({
    format: 'yyyy/mm/dd',
    autoclose: true,
    startDate: $startMonth.val(),
    todayHighlight: true,
}).on('changeDate', function () {
    $startMonth.datepicker('setEndDate', $endMonth.val());
});

/**
 * 
 * iCheck load
 */
$('input').iCheck({
    checkboxClass: 'icheckbox_minimal-blue',
    radioClass: 'iradio_minimal-blue'
});

$('#rikker_relate').keydown(function (e) {
    var keyCode = e.keyCode || e.which;
    var value = $.trim($(this).val());

    if (keyCode === 8 && value === '') { // backspace event
        backSpace('rikker_relate');
    } else if (keyCode === 9 || keyCode === 13
            || keyCode === 188 || keyCode === 186) { //tab, enter, comma, semi-colon press
        var value = $.trim($('#rikker_relate').val());
        if ((ajaxLoadingEmail['#rikker_relate'] !== undefined && !ajaxLoadingEmail['#rikker_relate']) ||
                ajaxLoadingEmail['#rikker_relate'] === undefined) {
            if (value !== '') {
                tabEvent('#rikker_relate', value);
                e.preventDefault();
            }
        } else {
            return false;
        }
    } else if (keyCode === 38 || keyCode === 40) { //up down arrow event
        selectUpDown('rikker_relate', keyCode);
    } else {
        if (typeof ajax_request !== 'undefined')
            ajax_request.abort();

        var keyValue = '';
        if (keyCode !== 8) { // not backspace
            keyValue = String.fromCharCode(e.keyCode);
            value += keyValue;
        } else {
            value = value.slice(0, -1);
        }
        ajaxLoadingEmail['#rikker_relate'] = true;
        showList('#rikker_relate', value);
    }
});


//disable enter to submit form
$('#frm_create_checkpoint').on('keyup keypress', function (e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
        e.preventDefault();
        return false;
    }
});

$('#rikker_relate').blur(function (e) {
    if ($().isClickWithoutDom({
        'container': $(this),
        'except': $(this).siblings('.rikker-result')
    })) {
        value = $.trim($('#rikker_relate').val());
        if (value !== '') {
            tabEvent('#rikker_relate', value, {setText: 1});
            e.preventDefault();
        }
    }
});

/*
 * Set select2 and multiselect if update page
 */
$(document).ready(function () {
    $('.evaluator-row').each(function () {
        console.log('1');
        $(this).find('#evaluator').select2();
        refreshMulti($(this).find('#evaluated'));
        var row = $(this).find('#evaluated').attr('row');
        $('.evaluator-row-' + row + ' .multiselect-container li input').attr('row', row);
    });
})

$('#set_team').on('change', function () {
    submitLoading();
    var teamId = $(this).val();
    $('.evaluator-row').remove();
    $('.add-evaluator-row #evaluator').find("option:gt(0)").remove();
    if (teamId == 0) {
        $('.add-evaluator-row').addClass('hidden');
        $('.evaluator-row').remove();
        return false;
    } else {
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        if (start_date.length > 0 && end_date.length > 0) {
            $.ajax({
                url: urlSetEmp,
                type: 'post',
                data: {
                    _token: token,
                    teamId: teamId,
                    start_date: start_date,
                    end_date: end_date,
                },
                success: function (data) {
                    submitLoaded();
                    var option = '';
                    var optionAll = '';
                    $('.add-evaluator').removeClass('hidden');
                    if (data == 0) {

                    } else {
                        for (var i = 0; i < data['empOfTeam'].length; i++) {
                            option += '<option value="' + data['empOfTeam'][i]['id'] + '">' + data['empOfTeam'][i]['nickname'] + '</option>';
                        }
                        for (var i = 0; i < data['empAll'].length; i++) {
                            optionAll += '<option value="' + data['empAll'][i]['id'] + '">' + data['empAll'][i]['nickname'] + '</option>';
                        }
                    }
                    $('.add-evaluator-row #evaluator').append(optionAll);
                    $('.add-evaluator-row #evaluated').html(option);
                    setSelect();
                },
                fail: function () {
                    submitLoaded();
                    alert("Ajax failed to fetch data");
                }
            });
        } else {
            return false;
        }
    }
});

$('.add-evaluator').on('click', function () {
    setSelect();
})

/*
 * Add a row evaluator, evaluated
 */
function setSelect() {
    var i = $('.evaluator-row:last').attr('row');
    if (i)
        i = parseInt(i) + 1;
    else
        i = 1;
    var $row = $('.add-evaluator-row').html();

    $('.btn-container').before('<div class="row evaluator-row evaluator-row-' + i + '" row="' + i + '">' + $row + '</div>');
    $('.evaluator-row-' + i).find('#evaluated').attr('row', i);
    var choosen = [];
    $('.evaluator-row').each(function () {
        var $evaluated = $(this).find('#evaluated');

        var values = $evaluated.val();
        if (values) {
            for (var k = 0; k < values.length; k++) {
                choosen.push(values[k]);
            }
        }
    });
    choosen = unique(choosen);
    for (var k = 0; k < choosen.length; k++) {
        $(".evaluator-row-" + i + " #evaluated option[value=" + choosen[k] + "]").prop('disabled', true);
    }

    $('.evaluator-row-' + i + ' #evaluator').select2();
    refreshMulti($('.evaluator-row-' + i + ' #evaluated'));
    $('.evaluator-row-' + i + ' .multiselect-container li input').attr('row', i);
    $('.evaluator-row-' + i + ' #evaluator').attr('row', i);
    $('.evaluator-row-' + i + ' #evaluator').attr('name', 'evaluator[' + i + ']');
    $('.evaluator-row-' + i + ' #evaluated').attr('name', 'evaluated[' + i + '][]');
    $('.evaluator-row-' + i + ' .btn-delete-row').attr('row', i);

    removeButtonDel();

}

/*
 * Remove duplicates elements from array
 */
function unique(list) {
    var result = [];
    $.each(list, function (i, e) {
        if ($.inArray(e, result) == -1)
            result.push(e);
    });
    return result;
}



/*
 * Get old value selected of evaluator select boxes
 */
$(document).on('focus', '.select2-container', function () {
    var input = $(this).find('span.select2-selection');
    if (input) {
        var id = input.attr('aria-activedescendant');
        if (id) {
            var lastindex = id.lastIndexOf('-');
            var oldVal = id.substring(lastindex + 1);
            console.log(oldVal);
            $(this).parent().find('#evaluator').attr('old', oldVal);
        }

    }

});

/*
 * Refresh bootstrap multiselect
 */
function refreshMulti(elem) {
    elem.multiselect('destroy').multiselect({
        enableFiltering: true,
        numberDisplayed: 3,
        nSelectedText: 'person',
        maxHeight: 300
    }).multiselect('refresh');
}

/*
 * Evaluator
 * Disabled|enabled option in another selects when this select selected|unselected it
 */
$(document).on('change', '#evaluator', function () {
    validateHtml($(this));
    var oldVal = $(this).attr('old');
    var selected = $(this).val();
    var row = $(this).attr('row');
    $('select[dataname=evaluator]').each(function () {
        var r = $(this).attr('row');
        if (r != row) {
            $(this).find('option[value=' + selected + ']').prop('disabled', true);
            $(this).find('option[value=' + oldVal + ']').prop('disabled', false);
            //refresh select2
            if (r != null) {
                $(this).select2();
            }
        }
    })
});

$(document).on('change', '#evaluated', function () {
    validateHtml($(this));
});

/*
 * Evaluated
 * disabled|enable option in another selects when this select selected|unselected it.
 */
$(document).on('change', 'input[type=checkbox]', function () {
    var value = $(this).val();
    var selectParent = $(this).closest('.evaluated-container').find('#evaluated');
    var row = selectParent.attr('row');
    if (this.checked) {
        $('select[dataname=evaluated]').each(function () {
            var r = $(this).attr('row');
            console.log(r);
            if (r != row && r != null) {
                $(this).find('option[value=' + value + ']').prop('disabled', true);
                $('input[type=checkbox][row=' + r + '][value=' + value + ']').prop('disabled', true);
                $('input[type=checkbox][row=' + r + '][value=' + value + ']').parent('li').prop('disabled', true);
            }
        });
    } else {
        $('select[dataname=evaluated]').each(function () {
            var r = $(this).attr('row');
            if (r != row && r != null) {
                $(this).find('option[value=' + value + ']').prop('disabled', false);
                $('input[type=checkbox][row=' + r + '][value=' + value + ']').prop('disabled', false);
                $('input[type=checkbox][row=' + r + '][value=' + value + ']').parent().parent().parent().addClass('disabled');
            }
        });
    }
});

/*
 * Delete a row evaluator
 */
$(document).on('click', '.btn-delete-row', function () {
    var row = $(this).attr('row');
    var selectEvaluator = $('select[dataname=evaluator][row=' + row + ']');
    var selectEvaluated = $('select[dataname=evaluated][row=' + row + ']');
    var evaluated = selectEvaluated.val();
    $('select[dataname=evaluator]').each(function () {
        var r = $(this).attr('row');
        if (r != row) {
            $(this).find('option[value=' + selectEvaluator.val() + ']').prop('disabled', false);
            //refresh select2
            if (r != null) {
                $(this).select2();
            }
        }
    })

    if (evaluated) {
        $('select[dataname=evaluated]').each(function () {
            var r = $(this).attr('row');
            if (r != row && r != null) {
                for (var i = 0; i < evaluated.length; i++) {
                    $(this).find('option[value=' + evaluated[i] + ']').prop('disabled', false);
                    $('input[type=checkbox][row=' + r + '][value=' + evaluated[i] + ']').prop('disabled', false);
                    $('input[type=checkbox][row=' + r + '][value=' + evaluated[i] + ']').parent().parent().parent().removeClass('disabled');
                }
            }
        });
    }

    $('.evaluator-row-' + row).remove();

    removeButtonDel();
});

$('.date').change(function () {
    if ($('#start_date').val().length > 0 && $('#end_date').val().length > 0) {
        $('#team-checkpoint').removeClass('hidden');
        $('#team-checkpoint').addClass('show');
    }
});


/*
 * Submit form create|update
 */
$(".btn-create").click(function () {
    //Validate form
    var invalid = false;
    var $label = $('#error_append');
    $("#frm_create_checkpoint label.error").remove();
    if ($('#check_time').val() == '0') {
        invalid = true;
        $('#check_time').after($label.html());
    }

    if ($('#start_date').val().trim() == '') {
        invalid = true;
        $('#start_date').after($label.html());
        $("#start_date").parent().find('label.error').css('left', '0');
    }

    if ($('#end_date').val().trim() == '') {
        invalid = true;
        $('#end_date').after($label.html());
        $("#end_date").parent().find('label.error').css('left', '0');
    }

    if ($('#set_team').val() == '0') {
        invalid = true;
        $('#set_team').after($label.html());
    }

    $("[name^=evaluator]").each(function () {
        var value = $(this).val();
        if (value == null) {
            invalid = true;
            $(this).after($label.html());
        }
    });

    $("[name^=evaluated]").each(function () {
        var value = $(this).val();
        if (value == null) {
            invalid = true;
            $(this).after($label.html());
        }
    });

    if ($('#rikker_relate_validate').val().trim() == '') {
        invalid = true;
        $('.rikker-relate-container').after($label.html());
    }

    if (invalid) {
        $("#frm_create_checkpoint label.error").show().html(requiredText);
        $('.rikker-relate-container').parent().find('label.error').html(emailInvalid);
        $('.btn-create').removeAttr('disabled');
        return false;
    }
    //End validate form

    var rikker_relate = [];
    $('input[name^=rikker_relate][type=hidden]').each(function () {
        rikker_relate.push($(this).val());
    });

    var evaluator = [];
    $("[name^=evaluator]").each(function () {
        var row = $(this).attr('row');
        var value = $(this).val();
        if (row != null && row > 0)
            evaluator[row] = value;
    });
    var evaluated = [];
    $("[name^=evaluated]").each(function () {
        var row = $(this).attr('row');
        var value = $(this).val();
        if (row != null && row > 0)
            evaluated[row] = value;
    });
    submitLoading();
    $.ajax({
        url: urlSave,
        type: 'post',
        dataType: 'html',
        data: {
            _token: token,
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            check_time: $('#check_time').val(),
            set_team: $('#set_team').val(),
            rikker_relate: rikker_relate,
            create_or_update: $('[name=create_or_update]').val(),
            checkpoint_id: $('[name=checkpoint_id]').val(),
            employee_id: $('#employee_id').val(),
            checkpoint_type_id: $('[name=checkpoint_type_id]:checked').val(),
            evaluator: evaluator,
            evaluated: evaluated,
        },
        success: function(response) {
            response = JSON.parse(response);
            if (response.success === 0) {
                RKExternal.notify(response.message_error, false);
                location.reload();
                return false;
            } else {
                window.location.href = response.url;
            }
        }
    })

            .fail(function () {
                submitLoaded();
                alert("Ajax failed to fetch data");
            })
});

function submitLoading() {
    $('.btn-create').prop('disabled', true);
    $('.btn-create i').removeClass('hidden');
}

function submitLoaded() {
    $('.btn-create').prop('disabled', false);
    $('.btn-create i').addClass('hidden');
}

/*
 * Check if count evaluator is 1 then remove btn delete
 */
function removeButtonDel() {
    if ($('.evaluator-row').length == 1) {
        $('.evaluator-row .btn-delete-row').remove();
    }
}

function validateHtml(elem) {
    var val = elem.val();
    var $label = $('#error_append');
    if (parseInt(val) == 0 || val == '' || val == null) {
        elem.after($label.html());
        elem.parent().find('label.error').show().html(requiredText);
    } else {
        elem.parent().find('label.error').remove();
    }
}
