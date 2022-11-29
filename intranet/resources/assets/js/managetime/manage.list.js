$(function() {
    $('.input-select-team-member').on('change', function(event) {
        value = $(this).val();
        window.location.href = value;
    });
        
    var fixTop = $('#position_start_header_fixed').offset().top;
    $(window).scroll(function() {
        var scrollTop = $(window).scrollTop();
        if (scrollTop > fixTop) {
            $('#managetime_table_fixed').css('top', scrollTop - $('.table-responsive').offset().top + 52);
            $('#managetime_table_fixed').show();
        } else {
            $('#managetime_table_fixed').hide();
        }
    });

    $(".managetime-show-popup a").click(function() {
        $.ajax({
            type: "GET",
            data : { 
                registerId: $(this).attr('value'),
            },
            url: urlShowPopup,
            success: function (result) {
                $('#modal_view').html(result);
                $("#approver").select2();
                $("#related_persons").select2();
                $('#modal_view').modal('show');
            },
        });   
    });

    $('#button_delete_submit').click(function() {
        var registerId = $('#register_id_delete').val();
        var urlCurrent = window.location.href.substr(window.location.href);
        var $this = $(this);
        $this.button('loading');

        $.ajax({
            type: "GET",
            url: urlDelete,
            data : { 
                registerId: registerId,
                urlCurrent: urlCurrent,
            },
            success: function (result) {
                $('#modal_delete').modal('hide');
                var data = JSON.parse(result);
                window.location = data.url;
            },
        });
    });  

    $('.button-delete').click(function() {
        var checkStatus = $(this).attr('data-status');
        if(checkStatus === statusApproved) {   
            $('#show_notification').text(contentDisallowDelete);
            $('#modal_allow_edit').modal('show');
            return false;
        }
        $('#register_id_delete').val($(this).val());
        $('#modal_delete').modal('show');
    });
});