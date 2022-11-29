$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
});
$(document).ready(function () {
    var isDev = false;
    $('#position_apply').change(function() {
        var positionArray = $('#position_apply');
        var yes = $.inArray( devPosition, positionArray.val());
        var eleAppend = "<em class='required' aria-required='true'>*</em>";
        if(yes === 0) {
            //$('label#candidate-program-lang').append(eleAppend);
            isDev = true;
        } else {
            //$('label#candidate-program-lang').find(eleAppend).remove();
            isDev = false;
        }
    });
    $("#form-create-candidate").validate({
        ignore: ':hidden:not("#team_id, #position_apply")',
        rules: {
            fullname: "required",
            birthday: "required",
            email: {
                required: true,
                validEmail: $.trim($('#email').val()),
                remote: {
                    type: "POST",
                    url: urlCheckMail,
                     data: {
                        _token: token,
                        id: id,
                        email: function () {
                            return $('#form-create-candidate :input[name="email"]').val();
                        }
                    }
                }
            },
            mobile: {
                validatePhone: true
            },
            skype: "required",
            experience: 'required',
            received_cv_date: 'required',
            recruiter: { valueNotEquals: "0" },
            'teams[]': 'required',
            'positions[]': 'required',
            type: { valueNotEquals: "0" },
            note: {
                required: checkEdit
            },
            channel_id: {
                valueNotEquals: "0"
            },
        },
        messages: {
            fullname: requiredText,
            birthday: requiredText,
            email: {
                required: requiredText,
                validEmail: invalidEmail,
                remote: uniqueMail
            },
            mobile: {
                validatePhone: phoneValidText,
            },
            skype: {
                required: requiredText,
            },
            experience: requiredText,
            received_cv_date: requiredText,
            recruiter: { valueNotEquals: requiredText },
            'teams[]': requiredText,
            'positions[]': requiredText,
            channel_id: { valueNotEquals: requiredText },
            type: { valueNotEquals: requiredText },
            note: {
                required: requiredText
            },
            university: requiredText,
        }
    });

   $('.date').datepicker({
       todayBtn: "linked",
       language: "it",
       autoclose: true,
       todayHighlight: true,
       format: 'yyyy-mm-dd'
   });

   $('#birthday').datepicker('setEndDate', '-1d');

   $('.btn-upload').click(function(){
       $('#cv').click();
   });
});

$(document).on('click', '.btn-delete', function(event) {
    $(this).parent('div').parent('div').remove();
    var text = reloadTextSelect();
    $('#programLanguages').val(text);
});

/*
 * Validate program language
 */
