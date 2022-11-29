$('input[type=text]').each(function(){
    var id = $(this).attr('id');
    if(id != 'employee_name' 
            && id != 'japanese_name' 
            && id != 'rikker_relate'
            && id != 'pm_email_jp'
            && id != 'rikker_relate_check' 
            && id != 'rikker_relate_validate'
            && id != 'start_date' 
            && id != 'end_date'
            && id != 'time_reply'
            && id != 'css_creator_name'
            && id != 'project_name_css'
            && id != 'start_onsite_date'
            && id != 'end_onsite_date') {
        $(this).prop('disabled', true);
    }
});

/**
 * 
 * iCheck load
 */
$('input').iCheck({
    checkboxClass: 'icheckbox_minimal-blue',
    radioClass: 'iradio_minimal-blue'
}); 

var teamArray = []; 

$(window).resize(function(){
    setMarginTopEndDate();
});

function setMarginTopEndDate(){
    var startDate = $.trim($('#start_date').val());
    if($(window).width() < 678){
        if(startDate === '') {
            $('#end_date').parent().css('margin-top','30px');
        } else {
            $('#end_date').parent().css('margin-top','0');
        }
    } else{
        $('#end_date').parent().css('margin-top','0');
    } 
}

$(document).ready(function () {
    $(".project_type input[type=radio]:first-child").prop('checked', true);
    $(".team-tree a").removeAttr("href");
    $(".team-tree a").attr("onclick", "change_bgcolor_element(this)");

    //hide calendar sau khi select
    $('#start_date').on('changeDate', function () {
        $(this).datepicker('hide');
        //$('#end_date').focus();
        $('#start_date').css('color','#555').css('font-size','14px');
        $('#start_date-error').remove();
    });
    
    $('#end_date').on('changeDate', function () {
        $(this).datepicker('hide');
        $('#end_date').css('color','#555').css('font-size','14px');
        $('#end_date-error').remove();
    });
});

$(document).ready(function () {
    /** Check is firefox */
    if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
        $(".date").css('top','-1px');
    }
    /** End check is firefox */
   
});


$('#rikker_relate').keydown(function(e){
    var keyCode = e.keyCode || e.which; 
    var value = $.trim($(this).val());
    
    if(keyCode === 8 && value === '') { // backspace event
        backSpace('rikker_relate');
    } else if (keyCode === 9 || keyCode === 13 
            || keyCode === 188 || keyCode === 186) { //tab, enter, comma, semi-colon press
        var value = $.trim($('#rikker_relate').val());
        if ((ajaxLoadingEmail['#rikker_relate'] !== undefined && ! ajaxLoadingEmail['#rikker_relate']) || 
            ajaxLoadingEmail['#rikker_relate'] === undefined) {
            if(value !== ''){
               tabEvent('#rikker_relate',value);
               e.preventDefault();
            }
        } else {
            return false;
        }
    } else if(keyCode === 38 || keyCode === 40){ //up down arrow event
        selectUpDown('rikker_relate',keyCode);
    } else { 
        if(typeof ajax_request !== 'undefined')
            ajax_request.abort();
        
        var keyValue = '';
        if(keyCode !== 8){ // not backspace
            keyValue = String.fromCharCode(e.keyCode);
            value += keyValue;
        } else {
            value = value.slice(0, -1); 
        }
        ajaxLoadingEmail['#rikker_relate'] = true;
        showList('#rikker_relate',value);
    }
});


$('#rikker_relate').blur(function(e) {
    if ($().isClickWithoutDom({
        'container': $(this),
        'except': $(this).siblings('.rikker-result')
    })) {
        value = $.trim($('#rikker_relate').val());
        if(value !== ''){
           tabEvent('#rikker_relate',value, {setText: 1});
           e.preventDefault();
        }
    }
});

 //disable enter to submit form
 $('#frm_create_css').on('keyup keypress', function(e) {
  var keyCode = e.keyCode || e.which;
  if (keyCode === 13) { 
    e.preventDefault();
    return false;
  }
});

