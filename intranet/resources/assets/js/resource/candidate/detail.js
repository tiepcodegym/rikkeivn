$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
});

/**
 * Init editor
 */
$(document).ready(function () {
    CKEDITOR.config.height = 400;
    // RKfuncion.CKEditor.init(['invite_letter_editor']);
    editorInviteLetter = CKEDITOR.replace('invite_letter_editor');
    editorInviteLetter.on('change', function () {
        var content_change = CKEDITOR.instances.invite_letter_editor.getData(),
            mail_offer = $('body').find("input[name='mail_offer']:checked").val(),
            nameLetter = 'inviteLetterVN_'+candidateId;
        if (mail_offer == typeMailOfferJP) {
            nameLetter = 'inviteLetterJP_'+candidateId;
        }
        localStorage.setItem(nameLetter, content_change);
    });
});

$(document).ready(function () {
    var $startWorkingDate = $('#start_working_date');
    var $trialWorkStartDate = $('#trial_work_start_date');
    var $trialWorkEndDate = $('#trial_work_end_date');
    var $test_plan = $('#test_plan');
    var $interview_plan = $('#interview_plan');
    var $interview2_plan = $('#interview2_plan');
    var $officialDate = $('#official_date');
    var $workingType = $('#working_type');
    var startDateLabel = {};
    startDateLabel[workingTypeBorrow] = outsourcingStartDate;
    startDateLabel[workingTypeSeasonal] = seasonalStartDate;
    startDateLabel[workingTypeProbation] = trialStartDate + ' <em class="required" aria-required="true">*</em>';
    var endDateLabel = {};
    endDateLabel[workingTypeBorrow] = outsourcingEndDate;
    endDateLabel[workingTypeSeasonal] = seasonalEndDate;
    endDateLabel[workingTypeProbation] = trialEndDate  + ' <em class="required" aria-required="true">*</em>';

    RKfuncion.keepStatusTab.init();

    jQuery.validator.addMethod("validateTestMark", function (value) {
        var result = parseInt($('#test_result').val());
        if (result == 0) {
            return true;
        }
        if (result == parseInt(resultFail)) {
            var testDate = $('#test_date').val().trim();
            if (!testDate) {
                return true;
            } else {
                if (value.trim()) {
                    return true;
                }
            }
        } else {
            if (value.trim()) {
                return true;
            }
        }
        return false;
    }, requiredText);

    $.validator.addMethod("testDateRequired", function (value)
    {
        var mark = $.trim($('#test_mark').val());
        var result = parseInt($('#test_result').val());
        if (mark || result == resultPass) {
            if (!value) {
                return false;
            }
        }
        return true;
    });

    $.validator.addMethod("rikkeiEmailFormat", function (value, element, check) {
        if (check) {
            var test = /@rikkeisoft.com$/.test(value);
            return test;
        }
        return true;
    });

    jQuery.validator.addMethod('greaterThan', function(value, element, param) {
        if (value && $(param).val()) {
            return this.optional(element) || value >= $(param).val();
        }
        return true;
    }, errorStartDateBefore);

    $('.date').datetimepicker({
        format: 'YYYY-MM-DD H:mm',
    });

    $('#start_working_date_interview').datetimepicker({
        format: 'YYYY-MM-DD H:mm',
    });

    $test_plan.datetimepicker({
        format: 'YYYY-MM-DD H:mm',
    });
    $interview_plan.datetimepicker({
        format: 'YYYY-MM-DD H:mm',
    });
    $interview2_plan.datetimepicker({
        format: 'YYYY-MM-DD H:mm',
    });
    //date time picker
    $('.field-date-picker').each(function () {
        $(this).attr('autocomplete', 'off');
        var format = $(this).data('format') || 'YYYY-MM-DD';
        $(this).datetimepicker({
            format: format,
            useCurrent: false,
        });
    });

    //validate tab test
    $("#form-test-candidate").validate({
        rules: {
            test_mark: {validateTestMark: true},
            test_mark_specialize: {validateTestMark: true},
            test_plan: {testDateRequired: true}
        },
        messages: {
            test_mark: {validateTestMark: requiredText},
            test_mark_specialize: {validateTestMark: requiredText},
            test_plan: {testDateRequired: requiredText}
        },
        highlight: function(element) {
            var inputGroup = $(element).closest('.input-group');
            if (inputGroup.length > 0) {
                element = inputGroup;
            }
            $(element).addClass('field-error');
        },
        unhighlight: function(element) {
            var inputGroup = $(element).closest('.input-group');
            if (inputGroup.length > 0) {
                element = inputGroup;
            }
            $(element).removeClass('field-error');
        },
    });

    //validate tab interview
    $('#interviewer').change(function () {
        var value = $(this).val();
        if (value == null) {
            $('#chk_interviewer').val('');
        } else {
            $('#chk_interviewer').val(1);
            $('#chk_interviewer-error').remove();
        }
    });
    $('#candidate_request').change(function () {
        var value = $(this).val();
        if (value == null) {
            $('#chk_request').val('');
        } else {
            $('#chk_request').val(1);
            $('#chk_request-error').remove();
        }
    });
    $("#form-interview-candidate").validate({
        rules: {
            chk_interviewer: 'required',
            chk_request: 'required',
        },
        messages: {
            chk_interviewer: requiredText,
            chk_request: requiredText,
        }
    });

    var listMessages = {
        request_id: {valueNotEquals: requiredText},
        team_id: {valueNotEquals: requiredText},
        position_apply: {valueNotEquals: requiredText},
        working_type: {valueNotEquals: requiredText},
        contract_length: {required: requiredText},
        programming_language_id : {valueNotEquals: requiredText},
        trial_work_start_date: {greaterThan: errorTrialStartDateBefore},
        trial_work_end_date: {greaterThan: errorTrialEndDateBefore},
        official_date: {greaterThan: errorOfficialDateBefore},
    };

    var listRules = {
        request_id: {valueNotEquals: "0"},
        team_id: {valueNotEquals: "0"},
        position_apply: {valueNotEquals: "0"},
        programming_language_id : {
            valueNotEquals: function () {
                if ($('#position_apply').val() == idPosDev) {
                    $('#programming_language_required').removeClass('hidden');
                    return "0";
                } else {
                    $('#programming_language_required').addClass('hidden');
                    return '';
                }
            }
        },
        start_working_date: {
            required: function () {
                return $('#offer_result option:selected').val() != resultFail;
            }
        },
        /*end_working_date: {
            greaterThan: '#start_working_date',
        },*/
        trial_work_start_date: {
            required: function () {
                return $('#offer_result option:selected').val() != resultFail;
            }
        },
        trial_work_end_date: {
            required: function () {
                return $('#offer_result option:selected').val() != resultFail;
            }
            // greaterThan: '#trial_work_start_date',
        },
        working_type: {valueNotEquals: "0"},
        contract_length: {
            required: function () {
                return $('#working_type').val() != workingTypeUnlimit;
            },
        },
        offer_date: {
            required: function () {
                return ($('#offer_result').val() != resultFail);
            }
        },
        offer_feedback_date: {
            required: function () {
                return ($('#offer_result').val() != resultFail);
            }
        },
        trainee_start_date: {
            required: function () {
                return ($('#offer_result').val() != resultFail && $('#offer_result').val() != resultFail);
            },
        },
        trainee_end_date: {
            required: function () {
                return ($('#offer_result').val() != resultFail && $('#offer_result').val() != resultFail);
            },
            greaterThan: '#trainee_start_date',
        },
        official_date: {
            required: function () {
                return ($('#offer_result').val() != resultFail);
            },
        },
    };
    $('#position_apply').on('change',function (){
        if ($(this).val() == idPosDev) {
            $('#programming_language_id').rules("add", {valueNotEquals:"0"});
            $('#programming_language_required').removeClass('hidden');
        } else {
            $('#programming_language_id').rules("remove", 'valueNotEquals');
            $('#programming_language_required').addClass('hidden');
        }
    })
    //validate tab offer
    $("#form-offering-candidate").validate({
        messages: listMessages,
        rules: listRules,
    });

    $("#form-offering-candidate").find('input').on('dp.change', function () {
        $("#form-offering-candidate").valid();
    });
    $("#form-offering-candidate").find('input, select').on('change', function () {
        $("#form-offering-candidate").valid();
    });

    $('#employee_card_id').keyup(function() {
        $('#employee_code').next('#employee_code-error').remove();
        var empCode = empCodePrefix + getEmpCodeFromCardId($(this).val(), isExtraEmpCode);
        if (empCode.match(new RegExp(prefixPartner, 'g')) === null) {
            empCode = empCode.substr(0, 10);
        }
        $('#employee_code').text(empCode);
        $('input[name="employee_code"]').val(empCode);
    });

    function getEmpCodeFromCardId(cardId, isExtraCode) {
        if (parseInt(isExtraCode)) {
            return cardId;
        }
        if (cardId < 10) {
            return '000000' + cardId;
        } else if (cardId < 100) {
            return '00000' + cardId;
        } else if (cardId < 1000) {
            return '0000' + cardId;
        } else if (cardId < 10000) {
            return '000' + cardId;
        } else if (cardId < 100000) {
            return '00' + cardId;
        } else if (cardId < 1000000) {
            return '0' + cardId;
        } else {
            return '' + cardId;
        }
    }
    $('#form-employee-candidate select[name="status"]').change(function () {
        var statusSelected = $('#form-employee-candidate select[name="status"]').val() || 0;
        if (parseInt(statusSelected) === 11)
        {
            $('#box-contract-team').show();
        } else
        {
            $('#box-contract-team').hide();
        }
        var status = $(this).val();
        $lbCardNum = $('label[data-label="id_card_number"]');
        $lbCardDate = $('label[data-label="id_card_date"]');
        $lbCardPlace = $('label[data-label="id_card_place"]');
        $lbCardAddress = $('label[data-label="native_addr"]');
        $lbEmail = $('label[data-label="email"]');
        $lbCard = $('label[data-label="employee_card_id"]');
        $lbCode = $('label[data-label="employee_code"]');
        if (status == '' || status == statusFail) {
            $lbCardNum.removeClass('required-text');
            $lbCardDate.removeClass('required-text');
            $lbCardPlace.removeClass('required-text');
            $lbCardAddress.removeClass('required-text');
            $lbEmail.removeClass('required-text');
            $lbCard.removeClass('required-text');
            $lbCode.removeClass('required-text');
            $('#form-employee-candidate .error').remove();
        } else {
            $lbCardNum.addClass('required-text');
            $lbCardDate.addClass('required-text');
            $lbCardPlace.addClass('required-text');
            $lbCardAddress.addClass('required-text');
            $lbEmail.addClass('required-text');
            $lbCard.addClass('required-text');
            $lbCode.addClass('required-text');
        }
    });
    //validate tab employee
    $('#form-employee-candidate').validate({
        ignore: '',
        rules: {
            status: 'required',
            'contract_team_id': {
                required: function ()
                {
                    var statusSelected = $('#form-employee-candidate select[name="status"]').val() || 0;
                    return (parseInt(statusSelected) === statusPreparing) ? true : false;
                }
            },
            'employee[email]': {
                required: function () {
                    var statusSelected = $('#form-employee-candidate select[name="status"]').val() || 0;
                    return (($('#offer_result').val() == offerWorkingValue
                            || $('#offer_result').val() == offerPassValue)
                            && (jQuery.inArray(parseInt(statusSelected), [statusPreparing, statusWorking]) !== -1));
                },
                rikkeiEmailFormat: function () {
                    var statusSelected = $('#form-employee-candidate select[name="status"]').val() || 0;
                    return !$('#is_old_member').is(':checked') && jQuery.inArray(parseInt(statusSelected), [statusPreparing, statusWorking]) !== -1;
                },
                email: true,
            },
            'employee[id_card_number]': {
                required: function () {
                    var statusSelected = $('#form-employee-candidate select[name="status"]').val() || 0;
                    return (($('#offer_result').val() == offerWorkingValue
                            || $('#offer_result').val() == offerPassValue)
                            && (jQuery.inArray(parseInt(statusSelected), [statusPreparing, statusWorking]) !== -1));
                },
            },
            'employee[id_card_date]': {
                required: function () {
                    var statusSelected = $('#form-employee-candidate select[name="status"]').val() || 0;
                    return (($('#offer_result').val() == offerWorkingValue
                            || $('#offer_result').val() == offerPassValue)
                            && (jQuery.inArray(parseInt(statusSelected), [statusPreparing, statusWorking]) !== -1));
                },
            },
            'employee[id_card_place]': {
                required: function () {
                    var statusSelected = $('#form-employee-candidate select[name="status"]').val() || 0;
                    return (($('#offer_result').val() == offerWorkingValue
                            || $('#offer_result').val() == offerPassValue)
                            && (jQuery.inArray(parseInt(statusSelected), [statusPreparing, statusWorking]) !== -1));
                },
            },
            'employee[contact][native_addr]': {
                required: function () {
                    var statusSelected = $('#form-employee-candidate select[name="status"]').val() || 0;
                    return (($('#offer_result').val() == offerWorkingValue
                            || $('#offer_result').val() == offerPassValue)
                            && (jQuery.inArray(parseInt(statusSelected), [statusPreparing, statusWorking]) !== -1));
                },
            },
            'employee[employee_card_id]': {
                required: function () {
                    var statusSelected = $('#form-employee-candidate select[name="status"]').val() || 0;
                    return (($('#offer_result').val() == offerWorkingValue
                            || $('#offer_result').val() == offerPassValue)
                            && (jQuery.inArray(parseInt(statusSelected), [statusPreparing, statusWorking]) !== -1));
                },
                remote: {
                    url: urlcheckExistEmpPropertyValue,
                    type: "post",
                    data: {
                        field: "card_id",
                        candidateid: function () {
                            return $("input[name='candidate_id']").val();
                        },
                        valueid: function () {
                            return $("#employee_card_id").val();
                        },
                        old_employee_id: function () {
                            return $('#emp_old_email').val() ? $('#emp_old_email').val() : 0;
                        },
                    },
                    dataFilter: function (response) {
                        response = JSON.parse(response);
                        if (response.status === 1) {
                            return true;
                        }
                        if (response.status === 0) {
                            if (response.card_id) {
                                var suggestText = $('#employee_card_id').siblings('.text-desc').text();
                                suggestText = suggestText.substring(0, suggestText.indexOf(':') + 2) + response.card_id;
                                $('#employee_card_id').siblings('.text-desc').text(suggestText);
                            }
                            return false;
                        }
                    },
                }
            },
            old_employee_id: {
                required: function () {
                    return $('#is_old_member').is(':checked');
                },
            },
        },
        messages: {
            'employee[email]': {
                rikkeiEmailFormat: emailFormat,
                remote: jQuery.validator.format("Email {0} is already taken."),
            },
            'employee[employee_card_id]': {remote: jQuery.validator.format("Employee code is already taken.")},
        },
        errorPlacement: function(error, element) {
            if (element.attr("name") === "employee[employee_card_id]" ) {
                error.insertAfter("#employee_code");
            } else {
                error.insertAfter(element);
            }
        },
    });

    $('#btn_submit_employee').on('click', function (e) {
        var btn = $(this);
        var form = btn.closest('form');
        var valid = form.valid();
        if (!valid) {
            e.stopImmediatePropagation();
            return false;
        }
        var status = form.find('select[name="status"]').val();
        var confirmStatus = btn.data('status');
        var message = '';
        if (confirmStatus.indexOf(parseInt(status)) < 0) {
            message = btn.data('other-noti');
        } else {
            message = btn.data('noti');
        }
        //check change old member
        if ($('#is_old_member').is(':checked')) {
            var aryChangeTitle = [];
            form.find('.check-change').each(function () {
                var oldValue = $(this).attr('data-value');
                var newValue = $(this).val();
                if ($(this).find('option').length > 0) {
                    newValue = $(this).find('option:selected').text();
                }
                if (oldValue != newValue) {
                    var fieldChange = $(this).attr('data-title') + ': ' + (oldValue ? oldValue : 'NULL') + ' --> ' + newValue;
                    aryChangeTitle.push(fieldChange);
                    var name = $(this).attr('name');
                    if (name == 'old_employee_id') {
                        aryChangeTitle.push(textFieldChangeOldEmp);
                    }
                }
            });
            if (aryChangeTitle.length > 0) {
                message += "<br /><br />" + aryChangeTitle.join('<br />');
            }
        }
        bootbox.confirm({
            message: message,
            className: 'modal-warning',
            buttons: {
                confirm: {
                    label: confirmYes,
                },
                cancel: {
                    label: confirmNo,
                },
            },
            callback: function (result) {
                if (result) {
                    form[0].submit();
                    btn.prop('disabled', true);
                }
            }
        });
        return false;
    });

    if (workingType == workingInternship || $.inArray(parseInt(workingType), typeNotRequireTrial) !== -1) {
        if (workingType == workingInternship) {
            $('#email').rules("remove", "required");
            $('#email').rules("remove", "rikkeiEmailFormat");
            $('#email').prev().find('em').remove();
            $('#employee_card_id').rules("remove", "required");
            $('#employee_card_id').rules("remove", "rikkeiEmailFormat");
            $('#employee_card_id').prev().find('em').remove();
            $('#start_working_date').rules("remove", "required");
            $('#start_working_date').parent().parent().prev().find('em').remove();
        }
    }

    $('#working_type').change(function () {
        $("#form-offering-candidate").validate().resetForm();
        var workingType = $(this).val();
        toggleGroupContract(workingType);

        $("#trial_work_start_date").closest(".form-group").children("label").html(startDateLabel[workingType]);
        $("#trial_work_end_date").closest(".form-group").children("label").html(endDateLabel[workingType]);

        if (workingType == workingInternship || $.inArray(parseInt(workingType), typeNotRequireTrial) !== -1) {
            if (workingType == workingInternship) {
                $('#email').rules("remove", "required");
                $('#email').rules("remove", "rikkeiEmailFormat");
                $('#email').prev().find('em').remove();
                $('#employee_card_id').rules("remove", "required");
                $('#employee_card_id').rules("remove", "number");
                $('#employee_card_id').prev().find('em').remove();
                $('#start_working_date').rules("remove", "required");
                $('#start_working_date').parent().parent().prev().find('em').remove();
            }
        } else {
//            $('#email').rules("add", {
//                required: function () {
//                    return ($('#offer_result').val() == offerWorkingValue
//                            || $('#offer_result').val() == offerPassValue);
//                },
//                rikkeiEmailFormat: function () {
//                    return !$('#is_old_member').is(':checked');
//                },
//                email: true
//            });
//            if ($('#email').prev().find('em').length == 0) {
//                $('#email').prev().append('<em class="required" aria-required="true">*</em>');
//            }
//            $('#employee_card_id').rules("add", {
//                number: true,
//                required: function () {
//                    return ($('#offer_result').val() == offerWorkingValue
//                            || $('#offer_result').val() == offerPassValue);
//                },
//            });
//            if ($('#employee_card_id').prev().find('em').length == 0) {
//                $('#employee_card_id').prev().append('<em class="required" aria-required="true">*</em>');
//            }
            $('#start_working_date').rules("add", {
                required: function () {
                    return $('#offer_result option:selected').val() != resultFail;
                }
            });
            if ($('#start_working_date').parent().parent().prev().find('em').length == 0) {
                $('#start_working_date').parent().parent().prev().append('<em class="required" aria-required="true">*</em>');
            }
        }
        //required contract length
        if (workingType != workingTypeUnlimit) {
            if ($('#contract_length').closest('p').find('label em').length < 1) {
                $('#contract_length').closest('p').find('label').append('<em class="required" aria-required="true">*</em>');
            }
        } else {
            $('#contract_length').closest('p').find('label em').remove();
        }

        if ($('#working_type').val() == 2) {
            $('#contract_length').val('');
            $('#contract_length_wrapper').css({'display': 'none'});
        } else {
            $('#contract_length_wrapper').css({'display': 'inline'});
        }
        //if working type probation then required start/end trial working date
        if ($workingType.val() == workingTypeProbation) {
            $trialWorkStartDate.rules('add', {
                greaterThan: '#start_working_date',
            });
            $trialWorkEndDate.rules('add', {
                greaterThan: '#trial_work_start_date',
            });
            $officialDate.rules('add', {
                greaterThan: '#start_working_date',
            });
            $trialWorkStartDate.rules('add', {
                required: true,
            });
            $trialWorkStartDate.closest('.form-group').find('.required').removeClass('hidden');
            $trialWorkEndDate.rules('add', {
                required: true,
            });
            $trialWorkEndDate.closest('.form-group').find('.required').removeClass('hidden');
        } else {
            $trialWorkStartDate.rules('remove', 'greaterThan');
            $trialWorkStartDate.rules('remove', 'required');
            $trialWorkStartDate.closest('.form-group').find('.required').addClass('hidden');
            $trialWorkEndDate.rules('remove', 'greaterThan');
            $trialWorkEndDate.rules('remove', 'required');
            $trialWorkEndDate.closest('.form-group').find('.required').addClass('hidden');
            $officialDate.rules('remove', 'greaterThan');
        }
    });

    /** show/hide group contract **/
    function toggleGroupContract(contractType) {
        contractType = parseInt(contractType);
        $('.group-contract').each(function () {
            var listTypes = $(this).data('contract') || [];
            if (listTypes.indexOf(contractType) > -1) {
                $(this).removeClass('hidden');
                $(this).find('input').prop('disabled', false);
            } else {
                $(this).addClass('hidden');
                $(this).find('input').prop('disabled', true);
            }
        });
    }

    if (candidateStatus == offerPassValue) {
        $('#email').rules("add", {
            remote: {
                url: urlcheckExistEmpPropertyValue,
                type: "post",
                data: {
                    field: "email",
                    candidateid: function () {
                        return $("input[name='candidate_id']").val();
                    },
                    valueid: function () {
                        return $("#email").val();
                    },
                    old_employee_id: function () {
                        return $('#emp_old_email').val() ? $('#emp_old_email').val() : 0;
                    },
                },
                dataFilter: function (response) {
                    response = JSON.parse(response);
                    if (response.status === 1) {
                        return true;
                    }
                    if (response.status === 0) {
                        if (response.email) {
                            var suggestText = $('.emp-new-email .text-desc').text();
                            suggestText = suggestText.substring(0, suggestText.indexOf(':') + 2) + response.email;
                            $('.emp-new-email .text-desc').text(suggestText);
                        }
                        return false;
                    }
                },
            },
        });
    } else{
        $('#email').rules("remove", "remote");
    }

    //event change start_working_date, set trial_work_start_date = this;
    $startWorkingDate.on('dp.change', function () {
        if (!isGenerateDate()) {
            return;
        }
        $trialWorkStartDate.val($startWorkingDate.val()).trigger('change');
    });

    //event change trial work start date, set official date = start working date
    $trialWorkStartDate.on('dp.change', function () {
        if (!isGenerateDate()) {
            return;
        }
        var date = $(this).val();
        var trialEndDate = null;
        if (!date || date === '') {
            if (!$officialDate.is(':disabled')) {
                $officialDate.val($startWorkingDate.val()).trigger('change');
            }
        } else {
            trialEndDate = new Date(date);
            var date2MonthsLater = new Date(date);
            date2MonthsLater.setDate(1);
            date2MonthsLater.setMonth(trialEndDate.getMonth() + 3);
            date2MonthsLater.setDate(0);

            if (trialEndDate.getDate() > 28 && trialEndDate.getDate() > date2MonthsLater.getDate()) {
                trialEndDate = date2MonthsLater;
            } else {
                trialEndDate.setMonth(trialEndDate.getMonth() + 2);
                trialEndDate.setDate(trialEndDate.getDate() - 1);
            }
        }
        if (!$trialWorkEndDate.is(':disabled')) {
            $trialWorkEndDate.val(trialEndDate ? trialEndDate.toDateString() : null).trigger('change');
        }
    });

    //event change trial working end date, set official date
    $trialWorkEndDate.on('dp.change', function () {
        if (!isGenerateDate()) {
            return;
        }
        var date = $(this).val();
        var officialDate = null;
        if (date) {
            officialDate = new Date(date);
            officialDate.setDate(officialDate.getDate() + 1);
        }
        if (!$officialDate.is(':disabled')) {
            $officialDate.val(officialDate ? officialDate.toDateString() : null).trigger('change');
        }
    });

    function isGenerateDate() {
        //parttime or borrow
        return ($('#working_type').val() != 3 && $('#working_type').val() != 6);
    }

});

