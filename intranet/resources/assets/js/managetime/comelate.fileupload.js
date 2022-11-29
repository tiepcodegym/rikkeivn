$(function() {
    $('.comelate-upload-file input:file').fileuploader({
        addMore: true,
        extensions : ['jpg', 'jpeg', 'png', 'bmp'],
        captions: {
            button: function(options) { return 'Choose image'; },
            feedback: function(options) { return text_upload_image; },
        },
    });
});