/**
 * team member skill
 */
(function($, RKExternal, document, window, siteConfigGlobal){
    var globalVar = typeof globalPassModule === 'object' ? globalPassModule : {};
    var trans = typeof globalVar.trans === 'object' ? globalVar.trans : {};
    var emplProfile = {};
    if (typeof $().datetimepicker === 'function') {
        $('input[data-flag-type="date"]').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
        });
        $('input[data-flag-type="date-year"]').datetimepicker({
            format: 'YYYY',
            useCurrent: false,
        });
    }
    /**
     * exec form info of employee
     */
    var formEmployeeInfo = {
        dom: $('#form-employee-info'),
        validate: function () {
            var that = this;
            if (!that.dom.length) {
                return false;
            }
            that.dom.validate({
                ignore: '',
                rules: that.rule(that.dom.data('valid-type')),
                lang: 'vi',
                messages: messages,
                errorPlacement: function (error, element) {
                    if (element.hasClass('select-search')) {
                        error.appendTo(element.closest('.input-box'));
                    } else {
                        error.insertAfter(element);
                    }
                },
            });
        },
        rule: function() {
            switch(globalVar.tabType) {
                case 'work':

                    return {
                        'employee[employee_code]': {
                            required: true,
                           maxlength: function(input) {
                               if (input.value.includes("Partner_")) {
                                   return undefined;
                               }
                               return 10;
                           },
                        },
                        'employee[employee_card_id]': {
                            number: true,
                            maxlength: 10,
                        },
                        'employee[email]': {
                            required: true,
                            email: true,
                            maxlength: 100,
                        },
                        'employee[join_date]': {
                            required: true,
                            // date : true,
                        },
                        'employee[offcial_date]': {
                            // date : true,
                            greaterEqualThan: '[name="employee[join_date]"]',
                        },
                        'employee[trial_date]': {
                            // date : true,
                            greaterEqualThan: '[name="employee[join_date]"]',
                        },
                        'employee[trial_end_date]': {
                            // date : true,
                            greaterEqualThan: ['[name="employee[join_date]"]', '[name="employee[trial_date]"]'],
                        },
                        'employee[leave_date]': {
                            // date : true,
                            greaterEqualThan: '[name="employee[join_date]"]',
                        },
                        'employee[leave_reason]': {
                            maxlength: 5000,
                        },
                        'e_w[tax_code]': {
                            maxlength: 255,
                        },
                        'e_w[bank_account]': {
                            maxlength: 255,
                        },
                        'e_w[bank_name]': {
                            maxlength: 255,
                        },
                        'e_w[insurrance_book]': {
                            maxlength: 255,
                        },
                        'e_w[insurrance_ratio]': {
                            number: true,
                            maxlength: 6,
                        },
                        'e_w[insurrance_h_code]': {
                            maxlength: 255,
                        },
                        'e_w[register_examination_place]': {
                            maxlength: 255,
                        },
                        'e_w[insurrance_date]': {
                            // date: true,
                        },
                        'e_w[insurrance_h_expire]': {
                            // date: true,
                        },
                    };
                case 'contact':
                    return {
                        'employeeContact[mobile_phone]': {
                            required: true,
                            maxlength: 20,
                        },
                        'employeeContact[office_phone]': {
                            maxlength: 20,
                        },
                        'employeeContact[home_phone]': {
                            maxlength: 20,
                        },
                        'employeeContact[other_phone]': {
                            maxlength: 20,
                        },
                        'employeeContact[personal_email]': {
                            email : true,
                            maxlength: 100,
                        },
                        'employeeContact[other_email]': {
                            email : true,
                            maxlength: 100,
                        },
                        'employeeContact[yahoo]': {
                            maxlength: 100,
                        },
                        'employeeContact[skype]': {
                            maxlength: 100,
                        },
                        'employeeContact[facebook]': {
                            maxlength: 100,
                        },
                        'employeeContact[native_addr]': {
                            maxlength: 255,
                        },
                        'employeeContact[native_province]': {
                            maxlength: 100,
                        },
                        'employeeContact[native_district]': {
                            maxlength: 100,
                        },
                        'employeeContact[native_ward]': {
                            maxlength: 100,
                        },
                        'employeeContact[tempo_addr]': {
                            maxlength: 255,
                        },
                        'employeeContact[tempo_province]': {
                            maxlength: 100,
                        },
                        'employeeContact[tempo_district]': {
                            maxlength: 100,
                        },
                        'employeeContact[tempo_ward]': {
                            maxlength: 100,
                        },
                        'employeeContact[emergency_contact_name]': {
                            maxlength: 100,
                        },
                        'employeeContact[emergency_mobile]':{
                            maxlength: 20,
                        },
                        'employeeContact[emergency_contact_mobile]':{
                            maxlength: 20,
                        },
                        'employeeContact[emergency_addr]': {
                            maxlength: 255,
                        },
                    };
                case 'health':
                    return {
                        'employeeHealth[height]': {
                           number : true,
                            maxlength : 6,
                         },
                         'employeeHealth[weigth]': {
                            number : true,
                            maxlength : 6,
                         },
                         'employeeHealth[health_status]': {
                            maxlength : 255,
                         },
                         'employeeHealth[health_note]': {
                            maxlength : 5000,
                         },
                         'employeeHealth[ailment]': {
                            maxlength : 5000,
                         },
                    };
                case 'hobby':
                    return {
                        'employeeHobby[hobby_content]': {
                            required: true,
                            maxlength: 5000,
                            normalizer: function( value ) {
                                return $.trim(value);
                            },
                        },
                        'employeeHobby[personal_goal]': {
                            maxlength : 5000,
                         },
                         'employeeHobby[forte]': {
                            maxlength : 5000,
                         },
                         'employeeHobby[hobby]': {
                            maxlength : 5000,
                         },
                         'employeeHobby[weakness]': {
                            maxlength : 5000,
                         },
                    };
                case 'costume':
                    return {
                        'employeeCostume[asia_shirts]': {
                            required: true,
                        },
                        'employeeCostume[euro_shirts]': {
                            required: true,
                        },
                        'employeeCostume[shoudler_width]': {
                            number: true,
                            maxlength : 8,
                        },
                        'employeeCostume[round_butt]': {
                            number: true,
                            maxlength : 8,
                        },
                        'employeeCostume[long_sleeve]': {
                            number: true,
                            maxlength : 8,
                        },
                        'employeeCostume[long_pants]': {
                            number: true,
                            maxlength : 8,
                        },
                        'employeeCostume[long_shirt]': {
                            number: true,
                            maxlength : 8,
                        },
                        'employeeCostume[long_skirt]': {
                            number: true,
                            maxlength : 8,
                        },
                        'employeeCostume[round_chest]': {
                            number: true,
                            maxlength : 8,
                        },
                        'employeeCostume[round_thigh]': {
                            number: true,
                            maxlength : 8,
                        },
                        'employeeCostume[round_waist]': {
                            number: true,
                            maxlength : 8,
                        },
                    };
                case 'politic':
                    return {
                        'employeePolitic[party_join_date]': {
                            date: true,
                        },
                        'employeePolitic[union_join_date]': {
                            date: true,
                        },
                        'employeePolitic[party_join_place]': {
                            maxlength : 255,
                        },
                        'employeePolitic[union_join_place]': {
                            maxlength : 255,
                        },
                    };
                case 'military':
                    return {
                        'employeeMilitary[branch]': {
                            maxlength : 255,
                        },
                        'employeeMilitary[left_reason]': {
                            maxlength : 255,
                        },
                        'employeeMilitary[num_disability_rate]': {
                            number: true,
                            max: 100,
                            min: 0,
                            maxlength : 8,
                        },
                        'employeeMilitary[left_date]': {
                            greaterEqualThan: '[name="employeeMilitary[join_date]"]',
                        },
                    };
                case 'japaninfo':
                    return {
                        'employeeJapan[from]': {
                            date: true,
                        },
                        'employeeJapan[to]': {
                            date: true,
                            greaterEqualThan: '[name="employeeJapan[from]"]',
                        },
                        'employeeJapan[note]': {
                            maxlength : 5000,
                        },
                    };
                case 'prize':
                    return {
                        'employeeItemPrize[name]': {
                            required: true,
                            maxlength: 255,
                        },
                        'employeeItemPrize[level]': {
                            maxlength: 255,
                        },
                        'employeeItemPrize[issue_date]': {
                            required: true,
                            date: true,
                        },
                        'employeeItemPrize[expire_date]': {
                            date: true,
                            greaterEqualThan: '[name="employeeItemPrize[issue_date]"]',
                        },
                        'employeeItemPrize[place]': {
                            maxlength: 255,
                        },
                        'employeeItemPrize[place]': {
                            maxlength: 5000,
                        },
                    };
                case 'relationship':
                    return {
                        'relative[name]': {
                            required: true,
                            maxlength: 255,
                        },
                        'relative[id_number]': {
                            maxlength: 100,
                        },
                        'relative[mobile]': {
                            maxlength: 20,
                        },
                        'relative[tel]': {
                            maxlength: 20,
                        },
                        'relative[address]': {
                            maxlength: 255,
                        },
                        'relative[email]': {
                            email: true,
                            maxlength: 100,
                        },
                        'relative[tax_code]': {
                            maxlength: 100,
                        },
                        'relative[career]': {
                            maxlength: 255,
                        },
                        'relative[working_place]': {
                            maxlength: 255,
                        },
                        'relative[note]': {
                            maxlength: 5000,
                        },
                        'relative[deduction_start_date]': {
                            date: true,
                        },
                        'relative[deduction_end_date]': {
                            date: true,
                            greaterEqualThan: '[name="relative[deduction_start_date]"]',
                        },
                    };
                    case 'docexpire':
                        return {
                            'doc[name]': {
                                required: true,
                                maxlength: 255,
                            },
                            'doc[place]': {
                                required: true,
                                maxlength: 255,
                            },
                            'doc[issue_date]': {
                                required: true,
                                date: true,
                            },
                            'doc[expired_date]': {
                                required: true,
                                date: true,
                                greaterEqualThan: '[name="doc[issue_date]"]',
                            },
                            'doc[note]': {
                                maxlength: 5000,
                            },
                        };
                    case 'attach':
                        return {
                            'attach_as': {
                                required: true,
                            },
                            'attach[title]': {
                                required: true,
                                maxlength: 255,
                            },
                            'attach[note]': {
                                maxlength: 5000,
                            },
                        };
                    case 'skill':
                        return {
                            'skill[type]': {
                                required: true,
                                maxlength: 255,
                            },
                            'skill[name]': {
                                required: true,
                            },
                            'skill[level]': {
                                required: true,
                            },
                            'skill[experience]': {
                                required: true,
                                maxlength: 255,
                                ifNumberThenPositive: true
                            },
                        };
                    case 'education':
                        return {
                            'edu[school_id]': {
                                required: true,
                            },
                            'edu[start_at]': {
                                required: true,
                                date: true,
                            },
                            'edu[end_at]': {
                                date: true,
                                greaterEqualThan: '[name="edu[start_at]"]',
                            },
                            'edu[country]': {
                                required: true,
                                maxlength: 50,
                            },
                            'edu[province]': {
                                required: true,
                                maxlength: 255,
                            },
                            'edu[faculty_id]': {
                                required: true,
                            },
                            'edu[major_id]': {
                                required: true,
                            },
                            'edu[quality]': {
                                required: true,
                            },
                            'edu[type]': {
                                required: true,
                            },
                            'edu[awarded_date]': {
                                date: true,
                                greaterEqualThan: '[name="edu[start_at]"]',
                            },
                            'edu[note]': {
                                maxlength: 5000,
                            },
                        };
                    case 'certificate':
                        return {
                            'cer[certificate_id]': {
                                required: true,
                                maxlength: 255,
                            },
                            'cer[level_other]': {
                                maxlength: 255,
                            },
                            'cer[place]': {
                                required: true,
                                maxlength: 255,
                            },
                            'cer[start_at]': {
                                date: true,
                            },
                            'cer[end_at]': {
                                date: true,
                                greaterEqualThan: '[name="cer[start_at]"]',
                            },
                            'cer[p_listen]': {
                                number: true,
                                min: 0,
                                maxlength : 8,
                            },
                            'cer[p_speak]': {
                                number: true,
                                min: 0,
                                maxlength : 8,
                            },
                            'cer[p_read]': {
                                number: true,
                                min: 0,
                                maxlength : 8,
                            },
                            'cer[p_write]': {
                                number: true,
                                min: 0,
                                maxlength : 8,
                            },
                            'cer[p_sum]': {
                                maxlength : 255,
                            },
                            'cer[note]': {
                                maxlength : 5000,
                            },
                            'certificate_image[]': {
                                required: function () {
                                    var length = 0;
                                    $('#image_preview .imgPreviewWrap').each(function () {
                                        length++;
                                    })
                                    if (length > 1) {
                                        return false;
                                    }
                                    else {
                                        return true;
                                    }
                                },
                            },
                        };
                    case 'comexper':
                        return {
                            'com[name]': {
                                required: true,
                                maxlength: 255,
                            },
                            'com[position]': {
                                required: true,
                                maxlength: 255,
                            },
                            'com[address]': {
                                required: true,
                                maxlength: 255,
                            },
                            'com[start_at]': {
                                date: true,
                            },
                            'com[end_at]': {
                                date: true,
                                greaterEqualThan: '[name="com[start_at]"]',
                            },
                        };
                    case 'experience':
                        return {
                            'exp[name]': {
                                required: true,
                                maxlength: 255,
                            },
                            'exp[position]': {
                                required: true,
                                maxlength: 255,
                            },
                            'exp[customer]': {
                                maxlength: 255,
                            },
                            'exp[start_at]': {
                                date: true,
                            },
                            'exp[end_at]': {
                                date: true,
                                greaterEqualThan: '[name="exp[start_at]"]',
                            },
                            'exp[no_member]': {
                                digits: true,
                                maxlength: 4,
                            },
                            'exp[env]': {
                                maxlength: 255,
                            },
                            'exp[other_tech]': {
                                maxlength: 5000,
                            },
                            'exp[implement]': {
                                maxlength: 5000,
                            },
                            'exp[description]': {
                                maxlength: 5000,
                            },
                            'ex_mo[per_y]': {
                                digits: true,
                                max: 100,
                            },
                            'ex_mo[per_m]': {
                                digits: true,
                                max: 12,
                            },
                        };
                    case 'wonsite':
                        return {
                            'ons[place]': {
                                required: true,
                                maxlength: 255,
                            },
                            'ons[start_at]': {
                                required: true,
                                date: true,
                            },
                            'ons[end_at]': {
                                date: true,
                                greaterEqualThan: '[name="ons[start_at]"]',
                            },
                            'ons[reason]': {
                                maxlength: 5000,
                            },
                            'ons[note]': {
                                maxlength: 5000,
                            },
                        };
                        break;
                default: // type = base
                    return baseRules;
            }
        },
    };
    
    var messages = {
        'employee[passport_date_exprie]': {
            greaterEqualThan: trans['base_passport_greater'],
        },
        'employee[offcial_date]': {
            greaterEqualThan: trans['offical_greater'],
        },
        'employee[trial_date]': {
            greaterEqualThan: trans['trial_greater'],
        },
        'employee[trial_end_date]': {
            greaterEqualThan: trans['trial_end_greater'],
        },
        'employee[leave_date]': {
            greaterEqualThan: trans['leave_greater'],
        },
        'employeeJapan[to]': {
            greaterEqualThan: trans['work_japan_to_greater'],
        },
        'employeeItemPrize[expire_date]': {
            greaterEqualThan: trans['prize_expire_greater'],
        },
        'relative[deduction_end_date]': {
            greaterEqualThan: trans['relative_deduction_greater'],
        },
        'employeeMilitary[left_date]': {
            greaterEqualThan: trans['military_left_greater'],
        },
        "doc[expired_date]": {
            greaterEqualThan: trans['doc_expired_greater'],
        },
        "edu[end_at]": {
            greaterEqualThan: trans['edu_end_greater'],
        },
        "edu[awarded_date]": {
            greaterEqualThan: trans['edu_end_greater'],
        },
        "cer[end_at]": {
            greaterEqualThan: trans['cer_end_greater'],
        },
        "cer[p_listen]": {
            min: trans['positive_equal0'],
        },
        "cer[p_speak]": {
            min: trans['positive_equal0'],
        },
        "cer[p_read]": {
            min: trans['positive_equal0'],
        },
        "cer[p_write]": {
            min: trans['positive_equal0'],
        },
        'skill[experience]': {
            ifNumberThenPositive: trans['positive'],
        },
        "com[end_at]": {
            greaterEqualThan: trans['edu_end_greater'],
        },
        'exp[end_at]': {
            greaterEqualThan: trans['exp_end_greater'],
        },
        'ons[end_at]': {
            greaterEqualThan: trans['wo_end_greater'],
        },
        'e_w[insurrance_h_expire]': {
            greaterEqualThan: trans['ew_ih_greater'],
        },
    };
    
    var baseRules = {
        'employee[name]': {
            required: true,
            maxlength: 45,
        },
        'employee[japanese_name]': {
            maxlength: 45,
        },
        'employee[email]': {
            required: true,
            email: true,
            maxlength: 100,
        },
        'employee[birthday]': {
            // date: true,
        },
        'employee[id_card_number]': {
            maxlength: 255,
        },
        // 'employee[id_card_date]': {
        //     date: true,
        // },
        'employee[id_card_place]': {
            maxlength: 255,
        },
        'employee[passport_number]': {
            maxlength: 50,
        },
        'employee[passport_date_start]': {
            // date: true,
        },
        'employee[passport_date_exprie]': {
            // date: true,
            greaterEqualThan: '[name="employee[passport_date_start]"]',
        },
        'employee[passport_addr]':{
            maxlength: 255,
        },
    };

    for (var i = 0; i < 100; i++) {
        var name = 'team[' + i + '][start_at]';
        baseRules[name] = {required: true};
    }

    /**
     * fine uploader
     */
    emplProfile.attach = {
        domW: $('[data-doc-scan="files"]'),
        uploader: null,
        imageInfo: [],
        init: function () {
            if (typeof qq === 'undefined') {
                return true;
            }
            var that = this;
            that.domUpload = $('#profile-attach-files');
            that.uploader = new qq.FineUploader({
                element: that.domUpload[0],
                request: {
                    endpoint: globalVar.urlUploadAttachFile,
                    params: {
                        _token: siteConfigGlobal.token
                    }
                },
                deleteFile: {
                    enabled: true,
                    endpoint: globalVar.urlDeleteAttachFile,
                    confirmMessage: 'Are you sure you want to delete {filename}?',
                    forceConfirm: true,
                    params: {
                        _token: siteConfigGlobal.token
                    }
                },
                validation: {
                    allowedExtensions: ['jpeg', 'jpg', 'gif', 'png', 'pdf', 'doc', 'docx',
                        'xls', 'xlsx'],
                    sizeLimit: 5 * 1000 * 1000,
                },
                callbacks: {
                    onComplete: function (fineImageid, fileName, response) {
                        if (response.item2_id) {
                            that.uploader.setUuid(fineImageid, response.item2_id);
                        }
                        if (response.item2_path) {
                            that.imageInfo[fineImageid] = globalVar.urlAssetStorage + response.item2_path;
                        }
                        if (response.file_upload) {
                            that.insertResponseInputFile(response.file_upload);
                        }
                    },
                },
                thumbnails: {
                    placeholders: {
                        notAvailablePath: globalVar.urlFileNotAvai,
                    },
                },
            });
            that.initFiles();
            $(document).on('click', '.qq-thumbnail-selector[qq-server-scale], [data-qq-selector="download"]', function () {
                var fileId = that.uploader.getId(this);
                if (typeof that.imageInfo[fileId] !== 'string') {
                    return true;
                }
                var win = window.open(that.imageInfo[fileId], '_blank');
                win.focus();
            });
        },
        /**
         * insert input hidden show response file data
         */
        insertResponseInputFile: function (fileUploadResult) {
            var that = this,
                domInputFile = that.domUpload.next('input[name="file_json"]'), val;
            if (!domInputFile.length) {
                that.domUpload.after('<input type="hidden" name="file_json" value="" />');
                domInputFile = that.domUpload.next('input[name="file_json"]');
                val = '';
            } else {
                val = domInputFile.val() + ',';
            }
            domInputFile.val(val + JSON.stringify(fileUploadResult));
        },
        /**
         * active uploader
         *
         * @param {object} response
         */
        activeUpload: function (response) {
            var that = this;
            if (that.domW.hasClass('hidden')) {
                if (typeof response === 'object' && response.relative_id) {
                    globalVar.urlUploadAttachFile = RKExternal.stringToUrlReplace(globalVar.urlUploadAttachFile, '0', ''+response.relative_id);
                    globalVar.urlDeleteAttachFile = RKExternal.stringToUrlReplace(globalVar.urlDeleteAttachFile, '0', ''+response.relative_id);
                }
                that.domW.removeClass('hidden');
                that.init();
            }
        },
        /**
         * init files attach
         */
        initFiles: function () {
            if (typeof attachFiles !== 'object') {
                return true;
            }
            var that = this;
            $.each (attachFiles, function (i, v) {
                that.uploader.addInitialFiles([
                    {
                        name: v.file_name,
                        uuid: v.file_id,
                        size: v.file_size * 1000,
                        thumbnailUrl: globalVar.urlAssetStorage + v.path,
                    },
                ]);
                that.imageInfo[i] = globalVar.urlAssetStorage + v.path;
            });
        }
    };
    formEmployeeInfo.validate();

    //enable select team before save because need get value of new team
    $(document).on('click', 'button[type=submit].btn-save-profile', function() {
        $('.group-team-position').each( function() {
            $(this).find('select').removeAttr('disabled');
        });
    })

    var tempFiles = [];
    var file_id = [];
    $("#certificate-image").change(function () {
        var el_this = $(this);
        var files = el_this[0].files;
        for (var i = 0; i < files.length; i++) {
            tempFiles.push(files[i]);
        }
    });
    $("#image_preview button.action-delete").click(function () {
        var el_this = $(this);
        var id = el_this.parent().parent().attr('data-id');
        var name = el_this.parent().parent().attr('data-name');
        file_id.push({id:id, name: name});
    });

    // submit form success callback after
    RKExternal.formEmployeeInfoSuccess = function(response, form) {
        // response id new team
        if(response.cer == 1) {
            var formData = new FormData();
            formData.append('_token', form.find('input[name="_token"]').val());
            formData.append('file_id', JSON.stringify(file_id));
            var size = 0;
            $('#image_preview .imgPreviewWrap').each(function (e_index) {
                if (typeof $(this).attr('data-index') != "undefined") {
                    var item_index = $(this).attr('data-index');
                    var file = tempFiles[item_index];
                    if (file) {
                        if (typeof file == 'object') {
                            size += file.size;
                            formData.append('images[' + e_index + ']', file);
                        } else {
                            formData.append('image_ids[' + e_index + ']', file);
                        }
                    }
                }
            });
            $.ajax({
                type: 'POST',
                url: response.url,
                data: formData,
                contentType: false,
                processData: false,
                success: function () {
                    tempFiles = [];
                    // document.location.reload();
                    window.location.reload();
                },
                error: function () {
                    window.location.reload();
                },
                complete: function () {
                    window.location.reload();
                }
            });
        }
        if (response.arrNew) {
            for (var u = 0; u < response.arrNew.length; u++) {
                $('.team-edit').find('[name="team['+response.arrNew[u]['number']+'][id]"]').val(response.arrNew[u]['id']);
                $('.team-edit').find('[name="team['+response.arrNew[u]['number']+'][id]"]').after('<input type="hidden" class="input-team-hidden" name="team['+response.arrNew[u]['number']+'][team]" value="'+response.arrNew[u]['team']+'"/>');
            }
        }
        // disable team select after save
        $('.team-edit .team').prop('disabled', true);
        // change avatar in nav header
        if (globalVar.isSelfProfile && $('img#employee-avatar_url').length) {
            //noimage: <i class="fa fa-user"></i>
            $('[data-dom-flag="profile-avatar"]')
                .html('<img src="' + $('img#employee-avatar_url').attr('src')
                    + '" class="user-image" />'
                );
        }
        if (response.employee_id) { // after create employee success
            $('[data-flag-profile="base-email"]').remove();
            // change url action form
            form.attr(
                'action', 
                RKExternal.stringToUrlReplace(form.attr('action'), '0', ''+response.employee_id)
            );
            // change url browser
            RKExternal.urlReplace(
                RKExternal.stringToUrlReplace(globalVar.urlViewProfile, '0', ''+response.employee_id)
            );
            // change url left menu
            $('[data-flag-dom="employee-left-menu"] > li.disabled').each(function(i, v) {
                $(v).removeClass('disabled');
                var aDom = $(v).children('a');
                aDom.attr('href', RKExternal.stringToUrlReplace(aDom.attr('href'), '0', ''+response.employee_id));
            });
            // show delete button
            var btnRemove = $('[data-flag-dom="btn-employee-remove"]');
            if (btnRemove.length) {
                btnRemove.removeClass('hidden');
                btnRemove.attr('action', RKExternal.stringToUrlReplace(btnRemove.attr('action'), '0', ''+response.employee_id));
            }
        }
        if (response.urlFormSubmitChange) {
            form.attr('action', response.urlFormSubmitChange);
        }
        if (response.urlFormDeleteItem) {
            var btnRemove = $('[data-flag-dom="btn-employee-remove-item"]');
            btnRemove.removeClass('hidden');
            btnRemove.attr('action', response.urlFormDeleteItem);
        }
        /*if (response.attach) {
            $('[data-flag-attach="input-as"]').val(1);
            $('[data-flag-attach="name-show"]').removeClass('hidden');
            $('[data-flag-attach="file-name"]').text(response.attach.name);
        }*/
        if (globalVar.tabType === 'attach') {
            emplProfile.attach.activeUpload(response);
        }
        if(typeof isCer != "undefined" && isCer === true) {
            document.location.reload();
        }
    };
    $('[data-flag-dom="employee-left-menu"] > li').click(function () {
        if ($(this).hasClass('disabled')) {
            return false;
        }
    });
    selectSearchReload();
    // member list - chooose team select
    $('.input-select-team-member').change(function(event) {
        var tab = $(".tab-content .tab-content .active").attr('id');
        var value = $(this).val();
        if (tab) {
            window.location.href = value + '#' + tab;
        } else {
            window.location.href = value;
        }

    });
    if ($('[data-checkbox-source]').length) {
        /**
         * checkbox enable field
         */
        $('[data-checkbox-source]').change(function() {
            var thisCheckbox = $(this),
            type = thisCheckbox.data('checkbox-source'),
            isChecked = thisCheckbox.is(':checked');
            if (!type) {
                return true;
            }
            $('[data-checkbox-dist="'+type+'"]').prop('disabled', !isChecked);
        });
    }
    /**
     * tab relationship
     */
    // checkbox birthday type
    var checkboxBirthFunct = {
        init: function () {
            var that = this;
            that.checkbox = $('[data-flag-dom="rela-birth-checkbox"]');
            if (!that.checkbox.length) {
                return true;
            }
            that.action();
            that.checkbox.change(function () {
                that.action();
            });
        },
        action: function () {
            var that = this;
            if (that.checkbox.is(':checked')) {
                $('[data-flag-dom="rela-birth-day"]').addClass('hidden');
                $('[data-flag-dom="rela-birth-year"]').removeClass('hidden');
            } else {
                $('[data-flag-dom="rela-birth-day"]').removeClass('hidden');
                $('[data-flag-dom="rela-birth-year"]').addClass('hidden');
            }
        },
        baseMatchHeight: function () {
            $('[data-height-same="team"]').matchHeight({
                child: '.box',
                notHeight: true
            });
        },
    };
    /**
     * skill action switch
     */
    switch (globalVar.tabType) {
        case 'skill':
            RKExternal.autoComplete.init({
                beforeRemote: function () {
                    var typeSkill = $('[name="skill[type]"]').val();
                    if (!typeSkill || typeSkill === globalVar.skillTypeAnother) {
                        return false;
                    }
                    return {
                        params: {
                            'type': $('[name="skill[type]"]').val(),
                        },
                    };
                },
            });
            break;
        case 'education':
            RKExternal.autoComplete.init({
                afterSelected: function (itemData) {
                    if (itemData.country) {
                        $('[name="edu[country]"]').val(itemData.country).change();
                    }
                    if (itemData.province) {
                        $('[name="edu[province]"]').val(itemData.province).change();
                    }
                },
            });
            break;
        case 'certificate':
            emplProfile.cer = {
                init: function () {
                    var that = this;
                    that.changeType($('#select-cer option:selected').attr("type"));
                    $('[name="cer[certificate_id]"]').change(function () {
                        var type = $("option:selected", this).attr("type");
                        that.changeType(type);
                        $('#select-type').val(type).change();
                    });
                },
                changeType: function (value) {
                    if (value != 1) {
                        value = 2;
                    }
                    if (value != 1) {
                        $('[data-cer-level="1"] input').val('');
                    }
                    $('[data-cer-level]').addClass('hidden');
                    $('[data-cer-level="'+value+'"]').removeClass('hidden');
                },
            };
            emplProfile.cer.init();
            RKExternal.autoComplete.init();
            break;
        case 'experience':
            $('[name="exp[start_at]"], [name="exp[end_at]"]').on('dp.change', function () {
                var start = $('[name="exp[start_at]"]').data('DateTimePicker').date(),
                end = $('[name="exp[end_at]"]').data('DateTimePicker').date();
                if (!start || !end || end.isBefore(start)) {
                    return true;
                }
                var year = end.diff(start, 'years'),
                month = end.diff(start, 'months');
                month = month - year * 12;
                month = month > 0 ? month : 0;
                $('[name="ex_mo[per_y]"]').val(year);
                $('[name="ex_mo[per_m]"]').val(month);
                
            });
            break;
        case 'base':
            checkboxBirthFunct.baseMatchHeight();
            break;
        default:
            //nothing;
    }
    /**
     * update check/uncheck function unit
     * @param {type} dom
     * @returns {undefined}
     */
    var updateCheckFunction = function (dom)
    {
        var dataId = dom.data('id');
        if (dom.is(':checked')) {
            $('.team-group-function[data-id=' + dataId + ']').show();
        } else {
            $('.team-group-function[data-id=' + dataId + ']').hide();
        }
    };
    //update checkbox is-function
    $('input.input-is-function').each( function( i, v ) {
        updateCheckFunction($(this));
    });
    $('input.input-is-function').on('change', function (event) {
        updateCheckFunction($(this));
    });

    /**
     * team position of profile init
     */
    emplProfile.teamPosi = {
        change: function () {
            var btnRow = $('[data-flag-dom="btn-add-profile-team"]:not([data-btn-add="profile-last"])'),
            btnLast = $('[data-flag-dom="btn-add-profile-team"][data-btn-add="profile-last"]');
            if (btnRow.length) {
                btnLast.addClass('hidden');
                btnRow.addClass('hidden');
                btnRow.last().removeClass('hidden');
            } else {
                btnLast.removeClass('hidden');
            }
        },
        left: function () {
            var that = this;
            that.domLeft = $('[data-flag-dom="label-left"]');
            that.inputLeft = $('[name="employee[leave_date]"]');
            if (!that.domLeft.length || !that.inputLeft.length) {
                that.domLeft.addClass('hidden');
                return true;
            }
            this.leftExec();
            this.leftChange();
        },
        leftExec: function () {
            var that = this;
            if (!that.inputLeft.val().trim()) {
                that.domLeft.addClass('hidden');
                return true;
            }
            var textLeft = that.domLeft.data('text-left'),
            textWill = that.domLeft.data('text-will'),
            date = that.inputLeft.data('DateTimePicker').date().startOf('day'),
                now = new moment().startOf('day');
            if (now.isAfter(date)) {
                // left
                that.domLeft.html(textLeft);
            } else {
                //will left
                that.domLeft.html(textWill);
            }
            that.domLeft.removeClass('hidden');
        },
        leftChange: function () {
            var that = this;
            that.inputLeft.on('dp.change', function () {
                that.leftExec();
            });
        },
    };
    /**
     * add / remove team action
     */
    var htmlAddTeamPositonOrigin = $('.group-team-position-orgin').html();
    $('.group-team-position-orgin').remove();
    var dataIdLast = $('.box-form-team-position').children('.group-team-position').length;
    if (!dataIdLast) {
        dataIdLast = 0;
    } else {
        dataIdLast = parseInt(dataIdLast);
    }
    if (dataIdLast == 1) {
        $('.box-form-team-position .group-team-position .input-remove').addClass('warning-action');
    }
    emplProfile.teamPosi.change();
    $(document).on('click touchstart', '[data-flag-dom="btn-add-profile-team"]', function(event) {
        dataIdLast++;
        var htmlAddTeamPositon = $(htmlAddTeamPositonOrigin);
        var startName = 'team[' + dataIdLast + ']';
        htmlAddTeamPositon.find('.input-team-position.input-team .input-id-hidden')
            .attr('name', startName + '[id]');
        htmlAddTeamPositon.find('.input-team-position.input-team select')
            .attr('name', startName + '[team]');
        htmlAddTeamPositon.find('.input-team-position.input-team select')
            .attr('id', dataIdLast);
        htmlAddTeamPositon.find('.input-team-position.input-team .input-team-hidden')
            .attr('name', startName + '[team]');
        htmlAddTeamPositon.find('.input-team-position.input-position select')
            .attr('name', startName + '[position]');
        htmlAddTeamPositon.find('.input-team-position.input-start_date .date-picker')
            .attr('name', startName + '[start_at]');
        htmlAddTeamPositon.find('.input-team-position.input-end_date .date-picker')
            .attr('name', startName + '[end_at]');
        htmlAddTeamPositon.find('.input-team-position.is_working .is-working')
            .attr('name', startName + '[is_working]');
        htmlAddTeamPositon.find('select[data-flag-dom="select2"]').addClass('select-search');
        $('.box-form-team-position').append(htmlAddTeamPositon);
        $('.box-form-team-position .group-team-position .input-remove').removeClass('warning-action');
        
        if ($('.box-form-team-position').children('.group-team-position').length == 1) {
            $('.box-form-team-position .group-team-position .input-remove').addClass('warning-action');
        } else {
            $('.box-form-team-position .group-team-position .input-remove').addClass('warn-confirm');
        }
        $('input[name="employee_team_change"]').val(1);
        selectSearchReload();
        emplProfile.teamPosi.change();
        checkboxBirthFunct.baseMatchHeight();

        // re-init datetimepicker
        $('input[data-flag-type="date"]').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
        });
    });

    // remove profile team
    $(document).on('click touchstart', '[data-flag-dom="btn-remove-profile-team"]', function(event) {
        $('input[name="employee_team_change"]').val(1);
        emplProfile.teamPosi.change();
        checkboxBirthFunct.baseMatchHeight();
    });
    
    //change select, update team availabel
    $(document).on('change', '.group-team-position select', function(e) {
        $('input[name="employee_team_change"]').val(1);
    });
    
    /**
     * update role label current
     */
    $('#employee-role-form').on('hide.bs.modal', function (e) {
        var htmlRoleList = '';
        $(this).find('.checkbox input:checked').each(function (i,k) {
            htmlRoleList += '<li><span>';
            htmlRoleList += $(this).parent().text().trim();
            htmlRoleList += '</li></span>';
        });
        $('ul.employee-roles').html(htmlRoleList);
        checkboxBirthFunct.baseMatchHeight();
    });
    $('[name="role[]"]').change(function() {
        $('input[name=employee_role_change]').val(1);
    });
    /**
     * padding table rule
     */
    lengthTrTableRule = $('.team-rule-wrapper table.table-team-rule tbody tr').length;
    $('.team-rule-wrapper table.table-team-rule tbody tr:last-child .form-input-dropdown').addClass('input-rule-last');
    $('.team-rule-wrapper table.table-team-rule tbody tr:nth-child(' + (lengthTrTableRule - 1) + ') .form-input-dropdown').addClass('input-rule-last');
    var paddingTableRule = $('.team-rule-wrapper .table-responsive').css('padding-bottom');
    $('.input-rule-last').on('show.bs.dropdown', function () {
        $('.team-rule-wrapper .table-responsive').css('padding-bottom', '90px');
    });
    $('.input-rule-last').on('hide.bs.dropdown', function () {
        $('.team-rule-wrapper .table-responsive').css('padding-bottom', paddingTableRule);
    });
    $(document).on('change', '#program-id', function() {
        var selectedText = $(this).find('option:selected').text();
        var $input = $(this).parent().find('.program-name');
        $input.val(selectedText);
    });
    
    checkboxBirthFunct.init();
    RKExternal.uploadFile.init();
    RKExternal.select2.init({
        minimumInputLength: 1
    });
    emplProfile.teamPosi.left();
    emplProfile.attach.init();

    $('#modal_contract_history').on('shown.bs.modal', function (e) {
        var elModal = $('#modal_contract_history');
        elModal.find('.modal-body').html('');
        $.ajax({
            url: elModal.data('url'),
            type: 'GET',
            data: {
                employeeId: elModal.data('employee-id')
            },
            success: function (html) {
                elModal.find('.modal-body').html(html);
            },
        });
    });

    //profile business trip
    if ($('#business_trip_tbl').length > 0) {
        var businessTbl = $('#business_trip_tbl');
        var noneRow = businessTbl.find('tbody tr.none-row');
        var numPerPage = parseInt($('.grid-pager-box select[name="limit"] option:selected').attr('data-value'));

        function initDatePicker() {
            $('#business_trip_tbl .date-picker').datetimepicker({
                format: 'YYYY-MM-DD'
            });
        }

        $('#btn_add_business').click(function(e) {
            e.preventDefault();
            var lastItem = businessTbl.find('tbody tr:last()');
            if (lastItem.hasClass('new-item') && !lastItem.hasClass('hidden')) {
                return;
            }
            var template = $('#tr_business_tpl').clone().removeAttr('id').removeClass('hidden');
            noneRow.addClass('hidden');
            template.appendTo(businessTbl.find('tbody'));
            initDatePicker();
        });

        $('body').on('click', '.btn-busi-delete', function (e) {
            e.preventDefault();
            var btn = $(this);
            var row = btn.closest('tr');
            if (row.hasClass('new-item')) {
                row.remove();
                return false;
            }
            var url = btn.attr('data-url');
            if (btn.is(':disabled') || !url) {
                return false;
            }
            bootbox.confirm({
                message: btn.attr('data-noti'),
                className: 'modal-warning',
                callback: function (result) {
                    if (result) {
                        btn.prop('disabled', true);
                        row.addClass('bg-yellow');
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                id: row.find('[name="id"]').val(),
                                _token: siteConfigGlobal.token
                            },
                            success: function (result) {
                                if (result.status) {
                                    var noIndex = 0;
                                    var lastIndex = businessTbl.find('tbody tr:last() td.col-stt');
                                    if (lastIndex.length > 0) {
                                        noIndex = parseInt(lastIndex.text());
                                    }
                                    var totalItem = parseInt($('.grid-pager-box .total').text());
                                    if (noIndex === totalItem) {
                                        row.remove();
                                        businessTbl.find('tbody tr:not(:first)').not('.hidden').not('.new-item').not('.edit-item').each(function (index) {
                                            $(this).find('.col-stt').text(index + 1);
                                        });
                                        decPaginateTotal();
                                    } else {
                                        window.location.reload();
                                    }
                                } else {
                                    bootbox.alert({
                                        message: result.message,
                                        className: 'modal-danger',
                                    });
                                }
                            },
                            complete: function () {
                                btn.prop('disabled', false);
                                row.removeClass('bg-yellow');
                            },
                        });
                    }
                },
            });
        });

        $('body').on('click', '.btn-busi-edit', function (e) {
            e.preventDefault();
            var btn = $(this);
            var row = btn.closest('tr');
            if (row.next('.edit-item').length > 0) {
                return;
            }
            var itemId = row.find('input[name="id"]').val();
            var template = $('#tr_business_tpl').clone().removeAttr('id').removeClass('hidden').removeClass('new-item').addClass('edit-item');
            template.find('.field').each(function () {
                var inputName = $(this).attr('name');
                var td = row.find('td.col-' + inputName);
                if (td.length > 0) {
                    $(this).val(td.text());
                }
            });
            template.find('td.col-actions').prepend('<input type="hidden" name="id" value="'+ itemId +'" class="field">');
            template.find('td.col-actions .btn-busi-delete').addClass('hidden');
            template.find('td.col-actions .btn-busi-cancel').removeClass('hidden');
            template.insertAfter(row);
            initDatePicker();
        });

        $('body').on('click', '.btn-busi-cancel', function (e) {
            e.preventDefault();
            $(this).closest('tr').remove();
        });

        $('body').on('click', '.btn-busi-save', function (e) {
            e.preventDefault();
            var btn = $(this);
            var row = btn.closest('tr');
            var field = row.find('.field');
            var url = btn.attr('data-url');
            var icon = btn.find('i');
            if (field.length < 1 || !url || icon.hasClass('.fa-spin')) {
                return;
            }
            var data = {
                _token: siteConfigGlobal.token,
            };
            //validate required
            var errorValid = false;
            field.each(function() {
                var fieldVal = $(this).val().trim();
                if ($(this).hasClass('required')) {
                    if (!fieldVal) {
                        if ($(this).next('.error').length < 1) {
                            $(this).after('<label class="error">'+ textErrorRequired +'</label>');
                        }
                        errorValid = true;
                    } else {
                        $(this).next('.error').remove();
                    }
                }
                data[$(this).attr('name')] = fieldVal;
            });
            //validate date
            var startDate = row.find('[name="start_at"]');
            var endDate = row.find('[name="end_at"]');
            if (endDate.val() && startDate.val()) {
                if (endDate.val() < startDate.val()) {
                    if (endDate.next('.error').length < 1) {
                        endDate.after('<label class="error">'+ textErrorEndDate +'</label>');
                    }
                    errorValid = true;
                } else {
                    endDate.next('.error').remove();
                }
            }
            if (errorValid) {
                return false;
            }

            icon.removeClass('fa-save').addClass('fa-refresh fa-spin');
            btn.prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                success: function (result) {
                    if (!result.status) {
                        bootbox.alert({
                            message: result.message,
                            className: 'modal-danger',
                        });
                        return;
                    }
                    var prevRow = row.prev('tr');
                    var noIndex = 0;
                    var isEdit = row.hasClass('edit-item');
                    if (prevRow.length > 0) {
                        var prevIndex = prevRow.find('.col-stt').text();
                        noIndex = prevIndex ? parseInt(prevIndex) : 0;
                    }
                    if (noIndex >= numPerPage && !isEdit) {
                        window.location.reload();
                        return;
                    }
                    if (!isEdit) {
                        noIndex++;
                    }
                    var item = result.item;
                    item.stt = noIndex;
                    var template = row.clone().removeClass('edit-item new-item');
                    $.each(item, function (key, value) {
                        template.find('.col-' + key).text(value);
                    });
                    if (template.find('td.col-actions [name="id"]').length < 1) {
                        template.find('td.col-actions').prepend('<input type="hidden" name="id" value="'+ item.id +'" class="field">');
                    }
                    template.find('.col-actions .btn-busi-delete').removeClass('new-del hidden');
                    template.find('.col-actions .btn-busi-save').addClass('hidden');
                    template.find('.col-actions .btn-busi-cancel').addClass('hidden');
                    template.find('.col-actions .btn-busi-save i.fa').addClass('fa-save').removeClass('fa-refresh fa-spin');
                    template.find('.col-actions .btn-busi-edit').removeClass('hidden');
                    if (isEdit) {
                        prevRow.replaceWith(template);
                        row.remove();
                    } else {
                        row.replaceWith(template);
                        incPaginateTotal();
                    }
                },
                complete: function () {
                    btn.prop('disabled', false);
                    icon.addClass('fa-save').removeClass('fa-refresh fa-spin');
                },
            });
        });
    }
})(jQuery, RKExternal, document, window, siteConfigGlobal);