Date.prototype.toDateString = function () {
    return this.getFullYear() + '-' + get2Digis(this.getMonth() + 1) + '-' + get2Digis(this.getDate());
};

function get2Digis(number){
    return number < 10 ? ('0' + number) : number;
}

/**
 * Show modal mail content
 * @param {int} type
 */
function showMailContent(type, typeContent) {
    var dom = getDom(type, typeContent);
    if (type == typeMailInterview) {
        for (var index in aryMailLocates) {
            var idLocate = aryMailLocates[index];
            var keyContent = 'interview_content_' + idLocate;
            var editorContent = CKEDITOR.instances[keyContent];
            if (typeof editorContent == 'undefined') {
                editorContent = RKfuncion.CKEditor.init([keyContent])[keyContent];
            }
            editorContent.setData($('.mail-interview_content_' + idLocate).html());
        }
    } else if (type == typeMailThanks) {
        for (var index in aryMailThanks) {
            var idLocate = aryMailThanks[index];
            var keyThanks = 'thanks_content';
            var editorContent = CKEDITOR.instances[keyThanks];
            if (typeof editorContent == 'undefined') {
                editorContent = RKfuncion.CKEditor.init([keyThanks])[keyThanks];
            }
            editorContent.setData($('.mail-thanks_' + idLocate).html());
        }
    } else if (type == typeMailOffer) {
        for (var i = 0; i < aryMailOffers.length; i++) {
           var idOffer = aryMailOffers[i];
           var keyOfferContent = 'offer_content_' + idOffer;
           var offerContent = CKEDITOR.instances[keyOfferContent];
           if (typeof offerContent == 'undefined') {
               offerContent = RKfuncion.CKEditor.init([keyOfferContent])[keyOfferContent];
           }
           offerContent.setData($('.mail-offer_content_' + idOffer).html());
        }
    } else if (type == typeMailFailInterview) {
        for (var i = 0; i < aryMailInterFails.length; i++) {
            var idIntFail = aryMailInterFails[i];
            var keyFailContent = 'interview_fail_content_' + idIntFail;
            var interviewFailContent = CKEDITOR.instances[keyFailContent];
            if (typeof interviewFailContent == 'undefined') {
                interviewFailContent = RKfuncion.CKEditor.init([keyFailContent])[keyFailContent];
            }
            interviewFailContent.setData($('.mail-interview_fail_content_' + idIntFail).html());
        }
    } else {
        var content = $('.mail-' + dom).html();
        var domEditor = CKEDITOR.instances[dom];
        if (typeof domEditor == 'undefined') {
            domEditor = RKfuncion.CKEditor.init([dom])[dom];
        }
        domEditor.setData(content);
    }
    $('#modal-' + dom).modal('show');
}

