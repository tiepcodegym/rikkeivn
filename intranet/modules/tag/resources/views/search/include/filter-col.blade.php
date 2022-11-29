<div class="filter-col">
        
    <script type="text/ng-template" id="filter_box.html">
        <h4 class="box-title">
            <label data-toggle="collapse" data-target="#field_collapse_<%= fId %>" 
                ng-class="{'disabled': !tagsOfField[fId] && fieldsPath[fId].child.length < 1}">
                <input ng-if="fieldsPath[fId].child.length < 1" 
                    type="checkbox" id="toggle_field_<%= fId %>"
                    ng-disabled="!tagsOfField[fId]"
                    ng-click="toggleCheckField(fId, $event)"> 
                <%= fieldsPath[fId].data.name %>
            </label>
            <span class="tag-count" ng-if="fieldsPath[fId].child.length < 1"><%= countProjOfField(fId) %></span>
        </h4>

        <div ng-if="fieldsPath[fId].child.length > 0">
            <div ng-repeat="fId in fieldsPath[fId].child" ng-include="'filter_box.html'" class="filter-box"></div>
        </div>

        <div ng-if="fieldsPath[fId].child.length < 1" class="filter-box-content collapse" id="field_collapse_<%= fId %>">
            <ul ng-if="tagsOfField[fId]" class="filter-tags list-unstyled">
                <li ng-repeat="tag in tagsOfField[fId]" 
                    ng-click="toggleFilterTag(tag, fId)" 
                    ng-class="{'active': tagsSelected.indexOf(tag.tag_id + '') > -1}"
                    data-id="<%= tag.tag_id %>">
                    <span class="tag-name"><%= tag.tag_name %></span>
                    <span class="tag-count"><%= tag.tag_count %></span>
                </li>
            </ul>

            <div ng-if="numberTagOfField[fId] > MIN_SEARCH" class="filter-search">
                <select select2-local='{"minimumInputLength":"1","placeholder":"Search"}'
                        class="form-control search-tag-field" 
                        ng-model="searchTagOfField[fId]"
                        local-data="localTagsOfField[fId]"
                        data-excerpt="<%= getCurrentTagOfField(fId) %>"
                        select2-local="1"
                        data-id="<%= fId %>">
                        <option value="">Search</option>
                </select>
            </div>
        </div>
    </script>

    <div class="box box-primary">

        <div class="toggle-filter text-center">
            <a href="javascript:void(0)" ng-click="toggleFilter($event)">
                <span>Toggle filter</span> 
                <i class="fa" ng-class="toggleFilterClose ? 'fa-caret-up' : 'fa-caret-down'"></i>
            </a>
        </div>

        <div class="field-box-list box-body">

            <div ng-if="teamList" class="filter-parent-box">
                <h4 class="box-title">
                    <label><span ng-bind="trans['Basic info']"></span></label>
                </h4>
                
                <div>
                    <div ng-if="teamList" class="filter-box">
                        <h4 class="box-title">
                            <label data-toggle="collapse" data-target="#field_collapse_team">
                                <input type="checkbox" id="toggle_team_checkbox"
                                   ng-click="toggleCheckTeam(team, $event)">
                                <span ng-bind="trans['Team']"></span>
                            </label>
                            <span class="tag-count" ng-bind="collection.length"></span>
                        </h4>
                        <div class="filter-box-content collapse" id="field_collapse_team">
                            <ul class="filter-tags list-unstyled">
                                <li ng-repeat="team in teamList" 
                                    ng-if="!team.disabled"
                                    ng-class="{'active': dataFilter.search['tpj.team_id'].indexOf(team.id) > -1, 'disabled': team.disabled}"
                                    data-id="<%= team.id %>"
                                    ng-click="toggleFilterTeam(team.id)"
                                    ng-show="countProjOfTeam(team.id)">
                                    <span class="tag-name"><%= team.label.trim() %></span>
                                    <span class="tag-count"><%= countProjOfTeam(team.id) %></span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="filter-box">
                        <h4 class="box-title">
                            <label data-toggle="collapse" data-target="#field_collapse_mm">
                                <input type="checkbox" id="toggle_mm_checkbox"
                                   ng-click="toggleCheckMM($event)">
                                <span ng-bind="trans['Man month']"></span>
                            </label>
                            <span class="tag-count" ng-bind="collection.length"></span>
                        </h4>
                        <div class="filter-box-content collapse margin-top-10 slider-filter" id="field_collapse_mm">
                            <div id="slider_mm"></div>
                            <div class="slider-text">
                                <div class="text-start pull-left" ng-bind="filterMMStart"></div>
                                <div class="text-end pull-right" ng-bind="filterMMEnd"></div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div ng-if="fieldsPath" class="fields-path">
                <div ng-repeat="fId in fieldsPath[varGlobalTag.SET_FIELD_PROJ].child" 
                     ng-include="'filter_box.html'" class="filter-parent-box">
                </div>
            </div>
            
        </div>

    </div>
</div>
