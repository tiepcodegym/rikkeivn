(function ($) {
var RKTagFunction = {};
/**
 * get html field tree
 */
RKTagFunction.getHtmlFieldTree = {
    treePath: null,
    html: null,
    option: {},
    init: function (idRoot, treePath, typeHtml, idSelected, option) {
        if (typeof idRoot === 'undefined' || 
            typeof treePath === 'undefined' || 
            !idRoot || !treePath
        ) {
            return true;
        }
        if (typeof option === 'undefined') {
            option = {};
        }
        this.treePath = treePath,
        this.html = '';
        this.option = option;
        switch (typeHtml) {
            case 2: //option in select
                this.html = [];
                this.html.push({
                    id: idRoot,
                    label: RKTagFunction.general.htmlDecode('&nbsp;')
                });
                this._getOptionRecursive(idRoot, idSelected, 0);
                break;
            default: //html list tree
                this._getHtmlRecursive(idRoot, 0);
        }
        return this.html;
    },
    // call recursive to call tree
    _getHtmlRecursive: function(idParentCheck, level) {
        var __this = this;
        if (typeof __this.treePath[idParentCheck] === 'undefined' ||
            !__this.treePath[idParentCheck].child.length
        ) {
            return '';
        }
        var index,
            children = __this.treePath[idParentCheck].child,
            htmlIndent = RKTagFunction.general.htmlLevel(level);
        for (index in children) {
            var idChild = children[index],
                item = __this.treePath[idChild],
                optionMore;
            if (typeof __this.treePath[idChild] === 'undefined') {
                continue;
            }
            if (this.option.attrMore) {
                optionMore = this.option.attrMore.replace(/\{\{id\}\}/gi, idChild);
            } else {
                optionMore = '';
            }
            __this.html += 
            '<li data-id="'+idChild+'" ' + 
                'class="field-item" ng-class="{\'active\': '+idChild+'==fieldActiveId}" ' + 
                'data-parent-id="'+idParentCheck+'" ' + optionMore + '>' + 
                '<span>' +
                    '<a href="javascript:void(0)">' +
                        htmlIndent + 
                        '<span class="field-color"' +
                            ' style="background-color: '+item.data.color+'"></span>' +
                        '<span class="field-text">' +item.data.name + '</span>&nbsp;&nbsp;' +
                        '<span class="tag-count tc-label" ng-if="fieldTagReviewCount['+idChild+'] > 0 || fieldTagTotal['+idChild+'] > 0">(</span>'+
                        '<span class="tag-count" ng-if="fieldTagReviewCount['+idChild+'] > 0"><span ng-bind="fieldTagReviewCount['+idChild+']"></span>/</span>' +
                        '<span class="tag-count tc-total" ng-if="fieldTagTotal['+idChild+'] > 0"><span ng-bind="fieldTagTotal['+idChild+']"></span></span>' +
                        '<span class="tag-count tc-label" ng-if="fieldTagReviewCount['+idChild+'] > 0 || fieldTagTotal['+idChild+'] > 0">)</span>'+
                    '</a>' +
                '</span>';
            if (__this.treePath[idChild].child.length) {
                // not show field type special
                if (__this.option.type &&
                    __this.option.type == __this.treePath[idChild].data.type &&
                    __this.option.level &&
                    __this.option.level - 2 < level) {
                } else {
                    //__this.html += '<ul>';
                    __this._getHtmlRecursive(idChild, level+1);
                    //__this.html += '</ul>';
                }
            }
            __this.html += '</li>';
        }
    },
    // call recursive to call option select
    _getOptionRecursive: function(idParentCheck, idSelected, level) {
        var __this = this;
        if (typeof __this.treePath[idParentCheck] === 'undefined' ||
            !__this.treePath[idParentCheck].child.length
        ) {
            return '';
        }
        if (typeof idSelected === 'undefined') {
            idSelected = 0;
        }
        var index, jndex, nameOption,
            children = __this.treePath[idParentCheck].child;
        for (index in children) {
            var idChild = children[index], selected = '';
            if (typeof __this.treePath[idChild] === 'undefined') {
                continue;
            }
            if (idSelected == idChild) { // not show itself and child
                continue;
            }
            if (__this.option.type &&
                __this.option.type == __this.treePath[idChild].data.type
            ) {
                continue;
            }
            nameOption = '';
            for (jndex = 0; jndex < level; jndex++) {
                nameOption += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            nameOption += __this.treePath[idChild].data.name;
            __this.html.push({
                id: idChild,
                label: RKTagFunction.general.htmlDecode(nameOption)
            });
            if (level < 0 && __this.treePath[idChild].child.length) {
                __this._getOptionRecursive(idChild, idSelected, level+1);
            }
        }
    }
};
RKTagFunction.general = {
    /**
     * get template with version
     * 
     * @param {type} template
     * @returns {String}
     */
    getTemplate: function(template) {
        return RKVarGlobalTag.pathAssetTemplate + 
        '/'+template+'?v=' + RKVarGlobalTag.assetVersion
    },
    /**
     * notify message, use lib notify
     * 
     * @param {type} message
     * @returns {undefined}
     */
    notifyError: function(message) {
        if (typeof message === 'undefined') {
            message = 'System error';
        }
        $.notifyClose();
        $.notify({
            message: message
        },{
            type: 'warning',
            z_index: 2000
        });
    },
    notifySuccess: function(message) {
        if (typeof message === 'undefined') {
            message = 'Success';
        }
        $.notifyClose();
        $.notify({
            message: message
        },{
            type: 'success',
            z_index: 2000
        });
    },
    /**
     * string to html code
     * 
     * @param {type} input
     */
    htmlDecode: function (input) {
        var e = document.createElement('div');
        e.innerHTML = input;
        return e.childNodes[0].nodeValue;
    },
    /**
     * create/edit move item in tree
     * 
     * @param {type} treeRoot
     * @param {type} idParent
     * @param {type} childInfo
     */
    treeCreateItem: function(treeRoot, idParent, childInfo, domChild) {
        var domTree = $('field-list-tree[data-type-id="'+treeRoot+'"] .tree-list'),
        domParent = domTree.find('.field-item[data-id="'+idParent+'"]'),
        levelChild,
        domLastChild;
        if (treeRoot == idParent) {
            levelChild = -1;
        } else {
            levelChild = domParent.find('.indent').length;
        }
        if (childInfo !== null) {
            domChild = $('<li class="field-item" ng-click="fieldItemClick($event);" ' +
                'data-id="'+childInfo.id+'" ng-class="{\'active\': '+childInfo.id+'==fieldActiveId}"' +
                'data-parent-id="'+idParent+'"><span><a href="#">'+
                    RKTagFunction.general.htmlLevel(levelChild + 1) +
                    '<span class="field-color"' +
                        ' style="background-color: '+childInfo.color+'"></span>' +
                    '<span class="field-text">'+childInfo.name+'</span>'+
                '</a></span></li>');
        } else {
            domChild.attr('data-parent-id', idParent);
        }
        if (treeRoot == idParent) {
            domTree.append(domChild);
            return domChild;
        }
        domLastChild = domTree
            .find('.field-item[data-parent-id="'+idParent+'"]').last();
        if (domLastChild.length) {
            domLastChild.after(domChild);
        } else {
            domParent.after(domChild);
        }
        return domChild;
    },
    /**
     * render html follow level
     */
    htmlLevel: function(level) {
        var html = '', index;
        if (level > 0) {
            for (index = 0 ; index < level ; index++) {
                html += '<span class="indent"></span>';
            }
        }
        return html;
    },
    /**
     * show popup confirm delete
     * 
     * @param {type} option: title, message, 
     *      ok: callback when confirm delelte
     *      close: callback closing popup
     *      closed: callback clsed popup
     * @param {type} data
     * @returns {Boolean}
     */
    popupConfirmDanger: function(option, data) {
        //title, message, ok: function callback
        if (typeof option.message === 'undefined' || option.message === null) {
            option.message = 'Are you sure delete item?';
        }
        if (typeof option.title === 'undefined' || option.title === null) {
            option.title = RKTagTrans.Confirm;
        }
        if (typeof data === 'undefined') {
            data = {};
        }
        var modal = $('#ng-modal-delete-confirm');
        if (!modal.length) {
            modal = $('' + 
'<div class="modal fade modal-danger" id="ng-modal-delete-confirm">' +
    '<div class="modal-dialog">' +
        '<div class="modal-content">' +
            '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>' +
                '<h4 class="modal-title">'+option.title+'</h4>' +
            '</div>' +
            '<div class="modal-body">' +
                option.message +
            '</div>' +
            '<div class="modal-footer">' +
                '<button type="button" class="btn btn-outline btn-close" data-dismiss="modal">Close</button>' +
                '<button type="button" class="btn btn-outline btn-ok">OK</button>' +
            '</div>' +
        '</div>' +
    '</div>' +
'</div>');
            $('body').append(modal);
        } else {
            modal.find('.modal-title').html(option.title);
            modal.find('.modal-body').html(option.message);
        }
        modal.modal('show');
        if (option.ok == 'undefined') {
            return true;
        }
        data.modal = modal;
        $('#ng-modal-delete-confirm .btn-ok').on('click', function() {
            option.ok(data);
            $('#ng-modal-delete-confirm .btn-ok').off('click');
            data.modal.modal('hide');
        });
        modal.on('hide.bs.modal', function() {
            $('#ng-modal-delete-confirm .btn-ok').off('click');
            if (typeof option.close === 'function') {
                option.close(data);
            }
        });
        modal.on('hidden.bs.modal', function() {
            if (typeof option.closed === 'function') {
                option.closed(data);
            }
        });
        
    },
    /**
     * get all file have type is tag
     * 
     * @param {type} tags
     */
    fieldTagTypes: function (tags) {
        var result = {
            approve: {},
            review: {}
        }, index, item;
        for (index in tags) {
            item = tags[index];
            if (item.status == RKVarGlobalTag.tagStatusApprove) {
                result.approve[item.id] = item;
            } else {
                result.review[item.id] = item;
            }
        }
        return result;
    },
    /**
     * json => array => json data to json (int avai)
     * 
     * @param {json} data
     * @returns {json}
     */
    jsonArrayToInt: function(data) {
        var result = {}, index;
        for (index in data) {
            result[index] = this.json2ToInt(data[index]);
        }
        return result;
    },
    /**
     * json => json data to json (int avai)
     * 
     * @param {type} data
     */
    json2ToInt: function(data) {
        var result = {}, index;
        for (index in data) {
            result[index] = this.jsonToInt(data[index]);
        }
        return result;
    },
    /**
     * json data to json (int avai)
     * 
     * @param {type} data
     * @returns {unresolved}
     */
    jsonToInt: function(data) {
        var result = {}, index;
        for (index in data) {
            if (isNaN(data[index]) || !data[index]) {
                result[index] = data[index];
            } else {
                result[index] = parseInt(data[index]);
            }
        }
        return result;
    },
    /**
     * json to array (int avai)
     * 
     * @param {type} jsonObject
     * @param {type} convertInt
     */
    jsonToArray: function(jsonObject, convertInt) {
        var result = [], i;
        if (typeof convertInt === 'undefined') {
            convertInt = true;
        }
        for (i in jsonObject) {
            if (convertInt) {
                i = parseInt(i);
            }
            result.push({
                id: i,
                label: jsonObject[i]
            });
        }
        return result;
    },
    objectFilter: function(object, arrayKey) {
        var objectFilter = {}, index;
        if (typeof arrayKey === 'undefined' || !arrayKey.length ||
            !object) {
            return objectFilter;
        }
        for (index in object) {
            index = parseInt(index);
            if (arrayKey.indexOf(index) >= 0) {
                objectFilter[index] = object[index];
            }
        }
        return objectFilter;
    },
    /**
     * validate blur remote
     */
    validateRemoteDelay: function() {
        $(document).on('focus','form [data-validate-remote="1"]', function() {
            $(this).closest('form').validate().settings.onkeyup = false;
        });
        $(document).on('blur','form [data-validate-remote="1"]', function() {
            $(this).closest('form').validate().settings.onkeyup =
                $.validator.defaults.onkeyup;
        });
    },
    /**
     * check object change value
     * 
     * @param {type} object
     * @param {type} data
     * @returns {Boolean}
     */
    notChangeValueObject: function(object, data) {
        //colGroup, colName, value
        try {
            if (Array.isArray(object[data.colGroup][data.colName]) ||
                Array.isArray(data.value)
            ) {
                return this.equalArray(object[data.colGroup][data.colName],
                    data.value);
            }
            if (object[data.colGroup][data.colName] == data.value) {
                return true;
            }
            return false;
        } catch(e) {
            return false;
        }
    },
    /**
     * compare 2 array
     * 
     * @param {type} array1
     * @param {type} array2
     * @returns {Boolean}
     */
    equalArray: function (array1, array2) {
        if (!Array.isArray(array1)) {
            array1 = [];
        }
        if (!Array.isArray(array2)) {
            array2 = [];
        }
        if (array1.length !== array2.length) {
            return false;
        }
        for (var index in array1) {
            if (array2.indexOf(array1[index]) < 0) {
                return false;
            }
        }
        return true;
    },
    /**
     * intersection 2 array
     * 
     * @param {type} array1
     * @param {type} array2
     * @returns {Boolean}
     */
    intersectionArray: function (array1, array2) {
        if (!Array.isArray(array1)) {
            array1 = [];
        }
        if (!Array.isArray(array2)) {
            array2 = [];
        }
        for (var index in array1) {
            if (array2.indexOf(array1[index]) > -1) {
                return true;
            }
        }
        return false;
    },
    /**
     * convert object to slug
     * 
     * @param {oject} params
     * @returns {object}
     */
    paramField: function(params) {
        var result = {}, j, key = 'field';
        result = $.extend(true, {}, params);
        if (typeof result[key] === 'undefined') {
            return result;
        }
        for (j in result[key]) {
            result[key][j] = result[key][j].join('-');
        }
        return result;
    },
    /**
     * convert field to slug: field=id:tag1-tag2
     * 
     * @param {oject} fields
     * @returns {string}
     */
    encodeSlugField: function(fields) {
        var result = '', i, item;
        for (i in fields) {
            item = fields[i];
            result += i + ':' + item.join('-') + '_';
        }
        if (result) {
            result = result.slice(0, -1);
        }
        return result;
    },
    /**
     * decode field string
     * 
     * @param {type} stringField
     * @returns {object}
     */
    decodeSlugField: function(stringField) {
        var result = {
            field: {},
            tag: []
        };
        stringField.split('_').map(function(item) {
            var fieldTag = item.split(':');
            if (fieldTag.length === 2) {
                var tags = fieldTag[1].split('-');
                result.field[fieldTag[0]] = tags;
                result.tag = result.tag.concat(tags);
            }
        });
        return result;
    },
    /**
     * convert tag to slug: tag=name1::name2
     * 
     * @param {oject} fields
     * @returns {string}
     */
    encodeSlugTag: function(tags) {
        return tags.join('::');
    },
    /**
     * decode field string
     * 
     * @param {type} stringField
     * @returns {object}
     */
    decodeSlugTag: function(string) {
        return string.split('::');
    }
};

RKTagFunction.validate = {
    /**
     * greate input date
     */
    addGreaterDate: function() {
        $.validator.addMethod("greaterDate", function(value, element, params) {
            if (!/Invalid|NaN/.test(new Date(value))) {
                return new Date(value) > new Date($(params).val());
            }
            return isNaN(value) && isNaN($(params).val()) 
                || (Number(value) > Number($(params).val())); 
        },'Must be greater than {0}.');
    },
    /**
     * greate equal input date
     */
    addGreaterEqualDate: function() {
        $.validator.addMethod("greaterEqualDate", function(value, element, params) {
            if (!/Invalid|NaN/.test(new Date(value))) {
                return new Date(value) >= new Date($(params).val());
            }
            return isNaN(value) && isNaN($(params).val()) 
                || (Number(value) >= Number($(params).val())); 
        },'Must be greater or equal than {0}.');
    },
    /**
     * less equal input date
     */
    addLessEqualDate: function() {
        $.validator.addMethod("lessEqualDate", function(value, element, params) {
            if (!/Invalid|NaN/.test(new Date(value))) {
                return new Date(value) <= new Date($(params).val());
            }
            return isNaN(value) && isNaN($(params).val()) 
                || (Number(value) <= Number($(params).val())); 
        },'Must be less or equal than {0}.');
    }
};

/**
 * select 2
 */
RKTagFunction.select2 = {
    notSearch: function() {
        return {
            minimumResultsForSearch: Infinity
        };
    },
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
    remote: function(url) {
        var __this = this;
        return {
            id: function(response){ 
                return response.id;
            },
            ajax: {
                url: url,
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
            minimumInputLength: 1,
            templateResult: __this.__formatReponse, // omitted for brevity, see the source of this page
            templateSelection: __this.__formatReponesSelection // omitted for brevity, see the source of this page
        };
    },
    __formatReponse: function (response) {
        if (response.loading) {
            return response.text;
        }
        return markup = "<div class='select2-result-repository clearfix'>" +
                "<div class='select2-result-repository__title'>" + response.text + "</div>" +
            "</div>";
      },
    __formatReponesSelection: function (response) {
        return  response.text;
    }
};

/**
 * function team tree
 */
/**
 * get html field tree
 */
RKTagFunction.teamTree = {
    treePath: {},
    teamDev: {},
    arrayCodeTeamDev: [],
    idsSelected: null,
    treePathParentTeamDev: [],
    html: null,
    init: function (treePath, teamDev, idsSelected) {
        this.treePath = treePath;
        this.teamDev = teamDev;
        this.arrayCodeTeamDev = Object.keys(teamDev);
        this.html = [];
        this._treeDev();
        if (typeof idsSelected === 'undefined' || !Array.isArray(idsSelected)) {
            this.idsSelected = [];
        } else {
            this.idsSelected = idsSelected;
        }
        this._getOptionRecursive(0, 0);
        return this.html;
    },
    // call recursive to call option select
    _getOptionRecursive: function(idParent, level) {
        var __this = this;
        if (typeof __this.treePath[idParent] === 'undefined' ||
            !__this.treePath[idParent].child.length
        ) {
            return null;
        }
        var index, jndex, nameOption, item, disabled, idChild,
            children = __this.treePath[idParent].child,
            itemParent = __this.treePath[idParent];
        // self parent is team code, but not show child
        if (__this.arrayCodeTeamDev.indexOf(itemParent.data.code) >= 0 && 
            !__this.teamDev[itemParent.data.code].child) {
            return null;
        }
        for (index in children) {
            idChild = children[index],
            item = __this.treePath[idChild],
            disabled = false;
            if (typeof __this.treePath[idChild] === 'undefined') {
                continue;
            }
            // self not dev team code && parent not dev team code && self not tree dev
            if (__this.arrayCodeTeamDev.indexOf(item.data.code) < 0 && 
                __this.arrayCodeTeamDev.indexOf(itemParent.data.code) < 0 && 
                __this.treePathParentTeamDev.indexOf(idChild) < 0
            ) {
                continue;
            }
            // (self not dev code & parent not dev code) ||
            // self is dev code & self disable
            if ((__this.arrayCodeTeamDev.indexOf(item.data.code) < 0 &&
                __this.arrayCodeTeamDev.indexOf(itemParent.data.code) < 0) ||
                (__this.arrayCodeTeamDev.indexOf(item.data.code) >= 0 &&
                !__this.teamDev[item.data.code].self)
            ) {
                disabled = true;
            }
            nameOption = '';
            for (jndex = 0; jndex < level; jndex++) {
                nameOption += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            nameOption += item.data.name;
            __this.html.push({
                id: idChild,
                label: RKTagFunction.general.htmlDecode(nameOption),
                disabled: disabled
            });
            if (__this.treePath[idChild].child.length) {
                __this._getOptionRecursive(idChild, level+1);
            }
        }
    },
    /**
     * get all id team format tree avai dev
     */
    _treeDev: function() {
        var __this = this, index, item, resultIds = [];
        for (index in __this.treePath) {
            item = __this.treePath[index];
            if (__this.arrayCodeTeamDev.indexOf(item.data.code) >= 0) {
                resultIds = resultIds.concat(item.parent);
            }
        }
        __this.treePathParentTeamDev = resultIds.filter(function (item, pos) {
            return resultIds.indexOf(item) === pos;
        });
        return __this.treePathParentTeamDev;
    }
};

/**
 * funciton tag ui
 */
RKTagFunction.tagUi = {
    flagIsDelete: false,
    processSaveTag: false,
    /**
     * list tag of a field
     * 
     * @param {dom} dom ul tag
     * @param {array} tags
     * @param {object json} option
     */
    list: function(dom, tags, option) {
        if (!dom.length) {
            return false;
        }
        var __this = this;
        if (typeof option === 'undefined') {
            option = {};
        }
        __this.flagIsDelete = false;
        var removeAvai = false,
            processTagIT = false,
        tagIt = dom.tagit({
            itemName: 'item',
            fieldName: 'tag_item',
            allowSpaces: true,
            caseSensitive: false,
            beforeTagRemoved: function(event, ui) {
                __this.flagIsDelete = true;
                option.scope.funcFieldTagDelete(ui.tag, $.extend(option, {
                    callback: function() {
                        __this.flagIsDelete = false;
                    }})
                );
                if (typeof option.beforeChangeAny === 'function') {
                    option.beforeChangeAny(dom, __this);
                }
            },
            afterTagAdded: function(event, ui) {
                var idTag = ui.tag.attr('class').match(/tag-id-\d+/);
                if (option.color) {
                    __this.setColorForTag(ui.tag, option.color);
                }
                if (!idTag || !idTag.length || !idTag[0]) {
                    var fieldId = ui.tag.closest('[data-field-id]').data('field-id');
                    if (option.compileAngular) {
                        option.scope.funcFieldTagAdd(fieldId, ui.tag, ui.tagLabel, option);
                    }
                    return true;
                }
                idTag = parseInt(idTag[0].replace(/\D+/, ''));
                ui.tag.data('id', idTag);
                if (option.tagReview) {
                    ui.tag.append('<span title="'+RKTagTrans['Approve']
                        +'" class="tag-check-approve" ng-click="funcFieldTagApprove($event, '+idTag+')"><i class="fa fa-check"></i></span>');
                }
                if (option.compileAngular) {
                    option.compile(ui.tag.contents())(option.scope);
                }
            },
            beforeTagAdded: function (event, ui) {
                if (typeof option.beforeChangeAny === 'function') {
                    option.beforeChangeAny(dom, __this);
                }
                if (typeof ui.duringInitialization !== 'undefined' &&
                    ui.duringInitialization.createDefault
                ) {
                    return true;
                }
                var tagUiReview = $(option.checkDuplidate);
                if (!option.checkDuplidate || !tagUiReview.length) {
                    return true;
                }
                if (__this.tagCheckExists(ui.tagLabel, tagUiReview)) {
                    return true;
                }
                return true;
            }
        });
        if (option.inputAddRemove) {
            dom.data("ui-tagit").tagInput.addClass('hidden');
        }
        if (typeof tags === 'undefined') {
            return tagIt;
        }
        var index, item;
        if (!option.classAdd) {
            option.classAdd = '';
        }
        for (index in tags) {
            item = tags[index];
            dom.tagit('createTag', item.value, option.classAdd + 
                ' tag-id-'+item.id + ' tag-status-' + item.status,
                {createDefault: true});
        }
        if (option.editor) {
            __this.editTag(dom, option);
        }
        if (typeof option.funcOptions === 'function') {
            option.funcOptions(dom, __this);
        }
        return __this;
    },
    /**
     * editable tag
     * 
     * @param {object} dom
     * @param {object} option
     */
    editTag: function(ulWrapper, option) {
        var __this = this;
        $(document).on('dblclick', ulWrapper.selector + ' .tagit-choice.tagit-choice-editable', 
        function(event) {
            if (typeof option.beforeChangeAny === 'function') {
                option.beforeChangeAny(ulWrapper, __this);
            }
            if (__this.flagIsDelete) {
                return true;
            }
            var domThis = $(this),
            idTag = domThis.data('id');
            if (!idTag) {
                return true;
            }
            if (!__this._activeEditTag(ulWrapper, domThis)) {
                return true;
            }
            
            var labelTag = domThis.children('.tagit-label').text(),
            input = $('<input type="text" class=" tagit-label input-tagit-editor" ' + 
                'autocomplete="off" value="'+labelTag+'" data-id="'+idTag+'">');
            domThis.prepend(input);
            input.focus();
        });
        __this.eventSaveEditorTag(ulWrapper, option);
    },
    /**
     * event editor tag
     */
    eventSaveEditorTag: function(ulWrapper, option) {
        var __this = this;
        $(document).on('keypress', '.tagit-choice-editable .input-tagit-editor', function(event) {
            if (event.which === $.ui.keyCode.ENTER) {
                __this.saveEditorTag(ulWrapper, $(this), option);
            }
        });
        $(document).on('blur', '.tagit-choice-editable .input-tagit-editor', function() {
            __this.saveEditorTag(ulWrapper, $(this), option);
        });
    },
    /**
     * save editor tag
     * 
     * @param {dom} ulWrapper
     * @param {dom} domThis
     * @param {object} option
     * @returns {Boolean}
     */
    saveEditorTag: function(ulWrapper, domThis, option) {
        ulWrapper = $(ulWrapper.selector);
        var __this = this,
        id = domThis.data('id'),
        value = domThis.val(),
        tagItemDom = domThis.closest('.tagit-choice-editable'),
        oldValue = tagItemDom.closest('li.tagit-choice')
            .children('.tagit-label').text().toLowerCase();
        if (__this.processSaveTag) {
            return true;
        }
        if (!id ||
            !value ||
            value.length > 255
        ) {
            return true;
        }
        if (oldValue == value.toLowerCase()) {
            __this._inactiveEditTag(ulWrapper);
            __this.processSaveTag = false;
            return true;
        }
        if (__this.tagCheckExists(value, ulWrapper, id)) {
            return true;
        }
        var tagUiReview = $(option.checkDuplidate);
        if (!option.checkDuplidate || !tagUiReview.length) {
            return true;
        }
        if (__this.tagCheckExists(value, tagUiReview, id)) {
            return true;
        }
        __this.processSaveTag = true;
        // call ajax save editor tag
        $.ajax({
            method: 'post',
            url: RKVarGlobalTag.urlTagSave,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            dataType: 'json',
            data: {
                tag_id: id,
                tag_name: value,
                _token: siteConfigGlobal.token
            },
            success: function(response) {
                if (!response.success) {
                    RKTagFunction.general.notifyError(response.message);
                    return true;
                }
                RKTagFunction.general.notifySuccess(response.message);
                __this._inactiveEditTag(ulWrapper);
                tagItemDom.children('.tagit-label').text(value);
                if (typeof option.thenUpdateLabelTag === 'function') {
                    option.thenUpdateLabelTag(id, value, response);
                }
            },
            error: function () {
                RKTagFunction.general.notifyError();
            },
            complete: function () {
                __this.processSaveTag = false;
            }
        });
    },
    /**
     * active editor tag
     * 
     * @param {dom} ulWrapper
     * @param {dom} liActive
     * @returns {Boolean}
     */
    _activeEditTag: function(ulWrapper, liActive) {
        if (liActive.hasClass('editoring')) {
            return false;
        }
        ulWrapper.children('.tagit-choice').removeClass('editoring');
        ulWrapper.find('input.input-tagit-editor').remove();
        liActive.addClass('editoring');
        return true;
    },
    /**
     * inactive editor tag
     * 
     * @param {dom} ulWrapper
     */
    _inactiveEditTag: function(ulWrapper) {
        ulWrapper.children('.tagit-choice').removeClass('editoring');
        try {
            ulWrapper.find('input.input-tagit-editor').remove();
        } catch(e) {}
    },
    /**
     * active highlight tag
     * 
     * @param {dom} ulWrapper
     * @param {dom} liActive
     * @returns {Boolean}
     */
    _activeHighlightTag: function(ulWrapper, liActive) {
        if (liActive.hasClass('hightlight')) {
            return false;
        }
        ulWrapper.children('.tagit-choice').removeClass('hightlight');
        liActive.addClass('hightlight');
        return true;
    },
    /**
     * inactive Highlight tag
     * 
     * @param {dom} ulWrapper
     */
    _inactiveHighlightTag: function(ulWrapper) {
        ulWrapper.children('.tagit-choice').removeClass('hightlight');
    },
    
    /**
     * delete tag item in field
     * 
     * @param {dom} tag
     * @returns {Boolean}
     */
    deleteItem: function(tag) {
        var idTag = tag.data('id');
        if (typeof option === 'undefined') {
            option = {};
        }
        if (!idTag) {
            return false;
        }
        $.ajax({
            method: 'delete',
            url: RKVarGlobalTag.urlFieldDeleteTagItem,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            dataType: 'json',
            data: {
                id: idTag,
                _token: siteConfigGlobal.token
            },
            success: function(response) {
                if (!response.success) {
                    RKTagFunction.general.notifyError(response.message);
                    return true;
                }
                RKTagFunction.general.notifySuccess(response.message);
            },
            error: function (response) {
                RKTagFunction.general.notifyError();
            }
        });
        return true;
    },
    
    /**
     * add new tag item in field
     * 
     * @param {dom} tag
     * @returns {Boolean}
     */
    /*addItem: function(tag, value, scope) {
        var fieldId = tag.closest('[data-field-id]').data('field-id');
        $.ajax({
            method: 'post',
            url: RKVarGlobalTag.urlFieldAddTagItem,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            dataType: 'json',
            data: {
                field_id: fieldId,
                tag_name: value,
                _token: siteConfigGlobal.token
            },
            success: function(response) {
                if (!response.success) {
                    RKTagFunction.general.notifyError(response.message);
                    return true;
                }
                tag.data('id', response.tagItem.id);
                RKTagFunction.general.notifySuccess(response.message);
                if (typeof scope !== 'undefined' &&
                    typeof scope.fieldTagTotal !== 'undefined'
                ) {
                    scope.fieldTagTotal[fieldId]++;
                }
            },
            error: function (response) {
                RKTagFunction.general.notifyError();
            }
        });
    },*/
    /**
     * approve tag
     * 
     * @param {dom} tag
     * @returns {Boolean}
     */
    /*approveItem: function(domApprove) {
        var tag = $(domApprove).closest('li');
        var tagId = tag.data('id');
        if (!tagId || $(domApprove).hasClass('ajax-loadings')) {
            return false;
        }
        $(domApprove).addClass('ajax-loading');
        $.ajax({
            method: 'post',
            url: RKVarGlobalTag.urlFieldApproveTagItem,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            dataType: 'json',
            data: {
                id: tagId,
                _token: siteConfigGlobal.token
            },
            success: function(response) {
                if (!response.success) {
                    RKTagFunction.general.notifyError(response.message);
                    return true;
                }
                RKTagFunction.general.notifySuccess(response.message);
                // remove tag in review add add tag in approve box
                tag.remove();
                $('.tag-field-list[data-type="approve"]')
                    .tagit('createTag', response.tagItem.value, 
                        ' tag-id-'+response.tagItem.id);
            },
            error: function (response) {
                RKTagFunction.general.notifyError();
            }
        });
    },*/
    updateColor: function(color, domItemTree) {
        var dom = $('.field-tag-wrapper .tag-field-list.tagit li.tagit-choice');
        if (color) {
            this.setColorForTag(dom, color);
            if (typeof domItemTree !== 'undefined') {
                domItemTree.find('.field-color').css({
                    'background': color
                });
            }
        } else {
            dom.removeAttr('style');
        }
    },
    setColorForTag: function(dom, color) {
        dom.css({
//            'border-left-color': color
        });
    },
    /**
     * check tag exists in tagit
     * 
     * @param {string} value
     * @param {dom} ulWrapper
     * @returns {Boolean}
     */
    tagCheckExists: function(value, ulWrapper, id) {
        var result = false;
        // hightlight
        ulWrapper.find('li.tagit-choice').each (function(i,v) {
            if ($(v).data('id') != id &&
                $(v).find('span.tagit-label:first').text().toLowerCase() === 
                value.toLowerCase()
            ) {
                $(v).effect('highlight');
                result = true;
                return false;
            }
        });
        return result;
    }
};

/**
 * function use for angular
 */
RKTagFunction.ng = {
    /**
     * search tag in session location
     * 
     * @param {object} $scope scope of angular
     * @param {string} request search work param
     * @param {object} element dom jQuery
     * @returns {Array}
     */
    tagitSource: function($scope, request, element) {
        //get local tags
        var listAvalidTags = $scope.getStorageTags(),
            limit = 10, counter = 0;
        if (!listAvalidTags) {
            return [];
        }
        //excerpt tag
        var excerptTag = element.tagit('assignedTags');
        //list search tag
        var search = [];
        for (var i in listAvalidTags) {
            for (var j in listAvalidTags[i]) {
                var tagItem = listAvalidTags[i][j];
                if (excerptTag.indexOf(tagItem.value) > -1) {
                    continue;
                }
                if (tagItem.value.toLowerCase().startsWith(request.term.toLowerCase())) {
                    search.push(tagItem.value);
                    counter++;
                    if (counter >= limit) {
                        return search;
                    }
                }
            }
        }
        return search;
    },
    
    tagOfField: function($scope, element, fieldId) {
        //get local tags
        var listAvalidTags = $scope.getStorageTags();
        if (!listAvalidTags || typeof listAvalidTags[fieldId] === 'undefined' ||
            !listAvalidTags[fieldId].length
        ) {
            return [];
        }
        //excerpt tag
        var excerptTag = element.tagit('assignedTags');
        //list search tag
        var search = [];
        for (var i in listAvalidTags[fieldId]) {
            var tagItem = listAvalidTags[fieldId][i];
            if (excerptTag.indexOf(tagItem.value) > -1) {
                continue;
            }
            search.push({
                id: tagItem.id,
                value: tagItem.value
            });
        }
        return search;
    }
};
/**
 * progress bar
 */
RKTagFunction.progressBar = {
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

/*
 * export global var
 */
window.RKTagFunction = RKTagFunction;
})(jQuery);