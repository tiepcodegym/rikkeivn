(function ($, RKExternal, window, RKfuncion) {
var varGlob = typeof globalPassModule === 'object' ? globalPassModule : {},
trans = typeof varGlob.trans === 'object' ? varGlob.trans : {};
var busy = {
    // init
    init: function () {
        var that = this;
        that.domList = $('[data-pager-list]');
        var progBar = $('[data-progress-dom="wrapper"]');
        that.filterInit();
        that.addFilterSkills();
        that.htmlProgrssItem = progBar.html();
        progBar.html('{htmlProgess}');
        that.htmlItem = $('[data-pager-item]')[0].outerHTML;
        that.pagerUrl = that.domList.data('pager-url');
        that.isProcess = false;
        $('[data-pager-item]').remove();
        that.formSearch = $('form#form-busy');
        that.icoLoading = that.formSearch.find('[data-ico-load="ajax"]');
        that.domNotResult = $('[data-pager-not]');
        that.domResult = $('[data-pager-result]');
        that.btnSearch = $('[data-pager-search-btn]');
        that.validate();
        that.plugin();
        that.widthProgress = 0;
        that.domPagerMore = $('[data-pager-more]');
        //action btn search
        that.btnSearch.click(function (e) {
            e.preventDefault();
            that.sendRequest();
        });
        //that.scrollLoadMore();
    },
    // send request ajax to search
    sendRequest: function () {
        var that = this;
        if (that.isProcess) {
            return true;
        }
        if (!that.formSearch.valid()) {
            return true;
        }
        if (that.ajaxLoadMore) {
            that.ajaxLoadMore.abort();
            that.ajaxLoadMore = null;
        }
        that.btnSearch.prop('disabled', true);
        that.icoLoading.removeClass('hidden');
        that.isProcess = true;
        $.ajax({
            url: that.pagerUrl,
            type: 'get',
            dataType: 'json',
            data: that.formSearch.find('[data-pager-input]').serialize(),
            success: function (data) {
                that.domList.html('');
                if (!data.status) {
                    RKExternal.notify(data.message, false);
                    return true;
                }
                that.calPeriodNumber(data.data, true);
                var count = 0;
                $.each (data.data, function (i, v) {
                    count++;
                    that.renderItem(v);
                });
                if (!count) {
                    that.domNotResult.removeClass('hidden');
                    that.domResult.addClass('hidden');
                } else {
                    that.domNotResult.addClass('hidden');
                    that.domResult.removeClass('hidden');
                    that.renderCount(data.count);
                }
                /*if (count < 50) { // no load more
                    that.domPagerMore.data('enableLoad', 0);
                    that.domPagerMore.addClass('hidden');
                } else { // enable load more
                    that.domPagerMore.data('enableLoad', 1);
                    that.domPagerMore.removeClass('hidden');
                }
                that.domPagerMore.data('page', 1);*/
            },
            error: function () {
                that.domList.html('');
                RKExternal.notify('System error', false);
            },
            complete: function () {
                that.isProcess = false;
                that.icoLoading.addClass('hidden');
                that.btnSearch.prop('disabled', false);
            },
        });
    },
    // render item after send request
    renderItem: function (data) {
        var that = this;
        data.data.email = data.data.email.replace(/@.*$/, '');
        data.data.url = varGlob.urlCV.replace(/xxx/gi, data.data.id);
        var htmlItem = that.htmlItem;
        $.each(data.data, function (key, value) {
            var reg = new RegExp('\{'+key+'\}', 'gi');
            htmlItem = htmlItem.replace(reg, value);
        });
        htmlItem = htmlItem.replace(/\{htmlProgess\}/gi, that.renderProgressBar(data.period));
        that.domList.append(htmlItem);
    },
    /**
     * render count busy employee
     *
     * @param {obj} data
     */
    renderCount: function (data) {
        if (typeof data !== 'object') {
            data = {};
        }
        $.each (data, function (i, v) {
            $('[data-count-busy="'+i+'"]').text(v);
        });
    },
    /**
     * render html progress bar item
     *
     * @param {obj} data
     * @return {String}
     */
    renderProgressBar: function (data) {
        var that = this,
            htmlProgressItem = '';
        $.each (data, function (date, effort) {
            var color;
            if (effort > 100) {
                color = 'red';
            } else if (effort > 80) {
                color = 'green';
            } else if (effort > 0) {
                color = 'yellow';
            } else {
                color = 'white';
            }
            htmlProgressItem += that.htmlProgrssItem.replace(/\{week\}/gi, date)
                .replace(/\{effort\}/gi, effort)
                .replace(/\{color\}/gi, color)
                .replace(/\{width\}/gi, that.widthProgress);
        });
        return htmlProgressItem;
    },
    /**
     * load more ajax
     *
     * @return {undefined}
     */
    scrollLoadMore: function () {
        var that = this;
        $(window).scroll(function () {
            if (!that.domPagerMore.data('enableLoad') ||
                that.domPagerMore.data('isLoading') ||
                that.isProcess
            ) {
                return true;
            }
            var heightScreen = $(window).scrollTop() + $(window).height(),
            heightDomMore = that.domPagerMore.offset().top;
            if (heightScreen < heightDomMore) {
                return true;
            }
            // load more ajax
            that.domPagerMore.data('isLoading', 1);
            var dataSend = that.formSearch.serialize(),
            page = parseInt(that.domPagerMore.data('page')+1);
            dataSend += '&page=' + page;
            that.ajaxLoadMore = $.ajax({
                url: that.pagerUrl,
                type: 'get',
                dataType: 'json',
                data: dataSend,
                success: function (data) {
                    if (!data.status) {
                        RKExternal.notify(data.message, false);
                        return true;
                    }
                    var count = 0;
                    $.each (data.data, function (i, v) {
                        count++;
                        that.renderItem(v);
                    });
                    if (count < 50) { // no load more
                        that.domPagerMore.data('enableLoad', 0);
                        that.domPagerMore.addClass('hidden');
                    } else { // enable load more
                        that.domPagerMore.data('enableLoad', 1);
                        that.domPagerMore.removeClass('hidden');
                    }
                    that.domPagerMore.data('page', page);
                },
                error: function () {
                },
                complete: function () {
                    that.domPagerMore.data('isLoading', 0);
                },
            });
        });
    },
    /**
     * validate form search
     */
    validate: function () {
        var that = this;
        that.formSearch.submit(function (event) {
            event.preventDefault();
        });
        that.formSearch.validate({
            messages: {
                end: {
                    greaterEqualThan: trans['end_greater'],
                },
            },
            rules: {
                start: {
                    required: true,
                    date: true,
                },
                end: {
                    required: true,
                    date: true,
                    greaterEqualThan: '[name="start"]',
                },
            },
        });
    },
    /**
     * load plugin js
     */
    plugin: function () {
        $('input[data-flag-type="date"]').datepicker({
            format: 'yyyy-mm-dd',
            useCurrent: false,
            autoclose: true,
            todayHighlight: true,
        });
        RKExternal.select2.init();
    },
    /**
     * cal period number
     *
     * @param {type} data
     * @param {type} force
     */
    calPeriodNumber: function (data, force) {
        var that = this;
        if (that.widthProgress && !force) {
            return true;
        }
        $.each (data, function (i, v) {
            var l = Object.keys(v.period).length;
            if (l) {
                that.widthProgress = 100 / l;
            } else {
                that.widthProgress = 0;
            }
            return false;
        });
        return that.widthProgress;
    },
    filterInit: function () {
        RKfuncion.teamTree.init(varGlob.teamPath);
        $('[data-fg-dom="team-dev"]').html(RKfuncion.teamTree.renderOptionSelect());
    },
    addFilterSkills: function () {
        var that = this, indexU = 1, html;
        that.domWrapSkills = $('[data-fg-dom="filter-skill"]');
        that.htmlFilterSkill = that.domWrapSkills.html();
        that.domWrapSkills.html('').removeClass('hidden');
        $('[data-fg-dom="btn-add-skill"]').click(function () {
            html = that.htmlFilterSkill.replace(/xxx/gi, indexU);
            html = $(html);
            that.domWrapSkills.append(html);
            RKExternal.select2.init();
            indexU++;
        });
        $(document).on('click', '[data-fg-dom="btn-remove-skill"]', function () {
            $(this).closest('[data-fg-dom="f-skill-item"]').remove();
        });
    }
};
busy.init();
})(jQuery, RKExternal, window, RKfuncion);
