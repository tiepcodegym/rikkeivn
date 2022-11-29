function _setStatus(e,t){"undefined"==typeof t&&(t=!1),t?_me_status.addClass("me_error"):_me_status.removeClass("me_error"),t&&(e+='<a class="close close_alert" href="javascript:void(0)">×</a>'),_me_status.html(e).addClass("show")}function _showStatus(e,t){clearTimeout(_showTimeout),"undefined"==typeof t&&(t=!1),"undefined"==typeof e&&(e=text_error_occurred),_setStatus(e,t),t||(_showTimeout=setTimeout(function(){_me_status.removeClass("show")},3e3))}function _hideStatus(){clearTimeout(_showTimeout),_me_status.removeClass("show")}function getOffsetParent(e,t){var a=e.offset(),n=t.offset();return{top:a.top-n.top,left:a.left-n.left}}!function(e){function t(e){if("undefined"!=typeof ArrayBuffer){for(var t=new ArrayBuffer(e.length),a=new Uint8Array(t),n=0;n!==e.length;++n)a[n]=255&e.charCodeAt(n);return t}for(var t=[],n=0;n!==e.length;++n)t[n]=255&e.charCodeAt(n);return t}function a(){var e=window.location;window.history.pushState({},document.title,e.origin+e.pathname)}function n(){var t=e(".fixed-table-container table ._check_item:checked"),a=0,n=0;t.each(function(){var t=e(this).closest("tr").attr("data-id"),r=e(this).closest(".fixed-table-container").find('table tr[data-id="'+t+'"] .reward-approved');r.length>0&&(r.data("status")==STATE_PAID?a++:n++)}),e(".btn-submit-confirm").prop("disabled",t.length<1),e(".btn-submit-unpaid").prop("disabled",a<1),e(".btn-submit-paid").prop("disabled",n<1)}function r(t,a){if("undefined"!=typeof t&&(s=[t],"undefined"!=typeof a)){var n=e("#me_reward_tbl tfoot .total-"+t),r=n.text();return r?(r=parseInt(r.replace(/,/g,"")),console.log(r,a)):r=0,void n.text(e.number(r+a))}for(var o={},i=0;i<s.length;i++){var l=s[i];"undefined"==typeof o[l]&&(o[l]=0),e('#me_reward_tbl tbody [data-col="'+l+'"]').each(function(){var t=0,a=e(this).find(".input-value");t=a.length>0?a.val().trim():e(this).text().trim(),t=t&&"N/A"!=t?t.replace(/,/g,""):0,o[l]+=parseInt(t)}),e("#me_reward_tbl tfoot .total-"+l).text(e.number(o[l]))}}function o(t,a){var n=e("#rw_comment_item_tpl").find(".comment_item").clone();n.addClass(a.type_class),a.comment_type==CM_TYPE_LATE_TIME?(n.find("._comment_avatar").attr("src","/common/images/login-r.png"),n.find("._comment_name").text("System")):(n.find("._comment_avatar").attr("src",a.avatar_url),n.find("._comment_name").text(a.name+" ("+RKfuncion.general.getNickName(a.email)+")")),n.find(".comment-attr").text(a.attr_label?a.attr_label:textNote),n.find(".date").text(a.created_at),n.find(".comment_content").text(a.content),n.appendTo(t)}var i=500;e("body").on("click",".input-edit .input-edit-btn",function(t){t.preventDefault();var a=e(this).parent(),n=a.find(".value-edit"),r=a.find(".value-view");a.hasClass("edit-show")||(e(".input-edit:not(.error)").removeClass("edit-show"),a.addClass("edit-show"),n.height(Math.max(r.height(),30)).focus())}),e("body").on("blur",".input-edit .value-edit",function(t){e(this).parent().hasClass("error")||e(this).parent().removeClass("edit-show")}),e("body").on("change",".input-edit .value-edit",function(t){var a=e(this).parent(),n=e(this).val();return a.removeClass("error"),n.length>i?(_showStatus(textErrorMaxLen,!0),void a.addClass("error")):(a.removeClass("edit-show"),void a.find(".value-view").text(n).removeClass("shortened").shortContent())}),e("body").on("change",".input-number",function(){var t=e(this).val();t&&e(this).val(e.number(t));var a=e(this).attr("data-val")||"0";a=a.replace(/,/g,""),t||(t="0"),t=t.replace(/,/g,""),r(e(this).closest("td").attr("data-col"),parseInt(t)-parseInt(a))}),e("body").on("focusin",".input-number",function(t){e(this).attr("data-val",e(this).val())}),e("body").on("keyup",".input-number",function(t){e.inArray(t.keyCode,[37,39,8,46,36])==-1&&e(this).val(function(e,t){return t.replace(/[a-z]|-/gi,"")})}),e(".btn-submit-confirm").click(function(){var t=e(this),a=e(this).closest("form"),n=a.find(".submit-data"),r=e(this).data("save");n.html("");var o="";if(!r){var i=e("#rw_team_filter").val(),s=e("#rw_time_filter").val();if(!i||"_all_"==i||!s||"_all_"==s)return bootbox.alert({message:textErrorSelectTeamMonth,className:"modal-danger"}),t.prop("disabled",!1),!1}var l=!1;return e("#me_reward_tbl .input-value").each(function(){var a=e(this).closest("tr").find("._check_item");if(a.is(":checked")){if(e(this).closest('[data-col="email"]').length>0&&!e(this).val())return bootbox.alert({message:textErrorSelectEmployee,className:"modal-danger"}),l=!0,!1;e(this).hasClass("error")?(t.prop("disabled",!1),l=!0):o+='<input type="hidden" name="'+e(this).attr("name")+'" value="'+e(this).val()+'" />'}}),!l&&(""==o?(bootbox.alert({message:textErrorNoItemChecked,className:"modal-danger"}),t.prop("disabled",!1),!1):(o+='<input type="hidden" name="filter_time" value="'+e("#rw_time_filter").val()+'">',o+='<input type="hidden" name="filter_team" value="'+e("#rw_team_filter").val()+'">',o+='<input type="hidden" name="filter_type" value="'+e("#rw_type_filter").val()+'">',r?(o+='<input type="hidden" name="is_save" value="1">',n.html(o),a.submit(),!1):(bootbox.confirm({message:t.data("noti"),className:"modal-default",callback:function(e){e?(n.html(o),a.submit()):t.prop("disabled",!1)}}),!1)))}),e.fn.shortContent=function(t){var a={showChars:40,showLines:2,ellipsesText:"...",moreText:"more",lessText:"less"};return t&&e.extend(a,t),e(document).off("click",".morelink"),e(document).on({click:function(){var t=e(this);return t.hasClass("less")?(t.removeClass("less"),t.html(a.moreText)):(t.addClass("less"),t.html(a.lessText)),t.parent().prev().toggle(),t.prev().toggle(),!1}},".morelink"),this.each(function(){var t=e(this);if(!t.hasClass("shortened")){t.addClass("shortened");var n=t.html(),r="",o=n.split("\n"),i=n,s="",l=!1;if(o.length>a.showLines&&(l=!0,n=o.splice(0,a.showLines).join("\n"),r=o.join("\n")),n.length>a.showChars?(l=!0,i=n.substr(0,a.showChars),s=n.substr(a.showChars,n.length-a.showChars)+r):(i=n,s=r),l){var d=i+'<span class="moreellipses">'+a.ellipsesText+' </span><span class="morecontent"><span>'+s+'</span> <a href="#" class="morelink">'+a.moreText+"</a></span>";t.html(d),e(".morecontent span").hide()}t.removeClass("hidden")}})},e(".input-edit .value-view").shortContent(),e("body").on("click",".close_alert",function(t){t.preventDefault(),e(this).parent().removeClass("show")}),e("#btn_export_excel").click(function(t){t.preventDefault();var a=e("#rw_team_filter").val(),n=e("#rw_time_filter").val();if(!n)return void _showStatus(textRequiredTeamAndTime,!0);e(this).prop("disabled",!0);var r=e(this);r.find(".fa-spin").removeClass("hidden"),e.ajax({url:e(this).data("url"),type:"GET",dataType:"json",data:{team_id:a,time:n},success:function(t){if(t.length<1){var a=e("#modal-warning-notification");return a.find(".text-default").text("No data!"),void a.modal("show")}var n=filterDataExport(t),r=e("#export_excel").excelexportjs({containerid:"export_excel",datatype:"json",dataset:n,returnUri:!0,columns:getColumns(paramsColumn)}),o=document.createElement("a");o.href=r;var i=new Date,s=i.getMonth()+1;s=s<10?"0"+s:s;var l="-date-"+i.getDate()+"-"+s+"-"+i.getFullYear(),d="ME-Reward-"+filterMonthFormat+l;o.download=d+".xls",document.body.appendChild(o),o.click(),document.body.removeChild(o)},error:function(e){_showStatus(e.responseJSON,!0)},complete:function(){r.prop("disabled",!1),r.find(".fa-spin").addClass("hidden")}})}),e("#input-export-reward-all").click(function(){var t=document.getElementById("input-export-reward-all").checked;e(".input-export-reward, .input-export-reward-team").prop("checked",t)}),e("body").on("click",".input-export-reward, .input-export-reward-team",function(){var t=e(".input-export-reward").length+e(".input-export-reward-team").length,a=e(".input-export-reward").filter(":checked").length+e(".input-export-reward-team").filter(":checked").length;e("#input-export-reward-all").prop("checked",t==a)}),e(document).on("click","#btn_export_base_osdc",function(a){a.preventDefault();var n=e("#rw_time_filter").val(),r=e("#rw_team_filter").val(),o=e("#rw_type_filter").val(),i=e("#rw_employee_filter").val(),s=e("#rw_projectname_filter").val(),l=e("#rw_status_paid_filter").val();if(!n)return void _showStatus(textRequiredTeamAndTime,!0);e(this).prop("disabled",!0);var d=e(this);d.find(".fa-spin").removeClass("hidden");var c=[],m=[],u=e(".input-export-reward:checked"),h=e(".input-export-reward-team:checked");e(u).each(function(){c.push(e(this).val())}),e(h).each(function(){m.push(e(this).val())}),e.ajax({url:e(this).data("url"),type:"POST",dataType:"json",data:{_token:e('meta[name="_token"]').attr("content"),"project_ids[]":c,"teamIds[]":m,time:n,group:r,type:o,pm:i,projName:s,statusPaid:l},success:function(a){if(a.length<1){var r=e("#modal-warning-notification");return r.find(".text-default").text("No data!"),void r.modal("show")}var o=a,i={SheetNames:[],Sheets:{}};e("#tbl_template").html(o);var s=e("#tbl_template table")[0],l=XLSX.utils.table_to_book_str(s,{cellStyles:!0,cellDates:!1,cellFormula:!0}).Sheets.Sheet1;l["!cols"]=[{wch:20},{wch:15},{wch:20},{wch:15},{wch:35},{wch:30},{wch:15}],i.SheetNames.push("Reward-"+n),i.Sheets["Reward-"+n]=l;var d=XLSX.write(i,{bookType:"xlsx",bookSST:!0,type:"binary"}),c="Reward-"+n+".xlsx";try{saveAs(new Blob([t(d)],{type:"application/octet-stream"}),c)}catch(m){return}e("#tbl_template").html("")},error:function(e){_showStatus(e.responseJSON,!0)},complete:function(){d.prop("disabled",!1),d.find(".fa-spin").addClass("hidden")}})}),e(".btn-reset-filter").on("click",function(){e("#rw_time_filter").attr("autocomplate","off"),a()}),e(".filter-grid").on("change",function(){a()}),e("body").on("change","._check_all",function(){var t=e(this).closest(".fixed-table-container").find("table");t.find("._check_item").prop("checked",e(this).is(":checked")).trigger("change")}),e("body").on("change","._check_item",function(){var t=e("#me_reward_tbl ._check_item").length,a=e(this).closest(".table");a.find("._check_all").prop("checked",a.find("._check_item:checked").length===t);var n=e(this).closest("tr").attr("data-id");e('#me_reward_tbl tr[data-id="'+n+'"] td ._check_item').prop("checked",e(this).is(":checked"))}),e(".td-histories").each(function(){var t=e(this).data("histories");if(t){var a="<div><strong>Histories</strong></div>";a+='<ul class="list-histories">';for(var n=0;n<t.length;n++){var r=t[n],o="undefined"!=typeof r.account?"<strong>"+r.account+"</strong><br/>":"";a+="<li>"+o+" Changed <strong>"+e.number(r.number)+"</strong> at "+r.time+"</li>"}a+="</ul>"}e(this).find(".icon-history").attr("title",a),"undefined"!=typeof e.fn.qtip&&e(this).find(".icon-history").qtip({position:{my:"bottom center",at:"top center"},style:{classes:"qtip-dark"}})}),e('.tbl-reward-report td[data-reward-col="approve"]').each(function(t,a){var n=e(a).data("reward"),r=e(a).siblings('td[data-reward-col="team"]');if(!r.length)return e(a).html(e.number(n)),!0;var o=r.text().split(",").length||1;n/=o,e(a).html(e.number(n))}),e("body").on("change",".fixed-table-container ._check_item, .fixed-table-container ._check_all",function(){n()}),setTimeout(function(){n()},200),e("form.reward_change_paid").on("submit",function(){var t=e("#me_reward_tbl ._check_item:checked"),a=e(this).find('button[type="submit"]'),n=e("#rw_team_filter").val(),r=e("#rw_time_filter").val();if(!n||"_all_"==n||!r||"_all_"==r)return bootbox.alert({message:textErrorSelectTeamMonth,className:"modal-danger"}),a.prop("disabled",!1),!1;var o=[];if(t.each(function(){o.push('<input type="hidden" name="eval_ids[]" value="'+e(this).val()+'">')}),o.length<1)return bootbox.alert({message:textErrorNoItemChecked,className:"modal-warning"}),a.prop("disabled",!1),!1;o.push('<input type="hidden" name="filter_time" value="'+r+'">'),o.push('<input type="hidden" name="filter_team" value="'+n+'">');var i=e(this).closest("form");return i.find(".item-eval-ids").html(o.join(" ")),!0}),e(document).ready(function(){setTimeout(function(){e(".fixed-table thead select").select2(),"undefined"!=typeof e.fn.qtip&&e(".el-qtip").qtip({position:{my:"bottom center",at:"top center"},style:{classes:"qtip-dark"}})},300)}),e("body").on("click","#btn_loadmore_reward",function(t){t.preventDefault();var a=e(this);if(!a.hasClass("loading")){var n=a.attr("data-url");if(n){var r=a.find(".icon-loading");r.removeClass("hidden"),a.addClass("loading"),e.ajax({type:"GET",url:n,data:{index:e(".tbl-reward-report tbody tr").length,monthFilter:filterMonthFormat},success:function(t){e(".tbl-reward-report tbody").append(t.htmlContent),a.attr("data-url",t.nextPageUrl),t.nextPageUrl||a.closest("tfoot").addClass("hidden")},complete:function(){a.removeClass("loading"),r.addClass("hidden")}})}}}),e("body").on("click","#add_item_btn",function(t){t.preventDefault();var a=e("#me_reward_tbl tbody"),n=e("#rw_table_template tbody tr:first").clone(),r=["proj_name","me_status","me_contribute","norm","effort","reward_suggest"];for(var o in r)n.find('[data-col="'+r[o]+'"]').text("N/A");var i=["employee_code","email","reward_submit","comment","reward_approve","reward_status","is_paid"];for(var o in i){var s=n.find('[data-col="'+i[o]+'"]');s.find("input").length>0||s.find("textarea").length>0?(s.find("input").val(""),s.find("textarea").val("")):s.html(""),s.find(".value-view").text("").removeClass("shortened")}var l,d=a.find("tr:not(.row-no-result):last");if(d.length<1)l=1;else{var c=d.attr("data-id");l=e.isNumeric(c)?1:parseInt(d.attr("data-new"))+1}n.attr("data-id","new_"+l).attr("data-new",l),n.find("[name]").each(function(){var t=e(this).attr("name"),a=t.replace("new_id","new_"+l);e(this).attr("name",a)}),n.find('[data-col="reward_submit"]').html('<input type="text" data-min="0" data-max="" name="rewards[new_'+l+'][submit]" class="form-control input-value input-number">'),n.find('[data-col="comment"]').html('<div class="input-edit" data-eval="new_'+l+'"><span class="value-view shortened"></span><textarea name="rewards[new_'+l+'][comment]" class="form-control input-value value-edit"></textarea><button type="button" class="btn btn-sm btn-success input-edit-btn"><i class="fa fa-edit"></i></button></div>'),n.find(".td-check ._check_item").prop("checked",!1).val("new_"+l),n.find('[data-col="employee_code"]').addClass("has-del").prepend('<button type="button" class="btn btn-danger btn-sm rw-del-btn"><i class="fa fa-trash"></i></button>'),n.find(".td-histories").removeAttr("data-histories"),n.find(".td-histories .icon-history").remove(),n.find('[data-col="email"]').html('<select class="form-control input-value select-search"data-remote-url="'+urlSearchEmployee+"?team_id="+e("#rw_team_filter").val()+'"name="rewards[new_'+l+'][employee_id]"></select>');var m="N/A";"_all_"!=e("#rw_type_filter").val()&&(m=e("#rw_type_filter option:selected").text()),n.find('[data-col="proj_type"]').text(m),a.find(".row-no-result").remove(),n.appendTo(a),n.find(".td-check ._check_item").trigger("click"),RKfuncion.select2.init({},n.find('[data-col="email"] .select-search'));var u=e(".fixed-table thead tr:first .td-fixed").length;e(".fixed-table").tableHeadFixer({left:u})}),e("body").on("change",'[data-col="email"] select',function(){var t=e(this).val();if(t){var a=e(this);e.ajax({type:"GET",url:urlEmployeeInfor,data:{employee_id:t,"cols[]":"employee_code"},success:function(e){var t=a.closest("tr").find('[data-col="employee_code"]');t.find(".value").length<1&&t.append('<span class="value"></span>'),t.find(".value").text(e.employee_code)},error:function(){a.val("").trigger("change")}})}}),e("body").on("click",".rw-del-btn",function(t){t.preventDefault();var a=e(this);if(!a.is(":disabled")){var n=a.closest("tr"),o=n.attr("data-id");e.isNumeric(o)?bootbox.confirm({className:"modal-danger",message:textConfirmDelete,callback:function(t){t&&(a.prop("disabled",!0),e.ajax({type:"DELETE",url:urlDeleteItem,data:{_token:_token,id:o},success:function(e){n.remove(),bootbox.alert({className:"modal-success",message:e.message}),r()},error:function(e){bootbox.alert({className:"modal-danger",message:e.responseJSON.message})},complete:function(){a.prop("disabled",!1)}}))}}):(n.remove(),r())}});var s=["norm","reward_suggest","reward_submit","reward_approve"];setTimeout(function(){"undefined"!=typeof hasMorePages&&hasMorePages?e.ajax({type:"GET",url:urlGetTotalReward,data:{hasBtnSubmit:hasBtnSubmit,isReview:isReview,routeName:routeName,team_id:team_id,time:filterMonth},success:function(t){e.each(t,function(t,a){e("#me_reward_tbl tfoot .total-"+t).text(a)})},error:function(t){e.each(s,function(t,a){e("#me_reward_tbl tfoot .total-"+a).text("...").addClass("error")})}}):r()},500),e("body").on("contextmenu",'[data-col="comment"]',function(e){return e.preventDefault(),!1});var l=null;e("body").on("mousedown",'[data-col="comment"]',function(t){if(3==t.which){var a=e(this),n=e("#me_comment_modal"),r=n.find(".me_comments_list");r.html(""),setTimeout(function(){n.removeClass("hidden");var t=e(".content-container .box:first"),r=getOffsetParent(a,t),o=r.left,i=r.top;o+n.width()+a.width()+10>=t.width()?o-=n.width():o=o+a.width()+10,n.css("top",i+"px").css("left",o+"px")},200),l&&l.abort();var i=a.closest("tr").attr("data-id"),s=n.find("._loading"),d=n.find("._no_comment"),c=n.find("._error");s.removeClass("hidden"),c.addClass("hidden"),l=e.ajax({type:"GET",url:urlLoadEvalComments,data:{id:i},success:function(e){if(e.length<1)d.removeClass("hidden");else{d.addClass("hidden");for(var t=0;t<e.length;t++){var a=e[t];o(r,a)}}},error:function(e){c.text(e.responseJson.message).removeClass("hidden")},complete:function(){s.addClass("hidden")}})}}),e("body").on("click","#me_comment_modal .close, #me_comment_modal .cancel-btn",function(){e("#me_comment_modal").addClass("hidden").css("top","auto").css("left","auto"),e("#me_comment_modal").find(".me_comments_list").html("")}),e("#btn_export_me_reward").click(function(t){t.preventDefault();var a=document.createElement("form");a.setAttribute("method","post"),a.setAttribute("action",e(this).data("url"));var n=parseInt(e('#modal_export_me_reward [name="is_all"]:checked').val()),r={_token:siteConfigGlobal.token,is_all:n,month:e("#rw_time_filter").val(),url_filter:window.location.origin+window.location.pathname};if(r=e.extend(r,RKfuncion.general.paramsFromUrl()),!n){var o=e("#me_reward_tbl ._check_item:checked");if(o.length<1)return bootbox.alert({className:"modal-danger",message:textErrorNoItemChecked}),!1;o.each(function(t){r["ids["+t+"]"]=e(this).val()})}for(var i in r){var s=document.createElement("input");s.setAttribute("type","hidden"),s.setAttribute("name",i),s.setAttribute("value",r[i]),a.appendChild(s)}document.body.appendChild(a),a.submit(),a.remove()})}(jQuery);var _me_status=$("#_me_alert"),text_error_occurred="Error!",_showTimeout;$(function(){$("body").on("contextmenu",'[data-col="norm"]',function(e){return e.preventDefault(),!1}),$("body").on("contextmenu",'[data-view_note="view-note"]',function(e){return e.preventDefault(),!1}),$("body").on("mousedown",'[data-view_note="view-note"]',function(e){if(3==e.which){var t=$(this),a=$("#me_allowance_onsites_modal"),n=a.find(".me_comments_list");n.html("");var r=t.find(".note-allowance-onsite ul").html();setTimeout(function(){a.removeClass("hidden");var e=$(".content-container .box:first"),o=getOffsetParent(t,e),i=o.left,s=o.top;i+a.width()+t.width()+10>=e.width()?i-=a.width():i=i+t.width()+10,a.css("top",s+"px").css("left",i+"px"),n.html(r)},200)}})}),$("body").on("click","#me_allowance_onsites_modal .close",function(e){$("#me_allowance_onsites_modal").addClass("hidden").css("top","auto").css("left","auto"),$("#me_allowance_onsites_modal").find(".me_comments_list").html("")});