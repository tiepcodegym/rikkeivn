(function ($) {

    RKfuncion.select2.init();

    if ($('#working_time_form').length > 0) {
        $('#working_time_form').validate({
            ignore: [],
            rules: {
                approver_id: {
                    required: true,
                },
                from_month: {
                    required: true,
                },
                to_month: {
                    required: true,
                    greaterThan: 'from_month',
                },
                start_time1: {
                    required: true,
                },
                end_time1: {
                    required: true,
                    greaterThan: 'start_time1',
                },
                start_time2: {
                    required: true,
                },
                end_time2: {
                    required: true,
                    greaterThan: 'start_time2',
                },
                reason: {
                    required: true,
                },
            },
            messages: {
                approver_id: {
                    required: textRequiredField,
                },
                from_month: {
                    required: textRequiredField,
                    validDate: true,
                },
                to_month: {
                    required: textRequiredField,
                    greaterThan: textGreaterThanFromMonth,
                    validDate: true,
                },
                start_time1: {
                    required: textRequiredField,
                    validDate: true,
                },
                end_time1: {
                    required: textRequiredField,
                    greaterThan: textGreaterThanStartTime,
                    validDate: true,
                },
                start_time2: {
                    required: textRequiredField,
                    validDate: true,
                },
                end_time2: {
                    required: textRequiredField,
                    greaterThan: textGreaterThanStartTime,
                    validDate: true,
                },
                reason: {
                    required: textRequiredField,
                },
            },
            errorPlacement: function (error, element) {
                error.appendTo(element.closest('.form-group'));
            },
        });
    }

    $('.select-tooltip').each(function () {
        if (typeof $(this).data('toggle') != 'undefined') {
            var select2 = $(this).next('.select2-container');
            select2.data('toggle', 'tooltip').attr('title', $(this).attr('title')).tooltip();
        }
    });

    $('#btn_working_time_submit').click(function () {
        var form = $(this).closest('form');
        var btn = $(this);
        if (form.valid()) {
            if (form.find('.input-group-time').length === 4) {
                var listTime = [];
                form.find('.input-group-time').each(function (index) {
                    var input = $(this).find('input');
                    listTime.push(moment(input.val(), input.data('format')));
                });
                if (listTime.length !== 4) {
                    bootbox.alert({
                        className: 'modal-danger',
                        message: textInvalidTotalWorkingTime,
                    });
                    btn.prop('disabled', false);
                    return false;
                }
                var diffTime1 = listTime[1].diff(listTime[0]) / (1000 * 60 * 60);
                var diffTime2 = listTime[3].diff(listTime[2]) / (1000 * 60 * 60);
                if (diffTime1 < MIN_HOUR_MOR || diffTime2 < MIN_HOUR_AFT) {
                    bootbox.alert({
                        className: 'modal-danger',
                        message: textInvalidShiftTime,
                    });
                    btn.prop('disabled', false);
                    return false;
                }
                var total = diffTime1 + diffTime2;
                if (total != TOTAL_TIME) {
                    bootbox.alert({
                        className: 'modal-danger',
                        message: textInvalidTotalWorkingTime,
                    });
                    btn.prop('disabled', false);
                    return false;
                }
            }

            bootbox.confirm({
                className: 'modal-warning',
                message: btn.data('noti'),
                buttons: {
                    cancel: {
                        label: confirmNo,
                    },
                    confirm:  {
                        label: confirmYes,
                    },
                },
                callback: function (result) {
                    if (result) {
                        form.submit();
                    } else {
                        btn.prop('disabled', false);
                    }
                },
            })
        }
        return false;
    });

    $('button.status-submit').click(function () {
        var btn = $(this);
        var url = btn.data('url');
        var status = btn.data('status');
        var id = btn.data('id');
        if (typeof id == 'undefined' || !id) {
            var ids = [];
            $('.working-time-tbl tbody .check-item:checked').each(function () {
                if (status != $(this).data('status')) {
                    ids.push($(this).val());
                }
            });
        } else {
            var ids = [id];
        }
        if (ids.length === 0) {
            bootbox.alert({
                className: 'modal-danger',
                message: textRequiredItem,
                buttons: {
                    ok: {
                        label: confirmYes,
                    },
                },
            });
            return false;
        }
        if (!url || !status) {
            return false;
        }
        btn.prop('disabled', true);
        bootbox.confirm({
            className: 'modal-warning',
            message: btn.data('noti'),
            buttons: {
                cancel: {
                    label: confirmNo,
                },
                confirm:  {
                    label: confirmYes,
                },
            },

            callback: function (result) {
                if (result) {
                    var form = document.createElement('form');
                    form.setAttribute('method', 'post');
                    form.setAttribute('action', url);
                    var params = {
                        ids: ids.join(','),
                        status: status,
                        _token: siteConfigGlobal.token,
                    };

                    for (var key in params) {
                        var hiddenField = document.createElement('input');
                        hiddenField.setAttribute('type', 'hidden');
                        hiddenField.setAttribute('name', key);
                        hiddenField.setAttribute('value', params[key]);
                        form.appendChild(hiddenField);
                    }

                    document.body.appendChild(form);
                    form.submit();
                    form.remove();
                }
                btn.prop('disabled', false);
            },
        });
    });

    $('#btn_time_modal_reject').click(function () {
        var form = $('#working_time_form');
        if (form.length > 0) {
            if (!form.valid()) {
                return false;
            }
        } else {
            var status = $(this).data('status');
            var ids = [];
            $('.working-time-tbl tbody .check-item:checked').each(function () {
                if ($(this).data('status') != status) {
                    ids.push($(this).val());
                }
            });
            if (ids.length < 1) {
                bootbox.alert({
                    className: 'modal-danger',
                    message: textItemNotValid,
                    buttons: {
                        ok: {
                            label: confirmYes,
                        },
                    },
                });
                return false;
            }
            $('#wk_time_modal_reject').find('input[name="ids"]').val(ids.join(','));
        }
        $('#wk_time_modal_reject').modal('show');
    });

    if (typeof jQuery.validator != 'undefined') {
        jQuery.validator.addMethod('validDate', function (value, element) {
            if (value) {
                var format = $(element).data('format');
                var date = moment(value, format);
                return this.optional(element) || date.isValid();
            }
            return true;
        }, notValidDateFormat);

        jQuery.validator.addMethod('greaterThan', function(value, element, param) {
            var elCompare = $('[name="'+ param +'"]');
            if (value && elCompare.val()) {
                var format = $(element).data('format');
                var thisTime = moment(value, format);
                var thatTime = moment(elCompare.val(), format);
                return this.optional(element) || thisTime >= thatTime;
            }
            return true;
        });
    }

    $('.input-group-month').each(function () {
        var elMonth = $(this);
        if (!elMonth.find('input[type="text"]').is(':disabled')) {
            elMonth.datetimepicker({
                viewMode: 'months',
                format: 'MM-YYYY',
                useCurrent: false,
                //minDate: moment().add(1, 'months').startOf('month'),
            });
        }
    });
 
    $('.input-group-month').on('dp.change', function (e){
        var input = $(this).find('input');
        if (input.attr('id') == 'from_month') {
            $('#to_month').closest('.input-group-month').data('DateTimePicker').minDate(e.date);
        }
        if (input.attr('id') == 'to_month') {
            $('#from_month').closest('.input-group-month').data('DateTimePicker').maxDate(e.date);
        }
    });

    $('.input-group-time').each(function () {
        var input = $(this).find('input');
        var qicon = $(this).closest('.form-group').find('label i.fa');
        if (!input.is(':disabled')) {
            var format = input.data('format');
            var range = input.data('range');
            var options = {
                format: format,
                stepping: STEPING_MINUTE,
                useCurrent: false,
            };
            if (range) {
                options.minDate = moment(range[0], format).startOf('minute');
                options.maxDate = moment(range[1], format).endOf('minute');

                qicon.attr('title', range[0] + ' - ' + range[1]);
                qicon.tooltip();
            } else {
                qicon.remove();
            }
            $(this).datetimepicker(options);
        } else {
            qicon.remove();
        }
    });

    $('.month-filter').datetimepicker({
        format: 'MM-YYYY',
        useCurrent: false,
        showClear: true,
    });

    $('.date-filter').datetimepicker({
        format: 'DD-MM-YY',
        useCurrent: false,
        showClear: true,
    });

    $('.short-content').shortedContent();

    $('.check-all').change(function () {
        $('.check-item').prop('checked', $(this).is(':checked'));
        $('.status-btn').prop('disabled', $('.check-item:checked').length === 0);
    });

    $('.check-item').change(function () {
        $('.check-all').prop('checked', $('.check-item').length === $('.check-item:checked').length);
        $('.status-btn').prop('disabled', $('.check-item:checked').length === 0);
    });

    if ($('#working_time_log_form').length > 0) {
        var rules = {};
        var messages = {};
        for (var i = 0; i < collectDates.length; i++) {
            var date = collectDates[i];
            rules['time_out['+ date +']'] = {greaterThan: 'time_in['+ date +']',};
            messages['time_out['+ date +']'] = {greaterThan: textGreaterThanStartTime,};
        }
        $('#working_time_log_form').validate({
            rules: rules,
            messages: messages,
        });
    }

})(jQuery);
