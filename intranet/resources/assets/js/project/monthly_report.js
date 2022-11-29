$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
});

$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
    $('.btn-search').prop('disabled', false);
});

/**
 * Save values to database
 */
$(document).on('click', 'button.btn-submit', function() {
    var btn = $(this);
    btn.find('i').removeClass('hidden');
    btn.prop('disabled', true);
    $.ajax({
        url: urlUpdate,
        type: 'post',
        dataType: 'html',
        data: {
            values: JSON.stringify(changeValues),
            year: $('.year-filter').val(),
        },
        timeout: requestTimeOut,
        success: function () {
            $('#modal-success-notification .modal-body p.text-default').text(successText);
            $('#modal-success-notification').modal('show');
        },
        error: function (x, t, m) {
            if(t === 'timeout') {
                $('#modal-warning-notification .modal-body p.text-default').text(errorTimeoutText);
            } else {
                $('#modal-warning-notification .modal-body p.text-default').text(errorText);
            }
            
            $('#modal-warning-notification').modal('show');
        },
        complete: function () {
            btn.find('i').addClass('hidden');
            btn.prop('disabled', false);
        }
    });
});

$(document).on('click', '.btn-search', function() {
    var btn = $(this);
    btn.find('i').removeClass('hidden');
    btn.prop('disabled', true);
    var startMonthFilter = $('.start-month').val();
    var endMonthFilter = $('.end-month').val();
    var yearFilter = $('.year-filter').val();
    showLoading();
    $.ajax({
        url: urlSearch,
        type: 'post',
        dataType: 'json',
        data: {
            startMonthFilter: startMonthFilter,
            endMonthFilter: endMonthFilter,
            yearFilter: yearFilter
        },
        timeout: requestTimeOut,
        success: function (data) {
            $('.data').html(data['html']);
            values = data['values'];
            btn.find('i').addClass('hidden');
            btn.prop('disabled', false);
            hideLoading();
            if (mrHasPermissUpdate) {
                $('table tr[data-row="'+ rowCost +'"] input').blur();
            }
        },
        error: function (x, t, m) {
            if(t === 'timeout') {
                $('#modal-warning-notification .modal-body p.text-default').text(errorTimeoutText);
            } else {
                $('#modal-warning-notification .modal-body p.text-default').text(errorText);
            }
            
            $('#modal-warning-notification').modal('show');
        }
    });
});

/**
 * Update values when a value focusout
 */
$(document).on('focusout', 'table input, table textarea', function() {
    var type = $(this).closest('table.dataTable').data('type');
    var value = $(this).val().trim();
    var month = $(this).data('month');
    var parentTabpane = $(this).closest('.tab-pane');
    var team = parentTabpane.data('team');
    var valueOrPoint = $(this).data('value-or-point');
    var row = $(this).closest('tr').data('row');
    if(!$.isArray(tempValues[team][month][row])) {
        tempValues[team][month][row] = {};
    }
    tempValues[team][month][row][valueOrPoint] = value;
    //Fill Business Effectiveness
    /*if (row == rowCost || row == rowPlanRevenue) {
        if (row == rowCost) {
            var costValue = value;
            var planRevenueValue = $(this).closest('table.dataTable').find('tr[data-row="'+rowPlanRevenue+'"] input[data-month='+month+']').val().trim();
        } else {
            var costValue = $(this).closest('table.dataTable').find('tr[data-row="'+rowCost+'"] input[data-month='+month+']').val().trim();
            var planRevenueValue = value;
        }
        var businessEffective = !planRevenueValue ? notAvailable : (costValue/planRevenueValue*100).toFixed(2);
        $(this).closest('tbody').find('tr[data-row="'+rowBusinessEffective+'"]').find('span[data-month='+month+'][data-value-or-point="'+isValue+'"]').text(businessEffective);
        if(!$.isArray(tempValues[team][month][rowBusinessEffective])) {
            tempValues[team][month][rowBusinessEffective] = {};
        }
        tempValues[team][month][rowBusinessEffective]['value'] = businessEffective;
    }*/
    updateBusinessEffect($(this));
    updateBillableComplete($(this));
    //updateBillStaffPlan($(this));
    updateAllocateStaff($(this));
    //updateBillStaffActual($(this));
    updateProductionCostStaff($(this));
    
    resetPoint(tempValues, month, rowBusinessEffective, type);
    resetPoint(tempValues, month, rowCompletedPlan, type);
    resetPoint(tempValues, month, rowAlloStaffActual, type);
    resetPoint(tempValues, month, rowProdStaffActual, type);
    //resetPoint(tempValues, month, rowBillStaffPlan, type);
    //resetPoint(tempValues, month, rowBillStaffActual, type);
    if (isNaN(value)) {
        $(this).closest('tr').find('td span[data-month='+month+'][data-value-or-point=point]').text(0);
    }
    compareArray(values, tempValues);
});

