function getYoutubeId() {
    var youtubeId = regexGetYoutubeId($('input[name="post[youtube_link]"]').val());
    $('#js-youtube-id').val(youtubeId);
}

$('#btn-preview').click(function(event) {
    event.preventDefault();
    var formEdit = $('#form-post-edit');
    var title= formEdit.find('input[name="post[title]"]').val().trim();
    var devPreview = $('.post-detail');
    devPreview.find('.preview-title').text(title);
    var contentWrapper = devPreview.find('.preview-content');
    if (+$('#is_video').val() === 0) {
        var content = CKEDITOR.instances.description.getData();
        contentWrapper.html(content);
    } else if (+$('#is_video').val() === 2) {
        var html = '<div class="center"><audio src="" controls="controls"></audio></div>';
        contentWrapper.html(html);
    } else {
        contentWrapper.addClass('youtube-player');
        div = document.createElement("div");
        var youtubeId = $('#js-youtube-id').val();
        div.setAttribute("data-id", youtubeId);
        div.innerHTML = noThumb(youtubeId);
        div.onclick = noIframe;
        contentWrapper.html('');
        contentWrapper.append(div);
    }
    $('.post-detail').removeClass('hidden');
    $('#form-post-edit').addClass('hidden');
});

$('.btn-back').click(function() {
        $('.preview-title').removeClass('youtube-player');
        $('.post-detail').addClass('hidden');
        $('#form-post-edit').removeClass('hidden');
});


function noThumb(id) {
    var thumb = '<img src="https://i.ytimg.com/vi/ID/maxresdefault.jpg">',
        play = '<div class="play"></div>';
    return thumb.replace("ID", id) + play;
}

function noIframe() {
    var iframe = document.createElement("iframe");
    var embed =
        "https://www.youtube.com/embed/ID?autoplay=1&modestbranding=1&iv_load_policy=3&rel=0&showinfo=0";
    iframe.setAttribute("src", embed.replace("ID", this.dataset.id));
    iframe.setAttribute("frameborder", "0");
    iframe.setAttribute("allowfullscreen", "1");
    iframe.setAttribute("allow", "autoplay; encrypted-media");
    this.parentNode.replaceChild(iframe, this);
}

