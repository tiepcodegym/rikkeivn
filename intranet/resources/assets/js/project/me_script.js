var _processing = false;
var _me_table = $('#_me_table');
var _me_tbl_res = $('._me_table_responsive');
var _me_status = $('#_me_alert');
var _form_assign = $('#_me_assignee_form');
var _status_content = $('#_status_content');
var _modal_success = $('#modal-success-notification');
var _modal_error = $('#modal-warning-notification');
var _modal_confirm = $('#_modal_confirm');
var GR_PERFORM = 2;
var GR_NORMAL = 1;

(function ($) {
    
    if (_error_text) {
        showModalError(_error_text);
    }
    
    var _urlPath = window.location.origin + window.location.pathname;
    
    $('#_me_month').change(function () {
       $(this).closest('form').attr('data-change', 1); 
       $(this).closest('form').submit();
       pushStateUrl();
    });
    
    if (curr_team_id) {
        $('#_me_team').val(curr_team_id);
    }
    
    $('#_me_team').change(function () {
        if (!$(this).val()) {
            return;
        }
        loadEvaluation($('#eval_form_filter'), true);
        pushStateUrl();
    });
    
    if (IS_TEAM) {
        if (curr_month) {
            $('#_me_month').val(curr_month);
        }
        if ($('#_me_team').val() && $('#_me_month').val()) {
            loadEvaluation($('#eval_form_filter'));
        }
    }
    
    $('#_me_project').change(function () {
        var project_id = $(this).val();
        if (!project_id) {
            return;
        }
        _me_table.addClass('hidden');
        _form_assign.addClass('hidden');
        loadMonthsOfProject(project_id);
        pushStateUrl();
    });
    
    if (curr_project_id && $('._me_review_page').length < 1) {
        $('#_me_project').val(curr_project_id);
        loadMonthsOfProject(curr_project_id);
    }
   
    $('#eval_form_filter').submit(function () {
        loadEvaluation($(this));
        return false;
    });
    
    function pushStateUrl() {
        var project_id = $('#_me_project').val();
        var team_id = $('#_me_team').val();
        if (typeof project_id != 'undefined') {
            history.pushState({}, '', _urlPath + '?project_id=' + project_id + '&month=' + $('#_me_month').val());
        } else {
            history.pushState({}, '', _urlPath + '?team_id=' + team_id + '&month=' + $('#_me_month').val());
        }
    }
    
    function loadMonthsOfProject(project_id) {
        $('#_me_month').html('<option value>'+ text_loading +'</option>');
        $.ajax({
           url: _loadMonthsProjectUrl,
           type: 'GET',
           data: {
               project_id: project_id
           },
           dataType: 'json',
           success: function (data) {
                var options = '<option value>'+ text_select_month +'</option>';
                for (var i in data.data) {
                    var month = data.data[i];
                    options += '<option value="'+month.timestamp+'">'+month.string+'</option>';
                }
                $('.team-of-project').text(data.team);
                $('#_me_month').html(options);
                var len = data.data.length;
                if (curr_month) {
                    $('#_me_month').val(curr_month);
                    loadEvaluation($('#eval_form_filter'));
                } else if (len > 0) {
                    var select_val = data.data[0].timestamp;
                    if (select_val > time_now) {
                        select_val = time_now;
                    }
                    $('#_me_month').val(select_val);
                    loadEvaluation($('#eval_form_filter'), true);
                    pushStateUrl();
                }
           }
        });
    }

    function loadEvaluation(form, ignored_change) {
        if (typeof ignored_change == "undefined") {
            ignored_change = false;
        }
        var project_id = $('#_me_project').val();
        var team_id = $('#_me_team').val();
        var month = $('#_me_month').val();
        if (typeof month == "undefined" || !month) {
            return;
        }
        //check new version and redirect url
        var type = 'project', currProj = null;
        if (project_id) {
            currProj = project_id;
        } else if (team_id) {
            currProj = team_id;
            type = 'team';
        }
        if (checkNewVersion(month, currProj, type)) {
            return false;
        }

        if (form.attr('data-change') == 1 || ignored_change) {
            _me_table.addClass('hidden');
            _form_assign.addClass('hidden');
            _status_content.addClass('hidden');
            var url = form.attr('action');
            _setStatus('<i class="fa fa-spin fa-refresh"></i>');
            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    project_id: project_id,
                    team_id: team_id,
                    month: month
                },
                success: function (data) {
                    if (data.status == 1) {
                        _me_table.removeClass('hidden');
                        _form_assign.removeClass('hidden');
                        _status_content.addClass('hidden');
                        $('#_me_table tbody').html(data.eval_items);
                        $('#_me_assignee').html(data.option_leaders);
                        form.attr('data-change', 0);
                        selectedAssignee();

                        initPoint(true);
                        updateAvgPointReload();
                        curr_project_id = null;
                        curr_month = null;
                        if (!data.submited) {
                            _form_assign.addClass('hidden');
                        }
                        _me_table.find('.select-search').select2({
                            minimumResultsForSearch: -1
                        });
                        _me_table.find('thead ._check_all').trigger('click');
                        //fixed cols
                        var fixedCols = $('.fixed-table thead tr:first .fixed-col').length;
                        $(".fixed-table").tableHeadFixer({"left" : fixedCols});
                    } else {
                        _status_content.html(data.eval_items).removeClass('hidden');
                        $('#_me_table tbody').html('');
                    }
                    form.find('button[type="submit"]').prop('disabled', false);
                    _hideStatus();
                    //show range time
                    $('.month-range-time').text('Date from: ' + data.range_time.start + ' to: ' + data.range_time.end);
                },
                error: function (err) {
                    form.find('button[type="submit"]').prop('disabled', false);
                    _showStatus(err.responseJSON, true);
                    form.attr('data-change', 0);
                }
            });
        } else {
            form.find('button[type="submit"]').prop('disabled', false);
        }
    }

    function selectedAssignee(){
        $('._item_select_assign').each(function () {
           var assignee = $(this).closest('td').attr('data-assignee'); 
           $(this).find('option[value="'+assignee+'"]').prop('selected', true);
        });
    }

    /*
     * change ME attribute point
     */
    $('body').on('change', '._me_attr_point', function () {
        var elThis = $(this);
        var pointVal = $(this).val();
        var oldVal = $(this).attr('data-value');
        var elGroup = $(this).closest('.point_group');
        var group = elGroup.attr('data-group');
        var currentCommented = elGroup.data('current-commented') || 0;
        //group performance if value excellent or unsatis then require comment
        if (group == GR_PERFORM && (pointVal == P_EXCELLENT || pointVal == P_UNSATIS)) {
            if (!currentCommented) {
                cm_tooltip.html(elGroup.find('.me_comment_box').clone());
                _setStatus('<i class="fa fa-spin fa-refresh"></i>');
                elThis.prop('disabled', true);
                loadAttrComments(cm_tooltip, _loadAttrCommentsUrl, false, function (response) {
                    _hideStatus();
                    elThis.prop('disabled', false);
                    currentCommented = response.current_user_comment;
                    if (!currentCommented) {
                        elGroup.trigger({
                            type: 'mousedown',
                            which: 3
                        });
                        if (cm_tooltip.find('.me_comment_title').next('p').length < 1) {
                            cm_tooltip.find('.me_comment_title').after('<p style="color: #00c0ef;"><i>'+ textRequiredComment +'</i></p>');
                        }
                        cm_tooltip.find('.me_comment_box').attr('data-change-point', pointVal);
                    }
                });
            } else {
                changePoint($(this));
            }
        } else {
            changePoint($(this));
        }
        cm_tooltip.find('.me_comments_list').removeClass('loaded');
    });
    
    $('body').on('click', '.me_comment_submit', function () {
        var el_this = $(this);
        var comment_box = $(this).closest('.me_comment_box');
        var eval_id = comment_box.attr('data-eval');
        var attr_id = comment_box.attr('data-attr') || null;
        var project_id = comment_box.attr('data-project');
        var is_leader = parseInt(comment_box.attr('data-leader') || null);
        var is_staff = parseInt(comment_box.attr('data-staff') || null);
        //check required comment change point
        var change_point_val = comment_box.attr('data-change-point') || null;
        var point_element = $('tr[data-eval="'+ eval_id +'"] td[data-attr="'+ attr_id +'"] ._me_attr_point');

        var comment_form = $(this).closest('.me_comment_form');
        var comment_input = comment_form.find('.me_comment_text');
        var comment_list = comment_box.find('.me_comments_list');
        var comment_text = comment_input.val();

        if (comment_text.trim() != "") {
            var ids = [eval_id];
            if ($('#_me_table tr[data-eval="'+ eval_id +'"] ._check_item').is(":checked")) {
                $('#_me_table ._check_item:checked').each(function () {
                    var item_id = $(this).val();
                    if (item_id != eval_id) {
                        ids.push(item_id); 
                    }
                });
            }
            var data = {
                    _token: _token,
                    comment_text: comment_text,
                    "eval_ids[]": ids,
                    attr_id: attr_id,
                    project_id: project_id,
                    is_leader: is_leader,
                    is_staff: is_staff
            };
            var comment_type = comment_box.attr('data-comment-type');
            if (typeof comment_type != "undefined") {
                data.comment_type = comment_type;
            } else {
                data["eval_ids[]"] = [eval_id];
            }
            
            el_this.prop('disabled', true);
            $.ajax({
                url: _addCommentUrl,
                type: 'POST',
                data: data,
                success: function (result) {
                    for (var i in result) {
                        var data = result[i];
                        var tr = _me_table.find('tbody tr[data-eval="' + data.id + '"]');
                        
                        if (attr_id) {
                            tr.find('td[data-attr="'+ attr_id +'"]').addClass('has_comment ' + data.td_type);
                            tr.find('td[data-attr="'+ attr_id +'"]').data('current-commented', 1);
                        } else {
                            tr.find('td.note_group').addClass('has_comment ' + data.td_type);
                        }
                        
                        var can_change = data.change_status;
                        if (can_change.approved || can_change.closed) {
                            tr.find('._btn_accept').prop('disabled', false);
                        } else {
                            tr.find('._btn_accept').prop('disabled', true);
                        }
                        if (can_change.feedback) {
                            tr.find('._btn_feedback').removeClass('is-disabled');
                        } else {
                            tr.find('._btn_feedback').addClass('is-disabled');
                        }
                    }
                    comment_list.append(result[eval_id].comment_item).addClass('loaded');
                    comment_list.closest('td').addClass('has_comment '+result[eval_id].td_type);
                    var dropdown_top = comment_list.closest('.dropdown').offset().top;
                    if (dropdown_top < 0) {
                        comment_list.addClass('maxh-140');
                    }
                    scrollBottom(comment_list);
                    comment_input.val('');
                    checkCanDoAction();
                    el_this.prop('disabled', false);
                    comment_input.attr('rows', 1);

                    //hide delete comments
                    var group = point_element.closest('.point_group').attr('data-group');
                    var pointVal = point_element.val();
                    if (group == GR_PERFORM && (pointVal == P_EXCELLENT || pointVal == P_UNSATIS)) {
                        comment_list.find('.btn_del_comment').addClass('hidden');
                    }

                    //do action change piont
                    if (change_point_val) {
                        point_element.val(change_point_val);
                        changePoint(point_element);
                        point_element.next('.select2').find('.select2-selection__rendered').text(point_element.find('option[value="'+ change_point_val +'"]').text());
                        comment_box.removeAttr('data-change-point');
                        comment_box.find('.me_comment_title').next('p').remove();
                    }
                },
                error: function (err) {
                    _showStatus(err.responseJSON, true);
                    el_this.prop('disabled', false);
                }
            });
        }
    });

    $('body').on('keydown', '.me_comment_text', function (e) {
        if (e.which == 13) {
            var rowsTextarea = $(this).attr('rows');
            if (!rowsTextarea) {
                rowsTextarea = 1;
            }
            rowsTextarea = parseInt(rowsTextarea);
            if (rowsTextarea > 1 ) {
                return true;
            }
            $(this).attr('rows', (rowsTextarea+1));
        }
    });
    
    $('body').on('click', '.btn_del_comment', function (e) {
       e.preventDefault();
       var btn = $(this);
       var url = btn.data('url');
       btn.prop('disabled', true);
       var eval_id = btn.closest('.me_comment_box').attr('data-eval');
       var attr_id = btn.closest('.me_comment_box').attr('data-attr');
       _setStatus('<i class="fa fa-spin fa-refresh"></i>');
       $.ajax({
           url: url,
           type: 'DELETE',
           data: {
               _token: _token,
               eval_id: eval_id,
               attr_id: attr_id
           },
           success: function (data) {
                var comment_id = data.comment_id;
                $('#me_comment_' + comment_id).remove();
                _hideStatus();
                btn.prop('disabled', false);
                var can_change = data.change_status;
                if (can_change) {
                    var tr = _me_table.find('tbody tr[data-eval="'+ eval_id +'"]');
                    if (can_change.approved || can_change.closed) {
                        tr.find('._btn_accept').prop('disabled', false);
                    } else {
                        tr.find('._btn_accept').prop('disabled', true);
                    }
                    if (can_change.feedback) {
                        tr.find('._btn_feedback').addClass('is-disabled');
                    } else {
                        tr.find('._btn_feedback').removeClass('is-disabled');
                    }
                    tr.find('td[data-attr="'+ attr_id +'"]').removeClass('td_gl_type td_pm_type td_st_type td_coo_type').addClass(data.td_class);
                }
           },
           error: function (error) {
               _showStatus(error.responseJSON, true);
               btn.prop('disabled', false);
           }
       });
    });

    /*
     * right mouse click load comment ME attributes
     */
    $('body').on('mousedown', '.point_group, .note_group', function (e) {
        if (e.which != 3) {
            return;
        }
        var el_this = $(this);
        var comment_box = el_this.find('.me_comment_box');
        var wh = $(window).height(), ww = $(window).width();
        var px = el_this.offset().left;
        var py = wh - el_this.offset().top;
        $('.point_group, .note_group').removeClass('highlight');
        el_this.addClass('highlight');
        cm_tooltip.css('bottom', py).css('left', px).css('right', 'auto').addClass('open').attr('data-left', px).attr('data-scroll', $('._me_table_responsive').scrollLeft());
        var comment_html = comment_box.clone();

        cm_tooltip.html(comment_html);
        loadAttrComments(cm_tooltip, _loadAttrCommentsUrl);

        cm_tooltip.find('.me_comment_text').focus();
        cm_tooltip.find('.me_comment_title').next('p').remove();
        if (cm_tooltip.offset().left + cm_tooltip.width() >= ww) {
            cm_tooltip.css('right', (ww - px - el_this.width()) - 10).css('left', 'auto');
            cm_tooltip.attr('data-left', (cm_tooltip.offset().left));
        }
    });

    $('body').on('contextmenu', '.point_group, .note_group', function () {
        return false;
    });

    $('body').on('click', '._comment_loadmore', function (e) {
        e.preventDefault();
        var point_group = $(this).closest('#_me_comments');
        if ($(this).attr('href') != '#') {
            loadAttrComments(point_group, $(this).attr('href'), true);
        }
    });
    
    $('._me_table_responsive').on('scroll', function () {
        var scroll_left = $(this).scrollLeft();
        var css_left = parseInt(cm_tooltip.attr('data-left'));
        var cm_left = parseInt(cm_tooltip.attr('data-scroll'));
        cm_tooltip.css('left', (css_left - scroll_left + cm_left)).css('right', 'auto');
    });
    
    $(document).click(function (e) {
        if ($(e.target).closest('.point_group').length < 1) {
            var point_group = $('.point_group');
            point_group.removeClass('open');
            point_group.find('.me_comments_list').removeClass('maxh-140');
            point_group.find('.me_comment_box').css('right', 'auto').css('top', 'auto');
        }
        if ($(e.target).closest('.note_group').length < 1) {
            var note_group = $('.note_group');
            note_group.removeClass('open');
            note_group.find('.me_comments_list').removeClass('maxh-140');
            note_group.find('.me_comment_box').css('right', 'auto').css('top', 'auto');
        }
        if ($(e.target).closest('#_me_comments').length < 1) {
            if (e.which == 3) {
                return;
            }
            cm_tooltip.removeClass('open');
            $('.point_group, .note_group').css('right', 'auto').css('top', 'auto').removeClass('highlight');
            //reset attribute point value
            resetPointCloseComment();
        }
    });
    $('body').on('click', '.me_comment_box .close', function () {
         cm_tooltip.removeClass('open').css('right', 'auto').css('top', 'auto');
         $('.point_group, .note_group').removeClass('highlight');
         resetPointCloseComment();
    });

    /*
     * on close popup comment, reset point value
     */
    function resetPointCloseComment() {
        var commentBox = cm_tooltip.find('.me_comment_box');
        var evalId = commentBox.attr('data-eval');
        var attrId = commentBox.attr('data-attr') || null;
        var elPoint = $('tr[data-eval="'+ evalId +'"] td[data-attr="'+ attrId +'"] ._me_attr_point');
        var group = elPoint.closest('.point_group').attr('data-group');
        if (elPoint.length > 0 && group == GR_PERFORM) {
            elPoint.val(elPoint.attr('data-value'));
            elPoint.next('.select2').find('.select2-selection__rendered').text(elPoint.find('option[value="'+ elPoint.val() +'"]').text());
        }
    }

    function loadAttrComments(element, url, more, loaded) {
        if (typeof more == "undefined") {
            more = false;
        }
        var comment_box = element.find('.me_comment_box');
        var attr_id = comment_box.attr('data-attr');
        var eval_id = comment_box.attr('data-eval');
        var comment_list = comment_box.find('.me_comments_list');
        var loadmore_btn = comment_box.find('._comment_loadmore');
        var loading = element.find('._loading');
        if (!comment_list.hasClass('loaded') || more) {
            loading.removeClass('hidden');
            var data = {
                eval_id: eval_id,
                attr_id: attr_id
            };
            var comment_type = element.find('.me_comment_box').attr('data-comment-type');
            if (typeof comment_type != "undefined") {
                data.comment_type = comment_type;
            }
            $.ajax({
                url: url,
                type: 'GET',
                data: data,
                success: function (data) {
                    loading.addClass('hidden');
                    comment_list.addClass('loaded').append(data.comment_html);
                    var elAttr = $('tr[data-eval="'+ eval_id +'"] td[data-attr="'+ attr_id +'"] ._me_attr_point');
                    if (elAttr.length > 0) {
                        var group = elAttr.closest('.point_group').attr('data-group');
                        var pointVal = elAttr.val();
                        if (group == GR_PERFORM && (pointVal == P_EXCELLENT || pointVal == P_UNSATIS)) {
                            comment_list.find('.btn_del_comment').addClass('hidden');
                        }
                    }
                    scrollBottom(comment_list);
                    if (data.next_page_url) {
                        loadmore_btn.attr('href', data.next_page_url).removeClass('hidden');
                    } else {
                        loadmore_btn.attr('href', '#').addClass('hidden');
                    }
                    var dropdown_top = comment_list.closest('.dropdown').offset().top;
                    if (dropdown_top < 0) {
                        comment_list.addClass('maxh-140');
                    }
                    $('tr[data-eval="'+ eval_id +'"] td[data-attr="'+ attr_id +'"]').data('current-commented', data.current_user_comment);
                    if (typeof loaded != 'undefined') {
                        loaded(data);
                    }
                },
                error: function (err) {
                    _showStatus(err.responseJSON, true);
                    loading.addClass('hidden');
                }
            });
        }
    }
    
    _form_assign.submit(function () {
        
        var form = $(this);
        var btnSubmit = $(this).find('button[type="submit"]');
        var evalIds = [];
        var status_id = $('#_me_status').val();
        var assignee_id = $('#_me_assignee').val(); 
        if ($('#_me_alert .fa-refresh').length > 0 &&
                $('#_me_alert').hasClass('show')) {
            btnSubmit.prop('disabled', false);
            return false;
        }

        if (checkError()) {
            btnSubmit.prop('disabled', false);
            showModalError(text_error_max_value);
            return false;
        }
        
        if (!assignee_id || assignee_id == 0) {
            btnSubmit.prop('disabled', false);
            showModalError(text_error_assignee);
            return false;
        }

        _me_table.find('tbody tr ._check_item:checked').each(function () {
            evalIds.push($(this).val());
        });
        if (evalIds.length < 1) {
            showModalError(NoneItemChecked);
            btnSubmit.prop('disabled', false);
            return false;
        }
        var hideForm = (evalIds.length === _me_table.find('tbody tr ._check_item').length);

        _modal_confirm.modal('show');
        _modal_confirm.find('.text-default').html(btnSubmit.data('noti'));
        $('.btn-outline').one('click', function (e) {
            e.preventDefault();
            if ($(this).hasClass('btn-ok')) {
                _modal_confirm.modal('hide');

                _setStatus('<i class="fa fa-spin fa-refresh"></i>');
                $.ajax({
                   url: form.attr('action'),
                   type: 'POST',
                   data: {
                       'eval_ids[]': evalIds,
                       assignee: assignee_id,
                       status: status_id,
                       _token: _token
                   },
                   success: function (data) {
                       btnSubmit.prop('disabled', false);
                        _showStatus(data.message);
                        $.each(data.results, function (evalId, item) {
                            var statusText = $('#_me_status option[value="'+ item.status +'"]').text();
                            var elSttText = $('#_me_table tbody tr[data-eval="'+ evalId +'"] ._status_text');
                            elSttText.text(statusText);
                            if (!item.can_change) {
                                var elAttrPoint = elSttText.closest('tr').find('._me_attr_point');
                                elAttrPoint.each(function () {
                                    if (!$(this).val()) {
                                        $(this).val(0);
                                    }
                                });
                                elAttrPoint.prop('disabled', true);
                                elSttText.closest('tr').find('._me_general_comment').prop('disabled', true);
                                _me_table.find('tbody tr[data-eval="'+ evalId +'"] ._check_item').remove();
                            }
                        });
                        if (hideForm) {
                            form.addClass('hidden');
                        }
                   },
                   error: function (err) {
                       btnSubmit.prop('disabled', false);
                       _showStatus(err.responseJSON, true);
                   }
                });
            } else {
                btnSubmit.prop('disabled', false);
            }
        });
        return false;  
        
    });
    
    $('body').on('mouseenter', '._action_btns', function () {
        $(this).addClass('open');
    });
    
    $('body').on('mouseleave', '._action_btns', function () {
        $(this).removeClass('open');
    });
    
    $('body').on('submit', '._me_item_form', function () {
        var el_this = $(this);
        var btn = el_this.find('button[type="submit"]');
        var status_btn = el_this.closest('._action_btns').find('.status-btn');
        var el_status = el_this.find('select[name="status"]');
        
        if (checkError()) {
            btn.prop('disabled', false);
            showModalError(text_error_max_value);
            return false;
        }
        
        _setStatus('<i class="fa fa-spin fa-refresh"></i>');
        $.ajax({
           url: $(this).attr('action'),
           type: 'POST',
           data: $(this).serialize(),
           success: function (data) {
               _hideStatus();
               _modal_success.modal('show');
                _modal_success.find('.text-default').html(data.message);
                el_status.val(data.status);
               btn.prop('disabled', false);
               var status = el_this.find('select[name="status"] option:selected').text();
               status_btn.text(status);
           },
           error: function (err) {
               _showStatus(err.responseJSON);
               btn.prop('disabled', false);
           }
        });
        return false;
    });
    
    $('body').on('mousedown', '.input_select input', function (e) {
        if (e.which != 1 || $(this).prop('disabled')) {
            return;
        }
        $('body').find('.input_select').removeClass('show');
        $('body').find('.point_group').removeClass('open');
        if ($(this).parent().hasClass('show')) {
            $(this).parent().removeClass('show');
        } else {
            $(this).parent().addClass('show');
        }
    });
    $('body').on('click', '.input_select select', function () {
        $(this).closest('.point_group').removeClass('show_tooltip');
    });
    $('body').on('keypress keyup blur', '.input_select input', function (key) {
        var keycode = (key.which) ? key.which : key.keyCode;
        if (keycode > 31 && (keycode < 48 || keycode > 57) && keycode != 46) {
            key.preventDefault();
        }
        else return true;
    });
    $('body').click(function (e) {
        if ($(e.target).closest('.input_select').length < 1) {
            $('body').find('.input_select').removeClass('show');
        }
    });
    
    $('body').on('click', '.close_alert', function (e) {
       e.preventDefault();
       $(this).closest('#_me_alert').html('').removeClass('show');
    });
    
    $('body').on('click', 'a.disabled', function (e) {
       e.preventDefault(); 
    });
    
    $('body').on('submit', '._actions_form', function (e) {
        var btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true);
        _me_table.find('tbody tr').removeClass('tr_error');
        var el_this = $(this);
        var ids = [];
        $('#_me_table ._check_item:checked').each(function () {
            if (btn.hasClass('btn_form_accept')) {
                var eval_id = $(this).closest('tr').attr('data-eval');
                if ($('#_me_table tr[data-eval="'+ eval_id +'"] ._btn_accept').length > 0) {
                    ids.unshift($(this).val());
                }
            } else {
                ids.unshift($(this).val());
            }
        });
        var action = el_this.find('input[name="action"]').val();
        
        _setStatus('<i class="fa fa-spin fa-refresh"></i>');
        $.ajax({
           url: el_this.attr('action'),
           type: 'POST',
           dataType: 'json',
           data: {
               _token: _token,
               'eval_ids[]': ids,
               action: action
           },
           success: function (data) {
                _showStatus(data);
                $('._check_all').prop('checked', false);
                window.location.reload();
           },
           error: function (err) {
               _hideStatus();
               var data = err.responseJSON;
                showModalError(data.message);
                if (typeof data.eval_id != "undefined") {
                    _me_table.find('tr[data-eval="'+ data.eval_id +'"]').addClass('tr_error');
                }
                $('._actions_form button[type="submit"]').prop('disabled', false);
           }
        });
        return false;
    });
    
    $('body').on('mouseenter', '.tooltip_group i', function (e) {
        var td = $(this).closest('th');
        var wh = $(window).height(), ww = $(window).width();
        var px = td.offset().left + td.width() / 2;
        var py = wh - td.offset().top;
        var tooltip = $(this).parent().find('.me_tooltip');
        me_tooltip.html(tooltip.html()).addClass('open');
        me_tooltip.css('bottom', py).css('left', px);
        if (tooltip.hasClass('text-center')) {
            me_tooltip.addClass('text-center');
        }
        if (me_tooltip.height() > td.offset().top - 20) {
            me_tooltip.css('top', 0).css('bottom', 'auto');
        }
        if (me_tooltip.offset().left + me_tooltip.width() > ww - 20) {
            me_tooltip.css('right', -me_tooltip.width() / 2).css('left', 'auto');
        }
        
        // Highlight related columns of calculated column
        var name = td.data('name') || '';
        if (td.hasClass('cal-value') && (name != '')) {
            $('[data-related-col="' + name + '"]').addClass('highlight');
        }
    });
    $('body').on('mouseleave', '.tooltip_group i', function () {
        me_tooltip.html('').removeClass('open text-center').css('bottom', 0).css('left', 0).css('top', 'auto').css('right', 'auto');
        
        // Remove highlight related columns of calculated column
        var td = $(this).closest('th');
        var name = td.data('name') || '';
        if (td.hasClass('cal-value') && (name != '')) {
            $('[data-related-col="' + name + '"]').removeClass('highlight');
        }
    });
    
    $('body').on('click', '.form_item_confirm button[type="submit"]', function () {
        var btn_submit = $(this);
        _modal_confirm.find('.btn-ok').removeClass('display-none');
        if(btn_submit.hasClass('is-disabled')) {
            _modal_confirm.modal('show');
            _modal_confirm.find('.text-default').html(btn_submit.data('warning'));
             _modal_confirm.find('.btn-ok').addClass('display-none');
            return false;
        }
        var form = $(this).closest('form');
        _modal_confirm.modal('show');
        _modal_confirm.find('.text-default').html(btn_submit.data('noti'));
        $('.btn-outline').one('click', function (e) {
            e.preventDefault();
            if ($(this).hasClass('btn-ok')) {
                _modal_confirm.modal('hide');
                form.submit();
                return true;
            } else {
                _modal_confirm.modal('hide');
                return false;
            }
        });
        return false;
    });
    
    $(document).ready(function () {
        if ($('._me_review_page').length > 0) {
            initPoint(true);
        }
        setTimeout(function () {
            hideOverLay();
        }, 300);
    });

    $('body').on('change', 'select.select-grid, .grid-pager-box select, .form-pager input', function () {
//        $('#_me_table, #_me_table, .hide-filter').addClass('hidden');
//        $('html, body').animate({ scrollTop: 0 }, 500);
        showOverlay();
    });
    $('body').on('click', '.btn-reset-filter, .btn-search-filter, .paginate_button a', function (e) {
        if ($(this).parent().hasClass('disabled')) {
            e.stopImmediatePropagation();
            return false;
        }
//        $('#_me_table, #_me_table, .hide-filter').addClass('hidden');
//        $('html, body').animate({ scrollTop: 0 }, 500);
        showOverlay();
    });
    //show overlay loading
    function showOverlay() {
        if ($('#overlay').length > 0) {
            $('#overlay').removeClass('hidden');
        }
    }
    //hide overlay loading
    function hideOverLay() {
        if ($('#overlay').length > 0) {
            $('#overlay').addClass('hidden');
        }
    }

    function initPoint(has_sumary) {
        _me_table.find('tbody tr').each(function () {
            calAvgRuleRow($(this).find('.point_group[data-group="' + GR_NORMAL + '"]:first'));
            $(this).find('.point_group[data-group="' + GR_PERFORM + '"]').each(function () {
                calPerformAvgCol($(this));
            });
            calPerformAvgRow($(this).find('.point_group[data-group="' + GR_PERFORM + '"]:first'));
        });
        calFactorPersonal();
        _me_table.find('tbody tr').each(function () {
            calPerformance($(this).find('.point_group[data-group="' + GR_PERFORM + '"]:first'));
            if (has_sumary) {
                calSumary($(this).find('.point_group[data-group="' + GR_PERFORM + '"]:first'));
            } 
        });
    }
    
    function changePoint(elements, disabled) {
        if (typeof disabled == "undefined") {
            disabled = false;
        }
        
        if (!$.isArray(elements)) {
            elements = [elements];
        }

        var data_evals = [];
        for (var i = 0; i < elements.length; i++) {
            var elment = elements[i];
            
            var tr = elment.closest('tr');
            var attr_id = elment.attr('data-attr');
            var eval_id = tr.attr('data-eval');
            var range_min = elment.attr('min');
            var range_max = elment.attr('max');
            var old_point = elment.attr('data-value');
            var point = elment.val(); 
            var dataType = elment.data('integer');
            if (typeof dataType != "undefined") {
                point = point.replace(/[^0-9]/g,'');
            }
            if (point) {
                point = parseInt(point);
            } else {
                point = 0;
            }
            elment.val(point);

            if (point == parseInt(old_point) || point < parseInt(range_min) || point > parseInt(range_max)) {
                elment.val(range_min);
                return;
            }

            var el_avg = elment.closest('tr').find('._point_avg ._value');

            calAvgRuleRow(elment);
            calPerformAvgCol(elment);
            calPerformAvgRow(elment);
            calFactorPersonal();
            calPerformance(elment);
            calSumary(elment);
            data_evals.push({eval_id: eval_id, attr_id: attr_id, point: point, avg_point: el_avg.text()});
        }

        if (data_evals.length < 1) {
            return;
        }
        _setStatus('<i class="fa fa-spin fa-refresh"></i>');
        $.ajax({
            url: _addAttrPointUrl,
            type: 'POST',
            data: {
                data_evals: data_evals,
                _token: _token
            },
            success: function (data) {
                if (data.length > 0) {
                    _showStatus('Saved');
                    for (var i in data) {
                        var eval_item = data[i];
                        var tr_item = _me_table.find('tr[data-eval="'+ eval_item.id +'"]');
                        var el_point = tr_item.find('td [data-attr="'+ eval_item.attr_id +'"]');
                        el_point.attr('data-value', point);
                        if (disabled){
                            el_point.prop('disabled', true);
                        }
                        tr.find('._contribute_val').text(eval_item.contribute_label);
                        tr.attr('data-change', 1);
                    }
                }
            },
            error: function (err) {
                for (var i in elements) {
                    var elment = elements[i];
                    var old_point = elment.attr('data-value');
                    elment.val(old_point);
                    calAvgRuleRow(elment);
                    calPerformAvgCol(elment);
                    calPerformAvgRow(elment);
                    calPerformance(elment);
                    calSumary(elment);
                }
                _me_table.find('.select-search').each(function () {
                    var old_text = $(this).find('option:selected').text();
                    var select2_next = $(this).next('.select2');
                    select2_next.find('.select2-selection__rendered').attr('title', old_text).text(old_text);
                });
                calFactorPersonal();
                _showStatus(err.responseJSON, true);
            }
        });
    }
    
    function updateAvgPointReload() {
        var data_evals = [];
        _me_table.find('._point_avg').each(function () {
            var tr = $(this).closest('tr');
            if (tr.attr('data-edit')) {
                var point = parseFloat($(this).find('._value').text());
                var eval_id = tr.attr('data-eval');
                data_evals.push({eval_id: eval_id, value: point});
            }
        });
        if (data_evals.length < 1) {
            return;
        }
        $.ajax({
            url: _updateAvgPointUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                _token: _token,
                data_evals: data_evals
            },
            success: function (data) {
               if (data.length > 0) {
                   for (var i in data) {
                       _me_table.find('tr[data-eval="'+ data[i].id +'"] ._contribute_val').text(data[i].label);
                       _me_table.find('tr[data-eval="'+ data[i].id +'"]').attr('data-edit', 0);
                   }
               }
            }
       });
    }
    
    // caculate average rule point for each row
    function calAvgRuleRow(element) {
        var row = element.closest('tr');
        var totalPoint = 0;
        var th_avg_rule_val = parseFloat($('.th_avg_rule_val').attr('data-weight'));
        row.find('.point_group[data-group="'+ GR_NORMAL +'"] ._me_attr_point').each(function () {
            var input = $(this);
            var point = parseFloat(input.val());
            var weight = parseFloat(input.attr('data-weight'));
            if (isNaN(point)) {
                point = parseFloat(input.text());
            }
            if (!point) {
                point = 0;
            }
            totalPoint += point * weight;
        });
        var avg_point = $.number(totalPoint / (th_avg_rule_val * 2), 2, '.', ',');
        row.find('._avg_rules').text(avg_point);
    }
    
    // caculate individual index point foreach row
    function calPerformAvgRow(element) {
        var row = element.closest('tr');
        var avg_point = 0;
        var total = 0;
        row.find('.point_group[data-group="' + GR_PERFORM + '"]').each(function () {
            var input = $(this).find('._me_attr_point'); 
            var point = parseFloat(input.val());
            if (isNaN(point)) {
                point = parseFloat(input.text());
            }
            if (!point) {
                point = 0;
            }
            if (point > P_NA) {
                avg_point += point;
                total ++;
            }
        });
        avg_point = $.number(avg_point / total, 2, '.', ',');
        row.find('._pf_person_avg').text(avg_point);
    }
    
    // caculate individual index point column index in each row
    function calPerformAvgCol(element) {
        var attr_id = element.attr('data-attr');
        var avg_point = 0;
        var total = 0;
        $('body').find('.point_group[data-group="' + GR_PERFORM + '"][data-attr="' + attr_id + '"]').each(function () {
            var input = $(this).find('._me_attr_point');
            var point = parseFloat(input.val());
            if (isNaN(point)) {
                point = parseFloat(input.text()); 
            }
            if (point > P_NA) {
                avg_point += point; 
                total ++;
            }
        });
        var avg_total = avg_point/total;
        var el_pf_avg = $('body').find('._pf_avg[data-attr="' + attr_id + '"]');
        el_pf_avg.text(getLabelIndividual(avg_total.toFixed(0)));
    }
    
    //get label individual by average point
    function getLabelIndividual(point) {
        if (point >= P_EXCELLENT) {
            return optionsPoint[P_EXCELLENT];
        } else if (point >= P_GOOD) {
            return optionsPoint[P_GOOD];
        } else if (point >= P_FAIR) {
            return optionsPoint[P_FAIR];
        } else if (point >= P_SATIS) {
            return optionsPoint[P_SATIS];
        } else if (point >= P_UNSATIS) {
            return optionsPoint[P_UNSATIS];
        } else {
            return optionsPoint[P_NA];
        }
    }
    
    // caculate average individual index point for all row
    function calFactorPersonal() {
        var point = 0;
        var total = 0;
        $('body').find('._pf_person_avg').each(function () {
            point += parseFloat($(this).text());
            total ++;
        });
        var avg_total = point/total * 100;
        var el_pf_avg = $('body').find('._pf_person_avg_col');
        el_pf_avg.text(avg_total.toFixed(0) + '%');
    }
    
    //caculate performance for each row
    function calPerformance(element) {
        var row = element.closest('tr');
        var project_point = parseFloat(row.find('._project_point').text()); 
        var project_type = parseFloat(row.find('._project_type').text()); 
        var avg = Math.min(project_point * project_type * MAX_POINT / MAX_PP, 5); 
        row.find('._perform_value').text($.number(avg, 2, '.', ','));
    }
    
    //caculate summary point for each row
    function calSumary(element) {
        var th_perform_val = parseInt($('.th_perform_val').attr('data-weight'));
        var th_avg_rule_val = parseInt($('.th_avg_rule_val').attr('data-weight'));
        var th_individual_val = parseInt($('.th_individual_val').attr('data-weight'));
        var row = element.closest('tr');
        if (!row.attr('data-edit')) {
            return;
        }
        var avg_rule_point = parseFloat(row.find('._avg_rules').text());
        var individual_point = parseFloat(row.find('._pf_person_avg').text());
        var perform_point = parseFloat(row.find('._perform_value').text());
        var point = avg_rule_point * th_avg_rule_val + individual_point * th_individual_val + perform_point * th_perform_val;
        point = $.number(point/100, 2, '.', ',');
        row.find('._point_avg ._value').text(point);
    }

    if ($('._check_all').length > 0) {
        $('._check_item').prop('checked', false);
        setTimeout(function () {
            $('._actions_form button[type="submit"]').prop('disabled', true);
        }, 1000);
        $('body').on('change', '#_me_table ._check_all', function () {
            $('#_me_table ._check_item').prop('checked', $(this).is(':checked'));
            checkCanDoAction();
        });
        $('body').on('change', '#_me_table ._check_item', function(){
            var itemLength = $('#_me_table ._check_item').length;

            $('#_me_table ._check_all').prop('checked', $('#_me_table ._check_item:checked').length === itemLength);
            checkCanDoAction();
        });
    }

    /**
     * check disabled button
     * @returns 
     */
    function checkCanDoAction() {
        var check_len = _me_table.find('._check_item:checked').length;
        if (check_len < 1) {
            $('.btn_form_feedback').prop('disabled', true);
            $('.btn_form_accept').prop('disabled', true);
            return;
        }
        var not_feedback_len = 0, accept_len = 0;
        textWarning = "";
        _me_table.find('._check_item:checked').each(function () {
            var tr = _me_table.find('tr[data-eval="'+ $(this).closest('tr').attr('data-eval') +'"]');
            var btn_accept = tr.find('._btn_accept');
            var btn_feedback = tr.find('._btn_feedback');
            if (btn_feedback.hasClass('is-disabled')) {
                textWarning += '<br />' + tr.find('.employee').text() + '-' + tr.find('.project_code_auto').text();
                not_feedback_len ++;
            }
            if (btn_accept.length > 0 && !btn_accept.is(":disabled")) {
                accept_len ++;
            }
        });
        if (not_feedback_len > 0) {
            message = $('.btn_form_feedback').data('mesasge');
            message2 = $('.btn_form_feedback').data('message2');
            $('.btn_form_feedback').addClass('is-disabled');
            $('.btn_form_feedback').attr('data-warning', message + textWarning + '<br />' +message2);
        } else {
            $('.btn_form_feedback').removeClass('is-disabled');
        }
        
        if (check_len > 0) {
            $('.btn_form_feedback').prop('disabled', false);
        } else {
            $('.btn_form_feedback').prop('disabled', true);
        }
        if (accept_len > 0) {
            $('.btn_form_accept').prop('disabled', false);
        } else {
            $('.btn_form_accept').prop('disabled', true);
        }
    }

    $('._btn_delete').on('click', function (event) {		
        $('#modal-delete-confirm').removeClass('modal-warning').addClass('modal-danger');		
    });		
    $('#modal-delete-confirm').on('hidden.bs.modal', function (event) {		
        $(this).addClass('modal-warning').removeClass('modal-danger');		
    });
    
    $('.btn-reset-filter').on('click', function () {
        resetUrl();
    });
    
    $('.filter-grid').on('change', function () {
        resetUrl();
    });
    //reset params url
    function resetUrl() {
        var location = window.location;
        window.history.pushState({}, document.title, location.origin + location.pathname);
    }

    // date tooltip z-index
    $('body').on('mouseenter', '.date-tooltip [data-toggle="tooltip"]', function () {
        $(this).closest('td').addClass('z-index-3000');
    });
    $('body').on('mouseleave', '.date-tooltip [data-toggle="tooltip"]', function () {
        $(this).closest('td').removeClass('z-index-3000');
    });

})(jQuery);

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
        _showTimeout = setTimeout(function () {
           _me_status.removeClass('show'); 
        }, 3000);
    }
}

