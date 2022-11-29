var MAX_FILE_UPLOAD = 50;
var arrayFiles = [];
var arrayLogo = [];

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var token = $('meta[name="csrf-token"]').attr('content');
jQuery(document).ready(function($) {

    $('#date').datepicker({
        autoclose: true,
    })
    $('.calendar-button').click(function(event) {
        $( "#date" ).datepicker( "show" );
    });
    var date = QueryString.date;
    var slideId = QueryString.id;
    if (typeof date !== 'undefined') {
        $('#date').val(date);
        if (typeof date !== 'undefined') {
            listSlideByDate($('#date'), date, slideId);
        } else {
            listSlideByDate($('#date'), date);
        }
    } else {
        if ($('#date').val()) {
            listSlideByDate($('#date'), $('#date').val());
        }
    }
    $(document).on('change', '#date', function (event) {
        if ($(this).attr('data-old-value') != $(this).val()) {
            var $this = $(this);
            $value = $this.val();
            listSlideByDate($this, $value);
        }
        $(this).attr('data-old-value', $(this).val());
    });

    $(document).on('change', '#fr-create-slide .logo_company', function (event) {
        var file = $(this).prop('files');
        var form = $('#fr-create-slide');
        displayLogo(file, form);
    });
    $(document).on('change', '#fr-update-slide .logo_company', function (event) {
        var file = $(this).prop('files');
        var form = $('#fr-update-slide');
        displayLogo(file, form);
    });
    $(document).on('change', '.url_video', function (event) {
        value = $(this).val().trim();
        videoId = getYouTubeId(value);
        classPreview = $('.preview-video');
        if (typeof videoId == 'undefined') {
            classPreview.find('iframe').attr('src', '');
            classPreview.addClass('display-none');
            alert($(this).attr('data-message'));
        } else {
            src = "https://www.youtube.com/embed/"+videoId+"?playlist="+videoId+"&loop=1&autoplay=1&cc_load_policy=1&rel=0&amp;controls=1&amp;showinfo=0&vq=hd1080";
            classPreview.find('iframe').attr('src', src);
            classPreview.removeClass('display-none');
        }
    });

    $(document).on('change', '#fr-create-slide .type', function (event) {
        if ($('#fr-create-slide').find('.input-image').hasClass('display-none')) {
            $('#fr-create-slide').find('.input-image').removeClass('display-none');
            $('#fr-create-slide').find('.input-video').addClass('display-none');
            $('.input-slide-show-type[data-type="image"]').removeClass('display-none');
            $('.input-slide-show-type[data-type="video"]').addClass('display-none');
            $('#fr-create-slide').find('.input-font-size[data-type="image"]').removeClass('display-none');
        } else {
            $('#fr-create-slide').find('.input-video').removeClass('display-none');
            $('#fr-create-slide').find('.input-image').addClass('display-none');
            $('.input-slide-show-type[data-type="image"]').addClass('display-none');
            $('.input-slide-show-type[data-type="video"]').removeClass('display-none');
            $('#fr-create-slide').find('.input-font-size[data-type="image"]').addClass('display-none');
        }
    });
    $(document).on('change', '#fr-update-slide .type', function (event) {
        if ($('#fr-update-slide').find('.input-image').hasClass('display-none')) {
            $('#fr-update-slide').find('.input-image').removeClass('display-none');
            $('#fr-update-slide').find('.input-video').addClass('display-none');
            $('.input-slide-show-type[data-type="image"]').removeClass('display-none');
            $('.input-slide-show-type[data-type="video"]').addClass('display-none');
            $('#fr-update-slide').find('.input-font-size[data-type="image"]').removeClass('display-none');
        } else {
            $('#fr-update-slide').find('.input-video').removeClass('display-none');
            $('#fr-update-slide').find('.input-image').addClass('display-none');
            $('.input-slide-show-type[data-type="image"]').addClass('display-none');
            $('.input-slide-show-type[data-type="video"]').removeClass('display-none');
            $('#fr-update-slide').find('.input-font-size[data-type="image"]').addClass('display-none');
        }
    });

    $(document).on('change', '#fr-create-slide .option', function (event) {
        var form = $('#fr-create-slide');
        changeOptionSlide(form, $(this).val());
    });

    $(document).on('change', '#fr-update-slide .option', function (event) {
        var form = $('#fr-update-slide');
        changeOptionSlide(form, $(this).val());
    });

    $(document).on('change', '#file_video_edit', function (event) {
        changeVideo('fr-update-slide', this);
    });
    $(document).on('change', '#file_video', function (event) {
        changeVideo('fr-create-slide', this);
    });
    $(document).on('click', '.btn-add-slide', function (event) {
        $('body, html').animate({scrollTop: 0}, '500', 'swing');
        window.history.pushState('', '', urSlidelSetting + '?date='+ $('#date').val());
        $('.list-slide-by-hour').find('li>a').css({
            color: '#3c8dbc'
        });
        $this = $(this);
        $hourForm = $this.attr('data-hour-form');
        $hourTo = $this.attr('data-hour-to');
        if ($('.detail-slide .content-create-slide').length) {
            $dataHourForm = $('.detail-slide .content-create-slide').attr('data-hour-form');
            $dataHourTo = $('.detail-slide .content-create-slide').attr('data-hour-to');
            if ($hourForm == $dataHourForm && $hourTo == $dataHourTo) {
                return ;
            }
        }
        var $divCreateSlide = $('.create-slide').html();
        var $divDetail = $('.detail-slide');
        $divDetail.html($divCreateSlide);
        $divCreateSlide = $divDetail.find('.content-create-slide');
        $divCreateSlide.find('.hour_start').addClass('select-2');
        $divCreateSlide.find('.hour_start').val($hourForm);
        $divCreateSlide.find('.hour_end').addClass('select-2');
        $divCreateSlide.find('.hour_end').val($hourTo);
        $divCreateSlide.find('.font_size').addClass('select-2');
        $divCreateSlide.find('.language').addClass('select-2');
        $divCreateSlide.removeClass('display-none');
        $divCreateSlide.attr('data-hour-form', $hourForm);
        $divCreateSlide.attr('data-hour-to', $hourTo);
        $divCreateSlide.find('.text-form').html($hourForm);
        $divCreateSlide.find('.text-to').html($hourTo);
        $divCreateSlide.find('iframe').attr('src', '');
        $('#fr-create-slide .select-2').select2();
        arrayFiles = [];
        arrayLogo = [];
        initFilerUploadImage(fnInitFIlerImage, 'filer_input2');
        initFilerLogo(fnInitFIlerImage, 'logo_company');
        $('.jFiler-items.jFiler-row').remove();
    });

    $(document).on('click', '.btn-detail-slide', function (event) {
        $this = $(this);
        $('.list-slide-by-hour').find('li>a').css({
            color: '#3c8dbc',
        });
        $('.list-slide-by-hour').find('li').css({
            color: '#3c8dbc',
        });
        $this.css({
            color: '#B71C1C',
        });
        $this.parent('li').css({
            color: '#B71C1C',
        });
        $('body, html').animate({scrollTop: 0}, '500', 'swing');
        $('.list-slide-by-hour').find()
        arrayFiles = [];
        arrayLogo = [];
        $('.create-slide').addClass('display-none');
        $id = $this.attr('data-id');
        if ($('.detail-slide .detail-slide-element').hasClass('detail-slide-element-' +$id)) {
            return ;
        }
        $('.loader').removeClass('display-none');
        $('.detail-slide').html('');
        if ($this.data('requestRunning')) {
            return;
        }
        $this.data('requestRunning', true);
        url = getDetailSlide;
        data = {
            _token: token,
            id: $id,
        }
        $.ajax({
            url: url,
            data: data,
            type: 'post',
            dataType: 'json',
            success: function ($data) {
                $('.detail-slide').html($data.content);
                if($('#slideBirthdayContent').length) {
                    CKEDITOR.replace('slideBirthdayContent');
                }
                $('#fr-update-slide .select-2').select2();
                $('.jFiler-items.jFiler-row').remove();
                if ($('#logo_company_edit').length) {
                    setValueArrayFiles($('#logo_company_edit'), PATH_DEFAULT, true);
                }
                initFilerLogo(fnInitFIlerImage, 'logo_company_edit');
                if ($('#filer_input_edit').length) {
                    setValueArrayFiles($('#filer_input_edit'), PATH_DEFAULT);
                }
                initFilerUploadImage(fnInitFIlerImage, 'filer_input_edit');
                window.history.pushState('', '', urSlidelSetting + '?date='+ $('#date').val() + '&id='+$id);
            },
            complete:function() {
                $('.loader').addClass('display-none');
                $this.data('requestRunning', false);
            }
        });
    });

    $(document).on('click', '.btn-create-slide', function (event) {
        $('.error-validate').remove();
        var formName = $('#fr-create-slide');
        if (formName.find('.option:checked').val() == OPTION_WELCOME) {
            var inputName = 'logo_company';
        } else if (formName.find('.option:checked').val() == OPTION_NOMAL){
            var inputName = 'filer_input2';
        }
        saveSlide($(this), formName, inputName);
    });

    $(document).on('click', '.btn-update-slide', function (event) {
        $('.error-validate').remove();
        var id = $(this).attr('data-id');
        var formName = $('#fr-update-slide');
        if (formName.find('.option:checked').val() == OPTION_WELCOME) {
            var inputName = 'logo_company_edit';
        } else if (formName.find('.option:checked').val() == OPTION_NOMAL){
            var inputName = 'filer_input_edit';
        }
        saveSlide($(this),formName, inputName, id, true);
    });
    $(document).on('change', '.form-horizontal .title', function (event) {
        if ($(this).val()) {
            if ($('.error-title').length) {
                $('.error-title').remove();
            }
        }
    });
    $(document).on('click', '#edit-password', function (event) {
        $('.error-validate-password').remove();
        $password = $('#password').val();
        data = {
            _token:token,
            password: $password,
        }
        url = urlChangePassword;
        if ($(this).data('requestRunning')) {
            return;
        }
        $(this).data('requestRunning', true);
        $.ajax({
            url: url,
            type: 'post',
            data: data,
            dataType: 'json',
            success: function(data) {
                if(data.status) {
                    $modal = $('.modal-slide');
                    $modal.find('.modal').removeClass().addClass('modal modal-success');
                    $modal.find('.modal-title-success').removeClass('display-none');
                    $modal.find('.modal-title-error').addClass('display-none');
                    $modal.find('.text-message').html($('#edit-password').attr('data-message-success'));
                    $modal.find('.modal').modal();
                } else {
                    if(data.message_error.password) {
                        $('#password').after('<p class="word-break error-validate-password error" for="password">' + data.message_error.password[0] + '</p>')
                    }
                }
            },
            error:function() {
                $modal = $('.modal-slide');
                $modal.find('.modal').removeClass().addClass('modal modal-danger');
                $modal.find('.modal-title-success').addClass('display-none');
                $modal.find('.modal-title-error').removeClass('display-none');
                $modal.find('.text-message').html($('#edit-password').attr('data-message-error'));
                $modal.find('.modal').modal();
            },
            complete:function () {
                $('#edit-password').data('requestRunning', false);
            }
        });
    });

    $(document).on('click', '.btn-delete-slide', function (event) {
        id = $(this).attr('data-id');
        url = urlDeleteSlide;
        $('#modal-delete-slide').modal('show');
        $(document).on('click', '#modal-delete-slide .btn-ok', function () {
            $('#modal-delete-slide').modal('hide');
            if ($(this).data('requestRunning')) {
                return;
            }
            $(this).data('requestRunning', true);
            data = {
                _token: token,
                id: id,
            };
            $.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: data,
                success: function ($data) {
                    $modal = $('.modal-slide');
                    if ($data.status) {
                        listSlideByDate($('#date'), $('#date').val());
                        $('.detail-slide').html('');
                        $modal.find('.modal').removeClass().addClass('modal modal-success');
                        $modal.find('.modal-title-success').removeClass('display-none');
                        $modal.find('.modal-title-error').addClass('display-none');
                        $modal.find('.text-message').html($modal.find('.text-message').attr('data-delete-success'));
                        $modal.find('.modal').modal();
                    } else {
                        $modal.find('.modal').removeClass().addClass('modal modal-danger');
                        $modal.find('.modal-title-success').addClass('display-none');
                        $modal.find('.modal-title-error').removeClass('display-none');
                        $modal.find('.text-message').html($modal.find('.text-message').attr('data-delete-error'));
                        $modal.find('.modal').modal();
                    }
                },
                error: function () {
                    $modal = $('.modal-slide');
                    $modal.find('.modal').removeClass().addClass('modal modal-danger');
                    $modal.find('.modal-title-success').addClass('display-none');
                    $modal.find('.modal-title-error').removeClass('display-none');
                    $modal.find('.text-message').html($modal.find('.text-message').attr('data-delete-error'));
                    $modal.find('.modal').modal();
                },
                complete: function () {
                    $('#modal-delete-slide .btn-ok').data('requestRunning', false);
                },
            });
        });
    });
    $(document).on('change', '#fr-create-slide .select-hour', function (event) {
        loadInterval('fr-create-slide');
    });
    $(document).on('change', '#fr-update-slide .select-hour', function (event) {
        $slideId = $('#fr-update-slide').attr('data-id');
        loadInterval('fr-update-slide', $slideId);
    });

});
/**
 * check process resize background
 */
