!function(e){function t(e){return String(e).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;")}function s(t){var s=[],n=t.prog_id,a=n.split(",");if(a.length<1)return"";for(var o=0;o<a.length;o++){var i="";if(e.isNumeric(a[o]))i="undefined"!=typeof listPrograms[a[o]]?listPrograms[a[o]]:"";else{var r=a[o].split("_")[1];i="undefined"!=typeof listPositions[r]?listPositions[r]:""}i&&s.push(i)}return s.join(", ")}e(document).ready(function(){e(".note-item .note-show").each(function(){e(this).shortContent(),e(this).removeClass("hidden")})}),e("body").on("click",".note-edit-btn",function(t){t.preventDefault();var s=e(this).closest(".note-item"),n=s.find(".note-show"),a=s.find(".note-edit");n.hasClass("hidden")?(n.removeClass("hidden"),a.addClass("hidden")):(n.addClass("hidden"),a.removeClass("hidden"))}),e("body").on("change",".note-item .note-edit",function(){var t=e(this),s=e(this).closest(".note-item"),n=s.find(".note-error");n.text("").addClass("hidden");var a=s.find(".loading");if(a.hasClass("hidden")){var o=t.val();if(o.trim().length>500)return void n.text(textErrorMaxLength).removeClass("hidden");var i=s.find(".note-show"),r=e(this).closest("tr").data("week");a.removeClass("hidden"),e.ajax({type:"POST",url:saveNoteUrl,data:{week:r,note:o,email:s.data("email"),_token:siteConfigGlobal.token},success:function(e){e["delete"]?(s.find(".note-name").text(""),s.addClass("note-current")):(s.find(".note-name").text(e.name+": "),s.removeClass("note-current")),i.text(e.note).removeClass("hidden shortened").shortContent(),t.addClass("hidden")},error:function(e){n.text(e.responseJSON).removeClass("hidden")},complete:function(){a.addClass("hidden")}})}}),e.fn.shortContent=function(t){var s={showChars:60,showLines:3,ellipsesText:"...",moreText:textShowMore,lessText:textShowLess};return t&&e.extend(s,t),e(document).off("click",".morelink"),e(document).on({click:function(){var t=e(this);return t.hasClass("less")?(t.removeClass("less"),t.html(s.moreText)):(t.addClass("less"),t.html(s.lessText)),t.parent().prev().toggle(),t.prev().toggle(),!1}},".morelink"),this.each(function(){var t=e(this);if(!t.hasClass("shortened")){t.addClass("shortened");var n=t.html(),a="",o=n.split("\n"),i=n,r="",d=!1;if(o.length>s.showLines&&(d=!0,n=o.splice(0,s.showLines).join("\n"),a=o.join("\n")),n.length>s.showChars?(d=!0,i=n.substr(0,s.showChars),r=n.substr(s.showChars,n.length-s.showChars)+a):(i=n,r=a),d){var l=i+'<span class="moreellipses">'+s.ellipsesText+' </span><span class="morecontent"><span>'+r+'</span> <a href="#" class="morelink">'+s.moreText+"</a></span>";t.html(l),e(".morecontent span").hide()}}})},listPrograms=listPrograms?JSON.parse(listPrograms):[],listPositions=listPositions?JSON.parse(listPositions):[];var n=e("#modal_report_detail table thead tr:first").clone();e("body").on("click","td.col-data",function(a){a.preventDefault();var o=e(this).data("items"),i=!1;if("undefined"!==e(this).data("working")&&e(this).data("working")&&(i=!0),"undefined"!=typeof o){var r=e(this).closest("tr").data("week"),d=e(this).closest("table").find("thead tr:first th:eq("+e(this).index()+")").text();if(e("#modal_report_detail .modal-title").html(textWeek+": "+r+": "+d),e("#modal_report_detail").modal("show"),!(o.length<0)){var l=e("#modal_report_detail table");l.closest(".dataTables_wrapper").length>0&&(l.DataTable().destroy(),l.empty());for(var h="",m=!1,f=0;f<o.length;f++){var c=o[f],p=s(c);h+="<tr><td>"+(f+1)+'</td><td><a target="_blank" href="'+routeDetail+"/"+c.id+'">'+t(c.fullname)+"</a></td><td>"+c.email+"</td><td>"+(p?p:"")+"</td><td>"+c.recruiter+"</td>","undefined"!=typeof c.start_working_date&&(h+="<td>"+c.start_working_date+"</td>",m=!0),"undefined"!=typeof c.name&&(h+="<td>"+c.name+"</td>"),h+="</tr>"}i?0===n.find(".th-add").length&&n.append('<th class="th-add">'+textDate+'</th><th class="th-add">'+textTeam+"</th>"):n.find(".th-add").remove(),h||(h=7),h="<thead>"+n[0].outerHTML+"</thead><tbody>"+h+"</tbody>",l.html(h),l.DataTable({pageLength:20})}}})}(jQuery);