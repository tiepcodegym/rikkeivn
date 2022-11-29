var pageLength = 20;

$(document).ready(function () {
    $.fn.textWidth = function(text, font) {
        if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
        $.fn.textWidth.fakeEl.text(text || this.val() || this.text()).css('font', font || this.css('font'));
        return $.fn.textWidth.fakeEl.width();
    };

    var tblAssetProfile = $('#table_asset_profile').DataTable({
        processing: true,
        lengthChange: false,
        bFilter: false,
        serverSide: true,
        ajax: urlAssetProfile ,
        pageLength: pageLength,
        order: [[ 4, "desc" ]],
        bDestroy: true,
        ordering: false,
        info: true,
        autoWidth: false,
        columnDefs: [
            {
                'targets': 0,
                'searchable':false,
                'orderable':false,
                'render': function (data, type, full, meta) {
                    return '<input type="checkbox" value="' + full.id + '" data-state="' + full.state_asset + '" '+ ((full.status_confirm == ALLOCATION_CONFIRM_NONE || arrAssetCheck.indexOf(full.state_asset) !== -1 ) ? 'disabled' : '') +'>';
                },
            },
        ],
        select: {
            'style': 'multi',
        },
        columns: [
            {data: '', name: ''},
            {data: '', name: ''},
            {data: 'asset_code', name: 'asset_code'},
            {data: 'asset_name', name: 'asset_name'},
            {data: 'category_name', name: 'category_name'},
            {data: 'state', name: 'state'},
            {data: 'received_date', name: 'received_date'},
            {data: 'note', name: 'note'},
            {data: 'note_of_emp', name: 'note_of_emp'},
            {data: 'allocation_confirm', name: 'allocation_confirm'},
            {data: '', name: ''},
        ],
        fnDrawCallback: function() {
            var info = tblAssetProfile.page.info();
            $('#table_asset_profile tbody tr').each(function (index) {
                $(this).find('td:nth-child(2)').not('.dataTables_empty').html((info.page) * pageLength + index + 1);
            });
        },
    });

    $('#table_asset_profile thead tr.row-filter select').change(function() {
        tblAssetProfile.ajax.url( getUrlFilterProfile() ).load();
    });

    $('#table_asset_profile thead tr.row-filter input[type=text]').keyup(function(e) {
        var code = e.keyCode || e.which;
        if(code === 13) {
            tblAssetProfile.ajax.url( getUrlFilterProfile() ).load();
        }
    });
});

$('#table_asset_profile thead').append($('#table_asset_profile_2 thead').html());

function getUrlFilterProfile() {
    var url = urlAssetProfile;
    url += '?asset_code=' + $('#table_asset_profile .filter-asset_code').val();
    url += '&asset_name=' + $('#table_asset_profile .filter-asset_name').val();
    url += '&category_name=' + $('#table_asset_profile .filter-category_name').val();
    url += '&state=' + $('#table_asset_profile .filter-state').val();
    url += '&allocation_confirm=' + $('#table_asset_profile .filter-allocation_confirm').val();
    url += '&employee_id=' + $('#table_asset_profile .filter-employee_id').val();
    return url;
}

$(document).on('click', '.btn-allocation-confirm', function(event) {
    event.preventDefault();
    $('#asset_id_confirm').val($(this).val());
    $('#modal_confirm').modal('show');
});


var listIds = [];
var listState = [];
$('body').on('change', '#table_asset_profile input[type=checkbox]', function () {
    if ($(this).is(":checked")) {
        var state = $(this).attr('data-state');
        if ($.inArray(state, listState) === -1) {
            listState.push(state);
        }
        if ($.inArray($(this).val(), listIds) === -1) {
            listIds.push($(this).val());
        }
    } else {
        listIds.splice($.inArray($(this).val(), listIds), 1);
        listState.splice($.inArray($(this).val(), listState), 1);
    }
});

