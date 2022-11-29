const Helper = {
    pushStateUrl: function (params = {}, uri = null) {
        if (!uri) {
            let location = window.location;
            uri = location.origin + location.pathname;
        }
        let strParams = [];
        Object.keys(params).map((name, keyName) => {
            strParams.push(name + '=' + params[name]);
        });
        uri += '?' + strParams.join('&');
        history.pushState({}, '', uri);
    },

    trans: function (text, params = {}) {
        if (typeof textTrans[text] == 'undefined') {
            return text;
        }
        text = textTrans[text];
        let paramKeys = Object.keys(params);
        if (paramKeys.length < 1) {
            return text;
        }
        for (let i = 0; i < paramKeys.length; i++) {
            text = text.replace(new RegExp(':'+ paramKeys[i]), params[paramKeys[i]]);
        }
        return text;
    },
    findIndexById: function (id, list) {
        for (let i = 0; i < list.length; i++) {
            if (list[i].id == id) {
                return i;
            }
        }
        return -1;
    },
    findItemById: function (id, list) {
        for (let i = 0; i < list.length; i++) {
            if (list[i].id == id) {
                return list[i];
            }
        }
        return null;
    },
    toMbSize: function (size) {
        return (size / (1024 * 1024)).toFixed(2);
    },
    toKbSize: function (size) {
        return (size / 1024).toFixed(2);
    },
    htmlEntities: function (str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },
    pushUniqueItem: function (item, list) {
        if (list.indexOf(item) < 0) {
            list.push(item);
        }
        return list;
    },
    removeListItem: function (item, list) {
        let index = list.indexOf(item);
        if (index > -1) {
            list.splice(index, 1);
        }
        return list;
    },
    getNickName: function(email) {
        let account = email.replace(/@.*/, '');
        return account.charAt(0).toUpperCase() + account.slice(1);
    },
    getItemMonth: function (timeStr, format = 'YYYY-MM') {
        let time = moment(timeStr);
        return time.format(format);
    },

    //filter data
    getFilterData: function (keys = [], that) {
        let {filterData} = that.state;
        if (keys.length < 1) {
            return '';
        }
        let evalStr = 'filterData';
        let result = '';
        for (let i = 0; i < keys.length; i++) {
            evalStr += '[\''+ keys[i] +'\']';
            eval(
                'if (typeof '+ evalStr +' == "undefined") {'
                    + evalStr + ' = {};'
                    + 'result = "";'
                + '} else {'
                    + 'if (!' + evalStr + ') {'
                        + 'result = "";'
                    + '} else {'
                        + 'result = Object.keys(' + evalStr + ').length == 0 ? "" : ' + evalStr + ';'
                    + '}'
                + '}'
            );
        }
        return result;
    },

    setFilterData: function (params, value, refresh, that, prefix = '') {
        if (params.length < 1) {
            return;
        }
        let {filterData} = that.state;
        if (params[0] == 'orderby') {
            let oldVal = this.getFilterData(params, that);
            if (!oldVal) {
                value = 'asc';
            } else {
                value = oldVal == 'asc' ? 'desc' : 'asc';
            }
            filterData.orderby = {};
        }

        let evalStr = 'filterData';
        for (let i = 0; i < params.length; i++) {
            evalStr += '[\''+ params[i] +'\']';
            eval('if (typeof '+ evalStr +' == "undefined") { '+ evalStr +' = {}; }');
            eval(evalStr);
        }
        evalStr += '=value';
        eval(evalStr);

        that.setState({
            filterData: filterData
        });
        this.session.setRawItem('filterData', filterData, prefix);
        if (refresh) {
            that.getCollection();
        }
    },

    initFilterData: function (elSelect, that) {
        elSelect.each(function () {
            let elThis = $(this);
            let key = elThis.attr('data-key');
            let key2 = elThis.attr('data-key2') || null;
            let keys = ['filter', key];
            if (key2) {
                keys.push(key2);
            }
            let value = that.getFilterData(keys);

            $(this).val(value);
        });
    },

    /*
     * remove empty value (null, empty array, empty object, empty string)
     */
    filterEmptyData: function (filterData) {
        let that = this;
        $.each(filterData, function (key, value) {
            if (value === null || ($.isArray(value) && value.length < 1)
                || (typeof value == 'object' && Object.keys(value).length < 1)) {
                delete filterData[key];
            }
            if (value !== null && typeof value == 'object' && Object.keys(value).length > 0) {
                filterData[key] = that.filterEmptyData(filterData[key]);
            }
        });
        return filterData;
    },

    //session
    session: {
        setItem: function (key, value) {
            if (typeof Storage == 'undefined') {
                return;
            }
            sessionStorage.setItem(key, value);
        },
        setRawItem: function (key, value, prefix = '') {
            if (typeof Storage == 'undefined') {
                return;
            }
            key = prefix ? prefix + '_' + key : key;
            value = value ? JSON.stringify(value) : '';
            sessionStorage.setItem(key, value);
        },
        getItem: function (key) {
            if (typeof Storage == 'undefined') {
                return '';
            }
            return sessionStorage.getItem(key);
        },
        getRawItem: function (key, prefix = '') {
            if (typeof Storage == 'undefined') {
                return null;
            }
            key = prefix ? prefix + '_' + key : key;
            let value = sessionStorage.getItem(key);
            if (!value) {
                return null;
            }
            return JSON.parse(value);
        },
        removeItem: function (key, prefix = '') {
            if (typeof Storage == 'undefined') {
                return;
            }
            key = prefix ? prefix + '_' + key : key;
            sessionStorage.removeItem(key);
        }
    },
    //message
    alertResError: function (error) {
        if (typeof error == 'undefined' || typeof error.responseJSON == 'undefined') {
            return;
        }
        let message = typeof error.responseJSON.message != 'undefined' ? error.responseJSON.message : error.responseJSON;
        bootbox.alert({
            className: 'modal-danger',
            message: message,
        });
    },
    alertSuccess: function (message) {
        bootbox.alert({
            className: 'modal-success',
            message: message,
        });
    },
    alertError: function (message) {
        bootbox.alert({
            className: 'modal-danger',
            message: message,
        });
    },
    scrollBottom: function (element) {
        element.animate({scrollTop: element.prop('scrollHeight')}, 100);
    },
    //offset
    getOffsetParent(elChild, elParent) {
        let childPos = elChild.offset();
        let parentPos = elParent.offset();
        return {
            top: childPos.top - parentPos.top,
            left: childPos.left - parentPos.left,
        };
    }
};

export default Helper;
