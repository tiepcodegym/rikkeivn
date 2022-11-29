$.fn.preSaveProcessing = function (){    

    $(this).click(function(){
        if ($('#recruiterList').val() != ""){
            $('#update-confirm').modal('show');
        }
        else {
            $('.errTxt').html(" "+errMsg).delay(2000).fadeOut();
        }
    });
    
    $('button#accept').click(function(){
        $('#update-recruiter').submit();
    });
}
