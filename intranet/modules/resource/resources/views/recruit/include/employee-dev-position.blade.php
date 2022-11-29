<?php
use Rikkei\Core\View\Form as FormView;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\TeamList;
use Carbon\Carbon;

$softDevTeams = TeamList::getSoftDevLeafTeams(['team_id' => $cdTeamFilter]);
$devTypes = getOptions::getInstance()->getDevTypeOptions();
$typeLen = count($devTypes);
$bgColors = ['#74a8fc', '#ffa75b', '#f7d86a', '#63c159', '#75d5d8', '#93C47D', '#FFD966', '#3C78D8', '#FCE5CD', '#C9DAF8'];
$teamColors = [];
$dataByProg = $collectionModel->groupBy('prog_id');
$totalByProg = [];
$numTotalProg = 0;
if ($programs) {
    foreach ($programs as $progId => $progName) {
        $count = 0;
        if (isset($dataByProg[$progId])) {
            $count = $dataByProg[$progId]->count();
            $totalByProg[$progId] = $count;
            $numTotalProg += $count;
        }
    }
}
$totalByTeam = [];
$numColor = 190;
?>
<div class="row tab-pane active" id="tab_dev_position">
    <div>
        @if (!$softDevTeams->isEmpty())
        <div class="table-responsive fixed-container" style="max-height: 67vh;">
            <table class="fixed-table table table-striped table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th class="text-uppercase text-center prog-bg" rowspan="2">{{ trans('resource::view.stat.Programming language') }}</th>
                        @foreach ($softDevTeams as $idx => $team)
                        <?php $teamColors[$team->id] = $bgColors[array_rand($bgColors)]; ?>
                        <th class="text-center" colspan="{{ $typeLen }}" style="background: {{ $teamColors[$team->id] }}">{{ $team->name }}</th>
                        @endforeach
                        <th class="prog-bg text-center" rowspan="2">{{ trans('resource::view.stat.Total') }}</th>
                        <th class="prog-bg text-center" rowspan="2">%</th>
                    </tr>
                    <tr>
                        @foreach ($softDevTeams as $team)
                            @foreach ($devTypes as $typeId => $typeName)
                            <th style="background: {{ $teamColors[$team->id] }}">{{ $typeName }}</th>
                            @endforeach
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @if ($programs)
                        @foreach ($programs as $progId => $progName)
                        <?php
                        $dataByTeam = [];
                        if (isset($dataByProg[$progId])) {
                            $dataByTeam = $dataByProg[$progId]->groupBy('team_id');
                        }
                        ?>
                        <tr>
                            <td class="text-bold prog-bg">{{ $progName }}</td>
                            @foreach ($softDevTeams as $team)
                                <?php
                                $dataByType = [];
                                if (isset($dataByTeam[$team->id])) {
                                    $dataByType = $dataByTeam[$team->id]->groupBy('type');
                                }
                                if (!isset($totalByTeam[$team->id])) {
                                    $totalByTeam[$team->id] = [];
                                }
                                ?>
                                @foreach ($devTypes as $typeId => $typeName)
                                <?php
                                $number = isset($dataByType[$typeId]) ? count($dataByType[$typeId]) : 0;
                                if (!isset($totalByTeam[$team->id][$typeId])) {
                                    $totalByTeam[$team->id][$typeId] = 0;
                                }
                                $totalByTeam[$team->id][$typeId] += $number;
                                ?>
                                <td style="text-align: right;
                                    {{ $number ? 'background: rgb(66, '. ($numColor - $number) . ', 245); color: #fff;' : ''  }}">
                                    {{ $number ? $number : null }}
                                </td>
                                @endforeach
                            @endforeach
                            <?php
                            $totalEachProg = isset($totalByProg[$progId]) ? $totalByProg[$progId] : 0;
                            ?>
                            <td class="text-bold prog-bg text-right">{{ $totalEachProg ? $totalEachProg : null }}</td>
                            <td class="text-bold prog-bg text-right">
                                {{ $totalEachProg ? number_format($totalEachProg * 100 / $numTotalProg, 1) . '%' : null }}
                            </td>
                        </tr>
                        @endforeach
                        <tr>
                            <td class="text-bold">{{ trans('resource::view.stat.Total') }}</td>
                            @foreach ($softDevTeams as $team)
                                @foreach ($devTypes as $typeId => $typeName)
                                <?php $totalType = isset($totalByTeam[$team->id][$typeId]) ? $totalByTeam[$team->id][$typeId] : 0; ?>
                                <td class="text-bold text-right">{{ $totalType ? $totalType : null }}</td>
                                @endforeach
                            @endforeach
                            <td class="text-bold text-right">{{ $numTotalProg ? $numTotalProg : null }}</td>
                            <td class="text-bold text-right">100%</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @else
            <p class="text-center">{{ trans('resource::message.Team not found or is not soft develop team')  }}</p>
        @endif
        <div class="box-body"></div>
    </div>
</div>

