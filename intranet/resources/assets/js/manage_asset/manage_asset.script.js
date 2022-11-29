$(function() {
    // Call function init select2
    selectSearchReload();
    $('.select-search-multiple').select2();

    $('.datetime-picker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true,
    });

    $('.filter-date').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true,
    }).on('changeDate', function(e) {
        e.stopPropagation();
        $('.btn-search-filter').trigger('click');
    }).on('keyup', function(e) {
        e.stopPropagation();
        if (e.keyCode === 13) {
            $('.btn-search-filter').trigger('click');
        }
    }).on('clearDate', function(e) {
        e.stopPropagation();
        $('.btn-search-filter').trigger('click');
    });

    RKfuncion.select2.elementRemote(
        $('.search-employee')
    );

    $('.btn-get-asset-approve').click(function() {
        var state = $(this).attr('state');
        $.ajax({
            url: urlAjaxGetAssetToApprove,
            type: 'GET',
            data: {
                assetState: state,
            },
            success: function (data) {
                $('#form_get_asset_to_approve').html(data.html);
                $('#modal_get_asset_to_approve').modal('show');
            },
            complete: function(data) {
                $('.asset-table input[type="checkbox"].minimal').iCheck({
                    checkboxClass: 'icheckbox_flat-green',
                });
                if ($('#table-asset-approval').find('.no-result-grid').length <= 0 ) {
                    $('.asset-table').DataTable({
                        "paging": true,
                        "lengthChange": false,
                        "searching": false,
                        "ordering": false,
                        "info": true,
                        "autoWidth": false,
                    });
                };
            },
        });
    });

    $('.call-ajax-get-modal').click(function() {
        var reportType = $(this).attr('value');
        $.ajax({
            url: urlAjaxGetModalReport,
            type: 'GET',
            data: {
                reportType: reportType,
            },
            success: function (data) {
                $('#modal_report').html(data.html);
                $('#modal_report').modal('show');
            },
            complete: function() {
                $('.report input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_minimal-blue',
                });
                tableReport = $('.asset-table-report').DataTable({
                    "paging": true,
                    "lengthChange": false,
                    "searching": false,
                    "ordering": false,
                    "info": true,
                    "autoWidth": false,
                    "bDestroy": true,
                    'columnDefs': [
                        {
                            'targets': 0,
                            'checkboxes': {
                                'selectRow': true,
                            },
                        },
                    ],
                    'select': {
                        'style': 'multi',
                    },
                    'order': [[1, 'asc']],
                });
            },
        });
    });

    $('.btn-detail-inventory').click(function (e) {
        e.preventDefault();
        $('#confirm_asset_none_item').addClass('hidden');
        $('#confirm_asset_table tbody tr:not(:first)').remove();
        $('#confirm_asset_table').attr('data-employee', $(this).attr('data-employee'));
        $('#modal_inventory').modal('show');
        var url = $(this).closest('table').find('.detail-col').data('url');
        loadConfirmAssetItemHistory(url);
    });

      function loadConfirmAssetItemHistory(url){
        var assetConfirmTbl = $('#confirm_asset_table');
        var employeeId = assetConfirmTbl.attr('data-employee');
        var inventoryId = $('.btn-detail-inventory').attr('data-inventory');
        var data = {};
        if (typeof employeeId != 'undefined') {
            data = {
                employeeId: employeeId,
                inventoryId: inventoryId,
            };
        }
        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            success: function (results) {
                var items = results.data;
                if (items.length > 0) {
                    for (var i = 0; i < items.length; i++) {
                        var item = items[i];
                        var assetItem = $('#confirm_asset_item').clone().removeAttr('id').removeClass('hidden');
                        var order = i + 1 + (results.current_page - 1) * results.per_page;
                        assetItem.find('.col-no').text(order);
                        assetItem.find('.col-code').text(item.asset_code);
                        assetItem.find('.col-name').text(item.asset_name);
                        assetItem.find('.col-confirm').attr('name', 'asset_ids['+ item.id +']').text(item.label);
                        assetItem.find('.col-note textarea').attr('name', 'employee_notes['+ item.id +']').val(item.employee_note);
                        assetItem.appendTo(assetConfirmTbl.find('tbody'));
                    }
                }
                if (results.current_page === 1 && items.length === 0) {
                    $('#confirm_asset_none_item').removeClass('hidden');
                } else {
                    $('#confirm_asset_none_item').addClass('hidden');
                }
            },
        });
    }

    $('#btn_inventory').click(function (e) {
        e.preventDefault();
        if ($('#confirm_asset_table tbody tr').length > 1) {
            $('#modal_inventory').modal('show');
            return;
        }
        var _this = $(this);
        var url = _this.data('url');
        if (typeof url == 'undefined' || !url || _this.is(':disabled')) {
            return;
        }
        var iconLoading = _this.find('i.fa-refresh');
        if (!iconLoading.hasClass('hidden')) {
            return;
        }
        iconLoading.removeClass('hidden');
        loadConfirmAssetItem(url, function () {
            $('#modal_inventory').modal('show');
            var ivtFormRules = {};
            $('#confirm_asset_table tbody tr:not(.hidden)').each(function () {
               var tr = $(this);
               var elConfirm = tr.find('.col-confirm input');
               var elNote = tr.find('.col-note textarea');
               ivtFormRules[elNote.attr('name')] = {
                   required: function () {
                       return !elConfirm.is(':checked');
                   }
               };
            });
            $('#profile_inventory_form').validate({
                rules: ivtFormRules,
            });
            iconLoading.addClass('hidden');
        }, function (error) {
            bootbox.alert({
                message: error.responseJSON,
                className: 'modal-danger',
            });
            iconLoading.addClass('hidden');
        });
    });

    $('#confirm_asset_more a').click(function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (!url) {
            return;
        }
        var loadingIcon = $(this).find('i');
        if (!loadingIcon.hasClass('hidden')) {
            return;
        }
        loadingIcon.removeClass('hidden');
        loadConfirmAssetItem(url, function () {
            loadingIcon.addClass('hidden');
        });
    });

    function loadConfirmAssetItem(url, done, errorCb){
        var assetConfirmTbl = $('#confirm_asset_table');
        var moreBtn = $('#confirm_asset_more');
        var employeeId = assetConfirmTbl.attr('data-employee');
        var data = {};
        if (typeof employeeId != 'undefined') {
            data = {
                employeeId: employeeId,
            };
        }
        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            success: function (results) {
                var items = results.data;
                var nextPageUrl = results.next_page_url;
                if (items.length > 0) {
                    for (var i = 0; i < items.length; i++) {
                        var item = items[i];
                        var assetItem = $('#confirm_asset_item').clone().removeAttr('id').removeClass('hidden');
                        var order = i + 1 + (results.current_page - 1) * results.per_page;
                        assetItem.find('.col-no').text(order);
                        assetItem.find('.col-code').text(item.asset_code);
                        assetItem.find('.col-name').text(item.asset_name);
                        assetItem.find('.col-confirm input').attr('name', 'asset_ids['+ item.id +']').prop('checked', parseInt(item.allocation_confirm) === 1).val(item.id);
                        assetItem.find('.col-note textarea').attr('name', 'employee_notes['+ item.id +']').val(item.employee_note);
                        assetItem.appendTo(assetConfirmTbl.find('tbody'));
                    }
                }
                if (results.current_page == 1 && items.length == 0) {
                    $('#confirm_asset_none_item').removeClass('hidden');
                } else {
                    $('#confirm_asset_none_item').addClass('hidden');
                }
                moreBtn.find('a').attr('href', nextPageUrl);
                if (nextPageUrl) {
                    moreBtn.removeClass('hidden');
                } else {
                    moreBtn.addClass('hidden');
                }
                if (typeof done != 'undefined') {
                    done();
                }
            },
            error: function (error) {
                if (typeof errorCb != 'undefined') {
                    errorCb(error);
                }
            },
        });
    }

    $('#btn_inventory_export').click(function (e) {
        e.preventDefault();
        var btn = $(this);
        var iconLoading = btn.find('i');
        var url = btn.data('url');
        var inventoryId = btn.data('id');
        if (!iconLoading.hasClass('hidden') || !url || !inventoryId) {
            return;
        }
        btn.prop('disabled', true);
        iconLoading.removeClass('hidden');
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                _token: siteConfigGlobal.token,
                inventory_id: inventoryId,
            },
            success: function (response) {
                var wb = XLSX.utils.book_new();
                var sheetsData = response.sheetsData;
                var colsHead = response.colsHead;
                var hasData = false;

                for (var sheetName in sheetsData) {
                    var sheetData = sheetsData[sheetName];
                    if (sheetData.length < 1) {
                        continue;
                    }
                    hasData = true;
                    var wsheet = XLSX.utils.json_to_sheet(sheetData);
                    //custom heading title
                    var range = XLSX.utils.decode_range(wsheet['!ref']);
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var addr = XLSX.utils.encode_col(C) + "1";
                        if (typeof wsheet[addr] == 'undefined') {
                            continue;
                        }
                        var cell = wsheet[addr];
                        cell.v = colsHead[cell.v]['tt'];
                        wsheet[addr] = cell;
                    }
                    wsheet['!ref'] = XLSX.utils.encode_range(range);
                    //set wch
                    var colsWch = [];
                    for (var col in colsHead) {
                        colsWch.push({wch: colsHead[col]['wch']});
                    }
                    wsheet['!cols'] = colsWch;
                    //set style
                    $.each(wsheet, function (index, cell) {
                        cell.s = {
                            alignment: {
                                wrapText: 1,
                            },
                        };
                        wsheet[index] = cell;
                    });
                    XLSX.utils.book_append_sheet(wb, wsheet, sheetName);
                }

                if (!hasData) {
                    bootbox.alert({
                        className: 'modal-warning',
                        message: btn.data('mess-none'),
                    });
                    return;
                }

                var fname = response.fileName + '.xlsx';
                var wbout = XLSX.write(wb, {bookType: 'xlsx', bookSST: true, type: 'binary'});
                try {
                    saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}), fname);
                } catch(e) {
                    //error
                    bootbox.alert({
                        message: 'Error export file, please try again later!',
                        className: 'modal-danger',
                    });
                    return;
                }
            },
            error: function (error) {
                bootbox.alert({
                    className: 'modal-danger',
                    message: error.responseJSON,
                });
            },
            complete: function () {
                iconLoading.addClass('hidden');
                btn.prop('disabled', false);
            },
        });
    });

    $('.btn-noti-inventory').click(function (e) {
        e.preventDefault();
        var btn = $(this);
        var url = btn.data('url');
        if (btn.is('disabled') || !url) {
            return;
        }
        var noti = btn.data('noti');
        var loadingIcon = btn.find('i');
        bootbox.confirm({
            message: noti,
            className: 'modal-warning',
            buttons: {
                confirm: {
                    label: confirmYes,
                },
                cancel: {
                    label: textCancel,
                },
            },
            callback: function (result) {
                if (result) {
                    btn.prop('disabled', true);
                    loadingIcon.removeClass('hidden');
                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: {
                            _token: siteConfigGlobal.token,
                        },
                        success: function (data) {
                            bootbox.alert({
                                className: 'modal-success',
                                message: data,
                                buttons: {
                                    ok: {
                                        label: confirmYes,
                                    },
                                },
                            });
                        },
                        error: function (error) {
                            bootbox.alert({
                                className: 'modal-danger',
                                message: error.responseJSON,
                                buttons: {
                                    ok: {
                                        label: confirmYes,
                                    },
                                },
                            });
                        },
                        complete: function () {
                            btn.prop('disabled', false);
                            loadingIcon.addClass('hidden');
                        },
                    });
                }
            },
        });
    });

    $('.btn-delete-item').click(function () {
        var btn = $(this);
        var noti = btn.data('noti');
        bootbox.confirm({
            message: noti,
            className: 'modal-danger',
            buttons: {
                confirm: {
                    label: confirmYes,
                },
                cancel: {
                    label: textCancel,
                },
            },
            callback: function (result) {
                if (result) {
                    btn.closest('form')[0].submit();
                }
            },
        });
        return false;
    });

    $(document).ready(function () {
        $('.el-short-content').shortedContent();
    });
});

/*
 * Function init iCheckbox
 */
function initCheckbox() {
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_flat-green',
        radioClass: 'iradio_minimal-green',
    });
    $('.report input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_minimal-blue',
    });
}

/*
 * custom xlsx function
 */
function s2ab(s) {
    if (typeof ArrayBuffer !== 'undefined') {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i = 0; i !== s.length; ++i) {
            view[i] = s.charCodeAt(i) & 0xFF;
        }
        return buf;
    } else {
        var buf = [];
        for (var i = 0; i !== s.length; ++i) {
            buf[i] = s.charCodeAt(i) & 0xFF;
        }
        return buf;
    }
}