/**
 * If .container-question height < child div reset this.height = new height
 */

function fixHeight(){
    //reset height
    $('.container-question').css('height','auto');
    $('.fix-height').css('height','auto');
    
    if($(window).width() >= 768){
        $('.container-question').each(function(){
            var height = 0;
            $(this).find('.fix-height').each(function(){
                if(height < $(this).outerHeight()){
                    height = $(this).outerHeight();
                }
            });

            if($(this).height() < height){
                $(this).height(height);
            } 
            var h = $(this).height();
            var h2 = $(this).find('.comment').outerHeight();
            $(this).find('.comment').css('margin-top', (h-h2)/2);
            $(this).find('.fix-height').outerHeight(h);
        });
    } else {
        $('.container-question').css('height','auto');
        $('.fix-height').css('height','auto');
    }
}

$(document).on('change', '#program-id', function() {
   var selectedText = $(this).find('option:selected').text();
   var $input = $(this).parent().find('.program-name');
   $input.val(selectedText);
});

function hoverHelp() {
    $('.tooltip-group i.fa-question-circle').hover(function() {
            $(this).closest('.tooltip-group').find('.tooltip').css({
                'display': 'block',
                'z-index': 1050,
                'opacity': 1,
                'visibility': 'visible',
            });
        }, function () {
            $(this).closest('.tooltip-group').find('.tooltip').css({
                'display': 'none',
                'z-index': 1050,
                'opacity': 0,
                'visibility': 'none',
            });
        }
    );
}

