function setCheckItem(sessionKeys) {
    var opporCheckedIds = RKSession.getRawItem(sessionKeys);
    if (opporCheckedIds) {
        for (var i = 0; i < opporCheckedIds.length; i++) {
            $('.check-item[value="' + opporCheckedIds[i] + '"]').prop('checked', true);
        }
        $('.check-all').prop('checked', $('.check-item').length === $('.check-item:checked').length);
    }

    $('.check-all').click(function() {
        $('.check-item').prop('checked', $(this).is(':checked')).trigger('change');
    });

    $('.check-item').change(function() {
        var checkAll = $('.check-all');
        checkAll.prop('checked', $('.check-item').length === $('.check-item:checked').length);
        var checkedIds = RKSession.getRawItem(sessionKeys);
        var index = checkedIds.indexOf($(this).val() + '');
        if ($(this).is(':checked')) {
            if (index < 0) {
                checkedIds.push($(this).val());
            }
        } else {
            if (index > -1) {
                checkedIds.splice(index, 1);
            }
        }
        RKSession.setRawItem(sessionKeys, checkedIds);
    });
}

function getCheckItem(sessionKeys) {
    return RKSession.getRawItem(sessionKeys);
}

function removeCheckItem(sessionKeys) {
    RKSession.removeItem(sessionKeys);
    $('.check-all').prop('checked', false);
    $('.check-item').prop('checked', false);
}