$(document).mouseup(function (e) {
    var container = $("#expand");
    if (!container.is(e.target) && container.has(e.target).length === 0) {
        checkAllProgramsLanguage();
    }
});
$(document).on('keyup','input[name="inputYear[]"]', function (e) {
    var regex = /^[+]?([0-9]{1,2})[.,]?([0-9]{1,2})?$/;
    $(this).parent().find('label.error').remove();
    if ($(this).val() === "") {
        $(this).after('<label id="recruiter-error" class="error" for="">'+requiredText+'</label>');
    } else {
        if($(this).val() >= 100) {
            $(this).after('<label id="recruiter-error" class="error" for="">'+valueYear+'</label>');
        } else {
            if (regex.test($(this).val()) === false) {
                $(this).after('<label id="recruiter-error" class="error" for="">'+invalidYear+'</label>');
            }
        }
    }
});
$(document).on('change','#program', function(event) {
    var text = reloadTextSelect();
    $('#programLanguages').val(text);
    var valueSelect = [];
    $("select[name='programs[]'] :selected").each(function() {
        if ($(this).val() === "0") {

        } else {
            $(this).parent().parent().find('label.error').remove();
            valueSelect.push($(this).val());
        }
    });
    $(this).parent().find('label.error').remove();
    if ($(this).val() === "0") {
        $(this).after('<label id="recruiter-error" class="error" for="">'+requiredText+'</label>');
    }
    function isExist(valueExist, x) {
        var u = 0;
        for(u; u < valueExist.length; u++) {
            if (valueExist[u] === x) {
                return true;
            }
        }
        return false;
    }
    var valueDuplicate = [];
    var i = 0;
    for(i; i < valueSelect.length; i++) {
        if(!isExist(valueDuplicate, valueSelect[i])) {
            valueDuplicate.push(valueSelect[i]);
        } else {
            $("select[name='programs[]'] :selected").each(function() {
                if ($(this).val() === valueSelect[i]) {
                    $(this).parent().parent().find('label.error').remove();
                    $(this).parent().after('<label id="recruiter-error" class="error" for="">'+languageRepeats+'</label>');
                }
            });
        }
    }
});
function checkAllProgramsLanguage() {
    var text = reloadTextSelect();
    $('#programLanguages').val(text);
    var regex = /^[+]?([0-9]{1,2})[.,]?([0-9]{1,2})?$/;
    var check;
    $('input[name="inputYear[]"]').each(function() {
        $(this).parent().find('label.error').remove();
        if ($(this).val() === "") {
            $(this).after('<label id="recruiter-error" class="error" for="">'+requiredText+'</label>');
            check = 1;
        } else {
            if($(this).val() >= 100) {
                $(this).after('<label id="recruiter-error" class="error" for="">'+valueYear+'</label>');
                check = 1;
            } else {
                if (regex.test($(this).val()) === false) {
                    $(this).after('<label id="recruiter-error" class="error" for="">'+invalidYear+'</label>');
                    check = 1;
                }
            }
        }
    });
    var valueSelect = [];
    $("select[name='programs[]'] :selected").each(function() {
        $(this).parent().parent().find('label.error').remove();
        if ($(this).val() === "0") {
            $(this).parent().after('<label id="recruiter-error" class="error" for="">'+requiredText+'</label>');
            check = 1;
        } else {
            valueSelect.push($(this).val());
        }
    });
    function isExist(valueExist, x) {
        var u = 0;
        for(u; u < valueExist.length; u++) {
            if (valueExist[u] === x) {
                return true;
            }
        }
        return false;
    }
    var valueDuplicate = [];
    var i = 0;
    for(i; i < valueSelect.length; i++) {
        if(!isExist(valueDuplicate, valueSelect[i])) {
            valueDuplicate.push(valueSelect[i]);
        } else {
            $("select[name='programs[]'] :selected").each(function() {
                if ($(this).val() === valueSelect[i]) {
                    $(this).parent().parent().find('label.error').remove();
                    $(this).parent().after('<label id="recruiter-error" class="error" for="">'+languageRepeats+'</label>');
                }
            });
        }
    }
    if(valueSelect.length !== valueDuplicate.length || check === 1) {
        return false;
    } else {
        return true;
    }
}
function reloadTextSelect() {
    var text = "";
    $("select[name='programs[]'] :selected").each(function() {
        if ($(this).val() !== "0") {
            text = text + $(this).text() + ', ';
        }
    });
    text = text.substring(0, text.length - 2);
    return text;
}

$("#form-create-candidate").submit( function(submitEvent) {
    if (!chkFileUpload() || !isSizeAllow(document.getElementById('cv'))) {
        submitEvent.preventDefault();
        $('button[type=submit]').prop('disabled', false);
    }
});

$('#cv').on('change', function() {
    $(this).parent().find('label#cv-error').remove();
    if (!chkFileUpload()) {
        $('#cv').after('<label id="cv-error" style="color:red;" for="cv">'+notAllowTypeText+'</label>');
        return;
    }
    if (!isSizeAllow(this)) {
        $('#cv').after('<label id="cv-error" style="color:red;" for="cv">'+notAllowSizeText+'</label>');
    }
    if (chkFileUpload() === 'pdf') {
        var file_data = $("#cv").prop("files")[0],
            form_data = new FormData();
        form_data.append("fileup", file_data);
        urlGenerateCV = typeof urlGenerateCV === 'undefined' ? 'https://cv-parser.rikkei.org/resume-parser' : urlGenerateCV;
        $.ajax({
            // google-chrome  --user-data-dir=”/var/tmp/Chrome” --disable-web-security
            url: urlGenerateCV,
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            data: form_data,
            success: function (result) {
                if (result) {
                    $('#gender>option[value="0"]').attr('selected', 'selected');
                    if (result.gender == 'nam') {
                        $('#gender>option[value="1"]').attr('selected', 'selected');
                    }
                    $('#recruiter>option[value="' + useremail + '"]').attr('selected', 'selected');
                    $('#other_contact').html(result.website);
                    $('#birthday').val(result.birthday);
                    $('#university').html(result.college);
                    $('#email').val(result.email);
                    $('#fullname').val(result.name);
                    $('#mobile').val(result.phone);
                    $('#old_company').val(result.company);
                    $('#certificate').val(result.certificate);
                    $('#received_cv_date').append(receivedCvDate);
                    $('#found_by').html('<option value="' + userId + '" selected="selected">' + username + '</option>');
                    $('#gender').select2();
                    $('#recruiter').select2();
                }
            }
        });
    }
})

