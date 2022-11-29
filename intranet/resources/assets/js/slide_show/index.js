var RKVarSlideShow = {
    timeNextSlide: 10000
};

jQuery(document).ready(function($) {
    if (!isPreview) {
        loadData(secondPlaySlide);
    }
    rkInitSlideSwipper();
    initWrapper(true);
});

function loadData(secondPlaySlide) {
    setTimeout(function(){
        loadSlide();
    }, secondPlaySlide * 1000);
}

function loadSlide() {
    token = siteConfigGlobal.token;
    widthScreen = screen.width;
    heightScreen  = screen.height;
    data = {
        _token: token,
        width_screen : widthScreen,
        height_screen : heightScreen
    };
    url = urlLoadSlideAjax;
    $.ajax({
        type: 'get',
        url: url,
        dataType: 'json',
        data:data,
        success: function ($data) {
            $('.content-slides').html($data.content);
            secondPlaySlide = $data.secondPlaySlide;
            $('html').find('title').html($data.title);
            $('html').find([name="description"]).html($data.description);
            rkInitSlideSwipper();
            loadData(secondPlaySlide);
            initWrapper();
        }
    });
}

/*var fnInitMaximage = function() {
    if($('#wowslider-container1').length) {
        var countSlide;
        arrayCaptionEffect = ['fade', 'slide', 'parallax', 'move'];
        captionEffect = arrayCaptionEffect[Math.floor(Math.random()*arrayCaptionEffect.length)];
        jQuery("#wowslider-container1").wowSlider( {
            effect: "turn,shift,louvers,cube_over,tv,lines,bubbles,dribbles,parallax,brick,collage, cube,book,rotate,slices,blast,basic,basic_linear,fade,fly,flip,page,stack,stack_vertical",
            prev: "", 
            next: "", 
            duration: 30*100, 
            delay: 30*100, 
            width: 640, 
            height: 360, 
            autoPlay: true, 
            autoPlayVideo: false, 
            playPause: true, 
            stopOnHover: false, 
            loop: false, 
            bullets: 1, 
            caption: true, 
            controls: true, 
            controlsThumb: false, 
            responsive: 3, 
            fullScreen: false, 
            gestures: 2, 
            captionEffect: captionEffect,
            images: 0,
            onBeforeStep: function(curIdx,count){
                $('.ws_images').css({
                    display: 'block'
                });
                $('.loader').css({
                    display: 'none'
                });
                return curIdx+1; // next slide
            }
        });
    }
};*/

function resizeImage($img, width, height) {
    widthScreen = $(window).width();
    heightScreen  = $(window).height();
    if (widthScreen >= width) {
        if (heightScreen >= height) {
            $img.css({
                width: 'auto'
            });
            $img.css('width', 'auto');
            margin_top = (heightScreen - height) / 2;
            $img.css('margin-top', margin_top+'px');
            $img.parent('li').css({
                textAlign: 'center'
            });
            $img.parent('div').css({
                textAlign: 'center'
            });

            margin_left = (widthScreen - width) / 2;
            $('.ws-title-wrapper .ws-title').css({
                marginLeft: margin_left + 'px',
                width: width
            });
            $('#wowslider-container1').css({
                textAlign: 'left'
            });
        } else {
            $widthNew = width /(height / heightScreen);
            $img.css({
                width: $widthNew,
                height: heightScreen,
                marginTop: '0px'
            });
            $img.parent('li').css({
                textAlign: 'center'
            });
            $img.parent('div').css({
                textAlign: 'center'
            });
            margin_left = (widthScreen - width) / 2;
            $('.ws-title-wrapper .ws-title').css({
                marginLeft: margin_left + 'px',
                width: width
            });
            $('#wowslider-container1').css({
                textAlign: 'left'
            });
        }
    } else {
        if (heightScreen >= height) {
            $heightNew = height /(width / widthScreen);
            $marginTop = (heightScreen - $heightNew)/2
            $img.css({
                height: $heightNew,
                width: widthScreen,
                marginTop: $marginTop + 'px'
            });
        } else {
            $widthNew = width /(height / heightScreen);
            $img.css({
                height: heightScreen,
                width: $widthNew,
                marginTop: '0px'
            });
            $img.parent('li').css({
                textAlign: 'center'
            });
            $img.parent('div').css({
                textAlign: 'center'
            });
            margin_left = (widthScreen - width) / 2;
            $('.ws-title-wrapper .ws-title').css({
                marginLeft: margin_left + 'px',
                width: width
            });
            $('#wowslider-container1').css({
                textAlign: 'left'
            });
        }
    }
}

/**
 * effect for slide text
 */
function rkSwiperEffectText(s) {
    var sliders = s.slides,
        widthSlide = s.width,
        activeIndex = s.activeIndex,
        prevIndex = s.previousIndex,
        slideActive = sliders[activeIndex],
        prevActive = sliders[prevIndex],
        nextActive = sliders[activeIndex+1];
    sliders.find('.slide-desc').removeAttr('style');
    $(slideActive).find('.slide-desc').css({
        'animation': 'slide-right-to-left 15s'
    });
}

/**
 * init slider swiper
 */
