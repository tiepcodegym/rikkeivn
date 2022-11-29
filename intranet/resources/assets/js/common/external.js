(function ($, document, window) {
var RKExternal = {};
var RKfunction = typeof RKfuncion === 'object' ? RKfuncion : {};
/**
 * notifycation popup
 * 
 * @param {type} message
 * @param {type} type: success, info, warning, danger
 * @return {unresolved}
 */
RKExternal.notify = function(message, type, position) {
    if (typeof type === 'undefined' || type === true || type === null) {
        type = 'success';
    } else if (type === false) {
        type = 'warning';
    }
    position = $.extend({
        from : 'top',
        align : 'right', // left, center
    }, position);
    var messageList = '';
    if (typeof message === 'undefined' || !message) {
        messageList = type;
    } else if (Array.isArray(message)) {
        if (message.length > 1) {
            messageList += '<ul>';
            $.each(message, function (i, v) {
                messageList += '<li>'+ v +'</li>';
            });
            messageList += '</ul>';
        } else if (message.length === 1) {
            messageList += message[0];
        } else {
            messageList = type;
        }
    } else {
        messageList = message;
    }
    $.notifyClose();
    return $.notify({
        message: messageList
    },{
        type: type,
        z_index: 2000,
        placement: {
            from : position.from,
            align : position.align,
        },
        delay: typeof position.delay !== 'undefined' ? position.delay : 5000
    });
};

/**
 * progess bar 
 */
RKExternal.progressBar = {
    bar: null,
    timeout: null,
    step: 100,
    process: false,
    /**
     * start progress bar
     */
    start: function() {
        var __this = this;
        if (__this.process) {
            return true;
        }
        __this.process = true;
        if (!this.bar) {
            if (!$(".progressbar").length) {
                $('body').append('<div class="progressbar ui-progressbar-top"></div>');
            }
            __this.bar = $(".progressbar");
            __this.bar.progressbar({
                value: 0,
                max: 100,
                complete: function() {
                    __this.end();
                },
                create: function() {
                    __this.progress();
                }
            });
        } else {
            __this.bar.progressbar('value', 0);
            __this.bar.show();
            __this.progress();
        }
    },
    /**
     * end progress bar
     */
    end: function() {
        var __this = this;
        __this.process = false;
        if (!__this.bar) {
            return true;
        }
        if (__this.bar.progressbar('value') !== 100) {
            __this.bar.progressbar('value', 100);
        }
        if (__this.timeout) {
            clearTimeout(__this.timeout);
        }
        setTimeout(function() {
            __this.bar.hide();
        }, 700);
        
    },
    /**
     * progress change value
     */
    progress: function() {
        var val = this.bar.progressbar('value') || 0,
            newVal = val + Math.floor( Math.random() * 10 ),
            __this = this;
        if (newVal < 70) {
            __this.bar.progressbar("value", newVal);
            __this.timeout = setTimeout(function() {
                __this.progress();
            }, __this.step);
        } else if (newVal < 95) {
            __this.bar.progressbar("value", val + 0.5);
            __this.timeout = setTimeout(function() {
                __this.progress();
            }, __this.step);
        } else {
            clearTimeout(__this.timeout);
        }
    },
    setStep: function(step) {
        this.step = step;
        return this;
    }
};

/**
 * submit form ajax
 *
 * flag:
 *      is use validate: data-flag-valid
 * option dom:
 *      data-cb-success="namefunction"
 *      data-cb-error="namefunction"
 *      data-cb-complete="namefunction"
 *      data-cb-before-submit="namefunction"
 *
 * response:
 *      reload: 1|0
 *      refresh: string url
 *      popup: 1|0
 *      status: 1|0
 *      message: string
 */
RKExternal.formAjax = {
    flagDom: '[data-form-submit="ajax"]',
    flagButton: '[data-btn-submit="ajax"]',
    flagFormFile: '[data-form-file="1"]',
    /**
     * inir form submit by ajax
     * run when reload page
     */
    init: function init() {
        var __this = this;
        $(__this.flagDom + ' [type="submit"]').prop('disabled', false);
        $(document).on('submit', __this.flagDom, function (event) {
            event.preventDefault();
            __this.elementSubmit($(this), 1);
        });
        $(document).on('click', __this.flagButton, function (event) {
            event.preventDefault();
            __this.elementSubmit($(this), 2);
        });
    },
    elementSubmit: function (dom, type) {
        var __this = this;
        if (''+dom.data('flag-valid') === '1') {
            if (!dom.valid()) {
                dom.find('[type=submit]').removeAttr('disabled');
                return true;
            }
        }
        if (dom.data('running')) {
            return true;
        }
        if (dom.data('submit-noti')) {
            RKExternal.confirm(dom.data('submit-noti'), function(response) {
                if (response.result) {
                    __this.execSubmit(dom, type);
                }
            });
        } else {
            __this.execSubmit(dom, type);
        }
    },
    execSubmit: function(dom, type) {
        var __this = this,
            loadingSubmit = dom.find('.loading-submit'),
            loadingHiddenSubmit = dom.find('.loading-hidden-submit'),
            callbackBeforeSubmit = dom.data('cb-before-submit'),
            callbackGetFormData = dom.data('cb-get-form-data'),
            btnSubmit, dataForm;
        if (type === 2) {
            btnSubmit = dom;
            dataForm = {
                _token: siteConfigGlobal.token,
            };
        } else {
            btnSubmit = dom.find('[type=submit]:not(.no-disabled)');
            if (callbackGetFormData && typeof RKExternal[callbackGetFormData] === 'function') {
                dataForm = RKExternal[callbackGetFormData](dom);
                if (dataForm === false) {
                    btnSubmit.prop('disabled', false);
                    return true;
                }
            } else {
                dataForm = __this.getDataForm(dom);
            }
        }
        if (callbackBeforeSubmit && typeof RKExternal[callbackBeforeSubmit] === 'function') {
            RKExternal[callbackBeforeSubmit](dataForm, dom);
        }
        dom.data('running', true);
        btnSubmit.prop('disabled', true);
        loadingSubmit.removeClass('hidden');
        loadingHiddenSubmit.addClass('hidden');
        var methodType = dom.attr('method');
        if (!methodType) {
            methodType = 'post';
        }
        var ajaxData = {
            url: dom.attr('action'),
            type: methodType,
            dataType: 'json',
            data: dataForm,
            success: function success(response) {
                if (typeof response.reload !== 'undefined' && ''+response.reload === '1') {
                    window.location.reload();
                    return true;
                }
                //error
                if (typeof response.status === 'undefined' || !response.status) {
                    RKExternal.notify(response.message, false);
                    var callbackError = dom.data('cb-error');
                    if (callbackError && typeof RKExternal[callbackError] === 'function') {
                        RKExternal[callbackError](response, dom);
                    }
                    return true;
                }
                if (typeof response.redirect !== 'undefined' && response.redirect) {
                    window.location.href = response.redirect;
                    return true;
                }
                if ((typeof response.popup === 'undefined' || ''+response.popup === '1') &&
                    typeof response.message !== 'undefined' && response.message
                ) {
                    RKExternal.notify(response.message, true, {
                        delay: typeof dom.data('delay-noti') !== 'undefined' ? dom.data('delay-noti') : 10000,
                    });
                }
                if (response.urlReplace) {
                    RKExternal.urlReplace(response.urlReplace);
                }
                var callbackSuccess = dom.data('cb-success');
                if (callbackSuccess && typeof RKExternal[callbackSuccess] === 'function') {
                    RKExternal[callbackSuccess](response, dom);
                }
            },
            error: function error(response) {
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
                var callbackError = dom.data('cb-error');
                if (callbackError && typeof RKExternal[callbackError] === 'function') {
                    RKExternal[callbackError](response, dom);
                }
            },
            complete: function complete(response) {
                if ((typeof response.reload !== 'undefined' && ''+response.reload === '1') ||
                    (typeof response.redirect !== 'undefined' && response.redirect) ||
                    (typeof response.responseJSON === 'object' && response.responseJSON.reload)
                ) {
                    return true;
                }
                dom.data('running', false);
                btnSubmit.prop('disabled', false);
                loadingSubmit.addClass('hidden');
                loadingHiddenSubmit.removeClass('hidden');
                var callbackDone = dom.data('cb-complete');
                if (callbackDone && typeof RKExternal[callbackDone] === 'function') {
                    RKExternal[callbackDone](response, dom);
                }
            },
        };
        if (dom.data('form-file')) {
            ajaxData.contentType = false;
            ajaxData.processData = false;
        }
        $.ajax(ajaxData);
    },
    /**
     * get data of form
     *
     * @param {object dom} form
     * @return {String | object FormData}
     */
    getDataForm: function(form) {
        if (!form.data('form-file')) {
            return form.serialize();
        }
        var formData = new FormData();
        form.find('input:not([disabled]), select:not([disabled]), textarea:not([disabled])')
            .each(function (i, v) {
            var type = $(v).attr('type'),
                name = $(v).attr('name'),
                value = $(v).val();
            switch (type) {
                case 'file':
                    if (v.files.length === 1) {
                        formData.append(name, v.files[0]);
                    } else if (v.files.length > 1) {
                        formData.append(name, v.files);
                    }
                    break;
                case 'checkbox':
                case 'radio':
                    if ($(v).is(':checked')) {
                        formData.append(name, value);
                    }
                    break;
                default:
                    formData.append(name, value);
                    break;
            }
        });
        return formData;
    },
};

/**
 * get params from url
 *
 * @return {object}
 */
RKExternal.params = function () {
    var params = {};
    decodeURIComponent(window.location.search).replace(/[?&]+([^=&]+)=([^&]*)/gi, function (str, key, value) {
        params[key] = value;
    });
    return params;
};

/**
 * replace url
 *
 * @param string url
 * @param {object} params
 * @param {boolean} isMergeParams
 * @return {String}
 */
RKExternal.urlReplace = function (url, params, isMergeParams) {
    if (typeof url === 'string') {
        window.history.pushState(null, null, url);
        return true;
    }
    if (typeof isMergeParams !== 'undefined' && isMergeParams) {
        params = $.extend(RKExternal.params(), params);
    }
    var href = window.location.href,
        paramsIndex = href.indexOf('?'),
        url;
    if (paramsIndex === -1) {
        url = href + '?' + $.param(params);
    } else {
        url = href.substr(0, paramsIndex) + '?' + $.param(params);
    }
    window.history.pushState(null, null, url);
};

/**
 * replace url with encode
 *
 * @param string url
 * @param {object} params
 * @param {boolean} isMergeParams
 * @return {String}
 */
RKExternal.urlReplaceEncode = function (params, isMergeParams) {
    if (typeof isMergeParams !== 'undefined' && isMergeParams) {
        params = $.extend(RKExternal.params(), params);
    }
    var href = window.location.href,
        paramsIndex = href.indexOf('?'),
        url, paramEncode = '';
    $.each(params, function (k, v) {
        paramEncode += k + '=' + v + '&';
    });
    paramEncode = encodeURIComponent(paramEncode.slice(0, -1));
    if (paramsIndex === -1) {
        url = href + '?' + paramEncode;
    } else {
        url = href.substr(0, paramsIndex) + '?' + paramEncode;
    }
    window.history.pushState(null, null, url);
};

/**
 * confirm popup
 * @param {string} msg
 * @param {function} callback: response{result, hide}
 * @param {object} option : noHideAutoYes
 */
RKExternal.confirm = function (msg, callback, option) {
    option = typeof option === 'object' ? option : {};
    option = $.extend({autoHide: true}, option);
    var __this = RKExternal.confirm,
        modalConfirm = $('#modal-confirm-submit');
    if (!modalConfirm.length) {
        modalConfirm = $('<div class="modal" id="modal-confirm-submit" data-backdrop="static" data-keyboard="false">'
            + '<div class="modal-dialog"><div class="modal-content">'
            + '<div class="modal-header"><h4 class="modal-title">' + textConfirm + '</h4></div>'
            + '<div class="modal-body"><p data-mconfirm="body"></p></div>'
            + '<div class="modal-footer">'
            + '<button type="button" class="btn btn-default btn-confirm-no pull-left" onclick="RKExternal.confirm.no()">' + confirmNo + '</button>'
            + '<button type="button" class="btn btn-primary btn-confirm-yes" onclick="RKExternal.confirm.yes()">' + confirmYes +'</button>'
            + '</div></div></div></div>');
        $('body').append(modalConfirm);
        __this.hide = function() {
            $('#modal-confirm-submit').modal('hide');
        };
    }
    $('#modal-confirm-submit').find('button.btn-confirm-yes')
        .removeClass('btn-primary')
        .removeClass('btn-danger');
    if (option.btnOkColor) {
        $('#modal-confirm-submit').find('button.btn-confirm-yes').addClass(option.btnOkColor);
    } else {
        $('#modal-confirm-submit').find('button.btn-confirm-yes').addClass('btn-primary');
    }
    __this.yes = function() {
        if (option.autoHide) {
            __this.hide();
        }
        if (typeof callback !== 'function') {
            return true;
        }
        return callback({
            result: true,
            hide: __this.hide,
        });
    };
    if (typeof __this.no !== 'function') {
        __this.no = function() {
            if (option.autoHide) {
                __this.hide();
            }
            if (typeof callback !== 'function') {
                return false;
            }
            return callback({
                result: false,
                hide: __this.hide,
            });
        };
    }
    if (typeof RKfunction.general !== 'undefined' &&
        typeof RKfunction.general.modalBodyPadding === 'function'
    ) {
        RKfunction.general.modalBodyPadding();
    }
    modalConfirm.find('[data-mconfirm="body"]').html(msg);
    modalConfirm.modal('show');
};

/**
 * string to url and replace search in tail string to replaceBy
 *
 * @param string link
 * @param string search
 * @param string replaceBy
 * @returns {string}
 */
RKExternal.stringToUrlReplace = function(link, search, replaceBy) {
    search = '' + search;
    replaceBy = '' + replaceBy;
    var lastIndex = link.lastIndexOf(search),
        lengthSearch = search.length;
    if (lastIndex < 0) {
        return link;
    }
    return link.substr(0, lastIndex) + replaceBy + link.substr(lastIndex + lengthSearch);
};

/**
 * js check upload file
 */
RKExternal.uploadFile = {
    fgWrapper: '[data-flag-attach="file-wrapper"]',
    fgInput: '[data-flag-attach="input"]',
    fgSize: '[data-flag-attach="file-size"]',
    fgName: '[data-flag-attach="file-name"]',
    fgRemove: '[data-flag-attach="file-remove"]',
    fgNameShow: '[data-flag-attach="name-show"]',
    fgInputRemove: '[data-flag-attach="input-remove"]',
    fgInputAs: '[data-flag-attach="input-as"]',
    init: function(option) {
        var __this = this;
        var optionDefault = {
            isCheckType: false,
            messageSize: 'File size is large',
            messageType: 'File type dont allow',
            size: 5, //MB
        };
        __this.option = $.extend(optionDefault, option);
        //change file
        $(__this.fgWrapper + ' ' + __this.fgInput).change(function () {
            __this.readFileUrl($(this).closest(__this.fgWrapper), $(this));
        });
        // remove file
        $(__this.fgWrapper + ' ' + __this.fgRemove).click(function (event) {
            event.preventDefault();
            __this.removeFile($(this).closest(__this.fgWrapper));
        });
    },
    /**
     * read file
     *
     * @param {dom} domWrapper
     * @param {dom} domInput
     */
    readFileUrl: function (domWrapper, domInput) {
        var __this = this;
        if (!domInput[0].files || !domInput[0].files[0]) {
            if (domWrapper.find(__this.fgName).text().trim()) {
                domWrapper.find(__this.fgNameShow).removeClass('hidden');
            }
            domWrapper.find(__this.fgInputAs).val('');
            return true;
        }
        var fileUpload = domInput[0].files[0],
            domSize = domWrapper.find(__this.fgSize);
        if(__this.option.isCheckType && $.inArray(fileUpload.type, __this.option.isCheckType) < 0) {
            domInput.val('');
            domWrapper.find(__this.fgInputAs).val('');
            alert(__this.option.messageType);
            domSize.val('');
            return true;
        }
        if (fileUpload.size / 1000 / 1000 > __this.option.size) {
            domInput.val('');
            alert(__this.option.messageSize);
            domSize.val('');
            domWrapper.find(__this.fgInputAs).val('');
            return true;
        }
        domWrapper.find(__this.fgInputAs).val('1');
        domWrapper.find(__this.fgNameShow).addClass('hidden');
        domSize.val(Intl.NumberFormat().format((fileUpload.size / 1000).toFixed(2)));
    },
    /**
     * remove file
     *
     * @param {dom} domWrapper
     */
    removeFile: function(domWrapper) {
        var __this = this;
        domWrapper.find(__this.fgNameShow).hide();
        domWrapper.find(__this.fgInputRemove).val(1);
        domWrapper.find(__this.fgInputAs).val('');
    },
};

/**
 * autocomplete ui
 */
RKExternal.autoComplete = {
    fgDom: '[data-autocomplete-dom="true"]',
    dataUrlRemote: 'ac-url',
    init: function (option) {
        var that = this;
        if (!$(that.fgDom).length) {
            return true;
        }
        var optionDefault = {
            minLength: 1,
            beforeRemote: null, // function return false|object
        };
        option = $.extend(optionDefault, option);
        $(that.fgDom).autocomplete({
            minLength: option.minLength,
            source: function (request, response) {
                var domInput = this.element,
                    paramsExtend = {},
                    params = {
                        'term': request.term,
                    };
                if (typeof option.beforeRemote === 'function') {
                    paramsExtend = option.beforeRemote(request, response, domInput);
                    if (!paramsExtend) {
                        return response([]);
                    }
                    if (typeof paramsExtend.params === 'object') {
                        params = $.extend(params, paramsExtend.params);
                    }
                }
                $.ajax({
                    url: this.element.data(that.dataUrlRemote),
                    type: 'GET',
                    dataType: 'json',
                    data: params,
                    success: function (res) {
                        return response(res.data);
                    },
                });
            },
            select: function (event, ui) {
                var inputDom = $(this);
                inputDom.data('item-id', ui.item.id);
                if (typeof option.afterSelected === 'function') {
                    option.afterSelected(ui.item, inputDom);
                }
            },
        });
    },
}

RKExternal.select2 = {
    fgSelect: '[data-select2-dom="1"]',
    dataRemote: 'select2-url',
    dataHasSearch: 'select2-search',
    option: {},
    init: function (option) {
        var __this = this;
        option = $.extend({}, option);
        __this.option = option;
        if (option.enforceFocus) {
            try {
                $.fn.modal.Constructor.prototype.enforceFocus = function(){};
            } catch(e) {}
        }
        $(__this.fgSelect).each(function(){
            var dom = $(this), optionMore;
            if (dom.data('select2')) {
                return true;
            }
            if (dom.attr('placeholder')) {
                optionMore = $.extend({
                    placeholder: dom.attr('placeholder')
                }, option);
            } else {
                optionMore = $.extend({}, option);
            }
            if ($(this).data(__this.dataRemote)) {
                __this.execRemote(dom, optionMore);
            } else {
                __this.exec(dom, optionMore);
            }
        });
    },
    exec: function(dom, option) {
        var __this = this;
        if (!dom.data(__this.dataHasSearch)) {
            option = $.extend({
                minimumResultsForSearch: Infinity
            }, option);
        }
        if (dom.data('select2-multi-trim') == '1') {
            option.templateSelection = __this.formatSelectedBasic;
            option.escapeMarkup = function (markup) { 
                return markup; 
            };
        }
        dom.select2(option);
        // cut text
        if (dom.data('select2-multi-trim') == '1') {
            dom.on('select2:select', function () {
                __this.trimText(dom);
            });
            dom.on('select2:unselect', function () {
                __this.trimText(dom);
            });
        }
    },
    /**
     * trim text selected
     */
    trimText: function (dom) {
        var text, domSelected;
        dom.siblings('.select2.select2-container')
        .find('ul.select2-selection__rendered > li.select2-selection__choice').each(function (i, v) {
            domSelected = $(v).find('[data-select2-result="selected"]');
            text = domSelected.text().trim();
            domSelected.html(text).attr('title', text);
        });
    },
    execRemote: function(dom, option) {
        /*
         * response need paginate of laravel
         *
         *  {
         *      data:[
         *          {id: 1, text: "show"},
         *          {id: 1, text: "show"}
         *      ],
         *      total: 2
         */
        var __this = this;
        option = $.extend({
                delay: 500,
                minimumInputLength: 2,
                allowClear: false,
            }, option);
        dom.select2({
            id: function(response){ 
                return response.id;
            },
            placeholder: option.placeholder,
            minimumInputLength: option.minimumInputLength,
            allowClear: option.allowClear,
            ajax: {
                url: dom.data('select2-url'),
                dataType: 'json',
                delay: option.delay,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * 10) < data.total
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { 
                return markup; 
            }, // let our custom formatter work
            templateResult: __this.formatReponse, // omitted for brevity, see the source of this page
            templateSelection: __this.formatReponesSelection // omitted for brevity, see the source of this page
        });
    },
    formatReponse: function (response) {
        if (response.loading) {
            return response.text;
        }
        return "<div class='select2-result-repository clearfix'>" +
            "<div class='select2-result-repository__title'>" + htmlEntities(response.text) + "</div>" +
            "</div>";
    },
    formatReponesSelection: function (response, domSpan) {
        if (typeof RKExternal.select2.option.afterSelected === 'function') {
            RKExternal.select2.option.afterSelected(
                response, 
                $(domSpan).closest('.select2.select2-container')
                    .prev('select[data-flag-dom="select2"]')
            );
        }
        return htmlEntities(response.text);
    },
    formatSelectedBasic: function (response) {
        return '<span data-select2-result="selected">' + response.text + '</span>';
    }
};

RKExternal.simple = {
    /**
     * cut text follow length text
     */
    textShort: function (domWrapper, isWindowLoad) {
        var dom,
        lengDe = 50,
        lengDom, textDom;
        if (typeof isWindowLoad === 'undefined') {
            isWindowLoad = true;
        }
        if (domWrapper && domWrapper.length) {
            dom = domWrapper.find('[data-text-short]');
        } else {
            dom = $('[data-text-short]');
        }
        if (!dom.length || !isWindowLoad) {
            return true;
        }
        dom.each(function (i, v) {
            lengDom = $(v).data('text-short');
            if (!lengDom || isNaN(lengDom)) {
                lengDom = lengDe;
            }
            if ($(v).attr('title')) {
                return true;
            }
            textDom = $(v).text().trim();
            if (textDom.length <= lengDom) {
                $(v).removeAttr('title');
                return true;
            }
            $(v).text(textDom.substr(0, lengDom - 3) + '...');
            $(v).attr('title', textDom);
        });
    },
    /**
     * cut text follow height text
     */
    textHeight: function (domWrapper) {
        var dom,
        heightDe = 100, //px
        heightDom, textDom, withDom, flagHeight, flagHeightCheched = [];
        if (!domWrapper || !domWrapper.length) {
            domWrapper = $('body');
        }
        dom = domWrapper.find('[data-text-height]');
        if (!dom.length) {
            return true;
        }
        dom.each(function () {
            var ele = $(this);
            textDom = ele.data('text-flag-org') || ele.text().trim();
            ele.text('');
            heightDom = ele.data('text-height');
            flagHeight = ele.data('flag-height');
            if (typeof flagHeight !== 'undefined' && flagHeightCheched.indexOf(flagHeight) === -1) {
                domWrapper.find('[data-text-height][data-flag-height="'+flagHeight+'"]').each (function () {
                    var domFlagHeight = $(this);
                    domFlagHeight.data('text-flag-org', domFlagHeight.text());
                    domFlagHeight.text('');
                });
                flagHeightCheched.push(flagHeight);
            }
            withDom = ele.data('fix-width') ? ele.data('fix-width') : ele.outerWidth();
            if (!heightDom || isNaN(heightDom)) {
                heightDom = heightDe;
            }
            ele.text(textDom);
            ele.data('text-flag-org', null);
            if (ele.outerHeight() <= heightDom && ele.outerWidth() <= withDom) {
                ele.removeAttr('style');
                ele.removeAttr('title');
                return true;
            }
            ele.width(withDom);
            if (ele.outerHeight() <= heightDom) {
                ele.removeAttr('title');
                return true;
            }
            var textCur = '', i, textLength = textDom.length;
            for (i = 0; i < textLength; i += 5) {
                textCur += textDom.substr(i, 5);
                ele.text(textCur);
                if (ele.outerHeight() > heightDom) {
                    break
                }
            }
            textCur += '...';
            ele.text(textCur);
            ele.attr('title', textDom);
        });
    },
    /**
     * cut text follow line
     * 
     * data-text-line="1" // default 1
     */
    cutTextLine: function (dom, line) {
        if (!dom || !dom.length) {
            dom = $('body [data-text-line]');
        }
        if (!line) {
            line = 1;
        }
        if (!dom.length) {
            return true;
        }
        dom.each(function () {
            var ele = $(this);
            if (ele.attr('title')) {
                return true;
            }
            var textDom = ele.data('text-flag-org') || ele.text().trim(),
            lineCut = ele.data('text-line') || line,
            heightLine = parseFloat(ele.css('line-height')) * lineCut;
            if (ele.outerHeight() <= heightLine) {
                return true;
            }
            var textCur = '', i, textLength = textDom.length;
            for (i = 0; i < textLength; i += 5) {
                textCur += textDom.substr(i, 5);
                ele.text(textCur);
                if (ele.outerHeight() > heightLine) {
                    textCur = textCur.slice(0, -8);
                    break
                }
            }
            textCur += '...';
            ele.text(textCur);
            ele.attr('title', textDom);
        });
    },
    /**
     * cal period time
     *
     * @param {object} start
     * @param {object} end
     * @return {array}
     */
    diffTimeYM: function (start, end) {
        if (typeof moment !== 'function') {
            alert('Miss lib moment.js');
            return true;
        }
        if (typeof start !== 'object') {
            start = moment(start);
        }
        if (!end) {
            end = moment(); // now
        } else if (typeof end !== 'object') {
            end = moment(end);
        }
        if (end.isBefore(start)) {
            return {
                Y: 0,
                M: 0,
            };
        }
        end.add(1, 'd');
        var result = {};
        result.Y = end.diff(start, 'Y');
        result.M = end.diff(start, 'M');
        result.M = result.M - result.Y * 12;
        var dayDiff = end.diff(start, 'd') - result.Y * 365 - result.M * 30;
        if (dayDiff > 15) {
            result.M++;
        }
        return result;
    },
    /**
     * format date time
     *
     * @param {string} format
     * @param {object} dateTime
     * @returns {String}
     */
    formatDate: function (format, dateTime) {
        var that = this;
        if (typeof dateTime !== 'object') {
            dateTime = new Date();
        }
        if (typeof format !== 'string') {
            format = 'Y-m-d H:i:s';
        }
        return format.replace(/Y/gi, dateTime.getFullYear())
            .replace(/m/gi, that.lengtwo(dateTime.getMonth() + 1))
            .replace(/d/gi, that.lengtwo(dateTime.getDate()))
            .replace(/H/gi, that.lengtwo(dateTime.getHours()))
            .replace(/i/gi, that.lengtwo(dateTime.getMinutes()))
            .replace(/s/gi, that.lengtwo(dateTime.getSeconds()));
    },
    /**
     * convert string length 1 to lenth 2
     *
     * @param {type} n
     * @returns {String}
     */
    lengtwo: function (n) {
        if (n < 10) {
            return '0' + n;
        }
        return n;
    },
    /**
     * get file split: path, name, ext from path
     *
     * @param {string} filePath
     * @returns {Array}
     */
    getFileSplitFromPath: function (filePath) {
        var path, ext, name,
        pos = filePath.lastIndexOf('/');
        if (pos === -1) {
            path = '';
            name = filePath;
        } else {
            path = filePath.substr(0, pos + 1);
            name = filePath.substr(pos + 1);
        }
        pos = name.lastIndexOf('.');
        if (pos === -1) {
            ext = '';
        } else {
            ext = name.substr(pos + 1);
            name = name.substr(0, pos);
        }
        return [path, name, ext];
    },
    /**
     * random function
     *  Adapted from http://indiegamr.com/generate-repeatable-random-numbers-in-js/
     *
     * @param {type} min
     * @param {type} max
     * @returns {Number}
     */
    rand: function (min, max, isRound) {
        var that = this,
        seed = that.seed;
        min = typeof min === 'undefined' ? 0 : min;
        max = typeof max === 'undefined' ? 1 : max;
        that.seed = (seed * 9301 + 49297) % 233280;
        seed = min + (that.seed / 233280) * (max - min);
        if (isRound) {
            return Math.round(seed);
        }
        return seed;
    },
};
// init seed for random
RKExternal.simple.seed = Date.now();
/**
 * fix width px for table responsive
 */
RKExternal.tblWidth = {
    fDom: '[data-tbl-fix="width"]',
    init: function (isShowHide) {
        var that = this;
        if (typeof isShowHide === 'undefined') {
            isShowHide = true;
        }
        if (isShowHide) {
            that.tdHide();
        }
        $(that.fDom).each (function () {
            var dom = $(this);
            if (dom.data('fixedWidth')) {
                return true;
            }
            dom.data('fixedWidth', true);
            that.fixItem(dom);
        });
        if (isShowHide) {
            that.tdShow();
        }
    },
    tdHide: function () {
        var that = this;
        $(that.fDom + ' > tbody > tr > td').children().addClass('tblWidth-hide');
    },
    tdShow: function () {
        var that = this;
        $(that.fDom + ' > tbody > tr > td').children().removeClass('tblWidth-hide');
    },
    fixItem: function (dom) {
        var that = this,
            widthTbl = dom.outerWidth(),
            colGroup = dom.children('colgroup').children('col'),
            sortNo = [],
            colDom, flagWidthColDom;
        if (!colGroup.length) {
            return true;
        }
        colGroup.each (function (i, v) {
            if ($(v).data('priority') && !isNaN($(v).data('priority'))) {
                sortNo.push(parseInt($(v).data('priority')));
            }
        });
        sortNo = sortNo.sort();
        $.each(sortNo, function(i, v){
            colDom = dom.children('colgroup').children('col[data-priority="'+v+'"]');
            flagWidthColDom = colDom.data('percent');
            if (!flagWidthColDom || isNaN(flagWidthColDom)) {
                return true;
            }
            colDom.width(widthTbl * flagWidthColDom / 100);
        });
        that.colRemainItem(dom, colGroup, widthTbl);
        that.colFixItem(dom);
    },
    /**
     * fix col column of body table, or fix block any in table
     */
    colFixItem: function (dom) {
        var colFix = dom.children('colgroup').children('col[data-col-fix]');
        if (!colFix.length) {
            return true;
        }
        colFix.each (function () {
            var colDom = $(this),
            widthColFix = colDom.outerWidth(),
            colFixValue = colDom.data('col-fix'),
            index = colDom.index(),
            tdIndex,
            widthTdIndex;
            if (colFixValue) { // fix width any block in table
                tdIndex = dom.find('[data-col-fixed="'+colFixValue+'"]');
            } else { // fix td or thead in table
                tdIndex = dom.find('> tbody > tr:first > td:eq('+index+')');
                if (!tdIndex.length) {
                    tdIndex = dom.find('> thead > tr > th:eq('+index+')');
                }
            }
            if (!tdIndex.length) {
                return true;
            }
            widthTdIndex = tdIndex.outerWidth();
            tdIndex.width(widthColFix);
            tdIndex.data('fix-width', widthColFix);
        });
    },
    /**
     * fix col column
     */
    colRemainItem: function (dom, colGroup, widthTbl) {
        var colDom = dom.children('colgroup').children('col[data-col-remain]');
        if (colDom.length !== 1) {
            return true;
        }
        var widthCols = 0;
        colGroup.each(function () {
            var colItem = $(this);
            if (colItem.is('[data-col-remain]')) {
                return true;
            }
            widthCols += colItem.outerWidth();
        });
        colDom.width(widthTbl - widthCols);
    },
};

/**
 * preview image
 */
RKExternal.previewImage = {
    init: function (option) {
        var that = this;
        option = typeof option === 'undefined' ? {} : option;
        that.option = $.extend({
            type: [ 'image/jpeg','image/png','image/gif'],
            size: 5120,
            message_size: 'File size is large',
            message_type: 'File type dont allow',
        }, option);
        $('[data-img-pre-input]').change(function(){
            that.readUrl(this, $(this).data('img-pre-input'));
        });
    },
    /**
     * read url from file upload
     */
    readUrl: function (input, type) {
        var that = this,
            domInput = $(input);
        if (!input.files || !input.files[0]) {
            return true;
        }
        var fileUpload = input.files[0];
        if($.inArray(fileUpload.type, that.option.type) < 0) {
            domInput.val('');
            alert(that.option.message_type);
            return true;
        }
        if (fileUpload.size / 1000 > that.option.size) {
            domInput.val('');
            alert(that.option.message_size);
            return true;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            $('[data-img-pre-img="'+type+'"]').attr('src', e.target.result);
        };
        reader.readAsDataURL(fileUpload);
    },
};

/**
 * table html => xml => excel
 * 
 * view more properties XML Spreadsheet at https://msdn.microsoft.com/en-us/library/Aa140066
 * --- xml styles: 
 *  <script type="text/xml" id="styleXml">
        <Style ss:ID="NameStyle">
            <Alignment ss:Vertical="Center" ss:Horizontal="Center" />
        </Style>
    </script>
 *
 * --- table styles:
 *  <table border="1" id="tbl1" class="table2excel" data-xml-ws-name="name sheet">
        <colgroup>
          <col data-width="100" />
          <col data-width="200" />
        </colgroup>
        <tbody>
            <tr data-height="200">
                <td data-xml-merge-across="1" data-xml-style-i-d="AlignCenter">Product</td>
                <td>12345679</td>
                <td data-xml-merge-down="2">Count</td> 
                -- data-xml: prefix
                -- merge-down: name of attribute xml Spreadsheet: MergeDown
            </tr>
        </tbody>
    </table>
 * --- table: allow style: colspan="2" rowspan="3", style tr, td must write in styles xml
 *
 *  --- use
 *  RKExternal.excel.init().exportExcel({
	tblFlg: $('.table'), // default $('table') - table to sheet (multi table => multi sheet)
        sheetsName: ['sheet name 1','sheet name 2'], // default [sheet1, sheet2]
        fileName: 'exportNameFile', // default exportExcel
        stylesFlg: '#styleXml', // default #styleXml - xml style for xml sheet
        replaceXml: function (xml, xmlDom, xmlElement, config) {}, // default null
    });
                
 * scrip lib:
 *  <script src="https://cdnjs.cloudflare.com/ajax/libs/blob-polyfill/2.0.20171115/Blob.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/javascript-canvas-to-blob/3.14.0/js/canvas-to-blob.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.min.js"></script>
 */
RKExternal.excel = {
    init: function () {
        var that = this;
        that.xmlHead = '<?xml version="1.0"?>'
+ '<?mso-application progid="Excel.Sheet"?>'
+ '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
    + 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" '
    + 'xmlns:o="urn:schemas-microsoft-com:office:office" '
    + 'xmlns:x="urn:schemas-microsoft-com:office:excel" '
    + 'xmlns:html="http://www.w3.org/TR/REC-html40">';
        that.xmlFoot = '</Workbook>';
        that.xml = that.xmlHead
    + '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">'
        + '<Author>Giang Soda</Author>'
        + '<Created>1527065780058</Created>'
        + '</DocumentProperties>'
    + '<Styles>'
        + '<Style ss:ID="Default">'
            + '<Font ss:Size="11" />'
        + '</Style>'
        + '<Style ss:ID="Currency">'
            +'<NumberFormat ss:Format="Currency"></NumberFormat>'
        + '</Style>'
        + '<Style ss:ID="Date">'
            + '<NumberFormat ss:Format="Medium Date"></NumberFormat>'
        + '</Style>'
        + '<Style ss:ID="Thead">'
            + '<Alignment ss:Vertical="Center" ss:Horizontal="Center" ss:WrapText="1"/>'
            + '<Font ss:Size="11" ss:Bold="1" />'
            + '<Interior ss:Color="#ececec" ss:Pattern="Solid" />'
            + '<Borders>'
                + '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>'
                + '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>'
                + '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>'
                + '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>'
            + '</Borders>'
        + '</Style>'
    + '</Styles>'
    + '<Worksheet ss:Name="wsName">'
        + '<Table>'
            + '<Column ss:AutoFitWidth="0" />'
            + '<Row ss:AutoFitHeight="0">'
                + '<Cell ss:StyleID="Default"><Data ss:Type="String"></Data></Cell>'
            + '</Row>'
        +'</Table>'
    +'</Worksheet>'
+ that.xmlFoot;
        return this;
    },
    /**
     * get xml from file url
     *
     * @param {type} url
     * @return {undefined}
     */
    getXmlFile: function (url, callback) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                if (typeof callback === 'function') {
                    callback(xhttp.responseXML);
                }
            }
        };
        xhttp.open('get', url, true);
        xhttp.send();
    },
    /**
     * get symbol in xml to excel
     *
     * @return object
     */
    getSymbol: function () {
        return {
            'enter': '&#13;&#10;',
        };
    },
    replaceEnterToBr: function (text) {
        return text.replace(/\r\n|\n|\r/gi, '{{--br--}}');
    },
    replaceBrToBreak: function (text) {
        return text.replace(/\{\{\-\-br\-\-\}\}/gi, '&#13;&#10;');
    },
    /**
     * export excel from table
     *
     * @param {type} config
     * @return {Boolean}
     */
    exportExcel: function (config) {
        if (typeof Blob === 'undefined' || typeof saveAs  === 'undefined') {
            alert('Miss Blob and FileSaver lib');
            return true;
        }
        var that = this;
        config = $.extend({
            tblFlg: $('table'), // flag jquery dom
            sheetsName: [],
            fileName: 'exportExcel',
            stylesFlg: '#styleXml',
            replaceXml: null, // function(xml, xmlDom, xmlElemetn, config)
        }, config);
        var xml = $.parseXML(that.xml.trim()),
        xmlDom = $(xml),
        domStyles = $(config.stylesFlg).html(),
        xmlElement = {};
        if (domStyles && domStyles.trim()) {
            domStyles = $.parseXML(that.xmlHead + domStyles.trim() + that.xmlFoot);
        } else {
            domStyles = null;
        }
        // init dom child
        xmlElement.cel = xmlDom.find('Cell');
        xmlDom.find('Cell').remove();
        xmlElement.row = xmlDom.find('Row');
        xmlDom.find('Row').remove();
        xmlElement.col = xmlDom.find('Column');
        xmlDom.find('Column').remove();
        xmlElement.tbl = xmlDom.find('Table');
        xmlDom.find('Table').remove();
        xmlElement.ws = xmlDom.find('Worksheet');
        xmlDom.find('Worksheet').remove();
        if (domStyles) {
            xmlDom.find('Styles').append($(domStyles).find('Style'));
        }
        if (typeof config.replaceXml === 'function') {
            config.replaceXml(xml, xmlDom, xmlElement, config);
        } else {
            if (!config.tblFlg.length) {
                return true;
            }
            that.tblToXml(xmlDom, xmlElement, config);
        }
        that.saveFile(that.xmlDomToString(xml), config.fileName);
    },
    /**
     * create worksheet, table
     *
     * @param {object} xmlDom
     * @param {object} xmlElement
     * @param {string} nameWs
     * @return {unresolved}
     */
    createWsTbl: function (xmlDom, xmlElement, nameWs) {
        var wsCur = xmlElement.ws.clone();
        wsCur.attr('ss:Name', nameWs);
        xmlDom.find('Workbook').append(wsCur);
        var tblCur = xmlElement.tbl.clone();
        wsCur.append(tblCur);
        return tblCur;
    },
    /**
     * contvert table html to xml Spreadsheet
     *
     * @param {object} xmlDom
     * @param {object} xmlElement
     * @param {object} config
     */
    tblToXml: function (xmlDom, xmlElement, config) {
        var that = this;
        config.tblFlg.each(function (i, j) { // each table => multi sheet
            if (typeof config.sheetsName[i] === 'undefined') {
                config.sheetsName[i] = $(j).data('xml-ws-name');
                if (!config.sheetsName[i]) {
                    config.sheetsName[i] = 'sheet' + i;
                }
            }
            var tblCur = that.createWsTbl(xmlDom, xmlElement, config.sheetsName[i]);
            // each col in colgroup => multi column
            $(j).find('> colgroup > col').each (function (m, n) {
                if ($(n).data('excel-fg') === 'ignore') {
                    return true;
                }
                var colCur = xmlElement.col.clone(),
                dataWidth = $(n).data('width');
                if (dataWidth) {
                  colCur.attr('ss:AutoFitWidth', 0);
                  colCur.attr('ss:Width', dataWidth);
                } else {
                  colCur.attr('ss:AutoFitWidth', 1);
                }
                tblCur.append(colCur);
            });

            // each thead
            var thead = $(j).find('> thead > tr');
            if (thead.length && thead.data('excel-fg') !== 'ignore') {
                var rowCur = xmlElement.row.clone(),
                dataHeight = thead.data('height');
                if (dataHeight) {
                    rowCur.attr('ss:AutoFitHeight', 0);
                    rowCur.attr('ss:Height', dataHeight);
                } else {
                  rowCur.attr('ss:AutoFitHeight', 1);
                }
                tblCur.append(rowCur);
                thead.children('th').each (function (u, v) {
                    if ($(v).data('excel-fg') === 'ignore') {
                        return true;
                    }
                    var cellCur = xmlElement.cel.clone(),
                    td = $(v);
                    that.setAttrMerge(cellCur, td);
                    cellCur.attr('ss:StyleID', 'Thead');
                    $.each(td.data(), function (key, value) {
                        if (!key.startsWith('xml')) {
                            return true;
                        }
                        cellCur.attr('ss:' + key.substr(3), value);
                    });
                    var value = td.text();
                    if (value) {
                        value = value.trim();
                    }
                    if (td.hasClass('number')) {
                        cellCur.children('Data').attr('ss:Type', 'Number');
                    }
                    cellCur.children('Data').text(value);
                    rowCur.append(cellCur);
                }); // end each tr rows
            } // end each thead

            // each tr rows in body
            $(j).find('> tbody > tr').each (function (m, n) {
                if ($(n).data('excel-fg') === 'ignore') {
                    return true;
                }
                var rowCur = xmlElement.row.clone(),
                dataHeight = $(n).data('height');
                if (dataHeight) {
                  rowCur.attr('ss:AutoFitHeight', 0);
                  rowCur.attr('ss:Height', dataHeight);
                } else {
                  rowCur.attr('ss:AutoFitHeight', 1);
                }
                tblCur.append(rowCur);
                $(n).find('> td').each (function (u, v) { // each td cell
                    if ($(v).data('excel-fg') === 'ignore') {
                        return true;
                    }
                    var cellCur = xmlElement.cel.clone(),
                    td = $(v);
                    // append attribute special -- colspan, rowspan
                    that.setAttrMerge(cellCur, td);
                    $.each(td.data(), function (key, value) {
                        if (!key.startsWith('xml')) {
                            return true;
                        }
                        cellCur.attr('ss:' + key.substr(3), value);
                    });
                    var value = td.text();
                    if (value) {
                        value = value.trim();
                    }
                    if (td.hasClass('number')) {
                        cellCur.children('Data').attr('ss:Type', 'Number');
                    }
                    cellCur.children('Data').text(value);
                    rowCur.append(cellCur);
                }); // end each td cell
            }); // end each tr rows
        }); // end each tbl
    },
    setAttrMerge: function (cellCur, td) {
        var objConvert = {
            'colspan': 'ss:MergeAcross',
            'rowspan': 'ss:MergeDown'
        };
        $.each(objConvert, function (htmlAttr, xmlAttr) {
            var mergeVal = td.attr(htmlAttr);
            if(mergeVal && !isNaN(mergeVal)) {
                cellCur.attr(xmlAttr, parseInt(mergeVal)-1);
            };
        });
    },
    /**
     * base64 string
     *
     * @param {String} s
     * @return {String}
     */
    base64: function(s) {
        return window.btoa(unescape(encodeURIComponent(s)));
    },
    /**
     * replace string s by obj {replaceSearch: replaceBy}
     *
     * @param {String} s
     * @param {Object} c
     * @return {string}
     */
    format: function(s, replaceObj) {
        return s.replace(/{(\w+)}/g, function(m, p) { return replaceObj[p]; }) ;
    },
    /**
     * xml dom object to string
     *
     * @param {dom} xmlDom
     * @return {string}
     */
    xmlDomToString: function (xmlDom) {
        return this.replaceBrToBreak(new XMLSerializer().serializeToString(xmlDom));
    },
    stringToXml: function (s) {
        var parser = new DOMParser();
        return parser.parseFromString(s, 'text/xml');
    },
    /**
     * save file, require blob and FileSaver
     * https://cdnjs.cloudflare.com/ajax/libs/blob-polyfill/2.0.20171115/Blob.min.js
     * https://cdnjs.cloudflare.com/ajax/libs/javascript-canvas-to-blob/3.14.0/js/canvas-to-blob.min.js
     * https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.min.js
     * @param {string} s
     * @param {String} fileName
     */
    saveFile: function (s, fileName, isAddXmlHead) {
        var that = this;
        if (typeof s === 'object') {
            s = that.xmlDomToString(s);
            if (isAddXmlHead) {
                s = '<?xml version="1.0"?>' + s;
            }
        }
        var blob = new Blob([s], {type: "application/vnd.ms-excel;charset=utf-8"});
        saveAs(blob, (fileName ? fileName : 'exportExcel') + '.xls');
    },
};

