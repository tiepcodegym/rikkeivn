$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
});
$('#approve').change(function(){
    var value = $(this).val();
    if (value == approveOff || value == approveYet) {
        $('#type').parent().parent().addClass('hidden');
        $('#recruiter').parent().parent().addClass('hidden');
    } else {
        $('#type').parent().parent().removeClass('hidden');
        if ($('#type').val() == typeRecruit) {
            $('#recruiter').parent().parent().removeClass('hidden');
        }
    }
});

$('#type').change(function(){
    if ($(this).val() == typeRecruit) {
        $('#recruiter').parent().parent().removeClass('hidden');
    } else {
        $('#recruiter').parent().parent().addClass('hidden');
    }
});

TYPE_CHANNEL = 99;

$(document).on('click', '.add-channel', function () {

    addChannel('channel');

});

$(document).on('click', '.remove-channel', function (event) {
    removeChannel('channel');
});

$(document).on('click', '.delete-channel', function () {
    deleteChannel('channel', this, urlAddChannel);
});

$(document).on('click', '.save-channel', function () {
    saveChannel('channel', this, urlAddChannel);
});

$(document).on('click', '.edit-channel', function () {
    id = $(this).data('id');
    $('.tr-channel-' + id + ' select').select2();
    editChannel('channel', this);
});

$(document).on('click', '.add-new-channel', function () {
    addNewChannel('channel', this, urlAddChannel);

});

function addChannel(className) {
    $('.error-validate-' +className).remove();

    $('.tr-' + className).removeClass('display-none');
    $('.tr-add-' + className).addClass('display-none');

    $('.slove-' + className + ' .remove-' + className).removeClass('display-none');
    $('.slove-' + className + ' .add-' + className).addClass('display-none');
}

function deleteChannel(className, element, url) {
    dataId = $(element).data('id');
    dataClassName = className;
    dataUrl = url;
    $('#modal-delete-confirm-new').modal('show');
    $(document).on('click', '#modal-delete-confirm-new .btn-ok', function () {
        var request_id = $('input[name=request_id]').val();
        $('#modal-delete-confirm-new').modal('hide');
        $('.edit-'+className+'-'+dataId).addClass('display-none');
        $('#loading-item-'+dataId).removeClass('display-none');
        data = {
            _token: token,
            request_id: request_id,
            channel_id: dataId,
            isDelete: true
        };
        $.ajax({
            url: dataUrl,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (data) {
                if (data.status) {
                    $('.edit-'+className+'-'+dataId).removeClass('display-none');
                    $('#loading-item-1-'+dataId).addClass('display-none');
                    $('.workorder-' + className).html(data.content);
                } else {
                    $('#modal-warning-notification .text-default').html(messageError);
                    $('#modal-warning-notification').modal('show');
                }
            },
            error: function () {
                $('#modal-warning-notification .text-default').html(messageError);
                $('#modal-warning-notification').modal('show');
            },
            complete: function () {
                $('#modal-delete-confirm-new .btn-ok').data('requestRunning', false);
            },
        });

    });
}

function editChannel(className, element) {

    id = $(element).data('id');
    $(element).addClass('display-none');
    $('.save-' + className + '-' + id).removeClass('display-none');
    $('.delete-' + className + '-' + id).addClass('display-none');
    $('.refresh-' + className + '-' + id).removeClass('display-none');

    setTimeout(function () {
        input = $('.input-topic-' + className + '-' + id)
        input.focus();
        var tmpStr = input.val();
        input.val('');
        input.val(tmpStr);
    }, 0);

    $('.channel_id-' + className + '-' + id).addClass('display-none');
    $('.url-' + className + '-' + id).addClass('display-none');
    $('.input-url-' + className + '-' + id).removeClass('display-none');

    $('.cost-' + className + '-' + id).addClass('display-none');
    $('.input-cost-' + className + '-' + id).removeClass('display-none');
}

$(document).on('click', '.refresh-channel', function () {
    id = $(this).data('id');
    var className = 'channel';
    $('.tr-' + className + '-' + id + ' .select2').addClass('display-none');
    $('.channel_id-' + className + '-' + id).removeClass('display-none');
    $('.select-channel_id-' + className + '-' + id).val($('.input-channel_id-' + className + '-' + id).val());

    $(this).addClass('display-none');
    $('.save-' + className + '-' + id).addClass('display-none');
    $('.edit-' + className + '-' + id).removeClass('display-none');
    $('.delete-' + className + '-' + id).removeClass('display-none');
    $('.refresh-' + className + '-' + id).addClass('display-none');

    $('.url-' + className + '-' + id).removeClass('display-none');
    $('.input-url-' + className + '-' + id).addClass('display-none');

    $('.cost-' + className + '-' + id).removeClass('display-none');
    $('.input-cost-' + className + '-' + id).addClass('display-none');

    $('.input-url-' + className + '-' + id).val($('.url-' + className + '-' + id).text());
    $('.input-cost-' + className + '-' + id).val($('.cost-' + className + '-' + id).text());

    $('.error-validate').remove();

});

