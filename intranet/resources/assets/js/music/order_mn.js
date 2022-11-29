function showMess(orderId){
    $('#showMess .modal-dialog .modal-body p').remove();
    var mess = $('#mess'+orderId).val();
    $('#showMess .modal-dialog .modal-body').append('<p>'+mess+'</p>')
    $('#showMess').modal('show');
}

$('.check_all').change(function(){
	if($(this).is(':checked')){
		$('[name="check_items[]"]').prop('checked',true);
	}else {
		$('[name="check_items[]"]').prop('checked',false);
	}
});

var check_item= document.getElementsByName("check_items[]");
$('[name="check_items[]"]').change(function(){
	if(getIdOrder().length<check_item.length){
		$('.check_all').prop('checked',false);
	}else{
		$('.check_all').prop('checked',true);
	}
});

function getIdOrder(){
	var allIds = [];
	$('[name="check_items[]"]').each(function(){
		if($(this).is(':checked')){
			allIds.push($(this).val());
		}
	});
	return allIds;
}

$('.m_action_btn').on('click',function(e){
    e.preventDefault();
    var url = $(this).attr('href');
    var token = $(this).attr('token');
    $('.btn-outline').one('click',function(e) {
        if ($(this).hasClass('btn-ok')) {
            var data = getIdOrder();
            if(data.length <= 0){
            	var errorMess = '<div class="alert alert-warning"><ul><li>Chưa có đối tượng nào được chọn</li></ul></div>';
            	$('#error .alert-warning').remove();
            	$('.not-found').remove();
            	$('.alert-success').remove();
            	$('#error').append(errorMess);
            }else{
            	$.ajax({
	                headers: {
	                    'X-CSRF-TOKEN': token,
	                },
	                type:"post",
	                url:url,
	                data:{'orderIds':data},
	                success:function(data){
	                    window.location.reload();
	                },
	                cache:false,
	                dataType: 'json'
            	});
            }
        } else {
            $('input:checkbox').removeAttr('checked');
        }
    });
})