var $projectPlanComments = $('#project-plan-comments'),
    $planCommentError = $projectPlanComments.find('#plan-comment-error'),
    $commentList = $projectPlanComments.find('.grid-data-query-table'),
    $btnAddComment = $projectPlanComments.find('#add-comment-feedback'),
    $modalWarning = $('#modal-warning-notification'),
    $modalSuccess = $modalWarning.next(),
    $btnUpload = $('#upload'),
    $inputFiles = $('#files'),
    $listInputFiles = $('#file-list'),
    $errorProjectFile = $('#project-file-error'),
    $fileContainer = $('.grid-data-file-list');
var membersOfProject = [],
    membersList = [];

$(document).ready(function () {
    $.ajax({
        url: urlGetMembers,
        type: 'POST',
        dataType: "json",
        data: {
            'projectId': projId,
            '_token': token,
        },
        success: function (response) {
            membersOfProject = response.data;
        },
    });

    $('#proj-plan-comment').mentiony({
        onDataRequest: function (mode, keyword, onDataRequestCompleteCallback) {
            var members = [],
                i = 0, member,
                length = membersOfProject.length,
                cloneMembers = JSON.parse(JSON.stringify(membersOfProject));
            for (; i < length; i++) {
                if (cloneMembers[i].name.toLowerCase().indexOf(keyword.toLowerCase()) > -1) {
                    member = cloneMembers[i];
                    member.name = htmlEntities(member.name);
                    member.info = htmlEntities(member.info);
                    member.email = htmlEntities(member.email);
                    members.push(member);
                }
            }
            // Call this to populate mention.
            onDataRequestCompleteCallback.call(this, members);
        }
    });
});

function saveComment() {
    // get mention array.
    $('.mentiony-link').each(function() {
        var member ={},
            that = $(this);
        member.id = that.attr("data-item-id");
        member.email = that.attr("href");
        member.name = that.text();
        membersList.push(member);
    });

    var comment = $('#proj-plan-comment').val();
    if (comment.trim() === '') {
        $planCommentError.show();
        return false;
    }
    $planCommentError.hide();
    $.ajax({
        type: 'POST',
        url: urlSaveComment,
        dataType: 'json',
        data: {
            'membersList': membersList,
            'comment' : comment,
            'projectId' : projId,
            '_token': token,
            'limit' : $projectPlanComments.find(":selected").text()
        },
        success: function (response) {
            if (response.success) {
                if (response.data !== '') {
                    $commentList.html(response.data);
                    $('#proj-plan-comment').val('');
                    $btnAddComment.prop('disabled', false);
                }
            }
            if (response.error) {
                $modalWarning.find('.text-default').text(response.message);
                $modalWarning.modal('show');
            }
            membersList = [];
            $('.mentiony-content').html('');
        },
        error: function(response) {
            if (response.error) {
                $modalWarning.find('.text-default').text(response.message);
                $modalWarning.modal('show');
            }
            membersList = [];
            $('#proj-plan-comment').val('');
            $btnAddComment.prop('disabled', false);
            $('.mentiony-content').html('');
        }
    });
}

//get file array
var tempFiles = [];
var formData = new FormData();
var totalSize = 0;
$inputFiles.change(function (e) {
    e.preventDefault();
    var el_this = $(this);
    var files = el_this[0].files;
    for (var i = 0; i < files.length; i++) {
        $errorProjectFile.hide();
        if (files[i].size > maxFileSize * 1024) {
            showModalError(maxFileSizeMsg + ' ('+ files[i].name +' - ' + Math.floor(files[i].size / 1024) + ' KB)' );
            return false;
        } else {
            totalSize += files[i].size;
            if (totalSize > maxFileSize * 1024) {
                showModalError(maxTotalFileSizeMsg);
                totalSize -= files[i].size;
                return false;
            }

            $listInputFiles.append('<p>' + files[i].name + '</p>');
            formData.append('files[]', files[i]);
            tempFiles.push(files[i]);
        }
    }
});

$btnUpload.click(function () {
    if (tempFiles.length === 0) {
        $errorProjectFile.show();
        return false;
    }
    $errorProjectFile.hide();
    $btnUpload.prop('disabled', true);
    formData.append("_token", token);
    formData.append('projectId', projId);
    $.ajax({
        url: urlUpload,
        type: 'POST',
        dataType: 'json',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.success) {
                response.data.forEach(function(value) {
                    var path = value.file_url;
                    var createdAt = value.created_at;
                    var realPath = urlDownload.replace(':file', path) + '?projectId=' + projId;
                    var newResource = '<div class="item">'
                        + '<p>'
                        + '<span>' + value.file_name +  " ( created at: " +  createdAt + ")" + '</span>'
                        + '<a class="margin-left-10" title="' + titleDownload + '" ' + 'href="'+realPath+'" >'
                        + '<span style="font-size: 20px"><i class="fa fa-download"></i></span>'
                        + '</a>'
                        + '<span class="btn-delete-file" data-remote-url="' + (urlDeleteFile + path) + '">'
                        + '<i class="fa fa-trash"></i>'
                        + '</span>'
                        + '</p>'
                        + '</div>';
                    $fileContainer.children('p').remove();
                    $fileContainer.prepend(newResource);
                });
                $modalSuccess.find('.text-default').text(response.message);
                $modalSuccess.modal('show');
            } else {
                $modalWarning.find('.text-default').text(response.message);
                $modalWarning.modal('show');
            }
            formData.delete('files[]');
            tempFiles = [];
            $inputFiles.val('');
            $btnUpload.prop('disabled', false);
            $listInputFiles.html('');
            totalSize = 0;
        },
        error: function(response) {
            formData.delete('files[]');
            tempFiles = [];
            $inputFiles.val('');
            $btnUpload.prop('disabled', false);
            $listInputFiles.html('');
            totalSize = 0;
            if (response.error) {
                $modalWarning.find('.text-default').text(response.message);
                $modalWarning.modal('show');
            }
        }
    });
});

$fileContainer.on('click', '.btn-delete-file', function () {
    if (!confirm(txtConfirmDeleteFile)) {
        return false;
    }
    var that = $(this),
        urlDeleteFile = that.attr('data-remote-url');
    $.ajax({
        method: 'delete',
        url: urlDeleteFile,
        dataType: 'json',
        data: {
            'projectId' : projId,
            '_token': token,
        },
        success: function (response) {
            if (response.success) {
                $modalSuccess.find('.text-default').text(response.message);
                $modalSuccess.modal('show');
                that.closest('.item').remove();
                $fileContainer.children('.item').length || $fileContainer.html('<p style="font-size: 18px">' + txtNoFiles + '</p>');
            }
            if (response.error) {
                $modalWarning.find('.text-default').text(response.message);
                $modalWarning.modal('show');
            }
        },
        error: function(response) {
            $modalWarning.find('.text-default').text(response.message);
            $modalWarning.modal('show');
        }
    });
});

//show message error
function showModalError(message) {
    $modalError.find('.text-default').html(message || 'Error occurred, please try again later!');
    $modalError.modal('show');
}
