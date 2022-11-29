$('.btn-submit-confirm').on("click", function() {
    $('#modal-submit-confirm').modal("show");
});

$('.btn-publish-request').on("click", function() {
    $('#modal-publish-request').modal("show");
});

$('.btn-ok').on("click", function(event) {
    var published = $(this).attr('data-publish');
    if (parseInt(published) === statusPublished) {
        var form = $('#form-create-request');
        var data = getDataRequest();
        $('.teams-container .box-position').each(function () {
            var position = $(this).find('select.position-apply').val();
            var number = $(this).find('.number-resource').val();
            data['positions['+position+']'] = number;
        });
        var programs = form.find('select[name="programs[]"]').val();
        var types = form.find('select[name="typecandidate[]"]').val();

        if (programs) {
            data['programs'] = programs.join(',');
        }
        if (types) {
            data['types'] = types.join(',');
        }
        $.ajax({
            type: 'POST',
            url: urlPostRequest,
            data: data,
            complete: function () {
                $('#form-create-request').submit();
            },
        });
        return false;
    }
    $('#modal-submit-confirm').modal("hide");
});

$(document).ready(function () {
    $('#interviewer').change(function(){
        var value = $(this).val();
        if (value == null) {
            $('#chk_interviewer').val('');
        } else {
            $('#chk_interviewer').val(1);
            $('#chk_interviewer-error').remove();
        }
    });

    $('#type').change(function(){
        var value = $(this).val();
        if (value == null) {
            $('#chk_type').val('');
        } else {
            $('#chk_type').val(1);
            $('#chk_type-error').remove();
        }
    });

    $("#form-create-request").validate({
        rules: {
            title: {
                requiredTrim : true,
                maxlength: 255,
            },
            customer: {
                maxlength: 50,
            },
            set_content: 'required',
            request_date: 'requiredTrim',
            start_working: 'requiredTrim',
            end_working: {
                greaterThan: "#start_working",
            },
            effort: { valueNotEquals: "0" },
            location: { valueNotEquals: "0" },
            interviewer: { valueNotEquals: "0" },
            chk_interviewer: 'required',
            deadline: 'requiredTrim',
            salary: 'requiredTrim',
            chk_type: "required",
            approve: { valueNotEquals: "0" },
            select_team: 'required',
            skill: { valueNotEquals: "0" },
            recruiter: { valueNotEquals: "0" },
        },
        messages: {
            title: {
                requiredTrim: requiredText,
            },
            set_content: requiredText,
            request_date: requiredText,
            number_resource: requiredText,
            start_working: requiredText,
            end_working: {
                greaterThan: checkWorkingDate,
            },
            effort: { valueNotEquals: requiredText },
            location: { valueNotEquals: requiredText },
            interviewer: { valueNotEquals: requiredText },
            chk_interviewer: requiredText,
            chk_type: requiredText,
            deadline: {
                required: requiredText,
            },
            salary: requiredText,
            approve: { valueNotEquals: requiredText },
            select_team: requiredText,
            skill: { valueNotEquals: requiredText },
            recruiter: { valueNotEquals: requiredText },
        }
    });

    jQuery.validator.addMethod("validateEndWorking", function(value, element, params) {
        if ($('.end-working-container').hasClass('display-end-wroking') && value && $(params).val()) {
            if (!/Invalid|NaN/.test(new Date(value))) {
                return new Date(value) > new Date($(params).val());
            }

            return isNaN(value) && isNaN($(params).val())
                || (Number(value) > Number($(params).val()));
        }
        return true;
    }, checkWorkingDate);

    $('.date').datepicker({
        todayBtn: "linked",
        language: "it",
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd'
    });




});

function generate(id, token, url) {
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'html',
        data: {
            _token: token,
            id: id,
        },
    })
}
//salary format
numberFormat($('#salary'));

/*
 * Datepicker set
 */
var $startWorking = $('#start_working');
var $endWorking = $('#end_working');
var $requestDate = $('#request_date');
var $deadline = $('#deadline');


