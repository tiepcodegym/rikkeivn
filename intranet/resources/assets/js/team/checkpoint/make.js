/**
 * Set row height question
 */

fixHeight();

$(window).resize(function(){
    fixHeight();
});

jQuery(document).ready(function ($) {
    var id = $('#checkpoint_id').val(),
    emp_id = $('#emp_id').val(),
    checkpoint_old;

    if ($.cookie('proposed_current['+id+']['+emp_id+']')) {
        $("#proposed").val(funcJsonValue.decode($.cookie('proposed_current['+id+']['+emp_id+']')));
    }
    if ($.cookie('checkpoint_current['+id+']['+emp_id+']')) {
        checkpoint_old = JSON.parse($.cookie('checkpoint_current['+id+']['+emp_id+']'));
        for(var i = 0; i<checkpoint_old.length; i++) {
            $('div[data-questionid="'+checkpoint_old[i][0]+'"]').removeClass('point-rank').removeClass('btn-primary');
            $('div[data-questionid="'+checkpoint_old[i][0]+'"][data-rank="'+checkpoint_old[i][1]+'"]').addClass('point-rank').addClass('btn-primary');
            $('input[data-questionid="'+checkpoint_old[i][0]+'"]').val(checkpoint_old[i][2]);
        }
        $('.total-point').html(getTotalPoint());
    }
    $('input').keyup(function() {
        setCookie();
    });

    $('textarea').keyup(function() {
        setCookie();
    });

    //hover help
    hoverHelp();
});

/**
 * Select rank
 * @param element
 */

function selectRank(elem) {
    $(elem).parent().find('.container-question-child').removeClass('point-rank').removeClass('btn-primary');

    $(elem).addClass('point-rank').addClass('btn-primary');
    $('.total-point').html(getTotalPoint());
    setCookie();
}

/**
 * set cookie
 **/ 
function setCookie() {
    var team_id = $('#team_id_result').val();
    var id = $('#checkpoint_id').val();
    var emp_id = $('#emp_id').val();
    var proposed = $("#proposed").val();
    var totalPoint =parseFloat( getTotalPoint());
    var arrayQuestion = [];
    var keyCmtFinal = 'proposed_current['+id+']['+emp_id+']';

    var team_id = $('#team_id_result').val();
    $(".container-question-child.point-rank").each(function(){
        var point = parseInt($(this).data('rank'));
        var questionId = $(this).data("questionid");
        var comment = $(".comment-question[data-questionid='"+questionId+"']").val();
        arrayQuestion.push([questionId,point,comment]);
    });
    $.removeCookie('checkpoint_current['+id+']['+emp_id+']');
    $.removeCookie('totalPoint_current['+id+']['+emp_id+']');
    $.removeCookie(keyCmtFinal);

    $.cookie.json = true;
    $.cookie('checkpoint_current['+id+']['+emp_id+']', arrayQuestion, {expires: 7});
    $.cookie('totalPoint_current['+id+']['+emp_id+']', totalPoint, {expires: 7});
    $.cookie(keyCmtFinal, funcJsonValue.encode(proposed) , {expires: 7});
}

/**
 * Caculate total point realtime
 * @returns {Number}
 */
function getTotalPoint(){
    var point = 0;
    $('.container-question').each(function(){
        if($(this).find('.container-question-child.point-rank').length > 0) {
            var rank = $(this).find('.container-question-child.point-rank').data('rank');
            var weight = $(this).find('.container-question-child.point-rank').data('weight');
            point += parseFloat(rank * weight / 100 );
        }
    });
    return point.toFixed(2);
}

/**
 * Check all question not empty rank
 */
function confirm(){
    var invalid = false;
    $('#modal-confirm').modal('hide');
    $('.container-question').each(function(){
        if($(this).find('.container-question-child').length > 0) {
            if($(this).find('.container-question-child.point-rank').length <= 0) {
                invalid = true;
            }
        }
        
    });
    
    if(invalid) {
        $('#modal-confirm').modal('show');
        return false;
    }
    
    $('#modal-submit .modal-body').html('Số điểm hiện tại là ' + getTotalPoint() + '. Bạn có chắc chắn muốn hoàn thành bài Checkpoint?');
    $('#modal-submit').modal('show');
}

/**
 *save current form
 *
 *
 */
function save(id){
    var totalPoint = getTotalPoint();
    var proposed = $("#proposed").val();
    var arrayQuestion = [];
    var team_id = $('#team_id_result').val();
    $(".container-question-child.point-rank").each(function(){
        var point = parseInt($(this).data('rank'));
        var questionId = $(this).data("questionid");
        var comment = $(".comment-question[data-questionid='"+questionId+"']").val();
        arrayQuestion.push([questionId,point,comment]);
    });

    $.ajax({
        url: baseUrl + 'team/checkpoint/temporary_save',
        dataType: 'html',
        data: {
            arrayQuestion: arrayQuestion, 
            totalPoint: totalPoint,
            proposed: proposed,
            id: id,
            team_id: team_id
        },
    }).done(function() {
        $( this ).addClass( "done" );
    });
}

/**
 * Submit form
 * @param {string} token
 * @param {int} id
 */

function submit(token, id) {
    var totalPoint = getTotalPoint();
    var proposed = $("#proposed").val();
    var team_id = $('#team_id_result').val();
    
    var arrayQuestion = [];
    $(".container-question-child.point-rank").each(function(){
        var point = parseInt($(this).data('rank'));
        var questionId = $(this).data("questionid");
        var comment = $(".comment-question[data-questionid='"+questionId+"']").val();
        arrayQuestion.push([questionId,point,comment]);
    });
    
    $(".apply-click-modal").show(); 
    
    $.ajax({
        url: urlSubmit,
        type: 'post',
        dataType: 'html',
        data: {
            _token: token, 
            arrayQuestion: arrayQuestion, 
            totalPoint: totalPoint,
            proposed: proposed,
            id: id,
            team_id: team_id
        },
    })
    .done(function (data) { 
        //reset text
        $('.comment-question').val('');
        $('.proposed').val('');
        //Reset cookie
        makeNewTurn($('#checkpoint_id').val(), $('#emp_id').val());
        //Go to success page
        location.href = urlSuccessPage;
    })
    .fail(function () {
        alert("Ajax failed to fetch data");
    })
}

// Wait for window load
$(window).load(function() {
    // Animate loader off screen
    $(".se-pre-con").fadeOut("slow");;
});

var funcJsonValue = {
    encode: function(value) {
        return {
            1: value
        };
    },
    decode: function(value) {
        if (value) {
            return JSON.parse(value)[1];
        }
        return null;
    }
};

/**
 * remove cookie Item
 * @param {string} sKey
 * @param {string} sPath
 * @param {string} sDomain
 */
 function removeItem(sKey, sPath, sDomain) {
    document.cookie = encodeURIComponent(sKey) + 
    "=; expires=Thu, 01 Jan 1970 00:00:00 GMT" + 
    (sDomain ? "; domain=" + sDomain : "") + 
    (sPath ? "; path=" + sPath : "");
}

/**
 * confirm if employee want to make new empty-checkpoint
 * @param {string} checkpoint_id
 * @param {string} emp_id
 */
 function makeNewTurn(checkpointId, empId) {
    var checkpointCurrent = 'checkpoint_current['+checkpointId+']['+empId+']';
    var proposedCurrent   = 'proposed_current['+checkpointId+']['+empId+']';
    var totalPointCurrent = 'totalPoint_current['+checkpointId+']['+empId+']';
    removeItem(checkpointCurrent);
    removeItem(proposedCurrent);
    removeItem(totalPointCurrent);
    location.reload();
}
