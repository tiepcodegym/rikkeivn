function initSameHeight() {
  setSameHeight('.home-latest-news-content', '.home-latest-news-item', mdMin);
  setSameHeight('.home-top-articles-content', '.home-top-articles-item');
  setSameHeight('.missing-articles-content', '.missing-articles-item');
  setSameHeight('.missing-articles-content', '.missing-articles-item-title');
}

$(window).resize(function () {
  //latest news  
  initSameHeight();
})

function init() {
  initSameHeight();
  initFeedbackForm();
}

init();