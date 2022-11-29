<?php
use Rikkei\Core\View\View;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Project;
use Rikkei\Resource\View\View as rView;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;

$teamPath = Team::getTeamPathTree();
?>
<!-- FILTER BOX -->
<div class="row">
    <div class="col-md-4">
        <label for="name" class="col-md-4 control-label margin-top-5">{{trans('resource::view.Dashboard.Project')}}</label>
        <div class="col-md-8">
            <span>
                <select id="proj_filter"  style="width:100%" class="form-control width-93 select2-hidden-accessible select-search">
                    <option value="0">{{trans('resource::view.Dashboard.Choose project')}}</option>
                    @foreach($projOptions as $option)
                    <option value="{{ $option->id }}"
                            @if ($filter && $option->id == $filter['projId']) selected @endif
                            >{{ $option->name }}</option>
                    @endforeach
                </select>
            </span>
        </div>
    </div>
    <div class="col-md-4">
        <label for="name" class="col-md-4 control-label margin-top-5">{{trans('resource::view.Dashboard.Project status')}}</label>
        <div class="col-md-8">
            <span>
                <select id="status_filter"  style="width:100%" class="form-control width-93 select2-hidden-accessible select-search">
                    <option value="0">{{trans('resource::view.Dashboard.Choose status')}}</option>
                    @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}"
                            @if ($filter && $value == $filter['projStatus']) selected @endif
                            >{{ $label }}</option>
                    @endforeach
                </select>
            </span>
        </div>
    </div>
    @if (Permission::getInstance()->isScopeCompany() || Permission::getInstance()->isScopeTeam())
        <div class="col-md-4 team-select-box">
            <label for="name" class="col-md-4 control-label margin-top-5">{{trans('resource::view.Dashboard.Group')}}</label>
            <div class="col-md-8">
                <span>
                    <select id="team_id" style="width:100%" class="form-control width-93" multiple name="team_id[]">
                        @foreach($teamsOptionAll as $option)
                            @if(in_array($option['value'], $teamOfPTPM))
                                <option value="{{ $option['value'] }}" class="checkbox-item"
                                        <?php if ((!in_array($option['value'], $teamsOfEmp) && !Permission::getInstance()->isScopeCompany()) || $option['is_soft_dev'] != Team::IS_SOFT_DEVELOPMENT): ?> disabled <?php endif; ?>
                                        @if ($filter && $option['value'] == $filter['teamId']) selected @endif
                            >
                            {{ $option['label'] }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </span>
            </div>
        </div>
    @endif
</div>
<div class="row margin-top-20">
    <div class="col-md-4">
        <label for="start-month" class="col-md-4 control-label margin-top-5">{{trans('resource::view.Date')}}</label>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6">
                    <span>
                        <input type='text' class="form-control date filter-grid" id="start-month" name="filter[except][start_month]" data-provide="datepicker" placeholder="YYYY-MM-DD" tabindex=1 value="{{$startDateFilter}}" />
                    </span>
                </div>
                <div class="col-md-6">
                    <span>
                        <input type='text' class="form-control date filter-grid" id="end-month" name="filter[except][end_month]" data-provide="datepicker" placeholder="YYYY-MM-DD" tabindex=1 value="{{$endDateFilter}}" />
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <label class="col-md-4 control-label margin-top-5">{{ trans('resource::view.Programming lang') }}</label>
        <div class="col-md-8">
            <span>
                <select id="program"  style="width:100%" 
                        class="form-control" multiple="multiple">
                    @foreach ($programs as $program)
                    <option value="{{ $program->name }}"
                         @if (isset($filter['programs']) && is_array($filter['programs']) && in_array($program->name, $filter['programs'])) selected @endif   
                    >{{ $program->name }}</option>
                    @endforeach
                </select>
            </span>
        </div>
    </div>
    @if (Permission::getInstance()->isScopeCompany() || Permission::getInstance()->isScopeTeam())
        <div class="col-md-4">
            <label for="employee" class="col-md-4 control-label margin-top-5">{{trans('resource::view.Dashboard.Employee')}}</label>
            <div class="col-md-8">
                <span>
                    <input type="text" class="form-control" id="employee-autocomplete" value="{{isset($filter['empText']) ? $filter['empText'] : '' }}"/>
                    <input type="hidden" id="emp_id" value="{{isset($filter['empId']) ? $filter['empId'] : '' }}" />
                </span>
            </div>
        </div>
    @endif
</div>
<div class="row margin-top-20">
    <div class="col-md-4">
        <label class="col-md-4 control-label margin-top-5">{{ trans('resource::view.View mode') }}</label>
        <div class="col-md-8">
            <span>
                <div class="btn-group btn-viewmode">
                    <button type="button" data-value="day" data-selected="{{ $filter['viewMode'] == 'day' ? 'true' : 'false' }}" class="btn btn-default {{ $filter['viewMode'] == 'day' ? 'bg-aqua' : '' }}" style="width: auto !important">{{ trans('resource::view.Day') }}</button>
                    <button type="button" data-value="week" data-selected="{{ $filter['viewMode'] == 'week' ? 'true' : 'false' }}" class="btn btn-default {{ $filter['viewMode'] == 'week' ? 'bg-aqua' : '' }}">{{ trans('resource::view.Week') }}</button>
                    <button type="button" data-value="month" data-selected="{{ $filter['viewMode'] == 'month' ? 'true' : 'false' }}" class="btn btn-default {{ $filter['viewMode'] == 'month' ? 'bg-aqua' : '' }}">{{ trans('resource::view.Dashboard.Month') }}</button>
                </div>
            </span>
        </div>
    </div>
    <div class="col-md-4">
        <label class="col-md-4 control-label margin-top-5">{{trans('resource::view.Effort')}}</label>
        <div class="col-md-8">
            <span>
                <select id="effort_filter"  style="width:100%" class="form-control width-93 select2-hidden-accessible select-search">
                    <option value="0">{{trans('resource::view.Choose effort')}}</option>
                    @foreach(getOptions::getEffortPeriodOptions() as $value => $label)
                    <option value="{{ $value }}"
                            @if ($filter && $value == $filter['effort']) selected @endif
                            >{{ $label }}</option>
                    @endforeach
                </select>
            </span>
        </div>
    </div>
</div>
<!-- ./FILTER BOX --> 
<div class="row">
    <div class="col-sm-12 grid-data-query" >
        <div class="box-body">
            <div class="filter-action">
                <button class="btn btn-success" id="btn-export-utilization">
                    <span>{{ trans('team::view.Export') }} </i></span>
                </button>
                <button class="btn btn-primary btn-reset">
                    <span>{{ trans('team::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                </button>
                <button class="btn btn-primary btn-filter">
                    <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                </button>
            </div>
        </div>
        @include ('resource::dashboard.include.allocation_week')
        <div class="box-body">
            @include('team::include.pager', ['domainTrans' => 'project'])
        </div>
        <form action="{{ route('resource::dashboard.export_utilization') }}" method="post" id="export-utilization" class="no-validate">
            {!! csrf_field() !!}
        </form>
    </div>
</div>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script>
    var teamPath = {!! json_encode($teamPath) !!};
</script>
