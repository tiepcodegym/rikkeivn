$(function() {
    $("#title-error").hide();
});

function checkComment() {
    var contentComment;
    contentComment = CKEDITOR.instances.comment_textarea.getData().replace(/<[^>]*>/gi, '');
    if (contentComment.trim().length == 0) {
        $('#check_comment').val('true');
        $("#title-error").show();
        return false;
    }
}

function getExtension(filename) {
    var parts = filename.split('.');
    return parts[parts.length - 1];
}

function isImage(filename) {
    var ext = getExtension(filename);
    switch (ext.toLowerCase()) {
    case 'jpg':
    case 'gif':
    case 'bmp':
    case 'png':
        //etc
        return true;
    }
    return false;
}

function checkTime(){
    var from = $('#deadline-check').val().split("-");
    var timehuydk = from[2].split(" ");
    var timeTmp = timehuydk[0] + '-' + from[1] + '-' + from[0] + ' ' + timehuydk[1];
    if( (Date.parse(timeTmp) / 1000 + 60) < (Math.floor(Date.now() / 1000) + 2*3600)){
        return false;
    }else{
        return true;
    }
}

// setInterval(function(){
//     if(!checkTime()){
//         $('.checkTime').show();
//     }else{
//         $('.checkTime').hide();
//     }
// },1000)

var removetmp = [];
$('#field').change(function(event) {
   $('input[type=file]').val();
   $('#remove').val('');
   $('#submit').prop("disabled", false);
   $("#errorSize").html("");
   $("#nameImage").html("");
   if(check()){
        var nameFile = $('input[type=file]').val().split('\\').pop();
        var files = $('#field').prop("files")
        var names = $.map(files, function(val) { return val.name; });
        names.forEach(function(item,index){
            var html = '<span class="tag"><span class="close_file" data-id='+index+' >&times;</span>'+item+'</span>';
            $("#nameImage").append(html);
        });
   }
});

$('#datetimepicker2').click(function(event) {
   $('#submit').prop("disabled", false);
});

$(function() {
     $(".fancybox").fancybox({
        openEffect  : 'none',
        closeEffect : 'none'
    });
    //tool tag upload file
    $("#nameImage").on("click", ".close_file", function() {
        removetmp.push(parseInt($(this).attr('data-id')));
        $('#remove').val(removetmp);
        $(this).parent("span").fadeOut(100);
    });
    //end tool
    $('#content-check').wysihtml5({
        toolbar: {
            "font-styles": true,
            "emphasis": true,
            "lists": true,
            "html": false,
            "link": false,
            "image": false,
            "color": false,
            "blockquote": true,
        }
    });

    $('#form-post-edit-check .wysihtml5-sandbox').contents().find('body').on("keydown",function() {
        $('#content-check-error').hide();
        setTimeout(function() {
          if( $('#content-check').val() == ''){
            $('#content-check-error').show();
          }
        }, 500);
        
    });
    
    $("#box_add").hide();
    $("#box_chart").hide();
    var date = new Date();
    var dateAdd = date.setMinutes(date.getMinutes()+121);
    $('#datetimepicker2').datetimepicker({
        allowInputToggle: true,
        defaultDate:dateAdd,
        sideBySide: true
    });

    var messageValidate = {
        required: MESSAGE_REQUIRE,
        rangelength: MESSAGE_RANGE_LENGTH
    };
    
    $('#form-post-edit-check').validate({
        ignore: ":hidden:not(textarea)",
        rules: {
            'subject': {
                required: true,
                rangelength: [1, 255]
            },
            'deadline': {
                required:true
            },
            WysiHtmlField: {
                required:true
            },
        },
        messages: {
            'subject': {
                required: messageValidate.required,
                rangelength: messageValidate.rangelength
            },
            'deadline':{
                required: messageValidate.required
            },
            'content' :{
                required: messageValidate.required
            }
        }
    });
});
        
function closeRequest()
{
    $('#box_reason_unsatisfied').hide();
    $('#satisfied').iCheck('check');
    $('#reason_unsatisfied-error').hide();
    $('#reason_unsatisfied').val('');
}

function closeChangePriority()
{
    $('#reason_change_priority-error').hide();
    $('#reason_change_priority').val('');
}

function closeChangeDeadline()
{
    $('#reason_change_deadline-error').hide();
    $('#reason_change_deadline').val('');
    $('.error-change-deadline').hide();
}

function getValueStatus(value)
{
    $('#change_status').val(value);
}

function checkHasLeader()
{
    hasLeader = true;
    leader_id = $('#leader_id_change').val();
    // if(leader_id.trim().length == 0)
    if(leader_id == 0)
    {
        $('#leader-change-error').show();
        hasLeader = false;
    }

    return hasLeader;
}

function closeChangeTeam()
{
    $('#leader-change-error').hide();
}

function checkTimeChangeDealine()
{
    var from = $('#change_deadline').val().split("-");
    
    var timehuydk = from[2].split(" ");
    
    var timeTmp = timehuydk[0] + '-' + from[1] + '-' + from[0] + ' ' + timehuydk[1];

    if((Date.parse(timeTmp) / 1000 + 60) < (Math.floor(Date.now() / 1000) + 2*3600))
    {
        return false;
    } else {
        return true;
    }
}

function checkSubmitChangeDeadline()
{
    var status = true;
    $('#check_submit_change_deadline').val('true');
    var checkTime = checkTimeChangeDealine();

    if(checkTime)
    {
        $('.error-change-deadline').hide();
        status = true;
    } else {
        $('.error-change-deadline').show();
        status = false;
        return status;
    }

    return status;
}

$('#datetimepicker-change-deadline').on("dp.change", function(e) {
    var check_submit_change_deadline = $('#check_submit_change_deadline').val();
    if(check_submit_change_deadline == 'true')
    {
        var checkTime = checkTimeChangeDealine();

        if(checkTime)
        {
            $('.error-change-deadline').hide();
        } else {
            $('.error-change-deadline').show();
        }
    }
});

