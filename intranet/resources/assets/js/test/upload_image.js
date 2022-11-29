(function ($) {

    var uploadBox = $('#uploaded_box');
    var listImage = uploadBox.find('.list-images');
    var uploadForm = $('#upload_form');
    var uploadField = $('#upload_field');
    var fileTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'audio/mp3', 'audio/wav', 'audio/wma'];

    $('#upload_field').click(function () {
        $(this).val('');
    });

    var tempFiles = [];
    var index = -1;
    uploadField.change(function (e) {
        e.preventDefault();
        var el_this = $(this);

        var formData = new FormData();
        formData.append('_token', _token);
        var files = el_this[0].files;
        var size = 0;

        for (var i = 0; i < files.length; i++) {
            formData.append('images[]', files[i]);
            if (files[i].size > 5 * 1024 * 1024) {
                showModalError(globMess.file_max_size + ' (' + files[i].name + ' - ' + Math.floor(files[i].size / 1024) + 'KB)');
                el_this.val('');
                return;
            }
            if (fileTypes.indexOf(files[i].type) == -1) {
                showModalError(globMess.file_mimes + ' (' + files[i].name + ')');
                el_this.val('');
                return;
            }
            size += files[i].size;
            index++;
            tempFiles[index] = files[i];
            var imgItem;
            //option file type
            if (isImageFile(files[i].type)) {
                var reader = new FileReader();
                (function (index) {
                    reader.readAsDataURL(files[i]);
                    reader.onload = function (e) {
                        imgItem = $('#preview_item').clone().removeAttr('id').removeClass('hidden').attr('data-index', index);
                        imgItem.find('.filename').text(tempFiles[index].name);
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
            } else if (isAudioFile(files[i].type)) {
                imgItem = $('#preview_item').clone().removeAttr('id').removeClass('hidden').attr('data-index', index);
                imgItem.find('.filename').text(files[i].name);
                imgItem.find('img').attr('src', urlAudioIcon);
                appendImgItem(imgItem, index);
            }
        }
    });

    uploadForm.submit(function () {
        var el_this = $(this);
        $('#error_box').addClass('hidden').html('');
        var btn = el_this.find('button[type="submit"]');
        
        if (tempFiles.length < 1) {
            showModalError(globMess.no_file_selected);
            btn.prop('disabled', false);
            return false;
        }
        var formData = new FormData();
        formData.append('_token', _token);
        var size = 0;
        uploadBox.find('.img-preview').each(function () {
            if (typeof $(this).attr('data-index') != "undefined") {
                var item_index = $(this).attr('data-index');
                var file = tempFiles[item_index];
                if (file) {
                    // check each file size < 5MB
                    if (file.size > 5 * 1024 * 1024) {
                        showModalError(globMess.file_max_size + ' (' + file.name + ' - ' + Math.floor(file.size / 1024) + 'KB)');
                        btn.prop('disabled', false);
                        return false;
                    }
                    size += file.size;
                    formData.append('images[' + item_index + ']', file);
                    $(this).find('.gen-url .url').removeClass('hidden');
                }
            }
        });
        // begin upload file
        uploadField.prop('disabled', true);
        btn.find('.uploading').removeClass('hidden');
        $.ajax({
            type: 'POST',
            url: el_this.attr('action'),
            dataType: 'json',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                window.location.reload();
            },
            error: function (err) {
                showModalError(err.responseJSON);
                $('.img-preview .gen-url .url').addClass('hidden');
            },
            complete: function () {
                btn.prop('disabled', false);
                uploadField.prop('disabled', false);
                btn.find('.uploading').addClass('hidden');
            }
        });
        return false;
    });

    //append image by order index
    function appendImgItem(imgItem, index) {
        var hasItem = false;
        for (var i = index; i >= 0; i--) {
            var idxItem = $('.img-preview[data-index="' + i + '"]');
            if (idxItem.length > 0) {
                idxItem.after(imgItem);
                hasItem = true;
                break;
            }
        }
        if (!hasItem) {
            imgItem.prependTo(listImage);
        }
    }

    function isImageFile(mime) {
        return (/image\/(.*)$/i).test(mime);
    }
    
    function isAudioFile(mime) {
        return (/audio\/(.*)$/i).test(mime);
    }

    $('body').on('click', '.del-btn', function (e) {
        e.preventDefault();
        var wrapper = $(this).closest(".img-preview");
        var item_index = parseInt(wrapper.attr('data-index'));
        tempFiles[item_index] = null;
        wrapper.remove();
    });
    
    var copyTooltip = $('#copy_tooltip');
    $('body').on('click', '.copy-btn', function () {
        var url = $(this).closest('tr').find('.url-col .text').text();
        var aux = document.createElement("input");
        aux.setAttribute("value", url);
        document.body.appendChild(aux);
        aux.select();
        document.execCommand("copy");
        document.body.removeChild(aux);
        
        var pos = $(this).position();
        copyTooltip.removeClass('hidden').css('top', pos.top - 32).css('left', pos.left - 10);
    });
    
    $('body').on('mouseleave', '.copy-btn', function () {
        copyTooltip.addClass('hidden');
    });
    
    $('body').on('click', '#m_action_delete', function () {
        var btnDel = $(this);
        var notiText = '';
        var notiOld = btnDel.data('noti');
        $('.check_item:checked').each(function () {
            var tr = $(this).closest('tr');
            if (parseInt(tr.attr('inuse')) == 1) {
                notiText += '<b>-</b> ' + tr.find('.img-title').text() + ': ' + tr.find('.btn-delete').attr('data-mess') + '<br />';
            }
        });
        if (notiText != '') {
            notiText += globMess.continue_delete;
            btnDel.data('noti', notiText);
        } else {
            btnDel.data('noti', notiOld);
        }
    });
    
    $(document).ready(function () {
        //check image url in use tests
        var imageIds = [];
        $('.check_item').each(function () {
            imageIds.push($(this).val());
        });
        if (imageIds.length < 1) {
            return;
        }
        $.ajax({
            url: checkImageInUseUrl,
            type: 'GET',
            data: {
                'image_ids[]': imageIds
            },
            success: function (results) {
                if (results.length > 0) {
                    for (var i = 0; i < results.length; i++) {
                        var item = results[i];
                        if (item.in_use) {
                            $('#image_' + item.id + ' .btn-delete').attr('data-noti', item.test_name + ' ' + globMess.continue_delete).attr('data-mess', item.test_name);
                            $('#image_' + item.id).attr('inuse', 1);
                        }
                    }
                }
            }
        });
    });
    
    $(document).ready(function () {
        //check image url in use tests
        var imageIds = [];
        $('.check_item').each(function () {
            imageIds.push($(this).val());
        });
        if (imageIds.length < 1) {
            return;
        }
        $.ajax({
            url: checkImageInUseUrl,
            type: 'GET',
            data: {
                'image_ids[]': imageIds
            },
            success: function (results) {
                if (results.length > 0) {
                    for (var i = 0; i < results.length; i++) {
                        var item = results[i];
                        if (item.in_use) {
                            $('#image_' + item.id + ' .btn-delete').attr('data-noti', item.test_name);
                        }
                    }
                }
            }
        });
    });

})(jQuery);