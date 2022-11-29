var modal_error = $('#modal-warning-notification');
var uploading = $('.uploading');
var MAX_FILE_SIZE = 10;

$(function () {
    $("#uploadPreview").sortable();
});
//Append array of image after sort to Form to send server
$("#frm_create_magazine").submit(function () {
    var el_this = $(this);
    $('#error_box').addClass('hidden').html('');
    var btn = el_this.find('button[type="submit"]');

    var formData = new FormData();
    formData.append('_token', el_this.find('input[name="_token"]').val());
    formData.append('name', $('#name').val());
    formData.append('select_index', select_index);

    var size = 0;
    $('#uploadPreview .imgPreviewWrap').each(function (e_index) {
        if (typeof $(this).attr('data-index') != "undefined") {
            var item_index = $(this).attr('data-index');
            var file = tempFiles[item_index];
            if (file) {
                if (typeof file == 'object') {
                    // check each file size < 10MB
                    if (file.size > MAX_FILE_SIZE * 1024 * 1024) {
                        showModalError(msg['validFileSize'] + ' ('+ file.name +' - ' + Math.floor(file.size / 1024) + 'KB)');
                        btn.prop('disabled', false);
                        return false;
                    }
                    size += file.size;
                    formData.append('images['+ e_index +']', file);
                } else {
                    formData.append('image_ids['+ e_index +']', file);
                }
            }
        }
    });
    //check total file size < server allow post file size
    if (size > MAX_SIZE * 1024 * 1024) {
        showModalError(msg['fileMaxSize']);
        btn.prop('disabled', false);
        return false;
    }
    
    // begin upload file
    $('#fileUpload').prop('disabled', true);
    // show loading
    $('.submit-alert').removeClass('hidden');

    $.ajax({
       type: 'POST',
       url: el_this.attr('action'),
       dataType: 'json',
       data: formData,
       cache: false,
       contentType: false,
       processData: false,
       success: function (data) {
          if (data.err) {
            $('#error_box').removeClass('hidden').html(data.err);
          } else {
            //return to list magazines
            window.location.href = listMagazineUrl;
          }
       },
       error: function (err) {
            if (err.status == 422) {
                $('#error_box').removeClass('hidden').html(err.responseJSON);
            } else {
                showModalError(err.responseJSON);
            }
       },
       complete: function () {
           btn.prop('disabled', false);
           $('.submit-alert').addClass('hidden');
           $('#fileUpload').prop('disabled', false);
       }
    });
    
    return false;
});
//upload file
$('.fileUpload').one('click', function () {
   uploading.removeClass('hidden');
   $(window).one('focus', function () {
      uploading.addClass('hidden');
   });
});

var tempFiles = [];
var index = -1;
var select_index = -1;

$('#uploadPreview .imgPreviewWrap').each(function () {
   var item_id = $(this).attr('data-id');
   if (typeof item_id != "undefined" && item_id) {
       index ++;
       tempFiles[index] = item_id;
       $(this).attr('data-index', index);
   }
});

$('#fileUpload').click(function () {
   $(this).val(''); 
});
$("#fileUpload").change(function (e) {
    e.preventDefault();
    var el_this = $(this);
    
    var formData = new FormData();
    formData.append('_token', _token);
    var files = el_this[0].files;
    var size = 0;
    for (var i = 0; i < files.length; i++) {
        formData.append('images[]', files[i]);
        if (files[i].size > MAX_FILE_SIZE * 1024 * 1024) {
            showModalError(msg['validFileSize'] + ' ('+ files[i].name +' - ' + Math.floor(files[i].size / 1024) + 'KB)');
            el_this.val('');
            return;
        }
        size += files[i].size;
        index++;
        tempFiles.push(files[i]);
        var reader = new FileReader();
        (function (index) {
            reader.readAsDataURL(files[i]);
            reader.onload = function (e) {
                var imgItem = $('#preview_item').clone().removeAttr('id').removeClass('hidden').attr('data-index', index);
                var img = imgItem.find('img');
                img.attr('src', e.target.result);
                img.src = img.prop('src');
                if (typeof EXIF != "undefined") {
                    EXIF.getData(img, function () {
                       var orient = EXIF.getTag(img, 'Orientation');
                       if (typeof orient != "undefined") {
                            img.addClass('rotate-' + orient);
                       }
                       appendImgItem(imgItem, index);
                    });
                } else {
                    appendImgItem(imgItem, index);
                }
            };
        })(index);
    }

    if (size > MAX_SIZE * 1024 * 1024) {
        showModalError(msg['fileMaxSize']);
        el_this.val('');
        return;
    }
    
});

//append image by order index
function appendImgItem(imgItem, index) {
    var hasItem = false;
    for (var i = index; i >= 0; i--) {
        var idxItem = $('.imgPreviewWrap[data-index="'+ i +'"]');
        if (idxItem.length > 0) {
            idxItem.after(imgItem);
            hasItem = true;
            break;
        }
    }
    if (!hasItem) {
        imgItem.prependTo($('#uploadPreview'));
    }
}

$('body').on("click", ".action-delete", function () {
    if (!$('.submit-alert').hasClass('hidden')) {
        return;
    }
    var wrapper = $(this).closest(".imgPreviewWrap");
    var item_index = wrapper.attr('data-index');
    tempFiles[item_index] = null;
    if (select_index == item_index) {
        select_index = -1;
    }
    wrapper.remove();
});

$('body').on("click", ".imgPreviewWrap", function () {
    var item_index = $(this).attr('data-index');
    if ($(this).hasClass('selected')) {
        $(this).removeClass('selected');
        select_index = -1;
    } else {
        $(".imgPreviewWrap").removeClass("selected");
        $(this).addClass("selected");
        select_index = item_index;
    }
});

//show message error
function showModalError(message) {
    if (typeof message == "undefined") {
        message = msg['errorOccurred'];
    }
    modal_error.find('.text-default').html(message);
    modal_error.modal('show');
}
