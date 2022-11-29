$(document).on('click','.btn-edit',function() {
    $("#myModal").modal("show");
});
$(document).on('click','#btn-submit',function(e) {
    e.preventDefault();
    radioValue = $("input[name='optradio']:checked"). val();
    text = $('#text').val();
    idRq = $("#btn-submit").data('type');
    $.ajax({
        headers: {'X-CSRF-Token': $('input[name="_token"]').val()},
        type:"post",
        url: url,
        data: {
            'status': radioValue,
            'text': text,
            'idRq': idRq,
        },
        success:function(data){
            if (data.status == true) {
                $("#myModal").modal("hide");
                location.reload();
            } else $("#myModal").modal("hide");   
        },
        dataType:"json"
    });
});