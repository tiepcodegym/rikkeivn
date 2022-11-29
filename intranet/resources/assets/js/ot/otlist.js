//open modal and show register info
function showInfo(regId) {
    $('#register-preview').modal('show');
    $.ajax({
        url: urlSearchReg,
        type: 'GET',
        data: {"id": regId}
    }).done(function (result) {
        $('#preview_status').text(result['register']['status']);
        $('#employee_name').val(result['applicant']['name']);
        $('#employee_code').val(result['applicant']['employee_code']);
        $('#employee_position').val(result['role']);
        $('#register_approver').val(result['register']['approver_name']);
        $('#register_project').val(result['register']['projs_name']);
        $('#register_start_at').val(moment(result['register']['start_at'], 'DD-MM-YYYY HH:mm').format('DD-MM-YYYY HH:mm'));
        $('#register_end_at').val(moment(result['register']['end_at'], 'DD-MM-YYYY HH:mm').format('DD-MM-YYYY HH:mm'));
        $('#register_reason').val(result['register']['reason']);
        $('#view_detail').attr('href', urlEditReg + '/' + result['register']['id']);
        $('#view_detail').attr('target');
    });
}

//redirect to edit page
function editRegister(regId) {
    window.open(urlEditReg + "/" + regId);
}

//set delete register id
function setDeleteId(id) {
    $('#ot_id_delete').val(id);
    $('#delete_warning').find('.page_type').val(pageType);
}

//set approve register id
function setApproveId(id) {
    $('#ot_approve_id').val(id);
    $('#approve_confirm').find('.page_type').val(pageType);
    $('#approve_confirm').find('form').prop('action', urlApproveRegister);
}

//set reject register id
function setRejectId(id) {
    $('#ot_reject_id').val(id);
    $('#reject_confirm').find('.page_type').val(pageType);
    $('#reject_confirm').find('form').prop('action', urlRejectRegister);
    $("#reject_confirm").find("div.rejectError").empty();
}

//validate reject reason
function checkRejectReason() {
    $("#reject_confirm").find("button.submit").click(function (e) {
        if (!$("#reject_reason").val()) {
            $("#reject_confirm").find("div.rejectError").html(errList[0]);
        } else {
            $(this).prop('disabled', true);
            $("#reject_confirm form").submit();
        }
    });
    $("#reject_reason").on('input', function () {
        if ($(this).val()) {
            $("#reject_confirm").find("div.rejectError").empty();
        }
    });
}

//init control for checkbox in list page
function controlListCheckBox() {
    var triggeredByChild = false;
    //init iCheck checkBox
    $('#check_all, .check_select').iCheck({
        checkboxClass: 'icheckbox_flat-green',
    });

    $('#check_all').on('ifChecked', function (event) {
        $('.check_select').iCheck('check');
        if ($('.check_select').filter(':checked').length > 0) {
            $('.btn-approve, .btn-reject').prop('disabled', false);
        }
        triggeredByChild = false;
    });

    $('#check_all').on('ifUnchecked', function (event) {
        if (!triggeredByChild) {
            $('.check_select').iCheck('uncheck');
            $('.btn-approve, .btn-reject').prop('disabled', true);
        }
        triggeredByChild = false;
    });

    // Removed the checked state from "All" if any checkbox is unchecked
    $('.check_select').on('ifUnchecked', function (event) {
        triggeredByChild = true;
        $('#check_all').iCheck('uncheck');
        if ($('.check_select').filter(':checked').length == 0) {
            $('.btn-approve, .btn-reject').prop('disabled', true);
        }
    });

    $('.check_select').on('ifChecked', function (event) {
        if ($('.check_select').filter(':checked').length == $('.check_select').length) {
            $('#check_all').iCheck('check');
        }
        $('.btn-approve, .btn-reject').prop('disabled', false);
    });
}

//set approve modal when click approve button
$.fn.setApproveIds = function () {
    $(this).click(function () {
        var regIdList = [];
        $('#approve_confirm').modal('show');
        $('#approve_confirm').find('.page_type').val(pageType);
        $('tbody').find('input.check_select:checked').each(function () {
            regIdList.push($(this).val());
        });
        $('#ot_approve_id').val(regIdList);
        $('#approve_confirm').find('form').prop('action', urlMassApproveRegister);
    });
}

//set reject modal when click reject button
$.fn.setRejectIds = function () {
    $(this).click(function () {
        var regIdList = [];
        $('#reject_confirm').modal('show');
        $('#reject_confirm').find('.page_type').val(pageType);
        $('tbody').find('input.check_select:checked').each(function () {
            regIdList.push($(this).val());
        });
        $('#ot_reject_id').val(regIdList);
        $("#reject_confirm").find("div.rejectError").empty();
        $('#reject_confirm').find('form').prop('action', urlMassRejectRegister);
    });
}

//init filter date
$.fn.initFilterCalendar = function() {
    $(this).datetimepicker({
        allowInputToggle: true,
        format: 'DD-MM-YYYY',
        useCurrent: false
    });
    $(this).on('dp.change', function (e){
        $('.btn-search-filter').trigger('click');
    });
}

//init list page
function initList() {
    //init select team options
    $('#select-team-select2').select2();
    $('#select-team-select2').on("change", function () {
        $('#select-team-form').submit();
    });

    //allow expand/shorten text
    $(".ot-read-more").shorten({
        "showChars": 200,
        "moreText": "See more",
        "lessText": "Less",
    });
    
    $('.btn-approve').setApproveIds();
    $('.btn-reject').setRejectIds();
}

$(".ot-show-popup a").click(function() {
    $.ajax({
        type: "GET",
        data : { 
            registerId: $(this).attr('value'),
        },
        url: urlShowPopup,
        success: function (result) {
            $('#modal_view').html(result);
            $('#modal_view').modal('show');
        }
    });   
});