RKExternal.normal = {
    scrollTo: function (dom) {
        if (!dom.length) {
            return true;
        }
        $('html, body').stop().animate({
            scrollTop: dom.offset().top
        }, 500);
    },
};

/**
 * pagination ajax
 *
 * format ajax response pagination:
 *  {current_page, data, is_next_page, is_prev_page, per_page}
 */
RKExternal.paginate = {
    data: {},
    dom: [],
    html: {},
    url: {},
    moreType: {}, // append, prepend, html
    moreLoad: {}, // scroll, paginate
    hasData: {},
    /**
     * init pager exec
     *
     * @param {object} collection
     * @return {object}
     */
    init: function (domWrapperList) {
        var that = this;
        if (!domWrapperList) {
            domWrapperList = $('[data-page-list]');
        }
        that.option = {
            numberPage: 5,
        };
        domWrapperList.each(function (i, v) {
            var domList = $(v),
            dom = domList.data('page-list');
            if (!dom) {
                return true;
            }
            that.dom.push(dom);
            that.data[dom] = {};
            that.html[dom] = {
                itemWrapper: $('[data-page-item-wrapper="'+dom+'"]'),
            };
            that.html[dom].itemHtml = that.html[dom].itemWrapper.html();
            that.url[dom] = domList.data('page-url');
            that.moreType[dom] = domList.data('page-more');
            that.moreLoad[dom] = domList.data('page-load');
            if (!that.moreType[dom] ||
                ['html', 'append', 'prepend'].indexOf(that.moreType[dom]) === -1
            ) {
                that.moreType[dom] = 'html';
            }
            if (!that.moreLoad[dom] ||
                ['scroll', 'paginate'].indexOf(that.moreLoad[dom]) === -1
            ) {
                that.moreLoad[dom] = 'scroll';
            }
            // remove item html
            that.html[dom].itemWrapper.html('');
            switch (domList.data('page-load')) {
                case 'scroll':
                    that.loadMoreScroll(dom);
                    break;
                case 'btn':
                    that.loadMoreBtn(dom);
                    break;
                default: // paginate
                    that.setHtmlPaginate();
                    that.loadMorePaginate(dom);
            }
            var pageParams = domList.data('page-param');
            if (!pageParams || pageParams !== 'no') { // page default
                if (!pageParams) {
                    pageParams = 'page';
                }
                var paramsUrl = RKExternal.params();
                domList.data('page', parseInt(paramsUrl[pageParams]) - 1);
            }
            if (typeof domList.data('load-init') === 'undefined' ||
                domList.data('load-init')
            ) { // load init
                that.loadMoreItemAjax(dom);
            }
        });
        that.paginateSearchInit();
        that.paginateSearch();
        return that;
    },
    setHtmlPaginate: function () {
        var that = this;;
        if (that.paginate) {
            return that;
        }
        that.paginate = {
            number: $('[data-pager-item="number"]')[0].outerHTML,
            more: $('[data-pager-item="more"]')[0].outerHTML,
            fgFirst: '[data-pager-item="first"]',
            fgLast: '[data-pager-item="last"]',
            fgPrev: '[data-pager-item="prev"]',
            fgNext: '[data-pager-item="next"]',
            fgNumber: '[data-pager-item="number"]',
            fgRenderNumber: '[data-pager-page]',
        };
        $('[data-pager-item="more"]').remove();
        that.paginate.wrapper = $('[data-pager-item="page"]')[0].outerHTML,
        $('[data-pager-item="page"]').remove();
        that.paginateClick();
    },
    setData: function (dom, data) {
        var that = this;
        that.data[dom] = data;
        that.exec(dom);
        return that;
    },
    execAllDom: function () {
        var that = this;
        that.dom.forEach(function (domType) {
            that.exec(domType);
        });
        return that;
    },
    exec: function (dom) {
        var that = this,
        html = '',
        htmlItem;
        if (typeof that.data[dom] !== 'object' ||
            !that.data[dom].data ||
            !that.data[dom].data.length
        ) {
            if (that.hasData[dom] &&
                ['append', 'prepend'].indexOf(that.moreType[dom]) > -1
            ) {
                if ($('[data-page-list="'+dom+'"]').data('page') == 1) {
                    that.showNoResult(dom);
                }
                return true;
            }
            that.showNoResult(dom);
            return that;
        }
        $.each(that.data[dom].data, function (i, dataItem) {
            htmlItem = that.html[dom].itemHtml;
            if (typeof RKExternal.paginate.beforeRender === 'object' &&
                typeof RKExternal.paginate.beforeRender[dom] === 'function') {
                dataItem = RKExternal.paginate.beforeRender[dom](dataItem);
            }
            $.each(dataItem, function (attr, value) {
                if (value === null) {
                    value = '';
                }
                var reg = new RegExp('\{' + attr + '\}', 'gm');
                htmlItem = htmlItem.replace(reg, value);
            });
            if (that.moreType[dom] === 'prepend') {
                html = htmlItem + html;;
            } else {
                html += htmlItem;
            }
        });
        // first page => html
        if ($('[data-page-list="'+dom+'"]').data('page') === 1) {
            that.html[dom].itemWrapper.html(html);
        } else {
            that.html[dom].itemWrapper[that.moreType[dom]](html);
        }
        if (that.moreLoad[dom] === 'paginate') {
            that.renderPaginate(dom);
        } else { // btn, scroll
            that.renderBtnLoadmore(dom);
        }
        if (that.moreType[dom] === 'html') {
            RKExternal.normal.scrollTo(that.html[dom].itemWrapper);
        }
        if (typeof RKExternal.paginate.afterDone === 'object' &&
            typeof RKExternal.paginate.afterDone[dom] === 'function') {
            RKExternal.paginate.afterDone[dom]();
        }
        $('[data-page-result="'+dom+'"]').removeClass('hidden');
        $('[data-page-noresult="'+dom+'"]').addClass('hidden');
        that.hasData[dom] = true;
        return that;
    },
    showNoResult: function (dom) {
        var that = this;
        $('[data-page-result="'+dom+'"]').addClass('hidden');
        $('[data-page-noresult="'+dom+'"]').removeClass('hidden');
        that.html[dom].itemWrapper[that.moreType[dom]]('');
    },
    loadMoreScroll: function (domType) {
        var that = this;
        $(window).scroll(function () {
            if (!that.data[domType].is_next_page) {
                return true;
            }
            if (that.moreType[domType] === 'prepend') {
                var posScrollWindow = $(window).scrollTop(),
                posItemWrapper = $('[data-page-item-wrapper="'+domType+'"]').offset().top;
                if (posScrollWindow < posItemWrapper + 10) {
                    that.loadMoreItemAjax(domType);
                }
                return true;
            }
            // for append, html
            var posScrollWindow = $(window).scrollTop() + $(window).height(),
            posItemWrapper = $('[data-page-item-wrapper="'+domType+'"]').offset().top
                + $('[data-page-item-wrapper="'+domType+'"]').height();
            if (posScrollWindow > posItemWrapper - 10) {
                that.loadMoreItemAjax(domType);
            }
        });
    },
    loadMoreBtn: function (domType) {
        var that = this;
        $(document).on('click', '[data-page-more-btn="'+domType+'"]', function () {
            if (!that.data[domType].is_next_page) {
                return true;
            }
            that.loadMoreItemAjax(domType);
        });
    },
    loadMorePaginate: function (domType) {
        var that = this;
        if (!that.data[domType].next_page_url) {
            return true;
        }
        that.loadMoreItemAjax(domType);
    },
    /**
     * render btn loadmore
     *
     * @param {string} domType
     */
    renderBtnLoadmore: function (domType) {
        var that = this;
        if (that.data[domType].is_next_page) {
            $('[data-page-more-btn="'+domType+'"]').removeClass('hidden');
        } else {
            $('[data-page-more-btn="'+domType+'"]').addClass('hidden');
        }
    },
    /*
     * need: data, next_page_url, current_page, last_page, 
     */
    renderPaginate: function (dom) {
        var that = this,
        domPaginate = $('[data-page-paginate="'+dom+'"]');
        if (!domPaginate.length) {
            return true;
        }
        var collection = that.data[dom];
        if (typeof collection !== 'object' ||
            !collection.last_page ||
            collection.last_page == 1
        ) {
            domPaginate.html('');
            return true;
        }
        // proces data
        var middleShow = parseInt((that.option.numberPage - 1) / 2),
            startPage = collection.current_page - middleShow,
            endPage = collection.current_page + middleShow,
            is_show_prev = collection.current_page > 1,
            is_show_next = collection.current_page < collection.last_page;
        if (startPage < 1) {
            endPage += 1 - startPage;
            startPage = 1;
        }
        if (endPage > collection.last_page) {
            startPage -= endPage - collection.last_page;
            if (startPage < 1) {
                startPage = 1;
            }
            endPage = collection.last_page;
        }
        // render html
        var domPage = $(that.paginate.wrapper);
        if (!is_show_prev) {
            domPage.find(that.paginate.fgPrev).addClass('disabled');
            domPage.find(that.paginate.fgFirst).addClass('disabled');
        } else {
            domPage.find(that.paginate.fgPrev).find(that.paginate.fgRenderNumber).attr('data-pager-page', collection.current_page - 1);
            domPage.find(that.paginate.fgFirst).find(that.paginate.fgRenderNumber).attr('data-pager-page', 1);;
        }
        var domNext = domPage.find(that.paginate.fgNext);
        if (!is_show_next) {
            domNext.addClass('disabled');
            domPage.find(that.paginate.fgLast).addClass('disabled');
        } else {
            domNext.find(that.paginate.fgRenderNumber).attr('data-pager-page', collection.current_page + 1);
            domPage.find(that.paginate.fgLast).find(that.paginate.fgRenderNumber).attr('data-pager-page', collection.last_page);
        }
        domPage.find(that.paginate.fgNumber).remove();
        for (var number = startPage; number <= endPage; number++) {
            var domNumber = $(that.paginate.number);
            domNumber.find(that.paginate.fgRenderNumber).attr('data-pager-page', number).text(number);
            if (number === parseInt(collection.current_page)) {
                domNumber.addClass('active');
            }
            domNext.before(domNumber);
        };
        if (endPage < collection.last_page) {
            domNext.before(that.paginate.more);
        }
        domPaginate.html(domPage[0].outerHTML);
        return that;
    },
    paginateClick: function () {
        var that = this;
        $(document).on('click', '[data-page-paginate] [data-pager-page]', function (event) {
            event.preventDefault();
            var pageItem = $(this),
            dom = pageItem.closest('[data-page-paginate]').data('page-paginate'),
            page = pageItem.data('pager-page');
            if (!page || isNaN(page)) {
                return true;
            }
            page = parseInt(page);
            if (page < 1) {
                return true;
            }
            $('[data-page-list="'+dom+'"]').data('page', page - 1);
            that.loadMoreItemAjax(dom);
        });
    },
    loadMoreItemAjax: function (dom, option) {
        var that = this,
        domList = $('[data-page-list="'+dom+'"]');
        if (!that.url[dom] || domList.data('process')) {
            return true;
        }
        option = typeof option === 'object' ? option : {};
        if (option.reset) {
            $('[data-page-reset-loading="'+dom+'"]').removeClass('hidden');
        } else {
            $('[data-page-loading="'+dom+'"]').removeClass('hidden');
        }
        $('[data-page-loading-org="'+dom+'"]').addClass('hidden');
        domList.data('process', 1);
        var paramsUrl = RKExternal.params();
        var page = domList.data('page');
        if ((isNaN(page) || !page || page < 1) && page != 0) {
            page = 0;
        }
        paramsUrl.page = page + 1;
        if (typeof RKExternal.paginate.beforeSendRequest === 'object' &&
            typeof RKExternal.paginate.beforeSendRequest[dom] === 'function') {
            paramsUrl = RKExternal.paginate.beforeSendRequest[dom](paramsUrl);
        }
        $.ajax({
            url: that.url[dom],
            type: 'GET',
            dataType: 'json',
            data: paramsUrl,
            success: function (response) {
                if (typeof RKExternal.paginate.beforeExecSuccess === 'object' &&
                    typeof RKExternal.paginate.beforeExecSuccess[dom] === 'function') {
                    RKExternal.paginate.beforeExecSuccess[dom](response);
                }
                domList.data('page', paramsUrl.page);
                that.setData(dom, response);
                if (typeof contactGetListUrl === 'string' && that.url[dom] === contactGetListUrl) {
                    $('[data-can-show-phone="' + NOT_SHOW_PHONE + '"]').map(function(i, e) {
                        $(e).next().remove();
                        $(e).remove();
                    });
                    $('[data-can-show-birthday="' + NOT_SHOW_BIRTHDAY + '"]').remove();
                    $('[data-can-show-birthday]').map(function (i, e) {
                        if ($(e).prev().prop('tagName') === 'BR' && $(e).prev().prev().prop('tagName') === 'BR') {
                            $(e).prev().remove();
                            $(e).parent().append('<br/>');
                        }
                    });
                }
            },
            complete: function () {
                domList.data('process', 0);
                $('[data-page-loading="'+dom+'"]').addClass('hidden');
                $('[data-page-reset-loading="'+dom+'"]').addClass('hidden');
                $('[data-page-loading-org="'+dom+'"]').removeClass('hidden');
            },
        });
        return that;
    },
    paginateSearch: function () {
        var that = this;
        $('[data-page-search]').keypress(function (e) {
            if (e.keyCode === 13) { // enter
                e.preventDefault();
                var inputDom = $(this),
                dom = inputDom.data('page-search');
                if (!dom) {
                    return true;
                }
                that.searchSubmit(dom);
            }
        });
        $('[data-page-search-btn]').click(function (e) {
            e.preventDefault();
            var inputDom = $(this),
            dom = inputDom.data('page-search-btn');
            if (!dom) {
                return true;
            }
            that.searchSubmit(dom);
        });
        $('[data-page-search-btn]').prop('disabled', false);
    },
    searchSubmit: function (dom) {
        var that = this,
        params = RKExternal.params(),
        checkboxName =[],
        radioName = [];
        $('[data-page-search="'+dom+'"]').each(function (i, v) {
            var nameInput = $(v).attr('name');
            if ('checkbox' === $(v).attr('type')) {
                if (checkboxName.indexOf(nameInput) > -1) {
                    return true;
                }
                checkboxName.push(nameInput);
                var valCheck = '';
                $('[name="'+nameInput+'"][data-page-search="'+dom+'"]:checked').each(function () {
                    valCheck += $(this).val() + '-';
                });
                valCheck = valCheck.slice(0, -1);
                if (valCheck) {
                    params[nameInput] = valCheck;
                } else {
                    delete params[nameInput];
                }
                return true;
            }
            if('radio' === $(v).attr('type')) {
                var nameInput = $(v).attr('name');
                if (radioName.indexOf(nameInput) > -1) {
                    return true;
                }
                radioName.push(nameInput);
                var domChecked = $('[name="'+nameInput+'"][data-page-search="'+dom+'"]:checked');
                if (!domChecked.length) {
                    delete params[nameInput];
                } else {
                    params[nameInput] = domChecked.val();
                }
                return true;
            }
            var value = $(v).val().trim();
            if (''+value === '') {
                delete params[nameInput];
            } else {
                params[nameInput] = value;
            }
        });
        $('[data-page-list="'+dom+'"]').data('page', 0);
        RKExternal.urlReplaceEncode(params);
        that.loadMoreItemAjax(dom);
    },
    /**
     * set value for form search when load page
     * run when reload page
     */
    paginateSearchInit: function () {
        var __this = this;
        if (!$('[data-page-search]').length) {
            return true;
        }
        var params = RKExternal.params();
        $.each(params, function (i, v) {
            var dom = $('[name="'+i+'"][data-page-search]');
            if (!dom.length) {
                return true;
            }
            v = v.trim();
            if ('radio' === dom.attr('type')) {
                $('[name="'+i+'"][data-page-search][value="' + v + '"]').prop('checked', true);
                return true;
            }
            if ('checkbox' === dom.attr('type')) {
                var valSplit = v.split('-');
                $.each (valSplit, function (m, n) {
                    $('[name="'+i+'"][data-page-search][value="' + n + '"]').prop('checked', true);
                });
                return true;
            }
            dom.val(v);
        });
    },
    reset: function (dom) {
        var that = this;
        $('[data-page-list="'+dom+'"]').data('page', 0);
        RKExternal.urlReplace(null, {}, false);
        that.loadMoreItemAjax(dom, {reset: true});
        $('[data-page-search="'+dom+'"]').val('');
        that.html[dom].itemWrapper.html('');
    },
};

