<?php
use Rikkei\Team\View\TeamList;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\EmplCvAttrValue;

$teamsOptionAll = TeamList::toOption(null, true, false);

//get table name
$teamTableAs = 'team_table';
$employeeTableAs = 'employees';
$employeeTeamTableAs = 'team_member_table';
$roleTabelAs = 'role_table';
$roleSpecialTabelAs = 'role_special_table';
$employeeWorkTbl = EmployeeWork::getTableName();
$permissExport = Permission::getInstance()->isAllow('team::team.member.export_member');
$tableEmplCvAttrValue = EmplCvAttrValue::getTableName();
?>

<div class="box-body-overview">
    <div class="row">
        <div class="col-md-9">
           <div class="row">
               <div class="form-group col-md-3">
                   <strong>{{ trans('project::me.StartMonth') }}: </strong>&nbsp;&nbsp;&nbsp;
                   <input type="text" id="activity_month_from_overview" name="month"
                          class="form-control form-inline month-picker-overview maxw-165"
                          value="{{ \Carbon\Carbon::now()->startOfyear()->format('Y-m') }}" autocomplete="off">
               </div>
               <div class="form-group col-md-3">
                   <strong>{{ trans('project::me.EndMonth') }}: </strong>&nbsp;&nbsp;&nbsp;
                   <input type="text" id="activity_month_to_overview" name="month"
                          class="form-control form-inline month-picker-overview maxw-165"
                          value="{{ \Carbon\Carbon::now()->format('Y-m') }}" autocomplete="off">
               </div>
               <div class="form-group col-md-3">
                   @if (is_object($teamIdsAvailable))
                       <p>
                           <b>Team:</b>
                           <span id="selected-team"
                                 data-id="{{$teamIdsAvailable->id}}">{{ $teamIdsAvailable->name }}</span>
                       </p>
                   @elseif ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                       <strong>{{ trans('team::view.Choose team') }} : </strong>&nbsp;&nbsp;&nbsp;
                       <select name="team_all" id="select-team-member"
                               class="form-control select-search input-select-team-member form-inline maxw-200"
                               autocomplete="off">
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
               </div>
           </div>
        </div>
    </div>
    <div id="messageBoxOverview"></div>
    <div class="loading-icon-overview">
        <i class="fa fa-spin fa-refresh"></i>
    </div>
    <div class="total-responsive-overview" id="reportWrapper">
        <div class="se-pre-con"></div>
        <div id="dataOverview" class="table-overview-responsive">
        </div>
    </div>
    <div id="graph-container" class="graph-container" style="height: 450px; padding-bottom: 20px;">
        <hr>
        <canvas id="results-graph"></canvas>
    </div>
</div>

@section('script')
    @parent
    <script src="{{ CoreUrl::asset('project/js/operation_overview.js') }}"></script>
@endsection
