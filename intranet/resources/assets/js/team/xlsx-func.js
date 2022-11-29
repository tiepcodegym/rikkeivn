(function ($) {

    //generate checked
    var sessionKeys = 'members_checked';
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
                checkedIds.push($(this).val());
            } else {
                var index = checkedIds.indexOf($(this).val());
                if (index > -1) {
                    checkedIds.splice(index, 1);
                }
            }
            RKSession.setRawItem(sessionKeys, checkedIds);
        }
    });

    $(document).on('click touchstart','.btn-reset-filter',function(e) {
        e.preventDefault();
        RKSession.removeItem(sessionKeys);
    });

    $('#form_export_member').submit(function () {
        var form = $(this);
        var errorMess = form.find('.error-mess');
        errorMess.addClass('hidden');
        var itemChecked = form.find('[name="itemsChecked"]');
        itemChecked.val('');
        var btn = form.find('button[type="submit"]');
        //check checked columns
        if (form.find('.check-item:checked').length < 1) {
            errorMess.text(textNoneColSelected).removeClass('hidden');
            btn.prop('disabled', false);
            return false;
        }
        //check item checked
        var checkedIds = RKSession.getRawItem(sessionKeys);
        if (parseInt(form.find('[name="export_all"]:checked').val()) === 0 && checkedIds.length < 1) {
            errorMess.text(textNoneItemSelected).removeClass('hidden');
            btn.prop('disabled', false);
            return false;
        }
        var iconProcessing = form.find('.icon-processing');
        if (!iconProcessing.hasClass('hidden')) {
            return false;
        }
        itemChecked.val(checkedIds.join(','));
        iconProcessing.removeClass('hidden');
        $.ajax({
            method: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            success: function (response) {
                var wb = XLSX.utils.book_new();
                var sheetsData = response.sheetsData;
                var colsHead = response.colsHead;
                for (var sheetName in sheetsData) {
                    var sheetData = sheetsData[sheetName];
                    var wsheet = XLSX.utils.json_to_sheet(sheetData);
                    //custom heading title
                    var colsFormat = [];
                    var range = XLSX.utils.decode_range(wsheet['!ref']);
                    // col from index rang.s.c to col index rang.e.c
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        //col index add row 1: A1, B1, ...
                        var colChar = XLSX.utils.encode_col(C);
                        var addr = colChar + "1";
                        if (typeof wsheet[addr] == 'undefined') {
                            continue;
                        }
                        var cell = wsheet[addr];
                        if (typeof colsHead[cell.v] != 'undefined') {
                            colsFormat[colChar] = {
                                t: colsHead[cell.v]['t'],
                                fm: colsHead[cell.v]['fm'],
                            };
                            cell.v = colsHead[cell.v]['tt'];
                            wsheet[addr] = cell;
                        }
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
                        if (index != '!ref' || index != '!cols') {
                            cell.s = {
                                alignment: {
                                    wrapText: 1,
                                },
                            };
                            //set data type
                            var splitCell = XLSX.utils.split_cell(index);
                            var colChar = splitCell[0];
                            if (colsFormat[colChar] && cell.v && splitCell.length > 1 && splitCell[1] != 1) {
                                //if data type is date 'd'
                                if (colsFormat[colChar]['t'] == 'd') {
                                    var cellMoment = moment(cell.v, 'DD/MM/YYYY');
                                    if (cellMoment.isValid()) {
                                        //impossible set cell date
                                        /*cell.t = 'd';
                                        cell.w = cell.v;
                                        cell.v = cellMoment.toDate();
                                        cell.z = 'dd/mm/yyyy';*/
                                    } else {
                                        cell.t = 's';
                                    }
                                } else if (colsFormat[colChar]['t'] == 'n') {
                                    if (colsFormat[colChar]['fm']) {
                                        cell.z = colsFormat[colChar]['fm'];
                                    } else {
                                        cell.t = 's';
                                    }
                                } else {
                                    cell.t = 's';
                                }
                            }
                            wsheet[index] = cell;
                        }
                    });
                    XLSX.utils.book_append_sheet(wb, wsheet, sheetName);
                }

                var fname = response.fileName + '.xlsx';
                var wbout = XLSX.write(wb, {
                    bookType: 'xlsx',
                    bookSST: true,
                    type: 'binary',
                    cellStyles: true,
                    cellDates: true,
                });
                try {
                    saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}), fname);
                } catch(e) {
                    //error
                    errorMess.text('Error export file, please try again later!').removeClass('hidden');
                    return;
                }
            },
            error: function (error) {
                errorMess.text(error.responseJSON).removeClass('hidden');
            },
            complete: function () {
                iconProcessing.addClass('hidden');
                btn.prop('disabled', false);
            },
        });
        return false;
    });

    $('#modal_member_relationship_export').click(function() {
        $('#modal-confirm-export').modal('show');
    });

    var colsOldSort = $('.list_export_cols').clone();
    $('.cols-sorting').click(function (e) {
        e.preventDefault();
        var btn = $(this);
        var order = 'asc';
        if (btn.hasClass('desc')) {
            order = 'desc';
        }
        var listSorted = $('.list_export_cols li').sort(function (a, b) {
            if (order === 'desc') {
                btn.addClass('asc').removeClass('desc');
                return $(a).text().toUpperCase().localeCompare($(b).text().toUpperCase());
            }
            btn.addClass('desc').removeClass('asc');
            return $(b).text().toUpperCase().localeCompare($(a).text().toUpperCase());
        });
        $('.list_export_cols').html(listSorted);
        //cols index
        $('.list_export_cols li input').each(function (index) {
            $(this).attr('name', 'columns['+ index +']');
        });
    });

    $('.cols-clear-sort').click(function (e) {
        e.preventDefault();
        colsOldSort.find('li input').each(function () {
            var checked = $('.list_export_cols li #' + $(this).attr('id')).is(':checked');
            if (checked) {
                $(this).prop('checked', checked).attr('checked', 'checked');
            }
        });
        $('.list_export_cols').html(colsOldSort.html());
        $('.list_export_cols li input').each(function (index) {
            $(this).attr('name', 'columns['+ index +']');
        });
    });

})(jQuery);

/*
 * convert spcial charactor
 */
function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

/*
 * render table html
 */
function renderTableHtml(colsHead, data) {
    var tableHtml = '';
    if (!data) {
        return tableHtml;
    }
    tableHtml += '<thead><tr>';
    for (var h in colsHead) {
        tableHtml += '<td>' + htmlEntities(colsHead[h]) + '</td>';
    }
    tableHtml += '</tr></thead>';
    for (var i = 0; i < data.length; i++) {
        var item = data[i];
        tableHtml += '<tr>';
        for (var h in colsHead) {
            tableHtml += '<td>' + htmlEntities(item[h]) + '</td>';
        }
        tableHtml += '</tr>';
    }
    return tableHtml;
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
