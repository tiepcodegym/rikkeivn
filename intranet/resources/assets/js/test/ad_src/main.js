var excel_file = $('#excel_file');
var loading_file = $('.loading_file');
var question_box = $('.question_box');
var list_question = $('#list_question');
var written_question = $('#list_written_question');
var optQuestionDataTable = {
    paging: false,
    info: false,
    ordering: false,
    order: [[ 1, 'asc' ]],
    initComplete: function () {
        initDataTableFilter(this);
    }
};
var tempEditQuestion = {
    id: null,
    order: null,
};

//init filter datatable
function initDataTableFilter(table) {
    table.api().columns().every( function () {
        var column = this;
        var excerptIdx = [0, 1, 2, 7];
        if (excerptIdx.indexOf(column.index()) == -1) {

            var select = $('<select class="select-search form-control"><option value="">&nbsp;</option></select>')
                .appendTo( $(column.header()).empty() )
                .on( 'change', function () {
                    var val = $.fn.dataTable.util.escapeRegex(
                        $(this).val()
                    );
                    val = val ? val.trim() : val;
                    column.search(val).draw();
                });
            var arrayVal = [];
            column.data().unique().sort().each( function ( d, j ) {
                d = d ? d.trim() : d;
                if (d && arrayVal.indexOf(d) < 0) {
                    arrayVal.push(d);
                    select.append('<option value="' + d + '">' + d + '</option>');
                }
            });

        }
    });
    selectSearchReload();
}

//sortable question
function sortableQuestion() {
    if (list_question.length > 0 && list_question.html().trim() != "" && typeof jQuery.ui != "undefined") {
        list_question.sortable({
            start: function (event, ui) {
                $(ui.item).addClass('dragging');
            },
            stop: function (event, ui) {
                list_question.find('.q_item').each(function (index) {
                    $(this).find('.q_order .num').text(parseInt(index) + 1);
                });
                $(ui.item).removeClass('dragging');
            },
            helper: function (e, ui) {
                ui.children().each(function() {  
                    $(this).width($(this).width());  
                });  
                return ui; 
            }
        });
    }
};