function showPdf(typeMailOffer) {
    if (typeMailOffer === typeMailOfferJP) {
        if (localStorage.getItem("inviteLetterJP_"+candidateId)) {
            CKEDITOR.instances.invite_letter_editor.setData(localStorage.getItem("inviteLetterJP_"+candidateId));
        } else {
            CKEDITOR.instances.invite_letter_editor.setData($('.invite-letter-content-jp').html());
        }
    } else {
        if (typeMailOffer === typeMailOfferHH3) {
            $('.invite-letter-content-vn .working-place').html('- ' + workingPlaceHH3);
        } else if (typeMailOffer === typeMailOfferHH4) {
            $('.invite-letter-content-vn .working-place').html('- ' + workingPlaceHH4);
        } else if (typeMailOffer === typeMailOfferDN) {
            $('.invite-letter-content-vn .working-place').html('- ' + workingPlaceDN);
        } else if (typeMailOffer === typeMailOfferHandico) {
            $('.invite-letter-content-vn .working-place').html('- ' + workingPlaceHandico);
        }
        if (localStorage.getItem("inviteLetterVN_"+candidateId)) {
            CKEDITOR.instances.invite_letter_editor.setData(localStorage.getItem("inviteLetterVN_"+candidateId));
        } else {
            CKEDITOR.instances.invite_letter_editor.setData($('.invite-letter-content-vn').html());
        }
    }
    $('#modal-invite-letter').modal('show');
    $('#modal-offer_content').modal('hide');
}