$('#experience').on('change', function () {
    var value = 1;
    $('#type>option[selected="selected"]').prop('selected', false);
    if ($(this).val() >= '1.2' && $(this).val() <= '3') {
        value = 2;
    } else if ($(this).val() > '3') {
        value = 3;
    }

    $('#type>option[value=' + value + ']').prop('selected', true);
    $('#type').select2();
})

/**
 * Check allow file type upload
 * @returns {Boolean}
 */
function chkFileUpload() {
    // get the file name, possibly with path (depends on browser)
    var filename = $("#cv").val();
    if (filename == '') return true;
    // Use a regular expression to trim everything before final dot
    var extension = filename.replace(/^.*\./, '');

    // Iff there is no dot anywhere in filename, we would have extension == filename,
    // so we account for this possibility now
    if (extension == filename) {
        extension = '';
    } else {
        // if there is an extension, we convert to lower case
        // (N.B. this conversion will not effect the value of the extension
        // on the file upload.)
        extension = extension.toLowerCase();
    }
    switch (extension) {
        case 'doc':
        case 'docx':
        case 'xls':
        case 'xlsx':
        case 'pdf':
            return extension;

        default:
            return false;
    }
}

/**
 * Check file size upload
 * @returns {Boolean}
 */
function isSizeAllow(element) {
    return typeof element.files[0] == "undefined" || element.files[0].size <= 5 * 1024 * 1024;
}

/** ---------------Presenter events---------------------- */

/**
 * Get old value before change
 */
$('#channel_id').on('focusin', function() {
     $(this).attr('data-val', $(this).val());
});

/**
 * Choose channel event
 * If channel is presenter then show modal popup to set presenter
 */
$('#channel_id').on('change', function() {
    var isPresenter = $(this).find('option:selected').attr('is_presenter');
    if (isPresenter == presenterYes) {
        $('.view-detail').removeClass('hidden');
        $('#modal-channel').modal('show');
    } else {
        $('.view-detail').addClass('hidden');
    }

    if (channelChange.indexOf($('#channel_id').val()) !== -1) {
        $('.cost').removeClass('hidden');
    } else {
        $('.cost').addClass('hidden');
    }
});

/**
 * Choose presenter type event
 */
$('input[name=presenter_type]').on('change', function () {
    $('#presenter_error').remove();
    $('#other-pre_error').remove();
    var value = $(this).val();
    if (value == presenterTypeCom) {
        $('.presenter-id-container').removeClass('hidden');
        $('.other-pre').addClass('hidden');
    } else {
        $('.presenter-id-container').addClass('hidden');
        $('.other-pre').removeClass('hidden');
    }
});

/**
 * QuyetND restore value after scratch
 */
$(document).ready(function () {
    $(".save_values").on("click", function () {
        if(!checkAllProgramsLanguage()) {
            $('#programLanguages').attr('aria-expanded', true);
            $('#expand').attr('aria-expanded', true);
            $('#expand').attr('style', 'width: 93%; border: 1px solid rgb(210, 214, 222); height: auto;');
            $('#expand').addClass('collapse in');
            return false;
        }
        var data_arr = [];
        $("input, select, textarea, checkbox, radio").each(function () {
            $(this).data("restore", $(this).val());
            data_arr.push({"key" : this.id, "value" : $(this).val()});
        });
        data_arr.push({"key" : 'language_save', "value" : $('.language-save').html()});
        localStorage.setItem('data', JSON.stringify(data_arr));
    });

    $("#restore_values").on("click", function () {
        var data_arr = JSON.parse(localStorage.getItem('data'));
        $.each(data_arr, function (key, object) {
            if (object['key'] == 'language_save') {
                $('#modal-choose-language .language-container').html(object['value']);
                $('.language-save').html($('.language-container').html());
                prependLanguageVal();
            } else {
                var currentElem = $('#' + object['key']);
                if (currentElem.attr('type') !== 'file') {
                    var classList = currentElem.attr('class');
                    if(classList !== undefined && classList.indexOf("multiple_select") > 0){
                        currentElem.multiselect('select', object['value']);
                        var $btnGroup = $('#' + object['key'] + ' + .btn-group');
                        setTeamText($btnGroup);
                    } else {
                        currentElem.val(object['value']).trigger("change");
                    }
                }
            }
        });
    });
});

