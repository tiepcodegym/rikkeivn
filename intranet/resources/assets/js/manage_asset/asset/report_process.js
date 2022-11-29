function reportProcessPdf(data, startDate, endDate) {
    var content = [];
    data.forEach(function (dataItem) {
        // push header name
        content.push({
            stack: [
                {text: 'Báo cáo biến động tài sản', style: 'header'},
            ],
        });
        if (startDate && endDate) {
            content.push({text: 'Từ ngày ' + startDate + 'Đến ngày ' + endDate, style: 'toDate', alignment: 'center'});
        } else {
            if (startDate) {
                content.push({text: 'Từ ngày ' + startDate, style: 'toDate', alignment: 'center'});
            }
            if (endDate) {
                content.push({text: ' Đến ngày ' + endDate, style: 'toDate', alignment: 'center'});
            }
        }
        content.push({text: 'Mã tài sản: ' + dataItem.asset_code, style: 'headerEmp'});
        content.push({text: 'Tên tài sản: ' + dataItem.asset_name , style: 'headerEmp'});
        var tableAsset = {
            style: 'tableAsset',
            width: 'auto',
            table: {
                headerRows: 1,
                widths: [ 'auto', 80, 130, 100, 100, 100, 165],
                body: [],
            },
        };
        if (dataItem.history.length > 0) {
            dataItem.history.forEach(function (history, index) {
                tableAsset.table.body.push([
                    index + 1, history.employee_code, history.employee_name, history.role_name, history.state_history, history.created_at, history.change_reason,
                ]);
            });
        } else {
            tableAsset.table.body.push([
                {colSpan: 7,  text: 'Không có dữ liệu', bold: true},
            ]);
        }
        tableAsset.table.body.unshift(['STT', 'Mã nhân viên', 'Tên nhân viên', 'Chức vụ', 'Tình trạng', 'Ngày', 'Lý do']);
        content.push(tableAsset);
        content.push({
            style: 'footer',
            columns: [
                {},{},
                {
                    stack: [
                        {text: 'Ngày...tháng...năm......', style: 'fontItalics'},
                        {text: 'NGƯỜI LẬP BIỂU', bold: true},
                        {text: '(Ký, ghi rõ họ tên)', style: 'fontItalics'},
                    ],
                },
            ],
            pageBreak: "after",
        });
    });
    if (content[content.length - 1]) {
        delete content[content.length - 1].pageBreak;
    }
    pdfMake.createPdf({
        info: {
            title: 'Báo cáo biến động tài sản',
        },
        content: content,
        styles: {
            headerEmp: {
                fontSize: 13,
                bold: true,
            },
            tableAsset: {
                alignment: 'center',
                margin: [0, 10, 0, 0],
            },
            header: {
                fontSize: 16,
                bold: true,
                alignment: 'center',
                margin: [0, 40, 0, 0],
            },
            fontItalics: {
                italics: true,
            },
            footer: {
                margin: [0, 20, 0, 0],
                alignment: 'center',
            },
            toDate: {
                italics: true,
                margin: [0, 0, 0, 30],
            },
        },
        pageMargins: [50, 30],
        pageOrientation: 'landscape',
    }).open();
}

$(document).on('submit', '.form-report-use-process', function(e) {
    var type = $(this).find('input[name="report_type"]').val();
    var rows_selected = tableReport.column(0).checkboxes.selected();
    var check = false;
    var assetIds = '';
    $.each(rows_selected, function(row, rowId) {
        check = true;
        assetIds += rowId + ',';
    });
    if (check) {
        $('button[type=submit]').prop('disabled', true);
        $.ajax({
            url: urlReport,
            method: 'POST',
            data: {
                data: $(this).serialize() + '&assets=' + assetIds,
                _token: _token,
            },
            dataType: 'JSON',
            success: function (res) {
                if (res.data) {
                    reportProcessPdf(res.data, res.startDate, res.endDate);
                    $('#modalReport-' + type).modal('hide');
                    $('.form-report-use-process').find('#team_id_report option:selected, #category_id_report option:selected').prop('selected', false);
                    $('button[type=submit]').prop('disabled', false);
                } else {
                    bootbox.alert("Error system");
                }
            },
        });
    }
    if (!check) {
        $('#no_asset-error').removeClass('hidden');
        $('button[type=submit]').prop('disabled', false);
    }
    return false;
});

$(document).on('change', '#category_id_report, #team_id, #date_from, #date_to', function(e) {
    $.ajax({
        url: urlAjaxGetAssetToReport,
        type: 'GET',
        data: {
            categoryId: function () {
                return $('#category_id_report').val();
            },
            teamId: function () {
                return $('#team_id').val();
            },
            dateFrom: $('#date_from').val(),
            dateTo: $('#date_to').val(),
        },
        success: function (data) {
            $('#group_assets').html(data.html);
        },
        complete: function() {
            tableReport = $('.asset-table-report').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": false,
                "bDestroy": true,
                "info": true,
                "autoWidth": false,
                'columnDefs': [
                    {
                        'targets': 0,
                        'checkboxes': {
                            'selectRow': true,
                        },
                    },
                ],
                'select': {
                    'style': 'multi',
                },
                'order': [[1, 'asc']],
            });
        },
    });
});