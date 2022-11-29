/**
 * Show modal display file on browser
 * @param {string} url
 */
function viewFile(type, url) {
    var $downloadContainer = $('.disabled-view-full');
    if (type == typeCv) {
        $downloadContainer.html($('.download-html').html());
    } else {
        $downloadContainer.html('');
    }
    $('#modal-view-file iframe').html('');
    $('#modal-view-file iframe').attr('src', url);
    $('#modal-view-file').modal('show');
}

/*
 * custom shorted button text bootstrap multiselect
 */
function customBtnText(options) {
    if (options.length === 0) {
        return 'None selected';
    } else if (options.length == 1) {
        var text = $(options[0]).text();
        var btnToggle = $(options[0]).parent().parent('div');
        var btnWidth = btnToggle.width();
        btnWidth = btnWidth < 230 ? 230 : btnWidth;
        var maxLen = (btnWidth - 50) / 7; //7px per charactor
        if (text.length > maxLen) {
            text = text.substr(0, parseInt(maxLen)) + '...';
        }
        return text;
    } else if (options.length > 1) {
        return options.length + ' selected';
    }
    var labels = [];
    options.each(function () {
        var text = $(this).text().replace('-- ', '');
        labels.push(text);
    });
    return labels.join(', ');
}
