(function ($) {
    var notiMenu = $('#notify_menu');
    var notifyList = notiMenu.find('.notify-list');
    var notiNum = notiMenu.find('.notify-num');
    var notiViewAll = notiMenu.find('.view-all');
    var notiNone = notiMenu.find('.none-item');
    var notiTpl = $('#notify_template');
    var docTitle = document.title;
    var modalPopupNotify = $('#modal-popup-notify');
    var contentModal = modalPopupNotify.find('.content');
    var btnCloseModal = modalPopupNotify.find('.close');

    function showPopupNotify(dataItem) {
        contentModal.html(dataItem.content);
        modalPopupNotify.show();
        btnCloseModal.attr('disabled', true).css('opacity', 0.2);
        if (contentModal.height() >= 400) {
            contentModal.css('overflow-y','auto');
        }
        $.ajax({
            type: 'PUT',
            url: notifyConst.set_read_url,
            data: {
                _token: siteConfigGlobal.token,
                notify_id: dataItem.notify_id,
            },
            success: function () {
                btnCloseModal.attr('disabled', false).css('opacity', 1);
            }
        });
    }
    // show next notification if available or close modal
    btnCloseModal.click(function () {
        popupNotifications.length ? showPopupNotify(popupNotifications.shift()) : modalPopupNotify.hide();
    });


    $(document).ready(function () {
        //loadingNotify(notifyList);
        var numNotify = parseInt(notiNum.text());
        if (numNotify) {
            changeDocTitle(numNotify);
        }
        //set read notify
        var queryUrl = window.location.search;
        if (!queryUrl) {
            queryUrl = window.location.hash;
        }
        var hasNotify = false;
        if (queryUrl) {
            var arrayUrl = queryUrl.split('?');
            if (arrayUrl.length !== 2) {
                return;
            }
            var params = arrayUrl[1].split('&');
            for (var i = 0; i < params.length; i++) {
                var arrParam = params[i].split('=');
                if (arrParam.length !== 2) {
                    continue;
                }
                if (arrParam[0] === 'notify_id') {
                    var tab = $('a[href="'+ arrayUrl[0] +'"]');
                    if (tab.attr('data-toggle')) {
                        tab.tab('show');
                    }
                    setReadNotify(arrParam[1]);
                    hasNotify = true;
                    break;
                }
            }
        }
        if (!hasNotify && numNotify > 0) {
            setReadNotify();
        }
    });

    //ajax set read notify
    $('body').on('click', '.notify-list .notify-item', function (e) {
        var _this = $(this);
        if (_this.attr('target') || !_this.attr('href')) {
            var notifyId = _this.attr('data-id');
            setReadNotify(notifyId);
        }
    });

    //mark read icon
    $('body').on('click', '.notify-item .mark-read', function (e) {
        e.preventDefault();
        var notifyId = $(this).closest('.notify-item').attr('data-id');
        setReadNotify(notifyId);
        e.stopPropagation();
    });

    //set read all
    $('body').on('click', '.check-readall a', function (e) {
        e.preventDefault();
        var _this = $(this);
        if (_this.hasClass('loading')) {
            return;
        }
        _this.addClass('loading');
        $.ajax({
            url: notifyConst.set_read_url,
            type: 'PUT',
            data: {
                _token: siteConfigGlobal.token,
                read_all: 1,
            },
            success: function () {
                $('#notify_list_page li a').removeClass('not-read');
            },
            complete: function () {
                _this.removeClass('loading');
            },
        });
    });

    function setReadNotify(notifyId) {
        var location = window.location;
        var currUrl = location.origin + location.pathname;
        if (typeof notifyId != 'undefined') {
            var elNotify = $('.notify-item[data-id="'+ notifyId +'"]');
        } else {
            notifyId = null;
            var elNotify = $('.notify-item[href^="'+ currUrl +'"]');
        }
        if (elNotify.length > 0) {
            if (!elNotify.hasClass('not-read')) {
                return false;
            }
            if (elNotify.hasClass('loading')) {
                return false;
            }
        }
        elNotify.addClass('loading');
        $.ajax({
            type: 'PUT',
            url: notifyConst.set_read_url,
            data: {
                _token: siteConfigGlobal.token,
                notify_id: notifyId,
                url: notifyId ? null : currUrl,
            },
            success: function (numRead) {
                elNotify.removeClass('not-read');
                var num = notiNum.text() ? parseInt(notiNum.text()) : 0;
                if (num) {
                    descreaseNotiNum(num - parseInt(numRead));
                }
            },
            complete: function () {
                elNotify.removeClass('loading');
            },
        });
    }

    notifyList.scroll(function () {
        if ($(this).scrollTop() + $(this).height() >= this.scrollHeight) {
            loadingNotify(notifyList);
        }
    });

    var timeoutSetHeight = null;
    //ajax loading notify
    function loadingNotify(notifyLists, loaded) {
        if (typeof loaded == 'undefined') {
            loaded = true;
        }
        var url = notifyLists.attr('data-url');
        if (!url) {
            return;
        }
        if (!notiMenu.find('a.notify-toggle').hasClass('loaded')) {
            notiMenu.find('.notify-list').html('');
        }
        var notiLoading = notifyLists.closest('.noti-contain').find('.noti-loading');
        notiLoading.removeClass('hidden');
        var notiLoadMore = notifyLists.closest('.noti-contain').find('.load-more');
        $.ajax({
            method: 'GET',
            url: url,
            success: function (result) {
                if (result.total > 0) {
                    notiNone.addClass('hidden');
                    notiViewAll.removeClass('hidden');
                } else {
                    notiNone.removeClass('hidden');
                    notiViewAll.addClass('hidden');
                }
                var items = result.notify_list;
                if (items.length > 0) {
                    for (var i = 0; i < items.length; i++) {
                        var item = items[i];
                        addNewNotifyHtml(item, notifyLists);
                    }
                    $('#notify_list_page .notify-content').shortedContent({showChars: 500,});
                    $('#notify_list .notify-content').shortedContent();
                }
                if (!result.next_page_url) {
                    notiLoadMore.addClass('hidden');
                }
                notifyLists.attr('data-url', result.next_page_url);
                if (loaded) {
                    notiMenu.find('a.notify-toggle').addClass('loaded');
                }
                //set scroll
                if (notiMenu.hasClass('open') && !timeoutSetHeight) {
                    timeoutSetHeight = setTimeout(function () {
                        var liHeight = 0;
                        notifyList.find('li').each(function () {
                            liHeight += $(this).height();
                        });
                        if (liHeight <= notifyList.height()) {
                            notifyList.height(liHeight - 5);
                        }
                    }, 1000);
                }
            },
            error: function () {
                notifyLists.append('<li><a class="notify-item">Something error!</a></li>');
            },
            complete: function () {
                notiLoading.addClass('hidden');
            },
        });
    }

    /*
     * add new notify element item
     */
    function addNewNotifyHtml(item, notifyLists, append) {
        if (typeof append == 'undefined') {
            append = true;
        }
        var itemLi = notiTpl.clone().find('li');
        var itemNoti = itemLi.find('.notify-item');
        var notiContent = $('<div>' + item.content + '</div>').text();
        if (!item.read_at) {
            itemNoti.addClass('not-read');
        } else {
            itemNoti.removeClass('not-read');
        }
        itemNoti.attr('data-id', item.id);
        itemNoti.attr('title', notiContent);
        itemNoti.attr('href', item.link);
        if (item.link === 'https://mail.google.com') {
            itemNoti.attr('target', '_blank');
        }
        itemNoti.find('.notify-icon img').attr('src', item.image);
        itemNoti.find('.notify-content').text(notiContent);
        itemNoti.find('.notify-time').attr('data-time', item.timestamp)
            .text(displayDiffTime(item.timestamp));
        if (append) {
            itemLi.appendTo(notifyLists);
        } else {
            itemLi.prependTo(notifyLists);
        }
    }

    $('body').on('click', '.notify-page .load-more a', function (e) {
        e.preventDefault();
        var _this = $(this);
        var notiContain = _this.closest('.noti-contain');
        var notifyLists = notiContain.find('.notify-list');
        loadingNotify(notifyLists, false);
    });

    /*
     * hover notify icon load data
     */
    $('body').on('click', '#notify_menu .notify-toggle', function (e) {
        resetNotifyNumber($(this));
    });

    /*
     * reset notify number to zero
     */
    function resetNotifyNumber(elThis) {
        var _this = elThis;
        if (!_this.hasClass('loaded')) {
            loadingNotify(notifyList);
        }
        var url = _this.data('reset-url');
        var notiNums = parseInt(_this.find('.notify-num').text());
        if (!notiNums) {
            return;
        }
        $.ajax({
            type: 'PUT',
            url: url,
            data: {
                _token: siteConfigGlobal.token,
            },
            success: function () {
                descreaseNotiNum(0);
                _this.addClass('reseted');
            },
        });
    }

    /*
     * btn refresh notify data
     */
    $('body').on('click', '#notify_menu .noti-header .refresh', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation()
        var iconLoading = $(this).find('i.fa');
        if (iconLoading.hasClass('fa-spin')) {
            return;
        }
        iconLoading.addClass('fa-spin');
        refreshNotiData(function () {
            iconLoading.removeClass('fa-spin');
        });
    });

    /*
     * interval refresh notify data
     */
    //setInterval(refreshNotiData, parseInt(notifyConst.refresh_minute) * 60 * 1000);

    //refresh time once per minute
    setInterval(refreshNotiTime, 60 * 1000);

    /*
     * refresh notify data
     */
    function refreshNotiData(done) {
        var lastId = $('#notify_list .notify-item:first').attr('data-id');
        if (typeof lastId == 'undefined') {
            lastId = notifyConst.max_id || 0;
        }
        refreshNotiTime();
        $.ajax({
            url: notifyConst.refresh_url,
            type: 'GET',
            data: {
                last_id: lastId,
            },
            success: function (result) {
                var items = result.notify_list;
                if (items.length > 0) {
                    $('.notify-list').each(function () {
                        $(this).closest('.noti-contain').find('.none-item').addClass('hidden');
                    });
                    $('.check-readall').removeClass('hidden');
                    for (var i = 0; i < items.length; i++) {
                        var item = items[i];
                        addNewNotifyHtml(item, $('.notify-list'), false);
                    }
                    notifyConst.max_id = items[items.length - 1].id;
                    $('#notify_list .notify-content').shortedContent();
                    $('#notify_list_page .notify-content').shortedContent({showChars: 500,});
                }
                var numNotRead = result.num_noti ? parseInt(result.num_noti) : 0;
                if (numNotRead === 0) {
                    numNotRead = '';
                }
                if (numNotRead > 99) {
                    numNotRead = '99+';
                }
                notiNum.text(numNotRead);
                changeDocTitle(numNotRead);
            },
            complete: function () {
                if (typeof done != 'undefined') {
                    done();
                }
            },
        });
    }

    /*
     * refresh notify update time
     */
    function refreshNotiTime() {
        $('.notify-list .notify-item').each(function () {
            var elNotiTime = $(this).find('.notify-time');
            var notiTime = parseInt(elNotiTime.attr('data-time'));
            elNotiTime.text(displayDiffTime(notiTime));
        });
    }

    /*
     * display human diff time
     */
    function displayDiffTime(time) {
        var curr_time = Math.floor(new Date().getTime() / 1000);
        var diff = curr_time - time;
        if (diff < 60) {
            return notifyConst.text_recently_update;
        }
        if (diff < 60 * 60) {
            return Math.floor(diff / 60) + ' ' + notifyConst.text_minutes_ago;
        }
        if (diff < 60 * 60 * 24) {
            return Math.floor(diff / (60 * 60)) + ' ' + notifyConst.text_hours_ago;
        }
        if (diff < 60 * 60 * 24 * 7) {
            return Math.floor(diff / (60 * 60 * 24)) + ' ' + notifyConst.text_days_ago;
        }
        var date = new Date(time * 1000);
        return twoDigit(date.getHours()) + ':' + twoDigit(date.getMinutes()) + ' '
            + twoDigit(date.getDate()) + '/' + twoDigit((date.getMonth() + 1)) + '/' + date.getFullYear();
    }

    /*
     * two digit number
     */
    function twoDigit(number) {
        if (number < 10) {
            return '0' + number;
        }
        return number;
    }

    /*
     * descrease notify number
     */
    function descreaseNotiNum(num) {
        if (typeof num == 'undefined') {
            num = parseInt(notiNum.text());
            if (num === 0) {
                return;
            }
            num = num - 1;

        }
        if (num < 1) {
            num = '';
        }
        notiNum.text(num);
        changeDocTitle(num);
    }

    /*
     * change page title
     */
    function changeDocTitle(count) {
        var newTitle = (count > 0 ? '(' + count + ') ' : '') + docTitle;
        document.title = newTitle;
    }

    //Listen Websocket
    var wsUrl = notifyConst.protocol
        + '://' + notifyConst.host
        + ':' + notifyConst.port + '?'
        + 'employee_id=' + notifyConst.employeeId
        + '&token=' + notifyConst.wsToken;
    var websocket = new WebSocket(wsUrl);

    //Send Verify token to determine the listeners is come from valid site
    websocket.onopen = function () {
        //connected websocket
        if (notifyConst.env == 'local') {
            console.log('connected to ' + wsUrl);
        }
    };

    var timeoutResetNum = null;
    //Listen New socket message
    websocket.onmessage = function (event) {
        var dataItem = JSON.parse(event.data);
        if (dataItem.app_env != notifyConst.notiEnv) {
            return;
        }
        if (notifyConst.env == 'local') {
            console.log(dataItem);
        }
        dataItem.id = dataItem.notify_id;
        dataItem.content = dataItem.title;
        dataItem.timestamp = dataItem.time;
        if (dataItem.type === notifyConst.typePopup) {
            popupNotifications.push(dataItem);
            if (modalPopupNotify.css('display') === 'none') {
                showPopupNotify(popupNotifications.shift());
            }
            return;
        }

        $('.notify-list').each(function () {
            $(this).closest('.noti-contain').find('.none-item').addClass('hidden');
        });
        $('.check-readall').removeClass('hidden');
        addNewNotifyHtml(dataItem, $('.notify-list'), false);

        notifyConst.max_id = dataItem.id;
        $('#notify_list .notify-content').shortedContent();
        $('#notify_list_page .notify-content').shortedContent({showChars: 500,});

        var numNotRead = notiNum.text() ? parseInt(notiNum.text()) : 0;
        numNotRead++;
        if (numNotRead === 0) {
            numNotRead = '';
        }
        if (numNotRead > 99) {
            numNotRead = '99+';
        }
        notiNum.text(numNotRead);
        changeDocTitle(numNotRead);

        //if open notify menu
        if (notiMenu.hasClass('open')) {
            if (timeoutResetNum) {
                clearTimeout(timeoutResetNum);
            }
            timeoutResetNum = setTimeout(function () {
                resetNotifyNumber(notiMenu.find('.notify-toggle'));
            }, 3000);
        }
    };

})(jQuery);
