if ($('.check_all').length > 0) {
    if ($('.check_item').length > 0 && $('.check_item:checked').length == $('.check_item').length) {
        $('.check_all').prop('checked', true);
    } else {
        $('.check_all').prop('checked', false);
    }
    $('.check_all').on('change', function () {
        if ($(this).is(':checked')) {
            $('.check_item').prop('checked', true);
            $('#select_q_error').addClass('hidden');
        } else {
            $('.check_item').prop('checked', false);
        }
    });
    $('body').on('change', '.check_item', function(){
        var item_length = $('.check_item').length;
        if($('.check_item:checked').length === item_length){
            $('.check_all').prop('checked', true);
        }else{
            $('.check_all').prop('checked', false);
        }
        $('#select_q_error').addClass('hidden');
    });
}

$('.m_action_btn').on('click',function(e){
    e.preventDefault();
    $('.btn-outline').one('click',function(e) {
        if ($(this).hasClass('btn-ok')) {
            var searchIDs = $('input:checked').not('.check_all').map(function(){
            return $(this).val();
            });
            data = searchIDs.get();
            $.ajax({
                headers: {
                      'X-CSRF-TOKEN': _token,
                },
                type:"post",
                url:url,
                data:{'data':data},
                success:function(data){
                    window.location.reload();
                    
                },
                cache:false,
                dataType: 'json'
            });
        } else {
            $('input:checkbox').removeAttr('checked');
        }
    });
})