/*
 * refresh busines effect value
 */
function updateBusinessEffect(inputElm) {
    var _this = inputElm;
    var table = _this.closest('table');
    var team = _this.closest('.tab-pane').data('team');
    var rowElm = table.find('tr[data-row="'+ rowBusinessEffective +'"]');
    rowElm.find('.month-value span').each(function () {
        var month = $(this).data('month');
        var costValue = table.find('tr[data-row="'+ rowCost +'"] input[data-month="'+ month +'"]').val().trim();
        var budgetValue = table.find('tr[data-row="'+ rowApprovedCost +'"] input[data-month="'+ month +'"]').val().trim();
        var effectValue = notAvailable;
        if (costValue !== '' && budgetValue !== '') {
            effectValue = parseFloat(budgetValue) > 0 ? (parseFloat(costValue) / parseFloat(budgetValue) * 100).toFixed(2) : notAvailable;
        }
        $(this).text(effectValue);
        if(!$.isArray(tempValues[team][month][rowBusinessEffective])) {
            tempValues[team][month][rowBusinessEffective] = {};
        }
        tempValues[team][month][rowBusinessEffective]['value'] = effectValue;
    });
}

/*
 * refresh billable complete value
 */
function updateBillableComplete(inputElm) {
    var _this = inputElm;
    var table = _this.closest('table');
    var team = _this.closest('.tab-pane').data('team');
    var rowElm = table.find('tr[data-row="'+ rowCompletedPlan +'"]');
    rowElm.find('.month-value span').each(function () {
        var month = $(this).data('month');
        var billPlanValue = table.find('tr[data-row="'+ rowApprovedProdCost +'"] input[data-month="'+ month +'"]').val().trim();
        var billActualValue = table.find('tr[data-row="'+ rowActual +'"] .month-value span[data-month="'+ month +'"]').text().trim();
        var billableCompleteVal = notAvailable;
        if (billPlanValue !== '' && billActualValue !== '') {
            billableCompleteVal = parseFloat(billPlanValue) > 0 ? (parseFloat(billActualValue) / parseFloat(billPlanValue) * 100).toFixed(2) : notAvailable;
        }
        $(this).text(billableCompleteVal);
        if(!$.isArray(tempValues[team][month][rowCompletedPlan])) {
            tempValues[team][month][rowCompletedPlan] = {};
        }
        tempValues[team][month][rowCompletedPlan]['value'] = billableCompleteVal;
    });
}

/*
 * refresh billable staff plan
 */
function updateBillStaffPlan(inputElm) {
    var _this = inputElm;
    var table = _this.closest('table');
    var team = _this.closest('.tab-pane').data('team');
    var tableHr = _this.closest('.tab-content').find('#' + team + '_hr table');
    var rowElm = table.find('tr[data-row="'+ rowBillStaffPlan +'"]');
    rowElm.find('.month-value span').each(function () {
        var month = $(this).data('month');
        var staffPlan = tableHr.find('tr[data-row="'+ rowHrPlan +'"] .month-value span[data-month="'+ month +'"]').text().trim();
        var billableVal = table.find('tr[data-row="'+ rowCompletedPlan +'"] .month-value span[data-month="'+ month +'"]').text().trim();
        var billStaffPlanVal = notAvailable;
        if (staffPlan !== '' && staffPlan !== notAvailable && billableVal !== notAvailable) {
            billStaffPlanVal = parseFloat(staffPlan) > 0 ? (parseFloat(billableVal) / parseFloat(staffPlan) * 100).toFixed(2) : notAvailable;
        }
        $(this).text(billStaffPlanVal);
        if(!$.isArray(tempValues[team][month][rowBillStaffPlan])) {
            tempValues[team][month][rowBillStaffPlan] = {};
        }
        tempValues[team][month][rowBillStaffPlan]['value'] = billStaffPlanVal;
    });
}

/*
 * refresh allocation actual / staff actual
 */
