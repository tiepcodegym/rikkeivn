
function showChannel(id, url, token) {
    
    jQuery.ajax({
        type: "POST",
        url: url,
        data: {
            _token: token, 
            id: id,
        },
        dataType: 'JSON',
        success: function(data){
           $('#modal-channel .modal-body').html(data['html']);
           $('#modal-channel').modal('show'); 
        }
    });
}

