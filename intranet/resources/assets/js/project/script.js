jQuery(document).ready(function ($) {
    function tooltip(className) {
        $('.' + className).each(function () {
            id = $(this).attr('data-id');
            text = $('.' + className + '-' + id).text();
            if (text.trim()) {
                $(this).qtip({
                    content: {
                        text: $('.' + className + '-' + id).html()
                    },
                    position: {
                        my: 'top left',
                        at: 'center',
                        viewport: $(window)
                    },
                    hide: {
                        fixed: true,
                        delay: 100
                    },
                    style: {
                        classes: 'custom-tooltip'
                    }
                });
            }
        });
    }

    function taskTooltip(weekSlug, qtipId, inputTypeId) {
        if (!$('.task-tooltip').length) {
            return false;
        }
        if (typeof globalPassModule === 'undefined' ||
                typeof globalPassModule.urlTaskTitle === 'undefined' ||
                !globalPassModule.urlTaskTitle) {
            return false;
        }
        if (typeof weekSlug == 'undefined') {
            weekSlug = globalPassModule.weekSlug;
        }
        if (typeof qtipId == 'undefined') {
            qtipId = null;
        }
        var urlTaskTitles = globalPassModule.urlTaskTitle,
                textTooltipTask = {},
                optionTooltipTask = {
                    position: {
                        my: 'top center',
                        at: 'center',
                        viewport: $(window),
                        adjust: {
                            y: 10
                        }
                    },
                    hide: {
                        fixed: true,
                        delay: 100
                    },
                    style: {
                        classes: 'custom-tooltip'
                    },
                    content: {
                        text: null
                    }
                },
                idsTaskTooltip = {},
                optionTooltipTaskItem = {};
        //check if click btn load note
        if (qtipId) {
            var projId = $('.task-tooltip[data-hasqtip="' + qtipId + '"]').data('id');
            idsTaskTooltip[projId] = projId;
        } else {
            $('.task-tooltip').each(function () {
                var id = $(this).data('id');
                if (id) {
                    idsTaskTooltip[id] = id;
                }
            });
        }
        if (!Object.keys(idsTaskTooltip).length) {
            return true;
        }
        $.ajax({
            url: urlTaskTitles,
            type: 'post',
            dataType: 'json',
            data: {
                '_token': siteConfigGlobal.token,
                'ids': idsTaskTooltip,
                weekSlug: weekSlug,
                noBaseline: globalPassModule.noBaseline
            },
            success: function (data) {
                if (!data || !data.success || !data.data) {
                    if (qtipId) {
                        var qtipLoadmore = $('#qtip-' + qtipId + '-content .btn-load-note');
                        qtipLoadmore.remove();
                    }
                    return true;
                }
                var prevWeeks = data.prev_weeks || null;
                $.each(data.data, function (projectId, titleTypes) {
                    if (!titleTypes || !Object.keys(titleTypes).length) {
                        return true;
                    }
                    //check if not click btn load note
                    if (!qtipId) {
                        $.each(titleTypes, function (typeId, titleType) {
                            titleType = titleType.trim();
                            if (!titleType) {
                                titleType = '<p>' + globalText.noComment + '</p>';
                            }
                            titleType = '<p><b>' + data.curr_week + '</b></p>' + titleType;
                            if (prevWeeks && prevWeeks[projectId]) {
                                titleType += '<a href="#" class="btn-load-note" data-week="' + prevWeeks[projectId] + '" data-type="' + typeId + '">' + globalText.loadMore + ' <i class="fa fa-spin fa-refresh hidden"></i></a>';
                            }
                            optionTooltipTaskItem = $.extend(optionTooltipTask, {
                                content: {
                                    text: titleType
                                }
                            });
                            if ($('.task-tooltip[data-id=' + projectId + '][data-type=' + typeId + ']').length) {
                                $('.task-tooltip[data-id=' + projectId + '][data-type=' + typeId + ']').qtip(optionTooltipTaskItem);
                            }
                        });
                    } else {
                        //when click btn load note
                        var titleType = '<p><b>' + data.curr_week + '</b></p>';
                        var resTitleType = titleTypes[inputTypeId].trim();
                        if (!resTitleType) {
                            titleType += '<p>' + globalText.noComment + '</p>';
                        } else {
                            titleType += resTitleType;
                        }
                        var qtipContent = $('#qtip-' + qtipId + '-content');
                        var qtipLoadmore = qtipContent.find('.btn-load-note');
                        qtipLoadmore.find('i').addClass('hidden');
                        qtipContent.append(titleType);
                        if (prevWeeks && prevWeeks[projectId]) {
                            qtipLoadmore.attr('data-week', prevWeeks[projectId]);
                            qtipLoadmore.detach().appendTo(qtipContent);
                        } else {
                            qtipLoadmore.remove();
                        }
                    }
                });
            },
            complete: function () {
                if (qtipId) {
                    var qtipContent = $('#qtip-' + qtipId + '-content');
                    var qtipLoadmore = qtipContent.find('.btn-load-note');
                    qtipLoadmore.find('i').addClass('hidden');
                }
            }
        });
    }

    //load more note
    $('body').on('click', '.btn-load-note', function (e) {
        e.preventDefault();
        var iconLoad = $(this).find('i');
        iconLoad.removeClass('hidden');
        var weekSlug = $(this).attr('data-week');
        var qtipId = $(this).closest('.qtip').attr('data-qtip-id');
        var inputTypeId = $(this).attr('data-type');
        taskTooltip(weekSlug, qtipId, inputTypeId);
    });

    function raiseContentTooltip() {
        var optionTooltipTask = {
            position: {
                my: 'top center',
                at: 'center',
                viewport: $(window),
                adjust: {
                    y: 10,
                },
            },
            hide: {
                fixed: true,
                delay: 100,
            },
            style: {
                classes: 'custom-tooltip',
            },
            content: {
                text: null,
            },
        };
        $('.raise-content-tooltip').each(function () {
            var _thisTooltip = $(this), optionTooltipItem;
            var raiseNote = _thisTooltip.data('raise-note');
            raiseNote = String(raiseNote).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/\n/g, '<br />');
            if (!raiseNote) {
                return true;
            }
            optionTooltipItem = $.extend(optionTooltipTask, {
                content: {
                    text: raiseNote,
                },
            });
            _thisTooltip.qtip(optionTooltipItem);
        });
    }

    function colTooltip() {
        if (!$('.col-hover-tooltip').length) {
            return false;
        }
        var optionTooltipTask = {
            position: {
                my: 'top center',
                at: 'center',
                viewport: $(window),
                adjust: {
                    y: 10,
                },
            },
            hide: {
                fixed: true,
                delay: 100,
            },
            style: {
                classes: 'custom-tooltip',
            },
            content: {
                text: null,
            },
        };
        $('.col-hover-tooltip').each(function () {
            var _thisTooltip = $(this), optionTooltipItem;
            if (!_thisTooltip.find('.tooltip-content').length) {
                return true;
            }
            text = _thisTooltip.find('.tooltip-content').html().trim();
            if (!text) {
                return true;
            }
            optionTooltipItem = $.extend(optionTooltipTask, {
                content: {
                    text: text,
                },
            });
            _thisTooltip.qtip(optionTooltipItem);
        });
    }

    function taskModal() {
        if (!$('.task-tooltip').length || !$('.task-list-modal').length) {
            return;
        }
        var taskModalHtml = $('.task-list-modal'),
                flagTaskModal = {};
        $('.task-tooltip').click(function (event) {
            event.preventDefault();
            var taskModalId = $(this).data('id'),
                    taskModalType = $(this).data('type');
            if (!taskModalType || !taskModalId) {
                return true;
            }
            if ($('.task-list-modal[data-id=' + taskModalId + '][data-type=' +
                    taskModalType + ']').length) {
                $('.task-list-modal[data-id=' + taskModalId + '][data-type=' +
                        taskModalType + ']').modal('show');
                return true;
            }
            var taskModalItem = taskModalHtml.clone();
            taskModalItem.attr('data-id', taskModalId);
            taskModalItem.attr('data-type', taskModalType);
            var urlAjax = taskModalItem.find('.grid-data-query').data('url'),
                    urlCreateTask = taskModalItem.find('.btn-add-task').data('url');
            urlAjax = urlAjax.replace(/\/0\//, '/' + taskModalId + '/');
            urlAjax += '?type=' + taskModalType;
            urlCreateTask = urlCreateTask.replace(/\/0\//, '/' + taskModalId + '/');
            urlCreateTask += '?type=' + taskModalType;
            taskModalItem.find('.grid-data-query').attr('data-url', urlAjax);
            if (typeof flagTaskModal[taskModalId] != 'undefined' &&
                    typeof flagTaskModal[taskModalId][taskModalType] != 'undefined' &&
                    flagTaskModal[taskModalId][taskModalType] == true
                    ) {
                return true;
            } else {
                flagTaskModal[taskModalId] = {};
                flagTaskModal[taskModalId][taskModalType] = true;
                $.ajax({
                    url: urlAjax,
                    type: 'get',
                    dataType: 'json',
                    success: function (data) {
                        if (typeof data.html != 'undefined') {
                            taskModalItem.find('.grid-data-query-table').html(data.html);
                            if (typeof data.is_open != 'undefined' && data.is_open) {
                                taskModalItem.find('.btn-add-task').attr('href', urlCreateTask);
                            } else {
                                taskModalItem.find('.btn-add-task').remove();
                            }
                            var titleModal = taskModalItem.find('.task-list-title .task-list-title-text[data-type=' + taskModalType + ']');
                            if (titleModal.length) {
                                taskModalItem.find('.task-list-title .task-list-title-text').addClass('hidden');
                                titleModal.removeClass('hidden');
                            }
                            $('.task-list-popup-wraper').append(taskModalItem);
                            $('.task-list-modal[data-id=' + taskModalId + '][data-type=' +
                                    taskModalType + ']').modal('show');
                        }
                        taskModalItem.find('.fa-refresh').addClass('hidden');
                    }
                });
            }
        });
    }
    /**
     * init dashboard by ajax
     *  show task, dom, color, point
     */
    function dashboardInit()
    {
        if (typeof globalPassModule == 'undefined' ||
                typeof globalPassModule.urlInitPoint == 'undefined' ||
                !globalPassModule.urlInitPoint
                ) {
            return;
        }
        $.ajax({
            url: globalPassModule.urlInitPoint,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (typeof data.success == 'undefined' || data.success != 1) {
                    return;
                }
                if (typeof data.html_task !== 'undefined' && data.html_task) {
                    $.each(data.html_task, function (i, k) {
                        if ($(i).length) {
                            $(i).find('.grid-data-query-table').html(k);
                            $(i).find('.fa-refresh').addClass('hidden');
                        }
                    });
                }
                if (typeof data.html !== 'undefined' && data.html) {
                    $.each(data.html, function (i, k) {
                        if ($(i).length) {
                            if (k === null) {
                                $(i).html('&nbsp;');
                            } else {
                                $(i).text(k);
                            }
                        }
                    });
                }
                if (typeof data.input !== 'undefined' && data.input) {
                    $.each(data.input, function (i, k) {
                        if ($('input.pp-input[name=' + i + ']').length) {
                            $('input.pp-input[name=' + i + ']').val(k).attr('data-value', k);
                        }
                    });
                }
                if (typeof data.color !== 'undefined' && data.color) {
                    $.each(data.color, function (i, k) {
                        if ($(i + ' img').length) {
                            $(i + ' img').attr('src', k);
                        }
                    });
                }
                if (typeof data.dom !== 'undefined' &&
                        typeof data.dom.hover !== 'undefined' &&
                        data.dom.hover
                        ) {
                    $.each(data.dom.hover, function (i, k) {
                        if ($(i).length) {
                            $(i).attr('title', RKfuncion.general.parseHtml(k));
                            $(i).tooltip();
                        }
                    });
                }
            }
        });
    }
    /**
     * init dashboard by ajax
     *  show task, dom, color, point
     */
    function workOrderInit()
    {
        if (typeof globalPassModule == 'undefined' ||
                typeof globalPassModule.teamProject == 'undefined' ||
                !globalPassModule.teamProject
                ) {
            return;
        }
        var dom = $('.content-wrapper .content-header h1');
        if (dom.length) {
            dom.attr('title', RKfuncion.general.parseHtml(globalPassModule.teamProject));
            dom.tooltip();
        }
    }
    /*function taskListAjaxInit()
     {
     var domWrapperParent = $('.grid-data-query.task-list-ajax');
     if (!domWrapperParent.length) {
     return;
     }
     domWrapperParent.each (function() {
     var domWrapperTask = $(this),
     urlSubmit = domWrapperTask.data('url');
     if (!urlSubmit) {
     return;
     }
     $.ajax({
     url: urlSubmit,
     type: 'GET',
     dataType: 'json',
     success: function(data) {
     if (typeof data.html != 'undefined') {
     domWrapperTask.find('.grid-data-query-table').html(data.html);
     }
     domWrapperTask.find('.fa-refresh').addClass('hidden');
     }
     });
     });
     }*/

    $(window).load(function () {
        taskTooltip();
        colTooltip();
        raiseContentTooltip();
        dashboardInit();
        workOrderInit();
    });
    taskModal();
    $(".view-highlight").click(function (event) {
        id = $(this).attr('data-id');
        $('.content-highlight').html($('.highlight-hidden-' + id).html());
        $("#show-highlight").modal('show');
    });

    /**
     * export dashboard
     */
    $('.export').click(function () {
        var url = $(this).data('url');
        var checkedItem = $('.export-project-dashboard:checked');
        if (checkedItem.length) {
            var param = '';
            $(checkedItem).each(function () {
                param += parseInt($(this).val()) + '-';
            });
            $('#export-member').append('<input type="hidden" name="projectIds" value="' + param + '">');
            $('#export-member').submit();
        }
    });
    $('.export-employees').click(function () {
        var url = $(this).data('url');
        var checkedItem = $('.export-project-dashboard:checked');
        if (checkedItem.length) {
            var param = '';
            $(checkedItem).each(function () {
                param += parseInt($(this).val()) + '-';
            });
            url = encodeURI(url + '/' + param);
            window.open(url);
        }
    });
    /**
     * export project
     */
    $('.export-project').click(function () {
        var checkedItem = $('.export-project-dashboard:checked');
        var checkAll = $('.checkAll:checked');
        if (checkedItem.length) {
            var param = '';
            $(checkedItem).each(function () {
                var index = param.indexOf($(this).val());
                if (index !== -1 && checkAll.length === 0) {
                    if (typeof param[index] !== 'undefined') {
                        delete param[index];
                    }
                } else {
                    param += parseInt($(this).val()) + '-';
                }
            });
            $('#export-project').append('<input type="hidden" name="projectIds" value="' + param + '">');
        } else {
            $('#export-project').append('<input type="hidden" name="projectIds" value="">');
        }
        $('#export-project').submit();
    });

    /***
     * Show modal content of Raise Project
     */
    $('.raise-submit').click(function () {
        var checkedItem = $('.export-project-dashboard:checked');
        var raiseNote = '';
        if (!checkedItem.length) {
            return;
        } else {
            $(checkedItem).each(function () {
                raiseNote += $(this).next().attr('data-raise-note') ? $(this).next().attr('data-raise-note').trim() : '' ;
                if (checkedItem.length > 1 && raiseNote.trim()) {
                    $('.warning-action').attr('data-noti', globalMsg.raiseTwoProject);
                    $('.warning-action').trigger('click');
                } else {
                    $('#modal-raise-note textarea[name=raise-note]').val(raiseNote.trim());
                    $('#title-error').addClass('hidden');
                    $('#modal-raise-note').modal('show');
                }
            });
        }
    });

    $('.export-employees').click(function () {
        var checkedItem = $('.export-project-dashboard:checked');
        if (!checkedItem.length) {
            return;
        } else {
            $(checkedItem).each(function () {
                if (checkedItem.length > 1) {
                    $('.warning-action').attr('data-noti', globalMsg.raiseTwoProject);
                    $('.warning-action').trigger('click');
                } else {
                    $('#title-error').addClass('hidden');
                    $('#modal-raise-note').modal('show');
                }
            });
        }
    });

    /***
     * Submit Raise after set note of rasie
     * @param project_id
     * @return
     */
    $('.submit-raise-note').click(function () {
        var checkedItem = $('.export-project-dashboard:checked'),
                btnSubmit = $(this);
        if (!checkedItem.length) {
            return;
        }
        var param = {}, value, data = {},
            url = $('.raise-submit').attr('data-url'),
            raiseNote = $('#modal-raise-note textarea[name=raise-note]').val(),
            loadingRefresh = btnSubmit.find('.submit-ajax-refresh-btn');
        if (!raiseNote.trim()) {
            $('#title-error').removeClass('hidden');
            return;
        }
        $(checkedItem).each(function () {
            value = parseInt($(this).val());
            param[value] = value;
        });
        loadingRefresh.removeClass('hidden');
        data = {
            ids: param,
            raiseNote: raiseNote,
            _token: siteConfigGlobal.token
        };

        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (data) {
                if (typeof data.success !== 'undefined' && data.success == 1) {
                    if (typeof data.refresh !== 'undefined' && data.refresh) {
                        window.location.reload();
                    }
                } else {
                    if (typeof data.message !== 'undefined' && data.message) {
                        $('.warning-action').attr('data-noti', data.message);
                    }
                    $('.warning-action').trigger('click');
                }
                loadingRefresh.addClass('hidden');
                btnSubmit.removeAttr('disabled');
            },
            error: function () {
                loadingRefresh.addClass('hidden');
                btnSubmit.removeAttr('disabled');
                $('.warning-action').trigger('click');
            }
        });
    });

    $('.export-project-dashboard').click(function () {
        if ($('.export-project-dashboard:checked').length) {
            $('.export, .raise-submit').removeAttr('disabled');
            $('.export, .export-employees').removeAttr('disabled');
        } else {
            $('.export, .raise-submit').attr('disabled', 'true');
            $('.export, .export-employees').attr('disabled', 'true');
        }
    });

    $(document).on('click', '#modal-submit-report-warn-confirm .modal-footer .btn-ok', function () {
        $('#modal-submit-report-warn-confirm').modal('hide');
        $('#submit-report').click();
        $('.is-report').find('.submit-ajax-refresh-btn').removeClass('hidden');
    });

    /**
     * modal confirm submit report
     */
    $(document).on('click touchstart', '.is-report', function () {
        var listFields = {
            _token: token,
            cost_plan_effort_current: $('#cost input[name="cost_plan_effort_current"]').val(),
            cost_actual_effort: $('#cost input[name="cost_actual_effort"]').val(),
            qua_leakage_errors: $('#quality input[name="qua_leakage_errors"]').val(),
            qua_defect_errors: $('#quality input[name="qua_defect_errors"]').val(),
            tl_schedule: $('#timeliness input[name="tl_schedule"]').val(),
            css_css: $('#css input[name="css_css"]').val(),
        };
        $.ajax({
            url: urlCheckNoteReport,
            type: 'post',
            dataType: 'json',
            data: listFields,
            success: function (result) {
                if (result.success === 1) {
                    $('#modal-submit-report-warn-confirm').modal('show');
                } else {
                    var warningMsg = result.message;
                    $('.warning-action').attr('data-noti', warningMsg);
                    $('.warning-action').trigger('click');
                }
            },
            error: function () {
                var warningMsg = globalMessage.errorSystemMsg;
                $('.warning-action').attr('data-noti', warningMsg);
                $('.warning-action').trigger('click');
            },
        });
    });
    $('#modal-submit-report-warn-confirm').on('show.bs.modal', function () {
        var notification;
        var buttonSubmitReport = $('.is-report');
        if (buttonSubmitReport && buttonSubmitReport.length) {
            notification = buttonSubmitReport.attr('data-noti');
        }
        if (notification) {
            $(this).find('.modal-header .modal-title-change').show().html(
                    buttonSubmitReport.attr('data-title')
                    );
            $(this).find('.modal-header .modal-title').hide();
            $(this).find('.modal-body .text-default').hide().html(notification);
            var inputNotChange = '';
            $('#form-dashboard-point .pp-input').each(function () {
                var $newValue = $(this).val();
                var $oldValue = $(this).attr('oldValue');
                var textInput = $(this).attr('text-data');
                if ($newValue == $oldValue && textInput) {
                    inputNotChange += '<li>' + textInput + '</li>';
                }
            });
            if (inputNotChange) {
                $(this).find('.modal-body .text-change').show().html(notification);
                $(this).find('.modal-body .ul-wraning').html(inputNotChange);
            } else {
                $(this).find('.modal-body').hide();
            }
            $(this).find('.modal-footer .btn-ok').addClass('submit-report');
        }
    });

    /** edit point */
    var defaultValuePP = {
        'cost_plan_effort_total': parseFloat($('.cost_plan_effort_total').text().trim()),
        'cost_resource_allocation_total': parseFloat($('.cost_resource_allocation_total').text().trim())
    };
    $('.pp-inputs').change(function (e) {
        if (!$('#form-dashboard-point').valid()) {
            return true;
        }
        value = $(this).val();
        name = $(this).attr('name');
        type = $(this).parents('.tab-pane').attr('id');
        data = {
            data: {}
        };
        data.data[name] = value;
        data._token = siteConfigGlobal.token;
        if (type && $('.fa-refresh.' + type).length) {
            $('.fa-refresh.' + type).removeClass('hidden');
        }
        $.ajax({
            url: globalPassModule.urlSavePoint,
            dataType: 'json',
            type: 'post',
            data: data,
            success: function (data) {
                if (typeof data.success !== 'undefined' && data.success == 1) {
                    if (typeof data.data !== 'undefined' && data.data) {
                        $.each(data.data, function (i, k) {
                            if ($(i).length) {
                                if (k === null) {
                                    $(i).html('&nbsp;');
                                } else {
                                    $(i).text(k);
                                }
                            }
                        });
                    }
                    if (typeof data.input !== 'undefined' && data.input) {
                        $.each(data.input, function (i, k) {
                            if ($('input.pp-input[name=' + i + ']').length) {
                                $('input.pp-input[name=' + i + ']').val(k).attr('data-value', k);
                            }
                        });
                    }
                    if (typeof data.color !== 'undefined' && data.color) {
                        $.each(data.color, function (i, k) {
                            if ($(i + ' img').length) {
                                $(i + ' img').attr('src', k);
                            }
                        });
                    }
                    if (typeof data.content_color !== 'undefined' && data.content_color) {
                        $.each(data.content_color, function (i, k) {
                            if ($(i).length) {
                                var tr = $(i).closest('tr');
                                tr.removeClass('pp-bg-red');
                                tr.removeClass('pp-bg-yellow');
                                if (k) {
                                    tr.addClass(k);
                                }
                            }
                        });
                    }
                } else {
                    if (typeof data.message !== 'undefined' && data.message) {
                        $('.warning-action').attr('data-noti', data.message);
                    }
                    $('.warning-action').trigger('click');
                }
                $('.fa-refresh.' + type).addClass('hidden');
            },
            error: function () {
                $('.warning-action').trigger('click');
                $('.fa-refresh.' + type).addClass('hidden');
            }
        });
    });

    $('.note-input').change(function (event) {
        var type = $(this).parents('.tab-pane').attr('id'),
                name = $(this).attr('name'),
                value = $(this).val(),
                data = {
                    data: {},
                    baselineId: globalPassModule.baselineId
                },
                domInputLoading = $(this).parent().find('.input-loading');
        data.data[name] = value;
        data._token = siteConfigGlobal.token;
        if (type && $('.fa-refresh.' + type).length) {
            $('.fa-refresh.' + type).removeClass('hidden');
        }
        domInputLoading.removeClass('hidden');
        $.ajax({
            url: globalPassModule.urlSaveNote,
            dataType: 'json',
            type: 'post',
            data: data,
            success: function (data) {
                if (typeof data.success !== 'undefined' && data.success == 1) {
                    $('.fa-refresh.' + type).addClass('hidden');
                    $(".highlight-td").load(location.href + " .highlight-td");
                    if (typeof data.data !== 'undefined' && data.data) {
                        $.each(data.data, function (i, v) {
                            var showText = $('.note-input[name=' + i + ']'),
                                    showTextTooltip = showText.closest('.text-tooltip-wrapper');
                            if (showText.length) {
                                showText.val(RKfuncion.general.parseHtml(v));
                                if (showTextTooltip.length) {
                                    if (data.disableTooltip &&
                                            typeof data.disableTooltip[i] !== 'undefined' &&
                                            data.disableTooltip[i]
                                            ) {
                                        showTextTooltip.addClass('tooltip-disable');
                                    } else {
                                        showTextTooltip.removeClass('tooltip-disable');
                                    }
                                }
                            }
                        });
                    }
                } else {
                    $('.fa-refresh.' + type).addClass('hidden');
                    $('.warning-action').attr('data-noti', data.message);
                    $('.warning-action').trigger('click');
                }
                domInputLoading.addClass('hidden');
            },
            error: function () {
                $('.warning-action').attr('data-noti', data.message);
                $('.warning-action').trigger('click');
                $('.fa-refresh.' + type).addClass('hidden');
                domInputLoading.addClass('hidden');
            }
        });
    });

    $('form.submit-disable').submit(function (event) {
        event.preventDefault();
    });

    if ($('#form-dashboard-point').length) {
        $('#form-dashboard-point').validate({
            rules: {
                'cost_plan_effort_current': {
                    number: true,
                    min: 0,
                    max: defaultValuePP.cost_plan_effort_total
                },
                'cost_resource_allocation_current': {
                    number: true,
                    min: 0,
                    max: defaultValuePP.cost_resource_allocation_total
                },
                'cost_actual_effort': {
                    number: true,
                    min: 0
                },
                'proc_compliance': {
                    digits: true,
                    min: 0
                },
                'proc_report_yes': {
                    digits: true,
                    min: 0
                },
                'proc_report_no': {
                    digits: true,
                    min: 0
                },
                'proc_report_delayed': {
                    digits: true,
                    min: 0
                },
                'tl_schedule': {
                    number: true,
                    min: 0
                },
                'qua_leakage_errors': {
                    digits: true,
                    min: 0
                },
                'qua_defect_errors': {
                    digits: true,
                    min: 0
                },
                'qua_defect_reward_errors': {
                    digits: true,
                    min: 0
                }
            }
        });
    }

    RKfuncion.formValidateTask();

    if ($('#form-task-comment').length) {
        formTaskValid = $('#form-task-comment').validate({
            rules: {
                'tc[content]' : "required",
            },
            messages: {
                'tc[content]' : requiredCmt,
            },
        });
    }
    RKfuncion.keepStatusTab.init();
    /* end edit point*/


    $(document).on('mouseenter', '.text-tooltip-wrapper:not(.tooltip-disable)', function () {
        var width = $(this).find('.text-display').width();
        if (width) {
            $(this).find('.text-tooltip').width(width);
        }
        $(this).addClass('open');
        $(this).closest('.table-responsive').addClass('disable-responsive');
    }).on('mouseleave', '.text-tooltip-wrapper:not(.tooltip-disable)', function () {
        $(this).removeClass('open');
        $(this).closest('.table-responsive').removeClass('disable-responsive');
    });
    $(document).on('click', '.btn-sync-source-server', function () {
        event.preventDefault();
        var __this = $(this),
                type = __this.data('type'),
                reload = __this.data('reload'),
                isProcess = __this.data('process');
        if (isProcess || !type || typeof globalPassModule.urlSyncSource == 'undefined') {
            return true;
        }
        __this.data('process', 1);
        __this.find('.sync-loading').removeClass('hidden');
        $.ajax({
            url: globalPassModule.urlSyncSource,
            type: 'post',
            data: {
                _token: siteConfigGlobal.token,
                type: type,
                reload: reload
            },
            dataType: 'json',
            success: function (data) {
                if (typeof data.success !== 'undefined' && data.success == 1) {
                    if (typeof data.popup == 'undefined' || data.popup != 1) {
                        if (typeof data.message !== 'undefined' && data.message) {
                            $('.success-action').attr('data-noti', data.message);
                        }
                        $('.success-action').trigger('click');
                        if (__this.hasClass('sync-parent') && __this.siblings('.sync-child').length) {
                            __this.addClass('hidden');
                            __this.siblings('.sync-child').removeClass('hidden');
                        }
                    } else if (typeof data.refresh !== 'undefined' && data.refresh) {
                        window.location.href = data.refresh;
                    } else {
                        window.location.reload();
                    }
                } else {
                    if (typeof data.message !== 'undefined' && data.message) {
                        $('.warning-action').attr('data-noti', data.message);
                    }
                    $('.warning-action').trigger('click');
                }
                __this.find('.sync-loading').addClass('hidden');
            },
            error: function () {
                if (!$('.warning-action').length) {
                    $('body').append('<button class="warning-action hidden"></button>');
                }
                $('.warning-action').trigger('click');
                __this.find('.sync-loading').addClass('hidden');
            },
            complete: function () {
                __this.data('process', 0);
            }
        });
    });
    RKfuncion.radioToggleClickShow.init();

    /**
     * approve wo
     */
    $(window).load(function () {
        RKfuncion.select2.elementRemote(
                $('.select-search-remote-reviewer')
                );
    });

    $(document).on('click', '.btn-change-approver', function () {
        var _this = $(this),
                type = _this.data('type'),
                parentItem = _this.closest('.assign-item');

        RKfuncion.select2.elementRemote(
                parentItem.find('.assign-name-select .select-search-remote')
                );
        parentItem.find('.assign-name-select').removeClass('hidden');
        parentItem.find('.assign-name-old').addClass('hidden');
        parentItem.find('.assign-name-save').removeClass('hidden');
        _this.addClass('hidden');
        parentItem.find('.btn-remove').removeClass('hidden');
    });
    $(document).on('click', '.btn-remove-approver', function () {
        var _this = $(this),
                parentItem = _this.closest('.assign-item');
        _this.addClass('hidden');
        parentItem.find('.assign-name-select').addClass('hidden');
        parentItem.find('.assign-name-old').removeClass('hidden');
        parentItem.find('.assign-name-save').addClass('hidden');
        parentItem.find('.btn-remove').addClass('hidden');
        parentItem.find('.btn-change-approver').removeClass('hidden');
    });
    RKfuncion.formSubmitAjax.woChangeApproverSuccess = function (dom, data) {
        if (typeof data.data == 'undefined' ||
                typeof data.data.id == 'undefined' ||
                typeof data.data.account == 'undefined' ||
                !data.data.id ||
                !data.data.account
                ) {
            return true;
        }
        dom.find('.assign-name .assign-name-old').text(data.data.name +
                ' (' + data.data.account + ')');
        $('.btn-remove-approver').trigger('click');
    };
    RKfuncion.formSubmitAjax.woDeleteReviewerSuccess = function (dom, data) {
        if (typeof data.data != 'undefined') {
            if (typeof data.data.reviewer_only != 'undefined' &&
                    data.data.reviewer_only == 1
                    ) {
                dom.closest('.wa-item').find('.btn-remove').addClass('hidden');
            }
            if (typeof data.data.self_remove != 'undefined' && data.data.self_remove == 1) {
                $('.btn-submit-reviewer').remove();
            }
        }
        dom.closest('.assign-item').remove();
    };

    /** wo new ui**/
    //Enable datepickers
    $('body').on('focus', '.datepicker', function (e) {
        $(this).datepicker({
            format: 'yyyy-mm-dd',
            weekStart: 1,
            todayHighlight: true,
            autoclose: true
        });
    });

    /*
     * Add busy indicator for save-on-changed inputs
     */
    $('.form-control.save-on-changed').on('change', function (e) {
        //TODO Make AJAX request to update

        var $this = $(this);
        var $formGroup = $this.closest('.form-group');
        var isInputGroup = $this.parent().hasClass('input-group');
        var isSelectTag = $this.prop("tagName").toLowerCase() == 'select';
        // Disable input when busy
        $this.prop('disabled', true);
        $this.tooltip('hide');

        // Show busy indicator
        var $indicator = $('<i class="fa fa-refresh fa-spin form-control-feedback"></i>');
        if (isSelectTag) {
            $indicator.css('right', '25px');
        } else if (isInputGroup) {
            var $rightAddon = $this.parent().find('.form-control + .input-group-addon');
            if ($rightAddon.length > 0) {
                var indicatorRightPosition = $rightAddon.outerWidth() + 15;
                $indicator.css('right', indicatorRightPosition + 'px');
            }
        }
        $formGroup.addClass('has-feedback');
        if (isInputGroup) {
            $indicator.insertAfter($this.parent());
        } else {
            $indicator.insertAfter($this);
        }

        //TODO Remove busy indicator when AJAX completed
        // Simulation
        setTimeout(function () {
            $this.prop('disabled', false);
            $indicator.remove();
            $formGroup.removeClass('has-feedback');
            if (!$this.hasClass('no-need-approvement')) {
                $this.addClass('changed');
            }
        }, 500);
    });

    /*
     * Edit table row
     */
    if ($('.wo-action-btns-href').length && $('.wo-action-btns').length) {
        $('.wo-action-btns').html($('.wo-action-btns-href').html());
    }
    $('#feedback-modal').on('shown.bs.modal', function () {
        $('#feedback-modal textarea').focus();
    });
    if ($('#form-wo-change-reviewer').length) {
        $('#form-wo-change-reviewer').validate({
            rules: {
                'assign[id]': {
                    required: true
                }
            }
        });
        RKfuncion.general.btnSubmitHref();
    }
    if ($('#form-wo-approve-feedback').length) {
        $('#form-wo-approve-feedback').validate({
            rules: {
                'fb[comment]': {
                    required: true
                }
            }
        });
    }
    if ($('#form-wo-review-feedback').length) {
        $('#form-wo-review-feedback').validate({
            rules: {
                'fb[comment]': {
                    required: true
                }
            }
        });
    }
    /** end wo new ui**/

    /**
     * project reward block
     */
    /**
     * copy reward of PM to leader
     */
    function copyRewardForLeader() {
        if (!RKProjectReward.is_submitted) {
            return true;
        }

    }
    function RKProjectRewardInit() {
        // move feedback modal
        if ($('#modal-reward-feedback').length && $('#form-project-reward').length) {
            $('#modal-reward-feedback').appendTo('#form-project-reward');
        }
        if (typeof RKProjectReward == 'undefined') {
            return;
        }
        var unitPointReward = {
            dev: RKProjectReward.point_employee.dev.total ?
                    RKProjectReward.reward.dev / RKProjectReward.point_employee.dev.total : 0,
            sqa: RKProjectReward.point_employee.sqa.total ?
                    RKProjectReward.reward.sqa / RKProjectReward.point_employee.sqa.total : 0,
            pqa: RKProjectReward.point_employee.pqa.total ?
                    RKProjectReward.reward.pqa / RKProjectReward.point_employee.pqa.total : 0
        },
                iCount = {
                    dev: 0,
                    sqa: 0,
                    pqa: 0
                },
                typesEmployee = ['dev', 'sqa', 'pqa', 'add'],
                rewardEmployee = 0,
                rewardNorm = 0,
                rewardEmployeeTotal = {
                    dev: 0,
                    sqa: 0,
                    pqa: 0
                };
        // caculator norm reward
        $('.reward-employee-row').each(function () {
            var rewardEmployeeRowDom = $(this),
                    typeEmployee = rewardEmployeeRowDom.data('type'),
                    idEmployeeReward = rewardEmployeeRowDom.data('id');
            if (!typeEmployee ||
                    !idEmployeeReward ||
                    typesEmployee.indexOf(typeEmployee) < 0
                    ) {
                return true;
            }
            iCount[typeEmployee]++;
            if (iCount[typeEmployee] >= RKProjectReward.count_employee[typeEmployee]) {
                rewardEmployee = Math.round(RKProjectReward.reward[typeEmployee] -
                        rewardEmployeeTotal[typeEmployee]);
            } else {
                rewardEmployee = Math.round(unitPointReward[typeEmployee] *
                        RKProjectReward.point_employee[typeEmployee][idEmployeeReward]);
                rewardEmployeeTotal[typeEmployee] += rewardEmployee;
            }
            rewardEmployeeRowDom.find('.reward-norm').html($.number(rewardEmployee));
            var domInput = rewardEmployeeRowDom.find('input.input-reward.input-reward-submit');
            // if PM not save, default reward employee = reward norm
            if (domInput.length &&
                RKProjectReward.pm_null
            ) {
                domInput.val($.number(rewardEmployee));
            }
            rewardNorm += rewardEmployee;
            if (RKProjectReward.is_submitted && RKProjectReward.leader_null) {
                rewardEmployeeRowDom.find('input.input-reward-confirm').val(
                    rewardEmployeeRowDom.find('[data-reward-fill="pm"]')
                        .text().trim().replace(/,/g, '')
                );
            } else if (RKProjectReward.is_reviewed && RKProjectReward.coo_null) {
                rewardEmployeeRowDom.find('input.input-reward-approve').val(
                    rewardEmployeeRowDom.find('[data-reward-fill="leader"]')
                        .text().trim().replace(/,/g, '')
                );
            } else {
                // nothing
            }
        });
        $('.reward-total-report [data-reward-total="norm"]').html($.number(rewardNorm));
        $('[data-flag-load="reward"]').remove();
        $('[data-loading="reward"]').removeClass('hidden');
        $('.bonus-money .toggle.btn.btn-sm').width('78');
    }
    RKProjectRewardInit();
    $('.btn-submit-reward').click(function () {
        var data = $(this).data('save');
        $('#form-project-reward input[name="save"] ').val(data);
        if ($('.rw-new-employee').length > 0) {
            var error = false;
            $('.rw-new-employee').each(function () {
                if (!$(this).val()) {
                    error = true;
                }
            });
            if (error) {
                bootbox.alert({
                   className: 'modal-danger',
                   message: textTrans.messageRequireEmployee,
                });
                return false;
            }
        }
    });
    $('.btn-reward-fill-feeback-confirm').click(function () {
        $(this).addClass('hidden');
        $('.btn-feedback-confirm').removeClass('hidden');
        $('.btn-reward-save').removeClass('hidden');
        $('.input-reward.input-reward-confirm').removeClass('hidden');
        $('.text-reward-confirm').addClass('hidden');
    });
    $('.btn-reward-fill-feeback-approve').click(function () {
        $(this).addClass('hidden');
        $('.btn-feedback-approve').removeClass('hidden');
        $('.btn-reward-save').removeClass('hidden');
        $('.input-reward.input-reward-approve').removeClass('hidden');
        $('.text-reward-approve').addClass('hidden');
    });
    $('.input-number-format').change(function () {
        try {
            var value = $.number($(this).val()),
                    valueFloat = $(this).val().trim().replace(/,/g, ''),
                    type = $(this).data('type');
            if (valueFloat) {
                $(this).val(value);
            }
        } catch (e) {
        }
    });
    $('body').on('change', 'input.input-reward', function () {
        var value = $.number($(this).val()),
                valueFloat = $(this).val().trim().replace(/,/g, ''),
                type = $(this).data('type');
        if (valueFloat) {
            $(this).val(value);
        }
        if (type) {
            var totalType = 0;
            $('input.input-reward[data-type="' + type + '"]').each(function () {
                var valueEach = $.number($(this).val()),
                        valueFloatEach = $(this).val().trim().replace(/,/g, '');
                if (valueFloatEach) {
                    totalType += parseFloat(valueFloatEach);
                }
            });
            $('.reward-total-cal[data-type="' + type + '"]').html($.number(totalType));
            $('input[name="total[' + type + ']"]').val(totalType);
        }
        // disable submit form
        var flagAvaiSubmit = true;
        $('#form-project-reward input.input-reward').each(function () {
            var valueFloat = parseFloat($(this).val().trim().replace(/,/g, ''));
            if (valueFloat < 0) {
                $(this).next('.error').remove();
                $('<span class="error">' +
                        varGlobalPassModule.messageValidateRewardMin + '</span>').insertAfter($(this));
                flagAvaiSubmit = false;
            } else {
                $(this).next('.error').remove();
            }
        });
        $('#form-project-reward').valid();
        //end validate
        if (!flagAvaiSubmit) {
            $('#form-project-reward [type="submit"]').attr('disabled', 'disabled');
        } else {
            $('#form-project-reward [type="submit"]').removeAttr('disabled');
        }
    });
    var typeCheck = {},
        rulesInputReward = {};
    $('input.input-reward').each(function () {
        var type = $(this).data('type'),
                totalType = 0;
        if (typeCheck[type]) {
            return true;
        }
        typeCheck[type] = true;
        $('input.input-reward[data-type="' + type + '"]').each(function () {
            if ($(this).val()) {
                totalType += parseFloat($(this).val().trim().replace(/,/g, ''));
            }
        });
        $('.reward-total-cal[data-type="' + type + '"]').html($.number(totalType));
    });
    if ($('#form-project-reward').length) {
        var rulesFormReard = {
            'total[submit]': {
                required: true,
                min: 0,
                max: RKProjectReward.reward.total
            },
            'total[confirm]': {
                required: true,
                min: 0,
                max: RKProjectReward.reward.total
            },
            'total[approve]': {
                required: true,
                min: 0,
                max: RKProjectReward.reward.total
            },
            'fb[comment]': {
                required: true
            }
        };
        $('#form-project-reward').validate({
            rules: rulesFormReard,
            messages: {
                'total[submit]': {
                    max: varGlobalPassModule.messageValidateRewardFillTotal,
                    min: varGlobalPassModule.messageValidateRewardMin
                },
                'total[confirm]': {
                    max: varGlobalPassModule.messageValidateRewardFillTotal,
                    min: varGlobalPassModule.messageValidateRewardMin
                },
                'total[approve]': {
                    max: varGlobalPassModule.messageValidateRewardFillTotal,
                    min: varGlobalPassModule.messageValidateRewardMin
                }
            }
        });
    }
    //reward bugdet
    function rKRewardBudgetValidate() {
        if (!$('.form-reward-budget').length) {
            return false;
        }
        var messageValidateGreater = 'Giá trị budget của level cao hơn không thể thấp hơn giá trị budget của level nhỏ hơn nó';
        $('.form-reward-budget input.input-reward-budget').change(function () {
            // disable submit form
            var flagAvaiSubmit = true;
            $('.form-reward-budget input.input-reward-budget').each(function () {
                $(this).next('.error').remove();
                var valueFloat = parseFloat($(this).val().trim().replace(/,/g, ''));
                if (valueFloat < 0) {
                    $(this).next('.error').remove();
                    $('<label class="error">' +
                            varGlobalPassModule.messageValidateRewardMin + '</label>').insertAfter($(this));
                    flagAvaiSubmit = false;
                }
                // if reward < 0 => not check greater
                if (!flagAvaiSubmit) {
                    return true;
                }
                var rewardLevel = parseInt($(this).data('reward-level')),
                        domRewardLevelGreater = $('.form-reward-budget ' +
                                'input.input-reward-budget[data-reward-level="' + (rewardLevel + 1) + '"]');
                if (!domRewardLevelGreater.length) {
                    return true;
                }
                var rewarardLevelGreater = parseFloat(domRewardLevelGreater.val().trim().replace(/,/g, ''));
                if (valueFloat > rewarardLevelGreater) {
                    $('<label class="error">' +
                            messageValidateGreater + '</label>')
                            .insertAfter(domRewardLevelGreater);
                    flagAvaiSubmit = false;
                }
            });
            if (!flagAvaiSubmit) {
                $('.form-reward-budget [data-block-form-submit="1"]').attr('disabled', 'disabled');
            } else {
                $('.form-reward-budget [data-block-form-submit="1"]').removeAttr('disabled');
            }
        });

        $('.form-reward-budget input.input-reward-budget-long').change(function () {
//            disable submit form
            var flagAvaiSubmit = true;
            $(this).next('.error').remove();
            var valueFloat = parseFloat($(this).val().trim().replace(/,/g, ''));
            if (valueFloat < 0) {
                $('input').find('.error').remove();
                $('<label class="error">' +
                        varGlobalPassModule.messageValidateRewardMin + '</label>').insertAfter($(this));
                flagAvaiSubmit = false;
            }

            var rewardLevel = parseInt($(this).data('reward-level')),
                    domRewardLevelGreater = $('input[data-reward-level="' + (rewardLevel + 1) + '"]');
            var rewardCurrentLever = 0;
            $('input[data-reward-level="' + rewardLevel + '"]').each(function () {
                rewardCurrentLever = rewardCurrentLever + parseFloat($(this).val().trim().replace(/,/g, ''));
            });

            var leverGreater = rewardLevel + 1;
            var rewarardLevelGreater = 0;
            $('input[data-reward-level="' + leverGreater + '"]').each(function () {
                rewarardLevelGreater = rewarardLevelGreater + parseFloat($(this).val().trim().replace(/,/g, ''));
            });
            $('td[sum-level="' + rewardLevel + '"]').text($.number(rewardCurrentLever));
            // if reward < 0 => not check greater
            if (!flagAvaiSubmit) {
                return true;
            }

            if (!domRewardLevelGreater.length) {
                return true;
            }

            if (rewardCurrentLever > rewarardLevelGreater) {
                $('<label class="error">' +
                        messageValidateGreater + '</label>')
                        .insertAfter($(this));
                flagAvaiSubmit = false;
            }

            if (!flagAvaiSubmit) {
                $('.form-reward-budget [data-block-form-submit="1"]').attr('disabled', 'disabled');
            } else {
                $('.form-reward-budget [data-block-form-submit="1"]').removeAttr('disabled');
            }

        });
    }
    rKRewardBudgetValidate();
    /* end project reward */

    /* MyTask */
    RKfuncion.formSubmitAjax['myTaskCallBack'] = function (dom, data) {
        if (data.taskObject) {
            $('#myTask-index .title-myTask-' + data.taskObject.id + ' .post-ajax .title-myTask').html(data.taskObject.title);
            if (data.taskObject.setColor) {
                $('#myTask-index .title-myTask-' + data.taskObject.id + ' .post-ajax .title-myTask').addClass('text-color-red');
            } else {
                $('#myTask-index .title-myTask-' + data.taskObject.id + ' .post-ajax .title-myTask').removeClass('text-color-red');
            }
            $('#myTask-index .type-myTask-' + data.taskObject.id).html(data.taskObject.type);
            $('#myTask-index .status-myTask-' + data.taskObject.id).html(data.taskObject.status);
            $('#myTask-index .priority-myTask-' + data.taskObject.id).html(data.taskObject.priority);
            $('#myTask-index .duedate-myTask-' + data.taskObject.id).html(data.taskObject.due_date);
            $('#myTask-index .created-myTask-' + data.taskObject.id).html(data.taskObject.created);
        }
    };

    /*End MyTask*/
    RKfuncion.formSubmitAjax['workoderErrorDate'] = function (dom, data) {
        if (!data.updateTime) {
            if (typeof data.message !== 'undefined' && data.message) {
                if (typeof data.reload !== 'undefined' && data.reload == 1) {
                    window.location.reload();
                } else {
                    $('.warning-action').attr('data-noti', data.message);
                    $('.warning-action').trigger('click');
                }
            }
            return true;
        }
        $('#modal-update-time-confirm .text-default').html(data.messageUpdateTime);
        $('#modal-update-time-confirm').modal('show');
    };
    // render team dev option
    if (typeof globalPassModule !== 'undefined' && $('select[data-team="dev"]').length) {
        var teamDevOption = RKfuncion.teamTree.init(globalPassModule.teamPath, globalPassModule.teamSelected);
        var htmlTeamDevOption, disabledTeamDevOption, selectedTeamDevOption;
        $.each(teamDevOption, function(i,v) {
            selectedTeamDevOption = v.selected ? ' selected' : '';
            if (v.id != globalPassModule.teamSelected) {
                selectedTeamDevOption = '';
            }
            htmlTeamDevOption += '<option value="'+v.id+'"'+disabledTeamDevOption+''
                +selectedTeamDevOption+'>' + v.label+'</option>';
        });
        $('select[data-team="dev"]').append(htmlTeamDevOption);
    }
    // end render team dev option

    // add custom member project reward
    $('body').on('click', '#btn-add-reward', function (e) {
        e.preventDefault();
        var tableReward = $('#table_reward_employees');
        if (tableReward.length < 1) {
            return;
        }
        var elItem = tableReward.find('tbody tr:first').clone();
        var prevItem = tableReward.find('tbody .reward-total-report').prev('tr');
        var tplId = prevItem.attr('data-id');
        if ($.isNumeric(tplId)) {
            var currId = 1;
        } else {
            var currId = parseInt(prevItem.attr('data-new')) + 1;
        }
        elItem.attr('data-id', 'new_' + currId).attr('data-new', currId);

        var colsInput = ['reward_submit', 'reward_confirm', 'reward_approve'];
        elItem.find('td').each(function (index, tdItem) {
            var colName = $(tdItem).attr('data-col') || null;
            if (colName && colsInput.indexOf(colName) > -1) {
                $(tdItem).find('input').val('');
                $(tdItem).find('.data-reward-fill').text('');
            } else {
                $(tdItem).text('');
            }
        });
        //col submit
        elItem.find('[data-col="reward_submit"] input').attr('name', 'reward[new]['+ currId +'][submit]').attr('data-id', 'new_' + currId);
        //search employee
        elItem.find('[data-col="employee"]').html(
            '<button type="button" class="btn btn-danger btn-sm btn-del-member"><i class="fa fa-trash"></i></button>' +
            '<select class="form-control select-search rw-new-employee" ' +
                'name="reward[new]['+ currId +'][employee_id]"' +
                'data-remote-url="'+ urlSearchEmployee +'" style="min-width: 150px;">' +
            '</select>'
        ).addClass('col-action');
        elItem.insertAfter(tableReward.find('tbody tr.reward-employee-row:last'));
        RKfuncion.select2.init({}, elItem.find('[data-col="employee"] .select-search'));
    });

    $('body').on('click', '#table_reward_employees .btn-del-member', function (e) {
        e.preventDefault();
        var btn = $(this);
        if (btn.is(':disabled')) {
            return;
        }
        var trItem = $(this).closest('tr');
        var trId = trItem.attr('data-id');

        if (!$.isNumeric(trId)) {
            trItem.remove();
        } else {
            bootbox.confirm({
                className: 'modal-warning',
                message: textTrans.messageConfirmDelete,
                callback: function (result) {
                    if (result) {
                        btn.prop('disabled', true);
                        trItem.addClass('bg-warning');
                        $.ajax({
                            url: urlDelEmpRW,
                            type: 'delete',
                            data: {
                                _token: siteConfigGlobal.token,
                                id: trId,
                            },
                            success: function (message) {
                                trItem.remove();
                                $('.input-reward:first').trigger('change');
                            },
                            error: function (error) {
                                bootbox.alert({
                                    className: 'modal-danger',
                                    message: error.responseJSON,
                                });
                            },
                            complete: function () {
                                btn.prop('disabled', false);
                                trItem.removeClass('bg-warning');
                            },
                        });
                    }
                }
            });
        }
    });
});

