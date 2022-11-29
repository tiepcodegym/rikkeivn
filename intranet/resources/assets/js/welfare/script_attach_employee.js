$(document).on('change','#fee_favorable_employee_attach',function(){
    var favorable = $(this).val();
    var url = $(this).data('url');
    var wel_id = $('input[name="welfare_id"]').val();
    var employee_id = $('input[name="employee_id"]').val();
    var id_attach = $('#id_attach_employee_hidden').val();
   
    $.ajax({
        headers: {
              'X-CSRF-Token': $('input[name="_token"]').val()
        },
        type: 'post',
        url: url,
        data: { 'favorable':favorable,
                'wel_id':wel_id,
                'employee_id':employee_id,
                'id_attach':id_attach,
        },
        success: function (data) {
            $('#show-relative-favorable').removeAttr('disabled');
            $('#show-relative-favorable').html(data.data);
            $('#favorable-require-aa').css('display','none');
        },
        dataType:"json"
    });
});