function saveChannel(className, e, url) {
    $(e).data('requestRunning', true);
    var id = $(e).data('id');
    $(e).addClass('display-none');
    $('#loading-item-' + id).removeClass('display-none');
    $('.error-validate-' + className + '-' + id).remove();

    var channel_id = $('.select-channel_id-channel-' +id).val();
    var channel_url = $('.input-url-' + className + '-' + id).val();
    var channel_cost = $('.input-cost-' + className + '-' + id).val();
    var request_id = $('input[name=request_id]').val();
    var rc_id = $('.input-rc_id-' + className + '-' + id).val();
    data = {
        _token: token,
        cost: channel_cost,
        url: channel_url,
        channel_id: channel_id,
        request_id: request_id,
        old_channel_id: id,
        rc_id: rc_id
    };

    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        data: data,
        success: function (data) {
            if (data.status) {
                $('.workorder-' + className).html(data.content);
            } else {

                if (data.message_error) {
                    if (data.message_error.url) {
                        $('.input-url-' + className + '-' + id).after('<p class="word-break error-validate error-validate-' + className + '-' + id + ' error-' + className + '" for="url">' + data.message_error.url[0] + '</p>');
                    }
                    if (data.message_error.cost) {
                        $('.input-cost-' + className + '-' + id).after('<p class="word-break error-validate error-validate-' + className + '-' + id + ' error-' + className + '" for="url">' + data.message_error.cost[0] + '</p>');
                    }
                    if (data.message_error.channel_id) {
                        $('.select-channel_id-' + className + '-' + id).parent().append('<p class="word-break error-validate error-validate-' + className + '-' + id + ' error-' + className + '" for="url">' + data.message_error.channel_id[0] + '</p>');
                    }
                }
            }
        },
        error: function () {
            $('#modal-warning-notification .text-default').html(messageError);
            $('#modal-warning-notification').modal('show');
        },
        complete: function () {
            $(e).data('requestRunning', false);
            $('#loading-item-' + id).addClass('display-none');
            $(e).removeClass('display-none');
        },
    });
}

function removeChannel(className) {


    $('.tr-' + className).addClass('display-none');
    $('.tr-add-' + className).removeClass('display-none');
    $('.url-' + className).text('');
    $('.cost-' + className).text('');
    $('.input-url-' + className).val('');
    $('.input-cost-' + className).val('');
    $('.select-channel_id-' + className ).val($('.select-channel_id-' + className + ' option:first').val());
    $('.error-' + className).remove();
}

//cost format
numberFormat($('.num'));

function addNewChannel(className, element, url, type) {
    if ($(element).data('requestRunning')) {
        return;
    }
    $(element).data('requestRunning', true);
    $(element).addClass('display-none');
    $('#loading-item').removeClass('display-none');
    $('.error-validate-add-' + className).remove();

    var request_id = $('input[name=request_id]').val();
    var channel_id = $('.select-channel_id-' + className).val();
    var channel_url = $.trim($('.input-url-' + className).val());
    var channel_cost = $.trim($('.input-cost-' + className).val());
    var data = {
        _token: token,
        request_id: request_id,
        channel_id: channel_id,
        url: channel_url,
        cost: channel_cost,
    };

    $.ajax({
        url: url,
        type: 'post',
        data: data,
        success: function (data) {
            if (data.status) {
                $('.slove-' + className + ' .remove-' + className).addClass('display-none');
                $('.slove-' + className + ' .add-' + className).removeClass('display-none');

                $('.workorder-' + className).html(data.content);

            } else {
                if (data.message_error) {
                    if (data.message_error.url) {
                        $('.input-url-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="url">' + data.message_error.url[0] + '</p>');
                    }
                    if (data.message_error.cost) {
                        $('.input-cost-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="url">' + data.message_error.cost[0] + '</p>');
                    }
                    if (data.message_error.channel_id) {
                        $('.select-channel_id-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="url">' + data.message_error.channel_id[0] + '</p>');
                    }
                }
            }
        },
        error: function () {
            $('#modal-warning-notification .text-default').html(messageError);
            $('#modal-warning-notification').modal('show');
        },
        complete: function () {
            $(element).data('requestRunning', false);
            $('#loading-item').addClass('display-none');
            $(element).removeClass('display-none');
        },
    });
}

$(document).ready(function() {
    RKfuncion.keepStatusTab.init();
    $('.teams-container .btn-add-position').remove();
    $('.teams-container .btn-danger').remove();
    $('.teams-container .btn-delete-row').remove();
    $('.teams-container .btn-add-team').remove();
    $('.teams-container .team').prop('disabled', true);
    $('.teams-container .position-apply').prop('disabled', true);
    $('.teams-container .number-resource').prop('readonly', true);
    $('#modal-teams .save-team').remove();
    $('#modal-teams .modal-footer .btn').text('Close');
    $('#recruiter').select2();
    $('#type').select2();
    $('#approve').select2();
    $('.position-apply').select2();
    selectSearchReload();
    $('.teams-container .number-resource').each(function() {
        $parentObject = $(this).parent().parent();
        $parentObject.addClass('col-md-6').removeClass('col-md-5');
    });
    // show modal dealine warning
    if(deadlineWarning) {
        $('#modal-deadline-warning').modal('show');
    }
});

