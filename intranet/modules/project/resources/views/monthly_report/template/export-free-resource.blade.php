<?php
use Rikkei\Project\View\MRExcel;

$countMonth = count($arrayMonths);
$offsetRow = 3;
$teamRow = 7;
$offsetCalColumn = 'B';
$arrRoleRows = [
    'total' => 'Số lượng nhân sự thừa',
    'pm' => 'PM',
    'brse' => 'BrSE',
    'dev' => 'Dev'
];
$arrayMonthValues = array_values($arrayMonths);
$resourceData = [];
if (!$freeData->isEmpty()) {
    $resourceData = $freeData->lists('free_effort', 'month')->toArray();
}
$collectCellRoles = [];
?>

<table style="border-collapse: collapse;">
    <thead>
        <tr>
            <th width="10"></th>
            <th style="text-align: center;" colspan="{{ $countMonth + 1 }}">Tình hình Resource {{ $arrayMonthValues[0] }} - {{ $arrayMonthValues[$countMonth - 1] }}</th>
        </tr>
        <tr>
            <th colspan="{{ $countMonth + 2 }}"></th>
        </tr>
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
            @foreach ($arrRoleRows as $key => $rowName)
            <tr>
                <td></td>
                <td>{{ $rowName }}</td>
                <?php 
                $idxMonth = 1;
                ?>
                @foreach ($arrayMonths as $format => $month)
                <?php
                $nextCol = chr(ord($offsetCalColumn) + $idxMonth);
                if ($key == 'total') {
                    $cellTotal = $nextCol . ($offsetRow + 2 + $num * $teamRow);
                    $collectCellRoles[$format][] = $cellTotal;
                }
                $idxMonth++;
                ?>
                <td style="text-align: right;">
                    @if (isset($resourceData[$format]) && isset($resourceData[$format][$team->id]))
                        <?php $teamData = $resourceData[$format][$team->id]; ?>
                        @if ($key == 'dev')
                            {{ $teamData['total'] - $teamData['pm'] - $teamData['brse'] }}
                        @else
                            {{ $teamData[$key] }}
                        @endif
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
            <tr>
                <td colspan="{{ $countMonth + 2 }}"></td>
            </tr>
        @endforeach
        <tr>
            <td></td>
            <td>{{ trans('project::view.Total personnel redundancy') }}</td>
            @foreach ($arrayMonths as $format => $month)
                <td style="text-align: right;">=SUM({{ implode(',', $collectCellRoles[$format]) }})</td>
            @endforeach
        </tr>
    </tbody>
</table>