$startWorking.datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    todayHighlight: true,
    todayBtn: "linked",
}).on('changeDate', function () {
    $endWorking.datepicker('setStartDate', $startWorking.val());
    $endWorking.datepicker('setFormat', 'yyyy-mm-dd');
    $requestDate.datepicker('setEndDate', $startWorking.val());
});

$endWorking.datepicker({
    autoclose: true,
    startDate: '0',
    format: 'yyyy-mm-dd',
    todayHighlight: true,
    todayBtn: "linked",
}).on('changeDate', function () {
    $startWorking.datepicker('setEndDate', $endWorking.val());
});

$requestDate.datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    todayHighlight: true,
    todayBtn: "linked",
}).on('changeDate', function () {
    $deadline.datepicker('setStartDate', $requestDate.val());
    $startWorking.datepicker('setStartDate', $requestDate.val());
});

$deadline.datepicker({
    autoclose: true,
    startDate: '0',
    format: 'yyyy-mm-dd',
    todayHighlight: true,
    todayBtn: "linked",
}).on('changeDate', function () {
    $requestDate.datepicker('setEndDate', $deadline.val());
});

/**
 * Show or hide end working input when change onsite type
 */
$('#onsite').on('change', function() {
    var value = $(this).val();
    if (value == onsiteOn) {
        //html = $('.end-working-container-fake').html();
        $('.end-working-container').show();
        $('.end-working-container').addClass('display-end-wroking');
    } else {
        $('.end-working-container').hide();
        $('.end-working-container').removeClass('display-end-wroking');
    }
});

/** ---------- TEAMS OF REQUEST ----------------------------------------------*/

/** Elements declare */
var $selectTeam = $('.select-team');
var $modalTeam = $('#modal-teams');
var $btnAddTeam = $('.btn-add-team');
var $addBoxTeam = $('.add-box-team');
var $htmlBoxTeam = $addBoxTeam.html();
var $boxPosition = $addBoxTeam.find('.box-position');
var $htmlBoxPosition = $boxPosition[0].outerHTML;
var $btnAddPosition = $('.btn-add-position');
var $teamsContainer = $('.teams-container');
var boxTeamClass = '.team-container';
var selectTeamClass = 'select.team';
var boxPositionClass = '.box-position';
var positionApplyClass = '.position-apply';
var numberClass = '.number-resource';
var messageRequired = '<label class="error bottom--24px">'+requiredText+'</label>';
var btnRemovePositionClass = '.btn-delete-row';
var btnRemoveTeamClass = '.btn-danger';

/**
 * Show modal edit team
 */
$selectTeam.on('click', function() {
    $teamsContainer.find(boxTeamClass + '[data-has!=1]').remove(); //Remove all team not saved
    if ($teamsContainer.find(boxTeamClass + '[data-has=1]').length == 0) { //New request
        $btnAddTeam.trigger('click');
    } else { //Edit request
        //Restore teams, positions not saved
        $teamsContainer.find(boxTeamClass).each(function() {
            var hasSaved = $(this).attr('data-has');
            if (parseInt(hasSaved) == 1) {
                $(this).removeClass('hidden');
                $(this).find(boxPositionClass).each(function() {
                    var hasSaved = $(this).attr('data-has');
                    if (parseInt(hasSaved) == 1) {
                        $(this).removeClass('hidden');
                    } else {
                        $(this).remove();
                    }
                });
            }
        });
    }
    delBtnRemovePosition();
    delBtnRemoveTeam();
    //Show modal
    $modalTeam.modal('show');
});

/**
 * Add box team
 */
$btnAddTeam.on('click', function() {
    $(this).before($htmlBoxTeam);
    var teamSelected = getTeamSelected();
    var lastBoxTeam = $teamsContainer.find(boxTeamClass+':last');
    var lastTeam = lastBoxTeam.find(selectTeamClass);
    var countSelected = teamSelected.length;
    for (var i=0; i< countSelected; i++) {
        lastTeam.find('option[value='+teamSelected[i]+']').prop('disabled', true);
    }
    selectSearchReload();
    var lastBoxPosition = lastBoxTeam.find(positionApplyClass+':last');
    lastBoxPosition.select2();
    delBtnRemovePosition();
    delBtnRemoveTeam();
});

