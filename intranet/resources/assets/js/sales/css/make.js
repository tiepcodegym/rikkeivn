$(document).ready(function(){
    fixPointContainer();
    /**
     * Hover rank to show description tooltip
     */
    $('.rateit').bind('over', function (event, value) { 
        $('span.tooltip').remove();
        $(this).before('<span class="tooltip"></span>');
        $tooltip = $(this).parent().find('span.tooltip');
        var offset = $(this).offset();
        var parentOffset = $(this).parent().offset();
        var leftPosition = offset.left - parentOffset.left;
        switch (value) {
            case 1: 
                $tooltip.text(textUnsatisfactory);
                if (currentLang == jpLang) {
                    $tooltip.css('left', leftPosition - 6);
                } else {
                    $tooltip.css('left', leftPosition - 32);
                }
                break;
            case 2: 
                $tooltip.text(textSatisfactory);
                if (currentLang == jpLang) {
                    $tooltip.css('left', leftPosition + 2);
                } else {
                    $tooltip.css('left', leftPosition - 5);
                }
                break;
            case 3: 
                $tooltip.text(textFair);
                if (currentLang == jpLang) {
                    $tooltip.css('left', leftPosition + 34);
                } else {
                    $tooltip.css('left', leftPosition + 36);
                }
                break;
            case 4: 
                $tooltip.text(textGood);
                if (currentLang == jpLang) {
                    $tooltip.css('left', leftPosition + 42);
                } else {
                    $tooltip.css('left', leftPosition + 51);
                }
                break;
            case 5:
                $tooltip.text(textExcellent);
                if (currentLang == jpLang) {
                    $tooltip.css('left', leftPosition + 74);
                } else {
                    $tooltip.css('left', leftPosition + 62);
                }
                break
            default:
                $(this).parent().find('span.tooltip').remove();
                break;
        }
    });  
});

$(window).scroll(function(){
    fixPointContainer();
});

$(window).resize(function(){
    fixPointContainer();
});

/**
 * show or hide total point container at top right make css page
 */
function fixPointContainer(){
    var screen_width = $(window).width();
    var project_width = $('#make-header').width();
    var point_width = $(".total-point-container ").outerWidth();
    if($('.visible-check').visible()){
        $(".total-point-container ").css('position','inherit');
    } else {
        $(".total-point-container ").css('position','fixed');
        var fix_width = (screen_width - project_width)/2;
        if(fix_width <= point_width){
            $(".total-point-container ").css('right',fix_width);
        } else {
            var fix_width = fix_width - point_width;
            $(".total-point-container ").css('right',fix_width);
        }
    }
}
/**
 * Hover comment to show description tooltip
 */
