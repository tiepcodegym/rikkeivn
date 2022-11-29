$(document).ready(function () {
    $("#form-employee-info input, #form-employee-info select, #form-employee-info textarea").not('#form-employee-info input[type="file"], #form-employee-info input[name="_token"], #form-employee-info textarea#comment').prop( "disabled", true);
    $("#form-employee-info input#certificate-image").prop( "disabled", true);
    $("#form-employee-info .imgPreviewWrap button").prop( "disabled", true);

    $('#form-employee-info button[type=submit]').not('#save_base').hide();
    $('[data-flag-dom="btn-add-profile-team"]').addClass('hidden');
    $('[data-flag-dom="btn-remove-profile-team"]').addClass('hidden');
    selectSearchReload();

    var $enrolmentDate = $('#employee-joindate');
    var $trialWorkStartDate = $('input[name="employee[trial_date]"]');
    var $trialWorkEndDate = $('#employee-trial_end_date');
    var $officialDate = $('#employee-offcial_date');

    $enrolmentDate.on('dp.change', function () {
        if ($('select[name="e_w[contract_type]"]').val() !== probationaryWorkingType) {
            return;
        }
        $trialWorkStartDate.val($enrolmentDate.val()).trigger('change');
    });
    //event change trial work start date, set official date = start working date
    $trialWorkStartDate.on('dp.change', function () {
        if ($('select[name="e_w[contract_type]"]').val() !== probationaryWorkingType) {
            return;
        }
        var date = $(this).val();
        var trialEndDate = null;
        if (!date || date === '') {
            if (!$officialDate.is(':disabled')) {
                $officialDate.val($enrolmentDate.val()).trigger('change');
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
        if ($('select[name="e_w[contract_type]"]').val() !== probationaryWorkingType) {
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
});

Date.prototype.toDateString = function () {
    return this.getFullYear() + '-' + get2Digis(this.getMonth() + 1) + '-' + get2Digis(this.getDate());
};
function get2Digis(number){
    return number < 10 ? ('0' + number) : number;
}

(function ($) {
    if ($('#setting_app_container').length > 0) {
        $('.btn-edit-form').click(function (e) {
            e.preventDefault();
            var formGroup = $(this).closest('.form-action-btns');
            formGroup.find('.btn-save-form, .btn-cancel-form').removeClass('hidden');
            $(this).addClass('hidden');
            $(this).closest('form').find('input, .btn-toggle-pass').prop('disabled', false);
        });

        $('.btn-cancel-form').click(function (e) {
            e.preventDefault();
            var formGroup = $(this).closest('.form-action-btns');
            formGroup.find('.btn-save-form').addClass('hidden');
            formGroup.find('.btn-edit-form').removeClass('hidden');
            $(this).addClass('hidden');
            $(this).closest('form').find('input, .btn-toggle-pass').prop('disabled', true);
        });

        $('.btn-toggle-pass').mousedown(function () {
            $(this).closest('.input-group').find('input').attr('type', 'text');
        }).mouseup(function () {
            $(this).closest('.input-group').find('input').attr('type', 'password');
        });

        $.validator.addMethod('exceptSpace', function (value, element) {
            return this.optional(element) || value.indexOf(" ") < 0 && value != "";
        }, "Password not allow space!");

        $('#form_file_password').validate({
            ignore: '',
            rules: {
                'emp_setting[pass_open_file]': {
                    required: true,
                    minlength: 4,
                    maxlength: 20,
                    exceptSpace: true,
                    remote: {
                        url: urlcheckExistSetting,
                        type: 'POST',
                        data: {
                            _token: siteConfigGlobal.token,
                            key: 'pass_open_file',
                            value: function () {
                                return $('[name="emp_setting[pass_open_file]"]').val();
                            },
                        },
                    },
                },
                new_password: {
                    required: true,
                    minlength: 4,
                    maxlength: 20,
                    exceptSpace: true,
                },
                new_password_confirmation: {
                    required: true,
                    minlength: 4,
                    maxlength: 20,
                    equalTo: '[name="new_password"]',
                    exceptSpace: true,
                },
            },
            messages: {
                'emp_setting[pass_open_file]': {
                    remote: 'Wrong input password!',
                },
            },
        });

        RKExternal['formEmployeeInfoSuccess'] =  function (response, dom) {
            var formGroup = dom.find('.form-action-btns');
            formGroup.find('.btn-cancel-form').trigger('click');
            if (dom.hasClass('reset-after-submit')) {
                dom[0].reset();
            }
        };

        $('#btn_show_password').click(function (e) {
            e.preventDefault();
            var btn = $(this);
            bootbox.prompt({
                title: textEnterPasswordToShow,
                inputType: 'password',
                className: 'modal-default',
                callback: function (result) {
                    if (result) {
                        $.ajax({
                            url: urlcheckExistSetting,
                            type: 'POST',
                            data: {
                                _token: siteConfigGlobal.token,
                                key: 'pass_open_file',
                                value: result,
                                history: 1,
                            },
                            success: function (data) {
                                if (data.trueKey) {
                                    var modal = $(btn.attr('data-modal'));
                                    modal.modal('show');
                                    var tableBody = modal.find('.modal-body .table tbody');
                                    var tblHtml = '';
                                    $.each(data.histories, function (index, item) {
                                        tblHtml += '<tr>'
                                                + '<td>'+ item.value + (item.is_current ? ' <i class="color-green fa fa-check"></i>' : '') + '</td>'
                                                + '<td>'+ item.updated_at +'</td>'
                                                + '</tr>';
                                    });
                                    tableBody.html(tblHtml);
                                    //auto close after 60 seconds
                                    setTimeout(function () {
                                        modal.modal('hide');
                                        modal.find('.modal-body .table tbody').html('');
                                    }, 60000);
                                } else {
                                    RKExternal.notify(textPasswordErrorTryAgain, 'warning');
                                }
                            },
                            error: function (error) {
                                RKExternal.notify(error.responseJSON, 'warning');
                            },
                        });
                    }
                },
            });
        });

        //check app pass
        $('.btn-check-pass').click(function (e) {
            var appPass = $('.app-password').val();
            $('.loading-check-pass').removeClass('hidden');
            $(this).attr("disabled", "disabled");
            e.preventDefault();
            $.ajax({
                url: urlcheckAppPass,
                type: 'GET',
                data: {
                    _token: siteConfigGlobal.token,
                    appPass: appPass,
                },
                success: function (data) {
                    $('.loading-check-pass').addClass('hidden');
                    bootbox.alert({
                        className: data === true ? 'modal-success text-center' : 'modal-danger text-center',
                        message: data === true ? checkAppPassSuccess : textPasswordErrorTryAgain,
                    });
                    if (data !== true) {
                        $('.btn-check-pass').prop('disabled', false);
                    }
                },
                error: function (error) {
                    RKExternal.notify(error.responseJSON, 'warning');
                },
            });
        });
    }

    $('.app-password').on('input', function (e) {
        var button = $('.btn-check-pass');
        var defaultVal = $(this).prop('defaultValue');

        if (defaultVal !== $(this).val()) {
            button.prop('disabled', false);
        } else {
            button.attr("disabled", "disabled");
        }
    });
})(jQuery);