function updateAllocateStaff(inputElm) {
    var _this = inputElm;
    var table = _this.closest('table');
    var team = _this.closest('.tab-pane').data('team');
    var tableHr = _this.closest('.tab-content').find('#' + team + '_hr table');
    var rowElm = table.find('tr[data-row="'+ rowAlloStaffActual +'"]');
    rowElm.find('.month-value span').each(function () {
        var month = $(this).data('month');
        var staffPlan = tableHr.find('tr[data-row="'+ rowHrActual +'"] .month-value span[data-month="'+ month +'"]').text().trim();
        var billableVal = table.find('tr[data-row="'+ rowActual +'"] .month-value span[data-month="'+ month +'"]').text().trim();
        var billStaffPlanVal = notAvailable;
        if (staffPlan !== '' && staffPlan !== notAvailable && billableVal !== notAvailable) {
            billStaffPlanVal = parseFloat(staffPlan) > 0 ? (parseFloat(billableVal) / parseFloat(staffPlan) * 100).toFixed(2) : notAvailable;
        }
        $(this).text(billStaffPlanVal);
        if(!$.isArray(tempValues[team][month][rowAlloStaffActual])) {
            tempValues[team][month][rowAlloStaffActual] = {};
        }
        tempValues[team][month][rowAlloStaffActual]['value'] = billStaffPlanVal;
    });
}

/*
 * refresh billable staff actual value
 */
function updateBillStaffActual(inputElm) {
    var _this = inputElm;
    var table = _this.closest('table');
    var team = _this.closest('.tab-pane').data('team');
    var tableHr = _this.closest('.tab-content').find('#' + team + '_hr table');
    var rowElm = table.find('tr[data-row="'+ rowBillStaffActual +'"]');
    rowElm.find('.month-value span').each(function () {
        var month = $(this).data('month');
        var staffActual = tableHr.find('tr[data-row="'+ rowHrActual +'"] .month-value span[data-month="'+ month +'"]').text().trim();
        var billableVal = table.find('tr[data-row="'+ rowCompletedPlan +'"] .month-value span[data-month="'+ month +'"]').text().trim();
        var billStaffActualVal = notAvailable;
        if (staffActual !== '' && staffActual !== notAvailable && billableVal !== notAvailable) {
            billStaffActualVal = parseFloat(staffActual) > 0 ? (parseFloat(billableVal) / parseFloat(staffActual) * 100).toFixed(2) : notAvailable;
        }
        $(this).text(billStaffActualVal);
        if(!$.isArray(tempValues[team][month][rowBillStaffActual])) {
            tempValues[team][month][rowBillStaffActual] = {};
        }
        tempValues[team][month][rowBillStaffActual]['value'] = billStaffActualVal;
    });
}

/*
 * refresh production cost / staff actual
 */
function updateProductionCostStaff(inputElm) {
    var _this = inputElm;
    var table = _this.closest('table');
    var team = _this.closest('.tab-pane').data('team');
    var tableHr = _this.closest('.tab-content').find('#' + team + '_hr table');
    var rowElm = table.find('tr[data-row="'+ rowProdStaffActual +'"]');
    rowElm.find('.month-value span').each(function () {
        var month = $(this).data('month');
        var staffActual = tableHr.find('tr[data-row="'+ rowHrActual +'"] .month-value span[data-month="'+ month +'"]').text().trim();
        var billableVal = table.find('tr[data-row="'+ rowApprovedProdCost +'"] .month-value span[data-month="'+ month +'"]').text().trim();
        var billStaffActualVal = notAvailable;
        if (staffActual !== '' && staffActual !== notAvailable && billableVal !== notAvailable) {
            billStaffActualVal = parseFloat(staffActual) > 0 ? (parseFloat(billableVal) / parseFloat(staffActual) * 100).toFixed(2) : notAvailable;
        }
        $(this).text(billStaffActualVal);
        if(!$.isArray(tempValues[team][month][rowProdStaffActual])) {
            tempValues[team][month][rowProdStaffActual] = {};
        }
        tempValues[team][month][rowProdStaffActual]['value'] = billStaffActualVal;
    });
}

/**
 * Get values changed
 * Difference between values and tempValues
 * 
 * @param object values
 * @param objecat tempValues
 * @return void
 */