function closeInviteBody() {
    $('#modal-invite-letter').modal('hide');
    $('#modal-offer_content').modal('show');
}

function createPdf(type) {
    //Mail offer: create only pdf file when first click button show modal offer content
    if (type == typeMailOffer) {
        $('.btn-create-pdf').prop('disabled', true);
        $('.btn-create-pdf .fa-refresh').removeClass('hidden');
        var content = CKEDITOR.instances.invite_letter_editor.getData()
        $.ajax({
            url: urlPdfSave,
            type: 'post',
            dataType: 'html',
            data: {content: content},
            timeout: 30000,
            success: function (result) {
                if (parseInt(result) == 1) {
                    $('#load_invite').val('1');
                    $('#modal-invite-letter').modal('hide');
                    $('#modal-offer_content').modal('show');
                    $('#modal-offer_content label.invite-letter-label').html(', ' + inviteHtmlGenerate);
                    $('.btn-send-mail-offer').removeClass('hidden');
                    $('.btn-create-pdf').prop('disabled', false);
                    $('.btn-create-pdf .fa-refresh').addClass('hidden');
                    $('.invite-letter-content').html(content);
                }
            },
            error: function (x, t, m) {
                if (t == "timeout") {
                    alert("got timeout");
                } else {
                    alert('ajax fail to fetch data');
                }
            },
            complete: function () {

            },
        });
    }
}

