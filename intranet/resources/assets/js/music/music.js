$('body').on('click', '.delTime', function () {
    $(this).closest('.mini-time').remove();
});

$('body').on('mouseenter', '.mini-time', function(){
    $(this).find('.delTime i').removeClass('hidden');
 });
$('body').on('mouseleave', '.mini-time', function () {
    $(this).find('.delTime i').addClass('hidden');
});

$('.mini-time').css('margin-bottom','8px');

function addTime() {
    var time = '<div class="col-sm-4 col-md-3 col-lg-4 mini-time" style="margin-bottom: 8px">'
                +'<div class="input-group">'
                +'<input class="form-control time" type="text" name="time[]" readonly/>'
                +'<span class="input-group-addon">'
                +'<span>'
                +'<i class="fa fa-clock-o" aria-hidden="true"></i>'
                +'</span></span></div>'
                +'<span class="delTime"><i class="fa fa-times-circle time-remove hidden" aria-hidden="true"></i>'
                +'</span>'
                +'</div>';
    $('#time').append(time);

    $('.input-group').each(function(){
        $(this).datetimepicker({
            ignoreReadonly: true,
            format: 'HH:mm',
            disabledTimeIntervals: [ [ moment().hour(0), moment().hour(7).minutes(59)], [ moment().hour(17).minutes(30), moment().hour(24) ] ]
        });
    });
}

$('.input-group').each(function(){
    $(this).datetimepicker({
        ignoreReadonly: true,
        format: 'HH:mm',
        disabledTimeIntervals: [ [ moment().hour(0), moment().hour(7).minutes(59)], [ moment().hour(17).minutes(30), moment().hour(24) ] ]
    });
});

/**
* vadilator unique name
*/
$.validator.addMethod('uniqueName', function (value, element, param) {
    if(uniqueName()) {
        return true;
    }
}, 'Tên này đã được sử dụng, hãy nhập vào tên khác');

$.validator.addMethod('timeLimit', function (value, element, param) {
    if(timeLimit()) {
        return true;
    }
}, 'Thời gian nhập vào phải nằm trong khoảng từ 08 giờ đến 17 giờ 30');


$.validator.addMethod('uniqueTime', function (value, element, param) {
    if(uniqueTime(element)) {
        return true;
    }
}, 'Các khung thời gian phải khác nhau');

$.validator.addMethod("time24", function(value, element) {
    console.log(timeFormat());
    return timeFormat();
}, "Thời gian nhập vào không đúng định dạng");


$('#form-edit-office').validate({
    onkeyup: false,
    rules: {
        'music_offices[name]': {
            required: true,
            maxlength: 50,
            uniqueName:true
        },
        'time[]': {
            uniqueTime: true,
            timeLimit: true,
            time24: true
        },
        'music_offices[sort_order]': {
            number: true,
            min: 1
        }
    },
    messages: {
        'music_offices[name]': {
            required: 'Bắt buộc phải điền tên',
            maxlength: 'Tên văn phòng không dài quá 50 ký tự'
        },
        'music_offices[sort_order]': {
            number: 'Sắp xếp phải là số và lớn hơn 0',
            min: 'Sắp xếp phải là số và lớn hơn 0'
        }
    }
});

var timeOutKey;
$('body').on('keypress', 'input.time', function () {
    clearTimeout(timeOutKey);
    timeOutKey = setTimeout(function () {
        $('#form-edit-office').valid();
    }, 500);
});

function uniqueName() {
    var link_check = $('#name').attr("checkName");
    var result = false;
    $.ajax({
        type:"GET",
        url: link_check,
        async: false,
        data: { 
                name: $('#name').val(),
                edit: $('#name').attr("edit")
                },
        success: function(data) {
            result = (data == 1) ? true : false;
        }
    });
    return result;
}

function uniqueTime(element) {
    var map = {}, i;
    var arr = [];
    $("input[name='time[]']").each(function() {
        if($(this).val() != ""){
            arr.push($(this).val()); 
        }
    });
    for(i = 0; i < arr.length; i++) {
        if(map[arr[i]]) {
            return false;
        }

        map[arr[i]] = true;
    }
    return true;
}

function timeFormat() {
     var arr = [], i;
    $("input[name='time[]']").each(function() {
        if($(this).val() != ""){
            arr.push($(this).val()); 
        }
    });

    for(i = 0; i<arr.length; i++){
        if (!/^\d{2}:\d{2}$/.test(arr[i])) return false;
        var parts = arr[i].split(':');
        if (parts[0] > 23 || parts[1] > 59) return false;
    }
    return true;
}

function timeLimit() {
    var arr = [], i;
    $("input[name='time[]']").each(function() {
        if($(this).val() != ""){
            arr.push($(this).val()); 
        }
    });
    for(i = 0; i<arr.length; i++){
        var arrSp = arr[i].split(":");
        if(arrSp[0]<8||arrSp[0]>17||(arrSp[0]==17&&arrSp[1]>30)) {
            return false;
        }
    }
    return true;
}

function formatRepo (repo) {
    if (repo.loading) return repo.text;

    var markup = '<div class="clearfix">' +
    '<div clas="col-sm-10">' +
    '<div class="clearfix">' +
    '<div class="col-sm-6">' + repo.text + '</div>' +
    '</div>';
    markup += '</div></div>';
    return markup;
}

function formatRepoSelection (repo) {
    return repo.text || repo.text;
}

$('.select2').select2({
    placeholder: "Điền mã nhân viên",
    allowClear: true,
    ajax: {
    url: $('#search-member').val(),
    dataType: 'json',
    delay: 250,
    data: function (params) {
      return {
        q: params.term,
        page: params.page
      };
    },
    processResults: function (data, params) {
      params.page = params.page || 1;

      return {
        results: data.items,
        pagination: {
          more: (params.page * 30) < data.total_count
        }
      };
    },
    cache: true
  },
  escapeMarkup: function (markup) { return markup; },
  minimumInputLength: 1,
  templateResult: formatRepo,
  templateSelection: formatRepoSelection
});
