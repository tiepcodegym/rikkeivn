$(function() {

    selectSearchReload();
    $('.statistic_date_picker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        endDate: new Date(),
    });

    $('body').on('change', '.asset-table-report tbody input[type="checkbox"], .asset-table-report thead input[type="checkbox"]', function(e) {
        setTimeout(function() {
            $('#no_asset-error').addClass('hidden');
            $('.submit-report').prop('disabled', false);
        }, 100);
    });
    $('body').on('change', '.asset-emp-table-report tbody input[type="checkbox"], .asset-emp-table-report thead input[type="checkbox"]', function(e) {
        setTimeout(function() {
            $('#no_employee-error').addClass('hidden');
            $('.submit-report').prop('disabled', false);
        }, 100);

    });
    $('body').on('click', '#show_modal_report_lost_and_broken', function() {
        $('#statistic_date-error').hide();
        $('#team_id-error').hide();
    });

    $('body').on('change', '.statistic_date_picker', function() {
        $('#statistic_date-error').hide();
    });
});

function submitReportLostAndBroken() {
    var status = 0;

    var statisticDate = $('#statistic_date').val();
    if (statisticDate === '' || statisticDate === null) {
        status = 1;
        $('#statistic_date-error').show();
    }
    if (status === 1) {
        return false;
    }
    $('#form_report_lost_and_broken').submit();
    return true;
}

$(document).on('click', '#report_by_employee', function () {
    $(".form-report-by-employee #team_id_report ").val(teamDefault).change();
    $.ajax({
        method: 'GET',
        url: urlAjaxGetEmployeeToReport,
        data: {
            teamId: teamDefault,
        },
        success: function (data) {
            $('#group_employees').html(data.html);
        },
        complete: function() {
            tableReportByEmp = $('.asset-emp-table-report').DataTable({
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


$(document).on('click', '#report_by_process', function () {
    $('.form-report-use-process').find('select').val('').change();
    $('#date_from').datepicker('setDate','');
    $('#date_to').datepicker('setDate','');
    $.ajax({
        method: 'GET',
        url: urlAjaxGetAssetToReport,
        data: {
            categoryId: null,
            teamId: null,
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