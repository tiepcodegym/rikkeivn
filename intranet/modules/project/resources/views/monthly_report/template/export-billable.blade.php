<?php
use Rikkei\Project\View\MRExcel;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;

$typesMember = ProjectMember::getTypeWithOT();
$lableStates = Project::lablelState() + [Project::STATE_OPPORTUNITY => 'Opportunity'];
$arrDayOfMonths = MRExcel::getDayOfListMonths($arrayMonths);
//thead column left
$colHeadLeft = ['No', 'Opportunity code', 'Customer company', 'Project name', 'Project code', 'Type', 'Estimated', 'Member', 'Role', 'Effort', 'Start', 'End'];
if (isset($groupMember)) {
    $colHeadLeft = ['No', 'Opportunity code', 'Member', 'Total effort', 'Customer company', 'Project name', 'Project code', 'Type', 'Estimated', 'Role', 'Effort', 'Start', 'End'];
}
$numColLeft = count($colHeadLeft);
//list array months input
$numMonth = count($arrayMonths);
//column after list month
$colHeadRight = ['Status', 'Released date', 'Price(USD)', 'Salesman'];
$numColRight = count($colHeadRight);
//total column
$numPerMonth = MRExcel::NUM_PER_MONTH;
$numColHead = $numColLeft + $numMonth * $numPerMonth + $numColRight;
$maxCount = $collection->count();

$offsetColumn = MRExcel::OFFSET_COL;
$typeColumn = MRExcel::TYPE_COL;
if (isset($groupMember)) {
    $collection = $collection->groupBy('member');
//    $offsetColumn = chr(ord($offsetColumn) + 1);
    $typeColumn = 'H';
} else {
    $collection = $collection->groupBy('project_name');
    $collectionItems = $collection->values();
}
$typeColCal = $typeColumn;
$countRowConfig = count($teams) * count($typesMember) + 1;
$roleColumn = MRExcel::ROLE_COL;
?>