function rkCheckProcessResize(showProcess) {
    if (typeof showProcess != 'undefined' && showProcess) {
        $('.slide-resize-process').removeClass('hidden');
    }
    // remove process show
    var slideProcessing = setInterval(function () {
        if ($('.slide-resize-process').length &&
            !$('.slide-resize-process').hasClass('hidden') &&
            typeof optionPassGlobal != 'undefined' && 
            typeof optionPassGlobal.urlCheckProcess != 'undefined' &&
            optionPassGlobal.urlCheckProcess
        ) {
            $.ajax({
                url: optionPassGlobal.urlCheckProcess,
                type: 'post',
                dataType: 'json',
                data: {
                    _token: siteConfigGlobal.token
                },
                success: function(data) {
                    if (typeof data != 'undefined' && 
                        typeof data.run != 'undefined' &&
                        data.run == 0
                    ) {
                        $('.slide-resize-process').addClass('hidden');
                    }
                }
            });
        } else {
            clearInterval(slideProcessing);
        }
    }, 20*1000);
}

var fnInitFIlerImage = function (template, input, logo) {
    if (typeof logo != 'undefined') {
        var fileInit = arrayLogo;
    } else {
        var fileInit = arrayFiles;
    }
    $("#" + input).filer({
        limit: MAX_FILE_UPLOAD,
        maxSize: optionPassGlobal.sizeImageMaxRegister,
        extensions: optionPassGlobal.imageTypeAllowRegister,
        box: null,
        changeInput: '<div class="jFiler-input-dragDrop jfiler-custom"><div class="jFiler-input-inner"><div class="jFiler-input-icon"><i class="fa fa-picture-o"></i></div></div><i class="fa fa-plus"></i></div>',
        showThumbs: true,
        theme: "dragdropbox",
        templates: {
            box: '<ul class="jFiler-items-list jFiler-items-grid"></ul>',
            item: template,
            itemAppend: template,
            progressBar: '<div class="bar"></div>',
            itemAppendToEnd: true,
            removeConfirmation: true,
            _selectors: {
                list: '.jFiler-items-list',
                item: '.jFiler-item',
                progressBar: '.bar',
                remove: '.jFiler-item-trash-action'
            }
        },
        dragDrop: {
            dragEnter: null,
            dragLeave: null,
            drop: null
        },
        files: fileInit,
        addMore: true,
        excludeName: null,
        captions: {
            button: "Choose Files",
            feedback: "Choose files To Upload",
            feedback2: "files were chosen",
            drop: "Drop file here to Upload",
            removeConfirmation: messageConfirmDeleteImage,
            errors: {
                filesLimit: "Chỉ cho phép upload tối đa {{fi-limit}} file",
                filesType: messageWarningFileTypeUpload,
                filesSize: "{{fi-name}} quá lớn! Vui lòng upload file có kích thước tối đa {{fi-maxSize}} MB.",
                filesSizeAll: "Các file bạn đã chọn có kích thước quá lớn ! Vui lòng upload file có kích thước tối đa {{fi-maxSize}} MB."
            }
        },
        afterShow: function () {
            var listFiles = $("#" + input).filer()[0].jFiler.files_list;
            if (!listFiles.length) {
                $("div.jFiler-theme-dragdropbox").next().show();
            } else {
                $("div.jFiler-theme-dragdropbox").next().hide();
            }
        },
        afterRender: function () {
            if (typeof logo != 'undefined') {
                arrayLogo = fileInit;
            } else {
                setValueDescription('#' + input);
                arrayFiles = fileInit;
            }
        }
    });
};

