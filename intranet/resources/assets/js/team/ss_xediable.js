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
        $('[name="cv_view_lang"][value="'+langCookie+'"]').closest('label').trigger('click');
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
        /**
         * translate value follow db
         */
        $('[data-trans-values]').each(function () {
            var domTransVal = $(this),
            dataValues = domTransVal.data('trans-values'), values;
            if (typeof dataValues !== 'object') {
                return true;
            }
            values = $.extend({}, dataValues);
            $.each(values, function (i, v) {
                if (typeof trans[lang][v] !== 'undefined') {
                    values[i] = trans[lang][v];
                }
            });
            if (domTransVal.data('editable')) {
                domTransVal.editable('option', 'source', values);
                domTransVal.editable('setValue', domTransVal.data('editable').value);
            } else {
                domTransVal.data('source', JSON.stringify(values));
            }
        });
        $('[data-db-lang]').each (function (i, v) {
            var name = $(v).data('db-lang'),
            value;
            if (typeof dbTrans[name + '_' + lang] !== 'undefined') {
                value = dbTrans[name + '_' + lang];
            } else {
                value = '';
            }
            try {
                if ($(v).data('editable')) {
                    $(v).editable('setValue', value);
                } else {
                    $(v).html(value);
                }
            } catch (e) {}
        });
    }
};
cv.lang.init();
/**
 * edit table tr
 */
