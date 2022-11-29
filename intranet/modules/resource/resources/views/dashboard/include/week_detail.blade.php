<?php

use Rikkei\Resource\View\View as rView;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Project\Model\ProjectMember;

?>

<h4 class="pull-right">Year {{ $year }}, Week {{ $week }}</h4>
<h4>Employee: {{ $empName }}</h4>
<table class="table table-bordered tbl-proj-name" role="grid" aria-describedby="example2_info">
    <thead>
        <th>
            
        </th>
        
    </thead>
    <tbody>
        <?php $row = 1; ?>
        @foreach ($projects as $project)
        <tr row="{{ $row }}">
            <td class="break-word">{{ $project->name . ' (' . ProjectMember::getTypeMemberByKey($project->type) . ')' }}</td>
        </tr>
        <?php $row++; ?>
        @endforeach
    </tbody>
</table>

<div class="div-responsive padding-left-250">
<table class="table table-bordered tbl-effort" role="grid" aria-describedby="example2_info">
    <thead>
        @foreach ($days as $number => $date)
        <th class="text-align-center <?php 
                if (rView::getInstance()->isWeekend($date)
                    || in_array($date, CoreConfigData::getSpecialHolidays(2))
                    || in_array(date('m-d', strtotime($date)), CoreConfigData::getAnnualHolidays(2))) {
                        echo 'bg-holiday'; 
                } 
        ?>">
            @if ($viewMode == 'week')
                {{ rView::getInstance()->getDayByNumber($number) }} <br>
            @endif
            {{ $date }}
        </th>
        @endforeach
    </thead>
    <tbody>
        <?php $row = 1; ?>
        @foreach ($projects as $project)
        <tr row="{{ $row }}">
            @foreach ($days as $number => $date)
            <td class="text-align-center 
                <?php 
                    if (rView::getInstance()->isWeekend($date)
                        || in_array($date, CoreConfigData::getSpecialHolidays(2))
                        || in_array(date('m-d', strtotime($date)), CoreConfigData::getAnnualHolidays(2))) {
                            echo 'bg-holiday'; 
                    }     
                ?>
            ">
            <?php 
                if (in_array($date, CoreConfigData::getSpecialHolidays(2))
                    || in_array(date('m-d', strtotime($date)), CoreConfigData::getAnnualHolidays(2))) {
                        echo 'HOLIDAY'; 
                } else if (rView::getInstance()->isWeekend($date)) {
                        echo 'WEEKEND'; 
                } else if (!empty($leaveDate) && $date > date('Y-m-d', strtotime($leaveDate))) {
                        echo 'OUT'; 
                } else if (date('Y-m-d', strtotime($project->start_at)) <= date('Y-m-d', strtotime($date)) 
                            && date('Y-m-d', strtotime($project->end_at)) >= date('Y-m-d', strtotime($date))
                            && date('Y-m-d', strtotime($date)) >= date('Y-m-d', strtotime($joinDate))) {
                        echo $project->effort . ' %';
                } else {
                        echo '';
                }
            ?>
            </td>
            @endforeach
        </tr>
        <?php $row++; ?>
        @endforeach
    </tbody>
</table>
</div>
