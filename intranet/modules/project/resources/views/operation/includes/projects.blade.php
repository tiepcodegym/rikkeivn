<?php
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectAdditional;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\CoreUrl;

$unitPrices = \Rikkei\Project\Model\ProjectApprovedProductionCost::getUnitPrices();
$urlSubmitFilter = GeneralProject::getUrlFilterDb();
$teamPath = Team::getTeamPathTree();
$teamFilter = Form::getFilterData('exception', 'teamId', $urlSubmitFilter);
$teamsOptionAll = TeamList::toOption(null, true, false);
$status = Project::lablelState() + [ProjectAdditional::STATE_FUTURE => 'Future'];
$types = Project::labelTypeProject();
$dataCookie = CookieCore::getRaw('filter.project.projects');
$filterType = [];
$filterState = [];
$pageLimit = 10;
if ($dataCookie) {
    $filterType = isset($dataCookie['project_type']) ? $dataCookie['project_type'] : $filterType;
    $filterState = isset($dataCookie['project_state']) ? $dataCookie['project_state'] : $filterState;
    $pageLimit = isset($dataCookie['page_limit']) ? $dataCookie['page_limit'] : $pageLimit;
}

?>

<div class="box-body">
    <div class="se-pre-con"></div>
    <div class="row">
        <div class="col-md-12" >
           <div class="row" style="display: flex; align-items: flex-end; flex-wrap: wrap;" >
               <div class="form-group col-md-2 col-sm-4">
                   <strong class="display-block">{{ trans('project::me.StartMonth') }}: </strong>
                   <input type="text" id="activity_month_from" name="month"
                          class="form-control form-inline month-picker width-110"
                          value="{{ \Carbon\Carbon::now()->startOfyear()->format('Y-m') }}" autocomplete="off">
               </div>
               <div class="form-group col-md-2 col-sm-4">
                   <strong class="display-block">{{ trans('project::me.EndMonth') }}: </strong>
                   <input type="text" id="activity_month_to" name="month"
                          class="form-control form-inline month-picker width-110"
                          value="{{ \Carbon\Carbon::now()->format('Y-m') }}" autocomplete="off">
               </div>
               <div class="form-group col-md-2 col-sm-4">
                   {{-- show team available --}}
                   @if (is_object($teamIdsAvailable))
                       <p>
                           <b>Team:</b>
                           <span id="selected-team"
                                 data-id="{{$teamIdsAvailable->id}}">{{ $teamIdsAvailable->name }}</span>
                       </p>
                   @elseif ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                       <strong class="display-block">{{ trans('team::view.Choose team') }} : </strong>
                       <select name="team_all" id="select-team-member"
                               class="form-control select-search input-select-team-member form-inline maxw-400"
                               autocomplete="off"  style="width: 400px">
                           {{-- show all member --}}
                           @if ($teamIdsAvailable === true)
                               <option value="" <?php
                               if (!$teamIdCurrent): ?> selected<?php endif;
                               ?><?php
                               if ($teamIdsAvailable !== true): ?> disabled<?php endif;
                                   ?>>&nbsp;
                               </option>
                           @endif
                           {{-- show team available --}}
                           @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                               @foreach($teamsOptionAll as $option)
                                   @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                                       <option value="{{ $option['value'] }}"
                                               <?php if ((!in_array($option['value'], $teamsOfEmp) && !Permission::getInstance()->isScopeCompany()) || $option['is_soft_dev'] != Team::IS_SOFT_DEVELOPMENT): ?> disabled
                                               class="style-disabled" <?php endif; ?>
                                               @if ($option['value'] == $teamIdCurrent) selected @endif>
                                           {{ $option['label'] }}
                                       </option>
                                   @endif
                               @endforeach
                           @endif
                       </select>
                   @endif
                   {{-- end show team available --}}
               </div>
               <div class="form-group col-md-2 col-sm-4">
                   <strong class="display-block">{{ trans('project::view.Status') }}: </strong>
                   <select class="form-control multi-select-bst hidden maxw-200" name="project_state" id="project_state" multiple="multiple">
                       @foreach($status as $key => $value)
                           <option value="{{ $key }}" {{ in_array($key, $filterState) ? 'selected' : '' }}>{{ $value }}</option>
                       @endforeach
                   </select>
               </div>
               <div class="form-group col-md-2 col-sm-4">
                   <strong class="display-block">{{ trans('project::view.Type') }}: </strong>
                   <select class="form-control multi-select-bst hidden maxw-200"
                           name="project_type" id="project_type" multiple="multiple">
                       @foreach($types as $key => $value)
                           <option value="{{ $key }}" {{ in_array($key, $filterType) ? 'selected' : '' }}>{{ $value }}</option>
                       @endforeach
                   </select>
               </div>
               <div class="col-md-2 text-right col-sm-4" style="margin-bottom: 15px;">
                   <a class="btn btn-edit button_tracking" data-toggle="modal" data-target="#taskModal" data-keyboard="false"
                      data-backdrop="static"><span class="glyphicon glyphicon-plus"></span>
                       &nbsp;<span>{{ trans('project::view.Create Project') }}</span></a>
               </div>
           </div>
        </div>

    </div>
    <div class="loading-icon ">
        <i class="fa fa-spin fa-refresh"></i>
    </div>
    <div>
        <div class="table-responsive hidden " id="reportWrapper">
            <table id="tblBatchBody" class="table table-striped dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th  class="col-name cell-no" data-order="no">No</th>
                        <th  class="cell-name head-action" id="jsSortCompanyName">{{ trans('project::view.Company') }}</th>
                        <th  class=" cell-name"
                            data-order="name_team">{{ trans('project::view.Name') }}</th>
                        <th  class=" cell-type" id="jsSortType">{{ trans('project::view.Type') }}</th>
                        <th  class=" type cell-team"
                            data-order="team">{{ trans('project::view.Team') }}</th>
                    </tr>
                    <tr>
                        <th  class="col-name cell-no" data-order="no"></th>
                        <th  class=" cell-name"
                             data-order="name_team">{{trans('project::view.Reward Total')}}</th>
                        <th  class="cell-type" ></th>
                        <th  class="cell-type" ></th>
                        <th  class="total cell-team"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="box-body table-responsive hidden ">
        <div class="grid-pager">
            <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
            </div>
        </div>
    </div>
