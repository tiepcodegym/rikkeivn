    $('.date').datepicker({
        format: 'yyyy-mm-dd',
        todayHighlight: true,
        autoclose: true,
    });
    RKfuncion.select2.init();
    a = $('#duplicate').children('.box-number').length;
    $(document).on('click','#add', function(e) {
        e.preventDefault();
        var html = $('#duplicate1').html();
        a++;
        console.log(a);
        $(this).parent().prev().append(html);
        $(this).parent().prev().find('.input-name').last().attr('name','asset['+a+'][name]');
        $(this).parent().prev().find('.input-number').last().attr('name','asset['+a+'][number]');
        
    });
    $(document).on('click','.btn-delete', function(e) {
        e.preventDefault();
        $(this).parent().parent().parent().remove();
    });

    var isRequestSubmited = false;
    $('#request-asset').submit(function() {
        isRequestSubmited = true;
    })
    $('#employ-request').on("select2:select", function(e) {
        e.preventDefault();
        if (isRequestSubmited) {
            $("#request-asset").valid();
        }
    });
    $('#room-request').on("select2:select",function(e) {
        e.preventDefault();
        if (isRequestSubmited) { 
            $("#request-asset").valid();
        }
    });