/**
 * Send mail
 * @param {elemnet} elem
 * @param {int} type
 */
function send(elem, type, typeContent) {
    var $btnSend = $(elem);
    var $refresh = $btnSend.find('.fa-refresh');
    $refresh.removeClass('hidden');
    $btnSend.prop('disabled', true);
    var bodyText = '';
    var dom = getDom(type, typeContent);
    var data = {
        content: CKEDITOR.instances[dom].getData(),
        candidate_id: $('#candidate_id').val().trim(),
        type: type,
        candidate_fullname: $('#candidate_fullname').val().trim(),
    };
    if (type == typeMailRecruiter) {
        data['candidate_email'] = $('#recruiter_email').val().trim();
        data['mail_title'] = $('#mail-title').val();
        data['related_ids[]'] = $('#recruit_related').val();
    } else {
        data['candidate_email'] = $('#candidate_email').val().trim();
    }
    $.ajax({
        url: urlMailOffer,
        type: 'post',
        dataType: 'html',
        data: data,
        timeout: 30000,
        success: function (result) {

            if (JSON.parse(result).success === 1) {
                bodyText = mailSuccessText;
                $('#modal-notification').removeClass('modal-warning').addClass('modal-success');
                $('#modal-interview_content').modal('hide');
            } else if (JSON.parse(result).success === 0) {
                bodyText = mailSentText;
                $('#modal-notification').removeClass('modal-success').addClass('modal-warning');
            } else {
                bodyText = mailFailText;
                $('#modal-notification').removeClass('modal-success').addClass('modal-warning');
            }
            $('#modal-notification .modal-body').html(bodyText);
            $('#modal-notification').modal('show');
            $('#modal-mail-offer-confirm').modal('hide');
            $('.mail-' + dom).html(data.content);
            //Set value 0 to recreate invite mail when show modal offer
            $('#load_invite').val('0');
            //Close modal content mail after send mail
            $('#modal-' + dom).modal('hide');
            //render comment
            var created_by = userNameCurrent+' ('+emailCurrent+') ';
            $('div.cmt-wrapper strong.cmt-created_by').text(created_by);
            $('div.cmt-wrapper span.cmt-created_at').text(JSON.parse(result).created_at);
            $('div.cmt-wrapper .comment').text(JSON.parse(result).content);
            var commentHtml = $('.cmt-wrapper').html();
            $('.grid-data-query-table').prepend(commentHtml);
            var e = jQuery.Event("keypress");
            e.keyCode = $.ui.keyCode.ENTER;
            $('input[name="page"]').val(1).trigger(e);
        },
        error: function (x, t, m) {
            if (t == "timeout") {
                alert("got timeout");
            } else {
                alert('ajax fail to fetch data');
            }
        },
        complete: function () {
            $refresh.addClass('hidden');
            $btnSend.prop('disabled', false);
            if (typeContent == 'mail_fail_interview') {
                submitInterview();
            }
        },
    });
}

