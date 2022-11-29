$(document).ready(function () {
    $.validator.addMethod("checkLevelExist", function(value, element, arg){
        var levels = value.split(',');
        return !checkDuplicate(levels);
    });
    $("#frm-lang-level").validate({
        rules: {
            name: "required",
            language_level: {checkLevelExist: true}
        },
        messages: {
            name: requiredText,
            language_level: {checkLevelExist: levelDuplicateText}
        }
    });
});
