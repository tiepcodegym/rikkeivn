(function($, RKApp, RKExternal){
RKApp.factory('qaFactory', function() {
    var factory = {};
    return factory;
});

RKApp.controller('QAController', function(
    $scope, $http, $location, $timeout, CoreFactory, CoreConstFactory
) {
    $scope.longText = 'Không thể <b>đến</b> trường được nữa đâu nhé Không thể đến trường được nữa đâu nhé Không thể đến trường được nữa đâu nhé "Không thể đến" trường được nữa đâu nhéKhông thể đến trường được nữa đâu nhéKhông thể đến trường được nữa đâu nhéKhông thể<br/> đến trường được nữa đâu nhéKhông thể đến trường được nữa đâu nhéKhông thể đến trường được nữa đâu nhéKhông thể đến trường được nữa đâu nhéKhông thể đến trường được nữa đâu nhéKhông thể <b>đến trường được</b> nữa đâu nhéKhông thể đến trường được nữa đâu nhéKhông thể đến trường được nữa đâu nhéKhông thể đến trường được nữa đâu nhéKhông thể đến trường được nữa đâu nhéKhông thể đến trường được nữa đâu nhé.';
    $scope.trans = RKTransQA;
    $scope.varGlobal = RKVarGlobalQA;
    CoreFactory.titlePage($scope);
    CoreFactory.initPage($scope);
    CoreFactory.includeTemplate($scope, 'loadedQATemplate', {
        'qaMenuLeft': 'qa-menu-left.html',
        'qaCateList': 'qa-cate-list.html',
        'qaCateEdit': 'qa-cate-edit.html'
    });
    CoreFactory.includeTemplate($scope, 'loadedCoreTemplate', {
        'corePager': 'core-pager.html'
    });
    CoreConstFactory.activeOptions($scope);
    CoreConstFactory.visibleOptions($scope);
    
    $scope.pageActive = $scope.varGlobal.pageActive;
    
    
    /**
     * edit/add form category
     * 
     * @param {type} cateId
     * @return {undefined}
     */
    $scope.funcCateEdit = function(cateId) {
        $scope.formCate = {
            titlePopup: $scope.trans['Create category']
        };
        $scope.formCateData = {
            item: {
                active: 1,
                public: 1
            },
            _token: $scope.token
        };
        // request category item
        if (!cateId) {
            $scope.showPopupCateEdit();
            return true;
        }
        $http({
            url: $scope.varGlobal.urlQaCateGetItem,
            method: 'get',
            params: {
                id: cateId
            },
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).then(function(response) {
            if (!response.data.success) {
                RKExternal.notify(response.data.message, false);
                return true;
            }
            $scope.formCateData.item = CoreFactory.parseInt(response.data.item,
                ['active', 'public']);
            $scope.showPopupCateEdit();
        }).catch(function(response) {
            if (typeof response.data !== 'undefined' && response.data.message) {
                RKExternal.notify(response.data.message, false);
                return;
            }
            window.location.href = '/';
        });
    };
    
    /**
     * show popup edit cate
     */
    $scope.showPopupCateEdit = function() {
        $('#modal-qa-cate-edit').modal('show');
        $timeout(function() {
            $('[name="item[active]"]').trigger('change');
            $('[name="item[public]"]').trigger('change');
        });
    };
    
    /**
     * submit form cateogry 
     */
    $scope.formQaCateSubmit = function($event) {
        $event.preventDefault();
        var dom = $($event.currentTarget);
        dom.validate({
            rules: {
                'item[name]': {
                    required: true,
                    maxlength: 255
                },
                'item[content]': {
                    required: true
                }
            }
        });
        if (dom.valid()) {
            CoreFactory.submitAjax($scope, $http, dom, $scope.formCateData);
        }
    };

    /**
     * pager click event of category list
     * 
     * @param {type} $event
     * @param {type} page
     */
    $scope.pagerClick = function($event, page) {
        CoreFactory.pagerClick($scope, page, {
            $event: $event,
            $location: $location,
            $http: $http
        });
    };
    var cateActive = $location.search().active;
    if (typeof cateActive === 'undefined' || cateActive === '1') {
        cateActive = 1;
    } else {
        cateActive = 0;
    }
    $scope.cateActive = cateActive;
    
    CoreFactory.pagerClick($scope, null, {
        $location: $location,
        $http: $http,
        pageDom: $('.qa-list-cate'),
        success: function(response) {
            $scope.varGlobal.qaMenuLeft = response.qaMenuLeft;
        }
    });
    
    $scope.funcCateList = function($event, active) {
        $scope.pageActive = 'cate';
        $scope.cateActive = active;
        $location.search('active', active);
        CoreFactory.pagerClick($scope, 0, {
            $event: $event,
            $location: $location,
            $http: $http
        });
    };
});
    
})(jQuery, RKApp, RKExternal);