(function ($) {

    //init tab
    var testTab = RKSession.getItem('test_edit_tab');
    if (testTab) {
        $('.test-tabs .nav-tabs a[href="'+ testTab +'"]').click();
    }
    $('.btn-edit-test').click(function () {
        RKSession.removeItem('test_edit_tab');
    });

    $('#total_q').attr('max', countQuestion());
    
    sortableQuestion();
    
    //choose excel file
    excel_file.click(function (e) {
       $(this).val(''); 
       $('#import_result').html('');
    });
    var currentQuestionHtml = '', currentCategories = [], currentWrittenHtml = '';
    //show modal import
    $('#btn_modal_import').click(function () {
        excel_file.val('');
        currentQuestionHtml = '';
        currentWrittenHtml = '';
        currentCategories = [];
        $('#import_result').html('');
        $('#submit_import').prop('disabled', true);
    });
    //choosed file
    excel_file.on('change', function () {
        $('.check_all').prop('checked', false);
        var el_this = $(this);
        $('#test_form button[type="submit"]').prop('disabled', false);
        $('#select_q_error').addClass('hidden');
        var modalImport = $('#modal_import_file');
        
        if (!el_this.hasClass('loading')) {
            $('#has_upload').val('1');
            
            var url = el_this.data('url');
            var random_order = $('input[name="random_order"]').is(':checked');
            var random_answer = $('input[name="random_answer"]').is(':checked');
            var test_id = $('input[name="test_id"]').val();
            var formData = new FormData();
            formData.append('file', el_this[0].files[0]);
            formData.append('random_order', random_order);
            formData.append('random_answer', random_answer);
            formData.append('_token', _token);
            formData.append('lang', currentLangEdit);
            formData.append('test_id', test_id);
            el_this.prop('disabled', true);
            loading_file.removeClass('hidden');
            modalImport.addClass('importing');
            $.ajax({
               url: url,
               type: 'POST',
               data: formData,
               processData: false,
               contentType: false,
               cache: false,
               success: function (data) {
                    currentQuestionHtml = data.html;
                    currentWrittenHtml = data.writtenHtml;
                    $('#import_result').html(data.message);
                    if (data.html) {
                        $('#submit_import').prop('disabled', false);
                    }
                    currentCategories = data.categories;
                    submitImportQuestion(currentWrittenHtml);
               },
               error: function (error) {
                    var message = 'Error!';
                    if (typeof error.responseJSON != 'undefined') {
                        message = error.responseJSON;
                    }
                    $('#import_result').html('<p class="error">' + message + '<p>');
               },
               complete: function () {
                   el_this.prop('disabled', false);
                   el_this.removeClass('loading');
                   loading_file.addClass('hidden');
                   modalImport.removeClass('importing');
               }
            });
        }
    });
    
    window.onbeforeunload = function () {
        if (currentQuestionHtml || currentWrittenHtml) {
            return true;
        }
    };
    
    //cancel import question
    $('#cancel_import').click(function () {
        currentQuestionHtml = '';
        currentWrittenHtml = '';
        currentCategories = [];
    });
    
    //submit import question
    function submitImportQuestion(currentWrittenHtml) {
        if (currentWrittenHtml) {
            var tblWrittenHtml = '';
            $('.table-written-questions tbody tr').each(function () {
                if ($(this).find('.dataTables_empty').length < 1) {
                    tblWrittenHtml += $(this)[0].outerHTML;
                }
            });
            tblWrittenHtml += currentWrittenHtml;
            $('.table-written-questions').DataTable().clear().destroy();
            written_question.html(tblWrittenHtml);
            written_question.find('.q_item').each(function (index) {
                $(this).find('.q_order .num').text(parseInt(index) + 1);
            });
            sortableQuestion();
            $('.table-written-questions').DataTable(optQuestionDataTable);
        }
    }

    $('#submit_import').click(function () {
        if (!currentQuestionHtml && !currentWrittenHtml) {
            return;
        }
        var importType = $('input[name="option_import"]:checked').val();
        $('#modal_import_file').modal('hide');
        if (currentQuestionHtml) {
            if (importType == 'append') {
                var tblHtml = '';
                $('.table-multiple-choice tbody tr').each(function () {
                    if ($(this).find('.dataTables_empty').length < 1) {
                        tblHtml += $(this)[0].outerHTML;
                    }
                });
                tblHtml += currentQuestionHtml;
                //clear data table
                $('.table-multiple-choice').DataTable().clear().destroy();

                list_question.html(tblHtml);
                list_question.find('.q_item').each(function (index) {
                    $(this).find('.q_order .num').text(parseInt(index) + 1);
                });
            } else {
                //clear data table
                $('.table-multiple-choice').DataTable().clear().destroy();

                list_question.html(currentQuestionHtml);
            }
            sortableQuestion();
            selectSearchReload();
            $('.check_all').prop('checked', true);
            $('.check_item').prop('checked', true);
            $('#total_q').attr('max', countQuestion())
            $('pre code').each(function(idx, block) {
               hljs.highlightBlock(block);
             });
            //reload data table
            $('.table-multiple-choice').DataTable(optQuestionDataTable);
            initAudioPlayer();
        }
        
        if (currentCategories) {
            for (var key in currentCategories) {
                var optionHtml = '<option value="">&nbsp;</option>';
                var pushedOptions = [];
                if (importType == 'append' && typeof currentCollectCats[key] != 'undefined') {
                    for (var i in currentCollectCats[key]) {
                        optionHtml += '<option value="'+ i +'">'+ currentCollectCats[key][i] +'</option>';
                        pushedOptions.push(i);
                    }
                }
                var cats = currentCategories[key];
                if (cats) {
                    for (var i in cats) {
                        if (pushedOptions.indexOf(i) < 0) {
                            optionHtml += '<option value="'+ i +'">'+ cats[i] +'</option>';
                        }
                    }
                }
                var select = $('.display-option-row .category_' + key);
                var oldVal = select.val();
                select.html(optionHtml);
                select.val(oldVal);
            }
            $('.display-option-row').each(function () {
                genNumberQuestionType($(this));
            });
        }
    });
    
    //generate display option question number
    $('.display-option-row').each(function () {
        genNumberQuestionType($(this));
    });
    
    //event before close modal
    $('.modal').on('hide.bs.modal', function () {
        if ($(this).hasClass('importing')) {
            return false;
        }
        return true;
    });
    
    //select category on change
    $('body').on('change', '.display-option-row select.select-cat', function () {
        var row = $(this).closest('.display-option-row');
        genNumberQuestionType(row);
        isValidUniqueOptionRow(row);
    });
    
    //input question number change
    $('body').on('change', '.display-option-row input.input-option', function () {
        var row = $(this).closest('.display-option-row');
        if (isValidRequireOptionRow(row)) {
            isValidTotalDisplayQuestion();
        }
    });
    
    //add new display option row
    $('#add_display_option_btn').click(function (e) {
         e.preventDefault();
         var displayBox = $('#display_option_box');
         displayBox.find('.display-option-row').removeClass('error');
         displayBox.find('p.error').remove();
         var rowTpl = $('#display_option_tpl').clone().removeAttr('id').removeClass('hidden');
         if (displayBox.find('.display-option-row').length > 0) {
             rowTpl = displayBox.find('.display-option-row:last').clone();
         }
         var index = displayBox.find('.display-option-row').length;
         rowTpl.find('select.select-cat').each(function () {
             $(this).val('');
             var catType = $(this).data('cat');
             $(this).attr('name', 'display_option['+ index +']['+ catType +']');
         });
         rowTpl.find('.total-option').val('');
         rowTpl.find('.input-option').val(1).attr('name', 'display_option['+ index +'][value]');
         displayBox.append(rowTpl);
    });
    
    //delete display option row
    $('body').on('click', '.btn-del-row', function (e) {
        e.preventDefault();
        var row = $(this).closest('.display-option-row');
        row.next('p.error').remove();
        row.remove();
        var displayBox = $('#display_option_box');
        displayBox.find('.display-option-row').each(function (index) {
            $(this).find('select.select-cat').each(function () {
                var catType = $(this).data('cat');
                $(this).attr('name', 'display_option['+ index +']['+ catType +']');
            });
            $(this).find('.input-option').attr('name', 'display_option['+ index +'][value]');
        });
    });
    
    $('#total_q').on('change', function () {
        isValidTotalDisplayQuestion();
    });
    
    //auto generate question type
    function genNumberQuestionType(row) {
        var trSelector = '';
        row.find('select.select-cat').each(function () {
            var value = $(this).val();
            if (value) {
                trSelector += '[data-cat-' + $(this).data('cat') + '="'+ value +'"]';
            }
        });
        var total = list_question.find(trSelector).length;
        row.find('.total-option').val(total);
        row.find('.input-option').attr('max', total).prop('disabled', (total === 0));
    }
    
    //validate required input number option row
    function isValidRequireOptionRow(row) {
        row.removeClass('error');
        var error = row.next('p.error');
        error.remove();
        error = $('<p class="error"></p>');
        
        var input = row.find('.input-option');
        var inputVal = parseInt(input.val());
        //check required
        if (input.val().trim() == '') {
            row.after(error);
            error.text(text_error_input_number_question);
            row.addClass('error');
            return false;
        //check min value
        } else if (inputVal < parseInt(input.attr('min'))) {
            row.after(error);
            error.text(please_input_value_not_less_than + ' ' + input.attr('min'));
            row.addClass('error');
            return false;
        //check max value
        } else if (inputVal > parseInt(input.attr('max'))) {
            row.after(error);
            error.text(please_input_value_not_greater_than + ' ' + input.attr('max'));
            row.addClass('error');
            return false;
        }
        return true;
    }
    
    //get total detail question
    function isValidTotalDisplayQuestion(){
        //check total question
        var error = $('<p class="error"></p>');
        var lastRow = $('#display_option_box .display-option-row').last();
        lastRow.next('p.error').remove();
        lastRow.removeClass('error');
        
        var totalQ = parseInt($('#total_q').val());
        var totalInput = 0;
        $('#display_option_box input.input-option').each(function () {
            totalInput += parseInt($(this).val());
        });
        
        if (totalInput > totalQ) {
            error.text(total_question_has_exceeded_the_limit);
            lastRow.after(error);
            lastRow.addClass('error');
            return false;
        }
        return true;
    }
    
    //validate unique display option row
    function isValidUniqueOptionRow(row) {
        row.removeClass('error');
        var error = row.next('p.error');
        error.remove();
        error = $('<p class="error"></p>');
        
        var selectCats = [];
        row.find('select.select-cat').each(function () {
            if ($(this).val()) {
                selectCats.push(parseInt($(this).val()));
            }
        });
        if (selectCats.length != row.find('select.select-cat').length) {
            return true;
        }
        
        var errorUnique = false;
        $('#display_option_box .display-option-row').not(row).each(function () {
            var optSelectCats = [];
            $(this).find('select.select-cat').each(function () {
                if ($(this).val()) {
                    optSelectCats.push(parseInt($(this).val()));
                }
            });
            var diff = $(selectCats).not(optSelectCats).get();
            if (!errorUnique && diff.length == 0) {
                errorUnique = true;
            }
        });
        if (errorUnique) {
            error.text(text_error_unique_display_option);
            row.after(error);
            row.addClass('error');
            return false;
        }
        return true;
    }
    
    //required select type category
    function isValidRequiredSelectCat(){
        var errorRequired = false;
        $('#display_option_box .display-option-row').each(function () {
            var row = $(this);
            row.removeClass('error');
            var error = row.next('p.error');
            error.remove();
            error = $('<p class="error"></p>');
            
            var itemError = true;
            row.find('select.select-cat').each(function () {
                if ($(this).val()) {
                    itemError = false;
                    return false;
                }
            });
            if (itemError) {
                error.text(text_error_required_select_cat);
                row.after(error);
                row.addClass('error');
            }
            if (itemError && !errorRequired) {
                errorRequired = true;
            }
        });
        return !errorRequired;
    }
    
    // submit test form
    $('body').on('submit', '#test_form', function () {
        var form = $(this);
        var btn = $(this).find('button[type="submit"]');
        
        if ($('.q_item .check_item:checked').length < 1) {
            $('.test-tabs ul li:eq(1) a').click();
            $('#select_q_error').removeClass('hidden');
            $('#test_form button[type="submit"]').prop('disabled', false);
            $('html, body').animate({
                scrollTop: $('#select_q_error').offset().top
            });
            btn.prop('disabled', false);
            return false;
        } else {
            $('#select_q_error').addClass('hidden');
        }
        if ($('.time_start .error').length > 0 || $('#total_q').parent().find('.error').length > 0 || $('.time_end .error').length > 0) {
            $('.test-tabs ul li:first a').click();
            btn.prop('disabled', false);
            return false;
        }
        //display validate
        if ($('#check_total').is(':checked')) {
            if (isValidRequiredSelectCat()) {
                isValidTotalDisplayQuestion();
            }
            if ($('#display_option_box .display-option-row.error').length > 0) {
                $('.test-tabs ul li:eq(2) a').click();
                btn.prop('disabled', false);
                return false;
            }
        }
        //checked questions
        var q_checked_html = '';
        var changeOrderHtml = '';
        list_question.find('.q_item .check_item:checked').each(function (index) {
            q_checked_html += '<input type="hidden" name="q_items['+ index +']" value="'+ $(this).val() +'" />';
            var oldOrder = $(this).closest('tr').attr('data-order');
            changeOrderHtml += '<input type="hidden" name="change_q_order['+ index +']" value="'+ oldOrder +'" />';
        });
        
        currentQuestionHtml = '';
        
        if (btn.data('noti')) {
            bootbox.confirm({
                message: btn.data('noti'),
                className: 'modal-warning',
                buttons: {
                    confirm: {
                        label: confirmYes,
                    },
                    cancel: {
                        label: confirmNo,
                    },
                },
                callback: function (result) {
                    if (result) {
                        $('#sorted_question').html(q_checked_html);
                        $('#sorted_question').append(changeOrderHtml);
                        form[0].submit();
                    } else {
                        btn.prop('disabled', false);
                        $('#sorted_question').html('');
                    }
                }
            });
            return false;
        } else {
            $('#sorted_question').html(q_checked_html);
            $('#sorted_question').append(changeOrderHtml);
            return true;
        }
    });
    
    $('#group_types').change(function () {
        var selected = $(this).find('option:selected');
        $('.data-target').prop('disabled', true).val('');
        $(selected.attr('data-target')).prop('disabled', false);
        $('.data-target').select2();
        $('#subjects-error').remove();
    });
    
    function countQuestion() {
        var len = $('.q_item').length;
        if (len == 0) {
            return 1000;
        }
        return len;
    }

    function compareTime() {
        var start_time = $('#time_start').val();
        var end_time = $('#time_end').val();
        if (!start_time || !end_time) {
            return true;
        }

        start_time = new Date(start_time);
        end_time = new Date(end_time);

        var diff = end_time - start_time;
        return diff >= 0;
    }

    $.validator.addMethod('compareTime', function (value, element, param) {
        if(compareTime()) {
            return true;
        }
    }, time_end_must_greater_than_time_start);
    jQuery.extend(jQuery.validator.messages, {
        required: requiredText,
    });
    //validate form
    $('.validate_form').validate({
        ignore: ".ignore",
        rules: {
            name: {
                required: true
            },
            time: {
                required: true,
                number: true,
                min: 1
            },
            type_id: {
                required: true
            },
            total_question: {
                required: function () {
                    return $('#check_total').is(":checked");
                },
                number: true,
                min: 1,
            },
            time_start: {
                required: function () {
                    return $('#set_time').is(":checked");
                },
            },
            time_end: {
                required: function () {
                    return $('#set_time').is(":checked");
                },
                compareTime: true,
            },
            min_point: {
                required: function () {
                    return $('#set_min_point').is(':checked');
                },
                number: true,
                min: 1,
                max: function() {
                    return ($('#set_min_point').is(':checked') && totalQuestion !== 'undefined')  ? totalQuestion : null;
                },
            },
        },
        errorPlacement: function(error, element) {
            var name = element.attr('name');
            if (name == 'total_question') {
                $('.test-tabs ul li:eq(2) a').click();
            } else {
                $('.test-tabs ul li:first a').click();
            }
            if (name == 'time_start' || name == 'time_end') {
                error.insertAfter(element.closest('.input-group'));
            } else {
                error.insertAfter(element);
            }
        }
    });

    $('.test-tabs ul li a').on('click', function () {
        var tab = $(this).attr('href');
        if (tab == '#general_tab') {
            selectSearchReload();
        }
        RKSession.setItem('test_edit_tab', tab);
    });
    
    if ($('.check_all').length > 0) {
        if ($('.check_item').length > 0 && $('.check_item:checked').length == $('.check_item').length) {
            $('.check_all').prop('checked', true);
        } else {
            $('.check_all').prop('checked', false);
        }
        $('.check_all').on('change', function () {
            if ($(this).is(':checked')) {
                $('.check_item').prop('checked', true);
                $('#select_q_error').addClass('hidden');
            } else {
                $('.check_item').prop('checked', false);
            }
        });
        $('body').on('change', '.check_item', function(){
            var item_length = $('.check_item').length;
            $('.check_all').prop('checked', $('.check_item:checked').length === item_length);
            
            $('#select_q_error').addClass('hidden');
            
            //sub check item
            var trItem = $(this).closest('tr');
            var checked = $(this).is(':checked');
            if (trItem.hasClass('tr-parent')) {
                var childItem = trItem.parent().find('.tr-child[data-id="'+ trItem.data('id') +'"]');
                childItem.find('.check_item').prop('checked', checked);
            }
        });
    }
    
    //export test result
    $('#btn_export_result').click(function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var btn = $(this);
        var checkItems = $('#tbl_test_result tr .check_item:checked');
        // if (checkItems.length < 1) {
        //     btn.prop('disabled', false);
        //     bootbox.alert({
        //         message: 'None item checked',
        //         className: 'modal-danger'
        //     });
        //     return false;
        // }
        
        var url = $(this).data('url');
        var form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', url);
        
        var field = document.createElement('input');
        field.setAttribute('type', 'hidden');
        field.setAttribute('name', '_token');
        field.setAttribute('value', _token);
        form.appendChild(field);
        
        checkItems.each(function () {
            var input = document.createElement('input');
            input.setAttribute('type', 'hidden');
            input.setAttribute('name', 'result_ids[]');
            input.setAttribute('value', $(this).val());
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        form.remove();
    });

    //delete result
    $('#btn_mass_del').click(function (e) {
        e.preventDefault();
        var _this = $(this);
        var url = _this.data('url');
        var checkItems = $('.check_item:checked');
        if (checkItems.length < 1) {
            bootbox.alert({
                className: 'modal-danger',
                message: textNoneItemSelected,
                buttons: {
                    ok: {
                        label: confirmYes,
                    },
                },
            });
            return false;
        }
        var ids = [];
        checkItems.each(function () {
            ids.push($(this).val());
        });
        bootbox.confirm({
            className: 'modal-warning',
            message: _this.data('noti'),
            buttons: {
                confirm: {
                    label: confirmYes,
                },
                cancel: {
                    label: confirmNo,
                },
            },
            callback: function (result) {
                if (result) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            item_ids: ids,
                            _token: _token,
                        },
                        success: function(data){
                            window.location.reload();
                        },
                        error: function(err){
                            window.location.reload();
                        },
                    });
                }
            },
        });
    });

    // multiple items action
    $('.m_action_btn').on('click', function(e){
        e.preventDefault();
        var _this = $(this);
        var href = _this.attr('href');
        var action = _this.attr('action');
        var checkItems = $('.check_item:checked');
        if (checkItems.length < 1) {
            bootbox.alert({
                className: 'modal-danger',
                message: textNoneItemSelected,
                buttons: {
                    ok: {
                        label: confirmYes,
                    },
                },
            });
            return false;
        }
        var ids = [];
        checkItems.each(function () {
            ids.push($(this).val());
        });
        bootbox.confirm({
            className: 'modal-warning',
            message: _this.data('noti'),
            buttons: {
                confirm: {
                    label: confirmYes,
                },
                cancel: {
                    label: confirmNo,
                },
            },
            callback: function (result) {
                if (result) {
                    $.ajax({
                        url: href,
                        type: 'POST',
                        data: {
                            action: action,
                            item_ids: ids,
                            _token: _token
                        },
                        success: function(data){
                            window.location.reload();
                        },
                        error: function(err){
                            window.location.reload();
                        }
                    });
                }
            },
        });
    });
    
    var elQuestionContent = $('.editor_question_content');
    if (typeof CKEDITOR != 'undefined' && elQuestionContent.length > 0) {
        //popup edit question
        var questionContentOpts = {
            toolbar: [
                { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline'] },
                { name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'Undo', 'Redo' ] },
                { name: 'links', items: [ 'Link', 'Unlink' ] },
                { name: 'insert', items: [ 'HorizontalRule', 'SpecialChar', 'Rkimage', 'Rkaudio' ] },
                { name: 'styles', items: [ 'Format', 'FontSize' ] },
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
                '/',
                { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align' ], items: [ 'NumberedList', 'BulletedList', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                { name: 'document', groups: [ 'mode'], items: [ 'Mathjax', 'CodeSnippet', '-', 'Source'] }
            ],
            tabSpaces: 4,
            extraPlugins: 'justify,colorbutton,codesnippet,mathjax,font,image2,rkimg,html5video,widget,widgetselection',
            height: '200px',
            mathJaxLib: 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.0/MathJax.js?config=TeX-AMS_HTML',
            extraConfig: {
                _token: _token,
                urlUploadImage: _urlUploadImage
            }
        };
        elQuestionContent.each(function () {
            var row = $(this).attr('rows');
            questionContentOpts.height = row * 40;
            CKEDITOR.replace($(this).attr('id'), questionContentOpts);
        });
    }
    
    $('body').on('click', '.btn-close-modal', function (e) {
       e.preventDefault();
       var modal = $(this).closest('.rk-modal');
       modal.fadeOut(300);
    });
    
    //ajax delete question
    $('body').on('click', '.btn-delete-question', function (e) {
        e.preventDefault();
        //var url = $(this).data('url');
        var qId = $(this).data('id');
        var elQuestion = $('#question_' + qId);
        //if (!url) {
        //    return;
        //}
        var btn = $(this);
        
        bootbox.confirm({
            message: text_confirm_delete,
            className: 'modal-danger',
            buttons: {
                confirm: {
                    label: confirmYes,
                },
                cancel: {
                    label: confirmNo,
                },
            },
            callback: function (result) {
                if (result) {
                    btn.prop('disabled', true);
                    elQuestion.css('background', '#f9eeb6');

                    /*$.ajax({
                        url: url,
                        type: 'delete',
                        data: {
                            _token: _token
                        },
                        success: function () {*/
                    var table = $('.table-multiple-choice').DataTable();
                    table.row(elQuestion).remove().draw();
                    initDataTableFilter($('.table-multiple-choice').dataTable());
                    $('.table-multiple-choice .q_item').each(function (index) {
                        $(this).find('.q_order .num').text(index + 1);
                    });
                    /*},
                    error: function (error) {
                        elQuestion.removeAttr('style');
                        showModalError(error.responseJSON);
                    },
                    complete: function () {
                        btn.prop('disabled', false);
                    }
                });*/
                } else {
                    btn.prop('disabled', false);
                }
            }
        });
    });

    //ajax delete written question
    $('body').on('click', '.btn-delete-written-question', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        var qId = $(this).data('id');
        var elQuestion = $('#question_' + qId);
        var btn = $(this);
        if (!url) {
            return;
        }

        bootbox.confirm({
            message: text_confirm_delete,
            className: 'modal-danger',
            buttons: {
                confirm: {
                    label: confirmYes,
                },
                cancel: {
                    label: confirmNo,
                },
            },
            callback: function (result) {
                if (result) {
                    btn.prop('disabled', true);
                    elQuestion.css('background', '#f9eeb6');

                    $.ajax({
                        url: url,
                        type: 'delete',
                        data: {
                            _token: _token
                        },
                        success: function () {
                            var table = $('.table-written-questions').DataTable();
                            table.row(elQuestion).remove().draw();
                            initDataTableFilter($('.table-written-questions').dataTable());
                            $('.table-written-questions .q_item').each(function (index) {
                                $(this).find('.q_order .num').text(index + 1);
                            });
                        },
                        error: function (error) {
                            elQuestion.removeAttr('style');
                            showModalError(error.responseJSON);
                        },
                        complete: function () {
                            btn.prop('disabled', false);
                        }
                    });
                } else {
                    btn.prop('disabled', false);
                }
            }
        });
    });

    if (typeof hljs != 'undefined') {
        hljs.initHighlightingOnLoad();
    }
    
    function updateMathContent() {
        MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
    }
    
    selectSearchReload();
    
    //window edit script
    if ($('#edit_question_window').length > 0) {    
        var answerEditorOpt = {
                toolbar: [
                    { name: 'insert', items: [ 'SpecialChar', 'Rkimage' ] },
                    { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                    { name: 'document', groups: [ 'mode'], items: [ 'CodeSnippet', '-', 'Source' ] }
                ],
                extraPlugins: 'codesnippet,image2,rkimg',
                height: '100px',
                enterMode : CKEDITOR.ENTER_BR,
                extraConfig: {
                    _token: _token,
                    urlUploadImage: _urlUploadImage
                }
            };

        if (!isType1) {
            $('.ans-content-col .ans_box textarea').each(function () {
                CKEDITOR.replace($(this).attr('id'), answerEditorOpt);
            });
        }

        //add answer
        $('.btn-add-answer').click(function (e) {
            e.preventDefault();
            $('#a-required-error').addClass('hidden');

            var ansLen = $('.ans-content-col .ans_box').length;
            var idxNew = 'new_' + ansLen;
            var ansBox = $('#ans_box_tpl').clone().removeAttr('id').attr('data-new', idxNew);
            var nextLabel = '';
            if (!isType1) {
                var lastLabel = $('.ans-content-col .ans_box:last .aw_label');
                if (lastLabel.find('input').length > 0) {
                    lastLabel = lastLabel.find('input').val().trim()[0];
                } else {
                    lastLabel = lastLabel.text().trim()[0];
                }
                if (!lastLabel) {
                    lastLabel = '@';
                }
                //generate next label (A->B)
                nextLabel = String.fromCharCode(lastLabel.charCodeAt(0) + 1);
            } else {
                ansBox.find('.aw_label').html('');
            }
            ansBox.find('.aw_label input').attr('name', 'answers_new['+ ansLen +'][label]').val(nextLabel);
            ansBox.find('textarea').attr('id', 'answer_new_' + ansLen).html('')
                    .attr('name', 'answers_new['+ ansLen +'][content]');
            ansBox.appendTo('.ans-content-col');

            if (isType2) {
                $('.ans-check-col select').append('<option value="'+ idxNew +'" data-new="'+ idxNew +'">'+ nextLabel +'</option>');
                $('#ans_type2_tpl select').append('<option value="'+ idxNew +'" data-new="'+ idxNew +'">'+ nextLabel +'</option>');
                CKEDITOR.replace('answer_new_' + ansLen, answerEditorOpt);
            } else if (!isType1) {
                CKEDITOR.replace('answer_new_' + ansLen, answerEditorOpt);
                var typeCheckAns = 'radio';
                if ($('#check_multi_choice').is(':checked')) {
                    typeCheckAns = 'checkbox';
                }
                var checkBox = '<div class="ans_check" data-new="'+ idxNew +'">' +
                                '<label><input type="'+ typeCheckAns +'" name="answers_new_correct[]" value="'+ ansLen +'"> ' +
                                    '<span class="ans_label">'+ nextLabel +'</span></label>' +
                            '</div>';
                $('.ans-check-col').append(checkBox);
            }

        });

        //del answer
        $('body').on('click', '.btn-del-answer', function (e) {
            e.preventDefault();
            if ($('.ans-content-col .ans_box').length == 1) {
                return;
            }
            var ansBox = $(this).closest('.ans_box');
            var newId = ansBox.attr('data-new');
            if (!isType2) {
                $('.ans-check-col .ans_check[data-new="'+ newId +'"]').remove();
            } else {
                $('.ans-check-col select option[data-new="'+ newId +'"]').remove();
                $('#ans_type2_tpl select option[data-new="'+ newId +'"]').remove();
            }
            ansBox.remove();
        });
        
        //change label answer
        $('body').on('change', '.ans_box .aw_label input', function () {
            var newId = $(this).closest('.ans_box').attr('data-new');
            $('.ans-check-col select option[data-new="'+ newId +'"]').text($(this).val());
            $('.ans-check-col div[data-new="'+ newId +'"] .ans_label').text($(this).val());
            $('#a-label-error').addClass('hidden');
        });
        
        //add child box with type2
        $('body').on('click', '.btn-add-qchild', function (e) {
            e.preventDefault();
            var qChildBox = $('#ans_type2_tpl').clone().removeAttr('id');
            var qIndex = $('.ans-check-col .child_num').length;
            qChildBox.find('.child_num').text(qIndex + 1);
            qChildBox.find('select').attr('name', 'answers_new_correct['+ (qIndex + 1) +']').val('');
            var qChildContent = qChildBox.find('textarea');
            qChildContent.attr('id', 'edit_question_content_new_' + (qIndex + 1)).attr('name', 'childs_new_content['+ (qIndex + 1) +']');
            qChildBox.appendTo('.ans-check-col');
            questionContentOpts.height = 120;
            CKEDITOR.replace('edit_question_content_new_' + (qIndex + 1), questionContentOpts);
        });
        
        //del child box with type2
        $('body').on('click', '.btn-del-qchild', function (e) {
            e.preventDefault();
            if ($('.qchild-box').length == 1) {
                return;
            }
            $(this).closest('.qchild-box').remove();
            $('.qchild-box').each(function (idx) {
                $(this).find('.child_num').text(idx + 1);
            });
        });

        $('#form_edit_question').submit(function () {
            var btn = $(this).find('button[type="submit"]');
            if ($('.ans-content-col .ans_box').length < 1) {
                $('#a-required-error').removeClass('hidden');
                btn.prop('disabled', false);
                return false;
            }
            
            var error = false;
            var requireFields = [
                {
                    element: '.editor_question_content',
                    errorElm: '#q-content-error',
                    extraFunc: function () {
                        $('.editor_question_content').each(function () {
                            var idText = $(this).attr('id');
                            CKEDITOR.instances[idText].updateElement();
                        });
                        CKEDITOR.instances['edit_question_content'].on('change', function () {
                            $('#q-content-error').addClass('hidden');
                        });
                        return true;
                    }
                },
                {
                    element: '.ans-content-col .aw_label input',
                    errorElm: '#a-label-error',
                    extraFunc: function () {
                        var existsLabel = [];
                        var labelError = $('#a-label-exists-error');
                        var errorField = false;
                        $('.ans-content-col .aw_label input').each(function () {
                            if (existsLabel.indexOf($(this).val()) == -1) {
                                existsLabel.push($(this).val());
                            } else {
                                if (!errorField) {
                                    errorField = true;
                                }
                            }
                        });
                        if (errorField) {
                            labelError.removeClass('hidden');
                        } else {
                            labelError.addClass('hidden');
                        }
                        return !errorField;
                    }
                },
                {
                    element: '.ans-content-col .ans_box textarea',
                    errorElm: '#a-content-error',
                    extraFunc: function() {
                        $('.ans_box textarea').each(function () {
                            if (typeof $(this).attr('id') != 'undefined') {
                                var elementId = $(this).attr('id');
                                if (typeof CKEDITOR.instances[elementId] != 'undefined') {
                                    CKEDITOR.instances[elementId].updateElement();
                                    CKEDITOR.instances[elementId].on('change', function () {
                                        $('#a-content-error').addClass('hidden');
                                    });
                                } else {
                                    $('.ans-content-col .ans_box textarea').on('change', function () {
                                        $('#a-content-error').addClass('hidden');
                                    });
                                }
                            }
                            return true;
                        });
                    }
                },
            ];
            //custom field of test type
            if (isType2) {
                requireFields.push({
                    element: '.ans-check-col select',
                    errorElm: '#ans-select-error'
                });
            } else if (!isType1) {
                requireFields.push({
                    element: '.ans-check-col .ans_check input',
                    errorElm: '#ans-select-error'
                });
            }
            //check is error in fields required
            var error = false;
            for (var i in requireFields) {
                var field = requireFields[i];
                if (typeof field.extraFunc != 'undefined') {
                    if (typeof field.extraFunc() != 'undefined' 
                            && !field.extraFunc()) {
                        error = true;
                        btn.prop('disabled', false);
                        return !error;
                    }
                }
                var fieldError = false;
                if (field.element === '.ans-check-col .ans_check input') {
                    if ($(field.element + ':checked').length < 1) {
                        fieldError = true;
                    }
                } else if (field.element === '.editor_question_content') {
                    var noContent = true;
                    $(field.element).each(function () {
                        if ($(this).val().trim()) {
                            noContent = false;
                        }
                    });
                    fieldError = noContent;
                } else {
                    $(field.element).each(function () {
                        if (!$(this).val().trim()) {
                            fieldError = true;
                        }
                    });
                }
                if (fieldError) {
                    $(field.errorElm).removeClass('hidden');
                    error = true;
                } else {
                    $(field.errorElm).addClass('hidden');
                }
                
                $(field.element).on('change', function () {
                    $(field.errorElm).addClass('hidden');
                });
            }
            //check required insert correct answer
            if (!error && !isType1) {
                if ($('.ans-check-col select').length < 1 && $('.ans-check-col .ans_check input').length < 1) {
                    $('#ans-select-error').removeClass('hidden');
                    error = true;
                }
            }
            if (error) {
                btn.prop('disabled', false);
            }
            if (isType4) {
                return true;
            }
            return !error;
        });

        CKEDITOR.instances['edit_question_content'].on('change', function () {
            $('#q-content-error').addClass('hidden');
        });

        $('#btn_close_window').click(function () {
            window.close();
        });
    }
    
    //create question popup windows
    $('.btn-create-question').click(function () {
        tempEditQuestion = {
            id: null,
            order: null,
        };
        var btn = $(this);
        var popup = popupWinEdit(btn.data('url'), $(window).width() * 0.8, $(window).height() * 0.9);
        $(window).on('beforeunload', function () {
            popup.close();
        });
    });
    
    //if edit page check all question
    if (typeof isEdit != 'undefined' && isEdit) {
        $('.table-multiple-choice .check_all').click();
    }

    /*
     * change edit lang question
     */
    $('body').on('change', '#question_change_lang', function () {
        var url = $(this).attr('data-url');
        if (!url) {
            return;
        }

        var form = document.createElement('form');
        form.setAttribute('method', 'get');
        form.setAttribute('action', url);
        var formFields = [];
        var reqParams = RKfuncion.general.paramsFromUrl();
        reqParams['lang'] = $(this).val();
        $.each(reqParams, function (name, value) {
            formFields.push({
                name: name,
                value: value,
            });
        });
        for (var i in formFields) {
            var field = formFields[i];
            var fieldElm = document.createElement('input');
            fieldElm.setAttribute('type', 'hidden');
            fieldElm.setAttribute('name', field.name);
            fieldElm.setAttribute('value', field.value);
            form.appendChild(fieldElm);
        }
        document.body.appendChild(form);
        form.submit();
        form.remove();
    });

    //apply change test type
    $('body').on('change', '#question_type', function () {
        if ($(this).data('url') == 'undefined') {
            return false;
        }
        
        var form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', $(this).data('url'));
        var formFields = [
            {name: 'question[type]', value: $(this).val()},
            {name: 'id', value: $(this).data('id')},
            {name: '_token', value: _token}
        ];
        for (var i in formFields) {
            var field = formFields[i];
            var fieldElm = document.createElement('input');
            fieldElm.setAttribute('type', 'hidden');
            fieldElm.setAttribute('name', field.name);
            fieldElm.setAttribute('value', field.value);
            form.appendChild(fieldElm);
        }
        document.body.appendChild(form);
        form.submit();
        form.remove();
    });
    
    //btn edit each question
    $('body').on('click', '.btn-popup', function () {
        var trItem = $(this).closest('tr');
        tempEditQuestion = {
            id: trItem.attr('data-id'),
            order: trItem.find('.q_order .num').text(),
        };
        var popup = popupWinEdit($(this).data('url'), $(window).width() * 0.8, $(window).height() * 0.9);
        $(window).on('beforeunload', function () {
            popup.close();
        });
    });
    
    function popupWinEdit(url, width, height) {
        var leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
        var topPosition = (window.screen.height / 2) - ((height / 2) + 50);
        //Open the window.
        return window.open(url, "edit_question",
        "status=no,height=" + height + ",width=" + width + ",resizable=yes,left="
        + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY="
        + topPosition + ",toolbar=no,menubar=no,scrollbars=1,location=no,directories=no");
    }
    
    //show modal detail question content
    $('.btn-show-content').click(function () {
        var qOrder = $(this).prev('strong').text();
        var qContent = $(this).next('.q_content');
        var modal = $('#modal_detail_question');
        modal.modal('show');
        modal.find('.q_num').text(qOrder);
        modal.find('.modal-body').html(qContent[0].outerHTML);
        modal.find('.q_content').removeClass('hidden');
    });
    
    var modalCopyTo = $('#modal_copy_to');
    modalCopyTo.on('show.bs.modal', function () {
        if ($('#list_question .check_item:checked').length < 1) {
            bootbox.alert({
                className: 'modal-danger',
                message: textNoneItemSelected,
                buttons: {
                    ok: {
                        label: confirmYes,
                    },
                },
            });
            return false;
        }
        
        $('#submit_copy').prop('disabled', true);
        $('#select_search_test').val('');
        var url = $('#select_search_test').data('remote-url');
        $('#select_search_test').select2({
            minimumInputLength: 0,
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 20) < data.total_count
                        }
                    };
                },
                cache: true
            }
        });
    });
    
    $('#select_search_test').on('change', function () {
        if ($(this).val()) {
            $('#submit_copy').prop('disabled', false);
        } else {
            $('#submit_copy').prop('disabled', true);
        }
    });
    //confirm copy question to test
    $('#submit_copy').click(function (e) {
        e.preventDefault();
        var testId = $('#select_search_test').val();
        if (!testId) {
            return false;
        }
        var qIds = [];
        list_question.find('.q_item .check_item:checked').each(function () {
           qIds.push($(this).val()); 
        });
        if (qIds.length < 1) {
            bootbox.alert({
                message: textNoneItemSelected,
                className: 'modal-danger',
                buttons: {
                    ok: {
                        label: confirmYes,
                    },
                },
            });
            return false;
        }
        
        var confirmMess = $(this).data('noti');
        var btn = $(this);
        var modalCopy = $('#modal_copy_to');
        
        bootbox.confirm({
            message: confirmMess,
            className: 'modal-warning',
            buttons: {
                confirm: {
                    label: confirmYes,
                },
                cancel: {
                    label: confirmNo,
                },
            },
            callback: function (result) {
                if (result) {
                    btn.prop('disabled', true);
                    modalCopy.addClass('importing');
                    
                    $.ajax({
                        url: btn.data('url'),
                        type: 'post',
                        data: {
                            _token: _token,
                            question_ids: qIds,
                            test_id: testId,
                            option_copy: $('input[name="option_copy"]:checked').val() 
                        },
                        success: function (data) {
                            bootbox.alert({
                                message: data.message, 
                                className: 'modal-success',
                                buttons: {
                                    ok: {
                                        label: confirmYes,
                                    },
                                },
                            });
                        },
                        error: function (error) {
                            bootbox.alert({
                                message: error.responseJSON.message,
                                className: 'modal-danger',
                                buttons: {
                                    ok: {
                                        label: confirmYes,
                                    },
                                },
                            });
                        },
                        complete: function () {
                            btn.prop('disabled', false);
                            modalCopy.removeClass('importing');
                        }
                    });
                } else {
                    btn.prop('disabled', false);
                }
            }
        });
    });
    
    //export excel question
    $('#btn_export_question').click(function (e) {
        e.preventDefault();
        var elmQChecked = list_question.find('.q_item .check_item:checked');
        var wrtQChecked = written_question.find('.q_item .check_item:checked');
        if (elmQChecked.length < 1 && wrtQChecked < 1) {
            bootbox.alert({
                message: textNoneItemSelected,
                className: 'modal-danger',
                buttons: {
                    ok: {
                        label: confirmYes,
                    },
                },
            });
            return false;
        }
        
        var url = $(this).data('url');
        var form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', url);
        
        var field = document.createElement('input');
        field.setAttribute('type', 'hidden');
        field.setAttribute('name', '_token');
        field.setAttribute('value', _token);
        form.appendChild(field);
        
        elmQChecked.each(function () {
            var input = document.createElement('input');
            input.setAttribute('type', 'hidden');
            input.setAttribute('name', 'questions[]');
            input.setAttribute('value', $(this).val());
            form.appendChild(input);
        });
        wrtQChecked.each(function () {
            var input = document.createElement('input');
            input.setAttribute('name', 'written[]');
            input.setAttribute('value', $(this).val());
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    });
    
    //check disable multichoice
    $('#check_multi_choice').on('change', function () {
        var inputAnsCheck = $('.ans-check-col .ans_check input');
        if ($(this).is(':checked')) {
            inputAnsCheck.attr('type', 'checkbox');
        } else {
            inputAnsCheck.attr('type', 'radio');
        }
    });
    
    //view more question content
    $('body').on('click', '.q_view_more', function (e) {
        e.preventDefault();
        var parent = $(this).closest('.q_content_toggle');
        var fullText = $(this).data('fullText');
        var shortText = $(this).data('shortText');
        if (parent.hasClass('q_show')) {
            parent.removeClass('q_show');
            $(this).text('[' + fullText + ']');
        } else {
            parent.addClass('q_show');
            $(this).text('[' + shortText + ']');
        }
    });

    //reset random test
    $('body').on('click', '.btn-reset-random', function (e) {
        e.preventDefault();
        var btn = $(this);
        var checkItems = $('#list_test_tbl .check_item:checked');
        if (checkItems.length < 1) {
            bootbox.alert({
                className: 'modal-danger',
                message: textNoneItemSelected,
                buttons: {
                    ok: {
                        label: confirmYes,
                    },
                },
            });
            return false;
        }
        bootbox.confirm({
            className: 'modal-warning',
            message: btn.data('noti'),
            buttons: {
                confirm: {
                    label: confirmYes,
                },
                cancel: {
                    label: confirmNo,
                },
            },
            callback: function (result) {
                if (result) {
                    var form = document.createElement('form');
                    form.setAttribute('method', 'post');
                    form.setAttribute('action', btn.data('url'));
                    checkItems.each(function () {
                       var input = document.createElement('input');
                       input.setAttribute('type', 'hidden');
                       input.setAttribute('name', 'test_ids[]');
                       input.setAttribute('value', $(this).val());
                       form.appendChild(input);
                    });
                    var tokenField = document.createElement('input');
                    tokenField.setAttribute('type', 'hidden');
                    tokenField.setAttribute('name', '_token');
                    tokenField.setAttribute('value', _token);
                    form.appendChild(tokenField);
                    //append body
                    document.body.appendChild(form);
                    form.submit();
                    form.remove();
                }
            },
        });
    });

    
    //render checked option
    setDisabledCheckTotal($('#check_total').is(':checked'));
    
    $(document).ready(function() {
        if (typeof $.fn.DataTable != 'undefined') {
            $('.table-multiple-choice').DataTable(optQuestionDataTable);
            $('.table-written-questions').DataTable(optQuestionDataTable);
        }
    });

    $('body').on('click', '#btn_reset_filter', function (e) {
        e.preventDefault();
        $('.table-multiple-choice').DataTable().search('').columns().search('').draw();
        $('.thead-filter select').each(function () {
            $(this).val('').trigger('change.select2');
        });
    });
    
    //type category of question
    $('body').on('click', '.add-type-box .btn-add-type-cat', function (e) {
        e.preventDefault();
        var btn = $(this);
        var typeBox = btn.closest('.add-type-box');
        var formBox = typeBox.find('.type-cat-new-box');
        formBox.removeClass('hidden');
        formBox.find('.cat_name').focus();
        btn.addClass('hidden');
        return false;
    });
    $('body').on('keypress', '.add-type-box .cat_name', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode == $.ui.keyCode.ENTER) {
            $(this).closest('.add-type-box').find('.btn-submit-cat').trigger('click');
            e.preventDefault();
            return false;
        }
    });
    //submit new type cat
    $('body').on('click', '.add-type-box .btn-submit-cat', function () {
        var btn = $(this);
        var typeBox = btn.closest('.add-type-box');
        var formBox = typeBox.find('.type-cat-new-box');
        var form = formBox.find('.form-add-cat');
        var btnBox = typeBox.find('.btn-add-type-cat');
        var selectCatList = typeBox.prev('.form-group').find('select');
        
        if (form.find('.cat_name').val().trim() == '') {
            return false;
        }
        
        if (btn.is(':disabled')) {
            return false;
        }
        
        btn.prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: form.data('url'),
            data: {
                _token: _token,
                cat: {
                    name: form.find('.cat_name').val(),
                    type_cat: form.find('.type_cat').val()
                },
                question_id: form.find('.question_id').val(),
                test_id: form.find('.test_id').val(),
                lang: currentLangEdit,
            },
            success: function (data) {
                if (data) {
                    if (selectCatList.find('option[value="'+ data.id +'"]').length < 1) {
                        selectCatList.append('<option value="'+ data.id +'">'+ data.name +'</option>');
                    }
                    selectCatList.val(data.id).trigger('change');
                }
                form.find('.cat_name').val('');
                formBox.addClass('hidden');
                btnBox.removeClass('hidden');
            },
            complete: function () {
                btn.prop('disabled', false);
            }
        });
        
        return false; 
    });

    //select link
    $('body').on('click', '.td-link .link-box', function () {
        if (window.getSelection) {
            var selection = window.getSelection();
            var range = document.createRange();
            range.selectNodeContents(this);
            selection.removeAllRanges();
            selection.addRange(range);
        } else if (document.body.createTextRange) {
            var range = document.body.createTextRange();
            range.moveToElementText(this);
            range.select();
        }
    });

})(jQuery);

