jQuery(document).ready(function(e){e("input").iCheck({checkboxClass:"icheckbox_flat-green"}),e(".checkbox-all input").on("ifClicked",function(n){var t=n.target.checked;t?(e(".checkbox-body input").iCheck("uncheck"),e(".btn-delete-data").prop("disabled",!0)):(e(".checkbox-body input").iCheck("check"),e(".btn-delete-data").prop("disabled",!1))}),e(".checkbox-body input").on("ifChanged",function(n){var t=n.target.checked,a=e(".checkbox-body input:checkbox").length,d=e(".checkbox-body input:checked").length;if(t)e(".btn-delete-data").prop("disabled",!1),a==d?e(".checkbox-all input").iCheck("check"):e(".checkbox-all input").iCheck("uncheck");else{var l=a-d;a==l&&e(".btn-delete-data").prop("disabled",!0),e(".checkbox-all input").iCheck("uncheck")}}),e(".btn-delete-data").click(function(){var n=[];e(".checkbox-body input:checked").each(function(){n.push(e(this).val())}),n.length>0?(e("#register_id_delete").val(n),e("#modal_delete").modal("show")):e("#modal_noselect").modal("show")}),e("#button_delete_submit").click(function(){var n=e("#register_id_delete").val(),t=e(this);t.button("loading"),e.ajax({type:"GET",url:urlDelete,data:{leaveDayIds:n},success:function(n){e("#modal_delete").modal("hide"),window.location.reload()},error:function(n){e("#modal_delete").modal("hide"),window.location.reload()}})}),e(".button-delete").click(function(){e("#register_id_delete").val(e(this).val()),e("#modal_delete").modal("show")}),e(document).on("click","tr.reason-data .reason-edit",function(n){var t=e(this).closest("tr");console.log(t.find(".employee-code").text()),e("#full_name").val(t.find(".full_name").text()),e("#employee_code").val(t.find(".employee_code").text()),e("#day_last_year").val(t.find(".day_last_year").text()),e("#day_last_transfer").val(t.find(".day_last_transfer").text()),e("#day_current_year").val(t.find(".day_current_year").text()),e("#day_seniority").val(t.find(".day_seniority").text()),e("#day_OT").val(t.find(".day_OT").text()),e("#day_used").val(t.find(".day_used").text()),e("#note").val(t.find(".note").text()),e("#day_id").val(t.attr("day-id")),e("#form-submit-reason").find("input[type=text]").removeClass("error"),e("#modal_edit_leave_day").modal("show"),e("#day_last_year-error").remove(),e("#day_current_year-error").remove(),e("#day_OT-error").remove(),e("#day_last_transfer-error").remove(),e("#day_seniority-error").remove(),e("#day_used-error").remove()}),e("#form-submit-day").validate({onkeyup:!1,rules:{day_last_year:{number:!0,min:0},day_last_transfer:{number:!0,min:0},day_current_year:{number:!0,min:0},day_seniority:{number:!0,min:0},day_OT:{number:!0,min:0},day_used:{number:!0,min:0}},messages:{day_last_year:{number:"Bắt buộc là số và lớn hơn 0",min:"Bắt buộc là số và lớn hơn 0"},day_last_transfer:{number:"Bắt buộc là số và lớn hơn 0",min:"Bắt buộc là số và lớn hơn 0"},day_current_year:{number:"Bắt buộc là số và lớn hơn 0",min:"Bắt buộc là số và lớn hơn 0"},day_seniority:{number:"Bắt buộc là số và lớn hơn 0",min:"Bắt buộc là số và lớn hơn 0"},day_OT:{number:"Bắt buộc là số và lớn hơn 0",min:"Bắt buộc là số và lớn hơn 0"},day_used:{number:"Bắt buộc là số và lớn hơn 0",min:"Bắt buộc là số và lớn hơn 0"}}}),e("#upload").on("change",function(){var n=e(this),t=new FormData;t.append("file",n[0].files[0]),t.append("_token",e("input[name=_token]").val()),url=n.attr("url"),n.parent().find(".fa-spin").removeClass("hidden"),e.ajax({url:url,type:"POST",data:t,processData:!1,contentType:!1,cache:!1,success:function(e){window.location.reload()},error:function(e){window.location.reload()}})})});