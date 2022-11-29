var modalSendMail = $('#modal-send-mail');
var modalPreviewEmail = $('#modal-preview-email');
var btnSendMail = $('.btn-send-mail');
var chkAllItems = $('.check-all-items');

RKfuncion.CKEditor.init(['content']);
CKEDITOR.config.height = 390;
selectSearchReload();
RKfuncion.select2.elementRemote(
    $('#request_filter')
);
$('#recruiterList').select2();
function formatState(opt) {
    if (!opt.id) {
        return opt.text;
    }
    return $('<span><i class="fa fa-star-o ' + $(opt.element).attr('class') + '"></i> ' + opt.text + '</span>');
}
$("#interested").select2({
    templateResult: formatState,
    templateSelection: formatState,
});

function getTotalCheckedWithEnable() {
    var total = 0;
    $('.check-item:enabled').map(function (index, el) {
        total += $(el).is(':checked') ? 1 : 0;
    });
    return total;
}
chkAllItems.on('click', function () {
    var domTable = $(this).closest('table');
    domTable.find('.check-item:disabled').prop('checked', false);
    domTable.find('.check-item:enabled').prop('checked', $(this).is(':checked'));
    btnSendMail.attr('disabled', !$('.check-item:checked').length);
});
$(document).on('click', 'table .check-item', function () {
    var domTable = $(this).closest('table');
    var totalChecked = getTotalCheckedWithEnable();
    chkAllItems.prop('checked', totalChecked === domTable.find('.check-item:enabled').length);
    btnSendMail.attr('disabled', !totalChecked);
});
btnSendMail.on('click', function () {
    modalSendMail.modal('show');
});

var rules = {
    app_pass: "required",
    subject: "required",
    content: {
        required: function(textarea) {
            CKEDITOR.instances[textarea.id].updateElement();
            return textarea.value.replace(/<[^>]*>/gi, '').length === 0;
        },
    },
};
$("#form-send-mail").submit(function(e) {
    e.preventDefault();
}).validate({
    ignore: [],
    rules: rules,
    messages: validateMessages,
    submitHandler: function() {
        return false;
    },
});
modalSendMail.on('click', '.btn-preview', function () {
    var that = $(this);
    that.attr('disabled', true).find('.fa-refresh').removeClass('hidden');
    $.ajax({
        url: urlPreviewMail,
        method: 'post',
        data: {
            _token: _token,
            subject: $('#modal-send-mail input[name="subject"]').val(),
            content: CKEDITOR.instances['content'].getData(),
            type: isTabBirthday ? typeMailBirthday : typeMailFollow,
        },
    }).done(function (data) {
        var iframe = $('<iframe style="height: 75vh; width: 100%;">');
        modalPreviewEmail.find('.preview-send-email').html(iframe);
        modalPreviewEmail.find('.preview-send-email-subject').html(data.subject);
        setTimeout(function() {
            $('body', iframe[0].contentWindow.document).replaceWith(data.content);
            modalPreviewEmail.modal('show');
        }, 1);
        that.attr('disabled', false).find('.fa-refresh').addClass('hidden');
    });
});
modalSendMail.on('click', '.btn-send', function () {
    if (!$("#form-send-mail").valid()) {
        return;
    }
    var that = $(this);
    that.attr('disabled', true).find('.fa-refresh').removeClass('hidden');
    var candidateIds = [];
    var items = [];
    $('.check-item:enabled').map(function (index, el) {
        if ($(el).is(':checked')) {
            candidateIds.push($(el).val());
            items.push($(el));
        }
    });
    $.ajax({
        url: urlSendMail,
        method: 'post',
        data: {
            _token: _token,
            app_pass: modalSendMail.find('input[name="app_pass"]').val(),
            subject: modalSendMail.find('input[name="subject"]').val(),
            candidateIds: candidateIds,
            type: isTabBirthday ? typeMailBirthday : typeMailFollow,
            content: CKEDITOR.instances['content'].getData(),
        },
        success: function (data) {
            bootbox.alert({
                className: data.success === 1 ? 'modal-success' : 'modal-danger',
                message: data.message,
            });
            if (data.success === 0) {
                return;
            }

            items.map(function (item, index) {
                item.attr('disabled', isTabBirthday).prop('checked', false);
                if (isTabBirthday) {
                    item.closest('tr').children('td.mail-status').text(txtSentMailCMSN);
                } else {
                    var domMailType = item.closest('tr').children('td.mail-type');
                    var prevTxt = domMailType.text();
                    if (!prevTxt.match(new RegExp(txtMailTypeInterested, 'i'))) {
                        var seperate = prevTxt.match(new RegExp(txtMailTypeMarketing, 'i')) ? ', ' : '';
                        domMailType.text(prevTxt + seperate + txtMailTypeInterested);
                    }
                    item.closest('tr').children('td.sent-date').text(data.sent_date);
                }
            });
            btnSendMail.attr('disabled', true);
            chkAllItems.prop('checked', false);
            modalSendMail.modal('hide');
        },
        error: function (data) {
            if (data.responseJSON && data.responseJSON.message) {
                bootbox.alert({
                    className: 'modal-danger',
                    message: data.responseJSON.message,
                });
            }
        },
        complete: function () {
            that.attr('disabled', false).find('.fa-refresh').addClass('hidden');
        },
    });
});
