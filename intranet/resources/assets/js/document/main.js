(function ($) {

    if (typeof docParams == 'undefined') {
        docParams = {};
    }
    var commentHash = window.location.hash;
    if (commentHash) {
        $('html, body').animate({
            scrollTop: $('#comment_box').offset().top
        }, 500);
    }
    var commentPage = window.location.search;
    if (commentPage.indexOf('comment_page') > -1) {
        $('html, body').animate({
            scrollTop: $('#comment_box').offset().top
        }, 500);
    }
    if (commentPage.indexOf('history_page') > -1) {
        $('html, body').animate({
            scrollTop: $('#box_history').offset().top
        }, 500);
    }

    $('body').on('change', 'input[type="file"]', function () {
        if ($(this).attr('id') == 'fileUpload') {
            return;
        }
        $(this).parents().find('.text-green').remove();
        var file = $(this)[0].files[0];
        if (getCurrentFileSize($(this)) > docParams.maxSize * 1024) {
            bootbox.alert({
                className: 'modal-danger',
                message: docParams.errorFileMaxSize + ' ('+ file.name +')',
                buttons: {
                    ok: {
                        label: confirmYes,
                    },
                },
            });
            $(this).val('');
            return false;
        }
        $(this).after('<span class="text-green">('+ (file.size / 1024).toFixed(2) +' KB)</span>');
    });

    function getCurrentFileSize(input){
        var size = 0;
        input.closest('form').find('input[type="file"]').each(function () {
            var files = $(this)[0].files;
            if (files.length > 0) {
                for (var i = 0; i < files.length; i++) {
                    size += files[i].size;
                }
            }
        });
        return size;
    }

    $('#btn_add_input_file').click(function (e) {
        e.preventDefault();
        var listInputField = $(this).closest('.upload-wrapper').find('.list-input-fields');
        listInputField.append('<div class="attach-file-item">' +
                                    '<button class="btn btn-danger btn-sm btn-del-file" type="button">' +
                                        '<i class="fa fa-close"></i>' +
                                    '</button>' +
                                    '<input type="file" name="'+ $(this).data('name') +'">' +
                                '</div>');
    });

    $('#btn_save_doc').click(function () {
        $(this).closest('form').find('input[name="status"]').val('');
    });

    $('.btn-submit').click(function () {
        var btn = $(this);
        var form = btn.closest('form');
        if (!form.valid()) {
            setTimeout(function () {
                btn.prop('disabled', false);
            }, 100);
            return;
        }
        var elStatus = form.find('input[name="status"]');
        bootbox.confirm({
            className: 'modal-warning',
            message: btn.data('noti'),
            buttons: {
                cancel: {
                    label: confirmNo,
                },
                confirm:  {
                    label: confirmYes,
                },
            },
            callback: function (result) {
               if (result) {
                   elStatus.val(btn.data('status'));
                   form.submit();
               } else {
                   btn.prop('disabled', false);
               }
            }
        });

        return false;
    });

    if ($('.multiselect2').length > 0) {
        $('.multiselect2').multiselect({
            includeSelectAllOption: false,
            numberDisplayed: 2,
            nonSelectedText: 'None selected',
            allSelectedText: 'Selected all',
            nSelectedText: ' Selected',
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            buttonText: function (options, select) {
                if (options.length === 0) {
                    return 'None selected';
                } else if (options.length > 2) {
                    return options.length + ' selected';
                } else {
                    var labels = [];
                    options.each(function() {
                        labels.push($(this).text().trim());
                    });
                    return labels.join(', ') + '';
                }
            }
        });
    }

    $('body').on('click', '.btn-del-comment', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        if (!url) {
            return;
        }
        if ($(this).is(':disabled')) {
            return;
        }
        var _this = $(this);
        bootbox.confirm({
            className: 'modal-danger',
            message: textConfirmDelete,
            buttons: {
                cancel: {
                    label: confirmNo,
                },
                confirm:  {
                    label: confirmYes,
                },
            },
            callback: function (result) {
                if (result) {
                    _this.prop('disabled', true);
                    var commentItem = _this.closest('.comment-item');
                    commentItem.addClass('deleting');

                    $.ajax({
                        type: 'DELETE',
                        url: url,
                        data: {
                            _token: siteConfigGlobal.token,
                        },
                        success: function () {
                            commentItem.remove();
                            if (typeof docParams != 'undefined') {
                                window.location.href = docParams.editUrl + '?comment_page=1';
                            }
                        },
                        error: function (error) {
                            var mess = error.responseJSON.message;
                            if (typeof mess == 'undefined') {
                                mess = 'Error!';
                            }
                            bootbox.alert({
                                className: 'modal-danger',
                                message: mess,
                                buttons: {
                                    ok: {
                                        label: confirmYes,
                                    },
                                },
                            });
                        },
                        complete: function () {
                            _this.prop('disabled', false);
                            commentItem.removeClass('deleting');
                        }
                    });
                }
            }
        });
    });

    $('body').on('click', '.btn-del-file', function (e) {
        e.preventDefault();
        $(this).closest('.attach-file-item').remove();
    });

    if ($('#doc_publish_form').length > 0) {
        $('#doc_publish_form').validate({
            ignore: [],
            rules: {
                'team_ids[]': {
                    required: function () {
                        return !$('#account_ids').val()
                    },
                },
                email_subject: {
                    required: true,
                },
            },
            errorPlacement: function (error, element) {
                error.appendTo(element.closest('.form-group'));
            },
        });
    }

    $('body').on('change', '#select_reviewer', function (e) {
        e.preventDefault();
        var _this = $(this);
        var url = docParams.urlAddAssignee;
        var type = _this.attr('data-type');
        if (typeof url == 'undefined' || typeof type == 'undefined') {
            return;
        }
        var employeeId = _this.val();
        if (!employeeId && type != docParams.typeEditor) {
            return;
        }

        _this.prop('disabled', true);
        var loading = _this.closest('tr').find('.add-loading');
        loading.removeClass('hidden');
        generateReviewers(documentId, [], [employeeId], function () {
            loading.addClass('hidden');
            _this.prop('disabled', false);
            _this.val('').trigger('change');
        });
    });

    function initRemoteSelectAssignee() {
        $('.select-remote-assignee').each(function () {
            var dom = $(this);
            dom.select2({
                minimumInputLength: 2,
                ajax: {
                    url: dom.data('remote-url'),
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        var excludeIds = [];
                        dom.closest('.table-list-assignee').find('tbody tr').each(function () {
                            var value = $(this).attr('data-emp');
                            if (value) {
                                excludeIds.push(value);
                            }
                        });
                        return {
                            q: params.term,
                            page: params.page,
                            'exclude_ids[]': excludeIds,
                            type: dom.data('type'),
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 20) < data.total_count
                            },
                        };
                    },
                    cache: true,
                },
            });
        });
    }

    initRemoteSelectAssignee();

    $('body').on('click', '.btn-add-assignee', function (e) {
        e.preventDefault();
        var _this = $(this);
        var url = _this.attr('data-url');
        var type = _this.attr('data-type');
        if (typeof url == 'undefined' || typeof type == 'undefined') {
            return;
        }
        var select = _this.closest('.tr-add-form').find('select');
        var employeeId = select.val();
        if (!employeeId && type != docParams.typeEditor) {
            bootbox.alert({
                className: 'modal-warning',
                message: textRequestAccount,
                buttons: {
                    ok: {
                        label: confirmYes,
                    },
                },
            });
            return;
        }
        var tr = _this.closest('tr');
        var trOther = $('.tr-toggle[data-toggle="'+ tr.data('toggle') +'"]').not(tr[0]);
        var loading = tr.find('.add-loading');
        loading.removeClass('hidden');

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                type: type,
                employee_id: employeeId,
                _token: siteConfigGlobal.token,
            },
            success: function (data) {
                trOther.find('td:first').text(data.displayName);
                if (data.itemHistory) {
                    $('#list_histories').prepend(data.itemHistory);
                }
                tr.find('.btn-toggle').click();

                if (type == docParams.typePublisher) {
                    window.location.reload();
                }
            },
            error: function (error) {
                bootbox.alert({
                    className: 'modal-danger',
                    message: error.responseJSON,
                });
            },
            complete: function () {
                loading.addClass('hidden');
            }
        });
    });

    $('body').on('click', '.btn-del-assignee', function (e) {
        e.preventDefault();
        var _this = $(this);
        if ($('#tbl_reviewers tbody tr').length < 2) {
            bootbox.alert({
                className: 'modal-danger',
                message: textMustHasPerson,
            });
            return;
        }
        var type = _this.attr('data-type');
        if (typeof type == 'undefined') {
            return;
        }

        var trItem = _this.closest('tr');
        bootbox.confirm({
            className: 'modal-danger',
            message: textConfirmDelete,
            buttons: {
                cancel: {
                    label: confirmNo,
                },
                confirm:  {
                    label: confirmYes,
                },
            },
            callback: function (result) {
                if (result) {
                    trItem.remove();
                }
            }
        });
    });

    function postToUrl(url, params) {
        var form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', url);

        for (var key in params) {
            var hiddenField = document.createElement('input');
            hiddenField.setAttribute('type', 'hidden');
            hiddenField.setAttribute('name', key);
            hiddenField.setAttribute('value', params[key]);
            form.appendChild(hiddenField);
        }

        document.body.appendChild(form);
        form.submit();
        form.remove();
    }

    $('.checkbox-all').click(function () {
        var checkboxGroup = $(this).closest('.form-group').find('.checkbox-group');
        checkboxGroup.find('input[type="checkbox"]').prop('checked', $(this).is(':checked'));
    });
    $('.checkbox-group input[type="checkbox"]').click(function () {
        var checkAll = $(this).closest('.form-group').find('.checkbox-all');
        var checkboxGroup = $(this).closest('.checkbox-group');
        checkAll.prop('checked', checkboxGroup.find('input[type="checkbox"]').length === checkboxGroup.find('input[type="checkbox"]:checked'));
    });

    /*
     * add new type
     */
    $('.btn-add-type').click(function (e) {
        e.preventDefault();
        var _this = $(this);
        if (_this.is(':disabled')) {
            return;
        }
        var form = _this.closest('.form-add-type');
        var typeName = form.find('.type-name').val();
        var typeParent = form.find('.type-parent').val();
        if (!typeName) {
            bootbox.alert({
                className: 'modal-danger',
                message: textAlertInputData,
                buttons: {
                    ok: {
                        label: confirmYes,
                    },
                },
            });
            return false;
        }
        _this.prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: form.data('url'),
            data: {
                _token: siteConfigGlobal.token,
                name: typeName,
                parent_id: typeParent,
            },
            success: function (data) {
                var typeCheckbox = $('#type_checkbox ul');
                var depth = 0;
                if (typeParent) {
                    depth = parseInt(typeCheckbox.find('li[data-id="'+ typeParent +'"]').attr('data-depth')) + 1;
                }
                var typeHtml = '<li data-id="'+ data.id +'" data-depth="'+ depth +'">'
                            + ('&nbsp;').repeat(depth * 8)
                            + '<label><input type="checkbox" checked name="type_ids[]" value="'+ data.id +'"> '
                            + $('<div>' + data.name + '</div>').text()
                            + '</label></li>';
                var typeOption = '<option value="'+ data.id +'">'+ ('--').repeat(depth) + ' ' + $('<div>' + data.name + '</div>').text() +'</option>';
                if (typeParent) {
                    typeCheckbox.find('li[data-id="'+ typeParent +'"]').after(typeHtml);
                    form.find('.type-parent option[value="'+ typeParent +'"]').after(typeOption);
                } else {
                    typeCheckbox.append(typeHtml);
                    form.find('.type-parent').append(typeOption);
                }
                form.find('.type-name').val('');
                form.find('.type-parent').val('').trigger('change');
            },
            error: function (error) {
                bootbox.alert({
                    className: 'modal-danger',
                    message: error.responseJSON,
                    buttons: {
                        ok: {
                            label: confirmYes,
                        },
                    },
                });
            },
            complete: function () {
                _this.prop('disabled', false);
            },
        });
    });

    $('.el-short-content').each(function () {
        var showChars = $(this).attr('data-showchars') || 120;
        $(this).shortedContent({
            showChars: showChars,
        })
    });

    $('.tr-toggle .btn-toggle').click(function (e) {
        e.preventDefault();
        var tr = $(this).closest('tr');
        var other = $('.tr-toggle[data-toggle="'+ tr.data('toggle') +'"]').not(tr[0]);
        tr.addClass('hidden');
        other.removeClass('hidden');
        var select2 = tr.find('.select2-container');
        if (select2.length < 1) {
            select2 = other.find('.select2-container');
        }
        select2.width(240);
    });

    if ($('.doc-form').length > 0) {
        jQuery.extend(jQuery.validator.messages, {
            required: requiredText,
        });
        $('.doc-form').validate({
            ignore: '',
            rules: {
                code: {
                    required: true,
                    maxlength: 255,
                    remote: {
                        url: docParams.urlCheckExistCode,
                        type: 'POST',
                        data: {
                            _token: siteConfigGlobal.token,
                            id: function () {
                                return $('input[name="id"]').length > 0 ? $('input[name="id"]').val() : null;
                            },
                            code: function () {
                                return $('input[name="code"]').val();
                            },
                        },
                    },
                },
                file: {
                    required: function () {
                        var elFileId = $('input[name="file_id"]');
                        var elFileLink = $('input[name="file_link"]');
                        if ($('#type_document').is(':checked') === true) {
                            if ((elFileId.length > 0 && elFileId.val()) ||
                                    (elFileLink.length > 0 && elFileLink.val())) {
                                return false;
                            }
                            return true;
                        }
                    },
                },
                id_magazine: {
                    required: function () {
                        if ($('#type_magazine').is(':checked') === true) {
                            return true;
                        }
                    },
                },
                'team_ids[]': {
                    required: true,
                },
                'assignees[3]': {
                    required: true,
                },
            },
            messages: {
                code: {
                    remote: docParams.errorCodeExists,
                },
            },
            errorPlacement: function (error, element) {
                var name = element.attr('name');
                var elAppend = element;
                if (name === 'file' || name === 'id_magazine') {
                    elAppend = $(element).closest('.file-group').find('label:first');
                } else {
                    elAppend = $(element).closest('.form-group').find('label:first');
                }
                error.insertAfter(elAppend);
            },
        });
    }

    $('.list-types-bar li b').click(function (e) {
        e.preventDefault();
        var itemLi = $(this).closest('li');
        if (itemLi.hasClass('open')) {
            itemLi.find('>ul.list-child').slideUp();
            itemLi.removeClass('open');
            $(this).removeClass('fa-minus').addClass('fa-plus');
        } else {
            itemLi.find('>ul.list-child').slideDown();
            itemLi.addClass('open');
            $(this).addClass('fa-minus').removeClass('fa-plus');
        }
    });

    $('.list-types-bar li.active').each(function () {
        var toggleIcon = $(this).find('>div>b');
        if (toggleIcon.length > 0) {
            toggleIcon.click();
        }
        $(this).parents('.list-child').each(function () {
            $(this).parent('li').find('>div>b').click();
        });
    });

    $('.radio-show-box').change(function () {
        $('.radio-show-box-content').slideUp();
        $('#radio_show_box_' + $(this).val()).slideDown();
    });

    $('.radio-show-box:checked').trigger('change');

    $('#checkbox_send_mail').change(function () {
        var mailBox = $(this).closest('.form-group').next('.send-mail-box');
        if ($(this).is(':checked')) {
            mailBox.slideDown();
        } else {
            mailBox.slideUp();
        }
    });

    $('#doc_publish_btn').click(function () {
        var message = $(this).data('noti');
        var form = $(this).closest('form');
        if ($('#checkbox_send_mail').is(':checked')) {
            bootbox.confirm({
                className: 'modal-warning',
                message: message,
                buttons: {
                    cancel: {
                        label: confirmNo,
                    },
                    confirm:  {
                        label: confirmYes,
                    },
                },
                callback: function (result) {
                    if (result) {
                        form[0].submit();
                    }
                }
            });
        } else {
            form[0].submit();
        }
        return false;
    });

    var xhr = null;

    $('body').on('click', '.btn-suggest-reviewer', function (e) {
        e.preventDefault();
        var btn = $(this);

        var teamIds = [];
        $('.doc-team-group input[type="checkbox"]:checked').each(function () {
            teamIds.push($(this).val());
        });
        if (teamIds.length < 1) {
            return;
        }
        var loading = btn.find('i');
        loading.removeClass('hidden');
        btn.prop('disabled', true);
        generateReviewers(documentId, teamIds, [], function () {
            loading.addClass('hidden');
            btn.prop('disabled', false);
        });
    });

    function generateReviewers(docId, teamIds, employeeIds, done) {
        if (typeof teamIds == 'undefined') {
            teamIds = [];
        }
        if (typeof employeeIds == 'undefined') {
            employeeIds = [];
        }

        xhr = $.ajax({
            url: docParams.urlGetSuggestReviewers,
            type: 'GET',
            data: {
                doc_id: docId,
                'team_ids[]': teamIds,
                'employee_ids[]': employeeIds,
            },
            success: function (response) {
                var container = $('#tbl_reviewers tbody');
                container.find('.item-none').remove();
                $('<table>' + response + '</table>').find('tr').each(function () {
                    var empId = $(this).attr('data-emp');
                    if (container.find('tr[data-emp="'+ empId +'"]').length < 1) {
                        container.append($(this)[0].outerHTML);
                    }
                });
            },
            error: function (error) {
                bootbox.alert({
                    className: 'modal-warning',
                    message: error.responseJSON
                });
            },
            complete: function () {
                if (typeof done != 'undefined') {
                    done();
                }
            },
        });
    }

})(jQuery);