/*
 * Change team event
 */
function changeTeam(elem) {
    refreshOptionTeam();
    var teamObject = $(elem);
    teamObject.parent().find('.error').remove();
    var teamVal = teamObject.val();
    if (parseInt(teamVal) == 0) {
        teamObject.parent().append(messageRequired);
    }
}

/**
 * Add box position of team
 */
function addBoxPosition(elem) {
    $(elem).before($htmlBoxPosition);
    var boxContainer = $(elem).closest('.box');
    var positionSelected = getPositionSelected(boxContainer);
    var lastBoxPosition = boxContainer.find(boxPositionClass+':last');
    var lastPosition = lastBoxPosition.find(positionApplyClass);
    var countSelected = positionSelected.length;
    for (var i=0; i< countSelected; i++) {
        lastPosition.find('option[value='+positionSelected[i]+']').prop('disabled', true);
    }
    lastPosition.select2();
    delBtnRemovePosition();
}

/*
 * Change position event
 */
function changePosition(elem) {
    var boxContainer = $(elem).closest('.box');
    refreshOptionPosition(boxContainer);
    var positionObject = $(elem);
    positionObject.parent().find('.error').remove();
    var positionVal = positionObject.val();
    if (parseInt(positionVal) == 0) {
        positionObject.parent().append(messageRequired);
    }
}

/**
 * Change number resource
 */
function numberInput(elem) {
    var numberObject = $(elem);
    numberObject.parent().find('.error').remove();
    var val = numberObject.val().trim();
    if (!val || parseInt(val) <= 0) {
        numberObject.parent().append(messageRequired);
    }
}

/**
 * Get position selected of team
 * @param {element} teamContainer
 * @returns {Array}
 */
function getPositionSelected(boxContainer) {
    var positionSelected = [];
    boxContainer.find(boxPositionClass).not('.hidden').each(function() {
        var val = $(this).find(positionApplyClass).val();
        if (parseInt(val) != 0) {
            positionSelected.push(val);
        }
    });
    return positionSelected;
}

function getTeamSelected() {
    var teamSelected = [];
    $teamsContainer.find(boxTeamClass).not('.hidden').each(function() {
        var val = $(this).find(selectTeamClass).val();
        if (parseInt(val) != 0) {
            teamSelected.push(val);
        }
    });
    return teamSelected;
}

$('.save-team').on('click', function() {
    var valid = validateTeam();
    if (valid) {
        $('input.team-val').remove();
        var strTeamSelected = '';
        $teamsContainer.find(boxTeamClass).each(function() {
            if ($(this).hasClass('hidden')) {
                $(this).remove();
            } else {
                $(this).attr('data-has', 1); // Mark saved
                var teamObject = $(this).find(selectTeamClass);
                var teamVal = teamObject.val();
                var teamLabel = teamObject.find('option:selected').text().trim();
                $(this).find(boxPositionClass).each(function() {
                    if ($(this).hasClass('hidden')) {
                        $(this).remove();
                    } else {
                        $(this).attr('data-has', 1); // Mark saved
                        var positionObject = $(this).find(positionApplyClass);
                        var numberObject = $(this).find(numberClass);
                        var positionVal = positionObject.val();
                        var numberVal = numberObject.val().trim();
                        $('#form-create-request').prepend('<input class="team-val" type="hidden" name="teams['+teamVal+']['+positionVal+']" value="'+numberVal+'" />');
                    }
                });
                if (strTeamSelected == '') {
                    strTeamSelected += teamLabel;
                } else {
                    strTeamSelected += ', ' + teamLabel;
                }
            }
        });
        $selectTeam.val(strTeamSelected);
        $modalTeam.modal('hide');
        //Input check required
        $('#select_team').val(strTeamSelected);
        $('#select_team-error').remove();
    }

});

