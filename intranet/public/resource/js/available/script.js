(function ($) {

    RKfuncion.select2.init();
    RKfuncion.bootstapMultiSelect.init();

    $('.td-lists li strong').shortedContent({
        showChars: 30,
        showLines: 3,
        ellipsesText: "...",
        moreText: '',
        lessText: '',
    });

    $('.date-filter').datetimepicker({
        format: 'YYYY-MM-DD',
        useCurrent: false,
        showClear: true,
    });

    $('.filter-grid, .select-grid').on('change', function (e) {
        return false;
    });

    $('.btn-search-filter').click(function (e) {
        $('.search-box .error').addClass('hidden');
        var error = false;
        var toDate = $('#to_date').val();
        var fromDate = $('#from_date').val();
        if (toDate && fromDate && toDate < fromDate) {
            $('#to_date_error').removeClass('hidden');
            error = true;
        }
        if (error) {
            return false;
        }
    });

    $('body').on('click', '.btn-add-filter-item', function (e) {
        e.preventDefault();
        var btn = $(this);
        var tplItem = $(btn.data('template') + ' tbody');
        var listFilter = btn.closest('.list-filter-items').find('tbody');
        listFilter.append(tplItem.html());
        initSearchParams(listFilter);
        filterInitSelect2(listFilter.find('tr:last select'));
    });

    $('body').on('click', '.btn-del-filter-item', function (e) {
        var trItem = $(this).closest('tr');
        trItem.remove();
    });

    /**
     * init filter select2
     * @param {jQuery element} $elements 'select'
     */
    function filterInitSelect2($elements) {
        $elements.each(function () {
            var options = {width: '100%'};
            if (!$(this).hasClass('has-search')) {
                options.minimumResultsForSearch = Infinity;
            }
            $(this).select2(options);
        });
    };

    /**
     * init search params filter
     * @param {jQuery element} $elFilters 'table or tbody'
     */
    function initSearchParams($elFilters) {
        var filterName = $elFilters.closest('table').attr('data-name');
        $elFilters.find('tr.tr-search').each(function (trIndex) {
            var tr = $(this);
            tr.find('.field').each(function () {
                var name = filterName + '['+ trIndex +'][' + $(this).attr('data-name') + ']';
                $(this).attr('name', name);
            });
        });
    }

    $('.full-projs-btn').click(function (e) {
        var employeeId = $(this).closest('tr').attr('data-employee');
        var modal = $($(this).attr('href'));
        var url = modal.data('url');
        if (!url || !employeeId) {
            return;
        }
        var loading = modal.find('.tr-loading');
        if (!loading.hasClass('hidden')) {
            return;
        }
        var notFound = modal.find('.tr-not-found');
        var tblBody = modal.find('table tbody');
        tblBody.find('tr:not(.tr-loading)').remove();
        loading.removeClass('hidden');
        notFound.addClass('hidden');
        $.ajax({
            type: 'GET',
            url: url,
            data: {
                employee_id: employeeId,
            },
            success: function (response) {
                if (response.length > 0) {
                    for (var i = 0; i < response.length; i++) {
                        var item = response[i];
                        tblBody.append('<tr>' +
                                '<td>'+ (i + 1) +'</td>' +
                                '<td>'+ item.name +'</td>' +
                                '<td>'+ item.start_date +'</td>' +
                                '<td>'+ item.end_date +'</td>' +
                                '<td>'+ parseFloat(item.effort).toFixed(0) +'%</td>' +
                        '</tr>');
                    }
                } else {
                    notFound.removeClass('hidden');
                }
            },
            error: function (error) {
                tblBody.append('<tr><td colspan="5" class="error text-center">'+ error.responseJSON +'</td></tr>');
            },
            complete: function () {
                loading.addClass('hidden');
            },
        });
    });

    $('body').on('click', '.note-edit-btn', function (e) {
        e.preventDefault();
        var noteItem = $(this).closest('.note-item');
        var noteShow = noteItem.find('.note-show');
        var noteEdit = noteItem.find('.note-edit');
        if (noteShow.hasClass('hidden')) {
            noteShow.removeClass('hidden');
            noteEdit.addClass('hidden');
        } else {
            noteShow.addClass('hidden');
            noteEdit.removeClass('hidden');
        }
    });

    $('body').on('change', '.note-item .note-edit', function () {
        var noteEdit = $(this);
        var noteItem = $(this).closest('.note-item');
        var noteError = noteItem.find('.note-error');
        noteError.text('').addClass('hidden');
        var loading = noteItem.find('.loading');
        if (!loading.hasClass('hidden')) {
            return;
        }
        var noteContent = noteEdit.val();
        if (noteContent.trim().length > 500) {
            noteError.text(textErrorMaxLength).removeClass('hidden');
            return;
        }
        var noteShow = noteItem.find('.note-show');
        var employeeId = $(this).closest('tr').data('employee');
        loading.removeClass('hidden');
        $.ajax({
            type: 'POST',
            url: saveNoteUrl,
            data: {
                employee_id: employeeId,
                note: noteContent,
                _token: siteConfigGlobal.token,
            },
            success: function (result) {
                if (result.delete) {
                    noteItem.find('.note-name').text('');
                    noteItem.addClass('note-current');
                } else {
                    noteItem.find('.note-name').text(result.name + ': ');
                    noteItem.removeClass('note-current');
                }
                noteShow.text(result.note).removeClass('hidden shortened').shortedContent();
                noteEdit.addClass('hidden');
            },
            error: function (error) {
                noteError.text(error.responseJSON).removeClass('hidden');
            },
            complete: function () {
                loading.addClass('hidden');
            },
        });
    });

    $('.btn-add-task').click(function (e) {
        e.preventDefault();
        var btn = $(this);
        var modal = $('#add_task_modal');
        var tr = btn.closest('tr');
        var empCol = tr.find('.emp-col');
        var form = $('#form_add_task');
        var employeeId = tr.attr('data-employee');
        var taskId = btn.attr('data-task');
        //set form action
        var formAction = saveTaskUrl;
        var loadTaskUrl = addTaskUrl;
        if (typeof taskId != 'undefined') {
            formAction = saveTaskUrl + '/' + taskId;
            loadTaskUrl = btn.attr('title');
        }
        //loading
        $('#form_add_task .form-content').html('<p class="text-center"><i class="fa fa-spin fa-refresh"></i></p>');
        $.ajax({
            type: 'GET',
            url: loadTaskUrl,
            data: {},
            success: function (data) {
                $('#form_add_task .form-content').html(data.html);
                initRenderLoadedTask();
                //init new
                if (typeof taskId == 'undefined') {
                    //set attribute
                    form.find('[name="task[title]"]').val(textNewTaskTitle + ' - ' + empCol.find('span').text());
                    form.find('[name="task[content]"]').val(empCol.find('a').text() + "\r\n" + empCol.find('a').attr('href'));
                    form.find('[name="task_assign[]"]').html('<option value="'+ currEmpId +'" selected>'+ currEmpAcc +'</option>').trigger('change');
                }
            },
        });

        form.find('[name="employee_id"]').val(employeeId);
        form.attr('data-employee', employeeId);
        form.attr('action', formAction);
        //show modal
        modal.modal('show');
    });

    function initRenderLoadedTask() {
        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        jQuery.extend(jQuery.validator.messages, {
            required: requiredText,
        });
        //init validate
        $('#form_add_task').validate({
            rules: {
                'task_assign[]': {
                    required: true,
                },
                'task[title]': {
                    required: true,
                },
                'task[duedate]': {
                    required: true,
                },
                'task[status]': {
                    required: true,
                },
                'task[content]': {
                    required: true,
                },
            }
        });
        //init select2
        RKfuncion.select2.init();
    }

    //checked item
    var keyCheckedEmp = 'employee_available_checked';
    var checkedEmpIds = RKSession.getRawItem(keyCheckedEmp);

    $('.check-all').click(function () {
        var items = $(this).closest('table').find('.check-item');
        items.prop('checked', $(this).is(':checked'));
        items.each(function () {
            updateCheckedEmpIds($(this));
        });
    });
    $('.check-item').click(function () {
        var table = $(this).closest('table');
        table.find('.check-all').prop('checked', table.find('.check-item:checked').length === table.find('.check-item').length);
        updateCheckedEmpIds($(this));
    });

    /*
     * update checked ids
     */
    function updateCheckedEmpIds(elItem) {
        var id = elItem.val();
        var idIndex = checkedEmpIds.indexOf(id);
        if (elItem.is(':checked')) {
             if (idIndex < 0) {
                 checkedEmpIds.push(id);
             }
        } else {
            if (idIndex > -1) {
                checkedEmpIds.splice(idIndex, 1);
            }
        }
        RKSession.setRawItem(keyCheckedEmp, checkedEmpIds);
    }

    $('#btn_export_result').click(function () {
        var btn = $(this);
        var form = btn.closest('form');
        form.find('[name="employee_ids"]').val(checkedEmpIds.join('-'));
        form.submit();
        setTimeout(function () {
            btn.prop('disabled', false);
        }, 3000);
        return false;
    });

    $('#btn_reset_checkbox').click(function () {
        $('.check-all').prop('checked', false);
        $('.check-item').prop('checked', false);
        checkedEmpIds = [];
        RKSession.setRawItem(keyCheckedEmp, checkedEmpIds);
        $('#form_export [name="employee_ids"]').val('');
    });

    $('#btn_update_data').click(function (e) {
        e.preventDefault();
        var btn = $(this);
        var url = btn.data('url');
        var loading = btn.find('i');
        if (!url || btn.is(':disabled')) {
            return;
        }

        btn.prop('disabled', true);
        loading.removeClass('hidden');
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                _token: siteConfigGlobal.token
            },
            success: function (data) {
                window.location.reload();
            },
            error: function (error) {
                bootbox.alert({
                    message: error.responseJSON,
                    className: 'modal-danger'
                });
            },
            complete: function () {
                btn.prop('disabled', false);
                loading.addClass('hidden');
            }
        });
    });

    $(document).ready(function () {
        if (checkedEmpIds) {
            for (var i = 0; i < checkedEmpIds.length; i++) {
                var id = checkedEmpIds[i];
                $('.check-item[value="'+ id +'"]').prop('checked', true);
            }
        }

        $('.list-filter-items').each(function () {
            var list = $(this);
            if (list.find('tbody tr.tr-search').length < 1) {
                list.find('.btn-add-filter-item').trigger('click');
            } else {
                initSearchParams(list.find('tbody'));
                filterInitSelect2(list.find('tbody tr select'));
            }
        });

        $('.note-item .note-show').each(function () {
            $(this).shortedContent();
            $(this).removeClass('hidden');
        });
    });

})(jQuery);

RKfuncion.formSubmitAjax['employeeTaskSuccess'] = function(dom, data) {
    if (typeof dom.attr('data-employee') != 'undefined') {
        var employeeId = dom.attr('data-employee');
        $('#table_employees tr[data-employee="'+ employeeId +'"] .btn-add-task')
                .attr('data-task', data.task_id)
                .attr('title', data.task_link)
                .html('<i class="fa fa-edit"></i> ' + textEditTask);
        dom.closest('.modal').modal('hide');
    }
};
