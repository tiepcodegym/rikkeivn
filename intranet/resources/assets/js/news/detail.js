$(document).ready(function() {
    $("#demo1").emojioneArea({
      container: "#container-content",
      hideSource: false,
    });

    $(".reply-text").emojioneArea({
      container: "#message-reply",
      hideSource: false,
    });
});

function initSameHeight() {
  setSameHeight('.home-latest-news-content', '.home-latest-news-item', mdMin);
  setSameHeight('.home-top-articles-content', '.home-top-articles-item');

}

function initPostRelated() {
  $('#jsPostRelated').slick({
        dots: true,
        infinite: false,
        centerPadding: '50px',
        speed: 300,
        arrows: true,
        prevArrow: "<button class='custom-slick-prev custom-slick-arrow'><i class=\"fa fa-chevron-left\" aria-hidden=\"true\"></i></button>",
        nextArrow: "<button class='custom-slick-next custom-slick-arrow'><i class=\"fa fa-chevron-right\" aria-hidden=\"true\"></i></button>",
        slidesToShow: 3,
        slidesToScroll: 3,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: false,
                    dots: true
                }
            },
            {
                breakpoint: 991,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object
        ]
    });
}

$(window).resize(function () {
  //latest news  
  initSameHeight();
});

function init() {
  initSameHeight();
  initFeedbackForm();
  initVideoThumbnail('youtube-player-detail', SIZE_LARGE,IS_POST_LIST_FALSE);
  initVideoThumbnail('youtube-player-list', SIZE_SMAll ,IS_POST_LIST_TRUE);
  initPostRelated();
}

init();