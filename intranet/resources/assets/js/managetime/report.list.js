"use strict";
$(function () {
    var fixTop = $('#position_start_header_fixed').offset().top;
    $(window).scroll(function () {
        var scrollTop = $(window).scrollTop();
        if (scrollTop > fixTop) {
            $('#managetime_table_fixed').css('top', scrollTop - $('.table-responsive').offset().top + 52);
            $('#managetime_table_fixed').show();
        } else {
            $('#managetime_table_fixed').hide();
        }
    });

    $('.datetime-picker').on('dp.change', function (e) {
        var filterDate = $('input#filter-date').val();
        var selCountry = $('select[name="sel_country"]').val();
        window.location.href = urlReportSearch + '/' + filterDate + '/' + selCountry;
    });
    $('select[name="sel_country"]').change(function () {
        var filterDate = $('input#filter-date').val();
        var selCountry = $('select[name="sel_country"]').val();
        window.location.href = urlReportSearch + '/' + filterDate + '/' + selCountry;
    });

    $('#btn-confirm-export').click(function () {
        $('#modal-confirm-export').modal('show');
    });
    $('#form_export_busines_trip button[type="submit"]').click(function () {
        var btn = $(this);
        setTimeout(function () {
            btn.prop('disabled', false);
            btn.closest('.modal').modal('hide');
        }, 500);
    });
});