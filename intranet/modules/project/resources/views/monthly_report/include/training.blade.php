<?php
use Rikkei\Project\Model\MonthlyReport;

?>

<div class="table-responsive padding-left-250">
    <table class="table table-bordered table-hover dataTable" role="grid" data-type='{{ MonthlyReport::TYPE_OPERATION }}'>
        <thead>
            <tr role="row">
                <th></th>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <th rowspan="1" colspan="2" class="align-center">T{{ $i }}</th>
                @endfor
            </tr>
            <tr role="row">
                <th></th>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                    <th rowspan="1" colspan="1" class="align-center">{{ trans('project::view.Value') }}</th>
                    <th rowspan="1" colspan="1" class="align-center">{{ trans('project::view.Point') }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            <tr role="row" class="odd" data-row='{{ $rowTrainingPlan }}'>
                <td class="width-200">
                    <span>Plan</span>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1" colspan="2">
                    @if ($updatePemission)
                    <textarea class="form-control resize-vertical-only" data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'>{{ isset($values[$team->id][$i][$rowTrainingPlan][MonthlyReport::IS_VALUE]) ?  $values[$team->id][$i][$rowTrainingPlan][MonthlyReport::IS_VALUE] : ''}}</textarea>
                    @else
                    {{ isset($values[$team->id][$i][$rowTrainingPlan][MonthlyReport::IS_VALUE]) ?  $values[$team->id][$i][$rowTrainingPlan][MonthlyReport::IS_VALUE] : ''}}
                    @endif
                </td>
                @endfor
            </tr>
            <tr role="row" class="odd" data-row='{{ $rowTrainingActual }}'>
                <td class="width-200">
                    <span>Actual</span>
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.Collect points from professional activities in ME') }}"></i>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'> 
                           {{ isset($values[$team->id][$i][$rowTrainingActual][MonthlyReport::IS_VALUE]) ?  $values[$team->id][$i][$rowTrainingActual][MonthlyReport::IS_VALUE] : 0}}
                    </span>
                </td>
                <td class="sorting_1 text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowTrainingActual][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowTrainingActual][MonthlyReport::IS_POINT] : $notAvailable}} 
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="odd" data-row='{{ $rowTrainingPoint }}'>
                <td class="width-200">
                    <span>Average training point</span>
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.Total points for taking partin professional activities/total number of employees') }}"></i>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'> 
                           {{ isset($values[$team->id][$i][$rowTrainingPoint][MonthlyReport::IS_VALUE]) ?  $values[$team->id][$i][$rowTrainingPoint][MonthlyReport::IS_VALUE] : $notAvailable}}
                    </span>
                </td>
                <td class="sorting_1 text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowTrainingPoint][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowTrainingPoint][MonthlyReport::IS_POINT] : $notAvailable}} 
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="odd" data-row='{{ $rowLanguageIndex }}'>
                <td class="width-200">
                    <span>Language index</span>
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans("project::view.Total points for employee's Japanese proficiency certificate") }}"></i>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'> 
                         {{ $notAvailable }}
                    </span>
                </td>
                <td class="sorting_1 text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ $notAvailable }}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="odd" data-row='{{ $rowAvgLanguageIndex }}'>
                <td class="width-200">
                    <span>Average language index</span>
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.Total languages index/total number of employees') }}"></i>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'> 
                         {{ $notAvailable }}
                    </span>
                </td>
                <td class="sorting_1 text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ $notAvailable }}
                    </span>
                </td>
                @endfor
            </tr>
            <tr role="row" class="odd" data-row='{{ $rowAvgSocialActivity }}'>
                <td class="width-200">
                    <span>Average social activity</span>
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.Total points for taking part in social activities in ME') }}"></i>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'> 
                        {{ isset($values[$team->id][$i][$rowAvgSocialActivity][MonthlyReport::IS_VALUE]) ?  $values[$team->id][$i][$rowAvgSocialActivity][MonthlyReport::IS_VALUE] : $notAvailable }}
                    </span>
                </td>
                <td class="sorting_1 text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowAvgSocialActivity][MonthlyReport::IS_POINT]) ? $values[$team->id][$i][$rowAvgSocialActivity][MonthlyReport::IS_POINT] : $notAvailable }}
                    </span>
                </td>
                @endfor
            </tr>
        </tbody>
    </table>
</div>
@if ($updatePemission)
<div class="row">
    <div class="col-md-12 align-center">
        <button type="button" class="btn btn-add btn-submit">
            Submit
            <i class="fa fa-spin fa-refresh hidden"></i>
        </button>
    </div>
</div>
@endif
