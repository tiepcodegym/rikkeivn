function initSameHeight(){setSameHeight(".home-latest-news-content",".home-latest-news-item",mdMin),setSameHeight(".home-top-articles-content",".home-top-articles-item")}function initPostRelated(){$("#jsPostRelated").slick({dots:!0,infinite:!1,centerPadding:"50px",speed:300,arrows:!0,prevArrow:'<button class=\'custom-slick-prev custom-slick-arrow\'><i class="fa fa-chevron-left" aria-hidden="true"></i></button>',nextArrow:'<button class=\'custom-slick-next custom-slick-arrow\'><i class="fa fa-chevron-right" aria-hidden="true"></i></button>',slidesToShow:3,slidesToScroll:3,responsive:[{breakpoint:1024,settings:{slidesToShow:3,slidesToScroll:3,infinite:!1,dots:!0}},{breakpoint:991,settings:{slidesToShow:2,slidesToScroll:2}},{breakpoint:480,settings:{slidesToShow:1,slidesToScroll:1}}]})}function init(){initSameHeight(),initFeedbackForm(),initVideoThumbnail("youtube-player-detail",SIZE_LARGE,IS_POST_LIST_FALSE),initVideoThumbnail("youtube-player-list",SIZE_SMAll,IS_POST_LIST_TRUE),initPostRelated()}$(document).ready(function(){$("#demo1").emojioneArea({container:"#container-content",hideSource:!1}),$(".reply-text").emojioneArea({container:"#message-reply",hideSource:!1})}),$(window).resize(function(){initSameHeight()}),init();