$(document).ready(function () {
    $('#form_add_asset_warehouse').validate({
        ignore: '',
        rules: {
            'item[name]': {
                required: true,
                remote: {
                    type: 'GET',
                    url: checkExist,
                    data: {
                        _token: token,
                        name: 'name',
                        warehouseId: function () {
                            return $("#warehouse_id").val();
                        },
                        value: function () {
                            return $("#warehouse_name").val().trim();
                        },
                    },
                },
            },
            'item[code]': {
                required: true,
                remote: {
                    type: 'GET',
                    url: checkExist,
                    data: {
                        _token: token,
                        name: 'code',
                        warehouseId: function () {
                            return $("#warehouse_id").val();
                        },
                        value: function () {
                            return $("#warehouse_code").val().trim();
                        },
                    },
                },
            },
            'item[address]': {
                required: true,
            },
            'item[manager_id]': {
                required: true,
            },
            'item[branch]': {
                required: true,
            },
        },
        messages: {
            'item[name]': {
                required: requiredText,
                remote: uniqueName,
            },
            'item[code]': {
                required: requiredText,
                remote: uniqueCode,
            },
            'item[address]': {
                required: requiredText,
            },
            'item[manager_id]': {
                required: requiredText,
            },
            'item[branch]': {
                required: requiredText,
            },
        },
    });
});

$(document).on('click', '#btn_add_asset_warehouse', function () {
    $('#modal_add_asset_warehouse .modal-title').html(titleAdd);
    $('#form_add_asset_warehouse input[type=text], #form_add_asset_warehouse textarea, #form_add_asset_warehouse input[name="item[id]"]').val('');
    $('#modal_add_asset_warehouse').modal('show');
});

$(document).on('click', '.btn-edit-warehouse', function () {
    $('#modal_add_asset_warehouse .modal-title').html(titleEdit);
    var item = $(this).closest('tr');
    $('#warehouse_id').val(item.attr('data-id'));
    $('#warehouse_name').val(item.attr('data-name'));
    $('#warehouse_code').val(item.attr('data-code'));
    $('#warehouse_address').val(item.attr('data-address'));
    $('#warehouse_manager_id').val(item.attr('data-manager-id')).change();
    $('#warehouse_branch').val(item.attr('data-branch')).change();
    $('#modal_add_asset_warehouse').modal('show');
});