/**
 * Set value and validate presenter
 * @returns {Boolean}
 */
function setPresenter() {

    $('#presenter_error').remove();
    $('#other-pre_error').remove();
    var presenterId = $('#presenter').val();
    if (parseInt(presenterId) == 0 || presenterId == null) {
        $('#presenter').after('<label id="presenter_error" class="error">This field is required.</label>');
        return false;
    }
    $('input[name=presenter_id]').val(presenterId);
    $('#modal-channel').modal('hide');
    $('.view-detail').removeClass('hidden');
    $('#channel_id').focus();
    $('#presenter').attr('data-old-id', $('#presenter').val());
}

/**
 * Reset value modal fill presenter
 */
function reset() {
    $('#presenter').val('0').trigger("change");
    $('span[aria-labelledby=select2-presenter-container]').removeClass('hidden');
}

/*
 * validate required
 */
$('#presenter').on('change', function() {
    $('#presenter_error').remove();
    var val = $(this).val();
    if (val == 0) {
        $(this).after('<label id="presenter_error" class="error">This field is required.</label>');
    }
});

/**
 * When choose channel is presenter but after cancel click
 *
 */
function cancelPresenter() {
    var prev = $('#channel_id').attr('data-val');
    if (prev != null) {
        $('#channel_id').val(prev).trigger('change');
        if (isPresenter(prev)) {
            $('.view-detail').removeClass('hidden');
        } else {
            $('.view-detail').addClass('hidden');
        }
    }
    var prevPresente = $('#presenter').attr('data-old-id');
    $('#presenter').val(prevPresente).trigger('change');
    $('#modal-channel').modal('hide');
}

function viewDetail() {
    $('#presenter_error').remove();
    $('#other-pre_error').remove();
    $('#modal-channel').modal('show');
}

function isPresenter(value) {
    var val = $('#channel_id option[value='+value+']').attr('is_presenter');
    return val == presenterYes;
}

/**
 * Trim text before and after comma
 *
 * @param string text
 * @return string
 */
function trimText(text) {
    var textSelected = '';
    var arr = text.split(',');
    for (var i = 0; i < arr.length; i++) {
        if (!textSelected) {
            textSelected = arr[i].trim();
        } else {
            textSelected += ', ' + arr[i].trim();
        }
    }
    return textSelected;
}

/**
 * Set button text of multiselect
 */
function setTeamText(elem) {
    var elemSelected = elem.find('.multiselect-selected-text');
    elemSelected.text(trimText(elemSelected.text()));
}

$(document).on('click', '#languages', function() {
   $('#modal-choose-language .language-container').html($('.language-save').html());
   $('#modal-choose-language').modal('show');
});

/**
 * Modal choose language
 * Level select box change event
 */
$(document).on('change', '.language-container .level', function() {
    $(this).find('option[value='+$(this).val()+']').attr('selected', true);
});

/**
 * Modal choose language
 * Language select box change event
 */
$(document).on('change', '.language-container .language', function() {
    var arrayValue = getLangUsed();
    removeLangLabelError($(this));
    $('.language-container .language option').prop('disabled', false);
    $('.language-container .language').each(function() {
        var curElem = $(this);
        var selected = curElem.val();
        $.each(arrayValue, function(k, v) {
            if (v != selected) {
                curElem.find('option[value='+v+']').prop('disabled', true);
            }
        });
    });
    var langSelected = $(this).val();
    $(this).find('option[value='+langSelected+']').attr('selected', true);
    var levelBox = $(this).closest('.row-add').find('select.level');
    var label = levelBox.parent().parent().find('label');
    levelBox.html("<option value='0'>"+chooseLevelText+"</option>");
    if (typeof langArray[langSelected] != 'undefined') {
        $.each(langArray[langSelected], function(langId, langName) {
            levelBox.append("<option value='"+langId+"'>"+langName+"</option>");
        });
    }
});

