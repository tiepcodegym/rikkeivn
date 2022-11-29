//Fix footer bottom
setHeightByWidth();
$(window).resize(function(){
    setHeightByWidth();
});

function setHeightByWidth(){
    var widthScreen = $(window).width();
    if(widthScreen < 480){
       setHeightBody('.welcome-body', 95);
    } else if(widthScreen <= 768) {
        setHeightBody('.welcome-body', 120);
    }else if(widthScreen <= 1024) {
        setHeightBody('.welcome-body', 100);
    } else {
        setHeightBody('.welcome-body', 90);
    }
}

$(document).on('click','#history',function(){
    data = $('#history').data('id');
    $.ajax({
        headers: {
            'X-CSRF-Token': $('input[name="_token"]').val()
        },
        type:"post",
        url: url,
        data:{'code': data,
            'idCss':idCss
        },
        success:function(data){
            if (data['status'] == true) {
                $('#appden_html').html(data['html']);
                 
            } else {
                $('#appden_html').html('<p>You don\'t work Css</p>');
            }
            $('#modal-history').modal('show'); 
        },
        cache:false,
        dataType: 'json'
    }); 
});