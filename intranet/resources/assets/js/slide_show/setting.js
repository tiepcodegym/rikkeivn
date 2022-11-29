$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var token = $('meta[name="csrf-token"]').attr('content');

jQuery(document).ready(function ($) {
    $('input.date-picker').datetimepicker({
        format: 'YYYY-MM-DD hh:mm:ss A'
    });
    $('.calendar-button').click(function(event) {
        $( "input.date-picker" ).datetimepicker( "show" );
    });
});

$(document).on('click', '#edit-password', function (event) {
    $('.error-validate-password').remove();
    $input = $('#password');
    $password = $input.val();
    data = {
        _token:token,
        password: $password,
    }
    $this = $(this);
    url = urlChangePassword;
    if ($this.data('requestRunning')) {
        return;
    }
    $this.data('requestRunning', true);

    saveData($this, url, data, $input);
});

$(document).on('click', '#edit-birhtday', function (event) {
    $('.error-validate-birthday_company').remove();
    $input = $('#birthday_company');
    $birthday_company = $input.val();
    data = {
        _token:token,
        birthday_company: $birthday_company,
    }
    $this = $(this);
    url = urlChangeBirthday;
    if ($this.data('requestRunning')) {
        return;
    }
    $this.data('requestRunning', true);

    saveData($this, url, data, $input);
});

function saveData(btn, url, data, input) {
    $.ajax({
        url: url,
        type: 'post',
        data: data,
        dataType: 'json',
        success: function(data) {
            if(data.status) {
                $modal = $('.modal-slide');
                $modal.find('.modal').removeClass().addClass('modal modal-success');
                $modal.find('.modal-title-error').css('display', 'none');
                $modal.find('.text-message').html(btn.attr('data-message-success'));
                $modal.find('.modal').modal();
            } else {
                if(data.message_error.password) {
                    input.after('<p class="word-break error-validate-password error" for="password" style="float:left">' + data.message_error.password[0] + '</p>')
                }
                if(data.message_error.birthday_company) {
                    input.after('<p class="word-break error-validate-birthday_company error" for="birthday_company" style="float:left">' + data.message_error.birthday_company[0] + '</p>')
                }
            }
        },
        error:function() {
            $modal = $('.modal-slide');
            $modal.find('.modal').removeClass().addClass('modal modal-danger');
            $modal.find('.modal-title-success').css('display', 'none');
            $modal.find('.text-message').html(btn.attr('data-message-error'));
            $modal.find('.modal').modal();
        },
        complete:function () {
            btn.data('requestRunning', false);
        }
    });
}