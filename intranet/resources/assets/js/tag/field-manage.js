(function(angular, $, RKVarGlobalTag, RKTagFunction){
/**
 * componen tree field
 */
rkTagApp.controller('fieldManageController', function (
    $scope, $sce, $element, $http, $templateRequest, $compile
) {
        /*
         * init data
         */
        var __ctrl = this, index;
        $scope.fieldPath = {};
        $scope.trans = RKTagTrans;
        $scope.varGlobalTag = RKVarGlobalTag;
        
        $scope.ajaxLoadindRequest = false;
        // data form
        $scope.token = siteConfigGlobal.token;
        
        // status of field: enable or disable
        $scope.fieldStatus = [];
        for (index in $scope.varGlobalTag.fieldStatus) {
            $scope.fieldStatus.push({
                id: parseInt(index),
                label: $scope.varGlobalTag.fieldStatus[index]
            });
        }
        // type of field: info, tag, ...
        $scope.fieldTypes = [];
        $scope.fieldTypes.push({
            id: null,
            label: RKTagFunction.general.htmlDecode('&nbsp')
        });
        for (index in $scope.varGlobalTag.fieldTypes) {
            $scope.fieldTypes.push({
                id: parseInt(index),
                label: $scope.varGlobalTag.fieldTypes[index]
            });
        }        
        $scope.fieldActiveId = $scope.rootFieldTree;
        $scope.fieldTypeTag = $scope.varGlobalTag.fieldTypeTag;
        $scope.formFieldData = {};
        $scope.fieldItem = {};
        $scope.tags = {};
        $scope.fieldTagReviewCount = {};
        /*
         * load tree path 
         */
        var templateUrl = $sce.getTrustedResourceUrl(
            RKTagFunction.general.getTemplate('field-manage-tree.html')
        );
        //load template field tab
        $templateRequest(templateUrl).then(function(template) {
            $scope.funcGetHtmlFieldManage = $sce.trustAsHtml(template);
        });
        
        /**
         * set root tree 
         * 
         * @param {int} id
         */
        $scope.funcSetRootTree = function(id) {
            $scope.rootFieldTree = parseInt(id);
            $scope.fieldPath[id] = {};
            angular.copy($scope.varGlobalTag.fieldPath, $scope.fieldPath[id]);
            var htmlTree = RKTagFunction.getHtmlFieldTree.init(
                $scope.rootFieldTree, 
                $scope.fieldPath[$scope.rootFieldTree],
                1,
                0,
                {
                    type: $scope.varGlobalTag.fieldTypeInfo, 
                    level: 2,
                    attrMore: 'ng-click="fieldItemClick($event);"'
                }
            );
            $scope.htmlFieldTree = $sce.trustAsHtml(htmlTree);
            
            $scope.formFielditemDefault = {
                    status: 1,
                    type: $scope.fieldTypeTag,
                    parent_id: $scope.rootFieldTree,
                    set: $scope.rootFieldTree,
                    name: ''
            };
            $scope.formFieldData = {};
            $scope.formFieldData.item = angular.copy($scope.formFielditemDefault);
            setTimeout(function() {
                $scope.funcFieldCountTag(id);
                //first click item
                angular.element('.tag-field-content .tree-list li:eq(1)').trigger('click');
            }, 200);
        };
        
        //click item tree
        $scope.fieldItemClick = function($event) {
            $event.preventDefault();
            if ($scope.ajaxLoadindRequest) {
                return true;
            }
            var dom = angular.element($event.currentTarget),
                fieldActiveId = dom.data('id');
            if (dom.hasClass('active')) {
                return true;
            }
            dom.closest('.tab-tag-wrapper').find('.actions-group')
                .find('.btn-action[data-action="edit"]')
                .data('field-id', fieldActiveId);
            $scope.disableSubmitBtn = false;
            $scope.fieldActiveId = fieldActiveId;
            $scope.funcSetBtnAjaxLoading(true, fieldActiveId);
            $http({
                method: 'get',
                url: RKVarGlobalTag.urlFieldGetTagItem,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                params: {
                    id: fieldActiveId
                }
            }).then(function (response) {
                if (!response.data.success) {
                    $.notify({
                        message: response.data.message
                    },{
                        type: 'warning'
                    });
                    $scope.funcSetBtnAjaxLoading(false, fieldActiveId);
                    return true;
                }
                // set form fiel data flonw respon
                var templateUrl = $sce.getTrustedResourceUrl(
                    RKTagFunction.general.getTemplate('field-tag.html'));
                $scope.updatefieldPath(response.data.fieldsPath);
                // load template tag
                $scope.tags = RKTagFunction.general.fieldTagTypes(response.data.tag);
                $scope.fieldItem = response.data.field;
                $scope.fieldTagsName = $scope.fieldPath[$scope.rootFieldTree][fieldActiveId].data.name;
                // check file type is tag or not child
                $scope.funcFieldTagAvai(response.data.field.type, fieldActiveId);
                $templateRequest(templateUrl).then(function(template) {
                    $compile(
                        $(".field-tag-wrapper")
                            .html(template).contents()
                    )($scope);
                    // call tag ui
                    if ($scope.fieldTags) {
                        $scope.funcCallTagIT({color: response.data.field.color});
                        $scope.fieldWrapperUi = 1;
                    } else if ($scope.fieldItem.type == $scope.varGlobalTag.fieldTypeInfo &&
                        $scope.fieldPath[$scope.rootFieldTree][fieldActiveId].parent.length >= 2
                    ) {
                        $scope.fieldWrapperUi = 2;
                    } else {
                        $scope.fieldWrapperUi = 0;
                    }
                }, function() {
                    RKTagFunction.general.notifyError();
                });
                $scope.funcSetBtnAjaxLoading(false, fieldActiveId);
            }, function (response) {
                RKTagFunction.general.notifyError();
                $scope.funcSetBtnAjaxLoading(false, fieldActiveId);
            });
        };
        
        /**
         * edit form popup
         */
        $scope.fieldEditorClick = function($event) {
            if ($scope.ajaxLoadindRequest) {
                return true;
            }
            var dom = angular.element($event.currentTarget),
                fieldActiveId = dom.data('field-id'),
                iconAjaxLoad = dom.find('.submit-ajax-refresh');
            iconAjaxLoad.removeClass('hidden');
            $scope.funcSetBtnAjaxLoading(true, fieldActiveId);
            $http({
                method: 'get',
                url: RKVarGlobalTag.urlFieldGetItem,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                params: {
                    id: fieldActiveId
                }
            }).then(function (response) {
                if (!response.data.success) {
                    $.notify({
                        message: response.data.message
                    },{
                        type: 'warning'
                    });
                    iconAjaxLoad.addClass('hidden');
                    $scope.funcSetBtnAjaxLoading(false, fieldActiveId);
                    return true;
                }
                // set form fiel data flonw respon
                if (response.data.field) {
                    $scope.formFieldData.item = RKTagFunction.general
                        .jsonToInt(response.data.field);
                    
                    $scope.modalFieldEditTitle = response.data.field.name;
                } else {
                    angular.copy($scope.formFielditemDefault, $scope.formFieldData.item);
                    if ($scope.fieldActiveId) {
                        $scope.formFieldData.item.parent_id = $scope.fieldActiveId;
                    }
                    $scope.modalFieldEditTitle = $scope.trans['Create new field'];
                }
                $scope.funcFieldTagInfo($scope.formFieldData.item.type);
                $scope.updatefieldPath(response.data.fieldsPath);
                var templateUrl = $sce.getTrustedResourceUrl(RKTagFunction.general.getTemplate('field-edit.html'));
                //load template modal edit field
                $templateRequest(templateUrl).then(function(template) {
                    $scope.fieldOptions = RKTagFunction.getHtmlFieldTree.init(
                        $scope.rootFieldTree, 
                        $scope.fieldPath[$scope.rootFieldTree], 
                        2, 
                        fieldActiveId,
                        {type: $scope.varGlobalTag.fieldTypeInfo}
                    );
                    // render and compile template modal field edit
                    $compile($(".field-manage-modal-wrapper").html(template).contents())($scope);
                    $('#modal-field-edit').modal('show');
                    $('#modal-field-edit').on('shown.bs.modal', function() {
                        $('#modal-field-edit input[name="item[color]"]').colorpicker({});
                    });
                }, function() {
                    RKTagFunction.general.notifyError();
                });
                iconAjaxLoad.addClass('hidden');
                $scope.funcSetBtnAjaxLoading(false, fieldActiveId);
            }, function (response) {
                RKTagFunction.general.notifyError();
                iconAjaxLoad.addClass('hidden');
                $scope.funcSetBtnAjaxLoading(false, fieldActiveId);
            });
        };
        /**
         * from submit field
         */
        $scope.formFieldSubmit = function($event) {
            $event.preventDefault();
            var dom = angular.element($event.currentTarget),
                fieldActiveId = $scope.formFieldData.item.id;
            if (!dom.valid()) {
                return true;
            }
            $http({
                method  : 'post',
                url     : dom.attr('action'),
                data    : $.param($scope.formFieldData),
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(function (response) {
                if (!response.data.success) {
                    RKTagFunction.general.notifyError(response.data.message);
                    return true;
                }
                RKTagFunction.general.notifySuccess(response.data.message);
                fieldActiveId = response.data.field.id;
                if (response.data.field.is_new) { //create new field
                    var domChild = RKTagFunction.general.treeCreateItem($scope.rootFieldTree, 
                        response.data.field.parent_id, response.data.field);
                    if (domChild) {
                        $compile(domChild.parent().contents())($scope);
                        setTimeout(function() {
                            domChild.trigger('click'); 
                            if ($scope.fieldTags) {
                                $scope.funcCallTagIT({color: response.data.field.color});
                            }
                        }, 300);
                    }
                } else { // edit field
                    var domField = $('.tree-list .field-item[data-id="'+fieldActiveId+'"]');
                    domField.find('.field-text').html(response.data.field.name);
                    if (response.data.field.parent_id_old != response.data.field.parent_id) {
                        RKTagFunction.general.treeCreateItem($scope.rootFieldTree, 
                            response.data.field.parent_id, null, domField);
                    }
                    $scope.fieldPath[$scope.rootFieldTree][fieldActiveId].data.name = $scope.formFieldData.item.name;
                    $scope.fieldTagsName = $scope.formFieldData.item.name;
                    $scope.fieldItem.color = response.data.field.color;
                    $scope.fieldPath[$scope.rootFieldTree][fieldActiveId].data.color = response.data.field.color;
                    $scope.funcFieldTagAvai(response.data.field.type, response.data.field.id);
                    setTimeout(function() {
                        RKTagFunction.tagUi.updateColor(response.data.field.color, domField);
                    });
                }
                $('#modal-field-edit').modal('hide');
                if ($('.field-item[data-id="'+response.data.field.id+'"]').length) {
                    $('html, body').animate({
                        scrollTop: $('.field-item[data-id="'+response.data.field.id+'"]')
                            .offset().top - 200
                    }, 300);
                }
            },function (response) {
                RKTagFunction.general.notifyError();
            });
        };
        /**
         * delete field
         */
        $scope.fieldDeleteClick = function($event) {
            if ($scope.ajaxLoadindRequest) {
                return true;
            }
            var dom = angular.element($event.currentTarget),
                fieldActiveId = dom.data('field-id'),
                iconAjaxLoad = dom.find('.submit-ajax-refresh');
            RKTagFunction.general.popupConfirmDanger({
                title: $scope.trans['Confirm'],
                message: $scope.trans['Are you sure delete this field and all children?'],
                ok: function(data) {
                    iconAjaxLoad.removeClass('hidden');
                    $scope.funcSetBtnAjaxLoading(true, fieldActiveId);
                    $http({
                        method: 'delete',
                        url: RKVarGlobalTag.urlFieldDelete,
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        params: {
                            id: fieldActiveId
                        }
                    }).then(function (response) {
                        if (!response.data.success) {
                            $.notify({
                                message: response.data.message
                            },{
                                type: 'warning'
                            });
                            iconAjaxLoad.addClass('hidden');
                            $scope.funcSetBtnAjaxLoading(false, fieldActiveId);
                            return true;
                        }
                        RKTagFunction.general.notifySuccess(response.data.message);
                        $('.field-item[data-id="'+fieldActiveId+'"]').remove();
                        $('.field-item[data-parent-id="'+fieldActiveId+'"]').remove();
                        $scope.fieldItem = {};
                        $scope.fieldActiveId = $scope.rootFieldTree;
                        iconAjaxLoad.addClass('hidden');
                        $scope.funcSetBtnAjaxLoading(false);
                    }, function (response) {
                        RKTagFunction.general.notifyError();
                        iconAjaxLoad.addClass('hidden');
                        $scope.funcSetBtnAjaxLoading(false, fieldActiveId);
                    });
                }
            },{id: fieldActiveId});
        };
        $scope.formFieldValidate = {
            rules: {
                'item[name]': {
                    required: true,
                    maxlength: 255
                }
            },
            messages: {
                'item[name]': {
                    required: $scope.trans['This field is required'],
                    maxlength: $scope.trans['Max length :number']
                }
            }
        };
        
        /**
         * check field is type info
         * 
         * @param {boolean} check
         * @returns {Boolean}
         */
        $scope.funcFieldTagInfo = function(type) {
            $scope.fieldTagInfo = type == $scope.varGlobalTag.fieldTypeInfo;
            return $scope.fieldTagInfo;
        };
        
        /**
         * check field availabel tag
         * 
         * @param {boolean} check
         * @returns {Boolean}
         */
        $scope.funcFieldTagAvai = function(type, id) {
            $scope.fieldTags = type == $scope.fieldTypeTag;
            if (typeof $scope.fieldPath[$scope.rootFieldTree][id] !== 'undefined' &&
                $scope.fieldPath[$scope.rootFieldTree][id].child.length) {
                $scope.fieldTags = false;
            }
            $scope.funcFieldTagInfo(type);
            return $scope.fieldTags;
        };
        
        /**
         * check field availabel color
         * 
         * @param {boolean} check
         * @returns {Boolean}
         */
        $scope.funcAvaiColor = function(id) {
            if (typeof $scope.fieldPath[$scope.rootFieldTree][id] === 'undefined') {
                return false;
            }
            var item = $scope.fieldPath[$scope.rootFieldTree][id];
            // not type tag or have child or not color
            if (item.data.type != $scope.varGlobalTag.fieldTypeTag ||
                item.child.length ||
                !item.data.color
            ) {
                return false;
            }
            return true;
        };
        
        /**
         * check field availabel tag
         * 
         * @param {boolean} check
         * @returns {Boolean}
         */
        $scope.funcIsFieldTagAvai = function(id) {
            if (typeof $scope.fieldPath[$scope.rootFieldTree][id] === 'undefined') {
                return false;
            }
            var item = $scope.fieldPath[$scope.rootFieldTree][id];
            // not type tag or have child
            if (item.data.type != $scope.varGlobalTag.fieldTypeTag ||
                item.child.length
            ) {
                return false;
            }
            return true;
        };
        
        /**
         * call tag IT
         * 
         * @param {object} option
         */
        $scope.funcCallTagIT = function(option) {
            if (typeof option === 'undefined') {
                option = {};
            }
            setTimeout(function() {
                RKTagFunction.tagUi.list(
                    $('.tag-field-list[data-type="approve"]'), 
                    $scope.tags.approve, 
                    {
                        color: option.color,
                        checkDuplidate: '.tag-field-list[data-type="review"]',
                        compileAngular: true,
                        scope: $scope,
                        compile: $compile,
                        editor: true,
                        thenUpdateLabelTag: function(tagId, tagName, response) {
                            $scope.thenUpdateLabelTagApprove(tagId, tagName, response);
                        },
                        beforeChangeAny: function(dom, object) {
                            try {
                                $scope.tagReViewInactive();
                                object._inactiveHighlightTag($('.tag-field-list[data-type="review"]'));
                                $scope.$digest();
                            } catch (err) {}
                        }
                    }
                );
                RKTagFunction.tagUi.list(
                    $('.tag-field-list[data-type="review"]'),
                    $scope.tags.review,
                    {
                        color: option.color,
                        inputAddRemove: true,
                        tagReview: true,
                        classAdd: 'tag-review',
                        compileAngular: true,
                        scope: $scope,
                        compile: $compile,
                        funcOptions: function(dom, object) {
                            $scope.showTagReviewLink(dom, object);
                        },
                        beforeChangeAny: function(dom, object) {
                            try {
                                $scope.tagReViewInactive();
                                object._inactiveHighlightTag($('.tag-field-list[data-type="review"]'));
                                $scope.$digest();
                            } catch (err) {}
                        }
                    }
                );
                
            });
        };
        
        $scope.isShowTagReviewLink = false;
        /**
         * show select choose link tag review
         * 
         * @param {type} dom
         * @param {type} object
         * @return {undefined}
         */
        $scope.showTagReviewLink = function(dom, object) {
            $(document).on('dblclick', dom.selector + ' .tagit-choice.tagit-choice-editable', function(event) {
                if (object.flagIsDelete) {
                    return true;
                }
                var domThis = $(this),
                idTag = domThis.data('id');
                if (!idTag) {
                    return true;
                }
                if (!object._activeHighlightTag(dom, domThis)) {
                    return true;
                }
                $scope.isShowTagReviewLink = true;
                $scope.tagReviewLinkOrgId = idTag;
                try {
                    $scope.$digest();
                } catch (err) {}
            });
        };
        
        /**
         * call back after update label success
         *
         * @param {int} tagId
         * @param {string} tagName
         * @param {object} response
         */
        $scope.thenUpdateLabelTagApprove = function(tagId, tagName, response) {
            if (typeof $scope.tags.approve === 'undefined') {
                $scope.tags.approve = {};
            }
            $scope.tags.approve[tagId] = {
                id: tagId,
                value: tagName,
                stauts: 1
            };
            $('select[data-s2-etype="reviewLink"]').trigger("change");
        };
        
        /**
         * delete after tag approve
         * 
         * @param {type} tagId
         * @return {Boolean}
         */
        $scope.thenDeleteApprove = function(tagId) {
            if (typeof $scope.tags.approve[tagId] === 'undefined') {
                return true;
            }
            delete $scope.tags.approve[tagId];
            $scope.tagReViewInactive();
        };
        
        /**
         * tag review link in active
         */
        $scope.tagReViewInactive = function() {
            $scope.isShowTagReviewLink = false;
            try {
                $('select[data-s2-etype="reviewLink"]').trigger("change");
            } catch (err) {}
        };
        
        /**
         * submit tag link review
         */
        $scope.tagReviewLinkSubmit = function() {
            var tagReviewLinkId = $('select[data-s2-etype="reviewLink"]').val();
            if (!tagReviewLinkId || !$scope.tagReviewLinkOrgId ||
                isNaN(tagReviewLinkId) || isNaN($scope.tagReviewLinkOrgId)) {
                return true;
            }
            if ($scope.tagReviewLinkSubmitProgress) {
                return true;
            }
            $scope.tagReviewLinkSubmitProgress = true;
            $http({
                method: 'post',
                url: $scope.varGlobalTag.urlTagReviewLinkSubmit,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                params: {
                    tagOrg: $scope.tagReviewLinkOrgId,
                    tagAs: tagReviewLinkId
                }
            }).then(function(response) {
                $scope.tagReviewLinkSubmitProgress = false;
                if (!response.data.success) {
                    RKTagFunction.general.notifyError(response.data.message);
                    return true;
                }
                RKTagFunction.general.notifySuccess(response.data.message);
                $scope.tagReViewInactive();
                $('.tag-field-list[data-type="approve"] li.tag-id-'+tagReviewLinkId)
                    .effect('highlight').effect('highlight').effect('highlight');
                if (typeof $scope.fieldTagReviewCount[$scope.fieldActiveId] !== 'undefined') {
                    $scope.fieldTagReviewCount[$scope.fieldActiveId]--;
                }
                $scope.fieldTagTotal[$scope.fieldActiveId]--;
                $('.tag-field-list[data-type="review"] li.tag-id-'+$scope.tagReviewLinkOrgId)
                        .remove();
            }).catch(function() {
                $scope.tagReviewLinkSubmitProgress = false;
                RKTagFunction.general.notifyError();
            });
        };
        
        /**
         * get length of object
         * 
         * @param {object} o
         */
        $scope.lengthObject = function(o) {
            return Object.keys(o).length;
        };
        
        /**
         * update field path
         * 
         * @param {object} fieldPath
         */
        $scope.updatefieldPath = function(fieldPath) {
            if (fieldPath) {
                $scope.fieldPath[$scope.rootFieldTree] = fieldPath;
            }
        };
        
        /**
         * set status for button submit
         * 
         * @param {boolean} status
         * @param {int} fieldActive
         */
        $scope.funcSetBtnAjaxLoading = function(status, fieldActive) {
            if (typeof fieldActive !== 'undefined' && fieldActive) {
                $scope.disableSubmitBtn = status;
            }
            $scope.disableSubmitBtnNew = status;
            $scope.ajaxLoadindRequest = status;
        };
        
        /**
         * count tag of field
         * 
         * @param {int} id
         */
        $scope.funcFieldCountTag = function(id) {
            var listItem = $('field-list-tree[data-type-id="'+id+
                '"] ul.tree-list li.field-item[data-id]'),
            fieldIds = [];
            if (!listItem.length) {
                return true;
            }
            listItem.each(function(i,v) {
                var id = parseInt($(v).data('id'));
                if ($scope.funcIsFieldTagAvai(id)) {
                    fieldIds.push(id);
                }
            });
            if (!fieldIds.length) {
                return true;
            }
            $http({
                method: 'get',
                url: $scope.varGlobalTag.urlFieldCountTag,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                params: {
                    'fieldIds': fieldIds.join('-')
                }
            }).then(function (response) {
                $scope.fieldTagReviewCount = {};
                $scope.fieldTagTotal = {};
                if (!response.data.count_tag_review.length) {
                    return true;
                }
                var index, item;
                for (index in response.data.count_tag_review) {
                    item = response.data.count_tag_review[index];
                    $scope.fieldTagReviewCount[parseInt(item.field_id)] = 
                        parseInt(item.total_tag_review);
                    $scope.fieldTagTotal[parseInt(item.field_id)] = 
                        parseInt(item.total_tag);
                }
            });
        };
        
        /**
         * approve tag
         * 
         * @param {object} $event
         * @param {int} id
         */
        $scope.funcFieldTagApprove = function($event, id) {
            var tagDom = $($event.currentTarget).closest('li');
            if (!id || tagDom.hasClass('ajax-loadings')) {
                return false;
            }
            tagDom.addClass('ajax-loading');
            $http({
                method: 'post',
                url: $scope.varGlobalTag.urlFieldApproveTagItem,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                dataType: 'json',
                data: $.param({
                    id: id,
                    _token: siteConfigGlobal.token
                })
            }).then(function(response) {
                if (!response.data.success) {
                    RKTagFunction.general.notifyError(response.data.message);
                    return true;
                }
                RKTagFunction.general.notifySuccess(response.data.message);
                // remove tag in review add add tag in approve box
                tagDom.remove();
                $('.tag-field-list[data-type="approve"]')
                    .tagit('createTag', response.data.tagItem.value, 
                        ' tag-id-'+response.data.tagItem.id);
                if (typeof $scope.fieldTagReviewCount[$scope.fieldActiveId] === 'undefined') {
                    return true;
                }
                $scope.fieldTagReviewCount[$scope.fieldActiveId]--;
            });
        };
        
        /**
         * delete tag
         * 
         * @param {object} $event
         * @param {int} id
         */
        $scope.funcFieldTagDelete = function(tag, option) {
            var idTag = tag.data('id');
            if (!idTag) {
                return false;
            }
            $http({
                method: 'delete',
                url: $scope.varGlobalTag.urlFieldDeleteTagItem,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                dataType: 'json',
                data: $.param({
                    id: idTag,
                    _token: siteConfigGlobal.token
                })
            }).then(function(response) {
                if (typeof option.callback === 'function') {
                    option.callback();
                }
                if (!response.data.success) {
                    RKTagFunction.general.notifyError(response.data.message);
                    return true;
                }
                RKTagFunction.general.notifySuccess(response.data.message);
                if (tag.hasClass('tag-review')) {
                    if (typeof $scope.fieldTagReviewCount[$scope.fieldActiveId] !== 'undefined') {
                        $scope.fieldTagReviewCount[$scope.fieldActiveId]--;
                    }
                }
                $scope.fieldTagTotal[$scope.fieldActiveId]--;
                if (typeof option.thenUpdateLabelTag === 'function') {
                    $scope.thenDeleteApprove(idTag);
                }
            }, function() {
                if (typeof option.callback === 'function') {
                    option.callback();
                }
                RKTagFunction.general.notifyError();
            });
            return true;
        };
        
        /**
         * field add tag
         * 
         * @param {int} fieldId
         * @param {jquery object dom} tagDom
         * @param {string} tagValue
         */
        $scope.funcFieldTagAdd = function(fieldId, tagDom, tagValue, option) {
            $http({
                method: 'post',
                url: $scope.varGlobalTag.urlFieldAddTagItem,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                dataType: 'json',
                data: $.param({
                    field_id: fieldId,
                    tag_name: tagValue,
                    _token: siteConfigGlobal.token
                })
            }).then(function(response) {
                if (!response.data.success) {
                    RKTagFunction.general.notifyError(response.data.message);
                    return true;
                }
                tagDom.data('id', response.data.tagItem.id);
                RKTagFunction.general.notifySuccess(response.data.message);
                $scope.fieldTagTotal[fieldId]++;
                if (typeof option.thenUpdateLabelTag === 'function') {
                    $scope.thenUpdateLabelTagApprove(response.data.tagItem.id, tagValue);
                }
            }, function (response) {
                RKTagFunction.general.notifyError();
                $scope.funcSetBtnAjaxLoading(false, fieldActiveId);
            });
        };
        /*
         * option select 2
         */
        $scope.select2OptionsNotSearch = RKTagFunction.select2.notSearch();
    
});
})(angular, jQuery, RKVarGlobalTag, RKTagFunction);