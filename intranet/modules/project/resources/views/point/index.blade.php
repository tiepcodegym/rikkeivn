<?php
use Rikkei\Project\Model\Project;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;

$teamsOptionAll = TeamList::toOption(null, true, false, null, false);
$teamFilter = Form::getFilterData('except','team_charge_id', $urlSubmitFilter);
?>
<tr class="filter-input-grid">
    <div class="col-sm-12">
        <div class="form-group row col-sm-4">
            <label for="" class="col-sm-12 col-lg-3 col-form-label">{{ trans('project::view.Name') }}</label>
            <div class="col-sm-12 col-lg-9">
                <input type="text" name="filter[{{ $tableProject }}.name]" value="{{ Form::getFilterData("{$tableProject}.name", null, $urlSubmitFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
            </div>
        </div>
        <div class="form-group row col-sm-4">
            <label for="" class="col-sm-12 col-lg-3 col-form-label">{{ trans('project::view.Team_in_charge') }}</label>
            <div class="col-sm-12 col-lg-9">
                <select name="filter[except][team_charge_id]" class="form-control select-grid filter-grid select-search">
                    <option value="">&nbsp;</option>
                    @foreach($teamsOptionAll as $option)
                        <option value="{{ $option['value'] }}"<?php
                        if ($option['value'] == $teamFilter): ?> selected<?php endif;
                            ?>>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- <div class="form-group row col-sm-4">
            <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Team joined') }}</label>
            <div class="col-sm-9">
                <select name="filter[exception][team_id]" class="form-control select-grid filter-grid select-search has-search" data-team="dev">
                    <option value="">&nbsp;</option>
                </select>
            </div>
        </div> --}}
        <div class="form-group row col-sm-4">
            <label for="" class="col-sm-12 col-lg-3 col-form-label">{{ trans('project::view.Team joined') }}</label>
            <div class="col-sm-12 col-lg-9">
                <div class="filter-mobile-left">
                    <div class="list-team-select-box">
                        <div class="input-box filter-multi-select multi-select-style btn-select-team">
                            <?php
                                $filterTeamIds = (array) Form::getFilterData('except', "team_ids", $urlSubmitFilter);
                            ?>
                            <select name="filter[except][team_ids][]" id="select-team-member" multiple
                                    class="form-control filter-grid multi-select-bst select-multi" autocomplete="off">
                                @foreach($teamsOptionAll as $option)
                                    <option value="{{ $option['value'] }}" class="checkbox-item"
                                            {{ in_array($option['value'], $filterTeamIds) ? 'selected' : '' }}>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="form-group row col-sm-4">
            <label for="" class="col-sm-12 col-lg-3 col-form-label">{{ trans('project::view.PM') }}</label>
            <div class="col-sm-12 col-lg-9">
                <input type="text" name="filter[{{ $tableEmployee }}.email]" value="{{ Form::getFilterData("{$tableEmployee}.email", null, $urlSubmitFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
            </div>
        </div>
        <div class="form-group row col-sm-4">
            <label for="" class="col-sm-12 col-lg-3 col-form-label">{{ trans('project::view.Status') }}</label>
            <input type="hidden" name="filter[exception][proj_type]" value="1" class="filter-grid"/>
            <div class="col-sm-12 col-lg-9 filter-multi-select">
                <?php
                $filterStates = (array) Form::getFilterData('exception', "{$tableProject}.state", $urlSubmitFilter);
                if (!$filterStates) {
                    $filterStates = Project::getStateSFilterDefault();
                }
                ?>
                <select class="form-control filter-grid" name="filter[exception][{{ $tableProject }}.state][]" id="state" multiple="multiple">
                    @foreach($status as $key => $value)
                        <option value="{{ $key }}" {{ in_array($key, $filterStates) ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row col-sm-4">
            <label for="" class="col-sm-12 col-lg-3 col-form-label">{{ trans('project::view.Type') }}</label>
            <div class="col-sm-12 col-lg-9 filter-multi-select">
                <?php $filterType = (array) Form::getFilterData('in', "{$tableProject}.type", $urlSubmitFilter);?>
                <select class="form-control multi-select-bst filter-grid hidden"
                        name="filter[in][{{ $tableProject }}.type][]" id="type" multiple="multiple">
                    @foreach($types as $key => $value)
                        <option value="{{ $key }}" {{ in_array($key, $filterType) ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="form-group row col-sm-4">
            <label for="" class="col-sm-12 col-lg-3 col-form-label">{{ trans('project::view.Raised') }}</label>
            <div class="col-sm-12 col-lg-9 select2-np">
                <select name="filter[number][raise]" class="form-control select-grid filter-grid select-search">
                    <?php
                    $filterRaise = Form::getFilterData('number', 'raise', $urlSubmitFilter);
                    ?>
                    @foreach($optionYesNo as $key => $value)
                        <option value="{{ $key }}"<?php
                        if ($key == $filterRaise): ?> selected<?php endif;
                            ?>>{!! $value !!}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row col-sm-4">
            <label for="" class="col-sm-12 col-lg-3 col-form-label">{{ trans('project::view.Is important') }}</label>
            <div class="col-sm-12 col-lg-9">
                <select name="filter[number][{{ $tableProject }}.is_important]" class="filter-grid form-control select-search select-grid">
                    <option value="">&nbsp;</option>
                    <option value="{{ Project::IS_IMPORTANT }}" {{ !empty($isImportantFilter) && Project::IS_IMPORTANT == $isImportantFilter ? 'selected' : '' }}>{{ trans('project::view.Yes') }}</option>
                    <option value="0" {{ !is_null($isImportantFilter) && 0 == $isImportantFilter ? 'selected' : '' }}>{{ trans('project::view.No') }}</option>
                </select>
            </div>
        </div>
        <div class="form-group row col-sm-4">
            <label for="" class="col-sm-12 col-lg-3 col-form-label">{{ trans('project::view.Company') }}</label>
            <div class="col-sm-12 col-lg-9">
                <input type="text" name="filter[except][{{$tableCompany}}.company]" value="{{ Form::getFilterData('except', "{$tableCompany}.company", $urlSubmitFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
            </div>
        </div>
    </div>
    <?php /*<td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tableCompany }}.company]" value="{{ Form::getFilterData("{$tableCompany}.company", null, $urlSubmitFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>*/ ?>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>

    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
    <div class="table-responsive">
        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th dataTable-project">
            <thead>
            <tr>
                <th style="width: 40px;">
                    {!! Permission::getInstance()->isAllow('project::project.export') ? "<input type='checkbox' value='' class='checkAll' name='epd[]' id='checkAll'>" : '' !!}
                </th>
                <th class=""  style="width: 30px;">{{ trans('project::view.Is important') }}</th>
                <th class="sorting {{ Config::getDirClass('name', $urlSubmitFilter) }} col-name width-110" data-order="name" data-dir="{{ Config::getDirOrder('name', $urlSubmitFilter) }}">{{ trans('project::view.Name') }}</th>
                <th class=""  style="width: 120px;">{{ trans('project::view.Team_in_charge') }}</th>
                <th class=""  style="width: 120px;">{{ trans('project::view.Team') }}</th>
                <th style="width: 120px;" class="sorting {{ Config::getDirClass('email', $urlSubmitFilter) }} col-email" data-order="email" data-dir="{{ Config::getDirOrder('email', $urlSubmitFilter) }}">{{ trans('project::view.PM') }}</th>
                <?php /*<th class="sorting {{ Config::getDirClass('company_name', $urlSubmitFilter) }} col-email width-110" data-order="company_name" data-dir="{{ Config::getDirOrder('company_name', $urlSubmitFilter) }}">Company</th>
                            <th class="sorting {{ Config::getDirClass('summary', $urlSubmitFilter) }} col-summary width-110" data-order="summary" data-dir="{{ Config::getDirOrder('summary', $urlSubmitFilter) }}">{{ trans('project::view.Summary') }}</th>*/ ?>
                <th class="sorting {{ Config::getDirClass('cost', $urlSubmitFilter) }} width-90" data-order="cost" data-dir="{{ Config::getDirOrder('cost', $urlSubmitFilter) }}">{{ trans('project::view.Cost') }}</th>
                <th class="sorting {{ Config::getDirClass('quality', $urlSubmitFilter) }} width-90" data-order="quality" data-dir="{{ Config::getDirOrder('quality', $urlSubmitFilter) }}">{{ trans('project::view.Quality') }}</th>
                <th class="sorting {{ Config::getDirClass('tl', $urlSubmitFilter) }} width-110" data-order="tl" data-dir="{{ Config::getDirOrder('tl', $urlSubmitFilter) }}">{{ trans('project::view.Timeliness') }}</th>
                <th class="sorting {{ Config::getDirClass('proc', $urlSubmitFilter) }} width-90" data-order="proc" data-dir="{{ Config::getDirOrder('proc', $urlSubmitFilter) }}">{{ trans('project::view.Process') }}</th>
                <th class="sorting {{ Config::getDirClass('css', $urlSubmitFilter) }} width-90" data-order="css" data-dir="{{ Config::getDirOrder('css', $urlSubmitFilter) }}">{{ trans('project::view.Css') }}</th>
                <th class="sorting {{ Config::getDirClass('point_total', $urlSubmitFilter) }} col-point width-90" data-order="point_total" data-dir="{{ Config::getDirOrder('point_total', $urlSubmitFilter) }}">{{ trans('project::view.Point') }}</th>
                <th class="col-status width-90">{{ trans('project::view.Status') }}</th>
                <th class="col-status width-90">{{ trans('project::view.Type') }}</th>
                <th class="box-title"> {{ trans('project::view.Watch') }} <i class="fa fa-info-circle" title="{{ trans('project::view.Add or remove project from the watch list') }}"></i></th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>

            @if(isset($collectionModel) && count($collectionModel))
                <?php
                if (!$viewBaseline) {
                    $routeView = 'project::point.edit';
                } else {
                    $routeView = 'project::point.baseline.detail';
                }
                $projs = new Project();
                ?>
                @foreach($collectionModel as $item)
                    <?php
                    $itemRaise = $item->raise;
                    if (($viewBaseline && !$item->first_report && !$projs->isTypeTrainingOfRD($item->type))) {
                        $colorWhite = 1;
                    } else {
                        $colorWhite = 0;
                    }
                    ?>
                    <tr>
                        <td>
                            <input type="checkbox" value="{{ $item->id }}" class="export-project-dashboard checkbox-item" name="epd[]">
                        @if ($item->raise == ProjectPoint::RAISE_UP)
                            @if ($isAllowRaise)
                                @if ($viewBaseline)
                                    <!-- view baseline -->
                                        <span class="dashboard-raise-flag cursor-pointer delete-confirm post-ajax raise-content-tooltip"
                                              data-url-ajax="{{ route('project::dashboard.raise.destroy.baseline', ['id' => $item->id]) }}"
                                              data-noti="{{ trans('project::view.Are you sure to destroy raise?') }}"
                                              data-callback-success="raiseDestroyAfterSuccess"
                                              data-raise-note="{{ $item->raise_note  }}"><i class="fa fa-flag-o"></i></span>
                                @else
                                    <!-- view dashboard -->
                                        <span class="dashboard-raise-flag cursor-pointer delete-confirm post-ajax raise-content-tooltip"
                                              data-url-ajax="{{ route('project::dashboard.raise.destroy', ['id' => $item->id]) }}"
                                              data-noti="{{ trans('project::view.Are you sure to destroy raise?') }}"
                                              data-callback-success="raiseDestroyAfterSuccess"
                                              data-raise-note="{{ $item->raise_note  }}"><i class="fa fa-flag-o"></i></span>
                                    @endif
                                @else
                                    <span class="dashboard-raise-flag raise-content-tooltip" data-raise-note="{{ $item->raise_note  }}"><i class="fa fa-flag-o"></i></span>
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
                            @if (!empty($item->is_important))
                                <i class="fa fa-fw fa-bookmark-o dashboard-is-important"></i>
                            @endif
                        </td>
                        <td class="break-word">
                            <a href="{{ route($routeView, ['id' => $item->id ]) }}">{{ $item->name }}</a>
                        </td>
                        <td class="col-hover-tooltip">{{ $item->team_charge }}</td>
                        <td class="col-hover-tooltip">{{ $item->name_team }}
                            <div class="hidden">
                                <div class="tooltip-content">
                                    <p>
                                        {{ trans('project::view.Customer') . ': '. $item->customer_name }}
                                        @if ($item->customer_name_jp)
                                            ({{ $item->customer_name_jp }})
                                        @endif
                                    </p>
                                    <p>
                                        {{ trans('project::view.Company') . ': ' . $item->company_name }}
                                        @if ($item->company_name_ja)
                                            ({{ $item->company_name_ja }})
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="text-uppercase break-word" style="min-width: 150px;">{{ preg_replace('/@.*/', '',$item->email) }}</td>
                        <?php /*<td class="break-word">
                                        {{ $item->company_name }}
                                    </td>
                                    <td class="align-center middle tr-td-not-click task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE }}">
                                        <span class="point-color summary-point">
                                            <img src="{{ $allColorStatus[$item->summary] }}" />
                                        </span>
                                    </td>*/ ?>
                        <td class="align-center middle task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_COST }}">
                                        <span class="point-color cost-point">
                                            @if ($colorWhite)
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, 0, Project::TYPE_RD)}}" />
                                            @else
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, $item->cost, $item->type)}}" />
                                            @endif
                                        </span>
                        </td>
                        <td class="align-center middle task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_QUA }}">
                                        <span class="point-color quality-point">
                                            @if ($colorWhite)
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, 0, Project::TYPE_RD)}}" />
                                            @else
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, $item->quality, $item->type)}}" />
                                            @endif
                                        </span>
                        </td>
                        <td class="align-center middle task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_TL }}">
                                        <span class="point-color timeliness-point">
                                            @if ($colorWhite)
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, 0, Project::TYPE_RD)}}" />
                                            @else
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, $item->tl, $item->type)}}" />
                                            @endif
                                        </span>
                        </td>
                        <td class="align-center middle task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_PROC }}">
                                        <span class="point-color process-point">
                                            @if ($colorWhite)
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, 0, Project::TYPE_RD)}}" />
                                            @else
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, $item->proc, $item->type)}}" />
                                            @endif
                                        </span>
                        </td>
                        <td class="align-center middle task-tooltip cursor-pointer" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_CSS}}">
                                        <span class="point-color css-point">
                                            @if ($colorWhite)
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, 0, Project::TYPE_RD)}}" />
                                            @else
                                                <img src="{{ ViewProject::generateColorStatus($allColorStatus, $item->css, $item->type)}}" />
                                            @endif
                                        </span>
                        </td>
                        <td class="align-center task-tooltip" data-id="{{ $item->project_id }}" data-type="{{ Task::TYPE_ISSUE_SUMMARY }}">
                            @if($item->type == Project::TYPE_TRAINING)
                                {{Project::POINT_PROJECT_TYPE_TRANING}}
                            @elseif($item->type == Project::TYPE_RD)
                                {{Project::POINT_PROJECT_TYPE_RD}}
                            @elseif($item->type == Project::TYPE_ONSITE)
                                {{Project::POINT_PROJECT_TYPE_ONSITE}}
                            @else
                                {{ $item->point_total }}
                            @endif
                        </td>
                        <td>{{ $item->getStateLabel() }}</td>
                        <td>{{ $item->getTypeLabel() }}</td>
                        <td><input class="toggle-trigger btn-change-status" type="checkbox" value="{{ $item->id }}" {{ in_array($item->id, $listMyTracking) ? 'checked' : '' }}
                                   data-toggle="toggle" data-on="Added" data-off="Removed"></td>
                        <td class="align-center white-space-nowrap">
                            <a href="{{ route('project::project.edit', ['id' => $item->project_id ]) }}" class="btn-edit" title="{{ trans('project::view.Workorder') }}" data-toggle="tooltip">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="{{ route('project::task.index', ['id' => $item->project_id ]) }}" class="btn-add" title="{{ trans('project::view.Task') }}" data-toggle="tooltip">
                                <i class="fa fa-tasks"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="13" class="text-center">
                        <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
    <div class="box-body">
        @include('team::include.pager', ['domainTrans' => 'project', 'urlSubmitFilter' => $urlSubmitFilter])
    </div>
    <div class="box-body">
        @if (!$viewBaseline)
            {{--                    <button type="button" class="btn-add export-employees" disabled--}}
            {{--                        data-url="{{ URL::route('project::dashboard.export') }}">{{ trans('project::view.Export') }}</button>--}}
            <button type="button" class="btn-add raise-submit" disabled
                    data-url="{{ URL::route('project::dashboard.raise') }}">{{ trans('project::view.Raise') }}
                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
        @else
            {{--                   <button type="button" class="btn-add export-employees" disabled--}}
            {{--                        data-url="{{ URL::route('project::dashboard.export.baseline') }}">{{ trans('project::view.Export') }}</button>--}}
            @if ($isAllowRaise)
                <button type="button" class="btn-add raise-submit" disabled
                        data-url="{{ URL::route('project::dashboard.raise.baseline') }}">{{ trans('project::view.Raise') }}
                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
            @endif
        @endif
    </div>
</div>
</div>
</div>
</div>
