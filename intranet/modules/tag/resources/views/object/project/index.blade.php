@extends('layouts.default')
<?php
use Rikkei\Core\View\CoreUrl;
use Illuminate\Support\Facades\Config;
use Rikkei\Tag\View\TagConst;
use Rikkei\Team\View\Config as ViewConfig;
use Rikkei\Project\Model\Project;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;
use Rikkei\Tag\View\TagGeneral;
use Rikkei\Team\Model\Permission as PermissionModel;

$teamList = TeamList::toOption(null, false, null);
$versionAsset = Config::get('view.assets_verson');
$scope = Permission::getInstance();
?>

@section('title')
@if ($isReview)
{{ trans('tag::view.Project tag review') }}
@else
{{ trans('tag::view.Project tagging') }}
@endif
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.1/css/bootstrap-colorpicker.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css" />
<link rel="stylesheet" href="{{ URL::asset('lib/tag-it/css/jquery.tagit.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('lib/tag-it/css/tagit.ui-zendesk.min.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_tag/css/styles.css') }}" />
@endsection

@section('body_attrs', 'ng-app="RkTagApp" ng-controller="projectController"')

@section('content')
<div class="row tag-object-manage">
    <div class="col-sm-12">

        <div class="box box-info">
            <div class="box-body row">
                <div class="col-md-6">
                    <button ng-if="{{ $permissProjOldEdit }}" class="btn-delete text-uppercase" ng-click="formProjectOldEdit($event)">
                        <i class="fa fa-plus"></i>&nbsp;
                        {{ trans('tag::view.Add old project') }}
                    </button>
                    
                    @if ($permissSubmit || $permissApprove)
                    <div class="form-inline bulk-actions">
                        <div class="input-group" ng-show="checkedItems.length > 0 && dataLoaded">
                            <select class="form-control not-reset" 
                                    ng-select2
                                    select2-search="0"
                                    ng-model="bulkAction" style="width: 180px;"
                                    ng-change="projTagActions()"
                                    ng-options="key as value for (key, value) in bulkActions">
                                <option value="">--Select action--</option>
                            </select>
                        </div>
                    </div>
                    @endif

                    <button type="button" ng-hide="dataLoaded" class="btn btn-primary"><i class="fa fa-spin fa-refresh"></i></button>
                    <div id="modal-project-wrapper" ng-bind-html-compile="htmlProjectEditorModal"></div>
                </div>
                <div class="col-md-6">
                    <div ng-include="generalTag.getTemplate('filter.html')"></div>
                </div>
            </div>
            
            <div class="table-responsive multiselect2-wrapper flag-over-hidden">
                <table class="table table-bordered table-hover dataTable table-project">
                    <thead>
                        <tr>
                            <th ng-if="globTag.permissSubmit || globTag.permissApprove"><input type="checkbox" ng-click="checkAllItem($event)" class="_check_all"></th>
                            <th>No.</th>
                            <th class="sorting width-150" ng-click="doSort('name')" ng-class="classSorting('name')" ng-bind="trans['Project name']"></th>
                            <th class="sorting width-100" ng-click="doSort('team_names')" ng-class="classSorting('team_names')" ng-bind="trans['Team']"></th>
                            <th class="sorting width-80" ng-click="doSort('assignee_name')" ng-class="classSorting('assignee_name')" ng-bind="trans['Assignee']"></th>
                            <th class="sorting width-80" ng-click="doSort('tag_status')" ng-class="classSorting('tag_status')" ng-bind="trans['Status']"></th>
                            <th class="sorting" ng-click="doSort('count_tags')" ng-class="classSorting('count_tags')" style="min-width: 300px;" ng-bind="trans['Tags']"></th>
                            <th class="width-40"></th>
                        </tr>
                    </thead>
                    <tbody class="tb-proj-list">
                        <tr>
                            <td ng-if="globTag.permissSubmit || globTag.permissApprove"></td>
                            <td></td>
                            <td>
                                <input type="text" ng-model="dataFilter.search['projs.name']" ng-keyup="searchData(1, $event)" 
                                       ng-attr-placeholder="<%= trans['Search'] %>..." class="filter-search form-control" />
                            </td>
                            <td class="dropdown bst-muls-wrapper td-filter-dropdown td-filter-teams">
                                <select multiple class="hidden bootstrap-multiselect"
                                    data-number="0"
                                    ng-model="dataFilter.search['tpj.team_id']"
                                    ng-options="item.value as item.label for item in teamList">
                                </select>
                            </td>
                            <td>
                                <input type="text" ng-model="dataFilter.search['empas.email']" ng-keyup="searchData(1, $event)" 
                                       ng-attr-placeholder="<%= trans['Search'] %>..." class="filter-search form-control" />
                            </td>
                            <td>
                                <select ui-select2="{ minimumResultsForSearch: -1 }" ng-change="searchData(2)" 
                                        ng-model="dataFilter.search['projs.tag_status']" class="filter-search form-control"
                                        ng-options="key as value for (key, value) in projTagStatuses">
                                    <option value="">&nbsp;</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" ng-model="dataFilter.search['tg_s.value']" ng-keyup="searchData(1, $event)" 
                                       ng-attr-placeholder="<%= trans['Search'] %>..." class="filter-search form-control" />
                            </td>
                            <td></td>
                        </tr>
                        <tr ng-if="collection" ng-repeat="(key, item) in collection" 
                            data-id="<%= item.id %>" ng-class="{'bg-yellow': item.loading}"
                            ng-init="renderProjTooltip(item)">
                            <td ng-if="globTag.permissSubmit || globTag.permissApprove">
                                <input type="checkbox" ng-click="checkItem(item.id, $event)" value="<%= item.id %>" class="_check_item">
                            </td>
                            <td ng-bind="(key + 1 + (pager.page - 1) * pager.limit)"></td>
                            <td ng-if="!funcIsProjOld(item.proj_status)" class="tooltip-proj-tag-search" data-id="<%= item.id %>">
                                <a href="<%= globTag.urlOrgProjEdit + '/' + item.id %>" target="_blank" title="<%= item.name %>" 
                                   ng-bind="trimObjNames(item.name, 30, ' ')"></a>
                            </td>
                            <td ng-if="funcIsProjOld(item.proj_status)" class="tooltip-proj-tag-search" data-id="<%= item.id %>">
                                <span title="<%= item.name %>" ng-bind="trimObjNames(item.name, 30, ' ')"></span>
                                <div><span class="label-tag-old" ng-bind="trans['Old']"></span></div>
                            </td>
                            <td ng-init="teamIdName = splitIdNameHtml(item.team_idnames, item.leader_id, 18)" 
                                title="<%= teamIdName.text %>" ng-bind-html="teamIdName.html | trustHtml"
                                class="team-str-list" data-leader-id="<%= item.leader_id %>"></td>
                            <td ng-bind="convertAccount(item.assignee_name)"></td>
                            <td class="td-status <%= getClassStatus(item.tag_status) %>" ng-bind="getLabelStatus(item.tag_status)"></td>
                            <td tag-resize data-tagids="<%= item.tag_ids %>" data-tagcount="<%= item.count_tags %>" class="td-tags tags-format-color" ng-bind-html="generateTagHtml(item.tag_names)"></td>
                            <td>
                                <button type="button" class="btn-edit" 
                                        ng-if="globTag.permissViewDetail"
                                        ng-click="formProjectOldEdit($event, item)">
                                        <i class="fa fa-tags" ng-if="!item.loadingModal"></i>
                                        <i class="fa fa-spin fa-refresh" ng-if="item.loadingModal"></i>
                                </button>
                            </td>
                        </tr>
                        
                        <tr ng-hide="dataLoaded">
                            <td colspan="8" class="text-center"><i class="fa fa-spin fa-refresh"></i></td>
                        </tr>
                        <tr ng-if="(!collection || collection.length < 1) && dataLoaded" ng-cloak>
                            <td colspan="8" class="text-center"><h4 ng-bind="trans['Not found item']"></h4></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="box-body">
                <div ng-paginate ctrl="projectController"></div>
            </div>
            
            <div id="modal-edit-tags"></div>
            <div id="modal-edit-assignee"></div>
        </div>
    </div>
    
