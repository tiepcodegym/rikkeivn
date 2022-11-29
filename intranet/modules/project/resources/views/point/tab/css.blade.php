<?php
use Rikkei\Core\View\View;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\ProjectPoint;
use Carbon\Carbon;
use Rikkei\Project\Model\Task;
use Rikkei\Team\View\Permission;

$taskType = Task::typeLabel();
$isTypeTrainingOrRD = $project->isTypeTrainingOfRD();
$viewProject = new ViewProject();
$colors = $viewProject->getColorByValue();
$colorCSS = $viewProject->getCssColor($projectPointInformation['css_css'], $projectPointInformation['css_css_target'], $projectPointInformation['css_css_lcl'], $checkCate);
$colorInput = $colors[$colorCSS];
?>
<div class="row">
    <div class="col-md-12">
        <p>
            <span>{{ trans('project::view.Customer satisfactions') }}:</span>
            @if($isTypeTrainingOrRD)
            <span>{{trans('project::view.NA')}}</span>
            @else
            <span class="pp-value css_cs_point">{{ $projectPointInformation['css_css_point'] }}</span>
            @endif
        </p>
        <?php /*<p>
            <span>{{ trans('project::view.Customer ideas') }} (#):</span>
            @if($isTypeTrainingOrRD)
            <span>{{trans('project::view.NA')}}</span>
            @else
            <span class="pp-value css_ci_point">{{ $projectPointInformation['css_ci_point'] }}</span>
            @endif
        </p>*/ ?>
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
            <tr class="">
                <td class="align-left" title="{{ trans('project::view.Formula Css') }}">{{ trans('project::view.Customer Satisfation (Point)') }}</td>
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
                <td class="align-right" bgcolor="{{ $colorInput }}">
                    @if($project->isTypeTrainingOrRD())
                        <span>{{trans('project::view.NA')}}</span>
                    @else
                        <span title="{{ trans('project::view.Formula Css') }}" data-toggle="tooltip" class="pp-value css_cs">&nbsp;
                            {{ $projectPointInformation['css_css'] }}
                        </span>
                    @endif
                </td>
                @if(!$viewBaseline || $viewNote)
                <td class="align-left dropdown text-tooltip-wrapper{{ ViewProject::isLtLength($projectPoint->css_css_note) ? ' tooltip-disable' : '' }}">
                    @if (isset($export))
                        {!! View::nl2br($projectPoint->css_css_note) !!}
                    @else
                        <div class="text-display">
                            <textarea name="css_css_note" class="note-input form-control" 
                                    rows="1"<?php if (!$permissionEditPP['note']): ?> disabled<?php 
                                    endif; ?>>{{ $projectPoint->css_css_note }}</textarea>
                        </div>
                        <div class="dropdown-menu text-tooltip">
                            <textarea name="css_css_note" class="note-input form-control" 
                                rows="5"<?php if (!$permissionEditPP['note']): ?> disabled<?php 
                                endif; ?>>{{ $projectPoint->css_css_note }}</textarea>
                        </div>
                        @if(!$timeCloseProject)<button data-loading-text="Loading..." onclick="getPrevNote(event, 'css_css_note');" class="btn btn-sm btn-primary btn-get-note">Get prev note</button>@endif
                    @endif
                </td>
                @endif
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td class="align-center">{{ trans('project::view.Positive') }}</td>
                <td class="align-center">{{ trans('project::view.Negative') }}</td>
                <td></td>
                @if(!$viewBaseline || $viewNote)
                    <td></td>
                @endif
            </tr>
           
            
        </tbody>
    </table>
</div>

<div>
    <h3>{{ trans('project::view.CSS list') }}</h3>
    <div class="table-responsive">
        <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
            <thead>
                <tr>
                    <th class="col-id" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                    <th class="" style="max-width: 300px">{{ trans('project::view.Evaluator\'s name') }}</th>
                    <th style="width:100px">{{ trans('project::view.CSS point') }}</th>
                    <th class="col-sm-1">{{ trans('project::view.Evaluation date') }}</th>
                    <th style="width:300px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cssResults as $key => $result)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $result->name }}</td>
                    <td>{{ $result->avg_point }}</td>
                    <td>{{ $result->created_at }}</td>
                    <td><a href="{{ route('sales::css.detail', $result->id) }}">{{ route('sales::css.detail', $result->id) }}</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<br>  
<!-- Commended and criticized -->
@include('project::point.tab.add_noti_remove_issue_list')

