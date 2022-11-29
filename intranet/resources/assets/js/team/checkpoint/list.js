function copyToClipboard(element) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(element).attr('data-href')).select();
    document.execCommand("copy");
    $temp.remove();

    $("#modal-clipboard").modal('show');
}

jQuery(document).ready(function($) {
    $('.css-list-copy-url').click(function() {
        this.selectionStart = 0;
        this.selectionEnd = $(this).val().length;
    });
    $(document).on('keyup keydown keypress', 'input.css-list-copy-url', function(e) {
        e.preventDefault();
        return false;
    });
});

$('.btn-reset-checkpoint').click(function(){
    if (!confirm('Are you sure?')) return false;
    
    $.ajax({
        url: baseUrl + '/team/checkpoint/reset',
        type: 'get',
    })
    .done(function () { 
        // Reload page
        location.href = currentUrl;
    })
    .fail(function () {
        alert("Ajax failed to fetch data");
    })
});

$('#button-save-period').click(function() {
    var form = $('#form-save-period');
    var period = $('#period-checkpoint').val();
    var yearCheckpoint = $('#add-period-checkpoint input[name="year-checkpoint"]').val();

    if (period === '0') {
        $('#form-save-period .message-period-error').removeClass('hidden');
        return false;
    } else {
        $('#form-save-period .message-period-error').addClass('hidden');
    }
    if (!yearCheckpoint) {
        $('#form-save-period .message-year-require').removeClass('hidden');
        return false;
    } else {
        $('#form-save-period .message-year-require').addClass('hidden');
    }

    form.submit();
});

$(document).ready(function() {
    $('.del-checkpoint-time').click(function() {
        var idCheckpointTime = $(this).closest('.action-checkpoint-time').data('id');
        $('.button-modal-confirm-checkpoint').attr('data-id', idCheckpointTime);
        $('#modal-confirm-del').modal('show');
    });

    $('.button-modal-confirm-checkpoint').click(function() {
        var url = $(this).data('url');
        var token = $('#csrf-token').val();
        var idCheckpointTime = $(this).data('id');

        $.ajax({
            url: url,
            type: 'post',
            data: {
                _token: token,
                id: idCheckpointTime,
            },
            success: function(response) {
                $('#modal-confirm-del').modal('hide');
                if (response.success === 1) {
                    RKExternal.notify(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 300);
                } else {
                    RKExternal.notify(response.message_error, false);
                }
            }
        });
    });

    $('.edit-checkpoint-time').click(function() {
        var idCheckpointTime = $(this).closest('.action-checkpoint-time').data('id');
        var checkTime = $(this).closest('.action-checkpoint-time').data('time');
        var arrayCheckTime = checkTime.split("/");

        $('#idCheckpoint').val(idCheckpointTime);
        $('.year-checkpoint').val(arrayCheckTime[1]);
        if (arrayCheckTime[0] == 3) {
            $('#select-period-checkpoint option[value="3"]').attr('selected', 'selected');
        } else if (arrayCheckTime[0] == 9) {
            $('#select-period-checkpoint option[value="9"]').attr('selected', 'selected');
        } else {
            $('#select-period-checkpoint option[value="0"]').attr('selected', 'selected');
        }
        $('#edit-period-checkpoint').modal('show');
    });

    $('#button-edit-period').click(function() {
        var form = $('#form-edit-period');
        var period = $('#select-period-checkpoint').val();
        var yearCheckpoint = $('#edit-period-checkpoint input[name="year-checkpoint"]').val();

        if (period === '0') {
            $('#form-edit-period .message-period-error').removeClass('hidden');
            return false;
        } else {
            $('#form-edit-period .message-period-error').addClass('hidden');
        }
        if (!yearCheckpoint) {
            $('#form-edit-period .message-year-require').removeClass('hidden');
            return false;
        } else {
            $('#form-edit-period .message-year-require').addClass('hidden');
        }

        form.submit();
    });

});
