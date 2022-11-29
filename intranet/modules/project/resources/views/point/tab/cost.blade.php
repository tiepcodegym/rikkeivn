<?php
use Rikkei\Core\View\View;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\ProjectProgramLang;
use Rikkei\Project\View\ProjDbHelp;
use Carbon\Carbon;

//init object viewProject
$viewProject = new ViewProject();
$colors = $viewProject->getColorByValue();
$costEEValue = $viewProject->getCostEEColor($projectPointInformation['cost_effort_efficiency2'], $projectPointInformation['cost_effort_efficiency_target'], $projectPointInformation['cost_effort_efficiency_lcl'], $checkCate);
$correctionCostColor = $viewProject->getCorrectionCostColor($projectPointInformation['correction_cost'], $projectPointInformation['correct_cost_target'], $projectPointInformation['correct_cost_ucl'], $projectPointInformation['correct_cost_lcl'], $checkCate);

$isTypeTrainingOrRD = $project->isTypeTrainingOfRD();
$programLang = ProjectProgramLang::getProgramLangOfProject($project);
$now = Carbon::now();
?>
<div class="row">
    <div class="col-md-12">
        <div class="row">
            <p class="col-md-6">
            </p>
            @if(!$viewBaseline && (!(isset($export)) || !$export))
                <p class="col-md-6">
                    <span class="desktop-right">
                        <a href="#" class="link-dw-convert">{{ trans('project::view.Number of working days in :month', ['month' => $now->format('m')]) }}</a>: <strong>{{ ProjDbHelp::getWorkDay($now) }}</strong>
                    </span>
                </p>
            @endif
        </div>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data table-point">
        <tbody>
        <tr>
            <td class="align-center col-title">
                <strong>{{ trans('project::view.Project Information') }}</strong>
            </td>
            <td class="align-center col-lcl">{{ trans('project::view.LCL') }}</td>
            <td class="align-center col-lcl">{{ trans('project::view.Target') }}</td>
            <td class="align-center col-lcl">{{ trans('project::view.UCL') }}</td>
            <td class="align-center col-value">{{ trans('project::view.Value') }}</td>
            @if(!$viewBaseline || $viewNote)
                <td class="align-center col-note">
                    {!! $textHtmlNoteHead !!}
                </td>
                @endif
            </tr>
        <tr>
            <td class="align-left" title="{{ trans('project::view.Formula Billable Effort') }}">{{ trans('project::view.Billable Effort') }} ({{ $project->getLabelTypeMM() }})</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="align-right">
                @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                @else
                    <span title="{{ trans('project::view.Formula Billable Effort') }}"
                          data-toggle="tooltip" class="pp-value cost_billable_effort">&nbsp;
                        {{ $projectPointInformation['cost_billable_effort'] }}
                    </span>
                @endif
            </td>
            @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->cost_billable_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->cost_billable_note) !!}
                    @else
                        <div class="text-display ">
                            <textarea name="cost_billable_note" class="note-input form-control"
                                rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->cost_billable_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="cost_billable_note" class="note-input form-control"
                                    rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                    endif; ?>>{{ $projectPoint->cost_billable_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'cost_billable_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
                @endif
            </tr>
        <tr>
            <td class="align-left" title="{{ trans('project::view.Formula calendar total') }}">{{ trans('project::view.Resource allocation - total') }} ({{ $project->getLabelTypeMM() }})</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="align-right">
                @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                @else
                    <span title="{{ trans('project::view.Formula calendar total') }}"
                          data-toggle="tooltip" class="pp-value cost_resource_allocation_total">&nbsp;
                        {{ $projectPointInformation['cost_resource_allocation_total'] }}
                    </span>
                @endif
            </td>
            @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->cost_resource_total_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->cost_resource_total_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="cost_resource_total_note" class="note-input form-control"
                                rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->cost_resource_total_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="cost_resource_total_note" class="note-input form-control"
                                rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->cost_resource_total_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'cost_resource_total_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
                @endif
            </tr>
        <tr>
            <td class="align-left" title="{{ trans('project::view.Formula calendar current') }}">{{ trans('project::view.Calendar Effort - current') }} ({{ $project->getLabelTypeMM() }})</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="align-right">
                @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                @else
                    <span title="{{ trans('project::view.Formula calendar current') }}"
                          data-toggle="tooltip" class="pp-value cost_resource_allocation_current">
                        &nbsp;{{ $projectPointInformation['cost_resource_allocation_current'] }}
                    </span>
                @endif
            </td>
            @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->cost_resource_current_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->cost_resource_current_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="cost_resource_current_note" class="note-input form-control"
                                    rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                    endif; ?>>{{ $projectPoint->cost_resource_current_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="cost_resource_current_note" class="note-input form-control"
                                rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->cost_resource_current_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'cost_resource_current_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
                @endif
            </tr>
        <tr @if($projectPointInformation['cost'] === 3 || $projectPointInformation['cost'] === 2) class='pp-bg-cost' @endif>
            <td class="align-left" title="{{ trans('project::view.Formula Actual Effort') }}">{{ trans('project::view.Actual Effort') }} ({{ $project->getLabelTypeMM() }})</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="align-right">
                @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                @else
                    <span title="{{ trans('project::view.Formula Actual Effort') }}"
                          data-toggle="tooltip" class="pp-value cost_resource_allocation_current">
                        &nbsp;{{ $projectPointInformation['cost_actual_effort'] }}
                    </span>
                @endif
            </td>
            @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->cost_actual_effort_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->cost_actual_effort_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="cost_actual_effort_note" class="note-input form-control"
                                    rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                    endif; ?>>{{ $projectPoint->cost_actual_effort_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="cost_actual_effort_note" class="note-input form-control"
                                rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->cost_actual_effort_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'cost_actual_effort_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
                @endif
            </tr>

        <tr>
            <td class="align-left" title="{{ trans('project::view.Formula Approved production cost') }}">{{ trans('project::view.Approved production cost') }} ({{ $project->getLabelTypeMM() }})</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="align-right">
                @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                @else
                    <span title="{{ trans('project::view.Formula Approved production cost') }}"
                           data-toggle="tooltip" class="pp-value cost_approved_production">&nbsp;
                        {{ $projectPointInformation['cost_approved_production'] }}
                    </span>
                @endif
            </td>
            @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->cost_approved_production_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->cost_approved_production_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="cost_approved_production_note" class="note-input form-control"
                                    rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                    endif; ?>>{{ $projectPoint->cost_approved_production_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="cost_approved_production_note" class="note-input form-control"
                                rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->cost_approved_production_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'cost_approved_production_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
                @endif
            </tr>
            <tr>
                <td class="align-left" title="{{ trans('project::view.Formula OT') }}">{{ trans('project::view.OT (MM)') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="align-right">
                    <span title="{{ trans('project::view.Formula OT') }}"
                      data-toggle="tooltip" class="pp-value cost_approved_production">&nbsp;
                        {{ $otTime }}
                    </span></td>
                @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->ot_time_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="ot_time_note" class="note-input form-control"
                                    rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                    endif; ?>>{{ $projectPoint->ot_time_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="ot_time_note" class="note-input form-control"
                                rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->ot_time_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'ot_time_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
                @endif
            </tr>

            <tr class="">
                <td class="align-left" title="{{ trans('project::view.Formula EE') }}">{{ trans('project::view.Effort Efficiency') }} (%)</td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['cost_effort_efficiency_lcl'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        {{ $projectPointInformation['cost_effort_efficiency_target'] }}
                    @endif
                </td>
                    <td class="align-right">
                        @if($isTypeTrainingOrRD || !$checkCate)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        {{ $projectPointInformation['cost_effort_efficiency_ucl'] }}
                    @endif
                </td>
                <td class="align-right" bgcolor="{{ $colors[$costEEValue] }}">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span title="{{ trans('project::view.Formula EE') }}"
                              data-toggle="tooltip" class="pp-value cost_effort_efficiency2">&nbsp;
                        {{ $projectPointInformation['cost_effort_efficiency2'] }}
                    </span>
                    @endif
                </td>
            @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->cost_eey2_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->cost_eey2_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="cost_eey2_note" class="note-input form-control"
                                rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->cost_eey2_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="cost_eey2_note" class="note-input form-control"
                                rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->cost_eey2_note }}</textarea>
                        </div>
                    @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'cost_eey2_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
            @endif
        </tr>

        <tr class="">
            <td class="align-left css-point css_css-value" title="{{ trans('project::view.Formula correction') }}">{{ trans('project::view.Correction Cost') }} (%)</td>
            <td class="align-right">
                @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                @else
                    {{ $projectPointInformation['correct_cost_lcl'] }}
                @endif
            </td>
            <td class="align-right">
                @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                @else
                    {{ $projectPointInformation['correct_cost_target'] }}
                @endif
            </td>
            <td class="align-right">
                @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                @else
                    <span title="{{ trans('project::view.Formula correction') }}"
                          data-toggle="tooltip" class="pp-value cost_effort_efficiency2">&nbsp;
                        {{ $projectPointInformation['correct_cost_ucl'] }}
                    </span>
                @endif
            </td>
            <td class="align-right" bgcolor="{{ $colors[$correctionCostColor] }}">
                @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                @else
                    <span title="Effort fix bug / (Calendar +OT Effort)"
                          data-toggle="tooltip" class="pp-value cost_effort_efficiency2">&nbsp;
                    {{ $projectPointInformation['correction_cost'] }}
                </span>
                @endif
            </td>
            @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->correction_cost_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->correction_cost_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="correction_cost_note" class="note-input form-control"
                                      rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->correction_cost_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="correction_cost_note" class="note-input form-control"
                                      rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->correction_cost_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'correction_cost_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
            @endif
        </tr>

        @if (count($programLang) && count($programLang) == 1)
        <tr>
            <td>&nbsp;</td>
            <td class="align-right">{{reset($programLang)}}</td>
            <td class="align-right">
                @if($isTypeTrainingOrRD)
                <span>{{trans('project::view.NA')}}</span>
                @else
                <span class="pp-value cost_productivity">&nbsp;
                    <!--{{ $projectPointInformation['cost_productivity'] }}-->
                    <!--LOC current/effort_dev_current-->
                    @if(!$viewBaseline)
                        @if ($effortDevCurrent['effort_dev_current'] && $effortDevCurrent['effort_dev_current'] > 0)
                        {{ round(((int)$projectMeta->lineofcode_current/$effortDevCurrent['effort_dev_current']), 2) }}
                        @endif
                    @else
                        @if (isset($costProductivityProgLang[key($programLang)]['effort_dev_current'])
                                && $costProductivityProgLang[key($programLang)]['effort_dev_current'] > 0
                                && isset($costProductivityProgLang[key($programLang)]['loc_current'])
                        )
                        {{ round(((int)$costProductivityProgLang[key($programLang)]['loc_current']/$costProductivityProgLang[key($programLang)]['effort_dev_current']), 2) }}
                        @endif
                    @endif
                </span>
                @endif
            </td>
            <td>
                @if ($isTypeTrainingOrRD)
                <span>{{trans('project::view.NA')}}</span>
                @else
                    @if (!$viewBaseline)
                        @if ($permissionEditPP['pm'] || $permissionEditPP['subPM'])
                        <input type="text" class="align-right pp-input form-control scope lineofcode_current input-cost-productivity-proglang" text name="cost_productivity_proglang[{{key($programLang)}}][loc_current]" data-id="{{key($programLang)}}"
                            id="lineofcode_current" text-data="{{reset($programLang)}}: {{trans('project::view.LOC (current)')}}"
                            @if (isset($projectMeta))
                                oldValue="{{ $projectMeta->lineofcode_current }}"
                                value="{{ $projectMeta->lineofcode_current }}"
                            @else
                                oldValue=""
                                value=""
                            @endif>
                        <label id="lineofcode_current-{{key($programLang)}}-error" class="hidden" for="lineofcode_current"></label>
                        @else
                        <span>
                            @if (isset($projectMeta))
                            {{ $projectMeta->lineofcode_current }}
                            @endif
                        </span>
                        @endif
                    @else
                    <span>
                        @if (isset($costProductivityProgLang[key($programLang)]['loc_current']))
                        {{$costProductivityProgLang[key($programLang)]['loc_current']}}
                        @endif
                    </span>
                    @endif
                @endif
            </td>
            <td class="align-right">
                <span>
                    @if(!$viewBaseline)
                        @if (isset($effortDevCurrent['effort_dev_current']))
                        {{$effortDevCurrent['effort_dev_current']}}
                        @endif
                    @else
                        @if (isset($costProductivityProgLang[key($programLang)]['effort_dev_current']))
                        {{$costProductivityProgLang[key($programLang)]['effort_dev_current']}}
                        @endif
                    @endif
                </span>
            </td>
            <td>&nbsp;</td>
        </tr>
        @else
            @foreach ($programLang as $key => $value)
            <tr>
                <td>&nbsp;</td>
                <td class="align-right">{{$value}}</td>
                <td>
                    @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                        @if(!$viewBaseline)
                            @if ($permissionEditPP['pm'] || $permissionEditPP['subPM'])
                            <input type="text" data-id="{{$key}}"
                                class="align-right pp-input form-control scope lineofcode_current input-cost-productivity-proglang productivity-loc-current-{{$key}}" name="cost_productivity_proglang[{{$key}}][loc_current]"
                                id="lineofcode_current" text-data="{{$value}}: {{trans('project::view.LOC (current)')}}"
                                @if (isset($costProductivityProgLang[$key]['loc_current']))
                                    oldValue="{{ $costProductivityProgLang[$key]['loc_current'] }}"
                                    value="{{ $costProductivityProgLang[$key]['loc_current'] }}"
                                @else
                                    oldValue="" value=""
                                @endif>
                            <label id="lineofcode_current-{{$key}}-error" class="hidden" for="lineofcode_current"></label>
                            @else
                            <span>
                                @if (isset($costProductivityProgLang[$key]['loc_current']))
                                {{ $costProductivityProgLang[$key]['loc_current'] }}
                                @endif
                            </span>
                            @endif
                        @else
                            <span>
                                @if (isset($costProductivityProgLang[$key]['loc_current']))
                                {{ $costProductivityProgLang[$key]['loc_current'] }}
                                @endif
                            </span>
                        @endif
                    @endif
                </td>
                <td>
                    @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                        @if(!$viewBaseline)
                            @if ($permissionEditPP['pm'] || $permissionEditPP['subPM'])
                            <input class="align-right pp-input form-control scope lineofcode_baseline input-cost-productivity-proglang productivity-loc-baseline-{{$key}}" name="cost_productivity_proglang[{{$key}}][loc_baseline]" data-id="{{$key}}"
                                    id="lineofcode_baseline" text-data="{{$value}}: {{trans('project::view.LOC (baseline)')}}"
                                    @if (isset($costProductivityProgLang[$key]['loc_baseline']))
                                        oldValue="{{$costProductivityProgLang[$key]['loc_baseline'] }}"
                                        value="{{$costProductivityProgLang[$key]['loc_baseline'] }}"
                                    @else
                                        oldValue=""
                                        value=""
                                   @endif>
                            <label id="lineofcode_baseline-{{$key}}-error" class="hidden" for="lineofcode_baseline"></label>
                            @else
                            <span>
                                @if (isset($costProductivityProgLang[$key]['loc_baseline']))
                                    {{$costProductivityProgLang[$key]['loc_baseline'] }}
                                @endif
                            </span>
                            @endif
                        @else
                        <span>
                            @if (isset($costProductivityProgLang[$key]['loc_baseline']))
                                {{$costProductivityProgLang[$key]['loc_baseline'] }}
                            @endif
                        </span>
                        @endif
                     @endif
                </td>
                <td>
                    @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                        @if(!$viewBaseline)
                            @if ($permissionEditPP['pm'] || $permissionEditPP['subPM'])
                            <input type="text" class="align-right pp-input form-control scope lineofcode_baseline input-cost-productivity-proglang productivity-dev-effort-{{$key}}" name="cost_productivity_proglang[{{$key}}][dev_effort]" data-id="{{$key}}"
                                    id="dev_effort" text-data="{{$value}}: {{trans('project::view.Total effort of dev (MD)')}}"
                                    @if (isset($costProductivityProgLang[$key]['dev_effort']))
                                        oldValue="{{$costProductivityProgLang[$key]['dev_effort']}}"
                                        value="{{$costProductivityProgLang[$key]['dev_effort']}}"
                                    @else
                                        oldValue=""
                                        value=""
                                    @endif>
                            <label id="dev_effort-{{$key}}-error" class="hidden" for="dev_effort"></label>
                            @else
                            <span>
                                @if (isset($costProductivityProgLang[$key]['dev_effort']))
                                    {{$costProductivityProgLang[$key]['dev_effort']}}
                                @endif
                            </span>
                            @endif
                        @else
                        <span>
                            @if (isset($costProductivityProgLang[$key]['dev_effort']))
                                {{$costProductivityProgLang[$key]['dev_effort']}}
                            @endif
                        </span>
                        @endif
                    @endif
                </td>
                <td>&nbsp;</td>
            </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</div>

@include('project::point.tab.add_noti_remove_issue_list')
