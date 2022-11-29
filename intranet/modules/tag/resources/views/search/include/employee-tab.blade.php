<div ng-controller="employeeController">
    <div class="table-responsive multiselect2-wrapper flag-over-hidden">
        <table class="table table-bordered table-hover dataTable table-middle table-employee">
            <thead>
                <tr>
                    <th class="width-150 sorting" ng-click="doSort('name')" ng-class="classSorting('name')" ng-bind="trans['Name']"></th>
                    <th class="sorting" ng-click="doSort('count_tags')" ng-class="classSorting('count_tags')" ng-bind="trans['Tags']"></th>
                    <th style="width: 150px" ng-bind="trans['Busy in next 3 months']"></th>
                </tr>
            </thead>
            <tbody class="tb-tag-emp-list">
                <tr>
                    <td>
                        <input type="text" ng-model="filterEmployee.search['emp.email']" 
                            ng-keyup="searchData(1, $event)"
                            class="form-control" placeholder="<%= trans['Search email'] %>...">
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr ng-if="employeesList" ng-repeat="(key, item) in employeesList" 
                    data-id="<%= item.id %>" ng-init="funcSetEmployeeBusyRate(item.id)">
                    <td>
                        <span><%= item.name %></span><br />
                        <small class="text-desc">
                            <%= convertAccount(item.email) %> - <%= trimObjNames(item.team_names, 20) %>
                        </small>
                    </td>
                    <td data-tagids="<%= item.tag_ids %>" data-tagcount="<%= item.count_tags %>" class="td-tags tags-format-color" ng-bind-html="generateTagHtml(item.tag_names)"></td>
                    <td>
                        <div ng-if="employeeBusyRate[item.id]">
                            <div class="progressbar tag-effort ui-progressbar ui-corner-all ui-widget ui-widget-content" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100">
                                <div ng-repeat="(busyDate, busyDetail) in employeeBusyRate[item.id]" class="ui-progressbar-value <%= busyDetail.color %>" 
                                    style="width: <%= numberPeriodBusyRate %>%;" data-toggle="tooltip" title="week <%= busyDetail.dateFormat %>: <%= busyDetail.effort %>%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                
                <tr ng-hide="dataLoaded">
                    <td colspan="2" class="text-center"><i class="fa fa-spin fa-refresh"></i></td>
                </tr>
                <tr ng-if="(!employeesList || employeesList.length < 1) && dataLoaded">
                    <td colspan="2" class="text-center"><h4 ng-bind="trans['Not found item']"></h4></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div ng-paginate ctrl="employeeController"></div>
    <div>
        {!!trans('tag::view.Note bar color')!!}
    </div>
</div>