//replace or append question udpate to list question
function replaceOrAppendQuestion(id, isCheck, qOrder) {
    if (typeof id == 'undefined' || !id) {
        return;
    }
    if (typeof isCheck == 'undefined') {
        isCheck = true;
    }
    if (typeof qOrder == 'undefined') {
        qOrder = null;
    }
    id = tempEditQuestion.id ? tempEditQuestion.id : id;
    var trQuestion = $('tr#question_' + id);
    if (qOrder === null || qOrder === '') {
        qOrder = tempEditQuestion.order !== null ? tempEditQuestion.order : trQuestion.find('.q_order .num').text();
    }

    $.ajax({
        type: 'GET',
        url: get_edit_question_url,
        data: {
            question_id: id,
            q_order: qOrder,
            test_id: currentTestId,
            lang: currentLangEdit,
        },
        success: function (result) {
            $('#tr_no_item').remove();
            var table = $('.table-multiple-choice').DataTable();
            if (trQuestion.length > 0) {
                table.row(trQuestion).data(trToData(result)).draw();
            } else {
                table.row.add($(result)).draw();
                $('tr#question_' + id + ' .q_order .num').text($('#list_question tr').length);
            }
            initDataTableFilter($('.table-multiple-choice').dataTable());
            if (isCheck) {
                $('tr#question_' + id + ' .check_item').prop('checked', isCheck);
            }
            $('tr#question_' + id + ' pre code').each(function(i, block) {
                hljs.highlightBlock(block);
            });
            initAudioPlayer();
        }
    });
}

