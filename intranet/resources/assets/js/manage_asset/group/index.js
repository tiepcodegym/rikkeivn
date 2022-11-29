/**
 * Created by root on 05/07/2018.
 */
$(document).ready(function () {
    var validator = $('#form_add_asset_group').validate({
        rules: {
            'item[name]': {
                required: true,
                rangelength: [1, 100],
                remote: {
                    type: 'GET',
                    url: urlCheckExistGroupName,
                    data: {
                        assetGroupId: function () {
                            return $('#asset_group_id').val();
                        },
                        assetGroupName: function () {
                            return $('#asset_group_name').val();
                        },
                    },
                },
            },
        },
        messages: {
            'item[name]': {
                required: requiredText,
                rangelength: rangelengthText,
                remote: uniqueAssetGroupName,
            },
        },
    });
    $('.btn-reset-validate').click(function() {
        validator.resetForm();
        $('#form_add_asset_group').find('.error').removeClass('error');
    });
});
$(document).on('click', '#btn_add_asset_group', function () {
    $('#form_add_asset_group input[type=text], textarea, #form_add_asset_group input[name="id"]').val('');
    $('#modal_add_asset_group .modal-title').html(titleAddOrigin);
    $('#modal_add_asset_group').modal('show');
});
$(document).on('click', '.btn-edit-asset-group', function () {
    var data = $(this).closest('tr');
    $('#asset_group_id').val(data.attr('asset-group-id'));
    $('#asset_group_name').val(data.attr('asset-group-name'));
    $('#asset_group_note').val(data.attr('asset-group-note'));
    $('#modal_add_asset_group .modal-title').html(titleInfoOrigin);
    $('#modal_add_asset_group').modal('show');
});
$(document).on('keyup', '#asset_group_name', function () {
    $('.btn-submit').attr('disabled', false);
});