</div>

<div class="field-manage-modal-wrapper"></div>
<div class='response-notifications top-right'></div>
@endsection


@section('script')
@include('tag::include.translate')
@include('tag::include.ng_template')
<div ng-include="'tag_field_multi_choose.html'"></div>
<script>
    var RKVarGlobalTag = {
        pathAssetTemplate: '{{ URL::asset('asset_tag/template') }}',
        assetVersion: {{ $versionAsset }},
        projectState: JSON.parse('{!! json_encode(TagConst::projectState()) !!}'),
        projStates: JSON.parse('{!! json_encode(Project::lablelState()) !!}'),
        projectReourceType: JSON.parse('{!! json_encode(TagConst::projectTypeResource()) !!}'),
        urlGetProjectDataNormal: '{{ URL::route('tag::object.project.data.normal') }}',
        urlProjectCreateSubmit: '{{ URL::route('tag::object.project.create') }}',
        urlRemoteSearchCustomer: '{{ URL::route('sales::search.ajax.customer') }}',
        urlRemoteSearchEmployee: '{{ URL::route('team::employee.list.search.ajax') }}',
        urlProjectCheckExists: '{{ URL::route('tag::object.project.data.check.exists') }}',
        urlProjectGetDataItem: '{{ URL::route('tag::object.project.get.data.item') }}',
        urlProjectUpdateInput: '{{ URL::route('tag::object.project.update.input') }}',
        
        SHOW_NUM_TAGS: {{ TagConst::NUM_SHOW_TAGS }},
        FIELDS: '{!! json_encode($fieldsPath) !!}',
        SET_FIELD_PROJ: {{ TagConst::SET_TAG_PROJECT }},
        PROJ_STT_APPROVE: {{ TagConst::TAG_STATUS_APPROVE }},
        PROJ_STT_REVIEW: {{ TagConst::TAG_STATUS_REVIEW }},
        PROJ_STT_ASSIGNED: {{ TagConst::TAG_STATUS_ASSIGNED }},
        IS_REVIEW: {{ $isReview }},
        permissSubmit: {{ $permissSubmit }},
        permissApprove: {{ $permissApprove }},
        permissViewDetail: {{ $permissViewDetail }},
        projectIds: '{!! json_encode(request()->get('project_ids')) !!}',
        TEAM_LIST: '{!! json_encode($teamList) !!}',
        ACTION_LIST: '{!! json_encode(TagGeneral::projTagActions()) !!}',
        ACTION_ASSIGN: {{ TagConst::ACTION_ASSIGN }},
        
        urlGetProjectList: '{{ URL::route('tag::object.project.data.list') }}',
        urlSaveProjectTag: '{{ URL::route('tag::object.project.save.tag') }}',
        urlEditProjectTag: '{{ URL::route('tag::object.project.edit.tag') }}',
        urlSuggestProjectTag: '{{ URL::route('tag::object.project.suggest.tags') }}',
        urlOrgProjEdit: '{{ URL::route('project::project.edit', ['id' => '']) }}',
        urlSearchEmployee: '{{ URL::route('team::employee.list.search.ajax') }}',
        urlSaveProjectAssignee: '{{ URL::route('tag::object.project.save.assignee') }}',
        urlSubmitProjectTag: '{{ URL::route('tag::object.project.submit.tag') }}',
        urlApproveProjectTag: '{{ URL::route('tag::object.project.approve.tag') }}',
        urlCountFieldsTag: '{{ URL::route('tag::object.project.data.count_tag') }}',
        urlBulkAction: '{{ URL::route('tag::object.project.action.tag') }}',
        urlAddTag: '{{ URL::route('tag::object.project.add.tag') }}',
        urlDeleteTag: '{{ URL::route('tag::object.project.delete.tag') }}',
        urlGetTagsInfo: '{{ URL::route('tag::object.project.tags.list') }}',
        urlExportTags: '{{ URL::route('tag::storage.export.tags') }}',
        urlGetProjLeaderTeam: '{{ URL::route('tag::search.project.get.leader.team') }}',
        
        limitPages: '{!! json_encode(ViewConfig::toOptionLimit()) !!}',
        projTagStatuses: '{!! json_encode(TagConst::projTagStatus()) !!}',
        tagActionClasses: '{!! json_encode(TagConst::tagActionClasses()) !!}',
        urlProjectSaveMember: '{{ URL::route('tag::object.project.member.save') }}',
        urlProjectGetDataItemMember: '{{ URL::route('tag::object.project.get.data.item.member') }}',
        urlProjectGetScope: '{{ URL::route('tag::object.project.get.scope') }}',
        urlProjMemberDelete: '{{ URL::route('tag::object.project.member.delete') }}',
        urlProjWoDetail: '{{ URL::route('project::project.edit', ['id' => '0']) }}',
        scopeCompany: {{ PermissionModel::SCOPE_COMPANY }},
        scopeTeam: {{ PermissionModel::SCOPE_TEAM }},
        userCurrent: {{ $scope->getEmployee()->id }}
    };
</script>
@include('tag::include.ng-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/mouse0270-bootstrap-notify/3.1.7/bootstrap-notify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://unpkg.com/dexie@latest/dist/dexie.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.js"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/indexed-search.js') }}"></script>
<script src="{{ URL::asset('lib/tag-it/js/tag-it.min.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/general.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/app.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/project.js') }}"></script>
<script>
jQuery(document).ready(function($) {
    RKTagFunction.general.validateRemoteDelay();
})
</script>
@endsection