//replace or append written question update to list question
function createOrEditWrittenQuestion(id, isCheck, qOrder) {
    if (typeof id == 'undefined' || !id) {
        return;
    }
    if (typeof isCheck == 'undefined') {
        isCheck = true;
    }
    if (typeof qOrder == 'undefined') {
        qOrder = null;
    }
    id = tempEditQuestion.id ? tempEditQuestion.id : id;
    var trQuestion = $('tr#question_' + id);
    if (qOrder === null || qOrder === '') {
        qOrder = tempEditQuestion.order !== null ? tempEditQuestion.order : trQuestion.find('.q_order .num').text();
    }

    $.ajax({
        type: 'GET',
        url: get_edit_question_url,
        data: {
            question_id: id,
            q_order: qOrder,
            test_id: currentTestId,
            lang: currentLangEdit,
            type: 4,
        },
        success: function (result) {
            $('#tr_no_item').remove();
            var table = $('.table-written-questions').DataTable();
            if (trQuestion.length > 0) {
                table.row(trQuestion).data(trToData(result)).draw();
            } else {
                table.row.add($(result)).draw();
                $('tr#question_' + id + ' .q_order .num').text($('#list_written_question tr').length);
            }
            initDataTableFilter($('.table-written-questions').dataTable());
            if (isCheck) {
                $('tr#question_' + id + ' .check_item').prop('checked', isCheck);
            }
            $('#DataTables_Table_1_length').hide();
            $('#DataTables_Table_1_filter').hide();
        }
    });
}

