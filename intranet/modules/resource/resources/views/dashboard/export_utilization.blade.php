<?php
use Rikkei\Resource\Model\Dashboard;
use Rikkei\Resource\View\View as rView;
use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
?>

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        table tr td {
            border: 1px solid #0a0a0a;
            text-align: center;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td>{{ trans('resource::view.Dashboard.Account') }}</td>
            <td>{{ trans('resource::view.Dashboard.Group') }}</td>
            @foreach ($columnsList as $column)
                <td class="text-align-center week 
                    <?php if (($viewMode === 'week' && strtok($column['number'], '/') == $currentWeek && $column['year'] == $cur_year)
                                || ($viewMode === 'month' && strtok($column['number'], '/') == $month && $column['year'] == $cur_year)
                                || ($viewMode === 'day' &&  $column['number'] == $currentDate)) echo 'current' ?>
                    <?php if ($viewMode === 'day' 
                            && rView::getInstance()->isHoliday($column['number'])) echo 'bg-gray' ?>" 
                >
                    @if ($viewMode == 'week')
                    {{ $column['year'] }} <br>
                    {{ trans('resource::view.Dashboard.Week number', ['number'=> sprintf("%02d", $column['number'])]) }}
                    @elseif ($viewMode == 'month')
                    {{ $column['number'] }}
                    @else
                    {{ trans('resource::view.Date number', ['number'=> date('d/m/Y', strtotime($column['number']))]) }}
                    @endif

                    <br>
                    @if ($viewMode !== 'day')
                    ({{ date('d/m', strtotime($column['start'])) . ' - ' . date('d/m', strtotime($column['end'])) }})
                    @endif
                </td>
            @endforeach
        </tr>

        @if(count($dashboard) > 0)
        @php
            $total = [];
        @endphp
            @foreach($dashboard as $email => $info)
                <tr>
                    <td>{{ rView::getInstance()->getNickname($email) }}</td>
                    <td>{{ $info['userInfo']['team'] }}</td>

                    @foreach ($columnsList as $key => $column)

                    <td> 
                        @if (($viewMode !== 'day' && $column['end'] <= $info['userInfo']['join_date'])
                            || ($viewMode === 'day' && $column['number'] <= $info['userInfo']['join_date']))
                            <span class="pj-info">
                                <span class="effort">
                                    @if ($viewMode === 'day' && rView::getInstance()->isHoliday($column['number']))
                                        HOLIDAY
                                    @endif
                                </span>
                            </span>
                            <br>
                        @else
                            <!-- employee working -->
                            @if (empty($info['userInfo']['leave_date']))
                                @if ($viewMode === 'day' && rView::getInstance()->isHoliday($column['number']))
                                    HOLIDAY
                                @else
                                    @if( count($info['effortInfo'][$column['number']]) > 0)
                                        <?php
                                            $i = 0;
                                            $totalEffort= 0;
                                            for($i >= 0; $i < count($info['effortInfo'][$column['number']]); $i++) {
                                                $totalEffort += $info['effortInfo'][$column['number']][$i]['effort'];
                                            }
                                        ?>
                                    @endif
                                    {{ $totalEffort }}%
                                    @foreach ($info['effortInfo'][$column['number']] as $itemEffort)
                                        @if (isset($total[$column['number']]))
                                            @php
                                                $total[$column['number']] += $itemEffort['effort'];
                                            @endphp
                                        @else
                                            @php
                                                $total[$column['number']] = $itemEffort['effort'];
                                            @endphp
                                        @endif
                                    @endforeach
                                @endif
                                    
                            @else <!-- employee leave -->
                                <!-- Employee outed -->
                                @if (($viewMode !== 'day' && $column['start'] >= $info['userInfo']['leave_date'])
                                    || ($viewMode === 'day' && $column['number'] >= $info['userInfo']['leave_date'])  
                                    )
                                    OUT
                                @else
                                    @if ($viewMode === 'day' && rView::getInstance()->isHoliday($column['number']))
                                        HOLIDAY
                                    @else
                                        @if( count($info['effortInfo'][$column['number']]) > 0)
                                            <?php
                                            $i = 0;
                                            $totalEffort= 0;
                                            for($i >= 0; $i < count($info['effortInfo'][$column['number']]); $i++) {
                                                $totalEffort += $info['effortInfo'][$column['number']][$i]['effort'];
                                            }
                                            ?>
                                        @endif
                                        {{ $totalEffort }}%
                                        @foreach ($info['effortInfo'][$column['number']] as $itemEffort )
                                            @if (isset($total[$column['number']]))
                                                @php
                                                    $total[$column['number']] += $itemEffort['effort'];
                                                @endphp
                                            @else
                                                @php
                                                    $total[$column['number']] = $itemEffort['effort'];
                                                @endphp
                                            @endif
                                        @endforeach
                                    @endif
                                @endif <!-- End Employee outed -->
                            @endif <!-- End employee working -->
                        @endif <!-- End employee have not worked -->
                    </td>
                    @endforeach
                </tr>
            @endforeach
        @endif
        <tr>
            <td colspan="2" class="align-right"><b>{{ trans('resource::view.Average effort (%)') }}</b></td>
            @foreach ($columnsList as $column)
                @php
                    $itemTotal = isset($total[$column['number']]) ? $total[$column['number']] : 0;
                @endphp
                <td>
                    @if (count($dashboard))
                        <b>{{ round($itemTotal/count($dashboard), 2) }}%</b>
                    @endif
                </td>
            @endforeach
        </tr>
        <tr>
            <td colspan="2" class="align-right"><b>{{ trans('resource::view.Total effort (%)') }}</b></td>
            @foreach ($columnsList as $column)
                @php
                    $itemTotal = isset($total[$column['number']]) ? $total[$column['number']] : 0;
                @endphp
                @if (count($dashboard))
                    <td class="abccc"><b>{{ $itemTotal }}%</b></td>
                @endif
            @endforeach
        </tr>
    </table>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.abccc').text('123123');
        });
    </script>
</body>
</html>