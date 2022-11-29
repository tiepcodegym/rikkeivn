<?php
use Rikkei\Project\Model\MonthlyReport;

$rowsHr = [
    $rowHrPlan => [
        'title' => 'Plan',
        'tooltip' => ''
    ],
    $rowHrActual => [
        'title' => 'Actual',
        'tooltip' => ''
    ],
    $rowHrOut  => [
        'title' => 'Out',
        'tooltip' => ''
    ],
    $rowHrCompletedPlan => [
        'title' => '% HR completed Planning',
        'tooltip' => 'Actual/Plan'
    ],
    $rowHrTurnOverate => [
        'title' => '% Turn overate',
        'tooltip' => 'Out/Actual'
    ],
]        
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
                <th rowspan="1" colspan="1" class="align-center">Value</th>
                <th rowspan="1" colspan="1" class="align-center">Point</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($rowsHr as $rowKey => $rowValue)
            <tr role="row" class="odd" data-row='{{ $rowKey }}'>
                <td class="width-200">
                    <span>{{ $rowValue['title'] }}</span>
                    @if (!empty($rowValue['tooltip']))
                    <i class="fa fa-question-circle pull-right" data-toggle="tooltip" title="{{ $rowValue['tooltip'] }}"></i>
                    @endif
                </td>
                @for ($i=$startMonth; $i<=$endMonth; $i++)
                <td class="sorting_1 month-value">
                    <?php
                    $monthValue = isset($values[$team->id][$i][$rowKey][MonthlyReport::IS_VALUE]) ?  $values[$team->id][$i][$rowKey][MonthlyReport::IS_VALUE] : 0;
                    ?>
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_VALUE }}'>
                        @if ($rowKey === $rowHrTurnOverate)
                            <?php
                            $turnOverate = $monthValue;
                            ?>
                            @if (is_numeric($turnOverate))
                            @for ($m = 1; $m < $i; $m++)
                                <?php
                                $turnOverateMonth = 0;
                                if (isset($values[$team->id][$m][$rowKey][MonthlyReport::IS_VALUE])
                                        && $values[$team->id][$m][$rowKey][MonthlyReport::IS_VALUE] !== 'N/A') {
                                    $turnOverateMonth = $values[$team->id][$m][$rowKey][MonthlyReport::IS_VALUE];
                                }
                                $turnOverate += $turnOverateMonth;
                                ?>
                            @endfor
                            @endif
                            {{ $turnOverate }}
                        @else
                           {{ $monthValue }}
                        @endif
                    </span>
                </td>
                <td class="sorting_1 month-point text-green">
                    <span data-month="{{ $i }}" data-value-or-point='{{ MonthlyReport::IS_POINT }}'>
                        {{ isset($values[$team->id][$i][$rowKey][MonthlyReport::IS_POINT]) ?  $values[$team->id][$i][$rowKey][MonthlyReport::IS_POINT] : $notAvailable}} 
                    </span>
                </td>
                @endfor
            </tr>
            @endforeach
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