/**
 * Send mail confirm event
 * @param {element} elem
 * @param {int} type
 */
function sendMail(elem, type, typeContent) {
    var dom = getDom(type, typeContent);
    if (type == typeMailRecruiter) {
        var title = $('#mail-title').val();
        if (!title) {
            setRequiredContent(mailTitleRequired);
            return false;
        }
    }
    var content = CKEDITOR.instances[dom].document.getBody().getChild(0).getText().trim();
    if (!content) {
        setRequiredContent(mailFailRequiredText);
        return false;
    }
    var str = messageConfirm(type);
    $('#modal-mail-offer-confirm .modal-body').html(str);
    $('#modal-mail-offer-confirm .btn-send').attr('onclick', 'send(this, ' + type + (typeof typeContent != 'undefined' ? (', "' + typeContent + '"') : '') + ')');
    $('#modal-mail-offer-confirm').modal('show');
    
    var mail_offer = $('body').find("input[name='mail_offer']:checked").val();
    if (mail_offer == typeMailOfferJP) {
        localStorage.removeItem("inviteLetterJP_"+candidateId);
    } else {
        localStorage.removeItem("inviteLetterVN_"+candidateId);
    }
}

/**
 * Set content for modal confirm
 * @param {string} content
 * @returns {void}
 */
function setRequiredContent(content) {
    var bodyText = content;
    $('#modal-notification').removeClass('modal-success').addClass('modal-warning');
    $('#modal-notification .modal-body').html(bodyText);
    $('#modal-notification').modal('show');
    $btnSend.prop('disabled', false);
    $refresh.addClass('hidden');
}

function messageConfirm(type) {
    var str = '';
    if (type == typeMailOffer) {
        if (!teamId) {
            str += '<p>- Ứng viên chưa có team.</p>';
        }
        if (!startWorking) {
            str += '<p>- Ứng viên chưa có ngày bắt đầu làm việc.</p>';
        }
    }
    str += '<p>Bạn chắc chắn muốn gửi mail?</p>';
    return str;
}

/**
 * Get class by type
 * @param {type} type
 * @returns {String}
 */
function getDom(type, typeContent) {
    if (typeContent == 'undefined') {
        typeContent = '';
    }
    if (typeContent == 'mail_interview') {
        return 'interview_content' + (type == typeMailInterview ? '' : '_' + type);
    }
    if (typeContent == 'mail_offer') {
        return 'offer_content' + (type == typeMailOffer ? '' : '_' + type);
    }
    if (typeContent == 'mail_fail_interview') {
        return 'interview_fail_content' + (type == typeMailFailInterview ? '' : '_' + type);
    }
    if (typeContent == 'mail_thanks') {
        return 'thanks_content' + (type == typeMailThanks ? '' : '_' + type);
    }

    switch (type) {
        case typeMailTest:
            return 'test_content';
        case typeMailRecruiter:
            return 'recruiter_content';
        default:
            return '';
    }
}

$('input[name=mail_interview]').change(function () {
    var target = $(this).data('target');
    $('.container-interview').addClass('hidden');
    $('#' + target).parent().removeClass('hidden');
});

$('input[name=mail_interview_content]').change(function () {
    var val = this.value;
    var target = $(this).data('target');
    $('.container-interview').addClass('hidden');
    $('#' + target).parent().removeClass('hidden');
    $('.btn-send-mail-interview').attr('onclick', 'sendMail(this, ' + val + ', "mail_interview");');
});

$('input[name=mail_thanks]').change(function () {
    var val = this.value;
    var target = $(this).data('target');
    $('.container-thanks').addClass('hidden');
    $('#' + target).parent().removeClass('hidden');
    $('.btn-send-mail-thanks').attr('onclick', 'sendMail(this, ' + val + ', "mail_thanks");');
});

$('input[name=mail_offer]').change(function () {
    var val = this.value;
    var target = $(this).data('target');
    $('.container-offer').addClass('hidden');
    $('#' + target).parent().removeClass('hidden');
    $('.btn-send-mail-offer').attr('onclick', 'sendMail(this, ' + val + ', "mail_offer");');
    $('.btn-show-pdf').attr('onclick', 'showPdf(' + val + ');');
});

$('#offer_result').change(function () {
    var value = $(this).val();
    var classFollow = '.start_working_date-container, .trial_work_end_date-container, .request-container, .teams-container, .pos-container';
    if (parseInt(value) == parseInt(resultPass) || parseInt(value) == parseInt(offerWorkingValue)) {
        $(classFollow).removeClass('hidden');
    } else {
        $(classFollow).addClass('hidden');
    }
});

$('#btn-test-history').click(function () {
    $('#test-history-modal').modal('show');
});

$('#btn-test-ricode').click(function () {
    $('#test-ricode-modal').modal('show');
});

$(document).ready(function () {
    /**
     * Init table test history of candidate
     */
    $('#test-history-table').DataTable({
        processing: true,
        lengthChange: false,
        bFilter: false,
        serverSide: true,
        ajax: urlTestHistory,
        pageLength: 10,
        'oLanguage': dataLang,
        order: [[4, "desc"]],
        columns: [
            {data: 'name', name: 'name'},
            {data: 'total_corrects', name: 'total_corrects'},
            {data: 'total_answers', name: 'total_answers'},
            {data: 'total_questions', name: 'total_questions'},
            {data: 'created_at', name: 'created_at'},
            {data: 'link', name: 'link'},
        ]
    });

    $(document).on('change', 'select#test_result', function () {
        $('label[for=test_mark] em.required, label[for=test_plan] em.required').remove();
        $('#test_mark-error, #test_mark_specialize-error, #test_plan-error').remove();
        if ($(this).val() == resultPass) {
            $('label[for=test_mark], label[for=test_plan]').append(htmlRequired);
        }
    });

    //disable contract length input if type = internship
    if ($('#working_type > option:selected').val() == 2) {
        $('#contract_length').val('');
        $('#contract_length_wrapper').css({'display': 'none'});
    }

    $('#offer_result').change(function () {
        collapseOfferResult();
        $('label.error').remove();
    });

    $('button[type="submit"]:disabled').prop('disabled', false);
});