$(document).ready(function () {
    $.fn.textWidth = function(text, font) {
        if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
        $.fn.textWidth.fakeEl.text(text || this.val() || this.text()).css('font', font || this.css('font'));
        return $.fn.textWidth.fakeEl.width();
    };

    $('textarea.comment-question').on('keyup', function (event) {
        const commentEle = $(this).parent('.comment');
        const content = $(this).val();
        var checkEnter = false;

        if(event.keyCode === 13) {
            checkEnter = true;
        }
        if($(this).textWidth() >= $(this).width() || checkEnter) {
            console.log('adsafsa');
            commentEle.addClass('dropdown');
        }
//                else if ($(this).textWidth() < $(this).width() && !checkEnter) {
//                    commentEle.removeClass('dropdown');
//                }

        commentEle.find('.dropdown-content').find('textarea').val(content);
    });
    $('.dropdown-content>textarea').on('keyup', function () {
        const commentEle = $(this).parent().parent('.comment');
        const content = $(this).val();
        commentEle.find('.comment-question').val(content);
    });

    $('.analysis').change( function() {
        var analysisContent = $(this).val();
        var questionId = $(this).data('questionid');
        var cssResultId = $(this).data('cssresultid');
        var token = $('.container-question').data('token');
        var url = $('.container-question').data('url');
        var cssId = $('.container-question').data('cssid');
        $(this).after('<span class="analysis-refresh"><i class="fa fa-refresh fa-spin"></i></span>');
        $(this).css({border: '1px solid #ccc'});
        $("textarea[name=analysis-question-"+questionId+"]").nextAll('.analysis-error').addClass('hidden');

        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {
              _token: token,
              questionId: questionId,
              cssResultId: cssResultId,
              cssId: cssId,
              analysisContent: analysisContent,
            },
            success: function(response) {
                RKExternal.notify(response.message);
                $('.analysis-error').addClass('hidden');
            },
        })
        .done(function() {
            $('.analysis-refresh').addClass('hidden');
        });

    });

    $('.submit-status').click(function() {
        var cssId = $(this).data('cssid');
        var status = $(this).data('value');
        var url = $(this).data('url');
        var token = $(this).data('token');
        var cssResultId = $('.analysis').data('cssresultid');
        var resultDetailCss = JSON.parse($('.submit-resultDetailCss').val());
        $('#submit-status').prop('disabled', true);
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {
              _token: token,
              cssId: cssId,
              statusCss: status,
              cssResultId: cssResultId,
            },
            success: function(response) {
                $('#submit-status').prop('disabled', false);
                if (response.success == 1) {
                    RKExternal.notify(response.message);
                    $('#status-css').addClass('btn-primary');
                    $('#status-css').removeClass('btn-danger');
                    $('#status-css').text("Submitted");
                    location.reload();
                } else if (response.success == 2) {
                    RKExternal.notify(response.message, false);
                } else {
                    if (resultDetailCss.length) {
                        for (var i = 0; i < resultDetailCss.length; i++) {
                            if (resultDetailCss[i].point > 0 && resultDetailCss[i].point <= 3) {
                                if (!$("textarea[name=analysis-question-"+resultDetailCss[i].question_id+"]").val()) {
                                    $("textarea[name=analysis-question-"+resultDetailCss[i].question_id+"]").css({border: '1px solid red'});
                                    $("textarea[name=analysis-question-"+resultDetailCss[i].question_id+"]").focus();
                                    $("textarea[name=analysis-question-"+resultDetailCss[i].question_id+"]").nextAll('.analysis-error').removeClass('hidden');
                                }
                            }
                        }
                    }
                }
            },
        })
    });

    $('.btn-cancel-css-result').click(function() {
        var url = $(this).data('url');
        var token = $(this).data('token');
        var cssResultId = $(this).data('cssresultid');

        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {
              _token: token,
              cssResultId: cssResultId,
            },
            success: function(response) {
                if (response.success == 1) {
                    RKExternal.notify(response.message);
                    location.reload();
                } else {
                    RKExternal.notify(response.message);
                }
            },
        })
    });

    $('.approve-status').click(function() {
        var cssId = $('.container-question').data('cssid');
        var cssResultId = $('.analysis').data('cssresultid');
        var status = $(this).data('value');
        var url = $(this).data('url');
        var token = $('.container-question').data('token');
        $('#approve-status').prop('disabled', true);

        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {
                _token: token,
                cssId: cssId,
                statusCss: status,
                cssResultId: cssResultId,
            },
            success: function(response) {
                if (response.success == 1) {
                    $('#approve-status').prop('disabled', false);
                    RKExternal.notify(response.message);
                    $('#status-css').addClass('btn-success');
                    $('#status-css').removeClass('btn-primary');
                    $('#status-css').removeClass('btn-danger');
                    $('#status-css').text("Approved");
                    location.reload();
                } else {
                    $('#approve-status').prop('disabled', false);
                    RKExternal.notify(response.message, false);
                }
            },
        })
    });

    $('.review-status').click(function() {
        var cssId = $('.container-question').data('cssid');
        var cssResultId = $('.analysis').data('cssresultid');
        var status = $(this).data('value');
        var url = $(this).data('url');
        var token = $('.container-question').data('token');
        $('#review-status').prop('disabled', true);

        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {
                _token: token,
                cssId: cssId,
                statusCss: status,
                cssResultId: cssResultId,
            },
            success: function(response) {
                $('#review-status').prop('disabled', false);
                if (response.success == 1) {
                    RKExternal.notify(response.message);
                    $('#status-css').addClass('btn-success');
                    $('#status-css').removeClass('btn-primary');
                    $('#status-css').removeClass('btn-danger');
                    $('#status-css').text("Reviewed");
                    location.reload();
                } else {
                    $('#review-status').prop('disabled', false);
                    RKExternal.notify(response.message, false);
                }
            },
        })
    });

    $('.feedback-status').click(function() {
        $('#modal-confirm-feedback-css').modal('show');
    });

    $('#comment-analysis-css').click(function() {
        var cssId = $('.container-question').data('cssid');
        var status = $('.feedback-status').data('value');
        var cssResultId = $('.analysis').data('cssresultid');
        var url = $('.feedback-status').data('url');
        var token = $('.container-question').data('token');
        var content = $('#content-css').val();
        $('#comment-analysis-css').prop('disabled', true);
        if (content == '') {
            $('#content-css').css('border', '1px solid red');
            $('.comment-css-error').removeClass('hidden');
        } else {
            $('#content-css').css('border', '1px solid #ccc');
            $('.comment-css-error').addClass('hidden');
        }

        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {
                _token: token,
                cssId: cssId,
                statusCss: status,
                content: content,
                cssResultId: cssResultId,
            },
            success: function(response) {
                if (response.success == 1) {
                    $('#comment-analysis-css').prop('disabled', false);
                    RKExternal.notify(response.message);
                    location.reload();
                }
            },
        })
    });
});
