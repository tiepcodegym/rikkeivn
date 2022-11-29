$(function() {
    $('input').iCheck({
        checkboxClass: 'icheckbox_flat-green'
    });

    $('.checkbox-all input').on('ifClicked', function(event) {
        var checked = event.target.checked;
        if (checked) {
            $('#position_start_header_fixed input').iCheck('uncheck');
            $('.approve-submit').prop('disabled', true);
            $('.disapprove-submit').prop('disabled', true);
        } else {
            $('#position_start_header_fixed input').iCheck('check');
            $('.approve-submit').prop('disabled', false);
            $('.disapprove-submit').prop('disabled', false);
        }
    });
        
    var fixTop = $('#position_start_header_fixed').offset().top;
    $(window).scroll(function() {
        var scrollTop = $(window).scrollTop();
        if (scrollTop > fixTop) {
            $('#managetime_table_fixed').css('top', scrollTop - $('.table-responsive').offset().top +52);
            $('#managetime_table_fixed').show();
        } else {
            $('#managetime_table_fixed').hide();
        }
    });

    $('#position_start_header_fixed input').on('ifChanged', function(event) {
        var checked = event.target.checked;
        var countCheckbox = $('#position_start_header_fixed input:checkbox').length;
        var countCheckboxChecked = $('#position_start_header_fixed input:checked').length;

        if (checked) {
            $('.approve-submit').prop('disabled', false);
            $('.disapprove-submit').prop('disabled', false);

            if(countCheckbox == countCheckboxChecked) {  
                $('.checkbox-all input').iCheck('check');
            } else {
                $('.checkbox-all input').iCheck('uncheck');
            }
        } else {
            var countCheckboxUnChecked = countCheckbox - countCheckboxChecked;
            if(countCheckbox == countCheckboxUnChecked) {  
                $('.approve-submit').prop('disabled', true);
                $('.disapprove-submit').prop('disabled', true);
            }
            $('.checkbox-all input').iCheck('uncheck');
        }
    });

    $('.approve-submit').click(function() {
        var arrListIdRegister = [];
        $('#position_start_header_fixed input:checked').each(function() {
            arrListIdRegister.push($(this).val());
        });
        if (arrListIdRegister.length > 0) {
            $('#register_id_approve').val(arrListIdRegister);
            $('#modal_approve').modal('show');
        } else {
            $('#modal_noselect').modal('show');
        }
    });

    $('.disapprove-submit').click(function() {
        $('#reason_disapprove-error').hide();
        $('#reason_disapprove').val('');
        var arrListIdRegister = [];
        $('#position_start_header_fixed input:checked').each(function() {
            arrListIdRegister.push($(this).val());
        });
        if (arrListIdRegister.length > 0) {
            $('#register_id_disapprove').val(arrListIdRegister);
            $('#modal_disapprove').modal('show');
        } else {
            $('#modal_noselect').modal('show');
        }
    });

    $('#button_approve_submit').click(function() {
        var registerId = $('#register_id_approve').val();
        var urlCurrent = window.location.href.substr(window.location.href);
        var $this = $(this);
        $this.button('loading');

        $.ajax({
            type: "GET",
            url: urlApprove,
            data : { 
                registerId: registerId,
                urlCurrent: urlCurrent
            },
            success: function (result) {
                $('#modal_approve').modal('hide');
                var data = JSON.parse(result);
                window.location = data.url;
            }
        });
    });

    $('#button_disapprove_submit').click(function() {
        var registerId = $('#register_id_disapprove').val();
        var urlCurrent = window.location.href.substr(window.location.href);
        var reasonDisapprove = $('#reason_disapprove').val();
        if (reasonDisapprove.trim() == '') {
            $('#reason_disapprove-error').show();
            return;
        }
        var $this = $(this);
        $this.button('loading');

        $.ajax({
            type: "GET",
            url: urlDisapprove,
            data : { 
                registerId: registerId,
                urlCurrent: urlCurrent,
                reasonDisapprove: reasonDisapprove
            },
            success: function (result) {
                $('#modal_disapprove').modal('hide');
                var data = JSON.parse(result);
                window.location = data.url;
            }
        });
    });

    $('#reason_disapprove').keyup(function() {
        var reasonDisapprove = $('#reason_disapprove').val();
        if (reasonDisapprove.trim() == '') {
            $('#reason_disapprove-error').show();
        } else {
            $('#reason_disapprove-error').hide();
        }
    });
});