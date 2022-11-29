$(document).ready(function () {
    $('#form_edit_asset_item').validate({
        rules: {
            'item[code]': {
                required: true,
                rangelength: [1, 100],
            },
            'item[name]': {
                required: true,
                rangelength: [1, 100],
            },
            'item[category_id]': {
                required: true,
            },
            'item[warehouse_id]': {
                required: true,
            },
            'item[serial]': {
                rangelength: [1, 100],
            },
            'item[warranty_exp_date]': {
                afterPurchaseDate: true,
            },
            'item[address]': {
                required: true,
            },
            'item[warranty_priod]': {
                validNumber: true,
                min: 0,
            },
        },
        messages: {
            'item[code]': {
                required: requiredText,
                rangelength: rangelengthText,
            },
            'item[name]': {
                required: requiredText,
                rangelength: rangelengthText,
            },
            'item[category_id]': {
                required: requiredText,
            },
            'item[warehouse_id]': {
                required: requiredText,
            },
            'item[serial]': {
                rangelength: rangelengthText,
            },
            'item[address]': {
                required: requiredText,
            },
            'item[warranty_priod]': {
                validNumber: requiredNumber,
                min: minNumber,
            },
        },
        errorPlacement: function(error, element) {
            var name = element.attr('name');
            if (name === 'item[warehouse_id]') {
                error.insertAfter( element.next("span"));
            } else {
                error.insertAfter(element);
            }
        },
    });

    $.validator.addMethod('afterPurchaseDate', function (value, element) {
        var purchaseDate = $('#purchase_date').val();
        var warrantyExpDate = $('#warranty_exp_date').val();
        if ((purchaseDate !== '') && (warrantyExpDate !== '')) {
            if (new Date(warrantyExpDate).getTime() < new Date(purchaseDate).getTime()) {
                return false;
            }
        }
        return true;
    }, 'Vui lòng chọn bằng hoặc sau ngày mua');

    $.validator.addMethod("validNumber", function(value, element) {
        if (value.trim()) {
            var regex = /^\d+$/;
            return regex.test(value.trim());
        }
        return true;
    }, requiredNumber);

});
$(function() {
    // Call function init select2
    selectSearchReload();
    $('.datetime-picker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true,
    });

    $('#purchase_datetime_picker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true,
    }).on('changeDate', function(selected) {
        var purchaseDate = new Date(selected.date.valueOf()), date = purchaseDate.getDate();
        purchaseDate.setDate(date);
        if (date === 28 || date === 29 || date === 30 || date === 31) {
            warrantyDate = lastDayOfMonth(purchaseDate.getFullYear(), purchaseDate.getMonth() + 1 + parseInt($('#warranty_priod').val()));
        } else {
            warrantyDate =  purchaseDate.setMonth(purchaseDate.getMonth() + parseInt($('#warranty_priod').val()));
        }
        warrantyDate = new Date(warrantyDate);
        $('#warranty_datetime_picker').datepicker('setDate', warrantyDate);
        $('#warranty_exp_date').prop('disabled', true);
    });

    $('#warranty_datetime_picker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true,
    });

    RKfuncion.select2.elementRemote(
        $('#manager_id')
    );

    $('#warranty_priod').on('change', function () {
        var purchaseDate =  $('#purchase_datetime_picker').datepicker('getDate');
        if (purchaseDate) {
            var date = purchaseDate.getDate();
            if (date === 28 || date === 29 || date === 30 || date === 31) {
                purchaseDate = lastDayOfMonth(purchaseDate.getFullYear(), purchaseDate.getMonth() + 1 + parseInt($('#warranty_priod').val()));
            } else {
                purchaseDate.setMonth(purchaseDate.getMonth() + parseInt($(this).val()));
            }
            warrantyDate = new Date (purchaseDate);
            $('#warranty_datetime_picker').datepicker('setDate', warrantyDate);
            $('#warranty_exp_date').prop('disabled', true);
        }
    });
    //iCheck for checkbox and radio inputs
    initCheckbox();

    $(document).on('change', '#category_id', function(e) {
        $.ajax({
            url: urlAjaxGetAssetAttributes,
            type: 'GET',
            data: {
                assetItemId:  function () {
                    return $('#asset_id').val();
                },
                assetCategoryId:  function () {
                    return $('#category_id').val();
                },
            },
            success: function (data) {
                $('#asset_attributes_list').html(data.html);
                $('#asset_code').val(data.code);
                //iCheck for checkbox and radio inputs
                initCheckbox();
            },
        });
    });
});

function initCheckbox() {
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_minimal-blue',
    });
}

function lastDayOfMonth(Year, Month) {
    return new Date( (new Date(Year, Month,1))-1 );
}