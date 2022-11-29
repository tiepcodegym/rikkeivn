$(function () {

    $('#fromDate, #toDate').datetimepicker({
        format: 'Y-MM-DD',
        showClear: true
    });
    // event change
    $('#fromDate, #toDate').on('dp.change', function (e) {
        $('.btn-search-filter').click();
    });

    $('.detail-employee').on('click', function () {
        var fromDate = $('#fromDate').val();
        var toDate = $('#toDate').val();
        var paramFromDate = '';
        var paramToDate = '';

        if (fromDate.trim() !== '') {
            paramFromDate = '?from_date=' + fromDate.trim()
        }

        if (toDate.trim() !== '') {
            paramToDate = fromDate.trim() !== '' ? '&' : '?';
            paramToDate = paramToDate + 'to_date=' + toDate.trim()
        }
        window.location.href = $(this).attr('data-url') + '/' + paramFromDate + paramToDate
    });

    $('#form_export button[type="submit"]').click(function () {
        var btn = $(this);
        $('#modal-confirm-export').attr({disabled: true})
        setTimeout(function () {
            btn.prop('disabled', false);
            btn.closest('.modal').modal('hide');
            $('#modal-confirm-export').attr({disabled: false})
        }, 500);
    });

    selectSearchReload();
});