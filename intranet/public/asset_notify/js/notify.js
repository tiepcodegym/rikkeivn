!function(t){function n(n){_.html(n.content),C.show(),x.attr("disabled",!0).css("opacity",.2),_.height()>=400&&_.css("overflow-y","auto"),t.ajax({type:"PUT",url:notifyConst.set_read_url,data:{_token:siteConfigGlobal.token,notify_id:n.notify_id},success:function(){x.attr("disabled",!1).css("opacity",1)}})}function o(n){var o=window.location,e=o.origin+o.pathname;if("undefined"!=typeof n)var i=t('.notify-item[data-id="'+n+'"]');else{n=null;var i=t('.notify-item[href^="'+e+'"]')}if(i.length>0){if(!i.hasClass("not-read"))return!1;if(i.hasClass("loading"))return!1}i.addClass("loading"),t.ajax({type:"PUT",url:notifyConst.set_read_url,data:{_token:siteConfigGlobal.token,notify_id:n,url:n?null:e},success:function(t){i.removeClass("not-read");var n=h.text()?parseInt(h.text()):0;n&&d(n-parseInt(t))},complete:function(){i.removeClass("loading")}})}function e(n,o){"undefined"==typeof o&&(o=!0);var e=n.attr("data-url");if(e){u.find("a.notify-toggle").hasClass("loaded")||u.find(".notify-list").html("");var a=n.closest(".noti-contain").find(".noti-loading");a.removeClass("hidden");var s=n.closest(".noti-contain").find(".load-more");t.ajax({method:"GET",url:e,success:function(e){e.total>0?(m.addClass("hidden"),p.removeClass("hidden")):(m.removeClass("hidden"),p.addClass("hidden"));var a=e.notify_list;if(a.length>0){for(var f=0;f<a.length;f++){var l=a[f];i(l,n)}t("#notify_list_page .notify-content").shortedContent({showChars:500}),t("#notify_list .notify-content").shortedContent()}e.next_page_url||s.addClass("hidden"),n.attr("data-url",e.next_page_url),o&&u.find("a.notify-toggle").addClass("loaded"),u.hasClass("open")&&!k&&(k=setTimeout(function(){var n=0;y.find("li").each(function(){n+=t(this).height()}),n<=y.height()&&y.height(n-5)},1e3))},error:function(){n.append('<li><a class="notify-item">Something error!</a></li>')},complete:function(){a.addClass("hidden")}})}}function i(n,o,e){"undefined"==typeof e&&(e=!0);var i=v.clone().find("li"),a=i.find(".notify-item"),s=t("<div>"+n.content+"</div>").text();n.read_at?a.removeClass("not-read"):a.addClass("not-read"),a.attr("data-id",n.id),a.attr("title",s),a.attr("href",n.link),"https://mail.google.com"===n.link&&a.attr("target","_blank"),a.find(".notify-icon img").attr("src",n.image),a.find(".notify-content").text(s),a.find(".notify-time").attr("data-time",n.timestamp).text(l(n.timestamp)),e?i.appendTo(o):i.prependTo(o)}function a(n){var o=n;o.hasClass("loaded")||e(y);var i=o.data("reset-url"),a=parseInt(o.find(".notify-num").text());a&&t.ajax({type:"PUT",url:i,data:{_token:siteConfigGlobal.token},success:function(){d(0),o.addClass("reseted")}})}function s(n){var o=t("#notify_list .notify-item:first").attr("data-id");"undefined"==typeof o&&(o=notifyConst.max_id||0),f(),t.ajax({url:notifyConst.refresh_url,type:"GET",data:{last_id:o},success:function(n){var o=n.notify_list;if(o.length>0){t(".notify-list").each(function(){t(this).closest(".noti-contain").find(".none-item").addClass("hidden")}),t(".check-readall").removeClass("hidden");for(var e=0;e<o.length;e++){var a=o[e];i(a,t(".notify-list"),!1)}notifyConst.max_id=o[o.length-1].id,t("#notify_list .notify-content").shortedContent(),t("#notify_list_page .notify-content").shortedContent({showChars:500})}var s=n.num_noti?parseInt(n.num_noti):0;0===s&&(s=""),s>99&&(s="99+"),h.text(s),c(s)},complete:function(){"undefined"!=typeof n&&n()}})}function f(){t(".notify-list .notify-item").each(function(){var n=t(this).find(".notify-time"),o=parseInt(n.attr("data-time"));n.text(l(o))})}function l(t){var n=Math.floor((new Date).getTime()/1e3),o=n-t;if(o<60)return notifyConst.text_recently_update;if(o<3600)return Math.floor(o/60)+" "+notifyConst.text_minutes_ago;if(o<86400)return Math.floor(o/3600)+" "+notifyConst.text_hours_ago;if(o<604800)return Math.floor(o/86400)+" "+notifyConst.text_days_ago;var e=new Date(1e3*t);return r(e.getHours())+":"+r(e.getMinutes())+" "+r(e.getDate())+"/"+r(e.getMonth()+1)+"/"+e.getFullYear()}function r(t){return t<10?"0"+t:t}function d(t){if("undefined"==typeof t){if(t=parseInt(h.text()),0===t)return;t-=1}t<1&&(t=""),h.text(t),c(t)}function c(t){var n=(t>0?"("+t+") ":"")+g;document.title=n}var u=t("#notify_menu"),y=u.find(".notify-list"),h=u.find(".notify-num"),p=u.find(".view-all"),m=u.find(".none-item"),v=t("#notify_template"),g=document.title,C=t("#modal-popup-notify"),_=C.find(".content"),x=C.find(".close");x.click(function(){popupNotifications.length?n(popupNotifications.shift()):C.hide()}),t(document).ready(function(){var n=parseInt(h.text());n&&c(n);var e=window.location.search;e||(e=window.location.hash);var i=!1;if(e){var a=e.split("?");if(2!==a.length)return;for(var s=a[1].split("&"),f=0;f<s.length;f++){var l=s[f].split("=");if(2===l.length&&"notify_id"===l[0]){var r=t('a[href="'+a[0]+'"]');r.attr("data-toggle")&&r.tab("show"),o(l[1]),i=!0;break}}}!i&&n>0&&o()}),t("body").on("click",".notify-list .notify-item",function(n){var e=t(this);if(e.attr("target")||!e.attr("href")){var i=e.attr("data-id");o(i)}}),t("body").on("click",".notify-item .mark-read",function(n){n.preventDefault();var e=t(this).closest(".notify-item").attr("data-id");o(e),n.stopPropagation()}),t("body").on("click",".check-readall a",function(n){n.preventDefault();var o=t(this);o.hasClass("loading")||(o.addClass("loading"),t.ajax({url:notifyConst.set_read_url,type:"PUT",data:{_token:siteConfigGlobal.token,read_all:1},success:function(){t("#notify_list_page li a").removeClass("not-read")},complete:function(){o.removeClass("loading")}}))}),y.scroll(function(){t(this).scrollTop()+t(this).height()>=this.scrollHeight&&e(y)});var k=null;t("body").on("click",".notify-page .load-more a",function(n){n.preventDefault();var o=t(this),i=o.closest(".noti-contain"),a=i.find(".notify-list");e(a,!1)}),t("body").on("click","#notify_menu .notify-toggle",function(n){a(t(this))}),t("body").on("click","#notify_menu .noti-header .refresh",function(n){n.preventDefault(),n.stopImmediatePropagation();var o=t(this).find("i.fa");o.hasClass("fa-spin")||(o.addClass("fa-spin"),s(function(){o.removeClass("fa-spin")}))}),setInterval(f,6e4);var w=notifyConst.protocol+"://"+notifyConst.host+":"+notifyConst.port+"?employee_id="+notifyConst.employeeId+"&token="+notifyConst.wsToken,b=new WebSocket(w);b.onopen=function(){"local"==notifyConst.env&&console.log("connected to "+w)};var T=null;b.onmessage=function(o){var e=JSON.parse(o.data);if(e.app_env==notifyConst.notiEnv){if("local"==notifyConst.env&&console.log(e),e.id=e.notify_id,e.content=e.title,e.timestamp=e.time,e.type===notifyConst.typePopup)return popupNotifications.push(e),void("none"===C.css("display")&&n(popupNotifications.shift()));t(".notify-list").each(function(){t(this).closest(".noti-contain").find(".none-item").addClass("hidden")}),t(".check-readall").removeClass("hidden"),i(e,t(".notify-list"),!1),notifyConst.max_id=e.id,t("#notify_list .notify-content").shortedContent(),t("#notify_list_page .notify-content").shortedContent({showChars:500});var s=h.text()?parseInt(h.text()):0;s++,0===s&&(s=""),s>99&&(s="99+"),h.text(s),c(s),u.hasClass("open")&&(T&&clearTimeout(T),T=setTimeout(function(){a(u.find(".notify-toggle"))},3e3))}}}(jQuery);