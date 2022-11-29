
jQuery(document).ready(function ($) {
    selectSearchReload();
    $('#form-order-music').validate({
        onkeyup: false,
        rules: {
            'music[link]': {
                required: true,
                url: true
            },
            'music[name]': {
                required: true
            },
            'music[message]': {
                required: true
            }
        },
        messages: {
            'music[link]': {
                required: requiredText,
                url: urlInvalid,
            },
            'music[name]': {
                required: requiredText,
            },
            'music[message]': {
                required: requiredText,
            },
        },
    });

    function vote(order_id) {
        var vote_url = $('#button_vote' + order_id).attr('link');
        var csrf_token = $('#csrf_token' + order_id).val();
        formData = {
            order_id: order_id,
            _token: siteConfigGlobal.token
        };
        $('#button_vote' + order_id).prop("disabled", true);
        $.ajax({
            type: "POST",
            url: vote_url,
            data: formData
        })
                .done(function (msg) {
                    if (msg["totalReal"] >= 1000) {
                        $('#countVote' + order_id).tooltip();
                        $('#countVote' + order_id).attr('data-original-title', msg["totalReal"]);
                    } else {
                        $('#countVote' + order_id).removeAttr("data-original-title");
                    }
                    if (checkvote(order_id) == false) {
                        $('#button_vote' + order_id + ' i').removeClass("thumb-dislike");
                        $('#button_vote' + order_id + ' i').addClass("thumb-like");
                        $('#button_vote' + order_id).attr('title', 'Bỏ thích');
                    } else {
                        $('#button_vote' + order_id + ' i').removeClass("thumb-like");
                        $('#button_vote' + order_id + ' i').addClass("thumb-dislike");
                        $('#button_vote' + order_id).attr('title', 'thích');
                    }
                    $('#button_vote' + order_id).prop("disabled", false);
                    $('#countVote' + order_id).text(msg["total_vote"]);
                });
    }

    function checkvote(order_id) {
        if ($('#button_vote' + order_id + ' i').hasClass('thumb-like')) {
            return true;
        } else {
            return false;
        }
    }

    $('.played-chk').iCheck({
        checkboxClass: 'icheckbox_square-red',
    });
    $('.played-chk').on('ifToggled', function (event) {
        var order_id = '';
        var played_url = $(".played-chk").attr('url-data');
        $(".played-chk:checked").each(function (index, ele) {
            order_id = $(ele).val();
        });
        formData = {
            order_id: order_id,
            _token: siteConfigGlobal.token
        };
        $.ajax({
            type: "POST",
            url: played_url,
            data: formData
        })
                .done(function (msg) {
                    if (msg['message'] = 'error') {
                        $("#order" + order_id).on('ifToggled', function (event) {
                        });

                    }
                });
    });

    var srcLeft = $('#left img').attr('src');
    $('#left').hover(
        function(){
            $('#left img').attr('src',$('#left img').attr('hov'));
        },function(){
            $('#left img').attr('src',srcLeft);
        }
    );

    var srcRight = $('#right img').attr('src');
    $('#right').hover(
        function(){
            $('#right img').attr('src',$('#right img').attr('hov'));
        },function(){
            $('#right img').attr('src',srcRight);
        }
    );

    var minHeight = 300;
    $('#left').css('left','-5px');
    $('#right').css('right','-5px');

    $('.inner-prev-next').css('position', 'absolute');

    var minWidth = 608;
    var mobileWidth = 991;

    $( window ).resize(function() {
        $('.arrow-down').css('margin-left', $('div.scrollmenu a:first-child').width()/2+1);
        $('.short-mess').each(function(){
            var numLine = Math.round($(this).find('.mess').height()/parseInt($(this).find('.mess').css('line-height'), 10));
            if(numLine>=3){
                $(this).find('.btn-link').show();
            }else {
                $(this).find('.btn-link').hide();
            }
        });
        if($(window).width()<=mobileWidth) {
            // $('.redundancy').hide();
            $('.music-list').css('width','auto');
            // $('#menu-list').hide();
            // $('.arrow-down').hide();
            $('#logo-sm').removeClass('align-center');
            // $('#logo-sm').css('height','93%');
            $('#logo-sm').addClass('align-left');
            $('#menu-sm').show();
        }else {
            $('.music-list').css('width','100%');
            // $('.redundancy').show();
            // $('#menu-list').show();
            // $('.arrow-down').show();
            $('#menu-sm').hide();
        }
        if($(window).width()<minWidth) {
            $('#left').hide();
            $('#right').hide();
            $('.prev-next').css('height', '0px');
        }else {
            $('#left').show();
            $('#right').show();
            $('.prev-next').css('height', $('.list').height());
            $('.inner-prev-next').css('position', 'absolute');
            if( $('.list').height() <= minHeight) {
                $('.inner-prev-next').css('top', '32%');
            }else {
                $('.inner-prev-next').css('top', '50%');
            }
        }

        $('.thumbnail').css('height',$('.thumbnail').width());
        $('.thumbnail a img').load(function() {
            $(this).css('margin-left', -($(this).width() - $(this).closest('.thumbnail').width())/2);
        }).each(function() {
            if(this.complete) $(this).load();
        });
    }).resize();

    $('.thumbnail').css('height',$('.thumbnail').width());
    $('.thumbnail a img').load(function() {
        $(this).css('margin-left', -($(this).width() - $(this).closest('.thumbnail').width())/2);
    }).each(function() {
        if(this.complete) $(this).load();
    });

// add jquery funtion short content
    $.fn.shortContent = function (settings) {

        var config = {
            showChars: 250,
            showLines: 2,
            ellipsesText: "...",
            moreText: 'more',
            lessText: 'less'
        };

        if (settings) {
            $.extend(config, settings);
        }

        $(document).off("click", '.morelink');

        $(document).on({click: function () {

                var $this = $(this);
                if ($this.hasClass('less')) {
                    $this.removeClass('less');
                    $this.html(config.moreText);
                } else {
                    $this.addClass('less');
                    $this.html(config.lessText);
                }
                $this.parent().prev().toggle();
                $this.prev().toggle();
                return false;
            }
        }, '.morelink');

        return this.each(function () {
            var $this = $(this);
            if ($this.hasClass("shortened"))
                return;

            $this.addClass("shortened");
            var content = $this.html();
            var moreContent = '';
            var arrLine = content.split("\n");
            var c = content, h = '';
            var hasMore = false;

            if (arrLine.length > config.showLines) {
                hasMore = true;
                content = arrLine.splice(0, config.showLines).join("\n");
                moreContent = arrLine.join("\n");
            }

            if (content.length > config.showChars) {
                hasMore = true;
                c = content.substr(0, config.showChars);
                h = content.substr(config.showChars, content.length - config.showChars) + moreContent;
            } else {
                c = content;
                h = moreContent;
            }

            if (hasMore) {
                var html = c + '<span class="moreellipses">' + config.ellipsesText + ' </span><span class="morecontent"><span>' + h + '</span> <a href="#" class="morelink">' + config.moreText + '</a></span>';
                $this.html(html);
                $(".morecontent span").hide();
            }
            $this.removeClass('hidden');
        });

    };

    $('.mess').shortContent();
});





