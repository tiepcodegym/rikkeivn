var tableStaffLate = {
    init: function() {
        this.loadSelect2();
        this.loadDatePicker();
        this.clickButton();
        this.createData();
        this.updateData();
        this.removeRow();
        this.addRow();
    },
    clickButton: function () {
        var that = this;
        $('body').on('click', "button[data-btn-action=edit]", function () {
            var row = $(this).closest('tr');
            row.find('.text').addClass('hidden');
            row.find('.input').removeClass('hidden');
        })
    },
    checkInput: function (row, dataAjax) {
        var status = true;
        row.find(".f-input").each(function() {
            var valInput = $(this).val();
            var name = $(this).attr('name');
            if (valInput === '' || valInput === undefined) {
                var className = '.' + name + '-error';
                row.find(className).text('không để trống');
                row.find(className).removeClass('hidden');
                status = false;
            } else {
                dataAjax[name] = valInput;
            }
        });
        if (typeof dataAjax.startDate !== 'undefined') {
            var d1 =  moment(dataAjax.startDate, "DD/MM/YYYY");
            var d2 =  moment(dataAjax.endDate, "DD/MM/YYYY");
            if (d1 > d2) {
                RKExternal.notify(mesStartLessEnd, false);
                status = false;
            }
        };
        if (typeof dataAjax.minute !== 'undefined') {
            if (dataAjax.minute > 119 || dataAjax.minute < 0) {
                RKExternal.notify('Số phút không hợp lệ', false);
                status = false;
            }
        };

        return status;
    },
    createData: function() {
        var that = this;
        $('#not-late-time').on('click', "button[data-btn-action=create]", function () {
            var thisBtn = this;
            var url = $(thisBtn).attr('data-url');
            var row = $(thisBtn).closest('tr');
            row.find('.error').addClass('hidden');
            var dataAjax = { _token: token, id: row.attr('data-row-id') };
            var statusUpdate = that.checkInput(row, dataAjax);
            if (statusUpdate) {
                $(thisBtn).find('.hidden').removeClass('hidden');
                $.ajax({
                    type: 'post',
                    url: url,
                    data: dataAjax,
                    success: function (res) {
                        if (res.errors) {
                            that.getMessageErrors(res);
                        }
                        if (res.status) {
                            var data = res.data;
                            row.attr('data-row-id', data.id);
                            row.find('.stt').text(++numberj);
                            row.find('.btn-success').attr('data-btn-action', 'update');
                            row.find('.btn-success').attr('data-url', urUpdateNotLateTime);
                            that.handlingResultSucees(res, row);
                        } else {
                            row.find('.fa-refresh').addClass('hidden');
                            RKExternal.notify(res.message, false);
                        }
                    },
                    error: function (error) {
                        RKExternal.notify(error, false)
                    },
                })
            }
        })
    },
    getMessageErrors: function (res) {
        var message = '';
        $.each(res.errors, function( key, value) {
            $.each(value, function( key2, value2) {
                message += value2 + "\n";
            });
        });
        RKExternal.notify(message, false)
    },
    handlingResultSucees: function (res, row) {
        var data = res.data;
        $.each(data, function( key, value) {
            row.find('.txt-' + key).text(value);
            row.find('.txt-' + key).val(value);
        });
        row.find('.fa-refresh').addClass('hidden');
        row.find('.text').removeClass('hidden');
        row.find('.input').addClass('hidden');
        RKExternal.notify(res.message, true)
    },
    updateData: function () {
        var that = this;
        $('#not-late-time').on('click', "button[data-btn-action=update]", function () {
            var thisBtn = this;
            var url = $(thisBtn).attr('data-url');
            var row = $(thisBtn).closest('tr');
            row.find('.error').addClass('hidden');
            var dataAjax = { _token: token, id: row.attr('data-row-id') };
            var statusUpdate = that.checkInput(row, dataAjax);
            if (statusUpdate) {
                $(thisBtn).find('.hidden').removeClass('hidden');
                $.ajax({
                    dataType: 'json',
                    type: 'post',
                    url: url,
                    data: dataAjax,
                    success: function (res) {
                        if (res.errors) {
                            that.getMessageErrors(res);
                        }
                        if (res.status) {
                            that.handlingResultSucees(res, row);
                        } else {
                            row.find('.fa-refresh').addClass('hidden');
                            RKExternal.notify(res.message, false);
                        }
                    },
                    error: function (error) {
                        row.find('.fa-refresh').addClass('hidden');
                        RKExternal.notify(error, false)
                    },
                })
            }
        })
    },
    removeRow: function() {
        var that = this;
        $('#not-late-time').on('click', "button[data-btn-action=delete]", function () {
            var url = $(this).attr('data-url');
            var row = $(this).closest('tr');
            var dataAjax = { _token: token, id: row.attr('data-row-id') };
            var name = row.find('.txt-emp_name').text();
            var result = confirm(messageDelete + ": " + name + "?");
            if (result) {
                $.ajax({
                    dataType: 'json',
                    type: 'post',
                    url: url,
                    data: dataAjax,
                    success: function (res) {
                        if (res.errors) {
                            var message = '';
                            $.each(res.errors, function( key, value) {
                                $.each(value, function( key2, value2) {
                                    message += value2 + "\n";
                                });
                            });
                            RKExternal.notify(message, false)
                        }
                        if (res.status) {
                            var data = res.data;
                            $.each(data, function( key, value) {
                                row.find('.txt-' + key).text(value);
                                row.find('.txt-' + key).val(value);
                            });
                            row.css("background", "red");
                            setTimeout(function(){row.remove();}, 500);
                            RKExternal.notify(res.message, true);
                        } else {
                            RKExternal.notify(res.message, false);
                        }
                    },
                    error: function (error) {
                        RKExternal.notify(error, false)
                    },
                })
            }
        })
    },
    addRow: function () {
        var that = this;
        $(document).on('click', '.btn-add', function (event) {
            var IdTable = $(this).attr('data-table-id');
            var row = $('#' + IdTable + ' tbody:last-child')[0];
            $(rowNotLateTime).find('.stt').text(numberj);
            $('#' + IdTable + ' tbody:last-child').append(rowNotLateTime);
            that.loadSelect2();
            that.loadDatePicker();
        });
    },
    loadSelect2: function () {
        $('.select-search-employee').selectSearchEmployee();
    },
    loadDatePicker: function() {
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
        });
    }
}
$(function () {
    tableStaffLate.init();
});