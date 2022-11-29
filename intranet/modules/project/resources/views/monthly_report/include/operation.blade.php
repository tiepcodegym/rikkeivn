<?php
use Rikkei\Project\Model\MonthlyReport;
use Illuminate\Support\Facades\Lang;

$rowsOperation = [
    $rowProjectPoint => [
        'title' => trans('project::view.Project Point'),
        'tooltip' => trans('project::view.Project Point tooltip'),
    ],
    $rowCssPoint => [
        'title' => trans('project::view.CSS point'),
        'tooltip' => trans('project::view.Collect average CSS of all projects'),
    ],
    $rowCssImprovement => [
        'title' => '%' . trans('project::view.CSS improvement'),
        'tooltip' => trans('project::view.(Average CSS/last period\'s baseline)*100%'),
    ],
];
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
            @foreach ($rowsOperation as $rowKey => $rowValue)
            <tr role="row" class="odd" data-row='{{ $rowKey }}'>
                <td  class="width-200">
                    <span>{{ $rowValue['title'] }}</span>
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ $rowValue['tooltip'] }}"></i>
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'> 
                           {{ isset($values[$team->id][$i][$rowKey][MonthlyReport::IS_VALUE]) ?  $values[$team->id][$i][$rowKey][MonthlyReport::IS_VALUE] : ''}}
                    </span>
                    @if ($rowKey == $rowProjectPoint)
                    <div class="tooltip">
                    @if (isset($values[$team->id][$i]['project_name_point']))
                    {!! nl2br($values[$team->id][$i]['project_name_point']) !!}
                    @endif
                    </div>
                    @endif
                </td>
                <td class="sorting_1 text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowKey][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowKey][MonthlyReport::IS_POINT] : $notAvailable}} 
                    </span>
                </td>
                @endfor
            </tr>
            @endforeach
            <tr role="row" class="odd" data-row='{{ $rowCusComment }}'>
                <td class="width-200">
                    <span>Customer comment</span>
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ trans('project::view.Collect feedbacks from customers in Project Dashboard, arranging according to the size of projects (from small to big)') }}"></i></td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1" colspan="2">
                    <span data-month="{{ $i }}"> 
                           {!! isset($values[$team->id][$i][$rowCusComment]) ?  nl2br($values[$team->id][$i][$rowCusComment]) : '' !!}
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