// convert to datatable row data
function trToData(row) {
   return $(row).find('td').map(function(i, el) {
        return el.innerHTML;
   }).get();
}

var modal_warning = $('#modal-warning-notification');
var modal_confirm = $('#modal-delete-confirm');

// if not found image return default image
function loadErrorImage(element) {
    element.onerror = '';
    element.src = window.location.origin + '/common/images/noimage.png';
    return true;
}

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

// confirm change file
function confirmChangeFile() {
    if (list_question.html().trim() == "") {
        return true;
    }
    return confirm(text_confirm_edit_upload);
}

// remove html tags in content test
function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

$('#check_total').change(function() {
    var isCheck = $(this).is(':checked');
    setDisabledCheckTotal(isCheck);
});

function setDisabledCheckTotal(isCheck)
{
    $('#total_q').prop('disabled', !isCheck);
    $('#display_option_box select, #display_option_box .input-option').prop('disabled', !isCheck);
    $('#add_display_option_btn').prop('disabled', !isCheck);
    if(!isCheck) {
        $('#total_q-error').remove();
        $('#total_q').removeClass('error');
        $('#display_option_box .display-option-row').removeClass('error');
        $('#display_option_box .display-option-row').next('p.error').remove();
    }
}

$('.time-group').each(function(){
    $(this).datetimepicker({
        ignoreReadonly: true,
        format: 'YYYY/MM/DD HH:mm'
    });
});

