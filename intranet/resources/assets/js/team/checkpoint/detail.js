/**
 * Set row height question
 */

fixHeight();

$(window).resize(function() {
    fixHeight();
});

// End set height

jQuery(document).ready(function ($) {
    //hover help
    hoverHelp();

    var id = $('#checkpoint_id').val();
    var emp_id = $('#emp_id').val();
    var resultId = $('#result_id').val();
    var checkpoint_old;

    // $('.total-point-container-leader').find('.total-point').html(totalPoint_old); 
    
    if ($.cookie('proposed_current['+id+']['+emp_id+']['+resultId+']')) {
        $("#proposed-leader").val(funcJsonValue.decode($.cookie('proposed_current['+id+']['+emp_id+']['+resultId+']')));
    }
    if ($.cookie('checkpoint_current['+id+']['+emp_id+']['+resultId+']')) {
        checkpoint_old = JSON.parse($.cookie('checkpoint_current['+id+']['+emp_id+']['+resultId+']'));
        for (var i = 0; i<checkpoint_old.length; i++) {
            $('div[data-questionid="'+checkpoint_old[i][0]+'"]').removeClass('point-rank').removeClass('btn-danger').removeClass('rank-review');
            $('div[data-questionid="'+checkpoint_old[i][0]+'"][data-rank="'+checkpoint_old[i][1]+'"]').addClass('point-rank').addClass('btn-danger').addClass('rank-review').removeClass('btn-default');
            $('.comment-question-leader[data-questionid="'+checkpoint_old[i][0]+'"]').val(checkpoint_old[i][2]);
        }
        $('.rank-review').parent().find('.btn-primary').removeClass('point-rank');
        $('.total-point-container-leader .total-point').html(getTotalPoint());
    }
    
    $('.comment-question-leader').keyup(function() {
        setCookie();
    });

    $('textarea').keyup(function() {
        setCookie();
    });
});

/**
 * Select rank
 * @param elem
 */

function selectRank(elem) {
    $(elem).parent().find('.container-question-child')
            .removeClass('point-rank')
            .removeClass('btn-danger')
            .removeClass('rank-review');
            
    $(elem).parent().find('.container-question-child').each(function() {
        if($(this).hasClass('btn-primary')) {
        
        } else {
            $(this).addClass('btn-default');
        }
    });
    // If leader choose same employee
    if($(elem).hasClass('btn-primary')) {

    } else {
        $(elem).addClass('btn-danger').removeClass('btn-default').addClass('rank-review');
    }
    $(elem).addClass('point-rank');

    $('.total-point-container-leader .total-point').html(getTotalPoint());
    setCookie();
}

/**
* set cookie
**/ 
function setCookie(){
    var id = $('#checkpoint_id').val();
    var empId = $('#emp_id').val();
    var resultId = $('#result_id').val();
    var proposed = $("#proposed-leader").val();
    var arrayQuestion = [];
    var keyCmtFinal = 'proposed_current['+id+']['+empId+']['+resultId+']';

    $(".container-question-child.rank-review").each(function(){
        var point = parseInt($(this).data('rank'));
        var questionId = $(this).data("questionid");
        var comment = $(".comment-question-leader[data-questionid='"+questionId+"']").val();
        arrayQuestion.push([questionId,point,comment]);
    });
    $.removeCookie('checkpoint_current['+id+']['+empId+']['+resultId+']');
    $.removeCookie('totalPoint_current['+id+']['+empId+']['+resultId+']');
    $.removeCookie(keyCmtFinal);

    $.cookie.json = true;
    $.cookie('checkpoint_current['+id+']['+empId+']['+resultId+']', arrayQuestion, {expires: 7});
    $.cookie(keyCmtFinal, funcJsonValue.encode(proposed) , {expires: 7});
}

if(canEdit == 1) {
    $(".hover-rank-detail").hover(
        function(){
            if($(this).hasClass('border-blue')){
                //$(this).css("outline", "#da3d7e solid 2px");
            } else {
                $(this).css("border", "#da3d7e solid 2px");
            }

        }, 
        function(){
            if($(this).hasClass('border-blue')){
                //$(this).css("outline", "none");
            } else {
                $(this).css("border", "none");
            }
        }
    );
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
//    $('.container-question').each(function(){
//        if($(this).find('.container-question-child').length > 0) {
//            if($(this).find('.container-question-child.point-rank').length <= 0) {
//                invalid = true;
//            }
//        }
//        
//    });
    
    if(invalid) {
        $('#modal-confirm').modal('show');
        return false;
    }
    if (isLeader) {
        $('#modal-submit .modal-body').html(leaderConfirmSubmitText);
        $('#modal-submit .modal-footer button.submit').html(submitText);
    } else {
        $('#modal-submit .modal-body').html('Số điểm hiện tại là ' + getTotalPoint() + '. Bạn có chắc chắn muốn hoàn thành bài Checkpoint?');
        $('#modal-submit .modal-footer button.submit').html(guiBai);
    }
    $('#modal-submit').modal('show');
}

/**
 * Submit form
 * @param {string} token
 * @param {int} id
 */

function submit(token,resultId, checkpoint_id, emp_id, resultId){
    var totalPoint = getTotalPoint();
    var proposed = $("#proposed-leader").val();
    
    var arrayQuestion = [];
    $(".container-question-child.point-rank").each(function(){
        var point = parseInt($(this).data('rank'));
        var questionId = $(this).data("questionid");
        var comment = $(".comment-question-leader[data-questionid='"+questionId+"']").val();
        arrayQuestion.push([questionId,point,comment]);
    });
    
    $(".apply-click-modal").show(); 
    
    $.ajax({
        url: baseUrl + 'team/checkpoint/save_result_leader',
        type: 'post',
        dataType: 'html',
        data: {
            _token: token,
            arrayQuestion: arrayQuestion, 
            totalPoint: totalPoint,
            proposed: proposed,
            resultId: resultId
        },
    })
    .done(function () { 
        //reset text
        $('.comment-question-leader').val('');
        $('.proposed-leader').val('');
        var checkpoint_current = 'checkpoint_current['+checkpoint_id+']['+emp_id+']['+resultId+']';
        var proposed_current   = 'proposed_current['+checkpoint_id+']['+emp_id+']['+resultId+']';
        removeItem(checkpoint_current);
        removeItem(proposed_current);
        location.href = baseUrl + "team/checkpoint/list";
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
 *remove cookie Item
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
 *confirm if employee want to make new empty-checkpoint
 * @param {string} checkpoint_id
 * @param {string} emp_id
 */
 function makeNewTurn(checkpointId, empId, resultId) {
    var checkpoint_current = 'checkpoint_current['+checkpointId+']['+empId+']['+resultId+']';
    var proposed_current   = 'proposed_current['+checkpointId+']['+empId+']['+resultId+']';
    removeItem(checkpoint_current);
    removeItem(proposed_current);
    location.reload();
}
