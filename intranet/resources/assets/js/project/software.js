$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
});

var globalData;
var globalurl;
var globalClassName;

$('#modal-warn-confirm .btn-ok').on('click', function () {
    globalData.approved = true;
    submitAjax(globalData);
});

function submitAjax(data) {
    $.ajax({
        url: globalurl,
        type: 'post',
        data: data,
        success: function (data) {
            $('.slove-' + globalClassName + ' .remove-' + globalClassName).addClass('display-none');
            $('.slove-' + globalClassName + ' .add-' + globalClassName).removeClass('display-none');
            showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
            $('.workorder-' + globalClassName).html(data.content);
            $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
        },
        error: function () {
            $('#modal-warning-notification .text-default').html(messageError);
            $('#modal-warning-notification').modal('show');
        },
        complete: function () {
            // Nothing
        }
    });
}

$(document).on('blur', '.amount-active', function (event) {
    mainUpdatePoint(event.target);
});

function mainUpdatePoint(e) {
    var valueCost = $(e).val().split(",");
    var inputUpdatedPoint = parseFloat(valueCost[0].split(".").join(""));
    var storePoint = 0;

    if (!Number.isNaN(inputUpdatedPoint)) {
        switch (true) {
            case inputUpdatedPoint <= 0:
                break;
            default:
                storePoint = inputUpdatedPoint;
        }
    }
    $(e).val(formatNumber(storePoint) + ',000');
}

function formatNumber(num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
}
