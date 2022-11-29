function reportPdf(data, dateTo) {
    var content = [];
    data.forEach(function (dataItem) {
        // push header name
        content.push({
            stack: [
                {text: 'Báo cáo chi tiết tài sản theo nhân viên', style: 'header'},
                {text: 'Đến ngày ' + dateTo, style: 'toDate', alignment: 'center'},
            ],
        });
        content.push({text: 'Tên nhân viên: ' + dataItem.employee_name, style: 'headerEmp'});
        content.push({text: 'Chức vụ: ' + dataItem.role_name , style: 'headerEmp'});
        var tableAsset = {
            style: 'tableAsset',
            width: 'auto',
            table: {
                headerRows: 1,
                widths: [ 'auto', 80, 130, 100, 100, 100, 160],
                body: [],
            },
        };
        if (dataItem.assets.length > 0) {
            dataItem.assets.forEach(function (dataAsset, index) {
                tableAsset.table.body.push([
                    index + 1, dataAsset.code, dataAsset.name, dataAsset.category, dataAsset.state_asset, dataAsset.received_date, dataAsset.specification,
                ]);
            });
        } else {
            tableAsset.table.body.push([
                {colSpan: 7,  text: 'Không có dữ liệu', bold: true},
            ]);
        }
        tableAsset.table.body.unshift(['STT', 'Mã tài sản', 'Tên tài sản', 'Loại tài sản', 'Tình trạng', 'Ngày nhận', 'Quy cách tài sản']);
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
    delete content[content.length - 1].pageBreak;
    pdfMake.createPdf({
        info: {
            title: 'Báo cáo chi tiết tài sản theo nhân viên',
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

$(document).on('submit', '.form-report-by-employee', function(e) {
    var rows_selected = tableReportByEmp.column(0).checkboxes.selected();
    var type = $(this).find('input[name="report_type"]').val();
    var check = false;
    var employees = '';
    $.each(rows_selected, function(row, rowId) {
        check = true;
        employees += rowId + ',';
    });
    if (check) {
        $('.submit-report').prop('disabled', true);
        $.ajax({
            url: urlReport,
            method: 'POST',
            data: {
                data: $(this).serialize() + '&employees=' + employees,
                _token: _token,
            },
            dataType: 'JSON',
            success: function (res) {
                if (res.data) {
                    reportPdf(res.data, res.dateTo);
                    $('#modalReport-' + type).modal('hide');
                    $(".form-report-by-employee #team_id_report ").val(teamDefault).change();
                    $('.submit-report').prop('disabled', false);
                } else {
                    bootbox.alert("Error system");
                }
            },
        });
    } else {
        $('#no_employee-error').removeClass('hidden');
        $('.submit-report').prop('disabled', false);
    }
    return false;
});

$(document).on('change', '#team_id_report', function(e) {
    $.ajax({
        url: urlAjaxGetEmployeeToReport,
        type: 'GET',
        data: {
            teamId:  function () {
                return $('#team_id_report').val();
            },
        },
        success: function (data) {
            $('#group_employees').html(data.html);
        },
        complete: function() {
            $('.report input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_minimal-blue',
            });
            tableReportByEmp = $('.asset-emp-table-report').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "bDestroy": true,
                "ordering": false,
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
