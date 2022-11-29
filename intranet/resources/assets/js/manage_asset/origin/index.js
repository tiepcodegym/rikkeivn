/**
 * Created by root on 05/07/2018.
 */
$(document).ready(function () {
    var validator = $('#form_add_asset_origin').validate({
        rules: {
            'item[name]': {
                required: true,
                rangelength: [1, 100],
                remote: {
                    type: 'GET',
                    url: urlCheckExistOriginName,
                    data: {
                        assetOriginId:  function () {
                            return $('#asset_origin_id').val();
                        },
                        assetOriginName: function () {
                            return $('#asset_origin_name').val();
                        },
                    },
                },
            },
        },
        messages: {
            'item[name]': {
                required: requiredText,
                rangelength: rangelengthText,
                remote: uniqueAssetOriginName,
            },
        },
    });
    $('.btn-reset-validate').click(function() {
        validator.resetForm();
        $('#form_add_asset_origin').find('.error').removeClass('error');
    });
});

$(document).on('click', '#btn_add_asset_origin', function() {
    $('#form_add_asset_origin input[type=text], #form_add_asset_origin textarea, #form_add_asset_origin input[name="id"]').val('');
    $('#modal_add_asset_origin .modal-title').html(titleAddOrigin);
    $('#modal_add_asset_origin').modal('show');
});
$(document).on('click', '.btn-edit-asset-origin', function() {
    var data = $(this).closest('tr');
    $('#asset_origin_id').val(data.attr('asset-origin-id'));
    $('#asset_origin_name').val(data.attr('asset-origin-name'));
    $('#asset_origin_note').val(data.attr('asset-origin-note'));
    $('#modal_add_asset_origin .modal-title').html(titleInfoOrigin);
    $('#modal_add_asset_origin').modal('show');
});
$(document).on('keyup', '#asset_origin_name', function() {
    $('.btn-submit').attr('disabled', false);
});