/*
 * Show modal team of request
 * @returns {undefined}
 */
function showTeam() {
    $('#modal-teams').modal('show');
}

/**
 * Show modal request content
 * @returns {undefined}
 */
function showContent() {
    $('#modal-content').modal('show');
}

/**
 * Show modal number resource info
 * @returns {undefined}
 */
function showNumberResourceInfo() {
    $('#modal-number-resource-info').modal('show');
}

/**
 * Init table candidate list of request
 * @param {type} param
 */
$('#candidate-table').DataTable({
    processing: true,
    lengthChange: false,
    bFilter: false,
    serverSide: true,
    ajax: urlCandidateList,
    pageLength: 10,
    columns: [
        {data: 'id', name: 'id'},
        {data: 'email', name: 'email'},
        {data: 'fullname', name: 'fullname'},
        {data: 'team_name', name: 'team_name'},
        {data: 'positions', name: 'positions'},
        {data: 'team_selected', name: 'team_selected'},
        {data: 'position_apply', name: 'position_apply'},
        {data: 'recruiter', name: 'recruiter'},
        {data: 'programs_name', name: 'programs_name'},
        {data: 'status', name: 'status'},
        {data: 'type', name: 'type'},
        {data: 'test_mark', name: 'test_mark'},
        {data: 'specialize_score', name: 'specialize_score'},
    ],
    createdRow: function( row, data, dataIndex ) {
        $( row ).addClass('cursor-pointer');
        var id = $( row ).find('td:eq(0)').text();
        $( row ).attr('onclick', 'candidateDetail('+id+');')
    }
});

/**
 * Redirect to candidate detail
 * @param {int} id
 * @returns new tab
 */
function candidateDetail(id) {
    window.open(baseUrl + 'resource/candidate/detail/' + id, '_blank');
}

$('.btn-preview').on("click", function() {
    var formId = $('div.request-info');
    $('#job_title').html(formId.find('.title').text());
    $('#job_expired').html(formId.find('.deadline').text());
    var description = formId.find('.description').text().split( "\n" );
    var job_qualifi = formId.find('.job_qualifi').text().split( "\n" );
    var benefits = formId.find('.benefits').text().split("\n");
    $('.job_des').html('');
    $('.job_qualification').html('');
    $('.job_ben').html('');
    $('#position').html('');
    var i;
    for (i = 0; i < description.length; i++) {
        var data = $.trim(description[i]);
        if (data) {
            $('.job_des').append('<li class="desc">'+ data +'</li>');
        }
    }
    for (i = 0; i < job_qualifi.length; i++) {
        var data = $.trim(job_qualifi[i]);
        if (data) {
            $('.job_qualification').append('<li class="qualification">'+ data +'</li>');
        }
    }
    for (i = 0; i < benefits.length; i++) {
        var data = $.trim(benefits[i]);
        if (data) {
            $('.job_ben').append('<li class="benefit">'+ data +'</li>');
        }
    }
    $('.teams-container .box-position').each(function () {
        var position = $(this).find('select.position-apply').val();
        var number = $(this).find('.number-resource').val();
        var positionName = $(this).find('select.position-apply option:selected').text();
        $('#position').append(positionName+ '-' + number+ ', ');

    });
    $('#location').html(formId.find('.location').text());
    $('#salary_modal').html(formId.find('.salary').text());
    $("#modal-preview").modal("show");
});

$('.btn-success-preview').on("click", function() {

    var formValue = $('div#input_value');
    var data = {
        title: formValue.find('#title').val(),
        position: data,
        request_id: requestId,
        expired: formValue.find('#deadline').val(),
        place: formValue.find('#location').val(),
        salary: formValue.find('#salary').val(),
        description: formValue.find('#description').val(),
        benefits: formValue.find('#benefits').val(),
        qualifications: formValue.find('#job_qualifi').val(),
        _token: _token,
        programs: formValue.find('#programs').val(),
        types: formValue.find('#types').val(),
        status_request: formValue.find('#status').val(),
        publish: true,
    };
    $('.teams-container .box-position').each(function () {
        var position = $(this).find('select.position-apply').val();
        var number = $(this).find('.number-resource').val();
        data['positions['+position+']'] = number;
    });
    $.ajax({
        type: 'POST',
        url: urlPostRequest,
        data: data,
        success: function (res) {
            bootbox.alert({
                message: res,
                className: 'modal-default'
            });
            $('.btn-preview').text('Republish');
        },
        error: function (res) {
            bootbox.alert({
                message: res.statusText,
                className: 'modal-default'
            });
        }
    });
    $('#modal-preview').modal("hide");
});