$(document).on('click', '.btn-edit-profile', function () {
    $('#form-employee-info input, #form-employee-info select, #form-employee-info textarea').not('.fill-disable').prop('disabled', false);
    $('#form-employee-info .btn-del-empl').removeClass('hidden');
    $("#form-employee-info .imgPreviewWrap button").prop( "disabled", false);
    $('.team-edit .team').prop('disabled', true);
    var teamLength = $('.box-form-team-position').children('.group-team-position').length;
    if (teamLength === 1) {
        $('.box-form-team-position .group-team-position .input-remove').addClass('warning-action');
        $('.box-form-team-position .group-team-position .input-remove').removeClass('warn-confirm');
    }
    $('#form-employee-info button[type=submit]').show();
    $('#form-employee-info button[id=request-approve]').show();
    $(this).addClass('hidden');
    $('[data-checkbox-source]').change();
    var btnRow = $('[data-flag-dom="btn-add-profile-team"]:not([data-btn-add="profile-last"])'),
        btnLast = $('[data-flag-dom="btn-add-profile-team"][data-btn-add="profile-last"]'),
        btnRemoveTeam = $('[data-flag-dom="btn-remove-profile-team"]');
    btnRemoveTeam.removeClass('hidden');
    if (btnRow.length) {
        btnLast.addClass('hidden');
        btnRow.addClass('hidden');
        btnRow.last().removeClass('hidden');
    } else {
        btnLast.removeClass('hidden');
    }
    selectSearchReload();
});

