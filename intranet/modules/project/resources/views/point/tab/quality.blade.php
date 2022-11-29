<?php
use Rikkei\Core\View\View;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Project;

//init object viewProject
$viewProject = new ViewProject();
$colors = $viewProject->getColorByValue();
$leakageColor = $viewProject->getLeakageColor($projectPointInformation['qua_leakage'], $projectPointInformation['qua_leakage_target'], $projectPointInformation['qua_leakage_ucl'], $checkCate);
$defectColor = $viewProject->getDefectColor($projectPointInformation['qua_defect'], $projectPointInformation['qua_defect_target'], $projectPointInformation['qua_defect_ucl'], $checkCate);

$isTypeTrainingOrRD = $project->isTypeTrainingOfRD();
?>

<div class="row">
    <div class="col-md-9">
    </div>
    @if(!$project->isTypeTrainNotReport() && $project->isOpen() && isset($isProjectSync) && isset($isProjectSync['redmine']) && $isProjectSync['redmine'] && $project->type != Project::TYPE_ONSITE)
        <div class="col-md-3 align-right margin-bottom-20">
            <button class="btn-add btn-sync-source-server" data-type="redmine" 
                id="sync_project_redmine" data-reload="1" type="button">{{ trans('project::view.Count bug in redmine') }}
                <i class="fa fa-spin fa-refresh hidden sync-loading"></i>
            </button>
        </div>
    @endif
</div>
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data table-point">
        <tbody>
            <tr>
                <td class="align-center col-title">
                    <strong>{{ trans('project::view.Project Information') }}</strong>
                </td>
                <td class="align-center col-input">{{ trans('project::view.Errors number') }}</td>
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
            
            <tr class="">
                <td class="align-left" title="{{ trans('project::view.Formula leakage') }}">{{ trans('project::view.Leakage') }}</td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    <span data-toggle="tooltip">
                        @if ($isSyncSource['redmine'])
                            &nbsp;{{ $projectPointInformation['qua_leakage_errors'] }}
                        @else
                            @if (isset($export))
                                {{ $projectPointInformation['qua_leakage_errors'] }}
                            @else
                                @if ($permissionEditPP['pm'] || $permissionEditPP['subPM'])
                                    <input type="text" class="pp-input form-control"
                                    name="qua_leakage_errors" data-type="quality" 
                                    value="{{ $projectPointInformation['qua_leakage_errors'] }}" />
                                @else
                                    &nbsp;{{ $projectPointInformation['qua_leakage_errors'] }}
                                @endif
                            @endif
                        @endif
                    </span>
                    @endif
                </td>
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
                <td class="align-right" bgcolor="{{ $colors[$leakageColor] }}">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span title="{{ trans('project::view.Formula leakage') }}"
                              data-toggle="tooltip" class="pp-value qua_leakage">&nbsp;
                        {{ $projectPointInformation['qua_leakage'] }}
                    </span>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->qua_leakage_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->qua_leakage_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="qua_leakage_note" class="note-input form-control" 
                                rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php 
                                endif; ?>>{{ $projectPoint->qua_leakage_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="qua_leakage_note" class="note-input form-control" 
                                rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php 
                                endif; ?>>{{ $projectPoint->qua_leakage_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'qua_leakage_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
                @endif
            </tr>
            <tr class="">
                <td class="align-left" title="{{ trans('project::view.Formula defect') }}">{{ trans('project::view.Defect rate') }}</td>
                <td class="align-right">
                    @if($isTypeTrainingOrRD)
                    <span>{{trans('project::view.NA')}}</span>
                    @else
                    <span data-toggle="tooltip">
                        @if ($isSyncSource['redmine'])
                            &nbsp;{{ $projectPointInformation['qua_defect_errors'] }}
                        @else
                            @if (isset($export))
                                {{ $projectPointInformation['qua_defect_errors'] }}
                            @else
                                @if ($permissionEditPP['pm'] || $permissionEditPP['subPM'])
                                    <input type="text" class="pp-input form-control"
                                    name="qua_defect_errors" data-type="quality" 
                                    value="{{ $projectPointInformation['qua_defect_errors'] }}" />
                                @else
                                    &nbsp;{{ $projectPointInformation['qua_defect_errors'] }}
                                @endif
                            @endif
                        @endif
                    </span>
                    @endif
                </td>
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
                <td class="align-right" bgcolor="{{ $colors[$defectColor] }}">
                    @if($isTypeTrainingOrRD)
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span title="{{ trans('project::view.Formula defect') }}"
                              data-toggle="tooltip" class="pp-value qua_defect">&nbsp;
                        {{ $projectPointInformation['qua_defect'] }}
                    </span>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->qua_defect_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->qua_defect_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="qua_defect_note" class="note-input form-control" 
                                rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php 
                                endif; ?>>{{ $projectPoint->qua_defect_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="qua_defect_note" class="note-input form-control" 
                                rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php 
                                endif; ?>>{{ $projectPoint->qua_defect_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'qua_defect_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
                @endif
            </tr>
        </tbody>
    </table>
</div>

@include('project::point.tab.add_noti_remove_issue_list')
