$(document).ready(function () {
    var validator = $('#form_add_asset_supplier').validate({
        rules: {
            'item[code]': {
                required: true,
                rangelength: [1, 100],
                remote: {
                    type: 'GET',
                    url: urlCheckExits,
                    data: {
                        name: 'code',
                        supplierId:  function () {
                            return $('#asset_supplier_id').val();
                        },
                        value: function () {
                            return $('#asset_supplier_code').val().trim();
                        },
                    },
                },
            },
            'item[name]': {
                required: true,
                rangelength: [1, 100],
                remote: {
                    type: 'GET',
                    url: urlCheckExits,
                    data: {
                        name: 'name',
                        supplierId:  function () {
                            return $('#asset_supplier_id').val();
                        },
                        value: function () {
                            return $('#asset_supplier_name').val().trim();
                        },
                    },
                },
            },
            'item[address]': {
                required: true,
                rangelength: [1, 255],
            },
            'item[phone]': {
                digits: true,
                rangelength: [1, 20],
            },
            'item[email]': {
                email: true,
                rangelength: [1, 100],
            },
            'item[website]': {
                rangelength: [1, 100],
            },
        },
        messages: {
            'item[code]': {
                required: requiredText,
                rangelength: rangelength100,
                remote: uniqueAssetSupplierCode,
            },
            'item[name]': {
                required: requiredText,
                rangelength: rangelength100,
                remote: uniqueAssetSupplierName,
            },
            'item[address]': {
                required: requiredText,
                rangelength: rangelength255,
            },
            'item[phone]': {
                digits: numberDigit,
                rangelength: rangelength20,
            },
            'item[email]': {
                email: invalidEmail,
                rangelength: rangelength100,
            },
            'item[website]': {
                rangelength: rangelength100,
            },
        },
    });
    $('.btn-reset-validate').click(function () {
        validator.resetForm();
        $('#form_add_asset_supplier').find('.error').removeClass('error');
    });
});
$(document).on('click', '#btn_add_asset_supplier', function () {
    $('#form_add_asset_supplier input[type=text], #form_add_asset_supplier input[name="id"]').val('');
    $('#asset_supplier_code').val(supplierCode);
    $('#modal_add_asset_supplier .modal-title').html(titleAddSupplier);
    $('#modal_add_asset_supplier').modal('show');
});
$(document).on('click', '.btn-edit-asset-supplier', function () {
    var data = $(this).closest('tr');
    $('#asset_supplier_id').val(data.attr('asset-supplier-id'));
    $('#asset_supplier_code').val(data.attr('asset-supplier-code'));
    $('#asset_supplier_name').val(data.attr('asset-supplier-name'));
    $('#asset_supplier_address').val(data.attr('asset-supplier-address'));
    $('#asset_supplier_phone').val(data.attr('asset-supplier-phone'));
    $('#asset_supplier_email').val(data.attr('asset-supplier-email'));
    $('#asset_supplier_website').val(data.attr('asset-supplier-website'));
    $('#modal_add_asset_supplier .modal-title').html(titleInfoSupplier);
    $('#modal_add_asset_supplier').modal('show');
});
$(document).on('keyup', '#asset_supplier_name', function () {
    $('.btn-submit').attr('disabled', false);
});


