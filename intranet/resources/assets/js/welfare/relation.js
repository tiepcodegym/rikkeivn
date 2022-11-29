$(document).ready(function () {    
    
    $('div.alert').delay(10000).slideUp();
    
    $('#form-edit-relations-name').on('submit', function(e) {
        e.preventDefault();
        var url = $(this).attr('action');
        var dataForm = new FormData($(this)[0]);        
        $.ajax({
            type: "POST",
            url: url,
            data: dataForm,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.success == 0) {
                    $('#error-name').removeClass('hidden');                    
                    $('#error-name').html(data.messages.name);
                    $('#error-name').show();
                    $('.btn-add').prop('disable', true);
                }
                if (data.success == 1) {
                    $.cookie("message", data.messages.success);
                    window.location.replace(data.url);                                        
                }
            }          
        });
    });
    
    $(document).on('keyup', 'input[name=name]', function() {
        $('#error-name').addClass('hidden');
        $('#error-name').html('');
        $('#error-name').hide();
        if ($('#form-edit-relations-name').valid()) {
            $('.btn-add').prop('disabled', false);
        } else {
            $('.btn-add').prop('disabled', 'disabled');
        }
    });
    
    $(document).on('click', '#modal-delete-relation .btn-ok', function () {
        var url = $('#form-edit-relations-name').attr('action');
        var dataForm = new FormData($('#form-edit-relations-name')[0]);
        dataForm.append('submit_delete', 1);
        $.ajax({
            type: "POST",
            url: url,
            data: dataForm,
            processData: false,
            contentType: false,
            success: function (data) {                
                if (data.success == 1) {
                    window.location.replace(data.url);   
                }
            }
        });
    });
    
});

