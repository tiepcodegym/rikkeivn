jQuery(document).ready(function($) {
    $(document).on('change', '#file_video', function (event) {
        var URL = window.URL || window.webkitURL
        $('.file-video-error').remove();
        $this = this;
        var file = $this.files[0]
        var videoNode = document.querySelector('.preview-video video')
        if (typeof file !== 'undefined') {
            var type = file.type
            var canPlay = videoNode.canPlayType(type)
            if (canPlay === '') canPlay = 'no'
            var isError = canPlay === 'no'
            if (isError) {
                console.log($this);
                $(this).val('');
                alert('You must upload video');
              return
            }
            if (file.size/1024/1024 > 200) {
                alert('You must be less than 200 MB');
            }
            var fileURL = URL.createObjectURL(file)
            videoNode.src = fileURL
            $('.file-video-error').remove();
            $('.preview-video').removeClass('display-none');
        } else {
            videoNode.src = '';
            $('.preview-video').addClass('display-none');
        }
    });

    $(document).on('change', '.url', function (event) {
        value = $(this).val().trim();
        videoId = getYouTubeId(value);
        classPreview = $('.preview-video');
        $('.error-url').remove();
        if (typeof videoId == 'undefined') {
            classPreview.find('iframe').attr('src', '');
            classPreview.addClass('display-none');
            $('#fr-upload-video').find("input.url").after('<p class="word-break error-validate error error-url" for="url">' + $(this).attr('data-incorrect') + '</p>')
        } else {
            src = "https://www.youtube.com/embed/"+videoId+"?playlist="+videoId+"&loop=1&autoplay=1&cc_load_policy=1&rel=0&amp;controls=1&amp;showinfo=0&vq=hd1080";
            classPreview.find('iframe').attr('src', src);
            classPreview.removeClass('display-none');
        }
    });
    $(document).on('click', '.btn-upload', function (event) {
        $this = $(this);
        event.preventDefault();
        if ($this.hasClass('requestRunning')) {
            return false;
        } else {
            $('.error-validate').remove();
            var error = false;
            formName = $('#fr-upload-video');
            title = formName.find("input.title");
            url = formName.find("input.url");
            if (!title.val().trim()) {
                error = true;
                formName.find("input.title").after('<p class="word-break error-validate error error-title" for="title">' + title.attr('data-message') + '</p>')
            }
            if (!url.val().trim()) {
                error = true;
                formName.find("input.url").after('<p class="word-break error-validate error error-url" for="url">' + title.attr('data-message') + '</p>')
            } else {
                urlVideoYoutube = formName.find('.url');
                videoId = getYouTubeId(urlVideoYoutube.val().trim());
                if (typeof videoId == 'undefined') {
                    error = true;
                    classPreview = $('.preview-video');
                    classPreview.find('iframe').attr('src', '');
                    classPreview.addClass('display-none');
                    formName.find("input.url").after('<p class="word-break error-validate error error-url" for="url">' + urlVideoYoutube.attr('data-incorrect') + '</p>')
                } else {
                    $('.url').val(videoId);
                }
            }
            // if($('#file_video')) {
            //     if (!$('#file_video').val() && formName.find('.preview-video').hasClass('display-none')) {
            //         error = true;
            //         formName.find(".input-video .btn-file").after('<p class="word-break error-validate error file-video-error" for="file_video">' + formName.find("#file_video").attr('data-message') + '</p>')
            //     }
            // }
            if (error) {
                return false;
            }
          
            $this.addClass('requestRunning');
            $this.attr('disabled', 'disabled');
            $this.find('.fa-spin').removeClass('display-none');
            // formName.ajaxSubmit({
            //     beforeSubmit: function() {
            //         var $process = formName.find('.progress');
            //         $process.removeClass('display-none');
            //     },
            //     uploadProgress: function(event, position, total, percentComplete) {
            //         $processBar = formName.find('.progress-bar')
            //         $processBar.attr('aria-valuenow', percentComplete);
            //         $processBar.css({
            //             width: percentComplete + '%',
            //         });
            //         $processBar.find('span').text(percentComplete + '% complete' );
            //     },
            //     success: function(data) {
            //         formName.find('.progress').addClass('display-none');
            //         window.location.href = urlSetting;
            //     },
            // }); 
            formName.submit();
        }
    });
});

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