function listSlideByDate($this, $value, $id, $isUpdate) {
    $('.error-date').remove();
    if (!$value) {
        $('.slide-list').html('');
        $('.detail-slide').html('');
        window.history.pushState('', '', urSlidelSetting);
        return;
    }
    if ($this.data('requestRunning')) {
        return;
    }
    window.history.pushState('', '', urSlidelSetting + '?date='+$value);
    $this.data('requestRunning', true);
    url = urlGetSlideList;
    data = {
        _token: token,
        date: $value,
    }
    $.ajax({
        type: 'post',
        url: url,
        dataType: 'json',
        data: data,
        success: function ($data) {
            if (!$data.status) {
                if($data.message_error.date) {
                    $("#date").after('<p class="word-break error-date error" for="date">' + $data.message_error.date[0] + '</p>')
                }
            } else {
                $('.slide-list').html($data.content);
                if (typeof $isUpdate !== 'undefined') {
                    $('.list-slide-by-hour').find('li>a').css({
                        color: '#3c8dbc',
                    });
                    $('detail-slide-' + $data.id).css({
                        color: '#00a65a',
                    });
                } else {
                    $('.detail-slide').html('');
                }
                if (typeof $id !== 'undefined') {
                    $('#btn-detail-slide-' + $id).click();
                }
            }
        },
        complete: function() {
            $this.data('requestRunning', false);
        }
    });

}
function initFilerUploadImage(callback, input) {
    $.get(urlGetTemplateImage, function (data) {
        callback(data, input);
    });
}