function collapseOfferResult() {
    var offer_result = $('#offer_result option:selected').val();
    if (offer_result== offerPassValue ||
            (employeeValidErrors == 1 && offer_result == offerPassValue)) {
        offerPassCollapse.collapse('show');
    } else {
        offerPassCollapse.collapse('hide');
    }
}

function sendMailTks(elem, toEmail, toName, candidateId)
{
    var $btnSend = $(elem);
    var $refresh = $btnSend.find('.fa-refresh');
    var $check = $btnSend.find('.fa-check');
    $refresh.removeClass('hidden');
    $btnSend.prop('disabled', true);
    $.ajax({
        url: urlMailThanks,
        type: 'post',
        dataType: 'html',
        data: { toEmail: toEmail, toName: toName, candidateId: candidateId },
        timeout: 30000,
        success: function (result) {
            $('.modal-success').modal('show');
            $check.removeClass('hidden');
        },
        error: function (x, t, m) {
            if (t == "timeout") {
                alert("got timeout");
            } else {
                alert('ajax fail to fetch data');
            }
            $btnSend.prop('disabled', false);
        },
        complete: function () {
            $refresh.addClass('hidden');
        },
    });
}

$('#btn_submit_offer').click(function (e) {
    if ($('#offer_result').val() != offerWorkingValue) {
        return true;
    }

    var form = $(this).closest('form');

    if (!form.valid()) {
        return false;
    }

    var btn = $(this);
    btn.prop('disabled', true);
    $.ajax({
        type: 'GET',
        url: urlCheckEmpMail,
        data: {
            email: form.find('#email').val(),
            employee_id: form.find('#employee_id').val()
        },
        success: function (data) {
            if (data.exists == '1') {
                var modalConfirm = $('#_modal_confirm');
                modalConfirm.find('.text-body').html(data.message);
                modalConfirm.modal('show');
                modalConfirm.find('.btn-ok').one('click', function () {
                    form[0].submit();
                });
            } else {
                form[0].submit();
            }
        },
        complete: function () {
            btn.prop('disabled', false);
        }
    });

    return false;
});

function submitInterview() {
    $('#form-interview-candidate').submit();
}

function submitInterviewClick() {
    if ($('#interview_result').val() == resultFail
        && !lastSendInterviewFail
        && $('#chk_interviewer').val()
        && $('#chk_request').val()
    ) {
        showMailContent(typeMailFailInterview, 'mail_fail_interview');
        return false;
    }

    if ($('#interview_result').val() != offerDefaultValue
        || !$('#chk_request').val()
        || !$('#chk_interviewer').val()
    ) {
        submitInterview();
        return false;
    } else {
        var modalConfirm = $('#modal-calendar-confirm');
        modalConfirm.modal('show');
        if (!calendarId || !eventId) {
            modalConfirm.find('.modal-body').html(messageCreateCalendarConfirm);
        } else {
            modalConfirm.find('.modal-body').html(messageUpdateCalendarConfirm);
        }
    }
}

$(document).on('click', '#modal-interview_fail_content .pull-left', function () {
    submitInterview();
});

function noShowFormCalendar() {
    $('#modal-calendar-confirm').modal('hide');
    $('#form-interview-candidate').submit();
}

function showFormCalendar() {
    $('#modal-calendar-confirm').modal('hide');
    $('#modal-calendar-create').modal('show');
    showCalendars(true);
}

select2Employees('#interviewer');

/**
 * Edit comment
 */
$(document).on('click', '.edit-comment', function (event) {
    event.preventDefault();
    var content = $(this).attr('data-content');
    var id = $(this).attr('data-id');
    $('.info-comment').removeClass('hidden');
    $('.button-action').addClass('hidden');
    $('.key_enter_submit_candidate').val(content);
    $("input[name=comment_id]").attr('value', id);
    $('#candidate_comment_submit').css('display', 'none');
    $('#candidate_comment_save').css('display', 'block');
    $('.key_enter_submit_candidate').css('box-shadow', '4px 4px 4px #666');
    $('.key_enter_submit_candidate').focus();
    formCandidateValid.resetForm();
});
$(document).on('click', '#candidate_comment_save', function (event) {
    event.preventDefault();
    var val = $('.key_enter_submit_candidate').val();
    if (val) {
        $('.key_enter_submit_candidate').css('box-shadow', 'none');
        $('.key_enter_submit_candidate').submit();
        $("input[name=comment_id]").attr('value', '');
        $('#candidate_comment_submit').css('display', 'block');
        $('#candidate_comment_save').css('display', 'none');
        $('.info-comment').addClass('hidden');
    } else {
        $('#comment-error').removeClass('hidden');
    }
});

// not working comment when press esc.
$(document).on('keyup', '.key_enter_submit_candidate', function(e) {
    if (e.keyCode === 27) {
        var commentInput = $("input[name=comment_id]");
        if (/^\d+$/.test(commentInput.val())) {
            $(this).val('');
            commentInput.attr('value', '');
            $('#candidate_comment_submit').css('display', 'block');
            $('#candidate_comment_save').css('display', 'none');
            $('.info-comment').addClass('hidden');
            $('.key_enter_submit_candidate').css('box-shadow', 'none');
            $('.button-action').removeClass('hidden');
            formCandidateValid.resetForm();
        }
    }
});

/**
 * Delete comment candidate.
 */
$(document).on('click', '.delete-comment', function (event) {
    event.preventDefault();
    var id = $(this).attr("data-id");
    bootbox.confirm({
        message: deleteCommentCandidateTextConfirm,
        className: 'modal-default',
        buttons: {
            cancel: {
                label: 'Cancel', className: 'pull-left',
            },
        },
        callback: function(result) {
            if (result) {
                $.ajax({
                    method: 'POST',
                    url: deleteCommentCandidate,
                    data: { id: id, _token: _token},
                    success: function (response) {
                        var e = jQuery.Event("keypress");
                        e.keyCode = $.ui.keyCode.ENTER;
                        $('input[name="page"]').val(1).trigger(e);
                    },
                });
            }
        },
    });
});

$('#is_old_member').click(function (e) {
    var checked = $(this).is(':checked');
    var newEmailGroup = $('.emp-new-email');
    var oldEmailGroup = $('.emp-old-email');
    var elOldEmpId = $('#emp_old_email');
    if (checked) {
        newEmailGroup.addClass('hidden');
        oldEmailGroup.removeClass('hidden');
    } else {
        newEmailGroup.removeClass('hidden');
        oldEmailGroup.addClass('hidden');
        elOldEmpId.val('').trigger('change');
    }
});