function checkDisplayResponsibleTeam() {
    var checkOptionTeam = [];
    var position = $('.group-team-position');
    $('.team-edit select[data-flag-dom="select2"]').each(function () {
        if (checkOptionTeam.indexOf($(this).val(), 0) == -1) {
            checkOptionTeam.push($(this).val());
        }
    });

    if (checkOptionTeam.indexOf("22") === -1) {// 22 is value team pqa.
        $('.team-responsible').addClass('hidden');
    } else {
        $('.team-responsible').removeClass('hidden');
    }
}

$(document).on('change', '.input-team-position select[data-flag-dom="select2"]', function() {
    checkDisplayResponsibleTeam();
});

$(document).on('change', '.group-team-position .team', function() {
    var datepickerStart = $(this).closest('.group-team-position').find('.input-start-at');
    var datepickerEnd = $(this).closest('.group-team-position').find('.input-end-at');
    $(this).focusout();
    datepickerStart.val('');
    datepickerEnd.val('');
    setTimeout(function() { datepickerStart.focus() }, 300);
    var thisTeam = $(this);
    var thisEndDay = $(this).closest('.group-team-position').find('.input-end-at');
    $('.group-team-position .team').each( function() {
        var endDay = $(this).closest('.group-team-position').find('.input-end-at');
        var startDay = $(this).closest('.group-team-position').find('.input-start-at');
        endDay.parent().find('label.error').remove();
        startDay.parent().find('label.error').remove();
        if (thisTeam.val() === $(this).val() && (thisTeam.attr('id') !== $(this).attr('id'))) {
            if (thisEndDay.val() === '' && endDay.val() === '') {
                thisEndDay.after('<label class="error">'+ noEndDate +'</label>');
                endDay.after('<label class="error">'+ noEndDate +'</label>');
                return false;
            }
        }
    });
});

