function showMess(e){$("#showMess .modal-dialog .modal-body p").remove();var c=$("#mess"+e).val();$("#showMess .modal-dialog .modal-body").append("<p>"+c+"</p>"),$("#showMess").modal("show")}function getIdOrder(){var e=[];return $('[name="check_items[]"]').each(function(){$(this).is(":checked")&&e.push($(this).val())}),e}$(".check_all").change(function(){$(this).is(":checked")?$('[name="check_items[]"]').prop("checked",!0):$('[name="check_items[]"]').prop("checked",!1)});var check_item=document.getElementsByName("check_items[]");$('[name="check_items[]"]').change(function(){getIdOrder().length<check_item.length?$(".check_all").prop("checked",!1):$(".check_all").prop("checked",!0)}),$(".m_action_btn").on("click",function(e){e.preventDefault();var c=$(this).attr("href"),t=$(this).attr("token");$(".btn-outline").one("click",function(e){if($(this).hasClass("btn-ok")){var a=getIdOrder();if(a.length<=0){var n='<div class="alert alert-warning"><ul><li>Chưa có đối tượng nào được chọn</li></ul></div>';$("#error .alert-warning").remove(),$(".not-found").remove(),$(".alert-success").remove(),$("#error").append(n)}else $.ajax({headers:{"X-CSRF-TOKEN":t},type:"post",url:c,data:{orderIds:a},success:function(e){window.location.reload()},cache:!1,dataType:"json"})}else $("input:checkbox").removeAttr("checked")})});