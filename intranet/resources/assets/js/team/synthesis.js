(function($, RKExternal, document, window){
var globalVar = typeof globalPassModule === 'object' ? globalPassModule : {},
trans = typeof globalTrans === 'object' ? globalTrans : {},
projTag = typeof varProjTag === 'object' ? varProjTag : {},
dbTrans = typeof globalDbTrans === 'object' ? globalDbTrans : {};
var cv = {};
/**
 * change language
 */
cv.lang = {
    cookieKey: 'cv_lang',
    init: function() {
        var that = this;
        var langCookie = $.cookie(that.cookieKey);
        if (langCookie !== 'en') {
            langCookie = 'ja';
        }
        $('[name="cv_view_lang"][value="'+langCookie+'"]').prop('checked', true);
        that.exec();
        $('[name="cv_view_lang"]').change(function () {
            that.exec();
        });
    },
    /**
     * get language choose current
     *
     * @return {String}
     */
    getLangCur: function() {
        var lang = $('[name="cv_view_lang"]:checked').val();
        if (lang !== 'en') {
            lang = 'ja';
        }
        return lang;
    },
    exec: function() {
        var that = this,
        lang = that.getLangCur();
        $.cookie(that.cookieKey, lang, { expires: 7 });
        $('[data-lang-show]:not([data-lang-show="'+lang+'"])').addClass('hidden');
        $('[data-lang-show="'+lang+'"]').removeClass('hidden');
        $('[data-lang-r]').each(function (i, item) {
            var word = $(item).data('lang-r');
            if (typeof trans[lang][word] !== 'undefined') {
                $(item).html(trans[lang][word]);
            } else {
                $(item).html('');
            }
        });
        $('[data-db-lang]').each (function (i, v) {
            var name = $(v).data('db-lang');
            if (typeof dbTrans[name + '_' + lang] !== 'undefined') {
                $(v).html(dbTrans[name + '_' + lang]);
            } else {
                $(v).html('');
            }
        });
    }
};
cv.lang.init();
/**
 * edit table tr
 */
cv.edit = {
    fgDom: '[data-dom-edit="dbclick"]',
    fgInput: '[data-edit-input]',
    fgLabel: '[data-edit-label]',
    fgInputName: '[data-edit-name="col"]',
    fgInputValue: '[data-edit-value="value"]',
    fgIn: '[data-edit-field="input"]',
    fgDataIn: '[data-edit-field]',
    isAjaxProgres: false,
    clickOne: false,
    select2Selected: false,
    messagesValid: {},
    resultValidate: true,
    htmlRow: {
        checkTr: '<i class="fa fa-circle"></i>',
    },
    init: function() {
        var that = this;
        that.submitType();
        $(that.fgDom).each (function (i, v) {
            that.editDom($(v));
        });
        if (!globalVar.isAccess) {
            return true;
        }
        // add optional method to use jquery validate
        that.validatorThis();
        // db click to edit
        $(document).on('dblclick', that.fgDom, function(event) {
            event.preventDefault();
            that.editDom($(this), true);
        });
        // click out to save
        $(document).mouseup(function (event) {
            // timeout to event change language exec before
            setTimeout(function() {
                if (that.clickOne) { // dbclick
                    that.clickOne = false;
                    clearTimeout(that.timer);
                    return true;
                } else {
                    that.timer = setTimeout(function() {
                        that.clickOne = false;
                    }, 200);
                    that.clickOne = true;
                    if (that.select2Selected) {
                        that.select2Selected  = false;
                        return true;
                    }
                }
                // click out editable dbclick
                if (!$(that.fgDom).is(event.target) &&
                    $(that.fgDom).has(event.target).length === 0
                ) {
                    that.viewMode();
                    return true;
                }
                var domTd;
                if ($(event.target).data('dom-edit') === 'dbclick') {
                    domTd = $(event.target);
                } else {
                    domTd = $(event.target).closest(that.fgDom);
                }
                if (!domTd || !domTd.length) {
                    return false;
                }
                // click same editabel but diffrent dom
                if (!domTd.children(that.fgLabel).hasClass('hidden')) {
                    that.viewMode();
                    return true;
                }
            });
        });
        // esc same click out dom
        $(document).keyup(function(e) {
            if (e.keyCode === 27) { // escape key maps to keycode `27`
                that.viewMode();
            }
        });
        // edit field
        $(document).on('change', that.fgDataIn, function () {
            that.inputChange($(this));
        });
        $('[data-row-edit]').each(function (i, v) {
            var typeRowEdit = $(v).data('row-edit');
            that.htmlRow[typeRowEdit] = $(v).children('table').children('tbody').html();
            $(v).remove();
        });
        var newId = 1;
        $('[data-btn-row-add]').click(function (event) {
            event.preventDefault();
            var domThis = $(this),
            type = domThis.data('btn-row-add');
            if (typeof that.htmlRow[type] === 'undefined') {
                return true;
            }
            var newRow = $(that.htmlRow[type].replace(/(\-9999)/g, 'new' + newId));
            newId++;
            $('[data-row-add="' + type + '"]').before(newRow);
            RKExternal.select2.init({
                afterSelected: function () {
                    that.select2Selected = true;
                }
            });
            $('input[data-flag-type="date"]').datetimepicker({
                format: 'YYYY-MM-DD',
                useCurrent: false,
            }).on('dp.change', function (e) {
                that.inputChange($(e.currentTarget));
            });
        });
        $('input[data-flag-type="date"]').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
        }).on('dp.change', function (e) {
            that.inputChange($(e.currentTarget));
        });
        RKExternal.select2.init({
            afterSelected: function () {
                that.select2Selected = true;
            }
        });
        $('body').on('mousedown', '[data-id][data-type]', function (e) {
            if (e.which === 3) { // click right mouse
                window.oncontextmenu = function () {
                    return false; // cancel default menu
                };
                that.deleteRowItem($(this));
                return false;
            }
        });
    },
    /**
     * edit col
     *
     * @param {type} dom
     * @param {type} isEdit
     * @return {Boolean}
     */
    editDom: function(dom, isEdit) {
        var that = this,
        trWrapper = dom.closest('tr[data-id]');
        // editting => no action
        if (dom.children(that.fgLabel).hasClass('hidden')) {
            return true;
        }
        var domLabel = dom.children(that.fgLabel),
        typeInput = domLabel.data('edit-label'),
        inputName = dom.data('edit-name'),
        inputValue = dom.data('edit-value');
        if (typeof inputValue === 'undefined') {
            inputValue = domLabel.text().trim();
        }
        var domInput = dom.children(that.fgInput),
        inputSelect = domInput.find(that.fgIn);
        if (!domInput.length && globalVar.isAccess) {
            switch (typeInput) {
                case 'date':
                    domInput = '<span data-edit-input="text" class="cv-input"><input disabled data-input-disabled="1" class="form-control input-click-edit" name="' 
                        + inputName + '" value="' + inputValue + '" type="text" data-edit-field="input" data-flag-type="date" /></span>';
                    domInput = that.insertInput(dom, domInput);
                    break;
                case 'textarea':
                    domInput = '<span data-edit-input="text" class="cv-input"><textarea disabled data-input-disabled="1" class="form-control input-click-edit" name="' 
                        + inputName + '" data-edit-field="input">'+inputValue+'</textarea></span>';
                    domInput = that.insertInput(dom, domInput);
                    break;
                case 'os': case 'lang': case 'language': case 'database':
                    var multiText = '';
                    if (domLabel.data('edit-multi') && domLabel.data('edit-multi') === 'no') {
                        multiText = '';
                    } else {
                        multiText = ' multiple';
                    }
                    domInput = '<span data-edit-input="selectMulti" class="cv-input">'
                        + '<select disabled data-input-disabled="1" name="' + inputName + '"'+multiText+' data-select2-dom="1" '
                        + 'data-edit-field="input" data-select2-url="' + globalVar['urlRemote' + typeInput] + '"></select></span>';
                    domInput = that.insertInput(dom, domInput);
                    break;
                case 'check-tr':
                    domInput = '<span data-edit-input="check-tr" class="cv-input"><input type="checkbox" disabled data-input-disabled="1" name="'
                        + inputName + '" data-edit-field="input" value="' + inputValue + '" /></span>';
                    domInput = that.insertInput(dom, domInput);
                    break;
                case 'select-lang':
                    var valuesSelect = dom.data('edit-values'),
                        valueCurrent = dom.data('edit-value');
                    domInput = '<span data-edit-input="select-lang" class="cv-input"><select disabled data-input-disabled="1" name="'
                        + inputName + '" data-edit-field="input" data-edit-values="'+valuesSelect+'"'
                        + ' data-edit-value="'+valueCurrent+'"></select></span>';
                    domInput = that.insertInput(dom, domInput);
                    break;
                default: // text
                    domInput = '<span data-edit-input="text" class="cv-input"><input disabled data-input-disabled="1" class="form-control input-click-edit" name="' 
                        + inputName + '" value="' + inputValue + '" type="text" data-edit-field="input" /></span>';
                    domInput = that.insertInput(dom, domInput);
                    break;
            }
            inputSelect = domInput.find(that.fgIn);
            if (domInput.data('edit-input') === 'selectMulti') {
                inputSelect.val('');
                var html = '', projectId = trWrapper.data('id');
                if (domLabel.data('id')) {
                    html = '<option selected value="'+domLabel.data('id')+'">'+inputValue+'</option>';
                } else {
                    if (typeof projTag[projectId] === 'undefined' ||
                        typeof projTag[projectId][typeInput] === 'undefined'
                    ) {
                        html = '';
                    } else {
                        $.each(projTag[projectId][typeInput], function (tagId, tagLabel) {
                            html += '<option selected value="'+tagId+'">'+tagLabel+'</option>';
                        });
                    }
                }
                inputSelect.html(html);
                inputSelect.change();
            }
        }
        if (domInput.data('edit-input') === 'select-lang') {
            that.selectLanguage(inputSelect);
        }
        if (isEdit) {
            inputSelect.prop('disabled', false);
            domLabel.addClass('hidden');
            domInput.removeClass('hidden');
        } else {
            domLabel.removeClass('hidden');
            domInput.addClass('hidden');
        }
        switch (domInput.data('edit-input')) {
            case 'text' :
                inputSelect.val('').focus().val(inputValue);
                break;
            case 'check-tr':
                if (domLabel.html().trim()) {
                    inputSelect.prop('checked', true);
                } else {
                    inputSelect.prop('checked', false);
                }
                break;
            default:
                break;
        }
    },
    /**
     * view mode
     */
    viewMode: function() {
        var that = this;
        that.resultValidate = true;
        $(that.fgDom + ' ' + that.fgLabel + '.hidden').each (function (i, v) {
            var domLabel = $(v),
            domInput = domLabel.siblings(that.fgInput),
            domTr = domInput.closest('tr[data-id][data-type]'),
            inputType = domInput.data('edit-input'),
            input = domInput.find(that.fgIn),
            id = domTr.data(id),
            valueSelect = input.val(),
            inputValidate, typeValidate;
            //validate js
            if (domLabel.data('dom-error') && $(domLabel.data('dom-error')).length) {
                inputValidate = $(domLabel.data('dom-error'));
            } else {
                inputValidate = input;
            }
            typeValidate = inputValidate.closest(that.fgDom)
                    .find(that.fgLabel).data('valid-type');
            if (!that.validate(inputValidate, typeValidate)) {
                that.resultValidate = false;
                return true;
            }
            switch (inputType) {
                case 'selectMulti':
                    var labelText = '',
                    valueProjectTagNew = {};
                    if (valueSelect) {
                        $(input).find('option').each (function(i,v) {
                            if (valueSelect.indexOf($(v).attr('value')) > -1) {
                                labelText += $(v).text() + ', ';
                                valueProjectTagNew[$(v).attr('value')] = $(v).text();
                            }
                        });
                    }
                    labelText = labelText.slice(0, -2);
                    domLabel.text(labelText);
                    if (typeof projTag[id] === 'undefined') {
                        projTag[id] = {};
                    }
                    projTag[id][domLabel.data('edit-label')] = valueProjectTagNew;
                    break;
                case 'check-tr':
                    if (input.is(':checked')) {
                        domTr.find('[data-edit-label="check-tr"]').html('');
                        domTr.find('[data-edit-input="check-tr"]').find(that.fgDataIn).prop('checked', false);
                        domLabel.html(that.htmlRow.checkTr);
                        input.prop('checked', true);
                    } else {
                        domLabel.html('');
                    }
                    break;
                case 'select-lang':
                    var labelText = '',
                    lang = cv.lang.getLangCur();
                    if (valueSelect) {
                        labelText += $(v).text() + ', ';
                    }
                    domLabel.text(trans[lang][valueSelect]);
                    input.data('edit-value', valueSelect);
                    domLabel.data('lang-r', valueSelect);
                    break;
                default: // text
                    domLabel.text(valueSelect);
                    break;
            }
            if (domLabel.data('db-lang')) {
                var nameDb = domLabel.data('db-lang');
                dbTrans[nameDb + '_' + cv.lang.getLangCur()] = valueSelect;
            }
            if (input.data('input-disabled') === '1') {
                input.prop('disabled', true);
            }
            domInput.addClass('hidden');
            domLabel.removeClass('hidden');
        });
        if (that.resultValidate) {
            $('[type="submit"][data-btn-submit]').prop('disabled', false);
        }
//        $(that.fgDom + ':not([is-ajax="1"])').children(that.fgInput).addClass('hidden');
//        $(that.fgDom + ':not([is-ajax="1"])').children(that.fgLabel).removeClass('hidden');
    },
    /**
     * insert input
     *
     * @param {object} dom
     * @param {string} domInput
     */
    insertInput: function(dom, domInput) {
        domInput = $(domInput);
        dom.append(domInput);
        return domInput;
    },
    /**
     * save input - not use
     */
    saveInput: function() {
        var that = this;
        var domInput = $(that.fgDom + ' ' + that.fgInput + ':not(.hidden):first');
        if (!domInput.length) {
            return true;
        }
        var domTr = domInput.closest('tr[data-id][data-type]');
        if (!domTr.length) {
            return true;
            that.viewMode();
        }
        var id = domTr.data('id'),
            type = domTr.data('type');
        if (!id || !type) {
            return true;
            that.viewMode();
        }
        var input = domInput.find(that.fgIn),
            dataSubmit,
            inputType = domInput.data('edit-input');
        if (inputType === 'selectMulti') {
            if (input.val() === null) {
                dataSubmit = encodeURI(input.attr('name')) + '=NULL'; // flag multiselect is null
            } else {
                dataSubmit = input.serialize();
            }
        } else {
            dataSubmit = input.serialize();
        }
        that.isAjaxProgres = true;
        $.ajax({
            url: globalVar.urlSaveCv,
            type: 'POST',
            dataType: 'json',
            data: dataSubmit + '&id=' + id + '&typeSave=' + type + '&_token=' + siteConfigGlobal.token,
            success: function (response) {
                if (typeof response.status === 'undefined' || !response.status) {
                    RKExternal.notify(response.message, false);
                    return true;
                }
                var domLabel = domInput.siblings(that.fgLabel);
                if (inputType === 'selectMulti') {
                    var valueSelect = input.val(),
                    labelText = '',
                    valueProjectTagNew = {};
                    if (valueSelect) {
                        $(input).find('option').each (function(i,v) {
                            if (valueSelect.indexOf($(v).attr('value')) > -1) {
                                labelText += $(v).text() + ', ';
                                valueProjectTagNew[$(v).attr('value')] = $(v).text();
                            }
                        });
                    }
                    labelText = labelText.slice(0, -2);
                    domLabel.text(labelText);
                    projTag[id][domLabel.data('edit-label')] = valueProjectTagNew;
                } else {
                    domLabel.text(input.val());
                }
                if (response.id) {
                    domTr.data('id', response.id);
                }
            },
            error: function () {
                RKExternal.notify('System error', false);
            },
            complete: function () {
                that.viewMode();
                that.isAjaxProgres = false;
                $('[is-ajax]').attr('is-ajax', 0);
            }
        });
    },
    /**
     * render html of select
     *
     * @param {type} selectDom
     * @return {Boolean}
     */
    selectLanguage: function(selectDom) {
        if (!selectDom.data('edit-values')) {
            return true;
        }
        var lang = cv.lang.getLangCur(),
            selectValues = selectDom.data('edit-values').split('|'),
            value = selectDom.data('edit-value'),
            html = '', selected;
        $.each(selectValues, function (i, v) {
            if (v === value) {
                selected = ' selected';
            } else {
                selected = '';
            }
            html += '<option value="'+v+'"'+selected+'>'+trans[lang][v]+'</option>';
        });
        selectDom.html(html);
    },
    /**
     * delete row item: project, skill
     */
    deleteRowItem: function (dom) {
        var id = dom.data('id'),
        type = dom.data('type');
        // remove db
        if (id && type && !isNaN(id) && parseInt(id) > 0) {
            dom.before('<input type="hidden" name="remove['+type+'][]" value="'+id+'" />');
        }
        $('[data-id="'+id+'"][data-type="'+type+'"]').remove();
    },
    /**
     * submit type save | submit | approve
     */
    submitType: function() {
        $('[data-btn-submit]').click(function () {
            var domBtn = $(this),
            type = domBtn.data('btn-submit'),
            domInputType = domBtn.siblings('input[name="save_type"]');
            if (!domInputType.length) {
                domBtn.after('<input type="hidden" name="save_type" value="" />');
                domInputType = domBtn.siblings('input[name="save_type"]');
            }
            domInputType.val(type);
        });
    },
    /**
     * validate manual, use jquery Validate function
     *
     * @param {dom} input
     * @param {string|Json} type
     * @return {Boolean}
     */
    validate: function (input, type) {
        var that = this;
        if (typeof type === undefined || !type) {
            that.inputValidPass(input);
            return true;
        }
        try {
            if (typeof type === 'string') {
                type = JSON.parse(type);
            }
        } catch (e){
            that.inputValidPass(input);
            return true;
        }
        input.data('valid-pass', true);
        var errorDom = input.next('.error');
        if (!errorDom.length) {
            input.after('<label class="error hidden"></label>');
            errorDom = input.next('.error');
        }
        $.each (type, function (key, param) {
            if (!input.data('valid-pass')) {
                that.resultValidate = false;
                that.inputValidFail(input);
                return true;
            }
            if (typeof $.validator.methods[key] !== 'function') {
                that.inputValidPass(input);
                return true;
            }
            if ($.validator.methods[key](input.val(), input[0], param)) {
                that.inputValidPass(input);
                return true;
            }
            that.resultValidate = false;
            that.inputValidFail(input);
            if (typeof that.messagesValid[cv.lang.getLangCur()] === 'undefined'
                || typeof that.messagesValid[cv.lang.getLangCur()][key] === 'undefined'
            ) {
                return true;
            }
            var message = that.messagesValid[cv.lang.getLangCur()][key];
            if (typeof message === "function") {
                message = message.call( this, param);
            }
            errorDom.html(message).removeClass('hidden');
        });
        if (input.data('valid-pass') === false) {
            that.showInputHideLabel(input);
            $('[type="submit"][data-btn-submit]').prop('disabled', true);
            return false;
        }
        errorDom.html('').addClass('hidden');
        that.inputValidPass(input);
        return true;
    },
    /**
     * add method this of validator
     */
    validatorThis: function () {
        var that = this;
        that.messagesValid.en = $.extend({},$.validator.messages);
        that.messagesValid.en['greaterEqualThan'] = trans.en['less start date'];
        that.messagesValid.en['isChecked'] = trans.en['rank checked'];
        $.getScript('https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/localization/messages_ja.min.js', function( data, textStatus, jqxhr ) {
            that.messagesValid.ja = $.validator.messages;
            that.messagesValid.ja['greaterEqualThan'] = trans.ja['less start date'];
            that.messagesValid.ja['isChecked'] = trans.ja['rank checked'];
        });
        $.validator.methods.optional = function (){return false;};
        $.validator.methods.getLength = function (value) {
            return value.length;
        };
        $.validator.methods.depend = function() {
            return true;
        };
        $.validator.methods.checkable = function(element) {
            $.validator.prototype.checkable(element);
        };
        // add validtor method
        $.validator.addMethod("isChecked", function (value, element, params) {
            return $(element).closest('tr[data-id][data-type]')
                .find('input[type="checkbox"]').filter(':checked').length ? true : false;
        }, 'Must checked.');
    },
    /**
     * exec input validate fail
     */
    inputValidFail: function (input) {
        input.data('valid-pass', false);
        input.addClass('error');
    },
    /**
     * exec input validate pass
     */
    inputValidPass: function (input) {
        input.data('valid-pass', true);
        input.removeClass('error');
    },
    /**
     * input change
     *
     * @param {type} inputSelect
     * @return {undefined}
     */
    inputChange: function (inputSelect) {
        var that = this,
            trWrapper = inputSelect.closest('tr');
        if (trWrapper.data('id') && trWrapper.data('type')) {
            $('tr[data-id="'+trWrapper.data('id')+'"]'
                +'[data-type="'+trWrapper.data('type')+'"]').find(that.fgDataIn).data('input-disabled', '0')
                .prop('disabled', false);
        } else {
            inputSelect.data('input-disabled', '0');
        }
    },
    /**
     * show input field, hide label of td editable
     */
    showInputHideLabel: function (input) {
        var that = this;
        var wrapper = input.closest(that.fgDom);
        wrapper.find(that.fgLabel).addClass('hidden');
        wrapper.find(that.fgInput).removeClass('hidden');
    }
};
cv.edit.init();
})(jQuery, RKExternal, document, window);