function regexGetYoutubeId(url) {
    if (url) {
        var reg = /^.*(youtu.be\/|v\/|embed\/|watch\?|youtube.com\/user\/[^#]*#([^\/]*?\/)*)\??v?=?([^#\&\?]*).*/i;
        var groupsMatch = url.match(reg);
        if (groupsMatch && groupsMatch[3] !== undefined) {
            return groupsMatch[3];
        }
    }

    return url;
}

function setNewIconPos() {
    $('.thumbnail').each(function () {
        var self = $(this);
        var imgThumb = self.find('img.portrait');
        var newIcon = self.find('img.new-icon');
        newIcon.css({
            'left': self.width() - 17 - (self.width() - imgThumb.width()) / 2,
            'right': 'unset',
        });
    });
}

function guid() {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
    }
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
            s4() + '-' + s4() + s4() + s4();
}

$('#btn-render').click(function (event) {
    event.preventDefault();
    var render = guid();
    $('#render').val(render);
    $('#render-link').val($('#link-render').val() + '/' + render);
});

$('#btn-remove-render').click(function (event) {
    event.preventDefault();
    $('#render').val('');
    $('#render-link').val('');
});

$(window).resize(function () {
    if ($(window).width() >= 768 && $(window).width() <= 991) {
        $('.post-author span').each(function () {
            $(this).text('- ' + $(this).attr('real-auth') + ' -');
        });
    } else if ($(window).width() == 1024 || $(window).width() == 320 || $(window).width() == 375) {
        if ($(window).width() == 375) {
            $('.post-author span').each(function () {
                $(this).text('- ' + $(this).attr('short-auth-375') + ' -');
            });
        } else {
            $('.post-author span').each(function () {
                $(this).text('- ' + $(this).attr('short-auth-1024') + ' -');
            });
        }
        $('.post-author').each(function () {
            $(this).removeClass("col-xs-8");
            $(this).addClass("col-xs-7");
        });
        $('.post-read-more').each(function () {
            $(this).removeClass("col-xs-4");
            $(this).addClass("col-xs-5");
        });
    } else {
        $('.post-author span').each(function () {
            $(this).text('- ' + $(this).attr('short-auth') + ' -');
        });
        $('.post-author').each(function () {
            $(this).removeClass("col-xs-7");
            $(this).addClass("col-xs-8");
        });
        $('.post-read-more').each(function () {
            $(this).removeClass("col-xs-5");
            $(this).addClass("col-xs-4");
        });
    }
}).resize();


/**
 * Confirm publish news
 */
$('.btn-publish-news').on('click', function () {
    $('#modal-publish-news').modal('show');
});


/**
 * Publish news
 */
$('.btn-publish-ok').on('click', function () {
    $('#modal-publish-news').modal('hide');
    CKEDITOR.instances['description'].updateElement();
    var data = $('#form-post-edit').serialize();
    $.ajax({
        method: 'POST',
        url: urlPublishNews,
        data: data,
        dataType: 'JSON',
        success: function (res) {
            bootbox.alert({
                message: res['message'],
                backdrop: true,
                className: 'modal-default',
            });
            $('.btn-publish-news').text('Published').prop('disabled', true);
        },
        error: function () {
            bootbox.alert({
                message: 'Error system',
                backdrop: true,
                className: 'modal-default',
            });
        },
    });
});

$('.btn-publish-recruitment').on('click', function () {
    $('#modal-publish-news').modal('hide');
    CKEDITOR.instances['description'].updateElement();
    var data = $('#form-post-edit').serialize();
    $.ajax({
        method: 'POST',
        url: urlPublishNewsRecruitment,
        data: data,
        dataType: 'JSON',
        success: function (res) {
            bootbox.alert({
                message: res['message'],
                backdrop: true,
                className: 'modal-default',
            });
            // $('.btn-publish-news').text('Published').prop('disabled', true);
        },
        error: function () {
            bootbox.alert({
                message: 'Error system',
                backdrop: true,
                className: 'modal-default',
            });
        },
    });
});

/**
 * Update webvn when save news intranet
 */

// $('.btn-save-news').on('click', function () {
//     var checkPublish = $(this).attr('data-published');
//     CKEDITOR.instances['description'].updateElement();
//     var data = $('#form-post-edit').serialize();
//     if (parseInt(checkPublish) === statusPublished) {
//         $.ajax({
//             method: 'POST',
//             url: urlPublishNews,
//             data: data,
//         });
//     }
// });

// $(window).scroll(function(){left
//     if ($("#main-content").length != 0) {
//         var offsetDocumentBottom = $(document).height() - $('#main-content').offset().top - $('#main-content').height();
//         var offsetMostReadBottom = $(document).height() - $('#wrap').offset().top - $('#wrap').height();
//         var offsetMostReadTopScreen = document.getElementById('wrap').getBoundingClientRect().top;
//
//         if ($(window).scrollTop() >= 300) {
//             if ($('.sidebar-right').hasClass('header-absolute')) {
//                 $('.sidebar-right').removeClass('header-absolute');
//                 $('.sidebar-right').addClass('fix-div');
//                 var offsetLeftMostRead = $('.blog-search').offset().left;
//                 $('.sidebar-right').css('left', offsetLeftMostRead + 'px');
//             }
//             else if (offsetMostReadBottom <= offsetDocumentBottom && $('.sidebar-right').hasClass('fix-div')) {
//                 $('.sidebar-right').removeClass('header-absolute');
//                 $('.sidebar-right').removeClass('fix-div');
//                 $('.sidebar-right').addClass('footer-absolute');
//                 var offsetLeftMostRead = $('.blog-search').offset().left - $('#main-content').offset().left;
//                 $('.sidebar-right').css('left',offsetLeftMostRead + 'px');
//             }
//             else if (offsetMostReadTopScreen >= 35 && $('.sidebar-right').hasClass('footer-absolute') ) {
//                 $('.sidebar-right').removeClass('footer-absolute');
//                 $('.sidebar-right').addClass('fix-div');
//                 var offsetLeftMostRead = $('.blog-search').offset().left;
//                 $('.sidebar-right').css('left', offsetLeftMostRead + 'px');
//             }
//         }
//         else {
//             $('.sidebar-right').removeClass('fix-div');
//             $('.sidebar-right').addClass('header-absolute');
//             $('.sidebar-right').removeClass('footer-absolute');
//             var offsetLeftMostRead = $('.blog-search').offset().left - $('#main-content').offset().left;
//             $('.sidebar-right').css('left',offsetLeftMostRead + 'px');
//         }
//     }
// });


(function ($) {
var globPass = typeof varGlobPass === 'object' ? varGlobPass : {};

var modulePost = {
    css: {
        like: 'thumb-like',
        dislike: 'thumb-dislike',
    },
    init: function () {
        var that = this;
        // like action
        $('[data-post-btn="like"]').click(function (e) {
            that.like($(this));
        });
        $('[data-count-like]').click(function (e) {
            e.preventDefault();
            that.showLike($(this), 'like');
        });
        $('[data-count-like_cmt]').click(function (e) {
            e.preventDefault();
            that.showLike($(this), 'like_cmt');
        });
        that.initDom();
        that.getAllCount();
        that.sameHeightPost();
    },
    getAllCount: function () {
        var that = this,
            ids = [];
        $('[data-post-id]').each(function (i, v) {
            var id = $(v).data('post-id');
            if (ids.indexOf(id) === -1) {
                ids.push(id);
            }
        });
        if (!ids.length) {
            return true;
        }
        ids = ids.join('-');
        $.ajax({
            type: 'get',
            url: globPass.urlGetAllCount,
            data: {
                ids: ids,
            },
            success: function (response) {
                if (!response.status) {
                    return true;
                }
                that.showCount(response);
                if (response.like) {
                    that.showActiveLike(response.like);
                }
                // $('.sidebar-right').css('display','block');
            },
        });
    },
    /**
    * Event click like/unlike post/comment
    * @param {dom} elem
    * @param {int} postId
    * @param {int} type
    */
    like: function (btnLike) {
        var that = this;
        if (btnLike.data('process')) {
            return true;
        }
        var postId = btnLike.data('item-id');
        if (!postId) {
            return true;
        }
        btnLike.prop("disabled", true);
        btnLike.data("process", 1);
        $.ajax({
            type: "POST",
            url: globPass.urlActionLike,
            data: {
                post_id: postId,
                type: btnLike.data('like-type'),
                _token: siteConfigGlobal.token
            },
            success: function (response) {
                if (!response.status) {
                    return true;
                }
                that.showCount(response);;
                if (response.like) {
                    that.showActiveLike(response.like, 'like');
                }
                if (response.like_cmt) {
                    that.showActiveLike(response.like_cmt, 'like_cmt');
                }
            },
            complete: function () {
                btnLike.prop("disabled", false);
                btnLike.data("process", 0);
            },
        });
    },
    /**
     *
     * @param {object} response
     *  {
     *      like: {
     *          postId: {
     *              count: 23,
     *              like: 1|0
     *          }
     *      },
     *      view: {
     *          postId: {
     *              count: 12
     *          }
     *      }
     *  }
     * @returns {undefined}
     */
    showCount: function (response) {
        var that = this;
        $.each(response, function (key, v) {
            if (typeof v !== 'object') {
                return true;
            }
            $.each (v, function (postId, data) {
                var count, countText;
                if (typeof data === 'object') {
                    count = data.count;
                } else {
                    count = data;
                }
                if (count > 0) {
                    $('[data-count-'+key+']').closest('[data-item-closest="'+postId+'"]').removeClass('hidden');
                } else {
                    $('[data-count-'+key+']').closest('[data-item-closest="'+postId+'"]').addClass('hidden');
                }
                countText = that.minifyCount(count);
                $('[data-post-id="'+postId+'"] [data-count-'+key+']').text(countText).data('count-org', count);
                $('[data-item-id="'+postId+'"][data-count-'+key+']').text(countText).data('count-org', count);
            });
        });
        $('[data-post-id]').removeClass('hidden');
        // show load more cmt
        if (response.cmt && $('[data-post-cmt-load]').length) {
            $('[data-post-cmt-load]').each(function (i, v) {
                var postId = $(v).data('post-cmt-load');
                if (!postId) {
                    return true;
                }
                if (typeof response.cmt[postId] !== 'object' ||
                    typeof response.cmt[postId].count === 'undefined'
                ) {
                    return true;
                }
                if (response.cmt[postId].count > globPass.cmtPerpage) {
                    $(v).removeClass('hidden');
                }
            });
        }
    },
    /**
     *
     * @param {object} response
     *  {
     *      postId: {
     *          count: 23,
     *          like: 1|0
     *      },
     *  }
     */
    showActiveLike: function (response, type) {
        if (typeof response !== 'object') {
            return true;
        }
        var that = this;
        $.each (response, function (postId, data) {
            var domLike;
            if (type === 'like_cmt') {
                domLike = $('[data-item-id="'+postId+'"][data-post-icon="like_cmt"]');
            } else {
                domLike = $('[data-post-id="'+postId+'"] [data-post-icon="like"]');
            }
            if (data.like) { //like
                domLike.addClass(that.css.like);
                domLike.removeClass(that.css.dislike);
                domLike.attr('title', globPass.trans.dislike);
            } else { // dislike
                domLike.removeClass(that.css.like);
                domLike.addClass(that.css.dislike);
                domLike.attr('title', globPass.trans.like);
            }
        });
    },
    /**
     * convert number to ministring
     *
     * @param {number} count
     * @returns {String}
     */
    minifyCount: function (count) {
        if (!count || isNaN(count)) {
            return '';
        }
        count = parseInt(count);
        if (!count) {
            return '';
        }
        if (count < 1e3) {
            return count;
        }
        if (count < 1e6) {
            return Math.round(count / 1e3 * 10) / 10 + 'K';
        }
        return Math.round(count / 1e6 * 10) / 10 + 'M';
    },
    sameHeightPost: function () {
        RKfuncion.boxMatchHeight.init({
            parent: '.bl-row',
            children: ['.bci-header', '.bci-image', '.post-desc', '.bc-item'],
            center: ['.bci-image']
        });
    },
    showLike: function (btnShowLike, type) {
        var that = this, postId;
        if (type === 'like_cmt') {
            postId = btnShowLike.data('item-id');
        } else {
            postId = btnShowLike.closest('[data-post-id]').data('post-id');
        }
        if (!postId || that.proShowLike) {
            return true;
        }
        $('#showLike .modal-body [data-like-dom="list"]').html('');
        $('#showLike .modal-body [data-like-dom="no-list"]').addClass('hidden');
        that.proShowLike = 1;
        $.ajax({
            url: globPass.urlListLike,
            data: {
                post_id: postId,
                type: btnShowLike.data('like-type'),
            },
            success: function (response) {
                if (!response.status) {
                    return true;
                }
                if (!response.data || !response.data.length) {
                    $('#showLike .modal-body [data-like-dom="no-list"]').removeClass('hidden');
                    return true;
                }
                var html = '';
                $.each(response.data, function (i, item) {
                    if (i%2 === 0) {
                        html += '<div class="row">';
                    }
                    html += '<div class="col-md-6 show-like">'+
                            '<img src="'+item.avatar_url+'" class="avartar" />'+
                            '<span class="name">&nbsp;'+item.name+'</span>'+
                            '<hr style="margin-top: 10px; margin-bottom: 10px;">'+
                        '</div>';
                    if (i%2 === 1) {
                        html += '</div>';
                    }
                });
                if (response.data.length % 2 === 1) {
                    html += '</div>';
                }
                $('#showLike .modal-body [data-like-dom="list"]').html(html);
            },
            complete: function () {
                that.proShowLike = 0;
            },
        });
        $('#showLike').modal('show');
    },
    initDom: function () {
        var that = this;
        that.htmlListLike = $('[data-like-dom="list"]').html();
    }
};
modulePost.init();
})(jQuery);

$('#category_webvn').change(function (){
   $('#input_category_webvn').val($(this).val());
});

function ChangeToSlug()
{
    var title, slug;
    //Lấy text từ thẻ input title
    title = document.getElementById("title").value;
    //Đổi chữ hoa thành chữ thường
    slug = title.toLowerCase();
    //Đổi ký tự có dấu thành không dấu
    slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
    slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
    slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
    slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
    slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
    slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
    slug = slug.replace(/đ/gi, 'd');
    //Xóa các ký tự đặt biệt
    slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi, '');
    //Đổi khoảng trắng thành ký tự gạch ngang
    slug = slug.replace(/ /gi, "-");
    //Đổi nhiều ký tự gạch ngang liên tiếp thành 1 ký tự gạch ngang
    //Phòng trường hợp người nhập vào quá nhiều ký tự trắng
    slug = slug.replace(/\-\-\-\-\-/gi, '-');
    slug = slug.replace(/\-\-\-\-/gi, '-');
    slug = slug.replace(/\-\-\-/gi, '-');
    slug = slug.replace(/\-\-/gi, '-');
    //Xóa các ký tự gạch ngang ở đầu và cuối
    slug = '@' + slug + '@';
    slug = slug.replace(/\@\-|\-\@|\@/gi, '');
    //In slug ra textbox có id “slug”
    document.getElementById('slug').value = slug;
}

