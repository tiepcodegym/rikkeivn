(function (angular,$, RKTagFunction) {
rkTagApp = angular.module('RkTagApp', [
    'ngValidate', 
    'ui.select2', 
    'angularModalService'
])
    .config(function ($interpolateProvider) {
        $interpolateProvider.startSymbol('<%=');
        $interpolateProvider.endSymbol('%>');
    });

/**
 * run: define globel attribute/function
 */
rkTagApp.run(function ($rootScope, $location, $sce, $http, $timeout) {
    $rootScope.trans = RKTagTrans;
    $rootScope.globTag = RKVarGlobalTag;
    $rootScope.generalTag = RKTagFunction.general;
    //pager
    $rootScope.pager = {
        current_page: 1,
        next_page: null,
        last_page: 1,
        total: 0
    };
    $rootScope.dataLoaded = false;
    $rootScope.dataSearched = true;
    $rootScope.dataReseted = true;
    $rootScope.errorMess = null;

    $rootScope.dataFilterDefault = {
        search: {},
        order: 'created_at',
        dir: 'desc',
        tag: [],
        field: {},
        page: $rootScope.pager.next_page || 1
    };
    
    $rootScope.dataFilter = angular.copy($rootScope.dataFilterDefault);

    //init filter data
    $rootScope.initFilter = function (isDel) {
        if (typeof isDel == 'undefined') {
            isDel = false;
        }
        $rootScope.dataFilter = angular.copy($rootScope.dataFilterDefault);
        if (!isDel) {
            var dataFilter = $rootScope.getFilter();
            if (dataFilter && Object.keys(dataFilter).length) {
                $rootScope.dataFilter = $.extend($rootScope.dataFilter, dataFilter);
            }
        } else {
            $rootScope.saveFilter();
        }
    };
    //save filter
    $rootScope.saveFilter = function () {
        $location.search({});
        var stringLocation = '';
        angular.forEach($rootScope.dataFilter, function (value, key) {
            if (key === 'field') {
                stringLocation = RKTagFunction.general.encodeSlugField(value);
                if (stringLocation) {
                    $location.search(key, stringLocation);
                }
                return true;
            }
            if (key === 'tag') {
                if (value.length) {
                    $location.search(key, RKTagFunction.general.encodeSlugTag(value));
                }
                return true;
            }
            if (angular.isObject(value)) {
                if (Object.keys(value).length) {
                    $location.search(key, JSON.stringify(value));
                }
            } else {
                if (value) {
                    $location.search(key, value);
                }
            }
        });
    };
    //get filter
    $rootScope.getFilter = function () {
        var search = $location.search();
        var filters = {};
        angular.forEach(search, function (value, key) {
            if (key === 'field') {
                var fieldTags = RKTagFunction.general.decodeSlugField(value);
                filters[key] = fieldTags.field;
                $rootScope.filterTagSelected = fieldTags.tag;
                return true;
            }
            if (key === 'tag') {
                filters[key] = RKTagFunction.general.decodeSlugTag(value);
                return true;
            }
            try {
                filters[key] = JSON.parse(value);
            } catch (e) {
                filters[key] = value;
            }
        });
        return filters;
    };
    //get class sorting
    $rootScope.classSorting = function (orderby) {
        if ($rootScope.dataFilter.order == orderby) {
            if ($rootScope.dataFilter.dir == 'asc') {
                return 'sorting_asc';
            }
            return 'sorting_desc';
        }
        return '';
    };
    //limit pages
    $rootScope.limitPages = [];
    if (typeof RKVarGlobalTag.limitPages != 'undefined') {
        $rootScope.limitPages = JSON.parse(RKVarGlobalTag.limitPages);
    }
    //get account name from email
    $rootScope.convertAccount = function (email) {
        if (!angular.isDefined(email) || !email) {
            return null;
        }
        var name = email.split("@");
        if (name.length < 2) {
            return email;
        }
        return name[0];
    };
    //split group concat items
    $rootScope.toArrayFromStr = function (str, separator) {
        if (!str) {
            return [];
        }
        if (typeof separator == 'undefined') {
            separator = ",";
        }
        var arrayItem = str.split(separator);
        return arrayItem;
    };
    //generate list tagit
    $rootScope.generateTagHtml = function (listTags, projectId) {
        var tagHtml = '';
        if (listTags) {
            tagHtml = '<ul class="tagit tag-field-list">';
            angular.forEach(listTags, function (item, idx) {
                if (typeof item === 'undefined') {
                    return true;
                }
                if (idx < RKVarGlobalTag.SHOW_NUM_TAGS) {
                    tagHtml += '<li class="tagit-choice tagit-choice-read-only" style="background-color: '+ item.color +';">' + 
                            '<span class="tagit-label">' + item.value + '</span>' +
                            '</li>';
                } else {
                    return false;
                }
            });
            tagHtml += '</ul>';
        }
        if (angular.isDefined(projectId)) {
            angular.element('tr[data-id="'+ projectId +'"] .td-tags').html(tagHtml);
        } else {
            return $sce.trustAsHtml(tagHtml);
        };
    };
    
    /*
     * cut string team names
     */
    $rootScope.trimObjNames = function(names, chLimit, symbolSplit) {
        if (!names) {
            return '';
        }
        if (!angular.isDefined(chLimit)) {
            chLimit = 25;
        }
        if (!angular.isDefined(symbolSplit)) {
            symbolSplit = ', ';
        }
        if (names.length <= chLimit) {
            return names;
        }
        var arrNames = names.split(symbolSplit);
        var result = [];
        for (var i in arrNames) {
            if ((result.length + arrNames[i].length + 2) < chLimit) {
                result += (result.length === 0 ? '' : symbolSplit) + arrNames[i];
            } else {
                break;
            }
        }
        return result.length === 0 ? names.substr(0, chLimit - 5) + '...' : result + symbolSplit + '...';
    };
    
    /**
     * split string format id:name to html
     *  format: id1:name1|id2:name2
     * 
     * @param {string} names
     * @param {int} activeId
     * @param {int} chLimit
     * @param {string} symbolSplit
     * @returns {String}
     */
    $rootScope.splitIdNameHtml = function(names, activeId, chLimit) {
        if (!names) {
            return '';
        }
        if (!angular.isDefined(chLimit)) {
            chLimit = 25;
        }
        var resultText = '', resultHtml = '', limitOver = false;
        angular.forEach(names.split('|'), function(item) {
            var splitIdName = item.split(':');
            if (splitIdName.length !== 2) {
                return true;
            }
            if ((resultText.length + splitIdName[1].length) > chLimit) {
                limitOver = true;
            } else {
                resultHtml += '<span data-team-id="' + splitIdName[0] + '">' 
                    + splitIdName[1] + '</span><span data-separator="1">, </span>';
            }
            resultText += splitIdName[1] + ', ';
        });
        resultText = resultText.slice(0, -2);
        resultHtml = resultHtml.slice(0, -34);
        if (!resultHtml) {
            resultHtml = resultText.substr(0, chLimit - 5);
        }
        if (limitOver) {
            resultHtml += '...';
        }
        return {
            text: resultText,
            html: resultHtml
        };
    };
    
    /**************************
     * Storage Tags
     *************************/
    var STORAGE = sessionStorage;
    var KEY_STORAGE_TAG = 'storage_list_tags';
    $rootScope.KEY_STORAGE_FIELD = 'storage_list_fields';
    
    /*
     * request to import tag data
     * @param object option: option.then() callback funciton
     */
    $rootScope.exportStorageTags = function (option) {
        if (angular.isDefined($rootScope.globTag.urlExportTags)) {
            $timeout(function () {
                var storageTagVer = $rootScope.getStorageItem('storage_tag_ver');
                $http.get($rootScope.globTag.urlExportTags, {
                    params: {
                        version: storageTagVer ? storageTagVer : 0
                    }
                }).then(function(result) {
                    if (result.data.tags) {
                        $rootScope.importStorageTags(result.data.tags, option);
                        $rootScope.setStorageItem('storage_tag_ver', result.data.version);
                        $rootScope.setStorageItem($rootScope.KEY_STORAGE_FIELD, result.data.fields);
                    }
                    $rootScope.localTagsOfField = $rootScope.getStorageTags();
                    if (typeof option !== 'undefined' && 
                        typeof option.then === 'function'
                    ) {
                        option.then();
                    }
                });
            }, 500);
        }
    };
    $rootScope.exportStorageTags();
    
    /*
     * import tag data
     */
    $rootScope.importStorageTags = function (data) {
        if (!angular.isDefined(STORAGE)) {
            return;
        }
        var importData = {};
        for (var i in data) {
            var tag = data[i];
            if (angular.isDefined(importData[tag.field_id])) {
                importData[tag.field_id].push({id: tag.id, value: tag.value.trim()});
            } else {
                importData[tag.field_id] = [{id: tag.id, value: tag.value.trim()}];
            }
        }
        try {
            STORAGE.setItem(KEY_STORAGE_TAG, JSON.stringify(importData));
        } catch (e) {
            return false;
        }
    };
    /*
     * get tag data
     */
    $rootScope.getStorageTags = function() {
        if (!angular.isDefined(STORAGE)) {
            return null;
        }
        var result = STORAGE.getItem(KEY_STORAGE_TAG);
        if (!result) {
            return null;
        }
        return JSON.parse(result);
    };
    
    $rootScope.getStorageItem = function (key) {
        if (!angular.isDefined(STORAGE)) {
            return null;
        }
        return STORAGE.getItem(key);
    };
    
    $rootScope.setStorageItem = function (key, value) {
        if (!angular.isDefined(STORAGE)) {
            return;
        }
        if (angular.isObject(value)) {
            value = JSON.stringify(value);
        }
        try {
            STORAGE.setItem(key, value);
        } catch (e) {
            return;
        }
    };
});
    
/**
 * complile html with directive 
 */
rkTagApp.directive('ngBindHtmlCompile', ['$compile', function ($compile) {
  return {
    restrict: 'AE',
    link: function (scope, element, attrs) {
      scope.$watch(function () {
        return scope.$eval(attrs.ngBindHtmlCompile);
      }, function (value) {
        // Incase value is a TrustedValueHolderType, sometimes it
        // needs to be explicitly called into a string in order to
        // get the HTML string.
        element.html(value && value.toString());
        // If scope is provided use it, otherwise use parent scope
        var compileScope = scope;
        if (attrs.bindHtmlScope) {
          compileScope = scope.$eval(attrs.bindHtmlScope);
        }
        $compile(element.contents())(compileScope);
      });
    }
  };
}]);

// pagination
rkTagApp.directive('ngPaginate', function ($rootScope) {
    return {
        restrict: 'A',
        templateUrl: RKTagFunction.general.getTemplate('pager.html')
    };
});

rkTagApp.directive('select2Local', function () {
    return {
        restrict: 'A',
        scope: {
            localData: '=localData'
        },
        link: function (scope, element, attrs) {
            var options = {};
            if (attrs.select2Local) {
                options = JSON.parse(attrs.select2Local);
            }
            if (typeof options.minimumInputLength === 'undefined') {
                options.minimumInputLength = 2;
            }
            //load data from local
            if (typeof attrs.select2Local != 'undefined' && attrs.select2Local) {
                var agEl = angular.element(element);
                scope.$watch('localData', function (value) {
                    if (value) {
                        agEl.show();
                        options.data = $.map(value, function (item) {
                            return {id: item.id, text: item.value};
                        });
                        //check excerpt id
                        var excerptIds = [];
                        if (angular.isDefined(attrs.excerpt) && attrs.excerpt) {
                            excerptIds = attrs.excerpt;
                        }
                        //custom search func
                        options.matcher = function (params, data) {
                            if (angular.isDefined(params.term) 
                                    && data.text.substr(0, params.term.length).toLowerCase() == params.term.toLowerCase() 
                                    && excerptIds.indexOf(data.id) < 0) {
                                return data;
                            }
                            return false;
                        };
                        agEl.select2(options);
                    } else {
                        agEl.hide();
                    }
                });
                return true;
            }
        }
    };
});

//select2
rkTagApp.directive('ngSelect2', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        priority: 1,
        link: function (scope, element, attrs) {
            if (typeof $().select2 === 'undefined') {
                return true;
            }
            var agEl = angular.element(element);
            if (typeof attrs.select2EnforceFocus === 'undefined' ||
                attrs.select2EnforceFocus !== '0') {
                try {
                    $.fn.modal.Constructor.prototype.enforceFocus = function(){};
                } catch(e) {}
            }
            var options = {};
            if (attrs.ngSelect2) {
                options = JSON.parse(attrs.ngSelect2);
            }
            if (typeof attrs.select2RemoteUrl === 'undefined' ||
                !attrs.select2RemoteUrl
            ) { 
                if (typeof attrs.select2Search !== 'undefined' && 
                    attrs.select2Search == '0') {
                    options.minimumResultsForSearch = Infinity;
                }
                agEl.select2(options);
                return true;
            }
            var __formatReponse = function (response) {
                if (response.loading) {
                    return response.text;
                }
                return "<div class='select2-result-repository clearfix'>" +
                        "<div class='select2-result-repository__title'>" + 
                        response.text + "</div>" +
                    "</div>";
            },
            __formatReponesSelection = function (response) {
                return  response.text;
            };
            //use remote
            setTimeout(function() {
                var remoteOptions = {
                    id: function(response){ 
                        return response.id;
                    },
                    ajax: {
                        url: attrs.select2RemoteUrl,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
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
                    minimumInputLength: 2,
                    templateResult: __formatReponse, // omitted for brevity, see the source of this page
                    templateSelection: __formatReponesSelection // omitted for brevity, see the source of this page
                };
                remoteOptions = angular.extend(remoteOptions, options);
                agEl.select2(remoteOptions);
            });
            //bind selected for select2 remote
            if (attrs.select2Model !== 'undefined' && attrs.select2Model) {
                agEl.on("select2:select", function (event) {
                    var value = event.params.data.id,
                    text = event.params.data.text,
                    getter = $parse(attrs.select2Model),
                    setter = getter.assign;
                    setter(scope, value);
                    if (attrs.select2Text !== 'undefined' && attrs.select2Text) {
                        getter = $parse(attrs.select2Text),
                        setter = getter.assign;
                        setter(scope, text);
                    }
                    // change value select bind
                    if (attrs.select2Changed !== 'undefined' &&
                        attrs.select2Changed &&
                        typeof scope[attrs.select2Changed] !== 'undefined'
                    ) {
                        scope[attrs.select2Changed](event, value);
                    }
                });
            }
        }
    };
}]);