/**
 * Modal choose language
 * Add row language
 */
$(document).on('click', '.btn-add-lang', function() {
    $(this).before($('.add-box-lang').html());
    var arrayValue = getLangUsed();
    $.each(arrayValue, function(k, v) {
        $('.language-container .language:last option[value='+v+']').prop('disabled', true);
    });

});

/*
 * Modal choose language
 * get all language selected
 */
function getLangUsed() {
    var arrayValue = [];
    $('.language-container .language').each(function() {
        var value = $(this).val();
        if (value != 0) {
            arrayValue.push(value);
        }
    });
    return arrayValue;
}

/**
 * Modal choose language
 * Validate items before save language
 */
function validate() {
    var invalid = false;
    $('#modal-choose-language .modal-footer .error').remove();
    $('.language-container select.language').each(function() {
        removeLangLabelError($(this));
        $(this).parent().find('label.error').remove();
        var value = $(this).val();
        if (value == 0) {
            invalid = true;
            $(this).after("<label class='error'>"+requiredText+"</label>");
        }
    });
    return invalid;
}

function removeLangLabelError(domSelectLang) {
    domSelectLang.parent().find('label.error').remove();
}

/**
 * Modal choose language
 * Save language
 */
function saveLang() {
    if (validate()) {
        return false;
    }
    $('#form-create-candidate .language-val').remove();
    prependLanguageVal();
    $('.language-save').html($('.language-container').html());
    $('#modal-choose-language').modal('hide');
    var strSelected = '';
    $('.language-container .language').each(function() {
        var langText = $(this).find('option:selected').text().trim();
        if (!strSelected) {
            strSelected += langText;
        } else {
            strSelected += ', ' + langText;
        }
    });
    $('#languages').val(strSelected);
}

/**
 * Get language in .language-container
 * Then prepend input type hidden languages value
 *
 * @returns void
 */
function prependLanguageVal() {
    $('.language-container .row-add').each(function() {
        var languageId = $(this).find('select.language').val();
        var levelId = $(this).find('select.level').val();
        $('#form-create-candidate').prepend('<input class="language-val" type="hidden" name="languages['+languageId+']" value="'+levelId+'" />');
    });
}

/**
 * Remove language on modal add language
 *
 * @param {dom} elem
 */
function removeLang(elem) {
    $(elem).closest('.row-add').remove();
}

$(document).ready(function () {
    var arrayValue = [];
    $('.language-save .language').each(function() {
        var value = $(this).val();
        if (value != 0) {
            arrayValue.push(value);
        }
    });
    $.each(arrayValue, function(k, v) {
        $('.language-save .language option:not(:selected)[value='+v+']').prop('disabled', true);
    });
});

select2Employees('#found_by');
select2Employees('#presenter');

function formatState(opt) {
    if (!opt.id) {
        return opt.text;
    }
    return $('<span><i class="fa fa-star-o ' + $(opt.element).attr('class') + '"></i> ' + opt.text + '</span>');
}
$("#interested").select2({
    templateResult: formatState,
    templateSelection: formatState,
});

$(document).ready(function () {
    $("#form-recommend-candidate").validate({
        rules: {
            fullname: "required",
            comment: "required",
            recruiter: "required",
            region: "required",
            cv: {
                required: checkEdit
            },
            email: {
                required: true,
                validEmail: $.trim($('#email').val()),
                remote: {
                    type: "POST",
                    url: urlCheckMailRecommend,
                    data: {
                        _token: token,
                        id: id,
                        email: function () {
                            return $('#form-recommend-candidate :input[name="email"]').val();
                        }
                    }
                }
            },
            mobile: {
                required: true,
                validatePhone: true
            },
        },
        messages: {
            fullname: requiredText,
            comment: requiredText,
            recruiter: requiredText,
            region: requiredText,
            cv: requiredText,
            email: {
                required: requiredText,
                validEmail: invalidEmail,
                remote: uniqueMail
            },
            mobile: {
                required: requiredText,
                validatePhone: phoneValidText,
            },
        }
    });
});