function initFilerLogo(callback, input) {
    $.get(urlGetTemplateLogo, function (data) {
        callback(data, input, true);
    });
}

function setValueDescription(e) {
    if (arrayDescription !== undefined && arrayDescription) {
        var j = 0;
        $.each($('.input-description-image'), function () {
            $(this).closest('div.jFiler-item-container .input-description-image').val(arrayDescription[j]);
            j++;
        });
    }
}

function setValueArrayFiles($e, path, logo) {
    if (typeof logo != 'undefined') {
        var isLogo = true;
    } else {
        var isLogo = false;
    }
    var listFile = $e.attr('oldvalue');
    listShowFile = $e.attr('imageShow');
    if (listFile) {
        arrayFileSplit = listFile.split(',');
        if (listShowFile) {
            listShowFile = listShowFile.split(',');
        }
        $.each(arrayFileSplit, function (key, value) {
            extensionFileValue = getExtensionFile(value);
            objectPush = {name: value, file: path + value, type: 'image/' + extensionFileValue};
            if (listShowFile && listShowFile[key] != undefined && listShowFile[key]) {
                objectPush.file = listShowFile[key];
            }
            if (isLogo) {
                arrayLogo.push(objectPush);
            } else {
                arrayFiles.push(objectPush);
            }
        });
    }
}

