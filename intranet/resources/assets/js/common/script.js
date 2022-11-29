/**
 * option custom
 */
var optionCustomR = {
    size: {
        adminlte_sm: 767,
        custom_sm: 1195
    }
};
var hash = window.location.hash;
(function($) {
    /**
     * active tab by hash
     */
    hash && $('ul.nav a[href="' + hash + '"]').tab('show');
    $('.nav-tabs a').click(function (e) {
        $(this).tab('show');
        var scrollmem = $('body').scrollTop() || $('html').scrollTop();
        window.location.hash = this.hash;
        $('html,body').scrollTop(scrollmem);
    });
    if (typeof RKfuncion === 'undefined') {
        RKfuncion = {};
    }
    /**
     * General function
     */
    RKfuncion.general = {
        parseHtml: function (note) {
            if (typeof note == 'undefined' || note.trim() == '') {
                return '';
            }
            return $.parseHTML(note.trim())[0].nodeValue
        },
        parseHTMLChar: function (str) {
            if (!str) {
                return '';
            }
            return str.replace(/&/g, "&amp;")
                .replace(/>/g, "&gt;")
                .replace(/</g, "&lt;")
                .replace(/"/g, "&quot;");
        },
        stripTagsAndEncode: function (str) {
            str = $('<textarea />').html(str).text();
            return this.parseHTMLChar(str);
        },
        btnSubmitHref: function() {
            $('.btn-submit-href').click(function(event) {
                event.preventDefault();
                var href = $(this).data('href');
                if (!href || !$(href).length || !$(href).is('form')) {
                    return true;
                }
                $(href).submit();
            });
        },
        modalBodyPadding: function(flagCss) {
            if (typeof flagCss == 'undefined' || !flagCss) {
                flagCss = '.modal';
            }
            var paddingRight = $('body').css('padding-right');
            $(flagCss).on('hidden.bs.modal', function (e) {
                $('body').css('padding-right', paddingRight);
                if ($(flagCss).is(':visible')) {
                    setTimeout(function() {
                        $('body').addClass('modal-open');
                    },500);
                }
            });
        },
        removeBlock: function () {
            $(document).on('click touchstart', '.remove-block-click', function(event) {
                event.preventDefault();
                $(this).closest('.remove-block-wrapper').remove();
            });
        },
        reloadBlockAjax: function(dom) {
            /**
             * reload block by ajax
             */
            if (!dom.length) {
                return false;
            }
            dom.each(function() {
                var __thisDom = $(this);
                __thisDom.find('.block-loading-icon').removeClass('hidden');
                $.ajax({
                    url: __thisDom.data('url'),
                    type: 'GET',
                    data: {},
                    dataType: 'json',
                    success: function(data) {
                        if (typeof data.html !== 'undefined') {
                            __thisDom.find('.grid-data-query-table').html(data.html);
                        }
                    },
                    complete: function() {
                        __thisDom.find('.block-loading-icon').addClass('hidden');
                    }
                });
            });
        },
        serializeDataBlock: function (dom) {
            var data = {};
            dom.find('[data-block-form="1"]').each(function() {
                data[$(this).attr('name')] = $(this).val();
            });
            return data;
        },
        initDateTimePicker: function (dom) {
            if (typeof $().datetimepicker == 'undefined') {
                return;
            }
            if (typeof dom == 'undefined') {
                dom = $('.input-datepicker');
            }
            dom.each(function () {
                var format = $(this).data('format') || 'YYYY-MM-DDDD';
                var options = {
                    format: format,
                };
                //extra options use: data-options="{{ json_encode(['maxDate' => 'xxxx-xx-xx', ...]) }}"
                var extraOptions = $(this).data('options') || {};
                options = $.extend(options, extraOptions);
                $(this).datetimepicker(options);
            });
        },
        initDatePicker: function (dom) {
            if (typeof $().datepicker == 'undefined') {
                return;
            }
            if (typeof dom == 'undefined') {
                dom = $('.input-datepicker');
            }
            dom.each(function () {
                var format = $(this).data('format') || 'dd-mm-yyyy';
                var options = {
                    format: format,
                };
                //extra options use: data-options="{{ json_encode(['maxDate' => 'xxxx-xx-xx', ...]) }}"
                var extraOptions = $(this).data('options') || {};
                options = $.extend(options, extraOptions);
                $(this).datepicker(options);
            });
        },
        getNickName: function(email) {
            var account = email.replace(/@.*/, '');
            return account.charAt(0).toUpperCase() + account.slice(1);
        },
        paramsFromUrl: function(url) {
            var searchStr = '';
            if (typeof url == 'undefined') {
                searchStr = window.location.search;
            } else {
                searchStr = new URL(url).search;
            }
            searchStr = searchStr.substring(1);
            var params = {};
            var searchArray = searchStr.split('&');
            for (var i = 0; i < searchArray.length; i++) {
                var paramStr = searchArray[i];
                var paramArr = paramStr.split('=');
                if (paramArr.length !== 2) {
                    continue;
                }
                params[paramArr[0]] = paramArr[1];
            }
            return params;
        },
        strRandom: function (length, hasSymbol) {
            if (typeof hasSymbol == 'undefined') {
                hasSymbol = true;
            }
            var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOP1234567890";
            if (hasSymbol) {
                chars += '~!@#$%^&*()-+_=<>{}[];:,.?';
            }
            var str = "";
            for (var x = 0; x < length; x++) {
                var i = Math.floor(Math.random() * chars.length);
                str += chars.charAt(i);
            }
            return str;
        },
    };

    RKfuncion.keepStatusTab = {
        classDom: '.tab-keep-status',
        init: function() {
            var __this = this;
            if (!$(__this.classDom).length) {
                return false;
            }
            $(__this.classDom).each(function() {
                __this._elementKeep($(this));
            });
        },
        _elementKeep: function(dom) {
            var __this = this;
            var type = dom.data('type');
            if (!type) {
                return false;
            }
            var keyCookie = __this.classDom + '-' + type;
            keyCookie = keyCookie.replace(/(\#|\?|\=|\&|\:|\/|\.)/g, '');
            dom.find('.nav-tabs:first > li > a').click(function() {
                if ($(this)[0].hasAttribute('data-toggle')) {
                    var href = $(this).attr('href').replace(/(\#|\?|\=|\&|\:|\/)/g, '');
                    $.cookie( keyCookie, href, { expires: 1, path: '/' });
                }
            });
        }
    };
    
    RKfuncion.formSubmitAjax = {
        classDom: '.form-submit-ajax',
        classButton: '.post-ajax',
        classDomChange: '.form-submit-change-ajax',
        init: function() {
            var __this = this;
            if (!$('.warning-action').length) {
                $('body').append('<button class="warning-action hidden"></button>');
            }
            if (!$('.success-action').length) {
                $('body').append('<button class="success-action hidden"></button>');
            }
            $(document).on('submit', __this.classDom, function(event) {
                event.preventDefault();
                __this._elementSubmit($(this), 1);
            });
            $(document).on('change', __this.classDomChange + ' input', function(event) {
                event.preventDefault();
                __this._elementSubmit($(this), 3);
            });
            $(document).on('click', __this.classButton, function(event) {
                event.preventDefault();
                var _thisButton = $(this);
                if (_thisButton.hasClass('delete-confirm') ||
                    _thisButton.hasClass('warn-confirm')) {
                    setTimeout(function() {
                        if (_thisButton.hasClass('process')) {
                            return true;
                        }
                        __this._elementSubmit(_thisButton, 2);
                    }, 300);
                } else {
                    __this._elementSubmit(_thisButton, 2);
                }
            });
        },
        _elementSubmit: function(dom, type) {
            var __this = this;
            switch (type) {
                case 1: // submit form
                    var btnSubmit = dom.find('[type=submit]:not(.no-disabled)'),
                        data = dom.serialize(),
                        url = dom.attr('action'),
                        loadingRefresh = dom.find('.submit-ajax-refresh');
                    break;
                case 2: // button click
                    var btnSubmit = dom,
                        data = {
                            _token: siteConfigGlobal.token
                        };
                        url = dom.data('url-ajax'),
                        loadingRefresh = dom.find('.submit-ajax-refresh-btn');
                    if(dom.is('[data-block-form-submit="1"]')) {
                        var blockFormData = dom.closest('.block-form-data');
                        if (blockFormData.length) {
                            data = $.extend(data, RKfuncion.general.serializeDataBlock(blockFormData));
                        }
                    } else if(dom.hasClass('is-submit-report')) {
                        data = $("#form-dashboard-point :input.pp-input").serialize() +
                            '&_token='+siteConfigGlobal.token;
                    }
                    break;
                case 3: // form change submit
                    var btnSubmit = dom,
                        inputName = dom.attr('name'),
                        inputValue = dom.val(),
                        data = {
                            _token: siteConfigGlobal.token,
                            data: {}
                        },
                        domWrapper = dom.closest(__this.classDomChange),
                        url = domWrapper.data('url-ajax'),
                        loadingRefresh = domWrapper.find('.submit-ajax-refresh');
                        data.data[inputName] = inputValue;
                    break;
                default: 
                    return true;
            }
            if (dom.hasClass('has-valid')) {
                if (!dom.valid()) {
                    dom.find('[type=submit]').removeAttr('disabled');
                    return true;
                }
            }
            if (btnSubmit.attr('requestRunning')) {
                return;
            }
            btnSubmit.attr('requestRunning', true);
            btnSubmit.attr('disabled', 'disabled');
            btnSubmit.find('.btn-submit-main').addClass('hidden');
            btnSubmit.find('.btn-submit-refresh').removeClass('hidden');
            loadingRefresh.removeClass('hidden');
            $.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: data,
                success: function (data) {
                    if (typeof data.success !== 'undefined' && data.success == 1) {
                        if (typeof data.popup == 'undefined' || data.popup != 1) {
                            // show popup success
                            if (typeof data.message !== 'undefined' && data.message) {
                                $('.success-action').attr('data-noti', data.message);
                            }
                            $('.success-action').trigger('click');
                            if (typeof data.refresh !== 'undefined' && data.refresh) {
                                $('#modal-success-notification').on('hide.bs.modal', function() {
                                    window.location.href = data.refresh;
                                });
                            } else {
                                var callbackSuccess = dom.data('callback-success');
                                if (callbackSuccess && 
                                    typeof RKfuncion.formSubmitAjax[callbackSuccess] != 'undefined'
                                ) {
                                    RKfuncion.formSubmitAjax[callbackSuccess](dom, data);
                                }
                            }
                            // replace data html dom
                            if (typeof data.htmlDom != 'undefined') {
                                $.each(data.htmlDom, function(i,k) {
                                    if ($(i).length) {
                                        $(i).html(k);
                                    }
                                });
                            }
                            // add class for dom
                            if (typeof data.addClassDom != 'undefined') {
                                $.each(data.addClassDom, function(i,k) {
                                    if ($(i).length) {
                                        $(i).addClass(k);
                                    }
                                });
                            }
                            // remove class for dom
                            if (typeof data.removeClassDom != 'undefined') {
                                $.each(data.removeClassDom, function(i,k) {
                                    if ($(i).length) {
                                        $(i).removeClass(k);
                                    }
                                });
                            }
                        } else if (typeof data.reload !== 'undefined' && data.reload) {
                            window.location.reload();
                        } else if (typeof data.refresh !== 'undefined' && data.refresh) {
                            window.location.href = data.refresh;
                        } else if (typeof data.reloadBlockAjax !== 'undefined' && data.reloadBlockAjax) {
                            //check if task is css
                            if ($('input[name=type]').length > 0 && $('input[name=type]').val() == typeIssueCSS) {
                                generateHtml(typeIssueCSS);
                            }
                            if (data.reloadBlockAjax instanceof Array) {
                                var i;
                                for (i in data.reloadBlockAjax) {
                                    RKfuncion.general.reloadBlockAjax($(data.reloadBlockAjax[i]));
                                }
                            } else if (typeof data.reloadBlockAjax === 'string'){
                                RKfuncion.general.reloadBlockAjax($([data.reloadBlockAjax]));
                            } else {
                                window.location.reload();
                            }
                            $('.modal').each(function() {
                                if ($(this).is(':visible')) {
                                    $(this).modal('hide');
                                }
                            });
                            /* myTask */ 
                            var callbackSuccess = dom.data('callback-success');
                            if (callbackSuccess && 
                                typeof RKfuncion.formSubmitAjax[callbackSuccess] != 'undefined'
                            ) {
                                RKfuncion.formSubmitAjax[callbackSuccess](dom, data);
                            }
                            /*End my Task*/
                            
                        } else {
                            var callbackSuccess = dom.data('callback-success');
                            if (callbackSuccess && 
                                typeof RKfuncion.formSubmitAjax[callbackSuccess] !== 'undefined'
                            ) {
                                RKfuncion.formSubmitAjax[callbackSuccess](dom, data);
                            }
                        }
                    } else {
                        var callbackError = dom.data('callback-error');
                        if (callbackError && 
                            typeof RKfuncion.formSubmitAjax[callbackError] !== 'undefined'
                        ) {
                            RKfuncion.formSubmitAjax[callbackError](dom, data);
                        } else if (typeof data.message !== 'undefined' && data.message) {
                            $('.warning-action').attr('data-noti', data.message);
                            $('.warning-action').trigger('click');
                        } else {
                            $('.warning-action').trigger('click');
                        }
                    }
                    btnSubmit.find('.btn-submit-main').removeClass('hidden');
                    btnSubmit.find('.btn-submit-refresh').addClass('hidden');
                    loadingRefresh.addClass('hidden');
                    if (typeof data.refresh == 'undefined' || !data.refresh) {
                        btnSubmit.removeAttr('disabled');
                        btnSubmit.removeAttr('requestrunning');
                    }
                },
                error: function() {
                    loadingRefresh.addClass('hidden');
                    btnSubmit.removeAttr('disabled');
                    btnSubmit.find('.btn-submit-main').removeClass('hidden');
                    btnSubmit.find('.btn-submit-refresh').addClass('hidden');
                    $('.warning-action').trigger('click');
                },
                complete: function () {
                    btnSubmit.removeAttr('requestrunning');
                    if(dom.hasClass('is-submit-report')) {
                        $('.is-report').find('.submit-ajax-refresh-btn').addClass('hidden');
                    }
                }
            });
        }
    };
    RKfuncion.radioToggleClickShow = {
        init: function() {
            var __this = this;
            $('.radio-toggle-click-wrapper').find('.radio-toggle-click-show')
                .addClass('hidden');
            $('.radio-toggle-click-wrapper input.radio-toggle-click').each (function () {
                __this._eachItem($(this));
            });
            $('.radio-toggle-click-wrapper input.radio-toggle-click').click(function() {
                __this._eachItem($(this));
            });
        },
        _eachItem: function (dom) {
            var toogleWrapper = dom.closest('.radio-toggle-click-wrapper'),
                id = dom.attr('id');
            if (dom.is(':checked')) {
                toogleWrapper.find('.radio-toggle-click-show')
                    .addClass('hidden');
                toogleWrapper.find('.radio-toggle-click-show[data-id="' + id + '"]')
                    .removeClass('hidden');
            }
        }
    };
    
    RKfuncion.fixHeightWindow = {
        init: function(wrapperFlag) {
            var windowHeight = $(window).outerHeight(),
            bodyHeight = $('body').outerHeight();
            if (typeof wrapperFlag != undefined) {
                var wrapper = $(wrapperFlag).outerHeight();
            }
            if (bodyHeight < windowHeight) {
                $('body').height(windowHeight);
                if (typeof wrapperFlag != undefined) {
                    $(wrapperFlag).height(windowHeight);
                }
            }
        }
    };
    RKfuncion.CKEditor = {
        init: function (arrayIdDom, ckfinder, option) {
            var that = this,
            ckEditorReturn = {};
            option = typeof option === 'object' ? option : {};
            var exPlugIn, rePlugin;
            if (option.extraPlugins) {
                exPlugIn = option.extraPlugins;
            } else if (option.extraPluginsMore) {
                exPlugIn = 'justify,colorbutton,indentblock,' + option.extraPluginsMore;
            } else {
                exPlugIn = 'justify,colorbutton,indentblock';
            }
            if (option.removePlugins) {
                rePlugin = option.removePlugins;
            } else if (option.removePluginsMore) {
                rePlugin = 'elementspath,save,wsc,scayt,undo,' + option.removePluginsMore;
            } else {
                rePlugin = 'elementspath,save,wsc,scayt,undo';
            }
            CKEDITOR.config.removePlugins = rePlugin;
            CKEDITOR.config.extraPlugins = exPlugIn;
            if (typeof arrayIdDom == 'undefined' || !arrayIdDom || !arrayIdDom.length) {
                return true;
            }
            var indexDom;
            for (indexDom in arrayIdDom) {
                if (!$('#' + arrayIdDom[indexDom]).length) {
                    continue;
                }
                ckEditorReturn[arrayIdDom[indexDom]] = CKEDITOR.replace( arrayIdDom[indexDom] );
                if (typeof ckfinder != 'undefined' && ckfinder) {
                    CKFinder.setupCKEditor( ckEditorReturn[arrayIdDom[indexDom]], '/lib/ckfinder' );
                }
            }
            $('.btn-submit-ckeditor').click(function() {
                for (indexDom in arrayIdDom) {
                    $('#' + arrayIdDom[indexDom]).val(CKEDITOR.instances[arrayIdDom[indexDom]].getData());
                }
            });
            if (option.attach) {
                that.attachment();
            }
            return ckEditorReturn;
        },
        /**
         * attachment file
         */
        attachment: function () {
            var that = this;
            $('[data-ckeditor-attach]').click(function(event) {
                event.preventDefault();
                var domThis = $(this),
                    typeId = domThis.data('ckeditor-attach'),
                    domTextarea = $('#' + typeId);
                if(!domTextarea.length) {
                    return false;
                }
                var finder = new CKFinder();
                finder.selectActionFunction = function(fileUrl) {
                    fileUrl = '/' + fileUrl.replace(/^[\/]+|[\/]+$/gm, '');
                    var valCkeditor = CKEDITOR.instances[typeId].getData();
                    valCkeditor += '<p><a href="'+fileUrl+'" target="_blank">'
                        + decodeURIComponent(that.filePathToName(fileUrl)) + '</a></p>';
                    CKEDITOR.instances[typeId].setData(valCkeditor);
                };
                finder.popup();
            });
        },
        /**
         * filePath to name
         *
         * @param {string} filePath
         * @returns {string}
         */
        filePathToName: function (filePath) {
            var index = filePath.lastIndexOf('/');
            if (index === -1) {
                index = 0;
            } else {
                index += 1;
            }
            return filePath.substr(index);
        },
    };
    RKfuncion.select2 = {
        init: function (option, element) {
            if (typeof $().select2 == 'undefined') {
                return true;
            }
            if (typeof element == 'undefined') {
                element = $('.select-search');
            }
            var __this = this;
            option = typeof option != 'undefined' ? option : {};
            if (option.enforceFocus) {
                try {
                    $.fn.modal.Constructor.prototype.enforceFocus = function(){};
                } catch(e) {}
            }
            element.each(function(){
                var extraOptions = $(this).attr('data-options');
                if (typeof extraOptions == 'undefined') {
                    extraOptions = {};
                } else {
                    extraOptions = JSON.parse(extraOptions);
                }
                var elOption = $.extend({}, option, extraOptions);
                if ($(this).attr('data-remote-url')) {
                    __this.elementRemote($(this), elOption);
                } else {
                    __this.element($(this), elOption);
                }
            });
        },
        element: function(dom, option) {
            if (typeof $().select2 == 'undefined') {
                return true;
            }
            var __this = this,
                optionDefault = {
                    showSearch: false,
                    templateResult: __this.__formatResult,
                    templateSelection: __this.__formatSelection,
                };
            if (!dom.hasClass('has-search')) {
                optionDefault.minimumResultsForSearch = Infinity;
            }
            option = $.extend({}, optionDefault, option);
            dom.select2(option);
            if ((typeof dom.attr('data-select2-trim') === 'undefined' || dom.attr('data-select2-trim') === '1')
                    && dom.attr('multiple') == 'undefined'
            ) {
                // error, not work for multiple
                var text = dom.find('option:selected').text().trim();
                dom.siblings('.select2-container')
                    .find('.select2-selection__rendered').text(text);
                dom.on('select2:select', function () {
                    var text = $(this).find('option:selected').text().trim();
                    $(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
                });
            }
        },
        elementRemote: function(dom, option) {
            if (typeof $().select2 == 'undefined') {
                return true;
            }
            /*
             * response need id and text, format
             * 
             *  {
             *      incomplete_results: true
             *      items:[
             *          {id: 1, text: "show"},
             *          {id: 1, text: "show"}
             *      ],
             *      total_count: 2
             */
            var __this = this;
            var optionDefault = {
                id: function(response){ 
                    return response.id;
                },
                minimumInputLength: 2,
                ajax: {
                    url: dom.data('remote-url'),
                    dataType: 'json',
                    delay: 500,
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
                },
                escapeMarkup: function (markup) {
                    return markup; 
                }, // let our custom formatter work
                templateResult: __this.__formatReponse, // omitted for brevity, see the source of this page
                templateSelection: __this.__formatReponesSelection // omitted for brevity, see the source of this page
            };
            option = $.extend(optionDefault, option);
            dom.select2(option);
            /*var text = dom.find('option:selected').text().trim();
            dom.siblings('.select2-container')
                .find('.select2-selection__rendered').text(text);
            dom.on('select2:select', function () {
                var text = $(this).find('option:selected').text().trim();
                $(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
            });*/
        },
        __formatReponse: function (response) {
            var text = RKfuncion.general.parseHTMLChar(response.text);
            if (response.loading) {
                return text;
            }
            return markup  = (response.avatar)? 
                "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__title'>" + 
                        "<img style=\"margin-right:8px;max-width: 32px;max-height: 32px;border-radius: 50%;\" src=\""+
                        response.avatar+"\">" + text + 
                    "</div>" +
                "</div>" 
                : 
                "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__title'>" + 
                        "<i style='margin-right:8px' class='fa fa-user-circle fa-2x' aria-hidden='true'></i>" +
                        text + 
                    "</div>" +
                "</div>"; 
          },
        __formatReponesSelection: function (response, domSpan) {
            if (typeof response.dataMore === 'object') {
                var domSelect = domSpan.closest('.select2.select2-container')
                    .siblings('select').first();
                $.each(response.dataMore, function (key, value) {
                    domSelect.data('select2-more-' + key, value);
                });
            }
            return RKfuncion.general.parseHTMLChar(response.text);
        },
        __formatResult: function (item) {
            var text = item.text;
            var element = $(item.element);
            if (typeof element.attr('data-icon-class') != 'undefined') {
                text = '<span><i class="'+ element.attr('data-icon-class') +'"></i> ' + text + '</span>';
                return $(text);
            }
            return text;
        },
        __formatSelection: function (item) {
            var text = item.text;
            var element = $(item.element);
            if (typeof element.attr('data-icon-class') != 'undefined') {
                text = '<span><i class="'+ element.attr('data-icon-class') +'"></i> ' + text + '</span>';
                return $(text);
            }
            return text;
        }
    };

    /**
     * match box height
     */
    RKfuncion.boxMatchHeight = {
        option: {},
        init: function (option) {
            var optionDefault = {
                width: 991, //min width to do action,
                parent: '',
                children: [],
                center: []
            },
            __this = this;
            __this.option = $.extend(optionDefault, option);
            if (!$(__this.option.parent).length || !__this.option.children.length) {
                return true;
            }
            __this.initWindow();
            $(window).load(function () {
                __this.initWindow();
            });
            $(window).resize(function () {
                __this.initWindow();
            });
            return __this;
        },
        initWindow: function () {
            var __this = this;
            __this.resetStyles();
            if ($(window).outerWidth() > __this.option.width) {
                $(__this.option.parent).each(function () {
                    __this.matchBoxParent($(this));
                });
            }
            return __this;
        },
        matchBoxParent: function (domParent) {
            var __this = this,
                    keyIndex,
                    flagChild,
                    heightBox;
            for (keyIndex in __this.option.children) {
                flagChild = __this.option.children[keyIndex];
                if (!domParent.find(flagChild)) {
                    continue;
                }
                heightBox = 0;
                domParent.find(flagChild).each(function () {
                    heightBox = $(this).height() > heightBox ? $(this).height() : heightBox;
                });
                if (heightBox > 0) {
                    domParent.find(flagChild).each(function () {
                        var heightChildCurrent = $(this).height();
                        $(this).height(heightBox);
                        // margin top if box center
                        if (__this.option.center.indexOf(flagChild) != -1) {
                            $(this).children().first().css('margin-top', (heightBox - heightChildCurrent) / 2)
                                    .css('display', 'block');
                        }
                    });
                }
            }
            return __this;
        },
        resetStyles: function () {
            var __this = this,
                    keyIndex;
            for (keyIndex in __this.option.children) {
                $(__this.option.children[keyIndex]).removeAttr('style');
            }
            for (keyIndex in __this.option.center) {
                $(__this.option.center[keyIndex]).each(function(){
                    $(this).children().first().removeAttr('style');
                });
            }
            return __this;
        }
    };
    
    /**
     * bootstrap-multiselect
     * 
     * -- class wrapper team-dropdown
     */ 
    RKfuncion.bootstapMultiSelect = {
        flagClass: '.bootstrap-multiselect',
        optionGlobal: {},
        init: function(option) {
            if (typeof option == 'undefined') {
                option = {};
            }
            var __this = this,
            optionDefault = {
                includeSelectAllOption: false,
                nonSelectedText: 'Choose items',
                allSelectedText: 'All',
                nSelectedText: 'items selected',
                numberDisplayed: 3,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                buttonText: function(options, select) {
                    if (options.length === 0) {
                        return 'Choose items';
                    } else if (options.length === options.context.length) {
                        return 'All' + ' (' + options.length + ')';
                    } else if (options.length > 3) {
                        return options.length + ' items selected';
                    } else {
                        var labels = [];
                        options.each(function() {
                            labels.push($(this).text().trim());
                        });
                        return labels.join(', ') + '';
                    }
                },
                onChange: function(optionChange) {
                    __this._removeSpace(optionChange.closest('select'));
                    if (typeof option.onChangeFunc != 'undefined') {
                        option.onChangeFunc(optionChange);
                    }
                },
                onDropdownShown: function(event) {
                    __this._overfollow($(event.currentTarget));
                },
                onDropdownHide: function(event) {
                    __this._overfollowClose($(event.currentTarget));
                }
            };
            var extOption = $.extend({}, optionDefault, option);
            __this.optionGlobal = extOption;
            $(__this.flagClass).multiselect(extOption);
            setTimeout(function () {
                $(__this.flagClass).each(function() {
                    __this._removeSpace($(this));
                });
            }, 100);
        },
        _removeSpace: function(selectDom, limit) {
            var __this = this,
            selectedOptions = selectDom.find('option:selected');
            if (typeof limit == 'undefined') {
                limit = __this.optionGlobal.numberDisplayed;
            }
            if (selectedOptions.length > limit) {
                return true;
            }
            var textSelected = '';
            selectedOptions.each(function (index) {
                if (index === 0) {
                    textSelected += $(this).text().trim();
                } else {
                    textSelected += ', ' + $(this).text().trim();
                }
            });
            if (textSelected) {
                setTimeout(function() {
                    selectDom.parent().find('.btn-group .multiselect-selected-text').text(textSelected);
                    selectDom.parent().find('.btn-group button.multiselect').attr('title', textSelected);
                }, 50);
            }
        },
        _overfollow: function (dom) {
            var wrapper = dom.closest('.multiselect2-wrapper.flag-over-hidden');
            if (wrapper.length) {
                wrapper.height(wrapper[0].scrollHeight);
            }
        },
        _overfollowClose: function(dom) {
            var wrapper = dom.closest('.multiselect2-wrapper.flag-over-hidden');
            if (wrapper.length) {
                wrapper.removeAttr('style');
            }
        }
    };
    
    /**
     * jquery validate extend validator function
     */
    RKfuncion.jqueryValidatorExtend = {
        greater: function() {
            if (typeof $.validator !== 'undefined' && 
                typeof $.validator.addMethod != 'undefined'
            ) {
                $.validator.addMethod('greater', function(value, element, param) {
                    return this.optional(element) || value > $(param).val();
                }, 'Please enter a value greater');
            }
        },
        lesser: function() {
            if (typeof $.validator !== 'undefined' && 
                typeof $.validator.addMethod != 'undefined'
            ) {
                $.validator.addMethod('lesser', function(value, element, param) {
                    return this.optional(element) || value < $(param).val();
                }, 'Please enter a value lesser');
            }
        },
        greaterEqual: function() {
            if (typeof $.validator !== 'undefined' && 
                typeof $.validator.addMethod != 'undefined'
            ) {
                $.validator.addMethod('greaterEqual', function(value, element, param) {
                    return this.optional(element) || value >= $(param).val();
                }, 'Please enter a value greater');
            }
        },
        lesserEqual: function() {
            if (typeof $.validator !== 'undefined' && 
                typeof $.validator.addMethod != 'undefined'
            ) {
                $.validator.addMethod('lesserEqual', function(value, element, param) {
                    return this.optional(element) || value <= $(param).val();
                }, 'Please enter a value lesser');
            }
        }
    };
    
    /**
     * function add items and delete item
     * class container: add-items-container
     * class wrapper include items:  add-items-wapper
     * class wrapper button add: add-items-btn-add
     * class template: add-items-template
     * class button delete: add-items-btn-delete
     * class item: add-items-item
     * flag id incremt: xxx
     */
    RKfuncion.addItems = {
        indexIncrement: 0,
        itemNewOrgHtml: '',
        flagContainer: '.add-items-container',
        flagWrapper: '.add-items-wapper',
        flagBtnAdd: '.add-items-btn-add',
        flagTemplate: '.add-items-template',
        flagItem: '.add-items-item',
        flagBtnDelete: '.add-items-btn-delete',
        dataProcess: {},
        option: {},
        init: function(option) {
            var __this = this;
            __this.dataProcess = {
                flagWrapper: __this.flagContainer + ' ' + __this.flagWrapper,
                flagBtnAdd: __this.flagContainer + ' ' + __this.flagBtnAdd,
                flagTemplate: __this.flagContainer + ' ' + __this.flagTemplate,
                flagItem: __this.flagContainer + ' ' + __this.flagItem,
                flagBtnDelete: __this.flagContainer + ' ' + __this.flagBtnDelete
            };
            if (!$(__this.flagContainer).length || 
                !$(__this.flagContainer + ' ' + __this.flagWrapper).length || 
                !$(__this.flagContainer + ' ' + __this.flagBtnAdd).length || 
                !$(__this.flagContainer + ' ' + __this.flagTemplate).length || 
                !$(__this.flagContainer + ' ' + __this.flagItem).length
            ) {
                return false;
            }
            var domContainer = $(__this.flagContainer);
            __this.itemNewOrgHtml = domContainer.find(__this.flagTemplate).html();
            domContainer.find(__this.flagTemplate).remove();
            // add new item to wrapper
            var quotationNewClone = __this.itemNewOrgHtml.replace(/xxx/g, __this.indexIncrement);
            __this.indexIncrement++;
            domContainer.find(__this.flagWrapper).append(quotationNewClone);
            __this._checkDeleteItem(domContainer);
            // option init
            if (typeof option == 'undefined') {
                option = {};
            }
            __this.option = option;
            // call action
            __this._addAction();
            __this._deleteAction();
        },
        _addAction: function() {
            var __this = this;
            $(document).on('click', __this.dataProcess.flagBtnAdd, function(event) {
                event.preventDefault();
                var quotationNewClone = __this.itemNewOrgHtml.replace(/xxx/g, __this.indexIncrement),
                    domContainer = $(this).closest(__this.flagContainer);
                domContainer.find(__this.flagTemplate).remove();
                __this.indexIncrement++;
                
                domContainer.find(__this.flagWrapper)
                    .append(quotationNewClone);
                __this._checkDeleteItem(domContainer);
            });
        },
        _deleteAction: function() {
            var __this = this;
            $(document).on('click', __this.dataProcess.flagBtnDelete, function(event) {
                event.preventDefault();
                var domContainer = $(this).closest(__this.flagContainer);
                domContainer.find(__this.flagTemplate).remove();
                $(this).closest(__this.flagItem).remove();
                __this._checkDeleteItem(domContainer);
            });
        },
        _checkDeleteItem: function(domContainer) {
            var __this = this;
            if (__this.option.isAllowDeleteAll) {
                return true;
            }
            if (domContainer.find(__this.flagItem).length > 1) {
                domContainer.find(__this.flagBtnDelete).removeClass('hidden');
            } else {
                domContainer.find(__this.flagBtnDelete).addClass('hidden');
            }
        }
    };
/**
 * get team dev tree path
 */
RKfuncion.teamTree = {
    treePath: {},
    teamDev: [],
    treeParentTeamDev: [],
    html: null,
    selectedIds: [],
    init: function (treePath, selectedIds, option) {
        var that = this;
        that.treePath = treePath;
        if (typeof selectedIds === 'undefined' || !selectedIds) {
            selectedIds = [];
        }
        that.option = typeof option === 'object' ? option : {};
        that.selectedIds = selectedIds;
        that.html = [];
        that._getTeamDev();
        that._getOptionRecursive(0, 0);
        return that.html;
    },
    // call recursive to call option select
    _getOptionRecursive: function(idParent, level) {
        var __this = this;
        if (typeof __this.treePath[idParent] === 'undefined' ||
            !__this.treePath[idParent].child.length
        ) {
            return null;
        }
        var index, jndex, nameOption, itemChild, disabled, idChild,
            children = __this.treePath[idParent].child;
        for (index in children) {
            idChild = children[index];
            itemChild = __this.treePath[idChild];
            disabled = false;
            if (typeof __this.treePath[idChild] === 'undefined') {
                continue;
            }
            // not dev team && parent not dev team
            if (__this.treeParentTeamDev.indexOf(idChild) === -1 && 
                !itemChild.data.is_soft_dev
            ) {
                continue;
            }
            // not is soft dev, in tree team dev => disabled
            if (!itemChild.data.is_soft_dev) {
                disabled = true;
            }
            if (__this.option.noSpace) {
                nameOption = itemChild.data.name;
            } else {
                nameOption = '';
                for (jndex = 0; jndex < level; jndex++) {
                    nameOption += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                nameOption += itemChild.data.name;
            }
            __this.html.push({
                id: idChild,
                label: RKfuncion.general.parseHtml(nameOption),
                disabled: disabled,
                selected: __this.selectedIds.indexOf(idChild) > -1
            });
            __this._getOptionRecursive(idChild, level+1);
        }
    },
    /**
     * get all id team format tree avai dev
     */
    _getTeamDev: function() {
        var __this = this, index, item, j;
        for (index in __this.treePath) {
            item =__this.treePath[index];
            if (item.data.is_soft_dev) {
                // push dev team
                __this.teamDev.push(index);
                //push dev parents dev team
                for (j in item.parent) {
                    if (__this.treeParentTeamDev.indexOf(item.parent[j]) === -1) {
                        __this.treeParentTeamDev.push(item.parent[j]);
                    }
                }
            }
        }
        return __this.teamDev;
    },
    /**
     * render html option
     *
     * @return {String}
     */
    renderOptionSelect: function () {
        var that = this,
            htmlOptionSelect = '',
            selected, disabled;
        $.each(that.html, function(i, v) {
            selected = v.selected ? ' selected' : '';
            disabled = v.disabled ? ' disabled' : '';
            htmlOptionSelect += '<option value="'+v.id+'"'+disabled+''
                +selected+'>' + v.label+'</option>';
        });
        return htmlOptionSelect;
    },
};

// menu active
RKfuncion.showActiveMenu = {
    init: function () {
        var that = this,
        path = window.location.pathname.replace(/^\/*|\/*$/gi, '');
        var result = that.activeFlag(path);
        if (!result) {
            that.activeAnalyze(path);
        }
    },
    activeClass: function (domActive) {
        if (!domActive.length) {
            return false;
        }
        domActive = domActive.parents('li').slice(-1);
        domActive.addClass('active');
        return true;
    },
    activeFlag: function (path) {
        if (!siteConfigGlobal.menu_active) {
            return false;
        }
        var domActive = $('[data-menu-main] > li > a[data-menu-slug="'+siteConfigGlobal.menu_active+'"]');
        this.activeClass(domActive);
        return true;
    },
    activeAnalyze: function (path) {
        var that = this,
        prefix = path,
        domain = window.location.origin,
        domActive,
        result;
        if (prefix) {
            prefix = '/' + prefix;
        }
        while(1) {
            domActive = $('[data-menu-main] a[href^="'+(domain+prefix)+'"]:first');
            result = that.activeClass(domActive);
            if (result) {
                return true;
            }
            if (!/\//.test(prefix)) {
                return false;
            }
            prefix = prefix.replace(/\/[0-9a-zA-Z_-]*$/,'');
        }
        return false;
    },
    slugify: function (text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    },
};
})(jQuery);

/**
 * select2 reload and trim text result
 */
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
    
    jQuery('.select-search').each(function(i,k){
        var text = jQuery(this).find('option:selected').text().trim();
        jQuery(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
    });
    jQuery('.select-search').on('select2:select', function (evt) {
        var text = jQuery(this).find('option:selected').text().trim();
        jQuery(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
    });
}

/**
 * get date format
 * 
 * @param {datetime} date
 * @param {string} format
 * @returns {String}
 */
function getDateFormat(date, format) {
    if (format == 'Y') {
        return date.getFullYear();
    }
    if (format == 'M/Y') {
        return (date.getMonth() + 1 ) + '/' + date.getFullYear();
    }
    return '';
}

/**
 * get array format unicode
 * 
 * @param {array} arrayData
 * @returns {Array}
 */
function getArrayFormat(arrayData) {
    if (! arrayData) {
        return [];
    }
    for (i in arrayData) {
        for (iItem in arrayData[i]) {
            if (arrayData[i][iItem] != undefined && arrayData[i][iItem]) {
                arrayData[i][iItem] = jQuery.parseHTML(arrayData[i][iItem])[0].nodeValue;
            }
        }
    }
    return arrayData;
}

(function($) {
    $.fn.clickWithoutDom = function(option) {
        if (option == undefined || 
            option.container == undefined || 
            option.except == undefined
        ) {
            return false;
        }
        if (option.type == undefined) {
            option.type = 0;
        }
        $(document).mouseup(function (e){
            if (option.except.is(e.target)
                || option.except.has(e.target).length !== 0
            ){
                return false;
            } else if (! option.container.is(e.target)
                && option.container.has(e.target).length === 0
            ){
                switch (option.type) {
                    case 1:
                        $("body").removeClass('sidebar-open').removeClass('sidebar-collapse').trigger('collapsed.pushMenu');
                        break;
                    case 2:
                        if (typeof $.AdminLTE != 'undefined') {
                            $.AdminLTE.controlSidebar.close($('.right-sidebar-control'),true);
                        }
                        break;
                    case 3:
                        return true;
                }
            }
        });
        $(document).keyup(function(e) {
            if (e.keyCode == 27) {
                switch (option.type) {
                    case 1:
                        $("body").removeClass('sidebar-open').removeClass('sidebar-collapse').trigger('collapsed.pushMenu');
                        break;
                    case 2:
                        if (typeof $.AdminLTE != 'undefined') {
                            $.AdminLTE.controlSidebar.close($('.right-sidebar-control'),true);
                        }
                        break;
                    case 3:
                        return true;
                }
            }
        });
    };
    
    /**
     * check click without dom
     */
    var clicky;
    $(document).mousedown(function(e) {
        clicky = e.target;
    });
    $(document).mouseup(function() {
        clicky = null;
    });
    $.fn.isClickWithoutDom = function(option) {
        if (option == undefined || 
            option.container == undefined || 
            option.except == undefined
        ) {
            return false;
        }
        if (option.type == undefined) {
            option.type = 0;
        }
        if (option.except.is(clicky)
            || option.except.has(clicky).length !== 0
        ){
            return false;
        } else if (! option.container.is(clicky)
            && option.container.has(clicky).length === 0
        ) {
            return true;
        }
    };
    /*
     * set z-index while hover main menu
     */
    $('#main-heaer-top ul.navbar-nav>li').mouseenter(function () {
        $('#main-heaer-top').addClass('z-index-2000');
    }).mouseleave(function () {
        $('#main-heaer-top').removeClass('z-index-2000');
    });
})(jQuery);

jQuery(document).ready(function ($) {
//    $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function (event) {
//        event.preventDefault();
//        event.stopPropagation();
//        if ($(this).parent().hasClass('open')) {
//            $(this).parent().removeClass('open');
//            $(this).parent().find('li.dropdown-submenu').removeClass('open');
//        } else {
//            $(this).parent().addClass('open');
//        }
//    });
    
    //modal delete confirm  '.delete-confirm'
    function modalDeleteConfirm(flagClassButton) {
        $('.' + flagClassButton).removeAttr('disabled');
        var buttonClickShowModal;
        $(document).on('click touchstart', '.' + flagClassButton, function (event) {
            if($(this).hasClass('process')) { //check flag processed
                return true;
            }
            event.preventDefault();
            buttonClickShowModal = $(this);
            $(this).addClass('process'); //set flag processing cofirm
            $('#modal-' + flagClassButton).modal('show');
        });
        $('#modal-' + flagClassButton).on('show.bs.modal', function (e) {
            $(this).find('.modal-footer .btn-ok').show();
            notification = buttonClickShowModal.data('noti');
            warning = buttonClickShowModal.attr('data-warning');
            if (buttonClickShowModal.hasClass('btn-remove-profile-team')) {
                notification = buttonClickShowModal.data('noti-confirm');
            }
            if(warning && buttonClickShowModal.hasClass('is-disabled')) {
                $(this).find('.modal-body .text-change').show().html(warning);
                $(this).find('.modal-body .text-default').hide().html(warning);
                $(this).find('.modal-footer .btn-ok').hide();
            } else {
                if (notification) {
                    $(this).find('.modal-body .text-change').show().html(notification);
                    $(this).find('.modal-body .text-default').hide().html(notification);
                } else {
                    $(this).find('.modal-body .text-change').hide();
                    $(this).find('.modal-body .text-default').show();
                }
            }
        });
        $('#modal-' + flagClassButton).on('hide.bs.modal', function (e) {
            buttonClickShowModal.removeClass('process'); //remove flag processing cofirm
        });
        $('#modal-' + flagClassButton + ' .modal-footer button').on('click touchstart', function (e) {
            if ($(this).hasClass('btn-ok')) {
                buttonClickShowModal.trigger('click');
                $('#modal-' + flagClassButton).modal('hide');
                if (buttonClickShowModal.hasClass('btn-remove-profile-team')) {
                    var teamLength = $('.box-form-team-position').children('.group-team-position').length;
                    if (teamLength > 1) {
                        buttonClickShowModal.parents('.group-team-position').remove();
                    }
                    if (teamLength === 2) {
                        $('.box-form-team-position .group-team-position .input-remove').addClass('warning-action');
                        $('.box-form-team-position .group-team-position .input-remove').removeClass('warn-confirm');
                    }
                    checkDisplayResponsibleTeam();
                }
                return true;
            }
            $('#modal-' + flagClassButton).modal('hide');
            return false;
        });
    }
    modalDeleteConfirm('delete-confirm');
    modalDeleteConfirm('warn-confirm');
    
    /**
     * model warning, success
     */
    var buttonClickShowModalWarning;
    $(document).on('click touchstart', '.warning-action, .success-action', function () {
        buttonClickShowModalWarning = $(this);
        if (buttonClickShowModalWarning.hasClass('warning-action')) {
            $('#modal-warning-notification').modal('show');
        } else {
            $('#modal-success-notification').modal('show');
        }
        return false;
    });
    $('#modal-warning-notification, #modal-success-notification').on('show.bs.modal', function () {
        if (typeof notification === 'undefined') {
            var notification;
        }
        if (buttonClickShowModalWarning && buttonClickShowModalWarning.length) {
            notification = buttonClickShowModalWarning.attr('data-noti');
        }
        if (notification) {
            $(this).find('.modal-body .text-change').show().html(notification);
            $(this).find('.modal-body .text-default').hide().html(notification);
        } else {
            $(this).find('.modal-body .text-change').hide();
            $(this).find('.modal-body .text-default').show();
        }
        buttonClickShowModalWarning = null;
    });
    
    /**
     * form input dropdown
     */
    $('.form-input-dropdown .input-menu a').click(function(event) {
        event.preventDefault();
        var textHtml = $(this).html();
        var dataValue = $(this).data('value');
        var wrapper = $(this).closest('.form-input-dropdown');
        wrapper.find('.input-show-data span').html(textHtml);
        wrapper.find('input').removeAttr('disabled');
        wrapper.find('.input').val(dataValue);
        wrapper.closest('td').find('>input').prop('disabled', false);
    });
});

jQuery(document).ready(function($) {
    /* filter-grid action */
    // get params from filter input
    function getSerializeFilter(dom)
    {
        var filterWrapper = dom.closest('.filter-wrapper');
        var filterUrl = filterWrapper.data('url');
        var urlSubmitFilter = typeof filterUrl == 'undefined' ? currentUrl : filterUrl;
        var valueFilter, nameFilter, params;
        params = '';
        if (filterWrapper.length > 0) {
            filterWrapper.find('.filter-grid').each(function(i,k) {
                valueFilter = $(k).val();
                nameFilter = $(k).attr('name');
                if (valueFilter && nameFilter) {
                    params += $(k).serialize() + '&';
                }
            });
        } else {
            $('.filter-grid').each(function(i,k) {
                valueFilter = $(k).val();
                nameFilter = $(k).attr('name');
                if (valueFilter && nameFilter) {
                    params += $(k).serialize() + '&';
                }
            });
        }
        params += 'current_url=' + encodeURIComponent(urlSubmitFilter);
        return params;
    }    
    //filter request with param filter
    function filterRequest(dom)
    {
        data = getSerializeFilter(dom);
        $('.btn-search-filter .fa').removeClass('hidden');
        $.ajax({
            url: baseUrl + 'grid/filter/request',
            type: 'GET',
            data: data,
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                }
            }
        });
    }
    //filter pager with param filter
    function filterPager(dataSubmit, domWrapper)
    {
        if (dataSubmit == undefined) {
            dataSubmit = {};
        }
        if (typeof domWrapper == 'undefined') {
            domWrapper = $('.table-grid-data:first');
        }
        //filter grid data ajax have pagination
        if (typeof domWrapper !== 'undefined' && domWrapper.parents('.grid-data-query').length) {
            domWrapperParent = domWrapper.parents('.grid-data-query');
            domWrapperParent.find('.block-loading-icon').removeClass('hidden');
            urlSubmit = domWrapperParent.attr('data-url');
            gridAjax = true;
        } else { //filter grid data
            domWrapperParent = domWrapper.parents('.box:first');
            if (domWrapperParent.length < 1) {
                domWrapperParent = domWrapper.closest('.tab-pager');
            }
            urlSubmit = baseUrl + 'grid/filter/pager';
            gridAjax = false;
        }
        if (dataSubmit.page == undefined || ! dataSubmit.page) {
            dataSubmit.page = parseInt(domWrapperParent.find('.grid-pager .form-pager input[name=page]').val());
        }
        if (dataSubmit.dir == undefined || ! dataSubmit.dir) {
            if (domWrapperParent.find('.form-dir-order input[name=dir]').val()) {
                dataSubmit.dir = domWrapperParent.find('.form-dir-order input[name=dir]').val();
            }
        }
        if (dataSubmit.order == undefined || ! dataSubmit.order) {
            if (domWrapperParent.find('.form-dir-order input[name=order]').val()) {
                dataSubmit.order = domWrapperParent.find('.form-dir-order input[name=order]').val();
            }
        }
        var filterUrl = domWrapper.closest('.filter-wrapper').data('url');
        var urlSubmitFilter = typeof filterUrl == 'undefined' ? currentUrl : filterUrl;
        dataSubmit.limit = domWrapperParent.find('.grid-pager select[name=limit] option:selected').data('value');
        if (!gridAjax) {
            dataSubmit = {'filter_pager': dataSubmit, 'current_url': urlSubmitFilter};
        }
        $('.btn-search-filter .fa').removeClass('hidden');
        $.ajax({
            url: urlSubmit,
            type: 'GET',
            data: dataSubmit,
            dataType: 'json',
            success: function(data) {
                if (!gridAjax) {
                    window.location.reload();
                } else {
                    if (typeof data.html != 'undefined') {
                        domWrapperParent.find('.grid-data-query-table').html(data.html);
                    }
                    domWrapperParent.find('.fa-refresh').addClass('hidden');
                }
            },
            complete: function() {
                domWrapperParent.find('.block-loading-icon').addClass('hidden');
                domWrapperParent.find('.fa-refresh').addClass('hidden');
            }
        });
    }
    
    //input filter grid key down - request filter action
    $(document).on('keydown','input.filter-grid',function(event) {
        if(event.which == 13) {
            filterRequest($(this));
            return false;
        }
    });

    // input filter grid change - request filter action
    $(document).on('change','select.select-grid',function(event) {
        filterRequest($(this));
    });
    RKfuncion.filterGrid = {
        filterRequest: function(dom) {
            filterRequest(dom);
        }
    };
    
    //reset filter
    $(document).on('click touchstart','.btn-reset-filter',function(event) {
        $('.btn-reset-filter .fa').removeClass('hidden');
        $('.filter-input-grid input.filter-grid').val('');
        $('.select-search.select-grid').val('');
        var filterUrl = $(this).closest('.filter-wrapper').data('url');
        var urlSubmitFilter = typeof filterUrl == 'undefined' ? currentUrl : filterUrl;
        $.ajax({
            url: baseUrl + 'grid/filter/remove',
            type: 'GET',
            data: 'current_url=' + urlSubmitFilter,
            success: function() {
                window.location.reload();
            }
        });
        return false;
    });
    
    //search filter button
    $(document).on('click touchstart','.btn-search-filter',function(event) {
        filterRequest($(this));
        return false;
    });
    
    //pager 
    $(document).on('change', '.grid-pager select[name=limit]', function(event) {
        event.preventDefault();
        filterPager({
            page: 1
        }, $(this));
    });
    $(document).on('click touchstart', '.grid-pager .pagination a', function(event) {
        if ($(this).hasClass('disabled') || $(this).parent().hasClass('disabled')) {
            return false;
        }
        page = parseInt($(this).data('page'));
        if (page) {
            event.preventDefault();
            filterPager({
                page: page
            }, $(this));
        }
    });
    $(document).on('keypress', '.grid-pager .pagination .form-pager input[name=page]', function(event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            page = parseInt($(this).val());
            filterPager({
                page: page
            }, $(this));
        }
    });
    
    //sort order
    $('.sorting').on('click touchstart', function(event) {
        order = $(this).data('order');
        dir = $(this).data('dir');
        if (! order || ! dir) {
            return;
        }
        filterPager({
            order: order,
            dir: dir
        }, $(this));
    });
    
    /* ---- endfilter-grid action */
    RKfuncion.showActiveMenu.init();
    //menu mobile
    $('.main-header .dropdown-menu').on('mouseover', function(event) {
        $(this).parents('li.dropdown').addClass('hover');
    });
    $('.main-header .dropdown-menu').on('mouseleave', function(event) {
        $(this).parents('li.dropdown').removeClass('hover');
    });
    $(window).load(function() {
        var domOpenChild = '<i class="fa fa-angle-left pull-right"></i>',
            menuMobileClone = $('#navbar-collapse .navbar-nav').clone();
        $('#navbar-collapse .navbar-nav > li').hover(function() {
            $(this).siblings('li').removeClass('open');
        });
        menuMobileClone.find('li:has(ul)',this).each(function() {
            $(this).children('a').append(domOpenChild);
            $(this).children('a').removeAttr('class').removeAttr('data-toggle').removeAttr('aria-expanded');
            $(this).addClass('treeview');
            $(this).removeClass('dropdown');
            $(this).removeClass('dropdown-submenu');
            $(this).children('ul').removeClass('dropdown-menu');
            $(this).children('ul').addClass('treeview-menu');
        });
        $('.main-sidebar .sidebar .sidebar-menu').html(menuMobileClone.html());
        if (typeof $.AdminLTE != 'undefined') {
            $.AdminLTE.layout.fix();
        }

        $('.sidebar-toggle').click(function(event) {
            windowWidth = $(window).width();
            if (windowWidth > optionCustomR.size.adminlte_sm) {
                if ($("body").hasClass('sidebar-open')) {
                    $("body").removeClass('sidebar-open').removeClass('sidebar-collapse').trigger('collapsed.pushMenu');
                } else {
                    $("body").addClass('sidebar-open').trigger('expanded.pushMenu');
                }
            }
        });

        $('.main-sidebar .sidebar-menu  li.treeview  a').on('click touchstart', function(event) {
            windowHeight = $(window).height();
            sidebarHeight = $(".sidebar").height();
            contentHeight = $(".content-wrapper").height();
            if (! $(this).parent().hasClass('active')) { //menu open
                if (windowHeight >= sidebarHeight) {
                    setTimeout(function () {
                        $(".content-wrapper").css('min-height', windowHeight);
                    }, 600);
                }
            } else {
                if (windowHeight < sidebarHeight) {
                    $(".content-wrapper, .right-side").css('min-height', sidebarHeight);
                }
            }
        });

        //menu setting
        menuMobileSettingClone = $('.main-header .navbar-custom-menu li.setting.dropdown > ul.dropdown-menu').clone();
        menuMobileSettingClone.find('li:has(ul)',this).each(function() {
            $(this).children('a').append(domOpenChild);
            $(this).children('a').removeAttr('class').removeAttr('data-toggle').removeAttr('aria-expanded');
            $(this).addClass('treeview');
            $(this).removeClass('dropdown');
            $(this).removeClass('dropdown-submenu');
            $(this).children('ul').removeClass('dropdown-menu');
            $(this).children('ul').addClass('treeview-menu');
        });
        $('.control-sidebar .sidebar .sidebar-menu').html(menuMobileSettingClone.html());
        
        $().clickWithoutDom({
            container: $("aside.main-sidebar"),
            except: $('.sidebar-toggle'),
            type: 1
        });
        $().clickWithoutDom({
            container: $("aside.right-sidebar-control"),
            except: $('.menu-setting-sidebar'),
            type: 2
        });
    });
    //------------------end menu mobile
    
    /* table click tr */
    $('.tr-clickable > td:not(.tr-td-not-click)').click(function() {
        window.location.href = $(this).parent('tr').data('url');
    });
    /* ---------------end table click tr */
    
    var topBtnClick = $('.top-up');
    topBtnClick.click(function(event) {
        event.preventDefault();
        $('body, html').animate({
            scrollTop: 0
          }, 500);
    });
    if (topBtnClick.length) {
        if ($(window).scrollTop() < 100) {
            topBtnClick.stop().animate({
                'bottom': '-100px'
            }, 600);
        }
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                topBtnClick.stop().animate({
                    'bottom': '20px'
                }, 300);
            } else {
                topBtnClick.stop().animate({
                    'bottom': '-100px'
                }, 300);
            }
        });
    }
});

/*  table thead fixed  */ 
(function($){
    function calculatorWidthThead(domTable) {
        tbodyTrFirst = domTable.children('tbody');
        if (! tbodyTrFirst.length) {
            return;
        }
        tbodyTrFirst = tbodyTrFirst.children('tr:nth-child(2)');
        if (! tbodyTrFirst.length) {
            return;
        }
        tbodyTrFirst = tbodyTrFirst.children('td');
        if (! tbodyTrFirst.length) {
            return;
        }
        width = {};
        tbodyTrFirst.each(function(i) {
            width[i] = $(this).width();
        });
        return width;
    }
    
    function fixThead(thisWrapper, THeadDom, tdTheadDom) {
        thisWrapper.removeClass('fixing');
        topHeightThead = THeadDom.offset().top;
        $(window).scroll(function() {
            topScroll = $(window).scrollTop();
            if (topScroll > topHeightThead) {
                thisWrapper.addClass('fixing');
                widthTd = calculatorWidthThead(thisWrapper);
                if (! widthTd) {
                    thisWrapper.removeClass('fixing');
                } else {
                    tdTheadDom.each(function(i) {
                        $(this).width(widthTd[i]);
                    });
                }
            } else {
                thisWrapper.removeClass('fixing');
                tdTheadDom.removeAttr('style');
            }
        });
    }
    
    $.fn.tableTHeadFixed = function(object) {
        var thisWrapper = $(this),
            THeadDom = thisWrapper.children('thead');
        if (! THeadDom.length) {
            return;
        }
        tdTheadDom = THeadDom.children('tr');
        if (! tdTheadDom.length) {
            return;
        }
        tdTheadDom = tdTheadDom.children();
        if (! tdTheadDom.length) {
            return;
        }
        
        fixThead(thisWrapper, THeadDom, tdTheadDom);
        $(window).load(function() {
            if (thisWrapper.hasClass('fixing')) {
                widthTd = calculatorWidthThead(thisWrapper);
                tdTheadDom.each(function(i) {
                    $(this).width(widthTd[i]);
                });
            }
        });
        
        $(window).resize(function() {
            fixThead(thisWrapper, THeadDom, tdTheadDom);
        });
    };
})(jQuery);
/* -----end table thead fixed  */ 

(function($){
    //dom vertical center
    $.fn.verticalCenter = function(option) {
        var thisWrapper = $(this);
        optionDefault = {
            parent: true
        };
        option = $.extend(optionDefault, option);
        if (option.parent === true) {
            parentDom = thisWrapper.parent();
        } else {
            parentDom = $(option.parent);
            if (! parentDom.length) {
                return;
            }
        }
        heightParent = parentDom.outerHeight();
        heightThis = thisWrapper.outerHeight();
        placeHeight = heightParent / 2 - heightThis / 2;
        if (placeHeight < 0) {
            placeHeight = 0;
        }
        thisWrapper.css('margin-top', placeHeight + 'px');
        $(window).resize(function() {
            heightParent = parentDom.outerHeight();
            heightThis = thisWrapper.outerHeight();
            placeHeight = heightParent / 2 - heightThis / 2;
            if (placeHeight < 0) {
                placeHeight = 0;
            }
            thisWrapper.css('margin-top', placeHeight + 'px');
        });
    }; //end dom vertical center
    
    // preview image
    $.fn.previewImage = function(option) {
        var thisWrapper = $(this);
        if (option == undefined || ! option) {
            option = {};
        }
        srcDemo = thisWrapper.find('.image-preview > img').attr('src');
        optionDefault = {
            type: [ 'image/jpeg','image/png','image/gif'],
            size: 2048,
            default_image: srcDemo,
            message_size: 'File size is large',
            message_type: 'File type dont allow',
        };
        option = $.extend(optionDefault, option);
        //exec src image preview
        
        //var allowType = ['image/jpeg','image/png','image/gif'];
        var domInputFile = thisWrapper.find(".img-input input[type=file]");
        function readURL(input) {
            if (input.files && input.files[0]) {
                var fileUpload = input.files[0];
                if($.inArray(fileUpload.type, option.type) < 0) {
//                    thisWrapper.find('.image-preview > img').attr('src', option.default_image);
                    domInputFile.val('');
                    alert(option.message_type);
                } else if (fileUpload.size / 1000 > option.size) {
                    thisWrapper.find('.image-preview > img').attr('src', option.default_image);
                    domInputFile.val('');
                    alert(option.message_size);
                }
                else {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        thisWrapper.find('.image-preview > img').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(fileUpload);
                }
            }
        }
        domInputFile.change(function(){
            readURL(this);
        });
    };
    
    //tooltip dom
    $('[data-tooltip="true"]').tooltip();
    
    //submit form disable
    $('form').submit(function () {
        if ($(this).hasClass('no-disabled') || $(this).hasClass('form-pager')) {
            //not action
        } else if ($(this).hasClass('no-validate')) {
            $(this).find('[type=submit]:not(.no-disabled)').attr('disabled', 'disabled');
        } else {
            if ($(this).valid()) {
                $(this).find('[type=submit]:not(.no-disabled)').attr('disabled', 'disabled');
            } else {
                $(this).find('[type=submit]').removeAttr('disabled');
            }
        }
    });
    
    /**
     * reset form validation
     * 
     * @param array option array form
     */
    $.fn.formResetVaidation = function(option) {
        if (option == undefined || ! option || ! option.length) {
            return;
        }
        for (i in option) {
            if (option[i] == undefined || ! option[i]) {
                continue;
            }
            option[i].resetForm();
            formCurrent = option[i].currentForm;
            if (formCurrent && formCurrent.length && $(formCurrent).length) {
                $(formCurrent).find('input, textarea').removeClass('error');
            }
        }
    };
    
    /**
     * get serialize param of inputs
     * 
     * @param {object} option
     * @returns {unresolved|String}
     */
    $.fn.getFormDataSerializeFilter = function (option) {
        if (option == undefined || 
            option.dom == undefined || 
            !option.dom ||
            !option.dom.length
        ) {
            return null;
        }
        dataParams = {};
        option.dom.each(function(i,k) {
            valueFilter = $.trim($(this).val());
            nameFilter = $(this).attr('name');
            if (valueFilter && nameFilter) {
                dataParams[nameFilter] = valueFilter;
            }
        });
        return $.param(dataParams);
    };
    
    /**
     * action button filter ajax action
     */
    $.fn.filterAjaxActionButton = function () {
        $(document).on('click touchstart','.filter-action .btn-reset-filter-ajax',function(event) {
            event.preventDefault();
            tableFilter = $(this).parent('.filter-action').data('table');
            tableFilter = '.' + tableFilter;
            if ($(tableFilter).length) {
                $(tableFilter).find('.filter-input-grid input.filter-grid-ajax').val('');
            }
        });
    };
    
    /**
     * caculator positoin menu
     */
    $('ul.dropdown-menu [data-toggle=dropdown]').on('click mouseenter', function(event) {
        // Avoid following the href location when clicking
        event.preventDefault(); 
        // Avoid having the menu to close when clicking
        event.stopPropagation(); 
        menu = $(this).siblings("ul:first");
        if (menu.length) {
            parent = $(this).parent();
            widthParent = parent.width();
            leftParent = parent.offset().left;
            menupos = $(menu).offset();
            if (widthParent + leftParent + menu.width() > $(window).width()) {
                menu.css({ left: -(widthParent-1) });
            } else {
                menu.css({ left: widthParent });
            }
        }
    });
    
    $.fn.selectText = function(){
        var doc = document;
        var element = this[0];
        if (doc.body.createTextRange) {
            var range = document.body.createTextRange();
            range.moveToElementText(element);
            range.select();
        } else if (window.getSelection) {
            var selection = window.getSelection();        
            var range = document.createRange();
            range.selectNodeContents(element);
            selection.removeAllRanges();
            selection.addRange(range);
        }
    };
    
    RKfuncion.formSubmitAjax.init();
    RKfuncion.general.modalBodyPadding();

    // add jquery funtion short content
    $.fn.shortedContent = function (settings) {

        var config = {
                showChars: 60,
                showLines: 3,
                ellipsesText: "...",
                moreText: (typeof textShowMore == 'undefined') ? 'show more' : textShowMore,
                lessText: (typeof textShowLess == 'undefined') ? 'show less' : textShowLess,
        };

        if (settings) {
                $.extend(config, settings);
        }

        $(document).off("click", '.morelink');

        $(document).on(
            {
                click: function () {
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
                },
            },
            '.morelink'
        );

        return this.each(function () {
                var $this = $(this);
                if ($this.hasClass("shortened")) {
                    return;
                }

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
                    var html = c + '<span class="moreellipses">' + config.ellipsesText + ' </span><span class="morecontent"><span>' + h + '</span>';
                    if (config.moreText) {
                        html += ' <a href="#" class="morelink">' + config.moreText + '</a>';
                    }
                    html += '</span>';
                    $this.html(html);
                    $(".morecontent span").hide();
                }
                $this.removeClass('hidden');
        });

    };

})(jQuery);

function isEmail(value) {
    re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    return re.test(value);
}

/**
 * Input only number 
 */
$(document).on("keydown", ".num", function (e) {
    // Allow: backspace, delete, tab, escape, enter
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
         // Allow: Ctrl+A, Command+A
        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
         // Allow: home, end, left, right, down, up
        (e.keyCode >= 35 && e.keyCode <= 40)) {
             // let it happen, don't do anything
             return;
    }

    // Allow . if has not class `int`
    if (!$(this).hasClass('int') && e.keyCode === 190) {
        return;
    }
    // negative => allow "-"
    if ($(this).hasClass('neg') && (e.keyCode === 109 || e.keyCode === 189) && $(this).val().toString().indexOf('-') === -1) {
        return;
    }
    // Ensure that it is a number and stop the keypress
    var condition = (e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105);
    if ($(this).hasClass('num-with-forward-slash')) {
        if ($(this).val().indexOf("/") >= 0 && (e.keyCode == 191 || e.keyCode == 111)) {
            e.preventDefault();
        }
        condition = condition && e.keyCode != 191 && e.keyCode != 111;
    }
    if (condition) {
        e.preventDefault();
    }
});

/**
 * Rounding number to {digit} digits after comma
 * @param {float} number
 * @returns {float}
 */
function rounding(number, digit) {
    var n = parseFloat(number); 
    number = Math.round(n * 1000)/1000; 
    return number.toFixed(digit);
}

function numberWithCommas(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

/**
 * if word is numeric then format as salary
 * 
 * @param {object} elem
 */
function numberFormat(elem) {
    $(elem).keyup(function () {

        var value = $(this).val();
        value = value.replace(/,/g, ''); // remove commas from existing input
        var arrValue = value.split(" ");
        var length = arrValue.length;
        for (var i = 0; i < length; i++) {
            if(Math.floor(arrValue[i]) == arrValue[i] && $.isNumeric(arrValue[i])) {
                arrValue[i] = numberWithCommas(arrValue[i]);
            }
        }

        $(this).val(arrValue.join(" "));
    });
}

/**
 * addClass() to first position of multiple classes
 * @param {element} sel
 * @param {string} strClass
 */
function prependClass(sel, strClass) {
    var $el = jQuery(sel);

    /* prepend class */
    var classes = $el.attr('class');
    classes = strClass +' ' +classes;
    $el.attr('class', classes);
}

/**
 * Check duplicate element in array
 * 
 * @param {array} array
 * @returns {Boolean}
 */
function checkDuplicate(array) {
    var arrayTrim = [];
    $.each(array, function(){
        arrayTrim.push($.trim(this.toUpperCase()));
    });
    var array = arrayTrim.sort(); 
    var flag = false;
    for (var i = 0; i < array.length - 1; i++) {
        if (array[i + 1] == array[i]) {
            flag = true;
            break;
        }
    }
    return flag;
}

$("a:contains('Music')").closest('li').find('ul li a').each(function() {
    if($(this).text() == 'Order') {
        $(this).attr('target', '_blank');
    }
});

var RKSession = {
    setItem: function (key, value) {
        if (typeof Storage == 'undefined') {
            return;
        }
        sessionStorage.setItem(key, value);
    },
    getItem: function (key) {
        if (typeof Storage == 'undefined') {
            return null;
        }
        return sessionStorage.getItem(key);
    },
    setRawItem: function (key, value) {
        if (typeof Storage == 'undefined') {
            return;
        }
        sessionStorage.setItem(key, JSON.stringify(value));
    },
    getRawItem: function (key) {
        if (typeof Storage == 'undefined') {
            return [];
        }
        var value = sessionStorage.getItem(key);
        if (!value) {
            return [];
        }
        return JSON.parse(value);
    },
    removeItem: function(key) {
        if (typeof Storage == 'undefined') {
            return;
        }
        sessionStorage.removeItem(key);
    },
};

function get2Digis(num) {
    return (num < 10 ? '0' : '') + num;
}

/*
 * increment total item pagination
 */
function incPaginateTotal(filterWrapper){
    var elTotal = $('.data-pager-info .total');
    if (typeof filterWrapper != 'undefined') {
        elTotal = filterWrapper.find('.data-pager-info .total');
    }
    if (elTotal.length < 1) {
        return;
    }
    var oldNum = parseInt(elTotal.text());
    elTotal.text(oldNum + 1);
    if (oldNum === 0) {
        $('.data-pager-info .num_page').text(1);
    }
}

/*
 * decrement total item pagination
 */
function decPaginateTotal(filterWrapper){
    var elTotal = $('.data-pager-info .total');
    if (typeof filterWrapper != 'undefined') {
        elTotal = filterWrapper.find('.data-pager-info .total');
    }
    if (elTotal.length < 1) {
        return;
    }
    var oldNum = parseInt(elTotal.text());
    if (oldNum < 1) {
        return;
    }
    elTotal.text(oldNum - 1);
    if (oldNum === 1) {
        $('.data-pager-info .num_page').text(0);
    }
}

/*
 * re-bind event mouse wheel if overflow-x of select2 is auto
 */
var prevTagScrollEvent = null;
document.addEventListener('scroll', function (event) {
    if (prevTagScrollEvent !== event.target
        && event.target.classList && event.target.classList[0] === 'select2-results__options'
        && event.target.scrollWidth > event.target.clientWidth
    ) {
        $('.select2-results__options').unmousewheel();
        $('.select2-results__options').bind('mousewheel', function (e) {
            var isAtTop = e.deltaY > 0 && this.scrollTop <= 0;
            var isAtBottom = e.deltaY <= 0 && this.scrollHeight - this.scrollTop <= this.clientHeight;
            if(isAtTop) {
                $(this).scrollTop(0);
                e.preventDefault();
                e.stopPropagation();
            } else if (isAtBottom) {
                $(this).scrollTop(this.scrollHeight - this.clientHeight);
                e.preventDefault();
                e.stopPropagation();
            } else {
                // nothing
            }
        });
        prevTagScrollEvent = event.target;
    }
}, true);

//custom class for bootstrap tooltip
(function ($) {
    if (typeof $.fn.tooltip.Constructor === 'undefined') {
        throw new Error('Bootstrap Tooltip must be included first!');
    }

    var Tooltip = $.fn.tooltip.Constructor;
    $.extend(Tooltip.DEFAULTS, {
        customClass: ''
    });
    var _show = Tooltip.prototype.show;
    Tooltip.prototype.show = function () {
        _show.apply(this, Array.prototype.slice.apply(arguments));
        if (this.options.customClass) {
            var $tip = this.tip()
            $tip.addClass(this.options.customClass);
        }
    };
})(window.jQuery);

function debounce(func, wait, immediate) {
    var _timeout;
    return function() {
        var context = this, args = arguments;
        var later = function() {
            _timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !_timeout;
        clearTimeout(_timeout);
        _timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

function htmlEntities(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/**
 * add method format date for class Date (contains Y: year, m: month, d: day, H: hour, i: minute, s: second)
 * @param {string} format
 * @returns {string}
 */
Date.prototype.format = function (format) {
    var yyyy = ('000' + this.getFullYear()).slice(-4),
        mm = ('0' + (this.getMonth() + 1)).slice(-2),
        dd = ('0' + this.getDate()).slice(-2),
        hh = ('0' + this.getHours()).slice(-2),
        ii = ('0' + this.getMinutes()).slice(-2),
        ss = ('0' + this.getSeconds()).slice(-2);
    return format.replace('Y', yyyy).replace('m', mm).replace('d', dd)
        .replace('H', hh).replace('i', ii).replace('s', ss);
};
/**
 * generate list dates in period time
 * @param {string} $start - format YYYY-mm-dd
 * @param {string} $end - format YYYY-mm-dd
 * @returns {array}
 */
function generateDatesInPeriod($start, $end) {
    if (!checkIsDate($start) || !checkIsDate($end) || $start > $end) {
        return [];
    }
    var $startDetail = $start.split('-');
    var $y = $startDetail[0], $m = $startDetail[1], $d = parseInt($startDetail[2]);
    var $aryDaysInMonth = {'01': 31, '03': 31, '05': 31, '07': 31, '08': 31, '10': 31, '12': 31, '04': 30, '06': 30, '09': 30, '11': 30};
    $aryDaysInMonth['02'] = ($y % 4 === 0 && $y % 100 !== 0 || $y % 400 === 0) ? 29 : 28;
    var $dateArray = [];
    while ($start <= $end) {
        $dateArray.push($start);
        if (++$d > $aryDaysInMonth[$m]) {
            $d = 1;
            if (++$m > 12) {
                $m = 1;
                $y++;
                if ($y === 10000) {
                    break;
                }
                $aryDaysInMonth['02'] = ($y % 4 === 0 && $y % 100 !== 0 || $y % 400 === 0) ? 29 : 28;
                $y = ('000' + $y).slice(-4);
            }
            $m < 10 && ($m = '0' + $m);
        }
        $start = $y + '-' + $m + '-' + ('0' + $d).slice(-2);
    }
    return $dateArray;
}

/**
 * check variable is date format YYYY-mm-dd
 * @param {string} $date
 * @returns {boolean}
 */
function checkIsDate($date) {
    if (! /\d{4}-\d{2}-\d{2}/.test($date)) {
        return false;
    }
    var $dateDetail = $date.split('-');
    var $y = $dateDetail[0], $m = $dateDetail[1], $d = $dateDetail[2];
    if ($m !== '02') {
        var $aryDaysInMonth = {
            '01': 31, '03': 31, '05': 31, '07': 31, '08': 31, '10': 31, '12': 31,
            '04': 30, '06': 30, '09': 30, '11': 30,
        };
        return $aryDaysInMonth[$m] && $d <= $aryDaysInMonth[$m];
    }
    var $daysInMonth = ($y % 4 === 0 && $y % 100 !== 0 || $y % 400 === 0) ? 29 : 28;
    return $d <= $daysInMonth;
}
