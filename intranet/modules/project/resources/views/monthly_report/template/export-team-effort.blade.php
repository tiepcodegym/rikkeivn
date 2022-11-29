<?php
use Rikkei\Project\View\MRExcel;

$countMonth = count($arrayMonths);
$hrPlans = $hrData['plan'];
$hrActuals = $hrData['actual'];
$offsetRow = 3;
$teamRow = 8;
$offsetCalColumn = 'B';
$collectCelHuman = [];
$collectCelHmActual = [];
$collectCelWork = [];
$arrayMonthValues = array_values($arrayMonths);
$offsetSummaryCol = 'A';
$summaryTotalRow = 4;
?>

<table style="border-collapse: collapse;">
    <thead>
        <tr>
            <th width="10"></th>
            <th style="text-align: center;" colspan="{{ $countMonth + 1 }}">{{ trans('project::view.RikkeiSoft work situation') }} {{ $arrayMonthValues[0] }} - {{ $arrayMonthValues[$countMonth - 1] }}</th>
        </tr>
        <tr><th colspan="{{ $countMonth + 2 }}"></th></tr>
    </thead>
    <tbody>
        @foreach ($teams as $num => $team)
        <tr>
            <th></th>
            <th colspan="{{ $countMonth + 1 }}">{{ ($num + 1) . '. ' . $team->name }}</th>
        </tr>
        <tr>
            <td></td>
            <td>{{ trans('project::view.Month_vi') }}</td>
            @foreach ($arrayMonths as $format => $month)
            <td style="text-align: right;">{{ $month }}</td>
            @endforeach
        </tr>
        <tr>
            <td></td>
            <td>{{ trans('project::view.Number of human plan') }}</td>
            @foreach ($arrayMonths as $format => $month)
            <td style="text-align: right;">{{ isset($hrPlans[$format]) && isset($hrPlans[$format][$team->id]) ? $hrPlans[$format][$team->id] : 0 }}</td>
            @endforeach
        </tr>
        <tr>
            <td></td>
            <td>{{ trans('project::view.Number of human actual') }}</td>
            @foreach ($arrayMonths as $format => $month)
            <td style="text-align: right;">{{ isset($hrActuals[$format]) && isset($hrActuals[$format][$team->id]) ? $hrActuals[$format][$team->id] : 0 }}</td>
            @endforeach
        </tr>
        <tr>
            <td></td>
            <td>{{ trans('project::view.Revenue') }}</td>
            @foreach ($arrayMonths as $format => $month)
            <td></td>
            @endforeach
        </tr>
        <tr>
            <td></td>
            <td>{{ trans('project::view.Work effort') }}</td>
            <?php
            $idxMonth = 0;
            ?>
            @foreach ($arrayMonths as $format => $month)
            <td>
                @if ($teamId == $team->id)
                    <?php
                    $nextCol = MRExcel::getColNameByIndex(ord($offsetSummaryCol) + $idxMonth * MRExcel::NUM_PER_MONTH + 2);
                    $idxMonth++;
                    ?>
                    ={{ $sheetSummary . '!' . $nextCol . $summaryTotalRow }}
                @else
                    {{ isset($teamBillables[$team->id][$format]) ? $teamBillables[$team->id][$format] : null }}
                @endif
            </td>
            @endforeach
        </tr>
        <tr>
            <td></td>
            <td>{{ trans('project::view.Busy rate') }} %</td>
            <?php 
            $idxMonth = 1;
            $rowHuman = $offsetRow + 2 + $num * $teamRow;
            $rowHmActual = $offsetRow + 3 + $num * $teamRow;
            $rowWork = $offsetRow + 5 + $num * $teamRow;
            ?>
            @foreach ($arrayMonths as $format => $month)
                <?php
                $nextCol = MRExcel::getColNameByIndex(ord($offsetCalColumn) + $idxMonth);
                $idxMonth++;
                $cellWork = $nextCol . $rowWork;
                $cellHuman = $nextCol . $rowHuman;
                $cellHmActual = $nextCol . $rowHmActual;
                $collectCelHuman[$format][$team->id] = $cellHuman;
                $collectCelWork[$format][$team->id] = $cellWork;
                $collectCelHmActual[$format][$team->id] = $cellHmActual;
                ?>
                <td style="text-align: right;">=IF(OR({{ $cellWork . '=0,' . $cellHuman . '=0' }}),0,{{ $cellWork . '/' . $cellHuman . '*100' }})</td>
            @endforeach
        </tr>
        <tr>
            <td colspan="{{ $countMonth + 2 }}"></td>
        </tr>
        @endforeach

        <tr>
            <td colspan="2">Thực tế</td>
            @foreach ($arrayMonths as $format => $month)
            <td style="text-align: right;">{{ $month }}</td>
            @endforeach
        </tr>
        <tr>
            <td>1</td>
            <td style="white-space: nowrap;">Tổng nhân sự Dev + QA</td>
            @foreach ($arrayMonths as $format => $month)
                <td style="text-align: right;">=SUM({{ implode(',', $collectCelHmActual[$format]) }})</td>
            @endforeach
        </tr>
        <tr>
            <td>2</td>
            <td style="white-space: nowrap;">Tổng man month</td>
            @foreach ($arrayMonths as $format => $month)
                <td style="text-align: right;">=SUM({{ implode(',', $collectCelWork[$format]) }})</td>
            @endforeach
        </tr>
        <tr>
            <td>3</td>
            <td style="white-space: nowrap;">Tổng doanh thu (USD)</td>
            @foreach ($arrayMonths as $format => $month)
                <td></td>
            @endforeach
        </tr>
        <tr>
            <td>4</td>
            <td style="white-space: nowrap;">Tỉ lệ mm/(Dev + QA)</td>
            <?php
            $idxMonth = 1;
            $actualRow = $teams->count() * 7 + $offsetRow - 1;
            ?>
            @foreach ($arrayMonths as $format => $month)
                <?php
                $nextCol = MRExcel::getColNameByIndex(ord($offsetCalColumn) + $idxMonth);
                $idxMonth++;
                $cellActualHuman = $nextCol . ($actualRow + 2);
                $cellActualWork = $nextCol . ($actualRow + 3);
                ?>
                <td style="text-align: right;">=IF(OR({{ $cellActualWork . '=0,' . $cellActualHuman . '=0' }}),0,{{ $cellActualWork . '/' . $cellActualHuman . '*100' }})</td>
            @endforeach
        </tr>
        <tr>
            <td>5</td>
            <td style="white-space: nowrap;">Tỉ lệ Thực tế/Kế hoạch</td>
            @foreach ($arrayMonths as $format => $month)
                <td style="text-align: right;"></td>
            @endforeach
        </tr>
        
        <tr>
            <td colspan="{{ $countMonth + 2 }}"></td>
        </tr>
        <tr>
            <td colspan="2">Kế hoạch chung</td>
            @foreach ($arrayMonths as $format => $month)
                <td style="text-align: right;">{{ $month }}</td>
            @endforeach
        </tr>
        <?php
        $planRows = ['Nhân sự (dev + QA)', 'Số man month', 'Doanh thu (USD)'];
        ?>
        @foreach ($planRows as $idx => $rowName)
        <tr>
            <td>{{ $idx + 1 }}</td>
            <td>{{ $rowName }}</td>
            @foreach ($arrayMonths as $format => $month)
                @if ($idx == 0)
                <td style="text-align: right;">=SUM({{ implode(',', $collectCelHuman[$format]) }})</td>
                @else
                <td style="text-align: right;"></td>
                @endif
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
