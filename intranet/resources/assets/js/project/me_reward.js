(function($) {
    var MAX_LEN = 500;

    $('body').on('click', '.input-edit .input-edit-btn', function(e) {
        e.preventDefault();
        var inputEdit = $(this).parent();
        var valueEdit = inputEdit.find('.value-edit');
        var valueView = inputEdit.find('.value-view');
        if (inputEdit.hasClass('edit-show')) {
            return;
        }
        $('.input-edit:not(.error)').removeClass('edit-show');
        inputEdit.addClass('edit-show');
        valueEdit.height(Math.max(valueView.height(), 30)).focus();
    });

    $('body').on('blur', '.input-edit .value-edit', function(e) {
        if ($(this).parent().hasClass('error')) {
            return;
        }
        $(this).parent().removeClass('edit-show');
    });

    $('body').on('change', '.input-edit .value-edit', function(e) {
        var inputEdit = $(this).parent();
        var comment = $(this).val();
        inputEdit.removeClass('error');

        if (comment.length > MAX_LEN) {
            _showStatus(textErrorMaxLen, true);
            inputEdit.addClass('error');
            return;
        }

        inputEdit.removeClass('edit-show');
        inputEdit.find('.value-view').text(comment).removeClass('shortened').shortContent();
    });

    //number format
    $('body').on('change', '.input-number', function() {
        var number = $(this).val();
        if (number) {
            $(this).val($.number(number));
        }
        var oldNum = $(this).attr('data-val') || '0';
        oldNum = oldNum.replace(/,/g, '');
        if (!number) {
            number = '0';
        }
        number = number.replace(/,/g, '');
        caculateTotalReward($(this).closest('td').attr('data-col'), parseInt(number) - parseInt(oldNum));
    });

    //save old value
    $('body').on('focusin', '.input-number', function(e) {
        $(this).attr('data-val', $(this).val());
    });

    //check input not number
    $('body').on('keyup', '.input-number', function(e) {
        if ($.inArray(e.keyCode, [37, 39, 8, 46, 36]) != -1) {
            return;
        }

        $(this).val(function(i, v) {
            return v.replace(/[a-z]|-/gi, '');
        });
    });

    //submit form
    $('.btn-submit-confirm').click(function() {
        var btn = $(this);

        var form = $(this).closest('form');
        var elSubmitData = form.find('.submit-data');
        var isSave = $(this).data('save');
        elSubmitData.html('');
        var inputHtml = '';

        if (!isSave) {
            var filterTeam = $('#rw_team_filter').val();
            var filterMonth = $('#rw_time_filter').val();
            if (!filterTeam || filterTeam == '_all_' || !filterMonth || filterMonth == '_all_') {
                bootbox.alert({
                    message: textErrorSelectTeamMonth,
                    className: 'modal-danger'
                });
                btn.prop('disabled', false);
                return false;
            }
        }

        var hasError = false;
        $('#me_reward_tbl .input-value').each(function() {
            var checkItem = $(this).closest('tr').find('._check_item');
            if (checkItem.is(":checked")) {
                if ($(this).closest('[data-col="email"]').length > 0) {
                    if (!$(this).val()) {
                        bootbox.alert({
                            message: textErrorSelectEmployee,
                            className: 'modal-danger',
                        });
                        hasError = true;
                        return false;
                    }
                }
                if ($(this).hasClass('error')) {
                    btn.prop('disabled', false);
                    hasError = true;
                } else {
                    inputHtml += '<input type="hidden" name="' + $(this).attr('name') + '" value="' + $(this).val() + '" />';
                }
            }
        });

        if (hasError) {
            return false;
        }
        if (inputHtml == '') {
            bootbox.alert({
                message: textErrorNoItemChecked,
                className: 'modal-danger'
            });
            btn.prop('disabled', false);
            return false;
        }
        //add filter time and team
        inputHtml += '<input type="hidden" name="filter_time" value="' + $('#rw_time_filter').val() + '">';
        inputHtml += '<input type="hidden" name="filter_team" value="' + $('#rw_team_filter').val() + '">';
        inputHtml += '<input type="hidden" name="filter_type" value="' + $('#rw_type_filter').val() + '">';

        if (isSave) {
            inputHtml += '<input type="hidden" name="is_save" value="1">';
            elSubmitData.html(inputHtml);
            form.submit();
            return false;
        } else {
            bootbox.confirm({
                message: btn.data('noti'),
                className: 'modal-default',
                callback: function(result) {
                    if (result) {
                        elSubmitData.html(inputHtml);
                        form.submit();
                    } else {
                        btn.prop('disabled', false);
                    }
                }
            });
            return false;
        }
        return false;
    });

    // add jquery funtion short content
    $.fn.shortContent = function(settings) {

        var config = {
            showChars: 40,
            showLines: 2,
            ellipsesText: "...",
            moreText: 'more',
            lessText: 'less'
        };

        if (settings) {
            $.extend(config, settings);
        }

        $(document).off("click", '.morelink');

        $(document).on({
            click: function() {

                var $this = $(this);
                if ($this.hasClass('less')) {
                    $this.removeClass('less');
                    $this.html(config.moreText);
                } else {
                    $this.addClass('less');
                    $this.html(config.lessText);
                }
                $this.parent().prev().toggle();
                $this.prev().toggle();
                return false;
            }
        }, '.morelink');

        return this.each(function() {
            var $this = $(this);
            if ($this.hasClass("shortened"))
                return;

            $this.addClass("shortened");
            var content = $this.html();
            var moreContent = '';
            var arrLine = content.split("\n");
            var c = content,
                h = '';
            var hasMore = false;

            if (arrLine.length > config.showLines) {
                hasMore = true;
                content = arrLine.splice(0, config.showLines).join("\n");
                moreContent = arrLine.join("\n");
            }

            if (content.length > config.showChars) {
                hasMore = true;
                c = content.substr(0, config.showChars);
                h = content.substr(config.showChars, content.length - config.showChars) + moreContent;
            } else {
                c = content;
                h = moreContent;
            }

            if (hasMore) {
                var html = c + '<span class="moreellipses">' + config.ellipsesText + ' </span><span class="morecontent"><span>' + h + '</span> <a href="#" class="morelink">' + config.moreText + '</a></span>';
                $this.html(html);
                $(".morecontent span").hide();
            }
            $this.removeClass('hidden');
        });

    };

    $('.input-edit .value-view').shortContent();

    $('body').on('click', '.close_alert', function(e) {
        e.preventDefault();
        $(this).parent().removeClass('show');
    });

    //draft function
    $('#btn_export_excel').click(function(e) {
        e.preventDefault();
        var team_id = $('#rw_team_filter').val();
        var time = $('#rw_time_filter').val();

        if (!time) {
            _showStatus(textRequiredTeamAndTime, true);
            return;
        }

        $(this).prop('disabled', true);
        var btn = $(this);
        btn.find('.fa-spin').removeClass('hidden');

        $.ajax({
            url: $(this).data('url'),
            type: 'GET',
            dataType: 'json',
            data: {
                team_id: team_id,
                time: time
            },
            success: function(result) {
                if (result.length < 1) {
                    var modalNoti = $('#modal-warning-notification');
                    modalNoti.find('.text-default').text('No data!');
                    modalNoti.modal('show');
                    return;
                }
                var data = filterDataExport(result);
                var uri = $("#export_excel").excelexportjs({
                    containerid: 'export_excel',
                    datatype: 'json',
                    dataset: data,
                    returnUri: true,
                    columns: getColumns(paramsColumn)
                });

                var link = document.createElement('a');
                link.href = uri;
                var date = new Date();
                var dateMonth = date.getMonth() + 1;
                dateMonth = (dateMonth < 10) ? '0' + dateMonth : dateMonth;
                var dateVer = '-date-' + date.getDate() + '-' + dateMonth + '-' + date.getFullYear();
                var fileName = 'ME-Reward-' + filterMonthFormat + dateVer;
                link.download = fileName + '.xls';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function(error) {
                _showStatus(error.responseJSON, true);
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.find('.fa-spin').addClass('hidden');
            }
        });
    });

    /** reward report checkbox all **/
    $('#input-export-reward-all').click(function() {
        var checkedItem = document.getElementById('input-export-reward-all').checked;
        $('.input-export-reward, .input-export-reward-team').prop('checked', checkedItem);
    });
    /** end checkbox all **/

    /**
     * Checkbox item click event
     * Set checkbox all checked or not
     */
    $('body').on('click', '.input-export-reward, .input-export-reward-team', function() {
        var countCheckboxItem = $('.input-export-reward').length + $('.input-export-reward-team').length;
        var countCheckboxItemChecked = $('.input-export-reward').filter(':checked').length + $('.input-export-reward-team').filter(':checked').length;
        $('#input-export-reward-all').prop('checked', countCheckboxItem == countCheckboxItemChecked);
    });
    /** end checkbox item click **/

    //export osdc and base reward
    $(document).on('click', '#btn_export_base_osdc', function(e) {
        e.preventDefault();
        var time = $('#rw_time_filter').val();
        var group = $('#rw_team_filter').val();
        var type = $('#rw_type_filter').val();
        var employee = $('#rw_employee_filter').val();
        var projectName = $('#rw_projectname_filter').val();
        var statusPaid = $('#rw_status_paid_filter').val();
        if (!time) {
            _showStatus(textRequiredTeamAndTime, true);
            return;
        }

        $(this).prop('disabled', true);
        var btn = $(this);
        btn.find('.fa-spin').removeClass('hidden');
        var projectIds = [];
        var teamIds = [];
        var checkedItem = $('.input-export-reward:checked');
        var checkedItemTeam = $('.input-export-reward-team:checked');
        $(checkedItem).each(function() {
            projectIds.push($(this).val());
        });
        $(checkedItemTeam).each(function() {
            teamIds.push($(this).val());
        });
        $.ajax({
            url: $(this).data('url'),
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('meta[name="_token"]').attr('content'),
                'project_ids[]': projectIds,
                'teamIds[]': teamIds,
                time: time,
                group: group,
                type: type,
                pm: employee,
                projName: projectName,
                statusPaid: statusPaid,
            },
            success: function(result) {
                if (result.length < 1) {
                    var modalNoti = $('#modal-warning-notification');
                    modalNoti.find('.text-default').text('No data!');
                    modalNoti.modal('show');
                    return;
                }

                var templates = result;
                var wb = { SheetNames: [], Sheets: {} };
                $('#tbl_template').html(templates);
                var table = $('#tbl_template table')[0];
                var wsheet = XLSX.utils.table_to_book_str(table, {
                    cellStyles: true,
                    cellDates: false,
                    cellFormula: true,
                }).Sheets.Sheet1;
                wsheet['!cols'] = [
                    { wch: 20 },
                    { wch: 15 },
                    { wch: 20 },
                    { wch: 15 },
                    { wch: 35 },
                    { wch: 30 },
                    { wch: 15 },
                ];

                wb.SheetNames.push('Reward-' + time);
                wb.Sheets['Reward-' + time] = wsheet;

                var wbout = XLSX.write(wb, { bookType: 'xlsx', bookSST: true, type: 'binary' });
                var fname = 'Reward-' + time + '.xlsx';
                try {
                    saveAs(new Blob([s2ab(wbout)], { type: "application/octet-stream" }), fname);
                } catch (ex) {
                    //error
                    return;
                }
                $('#tbl_template').html('');
            },
            error: function(error) {
                _showStatus(error.responseJSON, true);
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.find('.fa-spin').addClass('hidden');
            }
        });
    });

    function s2ab(s) {
        if (typeof ArrayBuffer !== 'undefined') {
            var buf = new ArrayBuffer(s.length);
            var view = new Uint8Array(buf);
            for (var i = 0; i !== s.length; ++i) {
                view[i] = s.charCodeAt(i) & 0xFF;
            }
            return buf;
        } else {
            var buf = [];
            for (var i = 0; i !== s.length; ++i) {
                buf[i] = s.charCodeAt(i) & 0xFF;
            }
            return buf;
        }
    }

    $('.btn-reset-filter').on('click', function() {
        $('#rw_time_filter').attr('autocomplate', 'off');
        resetUrl();
    });

    $('.filter-grid').on('change', function() {
        resetUrl();
    });
    //reset params url
    function resetUrl() {
        var location = window.location;
        window.history.pushState({}, document.title, location.origin + location.pathname);
    }

    $('body').on('change', '._check_all', function() {
        var table = $(this).closest('.fixed-table-container').find('table');
        table.find('._check_item').prop('checked', $(this).is(":checked")).trigger('change');
    });

    $('body').on('change', '._check_item', function() {
        var itemLength = $('#me_reward_tbl ._check_item').length;
        var table = $(this).closest('.table');
        table.find('._check_all').prop('checked', table.find('._check_item:checked').length === itemLength);
        var trId = $(this).closest('tr').attr('data-id');
        $('#me_reward_tbl tr[data-id="' + trId + '"] td ._check_item')
            .prop('checked', $(this).is(':checked'));
    });

    $('.td-histories').each(function() {
        var dataHistories = $(this).data('histories');
        if (dataHistories) {
            var htmlHistories = '<div><strong>Histories</strong></div>';
            htmlHistories += '<ul class="list-histories">';
            for (var i = 0; i < dataHistories.length; i++) {
                var hItem = dataHistories[i];
                var account = (typeof hItem.account != 'undefined') ? '<strong>' + hItem.account + '</strong><br/>' : '';
                htmlHistories += '<li>' + account + ' Changed <strong>' + $.number(hItem.number) + '</strong> at ' + hItem.time + '</li>';
            }
            htmlHistories += '</ul>';
        }
        $(this).find('.icon-history').attr('title', htmlHistories);
        if (typeof $.fn.qtip != 'undefined') {
            $(this).find('.icon-history').qtip({
                position: {
                    my: 'bottom center',
                    at: 'top center'
                },
                style: {
                    classes: 'qtip-dark'
                }
            });
        }
    });

    // cal reward report
    $('.tbl-reward-report td[data-reward-col="approve"]').each(function(i, v) {
        var rewardApprove = $(v).data('reward'),
            domTeam = $(v).siblings('td[data-reward-col="team"]');
        if (!domTeam.length) {
            $(v).html($.number(rewardApprove));
            return true;
        }
        var lengthTeam = domTeam.text().split(',').length || 1;
        rewardApprove /= lengthTeam;
        $(v).html($.number(rewardApprove));
    });

    //on change check item
    $('body').on('change', '.fixed-table-container ._check_item, .fixed-table-container ._check_all', function() {
        disablePaidButton();
    });

    //init disable button
    setTimeout(function() {
        disablePaidButton();
    }, 200);

    //disable button unpaid, paid
    function disablePaidButton() {
        var checkItems = $('.fixed-table-container table ._check_item:checked');
        var countPaid = 0,
            countUnpaid = 0;
        checkItems.each(function() {
            var tr = $(this).closest('tr').attr('data-id');
            var paidItem = $(this).closest('.fixed-table-container')
                .find('table tr[data-id="' + tr + '"] .reward-approved');
            if (paidItem.length > 0) {
                if (paidItem.data('status') == STATE_PAID) {
                    countPaid++;
                } else {
                    countUnpaid++;
                }
            }
        });

        $('.btn-submit-confirm').prop('disabled', checkItems.length < 1);
        $('.btn-submit-unpaid').prop('disabled', countPaid < 1);
        $('.btn-submit-paid').prop('disabled', countUnpaid < 1);
    }

    //on form change paid submit
    $('form.reward_change_paid').on('submit', function() {
        var itemChecked = $('#me_reward_tbl ._check_item:checked');
        var btn = $(this).find('button[type="submit"]');

        var filterTeam = $('#rw_team_filter').val();
        var filterMonth = $('#rw_time_filter').val();
        if (!filterTeam || filterTeam == '_all_' || !filterMonth || filterMonth == '_all_') {
            bootbox.alert({
                message: textErrorSelectTeamMonth,
                className: 'modal-danger'
            });
            btn.prop('disabled', false);
            return false;
        }

        var evalIds = [];
        itemChecked.each(function() {
            evalIds.push('<input type="hidden" name="eval_ids[]" value="' + $(this).val() + '">');
        });
        if (evalIds.length < 1) {
            bootbox.alert({
                message: textErrorNoItemChecked,
                className: 'modal-warning'
            });
            btn.prop('disabled', false);
            return false;
        }

        //add filter time and team
        evalIds.push('<input type="hidden" name="filter_time" value="' + filterMonth + '">');
        evalIds.push('<input type="hidden" name="filter_team" value="' + filterTeam + '">');

        var form = $(this).closest('form');
        form.find('.item-eval-ids').html(evalIds.join(' '));
        return true;
    });

    //update paid status ajax
    function updateStatusPaid(evalIds, status, successFunc, errorFunc) {
        $.ajax({
            type: 'POST',
            url: urlUpdateStatusPaid,
            data: {
                _token: _token,
                eval_ids: evalIds,
                status: (status ? 1 : 0)
            },
            success: function(result) {
                successFunc();
                bootbox.alert({
                    message: result.message,
                    className: 'modal-success'
                });
            },
            error: function(error) {
                errorFunc()
                bootbox.alert({
                    message: error.responseJSON.message,
                    className: 'modal-danger'
                });
            }
        });
    }

    $(document).ready(function() {
        //init select 2 + qtip tooltip
        setTimeout(function() {
            $('.fixed-table thead select').select2();
            if (typeof $.fn.qtip != 'undefined') {
                $('.el-qtip').qtip({
                    position: {
                        my: 'bottom center',
                        at: 'top center',
                    },
                    style: {
                        classes: 'qtip-dark',
                    },
                });
            }
        }, 300);

    });

    //loadmore reward script
    $('body').on('click', '#btn_loadmore_reward', function(e) {
        e.preventDefault();
        var _this = $(this);
        if (_this.hasClass('loading')) {
            return;
        }
        var url = _this.attr('data-url');
        if (!url) {
            return;
        }
        var iconLoading = _this.find('.icon-loading');
        iconLoading.removeClass('hidden');
        _this.addClass('loading');
        $.ajax({
            type: 'GET',
            url: url,
            data: {
                index: $('.tbl-reward-report tbody tr').length,
                monthFilter: filterMonthFormat,
            },
            success: function(result) {
                $('.tbl-reward-report tbody').append(result.htmlContent);
                _this.attr('data-url', result.nextPageUrl);
                if (!result.nextPageUrl) {
                    _this.closest('tfoot').addClass('hidden');
                }
            },
            complete: function() {
                _this.removeClass('loading');
                iconLoading.addClass('hidden');
            },
        });
    });

    $('body').on('click', '#add_item_btn', function(e) {
        e.preventDefault();
        var tableItem = $('#me_reward_tbl tbody');
        var tplItem = $('#rw_table_template tbody tr:first').clone();
        var colsNA = ['proj_name', 'me_status', 'me_contribute', 'norm', 'effort', 'reward_suggest'];
        for (var i in colsNA) {
            tplItem.find('[data-col="' + colsNA[i] + '"]').text('N/A');
        }
        var colsReset = ['employee_code', 'email', 'reward_submit', 'comment', 'reward_approve', 'reward_status', 'is_paid'];
        for (var i in colsReset) {
            var col = tplItem.find('[data-col="' + colsReset[i] + '"]');
            if (col.find('input').length > 0 || col.find('textarea').length > 0) {
                col.find('input').val('');
                col.find('textarea').val('');
            } else {
                col.html('');
            }
            col.find('.value-view').text('').removeClass('shortened');
        }
        var prevItem = tableItem.find('tr:not(.row-no-result):last');
        var currId;
        if (prevItem.length < 1) {
            currId = 1;
        } else {
            var tplId = prevItem.attr('data-id');
            if ($.isNumeric(tplId)) {
                currId = 1;
            } else {
                currId = parseInt(prevItem.attr('data-new')) + 1;
            }
        }
        tplItem.attr('data-id', 'new_' + currId).attr('data-new', currId);
        tplItem.find('[name]').each(function() {
            var oldName = $(this).attr('name');
            var newName = oldName.replace('new_id', 'new_' + currId);
            $(this).attr('name', newName);
        });
        tplItem.find('[data-col="reward_submit"]')
            .html('<input type="text" data-min="0" data-max="" name="rewards[new_' + currId + '][submit]" class="form-control input-value input-number">');
        tplItem.find('[data-col="comment"]')
            .html('<div class="input-edit" data-eval="new_' + currId + '">' +
                '<span class="value-view shortened"></span>' +
                '<textarea name="rewards[new_' + currId + '][comment]" class="form-control input-value value-edit"></textarea>' +
                '<button type="button" class="btn btn-sm btn-success input-edit-btn"><i class="fa fa-edit"></i></button>' +
                '</div>');
        tplItem.find('.td-check ._check_item').prop('checked', false).val('new_' + currId);
        tplItem.find('[data-col="employee_code"]').addClass('has-del')
            .prepend('<button type="button" class="btn btn-danger btn-sm rw-del-btn"><i class="fa fa-trash"></i></button>');
        //remove histories
        tplItem.find('.td-histories').removeAttr('data-histories');
        tplItem.find('.td-histories .icon-history').remove();
        //employee
        tplItem.find('[data-col="email"]').html('<select class="form-control input-value select-search"' +
            'data-remote-url="' + urlSearchEmployee + '?team_id=' + $('#rw_team_filter').val() + '"' +
            'name="rewards[new_' + currId + '][employee_id]"></select>');
        var textType = 'N/A';
        if ($('#rw_type_filter').val() != '_all_') {
            textType = $('#rw_type_filter option:selected').text();
        }
        tplItem.find('[data-col="proj_type"]').text(textType);

        tableItem.find('.row-no-result').remove();
        tplItem.appendTo(tableItem);
        tplItem.find('.td-check ._check_item').trigger('click');
        RKfuncion.select2.init({}, tplItem.find('[data-col="email"] .select-search'));
        var fixedCols = $('.fixed-table thead tr:first .td-fixed').length;
        $(".fixed-table").tableHeadFixer({ "left": fixedCols });
    });

    $('body').on('change', '[data-col="email"] select', function() {
        var employeeId = $(this).val();
        if (!employeeId) {
            return;
        }
        var elThis = $(this);

        $.ajax({
            type: 'GET',
            url: urlEmployeeInfor,
            data: {
                employee_id: employeeId,
                'cols[]': 'employee_code'
            },
            success: function(employee) {
                var colEmp = elThis.closest('tr').find('[data-col="employee_code"]');
                if (colEmp.find('.value').length < 1) {
                    colEmp.append('<span class="value"></span>');
                }
                colEmp.find('.value').text(employee.employee_code);
            },
            error: function() {
                elThis.val('').trigger('change');
            },
        });
    });

    $('body').on('click', '.rw-del-btn', function(e) {
        e.preventDefault();
        var btn = $(this);
        if (btn.is(':disabled')) {
            return;
        }
        var trItem = btn.closest('tr');
        var itemId = trItem.attr('data-id');
        if (!$.isNumeric(itemId)) {
            trItem.remove();
            caculateTotalReward();
        } else {
            bootbox.confirm({
                className: 'modal-danger',
                message: textConfirmDelete,
                callback: function(result) {
                    if (result) {
                        btn.prop('disabled', true);
                        $.ajax({
                            type: 'DELETE',
                            url: urlDeleteItem,
                            data: {
                                _token: _token,
                                id: itemId,
                            },
                            success: function(data) {
                                trItem.remove();
                                bootbox.alert({
                                    className: 'modal-success',
                                    message: data.message,
                                });
                                caculateTotalReward();
                            },
                            error: function(error) {
                                bootbox.alert({
                                    className: 'modal-danger',
                                    message: error.responseJSON.message,
                                });
                            },
                            complete: function() {
                                btn.prop('disabled', false);
                            },
                        });
                    }
                },
            });
        }
    });

    //caculate total reward
    var rewardCols = ['norm', 'reward_suggest', 'reward_submit', 'reward_approve'];
    setTimeout(function() {
        if (typeof hasMorePages !== 'undefined' && hasMorePages) {
            $.ajax({
                type: 'GET',
                url: urlGetTotalReward,
                data: {
                    hasBtnSubmit: hasBtnSubmit,
                    isReview: isReview,
                    routeName: routeName,
                    team_id: team_id,
                    time: filterMonth
                },
                success: function(res) {
                    $.each(res, function(key, total) {
                        $('#me_reward_tbl tfoot .total-' + key).text(total);
                    });
                },
                error: function(error) {
                    $.each(rewardCols, function(key, colName) {
                        $('#me_reward_tbl tfoot .total-' + colName).text('...').addClass('error');
                    });
                },
            });
        } else {
            caculateTotalReward();
        }
    }, 500);

    //caculate total reward
    function caculateTotalReward(colName, changedNum) {
        if (typeof colName != 'undefined') {
            rewardCols = [colName];
            if (typeof changedNum != 'undefined') {
                var elTotalCol = $('#me_reward_tbl tfoot .total-' + colName);
                var currColTotal = elTotalCol.text();
                if (currColTotal) {
                    currColTotal = parseInt(currColTotal.replace(/,/g, ''));
                    console.log(currColTotal, changedNum);
                } else {
                    currColTotal = 0;
                }
                elTotalCol.text($.number(currColTotal + changedNum));
                return;
            }
        }
        var totalRewards = {};
        for (var iCol = 0; iCol < rewardCols.length; iCol++) {
            var rwCol = rewardCols[iCol];
            if (typeof totalRewards[rwCol] == 'undefined') {
                totalRewards[rwCol] = 0;
            }
            $('#me_reward_tbl tbody [data-col="' + rwCol + '"]').each(function() {
                var rwNum = 0;
                var elInput = $(this).find('.input-value');
                if (elInput.length > 0) {
                    rwNum = elInput.val().trim();
                } else {
                    rwNum = $(this).text().trim();
                }
                if (!rwNum || rwNum == 'N/A') {
                    rwNum = 0;
                } else {
                    rwNum = rwNum.replace(/,/g, '');
                }
                totalRewards[rwCol] += parseInt(rwNum);
            });
            $('#me_reward_tbl tfoot .total-' + rwCol).text($.number(totalRewards[rwCol]));
        }
    }

    $('body').on('contextmenu', '[data-col="comment"]', function(e) {
        e.preventDefault();
        return false;
    });

    //load me comment
    var xhrLoadComment = null;
    $('body').on('mousedown', '[data-col="comment"]', function(e) {
        if (e.which != 3) {
            return;
        }
        var elThis = $(this);
        var elPopup = $('#me_comment_modal');
        var elCmList = elPopup.find('.me_comments_list');
        elCmList.html('');
        setTimeout(function() {
            elPopup.removeClass('hidden');
            var elParent = $('.content-container .box:first');
            var elOffset = getOffsetParent(elThis, elParent);
            var px = elOffset.left;
            var py = elOffset.top;
            if (px + elPopup.width() + elThis.width() + 10 >= elParent.width()) {
                px = px - elPopup.width();
            } else {
                px = px + elThis.width() + 10;
            }
            elPopup.css('top', py + 'px').css('left', px + 'px');
        }, 200);

        if (xhrLoadComment) {
            xhrLoadComment.abort();
        }

        var evalId = elThis.closest('tr').attr('data-id');
        var elLoading = elPopup.find('._loading');
        var elNoComment = elPopup.find('._no_comment');
        var elError = elPopup.find('._error');

        elLoading.removeClass('hidden');
        elError.addClass('hidden');
        xhrLoadComment = $.ajax({
            type: 'GET',
            url: urlLoadEvalComments,
            data: {
                id: evalId,
            },
            success: function(res) {
                if (res.length < 1) {
                    elNoComment.removeClass('hidden');
                } else {
                    elNoComment.addClass('hidden');
                    for (var i = 0; i < res.length; i++) {
                        var item = res[i];
                        appendRwComment(elCmList, item);
                    }
                }
            },
            error: function(error) {
                elError.text(error.responseJson.message).removeClass('hidden');
            },
            complete: function() {
                elLoading.addClass('hidden');
            },
        });
    });

    $('body').on('click', '#me_comment_modal .close, #me_comment_modal .cancel-btn', function() {
        $('#me_comment_modal').addClass('hidden').css('top', 'auto').css('left', 'auto');
        $('#me_comment_modal').find('.me_comments_list').html('');
    });

    function appendRwComment(elCmList, item) {
        var elItem = $('#rw_comment_item_tpl').find('.comment_item').clone();
        elItem.addClass(item.type_class);
        if (item.comment_type == CM_TYPE_LATE_TIME) {
            elItem.find('._comment_avatar').attr('src', '/common/images/login-r.png');
            elItem.find('._comment_name').text('System');
        } else {
            elItem.find('._comment_avatar').attr('src', item.avatar_url);
            elItem.find('._comment_name').text(item.name + ' (' + RKfuncion.general.getNickName(item.email) + ')');
        }
        elItem.find('.comment-attr').text(item.attr_label ? item.attr_label : textNote);
        elItem.find('.date').text(item.created_at);
        elItem.find('.comment_content').text(item.content);
        elItem.appendTo(elCmList);
    }

    //export ME osdc reward
    $('#btn_export_me_reward').click(function(e) {
        e.preventDefault();
        var form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', $(this).data('url'));
        var isExportAll = parseInt($('#modal_export_me_reward [name="is_all"]:checked').val());
        var params = {
            _token: siteConfigGlobal.token,
            is_all: isExportAll,
            month: $('#rw_time_filter').val(),
            url_filter: window.location.origin + window.location.pathname,
        };
        params = $.extend(params, RKfuncion.general.paramsFromUrl());
        if (!isExportAll) {
            var checkedItems = $('#me_reward_tbl ._check_item:checked');
            if (checkedItems.length < 1) {
                bootbox.alert({
                    className: 'modal-danger',
                    message: textErrorNoItemChecked,
                });
                return false;
            }
            checkedItems.each(function(index) {
                params['ids[' + index + ']'] = $(this).val();
            });
        }

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
    });

})(jQuery);

