function loadApp(){$("#canvas").fadeIn(1e3);var e=$(".magazine");return 0==e.width()||0==e.height()?void setTimeout(loadApp,10):(e.turn({width:1060,height:750,duration:1e3,acceleration:!isChrome(),gradients:!0,autoCenter:!0,elevation:50,pages:IMAGE_COUNT,when:{turning:function(e,n,o){var t=$(this),i=t.turn("page");t.turn("pages");Hash.go("page/"+n).update(),disableControls(n),$(".thumbnails .page-"+i).parent().removeClass("current"),$(".thumbnails .page-"+n).parent().addClass("current")},turned:function(e,n,o){disableControls(n),$(this).turn("center"),1==n&&$(this).turn("peel","br"),n==IMAGE_COUNT&&$("<div />",{"class":"exit-message"}).html("<div>Last page</div>").appendTo($("body")).delay(2e3).animate({opacity:0},500,function(){$(this).remove()})},missing:function(e,n){for(var o=0;o<n.length;o++)addPage(n[o],$(this))}}}),$(".magazine-viewport").zoom({flipbook:$(".magazine"),max:function(){return largeMagazineWidth()/$(".magazine").width()},when:{swipeLeft:function(){$(this).zoom("flipbook").turn("next")},swipeRight:function(){$(this).zoom("flipbook").turn("previous")},resize:function(e,n,o,t){1==n?loadSmallPage(o,t):loadLargePage(o,t)},zoomIn:function(){$(".thumbnails").hide(),$(".made").hide(),$(".magazine").removeClass("animated").addClass("zoom-in"),$(".zoom-icon").removeClass("zoom-icon-in").addClass("zoom-icon-out"),window.escTip||$.isTouch||(escTip=!0,$("<div />",{"class":"exit-message"}).html("<div>Press ESC to exit</div>").appendTo($("body")).delay(2e3).animate({opacity:0},500,function(){$(this).remove()}))},zoomOut:function(){$(".exit-message").hide(),$(".thumbnails").fadeIn(),$(".made").fadeIn(),$(".zoom-icon").removeClass("zoom-icon-out").addClass("zoom-icon-in"),setTimeout(function(){$(".magazine").addClass("animated").removeClass("zoom-in"),resizeViewport()},0)}}}),$.isTouch?$(".magazine-viewport").bind("zoom.doubleTap",zoomTo):$(".magazine-viewport").bind("zoom.tap",zoomTo),$(document).keydown(function(e){var n=37,o=39,t=27;switch(e.keyCode){case n:$(".magazine").turn("previous"),e.preventDefault();break;case o:$(".magazine").turn("next"),e.preventDefault();break;case t:$(".magazine-viewport").zoom("zoomOut"),e.preventDefault()}}),Hash.on("^page/([0-9]*)$",{yep:function(e,n){var o=n[1];void 0!==o&&$(".magazine").turn("is")&&$(".magazine").turn("page",o)},nop:function(e){$(".magazine").turn("is")&&$(".magazine").turn("page",1)}}),$(window).resize(function(){resizeViewport()}).bind("orientationchange",function(){resizeViewport()}),$(".thumbnails").click(function(e){var n;e.target&&(n=/page-([0-9]+)/.exec($(e.target).attr("class")))&&$(".magazine").turn("page",n[1])}),$(".thumbnails li").bind($.mouseEvents.over,function(){$(this).addClass("thumb-hover")}).bind($.mouseEvents.out,function(){$(this).removeClass("thumb-hover")}),$.isTouch?$(".thumbnails").addClass("thumbanils-touch").bind($.mouseEvents.move,function(e){e.preventDefault()}):$(".thumbnails ul").mouseover(function(){$(".thumbnails").addClass("thumbnails-hover")}).mousedown(function(){return!1}).mouseout(function(){$(".thumbnails").removeClass("thumbnails-hover")}),$.isTouch?$(".magazine").bind("touchstart",regionClick):$(".magazine").click(regionClick),$(".next-button").bind($.mouseEvents.over,function(){$(this).addClass("next-button-hover")}).bind($.mouseEvents.out,function(){$(this).removeClass("next-button-hover")}).bind($.mouseEvents.down,function(){$(this).addClass("next-button-down")}).bind($.mouseEvents.up,function(){$(this).removeClass("next-button-down")}).click(function(){$(".magazine").turn("next")}),$(".previous-button").bind($.mouseEvents.over,function(){$(this).addClass("previous-button-hover")}).bind($.mouseEvents.out,function(){$(this).removeClass("previous-button-hover")}).bind($.mouseEvents.down,function(){$(this).addClass("previous-button-down")}).bind($.mouseEvents.up,function(){$(this).removeClass("previous-button-down")}).click(function(){$(".magazine").turn("previous")}),resizeViewport(),void $(".magazine").addClass("animated"))}function toggleFullscreen(e){e=e||document.documentElement,document.fullscreenElement||document.mozFullScreenElement||document.webkitFullscreenElement||document.msFullscreenElement?document.exitFullscreen?document.exitFullscreen():document.msExitFullscreen?document.msExitFullscreen():document.mozCancelFullScreen?document.mozCancelFullScreen():document.webkitExitFullscreen&&document.webkitExitFullscreen():e.requestFullscreen?e.requestFullscreen():e.msRequestFullscreen?e.msRequestFullscreen():e.mozRequestFullScreen?e.mozRequestFullScreen():e.webkitRequestFullscreen&&e.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT)}$(".zoom-icon").bind("mouseover",function(){$(this).hasClass("zoom-icon-in")&&$(this).addClass("zoom-icon-in-hover"),$(this).hasClass("zoom-icon-out")&&$(this).addClass("zoom-icon-out-hover")}).bind("mouseout",function(){$(this).hasClass("zoom-icon-in")&&$(this).removeClass("zoom-icon-in-hover"),$(this).hasClass("zoom-icon-out")&&$(this).removeClass("zoom-icon-out-hover")}).bind("click",function(){$(this).hasClass("zoom-icon-in")?$(".magazine-viewport").zoom("zoomIn"):$(this).hasClass("zoom-icon-out")&&$(".magazine-viewport").zoom("zoomOut")}),$(".fullscreen-icon").bind("click",function(){$(this).hasClass("full-in")?$(this).removeClass("full-in").addClass("full-out"):$(this).removeClass("full-out").addClass("full-in"),toggleFullscreen()}),$("#canvas").hide(),yepnope({test:Modernizr.csstransforms,yep:["/magazine/turnjs/lib/turn.js"],nope:["/magazine/turnjs/lib/turn.html4.min.js"],both:["/magazine/turnjs/lib/zoom.min.js","/magazine/js/magazine.js","/magazine/css/magazine.css"],complete:loadApp});