/** 
 * 
 * iCheck load
 */
$('input').iCheck({
    checkboxClass: 'icheckbox_minimal-blue',
    radioClass: 'iradio_minimal-blue'
});   

$(function () { $('#rateit_star').rateit({min: 1, max: 10, step: 2}); });

$("#link-make").click(function(){
    $("#link-make").selectText();
});

/**
 * If .container-question height < child div reset this.height = new height
 */
$('.container-question').each(function(){
    var height = 0;
    $(this).find('.container-question-child').each(function(){
        if(height < $(this).outerHeight()){
            height = $(this).outerHeight();
        }
    });
    
    if($(this).height() < height){
        $(this).height(height);
    }
});

function removeError() {
    $('.inputTags-error').slideUp();
}

function showModalSuccess(text) {
    var $modalSuccess = $('#modal-success');
    $modalSuccess.find('.modal-body p').html(text);
    $('#modal-success').modal('show');
}

function showModalError(text) {
    var $modalError = $('#modal-error');
    $modalError.find('.modal-body p').html(text);
    $modalError.modal('show');
}

function showModalWarning(text) {
    var $modalWarning = $('#modal-warning');
    $modalWarning.find('.modal-body p').html(text);
    $modalWarning.modal('show');
}

function showModalConfirm(text) {
    var $modalConfirm = $('#modal-confirm');
    $modalConfirm.find('.modal-body p').html(text);
    $modalConfirm.modal('show');
}

function showModalConfirmDelete(text) {
    var $modalConfirmDelete = $('#modal-confirm-delete-email');
    $modalConfirmDelete.find('.modal-body p').html(text);
    $modalConfirmDelete.modal('show');
}

$('body').on('click', '.add-cus', function() { 
    var $container = $('.cus-row-container');
    var html = '<div class="cus-row margin-top-10 row cus-row-new">' +
                    '<div class="col-md-1">' +
                        '<input type="checkbox" class="check" />' +
                    '</div>' +
                    '<div class="col-md-2">' +
                        '<select class="form-control select-gender" name="select-gender">' +
                            '<option value="1">Mr.</option>' +
                            '<option value="0">Ms.</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<input type="text" class="form-control col-sm-3 customer-name" placeholder="Customer name">' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<input type="text" class="form-control col-sm-9 customer-email" placeholder="Email address">' +
                    '</div>' +
                    '<div class="col-md-1">' +
                        '<span class="btn-delete delete-x" onclick="removeRow(this);"><i class="fa fa-remove"></i></span>' +
                    '</div>' +
                '</div>';
    $container.append(html);    
    $('input').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_minimal-blue'
    });  
    $('#uncheck-all').addClass('hidden');
    $('#check-all').removeClass('hidden')
});

function removeRow(elem) {
    $(elem).parent().parent().remove();
    if ($('.check').filter(':checked').length == $('.check').length) {
        $('#check-all').addClass('hidden');
        $('#uncheck-all').removeClass('hidden');
    }
}

/**
 * Add error line
 * @param string text
 */
function addError(text) {
    return '<span class="error">'+text+'</span>';
}

/**
 * Send mail event
 */
$('#send-mail').on('click', function()
{
   $('.error').remove();
   var emailUnique = [];
   var valid = true;
   var checked = false; //check checkbox have any checked
   
   $('.cus-row').each(function(){
       var $check = $(this).find('.check');
       var name = $.trim($(this).find('.customer-name').val());
       var email = $.trim($(this).find('.customer-email').val());
        
        //Check required field and valid email
        if (name == '') {
            $(this).find('.customer-name').parent().append(addError(requiredField));
            valid = false;
        }
        if (email == '') {
            $(this).find('.customer-email').parent().append(addError(requiredField));
            valid = false;
        } else {
            if (!isEmail(email)) {
                 $(this).find('.customer-email').parent().append(addError(email_invalid));
                 valid = false;
             }
        }
        
        //Check email is exist
        if (email != '') {
            if (jQuery.inArray(email, emailUnique) == -1) {
                emailUnique.push(email);
                //if checkbox checked
                if ($check.length > 0 && $check.iCheck('update')[0].checked) {
                    checked = true; //have 1 or more checkbox checked
                }
            } else {
                $(this).find('.customer-email').parent().append(addError(email_exist));
                valid = false;
            }   
        }
   });
   
   if (!valid) {
       return false;
   }
   
   if (!checked) {
       showModalWarning(checkboxRequired);
       return false;
   }
   var selectTypeMail = '<label for="exampleFormControlSelect1" class="fs-17">Chọn mail gửi css</label>' +
                        '<select class="form-control h-35" id="typeMailSelect1">' +
                            '<option value="0">Chọn mail</option>' +
                            '<option value="1">Mail hoàn thành</option>' +
                            '<option value="2">Mail định kỳ</option>' +
                        '</select>' +
                        '<span class="message-select-mail hidden">Yêu cầu chọn kiểu loại mail trước khi gửi.</span>';
   $('#modal-confirm').find('.modal-body div').html(selectTypeMail);
   showModalConfirm(sureSendMail);
   
});

/**
 * Check all customer
 */
$('body').on('click', '#check-all', function() { 
    $('.cus-row').each(function(){
        var $check = $(this).find('.check');
        $check.iCheck('check');
    });
    $('#check-all').addClass('hidden');
    $('#uncheck-all').removeClass('hidden');
});