$('#set_time').change(function() {
    if($(this).is(':checked')) {
        $('#time-from-to').removeClass('hidden');
    } else {
        $('#time-from-to').addClass('hidden');
        $('#time-from-to input.error').removeClass('error');
        $('#time-from-to .input-group').next('.error').remove();
    }
});

$('#set_min_point').change(function () {
    if ($(this).is(':checked')) {
        $('#min_point').removeClass('hidden');
    } else {
        $('#min_point').addClass('hidden');
    }
});

$(document).on('change', 'input[name="thumbnail"]', function (event) {
    if (event.target.files && event.target.files[0]) {
        var file = event.target.files[0];
        var reader = new FileReader();
        var imgThumbnail = $('.img-thumbnail');
        var that = $(this);

        if (imageAllows.indexOf(file.type) === -1) {
            alert(errorFileNotAllow);
            oldThumbnail ? imgThumbnail.attr('src', oldThumbnail) : imgThumbnail.parent().remove();
            that.val('');
            return;
        }

        reader.onload = function (e) {
            if (imgThumbnail.length !== 0) {
                $(imgThumbnail).attr('src', e.target.result);
            } else {
                var domImage = '<div class="form-group">'
                    + '<img src="' + e.target.result +'" alt="thumbnail" class="img-bordered-sm img-responsive img-thumbnail" width="100" height="100">'
                    + '</div>';
                that.parent().after(domImage);
            }
        };

        reader.readAsDataURL(file);
    }
});
