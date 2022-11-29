$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
});


/**
 * Form submit
 */
function formSubmit() {
    $('#form-create-setting-education').submit()
}
/**
 * update values to database
 */
function update(){
    $.ajax({
        url: globurlSubmit,
        type: 'post',
        dataType: 'json',
        data: {
            id: $('#id_education').val(),
            code: $('#code').val(),
            name: $('#name').val()
        },
        success: function (data) {
            if (data.status) {
                $('.show-warning').addClass('warn-confirm').click();
            } else {
                formSubmit();
            }
        },
        error: function (x, t, m) {
            if(t === 'timeout') {
                $('#modal-warning-notification .modal-body p.text-default').text(errorTimeoutText);
            } else {
                $('#modal-warning-notification .modal-body p.text-default').text(errorText);
            }

            $('#modal-warning-notification').modal('show');
        },
        complete: function () {
            // btn.find('i').addClass('hidden');
            // btn.prop('disabled', false);
        }
    });
}

$('.setting-submit').on('click', function () {
    var mode_tyoe = $(this).attr('data-mode')
    $(this).attr("disabled", true)
    if(mode_tyoe === 'mode_update') {
        update()
    }else{
        formSubmit()
    }
})

$('.btn-ok').on('click', function(e) {
    e.preventDefault();
    formSubmit()
    $('.setting-submit').attr("disabled", false)
});

$('.btn-close').on('click', function(e) {
    e.preventDefault();
    $('.setting-submit').attr("disabled", false)
});

$("body").click(function() {
    if ($(".modal").is(":visible")) {
        $('.setting-submit').attr("disabled", false)
    }
});
