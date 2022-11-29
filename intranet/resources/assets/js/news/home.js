// Carousel Auto-Cycle
$(document).ready(function() {
    $('#rikkeiCarousel').carousel({
      interval: 3000
    })
  });


function initYume() {
  $('#yume').slick({
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

function initDontMissPost() {
  $('#jsPostRelated').slick({
    dots: true,
    infinite: false,
    centerPadding: '50px',
    speed: 300,
    slidesToShow: 3,
    slidesToScroll: 3,
    prevArrow: "<button class='custom-slick-prev custom-slick-arrow'><i class=\"fa fa-chevron-left\" aria-hidden=\"true\"></i></button>",
    nextArrow: "<button class='custom-slick-next custom-slick-arrow'><i class=\"fa fa-chevron-right\" aria-hidden=\"true\"></i></button>",
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

function initSameHeight() {
  // setSameHeight('.home-latest-news-content', '.home-latest-news-item', mdMin);
  // setSameHeight('.home-top-articles-content', '.home-top-articles-item');
  // setSameHeight('.missing-articles-content', '.missing-articles-item');
  setSameHeight('.missing-articles-content', '.missing-articles-item-title');
  // setSameHeight('.should-read-articles-content', '.should-read-articles-item--large .should-read-articles-item-title');
  // setSameHeight('.should-read-articles-content', '.should-read-articles-item--large');
  // setSameHeight('.should-read-articles-content', '.should-read-articles-item--small-row');
}

$(window).resize(function () {
  //latest news
  initSameHeight();
});

function init() {
  initFeedbackForm();
  initYume();
  initSameHeight();
  initDontMissPost();
  initVideoThumbnail('youtube-player-list', SIZE_SMAll ,IS_POST_LIST_TRUE);
}

init();