$(document).ready(function(){var e=$("#form_add_asset_category").validate({rules:{"item[name]":{required:!0,rangelength:[1,100],remote:{type:"GET",url:checkExitCate,data:{name:"name",assetCategoryId:function(){return $("#asset_category_id").val()},value:function(){return $("#asset_category_name").val().trim()}}}},"item[group_id]":{required:!0},"item[prefix_asset_code]":{required:!0,rangelength:[1,20],remote:{type:"GET",url:checkExitCate,data:{name:"prefix_asset_code",assetCategoryId:function(){return $("#asset_category_id").val()},value:function(){return $("#prefix_asset_code").val().trim()}}}}},messages:{"item[name]":{required:requiredText,rangelength:rangelengthText,remote:uniqueAssetCategoryName},"item[group_id]":{required:requiredText},"item[prefix_asset_code]":{required:requiredText,rangelength:rangelengthText20,remote:uniqueAssetCodePrefix}}});$("#prefix_asset_code").bind("copy paste cut",function(e){e.preventDefault()}),$(".btn-reset-validate").click(function(){e.resetForm(),$("#form_add_asset_category").find(".error").removeClass("error")})}),$(document).on("click","#btn_add_asset_category",function(){$('#form_add_asset_category input[type=text], #form_add_asset_category textarea, #form_add_asset_category input[name="id"]').val(""),$("#form_add_asset_category").find(".asset_group_id option:selected").prop("selected",!1),$("#asset_group_id").val(valueDefaultAssetGroup).trigger("change"),$("#modal_add_asset_category .modal-title").html(titleAddCate),$("#modal_add_asset_category").modal("show")}),$(document).on("click",".btn-edit-asset-category",function(){var e=$(this).closest("tr");$("#asset_category_id").val(e.attr("asset-category-id")),$("#asset_category_name").val(e.attr("asset-category-name")),$("#prefix_asset_code").val(e.attr("asset-code-prefix")),$("#asset_category_note").val(e.attr("asset-category-note")),$("#is_default").prop("checked",1===parseInt(e.attr("is-default"))),$("#asset_group_id").val(e.attr("asset-group-id")).trigger("change"),$("#modal_add_asset_category .modal-title").html(titleInfoCate),$("#modal_add_asset_category").modal("show")}),$(document).on("keyup","#asset_category_name",function(){$(".btn-submit").attr("disabled",!1)}),$(document).on("keypress","#prefix_asset_code",function(e){var t=e.charCode||e.keyCode;return 32!==t&&((t>47&&t<91||t>96&&t<123||32===t||0===t)&&void 0)});