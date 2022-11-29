var mdMin = 992;
var smMin = 768;
var isFirstVideoPlaying = false;
var isFirstVideoReady = false;
var ytPlayer = null;
var YT = undefined;
var done = 0;

const IS_POST_LIST_TRUE = true;
const IS_POST_LIST_FALSE = false;
const SIZE_SMAll = 'small';
const SIZE_LARGE = 'large';
const MAPPING_SIZE = {
  'small' : 'mqdefault.jpg',
  'large' : 'maxresdefault.jpg'
};

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': globalToken
  },
});


$('.dropdown').on('show.bs.dropdown', function () {
  $(this).find('.dropdown-menu').first().stop(true, true).slideDown();
  $('.input-search').focus();
  $('.input-search').select();
});

// Add slideUp animation to Bootstrap dropdown when collapsing.
$('.dropdown').on('hide.bs.dropdown', function () {
  $(this).find('.dropdown-menu').first().stop(true, true).slideUp();
});

//set child same height
function setSameHeight(classParent, classChild, defaultWidth) {
  defaultWidth = typeof defaultWidth !== 'undefined' ? defaultWidth : 0;
  $(classParent).each(function () {
    $(classChild, this).css('height', '');

    if ($(window).width() < defaultWidth) {
      return;
    }
    // Cache the highest
    var highestBox = 0;
    // Select and loop the elements you want to equalise
    $(classChild, this).each(function () {

      // If this box is higher than the cached highest then store it
      if ($(this).height() > highestBox) {
        highestBox = $(this).height();
      }

    });

    // Set the height of all those children to whichever was highest
    $(classChild, this).height(highestBox);

  });
}

//Feedback form action
var observe;
if (window.attachEvent) {
  observe = function (element, event, handler) {
    element.attachEvent('on' + event, handler);
  };
}
else {
  observe = function (element, event, handler) {
    element.addEventListener(event, handler, false);
  };
}

function initFeedbackForm() {
  var text = document.getElementById('js-feedback');
  function resize() {
    text.style.height = 'auto';
    text.style.height = text.scrollHeight + 'px';
  }
  /* 0-timeout to get the already changed text */
  function delayedResize() {
    window.setTimeout(resize, 0);
  }

  function triggerActive() {
    var btnSubmit = document.getElementById('js-feedback-btn-submit');
    if (text.value) {
      btnSubmit.disabled = false;
      btnSubmit.classList.add('active');
      text.classList.add('active');
    } else {
      btnSubmit.disabled = true;
      btnSubmit.classList.remove('active');
      text.classList.remove('active');
    }
  }
  observe(text, 'change', resize);
  observe(text, 'keyup', triggerActive);
  observe(text, 'cut', delayedResize);
  observe(text, 'paste', delayedResize);
  observe(text, 'drop', delayedResize);
  observe(text, 'keydown', delayedResize);

  resize();
}


function setCategoryHeaderMarginTop() {
  var headerHeight = $('#main-heaer-top').height();
  $('.nav-news-categories').css('margin-top', headerHeight + 'px');
}

$(window).resize(function () {
  setCategoryHeaderMarginTop();
});

$('.feedback-btn-submit').click(function (e) {
  e.preventDefault();
  var content = $('#js-feedback').val();

  $.ajax({
    url: globalFeedbacksubmitURL,
    type: 'post',
    data: {
      _token: globalToken,
      content : content,
      employee_id: globalEmployeeId
    },
    success: function (data) {
      if (data.status) {
        $('#feedbackModal').modal();
        $('#js-feedback').val(null);
        $('.feedback-btn-submit').disabled = true;
        $('.feedback-btn-submit').removeClass('active');
        initFeedbackForm();
      }
    },
  });
});

//Init search Post Param
const urlParams = new URLSearchParams(window.location.search);
var paramSearch = urlParams.get('search');
$('.input-search').val(paramSearch);


