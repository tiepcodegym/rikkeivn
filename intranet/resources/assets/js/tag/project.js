(function(angular, $, RKTagFunction, RKTagLDB, RKfuncion){
rkTagApp.controller('projectController', function(
    $scope, $http, $sce, $templateRequest, $compile, $timeout, ModalService, 
    $window, $location
) {
    var requestDataProject = false,
    dataProcess = false,
    __ctrl = this,
    projectItemDefault,
    projectDataRelateDefault;
    $scope.trans = RKTagTrans;
    $scope.varGlobalTag = RKVarGlobalTag;
    $scope.projectData = {
        pm: {},
        type: [],
        teamLeaderOption: {},
        teamLeaderLength: 0,
        memberTypes: {}
    };
    $scope.oldProjectData = {};
    $scope.projectItem = {};
    $scope.projectDataRelate = {}; // get default
    $scope.projectItemRelate = {}; // process data to show
    projectDataRelateDefault = {
        member: {},
        scope: {}
    };
    projectItemDefault = {
        base: {
            cust_contact_id: null,
            manager_id: null,
            type: 0,
            type_mm: 2
        },
        sale: {
            ids: []
        },
        team: {
            ids: []
        },
        lang: {
            ids: []
        },
        quality: {}
    };
    $scope.projectData.resourceType = RKTagFunction.general.jsonToArray(
        RKVarGlobalTag.projectReourceType);
    $scope.varGlobalTag.projectStateKey = Object
        .keys($scope.varGlobalTag.projectState).map(Number);
    $scope.projectIdActive = null;
    /**
     * click event show popup add/edit project
     * 
     * @param {object} $event
     */
    $scope.formProjectOldEdit = function($event, item) {
        var projectId;
        if (typeof item == 'undefined') {
            projectId = null;
            item = {loadingModal: false};
        } else {
            projectId = item.id;
        }
        //init project tags item;
        $scope.editItem = item;
        $scope.editProject = item;
        $scope.projAssignee = item.assignee_id;
        $scope.listAssignees = [{id: item.assignee_id, text: $scope.convertAccount(item.assignee_name)}];
        $scope.projectIdActive = projectId;
        // get normal data of project
        if (!requestDataProject) {
            if (dataProcess) {
                return true;
            }
            dataProcess = true;
            item.loadingModal = true;
            $http({
                method: 'get',
                url: $scope.varGlobalTag.urlGetProjectDataNormal,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function (response) {
                $scope.projectData.pm = Object.assign($scope.projectData.pm, response.data.pm);
                $scope.projectData.type = $scope.projectData.type.concat(
                        RKTagFunction.general.jsonToArray(response.data.project_type));
                $scope.projectData.teamLeader = RKTagFunction.general.json2ToInt(response.data.team_leader);
                $scope.projectData.team = RKfuncion.teamTree.init(
                    response.data.team
                );
                $scope.projectData.lang = response.data.lang;
                $scope.projectData.memberTypes = response.data.member_types;
                $scope.projectData.memberTypeAvaiLang = response.data.member_type_avai_lang;
                $scope._processGetProjectData(projectId, item);
                $scope.scopeProjOldEdit = response.data.scope_proj_old_edit;
                $scope.myTeam = response.data.my_team ? response.data.my_team : [];
                dataProcess = false;
                requestDataProject = true;
                item.loadingModal = false;
            }, function (response) {
                RKTagFunction.general.notifyError(response.data.message);
                dataProcess = false;
                item.loadingModal = false;
            });
            return true;
        }
        $scope._processGetProjectData(projectId, item);
    };
    
    /**
     * get project data
     */
    $scope._processGetProjectData = function(projectId, item) {
        $scope.project = {};
        if (typeof projectId === 'undefined' || !projectId) {
            $scope.funcResetModalProject('create');
            $scope._processRenderFormProject();
            return true;
        }
        item.loadingModal = true;
        $http({
            method: 'get',
            url: $scope.varGlobalTag.urlProjectGetDataItem,
            params: {
                id: projectId
            },
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        }).then(function (response) {
            if (!response.data.success) {
                RKTagFunction.general.notifyError(response.data.message);
                return true;
            }
            $scope.funcResetModalProject('edit', response.data.project);
            $scope.oldProjectData = response.data.project;
            $scope.project.sales = response.data.sales;
            $scope.project.customer_name = response.data.customer_name;
            // set leader name and manager name
            if (response.data.leader_pm && response.data.leader_pm.length) {
                var iLP, itemLP, flagLeader = false, flagPm = false;
                for (iLP in response.data.leader_pm) {
                    itemLP = response.data.leader_pm[iLP];
                    if (itemLP.id == $scope.projectItem.base.leader_id && !flagLeader) {
                        $scope.project.leader_name = 
                            $scope.funcEmailNameConcat(itemLP.email, itemLP.name);
                        flagLeader = true;
                    }
                    if (itemLP.id == $scope.projectItem.base.manager_id &&
                        !flagPm) {
                        $scope.project.manager_name = 
                                $scope.funcEmailNameConcat(itemLP.email, itemLP.name);
                        flagPm = true;
                    }
                    if (flagLeader && flagPm) {
                        break;
                    }
                }
            }
            $scope.project.sale_ids = $scope.projectItem.sale.ids;
            $scope._processRenderFormProject();
            item.loadingModal = false;
        }, function (response) {
            RKTagFunction.general.notifyError();
            dataProcess = false;
            item.loadingModal = false;
        });
    };
    
    /**
     * call show form edit project
     * 
     * @returns {undefined}
     */
    $scope._processRenderFormProject = function() {
        $scope.funcSetTitleModalProject();
        $scope.funcCheckProjStatusOld();
        $scope.isProjEditable();
        var templateUrlBasic = $sce.getTrustedResourceUrl(
            RKTagFunction.general.getTemplate('project/basic-info.html')
        );
        //load template project tab
        $templateRequest(templateUrlBasic).then(function(template) {
            $scope.htmlProjectBasicInfo = $sce.trustAsHtml(template);
        });
        
        var templateUrlForm = $sce.getTrustedResourceUrl(
            RKTagFunction.general.getTemplate('project-edit.html')
        );
        $templateRequest(templateUrlForm).then(function(template) {
            $scope.htmlProjectEditorModal = $sce.trustAsHtml(template);
            setTimeout(function() {
                $scope._showFormProject();
            });
        });
    };
    
    $scope.isChangeProjectTag = false;
    /**
     * show popup form project old
     */
    $scope._showFormProject = function() {
        if (!RKVarGlobalTag.IS_SEARCH) {
            $scope.funcSetValidateRule();
        }
        $scope.select2Init();
        $('#modal-project-old-edit').modal({
            backdrop: 'static',
            keyboard: false
        }).modal('show');
        // active tab tag
        $('a[href="#project-tags"]').trigger('click');
        // remove tab content load by ajax
        $('#modal-project-old-edit .tab-content .tab-pane .tab-content-loaded')
            .remove();
        $('#modal-project-old-edit').on('shown.bs.modal', function() {
            var changeOption = false;
            
            RKfuncion.bootstapMultiSelect.init({
                onChange: function(optionChange) {
                    RKfuncion
                        .bootstapMultiSelect
                        ._removeSpace(optionChange.closest('select'));
                    changeOption = true;
                },
                onDropdownHidden: function(event) {
                    if (!changeOption) {
                        return true;
                    }
                    var dom = $(event.currentTarget).siblings('select:first');
                    event.currentTarget = dom[0];
                    $scope.inputProjectSubmit(
                        event, 
                        $scope.projectItem[dom.data('col-group')][dom.data('col-name')], 
                        dom.data('col-group'), 
                        dom.data('col-name')
                    );
                    changeOption = false;
                    return true;
                }
            });
        });
        
        //event close modal edit reload list
        $('#modal-project-old-edit').on('hidden.bs.modal', function () {
            if ($('.modal-backdrop').length) {
                $('.modal-backdrop').remove();
            }
            if (!$scope.globTag.IS_SEARCH && $scope.isChangeProjectTag) {
                $scope.isChangeProjectTag = false;
                $scope.exportStorageTags({
                    then: function() {
                        $scope.getList($scope.dataFilter);
                    }
                });
            }
        });
    };
    
    /**
     * 
     * @param {type} objectget length of object
     */
    $scope.funcLengObj = function(object) {
        if (!object) {
            return 0;
        }
        return Object.keys(object).length;
    };
    
    RKTagFunction.validate.addGreaterDate();
    RKTagFunction.validate.addGreaterEqualDate();
    RKTagFunction.validate.addLessEqualDate();
    
    $scope.$watch('projectItem', function() {
        if (!$scope.isProjFormSubmitted) {
            return true;
        }
        $('#create-project-form').valid();
    }, true);
    /**
     * submit form create project
     */
    $scope.formProjectSubmit = function($event) {
        $event.preventDefault();
        var dom = angular.element($event.currentTarget),
            iconAjaxLoading = dom.find('[type="submit"] .ajax-loading');
        $scope.isProjFormSubmitted = true;
        if (!dom.valid()) {
            return true;
        }
        if (dataProcess) {
            return true;
        }
        $scope.disabledFormProject = true;
        iconAjaxLoading.removeClass('hidden');
        $http({
            method  : 'post',
            url     : dom.attr('action'),
            data    : $.param($scope.projectItem),
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).then(function (response) {
            if (!response.data.success) {
                RKTagFunction.general.notifyError(response.data.message);
                dataProcess = false;
                iconAjaxLoading.addClass('hidden');
                $scope.disabledFormProject = false;
                return true;
            }
            RKTagFunction.general.notifySuccess(response.data.message);
            $scope.projectItem.id = parseInt(response.data.id);
            $scope.projectItem.base.status = response.data.project.status;
            $scope.funcSetTitleModalProject();
            dataProcess = false;
            iconAjaxLoading.addClass('hidden');
            $scope.disabledFormProject = false;
            angular.copy($scope.projectItem, $scope.oldProjectData);
            $scope.isChangeProjectTag = true;
            $scope.editItem = response.data.project;
            $scope.isProjEditable();
            // active tab tag
            setTimeout(function() {
                $('#modal-project-old-edit .tab-content .tab-pane .tab-content-loaded')
                    .remove();
                $('a[href="#scope"]').trigger('click');
            });
        },function (response) {
            RKTagFunction.general.notifyError();
            dataProcess = false;
            iconAjaxLoading.addClass('hidden');
            $scope.disabledFormProject = false;
        });
    };
    
    /**
     * submit form input single project
     * 
     * @param {object} $event
     * @param {string} value
     * @param {string} colGroup column group: base, sale, team, lang, quality
     * @param {type} colName
     * @returns {Boolean}
     */
    $scope.inputProjectSubmit = function($event, value, colGroup, colName, flagValid) {
        if (!$scope.projectItem.id) {
            return true;
        }
        if (typeof $event === 'object' && typeof $event.preventDefault === 'function') {
            $event.preventDefault();
        }  else if (typeof $event === 'object' && $event.currentTarget) {
            // nothing
        } else if (typeof $event === 'string'){
            $event = {
                currentTarget: $($event)[0]
            };
        }
        var dom = angular.element($event.currentTarget),
            form = dom.closest('form#create-project-form'),
            inputAjaxLoading = dom.siblings('.input-ajax-loading'),
            param = {};
        if (form.length && (flagValid === 'undefined' || !flagValid)) {
            $scope.funcSetValidateRule(dom.attr('name'));
            setTimeout(function() {
                if (form.valid()) {
                    $scope.inputProjectSubmit($event, value, colGroup, colName, true);
                }
            }, 100);
            return true;
        }
        param = {
            id: $scope.projectItem.id,
            colGroup: colGroup,
            colName: colName,
            value: value
        };
        if (RKTagFunction.general.notChangeValueObject(
                $scope.oldProjectData, param)
        ) {
            return true;
        }
        if (colGroup === 'team') {
            param.relate = {
                'leader_id': $scope.projectItem.base.leader_id
            };
        }
        inputAjaxLoading.removeClass('hidden');
        $http({
            method  : 'post',
            url     : $scope.varGlobalTag.urlProjectUpdateInput,
            data    : $.param(param),
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).then(function (response) {
            if (response.data.reload) {
                window.location.reload();
                return true;
            }
            if (!response.data.success) {
                RKTagFunction.general.notifyError(response.data.message);
                inputAjaxLoading.addClass('hidden');
                return true;
            }
            RKTagFunction.general.notifySuccess(response.data.message);
            $scope.funcSetTitleModalProject();
            inputAjaxLoading.addClass('hidden');
            if (typeof $scope.oldProjectData[colGroup] === 'undefined') {
                $scope.oldProjectData[colGroup] = {};
            }
            $scope.oldProjectData[colGroup][colName] = value;
            
            // process after save lang ids: remove edit member
            if (colGroup === 'lang') {
                form.closest('.tab-content')
                    .find('#team-allocation .btn-delete.type-cancel')
                    .trigger('click');
                return true;
            }
            if (colGroup === 'base' && colName === 'type_mm') {
                $('#team-allocation').children()
                    .children('.tab-content-loaded').remove();
                return true;
            }
            if (colGroup === 'scope' || (
                colGroup === 'base' && ['start_at', 'end_at'].indexOf(colName) > -1)
            ) {
                if (colGroup === 'scope') {
                    if (typeof $scope.tooltipProjTagData[$scope.projectItem.id] === 'undefined') {
                        $scope.tooltipProjTagData[$scope.projectItem.id] = {};
                    }
                    $scope.tooltipProjTagData[$scope.projectItem.id].scope_desc
                        = $scope.projectDataRelate.scope.scope_desc;
                    $scope.tooltipProjTagData[$scope.projectItem.id].scope_scope
                        = $scope.projectDataRelate.scope.scope_scope;
                }
                $scope.renderProjTooltip({
                    project_id: $scope.projectItem.id,
                    start_date: $scope.projectItem.base.start_at,
                    end_date: $scope.projectItem.base.end_at,
                    scope_desc: $scope.tooltipProjTagData[$scope.projectItem.id].scope_desc,
                    scope_scope: $scope.tooltipProjTagData[$scope.projectItem.id].scope_scope
                });
                $scope.funcQtipItem(null, null, $scope.projectItem.id);
            }
        },function (response) {
            RKTagFunction.general.notifyError();
            inputAjaxLoading.addClass('hidden');
        });
    };
        
    /**
     * option select 2
     */
    $scope.select2OptionsNotSearch = RKTagFunction.select2.notSearch();
    $scope.select2OptionsRemote = function(url) {
        return RKTagFunction.select2.remote(url);
    };
    $scope.select2Init = function() {
        RKfuncion.select2.init({
            minimumInputLength: 2
        });
    };
    
    /**
     * even change select2 ajax of customer
     */
    $(document).on('change', 'select[name="cust_contact_id"]', function (evt) {
        var value;
        try {
            value = parseInt($(this).val());
        } catch (e) {
            value = null;
        }
        $scope.projectItem.base.cust_contact_id = value;
        $scope.inputProjectSubmit(evt, 
            value, 'base', 'cust_contact_id');
    });
    
    /**
     * set value select of sales
     */
    $(document).on('change', 'select[name="sale_ids[]"]', function (evt) {
        var value;
        try {
            value = $(this).val().map(Number);
        } catch (e) {
            value = [];
        }
        $scope.projectItem.sale.ids = value;
        $scope.inputProjectSubmit(evt, 
            value, 'sale', 'ids');
    });
    
    /**
     * change select 2 of pm
     */
    $scope.funcSelect2PMChanged = function(event, value) {
        var dom = $(event.currentTarget),
            colGroup = dom.attr('data-col-group'),
            colName = dom.attr('data-col-name');
        if (!colGroup || !colName) {
            return true;
        }
        $scope.inputProjectSubmit(event, value, colGroup, colName);
    };
    
    /**
     * set title of modal project - create or name project
     */
    $scope.funcSetTitleModalProject = function() {
        if ($scope.projectItem.id) {
            $scope.titleModalProject = $scope.projectItem.base.name;
        } else {
            $scope.titleModalProject = $scope.trans['Add old project'];
        }
    };
    
    /**
     * get proj link wo
     * 
     * @returns {string}
     */
    $scope.funcProjWoDetailLink = function() {
        if (!$scope.projectItem.id) {
            return '#';
        }
        var link = $scope.varGlobalTag.urlProjWoDetail;
        return link.replace(/0$/, $scope.projectItem.id);
    };
    
    /**
     * reset choose data in modal project
     */
    $scope.funcResetModalProject = function(typePopup, dataProject) {
        // create project and before event is create => nothing
        if (typePopup === 'create') {
            angular.copy(projectItemDefault, $scope.projectItem);
            angular.copy(projectDataRelateDefault, $scope.projectDataRelate);
            angular.copy(projectDataRelateDefault, $scope.projectItemRelate);
        } else if (typePopup === 'edit') {
            angular.copy(dataProject, $scope.projectItem);
            angular.copy(projectDataRelateDefault, $scope.projectDataRelate);
            angular.copy(projectDataRelateDefault, $scope.projectItemRelate);
            if ($scope.projectItem.team.ids) {
                $scope.projectItem.team.ids = $scope.projectItem.team.ids.map(Number);
            } else {
                $scope.projectItem.team.ids = [];
            }
            if ($scope.projectItem.lang.ids) {
                $scope.projectItem.lang.ids = $scope.projectItem.lang.ids.map(Number);
            } else {
                $scope.projectItem.lang.ids = [];
            }
        }
    };
    
    /**
     * load tab content project
     * 
     * @param {type} $event
     * @returns {Boolean}
     */
    $scope.funcProjLoadTabContent = function($event) {
        //pending load team
        var linkClick = $($event.currentTarget),
            flagId = linkClick.attr('href'),
            dom = $(flagId).children('[ng-bind-html-compile]');
        if (flagId == '#project-tags') {
            //init project field tags
            $scope.initProjectFields($scope.projectItem.id);
        }
        if (!dom.length) {
            return true;
        }
        var url = dom.data('url'),
            template = dom.data('template'),
            bind = dom.attr('ng-bind-html-compile');
        if (!url || 
            !template || 
            !bind || 
            dom.children('.tab-content-loaded').length
        ) {
            return true;
        }
        $scope[bind] = '<i class="fa fa-spin fa-refresh"></i>';
        $http({
            method: 'get',
            url: url,
            params: {
                id: $scope.projectItem.id
            },
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        }).then(function (response) {
            if (!response.data.success) {
                RKTagFunction.general.notifyError(response.data.message);
                return true;
            }
            angular.forEach(response.data.relate, function(item, key) {
                if (typeof item === 'object') {
                    if (!Array.isArray(item)) {
                        $scope.oldProjectData[key] = {};
                        $scope.projectDataRelate[key] = {};
                    } else {
                        $scope.oldProjectData[key] = [];
                        $scope.projectDataRelate[key] = [];
                    }
                    angular.copy(item, $scope.projectDataRelate[key]);
                    angular.copy(item, $scope.oldProjectData[key]);
                }
            });
            //angular.copy(response.data.relate, $scope.projectDataRelate);
            var templateUrl = $sce.getTrustedResourceUrl(
                RKTagFunction.general.getTemplate(template)
            );
            //load template team tab
            $templateRequest(templateUrl).then(function(template) {
                $scope[bind] = $sce.trustAsHtml(template);
            });
            $scope.disabledProjMemberBtn = false;
        }, function (response) {
            RKTagFunction.general.notifyError();
        });
    };
    
    /**
     * set rules of validate form
     */
    $scope.funcSetValidateRule = function(names) {
        $scope.isProjFormSubmitted = false;
        if (!$scope.projRulesDefault) {
            $scope.projRulesDefault = {
                messages: {
                    name: {
                        remote: 'This field is unique'
                    },
                    project_code: {
                        remote: 'This field is unique'
                    },
                    end_at: {
                        greaterDate: "Must be greater than start date"
                    }
                },
                ignore: '',
                errorPlacement: function(error, element) {
                    if (element.hasClass('bootstrap-multiselect')) {
                        // custom placement for hidden select
                        error.insertAfter(element.next('.btn-group'));
                        
                    } else if (element.hasClass('igs-input')) {
                        element.closest('.input-group-select2')
                            .after(error);
                    } else {
                        // message placement for everything else
                        error.insertAfter(element);
                    }
                }
            };
        }
        $scope.projrulesAvai = {
            'team_ids[]': {
                required: true
            },
            'lang_ids[]': {
                required: true
            },
            leader_id: {
                required: true
            },
            manager_id: {
                required: true
            },
            type: {
                required: true
            },
            billable_effort: {
                required: true,
                number: true,
                min: 0
            },
            plan_effort: {
                required: true,
                number: true,
                min: 0
            },
            start_at: {
                required: true,
                date: true
            },
            end_at: {
                required: true,
                date: true,
                greaterDate: "#project_start_at"
            },
            name: {
                required: true,
                maxlength: 255,
                remote: {
                    url: $scope.varGlobalTag.urlProjectCheckExists,
                    type: 'get',
                    data: {
                        col: 'name',
                        value: function() {
                            return $scope.projectItem.base.name;
                        },
                        id: $scope.projectItem.id
                    }
                }
            },
            project_code: {
                required: true,
                maxlength: 255,
                remote: {
                    url: $scope.varGlobalTag.urlProjectCheckExists,
                    type: 'get',
                    data: {
                        col: 'project_code',
                        value: function() {
                            return $scope.projectItem.base.project_code;
                        },
                        id: $scope.projectItem.id
                    }
                }
            }
        };
        if (typeof names === 'undefined') {
            $scope.formProjectRules = $scope.projrulesAvai;
        } else if (typeof $scope.projrulesAvai[names] === 'undefined') {
            $scope.formProjectRules = {};
        } else {
            $scope.formProjectRules = {};
            $scope.formProjectRules[names] = $scope.projrulesAvai[names];
            if (names === 'team_ids[]') {
                $scope.formProjectRules['leader_id'] = 
                    $scope.projrulesAvai['leader_id'];
            }
        }
        $scope.validationProjForm = $('form#create-project-form')
            .validate($scope.projRulesDefault);
        if ($scope.validationProjForm) {
            $scope.validationProjForm.settings.rules = $scope.formProjectRules;
        }
    };
    
    /**
     * init member data of a project
     */
    $scope.funcSetProjMember = function(itemTmp, key) {
        var item = {};
        angular.copy(itemTmp, item);
        item.account = item.email.replace(/@.*$/,'');
        item.account = item.account[0].toUpperCase() + item.account.slice(1);
        item.start_at = item.start_at.replace(/\s.*$/,'');
        item.end_at = item.end_at.replace(/\s.*$/,'');
        item.typeText = $scope.projectData.memberTypes[item.type];
        item.key = parseInt(key) + 1;
        if (typeof item.lang_ids !== 'undefined') {
        } else if (item.prog_lang) {
            item.lang_ids = (''+item.prog_lang).split(',').map(Number);
            var index, result = '';
            for (index in item.lang_ids) {
                if (typeof $scope.projectData.lang[item.lang_ids[index]] !== 'undefined') {
                    result += $scope.projectData.lang[item.lang_ids[index]] + ', ';
                }
            }
            item.prog_lang = result.slice(0, -2);
        }
        item.type = parseInt(item.type);
        item.employee_id = parseInt(item.employee_id);
        $scope.projectItemRelate.member[item.id] = {};
        angular.copy(item, $scope.projectItemRelate.member[item.id]);
        return $scope.projectItemRelate.member[item.id];
    };
    
    /**
     * btn click add form add member project
     */
    $scope.funcProjMemberAdd = function($event) {
        var domBtn = angular.element($event.currentTarget),
            formProj = domBtn.closest('#form-proj-member'),
            trDom = domBtn.closest('tr[data-member-id]'),
            memberId, isFlagEdit;
        if (formProj.find('input, select').length) {
            return true;
        }
        if (!trDom.length) {
            memberId = 0;
        } else {
            memberId = parseInt(trDom.attr('data-member-id'));
        }
        $scope.disabledProjMemberBtn = true;
        if (typeof memberId === 'undefined' || !memberId || 
            typeof $scope.projectItemRelate.member[memberId] === 'undefined'
        ) {
            $scope.projMember = {
                type: 1,
                employee_id: null,
                prog_langs: [],
                id: null
            };
            $scope.projMemberData = {
                account: null
            };
            isFlagEdit = false;
        } else {
            $scope.projMember = {
                id: $scope.projectItemRelate.member[memberId].id,
                type: $scope.projectItemRelate.member[memberId].type,
                employee_id: $scope.projectItemRelate.member[memberId].employee_id,
                prog_langs: $scope.projectItemRelate.member[memberId].lang_ids,
                start_at: $scope.projectItemRelate.member[memberId].start_at,
                end_at: $scope.projectItemRelate.member[memberId].end_at,
                effort: $scope.projectItemRelate.member[memberId].effort
            };
            $scope.projMemberData = {
                key: $scope.projectItemRelate.member[memberId].key,
                account: $scope.projectItemRelate.member[memberId].account,
                flat_resource: $scope.projectItemRelate.member[memberId].flat_resource
            };
            isFlagEdit = true;
        }
        var templateUrlTeam = $sce.getTrustedResourceUrl(
            RKTagFunction.general.getTemplate('project/member-edit.html')
        );
        //load template team tab
        $templateRequest(templateUrlTeam).then(function(template) {
            var trEditFrom;
            if (!isFlagEdit) { // show form create
                var bodyTableMember = $('#team-allocation .table-proj-members tbody');
                bodyTableMember.append(template);
                trEditFrom = bodyTableMember.children('tr:last');
            } else { // show form edit
                var trMemberView = domBtn.closest('tr[data-member-id]');
                trMemberView.after(template);
                trEditFrom = trMemberView.next();
                trMemberView.remove();
            }
            trEditFrom.attr('data-member-id', memberId);
            $compile(trEditFrom.contents())($scope);
            setTimeout(function() {
                RKfuncion.bootstapMultiSelect.init();
                trEditFrom.find('button').prop('disabled', false);
            });
        });
    };
    
    /**
     * event btc click remove project member
     * 
     * @param {object} $event
     * @param {int} id
     */
    $scope.funcProjMemberCancel = function($event) {
        var domBtn = angular.element($event.currentTarget),
            rowTable = domBtn.closest('tr[data-member-id]'),
            memberId = parseInt(rowTable.attr('data-member-id'));
        if (typeof memberId === 'undefined' || !memberId) { // cancel exists member 
            rowTable.remove();
        } else {
            $scope.funcProjMemberView(rowTable, memberId);
        }
        $scope.disabledProjMemberBtn = false;
    };
    
    /**
     * event btc click remove project member
     * 
     * @param {object} $event
     * @param {int} id
     */
    $scope.funcProjMemberRemove = function($event) {
        var domBtn = angular.element($event.currentTarget),
            rowTable = domBtn.closest('tr[data-member-id]'),
            iconAjaxLoading = domBtn.find('.ajax-loading'),
            iconAjaxMain = domBtn.find('.ajax-main-content'),
            memberId = parseInt(rowTable.attr('data-member-id'));
        if (typeof memberId === 'undefined' && !memberId) {
            return true;
        }
        RKTagFunction.general.popupConfirmDanger({
            title: $scope.trans['EConfirm'],
            ok: function() {
                iconAjaxMain.addClass('hidden');
                iconAjaxLoading.removeClass('hidden');
                $scope.disabledProjMemberBtn = true;
                $http({
                    method: 'delete',
                    url: $scope.varGlobalTag.urlProjMemberDelete,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    params: {
                        id: memberId,
                        project_id: $scope.projectItem.id
                    }
                }).then(function (response) {
                    if (!response.data.success) {
                        $.notify({
                            message: response.data.message
                        },{
                            type: 'warning'
                        });
                        iconAjaxMain.removeClass('hidden');
                        iconAjaxLoading.addClass('hidden');
                        $scope.disabledProjMemberBtn = false;
                        return true;
                    }
                    RKTagFunction.general.notifySuccess(response.data.message);
                    iconAjaxMain.removeClass('hidden');
                    iconAjaxLoading.addClass('hidden');
                    $scope.disabledProjMemberBtn = false;
                    rowTable.remove();
                    $scope.funcAfterRemoveMember(memberId);
                }, function (response) {
                    RKTagFunction.general.notifyError();
                    iconAjaxMain.removeClass('hidden');
                    iconAjaxLoading.addClass('hidden');
                    $scope.disabledProjMemberBtn = false;
                });
            },
            closed: function() {
                $('body').addClass('modal-open');
            }
        });
    };
    
    /**
     * after remove member
     *     reset key and destroy memory
     * 
     * @param {type} member
     * @returns {undefined}
     */
    $scope.funcAfterRemoveMember = function(memberId) {
        if (typeof $scope.projectItemRelate.member[memberId] === 'undefined') {
            return true;
        }
        var key = $scope.projectItemRelate.member[memberId].key,
            index, item;
        delete $scope.projectItemRelate.member[memberId];
        for (index in $scope.projectItemRelate.member) {
            item = $scope.projectItemRelate.member[index];
            if (item.key < key) {
                continue;
            }
            item.key--;
            $('#form-proj-member table > tbody > tr[data-member-id="'+
                item.id+'"] > td.member-key').text(item.key);
                
        }
        console.log($scope.projectItemRelate.member);
    };
    
    /**
     * event btn click save project member
     * 
     * @param {object} $event
     * @param {int} id
     */
    $scope.funcProjMemberSave = function($event) {
        var domBtn = angular.element($event.currentTarget),
            formProjMember = domBtn.closest('form#form-proj-member'),
            validateFormProjMember = {},
            validationProjForm;
        // set validation form data
        if (parseInt($scope.projMember.type) === 1) {
            validationProjForm = formProjMember
                .validate($scope.validateFormProjMemberDefault);
            validationProjForm.settings.rules = 
                $scope.validateFormProjMemberDefault.rules;
        } else {
            angular.copy($scope.validateFormProjMemberDefault, validateFormProjMember);
            delete validateFormProjMember.rules['prog_langs[]'];
            validationProjForm = formProjMember.validate(validateFormProjMember);
            validationProjForm.settings.rules = validateFormProjMember.rules;
        }
        if (!formProjMember.valid()) {
            return true;
        }
        $scope.disabledProjMemberBtn = false;
        $scope.disabledProjMemberBtn = true;
        var iconAjaxLoading = domBtn.find('.ajax-loading'),
            iconAjaxMain = domBtn.find('.ajax-main-content'),
            rowTable = domBtn.closest('tr[data-member-id]');
        $scope.projMember.project_id = $scope.projectItem.id;
        iconAjaxMain.addClass('hidden');
        iconAjaxLoading.removeClass('hidden');
        $http({
            method  : 'post',
            url     : $scope.varGlobalTag.urlProjectSaveMember,
            data    : $.param($scope.projMember),
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).then(function (response) {
            if (!response.data.success) {
                RKTagFunction.general.notifyError(response.data.message);
                iconAjaxLoading.addClass('hidden');
                iconAjaxMain.removeClass('hidden');
                $scope.disabledProjMemberBtn = false;
                return true;
            }
            // reset project member data
            var keyItem;
            if (!$scope.projMember.id) { // save new item
                keyItem = Object.keys($scope.projectItemRelate.member).length;
            } else { // save old item
                keyItem = $scope.projectItemRelate.member[$scope.projMember.id].key - 1;
            }
            $scope.projMember.email = $scope.projMemberData.account;
            if ($scope.projectData.memberTypeAvaiLang.indexOf($scope.projMember.type) > -1 &&
                $scope.projMember.prog_langs && 
                $scope.projMember.prog_langs.length
            ) {
                $scope.projMember.prog_lang = $scope.projMember.prog_langs.join();
            } else {
                $scope.projMember.prog_lang = '';
            }
            $scope.projMember.flat_resource = response.data.member.flat_resource;
            $scope.projMember.id = response.data.member.id;
            $scope.funcSetProjMember($scope.projMember, keyItem);
            $scope.funcProjMemberView(rowTable, response.data.member.id);
            RKTagFunction.general.notifySuccess(response.data.message);
            $scope.disabledProjMemberBtn = false;
        },function (response) {
            RKTagFunction.general.notifyError();
            $scope.disabledProjMemberBtn = false;
            iconAjaxLoading.addClass('hidden');
            iconAjaxMain.removeClass('hidden');
        });
    };
    
    /**
     * html view member row after save
     * 
     * @param {type} id
     * @returns {String}
     */
    $scope.funcProjMemberView = function(rowTable, id) {
        var templateUrl = $sce.getTrustedResourceUrl(
            RKTagFunction.general.getTemplate('project/member-view.html')
        );
        $scope.itemMemberId = id;
        $templateRequest(templateUrl).then(function(template) {
            rowTable.after(template);
            var rowNewTable = rowTable.next();
            rowNewTable.attr('data-member-id', id);
            $compile(rowNewTable.contents())($scope);
            rowTable.remove();
        });
    };
    
    /**
     * validate form project member
     */
    $scope.validateFormProjMemberDefault = {
        rules: {
            type: {
                required: true
            },
            employee_id: {
                required: true
            },
            start_at: {
                required: true,
                date: true,
                greaterEqualDate: '#project_start_at'
            },
            end_at: {
                required: true,
                date: true,
                lessEqualDate: '#project_end_at',
                greaterEqualDate: '#proj_member-start_at'
            },
            effort: {
                required: true,
                number: true,
                min: 0,
                max: 100
            },
            'prog_langs[]': {
                required: true
            }
        },
        messages: {
            start_at: {
                greaterEqualDate: 'Must be greater or equal than start date of project'
            },
            end_at: {
                lessEqualDate: 'Must be less or equal than end date of project',
                greaterEqualDate: 'Must be greater or equal than start date of member'
            }
        },
        ignore: '',
        errorPlacement: function(error, element) {
            if (element.hasClass('bootstrap-multiselect')) {
                // custom placement for hidden select
                error.insertAfter(element.next('.btn-group'));
            } else {
                // message placement for everything else
                error.insertAfter(element);
            }
        }
    };
    /**
     * delay remote check server
     */
    RKTagFunction.general.validateRemoteDelay();
    
    /**
     * check status old of project
     * 
     * @param {int|string} status
     * @returns {Boolean}
     */
    $scope.funcCheckProjStatusOld = function(status) {
        if (!$scope.projectItem.id) {
            $scope.isProjOld = true;
            return true;
        }
        if (typeof status === 'undefined' ||  !status) {
            status = $scope.projectItem.base.status;
        }
        $scope.isProjOld = ($scope.varGlobalTag.projectStateKey
            .indexOf(parseInt(status)) > -1);
        return $scope.isProjOld;
    };
    
    /**
     * check project is old
     * 
     * @param {string} status
     * @returns {Boolean}
     */
    $scope.funcIsProjOld = function(status) {
        return $scope.varGlobalTag.projectStateKey
            .indexOf(parseInt(status)) > -1;
    };
    
    /**
     * get groups name of project
     * 
     * @returns {String}
     */
    $scope.getProjGroupsName = function() {
        var index, item, result = '';
        for (index in $scope.projectData.team) {
            item = $scope.projectData.team[index];
            if ($scope.projectItem.team.ids.indexOf(parseInt(item.id)) > -1) {
                result += item.label.trim() + ', ';
            }
        }
        return result.slice(0, -2);
    };
    
    /**
     * get groups name of project
     * 
     * @returns {String}
     */
    $scope.getProjTypeText = function() {
        var index, item;
        for (index in $scope.projectData.type) {
            item = $scope.projectData.type[index];
            if ($scope.projectItem.base.type == item.id) {
                return item.label;
            }
        }
        return null;
    };
    
    /**
     * get sales text of project
     * 
     * @returns {String}
     */
    $scope.getProjSalesText = function() {
        if (typeof $scope.project.sales === 'undefined' || !$scope.project.sales) {
            return null;
        }
        var iS, result = '';
        for (iS in $scope.project.sales) {
            result += $scope.project.sales[iS].label + ', ';
        }
        return result.slice(0, -2);
    };
    
    /**
     * get type resource effort text
     * 
     * @returns {string}
     */
    $scope.getProjTypeResourceEffort = function() {
        if (typeof $scope.varGlobalTag.projectReourceType[$scope.projectItem.base.type_mm]
            === 'undefined'
        ) {
            return null;
        }
        return $scope.varGlobalTag.projectReourceType[$scope.projectItem.base.type_mm];
    };
    
    /**
     * get langs text of project
     * 
     * @returns {String}
     */
    $scope.getProjLangsText = function() {
        if (!$scope.projectItem.lang.ids || !$scope.projectItem.lang.ids.length) {
            return null;
        }
        var iL, itemLang, result = '';
        for (iL in $scope.projectItem.lang.ids) {
            itemLang = $scope.projectItem.lang.ids[iL];
            if (typeof $scope.projectData.lang[itemLang] !== 'undefined') {
                result += $scope.projectData.lang[itemLang] + ', ';
            }
        }
        return result.slice(0, -2);
    };
    
    /**
     * check editable of project
     * 
     * @returns {Boolean}
     */
    $scope.isProjEditable = function() {
        if (!$scope.projectItem.base.status) {
            $scope.isProjEdit = true;
            return true;
        }
        if (!$scope.funcIsProjOld($scope.projectItem.base.status)) {
            $scope.isProjEdit = false;
            return false;
        }
        //scope company
        if ($scope.scopeProjOldEdit == $scope.varGlobalTag.scopeCompany) {
            $scope.isProjEdit = true;
            return true;
        }
        // scope team
        if ($scope.scopeProjOldEdit == $scope.varGlobalTag.scopeTeam &&
            RKTagFunction.general.intersectionArray($scope.projectItem.team.ids, $scope.myTeam)
        ) {
            $scope.isProjEdit = true;
            return true;
        }
        //scope team or self => leader of pm
        if ($scope.projectItem.base.manager_id == $scope.varGlobalTag.userCurrent ||
            $scope.projectItem.base.leader_id == $scope.varGlobalTag.userCurrent
        ) {
            $scope.isProjEdit = true;
            return true;
        }
        
        $scope.isProjEdit = false;
        return false;
    };
    
    /**
     * concat email and name of employee
     */
    $scope.funcEmailNameConcat = function(email, name) {
        if (typeof email === 'undefined' || !email) {
            return name;
        }
        email = email.replace(/@.*/, '');
        if (typeof name === 'undefined' || !name) {
            return email;
        }
        return name + ' (' + email + ')';
    };
    
    /**
     * click next prev process
     * 
     * @param {string} flagHref
     */
    $scope.funcProcessNext = function(flagHref) {
        if (!$scope.projectItem.id) {
            return true;
        }
        $timeout(function() {
            $('a[href="' + flagHref + '"]').trigger('click');
        });
    };
    
    /**
     * project tag
     */
    $scope.collection = [];
    $scope.initFilter();
    if (!$scope.dataFilter.dir) {
        $scope.dataFilter.dir = 'desc';
    }
    $scope.dataLoaded = true;
    $scope.fieldTagTotal = [];
    $timeout(function(){
        $scope.$broadcast('tags_selected', $scope.filterTagSelected);
    });
    // listen event init search page
    $scope.$on('is_search_page', function (event, data) {
        $scope.isSearchPage = data;
    });
    //get list tags
    $scope.getList = function (filters, isReset) {
        if (!angular.isDefined(isReset)) {
            isReset = false;
        }
        if (!$scope.dataLoaded) {
            return;
        }
        if ($scope.isSearchPage) {
            RKTagFunction.progressBar.start();
        }
        //init data before search
        $scope.dataLoaded = false;
        if ($scope.globTag.IS_REVIEW) {
            filters.is_review = $scope.globTag.IS_REVIEW;
        }
        if ($scope.globTag.projectIds && !isReset) {
            filters['project_ids[]'] = JSON.parse($scope.globTag.projectIds);
        }
        var filterField = filters.field,
            filterTag = filters.tag;
        filters = RKTagFunction.general.paramField(filters);
        if (typeof filters.tag !== 'undefined' && filters.tag) {
            filters.tag = RKTagFunction.general.encodeSlugTag(filters.tag);
        }
        if ($scope.isSearchPage) {
            RKTagLDB.init({
                then: function(isNotData) {
                    if (isNotData) {
                        $scope.dataLoaded = true;
                        $scope.dataSearched = true;
                        $scope.dataReseted = true;
                        RKTagFunction.progressBar.end();
                        return true;
                    }
                    $scope.fullProjectIds = RKTagLDB.searchField(filterField, filterTag);
                    if ($scope.fullProjectIds) {
                        filters['proj_filter'] = $scope.fullProjectIds.join('-');
                        $scope.$broadcast("EventChangeProjFilter", filters['proj_filter']);
                    }
                    $scope.getListHttp(filters);
                }
            });
        } else {
            //RKTagLDB.init();
            $scope.getListHttp(filters);
        }
    };
    /**
     * filter get list result request ajax
     * 
     * @param {object json} params
     */
    $scope.getListHttp = function(params) {
        $http({
            method: 'get',
            url: $scope.globTag.urlGetProjectList,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            params: params
        }).then(function (result) {
            var data = result.data;
            var projectData;
            if (angular.isDefined(data.projectsList)) {
                projectData = data.projectsList;
                    $scope.tagsOfField = data.tagsOfField;
                $scope.numberTagOfField = data.numberTagOfField;
                $scope.teamCountProj = data.teamCountProj;
                $scope.totalProjInField = data.totalProjInField;
                $scope.totalProject = projectData.total || 0;
                
                $scope.$broadcast('tags_data_loaded');
            } else {
                projectData = data;
            }
            $scope.collection = projectData.data;
            $scope.getActiveTeamProj();
            $scope.pager = {
                current_page: projectData.current_page,
                next_page: (projectData.last_page > projectData.current_page) ? 
                    projectData.current_page + 1 : null,
                last_page: projectData.last_page || 0,
                limit: projectData.per_page + '',
                total: projectData.total || 0,
                page: projectData.current_page
            };
            $scope.dataFilter.limit = projectData.per_page;
            $scope.dataLoaded = true;
            $scope.dataSearched = true;
            $scope.dataReseted = true;
            $scope.checkedItems = [];
            angular.element('._check_all').prop('checked', false);
            $scope.bulkAction = null;
            
            $timeout(function () {
                getTagsInfo();
            }, 300);
            RKTagFunction.progressBar.end();
        }).catch(function (error) {
            RKTagFunction.general.notifyError();
            $scope.dataLoaded = true;
            $scope.dataSearched = true;
            $scope.dataReseted = true;
            RKTagFunction.progressBar.end();
        });
    };
    
    // get list init page
    $timeout(function() {
        $scope.getList($scope.dataFilter);
    });
    
    //call after render list project
    $scope.getActiveTeamProj = function() {
        if (!$('.tb-proj-list').length) {
            return false;
        }
        angular.getTestability($('.tb-proj-list')).whenStable(function() {
            //broadcast event change project
            $scope.$broadcast('project_list_change');
            var leaderIds = [];
            $('.tb-proj-list .team-str-list').each(function(i,v) {
                var leaderId = $(v).data('leader-id');
                // project have one team => not action
                if (!leaderId || $(v).find('[data-team-id]').length < 2) {
                    return true;
                }
                if (leaderIds.indexOf(leaderId) === -1) {
                    leaderIds.push(leaderId);
                }
            });
            $scope.funcTooltipShow();
            if (!leaderIds.length) {
                return true;
            }
            $http({
                method: 'GET',
                url: $scope.globTag.urlGetProjLeaderTeam,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                params: {
                    leader: leaderIds.join('-')
                }
            }).then(function(response) {
                if (!response.data.leader) {
                    return true;
                }
                angular.forEach(response.data.leader, function(item) {
                    var teamIds = item.team_ids.split(',').map(Number);
                    $('.tb-proj-list .team-str-list[data-leader-id="' + 
                        item.employee_id+'"]').each(function(i, trDom) {
                        if ($(trDom).find('[data-team-id]').length < 2) {
                            return true;
                        }
                        $(trDom).find('[data-team-id]').each(function(j,teamNameDom) {
                            if (teamIds.indexOf(parseInt($(teamNameDom).data('team-id'))) > -1) {
                                $(teamNameDom).addClass('active');
                            }
                        });
                    }); 
                });
            }).catch(function() {
                // not action
            });
        });
    };
    
    /**
     * show tooltip for project
     */
    $scope.funcTooltipShow = function() {
        $('.tooltip-proj-tag-search[data-id]').each(function(i,v){
            var projectId = $(v).data('id');
            if (typeof $scope.tooltipProjTag[projectId] === 'undefined' ||
                !$scope.tooltipProjTag[projectId]
            ) {
                return true;
            }
            $scope.funcQtipItem($(v), $scope.tooltipProjTag[projectId]);
        });
    };
    
    /**
     * render qtip item
     * 
     * @param {type} dom
     * @param {type} text
     * @return {undefined}
     */
    $scope.funcQtipItem = function(dom, text, id) {
        if (!dom || !text) {
            if (!id) {
                return false;
            }
            dom = $('.tooltip-proj-tag-search[data-id='+id+']');
            text = $scope.tooltipProjTag[id];
        }
        dom.qtip({
            content: {
                text: text
            },
            position: {
                my: 'top center',
                at: 'center',
                viewport: $(window)
            },
            hide: {
                fixed: true,
                delay: 100
            },
            style: {
                classes: 'custom-tooltip tooltip-proj-tag'
            }
        });
    };
    
    //get tag info
    function getTagsInfo() {
        var listTagIds = [];
        var listProjTags = {};
        angular.element('.table-project .td-tags').each(function () {
            var projectId = $(this).closest('tr').data('id');
            var tagIds = $(this).data('tagids');
            if (tagIds) {
                var arrIds = $scope.toArrayFromStr(tagIds + '', '-');
                if (arrIds.length > 0) {
                    for (var i = 0; i < arrIds.length; i++) {
                        if (listTagIds.indexOf(arrIds[i]) < 0) {
                            listTagIds.push(arrIds[i]);
                        }
                    }
                    listProjTags[projectId] = arrIds;
                }
            }
        });
        if (listTagIds.length < 1) {
            return;
        }
        
        //get data on local
        var listAllFields = $scope.getStorageItem($scope.KEY_STORAGE_FIELD);
        var listTagOfFields = $scope.localTagsOfField;
        if (listAllFields && listTagOfFields) {
            $scope.tagsInfo = {};
            listAllFields = JSON.parse(listAllFields);
            angular.forEach(listAllFields, function (field) {
                if (angular.isDefined(listTagOfFields[field.id])) {
                    angular.forEach(listTagOfFields[field.id], function (tag) {
                        tag.color = field.color;
                        $scope.tagsInfo[tag.id] = tag;
                    });
                }
            });
            
            renderTagProjInfo(listProjTags, $scope.tagsInfo);
            //resize hidden tags
            $timeout($scope.resizeHiddenTags, 200);
            return;
        }
        
        $http.get($scope.globTag.urlGetTagsInfo, {
            params: {'tag_str': listTagIds.join('-')}
        }).then(function (result) {
            $scope.tagsInfo = {};
            if (result.data) {
                angular.forEach(result.data, function (item, key) {
                    $scope.tagsInfo[item.id] = item;
                });
            }
            renderTagProjInfo(listProjTags, $scope.tagsInfo);
            //resize hidden tags
            $timeout($scope.resizeHiddenTags, 200);
        });
        
        //check hash to show popup
        var hash = $location.hash();
        if (hash && hash.indexOf("show_") > -1) {
             var splHash = hash.split('_');
             if (splHash.length > 1) {
                 var projectId = splHash[1];
                 angular.element('tr[data-id="'+ projectId +'"] button.btn-edit').trigger('click');
             }
        }
        //hidden bootstrap multiple filter
        angular.element('.td-filter-teams .btn-group').removeClass('open');
    };
    
    //render html tag info of project
    function renderTagProjInfo(listProjTags, tagsInfo) {
        if ($scope.collection) {
            angular.forEach($scope.collection, function (item) {
                if (angular.isDefined(listProjTags[item.id])) {
                    var tIds = listProjTags[item.id];
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
    
    //resize hidden tags
    $scope.resizeHiddenTags = function () {
        var moreWidth = 60;
        $('body tr .td-tags ul.tagit').each(function () {
            var elThis = $(this);
            var countTag = parseInt(elThis.parent().data('tagcount'));
            var containWidth = elThis.width() - moreWidth;
            var liWidth = 0;
            var listTags = elThis.find('li');
            var numHidden = 0;
            elThis.addClass('hidden');
            listTags.each(function (idx) {
                //set display block to get width
                var hasHidden = false;
                if ($(this).hasClass('hidden')) {
                    $(this).removeClass('hidden');
                    hasHidden = true;
                }
                elThis.removeClass('hidden');
                liWidth += $(this).outerWidth(true);
                elThis.addClass('hidden');
                //set hidden if has class hidden before
                if (hasHidden) {
                    $(this).addClass('hidden');
                }
                if (liWidth >= containWidth) {
                    listTags.eq(idx).nextAll().andSelf().not('.more-tag').addClass('hidden');
                    listTags.eq(idx).prevAll().removeClass('hidden');
                    numHidden = countTag - (idx);
                    return false;
                }
            });
            
            if (numHidden > 0) {
                if (elThis.find('.more-tag .num').length > 0) {
                    elThis.find('.more-tag .num').text(numHidden);
                } else {
                    elThis.append('<li class="more-tag">+<span class="num">'+ numHidden +'</span> more</li>');
                }
            } else {
                elThis.find('.more-tag').remove();
                listTags.removeClass('hidden');
                if (countTag > $scope.globTag.SHOW_NUM_TAGS) {
                    elThis.append('<li class="more-tag">+<span class="num">'+ (countTag - $scope.globTag.SHOW_NUM_TAGS) +'</span> more</li>');
                }
            }
            elThis.removeClass('hidden');
        });
    };
    
    var timeoutRzHiddenTag;
    $(window).resize(function () {
        $timeout.cancel(timeoutRzHiddenTag);
        timeoutRzHiddenTag = $timeout($scope.resizeHiddenTags, 200);
    });
    
    //sorting
    $scope.doSort = function (orderby) {
        if (!$scope.dataLoaded) {
            return;
        }
        if ($scope.dataFilter.order == orderby) {
            var dir = $scope.dataFilter.dir == 'asc' ? 'desc' : 'asc';
            $scope.dataFilter.dir = dir;
        }
        $scope.dataFilter.order = orderby;
        $scope.getList($scope.dataFilter);
        $scope.saveFilter();
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
        
        $scope.dataFilter.page = page;
        $scope.getList($scope.dataFilter);
        $scope.saveFilter();
        return false;
    };
    //change per page
    $scope.changePerPage = function () {
        if (!$scope.dataLoaded) {
            return;
        }
        $scope.pager.page = 1;
        $scope.dataFilter.limit = $scope.pager.limit;
        $scope.dataFilter.page = 1;
        $scope.getList($scope.dataFilter);
        $scope.saveFilter();
    };
    //search data
    $scope.searchData = function (type, $event, option) {
        if (!$scope.dataLoaded) {
            return;
        }
        $scope.dataFilter.page = 1;
        switch (type) {
            case 1://input
                if ($event.keyCode == 13) {
                    $scope.dataSearched = false;
                    $scope.getList($scope.dataFilter);
                    $scope.saveFilter();
                }
                break;
            case 3: // tagit
                if (typeof option === 'undefined' || 
                    typeof option.tagit === 'undefined'
                ) {
                    break;
                }
                //ts: tag-search
                if ($(option.tagit).tagit('assignedTags').length) {
                    $scope.dataFilter.tag = $(option.tagit)
                        .tagit('assignedTags');
                } else {
                    delete $scope.dataFilter.tag;
                }
                $scope.dataSearched = false;
                $scope.getList($scope.dataFilter);
                $scope.saveFilter();
                break;
            default: // 2
                $scope.dataSearched = false;
                $scope.getList($scope.dataFilter);
                $scope.saveFilter();
                break;
        }
    };
    //reset filter
    $scope.resetDataFilter = function ($event) {
        if (!$scope.dataLoaded) {
            return;
        }
        $scope.initFilter(true);
        $scope.dataFilter.page = 1;
        $scope.dataReseted = false;
        $location.search({});
        if ($window.location.search) {
            var location = $window.location;
            $window.location.href = location.origin + location.pathname;
            return;
        } else {
            //set tags selected null;
            $scope.$broadcast('tags_selected', []);
        }
        delete $scope.dataFilter['project_ids[]'];
        $scope.getList($scope.dataFilter, true);
        if ($event && $event.currentTarget) {
            var tagit = $($event.currentTarget).data('tagit');
            if (tagit && $(tagit).length) {
                $(tagit).tagit('removeAll');
            }
        }
        $timeout(function () {
            angular.element('.select2-hidden-accessible:not(.not-reset)').next('.select2-container')
                    .find('.select2-selection__rendered').text('');
            angular.element('.bootstrap-multiselect').multiselect('deselectAll');
            angular.element('.bootstrap-multiselect').multiselect('refresh');
        }, 200);
        //broadcast event reset project filter
        $scope.$broadcast('reset_project_filter');
    };
    //render item status
    $scope.projTagStatuses = JSON.parse($scope.globTag.projTagStatuses);
    $scope.getLabelStatus = function (status) {
        if (typeof $scope.projTagStatuses[status] != 'undefined') {
            return $scope.projTagStatuses[status];
        }
        return null;
    };
    //render class color status
    $scope.getClassStatus = function (status, isLabel) {
        if (!angular.isDefined(isLabel)) {
            isLabel = false;
        }
        if (status == $scope.globTag.PROJ_STT_APPROVE) {
            if (isLabel) {
                return 'label-success';
            }
            return 'text-green';
        } else if (status == $scope.globTag.PROJ_STT_REVIEW) {
            if (isLabel) {
                return 'label-primary';
            }
            return 'text-light-blue';
        } else if (status == $scope.globTag.PROJ_STT_ASSIGNED) {
            if (isLabel) {
                return 'label-info';
            }
            return 'text-aqua';
        } else {
            if (isLabel) {
                return 'label-danger';
            }
            return 'text-red';
        }
    };
    //render tag action
    $scope.actionClasses = JSON.parse($scope.globTag.tagActionClasses);
    $scope.tagActionClass = function (action) {
        var acClass = 'tag-action';
        action = parseInt(action);
        if (angular.isDefined($scope.actionClasses[action])) {
            return acClass + ' ' + $scope.actionClasses[action];
        }
        return acClass;
    };

    //hide modal
    $scope.dismissModal = function ($event) {
        angular.element($event.target).closest('.modal').modal('hide');
    };
    //show modal edit
    $scope.fields = [];
    if ($scope.globTag.FIELDS) {
        $scope.fields = JSON.parse($scope.globTag.FIELDS);
    }
    $scope.showModalEditTag = function (item) {
        $templateRequest($scope.generalTag.getTemplate('project/edit-tag.html'))
                .then(function(template) {
                    var modalElement = $compile(template)($scope);
                    var appendElm = angular.element('#modal-edit-tags');
                    appendElm.html('').append(modalElement);
                    modalElement.modal('show');
                    //generate project field tags
                    $timeout(function (){
                        modalElement.find('.tree-list li:eq(1) a').click();
                    }, 500);
                    
                    $scope.projectFields = [];
                    $scope.projectFields.push($scope.globTag.SET_FIELD_PROJ);
                    $scope.listEditTags = [];
                    $scope.fieldsLoaded = [];
                    $scope.editItem = item;
                    $scope.fieldActiveId = null;
                    $scope.fieldSelected = null;
                }).catch(function () {
                    RKTagFunction.general.notifyError();
                });
    };
    
    //init project field
    $scope.initProjectFields = function (projectId) {
        $scope.fieldActiveId = null;
        $scope.fieldsLoaded = [];
        $scope.listEditTags = [];
        $scope.fieldSelected = null;
        $scope.projectFields = [];
        $scope.projectFields.push($scope.globTag.SET_FIELD_PROJ);
        //generate project field tags
        $timeout(function (){
            $('#modal-project-old-edit').find('.tree-list li:eq(1) a').click();
        }, 200);
        
        $scope.fieldTagTotal = {};
        $http.get($scope.globTag.urlCountFieldsTag, {
            params: {project_id: projectId}
        }).then(function (result) {
            if (result.data) {
                angular.forEach(result.data, function (field, key) {
                    if (parseInt(field.tag_count) > 0) {
                        $scope.fieldTagTotal[parseInt(field.field_id)] = field.tag_count;
                    }
                });
            }
            $('#project_tags_form').addClass('count-tag-loaded');
        });
    };
    
    //listen data from search controller
    $scope.$on('listen_fields', function (event, data) {
        if ($scope.fields) {
            $scope.fields = data;
        }
    });
    //get list tags of project field
    $scope.listEditTags = [];
    $scope.fieldsLoaded = [];
    $scope.getFieldProjTags = function (fieldId, $event) {
        var modalElement = angular.element($event.target).closest('.modal');
        if ($scope.fieldActiveId == fieldId) {
            modalElement.find('.proj-tags[data-id="'+ fieldId +'"]').parent().removeClass('hidden');
            return;
        }
        $scope.fieldActiveId = fieldId;
        $scope.fieldSelected = $scope.fields[fieldId];
        //check has child
        if ($scope.fieldSelected.child.length > 0) {
            $scope.fieldNotLeaves = true;
            return;
        }
        $scope.fieldNotLeaves = false;
        //check loaded
        if ($scope.fieldsLoaded.indexOf(fieldId) > -1) {
            modalElement.find('.proj-tags[data-id="'+ fieldId +'"]').parent().removeClass('hidden');
            return;
        }
        $scope.fieldsLoaded.push(fieldId);
        //request to get list tags
        $scope.loadingTags = true;
        $scope.editItem = angular.extend($scope.editItem, $scope.projectItem);
        
        modalElement.find('.tags-content').addClass('hidden');
        $http.get(RKVarGlobalTag.urlEditProjectTag, {
            params: {
                proj_id: $scope.editItem.id,
                field_id: fieldId
            }
        }).then(function (result) {
            $scope.avalidTags = [];
            var elTagIt = modalElement.find('#project_tags_form .proj-tags[data-id="'+ fieldId +'"]');
            try {
                elTagIt.tagit("destroy");
            } catch (e) {}
            $scope.listEditTags[fieldId] = result.data.tags;
            $scope.tagReadOnly = true;
            $timeout(function () {
                var elTagIt = modalElement.find('#project_tags_form .proj-tags[data-id="'+ fieldId +'"]');
                //try to destroy elTagIt if it init before
                try {
                    elTagIt.tagit("destroy");
                } catch (e) {
                    //done
                }
                var readOnly = true;
                if (result.data.permissUpdateTag) {
                    readOnly = false;
                    $scope.editItem.showEditTagBtn = true;
                }
                $scope.permissUpdateTag = result.data.permissUpdateTag;
                if (($scope.editItem.tag_status == $scope.globTag.PROJ_STT_APPROVE
                        && !$scope.globTag.permissApprove 
                        || $scope.globTag.IS_SEARCH)) {
                    readOnly = true;
                }
                $scope.tagReadOnly = readOnly;
                elTagIt.tagit({
                    allowSpaces: true,
                    readOnly: readOnly,
                    caseSensitive: false,
                    tagSource: function (req, res) {
                        //get local tags
                        var listAvalidTags = $scope.getStorageTags();
                        if (!listAvalidTags || !listAvalidTags[fieldId]) {
                            res([]);
                        } else {
                            listAvalidTags = listAvalidTags[fieldId];
                            //excerpt tag
                            var excerptTag = $.map(result.data.tags, function (item) {
                                return item.value;
                            });
                            //list search tag
                            var search = [];
                            for (var i in listAvalidTags) {
                                var tagItem = listAvalidTags[i];
                                if (excerptTag.indexOf(tagItem.value) > -1) {
                                    continue;
                                }
                                if (tagItem.value.substr(0, req.term.length).toLowerCase() == req.term.toLowerCase()) {
                                    search.push(tagItem.value);
                                }
                            }
                            res(search);
                        }
                    },
                    
                    beforeTagAdded: function (event, ui) {
                        if (!ui.duringInitialization) {
                            $scope.loadingTags = true;
                            $http.post($scope.globTag.urlAddTag, {
                                project_id: $scope.editItem.id || $scope.projectItem.id,
                                field_id: fieldId,
                                tag: ui.tagLabel
                            }).then(function (result) {
                                if (result.data.reload) {
                                    window.location.reload();
                                    return true;
                                }
                                $scope.loadingTags = false;
                                $scope.isChangeProjectTag = true;
                                if (result.data.tag_id) {
                                    angular.element(ui.tag).addClass('tagid-' + result.data.tag_id);
                                }
                                updateFieldTagCount();
                            }).catch(function (error) {
                                RKTagFunction.general.notifyError(error.data.message);
                                $scope.loadingTags = false;
                                angular.element(ui.tag).remove();
                            });
                        }
                    },
                    beforeTagRemoved: function (event, ui) {
                        if (!ui.tagLabel) {
                            return;
                        }
                        $scope.loadingTags = true;
                        var idTag = ui.tag.attr('class').match(/tagid-\d+/);
                        if (!idTag || !idTag.length || !idTag[0]) {
                            return;
                        }
                        $http.delete($scope.globTag.urlDeleteTag, {
                            params: {
                                project_id: $scope.editItem.id || $scope.projectItem.id,
                                field_id: fieldId,
                                tag_id: idTag[0].replace(/\D+/, '')
                            }
                        }).then(function (result) {
                            if (result.data.reload) {
                                window.location.reload();
                                return true;
                            }
                            angular.element(ui.tag).remove();
                            $scope.loadingTags = false;
                            $scope.isChangeProjectTag = true;
                            updateFieldTagCount();
                        }).catch(function (error) {
                            RKTagFunction.general.notifyError(error.data.message);
                            $scope.loadingTags = false;
                        });
                        return false;
                    }
                });
                elTagIt.parent().removeClass('hidden');
                elTagIt.find('.tagit-new inpu').focus();
            }, 300);
            $scope.loadingTags = false;
        }).catch(function (error) {
            RKTagFunction.general.notifyError(error.data.message);
            $scope.loadingTags = false;
        });
    };

    // update js field tag count after submit, save, approve
    function updateFieldTagCount () {
        angular.element('.tags-content ul.tagit').each(function() {
            var fieldId = $(this).data('id') || null;
            if (fieldId) {
                $scope.fieldTagTotal[fieldId] = $(this).find('li.tagit-choice').length;
            }
        });
    };

    //show modal edit assignee
    $scope.modalEditAssignee = function (item) {
        //show modal
        $templateRequest($scope.generalTag.getTemplate('project/edit-assignee.html'))
                .then(function(template) {
                    var modalElement = $compile(template)($scope);
                    var appendElm = angular.element('#modal-edit-assignee');
                    appendElm.html('').append(modalElement);
                    modalElement.modal('show');
                    
                    $scope.editProject = item;
                    $scope.projAssignee = item.assignee_id;
                    $scope.listAssignees = [{id: item.assignee_id, text: $scope.convertAccount(item.assignee_name)}];
                }).catch(function () {
                    RKTagFunction.general.notifyError();
                });
    };
    
    //set select2 ui option remote data
    $scope.assigneeSelect2Opts = {
        ajax: {
            url: $scope.globTag.urlSearchEmployee,
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
            }
        },
        minimumInputLength: 1
    };

    //save assignee
    $scope.saveProjAssignee = function (valid, $event) {
        if (!valid) {
            return;
        }
        var form = angular.element($event.target);
        var assigneeId = form.find('select').val();
        var assigneeIdArr = assigneeId.split(':');
        if (assigneeIdArr.length > 1) {
            assigneeId = assigneeIdArr[1];
        }
        if (!assigneeId || isNaN(assigneeId)) {
            return true;
        }
        var projectId;
        if ($scope.isBulkAction) {
            projectId = $scope.checkedItems;
        } else {
            projectId = $scope.editProject.id || $scope.projectItem.id;
            /*if (assigneeId == $scope.editProject.assignee_id || assigneeId == $scope.projectItem.assignee_id) {
                return;
            }*/
        }
        
        $scope.savingAssignee = true;
        $http.post($scope.globTag.urlSaveProjectAssignee, {
            project_id: projectId,
            assignee_id: assigneeId
        }).then(function (result) {
            var modal = form.closest('.modal');
            if (modal.attr('id') == 'modal_edit_proj_assignee') {
                modal.modal('hide');
            }
            
            var data = result.data;
            if ($scope.isBulkAction) {
                $scope.isBulkAction = false;
                $scope.getList($scope.dataFilter);
            } else {
                $scope.editProject.assignee_id = data.assignee_id;
                $scope.editProject.assignee_name = data.assignee_name;
                $scope.editProject.tag_status = data.tag_status;
            }
            RKTagFunction.general.notifySuccess(result.data.message);
            $scope.savingAssignee = false;
        }).catch(function (error) {
            $scope.savingAssignee = false;
            RKTagFunction.general.notifyError(error.data.message);
        });
    };
    
    //submit edit tag
    $scope.submitEditTag = function (item, $event) {
        ModalService.showModal({
            templateUrl: $scope.generalTag.getTemplate('confirm-modal.html'),
            controller: 'projModalController'
        }).then(function (modal) {
            modal.element.modal();
            modal.scope.confirmTitle = $scope.trans['Confirm submit'];
            modal.scope.confirmContent = $scope.trans['Are you sure submit'];
            modal.close.then(function (result) {
                if (!result || item.tag_status == $scope.globTag.TAG_STT_REVIEW) {
                    return;
                }
                var form = angular.element($event.target).closest('form');
                var listTags = {};
                var listAllTags = [];
                form.find('ul.proj-tags').each(function () {
                    if ($(this).hasClass('tagit')) {
                        var fieldId = $(this).data('id');
                        var assignedTags = $(this).tagit('assignedTags');
                        listTags[fieldId] = assignedTags;
                        listAllTags = $.merge(listAllTags, assignedTags);
                    }
                });
                
                item.loading = true;
                $http.post($scope.globTag.urlSubmitProjectTag, {
                    project_id: item.id,
                    proj_tags: listTags
                }).then(function (result) {
                    if (result.data.reload) {
                        window.location.reload();
                        return true;
                    }
                    item.tag_status = result.data.tag_status;
                    item.assignee_id = result.data.assignee_id;
                    item.assignee_name = result.data.assignee_name;
                    if (angular.isDefined(result.data.tag_results)) {
                        item.tag_names = result.data.tag_results;
                    }
                    item.loading = false;
                    if (angular.isDefined($event)) {
                        angular.element($event.target).closest('.modal').modal('hide');
                    }
                    RKTagFunction.general.notifySuccess(result.data.message);
                    
                    updateFieldTagCount();
                }).catch(function (error) {
                    item.loading = false;
                    RKTagFunction.general.notifyError(error.data.message);
                });
            });
        });
    };
    
    //approve tag
    $scope.approveTag = function (item, $event) {
        ModalService.showModal({
            templateUrl: $scope.generalTag.getTemplate('confirm-modal.html'),
            controller: 'projModalController'
        }).then(function (modal) {
            modal.element.modal();
            modal.scope.confirmTitle = $scope.trans['Confirm submit'];
            modal.scope.confirmContent = $scope.trans['Are you sure submit'];
            modal.close.then(function (result) {
                if (!result) {
                    return;
                }
                
                var form = angular.element($event.target).closest('form');
                var listTags = {};
                var listAllTags = [];
                form.find('ul.proj-tags').each(function () {
                    if ($(this).hasClass('tagit')) {
                        var fieldId = $(this).data('id');
                        var assignedTags = $(this).tagit('assignedTags');
                        listTags[fieldId] = assignedTags;
                        listAllTags = $.merge(listAllTags, assignedTags);
                    }
                });
                
                item.loading = true;
                $http.post($scope.globTag.urlApproveProjectTag, {
                    project_id: item.id,
                    proj_tags: listTags
                }).then(function (result) {
                    if (result.data.reload) {
                        window.location.reload();
                        return true;
                    }
                    item.tag_status = result.data.tag_status;
                    item.assignee_id = result.data.assignee_id;
                    item.assignee_name = result.data.assignee_name;
                    item.loading = false;
                    if (angular.isDefined($event)) {
                        angular.element($event.target).closest('.modal').modal('hide');
                    }
                    
                    updateFieldTagCount();
                    RKTagFunction.general.notifySuccess(result.data.message);
                }).catch(function (error) {
                    item.loading = false;
                    RKTagFunction.general.notifyError(error.data.message);
                });
            });
        });
    };
    
    /**
     * check all items
     */
    $scope.checkedItems = [];
    $scope.checkAllItem = function ($event) {
        var __this = angular.element($event.target);
        angular.element('._check_item').prop('checked', __this.is(":checked"));
        $scope.checkedItems = [];
        angular.element('._check_item:checked').each(function () {
            $scope.checkedItems.push($(this).val());
        });
    };
    /**
     *  check item 
     */
    $scope.checkItem = function (id, $event) {
        var __this = angular.element($event.target);
        var index = $scope.checkedItems.indexOf(id);
        if (__this.is(":checked")) {
            if (index < 0) {
                $scope.checkedItems.push(id);
            }
        } else {
            if (index > -1) {
                $scope.checkedItems.splice(index, 1);
            }
        }
        var allLen = angular.element('._check_item').length;
        var checkedLen = angular.element('._check_item:checked').length;
        angular.element('._check_all').prop('checked', allLen === checkedLen);
    };
    
    //filter team list
    $scope.teamList = [];
    if ($scope.globTag.TEAM_LIST) {
        $scope.teamList = jQuery.map(JSON.parse($scope.globTag.TEAM_LIST), function (item, idx) {
            return {
                value: item.value,
                label: $scope.generalTag.htmlDecode(item.label)
            };
        });
    }
    
    $timeout(function () {
        var teamSelectChange = false;
        angular.element('.td-filter-teams select').multiselect({
            includeSelectAllOption: false,
            nonSelectedText: 'Choose items',
            allSelectedText: 'All',
            nSelectedText: 'items selected',
            numberDisplayed: 0,
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            onDropdownShow: function () {
                teamSelectChange = false;
            },
            onChange: function (optionChange) {
                teamSelectChange = true;
            },
            onDropdownHidden: function(event) {
                if (!teamSelectChange) {
                    return;
                }
                $scope.searchData(2);
            }
        });
        
        var inputGroupBtn = angular.element('.td-filter-teams .multiselect-container li.filter .input-group-btn');
        if (!inputGroupBtn.hasClass('apply-search')) {
            var btnApply = $compile('<button class="btn btn-default apply-search" ng-click="searchData(2)">Apply</button>')($scope);
            inputGroupBtn.addClass('apply-search').append(btnApply);
        }
    }, 200);
    
    //actions
    $scope.bulkActions = [];
    if ($scope.globTag.ACTION_LIST) {
        $scope.bulkActions = JSON.parse($scope.globTag.ACTION_LIST);
    }
    
    $scope.projTagActions = function () {
        if (!$scope.bulkAction || $scope.checkedItems.length < 1 || !$scope.dataLoaded) {
            return;
        }
        $scope.isBulkAction = true;
        if ($scope.bulkAction == $scope.globTag.ACTION_ASSIGN) {
            //show modal edit assignee
            $templateRequest($scope.generalTag.getTemplate('project/edit-assignee.html'))
                .then(function(template) {
                    var modalElement = $compile(template)($scope);
                    var appendElm = angular.element('#modal-edit-assignee');
                    appendElm.html('').append(modalElement);
                    modalElement.modal('show');
                    $scope.projAssignee = null;
                }).catch(function () {
                    RKTagFunction.general.notifyError('Not found template');
                });
            $scope.bulkAction = null;
        } else {
            ModalService.showModal({
                templateUrl: $scope.generalTag.getTemplate('confirm-modal.html'),
                controller: 'projModalController'
            }).then(function (modal) {
                modal.element.modal();
                modal.scope.confirmTitle = $scope.trans['Confirm'];
                modal.scope.confirmContent = $scope.trans['Are you sure action'];
                modal.close.then(function (result) {
                    if (!result) {
                        $scope.bulkAction = null;
                        return;
                    }

                    $scope.dataLoaded = false;
                    $http.post($scope.globTag.urlBulkAction, {
                        project_id: $scope.checkedItems,
                        action: $scope.bulkAction
                    }).then(function (result) {
                        $scope.dataLoaded = true;
                        $scope.getList($scope.dataFilter);
                        RKTagFunction.general.notifySuccess(result.data.message);
                    }).catch(function (error) {
                        $scope.dataLoaded = true;
                        RKTagFunction.general.notifyError(error.data.message);
                    });
                });
                $scope.isBulkAction = false;
            });
        }
    };
    /*
     * check show button submit, approve
     */
    $scope.checkShowBtnSubmitTag = function (editItem, type) {
        if ($scope.globTag.IS_SEARCH || !$scope.permissUpdateTag) {
            return false;
        }
        switch (type) {
            case 'submit':
                if (!$scope.globTag.permissSubmit) {
                    return false;
                }
                if ($scope.globTag.permissApprove 
                        || editItem.tag_status == $scope.globTag.PROJ_STT_APPROVE) {
                    return false;
                }
                return true;
            case 'approve':
                return $scope.globTag.permissApprove;
            default:
                return false;
        };
    };
    
    $scope.rootFieldTree = $scope.globTag.SET_FIELD_PROJ;
    $scope.$watch('fields', function() {
        var htmlTree = RKTagFunction.getHtmlFieldTree.init(
            $scope.rootFieldTree, 
            $scope.fields,
            1,
            0,
            {
                type: $scope.varGlobalTag.fieldTypeInfo, 
                level: 2,
                attrMore: 'ng-click="getFieldProjTags({{id}}, $event)" ' +
                    'id="field_{{id}}"'
            }
        );
        $scope.htmlFieldTree = $sce.trustAsHtml(htmlTree);
    });
    
    /*
     * load employee
     */
    $scope.toggleSearchTab = function (tab, $event) {
        if (tab == 'employee') {
            $timeout($scope.resizeHiddenTags, 200);
            if (!$scope.dataLoaded) {
                $event.preventDefault();
                $event.stopPropagation();
                return false;
            }
            $scope.$broadcast('load_employees');
        } else if (tab == 'project') {
            $timeout($scope.resizeHiddenTags, 200);
        } else {
            return;
        }
        $scope.saveFilter($scope.dataFilter);
    };
    $scope.tagit = {};
    /**
     * show scroll bar uitag
     * 
     * @param {object} dom
     * @returns {Boolean}
     */
    $scope.tagit.showScroll = function(dom) {
        var widthScroll = $('.tag-search.tagit')[0].scrollWidth,
            width = $('.tag-search.tagit').outerWidth();
        if (width >= widthScroll) {
            dom.removeClass('has-scroll-hoz');
            dom.closest('.content-header').removeClass('has-scroll-hoz');
            return true;
        }
        dom.addClass('has-scroll-hoz');
        dom.closest('.content-header').addClass('has-scroll-hoz');
        return true;
    };
    
    /**
     * search page for search box
     */
    if ($('.tag-search').length) {
        $('.tag-search').tagit({
            allowSpaces: true,
            caseSensitive: false,
            placeholderText: 'Enter tag...',
            afterTagAdded: function(event, ui) {
                $scope.tagit.showScroll($(event.target));
            },
            afterTagRemoved: function(event, ui) {
                $scope.tagit.showScroll($(event.target));
                $('.btn-search-tag').trigger('click');
            },
            autocomplete: {
                source: function(request, response) {
                    response(RKTagFunction.ng.tagitSource($scope, request, this.element));
                }
            }
        });
        if (typeof $scope.dataFilter.tag !== 'undefined') {
            angular.forEach($scope.dataFilter.tag, function(value) {
                $('.tag-search').tagit('createTag', value);
            });
        }
        $('.tag-search .tagit-new input').keypress(function(event) {
            if (event.which === $.ui.keyCode.ENTER && 
                !$(this).val() && 
                $('.btn-search-tag').length
            ) {
                $('.btn-search-tag').trigger('click');
            }
        });
    }
    
    /*
     * get project type
     */
    $scope.listProjectTypes = {};
    if (angular.isDefined($scope.globTag.labelProjectTypes)) {
        $scope.listProjectTypes = JSON.parse($scope.globTag.labelProjectTypes);
    }
    $scope.labelProjType = function (type) {
        if (!angular.isDefined($scope.listProjectTypes[type])) {
            return null;
        }
        return $scope.listProjectTypes[type];
    };
    /**
     * render project more information
     */
    $scope.tooltipProjTag = {};
    $scope.tooltipProjTagData = {};
    $scope.renderProjTooltip = function (project) {
        var id = project.project_id;
        if (!id) {
            return true;
        }
        if (typeof $scope.tooltipProjTagData[id] === 'undefined') {
            $scope.tooltipProjTagData[id] = {};
        }
        var html = '';
        if (project.pm_email) {
            html += '<li><strong>'+ $scope.trans["Project Manager"] +'</strong>: '+ $scope.convertAccount(project.pm_email) +'</li>';
            $scope.tooltipProjTagData[id].pm_email = project.pm_email;
        }
        if (project.start_date) {
            html += '<li><strong>'+ $scope.trans["Time"] +'</strong>: '+ project.start_date.replace(/\s.*/, '') +' <i class="fa fa-long-arrow-right"></i> '+ project.end_date.replace(/\s.*/, '') +'</li>';
            $scope.tooltipProjTagData[id].start_date = project.start_date;
            $scope.tooltipProjTagData[id].end_date = project.end_date;
        }
        var sale = '';
        if (project.sale_emails) {
            var saleEmails = $scope.toArrayFromStr(project.sale_emails, ', ');
            if (saleEmails.length > 0) {
                sale = [];
                for (var i in saleEmails) {
                    sale.push($scope.convertAccount(saleEmails[i]));
                }
                sale = sale.join(', ');
            }
            html += '<li><strong>'+ $scope.trans["Sale"] +': '+ sale +'</li>';
        }
        if (project.scope_desc) {
            html += '<li class="pre-text"><strong>'+ $scope.trans["Description"] +'</strong>: '+ project.scope_desc +'</li>';
            $scope.tooltipProjTagData[id].scope_desc = project.scope_desc;
        }
        if (project.scope_scope) {
            html += '<li class="pre-text"><strong>'+ $scope.trans["Scope"] +'</strong>: '+ project.scope_scope +'</li>';
            $scope.tooltipProjTagData[id].scope_scope = project.scope_scope;
        }
        if (!html) {
            return true;
        }
        html = '<ul class="project-tooltip">' + html + '</ul>';
        $scope.tooltipProjTag[id] = html;
    };
    
    /**
     * show popup tag for choose multi
     * 
     * @param {type} $event
     * @return {undefined}
     */
    $scope.funcTagForMulti = function ($event, fieldId) {
        $event.preventDefault();
        $scope.checkboxTag = {
            all: false,
            item: {}
        };
        $scope.tagOfFieldAvai = RKTagFunction.ng.tagOfField($scope, 
            $('.proj-tags.tag-field-list.tagit[data-id="'+fieldId+'"]'), fieldId);
        $('#modal-tag-field-multi-choose').modal('show');
        
    };
    
    /**
     * check all box tag of field
     * 
     * @param {type} flagModel
     * @return {undefined}
     */
    $scope.funcChangeSelectAllTag = function() {
        var value = $scope.checkboxTag.all;
        angular.forEach($('input[name="checkbox_tag_field_item"]'), function(item) {
            $scope.checkboxTag.item[$(item).val()] = value;
        });
    };
    
    /**
     * check/uncheck item
     * 
     * @param {type} tagId
     * @return {Boolean}
     */
    $scope.funcChangeSelectItemTag = function(tagId) {
        if (typeof $scope.checkboxTag.item[tagId] === 'undefined' ||
            !$scope.checkboxTag.item[tagId]
        ) {
            $scope.checkboxTag.all = false;
            return true;
        }
        var flagAll = true;
        angular.forEach($('input[name="checkbox_tag_field_item"]'), function(item) {
            if (!$scope.checkboxTag.item[$(item).val()]) {
                flagAll = false;
                return false;
            }
        });
        $scope.checkboxTag.all = flagAll;
    };
    
    /**
     * submit multi tag for field
     * 
     * @return {undefined}
     */
    $scope.funcSubmitTagMulti = function($event, fieldId) {
        $event.preventDefault();
        var values = [], i;
        for (i in $scope.checkboxTag.item) {
            if ($scope.checkboxTag.item[i]) {
                values.push(i);
            }
        }
        if (!values.length) {
            return true;
        }
        $scope.loadingTags = true;
        $('#modal-tag-field-multi-choose').modal('hide');
        $http.post($scope.globTag.urlAddTag, {
            project_id: $scope.projectIdActive,
            field_id: fieldId,
            tag: values.join('-'),
            multi: true
        }).then(function (result) {
            if (result.data.success) {
                $scope.loadingTags = false;
                $scope.isChangeProjectTag = true;
                if (result.data.count) {
                    $scope.fieldActiveId = null;
                    var index = $scope.fieldsLoaded.indexOf(fieldId);
                    $scope.fieldsLoaded.splice(index, 1);
                    $timeout(function() {
                        $('.tree-list .field-item[data-id="'+fieldId+'"]').trigger('click');
                    }, 200);
                    if (typeof $scope.fieldTagTotal[fieldId] === 'undefined') {
                        $scope.fieldTagTotal[fieldId] = 0;
                    }
                    $scope.fieldTagTotal[fieldId] += result.data.count;
                }
            } else {
                if (result.data.reload) {
                    window.location.reload();
                    return true;
                }
                RKTagFunction.general.notifyError(result.data.message);
                $scope.loadingTags = false;
            }
        }).catch(function (error) {
            if (typeof error.data === 'undefined') {
                var message = 'Error system';
            } else {
                var message = error.data.message;
            }
            RKTagFunction.general.notifyError(message);
            $scope.loadingTags = false;
        });
    };
});
/**
 * end projectController
 */

/**
 * projectModalController ModelService
 */
rkTagApp.controller('projModalController', function ($scope, close, $element) {
        $scope.dismissModal = function (result) {
            $element.modal('hide');
            close (result, 200);
        };
    });
/**
 * End projectModelController
 */

//generate tagit
rkTagApp.directive('tagUi', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            attrs.$observe('tagUi', function () {
                var options = attrs.tagUi;
                if (options) {
                    options = JSON.parse(options);
                } else {
                    options = {};
                }
                options.allowSpaces = true;
                element.tagit(options);
            });
        }
    };
});

})(angular, jQuery, RKTagFunction, RKTagLDB, RKfuncion);
