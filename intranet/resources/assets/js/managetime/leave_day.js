jQuery(document).ready(function($) {
    $('input').iCheck({
        checkboxClass: 'icheckbox_flat-green'
    });

    $('.checkbox-all input').on('ifClicked', function(event) {
        var checked = event.target.checked;
        if (checked) {
            $('.checkbox-body input').iCheck('uncheck');
            $('.btn-delete-data').prop('disabled', true);
        } else {
            $('.checkbox-body input').iCheck('check');
            $('.btn-delete-data').prop('disabled', false);
        }
    });

    $('.checkbox-body input').on('ifChanged', function(event) {
        var checked = event.target.checked;
        var countCheckbox = $('.checkbox-body input:checkbox').length;
        var countCheckboxChecked = $('.checkbox-body input:checked').length;

        if (checked) {
            $('.btn-delete-data').prop('disabled', false);

            if (countCheckbox == countCheckboxChecked) {
                $('.checkbox-all input').iCheck('check');
            } else {
                $('.checkbox-all input').iCheck('uncheck');
            }
        } else {
            var countCheckboxUnChecked = countCheckbox - countCheckboxChecked;
            if (countCheckbox == countCheckboxUnChecked) {
                $('.btn-delete-data').prop('disabled', true);
            }
            $('.checkbox-all input').iCheck('uncheck');
        }
    });

    $('.btn-delete-data').click(function() {
        var listLeaveDayIds = [];
        $('.checkbox-body input:checked').each(function() {
            listLeaveDayIds.push($(this).val());
        });
        if (listLeaveDayIds.length > 0) {
            $('#register_id_delete').val(listLeaveDayIds);
            $('#modal_delete').modal('show');
        } else {
            $('#modal_noselect').modal('show');
        }
    });

    $('#button_delete_submit').click(function() {
        var leaveDayIds = $('#register_id_delete').val();
        var $this = $(this);
        $this.button('loading');

        $.ajax({
            type: "GET",
            url: urlDelete,
            data: {
                leaveDayIds: leaveDayIds,
            },
            success: function(data) {
                $('#modal_delete').modal('hide');
                window.location.reload();
            },
            error: function(error) {
                $('#modal_delete').modal('hide');
                window.location.reload();
            }
        });
    });

    $('.button-delete').click(function() {
        $('#register_id_delete').val($(this).val());
        $('#modal_delete').modal('show');
    });

    $(document).on("click", "tr.reason-data .reason-edit", function(e) {
        var data = $(this).closest('tr');
        console.log(data.find('.employee-code').text());
        $('#full_name').val(data.find('.full_name').text());
        $('#employee_code').val(data.find('.employee_code').text());
        $('#day_last_year').val(data.find('.day_last_year').text());
        $('#day_last_transfer').val(data.find('.day_last_transfer').text());
        $('#day_current_year').val(data.find('.day_current_year').text());
        $('#day_seniority').val(data.find('.day_seniority').text());
        $('#day_OT').val(data.find('.day_OT').text());
        $('#day_used').val(data.find('.day_used').text());
        $('#note').val(data.find('.note').text());
        $('#day_id').val(data.attr('day-id'));
        $('#form-submit-reason').find('input[type=text]').removeClass('error');
        $('#modal_edit_leave_day').modal('show');

        $('#day_last_year-error').remove();
        $('#day_current_year-error').remove();
        $('#day_OT-error').remove();
        $('#day_last_transfer-error').remove();
        $('#day_seniority-error').remove();
        $('#day_used-error').remove();
    });

    function maxTransfer() {
        return parseFloat($('#day_last_year').val());
    }

    $('#form-submit-day').validate({
        onkeyup: false,
        rules: {
            'day_last_year': {
                number: true,
                min: 0,
            },
            'day_last_transfer': {
                number: true,
                min: 0,
            },
            'day_current_year': {
                number: true,
                min: 0,
            },
            'day_seniority': {
                number: true,
                min: 0,
            },
            'day_OT': {
                number: true,
                min: 0,
            },
            'day_used': {
                number: true,
                min: 0,
            }
        },
        messages: {
            'day_last_year': {
                number: 'Bắt buộc là số và lớn hơn 0',
                min: 'Bắt buộc là số và lớn hơn 0',
            },
            'day_last_transfer': {
                number: 'Bắt buộc là số và lớn hơn 0',
                min: 'Bắt buộc là số và lớn hơn 0',
            },
            'day_current_year': {
                number: 'Bắt buộc là số và lớn hơn 0',
                min: 'Bắt buộc là số và lớn hơn 0',
            },
            'day_seniority': {
                number: 'Bắt buộc là số và lớn hơn 0',
                min: 'Bắt buộc là số và lớn hơn 0',
            },
            'day_OT': {
                number: 'Bắt buộc là số và lớn hơn 0',
                min: 'Bắt buộc là số và lớn hơn 0',
            },
            'day_used': {
                number: 'Bắt buộc là số và lớn hơn 0',
                min: 'Bắt buộc là số và lớn hơn 0',
            }
        }
    });

    $('#upload').on('change', function() {
        var el_this = $(this);
        var formData = new FormData();
        formData.append('file', el_this[0].files[0]);
        formData.append('_token', $('input[name=_token]').val());
        url = el_this.attr('url');
        el_this.parent().find('.fa-spin').removeClass('hidden');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(data) {
                window.location.reload();
            },
            error: function(error) {
                window.location.reload();
            },
        });
    });
});