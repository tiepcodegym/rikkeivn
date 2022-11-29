var ajax_request,
    ajaxLoadingEmail = {},
    ajaxLoadDelay = 400,
    ajaxLoadFlagDelay = {},
    ajaxClearTimeout;
    
/**
 * Show list email rikkei
 */
function showList(elem,value) {
    if (ajaxLoadFlagDelay[elem] !== undefined) {
        ajaxLoadFlagDelay[elem] = false;
    }
    var token = $.trim($('#token').val());
    var dataFor = $(elem).data('name');
    var resultElem = $('body').find('.rikker-result[data-for="'+dataFor+'"]');
    if(value !== '') {
        clearTimeout(ajaxClearTimeout);
        ajaxClearTimeout = setTimeout (function() {
            ajax_request = $.ajax({
                url: baseUrl + '/css/get_rikker',
                type: 'post',
                dataType: 'JSON',
                data: {
                    _token: token, 
                    value: value
                }
            })
            .done(function (data) {
                if(data.length > 0) {
                    var html = '';
                    var count = data.length;

                    resultElem.css('display','block');
                    for(var i=0;i<count;i++){
                        html += '<div class="rikker-item" data-name="'+dataFor+'" onclick="chooseRikker(this);">';
                        html += '   <div class="col-xs-12 ">';
                        html += '       <div class="row">';
                        html += '           <div class="pull-left">';
                        html += '               <img style="border-radius: 50px; width: 50px; height: 50px;" src="'+data[i].avatar_url+'" />';
                        html += '           </div>';
                        html += '           <div class="pull-left" style="padding: 8px;">';
                        html += '               <p class="rikker-name" data-name="'+data[i].name+'">'+data[i].name+'</p>';
                        html += '               <p class="rikker-email" data-email="'+data[i].email+'">'+data[i].email+'</p>';
                        html += '               <input class="rikker-name-jp" type="hidden" data-name-jp="'+data[i].japanese_name+'" />';
                        html += '           </div>';
                        html += '       </div>';
                        html += '   </div>';
                        html += '</div>';
                    }

                    resultElem.html(html);
                    $('.rikker-result[data-for="'+dataFor+'"] .rikker-item:first-child').addClass('hovered');
                }else {
                    resultElem.html('');
                    resultElem.css('display','none');
                }
                if (ajaxLoadingEmail[elem] !== undefined) {
                    ajaxLoadingEmail[elem] = false;
                }
            })
            .fail(function () {
                console.log("Ajax failed to fetch data");
                if (ajaxLoadingEmail[elem] !== undefined) {
                    ajaxLoadingEmail[elem] = false;
                }
            });
        }, ajaxLoadDelay);
    } else {
        resultElem.css('display','none');
        if (ajaxLoadingEmail[elem] !== undefined) {
            ajaxLoadingEmail[elem] = false;
        }
    }
}



function selectUpDown(dataFor,keyCode){
    if($('.rikker-result[data-for="'+dataFor+'"] .rikker-item').length > 0) {
        if (keyCode === 40) { // down arrow
            if ($(".rikker-result[data-for='"+dataFor+"'] .rikker-item.hovered").length <= 0) { //if no li has the hovered class
                $('.rikker-result[data-for="'+dataFor+'"] .rikker-item:first-child').addClass("hovered");
            } else {
                $(".rikker-result[data-for='"+dataFor+"'] .rikker-item.hovered").removeClass("hovered").next().addClass("hovered");
            }
        } else if(keyCode == 38){ //up arrow
            if ($(".rikker-result[data-for='"+dataFor+"'] .rikker-item.hovered").length <= 0) { //if no li has the hovered class
                $('.rikker-result[data-for="'+dataFor+'"] .rikker-item:last-child').addClass("hovered");
            } else {
                $(".rikker-result[data-for='"+dataFor+"'] .rikker-item.hovered").removeClass("hovered").prev().addClass("hovered");
            }
        }
    }
}

function backSpace(dataFor){
    if($(".rikker-set[data-for='"+dataFor+"'] .vN").length > 0) {
        $(".rikker-set[data-for='"+dataFor+"'] .vN:last-child .vM").trigger('click');
    }
}