/**
 * Validate team of request
 * @returns {Boolean}
 */
function validateTeam() {
    var valid = true;
    $teamsContainer.find('.error').remove();
    if ($teamsContainer.find(boxTeamClass).length == 0) {
        valid = false;
    }
    $teamsContainer.find(boxTeamClass).not('.hidden').each(function() {
        var teamObject = $(this).find(selectTeamClass);
        var teamVal = teamObject.val();
        if (parseInt(teamVal) == 0) {
            valid = false;
            teamObject.parent().append(messageRequired);
        }
        $(this).find(boxPositionClass).not('.hidden').each(function() {
            var positionObject = $(this).find(positionApplyClass);
            var numberObject = $(this).find(numberClass);
            var positionVal = positionObject.val();
            var numberVal = numberObject.val().trim();
            if (parseInt(positionVal) == 0) {
                valid = false;
                positionObject.parent().append(messageRequired);
            }
            if (!numberVal || parseInt(numberVal) <= 0) {
                valid = false;
                numberObject.parent().append(messageRequired);
            }
        });
    });
    return valid;
}

/**
 * Delete row position
 */
function removePosition(elem) {
    $(elem).closest(boxPositionClass).addClass('hidden');
    var boxContainer = $(elem).closest('.box');
    refreshOptionPosition(boxContainer);
    delBtnRemovePosition();
}

/**
 * Delete box team
 * @param {element} elem
 */
function removeTeam(elem) {
    $(elem).closest(boxTeamClass).addClass('hidden');
    refreshOptionTeam()
    delBtnRemoveTeam();
}

/**
 * Refresh Position option disabled true|false
 * @param {element} boxContainer
 */
function refreshOptionPosition(boxContainer) {
    var positionSelected = getPositionSelected(boxContainer);
    boxContainer.find(positionApplyClass).not('.hidden').each(function() {
        $(this).find('option').prop('disabled', false); //enabled all option
        var val = $(this).val();
        var countSelected = positionSelected.length;
        for (var i=0; i< countSelected; i++) {
            if (positionSelected[i] != val) {
                $(this).find('option[value='+positionSelected[i]+']').prop('disabled', true);
            }
        }
        $(this).select2();
    });
}

/**
 * Refresh Team option disabled true|false
 */
function refreshOptionTeam() {
    var teamSelected = getTeamSelected();
    $teamsContainer.find(selectTeamClass).not('.hidden').each(function() {
        $(this).find('option[option!=1]').prop('disabled', false); //enabled all option is team childest
        var val = $(this).val();
        var countSelected = teamSelected.length;
        for (var i=0; i< countSelected; i++) {
            if (teamSelected[i] != val) {
                $(this).find('option[value='+teamSelected[i]+']').prop('disabled', true);
            }
        }
        selectSearchReload();
    });
}

function delBtnRemovePosition() {
    $teamsContainer.find(boxTeamClass).not('.hidden').each(function() {
        if ($(this).find(boxPositionClass).not('.hidden').length == 1) {
            $(this).find(btnRemovePositionClass).addClass('hidden');
        } else {
            $(this).find(btnRemovePositionClass).removeClass('hidden');
        }
    });
}

function delBtnRemoveTeam() {
    if ($teamsContainer.find(boxTeamClass).not('.hidden').length == 1) {
        $teamsContainer.find(btnRemoveTeamClass).addClass('hidden');
    } else {
        $teamsContainer.find(btnRemoveTeamClass).removeClass('hidden');
    }
}

$('#set-content').on('click', function() {
    $('#modal-content').modal('show');
});

$('.save-content').on('click', function() {
    var content = CKEDITOR.instances.content.getData();
    $('#set_content').val(content);
    $('#set_content-error').remove();
    if (!content) {
        $('#set-content').attr('placeholder', addContentText);
        $('#set_content').after('<label id="set_content-error" class="error" for="set_content" style="display: block;">'+requiredText+'</label>');
    } else {
        $('#set-content').attr('placeholder', updateContentText);
    }
});