function compareArray(values, tempValues)
{
    var keyValues = [
        rowBillEffort,
        rowApprovedCost,
        rowApprovedProdCost,
        rowCost,
        rowBusinessEffective,
        rowTraningPlan,
        rowApprovedProdCost,
        rowCompletedPlan,
        rowAlloStaffActual,
        rowProdStaffActual,
    ];
    $.each(values, function (teamId, value) {
        for (var month=1; month<=12; month++) {
            $.each(keyValues, function (k, v) {
                if ((typeof values[teamId][month][v] === 'undefined'
                        && typeof tempValues[teamId][month][v] !== 'undefined'
                        && tempValues[teamId][month][v]['value'] !== '')
                    || (typeof values[teamId][month][v] !== 'undefined'
                        && typeof tempValues[teamId][month][v] !== 'undefined'
                        && values[teamId][month][v]['value'] !== tempValues[teamId][month][v]['value'])) {
                    if (typeof changeValues[teamId] === 'undefined') {
                        changeValues[teamId] = {};
                    }
                    if (typeof changeValues[teamId][month] === 'undefined') {
                        changeValues[teamId][month] = {};
                    }
                    if (typeof changeValues[teamId][month]['value'] === 'undefined') {
                        changeValues[teamId][month][v] = {};
                    }
                    changeValues[teamId][month][v]['value'] = tempValues[teamId][month][v]['value'];
                }
            });
        }
    });
}

//validate export form
(function ($) {
    jQuery.validator.addMethod('lessThan', function(value, element, param) {
        if (value && $(param).val()) {
            var arrValue = value.split('-');
            var arrParam = $(param).val().split('-');
            if (arrValue.length < 2 || arrParam.length < 2) {
                this.optional(element) || value <= $(param).val();
            }
            return this.optional(element) ||
                    (new Date(arrValue[1], arrValue[0]) <= new Date(arrParam[1], arrParam[0]));
        } 
        return true;
    }, startDateBefore);

    $('#form_import_billable').validate({
        rules: {
            team_id: {
                required: true,
            },
            from_month: {
                lessThan: '#im_to_month',
            },
            excel_file: {
                required: true,
            },
        },
    });

    $('#form_export_billable').validate({
        rules: {
            team_id: {
                required: true,
            },
            from_month: {
                required: true,
                lessThan: '#ex_to_month',
            },
            to_month: {
                required: true,
            },
        },
    });

    $('#form_import_billable').submit(function () {
        var form = $(this);
        if (!form.valid()) {
            return false;
        }
        var loading = form.find('.loading-block');
        loading.removeClass('hidden');
    });

    $('#form_export_billable').submit(function () {
        var form = $(this);
        if (!form.valid()) {
            return false;
        }
        var btn = form.find('button[type="submit"]');
        var loading = form.find('.loading-block');
        var errorBlock = form.find('.error-block');
        errorBlock.addClass('hidden');
        loading.removeClass('hidden');
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function (response) {
                var templates = response.templates;

                var wb = {SheetNames:[], Sheets:{}};
                var dataValidate = [];
                for (var rang in response.colFomulas) {
                    var values = response.colFomulas[rang];
                    if (Array.isArray(values)) {
                        for (var i in values) {
                            values[i] = htmlEntities(values[i]);
                        }
                    }
                    dataValidate.push({
                        sqref: rang, values: values,
                    });
                }

                for (var sheetName in templates) {
                    $('#tbl_template').append(templates[sheetName]);
                    var table = $('#tbl_template table:last')[0];
                    var wsheet = XLSX.utils.table_to_book(table, {
                        cellStyles: true,
                        cellDates: false,
                        cellFormula: true,
                    }).Sheets.Sheet1;
                    if (sheetName === response.sheetRunning || sheetName === response.sheetOpportunity) {
                        var sheetValidate = dataValidate.slice(0);
                        if (sheetName === response.sheetRunning) {
                            sheetValidate.push({
                                sqref: response.statusRange, values: response.listProjStatuses,
                            });
                        } else if (sheetName === response.sheetOpportunity) {
                            sheetValidate.push({
                                sqref: response.statusRange, values: response.listStatusLabels,
                            });
                        }
                        wsheet['!dataValidation'] = sheetValidate;
                        wsheet['!cols'] = [
                            {hidden: false, wch: 5,},
                            {hidden: false, wch: 5,},
                            {hidden: false, wch: 10,},
                            {hidden: false, wch: 20,},
                            {hidden: false, wch: 15,},
                            {hidden: false, wch: 5,},
                            {hidden: false, wch: 5,},
                        ];
                    }
                    if (sheetName === 'D_Config') {
                        wsheet['!cols'] = [
                            {hidden: false, wch: 30,},
                        ];
                    }
                    if (sheetName === 'Overview') {
                        wsheet['!cols'] = [
                            {hidden: false, wch: 5,},
                            {hidden: false, wch: 30,},
                        ];
                    }
                    $.each(wsheet, function (index, value) {
                        var idxRow = parseInt(index.replace(/\D+/g, ''));
                        var offsetRow = parseInt(response.offsetRow);
                        if ((idxRow === offsetRow - 1 || idxRow === offsetRow - 2)
                                && (sheetName === response.sheetRunning || sheetName === response.sheetOpportunity || sheetName === response.sheetMember)) {
                            value.s = {
                                fill: {
                                    patternType: 'solid',
                                    bgColor: {rgb: 'f5f5f5', theme: "1", tint: "-0.25", indexed: 64},
                                },
                                alignment: {
                                    vertical: 'center',
                                    horizontal: 'center',
                                },
                            };
                        }
                        if (value.v && value.v[0] === '=') {
                            value.f = value.v;
                            value.v = 0;
                            wsheet[index] = value;
                        }
                    });

                    wb.SheetNames.push(sheetName);
                    wb.Sheets[sheetName] = wsheet;
                }

                var wbout = XLSX.write(wb, {bookType: 'xlsx', bookSST: true, type: 'binary'});
                var fname = response.fileName + '.xlsx';
                try {
                    saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}), fname);
                } catch(e) {
                    //error
                    return;
                }
                $('#tbl_template').html('');
                $('#modal_export_billable').modal('hide');
            },
            error: function (error) {
                var mess = error.responseJSON || 'Error!';
                errorBlock.removeClass('hidden').text(mess);
            },
            complete: function () {
                btn.prop('disabled', false);
                loading.addClass('hidden');
            },
        });
        return false;
    });

    $('#modal_import_billable, #modal_export_billable').on('shown.bs.modal', function () {
        $(this).find('.select2-container').css('width', '100%');
        var teamId = $('.tab-pane-team.active').attr('data-team');
        $(this).find('select[name="team_id"]').val(teamId).trigger('change');
    });
    $('#modal_export_billable').on('hide.bs.modal', function () {
        var loadingBlock = $(this).find('form .loading-block');
        return loadingBlock.hasClass('hidden');
    });

    $(document).ready(function () {
        //trigger blur input if has data import
        if (mrHasPermissUpdate) {
            $('table tr[data-row="'+ rowCost +'"] input').blur();
        }
    });

})(jQuery);

