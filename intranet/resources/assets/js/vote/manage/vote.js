jQuery.validator.addMethod('lessThan', function (value, element, params) {
    var hasParamValue = false;
    var minValue = $(params[0]).val();
    for (var i in params) {
        var paramVal = $(params[i]).val();
        if (paramVal) {
            hasParamValue = true;
            if (minValue > paramVal) {
                minValue = paramVal;
            }
        }
    }
    if (value && hasParamValue && minValue) {
        return this.optional(element) || value <= minValue;
    }
    return true;
}, textValidLessThan);

jQuery.validator.addMethod('greaterThan', function (value, element, params) {
    var hasParamvalue = false;
    var maxValue = $(params[0]).val();
    for (var i in params) {
        var paramVal = $(params[i]).val();
        if (paramVal) {
            hasParamvalue = true;
            if (maxValue < paramVal) {
                maxValue = paramVal;
            }
        }
    }
    if (value && hasParamvalue && maxValue) {
        return this.optional(element) || value >= maxValue;
    }
    return true;
}, textValidGreaterThan);

(function ($) {
    
    function detectChangeField() {
        var isChange = false;
        $('.vote-field').each(function () {
            var field = $(this);
            var oldVal = field.attr('data-value').trim();
            var newVal = field.val().trim();

            var ckContent = $('#cke_vote_content');
            if (oldVal != newVal) {
                field.css('background', '#fcf8e3');
                field.next('.select2-container').find('.select2-selection').css('background', '#fcf8e3');
                if (ckContent.length > 0) {
                    ckContent.find('.cke_top').css('background', '#fcf8e3');
                }
                if (!isChange) {
                    isChange = true;
                }
            } else {
                field.css('background', '#fff');
                field.next('.select2-container').find('.select2-selection').css('background', '#fff');
                if (ckContent.length > 0) {
                    ckContent.find('.cke_top').css('background', '#f8f8f8');
                }
            }
            if (field.val() && field.attr('id') == 'vote_content') {
                field.parent().find('label.error').remove();
            }
        });
        if (isChange) {
            $('#change_data').val(1);
        } else {
            $('#change_data').val(0);
        }
    }

    var dataDatePicker = {
        format: 'YYYY-MM-DD HH:mm'
    };
    if ($('.create-vote-page').length > 0) {
        var currDate = new Date();
        currDate.setHours(0);
        currDate.setMinutes(0);
        dataDatePicker.minDate = currDate;
    }
    $('.input_date').datetimepicker(dataDatePicker);
    
    $('.vote-field.input_date').on('dp.change', function (e) {
        $('#vote_form').valid();
    });

    $('#vote_form').validate({
        ignore: [],
        errorPlacement: function (error, element) {
            var formGroup = element.closest('div');
            if (formGroup.length > 0) {
                error.appendTo(formGroup);
            } else {
                element.after(error.css('display', 'block'));
            }
        },
        rules: {
            title: {
                required: true,
                maxlength: 255
            },
            status: 'required',
            nominate_start_at: {
                lessThan: ['input[name="nominate_end_at"]', 'input[name="vote_start_at"]']
            },
            nominate_end_at: {
                greaterThan: ['input[name="nominate_start_at"]'],
                lessThan: ['input[name="vote_start_at"]']
            },
            vote_start_at: {
                required: true,
                greaterThan: ['input[name="nominate_start_at"]', 'input[name="nominate_end_at"]'],
                lessThan: ['input[name="vote_end_at"]']
            },
            vote_end_at: {
                required: true,
                greaterThan: ['input[name="vote_start_at"]']
            },
            nominee_max: {
                number: true,
                min: 0
            },
            vote_max: {
                number: true,
                min: 0
            },
            content: {
                required: function () {
                    if (CKEDITOR.instances.vote_content) {
                        CKEDITOR.instances.vote_content.updateElement();
                    }
                    return true;
                }
            }
        },
        messages: {
            title: {
                required: textValidRequired,
                maxlength: textValidMaxLen + ' 255 ' + textSymbol
            },
            status: {
                required: textValidRequired
            },
            nominate_start_at: {
                lessThan: textValidLessThan + textFieldNominateEndAt
            },
            nominate_end_at: {
                lessThan: textValidLessThan + textFieldVoteStartAt,
                greaterThan: textValidGreaterThan + textFieldNominateStartAt
            },
            vote_start_at: {
                required: textValidRequired,
                lessThan: textValidLessThan + textFieldVoteEndAt,
                greaterThan: textValidGreaterThan + textFieldNominateStartAt + ' ' + textAnd + ' ' + textFieldNominateEndAt
            },
            vote_end_at: {
                required: textValidRequired,
                greaterThan: textValidGreaterThan + textFieldVoteStartAt
            },
            content: {
                required: textValidRequired
            },
            nominee_max: {
                number: textValidNumber,
                min: textValidMin + '0'
            },
            vote_max: {
                number: textValidNumber,
                min: textValidMin + '0'
            }
        }
    });
    
    $('#nominate_form_mail').validate({
        ignore: [],
        errorPlacement: function (error, element) {
            var formGroup = element.closest('.form-group');
            if (formGroup.length > 0) {
                error.appendTo(formGroup);
            } else {
                error.appendTo(element.closest('div'));
            }
        },
        rules: {
            'mail_bcc[]': {
                required: function () {
                    return !$('select[name="mail_team_ids[]"]').val();
                }
            },
            mail_subject: {
                required: true,
                maxlength: 255
            },
            mail_content: {
                required: function () {
                    if (CKEDITOR.instances.mail_content) {
                        CKEDITOR.instances.mail_content.updateElement();
                    }
                    return true;
                }
            }
        },
        messages: {
            'mail_bcc[]': {
                required: textValidSelectTeamOrEmail
            },
            mail_subject: {
                required: textValidRequired,
                maxlength: textValidMaxLen + ' 255 ' + textSymbol
            },
            mail_content: {
                required: textValidRequired
            }
        }
    });
    
    $('.validate-field').on('change', function () {
        if ($(this).val()) {
            $(this).parent().find('label.error').remove();
        }
    });
    
    $('#vote_form_mail').validate({
        ignore: [],
        errorPlacement: function (error, element) {
            var formGroup = element.closest('.form-group');
            if (formGroup.length > 0) {
                error.appendTo(formGroup);
            } else {
                error.appendTo(element.closest('div'));
            }
        },
        rules: {
            'mail_vote_bcc[]': {
                required: function () {
                    return !$('select[name="mail_vote_team_ids[]"]').val();
                }
            },
            mail_vote_subject: {
                required: true,
                maxlength: 255
            },
            mail_vote_content: {
                required: function () {
                    if (CKEDITOR.instances.mail_vote_content) {
                        CKEDITOR.instances.mail_vote_content.updateElement();
                    }
                    return true;
                }
            }
        },
        messages: {
            'mail_vote_bcc[]' : {
                required: textValidSelectTeamOrEmail
            },
            mail_vote_subject: {
                required: textValidRequired,
                maxlength: textValidMaxLen + ' 255 ' + textSymbol
            },
            mail_vote_content: {
                required: textValidRequired
            }
        }
    });
    
    $('#add_nominee_form').validate({
        rules: {
            nominee_employee_id: {
                required: true
            }
        },
        messages: {
            nominee_employee_id: {
                required: textValidRequired
            }
        }
    });
    
    var dataEmployees = [];
    $('.mail_bcc').each(function (){
        var url = $(this).data('url');
        var voteId = $(this).data('voteId') || null;
        $(this).select2({
            ajax: {
                url: url,
                dataType: 'json',
                delay: 500,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page,
                        vote_id: voteId
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    data.items.map(function (item) {
                        dataEmployees[item.id] = item.name;
                    });
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 20) < data.total_count
                        }
                    };
                },
                cache: true,
                minimumInputLength: 1
            },
            selectOnClose: true
        });
    });
    
    $('body').on('keyup', '.select2-search input.select2-search__field', function(e) {
        var element = $(this).closest('.select2-container').prev('.mail_bcc');
        if (element.length > 0 && e.keyCode == 32)  {
           element.select2('close');
        }
    });
    
    if ($('#nominee_name').length > 0) {
        $('.mail_bcc').change(function () {
            $('#nominee_name').val(dataEmployees[$(this).val()]);
        });
    }

    selectSearchReload();
    
    $('#btn_sendmail').on('click', function () {
        if (CKEDITOR.instances.vote_content) {
            CKEDITOR.instances.vote_content.updateElement();
        }
        detectChangeField();
        
        setTimeout(function () {
            if ($('#change_data').val() == 1) {
                showModalError(textValidSaveBeforeSendMail);
            } else {
                $('#sendmail_modal label.error').remove();
                $('#sendmail_modal').modal('show');
            }
        }, 300);
    });
    
    $('#btn_send_mail_vote').on('click', function () {
        if (CKEDITOR.instances.vote_content) {
            CKEDITOR.instances.vote_content.updateElement();
        }
        detectChangeField();
        
        setTimeout(function () {
            if ($('#change_data').val() == 1) {
                $('a[href="#vote_info"]').click();
                showModalError(textValidSaveBeforeSendMail);
            } else {
                $('#modal_send_mail_vote label.error').remove();
                $('#modal_send_mail_vote').modal('show');
            }
        }, 300);
    });
    
    $('#vote_tabs li a').on('shown.bs.tab', function (e) {
        loadVoteTabs($(this));
    });
    
    $(document).ready(function () {
        //load vote tabs
        loadVoteTabs($('#vote_tabs li.active a'));
        
        //load ckeditor
        var arrayEditors = [];
        if ($('#mail_vote_content').length > 0) {
            arrayEditors.push('mail_vote_content');
        }
        if ($('#mail_content').length > 0) {
            arrayEditors.push('mail_content');
        }
        if ($('#vote_content').length > 0) {
            arrayEditors.push('vote_content');
        }
        var ckEditors = null;
        if (arrayEditors.length > 0) {
            var ckEditors = RKfuncion.CKEditor.init(arrayEditors);
        }

        if (ckEditors && ckEditors.vote_content) {
            setTimeout(function () {
                var oldVoteContent = ckEditors.vote_content.getData().trim();
                $('#vote_content').attr('data-value', oldVoteContent);
            }, 500);
            ckEditors.vote_content.on('key', function () {
                $('#vote_content-error').remove();
            });
        }
        
    });
    
    //load vote edit tab
    function loadVoteTabs(tab) {
        var tabId = tab.attr('href');
        var elTab = $(tabId);
        if (elTab.hasClass('loaded')) {
            return;
        }
        var gridContent = elTab.find('.grid-data-query-table');
        gridContent.html('<i class="fa fa-spin fa-refresh"></i>');
        var url = tab.data('url');
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    gridContent.html(data.html);
                    elTab.addClass('loaded');
                } else {
                    gridContent.html(data.message);
                }
            },
            error: function () {
                gridContent.text(textErrorMessage);
            }
        });
    }
    
    $('#nominator_modal, #voter_modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var url = button.data('url');
        var nomineeName = button.closest('tr').find('.td-nominee-name').text();
        var modal = $(this);
        modal.find('.nominee-name').text(nomineeName);
        var gridData = modal.find('.grid-data-query');
        var gridContent = modal.find('.grid-data-query-table');
        if (url == gridData.attr('data-url')) {
            return;
        }
        gridData.attr('data-url', url);
        gridContent.html('<i class="fa fa-spin fa-refresh"></i>');
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    gridContent.html(data.html);
                    $('.content-more').shortContent();
                } else {
                    gridContent.html(data.message);
                }
            },
            error: function () {
                gridContent.text(textErrorMessage);
            }
        });
    });
    
    //show edit box
    $('body').on('click', '.edit-input .edit_btn', function (e) {
        e.preventDefault();
        $('.edit-input:not(.error)').removeClass('i_show');
        var editInput = $(this).parent();
        if (!editInput.hasClass('i_show')) {
            editInput.find('.edit-value').height(editInput.height());
            editInput.find('.edit-value').width(editInput.width() - 8*2);
        }
        editInput.addClass('i_show');
        editInput.find('.edit-value').focus();
    });
    //update description
    $('body').on('change', '.edit-input .edit-value', function (e) {
        var loading = $('.desc-loading');
        var elThis = $(this).parent();
        if (elThis.hasClass('updating')) {
            return;
        }
        var elValue = elThis.find('.value');
        var elEdit = elThis.find('.edit-value');
        var url = elThis.attr('data-url');
        if (elEdit.val().trim().length > 500) {
            elThis.addClass('i_show error');
            showModalError(textValidMaxLen + ' 255 ' + textSymbol + ' ('+ elEdit.val().trim().length +' '+ textSymbol +')');
            return;
        }
        loading.removeClass('hidden');
        elThis.addClass('updating');
        $.ajax({
            url: url,
            type: 'PUT',
            data: {
                _token: _token,
                description: elEdit.val()
            },
            success: function (data) {
                if (data.success) {
                    elValue.text(elEdit.val());
                    elThis.removeClass('i_show error');
                } else {
                    elThis.addClass('i_show error');
                    showModalError(data.message);
                }
            },
            error: function () {
                elThis.addClass('i_show error');
                showModalError(textErrorMessage);
            },
            complete: function () {
                loading.addClass('hidden');
                elThis.removeClass('updating');
            }
        });
    });
    //close edit box while click outside
    $(document).on('click', function (e) {
        if ($(e.target).closest('#modal-warning-notification').length > 0) {
            return;
        }
        var editInput = $(e.target.closest('.edit-input'));
        var isShow = false;
        if (editInput.length > 0 && editInput.hasClass('i_show')) {
            isShow = true;
        }
        $('.edit-input:not(.error)').removeClass('i_show');
        if (isShow) {
            editInput.addClass('i_show');
        }
    });
    
    $('#check_all_team').on('click', function () {
        var checkText = $(this).attr('check-label');
        var notCheckText = $(this).attr('not-check-label');
        if ($(this).hasClass('checked')) {
            $(this).text(checkText);
            $('.check_team_item').prop('checked', false);
            $(this).removeClass('checked');
        } else {
            $(this).text(notCheckText);
            $('.check_team_item').prop('checked', true);
            $(this).addClass('checked');
        }
    });
    
    $('#team_modal').on('show.bs.modal', function (event) {
        $('.check_team_item').prop('checked', false);
        var button = $(event.relatedTarget);
        var field = button.data('field');
        var text = button.data('text');
        $(this).find('#btn_submit_team').data('field', field).data('text', text);
        var teamIds = $(field).val();
        if (teamIds) {
            for (var i in teamIds) {
                var teamId = teamIds[i];
                $('.check_team_item[value="'+ teamId +'"]').prop('checked', true);
            }
        }
    });
    
    $('#btn_submit_team').on('click', function () {
        var labelTeams = [];
        var htmlTeamIds = '';
        var elField = $($(this).data('field'));
        var elText = $($(this).data('text'));
        $('.check_team_item:checked').each(function () {
            var label = $(this).parent().text().trim();
            labelTeams.push(label);
            htmlTeamIds += '<option value="'+ $(this).val() +'" selected></option>';
        });
        elField.html(htmlTeamIds);
        if (labelTeams.length > 0) {
            var relate = elField.attr('data-relate');
            if (typeof relate != 'undefined') {
                $(relate).parent().find('label.error').remove();
            }
        }
        elText.val(labelTeams.join(', '));
    });
    
    // add jquery funtion short content
    $.fn.shortContent = function (settings) {
	
        var config = {
                showChars: 150,
                showLines: 3,
                ellipsesText: "...",
                moreText: textShowMore,
                lessText: textShowLess
        };

        if (settings) {
                $.extend(config, settings);
        }

        $(document).off("click", '.morelink');

        $(document).on({click: function () {

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

        return this.each(function () {
                var $this = $(this);
                if($this.hasClass("shortened")) return;

                $this.addClass("shortened");
                var content = $this.html();
                var moreContent = '';
                var arrLine = content.split("\n");
                var c = content, h = '';
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
        });

    };
    
})(jQuery);

var modal_warning = $('#modal-warning-notification');
var modal_confirm = $('#modal-warn-confirm');

// show notification errors
function showModalError(message) {
    if (typeof message == "undefined") {
        message = 'Error!';
    }
    modal_warning.find('.text-default').html(message);
    modal_warning.modal('show');
}

// show confirm modal
function showModalConfirm(message) {
    modal_confirm.find('.text-default').html(message);
    modal_confirm.modal('show');
}


