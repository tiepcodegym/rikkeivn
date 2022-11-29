(function ($) {
    $('.search-datepicker').datetimepicker({
        viewMode: 'months',
        format: 'YYYY-MM',
        useCurrent: false,
    }).on('dp.change', function () {
        $('.btn-search-filter').trigger('click');
    });
    $('body').on('click', 'td.col-actions .btn-edit-money', function (e) {
        var rowEdit = $(this).closest('tr');
        rowEdit.find('.text, .btn-edit-money').addClass('hidden');
        rowEdit.find('.btn-editing, .input').removeClass('hidden');
    });
    $('body').on('click', '.btn-money-cancel', function () {
        $(this).closest('tr').find('.text, .btn-edit-money').removeClass('hidden');
        $(this).closest('tr').find('.btn-editing, .input').addClass('hidden')
    });
    $('body').on('click', 'td.col-actions .btn-money-save', function () {
        var btn = $(this);
        var row = btn.parents('tr');
        var data = {};
        row.find('.input').each(function () {
            data[$(this).attr('name')] = $(this).val();
        });
        if (row.find('.error-note').hasClass('error')) {
            return false;
        }
        bootbox.confirm({
            message: textConfirm,
            className: 'modal-default',
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-danger'
                },
            },
            callback: function (result) {
                if (result) {
                    $.ajax({
                        data: data,
                        url: btn.attr('data-action'),
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': _token,
                        },
                        success: function (res) {
                            row.find('.input, .btn-editing').addClass('hidden');
                            row.find('.text, .btn-edit-money').removeClass('hidden');
                            if (res.data !== undefined) {
                                row.find('span.text_amount').html(formatMoney(res.data.amount));
                                row.find('span.text_note').text(res.data.note);
                                row.find('span.text_status_amount').html(listStatus[res.data.status_amount]);
                                if (res.data.status_amount === STATUS_PAID) {
                                    row.css('background', '#dddddd');
                                } else if (res.data.status_amount.toString().indexOf([STATUS_UNPAID, STATUS_UPDATE_MONEY]) === -1) {
                                    row.css('background', '#ffffff');
                                }
                                $("#display-sum-money").load(location.href + " #display-sum-money .table-grid-data");
                            } else {
                                RKExternal.notify(data.message, false);
                            }
                        },
                        error: function () {
                            RKExternal.notify('System error!', false);
                        },
                    });
                }
            }
        });
    });
    $(document).on("keyup", "tbody tr .input[name='amount']", function (event) {
        if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
            return;
        }
        var $this = $(this);
        var input = parseInt($this.val().replace(/[\D\s\._\-]+/g, ""));
        isNaN(input) ? input = 0 : input;
        $this.val(function () {
            return (input === 0) ? 0 : formatMoney(input);
        });
    });

    $('body').on("keyup", "tbody tr input[name='note']", function (event) {
        var $this = $(this);
        $this.closest('tr.row-edit-money').find('.error-note').removeClass('error').text('');
        if ($this.val().trim().length > MAX_TEXT) {
            $this.parents('tr').find('.input').css('vertical-align', 'top');
            $this.parents('tr').find('.error-note').addClass('error').html(lengthMax);
        } else {
            $this.parents('tr').find('.input').css('vertical-align', '');
            $this.parents('tr').find('.error-note').removeClass('error').html('');
        }
    });

    $('#form_import_fines_money').validate({
        rules: {
            file: 'required',
        },
        messages: {
            file: textRequired,
        },
    });
    function formatMoney(value) {
        return value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
    }


    var sessionKeys = 'fines_money_checked';
    var memberCheckedIds = RKSession.getRawItem(sessionKeys);
    if (memberCheckedIds) {
        for (var i = 0; i< memberCheckedIds.length; i++) {
            $('.table-check-list .check-item[value="'+ memberCheckedIds[i] +'"]').prop('checked', true);
        }
        $('#tbl_check_all').prop('checked', $('.table-check-list .check-item').length === $('.table-check-list .check-item:checked').length);
    }

    $('.check-all').click(function () {
        var list = $($(this).data('list'));
        list.find('.check-item').prop('checked', $(this).is(':checked')).trigger('change');
    });
    $('body').on('change', '.check-item', function () {
        var idAll = $(this).closest('.checkbox-list').data('all');
        var checkAll = $(idAll);
        checkAll.prop('checked', $('[data-all="'+ idAll +'"] .check-item').length === $('[data-all="'+ idAll +'"] .check-item:checked').length);
        if (idAll === '#tbl_check_all') {
            var checkedIds = RKSession.getRawItem(sessionKeys);
            if ($(this).is(':checked')) {
                var itemVal = $(this).val().split(', ').filter(function(v){return v!==''});
                checkedIds = $.merge(itemVal, checkedIds);
            } else {
                var removeItem = $(this).val().split(', ').filter(function(v){return v!==''});
                checkedIds = arrayDiff(removeItem, checkedIds);
            }
            RKSession.setRawItem(sessionKeys, checkedIds);
        }
    });

    $(document).on('click touchstart','.btn-reset-filter',function(e) {
        e.preventDefault();
        RKSession.removeItem(sessionKeys);
    });

    $('#form_export_fines_money').submit(function () {
        var form = $(this);
        var errorMess = form.find('.error-mess');
        errorMess.addClass('hidden');
        var itemChecked = form.find('[name="itemsChecked"]');
        itemChecked.val('');
        var btn = form.find('button[type="submit"]');
        var checkedIds = RKSession.getRawItem(sessionKeys);

        if (parseInt(form.find('[name="export_all"]:checked').val()) === 0 && checkedIds.length < 1) {
            errorMess.text(textNoneItemSelected).removeClass('hidden');
            btn.prop('disabled', false);
            return false;
        }
        itemChecked.val(checkedIds.join(','));
        btn.prop('disabled', false);
    })

})(jQuery);
