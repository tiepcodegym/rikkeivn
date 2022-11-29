function initDataTableFilter(e){e.api().columns().every(function(){var e=this,t=[0,1,2,7];if(t.indexOf(e.index())==-1){var n=$('<select class="select-search form-control"><option value="">&nbsp;</option></select>').appendTo($(e.header()).empty()).on("change",function(){var t=$.fn.dataTable.util.escapeRegex($(this).val());t=t?t.trim():t,e.search(t).draw()}),a=[];e.data().unique().sort().each(function(e,t){e=e?e.trim():e,e&&a.indexOf(e)<0&&(a.push(e),n.append('<option value="'+e+'">'+e+"</option>"))})}}),selectSearchReload()}function sortableQuestion(){list_question.length>0&&""!=list_question.html().trim()&&"undefined"!=typeof jQuery.ui&&list_question.sortable({start:function(e,t){$(t.item).addClass("dragging")},stop:function(e,t){list_question.find(".q_item").each(function(e){$(this).find(".q_order .num").text(parseInt(e)+1)}),$(t.item).removeClass("dragging")},helper:function(e,t){return t.children().each(function(){$(this).width($(this).width())}),t}})}function replaceOrAppendQuestion(e,t,n){if("undefined"!=typeof e&&e){"undefined"==typeof t&&(t=!0),"undefined"==typeof n&&(n=null),e=tempEditQuestion.id?tempEditQuestion.id:e;var a=$("tr#question_"+e);null!==n&&""!==n||(n=null!==tempEditQuestion.order?tempEditQuestion.order:a.find(".q_order .num").text()),$.ajax({type:"GET",url:get_edit_question_url,data:{question_id:e,q_order:n,test_id:currentTestId,lang:currentLangEdit},success:function(n){$("#tr_no_item").remove();var i=$(".table-multiple-choice").DataTable();a.length>0?i.row(a).data(trToData(n)).draw():(i.row.add($(n)).draw(),$("tr#question_"+e+" .q_order .num").text($("#list_question tr").length)),initDataTableFilter($(".table-multiple-choice").dataTable()),t&&$("tr#question_"+e+" .check_item").prop("checked",t),$("tr#question_"+e+" pre code").each(function(e,t){hljs.highlightBlock(t)}),initAudioPlayer()}})}}function createOrEditWrittenQuestion(e,t,n){if("undefined"!=typeof e&&e){"undefined"==typeof t&&(t=!0),"undefined"==typeof n&&(n=null),e=tempEditQuestion.id?tempEditQuestion.id:e;var a=$("tr#question_"+e);null!==n&&""!==n||(n=null!==tempEditQuestion.order?tempEditQuestion.order:a.find(".q_order .num").text()),$.ajax({type:"GET",url:get_edit_question_url,data:{question_id:e,q_order:n,test_id:currentTestId,lang:currentLangEdit,type:4},success:function(n){$("#tr_no_item").remove();var i=$(".table-written-questions").DataTable();a.length>0?i.row(a).data(trToData(n)).draw():(i.row.add($(n)).draw(),$("tr#question_"+e+" .q_order .num").text($("#list_written_question tr").length)),initDataTableFilter($(".table-written-questions").dataTable()),t&&$("tr#question_"+e+" .check_item").prop("checked",t),$("#DataTables_Table_1_length").hide(),$("#DataTables_Table_1_filter").hide()}})}}function trToData(e){return $(e).find("td").map(function(e,t){return t.innerHTML}).get()}function loadErrorImage(e){return e.onerror="",e.src=window.location.origin+"/common/images/noimage.png",!0}function showModalError(e){"undefined"==typeof e&&(e="Error!"),modal_warning.find(".text-default").html(e),modal_warning.modal("show")}function showModalConfirm(e){modal_confirm.find(".text-default").html(e),modal_confirm.modal("show")}function confirmChangeFile(){return""==list_question.html().trim()||confirm(text_confirm_edit_upload)}function htmlEntities(e){return String(e).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;")}function setDisabledCheckTotal(e){$("#total_q").prop("disabled",!e),$("#display_option_box select, #display_option_box .input-option").prop("disabled",!e),$("#add_display_option_btn").prop("disabled",!e),e||($("#total_q-error").remove(),$("#total_q").removeClass("error"),$("#display_option_box .display-option-row").removeClass("error"),$("#display_option_box .display-option-row").next("p.error").remove())}var excel_file=$("#excel_file"),loading_file=$(".loading_file"),question_box=$(".question_box"),list_question=$("#list_question"),written_question=$("#list_written_question"),optQuestionDataTable={paging:!1,info:!1,ordering:!1,order:[[1,"asc"]],initComplete:function(){initDataTableFilter(this)}},tempEditQuestion={id:null,order:null};!function(e){function t(t){if(t){var n="";e(".table-written-questions tbody tr").each(function(){e(this).find(".dataTables_empty").length<1&&(n+=e(this)[0].outerHTML)}),n+=t,e(".table-written-questions").DataTable().clear().destroy(),written_question.html(n),written_question.find(".q_item").each(function(t){e(this).find(".q_order .num").text(parseInt(t)+1)}),sortableQuestion(),e(".table-written-questions").DataTable(optQuestionDataTable)}}function n(t){var n="";t.find("select.select-cat").each(function(){var t=e(this).val();t&&(n+="[data-cat-"+e(this).data("cat")+'="'+t+'"]')});var a=list_question.find(n).length;t.find(".total-option").val(a),t.find(".input-option").attr("max",a).prop("disabled",0===a)}function a(t){t.removeClass("error");var n=t.next("p.error");n.remove(),n=e('<p class="error"></p>');var a=t.find(".input-option"),i=parseInt(a.val());return""==a.val().trim()?(t.after(n),n.text(text_error_input_number_question),t.addClass("error"),!1):i<parseInt(a.attr("min"))?(t.after(n),n.text(please_input_value_not_less_than+" "+a.attr("min")),t.addClass("error"),!1):!(i>parseInt(a.attr("max")))||(t.after(n),n.text(please_input_value_not_greater_than+" "+a.attr("max")),t.addClass("error"),!1)}function i(){var t=e('<p class="error"></p>'),n=e("#display_option_box .display-option-row").last();n.next("p.error").remove(),n.removeClass("error");var a=parseInt(e("#total_q").val()),i=0;return e("#display_option_box input.input-option").each(function(){i+=parseInt(e(this).val())}),!(i>a)||(t.text(total_question_has_exceeded_the_limit),n.after(t),n.addClass("error"),!1)}function o(t){t.removeClass("error");var n=t.next("p.error");n.remove(),n=e('<p class="error"></p>');var a=[];if(t.find("select.select-cat").each(function(){e(this).val()&&a.push(parseInt(e(this).val()))}),a.length!=t.find("select.select-cat").length)return!0;var i=!1;return e("#display_option_box .display-option-row").not(t).each(function(){var t=[];e(this).find("select.select-cat").each(function(){e(this).val()&&t.push(parseInt(e(this).val()))});var n=e(a).not(t).get();i||0!=n.length||(i=!0)}),!i||(n.text(text_error_unique_display_option),t.after(n),t.addClass("error"),!1)}function r(){var t=!1;return e("#display_option_box .display-option-row").each(function(){var n=e(this);n.removeClass("error");var a=n.next("p.error");a.remove(),a=e('<p class="error"></p>');var i=!0;n.find("select.select-cat").each(function(){if(e(this).val())return i=!1,!1}),i&&(a.text(text_error_required_select_cat),n.after(a),n.addClass("error")),i&&!t&&(t=!0)}),!t}function s(){var t=e(".q_item").length;return 0==t?1e3:t}function l(){var t=e("#time_start").val(),n=e("#time_end").val();if(!t||!n)return!0;t=new Date(t),n=new Date(n);var a=n-t;return a>=0}function c(e,t,n){var a=window.screen.width/2-(t/2+10),i=window.screen.height/2-(n/2+50);return window.open(e,"edit_question","status=no,height="+n+",width="+t+",resizable=yes,left="+a+",top="+i+",screenX="+a+",screenY="+i+",toolbar=no,menubar=no,scrollbars=1,location=no,directories=no")}var d=RKSession.getItem("test_edit_tab");d&&e('.test-tabs .nav-tabs a[href="'+d+'"]').click(),e(".btn-edit-test").click(function(){RKSession.removeItem("test_edit_tab")}),e("#total_q").attr("max",s()),sortableQuestion(),excel_file.click(function(t){e(this).val(""),e("#import_result").html("")});var u="",p=[],m="";e("#btn_modal_import").click(function(){excel_file.val(""),u="",m="",p=[],e("#import_result").html(""),e("#submit_import").prop("disabled",!0)}),excel_file.on("change",function(){e(".check_all").prop("checked",!1);var n=e(this);e('#test_form button[type="submit"]').prop("disabled",!1),e("#select_q_error").addClass("hidden");var a=e("#modal_import_file");if(!n.hasClass("loading")){e("#has_upload").val("1");var i=n.data("url"),o=e('input[name="random_order"]').is(":checked"),r=e('input[name="random_answer"]').is(":checked"),s=e('input[name="test_id"]').val(),l=new FormData;l.append("file",n[0].files[0]),l.append("random_order",o),l.append("random_answer",r),l.append("_token",_token),l.append("lang",currentLangEdit),l.append("test_id",s),n.prop("disabled",!0),loading_file.removeClass("hidden"),a.addClass("importing"),e.ajax({url:i,type:"POST",data:l,processData:!1,contentType:!1,cache:!1,success:function(n){u=n.html,m=n.writtenHtml,e("#import_result").html(n.message),n.html&&e("#submit_import").prop("disabled",!1),p=n.categories,t(m)},error:function(t){var n="Error!";"undefined"!=typeof t.responseJSON&&(n=t.responseJSON),e("#import_result").html('<p class="error">'+n+"<p>")},complete:function(){n.prop("disabled",!1),n.removeClass("loading"),loading_file.addClass("hidden"),a.removeClass("importing")}})}}),window.onbeforeunload=function(){if(u||m)return!0},e("#cancel_import").click(function(){u="",m="",p=[]}),e("#submit_import").click(function(){if(u||m){var t=e('input[name="option_import"]:checked').val();if(e("#modal_import_file").modal("hide"),u){if("append"==t){var a="";e(".table-multiple-choice tbody tr").each(function(){e(this).find(".dataTables_empty").length<1&&(a+=e(this)[0].outerHTML)}),a+=u,e(".table-multiple-choice").DataTable().clear().destroy(),list_question.html(a),list_question.find(".q_item").each(function(t){e(this).find(".q_order .num").text(parseInt(t)+1)})}else e(".table-multiple-choice").DataTable().clear().destroy(),list_question.html(u);sortableQuestion(),selectSearchReload(),e(".check_all").prop("checked",!0),e(".check_item").prop("checked",!0),e("#total_q").attr("max",s()),e("pre code").each(function(e,t){hljs.highlightBlock(t)}),e(".table-multiple-choice").DataTable(optQuestionDataTable),initAudioPlayer()}if(p){for(var i in p){var o='<option value="">&nbsp;</option>',r=[];if("append"==t&&"undefined"!=typeof currentCollectCats[i])for(var l in currentCollectCats[i])o+='<option value="'+l+'">'+currentCollectCats[i][l]+"</option>",r.push(l);var c=p[i];if(c)for(var l in c)r.indexOf(l)<0&&(o+='<option value="'+l+'">'+c[l]+"</option>");var d=e(".display-option-row .category_"+i),h=d.val();d.html(o),d.val(h)}e(".display-option-row").each(function(){n(e(this))})}}}),e(".display-option-row").each(function(){n(e(this))}),e(".modal").on("hide.bs.modal",function(){return!e(this).hasClass("importing")}),e("body").on("change",".display-option-row select.select-cat",function(){var t=e(this).closest(".display-option-row");n(t),o(t)}),e("body").on("change",".display-option-row input.input-option",function(){var t=e(this).closest(".display-option-row");a(t)&&i()}),e("#add_display_option_btn").click(function(t){t.preventDefault();var n=e("#display_option_box");n.find(".display-option-row").removeClass("error"),n.find("p.error").remove();var a=e("#display_option_tpl").clone().removeAttr("id").removeClass("hidden");n.find(".display-option-row").length>0&&(a=n.find(".display-option-row:last").clone());var i=n.find(".display-option-row").length;a.find("select.select-cat").each(function(){e(this).val("");var t=e(this).data("cat");e(this).attr("name","display_option["+i+"]["+t+"]")}),a.find(".total-option").val(""),a.find(".input-option").val(1).attr("name","display_option["+i+"][value]"),n.append(a)}),e("body").on("click",".btn-del-row",function(t){t.preventDefault();var n=e(this).closest(".display-option-row");n.next("p.error").remove(),n.remove();var a=e("#display_option_box");a.find(".display-option-row").each(function(t){e(this).find("select.select-cat").each(function(){var n=e(this).data("cat");e(this).attr("name","display_option["+t+"]["+n+"]")}),e(this).find(".input-option").attr("name","display_option["+t+"][value]")})}),e("#total_q").on("change",function(){i()}),e("body").on("submit","#test_form",function(){var t=e(this),n=e(this).find('button[type="submit"]');if(e(".q_item .check_item:checked").length<1)return e(".test-tabs ul li:eq(1) a").click(),e("#select_q_error").removeClass("hidden"),e('#test_form button[type="submit"]').prop("disabled",!1),e("html, body").animate({scrollTop:e("#select_q_error").offset().top}),n.prop("disabled",!1),!1;if(e("#select_q_error").addClass("hidden"),e(".time_start .error").length>0||e("#total_q").parent().find(".error").length>0||e(".time_end .error").length>0)return e(".test-tabs ul li:first a").click(),n.prop("disabled",!1),!1;if(e("#check_total").is(":checked")&&(r()&&i(),e("#display_option_box .display-option-row.error").length>0))return e(".test-tabs ul li:eq(2) a").click(),n.prop("disabled",!1),!1;var a="",o="";return list_question.find(".q_item .check_item:checked").each(function(t){a+='<input type="hidden" name="q_items['+t+']" value="'+e(this).val()+'" />';var n=e(this).closest("tr").attr("data-order");o+='<input type="hidden" name="change_q_order['+t+']" value="'+n+'" />'}),u="",n.data("noti")?(bootbox.confirm({message:n.data("noti"),className:"modal-warning",buttons:{confirm:{label:confirmYes},cancel:{label:confirmNo}},callback:function(i){i?(e("#sorted_question").html(a),e("#sorted_question").append(o),t[0].submit()):(n.prop("disabled",!1),e("#sorted_question").html(""))}}),!1):(e("#sorted_question").html(a),e("#sorted_question").append(o),!0)}),e("#group_types").change(function(){var t=e(this).find("option:selected");e(".data-target").prop("disabled",!0).val(""),e(t.attr("data-target")).prop("disabled",!1),e(".data-target").select2(),e("#subjects-error").remove()}),e.validator.addMethod("compareTime",function(e,t,n){if(l())return!0},time_end_must_greater_than_time_start),jQuery.extend(jQuery.validator.messages,{required:requiredText}),e(".validate_form").validate({ignore:".ignore",rules:{name:{required:!0},time:{required:!0,number:!0,min:1},type_id:{required:!0},total_question:{required:function(){return e("#check_total").is(":checked")},number:!0,min:1},time_start:{required:function(){return e("#set_time").is(":checked")}},time_end:{required:function(){return e("#set_time").is(":checked")},compareTime:!0},min_point:{required:function(){return e("#set_min_point").is(":checked")},number:!0,min:1,max:function(){return e("#set_min_point").is(":checked")&&"undefined"!==totalQuestion?totalQuestion:null}}},errorPlacement:function(t,n){var a=n.attr("name");"total_question"==a?e(".test-tabs ul li:eq(2) a").click():e(".test-tabs ul li:first a").click(),"time_start"==a||"time_end"==a?t.insertAfter(n.closest(".input-group")):t.insertAfter(n)}}),e(".test-tabs ul li a").on("click",function(){var t=e(this).attr("href");"#general_tab"==t&&selectSearchReload(),RKSession.setItem("test_edit_tab",t)}),e(".check_all").length>0&&(e(".check_item").length>0&&e(".check_item:checked").length==e(".check_item").length?e(".check_all").prop("checked",!0):e(".check_all").prop("checked",!1),e(".check_all").on("change",function(){e(this).is(":checked")?(e(".check_item").prop("checked",!0),e("#select_q_error").addClass("hidden")):e(".check_item").prop("checked",!1)}),e("body").on("change",".check_item",function(){var t=e(".check_item").length;e(".check_all").prop("checked",e(".check_item:checked").length===t),e("#select_q_error").addClass("hidden");var n=e(this).closest("tr"),a=e(this).is(":checked");if(n.hasClass("tr-parent")){var i=n.parent().find('.tr-child[data-id="'+n.data("id")+'"]');i.find(".check_item").prop("checked",a)}})),e("#btn_export_result").click(function(t){t.preventDefault();var n=e(this).closest("form"),a=(e(this),e("#tbl_test_result tr .check_item:checked")),i=e(this).data("url"),n=document.createElement("form");n.setAttribute("method","post"),n.setAttribute("action",i);var o=document.createElement("input");o.setAttribute("type","hidden"),o.setAttribute("name","_token"),o.setAttribute("value",_token),n.appendChild(o),a.each(function(){var t=document.createElement("input");t.setAttribute("type","hidden"),t.setAttribute("name","result_ids[]"),t.setAttribute("value",e(this).val()),n.appendChild(t)}),document.body.appendChild(n),n.submit(),n.remove()}),e("#btn_mass_del").click(function(t){t.preventDefault();var n=e(this),a=n.data("url"),i=e(".check_item:checked");if(i.length<1)return bootbox.alert({className:"modal-danger",message:textNoneItemSelected,buttons:{ok:{label:confirmYes}}}),!1;var o=[];i.each(function(){o.push(e(this).val())}),bootbox.confirm({className:"modal-warning",message:n.data("noti"),buttons:{confirm:{label:confirmYes},cancel:{label:confirmNo}},callback:function(t){t&&e.ajax({url:a,type:"POST",data:{item_ids:o,_token:_token},success:function(e){window.location.reload()},error:function(e){window.location.reload()}})}})}),e(".m_action_btn").on("click",function(t){t.preventDefault();var n=e(this),a=n.attr("href"),i=n.attr("action"),o=e(".check_item:checked");if(o.length<1)return bootbox.alert({className:"modal-danger",message:textNoneItemSelected,buttons:{ok:{label:confirmYes}}}),!1;var r=[];o.each(function(){r.push(e(this).val())}),bootbox.confirm({className:"modal-warning",message:n.data("noti"),buttons:{confirm:{label:confirmYes},cancel:{label:confirmNo}},callback:function(t){t&&e.ajax({url:a,type:"POST",data:{action:i,item_ids:r,_token:_token},success:function(e){window.location.reload()},error:function(e){window.location.reload()}})}})});var h=e(".editor_question_content");if("undefined"!=typeof CKEDITOR&&h.length>0){var f={toolbar:[{name:"basicstyles",groups:["basicstyles","cleanup"],items:["Bold","Italic","Underline"]},{name:"clipboard",groups:["clipboard","undo"],items:["Cut","Copy","Paste","Undo","Redo"]},{name:"links",items:["Link","Unlink"]},{name:"insert",items:["HorizontalRule","SpecialChar","Rkimage","Rkaudio"]},{name:"styles",items:["Format","FontSize"]},{name:"colors",items:["TextColor","BGColor"]},{name:"tools",items:["Maximize","ShowBlocks"]},"/",{name:"paragraph",groups:["list","indent","blocks","align"],items:["NumberedList","BulletedList","-","Blockquote","-","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock"]},{name:"document",groups:["mode"],items:["Mathjax","CodeSnippet","-","Source"]}],tabSpaces:4,extraPlugins:"justify,colorbutton,codesnippet,mathjax,font,image2,rkimg,html5video,widget,widgetselection",height:"200px",mathJaxLib:"https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.0/MathJax.js?config=TeX-AMS_HTML",extraConfig:{_token:_token,urlUploadImage:_urlUploadImage}};h.each(function(){var t=e(this).attr("rows");f.height=40*t,CKEDITOR.replace(e(this).attr("id"),f)})}if(e("body").on("click",".btn-close-modal",function(t){t.preventDefault();var n=e(this).closest(".rk-modal");n.fadeOut(300)}),e("body").on("click",".btn-delete-question",function(t){t.preventDefault();var n=e(this).data("id"),a=e("#question_"+n),i=e(this);bootbox.confirm({message:text_confirm_delete,className:"modal-danger",buttons:{confirm:{label:confirmYes},cancel:{label:confirmNo}},callback:function(t){if(t){i.prop("disabled",!0),a.css("background","#f9eeb6");var n=e(".table-multiple-choice").DataTable();n.row(a).remove().draw(),initDataTableFilter(e(".table-multiple-choice").dataTable()),e(".table-multiple-choice .q_item").each(function(t){e(this).find(".q_order .num").text(t+1)})}else i.prop("disabled",!1)}})}),e("body").on("click",".btn-delete-written-question",function(t){t.preventDefault();var n=e(this).data("url"),a=e(this).data("id"),i=e("#question_"+a),o=e(this);n&&bootbox.confirm({message:text_confirm_delete,className:"modal-danger",buttons:{confirm:{label:confirmYes},cancel:{label:confirmNo}},callback:function(t){t?(o.prop("disabled",!0),i.css("background","#f9eeb6"),e.ajax({url:n,type:"delete",data:{_token:_token},success:function(){var t=e(".table-written-questions").DataTable();t.row(i).remove().draw(),initDataTableFilter(e(".table-written-questions").dataTable()),e(".table-written-questions .q_item").each(function(t){e(this).find(".q_order .num").text(t+1)})},error:function(e){i.removeAttr("style"),showModalError(e.responseJSON)},complete:function(){o.prop("disabled",!1)}})):o.prop("disabled",!1)}})}),"undefined"!=typeof hljs&&hljs.initHighlightingOnLoad(),selectSearchReload(),e("#edit_question_window").length>0){var _={toolbar:[{name:"insert",items:["SpecialChar","Rkimage"]},{name:"colors",items:["TextColor","BGColor"]},{name:"document",groups:["mode"],items:["CodeSnippet","-","Source"]}],extraPlugins:"codesnippet,image2,rkimg",height:"100px",enterMode:CKEDITOR.ENTER_BR,extraConfig:{_token:_token,urlUploadImage:_urlUploadImage}};isType1||e(".ans-content-col .ans_box textarea").each(function(){CKEDITOR.replace(e(this).attr("id"),_)}),e(".btn-add-answer").click(function(t){t.preventDefault(),e("#a-required-error").addClass("hidden");var n=e(".ans-content-col .ans_box").length,a="new_"+n,i=e("#ans_box_tpl").clone().removeAttr("id").attr("data-new",a),o="";if(isType1)i.find(".aw_label").html("");else{var r=e(".ans-content-col .ans_box:last .aw_label");r=r.find("input").length>0?r.find("input").val().trim()[0]:r.text().trim()[0],r||(r="@"),o=String.fromCharCode(r.charCodeAt(0)+1)}if(i.find(".aw_label input").attr("name","answers_new["+n+"][label]").val(o),i.find("textarea").attr("id","answer_new_"+n).html("").attr("name","answers_new["+n+"][content]"),i.appendTo(".ans-content-col"),isType2)e(".ans-check-col select").append('<option value="'+a+'" data-new="'+a+'">'+o+"</option>"),e("#ans_type2_tpl select").append('<option value="'+a+'" data-new="'+a+'">'+o+"</option>"),CKEDITOR.replace("answer_new_"+n,_);else if(!isType1){CKEDITOR.replace("answer_new_"+n,_);var s="radio";e("#check_multi_choice").is(":checked")&&(s="checkbox");var l='<div class="ans_check" data-new="'+a+'"><label><input type="'+s+'" name="answers_new_correct[]" value="'+n+'"> <span class="ans_label">'+o+"</span></label></div>";e(".ans-check-col").append(l)}}),e("body").on("click",".btn-del-answer",function(t){if(t.preventDefault(),1!=e(".ans-content-col .ans_box").length){var n=e(this).closest(".ans_box"),a=n.attr("data-new");isType2?(e('.ans-check-col select option[data-new="'+a+'"]').remove(),e('#ans_type2_tpl select option[data-new="'+a+'"]').remove()):e('.ans-check-col .ans_check[data-new="'+a+'"]').remove(),n.remove()}}),e("body").on("change",".ans_box .aw_label input",function(){var t=e(this).closest(".ans_box").attr("data-new");e('.ans-check-col select option[data-new="'+t+'"]').text(e(this).val()),e('.ans-check-col div[data-new="'+t+'"] .ans_label').text(e(this).val()),e("#a-label-error").addClass("hidden")}),e("body").on("click",".btn-add-qchild",function(t){t.preventDefault();var n=e("#ans_type2_tpl").clone().removeAttr("id"),a=e(".ans-check-col .child_num").length;n.find(".child_num").text(a+1),n.find("select").attr("name","answers_new_correct["+(a+1)+"]").val("");var i=n.find("textarea");i.attr("id","edit_question_content_new_"+(a+1)).attr("name","childs_new_content["+(a+1)+"]"),n.appendTo(".ans-check-col"),f.height=120,CKEDITOR.replace("edit_question_content_new_"+(a+1),f)}),e("body").on("click",".btn-del-qchild",function(t){t.preventDefault(),1!=e(".qchild-box").length&&(e(this).closest(".qchild-box").remove(),e(".qchild-box").each(function(t){e(this).find(".child_num").text(t+1)}))}),e("#form_edit_question").submit(function(){var t=e(this).find('button[type="submit"]');if(e(".ans-content-col .ans_box").length<1)return e("#a-required-error").removeClass("hidden"),t.prop("disabled",!1),!1;var n=!1,a=[{element:".editor_question_content",errorElm:"#q-content-error",extraFunc:function(){return e(".editor_question_content").each(function(){var t=e(this).attr("id");CKEDITOR.instances[t].updateElement()}),CKEDITOR.instances.edit_question_content.on("change",function(){e("#q-content-error").addClass("hidden")}),!0}},{element:".ans-content-col .aw_label input",errorElm:"#a-label-error",extraFunc:function(){var t=[],n=e("#a-label-exists-error"),a=!1;return e(".ans-content-col .aw_label input").each(function(){t.indexOf(e(this).val())==-1?t.push(e(this).val()):a||(a=!0)}),a?n.removeClass("hidden"):n.addClass("hidden"),!a}},{element:".ans-content-col .ans_box textarea",errorElm:"#a-content-error",extraFunc:function(){e(".ans_box textarea").each(function(){if("undefined"!=typeof e(this).attr("id")){var t=e(this).attr("id");"undefined"!=typeof CKEDITOR.instances[t]?(CKEDITOR.instances[t].updateElement(),CKEDITOR.instances[t].on("change",function(){e("#a-content-error").addClass("hidden")})):e(".ans-content-col .ans_box textarea").on("change",function(){e("#a-content-error").addClass("hidden")})}return!0})}}];isType2?a.push({element:".ans-check-col select",errorElm:"#ans-select-error"}):isType1||a.push({element:".ans-check-col .ans_check input",errorElm:"#ans-select-error"});var n=!1;for(var i in a){var o=a[i];if("undefined"!=typeof o.extraFunc&&"undefined"!=typeof o.extraFunc()&&!o.extraFunc())return n=!0,t.prop("disabled",!1),!n;var r=!1;if(".ans-check-col .ans_check input"===o.element)e(o.element+":checked").length<1&&(r=!0);else if(".editor_question_content"===o.element){var s=!0;e(o.element).each(function(){e(this).val().trim()&&(s=!1)}),r=s}else e(o.element).each(function(){e(this).val().trim()||(r=!0)});r?(e(o.errorElm).removeClass("hidden"),n=!0):e(o.errorElm).addClass("hidden"),e(o.element).on("change",function(){e(o.errorElm).addClass("hidden")})}return n||isType1||e(".ans-check-col select").length<1&&e(".ans-check-col .ans_check input").length<1&&(e("#ans-select-error").removeClass("hidden"),n=!0),n&&t.prop("disabled",!1),!!isType4||!n}),CKEDITOR.instances.edit_question_content.on("change",function(){e("#q-content-error").addClass("hidden")}),e("#btn_close_window").click(function(){window.close()})}e(".btn-create-question").click(function(){tempEditQuestion={id:null,order:null};var t=e(this),n=c(t.data("url"),.8*e(window).width(),.9*e(window).height());e(window).on("beforeunload",function(){n.close()})}),"undefined"!=typeof isEdit&&isEdit&&e(".table-multiple-choice .check_all").click(),e("body").on("change","#question_change_lang",function(){var t=e(this).attr("data-url");if(t){var n=document.createElement("form");n.setAttribute("method","get"),n.setAttribute("action",t);var a=[],i=RKfuncion.general.paramsFromUrl();i.lang=e(this).val(),e.each(i,function(e,t){a.push({name:e,value:t})});for(var o in a){var r=a[o],s=document.createElement("input");s.setAttribute("type","hidden"),s.setAttribute("name",r.name),s.setAttribute("value",r.value),n.appendChild(s)}document.body.appendChild(n),n.submit(),n.remove()}}),e("body").on("change","#question_type",function(){if("undefined"==e(this).data("url"))return!1;var t=document.createElement("form");t.setAttribute("method","post"),t.setAttribute("action",e(this).data("url"));var n=[{name:"question[type]",value:e(this).val()},{name:"id",value:e(this).data("id")},{name:"_token",value:_token}];for(var a in n){var i=n[a],o=document.createElement("input");o.setAttribute("type","hidden"),o.setAttribute("name",i.name),o.setAttribute("value",i.value),t.appendChild(o)}document.body.appendChild(t),t.submit(),t.remove()}),e("body").on("click",".btn-popup",function(){var t=e(this).closest("tr");tempEditQuestion={id:t.attr("data-id"),order:t.find(".q_order .num").text()};var n=c(e(this).data("url"),.8*e(window).width(),.9*e(window).height());e(window).on("beforeunload",function(){n.close()})}),e(".btn-show-content").click(function(){var t=e(this).prev("strong").text(),n=e(this).next(".q_content"),a=e("#modal_detail_question");a.modal("show"),a.find(".q_num").text(t),a.find(".modal-body").html(n[0].outerHTML),a.find(".q_content").removeClass("hidden")});var b=e("#modal_copy_to");b.on("show.bs.modal",function(){if(e("#list_question .check_item:checked").length<1)return bootbox.alert({className:"modal-danger",message:textNoneItemSelected,buttons:{ok:{label:confirmYes}}}),!1;e("#submit_copy").prop("disabled",!0),e("#select_search_test").val("");var t=e("#select_search_test").data("remote-url");e("#select_search_test").select2({minimumInputLength:0,ajax:{url:t,dataType:"json",delay:250,data:function(e){return{q:e.term,page:e.page}},processResults:function(e,t){return t.page=t.page||1,{results:e.items,pagination:{more:20*t.page<e.total_count}}},cache:!0}})}),e("#select_search_test").on("change",function(){e(this).val()?e("#submit_copy").prop("disabled",!1):e("#submit_copy").prop("disabled",!0)}),e("#submit_copy").click(function(t){t.preventDefault();var n=e("#select_search_test").val();if(!n)return!1;var a=[];if(list_question.find(".q_item .check_item:checked").each(function(){a.push(e(this).val())}),a.length<1)return bootbox.alert({message:textNoneItemSelected,className:"modal-danger",buttons:{ok:{label:confirmYes}}}),!1;var i=e(this).data("noti"),o=e(this),r=e("#modal_copy_to");bootbox.confirm({message:i,className:"modal-warning",buttons:{confirm:{label:confirmYes},cancel:{label:confirmNo}},callback:function(t){t?(o.prop("disabled",!0),r.addClass("importing"),e.ajax({url:o.data("url"),type:"post",data:{_token:_token,question_ids:a,test_id:n,option_copy:e('input[name="option_copy"]:checked').val()},success:function(e){bootbox.alert({message:e.message,className:"modal-success",buttons:{ok:{label:confirmYes}}})},error:function(e){bootbox.alert({message:e.responseJSON.message,className:"modal-danger",buttons:{ok:{label:confirmYes}}})},complete:function(){o.prop("disabled",!1),r.removeClass("importing")}})):o.prop("disabled",!1)}})}),e("#btn_export_question").click(function(t){t.preventDefault();var n=list_question.find(".q_item .check_item:checked"),a=written_question.find(".q_item .check_item:checked");if(n.length<1&&a<1)return bootbox.alert({message:textNoneItemSelected,className:"modal-danger",buttons:{ok:{label:confirmYes}}}),!1;var i=e(this).data("url"),o=document.createElement("form");o.setAttribute("method","post"),o.setAttribute("action",i);var r=document.createElement("input");r.setAttribute("type","hidden"),r.setAttribute("name","_token"),r.setAttribute("value",_token),o.appendChild(r),n.each(function(){var t=document.createElement("input");t.setAttribute("type","hidden"),t.setAttribute("name","questions[]"),t.setAttribute("value",e(this).val()),o.appendChild(t)}),a.each(function(){var t=document.createElement("input");t.setAttribute("name","written[]"),t.setAttribute("value",e(this).val()),o.appendChild(t)}),document.body.appendChild(o),o.submit()}),e("#check_multi_choice").on("change",function(){var t=e(".ans-check-col .ans_check input");e(this).is(":checked")?t.attr("type","checkbox"):t.attr("type","radio")}),e("body").on("click",".q_view_more",function(t){t.preventDefault();var n=e(this).closest(".q_content_toggle"),a=e(this).data("fullText"),i=e(this).data("shortText");n.hasClass("q_show")?(n.removeClass("q_show"),e(this).text("["+a+"]")):(n.addClass("q_show"),e(this).text("["+i+"]"))}),e("body").on("click",".btn-reset-random",function(t){t.preventDefault();var n=e(this),a=e("#list_test_tbl .check_item:checked");return a.length<1?(bootbox.alert({className:"modal-danger",message:textNoneItemSelected,buttons:{ok:{label:confirmYes}}}),!1):void bootbox.confirm({className:"modal-warning",message:n.data("noti"),buttons:{confirm:{label:confirmYes},cancel:{label:confirmNo}},callback:function(t){if(t){var i=document.createElement("form");i.setAttribute("method","post"),i.setAttribute("action",n.data("url")),a.each(function(){var t=document.createElement("input");t.setAttribute("type","hidden"),t.setAttribute("name","test_ids[]"),t.setAttribute("value",e(this).val()),i.appendChild(t)});var o=document.createElement("input");o.setAttribute("type","hidden"),o.setAttribute("name","_token"),o.setAttribute("value",_token),i.appendChild(o),document.body.appendChild(i),i.submit(),i.remove()}}})}),setDisabledCheckTotal(e("#check_total").is(":checked")),e(document).ready(function(){"undefined"!=typeof e.fn.DataTable&&(e(".table-multiple-choice").DataTable(optQuestionDataTable),e(".table-written-questions").DataTable(optQuestionDataTable))}),e("body").on("click","#btn_reset_filter",function(t){t.preventDefault(),e(".table-multiple-choice").DataTable().search("").columns().search("").draw(),e(".thead-filter select").each(function(){e(this).val("").trigger("change.select2")})}),e("body").on("click",".add-type-box .btn-add-type-cat",function(t){
t.preventDefault();var n=e(this),a=n.closest(".add-type-box"),i=a.find(".type-cat-new-box");return i.removeClass("hidden"),i.find(".cat_name").focus(),n.addClass("hidden"),!1}),e("body").on("keypress",".add-type-box .cat_name",function(t){var n=t.keyCode||t.which;if(n==e.ui.keyCode.ENTER)return e(this).closest(".add-type-box").find(".btn-submit-cat").trigger("click"),t.preventDefault(),!1}),e("body").on("click",".add-type-box .btn-submit-cat",function(){var t=e(this),n=t.closest(".add-type-box"),a=n.find(".type-cat-new-box"),i=a.find(".form-add-cat"),o=n.find(".btn-add-type-cat"),r=n.prev(".form-group").find("select");return""!=i.find(".cat_name").val().trim()&&(!t.is(":disabled")&&(t.prop("disabled",!0),e.ajax({type:"POST",url:i.data("url"),data:{_token:_token,cat:{name:i.find(".cat_name").val(),type_cat:i.find(".type_cat").val()},question_id:i.find(".question_id").val(),test_id:i.find(".test_id").val(),lang:currentLangEdit},success:function(e){e&&(r.find('option[value="'+e.id+'"]').length<1&&r.append('<option value="'+e.id+'">'+e.name+"</option>"),r.val(e.id).trigger("change")),i.find(".cat_name").val(""),a.addClass("hidden"),o.removeClass("hidden")},complete:function(){t.prop("disabled",!1)}}),!1))}),e("body").on("click",".td-link .link-box",function(){if(window.getSelection){var e=window.getSelection(),t=document.createRange();t.selectNodeContents(this),e.removeAllRanges(),e.addRange(t)}else if(document.body.createTextRange){var t=document.body.createTextRange();t.moveToElementText(this),t.select()}})}(jQuery);var modal_warning=$("#modal-warning-notification"),modal_confirm=$("#modal-delete-confirm");$("#check_total").change(function(){var e=$(this).is(":checked");setDisabledCheckTotal(e)}),$(".time-group").each(function(){$(this).datetimepicker({ignoreReadonly:!0,format:"YYYY/MM/DD HH:mm"})}),$("#set_time").change(function(){$(this).is(":checked")?$("#time-from-to").removeClass("hidden"):($("#time-from-to").addClass("hidden"),$("#time-from-to input.error").removeClass("error"),$("#time-from-to .input-group").next(".error").remove())}),$("#set_min_point").change(function(){$(this).is(":checked")?$("#min_point").removeClass("hidden"):$("#min_point").addClass("hidden")}),$(document).on("change",'input[name="thumbnail"]',function(e){if(e.target.files&&e.target.files[0]){var t=e.target.files[0],n=new FileReader,a=$(".img-thumbnail"),i=$(this);if(imageAllows.indexOf(t.type)===-1)return alert(errorFileNotAllow),oldThumbnail?a.attr("src",oldThumbnail):a.parent().remove(),void i.val("");n.onload=function(e){if(0!==a.length)$(a).attr("src",e.target.result);else{var t='<div class="form-group"><img src="'+e.target.result+'" alt="thumbnail" class="img-bordered-sm img-responsive img-thumbnail" width="100" height="100"></div>';i.parent().after(t)}},n.readAsDataURL(t)}});