(function ($) {
    //call back for destroy raise bom
    RKfuncion.formSubmitAjax.raiseDestroyAfterSuccess = function (dom, data) {
        dom.remove();
    };

    // callback for create/edit ncm
    RKfuncion.formSubmitAjax.loadModalFormSuccess = function (dom, data) {
        if (!$('#modal-ncm-editor .modal-ncm-editor-main').length) {
            return null;
        }
        $('#modal-ncm-editor .modal-ncm-editor-main').html(data.htmlModal);
        if (!data.modalTitle) {
            data.modalTitle = 'None Compliance';
        }
        $('#modal-ncm-editor .modal-header .modal-title').html(data.modalTitle);
        $('#modal-ncm-editor').modal('show');
    };

    // validate task form
    RKfuncion.formValidateTask = function () {
        if (!$('#form-task-edit').length) {
            return true;
        }
        if (typeof $.validator !== 'undefined' && typeof $.validator.addMethod != 'undefined') {
            $.validator.addMethod('le', function (value, element, param) {
                return this.optional(element) || value <= $(param).val();
            }, 'Please enter a value less than or equal Created date.');
            $.validator.addMethod('ge', function (value, element, param) {
                return this.optional(element) || value >= $(param).val();
            }, 'Please enter a value greater than or equal Created date');
        }
        $('#form-task-edit').validate({
            rules: {
                'task[type]': {
                    required: true
                },
                'task_assign[]': {
                    required: true
                },
                'task[title]': {
                    required: true
                },
                'task[duedate]': {
                    required: true,
                    ge: 'input[name^="task[created_at]"'
                },
                'task[status]': {
                    required: true
                },
                'task[priority]': {
                    required: true
                },
                'task[content]': {
                    required: true
                },
                'task[created_at]': {
                    required: true
                },
                'task[actual_date]': {
                    ge: 'input[name^="task[created_at]"'
                }
            },
            messages: {
                'task[type]': requiredText,
                'task_assign[]': requiredText,
                'task[title]': requiredText,
                'task[duedate]': {
                    required: requiredText,
                    ge: textGreater,
                },
                'task[status]': requiredText,
                'task[priority]': requiredText,
                'task[content]': requiredText,
                'task[created_at]': requiredText,
                'task[actual_date]': {
                    ge: textGreater,
                }
            }
        });
    };
})(jQuery);
// change task status and task priority in list general task
jQuery(document).ready(function ($) {
    // event hover status and priority
    statusHover('.priority-index, .status-index');
    $(document).on('click', '.priority-index, .status-index', function () {
        var thisObject = $(this);
        thisObject.find("i").remove();
        var isSelect = parseInt($(this).data('select'));
        if (isSelect) {
            return true;
        }
        var taskId = thisObject.data("id");
        var isPrioritySelect = false;
        if (thisObject.attr('data-priority') != undefined) {
            var isPrioritySelect = true;
            var path = varGlobalPassModule.routePriority;
            var priority = thisObject.data("priority");
            var htmlSelect = '<select name=task[priority]>';
            $.each(varGlobalPassModule.priorities, function (i, k) {
                htmlSelect += '<option value="' + i + '"';
                if (i == priority) {
                    htmlSelect += ' selected';
                }
                htmlSelect += '>' + k + '</option>';
            });
            htmlSelect += '</select>';
        }
        if (thisObject.attr('data-status') != undefined) {
            var path = varGlobalPassModule.routeStatus;
            var status = thisObject.data("status");
            var htmlSelect = '<select name=task[status]>';
            $.each(varGlobalPassModule.status, function (i, k) {
                htmlSelect += '<option value="' + i + '"';
                if (i == status) {
                    htmlSelect += ' selected';
                }
                htmlSelect += '>' + k + '</option>';
            });
            htmlSelect += '</select>';
        }
        thisObject.html(htmlSelect);
        thisObject.data('select', 1);
        var taskId = thisObject.data('id');
        thisObject.find('select').change(function () {
            if (isPrioritySelect) {
                prioritySelect = $(this).val();
                statusSelect = null;
            } else {
                statusSelect = $(this).val();
                prioritySelect = null;
            }
            $.ajax({
                url: path,
                type: 'post',
                dataType: 'json',
                data: {
                    _token: siteConfigGlobal.token,
                    id: taskId,
                    priority: prioritySelect,
                    status: statusSelect,
                },
                success: function (data) {
                    if (data.success == 1) {
                        if (isPrioritySelect) {
                            thisObject.html(data.priority);
                            thisObject.data('priority', data.priority_value);
                        } else {
                            thisObject.html(data.status);
                            thisObject.data('status', data.status_value);
                        }
                        thisObject.data('select', 0);
                        statusHover(thisObject);
                    }
                }
            });
        });
    });

    /**
     * func event right click reward actual
     *
     * @param {object jquey} dom
     */
    var funcShowCommentActualReward = function (event, dom) {
        var id = dom.attr('data-id');
        if (!id || $('#reward-comment-box[data-id="' + id + '"]').length ||
                !$('.reward-comment').length
                ) {
            return true;
        }
        $('#reward-comment-box .close').trigger('click');
        var html,
                positionX = event.pageX,
                positionY = event.pageY,
                outerWidth = $('#reward-comment-box').outerWidth(),
                windowWidth = $(window).width();
        html = $('.reward-comment #reward-comment-box').clone();
        html.attr('data-id', id);
        html.find('#reward-comments-list').addClass('reward-comments-list-' + id);
        html.find('#reward-comment-text').addClass('reward-comment-text-' + id);
        if (outerWidth + positionX > windowWidth) {
            positionX = windowWidth - outerWidth - 10;
        }
        html.css({
            'left': positionX + 'px',
            'top': positionY + 'px'
        });
        var data = {
            _token: siteConfigGlobal.token,
            id: id
        };
        $.ajax({
            url: urlGetCommentRW,
            type: 'post',
            data: data,
            dataType: 'json',
            success: function (data) {
                data = data.trim();
                if (data) {
                    html.find('.cmt-box-list .comments_list').text(data);
                    html.find('.cmt-input').val(data);
                } else {
                    html.find('.cmt-btn-edit').trigger('click');
                }
            }
        });
        $('body').append(html);
    };

    $(document).on('click', '.cmt-btn-edit', function (event) {
        event.preventDefault();
        var wrapper = $(this).closest('.cmt-box-wrapper');
        wrapper.find('.cmt-box-list').addClass('hidden');
        wrapper.find('.cmt-box-edit').removeClass('hidden');
    });

    $('body').on('mousedown', '.reward-employee-row', function (e) {
        window.oncontextmenu = function () {
            return false;     // cancel default menu
        };
        if (e.which === 3) { // click right mouse
            funcShowCommentActualReward(e, $(this));
            return false;
        }
    });

    $('body').on('click', '.reward_comment_submit', function (e) {
        e.preventDefault();
        var __this = $(this),
        wrapper = __this.closest('.cmt-box-wrapper'),
        id = wrapper.attr('data-id'),
        value = wrapper.find('.cmt-input').val().trim(),
        data = {
            _token: siteConfigGlobal.token,
            value: value,
            id: id
        };
        __this.prop('disabled', true);
        $.ajax({
            url: urlAddCommentRW,
            type: 'post',
            data: data,
            dataType: 'json',
            success: function (data) {
                var domRewardRow = $('.reward-employee-row[data-id="'+id+'"] > td.flag-parent-cmt');
                if (domRewardRow.length) {
                    if (value) {
                        domRewardRow.prepend('<span class="flag-has-cmt"></span>');
                    } else {
                        domRewardRow.children('.flag-has-cmt').remove();
                    }
                }

                wrapper.find('.cmt-box-list .comments_list').text(value);
                wrapper.find('.cmt-box-list').removeClass('hidden');
                wrapper.find('.cmt-box-edit').addClass('hidden');
            },
            complete: function () {
                __this.prop('disabled', false);
            }
        });
    });
    /**
     * show / hide column me and effort
     */
    $('[d-btn-reward="hide-me"]').click(function (e) {
        e.preventDefault();
        if ($('.col-mee').hasClass('hidden')) {
            $('.col-mee').removeClass('hidden');
            $('[data-cel-total-span]').attr('colspan', $('[data-cel-total-span]').data('cel-total-span'));
        } else {
            $('.col-mee').addClass('hidden');
            $('[data-cel-total-span]').attr('colspan', 1);
        }
    });
    // edit reward actual box
    var RKRABEdit = {
        init: function() {
            if (!$('#modal-reward-number').length) {
                return true;
            }
            var that = this;
            that.initNumber();
            that.validate();
            that.formatNumber();
        },
        initNumber: function () {
            $('[data-ra-number]').each(function () {
                var type = $(this).data('ra-number');
                $('[name="i['+type+']"]').val($(this).text().trim());
            });
        },
        validate: function () {
            $('#form-rae').validate({
                rules: {
                    'i[reward_budget]': {
                        numberFormat: true,
                        minFormat: 0,
                    },
                    'i[count_defect]': {
                        numberFormat: true,
                        minFormat: 0,
                    },
                    'i[count_defect_pqa]': {
                        numberFormat: true,
                        minFormat: 0,
                    },
                    'i[count_leakage]': {
                        numberFormat: true,
                        minFormat: 0,
                    },
                }
            });
        },
        formatNumber: function () {
            $('[data-input-number="format"]').change(function (v) {
                $(this).val($.number($(this).val().replace(/\,/gi, '')));
            });
        },
    };
    RKRABEdit.init();
    /* end reward script */
    $('body').on('click', '.close', function (e) {
        $(this).closest('div[data-id]').remove();
    });

    $(document).on('change', '.input-cost-productivity-proglang', function (event) {
        var $this = $(this);
        var value = $this.val(),
                name = $this.attr('id'),
                id = $this.attr('data-id');
        if (!$.isNumeric(value) && value != "") {
            $('#' + name + '-' + id + '-error').html('input field must be numeric!').show();
            $('#' + name + '-' + id + '-error').css("color", "red");
            $('#' + name + '-' + id + '-error').css("font-size", "12px");
            $('#' + name + '-' + id + '-error').removeClass('hidden');
        } else {
            $('#' + name + '-' + id + '-error').addClass('hidden');
        }
    });

    // popup convert day work
    $('.link-dw-convert').click(function(event) {
        event.preventDefault();
        if (!$('#modal-dw-convert').length) {
            var htmlModal = '' +
            '<div class="modal fade" id="modal-dw-convert" role="dialog">'+
                '<div class="modal-dialog">'+
                    '<div class="modal-content">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>'+
                            '<h4 class="modal-title">Working days</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row form-group-select2">'+
                                '<div class="col-md-4 margin-bottom-10">'+
                                    '<select class="select-search has-search" name="dw[year]" data-dw="year"></select>'+
                                '</div>'+
                                '<div class="col-md-4 margin-bottom-10">'+
                                    '<select class="select-search has-search" name="dw[month]" data-dw="month"></select>'+
                                '</div>'+
                                '<div class="col-md-4 margin-bottom-10">'+
                                    '<p class="form-control-static">Result: <span data-dw="result"></span></p>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
            '</div>';
            $('body').append(htmlModal);
            var now = new Date(),
            yearCurrent = now.getFullYear(),
            monthCurrent = now.getMonth() + 1,
            htmlOption = '',
            i, iText;
            for (i = 2016; i < yearCurrent + 11; i++) {
                htmlOption += '<option value="'+i+'"';
                if (i === yearCurrent) {
                    htmlOption += ' selected';
                }
                htmlOption += '>'+i+'</option>';
            }
            $('select[data-dw="year"]').append(htmlOption);
            htmlOption = '';
            for (i = 1; i < 13; i++) {
                if (i < 10) {
                    iText = '0' + i;
                } else {
                    iText = i;
                }
                htmlOption += '<option value="'+iText+'"';
                if (i === monthCurrent) {
                    htmlOption += ' selected';
                }
                htmlOption += '>'+iText+'</option>';
            }
            $('select[data-dw="month"]').append(htmlOption);
            RKfuncion.select2.init();
            $('select[data-dw="month"], select[data-dw="year"]').change(function() {
                funcGetWorkingDays();
            });
        }
        $('#modal-dw-convert').modal('show');
        funcGetWorkingDays();
    });
    var funcGetWorkingDays = function() {
        var year = $('select[data-dw="year"]').val(),
        month = $('select[data-dw="month"]').val(),
        domResult = $('[data-dw="result"]'),
        result = 0;

        domResult.html('');
        if (!year || !month || isNaN(year) || isNaN(month) || month < 1 ||
            month > 12 || year.length !== 4
        ) {
            return true;
        }
        result = $.cookie('ck-dw'+year+'-'+month);
        if (result) {
            domResult.html(result);
            return true;
        }
        if (typeof globalPassModule === 'undefined' ||
            typeof globalPassModule.urlWorkingDays === 'undefined'
        ) {
            return true;
        }
        $.ajax({
            url: globalPassModule.urlWorkingDays,
            type: 'GET',
            data: {
                year: year,
                month: month
            },
            dataType: 'json',
            success: function(data) {
                if (data.days) {
                    domResult.html(data.days);
                } else {
                    domResult.html('');
                }
            }
        });
    };
    // end popup convert day work

    // report project onsite
    var rkFuncFormatReportColor = function (result) {
        if (!result.id) {
            return result.text;
        }
        var optionElement = result.element,
        value = $(optionElement).attr('value');
        if (typeof globalPassModule.reportColorAll[value] === 'undefined') {
            return result.text;;
        }
        return '<div class="text-center"><img src="' + globalPassModule.reportColorAll[value] + '"/></div>';
    };
    var rkFuncReportColorSummary = function() {
        var maxValue = 0;
        $('[name^="color"]').each(function(i,v) {
            var value = $(v).val();
            if (isNaN(value)) {
                return true;
            }
            value = parseInt(value);
            if (value > maxValue) {
                maxValue = value;
            }
        });
        $('[data-report-color="summary"] img').attr('src', globalPassModule.reportColorAll[maxValue]);
    };
    if ($('[data-s2-init="report-color"]').length) {
        $('[data-s2-init="report-color"]').select2({
            templateResult: rkFuncFormatReportColor,
            templateSelection: rkFuncFormatReportColor,
            escapeMarkup: function(m) { return m; },
            minimumResultsForSearch: Infinity
        });
        $('[data-s2-init="report-color"]').on("select2:select", function (event) {
            var __this = $(this),
            value = event.params.data.id,
            parent = __this.closest('.report-color-select');
            parent.addClass('hidden');
            parent.siblings('[data-report-color]').removeClass('hidden');
            if (!globalPassModule.reportColorAll[value]) {
                return true;
            }
            parent.siblings('[data-report-color]').children('img')
                .attr('src', globalPassModule.reportColorAll[value]);
            var name = __this.data('name'),
            input = $('[name="'+name+'"]');
            input.val(value);
            rkFuncReportColorSummary();
        });

        $('[data-report-color]').dblclick(function(event) {
            event.preventDefault();
            if (!globalPassModule.isReportColor || !globalPassModule.isReportSubmitAvai) {
                return true;
            }
            var type = $(this).data('report-color'),
            __this = $(this);
            var arraysType = [
                'cost', 'quality', 'tl', 'proc', 'css', 'summary'
            ];
            if (arraysType.indexOf(type) < 0) {
                return true;
            }
            $('[data-report-color]').removeClass('hidden');
            $('.report-color-select').addClass('hidden');
            if (type !== 'summary') {
                __this.addClass('hidden');
                __this.siblings('.report-color-select').removeClass('hidden');
            }
        });
    }
    //end report project onsite
});
// hover on status and priority
function statusHover(selector) {
    $(document).on({
        mouseenter: function () {
            var thisObject = $(this);
            if (thisObject.find("i").length > 0 || thisObject.find("select").length > 0) {
                return true;
            }
            thisObject.append("<i class='fa fa-pencil-square-o pull-right' aria-hidden='true'></i>");
        },
        mouseleave: function () {
            var thisObject = $(this);
            thisObject.find("i").remove();
        }
    }, selector);
}