function tabEvent(elem, value, option){
    if (option == undefined) {
        option = {};
    }
    var dataFor = $(elem).data('name');
    if($('.rikker-result[data-for="'+dataFor+'"]').find('.rikker-item').length > 0 &&
            (option.setText == undefined || ! option.setText)
    ){
        $('.rikker-result[data-for="'+dataFor+'"] .rikker-item.hovered').trigger('click');
    } else{
        $('#pm_email_name').removeAttr('readonly');
        var flag = false;
        var flag2 = $('.vN').parent().attr('flag');
        //Check email exist
        $('input[type="hidden"][data-for="'+dataFor+'"]').each(function(){
            if($(this).val() === value) {
                flag = true;
                return false;
            }
        });
        if(flag2 === "unValidate") {
            if(!flag) { 
                var html = '';
                var set = $('body').find('.rikker-set[data-for="'+dataFor+'"]');
                if (validateEmailOutSide(value.toLowerCase())){
                    html = '<span class="vN bfK a3q" email="'+value+'"><div class="vT">'+value+'</div><div class="vM" data-remove="'+value+'" data-for="'+dataFor+'" onclick="removeRikker(this);"></div></span>';
                }else{
                    html = '<span class="vN bfK a3q error" email="'+value+'" style="background-clo"><div class="vT">'+value+'</div><div class="vM" data-remove="'+value+'" data-for="'+dataFor+'" onclick="removeRikker(this);"></div></span>';
                }
                
                var setHtml = set.html();
                
                set.css('display','inline');
                
                if (validateEmailOutSide(value.toLowerCase())){
                    $('.rikker-relate-container').append('<input type="hidden" data-for="'+dataFor+'" name="'+dataFor+'[]" value="'+value+'" />');
                }
                
                if(dataFor === 'pm_email'){ // choose only 1
                    $('#'+dataFor).attr('readonly','true').css('background-color','#fff');
                    set.html(html);
                }else{
                    set.html(setHtml + html);
                }
                checkSet(dataFor);
            } 
        } else {
            if(!flag) { 
                var html = '';
                var set = $('body').find('.rikker-set[data-for="'+dataFor+'"]');
                if (validateEmail(value.toLowerCase())){
                    html = '<span class="vN bfK a3q" email="'+value+'"><div class="vT">'+value+'</div><div class="vM" data-remove="'+value+'" data-for="'+dataFor+'" onclick="removeRikker(this);"></div></span>';
                }else{
                    html = '<span class="vN bfK a3q error" email="'+value+'" style="background-clo"><div class="vT">'+value+'</div><div class="vM" data-remove="'+value+'" data-for="'+dataFor+'" onclick="removeRikker(this);"></div></span>';
                }
                
                var setHtml = set.html();
                
                set.css('display','inline');
                
                if (validateEmail(value.toLowerCase())){
                    $('.rikker-relate-container').append('<input type="hidden" data-for="'+dataFor+'" name="'+dataFor+'[]" value="'+value+'" />');
                }
                
                if(dataFor === 'pm_email'){ // choose only 1
                    $('#'+dataFor).attr('readonly','true').css('background-color','#fff');
                    set.html(html);
                }else{
                    set.html(setHtml + html);
                }
                checkSet(dataFor);
            } 
        }
    }
    $('#'+dataFor).val('');
    $('.rikker-result[data-for="'+dataFor+'"]').css('display','none');
    $('.rikker-result[data-for="'+dataFor+'"]').html('');
}

function chooseRikker(elem) {
    var name = $(elem).find('.rikker-name').data('name');
    var email = $(elem).find('.rikker-email').data('email');
    var name_jp = $(elem).find('.rikker-name-jp').data('name-jp');
    var flag = false;
    var dataFor = $(elem).data('name');
    //Check email exist
    $('input[type="hidden"][data-for="'+dataFor+'"]').each(function(){
        if($(this).val() === email) {
            flag = true;
            return false;
        }
    });
    
    //If exist false
    if(!flag) {
        if (! name) {
            nameShowNull = email;
            nameValueNull = email.replace(/@.*$/,'');
        } else {
            nameShowNull = name;
            nameValueNull = name;
        }
        var set = $('body').find('.rikker-set[data-for="'+dataFor+'"]');
        var setHtml = set.html();
        var html = '<span class="vN bfK a3q" email="'+email+'"><div class="vT">'+nameShowNull+'</div><div class="vM" data-remove="'+email+'" data-for="'+dataFor+'" onclick="removeRikker(this);"></div></span>';
        if($('#'+dataFor).data('length') == '1'){ // choose only 1
            set.html(html);
            $('input[type="hidden"][data-for="'+dataFor+'"]').remove()
            $('.rikker-relate-container').append('<input type="hidden" data-for="'+dataFor+'" name="'+dataFor+'[]" value="'+email+'" />');
            
            $('body').find('#'+dataFor+'_name').val(nameValueNull);
            $('body').find('#'+dataFor+'_jp').val(name_jp);
            $('#'+dataFor).attr('readonly','true').css('background-color','#fff');
            $('#'+dataFor+'_name').attr('readonly','true');
        }else{
            set.html(setHtml + html);
            $('.rikker-relate-container').append('<input type="hidden" data-for="'+dataFor+'" name="'+dataFor+'[]" value="'+email+'" />');
        }
        
        checkSet(dataFor);
    }
    $('#'+dataFor).val('');
    $('#'+dataFor).focus();
    $('.rikker-result[data-for="'+dataFor+'"]').css('display','none');
    $('.rikker-result[data-for="'+dataFor+'"]').html('');
}