/**
 * Uncheck all customer
 */
$('body').on('click', '#uncheck-all', function() { 
    $('.cus-row').each(function(){
        var $check = $(this).find('.check');
        $check.iCheck('uncheck');
    });
    $('#uncheck-all').addClass('hidden');
    $('#check-all').removeClass('hidden');
});

// if all checkboxes are checked
$(document).on('ifChecked', '.check', function() {
    if ($('.check').filter(':checked').length == $('.check').length) {
        $('#check-all').addClass('hidden');
        $('#uncheck-all').removeClass('hidden');
    }
});

// if any checkbox uncheck
$(document).on('ifUnchecked', '.check', function() {
    $('#uncheck-all').addClass('hidden');
    $('#check-all').removeClass('hidden');
});

$('#send-mail-confirm').on('click', function(){
    var typeMail = $('#typeMailSelect1').val();
    if (typeMail == 0) {
        $('.message-select-mail').removeClass('hidden');
        return false;
    }
    $('#modal-confirm').modal('hide');
    var $button = $('#send-mail');
    var values = []; 
    var countEmail = $('.cus-row .check').length;
    $button.prop("disabled", true);
    $button.find('i').removeClass('hidden');
    $('.cus-row').each(function(){
       var $check = $(this).find('.check');
       var name = $.trim($(this).find('.customer-name').val());
       var email = $.trim($(this).find('.customer-email').val());
       var gender = $.trim($(this).find('.select-gender').val());
       
        //if checkbox checked
        if ($check.length > 0 && ($check.iCheck('update')[0].checked
            || countEmail == 1)) {
            values.push([name, email, gender]);
        }
    });
    $.ajax({
        url: urlSubmit,
        type: 'post',
        dataType: 'json',
        data: {
            _token: token, 
            emails: values,
            css_id: cssId,
            typeMail: typeMail,
        },
    })
    .done(function (json) {
        $button.prop("disabled", false);
        showModalSuccess(sendMailSuccess);
        $('#uncheck-all').addClass('hidden');
        $('#check-all').removeClass('hidden');
        $('input[type="checkbox"]').removeAttr('checked').iCheck('update');
        $button.find('span').html(resendText + '<i class="fa fa-spin fa-refresh hidden"></i>');
        $('.cus-row-new').each(function(i){
            var html = '<span class="btn-delete"><i class="fa fa-trash"></i></span>' +
                                '<input class="id-email-css" type="hidden" name="idEmailCss" value="'+json[i].cssMailId+'" />';
            $(this).find('input[type=text]').prop('disabled', true);
            $(this).find('.select-gender').prop('disabled', true);
            $(this).find('.btn-delete.delete-x').parent().addClass('delete-css-email');
            $(this).attr('id', 'item-'+json[i].cssMailId).removeClass('cus-row-new');
            $(this).find('.btn-delete.delete-x').after(html);
            $(this).find('.btn-delete.delete-x').remove();
        });
        textLastSend();
    })
    .fail(function () {
        $button.prop("disabled", false);
        $button.find('span').html(resendText + '<i class="fa fa-spin fa-refresh hidden"></i>');
        //alert("Ajax failed to fetch data");
    })
});

/**
 * Show/Hide preview
 */
$('.btn-show-hide').click(function (ev) {
    var t = ev.target
    $('#preview').toggle(500, function(){
       $(t).html($(this).is(':visible')? hideText : showText)
    });
    return false;
});

/**
 * Update last send mail date time for every customer send mail
 * @returns {undefined}
 */
function textLastSend() {
    $('.customer-email').each(function() {
        $(this).next('span').remove();
        $(this).after('<span>' + lastSendText + getNow() + '</span>');
        // window.location.reload();
    });
}

/**
 * get current datetime Y-m-d H:i:s
 * @returns {String}
 */
function getNow() {
    var currentdate = new Date(); 
    var datetime = currentdate.getFullYear() + "-"
                + (twoDigiss(currentdate.getMonth()+1))  + "-" 
                + twoDigiss(currentdate.getDate()) + " "  
                + twoDigiss(currentdate.getHours()) + ":"  
                + twoDigiss(currentdate.getMinutes()) + ":" 
                + twoDigiss(currentdate.getSeconds());
    return datetime;
}

/**
 * add 0 before number if number < 10
 * @param {int} number
 * @returns {int}
 */
function twoDigiss(number) {
    return ("00" + number).substr(-2);
}

/**
 * Delete mail event
 */
$('body').on('click', '.delete-css-email', function() {
    showModalConfirmDelete(sureDeleteMail);
    var idEmailCss = $(this).find('.id-email-css').val();
    $('.button-confirm-delete').val(idEmailCss);
});

/**
 * Submit delete mail event.
 */
$('#delete-mail-confirm').on('click', function(){
    $('#modal-confirm-delete-email').modal('hide');
    var idEmailCss = $('.button-confirm-delete').val();
    var values = [];
    values.push([idEmailCss]);
    $.ajax({
        url: urlSubmitDelete,
        type: 'post',
        dataType: 'json',
        data: {
            _token: token, 
            idEmail: values,
        },
    })
    showModalSuccess(deleteMailSuccess);
    $('#item-' + idEmailCss).remove();
});
