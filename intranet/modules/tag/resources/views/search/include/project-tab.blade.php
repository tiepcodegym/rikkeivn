<div class="table-responsive multiselect2-wrapper flag-over-hidden">
    <table class="table table-bordered table-hover dataTable table-middle table-project" id="search_table">
        <thead>
            <tr>
                <th class="sorting width-150" ng-click="doSort('name')" ng-class="classSorting('name')" ng-bind-html="trans['Project name'] | trustHtml"></th>
                <th class="sorting" ng-click="doSort('count_tags')" ng-class="classSorting('count_tags')" style="min-width: 300px;" ng-bind-html="trans['Tags'] | trustHtml"></th>
                <th class="width-40"></th>
            </tr>
        </thead>
        <tbody class="tb-proj-list">
            <tr>
                <td>
                    <input type="text" ng-model="dataFilter.search['t_proj.name']" 
                           ng-keyup="searchData(1, $event)" ng-attr-placeholder="<%= trans['Search'] %>..." 
                           class="filter-search form-control" />
                </td>
                <td></td>
                <td></td>
            </tr>
            <tr ng-if="collection" ng-repeat="(key, item) in collection" data-id="<%= item.id %>"
                ng-class="{'bg-yellow': item.loading}">
                <td ng-init="teamIdName = splitIdNameHtml(item.team_idnames, item.leader_id, 22)"
                    class="team-str-list" data-leader-id="<%= item.leader_id %>">
                    <div ng-init="renderProjTooltip(item)" data-html="true" class="tooltip-proj-tag-search" data-id="<%= item.id %>">
                        <a ng-if="!funcIsProjOld(item.proj_status)" href="<%= globTag.urlOrgProjEdit + '/' + item.id %>"
                           title="<%= item.name %>"
                           target="_blank" ng-bind="trimObjNames(item.name, 30, ' ') | trustHtml"></a>
                        <span ng-if="funcIsProjOld(item.proj_status)" title="<%= item.name %>" ng-bind="trimObjNames(item.name, 30, ' ') | trustHtml"></span><br />
                        <small class="text-desc" ng-bind-html="'' + (item.effort | number:2) + ' mm - ' + teamIdName.html | trustHtml"></small>
                    </div>
                </td>
                <td data-tagids="<%= item.tag_ids %>" data-tagcount="<%= item.count_tags %>" class="td-tags tags-format-color" ng-bind-html="generateTagHtml(item.tag_names)"></td>
                <td>
                    <button type="button" class="btn-edit" 
                            ng-click="formProjectOldEdit($event, item)">
                        <i class="fa fa-eye" ng-if="!item.loadingModal"></i>
                        <i class="fa fa-spin fa-refresh" ng-if="item.loadingModal"></i>
                    </button>
                </td>
            </tr>
            
            <tr ng-hide="dataLoaded">
                <td colspan="3" class="text-center"><i class="fa fa-spin fa-refresh"></i></td>
            </tr>
            <tr ng-if="(!collection || collection.length < 1) && dataLoaded" ng-cloak>
                <td colspan="3" class="text-center"><h4 ng-bind-html="trans['Not found item'] | trustHtml"></h4></td>
            </tr>
        </tbody>
    </table>
</div>

<div ng-paginate ctrl="searchObjectTagController"></div>


