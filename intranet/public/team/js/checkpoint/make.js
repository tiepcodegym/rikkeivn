function selectRank(t){$(t).parent().find(".container-question-child").removeClass("point-rank").removeClass("btn-primary"),$(t).addClass("point-rank").addClass("btn-primary"),$(".total-point").html(getTotalPoint()),setCookie()}function setCookie(){var t=($("#team_id_result").val(),$("#checkpoint_id").val()),o=$("#emp_id").val(),e=$("#proposed").val(),n=parseFloat(getTotalPoint()),i=[],a="proposed_current["+t+"]["+o+"]";$("#team_id_result").val();$(".container-question-child.point-rank").each(function(){var t=parseInt($(this).data("rank")),o=$(this).data("questionid"),e=$(".comment-question[data-questionid='"+o+"']").val();i.push([o,t,e])}),$.removeCookie("checkpoint_current["+t+"]["+o+"]"),$.removeCookie("totalPoint_current["+t+"]["+o+"]"),$.removeCookie(a),$.cookie.json=!0,$.cookie("checkpoint_current["+t+"]["+o+"]",i,{expires:7}),$.cookie("totalPoint_current["+t+"]["+o+"]",n,{expires:7}),$.cookie(a,funcJsonValue.encode(e),{expires:7})}function getTotalPoint(){var t=0;return $(".container-question").each(function(){if($(this).find(".container-question-child.point-rank").length>0){var o=$(this).find(".container-question-child.point-rank").data("rank"),e=$(this).find(".container-question-child.point-rank").data("weight");t+=parseFloat(o*e/100)}}),t.toFixed(2)}function confirm(){var t=!1;return $("#modal-confirm").modal("hide"),$(".container-question").each(function(){$(this).find(".container-question-child").length>0&&$(this).find(".container-question-child.point-rank").length<=0&&(t=!0)}),t?($("#modal-confirm").modal("show"),!1):($("#modal-submit .modal-body").html("Số điểm hiện tại là "+getTotalPoint()+". Bạn có chắc chắn muốn hoàn thành bài Checkpoint?"),void $("#modal-submit").modal("show"))}function save(t){var o=getTotalPoint(),e=$("#proposed").val(),n=[],i=$("#team_id_result").val();$(".container-question-child.point-rank").each(function(){var t=parseInt($(this).data("rank")),o=$(this).data("questionid"),e=$(".comment-question[data-questionid='"+o+"']").val();n.push([o,t,e])}),$.ajax({url:baseUrl+"team/checkpoint/temporary_save",dataType:"html",data:{arrayQuestion:n,totalPoint:o,proposed:e,id:t,team_id:i}}).done(function(){$(this).addClass("done")})}function submit(t,o){var e=getTotalPoint(),n=$("#proposed").val(),i=$("#team_id_result").val(),a=[];$(".container-question-child.point-rank").each(function(){var t=parseInt($(this).data("rank")),o=$(this).data("questionid"),e=$(".comment-question[data-questionid='"+o+"']").val();a.push([o,t,e])}),$(".apply-click-modal").show(),$.ajax({url:urlSubmit,type:"post",dataType:"html",data:{_token:t,arrayQuestion:a,totalPoint:e,proposed:n,id:o,team_id:i}}).done(function(t){$(".comment-question").val(""),$(".proposed").val(""),makeNewTurn($("#checkpoint_id").val(),$("#emp_id").val()),location.href=urlSuccessPage}).fail(function(){alert("Ajax failed to fetch data")})}function removeItem(t,o,e){document.cookie=encodeURIComponent(t)+"=; expires=Thu, 01 Jan 1970 00:00:00 GMT"+(e?"; domain="+e:"")+(o?"; path="+o:"")}function makeNewTurn(t,o){var e="checkpoint_current["+t+"]["+o+"]",n="proposed_current["+t+"]["+o+"]",i="totalPoint_current["+t+"]["+o+"]";removeItem(e),removeItem(n),removeItem(i),location.reload()}fixHeight(),$(window).resize(function(){fixHeight()}),jQuery(document).ready(function(t){var o,e=t("#checkpoint_id").val(),n=t("#emp_id").val();if(t.cookie("proposed_current["+e+"]["+n+"]")&&t("#proposed").val(funcJsonValue.decode(t.cookie("proposed_current["+e+"]["+n+"]"))),t.cookie("checkpoint_current["+e+"]["+n+"]")){o=JSON.parse(t.cookie("checkpoint_current["+e+"]["+n+"]"));for(var i=0;i<o.length;i++)t('div[data-questionid="'+o[i][0]+'"]').removeClass("point-rank").removeClass("btn-primary"),t('div[data-questionid="'+o[i][0]+'"][data-rank="'+o[i][1]+'"]').addClass("point-rank").addClass("btn-primary"),t('input[data-questionid="'+o[i][0]+'"]').val(o[i][2]);t(".total-point").html(getTotalPoint())}t("input").keyup(function(){setCookie()}),t("textarea").keyup(function(){setCookie()}),hoverHelp()}),$(window).load(function(){$(".se-pre-con").fadeOut("slow")});var funcJsonValue={encode:function(t){return{1:t}},decode:function(t){return t?JSON.parse(t)[1]:null}};