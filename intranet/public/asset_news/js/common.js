function setSameHeight(e,t,i){i="undefined"!=typeof i?i:0,$(e).each(function(){if($(t,this).css("height",""),!($(window).width()<i)){var e=0;$(t,this).each(function(){$(this).height()>e&&(e=$(this).height())}),$(t,this).height(e)}})}function initFeedbackForm(){function e(){a.style.height="auto",a.style.height=a.scrollHeight+"px"}function t(){window.setTimeout(e,0)}function i(){var e=document.getElementById("js-feedback-btn-submit");a.value?(e.disabled=!1,e.classList.add("active"),a.classList.add("active")):(e.disabled=!0,e.classList.remove("active"),a.classList.remove("active"))}var a=document.getElementById("js-feedback");observe(a,"change",e),observe(a,"keyup",i),observe(a,"cut",t),observe(a,"paste",t),observe(a,"drop",t),observe(a,"keydown",t),e()}function setCategoryHeaderMarginTop(){var e=$("#main-heaer-top").height();$(".nav-news-categories").css("margin-top",e+"px")}function initVideoThumbnail(e,t,i){var a,n,o=document.getElementsByClassName(e);if(i)for($(".home-videos__content__right_item").click(setIframeFromPostList),n=0;n<o.length;n++)o[n].dataset.id&&(a=document.createElement("div"),a.setAttribute("data-id",o[n].dataset.id),a.innerHTML=displayThumbnail(o[n].dataset.id,t),o[n].appendChild(a));else for(n=0;n<o.length;n++)o[n].dataset.id&&(a=document.createElement("div"),a.setAttribute("data-id",o[n].dataset.id),a.innerHTML=displayThumbnailPostDetail(o[n].dataset.id,t),a.onclick=setIframeFromPostDetail,o[n].appendChild(a))}function displayThumbnailPostDetail(e,t){var i=displayThumbnail(e,t),a='<div class="play"></div>';return i+a}function displayThumbnail(e,t){var i='<img src="https://i.ytimg.com/vi/ID/'+MAPPING_SIZE[t]+'">';return i.replace("ID",e)}function setIframeFromPostDetail(){setIframe(this,this)}function setIframeFromPostList(){var e=document.getElementById("js-primary-video");setIframe(this,e),$(".video-active").removeClass("video-active"),this.classList.add("video-active")}function setIframe(e,t){var i=document.createElement("iframe"),a="https://www.youtube.com/embed/ID?autoplay=1&modestbranding=1&iv_load_policy=3&rel=0&showinfo=0&mute=1&enablejsapi=1";i.setAttribute("src",a.replace("ID",e.dataset.id)),i.setAttribute("frameborder","0"),i.setAttribute("allowfullscreen","1"),i.setAttribute("enablejsapi","1"),i.setAttribute("allow","autoplay; encrypted-media"),i.setAttribute("id","js-primary-video"),t.parentNode.replaceChild(i,t)}function onPlayerStateChange(e){e.data===YT.PlayerState.ENDED&&($(".video-active").next().length?$(".video-active").next().trigger("click"):$(".home-videos__content__right_item").trigger("click"),done=!0)}function onPlayerReady(e){isFirstVideoReady=!0}var mdMin=992,smMin=768,isFirstVideoPlaying=!1,isFirstVideoReady=!1,ytPlayer=null,YT=void 0,done=0;const IS_POST_LIST_TRUE=!0,IS_POST_LIST_FALSE=!1,SIZE_SMAll="small",SIZE_LARGE="large",MAPPING_SIZE={small:"mqdefault.jpg",large:"maxresdefault.jpg"};$.ajaxSetup({headers:{"X-CSRF-TOKEN":globalToken}}),$(".dropdown").on("show.bs.dropdown",function(){$(this).find(".dropdown-menu").first().stop(!0,!0).slideDown(),$(".input-search").focus(),$(".input-search").select()}),$(".dropdown").on("hide.bs.dropdown",function(){$(this).find(".dropdown-menu").first().stop(!0,!0).slideUp()});var observe;observe=window.attachEvent?function(e,t,i){e.attachEvent("on"+t,i)}:function(e,t,i){e.addEventListener(t,i,!1)},$(window).resize(function(){setCategoryHeaderMarginTop()}),$(".feedback-btn-submit").click(function(e){e.preventDefault();var t=$("#js-feedback").val();$.ajax({url:globalFeedbacksubmitURL,type:"post",data:{_token:globalToken,content:t,employee_id:globalEmployeeId},success:function(e){e.status&&($("#feedbackModal").modal(),$("#js-feedback").val(null),$(".feedback-btn-submit").disabled=!0,$(".feedback-btn-submit").removeClass("active"),initFeedbackForm())}})});const urlParams=new URLSearchParams(window.location.search);var paramSearch=urlParams.get("search");$(".input-search").val(paramSearch),$.fn.isInViewport=function(){if($(this).length){var e=$(this).offset().top,t=e+$(this).outerHeight(),i=$(window).scrollTop(),a=i+$(window).height();return t>i&&e<a}return!1},$(document).ready(function(){function e(){$(".home-videos").isInViewport()&&(isFirstVideoPlaying||($(".home-videos__content__right_item").first().trigger("click"),isFirstVideoPlaying=!0),"undefined"==typeof YT||ytPlayer||(ytPlayer=new YT.Player("js-primary-video",{events:{onStateChange:onPlayerStateChange,onReady:onPlayerReady}})))}function t(){if("undefined"==typeof YT||"undefined"==typeof YT.Player){var e=document.createElement("script");e.src="https://www.youtube.com/iframe_api";var t=document.getElementsByTagName("script")[0];t.parentNode.insertBefore(e,t)}}$(function(){t(),e()}),$(window).on("resize scroll",function(){e(),ytPlayer&&isFirstVideoReady&&$(".youtube-player iframe").isInViewport()&&0==done&&(ytPlayer.playVideo(),done=1)})});