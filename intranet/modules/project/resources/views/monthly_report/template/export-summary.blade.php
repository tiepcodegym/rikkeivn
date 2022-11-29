<?php
use Rikkei\Project\View\MRExcel;

$offsetColumn = MRExcel::OFFSET_COL;
$typeColumn = MRExcel::getColNameByIndex(ord($offsetColumn) - 1);
$offsetRow = 1;
$toRow = $offsetRow + count($arrayTypeLabels) - 1;
$numPerMonth = MRExcel::NUM_PER_MONTH;
?>

<table>
    <thead>
        <tr>
            <th></th>
            @foreach ($arrayMonths as $month)
            <th colspan="{{ $numPerMonth }}" style="text-align: center;">{{ $month }}</th>
            @endforeach
        </tr>
        <tr>
            <td></td>
            @foreach ($arrayMonths as $month)
            <td>Allocation</td>
            <td>Approved production cost (MM)</td>
            <td>Approved cost (cash)</td>
            <td></td>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($headTotalRows as $offset => $rowName)
        <tr>
            <?php
            $idxMonth = 0;
            ?>
            <th style="text-align: right;">{{ $rowName }}</th>
            @foreach ($arrayMonths as $month)
                @for ($i = 0; $i < $numPerMonth; $i++)
                    <?php
                    $ordCol = ord($offsetColumn) + $idxMonth + $i;
                    $nextCol = MRExcel::getColNameByIndex($ordCol);
                    ?>
                    <td style="text-align: right;">
                        @if ($offset > 0 && $i !== $numPerMonth - 1)
                            =SUM({{ $sheetRunning . '!' . $nextCol . ($offsetRow + $offset) . ',' . $sheetOpportunity . '!' . $nextCol . ($offsetRow + $offset) }})
                        @endif
                    </td>
                @endfor
                <?php
                $idxMonth += MRExcel::NUM_PER_MONTH;
                ?>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
