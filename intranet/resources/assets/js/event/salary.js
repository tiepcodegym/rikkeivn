(function ($) {

    $('#file_upload').on('change', function () {
        var files = $(this)[0].files;
        if (files.length > 0) {
            var fileSize = files[0].size / 1024;
            if (fileSize > MAX_FILE_SIZE) {
                bootbox.alert({
                    className: 'modal-danger',
                    message: textErrorMaxFileSize
                });
                $(this).val('');
            }
        }
    });

    var btnSendMail = $('#btn_send_mail');
    var btnStopSend = $('#btn_stop_send');
    btnSendMail.click(function (e) {
        e.preventDefault();
        var activeRow = $('#salary_table tbody tr.active');
        if (activeRow.length < 1) {
            activeRow = $('#salary_table tbody tr:first');
            if (activeRow.hasClass('success')) {
                activeRow = $('#salary_table tbody tr.sent-error:first');
            }
        }
        stopSending = 0;
        sendEmail(activeRow);
        $(this).addClass('hidden');
        btnStopSend.removeClass('hidden');
    });

    var xhr = null;
    var timeoutSending;
    function sendEmail(row){
        if (stopSending || row.hasClass('success') || row.hasClass('sending')) {
            return;
        }
        //scroll
        var rowOffsetTop = row.offset().top - $('#salary_table thead').height();
        $("html, body").stop().animate({scrollTop: rowOffsetTop,}, 500, 'swing');

        var nextRow = row.next('tr');
        var rowStatus = row.find('.sending-status');
        row.addClass('sending').removeClass('sent-error success');
        rowStatus.text('').addClass('fa fa-spin fa-refresh');
        xhr = $.ajax({
            url: sendMailUrl,
            type: 'POST',
            data: {
                _token: siteConfigGlobal.token,
                email: row.data('email'),
                salary_file_id: row.closest('table').data('file-id'),
            },
            success: function () {
                rowStatus.removeClass('text-red fa-close').addClass('fa-check text-green');
                row.addClass('success');
                row.removeClass('active');
                nextRow.addClass('active');
            },
            error: function (error) {
                rowStatus.removeClass('text-green fa-check').addClass('fa-close text-red');
                row.addClass('sent-error');
                if (error.status !== 0) {
                    row.removeClass('active');
                    nextRow.addClass('active');
                }
            },
            complete: function () {
                row.removeClass('sending');
                rowStatus.removeClass('fa-spin fa-refresh');
                xhr = null;
                if (nextRow.length > 0) {
                    timeoutSending = setTimeout(function () {
                        sendEmail(nextRow);
                    }, delaySendMail * 1000);
                } else {
                    btnSendMail.removeClass('hidden');
                    btnStopSend.addClass('hidden');
                    if ($('#salary_table tbody tr.sent-error').length > 0) {
                        if (btnSendMail.find('.send-error').length < 1) {
                            btnSendMail.append('<span class="send-error"> (Gửi lại mục lỗi)</span>');
                        }
                    } else {
                        btnSendMail.addClass('hidden');
                    }
                    bootbox.alert({
                        className: 'modal-success',
                        message: 'Đã gửi xong email, <a href="'+ detailMailSentLink +'" style="color: #ffebc7;"> xem kết quả gửi mail</a>',
                    });
                }
            },
        });
    }

    $('body').on('click', '.del-temp-link', function (e) {
        e.preventDefault();
        $('#delete_salary_form').submit();
    });

    btnStopSend.click(function (e) {
        e.preventDefault();
        stopSendEmail();
        $(this).addClass('hidden');
        btnSendMail.removeClass('hidden');
    });

    function stopSendEmail() {
        if (xhr) {
            xhr.abort();
        }
        stopSending = 1;
        clearTimeout(timeoutSending);
    }

    window.onbeforeunload = function () {
        if ($('.send-salary-page').length > 0) {
            var trLength = $('#salary_table tbody tr').length;
            var trSuccess = $('#salary_table tbody tr.success').length;
            if (trLength !== trSuccess) {
                return true;
            }
        }
    };

    var groupBtn = $('.group-button');
    var tblOffsetTop = $('.content-container').offset().top;
    $(window).scroll(function () {
        var scrollTop = $(this).scrollTop();
        if (scrollTop > tblOffsetTop) {
            groupBtn.addClass('btn-fixed-right');
        } else {
            groupBtn.removeClass('btn-fixed-right');
        }
    });

    $('#send_pass_mail_btn').click(function (e) {
        e.preventDefault();
        var btn = $(this);
        var loading = btn.find('.loading');
        var url = btn.data('url');
        if (!loading.hasClass('hidden') || !url) {
            return;
        }

        bootbox.confirm({
            className: 'modal-warning',
            message: textConfirmSendMail,
            callback: function (result) {
                if (result) {
                    btn.prop('disabled', true);
                    loading.removeClass('hidden');
                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: {
                            _token: siteConfigGlobal.token,
                        },
                        success: function (data) {
                            bootbox.alert({
                                message: data,
                                className: 'modal-success',
                            });
                        },
                        error: function (error) {
                            bootbox.alert({
                                message: error.responseJSON,
                                className: 'modal-danger',
                            });
                        },
                        complete: function() {
                            btn.prop('disabled', false);
                            loading.addClass('hidden');
                        },
                    });
                }
            },
        });

    });

})(jQuery);