/**
 * add description image
 * @param {Element} e
 * @returns {void}
 */
function addDescriptionImage(e) {
    var description = $(e).parent().next().val();
    $(e).closest('div.jFiler-item-container').attr('data-original-title', description).tooltip('fixTitle')
            .tooltip();
    $(e).closest('.description-image').hide();
}

/**
 * clode input description
 * @param {ELement} e
 * @returns void
 */
function closeInputDecription(e) {
    $(e).closest('.description-image').hide();
    $(e).parent().next().val("");
}


/**
 * Function resize image
 * 
 * @param {type} defaultWidth
 * @param {type} defaultHeight
 * @returns {undefined}
 */
function resizeImage(img, defaultWidth, defaultHeight) {
    img = $(img);
    image = new Image();
    image.src = img.attr('src');
    var width = image.width;
    var height = image.height;
    var oldRatio = width / height;
    var ratio = defaultWidth / defaultHeight;
    var parent_width = img.parent().width();
    var parent_height = parent_width / ratio;
    img.parent().css("height", parent_height);
    if (oldRatio >= ratio) {
        img.css({
            'width': 'auto',
            'height': '100%'
        });
        var magrin_left = (parent_height * oldRatio - parent_width) / 2;
        img.css("margin-left", -magrin_left);
    } else {
        img.css({
            'width': '100%',
            'height': 'auto'
        });
        var margin_top = (parent_width / oldRatio - parent_height) / 2;
        img.css("margin-top", -margin_top);
    }
}
function saveSlide(button, formName, inputName, id, isUpdate)
{   
    var fileSelect = document.getElementById(inputName);
    var fd = new FormData();
    var error = false;
    fd.append('_token', token);
    title = formName.find("input.title");
    hour_start = formName.find(".hour_start");
    hour_end = formName.find(".hour_end");
    font_size = formName.find(".font_size");
    name_customer = formName.find(".name_customer");
    language = formName.find(".language");
    option = formName.find(".option");

    if (formName.find('.option:checked').val() == OPTION_WELCOME) {
//        if (!name_customer.val().trim()) {
//            error = true;
//            formName.find("input.name_customer").after('<p class="word-break error-validate error error-name_customer" for="name_customer">' + name_customer.attr('data-message') + '</p>')
//        }
    } else if (formName.find('.option:checked').val() == OPTION_NOMAL) {
        if (formName.find('.type:checked').val() == TYPE_IMAGE) {
            if (fileSelect) {
                var listFiles = $("#" + inputName).filer()[0].jFiler.files_list;
                if (!listFiles.length) {
                    error = true;
                    formName.find("div.jFiler-theme-dragdropbox").after('<p class="word-break error-validate error" for="filer_input2">' + formName.find("#" + inputName).attr('data-message') + '</p>')
                }
            }
        } else {
            urlVideoYoutube = formName.find('.url_video');
            videoId = getYouTubeId(urlVideoYoutube.val().trim());
            if (typeof videoId == 'undefined') {
                error = true;
                classPreview = $('.preview-video');
                classPreview.find('iframe').attr('src', '');
                classPreview.addClass('display-none');
                formName.find("input.url_video").after('<p class="word-break error-validate error error-url_video" for="url_video">' + urlVideoYoutube.attr('data-message') + '</p>')
            }
            //     if (typeof id !== 'undefined') {
            //         if($('#file_video_edit')) {
            //             if (!$('#file_video_edit').val() && formName.find('.preview-video').hasClass('display-none')) {
            //                 error = true;
            //                 formName.find(".input-video .btn-file").after('<p class="word-break error-validate error file-video-error" for="file_video">' + formName.find("#file_video_edit").attr('data-message') + '</p>')
            //             }
            //         }
            //     } else {
            //         if($('#file_video')) {
            //             if (!$('#file_video').val() && formName.find('.preview-video').hasClass('display-none')) {
            //                 error = true;
            //                 formName.find(".input-video .btn-file").after('<p class="word-break error-validate error file-video-error" for="file_video">' + formName.find("#file_video").attr('data-message') + '</p>')
            //             }
            //         }
            //     }
        }
    } else if (formName.find('.option:checked').val() == OPTION_QUOTATIONS) {
        // TODO validate quotation
    }  else if (formName.find('.option:checked').val() == OPTION_BIRTHDAY) {
        // TODO validate birthday
    }
    if (!title.val().trim()) {
        error = true;
        formName.find("input.title").after('<p class="word-break error-validate error error-title" for="title">' + title.attr('data-message') + '</p>')
    }
    if (!hour_start.val().trim()) {
        error = true;
        formName.find(".select-hour-start .select2-container").after('<p class="word-break error-validate error" for="hour_start">' + hour_start.attr('data-message') + '</p>')
    }
    if (!hour_end.val().trim()) {
        error = true;
        formName.find(".select-hour-end .select2-container").after('<p class="word-break error-validate error" for="hour_end">' + hour_end.attr('data-message') + '</p>')
    }
    
    if (error) {
        return false;
    }
    fd.append('title', title.val().trim());
    fd.append('hour_start', hour_start.val().trim());
    fd.append('hour_end', hour_end.val().trim());
    fd.append('date', $('#date').val());
    fd.append('option', formName.find('.option:checked').val());

    if (typeof id !== 'undefined') {
        fd.append('id', id);
    }
    var repeat = [];
    formName.find('.repeat:checked').each(function(i){
        fd.append('repeat[]', $(this).val());
    });
    if (formName.find('.option:checked').val() == OPTION_WELCOME) {
//        fd.append('name_customer', name_customer.val().trim());
        if(name_customer.val().trim()) {
            fd.append('name_customer', name_customer.val().trim());
        } else {
            fd.append('name_customer', '');
        }
        fd.append('language', language.val().trim());

        if (fileSelect) {
            var listFiles = $("#" + inputName).filer()[0].jFiler.files_list;
            var listFileObjects = $("#" + inputName).filer()[0].jFiler.files;
            fd.append('number_file', listFiles.length);
            $.each(listFiles, function (key, value) {
                if (value.file.size) {
                    fd.append("image_" + key, value.file);
                } else {
                    fd.append("image_" + key, value.file.name);
                }
            });
        }
    } else if (formName.find('.option:checked').val() == OPTION_NOMAL) {
        fd.append('type', formName.find('.type:checked').val());
        if (formName.find('.type:checked').val() == TYPE_IMAGE) {
            if (fileSelect) {
                var listFiles = $("#" + inputName).filer()[0].jFiler.files_list;
                var listFileObjects = $("#" + inputName).filer()[0].jFiler.files;
                fd.append('number_file', listFiles.length);
                $.each(listFiles, function (key, value) {
                    if (value.file.size) {
                        fd.append("image_" + key, value.file);
                    } else {
                        fd.append("image_" + key, value.file.name);
                    }
                });
            }
            fd.append('font_size', font_size.val().trim());
        } else {
            fd.append('video_id', videoId);
            // if (typeof id !== 'undefined') {
            //     fileVideo = $('#file_video_edit')[0].files[0];
            //     if (typeof fileVideo !== 'undefined') {
            //         fd.append('video', fileVideo); 
            //     }
            // } else {
            //     fileVideo = $('#file_video')[0].files[0];
            //     if (typeof fileVideo !== 'undefined') {
            //         fd.append('video', fileVideo); 
            //     }
            // }
        }
        
        if (formName.find('.type:checked').val() == TYPE_IMAGE) {
            formName.find('li div.jFiler-item-container').each(function (index, e) {
                fd.append('description_image[]', $(e).find('.input-description-image').val());
            });
        }
    } else if (formName.find('.option:checked').val() == OPTION_QUOTATIONS) {
        formName.find('.quotation-input[name]').each(function() {
            fd.append($(this).attr('name'), $(this).val().trim());
        });
    } 

    var dataSend = fd;
    var url = urlCreateSlide;
    if (button.data('requestRunning')) {
        return false;
    }
    button.data('requestRunning', true);
    button.attr('disabled', 'disabled');
    button.find('.fa-spin').removeClass('display-none');
    $.ajax({
        data: dataSend,
        url: url,
        type: 'post',
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        xhr: function(){
            //upload Progress
            var xhr = $.ajaxSettings.xhr();
            if (xhr.upload) {
                xhr.upload.addEventListener('progress', function(event) {
                    var percent = 0;
                    var position = event.loaded || event.position;
                    var total = event.total;
                    if (event.lengthComputable) {
                        percent = Math.ceil(position / total * 100);
                    }

                    var $process = formName.find('.progress');
                    $process.removeClass('display-none');
                    $processBar = $process.find('.progress-bar');
                    $processBar.attr('aria-valuenow', percent);
                    $processBar.css({
                        width: percent + '%'
                    });
                    $processBar.find('span').text(percent + '% complete' );
                }, true);
            }
            return xhr;
        },
        success: function ($data) {
            if(!$data.status) {
                if ($data.message_error.title) {
                    formName.find("input.title").after('<p class="word-break error-validate error error-title" for="title">' + $data.message_error.title[0] + '</p>');
                }
                if($data.hour_error) {
                    formName.find(".select-hour-start .select2-container").after('<p class="word-break error-validate error" for="hour_start">' + $data.hour_error[0] + '</p>');
                } else {
                    if ($data.message_error.hour_start) {
                        formName.find(".select-hour-start .select2-container").after('<p class="word-break error-validate error" for="hour_start">' + $data.message_error.hour_start[0] + '</p>');
                    }
                }
                if ($data.message_error.hour_end) {
                    formName.find(".select-hour-end .select2-container").after('<p class="word-break error-validate error" for="hour_end">' + $data.message_error.hour_end[0] + '</p>');
                }
                if ($data.repeat_error) {
                    formName.find(".type-repeat").after('<p class="word-break error-validate error col-sm-6" for="repeat">' + $data.repeat_error[0] + '</p>');
                } else {
                    if ($data.message_error.repeat) {
                        formName.find(".type-repeat").after('<p class="word-break error-validate error col-sm-6" for="repeat">' + $data.message_error.repeat[0] + '</p>');
                    }
                }
                if ($data.message_error.type) {
                    formName.find(".checkbox-type-slide .col-sm-10").after('<p class="word-break error-validate error col-sm-8 col-sm-offset-2" for="type">' + $data.message_error.type[0] + '</p>');
                }
                if ($data.message_error.option) {
                    formName.find("input.option").after('<p class="word-break error-validate error error-option" for="option">' + $data.message_error.option[0] + '</p>');
                }
                if ($data.message_error.name_customer) {
                    formName.find("input.name_customer").after('<p class="word-break error-validate error error-name_customer" for="name_customer">' + $data.message_error.name_customer[0] + '</p>');
                }
                if ($data.message_error.language) {
                    formName.find(".select-language .select2-container").after('<p class="word-break error-validate error" for="language">' + $data.message_error.language[0] + '</p>');
                }
                if ($data.message_error.quotation) {
                    formName.find(".btn-add-quotations").before('<p class="word-break error-validate error" for="quotation">' + $data.message_error.quotation[0] + '</p>');
                }
            } else {
                $modal = $('.modal-slide');
                if($data.success) {
                    var $this = $('#date');
                    $value = $this.val();
                    listSlideByDate($this, $value, $data.id);
                    $modal.find('.modal').removeClass().addClass('modal modal-success');
                    $modal.find('.modal-title-success').removeClass('display-none');
                    $modal.find('.modal-title-error').addClass('display-none');
                    $modal.find('.text-message').html($data.message);
                    $modal.find('.modal').modal();
                } else {
                    $modal.find('.modal').removeClass().addClass('modal modal-danger');
                    $modal.find('.modal-title-success').addClass('display-none');
                    $modal.find('.modal-title-error').removeClass('display-none');
                    $modal.find('.text-message').html($data.message);
                    $modal.find('.modal').modal();
                }
            }
        },
        complete: function() {
            button.data('requestRunning', false);
            button.removeAttr('disabled');
            button.find('.fa-spin').addClass('display-none');
            formName.find('.progress').addClass('display-none');
        }
    });
}
/**
 * get extension file
 * @param {string} file
 * @returns string
 */
