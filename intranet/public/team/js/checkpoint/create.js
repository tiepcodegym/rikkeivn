function setSelect(){var e=$(".evaluator-row:last").attr("row");e=e?parseInt(e)+1:1;var t=$(".add-evaluator-row").html();$(".btn-container").before('<div class="row evaluator-row evaluator-row-'+e+'" row="'+e+'">'+t+"</div>"),$(".evaluator-row-"+e).find("#evaluated").attr("row",e);var a=[];$(".evaluator-row").each(function(){var e=$(this).find("#evaluated"),t=e.val();if(t)for(var r=0;r<t.length;r++)a.push(t[r])}),a=unique(a);for(var r=0;r<a.length;r++)$(".evaluator-row-"+e+" #evaluated option[value="+a[r]+"]").prop("disabled",!0);$(".evaluator-row-"+e+" #evaluator").select2(),refreshMulti($(".evaluator-row-"+e+" #evaluated")),$(".evaluator-row-"+e+" .multiselect-container li input").attr("row",e),$(".evaluator-row-"+e+" #evaluator").attr("row",e),$(".evaluator-row-"+e+" #evaluator").attr("name","evaluator["+e+"]"),$(".evaluator-row-"+e+" #evaluated").attr("name","evaluated["+e+"][]"),$(".evaluator-row-"+e+" .btn-delete-row").attr("row",e),removeButtonDel()}function unique(e){var t=[];return $.each(e,function(e,a){$.inArray(a,t)==-1&&t.push(a)}),t}function refreshMulti(e){e.multiselect("destroy").multiselect({enableFiltering:!0,numberDisplayed:3,nSelectedText:"person",maxHeight:300}).multiselect("refresh")}function submitLoading(){$(".btn-create").prop("disabled",!0),$(".btn-create i").removeClass("hidden")}function submitLoaded(){$(".btn-create").prop("disabled",!1),$(".btn-create i").addClass("hidden")}function removeButtonDel(){1==$(".evaluator-row").length&&$(".evaluator-row .btn-delete-row").remove()}function validateHtml(e){var t=e.val(),a=$("#error_append");0==parseInt(t)||""==t||null==t?(e.after(a.html()),e.parent().find("label.error").show().html(requiredText)):e.parent().find("label.error").remove()}jQuery(document).ready(function(e){selectSearchReload()});var $startMonth=$("#start_date"),$endMonth=$("#end_date");$startMonth.datepicker({format:"yyyy/mm/dd",autoclose:!0,endDate:$endMonth.val(),todayHighlight:!0}).on("changeDate",function(){$endMonth.datepicker("setStartDate",$startMonth.val())}),$endMonth.datepicker({format:"yyyy/mm/dd",autoclose:!0,startDate:$startMonth.val(),todayHighlight:!0}).on("changeDate",function(){$startMonth.datepicker("setEndDate",$endMonth.val())}),$("input").iCheck({checkboxClass:"icheckbox_minimal-blue",radioClass:"iradio_minimal-blue"}),$("#rikker_relate").keydown(function(e){var t=e.keyCode||e.which,a=$.trim($(this).val());if(8===t&&""===a)backSpace("rikker_relate");else if(9===t||13===t||188===t||186===t){var a=$.trim($("#rikker_relate").val());if((void 0===ajaxLoadingEmail["#rikker_relate"]||ajaxLoadingEmail["#rikker_relate"])&&void 0!==ajaxLoadingEmail["#rikker_relate"])return!1;""!==a&&(tabEvent("#rikker_relate",a),e.preventDefault())}else if(38===t||40===t)selectUpDown("rikker_relate",t);else{"undefined"!=typeof ajax_request&&ajax_request.abort();var r="";8!==t?(r=String.fromCharCode(e.keyCode),a+=r):a=a.slice(0,-1),ajaxLoadingEmail["#rikker_relate"]=!0,showList("#rikker_relate",a)}}),$("#frm_create_checkpoint").on("keyup keypress",function(e){var t=e.keyCode||e.which;if(13===t)return e.preventDefault(),!1}),$("#rikker_relate").blur(function(e){$().isClickWithoutDom({container:$(this),except:$(this).siblings(".rikker-result")})&&(value=$.trim($("#rikker_relate").val()),""!==value&&(tabEvent("#rikker_relate",value,{setText:1}),e.preventDefault()))}),$(document).ready(function(){$(".evaluator-row").each(function(){console.log("1"),$(this).find("#evaluator").select2(),refreshMulti($(this).find("#evaluated"));var e=$(this).find("#evaluated").attr("row");$(".evaluator-row-"+e+" .multiselect-container li input").attr("row",e)})}),$("#set_team").on("change",function(){submitLoading();var e=$(this).val();if($(".evaluator-row").remove(),$(".add-evaluator-row #evaluator").find("option:gt(0)").remove(),0==e)return $(".add-evaluator-row").addClass("hidden"),$(".evaluator-row").remove(),!1;var t=$("#start_date").val(),a=$("#end_date").val();return t.length>0&&a.length>0&&void $.ajax({url:urlSetEmp,type:"post",data:{_token:token,teamId:e,start_date:t,end_date:a},success:function(e){submitLoaded();var t="",a="";if($(".add-evaluator").removeClass("hidden"),0==e);else{for(var r=0;r<e.empOfTeam.length;r++)t+='<option value="'+e.empOfTeam[r].id+'">'+e.empOfTeam[r].nickname+"</option>";for(var r=0;r<e.empAll.length;r++)a+='<option value="'+e.empAll[r].id+'">'+e.empAll[r].nickname+"</option>"}$(".add-evaluator-row #evaluator").append(a),$(".add-evaluator-row #evaluated").html(t),setSelect()},fail:function(){submitLoaded(),alert("Ajax failed to fetch data")}})}),$(".add-evaluator").on("click",function(){setSelect()}),$(document).on("focus",".select2-container",function(){var e=$(this).find("span.select2-selection");if(e){var t=e.attr("aria-activedescendant");if(t){var a=t.lastIndexOf("-"),r=t.substring(a+1);console.log(r),$(this).parent().find("#evaluator").attr("old",r)}}}),$(document).on("change","#evaluator",function(){validateHtml($(this));var e=$(this).attr("old"),t=$(this).val(),a=$(this).attr("row");$("select[dataname=evaluator]").each(function(){var r=$(this).attr("row");r!=a&&($(this).find("option[value="+t+"]").prop("disabled",!0),$(this).find("option[value="+e+"]").prop("disabled",!1),null!=r&&$(this).select2())})}),$(document).on("change","#evaluated",function(){validateHtml($(this))}),$(document).on("change","input[type=checkbox]",function(){var e=$(this).val(),t=$(this).closest(".evaluated-container").find("#evaluated"),a=t.attr("row");this.checked?$("select[dataname=evaluated]").each(function(){var t=$(this).attr("row");console.log(t),t!=a&&null!=t&&($(this).find("option[value="+e+"]").prop("disabled",!0),$("input[type=checkbox][row="+t+"][value="+e+"]").prop("disabled",!0),$("input[type=checkbox][row="+t+"][value="+e+"]").parent("li").prop("disabled",!0))}):$("select[dataname=evaluated]").each(function(){var t=$(this).attr("row");t!=a&&null!=t&&($(this).find("option[value="+e+"]").prop("disabled",!1),$("input[type=checkbox][row="+t+"][value="+e+"]").prop("disabled",!1),$("input[type=checkbox][row="+t+"][value="+e+"]").parent().parent().parent().addClass("disabled"))})}),$(document).on("click",".btn-delete-row",function(){var e=$(this).attr("row"),t=$("select[dataname=evaluator][row="+e+"]"),a=$("select[dataname=evaluated][row="+e+"]"),r=a.val();$("select[dataname=evaluator]").each(function(){var a=$(this).attr("row");a!=e&&($(this).find("option[value="+t.val()+"]").prop("disabled",!1),null!=a&&$(this).select2())}),r&&$("select[dataname=evaluated]").each(function(){var t=$(this).attr("row");if(t!=e&&null!=t)for(var a=0;a<r.length;a++)$(this).find("option[value="+r[a]+"]").prop("disabled",!1),$("input[type=checkbox][row="+t+"][value="+r[a]+"]").prop("disabled",!1),$("input[type=checkbox][row="+t+"][value="+r[a]+"]").parent().parent().parent().removeClass("disabled")}),$(".evaluator-row-"+e).remove(),removeButtonDel()}),$(".date").change(function(){$("#start_date").val().length>0&&$("#end_date").val().length>0&&($("#team-checkpoint").removeClass("hidden"),$("#team-checkpoint").addClass("show"))}),$(".btn-create").click(function(){var e=!1,t=$("#error_append");if($("#frm_create_checkpoint label.error").remove(),"0"==$("#check_time").val()&&(e=!0,$("#check_time").after(t.html())),""==$("#start_date").val().trim()&&(e=!0,$("#start_date").after(t.html()),$("#start_date").parent().find("label.error").css("left","0")),""==$("#end_date").val().trim()&&(e=!0,$("#end_date").after(t.html()),$("#end_date").parent().find("label.error").css("left","0")),"0"==$("#set_team").val()&&(e=!0,$("#set_team").after(t.html())),$("[name^=evaluator]").each(function(){var a=$(this).val();null==a&&(e=!0,$(this).after(t.html()))}),$("[name^=evaluated]").each(function(){var a=$(this).val();null==a&&(e=!0,$(this).after(t.html()))}),""==$("#rikker_relate_validate").val().trim()&&(e=!0,$(".rikker-relate-container").after(t.html())),e)return $("#frm_create_checkpoint label.error").show().html(requiredText),$(".rikker-relate-container").parent().find("label.error").html(emailInvalid),$(".btn-create").removeAttr("disabled"),!1;var a=[];$("input[name^=rikker_relate][type=hidden]").each(function(){a.push($(this).val())});var r=[];$("[name^=evaluator]").each(function(){var e=$(this).attr("row"),t=$(this).val();null!=e&&e>0&&(r[e]=t)});var o=[];$("[name^=evaluated]").each(function(){var e=$(this).attr("row"),t=$(this).val();null!=e&&e>0&&(o[e]=t)}),submitLoading(),$.ajax({url:urlSave,type:"post",dataType:"html",data:{_token:token,start_date:$("#start_date").val(),end_date:$("#end_date").val(),check_time:$("#check_time").val(),set_team:$("#set_team").val(),rikker_relate:a,create_or_update:$("[name=create_or_update]").val(),checkpoint_id:$("[name=checkpoint_id]").val(),employee_id:$("#employee_id").val(),checkpoint_type_id:$("[name=checkpoint_type_id]:checked").val(),evaluator:r,evaluated:o},success:function(e){return e=JSON.parse(e),0===e.success?(RKExternal.notify(e.message_error,!1),location.reload(),!1):void(window.location.href=e.url)}}).fail(function(){submitLoaded(),alert("Ajax failed to fetch data")})});