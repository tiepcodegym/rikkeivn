$(document).ready(function () {
    var globalIndex = 1;

    $('#form-create-update').find('input, select, textarea, a').attr('disabled', true);
    $('.detail_class_choose').removeClass('hidden');
    if (globalType == 1) {
        $('#course').removeClass('hidden');
        $('#class').removeClass('hidden');
    }
    if (varGlobalPassModule.dataRegisterTime.length > 0) {
        for(var i = 0; i < varGlobalPassModule.dataRegisterTime.length; i++){
            $('.tblDetailInput').append(renderHtmlDeitalShift((i+1), varGlobalPassModule.dataRegisterTime[i]));
            globalIndex = globalIndex + 1;
        }
    } else {
        $('.tblDetailInput').append(renderHtmlDeitalShift(globalIndex, null));
    }

    $('table>tbody.tblDetailInput').find('input').attr('disabled', true);
});