function removeRikker(elem) {
    var email = $(elem).data('remove');
    var dataFor = $(elem).data('for');
    $('input[type=hidden][data-for="'+dataFor+'"][value="'+email+'"]').remove();
    $(elem).parent().remove();
    $('#'+dataFor+'_name').val('').removeAttr('readonly');
    $('#'+dataFor+'_jp').val('');
    checkSet(dataFor);
    $('#'+dataFor).removeAttr('readonly');
    $('#'+dataFor).focus();
    
    
}

function checkSet(dataFor){
    $('#'+dataFor).removeClass('pm-email-update');
    $('#'+dataFor).removeClass('rikker-relate-update');
    if($('.rikker-set[data-for="'+dataFor+'"] .vN').length > 0) {
        $('.rikker-set[data-for="'+dataFor+'"]').css('display','inline');
        $('#'+dataFor).css('top','-3px ').css('height','26px ');
        if($('#'+dataFor).position().left < 15) {
            $('#'+dataFor).css('top','0 ');
        }
    } else { 
        $('.rikker-set[data-for="'+dataFor+'"]').css('display','none');
        $('#'+dataFor).css('top','0 ').css('height','32px');
    }
    
    checkEmail(dataFor);
}

function checkEmail(dataFor){
    var email_invalid = false;
    var email_format = false;
    $('.rikker-relate-container').parent().find('label.error').remove(); //remove not email rikkei label error
    if($('input[type=hidden][data-for="'+dataFor+'"]').length > 0){
        $('#'+dataFor+'_check').val('1'); //check required
        $('#'+dataFor+'_check-error').remove(); //check required
    }   
        
    if($('.rikker-set[data-for="'+dataFor+'"] .vN').length > 0){
        $('#'+dataFor+'_check').val('1'); //check required
        $('#'+dataFor+'_check-error').remove(); //check required

        $('.rikker-set[data-for="'+dataFor+'"] .vN').each(function(){
            var email = $(this).attr('email');
            var flag =$(this).parent().attr('flag');
            if(flag == "unValidate") {
                if (!validateEmailOutSide(email)){
                    email_invalid = true;
                    email_format = true;
                    return false;
                }
            } else {
                if(!validateEmail(email)){
                    email_invalid = true;
                    return false;
                }
            }
        });
        
        if(email_invalid){ // not email rikkei
            $('#'+dataFor+'_validate').val(''); //check validate email
            //add label error
            $('.rikker-relate-container').after('<label class="error" style="display: block;">'+emailInvalid+'</label>');
        }else {
            $('#'+dataFor+'_validate').val('1'); //check validate email
            $('#'+dataFor+'_validate-error').remove(); //check required
        }
        if(email_format){ // not email rikkei
            $('#'+dataFor+'_validate').val(''); //check validate email
            //add label error
            $('.rikker-relate-container').after('<label class="error" style="display: block;">'+emailFormat+'</label>');
        }else {
            $('#'+dataFor+'_validate').val('1'); //check validate email
            $('#'+dataFor+'_validate-error').remove(); //check required
        }
    }else {
        $('#'+dataFor+'_check').val(''); //check required
        $('#'+dataFor+'_validate').val('1'); //check validate email
        $('#'+dataFor+'_validate-error').remove(); //check required
    }
}


function validateEmail(email) {
    var re = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
    if (re.test(email)) {
        if (email.indexOf('@rikkeisoft.com', email.length - '@rikkeisoft.com'.length) !== -1) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function validateEmailOutSide(email) {
    var re = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
    if (re.test(email)) {
        return true;
    }
}
$(document).mouseup(function (e) {
    var container = $(".rikker-result");

    if (!container.is(e.target) // if the target of the click isn't the container...
        && container.has(e.target).length === 0) // ... nor a descendant of the container
    {
        container.hide();
        container.html('');
    }
});


/**
 * Calculate padding left input rikker relate when choose or remove 1 item
 * @returns Number
 */
function getLeft() {
    var setWidth = $('.rikker-set').width();
    return setWidth + 19;
}

$('.rikker-item').hover(
				
    function () {
       $(this).css("background-color","#ececec");
    }, 

    function () {
       $(this).css("background-color","#fff");
    }
 );
 