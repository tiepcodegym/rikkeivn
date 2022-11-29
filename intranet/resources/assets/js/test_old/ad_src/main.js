(function ($) {
    var GMAT_TYPE = 2;
    var TEST_TYPE = 1;
    
    if ($('.check_all').length > 0) {
        $('.check_all').on('change', function () {
            if ($(this).is(':checked')) {
                $('.check_item').prop('checked', true);
            } else {
                $('.check_item').prop('checked', false);
            }
        });
        var item_length = $('.check_item').length;
        $('.check_item').on('change', function(){
            if($('.check_item:checked').length === item_length){
                $('.check_all').prop('checked', true);
            }else{
                $('.check_all').prop('checked', false);
            }
        });
    }
    
    $('.m_action_btn').on('click', function(e){
       e.preventDefault();
       var title = $(this).attr('data-original-title');
       var href = $(this).attr('href');
       var action = $(this).attr('action');
       var ids = [];
        $('.check_item').each(function () {
            if ($(this).is(':checked')) {
                ids.push($(this).val());
            }
        });
        
       $('.btn-outline').click(function (e) {
          if ($(this).hasClass('btn-ok')) {
             $.ajax({
               url: href,
               type: 'POST',
               data: {
                   action: action,
                   item_ids: ids,
                   _token: _token
               },
               success: function(data){
                   window.location.reload();
               },
               error: function(err){
                   window.location.reload();
               }
           });
          } 
       });
    });
    
    selectSearchReload();
    
    $('#box_type').change(function () {
        if ($(this).val() == GMAT_TYPE) {
            $('#cat_box').val('').prop('disabled', true);
            $('.select2-selection__rendered').text($('#cat_box option:first').text());
        } else {
            $('#cat_box').val($('#cat_box option:first').val()).prop('disabled', false);
            $('.select2-selection__rendered').text($('#cat_box option:first').text());
        }
    });
    
})(jQuery);