$('body').on('click', '.btn-asset-profile', function () {
    var button = $(this);
    switch (button.val()) {
        case typeHanding:
            textConfirm = confirmHandover;
            break;
        case typeLost:
            textConfirm = confirmLost;
            break;
        case typeBroken:
            textConfirm = confirmBroken;
            break;
    }
    if (listIds.length) {
        bootbox.confirm({
            message: textConfirm,
            buttons: {
                confirm: {
                    label: confirmYes,
                    className: 'btn-success width-80',
                },
                cancel: {
                    label: confirmNo,
                    className: 'btn-danger width-80 pull-left',
                },
            },
            className: 'modal-default',
            callback: function (result) {
                if (result) {
                    $.ajax({
                        url: urlConfirm,
                        method: 'POST',
                        data: {
                            listIds: listIds,
                            listState: listState,
                            type: button.val(),
                            _token: _token,
                            employeeId: $('#employee_id').val(),
                        },
                        success: function (res) {
                            bootbox.confirm({
                                message: '<h4>' + res.message + '</h4>'+ '<br>' + (res.html ? res.html : ''),
                                size: 'large',
                                backdrop: true,
                                className: 'modal-default modal-bootbox',
                                buttons: {
                                    confirm: {
                                        label: confirmYes,
                                        className: 'btn-success width-80',
                                    },
                                    cancel: {
                                        label: confirmNo,
                                        className: 'btn-danger width-80 pull-left hidden',
                                    },
                                },
                                callback: function (response) {
                                    if (response) {
                                        location.reload();
                                    }
                                },
                            });
                            $('#table_asset_profile input:checked').each(function () {
                                var id = $(this).closest('tr.bt-item').attr('data-id');
                                if (listIds.indexOf(id) !== -1) {
                                    $(this).closest('tr.bt-item input[type="checkbox"]').prop('disabled', 'true');
                                }
                            });
                        },
                    });
                }
            }
        });
    } else {
        bootbox.alert({
            message: validAsset,
            backdrop: true,
            className: 'modal-default modal-bootbox',
            buttons: {
                ok: {
                    label: confirmYes,
                    className: 'btn btn-primary width-80',
                },
            },
        });
    }
});
$(document).on('change', '.note_asset_profile', function () {
    var note = $(this).val();
    var assetId = $(this).closest('tr').find('input[type="checkbox"]').val();
    $.ajax({
        url: urlSaveNoteOfEmp,
        type: 'post',
        dataType: 'json',
        data: {
            note: note,
            assetId: assetId,
            _token: _token,
        },
        success: function (data) {
            if (!data.status) {
                bootbox.alert({
                    message: 'Error system',
                    backdrop: true,
                    className: 'modal-default modal-bootbox',
                });
            } else {
                bootbox.alert({
                    message: data.message,
                    backdrop: true,
                    className: 'modal-success modal-bootbox',
                });
            }
        },
        error: function () {
            if (!data.status) {
                bootbox.alert({
                    message: 'Error system',
                    backdrop: true,
                    className: 'modal-default modal-bootbox',
                });
            }
        }
    });
});

/**
 * show comment when hover or keyup
 */
$(document).ajaxComplete(function() {
    $('textarea.note_asset_profile').hover(function () {
        const commentEle = $(this).parent('.comment');
        if ($(this).textWidth() >= $(this).width()) {
            commentEle.addClass('dropdown');
        } else {
            commentEle.removeClass('dropdown');
        }
    });
    $('textarea.note_asset_profile').on('keyup', function (event) {
        const commentEle = $(this).parent('.comment');
        const content = $(this).val();
        var checkEnter = false;

        if (event.keyCode === 13) {
            checkEnter = true;
        }
        if ($(this).textWidth() >= $(this).width() || checkEnter) {
            commentEle.addClass('dropdown');
        } else if ($(this).textWidth() < $(this).width() && !checkEnter) {
            commentEle.removeClass('dropdown');
        }
        if (content === '') {
            commentEle.removeClass('dropdown');
        }
        commentEle.find('.dropdown-content').find('textarea').val(content);
    });
});