function formatState(e){return e.id?$('<span><i class="fa fa-star-o '+$(e.element).attr("class")+'"></i> '+e.text+"</span>"):e.text}function getTotalCheckedWithEnable(){var e=0;return $(".check-item:enabled").map(function(t,a){e+=$(a).is(":checked")?1:0}),e}var modalSendMail=$("#modal-send-mail"),modalPreviewEmail=$("#modal-preview-email"),btnSendMail=$(".btn-send-mail"),chkAllItems=$(".check-all-items");RKfuncion.CKEditor.init(["content"]),CKEDITOR.config.height=390,selectSearchReload(),RKfuncion.select2.elementRemote($("#request_filter")),$("#recruiterList").select2(),$("#interested").select2({templateResult:formatState,templateSelection:formatState}),chkAllItems.on("click",function(){var e=$(this).closest("table");e.find(".check-item:disabled").prop("checked",!1),e.find(".check-item:enabled").prop("checked",$(this).is(":checked")),btnSendMail.attr("disabled",!$(".check-item:checked").length)}),$(document).on("click","table .check-item",function(){var e=$(this).closest("table"),t=getTotalCheckedWithEnable();chkAllItems.prop("checked",t===e.find(".check-item:enabled").length),btnSendMail.attr("disabled",!t)}),btnSendMail.on("click",function(){modalSendMail.modal("show")});var rules={app_pass:"required",subject:"required",content:{required:function(e){return CKEDITOR.instances[e.id].updateElement(),0===e.value.replace(/<[^>]*>/gi,"").length}}};$("#form-send-mail").submit(function(e){e.preventDefault()}).validate({ignore:[],rules:rules,messages:validateMessages,submitHandler:function(){return!1}}),modalSendMail.on("click",".btn-preview",function(){var e=$(this);e.attr("disabled",!0).find(".fa-refresh").removeClass("hidden"),$.ajax({url:urlPreviewMail,method:"post",data:{_token:_token,subject:$('#modal-send-mail input[name="subject"]').val(),content:CKEDITOR.instances.content.getData(),type:isTabBirthday?typeMailBirthday:typeMailFollow}}).done(function(t){var a=$('<iframe style="height: 75vh; width: 100%;">');modalPreviewEmail.find(".preview-send-email").html(a),modalPreviewEmail.find(".preview-send-email-subject").html(t.subject),setTimeout(function(){$("body",a[0].contentWindow.document).replaceWith(t.content),modalPreviewEmail.modal("show")},1),e.attr("disabled",!1).find(".fa-refresh").addClass("hidden")})}),modalSendMail.on("click",".btn-send",function(){if($("#form-send-mail").valid()){var e=$(this);e.attr("disabled",!0).find(".fa-refresh").removeClass("hidden");var t=[],a=[];$(".check-item:enabled").map(function(e,i){$(i).is(":checked")&&(t.push($(i).val()),a.push($(i)))}),$.ajax({url:urlSendMail,method:"post",data:{_token:_token,app_pass:modalSendMail.find('input[name="app_pass"]').val(),subject:modalSendMail.find('input[name="subject"]').val(),candidateIds:t,type:isTabBirthday?typeMailBirthday:typeMailFollow,content:CKEDITOR.instances.content.getData()},success:function(e){bootbox.alert({className:1===e.success?"modal-success":"modal-danger",message:e.message}),0!==e.success&&(a.map(function(t,a){if(t.attr("disabled",isTabBirthday).prop("checked",!1),isTabBirthday)t.closest("tr").children("td.mail-status").text(txtSentMailCMSN);else{var i=t.closest("tr").children("td.mail-type"),n=i.text();if(!n.match(new RegExp(txtMailTypeInterested,"i"))){var l=n.match(new RegExp(txtMailTypeMarketing,"i"))?", ":"";i.text(n+l+txtMailTypeInterested)}t.closest("tr").children("td.sent-date").text(e.sent_date)}}),btnSendMail.attr("disabled",!0),chkAllItems.prop("checked",!1),modalSendMail.modal("hide"))},error:function(e){e.responseJSON&&e.responseJSON.message&&bootbox.alert({className:"modal-danger",message:e.responseJSON.message})},complete:function(){e.attr("disabled",!1).find(".fa-refresh").addClass("hidden")}})}});