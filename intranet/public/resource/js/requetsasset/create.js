$(".date").datepicker({format:"yyyy-mm-dd",todayHighlight:!0,autoclose:!0}),RKfuncion.select2.init(),a=$("#duplicate").children(".box-number").length,$(document).on("click","#add",function(e){e.preventDefault();var t=$("#duplicate1").html();a++,console.log(a),$(this).parent().prev().append(t),$(this).parent().prev().find(".input-name").last().attr("name","asset["+a+"][name]"),$(this).parent().prev().find(".input-number").last().attr("name","asset["+a+"][number]")}),$(document).on("click",".btn-delete",function(e){e.preventDefault(),$(this).parent().parent().parent().remove()});var isRequestSubmited=!1;$("#request-asset").submit(function(){isRequestSubmited=!0}),$("#employ-request").on("select2:select",function(e){e.preventDefault(),isRequestSubmited&&$("#request-asset").valid()}),$("#room-request").on("select2:select",function(e){e.preventDefault(),isRequestSubmited&&$("#request-asset").valid()});