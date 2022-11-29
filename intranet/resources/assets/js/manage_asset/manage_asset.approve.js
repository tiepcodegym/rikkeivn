$(function() {
    // Call function init select2
    selectSearchReload();
    $('.checkbox-all input').on('ifClicked', function(event) {
        var checked = event.target.checked;
        if (checked) {
            $('.table-body input').iCheck('uncheck');
            $('.group-btn-approve').hide();
        } else {
            $('.table-body input').iCheck('check');
            $('.group-btn-approve').show();
        }
    });
    $('.table-body input').on('ifChanged', function(event) {
        var checked = event.target.checked;
        var countCheckbox = $('.table-body input:checkbox').length;
        var countCheckboxChecked = $('.table-body input:checked').length;

        if (checked) {
            if(countCheckbox === countCheckboxChecked) {  
                $('.checkbox-all input').iCheck('check');
            } else {
                $('.checkbox-all input').iCheck('uncheck');
            }
            $('.btn-approve-asset').prop('disabled', false);
            $('.btn-unapprove-asset').prop('disabled', false);
        } else {
            $('.checkbox-all input').iCheck('uncheck');
            if (countCheckboxChecked) {
                $('.btn-approve-asset').prop('disabled', false);
                $('.btn-unapprove-asset').prop('disabled', false);
            } else {
                $('.btn-approve-asset').prop('disabled', true);
                $('.btn-unapprove-asset').prop('disabled', true);
            }
        }
    });

    $('.datetime-picker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true,
    });

    RKfuncion.select2.init({
        enforceFocus: true
    });

    $('#employee_id').on('change', function() {
        $('#employee_id-error').hide();
    });
    $('#form_asset_allocation #employee_id').on('change', function() {
        var listCateId = [];
        $('#table_asset input[name="asset_category_id[]"]').each(function() {
            listCateId.push($(this).val());
        });
        $.ajax({
            url: urlAjaxGetRequestAsset,
            type: 'GET',
            data: {
                employeeId:  function () {
                    return $('#employee_id').val();
                },
                listCateId: listCateId,
            },
            success: function (data) {
                $('#request_asset').html(data.html);
                if (data.totalRequestAsset) {
                    $('#request_asset-error').hide();
                }
            },
        });
    });
    $('#request_asset').on('change', function() {
        $('#request_asset-error').hide();
    });
    $('#reason').on('keypress', function() {
        $('#reason-error').hide();
    });
    $('#received_date_picker').on('change', function() {
        $('#received_date-error').hide();
    });
    $('.form-disabled-submit').on('submit', function() {
        $('.btn-submit').prop('disabled', true);
    });
});

function validateSubmitAllocation() {
    var status = 0;

    var employeeId = $('#employee_id').val();
    if (employeeId === '' || employeeId === null) {
        status = 1;
        $('#employee_id-error').show();
    }
    var requestAsset = $('#request_asset').val();
    if (requestAsset === '' || requestAsset === null) {
        status = 1;
        $('#request_asset-error').show();
    }
    var receivedDate = $('#received_date').val();
    if (receivedDate === '' || receivedDate === null) {
        status = 1;
        $('#received_date-error').show();
    }
    var reason = $('#reason').val();
    if (reason === '' || reason === null) {
        status = 1;
        $('#reason-error').show();
    }
    if (status === 1) {
        return false;
    }
    return true;
}

function validateSubmit() {
    var status = 0;
    var warehouse = $('#warehouse').val();
    var receivedDate = $('#received_date').val();
    var reason = $('#reason').val();

    if (receivedDate === '' || receivedDate === null) {
        status = 1;
        $('#received_date-error').show();
    }
    if (reason === '' || reason === null) {
        status = 1;
        $('#reason-error').show();
    }
    if (warehouse === '' || warehouse === null) {
        status = 1;
        $('#warehouse-error').show();
    }
    if (status === 1) {
        return false;
    }
    return true;
}
