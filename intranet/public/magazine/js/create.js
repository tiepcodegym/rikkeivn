function appendImgItem(e,a){for(var i=!1,t=a;t>=0;t--){var r=$('.imgPreviewWrap[data-index="'+t+'"]');if(r.length>0){r.after(e),i=!0;break}}i||e.prependTo($("#uploadPreview"))}function showModalError(e){"undefined"==typeof e&&(e=msg.errorOccurred),modal_error.find(".text-default").html(e),modal_error.modal("show")}var modal_error=$("#modal-warning-notification"),uploading=$(".uploading"),MAX_FILE_SIZE=10;$(function(){$("#uploadPreview").sortable()}),$("#frm_create_magazine").submit(function(){var e=$(this);$("#error_box").addClass("hidden").html("");var a=e.find('button[type="submit"]'),i=new FormData;i.append("_token",e.find('input[name="_token"]').val()),i.append("name",$("#name").val()),i.append("select_index",select_index);var t=0;return $("#uploadPreview .imgPreviewWrap").each(function(e){if("undefined"!=typeof $(this).attr("data-index")){var r=$(this).attr("data-index"),n=tempFiles[r];if(n)if("object"==typeof n){if(n.size>1024*MAX_FILE_SIZE*1024)return showModalError(msg.validFileSize+" ("+n.name+" - "+Math.floor(n.size/1024)+"KB)"),a.prop("disabled",!1),!1;t+=n.size,i.append("images["+e+"]",n)}else i.append("image_ids["+e+"]",n)}}),t>1024*MAX_SIZE*1024?(showModalError(msg.fileMaxSize),a.prop("disabled",!1),!1):($("#fileUpload").prop("disabled",!0),$(".submit-alert").removeClass("hidden"),$.ajax({type:"POST",url:e.attr("action"),dataType:"json",data:i,cache:!1,contentType:!1,processData:!1,success:function(e){e.err?$("#error_box").removeClass("hidden").html(e.err):window.location.href=listMagazineUrl},error:function(e){422==e.status?$("#error_box").removeClass("hidden").html(e.responseJSON):showModalError(e.responseJSON)},complete:function(){a.prop("disabled",!1),$(".submit-alert").addClass("hidden"),$("#fileUpload").prop("disabled",!1)}}),!1)}),$(".fileUpload").one("click",function(){uploading.removeClass("hidden"),$(window).one("focus",function(){uploading.addClass("hidden")})});var tempFiles=[],index=-1,select_index=-1;$("#uploadPreview .imgPreviewWrap").each(function(){var e=$(this).attr("data-id");"undefined"!=typeof e&&e&&(index++,tempFiles[index]=e,$(this).attr("data-index",index))}),$("#fileUpload").click(function(){$(this).val("")}),$("#fileUpload").change(function(e){e.preventDefault();var a=$(this),i=new FormData;i.append("_token",_token);for(var t=a[0].files,r=0,n=0;n<t.length;n++){if(i.append("images[]",t[n]),t[n].size>1024*MAX_FILE_SIZE*1024)return showModalError(msg.validFileSize+" ("+t[n].name+" - "+Math.floor(t[n].size/1024)+"KB)"),void a.val("");r+=t[n].size,index++,tempFiles.push(t[n]);var d=new FileReader;!function(e){d.readAsDataURL(t[n]),d.onload=function(a){var i=$("#preview_item").clone().removeAttr("id").removeClass("hidden").attr("data-index",e),t=i.find("img");t.attr("src",a.target.result),t.src=t.prop("src"),"undefined"!=typeof EXIF?EXIF.getData(t,function(){var a=EXIF.getTag(t,"Orientation");"undefined"!=typeof a&&t.addClass("rotate-"+a),appendImgItem(i,e)}):appendImgItem(i,e)}}(index)}if(r>1024*MAX_SIZE*1024)return showModalError(msg.fileMaxSize),void a.val("")}),$("body").on("click",".action-delete",function(){if($(".submit-alert").hasClass("hidden")){var e=$(this).closest(".imgPreviewWrap"),a=e.attr("data-index");tempFiles[a]=null,select_index==a&&(select_index=-1),e.remove()}}),$("body").on("click",".imgPreviewWrap",function(){var e=$(this).attr("data-index");$(this).hasClass("selected")?($(this).removeClass("selected"),select_index=-1):($(".imgPreviewWrap").removeClass("selected"),$(this).addClass("selected"),select_index=e)});