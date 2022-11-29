<script type="text/ng-template" id="tag_assignee.html">
    
    <form name="assigneeForm" ng-submit="saveProjAssignee(assigneeForm.$valid, $event)" class="form-horizontal">
        <p class="input-group">
            <label class="input-group-addon"><%= trans['Select assignee'] %></label>
            <select ng-if="assigneeSelect2Opts" ui-select2="assigneeSelect2Opts" name="assignee"
                    ng-model="projAssignee" class="form-control" style="width: 100%;"
                    ng-options="item.id as item.text for item in listAssignees">
            </select>
            <input type="hidden" class="project-id" name="project_id" value="">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-success" ng-disabled="assigneeForm.$invalid || savingAssignee">
                    <i class="fa fa-user fa-user-assignee"></i> <%= trans['Assign'] %> 
                    <i class="fa fa-spin fa-refresh" ng-show="savingAssignee"></i>
                </button>
            </span>
        </p>
        <p ng-show="assigneeForm.assignee.$invalid && !assigneeForm.assignee.$pristine" class="help-block error">
            <%= trans['Please select assignee'] %>
        </p>
    </form>
    
</script>

<!--teamplate field item-->
<script type="text/ng-template" id="field_item.html">
    <a ng-click="getFieldProjTags(id, $event)" ng-class="{'active': id==fieldActiveId}" 
        class="field-item" id="field_<%= id %>"
        href="javascript:void(0)">
            <span class="field-color" ng-if="fields[id].data.color" style="background: <%= fields[id].data.color %>;"></span>
            <span class="field-color" ng-if="!fields[id].data.color"></span>
            <%= fields[id].data.name %>
    </a>
    <ul ng-if="fields[id].child.length > 0">
        <li ng-repeat="id in fields[id].child" ng-include="'field_item.html'"></li>
    </ul>
</script>
<!--<ul> 
    <li ng-repeat="id in fields[globTag.SET_FIELD_PROJ].child" ng-include="'field_item.html'"></li>
</ul>-->
<script type="text/ng-template" id="tag_template.html">
    
    <div class="row tag-field-manage">
        <div class="col-sm-4 col-lg-3 box-content">
            <!--generate field tree-->
            <div ng-if="fields" class="project-field-list">
                <ul ng-bind-html-compile="htmlFieldTree" class="tree-list"></ul>
            </div>
        </div>

        <div class="col-sm-8 col-lg-9">
            <h4 class="box-title" ng-if="fieldSelected">
                <%= trans['Edit tag for'] %>: <%= fieldSelected.data.name %> 
                &nbsp;
                <a ng-if="!fieldNotLeaves && !isSearchPage && !tagReadOnly" href="#" 
                    ng-click="funcTagForMulti($event, fieldActiveId)" title="Choose multi-tag">
                    <i class="fa fa-bars"></i>
                </a>
                <i class="fa fa-spin fa-refresh" ng-show="loadingTags"></i>

                <span ng-if="isSearchPage && editItem.showEditTagBtn" class="pull-right">
                    <a href="<%= globTag.urlProjectTaging %>#show_<%= editItem.id %>" target="_blank" ng-bind="trans['Edit']"></a>
                </span>
            </h4>

            <div ng-show="fieldNotLeaves">
                <p>Field không phải là field lá (field có con)</p>
            </div>

            <div ng-show="!fieldNotLeaves" class="tagit-block">
                <div class="tags-content hidden" ng-repeat="(id, item) in fields" ng-show="id==fieldActiveId">
                    <ul class="proj-tags tag-field-list review-no-margin" 
                        data-id="<%= id %>" data-color="<%= item.data.color %>" data-name="tags[<%= id %>][]"
                        ng-if="listEditTags[id]">
                        <li ng-repeat="tagItem in listEditTags[id]" data-tagid="<%= tagItem.id %>"
                            class="<%= tagActionClass(tagItem.status) %> tagid-<%= tagItem.id %>">
                            <%= tagItem.value %>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

</script>

<script type="text/ng-template" id="tag_field_multi_choose.html">
    <div class="modal fade in" id="modal-tag-field-multi-choose">
        <div class="modal-dialog modal-full-width">
            <div class="modal-content">
                <div class="modal-header" ng-if="tagOfFieldAvai.length">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-add pull-right" ng-click="funcSubmitTagMulti($event, fieldActiveId)">Add
                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div ng-if="tagOfFieldAvai.length">
                        <div class="modal-title checkbox">
                            <label>
                                <input type="checkbox" 
                                    ng-model="checkboxTag.all" ng-value="1" 
                                    ng-change="funcChangeSelectAllTag()" />
                                <strong>Select all</strong>
                            </label>
                        </div>
                        <div class="row">
                            <div class="col-md-3" ng-repeat="tagItem in tagOfFieldAvai">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox"
                                            name="checkbox_tag_field_item" ng-change="funcChangeSelectItemTag(tagItem.id)"
                                            ng-model="checkboxTag.item[tagItem.id]" 
                                            ng-value="tagItem.id" />
                                        <%= tagItem.value %>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div ng-if="!tagOfFieldAvai.length">
                        <p ng-bind="trans['Not found tags']"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                    <button ng-if="tagOfFieldAvai.length" type="button" class="btn-add" ng-click="funcSubmitTagMulti($event, fieldActiveId)">Add
                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</script>