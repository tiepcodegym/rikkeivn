<?php

namespace Rikkei\Project\View;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreConfigData;
use DatePeriod;
use DateInterval;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\ProjPointReport;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\View\View as ViewHelper;
use Rikkei\Resource\View\View as Rview;
use Rikkei\Project\Model\ProjectMember;


class ProjectExport
{
    /**
     * get list month of object employee in project
     * 
     * @param object $collection
     * @return array $months
     */
    public static function getListMonths($collection)
    {
        for($x = 0; $x < sizeof($collection); $x++){
            $minDate[] = $collection[$x]->start_at;
            $maxDate[] = $collection[$x]->end_at;
        }
        $period = CarbonPeriod::create(min($minDate), '1 month', max($maxDate));
        foreach ($period as $dt) {
            $months[] = $dt->format("Y-m");
        }
        return $months;
    }
}
