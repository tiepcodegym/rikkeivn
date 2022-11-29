$(document).on('submit', '#form_report_lost_and_broken', function () {
    var form = $(this);
    $.ajax({
        url: urlReport,
        data: {
            data: form.serialize(),
            _token: _token,
        },
        method: 'POST',
        success: function (res) {
            reportAssetLost(res.data, res.team, res.startDate, res.endDate, res.labelAsset);
            $('#report_lost_and_broken').modal('hide');
        },
    });
    return false;
});


function reportAssetLost(data, team, dateFrom, dateTo, label) {
    var teamName = team ? team.name : '';
    var content = [];
    content.push({
        stack: [
            {text: 'Danh sách tài sản hỏng, mất', style: 'header'},
            {text: 'Từ ngày ' + dateFrom + ' đến ngày ' + dateTo, alignment: 'center'},
        ],
    });
    content.push({text: 'Team quản lý: ' + teamName, style: 'headerEmp'});
    var tableAsset = {
        style: 'tableAsset',
        width: 'auto',
        table: {
            headerRows: 1,
            widths: [ 'auto', 80, 80, 80, 100, 100, 90, 120],
            body: [],
        },
    };
    if (data.length > 0) {
        data.forEach(function (dataItem, key) {
            tableAsset.table.body.push([
                key + 1, dataItem.code, dataItem.name, dataItem.user_name, dataItem.role_name, label[dataItem.state], dataItem.change_date, dataItem.reason,
            ]);
        });
    } else {
        tableAsset.table.body.push([
            {colSpan: 8,  text: 'Không có dữ liệu', bold: true},
        ]);
    }
    tableAsset.table.body.unshift(['STT', 'Mã tài sản', 'Tên tài sản', 'Người sử dụng', 'Chức vụ', 'Tình trạng', 'Ngày hỏng/mất', 'Lý do']);
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
    delete content[content.length - 1].pageBreak;
    pdfMake.createPdf({
        info: {
            title: 'Báo cáo danh sách tài sản hỏng, mất',
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
