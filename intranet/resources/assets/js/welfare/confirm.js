$(document).ready(function () {
    var optionDatePicker = {
        autoclose: true,
        format: 'yyyy-mm-dd',
        weekStart: 1,
        todayHighlight: true
    };
    $('#birthday').datepicker(optionDatePicker);
    var token = $('input[name=_token]').val();
    $('div.alert').delay(10000).slideUp();
    
    $(document).on('change', '#is_register_relatives', function () {
        if (this.checked) {
            $('.panel-default').removeClass("disabledbutton hidden");
        } else {
            $('.panel-default').addClass("disabledbutton hidden");
        }
    });    
    $(document).on('click', '.confirm .btn-open-modal-add', function () {        
        $('#form-add-wel-empl-relatives').trigger('reset');
        $('#modal-add-relatives').modal('show');
    });
    
    $('#form-add-wel-empl-relatives').on('submit', function(event) {
        if (!$(this).valid()) {
            return false;
        }
        event.preventDefault();
        var url = $('#form-add-wel-empl-relatives').attr('action');
        var formData = new FormData($('#form-add-wel-empl-relatives')[0]);
        formData.append('_token', token);
        $.ajax({
            type: 'POST',
            url: url,            
            processData: false,
            cache: false,
            contentType: false,
            data: formData,
            success: function (data) {
                if (data.status == 'fail') {
                    $('#favorable-require-max').show();
                } else if (data.status == 'raletion') {
                    $('#modal-add-relatives').modal('hide');
                    $(".confirm-participation-welfare .confirm .table-responsive ").load(location.href + " .confirm-participation-welfare .confirm .table-responsive >*", "");   
                }
                $('.btn-add-wel-empl-relatives').removeAttr('disabled');
            }
        });
    });
    
    $(document).on('click', '.table-responsive .edit-relative-attach', function() {
       var url = $(this).data('url');
       var id = $(this).data('id');
      
       $.ajax({
           type: 'GET',
           url: url,
           data: {'key': id},
           success: function(data) {
                for (var key in data)
                {
                    $('#modal-add-relatives #' + key).val(data[key]);
                }
                setSelectRelation(data['welfare_id'], data['support_cost']);
                $('.relation_name_id').append('<option value="'+ data['relation_name_id'] +'" selected="selected">'+ data['relation_name'] +'</option>').trigger('change');
                if (data['card_id'] == "") {
                    $('#modal-add-relatives').find('#not_card_id').prop('checked', true);
                    $('#modal-add-relatives').find('.label-card-id').html(label.replace('<em>*</em>', ''));
                    $('.input-relative_card_id').addClass('hidden');;
                } else {
                    $('#modal-add-relatives').find('#is_card_id').prop('checked', true);
                }
               $('#modal-add-relatives').modal('show');
           }
       });
    });
    
    $(document).on('click', '.delete-relative-attach', function() {
        $('.form-confirm-delete').attr('action', $(this).data('url'));
        $('.form-confirm-delete').find('input[name=welid]').val($(this).data('id'));
        $('.form-confirm-delete').find('.deleteContent').html($(this).data('noti'));
        $('#modal-delete-relative-attach').removeClass('modal-warning');
        $('#modal-delete-relative-attach').addClass('modal-danger');
        $('#modal-delete-relative-attach').modal('show');   
    });
    
    $(document).on('click', '#modal-delete-relative-attach .btn-ok', function () {        
        var url = $('.form-confirm-delete').attr('action');
        var id = $('#modal-delete-relative-attach #id').val();
        var submit_destroy = $('#modal-delete-relative-attach input[name=submit_destroy]').val();
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                '_token': token,
                'welid': id,
                'submit_destroy' : submit_destroy
            },
            success: function (data) {
                $('#modal-delete-relative-attach').modal('hide');
                $('.flash-message').hide();
                if (data.status === 'confirm') {
                    window.scrollTo(0, 0);
                    $('.error-message li').text(data.messages);
                    $('.error-message').removeClass('hidden');
                    $(".confirm-participation-welfare").load(location.href + " .confirm-participation-welfare>*", "");
                } 
                if (data.status === "ok") {
                    $(".confirm-participation-welfare .confirm .table-responsive ").load(location.href + " .confirm-participation-welfare .confirm .table-responsive >*", ""); 
                }
            }
        });
    });
    
    $(document).on('click', '.submit-destroy', function() {
        $('.form-confirm-delete').attr('action', $('#confirm-participation').attr('action'));
        $('.form-confirm-delete').find('input[name=welid]').val($('#confirm-participation input[name=welid]').val());
        $('.form-confirm-delete').find('input[name=submit_destroy]').val('destroy');
        $('.form-confirm-delete').find('.deleteContent').html($(this).data('noti'));
        $('#modal-delete-relative-attach').removeClass('modal-danger');
        $('#modal-delete-relative-attach').addClass('modal-warning');
        $('#modal-delete-relative-attach').modal('show');  
    });
    
    var label = $('.label-card-id').html();
    $('#modal-add-relatives').on('hidden.bs.modal', function () {
        $('#form-add-wel-empl-relatives').trigger('reset');
        $(this).find('#is_card_id').prop('checked', true);
        $(this).find('.label-card-id').html(label);
        if ($(this).find('.relation_name_id').hasClass("select2-hidden-accessible")) {
            $(this).find('.relation_name_id').select2("destroy");
        }
        $(this).find('p.error').hide();
        $(this).find('#key').val('');
        $('.input-relative_card_id').removeClass('hidden');
        // $('.input-relative_card_id').find('#custom-error-card-id').addClass('hidden');
        $(this).find('label.error').css("display", "none");
        $('.btn-add-wel-empl-relatives').prop('disabled', false);
    });
    
    $(document).on("change", "input[name=is_card_id]",function(){
        var text = $('.label-card-id').html();  
        if ($('#is_card_id').is(":checked")) {
            var str = text + '<em>*</em>';
            $('.label-card-id').html(str);
            $('.input-relative_card_id').removeClass('hidden');
        }
        if ($('#not_card_id').is(":checked")) {
            var str = text.replace('<em>*</em>', '');
            $('.label-card-id').html(str);
            $('.input-relative_card_id').find('#card_id').val('');
            $('.input-relative_card_id').addClass('hidden');
            // $('.input-relative_card_id').find('#custom-error-card-id').addClass('hidden');
        }
    });
    
    $('.relation_name_id').select2();  
    $(document).on('change', '.fee_favorable_attached', function () {
        var wel_id = $('input[name="welid"]').val();
        var favorable = $(this).val();
        $('p.error').hide();
        setSelectRelation(wel_id, favorable);
    });
    
    // $(document).on('keyup', '.input-relative_card_id #card_id', function () {
    //     var cardId = $(this).val();
    //     if (cardId.trim().length == 0) {
    //         $('.input-relative_card_id').find('#custom-error-card-id').removeClass('hidden');
    //         $('.input-relative_card_id').find('#custom-error-card-id').show();
    //     } else {
    //         $('.input-relative_card_id').find('#custom-error-card-id').addClass('hidden');
    //     }
    // });
});

function checkFormSubmit()
{
    var count = 0;
    if ($('#is_card_id').is(":checked")) {
        var cardId = $('.input-relative_card_id').find('#card_id').val();
        if (cardId.trim().length == 0) {            
            $('.input-relative_card_id').find('#custom-error-card-id').removeClass('hidden');
            $('.input-relative_card_id').find('#custom-error-card-id').show();
            count++;
        }
    }
    if (!$('#form-add-wel-empl-relatives').valid()) {
        count++;
    }
    return count == 0;
}

function setSelectRelation(wel_id, favorable)
{
    var url = $('.fee_favorable_attached').data('url');
    $('.relation_name_id').select2({
        ajax: {
            url: url + '/' + wel_id + '/' + favorable,
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name,
                            id: item.id
                        }
                    })
                };
            },
            cache: true
        }
    });
}