$(document).on('click', 'button[type=submit].btn-save-profile', function() {
    var status = true;
    hasChecked = false;
    var today = new Date();
    var countTeam = $(".group-team-position").length;
    now = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
    var teamsJP = [];
    $('.group-team-position').each( function() {
        var endDay = $(this).find('.input-end-at').val();
        var startDay = $(this).find('.input-start-at').val();
        var teamId = $(this).find('.select2-hidden-accessible').val();
        if (countTeam === 1) {
            $(this).find('.is-working').prop( "checked", true );
        }
        if (jQuery.inArray(teamId, listTeamJP) >= 0) {
            teamsJP.push(teamId);
        }
        if($(this).find('.is-working').is(':checked')) {
            if ((endDay !== '' && endDay >=  now) || endDay === '') {
                $('.is-working-error').addClass('hidden');
                hasChecked = true;
            }
        }
        if (endDay !== '' && endDay <= startDay) {
            RKExternal.notify(timeEndAfterStart, false);
            status = false;
            return false;
        }
    });
    if (teamsJP.length > 0) {
        checkJP = 1;
    } else {
        checkJP = 0;
    }

    if (isJP !== checkJP) {
        $('#notice-change-team').modal('show');
        return false;
    }
    if (!status) {
        return false;
    }
    if (!hasChecked) {
        $('.is-working-error').removeClass('hidden');
        return false;
    }
});