function bonusMoneyOnclick(value) {
    if ($(value).is(':checked')) {
        bonus_money = 1;
    } else {
        bonus_money = 0;
    }
    var data = {
        _token: token,
        bonus_money: bonus_money
    };
    $.ajax({
        url: urlBonusMoney,
        type: 'post',
        data: data,
        dataType: 'json',
        success: function (data) {
            $('#modal-success-notification').find('.text-default').html(data.message);
            $('#modal-success-notification').modal('show');
        }
    });
}

function generateHtml(type) {
    if (type !== typeIssueCSS) {
        return;
    }
    $.ajax({
        url: urlGenerateHtml,
        type: 'post',
        data: { type: typeCustomerFeedback, _token: token},
        dataType: 'json',
        success: function (data) {
            $('.grid-data-query[data-type="'+typeCustomerFeedback+'"] .grid-data-query-table').html(data);
        }
    });
}

$(document).on("click", ".feedbackchildren_btn, .riskChild_btn", function () {
     var parent_id = $(this).data('id');
     $('input[name="parent_id"]').val(parent_id);
     $('input[name="risk_id"]').val(parent_id);
});

$(document).on('click', '.edit-comment', function() {
    var regex = /<br\s*[\/]?>/gi;
    var content = $(this).attr('data-content').replace(regex, '\n');
    var comment_id = $(this).data('id');

    $('#comment').val(content);
    $('input[name=comment_id]').val(comment_id);
    $('.text-esc').removeClass('hidden');
    $('textarea#comment').focus().css('box-shadow', '0px 1px 1px 1px');
    $('#button-comment-add').text('Save');
});

