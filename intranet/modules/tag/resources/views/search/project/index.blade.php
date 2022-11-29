@extends('layouts.default')
<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\View\Config as ViewConfig;
use Rikkei\Tag\View\TagConst;
use Rikkei\Core\Model\CoreConfigData;

$versionAsset = Config::get('view.assets_verson');
$teamList = TeamList::toOption(null, false, null);
?>

@section('title', trans('tag::view.Search'))

@section('after_title')
<div class="ch-input-search">
    <ul class="tag-search" data-tagit-search>
    </ul>
    <a class="icon-search btn-search-tag" href="javascript:void(0)" ng-click="searchData(3, $event, {tagit: '.tag-search[data-tagit-search]'})">
        <i class="fa fa-search"></i>
    </a>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css" />
<link rel="stylesheet" href="{{ URL::asset('lib/tag-it/css/jquery.tagit.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('lib/tag-it/css/tagit.ui-zendesk.min.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_tag/css/styles.css') }}" />
@endsection

@section('body_attrs', 'ng-app="RkTagApp" ng-controller="projectController"')

@section('body_class', 'tagging-search')

@section('content')
<!--<input type="text" ng-model="dataFilter.search['t_proj_tag.tag_name']" 
    ng-keyup="searchData(1, $event)" class="filter-search" />-->
<div class="row-relative" ng-controller="searchObjectTagController">
    
    @include('tag::search.include.filter-col')
    
    <div class="result-col">
        <div id="modal-project-wrapper" ng-bind-html-compile="htmlProjectEditorModal"></div>
        
        <div class="nav-tab-wrapper">
            
            <div class="nt-panel-right">
                <div class="nt-pr-inner">
                    <a href="javascript:void(0)" class="icon-reset" aria-expanded="false"
                        ng-click="resetDataFilter($event)" data-tagit=".tag-search[data-tagit-search]">
                         {{ trans('tag::view.clear search') }}
                    </a>
                </div>
            </div>
            <div class="nav-tabs-search nav-tabs-custom">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="javascript:void(0)" data-target="#project_tab" rold="tab" ng-click="toggleSearchTab('project', $event)" data-toggle="tab">
                            {{ trans('tag::view.Project') }} <span ng-if="totalProject" ng-bind-html="'(' + totalProject + ')' | trustHtml"></span>
                        </a>
                    </li>
                    <li role="presentation" ng-show="dataLoaded">
                        <a href="javascript:void(0)" data-target="#employee_tab" role="tab" ng-click="toggleSearchTab('employee', $event)" data-toggle="tab">
                            {{ trans('tag::view.Employee') }} <span ng-if="totalEmployee" ng-bind-html="'(' + totalEmployee + ')' | trustHtml"></span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="project_tab">
                        @include('tag::search.include.project-tab')
                    </div>
                    <div role="tabpanel" class="tab-pane" id="employee_tab">
                        @include('tag::search.include.employee-tab')
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection


@section('script')
@include('tag::include.translate')
@include('tag::include.ng_template')

<script>
    var RKVarGlobalTag = {
        pathAssetTemplate: '{{ URL::asset('asset_tag/template') }}',
        assetVersion: {{ $versionAsset }},
        projectState: JSON.parse('{!! json_encode(TagConst::projectState()) !!}'),
        projectReourceType: JSON.parse('{!! json_encode(TagConst::projectTypeResource()) !!}'),
        projTagStatuses: '{!! json_encode(TagConst::projTagStatus()) !!}',
        tagActionClasses: '{!! json_encode(TagConst::tagActionClasses()) !!}',
        projTagStatuses: '{!! json_encode(TagConst::projTagStatus()) !!}',
        SET_FIELD_PROJ: {{ TagConst::SET_TAG_PROJECT }},
        IS_SEARCH: 1,
        PROJ_STT_APPROVE: {{ TagConst::TAG_STATUS_APPROVE }},
        PROJ_STT_REVIEW: {{ TagConst::TAG_STATUS_REVIEW }},
        PROJ_STT_ASSIGNED: {{ TagConst::TAG_STATUS_ASSIGNED }},
        
        urlGetProjectList: '{{ URL::route('tag::search.project.get.most.tag') }}',
        urlGetFieldsPath: '{{ URL::route('tag::search.project.get.data.normal') }}',
        urlGetProjectDataNormal: '{{ URL::route('tag::object.project.data.normal') }}',
        urlProjectGetDataItem: '{{ URL::route('tag::object.project.get.data.item') }}',
        urlProjWoDetail: '{{ URL::route('project::project.edit', ['id' => '0']) }}',
        urlCountFieldsTag: '{{ URL::route('tag::object.project.data.count_tag') }}',
        urlProjectGetDataItemMember: '{{ URL::route('tag::object.project.get.data.item.member') }}',
        urlProjectGetScope: '{{ URL::route('tag::object.project.get.scope') }}',
        urlEditProjectTag: '{{ URL::route('tag::object.project.edit.tag') }}',
        urlGetMoreTag: '{{ URL::route('tag::search.project.get.tags.more') }}',
        urlOrgProjEdit: '{{ URL::route('project::project.edit', ['id' => '']) }}',
        urlGetTagsInfo: '{{ URL::route('tag::object.project.tags.list') }}',
        urlProjectTaging: '{{ URL::route('tag::object.project.index') }}',
        urlGetEmployeeList: '{{ URL::route('tag::search.project.get.data.employees') }}',
        urlSearchTag: '{{ URL::route('tag::search.project.get.search.tag') }}',
        urlExportTags: '{{ URL::route('tag::storage.export.tags') }}',
        urlGetProjLeaderTeam: '{{ URL::route('tag::search.project.get.leader.team') }}',
        urlGetEmployeeBusyRate: '{{ URL::route('tag::search.project.get.employee.busy.rate') }}',
        
        SHOW_NUM_TAGS: {{ TagConst::NUM_SHOW_TAGS }},
        limitPages: '{!! json_encode(ViewConfig::toOptionLimit()) !!}',
        labelProjectTypes: '{!! json_encode($projectTypes) !!}',
        holiday: {
            special: JSON.parse('{!! json_encode(CoreConfigData::getSpecialHolidays(2)) !!}'),
            annual: JSON.parse('{!! json_encode(CoreConfigData::getAnnualHolidays(2)) !!}'),
            weekend: JSON.parse('{!! json_encode(CoreConfigData::get('project.weekend')) !!}'),
        }
        
    };
</script>
@include('tag::include.ng-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://unpkg.com/dexie@latest/dist/dexie.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mouse0270-bootstrap-notify/3.1.7/bootstrap-notify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.js"></script>
<script src="{{ URL::asset('lib/jslinq-2.10/jslinq.min.js') }}"></script>
<script src="{{ URL::asset('lib/tag-it/js/tag-it.min.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/indexed-search.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/general.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/app.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/project.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/search.js') }}"></script>
@endsection