$('#emp_old_email').change(function () {
    var elThis = $(this);
    var url = elThis.data('change-url');
    var employeeId = elThis.val();
    var form = elThis.closest('form');
    if (!employeeId || elThis.hasClass('loading')) {
        return;
    }
    $.ajax({
        type: 'POST',
        url: url,
        data: form.serialize() + '&employee_id=' + employeeId + '&_token=' + form.find('[name="_token"]').val(),
        success: function (data) {
            $.each(data, function (key, value) {
                var fieldName = form.find('[name*="'+ key +'"]');
                fieldName.val(value);
                if (fieldName.hasClass('select-search')) {
                    fieldName.trigger('change');
                }
                if (key == 'employee_card_id') {
                    fieldName.trigger('keyup');
                }
                form.valid();
            });
            $('#employee_id').val(employeeId);
        },
        error: function (error) {
            elThis.val('').trigger('change');
            $('#employee_id').val('');
            bootbox.alert({
                message: error.responseJSON,
                className: 'modal-danger'
            });
        },
        complete: function () {
            elThis.removeClass('loading');
        },
    });
});

$(document).on('click', '.input-group-edit .btn-toggle-edit', function (e) {
    e.preventDefault();
    var titleEdit = $(this).attr('data-title-edit');
    var titleDisable = $(this).attr('data-title-disable');
    var formControl = $(this).closest('.input-group').find('.form-control');
    var isMultiselect = $(this).closest('.input-group').find('.multiselect').length > 0;
    if (formControl.is(':disabled')) {
        formControl.prop('disabled', false).trigger('change');
        if (isMultiselect) {
            formControl.multiselect('enable');
        }
        $(this).find('span').text(titleDisable);
        $(this).removeClass('btn-primary').addClass('btn-warning');
    } else {
        formControl.prop('disabled', true).trigger('change');
        if (isMultiselect) {
            formControl.multiselect('disable');
        }
        $(this).find('span').text(titleEdit);
        $(this).removeClass('btn-warning').addClass('btn-primary');
    }
});

$("#form-ricode-test").submit(function () {
    return false;
});

var formRicodeTest = $("#form-ricode-test").validate({
    rules: {
        level_easy: {
            digits: true
        },
        level_medium: {
            digits: true
        },
        level_hard: {
            digits: true
        },
        duration: {
            required: true,
            digits: true,
            min : 1
        }
    },
    messages: {
        level_easy: {
            digits: isDigits
        },
        level_medium: {
            digits: isDigits
        },
        level_hard: {
            digits: isDigits
        },
        duration: {
            required: requiredText,
            digits: isDigits,
            min : minDuration
        }
    }
});

function callApiRicode(btn, type) {
    if (typeof $("#form-ricode-test").valid !== 'function' || !$("#form-ricode-test").valid()) {
        return false;
    }
    
    var level_easy = parseInt($('.input-ricode input[name=level_easy]').val());
    var level_medium = parseInt($('.input-ricode input[name=level_medium]').val());
    var level_hard = parseInt($('.input-ricode input[name=level_hard]').val());
    level_easy = level_easy ? level_easy : 0
    level_medium = level_medium ? level_medium : 0
    level_hard = level_hard ? level_hard : 0
    if(level_easy + level_medium + level_hard < 1) {
        $('#message-ricode').html('<div class="alert alert-error" role="alert">'+minTotalEasyMediumHard+'</div>')
        return false;
    }
    
    var getRoute = $("#form-ricode-test").attr('action');
    var buttonSubmit = $('#form-ricode-test .modal-footer button')
    buttonSubmit.attr('disabled', true);

    $.ajax({
        type: 'post',
        url: getRoute,
        data: $("#form-ricode-test").serialize()+ '&type='+type,
        success:function(data){
            data = JSON.parse(data)
            buttonSubmit.attr('disabled', false)
            
            var message = '';
            var status = '';
            switch(data.status) {
                case 200:
                    message = type === 'create' ? createSuccess : updateSuccess;
                    status = 'success'
                    if(data && data['data'] && data['data']['password']) {
                        var emailCandiate = $("#email-candidate").text();
                        var ricode_app_url = $('input[name=ricode_app_url]').val();
                        $("#password-candidate-gen").html("\
                            <div> \
                                <p><a target='_blank' href="+ricode_app_url+">Account login ricode rikkei</a></p> \
                                <p><b>Username:</b> "+emailCandiate+"</p> \
                                <p><b>Password:</b> "+data['data']['password']+"</p> \
                            </div> \
                        ")
                    }
                    $('#form-ricode-test .modal-footer button').attr('name', 'action-is-update');
                    $("#create-ricode-test").text('Recreate');
                    break;
                case 12003:
                    message = errorEasyMax
                    status = 'error'
                    break;
                case 12004:
                    message = errorMediumMax
                    status = 'error'
                    break;
                case 12005:
                    message = errorHardMax
                    status = 'error'
                    break;
                case 5001:
                    message = overtimeTest
                    status = 'error'
                    break;
                default:
                    break;
            }
            $('#message-ricode').html('<div class="alert alert-'+status+'" role="alert">'+message+'</div>')
            setTimeout(function() {
                $('#message-ricode').html('')
            }, 6000)
        },
        error: function(error) {
            $('#message-ricode').html('<div class="alert alert-error" role="alert">'+SystemError+'</div>')
            buttonSubmit.attr('disabled', false)
        }
    });
}
$('#create-ricode-test').click(function (e) {
    e.preventDefault();
    callApiRicode($(this), 'create');
});
$('#update-ricode-test').click(function (e) {
    e.preventDefault();
    callApiRicode($(this), 'update');
});


function formatState(opt) {
    if (!opt.id) {
        return opt.text;
    }
    return $('<span><i class="fa fa-star-o ' + $(opt.element).attr('class') + '"></i> ' + opt.text + '</span>');
}
$("select[name='interested']").select2({
    templateResult: formatState,
    templateSelection: formatState,
});

$(document).on('change', '#contact_result, #test_result, #interview_result, #offer_result', function () {
    var interestedSelect = $(this).closest('form').find('.interested-input-container');
    $(this).val() === resultFail ? interestedSelect.removeClass('hidden') : interestedSelect.addClass('hidden') ;
});
$(document).on('change', '.employee-status', function () {
    var interestedSelect = $(this).closest('form').find('.interested-input-container');
    $(this).val() === (statusFail + '') ? interestedSelect.removeClass('hidden') : interestedSelect.addClass('hidden') ;
});
