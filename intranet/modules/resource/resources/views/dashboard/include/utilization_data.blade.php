<?php
use Rikkei\Resource\Model\Dashboard;
use Rikkei\Resource\View\View as rView;
use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
?>

<table class="table table-bordered table-hover accountTable table-left" >
    <thead>
    <th style="width: 100px;" class="account vertical-align-middle bg-light-blue">{{ trans('resource::view.Dashboard.Account') }}</th>
        <th style="width: 150px;" class="account vertical-align-middle bg-light-blue">{{ trans('resource::view.Dashboard.Group') }}</th>
    </thead>
    <tbody>
        <?php $row = 1; ?>
        @if(count($dashboard) > 0)
        @foreach($dashboard as $email => $info)

        <tr row="{{$row}}">
            <td rowspan="1" colspan="1" class="vertical-align-middle" >{{ rView::getInstance()->getNickname($email) }}</td>
            <td style="word-break: break-all" rowspan="1" colspan="1" class="vertical-align-middle width-100" >{{ $info['userInfo']['team'] }}</td>
        </tr>
        <?php $row++; ?>
        @endforeach
        @endif
        <tr row="{{$row}}"><td colspan="2" class="align-right"><b>{{ trans('resource::view.Average effort (%)') }}</b></td></tr>
        <tr row="{{$row}}"><td colspan="2" class="align-right"><b>{{ trans('resource::view.Total effort (%)') }}</b></td></tr>
    </tbody>
</table>
<div class="table-responsive padding-left-250">
    <table class="table table-bordered table-hover effortTable table-right" role="grid" aria-describedby="example2_info">
        <thead>
            <tr class="bg-light-blue">
                @foreach ($columnsList as $column)
                    <th class="text-align-center week 
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
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <?php $row = 1; ?>
            @if(count($dashboard) > 0)
            @foreach($dashboard as $email => $info)
            <tr role="row" class="odd" row="{{$row}}">
                <?php $n = 1; ?>
                @foreach ($columnsList as $column)
                <td rowspan="1" class="effort-container font-bold text-align-center align-middle 
                    {{ $viewMode === 'day' && rView::getInstance()->isHoliday($column['number']) ? 'bg-gray' : '' }}"
                    data-child="{{$n}}" data-empid='{{ $info['userInfo']['id'] }}'
                    data-empname='{{ rView::getInstance()->getNickname($email) }}' 
                    data-leave="{{!empty($info['userInfo']['leave_date']) ? date('Y-m-d',strtotime($info['userInfo']['leave_date'])) : ''}}"
                    data-join="{{!empty($info['userInfo']['join_date']) ? date('Y-m-d',strtotime($info['userInfo']['join_date'])) : ''}}"
                    onmouseover="over(this);" onmouseout="out(this);"
                    @if ($viewMode !== 'day')
                        onclick="showDetail({{ strtok($column['number'], '/') }}, {{ $column['year'] }}, this, '{{ $viewMode }}');"
                    @else
                        style="cursor: auto;"
                    @endif
                > 
                    <!-- employee have not worked -->
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
                                <span class="pj-info">
                                    <span class="effort">
                                        HOLIDAY
                                    </span> 
                                </span> 
                            @else
                                @foreach ($info['effortInfo'][$column['number']] as $itemEffort )
                                    @if (!empty($itemEffort['effort']))
                                    <span class="pj-info hidden" data-id="{{ $itemEffort['projId'] }}">
                                        <span class="effort">
                                            {{ $itemEffort['effort'] }}%
                                        </span> 
                                        <span class="pj-name">
                                            {{ $itemEffort['projName'] }}
                                        </span>
                                    </span>
                                    @endif
                                @endforeach
                            @endif
                                
                        @else <!-- employee leave -->
                            <!-- Employee outed -->
                            @if (($viewMode !== 'day' && $column['start'] >= $info['userInfo']['leave_date'])
                                  || ($viewMode === 'day' && $column['number'] >= $info['userInfo']['leave_date'])  
                                )
                                <span class="pj-info">
                                    <span class="effort">
                                        OUT
                                    </span> 
                                </span> 
                                <br>

                            @else
                                @if ($viewMode === 'day' && rView::getInstance()->isHoliday($column['number']))
                                    <span class="pj-info">
                                        <span class="effort">
                                            HOLIDAY
                                        </span> 
                                    </span> 
                                @else
                                    @foreach ($info['effortInfo'][$column['number']] as $itemEffort )
                                        @if (!empty($itemEffort['effort']))
                                        <span class="pj-info hidden" data-id="{{ $itemEffort['projId'] }}">
                                            <span class="effort">
                                                {{ $itemEffort['effort'] }}%
                                            </span> 
                                            <span class="pj-name">
                                                {{ $itemEffort['projName'] }}
                                            </span>
                                        </span> 
                                        @endif
                                    @endforeach
                                @endif
                            @endif <!-- End Employee outed -->
                        @endif <!-- End employee working -->
                        <div class="tooltip"></div>
                    @endif <!-- End employee have not worked -->
                </td>
                <?php $n++; ?>
                @endforeach
            </tr>
            <?php $row++; ?>
            @endforeach
            <tr class="result" row="{{$row}}">
                <?php $n = 1; ?>
                    @foreach ($columnsList as $column)
                        <td class="font-bold average-effort {{ $viewMode === 'day' && rView::getInstance()->isWeekend($column['number']) ? 'bg-gray' : '' }}" data-child="{{ $viewMode === 'day' && rView::getInstance()->isWeekend($column['number']) ? 'WEEKEND' : $n }}">
                        </td>
                        <?php $n++; ?>
                    @endforeach
            </tr>
            <tr class="result-total" row="{{$row}}">
                <?php $n = 1; ?>
                    @foreach ($columnsList as $column)
                        <td class="font-bold average-effort {{ $viewMode === 'day' && rView::getInstance()->isWeekend($column['number']) ? 'bg-gray' : '' }}" data-child="{{ $viewMode === 'day' && rView::getInstance()->isWeekend($column['number']) ? 'WEEKEND' : $n }}">
                        </td>
                        <?php $n++; ?>
                    @endforeach
            </tr>
            @endif
        </tbody>        
             
    </table>
</div>  
<script>
    // count page
    totalPage = {{$collectionModel->lastPage()}};
    pagerInfo = '{{trans("resource::view.Total :records entries / :pages page",["records"=>$collectionModel->total(), "pages" => $collectionModel->lastPage()])}}';
    startDefault = '{{rView::getInstance()->setDefautDateFilter()[0]}}';
    endDefault = '{{rView::getInstance()->setDefautDateFilter()[1]}}';
</script>
