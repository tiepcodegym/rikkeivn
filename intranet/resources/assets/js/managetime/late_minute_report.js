$(function() {
    var matches = $('.ct-text-select option:selected').text();
    if (matches) {
        $('.select_team .select2-selection--single').text(matches.trim());
    }
    $('.select_team .select-search ').on('change', function() {
        var matches = $('.ct-text-select option:selected').text();
        $('.select_team .select2-selection--single').text(matches.trim());
    })
});

$('body').on('click', '.btn-export', function(e) {
    e.preventDefault();
    var _that = this;
    $(this).addClass('disabled');
    $(this).find('.fa-refresh').removeClass('hidden');

    var arrCheckItem = getCheckItem(sessionKeys);
    $.ajax({
        url: url_export,
        type: 'POST',
        dataType: 'json',
        data: {
            '_token': _token,
            'arrItem': arrCheckItem
        },
        success: function(result) {
            if (result.status) {
                var data = result.data;
                var ws_data = [];
                if (!data.length) {
                    alert('Không có nhân viên');
                    $(_that).find('.fa-refresh').addClass('hidden');
                    $(_that).removeClass('disabled');
                    return;
                }
                var wb = XLSX.utils.book_new();
                wb.SheetNames.push("Sheet 1");
                ws_data.push(result.heading);
                for (var index in data) {
                    ws_data.push([
                        data[index].employee_code,
                        data[index].employee_name,
                        data[index].team_name,
                        data[index].count_late_minute,
                        data[index].sum_late_minute,
                        data[index].total_fine_money
                    ]);
                }
                var ws = XLSX.utils.json_to_sheet(ws_data, { skipHeader: true });
                var wscols = [
                    { wch: 15 },
                    { wch: 20 },
                    { wch: 20 },
                ];
                ws['!cols'] = wscols;
                wb.Sheets["Sheet 1"] = ws;
                var wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });
                saveAs(new Blob([s2ab(wbout)], { type: "application/octet-stream" }), 'minute_late_report.xlsx');
                removeCheckItem(sessionKeys);
                $(_that).find('.fa-refresh').addClass('hidden');
                $(_that).removeClass('disabled');
            } else {
                alert(result.message);
                $(_that).find('.fa-refresh').addClass('hidden');
                $(_that).removeClass('disabled');
            }
        },
        error: function(e) {}
    });
})

function s2ab(s) {
    var buf = new ArrayBuffer(s.length); //convert s to arrayBuffer
    var view = new Uint8Array(buf); //create uint8array as viewer
    for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF; //convert to octet
    return buf;
}