</div>
@include('project::operation.includes.modal.project-create')
@include('project::operation.includes.modal.project-future')
@section('script')
    @parent
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            selectSearchReload();
            $('#project_type').multiselect({
                numberDisplayed: 2,
                nonSelectedText: '----',
                allSelectedText: '{{ trans('project::view.All') }}',
            });
            $('#project_state').multiselect({
                numberDisplayed: 2,
                nonSelectedText: '----',
                allSelectedText: '{{ trans('project::view.All') }}',
            });
        });
    </script>
    <script>
        var globalTeamModule = {
            teamPath: JSON.parse('{!! json_encode($teamPath) !!}'),
            teamSelected: JSON.parse('{!! json_encode($teamFilter) !!}')
        };
        var globalPostOperationUrl = '{{ route('project::operation.create') }}';
        var globalDeleteOperationUrl = '{{ route('project::operation.delete_operation') }}';
        var globalErrorText = '{{ trans("project::view.An error occurred") }}';
        var globalErrorTimeoutText = '{{ trans("project::view.Request time out") }}';
        var globalErrorOverCost = '{{ trans("project::view.Total approved production cost not matched") }}';
        var globalErrorTotalDetailCost = '{{ trans("project::view.Total approved production cost detail after fill") }}';
        var globalTooltipCost = '{{ trans("project::view.Total current approved production cost") }}';
        var globalId = '';
        var globalMonthFrom = gloabalStartDateFilter;
        var globalMonthTo = gloabalEndDateFilter;
        var globalCurrentMonth = globalCurrentMonth;
        var globalPage, globalCurrentUrl;
        var globalIndex = 1;
        var globalGetPointUpdateUrl = '{{ route('project::operation.update_project_cost') }}';
        var globalGetDataDetailProjectFutureUrl = '{{ route('project::operation.project-future.get') }}';
        var globalProjectState = JSON.parse('{!! json_encode($filterState) !!}');
        var globalProjectType = JSON.parse('{!! json_encode($filterType) !!}');
        var globalPageLimit = {{$pageLimit}};
        var globalUnitPrices = JSON.parse('{!! json_encode($unitPrices) !!}');

    </script>
    <script src="{{ CoreUrl::asset('project/js/operation_project.js') }}"></script>
@endsection
