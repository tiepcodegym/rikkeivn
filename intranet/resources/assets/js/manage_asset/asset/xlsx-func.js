$(function() {
    $('.check-all-modal').click(function () {
        $(".list_export_cols").find('input:checkbox').not(this).prop('checked', this.checked);
    });
});
var sessionKeys = 'asset_checked';
var checkedId = RKSession.getRawItem(sessionKeys);
$( document ).ajaxComplete(function() {
    if ($('.check-all').is(':checked')) {
        $("table").find('input:checkbox').attr('checked', 'checked');
        $.each($("input[name='asset_id']:checked"), function(){    
            checkedId.push($(this).val());
        });
        RKSession.setRawItem(sessionKeys, checkedId);
    }
    $('.check-all').change(function () {
        $("table").find('input:checkbox').not(this).prop('checked', this.checked);
        if ($(this).is(':checked')) {
            $.each($("input[name='asset_id']:checked"), function(){    
                checkedId.push($(this).val());
            });
            RKSession.setRawItem(sessionKeys, checkedId);
        } else {
            RKSession.setRawItem(sessionKeys, null);
            checkedId = [];
        }
    });
    $("input[name='asset_id']").change(function () {
        if ($(this).is(':checked')) {
            checkedId.push($(this).val());
        } else {
            var index = checkedId.indexOf($(this).val());
            if (index > -1) {
                checkedId.splice(index, 1);
            }
        }
        RKSession.setRawItem(sessionKeys, checkedId);
    });
    var export_all = $('input[name=export_all]').val();
    $('input[name=export_all]').change(function () {
        export_all = $(this).val();
    });
    $('#form_asset_export').submit(function (event) {
        event.preventDefault();
        var checkedIds = RKSession.getRawItem(sessionKeys);
        var form = $(this);
        var errorMess = form.find('.error-mess');
        errorMess.addClass('hidden');
        var itemChecked = form.find('[name="itemsChecked"]');
        itemChecked.val('');
        var btn = form.find('button[type="submit"]');
        //check checked columns
        if(parseInt(export_all) === 0){
            if (form.find('.check-item:checked').length < 1) {
                errorMess.text('None column checked!').removeClass('hidden');
                btn.prop('disabled', false);
                return false;
            }
            if (!checkedIds || checkedIds.length < 1) {
                errorMess.text('None item checked!').removeClass('hidden');
                btn.prop('disabled', false);
                return false;
            }
        }
        var iconProcessing = form.find('.icon-processing');
        if (!iconProcessing.hasClass('hidden')) {
            return false;
        }
        if (checkedIds && checkedIds.length) {
            itemChecked.val(checkedIds.join(','));
        }
        iconProcessing.removeClass('hidden');
        $.ajax({
            method: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            success: function (response) {
                var wb = XLSX.utils.book_new();
                var sheetsDataa = response.sheetsData;
                var colsHead = response.colsHead;
                for (var sheetName in sheetsDataa) {
                    if (sheetsDataa.hasOwnProperty(sheetName)) {
                        var wsheet = XLSX.utils.json_to_sheet(sheetsDataa[sheetName]);
                        //custom heading title
                        var range = XLSX.utils.decode_range(wsheet['!ref']);
                        for (var C = range.s.c; C <= range.e.c; ++C) {
                            var addr = XLSX.utils.encode_col(C) + "1";
                            if (typeof wsheet[addr] === 'undefined') {
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
                            if (colsHead.hasOwnProperty(col)) {
                                colsWch.push({wch: colsHead[col]['wch']});
                            }
                        }
                        wsheet['!cols'] = colsWch;
                        //set style
                        $.each(wsheet, function (index, celll) {
                            celll.s = {
                                alignment: {
                                    wrapText: 1,
                                },
                            };
                            wsheet[index] = celll;
                        });
                        XLSX.utils.book_append_sheet(wb, wsheet, sheetName);
                    }
                }

                var fname = response.fileName + '.xlsx';
                var wbout = XLSX.write(wb, {bookType: 'xlsx', bookSST: true, type: 'binary'});
                try {
                    saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}), fname);
                } catch(e) {
                    console.log(e);
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
})
$( window ).on( "load", function() {
    RKSession.setRawItem(sessionKeys, null);
    checkedId = [];
});