// Init youtube
function initVideoThumbnail(elClassName, size, isPostList) {
  var div,
      n,
      v = document.getElementsByClassName(elClassName);
  if (!isPostList) {
    for (n = 0; n < v.length; n++) {
      if (v[n].dataset.id) {
        div = document.createElement("div");
        div.setAttribute("data-id", v[n].dataset.id);
        div.innerHTML = displayThumbnailPostDetail(v[n].dataset.id, size);
        div.onclick = setIframeFromPostDetail ;
        v[n].appendChild(div);
      }
    }
  } else {
    $('.home-videos__content__right_item').click(setIframeFromPostList);
    for (n = 0; n < v.length; n++) {
      if (v[n].dataset.id) {
        div = document.createElement("div");
        div.setAttribute("data-id", v[n].dataset.id);
        div.innerHTML = displayThumbnail(v[n].dataset.id, size);
        v[n].appendChild(div);
      }
    }
  }

}

function displayThumbnailPostDetail(id, size) {
  var thumb = displayThumbnail(id, size),
      play = '<div class="play"></div>';
  return thumb + play;
}

function displayThumbnail(id, size) {
  var thumb = '<img src="https://i.ytimg.com/vi/ID/' + MAPPING_SIZE[size] + '">';

  return thumb.replace("ID", id);
}

function setIframeFromPostDetail() {
  setIframe(this, this);
}

function setIframeFromPostList() {
  var replaceNode = document.getElementById('js-primary-video');
  setIframe(this, replaceNode);
  $('.video-active').removeClass('video-active');
  this.classList.add('video-active');
}

function setIframe(currentNode, replaceNode) {
  var iframe = document.createElement("iframe");
  var embed =
      "https://www.youtube.com/embed/ID?autoplay=1&modestbranding=1&iv_load_policy=3&rel=0&showinfo=0&mute=1&enablejsapi=1";
  iframe.setAttribute("src", embed.replace("ID", currentNode.dataset.id));
  iframe.setAttribute("frameborder", "0");
  iframe.setAttribute("allowfullscreen", "1");
  iframe.setAttribute("enablejsapi", "1");
  iframe.setAttribute("allow", "autoplay; encrypted-media");
  iframe.setAttribute('id', 'js-primary-video');
  replaceNode.parentNode.replaceChild(iframe, replaceNode);
}

$.fn.isInViewport = function() {
  if ($(this).length) {
    var elementTop = $(this).offset().top;
    var elementBottom = elementTop + $(this).outerHeight();

    var viewportTop = $(window).scrollTop();
    var viewportBottom = viewportTop + $(window).height();

    return elementBottom > viewportTop && elementTop < viewportBottom;
  }

  return false;
};

$(document).ready(function () {
  function initIfVideoIsOnscreenFristLoad() {
    if ($('.home-videos').isInViewport()) {
      if (!isFirstVideoPlaying) {
        $('.home-videos__content__right_item').first().trigger('click');
        isFirstVideoPlaying = true;
      }
      if (typeof YT !== "undefined" && !ytPlayer) {
        ytPlayer = new YT.Player('js-primary-video', {
          events: {
            'onStateChange': onPlayerStateChange,
            'onReady': onPlayerReady,
          }
        });
      }
    }
  }
  function loadScript() {
    if (typeof (YT) == 'undefined' || typeof (YT.Player) == 'undefined') {
      var tag = document.createElement('script');
      tag.src = "https://www.youtube.com/iframe_api";
      var firstScriptTag = document.getElementsByTagName('script')[0];
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }
  }

  $(function () {
    loadScript();
    initIfVideoIsOnscreenFristLoad();
  });

  $(window).on('resize scroll', function () {
    initIfVideoIsOnscreenFristLoad();
    if (ytPlayer && isFirstVideoReady) {
      if ($('.youtube-player iframe').isInViewport() && (done == 0) ) {
        ytPlayer.playVideo();
        done = 1;
      }
    }
  });
});

function onPlayerStateChange(event) {
  if (event.data === YT.PlayerState.ENDED) {
    $('.video-active').next().length ?
        $('.video-active').next().trigger('click') :
        $('.home-videos__content__right_item').trigger('click');
        done = true;
  }
}

function onPlayerReady(event) {
  isFirstVideoReady = true;
}