function setTeam(token) {
    var value = $('#set_pj').val();
    if(value == 0) {
        $('input[type=text]').each(function(){
            var id = $(this).attr('id');
            if(id != 'employee_name' 
                && id != 'japanese_name'
                && id != 'rikker_relate_check' 
                && id != 'rikker_relate_validate') {
                $(this).val('');
            }
        });
        return false;
    }
    
    $.ajax({
        url: baseUrl + 'css/setTeam',
        type: 'post',
        dataType: 'json',
        data: {
            _token: token, 
            projsId: value,
        },
    })
    .done(function (data) {
        var dataCustomer = data['cus_name'];
        if (data['cus_email']) {
            dataCustomer += ' (' + data['cus_email'] + ')';
        }
        $('#company_name').val(data['company_name']);
        $('#customer_name').val(dataCustomer);
        $('#project_name').val(data['name']);
        $('.project_type').html(data['type']);
        $('input[name=pm_email]').val(data['pm_name'] + ' (' + data['pm_account'] + ')');
        $('#pm_email_name').val(data['pm_name']);
        if ($('#end_date').datepicker("setDate", data['end'])) {
            $('#start_date').datepicker("setDate", data['start']);
        } else {
            $('#start_date').datepicker("setDate", data['start']);
        }
        $('#team_relate').val(data['teamNames']);
        $('#team_ids').val(data['teamIds']);
        $('#project_code').val(data['project_code']);
        
        if(data['japanese_name'] != '') {
            $('#pm_name_jp-error').remove();
        }
        
        if (typeof data['cus_name'] == "undefined") {
            $('#modal-no-customer').modal('show');
        }
        if (data['type'] == 'ONSITE') {
            $('.onsite-date-container').removeClass('hidden');
        } else {
            $('.onsite-date-container').addClass('hidden');
        }
    })
    .fail(function () {
        alert("Ajax failed to fetch data");
    })
}

$('.btn-create').click(function() {
     // validate
    if(validate()) {
        return false;
    }
    setMarginTopEndDate();
    var $btnCreate = $(this);
    $btnCreate.prop('disabled', true);
    $('i.fa-refresh').removeClass('hidden');
    var employee_id = $('#employee_id').val();
    var japanese_name = $.trim($('#japanese_name').val());
    var projId = $('#set_pj').val();
    var pm_name_jp =     $.trim($('#pm_email_jp').val());
    var rikker_relate = '';
    var token = $('#token').val();
    var create_or_update = $('#create_or_update').val();
    var css_id = $('#css_id').val();
    var start_date = $('#start_date').val();
    var end_date = $('#end_date').val();
    var lang = $('input[name=lang]:checked').val();
    var project_name_css = $('#project_name_css').val();
    var time_reply = $('#time_reply').val();
    var css_creator_name = $('#css_creator_name').val();

    
    $('input[name="rikker_relate[]"]').each(function(){
        if(rikker_relate == '')
            rikker_relate += $.trim($(this).val());
        else
            rikker_relate += ',' + $.trim($(this).val());
    });
    
    $.ajax({
        url: baseUrl + 'css/save',
        type: 'post',
        dataType: 'html',
        data: {
            _token: token, 
            employee_id: employee_id,
            japanese_name: japanese_name,
            projId: projId,
            pm_name_jp: pm_name_jp,
            rikker_relate: rikker_relate,
            create_or_update: create_or_update,
            css_id: css_id,
            start_date: start_date,
            end_date: end_date,
            lang: lang,
            project_name_css:project_name_css,
            start_onsite_date: $('#start_onsite_date').val(),
            end_onsite_date: $('#end_onsite_date').val(),
            time_reply: time_reply,
            css_creator_name: css_creator_name
        },
    })
    .done(function (url) {
        $('#rikker_relate_check').val('');
        window.location.href = url;
    })
    .fail(function () {
        alert("Ajax failed to fetch data");
        $btnCreate.prop('disabled', false);
        $btnCreate.find('i').addClass('hidden');
    });
});