function getDataRequest() {
    var form = $('#form-create-request');
    var data = {
        title: form.find('input[name="title"]').val(),
        is_hot: form.find('input[name="is_hot"]').val(),
        position: data,
        request_id: form.find('input[name="request_id"]').val(),
        expired: form.find('input[name="deadline"]').val(),
        place: form.find('select[name="location"]').val(),
        salary: form.find('input[name="salary"]').val(),
        description: form.find('textarea[name="description"]').val(),
        benefits:form.find('textarea[name="benefits"]').val(),
        qualifications: form.find('textarea[name="job_qualifi"]').val(),
        _token: form.find('input[name="_token"]').val(),
        programs: data,
        types: data,
        status_request: form.find('select[name="status"]').val(),
    };
    $('.teams-container .box-position').each(function () {
        var position = $(this).find('select.position-apply').val();
        var number = $(this).find('.number-resource').val();
        data['positions['+position+']'] = number;
    });
    var programs = form.find('select[name="programs[]"]').val();
    var types = form.find('select[name="typecandidate[]"]').val();
    if (programs) {
        data['programs'] = programs.join(',');
    }
    if (types) {
        data['types'] = types.join(',');
    }
    return data;
}

$('.btn-publish-ok').click(function (event) {
    var formValue = $('div#input_value');
    var data = {
        title: formValue.find('#title').val(),
        hot: formValue.find('#is_hot').val(),
        position: data,
        request_id: requestId,
        expired: formValue.find('#deadline').val(),
        place: formValue.find('#location').val(),
        salary: formValue.find('#salary').val(),
        description: formValue.find('#description').val(),
        benefits: formValue.find('#benefits').val(),
        programs: formValue.find('#programs').val(),
        types: formValue.find('#types').val(),
        qualifications: formValue.find('#job_qualifi').val(),
        _token: _token,
        status_request: formValue.find('#status').val(),
        publish: true,
    };

    $('.teams-container .box-position').each(function () {
        var position = $(this).find('select.position-apply').val();
        var number = $(this).find('.number-resource').val();
        data['positions['+position+']'] = number;
    });
    var programs = formValue.find('#programs').val();
    var types = formValue.find('#types').val();

    $.ajax({
        type: 'POST',
        url: urlPostRequest,
        data: data,
        error: function() {
            bootbox.alert("Error. Please try later.");
        },
        success: function () {
            bootbox.alert('Publish request success');
            $('.btn-publish-request').text('Published').prop("disabled", true);
        }
    });
    $('#modal-publish-request').modal("hide");
});

$('.btn-publish-recruitment').click(function (event) {
    var formValue = $('div#input_value');
    var data = {
        title: formValue.find('#title').val(),
        hot: formValue.find('#is_hot').val(),
        position: data,
        request_id: requestId,
        expired: formValue.find('#deadline').val(),
        request_date: formValue.find('#request_date').val(),
        place: formValue.find('#location').val(),
        salary: formValue.find('#salary').val(),
        description: formValue.find('#description').val(),
        benefits: formValue.find('#benefits').val(),
        programs: formValue.find('#programs').val(),
        types: formValue.find('#types').val(),
        qualifications: formValue.find('#job_qualifi').val(),
        _token: _token,
        status_request: formValue.find('#status').val(),
        publish: true,
    };

    $('.teams-container .box-position').each(function () {
        var position = $(this).find('select.position-apply').val();
        var number = $(this).find('.number-resource').val();
        data['positions['+position+']'] = number;
    });

    $.ajax({
        type: 'POST',
        url: urlPostRequestRecruitment,
        data: data,
        error: function() {
            bootbox.alert("Error. Please try later.");
        },
        success: function () {
            bootbox.alert('Publish request success');
            // $('.btn-publish-request').text('Published').prop("disabled", true);
        }
    });
    $('#modal-publish-request').modal("hide");
});

