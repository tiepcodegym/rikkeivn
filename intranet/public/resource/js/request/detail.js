function addChannel(e){$(".error-validate-"+e).remove(),$(".tr-"+e).removeClass("display-none"),$(".tr-add-"+e).addClass("display-none"),$(".slove-"+e+" .remove-"+e).removeClass("display-none"),$(".slove-"+e+" .add-"+e).addClass("display-none")}function deleteChannel(e,a,n){dataId=$(a).data("id"),dataClassName=e,dataUrl=n,$("#modal-delete-confirm-new").modal("show"),$(document).on("click","#modal-delete-confirm-new .btn-ok",function(){var a=$("input[name=request_id]").val();$("#modal-delete-confirm-new").modal("hide"),$(".edit-"+e+"-"+dataId).addClass("display-none"),$("#loading-item-"+dataId).removeClass("display-none"),data={_token:token,request_id:a,channel_id:dataId,isDelete:!0},$.ajax({url:dataUrl,type:"post",dataType:"json",data:data,success:function(a){a.status?($(".edit-"+e+"-"+dataId).removeClass("display-none"),$("#loading-item-1-"+dataId).addClass("display-none"),$(".workorder-"+e).html(a.content)):($("#modal-warning-notification .text-default").html(messageError),$("#modal-warning-notification").modal("show"))},error:function(){$("#modal-warning-notification .text-default").html(messageError),$("#modal-warning-notification").modal("show")},complete:function(){$("#modal-delete-confirm-new .btn-ok").data("requestRunning",!1)}})})}function editChannel(e,a){id=$(a).data("id"),$(a).addClass("display-none"),$(".save-"+e+"-"+id).removeClass("display-none"),$(".delete-"+e+"-"+id).addClass("display-none"),$(".refresh-"+e+"-"+id).removeClass("display-none"),setTimeout(function(){input=$(".input-topic-"+e+"-"+id),input.focus();var a=input.val();input.val(""),input.val(a)},0),$(".channel_id-"+e+"-"+id).addClass("display-none"),$(".url-"+e+"-"+id).addClass("display-none"),$(".input-url-"+e+"-"+id).removeClass("display-none"),$(".cost-"+e+"-"+id).addClass("display-none"),$(".input-cost-"+e+"-"+id).removeClass("display-none")}function saveChannel(e,a,n){$(a).data("requestRunning",!0);var t=$(a).data("id");$(a).addClass("display-none"),$("#loading-item-"+t).removeClass("display-none"),$(".error-validate-"+e+"-"+t).remove();var i=$(".select-channel_id-channel-"+t).val(),o=$(".input-url-"+e+"-"+t).val(),r=$(".input-cost-"+e+"-"+t).val(),d=$("input[name=request_id]").val(),l=$(".input-rc_id-"+e+"-"+t).val();data={_token:token,cost:r,url:o,channel_id:i,request_id:d,old_channel_id:t,rc_id:l},$.ajax({url:n,type:"post",dataType:"json",data:data,success:function(a){a.status?$(".workorder-"+e).html(a.content):a.message_error&&(a.message_error.url&&$(".input-url-"+e+"-"+t).after('<p class="word-break error-validate error-validate-'+e+"-"+t+" error-"+e+'" for="url">'+a.message_error.url[0]+"</p>"),a.message_error.cost&&$(".input-cost-"+e+"-"+t).after('<p class="word-break error-validate error-validate-'+e+"-"+t+" error-"+e+'" for="url">'+a.message_error.cost[0]+"</p>"),a.message_error.channel_id&&$(".select-channel_id-"+e+"-"+t).parent().append('<p class="word-break error-validate error-validate-'+e+"-"+t+" error-"+e+'" for="url">'+a.message_error.channel_id[0]+"</p>"))},error:function(){$("#modal-warning-notification .text-default").html(messageError),$("#modal-warning-notification").modal("show")},complete:function(){$(a).data("requestRunning",!1),$("#loading-item-"+t).addClass("display-none"),$(a).removeClass("display-none")}})}function removeChannel(e){$(".tr-"+e).addClass("display-none"),$(".tr-add-"+e).removeClass("display-none"),$(".url-"+e).text(""),$(".cost-"+e).text(""),$(".input-url-"+e).val(""),$(".input-cost-"+e).val(""),$(".select-channel_id-"+e).val($(".select-channel_id-"+e+" option:first").val()),$(".error-"+e).remove()}function addNewChannel(e,a,n,t){if(!$(a).data("requestRunning")){$(a).data("requestRunning",!0),$(a).addClass("display-none"),$("#loading-item").removeClass("display-none"),$(".error-validate-add-"+e).remove();var i=$("input[name=request_id]").val(),o=$(".select-channel_id-"+e).val(),r=$.trim($(".input-url-"+e).val()),d=$.trim($(".input-cost-"+e).val()),l={_token:token,request_id:i,channel_id:o,url:r,cost:d};$.ajax({url:n,type:"post",data:l,success:function(a){a.status?($(".slove-"+e+" .remove-"+e).addClass("display-none"),$(".slove-"+e+" .add-"+e).removeClass("display-none"),$(".workorder-"+e).html(a.content)):a.message_error&&(a.message_error.url&&$(".input-url-"+e).after('<p class="word-break error-validate error-validate-add-'+e+" error-"+e+'" for="url">'+a.message_error.url[0]+"</p>"),a.message_error.cost&&$(".input-cost-"+e).after('<p class="word-break error-validate error-validate-add-'+e+" error-"+e+'" for="url">'+a.message_error.cost[0]+"</p>"),a.message_error.channel_id&&$(".select-channel_id-"+e).after('<p class="word-break error-validate error-validate-add-'+e+" error-"+e+'" for="url">'+a.message_error.channel_id[0]+"</p>"))},error:function(){$("#modal-warning-notification .text-default").html(messageError),$("#modal-warning-notification").modal("show")},complete:function(){$(a).data("requestRunning",!1),$("#loading-item").addClass("display-none"),$(a).removeClass("display-none")}})}}function showTeam(){$("#modal-teams").modal("show")}function showContent(){$("#modal-content").modal("show")}function showNumberResourceInfo(){$("#modal-number-resource-info").modal("show")}function candidateDetail(e){window.open(baseUrl+"resource/candidate/detail/"+e,"_blank")}$.ajaxSetup({headers:{"X-CSRF-TOKEN":$('meta[name="_token"]').attr("content")}}),$("#approve").change(function(){var e=$(this).val();e==approveOff||e==approveYet?($("#type").parent().parent().addClass("hidden"),$("#recruiter").parent().parent().addClass("hidden")):($("#type").parent().parent().removeClass("hidden"),$("#type").val()==typeRecruit&&$("#recruiter").parent().parent().removeClass("hidden"))}),$("#type").change(function(){$(this).val()==typeRecruit?$("#recruiter").parent().parent().removeClass("hidden"):$("#recruiter").parent().parent().addClass("hidden")}),TYPE_CHANNEL=99,$(document).on("click",".add-channel",function(){addChannel("channel")}),$(document).on("click",".remove-channel",function(e){removeChannel("channel")}),$(document).on("click",".delete-channel",function(){deleteChannel("channel",this,urlAddChannel)}),$(document).on("click",".save-channel",function(){saveChannel("channel",this,urlAddChannel)}),$(document).on("click",".edit-channel",function(){id=$(this).data("id"),$(".tr-channel-"+id+" select").select2(),editChannel("channel",this)}),$(document).on("click",".add-new-channel",function(){addNewChannel("channel",this,urlAddChannel)}),$(document).on("click",".refresh-channel",function(){id=$(this).data("id");var e="channel";$(".tr-"+e+"-"+id+" .select2").addClass("display-none"),$(".channel_id-"+e+"-"+id).removeClass("display-none"),$(".select-channel_id-"+e+"-"+id).val($(".input-channel_id-"+e+"-"+id).val()),$(this).addClass("display-none"),$(".save-"+e+"-"+id).addClass("display-none"),$(".edit-"+e+"-"+id).removeClass("display-none"),$(".delete-"+e+"-"+id).removeClass("display-none"),$(".refresh-"+e+"-"+id).addClass("display-none"),$(".url-"+e+"-"+id).removeClass("display-none"),$(".input-url-"+e+"-"+id).addClass("display-none"),$(".cost-"+e+"-"+id).removeClass("display-none"),$(".input-cost-"+e+"-"+id).addClass("display-none"),$(".input-url-"+e+"-"+id).val($(".url-"+e+"-"+id).text()),$(".input-cost-"+e+"-"+id).val($(".cost-"+e+"-"+id).text()),$(".error-validate").remove()}),numberFormat($(".num")),$(document).ready(function(){RKfuncion.keepStatusTab.init(),$(".teams-container .btn-add-position").remove(),$(".teams-container .btn-danger").remove(),$(".teams-container .btn-delete-row").remove(),$(".teams-container .btn-add-team").remove(),$(".teams-container .team").prop("disabled",!0),$(".teams-container .position-apply").prop("disabled",!0),$(".teams-container .number-resource").prop("readonly",!0),$("#modal-teams .save-team").remove(),$("#modal-teams .modal-footer .btn").text("Close"),$("#recruiter").select2(),$("#type").select2(),$("#approve").select2(),$(".position-apply").select2(),selectSearchReload(),$(".teams-container .number-resource").each(function(){$parentObject=$(this).parent().parent(),$parentObject.addClass("col-md-6").removeClass("col-md-5")}),deadlineWarning&&$("#modal-deadline-warning").modal("show")}),$("#candidate-table").DataTable({processing:!0,lengthChange:!1,bFilter:!1,serverSide:!0,ajax:urlCandidateList,pageLength:10,columns:[{data:"id",name:"id"},{data:"email",name:"email"},{data:"fullname",name:"fullname"},{data:"team_name",name:"team_name"},{data:"positions",name:"positions"},{data:"team_selected",name:"team_selected"},{data:"position_apply",name:"position_apply"},{data:"recruiter",name:"recruiter"},{data:"programs_name",name:"programs_name"},{data:"status",name:"status"},{data:"type",name:"type"},{data:"test_mark",name:"test_mark"},{data:"specialize_score",name:"specialize_score"}],createdRow:function(e,a,n){$(e).addClass("cursor-pointer");var t=$(e).find("td:eq(0)").text();$(e).attr("onclick","candidateDetail("+t+");")}}),$(".btn-preview").on("click",function(){var e=$("div.request-info");$("#job_title").html(e.find(".title").text()),$("#job_expired").html(e.find(".deadline").text());var a=e.find(".description").text().split("\n"),n=e.find(".job_qualifi").text().split("\n"),t=e.find(".benefits").text().split("\n");$(".job_des").html(""),$(".job_qualification").html(""),$(".job_ben").html(""),$("#position").html("");var i;for(i=0;i<a.length;i++){var o=$.trim(a[i]);o&&$(".job_des").append('<li class="desc">'+o+"</li>")}for(i=0;i<n.length;i++){var o=$.trim(n[i]);o&&$(".job_qualification").append('<li class="qualification">'+o+"</li>")}for(i=0;i<t.length;i++){var o=$.trim(t[i]);o&&$(".job_ben").append('<li class="benefit">'+o+"</li>")}$(".teams-container .box-position").each(function(){var e=($(this).find("select.position-apply").val(),$(this).find(".number-resource").val()),a=$(this).find("select.position-apply option:selected").text();$("#position").append(a+"-"+e+", ")}),$("#location").html(e.find(".location").text()),$("#salary_modal").html(e.find(".salary").text()),$("#modal-preview").modal("show")}),$(".btn-success-preview").on("click",function(){var e=$("div#input_value"),a={title:e.find("#title").val(),position:a,request_id:requestId,expired:e.find("#deadline").val(),place:e.find("#location").val(),salary:e.find("#salary").val(),description:e.find("#description").val(),benefits:e.find("#benefits").val(),qualifications:e.find("#job_qualifi").val(),_token:_token,programs:e.find("#programs").val(),types:e.find("#types").val(),status_request:e.find("#status").val(),publish:!0};$(".teams-container .box-position").each(function(){var e=$(this).find("select.position-apply").val(),n=$(this).find(".number-resource").val();a["positions["+e+"]"]=n}),$.ajax({type:"POST",url:urlPostRequest,data:a,success:function(e){bootbox.alert({message:e,className:"modal-default"}),$(".btn-preview").text("Republish")},error:function(e){bootbox.alert({message:e.statusText,className:"modal-default"})}}),$("#modal-preview").modal("hide")});