(function($) {
    $.fn.menumaker = function(options) {
        var cssmenu = $(this), settings = $.extend({
            format: "dropdown",
            sticky: false
        }, options);
        return this.each(function() {
            $(this).find(".button").on('click', function(){
                $(this).toggleClass('menu-opened');
                var mainmenu = $(this).next('ul');
                if (mainmenu.hasClass('open')) {
                    mainmenu.slideToggle().removeClass('open');
                }
                else {
                    mainmenu.slideToggle().addClass('open');
                    if (settings.format === "dropdown") {
                        mainmenu.find('ul').show();
                    }
                }
            });
            cssmenu.find('li ul').parent().addClass('has-sub');
            multiTg = function() {
                cssmenu.find(".has-sub").prepend('<span class="submenu-button"></span>');
                cssmenu.find('.submenu-button').on('click', function() {
                    $(this).toggleClass('submenu-opened');
                    if ($(this).siblings('ul').hasClass('open')) {
                        $(this).siblings('ul').removeClass('open').slideToggle();
                    }
                    else {
                        $(this).siblings('ul').addClass('open').slideToggle();
                    }
                });
            };
            if (settings.format === 'multitoggle') multiTg();
            else cssmenu.addClass('dropdown');
            if (settings.sticky === true) cssmenu.css('position', 'fixed');
            resizeFix = function() {
                var mediasize = 1000;
                if ($( window ).width() > mediasize) {
                    cssmenu.find('ul').show();
                }
                if ($(window).width() <= mediasize) {
                    cssmenu.find('ul').hide().removeClass('open');
                }
            };
            resizeFix();
            return $(window).on('resize', resizeFix);
        });
    };
})(jQuery);

(function($){
    $(document).ready(function(){
        $("#cssmenu").menumaker({
            format: "multitoggle"
        });
    });
})(jQuery);

$(function() {
    $.each($('.cut_desc_news'), function(index, item) {
        setCutString(this, 68);
    })

    $.each($('.cut_title_news'), function(index, item) {
        setCutString(this, 55);
    })

    $.each($('.cut_top_title_news'), function(index, item) {
        setCutString(this, 45);
    })

    function setCutString(str, _height) {
        var w = $(window).width();
        if (w > 768) {
            var h = $(str).height();
            while (h >= _height) {
                cutString(str);
                h = $(str).height();
            }
        }
    }

    function cutString(that) {
        var arr = $(that).text().split(" ");
        var str = '';
        var n = arr.length - 2;
        for (var i = 0; i < n; i++) {
            str += ' ' + arr[i];
        }
        str = $.trim(str) + ' ...';
        $(that).text(str);
    }
})