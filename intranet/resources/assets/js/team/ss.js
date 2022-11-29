(function($, RKExternal, document, window){
    var globalVar = typeof globalPassModule === 'object' ? globalPassModule : {},
        trans = typeof globalTrans === 'object' ? globalTrans : {},
        dbTrans = typeof globalDbTrans === 'object' ? globalDbTrans : {},
        dataFixSelect = typeof globalDataFixSelect === 'object' ? globalDataFixSelect : {},
        tagsData = typeof globalTagData === 'object' ? globalTagData : {},
        valueTrans = typeof globalValueTrans === 'object' ? globalValueTrans : {},
        validMess = typeof globalValidMess === 'object' ? globalValidMess : {};
    var cv = {};

// declare dom
    var $inputCvLang = $('[name="cv_view_lang"]'),
        $btnSubmit = $('[data-btn-submit]');

    /**
     * change language
     */
    cv.lang = {
        cookieKey: 'cv_lang',
        init: function() {
            var that = this,
                langCookie = $.cookie(that.cookieKey) === 'en' ? 'en' : 'ja';
            $inputCvLang.filter('[value="'+langCookie+'"]').prop('checked', true)
                .parent().trigger('click');
            that.exec(false);
            $inputCvLang.change(function () {
                that.exec(true);
                cv.edit.calProjPeriodAll();
            });
        },
        /**
         * get language choose current
         *
         * @return {String}
         */
        getLangCur: function() {
            return $inputCvLang.filter(':checked').val() === 'en' ? 'en' : 'ja';
        },
        exec: function(isClickChange) {
            var that = this,
                lang = that.getLangCur();
            $.cookie(that.cookieKey, lang, { expires: 7 });
            $('[data-lang-show]:not([data-lang-show="'+lang+'"])').addClass('hidden');
            $('[data-lang-show="'+lang+'"]').removeClass('hidden');
            $('[data-lang-readonly]:not([data-lang-readonly="'+lang+'"])').removeAttr('readonly');
            $('[data-lang-readonly="'+lang+'"]').attr('readonly', 'readonly');
            $('[data-lang-r]').each(function (i, item) {
                var word = $(item).data('lang-r');
                if (typeof trans[lang][word] !== 'undefined') {
                    $(item).html(trans[lang][word]);
                } else {
                    $(item).html('');
                }
            });
            $('[data-lang-placeholder]').each(function (i, item) {
                var word = $(item).data('lang-placeholder');
                if (typeof trans[lang][word] !== 'undefined') {
                    $(item).attr('placeholder', trans[lang][word]);
                } else {
                    $(item).attr('placeholder', '');
                }
            });
            $('[data-input-cv].error').removeClass('error');
            $('[data-fg-dom="mes-error"].error').html('').removeClass('error');
            $('[data-db-lang]').each(function (i, v) {
                that.viewFromDb($(v), 'db-lang', 'val');
            });
            $('[data-db-lang-view]').each(function (i, v) {
                that.viewFromDb($(v), 'db-lang-view', 'text');
            });
            $('[data-db-select]').each(function (i, v) {
                that.viewFromDb($(v), 'db-select', 'val');
            });
            $('[data-db-select-view]').each(function (i, v) {
                that.viewFromDb($(v), 'db-select-view', 'text');
            });
            //show hide projects
            $('.tbl-proj-exper tbody tr.row-proj').addClass('hidden');
            $('.tbl-proj-exper tbody tr[data-lang="'+ lang +'"]').removeClass('hidden');

            if (isClickChange) {
                cv.edit.renderProjRole();
                cv.edit.renderProjRes();
                cv.edit.viewMode();
                RKExternal.simple.textShort();
                // RKExternal.simple.textHeight();
            }
        },
        /**
         * view text from db
         */
        viewFromDb: function(dom, type, func) {
            var that = this,
                lang = that.getLangCur(),
                name = dom.data(type),
                value;
            if (typeof dbTrans[name + '_' + lang] !== 'undefined') {
                value = dbTrans[name + '_' + lang];
            }
            if (!value) {
                value = '';
            }
            if (name === 'address' && (typeof dbTrans['address_' + lang] === 'undefined' || dbTrans['address_' + lang] === '' )) {
                value = dom.attr('value');
            }
            if (type === 'db-select') {
                value = value.split('-');
            } else if (type === 'db-select-view') {
                value = '';
                $('[data-db-select="'+name+'"]').find('option:selected').each (function () {
                    value += $(this).text() + ', ';
                });
                value = (value.slice(0, -2));
            } else {
                //nothing
            }
            dom[func](value);
        },
    };
    /**
     * edit table tr
     */
    cv.edit = {
        messagesValid: {},
        htmlRow: {
            checkTr: '<i class="fa fa-circle"></i>',
            iconLoading: '<i class="fa fa-spin fa-refresh form-control-feedback'
                + ' margin-right-20 hidden" d-dom-loading></i>',
            overlayDom: '<div class="overlay-dom"></div>',
        },
        form: $('#form-employee-cv'),
        deleteData: {},
        newId: 1,
        inited: false,
        init: function() {
            var that = this;
            that.setHtmlRowAdd();
            that.activeTab();
            that.calProjPeriodAll();
            that.renderProjRole();
            that.renderProjRes();
            that.actionInitPage();
            if (!globalVar.isAccess && !globalVar.isAccessTeamEdit) {
                that.viewMode();
                return true;
            }
            that.reloadPlugin();
            /**
             * event add item
             */
            that.validatorInit();
            that.submit();
            that.actionAdd();
            that.actionEdit();
            that.actionDelete();
            that.actionAutoAdd();
            that.inputChange();
            that.actionGeneral();
            that.changeApprover();
            that.inited = true;
        },

        /**
         * render html project role
         */
        renderProjRole: function () {
            var langCur = cv.lang.getLangCur();
            $('.role-select').map(function (i, el) {
                var $this = $(el),
                    projId = $this.closest('tr[data-id][data-type="proj"]').data('id'),
                    roleId = valueTrans.role[projId] ? valueTrans.role[projId].id : null;
                $this.parent().siblings('span').text(roleId ? dataFixSelect.role[langCur][roleId] : '');
            });
        },

        /**
         * render html project response for
         *
         * @returns {undefined}
         */
        renderProjRes: function () {
            var that = this,
                langCur = cv.lang.getLangCur();
            $('ul[data-dom-tagui="res"]').each(function (i, v) {
                var domTagUl = $(this),
                    trWrapper = domTagUl.closest('tr[data-id][data-type="proj"]'),
                    projId = trWrapper.data('id');
                domTagUl.html('');
                if (domTagUl.data("ui-tagit")) {
                    domTagUl.data("ui-tagit").destroy();
                }
                if (!projId || isNaN(projId) || !valueTrans.res[projId] || typeof dataFixSelect.res[langCur] !== 'object') {
                    return true;
                }
                var html = '', input = '';
                if (trWrapper.find('[data-input-cv="1"]').length) {
                    input = ' <input type="hidden" value="{val}" name="'+(domTagUl.attr('name'))+'" data-input-cv="1" /> ';
                }
                $.each(valueTrans.res[projId], function (m, item) {
                    if (item.lang !== langCur) {
                        return true;
                    }
                    var tagLabel;
                    if (item.id) {
                        if (dataFixSelect.res[langCur][item.id]) {
                            tagLabel = dataFixSelect.res[langCur][item.id];
                            input = input.replace(/\{val\}/gi, item.id);
                        }
                    } else if (item.text) {
                        tagLabel = item.text;
                        input = input.replace(/\{val\}/gi, tagLabel);
                    } else {
                        // nothing
                    }
                    if (tagLabel) {
                        html += '<li>'+tagLabel + input + '</li>';
                    }
                });
                domTagUl.html(html);
            });
        },
        /**
         * action itit cv, click, ...
         */
        actionGeneral: function () {
            $(document).on('click', 'td.editable', function () {
                var dom = $(this),
                    domTag = dom.find('[data-dom-tagui]');
                if (!domTag.length ||
                    !domTag.data("ui-tagit") ||
                    !domTag.data("ui-tagit").tagInput.is(':visible') ||
                    domTag.data("ui-tagit").tagInput.is(':focus')
                ) {
                    return true;
                }
                domTag.data("ui-tagit").tagInput.focus();
            });
        },
        /**
         * add method this of validator
         */
        validatorInit: function () {
            var that = this;
            that.messagesValid.en = $.extend({},$.validator.messages, trans.more.en);
            /*that.messagesValid.en = $.extend({},$.validator.messages);
            that.messagesValid.en['greaterEqualThan'] = trans.en['less start date'];
            that.messagesValid.en['isChecked'] = trans.en['rank checked'];
            $.getScript('https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/localization/messages_ja.min.js', function( data, textStatus, jqxhr ) {
                that.messagesValid.ja = $.validator.messages;
                that.messagesValid.ja['greaterEqualThan'] = trans.ja['less start date'];
                that.messagesValid.ja['isChecked'] = trans.ja['rank checked'];
            });*/
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
         * subit form
         */
        submit: function () {
            var that = this;
            that.form.submit(function(e) {
                e.preventDefault();
            });
            $btnSubmit.click(function () {
                if (that.form.data('running')) {
                    return true;
                }
                var inputError = that.form.find('[data-input-cv].error:first');
                if (inputError.length) {
                    $('[data-toggle="tab"][href="#'+inputError.closest('.tab-pane').attr('id')+'"]').trigger('click');
                    $('html, body').animate({scrollTop: inputError.offset().top - 20});
                    return true;
                }
                var domBtn = $(this),
                    type = domBtn.data('btn-submit'),
                    formData = '';
                $btnSubmit.prop('disabled', true);
                that.form.data('running', true);
                formData += '_token=' + siteConfigGlobal.token + '&save_type=' + type;
                $('.overlay-par').removeClass('hidden');
                if (type == 2) {
                    var buttonSave = $('button[data-btn-action=save]');
                    var str = '';
                    $.each(buttonSave, function( index, value ) {
                        str = $(value).attr('class');
                        if (str.search('hidden') < 0) {
                            var wrapper = $(this).closest('tr[data-id][data-type]');
                            if (!wrapper.length) {
                                return true;
                            }
                            that.domInputChanged(wrapper, 2);
                        }
                    });
                }
                $.ajax({
                    url: that.form.attr('action'),
                    type: 'POST',
                    dataType: 'json',
                    data: formData,
                    success: function success(response) {
                        if (typeof response.reload !== 'undefined' && ''+response.reload === '1') {
                            $(window).unbind("beforeunload");
                            window.location.reload();
                            return false;
                        }
                        $('.overlay-par').addClass('hidden');
                        //error
                        if (typeof response.status === 'undefined' || !response.status) {
                            RKExternal.notify(response.message, false);
                            return true;
                        }
                    },
                    error: function error(response) {
                        if (typeof response === 'object' &&
                            typeof response.responseJSON === 'object' &&
                            response.responseJSON.message
                        ) {
                            RKExternal.notify(response.responseJSON.message, false);
                        } else {
                            RKExternal.notify('System error', false);
                        }
                        $('.overlay-par').addClass('hidden');
                    },
                    complete: function complete(response) {
                        if ((typeof response.reload !== 'undefined' && ''+response.reload === '1') ||
                            (typeof response.redirect !== 'undefined' && response.redirect) ||
                            (typeof response.responseJSON === 'object' && response.responseJSON.reload)
                        ) {
                            return false;
                        }
                        that.form.data('running', false);
                        $btnSubmit.prop('disabled', false);
                        $('.overlay-par').addClass('hidden');
                    },
                });
            });
        },
        /**
         * after change value of editable
         *
         * @param {type} dom
         * @param {type} newValue
         * @return {undefined}
         */
        inputChange: function () {
            var that = this;
            $(document).on('change', '[data-input-cv]', function () {
                that.domInputChanged($(this));
            });
            // cal old
            var domOld = $('[data-fg-dom="old"]');
            $('[name="employee[birthday]"]').datepicker().on('changeDate', function (ev) {
                if (!ev.date) {
                    domOld.text('0');
                    return true;
                }
                var diff = RKExternal.simple.diffTimeYM(moment(ev.date));
                domOld.text(diff['Y']);
            });
            that.calProjPeriod();
        },
        /**
         * cal change period of project
         *
         * @param {object dom} domTr
         */
        calProjPeriod: function(domTr) {
            var that = this, input;
            if (domTr) {
                input = domTr.find('[data-fg-dom="proj-date-start"], [data-fg-dom="proj-date-end"]');
            } else {
                input = $('[data-fg-dom="proj-date-start"], [data-fg-dom="proj-date-end"]');
            }
            // cal proj time period
            input.on('change', function (ev) {
                var domWrapper = $(ev.currentTarget).closest('tr[data-id][data-type]');
                that.calProjPeriodItem(domWrapper);
            });
            // input.on('change', function () {
            //     var $this = $(this);
            //     if (!$this.val()) {
            //         var domWrapper = $this.closest('tr[data-id][data-type]');
            //         that.calProjPeriodItem(domWrapper);
            //     }
            // });
        },
        /**
         * cl project period - all tr
         *
         * @param {object dom} domWrapper
         */
        calProjPeriodAll: function () {
            var that = this,
                langCur = cv.lang.getLangCur();
            $('tr[data-id][data-type="proj"]').each(function (i, v) {
                that.calProjPeriodItem($(v), langCur);
            });
        },
        /**
         * cl project period item - each tr
         *
         * @param {object dom} domWrapper
         * @returns {Boolean}
         */
        calProjPeriodItem: function (domWrapper, langCur) {
            var that = this,
                domPeriodY = domWrapper.find('[data-fg-dom="proj-period-y"]'),
                domPeriodM = domWrapper.find('[data-fg-dom="proj-period-m"]'),
                timeS = domWrapper.find('[data-fg-dom="proj-date-start"]').val(),
                timeE = domWrapper.find('[data-fg-dom="proj-date-end"]').val();
            if (!langCur) {
                langCur = cv.lang.getLangCur();
            }
            if (!timeS || !timeE) {
                that.calProjPeriodItemLabel(domWrapper, langCur, 0, 0);
                domPeriodY.text(0);
                domPeriodM.text(0);
                return true;
            }
            var diff = RKExternal.simple.diffTimeYM(moment(timeS), moment(timeE).add(1, 'M'));
            domPeriodY.text(diff['Y']);
            domPeriodM.text(diff['M']);
            that.calProjPeriodItemLabel(domWrapper, langCur, diff['Y'], diff['M']);
        },
        /**
         * change label period for language en: year(s)
         *
         * @param {obj} domWrapper
         * @param {string} langCur
         * @param {int} y
         * @param {int} m
         * @returns {Boolean}
         */
        calProjPeriodItemLabel: function (domWrapper, langCur, y, m) {
            if (langCur !== 'en') {
                return true;
            }
            var domY = domWrapper.find('[d-dom-proj="period_y_label"]'),
                textY = domY.text(),
                domM = domWrapper.find('[d-dom-proj="period_m_label"]'),
                textM = domM.text();
            textY = textY.replace(/s$/, '');
            textM = textM.replace(/s$/, '');
            if (y > 1 || y === 0) {
                textY += 's';
            }
            if (m > 1 || m === 0) {
                textM += 's';
            }
            domY.text(textY);
            domM.text(textM);
        },
        /**
         * dom input changed
         *  use for save basic info, save a row project, skill
         *
         * @param {type} dom
         * @return {undefined}
         */
        domInputChanged: function (dom, typeSaveData) {
            var that = this,
                col = dom.data('db-lang'),
                trWrapper = dom.closest('tr[data-id][data-type]'),
                newValue = dom.val(),
                domEffect;

            //check if null then set empty string for serialize.
            if (dom.val() === null) {
                dom.val('');
            } else if (Array.isArray(dom.val())) {
                var oldVal = dom.val();
                for (var i = 0; i < oldVal.length; i++) {
                    if (!oldVal[i]) {
                        oldVal.splice(i, 1);
                    }
                }
                dom.val(oldVal);
                var elSelect2 = dom.parent().find('.select2-container');
                if (elSelect2.length > 0) {
                    elSelect2.find('.select2-selection__rendered li').each(function () {
                        var title = $(this).attr('title');
                        if (typeof title != 'undefined' && title.trim() == '') {
                            $(this).remove();
                        }
                    });
                }
            }

            if (typeof typeSaveData === 'undefined' || !typeSaveData) {
                typeSaveData = 1; // default save single input
            } // save project, skill = save row, typeSaveData = 2
            if (!that.validate(dom, newValue)) {
                return false;
            }
            if (typeSaveData === 2) {
                trWrapper = dom;
                if (!that.validateMulti(trWrapper)
                    || (trWrapper.data('type') === 'proj' && !that.validateMulti(trWrapper.next()))) {
                    return false;
                }
            }
            if (trWrapper.length && typeSaveData === 1) {// change input project, skill
                return true;
            }
            if (!col) {
                col = dom.data('db-select');
            }
            if (Array.isArray(newValue)) {
                newValue = newValue.join('-');
            }
            var formData = '',
                inputData = '',
                removeData = {
                    remove: that.deleteData
                };
            if (typeSaveData === 2) {
                domEffect = dom.closest('tr[data-id][data-type]');
                inputData = trWrapper.find('[data-input-cv]').serialize();
                if (trWrapper.data('type') === 'proj') {
                    var $roleSelect = trWrapper.find('.role-select'),
                        roleId = $roleSelect.val();
                    inputData += '&' + $roleSelect.attr('name') + '=' + roleId;
                    inputData += '&' + trWrapper.next().find('[data-input-cv]').serialize();
                }
            } else {
                domEffect = dom;
                inputData = dom.serialize();
            }
            formData += '_token=' + siteConfigGlobal.token
                + '&' + $('[name="cv_view_lang"]').serialize()
                + '&' + $.param(removeData)
                + '&' + inputData;
            if (domEffect.data('running')) {
                return true;
            }
            domEffect.data('running', true);
            if (!trWrapper.length) { // save base
                if (!dom.nextAll('[d-dom-loading]').length) {
                    dom.after(that.htmlRow.iconLoading);
                }
                if (!dom.nextAll('.overlay-dom').length) {
                    dom.after(that.htmlRow.overlayDom);
                }
                dom.nextAll('[d-dom-loading]').removeClass('hidden');
                dom.nextAll('.overlay-dom').removeClass('hidden');
            } else {
                that.showHideBtn(domEffect, 'load');
                domEffect.find('.overlay-dom').removeClass('hidden');
                domEffect.find('.overlay-dom').parent().attr('style', 'position: unset;');
            }
            $.ajax({
                url: that.form.attr('action'),
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function success(response) {
                    //error
                    if (typeof response.status === 'undefined' || !response.status) {
                        RKExternal.notify(response.message, false);
                        return true;
                    }
                    if (typeof response.message === 'string' && response.message) {
                        RKExternal.notify(response.message);
                    }
                    that.changeSaveType(response.save);
                    // change value of db lang - switch lang
                    if (col) {
                        dbTrans[col + '_' + cv.lang.getLangCur()] = newValue;
                    }
                    that.showHideBtn(domEffect, 'view');
                    if (typeSaveData === 2 && response.item_id) { // save multirow
                        var newId = domEffect.data('id'),
                            dataType = domEffect.data('type'),
                            isProj = dataType === 'proj',
                            domEffectNext;
                        that.viewMode(domEffect);
                        if (isProj) {
                            domEffectNext = domEffect.next();
                            that.viewMode(domEffectNext);
                        }
                        if (isNaN(newId)) { //replace new id
                            // update input
                            domEffect.find('.tagit').each(function (i, v) {
                                if (typeof $(v).data("ui-tagit") === 'object') {
                                    $(v).data("ui-tagit").destroy();
                                }
                            });
                            domEffect.find('select').each(function (i, v) {
                                if (typeof $(v).data('select2') === 'object') {
                                    $(v).data('select2').destroy();
                                }
                            });
                            var arrayVal = {};
                            domEffect.find('input:not([type="radio"]), select, textarea').each(function (i, v) {
                                if (typeof $(v).attr('name') !== 'undefined') {
                                    arrayVal[$(v).attr('name')] = $(v).val();
                                }
                            });
                            if (isProj) {
                                domEffectNext.find('input[type="number"], textarea').each(function (i, v) {
                                    if (typeof $(v).attr('name') !== 'undefined') {
                                        arrayVal[$(v).attr('name')] = $(v).val();
                                    }
                                });
                            }
                            domEffect.find('input[type="radio"], input[type="checkbox"]').each(function (i, v) {
                                if ($(v).is(':checked')) {
                                    $(v).attr('checked', 'checked');
                                }
                            });
                            var orgHtml = domEffect[0].outerHTML,
                                orgHtmlNext = isProj ? domEffectNext[0].outerHTML : '',
                                newreg = new RegExp('('+newId+')', 'gi');
                            domEffect.replaceWith(orgHtml.replace(newreg, response.item_id));
                            isProj && domEffectNext.replaceWith(orgHtmlNext.replace(newreg, response.item_id));
                            var aryDomEffect = $('[data-id="'+response.item_id+'"][data-type="'+dataType+'"]');
                            domEffect = aryDomEffect.first();
                            domEffectNext = isProj ? aryDomEffect.last() : null;
                            domEffect.find('input:not([type="radio"]), select, textarea').each(function (i, v) {
                                var nameInput = $(v).attr('name');
                                if (!nameInput) {
                                    return true;
                                }
                                newreg = new RegExp('('+response.item_id+')', 'gi');
                                nameInput = nameInput.replace(newreg, newId);
                                if (typeof arrayVal[nameInput] !== 'undefined') {
                                    $(v).val(arrayVal[nameInput]);
                                }
                            });
                            if (isProj) {
                                domEffectNext.find('input[type="number"], textarea').each(function (i, v) {
                                    var nameInput = $(v).attr('name');
                                    if (!nameInput) {
                                        return true;
                                    }
                                    newreg = new RegExp('(' + response.item_id + ')', 'gi');
                                    nameInput = nameInput.replace(newreg, newId);
                                    if (typeof arrayVal[nameInput] !== 'undefined') {
                                        $(v).val(arrayVal[nameInput]);
                                    }
                                });
                            }
                            that.reloadPlugin();
                            //cv.lang.exec(false);
                            that.calProjPeriod(domEffect);
                        }
                        // update dbtrans multi row
                        that.updateValueMultirow(domEffect, response.item_id);
                        var totalMember, totalMM, start, end;
                        if (isProj) {
                            // get same value
                            start = domEffect.find('input[data-fg-dom="proj-date-start"]').val();
                            end = domEffect.find('input[data-fg-dom="proj-date-end"]').val();
                            totalMember = domEffectNext.find('input[data-dom-input="total_member"]').val();
                            totalMM = domEffectNext.find('input[data-dom-input="total_mm"]').val();
                            // update project row 2
                            that.updateValueMultirow(domEffectNext, 0);
                            // update view role
                            valueTrans.role[response.item_id] = {id: roleId};
                            valueTrans.role[response.projnewId] = {id: roleId};
                            domEffect.find('[d-dom-proj="role"]').text(dataFixSelect.role[cv.lang.getLangCur()][roleId]);
                        }
                        var repon = '';
                        if (typeof response.res != 'undefined') {
                            for (var i = 0; i < response.res.length; i++) {
                                var id = response.res[i];
                                if (repon === '') {
                                    repon = '{"id":"' + id + '","text":null,"lang":"' + response.lang + '"}';
                                } else if (Number.isInteger(id)) {
                                    repon = repon + ', {"id":"' + id + '","text":null,"lang":"' + response.lang + '"}';
                                } else {
                                    repon = repon + ', {"id":null,"text":"'+ id +'","lang":"' + response.lang + '"}';
                                }
                            }
                        }
                        var sript = '<script> globalValueTrans.res[' + response.projnewId + '] = [' + repon +']; </script>';

                        if (response.action == 'create') {
                            var projNumber = domEffect.find('[d-dom-proj="proj_number"]').text();
                            var string = 'proj_' + response.projnewId + '_number_' + response.lang;
                            dbTrans[string] = projNumber;

                            if (typeof response.projnewId != 'undefined') {
                                var cloneEffect = domEffect.clone(),
                                    cloneEffectNext = domEffect.next().clone();
                                cloneEffect.attr({
                                    'data-lang': response.lang,
                                    'data-id': response.projnewId,
                                    'data-input-cv': '',
                                }).addClass("hidden");
                                cloneEffect.find("td:last-child").removeAttr('style');
                                cloneEffect.find("td:last-child").find('div').addClass('hidden');
                                cloneEffect.find('[data-fg-dom="proj-date-start"]').attr('value', start);
                                cloneEffect.find('[data-fg-dom="proj-date-end"]').attr('value', end);
                                cloneEffect.find("[data-view-type='tagit']").append(sript);

                                cloneEffectNext.attr({
                                    'data-lang': response.lang,
                                    'data-id': response.projnewId,
                                    'data-input-cv': '',
                                }).addClass("hidden");
                                cloneEffectNext.find('input[data-dom-input="total_member"]').attr('value', totalMember);
                                cloneEffectNext.find('input[data-dom-input="total_mm"]').attr('value', totalMM);

                                var re = new RegExp('\\[' + response.item_id + '\\]', "gi");
                                var re_ = new RegExp('_' + response.item_id + '_', "gi");
                                var html = cloneEffect[0].outerHTML.replace(re, '[' + response.projnewId + ']');
                                html = html.replace(re_, '_' + response.projnewId + '_');
                                html += cloneEffectNext[0].outerHTML.replace(re, '[' + response.projnewId + ']');
                                $("tr[data-id][data-type=proj]:last").after(html);
                            }
                        } else {
                            if (dataType !== 'proj') {
                                return;
                            }
                            var cloneEffect = domEffect.clone(),
                                lang = cloneEffect.find('[d-dom-proj="lang"]').html(),
                                frame = cloneEffect.find('[d-dom-proj="other"]').html();
                            cloneEffect.find(".tagit-close").remove();
                            $('table tbody [data-id="' + response.projnewId + '"] [data-dom-tagui="language"]').html(getStringli(lang));
                            $('table tbody [data-id="' + response.projnewId + '"] [data-dom-tagui="dev_env"]').html(getStringli(frame));
                            $('table tbody [data-id="' + response.projnewId + '"] [data-view-type="tagit"]').append(sript);

                            var $aryOtherProject = $('tr[data-type="proj"][data-id="' + response.projnewId + '"]'),
                                $otherProject = $aryOtherProject.first(),
                                $otherProjectNext = $aryOtherProject.last();
                            // update start/end
                            $otherProject.find('[data-fg-dom="proj-date-start"]').val(start);
                            $otherProject.find('[data-fg-dom="proj-date-end"]').val(end);
                            // update input total member & total mm
                            $otherProjectNext.find('input[data-dom-input="total_member"]').val(totalMember);
                            $otherProjectNext.find('input[data-dom-input="total_mm"]').val(totalMM);
                        }
                    }
                },
                error: function error(response) {
                    if (typeof response === 'object' &&
                        typeof response.responseJSON === 'object' &&
                        response.responseJSON.message
                    ) {
                        RKExternal.notify(response.responseJSON.message, false);
                    } else {
                        RKExternal.notify('System error', false);
                    }
                    that.showHideBtn(domEffect, 'edit');
                },
                complete: function complete(response) {
                    if ((typeof response.reload !== 'undefined' && ''+response.reload === '1') ||
                        (typeof response.redirect !== 'undefined' && response.redirect) ||
                        (typeof response.responseJSON === 'object' && response.responseJSON.reload)
                    ) {
                        return false;
                    }
                    if (!trWrapper.length) { // save base
                        dom.nextAll('[d-dom-loading]').addClass('hidden');
                        dom.nextAll('.overlay-dom').addClass('hidden');
                    } else {
                        domEffect.find('.overlay-dom').addClass('hidden');
                        domEffect.find('.overlay-dom').parent().removeAttr('style');
                    }
                    domEffect.data('running', false);
                },
            });
            if (trWrapper.length) {
                trWrapper.find('[data-input-cv]').attr('data-input-cv', 1);
            }
            dom.attr('data-input-cv', 1);
        },
        changeSaveType: function (saveType) {
            if (saveType != 1) {
                return true;
            }
            $('.steps-ui .current').removeClass('current');
            $btnSubmit.filter('[data-btn-submit="3"]').remove();
            $('[d-dom-fg="box-approve"]').remove();
        },
        /**
         * update value translate for multirow - project, skill
         *
         * @param {dom} domEffect
         * @param {int} itemId
         */
        updateValueMultirow: function (domEffect, itemId) {
            var langCur = cv.lang.getLangCur();
            domEffect.find('[data-db-lang]').each(function () {
                var domLang = $(this),
                    typeDomLang = domLang.data('db-lang');
                if (!typeDomLang) {
                    return true;
                }
                var newValue = domLang.val();
                if (Array.isArray(newValue)) {
                    newValue = newValue.join('-');
                }
                dbTrans[typeDomLang + '_' + langCur] = newValue;
            });
            // project row 2 not update res
            if (itemId === 0) {
                return;
            }
            // delete old value of language
            if (typeof valueTrans.res[itemId] === 'object') {
                var newRes = [];
                $.each (valueTrans.res[itemId], function (i, v) {
                    if (v.lang !== langCur) {
                        newRes.push(v);
                    }
                });
                valueTrans.res[itemId] = newRes;
            } else {
                valueTrans.res[itemId] = [];
            }
            domEffect.find('[data-dom-tagui="res"] li').each(function (i, v) {
                var elV = $(v);
                if (elV.find('span.tagit-label').length > 0) {
                    elV = elV.find('span.tagit-label');
                }
                var newValue = elV.text().trim();
                if (!newValue) {
                    return true;
                }
                if (!isNaN(newValue)) { // new tag from option
                    valueTrans.res[itemId].push({
                        id: newValue,
                        text: null,
                        lang: langCur,
                    });
                }
                valueTrans.res[itemId].push({
                    id: null,
                    text: newValue.replace(/^n-/, ''),
                    lang: langCur,
                });
            });
        },
        /**
         * validate multi input: a row, multi input
         */
        validateMulti: function (dom) {
            var that = this, result = true;
            dom.find('[data-valid-type]').each (function () {
                if (!that.validate($(this), $(this).val())) {
                    result = false;
                }
            });
            return result;
        },
        /**
         * validate input after change
         */
        validate: function (dom, value) {
            var that = this,
                type = dom.data('valid-type');
            var errorDom = dom.parent().parent().find('label.error');
            if (!errorDom.length) {
                dom.parent().parent().append('<label class="error hidden" data-fg-dom="mes-error"></label>');
                errorDom = dom.parent().parent().find('label.error');
            }
            if (!type) {
                errorDom.html('').addClass('hidden');
                dom.removeClass('error');
                return true;
            }
            try {
                if (typeof type === 'string') {
                    type = JSON.parse(type);
                }
            } catch (e){
                errorDom.html('').addClass('hidden');
                dom.removeClass('error');
                return true;
            }
            var message;
            $.each (type, function (key, param) {
                if (typeof $.validator.methods[key] !== 'function') {
                    return true;
                }
                //pass valid
                if (key !== 'required' && value === '') {
                    return true;
                }
                if ($.validator.methods[key](value, dom[0], param)) {
                    return true;
                }
                if (typeof that.messagesValid.en === 'undefined'
                    || typeof that.messagesValid.en[key] === 'undefined'
                ) {
                    message = 'error';
                } else {
                    message = that.messagesValid.en[key];
                }
                if (typeof message === "function") {
                    message = message.call(this, param);
                }
                return false;
            });
            var $td = dom.closest('td');
            if ($td.children('.proj-edit-date').length > 0) {
                $td.css('height', message && $td[0].scrollHeight <= 205 ? '205px' : '');
            }
            if (!message) {
                errorDom.html('').addClass('hidden');
                dom.removeClass('error');
                return true;
            }
            errorDom.html(message).removeClass('hidden');
            dom.addClass('error');
            return false;
        },
        /**
         * set html row add
         */
        setHtmlRowAdd: function () {
            var that = this;
            $('[data-row-edit]').each(function (i, v) {
                var typeRowEdit = $(v).data('row-edit');
                that.htmlRow[typeRowEdit] = $(v).children('table').children('tbody').html();
                $(v).remove();
            });
        },
        /**
         * action add row
         */
        actionAdd: function () {
            var that = this;
            $(document).on('click', '[data-btn-row-add]', function (event) {
                event.preventDefault();
                that.actionAddItem($(this));
                var lang = cv.lang.getLangCur(),
                    className = 'count' + lang,
                    selected = $("." + className),
                    number = selected.length;
                $('.count').last().addClass(className).text(number + 1);
            });
        },
        /**
         * add item new row
         */
        actionAddItem: function (btnDom, isAutoAdd) {
            var that = this,
                type = btnDom.data('btn-row-add'),
                lang = cv.lang.getLangCur();
            if (typeof that.htmlRow[type] === 'undefined' || !that.htmlRow[type]) {
                return true;
            }
            var newRow = $(that.htmlRow[type].replace(/(\-9999)/g, 'new_item_fg_' + that.newId)
                    .replace(/(data-input-cv)/g, 'data-input-cv-flag')),
                tr = btnDom.closest('tr');
            var lastType = tr.data('btn-last'), trNew;
            if (!lastType || ['before', 'after'].indexOf(lastType) === -1) {
                lastType = 'before';
            }
            if (type === 'proj') {
                newRow.removeClass('hidden').attr('data-lang', cv.lang.getLangCur());
            }
            trNew = $('tr[data-id][data-type="'+type+'"]:last');
            if (trNew.length) {
                trNew.after(newRow);
            } else {
                tr[lastType](newRow);
            }
            that.newId++;
            if (typeof isAutoAdd === 'undefined' || isAutoAdd === false) {
                that.editMode(newRow);
                that.reloadPlugin();
                that.calProjPeriod(newRow);
                newRow.find('[data-lang-r]').each(function (i, item) {
                    var word = $(item).data('lang-r'),
                        value = typeof trans[lang][word] !== 'undefined' ? trans[lang][word] : '';
                    $(item).html(value);
                });
                that.calProjPeriodItem(newRow);
            }
            // reload data input cv to not change when add new
            setTimeout(function () {
                newRow.find('[data-input-cv-flag]').attr('data-input-cv', '0')
                    .removeAttr('data-input-cv-flag');
            }, 100);
        },
        /**
         * delete row item: project, skill
         */
        actionDelete: function () {
            var that = this;
            $(document).on('click', '[data-btn-action="delete"]', function () {
                var dom = $(this).closest('[data-id][data-type]'),
                    id = dom.attr('data-id'),
                    type = dom.attr('data-type'),
                    group = dom.attr('data-group'),
                    fgDelete = group ? group : type;
                if (id && type && !isNaN(id) && parseInt(id) > 0) {
                    RKExternal.confirm(validMess.confirm_delete, function(response) {
                        if (response.result) {
                            that.deleteRowItem(dom, id, fgDelete);
                        }
                    }, {
                        btnOkColor: 'btn-danger'
                    });
                } else {
                    dom.remove();
                }
            });
        },
        deleteRowItem: function (dom, id, fgDelete) {
            var that = this;
            dom.data('running', true);
            var formData = '',
                removeData = {
                    remove: {}
                };
            removeData.remove[fgDelete] = [id];
            formData += '_token=' + siteConfigGlobal.token
                + '&' + $('[name="cv_view_lang"]').serialize()
                + '&' + $.param(removeData);
            dom.find('[d-dom-loading="tr"]').removeClass('btn-primary').addClass('btn-danger');
            that.showHideBtn(dom, 'load');
            $.ajax({
                url: that.form.attr('action'),
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function success(response) {
                    //error
                    if (typeof response.status === 'undefined' || !response.status) {
                        RKExternal.notify(response.message, false);
                        return true;
                    }
                    RKExternal.notify(validMess.success_delete);
                    dom.remove();
                    $.isArray(response['delete']) && that.deleteTrTable(response['delete']);
                },
                error: function error(response) {
                    if (typeof response === 'object' &&
                        typeof response.responseJSON === 'object' &&
                        response.responseJSON.message
                    ) {
                        RKExternal.notify(response.responseJSON.message, false);
                    } else {
                        RKExternal.notify('System error', false);
                    }
                    dom.find('[d-dom-loading="tr"]').addClass('btn-primary').removeClass('btn-danger');
                },
                complete: function complete(response) {
                    if ((typeof response.reload !== 'undefined' && ''+response.reload === '1') ||
                        (typeof response.redirect !== 'undefined' && response.redirect) ||
                        (typeof response.responseJSON === 'object' && response.responseJSON.reload)
                    ) {
                        return false;
                    }
                    dom.data('running', false);
                    that.showHideBtn(dom, 'view');
                },
            });
        },
        deleteTrTable: function (ids) {
            for (var i = ids.length - 1; i >= 0; i--) {
                $('table tr[data-id="' + ids[i] +'"]').remove();
            }
        },
        /**
         * reload when add row - datetimepicker, select2
         */
        reloadPlugin: function () {
            var that = this;
            $('tr[data-type="proj"] input[data-flag-type="date"]').datepicker({
                format: 'yyyy-mm',
                viewMode: 'months',
                minViewMode: 'months',
            });
            $('input[name = "employee[birthday]"]').datepicker({
                format: 'yyyy-mm-dd',
                useCurrent: false,
                todayHighlight: true,
                weekStart: 1,
                autoclose: true,
                clearBtn: true,
            });
            $('input[data-flag-type="date"]').on('change', function(e) {
                that.domInputChanged($(this));
            });
            that.reloadSelect2();
        },
        reloadSelect2: function () {
            RKExternal.select2.init({
                templateResult: function(response) {
                    if (response.loading) {
                        return response.text;
                    }
                    if (response.element && parseInt(response.id) === -1) {
                        return null;
                    }
                    return response.text;
                }
            });
        },
        /**
         * load init tag it plugin
         *
         * @param {type} domWrapper
         * @returns {undefined}
         */
        tagitPlugin: function (domWrapper) {
            var that = this,
                domIt;
            if (domWrapper && domWrapper.length) {
                domIt = domWrapper.find('[data-dom-tagui]');
            } else {
                domIt = $('[data-dom-tagui]');
            }
            domIt.each(function (i, v) {
                var dom = $(v),
                    typeTag = dom.data('dom-tagui'),
                    inputName = dom.attr('name');
                if (dom.data('ui-tagit')) {
                    return true;
                }
                dom.tagit({
                    itemName: 'item',
                    fieldName: 'tag',
                    allowSpaces: true,
                    caseSensitive: false,
                    showAutocompleteOnFocus: true,
                    tagSource: function (req, res) {
                        if (typeTag === 'res') {
                            var lang = cv.lang.getLangCur(),
                                dataOptions = dataFixSelect['res'][lang];
                        } else {
                            dataOptions = tagsData[typeTag];
                        }
                        if (!dataOptions) {
                            res([]);
                            return true;
                        }
                        var filter = req.term.toLowerCase(),
                            search = [];
                        $.each(dataOptions, function (tagId, tagName) {
                            if (tagName.substr(0, req.term.length).toLowerCase() === filter) {
                                search.push(tagName);
                            }
                        });
                        res(search);
                    },
                    beforeTagAdded: function (event, ui) {
                        //add class for tag
                        if (typeTag === 'res') {
                            var lang = cv.lang.getLangCur(),
                                dataOptions = dataFixSelect['res'][lang];
                        } else {
                            dataOptions = tagsData[typeTag];
                        }
                        var id = that.findTagByLabel(dataOptions, ui.tagLabel),
                            input = ui.tag.find('input[type="hidden"][name="tag"]'),
                            val, attrInputCv = 'data-input-cv', valAttrInputCv = 0;
                        if (id) {
                            val = id;
                        } else {
                            val = 'n-' + input.val();
                        }
                        // not init, after load page
                        if (!ui.duringInitialization) {
                            valAttrInputCv = 1;
                            $('input[name="'+inputName+'"]').attr(attrInputCv, valAttrInputCv);
                            $(event.target).closest('tr[data-type="proj"]')
                                .find('['+attrInputCv+']').attr(attrInputCv, valAttrInputCv);
                        } else {
                            if ($(event.target).closest('tr[data-type="proj"]').find('[data-input-cv="1"]').length) {
                                valAttrInputCv = 1;
                            }
                        }
                        if (ui.tag.find('['+attrInputCv+']').length) {
                            ui.tag.find('['+attrInputCv+']').attr(attrInputCv, valAttrInputCv);
                        } else {
                            var inputHtml = '<input type="hidden" value="' + val
                                + '" name="' + inputName + '" ' + attrInputCv + '="'+valAttrInputCv+'" />';
                            ui.tag.append(inputHtml);
                        }
                        return true;
                    },
                    beforeTagRemoved: function (event) {
                        $(event.target).closest('tr[data-type="proj"]')
                            .find('[data-input-cv]').attr('data-input-cv', 1);
                    },
                });
            });
        },
        /**
         * find key by value
         *
         * @param {obj} tagsData
         * @param {string} label
         * @returns {int}
         */
        findTagByLabel: function (tagsData, label) {
            var result = null;
            if (!tagsData) {
                return result;
            }
            $.each(tagsData, function (tagId, tagName) {
                if (tagName.toLowerCase() === label.toLowerCase()) {
                    result = tagId;
                    return false;
                }
            });
            return result;
        },
        showHideBtn: function (dom, type) {
            var editMode, viewMode, loadMode;
            switch (type) {
                case 'edit':
                    editMode = 'addClass';
                    viewMode = 'removeClass';
                    loadMode = 'addClass';
                    break;
                case 'load':
                    editMode = 'addClass';
                    viewMode = 'addClass';
                    loadMode = 'removeClass';
                    break;
                default: // 'view'
                    editMode = 'removeClass';
                    viewMode = 'addClass';
                    loadMode = 'addClass';
                    break;
            }
            dom.find('[d-dom-loading="tr"]')[loadMode]('hidden');
            dom.find('[data-btn-action="save"]')[viewMode]('hidden');
            dom.find('[data-btn-action="cancel"]')[viewMode]('hidden');
            dom.find('[data-btn-action="edit"]')[editMode]('hidden');
            dom.find('[data-btn-action="delete"]')[editMode]('hidden');
        },
        /**
         * view Mode
         */
        viewMode: function (dom) {
            var that = this,
                domView,
                langCur = cv.lang.getLangCur();
            if (typeof dom === 'undefined' || !dom) {
                dom = $('body');
            }
            that.showHideBtn(dom, 'view');
            dom.find('[data-mode-dom="view"]').each(function () {
                domView = $(this);
                var domEdit = domView.nextAll('[data-mode-dom="edit"]:first'),
                    domInput = domEdit.children('[data-input-cv]'),
                    val = domInput.val(),
                    trWrapper = domView.closest('tr');
                domEdit.addClass('hidden');
                domView.removeClass('hidden');
                trWrapper.removeClass('editting');
                if (domInput.is('select')) {
                    var valText = '';
                    domInput.find('option:selected').each (function () {
                        valText += $(this).text() + ', ';
                    });
                    domView.text(valText.slice(0, -2));
                    return true;
                }
                if (domInput.is('input[type="radio"]')) {
                    var typeHtmlVal = domView.data('html-val');
                    if (domInput.is(':checked')) {
                        if (that.htmlRow[typeHtmlVal]) {
                            domView.html(that.htmlRow[typeHtmlVal]);
                        } else {
                            domView.text(val);
                        }
                    } else {
                        domView.text('');
                    }
                    return true;
                }
                if (domEdit.data('view-type') === 'tagit') {
                    var valText = '',
                        domViewMode = domEdit.find('ul.tagit li.tagit-choice .tagit-label');
                    if (!domViewMode.length) {
                        domViewMode = domEdit.find('ul > li');
                    }
                    domViewMode.each (function () {
                        valText += htmlEntities($(this).text().trim()) + '<br>';
                    });
                    domView.html(valText.slice(0, -4));
                    return true;
                }
                // show total member
                if (domInput.data('dom-input') === 'total_member') {
                    var txtMember = (parseInt(val) <= 1 && langCur === 'en') ? trans[langCur].member : trans[langCur].members;
                    domView.text(val ? trans[langCur].team + ': ' + val + ' ' + txtMember : '');
                    return true;
                }
                // show total MM
                if (domInput.data('dom-input') === 'total_mm') {
                    domView.text(val ? trans[langCur].total + ': ' + val + ' ' + trans[langCur].MM : '');
                    return true;
                }
                domView.text(val);
            });
            dom.find('[data-show-mode="view"]').removeClass('hidden');
            dom.removeClass('editting');
            RKExternal.simple.textShort(dom);
            // if (that.inited) {
            //     RKExternal.simple.textHeight(dom);
            // }
        },
        /**
         * action edit
         */
        actionEdit: function () {
            var that = this;
            $(document).on('click', '[data-btn-action="edit"]', function () {
                var wrapper = $(this).closest('tr[data-id][data-type]');
                if (!wrapper.length) {
                    return true;
                }
                that.editMode(wrapper);
                wrapper.data('type') === 'proj' && that.editMode(wrapper.next()); // edit project row 2
            });
            // save single project / skill
            $(document).on('click', '[data-btn-action="save"]', function () {
                var wrapper = $(this).closest('tr[data-id][data-type]');
                if (!wrapper.length) {
                    return true;
                }
                that.domInputChanged(wrapper, 2);
            });
            $(document).on('click', '[data-btn-action="cancel"]', function () {
                var wrapper = $(this).closest('tr[data-id][data-type]');
                if (!wrapper.length) {
                    return true;
                }
                that.viewMode(wrapper);
            });
        },
        /**
         * edit mode
         */
        editMode: function (dom) {
            var that = this,
                lang = cv.lang.getLangCur(),
                roleHtml = '',
                projId = dom.attr('data-id'),
                roleValue = typeof valueTrans.role[projId] !== 'undefined' ? valueTrans.role[projId].id : '';
            that.showHideBtn(dom, 'edit');
            Object.keys(dataFixSelect.role[lang]).map(function (key) {
                roleHtml += '<option value="' + key + '"' + (key === roleValue ? 'selected' : '') + '>'
                    + dataFixSelect.role[lang][key]
                    + '</option>';
            });
            dom.addClass('editting').find('.role-select').html(roleHtml);
            dom.find('[data-show-mode="view"]').addClass('hidden');
            that.tagitPlugin(dom);
            dom.find('[data-mode-dom="view"]').each (function () {
                var domView = $(this),
                    domEdit = domView.nextAll('[data-mode-dom="edit"]:first');
                domView.addClass('hidden');
                domEdit.removeClass('hidden');
            });
        },
        /**
         * auto add
         *
         * @return {undefined}
         */
        actionAutoAdd: function () {
            var that = this,
                types = [];
            $('[data-btn-row-add]').each (function() {
                var type = $(this).data('btn-row-add');
                if (!type || types.indexOf(type) > -1) {
                    return true;
                }
                types.push(type);
            });
            that.reloadPlugin();
            that.viewMode();
        },
        // action active tab
        activeTab: function () {
            var that = this;
            $('a[data-toggle="tab"][data-tab-title="cv-proj"]').on('shown.bs.tab', function (e) {
                that.activeTabMain();
            });
            setTimeout(function () {
                if ($('.tbl-proj-exper').is(':visible')) {
                    that.activeTabMain();
                }
            }, 200);
            $(window).load(function () {
                if ($('.tbl-proj-exper').is(':visible')) {
                    that.activeTabMain();
                }
            });
        },
        /**
         * active tab, call back function
         */
        activeTabMain: function () {
            RKExternal.tblWidth.init();
            // RKExternal.simple.textHeight();
            return true;
        },
        changeApprover: function () {
            var that = this;
            $('[d-ss-btn="approver"]').click(function (e) {
                e.preventDefault();
                that.changeApproverSubmit($(this));
            });
        },
        changeApproverSubmit: function (btnSubmit) {
            var that = this;
            if (btnSubmit.data('process')) {
                return true;
            }
            var assignId = $('[d-ss-dom="select-assign"]').val(),
                oldValue = $('[d-ss-dom="select-assign"]').data('old-value');
            if (!assignId) {
                return that.inputImportError(validMess.field_required);
            }
            if (assignId == oldValue) {
                return that.inputImportError(validMess.assign_same);
            }
            that.inputImportError('');
            btnSubmit.data('process', true);
            btnSubmit.find('.loading-submit').removeClass('hidden');
            btnSubmit.find('.loading-hidden-submit').addClass('hidden');
            btnSubmit.prop('disabled', true);
            $('.overlay-par').removeClass('hidden');
            $.ajax({
                url: btnSubmit.data('action'),
                type: 'POST',
                dataType: 'json',
                data: {
                    approverId: assignId,
                    _token: siteConfigGlobal.token,
                },
                success: function (response) {
                    if (typeof response.reload !== 'undefined' && ''+response.reload === '1') {
                        window.location.reload();
                        return true;
                    }
                    //error
                    if (typeof response.status === 'undefined' || !response.status) {
                        return RKExternal.notify(response.message, false);
                    }
                    RKExternal.notify(response.message);
                    $('[d-ss-dom="select-assign"]').data('old-value', assignId);
                },
                error: function (response) {
                    if (typeof response === 'object' && response.message) {
                        RKExternal.notify(response.message, false);
                    } else if (typeof response === 'object' &&
                        typeof response.responseJSON === 'object' &&
                        response.responseJSON.message
                    ) {
                        RKExternal.notify(response.responseJSON.message, false);
                    } else {
                        RKExternal.notify('System error', false);
                    }
                },
                complete: function () {
                    btnSubmit.prop('disabled', false);
                    btnSubmit.data('process', false);
                    btnSubmit.find('.loading-submit').addClass('hidden');
                    btnSubmit.find('.loading-hidden-submit').removeClass('hidden');
                    $('.overlay-par').addClass('hidden');
                }
            });
        },
        inputImportError: function (message) {
            var domError = $('[d-ss-error="select-assign"]');
            if (!domError.length) {
                return true;
            }
            domError.html(message);
            if (!message) {
                domError.addClass('hidden');
            } else {
                domError.removeClass('hidden');
            }
        },
        actionInitPage: function () {
            $('[data-flag-dom="employee-left-menu"] > li').click(function () {
                if ($(this).hasClass('disabled')) {
                    return false;
                }
            });
            if (globalVar.accessApprover) {
                $('[data-flag-dom="employee-left-menu"] > li:not(.active)')
                    .addClass('disabled');
            }
        },
    };
    /**
     * export skill sheet
     */
    cv.export = {
        init: function () {
            var that = this,
                now = new Date();
            that.xml = {ja: null, en: null};
            that.nameUser = $('[data-excel-render="user-name"]').text();
            that.fileName = 'Rikkeisoft_Skillsheet_'
                + $('[data-excel-render="user-name"]').text().trim()
                + '_' + RKExternal.simple.formatDate('Ymd');
            that.xmlElement = null;
            $('[data-fg-dom="btn-export-cv"]').click(function () {
                var btnThis = $(this);
                if (btnThis.data('process')) {
                    return true;
                }
                btnThis.data('process', 1);
                btnThis.prop('disabled', true);
                cv.edit.viewMode();
                var langCur = cv.lang.getLangCur();
                if (!that.xml[langCur]) {
                    RKExternal.excel.getXmlFile(globalVar.cvXmlFile[langCur], function (xmlDom) {
                        that.xml[langCur] = xmlDom;
                        that.exportDataCv(xmlDom.cloneNode(true), btnThis);
                    });
                    return true;
                }
                that.exportDataCv(that.xml[langCur].cloneNode(true), btnThis);
            });
        },
        /**
         * init xml element node default
         *
         * @param {type} xmlJq
         * @return {Boolean}
         */
        initXmlElement: function (xmlJq) {
            var that = this;
            var textDot = xmlJq.find('Cell[d-dom="dot"] Data').text();
            xmlJq.find('Cell[d-dom="dot"]').removeAttr('d-dom').children().text('');
            that.xmlElement = {
                Data: xmlJq.find('Data:first').clone().text(''),
                textDot: textDot,
                CellSkill: {
                    title: xmlJq.find('Cell[d-skill-title]:first').clone(),
                    exper: xmlJq.find('Cell[d-skill="exper"]').clone(),
                    name: xmlJq.find('Cell[d-skill="name"]').clone(),
                },
                CellProj: {},
                tail: {
                    proj: {},
                    skill: {},
                },
                CellMore: {
                    statement: {
                        label: xmlJq.find('Cell[d-cell-label="statement"]').clone(),
                        val: xmlJq.find('Cell[d-cell-val="statement"]').clone(),
                    },
                    spaceProj: xmlJq.find('Row[d-row-space="proj-more"]').clone(),
                },
                RowMulti: {},
                RowTitle: xmlJq.find('Row[d-row-multi="title"]'),
                StyleId: {
                    tail: {
                        proj: {},
                        skill: {},
                    },
                },
            };
            that.cloneDomsXml(xmlJq, that.xmlElement.CellSkill, 'd-skill', [
                'level-1','level-2','level-3','level-4','level-5']);
            that.cloneDomsXml(xmlJq, that.xmlElement.CellProj, 'd-proj', ['name',
                'desc', 'os', 'lang', 'other', 'responsible', 'start_at', 'period_y',
                'end_at', 'period_m']);
            that.cloneDomsXml(xmlJq, that.xmlElement.tail.proj, 'd-proj-tail', ['name',
                'desc', 'os', 'lang', 'other', 'responsible', 'start_at', 'period_y',
                'end_at', 'period_m']);
            that.cloneDomsXml(xmlJq, that.xmlElement.tail.skill, 'd-skill-tail', ['name',
                'level', 'exper']);
            that.getStyleIdDomsXml(xmlJq, that.xmlElement.StyleId.tail.proj, 'd-proj-tail', ['name',
                'desc', 'os', 'lang', 'other', 'responsible', 'start_at', 'period_y',
                'end_at', 'period_m']);
            that.getStyleIdDomsXml(xmlJq, that.xmlElement.StyleId.tail.skill, 'd-skill-tail', [
                'name', 'level', 'exper']);
            that.xmlElement.RowMulti = {
                1: xmlJq.find('Row[d-row-multi="1"]').text('').clone(),
                2: xmlJq.find('Row[d-row-multi="2"]').text('').clone(),
            };

            //remove element
            that.xmlElement.RowTitle.nextAll().remove();
        },
        /**
         * write excel cv
         */
        exportDataCv: function (xmlDom, btnExport) {
            var that = this,
                xmlJq = $(xmlDom);
            that.initXmlElement(xmlJq);
            // change name of ws
            xmlJq.find('Worksheet').attr('ss:Name', that.nameUser);
            that.renderBasicInfo(xmlJq);
            that.renderProj(xmlJq);
            that.renderInfoMore(xmlJq);
            that.renderSkillGroup(xmlJq);
            RKExternal.excel.saveFile(xmlDom, that.fileName, true);
            btnExport.data('process', 0);
            btnExport.prop('disabled', false);
        },
        /**
         * render basic info
         */
        renderBasicInfo: function (xmlJq) {
            var that = this;
            // get text in table
            xmlJq.find('[d-cell-text]').each(function (i, item) {
                var val = $(item).attr('d-cell-text');
                val = $('[data-excel-render="'+val+'"]').text();
                if (val) {
                    val = val.trim();
                }
                that.setValForCell($(item), val);
            });
            // get value of input in table
            xmlJq.find('[d-cell-val]').each(function (i, item) {
                var val = $(item).attr('d-cell-val');
                val = $('[name="'+val+'"]').val();
                if (val) {
                    val = val.trim();
                }
                that.setValForCell($(item), val);
            });
            // get value of select
            xmlJq.find('[d-cell-select]').each(function (i, item) {
                var type = $(item).attr('d-cell-select'), val = '';
                $('select[name="'+type+'"] option:selected').each (function () {
                    val += $(this).text().trim() + ', ';
                });
                that.setValForCell($(item), val.slice(0, -2));
            });
        },
        /**
         * render xml project
         *
         * @param {dom jquery} xmlJq
         */
        renderProj: function (xmlJq) {
            var that = this,
                domProjs = $('[data-tbl-cv="proj"] tbody tr[data-id][data-type="proj"][data-lang="'+ cv.lang.getLangCur() +'"]'),
                lengthProjs = domProjs.length;
            domProjs.each(function (i, v) {
                that.renderProjItem(xmlJq, $(v));
            });
            that.changeStyleIdTailProj(xmlJq);
            if (lengthProjs) {
                return true;
            }
            // insert demo project  if not exists any project
            that.renderProjItemDemo(xmlJq);
        },
        /**
         * render xml project item
         *
         * @param {dom jquery} xmlJq
         * @param {dom} domProj
         */
        renderProjItem: function (xmlJq, domProj) {
            var that = this,
                projData = {};
            that.getValOfDomHtml(domProj, projData, 'd-dom-proj', ['name',
                'os', 'lang', 'other', 'responsible', 'start_at', 'end_at',
                'period_y', 'period_y_label', 'period_m', 'period_m_label']);
            that.getValOfDomHtml(domProj, projData, 'd-dom-proj', ['desc'], 'val');
            projData['period_y'] += ' ' + projData['period_y_label'];
            projData['period_m'] += ' ' + projData['period_m_label'];
            // new item not fill => not export
            if (!projData.name && !projData.os && !projData.lang && !projData.other &&
                !projData.responsible && !projData.start_at && !projData.end_at
            ) {
                return true;
            }
            if (((projData.period_m === '0 Month' && projData.period_y === '0 Year') ||
                (projData.period_m === ' Month' && projData.period_y === ' Year') ||
                (projData.period_m === '0 月' && projData.period_y === '0 年') ||
                (projData.period_m === ' 月' && projData.period_y === ' 年')) &&
                (!projData.start_at || !projData.end_at)) {
                return false;
            }
            if (projData.desc) {
                projData.desc = RKExternal.excel.replaceEnterToBr(projData.desc);
            }
            var cellProj = that.cloneArrayDomsXml(that.xmlElement.CellProj),
                rowMulti = that.cloneArrayDomsXml(that.xmlElement.RowMulti);
            $.each (['name', 'desc', 'os', 'lang', 'other', 'responsible', 'start_at',
                'period_y'], function (i, v)
            {
                that.setValForCell(cellProj[v], projData[v]);
                rowMulti[1].append(cellProj[v]);
            });
            $.each (['end_at', 'period_m'], function (i, v)
            {
                that.setValForCell(cellProj[v], projData[v]);
                rowMulti[2].append(cellProj[v]);
            });
            xmlJq.find('Table').append(rowMulti[1]);
            xmlJq.find('Table').append(rowMulti[2]);
        },
        /**
         * change style for tail skill
         */
        changeStyleIdTailProj: function (xmlJq) {
            var that = this,
                rowTail1 = xmlJq.find('[d-proj="name"]:last').parent(),
                rowTail2 = xmlJq.find('[d-proj="end_at"]:last').parent();
            rowTail1.children('Cell').each (function (i, v) {
                var attrSkill = $(v).attr('d-proj');
                if (!attrSkill) {
                    return true;
                }
                if (typeof that.xmlElement.StyleId.tail.proj[attrSkill] !== 'string') {
                    return true;
                }
                $(v).attr('ss:StyleID', that.xmlElement.StyleId.tail.proj[attrSkill]);
            });
            rowTail2.children('Cell').each (function (i, v) {
                var attrSkill = $(v).attr('d-proj');
                if (!attrSkill) {
                    return true;
                }
                if (typeof that.xmlElement.StyleId.tail.proj[attrSkill] !== 'string') {
                    return true;
                }
                $(v).attr('ss:StyleID', that.xmlElement.StyleId.tail.proj[attrSkill]);
            });
        },
        /**
         * render project item demo null data
         *
         * @param {type} xmlJq
         * @return {undefined}
         */
        renderProjItemDemo: function (xmlJq) {
            var that = this,
                cellProj = that.cloneArrayDomsXml(that.xmlElement.tail.proj),
                rowMulti = that.cloneArrayDomsXml(that.xmlElement.RowMulti);
            $.each (['name', 'desc', 'os', 'lang', 'other', 'responsible', 'start_at',
                'period_y'], function (i, v)
            {
                rowMulti[1].append(cellProj[v]);
            });
            $.each (['end_at', 'period_m'], function (i, v)
            {
                rowMulti[2].append(cellProj[v]);
            });
            xmlJq.find('Table').append(rowMulti[1]);
            xmlJq.find('Table').append(rowMulti[2]);
        },
        /**
         * render infor more after project
         *
         * @param {obj} xmlJq
         */
        renderInfoMore: function (xmlJq) {
            var that = this;
            // insert space project / statement
            xmlJq.find('Table').append(that.xmlElement.CellMore.spaceProj.clone());
            // insert more
            var rowMore = that.xmlElement.RowMulti[1].clone(),
                label = that.xmlElement.CellMore.statement.label.clone(),
                val = that.xmlElement.CellMore.statement.val.clone(),
                valStatement = $('[d-excel-br="statement"]').val();
            if (valStatement) {
                valStatement = valStatement.trim();
            }
            that.setValForCell(label, $('[d-excel-text="statement"]').text().trim());
            that.setValForCell(val, RKExternal.excel.replaceEnterToBr(valStatement));
            rowMore.append(label);
            rowMore.append(val);
            xmlJq.find('Table').append(rowMore);
        },
        /**
         * render xml skill group
         *
         * @param {dom jquery} xmlJq
         */
        renderSkillGroup: function (xmlJq) {
            var that = this,
                domSkills = $('[data-skill-dom="head"]').nextAll(),
                RowsData = that.xmlElement.RowTitle.nextAll(),
                isNewRow = false,
                RowCur;
            that.RowIndexSkill = 0;
            that.countSkillType = {};
            that.skillTypeCur = null;
            domSkills.each(function (i, v) {
                that.renderSkill(xmlJq, RowsData, $(v));
            });
            if (!that.countSkillType[that.skillTypeCur]) {
                if (typeof RowsData[that.RowIndexSkill] === 'object') {
                    RowCur = $(RowsData[that.RowIndexSkill]);
                } else {
                    RowCur = that.xmlElement.RowMulti[that.RowIndexSkill%2+1].clone();
                    isNewRow = true;
                }
                that.renderSkillItemDemo(xmlJq, RowCur, isNewRow);
            }
            that.changeStyleIdTailSkill(xmlJq);
        },
        /**
         * render xml skill all
         *
         * @param {dom jquery} xmlJq
         */
        renderSkill: function (xmlJq, RowsData, trDom) {
            var that = this,
                RowCur, isNewRow = false;
            // check exist Row in excel
            if (typeof RowsData[that.RowIndexSkill] === 'object') {
                RowCur = $(RowsData[that.RowIndexSkill]);
            } else {
                RowCur = that.xmlElement.RowMulti[that.RowIndexSkill%2+1].clone();
                isNewRow = true;
            }
            // check tr is title group
            if (typeof trDom.attr('d-skill-title') !== 'undefined') {
                // change check type skill current, count skill = 0 => insert demo
                if (that.skillTypeCur !== null &&
                    that.skillTypeCur != trDom.attr('d-skill-title') &&
                    !that.countSkillType[that.skillTypeCur]
                ) {
                    that.renderSkillItemDemo(xmlJq, RowCur, isNewRow);
                    that.RowIndexSkill++;
                    if (typeof RowsData[that.RowIndexSkill] === 'object') {
                        RowCur = $(RowsData[that.RowIndexSkill]);
                        isNewRow = false;
                    } else {
                        RowCur = that.xmlElement.RowMulti[that.RowIndexSkill%2+1].clone();
                        isNewRow = true;
                    }
                }
                var cell = that.xmlElement.CellSkill.title.clone();
                that.setValForCell(cell, trDom.children('[d-skill-title="text"]').text().trim());
                RowCur.append(cell);
                if (isNewRow) {
                    xmlJq.find('Table').append(RowCur);
                }
                that.skillTypeCur = trDom.attr('d-skill-title');
                that.countSkillType[that.skillTypeCur] = 0;
                that.RowIndexSkill++;
                return true;
            }
            // tr is  skill item
            if (typeof trDom.attr('data-id') !== 'undefined' && typeof trDom.attr('data-type') !== 'undefined') {
                if (that.renderSkillItem(xmlJq, RowCur, trDom)) {
                    if (isNewRow) {
                        xmlJq.find('Table').append(RowCur);
                    }
                    that.countSkillType[that.skillTypeCur]++;
                    that.RowIndexSkill++;
                }
            }
        },
        /**
         * render xml skill item
         */
        renderSkillItem: function (xmlJq, RowCur, trDom) {
            var that = this,
                skillData = {};
            that.getValOfDomHtml(trDom, skillData, 'd-dom-skill', ['name',
                'exper_y', 'exper_y_label', 'exper_m', 'exper_m_label']);
            skillData['exper'] = skillData['exper_y'] + ' ' + skillData['exper_y_label']
                + ' - ' +skillData['exper_m'] + ' ' + skillData['exper_m_label'];
            skillData['level'] = trDom.find('[d-dom-skill="level"]:checked').val();
            // new item not fill => not export
            if (!parseInt(skillData.exper_y) && !parseInt(skillData.exper_m)) {
                return false;
            }
            if (!skillData.name && !skillData.exper_y && !skillData.exper_m && !skillData.level) {
                return false;
            }
            var cellSkill = that.cloneArrayDomsXml(that.xmlElement.CellSkill);
            $.each (['name', 'level-1', 'level-2', 'level-3', 'level-4', 'level-5', 'exper'], function (i, v)
            {
                that.setValForCell(cellSkill[v], skillData[v]);
                RowCur.append(cellSkill[v]);
            });
            if (skillData['level']) {
                that.setValForCell(RowCur.find('[d-skill="level-'+skillData['level']+'"]'), that.xmlElement.textDot);
            }
            return true;
        },
        /**
         * change style for tail skill
         */
        changeStyleIdTailSkill: function (xmlJq) {
            var that = this,
                rowTail = xmlJq.find('[d-skill="name"]:last').parent();
            rowTail.children('Cell').each (function (i, v) {
                var attrSkill = $(v).attr('d-skill');
                if (!attrSkill) {
                    return true;
                }
                if (attrSkill.indexOf('-') > -1) {
                    attrSkill = attrSkill.substr(0, attrSkill.indexOf('-'));
                }
                if (typeof that.xmlElement.StyleId.tail.skill[attrSkill] !== 'string') {
                    return true;
                }
                $(v).attr('ss:StyleID', that.xmlElement.StyleId.tail.skill[attrSkill]);
            });
        },
        /**
         * render skill item demo null data
         *
         * @param {type} xmlJq
         * @return {undefined}
         */
        renderSkillItemDemo: function (xmlJq, RowCur, isNewRow) {
            var that = this,
                cellSkill = that.cloneArrayDomsXml(that.xmlElement.CellSkill);
            $.each (['name', 'level-1', 'level-2', 'level-3', 'level-4', 'level-5', 'exper'], function (i, v)
            {
                RowCur.append(cellSkill[v]);
            });
            if (isNewRow) {
                xmlJq.find('Table').append(RowCur);
            }
        },
        /**
         * set value for cell xml
         *
         * @param {xml dom} cellJq
         * @param {string} val
         */
        setValForCell: function (cellJq, val) {
            var that = this;
            if (typeof val === 'undefined') {
                val = '';
            }
            if (cellJq.attr('d-cell-date-format') && val) {
                var type = cellJq.attr('d-cell-date-format'),
                    date = new Date(val);
                var day = date.getDate();
                if (day < 10) {
                    day = '0' + day;
                }
                var month = date.getMonth() + 1;
                if (month < 10) {
                    month = '0' + month;
                }
                val = type.replace(/y/gi, date.getFullYear())
                    .replace(/m/gi, month)
                    .replace(/d/gi, day);
            }
            if (cellJq.attr('d-val-suffix')) {
                val += ' ' + cellJq.attr('d-val-suffix');
            }
            if (cellJq.children('Data').length) {
                cellJq.children('Data').text(val);
            } else {
                var dataElement = that.xmlElement.Data.clone();
                dataElement.text(val);
                cellJq.append(dataElement);
            }
        },
        /**
         * clone xml doms follow key
         *
         * @param {xml} xmlJq
         * @param {obj} result
         * @param {array} keys
         * @param {string} dType
         * @return {obj}
         */
        cloneDomsXml: function (xmlJq, result, dType, keys, prefixKey) {
            if (typeof result !== 'object' || !result) {
                result = {};
            }
            if (typeof prefixKey !== 'string' || !prefixKey) {
                prefixKey = '';
            }
            $.each (keys, function (i, v) {
                result[v] = xmlJq.find('Cell['+dType+'="'+(prefixKey+v)+'"]').clone();
            });
            return result;
        },
        /**
         * get style id of xml element
         *
         * @param {xml dom} xmlJq
         * @param {obj} result
         * @param {string} dType
         * @param {array} keys
         */
        getStyleIdDomsXml: function (xmlJq, result, dType, keys) {
            if (typeof result !== 'object' || !result) {
                result = {};
            }
            $.each (keys, function (i, v) {
                result[v] = xmlJq.find('Cell['+dType+'="'+v+'"]').attr('ss:StyleID');
            });
            return result;
        },
        cloneArrayDomsXml: function (arrayXmlDoms) {
            var cloneResult = {};
            $.each(arrayXmlDoms, function (i, v) {
                cloneResult[i] = v.clone();
            });
            return cloneResult;
        },
        /**
         * get value (text) of dom html
         *
         * @param {obj Jq} domProj
         * @param {obj} result
         * @param {string} dType
         * @param {array} keys
         * @return {obj}
         */
        getValOfDomHtml: function (domProj, result, dType, keys, useFunc) {
            if (typeof result !== 'object' || !result) {
                result = {};
            }
            if (typeof useFunc !== 'string' || !useFunc) {
                useFunc = 'text'; // or = val
            }
            $.each (keys, function (i, v) {
                var domText = domProj.find('['+dType+'="'+v+'"]'),
                    valText = domText[useFunc]();
                if (valText) {
                    valText = valText.trim();
                }
                if (useFunc === 'val') {
                    result[v] = valText;
                    return true;
                }
                result[v] = domText.attr('title');
                if (!result[v]) {
                    result[v] = valText;
                }
            });
            return result;
        },
    };
    cv.import = {
        init: function () {
            var that = this,
                domInput = $('[d-ss-input="file-cv"]');
            if (!domInput.length) {
                return false;
            }
            that.option = {
                type: ['csv', 'xls', 'xlsx'],
                size: 5, // MB
                message_required: validMess.file_required,
                message_size: validMess.file_large,
                message_type: validMess.file_type + ': xls, xlsx, csv',
            };
            that.file = {
                colMin: 'A'.charCodeAt(0),
                colMax: 'T'.charCodeAt(0) + 1,
                rowPS: 0,
            };
            that.initDataLabel();
            $('[d-ss-dom="import"]').click(function (e) {
                e.preventDefault();
                that.changeInput($(this), domInput);
            });
        },
        /**
         * change validate input -> submit, and upload
         *
         * @param {type} btnUpload
         * @param {type} domInput
         * @returns {Boolean}
         */
        changeInput: function (btnUpload, domInput) {
            var that = this;
            if (!domInput[0].files || !domInput[0].files[0]) {
                that.inputImportError(that.option.message_required, domInput);
                return false;
            }
            var fileUpload = domInput[0].files[0];
            if($.inArray(RKExternal.simple.getFileSplitFromPath(fileUpload.name)[2], that.option.type) === -1) {
                domInput.val('');
                that.inputImportError(that.option.message_type, domInput);
                return false;
            }
            if (fileUpload.size / 1000 / 1000 > that.option.size) {
                domInput.val('');
                that.inputImportError(that.option.message_size, domInput);
                return false;
            }
            that.inputImportError('', domInput);
            that.preProcessFile(fileUpload, btnUpload, domInput);
        },
        preProcessFile: function (fileUpload, btnUpload) {
            var that = this,
                loadingSubmit = btnUpload.find('.loading-submit'),
                loadingHiddenSubmit = btnUpload.find('.loading-hidden-submit');
            if (btnUpload.data('running')) {
                return true;
            }
            btnUpload.data('running', true);
            btnUpload.prop('disabled', true);
            loadingSubmit.removeClass('hidden');
            loadingHiddenSubmit.addClass('hidden');
            $('.overlay-par').removeClass('hidden');
            that.readFile(fileUpload, btnUpload);
        },
        readFile: function (fileUpload, btnUpload) {
            var that = this,
                reader = new FileReader();
            reader.onload = function(e) {
                var data = e.target.result,
                    workbook = XLSX.read(data, {type: 'binary', dateNF: 'yyyy-mm-dd'}),
                    sheetName = workbook.SheetNames[0],
                    worksheet = workbook.Sheets[sheetName];
                delete worksheet["!margins"];
                delete worksheet["!ref"];
                delete worksheet["!merges"];
                that.messageError = '';
                that.execData(worksheet, btnUpload);
            };
            reader.readAsBinaryString(fileUpload);
        },
        /**
         * exec data to json
         *
         * @param {obj} worksheet
         */
        execData: function (worksheet, btnUpload) {
            var that = this;
            var valTitle = that.getValCell(worksheet['A2']),
                lang = cv.lang.getLangCur();
            if ((lang === 'en' && new RegExp('情 報 処 理 技 術 者 経 歴 書(.|\r|\n|\r\n)*').test(valTitle)
                || lang === 'ja' && new RegExp('Developer Skill Sheet(.|\r|\n|\r\n)*', 'i').test(valTitle))
                && !confirm(globalValidMess.confirm_continue_import)
            ) {
                that.processCompleteImport(btnUpload);
                return;
            }

            that.resetData();
            that.excecDataProj(worksheet);
            that.execDataBasicInfo(worksheet);
            that.changeData();
            // check error
            if (that.messageError) {
                that.inputImportError('<ul>' + that.messageError + '</ul>');
                that.processCompleteImport(btnUpload);
                return true;
            }
            $.ajax({
                url: btnUpload.attr('action'),
                type: 'POST',
                dataType: 'json',
                data: {
                    data: JSON.stringify(that.data),
                    _token: siteConfigGlobal.token,
                    lang: lang,
                },
                success: function (response) {
                    if (typeof response.reload !== 'undefined' && ''+response.reload === '1') {
                        window.location.reload();
                        return true;
                    }
                    //error
                    if (typeof response.status === 'undefined' || !response.status) {
                        RKExternal.notify(response.message, false);
                    }
                    that.processCompleteImport(btnUpload);
                },
                error: function (response) {
                    if (typeof response === 'object' && response.message) {
                        RKExternal.notify(response.message, false);
                    } else if (typeof response === 'object' &&
                        typeof response.responseJSON === 'object' &&
                        response.responseJSON.message
                    ) {
                        RKExternal.notify(response.responseJSON.message, false);
                    } else {
                        RKExternal.notify('System error', false);
                    }
                    that.processCompleteImport(btnUpload);
                },
            });
        },
        processCompleteImport: function (btnUpload) {
            btnUpload.data('running', false);
            btnUpload.prop('disabled', false);
            btnUpload.find('.loading-submit').addClass('hidden');
            btnUpload.find('.loading-hidden-submit').removeClass('hidden');
            $('.overlay-par').addClass('hidden');
        },
        execDataBasicInfo: function (worksheet) {
            var that = this, i;
            for (i = 4; i < 10; i++) {
                that.execDataBasicInfoCol(worksheet, i);
            }
            //row personal statement
            if (that.file.rowReference > 0) {
                that.execDataBasicInfoCol(worksheet, that.file.rowReference);
            }
        },
        execDataBasicInfoCol: function (worksheet, i) {
            var that = this;
            for (var j = that.file.colMin; j < that.file.colMax; j++) {
                var cell = worksheet[String.fromCharCode(j) + '' + i];
                if (typeof cell === 'undefined') {
                    continue;
                }
                var valLabel = that.getValCell(cell);
                $.each(that.labelColBasic, function (key, dbData) {
                    var newreg = new RegExp('^('+key+')$', 'i');
                    if (!newreg.test(valLabel) ||
                        typeof worksheet[String.fromCharCode(j + dbData[4]) + '' + i] === 'undefined'
                    ) {
                        return true;
                    }
                    var cellValue = worksheet[String.fromCharCode(j + dbData[4]) + '' + i],
                        valCol = that.getValCell(cellValue);
                    if (valCol) {
                        valCol = valCol.trim();
                    }
                    if (typeof (dbData[2]) !== 'undefined') { // callback function convert
                        valCol = that[dbData[2]](valCol, typeof dbData[3] !== 'undefined' ? dbData[3] : null);
                    }
                    if (valCol !== false) {
                        that.data[dbData[0]][dbData[1]] = valCol;
                    }
                    delete worksheet[String.fromCharCode(j + dbData[4]) + '' + i];
                });
                delete worksheet[String.fromCharCode(j) + '' + i];
            }
        },
        excecDataProj: function (worksheet) {
            var that = this,
                rowTitleProj = 13, // row title index of proj
                rowTitleSkill = 9, // row title index of skill
                titleIndex = {
                    proj: {},
                    skill: {},
                };
            // get title index of proj, skill
            [rowTitleProj, rowTitleSkill].map(function (row) {
                for (var j = that.file.colMin; j < that.file.colMax; j++) {
                    var jChar = String.fromCharCode(j),
                        cell = worksheet[jChar + '' + row];
                    if (typeof cell === 'undefined') {
                        continue;
                    }
                    var valLabel = that.getValCell(cell);
                    $.each(that.labelColSkills, function (key, dbData) {
                        var newreg = new RegExp('^('+key+')$', 'i');
                        if (!newreg.test(valLabel)) {
                            return true;
                        }
                        titleIndex[dbData[0]][dbData[1]] = {
                            col: jChar,
                            func: (typeof dbData[2] !== 'undefined' && dbData[2])? dbData[2] : null,
                            validate: (typeof dbData[3] === 'object' && typeof dbData[3].messages === 'object' && typeof dbData[3].rules === 'object')? dbData[3] : null,
                        };
                    });
                    // delete worksheet[jChar + '' + row]; // row title skill diff row title project
                }
            });
            that.execDataRowsProj(worksheet, titleIndex.proj);
            that.execDataRowsSkill(worksheet, titleIndex.skill);
        },
        execDataRowsProj: function (worksheet, titleProj) {
            var that = this;
            if ($.isEmptyObject(titleProj)) {
                return null;
            }
            var start = 14, // row start first project
                result = true;
            while (1) {
                if (!worksheet[titleProj.name.col+''+start]
                    && !worksheet[titleProj.name.col+''+(start + 2)]
                    && !worksheet[titleProj.name.col+''+(start + 4)]
                    && !worksheet[titleProj.name.col+''+(start + 6)]
                ) {
                    break;
                }
                that.data.proj[start] = {};
                $.each(titleProj, function (colKey, colIndex) {
                    //colIndex: {col: 'A', func: 'funcName', validate: 'validateFunc'};
                    var col = colIndex.col + '';
                    if (worksheet[col + start]) {
                        var value = that.getValCell(worksheet[col + start]);
                        if (colIndex.func) {
                            value = that[colIndex.func](value);
                        }
                        if (colIndex.validate) {
                            $.each(colIndex.validate.rules, function (key, rule) {
                                switch (key) {
                                    case 'max':
                                        if (value.length > rule) {
                                            that.messageError += '<li>' + col + start + ':' + colIndex.validate.messages.max + '</li>';
                                            result = false;
                                        }
                                        break;
                                    default:
                                        break;
                                }
                            });
                        }
                        if (colKey === 'name') {
                            that.data.proj[start]['name'] = value;
                            if (typeof worksheet[col + (start + 1)] != 'undefined') {
                                that.data.proj[start]['description'] = that.getValCell(worksheet[col + (start + 1)]);
                            }
                            return;
                        }
                        if (colKey === 'role_team_size') {
                            that.data.proj[start]['role'] = value;
                            that.data.proj[start]['total_member'] = null;
                            that.data.proj[start]['total_mm'] = null;
                            if (typeof worksheet[col + (start + 1)] != 'undefined') {
                                var cellData = that.getValCell(worksheet[col + (start + 1)]),
                                    memberMatches = cellData.match(/^\s*(Team|チーム)\s*:\s*(\d+)\s*(members*|名|)\s*/i),
                                    mmMatches = cellData.match(/\s*(全体|Total)\s*:\s*(\d+[\.\,]?\d+|\d+)\s*(MM|)\s*$/i);
                                // get total members
                                memberMatches && memberMatches.length === 4 && (that.data.proj[start]['total_member'] = memberMatches[2]);
                                // get total mm
                                mmMatches && mmMatches.length === 4 && (that.data.proj[start]['total_mm'] = mmMatches[2].replace(',', '.'));
                            }
                            return;
                        }
                        if (colKey === 'time') {
                            that.data.proj[start]['start_at'] = value;
                            if (typeof worksheet[col + (start + 2)] != 'undefined') {
                                that.data.proj[start]['end_at'] = that.convertDate(that.getValCell(worksheet[col + (start + 2)]));
                            }
                            return;
                        }
                        that.data.proj[start][colKey] = value;
                    }
                    delete worksheet[colIndex.col+''+start];
                });
                start += 4;
            }
            that.file.rowReference = start + 1;
            return result;
        },
        execDataRowsSkill: function (worksheet, titleSkill) {
            var that = this;
            if ($.isEmptyObject(titleSkill)) {
                return null;
            }
            titleSkill = that.execTitleSkill(titleSkill);
            var start = 10,
                titleType = 'lang',
                missData = 1;
            while(1) {
                start++;
                //don't know more
                /*if (!worksheet[titleSkill.info.tag_id.col+''+start] &&
                    !worksheet[titleSkill.more.exp_y.col+''+start]
                ) {*/
                if (!worksheet[titleSkill.info.tag_id.col+''+start]) {
                    missData++;
                    if (missData > 5) {
                        break;
                    }
                    continue;
                }
                missData = 1;
                //don't know more
                /*if (!worksheet[titleSkill.more.exp_y.col+''+start]) {*/
                if (!worksheet[titleSkill.info.exp.col+''+start]) {
                    // title type skill
                    titleType = that.getSkillTitleType(worksheet[titleSkill.info.tag_id.col+''+start]);
                    delete worksheet[titleSkill.info.tag_id.col+''+start];
                    continue;
                }
                var skillItem = {};
                // store name, exp number
                $.each(titleSkill.info, function (colKey, colIndex) {
                    if (worksheet[colIndex.col+''+start]) {
                        var value = that.getValCell(worksheet[colIndex.col+''+start]);
                        if (colIndex.func) {
                            value = that[colIndex.func](value);
                        }
                        skillItem[colKey] = value;
                    }
                    delete worksheet[colIndex.col+''+start];
                });
                if (!skillItem.exp) {
                    skillItem.exp = 0;
                }
                //var expNumber = that.convertExpTime(skillItem.exp, that.getValCell(worksheet[titleSkill.more.exp_y.col+''+start])); don't know more
                var expNumber = that.convertExpTime(skillItem.exp, 'Year');
                $.extend(skillItem, expNumber, true);
                // level
                $.each(titleSkill.level, function (level, colIndex) {
                    if (worksheet[colIndex.col+''+start]) {
                        var value = that.getValCell(worksheet[colIndex.col+''+start]);
                        if (value) {
                            skillItem['level'] = level;
                        }
                    }
                    delete worksheet[colIndex.col+''+start];
                });
                that.data.skill[start] = {};
                that.data.skill[start][titleType] = skillItem;
            }
        },
        execTitleSkill: function (titleSkill) {
            var result = {
                level: {},
                info: {},
                more: {},
            };
            $.each(titleSkill, function (i, v) {
                if (isNaN(i)) {
                    result.info[i] = v;
                } else {
                    result.level[i] = v;
                }
            });
            //don't know more???
            if (result.info.exp) {
                result.more.exp_y = {
                    col: String.fromCharCode((result.info.exp.col.charCodeAt() + 1)),
                    func: null,
                };
            }
            return result;
        },
        getSkillTitleType: function (cell) {
            var that = this,
                val = that.getValCell(cell),
                typeResult;
            $.each(that.skillHead, function (key, type) {
                var newreg = new RegExp('^('+key+')$', 'i');
                if (!newreg.test(val)) {
                    return true;
                }
                typeResult = type;
                return false;
            });
            return typeResult;
        },
        /**
         * show error for input file cv import
         *
         * @param {type} message
         * @param {type} domInput
         * @returns {undefined}
         */
        inputImportError: function (message, domInput) {
            var domError = $('[data-ss-error="file-cv"]');
            if (!domError.length) {
                domInput.after('<p class="error hidden" data-ss-error="file-cv"></p>');
                domError = $('[data-ss-error="file-cv"]');
            }
            domError.html(message);
            if (!message) {
                domError.addClass('hidden');
            } else {
                domError.removeClass('hidden');
            }
        },
        /**
         *
         * @returns {undefined}
         */
        initDataLabel: function () {
            var that = this;
            // label in excel: [tableName, columnName, function convert, col2, offset-x value]
            that.labelColBasic = {
                '氏名|Name': ['employees', 'name', undefined, undefined, 2],
                'No.|序数': ['employees', 'proj_number', undefined, undefined, 2],
                'カナ': ['employees', 'japanese_name', undefined, undefined, 2],
                '性別|Gender': ['employees', 'gender', 'convertGender', undefined, 1],
                '生年月日|Date of Birth': ['employees', 'birthday', 'convertDate', undefined, 1],
                '本籍地|Place of Birth': ['empl_cv_attr_values', 'code', 'convertAttr', 'address_home', 2],
                '住所|Address': ['empl_cv_attr_values', 'code', 'convertAttr', 'address', 2],
                '出身校|Alma Mater': ['empl_cv_attr_values', 'code', 'convertAttr', 'school_graduation', 2],
                '対応可能分野|Compatible Field': ['empl_cv_attr_values', 'code', 'convertAttr', 'field_dev', 2],
                '日本語レベル(.|\r|\n|\r\n)*|Others Languages|Japanese level': ['empl_cv_attr_values', 'code', 'convertAttrSingle', 'lang_ja_level', 1],
                '業務経験(.|\r|\n|\r\n)*|Work experience': ['empl_cv_attr_values', 'code', 'convertAttrSingle', 'exper_year', 2],
                '日本常駐経験|International Work Experience': ['empl_cv_attr_values', 'code', 'convertAttr', 'exper_japan', 2],
                '英語レベル(.|\r|\n|\r\n)*|English(.|\r|\n|\r\n)*level': ['empl_cv_attr_values', 'code', 'convertAttrSingle', 'lang_en_level', 1],
                '自己PR(.|\r|\n|\r\n)*|Personal Summary(.|\r|\n|\r\n)*': ['empl_cv_attr_value_texts', 'code', 'convertAttrText', 'statement', 2],
                '参照(.|\r|\n|\r\n)*|Reference(.|\r|\n|\r\n)*': ['empl_cv_attr_value_texts', 'code', 'convertAttrText', 'reference', 2],
            };
            that.labelColSkills = {
                'Previous Projects|業(.|\r|\n|\r\n)*務(.|\r|\n|\r\n)*内(.|\r|\n|\r\n)*容': ['proj', 'name', null, {rules: {max: 255}, messages: {max: 'Tên dự án không được lớn hơn 255 ký tự'}}],
                'No.|序(.|\r|\n|\r\n)*数': ['proj', 'proj_number'],
                'Role(.|\r|\n|\r\n)*Team size|役割(.|\r|\n|\r\n)*規模': ['proj', 'role_team_size'],
                'Programming Languages|使(.|\r|\n|\r\n)*用(.|\r|\n|\r\n)*言(.|\r|\n|\r\n)*語': ['proj', 'lang', 'convertBreak'],
                'Environment|環(.|\r|\n|\r\n)*境': ['proj', 'db', 'convertBreak'],
                'Assigned Phases|担(.|\r|\n|\r\n)*当(.|\r|\n|\r\n)*フ(.|\r|\n|\r\n)*ェ(.|\r|\n|\r\n)*ー(.|\r|\n|\r\n)*ズ': ['proj', 'res', 'convertBreak'],
                'Start(.|\r|\n|\r\n)*End|開(.|\r|\n|\r\n)*始(.|\r|\n|\r\n)*終了': ['proj', 'time', 'convertDate'],
                'Start(.|\r|\n|\r\n)*date|作(.|\r|\n|\r\n)*業(.|\r|\n|\r\n)*開(.|\r|\n|\r\n)*始': ['proj', 'start_at', 'convertDate'],
                'End(.|\r|\n|\r\n)*date|作(.|\r|\n|\r\n)*業(.|\r|\n|\r\n)*終(.|\r|\n|\r\n)*了': ['proj', 'end_at', 'convertDate'],
                'Ranking|ランキング': ['skill', 'tag_id'],
                '1': ['skill', '1'],
                '2': ['skill', '2'],
                '3': ['skill', '3'],
                '4': ['skill', '4'],
                '5': ['skill', '5'],
                'Years(.|\r|\n|\r\n)*of(.|\r|\n|\r\n)*experience|経験年数': ['skill', 'exp'],
            };
            that.skillHead = {
                'プログラミング言語|Languages': 'lang',
                'データベース|フレームワーク.*|Environment|Framework.*|Database': 'other',
                'プログラミング言語|OS': 'os',
            };
            that.formatsDate = {
                '^([0-9]{4})\\D+([0-9]{2})\\D+([0-9]{2})\\D*$': 'YYYY-MM-DD',
                '^([0-9]{4})\\D+([0-9]{1})\\D+([0-9]{2})\\D*$': 'YYYY-M-DD',
                '^([0-9]{4})\\D+([0-9]{1})\\D+([0-9]{1})\\D*$': 'YYYY-M-D',
                '^([0-9]{4})\\D+([0-9]{2})\\D+([0-9]{1})\\D*$': 'YYYY-MM-D',
                '^([0-9]{2})\\D+([0-9]{2})\\D+([0-9]{2})\\D*$': 'YY-MM-DD',
                '^([0-9]{2})\\D+([0-9]{1})\\D+([0-9]{2})\\D*$': 'YY-M-DD',
                '^([0-9]{2})\\D+([0-9]{1})\\D+([0-9]{1})\\D*$': 'YY-M-D',
                '^([0-9]{2})\\D+([0-9]{2})\\D+([0-9]{1})\\D*$': 'YY-MM-D',
                '^([0-9]{2})\\D+([0-9]{2})\\D+([0-9]{4})\\D*$': 'DD-MM-YYYY',
                '^([0-9]{2})\\D+([0-9]{1})\\D+([0-9]{4})\\D*$': 'DD-M-YYYY',
                '^([0-9]{1})\\D+([0-9]{2})\\D+([0-9]{4})\\D*$': 'D-MM-YYYY',
                '^([0-9]{1})\\D+([0-9]{1})\\D+([0-9]{4})\\D*$': 'D-M-YYYY',
                '^([0-9]{1})\\D+([0-9]{2})\\D+([0-9]{2})\\D*$': 'D-MM-YY',
                '^([0-9]{1})\\D+([0-9]{1})\\D+([0-9]{2})\\D*$': 'D-M-YY',
                '^([0-9]{2})\\D+([a-zA-Z]{3})\\D+([0-9]{2})\\D*$': 'DD-MMM-YY',
                '^([0-9]{1})\\D+([a-zA-Z]{3})\\D+([0-9]{2})\\D*$': 'D-MMM-YY',
                '^([0-9]{4})\\D+([0-9]{2})\\D*$': 'YYYY-MM',
                '^([0-9]{4})\\D+([0-9]{1})\\D*$': 'YYYY-M',
                '^([0-9]{2})\\D+([0-9]{2})\\D*$': 'YY-MM',
                '^([0-9]{2})\\D+([0-9]{1})\\D*$': 'YY-M',
                '^([a-zA-Z]{3})\\D+([0-9]{1})\\D+([0-9]{4})\\D*$': 'MMM-D-YYYY',
                '^([a-zA-Z]{3})\\D+([0-9]{2})\\D+([0-9]{4})\\D*$': 'MMM-DD-YYYY',
                '^([a-zA-Z]{3})\\D+([0-9]{1})\\D+([0-9]{2})\\D*$': 'MMM-D-YY',
                '^([a-zA-Z]{3})\\D+([0-9]{2})\\D+([0-9]{2})\\D*$': 'MMM-DD-YY',
                '^([a-zA-Z]{3})\\D+([0-9]{2})\\D*$': 'MMM-YY',
                '^([a-zA-Z]{3})\\D+([0-9]{4})\\D*$': 'MMM-YYYY',
            };
            that.resetData();
        },
        /**
         * reset data
         */
        resetData: function () {
            var that = this;
            that.data = {
                'eav' : {},
                'employees': {},
                'eav_s': {},
                'eav_t': {},
                'proj': {},
                'skill': {},
            };
        },
        /**
         * convert gender label to value
         *
         * @param string $value
         * @param string $columnCol
         * @param string $valueColumn
         * @return int
         */
        convertGender: function (value) {
            var genders = {
                'Male|男': 1,
                'Female|女': 0,
            }, result = null;
            $.each(genders, function (key, gender) {
                var newreg = new RegExp('^('+key+')$', 'i');
                if (newreg.test(value)) {
                    result = gender;
                    return false;
                }
            });
            return result;
        },
        /**
         * covert date
         *
         * @param string $value
         * @return string
         */
        convertDate: function(value) {
            var that = this,
                valueM = null;
            $.each(that.formatsDate, function (reg, format) {
                var newreg = new RegExp(reg, 'i'),
                    matches = value.match(newreg);
                if (matches) {
                    if (!matches[3]) {
                        matches[3] = 1;
                    }
                    if (format === 'DD-MM-YYYY' || format === 'D-MM-YYYY' || format === 'D-MM-YY') {
                        if (matches[2] > 12) {
                            var tmp = matches[2];
                            matches[2] = matches[1];
                            matches[1] = tmp;
                        }
                    }
                    valueM = moment(matches[1] + '-' + matches[2] + '-' + matches[3], format);
                    valueM = valueM.format('YYYY-MM-DD');
                    return false;
                }
            });
            if (valueM) {
                return valueM;
            }
            return null;
        },
        /**
         * group data to attribute value formal multi language
         *
         * @param string $value
         * @param string $valueColumn
         * @return boolean
         */
        convertAttr: function(value, labelColumn) {
            var that = this;
            if (typeof labelColumn === 'undefined') {
                labelColumn = null;
            }
            if (labelColumn == 'exper_japan') {
                value = parseFloat(value) + '';
            }
            that.data['eav'][labelColumn] = value;
            return false;
        },
        convertAttrText: function (value, labelColumn) {
            var that = this;
            if (typeof labelColumn === 'undefined') {
                labelColumn = null;
            }
            that.data['eav_t'][labelColumn] = value;
            return false;
        },
        /**
         * group data to attribute value format single
         *
         * @param string $value
         * @param string $valueColumn
         * @return boolean
         */
        convertAttrSingle: function (value, labelColumn)
        {
            var that = this;
            if (typeof labelColumn === 'undefined') {
                labelColumn = null;
            }
            if (labelColumn == 'exper_year') {
                value = parseFloat(value) + '';
            }
            that.data['eav_s'][labelColumn] = value;
            return false;
        },
        convertBreak: function (value) {
            var result = [];
            value.split(/\n|\r|\r\n|\,/).forEach(function (item) {
                if (!item) {
                    return true;
                }
                item = item.replace(/^(\s)+|(・)|(\s)+$/ig, '');
                if (!item) {
                    return true;
                }
                result.push(item);
            });
            return result;
        },
        convertExpTime: function (time, label) {
            //not check NaN
            /*if (isNaN(time)) {
                return {
                    exp_y: 0,
                    exp_m: 0,
                };
            }
            if (/Years|年/i.test(label)) {
                return {
                    exp_y: time,
                    exp_m: 0,
                };
            }
            return {
                    exp_y: 0,
                    exp_m: time,
                };*/
            if (!time) {
                return {
                    exp_y: 0,
                    exp_m: 0
                };
            }
            var matchs = time.match(new RegExp('^[\\s]{0,}([0-9]{1,})[\\s]{0,}(Y|年)[\\s]{0,}-[\\s]{0,}([0-9]{1,2})[\\s]{0,}(M|ヶ月)[\\s]{0,}$'));
            if (!matchs || matchs.lenth < 5) {
                return {
                    exp_y: 0,
                    exp_m: 0
                };
            }
            return {
                exp_y: matchs[1],
                exp_m: matchs[3]
            };
        },
        changeData: function () {
            var that = this,
                eavsExperYear = that.data.eav_s.exper_year;
            if (eavsExperYear) { // convert experience year to number
                eavsExperYear = eavsExperYear.replace(/(年|year|years).*/, '').trim();
                if (isNaN(eavsExperYear)) { // font japanese
                    $.each(that.charNumberJp(), function (jpNumber, unicodeNumber) {
                        var newreg = new RegExp('' + jpNumber, 'gi');
                        eavsExperYear = eavsExperYear.replace(newreg, unicodeNumber);
                    });
                }
                if (isNaN(eavsExperYear)) {
                    eavsExperYear = null;
                }
                that.data.eav_s.exper_year = eavsExperYear;
            }
        },
        getValCell: function (cell) {
            var vvv = (typeof cell !== 'undefined' && typeof cell.w !== 'undefined') ? cell.w : cell.v;
            if (vvv) {
                return vvv.trim();
            }
            return vvv;
        },
        charNumberJp: function () {
            return {
                '１': 1,
                '２': 2,
                '３': 3,
                '４': 4,
                '５': 5,
                '６': 6,
                '７': 7,
                '８': 8,
                '９': 9,
                '０': 0,
            };
        },
    };

    cv.lang.init();
    $(window).load(function () {
        cv.edit.init();
        cv.import.init();
    });
})(jQuery, RKExternal, document, window);

jQuery(document).ready(function ($) {
    $('#fb-ss').on('click', function () {
        $('#feedback-modal').modal('show');
        $('#feedback-modal textarea').focus();
    });
    if ($('#form-comment-feedback').length) {
        $('form[id="form-comment-feedback"]').validate({
            rules: {
                'skc[content]': 'required',
            },
            messages: {
                'skc[content]': requiredText,
            },
            submitHandler: function (form) {
                var url = $(this).attr('action');
                var content = $('#ss-comment').val();
                var token = $('#token-comment').val();
                var employeeId = $('#employeeId').val();
                $.ajax ({
                    type: "POST",
                    url: url,
                    data: {content: content, _token: token, employeeId: employeeId},
                    success: function (response) {
                        var created_by = response.name + ' (' + response.email + ') ';
                        $('div.cmt-wrapper strong.cmt-created_by').text(created_by);
                        $('div.cmt-wrapper span.cmt-created_at').text(response.created_at);
                        $('div.cmt-wrapper .comment').text(response.content);
                        var commentHtml = $('.cmt-wrapper').html();
                        $('.grid-data-query-table').prepend(commentHtml);
                        $('#ss-comment').val('');
                        $('#add-comment-feedback').removeAttr('disabled');
                    },
                });
            },
        });
    }

    if ($('#form-review-feedback').length) {
        $('form[id="form-review-feedback"]').validate({
            rules: {
                'fb': 'required',
            },
            messages: {
                'fb': requiredText,
            },
            submitHandler: function(form) {
                form.submit();
            },
        });
    }

});

$('body').on('click', '#btn_export_cv', function(e) {
    e.preventDefault();
    var url = $(this).data('url');
    if (typeof url == 'undefined') {
        return;
    }
    var lang = $('[name="cv_view_lang"]:checked').val();
    var typeExport = $('[name="type_export"]:checked').val();
    if (lang !== 'en') {
        lang = 'ja';
    }
    var form = document.createElement('form');
    form.setAttribute('method', 'post');
    form.setAttribute('action', url);
    var params = {
        _token: siteConfigGlobal.token,
        lang: lang,
        typeExport: typeExport,
        order: $("#order").attr('data-sort'),
    };
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

function getStringli(string) {
    var arr = string.split('<br>');
    var str = '';
    for (var i = 0; i < arr.length; i++) {
        str = str + '<li>' + arr[i] + '</li>';
    }
    return str;
}