var _me_status = $('#_me_alert');
var text_error_occurred = 'Error!';

function _setStatus(message, error) {
    if (typeof error == "undefined") {
        error = false;
    }
    if (error) {
        _me_status.addClass('me_error');
    } else {
        _me_status.removeClass('me_error');
    }
    if (error) {
        message += '<a class="close close_alert" href="javascript:void(0)">×</a>';
    }
    _me_status.html(message).addClass('show');
}
var _showTimeout;

function _showStatus(message, error) {
    clearTimeout(_showTimeout);
    if (typeof error == "undefined") {
        error = false;
    }
    if (typeof message == "undefined") {
        message = text_error_occurred;
    }
    _setStatus(message, error);

    if (!error) {
        _showTimeout = setTimeout(function() {
            _me_status.removeClass('show');
        }, 3000);
    }
}

function _hideStatus() {
    clearTimeout(_showTimeout);
    _me_status.removeClass('show');
}

function getOffsetParent(elChild, elParent) {
    var childPos = elChild.offset();
    var parentPos = elParent.offset();
    return {
        top: childPos.top - parentPos.top,
        left: childPos.left - parentPos.left,
    };
}

$(function() {
    $('body').on('contextmenu', '[data-col="norm"]', function(e) {
        e.preventDefault();
        return false;
    });
    
    $('body').on('contextmenu', '[data-view_note="view-note"]', function(e) {
        e.preventDefault();
        return false;
    });

    $('body').on('mousedown', '[data-view_note="view-note"]', function(e) {
        if (e.which != 3) {
            return;
        }
        var elThis = $(this);
        var elPopup = $('#me_allowance_onsites_modal');
        var elCmList = elPopup.find('.me_comments_list');
        elCmList.html('');
        var noteAllowance = elThis.find('.note-allowance-onsite ul').html();
        setTimeout(function() {
            elPopup.removeClass('hidden');
            var elParent = $('.content-container .box:first');
            var elOffset = getOffsetParent(elThis, elParent);
            var px = elOffset.left;
            var py = elOffset.top;
            if (px + elPopup.width() + elThis.width() + 10 >= elParent.width()) {
                px = px - elPopup.width();
            } else {
                px = px + elThis.width() + 10;
            }
            elPopup.css('top', py + 'px').css('left', px + 'px');
            elCmList.html(noteAllowance);
        }, 200);

    })
})
$('body').on ('click','#me_allowance_onsites_modal .close', function(e) {
    $('#me_allowance_onsites_modal').addClass('hidden').css('top', 'auto').css('left', 'auto');
    $('#me_allowance_onsites_modal').find('.me_comments_list').html('');
})
