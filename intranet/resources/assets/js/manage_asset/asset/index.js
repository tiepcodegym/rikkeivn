var pageLength = 50;
$(document).ready(function () {
    var tblAsset = $('#table_asset').DataTable({
        processing: true,
        lengthChange: false,
        bFilter: false,
        bDeferRender: true,
        serverSide: true,
        ajax: {
            url: urlAsset,
            type: 'get',
            data : function (data) {
                data.ids  = $('#id_asset_profile').val();
                return data;
            },
        } ,
        pageLength: pageLength,
        order: [[ 4, "desc" ]],
        columnDefs: [
            {
                'targets': 0,
                'searchable':false,
                'orderable':false,
                'render': function (data, type, full, meta) {
                    return '<input type="checkbox" class="item-checked" value="' + full.id + '" data-state="' + full.state_asset + '" name="asset_id">';
                },
            },
        ],
        bDestroy: true,
        ordering: false,
        info: true,
        autoWidth: false,
        select: {
            'style': 'multi',
        },
        columns: [
            {data: '', name: ''},
            {data: '', name: ''},
            {data: 'asset_code', name: 'asset_code'},
            {data: 'asset_name', name: 'asset_name'},
            {data: 'category_name', name: 'category_name'},
            {data: 'warehouse_name', name: 'warehouse_name'},
            {data: 'manager_name', name: 'manager_name'},
            {data: 'email', name: 'email'},
            {data: 'role_name', name: 'role_name'},
            {data: 'received_date', name: 'received_date'},
            {data: 'state', name: 'state'},
            {data: 'approver', name: 'approver'},
            {data: 'allocation_confirm', name: 'allocation_confirm'},
            {data: '', name: ''},
        ],
        fnDrawCallback: function() {
            var info = tblAsset.page.info();
            $('#table_asset tbody tr').each(function (index) {
                var inputCheck = $(this).find('td input[type="checkbox"]');
                if ($.inArray(inputCheck.val(), listItem) !== -1) {
                    inputCheck.prop('checked', true);
                }
                $(this).find('td:nth-child(2)').not('.dataTables_empty').html((info.page) * pageLength + index + 1);
            });
        },
    });
    $('#table_asset thead tr.row-filter select').change(function() {
        tblAsset.ajax.url( getUrlFilter() ).load();
    });

    $('#table_asset thead tr.row-filter input[type=text]').keyup(function(e) {
        var code = e.keyCode || e.which;
        if(code === 13) {
            tblAsset.ajax.url( getUrlFilter() ).load();
        }
    });

    $('#table_asset .dt-checkboxes-select-all input[type="checkbox"]').change(function () {
        if ($(this).is(":checked")) {
            tblAsset.rows().select();
        } else {
            tblAsset.rows().deselect();
        }
    });

    function filterAdditional(that, _class) {
        if (that.checked) {
            $(_class).val('null');
        } else {
            $(_class).val('');
        }
        tblAsset.ajax.url(getUrlFilter()).load();
    }

    $('#checkConfigureNull').change(function() {
        filterAdditional(this, '.filter-configure_additional');
    })

    $('#checkSerialNull').change(function() {
        filterAdditional(this, '.filter-serial_additional');
    })
});

$('body').on('click', '.btn-get-asset-information', function() {
    var btnValue = $(this).val();
    $.ajax({
        url: urlAjaxGetAssetInformation,
        type: 'GET',
        data: {
            listItem: listItem,
            stateModal: btnValue,
            listState: listState,
        },
        success: function (data) {
            if (data.success === 1) {
                $('#form_get_asset_information').html(data.html);
                $('#modal_get_asset_information').modal('show');
            } else if(data.success === 0) {
                bootbox.alert({
                    className: 'modal-default',
                    message: '<h4>' + data.messages + '</h4>'+ '<br>' + (data.html ? data.html : ''),
                    backdrop: true,
                    size: 'large',
                    buttons: {
                        ok: {
                            label: confirmYes,
                        },
                    },
                });
            }
        },
        error: function () {
            window.location.reload();
        },
    });
});

var listItem = [], listState = [];
$('body').on('change', '#table_asset input[type=checkbox]', function() {
    if ($(this).is(":checked")) {
        var state = $(this).attr('data-state');
        if ($.inArray(state, listState) === -1) {
            listState.push(state);
        }
        if ($.inArray($(this).val(), listItem) === -1) {
            listItem.push($(this).val());
        }
    } else {
        listItem.splice($.inArray($(this).val(), listItem), 1);
        listState.splice($.inArray($(this).val(), listState), 1);
    }
});
$( document ).ajaxComplete(function() {
    if ($('.check-all').is(':checked')) {
        $("table").find('input:checkbox').attr('checked', 'checked');
        $.each($("input[name='asset_id']:checked"), function () {
            var state = $(this).attr('data-state');
            if ($.inArray(state, listState) === -1) {
                listState.push(state);
            }
            if ($.inArray($(this).val(), listItem) === -1) {
                listItem.push($(this).val());
            }
        });
    }
    $('.check-all').change(function () {
        $("table").find('input:checkbox').not(this).prop('checked', this.checked);
        if ($(this).is(':checked')) {
            $.each($("input[name='asset_id']:checked"), function () {
                var state = $(this).attr('data-state');
                if ($.inArray(state, listState) === -1) {
                    listState.push(state);
                }
                if ($.inArray($(this).val(), listItem) === -1) {
                    listItem.push($(this).val());
                }
            });
        } else {
            listItem = [];
            listState = [];
        }
    });
});
$('#table_asset thead').append($('#tbl_asset_2 thead').html());

function getUrlFilter() {
    var url = urlAsset;
    url += '?asset_code=' + $('#table_asset .filter-asset_code').val();
    url += '&asset_name=' + $('#table_asset .filter-asset_name').val();
    url += '&category_name=' + $('#table_asset .filter-category_name').val();
    url += '&warehouse_name=' + $('#table_asset .filter-warehouse_name').val();
    url += '&manager_name=' + $('#table_asset .filter-manager_name').val();
    url += '&user_name=' + $('#table_asset .filter-user_name').val();
    url += '&state=' + $('#table_asset .filter-state').val();
    url += '&allocation_confirm=' + $('#table_asset .filter-allocation_confirm').val();
    url += '&employee_id=' + $('#table_asset .filter-employee_id').val();
    url += '&_configure=' + $('#table_asset .filter-configure_additional').val();
    url += '&_serial=' + $('#table_asset .filter-serial_additional').val();
    return url;
}

function myFunction() {
    document.getElementById("myDropdown").classList.toggle("show");
}