function rkInitSlideSwipper() {
    if (!$('.swiper-container').length) {
        return true;
    }
    if (typeof effectSwiper == 'undefined' || !effectSwiper) {
        effectSwiper = 'slide';
    }
    var slideAutoHeight = $('.swiper-container').attr('data-slide-autoHeight');
    if (typeof slideAutoHeight == 'undefined' || !slideAutoHeight) {
        slideAutoHeight = false;
    } else {
        slideAutoHeight = true;
    }
    var swipper = new Swiper('.swiper-container', {
        paginationClickable: true,
        autoplay: RKVarSlideShow.timeNextSlide,
        loop: true,
        parallax: true,
        autoHeight: slideAutoHeight,
        onInit: function() {
            $('.content-slides .loader').hide();
            $('.birthday').show();
        }
    });
}

function getImageForSlider($slideId) {
    token = siteConfigGlobal.token;
    widthScreen = screen.width;
    heightScreen  = screen.height;
    url = urlGetFileForSlider;
    data = {
        _token: token,
        slide_id: $slideId,
        width_screen : widthScreen,
        height_screen : heightScreen
    };
    $.ajax({
        type: 'post',
        url: url,
        dataType: 'json',
        data:data,
        success: function ($data) {
            $('#wowslider-container1 .swiper-wrapper').html($data.content);
            rkInitSlideSwipper();
        }
    });
}

function initWrapper(isShowCountdow) {
    if(typeof isShowCountdow != "undefined") {
        if ($('.clock').length) {
            second = $('.div-clock').attr('data-second');
            $('.clock').FlipClock(second, {
                clockFace: 'DailyCounter',
                countdown: true,
                stop: function() {
                    $('.div-clock').remove();
                    if (!$('.is-show-message').length) {
                        $('.message-birthday-hide').show();
                    }
                },
            });
        }
    } else {
        dataIsShow = $('.content-slide').attr('data-is-show-countdown');
        if (dataIsShow) {
            $('.birthday').show();
        } else {
            $('.birthday').hide();
        }
    }
    
    /**
     * fix position for login block - margin height
     */
    function fixPositionLoginBlock()
    {
        windowHeight = $(window).height();
        loginHeight = $('.login-wrapper').height();
        placeHeight = windowHeight / 2 - loginHeight / 2;
        $('.login-wrapper').css('margin-top', placeHeight-80 + 'px');
    }

    /**
     * fix position for welcome block - margin height
     */
    function fixPositionWelcomeBlock()
    {
        if ($('.slide-preview-quotations').length) {
            $('.slide-preview-quotations').removeAttr('style');
            $('.welcome-wrapper').removeAttr('style');
        }
        windowHeight = $(window).height();
        welcomeHeight = $('.welcome-wrapper').height();
        logoHeight = $('.logo-custom').height();
        footerContent = $('.footer-content').height();

        heightNew = windowHeight - logoHeight - footerContent - 0.01*windowHeight;             
        placeHeight = (heightNew) / 2 - welcomeHeight / 2 - 0.05*windowHeight;
        if (welcomeHeight < heightNew) {
            if (placeHeight > 0 && !$('.slide-preview-quotations').length) {
                $('.welcome-wrapper').css('margin-top', placeHeight + 'px');
            } else {
                $('.welcome-wrapper').removeAttr('style');
            }
            if ($('.welcome-wrapper').length) {
                minHeight = windowHeight - footerContent;
                $('.container-fluid').css('minHeight', minHeight + 'px');
            }
        }
        rkFixCenterQuotationItem();
    }

    fixPositionLoginBlock();
    fixPositionWelcomeBlock();
    $(window).resize(function (event) {
        fixPositionLoginBlock();
        fixPositionWelcomeBlock();
    });
}

/**
 * resize hight quotatio
 */
function rKResizeHeighQuotation() {
    if (!$('.slide-preview-quotations').length) {
        $('.slide-preview-quotations').removeAttr('style');
        return false;
    }
    $('.slide-preview-quotations').removeAttr('style');
    var heightWidow = $(window).height(),
        marginTopMain = $('.slide-preview-quotations').offset().top,
        heightFooter = $('.footer-welcome footer-content').height() + 70,
        heightMain = heightWidow - marginTopMain - heightFooter,
        heightQuotationItems = 0;
    $('.slide-preview-quotations').height(heightMain);
    if (heightQuotationItems > heightMain) {
        return true;
    }
}

/**
 * 
 * fix quote item center screen
 */
function rkFixCenterQuotationItem() {
    var heightHeader = $('.logo-custom').height(),
        heightFooter = $('.footer-welcome footer-content').height() + 110,
        heightWidow = $(window).height(),
        heightBody = heightWidow - heightHeader - heightFooter;
    $('.slide-preview-quotations .spq-item').each(function() {
        var __this = $(this),
            widthItem = __this.width(),
            widthContent = __this.find('.spqi-content .spqic-inner').width(),
            marginLeftContent = (widthItem -widthContent) / 2,
            widthAuthor = __this.find('.spqi-author .spqia-inner').width(),
            marginRightAuthor = 0;
        __this.find('.spqi-content').css('margin-left', marginLeftContent+'px');
        marginRightAuthor = marginLeftContent - widthAuthor / 2;
        if (marginRightAuthor > 0) {
            __this.find('.spqi-author .spqia-inner').css('margin-right', marginRightAuthor+'px');
        } else {
            __this.find('.spqi-author .spqia-inner').removeAttr('style');
        }
        
        // fix height center
        __this.removeAttr('style');
        var heightSlideItemInner = __this.height(),
            marginTopSlideItemInner = __this.offset().top;
        if (heightSlideItemInner > heightBody) {
            return true;
        }
        __this.css('margin-top', ((heightBody - heightSlideItemInner) / 2)+'px');
    });
}