function getExtensionFile(file) {
    return file.substr((file.lastIndexOf('.') + 1));
}

function localFileVideoPlayer(formName, inputName) {
    'use strict'
    var URL = window.URL || window.webkitURL
    $('.file-video-error').remove();
    var playSelectedFile = function (event) {
    var file = this.files[0]
    var type = file.type
    var videoNode = document.querySelector('#' + formName + ' .preview-video video')
    var canPlay = videoNode.canPlayType(type)
    if (canPlay === '') canPlay = 'no'
        var isError = canPlay === 'no'
        if (isError) {
            $('#' + inputName).val('');
            alert('You must upload video');
          return
        }
        if (file.size/1024/1024 > 200) {
            alert('You must be less than 200 MB');
        }
        var fileURL = URL.createObjectURL(file)
        videoNode.src = fileURL
        $('.file-video-error').remove();
        $('#' + formName + ' .preview-video').removeClass('display-none');
    }
    var inputNode = document.querySelector('#' +inputName)
    inputNode.addEventListener('change', playSelectedFile, false)
}

function changeVideo(formName, element) {
    var URL = window.URL || window.webkitURL
    $('.file-video-error').remove();
    var file = element.files[0]
    var videoNode = document.querySelector('#' + formName + ' .preview-video video')
    if (typeof file !== 'undefined') {
        var type = file.type
        var canPlay = videoNode.canPlayType(type)
        if (canPlay === '') canPlay = 'no'
        var isError = canPlay === 'no'
        if (isError) {
            $(element).val('');
            alert('You must upload video');
          return
        }
        if (file.size/1024/1024 > 200) {
            alert('You must be less than 200 MB');
        }
        var fileURL = URL.createObjectURL(file)
        videoNode.src = fileURL
        $('.file-video-error').remove();
        $('#' + formName + ' .preview-video').removeClass('display-none');
    } else {
        videoNode.src = '';
        $('#' + formName + ' .preview-video').addClass('display-none');
    }
}
function loadInterval(formName, $slideId) {
    hour_start = $('#' + formName).find('.hour_start').val();
    hour_end = $('#' + formName).find('.hour_end').val();
    url = urlGetTemplateInterval;
    data = {
        _token: token,
        hour_start: hour_start,
        hour_end: hour_end,
    }
    if (typeof $slideId !== 'undefined') {
        data['id'] = $slideId;
    }
    $.ajax({
        type: 'post',
        url: urlGetTemplateInterval,
        dataType: 'json',
        data: data,
        success: function ($data) {
            $('#' + formName).find('.type-repeat').html($data.content);
        }
    });
}