$('#confirm-change-team').click(function () {
    isJP = checkJP;
    $('#notice-change-team').modal('hide');
    if (!hasChecked) {
        $('.is-working-error').removeClass('hidden');
        return false;
    }
});

/* submit form profile base */
var isSubmitUploadAvatar = false,
    originalData = getDataProfileBase($('#form-employee-info').serializeArray()),
    currentData = originalData;

//preview image
if (typeof typeAllow === 'object') {
    $('.input-box-img-preview').previewImage({
        type: typeAllow,
        size: sizeAllow,
        default_image: imagePreviewImageDefault,
        message_size: txtMessageSize,
        message_type: txtMessageType,
    });
}
$('#employee-avatar_url').on('click', function(event) {
    event.preventDefault();
    var $ava = $('#avatar_url');
    if ($ava.length) {
        $ava.trigger('click');
    }
});
$("#avatar_url").change(function() {
    isSubmitUploadAvatar = true;
    $("#form-employee-info").submit();
});
// get form data
RKExternal.callbackGetFormData = function (dom) {
    var formData = new FormData();
    // upload avatar
    if (isSubmitUploadAvatar === true) {
        isSubmitUploadAvatar = false;
        var fileUpload = dom.find('input[name="avatar_url"]')[0].files[0];
        if (!fileUpload || typeAllow.indexOf(fileUpload.type) === -1 || fileUpload.size / 1000 > sizeAllow) {
            return false;
        }
        formData.append('avatar_url', fileUpload);
        formData.append('_token', siteConfigGlobal.token);
        return formData;
    }
    /* save profile base */
    var dataInput = dom.serializeArray();
    currentData = getDataProfileBase(dataInput);
    var isChangeBasic = !compareObjects(originalData.basic, currentData.basic),
        isChangeTeam = !compareObjects(originalData.teams, currentData.teams)
            || !compareObjects(originalData.team_responsible, currentData.team_responsible),
        isChangeRole = JSON.stringify(originalData.roles) !== JSON.stringify(currentData.roles);
    // user not change data basic, team and role.
    if (!isChangeBasic && !isChangeTeam && !isChangeRole) {
        $('.team-edit .team').prop('disabled', true);
        RKExternal.notify(globalPassModule.trans.save_successfully, true, {delay: 10000});
        return false;
    }

    dataInput.map(function (item) {
        formData.append(item.name, item.value);
    });
    // push flag change data
    formData.append('is_change_basic', isChangeBasic ? '1' : '0');
    formData.append('is_change_team', isChangeTeam ? '1' : '0');
    formData.append('is_change_role', isChangeRole ? '1' : '0');
    return formData;
};
// update original data
RKExternal.updateOriginalData = function (response) {
    if (typeof response.responseJSON === 'object') {
        response = response.responseJSON;
    }
    if (response.status + '' === '1') {
        originalData = currentData;
    }
};
// get data input to compare new data vs old data
function getDataProfileBase(dataInput) {
    var data = {
        roles: [],
        basic: [],
        teams: [],
        team_responsible: [],
        other: [],
    };
    dataInput.map(function (item) {
        var name = item.name || '';
        if (/^employee\[.+\]$/i.test(name)) {
            data.basic[name] = item.value;
            return;
        }
        if (/^team\-responsible\[\]$/i.test(name)) {
            data.team_responsible.push(item.value);
            return;
        }
        if (/^team\[.+\]\[.+\]$/i.test(name)) {
            data.teams[name] = item.value;
            return;
        }
        if (/^role\[\]$/i.test(name)) {
            data.roles.push(item.value);
            return;
        }
        data.other[name] = item.value;
    });
    return data;
}
// compare 2 objects (1 level)
function compareObjects(objX, objY) {
    var keyX = Object.keys(objX),
        lenX = keyX.length,
        key, i, itemX, itemY;
    if (lenX !== Object.keys(objY).length) {
        return false;
    }
    for (i = 0; i < lenX; i++) {
        key = keyX[i];
        itemX = objX[key];
        itemY = objY[key];
        if ($.isArray(itemX) && $.isArray(itemY)) {
            itemX.sort();
            itemY.sort();
            if (JSON.stringify(itemX) === JSON.stringify(itemY)) {
                continue;
            }
        }
        if (itemX !== itemY) {
            return false;
        }
    }
    return true;
}
/* end submit form profile base */
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
