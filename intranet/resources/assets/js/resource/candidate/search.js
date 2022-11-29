$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
});
var table = null;
jQuery(document).ready(function () {
    $('.date').datepicker({
        todayBtn: "linked",
        language: "it",
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd'
    });
    selectSearchReload();
    $('.filter-choice').select2();
    $('.column-choice').select2();
    $('.column-choice').parent().find('.select2-container li[title=Id] .select2-selection__choice__remove').remove();
    $('.column-choice').on('change', function() {
        $('.column-choice').parent().find('.select2-container li[title=Id] .select2-selection__choice__remove').remove();
    });
    RKfuncion.select2.elementRemote(
            $('#request')
            );
    RKfuncion.select2.elementRemote(
            $('#created_by')
            );
    RKfuncion.select2.elementRemote(
            $('#interviewer')
            );
    RKfuncion.select2.elementRemote(
            $('#presenter_id')
            );
    RKfuncion.select2.elementRemote(
            $('#found_by')
            );
    $('button.filter').on('click', function () {
        var btn = $(this);
        var searchType = btn.attr('data-type');
        var isExport = (typeof searchType != 'undefined') && (searchType == 'export');
        if (!isExport) {
            if (table != null) {
                table.destroy();
                $('#search-advance tbody').empty();
            }
            var value = $('#test_mark').val();
            if (value.indexOf("/") == -1) {
                if (value == '') {
                    $('#test_mark').val(0);
                }
            } else {
                var valueSplit = value.split('/'); 
                if (valueSplit[0] == '' || (typeof valueSplit[1] == 'undefined' || valueSplit[1] == '')) {
                    $('#test_mark').val(0);
                } 
            }
        }
        var filter = {};
        $('.filter-box .form-group ').not(".hidden").find('.filter-item').each(function () {
            var value = $(this).val() == null ? '' : $(this).val();
            var field = $(this).data('field');
            var compare = $('.compare-item[data-field="' + field + '"] .compare-list').val();
            var joinTable = $(this).data('jointable');
            var joinField = $(this).data('joinfield');
            var joinToField = $(this).data('jointofield');
            var except = $(this).data('except');
            var extra = $('.extra-item[data-field="' + field + '"]').val();
            var fieldExtra = $('.extra-item[data-field="' + field + '"]').data('field-extra');
            filter[field] = {
                value: value.trim(),
                compare: compare,
                joinTable: joinTable,
                joinField: joinField,
                joinToField: joinToField,
                except: except,
                extra: extra,
                fieldExtra: fieldExtra
            };
        });
        var columns = $('.column-choice').val();
        var columnsSelected = [{data: 'id', name: 'id'}];
        var columnsField = ['candidates.id'];
        var html = '<th>Id</th>';
        $.each(columns, function (key, value) {
            columnsSelected.push({data: value, name: value});
            var label = $('.column-choice option[value=' + value + ']').text();
            html += "<th>" + label + "</th>";
            var field = $('.column-choice option[value=' + value + ']').data('field');
            columnsField.push(field);
        });
        //check type
        if (isExport) {
            if (btn.is(':disabled')) {
                return;
            }
            $('button.filter').prop('disabled', true);
            btn.find('.fa-refresh').removeClass('hidden');
            var sortAsc = $('#search-advance thead tr th.sorting_asc');
            var sortDesc = $('#search-advance thead tr th.sorting_desc');
            var colSort = sortAsc.length > 0 ? sortAsc : (sortDesc.length > 0 ? sortDesc : null);
            var dir = colSort ? colSort.attr('aria-sort') : 'asc';

            $.ajax({
                type: 'POST',
                url: urlAdvanceSearch,
                data: {
                    filter: filter,
                    columnsField: columnsField,
                    type: searchType,
                    order: [colSort ? colSort.index() : 0, dir == 'ascending' ? 'asc' : 'desc'],
                },
                success: function (response) {
                    return exportSearchCandidates(response);
                },
                error: function () {
                    alert('Something went wrong!');
                },
                complete: function() {
                    $('button.filter').prop('disabled', false);
                    btn.find('.fa-refresh').addClass('hidden');
                }
            });
            return true;
        }

        html += "<th></th>";
        $('#search-advance thead tr').html(html);
        columnsSelected.push({data: '', name: '', orderable: false, searchable: false});
        //dataTable
        table = $('#search-advance').DataTable({
            processing: true,
            lengthChange: false,
            bFilter: false,
            serverSide: true,
            pageLength: 20,
            oLanguage: dataLang,
            ajax: {
                url: urlAdvanceSearch,
                data: {
                    filter: filter,
                    columnsField: columnsField,
                    type: searchType,
                },
                type:"post",
            },
            order: [[0, "desc"]],
            columns: columnsSelected,
            preDrawCallback: function (  ) {
                $('button.filter').prop('disabled', true);
                btn.find('.fa-refresh').removeClass('hidden');
            },
            drawCallback: function( settings ) {
                $('button.filter').prop('disabled', false);
                btn.find('.fa-refresh').addClass('hidden');
            }
        });
    });
    
    $('.filter-item').on('keyup', function(e) {
        if(e.keyCode == 13){
            $('button.filter').trigger('click');
        }
    });

    /*
     * dropbox language select change event
     */
    $(document).on('change', '#language', function () {
        var langSelected = $(this).val();
        var levelBox = $('.lang-level');
        levelBox.addClass('hidden');
        levelBox.find("option:gt(0)").remove();
        if (typeof langArray[langSelected] != 'undefined') {
            levelBox.removeClass('hidden');
            $.each(langArray[langSelected], function (langId, langName) {
                levelBox.append("<option value='" + langId + "'>" + langName + "</option>");
            });
        }
    });

    /*
     * dropbox .filter choice select change event
     * reset value when remove column from search list
     */
    $(document).on('change', '.filter-choice', function () {
        var selected = $(this).val();
        $('.filter-box .form-group').not('.select-columns').addClass('hidden');
        $.each(selected, function (key, value) {
            $('.filter-box .form-group').has($('#' + value)).removeClass('hidden');
        });
        //Reset value when remove that columns
        $('.filter-box .form-group.hidden').each(function () {
            $(this).find('input').val('');
            $(this).find('select').find('option:eq(0)').prop('selected', true);
            $(this).find('select').trigger('change');
            $(this).find('#request').html('');
            $(this).find('#created_by').html('');
            $(this).find('#interviewer').html('');
            $(this).find('#presenter_id').html('');
            $(this).find('#found_by').html('');
        });
    });

    /*
     * dropbox test_mark select change event
     */
    $(document).on('change', '.compare-item[data-field="test_mark"] .compare-list', function () {
        var value = $(this).val();
        if (value == nullCompareValue || value == notNullCompareValue) {
            $('#test_mark').hide();
        } else {
            $('#test_mark').show();
        }
    });

    /*
     * dropbox compare type of language select change event
     */
    $(document).on('change', '.compare-item .compare-list', function () {
        var value = $(this).val();
        var $filterItem = $(this).closest('.form-group').find('.filter-item');
        var dataField = $(this).closest('.compare-item').data('field');
        if (value == nullCompareValue || value == notNullCompareValue) {
            $filterItem.hide();
            $filterItem.parent().find('.select2-container').hide();
            if (dataField == 'language') {
                $('.lang-level').hide();
            }
        } else {
            $filterItem.show();
            $filterItem.parent().find('.select2-container').show();
            if (dataField == 'language') {
                $('.lang-level').show();
            }
        }
    });
    
});

/*
 * xlsx export candidate
 */
function exportSearchCandidates(response) {
    var wb = XLSX.utils.book_new();
    var sheetsData = response.sheetsData;
    var colsHead = response.colsHead;

    for (var sheetName in sheetsData) {
        var sheetData = sheetsData[sheetName];
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

    var fname = response.fileName + '.xlsx';
    var wbout = XLSX.write(wb, {bookType: 'xlsx', bookSST: true, type: 'binary'});
    try {
        saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}), fname);
    } catch(e) {
        console.log(e);
        return;
    }
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
