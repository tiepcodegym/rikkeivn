var pgurl = window.location.href.substr(window.location.href);
var url = $(".menu-ticket #set_menu").val();
        
if(pgurl == url)
{
    $(".menu-ticket #menu_all").addClass("active");
}

$(".menu-ticket li a").each(function(){
    if($(this).attr("href") == pgurl || $(this).attr("href") == '' )
        $(this).addClass("active");
});

$('#leader_id').val($('#team_id').select2().find(":selected").attr("data-leader"));

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
    var from = $('#deadline').val().split("-");
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
// },1000);

function check(event){
   
    var fp = $("#field");
    var lg = fp[0].files.length;
    var items = fp[0].files;
    var status = true;

    var leader_id = $('#leader_id').val();
    if(leader_id.trim().length == 0)
    {
        $('#leader-error').show();
        status = false;
        return status;
    }

    //checkTime
    if(!checkTime()){
        $('.checkTime').show();
        status = false;
        return status;
    }else{
        $('.checkTime').hide();
        status = true;
    }

    if (lg > 0) {
      for (var i = 0; i < lg; i++) {
        if (items[i].size > 1000000 ) {
          var status = false;
          $("#errorSize").html("File "+items[i].name+" dung lượng lớn hơn 1MB");
          break;
        }

        if (!isImage(items[i].name)) {
          var status = false;
          $("#errorSize").html("File "+items[i].name+" không đúng định dạng ảnh");
          break;
        }
      }
      return status;
   }else{
      return true;
   }
}

var removetmp = [];
$('#field').change(function(event) {
    $('input[type=file]').val();
    $('#remove').val('');
    $('#submit').prop("disabled", false);
    $("#errorSize").html("");
    $("#nameImage").html("");
    if(check()){
        var nameFile = $('input[type=file]').val().split('\\').pop();
        var files = $('#field').prop("files");
        var names = $.map(files, function(val) { return val.name; });
        names.forEach(function(item,index){
            var html = '<span class="tag"><span class="close_file" data-id='+index+' >&times;</span>'+item+'</span>';
            $("#nameImage").append(html);
        });
    }
});

$('#datetimepicker1').click(function(event) {
   $('#submit').prop("disabled", false);
});

$(function() {
    //tool tag upload file
    $("#nameImage").on("click", ".close_file", function() {
        removetmp.push(parseInt($(this).attr('data-id')));
        $('#remove').val(removetmp);
        $('input[type=file]').val('');
        $(this).parent("span").fadeOut(100);
    });
    //end tool
    $('#content').wysihtml5({
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


    $('.wysihtml5-sandbox').contents().find('body').on("keydown",function() {
        $('#content-error').hide();
        setTimeout(function() {
          if( $('#content').val() == ''){
            $('#content-error').show();
          }
        }, 500);
        
    });

    $("#box_add").hide();
    $("#box_chart").hide();
    var date = new Date();
    var dateAdd = date.setMinutes(date.getMinutes()+121);
    $('#datetimepicker1').datetimepicker({
        allowInputToggle: true,
        defaultDate:dateAdd,
        sideBySide: true
    });

    var messageValidate = {
        required: MESSAGE_REQUIRE,
        rangelength: MESSAGE_RANGE_LENGTH
    };
    
    $('#form-post-edit').validate({
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

$("#btn_add").click(function(){
    $("#box_add").show(800);
    $('#ticket_list').hide(800);
});

$("#close").click(function(){
    $("#box_add").hide(800);
    $("#btn_add").show();
    $('#ticket_list').show(800);
});



