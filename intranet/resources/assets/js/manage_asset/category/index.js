$(document).ready(function () {
    var validator = $('#form_add_asset_category').validate({
        rules: {
            'item[name]': {
                required: true,
                rangelength: [1, 100],
                remote: {
                    type: 'GET',
                    url: checkExitCate,
                    data: {
                        name: 'name',
                        assetCategoryId: function () {
                            return $('#asset_category_id').val();
                        },
                        value: function () {
                            return $('#asset_category_name').val().trim();
                        },
                    },
                },
            },
            'item[group_id]': {
                required: true,
            },
            'item[prefix_asset_code]': {
                required: true,
                rangelength: [1, 20],
                remote: {
                    type: 'GET',
                    url: checkExitCate,
                    data: {
                        name: 'prefix_asset_code',
                        assetCategoryId: function () {
                            return $('#asset_category_id').val();
                        },
                        value: function () {
                            return $('#prefix_asset_code').val().trim();
                        },
                    },
                },
            },
        },
        messages: {
            'item[name]': {
                required: requiredText,
                rangelength: rangelengthText,
                remote: uniqueAssetCategoryName,
            },
            'item[group_id]': {
                required: requiredText,
            },
            'item[prefix_asset_code]': {
                required: requiredText,
                rangelength: rangelengthText20,
                remote: uniqueAssetCodePrefix,
            },
        },
    });
    $('#prefix_asset_code').bind('copy paste cut', function (e) {
        e.preventDefault();
    });
    $('.btn-reset-validate').click(function () {
        validator.resetForm();
        $('#form_add_asset_category').find('.error').removeClass('error');
    });
});

$(document).on('click', '#btn_add_asset_category', function () {
    $('#form_add_asset_category input[type=text], #form_add_asset_category textarea, #form_add_asset_category input[name="id"]').val('');
    $('#form_add_asset_category').find('.asset_group_id option:selected').prop('selected', false);
    $('#asset_group_id').val(valueDefaultAssetGroup).trigger('change');
    $('#modal_add_asset_category .modal-title').html(titleAddCate);
    $('#modal_add_asset_category').modal('show');
});
$(document).on('click', '.btn-edit-asset-category', function () {
    var data = $(this).closest('tr');
    $('#asset_category_id').val(data.attr('asset-category-id'));
    $('#asset_category_name').val(data.attr('asset-category-name'));
    $('#prefix_asset_code').val(data.attr('asset-code-prefix'));
    $('#asset_category_note').val(data.attr('asset-category-note'));
    $('#is_default').prop('checked', parseInt(data.attr('is-default')) === 1 ? true : false);
    $('#asset_group_id').val(data.attr('asset-group-id')).trigger('change');
    $('#modal_add_asset_category .modal-title').html(titleInfoCate);
    $('#modal_add_asset_category').modal('show');
});
$(document).on('keyup', '#asset_category_name', function () {
    $('.btn-submit').attr('disabled', false);
});
$(document).on('keypress', '#prefix_asset_code', function (e) {
    var keycode = e.charCode || e.keyCode;
    if (keycode === 32) {
        return false;
    }
    if (!((keycode > 47 && keycode < 91) || (keycode > 96 && keycode < 123) || (keycode === 32) || (keycode === 0))) {
        return false;
    }
});