<table style="border-collapse: collapse;" id="table_billable">
    <?php
    $offsetRow = count($headTotalRows) + 4;
    $toRow = $offsetRow + $maxCount - 1 + MRExcel::MORE_COL;
    ?>
    <thead>
        @foreach ($headTotalRows as $offset => $rowName)
            <?php
            $idxMonth = 0;
            ?>
            <tr>
                <th></th>
                <th></th>
                @for ($i = 2; $i < $numColLeft - 1; $i++)
                    <th></th>
                @endfor
                <th style="text-align: right;">{{ $rowName }}</th>
                @foreach ($arrayMonths as $idx => $month)
                    @for ($i = 0; $i < $numPerMonth; $i++)
                    <?php
                    $ordCol = ord($offsetColumn) + $idxMonth + $i;
                    $nextCol = MRExcel::getColNameByIndex($ordCol);
                    ?>
                    <th style="text-align: right;">
                        @if ($i !== $numPerMonth - 1)
                            @if ($offset == 0)

                            @elseif ($offset == 1)
                                =SUM({{ $nextCol . $offsetRow . ':' . $nextCol . $toRow }})
                            @else
                                =SUMIF({{ $typeColCal . $offsetRow . ':' . $typeColCal . $toRow . ','
                                        . '"'. $rowName . '",'
                                        . $nextCol . $offsetRow . ':' . $nextCol . $toRow }})
                            @endif
                        @endif
                    </th>
                    @endfor
                    <?php
                    $idxMonth += $numPerMonth;
                    ?>
                @endforeach
                @for ($i = 0; $i < $numColRight; $i++)
                    <th></th>
                @endfor
            </tr>
        @endforeach
        <tr>
            @for ($i = 0; $i < $numColHead; $i++)
            <th></th>
            @endfor
        </tr>
        <tr class="bg-head">
            @foreach ($colHeadLeft as $hIdx => $rowName)
            <td rowspan="2" width="{{ $hIdx == 0 ? 5 : 20 }}">{{ $rowName }}</td>
            @endforeach
            @foreach ($arrayMonths as $month)
            <td colspan="{{ $numPerMonth }}" style="text-align: center;">{{ $month }}</td>
            @endforeach
            @foreach ($colHeadRight as $rowName)
            <td rowspan="2" width="20">{{ $rowName }}</td>
            @endforeach
            @if (!isset($groupMember))
            <td style="background: #ffe7a2;" width="40"></td>
            <td style="background: #ffe7a2;" width="40"></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            @endif
        </tr>
        <tr class="bg-head">
            
            @foreach ($arrayMonths as $month)
            <td width="20">Allocation</td>
            <td width="40">Approved production cost (MM)</td>
            <td width="30">Approved cost (cash)</td>
            <td width="20">Note</td>
            @endforeach
            
            @if (!isset($groupMember))
            <td style="background: #ffe7a2;"></td>
            <td style="background: #ffe7a2;"></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            @endif
        </tr>
    </thead>
    <tbody>
        @if (!isset($groupMember))
            <?php $groupOrder = -1; ?>
            @for ($rowIdx = 0; $rowIdx < $maxCount; $rowIdx++)
                <?php
                $groupItems = isset($collectionItems[$rowIdx]) ? $collectionItems[$rowIdx] : [];
                $countGroup = count($groupItems);
                ?>
                @if ($groupItems)
                    @foreach ($groupItems as $itemIdx => $item)
                    <tr>
                        <?php
                        $groupOrder++;

                        $itemStatus = Project::getLabelState($item->status, $lableStates);
                        $itemRole = ProjectMember::getType($item->role, $typesMember);
                        $itemProjType = Project::getLabelState($item->project_type, $arrayTypeLabels);
                        $itemEstimated = ($item->type_mm == Project::MD_TYPE) ? $item->estimated / 20 : $item->estimated;
                        ?>
                        <td>{{ $groupOrder + 1 }}</td>
                        @if ($itemIdx == 0)
                            <td rowspan="{{ $countGroup }}">{{ $item->code }}</td>
                            <td rowspan="{{ $countGroup }}">{{ $item->customer_company }}</td>
                            <td rowspan="{{ $countGroup }}">{{ $item->project_name }}</td>
                            <td rowspan="{{ $countGroup }}">{{ $item->project_code }}</td>
                            <td rowspan="{{ $countGroup }}">{{ $itemProjType }}</td>
                        @endif
                        <td>{{ $itemEstimated }}</td>
                        <td>{{ ucfirst(preg_replace('/@.*/', '', $item->member)) }}</td>
                        <td>{{ $itemRole }}</td>
                        <td style="background: #ccc;">{{ $item->effort }}</td>
                        <td t="s" style="white-space: nowrap;">{{ $item->start_at }}</td>
                        <td t="s" style="white-space: nowrap;">{{ $item->end_at }}</td>
                        <?php
                        $rowIdxMonth = 0;
                        ?>
                        @foreach ($arrDayOfMonths as $format => $monthData)
                            <td width="10" style="text-align: right; background: #ccc;">
                                {{ MRExcel::getEffortOfMonth($monthData['month'], $item, $monthData['num_day']) }}
                            </td>
                            @if ($itemIdx == 0)
                                <td rowspan="{{ $countGroup }}" width="10" style="text-align: right;">
                                    {{ $item->cost_approved_production }}
                                </td>
                                <td rowspan="{{ $countGroup }}" width="10" style="text-align: right;">
                                    {{ $item->approved_cost }}
                                </td>
                                <td rowspan="{{ $countGroup }}" width="10" style="text-align: right;">
                                    {!! MRExcel::breakLine($item->description) !!}
                                </td>
                            @endif
                            <?php
                            $rowIdxMonth += $numPerMonth;
                            ?>
                        @endforeach
                        @if ($itemIdx == 0)
                            <td rowspan="{{ $countGroup }}">{{ $itemStatus }}</td>
                            <td rowspan="{{ $countGroup }}" t="s" style="white-space: nowrap;">{{ $item->released_date }}</td>
                            <td rowspan="{{ $countGroup }}"></td>
                            <td rowspan="{{ $countGroup }}">{{ ucfirst(preg_replace('/@.*/', '', $item->saleman)) }}</td>
                        @endif

                        <td style="background: #ffe7a2;"></td>
                        <td style="background: #ffe7a2;"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        {!! str_repeat('<td></td>', $numColHead) !!}

                        <td style="background: #ffe7a2;"></td>
                        <td style="background: #ffe7a2;"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endif
            @endfor
        @else
            <?php
            $groupOrder = 0;
            ?>
            @if (!$collection->isEmpty())
                @foreach ($collection as $member => $groupItems)
                    <?php
                    $countGroup = $groupItems->count();
                    ?>
                    @foreach ($groupItems as $grIdx => $item)
                    <?php
                    $groupOrder++;

                    $itemStatus = Project::getLabelState($item->status, $lableStates);
                    $itemRole = ProjectMember::getType($item->role, $typesMember);
                    $itemProjType = Project::getLabelState($item->project_type, $arrayTypeLabels);
                    $itemEstimated = ($item->type_mm == Project::MD_TYPE) ? $item->estimated / 20 : $item->estimated;
                    ?>
                    <tr>
                        <td>{{ $groupOrder }}</td>
                        <td>{{ $item->code }}</td>
                        @if ($grIdx == 0)
                        <td rowspan="{{ $countGroup }}" style="vertical-align: top;">
                            <strong>{{ ucfirst(preg_replace('/@.*/', '', $item->member)) }}</strong>
                        </td>
                        <td rowspan="{{ $countGroup }}" style="vertical-align: top;">{{ $groupItems->sum('effort') }}</td>
                        @else
                        
                        @endif
                        <td>{{ $item->customer_company }}</td>
                        <td>{{ $item->project_name }}</td>
                        <td>{{ $item->project_code }}</td>
                        <td>{{ $itemProjType }}</td>
                        <td>{{ $itemEstimated }}</td>
                        <td>{{ $itemRole }}</td>
                        <td>{{ $item->effort }}</td>
                        <td t="s" style="white-space: nowrap;">{{ $item->start_at }}</td>
                        <td t="s" style="white-space: nowrap;">{{ $item->end_at }}</td>
                        <?php
                        $rowIdxMonth = 0;
                        ?>
                        @foreach ($arrDayOfMonths as $format => $monthData)
                            <td width="10" style="text-align: right;">
                                {{ MRExcel::getEffortOfMonth($monthData['month'], $item, $monthData['num_day']) }}
                            </td>
                            <td width="10" style="text-align: right;">
                                {{ $item->cost_approved_production }}
                            </td>
                            <td width="10" style="text-align: right;">
                                {{ $item->approved_cost }}
                            </td>
                            <td width="10" style="text-align: right;">
                                {!! MRExcel::breakLine($item->description) !!}
                            </td>
                            <?php
                            $rowIdxMonth++;
                            ?>
                        @endforeach
                        <td>{{ $itemStatus }}</td>
                        <td t="s" style="white-space: nowrap;">{{ $item->released_date }}</td>
                        <td></td>
                        <td>{{ ucfirst(preg_replace('/@.*/', '', $item->saleman)) }}</td>
                    </tr>
                    @endforeach
                @endforeach
            @endif
        @endif
    </tbody>
</table>