/**
 * Remove duplicate value in array
 * 
 * @param array list
 * @return array
 */
function unique(list) {
  var result = [];
  $.each(list, function(i, e) {
    if ($.inArray(e, result) == -1 && typeof e != 'undefined') result.push(e);
  });
  return result;
}

/**
 * Get items in array by value
 * 
 * @param array array
 * @param int|string value
 * @return array
 */
function searchItemInArray(array, value)
{
    result = [];
    $.each(array, function (key, val) { 
        if (val == value) {
            result[key] = val;
        }
    });
    return result;
}

$('.start-month').change(function() {
   var value = $(this).val();
   for (var i=1; i<=12; i++) {
       if (i < value) {
           $('.end-month option[value="'+i+'"]').prop('disabled', true);
       } else {
           $('.end-month option[value="'+i+'"]').prop('disabled', false);
       }
   }
   $('.btn-search').trigger('click');
   setTimeout(function () {
       selectSearchReload();
   }, 200);
});

$('.end-month').change(function() {
   var value = $(this).val();
   for (var i=1; i<=12; i++) {
       if (i > value) {
           $('.start-month option[value="'+i+'"]').prop('disabled', true);
       } else {
           $('.start-month option[value="'+i+'"]').prop('disabled', false);
       }
   }
   $('.btn-search').trigger('click');
   setTimeout(function () {
       selectSearchReload();
   }, 200);
});

$('.year-filter').change(function() {
   $('.btn-search').trigger('click');
});

function showLoading()
{
    $('.loader-container').removeClass('hidden');
}

function hideLoading()
{
    $('.loader-container').addClass('hidden');
}

/*
 * convert spcial charactor
 */
function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
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