(function ($) {
    RKfuncion.select2.init();
    RKfuncion.bootstapMultiSelect.init();

    if (typeof jQuery.validator != 'undefined') {

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

        $('#req_oppor_form').validate({
            ignore: '',
            rules: {
                name: {
                    required: true,
                    maxlength: 255,
                },
                code: {
                    required: true,
                    maxlength: 255,
                    remote: {
                        url: urlCheckExists,
                        type: 'POST',
                        data: {
                            id: function () {
                                return $('[name="id"]').val();
                            },
                            field: 'code',
                            value: function () {
                                return $('[name="code"]').val();
                            },
                            _token: siteConfigGlobal.token,
                        },
                    }
                },
                from_date: {
                    required: true,
                },
                to_date: {
                    required: true,
                    greaterThan: 'from_date'
                },
                sale_id: {
                    required: true,
                },
            },
            messages: {
                to_date: {
                    greaterThan: 'This field must be greater than From date',
                },
                code: {
                    remote: 'This field has already taken!',
                }
            },
            errorPlacement: function (error, element) {
                error.appendTo(element.closest('.form-group'));
            },
            submitHandler: function (form) {
                var nameRequiredAll = $(form).data('required-all');
                if (typeof nameRequiredAll != 'undefined') {
                    var valid = true;
                    var names = (nameRequiredAll + '').split(',');
                    for (var i = 0; i < names.length; i++) {
                        var className = names[i];
                        var inputNames = $(form).find('.' + className);
                        var lastItem = inputNames.last();
                        var inputError = true;
                        inputNames.each(function () {
                            if (!$(this).val().trim()) {
                                inputError = false;
                            }
                        });
                        lastItem.next('.error').remove();
                        if (!inputError) {
                            lastItem.after('<label class="error">Please fill all fields!</label>');
                            valid = false;
                        }
                    }
                    if (!valid) {
                        $(form).find('button[type="submit"]').prop('disabled', false);
                        return false;
                    }
                }
                form.submit();
            },
        });

    }

    $('#req_oppor_form').submit(function () {
        var form = $(this);
        setTimeout(function () {
            form.find('button[type="submit"]').prop('disabled', false);
        }, 3000);
    });

    $('.date-picker').each(function () {
        var format = $(this).data('format');
        $(this).datetimepicker({
            format: format,
        });
    });

    var listNumItems = $('#list_employees table tbody');
    var elTotalEmp = $('.total-number-emp');
    $('#btn_add_employee_old').click(function (e) {
        e.preventDefault();
        var item = $('#num_emp_tpl').clone().removeAttr('id').find('tbody tr:first');
        if (item.hasClass('new-emp-item')) {
            var count = listNumItems.find('.emp-item').length;
            item.find('input, select').each(function () {
                var name = $(this).attr('name');
                var newName = name + '['+ count +']';
                if ($(this).hasClass('new-multiselect')) {
                    newName += '[]';
                }
                $(this).attr('name', newName);
            });
        }
        item.appendTo(listNumItems);
        item.find('select.new-select2').select2();
        item.find('select.new-multiselect').multiselect();
        $('#req_oppor_form [name="members[numbers][]"]').rules('add', {
            required: true,
        });
    });

    $('body').on('click', '.btn-del-item-old', function (e) {
        e.preventDefault();
        $(this).closest('.emp-item').remove();
        listNumItems.find('.emp-item').each(function (index) {
            $(this).find('input, select').each(function () {
                var name = $(this).attr('name');
                if (typeof name != 'undefined') {
                    $(this).attr('name', name.replace(/\[\d+\]/, '['+ index +']'));
                }
            });
        });
    });

    /*$('body').on('change', '.emp-item .num-emp', function (e) {
        var total = 0;
        listNumItems.find('.num-emp').each(function () {
            var itemNum = $(this).val();
            if (itemNum) {
                total += parseInt(itemNum);
            }
        });
        elTotalEmp.text(total);
    });*/

    $('body').on('click', '.btn-submit-oppor', function (e) {
        var btn = $(this);
        var form = btn.closest('form');
        var status = form.find('[name="status"]').val();
        if (oldStatus && oldStatus != status && status == STT_SUBMIT) {
            bootbox.confirm({
                message: btn.data('noti'),
                className: 'modal-warning',
                callback: function (result) {
                    if (result) {
                        form[0].submit();
                    }
                },
            });
            return false;
        }
    });

    $('body').on('click', '.btn-export-oppor', function (e) {
        e.preventDefault();
        var btn = $(this);
        var url = btn.data('url');
        if (btn.is(':disabled') || !url) {
            return;
        }
        var iconLoading = btn.find('i');

        btn.prop('disabled', true);
        iconLoading.removeClass('hidden');
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                _token: siteConfigGlobal.token
            },
            success: function (response) {
                var wb = XLSX.utils.table_to_book($(response.table)[0], {sheet: "Sheet1",});
                var numCols = 12;
                var cols = [{wch: 5,}];
                for (var i = 1; i <= numCols; i++) {
                    cols.push({wch: 22,});
                }
                wb.Sheets.Sheet1['!cols'] = cols;
                var wbout = XLSX.write(wb, {bookType: 'xlsx', bookSST: false, type: 'binary',});
                var fname = response.fileName + '.xlsx';
                try {
                    saveAs(new Blob([s2ab(wbout)], {type: "",}), fname);
                } catch(e) {
                    //error
                    return;
                }
            },
            error: function (error) {
                bootbox.alert({
                    message: error.responseJSON,
                    className: 'modal-danger',
                });
            },
            complete: function () {
                btn.prop('disabled', false);
                iconLoading.addClass('hidden');
            },
        });
    });

    function s2ab(s) {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
        return buf;
    }

    //generate checked
    var sessionKeys = 'oppors_checked';
    var opporCheckedIds = RKSession.getRawItem(sessionKeys);
    if (opporCheckedIds) {
        for (var i = 0; i< opporCheckedIds.length; i++) {
            $('.check-item[value="'+ opporCheckedIds[i] +'"]').prop('checked', true);
        }
        $('.check-all').prop('checked', $('.check-item').length === $('.check-item:checked').length);
    }

    $('.check-all').click(function () {
        $('.check-item').prop('checked', $(this).is(':checked')).trigger('change');
    });

    $('.check-item').change(function () {
        var checkAll = $('.check-all');
        checkAll.prop('checked', $('.check-item').length === $('.check-item:checked').length);
        var checkedIds = RKSession.getRawItem(sessionKeys);
        var index = checkedIds.indexOf($(this).val() + '');
        if ($(this).is(':checked')) {
            if (index < 0) { 
                checkedIds.push($(this).val());
            }
        } else {
            if (index > -1) {
                checkedIds.splice(index, 1);
            }
        }
        RKSession.setRawItem(sessionKeys, checkedIds);
    });

    $(document).on('click touchstart','.btn-reset-filter',function(e) {
        e.preventDefault();
        RKSession.removeItem(sessionKeys);
    });

    $('#export_oppors').click(function (e) {
        e.preventDefault();
        //check item checked
        var checkedIds = RKSession.getRawItem(sessionKeys);
        if (checkedIds.length < 1) {
            bootbox.alert({
                message: textNoneItemChecked,
                className: 'modal-danger'
            });
            return false;
        }
        var btn = $(this);
        btn.prop('disabled', true);
        setTimeout(function () {
           btn.prop('disabled', false); 
        }, 3000);

        var form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', $(this).data('url'));
        var params = {
            _token: siteConfigGlobal.token,
            'ids': checkedIds
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
    });

})(jQuery);