function validate() {
    $('#japanese_name-error').remove();
    $('#pj-error').remove();   
    $('#pm_name_jp-error').remove();   
    $('#rikker-relate-error').remove();   
    $('#rikker-relate-validate-error').remove();  
    
    var japanese_name = $.trim($('#japanese_name').val());
    var projId = $('#set_pj').val();
    var pm_name_jp =     $.trim($('#pm_email_jp').val());
    var invalid = false;
    
    //validate project
    if(projId == 0) {
        $('#set_pj').after('<label id="pj-error" class="error" for="name">' +requiredProj +'</label>');
        invalid = true;
    }
    
    //validate emp japanese name
    if(japanese_name == '') {
        $('#japanese_name').after('<label id="japanese_name-error" class="error" for="name">' + requiredName + '</label>');
        invalid = true;
    }
    
    //validate pm japanese name
    if(pm_name_jp == '') {
        $('#pm_email_jp').after('<label id="pm_name_jp-error" class="error" for="name">' +requiredName + '</label>');
        invalid = true;
    }
    
    //validate rikker relate
    if($.trim($('#rikker_relate_check').val()) == '') {
        $('.rikker-relate-container').after('<label id="rikker-relate-error" class="error" for="name">' + requiredRelPerson + '</label>');
        invalid = true;
    }
    
    if($.trim($('#rikker_relate_validate').val()) == '') {
        $('.rikker-relate-container').after('<label id="rikker-relate-validate-error" class="error" for="name">' + requiredEmail +'</label>');
        invalid = true;
    } 
    
    if ($.trim($('#start_date').val()) == '') {
        $('#start_date').after('<label id="start_date-error" class="error" for="name">' + requiredStartDate + '</label>');
        invalid = true;
    }
    
    if ($.trim($('#end_date').val()) == '') {
        $('#end_date').after('<label id="end_date-error" class="error" for="name">' + requiredEndDate + '</label>');
        invalid = true;
    }

    if ($.trim($('#time_reply').val()) == '' && $('input[name=lang]:checked').val() == 0) {
        $('#time_reply').after('<label id="time_reply-error" class="error" for="name">' + requiredTimeReply + '</label>');
        invalid = true;
    } else {
        $('#time_reply-error').html('');
    }
    
    if (!$('.onsite-date-container').hasClass('hidden')) {
        if ($.trim($('#start_onsite_date').val()) == '') {
            $('#start_onsite_date').after('<label id="start_onsite_date-error" class="error" for="name">' + requiredStartOnsite + '</label>');
            invalid = true;
        }
        if ($.trim($('#end_onsite_date').val()) == '') {
            $('#end_onsite_date').after('<label id="end_onsite_date-error" class="error" for="name">' + requiredEndOnsite + '</label>');
            invalid = true;
        }
    }
    
    return invalid;
}

/*
* Datepicker set
*/
var aryElDates = [
    {
        start: $('#start_date'),
        end: $('#end_date'),
    },
    {
        start: $('#start_onsite_date'),
        end: $('#end_onsite_date'),
    },
];

for (var i = 0; i < aryElDates.length; i++) {
    var elDates = aryElDates[i];
    var $startMonth = elDates.start;
    var $endMonth = elDates.end;

    $startMonth.datepicker({
        format: 'yyyy/mm/dd',
        autoclose: true,
        endDate: $endMonth.val()
    }).on('changeDate', function () {
        $endMonth.datepicker('setStartDate', $startMonth.val());
    });

    $endMonth.datepicker({
        format: 'yyyy/mm/dd',
        autoclose: true,
        startDate: $startMonth.val()
    }).on('changeDate', function () {
        $startMonth.datepicker('setEndDate', $endMonth.val());
    });
};
    