cv.edit = {
    messagesValid: {},
    htmlRow: {
        checkTr: '<i class="fa fa-circle"></i>',
    },
    form: $('#form-employee-cv'),
    namesChanged: [],
    deleteData: {},
    init: function() {
        var that = this;
        that.setHtmlRowAdd();
        $.fn.editable.defaults.mode = 'inline';
        that.moreInputType();
        that.xeditable();
        $('[data-access-active]').each(function (i, v) {
            var type = $(v).data('access-active');
            if (globalVar.isAccess) {
                if (type === 'hidden') {
                    $(v).removeClass('hidden');
                }
            } else {
                $(v).remove();
            }
        });
        if (!globalVar.isAccess) {
            return true;
        }
        /**
         * event add item
         */
        that.validatorInit();
        that.submit();
        that.widthTbl = null;
        that.showAction();
        that.actionAdd();
        that.deleteRow();
    },
    /**
     * cal with tbl
     */
    calWithTbl: function () {
        var that = this;
        if (that.widthTbl) {
            return true;
        }
        that.widthTbl = {};
        $('[data-tbl-cv]').each(function (i, v) {
            var type = $(v).data('tbl-cv');
            that.widthTbl[type] = $(v).outerWidth();
        });
        that.widthTbl.window = $(window).outerWidth() - 100;
    },
    /**
     * add method this of validator
     */
    validatorInit: function () {
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
    moreInputType: function () {
        var that = this;
        var Radiolist = function(options) {
            this.init('radiolist', options, Radiolist.defaults);
        };
        $.fn.editableutils.inherit(Radiolist, $.fn.editabletypes.checklist);
        $.extend(Radiolist.prototype, {
            renderList : function() {
                var $label;
                this.$tpl.empty();
                if (!$.isArray(this.sourceData)) {
                    return;
                }
                for (var i = 0; i < this.sourceData.length; i++) {
                    $label = $('<div>').append($('<label>', {'class':this.options.inputclass})).append($('<input>', {
                        type : 'radio',
                        name : this.options.name,
                        value : this.sourceData[i].value
                    })).append($('<span>').text(this.sourceData[i].text));

                    // Add radio buttons to template
                    this.$tpl.append($label);
                }
                this.$input = this.$tpl.find('input[type="radio"]');
            },
            input2value : function() {
                return this.$input.filter(':checked').val();
            },
            str2value: function(str) {
               return str || null;
            },

            value2input: function(value) {
               this.$input.val([value]);
            },
            value2str: function(value) {
               return value || '';
            },
        });

        Radiolist.defaults = $.extend({}, $.fn.editabletypes.list.defaults, {
            /**
             @property tpl
             @default <div></div>
             **/
            tpl : '<div class="editable-radiolist"></div>',

            /**
             @property inputclass, attached to the <label> wrapper instead of the input element
             @type string
             @default null
             **/
            inputclass : '',

            name : 'defaultname'
        });
        $.fn.editabletypes.radiolist = Radiolist;

        /*
         * select2 
         */
        that.select2Option = {
            emptytext: '',
            inputclass: 'xeditor-input',
            success: function(response, newValue) {
                return that.afterInputChanged($(this), newValue);
            },
            display: function(value) {
                var html = '';
                if (value && !$.isArray(value)) {
                    value = [value];
                }
                $.each (value, function (i, v) {
                    if (typeof globalVar.tagData[v] !== 'undefined') {
                        html += $.fn.editableutils.escape(globalVar.tagData[v].text) + ', ';
                    }
                });
                html = html.slice(0, -2);
                if(html) {
                    $(this).html(html);
                } else {
                    $(this).empty(); 
                }
            },
            select2: {
                allowClear: true,
                width: 200,
                id: function(response) {
                    return response.id;
                },
                minimumInputLength: 1,
                ajax: {
                    url: function () {
                        var type = $(this).closest('[data-s2-edit]').data('s2-edit');
                        return globalVar['urlRemote' + type];
                    },
                    dataType: 'json',
                    delay: 200,
                    data: function (term, page) {
                        return {
                            q: term, // search term
                            page: page
                        };
                    },
                    processResults: function (data, page) {
                        page = page || 1;
                        return {
                            results: data.data,
                            pagination: {
                                more: (page * 10) < data.total
                            }
                        };
                    },
                    results: function (data, page) {
                        return { results: data };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { 
                    return markup; 
                }, // let our custom formatter work
                initSelection: function (element, callback) {
                    var val = $(element).val();
                    if (!val) {
                        return true;
                    }
                    val = val.split(',');
                    var result = [];
                    $.each (val, function (i, v) {
                        if (typeof globalVar.tagData[v] !== 'undefined') {
                            result.push(globalVar.tagData[v]);
                        }
                    });
                    return callback(result);
                },
                formatResult: function (item) {
                    return item.text;
                },
                formatSelection: function (item) {
                    if ($.isArray(item)) {
                        item = item[0];
                    }
                    globalVar.tagData[item.id] = item;
                    return item.text;
                },
            },
            validate: function(value) {
                return that.validate(this, value);
            },
        };
        that.select2OptionMulti = $.extend(true, {}, that.select2Option);
        that.select2OptionMulti.select2.multiple = true;
    },
    /**
     * active x-editable
     */
    xeditable: function () {
        var that = this;
        $('[data-edit-type="normal"]').editable({
            inputclass: 'xeditor-input',
            emptytext: '',
            send: 'never',
            datepicker: {
                weekStart: 1
            },
            success: function(response, newValue) {
                return that.afterInputChanged($(this), newValue);
            },
            validate: function(value) {
                return that.validate(this, value);
            },
        });
        /**
         * select2
         */
        $('[data-edit-type="select2"]').editable(that.select2Option);
        $('[data-edit-type="select2-multi"]').editable(that.select2OptionMulti);
        // radio same
        $('[data-edit-type="radiolist"]').editable({
            emptytext: '',
            source: [
                  {value: 1, text: '1'},
                  {value: 2, text: '2'},
                  {value: 3, text: '3'},
                  {value: 4, text: '4'},
                  {value: 5, text: '5'}
            ],
            display: function(value) {
                var dom = $(this);
                if (value && $.isArray(value)) {
                    value = value;
                }
                if (value == dom.data('flag-val')) {
                    dom.html(that.htmlRow.checkTr);
                } else {
                    dom.empty();
                }
            },
            success: function(response, newValue) {
                var dom = $(this),
                name = dom.data('name'),
                domSiblings = $('[data-name="'+name+'"]'),
                datakey = 'editable';
                domSiblings.each(function(i, v){
                    var domRadio = $(this);
                    domRadio.editable('setValue', newValue);
                    if (domRadio.data('flag-val') == newValue) {
                        domRadio.html(that.htmlRow.checkTr);
                    } else {
                        domRadio.html('');
                    }
                    
                });
                return that.afterInputChanged($(this), newValue);
            },
        });
    },
    /**
     * subit form
     */
    submit: function () {
        var that = this;
        $('[data-btn-submit]').click(function () {
            var saveTypeSubmit = $(this).data('btn-submit');
            $('[data-btn-submit]').prop('disabled', true);
            $('[data-edit-dom="submit"]').editable('submit', {
                data: {
                    _token: siteConfigGlobal.token,
                    save_type: saveTypeSubmit,
                    cv_view_lang: $('input[name="cv_view_lang"]:checked').val(),
                    remove: that.deleteData,
                },
                url: that.form.attr('action'), 
                ajaxOptions: {
                    dataType: 'json',
                },
                savenochange: false,
                success: function (response) {
                    if (typeof response.reload !== 'undefined' && ''+response.reload === '1') {
                        window.location.reload();
                    }
                },
                error: function (error) {
                    if (typeof error.message !== 'undefined' && error.message) {
                        RKExternal.notify(error.message, false);
                    } else {
                        RKExternal.notify('System error', false);
                    }
                    $('[data-btn-submit]').prop('disabled', false);
                },
                soda: {
                    filterValue: function (data) {
                        if (data && that.namesChanged.indexOf(data.options.name) === -1) {
                            return true;
                        }
                    }
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
    afterInputChanged: function (dom, newValue) {
        var that = this,
            col = dom.data('db-lang'),
            name = dom.data('editable').options.name;
        // change value of db lang - switch lang
        if (col) {
            dbTrans[col + '_' + cv.lang.getLangCur()] = newValue;
        }
        if (that.namesChanged.indexOf(name) === -1) {
            that.namesChanged.push(name);
        }
        // set default value for select2
        if ($.isArray(newValue) && !newValue.length && dom.data('edit-value-default')) {
            return {
                newValue: ['' + dom.data('edit-value-default')]
            };
        }
        return true;
    },
    validate: function (data, value) {
        var that = this,
        dom = $(data),
        type = dom.data('valid-type'),
        langCur = cv.lang.getLangCur();
        if (!type) {
            return false;
        }
        try {
            if (typeof type === 'string') {
                type = JSON.parse(type);
            }
        } catch (e){
            return false;
        }
        var message;
        $.each (type, function (key, param) {
            if (typeof $.validator.methods[key] !== 'function') {
                return true;
            }
            if ($.validator.methods[key](value, dom[0], param)) {
                return true;
            }
            if (typeof that.messagesValid[langCur] === 'undefined'
                || typeof that.messagesValid[langCur][key] === 'undefined'
            ) {
                message = 'error';
            } else {
                message = that.messagesValid[cv.lang.getLangCur()][key];
            }
            if (typeof message === "function") {
                message = message.call(this, param);
            }
            return false;
        });
        if (message) {
            return message;
        }
    },
    /**
     * show action row
     */
    showAction: function () {
        var that = this;
        $(document).mouseup(function (e) {
            var row = $('[data-id][data-type]'),
            editable = $('[data-id][data-type] a.editable'),
            editableContainer = $('.editable-container'),
            rowAction = $('[data-row-action]');
            // click editable => not action
            if (editable.is(e.target) ||
                editable.has(e.target).length !== 0 ||
                editableContainer.is(e.target) ||
                editableContainer.has(e.target).length !== 0
            ) {
                rowAction.addClass('hidden');
                $('[data-tbl-cv]').removeAttr('style');
                $('[data-tbl-res]').removeAttr('style');
                return true;
            }
            that.calWithTbl();
            rowAction.addClass('hidden');
            if (!row.is(e.target) // if the target of the click isn't the container...
                && row.has(e.target).length === 0 // ... nor a descendant of the container
            ) {
                $('[data-tbl-cv]').removeAttr('style');
                $('[data-tbl-res]').removeAttr('style');
                return true;
            }
            var rowActive = $(e.target).closest('[data-id][data-type]'),
            id = rowActive.data('id'),
            type = rowActive.data('type'),
            tblActive = rowActive.closest('[data-tbl-cv]'),
            tblType = tblActive.data('tbl-cv'),
            tblWrapper = tblActive.closest('[data-tbl-res]'),
            tblWrapperAnother = tblWrapper.siblings();
            rowActive = $('[data-id="'+id+'"][data-type="'+type+'"]');
            rowAction.addClass('hidden');
            rowActive.find('[data-row-action]').removeClass('hidden');
            tblActive.width(that.widthTbl[tblType]);
            var widthWrapper = that.widthTbl[tblType] + 50;
            if (widthWrapper > that.widthTbl.window) {
                widthWrapper = that.widthTbl.window;
            }
            tblWrapper.width(widthWrapper);
            tblWrapperAnother.width(that.widthTbl.window - widthWrapper);
        });
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
        var that = this,
            newId = 1;
        $(document).on('click', '[data-btn-row-add]', function (event) {
            event.preventDefault();
            var domThis = $(this),
            type = domThis.data('btn-row-add');
            if (typeof that.htmlRow[type] === 'undefined' || !that.htmlRow[type]) {
                return true;
            }
            var newRow = $(that.htmlRow[type].replace(/(\-9999)/g, 'new' + newId)),
            tr = domThis.closest('tr');
            newId++;
            var lastType = tr.data('btn-last');
            if (lastType) {
                if (lastType === 'before') {
                    tr.before(newRow);
                } else {
                    tr.after(newRow);
                }
            } else {
                tr = $('tr[data-id="'+tr.data('id')+'"][data-type="'+tr.data('type')+'"]:last')
                tr.after(newRow);
            }
            that.xeditable();
        });
    },
    /**
     * delete row item: project, skill
     */
    deleteRow: function () {
        var that = this;
        $(document).on('click', '[data-btn-action="delete"]', function () {
            var dom = $(this).closest('[data-id][data-type]'),
            id = dom.data('id'),
            type = dom.data('type');
            if (id && type && !isNaN(id) && parseInt(id) > 0) {
                if (typeof that.deleteData[type] === 'undefined') {
                    that.deleteData[type] = [];
                }
                that.deleteData[type].push(id);
            }
            $('[data-id="'+id+'"][data-type="'+type+'"]').remove();
        });
    },
};
cv.edit.init();
})(jQuery, RKExternal, document, window);
