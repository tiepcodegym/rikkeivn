<?php
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\Project;

$isTypeTrainingOrRD = $project->isTypeTrainingOfRD();
?>
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-point table-grid-data">
        <tbody>
            <tr>
                <td class="align-center col-title">
                    <strong>{{ trans('project::view.Metric') }}</strong>
                </td>
                <td class="align-center col-lcl">{{ trans('project::view.LSL') }}</td>
                <td class="align-center col-lcl">{{ trans('project::view.Target') }}</td>
                <td class="align-center col-lcl">{{ trans('project::view.USL') }}</td>
                <td class="align-center col-value">{{ trans('project::view.Value') }}</td>
                @if(!$viewBaseline || $viewNote)
                    <td class="align-center col-note">
                        {!! $textHtmlNoteHead !!}
                    </td>
                @endif
            </tr>

            <tr>
                <td class="align-left css-point css_css-value" title="{{ trans('project::view.Formula Css') }}">{{ trans('project::view.Customer Satisfation (Point)') }}</td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        {{ $projectPointInformation['css_css_lcl'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        {{ $projectPointInformation['css_css_target'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        {{ $projectPointInformation['css_css_ucl'] }}
                    @endif
                </td>
                <td class="align-right" bgcolor="@if($checkCate && $projectPointInformation['css_css'] != null) @if($projectPointInformation['css_css'] < $projectPointInformation['css_css_lcl']) #dd4b39
                       @elseif(($projectPointInformation['css_css_lcl'] <= $projectPointInformation['css_css']) && ($projectPointInformation['css_css'] < $projectPointInformation['css_css_target'])) #f3b812
                       @elseif($projectPointInformation['css_css_target'] <= $projectPointInformation['css_css']) #00a65a @endif @endif">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span title="{{ trans('project::view.Formula Css') }}" data-toggle="tooltip" class="pp-value css_cs">&nbsp;
                        {{ $projectPointInformation['css_css'] }}
                    </span>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                    <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->css_summary_note) ? ' tooltip-disable' : '' }}">
                        @if (isset($export))
                            {!! View::nl2br($projectPoint->css_summary_note) !!}
                        @else
                            <div class="text-display">
                            <textarea name="css_summary_note" class="note-input form-control"
                                      rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->css_summary_note }}</textarea>
                            </div>
                            <div class="dropdown-menu text-tooltip">
                            <textarea name="css_summary_note" class="note-input form-control"
                                      rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->css_summary_note }}</textarea>
                            </div>
                            @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'css_summary_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                        @endif
                    </td>
                @endif
            </tr>

            <tr class="">
                <td class="align-left" title="{{ trans('project::view.Formula leakage') }}">{{ trans('project::view.Leakage') }}</td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['qua_leakage_lcl'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['qua_leakage_target'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['qua_leakage_ucl'] }}
                    @endif
                </td>
                <td class="align-right" bgcolor="@if($checkCate && $projectPointInformation['qua_leakage'] != null) @if($projectPointInformation['qua_leakage'] > $projectPointInformation['qua_leakage_ucl']) #dd4b39
                        @elseif($projectPointInformation['qua_leakage_target'] == $projectPointInformation['qua_leakage']) green
                        @else #f3b812 @endif @endif">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span title="{{ trans('project::view.Formula leakage') }}" data-toggle="tooltip" class="pp-value qua_leakage">&nbsp;
                        {{ $projectPointInformation['qua_leakage'] }}
                    </span>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                    <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->qua_leakage_summary_note) ? ' tooltip-disable' : '' }}">
                        @if (isset($export))
                            {!! View::nl2br($projectPoint->qua_leakage_summary_note) !!}
                        @else
                            <div class="text-display">
                            <textarea name="qua_leakage_summary_note" class="note-input form-control"
                                      rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->qua_leakage_summary_note }}</textarea>
                            </div>
                            <div class="dropdown-menu text-tooltip">
                            <textarea name="qua_leakage_summary_note" class="note-input form-control"
                                      rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->qua_leakage_summary_note }}</textarea>
                            </div>
                            @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'qua_leakage_summary_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                        @endif
                    </td>
                @endif
            </tr>

            <tr class="">
                <td class="align-left" title="{{ trans('project::view.Formula defect') }}">{{ trans('project::view.Defect rate') }}</td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['qua_defect_lcl'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['qua_defect_target'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['qua_defect_ucl'] }}
                    @endif
                </td>
                <td class="align-right" bgcolor="@if($checkCate && $projectPointInformation['qua_defect'] != null) @if($projectPointInformation['qua_defect'] > $projectPointInformation['qua_defect_ucl']) #dd4b39
                        @elseif($projectPointInformation['qua_defect_target'] == $projectPointInformation['qua_defect']) green
                        @else #f3b812 @endif @endif">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span data-toggle="tooltip" title="{{ trans('project::view.Formula defect') }}" class="pp-value qua_defect">&nbsp;
                        {{ $projectPointInformation['qua_defect'] }}
                    </span>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                    <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->qua_defect_summary_note) ? ' tooltip-disable' : '' }}">
                        @if (isset($export))
                            {!! View::nl2br($projectPoint->qua_defect_summary_note) !!}
                        @else
                            <div class="text-display">
                            <textarea name="qua_defect_summary_note" class="note-input form-control"
                                      rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->qua_defect_summary_note }}</textarea>
                            </div>
                            <div class="dropdown-menu text-tooltip">
                            <textarea name="qua_defect_summary_note" class="note-input form-control"
                                      rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->qua_defect_summary_note }}</textarea>
                            </div>
                            @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'qua_defect_summary_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                        @endif
                    </td>
                @endif
            </tr>

            <tr class="">
                <td class="align-left" title="{{ trans('project::view.Formula PCV') }}">{{ trans('project::view.PCV') }}</td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        {{ $projectPointInformation['proc_compliance_lcl'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        {{ $projectPointInformation['proc_compliance_target'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        {{ $projectPointInformation['proc_compliance_ucl'] }}
                    @endif
                </td>
                <td class="align-right" bgcolor="@if($checkCate && $projectPointInformation['proc_compliance'] != null) @if($projectPointInformation['proc_compliance'] < $projectPointInformation['proc_compliance_lcl']) #dd4b39
                       @elseif(($projectPointInformation['proc_compliance_lcl'] <= $projectPointInformation['proc_compliance']) && ($projectPointInformation['proc_compliance'] < $projectPointInformation['proc_compliance_target'])) #f3b812
                       @elseif($projectPointInformation['proc_compliance_target'] <= $projectPointInformation['proc_compliance']) #00a65a @endif @endif">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span data-toggle="tooltip" title="{{ trans('project::view.Formula PCV') }}" class="pp-value proc_compliance">&nbsp;
                        {{ $projectPointInformation['proc_compliance'] }}
                    </span>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                    <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->proc_compliance_summary_note) ? ' tooltip-disable' : '' }}">
                        @if (isset($export))
                            {!! View::nl2br($projectPoint->proc_compliance_summary_note) !!}
                        @else
                            <div class="text-display">
                            <textarea name="proc_compliance_summary_note" class="note-input form-control"
                                      rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->proc_compliance_summary_note }}</textarea>
                            </div>
                            <div class="dropdown-menu text-tooltip">
                            <textarea name="proc_compliance_summary_note" class="note-input form-control"
                                      rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->proc_compliance_summary_note }}</textarea>
                            </div>
                            @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'proc_compliance_summary_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                        @endif
                    </td>
                @endif
            </tr>

            <tr class="">
                <td class="align-left" title="{{ trans('project::view.Formula timeliness') }}">{{ trans('project::view.timeliness') }} (%)</td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['tl_deliver_lcl'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['tl_deliver_target'] }}
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD || !$checkCate)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    {{ $projectPointInformation['tl_deliver_ucl'] }}
                    @endif
                </td>
                <td class="align-right" bgcolor="@if($checkCate && $projectPointInformation['tl_deliver'] != null) @if($projectPointInformation['tl_deliver'] < $projectPointInformation['tl_deliver_lcl']) #dd4b39
                       @elseif(($projectPointInformation['tl_deliver_lcl'] <= $projectPointInformation['tl_deliver']) && ($projectPointInformation['tl_deliver'] < $projectPointInformation['tl_deliver_ucl'])) #f3b812
                       @elseif($projectPointInformation['tl_deliver'] >= $projectPointInformation['tl_deliver_target']) #00a65a @endif @endif">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span data-toggle="tooltip" title="{{ trans('project::view.Formula timeliness') }}" class="pp-value tl_deliver">&nbsp;
                        {{ $projectPointInformation['tl_deliver'] }}
                    </span>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                    <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->deliver_summary_note) ? ' tooltip-disable' : '' }}">
                        @if (isset($export))
                            {!! View::nl2br($projectPoint->proc_compliance_summary_note) !!}
                        @else
                            <div class="text-display">
                            <textarea name="deliver_summary_note" class="note-input form-control"
                                      rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->deliver_summary_note }}</textarea>
                            </div>
                            <div class="dropdown-menu text-tooltip">
                            <textarea name="deliver_summary_note" class="note-input form-control"
                                      rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->deliver_summary_note }}</textarea>
                            </div>
                            @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'deliver_summary_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
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
                        {{ $projectPointInformation['correct_cost_ucl'] }}
                    @endif
                </td>
                <td class="align-right" bgcolor="@if($checkCate && $projectPointInformation['correction_cost'] != null) @if($projectPointInformation['correction_cost'] < $projectPointInformation['correct_cost_lcl'] || $projectPointInformation['correction_cost'] > $projectPointInformation['correct_cost_ucl']) #dd4b39
                       @elseif($projectPointInformation['correction_cost'] == $projectPointInformation['correct_cost_target']) #00a65a
                       @else #f3b812 @endif @endif">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span title="{{ trans('project::view.Formula correction') }}"
                              data-toggle="tooltip" class="pp-value cost_effort_efficiency2">&nbsp;
                        {{ $projectPointInformation['correction_cost'] }}
                    </span>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                    <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->correction_cost_summary_note) ? ' tooltip-disable' : '' }}">
                        @if (isset($export))
                            {!! View::nl2br($projectPoint->proc_compliance_summary_note) !!}
                        @else
                            <div class="text-display">
                            <textarea name="correction_cost_summary_note" class="note-input form-control"
                                      rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->correction_cost_summary_note }}</textarea>
                            </div>
                            <div class="dropdown-menu text-tooltip">
                            <textarea name="correction_cost_summary_note" class="note-input form-control"
                                      rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->correction_cost_summary_note }}</textarea>
                            </div>
                            @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'correction_cost_summary_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
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
                <td class="align-right" bgcolor="@if($checkCate && $projectPointInformation['cost_effort_efficiency2'] != null) @if($projectPointInformation['cost_effort_efficiency2'] < $projectPointInformation['cost_effort_efficiency_lcl']) #dd4b39
                       @elseif(($projectPointInformation['cost_effort_efficiency_lcl'] <= $projectPointInformation['cost_effort_efficiency2']) && ($projectPointInformation['cost_effort_efficiency2'] < $projectPointInformation['cost_effort_efficiency_target'])) #f3b812
                       @elseif($projectPointInformation['cost_effort_efficiency_target'] <= $projectPointInformation['cost_effort_efficiency2']) #00a65a @endif @endif">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span data-toggle="tooltip" title="{{ trans('project::view.Formula EE') }}" class="pp-value cost_effort_efficiency2">&nbsp;
                        {{ $projectPointInformation['cost_effort_efficiency2'] }}
                    </span>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                    <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->effort_efficiency_summary_note) ? ' tooltip-disable' : '' }}">
                        @if (isset($export))
                            {!! View::nl2br($projectPoint->proc_compliance_summary_note) !!}
                        @else
                            <div class="text-display">
                            <textarea name="effort_efficiency_summary_note" class="note-input form-control"
                                      rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->effort_efficiency_summary_note }}</textarea>
                            </div>
                            <div class="dropdown-menu text-tooltip">
                            <textarea name="effort_efficiency_summary_note" class="note-input form-control"
                                      rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php
                                endif; ?>>{{ $projectPoint->effort_efficiency_summary_note }}</textarea>
                            </div>
                            @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'effort_efficiency_summary_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                        @endif
                    </td>
                @endif
            </tr>

            <tr>
                <td class="align-left" title="{{ trans('project::view.Formula CC') }}">CC (number)</td>
                <td class="align-right">
                </td>
                <td class="align-right">
                </td>
                <td class="align-right">
                </td>
                <td class="align-right" >
                    <span data-toggle="tooltip" title="{{ trans('project::view.Formula CC') }}">{{ $projectPointInformation['countIssueCusComplaint'] }}</span>
                </td>
                <td class="align-right">
                </td>
            </tr>

            <?php /*</tr>
                <td class="align-left">{{ trans('project::view.Customer ideas') }} (#)</td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    <span title="{{ ViewProject::getHintPoint('css_ci') }}"
                          data-toggle="tooltip" class="pp-value css_ci">&nbsp;
                        {{ $projectPointInformation['css_ci'] }}
                    </span>
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    <span title="{{ ViewProject::getHintPoint('css_ci_point') }}"
                        data-toggle="tooltip" class="pp-value css_ci_point">&nbsp;
                        {{ $projectPointInformation['css_ci_point'] }}
                    </span>
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    <span title="{{ ViewProject::getHintPoint('css_ci_positive') }}"
                        data-toggle="tooltip" class="pp-value css_ci_positive">&nbsp;
                        {{ $projectPointInformation['css_ci_positive'] }}
                    <span>
                    @endif
                </td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    <span title="{{ ViewProject::getHintPoint('css_ci_negative') }}"
                        data-toggle="tooltip" class="pp-value css_ci_negative">&nbsp;
                        {{ $projectPointInformation['css_ci_negative'] }}
                    </span>
                    @endif
                </td>
                <td></td>
            </tr>*/ ?>
        </tbody>
    </table>
</div>