var QueryString = function () {
  // This function is anonymous, is executed immediately and 
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
        // If first entry with this name
    if (typeof query_string[pair[0]] === "undefined") {
      query_string[pair[0]] = decodeURIComponent(pair[1]);
        // If second entry with this name
    } else if (typeof query_string[pair[0]] === "string") {
      var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
      query_string[pair[0]] = arr;
        // If third or later entry with this name
    } else {
      query_string[pair[0]].push(decodeURIComponent(pair[1]));
    }
  } 
  return query_string;
}();

function getYouTubeId(url) {
    return getVideoId(url, [
        'youtube.com/watch?v=',
        'youtu.be/',
        'youtube.com/embed/'
    ]);
}

function getVideoId(str, prefixes) {
    var cleaned = str.replace(/^(https?:)?\/\/(www\.)?/, '');
    for(var i = 0; i < prefixes.length; i++) {
        if (cleaned.indexOf(prefixes[i]) === 0)
            return cleaned.substr(prefixes[i].length)
    }
    return undefined;
}

function changeOptionSlide(form, valueOption) {
    var inputTitleForm = form.find('.title-slide');
    var inputTitle = inputTitleForm.find('.title');
    var inputLanguage = form.find('.input-language');
    form.find('.error-validate').remove();
    if (!form.hasClass('is-update')) {
        inputTitle.val('');
    }
    form.find('[data-slide-option]').addClass('display-none');
    form.find('[data-slide-option="' + valueOption + '"]').removeClass('display-none');
    if (valueOption == 0 | valueOption == 2 | valueOption == 3) {
//        form.find('.option-nomal').removeClass('display-none');
//        form.find('.option-welcome').addClass('display-none');
        inputTitleForm.find('.control-label').text(labelTitle);
        inputTitle.attr('data-message', requiredTitle);
        inputTitle.attr('placeholder', labelTitle);
//        inputLanguage.addClass('display-none');
        if (form.hasClass('is-update')) {
            if (inputTitle.hasClass('is-company')) {
                inputTitle.val('');
            } else {
                inputTitle.val(inputTitle.attr('data-value'));
            }
        }
    } else {
//        form.find('.option-nomal').addClass('display-none');
//        form.find('.option-welcome').removeClass('display-none');
        inputTitleForm.find('.control-label').html(labelCompany);
        inputTitle.attr('data-message', requiredCompany);
        inputTitle.attr('placeholder', labelCompany);
//        inputLanguage.removeClass('display-none');
        if (form.hasClass('is-update')) {
            if (!inputTitle.hasClass('is-company')) {
                inputTitle.val('');
            } else {
                inputTitle.val(inputTitle.attr('data-value'));
            }
        }
    }
}

function displayLogo(file, form) {
    if (file.length) {
        fileUpload = file[0];
        if($.inArray(fileUpload.type, ["image/jpeg","image/png","image/gif", "image/bmp"]) < 0) {
            alert('You must upload image');
            form.find('.image-preview').addClass('display-none')
            form.find('#image-preview').attr('src', '');
            form.find('.logo_company').val('');
            return true;
        } else if (fileUpload.size / Math.pow(1024, 2) > 1) {
            alert("Size image must be less than 1MB");
            form.find('.image-preview').addClass('display-none')
            form.find('#image-preview').attr('src', '');
            form.find('.logo_company').val('');
            return true;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            form.find('#image-preview');
            form.find('.image-preview').removeClass('display-none')
            form.find('#image-preview').attr('src', e.target.result);
            form.find('#image-preview').css('height', '110px');
        };
        reader.readAsDataURL(file[0]);
    }
}