function _hideStatus() {
    clearTimeout(_showTimeout);
    _me_status.removeClass('show');
}

function scrollBottom(element) {
    element.animate({scrollTop: element.prop('scrollHeight')}, 100);
}

function checkError() {
    var el_pf_avg = $('body').find('._pf_person_avg_col');
    if (el_pf_avg.hasClass('me_error')) {
        return true;
    }
    var error = false;
    $('body').find('._pf_avg').each(function () {
       if ($(this).hasClass('me_error')) {
           error = true;
           return false;
       } 
    });
    return error;
}

function showModalSuccess(message) {
    _modal_success.find('.text-default').html(message);
    _modal_success.modal('show');
}

function showModalError(message) {
    if (typeof message == "undefined") {
        message = text_error_occurred;
    }
    _modal_error.find('.text-default').html(message);
    _modal_error.modal('show');
}

function checkNewVersion(time, currProj, type) {
    if (typeof currProj == 'undefined') {
        currProj = null;
    }
    if (typeof type == 'undefined') {
        type = 'project';
    }
    var dateTime = new Date(time);
    var sepDateTime = new Date(SEP_MONTH);
    if (dateTime.getTime() > sepDateTime.getTime()) {
        var params = '', sepParam = '?';
        if (currProj) {
            if (type == 'project') {
                params += '?project_id=' + currProj;
            } else {
                params += '?team_id=' + currProj;
            }
            sepParam = '&';
        }
        params += sepParam + 'month=' + time;
        window.location.href = newMeUrl + params;
        return true;
    }
    return false;
}