(function(angular, $, RKTagFunction, RKfuncion){
rkTagApp.controller('searchObjectTagController', function(
    $scope, $http, $timeout, $location, $sce, $httpParamSerializer, $q
) {
    $scope.trans = RKTagTrans;
    $scope.varGlobalTag = RKVarGlobalTag;
    
    $scope.isSearchPage = true;
    //init fields path
    $http.get($scope.varGlobalTag.urlGetFieldsPath, {})
            .then(function (result) {
                $scope.fieldsPath = result.data.fieldsPath;
                $scope.fields = result.data.fieldsPath;
                $scope.$emit('listen_fields', result.data.fieldsPath);
                
                $scope.teamList = RKfuncion.teamTree.init(
                    result.data.team
                );
                $scope.$broadcast('filter_team_loaded');
                
                $scope.maxMM = result.data.maxMM;
                $timeout(function () {
                    var searchParams = $location.search();
                    if (angular.isDefined(searchParams.field)) {
                        var fields = RKTagFunction.general.decodeSlugField(searchParams.field).field;
                        angular.forEach(fields, function (tags, fieldId) {
                            angular.element('#toggle_field_' + fieldId).trigger('click');
                        });
                    } else if (angular.isDefined(searchParams.search)) {
                        var search = searchParams.search;
                        if (search) {
                            search = JSON.parse(search);
                            if (angular.isDefined(search.billable_effort)) {
                                angular.element('#toggle_mm_checkbox').trigger('click');
                            } else if (angular.isDefined(search['tpj.team_id'])) {
                                angular.element('#toggle_team_checkbox').trigger('click');
                            } else {
                                angular.element('.fields-path .filter-box input[type="checkbox"]:first').trigger('click');
                            }
                        }
                    } else {
                        angular.element('.fields-path .filter-box input[type="checkbox"]:first').trigger('click');
                    }
                }, 1000);
            });
    
    /**
     * listen set tag selected
     */
    $scope.$on('tags_selected', function (event, data) {
        if (!data) {
            data = [];
        }
        $scope.tagsSelected = data;
    });
    // throw event
    $scope.$emit('is_search_page', $scope.isSearchPage);
    
    $scope.toggleFilterTag = function (tag, fieldId) {
        if (!$scope.dataLoaded) {
            return;
        }
        if (!angular.isDefined($scope.dataFilter.field[fieldId])) {
            $scope.dataFilter.field[fieldId] = [];
        }
        //check in tags selected
        var idx = $scope.tagsSelected.indexOf(tag.tag_id + ''),
            idxTag = $scope.dataFilter.field[fieldId].indexOf(tag.tag_id + '');
        if (idx > -1) { // selected
            $scope.tagsSelected.splice(idx, 1);
            if (idxTag > -1) {
                $scope.dataFilter.field[fieldId].splice(idxTag, 1);
                if (!$scope.dataFilter.field[fieldId].length) {
                    delete $scope.dataFilter.field[fieldId];
                }
            }
        } else { // non selected
            $scope.tagsSelected.push(tag.tag_id + '');
            if (idxTag < 0) {
                $scope.dataFilter.field[fieldId].push(tag.tag_id + '');
            }
        }
        $scope.getList($scope.dataFilter);
        $scope.saveFilter();
    };
    
    //listen tag data
    $scope.$on('tags_data_loaded', function (event) {
        initMinHeightContent();
    });
    
    //set min height content
    function initMinHeightContent() {
        $timeout(function () {
            var filterHeight = angular.element('.filter-col').height();
            angular.element('.content-container').css('min-height', filterHeight);
        }, 300);
    }
    
    //generate tag more url
    $scope.generateTagMoreUrl = function (fieldId) {
        var url = $scope.varGlobalTag.urlGetMoreTag;
        var params = {fieldId: fieldId};
        params['tagIdsExists[]'] = $scope.getCurrentTagOfField;
        return $sce.trustAsHtml(url + '/?' + $httpParamSerializer(params));
    };
    $scope.getCurrentTagOfField = function (fieldId) {
        if (angular.isDefined($scope.tagsOfField[fieldId])) {
            var tagIds = [];
            angular.forEach($scope.tagsOfField[fieldId], function (value, key) {
                tagIds.push(value.tag_id);
            });
            return tagIds;
        }
        return [];
    };
    
    $scope.MIN_SEARCH = 10;
    //on select search tags
    jQuery('body').on('change', '.search-tag-field', function () {
        var fieldId = $(this).data('id');
        var tagId = $(this).val();
        var tagText = $(this).find('option:selected').text();
        var tag = {
            tag_id: tagId,
            tag_name: tagText,
            tag_count: 0
        };
        $scope.tagsOfField[fieldId].push(tag);
        $scope.$apply();
        $scope.toggleFilterTag(tag, fieldId);
        $(this).next('.select2-container').find('.select2-selection__rendered').text('Search').attr('title', 'Search');
    });
    
    jQuery('body').on('click', '.filter-search .select2-container', function () {
        var scrollTop = jQuery(window).scrollTop();
        jQuery(window).scrollTop(scrollTop + 1);
    });
    jQuery('body').on('keyup', '.select2-container input.select2-search__field', function () {
        if ($(this).val().trim() == '') {
            var scrollTop = jQuery(window).scrollTop();
            jQuery(window).scrollTop(scrollTop + 1);
        }
    });
    
    // toggle check field, select tag
    $scope.toggleCheckField = function (fieldId, $event) {
        var elmBox = angular.element($event.target).closest('.filter-box');
        var checkbox = angular.element($event.target); 
        var isCheck = checkbox.is(":checked");
        var label = elmBox.find('.box-title label');
        
        $timeout(function () {
            checkbox.prop('checked', !label.hasClass('collapsed'));
        }, 50);
        
        if (!isCheck && angular.isDefined($scope.dataFilter.field[fieldId])) {
            var itvLoader = setInterval(function () {
                if ($scope.dataLoaded) {
                    clearInterval(itvLoader);

                    var splTags = $scope.dataFilter.field[fieldId];
                    for (var i = 0; i < splTags.length; i++) {
                        var tagId = splTags[i];
                        var idxTag = $scope.tagsSelected.indexOf(tagId + '');
                        if (idxTag > -1) {
                            $scope.tagsSelected.splice(idxTag, 1);
                        }
                    }
                    delete $scope.dataFilter.field[fieldId];
                    $scope.getList($scope.dataFilter);
                    $scope.saveFilter();
                }
            }, 500);
        }
        
        if (label.hasClass('collapsed')) {
            label.removeClass('active');
        } else {
            label.addClass('active');
        }
        
        $timeout(function () {
            elmBox.find('.select2-container').css('width', '100%');
        }, 200);
        
        initMinHeightContent();
    };
    
    //toggle filter team lit toggleFilterTag
    $scope.toggleFilterTeam = function (teamId) {
        var teamIds = [];
        if (angular.isDefined($scope.dataFilter.search) && angular.isDefined($scope.dataFilter.search['tpj.team_id'])) {
            teamIds = $scope.dataFilter.search['tpj.team_id'];
        }
        var idxTeam = teamIds.indexOf(teamId);
        if (idxTeam < 0) {
            teamIds.push(teamId);
        } else {
            teamIds.splice(idxTeam, 1);
        }
        if (!angular.isDefined($scope.dataFilter.search)) {
            $scope.dataFilter.search = {};
        }
        $scope.dataFilter.search['tpj.team_id'] = teamIds;
        $scope.getList($scope.dataFilter);
        $scope.saveFilter();
    };
    
    //toggle check team like toggle check field
    $scope.toggleCheckTeam = function (teamId, $event) {
        var elmBox = angular.element($event.target).closest('.filter-box');
        var checkbox = angular.element($event.target);
        var isCheck = checkbox.is(":checked");
        var label = elmBox.find('.box-title label');
        
        $timeout(function () {
            checkbox.prop('checked', !label.hasClass('collapsed'));
        }, 50);
        
        if (!isCheck && angular.isDefined($scope.dataFilter.search) 
                && angular.isDefined($scope.dataFilter.search['tpj.team_id'])) {
            
            var itvLoader = setInterval(function () {
                if ($scope.dataLoaded) {
                    clearInterval(itvLoader);
                    
                    delete $scope.dataFilter.search['tpj.team_id'];
                    $scope.getList($scope.dataFilter);
                    $scope.saveFilter();
                }
            }, 500);
        }
        
        if (label.hasClass('collapsed')) {
            label.removeClass('active');
        } else {
            label.addClass('active');
        }
    };
    //toggle check filter MM like toggle check field
    $scope.toggleCheckMM = function ($event) {
        var elmBox = angular.element($event.target).closest('.filter-box');
        var checkbox = angular.element($event.target); 
        var isCheck = checkbox.is(":checked");
        var label = elmBox.find('.box-title label');
        
        $timeout(function () {
            checkbox.prop('checked', !label.hasClass('collapsed'));
        }, 50);
        
        if (!isCheck && angular.isDefined($scope.dataFilter.search)
                && angular.isDefined($scope.dataFilter.search['billable_effort'])) {
            
            var itvLoader = setInterval(function () {
                if ($scope.dataLoaded) {
                    clearInterval(itvLoader);
                   
                    delete $scope.dataFilter.search['billable_effort'];
                    $scope.getList($scope.dataFilter);
                    $scope.saveFilter();
                    
                    $scope.filterMMStart = 0;
                    $scope.filterMMEnd = infinitySym;
                    $('#slider_mm').slider("values", [0, MAX_MM]);
                } 
            }, 500);
            
        }
        
        if (label.hasClass('collapsed')) {
            label.removeClass('active');
        } else {
            label.addClass('active');
        }
    };
    
    /*
     * count project of team
     */
    $scope.countProjOfTeam = function (teamId) {
        if (!angular.isDefined($scope.teamCountProj)) {
            return 0;
        }
        var count = 0;
        angular.forEach($scope.teamCountProj, function (dataCount) {
            if (dataCount.team_id == teamId) {
                count = dataCount.count_proj;
                return false;
            }
        });
        return count;
    };
    
    /*
     * count project of field 
     */
    $scope.countProjOfField = function (fieldId) {
        if (!angular.isDefined($scope.totalProjInField)) {
            return 0;
        }
        var count = 0;
        angular.forEach($scope.totalProjInField, function (dataCount) {
            if (dataCount.field_id == fieldId) {
                count = dataCount.total_item;
                return false;
            }
        });
        return count;
    };
    
    /*
     * toggle filter search
     */
    $scope.toggleFilterClose = true;
    $scope.toggleFilter = function () {
        var elmFilter = angular.element('.field-box-list');
        if ($scope.toggleFilterClose) {
            $scope.toggleFilterClose = false;
            elmFilter.addClass('ft-show');
        } else {
            $scope.toggleFilterClose = true;
            elmFilter.removeClass('ft-show');
        }
    };
    
    $scope.$on('total_employees', function (event, total) {
        $scope.totalEmployee = total || 0;
    });

    /*
     * init jquery slider
     */
    var MAX_MM = 100;
    var infinitySym = '∞';
    $scope.$watch('maxMM', function (newVal) {
        if (angular.isDefined(newVal)) {
            MAX_MM = parseInt(newVal) + 5;
        } else {
            return;
        }
        
        $scope.filterMMStart = 0;
        $scope.filterMMEnd = infinitySym;
        if (angular.isDefined($scope.dataFilter.search)) {
            if (angular.isDefined($scope.dataFilter.search['billable_effort'])) {
                $scope.filterMMStart = $scope.dataFilter.search['billable_effort'][0];
                if ($scope.dataFilter.search['billable_effort'][1] < MAX_MM) {
                    $scope.filterMMEnd = $scope.dataFilter.search['billable_effort'][1];
                }
            }
        }
        $('#slider_mm').each(function () {
            var elmThis = $(this);
            elmThis.slider({
                range: true,
                min: 0,
                max: MAX_MM,
                step: 0.1,
                values: [$scope.filterMMStart, MAX_MM],
                slide: function (event, ui) {
                    $scope.filterMMStart = ui.values[0];
                    $scope.filterMMEnd = ui.values[1];
                    if ($scope.filterMMEnd >= MAX_MM) {
                        $scope.filterMMEnd = infinitySym;
                    }
                    var step;
                    if (ui.value <= 10) {
                        step = 0.1;
                    } else {
                        step = 1;
                    }
                    elmThis.slider('option', 'step', step);
                    $scope.$apply();
                },
                change: function (event, ui) {
                    if (angular.isDefined($scope.dataFilter.search)) {
                        $scope.dataFilter.search['billable_effort'] = ui.values;
                    } else {
                        $scope.dataFilter.search = {
                            'billable_effort': ui.values
                        };
                    }
                    $scope.searchData(2);
                    if (ui.values[0] == 0 && ui.values[1] == MAX_MM) {
                        delete $scope.dataFilter.search['billable_effort'];
                        if (!$scope.dataFilter.search) {
                            delete $scope.dataFilter.search;
                        }
                    }
                    $scope.saveFilter($scope.dataFilter);
                }
            });
        });
        
    });
    
    //listen event reset project filter
    $scope.$on('reset_project_filter', function () {
        $scope.filterMMStart = 0;
        $scope.filterMMEnd = infinitySym;
        $('#slider_mm').slider("values", [0, MAX_MM]);
    });
    
});

rkTagApp.controller('employeeController', function ($scope, $http, $timeout, $compile) {
    $scope.employeesList = [];
    $scope.filterEmployee = {
        order: 'proj.created_at',
        dir: 'desc',
        page: 1,
        search: {}
    };
    $scope.pager = {
        current_page: 1,
        next_page: null,
        last_page: 0,
        limit: 50,
        total: 0
    };
    $scope.employeeBusyRate = {};
    $scope.projIdsFilter = '';
    
    /**
     * listen event change project filter
     * 
     * @param {type} filters
     * @param {type} isReset
     * @return {Boolean|undefined}
     */
    $scope.$on("EventChangeProjFilter", function(evt, data){
        $scope.projIdsFilter = data;
    });
    
    //get list tags
    $scope.getList = function (filters, isReset) {
        if (!angular.isDefined(isReset)) {
            isReset = false;
        }
        if (!$scope.dataLoaded) {
            return;
        }
        filters.project_ids = '';
        if ($scope.projIdsFilter) {
            filters.project_ids = $scope.projIdsFilter;
        } else {
            $scope.employeesList = null;
            $scope.pager = {
                current_page: 1,
                next_page: 0,
                last_page: 0,
                limit: 0,
                total: 0,
                page: 0
            };
            return true;
        }
        RKTagFunction.progressBar.start();
        $scope.dataLoaded = false;
        initBsMultiSelect();
        $http.get($scope.globTag.urlGetEmployeeList, {
            params: filters
        }).then(function (result) {
            $scope.employeeIdsGetBusyRate = [];
            var data = result.data;
            $scope.employeesList = data.data;
            $scope.$emit('total_employees', data.total);
            $scope.pager = {
                current_page: data.current_page,
                next_page: (data.last_page > data.current_page) ? 
                    data.current_page + 1 : null,
                last_page: data.last_page || 0,
                limit: data.per_page + '',
                total: data.total || 0,
                page: data.current_page
            };
            $scope.filterEmployee.limit = data.per_page;
            $timeout(getEmployeeTagsInfo, 200);
            $scope.employeeLoaded = true;
            $scope.dataLoaded = true;
            RKTagFunction.progressBar.end();
            $scope.getEmployeeBusyRate($scope.employeesList, data.total);
        }).catch(function (error) {
            RKTagFunction.general.notifyError();
            $scope.dataLoaded = true;
            RKTagFunction.progressBar.end();
        });
    };
    
    /**
     * set employee ids to get data for busy rate
     * 
     * @param {int} id
     * @return {Boolean}
     */
    $scope.funcSetEmployeeBusyRate = function(id) {
        if ($scope.employeeBusyRate.hasOwnProperty(id)) {
            return true;
        }
        $scope.employeeIdsGetBusyRate.push(id);
    };
    
    /**
     * get employee busy rate data
     * 
     * @param {object} employeeList
     * @param {int} total
     * @return {Boolean}
     */
    $scope.getEmployeeBusyRate = function(employeeList, total) {
        if (!total) {
            return true;
        }
        angular.getTestability($('.tb-tag-emp-list')).whenStable(function() {
            if (!$scope.employeeIdsGetBusyRate.length) {
                return true;
            }
            $http({
                method: 'get',
                url: $scope.globTag.urlGetEmployeeBusyRate,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                params: {
                    employee: $scope.employeeIdsGetBusyRate.join('-')
                }
            }).then(function(response) {
                if (!response.data ||
                    !response.data.employee.length || 
                    !response.data.month
                ) {
                    return true;
                }
                $scope.employeeBusyRateRender(response.data.employee, response.data.month);
            }).catch();
        });
    };
    
    /*
     * date period for busy rate 3 month
     */
    $scope.datePeriod = {};
    $scope.numberPeriodBusyRate = 0;
    $scope.setDatePeriod = function(dateStart) {
        if (!angular.equals({}, $scope.datePeriod)) {
            return $scope.datePeriod;
        }
        var period = 7,
            dateEnd = dateStart.clone();
        dateEnd.month(dateStart.month() + 2).endOf('month');
        $scope.numberPeriodBusyRate = 0;
        while (1) {
            var format = dateStart.format('YYYY-MM-DD');
            $scope.datePeriod[format] = {
                dateFormat: format,
                color: 'white',
                effort: 0,
                dateStart: dateStart.clone(),
                dateEnd: dateStart.clone().day('Sunday').add(period, 'd')
            };
            $scope.datePeriod[format].daysWork = $scope.daysWork(
                    $scope.datePeriod[format].dateStart, $scope.datePeriod[format].dateEnd);
            $scope.numberPeriodBusyRate++;
            if (dateStart.isAfter(dateEnd)) {
                break;
            }
            dateStart.day('Monday');
            dateStart.add(period, 'd');
        }
        $scope.numberPeriodBusyRate = 100 / $scope.numberPeriodBusyRate;
        return $scope.datePeriod;
    };
    
    /**
     * render progress employee busy rate
     * 
     * @param {object} data
     * @return {undefined}
     */
    $scope.employeeBusyRateRender = function(data, startMonth) {
        var dateStartMonth = moment(startMonth),
            datePeriod = $scope.setDatePeriod(dateStartMonth),
            employeeEffort = {};
        angular.forEach(data, function(item) {
            if (typeof employeeEffort[item.employee_id] === 'undefined') {
                employeeEffort[item.employee_id] = [];
                $scope.employeeBusyRate[item.employee_id] = {};
                angular.copy(datePeriod, $scope.employeeBusyRate[item.employee_id]);
                $scope.employeeIdsGetBusyRate.splice(
                    $scope.employeeIdsGetBusyRate.indexOf(item.employee_id), 1
                );
            }
            employeeEffort[item.employee_id].push({
                start_at: moment(item.start_at),
                end_at: moment(item.end_at),
                effort: item.effort
            });
        });
        // exec employee not have project
        if ($scope.employeeIdsGetBusyRate.length) {
            angular.forEach($scope.employeeIdsGetBusyRate, function(item) {
                $scope.employeeBusyRate[item] = {};
                angular.copy(datePeriod, $scope.employeeBusyRate[item]);
            });
            $scope.employeeIdsGetBusyRate = [];
        }
        //exec employee effort busy date
        angular.forEach(employeeEffort, function(employeeEffortDB, employeeId) {
            // check each data employee effort
            angular.forEach(employeeEffortDB, function (employeeEffortWork) {
                // check each period effort
                angular.forEach($scope.employeeBusyRate[employeeId], function(busyPeriod) {
                    // not in period
                    if (busyPeriod.dateStart.isAfter(employeeEffortWork.end_at)
                    ) {
                        return false;
                    }
                    // increment effort
                    var effortAdd = 0, endTmp, daysWork = 0;
                    if (busyPeriod.dateStart.isSameOrBefore(employeeEffortWork.start_at) &&
                        employeeEffortWork.start_at.isSameOrBefore(busyPeriod.dateEnd)
                    ) {
                        if (employeeEffortWork.end_at.isBefore(busyPeriod.dateEnd)) {
                            endTmp = employeeEffortWork.end_at.clone();
                        } else {
                            endTmp = busyPeriod.dateEnd.clone();
                        }
                        daysWork = $scope.daysWork(employeeEffortWork.start_at, endTmp);
                        effortAdd = daysWork * employeeEffortWork.effort / busyPeriod.daysWork;
                    } else if (busyPeriod.dateStart.isSameOrBefore(employeeEffortWork.end_at) &&
                        employeeEffortWork.end_at.isSameOrBefore(busyPeriod.dateEnd)
                    ) {
                        daysWork = $scope.daysWork(busyPeriod.dateStart, 
                            employeeEffortWork.end_at);
                        effortAdd = daysWork * employeeEffortWork.effort / busyPeriod.daysWork;
                    } else if (busyPeriod.dateStart.isAfter(employeeEffortWork.start_at) &&
                        busyPeriod.dateEnd.isBefore(employeeEffortWork.end_at)
                    ) {
                        effortAdd = parseFloat(employeeEffortWork.effort);
                    } else {
                        //nothing
                    }
                    if (effortAdd) {
                        effortAdd = parseFloat(effortAdd.toFixed(2));
                        busyPeriod.effort += effortAdd;
                        busyPeriod.color = $scope.busyRateColor(busyPeriod.effort);
                    }
                });
            });
        });
    };
    
    /**
     * get color follow effort
     * 
     * @param {int} effort
     * @return {String}
     */
    $scope.busyRateColor = function(effort) {
        if (effort > 100) {
            return 'red';
        }
        if (effort > 80) {
            return 'green';
        }
        if (effort > 0) {
            return 'yellow';
        }
        return 'white';
    };
    
    /**
     * cal works day
     * 
     * @param {date} start
     * @param {date} end
     * @return {Number}
     */
    $scope.daysWork = function(start, end) {
        var result = 0,
            startClone = start.clone();
        while(1) {
            if (startClone.isAfter(end)) {
                break;
            }
            var formatFull = startClone.format('YYYY-MM-DD'),
                formatMD = startClone.format('MM-DD'),
                formatDay = startClone.format('ddd');
            if ($scope.globTag.holiday.special.indexOf(formatFull) > -1||
                $scope.globTag.holiday.annual.indexOf(formatMD) > -1 ||
                $scope.globTag.holiday.weekend.indexOf(formatDay) > -1
            ) {
                // holiday => not action
            } else {
                result++;
            }
            startClone.add(1, 'd');
        }
        return result;
    };
    
    function getEmployeeTagsInfo() {
        var listTagIds = [];
        var listEmpTags = {};
        angular.element('.table-employee .td-tags').each(function () {
            var empId = $(this).closest('tr').data('id');
            var tagIds = $(this).data('tagids');
            if (tagIds) {
                var arrIds = $scope.toArrayFromStr(tagIds + '', '-');
                if (arrIds.length > 0) {
                    for (var i = 0; i < arrIds.length; i++) {
                        if (listTagIds.indexOf(arrIds[i]) < 0) {
                            listTagIds.push(arrIds[i]);
                        }
                    }
                    listEmpTags[empId] = arrIds;
                }
            }
        });
        if (listTagIds.length < 1) {
            return;
        }
        
        //get data on local
        var listAllFields = $scope.getStorageItem($scope.KEY_STORAGE_FIELD);
        var listTagOfFields = $scope.localTagsOfField || null;
        if (listAllFields && listTagOfFields) {
            //check if null tagsInfo then get from local
            if (angular.isDefined($scope.tagsInfo) && $scope.tagsInfo) {
                listAllFields = JSON.parse(listAllFields);
                angular.forEach(listAllFields, function (field) {
                    if (angular.isDefined(listTagOfFields[field.id])) {
                        angular.forEach(listTagOfFields[field.id], function (tag) {
                            tag.color = field.color;
                            $scope.tagsInfo[tag.id] = tag;
                        });
                    }
                });
            }
            
            renderTagEmpInfo(listEmpTags, $scope.tagsInfo);
            //resize hidden tags
            $timeout($scope.resizeHiddenTags, 200);
            return;
        }
        
        //if none storage local then get data from remote
        $http.get($scope.globTag.urlGetTagsInfo, {
            params: {'tag_str': listTagIds.join('-')}
        }).then(function (result) {
            $scope.tagsInfo = {};
            if (result.data) {
                angular.forEach(result.data, function (item, key) {
                    $scope.tagsInfo[item.id] = item;
                });
            }
            renderTagEmpInfo(listEmpTags, $scope.tagsInfo);
            //resize hidden tags
            $timeout($scope.resizeHiddenTags, 200);
        });
    }
    //render tag name, color
    function renderTagEmpInfo(listEmpTags, tagsInfo) {
        if ($scope.employeesList) {
            angular.forEach($scope.employeesList, function (item) {
                if (angular.isDefined(listEmpTags[item.id])) {
                    var tIds = listEmpTags[item.id];
                    var listTagArr = [];
                    for (var i in tIds) {
                        var tag = tagsInfo[tIds[i]];
                        listTagArr.push(tag);
                    }
                    item.tag_names = listTagArr;
                }
            });
        }
    }
    
    $scope.employeeLoaded = false;
    $scope.$on('load_employees', function () {
        if ($scope.employeeLoaded) {
            return;
        }
        $scope.getList($scope.filterEmployee);
    });
    
    //sorting
    $scope.doSort = function (orderby) {
        if (!$scope.dataLoaded) {
            return;
        }
        if ($scope.filterEmployee.order == orderby) {
            var dir = $scope.filterEmployee.dir == 'asc' ? 'desc' : 'asc';
            $scope.filterEmployee.dir = dir;
        }
        $scope.filterEmployee.order = orderby;
        $scope.getList($scope.filterEmployee);
    };
    //paginate go page number
    $scope.goPage = function (page) {
        if (!$scope.dataLoaded || !page) {
            return false;
        }
        if (!isFinite(page)) {
            page = 1;
        };
        if (page > $scope.pager.last_page) {
            page = $scope.pager.last_page;
            $scope.pager.current_page = page;
        }
        if (page < 1) {
            page = 1;
            $scope.pager.current_page = 1;
        }
        if ((page == 1 && $scope.pager.page == 1) 
                || (page == $scope.pager.last_page && $scope.pager.page == $scope.pager.last_page)) {
            return false;
        }
        
        $scope.filterEmployee.page = page;
        $scope.getList($scope.filterEmployee);
    };
    //change per page
    $scope.changePerPage = function () {
        if (!$scope.dataLoaded) {
            return;
        }
        $scope.pager.page = 1;
        $scope.filterEmployee.limit = $scope.pager.limit;
        $scope.filterEmployee.page = 1;
        $scope.getList($scope.filterEmployee);
    };
    //search data
    $scope.searchData = function (type, $event) {
        if (!$scope.dataLoaded) {
            return;
        }
        $scope.filterEmployee.page = 1;
        switch (type) {
            case 1://input
                if ($event.keyCode == 13) {
                    $scope.dataSearched = false;
                    $scope.getList($scope.filterEmployee);
                }
                break;
            default:
                $scope.dataSearched = false;
                $scope.getList($scope.filterEmployee);
                break;
        }
    };
    
    /*
     * sorting class extend rootScope
     */
    $scope.classSorting = function (orderby) {
        if ($scope.filterEmployee.order == orderby) {
            if ($scope.filterEmployee.dir == 'asc') {
                return 'sorting_asc';
            }
            return 'sorting_desc';
        }
        return '';
    };
    
    /*
     * catch event change project filter
     */
    $scope.$on('project_list_change', function () {
        $timeout(resetEmployeesList, 300);
    });
    
    function resetEmployeesList() {
        $scope.filterEmployee = {
            order: 'proj.created_at',
            dir: 'desc',
            page: 1,
            search: {}
        };
        $scope.pager = {
            current_page: 1,
            next_page: null,
            last_page: 0,
            limit: 50,
            total: 0
        };
        $scope.employeeLoaded = false;
        $timeout(function () {
            $('.nav-tabs-search .nav-tabs li:first a').click();
        }, 300);
        $scope.getList($scope.filterEmployee);
    };
    
    function initBsMultiSelect() {
        $timeout(function () {
            var teamSelectChange = false;
            angular.element('.emp-filter-teams select').multiselect({
                includeSelectAllOption: false,
                nonSelectedText: 'Choose items',
                allSelectedText: 'All',
                nSelectedText: 'items selected',
                numberDisplayed: 2,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                onDropdownShow: function () {
                    teamSelectChange = false;
                },
                onChange: function (optionChange) {
                    RKfuncion.bootstapMultiSelect._removeSpace(optionChange.closest('select'), 2);
                    teamSelectChange = true;
                },
                onDropdownHidden: function(event) {
                    if (!teamSelectChange) {
                        return;
                    }
                    $scope.searchData(2);
                }
            });

            var inputGroupBtn = angular.element('.emp-filter-teams .multiselect-container li.filter .input-group-btn');
            if (!inputGroupBtn.hasClass('apply-search')) {
                var btnApply = $compile('<button class="btn btn-default apply-search" ng-click="searchData(2)">Apply</button>')($scope);
                inputGroupBtn.addClass('apply-search').append(btnApply);
            }
        }, 200);
    };
    
});

})(angular, jQuery, RKTagFunction, RKfuncion);
