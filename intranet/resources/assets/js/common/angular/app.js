(function (angular, $, RKExternal) {
var RKApp = angular
    .module('RkApp', [])
    .config(function ($interpolateProvider) {
        $interpolateProvider.startSymbol('<%=');
        $interpolateProvider.endSymbol('%>');
    });

//filter trusted html
RKApp.filter('trustHtml', function ($sce) {
    return function (text) {
        return $sce.trustAsHtml(text);
    };
});

//select2
/**
 * ng-select2
 * 
 * require: select2 lib
 * param attribute html:
 *      data-ng-select2="{json string}"
 *      data-select2-enforce-focus="0|1"
 *      data-select2-remoteUrl="url": url remote search
 *      data-select2-model="string": model of angular, set value
 *      data-select2-text="string": model of angualr, set text label
 *      data-select2-search="0": not show search box, default show
 *      
 *      data-select2-changed(event, data): event chagne select2 value data
 */
RKApp.directive('ngSelect2', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        priority: 1,
        link: function (scope, element, attrs) {
            if (typeof $().select2 !== 'function') {
                return true;
            }
            var agEl = angular.element(element);
            if (typeof attrs.select2EnforceFocus === 'undefined' ||
                attrs.select2EnforceFocus !== '0'
            ) {
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
                        typeof scope[attrs.select2Changed] === 'function'
                    ) {
                        scope[attrs.select2Changed](event, value);
                    }
                });
            }
        }
    };
}]);
// end select2

/**
 * directive more less text
 *
 * directive: ng-moretext="textOfScope"
 * option:
 *      data-moretext-length="100"
 *      data-moretext-textmore="more" 
 *      data-moretext-textless="less"
 */
RKApp.directive('ngMoretext', ['$compile', function ($compile) {
    var directive = {};
    directive.restrict = 'A';
    directive.scope = true;
    directive.link = function (scope, element, attrs) {
        /* start collapse */
        scope.expand = false;
        /* create the function to toggle the collapse */
        /**
         * toogle expand
         */
        scope.toggleExpand = function () {
            scope.expand = !scope.expand;
        };
        var textMore = attrs.moretextTextLess,
                textLess = attrs.moretextTextless;
        if (!textMore) {
            textMore = 'more';
        }
        if (!textLess) {
            textLess = 'less';
        }
        var model = attrs.ngMoretext;
        /* wait for changes on the text */
        scope.$watch(model, function (text) {
            /* get the length from the attributes */
            var maxLength = scope.$eval(attrs.moretextLength);
            if (text.length <= maxLength) {
                element.empty();
                element.append(text);
                return true;
            }
            element.addClass('moretext');
            /* split the text in two parts, the first always showing */
            var firstPart = String(text).substring(0, maxLength),
                    secondPart = String(text).substring(maxLength);

            /* create some new html elements to hold the separate info */
            var firstSpan = $compile('<span class="moretext-first">' + firstPart + '</span>')(scope),
                    secondSpan = $compile('<span class="moretext-second" ng-if="expand">' + secondPart + '</span>')(scope),
                    moreIndicatorSpan = $compile('<span class="moretext-dot" ng-if="!expand">... </span>')(scope),
                    lineBreak = $compile('<br ng-if="expand">')(scope),
                    toggleButton = $compile('<span class="moretext-btn" ng-click="toggleExpand()" ng-bind="expand ? \'' + textLess + '\' : \'' + textMore + '\'"></span>')(scope);
            /* remove the current contents of the element and add the new ones we created */
            element.empty();
            element.append(firstSpan);
            element.append(secondSpan);
            element.append(moreIndicatorSpan);
            element.append(lineBreak);
            element.append(toggleButton);
        });
    };
    return directive;
}]);
  