/**
 * view more height px
 */
RKExternal.moreHeight = {
    o: {},
    init: function (dom, option) {
        if (typeof dom === 'undefined' || !dom || !dom.length) {
            dom = $('[data-more-height]');
        }
        var that = this;
        that.o = $.extend({
            textMore: 'view more',
            textLess: 'view less',
            height: 150,
            css: {
                height: '200px',
                overflow: 'hidden',
                display: 'block',
                position: 'relative',
                'margin-bottom': '0px',
            },
            cssMore: {
                height: 'auto',
                overflow: 'unset',
            },
            cssMargin: 40,
            cssBsd: {
                'box-shadow': 'rgba(176, 176, 176, 0.76) 0px 20px 40px 30px',
            }
        }, option);
        $.each (dom, function () {
            that.viewMore($(this));
        });
        $(document).on('click', '[data-more-btn]', function (e) {
            e.preventDefault();
            that.actionBtn($(this));
        });
    },
    /**
     * show btn view more => show less
     *
     * @param {object} dom
     * @param {Boolean} actionBtn
     */
    viewMore: function (dom, actionBtn) {
        var that = this,
        height = dom.data('more-height');
        if (!height) {
            height = that.o.height;
        }
        dom.removeAttr('style');
        dom.css('display', 'block');
        var heightDom = dom.outerHeight();
        if (heightDom <= height && !actionBtn) {
            return true;
        }
        dom.css($.extend({}, that.o.css, {height: ''+height+'px'}));
        if (dom.find('[data-more-btn]').length) {
            return true;
        }
        dom.append('<div class="block-more" style="position: absolute;bottom: 0;left: 0;width: 100%;" d-bm-wrap>'
            + '<div class="bm-shadow" d-bm-sd></div>'
            + '<button type="button" data-more-btn="more" class="btn btn-primary">'
            + that.o.textMore
            + '</button></div>');
        dom.find('[d-bm-sd]').css(that.o.cssBsd);
    },
    actionBtn: function (btn) {
        var that = this,
        type = btn.data('more-btn'),
        domBlockBtn = btn.closest('[d-bm-wrap]'),
        domMoreHeight = domBlockBtn.closest('[data-more-height]');
        if (type === 'more') { // show more
            btn.text(that.o.textLess);
            domMoreHeight.css('margin-bottom', '' + that.o.cssMargin + 'px')
                .css(that.o.cssMore);
            domBlockBtn.css('bottom', '-' + that.o.cssMargin + 'px');
            domBlockBtn.find('[d-bm-sd]').removeAttr('style');
            btn.data('more-btn', 'less');
        } else { // show less
            btn.text(that.o.textMore);
            that.viewMore(domMoreHeight, true);
            domBlockBtn.css('bottom', '0px');
            domBlockBtn.find('[d-bm-sd]').css(that.o.cssBsd);
            btn.data('more-btn', 'more');
        }
    },
};

/**
 * call when loaded window
 */
RKExternal.onload = {
    init: function () {
        var that = this;
        that.momentSD();
    },
    /**
     * moment start date monday
     */
    momentSD: function () {
        if (typeof moment === 'function' &&
            typeof moment.locale === 'function'
        ) {
            moment.locale('en', {
                week: { dow: 1 }
            });
        }
    },
};
// init load page
$(window).load(function () {
    RKExternal.formAjax.init();
    RKExternal.simple.textShort(null, true);
    RKExternal.onload.init();
});
window.RKExternal = RKExternal;
})(jQuery, document, window);
