var modal_warning = $('#modal-warning-notification');
var keyAnswers = 'arrAnswers';

(function ($) {

    $('.switch-lang-form .select-lang').change(function () {
        var form = $(this).closest('form');
        var url = form.attr('action');
        $.ajax({
            type: 'GET',
            url: url,
            data: {
                lang: $(this).val(),
            },
            success: function () {
                window.location.reload();
            },
        });
    });

    if ($('#form_auth').length > 0) {
        $('#form_auth input[name="email"]').focus();
    }
    
    $('#select_form').submit(function () {
        if (!$('#test_box').val()) {
            $('#test_error').text(text_please_select_test);
            return false;
        } else if ($('#candidate_box').length > 0 && !$('#candidate_box').val()) {
            $('#test_error').text(text_please_select_candidate);
            return false;
        } else {
            $('#test_error').text('');
            return true;
        }
    });
    
    if ($('#candidate_box').length > 0) {
         $('#candidate_box').select2({
             ajax: {
                 url: url_load_candidate,
                 dataType: 'json',
                 delay: 250,
                 data: function (params) {
                     return {
                         q: params.term
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
             },
             minimumInputLength: 1
         });
     }
    
    $('#submit_test_form').validate({
        rules: {
            "person[name]": {
                required: true,
                maxlength: 255,
            },
            "person[email]": {
                required: true,
                email: true,
                maxlength: 255,
            },
            "person[phone]": {
                required: true,
                maxlength: 11,
            },
        },
        messages: {
            "person[name]": {
                required: text_field_required,
                maxlength: text_max_length,
            },
            "person[email]": {
                required: text_field_required,
                email: text_email_format,
                maxlength: text_max_length,
            },
            "person[phone]": {
                required: text_field_required,
                maxlength: text_max_length_11,
            },
        }
    });
    
    $('#type_box').on('change', function () {
        if (!$(this).val()) {
            return;
        }
        var el_this = $(this);
        var selected = $(this).find('option:selected');
        var type_id = $(this).val();
        var is_group = selected.attr('data-parent');
        
        $('#test_box').prop('disabled', true);
        $(this).prop('disabled', true);
        $.ajax({
            url: $(this).data('url'),
            type: 'GET',
            data: {
                type_id: type_id,
                is_group: is_group
            },
            success: function (data) {
                if (data) {
                    $('#test_box').html(data);
                }
            },
            complete: function () {
                $('#test_box').prop('disabled', false);
                el_this.prop('disabled', false);
            }
        });
    });
    
    $('#test_box').on('change', function () {
        var form = $(this).closest('form');
        var url = form.data('url');
        form.attr('action', url + '/' + $(this).val());
        $('#test_error').text('');
    });

    var submit_box = $('.submit-box');
    var head_h = $('#header').height();
    var old_top = submit_box.css('top');
    $(window).scroll(function () {
        var scrollTop = $(this).scrollTop();
        if (scrollTop > 5) {
            submit_box.css('top', (scrollTop - head_h + 5));
        } else {
            submit_box.css('top', old_top);
        }
    });
    
    var person_email = $('#person_email');
    if (person_email.length > 0) {
        var email_keyup_timeout;
        person_email.on('keyup', function () {
           clearTimeout(email_keyup_timeout);
           email_keyup_timeout = setTimeout(function () {
                checkDoTest(person_email);
           }, 1000);
        });
        if (person_email.val().trim() != "") {
            checkDoTest(person_email);
        }
    }
    // check email do this test;
    function checkDoTest(element) {
        $.ajax({
            type: 'POST',
            url: element.data('url'),
            data: {
                test_id: $('#test_id').val(),
                email: element.val(),
                _token: _token
            },
            success: function (data) {
                var error_mess = element.closest('.form-group').find('.error_mess');
                if (data.check) {
                    error_mess.html(data.message);
                    $('.test-content').addClass('hidden');
                    $('#btn_submit_test').prop('disabled', true);
                    $('.submit-box').addClass('hidden');
//                    clearInterval(timeCountDown);
                } else {
                    error_mess.html('');
                    if (minute > 0) {
                        $('.test-content').removeClass('hidden');
                    }
                    $('#btn_submit_test').prop('disabled', false);
                    $('.submit-box').removeClass('hidden');
//                    startTest();
                }
            }
        });
    }

    if ($('.testing-page').length > 0) {
        var test_id = $('#test_id').val();
        var timeCountDown;
        var el_minute = $('.time-btn .minute');
        var el_second = $('.time-btn .second');
        var minute = parseInt(el_minute.text());
        var second = parseInt(el_second.text());
        startTest();

        var test_form_submit = false;
        $('#btn_submit_test').click(function () {
            var cancelSubmit = true;
            var btn = $(this);
            var form = btn.closest('form');

            $('textarea.written_answer').map(function(idx, elem) {
                if ($(elem).val() != '') {
                    cancelSubmit = false;
                }
            });
            if ($('input.written_answer').val() === 'showSubmit') {
                cancelSubmit = false;
            }
           if (cancelSubmit === true) {
               bootbox.dialog({
                   closeButton: false,
                   className: 'modal-warning modal-confirm-submit',
                   message: mesNoAnswersForWritten,
                   buttons: {
                       success:{
                           label: "Ok",
                           callback: function () {
                               btn.prop('disabled', false)
                           },
                       }
                   }

               });
               return false;
           }

            $('#required_answer').html('');
            btn.prop('disabled', true);

            if (!form.valid()) {
                btn.prop('disabled', false);
                return;
            }
            var textNoti = btn.data('noti');
            var qNotAnswers = collectNotAnswers();
            if (qNotAnswers.length > 0) {
                textNoti = text_questions_not_answer + ': ' + qNotAnswers.join(', ') + '<br />' + textNoti;
            }
            $('.modal-confirm-submit').modal('hide');
            bootbox.confirm({
                className: 'modal-warning modal-confirm-submit',
                message: textNoti,
                callback: function (result) {
                    if (result) {
                        test_form_submit = true;
                        t_removeItem(keyAnswers);
                        form.submit();
                    } else {
                        btn.prop('disabled', false);
                    }
                }
            });
            return false;
        });

        $('#submit_test_form').on('submit', function () {
            t_removeItem(keyAnswers);
        });
        
        /*
         * collect question number not answer
         */
        function collectNotAnswers() {
            var qNotAnswers = [];
            $('.q_item').each(function () {
                var qItem = $(this);
                var order = qItem.find('.q_order').data('order');
                //checkbox
                if (qItem.find('input[type="checkbox"]._answer').length > 0 &&
                        qItem.find('input[type="checkbox"]._answer:checked').length < 1) {
                    qNotAnswers.push(order);
                }
                //radio
                if (qItem.find('input[type="radio"]._answer').length > 0 &&
                        qItem.find('input[type="radio"]._answer:checked').length < 1) {
                    qNotAnswers.push(order);
                }
                //text
                var ansText = qItem.find('input[type="text"]._answer');
                if (ansText.length > 0 && !ansText.val().trim()) {
                    qNotAnswers.push(order);
                }
                //select (type 2)
                var ansSelect = qItem.find('select._answer');
                if (ansSelect.length > 0) {
                    var isAnswer = true;
                    ansSelect.each(function () {
                        if (!$(this).val() && isAnswer) {
                           isAnswer = false;
                        }
                    });
                    if (!isAnswer) {
                        qNotAnswers.push(order);
                    }
                }
            });
            return qNotAnswers;
        }
        
        window.onbeforeunload = function (e) {
            if (!test_form_submit) {
                return true;
            }
        };

        $('._answer').on('change', function () {
            var listAnswers;
            var questionId;
            var answerIds = [];
            var childItem = $(this).closest('.child_item');
            if (childItem.length > 0) {
                questionId = childItem.data('child');
                answerIds.push($(this).val());
            } else {
                questionId = $(this).closest('.q_item').data('id');
                $(this).closest('.q_item').find('._answer:checked').each(function () {
                    answerIds.push($(this).val());
                });
            }

            listAnswers = localStorage.getItem(keyAnswers);
            if (listAnswers) {
                listAnswers = JSON.parse(listAnswers);
            } else {
                listAnswers = [];
            }
            var hasQuestion = false;
            for (var i in listAnswers) {
                var item = listAnswers[i];
                if (item.qid == questionId) {
                    item.ans = answerIds;
                    listAnswers[i] = item;
                    hasQuestion = true;
                    break;
                }
            }
            var qAnswer = {
                qid: questionId,
                ans: answerIds,
            };
            if (!hasQuestion) {
                listAnswers.push(qAnswer);
            }
            var strListAnswers = JSON.stringify(listAnswers);
            localStorage.setItem(keyAnswers, strListAnswers);
            //save temp
            if (typeof testTemp != 'undefined' && typeof saveTempResultUrl != 'undefined') {
                var tempAnswers = localStorage.getItem(keyAnswers, strListAnswers);
                tempAnswers = JSON.parse(tempAnswers);
                for (var i in tempAnswers) {
                    var item = tempAnswers[i];
                    var question = $('#question_' + item.qid);
                    if (question.length > 0) {
                        var answers = item.ans;
                        for (var j in answers) {
                            $('#answer_' + answers[j]).prop('checked', true);
                        }
                    } else {
                        question = $('.child_item[data-child="'+ item.qid +'"]');
                        if (question.length > 0 && item.ans.length > 0) {
                            question.find('select._answer').val(item.ans[0]);
                        }
                    }
                }
            }
        });
        
        // lấy dữ liệu
        var tempAnswers = localStorage.getItem(keyAnswers);
        tempAnswers = JSON.parse(tempAnswers);
        for (var i in tempAnswers) {
            var item = tempAnswers[i];
            var question = $('#question_' + item.qid);
            if (question.length > 0) {
                var answers = item.ans;
                for (var j in answers) {
                    $('#answer_' + answers[j]).prop('checked', true);
                }
            } else {
                question = $('.child_item[data-child="'+ item.qid +'"]');
                if (question.length > 0 && item.ans.length > 0) {
                    question.find('select._answer').val(item.ans[0]);
                }
            }
        }
        
    }
    
    // start test
    function startTest() {
        clearInterval(timeCountDown);
        var origin_time = (minute * 60 + second) * 1000;
        t_setItem('originTime', origin_time);
        t_setItem('startTime', new Date().getTime());
        t_setItem('testId', test_id);

        timeCountDown = setInterval(countDown, 1000);
    }

    // count down time
    function countDown() {
        if (second == 0) {
            if (minute == 0) {
                var btnSubmit = $('#btn_submit_test');
                var cancelSubmit = true;
                btnSubmit.prop('disabled', true).addClass('hidden');
                clearInterval(timeCountDown);
                $('.time-btn').removeClass('btn-flickr');
                $('.test-content').addClass('hidden');

                $('textarea.written_answer').map(function (idx, elem) {
                    if ($(elem).val() != '') {
                        cancelSubmit = false;
                    }
                });
                if ($('input.written_answer').val() === 'showSubmit') {
                    cancelSubmit = false;
                }

               if (cancelSubmit === true) {
                   btnSubmit.prop('disabled', true)
                   test_form_submit = false;
                   alert(mesNoAnswersForWrittenTestDoesntCount);
               } else {
                    test_form_submit = true;
                    alert(text_test_time_over);
                    $('#submit_test_form').submit();
               }
            } else {
                second = 59;
                minute--;
                el_minute.text(twoDigit(minute));
            }
        } else {
            if (second == 1 && minute == 0) {
                bootbox.hideAll();
            }
            second--;
        }
        if (minute <= 5) {
            $('.time-btn').addClass('btn-flickr');
        }
        el_second.text(twoDigit(second));
    }

    selectSearchReload();

    $(document).ready(function () {
        // pagination
        if (typeof PER_PAGE != 'undefined') {
            var totalPage = Math.ceil($('.testing-page .q_item').length / PER_PAGE);
            testGoPage(1, totalPage);

            $('.test-paginate a').click(function (e) {
                e.preventDefault();
                var page = $(this).data('page');
                if (typeof page == 'undefined' || !page) {
                    return;
                }
                testGoPage(page, totalPage);
            });
        }
    });

    /*
     * test paginate, go to page number
     */
    function testGoPage(page, totalPage) {
        $('html, body').animate({ scrollTop: 0}, 500);

        var elPrevPage = $('.test-paginate .prev-page');
        var elNextPage = $('.test-paginate .next-page');

        var idxFrom = (page - 1) * PER_PAGE;
        var idxTo = page * PER_PAGE;
        $('.testing-page .q_item').addClass('hidden');
        $('.testing-page .q_item').slice(idxFrom, idxTo).removeClass('hidden');

        $('.test-paginate a').removeClass('active');
        $('.test-paginate a[data-page="'+ page +'"]').not('.next-page, .prev-page').addClass('active');

        if (page > 1) {
            elPrevPage.removeClass('hidden');
            elPrevPage.data('page', page - 1);
        } else {
            elPrevPage.addClass('hidden');
        }

        if (page < totalPage) {
            elNextPage.removeClass('hidden');
            elNextPage.data('page', page + 1);
        } else {
            elNextPage.addClass('hidden');
        }
    };

})(jQuery);

function twoDigit(value) {
    return (value < 10) ? '0' + value : value;
}

function t_setItem(key, val) {
    if (typeof Storage != "undefined") {
        sessionStorage.setItem(key, val);
    }
}

function t_getItem(key) {
    if (typeof Storage != "undefined") {
        var val = sessionStorage[key];
        return (typeof val != "undefined") ? val : null;
    }
    return null;
}

function t_removeItem(key) {
    if (typeof Storage != "undefined") {
        localStorage.removeItem(key);
    }
}

function t_setStart() {
    if (typeof Storage != "undefined") {
        localStorage.setItem('hasStarted', true);
    } 
}

function t_checkStart(){
    if (typeof Storage != "undefined") {
        var started = localStorage.getItem('hasStarted');
        return (typeof started != "undefined") ? started : false;
    }
    return false;
}

function t_delStart() {
    if (typeof Storage != "undefined") {
        localStorage.removeItem('hasStarted');
    }
}

function selectSearchReload(option) {
    optionDefault = {
        showSearch: false
    };
    option = jQuery.extend(optionDefault, option);
    if (option.showSearch) {
        jQuery(".select-search").select2();
    } else {
        jQuery(".select-search.has-search").select2();
        jQuery(".select-search:not(.has-search)").select2({
            minimumResultsForSearch: Infinity
        });
    }

    jQuery('.select-search').each(function (i, k) {
        text = jQuery(this).find('option:selected').text().trim();
        redirectRikkeiCode(text)
        jQuery(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
    });
    jQuery('.select-search').on('select2:select', function (evt) {
        text = jQuery(this).find('option:selected').text().trim();
        jQuery(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
        
        redirectRikkeiCode(text)
    });
}

function redirectRikkeiCode(text) {
    var startActionTestElement = $(".start-action-exam .start-test");
    if(text === 'Rikkei Code') {
        startActionTestElement.attr('disabled', true)
        window.open(exam_url, '_blank');
    } else {
        startActionTestElement.attr('disabled', false)
    }
}

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