//generate tagit readonly from list tags attrs
rkTagApp.directive('listTags', function ($rootScope) {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            attrs.$observe('listTags', function () {
                var arrayTags = $rootScope.toArrayFromStr(attrs.listTags, '-');
                generateTagHtml(arrayTags, element);
            });
        }
    };
    
    function generateTagHtml(arrayTags, element) {
        if (arrayTags.length > 0) {
            var tagHtml = '<ul class="tagit tag-field-list">';
            for (var i in arrayTags) {
                if (i < RKVarGlobalTag.SHOW_NUM_TAGS) {
                    var tagSpl = arrayTags[i].split('|');
                    var cssColor = '';
                    if (angular.isDefined(tagSpl[1]) && 
                        element.hasClass('tags-format-color')
                    ) {
                        cssColor = 'style="background-color: '+ tagSpl[1] +'"';
                    }
                    tagHtml += '<li class="tagit-choice tagit-choice-read-only" '+ cssColor +'>'+
                                '<span class="tagit-label">' + tagSpl[0] + '</span>'+
                            '</li>';
                } else {
                    break;
                }
            }
            if (arrayTags.length > RKVarGlobalTag.SHOW_NUM_TAGS) {
                tagHtml += '<li><span class="tagit-label">.....</span></li>';
            }
            tagHtml += '</ul>';
            element.html(tagHtml);
        } else {
            element.html('');
        }
    };
});

//filter trusted html
rkTagApp.filter('trustHtml', function ($sce) {
    return function (text) {
        return $sce.trustAsHtml(text);
    };
});
})(angular, jQuery, RKTagFunction);