//factory
RKApp.factory('CoreFactory', function() {
    var factory = {};
    /**
     * set title page
     *  
     * @param {type} $scope
     * @return {undefined}
     */
    factory.titlePage = function($scope) {
        $scope.varGlobal.titlePage = $scope.trans[$scope.varGlobal.titlePage];
    };
    
    /**
     * process pager collection
     *
     *  Param pagerCollection:
     *      data: []
     *      current_page: number
     *      last_page: number
     *      total: number
     *      
     * @param {type} $scope
     * @return {Boolean}
     */
    factory.pager = function($scope) {
        if (!$scope.pagerCollection ||
            !$scope.pagerCollection.total ||
            $scope.pagerCollection.last_page == 1
        ) {
            $scope.pagerCollection.is_show_pager = false;
            return true;
        }
        $scope.pagerCollection.is_show_pager = true;
        var option = {
            show: 5,
            param: 'page'
        },
        middleShow = (option.show - 1) / 2,
        startPage = $scope.pagerCollection.current_page - middleShow,
        endPage = $scope.pagerCollection.current_page + middleShow,
        index;
        $scope.pagerCollection.is_show_first = $scope.pagerCollection.current_page
            > (option.show + 1) / 2;
        $scope.pagerCollection.is_show_last = $scope.pagerCollection.last_page
            > $scope.pagerCollection.current_page + middleShow;
        $scope.pagerCollection.is_show_prev = $scope.pagerCollection.current_page > 1;
        $scope.pagerCollection.is_show_next = $scope.pagerCollection.current_page < 
            $scope.pagerCollection.last_page;
        if (startPage < 1) {
            endPage += 1 - startPage;
            startPage = 1;
        }
        if (endPage > $scope.pagerCollection.last_page) {
            startPage -= endPage - $scope.pagerCollection.last_page;
            if (startPage < 1) {
                startPage = 1;
            }
            endPage = $scope.pagerCollection.last_page;
        }
        endPage++;
        $scope.pagerCollection.range = [];
        for (index = startPage; index < endPage; index++) {
            $scope.pagerCollection.range.push(index);
        }
        $scope.pagerCollection.param = option.param;
        return $scope.pagerCollection;
    };
    
    /**
     * ng-include from extenal file
     *      
     *      loaded file:
     *          <ng-include src="'http://domain.com/file.html'" onload="loadedFlag = true"></ng-include>
     * 
     * @param {object} $scope
     * @param {string} strLoadedFlag
     * @param {object} ngIncludes {varInclude: templateSrting}
     */
    factory.includeTemplate = function($scope, strLoadedFlag, ngIncludes) {
        $scope[strLoadedFlag] = false;
        $scope.$watch(strLoadedFlag, function() {
            if (!$scope[strLoadedFlag]) {
                return true;
            }
            angular.forEach(ngIncludes, function(template, varInclude) {
                $scope[varInclude] = template;
            });
        });
    };
    
    /**
     * init page
     * 
     * @param {object} $scope
     * @return {unresolved}
     */
    factory.initPage = function($scope) {
        $scope.token = siteConfigGlobal.token;
        return $scope;
    };
    
    /**
     * submit form ajax
     * 
     * @param {object} $scope
     * @param {object} $http
     * @param {object} dom
     * @param {object} data
     * @return {unresolved}
     */
    factory.submitAjax = function($scope, $http, dom, data) {
        $http({
            method  : 'POST',
            url     : dom.attr('action'),
            data    : $.param(data),
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).then(function (response) {
            if (typeof response.data.reload !== 'undefined' && 
                response.data.reload == 1
            ) {
                window.location.reload();
                return true;
            }
            if (!response.data.success) {
                RKExternal.notify(response.data.message, false);
                return true;
            }
            if (typeof response.data.popup === 'undefined' || 
                response.data.popup == 1
            ) {
                RKExternal.notify(response.data.message, true);
            }
        }).catch(function(response) {
            if (typeof response.data !== 'undefined' && response.data.message) {
                RKExternal.notify(response.data.message, false);
                return;
            }
            window.location.href = '/';
        });
        return $scope;
    };
    
    /**
     * pager click event
     * 
     * @param {type} $scope
     * @param {type} page
     * @param {object} option: $event, $location, $http, pageDom
     * @return {Boolean}
     */
    factory.pagerClick = function($scope, page, option) {
        if (option.$event) {
            option.$event.preventDefault();
        }
        if (page === null) {
            page = option.$location.search().page;
            if (!page) {
                page = 1;
            }
        }
        if (isNaN(page)) {
            return true;
        }
        page = parseInt(page);
        if (page <= 1) {
            page = null;
        }
        if (!option.pageDom) {
            var dom = angular.element(option.$event.currentTarget);
            option.pageDom = dom.closest('[data-pager="page"]');
        }
        if (!option.pageDom.length) {
            return true;
        }
        option.$location.search('page', page);
        RKExternal.progressBar.start();
        option.$http({
            method  : 'GET',
            url     : option.pageDom.attr('data-pager-url'),
            params  : option.$location.search(),
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).then(function (response) {
            $scope.pagerCollection = response.data.pagerCollection;
            factory.pager($scope);
            if (typeof option.success === 'function') {
                option.success(response.data);
            }
            RKExternal.progressBar.end();
        }).catch(function(response) {
            RKExternal.progressBar.end();
            if (typeof response.data !== 'undefined' && response.data.message) {
                RKExternal.notify(response.data.message, false);
                return;
            }
            window.location.href = '/';
        });
    };
    
    /**
     * convert item object to int
     *
     * @param {object} item
     * @param {key} arrayKey
     * @return {undefined}
     */
    factory.parseInt = function(item, arrayKey) {
        angular.forEach(arrayKey, function(key) {
            if (typeof item[key] === 'undefined' || isNaN(item[key])) {
                return true;
            }
            item[key] = parseInt(item[key]);
        });
        return item;
    };
    return factory;
});

RKApp.factory('CoreConstFactory', function() {
    var factory = {};
    
    /**
     * yes no options
     * 
     * @param {object} $scope
     * @return {array}
     */
    factory.yesNoOptions = function($scope) {
        $scope.yesNoOptions = {
            0: {
                label: 'No',
                value: 0
            },
            1: {
                label: 'Yes',
                value: 1
            }
        };
        return $scope;
    };
    
    /**
     * yes no options
     * 
     * @param {object} $scope
     * @return {array}
     */
    factory.activeOptions = function($scope) {
        $scope.activeOptions = {
            0: {
                label: 'Inactive',
                value: 0
            },
            1: {
                label: 'Active',
                value: 1
            }
        };
        return $scope;
    };
    
    /**
     * yes no options
     * 
     * @param {object} $scope
     * @return {array}
     */
    factory.visibleOptions = function($scope) {
        $scope.visibleOptions = {
            0: {
                label: 'Private',
                value: 0
            },
            1: {
                label: 'Public',
                value: 1
            }
        };
        return $scope;
    };
    return factory;
});
//end factory

/**
 * global function, apply for all controller
 */
RKApp.run(function ($rootScope, $location, $http, CoreFactory) {
    
});

window.RKApp = RKApp;
})(angular, jQuery, RKExternal);
