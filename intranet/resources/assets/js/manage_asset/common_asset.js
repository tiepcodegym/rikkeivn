/**
 * Check valid file import
 */
function checkValidFile() {
    $('#form-import-supplier').validate({
        rules: {
            'file_upload' : {
                required: true,
                validFileImport: true,
            },
        },
        messages: {
            'file_upload' : {
                required: requiredText,
                validFileImport: invalidExFile,
            },
        },
    });
    jQuery.validator.addMethod("validFileImport", function(value, element) {
        var extension = value.replace(/^.*\./, '');
        return jQuery.inArray(extension, ['xls', 'xlsx', 'csv']) !== -1;

    }, 'File extension not invalid');
}

function readMore() {
    $(".read-more").shorten({
        "showChars" : 200,
        "moreText"  : "See more",
        "lessText"  : "Less",
    });
}