// not working comment when press esc.
$(document).on('keyup', '#comment', function(e) {
    if (e.keyCode === 27) {
        $(this).val('');
        $("input[name=comment_id]").attr('value', '');
        $('#button-comment-add').text('Add');
        $('#comment').css('box-shadow', 'none');
        $('.text-esc').addClass('hidden');
//        formTaskValid.resetForm();
    }
});

$('#comment').keypress(function (event) {
    if ((event.keyCode === 10 || event.keyCode === 13) && $(this).val() == '') {
        event.preventDefault();
    }
});

// delete comment task.
$(document).on('click', '.delete-comment', function(event) {
    event.preventDefault();
    var comment_id = $(this).data('id');
    var token = $(this).data('token');
    var urlDelComment = $(this).data('url');
    bootbox.confirm({
        message: 'Are you sure delete comment?',
        className: 'modal-default',
        buttons: {
            cancel: {
                label: 'Cancel', className: 'pull-left',
            },
        },
        callback: function(result) {
            if (result) {
                $.ajax({
                    method: 'POST',
                    url: urlDelComment,
                    data: { id: comment_id, _token: token},
                    success: function (response) {
                        var e = jQuery.Event("keypress");
                        e.keyCode = $.ui.keyCode.ENTER;
                        $('input[name="page"]').val(1).trigger(e);
                        $("input[name=comment_id]").attr('value', '');
                        $('#comment').val('');
                        $('#button-comment-add').text('Add');
                        $('#comment').css('box-shadow', 'none');
                        $('.text-esc').addClass('hidden');
                    },
                });
            }
        },
    });

});

// stop event submit when value comment is multi space.
$('#form-task-comment').submit(function(e) {
    if ($.trim($("#comment").val()) === "") {
        $('#button-comment-add').removeAttr('disabled');
        return false;
    }
});
